<?php

namespace App\Http\Controllers;

use App\Exports\ExcelExport;
use Session;

use Carbon\Carbon;
use App\Models\Group;
use App\Http\Requests;
use App\Models\Atmnew;
use App\Models\Contract;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Excel;


class ContractController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

  
    public function index(Request $request)
    {
        if (!$this->user->hasAccess('gestor_contratos')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            return redirect('/')->with('error', 'No posee permisos para realizar esta accion.');
        }
        $contratos =  \DB::table('business_groups')
                    ->select('business_groups.id as grupo_id',
                                'business_groups.ruc as grupo_ruc',
                                'business_groups.description as grupo_name',
                                'contract.id as contract_id',
                                'contract.number as contract_number', 
                                'contract.credit_limit as contract_credit',
                                'contract.status as contract_stats',
                                'contract.date_init as fecha_init',
                                'contract.date_end as fecha_end')
                    ->Join('contract','contract.busines_group_id','=','business_groups.id')
                    ->whereNull('business_groups.deleted_at')
                    ->get();
        return view('contratos.index', compact('contratos'));
    }

   
    public function create()
    {
        if (!$this->user->hasAccess('contratos.add|edit')  || !$this->user->hasAccess('gestor_contratos.add')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            return redirect('/')->with('error', 'No posee permisos para realizar esta accion.');
        }
      
        $grupos = Group::pluck('description', 'id','ruc')->toArray();
        $groups = Group::pluck('description', 'id','ruc')->toArray();
        $contract_types                 = \DB::table('contract_type')->pluck('description','id')->toArray();

        $date = Carbon::now();
        
        $contract_date_ini = ( $date)->format('d-m-Y');
        $contract_date_end = ( $date->addYear())->format('d-m-Y');
        $reservationtime_contract = $contract_date_ini .' - '.$contract_date_end;

        $grupo = [];
        $data = [
           
            'grupos'        => $grupos,
            'grupo'        => $grupo,
            'selected_grupo' => null,
        ];

        return view('contratos.create', compact('grupos','groups','contract_types','reservationtime_contract'));
    }

   
    public function store(Request $request)
    {
        if (!$this->user->hasAccess('contratos.add|edit') || !$this->user->hasAccess('gestor_contratos.add')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            return redirect('/')->with('error', 'No posee permisos para realizar esta accion.');
        }
        $input = $request->all();
       // dd($input);
        \DB::beginTransaction();

        
        /*SET DATE RANGE*/
        $daterange = explode(' - ',  str_replace('/','-',$request->reservationtime));
        $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
        $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));


        $contrato = new Contract();
        $contrato->number                   = $input['number'];
        $contrato->busines_group_id         = $input['group_id'];
        $contrato->credit_limit             = str_replace('.', '', $input['credit_limit']);
        //$contrato->date_init                = date("Y-m-d h:i:s", strtotime($request->date_init));
        //$contrato->date_end                 = date("Y-m-d h:i:s", strtotime($request->date_init));
        $contrato->date_init                = $daterange[0];
        $contrato->date_end                 = $daterange[1];
        //$contrato->date_init                = Carbon::createFromFormat('d/m/Y', $request->date_init)->toDateString();
        //$contrato->date_end                 = Carbon::createFromFormat('d/m/Y', $request->date_end)->toDateString();
        $contrato->reception_date           = $request->status==1?Carbon::now():null;// Estado = 1 (RECEPCIONADO)
        if($request->reception_date == 1 ){
            \Log::info('signature date 1');
            $contrato->signature_date       = Carbon::now();
        }
        $contrato->created_at               = Carbon::now();
        $contrato->updated_at               = Carbon::now();
        $contrato->observation              = $input['observation'];
        $contrato->created_by               = $this->user->id;
        $contrato->contract_type            = $input['contract_type'];
        $contrato->status                   = $input['status'];
        $contrato->image                    = isset($input['image'])?Carbon::now():null;

        if($request->ajax()){
            $respuesta = [];
            try{
                if ($contrato->save()){
                    $data = [];
                    $data['id'] = $contrato->id;
                    \Log::info("Nuevo Contrato creado");

                    //Asociando grupo a branch
                    \DB::table('branches')
                    ->where('id', $input['branch_id'])
                    ->update([
                        'group_id' => $input['group_id'],
                    ]);
                    \Log::info("Grupo asociado al branch correctamente");


                    //PROGRESO DE CREACION - ABM V2
                    if ( $request->abm == 'v2'){
                        \DB::table('atms')
                        ->where('id', $input['atm_id'])
                        ->update([
                            'atm_status' => -7,
                        ]);
                        \Log::info("ABM Version 2, Paso 3 - CONTRATOS. Estado de creacion: -7");
                    }

                    $respuesta['mensaje'] = 'Contrato agregado correctamente.';
                    $respuesta['tipo'] = 'success';
                    $respuesta['data'] = $data;
                    $respuesta['url'] = route('contracts.update',[$contrato->id]);

                    //CONTRACT HISTORY
                    \DB::table('contract_history')->insert(
                        ['contract_id'          => $contrato->id, 
                        'number'                => $contrato->number,
                        'date_init'             => $contrato->date_init,
                        'date_end'              => $contrato->date_end,
                        'status_2'              => $contrato->status,
                        'observation'           => $contrato->observation,
                        'created_at'            => $contrato->created_at,
                        'created_by'            => $contrato->created_by,
                        'updated_at'            => null,
                        'updated_by'            => null,
                        'contract_type'         => $contrato->contract_type
                    ]);
                    \Log::info("CONTRACT HISTORY agregado correctamente.");
                    \DB::commit();
                    return $respuesta;
                } else {
                    \DB::rollback();
                    Session::flash('error_message', 'Ocurrio un error al intentar guardar el registro');
                    return redirect()->back();
                }
            }catch (\Exception $e){
                \DB::rollback();
                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Error al crear el contrato.';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }
        }else{
            $respuesta = [];
            try{
                if($contrato->save()){

                    //CONTRACT HISTORY
                    \DB::table('contract_history')->insert(
                        ['contract_id'          => $contrato->id, 
                        'number'                => $contrato->number,
                        'date_init'             => $contrato->date_init,
                        'date_end'              => $contrato->date_end,
                        'status_2'              => $contrato->status,
                        'observation'           => $contrato->observation,
                        'created_at'            => $contrato->created_at,
                        'created_by'            => $contrato->created_by,
                        'updated_at'            => null,
                        'updated_by'            => null,
                        'contract_type'         => $contrato->contract_type
                    ]);
                    \Log::info("CONTRACT HISTORY agregado correctamente.");

                    $message = 'Agregado correctamente';
                    Session::flash('message', $message);

                    \DB::commit();
                    return redirect()->route('contracts.index')->with('guardar','ok');

                }else{
                    \DB::rollback();
                    Session::flash('error_message', 'Ocurrio un error al intentar guardar el registro');
                    return redirect()->back()->withInput()->with('error','ok');
                }
            }catch (\Exception $e){
                \DB::rollback();
                \Log::critical($e->getMessage());
                return redirect()->route('contracts.index')->with('error','ok');

            }
        }

    }

  
    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        if (!$this->user->hasAccess('contratos.add|edit')  || !$this->user->hasAccess('gestor_contratos.edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            return redirect('/')->with('error', 'No posee permisos para realizar esta accion.');
        }
       // dd($id);
        if($contrato = Contract::find($id))
        {  
            $grupo = Group::find($contrato->busines_group_id);
            $groups                         = Group::pluck('description', 'id');
            $contract_types                 = \DB::table('contract_type')->pluck('description','id');
            

            if(empty($contrato)){
                $reservationtime_contract = '';
            }else{
                $contract_date_ini = ($contrato->date_init)->format('d-m-Y');
                $contract_date_end = ($contrato->date_end)->format('d-m-Y');
                $reservationtime_contract = $contract_date_ini .' - '.$contract_date_end;
            }

            
            return view('contratos.edit', compact('contrato','groups','contract_types','reservationtime_contract','grupo'));
        }else{
            Session::flash('error_message', 'Contrato no encontrado.');
            return redirect('contracts');
        }
    }


    public function update(Request $request, $id)
    {
        if (!$this->user->hasAccess('contratos.add|edit')  || !$this->user->hasAccess('gestor_contratos.edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        //dd($request);
        $contrato = Contract::find($id);

        if (!$contrato) {
            \Log::warning("contrato not found");
            return redirect()->back()->with('error', 'contrato no valido');
        }
   
        /*SET DATE RANGE*/
        $daterange = explode(' - ',  str_replace('/','-',$request->reservationtime));
        $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
        $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));

        $contrato->number                       = $request->number;
        $contrato->busines_group_id             = $request->group_id;
        $contrato->credit_limit                 = str_replace('.', '', $request->credit_limit);
        // $contrato->date_init                    = date("Y-m-d H:i:s", strtotime($request->date_init));
        $contrato->date_init                    = $daterange[0];
        // $contrato->date_end                     = date("Y-m-d H:i:s", strtotime($request->date_end));
        $contrato->date_end                     = $daterange[1];
        //$contrato->date_init                    = Carbon::createFromFormat('d/m/Y', $request->date_init)->toDateString();
        //$contrato->date_end                     = Carbon::createFromFormat('d/m/Y', $request->date_end)->toDateString();
        //$contrato->reception_date               = isset($request->reception_date)?Carbon::now():null;
        if($request->status == 2 && $request->reception_date == 1 ){ //STATUS :'1' => 'RECEPCIONADO','2' => 'ACTIVO', '3' => 'INACTIVO','4' => 'VENCIDO'
            $contrato->signature_date       = Carbon::now();
        }else{
            $contrato->signature_date       = null;
        }
        //$contrato->created_at                 = Carbon::now();
        $contrato->updated_at                   = Carbon::now();
        $contrato->observation                  = $request->observation;
        //$contrato->created_by                 = $this->user->id;
        $contrato->number                       = $request->number;
        $contrato->contract_type                = $request->contract_type;
        $contrato->status                       = $request->status;
        $contrato->image                        = $request->image;

        if($request->ajax()){
            $respuesta = [];
            try{
                $dataAnterior = Contract::find($id);

                if ($contrato->save()){
                    $data = [];
                    $data['id'] = $contrato->id;
                    \Log::info("Contrato actualizado correctamente.");

                    // //PROGRESO DE CREACION - ABM V2
                    // if ( $request->abm == 'v2'){
                    //     \DB::table('atms')
                    //     ->where('id', $request->atm_id)
                    //     ->update([
                    //         'atm_status' => -7,
                    //     ]);
                    //     \Log::info("ABM Version 2, Paso 3 - CONTRATOS. Estado de actualizacion: -7");
                    // }
                   

                    \DB::table('contract_history')->insert(
                        ['contract_id'          => $contrato->id, 
                        'number'                => $contrato->number,
                        'date_init'             => $contrato->date_init,
                        'date_end'              => $contrato->date_end,
                        'status_2'              => $contrato->status,
                        'observation'           => $contrato->observation,
                        'created_at'            => $contrato->created_at,
                        'created_by'            => null,
                        'updated_by'            => $this->user->id,
                        'updated_at'            => $contrato->updated_at,
                        'contract_type'         => $contrato->contract_type
                    ]);
              
                    \Log::info("CONTRACT HISTORY actualizado correctamente.");
                    $respuesta['mensaje'] = 'Contrato Actualizado correctamente.';
                    $respuesta['tipo'] = 'success';
                    $respuesta['data'] = $data;
                    $respuesta['url'] = route('contracts.update',[$contrato->id]);
                    return $respuesta;
                }
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Error al actualizar el contrato.';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }
        }else{
            \DB::beginTransaction();
            try{

                if ($contrato->save()){
                    \DB::table('contract_history')->insert(
                        ['contract_id'          => $contrato->id, 
                        'number'                => $contrato->number,
                        'date_init'             => $contrato->date_init,
                        'date_end'              => $contrato->date_end,
                        'status_2'              => $contrato->status,
                        'observation'           => $contrato->observation,
                        'created_at'            => $contrato->created_at,
                        'created_by'            => null,
                        'updated_by'            => $this->user->id,
                        'updated_at'            => $contrato->updated_at,
                        'contract_type'         => $contrato->contract_type
                    ]);
                    \Log::info("CONTRACT HISTORY actualizado correctamente.");

                    $message = 'Actualizado correctamente';
                    Session::flash('message', $message);
                    \DB::commit();

                    return redirect()->route('contracts.index')->with('actualizar','ok');
                }else{
                    \Log::warning("Error updating the contrato data. Id: {$contrato->id}");
                    Session::flash('message', 'Error al actualziar el registro');
                    \DB::rollback();
                    return redirect()->route('contracts.index')->with('error','ok');
                }

            }catch (\Exception $e){
                \DB::rollback();
                \Log::critical($e->getMessage());
                Session::flash('message', 'Error al actualziar el registro');
                return redirect()->route('contracts.index')->with('error','ok');
            }
        }
    }

 
    public function destroy($id)
    {
        //
    }

    public function contracts_reports(){
        if (!$this->user->hasAccess('gestor_contratos.reports')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        try{
            $whereGroup = "";
            $whereAtm = "";
            $whereContract = "";

            $groups = Group::orderBy('business_groups.description')->where(function($query) use($whereGroup){
                if(!empty($whereGroup)){
                    $query->whereRaw($whereGroup);
                }
            })->get()->pluck('description', 'id')->prepend('Todos', '0')->toArray();

            $group_id= 0;
            $atms     = Atmnew::orderBy('atms.name')->where(function($query) use($whereAtm){
                if(!empty($whereAtm)){
                    $query->whereRaw($whereAtm);
                }
            })->get()->pluck('name','id')->prepend('Todos', '0')->toArray();

            $atm_id = 0;
            $status = 0;


            $date = Carbon::now();
        
            $contract_date_ini = ( $date)->format('d-m-Y');
            $contract_date_end = ( $date->addYear())->format('d-m-Y');
            $reservationtime_contract = $contract_date_ini .' - '.$contract_date_end;
    
            return view('contratos.contratos_report', compact('groups','atms','status','group_id','atm_id','reservationtime_contract'));

        }catch (\Exception $e){
            \Log::error("Error en la consulta de reportes" . $e);
            return redirect()->route('contracts.index')->with('error','ok');
        }

    }

    public function contractsSearch(Request $request){        
        if (!$this->user->hasAccess('gestor_contratos.reports')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = $request->all();

        if(isset($input['search'])){
  
            try{
                //$where = " CAST(atms.code AS BIGINT)= contract.number AND ";
                $where = " 1=1 AND ";
                if($input['reservationtime'] <> 'Todos'){
                    $fecha = explode(' - ',  str_replace('/','-',$input['reservationtime']));
                    $fecha[0] = date('Y-m-d H:i:s', strtotime($fecha[0]));
                    $fecha[1] = date('Y-m-d H:i:s', strtotime($fecha[1]));
                    $where .= "contract.created_at BETWEEN '{$fecha[0]}' AND '{$fecha[1]}' AND ";
                }
                $where .= ($input['group_id']<>0) ? "contract.busines_group_id = ". $input['group_id']." AND " : "";
                $where .= ($input['status']<>0) ? "CAST(contract.status AS BIGINT) = ". strval($input['status'])." AND " : "";
            
                $where = trim($where);
                $where = trim($where, 'AND');
                $where = trim($where);      

                $contratos = \DB::table('business_groups')
                    ->select(\DB::raw(" business_groups.id as grupo_id,     
                                        business_groups.ruc as group_ruc, 
                                        business_groups.description as group_description, 
                                        contract.id as id_contract, 
                                        contract.number as number_contract, 
                                        contract.credit_limit, 
                                        contract.date_init, 
                                        contract.date_end, 
                                        contract.status, 
                                        contract.reception_date,
                                        contract_type.description as description_contract_type, 
                                        contract.signature_date as fecha_aprobacion, 
                                        DATE_PART('day', contract.date_end - now()) as restantes"))
                    ->join('contract','contract.busines_group_id','=','business_groups.id')
                    ->join('contract_type','contract_type.id','=','contract.contract_type')
                    ->whereRaw("$where")
                    ->whereNull('business_groups.deleted_at')
                    ->orderBy('contract.number','desc')
                    ->get();
              
                /*Carga datos del formulario*/
                $whereGroup = "";
                $whereAtm ="";
    
                //Redes
                $groups     = Group::orderBy('business_groups.description')->where(function($query) use($whereGroup){
                    if(!empty($whereGroup)){
                        $query->whereRaw($whereGroup);
                    }
                })->get()->pluck('description','id');
                $groups->prepend('Todos','0');
    
                $atms     = Atmnew::orderBy('atms.name')->where(function($query) use($whereAtm){
                    if(!empty($whereAtm)){
                        $query->whereRaw($whereAtm);
                    }
                })->get()->pluck('name','id');
                $atms->prepend('Todos','0');
    
                $group_id = (isset($input['group_id'])?$input['group_id']:0);
                $reservationtime_contract  = (isset($input['reservationtime'])?$input['reservationtime']:'');
                $atm_id  = (isset($input['atm_id'])?$input['atm_id']:0);
                $status  = (isset($input['status'])?$input['status']:0);

       
                return view('contratos.contratos_report', compact('contratos','groups','atms','status','group_id','atm_id','reservationtime_contract'));
    
            }catch (\Exception $e){
                \Log::info($e);
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();    
            } 
        }

        if(isset($input['download'])){
            ini_set('max_execution_time', 300);
            try{
    
                $where = " 1=1 AND ";

                if($input['reservationtime'] <> 'Todos'){
                    /*SET DATE RANGE*/
                    $fecha = explode(' - ',  str_replace('/','-',$input['reservationtime']));
                    $fecha[0] = date('Y-m-d H:i:s', strtotime($fecha[0]));
                    $fecha[1] = date('Y-m-d H:i:s', strtotime($fecha[1]));
                    $where .= "contract.created_at BETWEEN '{$fecha[0]}' AND '{$fecha[1]}' AND ";
                }
                $where .= ($input['group_id']<>0) ? "contract.busines_group_id = ". $input['group_id']." AND " : "";
                // $where .= ($input['atm_id']<>0) ? "atms.id = ". $input['atm_id']." AND " : "";
                $where .= ($input['status']<>0) ? "CAST(contract.status AS BIGINT) = ". strval($input['status'])." AND " : "";
            
                $where = trim($where);
                $where = trim($where, 'AND');
                $where = trim($where);      

                $contratos = \DB::table('business_groups')
                    ->select(\DB::raw(" business_groups.id as grupo_id,     
                                        business_groups.ruc as group_ruc, 
                                        business_groups.description as group_description, 
                                        contract.id as id_contract, 
                                        contract.number as number_contract, 
                                        CASE 
                                            WHEN contract.status = '1'  THEN 'RECEPCIONADO'
                                            WHEN contract.status = '2'  THEN 'ACTIVO'
                                            WHEN contract.status = '3'  THEN 'INACTIVO'
                                            WHEN contract.status = '4'  THEN 'VENCIDO'
                                        END estado,
                                        contract_type.description,
                                        contract.credit_limit, 
                                        contract.date_init, 
                                        contract.date_end, 
                                        contract.reception_date,
                                        contract.signature_date as fecha_aprobacion, 
                                        DATE_PART('day', contract.date_end - now()) as restantes"))
                    ->join('contract','contract.busines_group_id','=','business_groups.id')
                    ->join('contract_type','contract_type.id','=','contract.contract_type')
                    ->whereRaw("$where")
                    ->whereNull('business_groups.deleted_at')
                    ->orderBy('business_groups.description','asc')
                    ->orderBy('contract.date_init','asc')
                    ->get();
    
                   $result = json_decode(json_encode($contratos),true);
                   $filename = 'contratos_'.time();

                   $columna1 = array(
                    'ID GRUPO', 'RUC','NOMBRE','ID CONTRATO','NRO CONTRATO','ESTADO','TIPO','LIMITE DE CREDITO','FECHA DE INICIO - VIGENCICA','FECHA FINALIZACION - VIGENCIA','FECHA DE RECEPCION','FECHA DE APROBACION','DIAS RESTANTES'
                   );
                   
                   if($result){

                        $excel = new ExcelExport($result,$columna1);
                        return Excel::download($excel, $filename . '.xls')->send();
                    //    Excel::create($filename, function($excel) use ($result) {
                    //        $excel->sheet('Página 1', function($sheet) use ($result) {
                    //            $sheet->rows($result,false);
                    //            $sheet->prependRow(array(
                    //                'ID GRUPO', 'RUC','NOMBRE','ID CONTRATO','NRO CONTRATO','ESTADO','TIPO','LIMITE DE CREDITO','FECHA DE INICIO - VIGENCICA','FECHA FINALIZACION - VIGENCIA','FECHA DE RECEPCION','FECHA DE APROBACION','DIAS RESTANTES'
                    //            ));
                    //        });
                    //    })->export('xls');
                    //    exit();
                   }else{
                       Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                       return redirect()->back();   
                   }
       
            }catch (\Exception $e){
                \Log::info($e);
                return false;
            }
        }
        
    }



}
