<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Requests\UsersRequest;
use App\Models\Owner;
use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use App\Services\Password;
use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Log;
use Mail;
use Carbon\Carbon;

use App\Http\Controllers\TerminalInteractionMonitoring\TerminalInteractionAccessController;
use Illuminate\Support\Arr;

class UsersController extends Controller
{
    /**
     * Currently logged in User
     * @var |Cartalyst|Sentinel|Users|UserInterface
     */
    protected $user;

    /**
     * Create a new Authentication controller instance
     */
    public function __construct()
    {
        $this->middleware('auth', ['except' => 'activate']);
        $this->user = \Sentinel::getUser();
    }

    /**
     * Display a list for all Users
     * @return |Response
     */
    public function index(Request $request)
    {
        if (!$this->user->hasAccess('users')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            return redirect('/')->with('error', 'No posee permisos para realizar esta accion.');
        }
        $description = $request->get('name');
        $users = User::filterAndPaginate($description);
        return view('users.index', compact('users'));
    }

    public function create()
    {
        if (!$this->user->hasAccess('users.add|edit')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'action' => \Request::route()->getActionName()]
            );
            return redirect('/')->with('error', 'No posee permisos para realizar esta accion.');
        }

        $permissions = Permission::orderBy('permission')->get();
        $branches = Branch::all(['description', 'id']);
        $branchJson = json_encode($branches);
        $rolesList = Role::all(['id', 'name']);
        $rolesJson = json_encode($rolesList);
        $owners = Owner::all(['name', 'id']);
        $ownersJson = json_encode($owners);
        $data = [
            'permissions' => $permissions, 'branchJson' => $branchJson,
            'rolesJson' => $rolesJson, 'ownersJson' => $ownersJson
        ];

        return view('users.create', $data);
    }

    public function store(UsersRequest $request, Password $password)
    {
        if (!$this->user->hasAccess('users.add|edit')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'action' => \Request::route()->getActionName()]
            );
            return redirect('/')->with('error', 'No posee permisos para realizar esta accion.');
        }

        $input = $request->all();

        if($request->ajax()){
            $respuesta = [];
            try{

                $generatePass = $password->generatePassword();
                if (\Sentinel::getUser()->isSuperUser()) {
                    $ownerId = $request->get('owner_id', null);
                    if (empty($ownerId)) $ownerId = null;
                } else {
                    $ownerId = \Sentinel::getUSer()->owner_id;
                }
                $credentials = [
                    'username' => $input['username'],
                    'email' => $input['email'],
                    'doc_number' => ($input['doc_number'] <> '') ? $input['doc_number'] : null,
                    'description' => $input['description'],
                    'password' => $generatePass,
                    'owner_id' => $ownerId
                ];

                if (!empty($request->get('branch_id'))) {
                    $permitedBranches = Branch::where('owner_id', '=', \Sentinel::getUser()->owner_id)->get();
                    $permitedBranches = Branch::where('owner_id', '=', 1);
                    $wishedBranches = Branch::find($request->get('branch_id'));

                    if ($permitedBranches->search($wishedBranches) === false) {
                        return redirect()->back()->with('error', 'La sucursal no pertenece a su red');
                    }
                    $credentials['branch_id'] = $request->get('branch_id');
                }

                if ($user = \Sentinel::register($credentials)) {
                    $expectedPermissions = Arr::pull($input, 'permissions');
                    $expectedPermissions = empty($expectedPermissions) ? [] : $expectedPermissions;

                    foreach ($expectedPermissions as $p => $v) {
                        if ($v['inherited'] === '0') {
                            $user->addPermission($p, isset($v['state']) ? filter_var(
                                $v['state'],
                                FILTER_VALIDATE_BOOLEAN
                            ) : false);
                        }
                    }

                    if (!$user->save()) {
                        \Log::error('Cant update Users Permissions.', $input);
                        return redirect()
                            ->back()
                            ->withInput()
                            ->with('error', 'Problemas al actualizar registro.');
                    }
                }

                $activation = \Activation::create($user);

                $data = [
                    'user' => $user,
                    'id' => $user->id,

                    'password' => $generatePass,
                    'link' => route('users.activate', [
                        'id' => $user->id,
                        'code' => $activation->code
                    ])
                ];

                Mail::send(
                    'mails.account_activation',
                    $data,
                    function ($message) use ($user) {
                        $message->to($user->email, $user->username)->subject('[EGLOBAL] Activar cuenta');
                    }
                );

                $expectedRoles = $input['roles'];

                if (!empty($expectedRoles)) {
                    $user->roles()->attach($expectedRoles);
                    \Log::info('Roles agregados a Usuario: ' . $user->username);
                }


                \Log::info("tipo de contrato creado");
                $respuesta['mensaje'] = 'Usuario crado con exito';
                $respuesta['tipo'] = 'success';
                $respuesta['data'] = $data;
                return $respuesta;
                
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Error al crear el usuario';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }

        }else{




            $generatePass = $password->generatePassword();
            if (\Sentinel::getUser()->isSuperUser()) {
                $ownerId = $request->get('owner_id', null);
                if (empty($ownerId)) $ownerId = null;
            } else {
                $ownerId = \Sentinel::getUSer()->owner_id;
            }
            $credentials = [
                'username' => $input['username'],
                'email' => $input['email'],
                'doc_number' => ($input['doc_number'] <> '') ? $input['doc_number'] : null,
                'description' => $input['description'],
                'password' => $generatePass,
                'owner_id' => $ownerId
            ];

            if (!empty($request->get('branch_id'))) {
                $permitedBranches = Branch::where('owner_id', '=', \Sentinel::getUser()->owner_id)->get();
                $permitedBranches = Branch::where('owner_id', '=', 1);
                $wishedBranches = Branch::find($request->get('branch_id'));

                if ($permitedBranches->search($wishedBranches) === false) {
                    return redirect()->back()->with('error', 'La sucursal no pertenece a su red');
                }
                $credentials['branch_id'] = $request->get('branch_id');
            }

            if ($user = \Sentinel::register($credentials)) {
                $expectedPermissions = Arr::pull($input, 'permissions');
                $expectedPermissions = empty($expectedPermissions) ? [] : $expectedPermissions;

                foreach ($expectedPermissions as $p => $v) {
                    if ($v['inherited'] === '0') {
                        $user->addPermission($p, isset($v['state']) ? filter_var(
                            $v['state'],
                            FILTER_VALIDATE_BOOLEAN
                        ) : false);
                    }
                }

                if (!$user->save()) {
                    \Log::error('Cant update Users Permissions.', $input);
                    return redirect()
                        ->back()
                        ->withInput()
                        ->with('error', 'Problemas al actualizar registro.');
                }
            }

            $activation = \Activation::create($user);

            $data = [
                'user' => $user,
                'password' => $generatePass,
                'link' => route('users.activate', [
                    'id' => $user->id,
                    'code' => $activation->code
                ])
            ];

            Mail::send(
                'mails.account_activation',
                $data,
                function ($message) use ($user) {
                    $message->to($user->email, $user->username)->subject('[EGLOBAL] Activar cuenta');
                }
            );

            $expectedRoles = $input['roles'];

            if (!empty($expectedRoles)) {
                $user->roles()->attach($expectedRoles);
                \Log::info('Roles agregados a Usuario: ' . $user->username);
            }

            return redirect()->route('users.index')
                ->with('success', 'Usuario creado exitosamente.');
        }
    }

    /**
     * Show an specified user
     * @param int $id
     * @return |Response
     */
    public function show($id)
    {
        if (!$this->user->hasAccess('users.view')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'action' => \Request::route()->getActionName()]
            );
            return redirect('/')->with('error', 'No posee permisos para realizar esta accion.');
        }

        if ($user = User::find($id)) {
            Log::debug("User {$id} find!");

            $activation_users = \DB::connection('eglobalt_auth')
                    ->table('activations')
                    ->selectRaw('user_id, code, completed, completed_at')
                    ->where('user_id', $user->id)
                    ->where('completed', true)
            ->first();

            if(is_null($activation_users)){
                $activate=false;
            }else{
                $activate=true;
            }
            
            return view('users.profile', compact('user', 'activate'));
        }

        Log::warning("User not found");
        return redirect()->back()->with('error', 'Usuario no encontrado');
    }

    public function edit($id)
    {
        if (!$this->user->hasAccess('users.add|edit')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'action' => \Request::route()->getActionName()]
            );
            return redirect('/')->with('error', 'No posee permisos para realizar esta accion.');
        }

        if ($user = User::with('roles')->find($id)) {

            $processedPermissions = $user->getProcessedPermissions()->all();

            $permissions = Permission::orderBy('permission')->get();

            foreach ($permissions as $permission) {
                if (array_key_exists($permission->permission, $processedPermissions)) {
                    $permission->has = $processedPermissions[$permission->permission]['state'];
                    $permission->inherited = $processedPermissions[$permission->permission]['inherited'];
                } else {
                    $permission->has = null;
                    $permission->inherited = null;
                }
            }

            $rolesList = $user->roles()->select(['id', 'name'])->get();
            $rolesIds = $rolesList->implode('id', ',');
            $rolesList = Role::all(['id', 'name']);
            $rolesJson = json_encode($rolesList);

            $owner = json_encode(Owner::all(['name', 'id']));
            //$data['ownersJson'] = json_encode($owner);
            $branches = json_encode(Branch::all(['description', 'id']));
            //$data['branchJson'] = json_encode($branches); 

            /**
             * TerminalInteractionAccessController ->
             * TerminalInteractionAccessService ->
             * terminal_interaction_access($user_id)
             */
            $tiac = new TerminalInteractionAccessController(); 
            $pos_boxes = $tiac->terminal_interaction_access($id);

            $data = [
                'user' => $user,
                'rolesJson' => $rolesJson,
                'rolesIds' => $rolesIds,
                'permissions' => $permissions,
                'ownersJson' => $owner,
                'branchJson' => $branches,
                'pos_boxes' => $pos_boxes
            ];

            return view('users.edit', $data);
        }

        return redirect()->back()->with('error', 'Usuario no encontrado.');
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @param UsersRequest $request
     * @return \Response
     */
    public function update(UsersRequest $request, $id)
    {
        if (!$this->user->hasAccess('users.add|edit')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'action' => \Request::route()->getActionName()]
            );
            return redirect('/')->with('error', 'No posee permisos para realizar esta accion.');
        }
        // Get User with Roles
        if ($user = User::with('roles')->find($id)) {
            // Get the form input
            $input = $request->all();

            // Get the form input expected permissions
            // Permissions Rules:
            //  if state is null and inherited is 0 -> revoked permission user specific
            //  if state is true and inherited is 0 -> grant permission user specific
            //  if state is true and inherited is 1 -> already granted permission role inherited
            //  if state is null and inherited is 1 -> already revoked permission role inherited
            //  if state is null and inherited is null -> permission set in neither role nor user
            $expectedPermissions = Arr::pull($input, 'permissions');
            $expectedPermissions = empty($expectedPermissions) ? [] : $expectedPermissions;

            foreach ($expectedPermissions as $p => $v) {
                if (!isset($v['inherited']) or $v['inherited'] === '0') {
                    $user->updatePermission($p, isset($v['state']) ? filter_var($v['state'], FILTER_VALIDATE_BOOLEAN) : false, true);
                }
            }

            if (!$user->save()) {
                \Log::error('Cant update Users Permissions.', $input);
                return redirect()
                    ->back()
                    ->withInput()
                    ->with('error', 'Problemas al actualizar registro.');
            }
            \Log::debug('Permissions Saved!. ', $input);
            $credentials = [
                'username' => $input['username'],
                'email' => $input['email'],
                'doc_number' => ($input['doc_number'] <> '') ? $input['doc_number'] : null,
                'description' => $input['description']
            ];

            if (\Sentinel::getUser()->isSuperUser()) {
                if (isset($input['owners']) and empty($input['owners'])) {
                    $credentials['owner_id'] = null;
                } else {
                    $credentials['owner_id'] = $request->get('owners');
                }
            }

            // Get array of the User's current Roles IDs
            $currentRoles = [];

            if (!$user->roles->isEmpty())
                $currentRoles = explode(',', $user->roles->implode('id', ','));

            // Get arry of the User's expected Roles IDs
            $expectedRoles = explode(',', $request->get('roles'));

            // Prepare array of Roles to detach from User
            if (!empty($currentRoles))
                $toDetachRoles = array_diff($currentRoles, $expectedRoles);

            if (!empty($toDetachRoles)) {
                $user->roles()->detach($toDetachRoles);
                Log::info('Roles eliminados de Usuario: ' . $user->username, $toDetachRoles);
            }

            // Prepare array of Roles to attach to User
            $toAttachRoles = array_diff($expectedRoles, $currentRoles);

            if (!empty($toAttachRoles)) {
                $user->roles()->attach($toAttachRoles);
                Log::info('Roles agregados a Usuario: ' . $user->username, $toAttachRoles);
            }


            // Check if the Branch selected is part of the User's Agent            
            if (!empty($request->get('branch'))) {
                if (!$user->isSuperUser()) {
                    $permitedBranches = Branch::where('owner_id', '=', $user->owner_id)->get();
                    $wishedBranch = Branch::find($request->get('branch'));
                    if ($permitedBranches->search($wishedBranch) === false)
                        //\Log::warning("La sucursal asignada no pertenece a su red");                        
                        return redirect()->back()->with('error_message', 'La sucursal no pertenece a su Agente.');
                }

                $credentials['branch_id'] = $input['branch'];
            }

            // Update User with credentials
            $user->update($credentials);
            \Log::info("User {$user->description} updated successfully");
            return redirect()
                ->route('users.index')
                ->with('message', 'Usuario actualizado.');
        }

        return redirect()
            ->route('users.index')
            ->with('error_message', 'Error al actualizar el Usuario.');
    }

    /**
     * Delete a given user
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        if (!$this->user->hasAccess('users.delete')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'action' => \Request::route()->getActionName()]
            );
            return redirect('/')->with('error', 'No posee permisos para realizar esta accion.');
        }

        $message = '';
        $error = '';

        if ($user = User::find($id)) {
            if (User::destroy($id) !== false) {
                \Log::info('User destroy.', $user->toArray());
                $message =  'Usuario eliminado correctamente';
                $error = false;
            } else {
                \Log::warning("Error while trying to destroy user: {$id}");
                $message =  'Error al intentar eliminar el usuario';
                $error = true;
            }
        } else {
            \Log::warning("User {$id} not found");
            $message =  'Usuario no encontrado';
            $error = true;
        }
        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }

    /**
     * Attempt User account activation
     *
     * @param $id
     * @param $code
     * @return \Response
     */
    public function activate($id, $code)
    {
        $user = \Sentinel::findUserById($id);

        if (!\Activation::complete($user, $code)) {
            return redirect('login')->withErrors('Codigo de activacion inválido o expirado');
        }

        /* When the user activate his account, we need to ask for a new personal password */
        $code = $user->getResetPasswordCode();

        return redirect()->route('reset.password.page', ['id' => $user->id, 'code' => $code])
            ->withSuccess('Cuenta de Usuario activada');
    }

    /**
     * @param int $id
     * @return |Response
     */
    public function banUser(Request $request)
    {
        $id = $request->_id;
        $params = $request->_params;
        if (!$this->user->hasAccess('users.force_logout')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'action' => \Request::route()->getActionName()]
            );
            return redirect('/')->with('error', 'No posee permisos para realizar esta accion.');
        }

        if ($usr = User::find($id)) {
            if ($params == 'ban') {
                $usr->ban();
                Log::info('User: ' . $usr->id . ' | Bloqueado: ' . ($usr->isBanned() ? 't' : 'f'));
                $action = 'bloqueado';
            } else {
                $usr->unBan();
                Log::info('User: ' . $usr->id . ' | Desbloqueado: ' . ($usr->isBanned() ? 't' : 'f'));
                $action = 'desbloqueado';
            }
            $message =  'Usuario ' . $action . ' correctamente';
            $error = false;
        } else {
            Log::error('No se encontro el usuario | Id: ' . $id);
            $message =  'Error al intentar bloquear/desbloquear el usuario';
            $error = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }

    public function resendActivation($id, Password $password)
    {

        if ($user = User::find($id)) {

            $activation_users = \DB::connection('eglobalt_auth')
                    ->table('activations')
                    ->selectRaw('id, user_id, code, completed, completed_at')
                    ->where('user_id', $user->id)
            ->first();
            $generatePass = $password->generatePassword();

            if(is_null($activation_users)){

                $activation = \Activation::create($user);
                $activation_code=$activation->code;
                
            }else{
                $activation_code = $activation_users->code;

                \DB::connection('eglobalt_auth')
                    ->table('activations')
                    ->where('id', $activation_users->id)
                    ->update([
                        'created_at' => Carbon::now(),
                        'updated_at' => Carbon::now(),
                ]);

            }

            $data = [
                'user' => $user,
                'id' => $user->id,
                'password'=> $generatePass,
                'link' => route('users.activate', [
                    'id' => $user->id,
                    'code' => $activation_code
                ])
            ];

            Mail::send(
                'mails.account_activation',
                $data,
                function ($message) use ($user) {
                    $message->to($user->email, $user->username)->subject('[EGLOBAL] Activar cuenta');
                }
            );

            return redirect()
                ->back()
                ->with('success', 'Se ha enviado un correo con instrucciones para continuar con la reactinvacion de la cuenta.');

        }

        return redirect()->back()->with('error', 'No existe el usuario.');

    }
}
