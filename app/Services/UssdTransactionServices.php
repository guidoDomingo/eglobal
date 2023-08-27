<?php

/**
 * User: avisconte
 * Date: 05/04/2021
 * Time: 11:00 am
 */

namespace App\Services;

use App\Exports\ExcelExport;
use Carbon\Carbon;
use Excel;

class UssdTransactionServices
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
    public function custom_error($e, $function)
    {
        $file = $e->getFile();
        $line = $e->getLine();
        $message = $e->getMessage();

        $error_detail = [
            "exception_message" => $message,
            "file"              => $file,
            "class"             => __CLASS__,
            "function"          => $function,
            "line"              => $line
        ];

        \Log::error('Ocurrió una excepción:', $error_detail);

        return $error_detail;
    }

    function ussd_transaction_report($request)
    {
        $list = array();
        $totals = array();
        $days = 0;
        $total = 0;
        $totals_status = 0;

        try {

            $time_init = '00:00:00';
            $time_end = '23:59:59';
            $from = date("d/m/Y");
            $to = date("d/m/Y");
            $timestamp = "$from $time_init - $to $time_end";

            $filters = [
                'timestamp' => $timestamp,
                'record_limit' => '',
                'menu_ussd_status_id' => '',
                'phone_number' => '',
                'atm_id' => '',
                'branch' => '',
                'service_id' => ''
            ];

            $list = $this->get_transactions($filters);
            $totals_status = $this->get_total_by_state($filters);
            $days = $this->get_days($filters);
            $totals = $this->get_total_transactions($filters);

            $message = 'La página cargó correctamente.';
            $message_type = 'message';
        } catch (\Exception $e) {
            $message = 'Error al crear datos de documento.';
            $message_type = 'error_message';
        }

        $data = [
            'filters' => $filters,
            'totals_status' => $totals_status,
            'totals' => $totals,
            'days' => $days,
            'list' => $list,
            'total' => $total
        ];

        //\Session::flash($message_type, $message);
        return view('ussd.ussd_transaction_report', compact('data'));
    }

    function ussd_transaction_relaunch($request, $user_id)
    {
        $message = 'Transacciones no relanzadas.';
        $error = false;
        $error_detail = null;
        $count = 0;

        try {
            $list = $this->get_transactions($request);

            for ($i = 0; $i < count($list); $i++) {

                $item = $list[$i];

                if ($item['menu_ussd_status_id'] == 3) {
                    $items = [
                        'id' => $item['operation'],
                        'replacement_number' => $item['phone_number'],
                        'reason' => 4,  # Error del sistema
                        'recharge_type' => 1, # Carga sin cambios
                        'commentary' => null,
                    ];

                    $data = $this->ussd_transaction_edit($items, $user_id);
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
            \Log::error($message, $error_detail);
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

    function ussd_transaction_search($request)
    {
        $list = array();
        $totals = array();
        $data_to_excel = array();
        $days = null;
        $total = null;
        $totals_status = null;

        try {
            $timestamp = $request['timestamp'];
            $record_limit = $request['record_limit'];
            $menu_ussd_status_id = $request['menu_ussd_status_id'];
            $phone_number = $request['phone_number'];
            $atm_id = $request['atm_id'];
            $branch = $request['branch'];
            $service_id = $request['service_id'];
            $submit = '';

            if (isset($request['search'])) {
                $submit = 'search';
            } else {
                $submit = 'generate_x';
            }

            $filters = [
                'timestamp' => $timestamp,
                'record_limit' => $record_limit,
                'menu_ussd_status_id' => $menu_ussd_status_id,
                'phone_number' => $phone_number,
                'atm_id' => $atm_id,
                'branch' => $branch,
                'service_id' => $service_id
            ];

            $list = $this->get_transactions($filters);
            $totals_status = $this->get_total_by_state($filters);
            $days = $this->get_days($filters);
            $totals = $this->get_total_transactions($filters);

            if ($submit == 'generate_x') {
                if (count($list) > 0) {
                    foreach ($list as $item) {
                        $item['amount'] = str_replace('.', '', $item['amount']);

                        $item = [
                            $item['transaction_id'],
                            $item['option'],
                            $item['menu_ussd_status'],
                            $item['phone_number'],
                            $item['amount'],
                            $item['atm'],
                            $item['created_at']
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
                    //     $excel->sheet('sheet1', function ($sheet) use ($data_to_excel) {
                    //         $sheet->rows($data_to_excel, false);
                    //         $sheet->prependRow(array(
                    //             'ID TRANSACCIÓN', 'TIPO', 'ESTADO', 'CONTACTO', 'MONTO', 'TERMINAL', 'FECHA Y HORA'
                    //         ));
                    //     });
                    // })->export('xls');
                    //exit();
                } else {
                    $message = 'No hay registros según los filtros seleccionados.';
                }
            } else {
                $message = 'Registros obtenidos correctamente.';
            }

            $message_type = 'message';
        } catch (\Exception $e) {
            $message = 'Error al crear datos de documento.';
            $message_type = 'error_message';
        }

        $data = [
            'filters' => $filters,
            'totals_status' => $totals_status,
            'totals' => $totals,
            'days' => $days,
            'list' => $list,
            'total' => $total
        ];

        \Session::flash($message_type, $message);
        return view('ussd.ussd_transaction_report', compact('data'));
    }

    /**
     * Edita una transacción.
     */
    function ussd_transaction_edit($request, $user_id)
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

            $record = \DB::table('ussd.menu_ussd_detail_client')->where('id', $id)->get();

            if ($record > 0) {
                $item = $record[0];
                $menu_ussd_status_id = $item->menu_ussd_status_id;
                $amount = $item->amount;
                $menu_ussd_detail_id = $item->menu_ussd_detail_id;
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

                    if ($recharge_type == 2) { # Tipo de recarga normal.
                        $menu_ussd = \DB::table('ussd.menu_ussd')
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
                        }
                    }

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

                    $message = "Recarga N° $id actualizada correctamente.";
                } else {
                    $message = 'No se puede modificar este registro.';
                }
            } else {
                $message = 'No existe el registro.';
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
     * Obtiene el total por cada estado de transacción
     */
    function get_total_by_state($filters)
    {
        $list = array();

        try {
            $timestamp = $filters['timestamp'];
            $record_limit = $filters['record_limit'];
            $menu_ussd_status_id = $filters['menu_ussd_status_id'];
            $phone_number = $filters['phone_number'];
            $atm_id = $filters['atm_id'];
            $branch = $filters['branch'];
            $service_id = $filters['service_id'];

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
            }, $menu_ussd_status_list);

            $records_list = \DB::table('ussd.menu_ussd_detail_client as mudc')
                ->select(
                    'mudc.menu_ussd_status_id',
                    \DB::raw("count(mudc.menu_ussd_status_id) as total"),
                    \DB::raw("sum(mudc.amount) as total_amount")
                )
                ->join('ussd.menu_ussd_detail as mud', 'mud.id', '=', 'mudc.menu_ussd_detail_id')
                ->join('ussd.menu_ussd_status as mus', 'mus.id', '=', 'mudc.menu_ussd_status_id')

                ->join('transactions as t', 't.id', '=', 'mudc.transaction_id')
                ->join('points_of_sale as pos', 't.atm_id', '=', 'pos.atm_id')
                ->join('atms as a', 'a.id', '=', 't.atm_id')
                ->join('branches as b', 'b.id', '=', 'pos.branch_id');

            if ($phone_number !== '') {
                $records_list = $records_list->where('mudc.phone_number', $phone_number);
            }

            if ($menu_ussd_status_id !== '') {
                $menu_ussd_status_id = intval($menu_ussd_status_id);
                $records_list = $records_list->where('mudc.menu_ussd_status_id', $menu_ussd_status_id);
            }

            if ($atm_id !== '') {
                $atm_id = intval($atm_id);
                $records_list = $records_list->where('t.atm_id', $atm_id);
            }

            if ($branch !== '') {
                $branch = intval($branch);
                $records_list = $records_list->where('b.id', $branch);
            }

            if ($service_id !== '') {
                $service_id = intval($service_id);
                $records_list = $records_list->where('mud.service_id', $service_id);
            }

            if ($timestamp !== null) {
                $aux  = explode(' - ', str_replace('/', '-', $timestamp));
                $from = date('Y-m-d H:i:s', strtotime($aux[0]));
                $to   = date('Y-m-d H:i:s', strtotime($aux[1]));
                $records_list = $records_list->whereRaw("mudc.created_at between '{$from}' and '{$to}'");
            }

            $records_list = $records_list->groupBy('mudc.menu_ussd_status_id');

            if ($record_limit !== '') {
                $records_list = $records_list->take(intval($record_limit));
            }

            //\Log::info($records_list->toSql());

            //La ultima sentencia del select.
            $records_list = $records_list->get();

            $menu_ussd_detail_client_list = array_map(function ($value) {
                return (array) $value;
            }, $records_list);

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
     * Obtine todas las transacciones según los filtros.
     */
    function get_total_transactions($filters)
    {
        $list = [
            "total" => 0,
            "total_amount" => 0
        ];

        try {
            $timestamp = $filters['timestamp'];
            $record_limit = $filters['record_limit'];
            $menu_ussd_status_id = $filters['menu_ussd_status_id'];
            $phone_number = $filters['phone_number'];
            $atm_id = $filters['atm_id'];
            $branch = $filters['branch'];
            $service_id = $filters['service_id'];

            $records_list = \DB::table('ussd.menu_ussd_detail_client as mudc')
                ->select(
                    \DB::raw('coalesce(count(mudc.id), 0) as total'),
                    \DB::raw('coalesce(sum(mudc.amount), 0) as total_amount')
                )
                ->join('ussd.menu_ussd_detail as mud', 'mud.id', '=', 'mudc.menu_ussd_detail_id')

                ->join('transactions as t', 't.id', '=', 'mudc.transaction_id')
                ->join('points_of_sale as pos', 't.atm_id', '=', 'pos.atm_id')
                ->join('atms as a', 'a.id', '=', 't.atm_id')
                ->join('branches as b', 'b.id', '=', 'pos.branch_id');

            if ($phone_number !== '') {
                $records_list = $records_list->where('mudc.phone_number', $phone_number);
            }

            if ($menu_ussd_status_id !== '') {
                $menu_ussd_status_id = intval($menu_ussd_status_id);
                $records_list = $records_list->where('mudc.menu_ussd_status_id', $menu_ussd_status_id);
            }

            if ($atm_id !== '') {
                $atm_id = intval($atm_id);
                $records_list = $records_list->where('t.atm_id', $atm_id);
            }

            if ($branch !== '') {
                $branch = intval($branch);
                $records_list = $records_list->where('b.id', $branch);
            }

            if ($service_id !== '') {
                $service_id = intval($service_id);
                $records_list = $records_list->where('mud.service_id', $service_id);
            }

            if ($timestamp !== null) {
                $aux  = explode(' - ', str_replace('/', '-', $timestamp));
                $from = date('Y-m-d H:i:s', strtotime($aux[0]));
                $to   = date('Y-m-d H:i:s', strtotime($aux[1]));
                $records_list = $records_list->whereRaw("mudc.created_at between '{$from}' and '{$to}'");
            }

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
     * Obtine todas las transacciones que dieron error.
     */
    function get_transactions($filters)
    {
        $list = array();

        try {
            $timestamp = $filters['timestamp'];
            $record_limit = $filters['record_limit'];
            $menu_ussd_status_id = $filters['menu_ussd_status_id'];
            $phone_number = $filters['phone_number'];
            $atm_id = $filters['atm_id'];
            $branch = $filters['branch'];
            $service_id = $filters['service_id'];

            $records_list = \DB::table('ussd.menu_ussd_detail_client as mudc')
                ->select(
                    'muo.description as operator',
                    'muse.description as option',
                    'mud.description as sub_option',
                    'mudc.amount',
                    'mudc.phone_number',
                    'mudc.id as operation',
                    'mudc.final_transaction_message',
                    'mudc.menu_ussd_status_id',
                    'mudc.wrong_run_counter',
                    'mus.description as menu_ussd_status',

                    't.id as transaction_id',
                    't.status_description',
                    'a.name as atm',
                    'b.description as branch',

                    \DB::raw("coalesce(to_char(mudc.created_at, 'DD/MM/YYYY HH24:MI:SS'), '') as created_at"),
                    \DB::raw("coalesce(to_char(mudc.created_at, 'YYYY-MM-DD HH24:MI:SS'), '') as created_at_date"),
                    \DB::raw("coalesce(to_char(mudc.updated_at, 'DD/MM/YYYY HH24:MI:SS'), '') as updated_at"),
                    \DB::raw("'No' as sent")
                )
                ->join('ussd.menu_ussd_detail as mud', 'mud.id', '=', 'mudc.menu_ussd_detail_id')
                ->join('ussd.menu_ussd_operator as muo', 'muo.id', '=', 'mudc.menu_ussd_operator_id')
                ->join('ussd.menu_ussd_status as mus', 'mus.id', '=', 'mudc.menu_ussd_status_id')
                ->join('ussd.menu_ussd_service as muse', 'muse.service_id', '=', 'mud.service_id')

                ->join('transactions as t', 't.id', '=', 'mudc.transaction_id')
                ->join('points_of_sale as pos', 't.atm_id', '=', 'pos.atm_id')
                ->join('atms as a', 'a.id', '=', 't.atm_id')
                ->join('branches as b', 'b.id', '=', 'pos.branch_id');


            if ($phone_number !== '') {
                $records_list = $records_list->where('mudc.phone_number', $phone_number);
            }

            if ($menu_ussd_status_id !== '') {
                $menu_ussd_status_id = intval($menu_ussd_status_id);
                $records_list = $records_list->where('mudc.menu_ussd_status_id', $menu_ussd_status_id);
            }

            if ($atm_id !== '') {
                $atm_id = intval($atm_id);
                $records_list = $records_list->where('t.atm_id', $atm_id);
            }

            if ($branch !== '') {
                $branch = intval($branch);
                $records_list = $records_list->where('b.id', $branch);
            }

            if ($service_id !== '') {
                $service_id = intval($service_id);
                $records_list = $records_list->where('mud.service_id', $service_id);
            }

            if ($timestamp !== null) {
                $aux  = explode(' - ', str_replace('/', '-', $timestamp));
                $from = date('Y-m-d H:i:s', strtotime($aux[0]));
                $to   = date('Y-m-d H:i:s', strtotime($aux[1]));
                $records_list = $records_list->whereRaw("mudc.created_at between '{$from}' and '{$to}'");
            }

            $records_list = $records_list->orderBy('mudc.created_at', 'asc');

            if ($record_limit !== '') {
                $records_list = $records_list->take(intval($record_limit));
            }

            //\Log::info($records_list->toSql());

            //La ultima sentencia del select.
            $records_list = $records_list->get();

            //Convertir la lista
            $records_list = array_map(function ($value) {
                return (array) $value;
            }, $records_list);

            //Recorrer la lista para identificar los desconocidos
            for ($i = 0; $i < count($records_list); $i++) {
                $item = $records_list[$i];
                $menu_ussd_detail_id = $item['operation'];
                $transaction_id = $item['transaction_id'];
                $menu_ussd_status_id = $item['menu_ussd_status_id'];
                $menu_ussd_status_id = $item['menu_ussd_status_id'];
                $phone_number = $item['phone_number'];
                $created_at_date = $item['created_at_date'];

                if ($menu_ussd_status_id == 2) {
                    $records_list[$i]['sent'] = 'Si';
                } else if ($menu_ussd_status_id == 4) {
                    $menu_ussd_phone_message = \DB::table('ussd.menu_ussd_phone_message')
                        ->select(
                            'id as menu_ussd_phone_message_id', 
                            'message_date_time', 
                            'created_at as created_at_phone_message'
                        )
                        ->whereRaw("message ~ '$phone_number'")
                        ->whereRaw("created_at > '$created_at_date'")
                        ->whereRaw("message_date_time between '$created_at_date' and created_at")
                        ->get();

                    //Convertir la lista
                    $menu_ussd_phone_message = array_map(function ($value) {
                        return (array) $value;
                    }, $menu_ussd_phone_message);

                    if (count($menu_ussd_phone_message) > 0) {
                        $records_list[$i]['sent'] = 'Si';
                        
                        $menu_ussd_phone_message_item = $menu_ussd_phone_message[0];
                        $menu_ussd_phone_message_id = $menu_ussd_phone_message_item['menu_ussd_phone_message_id'];
                        $message_date_time = $menu_ussd_phone_message_item['message_date_time'];
                        $created_at_phone_message = $menu_ussd_phone_message_item['created_at_phone_message'];
                        
                        \Log::info("ENCONTRADO {");
                        \Log::info("id de mensaje: $menu_ussd_phone_message_id");
                        \Log::info("id de carga: $menu_ussd_detail_id");
                        \Log::info("teléfono: $phone_number");
                        \Log::info("created_at_datail_client: $created_at_date");
                        \Log::info("message_date_time: $message_date_time");
                        \Log::info("createt_at_phone_message: $created_at_phone_message");
                        \Log::info("}");
                        \Log::info("-------------------------------------------------------");
                        
                    }
                }
            }

            $list = $records_list;
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }

    /**
     * Obtine todos los datos de un registro del cliente por id.
     */
    function ussd_reason()
    {
        $list = array();

        try {
            $list = \DB::table('ussd.menu_ussd_detail_client_reason')
                ->select('id', 'description')
                ->where('status', true)
                ->get();
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }

    /**
     * Obtine todos los tipos de recargas.
     */
    function ussd_recharge_type()
    {
        $list = array();

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

    /**
     * Obtine las sucursales.
     */
    function ussd_branch()
    {
        $list = array();

        try {
            $list = \DB::table('branches')
                ->select('id', 'description')
                ->get();
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }

    /**
     * Obtine todos los datos de un registro del cliente por id.
     */
    function ussd_status()
    {
        $list = array();

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
     * Obtine los atms activos
     */
    function ussd_atms()
    {
        $list = array();

        try {
            $list = \DB::table('atms')
                ->select('id', 'name as description')
                //->where('atm_status', 1)
                ->whereRaw("compile_version is not null")
                ->get();
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }

    /**
     * Obtine los servicios ussd.
     */
    function ussd_service()
    {
        $list = array();

        try {
            $list = \DB::table('ussd.menu_ussd_service')
                ->select('service_id as id', 'description')
                ->get();
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }
}
