<?php

namespace App\Http\Controllers\atm_baja;

use Session;

use Carbon\Carbon;
use App\Models\Atm;
use App\Models\Group;
use App\Models\Atmnew;
use Illuminate\Http\Request;
use App\Models\ReciboGanancia;
use App\Models\InactivateHistory;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class ReciboGananciaController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function index( $group_id, Request $request)
    {
        if (!$this->user->hasAccess('atms.group.recibo.ganancia')) {
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

        $recibos =  ReciboGanancia::where('group_id', $group_id)->get();

        return view('atm_baja.recibos_ganancia.index', compact('group_id','atm_list','grupo','atm_ids','recibos'));
    }

    public function create(Request $request)
    {
        if (!$this->user->hasAccess('atms.group.recibo.ganancia.add')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $atm_ids    = $request->get('atm_list');
        $group_id   = $request->get('group_id');
        $grupo      = Group::find($group_id);
        $atm_list   = Atmnew::findMany($atm_ids);
        $recibos    = ReciboGanancia::where('group_id', $group_id)->get();
        $numero     = $recibos->count()+1;

        return view('atm_baja.recibos_ganancia.create',compact('atm_ids','group_id','grupo','atm_list','numero'));
    }

    public function store(Request $request)
    {
        if (!$this->user->hasAccess('atms.group.recibo.ganancia.add')) {
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
        $importe_cobrado= $input['importe_cobrado'];
        $capital        = $input['capital'];
        $gestionado     = $input['gestionado'];
        $comentario     = $input['comentario'];
        $interes        = $input['interes'];

        if(!empty($input['imagen'])){
            $imagen         = $input['imagen'];
            $data_imagen    = json_decode($imagen);
            $nombre_imagen  = $data_imagen->name;
            $urlHost        = request()->getSchemeAndHttpHost();
            Storage::disk('baja_recibos')->put($nombre_imagen,  base64_decode($data_imagen->data));
            $imagen = $urlHost.'/resources/images/baja_recibos/'.$nombre_imagen;
        }else{
            $imagen = '';
        }

        try{
            $recibo = ReciboGanancia::create([                    
                'group_id'      => $group_id,
                'numero'        => $numero,
                'fecha_finiquito'=> Carbon::createFromFormat('d/m/Y', $fecha_finiquito)->toDateString(),
                'importe_cobrado'=> str_replace('.', '', $importe_cobrado),
                'capital'       => str_replace('.', '', $capital),
                'gestionado'    => $gestionado,
                'comentario'    => $comentario,
                'imagen'        => $imagen,
                'interes'       => $interes,
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
                $history->operation   = 'RECIBO GANANCIA - INSERT';
                $history->data        = json_encode($request->except('_token','imagen')+['imagen' => $imagen]);
                $history->created_at  = Carbon::now();
                $history->created_by  = $this->user->id;
                $history->updated_at  = NULL;
                $history->updated_by  = NULL;
                $history->deleted_at  = NULL;
                $history->deleted_by  = NULL;
                $history->save();

            //  //Auditoria
            //  $recibo_ganancia =\DB::table('atm_inactivate_history')
            //  ->insert([
            //      'atm_id'        => $atm_id,
            //      'group_id'      => $group_id,
            //      'operation'     => 'RECIBO GANANCIA - INSERT',
            //      'data'          => json_encode($request->except('_token','imagen')+['imagen' => $imagen]),
            //      'created_at'    => Carbon::now(),
            //      'created_by'    => $this->user->id,
            //      'updated_at'    => NULL,
            //      'updated_by'    => NULL,
            //      'deleted_at'    => NULL,
            //      'deleted_by'    => NULL
            //  ]);

            \DB::commit();
            return redirect()->to('atm/new/'.$group_id.'/'.$group_id.'/recibo_ganancia')->with('guardar','ok');
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
        if (!$this->user->hasAccess('atms.group.recibo.ganancia.edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $input      = $request->all();
 
        if($recibo = ReciboGanancia::find($id))
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
            return view('atm_baja.recibos_ganancia.edit', $data);
        }else{
            Session::flash('error_message', 'Recibo de ganancia no encontrado.');
            return redirect()->back();
        }

    }

    public function update(Request $request, $id)
    {
        if (!$this->user->hasAccess('atms.group.recibo.ganancia.edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();
        $input      = $request->all();
        $numero         = $input['numero'];
        $fecha_finiquito= $input['fecha_finiquito'];
        $importe_cobrado= $input['importe_cobrado'];
        $capital        = $input['capital'];
        $gestionado     = $input['gestionado'];
        $comentario     = $input['comentario'];
        $interes        = $input['interes'];

        if ($recibo = ReciboGanancia::find($id)){
            try{
                
                if(!empty($input['imagen'])){
                    $imagen         = $input['imagen'];
                    $data_imagen    = json_decode($imagen);
                    $nombre_imagen  = $data_imagen->name;
                    $urlHost        = request()->getSchemeAndHttpHost();
                    $input['imagen'] = $urlHost.'/resources/images/baja_recibos/'.$nombre_imagen;
        
                    if($recibo->imagen != $input['imagen'] && $recibo->imagen != null){
                        if(file_exists(public_path().'/resources'.trim($recibo->imagen))){
                            unlink(public_path().'/resources'.trim($recibo->imagen));
                        }
                        Storage::disk('baja_recibos')->put($nombre_imagen,  base64_decode($data_imagen->data));
                    }else{
                        // unset($input['image']);
                        Storage::disk('baja_recibos')->put($nombre_imagen,  base64_decode($data_imagen->data));
                        $imagen =  $urlHost.'/resources/images/baja_recibos/'.$nombre_imagen;
                        
                    }
                }else{
                    $imagen = $recibo->imagen;
                }

                $recibo->fill([
                    'numero'        => $numero,
                    'fecha_finiquito'=> Carbon::createFromFormat('d/m/Y', $fecha_finiquito)->toDateString(),
                    'importe_cobrado'=> str_replace('.', '', $importe_cobrado),
                    'capital'       => str_replace('.', '', $capital),
                    'gestionado'    => $gestionado,
                    'comentario'    => $comentario,
                    'imagen'        => $imagen,
                    'interes'       => $interes,
                    'updated_at'    => Carbon::now(),
                    'updated_by'    => $this->user->id
                ])->save();

            //     $recibo_update=\DB::table('grupos_recibo_ganancia')
            //     ->where('id',$id)
            //     ->update([
            //         'numero'        => $numero,
            //         'fecha_finiquito'=> Carbon::createFromFormat('d/m/Y', $fecha_finiquito)->toDateString(),
            //         'importe_cobrado'=> str_replace('.', '', $importe_cobrado),
            //         'capital'       => str_replace('.', '', $capital),
            //         'gestionado'    => $gestionado,
            //         'comentario'    => $comentario,
            //         'imagen'        => $imagen,
            //         'interes'       => $interes,
            //         'updated_at'    => Carbon::now(),
            //         'updated_by'    => $this->user->id
            //     ]);
            //   \Log::info('Recibo de ganancia id= '.$id.'actualizado correctamente');

                //Auditoria
                $history = new InactivateHistory();
                $history->group_id    = $recibo->group_id;
                $history->operation   = 'RECIBO GANANCIA - UPDATE';
                $history->data        = json_encode(['id'=> $recibo->id, 'numero' => $recibo->numero,'fecha_finiquito' => $recibo->fecha_finiquito, 'importe_cobrado' => $recibo->importe_cobrado,'capital' => $recibo->capital, 'interes' => $recibo->interes,'gestionado' => $recibo->gestionado, 'comentario' => $recibo->comentario, 'imagen' => $recibo->imagen]);
                $history->created_at  = NULL;
                $history->created_by  = NULL;
                $history->updated_at  = Carbon::now();
                $history->updated_by  = $this->user->id;
                $history->deleted_at  = NULL;
                $history->deleted_by  = NULL;
                $history->save();
              
                \DB::commit();
                return redirect()->to('atm/new/'.$recibo->group_id.'/'.$recibo->group_id.'/recibo_ganancia')->with('actualizar','ok');
                
            }catch (\Exception $e){
                \DB::rollback();
                \Log::error("Error updating Recibo de ganancia: " . $e->getMessage());
                return redirect()->to('atm/new/'.$recibo->group_id.'/'.$recibo->group_id.'/recibo_ganancia')->with('error','ok');
            }
        }else{
            \Log::warning("Pagare not found");
            return redirect()->to('atm/new/'.$recibo->group_id.'/'.$recibo->group_id.'/recibo_ganancia')->with('error','ok');
        }

    }

    public function destroy($id)
    {
        if (!$this->user->hasAccess('atms.group.recibo.ganancia.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();
        $message = '';
        $error = '';
        \Log::debug("Intentando elimiar recibo_ganancia_id ".$id);
        if ($recibo = ReciboGanancia::find($id)){
          
            try{
               
                if (ReciboGanancia::where('id',$id)->delete()){
                    $message    =  'Recibo de ganancia eliminado correctamente';
                    $error      = false;
                }

                //Auditoria
                $history = new InactivateHistory();
                $history->group_id    = $recibo->group_id;
                $history->operation   = 'RECIBO GANANCIA - DELETE';
                $history->data        = json_encode($recibo);
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
                \Log::error("Error deleting recibo: " . $e->getMessage());
                $message    =  'Error al intentar eliminar el Recibo de ganancia';
                $error      = true;
            }
        }else{
            $message    =  'Recibo de ganancia no encontrado';
            $error      = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }
   

}
