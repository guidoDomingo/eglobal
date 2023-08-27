<?php

/**
 * User: avisconte
 * Date: 26/02/2021
 * Time: 16:26 pm
 */

namespace App\Services\Conciliators;

use App\Exports\ExcelExport;
use Excel;
use Carbon\Carbon;
use DateTime;

class TransactionConciliatorServices
{

    /**
     * Recibe el objeto exception y retorna una lista personalizada
     *
     * @return $error_detail
     */
    private function custom_error($e, $function)
    {
        $file = $e->getFile();
        $line = $e->getLine();
        $message = $e->getMessage();

        $error_detail = [
            'exception_message' => $message,
            'file' => $file,
            'class' => __CLASS__,
            'function' => $function,
            'line' => $line
        ];

        \Log::error('Ocurrió un error. Detalles:');
        \Log::error($error_detail);

        return $error_detail;
    }

    public function read_file($file)
    {
        $sheets = [];

        //\Log::info("Leyendo excel");

        /*Excel::load($file, function ($reader) {
            //$reader->formatDates(true);

            $excel = $reader->getExcel();
            $reader->formatDates(true, 'Y-m-d');
            $reader->setDateFormat('Y-m-d');

            foreach ($excel->getAllSheets() as $sheet) {
                foreach ($sheet->getRowIterator() as $index => $row) {
                    foreach ($row->getCellIterator() as $cell) {
                        $value = $cell->getValue();

                        //$value = $cell->setValueExplicit($value, PHPExcel_Cell_DataType::TYPE_TEXT);

                        \Log::info('CELL:');
                        \Log::info($cell);
                    }
                }
            }
        });*/

        //\Log::info("excel leido");
        //die();

        Excel::load($file, function ($reader) use (&$sheets) {
            try {
                $sheet_list = $reader->getExcel()->getSheetNames();
                $sheet_list_size = count($sheet_list);

                for ($j = 0; $j < $sheet_list_size; $j++) {
                    $sheet = $reader->getExcel()->getSheet($j);
                    $highest_column = $sheet->getHighestColumn();
                    $list = [];

                    for ($k = 1; $k <= $sheet->getHighestRow(); $k++) {
                        $row_data = $sheet->rangeToArray("A$k:$highest_column$k", null, true, false);
                        array_push($list, $row_data[0]);
                    }

                    array_push($sheets, $list);
                }
            } catch (\Exception $e) {
                $this->custom_error($e, __FUNCTION__);
            }
        });

        //\Log::info($sheets);
        //\Log::info("Excel cargado.");
        //die();

        return $sheets;
    }

