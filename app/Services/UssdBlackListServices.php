<?php

/**
 * User: avisconte
 * Date: 05/04/2021
 * Time: 11:00 am
 */

namespace App\Services;

use Carbon\Carbon;

class UssdBlackListServices
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
    function ussd_black_list_report()
    {
        $filters = null;
        $list = array();

        try {
            $time_init = '00:00:00';
            $time_end = '23:59:59';
            $from = date("d/m/Y");
            $to = date("d/m/Y");
            $timestamp = "$from $time_init - $to $time_end";

            $aux  = explode(' - ', str_replace('/', '-', $timestamp));
            $from = date('Y-m-d H:i:s', strtotime($aux[0]));
            $to = date('Y-m-d H:i:s', strtotime($aux[1]));

            $filters = [
                'timestamp' => $timestamp,
                'menu_ussd_black_list_reason_id' => '',
                'phone_number' => '',
                'menu_ussd_operator_id' => ''
            ];

            $records_list = \DB::table('ussd.menu_ussd_black_list as mubl')
                ->select(
                    'mubl.id',
                    'mubl.phone_number',
                    'mubl.status',
                    'mublr.id as menu_ussd_black_list_reason_id',
                    'mublr.description as menu_ussd_black_list_reason',
                    'muo.id as menu_ussd_operator_id',
                    'muo.description as menu_ussd_operator',
                    \DB::raw("(case when mubl.status = true then 'Activo' else 'Inactivo' end) as status"),
                    \DB::raw("to_char(mubl.created_at, 'DD/MM/YYYY HH24:MI:SS') as created_at"),
                    \DB::raw("to_char(mubl.updated_at, 'DD/MM/YYYY HH24:MI:SS') as updated_at"),
                    'u.username as user'
                )
                ->join('ussd.menu_ussd_black_list_reason as mublr', 'mublr.id', '=', 'mubl.menu_ussd_black_list_reason_id')
                ->join('ussd.menu_ussd_operator as muo', 'muo.id', '=', 'mubl.menu_ussd_operator_id')
                ->leftjoin('users as u', 'u.id', '=', 'mubl.user_id')
                ->whereRaw("mubl.created_at between '{$from}' and '{$to}'")
                ->orderBy('mubl.id', 'asc');

                \Log::info($records_list->toSql());
                
                $records_list = $records_list->get();

            if (count($records_list) > 0) {
                //Convertir la lista
                $list = array_map(function ($value) {
                    return (array) $value;
                }, $records_list);

                \Log::info($list);
            }
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        $data = [
            'filters' => $filters,
            'list' => $list
        ];

        return view('ussd.ussd_black_list_report', compact('data'));
    }

    function ussd_black_list_search($request)
    {
        $filters = null;
        $list = array();

        try {
            $timestamp = $request['timestamp'];
            $menu_ussd_black_list_reason_id = $request['menu_ussd_black_list_reason_id'];
            $phone_number = $request['phone_number'];
            $menu_ussd_operator_id = $request['menu_ussd_operator_id'];

            $filters = [
                'timestamp' => $timestamp,
                'menu_ussd_black_list_reason_id' => $menu_ussd_black_list_reason_id,
                'phone_number' => $phone_number,
                'menu_ussd_operator_id' => $menu_ussd_operator_id
            ];

            $records_list = \DB::table('ussd.menu_ussd_black_list as mubl')
                ->select(
                    'mubl.id',
                    'mubl.phone_number',
                    'mubl.status',
                    'mublr.id as menu_ussd_black_list_reason_id',
                    'mublr.description as menu_ussd_black_list_reason',
                    'muo.id as menu_ussd_operator_id',
                    'muo.description as menu_ussd_operator',
                    \DB::raw("(case when mubl.status = true then 'Activo' else 'Inactivo' end) as status"),
                    \DB::raw("to_char(mubl.created_at, 'DD/MM/YYYY HH24:MI:SS') as created_at"),
                    \DB::raw("to_char(mubl.updated_at, 'DD/MM/YYYY HH24:MI:SS') as updated_at"),
                    'u.username as user'
                )
                ->join('ussd.menu_ussd_black_list_reason as mublr', 'mublr.id', '=', 'mubl.menu_ussd_black_list_reason_id')
                ->join('ussd.menu_ussd_operator as muo', 'muo.id', '=', 'mubl.menu_ussd_operator_id')
                ->leftjoin('users as u', 'u.id', '=', 'mubl.user_id');


            if ($phone_number !== '') {
                $records_list = $records_list->where('mubl.phone_number', $phone_number);
            }

            if ($menu_ussd_black_list_reason_id !== '') {
                $menu_ussd_black_list_reason_id = intval($menu_ussd_black_list_reason_id);
                $records_list = $records_list->where('mubl.menu_ussd_black_list_reason_id', $menu_ussd_black_list_reason_id);
            }

            if ($timestamp !== null) {
                $aux  = explode(' - ', str_replace('/', '-', $timestamp));
                $from = date('Y-m-d H:i:s', strtotime($aux[0]));
                $to   = date('Y-m-d H:i:s', strtotime($aux[1]));
                $records_list = $records_list->whereRaw("mubl.created_at between '{$from}' and '{$to}'");
            }

            $records_list = $records_list->orderBy('mubl.id', 'asc');

            \Log::info($records_list->toSql());

            $records_list = $records_list->get();

            if (count($records_list) > 0) {
                //Convertir la lista
                $list = array_map(function ($value) {
                    return (array) $value;
                }, $records_list);
            }

            $message = 'Registros obtenidos correctamente.';
            $message_type = 'message';
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
            $message = 'Error al crear datos de documento.';
            $message_type = 'error_message';
        }

        $data = [
            'filters' => $filters,
            'list' => $list
        ];

        \Session::flash($message_type, $message);
        return view('ussd.ussd_black_list_report', compact('data'));
    }


    /**
     * Traer las razones de lista negra.
     */
    function ussd_black_list_reason()
    {
        $list = array();

        try {
            $list = \DB::table('ussd.menu_ussd_black_list_reason')
                ->select('id', 'description')
                ->where('status', true)
                ->get();
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }

    /**
     * Traer los operadores
     */
    function ussd_black_list_operador()
    {
        $list = array();

        try {
            $list = \DB::table('ussd.menu_ussd_operator')
                ->select('id', 'description')
                ->where('status', true)
                ->get();
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }


    /**
     * Agrega un nuevo registro
     */
    function ussd_black_list_add($request, $user_id)
    {
        $message = 'Registro no agregado.';
        $error = false;
        $error_detail = null;

        try {
            $phone_number_add = $request['phone_number_add'];
            $menu_ussd_black_list_reason_id_add = $request['menu_ussd_black_list_reason_id_add'];
            $menu_ussd_operator_id_add = $request['menu_ussd_operator_id_add'];

            $menu_ussd_black_list = \DB::table('ussd.menu_ussd_black_list')
                ->select('phone_number')
                ->where('phone_number', $phone_number_add)
                ->where('menu_ussd_operator_id', $menu_ussd_operator_id_add)
                ->get();

            if (count($menu_ussd_black_list) <= 0) {
                \DB::beginTransaction();

                \DB::table('ussd.menu_ussd_black_list')->insert([
                    'phone_number' => $phone_number_add,
                    'status' => true,
                    'menu_ussd_black_list_reason_id' => $menu_ussd_black_list_reason_id_add,
                    'created_at' => Carbon::now(),
                    'user_id' => $user_id,
                    'menu_ussd_operator_id' => $menu_ussd_operator_id_add
                ]);

                \DB::commit();

                $message = 'Registro agregado correctamente.';
            } else {
                $message = 'El teléfono ya existe.';
                $error = true;
            }
        } catch (\Exception $e) {
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
            'error' => $error,
            'message' => $message,
            'error_detail' => $error_detail
        ];

        return $data;
    }

    /**
     * Modifica registro
     */
    function ussd_black_list_edit($request, $user_id)
    {
        $message = 'Registro no actualizado.';
        $error = false;
        $error_detail = null;

        try {
            $id = $request['id'];
            $phone_number_edit = $request['phone_number_edit'];
            $menu_ussd_black_list_reason_id_edit = $request['menu_ussd_black_list_reason_id_edit'];
            $menu_ussd_operator_id_edit = $request['menu_ussd_operator_id_edit'];
            $status_edit = $request['status_edit'];

            $menu_ussd_black_list = \DB::table('ussd.menu_ussd_black_list')
                ->select('phone_number')
                ->where('phone_number', $phone_number_edit)
                ->where('menu_ussd_operator_id', $menu_ussd_operator_id_edit)
                ->where('id', '!=', $id)
                ->get();

            if (count($menu_ussd_black_list) <= 0) {
                \DB::beginTransaction();

                \DB::table('ussd.menu_ussd_black_list')
                    ->where('id', $id)
                    ->update([
                        'phone_number' => $phone_number_edit,
                        'status' => $status_edit,
                        'menu_ussd_black_list_reason_id' => $menu_ussd_black_list_reason_id_edit,
                        'updated_at' => Carbon::now(),
                        'user_id' => $user_id,
                        'menu_ussd_operator_id' => $menu_ussd_operator_id_edit
                    ]);

                \DB::commit();

                $message = "Registro n° $id actualizado correctamente.";
            } else {
                $message = 'Hay otro teléfono que tiene ese número.';
                $error = true;
            }
        } catch (\Exception $e) {
            $error_detail = $this->custom_error($e, __FUNCTION__);
            $message = 'Error al actualizar registro.';
            $error = true;
        }

        if ($error == true) {
            \Log::error($message);
        } else {
            \Log::info($message);
        }

        $data = [
            'error' => $error,
            'message' => $message,
            'error_detail' => $error_detail
        ];

        return $data;
    }
}
