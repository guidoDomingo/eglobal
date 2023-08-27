<?php

namespace App\Http\Controllers;

use Session;

use Carbon\Carbon;
use App\Models\Group;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\PolicyType;

class PolicyTypeController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

  
    public function index(Request $request)
    {
        if (!$this->user->hasAccess('polizas_tipo')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            return redirect('/')->with('error', 'No posee permisos para realizar esta accion.');
        }
        //
    }

   
    public function create()
    {
        //
    }

   
    public function store(Request $request)
    {
        if (!$this->user->hasAccess('polizas_tipo.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            return redirect('/')->with('error', 'No posee permisos para realizar esta accion.');
        }
        $input = $request->all();
       
        if($request->ajax()){
            $respuesta = [];
            try{
                if ($insurance_type = PolicyType::create($input)) {
                    \Log::info("tipo de poliza creado");
                    $respuesta['mensaje'] = 'Nueva tipo de poliza creado';
                    $respuesta['tipo'] = 'success';
                    $respuesta['data'] = $insurance_type;
                    return $respuesta;
                }
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Error al crear tipo de poliza creado';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }

        }else{
            try{
                if ($insurance_type = PolicyType::create($input)) {
                    \Log::info("tipo de poliza creado");
                    Session::flash('message', 'tipo de poliza creado');
                    return redirect('contract.types.index');
                }
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                Session::flash('error_message', 'Error al crear tipo de poliza creado');
                return redirect()->back()->with('error', 'Error al crear tipo de poliza creado');
            }
        }

    }

  
    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }


    public function update(Request $request, $id)
    {
        //
    }

 
    public function destroy($id)
    {
        //
    }

}
