<?php

/**
 * User: avisconte
 * Date: 25/08/2022
 * Time: 09:25 am
 */

namespace App\Services\Info;

use Carbon\Carbon;

class TableServices
{

    public function __construct()
    {
        $this->user = \Sentinel::getUser();
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

        $message = '';

        $tables = [];

        $table_querys_actives = [];
        $table_querys_actives_headers = [];

        $table_meta_data = [];
        $table_meta_data_headers = [];

        $table_uses = [];
        $table_uses_headers = [];

        $table_form = [];
        $table_primary_key_name = null;

        $latest_changes = [];
        $latest_changes_headers = [];

        $get_info = true;
        $error_detail = [];

        try {

            /**
             * Lista de tablas con sus esquemas
             */
            $tables = \DB::select("
                select 
                    tables.table_schema || '.' || tables.table_name as id,
                    tables.table_schema || '.' || tables.table_name as description
                from information_schema.tables
                where tables.table_schema in ('public', 'commission', 'identidades', 'pattern', 'uml', 'ussd')
                and tables.table_name not ilike '%view_%'
                order by tables.table_schema;
            ");

            $tables = json_decode(json_encode($tables), true);

            /**
             * Traer los meta datos de la tabla filtrado por el esquema y nombre de la tabla
             */
            if (isset($request['table_id'])) {

                //GRANT SELECT ON ALL TABLES IN SCHEMA public TO test_prod;
                //GRANT SELECT ON ALL SEQUENCES IN SCHEMA public TO test_prod;

                $table_id = $request['table_id'];

                //--------------------------------------------------------------------------------

                $table_id_aux = explode('.', $table_id);
                $schema_aux = $table_id_aux[0];
                $table_aux = $table_id_aux[1];

                $table_querys_actives = \DB::table('view_pg_stat_activity')
                    ->whereRaw("\"Consulta\" ilike '%$table_aux%'")
                    ->whereRaw("\"Consulta\" not ilike '%view_pg_stat_activity%'")
                    ->get();

                $table_querys_actives = json_decode(json_encode($table_querys_actives), true);

                \Log::info('table_querys_actives:', [$table_querys_actives]);

                if (count($table_querys_actives) > 0) {
                    $table_querys_actives_headers = array_keys($table_querys_actives[0]);
                }

                //--------------------------------------------------------------------------------

                $table_meta_data = "
                    select
                        distinct 
                        
                        ci.column_name as \"Columna\",
                        
                        (ci.data_type || ', ' || ci.udt_name) || 
                            
                        coalesce( 
                            case when ci.numeric_precision is null 
                            then (' ( ' || ci.character_maximum_length) || ' )' 
                            else (' ( ' || ci.numeric_precision) || ' )' end, 
                        '')

                        as \"Tipo, Sub-tipo ( Tamaño )\",
                    
                        case
                            when ci.is_nullable = 'YES' then ' SI '
                            else ' NO '
                        end as \"Acepta Nulos\",
                        
                        string_agg(tc.constraint_type, ', ') as \"Restricción\",
                        
                        case
                            when kcu.table_name is null then ''
                            else kcu.table_schema || '.' || kcu.table_name || ' ( ' || kcu.column_name || ' )'
                        end as \"Referencia Foránea: tabla ( Columna )\",
                        
                        ci.column_default as \"Valor Inicial\"
                    from
                        information_schema.columns ci
                    left join information_schema.key_column_usage kcu on kcu.table_catalog = ci.table_catalog and kcu.table_schema = ci.table_schema and kcu.table_name = ci.table_name and kcu.column_name = ci.column_name
                    left join information_schema.table_constraints tc on tc.constraint_catalog = kcu.constraint_catalog and tc.constraint_schema = kcu.constraint_schema and tc.constraint_name = kcu.constraint_name
                    where
                        (ci.table_schema || '.' || ci.table_name) = '$table_id'
                    group by
                        ci.ordinal_position, ci.column_name, ci.data_type, ci.udt_name, ci.is_nullable,
                        ci.numeric_precision, ci.character_maximum_length, ci.column_default,
                        kcu.table_name, kcu.table_schema, kcu.column_name
                ";

                $table_meta_data = \DB::select($table_meta_data);
                $table_meta_data = json_decode(json_encode($table_meta_data), true);

                if (count($table_meta_data) > 0) {
                    $table_meta_data_headers = array_keys($table_meta_data[0]);
                }

                //--------------------------------------------------------------------------------

                $table_uses = \DB::table('view_pg_stat_user_tables')
                    ->whereRaw("schema_and_table = '$table_id'")
                    ->get();

                $table_uses = json_decode(json_encode($table_uses), true);
                $table_uses_aux = [];

                if (isset($table_uses[0])) {
                    foreach ($table_uses[0] as $key => $value) {

                        $title = '';
                        $description = '';

                        if ($key == 'seq_scan') {

                            $title = 'Lecturas secuenciales';

                            $description = "
                                Es el número de veces que se ha realizado una lectura secuencial completa de la tabla. 
                                Si este número es alto, es posible que haya problemas de rendimiento debido a la necesidad 
                                de leer grandes cantidades de datos de la tabla.
                            ";
                        } else if ($key == 'seq_tup_read') {

                            $title = 'Número de veces que se hizo lectura completa de la tabla';

                            $description = "
                                Es el número total de filas leídas durante las operaciones de lectura secuencial completa de la tabla. 
                                Este número puede ayudar a identificar cuántas filas se están leyendo de la tabla durante las operaciones de lectura secuencial completa, 
                                lo que puede ser útil para identificar problemas de rendimiento.
                            ";
                        } else if ($key == 'idx_scan') {

                            $title = 'Número de búsqueda de indice';

                            $description = "
                                Es el número de veces que se ha realizado una búsqueda índice completo de la tabla. 
                                Si este número es alto, es posible que el rendimiento se vea afectado debido a la necesidad 
                                de buscar en el índice de la tabla para recuperar datos.
                            ";
                        } else if ($key == 'idx_tup_fetch') {

                            $title = 'Número de veces que se hizo lectura sobre la tabla con indice';

                            $description = "
                                Es el número total de filas leídas durante las operaciones de búsqueda índice completo de la tabla. 
                                Este número puede ayudar a identificar cuántas filas se están leyendo de la tabla durante las operaciones 
                                de búsqueda índice completo.
                            ";
                        } else if ($key == 'n_tup_ins') {

                            $title = 'Número de filas insertadas';

                            $description = "
                                Es el número de filas que se han insertado en la tabla. 
                                Este número puede ayudar a identificar la frecuencia y cantidad de inserciones en la tabla.
                            ";
                        } else if ($key == 'n_tup_upd') {

                            $title = 'Número de filas actualizadas';

                            $description = "
                                Es el número de filas que se han actualizado en la tabla. 
                                Este número puede ayudar a identificar la frecuencia y cantidad de actualizaciones en la tabla.
                            ";
                        } else if ($key == 'n_tup_del') {

                            $title = 'Número de filas eliminadas';

                            $description = "
                                Es el número de filas que se han eliminado de la tabla. 
                                Este número puede ayudar a identificar la frecuencia y cantidad de eliminaciones en la tabla.
                            ";
                        } else if ($key == 'n_live_tup') {

                            $title = 'Número de actual de filas';

                            $description = "
                                Es el número actual de filas en la tabla. 
                                Este número puede ser útil para identificar la cantidad actual de datos almacenados en la tabla.
                            ";
                        } else if ($key == 'n_dead_tup') {

                            $title = 'Número de filas marcadas como eliminadas';

                            $description = "
                                Es el número actual de filas marcadas como eliminadas en la tabla. 
                                Este número puede ser útil para identificar la cantidad de espacio en disco que se está utilizando para almacenar datos eliminados.
                            ";
                        } else if ($key == 'n_mod_since_analyze') {

                            $title = 'Número de modificaciones desde el último analizis';

                            $description = "
                                Es el número de modificaciones a la tabla desde la última vez que se ejecutó la estadística de análisis. 
                                Si este número es alto, es posible que sea necesario ejecutar el análisis de estadística de la tabla para actualizar las estimaciones de estadísticas de la tabla.
                            ";
                        } else if ($key == 'last_vacuum') {

                            $title = 'Fecha y Hora de último vacío completo de la tabla';

                            $description = "
                                Es la fecha y hora en que se realizó la última operación de vacío completo en la tabla. 
                                Esta información puede ser útil para identificar cuándo se realizó la última limpieza de la tabla.
                            ";
                        } else if ($key == 'last_analyze') {

                            $title = 'Fecha y Hora de operación de análisis';

                            $description = "
                                Es la fecha y hora de la última operación de análisis de estadística en la tabla.
                                Esta información puede ser útil para identificar cuándo se realizó el análisis de estadística más reciente en la tabla.
                            ";
                        }

                        if ($key !== 'schema_and_table') {
                            $item = [
                                'title' => $title,
                                'description' => $description,
                                'value' => $value
                            ];

                            array_push($table_uses_aux, $item);
                        }
                    }
                }

                $table_uses = $table_uses_aux;

                /*\Log::info('table_uses:', [$table_uses]);

                if (count($table_uses) > 0) {
                    $table_uses_headers = array_keys($table_uses[0]);
                }*/


                /**
                 * select para el formulario de update:
                 */
                $query = "
                    select 
                        distinct 
                        --(ci.table_schema || '.' || ci.table_name) as schema_and_table,
                        ci.ordinal_position,
                        ci.column_name,
                        ci.data_type,
                        (case when ci.numeric_precision is null then ci.character_maximum_length else ci.numeric_precision end) as precision,
                        string_agg(tc.constraint_type, ', ') as constraint_type,
                        ci.is_nullable,
                        --ci.column_default,
                        null as old_value,
                        null as new_value,
                        false as update_value
                    from information_schema.columns ci
                    left join information_schema.key_column_usage kcu on kcu.table_catalog = ci.table_catalog and kcu.table_schema = ci.table_schema and kcu.table_name = ci.table_name and kcu.column_name = ci.column_name
                    left join information_schema.table_constraints tc on tc.constraint_catalog = kcu.constraint_catalog and tc.constraint_schema = kcu.constraint_schema and tc.constraint_name = kcu.constraint_name
                    where (ci.table_schema || '.' || ci.table_name) = '$table_id'
                    group by 
                        ci.ordinal_position,
                        ci.column_name,
                        ci.data_type,
                        ci.is_nullable,
                        ci.numeric_precision,
                        ci.character_maximum_length
                    order by ci.ordinal_position asc
                ";

                $table_form = \DB::select($query);

                $table_form = json_decode(json_encode($table_form), true);

                \Log::info('table_form:', [$table_form]);

                foreach ($table_form as $table_form_item) {

                    $column_name = $table_form_item['column_name'];
                    $data_type = $table_form_item['data_type'];
                    $precision = $table_form_item['precision'];
                    $constraint_type = $table_form_item['constraint_type'];
                    //$column_default = $table_form_item['column_default'];

                    if (strstr($constraint_type, 'PRIMARY KEY')) {
                        $table_primary_key_name = $column_name;
                        break;
                    }
                }


                /**
                 * select para el formulario de update:
                 */
                $latest_changes = "
                    select
                        atd.id as \"ID\",
                        u.description as \"Usuario\",
                        to_char(atd.created_at, 'DD/MM/YYYY HH24:MI:SS') as \"Fecha-Hora\",
                        atd.commentary as \"Comentario\",
                        at.description as \"Tabla\",
                        atd.pk_id as \"ID-Modificado\",
                        atd.data as \"Datos\"
                    from audit.tables as at
                    join audit.tables_data atd on at.id = atd.table_id 
                    join users as u on u.id = atd.created_by 
                    where at.description = '$table_id'
                    order by atd.id desc 
                    limit 20;
                ";

                $latest_changes = \DB::select($latest_changes);
                $latest_changes = json_decode(json_encode($latest_changes), true);

                if (count($latest_changes) > 0) {
                    $latest_changes_headers = array_keys($latest_changes[0]);
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

            \Log::error("Error, Detalles: " . json_encode($error_detail));

            $message = 'Ocurrió un error: (' . $e->getMessage() . ')';
        }

        $user = \Sentinel::getUser();
        $user_id = $user->id;

        $data = [
            'lists' => [
                'tables' => $tables,

                'table_querys_actives' => $table_querys_actives,
                'table_querys_actives_headers' => $table_querys_actives_headers,

                'table_meta_data' => $table_meta_data,
                'table_meta_data_headers' => $table_meta_data_headers,

                'table_uses' => $table_uses,
                'table_uses_headers' => $table_uses_headers,

                'table_form' => $table_form,

                'latest_changes' => $latest_changes,
                'latest_changes_headers' => $latest_changes_headers
            ],
            'inputs' => [
                'table_id' => isset($request['table_id']) ? $request['table_id'] : null,
                'table_primary_key_name' => $table_primary_key_name,
                'user_id' => $user_id
            ],
            'message' => $message,
            'error_detail' => $error_detail
        ];

        return view('info.table', compact('data'));
    }

    /**
     * Función guardar
     */
    public function save($request)
    {
    }

    /**
     * Función para buscar por id en la tabla
     */
    public function search_by_id($request)
    {

        if (!$this->user->hasAccess('info_table')) {
            return [];
        }

        $table_id = $request['table_id'];
        $table_primary_key_name = $request['table_primary_key_name'];
        $primary_key_search_id = $request['primary_key_search_id'];

        $select = \DB::table($table_id)
            ->whereRaw("$table_primary_key_name = $primary_key_search_id")
            ->get();

        return $select;
    }

    /**
     * Función para buscar por id en la tabla
     */
    public function update($request)
    {

        $response = [
            'error' => false,
            'message' => 'Actualización correcta'
        ];

        if (!$this->user->hasAccess('info_table')) {
            return [
                'error' => true,
                'message' => 'No tienes permiso para realizar esta operación.'
            ];
        }

        try {

            $table_id = $request['table_id'];
            $table_primary_key_name = $request['table_primary_key_name'];
            $primary_key_search_id = $request['primary_key_search_id'];
            $table_form = $request['table_form'];

            \Log::info("table_form obtenido:", [$table_form]);

            $commentary = $request['commentary'];
            $user_id = $request['user_id'];

            $columns_update = [];
            $table_form_update = false;

            for ($i = 0; $i < count($table_form); $i++) {
                $item = $table_form[$i];

                $constraint_type = $item['constraint_type'];
                $column_name = $item['column_name'];
                $new_value = $item['new_value'];
                $update_value = $item['update_value'];
                $is_nullable = $item['is_nullable'];

                if (strstr($constraint_type, 'PRIMARY KEY') == false and $update_value == 'true') {

                    if ($is_nullable == 'YES' and $new_value == '') {
                        $new_value = null;
                    }

                    $columns_update[$column_name] = $new_value;
                    $table_form_update = true;
                }

                if ($column_name == 'updated_at') {
                    $columns_update['updated_at'] = Carbon::now();
                }
            }

            if ($table_form_update) {

                \DB::beginTransaction();

                //Buscamos el nombre de la tabla a modificar.

                /*$table_name = \DB::table('information_schema.tables')
                    ->select(
                        'table_name'
                    )
                    ->whereRaw("(table_schema || '.' || table_name) = '$table_id'")
                    ->get();

                $table_name = $table_name[0]->table_name;

                //Buscamos el nombre de la tabla si existe en el esquema de auditoria

                $table_audit_exists = \DB::table('information_schema.tables')
                    ->select(
                        'table_name'
                    )
                    ->whereRaw("(table_schema || '.' || table_name) = 'audit.$table_name'")
                    ->get();

                \Log::info("table_audit_exists:", [$table_audit_exists]);

                //Si no existe la tabla en auditoría la creamos con las columnas de la tabla original.

                if (count($table_audit_exists) <= 0) {

                    $query = "
                        create table audit.$table_name as
                            select  
                                now() as audit_created_at, 
                                -1 as audit_created_by, 
                                '' as audit_commentary, 
                                * 
                            from $table_id 
                            limit 0
                    ";

                    \DB::select($query);

                    \Log::info("Tabla de auditoria creada: $query");
                }

                //Obtenemos todas las columnas que le falta a tabla de auditoría

                $select_columns_not_in = "
                    select 
                        'alter table audit.$table_name add column ' || column_name || ' ' || data_type || ';' as description
                    from information_schema.columns
                    where (table_schema || '.' || table_name) = '$table_id' and column_name not in (
                        select column_name
                        from information_schema.columns
                        where (table_schema || '.' || table_name) = 'audit.$table_name'
                    );
                ";

                $select_columns_not_in = \DB::select($select_columns_not_in);

                if (count($select_columns_not_in) > 0) {

                    foreach ($select_columns_not_in as $item) {

                        //Agregando las columnas que le falta a la tabla de auditoría:
                        $description = $item->description;
                        \DB::select($description);
                    }
                }*/

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

                //Obtenemos los datos de la tabla y el registro que se va actualizar:

                $select_data = \DB::table($table_id)
                    ->where($table_primary_key_name, $primary_key_search_id)
                    ->get();

                $select_data = $select_data[0];
                $select_data = json_encode(json_decode(json_encode($select_data), true));

                \Log::info("select_data: $select_data");

                $tables_data_insert = [
                    'created_at' => Carbon::now(),
                    'created_by' => $user_id,
                    'commentary' => $commentary,
                    'table_id' => $insert_table_id,
                    'pk_id' => $primary_key_search_id,
                    'data' => $select_data
                ];

                //Hacemos el insert en la tabla de auditoría:

                \DB::table('audit.tables_data')
                    ->insert($tables_data_insert);

                //Hacemos update en la tabla de 

                \DB::table($table_id)
                    ->where($table_primary_key_name, $primary_key_search_id)
                    ->update($columns_update);

                \DB::commit();
            }
        } catch (\Exception $e) {

            \DB::rollback();

            $error_detail = [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];

            \Log::error("Error, Detalles: " . json_encode($error_detail));

            $response['error'] = true;
            $response['message'] = 'Ocurrió un error: (' . $e->getMessage() . ')';
        }


        return $response;
    }
}
