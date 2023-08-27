<?php

namespace App\Http\Controllers\ServiceRuleParams;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

class ServiceRuleParamsController extends Controller
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
     * Recibe el objeto exception y retorna una lista personalizada
     *
     * @return $error_detail
     */
    private function custom_error($e, $function)
    {
        $error_detail = [
            'exception' => $e->getMessage(),
            'file' => $e->getFile(),
            'class' => __CLASS__,
            'function' => $function,
            'line' => $e->getLine()
        ];

        \Log::error('Ocurrió un error. Detalles:');
        \Log::error($error_detail);

        return $error_detail;
    }

    public function get_debt($atm_id)
    {
        $amount = 0;

        try {
            $balance_atms = \DB::table('balance_atms')
                ->select(
                    \DB::raw('coalesce(sum(total_transaccionado), 0)::numeric as transaccionado'),
                    \DB::raw('coalesce(sum(total_depositado), 0)::numeric as depositado'),
                    \DB::raw('coalesce(sum(total_reversado), 0)::numeric as reversado'),
                    \DB::raw('coalesce(sum(total_cashout), 0)::numeric as cashout'),
                    \DB::raw('coalesce(sum(total_pago_cashout), 0)::numeric as pago_cashout'),
                    \DB::raw('coalesce(sum(total_pago_qr), 0)::numeric as pago_qr'),
                    \DB::raw('coalesce(sum(total_multa), 0)::numeric as multa')
                )
                ->where('atm_id', $atm_id)
                ->get();

            if (count($balance_atms) > 0) {

                $item = $balance_atms[0];
                $transaccionado = $item->transaccionado;
                $depositado = $item->depositado;
                $reversado = $item->reversado;
                $cashout = $item->cashout;
                $pago_cashout = $item->pago_cashout;
                $pago_qr = $item->pago_qr;
                $multa = $item->multa;

                $amount = ($transaccionado + $depositado + $reversado + $cashout + $pago_cashout + $pago_qr + $multa);
            }
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $amount;
    }

    /**
     * Inicio de pantalla
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $list = [
            [
                'option' => 1,
                'option_checked' => '',
                'description' => 'Limite definido por el usuario',
                'amount' => 0,
                'amount_disabled' => false,
                'amount_type' => 'number',
                'message_user' => 'El limite definido por el usuario fué sobre pasado',
                'service_rule_id' => -1,
                'owner_id' => null,
                'atm_id' => null
            ],
            [
                'option' => 2,
                'option_checked' => '',
                'description' => 'Limite contra deuda',
                'amount' => null,
                'amount_disabled' => 'disabled',
                'amount_type' => 'text',
                'message_user' => 'El limite contra deuda fué sobre pasado',
                'service_rule_id' => -1,
                'owner_id' => null,
                'atm_id' => null
            ],
            [
                'option' => 3,
                'option_checked' => '',
                'description' => 'Sin limites',
                'amount' => 'Sin limites',
                'amount_disabled' => 'disabled',
                'amount_type' => 'text',
                'message_user' => '',
                'service_rule_id' => -1,
                'owner_id' => null,
                'atm_id' => null
            ]
        ];

        $data = [
            'headboard' => [
                'branch' => 'Sin información',
                'point_of_sale' => 'Sin información',
                'terminal' => 'Sin información',
                'user' => 'Sin información',
                'cashouts' => 0,
                'amount_available' => 0,
                'amount_available_view' => 0,
                'percentage' => 0
            ],
            'list' => $list,
        ];

        try {

            if (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {

                $user_in_group = \DB::table('users_x_groups')
                    ->select(
                        'group_id'
                    )
                    ->where('user_id', $this->user->id)
                    ->take(1)
                    ->get();

                if (count($user_in_group) > 0) {
                    $group_id = $user_in_group[0]->group_id;

                    //Obtener el atm_id con el id del usuario encargado del local.
                    $atm = \DB::table('users as u')
                        ->select(
                            'b.description as branch',
                            'pos.atm_id',
                            'pos.description as point_of_sale',
                            'a.owner_id',
                            'a.name as terminal',
                            'u.description as user'
                        )
                        ->join('branches as b', 'u.id', '=', 'b.user_id')
                        ->join('points_of_sale as pos', 'b.id', '=', 'pos.branch_id')
                        ->join('atms as a', 'a.id', '=', 'pos.atm_id')
                        ->where('b.user_id', $this->user->id) //Usuario 
                        ->whereIn('a.owner_id', [16, 21, 25]); //Solo para mini - terminales

                    \Log::info("query usuario-sucursal:");
                    \Log::info($atm->toSql());

                    $atm = $atm->get();

                    if (count($atm) > 0) {
                        $item = $atm[0];
                        $atm_id = $item->atm_id;
                        $owner_id = $item->owner_id;
                        $branch = $item->branch;
                        $point_of_sale = $item->point_of_sale;
                        $terminal = $item->terminal;
                        $user = $item->user;

                        for ($i = 0; $i < count($list); $i++) {
                            $list[$i]['owner_id'] = $owner_id;
                            $list[$i]['atm_id'] = $atm_id;
                        }

                        //\Log::info("atm_id: $atm_id");

                        //Preguntar si el atm tiene regla
                        $service_rule = \DB::table('service_rule as sr')
                            ->select(
                                'sr.idservice_rule as service_rule_id',
                                'sr.description',
                                'srp.value as amount',
                                'sro.option',
                                'sro.status'
                            )
                            ->join('service_rule_params as srp', 'sr.idservice_rule', '=', 'srp.service_rule_id')
                            ->join('service_rule_options as sro', 'sr.idservice_rule', '=', 'sro.service_rule_id')
                            ->where('sr.atm_id', $atm_id)
                            ->where('srp.param_id', 1) // Montos
                            ->where('sr.cashout', true);

                        //\Log::info('Query:');
                        //\Log::info($service_rule->toSql());

                        $service_rule = $service_rule->get();

                        if (count($service_rule) > 0) {

                            $indice = null;

                            foreach ($service_rule as $item) {
                                if ($item->option == 1) {
                                    $indice = 0;
                                    $amount_available = $item->amount;
                                    $list[$indice]['service_rule_id'] = $item->service_rule_id;
                                    $list[$indice]['amount'] = $amount_available; //Obtener el monto que se va actualizando
                                    $list[$indice]['description'] = $item->description; //Por si la descripción cambia.
                                } else if ($item->option == 2) {
                                    $indice = 1;
                                    $amount_available = $this->get_debt($atm_id);
                                    $list[$indice]['service_rule_id'] = $item->service_rule_id;
                                    $list[$indice]['amount'] = $amount_available;
                                    $list[$indice]['description'] = $item->description;
                                } else if ($item->option == 3) {
                                    $indice = 2;
                                    $amount_available = 1;
                                    $list[$indice]['service_rule_id'] = $item->service_rule_id;
                                    $list[$indice]['amount'] = 'Sin limite.';
                                    $list[$indice]['description'] = $item->description;
                                }

                                if ($indice !== null) {
                                    if (($indice + 1) == $item->option) {
                                        if ($item->status) {
                                            $list[$indice]['option_checked'] = 'checked';

                                            if ($indice != 2) {
                                                $amount_available_view = number_format($amount_available, 0, ',', '.');
                                            } else {
                                                $amount_available_view = 'Sin limite.';
                                            }
                                        }
                                    }
                                }
                            }

                            //\Log::info('list:');
                            //\Log::info($list);

                        } else {
                            $message = 'La terminal no cuenta con una regla de cashout.';
                        }
                    } else {
                        $message = 'Este usuario no tiene ninguna sucursal asignada.';
                    }
                } else {
                    $message = 'El usuario no se encuentra en grupos.';
                }
            } else {
                $message = 'Este usuario no tiene permiso de supervisor de mini-terminal.';
            }
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
            $message = 'Ocurrió un error al realizar la consulta de cashouts.';
        }

        if ($message !== '') {
            \Session::flash('error_message', $message);
        } else {
            \Session::flash('message', 'Consulta de cashouts exitosa.');
        }

        return view('service_rule_params.index', compact('data'));
    }

    /**
     * Guardar reglas de cashout
     *
     * @return \Illuminate\Http\Response
     */
    public function save(Request $request)
    {
        $option = $request['parameters']['option'];
        $description = $request['parameters']['description'];
        $status = $request['parameters']['status'];
        $amount = $request['parameters']['amount'];
        $message_user = $request['parameters']['message_user'];
        $owner_id = $request['parameters']['owner_id'];
        $atm_id = $request['parameters']['atm_id'];
        $service_rule_id = $request['parameters']['service_rule_id'];
        $param_id = 1;
        $ids = null;
        $message = 'Registro actualizado correctamente.';
        $status_update = 'success';

        try {
            if ($option != 1) {
                //$amount = null;
            }

            \Log::info("ATM_ID: $atm_id");

            if ($atm_id !== null and $atm_id !== '') {
                $service_rule_ids = \DB::table('service_rule_options as sro')
                    ->select(
                        \DB::raw("array_to_string(array_agg(sr.idservice_rule), ', ') as ids")
                    )
                    ->join('service_rule as sr', 'sr.idservice_rule', '=', 'sro.service_rule_id')
                    ->where('sr.atm_id', $atm_id);

                \Log::info("service_rule_ids:");
                \Log::info($service_rule_ids->toSql());

                $service_rule_ids = $service_rule_ids->get();

                if (count($service_rule_ids) > 0) {
                    $ids = $service_rule_ids[0]->ids;

                    if ($ids !== null) {
                        \DB::table('service_rule_options')
                            ->whereRaw("service_rule_id = any(array[$ids])")
                            ->update([
                                'status' => false, //Actualiza todos los registros relacionados al atm a false
                                'updated_at' => Carbon::now()
                            ]);
                    }

                    //Si existe verificar si cuenta con el parametro y actualizar.
                    if ($service_rule_id != -1) {
                        \DB::table('service_rule_options')
                            ->where('service_rule_id', $service_rule_id)
                            ->where('option', $option)
                            ->update([
                                'status' => $status,
                                'updated_at' => Carbon::now()
                            ]);

                        \DB::table('service_rule_params')
                            ->where('service_rule_id', $service_rule_id)
                            ->where('param_id', $param_id)
                            ->update([
                                'value' => $amount,
                            ]);

                        \Log::info('ACTUALIZADO.');
                        //Si no existe agregar.
                    } else {
                        $list_insert = [
                            'description' => $description,
                            'message_user' => $message_user,
                            'owner_id' => $owner_id,
                            'atm_id' => $atm_id,
                            'cashout' => true,
                            'created_at' => Carbon::now()
                        ];

                        $service_rule_insert = \DB::table('service_rule')
                            ->insert($list_insert);

                        //insert correcto
                        if ($service_rule_insert == 1) {
                            $service_rule = \DB::table('service_rule')
                                ->select(
                                    'idservice_rule as service_rule_id'
                                )
                                ->where('description', $description)
                                ->where('message_user', $message_user)
                                ->where('owner_id', $owner_id)
                                ->where('atm_id', $atm_id)
                                ->where('cashout', true)
                                ->get();

                            if (count($service_rule) > 0) {
                                $service_rule_id = $service_rule[0]->service_rule_id;

                                $list_insert = [
                                    'service_rule_id' => $service_rule_id,
                                    'param_id' => $param_id, //Descripción del parametro: amount
                                    'value' => $amount
                                ];

                                $service_rule_insert = \DB::table('service_rule_params')
                                    ->insert([
                                        'service_rule_id' => $service_rule_id,
                                        'param_id' => $param_id, //Descripción del parametro: amount
                                        'value' => $amount
                                    ]);

                                if ($service_rule_insert == 1) {
                                    \DB::table('service_rule_options')
                                        ->insert([
                                            'service_rule_id' => $service_rule_id,
                                            'option' => $option,
                                            'created_at' => Carbon::now(),
                                            'status' => true
                                        ]);
                                }
                            }
                        }

                        \Log::info('INSERTADO.');
                    }
                }
            } else {
                $message = 'El usuario no cuenta con una sucursal a cargo.';
                $status_update = 'error';
            }
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
            $message = 'No se pudo realizar la actualización.';
            $status_update = 'error';
        }

        $data = [
            'status' => $status_update,
            'message' => $message
        ];

        return $data;
    }
}
