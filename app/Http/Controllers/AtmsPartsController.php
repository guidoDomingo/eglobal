<?php

namespace App\Http\Controllers;

use App\Exports\ExcelExport;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

use Excel;

class AtmsPartsController extends Controller
{
    /**
     * @var class $user: Usuario
     * @global object 
     */
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    /**
     * Muestra todas las partes de los atms.
     */
    public function index(Request $request)
    {
        $username = $this->user->username;
        $action = \Request::route()->getActionName();

        if (!$this->user->hasAccess('atms_parts')) {
            \Log::error("El usuario: $username no tiene permisos para la realizar la acción: $action");
            \Session::flash('error_message', 'No posee permisos para realizar esta acción.');
            return redirect('/');
        }

        //capacidad maxima - capacidad actual por cada billete

        $user = \Sentinel::getUser();
        $user_id = $user->id;

        $records_list = \DB::table('atms as a')
            ->select(
                'a.id as atm_id',
                \DB::raw('ap.denominacion::text as denominacion'),
                \DB::raw("(ap.cantidad_maxima - ap.cantidad_actual) as actual")
            )
            ->join('atms_parts as ap', 'a.id', '=', 'ap.atm_id')
            ->whereRaw('a.owner_id not in (16, 21, 25)')
            ->whereRaw("a.deleted_at is null")
            ->whereRaw("ap.tipo_partes in ('Cassette', 'Hopper')");
            

        $submit = 'search';

        $owner_id = '';
        $atm_id = '';

        if (isset($request['owner_id'])) {
            $owner_id = $request['owner_id'];
            $atm_id = $request['atm_id'];

            if ($owner_id !== '') {
                $records_list = $records_list->where('a.owner_id', intval($owner_id));
            }

            if ($atm_id !== '') {
                $records_list = $records_list->where('a.id', intval($atm_id));
            }

            if (isset($request['button_name'])) {
                $submit = $request['button_name'];
            }

            \Log::info("$submit");
            
        } else {
            $atm_id = '';
            $owner_id = '';
        }

        $records_list = $records_list->orderBy('ap.denominacion', 'ASC');

        \Log::info($records_list->toSql());

        $records_list = $records_list->get();

        $atms = \DB::table('atms as a')
            ->select(
                'a.id',
                \DB::raw("'#' || a.id || '  ' || upper(a.name || '  ( ' || o.name || ' )') as description"),
                'b.description as branch',
                'u.description as user'
            )
            ->join('owners as o', 'o.id', '=', 'a.owner_id')
            ->join('points_of_sale as pos', 'a.id', '=', 'pos.atm_id')
            ->join('branches as b', 'b.id', '=', 'pos.branch_id')
            ->join('users as u', 'u.id', '=', 'b.user_id')
            ->whereRaw('o.id not in (16, 21, 25)')
            ->whereRaw("a.deleted_at is null");


        if ($owner_id !== '') {
            $atms = $atms->where('a.owner_id', intval($owner_id));
        }

        $atms = $atms->orderBy('a.id', 'ASC');        

        //\Log::info($atms->toSql());

        $atms = $atms->get();

        $atms_list = [];

        $add = false;
        $stop = false;

        foreach ($atms as $item) {

            if ($atm_id !== '') {
                if ($atm_id == $item->id) {
                    $add = true;
                    $stop = true;
                }
            } else {
                $add = true;
            }

            if ($add) {
                $new_item = [
                    'id' => $item->id,
                    'description' => $item->description,
                    'branch' => $item->branch,
                    'user' => $item->user,
                    '50' => 0,
                    '100' => 0,
                    '500' => 0,
                    '1000' => 0,
                    '2000' => 0,
                    '5000' => 0,
                    '10000' => 0,
                    '20000' => 0,
                    '50000' => 0,
                    '100000' => 0
               ];

                foreach ($records_list as $item_aux) {
    
                    $atm_id_aux = $item_aux->atm_id;
                    $denominacion_aux = $item_aux->denominacion;
                    $actual = $item_aux->actual;                    
                    if ($new_item['id'] == $atm_id_aux) {
                        $new_item[$denominacion_aux] = $new_item[$denominacion_aux] + $actual;                        
                    }                                              
                }
    
                array_push($atms_list, $new_item);
            }

            if ($stop) {
                break;
            }
        }

        //\Log::info('atms_list:');
        //\Log::info($atms_list);

        $message = '';

        if ($submit == 'generate_x') {

            /********detalle reporte*****************/
            $detalle_reporte = array();
            $column_detalle = [];
            $column_faltante = [];
            $objeto_detalle = [];
            $objeto = [];

            $detalle_general = [];
          
            $atm_id = isset($request['atm_id']) ? $request['atm_id'] : ''; 

            $atm_status = false;
            
            $detalle_reporte = \DB::table('atms_parts as ap')
            ->join('atms as a', 'a.id', '=', 'ap.atm_id')
            ->select('ap.atm_id','a.name', 'ap.tipo_partes', 'ap.nombre_parte', 'ap.denominacion', 'ap.cantidad_minima', 'ap.cantidad_actual', 'ap.cantidad_maxima', \DB::raw('ap.cantidad_maxima - ap.cantidad_actual as monto_a_abastecer'))
            //->where('atm_id', '=', $atm_id)
            ->where('tipo_partes', '<>', 'Purga')
            ->where('tipo_partes', '<>', 'Box')
            ->where('activo', '=', true)
            ->orderBy('denominacion','desc');

            if ($atm_id != "") {
                $detalle_reporte = $detalle_reporte->where('ap.atm_id', intval($atm_id));
                $atm_status = true;
            }

            $detalle_reporte = $detalle_reporte->get();
            
            if($atm_status){
                $atms = $detalle_reporte;
            }

            foreach ($atms as $key => $item) {
                $new_item = [
                        'id' => isset($item->id) ? $item->id : $item->atm_id,
                        'description' => isset($item->description) ? $item->description : '',
                        'Hopper1' => 0,
                        'Hopper2' => 0,
                        'Hopper3' => 0,
                        'Hopper4' => 0,
                        'Cass1' => 0,
                        'Cass2' => 0,
                        'Cass3' => 0,
                        'Cass4' => 0,
                        'Cass5' => 0,
                        'Cass6' => 0,
                        'CHIP Tigo' => 0,
                        'Purga' => 0
                ];

                foreach ($detalle_reporte as $item1) {

                    $atm_id_aux = $item1->atm_id;
                    $nombre_parte_aux = $item1->nombre_parte;
                                        
                    if ($new_item['id'] == $atm_id_aux) {
                        $new_item[$nombre_parte_aux] = $item1->monto_a_abastecer;                        
                    }   
                } 

                
                array_push($detalle_general, $new_item);
                

                if($atm_status){
                    break;
                }
            
            }
            /***************************/
            
            $data_to_excel = $atms_list;

            if (count($data_to_excel) > 0) {
                $filename = 'atms_parts_' . time();

                $style_array = [
                    'font'  => [
                        'bold'  => true,
                        'color' => ['rgb' => '367fa9'],
                        'size'  => 10,
                        'name'  => 'Verdana'
                    ]
                ];

                $columna1 = array(
                    'ATM ID', 'Descripción', 'Sede', 'Encargado', 
                    '50', '100', '500', '1000', '2000', '5000', '10000', '20000', '50000', '100000'
                );

                $columna2 = [
                        'id',
                        'description',
                        'Hopper1',
                        'Hopper2',
                        'Hopper3',
                        'Hopper4',
                        'Cass1',
                        'Cass2',
                        'Cass3',
                        'Cass4',
                        'Cass5',
                        'Cass6',
                        'CHIP Tigo',
                        'Purga'
                ];

                $excel = new ExcelExport($data_to_excel,$columna1,$detalle_general,$columna2);
                return Excel::download($excel, $filename . '.xls');
                
                //return Excel::download($excel, $filename . '.xls')->send();

                // Excel::create($filename, function ($excel) use ( $data_to_excel,$detalle_general, $column_detalle, $style_array) {

                //     $excel->sheet('Detalle por denominación', function ($sheet) use ($data_to_excel, $style_array) {
                //         $sheet->rows($data_to_excel, false);
                //         $sheet->prependRow(array(
                //             'ATM ID', 'Descripción', 'Sede', 'Encargado', 
                //             '50', '100', '500', '1000', '2000', '5000', '10000', '20000', '50000', '100000'
                //         ));
                //         $sheet->getStyle('A1:N1')->applyFromArray($style_array);
                //         $sheet->setHeight(1, 40);
                //     });

                //     /*Hoja 2*/
                  
                //         $excel->sheet('Detalle por parte', function ($sheet) use ($column_detalle,$detalle_general, $style_array) {
                //             $sheet->rows($detalle_general, false);
                //             $sheet->prependRow([
                //                 'id',
                //                 'description',
                //                 'Hopper1',
                //                 'Hopper2',
                //                 'Hopper3',
                //                 'Hopper4',
                //                 'Cass1',
                //                 'Cass2',
                //                 'Cass3',
                //                 'Cass4',
                //                 'Cass5',
                //                 'Cass6',
                //                 'CHIP Tigo',
                //                 'Purga'
                //         ]);
                //             $sheet->getStyle('A1:N1')->applyFromArray($style_array);
                //             $sheet->setHeight(1, 40);
                //         });
                    

                // })->export('xls');
                //exit();
            } else {
                $message = 'No hay registros para exportar.';
            }
        }


        $owners = \DB::table('owners as o')
            ->select(
                'o.id',
                \DB::raw("'#' || o.id || ' ' || upper(o.name) as description")
            )
            ->whereRaw('o.id not in (16, 21, 25)')
            ->orderBy('o.id', 'ASC')
            ->get();

        $inputs = [
            'atm_id' => $atm_id,
            'owner_id' => $owner_id,
        ];

        $data = [
            'lists' => [
                'atms_list' => $atms_list,
                'atms' => json_encode($atms),
                'owners' => json_encode($owners)
            ],
            'inputs' => json_encode($inputs)
        ];

        if ($message !== '') {
            \Session::flash('error_message', $message);
        }

        return view('atm.atms_parts', compact('data'));
    }
}