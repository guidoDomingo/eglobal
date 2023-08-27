<?php

namespace App\Http\Controllers;

use App\Models\ServiciosMarca;
use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use Session;
use Carbon\Carbon;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ServiciosMarcasController extends Controller
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
        if (!$this->user->hasAccess('servicio_marca')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $name = $request->get('name');
        $servicios_marcas = ServiciosMarca::filterAndPaginate($name);        
        //$marcas = Owner::paginate(10);        
        return view('servicios_marcas.index', compact('servicios_marcas', 'name'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!$this->user->hasAccess('servicio_marca.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $marcas = \DB::table('marcas')
            ->selectRaw("marcas.id, CONCAT(marcas.descripcion,' - ', app_categories.name, ' | ', services_providers_sources.description) as marca")
            ->whereNull('marcas.deleted_at')
            ->join('app_categories', 'app_categories.id','=', 'marcas.categoria_id')
            ->join('services_providers_sources', 'services_providers_sources.id','=', 'marcas.service_source_id')
            ->orderBy('marcas.descripcion','asc')
            ->pluck('marca','marcas.id');

        $service_sources = \DB::table('services_providers_sources')->whereNull('deleted_at')->orderBy('description','asc')->pluck('description','id');

        return view('servicios_marcas.create', compact('marcas','service_sources'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param OwnerRequest|Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!$this->user->hasAccess('servicio_marca.add|edit')) {
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
                $input['created_at'] = Carbon::now();
                $input['updated_at'] = Carbon::now();
                if(!empty($input['imagen_asociada'])){
                    $imagen = $input['imagen_asociada'];
                    $data_imagen = json_decode($imagen);
                    $nombre_imagen = $data_imagen->name;
                    Storage::disk('marcas_servicios')->put($nombre_imagen,  base64_decode($data_imagen->data));
                    $input['imagen_asociada'] = '/images/button/'.$nombre_imagen;
                }else{
                    $input['imagen_asociada'] = '--';
                }

                unset($input['_token']);

                if(empty($input['promedio_comision'])){
                    unset($input['promedio_comision']);
                }

                if ($servicio_marca =  \DB::table('servicios_x_marca')->insert($input)){
                    \Log::info("New Servicio x Marca on the house !");
                    Session::flash('message', 'Nuevo servicio por marca creada correctamente');
                    return redirect('servicios_marca');
                }
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                Session::flash('error_message', 'Error al crear servicio por marca');
                return redirect()->back()->with('error', 'Error al crear el servicio por marca');
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
    public function edit($service_id,$service_source_id,Request $request)
    {

        if (!$this->user->hasAccess('servicio_marca.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if($servicio_marca = \DB::table('servicios_x_marca')->where('service_id',$service_id)->where('service_source_id', $service_source_id)->first()){
            $marcas = \DB::table('marcas')
                ->selectRaw("marcas.id, CONCAT(marcas.descripcion,' - ', app_categories.name, ' | ', services_providers_sources.description) as marca")
                ->whereNull('marcas.deleted_at')
                ->join('app_categories', 'app_categories.id','=', 'marcas.categoria_id')
                ->join('services_providers_sources', 'services_providers_sources.id','=', 'marcas.service_source_id')
                ->orderBy('marcas.descripcion','asc')
                ->pluck('marca','marcas.id');
            $service_sources = \DB::table('services_providers_sources')->whereNull('deleted_at')->orderBy('description','asc')->pluck('description','id');
            $data = [
                'servicio_marca' => $servicio_marca,
                'marcas' => $marcas,
                'service_sources' => $service_sources,
            ];


            return view('servicios_marcas.edit', $data);
        }else{
            Session::flash('error_message', 'Servicio Por Marca no encontrada');
            return redirect('servicios_marca');
        }

    }

    /**
     * Update the specified resource in storage.
     *
     * @param OwnerRequest|Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $service_id, $service_source_id)
    {
        if (!$this->user->hasAccess('servicio_marca.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if ($servicio_marca = \DB::table('servicios_x_marca')
            ->where('service_id', $service_id)
            ->where('service_source_id', $service_source_id)
            ->where(function($query){
                $query->whereNull('deleted_at')
                    ->orWhereNotNull('deleted_at');
            })->first()){
            $input = $request->all();
            try{
                $input['updated_at'] = Carbon::now();
                if(!empty($input['imagen_asociada'])){
                    $imagen = $input['imagen_asociada'];
                    $data_imagen = json_decode($imagen);
                    $nombre_imagen = $data_imagen->name;
                    $input['imagen_asociada'] = '/images/button/'.$nombre_imagen;
                    if($servicio_marca->imagen_asociada != $input['imagen_asociada']){
                        if(file_exists(public_path().'/resources'.trim($servicio_marca->imagen_asociada))){
                            unlink(public_path().'/resources'.trim($servicio_marca->imagen_asociada));
                        }
                        Storage::disk('marcas_servicios')->put($nombre_imagen,  base64_decode($data_imagen->data));
                    }else{
                        unset($input['imagen_asociada']);
                    }
                }

                \DB::table('servicios_x_atms')->where('marca_id', $servicio_marca->marca_id)
                    ->update([
                        'marca_id' => $input['marca_id']
                    ]);
                
                unset($input['_token']);
                unset($input['_method']);
                if(empty($input['promedio_comision'])){
                    $input['promedio_comision'] = null;
                }
                if(\DB::table('servicios_x_marca')->where('service_id', $service_id)->where('service_source_id', $service_source_id)->update($input)){
                    Session::flash('message', 'Servicio por Marca actualizada exitosamente');
                    return redirect('servicios_marca');
                }
            }catch (\Exception $e){
                \Log::error("Error updating network: " . $e->getMessage());
                Session::flash('error_message','Error al intentar actualizar el servicio por marca');
                return redirect('servicios_marca');
            }
        }else{
            \Log::warning("Servicio Marca not found");
            Session::flash('error_message', 'Servicio por marca no encontrado');
            return redirect('servicios_marca');
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($service_id, $service_source_id)
    {
        if (!$this->user->hasAccess('servicio_marca.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $message = '';
        $error = '';
        \Log::debug("Intentando eliminar servicio_id ".$service_id." - service source id ".$service_source_id);
        if (ServiciosMarca::where('service_id', $service_id)->where('service_source_id', $service_source_id)->first()){
            try{
                if (ServiciosMarca::where('service_id', $service_id)->where('service_source_id', $service_source_id)->delete()){
                    $message =  'Servicio por Marca eliminada correctamente';
                    $error = false;
                }
            }catch (\Exception $e){
                \Log::error("Error deleting network: " . $e->getMessage());
                $message =  'Error al intentar eliminar el servicio marca';
                $error = true;
            }
        }else{
            $message =  'Servicio por Marca no encontrada';
            $error = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }
    
}
