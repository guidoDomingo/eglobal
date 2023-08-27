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

class AtmsPerUsersServices
{

    public function __construct()
    {
        $this->user = \Sentinel::getUser();
        $this->user_supervisor_id = $this->user->id;

        $this->connection = \DB::connection('eglobalt_pro');
        $this->connection_auth = \DB::connection('eglobalt_auth');
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

        if (!$this->user->hasAccess('atms_per_users')) {
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
        $users = [];
        $atms = [];
        $atms_per_users_free = [];
        $get_info = true;

        try {


            $connection = $this->connection;
            $user_supervisor_id = $this->user_supervisor_id;
            $super_user = false;
            $button_name = '';


            if ($this->user->hasRole('superuser')) {
                $super_user = true;
            }

            if (isset($request['button_name'])) {
                $button_name = $request['button_name'];
            }

            if ($button_name == 'search') {

                /**
                 * Trae el detalle de pago ordenado por proveedor, terminal, servicio y comisión 
                 */
                $records = $connection
                    ->table('public.atms_per_users as apu')
                    ->select(
                        'apu.id as atm_per_user_id',
                        'u.id as user_id',
                        \DB::raw("('#' || u.id || ' - ' || u.description || ' - ' || u.username) as user"),
                        'a.id as atm_id',
                        \DB::raw("('#' || a.id || ' - ' || a.name) as atm"),
                        \DB::raw("to_char(apu.created_at, 'DD/MM/YYYY - HH24:MI:SS') as created_at"),
                        \DB::raw("to_char(apu.updated_at, 'DD/MM/YYYY - HH24:MI:SS') as updated_at"),
                        'apu.status',
                        \DB::raw("(case when apu.status = true then 'Activo' else 'Inactivo' end) as status_view")
                    )
                    ->join('users as u', 'u.id', '=', 'apu.user_id')
                    ->join('atms as a', 'a.id', '=', 'apu.atm_id');

                if ($super_user == false) {
                    $records = $records->where('apu.user_supervisor_id', $user_supervisor_id);
                }

                /**
                 * Filtros para la búsqueda
                 */

                if (isset($request['user_id'])) {
                    if ($request['user_id'] !== '' and $request['user_id'] !== 'Todos') {
                        $records = $records->where('u.id', $request['user_id']);
                    }
                }

                if (isset($request['atm_id'])) {
                    if ($request['atm_id'] !== '' and $request['atm_id'] !== 'Todos') {
                        $records = $records->where('a.id', $request['atm_id']);
                    }
                }

                //\Log::info('user_id:' . $request['user_id']);
                //\Log::info('atm_id:' . $request['atm_id']);
                //\Log::info('QUERY:');
                //\Log::info($records->toSql());

                $records = $records
                    ->orderBy('a.id', 'ASC')
                    ->get();

                $records = json_decode(json_encode($records), true);

                if (count($records) > 0) {
                } else {
                    $data = [
                        'mode' => 'alert',
                        'type' => 'info',
                        'title' => 'Consulta sin registros',
                        'explanation' => 'La consulta no retornó ningún registro.'
                    ];

                    return view('messages.index', compact('data'));
                }

                /**
                 * Traer todos los atm_ids que esteen activos en otros registros distintos al del usuario actual
                 */
                $ids = $connection
                    ->table('public.atms_per_users as apu')
                    ->select(
                        \DB::raw("coalesce(array_to_string(array_agg(apu.atm_id), ', '), '-1') as ids")
                    )
                    ->join('atms as a', 'a.id', '=', 'apu.atm_id')
                    ->where('apu.status', true); // Traer todos los atms_ids que esteen con estado TRUE

                if ($super_user == false) {
                    $ids = $ids->where('apu.user_supervisor_id', $user_supervisor_id);
                }

                $ids = $ids->get();

                if (count($ids) > 0) {
                    $ids = $ids[0]->ids;
                } else {
                    $ids = '-1';
                }

                \Log::info("ids de atms con estado activo: $ids");

                $atms_per_users_free = $connection
                    ->table('public.atms_per_users as apu')
                    ->select(
                        'apu.atm_id',
                        \DB::raw("replace(a.name, '''', '') as description")
                    )
                    ->join('atms as a', 'a.id', '=', 'apu.atm_id')
                    ->whereRaw("apu.atm_id not in ($ids)")
                    ->where('apu.status', false);

                if ($super_user == false) {
                    $atms_per_users_free = $atms_per_users_free->where('apu.user_supervisor_id', $user_supervisor_id);
                }

                $atms_per_users_free = $atms_per_users_free
                    ->groupBy('apu.atm_id')
                    ->groupBy('a.name')
                    ->orderBy('apu.atm_id', 'DESC')
                    ->get();

                \Log::info("atms_per_users_free inactivos:", [$atms_per_users_free]);
            } else if ($request['button_name'] == 'generate_x') {

                $records = json_decode($request['json'], true);
                $records_aux = [];

                for ($i = 0; $i < count($records); $i++) {

                    $item = $records[$i];

                    $user_id_view = $item['user_id'];
                    $user = $item['user'];
                    $atm_id_view = $item['atm_id'];
                    $atm = $item['atm'];
                    $created_at = $item['created_at'];
                    $updated_at = $item['updated_at'];
                    $status_view = $item['status_view'];

                    $row = [
                        'user_id_view' => $user_id_view,
                        'user' => $user,
                        'atm_id' => $atm_id_view,
                        'atm' => $atm,
                        'created_at' => $created_at,
                        'updated_at' => $updated_at,
                        'status_view' => $status_view
                    ];

                    array_push($records_aux, $row);
                }


                $style_array = [
                    'font'  => [
                        'bold'  => true,
                        'color' => ['rgb' => '367fa9'],
                        'size'  => 12,
                        'name'  => 'Verdana'
                    ]
                ];

                $filename = 'report_' . time();
                $columnas = array(
                    'ID - Usuario',
                    'Usuario',
                    'ID - Terminal',
                    'Terminal',
                    'Fecha y Hora de creación',
                    'Fecha y Hora de actualización',
                    'Estado'
                );

                $excel = new ExcelExport($records_aux,$columnas);
                return Excel::download($excel, $filename . '.xls')->send();

                // Excel::create($filename, function ($excel) use ($records_aux, $style_array) {
                //     $excel->sheet('Usuarios por Terminal', function ($sheet) use ($records_aux, $style_array) {
                //         $sheet->rows($records_aux, false);

                //         $sheet->prependRow(array(
                //             'ID - Usuario',
                //             'Usuario',
                //             'ID - Terminal',
                //             'Terminal',
                //             'Fecha y Hora de creación',
                //             'Fecha y Hora de actualización',
                //             'Estado'
                //         ));

                //         $sheet->getStyle('A1:G1')->applyFromArray($style_array); //Aplicar los estilos del array
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
                    ->table('public.atms_per_users as apu')
                    ->select(
                        'u.id',
                        \DB::raw("replace((u.id || '# ' || u.description || ' - ' || u.username), '''', '') as description")
                    )
                    ->join('users as u', 'u.id', '=', 'apu.user_id')
                    ->groupBy('u.id')
                    ->groupBy('u.description')
                    ->groupBy('u.username')
                    ->orderBy('u.id', 'DESC');

                if ($super_user == false) {
                    $users = $users->where('apu.user_supervisor_id', $user_supervisor_id);
                }

                $users = $users->get();

                /**
                 * Trae los terminales relacionados al supervisor
                 */
                $atms = $connection
                    ->table('atms as a')
                    ->select(
                        'a.id',
                        \DB::raw("replace(a.name, '''', '') as description")
                    )
                    ->join('public.atms_per_users as apu', 'a.id', '=', 'apu.atm_id');

                if ($super_user == false) {
                    $atms = $atms->where('apu.user_supervisor_id', $user_supervisor_id);
                }

                $atms = $atms->groupBy('a.id')
                    ->orderBy('a.id', 'DESC')
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
                'atms_per_users_free' => $atms_per_users_free
            ],
            'inputs' => [
                'user_id' => isset($request['user_id']) ? $request['user_id'] : 'Todos',
                'atm_id' => isset($request['atm_id']) ? $request['atm_id'] : 'Todos',
            ]
        ];

        //\Log::info("data: ");
        //\Log::info($data);

        return view('atms_per_users.index', compact('data'));
    }



    /**
     * Función para guardar el estado
     */
    public function atms_per_users_save($request)
    {
        $response = [
            'error' => false,
            'message' => '',
        ];

        try {

            $connection = $this->connection;
            $user_supervisor_id = $this->user_supervisor_id;

            $atm_per_user_id = $request['atm_per_user_id'];
            $user_id = $request['user_id'];
            $atm_id = $request['atm_id'];
            $status = $request['status'];

            //\Log::info("Status = $status");

            $update = true;

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

                \Log::info("$movement_type_id_aux == 9 and intval($user_id) !== $user_id_aux");

                $aux = [
                    'user_id' => $user_id,
                    'user_id_aux' => $user_id_aux
                ];

                \Log::info($aux);

                /**
                 * El último tipo de movimiento fué apertura y el usuario que tiene que cerrar la caja es distinto al que estamos asignando.
                 */
                if ($movement_type_id_aux == 9 and intval($user_id) !== $user_id_aux) {

                    $response['message'] = "No se puede asignar la terminal: $atm_description\n\n.Falta cerrar la caja del terminal.\n\nEl encargado de hacer el cierre es: $user_description";

                    $update = false;
                }
            }

            //return $response;
            //die();

            if ($update) {
                if ($status) {
                    $connection->table('atms_per_users')
                        ->where('user_supervisor_id', $user_supervisor_id)
                        ->where('atm_id', $atm_id)
                        ->update([
                            'status' => false,
                            'updated_at' => Carbon::now(),
                            'updated_by' => $user_supervisor_id
                        ]);
                }

                $connection->table('atms_per_users')
                    ->where('id', $atm_per_user_id)
                    ->update([
                        'status' => $status,
                        'updated_at' => Carbon::now(),
                        'updated_by' => $user_supervisor_id
                    ]);
            } else {
                $response['message'] = "No se puede asignar la terminal: $atm_description\n\n.Falta cerrar la caja del terminal.\n\nEl encargado de hacer el cierre es: $user_description";
            }

            //\Log::debug("Registro actualizado", ['atm_per_user_id' => $atm_per_user_id]);
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
        }

        if ($response['message'] !== '') {
            $response['error'] = true;
        } else {
            $response['message'] = 'Actualización exitosa.';
        }

        return $response;
    }
}
