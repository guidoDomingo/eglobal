<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAtmRequest;
use App\Http\Requests\UpdateAtmRequest;
use App\Models\Atm;
use App\Models\Applications;
use App\Models\Owner;
use App\Models\Branch;
use App\Models\Group;
use App\Models\User;
use App\Models\VoucherType;
use App\Models\Pos;
use App\Models\PosSaleVoucher;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use App\Models\Housing;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Session;
use Cache;
use App\Models\WebService;
use App\Services\AtmStatusServices;
use HttpClient;
use GuzzleHttp\Psr7;
use ZipArchive;
use Mail;

use DateTime;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ExcelExport;

class AtmController extends Controller
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
        if (!$this->user->hasAccess('atms')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        //\Log::info("request inputs: ", $request->all());

        $name = $request['name'];
        $id = $request['id'];
        $owner_id = $request['owner_id'];
        $group_id = $request['group_id'];
        //$record_limit = $request['record_limit'];
        $tipo_id = $request['tipo_id'];

        $atm_active = '';

        if ($request->isMethod('get')) {
            $atm_active = 'on';
            \Log::info("GET");
        } else if ($request->isMethod('post')) {
            \Log::info("POST");
            if (isset($request['atm_active'])) {
                if ($request['atm_active'] == 'on') {
                    $atm_active = 'on';
                }
            }
        }

        $atms = \DB::table('atms as a')
            ->select(
                'a.id',
                \DB::raw("round(cast((extract(epoch from (now() - a.last_request_at)) / 60) as numeric), 1) as minutes"),
                \DB::raw("to_char(age(now(), a.last_request_at), 'YY \"Año/s\" mm \"Mes/es\" DD \"Día/s\" HH24 \"Hora/s\" MI \"Minuto/s\"') as minutes_to_data_time"),
                \DB::raw("
                    (case 
                        when (a.atm_status = 0 and (extract(epoch from (now() - a.last_request_at)) / 60) <= 20) or a.id = 153 then 'D'
                        when a.atm_status != 0 and a.atm_status != 80 and a.id != 153 then 'C'
                        when a.atm_status = 80 then 'B'
                        else 'A'
                    end) as order_status
                "),
                \DB::raw("
                    (case 
                        when (a.atm_status = 0 and (extract(epoch from (now() - a.last_request_at)) / 60) <= 20) or a.id = 153 then 'Online'
                        when a.atm_status != 0 and a.atm_status != 80 and a.id != 153 then 'Suspendido'
                        when a.atm_status = 80 then 'Acceso no autorizado'
                        else 'Offline'
                    end) as status
                "),
                \DB::raw("to_char(a.last_request_at, 'DD/MM/YYYY HH24:MI:SS') as last_request_at"),
                'a.name',
                'a.code',
                'a.atm_status',
                'a.arqueo_remoto',
                'a.grilla_tradicional',
                'a.compile_version',
                'a.block_type_id',
                'o.name as owner_name',
                //'bg.description as business_group',
                \DB::raw("
                    (case 
                        when bg.description is null or bg.description = ''  then 'Grupo Eglobalt'
                        else bg.description
                    end) as business_group
                ")
            )
            ->join('owners as o', 'o.id', '=', 'a.owner_id')
            ->join('points_of_sale as pos', 'a.id', '=', 'pos.atm_id')
            ->join('branches as b', 'b.id', '=', 'pos.branch_id')
            ->leftjoin('business_groups as bg', 'bg.id', '=', 'b.group_id');

        if ($name !== null and $name !== '') {
            $atms = $atms->whereRaw("a.name ilike '%$name%'");
        }

        if ($owner_id !== null and $owner_id !== '0') {
            $atms = $atms->whereRaw("a.owner_id = $owner_id");
        }

        if ($group_id !== null and $group_id !== '0') {
            $atms = $atms->whereRaw("a.group_id = $group_id");
        }

        if ($tipo_id !== null and $tipo_id !== '0') {
            $atms = $atms->whereRaw("bg.manager_id = $tipo_id");
        }

        if ($atm_active == 'on') {
            $atms = $atms->whereRaw("a.deleted_at is null");
        }

        $atms = $atms
            ->orderBy('order_status', 'asc')
            ->orderBy('minutes', 'desc');

        //\Log::info("atms query: " . $atms->toSql());

        $atms = $atms->get();

        $block_types = \DB::table('block_type as bt')
            ->select(
                'id',
                \DB::raw("('#' || id || ' ' || description) as description")
            )
            ->orderBy('id', 'ASC')
            ->get();

        $block_types = json_encode($block_types);


        $owners = Owner::orderBy('name')->get()->pluck('name', 'id')->toArray();
        $owners[0] = 'Red - Todos';
        ksort($owners);

        $groups = Group::orderBy('description')->get()->pluck('description', 'id')->toArray();
        $groups[0] = 'Grupo - Todos';
        ksort($groups);

        $owner = null;

        if ($owner_id <> 0) {
            $owner = Owner::where('id', $owner_id)->first();
        }

        $group = null;

        if ($group_id <> 0) {
            $group = Group::where('id', $group_id)->first();
        }

        if ($request->get('download')) {
            //dd($request->all());
            if ($request->input('download') == 'download') {
                $result = $this->exportAtm($group_id, $owner_id, $tipo_id, $name);
                $result = json_decode(json_encode($result), true);
            }
        }


        //dd($owners);

        /*foreach ($atms as $atm) {
            $now = Carbon::now();
            $end = Carbon::parse($atm->last_request_at_date_time);
            $atm->elasep = $now->diffInMinutes($end);

            $seconds = $atm->elasep * 60; // multiplicar los minutos por 60 segundos respectivamente.
            $dtF = new DateTime("@0"); 
            $dtT = new DateTime("@$seconds"); 
            //$atm->last_request_at_view = $dtF->diff($dtT)->format('%a días, %h horas, %i minutos y %s segundos');
            $atm->last_request_at_view = $dtF->diff($dtT)->format('%a días, %h horas, %i minutos');
        }*/

        return view('atm.index', compact('atms', 'owners', 'owner_id', 'owner', 'groups', 'group_id', 'group', 'block_types', 'tipo_id', 'atm_active', 'name'));
    }

    /**
     * Modificar el Block-Type del ATM
     */
    public function block_type_change(Request $request)
    {

        $class = __CLASS__;
        $function = __FUNCTION__;
        $inputs = json_encode($request->all());
        \Log::info("\n\nCampos obtenidos en $class \ $function:\n\n$inputs\n\n");

        $response = [
            'error' => false,
            'message' => '',
            'querys_log' => null
        ];

        try {

            $atm_id = $request['atm_id'];
            $block_type_id = $request['block_type_id'];
            $commentary = $request['commentary'];
            $user_id = $this->user->id;
            $now = Carbon::now();

            \DB::enableQueryLog();

            \DB::beginTransaction();

            // Nuevo metodo:
            $atm = \DB::table('public.atms as a')
                ->select(
                    'a.*'
                )
                ->where('a.id', $atm_id)
                ->get();

            $atm = $atm[0];
            $atm = json_decode(json_encode($atm), true);

            \Log::info('atm:');
            \Log::info($atm);

            $audit_atms_columns = \DB::table('information_schema.columns as c')
                ->select(
                    'c.column_name'
                )
                ->where('c.table_schema', 'audit')
                ->where('c.table_name', 'atms')
                ->whereRaw("c.column_name != 'audit_id'")
                ->get();

            $audit_atms_columns = json_decode(json_encode($audit_atms_columns), true);

            $audit_atms_columns_insert = [];

            foreach ($audit_atms_columns as $column) {
                $column = $column['column_name'];
                $audit_atms_columns_insert["$column"] = null;

                if (
                    $column !== 'audit_id' and
                    $column !== 'audit_created_at' and
                    $column !== 'audit_created_by' and
                    $column !== 'audit_commentary'
                ) {

                    $audit_atms_columns_insert["$column"] = $atm["$column"];
                }
            }

            $audit_atms_columns_insert['audit_created_at'] = $now;
            $audit_atms_columns_insert['audit_created_by'] = $user_id;
            $audit_atms_columns_insert['audit_commentary'] = $commentary;

            $audit_id = \DB::table('audit.atms')->insertGetId($audit_atms_columns_insert);

            if ($audit_id !== null) {

                /**
                 * Actualiza al nuevo block_type_id
                 */
                \DB::table('public.atms')
                    ->where('id', $atm_id)
                    ->update([
                        'block_type_id' => $block_type_id,
                        'updated_by' => $user_id,
                        'updated_at' => $now
                    ]);

                /**
                 * Obtenemos el saldo pendiente del atm, pero haciendo el calculo con total_transaccionado_cierre
                 */
                $balance_atms = \DB::table('balance_atms')
                    ->select(
                        'total_transaccionado as total_transaccionado_cierre',
                        'total_depositado as total_deposited',
                        'total_reversado as total_reverse',
                        'total_cashout as total_cashout',
                        'total_pago_cashout as total_payment_cashout',
                        'total_pago_qr as total_pago_qr',
                        'total_multa as multa'
                    )
                    ->where('atm_id', $atm_id)
                    ->get();

                if (count($balance_atms) > 0) {
                    $balance_atms = $balance_atms[0];

                    $total_transaccionado_cierre = abs($balance_atms->total_transaccionado_cierre);
                    $total_deposited = -abs($balance_atms->total_deposited);
                    $total_reverse = -abs($balance_atms->total_reverse);
                    $total_cashout = -abs($balance_atms->total_cashout);
                    $total_payment_cashout = abs($balance_atms->total_payment_cashout);
                    $total_pago_qr = abs($balance_atms->total_pago_qr);
                    $total_multa = abs($balance_atms->multa);

                    $total_balance = $total_transaccionado_cierre + $total_payment_cashout + $total_deposited + $total_reverse + $total_cashout + $total_pago_qr + $total_multa;
                } else {
                    $total_balance = 0;
                }

                /**
                 * block_type_id = 1 es Online 
                 * cualquier otro es locked = true
                 */
                if ($block_type_id == 0) {
                    $locked = false;
                } else {
                    $locked = true;
                }

                /**
                 * Inserta un nuevo registro en el historial_bloqueos
                 */

                \DB::table('public.historial_bloqueos')
                    ->insert([
                        'atm_id' => $atm_id,
                        'saldo_pendiente' => $total_balance,
                        'created_at' => $now,
                        'bloqueado' => $locked,
                        'block_type_id' => $block_type_id,
                        'commentary' => $commentary, // Comentario ingresado en pantalla.
                        'created_by' => $user_id // Usuario que creó el registro.
                    ]);
            }

            \DB::commit();

            \Log::info("El atm_id = $atm_id tiene el nuevo block_type_id = $block_type_id por el usuario con id = $user_id");
        } catch (\Exception $e) {
            $error_detail = [
                'message' => 'Ocurrió una excepción al querer modificar el block type.',
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => $class,
                'function' => $function,
                'line' => $e->getLine()
            ];

            $response['message'] = $error_detail['message'];
            $response['error_detail'] = $error_detail;

            $error_detail = json_encode($error_detail);

            \Log::error("\n\nError en $class \ $function:\nDetalles:\n\n$error_detail\n\n");
        }


        if ($response['message'] !== '') {
            $response['error'] = true;
        } else {
            $response['message'] = 'Acción exitosa.';
        }


        $response['querys_log'] = \DB::getQueryLog();

        $response_aux = json_encode($response);

        \Log::info("\n\nRespuesta de $class \ $function:\n\n$response_aux\n\n");

        return $response;
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!$this->user->hasAccess('atms.add|edit')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $public_key = Str::random(40);
        $private_key = Str::random(40);

        $owners = Owner::orderBy('name')->get()->pluck('name', 'id');
        $data = [
            'public_key'    => $public_key,
            'private_key'   => $private_key,
            'owners'        => $owners,
        ];

        //\Log::debug('ITEMS:', [$data]);

        return view('atm.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreAtmRequest|Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAtmRequest $request)
    {
        if (!$this->user->hasAccess('atms.add|edit')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $atm_code = \DB::table('atms')
            ->selectRaw('code')
            ->orderBy('created_at', 'desc')
            ->first();

        $atm = new Atm;
        $atm->public_key = $request->public_key;
        $atm->private_key = $request->private_key;
        $atm->code = $atm_code->code + 1;
        $atm->name = $request->name;
        $atm->owner_id = $request->owner_id;

        $atm->atm_status = -1;

        if ($request->ajax()) {
            $respuesta = [];
            try {
                if ($atm->save()) {
                    $data = [];
                    $data['id'] = $atm->id;
                    $ownerId = $request->owner_id;
                    if ($ownerId == 2) {
                        $ownerId = 11;
                    }

                    if ($ownerId == 16 || $ownerId == 21 || $ownerId == 25) {

                        \DB::table('parametros_comisiones')->insert([
                            ['service_id' => 49, 'service_source_id' => 0, 'atm_id' => $atm->id, 'tipo_comision' => 1, 'comision' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'tipo_servicio_id' => 1],
                            ['service_id' => 6, 'service_source_id' => 7, 'atm_id' => $atm->id, 'tipo_comision' => 1, 'comision' => 3.5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'tipo_servicio_id'  => 0],
                            ['service_id' => 3, 'service_source_id' => 0, 'atm_id' => $atm->id, 'tipo_comision' => 1, 'comision' => 3.5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'tipo_servicio_id' => 1],
                            ['service_id' => 10, 'service_source_id' => 7, 'atm_id' => $atm->id, 'tipo_comision' => 1, 'comision' => 3.5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'tipo_servicio_id'  => 0],
                            ['service_id' => 44, 'service_source_id' => 0, 'atm_id' => $atm->id, 'tipo_comision' => 1, 'comision' => 3.5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'tipo_servicio_id'  => 1],
                            ['service_id' => 7, 'service_source_id' => 0, 'atm_id' => $atm->id, 'tipo_comision' => 1, 'comision' => 0.7142, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'tipo_servicio_id' => 1,],
                            ['service_id' => 50, 'service_source_id' => 0, 'atm_id' => $atm->id, 'tipo_comision' => 1, 'comision' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'tipo_servicio_id'  => 1],
                            ['service_id' => 12, 'service_source_id' => 7, 'atm_id' => $atm->id, 'tipo_comision' => 1, 'comision' => 3.5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'tipo_servicio_id'  => 0],
                            ['service_id' => 54, 'service_source_id' => 0, 'atm_id' => $atm->id, 'tipo_comision' => 1, 'comision' => 5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'tipo_servicio_id' => 1],
                            ['service_id' => 14, 'service_source_id' => 8, 'atm_id' => $atm->id, 'tipo_comision' => 1, 'comision' => 3.5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'tipo_servicio_id' => 0],
                            ['service_id' => 15, 'service_source_id' => 8, 'atm_id' => $atm->id, 'tipo_comision' => 1, 'comision' => 3.5, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'tipo_servicio_id' => 0],
                            ['service_id' => 3, 'service_source_id' => 8, 'atm_id' => $atm->id, 'tipo_comision' => 1, 'comision' => 0.7142, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'tipo_servicio_id' => 0],
                            ['service_id' => 9, 'service_source_id' => 0, 'atm_id' => $atm->id, 'tipo_comision' => 1, 'comision' => 1, 'created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'tipo_servicio_id' => 1],
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
                    }

                    $aplicaciones = Applications::where('active', true)
                        ->get()
                        ->pluck('name', 'id');

                    $data['applications'] = [];
                    foreach ($aplicaciones as $applicationId => $texto) {
                        $valor = [];
                        $valor['id'] = $applicationId;
                        $valor['text'] = $texto;
                        $data['applications'][] = $valor;
                    }

                    \Log::info("Nuevo atm creado");
                    $respuesta['mensaje'] = 'Agregado correctamente';
                    $respuesta['tipo'] = 'success';
                    $respuesta['owner_id'] = $ownerId;
                    $respuesta['data'] = $data;
                    $respuesta['url'] = route('atm.update', [$atm->id]);
                    return $respuesta;
                }
            } catch (\Exception $e) {
                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Error al crear atm';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }
        } else {
            if ($atm->save()) {
                $message = 'Agregado correctamente';
                Session::flash('message', $message);
                //create resources directory
                if (!$this->createDirectoryResources($atm->id)) {
                    Session::flash('message', "Directorio de Recursos no creado.");
                }
                return redirect()->route('atm.index');
            } else {
                Session::flash('error_message', 'Error al guardar el registro');
                return redirect()->route('atm.index');
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!$this->user->hasAccess('atms.add|edit')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $atm = Atm::find($id);
        $pos = Pos::where('atm_id', $id)->first();

        $grupo = \DB::table('business_groups')
            ->select('business_groups.*', 'branches.id as branch_id')
            ->join('branches', 'branches.group_id', '=', 'business_groups.id')
            ->join('points_of_sale', 'points_of_sale.branch_id', '=', 'branches.id')
            ->where('points_of_sale.atm_id', $id)
            ->first();

        if (empty($pos)) {
            $posVoucher = [];
        } else {
            $posVoucher = PosSaleVoucher::with('voucherType')->where('point_of_sale_id', $pos->id)->first();
            if (!empty($posVoucher)) {
                $posVoucher->valid_from = date('d/m/Y', strtotime($posVoucher->valid_from));
                $posVoucher->valid_until = date('d/m/Y', strtotime($posVoucher->valid_until));
            }
        }

        if (empty($grupo)) {
            $grupo = [];
        }

        $aplicaciones = Applications::where('active', true)
            ->get()
            ->pluck('name', 'id');
        $app = \DB::table('atm_application')->where('atm_id', $atm->id)->first();

        $appId = null;
        if (!empty($app)) {
            $appId = $app->application_id;
        }

        $atm_parts = \DB::table('atms_parts')->where('atm_id', $atm->id)->count();


        $owners = Owner::orderBy('name')->get()->pluck('name', 'id');
        $branches = Branch::pluck('description', 'id');
        $groups = Group::pluck('description', 'id');
        // TODO get seller type fron ONDANET
        $sellerType['1'] = 'Testing Seller type';
        $users = User::all()->pluck('description', 'id');
        $users->prepend('Asignar usuario', '0');
        $user_id = 0;

        $voucherTypes = VoucherType::orderBy('id')->get()->pluck('description', 'id');

        $departamentos = \DB::table('departamento')->pluck('descripcion', 'id');

        $data = array(
            'atm'           => $atm,
            'pointofsale'   => $pos,
            'posVoucher'    => $posVoucher,
            'aplicaciones'  => $aplicaciones,
            'app_id'        => $appId,
            'atm_parts'     => $atm_parts,
            'auth_token'    => $atm->auth_token,
            'owners'        => $owners,
            'branches'      => $branches,
            'groups'        => $groups,
            'grupo'        => $grupo,
            'departamentos' => $departamentos,
            'ondanet_seller_types' => $sellerType,
            'selected_seller_type' => null,
            'selected_branch' => null,
            'selected_group' => null,
            // step new branch modal
            'users' => $users,
            'user_id' => $user_id,
            // step new voucher
            'voucherTypes' => $voucherTypes
        );

        return view('atm.edit_form_step', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateAtmRequest|Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAtmRequest $request, $id)
    {
        if (!$this->user->hasAccess('atms.add|edit')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
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
        $atm->name = $request->name;
        $atm->owner_id = $request->owner_id;
        //$atm->group_id = $request->group_id;

        if ($request->ajax()) {
            $respuesta = [];
            try {
                $dataAnterior = Atm::find($id);
                if ($atm->save()) {
                    $data = [];
                    $data['id'] = $atm->id;

                    if ($dataAnterior->owner_id !== $request->owner_id) {
                        $pos = Pos::where('atm_id', $id)->first();
                        if (!empty($pos)) {
                            \DB::table('points_of_sale')
                                ->where('atm_id', $id)
                                ->update([
                                    'owner_id' => $request->owner_id,
                                ]);
                        }
                    }

                    $aplicaciones = Applications::where('active', true)
                        ->get()
                        ->pluck('name', 'id');

                    $data['applications'] = [];
                    foreach ($aplicaciones as $applicationId => $texto) {
                        $valor = [];
                        $valor['id'] = $applicationId;
                        $valor['text'] = $texto;
                        $data['applications'][] = $valor;
                    }


                    \Log::info("Atm actualizado correctamente11");
                    $respuesta['mensaje'] = 'Actualizado correctamente';
                    $respuesta['tipo'] = 'success';
                    $respuesta['data'] = $data;
                    $respuesta['url'] = route('atm.update', [$atm->id]);
                    return $respuesta;
                }
            } catch (\Exception $e) {
                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Error al actualizar atm';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }
        } else {
            if (!$atm->save()) {
                \Log::warning("Error updating the Atm data. Id: {$atm->id}");
                Session::flash('message', 'Error al actualziar el registro');
                return redirect('atm');
            }
            $message = 'Actualizado correctamente';
            Session::flash('message', $message);
            return redirect('atm');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!$this->user->hasAccess('atms.delete')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
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
        } else {
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

        $response = [
            'error' => false,
            'message' => ''
        ];

        if (!$this->user->hasAccess('atms.delete')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );

            $response['error'] = true;
            $response['message'] = 'No tienes permiso para eliminar el cajero.';
        } else {
            try {
                \DB::table('atms')
                    ->where('id', $id)
                    ->update([
                        'deleted_at' => Carbon::now()
                    ]);

                \Log::info("ATM con id: #$id con soft-delete.");

                $response['message'] = "Cajero número $id eliminado.";
            } catch (\Exception $e) {
                $error_detail = [
                    'exception' => $e->getMessage(),
                    'file' => $e->getFile(),
                    'class' => __CLASS__,
                    'function' => __FUNCTION__,
                    'line' => $e->getLine()
                ];

                \Log::error("\nError en función: " . __FUNCTION__ . ", \nDetalles: " . json_encode($error_detail));

                $response['error'] = true;
                $response['message'] = 'Error al intentar eliminar el cajero';
            }
        }

        return $response;
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

    /**
     * Service required by the ATM machine
     * Returns current active application screens, objects and properties from the given ATM
     * @param id
     * @return Response
     */
    public function getApplicationInterface($id, Request $request)
    {

        $secret =  $request->headers->get('api-key');
        $key = env('APP_SHA1');
        if ($secret !== $key) {
            $response['error']      = true;
            $response['message']    = 'Not found - Unauthorized access  attempt';
            $response['message_user']    = 'Not found';
            \Log::warning($response);
            return $response;
        }

        $atm = Atm::find($id);


        if ($atm->type <> 'da') {
            $app = Atm::find($id)->activeApplication();
            $application =  Applications::find($app->id);

            //$screens = \DB::table('screens')->where('application_id',$app->id)
            $screens = \DB::table('screens')
                ->where('application_id', 1)
                ->orWhere('application_id', $app->id)
                ->orWhere('application_id', null)->orderBy('id', 'asc')->get();

            foreach ($screens as $screen) {
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
                    ->select('screen_objects.id', 'screen_objects.name', 'location_x', 'location_y', 'version_hash', 'screen_id', 'object_type_id', 'key')
                    ->join('object_types', 'screen_objects.object_type_id', '=', 'object_types.id')
                    ->where('screen_id', $screen->id)
                    ->orderBy('id', 'asc')
                    ->get();

                $screens_object_desc = [];
                foreach ($screens_objects as $screens_object) {
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
                    foreach ($object_properties as $object_property) {
                        $object_properties_desc["key"] = $object_property->key;
                        $object_properties_desc["value"] = $object_property->value;
                        $object_properties_desc["object_property_id"] = $object_property->object_property_id;
                        $screens_object_desc["properties"][] = $object_properties_desc;
                    }
                    $screens_desc["objects"][] = $screens_object_desc;
                }


                $pantallas[] = $screens_desc;
            }
        } else {
            $pantallas = [];
        }



        //OBTENER DATOS PDV
        $pdv = \DB::table('points_of_sale')->select('description')->where('atm_id', $atm->id)->first();
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

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function formStep()
    {
        if (!$this->user->hasAccess('atms.add|edit')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $public_key = Str::random(40);
        $private_key = Str::random(40);

        $owners = Owner::orderBy('name')->get()->pluck('name', 'id');
        $branches = Branch::pluck('description', 'id');
        // TODO get seller type fron ONDANET
        $sellerType['1'] = 'Testing Seller type';

        $users = User::all()->pluck('description', 'id');
        $users->prepend('Asignar usuario', '0');
        $user_id = 0;

        $groups = Group::pluck('description', 'id', 'ruc');

        $grupo = [];

        $voucherTypes = VoucherType::orderBy('id')->get()->pluck('description', 'id');

        $atm_code = \DB::table('atms')
            ->selectRaw('code')
            ->orderBy('created_at', 'desc')
            ->first();
        $departamentos = \DB::table('departamento')->pluck('descripcion', 'id');
        $ciudades = \DB::table('ciudades')->pluck('descripcion', 'id');
        $barrios = \DB::table('barrios')->pluck('descripcion', 'id');

        $data = [
            //step new atm
            'public_key'    => $public_key,
            'private_key'   => $private_key,
            'owners'        => $owners,
            'atm_code' => $atm_code->code += 1,
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
            'ciudades' => $ciudades,
            'barrios' => $barrios,

            // step new voucher
            'voucherTypes' => $voucherTypes,
            'atm_parts' => 0,
        ];

        //\Log::debug('ITEMS:', [$data]);

        return view('atm.form_step', $data);
    }

    /**
     * Validate atm code.
     *
     * @return \Illuminate\Http\Response
     */
    public function checkCode(Request $request)
    {
        if ($request->ajax()) {
            $parametros = $request;
            $data = \DB::table('atms')->where(function ($query) use ($parametros) {
                $query->where('code', $parametros->get('code'));
                if ($parametros->get('id') != null) {
                    $query->where('id', '<>', $parametros->get('id'));
                }
            })->count();

            if ($data < 1) {
                $valido = "true";
            } else {
                $valido = "false";
            }

            return $valido;
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function params($atmId, Request $request)
    {
        //
        if (!$this->user->hasAccess('atms.params')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $where = "atm_id = " . $atmId;
        if ($request->get('name')) {
            $where .= " AND atm_param.key LIKE '%" . $request->get('name') . "%'";
        }

        $params = \DB::table('atm_param')
            ->whereRaw($where)
            ->paginate(20);

        return view('atm.params_list', compact('atmId', 'params'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function paramStore($atmId, Request $request)
    {
        if (!$this->user->hasAccess('atms.param_store')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        \DB::beginTransaction();

        try {
            foreach ($request->key as $index => $key) {
                $result = \DB::table('atm_param')
                    ->where('key', '=', $key)
                    ->where('atm_id', '=', $atmId)
                    ->count();

                if ($result > 0) {
                    \DB::table('atm_param')
                        ->where('key', $key)
                        ->where('atm_id', $atmId)
                        ->update([
                            'value' => $request->value[$index]
                        ]);
                } else {
                    \DB::insert('insert into atm_param (atm_id, key, value) values (?, ?, ?)', [$atmId, $key, $request->value[$index]]);
                }
            }
            \DB::commit();
            \Log::info("Parametros agregados correctamente");

            Session::flash('message', 'Parametros agregados correctamente');
            return redirect()->route('atm.params', $atmId);
        } catch (\Exception $e) {
            \DB::rollback();
            \Log::warning($e);
            Session::flash('error_message', 'No se ha podido realizar la operacion');
            return redirect()->route('atm.params', $atmId);
        }
    }

    /**
     * Validate atm code.
     *
     * @return \Illuminate\Http\Response
     */
    public function checkKey(Request $request, $atmId)
    {
        if ($request->ajax()) {
            $data = \DB::table('atm_param')
                ->where('key', $request->key)
                ->where('atm_id', $atmId)
                ->count();
            if ($data < 1) {
                $valido = "true";
            } else {
                $valido = "false";
            }

            return $valido;
        }
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function parts($atmId, Request $request)
    {
        //
        if (!$this->user->hasAccess('atms.parts')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $where = "atm_id = " . $atmId;
        if ($request->get('name')) {
            $where .= " AND atms_parts.nombre_parte LIKE '%" . $request->get('name') . "%'";
        }

        $parts = \DB::table('atms_parts')
            ->whereRaw($where)
            ->orderBy('tipo_partes', 'asc')
            ->orderBy('nombre_parte', 'asc')
            ->paginate(20);

        return view('atm.parts_list', compact('atmId', 'parts'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function partsUpdate($atmId, Request $request)
    {
        if (!$this->user->hasAccess('atms.parts_update')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        // dd($request->all());
        \DB::beginTransaction();

        try {
            foreach ($request->denominacion as $index => $denominacion) {
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
            return redirect()->route('atm.parts', $atmId);
        } catch (\Exception $e) {
            \DB::rollback();
            \Log::warning($e);
            Session::flash('error_message', 'No se ha podido realizar la operacion');
            return redirect()->route('atm.parts', $atmId);
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function updateGooddeals(Request $request)
    {
        if (!$this->user->hasAccess('atms.update_gooddeal')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $datosExistentes = \DB::table('params')
            ->where('key', '=', 'gooddeals')
            ->first();

        $instancias = \DB::table('promotions_instances')->pluck('description', 'key');

        $data = [
            'instancias' => $instancias,
        ];

        return view('atm.update_gooddeals')->with('data', $data);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function lastUpdateGooddeals(Request $request)
    {
        if (!$this->user->hasAccess('atms.update_gooddeal')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $tmp_file = '';

        \DB::beginTransaction();
        try {

            $fecha = date('Y-m-d', strtotime(str_replace('/', '-', $request->last_update))) . ' 00:00:00';

            $session_id = '';
            $last_update = $fecha; //Carbon::now()->startOfMonth()->format('m/d/Y');
            $channel = 1;
            $index = 0;
            $range = 100;
            $version = 'null';
            $atm_id = null;
            $store_reference = '';


            $atms = \DB::connection('eglobalt_pro')->table('atm_services_credentials')
                ->where('service_id', 24)
                ->where('password', $request->instancia)
                ->where('source_id', null)
                ->get();

            $atm_success_count = 0;
            $atm_fail_count = 0;

            // Create new zip archive
            $zip = new ZipArchive();
            $tmp_file = '../public/Goodeals/imagenes_cupones.zip';
            $zip->open($tmp_file, ZipArchive::CREATE);

            if (empty($atms)) {
                \Log::info('GoodDeals | no existen atms para esta instancia | Info ');
                Session::flash('message', 'No existen atms para esta instancia');
                $tmp_file = null;

                return redirect()->route('gooddeals.update')->with(['tmp_file' => $tmp_file]);
            }

            foreach ($atms as $atm) {
                $responsePromotions = $this->getInboxPromotionsv2($session_id, $last_update, $channel, $index, $range, $version, $atm->atm_id);

                $responseImages = $this->getInboxPromotionsWithImages($session_id, $last_update, $channel, $store_reference, $index, $range, $version, $atm->atm_id, $zip);

                if ($responsePromotions == false && $responseImages == false) {
                    $atm_fail_count++;
                    \Log::info('GoodDeals - Hubo un fallo en la descarga de promociones | Atm_id ' . $atm->atm_id . ' | error_promociones: ' . $responsePromotions . ' - error_imagenes: ' . $responseImages);
                } else {
                    $atm_success_count++;
                    \Log::info('GoodDeals - Promociones actualizadas para Atm_id ' . $atm->atm_id);
                }
            }

            \Log::info('GoodDeals | proceso de descarga de promociones culminado | Exitosos ' . $atm_success_count . ' con error ' . $atm_fail_count);

            // Se registra la nueva fecha en la base de datos
            $datoExistente = \DB::table('params')
                ->where('key', '=', 'gooddeals')
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

            if (file_exists($tmp_file)) {
                $data['body'] = 'Las promociones fueron actualizadas correctamente, no hay imagenes para descargar.';
                Mail::send('mails.gooddeal_alert', $data, function ($message) use ($user_name, $user_email, $tmp_file) {
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
            $data['body'] = 'Error al actualizar las promociones <br> ' . $e;
            Mail::send('mails.gooddeal_alert', $data, function ($message) use ($user_name, $user_email, $tmp_file) {
                $message->to($user_email, $user_name)
                    ->cc('operaciones@eglobal.com.py')
                    ->subject('[GoodDeals] Error al actualizar las promociones ');
                //$message->to($user_email, $user_name)->subject('[EGLOBAL - DESARROLLO] Alertas del sistema');
            });
        }

        return redirect()->route('gooddeals.update')->with(['tmp_file' => $tmp_file]);
    }

    /*
     * Returns a response containing the coupons assigned to the shopper with the corresponding promotion image.
     */
    public function getInboxPromotionsv2($session_id, $last_update, $channel, $index, $range, $version, $atm_id)
    {
        $endpoint = "GetInboxPromotions";
        $urlget = $this->url . $endpoint;

        $store_credentials = \DB::connection('eglobalt_pro')->table('atm_services_credentials')->where('atm_id', $atm_id)->where('service_id', 24)->first();
        $store_identity = $store_credentials->user;
        $session_id     = $store_credentials->codEntity;
        $store_data = explode('-', $store_credentials->password);
        $store_tag      =   $store_data[1];
        $store_instance =   $store_data[0];
        $urlget = str_replace('[emblema]', $store_instance, $urlget);

        try {
            $petition = HttpClient::post(
                $urlget,
                [
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
                $error_description = $api_response->ErrorDescription;
                $response_msg = $error_description;
                $response_msg_user = "No se pudo procesar la operación";
                $response = $this->errorData($response_msg, $response_msg_user);
                \Log::warning('[gooddeal]' . $store_identity . " Atm_id " . $atm_id . "| Error " . $error_code . ' - ' . $error_description);
                return false;
            }

            $coupons = $api_response->Coupons;
            $cupones = array();
            $today =  (new \DateTime())->format('Y-m-d');
            foreach ($coupons as $coupon) {
                $start = explode(" ", $coupon->StartingDate);
                $start = str_replace('/', '-', $start[0]);
                $start = (new \DateTime($start))->format('Y-m-d');
                $expiration = explode(" ", $coupon->ExpirationDate);
                $expiration = str_replace('/', '-', $expiration[0]);
                $expiration = (new \DateTime($expiration))->format('Y-m-d');
                //if($today > $start && $today <= $expiration){
                if ($today <= $expiration) {
                    $promo = [
                        'coupon_code'           => $cupon['coupon_code'] = $coupon->CouponCode . $store_tag,
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

            if (!empty($cupones)) {
                \DB::table('gd_promotions')->where('atm_id', $atm_id)->delete();
                \DB::table('gd_promotions')->insert($cupones);
                \Log::info("Good Deals | Rows insertion for: " . count($cupones) . " coupons for Atm_id " . $atm_id);
            } else {
                \Log::info("Good Deals | No hay cupones disponibles");
            }


            return true;
        } catch (\GuzzleHttp\Exception\ConnectException $e) {
            $response_msg = "Tiempo de espera agotado " . $e;
            $response_msg_user = "No se pudo procesar la operación, por favor intente nuevamente";
            $response = $this->errorData($response_msg, $response_msg);
            \Log::warning('[gooddeal]' . $session_id . "| Error " . $e);
            return $response;
        } catch (\Exception $e) {
            $response_msg = "Error no especificado " . $e;
            \Log::warning('[gooddeal]' . $session_id . "| Error " . $e);
        }
    }

    /**
     Get inbox promotions and store images locally
     *
     */

    public function getInboxPromotionsWithImages($session_id, $last_update, $channel, $store_reference, $index, $range, $version, $atm_id, $zip)
    {
        ini_set("max_execution_time", 0);

        $endpoint = "GetInboxPromotions";
        $urlget = $this->url . $endpoint;
        $store_credentials = \DB::connection('eglobalt_pro')->table('atm_services_credentials')->where('atm_id', $atm_id)->where('service_id', 24)->first();
        $store_identity = $store_credentials->user;
        $session_id     = $store_credentials->codEntity;
        $store_data = explode('-', $store_credentials->password);
        $store_instance =   $store_data[0];
        $urlget = str_replace('[emblema]', $store_instance, $urlget);
        $img_path = str_replace('[emblema]', $store_instance, $this->url);
        try {
            $petition = HttpClient::post(
                $urlget,
                [
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

                foreach ($coupons as $coupon) {
                    $expiration = explode(" ", $coupon->ExpirationDate);
                    $expiration = str_replace('/', '-', $expiration[0]);
                    $expiration = (new \DateTime($expiration))->format('Y-m-d');
                    if ($today <= $expiration) {
                        $coupon_identity = $coupon->CouponIdentity;
                        $url = $img_path . 'GetPromotionImage?session=' . $session_id . '&couponIdentity=' . $coupon_identity . '&size=1&lastUpdate=1/1/2016&version=' . $version;
                        \Log::info($url);
                        $filename = $coupon_identity . '.jpg';
                        $file = file_get_contents($url);
                        if (!file_exists('../public/Goodeals/' . $filename)) {
                            \Log::info('Good Deals - Descargando imagen ' . $count . ' ...');
                            $save = file_put_contents('../public/Goodeals/' . $filename, $file);
                        } else {
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
    public function downloadImagesPromotions()
    {
        # send the file to the browser as a download
        header('Content-Type: application/force-download');
        header('Content-Disposition: inline; filename="imagenes_cupones.zip"');
        header('Content-Transfer-Encoding: binary');
        readfile(public_path() . '/Goodeals/imagenes_cupones.zip');
        unlink(public_path() . '/Goodeals/imagenes_cupones.zip');
    }

    /*
    *
    *
    * Download zip archive with promotions images gooddeals
    */
    public function getLastUpdateGooddeals(Request $request)
    {
        if ($request->ajax()) {
            $datosExistentes = \DB::table('promotions_instances')
                ->where('key', '=', $request->instancia_id)
                ->first();

            if (!empty($datosExistentes->last_update)) {
                $fecha = date('d/m/Y', strtotime($datosExistentes->last_update));
            } else {
                $fecha = '';
            }

            return $fecha;
        }
    }

    /*
    *
    * Get ciudades json
    */
    public function getCiudades(Request $request)
    {
        if ($request->ajax()) {
            $ciudades = \DB::table('ciudades')
                ->where('departamento_id', $request->get('departamento_id'))
                ->pluck('descripcion', 'id');

            $ciudades_select = '<option value="">Seleccione una opción</option>';
            foreach ($ciudades as $ciudad_id => $ciudad) {
                $ciudades_select .= '<option value="' . $ciudad_id . '">' . $ciudad . '</option>';
            }

            return $ciudades_select;
        }
    }

    /*
    *
    * Get barrios json
    */
    public function getBarrios(Request $request)
    {
        if ($request->ajax()) {
            \Log::info('get barrios');
            $barrios = \DB::table('barrios')
                ->where('ciudad_id', $request->get('ciudad_id'))
                ->pluck('descripcion', 'id');

            $barrios_select = '<option value="">Seleccione una opción</option>';
            foreach ($barrios as $barrio_id => $barrio) {
                $barrios_select .= '<option value="' . $barrio_id . '">' . $barrio . '</option>';
            }

            return $barrios_select;
        }
    }

    public function Procesar_reactivacion(Request $request)
    {
        if (!$this->user->hasAnyAccess('atms.add|edit')) {
            \Log::warning(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            $response = [
                'error'     => true,
                'message'   => 'Acceso no Autorizado'
            ];
            return $response;
        }

        $atm_id = $request->_atm_id;
        $comments = $request->txtDescription;

        if ($comments == '') {
            $response['error'] = true;
            $response['message'] = 'Campo comentario es requerido';

            return $response;
        }

        $atm = Atm::find($atm_id);
        $atm->atm_status = 0;
        $atm->save();

        \Log::info('ATM Reactivado: ' . $atm_id . ' - Autorizado por: ' . $this->user->username . ' el ' . Carbon::now());

        $notifications = \DB::table('notifications')
            ->where('atm_id', $atm_id)
            ->where('notification_type', 1)
            ->where('message', 'ALERTA DE SEGURIDAD - Acceso no autorizado')
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
        $response = $services->cierreYapertura($atm_id, true, $atm_status[1]->comments, $atm_status[1]->status);

        $response['error'] = false;
        $response['message'] = 'ATM Actualizado correctamente.';

        return $response;
    }

    public function enable_arqueo_remoto(Request $request)
    {
        if (!$this->user->hasAnyAccess('atms.add|edit')) {
            \Log::warning(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
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


        if ($value == true) {
            \Log::info('ATM Habilitado para arqueo remoto: ' . $atm_id . ' - Autorizado por: ' . $this->user->username . ' el ' . Carbon::now());
        }

        if ($value == false) {
            \Log::info('ATM bloqueado para arqueo remoto: ' . $atm_id . ' - Autorizado por: ' . $this->user->username . ' el ' . Carbon::now());
        }

        $response['error'] = false;
        return $response;
    }

    public function enable_grilla_tradicional(Request $request)
    {
        if (!$this->user->hasAnyAccess('atms.add|edit')) {
            \Log::warning(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
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


        if ($value == true) {
            \Log::info('ATM con grilla tradicional habilirada: ' . $atm_id . ' - Autorizado por: ' . $this->user->username . ' el ' . Carbon::now());
        }

        if ($value == false) {
            \Log::info('ATM con grilla tradicional deshabilitada: ' . $atm_id . ' - Autorizado por: ' . $this->user->username . ' el ' . Carbon::now());
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
        if (!$this->user->hasAccess('housing.add|edit')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $atm = Atm::find($atm_id);

        $housings = Housing::leftjoin('atms', 'housing.id', '=', 'atms.housing_id')
            ->where('atms.id', null)
            ->pluck('serialnumber', 'housing.id');

        $housings->prepend('Asignar housing', '0');

        if (!empty($atm->housing_id)) {
            $housing_id = $atm->housing_id;
            $housing = Housing::find($housing_id);
            $housings->prepend($housing->serialnumber, $housing_id);
        } else {
            $housing_id = null;
        }

        return view('atm.housing', compact('atm_id', 'housings', 'housing_id'));
    }

    public function store_housing($atm_id, Request $request)
    {
        if (!$this->user->hasAccess('housing.add|edit')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        \Log::info($request->all());

        try {
            $housing_asignado = Atm::where('housing_id', $request->housing_id)->first();
            if (empty($housing_asignado)) {
                $Atm = Atm::find($atm_id);
                $Atm->housing_id = $request->housing_id;
                $Atm->save();

                \Log::info("Housing #" . $request->housing_id . " asignado al atm #" . $atm_id . " correctamente");
                Session::flash('message', "Housing # " . $request->housing_id . " asignado al atm #" . $atm_id . " correctamente");
                return redirect('atm');
            }
        } catch (\Exception $e) {
            \Log::critical($e->getMessage());
            $respuesta['mensaje'] = 'Error al asignar housing al Atm';
            $respuesta['tipo'] = 'error';
            \Log::info($respuesta);
            Session::flash('error_message', 'Ocurrio un error al intentar asignar el housing al ATM');
            return redirect()->back()->withInput();
        }
    }

    public function exportAtm($group_id, $owner_id, $tipo_id, $name)
    {

        if (!$this->user->hasAccess('atms')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        // $atms = \DB::table('atms')
        //     ->select(
        //         'atms.id',
        //         'atms.code as codigo',
        //         'atms.name as nombre',
        //         'owners.name as red',
        //         'branches.address',
        //         'branches.latitud',
        //         'branches.longitud',
        //         'atms.atm_status as estado',
        //         'ciudades.descripcion as ciudad',
        //         'barrios.descripcion',
        //         'departamento.descripcion as departamento',
        //         'branches.more_info',
        //         'atms.last_request_at'
        //     )
        //     ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'atms.id')
        //     ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
        //     ->join('owners', 'owners.id', '=', 'atms.owner_id')
        //     ->leftjoin('barrios', 'barrios.id', '=', 'branches.barrio_id')
        //     ->leftjoin('ciudades', 'ciudades.id', '=', 'barrios.ciudad_id')
        //     ->leftjoin('departamento', 'departamento.id', '=', 'ciudades.departamento_id')
        //     ->where('atms.deleted_at', null)
        //     ->where('atms.owner_id', '!=', 18)
        //     ->where('atms.type', '!=', 'da')
        //     ->where(function ($query) use ($group_id) {
        //         if (!empty($group_id) && $group_id <> 0) {
        //             $query->where('branches.group_id', $group_id);
        //         }
        //     })
        //     ->where(function ($query) use ($owner_id) {
        //         if (!empty($owner_id) && $owner_id <> 0) {
        //             $query->where('atms.owner_id', $owner_id);
        //         }
        //     })
        //     ->get();

        // foreach ($atms as $atm) {

        //     $now  = Carbon::now();
        //     $end =  Carbon::parse($atm->last_request_at);
        //     $elasep = $now->diffInMinutes($end);

        //     if ($atm->estado == -1) {
        //         $atm->estado = '25%';
        //     }

        //     if ($atm->estado == -2) {
        //         $atm->estado = '50%';
        //     }

        //     if ($atm->estado == -3) {
        //         $atm->estado = '75%';
        //     }

        //     if ($atm->estado == -4) {
        //         $atm->estado = '100%';
        //     }

        //     if (($atm->estado == 0 && $elasep <= 20) || $atm->id == 153) {
        //         $atm->estado = 'Online';
        //     } else {
        //         if ($atm->estado <> 0 && $atm->estado <> 80 && $atm->id <> 153) {
        //             $atm->estado = 'Suspendido';
        //         } else {
        //             if ($atm->estado == 80) {
        //                 $atm->estado = 'ACCESO NO AUTORIZADO';
        //             } else {
        //                 $atm->estado = 'Offline';
        //             }
        //         }
        //     }

        //     $atm->last_request_at = '';
        // }

        $atms = \DB::table('atms as a')
            ->select(
                'a.id',
                'a.code as codigo',
                'a.name as nombre',
                'o.name as red',
                'b.address',
                'b.latitud',
                'b.longitud',
                'a.atm_status as estado',
                'a.atm_status as progreso',
                'c.descripcion as ciudad',
                'ba.descripcion',
                'd.descripcion as departamento',
                'b.more_info',
                'b.phone as telefono',
                'u.description as ejecutivo',
                'u2.description as operativo',
                'a.last_request_at'
            )
            ->join('owners as o', 'o.id', '=', 'a.owner_id')
            ->join('points_of_sale as pos', 'a.id', '=', 'pos.atm_id')
            ->join('branches as b', 'b.id', '=', 'pos.branch_id')
            ->join('business_groups as bg', 'bg.id', '=', 'b.group_id')
            ->leftjoin('barrios as ba', 'ba.id', '=', 'b.barrio_id')
            ->leftjoin('ciudades as c', 'c.id', '=', 'ba.ciudad_id')
            ->leftjoin('departamento as d', 'd.id', '=', 'c.departamento_id')
            ->leftjoin('users as u', 'u.id', '=', 'b.executive_id')
            ->leftjoin('users as u2', 'u2.id', '=', 'b.user_id')
            ->whereRaw("a.deleted_at is null")
            ->where('a.owner_id', '!=', 18)
            ->where('a.type', '!=', 'da')

            ->where(function ($query) use ($group_id) {
                if (!empty($group_id) && $group_id <> 0) {
                    $query->where('b.group_id', $group_id);
                }
            })

            ->where(function ($query) use ($owner_id) {
                if (!empty($owner_id) && $owner_id <> 0) {
                    $query->where('a.owner_id', $owner_id);
                }
            })

            ->where(function ($query) use ($tipo_id) {
                if (!empty($tipo_id) && $tipo_id <> 0) {
                    $query->whereRaw("bg.manager_id = $tipo_id");
                }
            })

            ->where(function ($query) use ($name) {
                if (!empty($name) && $name <> '') {
                    $query->whereRaw("a.name ilike '%$name%'");
                }
            })

            ->get();


        foreach ($atms as $atm) {

            $now  = Carbon::now();
            $end =  Carbon::parse($atm->last_request_at);
            $elasep = $now->diffInMinutes($end);

            if ($atm->estado == 1) {
                $atm->progreso = 'Suspendido';
            }

            if ($atm->estado == -1) {
                $atm->progreso = 'Pendiente de regularizar';
            }

            if ($atm->estado == -2) {
                $atm->progreso = 'Pendiente de regularizar';
            }

            if ($atm->estado == -3) {
                $atm->progreso = 'Pendiente de regularizar';
            }

            if ($atm->estado == -4) {
                $atm->progreso = 'Pendiente de regularizar';
            }
            if ($atm->estado == -5 || $atm->estado == -6) {
                $atm->progreso = 'Área Comercial';
            }

            if ($atm->estado == -7 || $atm->estado == -8) {
                $atm->progreso = 'Área de Legales';
            }
            if ($atm->estado == -14) {
                $atm->progreso = 'Área de sistemas - Antell';
            }
            if ($atm->estado == -9) {
                $atm->progreso = 'Área de Fraude - Antell';
            }

            if ($atm->estado == -10) {
                $atm->progreso = 'Área de Contabilidad';
            }
            if ($atm->estado == -11) {
                $atm->progreso = 'Área de Logísticas';
            }
            if ($atm->estado == -12) {
                $atm->progreso = 'Área de sistemas - Eglobalt';
            }


            if (($atm->estado == 0 && $elasep <= 20) || $atm->id == 153) {
                $atm->estado = 'Online';
                $atm->progreso = 'Online';
            } else {
                if ($atm->estado <> 0 && $atm->estado <> 80 && $atm->id <> 153) {
                    $atm->estado = 'Suspendido';
                } else {
                    if ($atm->estado == 80) {
                        $atm->estado = 'ACCESO NO AUTORIZADO';
                        $atm->progreso = 'ACCESO NO AUTORIZADO';
                    } elseif ($atm->estado == -5 || $atm->estado == -6) {
                        $atm->progreso = 'Área Comercial';
                    } elseif ($atm->estado == -7 || $atm->estado == -8) {
                        $atm->progreso = 'Área de Legales';
                    } elseif ($atm->estado == -14) {
                        $atm->progreso = 'Área de sistemas - Antell';
                    } elseif ($atm->estado == -9) {
                        $atm->progreso = 'Área de Fraude - Antell';
                    } elseif ($atm->estado == -10) {
                        $atm->progreso = 'Área de Contabilidad';
                    } elseif ($atm->estado == -11) {
                        $atm->progreso = 'Área de Logísticas';
                    } elseif ($atm->estado == -12) {
                        $atm->progreso = 'Área de sistemas - Eglobalt';
                    } else {
                        $atm->estado = 'Offline';
                        $atm->progreso = 'Offline';
                    }
                }
            }

            $atm->last_request_at = '';
        }

        $cajeros = json_decode(json_encode($atms), true);

        $filename = 'atms_' . time();

        $columnas = array(
            // '#', 'Código', 'Nombre', 'Red', 'Dirección', 'Latitud', 'Longitud', 'Estado', 'Ciudad', 'Barrio', 'Departamento', 'Horario de Atención'
            '#', 'Codigo', 'Nombre', 'Red', 'Direccion', 'Latitud', 'Longitud', 'Estado', 'Progreso', 'Ciudad', 'Barrio', 'Departamento', 'Horario de Atención', 'Telefono', 'Ejecutivo responsable', 'Operativo responsable'
        );

        

        if ($cajeros && !empty($cajeros)) {
            // Excel::download($filename, function ($excel) use ($cajeros) {
            //     $excel->sheet('Terminales', function ($sheet) use ($cajeros) {
            //         $sheet->rows($cajeros, false);
            //         $sheet->prependRow(array(
            //             // '#', 'Código', 'Nombre', 'Red', 'Dirección', 'Latitud', 'Longitud', 'Estado', 'Ciudad', 'Barrio', 'Departamento', 'Horario de Atención'
            //             '#', 'Codigo', 'Nombre', 'Red', 'Direccion', 'Latitud', 'Longitud', 'Estado', 'Progreso', 'Ciudad', 'Barrio', 'Departamento', 'Horario de Atención', 'Telefono', 'Ejecutivo responsable', 'Operativo responsable'

            //         ));
            //     });
            // })->export('xls');

            $excel = new ExcelExport($cajeros,$columnas);

            return Excel::download($excel, $filename . '.xls')->send();
            
        } else {
            Session::flash('error_message', 'No existen parametros para exportar');
        }
    }

    public function atm_status_history()
    {

        $report = new AtmStatusServices;
        $result = $report->getStatusHistory();

        return view('reporting.index')->with($result);
    }

    public function atm_status_history_search()
    {
        $input = \Request::all();

        $report = new AtmStatusServices;
        $result = $report->getStatusHistorySearch($input);
        return view('reporting.index')->with($result);
    }

    public function searchBranches(Request $request)
    {

        try {

            $owner_id = $request['id'];
            $tipo_id  = $request['tipo_id'];
            $manager = 1;

            if ($owner_id == 0) {
                $where = "atms.owner_id in (2,11,16)";
            } elseif ($owner_id == -1) {
                $where = "atms.owner_id = 16";
            } else {
                $where = "atms.owner_id = $owner_id";
            }

            $branches = Branch::orderBy('description')
                ->join('business_groups', 'business_groups.id', '=', 'branches.group_id')
                ->join('points_of_sale', 'points_of_sale.branch_id', '=', 'branches.id')
                ->join('atms', 'atms.id', '=', 'points_of_sale.atm_id')
                ->select('branches.id', 'branches.description')
                ->where(function ($query) use ($tipo_id, $manager) {
                    if (!empty($tipo_id) &&  $tipo_id <> 0 && $tipo_id <> -1) {
                        if ($tipo_id == 1) {
                            $query->where('business_groups.manager_id', '=', $manager);
                        } else {
                            $query->where('business_groups.manager_id', '<>', $manager);
                        }
                    }
                })
                ->whereRaw($where)
                ->whereNotNull('atms.last_token')
                ->whereNull('atms.deleted_at')
                ->get();

            return $branches;
        } catch (\Exception $e) {
            $error_detail = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];

            \Log::error("Ocurrió un error. Detalles:");
            \Log::error($error_detail);
        }
    }

    public function searchAtm(Request $request)
    {

        try {

            if ($request->id <> 0) {
                $where = "branches.id = $request->id";
            } else {
                $where = "owner_id in (2,11,16)";
            }

            $atms = \DB::table('atms')
                ->select('atms.id', 'atms.name as description')
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'atms.id')
                ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->whereRaw($where)
                ->get();
            return $atms;
        } catch (\Exception $e) {
            $error_detail = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];

            \Log::error("Ocurrió un error. Detalles:");
            \Log::error($error_detail);
        }
    }
}
