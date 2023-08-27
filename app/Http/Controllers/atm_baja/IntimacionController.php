<?php

namespace App\Http\Controllers\atm_baja;

use Session;

use Carbon\Carbon;
use App\Models\Atm;
use App\Models\Group;
use App\Models\Atmnew;
use App\Models\Intimacion;
use Illuminate\Http\Request;
use App\Models\InactivateHistory;
use App\Http\Controllers\Controller;

class IntimacionController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function index( $group_id, Request $request)
    {
        if (!$this->user->hasAccess('atms.group.intimacion')) {
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

        $intimaciones =  Intimacion::where('group_id', $group_id)->get();

        return view('atm_baja.intimaciones.index', compact('group_id','atm_list','grupo','intimaciones','atm_ids'));
    }

    public function create(Request $request)
    {
        if (!$this->user->hasAccess('atms.group.intimacion.add')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $atm_ids       = $request->get('atm_list');
        $group_id      = $request->get('group_id');
        $grupo         = Group::find($group_id);
        $atm_list      = Atmnew::findMany($atm_ids);
        $intimaciones  =  Intimacion::where('group_id', $group_id)->get();
        $numero        = $intimaciones->count()+1;

        return view('atm_baja.intimaciones.create',compact('atm_ids','group_id','grupo','atm_list','numero'));
    }

    public function store(Request $request)
    {
        if (!$this->user->hasAccess('atms.group.intimacion.add')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();
        
        $input              = $request->all();
        $group_id           = $input['group_id'];
        $numero             = $input['numero'];
        $fecha_vencimiento  = $input['fecha_vencimiento'];
        $fecha_envio        = $input['fecha_envio'];
        $fecha_recepcion    = $input['fecha_recepcion'];

        try{
            $intimacion = Intimacion::create([                    
                'group_id'          => $group_id,
                'numero'            => $numero,
                'fecha_vencimiento' => Carbon::createFromFormat('d/m/Y', $fecha_vencimiento)->toDateString(),
                'fecha_envio'       => Carbon::createFromFormat('d/m/Y', $fecha_envio)->toDateString(),
                'fecha_recepcion'   => Carbon::createFromFormat('d/m/Y', $fecha_recepcion)->toDateString(),
                'created_at'        => Carbon::now(),
                'created_by'        => $this->user->id,
                'updated_at'        => NULL,
                'updated_by'        => NULL,
                'deleted_at'        => NULL,
                'deleted_by'        => NULL
            ]);      

            //Auditoria
            $history = new InactivateHistory();
            $history->group_id    = $intimacion->group_id;
            $history->operation   = 'INTIMACION - INSERT';
            $history->data        = json_encode($intimacion);
            $history->created_at  = Carbon::now();
            $history->created_by  = $this->user->id;
            $history->updated_at  = NULL;
            $history->updated_by  = NULL;
            $history->deleted_at  = NULL;
            $history->deleted_by  = NULL;
            $history->save();


            \DB::commit();
            return redirect()->to('atm/new/'.$group_id.'/'.$group_id.'/intimacion')->with('guardar','ok');
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
        if (!$this->user->hasAccess('atms.group.intimacion.edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $input      = $request->all();

        if($intimacion = Intimacion::find($id))
        {
            $intimacion->fecha_envio        = date("d/m/Y", strtotime($intimacion->fecha_envio));
            $intimacion->fecha_vencimiento  = date("d/m/Y", strtotime($intimacion->fecha_vencimiento));
            $intimacion->fecha_recepcion    = date("d/m/Y", strtotime($intimacion->fecha_recepcion));
            $grupo                          = Group::find($intimacion->group_id);
            $atms = \DB::table('business_groups as bg')
            ->select('ps.atm_id as atm_id')
            ->join('branches as b','b.group_id','=','bg.id')
            ->join('points_of_sale as ps','ps.branch_id','=','b.id')
            ->join('atms as a','a.id', '=','ps.atm_id')
            ->where('bg.id',$intimacion->group_id)
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
                'intimacion' => $intimacion,
                'atm_list'   => $atm_list,
                'grupo'      => $grupo
            ];
            return view('atm_baja.intimaciones.edit', $data);
        }else{
            Session::flash('error_message', 'Intimación no encontrado.');
            return redirect()->back();
        }

    }

    public function update(Request $request, $id)
    {
        if (!$this->user->hasAccess('atms.group.intimacion.edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();
        $input      = $request->all();

        if ($intimacion = Intimacion::find($id)){
            try{
                
                $intimacion->fill([
                    'numero'            => $input['numero'],
                    'fecha_envio'       => Carbon::createFromFormat('d/m/Y', $input['fecha_envio'])->toDateString(),
                    'fecha_vencimiento' => Carbon::createFromFormat('d/m/Y', $input['fecha_vencimiento'])->toDateString(),
                    'fecha_recepcion'   => Carbon::createFromFormat('d/m/Y', $input['fecha_recepcion'])->toDateString(),
                    'updated_at'        => Carbon::now(),
                    'updated_by'        => $this->user->id,
                ])->save();

                //Auditoria
                $history = new InactivateHistory();
                $history->group_id    = $intimacion->group_id;
                $history->operation   = 'INTIMACION - UPDATE';
                $history->data        = json_encode($intimacion);
                $history->created_at  = NULL;
                $history->created_by  = NULL;
                $history->updated_at  = Carbon::now();
                $history->updated_by  = $this->user->id;
                $history->deleted_at  = NULL;
                $history->deleted_by  = NULL;
                $history->save();

                \DB::commit();
                Session::flash('message', 'Intimación actualizada exitosamente');
                return redirect()->to('atm/new/'.$intimacion->group_id.'/'.$intimacion->group_id.'/intimacion')->with('actualizar','ok');
                
            }catch (\Exception $e){
                \DB::rollback();
                \Log::error("Error updating intimacion: " . $e->getMessage());
                Session::flash('error_message','Error al intentar actualizar la intimación');
                return redirect()->to('atm/new/'.$intimacion->group_id.'/'.$intimacion->group_id.'/intimacion')->with('error','ok');
            }
        }else{
            \Log::warning("Intimacion not found");
            Session::flash('error_message', 'Intimación no encontrada');
            return redirect()->to('atm/new/'.$intimacion->group_id.'/'.$intimacion->group_id.'/intimacion')->with('error','ok');
        }

    }

    public function destroy($id)
    {
        if (!$this->user->hasAccess('atms.group.intimacion.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();
        $message = '';
        $error = '';
        if ($intimacion = Intimacion::find($id)){

            try{
               
                if (Intimacion::where('id',$id)->delete()){
                    $message    =  'Intimación eliminada correctamente';
                    $error      = false;
                }

                //Auditoria
                $history = new InactivateHistory();
                $history->group_id    = $intimacion->group_id;
                $history->operation   = 'INTIMACION - DELETE';
                $history->data        = json_encode($intimacion);
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
                \Log::error("Error deleting intimacion: " . $e->getMessage());
                $message    =  'Error al intentar eliminar la intimación';
                $error      = true;
            }
        }else{
            $message    =  'Intimación no encontrada';
            $error      = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }
   

}