    public function get_patterns()
    {

        $multi_list = [];

        $records_list = \DB::table('pattern.description as d')
            ->select(
                'd.id',
                'd.description as parent',
                'd2.description as child',
                'dv.id as description_version_id',
                'd.reference_id as reference_id_parent',
                'd2.reference_id as reference_id_child'
            )
            ->join('pattern.description as d2', 'd.id', '=', 'd2.description_id')
            ->join('pattern.description_version as dv', 'd2.id', '=', 'dv.description_id')
            ->join('pattern.version as v', 'v.id', '=', 'dv.version_id')
            ->where('d.description_type_id', 1) //Tipo 1: Proveedores, 2: Bancos
            ->where('dv.status', true)
            ->where('d.status', true)
            ->where('v.status', true)
            ->orderBy('d2.id', 'asc');

        //\Log::info($records_list->toSql());

        $records_list = $records_list->get();

        $records_list = array_map(function ($value) {
            return (array) $value;
        }, $records_list);

        //\Log::info($records_list);
        //die();

        for ($i = 0; $i < count($records_list); $i++) {
            $id = $records_list[$i]['id'];
            $parent = $records_list[$i]['parent'];
            $child = $records_list[$i]['child'];
            $description_version_id = $records_list[$i]['description_version_id'];
            $reference_id_parent = $records_list[$i]['reference_id_parent'];
            $reference_id_child = $records_list[$i]['reference_id_child'];

            $description_column_list = \DB::table('pattern.description_column as dc')
                ->select(
                    'ct.description as column_type',
                    'cst.description as column_sub_type',
                    'cstf.description as column_sub_type_format',
                    'cc.description as column_compare',
                    'cc.key',
                    \DB::raw("lower(dc.description) as description"),
                    \DB::raw("cc.alias || '.' || cc.description as alias"),
                    \DB::raw("null as index"),
                    \DB::raw("false as correct")
                )
                ->join('pattern.column_type as ct', 'ct.id', '=', 'dc.column_type_id')
                ->join('pattern.column_sub_type as cst', 'cst.id', '=', 'dc.column_sub_type_id')
                ->leftjoin('pattern.column_sub_type_format as cstf', 'cstf.id', '=', 'dc.column_sub_type_format_id')
                ->leftjoin('pattern.column_compare as cc', 'cc.id', '=', 'dc.column_compare_id')
                ->where('dc.description_version_id', $description_version_id)
                ->where('dc.status', true)
                ->orderBy('dc.id', 'asc');

            //\Log::info($description_column_list->toSql());

            $description_column_list = $description_column_list->get();

            $description_column_list = array_map(function ($value) {
                return (array) $value;
            }, $description_column_list);

            $description_column = [
                'reference_id_child' => $reference_id_child,
                'parent' => $parent,
                'labels' => [],
                'columns' => [],
                'files' => []
            ];

            for ($j = 0; $j < count($description_column_list); $j++) {
                array_push(
                    $description_column[$description_column_list[$j]['column_type']],
                    $description_column_list[$j]
                );
            }

            if (!isset($multi_list[$parent])) {
                $multi_list[$parent] = [
                    'reference_id_parent' => $reference_id_parent,
                    'column_compare' => null,
                    'data' => [],
                    'childs' => [],
                    'files' => []
                ];
            }

            $multi_list[$parent]['childs'][$child] = $description_column;
        }

        //\Log::info("Patrones cargados.");

        //\Log::info('multi_list:');
        //\Log::info($multi_list);
        //die();

        return $multi_list;
    }

    /**
     * Esta función sirve para entralazar la información de los archivos y la base de datos.
     */

