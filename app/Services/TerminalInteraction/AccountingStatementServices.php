<?php

/**
 * User: avisconte
 * Date: 05/04/2021
 * Time: 11:00 am
 */

namespace App\Services\TerminalInteraction;

use Excel;
use Carbon\Carbon;

class AccountingStatementServices
{
    /**
     * Esta función sirve para personalizar la excepción
     * 
     * @method custom_error
     * @access public
     * @category Tools
     * @param $e, $function
     * @return array $error_detail 
     */
    private function custom_error($e, $function)
    {
        $error_detail = [
            'exception_message' => $e->getMessage(),
            'file' => $e->getFile(),
            'class' => __CLASS__,
            'function' => $function,
            'line' => $e->getLine()
        ];

        \Log::error('Ocurrió un error. Detalles:');
        \Log::error($error_detail);

        return $error_detail;
    }

    function index($request, $user)
    {
        $data = [
            'filters' => [
                'timestamp' => '',
                'group_branch_user' => '-1',
                'show' => 'todos',
                'activate_summary' => '',
                'activate_closing' => '',
                'atm_ids' => '-1',
                'search_type' => ''
            ],
            'lists' => [
                'list' => null,
                'group_branch_user_list' => '{}'
            ],
            'totals' => [
                'transactions' => 0,
                'deposited' => 0,
                'balance' => 0,
                'days' => null,
            ],
        ];

        try {

            $submit = '';

            if (isset($request['search'])) {
                $submit = 'search';
            } else if (isset($request['generate_x'])) {
                $submit = 'generate_x';
            }


            if ($submit == 'search' or $submit == 'generate_x') {
                $data['filters']['timestamp'] = $request['timestamp'];
                $data['filters']['group_branch_user'] = $request['group_branch_user'];
                $data['filters']['show'] = $request['show'];
                $data['filters']['activate_summary'] = $request['activate_summary'];
                $data['filters']['activate_closing'] = $request['activate_closing'];
            } else {
                $time_init = '00:00:00';
                $time_end = '23:59:59';
                $from = date("d/m/Y");
                $to = date("d/m/Y");
                $timestamp = "$from $time_init - $to $time_end";

                $data['filters']['timestamp'] = $timestamp;
            }

            //\Log::info("submit: $submit");
            //\Log::info("filters:");
            //\Log::info($data['filters']);

            //--------------------------------------------------------------


            //PARA PROBAR:
            //$user->id = 46;

            /**
             * Grupo / Sucursal / Usuario
             */
            $group_branch_user = \DB::table('business_groups as bg')
                ->select(
                    \DB::raw("u.id, bg.description || ' / ' || b.description || ' / ' || u.description as description, b.user_id")
                )
                ->join('branches as b', 'bg.id', '=', 'b.group_id')
                ->join('users as u', 'u.id', '=', 'b.user_id');

            /**
             * Para saber si el usuario es encargado de un Grupo / Sucursales
             */
            $users_x_groups = \DB::table('users_x_groups as uxg')
                ->select(
                    'uxg.group_id'
                )
                ->join('users as u', 'u.id', '=', 'uxg.user_id')
                ->where('u.id', $user->id) //cambiar por user->id
                ->get();

            $business_group_id = null;
            $branch_id = $user->branch_id;

            if (count($users_x_groups) > 0) {
                $business_group_id = $users_x_groups[0]->group_id;
                /**
                 * Listar Grupo / Sucursal / Usuario-encargado
                 */
                $group_branch_user_list = $group_branch_user
                    ->where('bg.id', $business_group_id)
                    ->whereIn('b.owner_id', [16, 21, 25])
                    ->get();

                $data['filters']['search_type'] = 'Grupo Sucursal';
            } else {
                /**
                 * Para saber si el usuario es encargado de la Sucursal
                 */
                $group_branch_user_list = $group_branch_user
                    ->where('b.user_id', $user->id)
                    ->whereIn('b.owner_id', [16, 21, 25])
                    ->get();

                $data['filters']['search_type'] = 'Sucursal';
            }

            /**
             * Filtro del user_id dependiendo de la situación
             */
            if (count($group_branch_user_list) > 0) {
                /**
                 * El primer registro de la lista
                 */
                //$data['filters']['group_branch_user'] = $group_branch_user_list[0]->user_id;
                $data['lists']['group_branch_user_list'] = $group_branch_user_list;
            }

            if ($data['filters']['group_branch_user'] == '') {
                $data['filters']['group_branch_user'] = $user->id;
            }

            //$data['filters']['group_branch_user'] = 186;
            //$data['filters']['atm_ids'] = 210;


            //--------------------------------------------------------------

            $atms = \DB::table('atms as a')
                ->select(
                    \DB::raw("array_to_string(array_agg(a.id), ', ') as ids")
                )
                ->join('points_of_sale as pos', 'a.id', '=', 'pos.atm_id')
                ->join('branches as b', 'b.id', '=', 'pos.branch_id')
                ->where('b.user_id','=',$data['filters']['group_branch_user'])
                ->whereNull('pos.deleted_at')
                ->whereIn('a.owner_id' , [16,21, 25])
                ->groupBy('pos.id');

            //\Log::info('atms:');
            //\Log::info($atms->toSql());

            $atms = $atms->get();

            //\Log::info('atms lista:');
            //\Log::info($atms);

            if (count($atms) > 0) {
                $data['filters']['atm_ids'] = $atms[0]->ids;
            }

            //--------------------------------------------------------------


            //\Log::info('FILTROS FINALES:');
            //\Log::info($data['filters']);

            $list = $this->get_transactions($data['filters']);
            $data['lists']['list'] = $list['list'];
            $data['totals']['transactions'] = $list['transactions'];
            $data['totals']['deposited'] = $list['deposited'];
            $data['totals']['balance'] = $list['balance'];
            $data['totals']['days'] = $this->get_days($data['filters']);

            $message = '';
            $message_type = 'message';
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
            $message = 'Error al cargar página.';
            $message_type = 'error_message';
        }

        $data['filters'] = json_encode($data['filters']);
        $data['lists']['group_branch_user_list'] = json_encode($data['lists']['group_branch_user_list']);

        \Session::flash($message_type, $message);
        return view('terminal_interaction.accounting_statement.index', compact('data'));
    }

