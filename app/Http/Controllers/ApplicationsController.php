<?php

namespace App\Http\Controllers;

use App\Http\Requests\ApplicationsRequest;
use App\Models\Applications;
use App\Models\Owner;
use App\Models\Pos;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Atm;
use Session;

class ApplicationsController extends Controller
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
        if (!$this->user->hasAccess('applications')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $name = $request->get('name');
        if ($this->user->hasRole('security_admin') || $this->user->hasRole('superuser')) {
            $applications = Applications::filterAndPaginate($name);
        }else{
            $applications = Applications::where('owner_id', $this->user->owner_id)->paginate(20);
        }
        $data['applications'] = $applications;
        return view('applications.index', $data);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!$this->user->hasAccess('applications.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if ($this->user->hasRole('security_admin') || $this->user->hasRole('superuser')) {
            $applications = Applications::pluck('name', 'id');
            $owners = Owner::pluck('name', 'id');
        }else{
            $applications = Applications::where('owner_id', $this->user->owner_id)->pluck('name', 'id');
            $owners = null;
        }

        $data['applications'] = $applications;
        $data['active_desc'] = 'Desarrollo';
        $data['owners'] = $owners;
        $data['selected_owner'] = null;

        return view('applications.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ApplicationsRequest|Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(ApplicationsRequest $request)
    {
        if (!$this->user->hasAccess('applications.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = $request->except('_token');
        if ($this->user->hasRole('security_admin') || $this->user->hasRole('superuser')) {
            if ($input['owner_id'] == '') {
                Session::flash('error_message', 'Debe elegir una red');
                return redirect()->back()->withInput();
            }
        } else {
            $input['owner_id'] = \Sentinel::getUser()->owner_id;
        }
        $input['created_by'] = \Sentinel::getUser()->id;

        try {
            $app = Applications::create($input);

            if ($app){
                if (!$this->createDirectoryResource($app->id)){
                    Session::flash('message', 'Directorio de Recursos no creado');
                }
            }
            // This is some weird shit
            //If the new application was created from a previous app template
            if(isset($request->from_app_id) && $request->from_app_id != ''){
                $query = "SELECT duplicate_application(".$request->from_app_id.",".$app->id.")";
                $duplicate_template = DB::statement($query);
                //copying files into new folder
                //copying fonts
                $origin  ='resources/'.$request->from_app_id.'/fonts';
                $destiny ='resources/'.$app->id;
                exec("cp -r $origin $destiny");
                //copying audios
                $origin  ='resources/'.$request->from_app_id.'/audio';
                exec("cp -r $origin $destiny");
                //copying images
                $origin  ='resources/'.$request->from_app_id.'/images';
                exec("cp -r $origin $destiny");
                //copying videos
                $origin  ='resources/'.$request->from_app_id.'/videos';
                exec("cp -r $origin $destiny");
            }
            Session::flash('message', 'Producto creado correctamente');
            return redirect('applications');
        } catch (\Exception $e) {
            \Log::error("Error creating a new Product - {$e->getMessage()}");
            Session::flash('error_message', 'Error al intentar crear el registro, intente nuevamente');
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
        if (!$this->user->hasAccess('applications.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if ($app = Applications::find($id)) {
            $data = array();
            $data['application'] = $app;
            if ($this->user->hasRole('security_admin') || $this->user->isSuperuser()) {
                $pdv = Pos::pluck('description', 'id');
                $owners = Owner::pluck('name', 'id');
                $data['selected_owner'] = $app->owner_id;
            } else {
                $pdv = Pos::where('owner_id', $this->user->owner_id)->pluck('description', 'id');
                $owners = null;
                $data['selected_owner'] = $this->user->agent_id;

            }

            $assigned_atms = DB::table('atm_application')
                ->join('atms', 'atms.id', '=', 'atm_application.atm_id')
                ->join('points_of_sale','points_of_sale.atm_id','=','atms.id')
                ->where('application_id', $id)
                ->get();

            $data['active_desc'] = ($app->active == true) ? 'Producción' : 'Desarrolo';
            $data['assigned_atm'] = $assigned_atms;
            $data['pdvs'] = $pdv;
            $data['owners'] = $owners;


            return view('applications.edit', $data);
        }else{
            \Log::warning("App not found");
            Session::flash('error_message', 'Aplicacion no encontrada');
            return redirect('applications');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ApplicationsRequest|Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(ApplicationsRequest $request, $id)
    {
        if (!$this->user->hasAccess('applications.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        
        if ($app = Applications::find($id)){
            $input = $request->except(['_token', '_method']);
            try {
                $app->update($input);
                Session::flash('message', 'Aplicacion actualizada correctamente');
                return redirect('applications');
            } catch (\Exception $e) {
                \Log::warning("Error on update Application Id: {$app->id} | {$e->getMessage()}");
                Session::flash('error_message', 'Ha ocurrido un error al intentar actualziar el registro');
                return redirect('applications');
            }
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
        if (!$this->user->hasAccess('applications.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $message = '';
        $error = true;
        if ($app = Applications::find($id)) {
            try {

                Applications::destroy($id);
                $message = 'Producto eliminado exitosamente';
                $error = false;
            } catch (\Exception $e) {
                \Log::warning("Error attempting to destroy Application: {$id} - {$e->getMessage()}");
                $message = 'Ocurrio un error al intentar eliminar la aplicacion';
            }
        } else {
            $message = 'Aplicacion no encontrada ';
            \Log::warning('Error attempting to destroy application - Application not found');
        }
        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }

    /**
     * Create a resource directory for current application
     * @param $id
     * @return boolean
     */
    protected function createDirectoryResource($id)
    {

        $newPath = public_path('resources/');
        $newPath = mkdir($newPath . $id);
        if ( is_dir($newPath)){
            $resourceTypes = ['audios', 'fonts', 'videos', 'images'];
            foreach ($resourceTypes as $item => $value){
                mkdir($newPath . '/' . $value);
            }
            return true;
        }else{
            return false;
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return mixed
     */
    public function assignAtm(Request $request, $id)
    {
        if (!$this->user->hasAccess('applications.assign')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
      
        if($request->ajax()){
            $date = Carbon::now();
            $data = [
                'atm_id' => $request->atm_id,
                'application_id' => $id,
                'created_by' => $this->user->id,
                'active' => true,
                'created_at' => $date,
                'updated_at' => $date
            ];

            try{
                $apps = \DB::table('atm_application')->where('atm_id',$request->atm_id)->count();

                if($request->atm_parts <= 0){
                    if($request->reasignar){
                        DB::table('atms_parts')->where('atm_id', '=', ($request->atm_id))->delete();
                    }

                    if($apps > 0){
                        DB::table('atm_application')->where('atm_id', '=', ($request->atm_id))->delete();
                    }

                    DB::table('atm_application')
                        ->insert($data);

                    $tipoDispositivos = explode('|',$request->tipo_dispositivo);

                    $results = DB::select('select create_parts('.$request->atm_id.','.$tipoDispositivos[0].','.$tipoDispositivos[1].')');
                }else{
                    if($apps > 0){
                        DB::table('atm_application')->where('atm_id', '=', ($request->atm_id))->delete();
                    }

                    DB::table('atm_application')
                        ->insert($data);    
                }
           
                \DB::table('atms')
                ->where('id', $request->atm_id)
                ->update([
                    'atm_status' => -4,
                ]);
                \Log::info("Status atm version 1 : -4");

    
    


                $resumen = \DB::table('atms')
                ->select(\DB::raw('
                                atms.name, 
                                atms.code,
                                atms.owner_id,
                                owners.name as owner_name,
                                branches.description as branch_name,
                                points_of_sale.pos_code,
                                points_of_sale.ondanet_code,
                                points_of_sale.description as pos_name,
                                point_of_sale_vouchers.stamping,
                                point_of_sale_vouchers.valid_from,
                                point_of_sale_vouchers.valid_until,
                                point_of_sale_vouchers.from_number,
                                point_of_sale_vouchers.to_number,
                                point_of_sale_voucher_types.expedition_point,
                                voucher_types.description,
                                applications.name as application_name,
                                business_groups.description as grupo,
                                business_groups.ruc as ruc'
            ))
                    ->join('owners','owners.id','=','atms.owner_id')
                    ->join('points_of_sale','points_of_sale.atm_id','=','atms.id')
                    ->join('branches','branches.id','=','points_of_sale.branch_id')
                    ->join('point_of_sale_vouchers','point_of_sale_vouchers.point_of_sale_id','=','points_of_sale.id')
                    ->join('point_of_sale_voucher_types','point_of_sale_voucher_types.id','=','point_of_sale_vouchers.pos_voucher_type_id')
                    ->join('voucher_types','voucher_types.id','=','point_of_sale_voucher_types.voucher_type_id')
                    ->join('atm_application','atm_application.atm_id','=','atms.id')
                    ->join('applications','applications.id','=','atm_application.application_id')
                    ->leftjoin('business_groups','business_groups.id','=','branches.group_id')
                    ->whereRaw("atms.id = ".$request->atm_id)
                    ->first();


                if(empty($resumen)){
                    $resumen = \DB::table('atms')
                    ->select(\DB::raw('
                        atms.name, 
                        atms.code,
                        atms.owner_id,
                        owners.name as owner_name,
                        branches.description as branch_name,
                        points_of_sale.pos_code,
                        points_of_sale.ondanet_code,
                        points_of_sale.description as pos_name,
                        applications.name as application_name,
                        business_groups.description as grupo,
                        business_groups.ruc as ruc'
                    ))
                    ->join('owners','owners.id','=','atms.owner_id')
                    ->join('points_of_sale','points_of_sale.atm_id','=','atms.id')
                    ->join('branches','branches.id','=','points_of_sale.branch_id')
                    ->join('atm_application','atm_application.atm_id','=','atms.id')
                    ->join('applications','applications.id','=','atm_application.application_id')
                    ->leftjoin('business_groups','business_groups.id','=','branches.group_id')
                    ->whereRaw("atms.id = ".$request->atm_id)
                    ->first();

                }



                
                \Log::info("Aplicacion asignada a nuevo atm, correctamente");
                $respuesta['mensaje'] = '<small>A continuación debe agregar las credenciales al ATM creado, pulsando sobre el icono</small> <a href="'.route('atm.credentials.index',['id' => $request->atm_id]).'"><i class="fa fa-key" style="color:#3c8dbc"></i></a> <small>en la siguiente pantalla.</small>';
                $respuesta['tipo'] = 'success';
                $respuesta['titulo'] = 'Nuevo ATM registrado con éxito!';
                $respuesta['data'] = $data;
                $respuesta['reasignar'] = true;
                $respuesta['resumen'] = $resumen;

                return $respuesta;
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Ocurrio un error al asignar la aplicacion al atm';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }
        }else{
            if ($pdv = Pos::find($request->pdv_id)){
                if (!is_null($pdv->atm_id)){
                    $input = $request->except('_token');
                    $atmApps = DB::table('atm_application')
                        ->where('atm_id', '=', $pdv->atm_id)
                        ->get();

                    if ($pdv->owner_id == $input['owner_id']){
                        if (count($atmApps) == 0){
                            $date = Carbon::now();
                            $data = [
                                'atm_id' => $pdv->atm_id,
                                'application_id' => $id,
                                'created_by' => $this->user->id,
                                'active' => true,
                                'created_at' => $date,
                                'updated_at' => $date
                            ];
                            try{
                                DB::table('atm_application')
                                    ->insert($data);
                                Session::flash('atm_form_message', 'Actualizado correctamente');
                                return redirect()->back();
                            }catch (\Exception $e){
                                \Log::error("Error on insert new Atm_application - {$e->getMessage()}");
                                Session::flash('atm_form_error_message', 'Atm ya cuenta una aplicacion');
                                return redirect()->back();
                            }

                        }
                    }else{
                        Session::flash('atm_form_error_message', 'No puede asignar a un cajero diferente de su red');
                        return redirect()->back();
                    }
                }else{
                    Session::flash('atm_form_error_message', 'Sucursal no cuenta con cajero asignado');
                    return redirect()->back();
                }


            }else {
                Session::flash('atm_form_error_message', 'Atm no encontrado');
                return redirect()->back();
            }
        }

    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function removeAssignedAtm(Request $request)
    {
        $error = true;
        $message = '';
        if (!$this->user->hasAccess('applications.assign.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            $message = 'No tiene permisos para la operacion';
            return response()->json([
                'error' => $error,
                'message' => $message,
            ]);
        }
        if(isset($request->atm_id)){
            \Log::info("Deleting atm_application for atm id: {$request->atm_id}");
            try{
                DB::table('atm_application')->where('atm_id', '=', ($request->atm_id))->delete();
                $error = false;
                $message = 'Registro eliminado correctamente';
            }catch (\Exception $e){
                \Log::error("Error on insert new Atm_application - {$e->getMessage()}");
                $error = true;
                $message = "No se pudo eliminar el registro";
            }
            return response()->json([
                'error' => $error,
                'message' => $message,
            ]);
        }else{
            $message = 'Cajero no encontrado';
            return response()->json([
                'error' => $error,
                'message' => $message,
            ]);
        }
    }
}
