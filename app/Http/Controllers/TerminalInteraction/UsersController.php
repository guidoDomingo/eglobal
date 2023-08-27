<?php

namespace App\Http\Controllers\TerminalInteraction;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\TerminalInteraction\UsersRequest;
use Carbon\Carbon;

class UsersController extends Controller
{
    /**
     * Currently logged in User
     * @var |Cartalyst|Sentinel|Users|UserInterface
     */
    protected $user;

    /**
     * Sucursales
     */
    protected $branches;

    /**
     * Reglas
     */
    protected $roles;

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
        $username = $this->user->username;
        $action = \Request::route()->getActionName();

        if (!$this->user->hasAccess('terminal_interaction.manage.users')) {
            \Log::error("El usuario: $username no tiene permisos para la realizar la acción: $action");
            \Session::flash('error_message', 'No posee permisos para realizar esta acción.');
            return redirect('/');
        }

        $user = \Sentinel::getUser();
        $user_id = $user->id;

        $business_group_login = \DB::table('business_group_login as bgl')
            ->select(
                'u.branch_id',
                'bgl.business_group_id'
            )
            ->join('users as u', 'u.id', '=', 'bgl.user_id')
            ->where('u.id', $user_id)
            ->get();

        $branch_id = -1;
        $business_group_id = -1;

        if (count($business_group_login) > 0) {
            $branch_id = $business_group_login[0]->branch_id;
            $business_group_id = $business_group_login[0]->business_group_id;
        }

        $users = \DB::table('users as u')
            ->select(
                \DB::raw('u.*'),
                \DB::raw("
                    (select array_to_string(array_agg(r.name), ', ') 
                      from roles r 
                      join role_users ru on r.id = ru.role_id 
                      and u.id = ru.user_id) as role
                ")
            )
            ->join('business_group_login as bgl', 'u.id', '=', 'bgl.user_id')
            ->where('bgl.business_group_id', $business_group_id)
            ->where('u.branch_id', $branch_id)
            ->orderBy('u.id', 'asc')
            ->get();

        return view('terminal_interaction.users.index', compact('users'));
    }

    public function create()
    {
        $username = $this->user->username;
        $action = \Request::route()->getActionName();

        if (!$this->user->hasAccess('terminal_interaction.manage.users.create')) {
            \Log::error("El usuario: $username no tiene permisos para la realizar la acción: $action");
            \Session::flash('error_message', 'No posee permisos para realizar esta acción.');
            return redirect('/');
        }

        $message = 'Acción exitosa.';
        $message_type = 'message';

        $user_id = $this->user->id;

        $business_group_login = \DB::table('business_group_login as bgl')
            ->select(
                'bgl.business_group_id'
            )
            ->join('users as u', 'u.id', '=', 'bgl.user_id')
            ->where('u.id', $user_id)
            ->get();

        if (count($business_group_login) > 0) {

            $business_group_id = $business_group_login[0]->business_group_id;

            $this->branches = \DB::table('branches')
                ->select(
                    'id',
                    'description'
                )
                ->where('group_id', $business_group_id)
                ->get();

            if (count($this->branches) > 0) {

                $this->branches = array_map(function ($value) {
                    return (array) $value;
                }, $this->branches);

                $this->roles = \DB::table('roles')
                    ->select(
                        'id',
                        'name'
                    )
                    ->whereRaw("slug ilike '%terminal_interaction%'")
                    ->get();

                if (count($this->roles) > 0) {
                    $this->roles = array_map(function ($value) {
                        return (array) $value;
                    }, $this->roles);
                } else {
                    $message = 'No hay roles disponibles.';
                    $message_type = 'error_message';
                }
            } else {
                $message = 'El grupo del usuario no tiene sucursales relacionadas.';
                $message_type = 'error_message';
            }
        } else {
            $message = 'El usuario no tiene grupo de negocio asignado.';
            $message_type = 'error_message';
        }

        if ($message_type == 'message') {

            $inputs = [
                'description' => '',
                'username' => '',
                'password' => '',
                'password_2' => '',
                'doc_number' => '',
                'email' => '',
                'branch_id' => '',
                'role_id' => '',
            ];

            $data = [
                'branches' => json_encode($this->branches),
                'roles' => json_encode($this->roles),
                'inputs' => json_encode($inputs)
            ];

            //\Log::info("Data:");
            //\Log::info($data);

            return view('terminal_interaction.users.create', compact('data'));
        } else {
            return redirect('/')->with('error', $message);
        }
    }

    //UsersRequest $request

