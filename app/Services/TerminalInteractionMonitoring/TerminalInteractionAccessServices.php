<?php

/**
 * User: avisconte
 * Date: 05/04/2021
 * Time: 11:00 am
 */

namespace App\Services\TerminalInteractionMonitoring;

use Carbon\Carbon;

class TerminalInteractionAccessServices
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
    private function custom_error($e, $function)
    {
        $error_detail = [
            'exception_message' => $e->getMessage(),
            'file' => $e->getFile(),
            'class' => __CLASS__,
            'function' => $function,
            'line' => $e->getLine()
        ];

        \Log::error('Ocurrió un error. Detalles:');
        \Log::error($error_detail);

        return $error_detail;
    }

    function terminal_interaction_access($user_id)
    {
        $data = [
            'user_id' => $user_id,
            'status' => false,
            'pin_status' => 'new',
            'list' => []
        ];

        try {

            $terminal_interaction_login = \DB::table('terminal_interaction_login')
                ->select(
                    'id',
                    'status',
                    'pin'
                )
                ->where('user_id', $user_id)
                ->get();

            //\Log::info('terminal_interaction_login:');
            //\Log::info($terminal_interaction_login);

            if (count($terminal_interaction_login) > 0) {

                $terminal_interaction_login_id = $terminal_interaction_login[0]->id;
                $status = $terminal_interaction_login[0]->status;
                $pin = $terminal_interaction_login[0]->pin;

                if ($pin !== null and $pin !== '') {
                    $data['pin_status'] = 'update';
                }

                $records_list = \DB::table('pos_box as pb')
                    ->select(
                        'a.id as atm_id',
                        'a.name as atm_description',

                        'pos.id as points_of_sale_id',
                        'pos.description as points_of_sale_description',

                        'b.id as branch_id',
                        'b.description as branch_description',

                        'bg.id as group_id',
                        'bg.description as group_description',

                        'tia.id as terminal_interaction_access_id',
                        'tia.pos_box_id as pos_box_id_fk',
                        'tia.status',
                        'pb.id as pos_box_id',

                        \DB::raw("to_char(tia.updated_at, 'DD/MM/YYYY HH24:MI:SS') as updated_at"),
                        \DB::raw('coalesce(tia.group_supervisor, false) as group_supervisor'),
                        \DB::raw('coalesce(tia.branch_supervisor, false) as branch_supervisor'),
                        \DB::raw('coalesce(tia.atm_supervisor, false) as atm_supervisor'),
                        \DB::raw('coalesce(tia.status, false) as status')
                    )
                    ->join('atms as a', 'a.id', '=', 'pb.atm_id')
                    ->join('points_of_sale as pos', 'a.id', '=', 'pos.atm_id')
                    ->join('branches as b', 'b.id', '=', 'pos.branch_id')
                    ->join('business_groups as bg', 'bg.id', '=', 'b.group_id')
                    ->leftJoin('terminal_interaction_access as tia', function ($join) use ($terminal_interaction_login_id) {
                        $join->on('pb.id', "=", 'tia.pos_box_id')
                            ->where('tia.terminal_interaction_login_id', '=', $terminal_interaction_login_id);
                    });

                //\Log::info('query:');
                //\Log::info($records_list->toSql());

                $records_list = $records_list->get();

                if (count($records_list) > 0) {
                    foreach ($records_list as $item) {

                        if (!isset($list[$item->group_description])) {
                            $list[$item->group_description] = [
                                'group_id' => $item->group_id,
                                'group_description' => $item->group_description,
                                'group_supervisor' => $item->group_supervisor,
                                'updated_at' => $item->updated_at,
                                'branch_list' => []
                            ];
                        }

                        if (!isset($list[$item->group_description]['branch_list'][$item->branch_description])) {
                            $list[$item->group_description]['branch_list'][$item->branch_description] = [
                                'branch_id' => $item->branch_id,
                                'branch_description' => $item->branch_description,
                                'branch_supervisor' => $item->branch_supervisor,
                                'updated_at' => $item->updated_at,
                                'atm_list' => []
                            ];
                        }

                        if (!isset($list[$item->group_description]['branch_list'][$item->branch_description]['atm_list'][$item->atm_description])) {
                            $list[$item->group_description]['branch_list'][$item->branch_description]['atm_list'][$item->atm_description] = [
                                'atm_id' => $item->atm_id,
                                'atm_description' => $item->atm_description,
                                'atm_supervisor' => $item->atm_supervisor,
                                'updated_at' => $item->updated_at,
                                'pos_box_id' => $item->pos_box_id,
                                'pos_box_id_fk' => $item->pos_box_id_fk,
                                'status' => $item->status
                            ];
                        }
                    }

                    //\Log::info('LISTA:');
                    //\Log::info($list);

                    $data['list'] = $list;
                    $data['status'] = $status;
                } else {
                    //El usuario no tiene acceso a ninguna caja pos
                }
            } else {
                //El usuario no está autorizado para trabajar con caja pos
            }
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $data;
    }

    function terminal_interaction_access_edit($request, $user)
    {
        $data = [
            'error' => false,
            'message' => 'Acción exitosa.',
            'list' => []
        ];

        try {
            //\Log::info($request['parameters']);

            $parameters = $request['parameters'];

            $user_id = $parameters['user_id']; //Usuario a editar.
            $supervisor = $parameters['supervisor'];

            if ($supervisor == 'si') {
                $supervisor = false;
            } else if ($supervisor == 'no') {
                $supervisor = true;
            }

            $terminal_interaction_login = \DB::table('terminal_interaction_login')
                ->select(
                    'id',
                    'supervisor'
                )
                ->where('user_id', $user->id)
                ->get();

            if (count($terminal_interaction_login) > 0) {
                $terminal_interaction_login_id = $terminal_interaction_login[0]->id;
            }

            $terminal_interaction_login_edit = \DB::table('terminal_interaction_login')
                ->select(
                    'id',
                    'supervisor'
                )
                ->where('user_id', $user_id)
                ->get();

            if (count($terminal_interaction_login_edit) > 0) {
                $terminal_interaction_login_id_edit = $terminal_interaction_login_edit[0]->id;
            }

            $records_list = \DB::table('pos_box as pb')
                ->select(
                    'tia.id as terminal_interaction_access_id',
                    'tia.terminal_interaction_login_id',
                    'bg.id as group_id',
                    'b.id as branch_id',
                    'a.id as atm_id',
                    'pb.id as pos_box_id',
                    'tia.pos_box_id as pos_box_id_fk'
                )
                ->join('atms as a', 'a.id', '=', 'pb.atm_id')
                ->join('points_of_sale as pos', 'a.id', '=', 'pos.atm_id')
                ->join('branches as b', 'b.id', '=', 'pos.branch_id')
                ->join('business_groups as bg', 'bg.id', '=', 'b.group_id')
                ->leftJoin('terminal_interaction_access as tia', function ($join) use ($terminal_interaction_login_id_edit) {
                    $join->on('pb.id', '=', 'tia.pos_box_id')
                        ->where('tia.terminal_interaction_login_id', '=', $terminal_interaction_login_id_edit);
                });

            $carbon_now = Carbon::now();

            $insert = [
                'terminal_interaction_login_id' => $terminal_interaction_login_id_edit,
                'pos_box_id' => null,
                'created_at' => $carbon_now,
                'created_by_at' => $terminal_interaction_login_id,
                'status' => true
            ];

            $update = [
                'updated_at' => $carbon_now,
                'updated_by_at' => $terminal_interaction_login_id,
                'status' => true
            ];

            switch ($parameters['update_type']) {
                case 'group':
                    $records_list = $records_list->where('bg.id', '=', $parameters['referential_id']);

                    $insert['group_supervisor'] = $supervisor;
                    $insert['branch_supervisor'] = $supervisor;
                    $insert['atm_supervisor'] = $supervisor;

                    $update['group_supervisor'] = $supervisor;
                    $update['branch_supervisor'] = $supervisor;
                    $update['atm_supervisor'] = $supervisor;
                    break;

                case 'branch':
                    $records_list = $records_list->where('b.id', '=', $parameters['referential_id']);

                    $insert['branch_supervisor'] = $supervisor;
                    $insert['atm_supervisor'] = $supervisor;

                    $update['branch_supervisor'] = $supervisor;
                    $update['atm_supervisor'] = $supervisor;
                    break;

                case 'atm':
                    $records_list = $records_list->where('a.id', '=', $parameters['referential_id']);

                    $insert['atm_supervisor'] = $supervisor;

                    $update['atm_supervisor'] = $supervisor;
                    break;
            }

            $records_list = $records_list->get();

            foreach ($records_list as $item) {
                if ($item->pos_box_id_fk == null) {

                    $insert['pos_box_id'] = $item->pos_box_id;

                    \DB::table('terminal_interaction_access')
                        ->insert($insert);
                } else {
                    \DB::table('terminal_interaction_access')
                        ->where('terminal_interaction_login_id', $terminal_interaction_login_id_edit)
                        ->where('id', $item->terminal_interaction_access_id)
                        ->update($update);
                }
            }

            \Log::info('PARAMETROS:');
            \Log::info($parameters);
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $data;
    }

    function terminal_interaction_assign_atm($request, $user)
    {
        $data = [
            'error' => false,
            'message' => 'Acción exitosa.',
            'list' => []
        ];

        try {
            $parameters = $request['parameters'];
            $user_id = $parameters['user_id']; //Usuario a editar.
            $pos_box_id = $parameters['pos_box_id'];

            $terminal_interaction_login = \DB::table('terminal_interaction_login')
                ->select(
                    'id',
                    'supervisor'
                )
                ->where('user_id', $user->id)
                ->get();

            if (count($terminal_interaction_login) > 0) {
                $terminal_interaction_login_id = $terminal_interaction_login[0]->id;
            }

            $terminal_interaction_login_edit = \DB::table('terminal_interaction_login')
                ->select(
                    'id',
                    'supervisor'
                )
                ->where('user_id', $user_id)
                ->get();

            if (count($terminal_interaction_login_edit) > 0) {
                $terminal_interaction_login_id_edit = $terminal_interaction_login_edit[0]->id;
            }

            $records_list = \DB::table('terminal_interaction_access')
                ->select(
                    'id',
                    'status'
                )
                ->where('terminal_interaction_login_id', $terminal_interaction_login_id_edit)
                ->where('pos_box_id', $pos_box_id)
                ->get();

            $status = false;
            $insert = false;

            if (count($records_list) > 0) {
                $status = $records_list[0]->status;

                if ($status) {
                    $status = false;
                } else {
                    $status = true;
                }

                $insert = false;
            } else {
                $insert = true;
            }

            $carbon_now = Carbon::now();

            if ($insert) {
                $insert = [
                    'terminal_interaction_login_id' => $terminal_interaction_login_id_edit,
                    'pos_box_id' => $pos_box_id,
                    'created_at' => $carbon_now,
                    'created_by_at' => $terminal_interaction_login_id,
                    'status' => true
                ];

                \DB::table('terminal_interaction_access')
                    ->insert($insert);

                //\Log::info("INSERTADO, STATUS: $status");
            } else {
                $update = [
                    'updated_at' => $carbon_now,
                    'updated_by_at' => $terminal_interaction_login_id,
                    'status' => $status
                ];

                \DB::table('terminal_interaction_access')
                    ->where('terminal_interaction_login_id', $terminal_interaction_login_id_edit)
                    ->where('pos_box_id', $pos_box_id)
                    ->update($update);

                //\Log::info("ACTUALIZADO, STATUS: $status");
            }

            //\Log::info('PARAMETROS:');
            //\Log::info($parameters);
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $data;
    }

    public function terminal_interaction_login_add($request, $user)
    {
        $data = [
            'error' => false,
            'message' => 'Acción exitosa.',
            'list' => []
        ];

        try {
            $user_id = $request['user_id']; //Usuario a editar.

            $terminal_interaction_login = \DB::table('terminal_interaction_login')
                ->select(
                    'id',
                    'supervisor'
                )
                ->where('user_id', $user->id)
                ->get();

            if (count($terminal_interaction_login) > 0) {
                $terminal_interaction_login_id = $terminal_interaction_login[0]->id;
            }

            $terminal_interaction_login_edit = \DB::table('terminal_interaction_login')
                ->select(
                    'id',
                    'status'
                )
                ->where('user_id', $user_id)
                ->get();

            if (count($terminal_interaction_login_edit) > 0) {
                $terminal_interaction_login_id_edit = $terminal_interaction_login_edit[0]->id;
            }

            $status = false;
            $insert = false;

            if (count($terminal_interaction_login_edit) > 0) {
                $status = $terminal_interaction_login_edit[0]->status;

                if ($status) {
                    $status = false;
                } else {
                    $status = true;
                }

                $insert = false;
            } else {
                $insert = true;
            }

            $carbon_now = Carbon::now();

            if ($insert) {
                $insert = [
                    'user_id' => $user_id,
                    'created_at' => $carbon_now,
                    'status' => true
                ];

                \DB::table('terminal_interaction_login')
                    ->insert($insert);
            } else {
                $update = [
                    'updated_at' => $carbon_now,
                    'status' => $status
                ];

                \DB::table('terminal_interaction_login')
                    ->where('id', $terminal_interaction_login_id_edit)
                    ->update($update);
            }
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $data;
    }

    public function terminal_interaction_save_pin($request)
    {
        $data = [
            'error' => false,
            'message' => '',
            'list' => []
        ];

        try {

            $parameters = $request['parameters'];
            $user_id = $parameters['user_id'];
            $pin = $parameters['pin'];
            $pin_repeat = $parameters['pin_repeat'];
            $pin_status = $parameters['pin_status'];

            //\Log::info("pin_status:");
            //\Log::info($pin_status);

            $terminal_interaction_login = \DB::table('terminal_interaction_login')
                ->select(
                    'id',
                    'pin'
                )
                ->where('user_id', $user_id)
                ->get();

            //\Log::info('terminal_interaction_login');
            //\Log::info($terminal_interaction_login);

            if (count($terminal_interaction_login) > 0) {

                if ($pin !== null and $pin !== '' and $pin_repeat !== null and $pin_repeat !== '') {

                    $run = false;
                    $terminal_interaction_login_id = $terminal_interaction_login[0]->id;

                    if ($pin_status == 'new') {

                        if ($pin == $pin_repeat) {

                            /**
                             * Hay algún otro usuario que tenga el pin ingresado ?
                             */
                            $terminal_interaction_pin = \DB::table('terminal_interaction_login')
                                ->select(
                                    'id'
                                )
                                ->whereRaw("pin = md5('$pin')")
                                ->whereRaw("id != $terminal_interaction_login_id")
                                ->get();

                            if (count($terminal_interaction_pin) <= 0) {
                                $run = true;
                            } else {
                                $data['message'] = 'Otro usuario ya tiene ese pin.';
                            }
                        } else {
                            $data['message'] = 'Los campos no son iguales, volver a ingresar.';
                        }
                    } else if ($pin_status == 'update') {

                        if ($pin !== $pin_repeat) {

                            /**
                             * El pin antiguo coincide ?
                             */
                            $terminal_interaction_pin = \DB::table('terminal_interaction_login')
                                ->select(
                                    'id'
                                )
                                ->whereRaw("pin = md5('$pin')")
                                ->whereRaw("id = $terminal_interaction_login_id")
                                ->get();

                            if (count($terminal_interaction_pin) > 0) {

                                /**
                                 * El pin nuevo es diferente a todos ?
                                 */
                                $terminal_interaction_pin = \DB::table('terminal_interaction_login')
                                    ->select(
                                        'id'
                                    )
                                    ->whereRaw("pin = md5('$pin_repeat')")
                                    ->whereRaw("id != $terminal_interaction_login_id")
                                    ->get();
        
                                if (count($terminal_interaction_pin) <= 0) {
                                    $run = true;
                                } else {
                                    $data['message'] = 'El pin nuevo ingresado ya existe.';
                                }
                            } else {
                                $data['message'] = 'Pin antiguo no coincide.';
                            }
                        } else {
                            $data['message'] = 'Pin nuevo no debe ser igual al antiguo.';
                        }
                    }

                    if ($run) {
                        \DB::table('terminal_interaction_login')
                            ->where('id', $terminal_interaction_login_id)
                            ->update([
                                'pin' => \DB::raw("md5('$pin')"),
                                'updated_at' => \DB::raw("now()"),
                            ]);
                    }
                } else {
                    $data['message'] = 'Los campos deben estar completos.';
                }
            } else {
                $data['message'] = 'El usuario no cuenta con acceso a interacciones de terminal.';
            }

            if ($data['message'] !== '') {
                $data['error'] = true;
            } else {
                $data['message'] = 'Pin guardado correctamente.';
            }
            
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $data;
    }

}
