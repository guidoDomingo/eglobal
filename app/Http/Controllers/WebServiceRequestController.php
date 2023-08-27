<?php

namespace App\Http\Controllers;

use App\Models\WebServiceRequest;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Requests\StoreWSRequestRequest;
use App\Models\WebService;
use App\Http\Requests\UpdateWSRequestRequest;
use App\Http\Controllers\Controller;
use Mockery\CountValidator\Exception;

class WebServiceRequestController extends Controller
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
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreWSRequestRequest $request)
    {
        if (!$this->user->hasAccess('webservices.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = $request->all();

        $webservice = WebService::find($input['service_id']);
        $wsrequest = new WebServiceRequest;
        $wsrequest->endpoint = $input['endpoint'];
        $wsrequest->keyword = $input['keyword'];
        $wsrequest->cacheable = (isset($input['cacheable'])) ? true : false;
        $wsrequest->transactional = (isset($input['transactional'])) ? true : false;
        $wsrequest->created_by = $this->user->id;
        $wsrequest->get_fields = "{}";
        $wsrequest->post_fields = "{}";
        $wsrequest->response_fields = "{}";
        $wsrequest->service_id = $webservice->id;

        if ($wsrequest->save()) {
            if($wsrequest->transactional == true && isset($input['transactional'])){
                $wsrequest->serviceproviderproducts()->attach($input['service_provider_product_id']);
            }
            $message = 'Agregado correctamente';
            $error = false;
            $newobject = $wsrequest->toArray();
        } else {
            $error = true;
            $message = "No se pudo guardar el registro";
            $newobject = null;
        }
        if ($request->ajax()) {
            return response()->json([
                'error' => $error,
                'message' => $message,
                'object' => $newobject,
            ]);
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
        if (!$this->user->hasAccess('webservices')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $wsrequest = WebServiceRequest::find($id);

        if ($wsrequest->transactional == true) {
            $wsrequest->service_provider_product_id = $wsrequest->serviceproviderproducts()->first()->id;
        } else {
            $wsrequest->service_provider_product_id = null;
        }
        return response()->json($wsrequest, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateWSRequestRequest $request, $id)
    {
        if (!$this->user->hasAccess('webservices.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = $request->all();
        $webservice = WebService::find($input['service_id']);

        $webservicerequest = WebServiceRequest::find($id);

        if (strpos($input['endpoint'], $webservice->api_prefix) == 0) {
            $webservicerequest->endpoint = $input['endpoint'];
        } else {
            $webservicerequest->endpoint = $webservice->api_prefix . '/' . $input['endpoint'];
        }
        $webservicerequest->keyword = $input['keyword'];
        $webservicerequest->cacheable = (isset($input['cacheable'])) ? true : false;
        $webservicerequest->transactional = (isset($input['transactional'])) ? true : false;
        if($webservicerequest->transactional == true && isset($input['transactional'])){
            // Updates intermediate table with the given id
            $webservicerequest->serviceproviderproducts()->sync([$input['service_provider_product_id']]);
        }
        $webservicerequest->updated_by = $this->user->id;

        if ($webservicerequest->save()) {
            $message = 'Actualizado correctamente';
            $error = false;
            $updatedobject = $webservicerequest->toArray();
        } else {
            $error = true;
            $message = "No se pudo guardar el registro";
            $updatedobject = null;
        }
        if ($request->ajax()) {
            return response()->json([
                'error' => $error,
                'message' => $message,
                'object' => $updatedobject,
            ]);
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

        $webservicerequest = WebServiceRequest::find($id);
        if ($webservicerequest->delete()) {
            $error = false;
            $message = $webservicerequest->endpoint . ' ha sido eliminado correctamente';
        } else {
            $error = true;
            $message = $webservicerequest->endpoint . " no se pudo eliminar";
        }

        if ($request->ajax()) {
            return response()->json([
                'error' => $error,
                'message' => $message,
            ]);
        }
    }
}
