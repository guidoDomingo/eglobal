<?php

namespace App\Http\Controllers\atm_baja;

use Session;

use Carbon\Carbon;
use App\Models\Group;
use App\Models\Atmnew;
use App\Models\Pagare;
use Illuminate\Http\Request;
use App\Models\CompromisoPago;
use App\Models\InactivateHistory;
use App\Http\Controllers\Controller;

class CompromisoPagoController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function index( $group_id, Request $request)
    {
        if (!$this->user->hasAccess('atms.group.compromiso')) {
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
        $compromisos =  CompromisoPago::where('group_id', $group_id)->get();

        return view('atm_baja.compromisos.index', compact('group_id','atm_list','grupo','compromisos','atm_ids'));
    }

    public function create(Request $request)
    {
        if (!$this->user->hasAccess('atms.group.compromiso.add')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $atm_ids     = $request->get('atm_list');
        $group_id    = $request->get('group_id');
        $grupo       = Group::find($group_id);
        $atm_list    = Atmnew::findMany($atm_ids);
        $compromisos =  Pagare::where(['group_id'=> $group_id])->get();
        $numero      = $compromisos->count()+1;

        return view('atm_baja.compromisos.create',compact('atm_ids','group_id','grupo','atm_list','numero'));
    }

    public function store(Request $request)
    {
        if (!$this->user->hasAccess('atms.group.compromiso.add')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();
        
        $input          = $request->all();
        $group_id       = $input['group_id'];
        if($input['estado'] == 'incumplido'){
            $estado = 1;
        }else{
            $estado = 2;
        }
        $numero         = $input['numero'];
        $monto          = $input['monto'];
        $cantidad_pago  = $input['cantidad_pago'];
        $fecha          = $input['fecha'];
        $comentario     = $input['comentario'];

        try{
            $compromiso = CompromisoPago::create([                    
                'group_id'      => $group_id,
                'numero'        => $numero,
                'estado'        => $estado,
                'fecha'         => Carbon::createFromFormat('d/m/Y', $fecha)->toDateString(),
                'monto'         => str_replace('.', '', $monto),
                'cantidad_pago' => $cantidad_pago,
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
            $history->group_id    = $compromiso->group_id;
            $history->operation   = 'COMPROMISOS - INSERT';
            $history->data        = json_encode($compromiso);
            $history->created_at  = Carbon::now();
            $history->created_by  = $this->user->id;
            $history->updated_at  = NULL;
            $history->updated_by  = NULL;
            $history->deleted_at  = NULL;
            $history->deleted_by  = NULL;
            $history->save();
     
            \DB::commit();
            return redirect()->to('atm/new/'.$group_id.'/'.$group_id.'/compromiso')->with('guardar','ok');
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
        if (!$this->user->hasAccess('atms.group.compromiso.edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $input      = $request->all();
        
        if($compromiso = CompromisoPago::find($id))
        {
            $compromiso->fecha  = date("d/m/Y", strtotime($compromiso->fecha));
            $grupo              = Group::find($compromiso->group_id);

            $atms = \DB::table('business_groups as bg')
                ->select('ps.atm_id as atm_id')
                ->join('branches as b','b.group_id','=','bg.id')
                ->join('points_of_sale as ps','ps.branch_id','=','b.id')
                ->join('atms as a','a.id', '=','ps.atm_id')
                ->where('bg.id',$compromiso->group_id)
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
                'compromiso' => $compromiso,
                'atm_list'   => $atm_list,
                'grupo'      => $grupo

            ];
            return view('atm_baja.compromisos.edit', $data);
        }else{
            Session::flash('error_message', 'Pagaré no encontrado.');
            return redirect()->back();
        }

    }

    public function update(Request $request, $id)
    {
        if (!$this->user->hasAccess('atms.group.compromiso.edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();
        $input      = $request->all();

        if ($compromiso = CompromisoPago::find($id)){
            try{
                
                $compromiso->fill([
                    'estado'        => ($input['estado'] == 'incumplido') ? 1 : 2,
                    'cantidad_pago' => $input['cantidad_pago'],
                    'fecha'         => Carbon::createFromFormat('d/m/Y', $input['fecha'])->toDateString(),
                    'monto'         => str_replace('.', '', $input['monto']),
                    'comentario'    => $input['comentario'],
                    'updated_at'    => Carbon::now(),
                    'updated_by'    => $this->user->id,
                ])->save();

                //Auditoria
                $history = new InactivateHistory();
                $history->group_id    = $compromiso->group_id;
                $history->operation   = 'COMPROMISOS - UPDATE';
                $history->data        = json_encode($compromiso);
                $history->created_at  = NULL;
                $history->created_by  = NULL;
                $history->updated_at  = Carbon::now();
                $history->updated_by  = $this->user->id;
                $history->deleted_at  = NULL;
                $history->deleted_by  = NULL;
                $history->save();

                \DB::commit();
                return redirect()->to('atm/new/'.$compromiso->group_id.'/'.$compromiso->group_id.'/compromiso')->with('actualizar','ok');
                
            }catch (\Exception $e){
                \DB::rollback();
                \Log::error("Error updating compromiso: " . $e->getMessage());
                return redirect()->to('atm/new/'.$compromiso->group_id.'/'.$compromiso->group_id.'/compromiso')->with('error','ok');
            }
        }else{
            \Log::warning("Compromiso de pago not found");
            return redirect()->to('atm/new/'.$compromiso->group_id.'/'.$compromiso->group_id.'/compromiso')->with('error','ok');
        }

    }

    public function destroy($id)
    {
        if (!$this->user->hasAccess('atms.group.compromiso.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();
        $message = '';
        $error = '';
        if ($compromiso = CompromisoPago::find($id)){

            try{
               
                if (CompromisoPago::where('id',$id)->delete()){
                    $message    =  'Compromiso de pago eliminado correctamente';
                    $error      = false;
                }
                \Log::debug("Compromiso de pago eliminado, compromiso_id ".$id);

                //Auditoria
                $history = new InactivateHistory();
                $history->group_id    = $compromiso->group_id;
                $history->operation   = 'COMPROMISOS - DELETE';
                $history->data        = json_encode($compromiso);
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
                \Log::error("Error deleting compromiso de pago: " . $e->getMessage());
                $message    =  'Error al intentar eliminar el compromiso de pago';
                $error      = true;
            }
        }else{
            $message    =  'Compromiso de pago no encontrado';
            $error      = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }
   

}
