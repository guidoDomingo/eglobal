<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProvidersRequest;
use App\Models\Provider;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;

class ProvidersController extends Controller
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
        if (!$this->user->hasAccess('providers')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $providers = Provider::paginate(20);
        return view('providers.index', compact('providers'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!$this->user->hasAccess('providers.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        return view('providers.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ProvidersRequest|Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProvidersRequest $request)
    {
        if (!$this->user->hasAccess('providers.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $input = $request->all();
        if ($provider = Provider::where('ruc', $input['ruc'])->count() == 0) {
            $input['created_by'] = $this->user->id;
            try {
                Provider::create($input);
                Session::flash('message', 'Proveedor modificado exitosamente');
                return redirect('providers');
            } catch (\Exception $e) {
                \Log::error("Error on insert provider {$e->getMessage()}");
                Session::flash('error_message', 'Hubo un problema al actualizar el registro, 
                intenten nuevamente mas tarde');
                return redirect()->back();
            }
        } else {
            Session::flash('error_message', 'Ya existe un proveedor con el mismo RUC registrado');
            return redirect()->back();
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
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!$this->user->hasAccess('providers.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if ($provider = Provider::find($id)) {
            $data = ['provider' => $provider];
            return view('providers.edit', $data);
        } else {
            Session::flash('error_message', 'Proveedor no encontrado');
            return redirect()->back();
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param ProvidersRequest|Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProvidersRequest $request, $id)
    {
        if (!$this->user->hasAccess('providers.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if ($provider = Provider::find($id)) {
            $input = $request->all();
            $input['updated_by'] = $this->user->id;
            $provider->fill($input);
            try {
                $provider->save();
                Session::flash('message', 'Proveedor actualizado correctamente');
                return redirect('providers');
            } catch (\Exception $e) {
                \Log::error("Error on update provider {$e->getMessage()}");
                Session::flash('error_message', 'Hubo un problema al actualizar el registro, 
                intenten nuevamente mas tarde');
                return redirect()->back();
            }
        } else {
            Session::flash('error_message', 'Proveedor no encontrado');
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
        if (!$this->user->hasAccess('providers.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $message = '';
        $error = true;
        if ($provider = Provider::find($id)) {
            try {
                Provider::destroy($id);
                    $message = 'Punto de venta eliminado exitosamente';
                    $error = false;
            } catch (\Exception $e) {
                \Log::error("Error on update provider {$e->getMessage()}");
                $message= 'Hubo un problema al actualizar el registro, 
                intenten nuevamente mas tarde';
            }

        } else {
            $message = 'Punto de venta no encontrasdo';
            \Log::warning('Error attempting to destroy pos - Pos not found');
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }
}
