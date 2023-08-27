<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Branch;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Requests\GroupRequest;
use App\Services\OndanetServices;
use App\Models\Group;
use Carbon\Carbon;

use Session;

class GroupController extends Controller
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
    public function index(Request $request)
    {
        if (!$this->user->hasAccess('group')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $description = $request->get('description');
        $groups = Group::filterAndPaginate($description);
        //$owners = Owner::paginate(10);
        return view('groups.index', compact('groups', 'description'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!$this->user->hasAccess('group.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        return view('groups.create');
    }

    public function branches($groupId)
    {
        if (!$this->user->hasAccess('branches')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
    }

    

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(GroupRequest $request)
    {
        if (!$this->user->hasAccess('group.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = $request->except('token_');
        $input['created_by'] = $this->user->id;
        \DB::beginTransaction();
            try{
                // TODO Ondanet
                if ($group =Group::create($input)){

                    $insert_ondanet  = new OndanetServices();
                    $response = $insert_ondanet->sendCliente($group->id);
                    \Log::info($response);
                    // $response['error'] = false;
                    // $response['status'] = '';

                    if($response['error'] == false)
                    {
                        \DB::commit();
                        Session::flash('message', 'Registro creado exitosamente');
                        return redirect('groups');                            
                    }else
                    {
                        \DB::rollback();
                        Session::flash('error_message', 'Ocurrio un error al intentar guardar el registro: '. $response['status']);
                        return redirect()->back()->withInput();
                        \Log::info('Ocurrio un error al intentar guardar el grupo.');
                    }
                    
                }else{
                    \DB::rollback();
                    Session::flash('error_message', 'Ocurrio un error al intentar guardar el registro');
                    return redirect()->back()->withInput();
                    \Log::info('Ocurrio un error al intentar guardar el grupo.');
                }
            }catch (\Exception $e){
                \DB::rollback();
                \Log::error("Error saving new Group - {$e->getMessage()}");
                Session::flash('error_message', 'Ocurrio un error al intentar guardar el grupo');
                return redirect()->back()->withInput();
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
    public function edit($id)
    {
        if (!$this->user->hasAccess('group.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if($group = Group::find($id)){
            $data = ['group' => $group];
            return view('groups.edit', $data);
        }else{
            Session::flash('error_message', 'Grupo no encontrado');
            return redirect('groups');
        }

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!$this->user->hasAccess('group.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if ($group = Group::find($id)){
            $input = $request->all();
            \DB::beginTransaction();
            try{
                $group->fill($input);
                $group->fill(['updated_by' => $this->user->id]);
                $group->fill(['updated_at' => Carbon::now()]);
                if($group->update()){
                    $insert_ondanet  = new OndanetServices();
                    $response = $insert_ondanet->sendCliente($id);
                    \Log::info($response);

                    if($response['error'] == false)
                    {
                        \DB::commit();
                        Session::flash('message', 'Grupo actualizado exitosamente');
                        return redirect('groups');                          
                    }else
                    {
                        \DB::rollback();
                        Session::flash('error_message', 'Ocurrio un error al intentar guardar el registro: '. $response['status']);
                        return redirect()->back()->withInput();
                        \Log::info('Ocurrio un error al intentar guardar el grupo.');
                    }
                }
            }catch (\Exception $e){
                \DB::rollback();
                \Log::error("Error updating group: " . $e->getMessage());
                Session::flash('error_message','Error al intentar actualizar el grupo');
                return redirect('groups');
            }
        }else{
            \Log::warning("Group not found");
            Session::flash('error_message', 'Grupo no encontrado');
            return redirect('groups');
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        {
            if (!$this->user->hasAccess('group.delete')) {
                \Log::error('Unauthorized access attempt',
                    ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
                Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
                return redirect('/');
            }
            $message = '';
            $error = '';
            \Log::debug("Attempting to delete a given group");
            if (Group::find($id)){
                $activeBranches = Branch::where('group_id', $id)->get();
                if (count($activeBranches) == 0){
                    try{
                        if (Group::destroy($id)){
                            $message =  'Groupo eliminado correctamente';
                            $error = false;
                        }
                    }catch (\Exception $e){
                        \Log::error("Error deleting group: " . $e->getMessage());
                        $message =  'Error al intentar eliminar el grupo';
                        $error = true;
                    }
                }else{
                    \Log::warning("Group {$id} have still active branches");
                    $message =  'Este grupo aun cuenta con sucursales activas';
                    $error = true;
                }
            }else{
                $message =  'Grupo no encontrado';
                $error = true;
            }
    
            return response()->json([
                'error' => $error,
                'message' => $message,
            ]);
        }
    }

    public function store_branch($branch_id, Request $request){

        \Log::info($request->all());
        $this->user = \Sentinel::getUser()->id;
        $nombre=$request->description;
        $ruc=$request->ruc;
        $managers_id = $request->managers;
        $block_frecuency = $request->blocking;
        $direccion = $request->direccion;
        $telefono = $request->telefono;

        $group = new Group;
        $group->description = $nombre;
        $group->created_at = Carbon::now();
        $group->updated_at = Carbon::now();
        $group->created_by = $this->user;
        $group->ruc = $ruc;
        $group->direccion = $direccion;
        $group->telefono = $telefono;

        if($managers_id == 1)
        {
            $group->manager_id = $managers_id;
        }
        
        

        \DB::beginTransaction();
        \Log::debug('Managers_id:'.$managers_id);
        //INSERTAR NUEVO MANAGER
        if($managers_id <> 1)
        {
            $managerID = \DB::table('managers')
            ->insertGetId([
                'description' => $nombre
            ]);

            $group->manager_id = $managerID;
        }

        if($request->ajax()){
            if (Group::where('ruc', $ruc)->count() == 0){
                try{
                    if ($group->save()) {

                        if($branch_id != 0){

                            \DB::table('branches')
                            ->where('id', $branch_id)
                            ->update([
                                'group_id' => $group->id,
                            ]); 


                            if(!isset($abm_v2)){
                                $atm_id=$request->atm_id;

                                \Log::info('abm_v2');
                                $branch_caracteristicas = \DB::table('branches')
                                ->select('branches.*')
                                ->where('branches.id',$branch_id)
                                ->first();
                             

                                \DB::table('caracteristicas')
                                ->where('id', $branch_caracteristicas->caracteristicas_id)
                                ->update([
                                    'group_id' => $group->id,
                                ]);
                            }



                        }else{

                            $atm_id=$request->atm_id;

                            $branch = \DB::table('branches')
                            ->select('branches.*')
                            ->join('points_of_sale','points_of_sale.branch_id','=','branches.id')
                            ->where('points_of_sale.atm_id',$atm_id)
                            ->first();

                            \DB::table('branches')
                            ->where('id', $branch->id)
                            ->update([
                                'group_id' => $group->id,
                            ]);                            
                        }                                                

                        //insertar reglas al nuevo grupo creado
                        \Log::debug('Seteando frecuencia de reglas',['manager_id'=>$managers_id, 'frecuencia'=>$block_frecuency]);

                        if($managers_id == 0)
                        {  

                            /*
                            select c.credit_limit 
                            from contract_insurance as ci
                            join contract as c on c.id = ci.contract_id 
                            join insurance_policy as ip on ip.id = ci.insurance_policy_id
                            */ 
                            
                            $ccip = \DB::table('contract_insurance as ci')
                                ->select(
                                    'c.credit_limit'
                                )
                                ->join('contract as c', 'c.id', '=', 'ci.contract_id')
                                ->join('insurance_policy as ip', 'ip.id', '=', 'ci.insurance_policy_id')
                                ->where('c.busines_group_id', $group->id)
                                ->get();

                            $ccip_amount = null;

                            if (count($ccip) > 0) {
                                $ccip_amount = $ccip[0]->credit_limit;
                            }  
                            
                            if($block_frecuency == 1) {
                                //REGLAS SOLO LUNES (PARA CADENAS)

                                if ($ccip_amount == null) {
                                    $ccip_amount = 120000000;
                                }

                                $data_1 = [
                                    ['created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'dias_previos'=>1,'saldo_minimo'=>100, 'tipo_control'=>1, 'dia' => 1, 'group_id' => $group->id],
                                    ['created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'dias_previos'=>1,'saldo_minimo' => $ccip_amount, 'tipo_control'=>4, 'dia' => 1, 'group_id' => $group->id],
                                ];
                                $reglas = \DB::table('balance_rules')
                                ->insert($data_1);

                            } elseif($block_frecuency == 3) {
                                //REGLAS LUNES, MIERCOLES, VIERNES (PARA UNICOS)

                                if ($ccip_amount == null) {
                                    $ccip_amount = 5000000;
                                }

                                $data_3 = [
                                    ['created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'dias_previos'=>1,'saldo_minimo'=>100, 'tipo_control'=>1, 'dia' => 1, 'group_id' => $group->id],
                                    ['created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'dias_previos'=>1,'saldo_minimo'=>100, 'tipo_control'=>1, 'dia' => 3, 'group_id' => $group->id],
                                    ['created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'dias_previos'=>1,'saldo_minimo'=>100, 'tipo_control'=>1, 'dia' => 5, 'group_id' => $group->id],
                                    ['created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'dias_previos'=>1,'saldo_minimo' => $ccip_amount, 'tipo_control'=>4, 'dia' => 1, 'group_id' => $group->id],
                                ];
                                $reglas = \DB::table('balance_rules')
                                ->insert($data_3);
                            } elseif($block_frecuency == 5) {
                                //REGLAS LUNES a VIERNES (NUEVA OPCIÓN)
                                if ($ccip_amount == null) {
                                    $ccip_amount = 5000000;
                                }

                                $days = [
                                    1, 2, 3, 4, 5, 1
                                ];

                                $item = [
                                    'created_at' => Carbon::now(), 
                                    'updated_at' => Carbon::now(), 
                                    'dias_previos' => 1,
                                    'saldo_minimo' => 100, 
                                    'tipo_control' => 1, 
                                    'dia' => null, 
                                    'group_id' => $group->id
                                ]; 

                                $item_list = [];

                                for ($i = 0; $i < count($days); $i++) {

                                    $item['dia'] = $days[$i];

                                    //Inserta con el ultimo el parametro del limite.
                                    if ($item['dia'] == 1 and $i == 5) {
                                        $item['saldo_minimo'] = $ccip_amount;
                                        $item['tipo_control'] = 4;
                                    }

                                    array_push($item_list, $item);
                                }

                                $reglas = \DB::table('balance_rules')
                                    ->insert($item_list);

                                \Log::debug("Reglas de Lunes a Viernes insertadas con éxito en " . __FUNCTION__ . " para group_id: " . $group->id);  
                                \Log::debug("LISTA DE " . __FUNCTION__ . " :", [$item_list]);
                            } elseif($block_frecuency == 6) {
                                //REGLAS de bloqueo - FRANKI 
                                //LUNES, MIERCOLES, VIERNES  -> saldo_minimo = 100
                                //MARTES, JUEVES -> Saldo_minimo = 2.000.000

                                if ($ccip_amount == null) {
                                    $ccip_amount = 2000000;
                                }

                                $data_6 = [
                                    ['created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'dias_previos'=>1,'saldo_minimo'=>100, 'tipo_control'=>1, 'dia' => 1, 'group_id' => $group->id],
                                    ['created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'dias_previos'=>1,'saldo_minimo'=>100, 'tipo_control'=>1, 'dia' => 3, 'group_id' => $group->id],
                                    ['created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'dias_previos'=>1,'saldo_minimo'=>100, 'tipo_control'=>1, 'dia' => 5, 'group_id' => $group->id],
                                    ['created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'dias_previos'=>1,'saldo_minimo' => $ccip_amount, 'tipo_control'=>2, 'dia' => 2, 'group_id' => $group->id],
                                    ['created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'dias_previos'=>1,'saldo_minimo' => $ccip_amount, 'tipo_control'=>2, 'dia' => 4, 'group_id' => $group->id],

                                ];
                                $reglas = \DB::table('balance_rules')
                                ->insert($data_6);
                            }
                        }                       
                        //COMENTAR PARA LAS PRUEBAS
                        $insert_ondanet  = new OndanetServices();
                        $response = $insert_ondanet->sendCliente($group->id);
                        \Log::info('Creating new bussiness group on ondanet: ',['result'=>$response]);                        
                        //  $response['error'] = false;
                        //  $response['status'] = '';

                        if($response['error'] == false)
                        {
                            $data = [];
                            $data['id'] = $group->id;
                            $data['description'] = $group->description;
                            $data['ruc'] = $group->ruc;
                            \Log::info("Nuevo grupo creado correctamente");

                            \DB::commit();  

                            $respuesta['mensaje'] = 'Nuevo grupo creado correctamente';
                            $respuesta['tipo'] = 'success';
                            $respuesta['data'] = $data;
                            $respuesta['url'] = route('groups.update_branch',[$branch_id,$group->id]);
                            return $respuesta;
                        }else{
                            \DB::rollback();
                            \Log::info('Ocurrio un error al intentar guardar el cliente.');
                            $respuesta['mensaje'] = $response['status'];
                            $respuesta['tipo'] = 'error';
                            return $respuesta;
                        }
                    }
                }catch (\Exception $e){
                    \DB::rollback();
                    \Log::critical($e->getMessage());
                    $respuesta['mensaje'] = 'Error al crear grupo';
                    $respuesta['tipo'] = 'error';
                    return $respuesta;
                }
            }else{
                \DB::rollback();
                \Log::info('El siguiente RUC ya existe.');
                $respuesta['mensaje'] = 'El siguiente RUC ya existe';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }
        }
    }

    public function update_branch($branch_id, Request $request){
        if($request->ajax()){

            $input = $request->all();
            \Log::info($input);
            try{
                $group=Group::where('id', $input['group_id'])->first();

                $this->user = \Sentinel::getUser()->id;

                if($branch_id != 0){
                    $Branch = Branch::find($branch_id);
                }else{

                    $atm_id=$input['atm_id'];

                    $branch = \DB::table('branches')
                    ->select('branches.*')
                    ->join('points_of_sale','points_of_sale.branch_id','=','branches.id')
                    ->where('points_of_sale.atm_id',$atm_id)
                    ->first();

                    $Branch = Branch::find($branch->id);
                }
                
                $Branch->group_id = $input['group_id'];
                $Branch->updated_by = $this->user;
                $Branch->save();

                $data = [];
                $data['id'] = $input['group_id'];
                $data['branch_id'] = $Branch->id;
                $data['description'] = $group->description;
                
                \Log::info("Grupo actualizado correctamente");
                $respuesta['mensaje'] = 'Grupo actualizado correctamente';
                $respuesta['tipo'] = 'success';
                $respuesta['data'] = $data;

                if($branch_id != 0){
                    $respuesta['url'] = route('groups.update_branch',[$branch_id,$group->id]);
                }else{
                    $respuesta['url'] = route('groups.update_branch',[$branch->id,$group->id]);
                }
                
                return $respuesta;

            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Error al crear grupo';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }
        }
    }
    public function store_venta(Request $request)
    {   
        $input = $request->all();
        \Log::info($input);
        $input['created_by'] = $this->user->id;
        $nombre =   $request->description;
        $ruc    =   $request->ruc;
        $managers_id = $request->managers;
        $block_frecuency = $request->blocking;
        $direccion = $request->direccion;
        $telefono = $request->telefono;

        $group = new Group;
        $group->description = $nombre;
        $group->created_at = Carbon::now();
        $group->updated_at = Carbon::now();
        $group->created_by = $this->user->id;
        $group->ruc = $ruc;
        $group->direccion = $direccion;
        $group->telefono = $telefono;

        if($managers_id == 1)
        {
            $group->manager_id = $managers_id;
        }

        if($managers_id <> 1)
        {
            $managerID = \DB::table('managers')
            ->insertGetId([
                'description' => $nombre
            ]);
    
            $group->manager_id = $managerID;
        }

        \DB::beginTransaction();
        if($request->ajax()){
            if (Group::where('ruc', $ruc)->count() == 0){
                try{
                    // TODO Ondanet                    
                    if ($group->save()){

                        
                        //insertar reglas al nuevo grupo creado
                        \Log::debug('Seteando frecuencia de reglas',['manager_id'=>$managers_id, 'frecuencia'=>$block_frecuency]);
                       
                        
                        if($managers_id == 0)
                        {  

                            /*
                            select c.credit_limit 
                            from contract_insurance as ci
                            join contract as c on c.id = ci.contract_id 
                            join insurance_policy as ip on ip.id = ci.insurance_policy_id
                            */ 
                            
                            $ccip = \DB::table('contract_insurance as ci')
                                ->select(
                                    'c.credit_limit'
                                )
                                ->join('contract as c', 'c.id', '=', 'ci.contract_id')
                                ->join('insurance_policy as ip', 'ip.id', '=', 'ci.insurance_policy_id')
                                ->where('c.busines_group_id', $group->id)
                                ->get();

                            $ccip_amount = null;

                            if (count($ccip) > 0) {
                                $ccip_amount = $ccip[0]->credit_limit;
                            }  
                            
                            if($block_frecuency == 1) {
                                //REGLAS SOLO LUNES (PARA CADENAS)

                                if ($ccip_amount == null) {
                                    $ccip_amount = 120000000;
                                }

                                $data_1 = [
                                    ['created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'dias_previos'=>1,'saldo_minimo'=>100, 'tipo_control'=>1, 'dia' => 1, 'group_id' => $group->id],
                                    ['created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'dias_previos'=>1,'saldo_minimo' => $ccip_amount, 'tipo_control'=>4, 'dia' => 1, 'group_id' => $group->id],
                                ];
                                $reglas = \DB::table('balance_rules')
                                ->insert($data_1);

                            }elseif($block_frecuency == 3) {
                                //REGLAS LUNES, MIERCOLES, VIERNES (PARA UNICOS)

                                if ($ccip_amount == null) {
                                    $ccip_amount = 5000000;
                                }

                                $data_3 = [
                                    ['created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'dias_previos'=>1,'saldo_minimo'=>100, 'tipo_control'=>1, 'dia' => 1, 'group_id' => $group->id],
                                    ['created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'dias_previos'=>1,'saldo_minimo'=>100, 'tipo_control'=>1, 'dia' => 3, 'group_id' => $group->id],
                                    ['created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'dias_previos'=>1,'saldo_minimo'=>100, 'tipo_control'=>1, 'dia' => 5, 'group_id' => $group->id],
                                    ['created_at' => Carbon::now(), 'updated_at' => Carbon::now(), 'dias_previos'=>1,'saldo_minimo' => $ccip_amount, 'tipo_control'=>4, 'dia' => 1, 'group_id' => $group->id],
                                ];
                                $reglas = \DB::table('balance_rules')
                                ->insert($data_3);
                            }elseif($block_frecuency == 5) {
                                //REGLAS LUNES a VIERNES (NUEVA OPCIÓN)
                                if ($ccip_amount == null) {
                                    $ccip_amount = 5000000;
                                }

                                $days = [
                                    1, 2, 3, 4, 5, 1
                                ];

                                $item = [
                                    'created_at' => Carbon::now(), 
                                    'updated_at' => Carbon::now(), 
                                    'dias_previos' => 1,
                                    'saldo_minimo' => 100, 
                                    'tipo_control' => 1, 
                                    'dia' => null, 
                                    'group_id' => $group->id
                                ]; 

                                $item_list = [];

                                for ($i = 0; $i < count($days); $i++) {

                                    $item['dia'] = $days[$i];

                                    //Inserta con el ultimo el parametro del limite.
                                    if ($item['dia'] == 1 and $i == 5) {
                                        $item['saldo_minimo'] = $ccip_amount;
                                        $item['tipo_control'] = 4;
                                    }

                                    array_push($item_list, $item);
                                }

                                $reglas = \DB::table('balance_rules')
                                    ->insert($item_list);

                                \Log::debug("Reglas de Lunes a Viernes insertadas con éxito en " . __FUNCTION__ . " para group_id: " . $group->id);  
                                \Log::debug("LISTA DE " . __FUNCTION__ . " :", [$item_list]);
                            }
                        }  
                        
                        $insert_ondanet  = new OndanetServices();
                        $response = $insert_ondanet->sendCliente($group->id);                        

                        \Log::info($response);

                        if($response['error'] == false)
                        {
                            \DB::commit();
                            $data = [];
                            $data['id'] = $group->id;
                            $data['description'] = $input['description'];
                            $data['ruc'] = $input['ruc'];
                            \Log::info("Nuevo grupo creado correctamente");

                            $respuesta['mensaje'] = 'Nuevo grupo creado correctamente';
                            $respuesta['tipo'] = 'success';
                            $respuesta['data'] = $data;
                            return $respuesta;                          
                        }else
                        {
                            \DB::rollback();
                            \Log::info('Ocurrio un error al intentar guardar el cliente.');
                            $respuesta['mensaje'] = $response['status'];
                            $respuesta['tipo'] = 'error';
                            return $respuesta;
                        }
                        
                    }else{
                        \DB::rollback();
                        \Log::info('Ocurrio un error al intentar guardar el grupo.');
                        $respuesta['mensaje'] = 'Ocurrio un error al intentar guardar el grupo';
                        $respuesta['tipo'] = 'error';
                        return $respuesta;
                    }
                }catch (\Exception $e){
                    \DB::rollback();
                    \Log::error("Error saving new Group - {$e->getMessage()}");
                    $respuesta['mensaje'] = 'Error al crear grupo';
                    $respuesta['tipo'] = 'error';
                    return $respuesta;
                }
            }else{
                \DB::rollback();
                \Log::info('El siguiente RUC ya existe.');
                $respuesta['mensaje'] = 'El siguiente RUC ya existe';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }
        }
    }
}
