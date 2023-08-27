<?php

namespace App\Http\Controllers;

use Session;

use Carbon\Carbon;
use App\Models\Group;
use App\Http\Requests;
use App\Models\InsurancePolicy;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Requests\AlquilerRequest;

class InsurancePolicyController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

  
    public function index(Request $request)
    {
        if (!$this->user->hasAccess('insurances_form')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            return redirect('/')->with('error', 'No posee permisos para realizar esta accion.');
        }
        
        $polizas =\DB::table('insurance_policy')
        ->select('insurance_policy.id','insurance_policy.insurance_code','insurance_policy.number','insurance_type.description as tipo','insurance_policy.capital',
        'insurance_policy.capital_operativo','insurance_policy.status','insurance_policy.created_at','business_groups.description as grupo','business_groups.ruc as grupo_ruc')
        ->join('contract_insurance','contract_insurance.insurance_policy_id','=','insurance_policy.id')
        ->join('contract','contract.id','=','contract_insurance.contract_id')
        ->join('business_groups','business_groups.id','=','contract.busines_group_id')
        ->join('insurance_type','insurance_type.id','=','insurance_policy.insurance_policy_type_id')
        ->orderby('insurance_policy.created_at','desc')
        ->get();

        return view('polizas.index', compact('polizas'));
    }

   
    public function create()
    {
        //
    }

   
    public function store(Request $request)
    {
        if (!$this->user->hasAccess('polizas.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            return redirect('/')->with('error', 'No posee permisos para realizar esta accion.');
        }
        $input = $request->all();
  
        $poliza = new InsurancePolicy();
        $poliza->insurance_code                     = $input['insurance_code'];
        $poliza->insurance_policy_type_id           = $input['insurance_policy_type_id'];
        $poliza->number                             = $input['number'];
        $poliza->created_at                         = Carbon::now();
        $poliza->capital                            = str_replace('.', '', $request->capital);
        $poliza->capital_operativo                  = str_replace('.', '', $request->capital);
        $poliza->status                             = $input['status'];
        $poliza->observaciones                      = $input['observaciones'];
        $poliza->created_by                         = $this->user->id;

        if($request->ajax()){
            $respuesta = [];
            try{
                if ($poliza->save()){
                    $data = [];
                    $data['id'] = $poliza->id;
                    \Log::info("Nueva Poliza creada");
                   
                    $audit = \DB::table('insurance_policy_history')->insert([
                        'insurance_id'      => $poliza->id,
                        'insurance_code'    => $poliza->insurance_code,
                        'insurance_type'    => $poliza->insurance_policy_type_id,
                        'number'            => $poliza->number,
                        'status'            => $poliza->status,
                        'capital'           => str_replace('.', '', $poliza->capital),
                        'capital_operativo' => str_replace('.', '', $poliza->capital_operativo),
                        'created_at'        => Carbon::now(),
                        'created_by'        => $this->user->id,
                        'updated_at'        => NULL,
                        'updated_by'        => NULL,
                        'deleted_at'        => NULL,
                        'deleted_by'        => NULL,
                    ]);
                    
                    //PROGRESO DE CREACION - ABM V2
                    if ( $request->abm == 'v2'){
                        \DB::table('atms')
                        ->where('id', $input['atm_id'])
                        ->update([
                            'atm_status' => -8,
                        ]);
                        \Log::info("ABM Version 2, Paso 4 - POLIZAS. Estado de creacion: -8");
                    }

                    ///Asociar poliza con contrato
                   if(isset($request->contrato_id)){
                       try{
                            \DB::table('contract_insurance')->insert(['contract_id' => $request->contrato_id , 'insurance_policy_id' => $poliza->id]);
                            \Log::info('Poliza id: '.$poliza->id.' asociada al contrato id: '.$request->contrato_id);
                       }catch (\Exception $e){
                           \Log::error("Error al asociar la poliza con el ocntrato - {$e->getMessage()}");
                       };
                   }

                    $respuesta['mensaje'] = 'Póliza agregada correctamente.';
                    $respuesta['tipo'] = 'success';
                    $respuesta['data'] = $data;
                    $respuesta['url'] = route('insurances.update',[$poliza->id]);

                    return $respuesta;
                }
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Error al crear la póliza.';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }
        }else{
            if($poliza->save()){
                $message = 'Agregado correctamente';
                Session::flash('message', $message);
                \Log::info("Nuevo póliza creado");

                return redirect('insurances.index');  
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
        if (!$this->user->hasAccess('insurances_form.edit') ) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            return redirect('/')->with('error', 'No posee permisos para realizar esta accion.');
        }
        $poliza             = InsurancePolicy::where('id',$id)->first();
        $insurance_types    = \DB::table('insurance_type')->pluck('description','id');

        $data = [
            'poliza'           => $poliza,
            'insurance_types'  => $insurance_types,
        ];

        return view('polizas.edit', $data);
    }

    public function update(Request $request, $id)
    {
        if (!$this->user->hasAccess('polizas.add|edit') || !$this->user->hasAccess('insurances_form.edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            return redirect('/')->with('error', 'No posee permisos para realizar esta accion.');
        }
        \DB::beginTransaction();
        
        $poliza = InsurancePolicy::find($id);
       
        if (!$poliza) {
            \Log::warning("poliza not found");
            return redirect()->back()->with('error', 'Poliza no valida');
        }
        $poliza->insurance_code                         = $request->insurance_code;
        $poliza->insurance_policy_type_id               = $request->insurance_policy_type_id;
        $poliza->number                                 = $request->number;
        //$poliza->created_at                             = date("Y-m-d h:i:s", strtotime($request->date_init));
        $poliza->capital                                = str_replace('.', '', $request->capital);
        $poliza->status                                 = $request->status;
        $poliza->observaciones                          = $request->observaciones;
        //$poliza->created_by                             = $request->observation;

        if($request->ajax()){
            $respuesta = [];
            try{
                $dataAnterior = InsurancePolicy::find($id);

                if ($poliza->save()){
                    $data = [];
                    $data['id'] = $poliza->id;
              
                    $audit = \DB::table('insurance_policy_history')->insert([
                        'insurance_id'      => $poliza->id,
                        'insurance_code'    => $request->insurance_code,
                        'insurance_type'    => $request->insurance_policy_type_id,
                        'number'            => $request->number,
                        'status'            => $request->status,
                        'capital'           => str_replace('.', '', $request->capital),
                        'capital_operativo' => str_replace('.', '', $request->capital),
                        'created_at'        => NULL,
                        'created_by'        => NULL,
                        'updated_at'        => Carbon::now(),
                        'updated_by'        => $this->user->id,
                        'deleted_at'        => NULL,
                        'deleted_by'        => NULL,
                    ]);

                    \Log::info("Póliza actualizada correctamente.");
                    \DB::commit();
                    $respuesta['mensaje'] = 'Póliza Actualizada correctamente.';
                    $respuesta['tipo'] = 'success';
                    $respuesta['data'] = $data;
                    $respuesta['url'] = route('insurances.update',[$poliza->id]);
                    return $respuesta;
                }
            }catch (\Exception $e){
                \DB::rollback();
                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Error al actualizar el Póliza.';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }
        }else{

            //$poliza->capital                                = str_replace('.', '', $request->capital);
            //$poliza->insurance_policy_type_id               = $request->insurance_policy_type_id;
            $poliza->capital_operativo                      = str_replace('.', '', $request->capital_operativo);
            $poliza->status                                 = $request->status;
            $poliza->observaciones                          = $request->observaciones;

            if (!$poliza->save()) {
                \DB::rollback();
                \Log::warning("Error updating the poliza data. Id: {$poliza->id}");
                Session::flash('message', 'Error al actualziar el registro');
                return redirect('insurances');
            }

            // ACTUAL BALANCE_RULES

            $group_id_aux =\DB::table('insurance_policy')
            ->join('contract_insurance','contract_insurance.insurance_policy_id','=','insurance_policy.id')
            ->join('contract','contract.id','=','contract_insurance.contract_id')
            ->select('contract.busines_group_id')
            ->where('insurance_policy.id',$id)
            ->get();

            $group_id = -1;
            if(count ($group_id_aux)> 0){
                $group_id = $group_id_aux[0]->busines_group_id;
            }

            \Log::info('Actualizando saldo  minimo en balance rule del grupo_id: ',[$group_id]);

            \DB::table('balance_rules')
            ->where('group_id', $group_id)
            ->where('tipo_control', 4)
            ->update(['saldo_minimo' => $poliza->capital_operativo]);
            \Log::info('Actualizacion exitosa');

            $audit = \DB::table('insurance_policy_history')->insert([
                'insurance_id'      => $id,
                'insurance_code'    => $request->insurance_code,
                'insurance_type'    => $request->insurance_policy_type_id,
                'number'            => $request->number,
                'status'            => $request->status,
                'capital'           => str_replace('.', '', $request->capital),
                'capital_operativo' => str_replace('.', '', $request->capital_operativo),
                'created_at'        => NULL,
                'created_by'        => NULL,
                'updated_at'        => Carbon::now(),
                'updated_by'        => $this->user->id,
                'deleted_at'        => NULL,
                'deleted_by'        => NULL,
            ]);
         

            \DB::commit();
            $message = 'Póliza actualizada correctamente';
            Session::flash('message', $message);
            return redirect('insurances');
        }

    }

 
    public function destroy($id)
    {
        //
    }
}
