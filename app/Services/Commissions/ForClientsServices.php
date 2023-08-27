<?php

/**
 * User: avisconte
 * Date: 11/04/2022
 * Time: 09:00 am
 */

namespace App\Services\Commissions;

use Excel;

class ForClientsServices
{
    /**
     * Función inicial
     */
    public function index($request)
    {
        ini_set('max_execution_time', 0);
        ini_set('client_max_body_size', '20M');
        ini_set('max_input_vars', 10000);
        ini_set('upload_max_filesize', '20M');
        ini_set('post_max_size', '20M');
        ini_set('memory_limit', '-1');
        set_time_limit(3600);


        /**
         * -----------------------------------------------------------------------------------------------------------------
         */

        $message = '';
        $total = 0;
        $atms = [];
        $services = [];
        $services_providers_sources = [];
        $records = [];
        $totals_by_type_of_commission = [];
        $total_by_providers = [];
        $get_info = true;
        $atms_ids_per_user = null;
        $error_detail = [];

        /**
         * -----------------------------------------------------------------------------------------------------------------
         */

        $connection = \DB::connection('eglobalt_pro');

        $this->user = \Sentinel::getUser();

        if (!$this->user->hasAccess('commissions_for_clients')) {
            \Log::error(
                'No tienes permiso para acceder a esta pantalla',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );

            $data = [
                'mode' => 'message',
                'type' => 'error',
                'title' => 'Sin permiso',
                'explanation' => 'El usuario necesita tener el permiso asignado para acceder a esta pantalla.'
            ];

            return view('messages.index', compact('data'));
        }

        /**
         * -----------------------------------------------------------------------------------------------------------------
         */

        $roles = \DB::table('roles as r')
            ->select(
                \DB::raw("trim(array_to_string(array_agg(r.slug), ',')) as roles"),
                \DB::raw("trim(array_to_string(array_agg(r.name), ',')) as roles_description")
            )
            ->join('role_users as ru', 'r.id', '=', 'ru.role_id')
            ->join('users as u', 'u.id', '=', 'ru.user_id')
            ->where('u.id', $this->user->id)
            ->get();

        if (count($roles) > 0) {
            $item = $roles[0];
            $roles = $item->roles;
            $roles_description = $item->roles_description;
        } else {
            $roles = '';
            $roles_description = '';
        }

        if ($roles == '') {
            $data = [
                'mode' => 'message',
                'type' => 'error',
                'title' => 'Sin Roles',
                'explanation' => 'El usuario no tiene Roles.'
            ];

            return view('messages.index', compact('data'));
        }

        /**
         * -----------------------------------------------------------------------------------------------------------------
         */

        $super_user = str_contains($roles, 'superuser');
        $supervisor_miniterminal = str_contains($roles, 'supervisor_miniterminal');
        $mini_terminal = str_contains($roles, 'mini_terminal');

        if ($super_user == false) {

            if ($supervisor_miniterminal == false and $mini_terminal == false) {
                $data = [
                    'mode' => 'message',
                    'type' => 'error',
                    'title' => 'Sin asignación de Roles',
                    'explanation' => 'El usuario no tiene asigando el rol: Supervisor Miniterminal o Mini Terminal. Debe tener asignado uno de los dos.'
                ];

                return view('messages.index', compact('data'));
            }

            /**
             * -----------------------------------------------------------------------------------------------------------------
             */

            $atms_per_users = \DB::table('atms_per_users as apu')
                ->select(
                    \DB::raw("array_to_string(array_agg(apu.atm_id), ',') as atms_ids_per_user")
                );

            if ($supervisor_miniterminal) {
                $atms_per_users = $atms_per_users->where('apu.user_supervisor_id', $this->user->id);
            } else if ($mini_terminal) {
                $atms_per_users = $atms_per_users->where('apu.user_id', $this->user->id);
            }

            $atms_per_users = $atms_per_users->get();

            \Log::error("atms_per_users:", [$atms_per_users]);

            if (count($atms_per_users) > 0) {
                $atms_ids_per_user = $atms_per_users[0]->atms_ids_per_user;
            }

            if ($atms_ids_per_user == null) {
                $data = [
                    'mode' => 'message',
                    'type' => 'error',
                    'title' => 'ATMS no asignados',
                    'explanation' => 'El usuario no tiene ATMS asignados.'
                ];

                return view('messages.index', compact('data'));
            }
        }



        /**
         * -----------------------------------------------------------------------------------------------------------------
         */

        try {

            /**
             * Valores iniciales de los campos de búsqueda.
             */
            $from_view = date('d/m/Y') . ' 00:00:00';
            $to_view = date('d/m/Y') . ' 23:59:59';
            $timestamp = "$from_view - $to_view";
            $amount = null;

            $equal_amount = '';
            $lesser_amount = '';
            $higher_amount = '';

            $atm_id = [];

            $service_id = [];

            if (isset($request['button_name'])) {

                if ($request['button_name'] == 'search') {

                    \Log::info('request de comisiones para el cliente:', [$request->all()]);

                    /** 
                        select t.id, t.amount, t.created_at, t.service_source_id, t.service_id, t.atm_id, t.commission_net_level_1, 299 as user_id 
                        from transactions t 
                        join servicios_x_marca sxm on sxm.service_source_id = t.service_source_id and sxm.service_id = t.service_id 
                        where atm_id in (45)
                        and t.created_at between '2022-09-01 00:00:00' and '2022-09-30 23:59:59'
                     */
                    $records = $connection
                        ->table('transactions as t')
                        ->select(
                            't.atm_id',
                            'a.name as atm_description',
                            't.service_source_id',
                            't.service_id',
                            \DB::raw("(m.descripcion || ' - ' || sxm.descripcion) as service"),
                            't.id as transaction_id',
                            \DB::raw("to_char(t.created_at, 'DD/MM/YYYY HH24:MI:SS') as created_at"),
                            \DB::raw("abs(coalesce(t.amount, 0))::bigint as amount"),
                            \DB::raw("coalesce(t.commission_net_level_1, 0)::integer as commission_net_level_1"),
                            \DB::raw("trim(replace(to_char(coalesce(t.amount, 0), '999G999G999G999'), ',', '.')) as amount_view"),
                            \DB::raw("trim(replace(to_char(coalesce(t.commission_net_level_1, 0), '999G999G999G999'), ',', '.')) as commission_net_level_1_view")
                        )
                        ->join('servicios_x_marca as sxm', function ($join) {
                            $join->on('sxm.service_source_id', '=', 't.service_source_id');
                            $join->on('sxm.service_id', '=', 't.service_id');
                        })
                        ->join('marcas as m', 'm.id', '=', 'sxm.marca_id')
                        ->join('atms as a', 'a.id', '=', 't.atm_id');

                    if (isset($request['timestamp'])) {

                        $timestamp = $request['timestamp'];
                        $aux = explode(' - ', str_replace('/', '-', $timestamp));
                        $from = date('Y-m-d H:i:s', strtotime($aux[0]));
                        $to = date('Y-m-d H:i:s', strtotime($aux[1]));
                        $records = $records->whereRaw("t.created_at between '{$from}' and '{$to}'");

                        $from_view = date('d/m/Y', strtotime($aux[0])) . ' 00:00:00';
                        $to_view = date('d/m/Y', strtotime($aux[1])) . ' 23:59:59';
                        $timestamp = "$from_view - $to_view";
                    } else {
                        // Si no hay filtro de fecha se trae lo de hoy.
                        $from = date('Y-m-d H:i:s');
                        $to = date('Y-m-d H:i:s');
                        $records = $records->whereRaw("t.created_at between '{$from}' and '{$to}'");
                    }

                    if (isset($request['amount'])) {
                        if ($request['amount'] !== '') {
                            $amount = $request['amount'];

                            if (isset($request['equal_amount'])) {
                                $equal_amount = $request['equal_amount'];
                            }

                            if (isset($request['lesser_amount'])) {
                                $lesser_amount = $request['lesser_amount'];
                            }

                            if (isset($request['higher_amount'])) {
                                $higher_amount = $request['higher_amount'];
                            }

                            $where_raw_array = [];

                            if ($equal_amount !== '') {
                                $where_raw_array[] = "t.amount = $amount";
                            }

                            if ($lesser_amount !== '') {
                                $where_raw_array[] = "t.amount < $amount";
                            }

                            if ($higher_amount !== '') {
                                $where_raw_array[] = "t.amount > $amount";
                            }

                            $where_raw_string = implode(' or ', $where_raw_array);

                            $records = $records->whereRaw($where_raw_string);
                        }
                    }

                    \Log::info('atm_id:', [$request['atm_id']]);
                    \Log::info('service_id:', [$request['service_id']]);

                    if (isset($request['atm_id'])) {
                        if ($request['atm_id'][0] !== '' and $request['atm_id'][0] !== 'Todos') {
                            $atm_id = $request['atm_id'];
                            $atms_ids = implode(',', $atm_id);
                            $records = $records->whereRaw("a.id in ($atms_ids)");
                        }
                    }

                    if (isset($request['service_id'])) {
                        if ($request['service_id'][0] !== '' and $request['service_id'][0] !== 'Todos') {
                            $service_id = $request['service_id'];
                            $service_ids = "'" . implode("','", $service_id) . "'";

                            $records = $records->whereRaw("(sxm.service_source_id || '_' || sxm.service_id) in ($service_ids)");
                        }
                    }

                    \Log::info('Query de comisiones para el cliente:' . $records->toSql());

                    if ($atms_ids_per_user !== null) {
                        $records = $records->whereRaw("a.id in ($atms_ids_per_user)");
                    }

                    $records = $records
                        ->orderBy('t.id', 'ASC')
                        ->get();

                    $records = json_decode(json_encode($records), true);

                    if (count($records) <= 0) {
                        $message = 'La consulta no retornó registros.';

                        $data = [
                            'mode' => 'alert',
                            'type' => 'info',
                            'title' => 'Consulta sin registros',
                            'explanation' => 'La consulta no retornó ningún registro.'
                        ];

                        return view('messages.index', compact('data'));
                    }
                }
            }

            //Traer solo cuando hay búsqueda no cuando genera el excel.
            if ($get_info) {

                /**
                 * Trae los atms relacionados al usuario
                 */
                $atms = $connection
                    ->table('atms as a')
                    ->select(
                        'a.id',
                        'a.name as description'
                    );

                if ($atms_ids_per_user !== null) {
                    $atms = $atms->whereRaw("a.id in ($atms_ids_per_user)");
                }

                $atms = $atms
                    ->orderBy('id', 'ASC')
                    ->get();

                /**
                 * Trae los proveedores ordenado por descripción
                 */
                $services = $connection
                    ->table('servicios_x_marca as sxm')
                    ->select(
                        \DB::raw("(sxm.service_source_id || '_' || sxm.service_id) as id"),
                        \DB::raw("(m.descripcion || ' - ' || sxm.descripcion) as description")
                    )
                    ->join('marcas as m', 'm.id', '=', 'sxm.marca_id')
                    ->get();
            }
        } catch (\Exception $e) {
            $error_detail = [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];

            \Log::error("Error, Detalles: " . json_encode($error_detail));
        }

        $data = [
            'message' => $message,
            'lists' => [
                'records' => $records,
                'json' => json_encode($records, JSON_UNESCAPED_UNICODE),
                'atms' => json_encode($atms, JSON_UNESCAPED_UNICODE),
                'services' => json_encode($services, JSON_UNESCAPED_UNICODE),
                'services_providers_sources' => json_encode($services_providers_sources, JSON_UNESCAPED_UNICODE)
            ],
            'inputs' => [
                'timestamp' => $timestamp,
                'amount' => $amount,
                'equal_amount' => $equal_amount,
                'lesser_amount' => $lesser_amount,
                'higher_amount' => $higher_amount,
                'atm_id' => json_encode($atm_id, JSON_UNESCAPED_UNICODE),
                'service_id' => json_encode($service_id, JSON_UNESCAPED_UNICODE),
                'services_providers_sources_id' => isset($request['services_providers_sources_id']) ? $request['services_providers_sources_id'] : 'Todos',
                'service_by_brand_id' => isset($request['service_by_brand_id']) ? $request['service_by_brand_id'] : 'Todos',
            ],
            'error_detail' => $error_detail
        ];

        return view('commissions.for_clients', compact('data'));
    }

