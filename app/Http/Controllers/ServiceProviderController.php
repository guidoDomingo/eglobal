<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\WebServiceProvider;
use App\Http\Requests\StoreWebServiceProviderRequest;
use App\Http\Requests\UpdateWebServiceProviderRequest;
use Session;


class ServiceProviderController extends Controller
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
        if (!$this->user->hasAccess('webservices.providers')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $name = $request->get('name');
        $wsproviders = WebServiceProvider::filterAndPaginate($name);
        return view('wsproviders.index', compact('wsproviders', 'name'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!$this->user->hasAccess('webservices.providers.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if (!$this->user->hasAccess('webservices.providers.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        return view('wsproviders.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreWebServiceProviderRequest $request)
    {
        if (!$this->user->hasAccess('webservices.providers.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $wsprovider = new WebServiceProvider;
        $wsprovider->fill(['created_by' => $this->user->id]);
        $wsprovider->fill($request->all());
        $wsprovider->save();
        $message = 'Agregado correctamente';
        Session::flash('message', $message);
        return redirect()->route('wsproviders.index');
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
        if (!$this->user->hasAccess('webservices.providers.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $wsprovider = WebServiceProvider::find($id);
        $data = [
            'wsprovider' => $wsprovider,
        ];
        return view('wsproviders.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id, UpdateWebServiceProviderRequest $request)
    {
        if (!$this->user->hasAccess('webservices.providers.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $wsprovider = WebServiceProvider::find($id);
        $wsprovider->fill($request->all());
        $wsprovider->fill(['updated_by' => $this->user->id]);
        $wsprovider->save();

        $message = 'Actualizado correctamente';
        Session::flash('message', $message);
        return redirect()->route('wsproviders.index');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        if (!$this->user->hasAccess('webservices.providers.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $wsprovider = WebServiceProvider    ::find($id);

        if ($wsprovider->delete()) {
            $error = false;
            $message = $wsprovider->name . ' ha sido eliminado correctamente';
        } else {
            $error = true;
            $message = $wsprovider->name . " no se pudo eliminar";
        }

        if ($request->ajax()) {
            return response()->json([
                'error' => $error,
                'message' => $message,
            ]);
        } else {
            Session::flash('message', $message);
            return redirect()->route('wsproviders.index');
        }

    }
}
