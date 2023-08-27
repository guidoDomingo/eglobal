<?php

namespace App\Http\Controllers\TerminalInteractionMonitoring;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class PosBoxMovementController extends Controller
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
     * Display a list for all Users
     * @return |Response
     */
    public function index(Request $request)
    {
        $username = $this->user->username;
        $action = \Request::route()->getActionName();

        if (!$this->user->hasAccess('terminal_interaction_monitoring_pos_box_movement')) {
            \Log::error("El usuario: $username no tiene permisos para la realizar la acción: $action");
            \Session::flash('error_message', 'No posee permisos para realizar esta acción.');
            return redirect('/');
        }

        $user = \Sentinel::getUser();
        $user_id = $user->id;
        $branch_id = $user->branch_id;
        $atms = [];
        $opening_closing_list = [];
        $message = '';
        $pos_box = null;

        if (isset($request['timestamp'])) {
            $timestamp = $request['timestamp'];
            $pos_box_id = $request['pos_box_id'];
        } else {
            $timestamp = date("d/m/Y");
            $timestamp = "$timestamp 00:00:00 - $timestamp 23:59:59";
            $pos_box_id = null;
        }

        if ($timestamp !== null and $timestamp !== '') {
            $aux = explode(' - ', str_replace('/', '-', $timestamp));
            $from = date('Y-m-d H:i:s', strtotime($aux[0]));
            $to = date('Y-m-d H:i:s', strtotime($aux[1]));
        }

        //obtener atm.
        $terminal_interaction_login = \DB::table('terminal_interaction_login as til')
            ->select(
                'til.id'
            )
            ->where('til.user_id', $user_id)
            ->get();


        if (count($terminal_interaction_login) > 0) {

            $terminal_interaction_login_id = $terminal_interaction_login[0]->id;

            $terminal_interaction_access = \DB::table('terminal_interaction_access as tia')
                ->select(
                    \DB::raw("array_to_string(array_agg(tia.pos_box_id), ',') as pos_box_ids")
                )
                ->where('tia.terminal_interaction_login_id', $terminal_interaction_login_id)
                ->where('tia.status', true)
                ->get();

            if (count($terminal_interaction_access) > 0) {
                $pos_box_ids = $terminal_interaction_access[0]->pos_box_ids;

                $pos_box = \DB::table('pos_box as pb')
                    ->select(
                        'pb.id',
                        'a.name as description'
                    )
                    ->join('atms as a', 'a.id', '=', 'pb.atm_id')
                    ->whereRaw("pb.id in ($pos_box_ids)")
                    ->get();

                if (count($pos_box) > 0) {

                    foreach ($pos_box as $pos_box_item) {
                        //\Log::info('ITEM:');
                        //\Log::info($pos_box_item->id);

                        $pos_box_movement = \DB::table('pos_box_movement as pbm')
                            ->select(
                                'a.id as atm_id',
                                'a.name as atm',
                                'pbm.id',
                                'pbm.transaction_id',
                                't.description as turn',
                                'mt.description as movement',
                                'u.description as user',
                                \DB::raw("coalesce(to_char(pbm.created_at, 'DD/MM/YYYY HH24:MI:SS'), '') as created_at"),
                                \DB::raw("coalesce(to_char(pbm.created_at, 'YYYY-MM-DD HH24:MI:SS'), '') as created_at_filter"),
                                \DB::raw("pbm.amount::integer")
                            )
                            ->join('pos_box as pb', 'pb.id', '=', 'pbm.pos_box_id')
                            ->join('atms as a', 'a.id', '=', 'pb.atm_id')
                            ->join('points_of_sale as pos', 'a.id', '=', 'pos.atm_id')
                            ->join('branches as b', 'b.id', '=', 'pos.branch_id')
                            ->join('business_groups as bg', 'bg.id', '=', 'b.group_id')
                            ->join('turn as t', 't.id', '=', 'pbm.turn_id')
                            ->join('movement_type as mt', 'mt.id', '=', 'pbm.movement_type_id')
                            ->join('terminal_interaction_login as til', 'til.id', '=', 'pbm.terminal_interaction_login_id')
                            ->join('users as u', 'u.id', '=', 'til.user_id')
                            ->where('pb.id', $pos_box_item->id);

                        if ($pos_box_id !== null and $pos_box_id !== '') {
                            $pos_box_movement = $pos_box_movement->where('pb.id', $pos_box_id);
                        }

                        $pos_box_movement = $pos_box_movement->whereRaw("pbm.created_at between '{$from}' and '{$to}'")
                            ->orderBy('pbm.id', 'ASC')
                            ->get();

                        for ($i = 0; $i < count($pos_box_movement); $i++) {
                            //\Log::info('pos_box_movement:');
                            //\Log::info($pos_box_movement[$i]->id);

                            $opening_closing = [
                                'atm_id' => $pos_box_movement[$i]->atm_id,
                                'atm' => $pos_box_movement[$i]->atm,
                                'opening_date_time' => $pos_box_movement[$i]->created_at,
                                'opening_date_time_filter' => $pos_box_movement[$i]->created_at_filter,
                                'closing_date_time' => 'Sin fecha-hora de cierre.',
                                'closing_date_time_filter' => '',
                                'opening_user' => $pos_box_movement[$i]->user,
                                'closing_user' => 'Sin usuario de cierre.',
                                'transaction_count' => 0,
                                'transaction_sum' => 0
                            ];

                            if (isset($pos_box_movement[$i + 1])) {
                                $opening_closing['closing_date_time'] = $pos_box_movement[$i + 1]->created_at;
                                $opening_closing['closing_date_time_filter'] = $pos_box_movement[$i + 1]->created_at_filter;
                                $opening_closing['closing_user'] = $pos_box_movement[$i + 1]->user;

                                $atm_id = $pos_box_movement[$i]->atm_id;
                                $opening_created_filter = $pos_box_movement[$i]->created_at_filter;
                                $closing_created_filter = $pos_box_movement[$i + 1]->created_at_filter;

                                $transactions = \DB::table('transactions as t')
                                    ->select(
                                        \DB::raw('count(t.id)'),
                                        \DB::raw("trim(replace(to_char(sum(abs(t.amount))::numeric, '999G999G999G999'), ',', '.')) as sum")
                                    )
                                    //->join('points_of_sale as pos', 'pos.atm_id', '=', 't.atm_id')
                                    ->join('atms as a', 'a.id', '=', 't.atm_id')
                                    //->join('branches as b', 'b.id', '=', 'pos.branch_id')
                                    ->join('services_providers_sources as sps', 'sps.id', '=', 't.service_source_id')
                                    ->join('servicios_x_marca as sxm', function ($join) {
                                        $join->on('sxm.service_id', '=', 't.service_id');
                                        $join->on('sxm.service_source_id', '=', 't.service_source_id');
                                    })
                                    ->join('marcas as m', 'm.id', '=', 'sxm.marca_id')
                                    //->leftjoin('transactions_x_payments as txp', 't.id', '=', 'txp.transactions_id')
                                    //->leftjoin('payments as p', 'p.id', '=', 'txp.payments_id')
                                    ->where('t.status', 'success')
                                    ->whereRaw("t.transaction_type in (1,7,12)")
                                    ->where('a.id', $atm_id)
                                    ->whereRaw("t.created_at between '$opening_created_filter' and '$closing_created_filter'")
                                    ->groupBy('t.atm_id')
                                    ->get();


                                if (count($transactions) > 0) {
                                    $opening_closing['transaction_count'] = $transactions[0]->count;
                                    $opening_closing['transaction_sum'] = $transactions[0]->sum;
                                }

                                $i++;
                            }

                            array_push($opening_closing_list, $opening_closing);
                        }
                    }
                } else {
                    $message = "El/Los ATM's. no tiene/n caja creada.";
                }
            } else {
                $message = 'El usuario no tiene acceso a ninún ATM.';
            }
        } else {
            $message = 'El usuario no tiene acceso al módulo de interacciones.';
        }


        \Log::info('opening_closing_list:');
        \Log::info($opening_closing_list);

        $inputs = [
            'timestamp' => $timestamp,
            'pos_box_id' => $pos_box_id
        ];

        $data = [
            'lists' => [
                'records_list' => $opening_closing_list,
                'pos_box' => json_encode($pos_box),
            ],
            'inputs' => json_encode($inputs)
        ];

        if ($message !== '') {
            \Log::error("Falta más configuraciones para el usuario: $user_id");
            \Session::flash('error_message', $message);
            $data['lists']['records_list'] = [];
        }


        return view('terminal_interaction_monitoring.pos_box_movement.index', compact('data'));
    }

    function get_transactions_by_atm(Request $request)
    {

        $atm_id = $request['atm_id'];
        $opening_date_time_filter = $request['opening_date_time_filter'];
        $closing_date_time_filter = $request['closing_date_time_filter'];

        $transactions = [];

        $validate = ($opening_date_time_filter !== null and
            $opening_date_time_filter !== '' and
            $closing_date_time_filter !== null and
            $closing_date_time_filter !== ''
        ) ? true : false;

        //\Log::info("validate: $validate");

        if ($validate) {

            $transactions = \DB::table('transactions as t')
                ->select(
                    \DB::raw("trim(replace(to_char(t.id, '999G999G999G999'), ',', '.')) as transaction_id"),
                    'sps.description as provider',
                    \DB::raw("m.descripcion || ' - ' || sxm.descripcion as service"),
                    \DB::raw("coalesce(to_char(t.created_at, 'DD/MM/YYYY HH24:MI:SS'), '') as created_at"),
                    \DB::raw("trim(replace(to_char(t.amount, '999G999G999G999'), ',', '.')) as amount")
                )
                //->join('points_of_sale as pos', 'pos.atm_id', '=', 't.atm_id')
                ->join('atms as a', 'a.id', '=', 't.atm_id')
                //->join('branches as b', 'b.id', '=', 'pos.branch_id')
                ->join('services_providers_sources as sps', 'sps.id', '=', 't.service_source_id')
                ->join('servicios_x_marca as sxm', function ($join) {
                    $join->on('sxm.service_id', '=', 't.service_id');
                    $join->on('sxm.service_source_id', '=', 't.service_source_id');
                })
                ->join('marcas as m', 'm.id', '=', 'sxm.marca_id')
                //->leftjoin('transactions_x_payments as txp', 't.id', '=', 'txp.transactions_id')
                //->leftjoin('payments as p', 'p.id', '=', 'txp.payments_id')
                ->where('t.status', 'success')
                ->whereRaw("t.transaction_type in (1,7,12)")
                ->where('a.id', $atm_id)
                ->whereRaw("t.created_at between '$opening_date_time_filter' and '$closing_date_time_filter'")
                ->orderBy('t.id', 'asc')
                ->get();
        }

        return $transactions;
    }
}
