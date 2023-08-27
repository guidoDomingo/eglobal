<?php

namespace App\Http\Controllers;

use App\Http\Requests\RoleRequest;
use App\Models\Permission;
use App\Models\Role;
use Response;
use Session;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class RolesController extends Controller
{
    /**
     * Currently logged in User
     * @var |Cartalyst|Sentinel|Users|UserInterface
     */
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    /**
     * Display a list of roles available
     * @return Response
     */
    public function index(Request $request)
    {
        if (!$this->user->hasAccess('roles')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            return redirect('/')->with('error', 'No posee permisos para realizar esta accion.');
        }

        $description = $request->get('name');
        $roles = Role::filterAndPaginate($description);
        return view('roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new role
     * @return Response
     */
    public function create()
    {
        if (!$this->user->hasAccess('roles.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            return redirect('/')->with('error', 'No posee permisos para realizar esta accion.');
        }

        $permissions = Permission::orderBy('permission')->where('deleted_at',null)->get();
        return view('roles.create', compact('permissions'));
    }

    /**
     * Store a new role
     * @param RoleRequest $request
     * @return Response
     */
    public function store(RoleRequest $request)
    {
        if (!$this->user->hasAccess('roles.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            return redirect('/')->with('error', 'No posee permisos para realizar esta accion.');
        }

        $input = $request->all();
        $rolePermissions = [];
        if (isset($input['permissions']))
            foreach ($input['permissions'] as $p => $v) {
                if ($v['inherited'] === '0') {
                    $rolePermissions[$p] = true;
                }
            }
        $input['permissions'] = $rolePermissions;
        if ($role = Role::create($input)) {
            \Log::info('Rol creado.', $role->toArray());

            return redirect()
                ->route('roles.index')
                ->with('success', 'Rol creado correctamente');
        }

        \Log::error('Creacion de Rol.', $input);
        return redirect()
            ->route('roles.create')
            ->withInput()
            ->with('error', 'Problemas al crear el Rol de Usuario');
    }

    /**
     * Show the form for editing the given role
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        if (!$this->user->hasAccess('roles.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            return redirect('/')->with('error', 'No posee permisos para realizar esta accion.');
        }

        if ($role = Role::find($id)){
            $permissions = Permission::orderBy('permission')->where('deleted_at',null)->get();

            /* Array for displaying permissions section on view */
            foreach ($permissions as $p) {
                if ($role->hasAccess($p->permission)) {
                    $p['has'] = true;
                } else {
                    $p['has'] = false;
                }
            }
            return view('roles.edit', compact(['role', 'permissions']));
        }

        return redirect()->back()->with('error', 'Rol no encontrado.');
    }

    /**
     * Update the specific role
     * @param RoleRequest $request
     * @param int $id
     * @return Response
     */
    public function update(RoleRequest $request, $id)
    {
        if (!$this->user->hasAccess('roles.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            return redirect('/')->with('error', 'No posee permisos para realizar esta accion.');
        }

        if (!$role = Role::find($id)){
            Session::flash('error_message', 'Rol no encontrado');
            return redirect('roles');
        }
        
        $input = $request->all();

        $currentPermissions = $role->permissions;
        $expectedPermissions = Arr::pull($input, 'permissions');

        $expectedPermissions = empty($expectedPermissions) ? [] : $expectedPermissions;
        foreach ($expectedPermissions as $p => $v) {
            if ($v['inherited'] === '0') {
                if (!array_key_exists($p, $currentPermissions))
                    $role->addPermission($p);
            }
        }

        foreach ($currentPermissions as $p => $v) {
            if (!in_array($p, $expectedPermissions) && $expectedPermissions[$p]['inherited'] != '')
                $role->removePermission($p);
        }
        $role->fill($input);

        if ($role->save()) {

            \Log::info('Rol actualizado.', $role->toArray());

            #$role->killUsersSession();

            return redirect()
                ->route('roles.index')
                ->with('success', 'Rol actualizado correctamente');
        }


        \Log::error('Actualizacion de Rol.', $role->toArray());
        return redirect()
            ->route('roles.index')
            ->with('error', 'Problemas al actualizar Rol de Usuario');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        if (!$this->user->hasAccess('roles.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            return redirect('/')->with('error', 'No posee permisos para realizar esta accion.');
        }
        $message = '';
        $error = '';
        if ($role = Role::find($id)) {
            try {
                if (Role::destroy($id)) {
                    $message = 'Rol eliminado correctamente';
                    $error = false;
                }
            } catch (\Exception $e) {
                \Log::error("Error deleting role: " . $e->getMessage());
                $message = 'Error al intentar eliminar el rol';
                $error = true;
            }
        }else{
                \Log::warning("Role {$id} not found");
                $message =  'Rol no encontrado';
                $error = true;
            }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }
}