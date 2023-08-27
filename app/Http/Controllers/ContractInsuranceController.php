<?php

namespace App\Http\Controllers;

use Session;

use Carbon\Carbon;
use App\Models\Group;
use App\Http\Requests;
use App\Models\Contract;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ContractInsuranceController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

  
    public function index()
    {
      
    }

   
    public function create()
    {
       
    }

   
    public function store(Request $request)
    {
        $input = $request->all();
     
        \DB::beginTransaction();

       
        if($request->ajax()){
            $respuesta = [];
            try{
                if (\DB::table('contract_insurance')->insert(['contract_id' => $input['contract_id'] , 'insurance_policy_id' => $input['insurance_policy_id']])){
                    $data = [];
                    //$data['contract_id'] = $input['contract_id'];
                    //$data['insurance_policy_id'] = $input['insurance_policy_id'];

                   \Log::info("Póliza asociada al contrato correctamente");
                    $respuesta['mensaje'] = 'Póliza asociada al contrato correctamente.';
                    $respuesta['tipo'] = 'success';
                    //$respuesta['data'] = $data;
                    $respuesta['url'] = route('contracts.insurances.update',[$input['contract_id'],$input['insurance_policy_id']]);
                    \DB::commit();

                    return $respuesta;
                }
            }catch (\Exception $e){
                \DB::rollback();

                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Error al asociar la póliza con el contrato.';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }
        }else{
            if (\DB::table('contract_insurance')->insert(['contract_id' => $input['contract_id'] , 'insurance_policy_id' => $input['insurance_policy_id']])){
                $message = 'Póliza asociada al contrato correctamente.';
                Session::flash('message', $message);
                \Log::info("Póliza asociada al contrato correctamente.");

                return redirect('contracts.insurances.index');  
            }else{
                \DB::rollback();
                Session::flash('error_message', 'Ocurrio un error al intentar guardar el registro');
                return redirect()->back()->withInput();
                \Log::info('This is some useful information.');
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
        $contrato = Contract::find($id);

        if (!$contrato) {
            \Log::warning("contrato not found");
            return redirect()->back()->with('error', 'contrato no valido');
        }
        $contrato->number                       = $request->number;
        $contrato->busines_group_id             = $request->group_id;
        $contrato->credit_limit                 = $request->credit_limit;

        if($request->ajax()){
            $respuesta = [];
            try{
                $dataAnterior = Contract::find($id);

                if ($contrato->save()){
                    $data = [];
                    $data['id'] = $contrato->id;
              
                    \Log::info("Contrato actualizado correctamente.");
                    $respuesta['mensaje'] = 'Contrato Actualizado correctamente.';
                    $respuesta['tipo'] = 'success';
                    $respuesta['data'] = $data;
                    $respuesta['url'] = route('contracts.update',[$contrato->id]);
                    return $respuesta;
                }
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Error al actualizar el contrato.';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }
        }else{
            if (!$contrato->save()) {
                \Log::warning("Error updating the contrato data. Id: {$contrato->id}");
                Session::flash('message', 'Error al actualziar el registro');
                return redirect('contracts');
            }
            $message = 'Actualizado correctamente';
            Session::flash('message', $message);
            return redirect('contracts');
        }
    }

 
    public function destroy($id)
    {
        //
    }

}
