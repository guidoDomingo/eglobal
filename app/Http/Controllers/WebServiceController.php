<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\WebService;
use App\Models\WebServiceProvider;
use App\Models\WebServiceRequest;
use App\Models\ServiceProviderProduct;
use Mockery\CountValidator\Exception;
use App\Http\Requests\StoreWebServiceRequest;
use App\Http\Requests\UpdateWebServiceRequest;
use Illuminate\Support\Facades\Session;
class WebServiceController extends Controller
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
        if (!$this->user->hasAccess('webservices')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $name = $request->get('name');
        $wsproviderId = $request->get('wsprovider');
        $webservices = WebService::filterAndPaginate($wsproviderId, $name);
   
        return view('webservices.index', compact('webservices', 'name', 'wsproviderId'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!$this->user->hasAccess('webservices.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $wsproviders = WebServiceProvider::all()->pluck('name', 'id');
        $app_categories = \DB::table('app_categories')
            ->select('name', 'id')
            ->orderBy("name")
            ->pluck('name', 'id');
        $data = ['wsproviders' => $wsproviders,
            'app_categories' => $app_categories];
        return view('webservices.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreWebServiceRequest $request)
    {
        if (!$this->user->hasAccess('webservices.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        try{
            $input = $request->all();
            $webservice = new WebService;
            $webservice->name = $input['name'];
            $webservice->app_categories_id = $input['app_categories_id'];
            $webservice->api_prefix = $input['api_prefix'];
            $webservice->url = $input['url'];
            $webservice->ip_address = $input['ip_address'];
            $webservice->port = ($input['port'] == "") ? 0 : $input['port'];
            $webservice->api_key = $input['api_key'];
            $webservice->created_by = $this->user->id;
            $webservice->service_provider_id = $input['service_provider_id'];
            $webservice->user_name = $input['user_name'];
            $webservice->password   = $input['password'];

            if ($webservice->save()) {
                $message = 'Agregado correctamente';
                Session::flash('message', $message);
                return redirect()->route('webservices.index');
            } else {
                \log::warning("Error al guardar el registro");
                Session::flash('error_message', 'Error al guardar el registro');
                return redirect()->route('webservices.index');
            }
        }catch (Exception $e){
            Session::flash('error_message', 'Error al guardar el registro');
            \log::warning("Error al guardar el registro ".$e);
            return redirect()->route('webservices.index');
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
        if (!$this->user->hasAccess('webservices.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $wsproviders = WebServiceProvider::all()->pluck('name', 'id');
        $app_categories = \DB::table('app_categories')
            ->select('name', 'id')
            ->orderBy("name")
            ->pluck('name', 'id');
        $webservice = WebService::where('id',$id)->first();
        $requests = WebServiceRequest::where('service_id',$id)->get();
        $wsproducts = ServiceProviderProduct::where('service_provider_id',$webservice->service_provider_id)->get()->pluck('description','id');
        $data = [
            'wsproviders' => $wsproviders,
            'webservice' => $webservice,
            'requests' => $requests,
            'wsproducts' => $wsproducts,
            'app_categories' => $app_categories
        ];
        return view('webservices.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id, UpdateWebServiceRequest $request)
    {
        if (!$this->user->hasAccess('webservices.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        try{
            $input = $request->all();
            $webservice = WebService::find($id);
            $webservice->app_categories_id = $input['app_categories_id'];
            $webservice->name = $input['name'];
            $webservice->api_prefix = $input['api_prefix'];
            $webservice->url = $input['url'];
            $webservice->ip_address = $input['ip_address'];
            $webservice->port = ($input['port'] == "") ? 0 : $input['port'];
            $webservice->api_key = $input['api_key'];
            $webservice->updated_by = $this->user->id;
            $webservice->service_provider_id = $input['service_provider_id'];
            $webservice->user_name = $input['user_name'];
            $webservice->password = $input['password'];

            if ($webservice->save()) {
                $message = 'Actualizado correctamente';
                Session::flash('message', $message);
                return redirect()->back();
            } else {
                Session::flash('error_message', 'Error al guardar el registro');
                return redirect()->back();
            }

        }catch (Exception $e){
            Session::flash('error_message', 'Error al guardar el registro');
            \Log::warning('Error al guardar el registro ' .$e);
            return redirect()->back();
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        if (!$this->user->hasAccess('webservices.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $encontrado = WebServiceRequest::where('service_id', $id)->get();
        $webservice = WebService::find($id);
        if (count($encontrado) > 0) {
            $error = true;
            $message = "Este Web Service posee registros relacionados";
        } else {
            if ($webservice->delete()) {
                $error = false;
                $message = $webservice->name . ' ha sido eliminado correctamente';
            } else {
                $error = true;
                $message = $webservice->name . " no se pudo eliminar";
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'error' => $error,
                'message' => $message,
            ]);
        } else {
            Session::flash('error-message', $message);
            return redirect()->route('webservices.index');
        }

    }

    public function UpdateServiceStatus($id, Request $request)
    {
        if (!$this->user->hasAccess('webservices.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $service_id     = $request->id;
        $value     = $request->value;
        try{
        if(is_numeric($service_id) && is_numeric($value) ){
            $update = \DB::table('services')->where('id', $service_id)->update(
                [
                    'status'       => $value
                ]
            );
        }
        return redirect('/webservices');
        }catch (Exception $e){
            \Log::warning('No se pudo actualizar el estado del servicio '.$e);
            Session::flash('error_message', 'No se pudo actualizar el estado del servicio');
            return redirect('/webservices');
        }

    }
}
