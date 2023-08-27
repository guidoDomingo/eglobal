<?php

/**
 * User: avisconte
 * Date: 05/04/2021
 * Time: 11:00 am
 */

namespace App\Services;

class UssdMenuServices
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
     * Esta función recorre recursivamente todos los registros de opciones y sub-opciones.
     * 
     * @method getRecordsRecursively
     * @access public
     * @category Tool
     * @uses $value = $mus->getRecordsRecursively($menu_ussd_id, null); 
     * @param integer $menu_ussd_id
     * @param integer $menu_ussd_detail_parent_id
     * @return array $list 
     */
    public function get_options_recursively($menu_ussd_id, $menu_ussd_detail_parent_id)
    {
        $list = array();

        try {

            $menu_ussd_detail_list = \DB::table('ussd.menu_ussd_detail as mud')
                ->select(
                    'mud.id',
                    'mud.description',
                    'mud.status',
                    'mud.command',
                    'mul.description as level'
                )
                ->join('ussd.menu_ussd_level as mul', 'mul.id', '=', 'mud.menu_ussd_level_id')
                ->where('mud.menu_ussd_id', $menu_ussd_id)
                ->where('mud.menu_ussd_detail_parent_id', $menu_ussd_detail_parent_id)
                //->whereNotNull('service_id')
                ->orderBy('mud.id', 'asc')
                ->get();

            $menu_ussd_detail_list = array_map(function ($value) {
                return (array) $value;
            }, $menu_ussd_detail_list);

            for ($i = 0; $i < count($menu_ussd_detail_list); $i++) {
                $menu_ussd_detail_parent_id = $menu_ussd_detail_list[$i]['id'];
                $option_list = $this->get_options_recursively($menu_ussd_id, $menu_ussd_detail_parent_id);
                $menu_ussd_detail_list[$i]['options'] = $option_list;
                array_push($list, $menu_ussd_detail_list[$i]);
            }
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }

    /**
     * Obtine todos los telefonos con su saldo actual.
     */
    function ussd_menu_report()
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
            }, $operator_list);

            for ($i = 0; $i < count($operator_list); $i++) {

                $menu_ussd_operator_id = $operator_list[$i]['id'];
                $operator_list[$i]['status'] = ($operator_list[$i]['status']) ? 'Activo' : 'Inactivo';
                $operator_list[$i]['menus'] = array();

                $menu_ussd_list = \DB::table('ussd.menu_ussd')
                    ->where('menu_ussd_operator_id', $menu_ussd_operator_id)
                    ->get();

                $menu_ussd_list = array_map(function ($value) {
                    return (array) $value;
                }, $menu_ussd_list);

                for ($j = 0; $j < count($menu_ussd_list); $j++) {
                    $menu_ussd_id = $menu_ussd_list[$j]['id'];
                    $menu_ussd_list[$j]['status'] = ($menu_ussd_list[$j]['status']) ? 'Activo' : 'Inactivo';
                    $menu_ussd_list[$j]['options'] = $this->get_options_recursively($menu_ussd_id, null);
                    array_push($operator_list[$i]['menus'], $menu_ussd_list[$j]);
                }
            }

            $list = $operator_list;
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $list;
    }
}
