<?php

/**
 * User: avisconte
 * Date: 11/04/2022
 * Time: 09:00 am
 */

namespace App\Services\Commissions;

use App\Exports\ExcelExport;
use Excel;

class ParametersValuesServices
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

        $message = '';
        $total = 0;
        $atms = [];
        $services_providers_sources = [];
        $parameters_values = [];
        $totals_by_type_of_commission = [];
        $total_by_providers = [];
        $get_info = true;

        try {

            $connection = \DB::connection('eglobalt_pro');

            $round = 4;

            if (isset($request['button_name'])) {

                if ($request['button_name'] == 'search') {

                    /**
                     * Trae el detalle de pago ordenado por proveedor, terminal, servicio y comisión 
                     */
                    $parameters_values = $connection
                        ->table('commission.parameters as p')
                        ->select(
                            'sps.description as provider',
                            \DB::raw("(m.descripcion || ' - ' || sxm.descripcion) as service"),
                            \DB::raw("
                                coalesce((
                                    json_agg(
                                        json_build_object(
                                            'parameters_id', p.id,
                                            'validity', 'De ' || to_char(p.start_validity, 'DD/MM/YYYY') || ' a ' || to_char(p.end_validity, 'DD/MM/YYYY'),
                                            'status_validity', (case when p.status = true then 'Activo' else 'Inactivo' end),
                                            'commission_type', ty.description,
                                            'value_fixed', pv.value_fixed,
                                            'value_percentage', pv.value_percentage,
                                            'value_min', pv.value_min,
                                            'value_max', pv.value_max,
                                            'contract_value_for_the_point', pv.value_level_1,
                                            'standard_calculation', pv.value_percentage_level_1
                                        ) order by (p.start_validity) asc
                                    )
                                ), '[]'::json) as parameters_values_details
                            ")

                        )
                        ->join('commission.parameters_values as pv', 'p.id', '=', 'pv.parameters_id')
                        ->join('commission.type as ty', 'ty.id', '=', 'pv.type_id')
                        ->join('servicios_x_marca as sxm', function ($join) {
                            $join->on('p.service_source_id', '=', 'sxm.service_source_id');
                            $join->on('p.service_id', '=', 'sxm.service_id');
                        })
                        ->join('marcas as m', 'm.id', '=', 'sxm.marca_id')
                        ->join('services_providers_sources as sps', 'sps.id', '=', 'p.service_source_id');

                    if (isset($request['services_providers_sources_id'])) {
                        if ($request['services_providers_sources_id'] !== '' and $request['services_providers_sources_id'] !== 'Todos') {
                            $parameters_values = $parameters_values->where('sps.id', $request['services_providers_sources_id']);
                        }
                    }

                    if (isset($request['service_by_brand_id'])) {
                        if ($request['service_by_brand_id'] !== '' and $request['service_by_brand_id'] !== 'Todos') {
                            $parameters_values = $parameters_values->where('sxm.service_id', $request['service_by_brand_id']);
                        }
                    }

                    $parameters_values = $parameters_values
                        ->groupBy(\DB::raw("
                            sps.description,
                            m.descripcion,
                            sxm.descripcion
                        "))
                        ->orderBy('sps.description', 'ASC');

                    //\Log::info('SQL:' . $parameters_values->toSql());

                    $parameters_values = $parameters_values->get();

                    if (count($parameters_values) > 0) {
                        
                    } else {
                        //$message = 'La consulta no retornó registros.';

                        $data = [
                            'mode' => 'alert',
                            'type' => 'info',
                            'title' => 'Consulta sin registros',
                            'explanation' => 'La consulta no retornó ningún registro.'
                        ];

                        return view('messages.index', compact('data'));
                    }
                } else if ($request['button_name'] == 'generate_x') {

                    $parameters_values = json_decode($request['json']);
                    $parameters_values_aux = [];

                    \Log::info('parameters_values:', [$parameters_values]);

                    $style_array = [
                        'font'  => [
                            'bold'  => true,
                            'color' => ['rgb' => '367fa9'],
                            'size'  => 12,
                            'name'  => 'Verdana'
                        ]
                    ];

                    $filename = 'parameters_values_report_' . time();


                    foreach ($parameters_values as $item) {
                        $provider = $item->provider;
                        $service = $item->service;
                        $details = json_decode($item->parameters_values_details);

                        foreach ($details as $sub_item) {

                            $parameters_id = $sub_item->parameters_id;
                            $validity = $sub_item->validity;
                            $commission_type = $sub_item->commission_type;
                            $value_fixed = $sub_item->value_fixed;
                            $value_percentage = $sub_item->value_percentage;
                            $value_min = $sub_item->value_min;
                            $value_max = $sub_item->value_max;
                            $contract_value_for_the_point = $sub_item->contract_value_for_the_point;
                            $standard_calculation = $sub_item->standard_calculation;

                            $item_aux = [
                                'Proveedor' => $provider,
                                'Servicio' => $service,
                                'Vigencia' => $validity,
                                'Tipo de Comisión' => $commission_type,
                                'Fijo' => $value_fixed,
                                'Porcentaje' => $value_percentage,
                                'Mínimo' => $value_min,
                                'Máximo' => $value_max,
                                'Punto' => $contract_value_for_the_point,
                                'Estándar' => $standard_calculation
                            ];

                            array_push($parameters_values_aux, $item_aux);
                        }
                    }

                    $columnas = array(
                        'Proveedor',
                        'Servicio',
                        'Vigencia',
                        'Tipo',
                        'Fijo',
                        'Porcentual',
                        'Mínimo',
                        'Máximo',
                        'Punto',
                        'Estándar'
                    );
        
                    $excel = new ExcelExport($parameters_values_aux,$columnas);
                    return Excel::download($excel, $filename . '.xls')->send();

                    // Excel::create($filename, function ($excel) use ($parameters_values_aux, $style_array) {
                    //     $excel->sheet('Tarifario de Comisiones', function ($sheet) use ($parameters_values_aux, $style_array) {
                    //         $sheet->rows($parameters_values_aux, false);

                    //         $sheet->prependRow(array(
                    //             'Proveedor',
                    //             'Servicio',
                    //             'Vigencia',
                    //             'Tipo',
                    //             'Fijo',
                    //             'Porcentual',
                    //             'Mínimo',
                    //             'Máximo',
                    //             'Punto',
                    //             'Estándar'
                    //         ));

                    //         $sheet->getStyle('A1:J1')->applyFromArray($style_array); //Aplicar los estilos del array
                    //         $sheet->setHeight(1, 25); //Aplicar tamaño de la primera fila
                    //     });
                    // })->export('xlsx');

                    $get_info = false;
                }
            } else {
            }

            //Traer solo cuando hay búsqueda no cuando genera el excel.
            if ($get_info) {

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
                'parameters_values' => $parameters_values,
                'totals_by_type_of_commission' => $totals_by_type_of_commission,
                'total_by_providers' => $total_by_providers,
                'json' => json_encode($parameters_values, JSON_UNESCAPED_UNICODE),
                'services_providers_sources' => json_encode($services_providers_sources, JSON_UNESCAPED_UNICODE)
            ],
            'total' => $total,
            'inputs' => [
                'services_providers_sources_id' => isset($request['services_providers_sources_id']) ? $request['services_providers_sources_id'] : 'Todos',
                'service_by_brand_id' => isset($request['service_by_brand_id']) ? $request['service_by_brand_id'] : 'Todos',
            ]
        ];

        return view('commissions.parameters_values', compact('data'));
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
