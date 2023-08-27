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
use App\Models\GastoAdministrativo;
use App\Http\Controllers\Controller;

class GastoAdministrativoController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function index( $group_id, Request $request)
    {
        if (!$this->user->hasAccess('atms.group.gasto.administrativo')) {
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

        $gastos =  GastoAdministrativo::where('group_id', $group_id)->get();

        return view('atm_baja.gastos.index', compact('group_id','atm_list','grupo','atm_ids','gastos'));
    }

    public function create(Request $request)
    {
        if (!$this->user->hasAccess('atms.group.gasto.administrativo.add')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        
        $atm_ids    = $request->get('atm_list');
        $group_id   = $request->get('group_id');
        $grupo      = Group::find($group_id);
        $atm_list   = Atmnew::findMany($atm_ids);
        $gastos     = GastoAdministrativo::where('group_id', $group_id)->get();
        $numero     = $gastos->count()+1;


        return view('atm_baja.gastos.create',compact('atm_ids','group_id','grupo','atm_list','numero'));
    }

    public function store(Request $request)
    {
        if (!$this->user->hasAccess('atms.group.gasto.administrativo.add')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();
        
        $input      = $request->all();
        $group_id   = $input['group_id'];
        $numero     = $input['numero'];
        $proveedor  = $input['proveedor'];
        $fecha      = $input['fecha'];
        $interno    = $input['interno'];
        $monto      = $input['monto'];
        $comentario = $input['comentario'];

        try{
            $gastos = GastoAdministrativo::create([                    
                'group_id'      => $group_id,
                'numero'        => $numero,
                'proveedor'     => $proveedor,
                'fecha'         => Carbon::createFromFormat('d/m/Y', $fecha)->toDateString(),
                'monto'         => str_replace('.', '', $monto),
                'interno'       => $interno,
                'comentario'    => $comentario,

                'created_at'    => Carbon::now(),
                'created_by'    => $this->user->id,
                'updated_at'    => NULL,
                'updated_by'    => NULL,
                'deleted_at'    => NULL,
            ]);      

            //Auditoria
            $history = new InactivateHistory();
            $history->group_id    = $gastos->group_id;
            $history->operation   = 'GASTO ADMINISTRATIVO - INSERT';
            $history->data        = json_encode($gastos);
            $history->created_at  = Carbon::now();
            $history->created_by  = $this->user->id;
            $history->updated_at  = NULL;
            $history->updated_by  = NULL;
            $history->deleted_at  = NULL;
            $history->deleted_by  = NULL;
            $history->save();

            \DB::commit();
            return redirect()->to('atm/new/'.$group_id.'/'.$group_id.'/gasto_administrativo')->with('guardar','ok');
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
        if (!$this->user->hasAccess('atms.group.gasto.administrativo.edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $input      = $request->all();


        if($gasto = GastoAdministrativo::find($id))
        {
            $gasto->fecha = date("d/m/Y", strtotime($gasto->fecha));
            $grupo        = Group::find($gasto->group_id);

            $atms = \DB::table('business_groups as bg')
            ->select('ps.atm_id as atm_id')
            ->join('branches as b','b.group_id','=','bg.id')
            ->join('points_of_sale as ps','ps.branch_id','=','b.id')
            ->join('atms as a','a.id', '=','ps.atm_id')
            ->where('bg.id',$gasto->group_id)
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
                'gasto'     => $gasto,
                'atm_list'  => $atm_list,
                'grupo'     => $grupo
            ];
            return view('atm_baja.gastos.edit', $data);
        }else{
            Session::flash('error_message', 'Gasto administrativo no encontrado.');
            return redirect()->back();
        }

    }

    public function update(Request $request, $id)
    {
        if (!$this->user->hasAccess('atms.group.gasto.administrativo.edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();
        $input      = $request->all();       
        $numero     = $input['numero'];
        $proveedor  = $input['proveedor'];
        $fecha      = $input['fecha'];
        $interno    = $input['interno'];
        $monto      = $input['monto'];
        $comentario = $input['comentario'];

        if ($gasto = GastoAdministrativo::find($id)){
            try{
                
                $gasto->fill([
                    'numero'        => $numero,
                    'proveedor'     => $proveedor,
                    'fecha'         => Carbon::createFromFormat('d/m/Y', $fecha)->toDateString(),
                    'monto'         => str_replace('.', '', $monto),
                    'interno'       => $interno,
                    'comentario'    => $comentario,
                    'updated_at'    => Carbon::now(),
                    'updated_by'    => $this->user->id
                ])->save();

            //     $gasto_update=\DB::table('grupos_gasto_administrativo')
            //     ->where('id',$id)
            //     ->update([
            //         'numero'        => $numero,
            //         'proveedor'     => $proveedor,
            //         'fecha'         => Carbon::createFromFormat('d/m/Y', $fecha)->toDateString(),
            //         'monto'         => str_replace('.', '', $monto),
            //         'interno'       => $interno,
            //         'comentario'    => $comentario,
            //         'updated_at'    => Carbon::now(),
            //         'updated_by'    => $this->user->id
            //     ]);
            //   \Log::info('Remisión de gasto id= '.$id.'actualizado correctamente');

                //Auditoria
                $history = new InactivateHistory();
                $history->group_id    = $gasto->group_id;
                $history->operation   = 'GASTO ADMINISTRATIVO - UPDATE';
                $history->data        = json_encode($gasto);
                $history->created_at  = NULL;
                $history->created_by  = NULL;
                $history->updated_at  = Carbon::now();
                $history->updated_by  = $this->user->id;
                $history->deleted_at  = NULL;
                $history->deleted_by  = NULL;
                $history->save();
             

                \DB::commit();
                return redirect()->to('atm/new/'.$gasto->group_id.'/'.$gasto->group_id.'/gasto_administrativo')->with('actualizar','ok');
                
            }catch (\Exception $e){
                \DB::rollback();
                \Log::error("Error updating Gasto administrativo: " . $e->getMessage())->with('error','ok');
                return redirect()->to('atm/new/'.$gasto->group_id.'/'.$gasto->group_id.'/gasto_administrativo');
            }
        }else{
            \Log::warning("Gasto administrativo not found");
            return redirect()->to('atm/new/'.$gasto->group_id.'/'.$gasto->group_id.'/gasto_administrativo')->with('error','ok');
        }

    }

    public function destroy($id)
    {
        if (!$this->user->hasAccess('atms.group.gasto.administrativo.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();
        $message = '';
        $error = '';
        \Log::debug("Intentando elimiar pagare_id ".$id);
        if ($gasto = GastoAdministrativo::find($id)){

            try{
               
                if (GastoAdministrativo::where('id',$id)->delete()){
                    $message    =  'Gasto administrativo eliminado correctamente';
                    $error      = false;
                }
                \Log::debug("Gasto administrativo eliminado, pagare_id ".$id);


                 //Auditoria
                 $history = new InactivateHistory();
                 $history->group_id    = $gasto->group_id;
                 $history->operation   = 'GASTO ADMINISTRATIVO - DELETE';
                 $history->data        = json_encode($gasto);
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
