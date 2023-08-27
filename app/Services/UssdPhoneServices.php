<?php

/**
 * User: avisconte
 * Date: 05/04/2021
 * Time: 11:00 am
 */

namespace App\Services;

use Carbon\Carbon;

class UssdPhoneServices
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
    function ussd_phone_report()
    {
        $list = [];

        try {
            $records_list = \DB::table('ussd.menu_ussd_phone as mup')
                ->select(
                    'muo.description as operator',
                    'mup.id',
                    'mup.phone_number',
                    'mup.signal',
                    \DB::raw("(case when (mup.status) then 'Activo' else 'Inactivo' end) as status"),
                    \DB::raw("trim(replace(to_char(mup.current_amount, '999G999G999G999'), ',', '.')) as current_amount "),
                    \DB::raw("to_char(mup.updated_at, 'DD/MM/YYYY HH24:MI:SS') as updated_at")
                )
                ->join('ussd.menu_ussd_operator as muo', 'muo.id', '=', 'mup.menu_ussd_operator_id')
                ->orderBy('mup.updated_at', 'desc')
                ->get();

            $records_list = array_map(function ($value) {
                return (array) $value;
            }, $records_list);

            $list = $records_list;

            /*if (count($records_list) > 0) {
                foreach ($records_list as $item) {

                    $item->status = ($item->status) ? 'Activo' : 'Inactivo';

                    $item->current_amount = number_format($item->current_amount, 0, ',', '.');

                    $item = [
                        "operator"       => $item->operator,
                        "id"             => $item->id,
                        "phone_number"   => $item->phone_number,
                        "current_amount" => $item->current_amount,
                        "status"         => $item->status,
                        "updated_at"     => $item->updated_at
                    ];

                    array_push($list, $item);
                }
            }*/
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }

    function ussd_phone_set_status($request, $user_id)
    {
        $message = 'Teléfono actualizado correctamente.';
        $error = false;
        $error_detail = null;

        try {
            $id = $request['id'];
            $menu_ussd_phone_status = $request['status'];

            \DB::beginTransaction();

            //Verificar si el operador está activo para habilitarse.
            $menu_ussd_phone = \DB::table('ussd.menu_ussd_phone')
                ->select('menu_ussd_operator_id')
                ->where('id', $id)
                ->get();

            if (count($menu_ussd_phone) > 0) {
                $menu_ussd_operator_id = $menu_ussd_phone[0]->menu_ussd_operator_id;

                $menu_ussd_operator = \DB::table('ussd.menu_ussd_operator')
                    ->where('id', $menu_ussd_operator_id)
                    ->where('status', true)
                    ->get();

                $menu_ussd_operator = (array) $menu_ussd_operator;

                if (count($menu_ussd_operator) <= 0) {
                    \DB::table('ussd.menu_ussd_operator')
                        ->where('id', $menu_ussd_operator_id)
                        ->update([
                            'status' => true,
                            'user_id' => $user_id,
                            'updated_at' => Carbon::now()
                        ]);
                } else {
                    \Log::info('Operador no actualizado...');
                }
            }

            \DB::table('ussd.menu_ussd_phone')
                ->where('id', $id)
                ->update([
                    'status' => $menu_ussd_phone_status,
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
