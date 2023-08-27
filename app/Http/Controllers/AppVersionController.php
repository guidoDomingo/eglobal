<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Applications;
use Carbon\Carbon;
use DB;
use Illuminate\Support\Facades\Session;

class AppVersionController extends Controller
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
    public function index(Request $request, $appId)
    {
        if (!$this->user->hasAccess('applications.versions')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $where = "apps_versions.application_id = ".$appId;
        if($request->name){
            $where .= " AND apps_versions.name like '%".$request->name."%'";
        }
        $versiones   = DB::table('apps_versions')
            ->select('apps_versions.*','screens.name as screen_name')
            ->join('screens','screens.id','=','apps_versions.primary_screen_id')
            ->whereRaw("$where")
            ->paginate(20);

        $current_app = DB::table('applications')
            ->where("id", $appId)
            ->first();

        return view('versions.index', compact('appId', 'versiones', 'current_app'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($appId)
    {
        if (!$this->user->hasAccess('applications.versions.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $application = Applications::find($appId);
        $pantallas   = DB::table('screens')
            ->select('name', 'id')
            ->where("application_id","=",$appId)
            ->where("screen_type","=",7)
            ->orderBy("name")
            ->pluck('name', 'id');

        $service_providers = DB::table('service_providers')
            ->where('deleted_at',null)
            ->where('id','<>',-1)
            ->get();
        $service_providers_groups = array(1=>"Local",2=>"Categorias",3=>"Netel",4=>"Pronet");
        return view('versions.create', compact('appId','application','pantallas','service_providers','service_providers_groups'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!$this->user->hasAccess('applications.versions.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $app_id = $request->app_id;
        $screen_type = $request->primary_screen_id;
        $brands_asigned = $request->brands_selected;
        $version_name   = $request->name;
        $created_at =  Carbon::now();
        try{
            $new_app_version_id = DB::table('apps_versions')->insertGetId(
                [
                    'name' => $version_name,
                    'hash' => '0',
                    'primary_screen_id' => $screen_type,
                    'created_at'        => $created_at,
                    'status'            => 1,
                    'application_id'    => $app_id
                ]
            );

            $brands = explode(",", $brands_asigned);
            $order = 1;
            foreach ($brands as $brand){

                $brand = explode("-",$brand);
                if($brand[0] == 1){  //marcas
                    $categoria   = -1;
                    $marca       = $brand[1];
                    $servicio_id = -1;
                    $service_source_id       = 0;
                }

                if($brand[0] == 2){  //categorias
                    $categoria   = $brand[1];
                    $marca       = -1;
                    $servicio_id = -1;
                    $service_source_id       = 0;
                }

                if($brand[0] == 3){  //Marcas Netel
                    $categoria   = -1;
                    $marca       = $brand[1];
                    $servicio_id = -1;
                    $service_source_id       = 1;
                }

                if($brand[0] == 4){  //Marcas Pronet
                    $categoria   = -1;
                    $marca       = $brand[1];
                    $servicio_id = -1;
                    $service_source_id       = 4;
                }

                if($brand[0] == 0){  //Listar todas las categorias
                    $categoria   = 0;
                    $marca       = -1;
                    $servicio_id = -1;
                    $service_source_id       = 0;
                }

                $setup_app_menu = DB::table('app_menu')->insert(
                    [
                        'app_id' => $app_id,
                        'app_version_id' => $new_app_version_id,
                        'categoria_id' => $categoria,
                        'marca_id'     => $marca,
                        'order'        => $order,
                        'servicio_id'  => $servicio_id,
                        'service_source_id'  => $service_source_id,
                        'status'       => 1
                    ]
                );
                $order++;
            }

            $application = DB::table('applications')
                ->where('id', $app_id)
                ->update(['current_version' => $new_app_version_id]);

            return redirect('/applications/'.$app_id.'/versions');
        } catch (Exception $e) {
            \Log::warning('No se completo la inserción '. $e);
            return redirect('/applications/'.$app_id.'/versions');

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
    public function edit($appId,$id)
    {
        if (!$this->user->hasAccess('applications.versions.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $application = Applications::find($appId);
        $pantallas   = DB::table('screens')
            ->select('name', 'id')
            ->where("application_id","=",$appId)
            ->where("screen_type","=",7)
            ->orderBy("name")
            ->pluck('name', 'id');

        $service_providers = DB::table('service_providers')
            ->where('deleted_at',null)
            ->where('id','<>',-1)

            ->get();
        $version_id = $id;
        $service_providers_groups = array(1=>"Local",2=>"Categorias",3=>"Netel",4=>"Pronet");

        $app_version = DB::table('apps_versions')->where('id',$version_id)->first();

        return view('versions.edit', compact('appId','app_version','application','pantallas','service_providers','version_id','service_providers_groups'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $appid,$version_id)
    {
        if (!$this->user->hasAccess('applications.versions.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        try{
            $screen_type = $request->primary_screen_id;
            $brands_asigned = $request->brands_selected;
            $version_name   = $request->name;
            $updated_at =  Carbon::now();
            $app_id = $request->app_id;
            if($version_id){
                $update = DB::table('apps_versions')->where('id', $version_id)
                    ->update(
                        [
                            'name' => $version_name,
                            'primary_screen_id' => $screen_type,
                            'updated_at'        => $updated_at,
                            'application_id'    => $app_id
                        ]
                    );
                \Log::info('Edicion de version de aplicacion exitosa id: '. $app_id);
            }


            if($brands_asigned <> ""){
                //delete old settings
                $delete_config = DB::table('app_menu')->where('app_version_id',$version_id)->delete();

                //store_new_settings

                $brands = explode(",", $brands_asigned);
                $order = 1;
                foreach ($brands as $brand){

                    $brand = explode("-",$brand);
                    if($brand[0] == 1){  //marcas
                        $categoria   = -1;
                        $marca       = $brand[1];
                        $servicio_id = -1;
                        $service_source_id = 0;
                    }

                    if($brand[0] == 2){  //categorias
                        $categoria   = $brand[1];
                        $marca       = -1;
                        $servicio_id = -1;
                        $service_source_id       = 0;
                    }

                    if($brand[0] == 3){  //Marcas Netel
                        $categoria   = -1;
                        $marca       = $brand[1];
                        $servicio_id = -1;
                        $service_source_id       = 1;
                    }

                    if($brand[0] == 4){  //Marcas Pronet
                        $categoria   = -1;
                        $marca       = $brand[1];
                        $servicio_id = -1;
                        $service_source_id       = 4;
                    }

                    if($brand[0] == 0){  //Listar todas las categorias
                        $categoria   = 0;
                        $marca       = -1;
                        $servicio_id = -1;
                        $service_source_id       = 0;
                    }

                    $setup_app_menu = DB::table('app_menu')->insert(
                        [
                            'app_id' => $app_id,
                            'app_version_id'    => $version_id,
                            'categoria_id'      => $categoria,
                            'marca_id'          => $marca,
                            'order'             => $order,
                            'servicio_id'       => $servicio_id,
                            'service_source_id' => $service_source_id,
                            'status'            => 1
                        ]
                    );
                    $order++;
                }

            }


            \Log::info('Edicion de version de aplicacion fallida id: '. $app_id .'falta id de versión');

            return redirect('/applications/'.$app_id.'/versions');

        } catch (Exception $e) {
            \Log::info('Edicion de version de aplicacion fallida id: '. $app_id);
            return redirect('/applications/'.$app_id.'/versions');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($appId, $id, Request $request)
    {
        if (!$this->user->hasAccess('applications.versions.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $current_app = DB::table('applications')
            ->where("id", $appId)
            ->first();

        if($current_app->current_version == $id){
            $error = true;
            $message = "No se puede eliminar una version activa";
        }else{

            try {
                \DB::table('app_menu')->where('app_version_id', '=', $id)->delete();
                \DB::table('apps_versions')->where('id', '=', $id)->delete();
                $error = false;
                $message = "Registro eliminado correctamente";
            } catch (Exception $e) {
                $error = true;
                $message = "Hubo un problema al eliminar el registro";
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'error' => $error,
                'message' => $message,
            ]);
        }
    }

    public function getproviderGroups($group_id, Request $request){
        $error = false;

        if($group_id == 1){

            $service_providers = DB::table('service_providers')
                ->where('deleted_at',null)
                ->where('id','<>',-1)
                ->get();

            foreach ($service_providers as $service_provider){
                $items['id'] = $service_provider->id;
                $items['group'] = $group_id;
                $items['name'] = $service_provider->name;

                $result[] = $items;
            }


        }elseif($group_id == 2){
            $service_providers = DB::table('app_categories')
                ->where('status',true)
                ->get();

            foreach ($service_providers as $service_provider){
                $items['id'] = $service_provider->id;
                $items['group'] = $group_id;
                $items['name'] = $service_provider->name;

                $result[] = $items;
            }
        }elseif($group_id == 3){
            $service_providers = DB::table('netel_most_popular')
                ->where('status',true)
                ->get();

            foreach ($service_providers as $service_provider){
                $items['id'] = $service_provider->netel_brand_id;
                $items['group'] = $group_id;
                $items['name'] = $service_provider->name;

                $result[] = $items;
            }
        }elseif($group_id == 4){
            $service_providers = DB::table('pronet_most_popular')
                ->where('status',true)
                ->get();

            foreach ($service_providers as $service_provider){
                $items['id'] = $service_provider->pronet_brand_id;
                $items['group'] = $group_id;
                $items['name'] = $service_provider->name;

                $result[] = $items;
            }
        }


        if ($request->ajax()) {
            return response()->json([
                'error' => $error,
                'values' => $result,
            ]);
        }
    }

    public function UpdateCurrentVersion(Request $request){

        if (!$this->user->hasAccess('applications.versions.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        try{
            $version_id = $request->id;
            $app_id     = $request->app_id;

            if(is_numeric($version_id) && is_numeric($app_id) ){
                $hash   = $this->randStrGen(6);

                $update = DB::table('applications')->where('id', $app_id)->update(
                    [
                        'current_version'       => $version_id,
                        'current_version_hash'  => $hash
                    ]
                );
            }
            return redirect('/admin/applications/'.$app_id.'/versions');
        }catch (Exception $e){
            \Log::warning('No se pudo actualizar el estado de la aplicacion '.$e);
            return redirect('/applications/'.$app_id.'/versions');
        }

    }

    private function randStrGen($len){
        $result = "";
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $charArray = str_split($chars);
        for($i = 0; $i < $len; $i++){
            $randItem = array_rand($charArray);
            $result .= "".$charArray[$randItem];
        }
        return $result;
    }
}
