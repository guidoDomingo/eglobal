<?php

namespace App\Http\Controllers;

use Session;
use HttpClient;

use Carbon\Carbon;
use App\Models\User;
use App\Http\Requests;
use App\Models\Barrio;
use App\Models\Branch;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CaracteristicaSucursal;


class BranchController extends Controller
{

    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($ownerId, Request $request)
    {
        if (!$this->user->hasAccess('branches')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $name = $request->get('name');
        $branches = Branch::filterAndPaginate($ownerId, $name);
        return view('branches.index', compact('branches', 'name', 'ownerId'));
    }

    public function index_group($groupId, Request $request)
    {
        $users = \Sentinel::getUser();
        if (!$this->user->hasAccess('branches')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $description = $request->get('description');
        $branches = Branch::filterPaginate($groupId, $description);
        return view('branches.index_group', compact('branches', 'description', 'groupId','users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($ownerId)
    {
        if (!$this->user->hasAccess('branches.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $users = User::all()->pluck('description','id');
        $users->prepend('Asignar usuario','0');
        $user_id = 0;

        $barrios = Barrio::all()->pluck('descripcion','id');
        $barrios->prepend('Asignar barrio','0');
        $barrio_id = 0;

        $executives = User::where('manager_eglobalt', true)->pluck('description','id');
        $executives->prepend('Asignar ejecutivo','0');
        $executive_id = 0;


        return view('branches.create', compact('ownerId','users','user_id','executives','executive_id','barrios','barrio_id'));

    }

    public function create_group($groupId)
    {
        if (!$this->user->hasAccess('branches.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $users = User::all()->pluck('description','id');
        $users->prepend('Asignar usuario','0');
        $user_id = 0;

        $branches = Branch::orderBy('description')
            ->where('group_id',null)
            ->where('owner_id','!=',18)
            ->get()
            ->pluck('description','id')
        ->toArray();

        ksort($branches);

        return view('branches.create_group', compact('groupId','users','user_id','branches'));

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($ownerId, Request $request)
    {
        if (!$this->user->hasAccess('branches.add|edit') && !\Sentinel::getUser()->inRole('atms_v2.area_comercial')) {

            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();

        /*$encontrado = Branch::where('owner_id', $ownerId)->where('branch_code', $request->branch_code)->get();
        if (count($encontrado) > 0) {
            $message = 'Ya existe una Sucursal con el mismo código: ' . $request->branch_code;
            Session::flash('error_message', $message);
            return redirect()->back()->withInput();
        }*/
        $this->user = \Sentinel::getUser()->id;
        $username = \Sentinel::getUser()->username;
        $branch = new Branch;
        $branch->fill(['created_by' => $this->user]);
        $branch->fill(['owner_id' => $ownerId]);
        $branch->fill($request->all());
        $branch->branch_code = $request->branch_code;
        $branch->more_info = $request->more_info;
        $branch->caracteristicas_id = $request->caracteristicas_id;
        $branch->executive_id = $request->executive_id;

     
        if($ownerId == 16){
            $branch->description = 'Miniterminal - '.$request->description;
        }else if($ownerId == 21){
            $branch->description = 'Nanoterminal - '.$request->description;
        }else if($ownerId == 25){
            $branch->description = 'FK - '.$request->description;
        }else{
            $branch->description = $request->description;
        }

        if($request->user_id == -1){
            $branch->user_id = null;
        }else{
            $branch->user_id = $request->user_id;
        }

        $branch->barrio_id = $request->barrio_id;
    
        if($request->ajax()){
            $respuesta = [];
            try{
                if ($branch->save()) {

                    //Actualizar el encargado del ATM, atms->related_user
                    $atm_id = $request->atm_id;
                    $encargado = $request->related_id;

                    $update_atm =\DB::table('atms')
                    ->where('id',  $atm_id)
                    ->update([
                        'related_user' => $encargado
                    ]);
                    \Log::info("[ALTAS]Encargado de atm Acutalizado. ATM_ID:".$atm_id." related_user: ".$encargado);


                    $data = [];
                    $data['id'] = $branch->id;
                    $data['description'] = $branch->description;

                    $owner=\DB::table('owners')->where('id',$ownerId)->first();

                    $tdp= $this->insert_tdp($branch->description, $owner->name, $branch->address, $branch->latitud, $branch->longitud);
                    $alta_quiniela = $this->alta_quiniela($branch->description, $atm_id);
                    \Log::info('[ALTA QUINIELA - RESPONSE:] ', ['response' => $alta_quiniela]);

                    \Log::info("Nuevo sucursal creada");
                    $respuesta['mensaje'] = 'Sucursal creada correctamente';
                    $respuesta['tipo'] = 'success';
                    $respuesta['data'] = $data;
                    \DB::commit();


                    return $respuesta;
                } else {
                    \DB::rollback();
                   \Log::critical($e->getMessage());
                    $respuesta['mensaje'] = 'Error al crear sucursal';
                    $respuesta['tipo'] = 'error';
                    return $respuesta;
                }
            }catch (\Exception $e){
                \DB::rollback();
                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Error al crear sucursal';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }
        }else{
            if ($branch->save()) {
           
                $message = 'Agregado correctamente';
                Session::flash('message', $message);
                \DB::commit();
                return redirect()->route('owner.branches.index', $ownerId);
            }
        }
    }

    public function store_group($groupId, Request $request)
    {
        if (!$this->user->hasAccess('applications.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = $request->all();
      
        $input['updated_by'] = $this->user->id;
        try
        {
            $branches=\DB::table('branches')
                        ->where('id', $input['branch_id'])
                        ->update([
                            'group_id' => $groupId,
                            'updated_by' => $input['updated_by'],
                            'updated_at' => Carbon::now()
                        ]);
                            
           if($branches)
           {
            \Log::info("Se agrego una nueva sucursal al grupo");
            Session::flash('message', 'Nueva sucursal agregada correctamente');
            return redirect()->route('groups.branches', $groupId);
            //return redirect('groups.branches');
           }else{
            \Log::info("Ha ocurrido un error");
            Session::flash('message', 'Ha ocurrido un error');
            return redirect()->route('groups.branches', $groupId);
           }    
        }
        catch (\Exception $e)
        {
            \Log::warning($e->getMessage());
            Session::flash('error_message', 'Error al publicar actualización');
            return redirect()->back()->with('error', 'Error al publicar actualización');
        } 
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id,$branch)
    {
        if (!$this->user->hasAccess('branches.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $branch = Branch::find($branch);
        $users = User::all()->pluck('description','id');
        $users->prepend('Asignar usuario','0');
        $user_id = $branch->user_id;

        $barrios = Barrio::all()->pluck('descripcion','id');
        $barrios->prepend('Asignar barrio','0');
        $barrio_id = $branch->barrio_id;

        $executives = User::where('manager_eglobalt', true)->pluck('description','id');
        $executives->prepend('Asignar ejecutivo','0');
        $executive_id = $branch->executive_id;

        $data = [
            'branch'    => $branch,
            'ownerId'   => $id,
            'users'     => $users,
            'user_id'   => $user_id,
            'barrios'     => $barrios,
            'barrio_id'   => $barrio_id,
            'executives'   => $executives,
            'executive_id' => $executive_id
        ];
        return view('branches.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id, $branch)
    {
        if (!$this->user->hasAccess('branches.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $ownerId = $id;
        if ($branch = Branch::find($branch)){
            $input = $request->all();
            try{
                $branch->fill($input);
                $branch->fill(['updated_by' => $this->user->id]);
                if($branch->update()){
                    Session::flash('message', 'Sucursal actualizada exitosamente');
                    //return redirect('owner');
                    $branches = Branch::orderBy('id', 'desc')->paginate(10);
                    return view('branches.index', compact('branches','ownerId'));
                }
            }catch (\Exception $e){
                \Log::error("Error updating Branch: " . $e->getMessage());
                Session::flash('error_message','Error al intentar actualizar la sucursal');
                //return redirect('owner');
                $branches = Branch::orderBy('id', 'desc')->paginate(10);
                return view('branches.index', compact('branches','ownerId'));
            }
        }else{
            \Log::warning("Branch not found");
            Session::flash('error_message', 'Sucursal no encontrada');
            //return redirect('owner');
            $branches = Branch::orderBy('id', 'desc')->paginate(20);
            return view('branches.index', compact('branches','ownerId'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($ownerId, $branch_id)
    {
        if (!$this->user->hasAccess('branches.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $message = '';
        $error = '';
        \Log::debug("Attempting to delete a given network");
        if (Branch::find($branch_id)){
                try{
                    if (Branch::destroy($branch_id)){
                        $message =  'Sucursal eliminada correctamente';
                        $error = false;
                    }
                }catch (\Exception $e){
                    \Log::error("Error deleting network: " . $e->getMessage());
                    $message =  'Error al intentar eliminar la sucursal';
                    $error = true;
                }

        }else{
            $message =  'Sucursal no encontrada';
            $error = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);



        
    }

    public function destroy_group($id)
    {
        if (!$this->user->hasAccess('branches.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $message = '';
        $error = '';
        \Log::debug("Intentando eliminando la sucursal ".$id." del grupo");

        if (Branch::find($id)){
                try{
                    \DB::table('branches')
                            ->where('id', $id)
                            ->update([
                                'updated_at' => Carbon::now(),
                                'group_id'  => null,
                                'updated_by'   => $this->user->id
                            ]);
                $error = false;
                $message =  'Sucursal eliminada del grupo exitosamente';

                }catch (\Exception $e){
                    \Log::error("Error deleting network: " . $e->getMessage());
                    $message =  'Error al intentar eliminar la sucursal';
                    $error = true;
                }

        }else{
            $message =  'Sucursal no encontrada';
            $error = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);



        
    }

    public function insert_tdp($sucursal_name,$descripcion,$direccion,$latitud,$longitud){

        try{
            $data= [
                'sucursal_name' => $sucursal_name,
                'descripcion' => $descripcion,
                'direccion' => $direccion,
                'latitud' => $latitud,
                'longitud' => $longitud
            ];
            \Log::info('[ABM - TDP] parametros recibidos:',$data);

            $token='9ZB2F8FPWsbj9QZCEqdyC5tDjvfXKKDV';
            $url = 'https://central.z1.mastarjeta.net/v1/externo/agencia/';

            $lat = str_replace(',','.',$latitud);
            $long = str_replace(',','.',$longitud);


            $petition = HttpClient::post(
                $url, [
                    'connect_timeout' => 180,
                    'headers' => [
                        'Authorization' => 'Token '.$token,
                    ],
                    'json' =>[
                        'nombre'        => $sucursal_name,
                        'descripcion'   => $descripcion,
                        'direccion'     => $direccion,
                        'latitud'       => $lat,
                        'longitud'      => $long
                    ],
                    'verify' => false
                ]
            );

            $result = json_decode($petition->getBody()->getContents());

            if($result){
                
                $last_atm_id = \DB::table('atms')
                ->selectRaw('id')
                ->orderBy('created_at','desc')
                ->first();

                \DB::table('atm_param')->insert([
                    ['atm_id'   => $last_atm_id->id, 'key'  => 'tdp_device_id', 'value' => $result->id_preasignado],
                    ['atm_id'   => $last_atm_id->id, 'key'  => 'pagoPaquetigo', 'value' => 'pagoUnico'],
                    ['atm_id'   => $last_atm_id->id, 'key'  => 'pagoTelefonia', 'value' => 'pagoUnico']
                ]);

                $response['error']          = false;
                $response['message']        = 'Sucursal registrada correctamente en TDP';
                $response['id_preasignado'] = $result->id_preasignado;
                $response['id_atm']         = $last_atm_id->id;
                $response['nombre']         = $result->nombre;
                $response['direccion']      = $direccion;
                $response['latitud']        = $latitud;
                $response['longitud']       = $longitud;

                \Log::info($response);
                return $response;
                
            }else{
                $response['error']          = true;
                $response['message']        = 'Problemas al insertar en TDP';
                \Log::info('[ABM - TDP] error:',$response);
                return $response;
            }
            

        }catch (\GuzzleHttp\Exception\ConnectException $e) {
            $message = 'Error: ' . $e->getCode() . ' - ' . $e->getMessage();
            $message_user = 'El servicio TDP no esta disponible';
            return $this->errorData($message, $message_user);
        }catch (\Exception $e){     
            $response['error'] = true;
            $response['error_code'] = $e->getCode();
            $response['message'] = $e->getMessage();
            \Log::info('[ABM - TDP] error al insertar en tdp:',$response);
            // \Log::warning('Ocurrio un error en insert_tdp: '.$e);
            // return $this->errorData('Error: ' . $e->getMessage(), 'No se pudo realizar la operacion');
            return $response;
        }

    }

    public function store_caracteristicas(Request $request)
    {
        // if (!$this->user->hasAnyAccess('atms_v2.add|edit')) {
        //     \Log::warning('Unauthorized access attempt',
        //         ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
        //     $response = [
        //         'error'     => true,
        //         'message'   => 'Acceso no Autorizado'
        //     ];
        //     return $response;
        // }
        $input = $request->all();

        if(!empty($request->nro_cuenta)){

            $cuenta_bancaria_id = \DB::table('clientes_cuentas_bancarias')->insertGetId([
                'clientes_bancos_id'      => $request->banco_id,
                'clientes_tipo_cuenta_id' => $request->tipo_cuenta,
                'numero'                  => $request->nro_cuenta,
                'created_at'              =>  Carbon::now(),
                'updated_at'              =>  Carbon::now(),
                'deleted_at'              =>  null
            ]);
        }else{
            $cuenta_bancaria_id = null;
        }

        $caracteristica = new CaracteristicaSucursal;
        $caracteristica->canal_id           = $input['canal_id'];
        $caracteristica->categoria_id       = $input['categoria_id'];
        $caracteristica->cta_bancaria_id    = $cuenta_bancaria_id;
        $caracteristica->referencia         = $input['referencia'];
        $caracteristica->accesibilidad      = $input['accesibilidad'];
        $caracteristica->visibilidad        = $input['visibilidad'];
        $caracteristica->trafico            = $input['trafico'];
        $caracteristica->dueño              = $input['dueño'];
        $caracteristica->atendido_por       = $input['atendido_por'];
        $caracteristica->estado_pop         = $input['estado_pop'];
        $caracteristica->permite_pop        = (isset($input['permite_pop'])? true : false);
        $caracteristica->tiene_pop          = (isset($input['tiene_pop'])? true : false);
        $caracteristica->tiene_bancard      = (isset($input['tiene_bancard'])? true : false);
        $caracteristica->tiene_pronet       = (isset($input['tiene_pronet'])? true : false);
        $caracteristica->tiene_netel        = (isset($input['tiene_netel'])? true : false);
        $caracteristica->tiene_pos_dinelco  = (isset($input['tiene_pos_dinelco'])? true : false);
        $caracteristica->tiene_pos_bancard  = (isset($input['tiene_pos_bancard'])? true : false);
        $caracteristica->tiene_billetaje    = (isset($input['tiene_billetaje'])? true : false);
        $caracteristica->tiene_tm_telefonito =(isset($input['tiene_tm_telefonito'])? true : false);
        
        $caracteristica->visicooler          = (isset($input['visicooler'])? true : false);
        $caracteristica->bebidas_alcohol     = (isset($input['bebidas_alcohol'])? true : false);
        $caracteristica->bebidas_gasificadas = (isset($input['bebidas_gasificadas'])? true : false);
        $caracteristica->productos_limpieza  = (isset($input['productos_limpieza'])? true : false);
        $caracteristica->correo              = (isset($input['correo'])? $input['correo'] : NULL);

        if($request->ajax()){
            $respuesta = [];
            try{
                if ($caracteristica->save()) {
                    $data = [];
                    $data['id'] = $caracteristica->id;
                    $data['descripcion'] = 'Caracteristica agregada';
                    \Log::info("nueva caracteristica sucursal creada");

                                     
                    $respuesta['mensaje'] = 'Sucursal creada correctamente';
                    $respuesta['tipo'] = 'success';
                    $respuesta['data'] = $data;

                    return $respuesta;
                } else {
                    \DB::rollback();
                   \Log::critical($e->getMessage());
                    $respuesta['mensaje'] = 'Error al crear la caracteristica';
                    $respuesta['tipo'] = 'error';
                    return $respuesta;
                }
            }catch (\Exception $e){
                \DB::rollback();
                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Error al crear la caracteristica';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }

        }



    }

    public function alta_quiniela($sucursal_name,$atm_id)
    {

        try{
            //Produccion
            $url = 'https://apitdpbiz.tedepasa.com/graphql';
            //Desarrollo
            //$url = 'http://dev.mastarjeta.net:4002/graphql';

            $parameters = array(
                'dispositivo_id'  => intval($atm_id),
                'umn_id'          => 182,
                'sucursal'        => $sucursal_name,
            );
        
            $request_data = array(
                'query' => 'mutation { altaUsuarioTerminal(dispositivo_id:' . json_encode($parameters['dispositivo_id']) . ', umn_id:' . json_encode($parameters['umn_id']). ', sucursal:' .json_encode($parameters['sucursal']). ' ) }'
            );
            \Log::info('[ALTA QUINIELA - REQUEST_DATA] ',['request_data' => $request_data]);

            $petition = (new Client)->request('post', $url, [
                'headers' => ['Content-Type' => 'application/json'],
                'body'    => json_encode($request_data),
                'verify' => false
        
            ]);

            $result = json_decode($petition->getBody()->getContents());
            \Log::info('[ALTA_QUINIELA - RESULT] ', ['post_data' => $request_data, 'result' => $result]);
      
            if (array_key_exists('errors', $result)) {
        
                $response['operation']            = 'Alta de quiniela';
                $response['error']                = true;
                $response['message']              = $result->errors[0]->message;
                $response['data']                 = $result->data->quinielaEglobal;
           
                \Log::info('[ALTA QUINIELA - ERROR] ',['request' => $parameters, 'result' => json_encode($result)]);
                return $response;

              } elseif (array_key_exists('data', $result)) {
        
                $response['operation']                = 'Alta de quiniela';
                $response['error']                    = false;
                $response['message']                  = 'Alta quiniela exitoso';
                $response['data']['id']               = $result->data->id;
                $response['data']['numero']           = $result->data->numero;
                \Log::info('[ALTA QUINIELA - SUCCESS] ',['request' => $parameters, 'result' => json_encode($result)]);
               return $response;

              }

        }catch (\GuzzleHttp\Exception\ConnectException $e) {
            
            $response['operation']            = 'Alta de quiniela';
            $response['petition']             = $parameters;
            $response['error']                = true;
            $response['message_user']         = 'El servicio TEDEPASA no esta disponible';
            $response['message']              = $e->getMessage();

            return $response;
        }catch (\Exception $e){     
            $response['operation']            = 'Alta de quiniela';
            $response['petition']             = $parameters;
            $response['error']                = true;
            $response['message_user']         = 'Erro general al dar de alta la quiniela';
            $response['message']              = $e->getMessage();

            \Log::warning('Ocurrio un error al agregar el alta de quiniela: '.$e);
            
            return $response;
        }

    }
}
