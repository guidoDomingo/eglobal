<?php

/**
 * User: avisconte
 * Date: 05/04/2021
 * Time: 11:00 am
 */

namespace App\Services\TerminalInteraction;

use App\Exports\ExcelExport;
use Excel;

class TransactionServices
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
        $file = $e->getFile();
        $line = $e->getLine();
        $message = $e->getMessage();

        $error_detail = [
            'exception_message' => $message,
            'file' => $file,
            'class' => __CLASS__,
            'function' => $function,
            'line' => $line
        ];

        \Log::error('Ocurrió un error. Detalles:');
        \Log::error($error_detail);

        return $error_detail;
    }

    function index($request, $user)
    {
        $list = array();
        $totals = array();
        $days = 0;
        $total = 0;
        $totals_status = 0;

        try {
            $submit = '';

            if (isset($request['search'])) {
                $submit = 'search';
            } else if (isset($request['generate_x'])) {
                $submit = 'generate_x';
            }

            if ($submit == 'search' or $submit == 'generate_x') {
                $timestamp = $request['timestamp'];

                $filters = [
                    'timestamp' => $timestamp,
                    'branch_id' => $user->branch_id
                ];
            } else {
                $time_init = '00:00:00';
                $time_end = '23:59:59';
                $from = date("d/m/Y");
                $to = date("d/m/Y");
                $timestamp = "$from $time_init - $to $time_end";

                $filters = [
                    'timestamp' => $timestamp,
                    'branch_id' => $user->branch_id
                ];
            }

            //\Log::info("filters:");
            //\Log::info($filters);

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
        return view('terminal_interaction.transaction.index', compact('data'));
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
            //Para una lista de estados y montos
            $status_list = \DB::table('transactions_status')
                ->select(
                    'id',
                    'name as status',
                    'description',
                    \DB::raw('0 as total'),
                    \DB::raw('0 as total_amount')
                )
                ->get();

            $status_list = array_map(function ($value) {
                return (array) $value;
            }, $status_list);

            $records_list = \DB::table('transactions as t')
                ->select(
                    't.status',
                    \DB::raw("count(t.id) as total"),
                    \DB::raw("sum(t.amount) as total_amount")
                );

            $records_list = $this->conditions($filters, $records_list);

            $records_list = $records_list->groupBy('t.status');

            //\Log::info($records_list->toSql());

            //La ultima sentencia del select.
            $records_list = $records_list->get();

            $records_list = array_map(function ($value) {
                return (array) $value;
            }, $records_list);

            for ($i = 0; $i < count($status_list); $i++) {
                for ($j = 0; $j < count($records_list); $j++) {
                    if ($status_list[$i]['status'] == $records_list[$j]['status']) {
                        $status_list[$i]['total'] = $records_list[$j]['total'];
                        $status_list[$i]['total_amount'] = $records_list[$j]['total_amount'];
                    }
                }
            }

            $list = $status_list;

            //\Log::info($status_list);
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
            $records_list = \DB::table('transactions as t')
                ->select(
                    \DB::raw('coalesce(count(t.id), 0) as total'),
                    \DB::raw('coalesce(sum(t.amount), 0) as total_amount')
                );

            $records_list = $this->conditions($filters, $records_list);

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
            $records_list = \DB::table('transactions as t')
                ->select(
                    'o.name as owner',
                    'sps.description as provider',
                    'm.descripcion as brand',
                    'sxm.descripcion as service',
                    't.id as transaction_id',
                    't.status',
                    't.status_description',
                    \DB::raw("t.amount::integer"),
                    \DB::raw("coalesce(to_char(t.created_at, 'DD/MM/YYYY HH24:MI:SS'), '') as created_at"),
                    \DB::raw("coalesce(to_char(t.updated_at, 'DD/MM/YYYY HH24:MI:SS'), '') as updated_at")
                );

            $records_list = $this->conditions($filters, $records_list);

            $records_list = $records_list->orderBy('t.created_at', 'asc');

            \Log::info('QUERY:');
            \Log::info($records_list->toSql());

            //La ultima sentencia del select.
            $records_list = $records_list->get();

            //Convertir la lista
            $records_list = array_map(function ($value) {
                return (array) $value;
            }, $records_list);

            $list = $records_list;
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }

    private function conditions($filters, $records_list)
    {

        $timestamp = $filters['timestamp'];
        $branch_id = $filters['branch_id'];

        $records_list = $records_list
            ->join('services_providers_sources as sps', 'sps.id', '=', 't.service_source_id')
            ->join('servicios_x_marca as sxm', 'sxm.service_id', '=', 't.service_id')
            ->join('marcas as m', 'm.id', '=', 'sxm.marca_id')
            ->join('owners as o', 'o.id', '=', 't.owner_id')
            ->join('atms as a', 'a.id', '=', 't.atm_id')
            ->join('points_of_sale as pos', 'a.id', '=', 'pos.atm_id')
            ->join('branches as b', 'b.id', '=', 'pos.branch_id')
            ->whereRaw("sps.id = sxm.service_source_id");

        if ($branch_id !== '') {
            $branch_id = intval($branch_id);
            $records_list = $records_list->where('b.id', $branch_id);
        }

        if ($timestamp !== null) {
            $aux  = explode(' - ', str_replace('/', '-', $timestamp));
            $from = date('Y-m-d H:i:s', strtotime($aux[0]));
            $to   = date('Y-m-d H:i:s', strtotime($aux[1]));
            $records_list = $records_list->whereRaw("t.created_at between '{$from}' and '{$to}'");
        }

        return $records_list;
    }
}
