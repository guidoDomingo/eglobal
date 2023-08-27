<?php

namespace App\Http\Controllers\atm_baja;

use Session;

use Carbon\Carbon;
use App\Models\Atm;
use App\Models\Group;
use App\Models\Atmnew;
use App\Models\Pagare;
use Illuminate\Http\Request;
use App\Models\ReciboPerdida;
use App\Models\InactivateHistory;
use App\Http\Controllers\Controller;

class ReciboPerdidaController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function index( $group_id, Request $request)
    {
        if (!$this->user->hasAccess('atms.group.recibo.perdida')) {
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

        $recibos =  ReciboPerdida::where('group_id', $group_id)->get();

        return view('atm_baja.recibos_perdida.index', compact('group_id','atm_list','grupo','atm_ids','recibos'));
    }

    public function create(Request $request)
    {
        if (!$this->user->hasAccess('atms.group.recibo.perdida.add')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $atm_ids    = $request->get('atm_list');
        $group_id   = $request->get('group_id');
        $grupo      = Group::find($group_id);
        $atm_list   = Atmnew::findMany($atm_ids);
        $recibos =  ReciboPerdida::where('group_id', $group_id)->get();
        $numero     = $recibos->count()+1;

        return view('atm_baja.recibos_perdida.create',compact('atm_ids','group_id','grupo','atm_list','numero'));
    }

    public function store(Request $request)
    {
        if (!$this->user->hasAccess('atms.group.recibo.perdida.add')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();
        
        $input = $request->all();
        $group_id       = $input['group_id'];
        
        $numero         = $input['numero'];
        $fecha_finiquito= $input['fecha_finiquito'];
        $valor          = $input['valor'];
        $forma_cobro    = $input['forma_cobro'];
        $gestionado     = $input['gestionado'];
        $comentario     = $input['comentario'];

        try{
            $recibo = ReciboPerdida::create([                    
                'group_id'      => $group_id,
                'numero'        => $numero,
                'forma_cobro'   => $forma_cobro,
                'fecha_finiquito'=> Carbon::createFromFormat('d/m/Y', $fecha_finiquito)->toDateString(),
                'valor'         => str_replace('.', '', $valor),
                'gestionado'    => $gestionado,
                'comentario'    => $comentario,
                'created_at'    => Carbon::now(),
                'created_by'    => $this->user->id,
                'updated_at'    => NULL,
                'updated_by'    => NULL,
                'deleted_at'    => NULL,
                'deleted_by'    => NULL
            ]);      
            //Auditoria
            $history = new InactivateHistory();
            $history->group_id    = $recibo->group_id;
            $history->operation   = 'RECIBO PERDIDA - INSERT';
            $history->data        = json_encode($recibo);
            $history->created_at  = Carbon::now();
            $history->created_by  = $this->user->id;
            $history->updated_at  = NULL;
            $history->updated_by  = NULL;
            $history->deleted_at  = NULL;
            $history->deleted_by  = NULL;
            $history->save();


            //  //Auditoria
            //  $recibo_perdida =\DB::table('atm_inactivate_history')
            //  ->insert([
            //      'atm_id'        => $atm_id,
            //      'group_id'      => $group_id,
            //      'operation'     => 'RECIBO PERDIDA - INSERT',
            //      'data'          => json_encode($request->except('_token')),
            //      'created_at'    => Carbon::now(),
            //      'created_by'    => $this->user->id,
            //      'updated_at'    => NULL,
            //      'updated_by'    => NULL,
            //      'deleted_at'    => NULL,
            //      'deleted_by'    => NULL
            //  ]);

            \DB::commit();
            return redirect()->to('atm/new/'.$group_id.'/'.$group_id.'/recibo_perdida')->with('guardar','ok');
        }catch (\Exception $e){
            \DB::rollback();
            \Log::critical($e->getMessage());
            Session::flash('error_message', 'Error al registrar el Recibo de perdida');
            return redirect()->back()->with('error', 'ok');
        }
    }

    public function show($id)
    {
        //
    }
   
    public function edit($id, Request $request)
    {
        if (!$this->user->hasAccess('atms.group.recibo.perdida.edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $input      = $request->all();
      

        if($recibo = ReciboPerdida::find($id))
        {
            $recibo->fecha_finiquito = date("d/m/Y", strtotime($recibo->fecha_finiquito));
            $grupo                   = Group::find($recibo->group_id);

            $atms = \DB::table('business_groups as bg')
            ->select('ps.atm_id as atm_id')
            ->join('branches as b','b.group_id','=','bg.id')
            ->join('points_of_sale as ps','ps.branch_id','=','b.id')
            ->join('atms as a','a.id', '=','ps.atm_id')
            ->where('bg.id',$recibo->group_id)
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
                'recibo'    => $recibo,
                'atm_list'  => $atm_list,
                'grupo'     => $grupo
            ];
            return view('atm_baja.recibos_perdida.edit', $data);
        }else{
            Session::flash('error_message', 'Recibo de perdida no encontrado.');
            return redirect()->back();
        }

    }

    public function update(Request $request, $id)
    {
        if (!$this->user->hasAccess('atms.group.recibo.perdida.edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();
        $input      = $request->all();
        $numero         = $input['numero'];
        $fecha_finiquito= $input['fecha_finiquito'];
        $valor          = $input['valor'];
        $forma_cobro    = $input['forma_cobro'];
        $gestionado     = $input['gestionado'];
        $comentario     = $input['comentario'];

        if ($recibo = ReciboPerdida::find($id)){
            try{
                
                $recibo->fill([
                    'numero'        => $numero,
                    'forma_cobro'   => $forma_cobro,
                    'fecha_finiquito'=> Carbon::createFromFormat('d/m/Y', $fecha_finiquito)->toDateString(),
                    'valor'         => str_replace('.', '', $valor),
                    'gestionado'    => $gestionado,
                    'comentario'    => $comentario,
                    'updated_at'    => Carbon::now(),
                    'updated_by'    => $this->user->id
                ])->save();

            //     $recibo_update=\DB::table('grupos_recibo_perdida')
            //     ->where('id',$id)
            //     ->update([
            //         'numero'        => $numero,
            //         'forma_cobro'   => $forma_cobro,
            //         'fecha_finiquito'=> Carbon::createFromFormat('d/m/Y', $fecha_finiquito)->toDateString(),
            //         'valor'         => str_replace('.', '', $valor),
            //         'gestionado'    => $gestionado,
            //         'comentario'    => $comentario,
            //         'updated_at'    => Carbon::now(),
            //         'updated_by'    => $this->user->id
            //     ]);
            //   \Log::info('Recibo de perdida id= '.$id.'actualizado correctamente');

               //Auditoria
               $history = new InactivateHistory();
               $history->group_id    = $recibo->group_id;
               $history->operation   = 'RECIBO PERDIDA - UPDATE';
               $history->data        = json_encode($recibo);
               $history->created_at  = NULL;
               $history->created_by  = NULL;
               $history->updated_at  = Carbon::now();
               $history->updated_by  = $this->user->id;
               $history->deleted_at  = NULL;
               $history->deleted_by  = NULL;
               $history->save();

                \DB::commit();
                return redirect()->to('atm/new/'.$recibo->group_id.'/'.$recibo->group_id.'/recibo_perdida')->with('actualizar','ok');
                
            }catch (\Exception $e){
                \DB::rollback();
                \Log::error("Error updating Recibo de perdida: " . $e->getMessage());
                return redirect()->to('atm/new/'.$recibo->group_id.'/'.$recibo->group_id.'/recibo_perdida')->with('error','ok');
            }
        }else{
            \Log::warning("Pagare not found");
            return redirect()->to('atm/new/'.$recibo->group_id.'/'.$recibo->group_id.'/recibo_perdida')->with('error','ok');
        }

    }

    public function destroy($id)
    {
        if (!$this->user->hasAccess('atms.group.recibo.perdida.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();
        $message = '';
        $error = '';
        \Log::debug("Intentando elimiar recibo_perdida_id ".$id);
        if ($recibo = ReciboPerdida::find($id)){


            try{
               
                if (ReciboPerdida::where('id',$id)->delete()){
                    $message    =  'Recibo de perdida eliminado correctamente';
                    $error      = false;
                }
                \Log::debug("Recibo de perdida eliminado, recibo_id ".$id);


                //Auditoria
                $history = new InactivateHistory();
                $history->group_id    = $recibo->group_id;
                $history->operation   = 'RECIBO PERDIDA - DELETE';
                $history->data        = json_encode($recibo);
                $history->created_at  = NULL;
                $history->created_by  = NULL;
                $history->updated_at  = NULL;
                $history->updated_by  = NULL;
                $history->deleted_at  = Carbon::now();
                $history->deleted_by  = $this->user->id;
                $history->save();

                // //Auditoria
                // $recibo_history =\DB::table('atm_inactivate_history')
                // ->insert([
                //     'atm_id'        => $atm[0]->atm_id,
                //     'group_id'      => $recibo->group_id,
                //     'operation'     => 'RECIBO PERDIDA - DELETE',
                //     'data'          => json_encode(['id'=> $recibo->id, 'numero' => $recibo->numero,'fecha_finiquito' => $recibo->fecha_finiquito, 'valor' => $recibo->valor, 'forma_cobro' => $recibo->forma_cobro,'gestionado' => $recibo->gestionado, 'comentario' => $recibo->comentario]),
                //     'created_at'    => NULL,
                //     'created_by'    => NULL,
                //     'updated_at'    => NULL,
                //     'updated_by'    => NULL,
                //     'deleted_at'    => Carbon::now(),
                //     'deleted_by'    => $this->user->id
                // ]);
                // \Log::info("Recibo de perdida registrado en auditoria");

                \DB::commit();
            }catch (\Exception $e){
                \DB::rollback();
                \Log::error("Error deleting recibo: " . $e->getMessage());
                $message    =  'Error al intentar eliminar el Recibo de perdida';
                $error      = true;
            }
        }else{
            $message    =  'Recibo de perdida no encontrado';
            $error      = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }
   

}
