<?php

namespace App\Http\Controllers\atm_baja;

use Session;

use Carbon\Carbon;
use App\Models\Atm;
use App\Models\Group;
use App\Models\Atmnew;
use Illuminate\Http\Request;
use App\Models\InactivateHistory;
use App\Models\RetiroDispositivo;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;

class RetiroDispositivoController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function index( $group_id, Request $request)
    {
        if (!$this->user->hasAccess('atms.group.retiro')) {
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
        $atm_list  =  Atmnew::findMany($atm_ids);
        $retiros =  RetiroDispositivo::where('group_id', $group_id)->get();
        //$response = null;
        return view('atm_baja.retiros.index', compact('group_id','atm_list','grupo','retiros','atm_ids'));
    }

    public function create(Request $request)
    {
        if (!$this->user->hasAccess('atms.group.retiro.add')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $atm_ids    = $request->get('atm_list');
        $group_id   = $request->get('group_id');
        $grupo      = Group::find($group_id);
        $atm_list   = Atmnew::findMany($atm_ids);
        
        $retiros =  RetiroDispositivo::where(['group_id'=> $group_id])->get();
        $numero  = $retiros->count()+1;
        return view('atm_baja.retiros.create',compact('atm_list','atm_ids','group_id','grupo','numero'));
    }

    public function store(Request $request)
    {
        if (!$this->user->hasAccess('atms.group.retiro.add')) {
            Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        // DB::beginTransaction();
        
        $input      = $request->all();
        $group_id   = $input['group_id'];
        $numero     = $input['numero'];
        $fecha      = $input['fecha'];
        $encargado  = $input['encargado'];
        $firma      = $input['firma'];
        $comentario = $input['comentario'];
        $retiro     = $input['retiro'];

        if(!empty($input['imagen'])){
            $imagen         = $input['imagen'];
            $data_imagen    = json_decode($imagen);
            $nombre_imagen  = $data_imagen->name;
            $urlHost        = request()->getSchemeAndHttpHost();
            Storage::disk('baja_retiro_dispositivo')->put($nombre_imagen,  base64_decode($data_imagen->data));
            // $input['imagen'] = $urlHost.'/resources/images/retiro_dispositivo/'.$nombre_imagen;
            $imagen = $urlHost.'/resources/images/retiro_dispositivo/'.$nombre_imagen;
        }else{
            //$input['imagen'] = '';
            $imagen = '';
        }

        try{
            if($retiro == 'si'){

                $retiro = RetiroDispositivo::create([                    
                    'group_id'      => $group_id,    
                    'numero'        => $numero,
                    'fecha'         => Carbon::createFromFormat('d/m/Y', $fecha)->toDateString(),
                    'firma'         => $firma,
                    'encargado'     => $encargado,
                    'imagen'        => $imagen,
                    'comentario'    => $comentario,
                    'retirado'      => true,
                    'created_at'    => Carbon::now(),
                    'created_by'    => $this->user->id,
                    'updated_at'    => NULL,
                    'updated_by'    => NULL,
                    'deleted_at'    => NULL,
                    'status_ondanet' => NULL,
                    'numero_transferencia'  => NULL,
                ]);   
                Log::info('[BAJAS - RETIRO DE DISPOSITIVO] Retiro de dispositivo registrado');

                //Auditoria
                $history = new InactivateHistory();
                $history->group_id    = $retiro->group_id;
                $history->operation   = 'RETIRO DE DISPOSITIVO - INSERT';
                $history->data        = json_encode($request->except('_token', 'imagen')+['imagen' => $imagen]);
                $history->created_at  = Carbon::now();
                $history->created_by  = $this->user->id;
                $history->updated_at  = NULL;
                $history->updated_by  = NULL;
                $history->deleted_at  = NULL;
                $history->deleted_by  = NULL;
                $history->save();

                // 1- SUSPENDER LA GENERACION  DE CUOTAS
                $alquiler_id = DB::table('alquiler')
                ->where('group_id', $group_id)
                ->where('activo', true)
                ->select('id','destination_operation_id','importe','activo')
                ->get();
                Log::info('alquiler_id');
                Log::info($alquiler_id);

                // if(!isset($alquiler_id)){
                if(!empty($alquiler_id) && !is_null($alquiler_id[0]->id)){

                    $suspender_cuotas_alquiler = DB::table('alquiler')
                    ->where('group_id', $group_id)
                    ->where('activo', true)
                    ->update(['activo' => false,
                              'updated_at' => Carbon::now()]);
                    Log::info('[BAJAS - SUSPENDER CUOTAS] Actualizado el campo activo en la tabla alquiler');

                    $suspender_cuotas_alquiler_housing = DB::table('alquiler_housing')
                    ->where('alquiler_id', $alquiler_id[0]->id)
                    ->update(['activo' => false,
                              'updated_at' => Carbon::now()]);
                    Log::info('[BAJAS - SUSPENDER CUOTAS] Actualizado el campo activo en la tabla alquiler_housing');

                    //Auditoria
                    $history = new InactivateHistory();
                    $history->group_id    = $group_id;
                    $history->operation   = 'RETIRO DE DISPOSITIVO - SUSPENDER GENERACION DE CUOTAS';
                    $history->data        = json_encode(['data_old' => ['alquiler_id' => $alquiler_id [0]->id,
                                                                        'destination_operation_id'=> $alquiler_id [0]->destination_operation_id,
                                                                        'importe'=> $alquiler_id [0]->importe,
                                                                        'activo' => $alquiler_id [0]->activo]]+
                                                        ['data_new' => ['activo' => false,
                                                                        'updated_at' =>  Carbon::now()]]);
                    $history->created_at  = Carbon::now();
                    $history->created_by  = $this->user->id;
                    $history->updated_at  = NULL;
                    $history->updated_by  = NULL;
                    $history->deleted_at  = NULL;
                    $history->deleted_by  = NULL;
                    $history->save();
                    Log::info('[BAJAS - SUSPENDER CUOTAS] Registrado en auditoria');

                }

                // 2 - TRANSFERENCIAS DE EQUIPOS
                /*datos para ondanet*/                
                $housing    = DB::table('atms')
                            ->join('points_of_sale', 'points_of_sale.atm_id','=','atms.id')
                            ->join('branches', 'branches.id','=','points_of_sale.branch_id')
                            ->join('business_groups', 'business_groups.id','=','branches.group_id')
                            ->leftjoin('housing', 'housing.id','=','atms.housing_id') //VER
                            ->whereNull('atms.deleted_at')
                            ->whereNull('business_groups.deleted_at')
                            ->whereNotNull('atms.last_token')
                            ->where('business_groups.id',$group_id)
                            ->select('housing.id','housing.serialnumber')
                            ->get();
       
                $imei= '';
                foreach($housing as $item){
                    $imei .= ",'".$item->serialnumber."'";
                }
                $imeis     = substr($imei,1);
                $response  = $this->sendTransferencia($group_id, $imeis, $retiro->id);
                Log::info('[BAJAS - sendTransferencia] Respuesta');
                Log::info($response);
                if ($response['error'] == false) {
                    //actualizar el status del retiro
                    $saveondanet = RetiroDispositivo::find($retiro->id);
                    $saveondanet->fill([
                        'status_ondanet'        => $response['status'],
                        'numero_transferencia'  => $response['numero_transferencia'],
                    ]);
                    $saveondanet->save();
           
                    //Auditoria
                    $history = new InactivateHistory();
                    $history->group_id    = $group_id;
                    $history->operation   = 'RETIRO DE DISPOSITIVO - UPDATE';
                    $history->data        = json_encode($request->except('_token', 'imagen')+['error' => false,'status_ondanet'=> $response['status'],'numero_transferencia'=> $response['numero_transferencia'],'imagen' => $imagen]);
                    $history->created_at  = Carbon::now();
                    $history->created_by  = $this->user->id;
                    $history->updated_at  = NULL;
                    $history->updated_by  = NULL;
                    $history->deleted_at  = NULL;
                    $history->deleted_by  = NULL;
                    $history->save();

                    //actualizar el estado del cliente a GESTION COMERCIAL - GC
                    $status_group =DB::table('business_groups')
                    ->where('id', $group_id)
                    ->update(['status' => 3,
                              'updated_at' => Carbon::now()]);
                    Log::info('[BAJAS - Retiro de dispositivo] Actualizado el estado');
                    Log::info($status_group);
                   //DB::commit();
                } else {

                    //actualizar el status del retiro
                    $error_ondanet = RetiroDispositivo::find($retiro->id);
                    $error_ondanet->fill(['status_ondanet' => $response['code'] ]);
                    $error_ondanet->save();
            
                    //Auditoria
                    $history = new InactivateHistory();
                    $history->group_id    = $group_id;
                    $history->operation   = 'RETIRO DE DISPOSITIVO - INSERT';
                    $history->data        = json_encode($request->except('_token', 'imagen')+[   'imagen'        => $imagen,
                                                                                                'error'          => true,
                                                                                                'status_ondanet' => $response['status'],
                                                                                                'code_error'     => $response['code'] ]);
                    $history->created_at  = Carbon::now();
                    $history->created_by  = $this->user->id;
                    $history->updated_at  = NULL;
                    $history->updated_by  = NULL;
                    $history->deleted_at  = NULL;
                    $history->deleted_by  = NULL;
                    $history->save();

                    return (redirect()->to('atm/new/'.$group_id.'/'.$group_id.'/retiro_dispositivo'))->with('error_ondanet','ok');
                }

                // DB::commit();
                return redirect()->to('atm/new/'.$group_id.'/'.$group_id.'/retiro_dispositivo')->with('guardar','ok');
            }else{

                $retiro = RetiroDispositivo::create([                    
                    'group_id'      => $group_id,
                    // 'atm_id'        => $atm_id,ñ
                    'numero'        => $numero,
                    'fecha'         => Carbon::createFromFormat('d/m/Y', $fecha)->toDateString(),
                    'firma'         => $firma,
                    'encargado'     => $encargado,
                    'imagen'        => $imagen,
                    'comentario'    => $comentario,
                    'retirado'      => false,
                    'created_at'    => Carbon::now(),
                    'created_by'    => $this->user->id,
                    'updated_at'    => NULL,
                    'updated_by'    => NULL,
                    'deleted_at'    => NULL,
                ]);      
    
                //Auditoria
                $history = new InactivateHistory();
                $history->group_id    = $group_id;
                $history->operation   = 'RETIRO DE DISPOSITIVO - INSERT';
                $history->data        = json_encode($request->except('_token', 'imagen')+['imagen' => $imagen]);
                $history->created_at  = Carbon::now();
                $history->created_by  = $this->user->id;
                $history->updated_at  = NULL;
                $history->updated_by  = NULL;
                $history->deleted_at  = NULL;
                $history->deleted_by  = NULL;
                $history->save();
                
                DB::commit();
                return redirect()->to('atm/new/'.$group_id.'/'.$group_id.'/retiro_dispositivo')->with('guardar','ok');
            }           
        }catch (\Exception $e){
            // DB::rollback();
            Log::critical($e->getMessage());
            // Session::flash('error_message', 'Error al registrar el Retiro de dispositivo');
            return redirect()->to('atm/new/'.$group_id.'/'.$group_id.'/retiro_dispositivo')->with('error','ok');
        }
    }

    public function show($id)
    {
        //
    }
   
    public function edit($id, Request $request)
    {
        if (!$this->user->hasAccess('atms.group.retiro.edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $input      = $request->all();

        if($retiro = RetiroDispositivo::find($id))
        {
            $retiro->fecha = date("d/m/Y", strtotime($retiro->fecha));
            $grupo         = Group::find($retiro->group_id);
            $atms = \DB::table('business_groups as bg')
                ->select('ps.atm_id as atm_id')
                ->join('branches as b','b.group_id','=','bg.id')
                ->join('points_of_sale as ps','ps.branch_id','=','b.id')
                ->join('atms as a','a.id', '=','ps.atm_id')
                ->where('bg.id',$retiro->group_id)
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
                'retiro' => $retiro,
                'grupo'  => $grupo,
                'atm_list'  => $atm_list
            ];

            return view('atm_baja.retiros.edit', $data);
        }else{
            Session::flash('error_message', 'Retiro de dispositivo no encontrado.');
            return redirect()->back();
        }

    }

    public function update(Request $request, $id)
    {
        if (!$this->user->hasAccess('atms.group.retiro.edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();
        $input      = $request->all();
        $numero     = $input['numero'];
        $fecha      = $input['fecha'];
        $encargado  = $input['encargado'];
        $firma      = $input['firma'];
        $comentario = $input['comentario'];
        $retiro     = $input['retiro'];

        if ($retiro = RetiroDispositivo::find($id)){
            try{
                
                if(!empty($input['imagen'])){
                    $imagen         = $input['imagen'];
                    $data_imagen    = json_decode($imagen);
                    $nombre_imagen  = $data_imagen->name;
                    $urlHost        = request()->getSchemeAndHttpHost();
                    $input['imagen'] = $urlHost.'/resources/images/retiro_dispositivo/'.$nombre_imagen;
        
                    if($retiro->imagen != $input['imagen'] && $retiro->imagen != null){
                        if(file_exists(public_path().'/resources'.trim($retiro->imagen))){
                            unlink(public_path().'/resources'.trim($retiro->imagen));
                        }
                        Storage::disk('baja_retiro_dispositivo')->put($nombre_imagen,  base64_decode($data_imagen->data));
                    }else{
                        // unset($input['image']);
                        Storage::disk('baja_retiro_dispositivo')->put($nombre_imagen,  base64_decode($data_imagen->data));
                        $imagen =  $urlHost.'/resources/images/retiro_dispositivo/'.$nombre_imagen;
                    }
                }else{
                    $imagen = $retiro->imagen;
                }

                if($input['retiro'] == 'si'){
                    //registrar acutalizacion del retiro

                    $retiro->fill([
                        'numero'        => $input['numero'],
                        'fecha'         => Carbon::createFromFormat('d/m/Y', $input['fecha'])->toDateString(),
                        'firma'         => $input['firma'],
                        'encargado'     => $input['encargado'],
                        'retirado'      => TRUE,
                        'imagen'        => $imagen,
                        'comentario'    => $input['comentario'],
                        'updated_at'    => Carbon::now(),
                        'updated_by'    => $this->user->id
                    ])->save();

                    //Auditoria
                    $history = new InactivateHistory();
                    $history->group_id    = $retiro->group_id;
                    $history->operation   = 'RETIRO DE DISPOSITIVO - UPDATE';
                    $history->data        = json_encode($retiro);
                    $history->created_at  = NULL;
                    $history->created_by  = NULL;
                    $history->updated_at  = Carbon::now();
                    $history->updated_by  = $this->user->id;
                    $history->deleted_at  = NULL;
                    $history->deleted_by  = NULL;
                    $history->save();
           
                }else{
                    $retiro->fill([
                        'numero'        => $input['numero'],
                        'fecha'         => Carbon::createFromFormat('d/m/Y', $input['fecha'])->toDateString(),
                        'firma'         => $input['firma'],
                        'encargado'     => $input['encargado'],
                        'retirado'      => FALSE,
                        'imagen'        => $imagen,
                        'comentario'    => $input['comentario'],
                        'updated_at'    => Carbon::now(),
                        'updated_by'    => $this->user->id
                    ])->save();

                    //Auditoria
                    $history = new InactivateHistory();
                    $history->group_id    = $retiro->group_id;
                    $history->operation   = 'RETIRO DE DISPOSITIVO - UPDATE';
                    $history->data        = json_encode($retiro);
                    $history->created_at  = NULL;
                    $history->created_by  = NULL;
                    $history->updated_at  = Carbon::now();
                    $history->updated_by  = $this->user->id;
                    $history->deleted_at  = NULL;
                    $history->deleted_by  = NULL;
                    $history->save();

               }
                

                \DB::commit();
                Session::flash('message', 'Retiro de dispositivo actualizado exitosamente');
                return redirect()->to('atm/new/'.$retiro->group_id.'/'.$retiro->group_id.'/retiro_dispositivo')->with('actualizar','ok');

            }catch (\Exception $e){
                \DB::rollback();
                \Log::error("Error updating Retiro de dispositivo: " . $e->getMessage());
                Session::flash('error_message','Error al intentar actualizar el Retiro de dispositivo');
                return redirect()->to('atm/new/'.$retiro->group_id.'/'.$retiro->group_id.'/retiro_dispositivo')->with('error','ok');
            }
        }else{
            \Log::warning("Retiro de dispositivo not found");
            Session::flash('error_message', 'Retiro de dispositivo no encontrado');
            return redirect()->to('atm/new/'.$retiro->group_id.'/'.$retiro->group_id.'/retiro_dispositivo')->with('error','ok');
        }

    }

    public function destroy($id)
    {
        if (!$this->user->hasAccess('atms.group.retiro.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();
        $message = '';
        $error = '';
        if ($retiro = RetiroDispositivo::find($id)){

            try{
               
                if (RetiroDispositivo::where('id',$id)->delete()){
                    $message    =  'Retiro de dispositivo eliminado correctamente';
                    $error      = false;
                }


                  //Auditoria
                  $history = new InactivateHistory();
                  $history->group_id    = $retiro->group_id;
                  $history->operation   = 'RETIRO DE DISPOSITIVO - DELETE';
                  $history->data        = json_encode($retiro);
                  $history->created_at  = NULL;
                  $history->created_by  = NULL;
                  $history->updated_at  = NULL;
                  $history->updated_by  = NULL;
                  $history->deleted_at  = Carbon::now();
                  $history->deleted_by  = $this->user->id;
                  $history->save();
                // //Auditoria
             
                \DB::commit();
            }catch (\Exception $e){
                \DB::rollback();
                \Log::error("Error deleting Retiro de dispositivo: " . $e->getMessage());
                $message    =  'Error al intentar eliminar el Retiro de dispositivo';
                $error      = true;
            }
        }else{
            $message    =  'Retiro de dispositivo no encontrado';
            $error      = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }
    
    /**
        * TRANSFERENCIA: Método [dbo].[P_TRANSFERENCIAS_MINITERMINAL]
        * ---------------------------------------VARIABLES -------------------------------
        * @PDV VARCHAR(20) -> Código (RUC) del cliente que se encuentra creado tanto en el CMS EGLOBAL como en ONDANET EGLOBAL.
        * @IMEI1 VARCHAR(100) -> IMEI/SERIAL de la mini/nano terminal que se encuentra creado tanto en el CMS EGLOBAL como en ONDANET EGLOBAL.
        * ---------------------------------------VARIABLES Opcionales-------------------------------
        * @IMEI2 VARCHAR(100) -> IMEI/SERIAL de la mini/nano terminal que se encuentra creado tanto en el CMS EGLOBAL como en ONDANET EGLOBAL.
        * @IMEI3 VARCHAR(100) -> IMEI/SERIAL de la mini/nano terminal que se encuentra creado tanto en el CMS EGLOBAL como en ONDANET EGLOBAL.
        * @IMEI4 VARCHAR(100) -> IMEI/SERIAL de la mini/nano terminal que se encuentra creado tanto en el CMS EGLOBAL como en ONDANET EGLOBAL.
        * @IMEI5 VARCHAR(100) -> IMEI/SERIAL de la mini/nano terminal que se encuentra creado tanto en el CMS EGLOBAL como en ONDANET EGLOBAL.
        * @IMEI6 VARCHAR(100) -> IMEI/SERIAL de la mini/nano terminal que se encuentra creado tanto en el CMS EGLOBAL como en ONDANET EGLOBAL.
        * @IMEI7 VARCHAR(100) -> IMEI/SERIAL de la mini/nano terminal que se encuentra creado tanto en el CMS EGLOBAL como en ONDANET EGLOBAL.
        * @IMEI8 VARCHAR(100) -> IMEI/SERIAL de la mini/nano terminal que se encuentra creado tanto en el CMS EGLOBAL como en ONDANET EGLOBAL.
        * @IMEI9 VARCHAR(100) -> IMEI/SERIAL de la mini/nano terminal que se encuentra creado tanto en el CMS EGLOBAL como en ONDANET EGLOBAL.
        * @IMEI10 VARCHAR(100) -> IMEI/SERIAL de la mini/nano terminal que se encuentra creado tanto en el CMS EGLOBAL como en ONDANET EGLOBAL.  
    */

    public function sendTransferencia($group_id, $imeis, $retiro_id)
    {
        try {
            DB::beginTransaction();

            $grupo  = DB::table('business_groups')->where('id', $group_id)->first();
            $pdv    = $grupo->ruc;

            $query  =  "SET NOCOUNT ON;
                        SET ANSI_WARNINGS OFF;
                        SET DATEFORMAT dmy;                        
                        DECLARE @rv Numeric(25)
                        EXEC @rv = [DBO].[P_TRANSFERENCIAS_MINITERMINAL]
                        '$pdv',$imeis
                        SELECT @rv";
            Log::info("[Baja - transferencia] Prodecimiento a ejecutar en Ondanet ", ['query' => $query]);
            
            // Auditoria, insertar request a ondanet  
            //Auditoria
            $history = new InactivateHistory();
            $history->group_id    = $group_id;
            $history->operation   = 'RETIRO DE DISPOSITIVO - TRANSFERENCIA REQUEST';
            $history->data        = $query;
            $history->created_at  = Carbon::now();
            $history->created_by  = $this->user->id;
            $history->updated_at  = NULL;
            $history->updated_by  = NULL;
            $history->deleted_at  = NULL;
            $history->deleted_by  = NULL;
            $history->save();     

         
            $udpate_retiro = RetiroDispositivo::find($retiro_id);
            $udpate_retiro->fill(['request_data' => $query]);
            $udpate_retiro->save();
      

            $results = $this->get_one($query);
            Log::info("[Baja - transferencia] Respuesta de Ondanet ", ['response' => $results]);

            //Auditoria, insertar respuesta de ondanet
            $history = new InactivateHistory();
            $history->group_id    = $group_id;
            $history->operation   = 'RETIRO DE DISPOSITIVO - TRANSFERENCIA RESPONSE';
            $history->data        = json_encode($results);
            $history->created_at  = Carbon::now();
            $history->created_by  = $this->user->id;
            $history->updated_at  = NULL;
            $history->updated_by  = NULL;
            $history->deleted_at  = NULL;
            $history->deleted_by  = NULL;
            $history->save(); 

            $tamano = sizeof($results);

            if($tamano == 1){
                $response   = $results[0];
                $result     = '';
                $result_aux = '';
                foreach ($response as $key => $value) {
                    $result_aux = $value;
                }
                foreach ($result_aux as $key1 => $value2) {
                    $result = $value2;
                }
            }else{
                $response   = $results[1];
                $result     = '';
                foreach ($response as $key => $value) {
                    $result = $value;
                }
            }
            //se configura un array con los estados de errores conocidos
            $errors = array(
                "-1 | IMEI no encontrado en el deposito de ONDANET"                                                 => "-1",
                "-2 | Sucursal destino no existe"                                                                   => "-2",
                "-3 | El IMEI ingresado ya se encuentra en el deposito: 1010 - PRODUCTOS TERMINADOS MINITERMINALES" => "-3",
                "-9 | Numero de transferencia no habilitado en Rangos activos"                                      => "-9",
                "-11 | Verifique, el producto que se quiere transferir tiene que ser de la LINEA 'EQUIPOS'"         => "-11",
                "-14 | Número de TRANSFERENCIA ya existe con el mismo Tipo de Comprobante Y TIMBRADO"               => "-14",
	            "-18 | IMEI no disponible"                                                                          => "-18",
	            "-19 | Dos veces ingresado el mismo IMEI"                                                           => "-19",
	            "-25 | Código (STATUS) de transferencia Duplicado, reexportar nuevamente."                          => "-25",
	            "-250 | Cantidad insuficiente"                                                                      => "-250",
	            "212 | Otros errores no definidos ni citados en el procedimiento."                                  => "212"
            );

            $check = array_search($result, $errors);
   
            if (!$check) {

                $numtransferencia             = $results[0][0][''];
                $response_id                  = $results[1][0]['']; //PRODUCCION VACIO
                $data['error']                = false;
                $data['status']               = $response_id;
                $data['numero_transferencia'] = $numtransferencia;
                Log::info($data);
                DB::commit();
                return $data;
            } else {
                $message        = explode("|", $check);
                $data['error']  = true;
                $data['status'] = $check;
                $data['code']   = $message[0];
                Log::info($data);
                DB::commit();
                return $data;
            }
        } catch (\Exception $e) {
            DB::rollback();
            $response['error']   = true;
            $response['status']  = '212';
            $response['code']    = '212';
            Log::warning('[Eglobal - Cliente]', ['result' => $e]);
            return $response;
        }
    }

    /** FUNCIONES PRIVADAS COMUNES*/

    public function get_one($query)
    {
        try {
            DB::beginTransaction();
            $db     = DB::connection('ondanet')->getPdo();
            $stmt   = $db->prepare($query);
            $stmt->execute();

            $register = array();
            $i = 0;
            do {
                $register[$i] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                if (!empty($register[$i])) {
                    $i++;
                }
            } while ($stmt->nextRowset());
            DB::commit();
            return $register;
        } catch (\PDOException $e) {
            DB::rollback();
            return $e;
        }
    }

    public function relanzar($retiro_id)
    {

        try {
            DB::beginTransaction();
            $retiro     = RetiroDispositivo::find($retiro_id);
            $query      = $retiro->request_data;
            Log::info("[Baja - transferencia] Prodecimiento a ejecutar en Ondanet ", ['query relanzar' => $query]);
            
            $results = $this->get_one($query);
            Log::info("[Baja - transferencia] Respuesta de Ondanet ", ['response relanzar' => $results]);

            $tamano = sizeof($results);

            if($tamano == 1){
                $response   = $results[0];
                $result     = '';
                $result_aux = '';
                foreach ($response as $key => $value) {
                    $result_aux = $value;
                }
                foreach ($result_aux as $key1 => $value2) {
                    $result = $value2;
                }
            }else{
                $response   = $results[1];
                $result     = '';
                foreach ($response as $key => $value) {
                    $result = $value;
                }
            }
            //se configura un array con los estados de errores conocidos
            $errors = array(
                "-1 | IMEI no encontrado en el deposito de ONDANET"                                                 => "-1",
                "-2 | Sucursal destino no existe"                                                                   => "-2",
                "-3 | El IMEI ingresado ya se encuentra en el deposito: 1010 - PRODUCTOS TERMINADOS MINITERMINALES" => "-3",
                "-9 | Numero de transferencia no habilitado en Rangos activos"                                      => "-9",
                "-11 | Verifique, el producto que se quiere transferir tiene que ser de la LINEA 'EQUIPOS'"         => "-11",
                "-14 | Número de TRANSFERENCIA ya existe con el mismo Tipo de Comprobante Y TIMBRADO"               => "-14",
	            "-18 | IMEI no disponible"                                                                          => "-18",
	            "-19 | Dos veces ingresado el mismo IMEI"                                                           => "-19",
	            "-25 | Código (STATUS) de transferencia Duplicado, reexportar nuevamente."                          => "-25",
	            "-250 | Cantidad insuficiente"                                                                      => "-250",
	            "212 | Otros errores no definidos ni citados en el procedimiento."                                  => "212"
            );

            $check = array_search($result, $errors);
   
            if (!$check) {

                $numtransferencia             = $results[0][0][''];
                $response_id                  = $results[1][0]['']; //PRODUCCION VACIO
                $data['error']                = false;
                $data['status']               = $response_id;
                $data['numero_transferencia'] = $numtransferencia;

                $retiro->fill([
                    'status'                => $data['code'],
                    'numero_transferencia'  => $data['numero_transferencia']]);
                $retiro->save();

                DB::commit();
                return redirect()->back();
            } else {

                $message  = explode("|", $check);
                $retiro->fill([
                    'status'                => $message[0],
                    'numero_transferencia'  => $message[1]]);
                $retiro->save();

                DB::commit();
                return redirect()->back();
            }
        } catch (\Exception $e) {
            DB::rollback();
            $response['error']   = true;
            $response['status']  = '212';
            $response['code']    = '212';
            Log::warning('[Eglobal - Cliente]', ['result' => $e]);
            return redirect()->back();
        }
    }


}
