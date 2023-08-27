<?php

/**
 * User: avisconte
 * Date: 01/02/2022
 * Time: 15:20
 */

namespace App\Http\Controllers\Info;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Excel;

class FileToTableController extends Controller
{
    /**
     * @var class $user: Usuario
     * @global object 
     */
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    /**
     * Inicia y busca (Servicio)
     */
    public function index_($request)
    {

        ini_set('max_execution_time', 0);
        ini_set('client_max_body_size', '20M');
        ini_set('max_input_vars', 10000);
        ini_set('upload_max_filesize', '20M');
        ini_set('post_max_size', '20M');
        ini_set('memory_limit', '-1');
        set_time_limit(3600);

        $error = false;
        $message = '';
        $response = [];
        $data = [
            'lists' => [
                'schemas' => [],
                'records' => [],
                'headers' => []
            ],
            'inputs' => [
                'file' => null,
                //'schema' => null,
                'table_name' => null
            ]
        ];

        $user_id = $this->user->id;

        if (!$this->user->hasAccess('query_to_export')) {
            \Log::error(
                'No tienes permiso para acceder a esta pantalla',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            \Session::flash('error_message', 'No tienes permiso para acceder a esta pantalla.');

            return redirect('/');
        } else {

            $schemas = \DB::table('pg_catalog.pg_namespace')
                ->select(
                    'nspname as id',
                    'nspname as description'
                )
                ->whereRaw("nspname not ilike all(array['%pg_toast%','%pg_temp%', '%pg_toast_temp%', '%pg_catalog%', '%information_schema%'])")
                ->orderBy('nspname', 'ASC')
                ->get();

            $data['lists']['schemas'] = json_encode($schemas);

            if (isset($request['file']) and isset($request['table_name'])) {

                $file = $request['file'];
                $table_name = $request['table_name'];
                $schema_table_name = "info.$table_name";

                $data['inputs']['file'] = $file;
                $data['inputs']['table_name'] = $table_name;


                $client_original_name      = $file->getClientOriginalName();
                $client_original_extension = $file->getClientOriginalExtension();
                $file_size                 = $file->getSize();
                $file_type                 = $file->getMimeType();
                $file_real_path            = $file->getRealPath();

                \Log::info("Nombre del archivo: $client_original_name, extension: $client_original_extension, longitud: $file_size");

                try {

                    $excel = [];

                    /*
                        $counter=0;
                        // add & to have var passed by reference
                        Excel::load($fileDetails['file_path'], function($sheet) use(&$counter) {
                            // add & to have var passed by reference
                            $sheet->each(function($sheet) use(&$counter) {
                                echo "It works</br>"; 
                                $counter++;
                            });
                        });
                        echo $counter;
                        exit;
                    */

                    $file_to_array = Excel::load($file, function ($reader) {
                        /**
                         * lectura de excel
                         */
                    })->get();

                    $file_to_array = json_decode(json_encode($file_to_array), true);

                    //\Log::info('file_to_array:', [$file_to_array]);

                    $headers = $file_to_array[0];

                    \Log::info('headers:', [$headers]);

                    unset($headers['0']);

                    if (count($headers) > 0) {

                        $headers_aux = [];

                        foreach ($headers as $key => $value) {

                            $headers_aux[$key] = null;

                            if (strpos($key, '.')) {
                                //echo "Word Found!";
                            } else {
                                //echo "Word Not Found!";
                            }
                        }

                        $data['lists']['headers'] = $headers;

                        $information_schema_tables = $connection
                            ->table('information_schema.tables')
                            ->select(
                                \DB::raw("table_schema || '.' || table_name")
                            )
                            ->whereRaw("lower('info.' || table_name) = lower('$schema_table_name')")
                            ->get();

                        \Log::info("information_schema_tables:", [$information_schema_tables]);

                        if (count($information_schema_tables) > 0) {
                            \DB::select("drop table $schema_table_name;");
                            \DB::select("create table $schema_table_name();");

                            \Log::info("TABLA: ($schema_table_name) EXISTENTE Y ELIMINADA");
                        } else {
                            \DB::select("create table $schema_table_name();");

                            \Log::info("TABLA ($schema_table_name) NO EXISTENTE Y CREADA");
                        }

                        $columns_insert = [];
                        $columns_alter = "alter table $schema_table_name ";

                        foreach ($headers as $key => $value) {
                            $columns_alter .= "add column \"$key\" text, ";
                        }

                        if ($columns_alter !== '') {
                            $columns_alter .= "add column row_created_by integer, ";
                            $columns_alter .= "add column row_created_at timestamp;";
                            \DB::statement($columns_alter);
                        }

                        \Log::info('columns_alter: ' . $columns_alter);

                        $columns = '';

                        foreach ($headers as $key => $value) {
                            $columns .= "\"$key\",";
                        }

                        $columns = "$columns row_created_by, row_created_at";

                        $inserts = "insert into $schema_table_name($columns) values \n";

                        $columns_values_list = [];

                        for ($i = 0; $i < count($file_to_array); $i++) {

                            $item = $file_to_array[$i];

                            $columns_values = '(';

                            foreach ($headers as $key => $value) {
                                $value = $item[$key];

                                if (is_array($value)) {
                                    $value = $value['date'];
                                } else {
                                    if (str_contains($value, "'")) {
                                        $value = str_replace("'", "''", $value);
                                    }

                                    $value = ltrim($value);
                                    $value = rtrim($value);
                                }

                                $columns_values .= "'$value', ";
                            }

                            $columns_values .= "$user_id, now())";

                            array_push($columns_values_list, $columns_values);
                        }

                        $inserts .= implode(', ', $columns_values_list);

                        \Log::info('insertando...');

                        if ($inserts !== '') {
                            \DB::select($inserts);
                        }

                        $records = \DB::select("select * from $schema_table_name;");

                        $data['lists']['records'] = json_decode(json_encode($records), true);
                    }
                } catch (\Exception $e) {
                    $error_detail = [
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'class' => __CLASS__,
                        'function' => __FUNCTION__,
                        'line' => $e->getLine()
                    ];

                    \Log::debug("\nOcurrió un error al ejecutar la consulta:\n", [$error_detail]);

                    $message = "Ocurrió un problema al realizar la acción, Detalles: \n" . $e->getMessage();
                }
            }

            $response = view('info.file_to_table', compact('data'));
        }

        if ($message == '') {
            \Session::flash('message', 'Acción exitosa.');
        } else {
            \Session::flash('error_message', $message);
        }

        return $response;
    }

    /**
     * Editar registro
     */
    public function save_($request)
    {
        $response = [
            'error' => false,
            'message' => 'Acción exitosa.'
        ];

        try {
            //\Log::debug("\nMODO:\n", [$request['mode']]);
            \Log::debug("\nITEMS:\n", [$request['table_id'], $request['ids']]);

            $columns_insert = [];

            for ($i = 0; $i < count($request['ids']); $i++) {
                $item = $request['ids'][$i];

                if ($item['is_pk'] == 'false' and $item['new_value'] !== '') {
                    $columns_insert[$item['column_name']] = $item['new_value'];
                }
            }

            \DB::table($request['table_id'])->insert($columns_insert);

            \Log::debug("\ncolumns_insert:\n", [$columns_insert]);
        } catch (\Exception $e) {
            $error_detail = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];

            \Log::debug("\nOcurrión un error al modificar el registro:\n", [$error_detail]);

            $response['message'] = "Ocurrió un problema al realizar la acción";
        }

        if ($response['message'] !== 'Acción exitosa.') {
            $response['error'] == true;
        }

        return $response;
    }

    /**
     * Inicia y busca
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->index_($request);
    }

    /**
     * Inicia y busca
     *
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request)
    {
        return $this->save_($request);
    }
}
