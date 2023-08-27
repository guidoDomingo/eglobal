<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\WebServiceViewsBuilder;
use App\Models\WebservicesControllers;


class WebServiceViewsBuilderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($wsproduct_id, $wsscreen_id)
    {
        $objects_controls = \DB::table('services_views')
            ->select('services_views.id','service_id', 'services_views.screen_id','tipo','longitud_min','longitud_max','valorMinimo','requerido','oculto','editable','err_msg','model','controller','object_id','value','view_id','name','object_type_id')
            ->leftjoin('screen_objects','screen_objects.id','=','services_views.object_id')
            ->where('service_id', $wsproduct_id)
            ->where('view_id', $wsscreen_id)
            ->get();

        return view('webservicesbuilderviews.index', compact('wsproduct_id','wsscreen_id','objects_controls'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($wsproduct_id, $wsscreen_id)
    {
        $view = \DB::table('services_views_description')->where('id', $wsscreen_id)->first();
        $screen_id = $view->screen_id;


        $screen_objects = \DB::table('screen_objects')->where('screen_id', $screen_id)
            ->pluck('name','id');
        $screen_objects[0] = 'Ninguno';

        //get service request
        $service_requests = \DB::table('service_provider_product_service_request')
            ->join('service_requests','service_requests.id','=','service_provider_product_service_request.service_request_id')
            ->where('service_provider_product_id',$wsproduct_id)
            ->pluck('keyword','id');

        //get service models
        $models = \DB::table('services_model')
            ->where('service_id',$wsproduct_id)
            ->pluck('key','key');

        //get service views
        $screens = \DB::table('services_views_description')
            ->where('service_id',$wsproduct_id)
            ->pluck('description','id');

        $objects_types = \DB::table('screens_mask')->pluck('value','value');

        return view('webservicesbuilderviews.create',compact('wsproduct_id','wsscreen_id','screen_objects','objects_types','service_requests','models','screens'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($wsproduct_id,$wsscreen_id,Request $request)
    {
        //obtener screen_id
        $screen = \DB::table('services_views_description')->where('id',$wsscreen_id)->first();
        $controller_id = null;
        //si es necesario, insertar un controller
        if($request->on_success_path <> ""){
            $webServiceController = new WebservicesControllers;
            $webServiceController->service_id = $wsproduct_id;
            $webServiceController->request_service_id = ($request->request_service_id <> "")?$request->request_service_id:null;
            //get endpoint to selected request_ service_id
            $endpoint = null;
            if($request->request_service_id){
                $service_request_data = \DB::table('service_requests')
                    ->where('id',$request->request_service_id)
                    ->get();
                $endpoint = $service_request_data[0]->endpoint;
            }
            $webServiceController->endpoint = $endpoint;
            $webServiceController->model = ($request->controller_model <> "")?$request->controller_model:null;
            $webServiceController->on_success_path = ($request->on_success_path <> "")?$request->on_success_path:null;
            $webServiceController->on_fail_path =   ($request->on_fail_path <> "")?$request->on_fail_path:null;

            $webServiceController->save();
            $controller_id = $webServiceController->id;
        }

        //se insertan los detalles de los objetos
        $webServiceView = new WebServiceViewsBuilder;
        $webServiceView->service_id     =  $wsproduct_id;
        $webServiceView->screen_id      =  $screen->screen_id;
        $webServiceView->tipo           =  $request->tipo;
        $webServiceView->longitud_min   =  ($request->longitud_min <>  "")?$request->longitud_min:null;
        $webServiceView->longitud_max   =  ($request->longitud_max <> "")?$request->longitud_max:null;
        $webServiceView->valorMinimo    =  ($request->valorMinimo <> "")?$request->valorMinimo:null;

        $webServiceView->requerido      =  ($request->requerido == 1) ? "true":"false";
        $webServiceView->oculto         =  ($request->oculto == 1)? "true":"false";
        $webServiceView->editable       =  ($request->editable ==  1)? "true":"false";

        $webServiceView->err_msg        =  ($request->err_msg <> "")?$request->err_msg:null;
        $webServiceView->model          =  ($request->service_model <> "")?$request->service_model:null;
        $webServiceView->controller     =  $controller_id;
        $webServiceView->object_id      =  $request->object_id;
        $webServiceView->value          =  $request->value;
        $webServiceView->view_id        =  $wsscreen_id;

        $webServiceView->save();

        return redirect()->route('wsproducts.wsbuilder.views.index', [$wsproduct_id,$wsscreen_id]);
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
    public function edit($wsproduct_id,$wsscreen_id, $objects_control)
    {
        //dd($wsproduct_id.' / '.$wsscreen_id.' / '. $objects_control);

        $view = \DB::table('services_views_description')->where('id', $wsscreen_id)->first();
        $screen_id = $view->screen_id;

        $screen_objects = \DB::table('screen_objects')->where('screen_id', $screen_id)
            ->pluck('name','id');
        $screen_objects[0] = 'Ninguno';

        //get service request
        $service_requests = \DB::table('service_provider_product_service_request')
            ->join('service_requests','service_requests.id','=','service_provider_product_service_request.service_request_id')
            ->where('service_provider_product_id',$wsproduct_id)
            ->pluck('keyword','id');

        //get service models
        $models = \DB::table('services_model')
            ->where('service_id',$wsproduct_id)
            ->pluck('key','key');

        //get service views
        $screens = \DB::table('services_views_description')
            ->where('service_id',$wsproduct_id)
            ->pluck('description','id');

        $objects_types = \DB::table('screens_mask')->pluck('value','value');

        $service_view = \DB::table('services_views')
            ->select('services_views.id','screen_id','tipo','longitud_min','longitud_max','valorMinimo','requerido','oculto','editable','err_msg','services_views.model as service_model','controller','object_id','value','view_id','request_service_id','endpoint','services_controllers.model as controller_model','on_success_path','on_fail_path')
            ->leftJoin('services_controllers','services_controllers.id','=','services_views.controller')
            ->where('services_views.id', $objects_control)->first();

        return view('webservicesbuilderviews.edit',compact('wsproduct_id','wsscreen_id','screen_objects','objects_types','service_requests','models','screens','objects_control','service_view'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $wsproduct_id, $wsscreen_id, $id )
    {
        try{

            //obtener screen_id
            $screen = \DB::table('services_views_description')->where('id',$wsscreen_id)->first();

            //como se va a editar el objeto se resetea el controller asignado anteriormente
            $current_controller = \DB::table('services_views')->where('id',$id)->first();

            //si existe un control previamente asignado eliminar
            if($current_controller->controller){
                $remove_controller = \DB::table('services_controllers')->where('id', $current_controller->controller)->delete();
            }
            $controller_id = null;
            //si es necesario, insertar un nuevo controller
            if($request->on_success_path <> ""){
                $webServiceController = new WebservicesControllers;
                $webServiceController->service_id = $wsproduct_id;
                $webServiceController->request_service_id = ($request->request_service_id <> "")?$request->request_service_id:null;
                //get endpoint to selected request_ service_id
                $endpoint = null;
                if($request->request_service_id){
                    $service_request_data = \DB::table('service_requests')
                        ->where('id',$request->request_service_id)
                        ->get();
                    $endpoint = $service_request_data->endpoint;
                }
                $webServiceController->endpoint = $endpoint;
                $webServiceController->model = ($request->controller_model <> "")?$request->controller_model:null;
                $webServiceController->on_success_path = ($request->on_success_path <> "")?$request->on_success_path:null;
                $webServiceController->on_fail_path =   ($request->on_fail_path <> "")?$request->on_fail_path:null;

                $webServiceController->save();
                $controller_id = $webServiceController->id;
            }

            $webServiceView =  WebServiceViewsBuilder::where('id',$id)->update([
                'service_id'    => $wsproduct_id,
                'screen_id'     => $screen->screen_id,
                'tipo'          => $request->tipo,
                'longitud_min'  => ($request->longitud_min <>  "")?$request->longitud_min:null,
                'longitud_max'  => ($request->longitud_max <> "")?$request->longitud_max:null,
                'valorMinimo'   => ($request->valorMinimo <> "")?$request->valorMinimo:null,
                'requerido'     => ($request->requerido == 1) ? "true":"false",
                'oculto'        => ($request->oculto == 1)? "true":"false",
                'editable'      => ($request->editable ==  1)? "true":"false",
                'err_msg'       => ($request->err_msg <> "")?$request->err_msg:null,
                'model'         => ($request->service_model <> "")?$request->service_model:null,
                'controller'    => $controller_id,
                'object_id'     => $request->object_id,
                'value'         => $request->value,
                'view_id'       => $wsscreen_id

            ]);

            return redirect()->route('wsproducts.wsbuilder.views.index', [$wsproduct_id,$wsscreen_id]);
        }catch(\Exception $e){
            return redirect()->route('wsproducts.wsbuilder.views.index', [$wsproduct_id,$wsscreen_id]);
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
        //
    }
}
