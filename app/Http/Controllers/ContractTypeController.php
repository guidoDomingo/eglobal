<?php

namespace App\Http\Controllers;

use Session;

use Carbon\Carbon;
use App\Models\Group;
use App\Http\Requests;
use App\Models\Contract;

use App\Models\TypeContract;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\AlquilerRequest;
use App\Models\ContractType;

class ContractTypeController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

  
    public function index(Request $request)
    {
        if (!$this->user->hasAccess('contratos_tipos')) {
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
        if (!$this->user->hasAccess('contratos_tipos.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            return redirect('/')->with('error', 'No posee permisos para realizar esta accion.');
        }

        $input = $request->all();

        if($request->ajax()){
            $respuesta = [];
            try{
                if ($contract_type = ContractType::create($input)) {
                    \Log::info("tipo de contrato creado");
                    $respuesta['mensaje'] = 'Nueva tipo de contrato creado';
                    $respuesta['tipo'] = 'success';
                    $respuesta['data'] = $contract_type;
                    return $respuesta;
                }
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Error al crear tipo de contrato creado';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }

        }else{
            try{
                if ($contract_type = ContractType::create($input)) {
                    \Log::info("tipo de contrato creado");
                    Session::flash('message', 'tipo de contrato creado');
                    return redirect('contract.types.index');
                }
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                Session::flash('error_message', 'Error al crear tipo de contrato creado');
                return redirect()->back()->with('error', 'Error al crear tipo de contrato creado');
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
