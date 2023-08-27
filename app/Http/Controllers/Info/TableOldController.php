<?php

/**
 * User: avisconte
 * Date: 01/02/2022
 * Time: 15:20
 */

namespace App\Http\Controllers\Info;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TableController extends Controller
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
        $error = false;
        $response = [];
        $data = [
            'lists' => [
                'information_schema_tables' => [],
                'information_schema_columns' => [],
                'columns_one_hide' => ['created_at', 'updated_at', 'deleted_at', 'created_by', 'updated_by', 'deleted_by'],
                'records' => []
            ],
            'primary_keys_count' => 0,
            'query_generated' => null,
            'combos' => [
                0 => [
                    'id' => 'table_id',
                    'list' => [],
                    'value' => null
                ]
            ]
        ];

        if (!$this->user->hasAccess('info_table')) {
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

        $users = \DB::table('public.users')
            ->select(
                'id',
                \DB::raw("('#' || id || '. ' || description || ' (' || username || ')') as description")
            )
            ->whereRaw('deleted_at is null')
            ->orderBy('id', 'asc')
            ->get();


        $connection = \DB::connection('eglobalt_pro');  // Todos los select's apuntan a replica, menos la de users porque da un error extraño: Undefined file: 7 ERROR: could not access file "$libdir/postgres_fdw": No such file or directory

        $information_schema_tables = $connection
            ->table('information_schema.tables')
            ->select(
                \DB::raw("table_schema || '.' || table_name as id"),
                \DB::raw("table_schema || '.' || table_name as description")
            )
            ->whereRaw("table_schema = any(array['public', 'ussd', 'commission', 'identidades', 'info', 'uml'])") // Solo los esquemas especificados, para no traer esquemas del motor.
            ->orderBy('table_schema', 'asc')
            ->get();

        //$data['lists']['information_schema_tables'] = json_encode($information_schema_tables);

        $data['combos'][0]['list'] = json_encode($information_schema_tables);

        if (isset($request['table_id'])) {
            //\Log::debug('TABLA', [$request['table_id']]);

            $table_id = $request['table_id'];

            $information_schema_columns = $connection->select("
                    select *
                    from info.view_table_meta_data
                    where schema_and_table = '$table_id'
                ");

            $data['lists']['information_schema_columns'] = $information_schema_columns;


            //\Log::debug('ids:');
            //\Log::info($data['lists']['information_schema_columns']);

            $primary_keys_count = $connection
                ->select("
                        select count(*) 
                        from info.view_table_meta_data
                        where schema_and_table = '$table_id'
                        and key_type = 'PRIMARY KEY' -- Si hay más de dos primary key quiere decir que la clave es compuesta y hay que ingresar en el formulario
                    ");

            $primary_keys_count = $primary_keys_count[0]->count;

            //Log::debug('primary_keys_count:', [$primary_keys_count]);

            $data['primary_keys_count'] = $primary_keys_count;

            $columns_filters = [];

            $i = 1;

            foreach ($information_schema_columns as $item) {

                $key_type = $item->key_type;
                $column_fk = $item->column_name;
                $table_fk = $item->reference_table;
                $search_value_fk = $item->search_value;
                $item->list = [];

                if ($key_type == 'FOREIGN KEY') {

                    /**
                     * Si la columna es FK traemos toda su información para evaluar si puede ser un combo.
                     */
                    $information_schema_columns_fk = $connection
                        ->select("
                                select * 
                                from info.view_table_meta_data
                                where schema_and_table = '$table_fk'
                            ");

                    $columns_select_view = [];
                    $columns_where = [];
                    $column_pk = '';
                    $column_name = false;
                    $column_description = false;

                    foreach ($information_schema_columns_fk as $item_fk) {

                        $key_type_fk = $item_fk->key_type;
                        $data_type_fk = $item_fk->data_type;
                        $column_name_fk = $item_fk->column_name;

                        if (($data_type_fk == 'character' or
                            $data_type_fk == 'character varying' or
                            $data_type_fk == 'text')) {

                            array_push($columns_select_view, $column_name_fk); //select de todas las columnas que sean texto para mostrar en el combo jeje

                            if ($column_name_fk == 'name') {
                                $column_name = true;
                            } else if ($column_name_fk == 'description') {
                                $column_description = true;
                            }
                        } else if ($data_type_fk == 'boolean' and $column_name_fk == 'status') {

                            array_push($columns_where, "$column_name_fk = true");
                        } else if ($data_type_fk == 'timestamp without time zone' and $column_name_fk == 'deleted_at') {

                            array_push($columns_where, "$column_name_fk is null");
                        }

                        // Verificamos si la tabla fk tiene un primary key para crear el select del combo
                        if ($key_type_fk == 'PRIMARY KEY') {
                            $column_pk = $column_name_fk;
                        }
                    }

                    if ($column_name and $column_description) {
                        $columns_select_view = [];
                        array_push($columns_select_view, 'name');
                        array_push($columns_select_view, 'description');
                    } else if ($column_name) {
                        $columns_select_view = [];
                        array_push($columns_select_view, 'name');
                    } else if ($column_description) {
                        $columns_select_view = [];
                        array_push($columns_select_view, 'description');
                    }


                    $records_fk = [];

                    /**
                     * Preguntamos si la tabla FK tiene columnas de texto y un primary key
                     */
                    if (count($columns_select_view) > 0 and $column_pk !== '') {

                        $columns_texts = implode(" || ' - ' || ", $columns_select_view);

                        $columns_to_select = "$column_pk as id, ('#' || $column_pk || '. ' || $columns_texts) as description";

                        if (count($columns_where) > 0) {
                            $columns_where = 'where ' . implode(" and ", $columns_where);
                        } else {
                            $columns_where = '';
                        }

                        $query = "
                                select $columns_to_select
                                from $table_fk
                                $columns_where
                                order by $columns_texts ASC
                            ";

                        //\Log::info("QUERY: $query");

                        //Trae los registros para el combo
                        $records_fk = $connection->select($query);

                        $reference_rows_count = count($records_fk);

                        $item->reference_rows_count = $reference_rows_count;

                        //\Log::info("reference_rows_count: $reference_rows_count");

                        if ($reference_rows_count > 0) {

                            $item->list = json_decode(json_encode($records_fk), true);
                            $item->combo = true;
                        }
                    }
                }

                if ($column_fk == 'created_by' or $column_fk == 'updated_by' or $column_fk == 'deleted_by' or $column_fk == 'user_id') {

                    $item->key_type = 'FOREIGN KEY';
                    $item->data_type = 'text';
                    $item->reference_table = 'public.users';
                    $item->reference_rows_count = count($users);
                    $item->list = json_encode($users);


                    $item->combo = true;
                }
            }

            //\Log::debug('COMBOS:');
            //\Log::debug($data['combos']);

            //\Log::debug('information_schema_columns:');
            //\Log::debug($data['lists']['information_schema_columns']);

            $count = $connection->table($request['table_id'])
                ->select(
                    \DB::raw('count(*) as count')
                );

            $records = $connection->table($request['table_id']);


            //Campo 'ids' que trae los valores de los campos.
            if (isset($request['ids'])) {

                \Log::debug('IDS:', [count($request['ids'])]);
                \Log::debug(json_decode($request['ids']));

                $ids = json_decode($request['ids']);

                $data['lists']['information_schema_columns'] = $ids;

                foreach ($ids as $item_search) {

                    $column_name = $item_search->column_name;
                    $data_type = $item_search->data_type;
                    $search_value = $item_search->search_value;
                    $where_raw = '';

                    if ($search_value !== null and $search_value !== '') {

                        if ($data_type == 'smallint' or $data_type == 'integer' or $data_type == 'numeric' or $data_type == 'bigint' or $data_type == 'double precision') {

                            $search_value = intval($search_value);
                            $where_raw = "$column_name = $search_value";
                        } else if ($data_type == 'numeric' or $data_type == 'double precision') {

                            $search_value = floatval($search_value);
                            $where_raw = "$column_name = $search_value"; //no tiene comillas

                        } elseif ($data_type == 'character' or $data_type == 'character varying' or $data_type == 'text' or $data_type == 'date' or $data_type == 'ARRAY' or $data_type == 'json') {

                            $where_raw = "$column_name = '$search_value'"; //tiene comillas

                        } elseif ($data_type == 'boolean') {

                            $search_value = ($search_value == 1) ? 'true' : 'false';
                            $where_raw = "$column_name = $search_value";
                        } elseif ($data_type == 'timestamp without time zone') {

                            $aux  = explode(' - ', str_replace('/', '-', $search_value));
                            $from = date('Y-m-d H:i:s', strtotime($aux[0]));
                            $to = date('Y-m-d H:i:s', strtotime($aux[1]));
                            $where_raw = "$column_name between '{$from}' and '{$to}'"; //convertir fecha

                        }

                        $count = $count->whereRaw($where_raw);
                        $records = $records->whereRaw($where_raw);

                        \Log::debug('item:', [$column_name, $data_type, $search_value]);
                    }
                }
            }

            $count = $count->get();

            $count = $count[0]->count;

            $limit = 1000;

            if ($count >= $limit) {
                $records = $records->take($limit);
            }

            //\Log::debug('QUERY GENERADO', [$records->toSql()]);

            $data['query_generated'] = $records->toSql();

            $records = $records->get();

            $data['lists']['records'] = json_decode(json_encode($records), true);

            //\Log::debug('DATA:', [$data['lists']['records']]);

            $data['combos'][0]['value'] = $table_id;
        } else {
            //\Log::debug('no vino nada');
        }

        $data['lists']['information_schema_tables'] = json_encode($information_schema_tables);
        //$data['lists']['columns'] = null;

        $response = view('info.table', compact('data'));

        return $response;
    }

    /**
     * Agregar o Editar registro
     */
    public function save_($request)
    {
        $response = [
            'error' => false,
            'message' => ''
        ];

        try {
            //\Log::debug("\Tabla:\n", [$request['table_id']]);
            \Log::debug("\nColumnas:\n", [$request['ids']]);

            $columns_insert = [];

            for ($i = 0; $i < count($request['ids']); $i++) {
                $item = $request[$i];

                \Log::debug("ITEM:", [$item]);

                $key_type = $item['key_type'];
                $new_value = $item['new_value'];
                $column_name = $item['column_name'];

                if ($key_type !== 'PRIMARY KEY' and $new_value !== '') {
                    $columns_insert[$column_name] = "$new_value";
                } else {
                    \Log::debug("UN ITEM ES PK");
                }
            }

            \Log::debug("\ncolumns_insert:\n", [$columns_insert]);

            die();

            //\DB::table($request['table_id'])->insert($columns_insert);


        } catch (\Exception $e) {

            $exception = $e->getMessage();

            $error_detail = [
                'exception' => $exception,
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];

            \Log::debug("\nOcurrión un error al modificar el registro:\n", [$error_detail]);

            $response['message'] = "Error en la acción.\nExcepción: $exception";
        }

        if ($response['message'] !== '') {
            $response['error'] = true;
        } else {
            $response['message'] = 'Registro agregado correctamente.';
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
