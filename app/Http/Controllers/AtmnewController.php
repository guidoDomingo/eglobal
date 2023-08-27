<?php

namespace App\Http\Controllers;

use App\Exports\ExcelExport;
use Mail;
use Cache;
use Excel;
use Session;
use DateTime;
use HttpClient;
use ZipArchive;
use Carbon\Carbon;
use App\Models\Atm;
use App\Models\Pos;
use App\Models\Role;
use App\Models\User;
use App\Models\Zona;
use GuzzleHttp\Psr7;
use App\Models\Group;
use App\Models\Owner;
use App\Http\Requests;
use App\Models\Atmnew;
use App\Models\Branch;
use App\Models\Ciudad;
use App\Models\Content;
use App\Models\Housing;
use App\Models\Contract;
use App\Models\Permission;
use App\Models\WebService;
use App\Models\VoucherType;
use Illuminate\Support\Str;
use App\Models\Applications;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\PosSaleVoucher;
use App\Models\HousingHistory;
use App\Models\InsurancePolicy;
use App\Models\NetworkConection;
use App\Models\InactivateHistory;
use PhpParser\Node\Stmt\TryCatch;
use App\Services\AtmStatusServices;
use App\Services\ExtractosServices;
use App\Http\Controllers\Controller;
use App\Models\AtmCredentialsOndanet;
use App\Models\AtmServicesCredentials;
use App\Http\Requests\StoreAtmnewRequest;
use App\Http\Requests\UpdateAtmnewRequest;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AtmnewController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth', ['except' => 'getApplicationInterface']);
        $this->user = \Sentinel::getUser();

        // Gooddeals
        $expiresAt = Carbon::now()->addMinutes(60);
        Cache::forget('webservice_config_gooddeal');
        $WebServiceConfig = Cache::remember('webservice_config_gooddeal', $expiresAt, function () {
            return WebService::with('webservicerequests')->where('api_prefix', 'gooddeal')->first();
        });
        
        $this->url      =  $WebServiceConfig->url;
        $this->serviceId = $WebServiceConfig->id;

        foreach ($WebServiceConfig->webservicerequests as $request) {
            $requests[$request->keyword] = $request->id;
        }

        $this->serviceRequests = $requests;
    }

    public function index(Request $request)
    {

        if (!$this->user->hasAccess('atms_v2')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        //$atms = Atm::all();
        //$owner_id = ($request->has('owner_id')) ? $request->get('owner_id'): 0;
        //$group_id = ($request->has('group_id')) ? $request->get('group_id'): 0;
        //$atms = Atm::filterAndPaginateStatus($name,$id,$owner_id,$group_id);


        $name = $request->get('name');
        $id = $request->get('id');

        $owner_id = $request['owner_id'];
        $group_id = $request['group_id'];
        $record_limit = $request['record_limit'];

        $atms = \DB::table('atms as a')
            ->select(
                'a.id',
                'a.name',
                'a.code',
                'a.atm_status',
                \DB::raw("to_char(a.last_request_at, 'DD/MM/YYYY HH24:MI:SS') as last_request_at"),
                \DB::raw("a.last_request_at as last_request_at_date_time"),
                'a.arqueo_remoto',
                'a.grilla_tradicional',
                'a.compile_version',
                'o.name as owner_name'
            )
            ->join('owners as o', 'o.id', '=', 'a.owner_id');

            \Log::info("NAME: $name");

            if ($name !== null and $name !== '') {
                $atms = $atms->whereRaw("a.name ilike '%$name%'");
            }

            if ($owner_id !== null and $owner_id !== '0') {
                $atms = $atms->whereRaw("a.owner_id = $owner_id");
            }

            if ($group_id !== null and $group_id !== '0') {
                $atms = $atms->whereRaw("a.group_id = $group_id");
            }

            $atms = $atms->whereRaw("a.deleted_at is null");

            $atms = $atms->orderBy('a.name', 'ASC');

            if ($record_limit == '' or $record_limit == 'TODOS') {
                
            } else if ($record_limit !== null) {
                $atms = $atms->take(intval($record_limit));
            }

            //\Log::info("record_limit: " . $record_limit);
            \Log::info("QUERY: " . $atms->toSql());

            $atms = $atms->get();
           

        $owners = Owner::orderBy('name')->get()->pluck('name','id')->toArray();
        $owners[0] = 'Red - Todos';
        ksort($owners);

        $groups = Group::orderBy('description')->get()->pluck('description','id')->toArray();
        $groups[0] = 'Grupo - Todos';
        ksort($groups);

        if($owner_id <> 0) {
            $owner = Owner::where('id',$owner_id)->first();
        }

        if($group_id <> 0) {
            $group = Group::where('id',$group_id)->first();
        }

        if($request->get('download')){
            //dd($request->all());
            if($request->input('download') == 'download'){
                $result = $this->exportAtm($group_id, $owner_id);
                $result = json_decode(json_encode($result),true);
            } 
        }

        foreach ($atms as $atm) {
            $now  = Carbon::now();
            $end =  Carbon::parse($atm->last_request_at_date_time);
            $elasep = $now->diffInMinutes($end);

            $seconds = $elasep;
            $dtF = new DateTime("@0"); 
            $dtT = new DateTime("@$seconds"); 
            $atm->elasep = $dtF->diff($dtT)->format('%a días, %h horas, %i minutos y %s segundos');
        }

        return view('atmnew.index', compact('atms','owners','owner_id','groups','group_id', 'record_limit' ));
    }

    public function create()
    {
        if (!$this->user->hasAccess('atms_v2.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $public_key = Str::random(40);
        $private_key = Str::random(40);

        $owners = Owner::orderBy('name')->get()->pluck('name','id');
        $data = [
            'public_key'    => $public_key,
            'private_key'   => $private_key,
            'owners'        => $owners,

        ];

        return view('atmnew.create', $data);
    }

    public function store(StoreAtmnewRequest $request)
    {
        if (!$this->user->hasAccess('atms_v2.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $atm_code = \DB::table('atms')
            ->selectRaw('code')
            ->orderBy('created_at','desc')
            ->first();

        $atm = new Atm;
        $atm->public_key    = $request->public_key;
        $atm->private_key   = $request->private_key;
        $atm->code          = $atm_code->code+1;
        $atm->owner_id      = $request->owner_id;
        $atm->created_by    = $this->user->id;

        if($atm->owner_id == 16){
            $atm->name = 'Miniterminal - '.$request->name;
        }elseif($atm->owner_id == 21){
            $atm->name = 'Nanoterminal - '.$request->name;
        }
        elseif($atm->owner_id == 25){
            $atm->name = 'FK - '.$request->name;
        }else{
            $atm->name = $request->name;
        }

        //PROGRESO DE CREACION - ABM V2
        $atm->atm_status = -5;

        if($request->ajax()){
            $respuesta = [];
            try{
                if ($atm->save()){
                    $data = [];
                    $data['id'] = $atm->id;
                    $atmName = $atm->name;
                    $atmCode = $atm->code;
                    $ownerId = $request->owner_id;
                    //PROGRESO DE CREACION - ABM V2
                    \Log::info("ABM Version 2, Paso 1 - ATM. Estado de creacion: -5");

                    if($ownerId == 2){
                        $ownerId = 11;
                    }

                    if($ownerId == 16 || $ownerId == 21 || $ownerId == 25){

                        //Insertando Balance_Atms
                        \DB::table('balance_atms')->insert([
                            'atm_id' => $atm->id, 
                            'created_at' => Carbon::now()
                        ]);

                        //Insertar parametro "VentasQR" para las redes de nanos y minis
                        \DB::table('atm_param')->insert([ 
                            ['atm_id' => $atm->id, 'key' => 'ventasQR', 'value' => 'true'] ]); 
                        //Insertar credenciales "BancardQR" para las redes de nanos y minis
                        \DB::table('atm_services_credentials')->insert([ 
                            ['atm_id' => $atm->id,'service_id' => 69,'cnb_service_code' => NULL,'source_id' => NULL,'user' => '272133','password' => '1','created_at' => Carbon::now(),'updated_at' => NULL ]]); 

                        \DB::table('parametros_comisiones')->insert([
                            ['service_id' => 49, 'service_source_id' => 0, 'atm_id' => $atm->id, 'tipo_comision' => 1, 'comision' => 1,'created_at' => Carbon::now(), 'updated_at'=> Carbon::now(), 'tipo_servicio_id' => 1],
                            ['service_id' => 6, 'service_source_id' => 7, 'atm_id' => $atm->id, 'tipo_comision' => 1, 'comision' => 3.5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'tipo_servicio_id'  => 0],
                            ['service_id' => 3, 'service_source_id' => 0, 'atm_id' => $atm->id, 'tipo_comision'=> 1, 'comision' => 3.5, 'created_at' => Carbon::now(), 'updated_at' =>Carbon::now(), 'tipo_servicio_id' => 1],
                            ['service_id' => 10, 'service_source_id' => 7, 'atm_id' => $atm->id, 'tipo_comision' => 1, 'comision' => 3.5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'tipo_servicio_id'  => 0],
                            ['service_id' => 44, 'service_source_id' => 0, 'atm_id' => $atm->id, 'tipo_comision' => 1, 'comision' => 3.5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'tipo_servicio_id'  => 1],
                            ['service_id' => 7, 'service_source_id' => 0, 'atm_id' => $atm->id, 'tipo_comision' => 1, 'comision' => 0.7142, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'tipo_servicio_id' => 1,],
                            ['service_id' => 50, 'service_source_id' => 0, 'atm_id' => $atm->id, 'tipo_comision' => 1, 'comision' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'tipo_servicio_id'  => 1],
                            ['service_id' => 12, 'service_source_id' => 7, 'atm_id' => $atm->id, 'tipo_comision' => 1, 'comision' => 3.5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(),'tipo_servicio_id'  => 0],
                            ['service_id' => 54, 'service_source_id' => 0, 'atm_id' => $atm->id, 'tipo_comision' => 1, 'comision' => 5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'tipo_servicio_id' => 1],
                            ['service_id' => 14, 'service_source_id' => 8, 'atm_id' => $atm->id, 'tipo_comision' => 1, 'comision' => 3.5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'tipo_servicio_id' => 0],
                            ['service_id' => 15, 'service_source_id' => 8, 'atm_id' => $atm->id, 'tipo_comision' => 1, 'comision' => 3.5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'tipo_servicio_id' => 0],
                            ['service_id' => 3, 'service_source_id' => 8, 'atm_id' => $atm->id, 'tipo_comision' => 1, 'comision' => 0.7142, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'tipo_servicio_id' => 0],
                            ['service_id' => 9, 'service_source_id' => 0, 'atm_id' => $atm->id, 'tipo_comision' => 1, 'comision' => 0.75, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'tipo_servicio_id' => 1],
                            ['service_id' => 11, 'service_source_id' => 0, 'atm_id' => $atm->id, 'tipo_comision' => 1, 'comision' => 5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'tipo_servicio_id' => 1],
                            ['service_id' => 5, 'service_source_id' => 7, 'atm_id' => $atm->id, 'tipo_comision' => 1, 'comision' => 0.75, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'tipo_servicio_id' => 0],
                            ['service_id' => 7, 'service_source_id' => 7, 'atm_id' => $atm->id, 'tipo_comision' => 1, 'comision' => 0.75, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'tipo_servicio_id' => 0],
                            ['service_id' => 9, 'service_source_id' => 7, 'atm_id' => $atm->id, 'tipo_comision' => 1, 'comision' => 0.75, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'tipo_servicio_id' => 0],
                            ['service_id' => 11, 'service_source_id' => 7, 'atm_id' => $atm->id, 'tipo_comision' => 1, 'comision' => 0.75, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'tipo_servicio_id' => 0],
                            ['service_id' => 13, 'service_source_id' => 7, 'atm_id' => $atm->id, 'tipo_comision' => 1, 'comision' => 0.75, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'tipo_servicio_id' => 0],
                            ['service_id' => 31, 'service_source_id' => 7, 'atm_id' => $atm->id, 'tipo_comision' => 1, 'comision' => 0.75, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'tipo_servicio_id' => 0],
                            ['service_id' => 80, 'service_source_id' => 7, 'atm_id' => $atm->id, 'tipo_comision' => 1, 'comision' => 0.75, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'tipo_servicio_id' => 0],
                            ['service_id' => 851, 'service_source_id' => 7, 'atm_id' => $atm->id, 'tipo_comision' => 1, 'comision' => 0.75, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'tipo_servicio_id' => 0],
                            ['service_id' => 73, 'service_source_id' => 8, 'atm_id' => $atm->id, 'tipo_comision' => 1, 'comision' => 3.5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'tipo_servicio_id' => 0],
                            ['service_id' => 1, 'service_source_id' => 8, 'atm_id' => $atm->id, 'tipo_comision' => 1, 'comision' => 3.5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'tipo_servicio_id' => 0]
                        ]);

                        if( $request->grilla_completa == 'si' && $ownerId == 16){
                            $insert_atm_param= \DB::table('atm_param')
                                ->insert([ 
                                    ['atm_id' => $atm->id, 
                                    'key'     => 'otrosServicios', 
                                    'value'   => 'true'] 
                                ]); 
                            \Log::info('[ALTAS - ATM_PARAM] insertado otrosServicios');

                           $update_atm= \DB::table('atms')
                                ->where('id',$atm->id)
                                ->update([
                                    'grilla_tradicional' => FALSE,
                                ]);
                            \Log::info('[ALTAS - ATMS] actualizado la grilla tradicional');

                        }
                    }

                    $aplicaciones = Applications::where('active',true)
                        ->get()
                        ->pluck('name','id');

                    $data['applications'] = [];
                    foreach ($aplicaciones as $applicationId => $texto) {
                        $valor                  = [];
                        $valor['id']            = $applicationId;
                        $valor['text']          = $texto;
                        $data['applications'][] = $valor; 
                    }
                    
                    \Log::info("Nuevo atm creado");
                    $respuesta['mensaje']   = 'ATM agregado correctamente';
                    $respuesta['tipo']      = 'success';
                    $respuesta['owner_id']  = $ownerId;
                    $respuesta['atm_name']  = $atmName;
                    $respuesta['atm_code']  = $atmCode;
                    $respuesta['data']      = $data;
                    $respuesta['url']       = route('atmnew.update',[$atm->id]);
                    return $respuesta;
                }
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Error al crear el ATM';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }
        }else{
            if ($atm->save()) {
                $message = 'Agregado correctamente';
                Session::flash('message', $message);
                //create resources directory
                if (!$this->createDirectoryResources($atm->id)) {
                    Session::flash('message', "Directorio de Recursos no creado.");
                }
                return redirect()->route('atmnew.index')->with('guardar','ok');
            } else {
                Session::flash('error_message', 'Error al guardar el registro');
                return redirect()->route('atmnew.index')->with('guardar','ok');
            }
        }
    }

    public function show($id)
    {
        
        // $public_key = Str::random(40);
        // $private_key = Str::random(40);

        // $owners = Owner::orderBy('name')->get()->pluck('name','id');
        // $data = [
        //     'public_key'    => $public_key,
        //     'private_key'   => $private_key,
        //     'owners'        => $owners,
        // ];
        // return view('atmnew.prueba',$data);

    }

    public function edit($id)
    {
        if (!$this->user->hasAccess('atms_v2.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $atm = Atm::find($id);
        ///Obtener branch
        $branch_ini = \DB::table('points_of_sale')
        ->select('branches.id as branch_id')
        ->join('branches','branches.id','=','points_of_sale.branch_id')
        ->where('points_of_sale.atm_id',$id)
        ->first();

        //dd($branch_ini);
        if(empty($branch_ini)){
            $branch_ini = [];
        }
       
        ////AREA DE LEGALES - CONTRATOS
        $grupo = \DB::table('business_groups')
        ->select('business_groups.*', 'branches.id as branch_id')
        ->join('branches','branches.group_id','=','business_groups.id')
        ->join('points_of_sale','points_of_sale.branch_id','=','branches.id')
        ->where('points_of_sale.atm_id',$id)
        ->first();
        if(empty($grupo)){
            $grupo = [];
            $grupo_caracteristica = [];            
        }else{
            // $grupo_caracteristica = \DB::table('caracteristicas')
            // ->select('caracteristicas.correo')
            // ->where('caracteristicas.group_id',$grupo->id)
            // ->first();

          
        }
        $grupo_caracteristica = \DB::table('caracteristicas')
        ->select('caracteristicas.correo')
        ->join('branches','branches.caracteristicas_id','=','caracteristicas.id')
        ->join('points_of_sale','points_of_sale.branch_id','=','branches.id')
        ->where('points_of_sale.atm_id',$id)
        ->first();

        $contract_id = \DB::table('contract')
        ->select('contract.id')
        ->join('business_groups','business_groups.id','=','contract.busines_group_id')
        ->join('branches','branches.group_id','=','business_groups.id')
        ->join('points_of_sale','points_of_sale.branch_id','=','branches.id')
        ->where('points_of_sale.atm_id',$id)
        ->where('contract.number',intval($atm->code))
        ->get();
       
        $contract_id_aux = -1;
        if(count ($contract_id)> 0){
            $contract_id_aux = $contract_id[0]->id;
        }

        $contrato = Contract::where('id',$contract_id_aux )->first();
      
        if(empty($contrato)){
            //$contrato = [];
            $reservationtime = '';
        }else{
            $contract_date_ini = ($contrato->date_init)->format('d-m-Y');
            $contract_date_end = ($contrato->date_end)->format('d-m-Y');
            $reservationtime = $contract_date_ini .' - '.$contract_date_end;
        }


        //// POLIZAS
        $poliza_id = \DB::table('insurance_policy')
        ->select('insurance_policy.id')
        ->join('contract_insurance','contract_insurance.insurance_policy_id','=','insurance_policy.id')
        ->join('contract','contract.id','=','contract_insurance.contract_id')
        ->join('business_groups','business_groups.id','=','contract.busines_group_id')
        ->join('branches','branches.group_id','=','business_groups.id')
        ->join('points_of_sale','points_of_sale.branch_id','=','branches.id')
        ->where('points_of_sale.atm_id',$id)
        ->get();
        $poliza_id_aux = -1;
        if(count ($poliza_id)> 0){
            $poliza_id_aux = $poliza_id[0]->id;
        }
        $poliza = InsurancePolicy::where('id',$poliza_id_aux)->first();
    


        ////AREA CONTABILIDAD
        $pos = Pos::where('atm_id', $id)->first();
        if(empty($pos)){
            $posVoucher = [];
            
        }else{
            $posVoucher = PosSaleVoucher::with('voucherType')->where('point_of_sale_id', $pos->id)->first();
            if(!empty($posVoucher)){
                $posVoucher->valid_from = date('d/m/Y',strtotime($posVoucher->valid_from));
                $posVoucher->valid_until = date('d/m/Y',strtotime($posVoucher->valid_until));
            }
            $branch_internet_contract = Branch::where('id',$pos->branch_id)->first();
            
        }
   
        //AREA DE LOGISTICAS
        $network_id = \DB::table('network_conection')
        ->select('network_conection.id')
        ->join('network_technology','network_technology.id','=','network_conection.network_technology_id')
        ->join('internet_service_contract','internet_service_contract.id','=','network_conection.internet_service_contract_id')
        ->join('isp','isp.id','=','internet_service_contract.isp_id')
        // ->join('branches','branches.internet_service_contract_id','=','internet_service_contract.id')
        ->join('branches','branches.network_connection_id','=','network_conection.id')
        ->join('points_of_sale','points_of_sale.branch_id','=','branches.id')
        ->where('points_of_sale.atm_id',$id)
        ->get();    
        $network_id_aux = -1;
        if(count ($network_id)> 0){
            $network_id_aux = $network_id[0]->id;
        }
        $network = NetworkConection::where('id', $network_id_aux)->first();
        //Housing
        $housings = Housing::leftjoin('atms', 'housing.id', '=', 'atms.housing_id')
                            ->where('atms.id', null)
                            ->whereNull('atms.deleted_at')
                            ->pluck('serialnumber','housing.id');
        $housings->prepend('Asignar housing','0');

        if(!empty($atm->housing_id)){
            $housing_id = $atm->housing_id;
            $housing = Housing::find($housing_id);
            $housings->prepend($housing->serialnumber,$housing_id);
        }else{
            $housing_id = null;
        }

        ////AREA SISTEMAS  EGLOBALT
        $aplicaciones = Applications::where('active',true)
                        ->get()
                        ->pluck('name','id');
        $app = \DB::table('atm_application')->where('atm_id', $atm->id)->first();

        //$appId = null;
        $appId = 0;
        if(!empty($app)){
            $appId = $app->application_id;
        }

        //AREA DE SISTEMAS ANTELL - CREDENCIALES
       
        $credencial_ondanet = AtmCredentialsOndanet::where('atm_id',$id )->first();
        if(empty($credencial_ondanet)){
            $credencial_ondanet = [];
        }


        $vendedores_ondanet=  \DB::table('ondanet_abm')
        ->select('vendedor', 'vendedor_cash','vendedor_descripcion','vendedor_descripcion_cash', 'caja', 'caja_cash','sucursal','sucursal_cash', 'deposito','deposito_cash')
        ->where('atm_id',$id)->get();

        $vendedor_ondanet = '';
        $vendedor_cash_ondanet = '';
        $vendedor_descripcion_ondanet = '';
        $vendedor_descripcion_cash_ondanet = '';
        $caja_ondanet = '';
        $caja_cash_ondanet = '';
        $sucursal_ondanet = '';
        $sucursal_cash_ondanet = '';
        $deposito_ondanet = '';
        $deposito_cash_ondanet = '';
        $branch_internet_contract = '';
     
        if(count ($vendedores_ondanet)> 0){
            $vendedor_ondanet       = $vendedores_ondanet[0]->vendedor;
            $vendedor_cash_ondanet = $vendedores_ondanet[0]->vendedor_cash;

            $vendedor_descripcion_ondanet       = $vendedores_ondanet[0]->vendedor_descripcion;
            $vendedor_descripcion_cash_ondanet = $vendedores_ondanet[0]->vendedor_descripcion_cash;

            $caja_ondanet       = $vendedores_ondanet[0]->caja;
            $caja_cash_ondanet = $vendedores_ondanet[0]->caja_cash;

            $sucursal_ondanet       = $vendedores_ondanet[0]->sucursal;
            $sucursal_cash_ondanet = $vendedores_ondanet[0]->sucursal_cash;

            $deposito_ondanet       = $vendedores_ondanet[0]->deposito;
            $deposito_cash_ondanet = $vendedores_ondanet[0]->deposito_cash;

        }

        //dd($vendedor_descripcion_cash_ondanet);

        //AREA DE FRAUDE ANTELL - CREDENCIALES
        $credencial_id = \DB::table('atm_services_credentials')
        ->select('atm_services_credentials.id')
        ->where('atm_services_credentials.atm_id',$id)
        ->first(); 
        
        // $credencial_id_aux = -1;
        // if(count ($credencial_id)> 0){
        //     $credencial_id_aux = $credencial_id[0]->id;
        // 
        if($credencial_id <> null ){

            $credencial = AtmServicesCredentials::where('id',$credencial_id->id )->first();

            if(empty($credencial)){
                $credencial = [];
            }

             // Credenciales asigandas para el servicio 6 =  Tigo 
            $credencial_id_6 = \DB::table('atm_services_credentials')
            ->select('atm_services_credentials.id')
            ->where('atm_services_credentials.atm_id',$id)
            ->where('atm_services_credentials.service_id',6)
            ->get(); 

            $credencial_id_aux_6 = -1;
            if(count ($credencial_id_6)> 0){
                $credencial_id_aux_6 = $credencial_id_6[0]->id;
            }

            $credencial_6 = AtmServicesCredentials::leftjoin('services', 'atm_services_credentials.service_id','=', 'services.id')
                            ->where('atm_services_credentials.id',$credencial_id_aux_6 )
                            ->first();

                            // Credenciales asigandas para el servicio 9 =  Tigo Money
            $credencial_id_9 = \DB::table('atm_services_credentials')
            ->select('atm_services_credentials.id')
            ->where('atm_services_credentials.atm_id',$id)
            ->where('atm_services_credentials.service_id',9)
            ->get();    
                            
            $credencial_id_aux_9 = -1;
            if(count ($credencial_id_9)> 0){
                $credencial_id_aux_9 = $credencial_id_9[0]->id;
            }
            $credencial_9 = AtmServicesCredentials::leftjoin('services', 'atm_services_credentials.service_id','=', 'services.id')
                            ->where('atm_services_credentials.id',$credencial_id_aux_9 )
                            ->first();

        }else{
            $credencial = [];
            $credencial_6 = [];
            $credencial_9 = [];
        }

         //AREA DE EGLOBAL - POS BOX
       
        $records_list = \DB::table('atms as a')
            ->select(
                'bg.description as bg_description',
                'b.description as b_description',
                'pos.description as pos_description',
                'a.name as a_description',
                'a.id as a_id',
                'pb.id as pb_id',
                \DB::raw("case when pb.id is not null then 'Si' else 'No' end as box"),
                \DB::raw("case when pb.status = true then 'Activo' else 'Inactivo' end as status"),
                \DB::raw("coalesce(to_char(pb.created_at, 'DD/MM/YYYY HH24:MI:SS'), '') as created_at")
            )
            ->join('points_of_sale as pos', 'a.id', '=', 'pos.atm_id')
            ->join('branches as b', 'b.id', '=', 'pos.branch_id')
            ->join('business_groups as bg', 'bg.id', '=', 'b.group_id')
            ->leftjoin('pos_box as pb', 'a.id', '=', 'pb.atm_id')
            ->where('a.id',$id)
            ->get();

        $posbox_status = 'No';

        if(count ($records_list)> 0){
            $posbox_status = $records_list[0]->box;
        }
        //POSBOX TURN
        $turnos1 = \DB::table('pos_box as pb')
        ->select(\DB::raw("case when pos_turn.turn_id is not null then 'Si' else 'No' end as turno_1"))
        ->join('pos_box_turn as pos_turn', 'pos_turn.pos_box_id', '=', 'pb.id')
        ->where('pb.atm_id',$id)
        ->where('pos_turn.turn_id',1)
        ->get();
        $turno_1 = 'No';
        if(count ($turnos1)> 0){
            $turno_1 = $turnos1[0]->turno_1;
        }

        $turnos2 = \DB::table('pos_box as pb')
        ->select(\DB::raw("case when pos_turn.turn_id is not null then 'Si' else 'No' end as turno_2"))
        ->join('pos_box_turn as pos_turn', 'pos_turn.pos_box_id', '=', 'pb.id')
        ->where('pb.atm_id',$id)
        ->where('pos_turn.turn_id',2)
        ->get();
        $turno_2 = 'No';
        if(count ($turnos2)> 0){
            $turno_2 = $turnos2[0]->turno_2;
        }

        $turnos3 = \DB::table('pos_box as pb')
        ->select(\DB::raw("case when pos_turn.turn_id is not null then 'Si' else 'No' end as turno_3"))
        ->join('pos_box_turn as pos_turn', 'pos_turn.pos_box_id', '=', 'pb.id')
        ->where('pb.atm_id',$id)
        ->where('pos_turn.turn_id',3)
        ->get();
        $turno_3 = 'No';
        if(count ($turnos3)> 0){
            $turno_3 = $turnos3[0]->turno_3;
        }

        $turnos4 = \DB::table('pos_box as pb')
        ->select(\DB::raw("case when pos_turn.turn_id is not null then 'Si' else 'No' end as turno_4"))
        ->join('pos_box_turn as pos_turn', 'pos_turn.pos_box_id', '=', 'pb.id')
        ->where('pb.atm_id',$id)
        ->where('pos_turn.turn_id',4)
        ->get();
        $turno_4 = 'No';
        if(count ($turnos4)> 0){
            $turno_4 = $turnos4[0]->turno_4;
        }



        $webservices                    = WebService::all()->pluck('name', 'id');
        $atm_parts                      = \DB::table('atms_parts')->where('atm_id', $atm->id)->count();
        $owners                         = Owner::orderBy('name')->get()->pluck('name','id')->toArray();
        $branches                       = Branch::pluck('description', 'id')->toArray();
        $groups                         = Group::pluck('description', 'id')->toArray();
        $sellerType['1']                = 'Testing Seller type';
        $users                          = User::all()->pluck('description','id')->prepend('Asignar usuario','0')->toArray();
        $user_id                        = 0;
        $voucherTypes                   = VoucherType::orderBy('id')->get()->pluck('description','id')->toArray();
        $departamentos                  = \DB::table('departamento')->pluck('descripcion','id')->toArray();
        $ciudades                       = \DB::table('ciudades')->pluck('descripcion','id')->toArray();
        $barrios                        = \DB::table('barrios')->pluck('descripcion','id')->toArray();
        $zonas                          = Zona::pluck('descripcion', 'id')->toArray();
        $contract_types                 = \DB::table('contract_type')->pluck('description','id')->toArray();
        $insurance_types                = \DB::table('insurance_type')->pluck('description','id')->toArray();
        $internet_service_contracts     = \DB::table('internet_service_contract')->pluck('isp_acount_number','id')->toArray();
        $network_technologies           = \DB::table('network_technology')->pluck('description','id')->toArray();
        $isp_types                      = \DB::table('isp')->pluck('description','id')->toArray();
        $contracts                      = \DB::table('contract')->pluck('number','id')->toArray();
        $insurances                     = \DB::table('insurance_policy')->pluck('number','id')->toArray();
        
        $permissions = Permission::orderBy('permission')->get();
        $branches2 = Branch::all(['description', 'id']);
        $branchJson = json_encode($branches2);
        $rolesList = Role::all(['id', 'name']);
        $rolesJson = json_encode($rolesList);
        $owners2 = Owner::all(['name', 'id']);
        $ownersJson = json_encode($owners2);

        $bancos                         = \DB::table('clientes_bancos')->pluck('descripcion','id')->toArray();
        $banco_id = 0;
        
        $tipo_cuentas                   = \DB::table('clientes_tipo_cuenta')->pluck('descripcion','id')->toArray();
        $tipo_cuentas_id = 0;

        $canales                          = \DB::table('canal')->pluck('descripcion','id')->toArray();
        $canal_id = 0;
        $categorias                     = \DB::table('categorias')->pluck('descripcion','id')->toArray();
        $categoria_id = 0;
        $caracteristicas                  = array( 'Agregar caracteristica');
        $responsables                     = User::where('manager_eglobalt', true)->pluck('description','id')->toArray();

        $data = array(
            'atm'                           => $atm,
            'pointofsale'                   => $pos,
            'posVoucher'                    => $posVoucher,
            'aplicaciones'                  => $aplicaciones,
            'app_id'                        => $appId,
            'atm_parts'                     => $atm_parts,
            'auth_token'                    => $atm->auth_token,
            'owners'                        => $owners,
            'branches'                      => $branches,
            'groups'                        => $groups,
            'grupo'                         => $grupo,
            'departamentos'                 => $departamentos,
            'ciudades'                      => $ciudades,
            'barrios'                       => $barrios,
            'zonas'                         => $zonas,
            'branch_id'                     => $branch_ini,
            'contrato'                      => $contrato,
            'poliza'                        => $poliza,
            'contract_types'                => $contract_types,
            'network'                       =>  $network,          
            'contracts'                     => $contracts,
            'insurances'                    => $insurances,
            'insurance_types'               => $insurance_types,
            'branch_internet_contract'      => $branch_internet_contract,
            'internet_service_contracts'    => $internet_service_contracts,
            'network_technologies'          => $network_technologies,
            'isp_types'                     => $isp_types,
            'housings'                      => $housings,
            'housing_id'                    => $housing_id,
            'credencial_ondanet'            => $credencial_ondanet,
            'credencial'                    => $credencial,
            'credencial_6'                  => $credencial_6,
            'credencial_9'                  => $credencial_9,
            'ondanet_seller_types'          => $sellerType,
            'selected_seller_type'          => null,
            'selected_branch'               => null,
            'selected_group'                => null,
            'users'                         => $users,
            'user_id'                       => $user_id,
            'voucherTypes'                  => $voucherTypes,
            'webservices'                   => $webservices,
            'reservationtime_contract'      => $reservationtime,
            'posbox_status'                 => $posbox_status,
            'permissions'                   => $permissions,
            'branchJson'                    => $branchJson,
            'rolesJson'                     => $rolesJson,
            'ownersJson'                    => $ownersJson,

            'vendedor_ondanet'              => $vendedor_ondanet,
            'vendedor_cash_ondanet'         => $vendedor_cash_ondanet,
            'vendedor_descripcion_ondanet'  => $vendedor_descripcion_ondanet,
            'vendedor_descripcion_cash_ondanet' => $vendedor_descripcion_cash_ondanet,
            'caja_ondanet'                  => $caja_ondanet,
            'caja_cash_ondanet'             => $caja_cash_ondanet,
            'sucursal_ondanet'              => $sucursal_ondanet,
            'sucursal_cash_ondanet'         => $sucursal_cash_ondanet,
            'deposito_ondanet'              => $deposito_ondanet,
            'deposito_cash_ondanet'         => $deposito_cash_ondanet,
            'turno_1'                       => $turno_1,
            'turno_2'                       => $turno_2,
            'turno_3'                       => $turno_3,
            'turno_4'                       => $turno_4,
            'bancos'                        => $bancos,
            'banco_id'                      => $banco_id,
            'tipo_cuentas'                  => $tipo_cuentas,
            'tipo_cuentas_id'               => $tipo_cuentas_id,
            'canales'                       => $canales,
            'canal_id'                      => $canal_id,
            'categorias'                    => $categorias,
            'categoria_id'                  => $categoria_id,
            'caracteristicas'               => $caracteristicas,
            'responsables'                  => $responsables,
            'grupo_caracteristica'          => $grupo_caracteristica
        );
 
        return view('atmnew.edit_form_step', $data);
    }

    public function update(UpdateAtmnewRequest $request, $id)
    {
        if (!$this->user->hasAccess('atms_v2.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $atm = Atm::find($id);

        if (!$atm) {
            \Log::warning("Atm not found");
            return redirect()->back()->with('error', 'Cajero no valido');
        }

        $atm->public_key = $request->public_key;
        $atm->private_key = $request->private_key;
        $atm->code = $request->code;
        //$atm->name = $request->name;
        $atm->owner_id = $request->owner_id;
        //$atm->group_id = $request->group_id;
        
        //PROGRESO DE CREACION - ABM V2
        $atm->atm_status = -5;

        //Añadir el prefijo de la red
        // $redes = \DB::table('owners')
        //     ->select('name')
        //     ->where('id', $request->owner_id )
        //     ->get();

        // foreach ($redes as $red) {
        //     $atm->name =  $red->name .' - '. $request->name ;
        // }


        if($request->ajax()){

            $respuesta = [];
            try{
                $dataAnterior = Atm::find($id);
    
                if ($atm->save()){
                    $data = [];
                    $data['id'] = $atm->id;
                    \Log::info("ABM Version 2, Paso 1 - ATM. Estado de actualizacion: -5");

                    if($dataAnterior->owner_id !== $request->owner_id){
                        $pos = Pos::where('atm_id', $id)->first();
                        if(!empty($pos)){
                            \DB::table('points_of_sale')
                                ->where('atm_id',$id)
                                ->update([
                                    'owner_id' => $request->owner_id,
                                ]);
                        }
                    }

                    $aplicaciones = Applications::where('active',true)
                        ->get()
                        ->pluck('name','id');

                    $data['applications'] = [];
                    foreach ($aplicaciones as $applicationId => $texto) {
                        $valor = [];
                        $valor['id'] = $applicationId;
                        $valor['text'] = $texto;
                        $data['applications'][] = $valor; 
                    }

                    
                    \Log::info("Atm actualizado correctamente");
                    $respuesta['mensaje'] = 'ATM Actualizado correctamente';
                    $respuesta['tipo'] = 'success';
                    $respuesta['data'] = $data;
                    $respuesta['url'] = route('atmnew.update',[$atm->id]);
                    return $respuesta;
                }
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Error al actualizar ATM';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }
        }else{
            if (!$atm->save()) {
                \Log::warning("Error updating the Atm data. Id: {$atm->id}");
                Session::flash('message', 'Error al actualziar el registro');
                return redirect('atmnew');
            }
            $message = 'Actualizado correctamente';
            Session::flash('message', $message);
            return redirect('atmnew');
        }

    }

    public function destroy($id)
    {
        if (!$this->user->hasAccess('atms_v2.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $message = '';
        $error = '';
        if ($atm = Atm::find($id)) {
            try {
                if (Atm::destroy($id)) {
                    $message = 'Cajero eliminado correctamente';
                    $error = false;
                }
            } catch (\Exception $e) {
                \Log::error("Error deleting atm: " . $e->getMessage());
                $message = 'Error al intentar eliminar el cajero';
                $error = true;
            }
        }else{
            \Log::warning("Atm {$id} not found");
            $message =  'Cajero no encontrado';
            $error = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }

    /**
     * Eliminar un atm.
     */
    public function delete($id)
    {
        if (!$this->user->hasAccess('atms_v2.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $message = '';
        $type = '';

        try {
            \DB::table('atms')
                ->where('id', $id)
                ->update([
                    'deleted_at' => Carbon::now()
                ]);

                \Log::info("ATM con id: $id con soft-delete.");
        } catch (\Exception $e) {
            \Log::error("Error deleting atm: " . $e->getMessage());
            $message = 'Error al intentar eliminar el cajero';
        }

        if ($message !== '') {
            $type = 'error';
        } else {
            $type = 'message';
        }

        Session::flash($type, $message);

        return redirect('atmnew');
    }

    public function generateHash()
    {
        return Str::random(40);
    }

    protected function createDirectoryResources($id)
    {
        $nuevoPath = public_path('resources/' . $id);
        return is_dir($nuevoPath) || mkdir($nuevoPath);
    }

    public function getApplicationInterface($id,Request $request)
    {
        
        $secret =  $request->headers->get('api-key');        
        $key = env('APP_SHA1');
        if($secret !== $key)
        {
            $response['error']      = true;
            $response['message']    = 'Not found - Unauthorized access  attempt';
            $response['message_user']    = 'Not found';
            \Log::warning($response);
            return $response;
        }
        
        $atm = Atm::find($id);        
        
        
        if($atm->type <> 'da')
        {
            $app = Atm::find($id)->activeApplication();
            $application =  Applications::find($app->id);

            //$screens = \DB::table('screens')->where('application_id',$app->id)
            $screens = \DB::table('screens')
            ->where('application_id',1)
            ->orWhere('application_id',$app->id)
            ->orWhere('application_id',null)->orderBy('id', 'asc')->get();

            foreach ($screens as $screen){
                $screens_desc["id"]     = $screen->id;
                $screens_desc["name"]   = $screen->name;
                $screens_desc["screen_type"]    = $screen->screen_type;
                $screens_desc["description"]    = $screen->description;
                $screens_desc["version_hash"]   = $screen->version_hash;
                $screens_desc["refresh_time"]   = $screen->refresh_time;
                $screens_desc["application_id"] = $screen->application_id;
                $screens_desc["template"] = $screen->template;
                $screens_desc["service_provider_id"] = $screen->service_provider_id;
                $screens_desc["objects"] = [];

                $screens_objects = \DB::table('screen_objects')
                    ->select('screen_objects.id','screen_objects.name','location_x','location_y','version_hash','screen_id','object_type_id','key')
                    ->join('object_types','screen_objects.object_type_id','=','object_types.id')
                    ->where('screen_id',$screen->id)
                    ->orderBy('id', 'asc')
                    ->get();

                $screens_object_desc = [];
                foreach($screens_objects as $screens_object){
                    $screens_object_desc["id"] = $screens_object->id;
                    $screens_object_desc["name"] = $screens_object->name;
                    $screens_object_desc["location_x"] = $screens_object->location_x;
                    $screens_object_desc["location_y"] = $screens_object->location_y;
                    $screens_object_desc["version_hash"] = $screens_object->version_hash;
                    $screens_object_desc["screen_id"] = $screens_object->screen_id;
                    $screens_object_desc["object_type_id"] = $screens_object->object_type_id;
                    $screens_object_desc["object_type_key"] = $screens_object->key;
                    $screens_object_desc["properties"] = [];


                    $object_properties = \DB::table('object_properties_values')
                        ->where('screen_object_id', $screens_object->id)
                        ->get();
                    $object_properties_desc = [];
                    foreach($object_properties as $object_property){
                        $object_properties_desc["key"] = $object_property->key;
                        $object_properties_desc["value"] = $object_property->value;
                        $object_properties_desc["object_property_id"] = $object_property->object_property_id;
                        $screens_object_desc["properties"][] = $object_properties_desc;
                    }
                    $screens_desc["objects"][] = $screens_object_desc;


                }


                $pantallas[] = $screens_desc;
            }
        }else{
            $pantallas = [];
        }

        

        //OBTENER DATOS PDV
        $pdv = \DB::table('points_of_sale')->select('description')->where('atm_id',$atm->id)->first();        
        $application['atm_public_key'] = $atm->public_key;
        $application['atm_private_key'] = $atm->private_key;
        $application['pdv'] = "$pdv->description";
        $application['screens'] = $pantallas;

        $message = "No existe una aplicación activa para el ATM";
        if ($application) {
            $response = [
                'error' => "false",
                'data' => $application,
            ];

        } else {
            $response = [
                'error' => "true",
                'message' => $message,
            ];
        }

        return response()->json($response);
    }

    public function prueba(){
        $public_key = Str::random(40);
        $private_key = Str::random(40);

        $owners = Owner::orderBy('name')->get()->pluck('name','id');
        $branches = Branch::pluck('description', 'id');
        // TODO get seller type fron ONDANET
        $sellerType['1'] = 'Testing Seller type';

        $users = User::all()->pluck('description','id');
        $users->prepend('Asignar usuario','0');
        $user_id = 0;

        $groups = Group::pluck('description', 'id','ruc');

        $grupo = [];

        $voucherTypes = VoucherType::orderBy('id')->get()->pluck('description','id');

        $atm_code = \DB::table('atms')
            ->selectRaw('code')
            ->orderBy('created_at','desc')
            ->first();        
        $departamentos = \DB::table('departamento')->pluck('descripcion','id');

        $data = [
            //step new atm
            'public_key'    => $public_key,
            'private_key'   => $private_key,
            'owners'        => $owners,
            'atm_code' => $atm_code->code+=1,
            //step new pos
            'branches' => $branches,
            'groups'        => $groups,
            'grupo'        => $grupo,
            'ondanet_seller_types' => $sellerType,
            'selected_seller_type' => null,
            'selected_branch' => null,
            'selected_group' => null,
            // step new branch modal
            'users' => $users,
            'user_id' => $user_id,
            'departamentos' => $departamentos,
            // step new voucher
            'voucherTypes' => $voucherTypes,
            'atm_parts' => 0,
        ];        
        return view('atmnew.prueba', $data);

    }



    public function formStep(){

        if (!$this->user->hasAccess('atms_v2.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $public_key                     = Str::random(40);
        $private_key                    = Str::random(40);
        $owners                         = Owner::orderBy('name')->get()->pluck('name','id');
        $branches                       = Branch::pluck('description', 'id');
        $sellerType['1']                = 'Testing Seller type';
        $users                          = User::all()->pluck('description','id');
        $users->prepend('Asignar usuario','0');
        $user_id                        = 0;
        $groups                         = Group::pluck('description', 'id','ruc');
        $grupo                          = [];
        $voucherTypes                   = VoucherType::orderBy('id')->get()->pluck('description','id');
        $atm_code                       = \DB::table('atms')->selectRaw('code')->orderBy('created_at','desc')->first();        
        $departamentos                  = \DB::table('departamento')->pluck('descripcion','id');
        //$ciudades                       = \DB::table('ciudades')->pluck('descripcion','id');
        $ciudades                       = array( '');
        //$barrios                        = \DB::table('barrios')->pluck('descripcion','id');
        $barrios                        = array( '');
        //$zonas                          = Zona::where('deleted_at',null)->pluck('descripcion', 'id');
        $zonas                          = array( '');

        $contract_types                 = \DB::table('contract_type')->pluck('description','id');
        $insurance_types                = \DB::table('insurance_type')->pluck('description','id');
        $internet_service_contracts     = \DB::table('internet_service_contract')->pluck('isp_acount_number','id');
        $network_technologies           = \DB::table('network_technology')->pluck('description','id');
        $isp_types                      = \DB::table('isp')->pluck('description','id');
        $housings                       = Housing::leftjoin('atms', 'housing.id', '=', 'atms.housing_id')
                                        ->where('atms.id', null)
                                        ->whereNull('atms.deleted_at')
                                        ->pluck('serialnumber','housing.id');
        $housings->prepend('Asignar housing','0');
        $contracts                      = \DB::table('contract')->pluck('number','id');
        $insurances                     = \DB::table('insurance_policy')->pluck('number','id');
        $webservices                    = WebService::all()->pluck('name', 'id');
        $posbox_status                  = 'No';

        $permissions = Permission::orderBy('permission')->get();
        $branches2 = Branch::all(['description', 'id']);
        $branchJson = json_encode($branches2);
        $rolesList = Role::all(['id', 'name']);
        $rolesJson = json_encode($rolesList);
        $owners2 = Owner::all(['name', 'id']);
        $ownersJson = json_encode($owners2);
       
        $bancos                         = \DB::table('clientes_bancos')->pluck('descripcion','id');
        $banco_id = 0;
        $tipo_cuentas                   = \DB::table('clientes_tipo_cuenta')->pluck('descripcion','id');
        $tipo_cuentas_id = 0;
        $canales                        = \DB::table('canal')->pluck('descripcion','id');
        $canal_id = 0;
        $categorias                     = \DB::table('categorias')->pluck('descripcion','id');
        $categoria_id = 0;
        //$caracteristicas                  = \DB::table('departamento')->pluck('descripcion','id');
        $caracteristicas                  = array( '');
        $responsables                     = User::where('manager_eglobalt', true)->pluck('description','id');
        $related_id                     = 0;
        $grupo_caracteristica           = [];
        $data = [
            'public_key'                    => $public_key,
            'private_key'                   => $private_key,
            'owners'                        => $owners,
            'atm_code'                      => $atm_code->code+=1,
            'branches'                      => $branches,
            'groups'                        => $groups,
            'grupo'                         => $grupo,
            'ondanet_seller_types'          => $sellerType,
            'contracts'                     => $contracts,
            'insurances'                    => $insurances,
            'insurance_types'               => $insurance_types,
            'contract_types'                => $contract_types,
            'selected_seller_type'          => null,
            'selected_branch'               => null,
            'selected_group'                => null,
            'selected_zona'                 => null,
            'users'                         => $users,
            'user_id'                       => $user_id,
            'departamentos'                 => $departamentos,
            'ciudades'                      => $ciudades,
            'barrios'                       => $barrios,
            'zonas'                         => $zonas,
            'internet_service_contracts'    => $internet_service_contracts,
            'network_technologies'          => $network_technologies,
            'isp_types'                     => $isp_types,
            'housings'                      => $housings,
            'voucherTypes'                  => $voucherTypes,
            'atm_parts'                     => 0,
            'webservices'                   => $webservices,
            'posbox_status'                 => $posbox_status,
            'permissions'                   => $permissions,
            'branchJson'                    => $branchJson,
            'rolesJson'                     => $rolesJson,
            'ownersJson'                    => $ownersJson,
            'turno_1'                       =>'No',
            'turno_2'                       =>'No',
            'turno_3'                       =>'No',
            'turno_4'                       =>'No',
            'bancos'                        =>$bancos,
            'banco_id'                      =>$banco_id,
            'tipo_cuentas'                  =>$tipo_cuentas,
            'tipo_cuentas_id'               =>$tipo_cuentas_id,
            'canales'                       =>$canales,
            'canal_id'                      =>$canal_id,
            'categorias'                    =>$categorias,
            'categoria_id'                  =>$categoria_id,
            'caracteristicas'               =>$caracteristicas,
            'related_id'                    =>$related_id,
            'responsables'                  =>$responsables,
            'grupo_caracteristica'          =>$grupo_caracteristica,
            'reservationtime_contract'      => ''

        ];
        return view('atmnew.form_step_new', $data);
    }

 
    public function checkCode(Request $request){
        if($request->ajax()){
            $parametros = $request;
            $data = \DB::table('atms')->where(function($query) use($parametros){
                $query->where('code',$parametros->get('code'));
                if($parametros->get('id') != null){
                    $query->where('id','<>',$parametros->get('id'));
                }
            })->count();

            if($data < 1){
                $valido = "true";
            }else{
                $valido = "false";
            }

            return $valido;
        }
    }


    public function params($atmId,Request $request){
        //
        if (!$this->user->hasAccess('atms_v2.params')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $where = "atm_id = ".$atmId;
        if($request->get('name')){
           $where .= " AND atm_param.key LIKE '%". $request->get('name') ."%'";
        }

        $params = \DB::table('atm_param')
            ->whereRaw($where)
            ->paginate(20);
        
        $index = "";

        return view('atmnew.params_list', compact('atmId','params','index'));
    }

    public function paramStore($atmId, Request $request)
    {
        if (!$this->user->hasAccess('atms.param_store')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        \DB::beginTransaction();
        
        try{
            foreach($request->key as $index => $key){
                $result = \DB::table('atm_param')
                    ->where('key','=',$key)
                    ->where('atm_id','=',$atmId)
                    ->count();
                
                if($result > 0){
                    \DB::table('atm_param')
                        ->where('key', $key)
                        ->where('atm_id', $atmId)
                        ->update([
                            'value' => $request->value[$index]
                        ]);
                }else{
                    \DB::insert('insert into atm_param (atm_id, key, value) values (?, ?, ?)', [$atmId, $key, $request->value[$index]]);
                }
            }
            \DB::commit();
            \Log::info("Parametros agregados correctamente");
            
            Session::flash('message', 'Parametros agregados correctamente');
            return redirect()->route('atmnew.params',$atmId);
        }catch(\Exception $e){
            \DB::rollback();
            \Log::warning($e);
            Session::flash('error_message', 'No se ha podido realizar la operacion');
            return redirect()->route('atmnew.params',$atmId);
        }
    }


    public function checkKey(Request $request, $atmId){
        if($request->ajax()){
            $data = \DB::table('atm_param')
                ->where('key',$request->key)
                ->where('atm_id',$atmId)
                ->count();
            if($data < 1){
                $valido = "true";
            }else{
                $valido = "false";
            }

            return $valido;
        }
    }

 
    public function parts($atmId,Request $request){
        //
        if (!$this->user->hasAccess('atms_v2.parts')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $where = "atm_id = ".$atmId;
        if($request->get('name')){
           $where .= " AND atms_parts.nombre_parte LIKE '%". $request->get('name') ."%'";
        }

        $parts = \DB::table('atms_parts')
            ->whereRaw($where)
            ->orderBy('tipo_partes','asc')
            ->orderBy('nombre_parte','asc')
            ->paginate(20);
        $index = "";

        return view('atmnew.parts_list', compact('atmId','parts','index'));
    }

    public function partsUpdate($atmId, Request $request)
    {
        if (!$this->user->hasAccess('atms.parts_update')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        // dd($request->all());
        \DB::beginTransaction();
        
        try{
            foreach($request->denominacion as $index => $denominacion){
                \Log::info((isset($request->activo[$index])) ? $request->activo[$index] : false);
                \DB::table('atms_parts')
                    ->where('id', $request->id[$index])
                    ->update([
                        'denominacion' => $denominacion,
                        'cantidad_minima' => $request->cantidad_minima[$index],
                        'cantidad_alarma' => $request->cantidad_alarma[$index],
                        'cantidad_maxima' => $request->cantidad_maxima[$index],
                        'activo' => (isset($request->activo[$index])) ? $request->activo[$index] : false
                    ]);
            }
            \DB::commit();
            \Log::info("Partes actualizadas correctamente");
            
            Session::flash('message', 'Partes actualizadas correctamente');
            return redirect()->route('atmnew.parts',$atmId);
        }catch(\Exception $e){
            \DB::rollback();
            \Log::warning($e);
            Session::flash('error_message', 'No se ha podido realizar la operacion');
            return redirect()->route('atmnew.parts',$atmId);
        }
    }

    public function updateGooddeals(Request $request){
        if (!$this->user->hasAccess('atms.update_gooddeal')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $datosExistentes = \DB::table('params')
            ->where('key','=','gooddeals')
            ->first();

        $instancias = \DB::table('promotions_instances')->pluck('description','key');

        $data = [
            'instancias' => $instancias,
        ];

        return view('atmnew.update_gooddeals')->with('data', $data);
    }

 
    public function lastUpdateGooddeals(Request $request){
        if (!$this->user->hasAccess('atms.update_gooddeal')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $tmp_file = '';

        \DB::beginTransaction();
        try {

            $fecha = date('Y-m-d', strtotime(str_replace('/', '-', $request->last_update))).' 00:00:00';

            $session_id = '';
            $last_update = $fecha;//Carbon::now()->startOfMonth()->format('m/d/Y');
            $channel = 1;
            $index = 0;
            $range = 100;
            $version = 'null';
            $atm_id = null;
            $store_reference = '';


            $atms = \DB::connection('eglobalt_pro')->table('atm_services_credentials')
                ->where('service_id',24)
                ->where('password', $request->instancia)
                ->where('source_id',null)
                ->get();

            $atm_success_count = 0;
            $atm_fail_count = 0;

            // Create new zip archive
            $zip = new ZipArchive();
            $tmp_file = '../public/Goodeals/imagenes_cupones.zip';
            $zip->open($tmp_file, ZipArchive::CREATE);

            if(empty($atms)){
                \Log::info('GoodDeals | no existen atms para esta instancia | Info ');
                Session::flash('message', 'No existen atms para esta instancia');
                $tmp_file = null;

                return redirect()->route('gooddeals.update')->with(['tmp_file' =>$tmp_file]);
            }

            foreach ($atms as $atm){                
                $responsePromotions = $this->getInboxPromotionsv2($session_id,$last_update,$channel,$index,$range,$version,$atm->atm_id);

                $responseImages = $this->getInboxPromotionsWithImages($session_id,$last_update,$channel,$store_reference,$index,$range,$version, $atm->atm_id, $zip);

                if($responsePromotions == false && $responseImages == false){
                    $atm_fail_count++;
                    \Log::info('GoodDeals - Hubo un fallo en la descarga de promociones | Atm_id '.$atm->atm_id.' | error_promociones: '.$responsePromotions.' - error_imagenes: '.$responseImages);
                }else{
                    $atm_success_count++;
                    \Log::info('GoodDeals - Promociones actualizadas para Atm_id '.$atm->atm_id);
                }
            }
            
            \Log::info('GoodDeals | proceso de descarga de promociones culminado | Exitosos '. $atm_success_count.' con error '. $atm_fail_count);

            // Se registra la nueva fecha en la base de datos
            $datoExistente = \DB::table('params')
                ->where('key','=','gooddeals')
                ->first();

            \DB::table('promotions_instances')
                ->where('key', '=', $request->instancia)
                ->update([
                    'last_update' => $fecha,
                ]);

            \DB::commit();
            $zip->close();

            $user_email = 'sistemas@eglobal.com.py';
            $user_name = 'Admin';

            $data = [];
            $data['user_email'] = $user_email;
            $data['user_name'] = $user_name;
            $data['body'] = 'Las promociones fueron actualizadas correctamente.';

            if(file_exists($tmp_file)){
                $data['body'] = 'Las promociones fueron actualizadas correctamente, no hay imagenes para descargar.';
                Mail::send('mails.gooddeal_alert', $data, function ($message) use($user_name, $user_email,$tmp_file){
                    $message->to($user_email, $user_name)                    
                    ->cc('operaciones@eglobal.com.py')
                    ->subject('[GoodDeals] Promociones Actualizadas')
                    ->attach($tmp_file);
                    //$message->to($user_email, $user_name)->subject('[EGLOBAL - DESARROLLO] Alertas del sistema');
                });
            }
        } catch (Exception $e) {
            \DB::rollback();
            \Log::warning($e);
            $user_email = 'sistemas@eglobal.com.py';            
            $user_name = 'Admin';

            $data = [];
            $data['user_email'] = $user_email;
            $data['user_name'] = $user_name;
            $data['body'] = 'Error al actualizar las promociones <br> '.$e;
            Mail::send('mails.gooddeal_alert', $data, function ($message) use($user_name, $user_email,$tmp_file){
                $message->to($user_email, $user_name)                
                ->cc('operaciones@eglobal.com.py')
                ->subject('[GoodDeals] Error al actualizar las promociones ');
                //$message->to($user_email, $user_name)->subject('[EGLOBAL - DESARROLLO] Alertas del sistema');
            });
        }

        return redirect()->route('gooddeals.update')->with(['tmp_file' =>$tmp_file]);
    }

    /*
     * Returns a response containing the coupons assigned to the shopper with the corresponding promotion image.
     */
    public function getInboxPromotionsv2($session_id, $last_update, $channel, $index, $range, $version,$atm_id){
        $endpoint = "GetInboxPromotions";
        $urlget = $this->url.$endpoint;

        $store_credentials = \DB::connection('eglobalt_pro')->table('atm_services_credentials')->where('atm_id', $atm_id)->where('service_id', 24)->first();
        $store_identity = $store_credentials->user;
        $session_id     = $store_credentials->codEntity;
        $store_data = explode('-',$store_credentials->password);
        $store_tag      =   $store_data[1];
        $store_instance =   $store_data[0];
        $urlget = str_replace('[emblema]',$store_instance,$urlget);
        
        try{            
            $petition = HttpClient::post(
                $urlget, [
                    'json' => [
                        'Session' => $session_id,
                        'LastUpdate' => $last_update,
                        'Channel' => $channel,
                        'StoreReference' => "null",
                        'Index' => $index,
                        'Range' => $range,
                        'Version' => $version
                    ],'connect_timeout' => 240
                ]
            );

            $api_response = json_decode($petition->getBody()->getContents()); 

            //Check for errors in the API Response
            $error_code = $api_response->ErrorCode;

            if ($error_code <> 0){
                $error_description = $api_response->ErrorDescription;
                $response_msg = $error_description;
                $response_msg_user = "No se pudo procesar la operación";
                $response = $this->errorData($response_msg,$response_msg_user);
                \Log::warning('[gooddeal]'.$store_identity." Atm_id ". $atm_id ."| Error ".$error_code.' - '.$error_description);
                return false;
            }

            $coupons = $api_response->Coupons;
            $cupones = array();
            $today =  (new \DateTime())->format('Y-m-d');            
            foreach($coupons as $coupon){
                $start = explode(" ",$coupon->StartingDate);
                $start = str_replace('/','-',$start[0]);
                $start = (new \DateTime($start))->format('Y-m-d');
                $expiration = explode(" ",$coupon->ExpirationDate);
                $expiration = str_replace('/','-',$expiration[0]);
                $expiration = (new \DateTime($expiration))->format('Y-m-d');                
                //if($today > $start && $today <= $expiration){
                if($today <= $expiration){                        
                    $promo = [
                        'coupon_code'           => $cupon['coupon_code'] = $coupon->CouponCode.$store_tag,
                        'coupon_identity'       => $coupon->CouponIdentity,
                        'coupon_reference'      => $coupon->CouponReference,
                        'coupon_text'           => $coupon->CouponText,
                        'discount_text'         => $coupon->DiscountText,
                        'expiration_date'       => $expiration,
                        'starting_date'         => $start,
                        'atm_id'                => $atm_id
                    ];

                    array_push($cupones, $promo);
                }
            }

            if(!empty($cupones)){
                \DB::table('gd_promotions')->where('atm_id', $atm_id)->delete();
                \DB::table('gd_promotions')->insert($cupones);
                \Log::info("Good Deals | Rows insertion for: " . count($cupones) . " coupons for Atm_id ". $atm_id);
            }else{
                \Log::info("Good Deals | No hay cupones disponibles");
            }


            return true;



        }catch (\GuzzleHttp\Exception\ConnectException $e){
            $response_msg = "Tiempo de espera agotado ". $e;
            $response_msg_user = "No se pudo procesar la operación, por favor intente nuevamente";
            $response = $this->errorData($response_msg, $response_msg);
            \Log::warning('[gooddeal]'.$session_id."| Error ".$e);
            return $response;


        } catch (\Exception $e){
            $response_msg = "Error no especificado ". $e;
            \Log::warning('[gooddeal]'.$session_id."| Error ".$e);
        }

    }

    /**
     Get inbox promotions and store images locally
     *
     */

    public function getInboxPromotionsWithImages($session_id,$last_update,$channel,$store_reference,$index,$range,$version,$atm_id, $zip)
    {
        ini_set("max_execution_time",0);

        $endpoint = "GetInboxPromotions";
        $urlget = $this->url . $endpoint;        
        $store_credentials = \DB::connection('eglobalt_pro')->table('atm_services_credentials')->where('atm_id', $atm_id)->where('service_id', 24)->first();
        $store_identity = $store_credentials->user;
        $session_id     = $store_credentials->codEntity;
        $store_data = explode('-',$store_credentials->password);
        $store_instance =   $store_data[0];
        $urlget = str_replace('[emblema]',$store_instance,$urlget);
        $img_path = str_replace('[emblema]',$store_instance,$this->url);
        try {
            $petition = HttpClient::post(
                $urlget, [
                    'json' => [
                        'Session' => $session_id,
                        'LastUpdate' => $last_update,
                        'Channel' => $channel,
                        'StoreReference' => "null",
                        'Index' => $index,
                        'Range' => $range,
                        'Version' => $version
                    ], 'connect_timeout' => 240
                ]
            );            

            $api_response = json_decode($petition->getBody()->getContents());

            //Check for errors in the API Response
            $error_code = $api_response->ErrorCode;
            if ($error_code <> 0) {
                return false;
            } else {
                //return $api_response;
                $coupons = $api_response->Coupons;
                $count = 0;
                $today =  (new \DateTime())->format('Y-m-d');

                foreach($coupons as $coupon){
                    $expiration = explode(" ",$coupon->ExpirationDate);
                    $expiration = str_replace('/','-',$expiration[0]);
                    $expiration = (new \DateTime($expiration))->format('Y-m-d');
                    if($today <= $expiration) {
                        $coupon_identity = $coupon->CouponIdentity;
                        $url = $img_path . 'GetPromotionImage?session=' . $session_id . '&couponIdentity=' . $coupon_identity . '&size=1&lastUpdate=1/1/2016&version=' . $version;
                        \Log::info($url);
                        $filename = $coupon_identity . '.jpg';
                        $file = file_get_contents($url);
                        if(!file_exists('../public/Goodeals/' . $filename)) {
                            \Log::info('Good Deals - Descargando imagen ' . $count . ' ...');
                            $save = file_put_contents('../public/Goodeals/' . $filename, $file);
                        }else{
                            \Log::info('Good Deals - imagen ya existe ...');
                        }

                        #add it to the zip
                        $zip->addFromString($filename, $file);

                        $count++;

                    }

                }
                $response = true;

                \Log::info('Good Deals - Proceso de descarga de imagenes finalizado');
                return $response;

            }
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            $response_msg = "Tiempo de espera agotado " . $e;
            $response_msg_user = "No se pudo procesar la operación, por favor intente nuevamente";
            $response = $this->errorData($response_msg, $response_msg);
            \Log::warning('[gooddeal]' . $session_id . "| Error " . $e);
            return false;


        } catch (\Exception $e) {
            $response_msg = "Error no especificado " . $e;
            \Log::warning('[gooddeal]' . $session_id . "| Error " . $e);
            return false;

        }

    }

    // Download zip archive with promotions images gooddeals
    public function downloadImagesPromotions(){
         # send the file to the browser as a download
        header('Content-Type: application/force-download');
        header('Content-Disposition: inline; filename="imagenes_cupones.zip"');
        header('Content-Transfer-Encoding: binary');
        readfile(public_path().'/Goodeals/imagenes_cupones.zip');
        unlink(public_path().'/Goodeals/imagenes_cupones.zip');
    }

    /*
    *
    *
    * Download zip archive with promotions images gooddeals
    */
    public function getLastUpdateGooddeals(Request $request){
        if($request->ajax()){
            $datosExistentes = \DB::table('promotions_instances')
                ->where('key','=',$request->instancia_id)
                ->first();

            if(!empty($datosExistentes->last_update)){
                $fecha = date('d/m/Y', strtotime($datosExistentes->last_update));
            }else{
                $fecha = '';
            }

            return $fecha;
        }
    }

    /*
    *
    * Get ciudades json
    */
    public function getCiudades(Request $request){
        if($request->ajax()){
            $ciudades = \DB::table('ciudades')
                ->where('departamento_id', $request->get('departamento_id'))
                ->pluck('descripcion','id');

            $ciudades_select = '<option value="">Seleccione una opción</option>';
            foreach($ciudades as $ciudad_id => $ciudad){
                $ciudades_select .= '<option value="'.$ciudad_id.'">'.$ciudad.'</option>';
            }

            return $ciudades_select;
        }
    }

    public function getCiudadesAll(Request $request){
        $search = $request->search;
        $ciudaditems = Ciudad::orderby('id','desc')->select('id','descripcion');
        if($search !== ''){
           $ciudaditems = $ciudaditems->where('descripcion', 'like', '%' .$search . '%');
        }
        $ciudaditems = $ciudaditems->get();
        $response = array();
        foreach($ciudaditems as $cuidaditem){
            $item = [
                "id" => $cuidaditem->id,
                "description" => $cuidaditem->descripcion 
            ];
            array_push($response, $item);
        }
        return $response;
     }

    /*
    *
    * Get barrios json
    */
    public function getBarrios(Request $request){
        if($request->ajax()){
            $barrios = \DB::table('barrios')
                ->where('ciudad_id', $request->get('ciudad_id'))
                ->pluck('descripcion','id');

            $barrios_select = '<option value="">Seleccione una opción</option>';
            foreach($barrios as $barrio_id => $barrio){
                $barrios_select .= '<option value="'.$barrio_id.'">'.$barrio.'</option>';
            }

            return $barrios_select;
        }
    }
    /*
    *
    * Get zonas json
    */
    public function getZonas(Request $request){
        if($request->ajax()){
  
            $zonas = \DB::table('zona')
                ->join('ciudad_zona','zona.id','=','ciudad_zona.zona_id')
                ->join('ciudades','ciudades.id','=','ciudad_zona.ciudades_id')
                ->where('ciudad_zona.ciudades_id', $request->get('ciudad_id'))
                ->where('zona.deleted_at',null)
                ->pluck('zona.descripcion','zona.id');

            $zonas_select = '<option value="">Seleccione una opción</option>';
            foreach($zonas as $zona_id => $zona){
                $zonas_select .= '<option value="'.$zona_id.'">'.$zona.'</option>';
            }

            return $zonas_select;
        }
    }

    public function getZonasAll(Request $request){
        $search = $request->search;
        $zonaitems = Zona::orderby('id','desc')->select('id','descripcion');
        if($search !== ''){
           $zonaitems = $zonaitems->where('descripcion', 'like', '%' .$search . '%');
        }
        $zonaitems = $zonaitems->get();
        $response = array();
        foreach($zonaitems as $zonaitem){
            $item = [
                "id" => $zonaitem->id,
                "description" => $zonaitem->descripcion 
            ];
            array_push($response, $item);
        }
        return $response;
     }
     

    public function Procesar_reactivacion(Request $request){
        if (!$this->user->hasAnyAccess('atms.add|edit')) {
            \Log::warning('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            $response = [
                'error'     => true,
                'message'   => 'Acceso no Autorizado'
            ];
            return $response;
        }

        $atm_id = $request->_atm_id;
        $comments = $request->txtDescription;

        if($comments == ''){
            $response['error'] = true;
            $response['message'] = 'Campo comentario es requerido';

            return $response;
        }

        $atm = Atm::find($atm_id);
        $atm->atm_status = 0;
        $atm->save();

        \Log::info('ATM Reactivado: '.$atm_id.' - Autorizado por: '.$this->user->username .' el '.Carbon::now());

        $notifications = \DB::table('notifications')
            ->where('atm_id',$atm_id)
            ->where('notification_type',1)
            ->where('message','ALERTA DE SEGURIDAD - Acceso no autorizado')
            ->update(
                [
                    'processed'  => true,
                    'updated_at' => Carbon::now(),
                    'comments'   => $comments,
                    'asigned_to' => $this->user->id
                ]
            );

            $atm_status = \DB::table('atm_status_history')
            ->where('atm_id', $atm_id)
            ->orderBy('created_at', 'desc')
            ->get();

            $services = new AtmStatusServices();
            $response = $services->cierreYapertura($atm_id,true,$atm_status[1]->comments,$atm_status[1]->status);

        $response['error'] = false;
        $response['message'] = 'ATM Actualizado correctamente.';

        return $response;
    }

    public function enable_arqueo_remoto(Request $request){
        if (!$this->user->hasAnyAccess('atms.add|edit')) {
            \Log::warning('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            $response = [
                'error'     => true,
                'message'   => 'Acceso no Autorizado'
            ];
            return $response;
        }
        $atm_id = $request->_atm_id;
        $value  = $request->_value;

        $atm = Atm::find($atm_id);
        $atm->arqueo_remoto = $value;
        $atm->save();


        if($value == true){
            \Log::info('ATM Habilitado para arqueo remoto: '.$atm_id.' - Autorizado por: '.$this->user->username .' el '.Carbon::now());
        }

        if($value == false){
            \Log::info('ATM bloqueado para arqueo remoto: '.$atm_id.' - Autorizado por: '.$this->user->username .' el '.Carbon::now());
        }

        $response['error'] = false;
        return $response;

    }

    public function enable_grilla_tradicional(Request $request){
        if (!$this->user->hasAnyAccess('atms_v2.add|edit')) {
            \Log::warning('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            $response = [
                'error'     => true,
                'message'   => 'Acceso no Autorizado'
            ];
            return $response;
        }
        $atm_id = $request->_atm_id;
        $value  = $request->_value;

        $atm = Atm::find($atm_id);
        $atm->grilla_tradicional = $value;
        $atm->save();


        if($value == true){
            \Log::info('ATM con grilla tradicional habilirada: '.$atm_id.' - Autorizado por: '.$this->user->username .' el '.Carbon::now());
        }

        if($value == false){
            \Log::info('ATM con grilla tradicional deshabilitada: '.$atm_id.' - Autorizado por: '.$this->user->username .' el '.Carbon::now());
        }

        $response['error'] = false;
        return $response;

    }

    /*
     * Helper function to create an error response
     */
    private function errorData($message = 'Parámetros incorrectos', $message_user = 'Datos ingresados no son correctos')
    {
        $error_message =
            [
                'error' => true,
                'message' => $message,
                'message_user' => $message_user,
            ];

        return $error_message;
    }

    public function housing($atm_id)
    {   
        // if (!$this->user->hasAccess('housing.add|edit')) {
        //     \Log::error('Unauthorized access attempt',
        //         ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
        //     Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
        //     return redirect('/');
        // } 


        $atm = Atm::find($atm_id);

        $housings = Housing::leftjoin('atms', 'housing.id', '=', 'atms.housing_id')
        ->where('atms.id', null)
        ->pluck('serialnumber','housing.id');

        $housings->prepend('Asignar housing','0');

        if(!empty($atm->housing_id)){
            $housing_id = $atm->housing_id;
            $housing = Housing::find($housing_id);
            $housings->prepend($housing->serialnumber,$housing_id);
        }else{
            $housing_id = null;
        }

        return view('atmnew.housing', compact('atm_id', 'housings', 'housing_id'));
        
    }

    public function store_housing($atm_id, Request $request){
        if (!$this->user->hasAccess('housing.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        \Log::info($request->all());

        try{
            $housing_asignado=Atm::where('housing_id', $request->housing_id)->first();
            if(empty($housing_asignado)){
                $Atm = Atm::find($atm_id);
                $Atm->housing_id = $request->housing_id;
                $Atm->save();

                \Log::info("Housing #".$request->housing_id." asignado al atm #".$atm_id." correctamente");
                Session::flash('message', "Housing # ".$request->housing_id." asignado al atm #".$atm_id." correctamente");
                return redirect('atmnew'); 
            }
        }catch (\Exception $e){
                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Error al asignar housing al Atm';
                $respuesta['tipo'] = 'error';
                \Log::info($respuesta);
                Session::flash('error_message', 'Ocurrio un error al intentar asignar el housing al ATM');
                return redirect()->back()->withInput();
        }
        
    }

    public function exportAtm($group_id, $owner_id){

        if (!$this->user->hasAccess('atms_v2')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $atms = \DB::table('atms')
        ->select(
            'atms.id',
            'atms.code as codigo',
            'atms.name as nombre',
            'owners.name as red',
            'branches.address',
            'branches.latitud',
            'branches.longitud',
            'atms.atm_status as estado',
            'atms.atm_status as progreso',
            'ciudades.descripcion as ciudad',
            'barrios.descripcion',
            'departamento.descripcion as departamento',
            'branches.more_info',
            'branches.phone as telefono',
            'users.description as ejecutivo',
            'u2.description as operativo',
            'atms.last_request_at'
        )
        ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'atms.id')
        ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
        ->join('owners', 'owners.id', '=', 'atms.owner_id')
        ->leftjoin('barrios', 'barrios.id', '=', 'branches.barrio_id')
        ->leftjoin('ciudades', 'ciudades.id', '=', 'barrios.ciudad_id')
        ->leftjoin('departamento', 'departamento.id', '=', 'ciudades.departamento_id')
        ->leftjoin('users', 'users.id', '=', 'branches.executive_id')
        ->leftjoin('users as u2', 'u2.id', '=', 'branches.user_id')
        ->where('atms.deleted_at', null)
        ->where('atms.owner_id', '!=', 18)
        ->where('atms.type', '!=', 'da')
        ->where(function ($query) use ($group_id) {
            if (!empty($group_id) && $group_id <> 0) {
                $query->where('branches.group_id', $group_id);
            }
        })
        ->where(function ($query) use ($owner_id) {
            if (!empty($owner_id) && $owner_id <> 0) {
                $query->where('atms.owner_id', $owner_id);
            }
        })
        ->get();
        foreach($atms as $atm){

            $now  = Carbon::now();
            $end =  Carbon::parse($atm->last_request_at);
            $elasep = $now->diffInMinutes($end);

            if($atm->estado == 1){
                $atm->progreso = 'Suspendido';
            }

            if($atm->estado == -1){
                $atm->progreso = 'Pendiente de regularizar';
            }

            if($atm->estado == -2){
                $atm->progreso = 'Pendiente de regularizar';
            }

            if($atm->estado == -3){
                $atm->progreso = 'Pendiente de regularizar';
            }
            
            if($atm->estado == -4 ){
                $atm->progreso = 'Pendiente de regularizar';
            }
            if($atm->estado == -5 || $atm->estado == -6){
                $atm->progreso = 'Área Comercial';
            }

            if($atm->estado == -7 || $atm->estado == -8){
                $atm->progreso = 'Área de Legales';
            }
            if($atm->estado == -14){
                $atm->progreso = 'Área de sistemas - Antell';
            }
            if($atm->estado == -9){
                $atm->progreso = 'Área de Fraude - Antell';
            }

            if($atm->estado == -10){
                $atm->progreso = 'Área de Contabilidad';
            }
            if($atm->estado == -11){
                $atm->progreso = 'Área de Logísticas';
            }
            if($atm->estado == -12){
                $atm->progreso = 'Área de sistemas - Eglobalt';
            }


            if( ($atm->estado == 0 && $elasep <= 20) || $atm->id == 153 ){
                $atm->estado = 'Online';
                $atm->progreso = 'Online';
            }else{
                if($atm->estado <> 0 && $atm->estado <> 80 && $atm->id <> 153){
                    $atm->estado = 'Suspendido';
                }else{
                    if($atm->estado == 80){
                        $atm->estado = 'ACCESO NO AUTORIZADO';
                        $atm->progreso = 'ACCESO NO AUTORIZADO';
                    }elseif($atm->estado == -5 || $atm->estado == -6){
                        $atm->progreso = 'Área Comercial';
                    }elseif($atm->estado == -7 || $atm->estado == -8){
                        $atm->progreso = 'Área de Legales';
                    }elseif($atm->estado == -14){
                        $atm->progreso = 'Área de sistemas - Antell';
                    }elseif($atm->estado == -9){
                        $atm->progreso = 'Área de Fraude - Antell';
                    }elseif($atm->estado == -10){
                        $atm->progreso = 'Área de Contabilidad';
                    }elseif($atm->estado == -11){
                        $atm->progreso = 'Área de Logísticas';
                    }elseif($atm->estado == -12){
                        $atm->progreso = 'Área de sistemas - Eglobalt';
                    }else{
                        $atm->estado = 'Offline';
                    }
                }
            }

            $atm->last_request_at='';
        }

     
        $cajeros=json_decode(json_encode($atms),true);

        $filename = 'atms_'.time();

        $columnas = array(
            '#', 'Codigo','Nombre','Red','Direccion','Latitud','Longitud','Estado', 'Progreso','Ciudad','Barrio','Departamento','Horario de Atención','Telefono','Ejecutivo responsable', 'Operativo responsable'
        );

        if($cajeros && !empty($cajeros)){
            // Excel::create($filename, function($excel) use ($cajeros) {
            //     $excel->sheet('sheet1', function($sheet) use ($cajeros) {
            //         $sheet->rows($cajeros,false);
            //         $sheet->prependRow(array(
            //             '#', 'Codigo','Nombre','Red','Direccion','Latitud','Longitud','Estado', 'Progreso','Ciudad','Barrio','Departamento','Horario de Atención','Telefono','Ejecutivo responsable', 'Operativo responsable'
            //         ));
            //     });
            // })->export('xls');
            // exit();
            $excel = new ExcelExport($cajeros,$columnas);
            return Excel::download($excel, $filename . '.xls')->send();

        }else{
            Session::flash('error_message', 'No existen parametros para exportar');
        }
        
    }

    public function index_baja(Request $request)
    {

        // if (!$this->user->hasAccess('atms.inactivate.index')) {
        //     \Log::error('Unauthorized access attempt',
        //         ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
        //     Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
        //     return redirect('/');
        // }

        $atms = \DB::table('atms as a')
        ->select('a.id','a.name','a.code','a.atm_status',
            \DB::raw("to_char(a.last_request_at, 'DD/MM/YYYY HH24:MI:SS') as last_request_at"),
            \DB::raw("a.last_request_at as last_request_at_date_time"),
            'a.arqueo_remoto','a.grilla_tradicional','a.compile_version','o.name as owner_name',
            'bg.ruc as ruc_grupo','bg.id',
            'bg.description as nombre_grupo','bg.status as status_grupo')
        ->join('owners as o', 'o.id', '=', 'a.owner_id')
        ->join('points_of_sale as ps','ps.atm_id','=','a.id')
        ->join('branches as b','b.id','=','ps.branch_id')
        ->join('business_groups as bg','bg.id','=','b.group_id')
        ->whereNull('a.deleted_at')
        ->whereNull('bg.deleted_at')
        ->whereNotNull('a.last_token')
        ->get();
           
        foreach ($atms as $atm) {
            $now  = Carbon::now();
            $end =  Carbon::parse($atm->last_request_at_date_time);
            $elasep = $now->diffInMinutes($end);

            $seconds = $elasep;
            $dtF = new DateTime("@0"); 
            $dtT = new DateTime("@$seconds"); 
            $atm->elasep = $dtF->diff($dtT)->format('%a días, %h horas, %i minutos y %s segundos');
        }


        //$grupos      = Group::all( );

        $grupos = Group::join('branches','branches.group_id', '=', 'business_groups.id')
        ->join('points_of_sale','points_of_sale.branch_id', '=', 'branches.id')
        ->join('atms','atms.id', '=', 'points_of_sale.atm_id')
        ->join('owners','owners.id', '=', 'atms.owner_id')
        ->whereIn('atms.owner_id',[16,21,25])
        //->whereNull('atms.deleted_at')
        ->whereNull('business_groups.deleted_at')
        ->whereNotNull('atms.last_token')
        ->select('business_groups.id', 'business_groups.description','business_groups.ruc',
        'business_groups.direccion','business_groups.telefono','business_groups.status')
        ->groupBy('business_groups.id', 'business_groups.description','business_groups.ruc',
        'business_groups.direccion','business_groups.telefono','business_groups.status')
        ->orderBy('business_groups.status', 'DESC')
        ->get();


        return view('atm_baja.index', compact('atms','grupos'));
    }

    public function change_status_group( $groupId, Request $request)
    {
         if (!$this->user->hasAccess('atms.change.status.comercial')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        
        //    $grupo = \DB::table('business_groups as bg')
        //    ->select('bg.id as id','bg.description as name','bg.ruc as ruc','bg.direccion as direccion', 'bg.status as status','bg.telefono as telefono','ps.atm_id as atmId', 'a.code as atm_code', 'a.name as atm_name')
        //    ->join('branches as b','b.group_id','=','bg.id')
        //    ->join('points_of_sale as ps','ps.branch_id','=','b.id')
        //    ->join('atms as a','a.id','=','ps.atm_id')
        //    ->where('bg.id',$groupId)
        //    ->get();

       $grupo = Group::find($groupId);

       $atms_v1 = \DB::table('business_groups as bg')
       ->select('ps.atm_id as atm_id')
       ->join('branches as b','b.group_id','=','bg.id')
       ->join('points_of_sale as ps','ps.branch_id','=','b.id')
       ->join('atms as a','a.id', '=','ps.atm_id')
       ->where('bg.id',$groupId)
       //->whereNull('a.deleted_at')
       //->whereNull('bg.deleted_at')
       ->get();

       $atm_ids = array();
       foreach($atms_v1 as $item){
           $id = $item->atm_id;
           array_push($atm_ids, $id);
       }

       //$atm_list =  Atmnew::findMany($atm_ids);
       $atm_list    =  \DB::table('atms as a')
       ->select('a.id as id','a.code as code', 'a.name as name')
       ->whereIn('a.id',$atm_ids)
       ->get();

      // dd($atm_list_2);


       //v1 - multiselect
       $atms = \DB::table('business_groups as bg')
       ->join('branches as b','b.group_id','=','bg.id')
       ->join('points_of_sale as ps','ps.branch_id','=','b.id')
       ->join('atms as a','a.id', '=','ps.atm_id')
       ->where('bg.id',$groupId)
       ->whereNull('a.deleted_at')
       ->whereNull('bg.deleted_at')
       ->orderBy('a.id','asc')
       ->pluck('a.name as name','a.id as id');


       $atmsLists = Group::join('branches as b','b.group_id','=','business_groups.id')
       ->join('points_of_sale as ps','ps.branch_id','=','b.id')
       ->join('atms as a','a.id', '=','ps.atm_id')
       ->where('business_groups.id',$groupId)
       ->get(['a.name as name','a.id as id']);

        $atmsJsonAll  = json_encode($atmsLists);
       //v2 select individual
        $atms_v2 = \DB::table('business_groups as bg')
       ->join('branches as b','b.group_id','=','bg.id')
       ->join('points_of_sale as ps','ps.branch_id','=','b.id')
       ->join('atms as a','a.id', '=','ps.atm_id')
       ->join('housing as h','h.id','=', 'a.housing_id')
       ->where('bg.id',$groupId)
       ->whereNull('a.deleted_at')
       ->whereNull('bg.deleted_at')
       ->orderBy('a.id','asc')
       //->pluck('a.name || - || a.housing_id as name','a.id as id');
       //->pluck(\DB::raw("concat(a.name,' | Housing_id: ', a.housing_id) as name"),'a.id as id');
       ->pluck(\DB::raw("concat(a.name,' | Housing : ', h.serialnumber) as name"),'a.id as id');




        return view('atm_baja.change_status', compact('groupId','grupo',
                                                    'atm_list',
                                                    //'atm_list_v2',
                                                    'atmsLists',
                                                    'atms',
                                                    'atmsJsonAll',
                                                    'atms_v2'
                                                ));
    }

    public function change_status_group_update( $atmId, Request $request)
    {
        if (!$this->user->hasAccess('atms.change.status.comercial')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();
        $group_id   = $request->group_id;
        $status     = $request->status;
        $atm_id     = $request->atm_id;

        //$receivedAtms   = $request->atms;
        //$array_atms = explode(",",$receivedAtms);     

        try{
            $grupo = Group::find($group_id);

            //Actualizar el estado del grupo
            $status_group =\DB::table('business_groups')
            ->where('id', $group_id)
            ->update(['status' => $status,'updated_at' => Carbon::now()]);      

            //Auditoria
            $history = new InactivateHistory();
            $history->group_id    = $grupo->group_id;
            $history->operation   = 'ACTUALIZAR - ESTADO';
            $history->data        = json_encode(['data_old' =>['id' => $grupo->id, 'description' => $grupo->description, 'status' => $grupo->status], 'data_new'=> ['id' => $grupo->id, 'description' => $grupo->description, 'status' => $status]]);
            $history->created_at  = NULL;
            $history->created_by  = NULL;
            $history->updated_at  = Carbon::now();
            $history->updated_by  = $this->user->id;
            $history->deleted_at  = NULL;
            $history->deleted_by  = NULL;
            $history->save();

            //STATUS = 7 -> INACTIVAR ATM
            if($status == 7){

                //foreach($array_atms as $one_atm){
                    // DB::table('campaigns_details')->insert(['contents_id' => $one_atm, 'campaigns_id' => $campaign_id]);
                $cliente    = new AtmnewController();
                $response   = $cliente->inactivar_alquiler_or_venta($atm_id, $group_id);

                if($response['error'] == false){
                    \Log::debug("ATM inactivado correctamente, atm_id: ".$atm_id);
                    
                    \DB::commit();
                    // return redirect()->back()->with('actualizar','ok')->withInput();
                    return redirect()->to('atm/new/'.$group_id.'/'.$group_id.'/retiro')->with('actualizar','ok');
                }else{
                    \Log::debug("No se pudo inactivar el ATM_id: ".$atm_id); 
                    \DB::rollback();
                    return redirect()->back()->with('error','ok')->withInput();
                }
                //}
            }
            
            \DB::commit();
            return redirect()->back()->with('actualizar','ok')->withInput();
        }catch (\Exception $e){
            \DB::rollback();
            \Log::critical($e->getMessage());
            return redirect()->back()->with('error','ok');;
        }

    }

    public function atms_x_group( $groupId, Request $request)
    {
        //  if (!$this->user->hasAccess('atms.groups.list')) {
        //     \Log::error('Unauthorized access attempt',
        //         ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
        //     Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
        //     return redirect('/');
        // }

       //$grupo = Group::find($groupId);
        $grupo = Group::select('business_groups.id',
            'business_groups.description',
            'business_groups.ruc as ruc',
            'business_groups.direccion', 
            'business_groups.status',
            'business_groups.telefono',
            //\DB::raw("sum(multas_alquiler.saldo) as saldo"),
            \DB::raw("sum(mt_penaltys.amount_total_to_pay) as total"))
            ->leftjoin('mt_movements as m', 'business_groups.id','=','m.group_id')
            ->leftjoin('mt_sales as ms', 'm.id','=','ms.movements_id')
            ->leftjoin('mt_penaltys', 'ms.id','=','mt_penaltys.sale_id')
            ->groupBy('business_groups.id')
            ->where('business_groups.id', '=',  $groupId)
        ->get();
        //dd($grupo);
        //    $atms = \DB::table('business_groups as bg')
        //    ->select('bg.id as id','bg.description as name','bg.ruc as ruc','bg.direccion as direccion', 'bg.status as status','bg.telefono as telefono','a.id as atm_id','a.name as atm_name','a.code as atm_code','o.name as atm_owner')
        //    ->join('branches as b','b.group_id','=','bg.id')
        //    ->join('points_of_sale as ps','ps.branch_id','=','b.id')
        //    ->join('atms as a','a.id','=','ps.atm_id')
        //    ->join('owners as o','o.id','=','a.owner_id')
        //    ->where('bg.id',$groupId)
        //    ->get();

        $service = new ExtractosServices('');

        //Se trae el balance del grupo
        $balance = $service->getBalanceCierre($groupId);
        //El saldo total del cliente
        $saldo_cliente = $balance['total_saldo'];

       $atms = \DB::table('atms')
       ->join('points_of_sale', 'points_of_sale.atm_id','=','atms.id')
       ->join('branches', 'branches.id','=','points_of_sale.branch_id')
       ->join('business_groups', 'business_groups.id','=','branches.group_id')
       ->join('owners', 'owners.id','=','atms.owner_id')
       ->leftjoin('housing', 'housing.id','=','atms.housing_id') //VER
       //->whereNull('atms.deleted_at')
       ->whereNull('business_groups.deleted_at')
       ->whereNotNull('atms.last_token')
       ->where('business_groups.id',$groupId)
       ->select('business_groups.id as id',
                'business_groups.description as name',
                'business_groups.ruc as ruc',
                'business_groups.direccion as direccion', 
                'business_groups.status as status',
                'business_groups.telefono as telefono',
                'atms.id as atm_id',
                'atms.name as atm_name',
                'atms.code as atm_code',
                'atms.deleted_at as activo',
                'housing.serialnumber as serialnumber',
                'owners.name as atm_owner')
       ->get();


        return view('atm_baja.atms_x_group', compact('groupId','atms','grupo','saldo_cliente'));
    }

    public function factura_penalizacion( $groupId, Request $request)
    {
        if (!$this->user->hasAccess('atms.group.penalizacion')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        
       $grupo = Group::find($groupId);

       $atms = \DB::table('business_groups as bg')
       ->select('ps.atm_id as atm_id')
       ->join('branches as b','b.group_id','=','bg.id')
       ->join('points_of_sale as ps','ps.branch_id','=','b.id')
       ->join('atms as a','a.id', '=','ps.atm_id')
       ->where('bg.id',$groupId)
       ->whereNull('a.deleted_at')
       ->whereNull('bg.deleted_at')
       ->get();

       $atm_ids = array();
       foreach($atms as $item){
           $id = $item->atm_id;
           array_push($atm_ids, $id);
       }
       $atm_list       =  Atmnew::findMany($atm_ids);

        return view('atm_baja.penalizacion', compact('groupId','grupo','atm_list'));
    }

    public function inactivar_alquiler_or_venta( $atm_id, $group_id )
    {  
        \DB::beginTransaction();
        $reponse = ['error'   => false,
                    'message' => ''];
        try{
            //Obtener el housing del atm
            $housing_id =\DB::table('atms')
                        ->join('housing','housing.id', '=','atms.housing_id')
                        ->where('atms.id', $atm_id)
                        ->select('atms.housing_id as housing_id','housing.serialnumber as serial')
                        ->get(); 
            \Log::info('[BAJAS - INACTIVAR] obtener housing_id: '.$housing_id[0]->housing_id);
            \Log::info('[BAJAS - INACTIVAR] obtener serial: '.$housing_id[0]->serial);

            //Verificar si existe el housing, si existe buscar en alquiler_housing y el estado sea activo
            if(!empty($housing_id) && !is_null($housing_id[0]->housing_id)){

                $alquiler_id =\DB::table('alquiler_housing')
                            ->where('housing_id', $housing_id[0]->housing_id)
                            ->where('activo', true)
                            ->select('alquiler_id','housing_id','activo','updated_at')
                            ->get(); 
                //\Log::info('[BAJAS - INACTIVAR] alquiler_housing - obtener alquiler_id: '.$alquiler_id[0]->alquiler_id);

                 //Auditoria housing history
                $housing_history = new HousingHistory();
                $housing_history->housing_id     = $housing_id[0]->housing_id;
                $housing_history->last_atm_id    = $atm_id;
                $housing_history->available      = true;
                $housing_history->operation_type = 'BAJA';
                $housing_history->created_at     = Carbon::now();
                $housing_history->created_by     = $this->user->id;
                $housing_history->updated_at     = NULL;
                $housing_history->updated_by     = NULL;
                $housing_history->deleted_at     = NULL;
                $housing_history->deleted_by     = NULL;
                $housing_history->save();


                ///////////////////////////////////
                // 2 - TRANSFERENCIAS DE EQUIPOS
                /*datos para ondanet*/                       
                $imei = "'".$housing_id[0]->serial."'";
                \Log::info('***************************************');
                \Log::info('enviando sendtransferencia');
                $response_ondanet  = $this->sendTransferencia($group_id, $imei);
                \Log::info('***************************************');




                // verificar si existe el alquiler, sino buscar en ventas
                if(!empty($alquiler_id) && !is_null($alquiler_id[0]->alquiler_id)){

                    //si existe el alquiler_id, actualiar la tabla alquiler_housing : updated_at-> now() y activo : false
                    $update_alquiler_housing =\DB::table('alquiler_housing')
                                            ->where('housing_id', $housing_id[0]->housing_id)
                                            ->where('activo', true)
                                            ->update([  'activo'     => false,
                                                        'updated_at' => Carbon::now()]); 
                    \Log::info('[BAJAS - INACTIVAR] actualizar alquiler_housing -> housing_id: '.$housing_id[0]->housing_id);

                    $history = new InactivateHistory();
                    $history->atm_id      = $atm_id;
                    $history->group_id    = $group_id;
                    $history->operation   = 'ESTADO - INACTIVAR ALQUILER_HOUSING';
                    $history->data        = json_encode(['data_old' =>[ 'group_id'   => $group_id, 
                                                                        'alquiler_id'=> $alquiler_id[0]->alquiler_id, 
                                                                        'housing_id' => $alquiler_id[0]->housing_id, 
                                                                        'activo'     => $alquiler_id[0]->activo, 
                                                                        'updated_at' => $alquiler_id[0]->updated_at],
                                                         'data_new'=> [ 'group_id'   => $group_id, 
                                                                        'alquiler_id'=> $alquiler_id[0]->alquiler_id, 
                                                                        'housing_id' => $alquiler_id[0]->housing_id, 
                                                                        'activo'     => false, 
                                                                        'updated_at' => Carbon::now()]]);
                    $history->created_at  = NULL;
                    $history->created_by  = NULL;
                    $history->updated_at  = Carbon::now();
                    $history->updated_by  = $this->user->id;
                    $history->deleted_at  = NULL;
                    $history->deleted_by  = NULL;
                    $history->save();

                    //buscar el alquiler
                    $alquiler =\DB::table('alquiler')
                            ->where('id', $alquiler_id[0]->alquiler_id)
                            ->where('activo', true)
                            ->where('group_id', $group_id)
                            ->select('id','activo','updated_at')
                            ->get(); 

                    if(!empty($alquiler) && !is_null($alquiler[0]->id)){
                        //si existe el alquiler con el estado activo, se acutaliza: estado -> false, updated_at->now()
                        $update_alquiler =\DB::table('alquiler')
                        ->where('id', $alquiler[0]->id)
                        ->update([  'activo' => false,
                                    'updated_at' => Carbon::now()]); 
                        \Log::info('[BAJAS - INACTIVAR] actualizar alquiler -> alquiler_id: '.$alquiler[0]->id);

                        $history = new InactivateHistory();
                        $history->atm_id      = $atm_id;
                        $history->group_id    = $group_id;
                        $history->operation   = 'ESTADO - INACTIVAR ALQUILER';
                        $history->data        = json_encode(['data_old' =>[ 'group_id'   => $group_id, 
                                                                            'alquiler_id'=> $alquiler[0]->id, 
                                                                            'activo'     => $alquiler[0]->activo, 
                                                                            'updated_at' => $alquiler[0]->updated_at],
                                                             'data_new'=> [ 'group_id'   => $group_id, 
                                                                            'alquiler_id'=> $alquiler[0]->id, 
                                                                            'activo'     => false, 
                                                                            'updated_at' => Carbon::now()]]);
                        $history->created_at  = NULL;
                        $history->created_by  = NULL;
                        $history->updated_at  = Carbon::now();
                        $history->updated_by  = $this->user->id;
                        $history->deleted_at  = NULL;
                        $history->deleted_by  = NULL;
                        $history->save();

                        //2- actualizar ATM
                        $update_atm =\DB::table('atms')
                                    ->where('id', $atm_id)
                                    ->update([  'deleted_at' => Carbon::now(),
                                                'housing_id' => NULL]); 
                                    \Log::info('[BAJAS - INACTIVAR] inactivar ATM_ID: '.$atm_id);

                        //Inactivar atms_per_users
                        $atm_per_user = \DB::table('atms_per_users')
                                    ->where('atm_id', $atm_id)
                                    ->where('status', true)
                                    ->select('id','status','atm_id','deleted_at','deleted_by')
                                    ->get(); 
                                    \Log::debug('atm_per_user');
                                    \Log::debug($atm_per_user);

                        if(!empty($atm_per_user) && !is_null($atm_per_user[0]->id)){


                            $update_atm_per_user =\DB::table('atms_per_users')
                                            ->where('id', $atm_per_user[0]->id)
                                            ->update([  'status'     => false,
                                                        'deleted_at' => Carbon::now(),
                                                        'deleted_by' => $this->user->id
                                                    ]); 
                            \Log::info('[BAJAS - INACTIVAR] actualizar atms_per_users -> id: '.$atm_per_user[0]->id);

                            $history = new InactivateHistory();
                            $history->atm_id      = $atm_id;
                            $history->group_id    = $group_id;
                            $history->operation   = 'ESTADO - INACTIVAR ATMS_PER_USERS';
                            $history->data        = json_encode(['data_old' =>[ 'group_id'   => $group_id, 
                                                                                'id'         => $atm_per_user[0]->id, 
                                                                                'atm_id'     => $atm_per_user[0]->atm_id, 
                                                                                'status'     => $atm_per_user[0]->status, 
                                                                                'deleted_at' => $atm_per_user[0]->deleted_at,
                                                                                'deleted_by' => $atm_per_user[0]->deleted_by],
                                                                'data_new'=> [ 'group_id'   => $group_id, 
                                                                                'id'         => $atm_per_user[0]->id, 
                                                                                'atm_id'     => $atm_per_user[0]->atm_id, 
                                                                                'activo'     => false, 
                                                                                'deleted_at' => Carbon::now(),
                                                                                'deleted_by' => $this->user->id ]]);
                            $history->created_at  = NULL;
                            $history->created_by  = NULL;
                            $history->updated_at  = Carbon::now();
                            $history->updated_by  = $this->user->id;
                            $history->deleted_at  = NULL;
                            $history->deleted_by  = NULL;
                            $history->save();
    
                        }

                    }else{
                        \Log::info('[BAJAS - INACTIVAR] no se encontro el alquiler -> alquiler_id: '.$alquiler_id[0]->alquiler_id);
                        $reponse = ['error'   => true,
                                    'message' => 'alquiler no encontrado'];
                    }
                
                }else{
                    \Log::info('[BAJAS - INACTIVAR] no se encontro el alquiler_housing -> housing_id: '.$housing_id[0]->housing_id);
                    $reponse = ['error'   => true,
                                'message' => 'alquiler_housing no encontrado'];

                    // si no se encontro en alguiler_housing, buscar en ventas_housing
                    $ventas_id =\DB::table('venta_housing')
                            ->where('housing_id', $housing_id[0]->housing_id)
                            ->where('estado', true)
                            ->select('venta_id','housing_id','estado','updated_at')
                            ->get(); 
                    \Log::info('[BAJAS - INACTIVAR] venta_housing - obtener venta_id de housing_id: '.$housing_id[0]->housing_id);

                    if(!empty($ventas_id) && !is_null($ventas_id[0]->venta_id)){

                        //si existe el ventas_id, actualiar la tabla venta_housing : updated_at-> now() y activo : false
                        $update_venta_housing =\DB::table('venta_housing')
                                            ->where('housing_id', $housing_id[0]->housing_id)
                                            ->where('estado', true)
                                            ->update([  'estado'     => false,
                                                        'updated_at' => Carbon::now()]); 
                        \Log::info('[BAJAS - INACTIVAR] actualizar venta_housing -> housing_id: '.$housing_id[0]->housing_id);

                        $history = new InactivateHistory();
                        $history->atm_id      = $atm_id;
                        $history->group_id    = $group_id;
                        $history->operation   = 'ESTADO - INACTIVAR VENTA_HOUSING';
                        $history->data        = json_encode(['data_old' =>[ 'group_id'   => $group_id, 
                                                                            'venta_id'   => $ventas_id[0]->venta_id, 
                                                                            'housing_id' => $ventas_id[0]->housing_id, 
                                                                            'estado'     => $ventas_id[0]->estado, 
                                                                            'updated_at' => $ventas_id[0]->updated_at],
                                                             'data_new'=> [ 'group_id'   => $group_id, 
                                                                            'venta_id'   => $ventas_id[0]->venta_id, 
                                                                            'housing_id' => $ventas_id[0]->housing_id, 
                                                                            'estado'     => false, 
                                                                            'updated_at' => Carbon::now()]]);
                        $history->created_at  = NULL;
                        $history->created_by  = NULL;
                        $history->updated_at  = Carbon::now();
                        $history->updated_by  = $this->user->id;
                        $history->deleted_at  = NULL;
                        $history->deleted_by  = NULL;
                        $history->save();
    
                        //buscar la venta
                        $venta =\DB::table('venta')
                                ->where('id', $ventas_id[0]->venta_id)
                                ->where('activo', true)
                                ->where('group_id', $group_id)
                                ->select('id','activo','updated_at')
                                ->get(); 

                        if(!empty($venta) && !is_null($venta[0]->id)){
                            //si existe la venta con el estado activo, se acutaliza: activo -> false, updated_at->now()
                            $update_alquiler =\DB::table('alquiler')
                                            ->where('id', $venta[0]->id)
                                            ->update([  'activo'     => false,
                                                        'updated_at' => Carbon::now()]); 
                            \Log::info('[BAJAS - INACTIVAR] actualizar venta -> venta_id: '.$venta[0]->id);

                            $history = new InactivateHistory();
                            $history->atm_id      = $atm_id;
                            $history->group_id    = $group_id;
                            $history->operation   = 'ESTADO - INACTIVAR VENTA';
                            $history->data        = json_encode(['data_old' =>[ 'group_id'   => $group_id, 
                                                                                'venta_id'   => $venta[0]->id, 
                                                                                'activo'     => $venta[0]->activo, 
                                                                                'updated_at' => $venta[0]->updated_at],
                                                                 'data_new'=> [ 'group_id'   => $group_id, 
                                                                                'venta_id'   => $venta[0]->id, 
                                                                                'activo'     => false, 
                                                                                'updated_at' => Carbon::now()]]);
                            $history->created_at  = NULL;
                            $history->created_by  = NULL;
                            $history->updated_at  = Carbon::now();
                            $history->updated_by  = $this->user->id;
                            $history->deleted_at  = NULL;
                            $history->deleted_by  = NULL;
                            $history->save();

                            //2- actualizar ATM
                            $update_atm =\DB::table('atms')
                            ->where('id', $atm_id)
                            ->update([  'deleted_at' => Carbon::now(),
                                        'housing_id' => NULL]); 
                            \Log::info('[BAJAS - INACTIVAR] inactivar ATM_ID: '.$atm_id);

                            $reponse = ['error'   => false,
                                        'message' => 'ventas - inactivada correctamente'];
                        }else{
                            \Log::info('[BAJAS - INACTIVAR] no se encontro la venta -> venta_id: '.$ventas_id[0]->venta_id);
                            $reponse = ['error'   => true,
                                        'message' => 'venta no encontrada'];
                        }
                    }else{
                        \Log::info('[BAJAS - INACTIVAR] no se encontro en venta_housing -> housing_id: '.$housing_id[0]->housing_id);
                        $reponse = ['error'   => true,
                                    'message' => 'venta_housing no encontrada'];
                    }
                }
            }else{
                \Log::info('[BAJAS - INACTIVAR] no se encontro el housing_id: '.$housing_id[0]->housing_id);
                $reponse = ['error'   => true,
                            'message' => 'ATM sin housing'];
            }

            \DB::commit();
            return $reponse;

        }catch (\Exception $e){
            $reponse = ['error'   => true,
                        'message' => $e->getMessage()];
            \DB::rollback();
            \Log::critical($e->getMessage());
            return $reponse;
        }       

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

    public function sendTransferencia($group_id, $imei)
    {
        try {
            \DB::beginTransaction();

            $grupo  = \DB::table('business_groups')->where('id', $group_id)->first();
            $pdv    = $grupo->ruc;

            $query  =  "SET NOCOUNT ON;
                        SET ANSI_WARNINGS OFF;
                        SET DATEFORMAT dmy;                        
                        DECLARE @rv Numeric(25)
                        EXEC @rv = [DBO].[P_TRANSFERENCIAS_MINITERMINAL]
                        '$pdv',$imei
                        SELECT @rv";
                        \Log::info("[Baja -Inactivar - Transaferencia] Prodecimiento a ejecutar en Ondanet ", ['query' => $query]);
            
            // Auditoria, insertar request a ondanet  
            //Auditoria
            $history = new InactivateHistory();
            $history->group_id    = $group_id;
            $history->operation   = 'ESTADO | INACTIVAR - TRANSFERENCIA REQUEST';
            $history->data        = $query;
            $history->created_at  = Carbon::now();
            $history->created_by  = $this->user->id;
            $history->updated_at  = NULL;
            $history->updated_by  = NULL;
            $history->deleted_at  = NULL;
            $history->deleted_by  = NULL;
            $history->save();     

         
            $results = $this->get_one($query);
            \Log::info("[Baja - Inactivar - transferencia] Respuesta de Ondanet ", ['response' => $results]);

            //Auditoria, insertar respuesta de ondanet
            $history = new InactivateHistory();
            $history->group_id    = $group_id;
            $history->operation   = 'ESTADO | INACTIVAR - TRANSFERENCIA RESPONSE';
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
                \Log::info($data);
                \DB::commit();
                return $data;
            } else {
                $message        = explode("|", $check);
                $data['error']  = true;
                $data['status'] = $check;
                $data['code']   = $message[0];
                \Log::info($data);
                \DB::commit();
                return $data;
            }
        } catch (\Exception $e) {
            \DB::rollback();
            $response['error']   = true;
            $response['status']  = '212';
            $response['code']    = '212';
            \Log::warning('[Eglobal - Cliente]', ['result' => $e]);
            return $response;
        }
    }

    /** FUNCIONES PRIVADAS COMUNES*/

    public function get_one($query)
    {
        try {
            \DB::beginTransaction();
            $db     = \DB::connection('ondanet')->getPdo();
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
            \DB::commit();
            return $register;
        } catch (\PDOException $e) {
            \DB::rollback();
            return $e;
        }
    }
}
