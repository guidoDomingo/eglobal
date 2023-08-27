<?php

namespace App\Http\Controllers\Ussd;

use App\Exports\ExcelExport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Excel;

class UssdTransactionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    /**
     * Esta función sirve para personalizar la excepción
     * 
     * @method custom_error
     * @access public
     * @category Tools
     * @param $e, $function
     * @return array $error_detail 
     */
    public function custom_error($e, $function)
    {
        $error_detail = [
            'message' => $e->getMessage(),
            'file' => $e->getFile(),
            'class' => __CLASS__,
            'function' => $function,
            'line' => $e->getLine()
        ];

        \Log::error('Ocurrió una excepción en:');
        \Log::error($error_detail);

        return $error_detail;
    }

    /**
     * Todas las consultas a la base de datos contienen los filtros que provienen del front-end
     */
    function customize_filters($filters, $records_list)
    {
        try {
            $timestamp = $filters['timestamp'];
            $phone_number = $filters['phone_number'];

            //$record_limit = $filters['record_limit'];
            $status_id = $filters['status_id'];
            $atm_id = $filters['atm_id'];
            $branch_id = $filters['branch_id'];
            $service_id = $filters['service_id'];

            $operator_id = $filters['operator_id'];
            $channel_id = $filters['channel_id'];
            $option_id = $filters['option_id'];
            $final_transaction_message_id = $filters['final_transaction_message_id'];
            $transaction_id = $filters['transaction_id'];
            $transaction_status_id = $filters['transaction_status_id'];
            $send = $filters['send'];
            $historic = $filters['historic'];

            $list_of_ids = $filters['list_of_ids'];

            if ($timestamp !== null) {
                $aux = explode(' - ', str_replace('/', '-', $timestamp));
                $from = date('Y-m-d H:i:s', strtotime($aux[0]));
                $to = date('Y-m-d H:i:s', strtotime($aux[1]));
                $records_list = $records_list->whereRaw("mudc.created_at between '{$from}' and '{$to}'");
            }

            if ($phone_number !== '') {
                $records_list = $records_list->where('mudc.phone_number', $phone_number);
            }

            if ($status_id !== '') {
                $records_list = $records_list->where('mudc.menu_ussd_status_id', intval($status_id));
            }

            if ($atm_id !== '') {
                $records_list = $records_list->where('t.atm_id', intval($atm_id));
            }

            if ($branch_id !== '') {
                $records_list = $records_list->where('b.id', intval($branch_id));
            }

            if ($service_id !== '') {
                $records_list = $records_list->where('mud.service_id', intval($service_id));
            }

            if ($operator_id !== '') {
                $records_list = $records_list->where('mu.menu_ussd_operator_id', intval($operator_id));
            }

            if ($channel_id !== '') {
                $records_list = $records_list->where('a.type', $channel_id);
            }

            if ($option_id !== '') {
                $records_list = $records_list->where('mud.id', intval($option_id));
            }

            if ($transaction_id !== '') {
                $records_list = $records_list->where('t.id', intval($transaction_id));
            }

            if ($transaction_status_id !== '') {
                $records_list = $records_list->where('t.status', $transaction_status_id);
            }

            if ($final_transaction_message_id !== '') {
                $final_transaction_message_id = json_decode($final_transaction_message_id);
                $final_transaction_message_id = $final_transaction_message_id->description;
                $records_list = $records_list->whereRaw("mudc.final_transaction_message ilike '%$final_transaction_message_id%'");
            }

            if ($historic !== '') {

                $in_not_in = 'not in';

                if ($historic == 'historic') {
                    $in_not_in = 'in';
                }

                $sub_query = "
                    mudc.id $in_not_in (
                        select distinct mudca.menu_ussd_detail_client_id_old 
                        from ussd.menu_ussd_detail_client_audit mudca
                        where mudc.id = mudca.menu_ussd_detail_client_id_old
                        limit 1
                    ) 
                ";

                $records_list = $records_list->whereRaw($sub_query);
            }

            //transacción fecha de creación: 20/11/2021
            //transacción fecha de actualización: 20/11/2021

            //transacción fecha de creación: 20/11/2021
            //transacción fecha de actualización: 20/11/2021

            if ($send !== '') {

                $boolean_1 = 'false';

                if ($send == 'SI') {
                    $boolean_1 = 'true';
                }

                $sub_query = "
                    $boolean_1 = coalesce(
                        (select $boolean_1
                         from ussd.menu_ussd_phone_message as mupm
                         where mupm.message ilike '%' || mudc.phone_number || '%' 
                         and mupm.created_at > mupm.message_date_time
                         and mupm.message_date_time between mudc.created_at and mupm.created_at
                         limit 1), false
                    ) and mudc.menu_ussd_status_id = 4
                ";

                /*$sub_query = "
                    $boolean_1 = coalesce (
                        false, (
                            select $boolean_2
                            from ussd.menu_ussd_phone_message as mupm
                            where mupm.message ~ mudc.phone_number 
                            and mupm.created_at > mudc.created_at 
                            and mupm.message_date_time between mudc.created_at and mupm.created_at
                            limit 1
                        ) 
                    ) and mudc.menu_ussd_status_id = 4
                ";*/

                \Log::info('Query generado:');
                \Log::info($sub_query);

                $records_list = $records_list->whereRaw($sub_query);
            }

            if ($list_of_ids !== '') {
                $records_list = $records_list->whereRaw("mudc.id = any(array[$list_of_ids])");
            }

            //\Log::info('Query generado:');
            //\Log::info($records_list->toSql());
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $records_list;
    }

    /**
     * Obtine todas las transacciones que dieron error.
     */
    function get_menu_ussd_detail()
    {
        return \DB::table('ussd.menu_ussd_operator as muo')
            ->select(
                'muo.id as menu_ussd_operator_id',
                'muo.description as menu_ussd_operator',
                'mud.id as menu_ussd_detail_id',
                'mud.description as option',
                'mud.amount'
            )
            ->join('ussd.menu_ussd as mu', 'muo.id', '=', 'mu.menu_ussd_operator_id')
            ->join('ussd.menu_ussd_detail as mud', 'mu.id', '=', 'mud.menu_ussd_id')
            ->where('mu.status', true)
            ->where('mud.status', true)
            ->whereRaw('mud.service_id = any(array[12, 16])')
            ->groupBy('muo.id', 'muo.description', 'mud.id', 'mud.description', 'mud.amount')
            ->orderBy('mud.amount', 'asc')
            ->get();
    }

    /**
     * Obtine todas las transacciones que dieron error.
     */
    function get_transactions($filters)
    {
        $list = [];

        $this->get_menu_ussd_detail();

        try {
            $status_id = $filters['status_id'];
            $phone_number = $filters['phone_number'];

            $records_list = \DB::table('ussd.menu_ussd_detail_client as mudc')
                ->select(
                    'muo.id as menu_ussd_operator_id',
                    'muo.description as operator',
                    'muse.description as option',
                    'mud.description as sub_option',
                    'mudc.amount',
                    'mudc.phone_number',
                    'mudc.id as operation',
                    'mudc.menu_ussd_status_id as status_id',
                    'mudc.wrong_run_counter',
                    'mus.description as menu_ussd_status',

                    't.id as transaction_id',
                    't.status as transaction_status',
                    't.status_description',
                    'a.name as atm',
                    'b.description as branch',
                    'pos.description as points_of_sale',

                    \DB::raw("case 
                        when a.type = 'da' then 'App Billetaje' 
                        when a.type = 'ws' then 'Web Service' 
                        when a.type = 'at' then 'ATM' 
                        else 'Sin canal.' 
                        end as channel
                    "),

                    \DB::raw("
                        case when mudc.final_transaction_message is null or mudc.final_transaction_message = '' 
                        then 'Sin mensaje' 
                        else mudc.final_transaction_message 
                        end as final_transaction_message
                    "),

                    \DB::raw("
                        (select count(mudca.id)
                        from ussd.menu_ussd_detail_client_audit mudca 
                        where mudc.id = mudca.menu_ussd_detail_client_id_old) as relaunch_amount
                    "),

                    \DB::raw("
                        (case
                         when mudc.menu_ussd_status_id = 2 then 'SI'
                         when mudc.menu_ussd_status_id = 4
                            then coalesce(
                                (select 'SI'
                                 from ussd.menu_ussd_phone_message as mupm
                                 where mupm.message ilike '%' || mudc.phone_number || '%' 
                                 and mupm.created_at > mupm.message_date_time
                                 and mupm.message_date_time between mudc.created_at and mupm.created_at
                                 limit 1)::text, 'NO'
                            )
                            else 'NO'
                        end) as sent
                    "),

                    \DB::raw("coalesce(to_char(mudc.created_at, 'DD/MM/YYYY HH24:MI:SS'), '') as created_at"),
                    \DB::raw("coalesce(to_char(mudc.updated_at, 'DD/MM/YYYY HH24:MI:SS'), '') as updated_at")
                )
                ->join('ussd.menu_ussd_detail as mud', 'mud.id', '=', 'mudc.menu_ussd_detail_id')
                ->join('ussd.menu_ussd as mu', 'mu.id', '=', 'mud.menu_ussd_id')
                ->join('ussd.menu_ussd_operator as muo', 'muo.id', '=', 'mudc.menu_ussd_operator_id')
                ->join('ussd.menu_ussd_status as mus', 'mus.id', '=', 'mudc.menu_ussd_status_id')
                ->join('ussd.menu_ussd_service as muse', 'muse.service_id', '=', 'mud.service_id')

                ->join('transactions as t', 't.id', '=', 'mudc.transaction_id')
                ->join('points_of_sale as pos', 't.atm_id', '=', 'pos.atm_id')
                ->join('atms as a', 'a.id', '=', 't.atm_id')
                ->join('branches as b', 'b.id', '=', 'pos.branch_id');

            $records_list = $this->customize_filters($filters, $records_list);

            $records_list = $records_list->orderBy('mudc.created_at', 'asc');

            $record_limit = $filters['record_limit'];

            if ($record_limit !== '') {
                $records_list = $records_list->take(intval($record_limit));
            }

            \Log::info("QUERY:");
            \Log::info($records_list->toSql());

            //La ultima sentencia del select.
            $records_list = $records_list->get();

            $records_list_aux = [];

            //Recorrer la lista para identificar los desconocidos
            /*foreach ($records_list as $item) {
                $status_id = $item->status_id;
                $phone_number = $item->phone_number;
                $created_at_date = $item->created_at_date;

                if ($status_id == 2) {
                    $item->sent = 'SI';
                } else if ($status_id == 4) {
                    $menu_ussd_phone_message = \DB::table('ussd.menu_ussd_phone_message')
                        ->select(
                            'id'
                        )
                        ->whereRaw("message ~ '$phone_number'")
                        ->whereRaw("created_at > '$created_at_date'")
                        ->whereRaw("message_date_time between '$created_at_date' and created_at")
                        ->take(1)
                        ->get();

                    //Convertir la lista
                    if (count($menu_ussd_phone_message) > 0) {
                        $item->sent = 'SI';
                    }
                }
            }*/
            
            /*
                                \DB::raw("
                        (
                            case
                            when mudc.menu_ussd_status_id = 2 then 'SI' 
                            when mudc.menu_ussd_status_id = 3 or mudc.menu_ussd_status_id = 4 
                            then coalesce(
                                'NO', (
                                    select 'SI'
                                    from ussd.menu_ussd_phone_message as mupm
                                    where mupm.message ~ mudc.phone_number 
                                    and mupm.created_at > mudc.created_at 
                                    and mupm.message_date_time between mudc.created_at and mupm.created_at
                                    limit 1
                                ) 
                            )
                            else 'NO' 
                            end
                        ) as sent
                    ")
            */

            $list = $records_list;
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }

    /**
     * Obtiene el total por cada estado de transacción
     */
    function get_total_by_state($filters)
    {
        $list = [];

        try {
            //Para una lista de estados y montos
            $menu_ussd_status_list = \DB::table('ussd.menu_ussd_status')
                ->select(
                    'id',
                    'description',
                    \DB::raw('0 as total'),
                    \DB::raw('0 as total_amount')
                )
                ->orderBy('id', 'asc')
                ->get();

            $menu_ussd_status_list = array_map(function ($value) {
                return (array) $value;
            }, $menu_ussd_status_list->toArray());

            $records_list = \DB::table('ussd.menu_ussd_detail_client as mudc')
                ->select(
                    'mudc.menu_ussd_status_id',
                    \DB::raw("count(mudc.menu_ussd_status_id) as total"),
                    \DB::raw("sum(mudc.amount) as total_amount")
                )
                ->join('ussd.menu_ussd_detail as mud', 'mud.id', '=', 'mudc.menu_ussd_detail_id')
                ->join('ussd.menu_ussd as mu', 'mu.id', '=', 'mud.menu_ussd_id')
                ->join('ussd.menu_ussd_status as mus', 'mus.id', '=', 'mudc.menu_ussd_status_id')

                ->join('transactions as t', 't.id', '=', 'mudc.transaction_id')
                ->join('points_of_sale as pos', 't.atm_id', '=', 'pos.atm_id')
                ->join('atms as a', 'a.id', '=', 't.atm_id')
                ->join('branches as b', 'b.id', '=', 'pos.branch_id');

            $records_list = $this->customize_filters($filters, $records_list);

            $records_list = $records_list->groupBy('mudc.menu_ussd_status_id');

            $record_limit = $filters['record_limit'];

            if ($record_limit !== '') {
                $records_list = $records_list->take(intval($record_limit));
            }

            //\Log::info($records_list->toSql());

            //La ultima sentencia del select.
            $records_list = $records_list->get();

            $menu_ussd_detail_client_list = array_map(function ($value) {
                return (array) $value;
            }, $records_list->toArray());

            for ($i = 0; $i < count($menu_ussd_status_list); $i++) {
                for ($j = 0; $j < count($menu_ussd_detail_client_list); $j++) {
                    if ($menu_ussd_status_list[$i]['id'] == $menu_ussd_detail_client_list[$j]['menu_ussd_status_id']) {
                        $menu_ussd_status_list[$i]['total'] = $menu_ussd_detail_client_list[$j]['total'];
                        $menu_ussd_status_list[$i]['total_amount'] = $menu_ussd_detail_client_list[$j]['total_amount'];
                    }
                }
            }

            $list = $menu_ussd_status_list;
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

    /**
     * Obtine todas las transacciones según los filtros.
     */
    function get_total_transactions($filters)
    {
        $list = [
            "total" => 0,
            "total_amount" => 0
        ];

        try {
            $records_list = \DB::table('ussd.menu_ussd_detail_client as mudc')
                ->select(
                    \DB::raw('coalesce(count(mudc.id), 0) as total'),
                    \DB::raw('coalesce(sum(mudc.amount), 0) as total_amount')
                )
                ->join('ussd.menu_ussd_detail as mud', 'mud.id', '=', 'mudc.menu_ussd_detail_id')
                ->join('ussd.menu_ussd as mu', 'mu.id', '=', 'mud.menu_ussd_id')

                ->join('transactions as t', 't.id', '=', 'mudc.transaction_id')
                ->join('points_of_sale as pos', 't.atm_id', '=', 'pos.atm_id')
                ->join('atms as a', 'a.id', '=', 't.atm_id')
                ->join('branches as b', 'b.id', '=', 'pos.branch_id');

            $records_list = $this->customize_filters($filters, $records_list);

            $record_limit = $filters['record_limit'];

            if ($record_limit !== '') {
                $records_list = $records_list->take(intval($record_limit));
            }

            //La ultima sentencia del select.
            $records_list = $records_list->get();

            if (count($records_list) > 0) {
                $list = [
                    'total' => $records_list[0]->total,
                    'total_amount' => $records_list[0]->total_amount
                ];
            }
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }

    /**
     * Obtine los servicios ussd.
     */
    function get_operators()
    {
        $list = [];

        try {
            $list = \DB::table('ussd.menu_ussd_operator')
                ->select(
                    'id',
                    \DB::raw(
                        "upper(description) || ' actualmente: ' || (case when status = true then 'Activo' else 'Inactivo' end) as description"
                    )
                )
                ->orderBy('created_at', 'asc')
                ->get();
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }

    /**
     * Obtine los servicios ussd.
     */
    function get_services()
    {
        $list = [];

        try {
            $list = \DB::table('ussd.menu_ussd_service as mus')
                ->select(
                    'mus.service_id as id',
                    \DB::raw(
                        "upper(muo.description) || ' - ' || mus.description || ' - ' || 
                        (case when mus.status = true then 'Activo' else 'Inactivo' end) as description"
                    )
                )
                ->join('ussd.menu_ussd_operator as muo', 'muo.id', '=', 'mus.menu_ussd_operator_id')
                ->orderBy('muo.created_at', 'asc')
                ->get();
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }

    /**
     * Obtine las opciones disponibles
     */
    function get_options()
    {
        $list = [];

        try {
            $list = \DB::table('ussd.menu_ussd_detail as mud')
                ->select(
                    'mud.id',
                    \DB::raw(
                        "upper(muo.description) || ' - ' || mus.description || ' - ' || mud.description || 
                        (case when mud.multiple_pack is not null then ' - Paquete múltiple' else '' end) || 
                        (case when mud.status = true then ' - Activo' else ' - Inactivo' end)
                        as description"
                    )
                )
                ->join('ussd.menu_ussd as mu', 'mu.id', '=', 'mud.menu_ussd_id')
                ->join('ussd.menu_ussd_service as mus', 'mus.service_id', '=', 'mud.service_id')
                ->join('ussd.menu_ussd_operator as muo', 'muo.id', '=', 'mus.menu_ussd_operator_id')
                ->where('mu.status', true)
                //->where('mud.status', true)
                ->where('mud.menu_ussd_type_id', 2)
                ->whereRaw('mud.amount is not null')
                ->orderBy('mud.id', 'asc')
                ->get();
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }

    /**
     * Obtine todos los datos de un registro del cliente por id.
     */
    function get_states()
    {
        $list = [];

        try {
            $list = \DB::table('ussd.menu_ussd_status')
                ->select('id', 'description')
                ->where('status', true)
                ->orderBy('id', 'asc')
                ->get();
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }

    /**
     * Obtine las sucursales.
     */
    function get_branches()
    {
        $list = [];

        try {
            $list = \DB::table('branches')
                ->select(
                    'id',
                    'description'
                )
                ->get();
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }

    /**
     * Obtine los atms activos
     */
    function get_atms()
    {
        $list = [];

        try {
            $list = \DB::table('atms')
                ->select(
                    'id',
                    \DB::raw("'#' || id || '. ' || name as description")
                )
                ->orderBy('id', 'asc')
                ->get();
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }

    /**
     * Obtine los canales
     */
    function get_channels()
    {
        $list = [];

        try {
            $list = [
                [
                    'id' => 'da',
                    'description' => 'App Billetaje'
                ],
                [
                    'id' => 'ws',
                    'description' => 'Web Service'
                ],
                [
                    'id' => 'at',
                    'description' => 'ATM'
                ]
            ];
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }

    /**
     * Obtine la lista de limites
     */
    function get_record_limits()
    {
        $list = [];

        try {

            $options = [
                1, 2, 5, 10, 20, 30, 50, 70, 100, 150, 300, 500, 1000, 1500, 2000
            ];

            for ($i = 0; $i < count($options); $i++) {

                $option = $options[$i];

                $item = [
                    'id' => "$option",
                    'description' => "Listar solo $option"
                ];

                array_push($list, $item);
            }
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }

    /**
     * Obtine los estados para envíados
     */
    function get_sends()
    {
        $list = [];

        try {
            $list = [
                [
                    'id' => 'SI',
                    'description' => 'Envíados'
                ],
                [
                    'id' => 'NO',
                    'description' => 'No Envíados'
                ]
            ];

        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }

    /*
        $transaction_status == 'error' or 
        $transaction_status == 'rollback' or 
        $transaction_status == 'error dispositivo' or 
        $transaction_status == 'devolucion' or 
        $transaction_status == 'inconsistency') {
        $transaction_label = 'danger';
    }*/

    /**
     * Obtine los estados para envíados
     */
    function get_transaction_status()
    {
        $list = [];

        try {
            $list = [
                [
                    'id' => 'success',
                    'description' => 'Exitosa'
                ],
                [
                    'id' => 'error',
                    'description' => 'Error'
                ],
                [
                    'id' => 'pendiente',
                    'description' => 'Pendiente'
                ],
                [
                    'id' => 'reprocesando',
                    'description' => 'Reprocesando'
                ],
                [
                    'id' => 'iniciated',
                    'description' => 'Iniciado'
                ],
                [
                    'id' => 'nulled',
                    'description' => 'Anulado'
                ],
                [
                    'id' => 'canceled',
                    'description' => 'Cancelado'
                ],
                [
                    'id' => 'rollback',
                    'description' => 'Retroceso'
                ],
                [
                    'id' => 'error dispositivo',
                    'description' => 'Error dispositivo'
                ],
            ];
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }

    /**
     * Obtine lista de historico
     */
    function get_historics()
    {
        $list = [];

        try {
            $list = [
                [
                    'id' => 'historic',
                    'description' => 'Con historico.'
                ],
                [
                    'id' => 'no_historic',
                    'description' => 'Sin historico.'
                ]
            ];
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }


    /**
     * Obtine todas las razones de recarga
     */
    function get_reason()
    {
        $list = [];

        try {
            $list = \DB::table('ussd.menu_ussd_detail_client_reason')
                ->select(
                    'id',
                    'description'
                )
                ->where('status', true)
                ->orderBy('description', 'asc')
                ->get();
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }

    /**
     * Obtine todos los tipos de recargas.
     */
    function get_recharge_type()
    {
        $list = [];

        try {
            $list = \DB::table('ussd.menu_ussd_recharge_type')
                ->select('id', 'description')
                ->where('status', true)
                ->get();
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }

    
    /*select muo.description as operator, 
        case when mudc.final_transaction_message is null or mudc.final_transaction_message = '' 
        then 'Sin mensaje de operadora. ' || (
            case when mus.id = 1 then mus.description else 'Ejecutados' end
        )
        else mudc.final_transaction_message 
        end as final_transaction_message, 
        count(mudc.id),
        array_agg(mudc.id) as list_of_ids
    from ussd.menu_ussd_detail_client mudc 
    join ussd.menu_ussd_operator muo on muo.id = mudc.menu_ussd_operator_id 
    join ussd.menu_ussd_status mus on mus.id = mudc.menu_ussd_status_id 
    where mudc.created_at::date between '2021-10-01' and '2021-10-31' 
    group by muo.id, muo.description, mus.id, mudc.final_transaction_message
    order by muo.id*/

    /**
     * Obtine los mensajes por operadora.
     */
    function get_messages_by_operator($filters)
    {
        $list = [];

        try {
            $records_list = \DB::table('ussd.menu_ussd_detail_client as mudc')
                ->select(
                    'muo.id',
                    'muo.description as operator',
                    \DB::raw(
                        "case when mudc.final_transaction_message is null or mudc.final_transaction_message = '' 
                        then (case when mus.id = 1 then '(' || mus.description || ')' else '(Ejecutados)' end) || ' Sin mensaje de operadora.'
                        else mudc.final_transaction_message 
                        end as final_transaction_message"
                    ),
                    \DB::raw('count(mudc.id)'),
                    \DB::raw("array_to_string(array_agg(mudc.id), ', ') as list_of_ids")
                )
                ->join('ussd.menu_ussd_detail as mud', 'mud.id', '=', 'mudc.menu_ussd_detail_id')
                ->join('ussd.menu_ussd as mu', 'mu.id', '=', 'mud.menu_ussd_id')
                ->join('ussd.menu_ussd_operator as muo', 'muo.id', '=', 'mudc.menu_ussd_operator_id')
                ->join('ussd.menu_ussd_status as mus', 'mus.id', '=', 'mudc.menu_ussd_status_id')
                ->join('ussd.menu_ussd_service as muse', 'muse.service_id', '=', 'mud.service_id')

                ->join('transactions as t', 't.id', '=', 'mudc.transaction_id')
                ->join('points_of_sale as pos', 't.atm_id', '=', 'pos.atm_id')
                ->join('atms as a', 'a.id', '=', 't.atm_id')
                ->join('branches as b', 'b.id', '=', 'pos.branch_id');

                $this->customize_filters($filters, $records_list);
                
                $records_list = $records_list->groupBy('muo.id', 'muo.description', 'mus.id', 'mudc.final_transaction_message')
                                             ->orderBy('muo.id', 'asc');

                $record_limit = $filters['record_limit'];
    
                if ($record_limit !== '') {
                    $records_list = $records_list->take(intval($record_limit));
                }

                $list = $records_list->get();

                //\Log::info('list:');
                //\Log::info($list);
                
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }



    /**
     * Obtine los totales por canal
     */
    function get_total_by_channels($filters)
    {
        $list = [];

        try {
            $records_list = \DB::table('ussd.menu_ussd_detail_client as mudc')
                ->select(
                    \DB::raw("
                        case 
                        when a.type = 'da' then 'App Billetaje' 
                        when a.type = 'ws' then 'Web Service' 
                        when a.type = 'at' then 'ATM' 
                        else 'Sin canal.' 
                        end as channel
                    "),

                    \DB::raw('count(mudc.id)'),
                    \DB::raw("array_to_string(array_agg(mudc.id), ', ') as list_of_ids")
                )
                ->join('ussd.menu_ussd_detail as mud', 'mud.id', '=', 'mudc.menu_ussd_detail_id')
                ->join('ussd.menu_ussd as mu', 'mu.id', '=', 'mud.menu_ussd_id')
                ->join('ussd.menu_ussd_operator as muo', 'muo.id', '=', 'mudc.menu_ussd_operator_id')
                ->join('ussd.menu_ussd_status as mus', 'mus.id', '=', 'mudc.menu_ussd_status_id')
                ->join('ussd.menu_ussd_service as muse', 'muse.service_id', '=', 'mud.service_id')

                ->join('transactions as t', 't.id', '=', 'mudc.transaction_id')
                ->join('points_of_sale as pos', 't.atm_id', '=', 'pos.atm_id')
                ->join('atms as a', 'a.id', '=', 't.atm_id')
                ->join('branches as b', 'b.id', '=', 'pos.branch_id');

                $this->customize_filters($filters, $records_list);
                
                $records_list = $records_list->groupBy('channel');
                //$records_list = $records_list->orderBy('pos.description');

                $record_limit = $filters['record_limit'];
    
                if ($record_limit !== '') {
                    $records_list = $records_list->take(intval($record_limit));
                }

                $list = $records_list->get();

                //\Log::info('list:');
                //\Log::info($list);
                
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }

        /**
     * Obtine los totales de los puntos de venta.
     */
    function get_total_by_points_of_sale($filters)
    {
        $list = [];

        try {
            $records_list = \DB::table('ussd.menu_ussd_detail_client as mudc')
                ->select(
                    'pos.description',
                    \DB::raw('count(mudc.id)'),
                    \DB::raw("array_to_string(array_agg(mudc.id), ', ') as list_of_ids")
                )
                ->join('ussd.menu_ussd_detail as mud', 'mud.id', '=', 'mudc.menu_ussd_detail_id')
                ->join('ussd.menu_ussd as mu', 'mu.id', '=', 'mud.menu_ussd_id')
                ->join('ussd.menu_ussd_operator as muo', 'muo.id', '=', 'mudc.menu_ussd_operator_id')
                ->join('ussd.menu_ussd_status as mus', 'mus.id', '=', 'mudc.menu_ussd_status_id')
                ->join('ussd.menu_ussd_service as muse', 'muse.service_id', '=', 'mud.service_id')

                ->join('transactions as t', 't.id', '=', 'mudc.transaction_id')
                ->join('points_of_sale as pos', 't.atm_id', '=', 'pos.atm_id')
                ->join('atms as a', 'a.id', '=', 't.atm_id')
                ->join('branches as b', 'b.id', '=', 'pos.branch_id');

                $this->customize_filters($filters, $records_list);
                
                $records_list = $records_list->groupBy('pos.description');
                //$records_list = $records_list->orderBy('pos.description');

                $record_limit = $filters['record_limit'];
    
                if ($record_limit !== '') {
                    $records_list = $records_list->take(intval($record_limit));
                }

                $list = $records_list->get();

                //\Log::info('list:');
                //\Log::info($list);
                
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }

    /**
     * Obtine el total por operador
     */
    function get_total_by_operator($filters)
    {
        $list = [];

        try {
            $records_list = \DB::table('ussd.menu_ussd_detail_client as mudc')
                ->select(
                    'muo.id',
                    'muo.description as operator',
                    \DB::raw('count(mudc.id)'),
                    \DB::raw("array_to_string(array_agg(mudc.id), ', ') as list_of_ids")
                )
                ->join('ussd.menu_ussd_detail as mud', 'mud.id', '=', 'mudc.menu_ussd_detail_id')
                ->join('ussd.menu_ussd as mu', 'mu.id', '=', 'mud.menu_ussd_id')
                ->join('ussd.menu_ussd_operator as muo', 'muo.id', '=', 'mudc.menu_ussd_operator_id')
                ->join('ussd.menu_ussd_status as mus', 'mus.id', '=', 'mudc.menu_ussd_status_id')
                ->join('ussd.menu_ussd_service as muse', 'muse.service_id', '=', 'mud.service_id')

                ->join('transactions as t', 't.id', '=', 'mudc.transaction_id')
                ->join('points_of_sale as pos', 't.atm_id', '=', 'pos.atm_id')
                ->join('atms as a', 'a.id', '=', 't.atm_id')
                ->join('branches as b', 'b.id', '=', 'pos.branch_id');

                $this->customize_filters($filters, $records_list);
                
                $records_list = $records_list->groupBy('muo.id', 'muo.description');
                $records_list = $records_list->orderBy('muo.id', 'asc');

                $record_limit = $filters['record_limit'];
    
                if ($record_limit !== '') {
                    $records_list = $records_list->take(intval($record_limit));
                }

                $list = $records_list->get();

                //\Log::info('list:');
                //\Log::info($list);
                
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }

    /**
     * Obtine los puntos de venta.
     */
    function get_points_of_sale()
    {
        $list = [];

        try {
            $list = \DB::table('points_of_sale')
                ->select(
                    'id',
                    'description'
                )
                ->get();
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }

    /**
     * Obtine los mensajes por operadora.
     */
    function get_final_transaction_messages($filters)
    {
        $list = [];

        try {
            $records_list = \DB::table('ussd.menu_ussd_detail_client as mudc')
                ->select(
                    \DB::raw("
                        '{\"id\": ' || muo.id || ', \"description\": \"' || mudc.final_transaction_message || '\"}' as id
                    "),
                    \DB::raw("
                        upper(muo.description) || ' ('  || mus.description || ') ' ||
                        case when mudc.final_transaction_message is null or mudc.final_transaction_message = '' 
                        then 'Sin mensaje de operadora.'
                        else mudc.final_transaction_message 
                        end as description
                    ")
                )
                ->join('ussd.menu_ussd_status as mus', 'mus.id', '=', 'mudc.menu_ussd_status_id')
                ->join('ussd.menu_ussd_operator as muo', 'muo.id', '=', 'mudc.menu_ussd_operator_id')

                ->join('transactions as t', 't.id', '=', 'mudc.transaction_id')
                ->join('points_of_sale as pos', 't.atm_id', '=', 'pos.atm_id')
                ->join('atms as a', 'a.id', '=', 't.atm_id')
                ->join('branches as b', 'b.id', '=', 'pos.branch_id')
                ->whereRaw("mudc.final_transaction_message not ilike '%Confirma la venta de%'")
                ->groupBy('muo.id', 'muo.description', 'mudc.final_transaction_message', 'mus.id')
                ->orderBy('muo.id', 'asc')
                ->orderBy('mus.id', 'asc');
                
                $list = $records_list->get();

                //\Log::info('list:');
                //\Log::info($list);
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }



    /**
     * Búsqueda inicial para la pantalla de trancciones de ussd.
     *
     * @return \Illuminate\Http\Response
     */
    public function ussd_transaction_report(Request $request)
    {
        ini_set('max_execution_time', 0);
        ini_set('client_max_body_size', '20M');
        ini_set('max_input_vars', 10000);
        ini_set('upload_max_filesize', '20M');
        ini_set('post_max_size', '20M');
        ini_set('memory_limit', '-1');
        set_time_limit(3600);

        $list = [];
        $totals = [];
        $data_to_excel = [];
        $days = 0;
        $total = 0;
        $totals_status = 0;

        try {

            //\Log::info('PARAMETROS:');
            //\Log::info($request->all());

            //Campo dinamico.
            $list_of_ids = '';

            if (isset($request['list_of_ids'])) {
                $list_of_ids = $request['list_of_ids'];
            }

            if (isset($request['timestamp'])) {
                $filters = [
                    'timestamp' => $request['timestamp'],
                    'phone_number' => $request['phone_number'],

                    'record_limit' => $request['record_limit'],
                    'status_id' => $request['status_id'],
                    'atm_id' => $request['atm_id'],
                    'branch_id' => $request['branch_id'],
                    'service_id' => $request['service_id'],
                    'operator_id' => $request['operator_id'],
                    'channel_id' => $request['channel_id'],
                    'option_id' => $request['option_id'],
                    'pos_id' => $request['pos_id'],
                    'final_transaction_message_id' => $request['final_transaction_message_id'],
                    'send' => $request['send'],
                    'transaction_id' => $request['transaction_id'],
                    'transaction_status_id' => $request['transaction_status_id'],
                    'historic' => $request['historic'],
                    'list_of_ids' => $list_of_ids,
                ];
            } else {
                $time_init = '00:00:00';
                $time_end = '23:59:59';
                $from = date("d/m/Y");
                $to = date("d/m/Y");
                $timestamp = "$from $time_init - $to $time_end";

                $filters = [
                    'timestamp' => $timestamp,
                    'phone_number' => '',

                    'record_limit' => '',
                    'status_id' => '',
                    'atm_id' => '',
                    'branch_id' => '',
                    'service_id' => '',
                    'operator_id' => '',
                    'channel_id' => '',
                    'option_id' => '',
                    'pos_id' => '',
                    'final_transaction_message_id' => '',
                    'send' => '',
                    'transaction_id' => '',
                    'transaction_status_id' => '',
                    'historic' => '',
                    'list_of_ids' => ''
                ];
            }

            $transactions = $this->get_transactions($filters);
            $messages_by_operator = $this->get_messages_by_operator($filters);
            $total_by_channels = $this->get_total_by_channels($filters);

            $totals_status = $this->get_total_by_state($filters);
            $totals = $this->get_total_transactions($filters);
            $days = $this->get_days($filters);

            $operators = $this->get_operators();
            $services = $this->get_services();
            $states = $this->get_states();
            $branches = $this->get_branches();
            $options = $this->get_options();
            $atms = $this->get_atms();
            $channels = $this->get_channels();
            $record_limits = $this->get_record_limits();
            $reasons = $this->get_reason();
            $recharge_types = $this->get_recharge_type();
            $points_of_sale = $this->get_points_of_sale();
            $sends = $this->get_sends();
            $transaction_status = $this->get_transaction_status(); 
            $historics = $this->get_historics();
            
            $menu_ussd_detail_list = $this->get_menu_ussd_detail();

            $total_by_points_of_sale = $this->get_total_by_points_of_sale($filters);
            $total_by_operator = $this->get_total_by_operator($filters);
            $final_transaction_messages = $this->get_final_transaction_messages($filters);

            if (isset($request['button_name'])) {
                if ($request['button_name'] == 'generate_x') {
                    if (count($transactions) > 0) {
                        foreach ($transactions as $item) {
                            $item->amount = str_replace('.', '', $item->amount);

                            $item = [
                                $item->transaction_id,
                                $item->option,
                                $item->menu_ussd_status,
                                $item->phone_number,
                                $item->amount,
                                $item->atm,
                                $item->created_at
                            ];

                            array_push($data_to_excel, $item);
                        }
                    }

                    if (count($data_to_excel) > 0) {
                        $filename = 'ussd_transaction_' . time();

                        $columnas = array(
                            'ID TRANSACCIÓN', 'TIPO', 'ESTADO', 'CONTACTO', 'MONTO', 'TERMINAL', 'FECHA Y HORA'
                        );

                        $excel = new ExcelExport($data_to_excel,$columnas);
                        return Excel::download($excel, $filename . '.xls')->send();
                       

                        // Excel::create($filename, function ($excel) use ($data_to_excel) {
                        //     $excel->sheet('Registros', function ($sheet) use ($data_to_excel) {
                        //         $sheet->rows($data_to_excel, false);
                        //         $sheet->prependRow(array(
                        //             'ID TRANSACCIÓN', 'TIPO', 'ESTADO', 'CONTACTO', 'MONTO', 'TERMINAL', 'FECHA Y HORA'
                        //         ));
                        //     });
                        // })->export('xls');
                    } else {
                        $message = 'No hay registros según los filtros seleccionados.';
                    }
                }
            }

            $message = 'Consulta exitosa.';
            $message_type = 'message';
        } catch (\Exception $e) {
            $message = 'Error al crear datos de documento.';
            $message_type = 'error_message';

            $error_detail = [];
            $error_detail['exception'] = $e->getMessage();
            $error_detail['file'] = $e->getFile();
            $error_detail['class'] = __CLASS__;
            $error_detail['function'] = __FUNCTION__;
            $error_detail['line'] = $e->getLine();
            $error_detail['status'] = $e->getCode();

            \Log::error($message);
            \Log::error('Detalles del error:');
            \Log::error($error_detail);
        }

        $data = [
            'filters' => json_encode($filters),
            'totals' => $totals,
            'days' => $days,
            'total' => $total,
            'lists' => [
                'transactions' => $transactions,
                'totals_status' => $totals_status,
                'messages_by_operator' => $messages_by_operator,
                'total_by_points_of_sale' => $total_by_points_of_sale,
                'total_by_operator' => $total_by_operator,
                'total_by_channels' => $total_by_channels,
                'operators' => json_encode($operators),
                'services' => json_encode($services),
                'states' => json_encode($states),
                'branches' => json_encode($branches),
                'options' => json_encode($options),
                'atms' => json_encode($atms),
                'channels' => json_encode($channels),
                'record_limits' => json_encode($record_limits),
                'reasons' => json_encode($reasons),
                'recharge_types' => json_encode($recharge_types),
                'points_of_sale' => json_encode($points_of_sale),
                'final_transaction_messages' => json_encode($final_transaction_messages),
                'sends' => json_encode($sends),
                'transaction_status' => json_encode($transaction_status),
                'historics' => json_encode($historics),
                'menu_ussd_detail_list' => json_encode($menu_ussd_detail_list)
            ]
        ];

        //\Log::info('lista:');
        //\Log::info($data);
        \Session::flash($message_type, $message);
        return view('ussd.ussd_transaction_report', compact('data'));
    }

    /**
     * Edita una transacción.
     */
    function ussd_transaction_edit(Request $request)
    {
        $message = 'Recarga no actualizada.';
        $error = false;
        $error_detail = null;

        try {
            $id = $request['id'];
            $replacement_number = $request['replacement_number'];
            $reason = $request['reason'];
            $recharge_type = $request['recharge_type'];
            $commentary = $request['commentary'];
            $ids = $request['ids'];

            //\Log::info("IDS: ");
            //\Log::info($ids);
            //die();

            $record = \DB::table('ussd.menu_ussd_detail_client')->where('id', $id)->get();

            if ($record > 0) {
                $item = $record[0];
                $menu_ussd_status_id = $item->menu_ussd_status_id;
                $amount = $item->amount;
                $menu_ussd_detail_id = $item->menu_ussd_detail_id;
                $phone_number = $item->phone_number;
                $command = $item->command;

                if ($menu_ussd_status_id == 3 or $menu_ussd_status_id == 4) {

                    \DB::beginTransaction();

                    $menu_ussd_audit_id = \DB::table('ussd.menu_ussd_audit')->insertGetId([
                        'user_id' => $user_id,
                        'created_at' => Carbon::now()
                    ]);

                    \DB::table('ussd.menu_ussd_detail_client_audit')->insert([
                        'menu_ussd_detail_client_id_old' => $item->id,
                        'phone_number_old' => $item->phone_number,
                        'amount_old' => $item->amount,
                        'command_old' => $item->command,
                        'messages_old' => $item->messages,
                        'final_transaction_message_old' => $item->final_transaction_message,
                        'wrong_run_counter_old' => $item->wrong_run_counter,
                        'menu_ussd_operator_id_old' => $item->menu_ussd_operator_id,
                        'menu_ussd_detail_id_old' => $item->menu_ussd_detail_id,
                        'menu_ussd_status_id_old' => $item->menu_ussd_status_id,
                        'transaction_id_old' => $item->transaction_id,
                        'created_at_old' => $item->created_at,
                        'updated_at_old' => $item->updated_at,
                        'menu_ussd_audit_id' => $menu_ussd_audit_id,
                        'created_at' => Carbon::now()
                    ]);

                    \DB::table('ussd.menu_ussd_detail_client_change')->insert([
                        'replacement_number' => $replacement_number,
                        'commentary' => $commentary,
                        'user_id' => $user_id,
                        'menu_ussd_detail_client_reason_id' => $reason,
                        'menu_ussd_detail_client_id' => $id,
                        'created_at' => Carbon::now(),
                        'menu_ussd_recharge_type_id' => $recharge_type,
                        'menu_ussd_relaunched_type_id' => 1
                    ]);

                    $command = str_replace($phone_number, '[phone_number]', $command);
                    $command = str_replace('[phone_number]', $replacement_number, $command);

                    \DB::table('ussd.menu_ussd_detail_client')
                        ->where('id', $id)
                        ->update([
                            'phone_number' => $replacement_number,
                            'amount' => $amount,
                            'command' => $command,
                            'menu_ussd_detail_id' => $menu_ussd_detail_id,
                            'menu_ussd_status_id' => 5, # Estado relanzado
                            'updated_at' => Carbon::now()
                        ]);

                    \DB::commit();

                    if ($recharge_type == 2) { # Tipo de recarga normal.
                        /*$menu_ussd = \DB::table('ussd.menu_ussd')
                            ->select('id')
                            ->where([
                                'status' => true
                            ])->get();

                        if (count($menu_ussd) > 0) {
                            $menu_ussd_id = $menu_ussd[0]->id;

                            $menu_ussd_detail = \DB::table('ussd.menu_ussd_detail')
                                ->select('id', \DB::raw("replace(command, '[phone_number]', '$replacement_number') as command"))
                                ->where([
                                    'menu_ussd_id' => $menu_ussd_id,
                                    'status' => true,
                                    'service_id' => 16,
                                    'menu_ussd_type_id' => 2,
                                    'amount' => $amount
                                ])->get();

                            if (count($menu_ussd_detail) > 0) {
                                $menu_ussd_detail_id = $menu_ussd_detail[0]->id;
                                $command = $menu_ussd_detail[0]->command;
                            } else {
                                $amount_aux = 0;

                                if ($amount == 4000) {
                                    $amount = 2000;
                                    $amount_aux = 2000;
                                } else if ($amount == 7000) {
                                    $amount = 5000;
                                    $amount_aux = 2000;
                                } else if ($amount == 12000) {
                                    $amount = 10000;
                                    $amount_aux = 2000;
                                }

                                $menu_ussd_detail = \DB::table('ussd.menu_ussd_detail')
                                    ->select('id', \DB::raw("replace(command, '[phone_number]', '$replacement_number') as command"))
                                    ->where([
                                        'menu_ussd_id' => $menu_ussd_id,
                                        'status' => true,
                                        'service_id' => 16,
                                        'menu_ussd_type_id' => 2,
                                    ])
                                    ->whereRaw("amount = any(array[$amount, $amount_aux])")
                                    ->orderBy('amount', 'desc')
                                    ->get();

                                if (count($menu_ussd_detail) > 0) {
                                    $menu_ussd_detail_id = $menu_ussd_detail[0]->id;
                                    $command = $menu_ussd_detail[0]->command;

                                    $menu_ussd_detail_id_aux = $menu_ussd_detail[1]->id;
                                    $command_aux = $menu_ussd_detail[1]->command;

                                    \DB::table('ussd.menu_ussd_detail_client')->insert([
                                        'phone_number' => $replacement_number,
                                        'amount' => $amount_aux,
                                        'command' => $command_aux,
                                        'messages' => null,
                                        'final_transaction_message' => null,
                                        'wrong_run_counter' => 0,
                                        'menu_ussd_operator_id' => $item->menu_ussd_operator_id,
                                        'menu_ussd_detail_id' => $menu_ussd_detail_id_aux,
                                        'menu_ussd_status_id' => 5,
                                        'transaction_id' => $item->transaction_id,
                                        'created_at' => Carbon::now(),
                                        'updated_at' => null
                                    ]);
                                }
                            }
                        }*/
                    }

                    $message = "Recarga N° $id actualizada correctamente.";
                } else {
                    $message = 'No se puede modificar este registro.';
                }
            } else {
                $message = 'No existe el registro ussd.';
            }
        } catch (\Exception $e) {
            $error_detail = $this->custom_error($e, __FUNCTION__);
            $message = 'Error al modificar registro.';
            $error = true;
        }

        if ($error == true) {
            \Log::error($message, $error_detail);
        } else {
            \Log::info($message);
        }

        $data = [
            'error' => $error,
            'message' => $message,
            'error_detail' => $error_detail
        ];

        return $data;
    }


    /**
     * Relanza todas las transacciones fallidas.
     *
     * @return \Illuminate\Http\Response
     */
    public function ussd_transaction_relaunch(Request $request)
    {
        $message = 'Transacciones no relanzadas.';
        $error = false;
        $error_detail = null;
        $count = 0;

        try {
            $list = $this->get_transactions($request);

            for ($i = 0; $i < count($list); $i++) {

                $item = $list[$i];

                if ($item['status_id'] == 3) {
                    $items = [
                        'id' => $item['operation'],
                        'replacement_number' => $item['phone_number'],
                        'reason' => 4,  # Error del sistema
                        'recharge_type' => 1, # Carga sin cambios
                        'commentary' => null,
                    ];

                    $data = $this->ussd_transaction_edit($items, $this->user->id);
                    $data_error = $data['error'];

                    if ($data_error == false) {
                        $count++;
                    }
                }
            }

            if ($count > 0) {
                if ($count == 1) {
                    $message = "$count transacción relanzada.";
                } else {
                    $message = "$count transacciones relanzadas.";
                }
            }
        } catch (\Exception $e) {
            $error_detail = $this->custom_error($e, __FUNCTION__);
            $message = 'Error al relanzar transacciones.';
            $error = true;
        }

        if ($error == true) {
            \Log::error($message);
            \Log::error($error_detail);
        } else {
            \Log::info($message);
        }

        $data = [
            'error' => $error,
            'message' => $message,
            'count' => $count
        ];

        return $data;
    }
}