    public function get_record_validations($parameters, $user)
    {

        ini_set('max_execution_time', 0);
        ini_set('client_max_body_size', '20M');
        ini_set('max_input_vars', 10000);
        ini_set('upload_max_filesize', '20M');
        ini_set('post_max_size', '20M');
        ini_set('memory_limit', '-1');
        set_time_limit(3600);

        $multi_list = [];
        $providers = [];
        $extensions = ['xls', 'xlsx', 'csv'];
        $array_keys_multi_list = [];
        $list_coincidences = [];
        $maximum_rows = 5;

        try {
            //Hace que no funcionen los logs.
            //\Log::getMonolog()->setHandlers([]);

            $timestamp = $parameters['timestamp'];
            $files = $parameters['files'];
            //$consistent = $parameters['consistent'];
            $record_limit = $parameters['record_limit'];

            $multi_list = $this->get_patterns();

            foreach ($multi_list as $index => $value) {
                foreach ($value['childs'] as $key => $value) {
                    $list_coincidences[$key] = 0;
                    array_push($array_keys_multi_list, $key);
                }
            }

            $providers = implode(',', array_keys($multi_list));

            //---------------------------------------------------------------------------

            $aux = explode(' - ', str_replace('/', '-', $timestamp));
            $from = date('Y-m-d H:i:s', strtotime($aux[0]));
            $to = date('Y-m-d H:i:s', strtotime($aux[1]));
            $year = date('Y', strtotime($aux[1]));

            for ($i = 0; $i < count(collect($files)); $i++) {
                $file = $files[$i];

                if ($file !== null) {
                    $client_original_name = $file->getClientOriginalName();
                    $client_original_extension = $file->getClientOriginalExtension();
                    $file_size = $file->getSize();
                    $file_type = $file->getMimeType();
                    $file_real_path = $file->getRealPath();
                    $file_size_ = ($file_size < 1000000) ? floor($file_size / 1000) . ' KB' : floor($file_size / 1000000) . ' MB';
                    $valid = false;

                    if (in_array($client_original_extension, $extensions) and is_readable($file)) {

                        $sheets = $this->read_file($file);

                        //\Log::info('excel leido');

                        $sheet_and_coincidences = [];

                        //Recorre las hojas del excel para hacer algunas cosas.
                        for ($j = 0; $j < count($sheets); $j++) {
                            //Asigna cero coincidencias a todas las descripciones
                            foreach ($array_keys_multi_list as $index => $key) {
                                $list_coincidences[$key] = 0;
                            }

                            $sheet_item = [
                                'list_coincidences' => null,
                                'list' => null
                            ];

                            for ($k = 0; $k < count($sheets[$j]); $k++) {
                                if ($k < $maximum_rows) {
                                    for ($l = 0; $l < count($sheets[$j][$k]); $l++) {
                                        $cell_value = strtolower(str_replace("'", '', rtrim(ltrim($sheets[$j][$k][$l]))));
                                        $sheets[$j][$k][$l] = $cell_value;
                                        if ($cell_value !== null and $cell_value !== '' and !is_null($cell_value)) {
                                            foreach ($multi_list as $key => $value) { //Lista que contiene Eglobal y Infonet
                                                foreach ($value['childs'] as $sub_key => $sub_value) { //Lista que contine los hijos
                                                    foreach ($value['childs'][$sub_key]['labels'] as $item) { //Lista que contine las etiquetas de cada hijo
                                                        //$d = $item['description'];
                                                        //\Log::info("cell: $cell_value == $d");

                                                        if ($cell_value == $item['description']) {
                                                            $list_coincidences[$sub_key]++;
                                                        }
                                                    }
                                                }
                                            }
                                        }
                                    }
                                } else {
                                    break;
                                }
                            }

                            $sheet_item['list_coincidences'] = $list_coincidences;
                            $sheet_item['list'] = $sheets[$j];
                            array_push($sheet_and_coincidences, $sheet_item);
                        }

                        //\Log::info('sheets leidos');
                        //\Log::info($sheet_and_coincidences);

                        $sheet_item_max = [];

                        if (count($sheet_and_coincidences) > 0) {
                            $sheet_item_max = $sheet_and_coincidences[0];
                        }

                        //Recorre para saber que sheet tiene más coincidencias
                        for ($j = 0; $j < count($sheet_and_coincidences); $j++) {
                            $sheet_item = $sheet_and_coincidences[$j];
                            if (array_sum($sheet_item['list_coincidences']) > array_sum($sheet_item_max['list_coincidences'])) {
                                $sheet_item_max = $sheet_item;
                            }
                        }

                        $coincidences = 0;

                        if (count($sheet_item_max) > 0) {
                            if (count($sheet_item_max['list_coincidences']) > 0) {
                                $coincidences = max($sheet_item_max['list_coincidences']);
                            }
                        }

                        $parent = 'Desconocido';
                        $key = 'Desconocido';
                        $reference_id_parent = null;
                        $reference_id_child = null;


                        if ($coincidences > 0) {
                            //Recorre para ver que child tiene más coincidencias
                            foreach ($multi_list as $index => $value) {
                                foreach ($value['childs'] as $sub_index => $sub_value) {
                                    if ($coincidences == $list_coincidences[$sub_index]) {
                                        $parent = $index;
                                        $key = $sub_index;
                                        $reference_id_parent = $multi_list[$parent]['reference_id_parent'];
                                        $reference_id_child = $multi_list[$parent]['childs'][$key]['reference_id_child'];
                                        $valid = true;
                                        break;
                                    }
                                }

                                if ($valid) {
                                    //\Log::info('Finalizado por valid');
                                    break;
                                }
                            }
                        }

                        //\Log::info('Documento detectado.');

                        $excel = $sheet_item_max['list'];

                        //$parent = 'Eglobal'; $reference_id_parent: service_source_id = 0 
                        //$key = '????'; $reference_id_child: service_id = ?

                        /*$filters = [
                            'from' => $from,
                            'to' => $to,
                            'providers' => $providers,
                            'record_limit' => $record_limit,
                            'parent' => $parent,
                            'child' => $key,
                            'reference_id_parent' => $reference_id_parent,
                            'reference_id_child' => $reference_id_child
                        ];*/

                        //\Log::info($filters);

                        $columns = [];

                        if (isset($multi_list[$parent]['childs'][$key]['columns'])) {
                            $columns = $multi_list[$parent]['childs'][$key]['columns'];
                        }

                        //\Log::info('transacciones obtenidas');
                        //\Log::info($columns);

                        //$columns_compare_detected = 0;
                        $columns_size = count($columns);

                        if ($columns_size > 0 and $valid == true) {

                            //\Log::info('columnas seteadas');

                            $index_list = [];
                            $columns_size = count($columns);
                            $columns_detected = 0;

                            //\Log::info($columns);

                            $columns_create = [
                                'exists boolean'
                            ];

                            $columns_where = [];
                            $columns_insert = [];
                            $columns_where_join_1 = "";
                            $columns_where_join_2 = "";

                            for ($j = 0; $j < count($excel); $j++) {
                                $row = $excel[$j];

                                if ($j < $maximum_rows) {
                                    for ($k = 0; $k < count($row); $k++) {
                                        $cell = $row[$k];
                                        for ($l = 0; $l < $columns_size; $l++) {

                                            if ($columns[$l]['description'] == $cell) {
                                                $columns[$l]['index'] = $k;
                                                $column_create = $columns[$l]['column_compare'];
                                                $column_sub_type_insert = $columns[$l]['column_sub_type'];
                                                $alias = $columns[$l]['alias'];

                                                $columns_insert[$columns[$l]['column_compare']] = null;

                                                if ($column_sub_type_insert == 'date') {
                                                    $column_sub_type_insert = 'text';
                                                    $alias = "to_char($alias, 'DD/MM/YYYY')";
                                                }

                                                array_push($index_list, $k);
                                                array_push($columns_create, "$column_create $column_sub_type_insert");

                                                if ($columns[$l]['key']) {
                                                    $columns_where_join_1 = "$alias";
                                                    $columns_where_join_2 = "tt.$column_create";
                                                } else {
                                                    $column_where = [
                                                        'columns_where_join_1' => $alias,
                                                        'columns_where_join_2' => "tt.$column_create"
                                                    ];
                                                    array_push($columns_where, $column_where);
                                                }

                                                $columns_detected++;
                                            }

                                            if ($columns_detected == $columns) {
                                                break;
                                            }
                                        }

                                        if ($columns_detected == $columns) {
                                            break;
                                        }
                                    }

                                    if ($columns_detected == $columns) {
                                        break;
                                    }
                                } else {
                                    break;
                                }
                            }

                            //\Log::info('Indices, condiciones cargadas.');

                            $columns_create = implode(', ', $columns_create);
                            //$columns_where = implode(' and ', $columns_where);

                            /* \Log::info('indices definidos');
                            \Log::info('columns_create: ');
                            \Log::info($columns_create);
                            \Log::info('columns_where: ');
                            \Log::info($columns_where); */
                            //die();

                            $excel_new = [];
                            $excel_new_insert = [];
                            $columns_size = count($columns);

                            for ($j = 0; $j < count($excel); $j++) {
                                $row = $excel[$j];
                                $item = [];

                                $columns_insert = [];

                                //\Log::info("Verificando fila n° $j");

                                for ($k = 0; $k < $columns_size; $k++) {
                                    $column_index = $columns[$k]['index'];
                                    $description = $columns[$k]['description'];
                                    $column_sub_type = $columns[$k]['column_sub_type'];
                                    $column_compare = $columns[$k]['column_compare'];

                                    for ($l = 0; $l < count($index_list); $l++) {
                                        $index = $index_list[$l];
                                        $cell = $row[$index];

                                        //\Log::info("$index == $column_index and $cell !== '' and $cell !== $description");

                                        if ($index == $column_index and $cell !== '' and $cell !== $description) {

                                            if ($column_sub_type == 'numeric') {
                                                $cell = preg_replace('/\D/', '', $cell);

                                                if ($cell !== '0') {
                                                    $cell = ltrim($cell, '0');
                                                }

                                                $cell = intval(trim($cell));
                                            } else if ($column_sub_type == 'date') {
                                                if (is_numeric($cell)) {
                                                    $cell = gmdate('d/m/Y', (intval($cell) - 25569) * 86400);
                                                } else {
                                                    //\Log::info("Fecha original: $cell");

                                                    $cell = str_replace("'", '', $cell);
                                                    $date = str_replace('/', '-', $cell);

                                                    $date_time = new DateTime($date);
                                                    $date = $date_time->format('d/m/Y');

                                                    $aux = explode('/', $date);

                                                    if ($aux[2] !== $year) {
                                                        $lasts_chars = substr($aux[2], -2);
                                                        $date = $lasts_chars . '/' . $aux[1] . '/' . $year;
                                                    }

                                                    $cell = $date;

                                                    //\Log::info("Fecha cambiada: $cell");
                                                }
                                            }

                                            $sub_item['value'] = $cell;
                                            $sub_item['correct'] = false;
                                            array_push($item, $sub_item);

                                            $columns_insert[$column_compare] = $cell;
                                        }
                                    }
                                }

                                if (count($item) > 0) {
                                    array_push($excel_new, $item);
                                }

                                if (count($columns_insert) > 0) {
                                    $columns_insert['exists'] = true;
                                    array_push($excel_new_insert, $columns_insert);
                                }

                                //\Log::info($columns_insert);
                            }

                            //--------------------------------------------------------------------------------
                            //Proceso nuevo de comparación:

                            //\Log::info('Proceso iniciado.');

                            $table_insert = "pattern.temporary_table_$user";

                            \DB::statement("drop table if exists $table_insert");

                            \DB::statement("
                                create table $table_insert(
                                    $columns_create
                                );
                            ");

                            //\Log::info('tabla creada.');

                            $list_insert = [];

                            //\Log::info('excel_new_insert:');
                            //\Log::info($excel_new_insert);
                            //die();

                            $insert_count = count($excel_new_insert);

                            for ($i = 0; $i < $insert_count; $i++) {
                                $insert = $excel_new_insert[$i];

                                array_push($list_insert, $insert);

                                $limit = (count($list_insert)) * $insert_count;

                                if ($limit >= 65535) {
                                    //\Log::info('list_insert:');
                                    //\Log::info($list_insert);
                                    //die();
                                    \DB::table($table_insert)->insert($list_insert);
                                    $list_insert = [];
                                    //\Log::info('se detectó limite de inserción');
                                    //\Log::info("Limite obtenido: $limit");
                                }
                            }

                            \DB::table($table_insert)->insert($list_insert);
                            //\Log::info('insert terminado.');

                            $records_list = \DB::table('transactions as t')
                                ->select(
                                    'o.name as owner',
                                    'sps.description as key',
                                    'm.descripcion as brand',
                                    'sxm.descripcion as service',
                                    't.status',
                                    't.referencia_numero_1',
                                    't.referencia_numero_2',
                                    't.identificador_transaction_id',
                                    'i.destination_operation_id',
                                    \DB::raw("t.id::integer as transaction_id"),
                                    \DB::raw("t.amount::integer as amount"),
                                    //\DB::raw("trim(replace(to_char(t.id,'999G999G999G999'), ',', '.')) as transaction_id_view"),
                                    //\DB::raw("trim(replace(to_char(t.amount,'999G999G999G999'), ',', '.')) || ' Gs.' as amount_view"),
                                    \DB::raw("to_char(t.created_at, 'DD/MM/YYYY') as created_at"),
                                    \DB::raw("to_char(t.updated_at, 'DD/MM/YYYY') as updated_at"),
                                    \DB::raw("to_char(t.created_at, 'DD/MM/YYYY HH24:MI:SS') as created_at_view"),
                                    \DB::raw("to_char(t.updated_at, 'DD/MM/YYYY HH24:MI:SS') as updated_at_view"),
                                    \DB::raw("(case when (i.id is not null) then 'Existe' else 'No existe' end) as incomes"),
                                    \DB::raw("(case when (tt.exists = true) then 'Existe' else 'No existe' end) as file"),
                                    \DB::raw("(case when (i.id is not null and tt.exists = true) then true else false end) as correct"),
                                    \DB::raw("(case when (i.id is null and tt.exists = null) then true else false end) as incorrect"),
                                    \DB::raw("false as view")
                                )
                                ->leftjoin('incomes as i', 't.id', '=', 'i.transaction_id')
                                //->leftjoin("$table_insert as tt", "$columns_where_join_1", "=", "$columns_where_join_2")
                                ->leftJoin("$table_insert as tt", function ($join) use ($columns_where, $columns_where_join_1, $columns_where_join_2) {
                                    $join->on("$columns_where_join_1", "=", "$columns_where_join_2");

                                    for ($j = 0; $j < count($columns_where); $j++) {
                                        //$records_list = $records_list->whereRaw($columns_where[$j]);
                                        $columns_where_join_1 = $columns_where[$j]['columns_where_join_1'];
                                        $columns_where_join_2 = $columns_where[$j]['columns_where_join_2'];
                                        $join->on(\DB::raw($columns_where_join_1), "=", $columns_where_join_2);
                                    }
                                })
                                ->join('services_providers_sources as sps', 'sps.id', '=', 't.service_source_id')
                                ->join('servicios_x_marca as sxm', 'sxm.service_id', '=', 't.service_id')
                                ->join('marcas as m', 'm.id', '=', 'sxm.marca_id')
                                ->join('owners as o', 'o.id', '=', 't.owner_id')
                                ->whereRaw("sps.id = sxm.service_source_id")
                                ->whereRaw("sps.description = any('{{$providers}}')")
                                ->whereRaw("t.created_at between '{$from}' and '{$to}'");

                            if ($reference_id_parent !== null) {
                                $records_list = $records_list->where('t.service_source_id', $reference_id_parent);
                            }

                            if ($reference_id_child !== null) {
                                $records_list = $records_list->where('t.service_id', $reference_id_child);
                            }

                            if ($record_limit !== '') {
                                $records_list = $records_list->take(intval($record_limit));
                            }

                            $records_list = $records_list->orderBy('t.id', 'asc');

                            //\Log::info('QUERY:');
                            //\Log::info($records_list->toSql());


                            $records_list = $records_list->get();

                            $records_list = array_map(function ($value) {
                                return (array) $value;
                            }, $records_list);

                            //\Log::info("LISTA:");
                            //\Log::info($records_list);

                            //$records_list_count = count($records_list);

                            /*\Log::info('Registros coincidentes:');
                            \Log::info("Cantidad: $records_list_count");
                            \Log::info($records_list);

                            \DB::statement("drop table if exists $table_insert");
                            \Log::info('Proceso terminado.');*/

                            //die();

                            \DB::statement("drop table if exists $table_insert");

                            //\Log::info('Proceso terminado.');

                            if (isset($multi_list[$parent]['data'])) {
                                $multi_list[$parent]['data'] = $records_list;
                            }
                        }

                        $data = array(
                            'parent' => $parent,
                            'child' => $key,
                            'name' => $client_original_name,
                            'extension' => $client_original_extension,
                            'size' => $file_size_,
                            'type' => $file_type,
                            'path' => $file_real_path,
                            'data' => $excel,
                            'coincidences' => $coincidences,
                            'valid' => $valid
                        );

                        //\Log::info($data);

                        if (isset($multi_list[$parent]['childs'][$key]['files'])) {
                            array_push($multi_list[$parent]['childs'][$key]['files'], $data);
                        }
                    }
                }
            }

            //\Log::info($multi_list);
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $multi_list;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function transaction_conciliator()
    {

        $time_init = '00:00:00';
        $time_end = '23:59:59';
        $from = date("d/m/Y");
        $to = date("d/m/Y");
        $timestamp = "$from $time_init - $to $time_end";
        //$consistent = 'Inconsistentes';

        $filters = [
            'timestamp' => $timestamp,
            //'consistent' => $consistent,
            'record_limit' => '',
        ];

        $data = [
            'open_modal' => 'no',
            'filters' => $filters,
            'list' => null,
            'date' => null,
            'method' => 'index',
            'execution_time' => null
        ];

        //01/06/21 return 06/01/21 
        //01/06/2021 return 06/01/21 
        //2021-06-01 or 21-06-01 or 01-06-2021 return 01/06/21

        /*$date_list = [
            '01/06/21 00:00', // 0 no
            '01/06/2021 00:00', // 1 si
            '2021/06/01 00:00', // 2 si
            '21-06-01 00:00', // 3 si
            '01-06-21 00:00', // 4 no
            '2021-06-01 00:00', // 5 si
            '01-06-2021 00:00' // 6 si
        ];

        for ($i = 0; $i < count($date_list); $i++) {
            $date = $date_list[$i];
            $date = str_replace('/', '-', $date);

            $date_time = new DateTime($date);
            $date = $date_time->format('d/m/Y');

            $aux = explode('/', $date);

            if ($aux[2] !== $year) {
                $lasts_chars = substr($aux[2], -2);
                $date = $lasts_chars .'/'.$aux[1].'/'.$year;
            }

            \Log::info("$i) Fecha: $date");
        }

        $string = 'This is a string';
$lastChar = substr($string, -2);
echo "The last char of the string is $lastChar.";*/

        //$date = DateTime::createFromFormat("Y/m/d", '01/06/21 00:00');
        //$date->format("j-M-Y");

        /* for($i = 0; $i < count($date_list); $i++) {
            $date = $date_list[$i];
            $slash = strpos($date, '/'); //La fecha tiene barra ?
            $date = str_replace('/', '-', $date);
            $type = "guión medio";

            if ($slash) {
                \Log::info("size:".strlen($date));
                $date_time = new DateTime($date);
                $date = $date_time->format('d/m/Y');

                if (strlen($date) == 10) {
                    $aux = explode('/', $date);
                    $date = $aux[1].'/'.$aux[0].'/'.$aux[2];
                }
                $type = "slash";
                
            } else {            
                $date_time = new DateTime($date);
                $date = $date_time->format('d/m/Y');
                \Log::info("size:".strlen($date));
            }

            \Log::info("$i) Fecha con $type: $date");
        }*/

        //'2021/06/01 00:00', // no
        //'01-06-21 00:00', // no

        //die();

        return view('conciliators.transaction_conciliator', compact('data'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function transaction_conciliator_validate($request, $user)
    {
        $open_modal = 'no';
        $message = '';
        $message_type = 'message';
        $list = [];
        $execution_time = null;

        try {
            $message = 'Cargando...';
            $message_type = 'message';
            $open_modal = 'si';

            $filters = [
                'files' => $request['files'],
                //'consistent' => $request['consistent'],
                'timestamp' => $request['timestamp'],
                'record_limit' => $request['record_limit']
            ];

            //\Log::info('transaction_conciliator_validate');
            //\Log::info($filters);

            $date_time_init = Carbon::now();

            //\Log::info("Comparación iniciada...");

            $list = json_encode($this->get_record_validations($filters, $user));

            //\Log::info("Comparación terminada...");

            //$execution_time = Carbon::now() - $date_time_init;
            //$execution_time = $execution_time->toTimeString(); 

        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
            $message = 'Error al validar datos del documento.';
            $message_type = 'error_message';
            $open_modal = 'no';
        }

        $data = [
            'open_modal' => $open_modal,
            'filters' => $filters,
            'list' => $list,
            'date' => $filters['timestamp'],
            'method' => 'create',
            'execution_time' => $execution_time
        ];

        \Session::flash($message_type, $message);
        return view('conciliators.transaction_conciliator', compact('data'));
    }

    public function transaction_conciliator_export($request, $user)
    {

        ini_set('max_execution_time', 0);
        ini_set('client_max_body_size', '20M');
        ini_set('max_input_vars', 10000);
        ini_set('upload_max_filesize', '20M');
        ini_set('post_max_size', '20M');
        ini_set('memory_limit', '-1');
        set_time_limit(3600);


        try {
            //\Log::info("Exportación iniciada...");

            $json = $request['json'];
            $list = json_decode($json, true);

            $data_to_excel = [];
            $file_to_excel = [];

            foreach ($list as $key => $value) {
                $sub_list = $list[$key]['data'];

                $files = $list[$key]['childs'];

                foreach ($files as $sub_key => $sub_value) {
                    if (count($files[$sub_key]['files']) > 0) {
                        $item = $files[$sub_key]['files'][0];
                        $row = [
                            $item['parent'] . ' / ' . $item['child'],
                            $item['name'],
                            $item['extension'],
                            $item['size'],
                        ];

                        array_push($file_to_excel, $row);
                        break;
                    }
                }

                for ($j = 0; $j < count($sub_list); $j++) {
                    $item = $sub_list[$j];
                    $status = $item['status'];
                    $incomes = $item['incomes'];
                    $file = $item['file'];
                    $view = true;

                    //\Log::info("$status, $incomes, $file");

                    if ($status == 'success') {
                        if ($incomes == 'Existe' and $file == 'Existe') {
                            $view = false;
                        }
                    } else {
                        if ($incomes == 'No existe' and $file == 'No existe') {
                            $view = false;
                        }
                    }

                    if ($view) {

                        if ($status == 'success') {
                            $status = "Aprobado($status)";
                        } else if ($status == 'canceled') {
                            $status = "Cancelado($status)";
                        } else if ($status == 'rollback') {
                            $status = "Reversado($status)";
                        } else if ($status == 'iniciated') {
                            $status = "Iniciado($status)";
                        } else if ($status == 'error dispositivo') {
                            $status = "Error de dispositivo($status)";
                        } else if ($status == 'inconsistency') {
                            $status = "Inconsistencia($status)";
                        } else {
                            $status = "Sin estado";
                        }

                        $item['status'] = $status;

                        $row = [
                            $item['key'],
                            $item['brand'],
                            $item['service'],
                            $item['amount'],
                            $item['created_at_view'],
                            $item['updated_at_view'],
                            $item['transaction_id'],
                            $item['status'],
                            $item['incomes'],
                            $item['file']
                        ];

                        array_push($data_to_excel, $row);
                    }
                }
            }

            $data_to_excel_headers = [
                'Proveedor',
                'Marca',
                'Servicio',
                'Monto',
                'Creación',
                'Actualización',
                'Transacción',
                'Estado(Admin)',
                'Ingreso(Ondanet)',
                'Datos(Archivo)'
            ];

            $file_to_excel_headers = [
                'Identificación',
                'Nombre',
                'Extensión',
                'Tamaño'
            ];

            array_unshift($data_to_excel, $data_to_excel_headers);
            array_unshift($file_to_excel, $file_to_excel_headers);

            $date = date("d/m/Y H:i:s.") . gettimeofday()["usec"];

            $filename = "transaction_conciliator_" . time();

            $style_array = [
                'font'  => [
                    'bold'  => true,
                    'color' => ['rgb' => '367fa9'],
                    'size'  => 15,
                    'name'  => 'Verdana'
                ]
            ];

            $columna1 = [];
            $columna2 = [];

            $excel = new ExcelExport($file_to_excel,$columna1,$data_to_excel,$columna2);
            return Excel::download($excel, $filename . '.xls')->send();

            // Excel::create($filename, function ($excel) use ($data_to_excel, $file_to_excel, $style_array) {

            //     $excel->sheet('Archivo comparado', function ($sheet) use ($file_to_excel) {
            //         $sheet->rows($file_to_excel, false);
            //         $sheet->getStyle("A1:D1")->getFont()->setBold(true);
            //     });

            //     $excel->sheet('Registros del sistema', function ($sheet) use ($data_to_excel, $style_array) {
            //         $range = 'A1:J1';
            //         $sheet->rows($data_to_excel, false); //Cargar los datos
            //         $sheet->getStyle($range)->applyFromArray($style_array); //Aplicar los estilos del array
            //         $sheet->setHeight(1, 50); //Aplicar tamaño de la primera fila
            //         $sheet->cells($range, function ($cells) {
            //             $cells->setAlignment('center'); // Alineamiento horizontal a central
            //             $cells->setValignment('center'); // Alineamiento vertical a central
            //         });

            //         $rows = count($data_to_excel);

            //         $sheet->cell("G1:J$rows", function ($cell) {
            //             $cell->setFontWeight('bold');
            //             //$cell->setBackground('#d2d6de');
            //             //$cell->setBorder('thin','thin','thin','thin');
            //         });
            //     });
            // })->export('xls');

            //\Log::info("Exportación terminada...");

            exit();
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }
    }
}
