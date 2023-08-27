<?php

namespace App\Http\Controllers\atm_baja;

use Session;

use Carbon\Carbon;
use App\Models\Atm;
use App\Models\Group;
use App\Models\Atmnew;
use App\Models\Pagare;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CobranzaController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function index(Request $request)
    {
        // if (!$this->user->hasAccess('atms.group.cobranzas')) {
        //     \Log::error('Unauthorized access attempt',
        //         ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
        //     Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
        //     return redirect('/');
        // }
        // $grupo = Group::find($group_id);

        // $atms = \DB::table('business_groups as bg')
        //     ->select('ps.atm_id as atm_id')
        //     ->join('branches as b','b.group_id','=','bg.id')
        //     ->join('points_of_sale as ps','ps.branch_id','=','b.id')
        //     ->join('atms as a','a.id', '=','ps.atm_id')
        //     ->where('bg.id',$group_id)
        //     ->whereNull('a.deleted_at')
        //     ->whereNull('bg.deleted_at')
        //     ->get();

        // $atm_ids = array();
        // foreach($atms as $item){
        //     $id = $item->atm_id;
        //     array_push($atm_ids, $id);
        // }
        // $atm_list   =  Atmnew::findMany($atm_ids);
        // $cobranzas    =  Cobranza::where('group_id', $group_id)->get();
        // dd($atm_ids);

        return view('atm_baja.cobranzas.index');
    }

    public function create(Request $request)
    {
        // if (!$this->user->hasAccess('atms.group.pagare.add')) {
        //     \Log::error('Unauthorized access attempt',
        //         ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
        //     Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
        //     return redirect('/');
        // }

        $atms = \DB::table('atms')
            ->select('atms.id', 'atms.name')
            ->join('atms_per_users', 'atms.id', '=', 'atms_per_users.atm_id')
            ->where('atms_per_users.user_id', $this->user->id)
            ->whereIn('atms.owner_id', [16, 21, 25])
            ->orderBy('atms.id', 'asc')
        ->pluck('name', 'id');

        $tipo_pago = \DB::table('tipo_pago')
        ->orderBy('id', 'asc')
        ->pluck('descripcion', 'id');

        $bancos = [];

        $cuentas = [];

        $grupos = \DB::table('business_groups')
        ->orderBy('id', 'asc')
        ->whereNull('deleted_at')
        ->pluck('description','id');
        


        return view('atm_baja.cobranzas.create', compact('tipo_pago', 'bancos', 'cuentas', 'atms','grupos'));
    }

    public function store(Request $request)
    {
        // if (!$this->user->hasAccess('atms.group.pagare.add')) {
        //     \Log::error('Unauthorized access attempt',
        //         ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
        //     Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
        //     return redirect('/');
        // }
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
            Pagare::create([                    
                'group_id'      => $group_id,
                // 'atm_id'        => $atm_id,
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
            \Log::info("[BAJA | Pagare ingresado correctamente]");

             //Auditoria
             $pagare =\DB::table('atm_inactivate_history')
             ->insert([
                //  'atm_id'        => $atm_id,
                 'group_id'      => $group_id,
                 'operation'     => 'PAGARE - INSERT',
                 'data'          => json_encode($request->except('_token')),
                 'created_at'    => Carbon::now(),
                 'created_by'    => $this->user->id,
                 'updated_at'    => NULL,
                 'updated_by'    => NULL,
                 'deleted_at'    => NULL
             ]);

            \DB::commit();
            Session::flash('message', 'Pagaré registrado correctamente.');
            return redirect()->to('atm/new/'.$group_id.'/'.$group_id.'/pagare')->with('guardar','ok');
        }catch (\Exception $e){
            \DB::rollback();
            \Log::critical($e->getMessage());
            Session::flash('error_message', 'Error al registrar el pagaré');
            return redirect()->back()->with('error', 'Error al registrar el pagaré');
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

        //dd($input);
        //$atm_id     = $input['atm_id'];
        //$group_id   = $input['group_id'];
        $tipo           = $input['tipo'];
        $numero         = $input['numero'];
        $firmante       = $input['firmante'];
        $monto          = $input['monto'];
        $cantidad_pagos = $input['cantidad_pagos'];
        $tasa_interes   = $input['tasa_interes'];
        $vencimiento    = $input['vencimiento'];

        if ($pagare = Pagare::find($id)){
            try{
                
                $pagare_update=\DB::table('grupos_pagares')
                ->where('id',$id)
                ->update([
                    'numero'        => $numero,
                    'firmante'      => $firmante,
                    'tipo'          => $tipo,
                    'vencimiento'   => Carbon::createFromFormat('d/m/Y', $vencimiento)->toDateString(),
                    'monto'         => str_replace('.', '', $monto),
                    'tasa_interes'  => $tasa_interes,
                    'cantidad_pagos'=> $cantidad_pagos,
                    'updated_at'    => Carbon::now(),
                    'updated_by'    => $this->user->id
                ]);
                //   \Log::info('Pagare id= '.$id.'actualizado correctamente');
              \Log::info("[BAJA | Pagare id= '.$id.'actualizado correctamente]");

                //Auditoria
                $pagare_history =\DB::table('atm_inactivate_history')
                ->insert([
                    // 'atm_id'        => $atm_id,
                    'group_id'      => $pagare->group_id,
                    'operation'     => 'PAGARE - UPDATE',
                    'data'          => json_encode($request->except('_token','_method')),
                    'created_at'    => NULL,
                    'created_by'    => NULL,
                    'updated_at'    => Carbon::now(),
                    'updated_by'    => $this->user->id,
                    'deleted_at'    => NULL
                ]);
                \Log::info("[BAJA | Pagare registrado en auditoria]");

                \DB::commit();
                Session::flash('message', 'Pagaré actualizado exitosamente');
                return redirect()->to('atm/new/'.$pagare->group_id.'/'.$pagare->group_id.'/pagare')->with('actualizar','ok');
                
            }catch (\Exception $e){
                \DB::rollback();
                \Log::error("Error updating pagare: " . $e->getMessage());
                Session::flash('error_message','Error al intentar actualizar el pagaré');
                return redirect()->to('atm/new/'.$pagare->group_id.'/'.$pagare->group_id.'/pagare');
            }
        }else{
            \Log::warning("Pagare not found");
            Session::flash('error_message', 'Pagaré no encontrado');
            return redirect()->to('atm/new/'.$pagare->group_id.'/'.$pagare->group_id.'/pagare');
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

            // $atm = \DB::table('business_groups as bg')
            // ->select('ps.atm_id as atm_id')
            // ->join('branches as b','b.group_id','=','bg.id')
            // ->join('points_of_sale as ps','ps.branch_id','=','b.id')
            // ->where('bg.id',$pagare->group_id)
            // ->get();

            try{
               
                if (Pagare::where('id',$id)->delete()){
                    $message    =  'Pagaré eliminado correctamente';
                    $error      = false;
                }
                \Log::debug("Pagare eliminado, pagare_id ".$id);
                \Log::info("[BAJA | Pagare registrado en auditoria]");

                //Auditoria
                $pagare_history =\DB::table('atm_inactivate_history')
                ->insert([
                    // 'atm_id'        => $atm[0]->atm_id,
                    'group_id'      => $pagare->group_id,
                    'operation'     => 'PAGARE - DELETE',
                    'data'          => json_encode(['id'=> $pagare->id, 'numero' => $pagare->numero,'firmante' => $pagare->firmante, 'monto' => $pagare->monto, 'vencimiento' => $pagare->vencimiento,'tasa' => $pagare->tasa_interes, 'cantidad_pagos' => $pagare->cantidad_pagos]),
                    'created_at'    => NULL,
                    'created_by'    => NULL,
                    'updated_at'    => NULL,
                    'updated_by'    => NULL,
                    'deleted_at'    => Carbon::now(),
                    'deleted_by'    => $this->user->id
                ]);
                \Log::info("Pagare registrado en auditoria");

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
