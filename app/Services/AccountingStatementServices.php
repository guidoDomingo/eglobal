<?php

/**
 * User: avisconte
 * Date: 11/04/2023
 * Time: 13:35 pm
 */

namespace App\Services;

use App\Exports\ExcelExport;
use Carbon\Carbon;
use Excel;

class AccountingStatementServices
{

    public function __construct()
    {
        $this->user = \Sentinel::getUser();
    }

    public function format_amount($records, $key)
    {
        $format = number_format(
            array_sum(
                array_map(
                    function ($item) use ($key) {
                        return str_replace(',', '', $item[$key]);
                    },
                    $records
                )
            ),
            0,
            '.',
            ','
        );

        return $format;
    }

    /**
     * Función inicial
     */
    public function index($request)
    {

        //\Log::info('request info:', [$request->all()]);

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

        $owners = [];
        $groups = [];
        $atms = [];
        $block_types = [];
        $managers = [];
        $records = [];

        $get_info = true;
        $error_detail = [];

        /**
         * -----------------------------------------------------------------------------------------------------------------
         */

        $this->user = \Sentinel::getUser();

        if (!$this->user->hasAccess('accounting_statement_report')) {
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

        try {

            /**
             * Valores iniciales de los campos de búsqueda.
             */
            $from_view = date('d/m/Y') . ' 00:00:00';
            $to_view = date('d/m/Y') . ' 23:59:59';
            $timestamp = "$from_view - $to_view";

            $summary_to_date = 'on';
            $summary_closing = '';

            $owner_id = null;
            $group_id = null;
            $atm_id = null;
            $manager_id = null;

            $where_group = '';
            $where_atm = '';

            if (isset($request['button_name'])) {

                if ($request['button_name'] == 'search') {

                    $summary_to_date = '';
                    $summary_closing = '';
                    $where = '';

                    if (isset($request['summary_to_date'])) {
                        if ($request['summary_to_date'] !== '') {
                            $summary_to_date = $request['summary_to_date'];
                        }
                    }

                    if (isset($request['summary_closing'])) {
                        if ($request['summary_closing'] !== '') {
                            $summary_closing = $request['summary_closing'];
                        }
                    }

                    if ($summary_closing == 'on') {
                        $summary_to_date = '';
                    }

                    if (isset($request['owner_id'])) {
                        if ($request['owner_id'] !== '' and $request['owner_id'] !== 'Todos') {
                            $owner_id = $request['owner_id'];
                        }
                    }

                    if (isset($request['group_id'])) {
                        if ($request['group_id'] !== '' and $request['group_id'] !== 'Todos') {
                            $group_id = $request['group_id'];
                        }
                    }

                    if (isset($request['atm_id'])) {
                        if ($request['atm_id'] !== '' and $request['atm_id'] !== 'Todos') {
                            $atm_id = $request['atm_id'];
                        }
                    }

                    if (isset($request['manager_id'])) {
                        if ($request['manager_id'] !== '' and $request['manager_id'] !== 'Todos') {
                            $manager_id = $request['manager_id'];
                        }
                    }


                    if (isset($request['timestamp'])) {

                        $timestamp = $request['timestamp'];
                        $aux = explode(' - ', str_replace('/', '-', $timestamp));
                        $from = date('Y-m-d H:i:s', strtotime($aux[0]));
                        $to = date('Y-m-d H:i:s', strtotime($aux[1]));

                        $from_view = date('d/m/Y', strtotime($aux[0])) . ' 00:00:00';
                        $to_view = date('d/m/Y', strtotime($aux[1])) . ' 23:59:59';
                        $timestamp = "$from_view - $to_view";
                    } else {
                        $from = date('Y-m-d H:i:s');
                        $to = date('Y-m-d H:i:s');
                    }

                    $option = 1;

                    if ($summary_to_date == 'on') {
                        $option = 2;
                    } else if ($summary_closing == 'on') {
                        $option = 3;
                    }

                    $where_group = '';
                    $where_atm = '';

                    if ($group_id !== null) {
                        $where_group .= " and bg.id = $group_id";
                    }

                    if ($owner_id !== null) {
                        $where_group .= " and a.owner_id = $owner_id";
                    }

                    if ($atm_id !== null) {
                        $where_group = " and bg.id = $group_id"; // Se reemplaza directamente.
                        $where_atm = " and a.id = $atm_id";
                    }

                    if ($manager_id !== null) {
                        $where_group = " and bg.manager_id = $manager_id"; // Se reemplaza directamente.
                        $where_atm = "";
                    }


                    if ($summary_to_date == 'on') {
                        $query = $this->select_al_dia_de_hoy_grupo($where_group, $where_atm); // select al día de hoy
                    } else {
                        $query = $this->select_al_cierre_grupo($where_group, $where_atm, $option, $from, $to); // select al cierre o con fecha dinámica
                    }

                    if ($summary_to_date == '' and $summary_closing == '') {
                        $query = $this->select_al_cierre_grupo($where_group, $where_atm, $option, $from, $to);
                    }

                    \Log::info("$query");

                    $records = \DB::select($query);

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
                } else if ($request['button_name'] == 'generate_x') {

                    // Comisión por Transacciones
                    $json = json_decode($request['json'], true);

                    //Variable que se usa en todo el documento 
                    $records = $json['lists']['records'];
                    $totals_list = $json['totals'];
                    $totals_list_aux = [];

                    //\Log::info("totals_list:", [$totals_list]);

                    foreach ($totals_list as $key => $value) {
                        if ($value !== null) {
                            $item_aux = [
                                'dato' => $key,
                                'valor' => $value
                            ];

                            array_push($totals_list_aux, $item_aux);
                        }
                    }

                    $totals_list = $totals_list_aux;

                    \Log::info("totals_list 2:", [$totals_list]);

                    $style_array = [
                        'font'  => [
                            'bold'  => true,
                            'color' => ['rgb' => '285f6c'],
                            'size'  => 12,
                            'name'  => 'Verdana'
                        ]
                    ];

                    $style_cell = [
                        'font'  => [
                            'bold'  => true,
                            'color' => ['rgb' => 'FFFFFF'],
                            'size'  => 12,
                            'name'  => 'Verdana'
                        ]
                    ];

                    $filename = 'report_' . time();

                    $result_aux = [];

                    foreach ($records as $result_item) {

                        $row = [
                            $result_item['group_id'],
                            $result_item['group_description'],
                            $result_item['saldo'],
                            $result_item['estado'],
                            $result_item['total_multa'],
                            $result_item['total_transaccionado'],
                            $result_item['total_depositos'],
                            $result_item['total_reversiones'],
                            $result_item['total_cashouts'],
                            $result_item['total_pago_qr'],
                            $result_item['total_quota_sales'],
                            $result_item['total_quota_rental'],
                            $result_item['total_cuota'],
                            $result_item['regla'],
                            $result_item['atms_con_regla'],
                            $result_item['atms_sin_regla']
                        ];

                        array_push($result_aux, $row);
                    }

                    $records = $result_aux;

                    $columna1 = ['Dato', 'Valor'];

                    $columna2 = array(
                        'ID-Cliente',
                        'Cliente',
                        'Saldo',
                        'Estado',
                        'Total-Multa',
                        'Total-Transaccionado',
                        'Total-Pagado',
                        'Total-Reversado',
                        'Total-Cashout',
                        'Total-Pago-QR',
                        'Total-Cuota-Venta',
                        'Total-Cuota-Alquiler',
                        'Total-Cuotas',
                        'Regla',
                        'Terminales Con Regla',
                        'Terminales Sin Regla'
                    );

                    $excel = new ExcelExport($totals_list,$columna1,$records,$columna2);
                    return Excel::download($excel, $filename . '.xls')->send();

                    // Excel::create($filename, function ($excel) use ($totals_list, $records, $style_array, $style_cell) {

                    //     $excel->sheet('Total General', function ($sheet) use ($totals_list, $style_array) {
                    //         $sheet->rows($totals_list, false);
                    //         $sheet->prependRow(['Dato', 'Valor']);
                    //         $sheet->getStyle('A1:B1')->applyFromArray($style_array); //Aplicar los estilos del array
                    //         $sheet->setHeight(1, 25); //Aplicar tamaño de la primera fila
                    //     });

                    //     $excel->sheet('Total por Grupo', function ($sheet) use ($records, $style_array) {
                    //         $sheet->rows($records, false);
                    //         $sheet->prependRow(array(
                    //             'ID-Cliente',
                    //             'Cliente',
                    //             'Saldo',
                    //             'Estado',
                    //             'Total-Multa',
                    //             'Total-Transaccionado',
                    //             'Total-Pagado',
                    //             'Total-Reversado',
                    //             'Total-Cashout',
                    //             'Total-Pago-QR',
                    //             'Total-Cuota-Venta',
                    //             'Total-Cuota-Alquiler',
                    //             'Total-Cuotas',
                    //             'Regla',
                    //             'Terminales Con Regla',
                    //             'Terminales Sin Regla'
                    //         ));

                    //         $sheet->getStyle('A1:P1')->applyFromArray($style_array); //Aplicar los estilos del array
                    //         $sheet->setHeight(1, 25); //Aplicar tamaño de la primera fila
                    //     });

                    //     $excel->setActiveSheetIndex(1);

                    //     // Definir límites de la hoja de Excel
                    //     $highestColumn = $excel->getActiveSheet()->getHighestColumn();
                    //     $highestRow = $excel->getActiveSheet()->getHighestRow();

                    //     for ($row = 2; $row <= $highestRow; $row++) {

                    //         for ($col = 'C'; $col <= $highestColumn; $col++) {

                    //             $cellValue = $excel->getActiveSheet()->getCell($col . $row)->getValue();

                    //             $color = '';

                    //             $style_cell['font']['color']['rgb'] = 'FFFFFF';

                    //             if ($col == 'C') {

                    //                 $saldo_aux = (int) str_replace(',', '', $cellValue);

                    //                 if ($saldo_aux <= 0) {

                    //                     $color = '00a65a';
                    //                 } else if ($saldo_aux > 0) {

                    //                     $color = 'dd4b39';
                    //                 }

                    //                 if ($color !== '') {
                    //                     $style_cell['font']['color']['rgb'] = $color;
                    //                     $excel->getActiveSheet()->getStyle($col . $row)->applyFromArray($style_cell);
                    //                 }
                    //             } else if ($col == 'D') {

                    //                 if ($cellValue == 'Activo') {

                    //                     $color = '00a65a';
                    //                 } else if (strpos($cellValue, 'Bloqueado') !== false) {

                    //                     $color = 'dd4b39';
                    //                 } else if ($cellValue == 'Inactivo') {

                    //                     $color = 'f39c12';
                    //                 } else if ($cellValue == 'Sin estado') {

                    //                     $color = '00c0ef';
                    //                 }

                    //                 if ($color !== '') {
                    //                     $excel->getActiveSheet()->getStyle($col . $row)->applyFromArray($style_cell);
                    //                     $excel->getActiveSheet()->getStyle($col . $row)->getFill()->setFillType(\PHPExcel_Style_Fill::FILL_SOLID)->getStartColor()->setRGB($color);
                    //                 }
                    //             }
                    //         }
                    //     }
                    // })->export('xlsx');

                    $get_info = false;
                }
            }

            //Traer solo cuando hay búsqueda no cuando genera el excel.
            if ($get_info) {

                /**
                 * Trae las redes
                 */
                $owners = \DB::table('owners as o')
                    ->select(
                        'o.id',
                        \DB::raw("(o.id || '# ' || o.name) as description")
                    )
                    ->whereRaw('o.deleted_at is null')
                    ->orderBy('o.id', 'asc')
                    ->get();

                /**
                 * Trae los grupos
                 */
                $groups = \DB::table('business_groups as bg')
                    ->select(
                        'bg.id',
                        \DB::raw("(bg.id || '# | ' || bg.description || ' | ' || bg.ruc) as description")
                    )
                    ->whereRaw('bg.ruc is not null')
                    ->orderBy('bg.description', 'ASC')
                    ->get();

                /**
                 * Trae los atms relacionados al usuario
                 */
                $atms = \DB::table('business_groups as bg')
                    ->select(
                        'bg.id as group_id',
                        'a.id as atm_id',
                        \DB::raw("(a.id || '# ' || a.name) as atm_description")
                    )
                    ->join('branches as b', 'bg.id', '=', 'b.group_id')
                    ->join('points_of_sale as pos', 'b.id', '=', 'pos.branch_id')
                    ->join('atms as a', 'a.id', '=', 'pos.atm_id')
                    ->whereRaw('bg.ruc is not null')
                    ->orderBy('bg.description', 'ASC')
                    ->get();

                /**
                 * Trae los block_types 
                 */
                $block_types = \DB::table('block_type as bt')
                    ->select(
                        'bt.id',
                        'bt.description'
                    )
                    ->orderBy('bt.id', 'ASC')
                    ->get();

                /**
                 * Manejadores de grupos
                 */
                $managers = \DB::table('managers as m')
                    ->select(
                        'm.id',
                        'm.description'
                    )
                    ->join('business_groups as bg', 'm.id', '=', 'bg.manager_id')
                    ->groupBy('m.id')
                    ->orderBy('m.id', 'ASC')
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

            //\Log::info("$query");
        }

        $data = [
            'message' => $message,
            'lists' => [
                'records' => $records,
                'owners' => json_encode($owners, JSON_UNESCAPED_UNICODE),
                'groups' => json_encode($groups, JSON_UNESCAPED_UNICODE),
                'block_types' => json_encode($block_types, JSON_UNESCAPED_UNICODE),
                'atms' => json_encode($atms, JSON_UNESCAPED_UNICODE),
                'managers' => json_encode($managers, JSON_UNESCAPED_UNICODE)
            ],
            'inputs' => [
                'user_id' => $this->user->id,
                'timestamp' => $timestamp,
                'summary_to_date' => $summary_to_date,
                'summary_closing' => $summary_closing,
                'owner_id' => isset($request['owner_id']) ? $request['owner_id'] : 'Todos',
                'group_id' => isset($request['group_id']) ? $request['group_id'] : 'Todos',
                'atm_id' => isset($request['atm_id']) ? $request['atm_id'] : 'Todos',
                'manager_id' => isset($request['manager_id']) ? $request['manager_id'] : 'Todos'
            ],
            'totals' => [
                'ESTADO' => count($records) == 1 ? $records[0]['estado'] : null,
                'SALDO' => $this->format_amount($records, 'saldo'),
                'TOTAL TRANSACCIONES' => $this->format_amount($records, 'total_transaccionado'),
                'TOTAL PAGADO' => $this->format_amount($records, 'total_depositos'),
                'TOTAL REVERSADO' => $this->format_amount($records, 'total_reversiones'),
                'TOTAL CASHOUT' => $this->format_amount($records, 'total_cashouts'),
                'TOTAL PAGO QR' => $this->format_amount($records, 'total_pago_qr'),
                'TOTAL CUOTA' => $this->format_amount($records, 'total_cuota'),
                'TOTAL MULTA' => $this->format_amount($records, 'total_multa')
            ],
            'error_detail' => $error_detail
        ];

        /*\Log::info("TOTAL:", [
            'ESTADO' => count($records) == 1 ? $records[0]['estado'] : null,
            'SALDO' => $this->format_amount($records, 'saldo'),
            'TOTAL TRANSACCIONES' => $this->format_amount($records, 'total_transaccionado'),
            'TOTAL PAGADO' => $this->format_amount($records, 'total_depositos'),
            'TOTAL REVERSADO' => $this->format_amount($records, 'total_reversiones'),
            'TOTAL CASHOUT' => $this->format_amount($records, 'total_cashouts'),
            'TOTAL PAGO QR' => $this->format_amount($records, 'total_pago_qr'),
            'TOTAL CUOTA' => $this->format_amount($records, 'total_cuota'),
            'TOTAL MULTA' => $this->format_amount($records, 'total_multa')
        ]);*/

        return view('accounting_statement.index', compact('data'));
    }

    /**
     * Modificar el Block-Type del ATM
     */
    public function block_type_change($request)
    {

        $class = __CLASS__;
        $function = __FUNCTION__;
        $inputs = json_encode($request);
        \Log::info("\n\nCampos obtenidos en $class \ $function:\n\n$inputs\n\n");

        $response = [
            'error' => false,
            'message' => '',
            'querys_log' => null
        ];

        try {

            $user_id = $request['user_id'];
            $atm_id = $request['atm_id'];
            $block_type_id = $request['block_type_id'];
            $commentary = $request['commentary'];

            $now = Carbon::now();

            \DB::enableQueryLog();

            \DB::beginTransaction();

            // Nuevo metodo:
            $atm = \DB::table('public.atms as a')
                ->select(
                    'a.*'
                )
                ->where('a.id', $atm_id)
                ->get();

            $atm = $atm[0];
            $atm = json_decode(json_encode($atm), true);

            //\Log::info('atm:', [$atm]);

            $audit_atms_columns = \DB::table('information_schema.columns as c')
                ->select(
                    'c.column_name'
                )
                ->where('c.table_schema', 'audit')
                ->where('c.table_name', 'atms')
                ->whereRaw("c.column_name != 'audit_id'")
                ->get();

            $audit_atms_columns = json_decode(json_encode($audit_atms_columns), true);

            $audit_atms_columns_insert = [];

            foreach ($audit_atms_columns as $column) {
                $column = $column['column_name'];
                $audit_atms_columns_insert["$column"] = null;

                if (
                    $column !== 'audit_id' and
                    $column !== 'audit_created_at' and
                    $column !== 'audit_created_by' and
                    $column !== 'audit_commentary'
                ) {
                    $audit_atms_columns_insert["$column"] = $atm["$column"];
                }
            }

            $audit_atms_columns_insert['audit_created_at'] = $now;
            $audit_atms_columns_insert['audit_created_by'] = $user_id;
            $audit_atms_columns_insert['audit_commentary'] = $commentary;

            $audit_id = \DB::table('audit.atms')
                ->insertGetId($audit_atms_columns_insert);

            if ($audit_id !== null) {

                /**
                 * Actualiza al nuevo block_type_id
                 */
                \DB::table('public.atms')
                    ->where('id', $atm_id)
                    ->update([
                        'block_type_id' => $block_type_id,
                        'updated_by' => $user_id,
                        'updated_at' => $now
                    ]);

                /**
                 * Obtenemos el saldo pendiente del atm, pero haciendo el calculo con total_transaccionado_cierre
                 */
                $balance_atms = \DB::table('balance_atms')
                    ->select(
                        'total_transaccionado as total_transaccionado_cierre',
                        'total_depositado as total_deposited',
                        'total_reversado as total_reverse',
                        'total_cashout as total_cashout',
                        'total_pago_cashout as total_payment_cashout',
                        'total_pago_qr as pago_qr',
                        'total_multa as multa'
                    )
                    ->where('atm_id', $atm_id)
                    ->get();

                if (count($balance_atms) > 0) {
                    $balance_atms = $balance_atms[0];

                    $total_transaccionado_cierre = abs($balance_atms->total_transaccionado_cierre);
                    $total_deposited = -abs($balance_atms->total_deposited);
                    $total_reverse = -abs($balance_atms->total_reverse);
                    $total_cashout = -abs($balance_atms->total_cashout);
                    $total_payment_cashout = abs($balance_atms->total_payment_cashout);
                    $total_pago_qr = -abs($balance_atms->pago_qr);
                    $total_multa = abs($balance_atms->multa);

                    $total_balance = $total_transaccionado_cierre + $total_payment_cashout + $total_deposited + $total_reverse + $total_cashout + $total_pago_qr + $total_multa;
                } else {
                    $total_balance = 0;
                }

                /**
                 * block_type_id = 1 es Online 
                 * cualquier otro es locked = true
                 */
                if ($block_type_id == 0) {
                    $locked = false;
                } else {
                    $locked = true;
                }

                /**
                 * Inserta un nuevo registro en el historial_bloqueos
                 */

                \DB::table('public.historial_bloqueos')
                    ->insert([
                        'atm_id' => $atm_id,
                        'saldo_pendiente' => $total_balance,
                        'created_at' => $now,
                        'bloqueado' => $locked,
                        'block_type_id' => $block_type_id,
                        'commentary' => "$commentary (Comentario agregado desde Estado Contable Unificado", // Comentario ingresado en pantalla.
                        'created_by' => $user_id // Usuario que creó el registro.
                    ]);
            }

            \DB::commit();

            \Log::info("El atm_id = $atm_id tiene el nuevo block_type_id = $block_type_id por el usuario con id = $user_id");
        } catch (\Exception $e) {

            \DB::rollback();

            $error_detail = [
                'message' => 'Ocurrió una excepción al querer modificar el block type del atm.',
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


        $response['querys_log'] = \DB::getQueryLog();

        $response_aux = json_encode($response);

        \Log::info("\n\nRespuesta de $class \ $function:\n\n$response_aux\n\n");

        return $response;
    }

    /**
     * Función obtener detalles del grupo
     */
    public function get_details_per_group($request)
    {

        \Log::info('get_details_per_group:', [$request->all()]);

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

        $this->user = \Sentinel::getUser();

        if (!$this->user->hasAccess('accounting_statement_report')) {
            return [];
        }


        /**
         * -----------------------------------------------------------------------------------------------------------------
         */

        try {

            //262315395 - 262065395
            //-255,679,436

            $case = $request['case'];
            $group_id = $request['group_id'];
            $atm_id = $request['atm_id'];

            $query = '';
            $error = false;
            $message = '';
            $list = [];
            $error_detail = [];

            /**
             * Valores iniciales de los campos de búsqueda.
             */
            $from_view = date('d/m/Y') . ' 00:00:00';
            $to_view = date('d/m/Y') . ' 23:59:59';
            $timestamp = "$from_view - $to_view";

            $summary_to_date = 'on';
            $summary_closing = '';

            switch ($case) {
                case 'reglas':

                    $query = "select * from view_get_group_rules where group_id = $group_id";
                    $list = \DB::select($query);

                    break;

                case 'terminales':

                    $summary_to_date = '';
                    $summary_closing = '';
                    $where = 'where 1 = 1';

                    if ($request['summary_to_date'] !== '') {
                        $summary_to_date = $request['summary_to_date'];
                    }

                    if ($request['summary_closing'] !== '') {
                        $summary_closing = $request['summary_closing'];
                    }

                    if ($summary_closing == 'on') {
                        $summary_to_date = '';
                    }

                    if ($request['owner_id'] !== '' and $request['owner_id'] !== 'Todos') {
                        $owner_id = $request['owner_id'];
                        $owner_id_aux = $owner_id;
                    }

                    if ($request['group_id'] !== '' and $request['group_id'] !== 'Todos') {
                        $group_id = $request['group_id'];
                        $group_id_aux = $group_id;

                        $where .= " and group_id = $group_id";
                    }

                    if ($request['atm_id'] !== '' and $request['atm_id'] !== 'Todos') {
                        $atm_id = $request['atm_id'];
                        $atm_id_aux = $atm_id;

                        $where .= " and atm_id = $atm_id";
                    }

                    if (isset($request['timestamp'])) {

                        $timestamp = $request['timestamp'];
                        $aux = explode(' - ', str_replace('/', '-', $timestamp));
                        $from = date('Y-m-d H:i:s', strtotime($aux[0]));
                        $to = date('Y-m-d H:i:s', strtotime($aux[1]));

                        $from_view = date('d/m/Y', strtotime($aux[0])) . ' 00:00:00';
                        $to_view = date('d/m/Y', strtotime($aux[1])) . ' 23:59:59';
                        $timestamp = "$from_view - $to_view";
                    } else {
                        $from = date('Y-m-d H:i:s');
                        $to = date('Y-m-d H:i:s');
                    }

                    $option = '1';

                    if ($summary_to_date == 'on') {
                        $option = '2';
                    } else if ($summary_closing == 'on') {
                        $option = '3';
                    }

                    if ($summary_to_date == 'on') {
                        $query = $this->select_al_dia_de_hoy_terminal($group_id, $atm_id);
                    } else {
                        $query = $this->select_al_cierre_terminal($option, $group_id, $atm_id, $from, $to);
                    }

                    $list = \DB::select($query);

                    break;

                case 'cuotas':

                    $query = "select * from view_get_atm_quota_sales_detail where group_id = $group_id";
                    $list = \DB::select($query);

                    if (count($list) <= 0) {
                        $query = "select * from view_get_atm_quota_rental_detail where group_id = $group_id";
                        $list = \DB::select($query);
                    }
                    break;

                case 'multas':

                    $list = \DB::table('mt_penaltys as mp')
                        ->select(
                            'mpt.description as penalty_type',
                            'mp.id as penalty_id',
                            'mp.sale_id',

                            \DB::raw("trim(replace(to_char(mp.amount_penalty, '999G999G999G999G999'), '.', ',')) as amount_penalty"),
                            \DB::raw("trim(replace(to_char(mp.amount_discount, '999G999G999G999G999'), '.', ',')) as amount_discount"),
                            \DB::raw("trim(replace(to_char(mp.amount_total_to_pay, '999G999G999G999G999'), '.', ',')) as amount_total_to_pay"),

                            \DB::raw("to_char(mp.created_at, 'DD/MM/YYYY HH24:MI:SS') as created_at"),
                            'mp.observation'
                        )
                        ->join('mt_penalty_type as mpt', 'mpt.id', '=', 'mp.penalty_type_id')
                        ->join('mt_sales as ms', 'ms.id', '=', 'mp.sale_id')
                        ->join('mt_movements as mm', 'mm.id', '=', 'ms.movements_id')
                        ->where('mm.group_id', $group_id)
                        ->get();

                    break;

                case 'block_type':

                    $user_id = $request['user_id'];
                    $block_type_id = $request['block_type_id'];
                    $commentary = $request['commentary'];

                    $atms = \DB::table('business_groups as bg')
                        ->select(
                            'a.id as atm_id'
                        )
                        ->join('branches as b', 'bg.id', '=', 'b.group_id')
                        ->join('points_of_sale as pos', 'b.id', '=', 'pos.branch_id')
                        ->join('atms as a', 'a.id', '=', 'pos.atm_id')
                        ->where('bg.id', $group_id)
                        ->whereRaw('a.deleted_at is null')
                        ->get();

                    \Log::info("atms a modificar: ", [$atms]);

                    if (count($atms) > 0) {
                        foreach ($atms as $item) {
                            $atm_id = $item->atm_id;

                            $parameters = [
                                'atm_id' => $atm_id,
                                'user_id' => $user_id,
                                'block_type_id' => $block_type_id,
                                'commentary' => $commentary
                            ];

                            $block_type_change_response = $this->block_type_change($parameters);
                            $error = $block_type_change_response['error'];
                            $message = $block_type_change_response['message'];

                            if ($error) {
                                break;
                            }
                        }
                    } else {
                        $error = true;
                        $message = 'Los terminales de este grupo se encuentran eliminados.';
                    }

                    break;

                case 'reglas_atm':

                    $atm_id = $request['atm_id'];
                    $query = "select * from view_get_group_rules where atm_id = $atm_id";
                    $list = \DB::select($query);

                    break;
                    
                case 'multas_atm':

                    $list = \DB::table('mt_penaltys as mp')
                        ->select(
                            'mpt.description as penalty_type',
                            'mp.id as penalty_id',
                            'mp.sale_id',

                            \DB::raw("trim(replace(to_char(mp.amount_penalty, '999G999G999G999G999'), '.', ',')) as amount_penalty"),
                            \DB::raw("trim(replace(to_char(mp.amount_discount, '999G999G999G999G999'), '.', ',')) as amount_discount"),
                            \DB::raw("trim(replace(to_char(mp.amount_total_to_pay, '999G999G999G999G999'), '.', ',')) as amount_total_to_pay"),

                            \DB::raw("to_char(mp.created_at, 'DD/MM/YYYY HH24:MI:SS') as created_at"),
                            'mp.observation'
                        )
                        ->join('mt_penalty_type as mpt', 'mpt.id', '=', 'mp.penalty_type_id')
                        ->join('mt_sales as ms', 'ms.id', '=', 'mp.sale_id')
                        ->join('mt_movements as mm', 'mm.id', '=', 'ms.movements_id')
                        ->where('mm.atm_id', $atm_id)
                        ->get();

                    break;
            }

            \Log::info("query de group: $query");

            $list = json_decode(json_encode($list), true);
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
            'error' => $error,
            'message' => $message,
            'list' => $list,
            'error_detail' => $error_detail
        ];

        return $data;
    }

    public function select_al_dia_de_hoy_grupo($where, $where_atm)
    {

        $separador = "999G999G999G999G999";

        $query = "
            select
                sq.group_id,
                sq.group_description,

                trim(to_char(
                    sq.total_depositos + sq.total_transaccionado + sq.total_reversiones + sq.total_cashouts + sq.total_pago_cashout + sq.total_pago_qr + sq.total_quota_sales + sq.total_quota_rental + sq.total_multa, '$separador'
                )) as saldo,
                
                trim(to_char(
                    sq.total_depositos + sq.total_transaccionado_cierre + sq.total_reversiones + sq.total_cashouts + sq.total_pago_cashout + sq.total_pago_qr + sq.total_quota_sales + sq.total_quota_rental + sq.total_multa, '$separador'
                )) as saldo_cierre,

                trim(to_char(sq.total_depositos, '$separador')) as total_depositos,
                trim(to_char(sq.total_transaccionado, '$separador')) as total_transaccionado,
                trim(to_char(sq.total_transaccionado_cierre, '$separador')) as total_transaccionado_cierre,
                trim(to_char(sq.total_reversiones, '$separador')) as total_reversiones,
                trim(to_char(sq.total_cashouts, '$separador')) as total_cashouts,
                trim(to_char(sq.total_pago_cashout, '$separador')) as total_pago_cashout,
                trim(to_char(sq.total_pago_qr, '$separador')) as total_pago_qr,
                trim(to_char(sq.total_quota_sales, '$separador')) as total_quota_sales,
                trim(to_char(sq.total_quota_rental, '$separador')) as total_quota_rental,
                trim(to_char(sq.total_quota_sales + sq.total_quota_rental, '$separador')) as total_cuota,

                trim(to_char(sq.total_multa, '$separador')) as total_multa,
                
                coalesce((select vggs.status from view_get_group_status vggs where sq.group_id = vggs.group_id), 'sin estado') as estado,

                case
                    when ((select vggr.group_id from view_get_group_rules vggr where vggr.group_id = sq.group_id group by vggr.group_id)) is not null then 'Con Regla'
                    else 'Sin Regla'
                end as regla,

                coalesce(sq.regla_conteo ->> 'con_regla', '0') as atms_con_regla,
                coalesce(sq.regla_conteo ->> 'sin_regla', '0') as atms_sin_regla,

                case
                    when ((sq.regla_conteo ->> 'con_regla')::integer) > 0 then 'Con Regla'
                    else null
                end as atms_con_regla_descripcion,

                case
                    when ((sq.regla_conteo ->> 'sin_regla')::integer) > 0 then 'Sin Regla'
                    else null
                end as atms_sin_regla_descripcion
            from (
                select
                    bg.id as group_id,
                    (('# ' || bg.id) || ' | ') || bg.description as group_description,
                    coalesce(sum(ba.total_depositado), 0) as total_depositos,
                    coalesce(sum(ba.total_transaccionado), 0) as total_transaccionado,
                    coalesce(sum(ba.total_transaccionado_cierre), 0) as total_transaccionado_cierre,
                    coalesce(sum(ba.total_reversado), 0) as total_reversiones,
                    coalesce(sum(ba.total_cashout), 0) as total_cashouts,
                    coalesce(sum(ba.total_pago_cashout), 0) as total_pago_cashout,
                    coalesce(sum(ba.total_pago_qr), 0) as total_pago_qr,
                    coalesce(sum(ba.total_multa), 0) as total_multa,

                    (coalesce((select vggqr.total from view_get_group_quota_rental vggqr where vggqr.group_id = bg.id), 0)) as total_quota_sales,
                    (coalesce((select vggqs.total from view_get_group_quota_sales vggqs where vggqs.group_id = bg.id), 0)) as total_quota_rental,

                    (select vgrcbga.conteo from view_get_rule_count_by_group_atm vgrcbga where vgrcbga.group_id = bg.id) as regla_conteo
                from balance_atms ba
                join atms a on a.id = ba.atm_id
                join points_of_sale pos on a.id = pos.atm_id
                join branches b on b.id = pos.branch_id
                join business_groups bg on bg.id = b.group_id
                $where
                $where_atm
                group by bg.id
            ) sq;
        ";

        return $query;
    }

    public function select_al_cierre_grupo($where, $where_atm, $query_type, $date_from, $date_to)
    {

        $separador = "999G999G999G999G999";

        $sub_query = '';

        switch ($query_type) {

            case 1:
                $sub_query = "
                    'transaccionado', coalesce((sum(case when m.debit_credit = 'de' and ms.fecha between '$date_from' and '$date_to' and m.movement_type_id not in (18) then (m.amount) else 0 end)), 0),
                    'depositos', coalesce(sum(case when m.movement_type_id = 2 and m.created_at between '$date_from' and '$date_to' then (m.amount) else 0 end), 0),
                    'reversiones', coalesce(sum(case when m.movement_type_id = 3 and m.created_at between '$date_from' and '$date_to' then (m.amount) else 0 end), 0),
                    'cashouts', coalesce(sum(case when m.movement_type_id = 11 and m.created_at between '$date_from' and '$date_to' then (m.amount) else 0 end), 0),
                    'pago_qr', coalesce(sum(case when m.movement_type_id = 17 and m.created_at between '$date_from' and '$date_to' then (m.amount) else 0 end), 0),
                    'multa', coalesce(sum(case when m.movement_type_id = 18 and m.created_at between '$date_from' and '$date_to' then (m.amount) else 0 end), 0)
                ";
                break;

            case 2:
                $sub_query = "
                    'transaccionado', coalesce((sum(case when m.debit_credit = 'de' and m.created_at <= now() and m.movement_type_id not in (18) then (m.amount) else 0 end)), 0),
                    'depositos', coalesce(sum(case when m.movement_type_id = 2 and m.created_at <= now() then (m.amount) else 0 end), 0),
                    'reversiones', coalesce(sum(case when m.movement_type_id = 3 and m.created_at <= now() then (m.amount) else 0 end), 0),
                    'cashouts', coalesce(sum(case when m.movement_type_id = 11 and m.created_at <= now() then (m.amount) else 0 end), 0),
                    'pago_qr', coalesce(sum(case when m.movement_type_id = 17 and m.created_at <= now() then (m.amount) else 0 end), 0),
                    'multa', coalesce(sum(case when m.movement_type_id = 18 and m.created_at <= now() then (m.amount) else 0 end), 0)
                ";
                break;

            case 3:

                $day = date('N');

                if ($day == 1 or $day == 3 or $day == 5) {
                    $days = '-1 day';
                } else if ($day == 2 or $day == 4 or $day == 6) {
                    $days = '-2 day';
                } else {
                    $days = '-3 day';
                }

                $date_to_nano = Carbon::parse(date('Y-m-d 23:59:59'))->modify('-1 day');
                $date_to_mini = Carbon::parse(date('Y-m-d 23:59:59'))->modify($days);

                $sub_query = "
                    'transaccionado', coalesce((                                          
                        sum(
                            case
                                when m.debit_credit = 'de' and a.owner_id in (16, 21, 25) and a.grilla_tradicional = false and ms.fecha <= '$date_to_nano' and m.movement_type_id not in (18) then m.amount
                                when m.debit_credit = 'de' and a.owner_id in (16) and a.grilla_tradicional = true and ms.fecha <= '$date_to_mini'  and m.movement_type_id not in (18) then m.amount
                                else 0
                            end
                        )   
                    ), 0),
                    'depositos', coalesce(sum(case when m.movement_type_id = 2 and m.created_at <= now() then (m.amount) else 0 end), 0),
                    'reversiones', coalesce(sum(case when m.movement_type_id = 3 and m.created_at <= now() then (m.amount) else 0 end), 0),
                    'cashouts', coalesce(sum(case when m.movement_type_id = 11 and m.created_at <= now() then (m.amount) else 0 end), 0),
                    'pago_qr', coalesce(sum(case when m.movement_type_id = 17 and m.created_at <= now() then (m.amount) else 0 end), 0),
                    'multa', coalesce(sum(case when m.movement_type_id = 18 and m.created_at <= now() then (m.amount) else 0 end), 0)
                ";
                break;
        }

        $query = "
            select 
                sq.group_id,
                sq.group_description,

                trim(to_char((sq.transaccionado + sq.depositos + sq.reversiones + sq.cashouts + sq.pago_qr + sq.cuota_venta + sq.cuota_alquiler + sq.multa), '$separador')) as saldo,

                coalesce(sq.estado, 'Sin estado') as estado,

                coalesce(sq.regla, 'Sin Regla') as regla,

                coalesce(regla_conteo->>'con_regla', '0') as atms_con_regla,
                coalesce(regla_conteo->>'sin_regla', '0') as atms_sin_regla,

                (case when (regla_conteo->>'con_regla')::integer > 0 then 'Con Regla' end) as atms_con_regla_descripcion,
                (case when (regla_conteo->>'sin_regla')::integer > 0 then 'Sin Regla' end) as atms_sin_regla_descripcion,

                trim(to_char((sq.multa), '$separador')) as total_multa,
                trim(to_char(sq.transaccionado, '$separador')) as total_transaccionado,
                trim(to_char(sq.depositos, '$separador')) as total_depositos,
                trim(to_char(sq.reversiones, '$separador')) as total_reversiones,
                trim(to_char(sq.cashouts, '$separador')) as total_cashouts,
                trim(to_char(sq.pago_qr, '$separador')) as total_pago_qr,
                trim(to_char(sq.cuota_venta, '$separador')) as total_cuota_venta,
                trim(to_char(sq.cuota_alquiler, '$separador')) as total_cuota_alquiler,
                trim(to_char((sq.cuota_venta + sq.cuota_alquiler), '$separador')) as total_cuota
            
            from (
                select 
                    sq.group_id,
                    sq.group_description,

                    round(sum((sq.total ->> 'transaccionado')::numeric)) as transaccionado,
                    round(sum((sq.total ->> 'depositos')::numeric)) as depositos,
                    round(sum((sq.total ->> 'reversiones')::numeric)) as reversiones,
                    round(sum((sq.total ->> 'cashouts')::numeric)) as cashouts,
                    round(sum((sq.total ->> 'pago_qr')::numeric)) as pago_qr,

                    coalesce((select vggqs.total from view_get_group_quota_sales as vggqs where vggqs.group_id = sq.group_id), 0) as cuota_venta,
                    coalesce((select vggqr.total from view_get_group_quota_rental as vggqr where vggqr.group_id = sq.group_id), 0) as cuota_alquiler,

                    round(sum((sq.total ->> 'multa')::numeric)) as multa,

                    (select vggs.status from view_get_group_status vggs where vggs.group_id = sq.group_id limit 1) as estado,

                    (select 'Con Regla' from view_get_group_rules as vggr where vggr.group_id = sq.group_id group by vggr.group_id) as regla,

                    (select vgrcbga.conteo from view_get_rule_count_by_group_atm as vgrcbga where vgrcbga.group_id = sq.group_id) as regla_conteo

                from (
                    select
                        bg.id as group_id,
                        (' # ' || bg.id || ' | ' || bg.description || ' | ' || bg.ruc) as group_description,
                        (
                            coalesce(
                                (
                                    select
                                        json_build_object(
                                            $sub_query
                                        )
                                    from
                                        mt_movements as m
                                    join atms as a on a.id = m.atm_id
                                    left join mt_sales as ms on m.id = ms.movements_id
                                    where m.movement_type_id not in (4, 5, 7, 8, 9, 10)
                                    and m.deleted_at is null
                                    and m.group_id = bg.id
                                    
                                    $where_atm

                                ), 
                                json_build_object(
                                    'transaccionado', 0,
                                    'depositos', 0,
                                    'reversiones', 0,
                                    'cashouts', 0,
                                    'pago_qr', 0
                                )
                            )
                        ) as total
                    from business_groups as bg

                    where 1 = 1
                    $where

                    group by bg.id
                    order by bg.id
                ) as sq
                where sq.group_description is not null
                group by sq.group_id, sq.group_description
            ) as sq
            order by saldo            
        ";

        //\Log::info("QUERY:$query");

        return $query;
    }

    public function select_al_dia_de_hoy_terminal($group_id)
    {

        $where = "where 1 = 1";

        if ($group_id !== null and $group_id !== '') {
            $where .= " and bg.id = $group_id";
        }

        $separador = "999G999G999G999G999";

        $query = "
            select 
                sq.group_id,
                sq.atm_id,
                sq.atm_description,
                sq.atm_last_request,
                sq.estado,
                trim(to_char(sq.total_depositos + sq.total_transaccionado + sq.total_reversiones + sq.total_cashouts + sq.total_pago_cashout + sq.total_pago_qr + sq.total_quota_sales + sq.total_quota_rental + sq.total_multa, '$separador')) as saldo,
                trim(to_char(sq.total_depositos + sq.total_transaccionado + sq.total_reversiones + sq.total_cashouts + sq.total_pago_cashout + sq.total_pago_qr + sq.total_quota_sales + sq.total_quota_rental + sq.total_multa, '$separador')) as saldo_cierre,
                trim(to_char(sq.total_depositos, '$separador')) as total_depositos,
                trim(to_char(sq.total_transaccionado, '$separador')) as total_transaccionado,
                trim(to_char(sq.total_transaccionado_cierre, '$separador')) as total_transaccionado_cierre,
                trim(to_char(sq.total_reversiones, '$separador')) as total_reversiones,
                trim(to_char(sq.total_cashouts, '$separador')) as total_cashouts,
                trim(to_char(sq.total_pago_cashout, '$separador')) as total_pago_cashout,
                trim(to_char(sq.total_pago_qr, '$separador')) as total_pago_qr,
                trim(to_char(sq.total_quota_sales, '$separador')) as total_quota_sales,
                trim(to_char(sq.total_quota_rental, '$separador')) as total_quota_rental,
                trim(to_char(sq.total_quota_sales + sq.total_quota_rental, '$separador')) as total_cuota,
                trim(to_char(sq.total_multa, '$separador')) as total_multa
            from ( 
                select 
                    bg.id as group_id,
                    a.id as atm_id,
                    (('# ' || a.id) || ' | ') || a.name as atm_description,
                    a.last_request_at as atm_last_request,
                    coalesce(ba.total_depositado, 0) as total_depositos,
                    coalesce(ba.total_transaccionado, 0) as total_transaccionado,
                    coalesce(ba.total_transaccionado_cierre, 0) as total_transaccionado_cierre,
                    coalesce(ba.total_reversado, 0) as total_reversiones,
                    coalesce(ba.total_cashout, 0) as total_cashouts,
                    coalesce(ba.total_pago_cashout, 0) as total_pago_cashout,
                    coalesce(ba.total_pago_qr, 0) as total_pago_qr,
                    coalesce((select vgaqs.total from view_get_atm_quota_sales vgaqs where ba.atm_id = vgaqs.atm_id), 0) as total_quota_sales,
                    coalesce((select vgaqr.total from view_get_atm_quota_rental vgaqr where ba.atm_id = vgaqr.atm_id), 0) as total_quota_rental,
                    coalesce((select vgas.status from view_get_atm_status vgas where vgas.atm_id = a.id), 'sin estado') as estado,
                    coalesce(ba.total_multa, 0) as total_multa
                from atms a
                join points_of_sale pos on a.id = pos.atm_id
                join branches b on b.id = pos.branch_id
                join business_groups bg on bg.id = b.group_id
                left join balance_atms ba on a.id = ba.atm_id
                $where
            ) sq;
        ";

        return $query;
    }

    public function select_al_cierre_terminal($query_type, $group_id, $atm_id, $date_from, $date_to)
    {

        $where = "where 1 = 1";

        if ($group_id !== null and $group_id !== '') {

            $select = \DB::table('business_groups as bg')
                ->select(
                    \DB::raw("string_agg(a.id::text, ', ') as ids")
                )
                ->join('branches as b', 'bg.id', '=', 'b.group_id')
                ->join('points_of_sale as pos', 'b.id', '=', 'pos.branch_id')
                ->join('atms as a', 'a.id', '=', 'pos.atm_id')
                ->where('bg.id', $group_id)
                ->whereRaw('a.deleted_at is null')
                ->get();

            $ids = $select[0]->ids;

            $where .= " and a.id in ($ids)";
        }

        if ($atm_id !== null and $atm_id !== '') {
            $where = " and a.id = $atm_id";
        }

        $separador = "999G999G999G999G999";

        $sub_query = '';

        switch ($query_type) {

            case 1:
                $sub_query = "
                    'transaccionado', coalesce((sum(case when m.debit_credit = 'de' and ms.fecha between '$date_from' and '$date_to' and m.movement_type_id not in (18) then (m.amount) else 0 end)), 0),
                    'depositos', coalesce(sum(case when m.movement_type_id = 2 and m.created_at between '$date_from' and '$date_to' then (m.amount) else 0 end), 0),
                    'reversiones', coalesce(sum(case when m.movement_type_id = 3 and m.created_at between '$date_from' and '$date_to' then (m.amount) else 0 end), 0),
                    'cashouts', coalesce(sum(case when m.movement_type_id = 11 and m.created_at between '$date_from' and '$date_to' then (m.amount) else 0 end), 0),
                    'pago_qr', coalesce(sum(case when m.movement_type_id = 17 and m.created_at between '$date_from' and '$date_to' then (m.amount) else 0 end), 0),
                    'multa', coalesce(sum(case when m.movement_type_id = 18 and m.created_at between '$date_from' and '$date_to' then (m.amount) else 0 end), 0)
                ";
                break;

            case 2:
                $sub_query = "
                    'transaccionado', coalesce((sum(case when m.debit_credit = 'de' and m.created_at <= now() and m.movement_type_id not in (18) then (m.amount) else 0 end)), 0),
                    'depositos', coalesce(sum(case when m.movement_type_id = 2 and m.created_at <= now() then (m.amount) else 0 end), 0),
                    'reversiones', coalesce(sum(case when m.movement_type_id = 3 and m.created_at <= now() then (m.amount) else 0 end), 0),
                    'cashouts', coalesce(sum(case when m.movement_type_id = 11 and m.created_at <= now() then (m.amount) else 0 end), 0),
                    'pago_qr', coalesce(sum(case when m.movement_type_id = 17 and m.created_at <= now() then (m.amount) else 0 end), 0),
                    'multa', coalesce(sum(case when m.movement_type_id = 18 and m.created_at <= now() then (m.amount) else 0 end), 0)
                ";
                break;

            case 3:

                $day = date('N');

                if ($day == 1 or $day == 3 or $day == 5) {
                    $days = '-1 day';
                } else if ($day == 2 or $day == 4 or $day == 6) {
                    $days = '-2 day';
                } else {
                    $days = '-3 day';
                }

                $date_to_nano = Carbon::parse(date('Y-m-d 23:59:59'))->modify('-1 day');
                $date_to_mini = Carbon::parse(date('Y-m-d 23:59:59'))->modify($days);

                $sub_query = "
                    'transaccionado', coalesce((                                          
                        sum(
                            case
                                when m.debit_credit = 'de' and a.owner_id in (16, 21, 25) and a.grilla_tradicional = false and ms.fecha <= '$date_to_nano' and m.movement_type_id not in (18) then m.amount
                                when m.debit_credit = 'de' and a.owner_id in (16) and a.grilla_tradicional = true and ms.fecha <= '$date_to_mini'  and m.movement_type_id not in (18) then m.amount
                                else 0
                            end
                        )   
                    ), 0),
                    'depositos', coalesce(sum(case when m.movement_type_id = 2 and m.created_at <= now() then (m.amount) else 0 end), 0),
                    'reversiones', coalesce(sum(case when m.movement_type_id = 3 and m.created_at <= now() then (m.amount) else 0 end), 0),
                    'cashouts', coalesce(sum(case when m.movement_type_id = 11 and m.created_at <= now() then (m.amount) else 0 end), 0),
                    'pago_qr', coalesce(sum(case when m.movement_type_id = 17 and m.created_at <= now() then (m.amount) else 0 end), 0),
                    'multa', coalesce(sum(case when m.movement_type_id = 18 and m.created_at <= now() then (m.amount) else 0 end), 0)
                ";
                break;
        }

        $query = "
            select 
                sq.atm_id,
                sq.atm_description,
                sq.atm_last_request_at,
                trim(to_char(
                    (
                        sq.transaccionado +
                        sq.depositos +
                        sq.reversiones +
                        sq.cashouts +
                        sq.pago_qr +
                        sq.cuota_venta + 
                        sq.cuota_alquiler +
                        sq.multa
                    ), 
                    '$separador'
                )) as saldo,
                    
                coalesce(sq.estado, ''Sin estado'') as estado,

                trim(to_char((sq.multa), '$separador')) as total_multa
                trim(to_char(sq.transaccionado, '$separador')) as total_transaccionado,
                trim(to_char(sq.depositos, '$separador')) as total_depositos,
                trim(to_char(sq.reversiones, '$separador')) as total_reversiones,
                trim(to_char(sq.cashouts, '$separador')) as total_cashouts,
                trim(to_char(sq.pago_qr, '$separador')) as total_pago_qr,
                trim(to_char(sq.cuota_venta, '$separador')) as total_cuota_venta,
                trim(to_char(sq.cuota_alquiler, '$separador')) as total_cuota_alquiler,
                trim(to_char((sq.cuota_venta + sq.cuota_alquiler), '$separador')) as total_cuota
            
            from (
                select 
                    sq.atm_id,
                    sq.atm_description,
                    sq.atm_last_request_at,
                    round(sum((sq.total ->> 'multa')::numeric)) as multa,
                    round(sum((sq.total ->> 'transaccionado')::numeric)) as transaccionado,
                    round(sum((sq.total ->> 'depositos')::numeric)) as depositos,
                    round(sum((sq.total ->> 'reversiones')::numeric)) as reversiones,
                    round(sum((sq.total ->> 'cashouts')::numeric)) as cashouts,
                    round(sum((sq.total ->> 'pago_qr')::numeric)) as pago_qr,
                    coalesce((select vaaqs.total from view_get_atm_quota_sales as vaaqs where vaaqs.atm_id = sq.atm_id), 0) as cuota_venta,
                    coalesce((select vaaqr.total from view_get_atm_quota_rental as vaaqr where vaaqr.atm_id = sq.atm_id), 0) as cuota_alquiler,
                    (select vaas.status from view_get_atm_status vaas where vaas.atm_id = sq.atm_id limit 1) as estado
                from (
                    select
                        a.id as atm_id,
                        (' # ' || a.id || ' | ' || a.name) as atm_description,
                        to_char(a.last_request_at, ''DD/MM/YYYY HH24:MI:SS'') as atm_last_request_at,
                        (
                            coalesce(
                                (
                                    select
                                        json_build_object(
                                            $sub_query
                                        )
                                    from
                                        mt_movements as m
                                    join atms as a2 on a2.id = m.atm_id
                                    left join mt_sales as ms on m.id = ms.movements_id
                                    where m.movement_type_id not in (4, 5, 7, 8, 9, 10)
                                    and m.deleted_at is null
                                    and m.atm_id = a.id
                                ), 
                                json_build_object(
                                    'transaccionado', 0,
                                    'depositos', 0,
                                    'reversiones', 0,
                                    'cashouts', 0,
                                    'pago_qr', 0,
                                    'multa', 0
                                )
                            )
                        ) as total
                    from atms as a
                    where 1 = 1

                    group by a.id
                    order by a.id
                ) as sq
                where sq.atm_description is not null
                group by sq.atm_id, sq.atm_description, sq.atm_last_request_at
            ) as sq
            order by saldo            
        ";

        //\Log::info("QUERY:$query");

        return $query;
    }
}
