<?php

/**
 * User: avisconte
 * Date: 11/04/2022
 * Time: 09:00 am
 */

namespace App\Services\Commissions;

use App\Exports\ExcelExport;
use Carbon\Carbon;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Maatwebsite\Excel\Facades\Excel;
use NumberFormatter;

class ViewCommissionService
{
    /**
     * Función inicial
     */

    protected $user;

    public function __construct()
    {
        $this->user = Sentinel::getUser();
    }

    
    
    public function commissions_generales($request)
    {
         $input = $request->all();
         ini_set('max_execution_time', 0);
         ini_set('client_max_body_size', '20M');
         ini_set('max_input_vars', 10000);
         ini_set('upload_max_filesize', '20M');
         ini_set('post_max_size', '20M');
         ini_set('memory_limit', '-1');
         set_time_limit(3600);
       
         //variables globales
         $owners_input = isset($input['owners']) && !empty($input['owners']) ? $input['owners'] : '' ;
         $fecha_input = isset($input['reservationtime']) && !empty($input['reservationtime']) ? $input['reservationtime'] : '' ;
         $button_name = isset($input['button_name']) && !empty($input['button_name']) ? $input['button_name'] : '' ;
         
         //variables del servicio
         $sql = "";
         $fecha_inicio = "";
         $fecha_fin = "";
         $input_fecha = "";
         $nombre_excel = "";   
         $owners = null;
         $commission_agrupado = [];
         $get_info = true;
         $var_sql = '';
     
         try {
             $connection = DB::connection('eglobal');         

            if(!empty($owners_input)  && !empty($fecha_input)) {
                $daterange = explode(' - ',  str_replace('/', '-', $fecha_input));
                $fecha_inicio = date('Y-m-d H:i:s', strtotime($daterange[0]));
                $fecha_fin = date('Y-m-d H:i:s', strtotime($daterange[1]));
                $input_fecha = $input_fecha;

                $var_sql.=" and  ow.id = '".$owners_input."'". " and t.created_at between "."'".$fecha_inicio."'". " and "."'".$fecha_fin."'";    

            }else if(!empty($fecha_input)){
                $daterange = explode(' - ',  str_replace('/', '-', $fecha_input));
                $fecha_inicio = date('Y-m-d H:i:s', strtotime($daterange[0]));
                $fecha_fin = date('Y-m-d H:i:s', strtotime($daterange[1]));
                $input_fecha = $input_fecha;

                $var_sql.= " and t.created_at between "."'".$fecha_inicio."'". " and "."'".$fecha_fin."'"; 

            }else if(!empty($owners_input)){

                $var_sql.=  " and ow.id =  "."'".$owners_input."'";  
            }

                $sql = " 
                    select
                        ow.id,
                        ow.name as red,
                        sum(t.amount) as monto,
                        count(t.id) as cantidad_transaccion,
                        sum(t.commission_gross) as comision_bruta,
                        sum(t.commission_net) as comision_eglobal,
                        sum(t.commission_net_level_1) as comision_punto,
                        min(t.created_at) as fecha_min,
                        max(t.created_at) as fecha_max
                    from public.transactions t
                        join public.owners ow 
                            on ow.id = t.owner_id
                        join public.atms a
                            on a.id = t.atm_id    
                        join public.servicios_x_marca sxm 
                            on t.service_source_id = (case when sxm.service_source_id = 9 then 0 else sxm.service_source_id end) and t.service_id = sxm.service_id
                        join public.marcas as m on m.id = sxm.marca_id
                        where t.status = 'success' and sxm.service_source_id  = t.service_source_id and sxm.service_id = t.service_id  
                        ".$var_sql."
                    group by ow.name, ow.id
                ";

            //return $sql;
            if(!empty($fecha_input)){
                $commission_agrupado = $connection->select($sql);   
                $commission_agrupado = json_decode(json_encode($commission_agrupado), true);
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

                    $col_nombre = $nombre_excel;

                    $filename = 'commissions_report_' . time();

                    $columnas = array(
                        'Id','Red', 'Monto total', 'Cantidad transacción','Comisión bruta','Comisón eglobal','Comisión para el punto','Inicio','Fin'
                    );

                    $excel = new ExcelExport($commission_agrupado,$columnas);
                    return Excel::download($excel, $filename . '.xls')->send();
                    
                    // Excel::create($filename, function ($excel) use ($commission_agrupado, $style_array,$col_nombre) {
                    //     $excel->sheet('Registros', function ($sheet) use ($commission_agrupado, $style_array,$col_nombre) {
                    //         $sheet->rows($commission_agrupado, false);
                            
                    //         $sheet->prependRow(array(
                    //             'Id','Red', 'Monto total', 'Cantidad transacción','Comisión bruta','Comisón eglobal','Comisión para el punto','Inicio','Fin'
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
                    
                    $owners = $connection
                        ->table('public.owners as o')
                        ->select(
                            'o.id',
                            'o.name'
                        )
                        ->where('o.deleted_at',null)
                        ->groupBy('o.name','o.id')
                        ->get();

                    $owners = json_decode(json_encode($owners), true);  
                    
                }
                
                $data = [
                    'lists' => [
                        'commission_agrupado' => $commission_agrupado,
                        'owners' => $owners
                    ],
                    'inputs' => [
                        'fecha' => $fecha_input,
                        'owners' => $owners_input,
                        'get_info' => $get_info
                    ]
                ];
                
                
                return view('commissions.commissionGenerales', compact('data'));
     
            }catch (\Exception $e) {
                $error_detail = [
                    'exception' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'class' => __CLASS__,
                    'function' => __FUNCTION__,
                    'line' => $e->getLine()
                ];
    
                Log::error("Error, Detalles: " . json_encode($error_detail));
            }
     
    }
    public function service_detallado($request)
    {
        $data = $request->all();
        //variables globales
        $id = $data['id'];
        $red = $data['red'];
        $fecha = isset($data['fecha']) && !empty($data['fecha']) ? $data['fecha'] : '';
        //variables de los filtros de la búsqueda
        $input_atm = isset($data['atm']) && !empty($data['atm']) ? $data['atm'] : '';
        $input_fecha = isset($data['reservationtime']) && !empty($data['reservationtime']) ? $data['reservationtime'] : '';
        $input_button = isset($data['button_name']) && !empty($data['button_name']) ? $data['button_name'] : '';
        $lista_atm = isset($data['lista_atm']) && !empty($data['lista_atm']) ? $data['lista_atm'] : '';

        //variable del servicio
        $var_sql = '';
        $get_info = true;
        $filtro = false;
        try{
            $connection = DB::connection('eglobal');

                if(!empty($fecha)){
                    $daterange = explode(' - ',  str_replace('/', '-', $fecha));
                    $fecha_inicio = date('Y-m-d H:i:s', strtotime($daterange[0]));
                    $fecha_fin = date('Y-m-d H:i:s', strtotime($daterange[1]));
                    $input_fecha = $input_fecha;

                    $var_sql= " and t.created_at between "."'".$fecha_inicio."'". " and "."'".$fecha_fin."'";  
            
                }
            
                if(!empty($input_atm) && $input_atm != "no-status" && !empty($input_fecha)) {
                    $daterange = explode(' - ',  str_replace('/', '-', $input_fecha));
                    $fecha_inicio = date('Y-m-d H:i:s', strtotime($daterange[0]));
                    $fecha_fin = date('Y-m-d H:i:s', strtotime($daterange[1]));
                    $input_fecha = $input_fecha;
    
                    $var_sql= " and a.id =  "."'".$input_atm."'". " and t.created_at between "."'".$fecha_inicio."'". " and "."'".$fecha_fin."'";  
                    
                    $filtro = true;
    
                }else if(!empty($input_fecha)){
                    $daterange = explode(' - ',  str_replace('/', '-', $input_fecha));
                    $fecha_inicio = date('Y-m-d H:i:s', strtotime($daterange[0]));
                    $fecha_fin = date('Y-m-d H:i:s', strtotime($daterange[1]));
                    $input_fecha = $input_fecha;
    
                    $var_sql= " and t.created_at between "."'".$fecha_inicio."'". " and "."'".$fecha_fin."'";  

                    $filtro = true;
             
                }else if(!empty($input_atm)){
    
                    $var_sql=  " and a.id =  "."'".$input_atm."'";  

                    $filtro = true;
                }  

                 $sql = " 
                 select
                     a.name,
                     a.id,
                     count(t.id) as cantidad_transaccion,
                     sum(t.amount) as monto_red,
                     sum(t.commission_gross) as comision_bruta,
                     sum(t.commission_net) as comi_neta_eglobal,
                     sum(t.commission_net_level_1) as comi_neta_punto,
                     min(t.created_at) as inicio,
                     max(t.created_at) as fin
                 from public.transactions t
                     join public.owners o 
                         on o.id = t.owner_id
                     join public.atms a 
                         on a.id = t.atm_id
                     join public.servicios_x_marca sxm 
                         on t.service_source_id = (case when sxm.service_source_id = 9 then 0 else sxm.service_source_id end) and t.service_id = sxm.service_id
                     join public.marcas as m on m.id = sxm.marca_id
                 where t.owner_id = ".$id."
                     and t.status = 'success'
                     and sxm.service_source_id  = t.service_source_id 
                     and sxm.service_id = t.service_id 
                     ".$var_sql."
                 group by a.name, a.id
            ";
                
             //return $sql;          
            $resultado = $connection->select($sql);   
            $resultado = json_decode(json_encode($resultado), true);


            if(!$filtro){

                $items_providers = [];
                $items_services = [];
                
                if (count($resultado) > 0) {
                    foreach($resultado as $key => $item) {
    
                        $objeto2 = [
                            "name" => $item['name'],
                            "id" => $item['id']
                        ];
    
                        if(!in_array($objeto2,$items_services)){
                            array_push($items_services, $objeto2);
                        }
                               
    
                    }
                }
            }else{
                //$items_providers = json_decode($lista_proveedores,true);
                $items_services = json_decode($lista_atm,true);
                
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


                    $filename = 'commissions_report_' . time();

                    $columnas = array(
                        'Atm','Atm id','Total transacción', 'Monto','Comisión bruta','Comisón eglobal','Comisión para el punto','Inicio','Fin'
                    );

                    $excel = new ExcelExport($resultado,$columnas);
                    return Excel::download($excel, $filename . '.xls')->send();
                    
                    // Excel::create($filename, function ($excel) use ($resultado, $style_array) {
                    //     $excel->sheet('Registros', function ($sheet) use ($resultado, $style_array) {
                    //         $sheet->rows($resultado, false);
                            
                    //         $sheet->prependRow(array(
                    //             'Atm','Atm id','Total transacción', 'Monto','Comisión bruta','Comisón eglobal','Comisión para el punto','Inicio','Fin'
                    //         ));

                    //         $sheet->getStyle('A1:E1')->applyFromArray($style_array); //Aplicar los estilos del array
                    //         $sheet->setHeight(1, 30); //Aplicar tamaño de la primera fila
                    //     });
                    // })->export('xls');

                    $get_info = false;
                }
            }
            
            
          
            $data = [
                'lists' => [
                    'resultado' => $resultado,
                    'atms' => $items_services
                ],
                'inputs' => [
                    'red' => $red,
                    'id' => $id,
                    'input_atm' => $input_atm,
                    'fecha' => !empty($input_fecha) ? $input_fecha : $fecha 
                ]
            ];

            
            return view('commissions.commissionGeneralesDetallado', compact('data'));
            
        }catch (\Exception $e) {
            $error_detail = [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];

            Log::error("Error, Detalles: " . json_encode($error_detail));
        }
    }
    public function service_detallado_nivel3($request)
    {
        $data = $request->all();
        //return $data;
        //variables globales
        $id = $data['id'];
        $red = $data['red'];
        $fecha = isset($data['fecha']) && !empty($data['fecha']) ? $data['fecha'] : '' ;

        //variables de los filtros de la búsqueda
        $input_atm = isset($data['atm_name']) && !empty($data['atm_name']) ? $data['atm_name'] : '';
        $input_atm_id = isset($data['atm_id']) && !empty($data['atm_id']) ? $data['atm_id'] : '';
        $service_atm = isset($data['atm_service']) && !empty($data['atm_service']) ? $data['atm_service'] : '';
        $proveedor_service = isset($data['proveedor_service']) && !empty($data['proveedor_service']) ? $data['proveedor_service'] : '';
        $input_fecha = isset($data['reservationtime']) && !empty($data['reservationtime']) ? $data['reservationtime'] : '';
        $input_button = isset($data['button_name']) && !empty($data['button_name']) ? $data['button_name'] : '';
        $input_descripcion_ = isset($data['input_descripcion']) && !empty($data['input_descripcion']) ? $data['input_descripcion'] : ''; 
        //dd($input_descripcion);
        $input_descripcion = json_decode($input_descripcion_,true);
        //return $input_descripcion['service_source_id'];
        //heredmos el listado de proveedores y servicios
        $lista_proveedores = isset($data['lista_proveedor']) && !empty($data['lista_proveedor']) ? $data['lista_proveedor'] : '';
        $lista_service = isset($data['lista_service']) && !empty($data['lista_service']) ? $data['lista_service'] : '';

        //variables del servicio
        $get_info = true;
        $var_sql = '';
        $filtro = false;

        try{
            //cantidad total de transaccion por red
            $connection = DB::connection('eglobal');           
                 
                 if(!empty($fecha)){
                    $daterange = explode(' - ',  str_replace('/', '-', $fecha));
                    $fecha_inicio = date('Y-m-d H:i:s', strtotime($daterange[0]));
                    $fecha_fin = date('Y-m-d H:i:s', strtotime($daterange[1]));
                    $input_fecha = $input_fecha;
    
                    $var_sql.= " and t.created_at between "."'".$fecha_inicio."'". " and "."'".$fecha_fin."'";  

                 }else if(!empty($input_fecha) && !empty($input_descripcion)) {
                    $daterange = explode(' - ',  str_replace('/', '-', $input_fecha));
                    $fecha_inicio = date('Y-m-d H:i:s', strtotime($daterange[0]));
                    $fecha_fin = date('Y-m-d H:i:s', strtotime($daterange[1]));
                    $input_fecha = $input_fecha;
    
                    $var_sql.= " and sxm.service_id =  ".$input_descripcion['service_id']." and sxm.service_source_id =  ".$input_descripcion['service_source_id']." and t.created_at between "."'".$fecha_inicio."'". " and "."'".$fecha_fin."'"; 
                    $filtro = true;   
    
                }else if(!empty($input_fecha)){
                    $daterange = explode(' - ',  str_replace('/', '-', $input_fecha));
                    $fecha_inicio = date('Y-m-d H:i:s', strtotime($daterange[0]));
                    $fecha_fin = date('Y-m-d H:i:s', strtotime($daterange[1]));
                    $input_fecha = $input_fecha;
    
                    $var_sql.= " and t.created_at between "."'".$fecha_inicio."'". " and "."'".$fecha_fin."'"; 
                    $filtro = true;

                }else if(!empty($input_descripcion)){
    
                    $var_sql.=  " and sxm.service_id =  ".$input_descripcion['service_id']." and sxm.service_source_id =  ".$input_descripcion['service_source_id']." ";  
                    $filtro = true;
                }  


               $sql = " 
                    select
                    o.name as red,
                    a.name as atm,
                    sxm.service_id,
                    sxm.service_source_id,
                    (m.descripcion || ' - ' || sxm.descripcion) as service,
                    count(DISTINCT t.id) as cantidad_transaccion,
                    sum(t.amount) as monto,
                    sum(t.commission_gross) as comision_bruta,
                    sum(t.commission_net) as comi_neta_eglobal,
                    sum(t.commission_net_level_1) as comi_neta_punto,
                    min(t.created_at) as inicio,
                    max(t.created_at) as fin
                    from
                    public.transactions t
                    join public.owners o on o.id = t.owner_id
                    join public.atms a on a.id = t.atm_id
                    join public.servicios_x_marca sxm on t.service_source_id = (
                        case
                        when sxm.service_source_id = 9 then 0
                        else sxm.service_source_id
                        end
                    )
                    and t.service_id = sxm.service_id
                    join public.marcas as m on m.id = sxm.marca_id
                    where
                    t.owner_id = $id
                    and a.id = $input_atm_id
                    and t.status = 'success'
                    and sxm.service_source_id  = t.service_source_id 
                    and sxm.service_id = t.service_id 
                    $var_sql
                    group by
                    red,
                    atm,
                    sxm.service_id,
                    sxm.service_source_id,
                    service
               ";
                
            //return $sql;                
            $resultado = $connection->select($sql);  
            //return $resultado;
            $resultado = json_decode(json_encode($resultado), true);

            //return $resultado; 
            //creamos el listado de proveedores y servicios a partir de la consulta principal
         
            
            if(!$filtro){

                $items_providers = [];
                $items_services = [];
                
                if (count($resultado) > 0) {
                    foreach($resultado as $key => $item) {
                    
                        //$provider = $item['proveedor'];
                        $service = $item['service'];
                        
    
                        $objeto2 = [
                            "name" => $service,
                            "service_id" => $item['service_id'],
                            "service_source_id" => $item['service_source_id']
                        ];
                        
                        if(!in_array($objeto2,$items_services)) {
                            array_push($items_services, $objeto2);
                        }
                               
                            
                    }
                }
            }else{
                //$items_providers = json_decode($lista_proveedores,true);
                $items_services = json_decode($lista_service,true);
                
            }

            //return $items_services;

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


                    $filename = 'commissions_report_' . time();

                    $columnas = array(
                        'Red','Atm','servicio','Total transacción', 'Monto','Comisión bruta','Comisón eglobal','Comisión para el punto','Inicio','Fin'
                    );

                    $excel = new ExcelExport($resultado,$columnas);
                    return Excel::download($excel, $filename . '.xls')->send();
                    
                    // Excel::create($filename, function ($excel) use ($resultado, $style_array) {
                    //     $excel->sheet('Registros', function ($sheet) use ($resultado, $style_array) {
                    //         $sheet->rows($resultado, false);
                            
                    //         $sheet->prependRow(array(
                    //             'Red','Atm','servicio','Total transacción', 'Monto','Comisión bruta','Comisón eglobal','Comisión para el punto','Inicio','Fin'
                    //         ));

                    //         $sheet->getStyle('A1:E1')->applyFromArray($style_array); //Aplicar los estilos del array
                    //         $sheet->setHeight(1, 30); //Aplicar tamaño de la primera fila
                    //     });

                    // })->export('xls');

                    $get_info = false;
                }
            }

            
          
            $data = [
                'lists' => [
                    'resultado' => $resultado,
                    'services' => $items_services
                ],
                'inputs' => [
                    'red' => $red,
                    'id' => $id,
                    'input_atm' => $input_atm,
                    'input_atm_id' => $input_atm_id,
                    'input_descripcion' => $input_descripcion_,
                    'fecha' => empty($input_fecha) ? $fecha : $input_fecha,
                    'service_atm' => $service_atm,
                    'proveedor_service' => $proveedor_service
                ]
            ];

            //return $data;
            return view('commissions.comissionGeneralesNivelTres', compact('data'));
            
        }catch (\Exception $e) {
            $error_detail = [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];

            Log::error("Error, Detalles: " . json_encode($error_detail));
        }
    }
    public function service_detallado_nivel4($request)
    {
        $data = $request->all();

        //return $data;
        //variables globales
        $id = $data['id'];
        $red = $data['red'];
        $fecha = isset($data['fecha']) && !empty($data['fecha']) ? $data['fecha'] : '' ;
        $atm = isset($data['atm_name']) && !empty($data['atm_name']) ? $data['atm_name'] : '';
        $proveedor = isset($data['proveedor']) && !empty($data['proveedor']) ? $data['proveedor'] : '';
        $servicio = isset($data['servicio']) && !empty($data['servicio']) ? $data['servicio'] : '';

        //variables de los filtros de la búsqueda
        $input_atm = isset($data['atm_name']) && !empty($data['atm_name']) ? $data['atm_name'] : '';
        $input_atm_id = isset($data['atm_id']) && !empty($data['atm_id']) ? $data['atm_id'] : '';
        $service_atm = isset($data['atm_service']) && !empty($data['atm_service']) ? $data['atm_service'] : '';
        $proveedor_service = isset($data['proveedor_service']) && !empty($data['proveedor_service']) ? $data['proveedor_service'] : '';
        $input_fecha = isset($data['reservationtime']) && !empty($data['reservationtime']) ? $data['reservationtime'] : '';
        $input_button = isset($data['button_name']) && !empty($data['button_name']) ? $data['button_name'] : ''; 
        $input_descripcion_ = isset($data['input_descripcion']) && !empty($data['input_descripcion']) ? $data['input_descripcion'] : ''; 
        $input_descripcion = json_decode($input_descripcion_,true);

        //dd($input_descripcion['service_id']);

        //heredamos el listado de proveedores y servicios
        $lista_proveedores = isset($data['lista_proveedor']) && !empty($data['lista_proveedor']) ? $data['lista_proveedor'] : '';
        $lista_service = isset($data['lista_service']) && !empty($data['lista_service']) ? $data['lista_service'] : '';

        //variables del servicio
        $var_sql = '';
        $get_info = true;
        $filtro = false;

        try{
            //cantidad total de transaccion por red
            $connection = DB::connection('eglobal');           
                 
                 if(!empty($fecha)){
                    $daterange = explode(' - ',  str_replace('/', '-', $fecha));
                    $fecha_inicio = date('Y-m-d H:i:s', strtotime($daterange[0]));
                    $fecha_fin = date('Y-m-d H:i:s', strtotime($daterange[1]));
                    $input_fecha = $input_fecha;
    
                    $var_sql.= " and t.created_at between "."'".$fecha_inicio."'". " and "."'".$fecha_fin."'";  

                 }else if(!empty($input_fecha)) {
                    $daterange = explode(' - ',  str_replace('/', '-', $input_fecha));
                    $fecha_inicio = date('Y-m-d H:i:s', strtotime($daterange[0]));
                    $fecha_fin = date('Y-m-d H:i:s', strtotime($daterange[1]));
                    $input_fecha = $input_fecha;
    
                    $var_sql.= " and t.created_at between "."'".$fecha_inicio."'". " and "."'".$fecha_fin."'"; 
                    $filtro = true;   
    
                }
                

               $sql = " 
                    select
                        o.name as red,
                        a.name as atm,
                        sxm.service_id,
                        sxm.service_source_id,
                        (m.descripcion || ' - ' || sxm.descripcion) as service,
                        t.id as cantidad_transaccion,
                        t.amount as monto,
                        t.commission_gross as comision_bruta,
                        t.commission_net as comi_neta_eglobal,
                        t.commission_net_level_1 as comi_neta_punto,
                        t.created_at as fecha
                    from public.transactions t
                        join public.owners o 
                            on o.id = t.owner_id
                        join public.atms a 
                            on a.id = t.atm_id
                        join public.servicios_x_marca sxm 
                            on t.service_source_id = (case when sxm.service_source_id = 9 then 0 else sxm.service_source_id end) and t.service_id = sxm.service_id
                        join public.marcas as m on m.id = sxm.marca_id
                    where t.owner_id = ".$id."
                        and a.id = '".$input_atm_id."'
                        and sxm.service_id = ".$input_descripcion['service_id']."
                        and sxm.service_source_id = ".$input_descripcion['service_source_id']."
                        and t.status = 'success'
                        ".$var_sql."
                        group by o.name,a.name,sxm.service_id, sxm.service_source_id, service, t.id
                ";
                
            //group by o.name,a.name, service, t.id
            //return $sql;        
            $resultado = $connection->select($sql);  
          
            $resultado = json_decode(json_encode($resultado), true);
            
            $items_services = [];
            $items_providers = [];


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


                    $filename = 'commissions_report_' . time();

                    $columnas = array(
                        'Red','Atm','Servicio','Transacción id', 'Monto','Comisión bruta','Comisón eglobal','Comisión para el punto','Fecha'
                    );

                    $excel = new ExcelExport($resultado,$columnas);
                    return Excel::download($excel, $filename . '.xls')->send();
                    
                    // Excel::create($filename, function ($excel) use ($resultado, $style_array) {
                    //     $excel->sheet('Registros', function ($sheet) use ($resultado, $style_array) {
                    //         $sheet->rows($resultado, false);
                            
                    //         $sheet->prependRow(array(
                    //             'Red','Atm','Servicio','Transacción id', 'Monto','Comisión bruta','Comisón eglobal','Comisión para el punto','Fecha'
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
                     

            }
            
          
            $data = [
                'lists' => [
                    'resultado' => $resultado,
                    'services' => $items_services,
                    'proveedor_services' => $items_providers
                ],
                'inputs' => [
                    'red' => $red,
                    'id' => $id,
                    'input_atm_id' => $input_atm_id,
                    'input_atm' => $input_atm,
                    'input_descripcion' => $input_descripcion_,
                    'proveedor' => $proveedor,
                    'servicio' => $servicio,
                    'fecha' => empty($input_fecha) ? $fecha : $input_fecha,
                    'service_atm' => $service_atm,
                    'proveedor_service' => $proveedor_service         
                ]
            ];

            //return $data;
            return view('commissions.comissionGeneralesNivelCuatro', compact('data'));
            
        }catch (\Exception $e) {
            $error_detail = [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];

            Log::error("Error, Detalles: " . json_encode($error_detail));
        }
    }

    /*
        Factura
    */
    public function comisionFactura($request, $data)
    {
        $var_sql = '';
        $var_sql = " where CAST (iq.status_code AS INTEGER) > 0 ";

        if($data['cliente']){

            $group_id = $this->user->bussines_group_id;
            //$group_id = 168;
            $var_sql = " and ipq.id_grupo_atm = $group_id ";
        }

         $input = $request->all();
         ini_set('max_execution_time', 0);
         ini_set('client_max_body_size', '20M');
         ini_set('max_input_vars', 10000);
         ini_set('upload_max_filesize', '20M');
         ini_set('post_max_size', '20M');
         ini_set('memory_limit', '-1');
         set_time_limit(3600);
       
         //variables globales
         $group_input = isset($input['group']) && !empty($input['group']) ? $input['group'] : '' ;
         $fecha_input = isset($input['reservationtime']) && !empty($input['reservationtime']) ? $input['reservationtime'] : '' ;
         
        
         //variables del servicio
         $data_invoice = [];
         $sql = "";
         $fecha_inicio = "";
         $fecha_fin = "";
         $input_fecha = "";
         $nombre_excel = "";   
         $get_info = true;
         
     
         try {
             
            //return $request['button_name'];

            if (isset($request['button_name_pdf'])) {
                if ($request['button_name_pdf'] == 'generate_pdf') {


                    $mostrar_spiner = true;

                    try{

                        $data = $request->all();
            
                        Log::debug(["FAC-CLIENTE-QR-DATA" => $data]);
            
                        $timbrado = DB::table('points_of_sale_vouchers_generic')
                            ->whereNull('owner_id')
                            ->whereNull('deleted_at')
                            ->orderBy('id', 'DESC')
                        ->first();
            
                        Log::debug(["FAC-CLIENTE-QR-TIMBRADO" => $timbrado]);
            
                        $grupo = DB::table('invoice_products_qr as ipq')
                        ->join('invoices_qr as iq', 'iq.id', '=', 'ipq.invoice_id')
                        ->select('ipq.*','iq.invoice_number', 'iq.status_code', 'iq.client_id', 'ipq.created_at')
                        ->where('ipq.invoice_id',$data['id_invoice'])
                        ->first();
            
                        Log::debug(["FAC-CLIENTE-QR-GRUPO" => $grupo]);
                        
                        $voucher_data = json_decode($grupo->voucher_data,false);
                        $fecha = !is_null($grupo->created_at) ? $grupo->created_at : Carbon::parse('2022-11-30')->format('d-m-Y');
            
                        $azaz = new NumberFormatter("es-PY", NumberFormatter::SPELLOUT);
                        $text = preg_replace('~\x{00AD}~u', '', $azaz->format($grupo->total_comision));
            
                        $porcentaje= $grupo->total_comision/11;
                        
                        $nombre='Factura-venta-qr-'. $grupo->nombre_grupo.'-'.$fecha.'.pdf';
                        
                        $view =  \View::make('commissions.pdfFactura', compact('grupo','fecha', 'text', 'porcentaje', 'timbrado','voucher_data'))->render();
                        $pdf = \App::make('dompdf.wrapper');
                        $pdf->loadHTML($view);
            
                        return $pdf->stream($nombre);
            
                    }catch(Exception $e){
                        Log::debug(["FAC-CLIENTE-QR" => $e->getMessage()]);
                    }
                    $get_info = false;
                    
                }
            }

            $connection = DB::connection('eglobal');         

            if(!empty($group_input)  && !empty($fecha_input)) {
                $daterange = explode(' - ',  str_replace('/', '-', $fecha_input));
                $fecha_inicio = date("Y-m-01 00:00:00",strtotime($daterange[1]));
                $fecha_fin = date("Y-m-t 23:59:59",strtotime($daterange[1]));
                $input_fecha = $input_fecha;

                $var_sql.=" and ipq.id_grupo_atm = '".$group_input."'". " and ipq.created_at between "."'".$fecha_inicio."'". " and "."'".$fecha_fin."'";    

            }else if(!empty($fecha_input)){
                $daterange = explode(' - ',  str_replace('/', '-', $fecha_input));
                $fecha_inicio = date("Y-m-01 00:00:00",strtotime($daterange[1]));
                $fecha_fin = date("Y-m-t 23:59:59",strtotime($daterange[1]));
                $input_fecha = $input_fecha;

                $var_sql.= " and ipq.created_at between "."'".$fecha_inicio."'". " and "."'".$fecha_fin."'"; 

            }else if(!empty($group_input)){

                $var_sql.=  " and ipq.id_grupo_atm  =  "."'".$group_input."'";  
            }

                $sql = " 
                        select 
                        iq.invoice_number , iq.status_code ,iq.client_id , iq.created_at ,ipq.*
                        from invoices_qr iq 
                        inner join invoice_products_qr ipq 
                        on iq.id = ipq.invoice_id 
                        ".$var_sql."
                        order by ipq.created_at desc
               
                ";

                

           // return $sql;
           
            if(!empty($group_input) || !empty($fecha_input)){
               $data_invoice = $connection->select($sql);   
               $data_invoice = json_decode(json_encode($data_invoice), true);
            }
            
            
            //return $data_invoice;   
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
                    
                    $sql = " 
                        select 
                        ipq.nombre_grupo,ipq.ruc_cliente,ipq.description, iq.invoice_number,ipq.total_comision,ipq.total_comision_td,ipq.total_comision_dc,ipq.total_comision_tc,ipq.created_at
                        from invoices_qr iq 
                        inner join invoice_products_qr ipq 
                        on iq.id = ipq.invoice_id 
                        ".$var_sql."
                        order by ipq.created_at desc
               
                    ";


                    $data_excel = $connection->select($sql);   
                    $data_excel = json_decode(json_encode($data_excel), true);            

                    $filename = 'commissions_report_' . time();

                    $columnas = array(
                        'Nombre','Ruc', 'Producto', 'Factura','Comisión total','Comisón TD','Comisión DC','Comisión TC','Mes'
                    );

                    $excel = new ExcelExport($data_excel,$columnas);
                    return Excel::download($excel, $filename . '.xls')->send();
                    
                    // Excel::create($filename, function ($excel) use ($data_excel, $style_array) {
                    //     $excel->sheet('Registros', function ($sheet) use ($data_excel, $style_array) {
                    //         $sheet->rows($data_excel, false);
                            
                    //         $sheet->prependRow(array(
                    //             'Nombre','Ruc', 'Producto', 'Factura','Comisión total','Comisón TD','Comisión DC','Comisión TC','Mes'
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
                    
                    $groups = $connection
                        ->table('public.business_groups as bg')
                        ->select(
                            'bg.id',
                            'bg.description'
                        )
                        ->whereNull('bg.deleted_at')
                        ->groupBy('bg.id','bg.description')
                        ->get();

                    $groups = json_decode(json_encode($groups), true);  
                    
                }

                $year = Carbon::now()->format('Y');
                $meses = ["Enero", "Febrero", "Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];
           
                $arra1 = array();

                for($i = 0 ; $i < count($meses) ; $i++){
                    
                    $objeto = [
                        'id' => $i + 1 ,
                        'mes' => $meses[$i],
                        'year' => $year
                    ];

                    array_push($arra1 , $objeto);
                }

                //return $arra1;
                
                $data = [
                    'lists' => [
                        'data_invoice' => $data_invoice,
                        'group' => $groups,
                        'meses' => $arra1
                    ],
                    'inputs' => [
                        'fecha' => $fecha_input,
                        'group' => $group_input
                    ]
                ];
                
                //return $data;
            
                return view('commissions.comisionFactura', compact('data'));
     
            }catch (\Exception $e) {
                $error_detail = [
                    'exception' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'class' => __CLASS__,
                    'function' => __FUNCTION__,
                    'line' => $e->getLine()
                ];
    
                Log::error("Error, Detalles: " . json_encode($error_detail));
            }
     
    }
    public function comisionFacturaCliente($request, $data)
    {
        //return $this->user;
        $var_sql = '';
        $var_sql = " where CAST (iq.status_code AS INTEGER) > 0 ";
        $execute_sql = true;

        if($data['cliente']){

            $user_id = $this->user->id;
            //$user_id = 1296 ; //801;//956; //801;
            $atm_per_user = DB::table('atms_per_users')->where('user_id', $user_id)->first();
        
            if(isset($atm_per_user) && !empty($atm_per_user) ){

                $busine_group = DB::table('points_of_sale as pos')
                ->join('branches as b','pos.branch_id', '=', 'b.id' )
                ->join('business_groups as bg','b.group_id', '=', 'bg.id' )
                ->select('bg.id')
                ->where('pos.atm_id',$atm_per_user->atm_id)
                ->first();

                $group_id = $busine_group->id; //797; //187

            }
           
            
            if(empty($group_id) || !isset($group_id)){
                $execute_sql = false;
            }else{
                $var_sql = " and ipq.id_grupo_atm = $group_id ";
            }

           // return ["valor" => $execute_sql];
            
            
        }

         $input = $request->all();
         ini_set('max_execution_time', 0);
         ini_set('client_max_body_size', '20M');
         ini_set('max_input_vars', 10000);
         ini_set('upload_max_filesize', '20M');
         ini_set('post_max_size', '20M');
         ini_set('memory_limit', '-1');
         set_time_limit(3600);
       
         //variables globales
         $group_input = isset($input['group']) && !empty($input['group']) ? $input['group'] : '' ;
         $fecha_input = isset($input['reservationtime']) && !empty($input['reservationtime']) ? $input['reservationtime'] : '' ;
         
        
         //variables del servicio
         $data_invoice = [];
         $sql = "";
         $fecha_inicio = "";
         $fecha_fin = "";
         $input_fecha = "";
         $nombre_excel = "";   
         $get_info = true;
         $mostrar_spiner = false;
         
     
         try {
             $connection = DB::connection('eglobal');         

            if(!empty($group_input)  && !empty($fecha_input)) {
                $daterange = explode(' - ',  str_replace('/', '-', $fecha_input));
                $fecha_inicio = date("Y-m-01 00:00:00",strtotime($daterange[1]));
                $fecha_fin = date("Y-m-t 23:59:59",strtotime($daterange[1]));
                $input_fecha = $input_fecha;

                $var_sql.=" and ipq.id_grupo_atm = '".$group_input."'". " and ipq.created_at between "."'".$fecha_inicio."'". " and "."'".$fecha_fin."'";    

            }else if(!empty($fecha_input)){
                $daterange = explode(' - ',  str_replace('/', '-', $fecha_input));
                $fecha_inicio = date("Y-m-01 00:00:00",strtotime($daterange[1]));
                $fecha_fin = date("Y-m-t 23:59:59",strtotime($daterange[1]));
                $input_fecha = $input_fecha;

                $var_sql.= " and ipq.created_at between "."'".$fecha_inicio."'". " and "."'".$fecha_fin."'"; 

            }else if(!empty($group_input)){

                $var_sql.=  " and ipq.id_grupo_atm  =  "."'".$group_input."'";  
            }

                $sql = " 
                        select 
                        iq.invoice_number , iq.status_code ,iq.client_id , iq.created_at ,ipq.*
                        from invoices_qr iq 
                        inner join invoice_products_qr ipq 
                        on iq.id = ipq.invoice_id 
                        ".$var_sql."
                        order by ipq.created_at desc
                        limit 12
                ";

            //return $sql;
            if($execute_sql){
                $data_invoice = $connection->select($sql); 
                $data_invoice = json_decode(json_encode($data_invoice), true);
            }
            
             //return $data_invoice;   
            if (isset($request['button_name'])) {
                if ($request['button_name'] == 'generate_x') {


                    $mostrar_spiner = true;

                    try{

                        $data = $request->all();
            
                        Log::debug(["FAC-CLIENTE-QR-DATA" => $data]);
            
                        $timbrado = DB::table('points_of_sale_vouchers_generic')
                            ->whereNull('owner_id')
                            ->whereNull('deleted_at')
                            ->orderBy('id', 'DESC')
                        ->first();
            
                        Log::debug(["FAC-CLIENTE-QR-TIMBRADO" => $timbrado]);
            
                        $grupo = DB::table('invoice_products_qr as ipq')
                        ->join('invoices_qr as iq', 'iq.id', '=', 'ipq.invoice_id')
                        ->select('ipq.*','iq.invoice_number', 'iq.status_code', 'iq.client_id', 'ipq.created_at')
                        ->where('ipq.invoice_id',$data['id_invoice'])
                        ->first();
            
                        Log::debug(["FAC-CLIENTE-QR-GRUPO" => $grupo]);
                        
                        $voucher_data = json_decode($grupo->voucher_data,false);
                        $fecha = !is_null($grupo->created_at) ? $grupo->created_at : Carbon::parse('2022-11-30')->format('d-m-Y');
            
                        $azaz = new NumberFormatter("es-PY", NumberFormatter::SPELLOUT);
                        $text = preg_replace('~\x{00AD}~u', '', $azaz->format($grupo->total_comision));
            
                        $porcentaje= $grupo->total_comision/11;
                        
                        $nombre='Factura-venta-qr-'. $grupo->nombre_grupo.'-'.$fecha.'.pdf';
                        
                        $view =  \View::make('commissions.pdfFactura', compact('grupo','fecha', 'text', 'porcentaje', 'timbrado','voucher_data'))->render();
                        $pdf = \App::make('dompdf.wrapper');
                        $pdf->loadHTML($view);
            
                        return $pdf->stream($nombre);
            
                    }catch(Exception $e){
                        Log::debug(["FAC-CLIENTE-QR" => $e->getMessage()]);
                    }
                    $get_info = false;
                    
                }
            }
     
                //Traer solo cuando hay búsqueda no cuando genera el excel.
                if ($get_info) {
                    
                    $groups = $connection
                        ->table('public.business_groups as bg')
                        ->select(
                            'bg.id',
                            'bg.description'
                        )
                        ->where('bg.deleted_at',null)
                        ->groupBy('bg.id','bg.description')
                        ->get();

                    $groups = json_decode(json_encode($groups), true);  
                    
                }

                $year = Carbon::now()->format('Y');
                $meses = ["Enero", "Febrero", "Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre"];
           
                $arra1 = array();

                for($i = 0 ; $i < count($meses) ; $i++){
                    
                    $objeto = [
                        'id' => $i + 1 ,
                        'mes' => $meses[$i],
                        'year' => $year
                    ];

                    array_push($arra1 , $objeto);
                }

                //return $arra1;
                
                $data = [
                    'lists' => [
                        'data_invoice' => isset($data_invoice) ? $data_invoice : [],
                        'group' => $groups,
                        'meses' => $arra1
                    ],
                    'inputs' => [
                        'fecha' => $fecha_input,
                        'group' => $group_input,
                        'mostrar_spiner' => $mostrar_spiner
                    ]
                ];
                
                //return $data;
            
                return view('commissions.comisionFacturaCliente', compact('data'));
     
            }catch (\Exception $e) {
                $error_detail = [
                    'exception' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'class' => __CLASS__,
                    'function' => __FUNCTION__,
                    'line' => $e->getLine()
                ];
    
                Log::error("Error, Detalles: " . json_encode($error_detail));
            }
     
    }

    public function generarFacturaQr($request)
    {

        try{

            $data = $request->all();

            Log::debug(["FAC-CLIENTE-QR-DATA" => $data]);

            $timbrado = DB::table('points_of_sale_vouchers_generic')
                ->whereNull('owner_id')
                ->whereNull('deleted_at')
                ->orderBy('id', 'DESC')
            ->first();

            Log::debug(["FAC-CLIENTE-QR-TIMBRADO" => $timbrado]);

            $grupo = DB::table('invoice_products_qr as ipq')
            ->join('invoices_qr as iq', 'iq.id', '=', 'ipq.invoice_id')
            ->select('ipq.*','iq.invoice_number', 'iq.status_code', 'iq.client_id', 'ipq.created_at')
            ->where('ipq.invoice_id',$data['id_invoice'])
            ->first();

            Log::debug(["FAC-CLIENTE-QR-GRUPO" => $grupo]);
            
            $voucher_data = json_decode($grupo->voucher_data,false);
            $fecha = !is_null($grupo->created_at) ? $grupo->created_at : Carbon::parse('2022-11-30')->format('d-m-Y');

            $azaz = new NumberFormatter("es-PY", NumberFormatter::SPELLOUT);
            $text = preg_replace('~\x{00AD}~u', '', $azaz->format($grupo->total_comision));

            $porcentaje= $grupo->total_comision/11;
            
            $nombre='Factura-venta-qr-'. $grupo->nombre_grupo.'-'.$fecha.'.pdf';
            
            $view =  \View::make('commissions.pdfFactura', compact('grupo','fecha', 'text', 'porcentaje', 'timbrado','voucher_data'))->render();
            $pdf = \App::make('dompdf.wrapper');
            $pdf->loadHTML($view);

            $pdf->stream($nombre);

        }catch(Exception $e){
            Log::debug(["FAC-CLIENTE-QR" => $e->getMessage()]);
        }

        
    }
}