    public function store(UsersRequest $request)
    {
        $username = $this->user->username;
        $action = \Request::route()->getActionName();

        if (!$this->user->hasAccess('terminal_interaction.manage.users.store')) {
            \Log::error("El usuario: $username no tiene permisos para la realizar la acción: $action");
            \Session::flash('error_message', 'No posee permisos para realizar esta acción.');
            return redirect('/');
        }

        $input = $request->all();

        $message = 'Acción exitosa.';
        $message_type = 'message';

        //----------------------------------------------------------------------

        $user = \DB::table('users')
            ->where('username', $request['username'])
            ->get();

        if (count($user) > 0) {
            $message = 'El nombre del usuario ya existe.';
            $message_type = 'error_message';
        }

        $user = \DB::table('users')
            ->where('doc_number', $request['doc_number'])
            ->get();

        if (count($user) > 0) {
            $message = 'El número de documento ya existe.';
            $message_type = 'error_message';
        }

        //----------------------------------------------------------------------

        if ($message_type == 'message') {
            $branch = \DB::table('branches')
                ->select(
                    'owner_id',
                    'group_id'
                )
                ->where('id', $request['branch_id'])
                ->get();

            $owner_id = null;
            $group_id = null;

            if (count($branch) > 0) {
                $owner_id = $branch[0]->owner_id;
                $group_id = $branch[0]->group_id;
            }

            try {
                //\DB::beginTransaction();

                /*$insert_get_user_id = \DB::table('users')->insertGetId([
                    'username' => \DB::raw("select max(id) + 1 from users"),
                    'username' => $request['username'],
                    'email' => $request['email'],
                    'password' => $request['password'],
                    'description' => $request['description'],
                    'permissions' => null,
                    'owner_id' => $owner_id,
                    'branch_id' => $request['branch_id'],
                    'created_at' => Carbon::now(),
                    'doc_number' => $request['doc_number']
                ]);*/

                $credentials = [
                    'username' => $request['username'],
                    'email' => $request['email'],
                    'password' => $request['password'],
                    'description' => $request['description'],
                    'owner_id' => $owner_id,
                    'branch_id' => $request['branch_id'],
                    'created_at' => Carbon::now(),
                    'doc_number' => $request['doc_number']
                ];

                if ($user = \Sentinel::register($credentials)) {
                    $description = $request['description'] . ' - ' . $request['username'];

                    \DB::table('business_group_login')->insert([
                        'description' => $description,
                        'business_group_id' => $group_id,
                        'user_id' => $user->id,
                        'created_at' => Carbon::now()
                    ]);

                    \DB::table('role_users')->insert([
                        'user_id' => $user->id,
                        'role_id' => $request['role_id']
                    ]);
                }

                //\DB::commit();
            } catch (\Exception $e) {
                //\DB::rollback();
                $message = 'Error al crear usuario.';
            }

            return redirect()->route('terminal_interaction_users')->with('success', $message);
        } else {

            $user_id = $this->user->id;

            $business_group_login = \DB::table('business_group_login as bgl')
                ->select(
                    'bgl.business_group_id'
                )
                ->join('users as u', 'u.id', '=', 'bgl.user_id')
                ->where('u.id', $user_id)
                ->get();

            if (count($business_group_login) > 0) {

                $business_group_id = $business_group_login[0]->business_group_id;

                $this->branches = \DB::table('branches')
                    ->select(
                        'id',
                        'description'
                    )
                    ->where('group_id', $business_group_id)
                    ->get();

                if (count($this->branches) > 0) {

                    $this->branches = array_map(function ($value) {
                        return (array) $value;
                    }, $this->branches);

                    $this->roles = \DB::table('roles')
                        ->select(
                            'id',
                            'name'
                        )
                        ->whereRaw("slug ilike '%terminal_interaction%'")
                        ->get();

                    if (count($this->roles) > 0) {
                        $this->roles = array_map(function ($value) {
                            return (array) $value;
                        }, $this->roles);
                    }
                }
            }

            $inputs = [
                'description' => $request['description'],
                'username' => $request['username'],
                'password' => $request['password'],
                'password_2' => $request['password_2'],
                'doc_number' => $request['doc_number'],
                'email' => $request['email'],
                'branch_id' => $request['branch_id'],
                'role_id' => $request['role_id']
            ];

            $data = [
                'branches' => json_encode($this->branches),
                'roles' => json_encode($this->roles),
                'inputs' => json_encode($inputs)
            ];

            \Session::flash($message_type, $message);
            return view('terminal_interaction.users.create', compact('data'));
        }
    }
}
