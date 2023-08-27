<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Http\Requests\OwnerRequest;
use App\Models\Marca;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class MarcasController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!$this->user->hasAccess('marca')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $name = $request->get('name');
        $marcas = Marca::filterAndPaginate($name);
    
        //$marcas = Owner::paginate(10);
        return view('marcas.index', compact('marcas', 'name'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!$this->user->hasAccess('marca.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $categorias = \DB::table('app_categories')->orderBy('name','asc')->pluck('name','id');
        $service_sources = \DB::table('services_providers_sources')->orderBy('description','asc')->pluck('description','id');

        return view('marcas.create', compact('categorias','service_sources'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param OwnerRequest|Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!$this->user->hasAccess('marca.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $input = $request->all();

        if($request->ajax()){
            /*$respuesta = [];
            try{
                if ($owner = Owner::create($input)){
                    \Log::info("New Owner on the house !");
                    $respuesta['mensaje'] = 'Nueva red creada correctamente';
                    $respuesta['tipo'] = 'success';
                    $respuesta['data'] = $owner;
                    return $respuesta;
                }
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Error al crear la red';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }*/
        }else{
            try{
                if(!empty($input['imagen_asociada'])){
                    $imagen = $input['imagen_asociada'];
                    $data_imagen = json_decode($imagen);
                    $nombre_imagen = $data_imagen->name;
                    Storage::disk('marcas_servicios')->put($nombre_imagen,  base64_decode($data_imagen->data));
                    $input['imagen_asociada'] = 'https://cms.eglobalt.com.py/resources/images/button/'.$nombre_imagen;
                }else{
                    $input['imagen_asociada'] = '--';
                }

                if ($marca =  Marca::create($input)){
                    \Log::info("New Marca on the house !");
                    Session::flash('message', 'Nueva marca creada correctamente');
                    return redirect('marca');
                }
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                Session::flash('error_message', 'Error al crear la marca');
                return redirect()->back()->with('error', 'Error al crear marca');
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!$this->user->hasAccess('marca.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if($marca = Marca::find($id)){
            $categorias = \DB::table('app_categories')->orderBy('name','asc')->pluck('name','id');
            $service_sources = \DB::table('services_providers_sources')->orderBy('description','asc')->pluck('description','id');
            $data = [
                'marca' => $marca,
                'categorias' => $categorias,
                'service_sources' => $service_sources,
            ];
            //dd($data);
            return view('marcas.edit', $data);
        }else{
            Session::flash('error_message', 'Marca no encontrada');
            return redirect('marca');
        }

    }

    /**
     * Update the specified resource in storage.
     *
     * @param OwnerRequest|Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!$this->user->hasAccess('marca.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if ($marca = Marca::find($id)){
            $input = $request->all();
            try{
                if(!empty($input['imagen_asociada'])){
                    $imagen = $input['imagen_asociada'];
                    $data_imagen = json_decode($imagen);
                    $nombre_imagen = $data_imagen->name;
                    $input['imagen_asociada'] = '/images/button/'.$nombre_imagen;
                    if($marca->imagen_asociada != $input['imagen_asociada']){
                        if(file_exists(public_path().'/resources'.trim($marca->imagen_asociada))){
                            unlink(public_path().'/resources'.trim($marca->imagen_asociada));
                        }
                        Storage::disk('marcas_servicios')->put($nombre_imagen,  base64_decode($data_imagen->data));
                    }else{
                        unset($input['imagen_asociada']);
                    }
                }else{
                    if(strstr($marca->imagen_asociada, 'http')){
                        unset($input['imagen_asociada']);
                    }
                }
                //Actualización para guardar ruta completa de las imágenes
                $input['imagen_asociada'] = 'https://cms.eglobalt.com.py/resources'.$input['imagen_asociada'];
                $marca->fill($input);
                if($marca->update()){
                    Session::flash('message', 'Marca actualizada exitosamente');
                    return redirect('marca');
                }
            }catch (\Exception $e){
                \Log::error("Error updating network: " . $e->getMessage());
                Session::flash('error_message','Error al intentar actualizar la marca');
                return redirect('marca');
            }
        }else{
            \Log::warning("Marca not found");
            Session::flash('error_message', 'Marca no encontrada');
            return redirect('marca');
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!$this->user->hasAccess('marca.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $message = '';
        $error = '';
        \Log::debug("Intentando elimiar marca ".$id);
        if ($marca = Marca::find($id)){
            try{
                if(Marca::where('id',$id)->delete()){
                    $message =  'Marca eliminada correctamente';
                    $error = false;
                }
            }catch (\Exception $e){
                \Log::error("Error deleting network: " . $e->getMessage());
                $message =  'Error al intentar eliminar la marca';
                $error = true;
            }
        }else{
            $message =  'Marca no encontrada';
            $error = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function grilla_servicios(Request $request)
    {
        if (!$this->user->hasAccess('marca.grilla')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $atms = \DB::table('atms')->where('deleted_at', null)->pluck('name','id');
        $atms_base = \DB::table('atms')
            ->where('deleted_at', null)
            ->whereIn('id', function($query){
                $query->select('atm_id')
                    ->from('servicios_x_atms');
            })
            ->pluck('name','id');
        $atm_id = ($request->has('atm_id')) ? $request->get('atm_id'):null;


        $marcas = \DB::table('marcas')
            ->whereIN('marcas.service_source_id', [0,9])                                 
            ->whereNull('marcas.deleted_at')
            ->orderBy('descripcion');        
        $marcas_no_asociadas = [];
        if(!empty($atm_id)){
            $servicios_atm = \DB::table('servicios_x_atms')
                ->where('atm_id', $atm_id)
                ->whereNull('servicios_x_atms.marca_deleted_at')
                ->groupBy('marca_id')
                ->pluck('marca_id','marca_id');            
            $marcas->whereIn('id', $servicios_atm);
            
            $marcas_no_asociadas = \DB::table('marcas')
                ->whereNotIn('id', $servicios_atm)
                ->whereIN('marcas.service_source_id',[0,9])                
                ->whereNull('marcas.deleted_at')
                ->orderBy('marcas.descripcion')
                ->get();
        }

        $name = $request->get('name');
        if (trim($name) != "") {
            $marcas->where('descripcion', 'ILIKE', "%$name%");
        }

        $marcas = $marcas->paginate(25);

        $data = [];
        foreach($marcas as $key => $marca){
            $servicios = \DB::table('servicios_x_marca')
                ->select([
                    'servicios_x_marca.descripcion as servicio',
                    'servicios_x_marca.service_id',
                    'servicios_x_marca.service_source_id',
                    'servicios_x_marca.deleted_at',
                    'services_providers_sources.description as source',
                ])
                ->join('services_providers_sources', 'services_providers_sources.id','=', 'servicios_x_marca.service_source_id')
                ->orderBy('servicios_x_marca.created_at','desc')
                ->where('servicios_x_marca.marca_id', $marca->id);

            if(!empty($atm_id)){
                $servicios = $servicios->select([
                    'servicios_x_marca.descripcion as servicio',
                    'servicios_x_marca.service_id',
                    'servicios_x_marca.service_source_id',
                    'servicios_x_atms.deleted_at',
                    'services_providers_sources.description as source',
                ])
                ->leftJoin('servicios_x_atms', function($join) use($atm_id){
                    $join->on('servicios_x_atms.service_id', '=', 'servicios_x_marca.service_id');
                    $join->on('servicios_x_atms.service_source_id', '=', 'servicios_x_marca.service_source_id');
                    $join->where('servicios_x_atms.atm_id', '=', $atm_id);
                })
                ->where('servicios_x_atms.marca_id', $marca->id);

                // consulta para mostrar tambien los servicios que no tiene el atm filtrado
                $servicios_no_asociados = \DB::table('servicios_x_marca')
                    ->where('servicios_x_marca.marca_id', $marca->id)
                    ->whereNotIn('service_id', function($query) use($atm_id, $marca){
                        $query->select('service_id')
                            ->from('servicios_x_atms')
                            ->where('atm_id', $atm_id)
                            ->where('marca_id', $marca->id);
                    })
                    ->pluck('service_id', 'service_id');

                $servicios->orWhere(function($query) use($servicios_no_asociados, $marca){
                    $query->whereIn('servicios_x_marca.service_id', $servicios_no_asociados)
                        ->where('servicios_x_marca.marca_id', $marca->id);
                });
            }

            $servicios = $servicios->get();

            // Se muestran los servicios de las marcas activas (si el filtro es atm, se filtran por sus propias marcas activas)
            $data[$marca->id]['id'] = $marca->id;
            $data[$marca->id]['name'] = $marca->descripcion;
            $data[$marca->id]['imagen'] = $marca->imagen_asociada;
            $data[$marca->id]['servicios'] = [];


            foreach($servicios as $servicio){
                $parcial_data = [];
                $parcial_data['name'] = $servicio->servicio.' ('.$servicio->source.')';
                $parcial_data['id'] = $servicio->service_id.'|'.$servicio->service_source_id;
                $parcial_data['service_id'] = $servicio->service_id;
                if(!empty($atm_id)){
                    if(!in_array($servicio->service_id, $servicios_no_asociados)){
                        $parcial_data['selected'] = (empty($servicio->deleted_at)) ? true:false;
                    }else{
                        $parcial_data['selected'] = false;
                    }
                }else{
                    $parcial_data['selected'] = (empty($servicio->deleted_at)) ? true:false;
                }

                $data[$marca->id]['servicios'][] = $parcial_data;
            }
        }

        return view('marcas.grilla_servicios', compact('data', 'marcas', 'atms', 'atm_id', 'marcas_no_asociadas', 'atms_base'));
    }

    public function grilla_servicios_store(Request $request)
    {
        if (!$this->user->hasAccess('marca.guardar_grilla_general')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        try {
            $input = $request->all();

            if(isset($input['servicios'])){
                foreach ($input['servicios'] as $marca_id => $marcas) {
                    foreach ($marcas as $key => $servicio) {
                        if(is_array($servicio)){
                            if(isset($servicio['valores']['1'])){
                                # Array con los servicios a deshabilitar
                                foreach ($servicio['valores']['1'] as $key => $deshabilitar) {
                                    $data_servicio = explode('|', $deshabilitar);
                                    if(isset($input['atm_id'])){
                                        \Log::info($input['atm_id']);
                                        \DB::table('servicios_x_atms')->where([
                                            'atm_id' => $input['atm_id'],
                                            'marca_id' => $marca_id,
                                            'service_source_id' => $data_servicio[1],
                                            'service_id' => $data_servicio[0],
                                        ])->update([
                                            'deleted_at' => date('Y-m-d H:i:s')
                                        ]);
                                    }else{
                                        \DB::table('servicios_x_marca')->where([
                                            'marca_id' => $marca_id,
                                            'service_source_id' => $data_servicio[1],
                                            'service_id' => $data_servicio[0],
                                        ])->update([
                                            'deleted_at' => date('Y-m-d H:i:s')
                                        ]);
                                    }
                                }
                            }

                            if(isset($servicio['valores']['2'])){
                                # Array con los servicios a habilitar
                                foreach ($servicio['valores']['2'] as $key => $habilitar) {
                                    $data_servicio = explode('|', $habilitar);
                                    if(isset($input['atm_id'])){
                                        \Log::info($input['atm_id']);
                                        $servicio_nuevo = \DB::table('servicios_x_atms')->where([
                                            'atm_id' => $input['atm_id'],
                                            'marca_id' => $marca_id,
                                            'service_source_id' => $data_servicio[1],
                                            'service_id' => $data_servicio[0],
                                        ])->first();

                                        if(empty($servicio_nuevo)){
                                            \DB::table('servicios_x_atms')->insert([
                                                'atm_id' => $input['atm_id'],
                                                'service_id' => $data_servicio[0],
                                                'marca_id' => $marca_id,
                                                'service_source_id' => $data_servicio[1],
                                                'created_at' => date('Y-m-d H:i:s'),
                                                'updated_at' => date('Y-m-d H:i:s'),
                                            ]);
                                        }else{
                                            \DB::table('servicios_x_atms')->where([
                                                'atm_id' => $input['atm_id'],
                                                'marca_id' => $marca_id,
                                                'service_source_id' => $data_servicio[1],
                                                'service_id' => $data_servicio[0],
                                            ])->update([
                                                'deleted_at' => null
                                            ]);
                                        }
                                    }else{
                                        \DB::table('servicios_x_marca')->where([
                                            'marca_id' => $marca_id,
                                            'service_source_id' => $data_servicio[1],
                                            'service_id' => $data_servicio[0],
                                        ])->update([
                                            'deleted_at' => null
                                        ]);
                                    }

                                }
                            }
                        }
                    }
                }
            }

            Session::flash('message', 'Grilla de servicios actualizada exitosamente');
            return redirect()->back();
        } catch (Exception $e) {
            \Log::error('error', ['grilla_servicios_store' => $e]);
        }
    }
    
    public function grilla_servicios_atm_store(Request $request)
    {
        if (!$this->user->hasAccess('marca.guardar_grilla')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        try {
            $input = $request->all();
            $atm_id = $request->get('atm_id');
            $atm_base_id = $request->get('atm_base_id');

            if(empty($atm_base_id)){
                $servicios = \DB::table('servicios_x_marca')
                    ->select([
                        'servicios_x_marca.*'
                    ])
                    ->join('marcas', 'marcas.id', '=', 'servicios_x_marca.marca_id')
                    //->where('marcas.service_source_id', 0)
                    ->whereIN('marcas.service_source_id',[0,9])                
                    ->get();

            }else{
                $servicios = \DB::table('servicios_x_atms')
                    ->select([
                        'servicios_x_atms.*'
                    ])
                    ->where('atm_id', $atm_base_id)
                    ->get();
            }

            $servicios_atm = [];
            foreach ($servicios as $key => $servicio) {
                $data = [];
                $data['atm_id'] = $atm_id;
                $data['service_id'] = $servicio->service_id;
                $data['marca_id'] = $servicio->marca_id;
                $data['service_source_id'] = $servicio->service_source_id;
                $data['created_at'] = date('Y-m-d H:i:s');
                $data['updated_at'] = date('Y-m-d H:i:s');
                $data['deleted_at'] = $servicio->deleted_at;

                if(!empty($atm_base_id)){
                    $data['marca_deleted_at'] = $servicio->marca_deleted_at;
                }

                $servicios_atm[] = $data;
            }

            \DB::table('servicios_x_atms')->insert($servicios_atm);

            Session::flash('message', 'Grilla de servicios generada exitosamente');

            return redirect()->back();
        } catch (Exception $e) {
            \Log::error('error', ['grilla_servicios_store' => $e]);
        }
    }

    public function quitarMarcaAtm($marca_id, $atm_id)
    {
        if (!$this->user->hasAccess('marca.quitar_marca_grilla')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        try {
            $servicios = \DB::table('servicios_x_atms')
                ->where([
                    'marca_id' => $marca_id,
                    'atm_id' => $atm_id,
                ])
                ->update([
                    'marca_deleted_at' => date('Y-m-d H:i:s')
                ]);

            Session::flash('message', 'Grilla de servicios actualizada exitosamente');

            return redirect()->back();
        } catch (Exception $e) {
            \Log::error('error', ['quitarMarcaAtm' => $e]);
        }
    }

    public function activar_marca(Request $request){
        if (!$this->user->hasAnyAccess('marca.activar_marca_grilla')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $atm_id = $request->_atm_id;
        $marca_id  = $request->_marca_id;
        $value  = $request->_value;

        if($value == true){
            $servicios = \DB::table('servicios_x_marca')
                ->select([
                    'servicios_x_marca.*'
                ])
                ->join('marcas', 'marcas.id', '=', 'servicios_x_marca.marca_id')
                ->where('marcas.id', $marca_id)
                ->get();

            $servicios_atm = [];
            foreach ($servicios as $key => $servicio) {
                $servicio_nuevo = \DB::table('servicios_x_atms')->where([
                    'atm_id' => $atm_id,
                    'marca_id' => $marca_id,
                    'service_source_id' => $servicio->service_source_id,
                    'service_id' => $servicio->service_id,
                ])->first();

                if(empty($servicio_nuevo)){
                    \DB::table('servicios_x_atms')->insert([
                        'atm_id' => $atm_id,
                        'service_id' => $servicio->service_id,
                        'marca_id' => $marca_id,
                        'service_source_id' => $servicio->service_source_id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                        'deleted_at' => $servicio->deleted_at,
                    ]);
                }else{
                    \DB::table('servicios_x_atms')->where([
                        'atm_id' => $atm_id,
                        'marca_id' => $marca_id,
                        'service_source_id' => $servicio->service_source_id,
                        'service_id' => $servicio->service_id,
                    ])->update([
                        'deleted_at' => $servicio_nuevo->deleted_at,
                        'marca_deleted_at' => null
                    ]);
                }
            }

            \Log::info('ATM con nueva marca habilitada: '.$marca_id.' | atm_id '.$atm_id.' - Autorizado por: '.$this->user->username .' el '.date('d/m/Y H:i:s'));
        }

        if($value == false){
            $servicios = \DB::table('servicios_x_atms')
                ->where([
                    'marca_id' => $marca_id,
                    'atm_id' => $atm_id,
                ])
                ->update([
                    'marca_deleted_at' => date('Y-m-d H:i:s')
                ]);
            \Log::info('ATM con nueva marca deshabilitada: '.$marca_id.' | atm_id '.$atm_id.' - Autorizado por: '.$this->user->username .' el '.date('d/m/Y H:i:s'));
        }

        $response['error'] = false;
        return $response;

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function consolidar(Request $request)
    {
        if (!$this->user->hasAccess('marca.consolidar')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if($request->isMethod('post')){
            try {
                $marca_principal = $request->get('marca_id');
                $marcas_a_consolidar = $request->get('marcas_varias_id');
                \DB::table('servicios_x_marca')
                    ->whereIn('marca_id', $marcas_a_consolidar)
                    ->update([
                        'marca_id' => $marca_principal
                    ]);

                \DB::table('marcas')
                    ->whereIn('id', $marcas_a_consolidar)
                    ->whereNotIn('id', [$marca_principal])
                    ->update([
                        'deleted_at' => date('Y-m-d H:i:s')
                    ]);
                
                Session::flash('message', 'Marca consolidada correctamente');
                
            } catch (Exception $e) {
                \Log::error($e);
                Session::flash('error_message', 'Ha ocurrido un error');
            }
        }

        $marcas_eglobalt = \DB::table('marcas')
            ->whereIn('marcas.service_source_id', [0, 9])
            ->whereNull('marcas.deleted_at')
            ->orderBy('marcas.descripcion','asc')
            ->join('servicios_x_marca', 'servicios_x_marca.marca_id', '=', 'marcas.id')
            ->pluck(\DB::raw("concat(marcas.descripcion, ' ', '#',id) as descripcion"),'id');

        $marcas = \DB::table('marcas')
            ->whereIn('marcas.service_source_id', [1, 4, 7, 0, 9])
            ->join('servicios_x_marca', 'servicios_x_marca.marca_id', '=', 'marcas.id')
            ->orderBy('marcas.descripcion','asc')
            ->pluck(\DB::raw("concat(marcas.descripcion, ' ', '#',marcas.id) as descripcion"),'marcas.id');

        return view('marcas.consolidacion', compact('marcas', 'marcas_eglobalt'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function order(Request $request)
    {
        if (!$this->user->hasAccess('marca.order')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if($request->isMethod('post')){
            try {
                $orden = $request->orden;
                if(!empty($orden)){
                    $orden = str_replace('item[]=', '', $orden);
                    $orden = explode('&', $orden);
                    $i = 1;

                    foreach ($orden as $key => $marca_id) {
                        \DB::table('marcas')
                            ->where('id', $marca_id)
                            ->update([
                                'order' => $i
                            ]);
                        $i++;
                    }
                }

                $response['error'] = false;
                return $response;
                
            } catch (Exception $e) {
                \Log::error($e);
                $response['error'] = false;
                return $response;
            }
        }

        $categorias = \DB::table('app_categories')
            ->where('owner_id', 11)
            ->pluck('name', 'id');

        $marcas = [];

        return view('marcas.order', compact('marcas', 'categorias'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function get_by_category(Request $request)
    {
        if (!$this->user->hasAccess('marca.order')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $categoria_id = $request->categoria_id;

        $marcas = \DB::table('marcas')
            ->select(['id', 'descripcion', 'imagen_asociada'])
            ->whereIn('marcas.service_source_id', [0, 9])
            ->where('marcas.categoria_id', $categoria_id)
            ->whereNull('marcas.deleted_at')
            ->orderBy('marcas.order','asc')
            ->get();

        $order = '';
        foreach ($marcas as $key => $marca) {
            $order .= '<div class="col-md-3 ventana" id="item-'.$marca->id.'">';
            $order .= '<div class="col-md-12 ventana-2">';
            if(strstr($marca->imagen_asociada, 'http')){
                $order .= '<img class="imagen_marcas_servicios" src="'.$marca->imagen_asociada.'">';
            }else{
                if(base64_encode(base64_decode($marca->imagen_asociada, true)) === $marca->imagen_asociada && !empty($marca->imagen_asociada)){
                    $order .= '<img class="imagen_marcas_servicios" src="data:image/png;base64,'.$marca->imagen_asociada.'">';
                }else if(file_exists(public_path().'/resources'.trim($marca->imagen_asociada)) && !empty($marca->imagen_asociada)){
                    $order .= '<img class="imagen_marcas_servicios" src="'.url("/resources".$marca->imagen_asociada).'">';
                }
            }
            $order .= '<label class="marca-descripcion">'.$marca->descripcion.'</label>';
            $order .= '</div>';
            $order .= '</div>';
        }

        return $order;
    }
}
