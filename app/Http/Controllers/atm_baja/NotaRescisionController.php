<?php

namespace App\Http\Controllers\atm_baja;

use Session;

use Carbon\Carbon;
use App\Models\Atm;
use App\Models\Group;
use App\Models\Atmnew;
use Illuminate\Http\Request;
use App\Models\NotaRescision;
use App\Http\Controllers\Controller;

class NotaRescisionController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function index( $group_id, Request $request)
    {
        if (!$this->user->hasAccess('atms.group.rescision')) {
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
        $atm_list       =  Atmnew::findMany($atm_ids);
        $rescisiones    =  NotaRescision::where('group_id', $group_id)->get();

        return view('atm_baja.notas_rescisiones.index', compact('group_id','atm_list','grupo','rescisiones','atm_ids'));
    }

    public function create(Request $request)
    {
        if (!$this->user->hasAccess('atms.group.rescision.add')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $atm_ids    = $request->get('atm_list');
        $group_id   = $request->get('group_id');
        $grupo      = Group::find($group_id);
        $atm_list   = Atm::findMany($atm_ids);
        // $atm_list   =  \DB::table('atms')
        //             ->select('atms.id','atms.code', 'atms.name')
        //             ->whereIn('atms.id',$atm_ids)
        //             ->toSql();

        $notas      =  NotaRescision::where(['group_id'=> $group_id])->get();
        $numero     = $notas->count()+1;

        // $atm_id     = $request->get('atm_id');
        // $group_id   = $request->get('group_id');
        // $grupo      = Group::find($group_id );
        // $atm        = Atm::find($atm_id );
        
        // $notas =  NotaRescision::where(['group_id'=> $group_id, 'atm_id'=>$atm_id])->get();
        // $numero  = $notas->count()+1;
        return view('atm_baja.notas_rescisiones.create',compact('atm_ids','atm_list','group_id','grupo','numero'));
    }

    public function store(Request $request)
    {
        if (!$this->user->hasAccess('atms.group.rescision.add')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();
        
        $input          = $request->all();
        $group_id       = $input['group_id'];

        // $atm_id         = $input['atm_id'];
        $group_id       = $input['group_id'];
      
        $numero         = $input['numero'];
        $nombre         = $input['nombre_comercial'];
        $direccion      = $input['direccion'];
        $fecha          = $input['fecha'];

        try{
            NotaRescision::create([                    
                'group_id'      => $group_id,
                'numero'        => $numero,
                'nombre_comercial' => $nombre,
                'fecha'         => Carbon::createFromFormat('d/m/Y', $fecha)->toDateString(),
                'direccion'     => $direccion,      
                'created_at'    => Carbon::now(),
                'created_by'    => $this->user->id,
                'updated_at'    => NULL,
                'updated_by'    => NULL,
                'deleted_at'    => NULL,
                'deleted_by'    => NULL
            ]);      
            \Log::info("[BAJA |Nota de rescision ingresado correctamente]");

             //Auditoria
             $nota_rescision =\DB::table('atm_inactivate_history')
             ->insert([
                 //'atm_id'        => $atm_id,
                 'group_id'      => $group_id,
                 'operation'     => 'NOTA RESCISION - INSERT',
                 'data'          => json_encode($request->except('_token')),
                 'created_at'    => Carbon::now(),
                 'created_by'    => $this->user->id,
                 'updated_at'    => NULL,
                 'updated_by'    => NULL,
                 'deleted_at'    => NULL,
                 'deleted_by'    => NULL
             ]);

            \DB::commit();
            Session::flash('message', 'Nota de rescisión registrado correctamente.');
            return redirect()->to('atm/new/'.$group_id.'/'.$group_id.'/rescision')->with('guardar','ok');
        }catch (\Exception $e){
            \DB::rollback();
            \Log::critical($e->getMessage());
            Session::flash('error_message', 'Error al registrar la Nota de rescisión');
            return redirect()->back()->with('error', 'Error al registrar la Nota de rescisión');
        }
    }

    public function show($id)
    {
        //
    }
   
    public function edit($id, Request $request)
    {
        if (!$this->user->hasAccess('atms.group.rescision.edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $input      = $request->all();

        // $atm_id     = $input['atm_id'];
        // $atm        = Atm::find($atm_id );

        if($nota = NotaRescision::find($id))
        {
            $nota->fecha = date("d/m/Y", strtotime($nota->fecha));
            $grupo        = Group::find($nota->group_id);
            $atms = \DB::table('business_groups as bg')
            ->select('ps.atm_id as atm_id')
            ->join('branches as b','b.group_id','=','bg.id')
            ->join('points_of_sale as ps','ps.branch_id','=','b.id')
            ->join('atms as a','a.id', '=','ps.atm_id')
            ->where('bg.id',$nota->group_id)
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
                'nota'      => $nota,
                'atm_list'  => $atm_list,
                'grupo'     => $grupo

            ];
            return view('atm_baja.notas_rescisiones.edit', $data);
        }else{
            Session::flash('error_message', 'Nota de rescisión no encontrada.');
            return redirect()->back();
        }

    }

    public function update(Request $request, $id)
    {
        if (!$this->user->hasAccess('atms.group.rescision.edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();
        $input      = $request->all();
     
        $numero         = $input['numero'];
        $nombre         = $input['nombre_comercial'];
        $direccion      = $input['direccion'];
        $fecha          = $input['fecha'];


        if ($nota = NotaRescision::find($id)){
            try{
                
                $nota_update=\DB::table('grupos_nota_rescision')
                ->where('id',$id)
                ->update([
                    'numero'            => $numero,
                    'nombre_comercial'  => $nombre,
                    'fecha'             => Carbon::createFromFormat('d/m/Y', $fecha)->toDateString(),
                    'direccion'         => $direccion,
                    'updated_at'        => Carbon::now(),
                    'updated_by'        => $this->user->id
                ]);
              \Log::info("[BAJA |Nota de rescision id= '.$id.'actualizado correctamente]");

                //Auditoria
                $nota_history =\DB::table('atm_inactivate_history')
                ->insert([
                    //'atm_id'        => $atm_id,
                    'group_id'      => $nota->group_id,
                    'operation'     => 'NOTA RESCISION - UPDATE',
                    'data'          => json_encode($request->except('_token','_method')),
                    'created_at'    => NULL,
                    'created_by'    => NULL,
                    'updated_at'    => Carbon::now(),
                    'updated_by'    => $this->user->id,
                    'deleted_at'    => NULL,
                    'deleted_by'    => NULL
                ]);
                \Log::info("[BAJA |Nota de rescision registrada en auditoria]");

                \DB::commit();
                Session::flash('message', 'Nota de rescisión actualizada exitosamente');
                return redirect()->to('atm/new/'.$nota->group_id.'/'.$nota->group_id.'/rescision')->with('actualizar','ok');
                
            }catch (\Exception $e){
                \DB::rollback();
                \Log::error("Error updating rescision: " . $e->getMessage());
                Session::flash('error_message','Error al intentar actualizar la nota de rescision');
                return redirect()->to('atm/new/'.$nota->group_id.'/'.$nota->group_id.'/rescision');
            }
        }else{
            \Log::warning("Nota de rescision not found");
            Session::flash('error_message', 'Pagaré no encontrado');
            return redirect()->to('atm/new/'.$nota->group_id.'/'.$nota->group_id.'/rescision');
        }

    }

    public function destroy($id)
    {
        if (!$this->user->hasAccess('atms.group.rescision.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();
        $message = '';
        $error = '';
        \Log::debug("Intentando elimiar nota_rescisioin_id ".$id);
        if ($nota = NotaRescision::find($id)){

            // $atm = \DB::table('business_groups as bg')
            // ->select('ps.atm_id as atm_id')
            // ->join('branches as b','b.group_id','=','bg.id')
            // ->join('points_of_sale as ps','ps.branch_id','=','b.id')
            // ->where('bg.id',$nota->group_id)
            // ->get();

            try{
               
                if (NotaRescision::where('id',$id)->delete()){
                    $message    =  'Nota de rescisión eliminado correctamente';
                    $error      = false;
                }
                \Log::debug("Nota de rescisión eliminado, pagare_id ".$id);

                //Auditoria
                $nota_history =\DB::table('atm_inactivate_history')
                ->insert([
                    // 'atm_id'        => $atm[0]->atm_id,
                    'group_id'      => $nota->group_id,
                    'operation'     => 'NOTA RESCISION - DELETE',
                    'data'          => json_encode(['id'=> $nota->id, 'numero' => $nota->numero, 'nombre_comercial' => $nota->nombre_comercial, 'fecha' => $nota->fecha,'direccion' => $nota->direccion]),
                    'created_at'    => NULL,
                    'created_by'    => NULL,
                    'updated_at'    => NULL,
                    'updated_by'    => NULL,
                    'deleted_at'    => Carbon::now(),
                    'deleted_by'    => $this->user->id
                ]);
                \Log::info("[BAJA |Nota de rescision registrado en auditoria]");

                \DB::commit();
            }catch (\Exception $e){
                \DB::rollback();
                \Log::error("Error deleting nota de rescision: " . $e->getMessage());
                $message    =  'Error al intentar eliminar la nota de rescisión';
                $error      = true;
            }
        }else{
            $message    =  'Nota de rescision no encontrado';
            $error      = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }
   

}
