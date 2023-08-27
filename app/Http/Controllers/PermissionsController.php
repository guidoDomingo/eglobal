<?php

namespace App\Http\Controllers;

use App\Http\Requests\PermissionRequest;
use App\Models\Permission;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use App\Http\Requests;
use Illuminate\Support\MessageBag;

class PermissionsController extends Controller
{
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
        if (!$this->user->hasAccess('permissions')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            return redirect('/')->with('error', 'No posee permisos para realizar esta accion.');
        }

        $name = $request->get('name');
        $permissions = Permission::filterAndPaginate($name);
        $data['permissions'] = $permissions;
        return view('permissions.index', $data);
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!$this->user->hasAccess('permissions.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            return redirect('/')->with('error', 'No posee permisos para realizar esta accion.');
        }
        
        $permission = \Session::get('permission', new Permission());
        return view('permissions.create', compact('permission'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param PermissionRequest|Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(PermissionRequest $request)
    {
        if (!$this->user->hasAccess('permissions.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            return redirect('/')->with('error', 'No posee permisos para realizar esta accion.');
        }
        
        $input = $request->except('_token');
        $input['permission'] = strtolower(trim($input['permission']));

        \Log::notice('Creating new Permission',
            ['action' => 'permissions.store', 'input' => $input]);

        try {

            $permission = new Permission();
            $permission->permission = $request->get('permission');
            $permission->description = $request->get('description');

            $permission->save();

            return redirect()
                ->route('permissions.index')
                ->with('success', 'Permiso creado exitosamente.');


        } catch (QueryException $e) {

            if ($e->getCode() == 23505) {
                \Log::error('Unique permission violation.',
                    ['action' => 'permissions.store', 'input' => $input, 'message' => $e->getMessage()]);

                return redirect()->route('permissions.create')
                    ->with('permission', $permission)
                    ->withErrors((new MessageBag())->add('permission', 'El Permiso ya existe.'));
            }


            \Log::critical($e->getMessage(),
                ['action' => 'permissions.store', 'input' => $input]);
            return view('permissions.create')
                ->with('permission', $permission)
                ->with('error', 'Ha ocurrido un error, intentelo mas tarde.');

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
        if (!$this->user->hasAccess('permissions.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            return redirect('/')->with('error', 'No posee permisos para realizar esta accion.');
        }
        
        if (!$permission = Permission::find($id)) {
            \Log::notice('Permission doesnt exists', ['action' => 'permissions.edit', 'id' => $id]);
            return redirect()
                ->route('permissions.edit')
                ->with('error', 'El Permiso no existe.');
        }

        return view('permissions.edit', compact('permission'));
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
        if (!$this->user->hasAccess('permissions.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            return redirect('/')->with('error', 'No posee permisos para realizar esta accion.');
        }
        
        $input = $request->except(['_token', '_method']);

        $input['permission'] = strtolower(trim($input['permission']));

        \Log::notice('Updating permission',
            ['action' => 'permissions.update', 'input' => $input]);

        if (!$permission = Permission::find($id)) {
            \Log::notice('Permission doesnt exists', ['action' => 'permissions.update', 'id' => $id]);
            return redirect()
                ->route('permissions.edit')
                ->with('error', 'El Permiso no existe.');
        }

        $permission->fill($input);

        try {

            //TODO update all Roles and Users permissions
            $permission->save();

            return redirect()
                ->route('permissions.index')
                ->with('success', 'Permiso actualizado exitosamente.');


        } catch (QueryException $e) {

            if ($e->getCode() == 23505) {
                \Log::error('Unique permission violation.',
                    ['action' => 'permissions.udpate', 'input' => $input]);

                return redirect()->route('permissions.create')
                    ->with('permission', $permission)
                    ->withErrors((new MessageBag())->add('permission', 'El Permiso ya existe.'));
            }

            \Log::critical($e->getMessage(),
                ['action' => 'permissions.udpate', 'input' => $input]);
            return redirect()->route('permissions.create')
                ->with('permission', $permission)
                ->with('error', 'Ha ocurrido un error, intentelo mas tarde.');
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
        if (!$this->user->hasAccess('permissions.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            return redirect('/')->with('error', 'No posee permisos para realizar esta accion.');
        }
        $message = '';
        $error = '';

        if ($permission = Permission::find($id)) {
            \Log::notice('Deleting permission',
                ['action' => 'permissions.delete', 'id' => $id]);

            try {
                if (Permission::destroy($id)){
                    $message =  'Permiso eliminado correctamente';
                    $error = false;
                }
            } catch (QueryException $e) {
                \Log::critical($e->getMessage(),
                    ['action' => 'permissions.delete', 'id' => $id]);
                $message =  'EError intentando eliminar el permiso, intente nuevamente';
                $error = true;
            }
        }else{
            \Log::notice('Permission doesnt exists', ['action' => 'permissions.delete', 'id' => $id]);
            $message =  'Red no encontrada';
            $error = true;
        }
        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);

    }
}
