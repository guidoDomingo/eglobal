<?php

namespace App\Http\Controllers\TerminalInteraction;

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

        $records_list = \DB::table('pos_box_movement as pbm')
            ->select(
                'pbm.id',
                'pbm.transaction_id',
                't.description as turn',
                'mt.description as movement',
                'u.description as user',
                \DB::raw("coalesce(to_char(pbm.created_at, 'DD/MM/YYYY HH24:MI:SS'), '') as created_at"),
                \DB::raw("pbm.amount::integer")
            )
            ->join('turn as t', 't.id', '=', 'pbm.turn_id')
            ->join('movement_type as mt', 'mt.id', '=', 'pbm.movement_type_id')
            ->join('terminal_interaction_login as til', 'til.id', '=', 'pbm.terminal_interaction_login_id')
            ->join('users as u', 'u.id', '=', 'til.user_id')
            ->join('branches as b', 'b.id', '=', 'u.branch_id')
            ->where('b.id', $branch_id);

        if (isset($request['timestamp'])) {
            $timestamp = $request['timestamp'];
            $movement_type_id = $request['movement_type_id'];
            $turn_id = $request['turn_id'];
            $user_search_id = $request['user_id'];
            $record_limit = $request['record_limit'];

            if ($movement_type_id !== '') {
                $movement_type_id = intval($movement_type_id);
                $records_list = $records_list->where('mt.id', $movement_type_id);
            }

            if ($turn_id !== '') {
                $turn_id = intval($turn_id);
                $records_list = $records_list->where('t.id', $turn_id);
            }

            if ($user_search_id !== '') {
                $user_search_id = intval($user_search_id);
                $records_list = $records_list->where('u.id', $user_search_id);
            }

            if ($timestamp !== null and $timestamp !== '') {
                $aux  = explode(' - ', str_replace('/', '-', $timestamp));
                $from = date('Y-m-d H:i:s', strtotime($aux[0]));
                $to = date('Y-m-d H:i:s', strtotime($aux[1]));
                $records_list = $records_list->whereRaw("pbm.created_at between '{$from}' and '{$to}'");
            }

            $records_list = $records_list->orderBy('pbm.id', 'asc');

            if ($record_limit !== '') {
                $records_list = $records_list->take(intval($record_limit));
            }
        } else {
            $timestamp = date("d/m/Y");
            $timestamp = "$timestamp 00:00:00 - $timestamp 23:59:59";
            $movement_type_id = '';
            $turn_id = '';
            $user_search_id = '';
            $record_limit = '';

            $records_list = $records_list->whereRaw("to_char(pbm.created_at, 'DD/MM/YYYY') = to_char(now(), 'DD/MM/YYYY')");
        }

        \Log::info($records_list->toSql());

        $records_list = $records_list->get();

        $users = \DB::table('users as u')
            ->select(
                'u.id',
                'u.description'
            )
            ->join('terminal_interaction_login as til ', 'u.id', '=', 'til.user_id')
            //->where('bgl.business_group_id', $business_group_id)
            ->orderBy('u.description', 'asc')
            ->get();

        $movement_types = \DB::table('movement_type')
            ->select(
                'id',
                'description'
            )
            ->whereRaw("description = 'Apertura' or description = 'Cierre'")
            ->orderBy('description', 'asc')
            ->get();

        $turns = \DB::table('turn')
            ->select(
                'id',
                'description'
            )
            ->orderBy('id', 'asc')
            ->get();

        $inputs = [
            'timestamp' => $timestamp,
            'movement_type_id' => $movement_type_id,
            'turn_id' => $turn_id,
            'user_id' => $user_search_id,
            'record_limit' => $record_limit
        ];

        $data = [
            'lists' => [
                'records_list' => $records_list,
                'users' => json_encode($users),
                'movement_types' => json_encode($movement_types),
                'turns' => json_encode($turns)
            ],
            'inputs' => json_encode($inputs)
        ];

        return view('terminal_interaction.pos_box_movement.index', compact('data'));
    }
}
