<?php

/**
 * User: avisconte
 * Date: 11/04/2022
 * Time: 09:00 am
 */

namespace App\Services\AtmsPerUsers;

use App\Exports\ExcelExport;
use Excel;
use Carbon\Carbon;
use App\Services\Password;
use HttpClient;
use App\Models\User;

class AtmsPerUsersManagementServices
{

    public function __construct()
    {
        $this->user = \Sentinel::getUser();

        if ($this->user != null) {
            $this->user_supervisor_id = $this->user->id;
            $this->connection = \DB::connection('eglobalt_pro');
            $this->connection_auth = \DB::connection('eglobalt_auth');
            $this->password_service = new Password();
    
            //------------------------------------------------------------------------
    
            $this->url = 'http://eglobaltws.local/';
            $this->env = env('APP_ENV');
    
            if ($this->env == 'local') {
                $this->url = 'http://eglobaltws.local/';
                $this->app_key = 'pzRFvqFSTxGaTvq8NR5uf00CcCFNJ5wHWfMT72Yd';
                $this->public_key = 'CEaqVRie6WcBN7hJpSbgvNoNbVJFy8PppvguawuK';
            } else if ($this->env == 'remote') {
                $this->url = 'https://api.eglobalt.com.py';
                $this->app_key = 'srteFHCgHiWBzdzMOM59rUYLhS9MozrBRSjWBTAI';
                $this->public_key = 'hHU8c6EWijaZkAARKQOkNvYc1kmRXX9liTSjdcLn';
            }
        } else {
            return back()->withInput();
        }
    }


