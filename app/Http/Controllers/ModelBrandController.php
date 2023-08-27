<?php

namespace App\Http\Controllers;

use DB;
use Excel;
use Session;

use HttpClient;
use Carbon\Carbon;
use App\Models\User;
use App\Http\Requests;
use App\Models\Device;
use App\Models\Housing;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeviceRequest;
use App\Models\Brand;
use App\Models\ModelBrand;

class ModelBrandController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }
    
    public function index($brandId, Request $request)
    {
        if (!$this->user->hasAccess('model')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $name = $request->get('name');
        $modelos = ModelBrand::filterAndPaginate($brandId, $name);
        return view('modelBrand.index', compact('modelos', 'name', 'brandId'));
    }

    
    public function create($brandId)
    {  
        if (!$this->user->hasAccess('model.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        return view('modelBrand.create', compact('brandId'));    
    }

    
    public function store($brandId, Request $request)
    {
        if (!$this->user->hasAccess('model.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $ahora = Carbon::now();
        $model = new ModelBrand;
        $model->fill($request->all());
        $priority_input           = $request->priority==1?true:false;
        $model->fill(['priority' => $priority_input]);
        $model->fill(['brand_id' => $brandId]);

     //dd($request);
        if($request->ajax()){
            $respuesta = [];
            try{
                if ($model->save()) {
                    $data = [];
                    $data['id'] = $model->id;
                    //$data['serialnumber'] = $model->serialnumber;
                    $data['description'] = $model->description;
                    $data['activated_at'] = $model->activated_at;

                    \Log::info("Nuevo modelo creado");
                    $respuesta['mensaje'] = 'Modelo creado correctamente';
                    $respuesta['tipo'] = 'success';
                    $respuesta['data'] = $data;
                    return $respuesta;
                } else {
                   \Log::critical($e->getMessage());
                    $respuesta['mensaje'] = 'Error al crear el modelo';
                    $respuesta['tipo'] = 'error';
                    return $respuesta;
                }
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Error al crear el modelo';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }
        }else{
            if ($model->save()) {
                $message = 'Agregado correctamente el modelo';
                Session::flash('message', $message);
                return redirect()->route('model.brand.index', $brandId);
            }
        }



    }


    public function show( Request $request)
    {
       
    }


    public function edit($id,$modelo)
    {  
        if (!$this->user->hasAccess('model.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $modelo = ModelBrand::find($modelo);
        $brands = Brand::all()->pluck('description', 'id');

        $data = [
            'modelo'       => $modelo,
            'brandId'    => $id,
            'brands'    => $brands
        ];
        return view('modelBrand.edit', $data);
    }

    public function update(Request $request, $id, $modelo)
    {  
        if (!$this->user->hasAccess('model.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        } 
        $brandId = $id;
        $ahora = Carbon::now();

        if ($modelo = ModelBrand::find($modelo)){
            $input = $request->all();
            
            try{
              
                $modelo->fill($input);
                $priority_store   = $request->priority==1?true:false;
                $modelo->fill(['priority' => $priority_store]);
                //dd($device);
                if($modelo->update()){
                    Session::flash('message', 'Modelo actualizado exitosamente');
                    $modelos = ModelBrand::where('brand_id',$brandId)->paginate(10);
                    //$devices = Device::orderBy('id', 'desc')->paginate(10);
                    return view('modelBrand.index', compact('modelos','brandId'));
                }
            }catch (\Exception $e){
                \Log::error("Error updating Model: " . $e->getMessage());
                Session::flash('error_message','Error al intentar actualizar el modelo');
                $modelos = ModelBrand::orderBy('id', 'desc')->paginate(10);
                return view('modelBrand.index', compact('modelos','brandId'));
            }
        }else{
            \Log::warning("Model not found");
            Session::flash('error_message', 'Modelo no encontrado');
            $modelos = ModelBrand::orderBy('id', 'desc')->paginate(20);
            return view('modelBrand.index', compact('modelos','brandId'));
        }
    }

 
    public function destroy($brandId,$modelo_id)
    { 
        if (!$this->user->hasAccess('model.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $message = '';
        $error = '';
        \Log::debug("Attempting to delete a given Model");
        if (ModelBrand::find($modelo_id)){
            try{
                if (ModelBrand::destroy($modelo_id)){
                    $message =  'Modelo eliminado correctamente';
                    $error = false;
                }
            }catch (\Exception $e){
                \Log::error("Error deleting Model: " . $e->getMessage());
                $message =  'Error al intentar eliminar el modelo';
                $error = true;
            }
        }else{
            $message =  'Modelo no encontrado';
            $error = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }
        



}
