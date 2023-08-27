<?php

/**
 * User: avisconte
 * Date: 24/08/2022
 * Time: 13:00 ñm
 */

namespace App\Services\Info;

use Carbon\Carbon;

class PlantUmlServices
{

    public function __construct()
    {
        $this->user = \Sentinel::getUser();
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

        $this->user = \Sentinel::getUser();
        $user_id = $this->user->id;

        if (!$this->user->hasAccess('info_plant_uml')) {
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
        $get_info = true;

        try {

            $connection = $this->connection;

            /**
             * Trae las consultas activas
             */
            $records = $connection
                ->table('uml.generated_diagrams')
                ->select(
                    'id',
                    'title',
                    'description',
                    'code',
                    'width',
                    'height',
                    \DB::raw("to_char(created_at, 'DD/MM/YYYY HH24:MI:SS') as created_at"),
                    \DB::raw("'update' as mode")
                )
                ->where('created_by', $user_id)
                ->get();

            $records = json_decode(json_encode($records), true);

            $headers = [];

            if (count($records) > 0) {
                $records = json_decode(json_encode($records), true);
                $headers = array_keys($records[0]);

                $data['lists']['records'] = $records;
                $data['lists']['headers'] = $headers;
            }

            //Traer solo cuando hay búsqueda no cuando genera el excel.
            if ($get_info) {
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
                'headers' => $headers,
                'records' => $records,
            ],
            'inputs' => [
                'payments_id' => isset($request['payments_id']) ? $request['payments_id'] : null,
                'atm_id' => isset($request['atm_id']) ? $request['atm_id'] : null
            ]
        ];

        return view('info.plant_uml', compact('data'));
    }

    /**
     * Función guardar
     */
    public function save($request)
    {

        $response = [
            'error' => false,
            'message' => '',
            'error_detail' => null
        ];

        try {
            $this->user = \Sentinel::getUser();
            $user_id = $this->user->id;

            $id = $request['id'];
            $title = $request['title'];
            $description = $request['description'];
            $code = $request['code'];
            $width = ($request['width'] == '') ? null : $request['width'];
            $height = ($request['height'] == '') ? null : $request['height'];
            $mode = $request['mode'];

            $columns = [
                'title' => $title,
                'description' => $description,
                'code' => $code,
                'width' => $width,
                'height' => $height,
            ];

            $now = Carbon::now();

            \DB::beginTransaction(); // Inicio de inserciones

            if ($mode == 'insert') {

                $columns['created_at'] = $now;
                $columns['created_by'] = $user_id;

                $generated_diagrams_id = \DB::table('uml.generated_diagrams')
                    ->insertGetId($columns);
            } else if ($mode == 'update') {

                $columns['updated_at'] = $now;

                \DB::table('uml.generated_diagrams')
                    ->where('id', $id)
                    ->update($columns);
            }

            \Log::info("Se guardó exitosamente el diagrama UML");

            \DB::commit(); // Confirmación de inserciones y actualizaciones
        } catch (\Exception $e) {

            \DB::rollback(); // Deshacer todo si ocurre un error

            $error_detail = [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];

            $response['error'] = true;
            $response['message'] = 'Ocurrió un error con el servicio de pagos.';
            $response['error_detail'] = $error_detail;

            \Log::error("\nError en " . __FUNCTION__ . ": \nDetalles: " . json_encode($error_detail));
        }

        return $response;
    }
}
