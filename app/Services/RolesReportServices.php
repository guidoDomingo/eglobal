<?php

/**
 * User: avisconte
 * Date: 16/02/2023
 * Time: 16:20
 */

namespace App\Services;

use Excel;

class RolesReportServices
{

    /**
     * Inicializar valores para usar en todas las funciones
     */
    public function __construct()
    {

        ini_set('max_execution_time', 0);
        ini_set('client_max_body_size', '20M');
        ini_set('max_input_vars', 10000);
        ini_set('upload_max_filesize', '20M');
        ini_set('post_max_size', '20M');
        ini_set('memory_limit', '-1');
        set_time_limit(3600);

        $this->connection = \DB::connection('eglobalt_pro');
        $this->connection_auth = \DB::connection('eglobalt_auth');
        $this->user = \Sentinel::getUser();
    }

    /**
     * Lista de Roles con sus permisos
     */
    public function index_report($request)
    {

        if (!$this->user->hasAccess('roles')) {
            \Log::error(
                'No tienes permiso para acceder a esta pantalla',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );

            $data = [
                'mode' => 'message',
                'type' => 'error',
                'title' => 'Sin permiso',
                'explanation' => 'El usuario necesita tener el permiso asignado para acceder a esta pantalla.'
            ];

            return view('messages.index', compact('data'));
        }

        $records = [];
        $roles = [];
        $permissions = [];
        $users = [];

        try {

            $connection = $this->connection_auth;

            if (isset($request['button_name'])) {

                if ($request['button_name'] == 'search') {

                    /**
                     * Trae el detalle de pago ordenado por proveedor, terminal, servicio y comisión 
                     */

                    $records = $connection
                        ->table('users as u')
                        ->select(
                            'u.id as user_id',
                            \DB::raw("u.id || '# ' || (u.description || ' - ' || u.username) as description"),
                            'u.permissions',
                            \DB::raw("
                                coalesce(
                                    json_agg(
                                        json_build_object(
                                            'id', r.id,
                                            'slug', r.slug,
                                            'name', r.name,
                                            'description', r.description,
                                            'permissions', r.permissions
                                        )
                                    ), '[]'::json
                                ) as roles_per_user
                            ")
                        )
                        ->join('role_users as ru', 'u.id', '=', 'ru.user_id')
                        ->join('roles as r', 'r.id', '=', 'ru.role_id');

                    if (isset($request['rol_id'])) {
                        if ($request['rol_id'] !== '' and $request['rol_id'] !== 'Todos') {
                            $records = $records->where('r.id', $request['rol_id']);
                        }
                    }

                    if (isset($request['permission_id'])) {
                        if ($request['permission_id'] !== '' and $request['permission_id'] !== 'Todos') {
                            $permission_id = $request['permission_id'];
                            $records = $records->whereRaw("r.permissions ilike '%$permission_id%'");
                        }
                    }

                    if (isset($request['user_id'])) {
                        if ($request['user_id'] !== '' and $request['user_id'] !== 'Todos') {
                            $records = $records->where('u.id', $request['user_id']);
                        }
                    }

                    $records = $records
                        ->whereRaw('u.deleted_at is null')
                        ->groupBy(
                            \DB::raw("
                                u.id
                            ")
                        )
                        ->orderBy('u.id');

                    //\Log::info($records->toSql());

                    $records = $records->get();

                    $records = json_decode(json_encode($records), true);

                    //------------------------------------------------------------------------------------------------

                    //Traemos de una vez todos los permisos para comparar en el ciclo
                    $permission_aux = $connection
                        ->table('permissions as p')
                        ->select(
                            'p.id',
                            'p.permission',
                            'p.description'
                        )
                        ->get();

                    //\Log::info('permission_details query:' . $permission_aux->toSql());

                    $permission_aux = json_decode(json_encode($permission_aux), true);

                    //------------------------------------------------------------------------------------------------

                    //Traemos de una vez todos los roles para comparar en el ciclo
                    $roles_aux = $connection
                        ->table('roles as r')
                        ->select(
                            'r.id',
                            'r.slug',
                            'r.name',
                            'r.description',
                            'r.permissions'
                        )
                        ->get();

                    $roles_aux = json_decode(json_encode($roles_aux), true);

                    for($i = 0; $i < count($roles_aux); $i++) {
                        $roles_aux[$i]['permissions'] = json_decode($roles_aux[$i]['permissions'], true);

                        $item_permissions_aux = [];

                        if ($roles_aux[$i]['permissions'] !== null) {
                            foreach ($roles_aux[$i]['permissions'] as $permission => $status) {

                                foreach ($permission_aux as $permission_aux_item) {
    
                                    if ($permission == $permission_aux_item['permission']) {
                                        $item_aux = [
                                            'id' => $permission_aux_item['id'],
                                            'permission' => $permission_aux_item['permission'],
                                            'description' => $permission_aux_item['description'],
                                            'status' => $status
                                        ];
    
                                        array_push($item_permissions_aux, $item_aux);
                                        break;
                                    }
                                }
                            }
                        }

                        $roles_aux[$i]['permissions'] = $item_permissions_aux;
                    }

                    //------------------------------------------------------------------------------------------------

                    for($i = 0; $i < count($records); $i++) {
                        
                        $records[$i]['roles_per_user'] = json_decode($records[$i]['roles_per_user'], true);
                        $roles_per_user_aux = [];

                        for($j = 0; $j < count($records[$i]['roles_per_user']); $j++) {

                            $slug = $records[$i]['roles_per_user'][$j]['slug'];

                            for($k = 0; $k < count($roles_aux); $k++) {

                                if ($slug == $roles_aux[$k]['slug']) {

                                    $records[$i]['roles_per_user'][$j] = $roles_aux[$k];
                                    break;

                                }
                            }
                        }


                        $item_permissions = json_decode($records[$i]['permissions'], true);
                        $item_permissions_aux = [];

                        if ($item_permissions !== null) {

                            foreach ($item_permissions as $permission => $status) {

                                foreach ($permission_aux as $permission_aux_item) {

                                    if ($permission == $permission_aux_item['permission']) {
                                        $item_aux = [
                                            'id' => $permission_aux_item['id'],
                                            'permission' => $permission_aux_item['permission'],
                                            'description' => $permission_aux_item['description'],
                                            'status' => $status
                                        ];

                                        array_push($item_permissions_aux, $item_aux);
                                        break;
                                    }
                                }
                            }
                        }

                        $records[$i]['permissions'] = $item_permissions_aux;

                    }

                    //\Log::info('records:', [$records]);
                    //die();
                }
            }

            $roles = $connection
                ->table('roles')
                ->select(
                    'id',
                    \DB::raw("(name || ' (' || slug || ')') as description")
                )
                ->get();

            $permissions = $connection
                ->table('permissions')
                ->select(
                    'permission as id', // Para que busque por el código del permiso
                    \DB::raw("(description || ' (' || permission || ')') as description")
                )
                ->get();

            $users = $connection
                ->table('users')
                ->select(
                    'id',
                    \DB::raw("(description || ' - ' || username) as description")
                )
                ->whereRaw('deleted_at is null')
                ->get();
        } catch (\Exception $e) {
            $error_detail = [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];

            \Log::error("Error, Detalles: " . json_encode($error_detail));
        }

        $data = [
            'lists' => [
                'records' => $records,
                'json' => json_encode($records, JSON_UNESCAPED_UNICODE),
                'roles' => json_encode($roles, JSON_UNESCAPED_UNICODE),
                'permissions' => json_encode($permissions, JSON_UNESCAPED_UNICODE),
                'users' => json_encode($users, JSON_UNESCAPED_UNICODE)
            ],
            'inputs' => [
                'rol_id' => isset($request['rol_id']) ? $request['rol_id'] : 'Todos',
                'permission_id' => isset($request['permission_id']) ? $request['permission_id'] : 'Todos',
                'user_id' => isset($request['user_id']) ? $request['user_id'] : 'Todos',
            ]
        ];

        //\Log::info('DATA:', $data);

        return view('roles.index_report', compact('data'));
    }

    /**
     * Traer todos los Permisos de un Rol
     */
    public function get_roles_permissions($request)
    {
        if (!$this->user->hasAccess('roles')) {
            \Log::error(
                'No tienes permiso para acceder a esta pantalla',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );

            $data = [
                'mode' => 'message',
                'type' => 'error',
                'title' => 'Sin permiso',
                'explanation' => 'El usuario necesita tener el permiso asignado para acceder a esta pantalla.'
            ];

            return view('messages.index', compact('data'));
        }

        $connection = $this->connection_auth;

        $rol_id = $request['rol_id'];

        $permissions = $connection
            ->table('roles')
            ->select(
                'permissions'
            )
            ->where('id', $rol_id)
            ->get();

        $permissions = json_decode(json_encode($permissions), true);

        \Log::info('permissions', [$permissions]);

        $permissions_aux = [];

        foreach ($permissions as $item) {

            $item = json_decode($item['permissions'], true);

            foreach ($item as $key => $value) {

                $item = [
                    'id' => $key,
                    'description' => $key
                ];

                array_push($permissions_aux, $item);
            }
        }

        \Log::info('permissions_aux', [$permissions_aux]);

        return $permissions_aux;
    }
}
