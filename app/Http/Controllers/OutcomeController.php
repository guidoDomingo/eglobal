<?php

namespace App\Http\Controllers;

use App\Http\Requests\OutcomeRequest;
use App\Models\Outcomes;
use App\Models\Owner;
use App\Models\Provider;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;

class OutcomeController extends Controller
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
        if (!$this->user->hasAccess('outcomes')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if ($this->user->hasRole('security_admin') || $this->user->hasRole('superuser')) {
            \Log::info("Securty admin or SuperUser - Display all outcomes");
            $outcomes = Outcomes::paginate(20);
        }else{
            \Log::info("Display outcomes for Owner_id: {$this->user->owner_id}");
            $outcomes = Outcomes::where('owner_id', $this->user->owner_id)->paginate(20);
        }

        return view('outcomes.index', compact('outcomes'));
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!$this->user->hasAccess('outcomes.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if ($this->user->hasRole('security_admin') || $this->user->hasRole('superuser')) {
            \Log::info("Securty admin or SuperUser - Display all providers");
            $providers = Provider::pluck('business_name', 'id');
            $owners = Owner::pluck('name', 'id');
        }else{
            \Log::info("Display providers for Owner_id: {$this->user->owner_id}");
            $providers = Provider::where('owner_id', $this->user->owner_id)->pluck('business_name', 'id');
            $owners = null;
        }
        $data = [
            'providers' => $providers,
            'owners' => $owners,
            'selected_owner' => null,
            'selected_provider' => null
        ];

        return view('outcomes.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param OutcomeRequest|Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(OutcomeRequest $request)
    {
        if (!$this->user->hasAccess('outcomes.add|edit')) {
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
        }else{
            $input['owner_id'] = \Sentinel::getUser()->owner_id;
        }
        $input['created_by'] = \Sentinel::getUser()->id;
        try {
            Outcomes::create($input);
            // Todo ondanet
            Session::flash('message', 'Producto creado correctamente');
            return redirect('outcome');
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
        if (!$this->user->hasAccess('outcomes.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if ($outcome = Outcomes::find($id)){
            if ($this->user->hasRole('security_admin') || $this->user->hasRole('superuser')) {
                \Log::info("Securty admin or SuperUser - Display all providers");
                $providers = Provider::pluck('business_name', 'id');
                $owners = Owner::pluck('name', 'id');
            }else{
                \Log::info("Display providers for Owner_id: {$this->user->owner_id}");
                $providers = Provider::where('owner_id', $this->user->owner_id)->pluck('business_name', 'id');
                $owners = null;
            }
            $data = [
                'outcome' => $outcome,
                'providers' => $providers,
                'owners' => $owners,
                'selected_owner' => $outcome->owner_id,
                'selected_provider' => $outcome->provider_id
            ];
            return view('outcomes.edit', $data);
        }else{
            Session::flash('error_message', 'Servicio no encontrado');
            return redirect()->back();
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
        if (!$this->user->hasAccess('outcomes.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        if ($outcome = Outcomes::find($id)) {
            $input = $request->except(['_token', '_method']);
            $input['updated_by'] = $this->user->id;
            try {
                $outcome->update($input);
                Session::flash('message', 'Servicio actualizado correctamente');
                return redirect('outcome');
            } catch (\Exception $e) {
                \Log::warning("Error on update Outcome Id: {$outcome->id} | {$e->getMessage()}");
                Session::flash('error_message', 'Ha ocurrido un error al intentar actualizar el registro');
                return redirect('outcome');
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
        if (!$this->user->hasAccess('outcomes.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $message = '';
        $error = true;
        if ($outcome = Outcomes::find($id)) {
            try {
                Outcomes::destroy($id);
                $message = 'Servicio eliminado exitosamente';
                $error = false;
            } catch (\Exception $e) {
                \Log::warning("Error attempting to destroy outcome Id: {$id} - {$e->getMessage()}");
                $message = 'Ocurrio un error al intentar eliminar el servicio';
            }
        } else {
            $message = 'Producto no encontrado ';
            \Log::warning('Error attempting to destroy Outcome - Product not found');
        }
        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }
}
