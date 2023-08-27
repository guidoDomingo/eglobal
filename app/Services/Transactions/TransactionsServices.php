<?php

/**
 * User: avisconte
 * Date: 28/06/2022
 * Time: 15:51
 */

namespace App\Services\Transactions;

use App\Exports\ExcelExport;
use Excel;
use Carbon\Carbon;

class TransactionsServices
{

    /**
     * Inicializar valores para usar en todas las funciones
     */
    public function __construct()
    {

        ini_set('max_execution_time', 0);
        ini_set('client_max_body_size', '20M');
        ini_set('max_input_vars', 10000);
        ini_set('upload_max_filesize', '20M');
        ini_set('post_max_size', '20M');
        ini_set('memory_limit', '-1');
        set_time_limit(3600);

        $this->connection = \DB::connection('eglobalt_pro');
        $this->user = \Sentinel::getUser();
    }

    /**
     * Lista de transacciones a devolver.
     */
    public function index($request)
    {
        if (!$this->user->hasAccess('cms_transactions_report')) {
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

        $user_id = $this->user->id;

        $message = '';
        $total = 0;
        $atms = [];
        $transactions_status = [];
        $services_providers_sources = [];
        $transactions = [];
        $totals_by_type_of_commission = [];
        $total_by_providers = [];
        $get_info = true;

        try {

            $connection = $this->connection;

            if (isset($request['button_name'])) {

                if ($request['button_name'] == 'search') {

                    /**
                     * Trae el detalle de pago ordenado por proveedor, terminal, servicio y comisión 
                     */
                    $transactions = \DB::table('transactions as t')
                        ->select(
                            \DB::raw('distinct t.id as transaction_id'),
                            \DB::raw("(m.descripcion || ' - ' || sxm.descripcion) as service"),
                            \DB::raw("trim(replace(to_char(t.amount, '999G999G999G999'), ',', '.')) as amount"),
                            \DB::raw("to_char(t.created_at, 'DD/MM/YYYY HH24:MI:SS') as created_at"),
                            't.status',
                            't.status_description',
                            't.service_source_id',
                            't.service_id',
                            't.owner_id'
                        )
                        ->join('services_providers_sources as sps', 'sps.id', '=', 't.service_source_id')
                        ->join('servicios_x_marca as sxm', function ($join) {
                            $join->on('t.service_source_id', '=', 'sxm.service_source_id');
                            $join->on('t.service_id', '=', 'sxm.service_id');
                        })
                        ->join('marcas as m', 'm.id', '=', 'sxm.marca_id')
                        ->whereRaw("t.transaction_type in (1,7,12)");

                    if (isset($request['created_at'])) {

                        $created_at = $request['created_at'];
                        $aux = explode(' - ', str_replace('/', '-', $created_at));
                        $from = date('Y-m-d H:i:s', strtotime($aux[0]));
                        $to = date('Y-m-d H:i:s', strtotime($aux[1]));
                        $transactions = $transactions->whereRaw("t.created_at between '{$from}' and '{$to}'");

                        $from_view = date('d/m/Y', strtotime($aux[0])) . ' 00:00:00';
                        $to_view = date('d/m/Y', strtotime($aux[1])) . ' 23:59:59';
                        $request['created_at'] = "$from_view - $to_view";
                    } else {
                        // Si no hay filtro de fecha se trae lo de hoy.
                        $from = date('Y-m-d H:i:s');
                        $to = date('Y-m-d H:i:s');
                        $transactions = $transactions->whereRaw("t.created_at between '{$from}' and '{$to}'");
                    }

                    if (isset($request['transaction_id'])) {
                        if ($request['transaction_id'] !== '') {
                            $transactions = $transactions->where('t.id', $request['transaction_id']);
                        }
                    }

                    if (isset($request['amount'])) {
                        if ($request['amount'] !== '') {
                            $transactions = $transactions->where('t.amount', $request['amount']);
                        }
                    }

                    if (isset($request['transaction_status_id'])) {
                        if ($request['transaction_status_id'] !== '' and $request['transaction_status_id'] !== 'Todos') {
                            $transactions = $transactions->where('t.status', $request['transaction_status_id']);
                        }
                    }

                    if (isset($request['service_source_id'])) {
                        if ($request['service_source_id'] !== '' and $request['service_source_id'] !== 'Todos') {
                            $transactions = $transactions->where('t.service_source_id', $request['service_source_id']);
                        }
                    }

                    if (isset($request['service_id'])) {
                        if ($request['service_id'] !== '' and $request['service_id'] !== 'Todos') {
                            $transactions = $transactions->where('t.service_id', $request['service_id']);
                        }
                    }

                    $query = $transactions->toSql();

                    \Log::info("QUERY: \n$query");


                    $transactions = $transactions
                        ->orderBy('t.id', 'DESC')
                        ->take(100)
                        ->get();

                    //$transactions = json_decode(json_encode($transactions), true);

                    if (count($transactions) <= 0) {
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

                $transactions_status = $connection
                    ->table('transactions_status as ts')
                    ->select(
                        'ts.name as id',
                        \DB::raw("(ts.name || ' (' || ts.description || ')') as description")
                    )
                    ->get();

                /**
                 * Trae los proveedores ordenado por descripción
                 */
                $services_providers_sources = $connection
                    ->table('services_providers_sources')
                    ->select(
                        'id',
                        \DB::raw("(id || '# ' || description) as description")
                    )
                    ->whereRaw('id = any(array[0, 1, 4, 7, 8, 9, 10])')
                    ->orderBy('id', 'ASC')
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
                'transactions' => $transactions,
                'json' => json_encode($transactions, JSON_UNESCAPED_UNICODE),
                'transaction_status' => json_encode($transactions_status, JSON_UNESCAPED_UNICODE),
                'services_providers_sources' => json_encode($services_providers_sources, JSON_UNESCAPED_UNICODE)
            ],
            'inputs' => [
                'created_at' => isset($request['created_at']) ? $request['created_at'] : null,
                'transaction_id' => isset($request['transaction_id']) ? $request['transaction_id'] : null,
                'amount' => isset($request['amount']) ? $request['amount'] : null,
                'transaction_status_id' => isset($request['transaction_status_id']) ? $request['transaction_status_id'] : 'Todos',
                'service_source_id' => isset($request['service_source_id']) ? $request['service_source_id'] : 'Todos',
                'service_id' => isset($request['service_id']) ? $request['service_id'] : 'Todos',
                'user_id' => $user_id,
            ]
        ];

        return view('transactions.index', compact('data'));
    }

    /**
     * Lista de transacciones devueltas.
     */
    public function index_devolutions($request)
    {

        if (!$this->user->hasAccess('cms_transactions_report_devolution')) {
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

        $user_id = $this->user->id;

        $message = '';
        $atms = [];
        $transactions_status = [];
        $services_providers_sources = [];
        $devolution_status = [];
        $users = [];
        $transactions = [];
        $get_info = true;

        try {

            $connection = $this->connection;

            if (isset($request['button_name'])) {

                if ($request['button_name'] == 'search') {

                    /**
                     * Trae el detalle de pago ordenado por proveedor, terminal, servicio y comisión 
                     */
                    $transactions = \DB::table('transactions as t')
                        ->select(

                            // Datos de transacción principal
                            'td.transaction_id as transaction_id',
                            't2.amount as amount_main',
                            \DB::raw("trim(replace(to_char(coalesce(t2.amount, 0), '999G999G999G999'), ',', '.')) as amount_main_view"),
                            't2.status as status_main',
                            't2.status_description as status_description_main',
                            'a.id as atm_id',
                            'a.name as atm_description',

                            'td.transaction_devolution_id as transaction_devolution_id',

                            \DB::raw("trim(replace(to_char(coalesce(td.transaction_id, 0), '999G999G999G999'), ',', '.')) as transaction_id_view"),
                            \DB::raw("trim(replace(to_char(coalesce(td.transaction_devolution_id, 0), '999G999G999G999'), ',', '.')) as transaction_devolution_id_view"),

                            'sps.id as provider_id',
                            'sps.description as provider',

                            \DB::raw("(m.descripcion || ' - ' || sxm.descripcion) as service"),

                            \DB::raw("trim(replace(to_char(coalesce(t.amount, 0), '999G999G999G999'), ',', '.')) as amount_view"),

                            \DB::raw("to_char(td.created_at, 'DD/MM/YYYY HH24:MI:SS') as created_at"),

                            't.status',
                            't.status_description',

                            'td.devolution_amount',

                            'dr.id as devolution_reason_id',
                            'dt.id as devolution_type_id',
                            'ds.id as devolution_status_id',
                            'ar.id as ajustement_reason_id',

                            'dr.description as devolution_reason',
                            'dt.description as devolution_type',
                            'ds.description as devolution_status',

                            \DB::raw("(case when td.ajustement = true then 'Con Ajuste' else 'Sin Ajuste' end) as ajustement"),
                            \DB::raw("coalesce(ar.description, 'Sin motivo') as ajustement_reason"),
                            \DB::raw("coalesce(td.ajustement_amount, 0) as ajustement_amount"),
                            \DB::raw("coalesce(td.ajustement_percentage, 0) as ajustement_percentage"),

                            'u.id as user_id',
                            'u.description as user_description',
                            'td.comment',

                            \DB::raw("
                                coalesce((
                                    json_agg(
                                        json_build_object(
                                            'transaction_devolution_id', td.transaction_devolution_id,
                                            'transaction_id_old', atd.transaction_id_old,
                                            'transaction_id_new', atd.transaction_id_new,
                                            'income_id', i.id,
                                            'ondanet_code', i.ondanet_code,
                                            'ondanet_destination_operation_id', (case when i.destination_operation_id = '0' then 'Pendiente' else i.destination_operation_id end),
                                            'ondanet_response', (case when i.response is null or i.response = '' then 'Sin respuesta' else i.response end),
                                            'ondanet_updated_at', coalesce(to_char(i.updated_at, 'DD/MM/YYYY HH24:MI:SS.MS'), 'Sin fecha y hora'),
                                            'ondanet_request_string', coalesce(i.request, 'Sin cadena disponible.')
                                        ) order by (i.id) asc
                                    )
                                ), '[]'::json) as ondanet_detail
                            "),

                            \DB::raw("(case when atd.transaction_devolution_id is null then false else true end) as audit"), // Para saber si tiene auditoría 

                            \DB::raw("(case when atd.transaction_id_new is null then false else true end) as audit_relaunch"), // Para saber si con el cambio se puede hacer un relanzamiento

                            \DB::raw("(case when ai.transaction_id is null then false else true end) as audit_incomes"), // Saber si ya se relanzó por segunada vez.

                            \DB::raw("
                                coalesce((
                                    json_agg(
                                        json_build_object(
                                            'user_id_new', atd.user_id,
                                            'comment_new', atd.comment,
                                            'created_at_new', to_char(atd.created_at, 'DD/MM/YYYY HH24:MI:SS.MS'),

                                            'transaction_id_old', atd.transaction_id_old,
                                            'transaction_id_new', atd.transaction_id_new,

                                            'devolution_status_id_old', atd.devolution_status_id_old,
                                            'devolution_status_id_new', atd.devolution_status_id_new
                                        ) order by (atd.id) desc
                                    )
                                ), '[]'::json) as audit_detail
                            ")
                        )
                        ->join('services_providers_sources as sps', 'sps.id', '=', 't.service_source_id')
                        ->join('servicios_x_marca as sxm', function ($join) {
                            $join->on('t.service_source_id', '=', 'sxm.service_source_id');
                            $join->on('t.service_id', '=', 'sxm.service_id');
                        })
                        ->join('marcas as m', 'm.id', '=', 'sxm.marca_id')
                        ->join('transaction_devolution as td', 't.id', '=', 'td.transaction_devolution_id')
                        ->join('incomes as i', 'td.transaction_devolution_id', '=', 'i.transaction_id')
                        ->join('devolution_method as dm', 'dm.id', '=', 'td.devolution_method_id')
                        ->join('devolution_reason as dr', 'dr.id', '=', 'td.devolution_reason_id')
                        ->join('devolution_type as dt', 'dt.id', '=', 'td.devolution_type_id')
                        ->join('devolution_status as ds', 'ds.id', '=', 'td.devolution_status_id')
                        ->leftjoin('ajustement_reason as ar', 'ar.id', '=', 'td.ajustement_reason_id')
                        ->leftjoin('audit.transaction_devolution as atd', 'td.transaction_devolution_id', '=', 'atd.transaction_devolution_id')
                        ->leftjoin('audit.incomes as ai', 'td.transaction_devolution_id', '=', 'ai.transaction_id')
                        ->join('users as u', 'u.id', '=', 'td.user_id')

                        ->join('transactions as t2', 't2.id', '=', 'td.transaction_id')
                        ->join('atms as a', 'a.id', '=', 't2.atm_id')

                        ->where('td.status', true);

                    if (isset($request['created_at'])) {

                        $created_at = $request['created_at'];
                        $aux = explode(' - ', str_replace('/', '-', $created_at));
                        $from = date('Y-m-d H:i:s', strtotime($aux[0]));
                        $to = date('Y-m-d H:i:s', strtotime($aux[1]));
                        $transactions = $transactions->whereRaw("t.created_at between '{$from}' and '{$to}'");

                        $from_view = date('d/m/Y', strtotime($aux[0])) . ' 00:00:00';
                        $to_view = date('d/m/Y', strtotime($aux[1])) . ' 23:59:59';
                        $request['created_at'] = "$from_view - $to_view";
                    } else {
                        // Si no hay filtro de fecha se trae lo de hoy.
                        $from = date('Y-m-d H:i:s');
                        $to = date('Y-m-d H:i:s');
                        $transactions = $transactions->whereRaw("t.created_at between '{$from}' and '{$to}'");
                    }

                    if (isset($request['transaction_id'])) {
                        if ($request['transaction_id'] !== '') {
                            $transactions = $transactions->where('td.transaction_id', $request['transaction_id']);
                        }
                    }

                    if (isset($request['transaction_devolution_id'])) {
                        if ($request['transaction_devolution_id'] !== '') {
                            $transactions = $transactions->where('td.transaction_devolution_id', $request['transaction_devolution_id']);
                        }
                    }

                    if (isset($request['amount'])) {
                        if ($request['amount'] !== '') {
                            $transactions = $transactions->where('t.amount', $request['amount']);
                        }
                    }

                    if (isset($request['transaction_status_id'])) {
                        if ($request['transaction_status_id'] !== '' and $request['transaction_status_id'] !== 'Todos') {
                            $transactions = $transactions->where('t.status', $request['transaction_status_id']);
                        }
                    }

                    if (isset($request['service_source_id'])) {
                        if ($request['service_source_id'] !== '' and $request['service_source_id'] !== 'Todos') {
                            $transactions = $transactions->where('t.service_source_id', $request['service_source_id']);
                        }
                    }

                    if (isset($request['service_id'])) {
                        if ($request['service_id'] !== '' and $request['service_id'] !== 'Todos') {
                            $transactions = $transactions->where('t.service_id', $request['service_id']);
                        }
                    }

                    $query = $transactions->toSql();

                    $transactions = $transactions
                        ->groupBy(\DB::raw("
                            td.transaction_id,
                            sps.id, m.descripcion, sxm.descripcion, t.amount,
                            td.created_at, t.status, t.status_description, td.devolution_amount,
                            dr.id, dt.id, ds.id, ar.id, u.id, u.description,
                            td.ajustement, td.ajustement_amount, td.ajustement_percentage,
                            td.comment, atd.transaction_id_new,
                            td.transaction_devolution_id,
                            atd.transaction_devolution_id,
                            ai.transaction_id,

                            a.id,
                            t2.status,
                            t2.status_description,
                            t2.amount
                        "))
                        ->orderBy('td.transaction_id', 'DESC')
                        ->take(1000);

                    // \Log::info("transactions: \n$query");

                    $transactions = $transactions->get();

                    //$transactions = json_decode(json_encode($transactions), true);

                    if (count($transactions) <= 0) {
                        $data = [
                            'mode' => 'alert',
                            'type' => 'info',
                            'title' => 'Consulta sin registros',
                            'explanation' => 'La consulta no retornó ningún registro.'
                        ];

                        return view('messages.index', compact('data'));
                    }
                } else if ($request['button_name'] == 'generate_x') {

                    $records = json_decode($request['json'], true);
                    $transactions = $records;

                    //\Log::info('records:', [$records]);

                    $style_array = [
                        'font'  => [
                            'bold'  => true,
                            'color' => ['rgb' => '367fa9'],
                            'size'  => 12,
                            'name'  => 'Verdana'
                        ]
                    ];

                    $filename = 'records_report_' . time();

                    $records_aux = [];

                    foreach ($records as $item) {
                        $amount_view = (int) str_replace('.', '', $item['amount_view']);
                        $item_ondanet_detail = json_decode($item['ondanet_detail'], true);

                        $item['ondanet_servicio'] = '';
                        $item['ondanet_devolucion'] = '';
                        $item['ondanet_comision'] = '';

                        foreach ($item_ondanet_detail as $sub_item) {
                            $ondanet_code = $sub_item['ondanet_code'];
                            $ondanet_destination_operation_id = $sub_item['ondanet_destination_operation_id'];
                            $ondanet_response = $sub_item['ondanet_response'];
                            $ondanet_updated_at = $sub_item['ondanet_updated_at'];

                            $ondanet_detail_aux = '';
                            $ondanet_detail_aux .= "Código enviado: $ondanet_code | ";
                            $ondanet_detail_aux .= "Código Recibido: $ondanet_destination_operation_id | ";
                            $ondanet_detail_aux .= "Respuesta: $ondanet_response | ";
                            $ondanet_detail_aux .= "Fecha y Hora: $ondanet_updated_at";
                            $ondanet_detail_aux .= "\n\n";

                            if ($ondanet_code == '2300') {
                                $item['ondanet_devolucion'] = $ondanet_detail_aux;
                            } else if ($ondanet_code == '2302') {
                                $item['ondanet_comision'] = $ondanet_detail_aux;
                            } else {
                                $item['ondanet_servicio'] = $ondanet_detail_aux;
                            }
                        }

                        $item['amount_view'] = $amount_view;

                        unset($item['transaction_id_view']);
                        unset($item['transaction_devolution_id_view']);
                        unset($item['provider_id']);
                        unset($item['devolution_reason_id']);
                        unset($item['devolution_type_id']);
                        unset($item['devolution_status_id']);
                        unset($item['ajustement_reason_id']);
                        unset($item['user_id']);
                        unset($item['ondanet_detail']);
                        unset($item['audit_detail']);
                        unset($item['audit']);
                        unset($item['audit_relaunch']);
                        unset($item['audit_incomes']);

                        unset($item['atm_id']);
                        unset($item['amount_main_view']);

                        array_push($records_aux, $item);

                        \Log::info('item in devolution:', $item);
                    }

                    $columnas = array(
                        'Transacción Principal ID',
                        'Transacción Principal Monto',
                        'Transacción Principal Estado',
                        'Transacción Principal Estado Descripción',
                        'Transacción Principal Terminal',

                        'Transacción Devolución ID',
                        'Proveedor',
                        'Servicio',
                        'Monto',
                        'Fecha - Hora',
                        'Transacción Estado',
                        'Transacción Estado Descripción',
                        'Monto de Devolución',
                        'Motivo de Devolución',
                        'Tipo de Devolución',
                        'Estado de Devolución',
                        'Ajuste',
                        'Motivo de Ajuste',
                        'Monto de Ajuste',
                        'Porcentaje de Ajuste',
                        'Usuario',
                        'Comentario',
                        'ONDANET Servicio',
                        'ONDANET Devolución',
                        'ONDANET Comision'
                    );

                    $excel = new ExcelExport($records_aux,$columnas);
                    return Excel::download($excel, $filename . '.xls')->send();

                    // Excel::create($filename, function ($excel) use ($records_aux, $style_array) {
                    //     $excel->sheet('Devoluciones Realizadas', function ($sheet) use ($records_aux, $style_array) {
                    //         $sheet->rows($records_aux, false);

                    //         $sheet->prependRow(array(
                    //             'Transacción Principal ID',
                    //             'Transacción Principal Monto',
                    //             'Transacción Principal Estado',
                    //             'Transacción Principal Estado Descripción',
                    //             'Transacción Principal Terminal',

                    //             'Transacción Devolución ID',
                    //             'Proveedor',
                    //             'Servicio',
                    //             'Monto',
                    //             'Fecha - Hora',
                    //             'Transacción Estado',
                    //             'Transacción Estado Descripción',
                    //             'Monto de Devolución',
                    //             'Motivo de Devolución',
                    //             'Tipo de Devolución',
                    //             'Estado de Devolución',
                    //             'Ajuste',
                    //             'Motivo de Ajuste',
                    //             'Monto de Ajuste',
                    //             'Porcentaje de Ajuste',
                    //             'Usuario',
                    //             'Comentario',
                    //             'ONDANET Servicio',
                    //             'ONDANET Devolución',
                    //             'ONDANET Comision'
                    //         ));

                    //         $sheet->getStyle('A1:Y1')->applyFromArray($style_array);
                    //         $sheet->setHeight(1, 25);
                    //     });
                    // })->export('xlsx');

                    $get_info = false;
                }
            }

            //Traer solo cuando hay búsqueda no cuando genera el excel.
            if ($get_info) {

                $transactions_status = $connection
                    ->table('transactions_status as ts')
                    ->select(
                        'ts.name as id',
                        \DB::raw("(ts.name || ' (' || ts.description || ')') as description")
                    )
                    ->get();

                /**
                 * Trae los proveedores ordenado por descripción
                 */
                $services_providers_sources = $connection
                    ->table('services_providers_sources')
                    ->select(
                        'id',
                        \DB::raw("(id || '# ' || description) as description")
                    )
                    ->whereRaw('id = any(array[0, 1, 4, 7, 8, 9, 10])')
                    ->orderBy('id', 'ASC')
                    ->get();

                /**
                 * Trae los estados de devolución
                 */
                $devolution_status = $connection
                    ->table('devolution_status')
                    ->select(
                        'id',
                        'description'
                    )
                    ->get();

                /**
                 * Trae los estados de devolución
                 */
                $users = $connection
                    ->table('users')
                    ->select(
                        'id',
                        'description'
                    )
                    ->whereRaw("permissions ilike '%cms_transactions_devolutions%'")
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
                'transactions' => $transactions,
                'json' => json_encode($transactions, JSON_UNESCAPED_UNICODE),
                'transaction_status' => json_encode($transactions_status, JSON_UNESCAPED_UNICODE),
                'services_providers_sources' => json_encode($services_providers_sources, JSON_UNESCAPED_UNICODE),
                'devolution_status' => json_encode($devolution_status, JSON_UNESCAPED_UNICODE),
                'users' => json_encode($users, JSON_UNESCAPED_UNICODE)
            ],
            'inputs' => [
                'created_at' => isset($request['created_at']) ? $request['created_at'] : null,
                'transaction_id' => isset($request['transaction_id']) ? $request['transaction_id'] : null,
                'transaction_devolution_id' => isset($request['transaction_devolution_id']) ? $request['transaction_devolution_id'] : null,
                'amount' => isset($request['amount']) ? $request['amount'] : null,
                'transaction_status_id' => isset($request['transaction_status_id']) ? $request['transaction_status_id'] : 'Todos',
                'service_source_id' => isset($request['service_source_id']) ? $request['service_source_id'] : 'Todos',
                'service_id' => isset($request['service_id']) ? $request['service_id'] : 'Todos',
                'user_id' => $user_id
            ]
        ];

        return view('transactions.index_devolutions', compact('data'));
    }


    /**
     * Informe de servicios que requieren más devoluciones.
     */
    public function index_services_with_more_returns($request)
    {

        if (!$this->user->hasAccess('cms_services_with_more_returns')) {
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

        $user_id = $this->user->id;

        $message = '';
        $atms = [];
        $transactions_status = [];
        $services_providers_sources = [];
        $devolution_status = [];
        $users = [];
        $transactions = [];
        $get_info = true;

        $error_detail = [];

        try {

            $connection = $this->connection;

            if (isset($request['button_name'])) {

                if ($request['button_name'] == 'search') {

                    /**
                     * Trae el detalle de pago ordenado por proveedor, terminal, servicio y comisión 
                     */
                    $transactions = \DB::table('transactions as t')
                        ->select(
                            'sps.description as provider',
                            \DB::raw("m.descripcion || ' - ' || sxm.descripcion as service"),
                            \DB::raw("count(t2.id) as transactions_count"),
                            \DB::raw("round(sum(abs(t2.amount))) as transactions_amount"),
                            \DB::raw("round(avg(abs(t2.amount))) as transactions_amount_avg")
                        )
                        ->join('transaction_devolution as td', 't.id', '=', 'td.transaction_id')
                        ->join('transactions as t2', 't2.id', '=', 'td.transaction_devolution_id')
                        ->join('services_providers_sources as sps', 'sps.id', '=', 't.service_source_id')
                        ->join('servicios_x_marca as sxm', function ($join) {
                            $join->on('sxm.service_source_id', '=', 't.service_source_id');
                            $join->on('sxm.service_id', '=', 't.service_id');
                        })
                        ->join('marcas as m', 'm.id', '=', 'sxm.marca_id');

                    if (isset($request['created_at'])) {

                        $created_at = $request['created_at'];
                        $aux = explode(' - ', str_replace('/', '-', $created_at));
                        $from = date('Y-m-d H:i:s', strtotime($aux[0]));
                        $to = date('Y-m-d H:i:s', strtotime($aux[1]));
                        $transactions = $transactions->whereRaw("t2.created_at between '{$from}' and '{$to}'");

                        $from_view = date('d/m/Y', strtotime($aux[0])) . ' 00:00:00';
                        $to_view = date('d/m/Y', strtotime($aux[1])) . ' 23:59:59';
                        $request['created_at'] = "$from_view - $to_view";
                    } else {
                        // Si no hay filtro de fecha se trae lo de hoy.
                        $from = date('Y-m-d H:i:s');
                        $to = date('Y-m-d H:i:s');
                        $transactions = $transactions->whereRaw("t2.created_at between '{$from}' and '{$to}'");
                    }

                    if (isset($request['transaction_id'])) {
                        if ($request['transaction_id'] !== '') {
                            $transactions = $transactions->where('td.transaction_id', $request['transaction_id']);
                        }
                    }

                    if (isset($request['transaction_devolution_id'])) {
                        if ($request['transaction_devolution_id'] !== '') {
                            $transactions = $transactions->where('td.transaction_devolution_id', $request['transaction_devolution_id']);
                        }
                    }

                    if (isset($request['amount'])) {
                        if ($request['amount'] !== '') {
                            $transactions = $transactions->where('t.amount', $request['amount']);
                        }
                    }

                    if (isset($request['transaction_status_id'])) {
                        if ($request['transaction_status_id'] !== '' and $request['transaction_status_id'] !== 'Todos') {
                            $transactions = $transactions->where('t.status', $request['transaction_status_id']);
                        }
                    }

                    if (isset($request['service_source_id'])) {
                        if ($request['service_source_id'] !== '' and $request['service_source_id'] !== 'Todos') {
                            $transactions = $transactions->where('t.service_source_id', $request['service_source_id']);
                        }
                    }

                    if (isset($request['service_id'])) {
                        if ($request['service_id'] !== '' and $request['service_id'] !== 'Todos') {
                            $transactions = $transactions->where('t.service_id', $request['service_id']);
                        }
                    }



                    $transactions = $transactions
                        ->groupBy('provider', 'service')
                        ->orderBy('transactions_count', 'desc');

                    $query = $transactions->toSql();

                    \Log::info("QUERY: \n$query");

                    $transactions = $transactions->get();

                    //$transactions = json_decode(json_encode($transactions), true);

                    if (count($transactions) <= 0) {
                        $data = [
                            'mode' => 'alert',
                            'type' => 'info',
                            'title' => 'Consulta sin registros',
                            'explanation' => 'La consulta no retornó ningún registro.'
                        ];

                        return view('messages.index', compact('data'));
                    }
                } else if ($request['button_name'] == 'generate_x') {

                    $records = json_decode($request['json'], true);

                    \Log::info('records:', [$records]);

                    $style_array = [
                        'font'  => [
                            'bold'  => true,
                            'color' => ['rgb' => '367fa9'],
                            'size'  => 12,
                            'name'  => 'Verdana'
                        ]
                    ];

                    $filename = 'records_report_' . time();

                    $columnas = array(
                        'Proveedor',
                        'Servicio',
                        'Cantidad de transacciones',
                        'Monto total de transacciones',
                        'Promedio de monto'
                    );

                    $excel = new ExcelExport($records,$columnas);
                    return Excel::download($excel, $filename . '.xls')->send();

                    // Excel::create($filename, function ($excel) use ($records, $style_array) {
                    //     $excel->sheet('Informe', function ($sheet) use ($records, $style_array) {
                    //         $sheet->rows($records, false);

                    //         $sheet->prependRow(array(
                    //             'Proveedor',
                    //             'Servicio',
                    //             'Cantidad de transacciones',
                    //             'Monto total de transacciones',
                    //             'Promedio de monto'
                    //         ));

                    //         $sheet->getStyle('A1:E1')->applyFromArray($style_array);
                    //         $sheet->setHeight(1, 25);
                    //     });
                    // })->export('xlsx');

                    $get_info = false;
                }
            }

            //Traer solo cuando hay búsqueda no cuando genera el excel.
            if ($get_info) {

                $transactions_status = $connection
                    ->table('transactions_status as ts')
                    ->select(
                        'ts.name as id',
                        \DB::raw("(ts.name || ' (' || ts.description || ')') as description")
                    )
                    ->get();

                /**
                 * Trae los proveedores ordenado por descripción
                 */
                $services_providers_sources = $connection
                    ->table('services_providers_sources')
                    ->select(
                        'id',
                        \DB::raw("(id || '# ' || description) as description")
                    )
                    ->whereRaw('id = any(array[0, 1, 4, 7, 8, 9, 10])')
                    ->orderBy('id', 'ASC')
                    ->get();

                /**
                 * Trae los estados de devolución
                 */
                $devolution_status = $connection
                    ->table('devolution_status')
                    ->select(
                        'id',
                        'description'
                    )
                    ->get();

                /**
                 * Trae los estados de devolución
                 */
                $users = $connection
                    ->table('users')
                    ->select(
                        'id',
                        'description'
                    )
                    ->whereRaw("permissions ilike '%cms_transactions_devolutions%'")
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
                'transactions' => $transactions,
                'json' => json_encode($transactions, JSON_UNESCAPED_UNICODE),
                'transaction_status' => json_encode($transactions_status, JSON_UNESCAPED_UNICODE),
                'services_providers_sources' => json_encode($services_providers_sources, JSON_UNESCAPED_UNICODE),
                'devolution_status' => json_encode($devolution_status, JSON_UNESCAPED_UNICODE),
                'users' => json_encode($users, JSON_UNESCAPED_UNICODE)
            ],
            'inputs' => [
                'created_at' => isset($request['created_at']) ? $request['created_at'] : null,
                'transaction_id' => isset($request['transaction_id']) ? $request['transaction_id'] : null,
                'transaction_devolution_id' => isset($request['transaction_devolution_id']) ? $request['transaction_devolution_id'] : null,
                'amount' => isset($request['amount']) ? $request['amount'] : null,
                'transaction_status_id' => isset($request['transaction_status_id']) ? $request['transaction_status_id'] : 'Todos',
                'service_source_id' => isset($request['service_source_id']) ? $request['service_source_id'] : 'Todos',
                'service_id' => isset($request['service_id']) ? $request['service_id'] : 'Todos',
                'user_id' => $user_id
            ],
            'error_detail' => $error_detail
        ];

        return view('transactions.index_services_with_more_returns', compact('data'));
    }

    /**
     * Obtener servicios por marca 
     */
    public function get_services_by_brand_for_transactions($request)
    {
        try {

            $connection = $this->connection;

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


            if (isset($request['service_source_id'])) {
                if ($request['service_source_id'] !== '' and $request['service_source_id'] !== 'Todos') {
                    $services_by_brand = $services_by_brand
                        ->where('sps.id', $request['service_source_id'])
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

    /**
     * Actualizar la transacción de devolución
     */
    public function update_transaction_devolution($request)
    {

        $class = __CLASS__;
        $function = __FUNCTION__;
        $inputs = json_encode($request);

        \Log::info("\n\nCampos obtenidos en $class \ $function:\n\n$inputs\n\n");

        \Log::info('PARAMETROS:', [$request->all()]);

        $response = [
            'error' => false,
            'message' => ''
        ];

        try {

            $user_id = $this->user->id;

            $transaction_devolution_id = $request['transaction_devolution_id'];
            $transaction_id_old = $request['transaction_id_old'];
            $transaction_id_new = $request['transaction_id_new'];
            $devolution_status_id_old = $request['devolution_status_id_old'];
            $devolution_status_id_new = $request['devolution_status_id_new'];
            $comment_new = $request['comment_new'];

            if ($transaction_id_new !== null and $transaction_id_new !== '') {
                if ($transaction_id_old == $transaction_id_new) {
                    $response['message'] = "El ID de la transacción principal ingresada es igual.\n\n";
                }

                if ($transaction_id_new == $transaction_devolution_id) {
                    $response['message'] = "El ID de la transacción principal ingresada no tiene que ser igual al de la devolución.\n\n";
                }
            }

            if ($devolution_status_id_new !== null and $devolution_status_id_new !== '') {
                if ($devolution_status_id_old == $devolution_status_id_new) {
                    $response['message'] .= "El estado seleccionado es igual.\n\n";
                }
            }

            if ($comment_new == null and $comment_new == '') {
                $response['message'] .= "El comentario no debe quedar vacío.\n\n";
            }

            if (($transaction_id_new == null or $transaction_id_new == '') and ($devolution_status_id_new == null or $devolution_status_id_new == '')) {
                $response['message'] .= "No se realizó ninguna modificación.\n\n";
            }


            if ($response['message'] == '') {
                $audit_transaction_devolution = \DB::table('audit.transaction_devolution as td')
                    ->select(
                        'td.id'
                    )
                    ->where('td.transaction_devolution_id', $transaction_devolution_id)
                    ->get();

                if (count($audit_transaction_devolution) <= 0) {

                    $update = [
                        'updated_at' => Carbon::now()
                    ];

                    if ($transaction_id_new !== null and $transaction_id_new !== '') {
                        $update['transaction_id'] = $transaction_id_new;
                    } else {
                        $transaction_id_old = null;
                        $transaction_id_new = null;
                    }

                    if ($devolution_status_id_new !== null and $devolution_status_id_new !== '') {
                        $update['devolution_status_id'] = $devolution_status_id_new;
                    } else {
                        $devolution_status_id_old = null;
                        $devolution_status_id_new = null;
                    }

                    \DB::beginTransaction();

                    \DB::table('transaction_devolution')
                        ->where('transaction_devolution_id', $transaction_devolution_id)
                        ->update($update);

                    \DB::table('audit.transaction_devolution')
                        ->insert([
                            'user_id' => $user_id,
                            'comment' => $comment_new,
                            'created_at' => Carbon::now(),

                            'transaction_devolution_id' => $transaction_devolution_id,
                            'transaction_id_old' => $transaction_id_old,
                            'transaction_id_new' => $transaction_id_new,
                            'devolution_status_id_old' => $devolution_status_id_old,
                            'devolution_status_id_new' => $devolution_status_id_new
                        ]);

                    \DB::commit();
                } else {
                    $response['message'] = 'Solo se puede modificar una vez este registro.';
                }
            }
        } catch (\Exception $e) {

            \DB::rollback();

            $error_detail = [
                'from' => 'CMS',
                'message' => 'Ocurrió un error al querer modificar el registro de devolución.',
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => $class,
                'function' => $function,
                'line' => $e->getLine()
            ];

            $response['message'] = $error_detail['message'];
            $response['error_detail'] = $error_detail;

            $error_detail = json_encode($error_detail);

            \Log::error("\n\nError en $class \ $function:\nDetalles:\n\n$error_detail\n\n");
        }


        if ($response['message'] !== '') {
            $response['error'] = true;
        } else {
            $response['message'] = 'Acción exitosa.';
        }

        /**
         * ---------------------------------------------------------------------------------------------------
         * Para mostrar todos los detalles en el log.
         */
        $response_aux = json_encode($response);

        \Log::info("\n\nRespuesta de $class \ $function:\n\n$response_aux\n\n");

        return $response;
    }

    /**
     * Relanzar comando de ondanet por cambio de transaction_id principal.
     */
    public function relaunch_code_by_change($request)
    {

        $class = __CLASS__;
        $function = __FUNCTION__;
        $inputs = json_encode($request);

        \Log::info("\n\nCampos obtenidos en $class \ $function:\n\n$inputs\n\n");

        \Log::info('PARAMETROS:', [$request->all()]);

        $response = [
            'error' => false,
            'message' => ''
        ];

        try {

            $transaction_devolution_id = $request['transaction_devolution_id']; // ID principal antiguo.
            $income_id = $request['income_id']; // ID de income a actualizar.
            $transaction_id_old = $request['transaction_id_old']; // ID principal antiguo.
            $transaction_id_new = $request['transaction_id_new']; // ID principal nuevo.
            $user_id = $this->user->id;

            //--------------------------------------------------------------------------

            $incomes = \DB::table('incomes as i')
                ->select(
                    'pos.atm_id',
                    'i.pos_id',
                    'i.destination_operation_id',
                    'i.response',
                    'i.request',
                    'i.updated_at'
                )
                ->join('points_of_sale as pos', 'pos.id', '=', 'i.pos_id')
                ->where('i.id', $income_id)
                ->get();

            $incomes = $incomes[0];

            $atm_id_old = $incomes->atm_id;
            $pos_id_old = $incomes->pos_id;
            $destination_operation_id_old = $incomes->destination_operation_id;
            $response_old = $incomes->response;
            $request_old = $incomes->request;
            $updated_at_old = $incomes->updated_at;

            //--------------------------------------------------------------------------

            $transactions = \DB::table('transactions as t')
                ->select(
                    't.atm_id',
                    'pos.id as pos_id'
                )
                ->join('atms as a', 'a.id', '=', 't.atm_id')
                ->join('points_of_sale as pos', 'a.id', '=', 'pos.atm_id')
                ->where('t.id', $transaction_id_new)
                ->get();

            $transactions = $transactions[0];

            $atm_id_new = $transactions->atm_id;
            $pos_id_new = $transactions->pos_id;

            $destination_operation_id_new = '0'; // 0 para relanzar el registro a ondanet
            $response_new = null;
            $request_new = null;
            $now = Carbon::now();

            //--------------------------------------------------------------------------

            $income_update = [

                'pos_id' => $pos_id_new,

                'destination_operation_id' => $destination_operation_id_new,

                'response' => $response_new,
                'request' => $request_new,

                'updated_at' => $now

            ];

            //--------------------------------------------------------------------------

            $audit_income_insert = [

                'income_id' => $income_id,
                'transaction_id' => $transaction_devolution_id,
                'user_id' => $user_id,
                'created_at' => $now,

                'atm_id_old' => $atm_id_old,
                'atm_id_new' => $atm_id_new,

                'pos_id_old' => $pos_id_old,
                'pos_id_new' => $pos_id_new,

                'destination_operation_id_old' => $destination_operation_id_old,

                'response_old' => $response_old,

                'request_old' => $request_old,

                'updated_at_old' => $updated_at_old,
                'updated_at_new' => $now

            ];

            //--------------------------------------------------------------------------

            \DB::beginTransaction();

            \Log::info("income_id = $income_id actualizado con los siguientes datos:", [$income_update]);

            \DB::table('incomes')
                ->where('id', $income_id)
                ->update($income_update);

            \Log::info("income_id = $income_id insertado en auditoría:", [$audit_income_insert]);

            \DB::table('audit.incomes')
                ->insert($audit_income_insert);

            \DB::commit();

            \Log::info("income_id = $income_id se realizó todos los cambios exitosamente.");
        } catch (\Exception $e) {

            \DB::rollback();

            $error_detail = [
                'from' => 'CMS',
                'message' => 'Ocurrió un error al querer modificar el registro de devolución.',
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => $class,
                'function' => $function,
                'line' => $e->getLine()
            ];

            $response['message'] = $error_detail['message'];
            $response['error_detail'] = $error_detail;

            $error_detail = json_encode($error_detail);

            \Log::error("\n\nError en $class \ $function:\nDetalles:\n\n$error_detail\n\n");
        }


        if ($response['message'] !== '') {
            $response['error'] = true;
        } else {
            $response['message'] = 'El registro será relanzado en unos momentos.';
        }

        /**
         * ---------------------------------------------------------------------------------------------------
         * Para mostrar todos los detalles en el log.
         */
        $response_aux = json_encode($response);

        \Log::info("\n\nRespuesta de $class \ $function:\n\n$response_aux\n\n");

        return $response;
    }
}
