<?php

namespace App\Http\Controllers\TerminalInteractionMonitoring;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

use Carbon\Carbon;

class PosBoxController extends Controller
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

    /**
     * Display a list for all Users
     * @return |Response
     */
    public function index(Request $request)
    {
        $username = $this->user->username;
        $action = \Request::route()->getActionName();

        if (!$this->user->hasAccess('terminal_interaction_monitoring_pos_box')) {
            \Log::error("El usuario: $username no tiene permisos para la realizar la acción: $action");
            \Session::flash('error_message', 'No posee permisos para realizar esta acción.');
            return redirect('/');
        }

        $user = \Sentinel::getUser();
        $user_id = $user->id;

        $records_list = \DB::table('atms as a')
            ->select(
                'bg.description as bg_description',
                'b.description as b_description',
                'pos.description as pos_description',
                'a.name as a_description',
                'a.id as a_id',
                'pb.id as pb_id',
                \DB::raw("case when pb.id is not null then 'Si' else 'No' end as box"),
                \DB::raw("case when pb.status = true then 'Activo' else 'Inactivo' end as status"),
                \DB::raw("coalesce(to_char(pb.created_at, 'DD/MM/YYYY HH24:MI:SS'), '') as created_at")
            )
            ->join('points_of_sale as pos', 'a.id', '=', 'pos.atm_id')
            ->join('branches as b', 'b.id', '=', 'pos.branch_id')
            ->join('business_groups as bg', 'bg.id', '=', 'b.group_id')
            ->leftjoin('pos_box as pb', 'a.id', '=', 'pb.atm_id');

        if (isset($request['timestamp'])) {
            $timestamp = $request['timestamp'];
            $record_limit = $request['record_limit'];
            $atm_id = $request['atm_id'];

            if ($atm_id !== '') {
                $records_list = $records_list->where('a.id', intval($atm_id));
            }

            if ($timestamp !== null and $timestamp !== '') {
                $aux  = explode(' - ', str_replace('/', '-', $timestamp));
                $from = date('Y-m-d H:i:s', strtotime($aux[0]));
                $to = date('Y-m-d H:i:s', strtotime($aux[1]));
                $records_list = $records_list->whereRaw("pb.created_at between '{$from}' and '{$to}'");
            }
        } else {
            $timestamp = date("d/m/Y");
            $timestamp = "$timestamp 00:00:00 - $timestamp 23:59:59";
            $record_limit = '';
            $atm_id = '';
        }

        if ($record_limit !== '') {
            $records_list = $records_list->take(intval($record_limit));
        }

        \Log::info($records_list->toSql());

        $records_list = $records_list->get();

        //\Log::info($records_list);

        $atms = \DB::table('atms as a')
            ->select(
                'a.id',
                'a.name as description'
            )
            ->get();

        /*$branches = \DB::table('branches')
            ->select(
                'id',
                'description'
            )
            ->orderBy('description', 'asc')
            ->whereRaw('deleted_at is null')
            ->whereRaw('group_id is not null')
            ->get();

        $points_of_sale = \DB::table('points_of_sale')
            ->select(
                'id',
                'description'
            )
            ->whereRaw('deleted_at is null')
            ->whereRaw('branch_id is not null')
            ->orderBy('description', 'asc')
            ->get();*/

        $inputs = [
            'timestamp' => $timestamp,
            'record_limit' => $record_limit,
            'atm_id' => $atm_id,
        ];

        $data = [
            'lists' => [
                'records_list' => $records_list,
                'atms' => json_encode($atms),
            ],
            'inputs' => json_encode($inputs)
        ];

        return view('terminal_interaction_monitoring.pos_box.index', compact('data'));
    }

    public function edit(Request $request)
    {
        $data = [
            'error' => false,
            'message' => 'Acción exitosa.',
            'list' => []
        ];

        try {
            $parameters = $request['parameters'];
            $a_id = $parameters['a_id'];
            $pb_id = $parameters['pb_id'];
            $box = $parameters['box'];
            $status_description = $parameters['status'];

            //\Log::info('PARAMETROS:');
            //\Log::info($parameters);

            $status = 0;
            $insert = false;

            if ($box == 'Si') {
                if ($status_description == 'Activo') {
                    $status = 0;
                } else {
                    $status = 1;
                }
            } else {
                $insert = true;
            }

            $carbon_now = Carbon::now();

            if ($insert) {
                $insert = [
                    'atm_id' => $a_id,
                    'created_at' => $carbon_now,
                    'status' => true
                ];

                \DB::table('pos_box')->insert($insert);
            } else {
                $update = [
                    'updated_at' => $carbon_now,
                    'status' => $status
                ];

                \DB::table('pos_box')->where('id', $pb_id)->update($update);
            }

            \Log::info("Box: $box, Status: $status, pb_id: $pb_id");
        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }

        return $data;
    }
}