    /**
     * Función inicial
     */
    public function index($request)
    {
        ini_set('max_execution_time', 0);
        ini_set('client_max_body_size', '20M');
        ini_set('max_input_vars', 10000);
        ini_set('upload_max_filesize', '20M');
        ini_set('post_max_size', '20M');
        ini_set('memory_limit', '-1');
        set_time_limit(3600);

        //------------------------------------------------------------------------

        $connection = $this->connection;
        $user_supervisor_id = $this->user_supervisor_id;
        $super_user = false;
        $business_group_id = null;

        $records = [];
        $supervisors = [];
        $users = [];
        $atms = [];
        $get_info = true;

        //apu.user_supervisor_id = 186

        //------------------------------------------------------------------------

        if ($this->user->hasRole('superuser')) {
            $super_user = true;
            //$user_supervisor_id = null; // El usuario super admin se encarga de elegir el supervisor
        }

        if (!$this->user->hasAccess('atms_per_users_management')) {
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

        //------------------------------------------------------------------------

        try {

            $button_name = '';

            if (isset($request['button_name'])) {
                $button_name = $request['button_name'];
            }

            if ($button_name == '' or $button_name == 'search') {

                $records = $connection
                    ->table('users as u')
                    ->select(
                        'u.id',
                        'u.description',
                        'u.doc_number',
                        'u.username',
                        'u.email',
                        'u.phone_number',
                        \DB::raw("(u.description || ' - ' || u.username) as user"),
                        \DB::raw("to_char(u.created_at, 'DD/MM/YYYY - HH24:MI:SS') as created_at"),
                        \DB::raw("to_char(u.updated_at, 'DD/MM/YYYY - HH24:MI:SS') as updated_at"),
              

                        \DB::raw("COUNT(apu.id) as atms_per_user_count"),
                        \DB::raw("
                            coalesce(
                                json_agg(
                                    json_build_object(
                                        'atm_id', a.id, 
                                        'description', a.name, 
                                        'status', apu.status,
                                        'status_view', (case when apu.status then 'Activo' else 'Inactivo' end),
                                        'created_at', to_char(apu.created_at, 'DD/MM/YYYY - HH24:MI:SS')
                                )), '[]'::json
                            ) as atms_per_user
                        "),

                        'u2.id as user_supervisor_id',
                        'u2.description as user_supervisor_description',
                        'bg.description as user_supervisor_group'
                    )
                    ->leftjoin('atms_per_users as apu', 'u.id', '=', 'apu.user_id')
                    ->leftjoin('atms as a', 'a.id', '=', 'apu.atm_id')
                    ->leftjoin('users as u2', 'u2.id', '=', 'apu.user_supervisor_id')
                    ->leftjoin('branches as b', 'u2.id', '=', 'b.user_id')
                    ->leftjoin('business_groups as bg', 'bg.id', '=', 'b.group_id');

                /**
                 * Filtros para la búsqueda
                 */

             

                if (isset($request['user_id'])) {
                    if ($request['user_id'] !== '' and $request['user_id'] !== 'Todos') {
                        $records = $records->where('u.id', $request['user_id']);
                    }
                }

                if ($super_user == false) {
                    $records = $records->where('u2.id', $user_supervisor_id);
                }

               

                $records = $records
                    ->groupBy(
                        \DB::raw("
                            u.id,
                            u.description,
                            u.doc_number,
                            u.username,
                            u.email,
                            u.phone_number,
                            u.created_at,
                            u.updated_at,
                            u2.id,
                            u2.description,
                            bg.description
                        ")
                    )
                    ->orderBy('atms_per_user_count', 'DESC');

               

                //\Log::info('QUERY:' . $records->toSql());

                $records = $records->get();
               

                $records = json_decode(json_encode($records), true);

                if (count($records) > 0) {
                } else {
                    /*$data = [
                        'mode' => 'message',
                        'type' => 'error',
                        'title' => 'Consulta sin registros',
                        'explanation' => 'La consulta no retornó ningún registro.'
                    ];

                    return view('messages.index', compact('data'));*/
                }
            } else if ($button_name == 'generate_x') {

                $records = json_decode($request['json'], true);
                $records_aux = [];

                $style_array = [
                    'font'  => [
                        'bold'  => true,
                        'color' => ['rgb' => '367fa9'],
                        'size'  => 12,
                        'name'  => 'Verdana'
                    ]
                ];

                $filename = 'report_' . time();

                //\Log::info('records:', [$records]);

                for ($i = 0; $i < count($records); $i++) {

                    $item = $records[$i];

                    $id = $item['id'];
                    $description = $item['description'];
                    $doc_number = $item['doc_number'];
                    $username = $item['username'];
                    $email = $item['email'];
                    $user = $item['user'];
                    $created_at = $item['created_at'];
                    $updated_at = $item['updated_at'];
                    $atms_per_user = json_decode($item['atms_per_user']);
                    $atms_per_user_aux_array = [];
                    $atms_per_user_aux_string = '';

                    for ($j = 0; $j < count($atms_per_user); $j++) {
                        $sub_item = $atms_per_user[$j];
                        $description = $sub_item->description;
                        array_push($atms_per_user_aux_array, $description);
                    }

                    $atms_per_user_aux_string = join(', ', $atms_per_user_aux_array);

                    $row = [
                        'id' => $id,
                        'description' => $description,
                        'doc_number' => $doc_number,
                        'username' => $username,
                        'email' => $email,
                        'created_at' => $created_at,
                        'updated_at' => $updated_at,
                        'atms_per_user' => $atms_per_user_aux_string
                    ];

                    array_push($records_aux, $row);
                }

                $columnas = array(
                    'ID',
                    'Nombre y Apellido',
                    'Documento',
                    'Usuario',
                    'Correo',
                    'Fecha y Hora de Creación',
                    'Fecha y Hora de Última actualización',
                    'Terminales'
                );

                $excel = new ExcelExport($records_aux,$columnas);
                return Excel::download($excel, $filename . '.xls')->send();

                // Excel::create($filename, function ($excel) use ($records_aux, $style_array) {
                //     $excel->sheet('Usuarios', function ($sheet) use ($records_aux, $style_array) {
                //         $sheet->rows($records_aux, false);

                //         $sheet->prependRow(array(
                //             'ID',
                //             'Nombre y Apellido',
                //             'Documento',
                //             'Usuario',
                //             'Correo',
                //             'Fecha y Hora de Creación',
                //             'Fecha y Hora de Última actualización',
                //             'Terminales'
                //         ));

                //         $sheet->getStyle('A1:I1')->applyFromArray($style_array); //Aplicar los estilos del array
                //         $sheet->setHeight(1, 25); //Aplicar tamaño de la primera fila
                //     });
                // })->export('xlsx');

                $get_info = false;
            }

            //Traer solo cuando hay búsqueda no cuando genera el excel.

            if ($get_info) {

                /**
                 * Trae las sucursales
                 */
                $users = $connection
                    ->table('users as u')
                    ->select(
                        'u.id',
                        \DB::raw("replace((u.id || '# ' || u.description || ' - ' || u.username), '''', '') as description")
                    )
                    ->join('atms_per_users as apu', 'u.id', '=', 'apu.user_id');

                if ($super_user == false) {
                    $users = $users->where('u.created_by', $user_supervisor_id);
                }

                $users = $users
                    ->groupBy(
                        \DB::raw("
                            u.id,
                            u.description,
                            u.username
                        ")
                    )
                    ->get();

                //------------------------------------------------------------------------

                $parameters = [
                    'user_id' => -1,
                    'user_supervisor_id' => -1
                ];

                $atms = $this->get_atms_per_user($parameters)['response'];

                //------------------------------------------------------------------------

                /*
                    select 
                        (bg.id || '# ' || bg.description || ' / ' || u.id || '# '|| u.description) as description 
                    from business_groups as bg 
                    join branches as b on bg.id = b.group_id
                    join users as u on u.id = b.user_id  
                    group by bg.id, u.id, u.description
                    order by bg.description asc, u.description asc
                */

                /**
                 * Trae los supervisores
                 */
                $supervisors = $connection
                    ->table('atms_per_users as apu')
                    ->select(
                        'apu.user_supervisor_id as id',
                        'u.description'
                    )
                    ->join('users as u', 'u.id', '=', 'apu.user_supervisor_id');

                if ($super_user == false) {
                    $supervisors = $supervisors->where('u.id', $user_supervisor_id);
                }

                $supervisors = $supervisors
                    ->groupBy(
                        \DB::raw("
                            apu.user_supervisor_id,
                            u.description
                        ")
                    )
                    ->orderBy('u.description', 'ASC')
                    ->get();
            }
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
                'users' => json_encode($users, JSON_UNESCAPED_UNICODE),
                'atms' => json_encode($atms, JSON_UNESCAPED_UNICODE),
                'supervisors' => json_encode($supervisors, JSON_UNESCAPED_UNICODE)
            ],
            'inputs' => [
                'user_supervisor_id' => $user_supervisor_id,
                'user_id' => isset($request['user_id']) ? $request['user_id'] : 'Todos',
                'atm_id' => isset($request['atm_id']) ? $request['atm_id'] : 'Todos',
            ]
        ];

        return view('atms_per_users_management.index', compact('data'));
    }

    /**
     * Función inicial
     */
    public function management($request)
    {
        $response = [
            'error' => false,
            'message' => '',
        ];

        try {

            $connection = $this->connection;
            $connection_auth = $this->connection_auth;
            //$user_supervisor_id = $this->user_supervisor_id;

            $context = $request['context'];
            $user_id = $request['user_id'];
            $user_supervisor_id = $request['user_supervisor_id'];
            $description = $request['description'];
            $doc_number = $request['doc_number'];
            $username = $request['username'];
            $email = $request['email'];
            $phone_number = $request['phone_number'];
            $email_or_phone_number = $request['email_or_phone_number'];
            $atms_selected = $request['atms_selected'];

            $user_created = false;
            $user_updated = false;
            $send = false;

            //\Log::info("email_or_phone_number: $email_or_phone_number");
            //\Log::info("email: $email");
            \Log::info("phone_number: $phone_number");

            if ($context == 'add') {

                //.created_by: para saber cuantos usuarios creó

                $users_created = $connection_auth
                    ->table('users as u')
                    ->select(
                        'u.id as user_id',
                        'u.username as description'
                    )
                    ->where('u.created_by', $user_supervisor_id)
                    ->get();

                $users_created_count = count($users_created);

                \Log::info("CANTIDAD DE USUARIOS CREADOS: $users_created_count");

                if ($users_created_count <= 9) {
                } else {
                    $response['message'] = 'Ya no se pueden agregar usuarios limite de 10 usuarios alcanzado.';
                }

                /**
                 * Validar si el usuario ya existe
                 */

                $validate = $connection_auth
                    ->table('users as u')
                    ->select(
                        'u.id'
                    )
                    ->where('u.username', $username)
                    ->get();

                if (count($validate) <= 0) {
                } else {
                    $response['message'] = 'El nombre del usuario ya existe.';
                }

                /**
                 * VALIDACIÓN SI UNA CAJA ESTÁ ABIERTA NO LE VA DEJAR ASIGNAR LA TERMINAL
                 */
                for ($i = 0; $i < count($atms_selected); $i++) {
                    $item = $atms_selected[$i];
                    $atm_id = $item['atm_id'];
                    $status = $item['status'];

                    if ($status == 'true') {
                        $pos_box_movement = $connection
                            ->table('pos_box_movement as pbm')
                            ->select(
                                'pbm.movement_type_id',
                                'a.id as atm_id',
                                'a.name as atm_description',
                                'u.id as user_id',
                                'u.description as user_description'
                            )
                            ->join('pos_box as pb', 'pb.id', '=', 'pbm.pos_box_id')
                            ->join('atms as a', 'a.id', '=', 'pb.atm_id')
                            ->join('terminal_interaction_login as til', 'til.id', '=', 'pbm.terminal_interaction_login_id')
                            ->join('users as u', 'u.id', '=', 'til.user_id')
                            ->where('a.id', $atm_id)
                            ->orderBy('pbm.created_at', 'DESC')
                            ->take(1)
                            ->get();

                        $movement_type_id_aux = null;

                        if (count($pos_box_movement) > 0) {
                            $movement_type_id_aux = $pos_box_movement[0]->movement_type_id;
                            $user_id_aux = $pos_box_movement[0]->user_id;
                            $atm_description = $pos_box_movement[0]->atm_description;
                            $user_description = $pos_box_movement[0]->user_description;

                            if ($movement_type_id_aux == 9) {
                                $response['message'] = "No se puede asignar la terminal: $atm_description\n\n.Falta cerrar la caja del terminal.\n\nEl encargado de hacer el cierre es: $user_description";
                                break;
                            }
                        }
                    }
                }

                if ($response['message'] == '') {
                    /**
                     * Creación del usuario
                     */
                    $password = new Password();
                    $generate_password = $password->generatePassword();

                    $credentials = [
                        'description' => $description,
                        'doc_number' => $doc_number,
                        'username' => $username,
                        'email' => $email,
                        'phone_number' => $phone_number,
                        'password' => $generate_password,
                        'owner_id' => null,
                        'created_by' => $user_supervisor_id,
                    ];

                    $user = \Sentinel::register($credentials);
                    $user_id = $user->id;

                    \Log::info("Usuario $description creado.");

                    /**
                     * Relacionar role de Mini Terminal al usuario.
                     */
                    $role_users_values = [
                        'user_id' => $user_id,
                        'role_id' => 22 // Usuario de Mini Terminal
                    ];

                    $connection_auth->table('role_users')
                        ->insert($role_users_values);

                    \Log::info("Rol de $description creado.");

                    /**
                     * Crear registro de activación
                     */

                    $activation = \Activation::create($user);

                    \Log::info("Activación de $description creada.");

                    /**
                     * Relacionar supervisor y terminales al usuario.
                     */

                    $atms_per_users_values = [];

                    for ($i = 0; $i < count($atms_selected); $i++) {
                        $item = $atms_selected[$i];
                        $atm_id = $item['atm_id'];
                        $status = $item['status'];

                        if ($status == 'true') {

                            $values = [
                                'user_supervisor_id' => $user_supervisor_id,
                                'user_id' => $user_id,
                                'atm_id' => $atm_id,
                                'status' => true,
                                'created_at' => Carbon::now(),
                                'created_by' => $user_supervisor_id,
                            ];

                            array_push($atms_per_users_values, $values);
                        }
                    }

                    $connection->table('atms_per_users')
                        ->insert($atms_per_users_values);

                    \Log::info("Terminales de $description creada.");

                    $user_created = true;


                    $link = route('users.activate', [
                        'id' => $user->id,
                        'code' => $activation->code
                    ]);

                    /**
                     * Enviar código al correo o al teléfono
                     */

                    if ($email_or_phone_number == 'email') {
                        /**
                         * Enviar datos al correo especificado
                         */

                        $data = [
                            'user' => $user,
                            'id' => $user->id,
                            'password' => $generate_password,
                            'link' => $link
                        ];

                        \Mail::send(
                            'mails.account_activation',
                            $data,
                            function ($message) use ($user, $description) {
                                $message->to($user->email, $user->username)->subject("[EGLOBAL] Activar cuenta del usuario: $description");
                            }
                        );

                        \Log::info("Mail envíado a $description");
                    } else if ($email_or_phone_number == 'phone_number') {

                        /**
                         * Enviar datos al teléfono especificado
                         */

                        $url = $this->url . 'send_sms';
                        $app_key = $this->app_key;
                        $public_key = $this->public_key;

                        $json = [
                            'phone_number' => $phone_number,
                            'text' => $link
                        ];

                        $date = Carbon::now();
                        $params = array($date->format('D, d M Y H:i:s T00'), 'POST', $url, json_encode($json));
                        $params_hash = hash_hmac("sha1", implode("\n", $params), $app_key);

                        $petition = HttpClient::post(
                            $url,
                            [
                                'auth' => [$public_key, $params_hash],
                                'headers' => [
                                    'Timestamp' => $date->format('D, d M Y H:i:s T00')
                                ],
                                'json' => $json,
                                'connect_timeout' => 180,
                                'verify' => false
                            ]
                        );

                        $response['response'] = json_decode($petition->getBody()->getContents());

                        /*$error = $response['response']['error'];

                            if ($error) {
                                \Log::error("Código de verificación NO envíado a $phone_number");
                            } else {
                                \Log::info("Código de verificación envíado a $phone_number");
                            }*/

                        \Log::debug("Respuesta del servicio que envía mensajes:", [$response['response']]);
                    }

                    $send = true;
                }
            } else if ($context == 'edit') {

                \Log::debug("USER_ID A EDITAR: $user_id");

                /**
                 * Validar si el usuario ya existe
                 */

                $validate = $connection_auth
                    ->table('users as u')
                    ->select(
                        'u.id'
                    )
                    ->where('u.username', $username)
                    ->whereRaw("u.id != $user_id")
                    ->get();

                if (count($validate) <= 0) {
                } else {
                    $response['message'] = 'El nombre del usuario ya existe.';
                }

                /**
                 * VALIDACIÓN SI UNA CAJA ESTÁ ABIERTA NO LE VA DEJAR ASIGNAR LA TERMINAL
                 */
                for ($i = 0; $i < count($atms_selected); $i++) {
                    $item = $atms_selected[$i];
                    $atm_id = $item['atm_id'];
                    $status = $item['status'];

                    if ($status == 'true') {
                        $pos_box_movement = $connection
                            ->table('pos_box_movement as pbm')
                            ->select(
                                'pbm.movement_type_id',
                                'a.id as atm_id',
                                'a.name as atm_description', 
                                'u.id as user_id',
                                'u.description as user_description'
                            )
                            ->join('pos_box as pb', 'pb.id', '=', 'pbm.pos_box_id')
                            ->join('atms as a', 'a.id', '=', 'pb.atm_id')
                            ->join('terminal_interaction_login as til', 'til.id', '=', 'pbm.terminal_interaction_login_id')
                            ->join('users as u', 'u.id', '=', 'til.user_id')
                            ->where('a.id', $atm_id)
                            ->orderBy('pbm.created_at', 'DESC')
                            ->take(1)
                            ->get();

                        $movement_type_id_aux = null;

                        if (count($pos_box_movement) > 0) {
                            $movement_type_id_aux = $pos_box_movement[0]->movement_type_id;
                            $user_id_aux = $pos_box_movement[0]->user_id;
                            $atm_description = $pos_box_movement[0]->atm_description;
                            $user_description = $pos_box_movement[0]->user_description;

                            /*\Log::info("$movement_type_id_aux == 9 and $user_id !== $user_id_aux");

                            $aux = [
                                'user_id' => $user_id,
                                'user_id_aux' => $user_id_aux
                            ];

                            \Log::info($aux);*/

                            /**
                             * El último tipo de movimiento fué apertura y el usuario que tiene que cerrar la caja es distinto al que estamos asignando.
                             */
                            if ($movement_type_id_aux == 9 and intval($user_id) !== $user_id_aux) {

                                $response['message'] = "No se puede asignar la terminal: $atm_description\n\n.Falta cerrar la caja del terminal.\n\nEl encargado de hacer el cierre es: $user_description";
                            }

                            break;
                        }
                    }
                }

                if ($response['message'] == '') {

                    /**
                     * Modificar datos del usuario
                     */
                    $credentials = [
                        'description' => $description,
                        'doc_number' => $doc_number,
                        //'username' => $username,
                        'email' => $email,
                        'phone_number' => $phone_number,
                        'updated_at' => Carbon::now()
                    ];

                    \Log::info("phone_number: $phone_number");
                    \Log::debug("Usuario a modificar:", [$credentials]);
                    //\Log::debug("user_id:", [$user_id]);

                    $connection_auth->table('users')
                        ->where('id', $user_id)
                        ->update($credentials);

                    \Log::info("Usuario con ID: $user_id fué modificado.");

                    /**
                     * Verificar los check de atms y ver que se inserta y que se actualiza
                     */

                    for ($i = 0; $i < count($atms_selected); $i++) {
                        $item = $atms_selected[$i];
                        $atm_id = $item['atm_id'];
                        $status = $item['status'];
                        $status_aux = null;

                        if ($status == 'true') {
                            $status_aux = true;
                        } else if ($status == 'false') {
                            $status_aux = false;
                        }

                        /* $connection->table('atms_per_users')
                                ->where('user_supervisor_id', $user_supervisor_id)
                                ->where('atm_id', $atm_id)
                                ->update([
                                    'status' => false,
                                    'updated_at' => Carbon::now(),
                                    'updated_by' => $user_supervisor_id
                                ]);*/

                        $atms_aux = $connection
                            ->table('public.atms_per_users as apu')
                            ->select(
                                'apu.id as atm_per_user_id'
                            )
                            //->where('apu.user_supervisor_id', $user_supervisor_id)
                            ->where('apu.user_id', $user_id)
                            ->where('apu.atm_id', $atm_id)
                            ->get();

                        \Log::info("user_supervisor_id: $user_supervisor_id, user_id: $user_id, atm_id: $atm_id");

                        if (count($atms_aux) > 0) {

                            $connection
                                ->table('atms_per_users')
                                //->where('user_supervisor_id', $user_supervisor_id)
                                ->where('user_id', $user_id)
                                ->where('atm_id', $atm_id)
                                ->update([
                                    'status' => $status_aux,
                                    'updated_at' => Carbon::now(),
                                    'updated_by' => $user_supervisor_id
                                ]);

                            \Log::info("Se actualizó un atm. $atm_id con status = $status");
                        } else {

                            /**
                             * Si marcó a activo un atm que no tiene 
                             */
                            if ($status_aux) {
                                $values_insert = [
                                    'user_supervisor_id' => $user_supervisor_id,
                                    'user_id' => $user_id,
                                    'atm_id' => $atm_id,
                                    'status' => true,
                                    'created_at' => Carbon::now(),
                                    'created_by' => $user_supervisor_id
                                ];

                                $connection->table('atms_per_users')
                                    ->insert($values_insert);

                                \Log::info("Se agregó un atm.");
                            }
                        }


                        /*if (count($atms_per_users) <= 0 and $status == 'true') {
                                $values_insert = [
                                    'user_supervisor_id' => $user_supervisor_id,
                                    'user_id' => $user_id,
                                    'atm_id' => $atm_id,
                                    'status' => true,
                                    'created_at' => Carbon::now(),
                                    'created_by' => $user_supervisor_id
                                ];

                                array_push($atms_per_users_values_insert, $values_insert);
                            } else if (count($atms_per_users) > 0) {

                                $status_old = $atms_per_users[0]->status;

                                $update = false;

                                if (($status == 'true' and $status_old == false) or ($status == 'false' and $status_old == true)) {
                                    $update = true;
                                }

                                if ($update) {
                                    $connection->table('atms_per_users')
                                        ->where('user_supervisor_id', $user_supervisor_id)
                                        ->where('user_id', $user_id)
                                        ->where('atm_id', $atm_id)
                                        ->update([
                                            'status' => $status,
                                            'updated_at' => Carbon::now(),
                                            'updated_by' => $user_supervisor_id
                                        ]);

                                    $values_update = [
                                        'user_supervisor_id' => $user_supervisor_id,
                                        'user_id' => $user_id,
                                        'atm_id' => $atm_id,
                                        'status' => $status,
                                        'updated_at' => Carbon::now(),
                                        'updated_by' => $user_supervisor_id
                                    ];

                                    array_push($atms_per_users_values_update, $values_update);
                                }
                            }*/
                    }

                    /*if (count($atms_per_users_values_update) > 0) {

                            \Log::debug("atms_per_users_values a ACTUALIZAR para el usuario con ID: $user_id :", [$atms_per_users_values_update]);
                        }

                        if (count($atms_per_users_values_insert) > 0) {

                            \Log::debug("atms_per_users_values a INSERTAR para el usuario con ID: $user_id :", [$atms_per_users_values_insert]);

                            $connection->table('atms_per_users')
                                ->insert($atms_per_users_values_insert);
                        }*/

                    $user_updated = true;
                }
            }
        } catch (\Exception $e) {
            $error_detail = [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];

            \Log::debug("Error, Detalles: ", [$error_detail]);

            $response['message'] = 'Ocurrió un error al guardar.';
            $response['response'] = $error_detail;
        }

        if ($response['message'] !== '') {
            $response['error'] = true;
        }

        if ($context == 'add') {
            if ($user_created and $send == false) {
                $response['message'] = "El usuario fué creado correctamente,\npero no se pudo envíar el link de activación\n\nContacte con sistemas.";
            } else if ($user_created and $send) {
                $response['message'] = "El usuario fué creado correctamente.\n\nLink de activación envíado.";
            }
        } else if ($context == 'edit') {
            if ($user_updated == false) {
                $response['message'] = $response['message'] . "\n\nNo se pudo actualizar el usuario.\n\nContacte con sistemas.";
            } else {
                $response['message'] = "El usuario fué actualizado correctamente.";
            }
        }

        return $response;
    }

    /**
     * Función inicial
     */
    public function send($request)
    {
        $response = [
            'error' => false,
            'message' => '',
        ];

        try {

            //$connection = $this->connection;
            //$connection_auth = $this->connection_auth;
            //$user_supervisor_id = $this->user_supervisor_id;

            $user_id = $request['user_id'];
            $description = $request['description'];

            $email_or_phone_number = $request['email_or_phone_number'];
            $email = $request['email'];
            $phone_number = $request['phone_number'];

            //\Log::info("email_or_phone_number: $email_or_phone_number");
            //\Log::info("email: $email");
            //\Log::info("phone_number: $phone_number");

            /**
             * Generación de clave
             */
            $password = new Password();
            $generate_password = $password->generatePassword();

            /**
             * Crear registro de activación
             */

            $user = User::find($user_id);
            $activation = \Activation::create($user);
            $activation_code = $activation->code;

            \Log::info("Activación de $description creada.");

            /**
             * Ruta a envíar
             */
            $link = route('users.activate', [
                'id' => $user_id,
                'code' => $activation_code
            ]);

            /**
             * Enviar código al correo o al teléfono
             */

            if ($email_or_phone_number == 'email') {

                /**
                 * Enviar datos al correo especificado
                 */

                $data = [
                    'user' => $user,
                    'id' => $user->id,
                    'password' => $generate_password,
                    'link' => $link
                ];

                \Mail::send(
                    'mails.account_activation',
                    $data,
                    function ($message) use ($user, $email, $description) {
                        $message->to($email, $user->username)->subject("[EGLOBAL] Activar cuenta del usuario: $description");
                    }
                );

                \Log::info("Mail envíado a $description");
            } else if ($email_or_phone_number == 'phone_number') {

                /**
                 * Enviar datos al teléfono especificado
                 */

                $url = $this->url . 'send_sms';
                $app_key = $this->app_key;
                $public_key = $this->public_key;

                $json = [
                    'phone_number' => $phone_number,
                    'text' => $link
                ];

                $date = Carbon::now()->format('D, d M Y H:i:s T00');
                $params = array($date, 'POST', $url, json_encode($json));
                $params_hash = hash_hmac("sha1", implode("\n", $params), $app_key);

                $petition = HttpClient::post(
                    $url,
                    [
                        'auth' => [$public_key, $params_hash],
                        'headers' => [
                            'Timestamp' => $date
                        ],
                        'json' => $json,
                        'connect_timeout' => 180,
                        'verify' => false
                    ]
                );

                $response['response'] = json_decode($petition->getBody()->getContents());

                \Log::debug("Respuesta del servicio que envía mensajes:", [$response['response']]);
            }
        } catch (\Exception $e) {
            $error_detail = [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];

            \Log::debug("Error, Detalles: ", [$error_detail]);

            $response['message'] = "Ocurrió un error al envíar el link.\n\nContacte con sistemas.";
            $response['response'] = $error_detail;
        }

        if ($response['message'] !== '') {
            $response['error'] = true;
        } else {
            $response['message'] = 'Se envío correctamente el link.';
        }

        return $response;
    }

    /**
     * OBtener terminales por usuario
     */
    public function get_atms_per_user($request)
    {

        $response = [
            'error' => false,
            'message' => '',
            'response' => [],
            'error_detail' => null
        ];

        $records = [];
        $records_aux = [];

        try {

            $connection = $this->connection;
            $user_id = $request['user_id'];
            $user_supervisor_id = $request['user_supervisor_id'];
            //$user_supervisor_id = $this->user->id;
            $ids_aux = -1;
            $ids_aux_2 = -1;

            \Log::info("------------------------------------------------");
            \Log::info("user_id: $user_id");
            \Log::info("user_supervisor_id: $user_supervisor_id");
            \Log::info("------------------------------------------------");

            /**
             * Traer todos los atm_ids que esteen activos en otros registros distintos al del usuario actual
             */
            /*$ids = $connection
                ->table('public.atms_per_users as apu')
                ->select(
                    \DB::raw("coalesce(array_to_string(array_agg(apu.atm_id), ', '), '-1') as ids")
                )
                ->join('atms as a', 'a.id', '=', 'apu.atm_id');

            if ($super_user == false) {
                $ids = $ids->where('apu.user_supervisor_id', $user_supervisor_id);
            }

            $ids = $ids->whereRaw("apu.user_id != $user_id")
                ->where('apu.status', true) // Traer todos los atms_ids que esteen con estado TRUE
                ->get();*/

            //Trae todos los ids que esteen relacionados al usuario supervisor, que no sean igual al usuario normal y que esteen activos

            $ids = $connection
                ->table('public.atms_per_users as apu')
                ->select(
                    \DB::raw("coalesce(array_to_string(array_agg(apu.atm_id), ', '), '-1') as ids")
                )
                ->join('atms as a', 'a.id', '=', 'apu.atm_id')
                ->where('apu.user_supervisor_id', $user_supervisor_id)
                ->whereRaw("apu.user_id != $user_id")
                ->where('apu.status', true) // Traer todos los atms_ids que esteen con estado TRUE
                ->get();

            $response['ids'] = $ids;

            /**
             * Trae los terminales relacionado al usuario que esteen activos e inactivos
             */
            $records = $connection
                ->table('public.atms_per_users as apu')
                ->select(
                    'apu.atm_id',
                    'apu.user_id',
                    \DB::raw("replace(a.name, '''', '') as description"),
                    'apu.status'
                )
                ->join('atms as a', 'a.id', '=', 'apu.atm_id')
                ->where('apu.user_id', $user_id); // Traer todos los que son del usuario

            if (count($ids) > 0) {
                $ids_aux = $ids[0]->ids;
                $records = $records->whereRaw("apu.atm_id not in ($ids_aux)");
            }

            $records = $records
                ->where('apu.user_supervisor_id', $user_supervisor_id)
                ->get();

            $response['records'] = $records;

            foreach ($records as $item) {

                $item_aux = [
                    'atm_id' => $item->atm_id,
                    'user_id' => $item->user_id,
                    'description' => $item->description,
                    'status' => $item->status
                ];

                array_push($records_aux, $item_aux);
            }

            /**
             * Trae los terminales relacionado al usuario
             */
            $ids_2 = $connection
                ->table('public.atms_per_users as apu')
                ->select(
                    \DB::raw("coalesce(array_to_string(array_agg(apu.atm_id), ', '), '-1') as ids")
                )
                ->join('atms as a', 'a.id', '=', 'apu.atm_id')
                ->where('apu.user_supervisor_id', $user_supervisor_id)
                ->where('apu.user_id', $user_id)
                ->whereRaw("apu.atm_id not in ($ids_aux)")
                ->get();

            $response['ids_2'] = $ids_2;

            $records = $connection
                ->table('public.atms_per_users as apu')
                ->select(
                    'apu.atm_id',
                    \DB::raw("replace(a.name, '''', '') as description")
                )
                ->join('atms as a', 'a.id', '=', 'apu.atm_id')
                ->where('apu.user_supervisor_id', $user_supervisor_id)
                ->whereRaw("apu.user_id != $user_id");

            if (count($ids_2) > 0) {
                $ids_aux_2 = $ids_2[0]->ids;

                $records = $records->whereRaw("apu.atm_id not in ($ids_aux)");
                $records = $records->whereRaw("apu.atm_id not in ($ids_aux_2)");
            }

            $records = $records->where('apu.status', false)
                ->groupBy('apu.atm_id')
                ->groupBy('a.name')
                ->get();

            $response['records_2'] = $records;

            foreach ($records as $item) {

                $item_aux = [
                    'atm_id' => $item->atm_id,
                    'user_id' => null,
                    'description' => $item->description,
                    'status' => false,
                ];

                array_push($records_aux, $item_aux);
            }

            $response['records_aux'] = $records_aux;

            $response['message'] = 'Acción exitosa';
            $response['response'] = $records_aux;
        } catch (\Exception $e) {
            $error_detail = [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];

            $response['error_detail'] = $error_detail;

            \Log::error("Error, Detalles: " . json_encode($error_detail));
        }

        return $response;
    }
}