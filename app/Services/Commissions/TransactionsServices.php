<?php

/**
 * User: avisconte
 * Date: 11/04/2022
 * Time: 09:00 am
 */

namespace App\Services\Commissions;

use App\Exports\ExcelExport;
use Excel;

class TransactionsServices
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

        $this->user = \Sentinel::getUser();

        if (!$this->user->hasAccess('commissions_transactions')) {
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

        $atms = [];
        $services_providers_sources = [];
        $commissions_transactions = [];
        $get_info = true;

        try {

            $connection = \DB::connection('eglobalt_pro');

            $round = 4;

            if (isset($request['button_name'])) {

                if ($request['button_name'] == 'search') {

                    /**
                     * Trae el detalle de pago ordenado por proveedor, terminal, servicio y comisión 
                     */
                    $commissions_transactions = $connection
                        ->table('commission.parameters as p')
                        ->select(
                            \DB::raw('distinct a.name as terminal'),
                            'sps.description as provider',
                            'm.descripcion as brand',
                            'sxm.descripcion as service',
                            't.id as transaction_id',
                            \DB::raw('to_char(t.created_at, \'DD/MM/YYYY HH24:MI:SS\') as timestamp'),
                            't.amount',
                            'ty.description as commission_type',
                            \DB::raw("replace(round(t.commission_contract, $round)::text, '.', ',') as contract_value_for_eglobalt"),
                            \DB::raw("trim(replace(to_char(t.commission_gross, '999G999G999G999'), ',', '.')) as gross_value_to_distribute"),
                            \DB::raw("trim(replace(to_char(t.commission_net, '999G999G999G999'), ',', '.')) as net_worth_to_eglobalt"),
                            \DB::raw("replace(round(t.commission_contract_level_1, $round)::text, '.', ',')  as contract_value_for_the_point"),
                            \DB::raw("trim(replace(to_char(t.commission_net_level_1, '999G999G999G999'), ',', '.')) as net_worth_for_the_point"),
                            \DB::raw("replace(round(t.commission_calculation_level_1_from_level_0, $round)::text, '.', ',')  as parameterized_calculation")
                        )
                        ->join('transactions as t', 'p.id', '=', 't.commission_parameters_id')
                        ->join('commission.parameters_values as pv', 'p.id', '=', 'pv.parameters_id')
                        ->join('commission.type as ty', 'ty.id', '=', 'pv.type_id')
                        ->join('servicios_x_marca as sxm', function ($join) {
                            $join->on('p.service_source_id', '=', \DB::raw("(case when sxm.service_source_id = 9 then 0 else sxm.service_source_id end)"));
                            $join->on('p.service_id', '=', 'sxm.service_id');
                        })
                        ->join('marcas as m', 'm.id', '=', 'sxm.marca_id')
                        ->join('atms as a', 'a.id', '=', 't.atm_id')
                        ->join('services_providers_sources as sps', 'sps.id', '=', 't.service_source_id')
                        ->whereRaw("t.status = 'success'");


                    if (isset($request['timestamp'])) {
                        $timestamp = $request['timestamp'];
                        $aux = explode(' - ', str_replace('/', '-', $timestamp));
                        $from = date('Y-m-d H:i:s', strtotime($aux[0]));
                        $to = date('Y-m-d H:i:s', strtotime($aux[1]));
                        $commissions_transactions = $commissions_transactions->whereRaw("t.created_at between '{$from}' and '{$to}'");
                    } else {
                        // Si no hay filtro de fecha se trae lo de hoy.
                        $from = date('Y-m-d H:i:s');
                        $to = date('Y-m-d H:i:s');
                        $commissions_transactions = $commissions_transactions->whereRaw("t.created_at between '{$from}' and '{$to}'");
                    }

                    if (isset($request['services_providers_sources_id'])) {
                        if ($request['services_providers_sources_id'] !== '') {
                            $commissions_transactions = $commissions_transactions->where('t.service_source_id', $request['services_providers_sources_id']);
                        }
                    }

                    if (isset($request['atm_id'])) {
                        if ($request['atm_id'] !== '') {
                            $commissions_transactions = $commissions_transactions->where('a.id', $request['atm_id']);
                        }
                    }

                    $commissions_transactions = $commissions_transactions
                        ->orderBy('t.id', 'ASC');

                    //\Log::info('QUERY:');
                    //\Log::info($commissions_transactions->toSql());

                    $commissions_transactions = $commissions_transactions->get();

                    $commissions_transactions = json_decode(json_encode($commissions_transactions), true);

                } else if ($request['button_name'] == 'generate_x') {

                    $commissions_transactions = json_decode($request['json'], true);

                    $style_array = [
                        'font'  => [
                            'bold'  => true,
                            'color' => ['rgb' => '367fa9'],
                            'size'  => 12,
                            'name'  => 'Verdana'
                        ]
                    ];

                    $filename = 'commissions_transactions_report_' . time();
                    $columnas = array(
                        'Terminal',
                        'Proveedor',
                        'Marca',
                        'Servicio',
                        'ID - Transacción',
                        'Fecha - Hora',
                        'Monto',
                        'Tipo de Comisión',
                        'Valor de contrato para Eglobalt',
                        'Valor bruto a repartir',
                        'Valor neto para Eglobalt',
                        'Valor de contrato para el punto',
                        'Valor neto para el punto',
                        'Calculo parametrizado'
                    );

                    $excel = new ExcelExport($commissions_transactions,$columnas);
                    return Excel::download($excel, $filename . '.xls')->send();

                    // Excel::create($filename, function ($excel) use ($commissions_transactions, $style_array) {
                    //     $excel->sheet('Detalle de Comisiones', function ($sheet) use ($commissions_transactions, $style_array) {
                    //         $sheet->rows($commissions_transactions, false);
                    //         $sheet->prependRow(array(
                    //             'Terminal',
                    //             'Proveedor',
                    //             'Marca',
                    //             'Servicio',
                    //             'ID - Transacción',
                    //             'Fecha - Hora',
                    //             'Monto',
                    //             'Tipo de Comisión',
                    //             'Valor de contrato para Eglobalt',
                    //             'Valor bruto a repartir',
                    //             'Valor neto para Eglobalt',
                    //             'Valor de contrato para el punto',
                    //             'Valor neto para el punto',
                    //             'Calculo parametrizado'
                    //         ));

                    //         $sheet->getStyle('A1:N1')->applyFromArray($style_array); //Aplicar los estilos del array
                    //         $sheet->setHeight(1, 25); //Aplicar tamaño de la primera fila
                    //     });
                    // })->export('xlsx');

                    $get_info = false;
                }
            }

            //Traer solo cuando hay búsqueda no cuando genera el excel.
            if ($get_info) {

                /**
                 * Trae las terminales con su red que no tengan soft-delete ordenado por red y terminal
                 */
                $atms = $connection
                    ->table('atms as a')
                    ->select(
                        'a.id',
                        //'a.name as description',
                        \DB::raw("(o.name || ' - ' || a.name) as description")
                    )
                    ->join('owners as o', 'o.id', '=', 'a.owner_id')
                    ->where('a.deleted_at', null)
                    ->orderBy('o.name', 'ASC')
                    ->orderBy('a.name', 'ASC')
                    ->get();

                /**
                 * Trae los proveedores ordenado por descripción
                 */
                $services_providers_sources = $connection
                    ->table('services_providers_sources')
                    ->select(
                        'id',
                        'description'
                    )
                    ->whereRaw('id = any(array[0, 1, 4, 7, 8, 9, 10])')
                    ->orderBy('description', 'ASC')
                    ->get();
            }

            //\Log::debug('payments_detail_aux:', [$payments_detail_aux]);
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
            'lists' => [
                'commissions_transactions' => $commissions_transactions,
                'services_providers_sources' => json_encode($services_providers_sources),
                'atms' => json_encode($atms),
                'json' => json_encode($commissions_transactions)
            ],
            'inputs' => [
                'timestamp' => isset($request['timestamp']) ? $request['timestamp'] : null,
                'services_providers_sources_id' => isset($request['services_providers_sources_id']) ? $request['services_providers_sources_id'] : null,
                'atm_id' => isset($request['atm_id']) ? $request['atm_id'] : null
            ]
        ];

        return view('commissions.transactions', compact('data'));
    }
}
