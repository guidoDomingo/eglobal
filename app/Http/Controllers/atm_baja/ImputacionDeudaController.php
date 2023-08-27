<?php

namespace App\Http\Controllers\atm_baja;

use Session;

use Carbon\Carbon;
use App\Models\Atm;
use App\Models\Group;
use App\Models\Atmnew;
use App\Models\Pagare;
use App\Models\Presupuesto;
use Illuminate\Http\Request;
use App\Models\ImputacionDeuda;
use App\Models\InactivateHistory;
use App\Http\Controllers\Controller;

class ImputacionDeudaController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function index( $group_id, Request $request)
    {
        if (!$this->user->hasAccess('atms.group.imputacion')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $grupo = Group::find($group_id);

        $atms = \DB::table('business_groups as bg')
            ->select('ps.atm_id as atm_id')
            ->join('branches as b','b.group_id','=','bg.id')
            ->join('points_of_sale as ps','ps.branch_id','=','b.id')
            ->join('atms as a','a.id', '=','ps.atm_id')
            ->where('bg.id',$group_id)
            ->whereNull('a.deleted_at')
            ->whereNull('bg.deleted_at')
            ->get();

        $atm_ids = array();
        foreach($atms as $item){
            $id = $item->atm_id;
            array_push($atm_ids, $id);
        }
        $atm_list   =  Atmnew::findMany($atm_ids);

        $imputaciones =  ImputacionDeuda::where('group_id', $group_id)->get();

        return view('atm_baja.imputaciones.index', compact('group_id','atm_list','grupo','atm_ids','imputaciones'));
    }

    public function create(Request $request)
    {
        if (!$this->user->hasAccess('atms.group.imputacion.add')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $atm_ids    = $request->get('atm_list');
        $group_id   = $request->get('group_id');
        $grupo      = Group::find($group_id);
        $atm_list   = Atmnew::findMany($atm_ids);
        $imputaciones =  ImputacionDeuda::where('group_id', $group_id)->get();
        $numero     = $imputaciones->count()+1;

        return view('atm_baja.imputaciones.create',compact('atm_ids','group_id','grupo','atm_list','numero'));
    }

    public function store(Request $request)
    {
        if (!$this->user->hasAccess('atms.group.imputacion.add')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();
        
        $input = $request->all();
        $group_id       = $input['group_id'];
       
        $numero         = $input['numero'];
        $numero_contrato= $input['numero_contrato'];
        $fecha_siniestro= $input['fecha_siniestro'];
        $fecha_cobro    = $input['fecha_cobro'];
        $monto          = $input['monto'];
        if($input['estado'] == 'pendiente'){
            $estado = 1;
        }else{
            $estado = 0;
        }
        $procentaje_franquicia= $input['procentaje_franquicia'];

        try{
            $imputacion = ImputacionDeuda::create([                    
                'group_id'      => $group_id,
                'numero'            => $numero,
                'numero_contrato'   => $numero_contrato,
                'fecha_siniestro'   => Carbon::createFromFormat('d/m/Y', $fecha_siniestro)->toDateString(),
                'fecha_cobro'       => Carbon::createFromFormat('d/m/Y', $fecha_cobro)->toDateString(),
                'monto'             => str_replace('.', '', $monto),
                'estado'            => $estado,
                'procentaje_franquicia'=> $procentaje_franquicia,
                'created_at'    => Carbon::now(),
                'created_by'    => $this->user->id,
                'updated_at'    => NULL,
                'updated_by'    => NULL,
                'deleted_at'    => NULL,
            ]);      

            //Auditoria
            $history = new InactivateHistory();
            $history->group_id    = $imputacion->group_id;
            $history->operation   = 'IMPUTACION DEUDA - INSERT';
            $history->data        = json_encode($imputacion);
            $history->created_at  = Carbon::now();
            $history->created_by  = $this->user->id;
            $history->updated_at  = NULL;
            $history->updated_by  = NULL;
            $history->deleted_at  = NULL;
            $history->deleted_by  = NULL;
            $history->save();
            //  //Auditoria
            //  $imputacion =\DB::table('atm_inactivate_history')
            //  ->insert([
            //      'atm_id'        => $atm_id,
            //      'group_id'      => $group_id,
            //      'operation'     => 'IMPUTACION DEUDA - INSERT',
            //      'data'          => json_encode($request->except('_token')),
            //      'created_at'    => Carbon::now(),
            //      'created_by'    => $this->user->id,
            //      'updated_at'    => NULL,
            //      'updated_by'    => NULL,
            //      'deleted_at'    => NULL,
            //      'deleted_by'    => NULL
            //  ]);

            \DB::commit();
            return redirect()->to('atm/new/'.$group_id.'/'.$group_id.'/imputacion')->with('guardar','ok');
        }catch (\Exception $e){
            \DB::rollback();
            \Log::critical($e->getMessage());
            return redirect()->back()->with('error', 'ok');
        }
    }

    public function show($id)
    {
        //
    }
   
    public function edit($id, Request $request)
    {
        if (!$this->user->hasAccess('atms.group.imputacion.edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $input      = $request->all();
    
        if($imputacion = ImputacionDeuda::find($id))
        {
            $imputacion->fecha_siniestro    = date("d/m/Y", strtotime($imputacion->fecha_siniestro));
            $imputacion->fecha_cobro        = date("d/m/Y", strtotime($imputacion->fecha_cobro));
            $grupo                          = Group::find($imputacion->group_id);

            $atms = \DB::table('business_groups as bg')
            ->select('ps.atm_id as atm_id')
            ->join('branches as b','b.group_id','=','bg.id')
            ->join('points_of_sale as ps','ps.branch_id','=','b.id')
            ->join('atms as a','a.id', '=','ps.atm_id')
            ->where('bg.id',$imputacion->group_id)
            ->whereNull('a.deleted_at')
            ->whereNull('bg.deleted_at')
            ->get();

            $atm_ids = array();
            foreach($atms as $item){
                $id = $item->atm_id;
                array_push($atm_ids, $id);
            }
            $atm_list   =  Atmnew::findMany($atm_ids);

            $data = [
                'imputacion' => $imputacion,
                'atm_list'   => $atm_list,
                'grupo'      => $grupo
            ];
            return view('atm_baja.imputaciones.edit', $data);
        }else{
            Session::flash('error_message', 'Imputacion de deuda no encontrada.');
            return redirect()->back();
        }

    }

    public function update(Request $request, $id)
    {
        if (!$this->user->hasAccess('atms.group.imputacion.edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();
        $input          = $request->all();     
        $numero         = $input['numero'];
        $numero_contrato= $input['numero_contrato'];
        $fecha_siniestro= $input['fecha_siniestro'];
        $fecha_cobro    = $input['fecha_cobro'];
        $monto          = $input['monto'];
        if($input['estado'] == 'pendiente'){
            $estado = 1;
        }else{
            $estado = 0;
        }        
        $procentaje_franquicia= $input['procentaje_franquicia'];

        if ($imputacion = ImputacionDeuda::find($id)){
            try{

                $imputacion->fill([
                    'numero'            => $numero,
                    'numero_contrato'   => $numero_contrato,
                    'fecha_siniestro'   => Carbon::createFromFormat('d/m/Y', $fecha_siniestro)->toDateString(),
                    'fecha_cobro'       => Carbon::createFromFormat('d/m/Y', $fecha_cobro)->toDateString(),
                    'monto'             => str_replace('.', '', $monto),
                    'estado'            => $estado,
                    'procentaje_franquicia'=> $procentaje_franquicia,    
                    'updated_at'    => Carbon::now(),
                    'updated_by'    => $this->user->id
                ])->save();

            //     $imputacion_update=\DB::table('grupos_imputacion_deuda')
            //     ->where('id',$id)
            //     ->update([
            //         'numero'            => $numero,
            //         'numero_contrato'   => $numero_contrato,
            //         'fecha_siniestro'   => Carbon::createFromFormat('d/m/Y', $fecha_siniestro)->toDateString(),
            //         'fecha_cobro'       => Carbon::createFromFormat('d/m/Y', $fecha_cobro)->toDateString(),
            //         'monto'             => str_replace('.', '', $monto),
            //         'estado'            => $estado,
            //         'procentaje_franquicia'=> $procentaje_franquicia,    
            //         'updated_at'    => Carbon::now(),
            //         'updated_by'    => $this->user->id
            //     ]);
            //   \Log::info('Imputacion de deuda id= '.$id.'actualizado correctamente');


            //Auditoria
            $history = new InactivateHistory();
            $history->group_id    = $imputacion->group_id;
            $history->operation   = 'IMPUTACION DEUDA - UPDATE';
            $history->data        = json_encode($imputacion);
            $history->created_at  = NULL;
            $history->created_by  = NULL;
            $history->updated_at  = Carbon::now();
            $history->updated_by  = $this->user->id;
            $history->deleted_at  = NULL;
            $history->deleted_by  = NULL;
            $history->save();


                // //Auditoria
                // $imputacion_history =\DB::table('atm_inactivate_history')
                // ->insert([
                //     'atm_id'        => $atm_id,
                //     'group_id'      => $imputacion->group_id,
                //     'operation'     => 'IMPUTACION DEUDA - UPDATE',
                //     'data'          => json_encode($request->except('_token','_method')),
                //     'created_at'    => NULL,
                //     'created_by'    => NULL,
                //     'updated_at'    => Carbon::now(),
                //     'updated_by'    => $this->user->id,
                //     'deleted_at'    => NULL,
                //     'deleted_by'    => NULL
                // ]);
                // \Log::info("Imputacion de deuda registrado en auditoria");

                \DB::commit();
                return redirect()->to('atm/new/'.$imputacion->group_id.'/'.$imputacion->group_id.'/imputacion')->with('actualizar','ok');
                
            }catch (\Exception $e){
                \DB::rollback();
                \Log::error("Error updating Imputacion de deuda: " . $e->getMessage());
                return redirect()->to('atm/new/'.$imputacion->group_id.'/'.$imputacion->group_id.'/imputacion')->with('error','ok');
            }
        }else{
            \Log::warning("Imputacion de deuda not found");
            return redirect()->to('atm/new/'.$imputacion->group_id.'/'.$imputacion->group_id.'/imputacion')->with('error','ok');
        }

    }

    public function destroy($id)
    {
        if (!$this->user->hasAccess('atms.group.imputacion.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();
        $message = '';
        $error = '';
        // \Log::debug("Intentando elimiar Imputacion de deuda id: ".$id);
        if ($imputacion = ImputacionDeuda::find($id)){

            try{
               
                if (ImputacionDeuda::where('id',$id)->delete()){
                    $message    =  'Imputacion de deuda eliminado correctamente';
                    $error      = false;
                }
                // \Log::debug("Imputacion de deuda eliminado, imputacion_id ".$id);
                //Auditoria
                $history = new InactivateHistory();
                $history->group_id    = $imputacion->group_id;
                $history->operation   = 'IMPUTACION DEUDA - DELETE';
                $history->data        = json_encode($imputacion);
                $history->created_at  = NULL;
                $history->created_by  = NULL;
                $history->updated_at  = NULL;
                $history->updated_by  = NULL;
                $history->deleted_at  = Carbon::now();
                $history->deleted_by  = $this->user->id;
                $history->save();

                // //Auditoria
                // $imputacion_history =\DB::table('atm_inactivate_history')
                // ->insert([
                //     'atm_id'        => $atm[0]->atm_id,
                //     'group_id'      => $imputacion->group_id,
                //     'operation'     => 'IMPUTACION DEUDA - DELETE',
                //     'data'          => json_encode(['id'=> $imputacion->id, 'numero' => $imputacion->numero,'numero_contrato' => $imputacion->numero_contrato,  'monto' => $imputacion->monto, 'fecha_siniestro' => $imputacion->fecha_siniestro,'fecha_cobro' => $imputacion->fecha_cobro,'estado' => $imputacion->estado, 'procentaje_franquicia' => $imputacion->procentaje_franquicia]),
                //     'created_at'    => NULL,
                //     'created_by'    => NULL,
                //     'updated_at'    => NULL,
                //     'updated_by'    => NULL,
                //     'deleted_at'    => Carbon::now(),
                //     'deleted_by'    => $this->user->id
                // ]);
                // \Log::info("Imputacion de deuda registrado en auditoria");

                \DB::commit();
            }catch (\Exception $e){
                \DB::rollback();
                \Log::error("Error deleting Imputacion de deuda: " . $e->getMessage());
                $message    =  'Error al intentar eliminar la Imputacion de deuda';
                $error      = true;
            }
        }else{
            $message    =  'Imputacion de deuda no encontrada';
            $error      = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }
   

}
