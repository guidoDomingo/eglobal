<?php

/**
 * User: avisconte
 * Date: 05/04/2021
 * Time: 11:00 am
 */

namespace App\Services;

use Carbon\Carbon;

class UssdOperatorServices
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
     * Obtine todos los telefonos con su saldo actual.
     */
    function ussd_operator_report()
    {
        $list = array();

        try {
            $records_list = \DB::table('ussd.menu_ussd_operator')->get();

            if (count($records_list) > 0) {
                foreach ($records_list as $item) {
                    $item->status = ($item->status) ? 'Activo' : 'Inactivo';

                    $item = [
                        'id' => $item->id,
                        'description' => $item->description,
                        'status' => $item->status,
                    ];

                    array_push($list, $item);
                }
            }
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }

    function ussd_operator_get_info($id)
    {
        $message = 'Operador obtenido correctamente.';
        $error = false;
        $error_detail = null;
        $list = null;

        try {
            $menu_ussd_operator = \DB::table('ussd.menu_ussd_operator')
                ->where('id', $id)
                ->get();

            if (count($menu_ussd_operator) > 0) {
                $menu_ussd_operator[0]->status = ($menu_ussd_operator[0]->status) ? 'Activo' : 'Inactivo';
                $list = $menu_ussd_operator[0];
            }
        } catch (\Exception $e) {
            $error_detail = $this->custom_error($e, __FUNCTION__);
            $message = 'Ocurrió un problema al obtener el registro';
            $error = true;
        }

        $data = [
            'message' => $message,
            'error' => $error,
            'error_detail' => $error_detail,
            'list' => $list
        ];

        return $data;
    }

    function ussd_operator_get_by_description($description)
    {
        $message = 'Operador obtenido correctamente.';
        $error = false;
        $error_detail = null;
        $data = null;

        try {
            $menu_ussd_operator = \DB::table('ussd.menu_ussd_operator')
                ->where('description', $description)
                ->get();

            if (count($menu_ussd_operator) > 0) {
                $menu_ussd_operator[0]->status = ($menu_ussd_operator[0]->status) ? 'Activo' : 'Inactivo';
                $data = $menu_ussd_operator[0];
            }
        } catch (\Exception $e) {
            $error_detail = $this->custom_error($e, __FUNCTION__);
            $message = 'Ocurrió un problema al obtener el registro';
            $error = true;
        }

        $data = [
            'message' => $message,
            'error' => $error,
            'error_detail' => $error_detail,
            'data' => $data
        ];

        return $data;
    }

    function ussd_operator_set_status($request, $user_id)
    {
        $message = 'Operador actualizado correctamente.';
        $error = false;
        $error_detail = null;

        try {
            $id = $request['id'];
            $status = $request['status'];

            \DB::beginTransaction();

            \DB::table('ussd.menu_ussd_operator')
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