    /**
     * Obtine todas las transacciones.
     * Tipos de acceso-consulta:
     * 1) Grupo / Sucursales
     * 2) Sucursal
     */
    function get_transactions($filters)
    {
        $list = [
            'list' => null,
            'transactions' => 0,
            'deposited' => 0,
            'balance' => 0
        ];

        try {

            $timestamp = $filters['timestamp'];
            $group_branch_user = $filters['group_branch_user'];
            $show = $filters['show'];
            $activate_summary = $filters['activate_summary'];
            $activate_closing = $filters['activate_closing'];
            $atm_ids = $filters['atm_ids'];

            $aux = explode(' - ', str_replace('/', '-', $timestamp));
            $from = date('Y-m-d H:i:s', strtotime($aux[0]));
            $to = date('Y-m-d H:i:s', strtotime($aux[1]));

            if ($activate_summary == 'on') {
                $date = date('N');
                $days = '';

                if ($date == 1 || $date == 3 || $date == 5) {
                    $days = '-1 days';
                } else if ($date == 2 || $date == 4 || $date == 6) {
                    $days = '-2 days';
                } else {
                    $days = '-3 days';
                }

                //\Log::info("days: $days");

                $to = Carbon::parse(date('Y-m-d 23:59:59'))->modify($days);
            } else if ($activate_closing == 'on') {
                $to = date('Y-m-d H:i:s');
            }

            //\Log::info("to: $to");

            $query = "";

            $joins = "
                left join service_provider_products sp on t.service_id = sp.id and t.service_source_id = 0
                left join service_providers on service_providers.id = sp.service_provider_id and t.service_source_id = 0
                left join mt_recibos_reversiones on t.id = mt_recibos_reversiones.transaction_id
                left join services_providers_sources sps on t.service_source_id = sps.id and t.service_source_id <> 0
                left join services_ondanet_pairing sop on t.service_id = sop.service_request_id
                                                       and t.service_source_id = sop.service_source_id
                                                       and t.service_source_id <> 0
            ";

            $select_union = "
                select
                    bd.fecha as created_at,
                    to_char(bd.fecha, 'DD/MM/YYYY HH24:MI:SS') as created_at_view,
                    concat('Boleta Depósito Nro.',' ', bd.boleta_numero , ' | ', bancos.descripcion, ' | Cta. ', cuentas_bancarias.numero_banco) as concept,
                    0 as debe,
                    bd.monto::bigint as haber
                from boletas_depositos bd                                    
                inner join cuentas_bancarias on cuentas_bancarias.id = bd.cuenta_bancaria_id
                inner join bancos on bancos.id = cuentas_bancarias.banco_id    
                where bd.estado = true and user_id = $group_branch_user
            ";

            $sub_select = " 
                select
                    balances.*,
                    sum (balances.haber + balances.debe) over (
                        order by created_at
                        rows between unbounded preceding and current row
                    ) as saldo
                from balances
                where created_at between '{$from}' and '{$to}'
                order by created_at asc;
            ";

            switch ($show) {
                case 'todos':
                    $select_main = "
                        select
                            t.created_at,
                            to_char(t.created_at, 'DD/MM/YYYY HH24:MI:SS') as created_at_view,
                            (case when t.service_source_id = 0 then concat(service_providers.name, ' - ', sp.description)
                            else concat(sps.description, ' - ', sop.service_description) end) as concept,
                            - 
                            (case when status = 'success' then abs(t.amount)
                            when status = 'error' and t.service_id in(14, 15) then abs(t.amount) end)::bigint as debe,
                            0 as haber
                        from transactions t
                    ";

                    $query = "
                        with balances as (
                            $select_main
                            $joins
                            where atm_id in ($atm_ids) and mt_recibos_reversiones.transaction_id is null and t.transaction_type in (1,7)
                            union
                            $select_union
                        )
                        $sub_select
                    ";
                    break;

                case 'depositos':
                    $query = "
                        with balances as (
                            $select_union
                        )
                        $sub_select
                    ";

                    break;

                case 'transacciones':
                    $select_main = "
                        select
                            t.created_at,
                            to_char(t.created_at, 'DD/MM/YYYY HH24:MI:SS') as created_at_view,
                            (case when t.service_source_id = 0 then concat(service_providers.name, ' - ', sp.description)
                            else concat(sps.description, ' - ', sop.service_description) end) as concept,
                            - 
                            (case when status = 'success' then abs(t.amount)
                            when status = 'error' and t.service_id in(14, 15) THEN abs(t.amount)
                            else 0 end) as debe,
                            0 as haber
                        from transactions t
                    ";

                    $query = "
                        with balances as (
                            $select_main

                            $joins 

                            where atm_id in ($atm_ids)
                            and status in ('success', 'error')
                            and mt_recibos_reversiones.transaction_id is null
                            and t.transaction_type in (1,7)
                        )
                        $sub_select
                    ";

                    break;
            }

            $records_list = ($query !== "") ? \DB::select(\DB::raw($query)) : [];

            if (count($records_list) > 0) {
                //Convertir la lista
                $records_list = array_map(function ($value) {
                    return (array) $value;
                }, $records_list);
            }

            //-------------------------------------------------------------------------------------

            /**
             * Totales:
             * Transactions: debe
             * Deposited: haber
             * Balance: saldo
             */

            $transactions = \DB::select("
                select
                    coalesce(sum(case when status = 'success' then abs(t.amount)
                                when status = 'error' and t.service_id in(14, 15) then abs(t.amount)
                            else 0 end), 0)::bigint as total
                from transactions t
                
                $joins

                where atm_id in ($atm_ids)
                and mt_recibos_reversiones.transaction_id is null
                and t.transaction_type in (1,7)
                and t.created_at between '{$from}' and '{$to}'
            ");

            $deposited = \DB::table('boletas_depositos as bd')
                ->selectRaw(
                    'coalesce(sum(monto), 0)::bigint as total'
                )
                ->where('bd.estado', true)
                ->where('bd.user_id', $group_branch_user)
                ->whereRaw("bd.fecha between '{$from}' and '{$to}'");

            //\Log::info('query:');
            //\Log::info($deposited->toSql());

            $deposited = $deposited->get();

            if (count($transactions) > 0) {
                $transactions = $transactions[0]->total;
            }

            if (count($deposited) > 0) {
                $deposited = $deposited[0]->total;
            }

            $balance = $deposited - $transactions;

            //-------------------------------------------------------------------------------------

            $list = [
                'list' => $records_list,
                'transactions' => number_format((int) $transactions, 0, '', '.'),
                'deposited' => number_format((int) $deposited, 0, '', '.'),
                'balance' => number_format((int) $balance, 0, '', '.')
            ];
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }


    /**
     * Obtiene los días entre dos fechas por medio de un calculo en postgresql
     */
    function get_days($filters)
    {
        $days = 0;

        try {
            $timestamp = $filters['timestamp'];
            $aux = explode(' - ', str_replace('/', '-', $timestamp));
            $from = date('Y-m-d H:i:s', strtotime($aux[0]));
            $to = date('Y-m-d H:i:s', strtotime($aux[1]));

            $days = \DB::select("select ('{$to}'::date - '{$from}'::date) as days");
            $days = $days[0]->days;
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $days;
    }
}
