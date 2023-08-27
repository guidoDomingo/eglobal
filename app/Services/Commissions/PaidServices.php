<?php

/**
 * User: avisconte
 * Date: 11/04/2022
 * Time: 09:00 am
 */

namespace App\Services\Commissions;

use App\Exports\ExcelExport;
use Excel;

class PaidServices
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

        if (!$this->user->hasAccess('commissions_parameters_values')) {
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

        $payments = [];
        $atms = [];
        $services_providers_sources = [];
        $payments_detail_aux = [];
        $get_info = true;

        try {

            $connection = \DB::connection('eglobalt_pro');

            /**
             * Trae el detalle de pago ordenado por proveedor, terminal, servicio y comisión 
             */
            $payments_detail_aux = $connection
                ->table('commission.payments_detail_aux as pda')
                ->select(
                    'sps.description as provider',
                    'a.name as terminal',
                    \DB::raw("m.descripcion || ' - ' || sxm.descripcion as service"),
                    \DB::raw("round(pda.commission_total_atm) as total_commission_for_the_point"),
                    'p.description as period'
                )
                ->join('commission.payments as p', 'p.id', '=', 'pda.payments_id')
                ->join('atms as a', 'a.id', '=', 'pda.atm_id')
                ->join('services_providers_sources as sps', 'sps.id', '=', 'pda.service_source_id')
                ->join('servicios_x_marca as sxm', function ($join) {
                    $join->on('pda.service_source_id', '=', \DB::raw("(case when sxm.service_source_id = 9 then 0 else sxm.service_source_id end)"));
                    $join->on('pda.service_id', '=', 'sxm.service_id');
                })
                ->join('marcas as m', 'm.id', '=', 'sxm.marca_id');


            if (isset($request['payments_id'])) {
                if ($request['payments_id'] !== '') {
                    $payments_detail_aux = $payments_detail_aux->where('pda.payments_id', $request['payments_id']);
                }
            }

            if (isset($request['atm_id'])) {
                if ($request['atm_id'] !== '') {
                    $payments_detail_aux = $payments_detail_aux->where('pda.atm_id', $request['atm_id']);
                }
            }

            $payments_detail_aux = $payments_detail_aux
                ->orderBy('provider', 'ASC')
                ->orderBy('terminal', 'ASC')
                ->orderBy('service', 'ASC')
                ->orderBy('total_commission_for_the_point', 'ASC');

            /**
             * Solamente si recibe los filtros hace el select
             */
            if (isset($request['payments_id']) or isset($request['atm_id'])) {
                $payments_detail_aux = $payments_detail_aux->get();
                $payments_detail_aux = json_decode(json_encode($payments_detail_aux), true);
            } else {
                $payments_detail_aux = [];
            }


            if (isset($request['button_name'])) {
                if ($request['button_name'] == 'generate_x') {

                    $style_array = [
                        'font'  => [
                            'bold'  => true,
                            'color' => ['rgb' => '367fa9'],
                            'size'  => 12,
                            'name'  => 'Verdana'
                        ]
                    ];

                    $filename = 'commissions_paid_report_' . time();
                    $columnas = array(
                        'Proveedor', 
                        'Terminal', 
                        'Servicio', 
                        'Comisión total para el punto', 
                        'Periodo'
                    );

                    $excel = new ExcelExport($payments_detail_aux,$columnas);
                    return Excel::download($excel, $filename . '.xls')->send();

                    // Excel::create($filename, function ($excel) use ($payments_detail_aux, $style_array) {
                    //     $excel->sheet('Registros', function ($sheet) use ($payments_detail_aux, $style_array) {
                    //         $sheet->rows($payments_detail_aux, false);
                    //         $sheet->prependRow(array(
                    //             'Proveedor', 
                    //             'Terminal', 
                    //             'Servicio', 
                    //             'Comisión total para el punto', 
                    //             'Periodo'
                    //         ));

                    //         $sheet->getStyle('A1:E1')->applyFromArray($style_array); //Aplicar los estilos del array
                    //         $sheet->setHeight(1, 30); //Aplicar tamaño de la primera fila
                    //     });
                    // })->export('xls');

                    $get_info = false;
                }
            }

            //Traer solo cuando hay búsqueda no cuando genera el excel.
            if ($get_info) {
                /**
                 * Trae las cabeceras de comisiones ordenado por periodo inicial y descripción
                 */
                $payments = $connection
                    ->table('commission.payments')
                    ->select(
                        'id',
                        'description'
                    )
                    ->where('status', true)
                    ->orderBy("initial_period", 'ASC')
                    ->orderBy('description', 'ASC')
                    ->get();

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
                        \DB::raw('description')
                    )
                    ->whereRaw('id = any(array[0, 1, 4, 7, 8, 9, 10])')
                    ->orderBy('description', 'ASC')
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
                'payments' => json_encode($payments),
                'atms' => json_encode($atms),
                'services_providers_sources' => json_encode($services_providers_sources),
                'payments_detail_aux' => $payments_detail_aux
            ],
            'inputs' => [
                'payments_id' => isset($request['payments_id']) ? $request['payments_id'] : null,
                'atm_id' => isset($request['atm_id']) ? $request['atm_id'] : null
            ]
        ];

        return view('commissions.paid', compact('data'));
    }
}