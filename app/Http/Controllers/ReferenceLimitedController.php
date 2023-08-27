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
use App\Http\Requests\ReferencesRuleRequest;
use App\Models\ReferenceLimited;

class ReferenceLimitedController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function index(Request $request)
    {
        if (!$this->user->hasAccess('references_rules')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        
        $name = $request->get('name') ?? "";
        $references = ReferenceLimited::filterAndPaginate($name);
        $servicesRules = ServiceRule::get();
        $paramsRules= ParamsRule::get();


     
        return view('references_limited.index', compact('servicesRules','references','paramsRules','name'));
    }
    
    public function create()
    {
        if (!$this->user->hasAccess('references_rules.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $serviciosRules = ServiceRule::all()->pluck('description','idservice_rule');
        $serviciosRules->prepend('Ninguno', '0');
        $parametrosRules= ParamsRule::all()->pluck('description','idparam_rules');
        $parametrosRules->prepend('Ninguno', '0');
        return view('references_limited.create', compact('parametrosRules','serviciosRules'));
    }



    public function store(ReferencesRuleRequest $request)
    { 
        if (!$this->user->hasAccess('references_rules.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = $request->except('_token');
    
        try{            
            //dd($input);
            \DB::table('reference_limited')->insert([
                    'current_params_rule_id'    => $input['current_params_rule_id'],
                    'service_rule_id'           => $input['service_rule_id'],
                    'reference'                 => $input['reference'],                    
                    'created_at'                => Carbon::now(),
                    'frequency_last_updated'     => Carbon::now(),
                    ]);

                \Log::info("Referencia ingresado correctamente!");
                Session::flash('message', 'Nueva referencia creada correctamente');
                return redirect('references');
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                Session::flash('error_message', 'Error al crear referencia');
                return redirect()->back()->with('error', 'Error al crear la referencia');
            }
        
    }
    public function show($idservice_rule)
    {  
    }

    public function edit($service_rule_id , $current_params_rule_id , $reference , Request $request)
    {   
    
        if (!$this->user->hasAccess('references_rules.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if($reference_limited = \DB::table('reference_limited')->where('service_rule_id', $service_rule_id)->where('current_params_rule_id', $current_params_rule_id)->where('reference', $reference)->first()){
            $serviciosRules = ServiceRule::all()->pluck('description','idservice_rule');
            $parametrosRules= ParamsRule::all()->pluck('description','idparam_rules');            
            $frequency =$reference_limited->frequency_last_updated;
            $data = [
                'reference_limited' => $reference_limited,
                'serviciosRules' => $serviciosRules,
                'parametrosRules' => $parametrosRules,                                
            ];

            return view('references_limited.edit', $data);
        }else{
            Session::flash('error_message', 'Referencia no encontrada');
            return redirect('references');
        }


        
    }

    public function update(Request $request, $idparam_rules, $current_params_rule_id,$reference)
    {   
        if (!$this->user->hasAccess('references_rules.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if ($reference_limited = \DB::table('reference_limited')
                                    ->where('service_rule_id', $idparam_rules)
                                    ->where('current_params_rule_id', $current_params_rule_id)
                                    ->where('reference', $reference)
                                    ->first()) {
            $input = $request->all();
            try{
                $input['updated_at'] = Carbon::now();                
           
                unset($input['_token']);
                unset($input['_method']);
                
               
                if(\DB::table('reference_limited')->where('service_rule_id', $idparam_rules)->where('current_params_rule_id', $current_params_rule_id)->where('reference', $reference)->update($input)){
                    Session::flash('message', 'Referencia actualizada exitosamente');
                    return redirect('references');
                }
            }catch (\Exception $e){
                \Log::error("Error updating network: " . $e->getMessage());
                Session::flash('error_message','Error al intentar actualizar la referencia');
                return redirect('references');
            }
        }else{
            \Log::warning("Servicio Marca not found");
            Session::flash('error_message', 'Referencia no encontrada');
            return redirect('references');
        }

    }

    public function destroy($idparam_rules, $current_params_rule_id,$reference)
    {  
        if (!$this->user->hasAccess('references_rules.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        } 

        $message = '';
        $error = '';
        \Log::debug("Intentando eliminar parametro ".$idparam_rules." - regla de servicio id ".$current_params_rule_id." - referencia  ".$reference);

        if (ReferenceLimited::where('service_rule_id', $idparam_rules)->where('current_params_rule_id', $current_params_rule_id)->where('reference', $reference)->first()){
            try{
                if (ReferenceLimited::where('service_rule_id', $idparam_rules)->where('current_params_rule_id', $current_params_rule_id)->where('reference', $reference)->delete()){
                    $message =  'La referencia fue eliminada correctamente';
                    $error = false;
                }
            }catch (\Exception $e){
                \Log::error("Error deleting network: " . $e->getMessage());
                $message =  'Error al intentar eliminar la referencia';
                $error = true;
            }
        }else{
            $message =  'Referencia no encontrada';
            $error = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }

   
}