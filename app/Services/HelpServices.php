<?php

/**
 * User: avisconte
 * Date: 10/06/2021
 * Time: 10:15 am
 */

namespace App\Services;

use Carbon\Carbon;

class HelpServices
{
    /**
     * Esta función sirve para personalizar la excepción
     * 
     * @method custom_error
     * @access public
     * @category Tools
     * @param $e, $function
     * @return array $error_detail 
     */
    public function custom_error($e, $function)
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

        \Log::error('Ocurrió una excepción:', $error_detail);

        return $error_detail;
    }

    /**
     * Indice de la página ayuda
     */
    function help_index($request)
    {

        \Log::info('Request:');
        \Log::info($request['help_id']);

        $data = [
            'filters' => null,
            'list' => null
        ];

        return view('help.help_index', compact('data'));
    }

    /**
     * Indice de la página ayuda
     */
    function help_report($filters)
    {
        $list = array();

        try {

            if (isset($filters)) {
                $timestamp = $filters['timestamp'];
                $module_id = $filters['module_id'];
            } else {
                $time_init = '00:00:00';
                $time_end = '23:59:59';
                $from = date("d/m/Y");
                $to = date("d/m/Y");
                $timestamp = "$from $time_init - $to $time_end";

                $module_id = '';
            }

            $filters = [
                'timestamp' => $timestamp,
                'module_id' => $module_id
            ];

            $records_list = \DB::table('help.help as h')
                ->select(
                    'h.id',
                    'h.description',
                    'm.description as module',
                    \DB::raw("(case when h.status = true then 'Activo' else 'Inactivo' end) as status"),
                    \DB::raw("u.description || ' - ' || u.username as user"),
                    \DB::raw("coalesce(to_char(h.created_at, 'DD/MM/YYYY HH24:MI:SS'), '') as created_at")
                )
                ->join('help.module as m', 'm.id', '=', 'h.module_id')
                ->join('users as u', 'u.id', '=', 'h.user_id');

            \Log::info("Module_id = $module_id");

            if ($module_id !== '') {
                $module_id = intval($module_id);
                $records_list = $records_list->where('h.module_id', $module_id);
            }

            if ($timestamp !== null) {
                $aux  = explode(' - ', str_replace('/', '-', $timestamp));
                $from = date('Y-m-d H:i:s', strtotime($aux[0]));
                $to   = date('Y-m-d H:i:s', strtotime($aux[1]));
                $records_list = $records_list->whereRaw("h.created_at between '{$from}' and '{$to}'");
            }

            \Log::info($records_list->toSql());

            $records_list = $records_list->get();

            $records_list = array_map(function ($value) {
                return (array) $value;
            }, $records_list);

            $list = $records_list;
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        $data = [
            'filters' => $filters,
            'list' => $list
        ];

        return view('help.help_report', compact('data'));
    }

    /**
     * Obtener los módulos.
     */
    function help_module()
    {
        $list = array();

        try {
            $list = \DB::table('help.module')
                ->select('id', 'description')
                ->where('status', true)
                ->get();
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }

    /**
     * Obtener las plantillas.
     */
    function help_template()
    {
        $list = array();

        try {
            $list = \DB::table('help.template')
                ->select('id', 'description')
                ->where('status', true)
                ->get();
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }

    /**
     * Obtener los atributos.
     */
    function help_attribute()
    {
        $list = array();

        try {
            $list = \DB::table('help.attribute')
                ->select('id', 'description')
                ->where('status', true)
                ->get();
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }

    /**
     * Obtener los atributos.
     */
    function help_content_position_next()
    {
        $list = array();

        try {
            $list = \DB::table('help.content_position_next')
                ->select('id', 'description')
                ->where('status', true)
                ->get();
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }

    /**
     * Obtener las vistas
     */
    function help_item_type()
    {
        $list = array();

        try {
            $list = \DB::table('help.item_type')
                ->select('id', 'description')
                ->where('status', true)
                ->get();
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }

    /**
     * Obtener las vistas
     */
    function help_view()
    {
        $list = array();

        try {
            $list = \DB::table('help.view')
                ->select('id', 'description')
                ->where('status', true)
                ->get();
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }

    /**
     * Agregar cabecera de ayuda
     */
    function help_add($request, $user_id)
    {
        $help_id = null;
        $message = 'Ayuda no agregada.';
        $error = false;
        $error_detail = null;

        try {
            $module_id = $request['module_id'];
            $key = $request['key'];
            $description = $request['description'];
            $template_id = $request['template_id'];
            $view_id = $request['view_id'];

            $help_screen = \DB::table('help.help')
                ->where('key', $key)
                ->get();

            if (count($help_screen) <= 0) {
                \DB::beginTransaction();

                $help_id = \DB::table('help.help')->insertGetId([
                    'key' => $key,
                    'description' => $description,
                    'status' => true,
                    'created_at' => Carbon::now(),
                    'updated_at' => null,
                    'module_id' => $module_id,
                    'view_id' => $view_id,
                    'template_id' => $template_id,
                    'user_id' => $user_id
                ]);

                \DB::commit();

                $message = "Se agregó correctamente la ayuda n° $help_id.";
            } else {
                $message = 'Ya existe una ayuda con ese identificador.';
                $error = true;
            }
        } catch (\Exception $e) {
            \DB::rollback();
            $error_detail = $this->custom_error($e, __FUNCTION__);
            $message = 'Error al modificar registro.';
            $error = true;
        }

        if ($error == true) {
            \Log::error($message);
        } else {
            \Log::info($message);
        }

        $data = [
            'id' => $help_id,
            'message' => $message,
            'error' => $error,
            'error_detail' => $error_detail
        ];

        return $data;
    }

    /**
     * Agregar elemento
     */
    function item_add($request, $user_id)
    {
        $item_number = null;
        $item_id = null;
        $level_id = null;
        $message = 'Elemento no agregado.';
        $error = false;
        $error_detail = null;

        try {
            $help_id = $request['help_id'];
            $parent_id = $request['parent_id'];
            $item_type_id = $request['item_type_id'];

            $parent_id = ($parent_id == 'initial') ? null : $parent_id;

            $item_number = \DB::table('help.item')
                ->select(\DB::raw('coalesce(count(id), 0) + 1 as item_number'))
                ->where('help_id', $help_id)
                ->where('item_id', $parent_id);

            $item_number  = $item_number->get();

            $item_number = (count($item_number) > 0) ? $item_number[0]->item_number : 1;

            $level_id = \DB::table('help.item')
                ->select(\DB::raw('level_id + 1 as level_id'))
                ->where('help_id', $help_id)
                ->where('id', $parent_id);

            //\Log::info($level_id->toSql());
            $level_id = $level_id->get();
            $level_id = (count($level_id) > 0) ? $level_id[0]->level_id : 1;

            \DB::beginTransaction();

            $item_id = \DB::table('help.item')->insertGetId([
                'status' => true,
                'created_at' => Carbon::now(),
                'updated_at' => null,
                'item_type_id' => $item_type_id,
                'help_id' => $help_id,
                'level_id' => $level_id,
                'item_id' => $parent_id,
                'user_id' => $user_id
            ]);

            \DB::commit();

            $message = "Se agregó el elemento n° $item_number al nivel $level_id";
        } catch (\Exception $e) {
            \DB::rollback();
            $error_detail = $this->custom_error($e, __FUNCTION__);
            $message = 'Error al agregar registro.';
            $error = true;
        }

        if ($error == true) {
            \Log::error($message);
        } else {
            \Log::info($message);
        }

        $data = [
            'help_id' => $help_id,
            'parent_id' => $parent_id,
            'item_id' => $item_id,
            'level_id' => $level_id,
            'item_number' => $item_number,
            'message' => $message,
            'error' => $error,
            'error_detail' => $error_detail
        ];

        //\Log::info('DATOS:');
        //\Log::info($data);

        return $data;
    }

    /**
     * Agregar elemento
     */
    function item_detail_add($request, $user_id)
    {
        $message = 'Contenido no agregado.';
        $error = false;
        $error_detail = null;

        $position = null;
        $item_detail_id = null;


        try {
            $item_id = $request['item_id'];

            $position = \DB::table('help.item_detail')
                ->select(\DB::raw('coalesce(max(position), 0) + 1 as position'))
                ->where('item_id', $item_id)
                ->get();

            $position = (count($position) > 0) ? $position[0]->position : 1;

            \DB::beginTransaction();

            $item_detail_id = \DB::table('help.item_detail')->insertGetId([
                'position' => $position,
                'description' => null,
                'status' => true,
                'created_at' => Carbon::now(),
                'updated_at' => null,
                'item_id' => $item_id,
                'attribute_id' => null,
                'template_id' => null,
                'content_position_next_id' => null,
                'user_id' => $user_id
            ]);

            \DB::commit();

            $message = "Se agregó un nuevo contenido";
        } catch (\Exception $e) {
            \DB::rollback();
            $error_detail = $this->custom_error($e, __FUNCTION__);
            $message = 'Error al agregar registro.';
            $error = true;
        }

        if ($error == true) {
            \Log::error($message);
        } else {
            \Log::info($message);
        }

        $data = [
            'item_detail_id' => $item_detail_id,
            'position' => $position,
            'message' => $message,
            'error' => $error,
            'error_detail' => $error_detail
        ];

        //\Log::info('DATOS:');
        //\Log::info($data);

        return $data;
    }
}
