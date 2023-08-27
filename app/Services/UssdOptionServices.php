<?php

/**
 * User: avisconte
 * Date: 05/04/2021
 * Time: 11:00 am
 */

namespace App\Services;

use Carbon\Carbon;

class UssdOptionServices
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
            "exception_message" => $message,
            "file"              => $file,
            "class"             => __CLASS__,
            "function"          => $function,
            "line"              => $line
        ];

        \Log::error('Ocurrió una excepción:', $error_detail);

        return $error_detail;
    }

    /**
     * Obtine todas las opciones
     */
    function ussd_option_report()
    {
        $list = array();

        try {
            $operator_list = \DB::table('ussd.menu_ussd_operator as muo')
                ->select(
                    'muo.id',
                    'muo.description',
                    'muo.status',
                    \DB::raw("to_char(muo.created_at, 'DD/MM/YYYY HH24:MI:SS') as created_at"),
                    \DB::raw("to_char(muo.updated_at, 'DD/MM/YYYY HH24:MI:SS') as updated_at"),
                    'u.description as user'
                )
                ->leftjoin('users as u', 'u.id', '=', 'muo.user_id')
                ->orderBy('muo.id', 'asc')
                ->get();

            $operator_list = array_map(function ($value) {
                return (array) $value;
            }, $operator_list->toArray());

            for ($i = 0; $i < count($operator_list); $i++) {

                $menu_ussd_operator_id = $operator_list[$i]['id'];
                $operator_list[$i]['status'] = ($operator_list[$i]['status']) ? 'Activo' : 'Inactivo';
                $operator_list[$i]['services'] = array();

                $operator_count = \DB::table('ussd.menu_ussd_operator as muo')
                    ->select(\DB::raw("count(muo.id)"))
                    ->join('ussd.menu_ussd_detail_client as mudc', 'muo.id', '=', 'mudc.menu_ussd_operator_id')
                    ->where('muo.id', $menu_ussd_operator_id)
                    ->get();

                $operator_list[$i]['count'] = $operator_count[0]->count;

                $service_list = \DB::table('ussd.menu_ussd_service as mus')
                    ->select(
                        'mus.id',
                        'mus.description',
                        'mus.status',
                        'mus.created_at',
                        'mus.menu_ussd_operator_id',
                        'mus.service_id',
                        \DB::raw("to_char(mus.created_at, 'DD/MM/YYYY HH24:MI:SS') as created_at"),
                        \DB::raw("to_char(mus.updated_at, 'DD/MM/YYYY HH24:MI:SS') as updated_at"),
                        'u.description as user'
                    )
                    ->leftjoin('users as u', 'u.id', '=', 'mus.user_id')
                    ->where('mus.menu_ussd_operator_id', $menu_ussd_operator_id)
                    ->orderBy('mus.id', 'asc')
                    ->get();

                $service_list = array_map(function ($value) {
                    return (array) $value;
                }, $service_list->toArray());

                for ($j = 0; $j < count($service_list); $j++) {

                    $service_list[$j]['status'] = ($service_list[$j]['status']) ? 'Activo' : 'Inactivo';
                    $service_list[$j]['options'] = array();

                    $menu_ussd_list = \DB::table('ussd.menu_ussd')
                        ->select('id')
                        ->where('menu_ussd_operator_id', $menu_ussd_operator_id)
                        ->where('status', true)
                        ->get();

                    $menu_ussd_list = array_map(function ($value) {
                        return (array) $value;
                    }, $menu_ussd_list->toArray());

                    if (count($menu_ussd_list) > 0) {
                        $menu_ussd_id = $menu_ussd_list[0]['id'];

                        $option_list = \DB::table('ussd.menu_ussd_detail')
                            ->select('id', 'description', 'status', 'created_at', 'amount', 'service_id')
                            ->whereNotNull('option')
                            ->whereNotNull('amount')
                            ->whereNotNull('command')
                            ->where('menu_ussd_type_id', 2)
                            ->where('menu_ussd_id', $menu_ussd_id)
                            ->orderBy('option', 'asc')
                            ->get();

                        $option_list = array_map(function ($value) {
                            return (array) $value;
                        }, $option_list->toArray());

                        for ($k = 0; $k < count($option_list); $k++) {
                            $option_list[$k]['status'] = ($option_list[$k]['status']) ? 'Activo' : 'Inactivo';

                            if ($service_list[$j]['service_id'] == $option_list[$k]['service_id']) {
                                array_push($service_list[$j]['options'], $option_list[$k]);
                            }
                        }
                    }

                    if ($operator_list[$i]['id'] == $service_list[$j]['menu_ussd_operator_id']) {
                        array_push($operator_list[$i]['services'], $service_list[$j]);
                    }
                }
            }

            $list = $operator_list;
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }

    function ussd_option_set_status($request, $user_id)
    {
        $message = 'Opción actualizada correctamente.';
        $error = false;
        $error_detail = null;

        try {
            $id = $request['id'];
            $status = $request['status'];

            \DB::beginTransaction();

            //Verificar si el operador está activo para habilitarse.
            \DB::table('ussd.menu_ussd_detail')
                ->where('id', $id)
                ->update([
                    'status' => $status,
                    'user_id' => $user_id,
                    'updated_at' => Carbon::now()
                ]);

            \DB::commit();
        } catch (\Exception $e) {
            $error_detail = $this->custom_error($e, __FUNCTION__);
            $message = 'Ocurrió un problema al modificar el registro';
            $error = true;

            \DB::rollback();
        }

        $data = [
            'message' => $message,
            'error' => $error,
            'error_detail' => $error_detail
        ];

        return $data;
    }
}
