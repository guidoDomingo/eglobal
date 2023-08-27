<?php

namespace App\Http\Controllers;

use DB;

use Excel;
use Session;
use Carbon\Carbon;
use App\Models\Owner;
use App\Http\Requests;
use App\Models\ParamsRule;
use App\Models\ServiceRule;
use Illuminate\Http\Request;
use App\Models\ServiciosMarca;
use App\Http\Controllers\Controller;
use App\Http\Requests\ParamsRuleRequest;
use App\Http\Requests\ServicesRuleRequest;

class ServicesRuleController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function index(Request $request)
    {
        if (!$this->user->hasAccess('services_rules')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        
        $owners = Owner::all()->pluck('name','id');

        $name = $request->get('name');
        $servicesRules = ServiceRule::filterAndPaginate($name);

        $servicios = \DB::table('servicios_x_marca')
                    ->select('servicios_x_marca.service_id','servicios_x_marca.service_source_id','servicios_x_marca.descripcion', 'services_providers_sources.description')
                    ->join('services_providers_sources','servicios_x_marca.service_source_id' , '=','services_providers_sources.id' )
                    ->join('service_provider_products', 'servicios_x_marca.service_id', '=', 'service_provider_products.id')
                    ->get();

        return view('services_rule.index', compact('servicesRules','owners','servicios','name'));
    }
    
    public function create()
    {
        if (!$this->user->hasAccess('services_rules.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $owners = Owner::all()->pluck('name','id');
        $owners->prepend('Ninguno', '0');
        
        $services   = ServiciosMarca::all()->pluck('descripcion','marca_id');
        $services->prepend('Ninguno','-1');      
        
        return view('services_rule.create', compact('owners','services'));
    }


    public function store(ServicesRuleRequest $request)
    { 
        if (!$this->user->hasAccess('services_rules.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = $request->except('_token');
        if($request->ajax()){
            $respuesta = [];
            try{
                if ($servicio = ServiceRule::create($input)){
                    \Log::info("New params rule created !");
                    $respuesta['mensaje'] = 'Nueva services rule creado correctamente';
                    $respuesta['tipo'] = 'success';
                    $respuesta['data'] = $servicio;
                    return $respuesta;
                }
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Error al crear el services rule';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }
        }else{
            try{
                $input['service_source_id'] = null;   
                $input['service_id'] = null;   

                if($input['owner_id'] == '0'){
                    $input['owner_id'] = NULL;
                }

                if ($input['marca_id'] <> '-1'){
                    $marca = $input['marca_id'];
                    $variableSource =\DB::table('servicios_x_marca')
                                    ->select(\DB::raw('service_source_id::text'))
                                    ->where('marca_id',"=",$marca)
                                    ->get();
                    if(count($variableSource) > 0){
                        $input['service_source_id'] = $variableSource[0]->service_source_id;
                    }   

                    $variableService =\DB::table('servicios_x_marca')
                                    ->select(\DB::raw('service_id::text'))
                                    ->where('marca_id',"=",$marca)
                                    ->get();

                    if(count($variableService) > 0){
                        $input['service_id'] = $variableService[0]->service_id;
                    }                
                }
        
                \DB::table('service_rule')->insert([
                    'description'       => $input['description'],
                    'owner_id'          => $input['owner_id'],
                    'service_id'        => $input['service_id'],
                    'service_source_id' => $input['service_source_id'],
                    'message_user'      => $input['message_user'],
                ]);

                \Log::info("Services Rule ingresado correctamente!");
                Session::flash('message', 'Nueva regla de servicio creada correctamente');
                return redirect('services_rules');
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                Session::flash('error_message', 'Error al crear services rule');
                return redirect()->back()->with('error', 'Error al crear la regla de servicio');
            }
        } 
    }
    public function show($idservice_rule)
    {  
    }

    public function edit($idservice_rule)
    {   
        if (!$this->user->hasAccess('services_rules.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $owners = Owner::all()->pluck('name','id');
        $owners->prepend('Ninguno', '0');
        
        $services   = ServiciosMarca::all()->pluck('descripcion','marca_id');
        $services->prepend('Ninguno','-1');
        
        if($servicio = ServiceRule::find($idservice_rule)){
            $data = ['servicio' => $servicio];
            return view('services_rule.edit', $data, compact('owners','services'));
        }else{
            Session::flash('error_message', 'Service rules no encontrada');
            return redirect('');
        }
    }

    public function update(ServicesRuleRequest $request, $idservice_rule)
    {   
        if (!$this->user->hasAccess('services_rules.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if ($servicio = ServiceRule::find($idservice_rule)){
            $input = $request->all();
    
            try{
                $input['service_source_id'] = null;   
                $input['service_id'] = null;   

                if($input['owner_id'] == '0'){
                    $input['owner_id'] = NULL;
                }

                if ($input['marca_id'] <> '-1'){
                    $marca = $input['marca_id'];
                    $variableSource =\DB::table('servicios_x_marca')
                                    ->select(\DB::raw('service_source_id::text'))
                                    ->where('marca_id',"=",$marca)
                                    ->get();
                    if(count($variableSource) > 0){
                        $input['service_source_id'] = $variableSource[0]->service_source_id;
                    }   

                    $variableService =\DB::table('servicios_x_marca')
                                    ->select(\DB::raw('service_id::text'))
                                    ->where('marca_id',"=",$marca)
                                    ->get();

                    if(count($variableService) > 0){
                        $input['service_id'] = $variableService[0]->service_id;
                    }                
                }

                \DB::table('service_rule')->where('idservice_rule',$idservice_rule)
                                        ->update([
                    'description'       => $input['description'],
                    'owner_id'          => $input['owner_id'],
                    'service_id'        => $input['service_id'],
                    'service_source_id' => $input['service_source_id'],
                    'message_user'      => $input['message_user'],
                ]);
                \Log::info("Service Rule actualizada exitosamente");
                Session::flash('message', 'Reglas de servicios actualizada exitosamente');
                return redirect('services_rules');

            }catch (\Exception $e){
                \Log::error("Error updating network: " . $e->getMessage());
                Session::flash('error_message','Error al intentar actualizar la regla de servicio');
                return redirect('services_rules');
            }
        }else{
            \Log::warning("Paarams Rule not found");
            Session::flash('error_message', 'Regla de servicio no encontrada');
            return redirect('services_rules');
        }
    }

    public function destroy($idservice_rule)
    {  
        if (!$this->user->hasAccess('services_rules.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        } 
        $message = '';
        $error = '';
        \Log::debug("Attempting to delete a given Service rules");
        if (ServiceRule::find($idservice_rule)) {
            try {
                if (ServiceRule::destroy($idservice_rule)) {
                    $message =  'Service rules eliminada correctamente';
                    $error = false;
                }
            } catch (\Exception $e) {
                \Log::error("Error deleting params rules: " . $e->getMessage());
                $message =  'Error al intentar eliminar la regla de parametro';
                $error = true;
            }
        } else {
            $message =  'Service rules de parametro no encontrada';
            $error = true;
        }
        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }

   
}