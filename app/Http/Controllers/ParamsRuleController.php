<?php

namespace App\Http\Controllers;

use DB;

use Excel;
use Session;
use Carbon\Carbon;
use App\Http\Requests;
use App\Models\ParamsRule;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\ParamsRuleRequest;


class ParamsRuleController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function index(Request $request)
    {
        if (!$this->user->hasAccess('params_rules')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $name = $request->get('name');
        $parametros = ParamsRule::filterAndPaginate($name);
        return view('params_rule.index', compact('parametros','name'));
    }
    
    public function create()
    {
        if (!$this->user->hasAccess('params_rules.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        return view('params_rule.create');
    }



    public function store(ParamsRuleRequest $request)
    { 
        if (!$this->user->hasAccess('params_rules.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = $request->except('_token');
        $description = $input['description'];
        $type = $input['type'];
        if($request->ajax()){
            $respuesta = [];
            try{
                if ($parametro = ParamsRule::create($input)){
                    \Log::info("New params rule created !");
                    $respuesta['mensaje'] = 'Nueva parámetro creado correctamente';
                    $respuesta['tipo'] = 'success';
                    $respuesta['data'] = $parametro;
                    return $respuesta;
                }
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Error al crear el parámetro';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }
        }else{
            try{
                if ($parametro = ParamsRule::create($input)){
                    \Log::info("Parámetro ingresado correctamente!");
                    Session::flash('message', 'Nuevo Parámetro creado correctamente');
                    return redirect('params_rules');
                 }
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                Session::flash('error_message', 'Error al crear el parámetro');
                return redirect()->back()->with('error', 'Error al crear el parámetro');
            }
        } 
    }
    public function show($idparam_rules)
    {  
    }

    public function edit($idparam_rules)
    {   
        if (!$this->user->hasAccess('params_rules.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        if($parametro = ParamsRule::find($idparam_rules)){
            $data = ['parametro' => $parametro];
            return view('params_rule.edit', $data);
        }else{
            Session::flash('error_message', 'Parámetro no encontrado');
            return redirect('params_rules');
        }
    }

    public function update(ParamsRuleRequest $request, $idparam_rules)
    {   
        if (!$this->user->hasAccess('params_rules.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if ($parametro = ParamsRule::find($idparam_rules)){
            $input = $request->all();
            try{
                $parametro->fill($input);
                if($parametro->update()){
                    \Log::info("Parámetro actualizado exitosamente");
                    Session::flash('message', 'Parámetro actualizado exitosamente');
                    return redirect('params_rules');
                }
            }catch (\Exception $e){
                \Log::error("Error updating network: " . $e->getMessage());
                Session::flash('error_message','Error al intentar actualizar el Parámetro');
                return redirect('params_rules');
            }
        }else{
            \Log::warning("Parámetro not found");
            Session::flash('error_message', 'Parámetro no encontrado');
            return redirect('params_rules');
        }
    }

    public function destroy($idparam_rules)
    {  
        if (!$this->user->hasAccess('params_rules.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        } 
        $message = '';
        $error = '';
        \Log::debug("Attempting to delete a given Params rules");
        if (ParamsRule::find($idparam_rules)) {
            try {
                if (ParamsRule::destroy($idparam_rules)) {
                    $message =  'Parámetro eliminado correctamente';
                    $error = false;
                }
            } catch (\Exception $e) {
                \Log::error("Error deleting params rules: " . $e->getMessage());
                $message =  'Error al intentar eliminar el Parámetro';
                $error = true;
            }
        } else {
            $message =  'Parámetro no encontrado';
            $error = true;
        }
        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }

   
}