<?php

/**
 * User: avisconte
 * Date: 01/02/2022
 * Time: 15:20
 */

namespace App\Http\Controllers\Info;

use App\Exports\ExcelExport;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Excel;
use Carbon\Carbon;

class QueryToExportController extends Controller
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

    /*
    <div class="box box-default" style="border: 1px solid #d2d6de;">
        <div class="box-header with-border">
            <h3 class="box-title">Información del sitema:</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse" id="search_open"><i class="fa fa-minus"></i></button>
            </div>
        </div>
        <div class="box-body" style="font-size: 12px">
            <?php
                $info_items = [
                    'Sistema:' => shell_exec('uname -a'),
                    'PHP:' => shell_exec('php -v'),
                    'Postgresql:' => shell_exec('psql --version'),
                    //'Vagrant:' => shell_exec('vagrant --v')
                    'nginx' => shell_exec('service nginx status')
                ];
            ?>

            @foreach ($info_items as $key => $value)
                <b>{{$key}} </b> {{$value}} <br/>
            @endforeach
        </div>
    </div>
    */

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
            'message' => $message,
            'lists' => [
                'records' => [],
                'headers' => []
            ],
            'inputs' => [
                'query' => '',
                'json' => []
            ],
            'error_detail' => []
        ];

        if (!$this->user->hasAccess('query_to_export')) {

            \Log::error(
                'No tienes permiso para acceder a esta pantalla',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );

            \Session::flash('error_message', 'No tienes permiso para acceder a esta pantalla.');

            return redirect('/');
        } else {

            if (isset($request['query'])) {

                $query = $request['query'];

                try {

                    $data['inputs']['query'] = $query;

                    if (isset($request['button_name'])) {

                        $button_name = $request['button_name'];

                        if ($button_name == 'search') {

                            $excluded_words = array('drop ', 'delete ', 'truncate ');

                            foreach ($excluded_words as $word) {

                                if (stripos(strtoupper($query), strtoupper($word)) !== false) {

                                    $message = "¡Alto ahí! No puedes ejecutar la sentencia: $word";
                                    break;
                                }
                            }

                            // Validación para DELETE
                            /*if (stripos($query, strtoupper('delete')) !== false && stripos($query, strtoupper('where')) === false) {
                                $message = "La consulta DELETE debe incluir una cláusula WHERE.";
                            }*/

                            // Validación para UPDATE
                            if (stripos($query, strtoupper(' update ')) !== false && stripos($query, strtoupper('where')) === false) {
                                $message = "La consulta UPDATE debe incluir una cláusula WHERE.";
                            }

                            if ($message == '') {

                                $pattern = '/\bUPDATE\b/i';

                                if (preg_match($pattern, $query) === 1) {

                                    $update_query = $query;

                                    preg_match('/UPDATE\s+([^\s]+)\s+SET\s+(.*)\s+WHERE\s+(.*)/is', $query, $matches);

                                    \Log::info('maches count:', [count($matches)]);
                                    \Log::info('maches:', [$matches]);

                                    if (count($matches) == 4) {

                                        $table_id = $matches[1];
                                        $set_clause = $matches[2];
                                        $where_clause = $matches[3];

                                        // Construir la consulta SELECT equivalente
                                        $query = "select * from $table_id where $where_clause";

                                        //\Log::debug("update: $update_query | select: $query");

                                        $get_table = \DB::table('audit.tables')
                                            ->select(
                                                'id'
                                            )
                                            ->whereRaw("description = '$table_id'")
                                            ->get();

                                        if (count($get_table) <= 0) {

                                            $insert_table_id = \DB::table('audit.tables')
                                                ->insertGetId([
                                                    'description' => $table_id,
                                                    'created_at' => Carbon::now()
                                                ]);
                                        } else {
                                            $insert_table_id = $get_table[0]->id;
                                        }

                                        $query_primary_key_column = "
                                            select 
                                                distinct 
                                                ci.column_name
                                            from information_schema.columns ci
                                            left join information_schema.key_column_usage kcu on kcu.table_catalog = ci.table_catalog and kcu.table_schema = ci.table_schema and kcu.table_name = ci.table_name and kcu.column_name = ci.column_name
                                            left join information_schema.table_constraints tc on tc.constraint_catalog = kcu.constraint_catalog and tc.constraint_schema = kcu.constraint_schema and tc.constraint_name = kcu.constraint_name
                                            where (ci.table_schema || '.' || ci.table_name) = '$table_id'
                                            and tc.constraint_type = 'PRIMARY KEY'
                                        ";

                                        $query_primary_key_column = \DB::select($query_primary_key_column);
                                        $primary_key_column = null;

                                        if (count($query_primary_key_column) > 0) {
                                            $primary_key_column = $query_primary_key_column[0]->column_name;
                                        }

                                        if ($primary_key_column !== null) {

                                            $select_data = \DB::select($query);
                                            $select_data = json_decode(json_encode($select_data), true);

                                            \DB::beginTransaction();

                                            try {

                                                for ($i = 0; $i < count($select_data); $i++) {
    
                                                    $item = $select_data[$i];
                                                    $pk_id = $item[$primary_key_column];
                                                    $item_json = json_encode($item);
    
                                                    $tables_data_insert = [
                                                        'created_at' => Carbon::now(),
                                                        'created_by' => $this->user->id,
                                                        'commentary' => 'Se realiza actualización desde el exportador del CMS.',
                                                        'table_id' => $insert_table_id,
                                                        'pk_id' => $pk_id,
                                                        'data' => $item_json
                                                    ];
                                    
                                                    /**
                                                        Insertar en auditoría:
                                                    */
                                                    \DB::table('audit.tables_data')
                                                        ->insert($tables_data_insert);
                                                }


                                                $tables_data_insert = [
                                                    'created_at' => Carbon::now(),
                                                    'created_by' => $this->user->id,
                                                    'commentary' => 'Se ejecuta el update desde el exportador del CMS.',
                                                    'table_id' => $insert_table_id,
                                                    'query' => $update_query
                                                ];
                                
                                                /**
                                                    Insertar en tables_query:
                                                */
                                                \DB::table('audit.tables_query')
                                                    ->insert($tables_data_insert);

    
                                                /**
                                                    Realizar la actualización:
                                                 */
                                                \DB::select($update_query);


                                                \DB::commit();

                                            } catch (\Exception $e) {

                                                \DB::rollback();

                                                $error_detail = [
                                                    'message' => $e->getMessage(),
                                                    'file' => $e->getFile(),
                                                    'class' => __CLASS__,
                                                    'function' => __FUNCTION__,
                                                    'line' => $e->getLine()
                                                ];

                                                $data['error_detail'] = $error_detail;

                                                $message = "Ocurrió un problema al ejecutar el update, Detalles:" . $e->getMessage();

                                                \Log::error("\nOcurrió un error al ejecutar el update:\n", [$error_detail]);
                                            }
                                        } else {
                                            $message = 'La tabla a actualizar no tiene PRIMARY KEY';
                                        }
                                    } else {
                                        $message = 'Al update le falta una clausula para ser ejecutada.';
                                    }
                                }

                                $records = \DB::select($query);

                                $data['lists']['records'] = [];
                                $data['lists']['headers'] = [];

                                if (count($records) > 0) {
                                    $records = json_decode(json_encode($records), true);
                                    $headers = array_keys($records[0]);

                                    $data['lists']['records'] = $records;
                                    $data['lists']['headers'] = $headers;
                                }

                                if (isset($records[0])) {
                                    if (count($records[0]) <= 0) {
                                        $records = [];
                                    }
                                }
                            }
                        } else {

                            $records = json_decode($request['json'], true);

                            if (count($records) > 0) {
                                $records = json_decode(json_encode($records), true);
                                $headers = array_keys($records[0]);

                                $data['lists']['records'] = $records;
                                $data['lists']['headers'] = $headers;
                            }

                            if (count($records) > 0) {
                                $filename = 'export_to_' . $button_name . '_' . time();

                                $style_array = [
                                    'font'  => [
                                        'bold'  => true,
                                        'color' => ['rgb' => '367fa9'],
                                        'size'  => 12,
                                        'name'  => 'Verdana'
                                    ]
                                ];

                                $alphabet = [];
                                $alphabet_aux = [];

                                for ($i = 65; $i <= 90; $i++) {
                                    $letter = chr($i);
                                    array_push($alphabet, $letter);
                                }

                                $alphabet_aux = $alphabet;

                                for ($i = 0; $i < count($alphabet_aux); $i++) {

                                    $letter_i = $alphabet_aux[$i];

                                    for ($j = 0; $j < count($alphabet_aux); $j++) {
                                        $letter_j = $alphabet_aux[$j];
                                        $letters = "$letter_i$letter_j";
                                        array_push($alphabet, $letters);
                                    }
                                }

                                $letter = $alphabet[count($headers) - 1];

                                array_unshift($records, $headers);

                                $columnas = [];

                                $excel = new ExcelExport($records,$columnas);
                                return Excel::download($excel, $filename . '.' .$button_name)->send();

                                // Excel::create($filename, function ($excel) use ($records, $style_array, $letter, $query, $button_name) {

                                //     $excel->sheet('Registros', function ($sheet) use ($records, $style_array, $letter, $button_name) {
                                //         $range = 'A1:' . $letter . '1';
                                //         $sheet->rows($records, false);

                                //         if ($button_name !== 'csv') {
                                //             $sheet->getStyle($range)->applyFromArray($style_array);
                                //             $sheet->setHeight(1, 30);
                                //         }
                                //     });
                                // })->export($button_name);
                            } else {
                                $message = 'La consulta no retornó ningún registro.';
                            }
                        }
                    }
                } catch (\Exception $e) {
                    $error_detail = [
                        'message' => $e->getMessage(),
                        'file' => $e->getFile(),
                        'class' => __CLASS__,
                        'function' => __FUNCTION__,
                        'line' => $e->getLine()
                    ];

                    $data['error_detail'] = $error_detail;

                    $message = "Ocurrió un problema al ejecutar la sentencia, Detalles:" . $e->getMessage();

                    \Log::debug("\nOcurrió un error al ejecutar la consulta:\n", [$error_detail]);
                }
            }

            $data['message'] = $message;

            $response = view('info.query_to_export', compact('data'));
        }

        //\Log::info("Mensaje: $message");

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
}
