<?php

namespace App\Http\Controllers\atm_baja;

use Session;

use Carbon\Carbon;
use App\Models\Atm;
use App\Models\Group;
use App\Models\Atmnew;
use App\Models\Pagare;
use Illuminate\Http\Request;
use App\Models\RemisionPagare;
use App\Models\InactivateHistory;
use App\Http\Controllers\Controller;

class RemisionPagareController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function index( $group_id, Request $request)
    {
        if (!$this->user->hasAccess('atms.group.remision.pagare')) {
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

        $remisiones =  RemisionPagare::where('group_id', $group_id)->get();

        return view('atm_baja.remisiones.index', compact('group_id','atm_list','grupo','atm_ids','remisiones'));
    }

    public function create(Request $request)
    {
        if (!$this->user->hasAccess('atms.group.remision.pagare.add')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $atm_ids    = $request->get('atm_list');
        $group_id   = $request->get('group_id');
        $grupo      = Group::find($group_id);
        $atm_list   = Atmnew::findMany($atm_ids);
        $remisiones =  RemisionPagare::where('group_id', $group_id)->get();
        $numero     = $remisiones->count()+1;

        return view('atm_baja.remisiones.create',compact('atm_ids','group_id','grupo','atm_list','numero'));
    }

    public function store(Request $request)
    {
        if (!$this->user->hasAccess('atms.group.remision.pagare.add')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();
        
        $input = $request->all();
        $group_id       = $input['group_id'];
     
        $numero         = $input['numero'];
        $titular_deudor = $input['titular_deudor'];
        $fecha          = $input['fecha'];
        $importe        = $input['importe'];
        $importe_deuda  = $input['importe_deuda'];
        $importe_imputado = $input['importe_imputado'];
        $nro_contrato   = $input['nro_contrato'];
        $recepcionado   = $input['recepcionado'];


        try{
            $remision = RemisionPagare::create([  
                'group_id'      => $group_id,                  
                'numero'        => $numero,
                'titular_deudor'=> $titular_deudor,
                'fecha'         => Carbon::createFromFormat('d/m/Y', $fecha)->toDateString(),
                'importe'       => str_replace('.', '', $importe),
                'importe_deuda' => str_replace('.', '', $importe_deuda),
                'importe_imputado'=> str_replace('.', '', $importe_imputado),
                'nro_contrato'  => $nro_contrato,
                'recepcionado'  => $recepcionado,
                'created_at'    => Carbon::now(),
                'created_by'    => $this->user->id,
                'updated_at'    => NULL,
                'updated_by'    => NULL,
                'deleted_at'    => NULL,
            ]);      

            //Auditoria
            $history = new InactivateHistory();
            $history->group_id    = $remision->group_id;
            $history->operation   = 'REMISION PAGARE - INSERT';
            $history->data        = json_encode($remision);
            $history->created_at  = Carbon::now();
            $history->created_by  = $this->user->id;
            $history->updated_at  = NULL;
            $history->updated_by  = NULL;
            $history->deleted_at  = NULL;
            $history->deleted_by  = NULL;
            $history->save();

             //Auditoria
            //  $remision =\DB::table('atm_inactivate_history')
            //  ->insert([
            //      'atm_id'        => $atm_id,
            //      'group_id'      => $group_id,
            //      'operation'     => 'REMISION PAGARE - INSERT',
            //      'data'          => json_encode($request->except('_token')),
            //      'created_at'    => Carbon::now(),
            //      'created_by'    => $this->user->id,
            //      'updated_at'    => NULL,
            //      'updated_by'    => NULL,
            //      'deleted_at'    => NULL,
            //      'deleted_by'    => NULL
            //  ]);

            \DB::commit();
            return redirect()->to('atm/new/'.$group_id.'/'.$group_id.'/remision')->with('guardar','ok');
        }catch (\Exception $e){
            \DB::rollback();
            \Log::critical($e->getMessage());
            return redirect()->back()->with('error','ok');
        }
    }

    public function show($id)
    {
        //
    }
   
    public function edit($id, Request $request)
    {
        if (!$this->user->hasAccess('atms.group.remision.pagare.edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $input      = $request->all();

        if($remision = RemisionPagare::find($id))
        {
            $remision->fecha = date("d/m/Y", strtotime($remision->fecha));
            $grupo           = Group::find($remision->group_id);

            $atms = \DB::table('business_groups as bg')
            ->select('ps.atm_id as atm_id')
            ->join('branches as b','b.group_id','=','bg.id')
            ->join('points_of_sale as ps','ps.branch_id','=','b.id')
            ->join('atms as a','a.id', '=','ps.atm_id')
            ->where('bg.id',$remision->group_id)
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
                'remision'  => $remision,
                'atm_list'  => $atm_list,
                'grupo'     => $grupo
            ];
            return view('atm_baja.remisiones.edit', $data);
        }else{
            Session::flash('error_message', 'Remisión de Pagaré no encontrado.');
            return redirect()->back();
        }

    }

    public function update(Request $request, $id)
    {
        if (!$this->user->hasAccess('atms.group.remision.pagare.edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();
        $input      = $request->all();       
        $numero         = $input['numero'];
        $titular_deudor = $input['titular_deudor'];
        $fecha          = $input['fecha'];
        $importe        = $input['importe'];
        $importe_deuda  = $input['importe_deuda'];
        $importe_imputado = $input['importe_imputado'];
        $nro_contrato   = $input['nro_contrato'];
        $recepcionado   = $input['recepcionado'];

        if ($remision = RemisionPagare::find($id)){
            try{
                
                $remision->fill([
                    'numero'        => $numero,
                    'titular_deudor'=> $titular_deudor,
                    'fecha'         => Carbon::createFromFormat('d/m/Y', $fecha)->toDateString(),
                    'importe'       => str_replace('.', '', $importe),
                    'importe_deuda' => str_replace('.', '', $importe_deuda),
                    'importe_imputado'=> str_replace('.', '', $importe_imputado),
                    'nro_contrato'  => $nro_contrato,
                    'recepcionado'  => $recepcionado,
                    'updated_at'    => Carbon::now(),
                    'updated_by'    => $this->user->id
                ])->save();

                // $remision_update=\DB::table('grupos_pagares_remision')
                // ->where('id',$id)
                // ->update([
                //     'numero'        => $numero,
                //     'titular_deudor'=> $titular_deudor,
                //     'fecha'         => Carbon::createFromFormat('d/m/Y', $fecha)->toDateString(),
                //     'importe'       => str_replace('.', '', $importe),
                //     'importe_deuda' => str_replace('.', '', $importe_deuda),
                //     'importe_imputado'=> str_replace('.', '', $importe_imputado),
                //     'nro_contrato'  => $nro_contrato,
                //     'recepcionado'  => $recepcionado,
                //     'updated_at'    => Carbon::now(),
                //     'updated_by'    => $this->user->id
                // ]);

                //Auditoria
                $history = new InactivateHistory();
                $history->group_id    = $remision->group_id;
                $history->operation   = 'REMISION PAGARE - UPDATE';
                $history->data        = json_encode($remision);
                $history->created_at  = NULL;
                $history->created_by  = NULL;
                $history->updated_at  = Carbon::now();
                $history->updated_by  = $this->user->id;
                $history->deleted_at  = NULL;
                $history->deleted_by  = NULL;
                $history->save();

                \DB::commit();
                return redirect()->to('atm/new/'.$remision->group_id.'/'.$remision->group_id.'/remision')->with('actualizar','ok');
                
            }catch (\Exception $e){
                \DB::rollback();
                \Log::error("Error updating Remisión de pagare: " . $e->getMessage());
                return redirect()->to('atm/new/'.$remision->group_id.'/'.$remision->group_id.'/remision')->with('error','ok');
            }
        }else{
            \Log::warning("Remisión de Pagare not found");
            Session::flash('error_message', 'Remisión de Pagaré no encontrado');
            return redirect()->to('atm/new/'.$remision->group_id.'/'.$remision->group_id.'/remision')->with('error','ok');
        }

    }

    public function destroy($id)
    {
        if (!$this->user->hasAccess('atms.group.remision.pagare.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();
        $message = '';
        $error = '';
        \Log::debug("Intentando elimiar pagare_id ".$id);
        if ($remision = RemisionPagare::find($id)){

          
            try{
               
                if (RemisionPagare::where('id',$id)->delete()){
                    $message    =  'Remisión de Pagaré eliminado correctamente';
                    $error      = false;
                }
                \Log::debug("Remisión Pagare eliminado, pagare_id ".$id);


                 //Auditoria
                 $history = new InactivateHistory();
                 $history->group_id    = $remision->group_id;
                 $history->operation   = 'REMISION PAGARE - DELETE';
                 $history->data        = json_encode($remision);
                 $history->created_at  = NULL;
                 $history->created_by  = NULL;
                 $history->updated_at  = NULL;
                 $history->updated_by  = NULL;
                 $history->deleted_at  = Carbon::now();
                 $history->deleted_by  = $this->user->id;
                 $history->save();

                \DB::commit();
            }catch (\Exception $e){
                \DB::rollback();
                \Log::error("Error deleting Remisión: " . $e->getMessage());
                $message    =  'Error al intentar eliminar la Remisión de pagaré';
                $error      = true;
            }
        }else{
            $message    =  'Remisión de Pagaré no encontrado';
            $error      = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }
   

}
