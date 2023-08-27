<?php

namespace App\Http\Controllers;

use App\Http\Requests\ScreenRequest;
use App\Models\Applications;
use App\Models\Screens;
use App\Models\WebServiceProvider;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Session;

class ScreenController extends Controller
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
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index($appId, Request $request)
    {
        if (!$this->user->hasAccess('applications.screens')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $name = $request->get('name');
        if ($screens = Screens::filterAndPaginate($appId, $name)) {
           $data = [
               'screens' => $screens,
               'name' => $name,
               'app_id' => $appId,
            ];
            return view('screens.index', $data);
        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @param null $appId
     * @return \Illuminate\Http\Response
     * @internal param null $atmId
     */

    public function create($appId = null)
    {
        if (!$this->user->hasAccess('applications.screens.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if (!is_null($appId)) {
            $app = Applications::where('id', $appId)->pluck('name', 'id');
        } else {
            // Rodri decia que esto debia ser por usuarios - Yo estoy en desacuerdo
            if ($this->user->hasRole('security_admin') || $this->user->isSuperuser()) {
                $app = Applications::pluck('name', 'id');
                // TODO filter by owner_id - Angel request
//                $serviceProvider = 
            } else {
                $app = Applications::where('owner_id', $this->user->owner_id)->pluck('name', 'id');
            }
        }

        $serviceProvider = WebServiceProvider::orderBy('name')->pluck('name', 'id');

        $data = [
            'applications' => $app,
            'app_id' => $appId,
            'service_providers' => $serviceProvider
        ];

        return view('screens.create', $data);


    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ScreenRequest|Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(ScreenRequest $request)
    {
        if (!$this->user->hasAccess('applications.screens.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = $request->except('_token');
        if ($input['service_provider_id'] <> 0) {
            $input['application_id'] = null;
        }

        if ($input['service_provider_id'] == "") {
            $input['service_provider_id'] = null;
        }
        $input['version_hash'] = Str::random(40);
        try {
            // It's alive!
            Screens::create($input);
            Session::flash('message', 'Registro creado exitosamente');
            //return redirect('screens');
            return redirect()->route('applications.screens.index', ['application' => $input['application_id']]);
        } catch (\Exception $e) {
            \Log::error("Error creating a new Screen - {$e->getMessage()}");
            Session::flash('error_message', 'Error al intentar crear el registro, intente nuevamente');
            return redirect()->back()->withInput();
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
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function edit($id, Request $request)
    {
        if (!$this->user->hasAccess('applications.screens.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        if ($screen = Screens::find($id)) {
            $appId = $request->get('app_id');
            if (!is_null($appId)) {
                $app = Applications::where('id', $appId)->pluck('name', 'id');
            } else {
                if ($this->user->hasRole('security_admin') || $this->user->isSuperuser()) {
                    $app = Applications::pluck('name', 'id');
                    // TODO filter by owner_id - Angel request
//                $serviceProvider =
                } else {
                    $app = Applications::where('owner_id', $this->user->owner_id)->pluck('name', 'id');
                }
            }

            $serviceProvider = WebServiceProvider::orderBy('name')->pluck('name', 'id');

            $data = [
                'applications' => $app,
                'service_providers' => $serviceProvider,
                'screen' => $screen,
                'app_id' => $screen->application_id,
            ];

            return view('screens.edit', $data);
        } else {
            Session::flash('error_message', 'Pantalla no encontrada');
            return redirect()->back();
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ScreenRequest|Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(ScreenRequest $request, $id)
    {
        if (!$this->user->hasAccess('applications.screens.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        if ($screen = Screens::find($id)) {
            $input = $request->all();
            if ($input['service_provider_id'] <> 0) {
                $input['application_id'] = null;
            }
            if ($input['service_provider_id'] == "") {
                $input['service_provider_id'] = null;
            }
            $input['version_hash'] = Str::random(40);

            $screen->fill($input);
            try {
                $screen->save();
                Session::flash('message', 'Actualizado Correctamente');
                return redirect()->route('applications.screens.index', ['application' => $screen->application_id]);
            } catch (\Exception $e) {
                \Log::error("Error updating a Screen - {$e->getMessage()}");
                Session::flash('error_message', 'Error al intentar actualizar el registro, intente nuevamente');
                return redirect()->back()->withInput();
            }
        } else {
            Session::flash('error_message', 'Pantalla no encontrada');
            return redirect()->back();
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
        if (!$this->user->hasAccess('applications.screens.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $message = '';
        $error = true;
        if ($screen = Screens::find($id)) {
            try{
                Screens::destroy($id);
                $message = 'Punto de venta eliminado exitosamente';
                $error = false;
            }catch (\Exception $e){
                $message = 'Ocurrio un error al intentar eliminar el cajero';
                \Log::warning("Error attempting to destroy Screen | {$e->getMessage()}");
            }
        } else {
            $message = 'Pantallano encontrada';
            \Log::warning('Error attempting to destroy screen - Screen not found');
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }
}
