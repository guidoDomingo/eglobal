<?php

/**
 * User: avisconte
 * Date: 11/04/2022
 * Time: 09:00 am
 */

namespace App\Services\Commissions;

use App\Exports\ExcelExport;
use Excel;

class TransactionsClientServices
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

        $connection = \DB::connection('eglobalt_pro');

        $round = 2;
        $atms = [];
        $services = [];
        $services_providers_sources = [];
        $commissions_transactions_client = [];
        $totals_in_brands_and_services = [];
        $total = 0;
        $get_info = true;

        $this->user = \Sentinel::getUser();
        $user_id = $this->user->id;

        if (!$this->user->hasAccess('commissions_transactions_client')) {
            
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

        //Saber si el usuario tiene relacionado terminales

        $atms_per_user = $connection
            ->table('atms as a')
            ->select(
                \DB::raw("coalesce(array_to_string(array_agg(a.id order by a.id asc), ', '), '-1')")
            )
            ->join('points_of_sale as pos', 'a.id', '=', 'pos.atm_id')
            ->join('branches as b', 'b.id', '=', 'pos.branch_id')
            ->where('b.user_id', 30)
            ->get();

        $atms_per_user = $atms_per_user[0]->coalesce;


        if ($atms_per_user == -1) {
            \Log::error(
                'El usuario no tiene ningún terminal relacionado.',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            \Session::flash('error_message', 'El usuario no tiene ningún terminal relacionado.');
            
            $data = [
                'mode' => 'alert',
                'type' => 'error',
                'title' => 'Sin asignación',
                'explanation' => 'El usuario que inició sesión necesita ser asignado a las terminales.'
            ];

            return view('messages.index', compact('data'));
        }

        try {

            if (isset($request['button_name'])) {

                if ($request['button_name'] == 'search') {

                    /**
                     * Trae el detalle de pago ordenado por proveedor, terminal, servicio y comisión 
                     */
                    $commissions_transactions_client = $connection
                        ->table('commission.parameters as p')
                        ->select(
                            \DB::raw('distinct a.name as terminal'),
                            \DB::raw("(m.descripcion || ' - ' || sxm.descripcion) as service"),
                            't.id as transaction_id',
                            \DB::raw('to_char(t.created_at, \'DD/MM/YYYY HH24:MI:SS\') as timestamp'),
                            \DB::raw('round(t.amount) as amount'),
                            \DB::raw("round(t.commission_net_level_1, $round) as net_worth_for_the_point")
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
                        $commissions_transactions_client = $commissions_transactions_client->whereRaw("t.created_at between '{$from}' and '{$to}'");

                        $from_view = date('d/m/Y', strtotime($aux[0])) . ' 00:00:00';
                        $to_view = date('d/m/Y', strtotime($aux[1])) . ' 23:59:59';
                        $request['timestamp'] = "$from_view - $to_view";
                        
                    } else {
                        // Si no hay filtro de fecha se trae lo de hoy.
                        $from = date('Y-m-d H:i:s');
                        $to = date('Y-m-d H:i:s');
                        $commissions_transactions_client = $commissions_transactions_client->whereRaw("t.created_at between '{$from}' and '{$to}'");
                    }

                    if (isset($request['atm_id'])) {
                        if ($request['atm_id'] !== '' and $request['atm_id'] !== 'Todos') {
                            $commissions_transactions_client = $commissions_transactions_client->where('a.id', $request['atm_id']);
                        } else {
                            $commissions_transactions_client = $commissions_transactions_client->whereRaw("a.id in ($atms_per_user)");
                        }
                    } else {
                        $commissions_transactions_client = $commissions_transactions_client->whereRaw("a.id in ($atms_per_user)");
                    }

                    if (isset($request['service_id'])) {
                        if ($request['service_id'] !== '' and $request['service_id'] !== 'Todos') {

                            $aux = $request['service_id'];
                            $aux = explode(',', $aux);

                            $service_source_id_aux = $aux[0];
                            $service_id_aux = $aux[1];

                            $commissions_transactions_client = $commissions_transactions_client->where('t.service_source_id', $service_source_id_aux);

                            $commissions_transactions_client = $commissions_transactions_client->where('t.service_id', $service_id_aux);
                        }
                    }

                    $commissions_transactions_client = $commissions_transactions_client
                        ->orderBy('terminal', 'ASC')
                        ->orderBy('service', 'ASC');

                    $commissions_transactions_client = $commissions_transactions_client->limit(100)->get();

                    $commissions_transactions_client = json_decode(json_encode($commissions_transactions_client), true);

                    if (count($commissions_transactions_client) > 0) {
                        foreach ($commissions_transactions_client as $item) {
                            $terminal = $item['terminal'];
                            $service = $item['service'];
                            $net = floatval($item['net_worth_for_the_point']);

                            if (!isset($totals_in_brands_and_services[$terminal][$service])) {
                                $totals_in_brands_and_services[$terminal][$service] = 0;
                            }

                            if (!isset($totals_in_brands_and_services[$terminal]['total'])) {
                                $totals_in_brands_and_services[$terminal]['total'] = 0;
                            }

                            $totals_in_brands_and_services[$terminal][$service] = $totals_in_brands_and_services[$terminal][$service] + $net;

                            $totals_in_brands_and_services[$terminal]['total'] = $totals_in_brands_and_services[$terminal]['total'] + $net; // Total por ATM

                            $total = $total + $net; // Total general
                        }
                    }

                } else if ($request['button_name'] == 'generate_x') {

                    // Comisión por Transacciones
                    $commissions_transactions_client = json_decode($request['json'], true);

                    $totals_in_brands_and_services = json_decode($request['totals_in_brands_and_services_aux'], true);

                    $total_per_terminal = [];

                    $total_per_terminal_and_services = [];

                    foreach ($totals_in_brands_and_services as $key => $value) {

                        $item = [
                            'terminal' => $key,
                            'total' => $value['total']
                        ];

                        array_push($total_per_terminal, $item);

                        $sub_item = [
                            'terminal' => $key,
                            'service' => null,
                            'total' => null
                        ];

                        foreach ($value as $sub_key => $sub_value) {
                            if ($sub_key !== 'total') {
                                $sub_item['service'] = $sub_key;
                                $sub_item['total'] = $sub_value;
                                array_push($total_per_terminal_and_services, $sub_item);
                            }
                        }
                    }


                    $style_array = [
                        'font'  => [
                            'bold'  => true,
                            'color' => ['rgb' => '367fa9'],
                            'size'  => 12,
                            'name'  => 'Verdana'
                        ]
                    ];

                    $filename = 'commissions_transactions_client_report_' . time();

                    $columna1 = array(
                        'Terminal',
                        'Total'
                    );

                    $columna2 = array(
                        'Terminal',
                        'Servicio',
                        'Total'
                    );

                    $columna3 = array(
                        'Terminal',
                        'Servicio',
                        'ID - Transacción',
                        'Fecha - Hora',
                        'Monto',
                        'Valor neto para el punto'
                    );


                    $excel = new ExcelExport($total_per_terminal,$columna1,$total_per_terminal_and_services,$columna2,$commissions_transactions_client,$columna3);
                    return Excel::download($excel, $filename . '.xls')->send();

                    // Excel::create($filename, function ($excel) use ($commissions_transactions_client, $total_per_terminal, $total_per_terminal_and_services, $style_array) {
                
                    //     $excel->sheet('Comisión por Terminal', function ($sheet) use ($total_per_terminal, $style_array) {
                    //         $sheet->rows($total_per_terminal, false);
                    //         $sheet->prependRow(array(
                    //             'Terminal',
                    //             'Total'
                    //         ));

                    //         $sheet->getStyle('A1:B1')->applyFromArray($style_array); //Aplicar los estilos del array
                    //         $sheet->setHeight(1, 25); //Aplicar tamaño de la primera fila
                    //     });

                    //     $excel->sheet('Comisión por Servicios', function ($sheet) use ($total_per_terminal_and_services, $style_array) {
                    //         $sheet->rows($total_per_terminal_and_services, false);
                    //         $sheet->prependRow(array(
                    //             'Terminal',
                    //             'Servicio',
                    //             'Total'
                    //         ));

                    //         $sheet->getStyle('A1:C1')->applyFromArray($style_array); //Aplicar los estilos del array
                    //         $sheet->setHeight(1, 25); //Aplicar tamaño de la primera fila
                    //     });

                    //     $excel->sheet('Comisión por Transacciones', function ($sheet) use ($commissions_transactions_client, $style_array) {
                    //         $sheet->rows($commissions_transactions_client, false);
                    //         $sheet->prependRow(array(
                    //             'Terminal',
                    //             'Servicio',
                    //             'ID - Transacción',
                    //             'Fecha - Hora',
                    //             'Monto',
                    //             'Valor neto para el punto'
                    //         ));

                    //         $sheet->getStyle('A1:G1')->applyFromArray($style_array); //Aplicar los estilos del array
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
                    ->whereRaw("a.id in ($atms_per_user)")
                    ->orderBy('o.name', 'ASC')
                    ->orderBy('a.name', 'ASC');

                $atms = $atms->get();

                /**
                 * Trae los servicios con sus marcas.
                 */
                $services = $connection
                    ->table('servicios_x_marca as sxm')
                    ->select(
                        \DB::raw("(sxm.service_source_id || ',' || sxm.service_id) as id"),
                        \DB::raw("upper(m.descripcion || ' - ' || sxm.descripcion) as description")
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
            'lists' => [
                'commissions_transactions_client' => $commissions_transactions_client,
                'totals_in_brands_and_services' => $totals_in_brands_and_services,
                'totals_in_brands_and_services_aux' => json_encode($totals_in_brands_and_services),
                'json' => json_encode($commissions_transactions_client),
                'atms' => json_encode($atms),
                'services' => json_encode($services)
            ],
            'total' => $total,
            'inputs' => [
                'timestamp' => isset($request['timestamp']) ? $request['timestamp'] : null,
                'atm_id' => isset($request['atm_id']) ? $request['atm_id'] : 'Todos',
                'service_id' => isset($request['service_id']) ? $request['service_id'] : 'Todos'
            ]
        ];

        return view('commissions.transactions_client', compact('data'));
    }
}