    /**
     * Obtener servicios por marca 
     */
    public function get_services_by_brand($request)
    {
        try {

            $connection = \DB::connection('eglobalt_pro');

            /**
             * Trae los servicios por marca filtrado por proveedor 
             */
            $services_by_brand = $connection
                ->table('servicios_x_marca as sxm')
                ->select(
                    'sxm.service_id as id',
                    \DB::raw("(sxm.service_id || '# ' || m.descripcion || ' - ' || sxm.descripcion) as description")
                )
                ->join('services_providers_sources as sps', function ($join) {
                    $join->on('sps.id', '=', \DB::raw("(case when sxm.service_source_id = 9 then 0 else sxm.service_source_id end)"));
                })
                ->join('marcas as m', 'm.id', '=', 'sxm.marca_id');


            if (isset($request['services_providers_sources_id'])) {
                if ($request['services_providers_sources_id'] !== '' and $request['services_providers_sources_id'] !== 'Todos') {
                    $services_by_brand = $services_by_brand
                        ->where('sps.id', $request['services_providers_sources_id'])
                        ->orderBy('sxm.service_id', 'ASC')
                        ->get();
                } else {
                    $services_by_brand = [];
                }
            } else {
                $services_by_brand = [];
            }
        } catch (\Exception $e) {
            $error_detail = [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];

            \Log::error("Error, Detalles: " . json_encode($error_detail));
        }

        return $services_by_brand;
    }
}
