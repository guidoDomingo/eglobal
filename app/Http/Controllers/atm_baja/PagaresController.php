<?php

namespace App\Http\Controllers\atm_baja;

use Session;

use Carbon\Carbon;
use App\Models\Atm;
use App\Models\Group;
use App\Models\Atmnew;
use App\Models\Pagare;
use Illuminate\Http\Request;
use App\Models\InactivateHistory;
use App\Http\Controllers\Controller;

class PagaresController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function index( $group_id, Request $request)
    {
        if (!$this->user->hasAccess('atms.group.pagare')) {
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
        $pagares    =  Pagare::where('group_id', $group_id)->get();
        //dd($atm_ids);

        return view('atm_baja.pagares.index', compact('group_id','atm_list','grupo','pagares','atm_ids'));
    }

    public function create(Request $request)
    {
        if (!$this->user->hasAccess('atms.group.pagare.add')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $atm_ids    = $request->get('atm_list');
        $group_id   = $request->get('group_id');
        $grupo      = Group::find($group_id);
        $atm_list   = Atmnew::findMany($atm_ids);
        $pagares    =  Pagare::where(['group_id'=> $group_id])->get();
        $numero     = $pagares->count()+1;
        return view('atm_baja.pagares.create',compact('atm_ids','group_id','grupo','atm_list','numero'));
    }

    public function store(Request $request)
    {
        if (!$this->user->hasAccess('atms.group.pagare.add')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();
        
        $input = $request->all();

        $group_id       = $input['group_id'];
        if($input['tipo'] == 'unico'){
            $tipo = 1;
        }else{
            $tipo = 2;
        }
        $numero         = $input['numero'];
        $firmante       = $input['firmante'];
        $monto          = $input['monto'];
        $cantidad_pagos = $input['cantidad_pagos'];
        $tasa_interes   = $input['tasa_interes'];
        $vencimiento    = $input['vencimiento'];

        try{
            $pagare = Pagare::create([                    
                'group_id'      => $group_id,
                'numero'        => $numero,
                'firmante'      => $firmante,
                'tipo'          => $tipo,
                'vencimiento'   => Carbon::createFromFormat('d/m/Y', $vencimiento)->toDateString(),
                'monto'         => str_replace('.', '', $monto),
                'tasa_interes'  => $tasa_interes,
                'cantidad_pagos'=> $cantidad_pagos,
                'created_at'    => Carbon::now(),
                'created_by'    => $this->user->id,
                'updated_at'    => NULL,
                'updated_by'    => NULL,
                'deleted_at'    => NULL
            ]);      

            //Auditoria
            $history = new InactivateHistory();
            $history->group_id    = $pagare->group_id;
            $history->operation   = 'PAGARE - INSERT';
            $history->data        = json_encode($pagare);
            $history->created_at  = Carbon::now();
            $history->created_by  = $this->user->id;
            $history->updated_at  = NULL;
            $history->updated_by  = NULL;
            $history->deleted_at  = NULL;
            $history->deleted_by  = NULL;
            $history->save();

            \DB::commit();
            return redirect()->to('atm/new/'.$group_id.'/'.$group_id.'/pagare')->with('guardar','ok');
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
        if (!$this->user->hasAccess('atms.group.pagare.edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $input      = $request->all();
    
        if($pagare = Pagare::find($id))
        {
            $pagare->vencimiento = date("d/m/Y", strtotime($pagare->vencimiento));
            $grupo               = Group::find($pagare->group_id);

            $atms = \DB::table('business_groups as bg')
                ->select('ps.atm_id as atm_id')
                ->join('branches as b','b.group_id','=','bg.id')
                ->join('points_of_sale as ps','ps.branch_id','=','b.id')
                ->join('atms as a','a.id', '=','ps.atm_id')
                ->where('bg.id',$pagare->group_id)
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
                'pagare'    => $pagare,
                'atm_list'  => $atm_list,
                'grupo'     => $grupo
            ];
            return view('atm_baja.pagares.edit', $data);
        }else{
            Session::flash('error_message', 'Pagaré no encontrado.');
            return redirect()->back();
        }

    }

    public function update(Request $request, $id)
    {
        if (!$this->user->hasAccess('atms.group.pagare.edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();
        
        $input      = $request->all();

        if($input['tipo'] == 'unico'){
            $tipo = 1;
        }else{
            $tipo = 2;
        }
     
        if ($pagare = Pagare::find($id)){
            try{
              
                $pagare->fill([
                    'numero'        => $input['numero'],
                    'firmante'      => $input['firmante'],
                    'tipo'          => $tipo,
                    'vencimiento'   => Carbon::createFromFormat('d/m/Y', $input['vencimiento'])->toDateString(),
                    'monto'         => str_replace('.', '', $input['monto']),
                    'tasa_interes'  => $input['tasa_interes'],
                    'cantidad_pagos'=> $input['cantidad_pagos'],
                    'updated_at'    => Carbon::now(),
                    'updated_by'    => $this->user->id,
                ])->save();

                //Auditoria
                $history = new InactivateHistory();
                $history->group_id    = $pagare->group_id;
                $history->operation   = 'PAGARE - UPDATE';
                $history->data        = json_encode($pagare);
                $history->created_at  = NULL;
                $history->created_by  = NULL;
                $history->updated_at  = Carbon::now();
                $history->updated_by  = $this->user->id;
                $history->deleted_at  = NULL;
                $history->deleted_by  = NULL;
                $history->save();

                \DB::commit();
                return redirect()->to('atm/new/'.$pagare->group_id.'/'.$pagare->group_id.'/pagare')->with('actualizar','ok');
                
            }catch (\Exception $e){
                \DB::rollback();
                \Log::error("Error updating pagare: " . $e->getMessage());
                return redirect()->to('atm/new/'.$pagare->group_id.'/'.$pagare->group_id.'/pagare')->with('error','ok');
            }
        }else{
            \Log::warning("Pagare not found");
            return redirect()->to('atm/new/'.$pagare->group_id.'/'.$pagare->group_id.'/pagare')->with('error','ok');
        }

    }

    public function destroy($id)
    {
        if (!$this->user->hasAccess('atms.group.pagare.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();
        $message = '';
        $error = '';
        \Log::debug("Intentando elimiar pagare_id ".$id);
        if ($pagare = Pagare::find($id)){

            try{
               
                if (Pagare::where('id',$id)->delete()){
                    $message    =  'Pagaré eliminado correctamente';
                    $error      = false;
                }
            
                //Auditoria
                $history = new InactivateHistory();
                $history->group_id    = $pagare->group_id;
                $history->operation   = 'PAGARE - DELETE';
                $history->data        = json_encode($pagare);
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
                \Log::error("Error deleting pagare: " . $e->getMessage());
                $message    =  'Error al intentar eliminar el pagaré';
                $error      = true;
            }
        }else{
            $message    =  'Pagaré no encontrado';
            $error      = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }
   

}
