<?php

/**
 * Created by PhpStorm.
 * User: thavo
 * Date: 25/04/17
 * Time: 04:35 PM
 */

namespace App\Services;

use App\Models\Owner;
use App\Models\Branch;
use App\Models\Pos;
use App\Models\ServiceProviderProduct;
use Carbon\Carbon;
use App\Models\User;
use App\Models\Transactions_batch;
use App\Models\Atm;
use App\Models\Atmnew;
use App\Models\Contract;
use App\Models\Group;
use PhpParser\Node\Stmt\TryCatch;
use Mail;

class ReportServices
{
    protected $input;

    public function __construct($var)
    {
        $this->input = $var;
        ini_set('memory_limit', '1024M');
        $this->user = \Sentinel::getUser();

        //\Log::info('VALUES:');
        //\Log::info($this->input);
        //DIE();
    }

    /*TRANSAcTIONS*/
    public function transactionsReports()
    {

        $branches = [];

        try {
            //Redes
            $whereGroup = "";
            $whereOwner = "";
            $whereBranch = "";
            $wherePos = "points_of_sale.deleted_at is null ";

            ///////////////////////////////////////////////////////////////
            if (!\Sentinel::getUser()->inRole('mini_terminal') && !\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                $branches   = Branch::orderBy('description')->where(function ($query) use ($whereBranch) {
                    if (!empty($whereBranch)) {
                        $query->whereRaw($whereBranch);
                    }
                })->get()->pluck('description', 'id');
                $branches->prepend('Todos', '0');
            } else if (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {

                if ($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11) {
                    $whereOwner = "owners.id = " . $this->user->owner_id;
                    $whereBranch = "branches.owner_id = " . $this->user->owner_id;
                    $wherePos .= "and points_of_sale.owner_id = " . $this->user->owner_id;

                    $supervisor = \DB::table('users_x_groups')->where('user_id', $this->user->id)->first();

                    $branchess = \DB::table('branches')
                        ->select(['branches.description', 'users.username', 'users.id'])
                        ->join('users', 'branches.user_id', '=', 'users.id')
                        ->join('role_users', 'users.id', '=', 'role_users.user_id')
                        ->where('role_users.role_id', 22)
                        ->where('branches.group_id', $supervisor->group_id)
                        ->get();
                    $branches = [];
                    foreach ($branchess as $key => $branch) {
                        $branches[$branch->id] = $branch->description . ' | ' . $branch->username;
                    }
                }
            } else {
                $branches = \DB::table('branches')
                    ->select(['branches.description', 'users.username', 'users.id'])
                    ->join('users', 'branches.user_id', '=', 'users.id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->where('branches.user_id', $this->user->id)
                    ->get();
            }
            ///////////////////////////////////////////////////////////////

            //Redes
            $groups     = Group::orderBy('business_groups.description')->where(function ($query) use ($whereGroup) {
                if (!empty($whereGroup)) {
                    $query->whereRaw($whereGroup);
                }
            })->get()->pluck('description', 'id');
            $groups->prepend('Todos', '0');

            $owners     = Owner::orderBy('owners.name')->where(function ($query) use ($whereOwner) {
                if (!empty($whereOwner)) {
                    $query->whereRaw($whereOwner);
                }
            })->get()->pluck('name', 'id');
            $owners->prepend('Todos', '0');

            $pdvs = Pos::orderBy('description')
                ->where(function ($query) use ($wherePos) {
                    if (!empty($wherePos)) {
                        $query->whereRaw($wherePos);

                        \Log::info("Filtro de pos: $wherePos");
                    }
                })
                ->with('Atm')
                ->get();

            $pos = [];
            $item = array();
            $item[0] = 'Todos';

            foreach ($pdvs  as $pdv) {
                $item[$pdv->id] = $pdv->description . ' - ' . $pdv->Atm->code;
                $pos = $item;
            }
            $status = array('0' => 'Todos', 'success' => 'Aprobado', 'canceled' => 'Cancelado', 'error' => 'Error', 'rollback' => 'Reversado', 'iniciated' => 'Iniciado', 'error dispositivo' => 'Error de dispositivo', 'error dispositivo' => 'Error de dispositivo', 'devolucion' => 'Devolución', 'inconsistency' => 'Inconsistencia');
            $atmType = array('0' => 'Todos', 'da' => 'App Billetaje', 'ws' => 'Web Service', 'at' => 'Atm');
            $payment_methods = array('0' => 'Todos', 'efectivo' => 'Efectivo', 'canje' => 'Canje', 'QR' => 'Todos QR', 'TC' => 'Tarjeta de crédito', 'TD' => 'Tarjeta de débito');

            $services   = ServiceProviderProduct::with('WebServiceProvider')->orderBy('service_provider_id', 'DESC')->get();
            $services_data = [];
            $service_item = array();
            if ($this->user->hasAccess('ticketea') && !$this->user->hasAccess('superuser')) {
                $service_item[28] = 'Ticketea - Venta de tickets';
                $services_data = $service_item;
            } else {
                $service_item[0] = 'Todos';
                $service_item[-2] = 'Netel';
                $service_item[-4] = 'Pronet';
                $service_item[-6] = 'Practipago';
                $service_item[-7] = 'Infonet';
                $service_item[-8] = 'Toval';
                $service_item[-10] = 'Netel Trex';
                $service_item[-11] = 'Bancard Ventas QR';
                foreach ($services  as $service) {
                    if ($service->id <> 100) //excluir ventas qr bancard
                    {
                        if ($service->id <> 27 && $service->id <> -1 && $service->WebServiceProvider->name <> 'Toval - Integraciones' && $service->WebServiceProvider->name <> 'Momo - Ken') {
                            $service_item[$service->id] = $service->WebServiceProvider->name . ' - ' . $service->description;
                            $services_data = $service_item;
                        }
                    }
                }
            }

            $resultset = array(
                'target'        => 'Transacciones',
                'groups'        => $groups,
                'owners'        => $owners,
                'branches'      => $branches,
                'pos'           => $pos,
                'status'        => $status,
                'type'          => $atmType,
                'services_data' => $services_data,
                'payment_methods' => $payment_methods,
                'group_id'      => 0,
                'owner_id'      => 0,
                'branch_id'     => 0,
                'pos_id'        => 0,
                'status_set'    => 0,
                'payment_methods_set' => 0,
                'type_set'    => 0,
                'service_id'    => 0,
                'service_request_id'    => '',
                'user_id'      => 0,
                'show_alert' => 'NO',
                'pos_active' => 'on',
                'transaction_id' => null,
                'amount' => null,
                'search' => false
            );

            return $resultset;
        } catch (\Exception $e) {
            $error_detail = [
                'from' => 'CMS',
                'message' => '[transactionsReports] Ocurrió un error al realizar la búsqueda.',
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine(),
                'user' => [
                    'user_id' => $this->user->id,
                    'username' => $this->user->username,
                    'description' => $this->user->description
                ]
            ];

            \Log::error($error_detail['message'], [$error_detail]);

            return false;
        }
    }

    public function transactionsSearch()
    {
        try {



            $query_to_export = '';
            $transactions = [];
            $total_transactions = 0;
            $input = $this->input;

            //\Log::info('POS_ACTIVE:', [$input]);

            $where = "t.transaction_type in (1,7,11,12,13, 17) and ";

            $transaction_id = null;

            if (isset($input['transaction_id'])) {
                if ($input['transaction_id'] !== null and $input['transaction_id'] !== '') {
                    $transaction_id = $input['transaction_id'];
                    $where .= "t.id = $transaction_id and ";
                }
            }


            if ($transaction_id == null) {
                /*Busqueda minusiosa*/
                if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
                    $where .= "t.id = {$input['context']} OR ";
                    $where .= "t.referencia_numero_1 = '{$input['context']}' and ";
                } else {

                    /*SET DATE RANGE*/
                    $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                    $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                    $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                    $where .= "t.created_at between '{$daterange[0]}' and '{$daterange[1]}' and ";

                    if (isset($input['group_id'])) {
                        $where .= ($input['group_id'] <> 0) ? "b.group_id = " . $input['group_id'] . " and " : "";
                    }

                    /*SET OWNER*/
                    if (!$this->user->hasAccess('superuser')) {
                        if (($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11)) {
                            $where .= "t.owner_id = " . $this->user->owner_id . " and ";
                        } else {
                            if (isset($input['owner_id'])) {
                                $where .= ($input['owner_id'] <> 0) ? "t.owner_id = " . $input['owner_id'] . " and " : "";
                            }
                        }
                        //USER GET BRANCH    

                        if ($this->user->branch_id <> null) {
                            $input['branch_id'] = $this->user->branch_id;
                        }
                    } else {
                        if (isset($input['owner_id'])) {
                            $where .= ($input['owner_id'] <> 0) ? "t.owner_id = " . $input['owner_id'] . " and " : "";
                        }
                    }

                    if (isset($input['branch_id'])) {
                        $where .= ($input['branch_id'] <> 0) ? "pos.branch_id = " . $input['branch_id'] . " and " : "";
                    }

                    if (isset($input['payment_method_id'])) {

                        if ($input['payment_method_id'] == 'QR') {
                            $where .= "p.tipo_pago in ('TC','TD','DC','QR') and ";
                        } else {
                            //$where .= ($input['payment_method_id'] <> '0')?'p.tipo_pago = '.$input['payment_method_id'].' and ':'';                        
                            $where .= ($input['payment_method_id'] <> '0') ? "p.tipo_pago = '{$input['payment_method_id']}' and " : "";
                        }
                    }


                    /**
                     * Filtro para Puntos de Ventas
                     */
                    //\Log::debug(['pos_id_antell' => $input['pos_id']]);

                    if (isset($input['pos_id'])) {
                        $where .= ($input['pos_id'] <> 0) ? "pos.id = " . $input['pos_id'] . " and " : "";
                    }

                    $where .= ($input['status_id'] <> "0") ? "t.status =  '{$input['status_id']}' and " : "";

                    if (isset($input['type'])) {
                        $where .= ($input['type'] <> "0") ? "a.type =  '{$input['type']}' and " : "";
                    }

                    if (\Sentinel::getUser()->inRole('mini_terminal')) {
                        //obtener los branches asociados a un usuario
                        $branches = \DB::table('points_of_sale')
                            ->select('branch_id')
                            ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                            ->where('branches.user_id', '=', $this->user->id)
                            ->first();

                        $where .= ' pos.branch_id = ' . $branches->branch_id . ' and ';
                    }

                    if (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                        $supervisor = \DB::table('users_x_groups')->where('user_id', $this->user->id)->first();
                        $branches = \DB::table('branches')
                            ->select(['branches.description', 'users.username', 'users.id'])
                            ->join('users', 'branches.user_id', '=', 'users.id')
                            ->join('role_users', 'users.id', '=', 'role_users.user_id')
                            ->where('role_users.role_id', 22)
                            ->where('branches.group_id', $supervisor->group_id)
                            ->get();

                        $atms = \DB::table('branches')
                            ->select(['points_of_sale.atm_id'])
                            ->join('users', 'branches.user_id', '=', 'users.id')
                            ->join('role_users', 'users.id', '=', 'role_users.user_id')
                            ->join('points_of_sale', 'branches.id', '=', 'points_of_sale.branch_id')
                            ->where('role_users.role_id', 22)
                            ->where('branches.group_id', $supervisor->group_id)
                            ->whereNotNull('atm_id')
                            ->where(function ($query) use ($input) {
                                if (!empty($input['user_id'])) {
                                    $query->where('branches.user_id', $input['user_id']);
                                }
                            })
                            ->pluck('atm_id', 'atm_id');
                        $atm_id = '(';
                        foreach ($atms as $id_atm => $atm) {
                            $atm_id .= $atm . ', ';
                        }

                        $atm_id = rtrim($atm_id, ', ');
                        $atm_id .= ')';

                        //$where .= ' branches.user_id = '.$input['user_id'];
                        $where .= ' a.id in ' . $atm_id;
                    }

                    if ($input['service_id'] == -2) {
                        $where .= 't.service_source_id = 1';
                        if ($input['service_request_id'] <> '0') {
                            $where .= ' and t.service_id = ' . $input['service_request_id'];
                        }
                    } elseif ($input['service_id'] == -4) {
                        $where .= 't.service_source_id = 4';
                        if ($input['service_request_id'] <> '0') {
                            $where .= ' and t.service_id = ' . $input['service_request_id'];
                        }
                    } elseif ($input['service_id'] == -6) {
                        $where .= 't.service_source_id = 6';
                        if ($input['service_request_id'] <> '0') {
                            $where .= ' and t.service_id = ' . $input['service_request_id'];
                        }
                    } elseif ($input['service_id'] == -7) {
                        $where .= 't.service_source_id = 7';
                        if ($input['service_request_id'] <> '0') {
                            $where .= ' and t.service_id = ' . $input['service_request_id'];
                        }
                    } elseif ($input['service_id'] == -8) {
                        $where .= 't.service_source_id = 8';
                        if ($input['service_request_id'] <> '0') {
                            $where .= ' and t.service_id = ' . $input['service_request_id'];
                        }
                    } elseif ($input['service_id'] == -10) {
                        $where .= 't.service_source_id = 10';
                        if ($input['service_request_id'] <> '0') {
                            $where .= ' and t.service_id = ' . $input['service_request_id'];
                        }
                    } elseif ($input['service_id'] == -11) {
                        $where .= 't.service_source_id = 11';
                        $where .= ' and t.service_id = 100';
                    } elseif ($input['service_id'] == 66) {
                        $where .= 't.service_source_id = 8 and t.service_id= 14';
                        if ($input['service_request_id'] <> '0') {
                            $where .= ' and t.service_id = ' . $input['service_request_id'];
                        }
                    } elseif ($input['service_id'] == 59) {
                        $where .= 't.service_source_id = 8 and t.service_id= 3';
                        if ($input['service_request_id'] <> '0') {
                            $where .= ' and t.service_id = ' . $input['service_request_id'];
                        }
                    } elseif ($input['service_id'] == 57) {
                        $where .= 't.service_source_id = 8 and t.service_id= 4';
                        if ($input['service_request_id'] <> '0') {
                            $where .= ' and t.service_id = ' . $input['service_request_id'];
                        }
                    } elseif ($input['service_id'] == 58) {
                        $where .= 't.service_source_id = 8 and t.service_id= 5';
                        if ($input['service_request_id'] <> '0') {
                            $where .= ' and t.service_id = ' . $input['service_request_id'];
                        }
                    } elseif ($input['service_id'] == 67) {
                        $where .= 't.service_source_id = 8 and t.service_id= 15';
                        if ($input['service_request_id'] <> '0') {
                            $where .= ' and t.service_id = ' . $input['service_request_id'];
                        }
                    } elseif ($this->user->hasAccess('ticketea') && !$this->user->hasAccess('superuser')) {
                        $where .= 't.service_id = 28';
                    } else {
                        $where .= ($input['service_id'] <> 0) ? "t.service_id = " . $input['service_id'] . " and service_source_id = 0" : "";
                    }
                }

                /**
                 * Filtro para el monto.
                 */

                if (isset($input['amount'])) {
                    if ($input['amount'] !== null  and $input['amount'] !== '') {
                        $where .= "t.amount = " . $input['amount'] . " and ";
                    }
                }
            }


            $where = trim($where);
            $where = trim($where, 'and');
            $where = trim($where);

            //\Log::info("$where");

            $transactions = \DB::table('transactions as t')
                ->select(
                    't.id',
                    \DB::raw("trim(replace(to_char(t.amount, '999G999G999G999'), ',', '.')) as amount"),
                    't.service_id',
                    'service_request_id',
                    't.atm_transaction_id',
                    't.status',
                    't.status as estado',
                    't.status_description',
                    't.status_description as estado_descripcion',
                    't.identificador_transaction_id',
                    't.factura_numero',
                    't.transaction_type',
                    \DB::raw("to_char(t.created_at, 'DD/MM/YYYY HH24:MI:SS') as created_at"),
                    \DB::raw("to_char(t.created_at, 'DD/MM/YYYY') as fecha"),
                    \DB::raw("to_char(t.created_at, 'HH24:MI:SS') as hora"),
                    /*\DB::raw(
                        "
                        trim(replace(to_char((
                            case when (t.amount < 0) 
                            then t.amount * -1 
                            else t.amount end), '999G999G999G999'
                        ), ',', '.')) as valor_transaccion"
                    ),*/
                    't.amount as valor_transaccion',
                    \DB::raw("0 as commission_amount"),
                    'p.id as cod_pago',
                    'p.tipo_pago as forma_pago',
                    'p.valor_a_pagar',
                    'p.valor_recibido',
                    'valor_entregado',
                    't.identificador_transaction_id as identificador_transaccion',
                    't.factura_numero as factura_nro',
                    'pos.description as sede',
                    //'tt.reprinted',
                    /*\DB::raw("
                        (case 
                        when (t.owner_id = 2) then 'Antell'
                        when (t.owner_id = 11) then 'Eglobal' 
                        else t.owner_id::text end) as owner_id
                    "),*/
                    //'o.name as owner_id',
                    //\DB::raw("(select o.name from owners o where o.id = t.owner_id) as owner_id"),
                    'o.name as owner_id',
                    'referencia_numero_1',
                    'referencia_numero_2',
                    'referencia_numero_1 as ref1',
                    'referencia_numero_2 as ref2',
                    'a.code as codigo_cajero',
                    'a.code as code',
                    't.service_source_id',
                    'sp.name as provider',
                    'spp.description as servicio',
                    \DB::raw("
                        (case when (t.service_source_id <> 0) then
                            (select sps.description 
                            from services_providers_sources sps 
                            where sps.id = t.service_source_id 
                            limit 1)
                        else 
                            sp.name
                        end) as proveedor
                    "),
                    \DB::raw("
                        (case when (t.service_source_id <> 0) then
                            case when (t.service_source_id = 8) then
                                (select sop.service_description 
                                from services_ondanet_pairing sop 
                                where sop.service_request_id = t.service_id 
                                and sop.service_source_id = t.service_source_id 
                                limit 1)
                            else 
                                (select sop.service_description 
                                from services_ondanet_pairing sop 
                                where sop.service_request_id = t.service_id 
                                and sop.service_source_id = t.service_source_id 
                                limit 1)
                            end
                        else 
                            spp.description
                        end) as tipo
                    "),
                    'mtr.reversion_id'
                )
                ->join('points_of_sale as pos', 'pos.atm_id', '=', 't.atm_id')
                ->join('atms as a', 'a.id', '=', 't.atm_id')
                ->join('owners as o', 'o.id', '=', 't.owner_id')
                ->leftjoin('service_provider_products as spp', 'spp.id', '=', 't.service_id')
                ->leftjoin('service_providers as sp', 'sp.id', '=', 'spp.service_provider_id')
                //->leftjoin('transaction_tickets as tt', 'tt.transaction_id', '=', 't.id')
                ->leftjoin('transactions_x_payments as txp', 't.id', '=', 'txp.transactions_id')
                ->leftjoin('payments as p', 'p.id', '=', 'txp.payments_id')
                ->leftjoin('branches as b', 'b.id', '=', 'pos.branch_id')
                ->leftjoin('mt_recibos_reversiones as mtr', 't.id', '=', 'mtr.transaction_id')
                ->whereRaw("$where")
                //->orderBy('p.id', 'desc')
                ->orderBy('t.id', 'desc');


            if (isset($input['reservationtime'])) {
                $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                $to = date('Y-m-d H:i:s', strtotime($daterange[0]));
                $from = date('Y-m-d H:i:s', strtotime($daterange[1]));
                $days = \DB::select("select ('{$from}'::date - '{$to}'::date) + 1 as days");
                $days = $days[0]->days;
            } else {
                $days = 0;
            }

            //\Log::info('SQL:');
            //\Log::info($transactions->toSql());

            if ($days > 1 and $transaction_id == null) {
                $query_to_export = $transactions->toSql();
                $transactions = [];
                $total_transactions = [];
                $transactions_total = 0;
            } else {
                $transactions = $transactions->paginate(20);
                $transactions_total = $transactions->total();

                $total_transactions = \DB::table('transactions as t')
                    ->select(
                        \DB::raw("
                                trim(
                                    replace(
                                        to_char(sum(abs(t.amount)), '999G999G999G999G999'), ',', '.'
                                    )
                                ) as monto
                            ")
                    )
                    ->join('points_of_sale as pos', 'pos.atm_id', '=', 't.atm_id')
                    ->join('atms as a', 'a.id', '=', 't.atm_id')
                    ->leftjoin('transactions_x_payments as txp', 't.id', '=', 'txp.transactions_id')
                    ->leftjoin('payments as p', 'p.id', '=', 'txp.payments_id')
                    ->leftjoin('branches as b', 'b.id', '=', 'pos.branch_id')
                    ->whereRaw("$where");

                //\Log::info('SQL:');
                //\Log::info($total_transactions->toSql());

                $total_transactions = $total_transactions->get();

                if (count($total_transactions) > 0) {
                    $total_transactions = $total_transactions[0]->monto;
                } else {
                    $total_transactions = 0;
                }

                /*foreach($transactions as $transaction){
                                //configurar labels de estado
                        if($transaction->status == 'success'){
                            $transaction->status =  '<span class="label label-success">'.$transaction->status.'</span>';
                        }elseif($transaction->status == 'canceled' || $transaction->status == 'iniciated'){
                            $transaction->status =  '<span class="label label-warning">'.$transaction->status.'</span>';
                        }elseif($transaction->status == 'inconsistency'){
                            $transaction->status =  '<span class="label label-danger">'.'Inconsistencia'.'</span>';
                        }else{
                            $transaction->status =  '<span class="label label-danger">'.$transaction->status.'</span>';
                        }

                        if($transaction->service_source_id <> 0){
                            if($transaction->service_source_id == 8)
                            {
                                $serv_provider = \DB::table('services_providers_sources')->where('id',$transaction->service_source_id)->first();
                                $transaction->provider = $serv_provider->description;
                                $service_data = \DB::table('services_ondanet_pairing')->where('service_request_id',$transaction->service_request_id)->
                                where('service_source_id',$transaction->service_source_id)->first();
                                $transaction->servicio = isset($service_data->service_description)?$service_data->service_description:'';
                            }else
                            {
                                $serv_provider = \DB::table('services_providers_sources')->where('id',$transaction->service_source_id)->first();
                                $transaction->provider = $serv_provider->description;
                                $service_data = \DB::table('services_ondanet_pairing')->where('service_request_id',$transaction->service_id)->
                                where('service_source_id',$transaction->service_source_id)->first();
                                $transaction->servicio = isset($service_data->service_description)?$service_data->service_description:'';
                            }
                        }
                    }*/
            }



            /*Carga datos del formulario*/
            $whereGroup = "";
            $whereOwner = "";
            $whereBranch = "";
            $wherePos = "points_of_sale.atm_id is not null ";

            if (isset($input['pos_active'])) {
                if ($input['pos_active'] == 'on') {
                    $wherePos .= "and points_of_sale.deleted_at is null ";
                }
            }

            if (isset($input['branch_id'])) {
                if ($input['branch_id'] !== null and $input['branch_id'] !== '0') {
                    $wherePos .= "and points_of_sale.branch_id = " . $input['branch_id'] . " ";
                }
            }

            if (!\Sentinel::getUser()->inRole('mini_terminal') && !\Sentinel::getUser()->inRole('supervisor_miniterminal')) {

                $branches   = Branch::orderBy('description')->where(function ($query) use ($whereBranch) {
                    if (!empty($whereBranch)) {
                        $query->whereRaw($whereBranch);
                    }
                })->get()->pluck('description', 'id');
                $branches->prepend('Todos', '0');
            } else if (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {

                if ($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11) {
                    $whereOwner = "owners.id = " . $this->user->owner_id;
                    $whereBranch = "branches.owner_id = " . $this->user->owner_id;
                    $wherePos = "and points_of_sale.owner_id = " . $this->user->owner_id;

                    $supervisor = \DB::table('users_x_groups')->where('user_id', $this->user->id)->first();

                    $branchess = \DB::table('branches')
                        ->select(['branches.description', 'users.username', 'users.id'])
                        ->join('users', 'branches.user_id', '=', 'users.id')
                        ->join('role_users', 'users.id', '=', 'role_users.user_id')
                        ->where('role_users.role_id', 22)
                        ->where('branches.group_id', $supervisor->group_id)
                        ->get();
                    $branches = [];
                    foreach ($branchess as $key => $branch) {
                        $branches[$branch->id] = $branch->description . ' | ' . $branch->username;
                    }
                }
            } else {
                $branches = \DB::table('branches')
                    ->select(['branches.description', 'users.username', 'users.id'])
                    ->join('users', 'branches.user_id', '=', 'users.id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->where('branches.user_id', $this->user->id)
                    ->get();
            }
            //Redes
            $groups     = Group::orderBy('business_groups.description')->where(function ($query) use ($whereGroup) {
                if (!empty($whereGroup)) {
                    $query->whereRaw($whereGroup);
                }
            })->get()->pluck('description', 'id');
            $groups->prepend('Todos', '0');

            $owners     = Owner::orderBy('owners.name')->where(function ($query) use ($whereOwner) {
                if (!empty($whereOwner)) {
                    $query->whereRaw($whereOwner);
                }
            })->get()->pluck('name', 'id');
            $owners->prepend('Todos', '0');

            // $branches   = Branch::orderBy('description')->where(function($query) use($whereBranch){
            //     if(!empty($whereBranch)){
            //         $query->whereRaw($whereBranch);
            //     }
            // })->get()->pluck('description','id');
            // $branches->prepend('Todos','0');


            $pdvs       = Pos::orderBy('description')->where(function ($query) use ($wherePos) {
                if (!empty($wherePos)) {
                    $query->whereRaw($wherePos);
                }
            })->with('Atm')->get();
            $pos = [];
            $item = array();
            $item[0] = 'Todos';
            foreach ($pdvs  as $pdv) {
                $item[$pdv->id] = $pdv->description . ' - ' . $pdv->Atm->code;
                $pos = $item;
            }

            $status = array('0' => 'Todos', 'success' => 'Aprobado', 'canceled' => 'Cancelado', 'error' => 'Error', 'rollback' => 'Reversado', 'iniciated' => 'Iniciado', 'error dispositivo' => 'Error de dispositivo', 'devolucion' => 'Devolución', 'inconsistency' => 'Inconsistencia');
            $atmType = array('0' => 'Todos', 'da' => 'App Billetaje', 'ws' => 'Web Service', 'at' => 'Atm');
            $payment_methods = array('0' => 'Todos', 'efectivo' => 'Efectivo', 'canje' => 'Canje', 'QR' => 'Todos QR', 'TC' => 'Tarjeta de crédito', 'TD' => 'Tarjeta de débito');

            $services   = ServiceProviderProduct::with('WebServiceProvider')->orderBy('service_provider_id', 'DESC')->get();
            if ($this->user->hasAccess('ticketea') && !$this->user->hasAccess('superuser')) {
                $service_item[28] = 'Ticketea - Venta de tickets';
                $services_data = $service_item;
            } else {
                $service_item[0] = 'Todos';
                $service_item[-2] = 'Netel';
                $service_item[-4] = 'Pronet';
                $service_item[-6] = 'Practipago';
                $service_item[-7] = 'Infonet';
                $service_item[-8] = 'Toval';
                $service_item[-10] = 'Netel Trex';
                $service_item[-11] = 'Bancard Ventas QR';
                foreach ($services  as $service) {
                    if ($service->id <> 100) {
                        if ($service->id <> 27 && $service->id <> -1 && $service->WebServiceProvider->name <> 'Toval - Integraciones' && $service->WebServiceProvider->name <> 'Momo - Ken') {
                            $service_item[$service->id] = $service->WebServiceProvider->name . ' - ' . $service->description;
                            $services_data = $service_item;
                        }
                    }
                }
            }

            $resultset = array(
                'target'        => 'Transacciones',
                'groups'        => $groups,
                'owners'        => $owners,
                'branches'      => $branches,
                'payment_methods' => $payment_methods,
                'pos'           => $pos,
                'status'        => $status,
                'type'          => $atmType,
                'services_data' => $services_data,
                'transactions'  => $transactions,
                'total_transactions'  => $total_transactions,
                'group_id'      => (isset($input['group_id']) ? $input['group_id'] : 0),
                'owner_id'      => (isset($input['owner_id']) ? $input['owner_id'] : 0),
                'branch_id'     => (isset($input['branch_id']) ? $input['branch_id'] : 0),
                'pos_id'        => (isset($input['pos_id']) ? $input['pos_id'] : 0),
                'status_set'    => (isset($input['status_id']) ? $input['status_id'] : 0),
                'payment_methods_set' => (isset($input['payment_method_id']) ? $input['payment_method_id'] : 0),
                'type_set'    => (isset($input['type']) ? $input['type'] : 0),
                'service_id'    => (isset($input['service_id']) ? $input['service_id'] : 0),
                'reservationtime' => (isset($input['reservationtime']) ? $input['reservationtime'] : 0),
                'i'             =>  1,
                'service_request_id' => (isset($input['service_request_id']) ? $input['service_request_id'] : 0),
                'query_to_export' => $query_to_export,
                'transactions_total' => $transactions_total,

                //Filtro para puntos de ventas.
                'pos_active' => (isset($input['pos_active']) ? 'on' : ''),

                //Nuevos filtros
                'transaction_id' => (isset($input['transaction_id']) ? $input['transaction_id'] : null),
                'amount' => (isset($input['amount']) ? $input['amount'] : null),

                //Para mostrar un mensaje al usuario.
                'search' => true
            );

            //\Log::info('pos:', [$pos]);
            //\Log::info("wherePos: $wherePos");
            //die();

            return $resultset;
        } catch (\Exception $e) {
            $error_detail = [
                'from' => 'CMS',
                'message' => '[transactionsSearch] Ocurrió un error la querer realizar una búsqueda.',
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine(),
                'user' => [
                    'user_id' => $this->user->id,
                    'username' => $this->user->username,
                    'description' => $this->user->description
                ]
            ];

            \Log::error($error_detail['message'], [$error_detail]);

            return false;
        }
    }


    public function transactionsSearchExport()
    {
        try {
            $input = $this->input;
            $where = "t.transaction_type in (1,7,11,12,13, 17) and ";

            $transaction_id = null;

            if (isset($input['transaction_id'])) {
                if ($input['transaction_id'] !== null and $input['transaction_id'] !== '') {
                    $transaction_id = $input['transaction_id'];
                    $where .= "t.id = $transaction_id and ";
                }
            }


            if ($transaction_id == null) {

                /*Busqueda minusiosa*/
                if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
                    $where .= "t.id = {$input['context']} or ";
                    $where .= "t.referencia_numero_1 = '{$input['context']}' and ";
                } else {
                    /*SET DATE RANGE*/
                    $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                    $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                    $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                    $where .= "t.created_at between '{$daterange[0]}' and '{$daterange[1]}' and ";

                    if (isset($input['group_id'])) {
                        $where .= ($input['group_id'] <> 0) ? "b.group_id = " . $input['group_id'] . " and " : "";
                    }

                    /*SET OWNER*/
                    if (!$this->user->hasAccess('superuser')) {
                        if (($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11)) {
                            $where .= "t.owner_id = " . $this->user->owner_id . " and ";
                        } else {
                            if (isset($input['owner_id'])) {
                                $where .= ($input['owner_id'] <> 0) ? "t.owner_id = " . $input['owner_id'] . " and " : "";
                            }
                        }
                        //USER GET BRANCH    
                        if ($this->user->branch_id <> null) {
                            $input['branch_id'] = $this->user->branch_id;
                        }
                    } else {

                        if (isset($input['owner_id'])) {
                            $where .= ($input['owner_id'] <> 0) ? "t.owner_id = " . $input['owner_id'] . " and " : "";
                        }
                    }

                    if (isset($input['branch_id'])) {
                        $where .= ($input['branch_id'] <> 0) ? "pos.branch_id = " . $input['branch_id'] . " and " : "";
                    }

                    if (isset($input['pos_id'])) {
                        $where .= ($input['pos_id'] <> 0) ? "pos.id = " . $input['pos_id'] . " and " : "";
                    }

                    $where .= ($input['status_id'] <> "0") ? "t.status =  '{$input['status_id']}' and " : "";

                    if (isset($input['type'])) {
                        $where .= ($input['type'] <> "0") ? "a.type =  '{$input['type']}' and " : "";
                    }

                    if (isset($input['payment_method_id'])) {
                        if ($input['payment_method_id'] == 'QR') {
                            $where .= "p.tipo_pago in ('TC','TD','DC','QR')";
                        } else {
                            $where .= ($input['payment_method_id'] <> '0') ? "p.tipo_pago = '{$input['payment_method_id']}' and " : "";
                        }
                    }

                    if (\Sentinel::getUser()->inRole('mini_terminal')) {
                        //obtener los branches asociados a un usuario
                        $branches = \DB::table('points_of_sale')
                            ->select('branch_id')
                            ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                            ->where('branches.user_id', '=', $this->user->id)
                            ->first();

                        $where .= ' pos.branch_id = ' . $branches->branch_id . ' and ';
                    }

                    if (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                        $supervisor = \DB::table('users_x_groups')->where('user_id', $this->user->id)->first();
                        $branches = \DB::table('branches')
                            ->select(['branches.description', 'users.username', 'users.id'])
                            ->join('users', 'branches.user_id', '=', 'users.id')
                            ->join('role_users', 'users.id', '=', 'role_users.user_id')
                            ->where('role_users.role_id', 22)
                            ->where('branches.group_id', $supervisor->group_id)
                            ->get();

                        $atms = \DB::table('branches')
                            ->select(
                                \DB::raw("array_to_string(array_agg(pos.atm_id), ',') as ids")
                            )
                            ->join('users as u', 'b.user_id', '=', 'u.id')
                            ->join('role_users as ru', 'u.id', '=', 'ru.user_id')
                            ->join('points_of_sale as pos', 'b.id', '=', 'pos.branch_id')
                            ->where('ru.role_id', 22)
                            ->where('b.group_id', $supervisor->group_id)
                            ->whereNotNull('pos.atm_id');

                        if (!empty($input['user_id'])) {
                            $atms = $atms->where('b.user_id', $input['user_id']);
                        }

                        $atms->$atms->get();

                        $atm_id = '-1';

                        if (count($atms) > 0) {
                            $atm_id = $atms[0]->ids;
                        }

                        $where .= " a.id in ($atm_id)";
                    }

                    if ($input['service_id'] == -2) {
                        $where .= 't.service_source_id = 1';
                        if ($input['service_request_id'] <> '0') {
                            $where .= ' and t.service_id = ' . $input['service_request_id'];
                        }
                    } elseif ($input['service_id'] == -4) {
                        $where .= 't.service_source_id = 4';
                        if ($input['service_request_id'] <> '0') {
                            $where .= ' and t.service_id = ' . $input['service_request_id'];
                        }
                    } elseif ($input['service_id'] == -6) {
                        $where .= 't.service_source_id = 6';
                        if ($input['service_request_id'] <> '0') {
                            $where .= ' and t.service_id = ' . $input['service_request_id'];
                        }
                    } elseif ($input['service_id'] == -7) {
                        $where .= 't.service_source_id = 7';
                        if ($input['service_request_id'] <> '0') {
                            $where .= ' and t.service_id = ' . $input['service_request_id'];
                        }
                    } elseif ($input['service_id'] == -8) {
                        $where .= 't.service_source_id = 8';
                        if ($input['service_request_id'] <> '0') {
                            $where .= ' and t.service_id = ' . $input['service_request_id'];
                        }
                    } elseif ($input['service_id'] == -11) {
                        $where .= 't.service_source_id = 11';
                        $where .= ' and t.service_id = 100';
                    } elseif ($input['service_id'] == 66) {
                        $where .= 't.service_source_id = 8 and t.service_id= 14';
                        if ($input['service_request_id'] <> '0') {
                            $where .= ' and t.service_id = ' . $input['service_request_id'];
                        }
                    } elseif ($input['service_id'] == 59) {
                        $where .= 't.service_source_id = 8 and t.service_id= 3';
                        if ($input['service_request_id'] <> '0') {
                            $where .= ' and t.service_id = ' . $input['service_request_id'];
                        }
                    } elseif ($input['service_id'] == 57) {
                        $where .= 't.service_source_id = 8 and t.service_id= 4';
                        if ($input['service_request_id'] <> '0') {
                            $where .= ' and t.service_id = ' . $input['service_request_id'];
                        }
                    } elseif ($input['service_id'] == 58) {
                        $where .= 't.service_source_id = 8 and t.service_id= 5';
                        if ($input['service_request_id'] <> '0') {
                            $where .= ' and t.service_id = ' . $input['service_request_id'];
                        }
                    } elseif ($input['service_id'] == 67) {
                        $where .= 't.service_source_id = 8 and t.service_id= 15';
                        if ($input['service_request_id'] <> '0') {
                            $where .= ' and t.service_id = ' . $input['service_request_id'];
                        }
                    } elseif ($this->user->hasAccess('ticketea') && !$this->user->hasAccess('superuser')) {
                        $where .= 't.service_id = 28';
                    } else {
                        $where .= ($input['service_id'] <> 0) ? "t.service_id = " . $input['service_id'] . " and service_source_id = 0" : "";
                    }
                }
            }

            $where = trim($where);
            $where = trim($where, 'and');
            $where = trim($where);

            $transactions = \DB::table('transactions as t')
                ->select(
                    't.id',
                    \DB::raw("trim(replace(to_char(t.amount, '999G999G999G999'), ',', '.')) as amount"),
                    't.service_id',
                    'service_request_id',
                    't.atm_transaction_id',
                    't.status',
                    't.status as estado',
                    't.status_description',
                    't.status_description as estado_descripcion',
                    't.identificador_transaction_id',
                    't.factura_numero',
                    \DB::raw("to_char(t.created_at, 'DD/MM/YYYY HH24:MI:SS') as created_at"),
                    \DB::raw("to_char(t.created_at, 'DD/MM/YYYY') as fecha"),
                    \DB::raw("to_char(t.created_at, 'HH24:MI:SS') as hora"),
                    /*\DB::raw(
                        "
                        trim(replace(to_char((
                            case when (t.amount < 0) 
                            then t.amount * -1 
                            else t.amount end), '999G999G999G999'
                        ), ',', '.')) as valor_transaccion"
                    ),*/
                    't.amount as valor_transaccion',
                    \DB::raw("0 as commission_amount"),
                    'p.id as cod_pago',
                    'p.tipo_pago as forma_pago',
                    'p.valor_a_pagar',
                    'p.valor_recibido',
                    'valor_entregado',
                    't.identificador_transaction_id as identificador_transaccion',
                    't.factura_numero as factura_nro',
                    'pos.description as sede',
                    /*\DB::raw("
                        (case 
                        when (t.owner_id = 2) then 'Antell'
                        when (t.owner_id = 11) then 'Eglobal' 
                        else t.owner_id::text end) as owner_id
                    "),*/
                    //'o.name as owner_id',
                    \DB::raw("(select o.name from owners o where o.id = t.owner_id) as owner_id"),
                    'referencia_numero_1',
                    'referencia_numero_2',
                    'referencia_numero_1 as ref1',
                    'referencia_numero_2 as ref2',
                    'a.code as codigo_cajero',
                    'a.code as code',
                    't.service_source_id',
                    'sp.name as provider',
                    'spp.description as servicio',
                    \DB::raw("
                        (case when (t.service_source_id <> 0) then
                            (select sps.description 
                            from services_providers_sources sps 
                            where sps.id = t.service_source_id 
                            limit 1)
                        else 
                            sp.name
                        end) as proveedor
                    "),
                    \DB::raw("
                        (case when (t.service_source_id <> 0) then
                            case when (t.service_source_id = 8) then
                                (select sop.service_description 
                                from services_ondanet_pairing sop 
                                where sop.service_request_id = t.service_id 
                                and sop.service_source_id = t.service_source_id 
                                limit 1)
                            else 
                                (select sop.service_description 
                                from services_ondanet_pairing sop 
                                where sop.service_request_id = t.service_id 
                                and sop.service_source_id = t.service_source_id 
                                limit 1)
                            end
                        else 
                            spp.description
                        end) as tipo
                    ")
                )
                ->join('points_of_sale as pos', 'pos.atm_id', '=', 't.atm_id')
                ->join('atms as a', 'a.id', '=', 't.atm_id')
                //->leftjoin('owners as o', 'o.id', '=', 't.owner_id')
                ->leftjoin('service_provider_products as spp', 'spp.id', '=', 't.service_id')
                ->leftjoin('service_providers as sp', 'sp.id', '=', 'spp.service_provider_id')
                ->leftjoin('transactions_x_payments as txp', 't.id', '=', 'txp.transactions_id')
                ->leftjoin('payments as p', 'p.id', '=', 'txp.payments_id')
                ->leftjoin('branches as b', 'b.id', '=', 'pos.branch_id')
                ->whereRaw("$where")
                ->orderBy('cod_pago', 'desc')
                ->orderBy('t.created_at', 'desc');

            $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
            $to = date('Y-m-d H:i:s', strtotime($daterange[0]));
            $from = date('Y-m-d H:i:s', strtotime($daterange[1]));
            $days = \DB::select("select ('{$from}'::date - '{$to}'::date) + 1 as days");
            \Log::info('[Exportar reporte]', ['días' => $days]);
            $days = $days[0]->days;

            if ($days > 1 and $transaction_id == null) {
                $transactions = trim($transactions->toSql());
            } else {
                $transactions = $transactions->get();
            }

            return $transactions;
        } catch (\Exception $e) {
            $error_detail = [
                'from' => 'CMS',
                'message' => '[transactionsSearchExport] Ocurrió un error la querer buscar y exportar transacciones.',
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine(),
                'user' => [
                    'user_id' => $this->user->id,
                    'username' => $this->user->username,
                    'description' => $this->user->description
                ]
            ];

            \Log::error($error_detail['message'], [$error_detail]);

            return false;
        }
    }



    public function transactionsSearchExportMovements()
    {
        try {
            $input = $this->input;
            $where = "transactions.transaction_type in (1,7,11,12,13) and ";

            $transaction_id = null;

            if (isset($input['transaction_id'])) {
                if ($input['transaction_id'] !== null and $input['transaction_id'] !== '') {
                    $transaction_id = $input['transaction_id'];
                    $where .= "transactions.id = $transaction_id and ";
                }
            }

            if ($transaction_id == null) {
                /*Busqueda minusiosa*/
                if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
                    $where .= "transactions.id = {$input['context']} OR ";
                    $where .= "transactions.referencia_numero_1 = '{$input['context']}' and ";
                } else {
                    /*SET DATE RANGE*/
                    $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                    $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                    $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                    $where .= "transactions.created_at BETWEEN '{$daterange[0]}' and '{$daterange[1]}' and ";

                    // if(isset($input['group_id']))
                    // {
                    //     $where .= ($input['group_id']<>0) ? "branches.group_id = ". $input['group_id']." and " : "";
                    // }

                    /*SET OWNER*/
                    if (!$this->user->hasAccess('superuser')) {
                        if (($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11)) {
                            $where .= "transactions.owner_id = " . $this->user->owner_id . " and ";
                        } else {
                            if (isset($input['owner_id'])) {
                                $where .= ($input['owner_id'] <> 0) ? "transactions.owner_id = " . $input['owner_id'] . " and " : "";
                            }
                        }
                        //USER GET BRANCH    

                        if ($this->user->branch_id <> null) {
                            $input['branch_id'] = $this->user->branch_id;
                        }
                    } else {
                        $where .= ($input['owner_id'] <> 0) ? "transactions.owner_id = " . $input['owner_id'] . " and " : "";
                    }

                    if (isset($input['branch_id'])) {
                        $where .= ($input['branch_id'] <> 0) ? "points_of_sale.branch_id = " . $input['branch_id'] . " and " : "";
                    }

                    if (isset($input['pos_id'])) {
                        $where .= ($input['pos_id'] <> 0) ? "points_of_sale.id = " . $input['pos_id'] . " and " : "";
                    }

                    $where .= ($input['status_id'] <> "0") ? "transactions.status =  '{$input['status_id']}' and " : "";

                    if (isset($input['type'])) {
                        $where .= ($input['type'] <> "0") ? "atms.type =  '{$input['type']}' and " : "";
                    }

                    // if(\Sentinel::getUser()->inRole('mini_terminal')){                    
                    //     //obtener los branches asociados a un usuario
                    //     $branches = \DB::table('points_of_sale')
                    //         ->select('branch_id')
                    //         ->join('branches', 'branches.id','=','points_of_sale.branch_id')
                    //         ->where('branches.user_id','=',$this->user->id)
                    //         ->first();

                    //         $where .= ' points_of_sale.branch_id = ' . $branches->branch_id . ' AND ';
                    // }
                    // if(\Sentinel::getUser()->inRole('supervisor_miniterminal')){
                    //     $supervisor = \DB::table('users_x_groups')->where('user_id',$this->user->id)->first();
                    //     $branches = \DB::table('branches')
                    //         ->select(['branches.description', 'users.username', 'users.id'])
                    //         ->join('users', 'branches.user_id', '=', 'users.id')
                    //         ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    //         ->where('role_users.role_id', 22)
                    //         ->where('branches.group_id', $supervisor->group_id)
                    //         ->get();
                    //     $atms = \DB::table('branches')
                    //         ->select(['points_of_sale.atm_id'])
                    //         ->join('users', 'branches.user_id', '=', 'users.id')
                    //         ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    //         ->join('points_of_sale', 'branches.id', '=', 'points_of_sale.branch_id')
                    //         ->where('role_users.role_id', 22)
                    //         ->where('branches.group_id', $supervisor->group_id)
                    //         ->whereNotNull('atm_id')
                    //         ->where(function($query) use($input){
                    //             if(!empty($input['user_id'])){
                    //                 $query->where('branches.user_id', $input['user_id']);
                    //             }
                    //         })
                    //         ->pluck('atm_id', 'atm_id');
                    //     $atm_id = '(';
                    //     foreach ($atms as $id_atm => $atm) {
                    //         $atm_id .= $atm.', ';
                    //     }

                    //     $atm_id = rtrim($atm_id, ', ');
                    //     $atm_id .= ')';

                    //     //$where .= ' branches.user_id = '.$input['user_id'];
                    //     $where .= ' atms.id in '.$atm_id;

                    // }

                    if ($input['service_id'] == -2) {
                        $where .= 'transactions.service_source_id = 1';
                        if ($input['service_request_id'] <> '0') {
                            $where .= ' and transactions.service_id = ' . $input['service_request_id'];
                        }
                    } elseif ($input['service_id'] == -4) {
                        $where .= 'transactions.service_source_id = 4';
                        if ($input['service_request_id'] <> '0') {
                            $where .= ' and transactions.service_id = ' . $input['service_request_id'];
                        }
                    } elseif ($input['service_id'] == -6) {
                        $where .= 'transactions.service_source_id = 6';
                        if ($input['service_request_id'] <> '0') {
                            $where .= ' and transactions.service_id = ' . $input['service_request_id'];
                        }
                    } elseif ($input['service_id'] == -7) {
                        $where .= 'transactions.service_source_id = 7';
                        if ($input['service_request_id'] <> '0') {
                            $where .= ' and transactions.service_id = ' . $input['service_request_id'];
                        }
                    } elseif ($input['service_id'] == -8) {
                        $where .= 'transactions.service_source_id = 8';
                        if ($input['service_request_id'] <> '0') {
                            $where .= ' and transactions.service_id = ' . $input['service_request_id'];
                        }
                    } elseif ($input['service_id'] == -10) {
                        $where .= 'transactions.service_source_id = 10';
                        if ($input['service_request_id'] <> '0') {
                            $where .= ' and transactions.service_id = ' . $input['service_request_id'];
                        }
                    } elseif ($input['service_id'] == 66) {
                        $where .= 'transactions.service_source_id = 8 and transactions.service_id= 14';
                        if ($input['service_request_id'] <> '0') {
                            $where .= ' and transactions.service_id = ' . $input['service_request_id'];
                        }
                    } elseif ($input['service_id'] == 59) {
                        $where .= 'transactions.service_source_id = 8 and transactions.service_id= 3';
                        if ($input['service_request_id'] <> '0') {
                            $where .= ' and transactions.service_id = ' . $input['service_request_id'];
                        }
                    } elseif ($input['service_id'] == 57) {
                        $where .= 'transactions.service_source_id = 8 and transactions.service_id= 4';
                        if ($input['service_request_id'] <> '0') {
                            $where .= ' and transactions.service_id = ' . $input['service_request_id'];
                        }
                    } elseif ($input['service_id'] == 58) {
                        $where .= 'transactions.service_source_id = 8 and transactions.service_id= 5';
                        if ($input['service_request_id'] <> '0') {
                            $where .= ' and transactions.service_id = ' . $input['service_request_id'];
                        }
                    } elseif ($input['service_id'] == 67) {
                        $where .= 'transactions.service_source_id = 8 and transactions.service_id= 15';
                        if ($input['service_request_id'] <> '0') {
                            $where .= ' and transactions.service_id = ' . $input['service_request_id'];
                        }
                    } elseif ($this->user->hasAccess('ticketea') && !$this->user->hasAccess('superuser')) {
                        $where .= 'transactions.service_id = 28';
                    } else {
                        $where .= ($input['service_id'] <> 0) ? "transactions.service_id = " . $input['service_id'] . " and service_source_id = 0" : "";
                    }
                }
            }


            $where = trim($where);
            $where = trim($where, 'and');
            $where = trim($where);

            $transactions = \DB::table('transactions_movements')
                ->select(
                    'transactions_movements.id',
                    'transactions_movements.transactions_id as transaction_id',
                    'transactions_movements.atms_parts_id as atm',
                    'transactions_movements.accion',
                    'transactions_movements.cantidad',
                    'transactions_movements.valor',
                    'transactions_movements.dinero_virtual',
                    'transactions_movements.payments_id',
                    \DB::raw("to_char(transactions.created_at, 'DD/MM/YYYY') as fecha"),
                    \DB::raw("to_char(transactions.created_at, 'HH24:MI:SS') as hora")
                )
                ->join('transactions', 'transactions.id', '=', 'transactions_movements.transactions_id')
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'transactions.atm_id')
                ->join('atms', 'atms.id', '=', 'points_of_sale.atm_id')
                ->leftjoin('atms_parts', 'atms_parts.id', '=', 'transactions_movements.atms_parts_id')
                ->leftjoin('payments', 'payments.id', '=', 'transactions_movements.payments_id')
                ->whereRaw("$where")
                ->orderBy('transactions.created_at', 'desc');

            $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
            $to = date('Y-m-d H:i:s', strtotime($daterange[0]));
            $from = date('Y-m-d H:i:s', strtotime($daterange[1]));
            $days = \DB::select("select ('{$from}'::date - '{$to}'::date) + 1 as days");
            $days = $days[0]->days;

            if ($days > 1 and $transaction_id == null) {
                $transactions = $transactions->toSql();
                //\Log::info('Día mayor a 0 en transactionsSearchExportMovements()');
                //\Log::info("Query: $transactions");
            } else {
                $transactions = $transactions->get();

                /*foreach($transactions as $transaction){

                            $transaction->fecha = date('d-m-Y', strtotime($transaction->fecha));
                            $transaction->hora = date('H:i:s', strtotime($transaction->hora));
                        };*/
            }

            return $transactions;
        } catch (\Exception $e) {
            $error_detail = [
                'from' => 'CMS',
                'message' => '[transactionsSearchExportMovements] Ocurrió un error la querer buscar y exportar movimientos.',
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine(),
                'user' => [
                    'user_id' => $this->user->id,
                    'username' => $this->user->username,
                    'description' => $this->user->description
                ]
            ];

            \Log::error($error_detail['message'], [$error_detail]);

            return false;
        }
    }


    /*TRANSACTION BATCH REPORTS */
    public function batchTransactionsReports()
    {
        try {

            //Redes
            $whereGroup = "";
            $whereOwner = "";
            $whereBranch = "";
            $wherePos = "";
            if (!$this->user->hasAccess('superuser')) {
                if ($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11) {
                    $whereOwner = "owners.id = " . $this->user->owner_id;
                    $whereBranch = "branches.owner_id = " . $this->user->owner_id;
                    $wherePos = "points_of_sale.owner_id = " . $this->user->owner_id;
                }
            }
            //Redes
            $groups     = Group::orderBy('business_groups.description')->where(function ($query) use ($whereGroup) {
                if (!empty($whereGroup)) {
                    $query->whereRaw($whereGroup);
                }
            })->get()->pluck('description', 'id');
            $groups->prepend('Todos', '0');

            $owners     = Owner::orderBy('owners.name')->where(function ($query) use ($whereOwner) {
                if (!empty($whereOwner)) {
                    $query->whereRaw($whereOwner);
                }
            })->get()->pluck('name', 'id');
            $owners->prepend('Todos', '0');
            $branches   = Branch::orderBy('description')->where(function ($query) use ($whereBranch) {
                if (!empty($whereBranch)) {
                    $query->whereRaw($whereBranch);
                }
            })->get()->pluck('description', 'id');
            $branches->prepend('Todos', '0');
            $pdvs       = Pos::orderBy('description')->where(function ($query) use ($wherePos) {
                if (!empty($wherePos)) {
                    $query->whereRaw($wherePos);
                }
            })->with('Atm')->get();
            $pos = [];
            $item = array();
            $item[0] = 'Todos';

            foreach ($pdvs  as $pdv) {
                $item[$pdv->id] = $pdv->description . ' - ' . $pdv->Atm->code;
                $pos = $item;
            }
            $status = array('0' => 'Todos', 'success' => 'Aprobado', 'pendiente' => 'Pendiente', 'error' => 'Error', 'error dispositivo' => 'Error de dispositivo', 'inconsistency' => 'Inconsistencia');
            $atmType = array('0' => 'Todos', 'da' => 'App Billetaje', 'ws' => 'Web Service', 'at' => 'Atm');

            $service_item['0']    = 'Todos';
            $service_item['0-3']    = 'Tigo - Minicarga';
            $service_item['1-7']   = 'Personal - Maxicarga';
            $service_item['1-3']   = 'Claro - Recarga';
            $service_item['1-549'] = 'Vox - Michimi';

            $services_data = $service_item;

            $resultset = array(
                'target'        => 'Transacciones_batch',
                'groups'        => $groups,
                'owners'        => $owners,
                'branches'      => $branches,
                'pos'           => $pos,
                'status'        => $status,
                'type'          => $atmType,
                'services_data' => $services_data,
                'group_id'      => 0,
                'owner_id'      => 0,
                'branch_id'     => 0,
                'pos_id'        => 0,
                'status_set'    => 0,
                'type_set'      => 0,
                'service_id'    => 0

            );

            return $resultset;
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de reportes" . $e);
            return false;
        }
    }

    public function batchTransactionsSearch()
    {
        $input = $this->input;
        if (!isset($input['pos_id'])) {
            $input['pos_id'] = 0;
        }
        $where = '';
        /*Busqueda minusiosa*/
        if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
            $where .= "transactions_batch.referencia_numero_1 = '{$input['context']}' OR ";
            $where .= "transactions_batch.transaction_id = '{$input['context']}'";
        } else {
            /*SET DATE RANGE*/
            $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
            $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
            $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
            $where .= "transactions_batch.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' AND ";
            /*SET OWNER*/
            $where .= ($input['group_id'] <> 0) ? "branches.group_id = " . $input['group_id'] . " AND " : "";

            if (!$this->user->hasAccess('superuser')) {
                if (($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11)) {
                    $where .= "transactions_batch.owner_id = " . $this->user->owner_id . " AND ";
                } else {
                    $where .= ($input['owner_id'] <> 0) ? "transactions_batch.owner_id = " . $input['owner_id'] . " AND " : "";
                }
            } else {
                $where .= ($input['owner_id'] <> 0) ? "transactions_batch.owner_id = " . $input['owner_id'] . " AND " : "";
            }
            $where .= ($input['branch_id'] <> 0) ? "points_of_sale.branch_id = " . $input['branch_id'] . " AND " : "";
            $where .= ($input['pos_id'] <> 0) ? "points_of_sale.id = " . $input['pos_id'] . " AND " : "";
            $where .= ($input['status_id'] <> "0") ? "transactions_batch.status =  '{$input['status_id']}' AND " : "";
            $where .= ($input['type'] <> "0") ? "atms.type =  '{$input['type']}' AND " : "";

            $service_data = explode('-', $input['service_id']);
            if (count($service_data) > 1) {
                $service_source_id = $service_data[0];
                $service_id        = $service_data[1];
                $where .= 'transactions_batch.service_source_id = ' . $service_source_id . ' AND transactions_batch.service_id = ' . $service_id;
            }

            $where = trim($where);
            $where = trim($where, 'AND');
            $where = trim($where);
        }
        $batch_transactions = \DB::table('transactions_batch')
            ->select(\DB::raw('transactions_batch.id, service_id, service_source_id, service_providers.name as provider,service_provider_products.description as servicio, referencia_numero_1, amount, transaction_id, status, users.username, processed , parent_transaction_id,transactions_batch.created_at as fecha ,points_of_sale.description as pdv, atms.code, atms.type'))
            ->join('atms', 'atms.id', '=', 'transactions_batch.atm_id')
            ->leftjoin('users', 'users.id', '=', 'transactions_batch.updated_by_user')
            ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'transactions_batch.atm_id')
            ->leftjoin('service_provider_products', 'service_provider_products.id', '=', 'transactions_batch.service_id')
            ->leftjoin('service_providers', 'service_providers.id', '=', 'service_provider_products.service_provider_id')
            ->leftjoin('branches', 'branches.id', '=', 'points_of_sale.branch_id')
            ->whereRaw("$where")
            ->orderBy('transactions_batch.status', 'asc')
            ->orderBy('transactions_batch.id', 'desc')
            ->paginate(20);

        $total_transactions = \DB::table('transactions_batch')
            ->select(\DB::raw('sum(abs(amount)) as monto'))
            ->join('atms', 'atms.id', '=', 'transactions_batch.atm_id')
            ->leftjoin('users', 'users.id', '=', 'transactions_batch.updated_by_user')
            ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'transactions_batch.atm_id')
            ->leftjoin('service_provider_products', 'service_provider_products.id', '=', 'transactions_batch.service_id')
            ->leftjoin('service_providers', 'service_providers.id', '=', 'service_provider_products.service_provider_id')
            ->leftjoin('branches', 'branches.id', '=', 'points_of_sale.branch_id')
            ->whereRaw("$where")
            ->get();

        foreach ($batch_transactions as $batch_transaction) {
            if ($batch_transaction->status == 'success') {
                $batch_transaction->status_description =  '<span class="label label-success">' . $batch_transaction->status . '</span>';
            } elseif ($batch_transaction->status == 'pendiente') {
                $batch_transaction->status_description =  '<span class="label label-warning">' . $batch_transaction->status . '</span>';
            } else {
                $batch_transaction->status_description =  '<span class="label label-danger">' . $batch_transaction->status . '</span>';
            }

            if ($batch_transaction->service_source_id <> 0) {
                $serv_provider = \DB::table('services_providers_sources')->where('id', $batch_transaction->service_source_id)->first();
                $batch_transaction->provider = $serv_provider->description;
                $service_data = \DB::table('services_ondanet_pairing')->where('service_request_id', $batch_transaction->service_id)->where('service_source_id', $batch_transaction->service_source_id)->first();
                $batch_transaction->servicio = isset($service_data->service_description) ? $service_data->service_description : '';
            }
        }




        //Redes
        $whereGroup = "";
        $whereOwner = "";
        $whereBranch = "";
        $wherePos = "";
        if (!$this->user->hasAccess('superuser')) {
            if ($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11) {
                $whereOwner = "owners.id = " . $this->user->owner_id;
                $whereBranch = "branches.owner_id = " . $this->user->owner_id;
                $wherePos = "points_of_sale.owner_id = " . $this->user->owner_id;
            }
        }
        //Redes
        $groups     = Group::orderBy('business_groups.description')->where(function ($query) use ($whereGroup) {
            if (!empty($whereGroup)) {
                $query->whereRaw($whereGroup);
            }
        })->get()->pluck('description', 'id');
        $groups->prepend('Todos', '0');

        $owners     = Owner::orderBy('owners.name')->where(function ($query) use ($whereOwner) {
            if (!empty($whereOwner)) {
                $query->whereRaw($whereOwner);
            }
        })->get()->pluck('name', 'id');
        $owners->prepend('Todos', '0');
        $branches   = Branch::orderBy('description')->where(function ($query) use ($whereBranch) {
            if (!empty($whereBranch)) {
                $query->whereRaw($whereBranch);
            }
        })->get()->pluck('description', 'id');
        $branches->prepend('Todos', '0');
        $pdvs       = Pos::orderBy('description')->where(function ($query) use ($wherePos) {
            if (!empty($wherePos)) {
                $query->whereRaw($wherePos);
            }
        })->with('Atm')->get();
        $pos = [];
        $item = array();
        $item[0] = 'Todos';

        foreach ($pdvs  as $pdv) {
            $item[$pdv->id] = $pdv->description . ' - ' . $pdv->Atm->code;
            $pos = $item;
        }
        $status = array('0' => 'Todos', 'success' => 'Aprobado', 'pendiente' => 'Pendiente', 'error' => 'Error', 'error dispositivo' => 'Error de dispositivo', 'inconsistency' => 'Inconsistencia');
        $atmType = array('0' => 'Todos', 'da' => 'App Billetaje', 'ws' => 'Web Service', 'at' => 'Atm');

        $service_item['0']    = 'Todos';
        $service_item['0-3']  = 'Tigo - Minicarga';
        $service_item['1-7']  = 'Personal - Maxicarga';
        $service_item['1-3']  = 'Claro - Recarga';
        $service_item['1-549'] = 'Vox - Michimi';

        $services_data = $service_item;

        $resultset = array(
            'target'                => 'Transacciones_batch',
            'groups'                => $groups,
            'owners'                => $owners,
            'branches'              => $branches,
            'pos'                   => $pos,
            'status'                => $status,
            'type'                  => $atmType,
            'services_data'         => $services_data,
            'batch_transactions'    => $batch_transactions,
            'total_transactions'    => $total_transactions,
            'group_id'          => (isset($input['group_id']) ? $input['group_id'] : 0),
            'owner_id'          => (isset($input['owner_id']) ? $input['owner_id'] : 0),
            'branch_id'         => (isset($input['branch_id']) ? $input['branch_id'] : 0),
            'pos_id'            => (isset($input['pos_id']) ? $input['pos_id'] : 0),
            'status_set'        => (isset($input['status_id']) ? $input['status_id'] : 0),
            'type_set'          => (isset($input['type']) ? $input['type'] : 0),
            'service_id'        => (isset($input['service_id']) ? $input['service_id'] : 0),
            'reservationtime'   => (isset($input['reservationtime']) ? $input['reservationtime'] : 0),
            'i'                 =>  1
        );
        return $resultset;
    }

    public function batchTransactionsExport()
    {
        $input = $this->input;
        $where = '';

        /*SET DATE RANGE*/
        $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
        $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
        $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
        $where .= "transactions_batch.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' AND ";

        /*SET OWNER*/
        if (!$this->user->hasAccess('superuser')) {
            if (($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11)) {
                $where .= "transactions_batch.owner_id = " . $this->user->owner_id . " AND ";
            } else {
                $where .= ($input['owner_id'] <> 0) ? "transactions_batch.owner_id = " . $input['owner_id'] . " AND " : "";
            }
        } else {
            $where .= ($input['owner_id'] <> 0) ? "transactions_batch.owner_id = " . $input['owner_id'] . " AND " : "";
        }
        $where .= ($input['branch_id'] <> 0) ? "points_of_sale.branch_id = " . $input['branch_id'] . " AND " : "";
        $where .= ($input['pos_id'] <> 0) ? "points_of_sale.id = " . $input['pos_id'] . " AND " : "";
        $where .= ($input['status_id'] <> "0") ? "transactions_batch.status =  '{$input['status_id']}' AND " : "";
        $where .= ($input['type'] <> "0") ? "atms.type =  '{$input['type']}' AND " : "";

        $service_data = explode('-', $input['service_id']);
        if (count($service_data) > 1) {
            $service_source_id = $service_data[0];
            $service_id        = $service_data[1];
            $where .= 'transactions_batch.service_source_id = ' . $service_source_id . ' AND transactions_batch.service_id = ' . $service_id;
        }

        $where = trim($where);
        $where = trim($where, 'AND');
        $where = trim($where);


        $batch_transactions = \DB::table('transactions_batch')
            ->select(\DB::raw('transactions_batch.id, service_id, service_source_id, service_providers.name as proveedor,service_provider_products.description as servicio, referencia_numero_1, amount as monto, transaction_id as ID_transaccion, status, transactions_batch.created_at as fecha, transactions_batch.created_at as hora ,points_of_sale.description as pdv, atms.code as Cod_Atm, atms.type'))
            ->join('atms', 'atms.id', '=', 'transactions_batch.atm_id')
            ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'transactions_batch.atm_id')
            ->leftjoin('service_provider_products', 'service_provider_products.id', '=', 'transactions_batch.service_id')
            ->leftjoin('service_providers', 'service_providers.id', '=', 'service_provider_products.service_provider_id')
            ->whereRaw("$where")
            ->orderBy('transactions_batch.status', 'asc')
            ->orderBy('transactions_batch.id', 'desc')
            ->get();

        foreach ($batch_transactions as $batch_transaction) {
            if ($batch_transaction->service_source_id <> 0) {
                $serv_provider = \DB::table('services_providers_sources')->where('id', $batch_transaction->service_source_id)->first();
                $batch_transaction->proveedor = $serv_provider->description;
                $service_data = \DB::table('services_ondanet_pairing')->where('service_request_id', $batch_transaction->service_id)->where('service_source_id', $batch_transaction->service_source_id)->first();
                $batch_transaction->servicio = isset($service_data->service_description) ? $service_data->service_description : '';
            }

            $batch_transaction->hora = date('H:i:s', strtotime($batch_transaction->hora));
            $batch_transaction->fecha = date('d-m-Y', strtotime($batch_transaction->fecha));
            $batch_transaction->monto = number_format($batch_transaction->monto, 0, '', '');

            unset($batch_transaction->service_source_id);
            unset($batch_transaction->service_id);
        }

        return $batch_transactions;
    }

    public function batchReprocessTransaction($batchID)
    {
        try {
            $transaction_batch = new Transactions_batch();
            $transaction = $transaction_batch::find($batchID);
            //$this->user =
            $user = \Sentinel::getUser();

            if (($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11)) {
                $response = [
                    'error'     => true,
                    'message'   => 'Se requiere autorización para procesar esta acción '
                ];

                return $response;
            }

            if ($transaction->processed == true && $transaction->status == 'error') {
                $transaction->processed = false;
                $transaction->status    = 'pendiente';
                $transaction->updated_at = Carbon::now();
                $transaction->updated_by_user = $user->id;
                $transaction->save();

                $response = [
                    'error'     => false,
                    'message'   => 'Todo salió bien ID : ' . $batchID
                ];
            } else {
                $response = [
                    'error'     => true,
                    'message'   => 'La transacción aun no fue procesada. No se puede cambiar el estado ID : ' . $batchID
                ];
            }
            return $response;
        } catch (\Exception $e) {
            $response = [
                'error'     => true,
                'message'   => 'Procedimiento no Autorizado'
            ];
            \Log::warning('Error ' . $e->getMessage());
            return $response;
        }
    }

    public function batchReprocessTransactionManually($batchID, $parentID)
    {
        try {
            $transaction_batch = new Transactions_batch();
            $batchInfo = $transaction_batch::find($batchID);
            $user = \Sentinel::getUser();

            if (($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11)) {
                $response = [
                    'error'     => true,
                    'message'   => 'Se requiere autorización para procesar esta acción '
                ];

                return $response;
            }

            /*$transactions = new transactions();
            $transactionInfo = $transactions::find($parentID);*/
            $transactionInfo = \DB::table('transactions')
                ->select(\DB::raw('service_id, service_source_id, amount,referencia_numero_1,referencia_numero_2, created_at'))
                ->where('id', $parentID)
                ->first();

            //Validar datos de la transaccion proporsionada

            if ($batchInfo->service_id <> $transactionInfo->service_id && $batchInfo->service_source_id <> $transactionInfo->service_source_id) {
                $response = [
                    'error' => true,
                    'message' => 'Id de transacción proporcionado no coincide con el servicio de la transacción original'
                ];
                return $response;
            }

            if ($batchInfo->amount <> number_format($transactionInfo->amount, 0, '', '')) {
                $response = [
                    'error' => true,
                    'message' => 'Monto de transacción proporcionado no coincide con el monto de la transacción original'
                ];
                return $response;
            }

            if ($batchInfo->service_source_id == 0 && ($batchInfo->referencia_numero_1 <> $transactionInfo->referencia_numero_1)) {
                $response = [
                    'error' => true,
                    'message' => 'El nro de referencia no coincide con  la transacción original'
                ];
                return $response;
            }

            if ($batchInfo->service_source_id == 1 && ($batchInfo->referencia_numero_1 <> $transactionInfo->referencia_numero_2)) {
                $response = [
                    'error' => true,
                    'message' => 'El nro de referencia no coincide con  la transacción original'
                ];
                return $response;
            }

            $now = Carbon::now();
            $bacthDate = Carbon::parse($batchInfo->created_at);
            $elasep_time = $bacthDate->diffInDays($now);
            if ($elasep_time > 3) {
                $response = [
                    'error' => true,
                    'message' => 'Han pasado demasiados días desde la creación de la transacción, no se puede procesar'
                ];
                return $response;
            }

            //Pasados todos los filtros se procede a actualizar el estado de la transaccion Batch
            if ($batchInfo->processed == true && $batchInfo->status == 'error') {
                $batchInfo->transaction_id              = $parentID;
                $batchInfo->processed                   = true;
                $batchInfo->status                      = 'success';
                $batchInfo->updated_at                  = Carbon::now();
                $batchInfo->updated_by_user             = $user->id;
                $batchInfo->save();

                $response = [
                    'error'     => false,
                    'message'   => 'Todo salió bien ID : ' . $batchID
                ];
            } else {
                $response = [
                    'error'     => true,
                    'message'   => 'La transacción aun no fue procesada. No se puede cambiar el estado ID : ' . $batchID
                ];
            }

            return $response;
        } catch (\Exception $e) {
            $response = [
                'error'     => true,
                'message'   => 'Procedimiento no Autorizado'
            ];
            \Log::warning('Error ' . $e->getMessage());
            return $response;
        }
    }

    /* PAYMENTS REPORTS*/
    public function paymentsReports()
    {
        $whereGroup = "";
        $whereOwner = "";
        $whereBranch = "";
        $wherePos = "";
        if (!$this->user->hasAccess('superuser')) {
            if ($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11) {
                $whereOwner = "owners.id = " . $this->user->owner_id;
                $whereBranch = "branches.owner_id = " . $this->user->owner_id;
                $wherePos = "points_of_sale.owner_id = " . $this->user->owner_id;
            }
        }
        //Redes
        $groups     = Group::orderBy('business_groups.description')->where(function ($query) use ($whereGroup) {
            if (!empty($whereGroup)) {
                $query->whereRaw($whereGroup);
            }
        })->get()->pluck('description', 'id');
        $groups->prepend('Todos', '0');

        $owners     = Owner::orderBy('owners.name')->where(function ($query) use ($whereOwner) {
            if (!empty($whereOwner)) {
                $query->whereRaw($whereOwner);
            }
        })->get()->pluck('name', 'id');
        $owners->prepend('Todos', '0');
        $branches   = Branch::orderBy('description')->where(function ($query) use ($whereBranch) {
            if (!empty($whereBranch)) {
                $query->whereRaw($whereBranch);
            }
        })->get()->pluck('description', 'id');
        $branches->prepend('Todos', '0');
        $pdvs       = Pos::orderBy('description')->where(function ($query) use ($wherePos) {
            if (!empty($wherePos)) {
                $query->whereRaw($wherePos);
            }
        })->with('Atm')->get();
        $pos = [];
        $item = array();
        $item[0] = 'Todos';

        foreach ($pdvs  as $pdv) {
            $item[$pdv->id] = $pdv->description . ' - ' . $pdv->Atm->code;
            $pos = $item;
        }

        $resultset = array(
            'target'        => 'Payments',
            'groups'        => $groups,
            'owners'        => $owners,
            'branches'      => $branches,
            'pos'           => $pos,
            'group_id'      => 0,
            'owner_id'      => 0,
            'branch_id'     => 0,
            'pos_id'        => 0,
            'status_set'    => 0,
            'service_id'    => 0

        );

        return $resultset;
    }

    public function paymentsSearch()
    {
        try {

            $input = $this->input;
            $where = '';
            /*Busqueda minusiosa*/
            if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
                $where .= "payments.id = {$input['context']} AND ";
            } else {
                $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                $where .= "payments.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' AND ";

                /*SET OWNER*/
                $where .= ($input['group_id'] <> 0) ? "branches.group_id = " . $input['group_id'] . " AND " : "";

                if (!$this->user->hasAccess('superuser')) {
                    if (($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11)) {
                        $where .= "transactions.owner_id = " . $this->user->owner_id . " AND ";
                    } else {
                        $where .= ($input['owner_id'] <> 0) ? "transactions.owner_id = " . $input['owner_id'] . " AND " : "";
                    }
                } else {
                    $where .= ($input['owner_id'] <> 0) ? "transactions.owner_id = " . $input['owner_id'] . " AND " : "";
                }

                $where .= ($input['branch_id'] <> 0) ? "points_of_sale.branch_id = " . $input['branch_id'] . " AND " : "";
                $where .= ($input['pos_id'] <> 0) ? "points_of_sale.id = " . $input['pos_id'] . " AND " : "";
            }

            $where = trim($where);
            $where = trim($where, 'AND');
            $where = trim($where);

            $payments = \DB::table('payments')
                ->select(\DB::raw('payments.id, tipo_pago, valor_a_pagar, valor_recibido, valor_entregado, payments.created_at, transactions.atm_id, transactions.owner_id, atms.code, points_of_sale.description'))
                ->Join('transactions_x_payments', 'transactions_x_payments.payments_id', '=', 'payments.id')
                ->Join('transactions', 'transactions.id', '=', 'transactions_x_payments.transactions_id')
                ->Join('atms', 'atms.id', '=', 'transactions.atm_id')
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'transactions.atm_id')
                ->leftjoin('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->whereRaw("$where")
                ->groupBy('payments.id', 'tipo_pago', 'valor_a_pagar', 'valor_recibido', 'valor_entregado', 'payments.created_at', 'transactions.atm_id', 'transactions.owner_id', 'atms.code', 'points_of_sale.description')
                ->orderBy('payments.created_at', 'desc')
                ->paginate(20);

            $total_payments = \DB::table('payments')
                ->select(\DB::raw('sum(abs(valor_a_pagar)) as monto'))
                ->Join('transactions_x_payments', 'transactions_x_payments.payments_id', '=', 'payments.id')
                ->Join('transactions', 'transactions.id', '=', 'transactions_x_payments.transactions_id')
                ->Join('atms', 'atms.id', '=', 'transactions.atm_id')
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'transactions.atm_id')
                ->leftjoin('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->whereRaw("$where")
                ->paginate(20);

            $whereGroup = "";
            $whereOwner = "";
            $whereBranch = "";
            $wherePos = "";
            if (!$this->user->hasAccess('superuser')) {
                if ($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11) {
                    $whereOwner = "owners.id = " . $this->user->owner_id;
                    $whereBranch = "branches.owner_id = " . $this->user->owner_id;
                    $wherePos = "points_of_sale.owner_id = " . $this->user->owner_id;
                }
            }
            //Redes
            $groups     = Group::orderBy('business_groups.description')->where(function ($query) use ($whereGroup) {
                if (!empty($whereGroup)) {
                    $query->whereRaw($whereGroup);
                }
            })->get()->pluck('description', 'id');
            $groups->prepend('Todos', '0');

            $owners     = Owner::orderBy('owners.name')->where(function ($query) use ($whereOwner) {
                if (!empty($whereOwner)) {
                    $query->whereRaw($whereOwner);
                }
            })->get()->pluck('name', 'id');
            $owners->prepend('Todos', '0');
            $branches   = Branch::orderBy('description')->where(function ($query) use ($whereBranch) {
                if (!empty($whereBranch)) {
                    $query->whereRaw($whereBranch);
                }
            })->get()->pluck('description', 'id');
            $branches->prepend('Todos', '0');
            $pdvs       = Pos::orderBy('description')->where(function ($query) use ($wherePos) {
                if (!empty($wherePos)) {
                    $query->whereRaw($wherePos);
                }
            })->with('Atm')->get();
            $pos = [];
            $item = array();
            $item[0] = 'Todos';

            foreach ($pdvs  as $pdv) {
                $item[$pdv->id] = $pdv->description . ' - ' . $pdv->Atm->code;
                $pos = $item;
            }
            $resultset = array(
                'target'        => 'Payments',
                'groups'        => $groups,
                'owners'        => $owners,
                'branches'      => $branches,
                'pos'           => $pos,
                'payments'      => $payments,
                'total_payments'    => $total_payments,
                'group_id'      => (isset($input['group_id']) ? $input['group_id'] : 0),
                'owner_id'      => (isset($input['owner_id']) ? $input['owner_id'] : 0),
                'branch_id'     => (isset($input['branch_id']) ? $input['branch_id'] : 0),
                'pos_id'        => (isset($input['pos_id']) ? $input['pos_id'] : 0),
                'status_set'    => (isset($input['status_id']) ? $input['status_id'] : 0),
                'service_id'    => (isset($input['service_id']) ? $input['service_id'] : 0),
                'reservationtime' => (isset($input['reservationtime']) ? $input['reservationtime'] : 0),
                'i'             =>  1
            );
            return $resultset;
        } catch (\Exception $e) {
            \Log::error('error ' . $e);
        }
    }

    /*NOTIFICATIONS*/
    public function notificationsReports()
    {
        try {
            //Redes


            $owners     = Owner::orderBy('name')->get()->pluck('name', 'id');
            $owners->prepend('Todos', '0');
            $branches   = Branch::orderBy('description')->get()->pluck('description', 'id');
            $branches->prepend('Todos', '0');
            //$types = array('0'=>'Todos','1'=>'Estados Atms','2'=>'Servicios','4'=>'Saldos');
            $pdvs       = Pos::orderBy('description')->with('Atm')->get();
            $pos = [];
            $item = array();
            $item[0] = 'Todos';
            foreach ($pdvs  as $pdv) {
                $item[$pdv->id] = $pdv->description . ' - ' . $pdv->Atm->code;
                $pos = $item;
            }

            $resultset = $this->notificationsSearch();


            return $resultset;
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de reportes" . $e->getMessage());
            return false;
        }
    }

    public function notificationsSearch()
    {
        try {
            if (!$this->input) {
                $reservation_time = Carbon::today() . ' - ' . Carbon::today()->endOfDay();
                $branch_id = 0;
                $pos_id = 0;
                $type_id = 0;
                $owner_id = 0;
                $status_id = -1;
            } else {
                $input = $this->input;
                $reservation_time = $input['reservationtime'];
                $branch_id = $input['branch_id'];
                $pos_id = $input['pos_id'];
                $type_id = $input['type_id'];
                $owner_id = $input['owner_id'];
                $status_id = $input['status_id'];
            }

            $where = "";
            /*SET DATE RANGE*/
            $daterange = explode(' - ',  str_replace('/', '-', $reservation_time));
            $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
            $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
            $where .= "notifications.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' AND ";
            $where .= ($status_id <> -1) ? "notifications.processed = " . $status_id . " AND " : "";
            $where .= ($branch_id <> 0) ? "points_of_sale.branch_id = " . $branch_id . " AND " : "";
            $where .= ($pos_id <> 0) ? "points_of_sale.id = " . $pos_id . " AND " : "";
            $where .= ($owner_id <> 0) ? "owners.id = " . $owner_id . " AND " : "";
            $where .= ($type_id > 0) ? "notifications.notification_type = " . $type_id . "AND" : "";

            if ($type_id > 0) {
                $valor = \DB::table('notification_types')
                    ->where('id', $type_id)
                    ->get();
                $idType = $valor[0]->id;
                $descriptionType = $valor[0]->description;
            } else {
                $idType = 0;
                $descriptionType = 'Todos';
            }


            $where = trim($where);
            $where = trim($where, 'AND');
            $where = trim($where);
            $notifications = \DB::table('notifications')
                ->select(\DB::raw('notifications.id as id,notification_types.description as type, points_of_sale.description as pdv, atms.code, notifications.created_at, notifications.updated_at, message, processed, notifications_status.status_description, users.username, services_providers_sources.description as provider, services_providers_sources.id as provider_id, notifications.service_id,notifications.service_source_id,servicios_x_marca.descripcion as service_description'))
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'notifications.atm_id')
                ->join('atms', 'atms.id', '=', 'notifications.atm_id')
                ->join('owners', 'atms.owner_id', '=', 'owners.id')
                ->leftJoin('servicios_x_marca', function ($join) {
                    $join->on('notifications.service_id', '=', 'servicios_x_marca.service_id');
                    $join->on('notifications.service_source_id', '=', 'servicios_x_marca.service_source_id');
                })
                ->join('notification_types', 'notification_types.id', '=', 'notifications.notification_type')
                ->join('notifications_status', 'notifications_status.id', '=', 'notifications.status')
                ->leftjoin('users', 'users.id', '=', 'notifications.asigned_to')
                ->leftjoin('services_providers_sources', 'services_providers_sources.id', '=', 'notifications.service_source_id')
                ->whereRaw("$where")
                ->orderBy('notifications.created_at', 'desc')
                ->paginate(20);
            // foreach ($notifications as $notification) {
            //     if ($notification->service_source_id > 0) {
            //         $service_data = \DB::table('services_ondanet_pairing')->where('service_source_id', $notification->service_source_id)->where('service_request_id', $notification->service_id)->first();
            //         if ($service_data) {
            //             $notification->service_description = $service_data->service_description;
            //         } else {
            //             $notification->service_description = ' Concepto no registrado en ondanet ';
            //         }
            //     } else {
            //         $service_data = \DB::table('service_provider_products')->where('id', $notification->service_id)->first();
            //         if ($service_data) {
            //             $notification->service_description = $service_data->description;
            //         } else {
            //             $notification->service_description = '';
            //         }
            //     }
            // }

            $types = \DB::table('notification_types')
                ->get();

            /*Carga datos del formulario*/
            $owners     = Owner::orderBy('name')->pluck('name', 'id');
            $owners->prepend('Todos', '0');
            $branches   = Branch::orderBy('description')->pluck('description', 'id');
            $branches->prepend('Todos', '0');
            //$types = array('0'=>'Todos','1'=>'Estados Atms','2'=>'Servicios','4'=>'Saldos');
            $status = array('-1' => 'Todos', 'false' => 'Pendientes', 'true' => 'Procesados');
            $pdvs       = Pos::orderBy('description')->with('Atm')->get();
            $pos = [];
            $item = array();
            $item[0] = 'Todos';
            foreach ($pdvs  as $pdv) {
                $item[$pdv->id] = $pdv->description . ' - ' . $pdv->Atm->code;
                $pos = $item;
            }


            $resultset = array(
                'target'        => 'Notificaciones',
                'owners'        => $owners,
                'branches'      => $branches,
                'pos'           => $pos,
                'types'         => $types,
                'status'        => $status,
                'notifications' => $notifications,
                'owner_id'      => (isset($input['owner_id']) ? $input['owner_id'] : 0),
                'branch_id'     => (isset($input['branch_id']) ? $input['branch_id'] : 0),
                'pos_id'        => (isset($input['pos_id']) ? $input['pos_id'] : 0),
                'type_id'       => (isset($input['type_id']) ? $input['type_id'] : 0),
                'status_id'     => (isset($input['status_id']) ? $input['status_id'] : -1),
                'reservationtime' => (isset($input['reservationtime']) ? $input['reservationtime'] : $reservation_time),
                'types' => $types,
                'idType' => $idType,
                'descriptionType' => $descriptionType,
                'i'             =>  1
            );


            return $resultset;
        } catch (\Exception $e) {

            $error_detail = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];

            \Log::error("Ocurrió un error. Detalles:");
            \Log::error($error_detail);
            return false;
        }
    }

    public function notificationsSearchExport()
    {
        try {
            $input = $this->input;
            $reservation_time = $input['reservationtime'];
            $branch_id = $input['branch_id'];
            $pos_id = $input['pos_id'];
            $type_id = $input['type_id'];
            $status_id = $input['status_id'];
            $owner_id = $input['owner_id'];

            $where = "";

            /*SET DATE RANGE*/
            $daterange = explode(' - ',  str_replace('/', '-', $reservation_time));
            $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
            $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
            $where .= "notifications.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' AND ";
            $where .= ($status_id <> -1) ? "notifications.processed = " . $status_id . " AND " : "";
            $where .= ($branch_id <> 0) ? "points_of_sale.branch_id = " . $branch_id . " AND " : "";
            $where .= ($pos_id <> 0) ? "points_of_sale.id = " . $pos_id . " AND " : "";
            $where .= ($owner_id <> 0) ? "owners.id = " . $owner_id . " AND " : "";
            $where .= ($type_id > 0) ? "notifications.notification_type = " . $type_id . "AND" : "";

            $where = trim($where);
            $where = trim($where, 'AND');
            $where = trim($where);

            $notifications = \DB::table('notifications')
                //->select(\DB::raw('notifications.id as id,notification_types.description as type, points_of_sale.description as pdv, atms.code, notifications.created_at, notifications.updated_at, message, processed, notifications_status.status_description, users.username, services_providers_sources.description as provider, services_providers_sources.id as provider_id, notifications.service_id,notifications.service_source_id'))
                ->select(\DB::raw('notifications.id as id, notifications.service_id, points_of_sale.description as sucursal, notifications_status.status_description as estado,notification_types.description as tipo, message as mensaje, notifications.created_at as fecha_inicio, notifications.updated_at as fecha_fin, users.username as asignado_a,notifications.service_source_id,servicios_x_marca.descripcion as service_description'))
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'notifications.atm_id')
                ->join('atms', 'atms.id', '=', 'notifications.atm_id')
                ->join('owners', 'atms.owner_id', '=', 'owners.id')
                ->leftJoin('servicios_x_marca', function ($join) {
                    $join->on('notifications.service_id', '=', 'servicios_x_marca.service_id');
                    $join->on('notifications.service_source_id', '=', 'servicios_x_marca.service_source_id');
                })
                ->join('notification_types', 'notification_types.id', '=', 'notifications.notification_type')
                ->join('notifications_status', 'notifications_status.id', '=', 'notifications.status')
                ->leftjoin('users', 'users.id', '=', 'notifications.asigned_to')
                ->leftjoin('services_providers_sources', 'services_providers_sources.id', '=', 'notifications.service_source_id')
                ->whereRaw("$where")
                ->orderBy('notifications.created_at', 'desc')
                ->get();

            // foreach ($notifications as $notification) {
            //     if ($notification->service_source_id > 0) {
            //         $service_data = \DB::table('services_ondanet_pairing')->where('service_source_id', $notification->service_source_id)->where('service_request_id', $notification->service_id)->first();
            //         if ($service_data) {
            //             $notification->service_description = $service_data->service_description;
            //         } else {
            //             $notification->service_description = ' Concepto no registrado en ondanet ';
            //         }
            //     } else {
            //         $service_data = \DB::table('service_provider_products')->where('id', $notification->service_id)->first();
            //         if ($service_data) {
            //             $notification->service_description = $service_data->description;
            //         } else {
            //             $notification->service_description = '';
            //         }
            //     }
            //     unset($notification->service_source_id);
            // }

            return $notifications;
        } catch (\Exception $e) {
            $error_detail = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];

            \Log::error("Ocurrió un error. Detalles:");
            \Log::error($error_detail);
            return false;
        }
    }

    /*ARQUEO Y CARGAS */
    public function arqueosReports()
    {
        try {

            $whereGroup = "";
            $whereOwner = "";
            $whereBranch = "";
            $wherePos = "";
            ///////////////////////////////////////////////////////////////
            if (!\Sentinel::getUser()->inRole('mini_terminal') && !\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                $branches   = Branch::orderBy('description')->where(function ($query) use ($whereBranch) {
                    if (!empty($whereBranch)) {
                        $query->whereRaw($whereBranch);
                    }
                })->get()->pluck('description', 'id');
                $branches->prepend('Todos', '0');
            } else if (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {

                if ($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11) {
                    $whereOwner = "owners.id = " . $this->user->owner_id;
                    $whereBranch = "branches.owner_id = " . $this->user->owner_id;
                    $wherePos = "points_of_sale.owner_id = " . $this->user->owner_id;

                    $supervisor = \DB::table('users_x_groups')->where('user_id', $this->user->id)->first();

                    $branchess = \DB::table('branches')
                        ->select(['branches.description', 'users.username', 'users.id'])
                        ->join('users', 'branches.user_id', '=', 'users.id')
                        ->join('role_users', 'users.id', '=', 'role_users.user_id')
                        ->where('role_users.role_id', 22)
                        ->where('branches.group_id', $supervisor->group_id)
                        ->get();
                    $branches = [];
                    foreach ($branchess as $key => $branch) {
                        $branches[$branch->id] = $branch->description . ' | ' . $branch->username;
                    }
                    //$branchess->prepend('Todos','0');
                }
            } else {
                $branches = \DB::table('branches')
                    ->select(['branches.description', 'users.username', 'users.id'])
                    ->join('users', 'branches.user_id', '=', 'users.id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->where('branches.user_id', $this->user->id)
                    ->get();
            }
            //Redes
            $groups     = Group::orderBy('business_groups.description')->where(function ($query) use ($whereGroup) {
                if (!empty($whereGroup)) {
                    $query->whereRaw($whereGroup);
                }
            })->get()->pluck('description', 'id');
            $groups->prepend('Todos', '0');

            $owners     = Owner::orderBy('owners.name')->where(function ($query) use ($whereOwner) {
                if (!empty($whereOwner)) {
                    $query->whereRaw($whereOwner);
                }
            })->get()->pluck('name', 'id');
            $owners->prepend('Todos', '0');
            $pdvs       = Pos::orderBy('description')->where(function ($query) use ($wherePos) {
                if (!empty($wherePos)) {
                    $query->whereRaw($wherePos);
                }
            })->with('Atm')->get();
            $pos = [];
            $item = array();
            $item[0] = 'Todos';
            foreach ($pdvs  as $pdv) {
                $item[$pdv->id] = $pdv->description . ' - ' . $pdv->Atm->code;
                $pos = $item;
            }

            $resultset = array(
                'target'        => 'Arqueos',
                'groups'        => $groups,
                'owners'        => $owners,
                'branches'      => $branches,
                'pos'           => $pos,
                'group_id'      => 0,
                'owner_id'      => 0,
                'branch_id'     => 0,
                'pos_id'        => 0
            );


            return $resultset;
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de reportes" . $e->getMessage());
            return redirect('/');
        }
    }

    public function arqueosSearch()
    {
        try {
            $input = $this->input;

            //$where = "transactions.transaction_type in (2,3) AND ";
            $where = "transactions.transaction_type in (2,3,8,9,10) AND ";

            /*Busqueda minusiosa*/
            if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
                //$where .= "transactions.request_data like '%{$input['context']}%'";
            } else {
                /*SET DATE RANGE*/
                $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                $where .= "transactions.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' AND ";

                /*SET OWNER*/
                if (isset($input['group_id'])) {
                    $where .= ($input['group_id'] <> 0) ? "branches.group_id = " . $input['group_id'] . " AND " : "";
                }

                if (!$this->user->hasAccess('superuser')) {
                    if (($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11)) {
                        $where .= "transactions.owner_id = " . $this->user->owner_id . " AND ";
                    } else {
                        if (isset($input['owner_id'])) {
                            $where .= ($input['owner_id'] <> 0) ? "transactions.owner_id = " . $input['owner_id'] . " AND " : "";
                        }
                    }
                } else {
                    if (isset($input['owner_id'])) {
                        $where .= ($input['owner_id'] <> 0) ? "transactions.owner_id = " . $input['owner_id'] . " AND " : "";
                    }
                }

                //USER GET BRANCH
                if ($this->user->branch_id <> null) {
                    $input['branch_id'] = $this->user->branch_id;
                }

                if (isset($input['branch_id'])) {
                    $where .= ($input['branch_id'] <> 0) ? "points_of_sale.branch_id = " . $input['branch_id'] . " AND " : "";
                }

                if (isset($input['pos_id'])) {

                    $where .= ($input['pos_id'] <> 0) ? "points_of_sale.id = " . $input['pos_id'] . " AND " : "";
                }
            }
            if (\Sentinel::getUser()->inRole('mini_terminal')) {
                //obtener los branches asociados a un usuario
                $branches = \DB::table('points_of_sale')
                    ->select('branch_id')
                    ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                    ->where('branches.user_id', '=', $this->user->id)
                    ->first();

                $where .= ' points_of_sale.branch_id = ' . $branches->branch_id . ' AND ';
            }
            if (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                $supervisor = \DB::table('users_x_groups')->where('user_id', $this->user->id)->first();
                $branches = \DB::table('branches')
                    ->select(['branches.description', 'users.username', 'users.id'])
                    ->join('users', 'branches.user_id', '=', 'users.id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->where('branches.group_id', $supervisor->group_id)
                    ->get();
                $atms = \DB::table('branches')
                    ->select(['points_of_sale.atm_id'])
                    ->join('users', 'branches.user_id', '=', 'users.id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->join('points_of_sale', 'branches.id', '=', 'points_of_sale.branch_id')
                    ->where('role_users.role_id', 22)
                    ->where('branches.group_id', $supervisor->group_id)
                    ->whereNotNull('atm_id')
                    ->where(function ($query) use ($input) {
                        if (!empty($input['user_id'])) {
                            $query->where('branches.user_id', $input['user_id']);
                        }
                    })
                    ->pluck('atm_id', 'atm_id');

                $atm_id = '(';
                foreach ($atms as $id_atm => $atm) {
                    $atm_id .= $atm . ', ';
                }

                $atm_id = rtrim($atm_id, ', ');
                $atm_id .= ')';

                //$where .= ' branches.user_id = '.$input['user_id'];
                $where .= ' atms.id in ' . $atm_id;
            }

            $where = trim($where);
            $where = trim($where, 'AND');
            $where = trim($where);

            /*$transactions = \DB::table('transactions')
                    ->select(\DB::raw('transactions.id,transactions.amount,transactions.atm_transaction_id, transaction_tickets.id as ticket_id,transaction_tickets.reprinted, transaction_types.description as transaction_type ,transactions.created_at,points_of_sale.description as sede, atms.code as code, users.username as autorizador ,atm_arqueos_hash.user_id as autorizado'))
                    ->join('points_of_sale','points_of_sale.atm_id','=','transactions.atm_id')
                    ->join('atms','atms.id','=','transactions.atm_id')
                    ->join('transaction_types','transaction_types.id','=','transactions.transaction_type')
                    ->leftjoin('transaction_tickets','transaction_tickets.transaction_id','=','transactions.id')
                    ->leftjoin('transactions_x_arqueos','transactions_x_arqueos.transaction_id','=','transactions.id')
                    ->leftjoin('atm_arqueos_hash','transactions_x_arqueos.atm_arqueos_hash_id','=','atm_arqueos_hash.id')
                    ->leftjoin('users','users.id','=','atm_arqueos_hash.cms_user_id')
                    ->leftjoin('branches','branches.id','=','points_of_sale.branch_id')
                    ->whereRaw("$where")
                    ->orderBy('transactions.created_at','desc')
                    ->Paginate(20);


                foreach ($transactions as $transaction){
                    if($transaction->autorizado){
                        $autorizado = User::find($transaction->autorizado);
                        $username = $autorizado->username;
                        $transaction->autorizado = $username;
                    }
                }*/

            $transactions = \DB::table('transactions')
                ->select(
                    \DB::raw('
                            transactions.id,
                            transactions.amount,
                            transactions.atm_transaction_id, 
                            transaction_tickets.id as ticket_id,
                            transaction_tickets.reprinted, 
                            transaction_types.description as transaction_type,
                            transactions.created_at,
                            points_of_sale.description as sede, 
                            atms.code as code, 
                            users.username as autorizador,
                            u2.username as autorizado
                        ')
                )
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'transactions.atm_id')
                ->join('atms', 'atms.id', '=', 'transactions.atm_id')
                ->join('transaction_types', 'transaction_types.id', '=', 'transactions.transaction_type')
                ->leftjoin('transaction_tickets', 'transaction_tickets.transaction_id', '=', 'transactions.id')
                ->leftjoin('transactions_x_arqueos', 'transactions_x_arqueos.transaction_id', '=', 'transactions.id')
                ->leftjoin('atm_arqueos_hash', 'transactions_x_arqueos.atm_arqueos_hash_id', '=', 'atm_arqueos_hash.id')
                ->leftjoin('users', 'users.id', '=', 'atm_arqueos_hash.cms_user_id')
                ->leftjoin('users as u2', 'u2.id', '=', 'atm_arqueos_hash.user_id')
                ->leftjoin('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->whereRaw("$where")
                ->orderBy('transactions.created_at', 'desc')
                ->paginate(10);

            //\Log::info($transactions->toSql());
            //$transactions = $transactions->get();
            //\Log::info($transactions);

            /*foreach ($transactions as $transaction){
                    if($transaction->autorizado){
                        $autorizado = User::find($transaction->autorizado);
                        $username = $autorizado->username;
                        $transaction->autorizado = $username;
                    }
                }*/

            /*Carga datos del formulario*/
            $whereGroup = "";
            $whereOwner = "";
            $whereBranch = "";
            $wherePos = "";

            if (!$this->user->hasAccess('superuser')) {
                if ($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11) {
                    $whereOwner = "owners.id = " . $this->user->owner_id;
                    $whereBranch = "branches.owner_id = " . $this->user->owner_id;
                    $wherePos = "points_of_sale.owner_id = " . $this->user->owner_id;
                }
            }

            ///////////////////////////////////////////////////////////////
            if (!\Sentinel::getUser()->inRole('mini_terminal') && !\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                $branches   = Branch::orderBy('description')->where(function ($query) use ($whereBranch) {
                    if (!empty($whereBranch)) {
                        $query->whereRaw($whereBranch);
                    }
                })->get()->pluck('description', 'id');
                $branches->prepend('Todos', '0');
            } else if (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {

                if ($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11) {
                    $whereOwner = "owners.id = " . $this->user->owner_id;
                    $whereBranch = "branches.owner_id = " . $this->user->owner_id;
                    $wherePos = "points_of_sale.owner_id = " . $this->user->owner_id;

                    $supervisor = \DB::table('users_x_groups')->where('user_id', $this->user->id)->first();

                    $branchess = \DB::table('branches')
                        ->select(['branches.description', 'users.username', 'users.id'])
                        ->join('users', 'branches.user_id', '=', 'users.id')
                        ->join('role_users', 'users.id', '=', 'role_users.user_id')
                        ->where('role_users.role_id', 22)
                        ->where('branches.group_id', $supervisor->group_id)
                        ->get();
                    $branches = [];
                    foreach ($branchess as $key => $branch) {
                        $branches[$branch->id] = $branch->description . ' | ' . $branch->username;
                    }
                }
            } else {
                $branches = \DB::table('branches')
                    ->select(['branches.description', 'users.username', 'users.id'])
                    ->join('users', 'branches.user_id', '=', 'users.id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->where('branches.user_id', $this->user->id)
                    ->get();
            }

            //Redes
            $groups     = Group::orderBy('business_groups.description')->where(function ($query) use ($whereGroup) {
                if (!empty($whereGroup)) {
                    $query->whereRaw($whereGroup);
                }
            })->get()->pluck('description', 'id');
            $groups->prepend('Todos', '0');

            $owners     = Owner::orderBy('owners.name')->where(function ($query) use ($whereOwner) {
                if (!empty($whereOwner)) {
                    $query->whereRaw($whereOwner);
                }
            })->get()->pluck('name', 'id');
            $owners->prepend('Todos', '0');

            $pdvs       = Pos::orderBy('description')->where(function ($query) use ($wherePos) {
                if (!empty($wherePos)) {
                    $query->whereRaw($wherePos);
                }
            })->with('Atm')->get();
            $pos = [];
            $item = array();
            $item[0] = 'Todos';
            foreach ($pdvs  as $pdv) {
                $item[$pdv->id] = $pdv->description . ' - ' . $pdv->Atm->code;
                $pos = $item;
            }

            $reservationtime = (isset($input['reservationtime']) ? $input['reservationtime'] : 0);

            $resultset = array(
                'target'        => 'Arqueos',
                'groups'        => $groups,
                'owners'        => $owners,
                'branches'      => $branches,
                'pos'           => $pos,
                'transactions'       => $transactions,
                'group_id'      => (isset($input['group_id']) ? $input['group_id'] : 0),
                'owner_id'      => (isset($input['owner_id']) ? $input['owner_id'] : 0),
                'branch_id'     => (isset($input['branch_id']) ? $input['branch_id'] : 0),
                'pos_id'        => (isset($input['pos_id']) ? $input['pos_id'] : 0),
                'reservationtime' => (isset($input['reservationtime']) ? $input['reservationtime'] : $reservationtime),
                'i'             =>  1
            );

            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    public function arqueosSearchExport()
    {
        try {
            $input = $this->input;
            //$where = "transactions.transaction_type in (2,3) AND ";
            $where = "transactions.transaction_type in (2,3,8,9,10) AND ";
            /*Busqueda minusiosa*/
            if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
                //$where .= "transactions.request_data like '%{$input['context']}%'";
            } else {
                /*SET DATE RANGE*/
                $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                $where .= "transactions.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' AND ";

                /*SET OWNER*/

                if (isset($input['group_id'])) {
                    $where .= ($input['group_id'] <> 0) ? "branches.group_id = " . $input['group_id'] . " AND " : "";
                }

                if (!$this->user->hasAccess('superuser')) {
                    if (($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11)) {
                        $where .= "transactions.owner_id = " . $this->user->owner_id . " AND ";
                    } else {
                        if (isset($input['owner_id'])) {
                            $where .= ($input['owner_id'] <> 0) ? "transactions.owner_id = " . $input['owner_id'] . " AND " : "";
                        }
                    }
                } else {
                    if (isset($input['owner_id'])) {
                        $where .= ($input['owner_id'] <> 0) ? "transactions.owner_id = " . $input['owner_id'] . " AND " : "";
                    }
                }

                //USER GET BRANCH
                if ($this->user->branch_id <> null) {
                    $input['branch_id'] = $this->user->branch_id;
                }

                if (isset($input['branch_id'])) {
                    $where .= ($input['branch_id'] <> 0) ? "points_of_sale.branch_id = " . $input['branch_id'] . " AND " : "";
                }

                if (isset($input['pos_id'])) {

                    $where .= ($input['pos_id'] <> 0) ? "points_of_sale.id = " . $input['pos_id'] . " AND " : "";
                }
            }

            if (\Sentinel::getUser()->inRole('mini_terminal')) {
                //obtener los branches asociados a un usuario
                $branches = \DB::table('points_of_sale')
                    ->select('branch_id')
                    ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                    ->where('branches.user_id', '=', $this->user->id)
                    ->first();

                $where .= ' points_of_sale.branch_id = ' . $branches->branch_id . ' AND ';
            }
            if (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                $supervisor = \DB::table('users_x_groups')->where('user_id', $this->user->id)->first();
                $branches = \DB::table('branches')
                    ->select(['branches.description', 'users.username', 'users.id'])
                    ->join('users', 'branches.user_id', '=', 'users.id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->where('branches.group_id', $supervisor->group_id)
                    ->get();
                $atms = \DB::table('branches')
                    ->select(['points_of_sale.atm_id'])
                    ->join('users', 'branches.user_id', '=', 'users.id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->join('points_of_sale', 'branches.id', '=', 'points_of_sale.branch_id')
                    ->where('role_users.role_id', 22)
                    ->where('branches.group_id', $supervisor->group_id)
                    ->whereNotNull('atm_id')
                    ->where(function ($query) use ($input) {
                        if (!empty($input['user_id'])) {
                            $query->where('branches.user_id', $input['user_id']);
                        }
                    })
                    ->pluck('atm_id', 'atm_id');

                $atm_id = '(';
                foreach ($atms as $id_atm => $atm) {
                    $atm_id .= $atm . ', ';
                }

                $atm_id = rtrim($atm_id, ', ');
                $atm_id .= ')';

                //$where .= ' branches.user_id = '.$input['user_id'];
                $where .= ' atms.id in ' . $atm_id;
            }

            $where = trim($where);
            $where = trim($where, 'AND');
            $where = trim($where);

            $transactions = \DB::table('transactions')
                ->select(\DB::raw('transactions.id,transactions.created_at as fecha, transactions.amount as valor, users.username as autorizado_por,atms.code as codigo_cajero, transaction_types.description as tipo,atm_arqueos_hash.user_id as operativo,points_of_sale.description as sede'))
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'transactions.atm_id')
                ->join('atms', 'atms.id', '=', 'transactions.atm_id')
                ->join('transaction_types', 'transaction_types.id', '=', 'transactions.transaction_type')
                ->leftjoin('transactions_x_arqueos', 'transactions_x_arqueos.transaction_id', '=', 'transactions.id')
                ->leftjoin('atm_arqueos_hash', 'transactions_x_arqueos.atm_arqueos_hash_id', '=', 'atm_arqueos_hash.id')
                ->leftjoin('users', 'users.id', '=', 'atm_arqueos_hash.cms_user_id')
                ->whereRaw("$where")
                ->orderBy('transactions.created_at', 'desc')
                ->get();

            foreach ($transactions as $transaction) {
                if (isset($transaction->operativo)) {
                    $autorizado = $transaction->operativo;
                    $autorizado = User::find($autorizado);
                    $username = $autorizado->username;
                    $transaction->autorizado = $username;
                } else {
                    $transaction->autorizado = null;
                }
                $transaction->valor = number_format($transaction->valor, 0, '', '');
                unset($transaction->operativo);
            }

            return $transactions;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    public function cargasReports()
    {
        try {
            $whereGroup = "";
            $whereOwner = "";
            $whereBranch = "";
            $wherePos = "";
            if (!$this->user->hasAccess('superuser')) {
                if ($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11) {
                    $whereOwner = "owners.id = " . $this->user->owner_id;
                    $whereBranch = "branches.owner_id = " . $this->user->owner_id;
                    $wherePos = "points_of_sale.owner_id = " . $this->user->owner_id;
                }
            }
            //Redes
            $groups     = Group::orderBy('business_groups.description')->where(function ($query) use ($whereGroup) {
                if (!empty($whereGroup)) {
                    $query->whereRaw($whereGroup);
                }
            })->get()->pluck('description', 'id');
            $groups->prepend('Todos', '0');

            $owners     = Owner::orderBy('owners.name')->where(function ($query) use ($whereOwner) {
                if (!empty($whereOwner)) {
                    $query->whereRaw($whereOwner);
                }
            })->get()->pluck('name', 'id');
            $owners->prepend('Todos', '0');
            $branches   = Branch::orderBy('description')->where(function ($query) use ($whereBranch) {
                if (!empty($whereBranch)) {
                    $query->whereRaw($whereBranch);
                }
            })->get()->pluck('description', 'id');
            $branches->prepend('Todos', '0');
            $pdvs       = Pos::orderBy('description')->where(function ($query) use ($wherePos) {
                if (!empty($wherePos)) {
                    $query->whereRaw($wherePos);
                }
            })->with('Atm')->get();
            $pos = [];
            $item = array();
            $item[0] = 'Todos';
            foreach ($pdvs  as $pdv) {
                $item[$pdv->id] = $pdv->description . ' - ' . $pdv->Atm->code;
                $pos = $item;
            }
            $resultset = array(
                'target'        => 'Cargas',
                'groups'        => $groups,
                'owners'        => $owners,
                'branches'      => $branches,
                'pos'           => $pos,
                'group_id'      => 0,
                'owner_id'      => 0,
                'branch_id'     => 0,
                'pos_id'        => 0
            );

            return $resultset;
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de reportes" . $e->getMessage());
            return redirect('/');
        }
    }

    public function cargasSearch()
    {
        try {

            $input = $this->input;
            $where = "";
            /*Busqueda minusiosa*/
            if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
                //$where .= "transactions.request_data like '%{$input['context']}%'";
            } else {
                /*SET DATE RANGE*/
                $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                $where .= "transactions.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' AND ";

                /*SET OWNER*/
                $where .= ($input['group_id'] <> 0) ? "branches.group_id = " . $input['group_id'] . " AND " : "";

                if (!$this->user->hasAccess('superuser')) {
                    if (($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11)) {
                        $where .= "transactions.owner_id = " . $this->user->owner_id . " AND ";
                    } else {
                        $where .= ($input['owner_id'] <> 0) ? "transactions.owner_id = " . $input['owner_id'] . " AND " : "";
                    }
                } else {
                    $where .= ($input['owner_id'] <> 0) ? "transactions.owner_id = " . $input['owner_id'] . " AND " : "";
                }
                $where .= ($input['branch_id'] <> 0) ? "points_of_sale.branch_id = " . $input['branch_id'] . " AND " : "";
                $where .= ($input['pos_id'] <> 0) ? "points_of_sale.id = " . $input['pos_id'] . " AND " : "";
            }

            $where .= "(transactions.transaction_type = 4 OR transactions.transaction_type = 5 OR transactions.transaction_type = 6)";

            $where = trim($where);
            $where = trim($where, 'AND');
            $where = trim($where);
            $transactions = \DB::table('transactions')
                ->select(\DB::raw('transactions.id,transactions.amount, transaction_tickets.id as ticket_id,transaction_tickets.reprinted, transactions.atm_transaction_id,transaction_types.description as transaction_type ,transactions.created_at,points_of_sale.description as sede, atms.code as code, users.username as autorizador ,atm_arqueos_hash.user_id as autorizado'))
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'transactions.atm_id')
                ->join('atms', 'atms.id', '=', 'transactions.atm_id')
                ->join('transaction_types', 'transaction_types.id', '=', 'transactions.transaction_type')
                ->leftjoin('transaction_tickets', 'transaction_tickets.transaction_id', '=', 'transactions.id')
                ->leftjoin('transactions_x_arqueos', 'transactions_x_arqueos.transaction_id', '=', 'transactions.id')
                ->leftjoin('atm_arqueos_hash', 'transactions_x_arqueos.atm_arqueos_hash_id', '=', 'atm_arqueos_hash.id')
                ->leftjoin('users', 'users.id', '=', 'atm_arqueos_hash.cms_user_id')
                ->leftjoin('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->whereRaw("$where")
                ->orderBy('transactions.created_at', 'desc')
                ->paginate(20);

            foreach ($transactions as $transaction) {
                if (isset($transaction->autorizado)) {
                    $autorizado = User::find($transaction->autorizado);
                    $username = $autorizado->username;
                    $transaction->autorizado = $username;
                }
            }

            /*Carga datos del formulario*/
            $whereGroup = "";
            $whereOwner = "";
            $whereBranch = "";
            $wherePos = "";
            if (!$this->user->hasAccess('superuser')) {
                if ($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11) {
                    $whereOwner = "owners.id = " . $this->user->owner_id;
                    $whereBranch = "branches.owner_id = " . $this->user->owner_id;
                    $wherePos = "points_of_sale.owner_id = " . $this->user->owner_id;
                }
            }
            //Redes
            $groups     = Group::orderBy('business_groups.description')->where(function ($query) use ($whereGroup) {
                if (!empty($whereGroup)) {
                    $query->whereRaw($whereGroup);
                }
            })->get()->pluck('description', 'id');
            $groups->prepend('Todos', '0');

            $owners     = Owner::orderBy('owners.name')->where(function ($query) use ($whereOwner) {
                if (!empty($whereOwner)) {
                    $query->whereRaw($whereOwner);
                }
            })->get()->pluck('name', 'id');
            $owners->prepend('Todos', '0');
            $branches   = Branch::orderBy('description')->where(function ($query) use ($whereBranch) {
                if (!empty($whereBranch)) {
                    $query->whereRaw($whereBranch);
                }
            })->get()->pluck('description', 'id');
            $branches->prepend('Todos', '0');
            $pdvs       = Pos::orderBy('description')->where(function ($query) use ($wherePos) {
                if (!empty($wherePos)) {
                    $query->whereRaw($wherePos);
                }
            })->with('Atm')->get();
            $pos = [];
            $item = array();
            $item[0] = 'Todos';
            foreach ($pdvs  as $pdv) {
                $item[$pdv->id] = $pdv->description . ' - ' . $pdv->Atm->code;
                $pos = $item;
            }

            $reservationtime = (isset($input['reservationtime']) ? $input['reservationtime'] : 0);

            $resultset = array(
                'target'        => 'Cargas',
                'groups'        => $groups,
                'owners'        => $owners,
                'branches'      => $branches,
                'pos'           => $pos,
                'transactions'       => $transactions,
                'group_id'      => (isset($input['group_id']) ? $input['group_id'] : 0),
                'owner_id'      => (isset($input['owner_id']) ? $input['owner_id'] : 0),
                'branch_id'     => (isset($input['branch_id']) ? $input['branch_id'] : 0),
                'pos_id'        => (isset($input['pos_id']) ? $input['pos_id'] : 0),
                'reservationtime' => (isset($input['reservationtime']) ? $input['reservationtime'] : $reservationtime),
                'i'             =>  1
            );

            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
        }
    }

    public function cargasSearchExport()
    {
        try {

            $input = $this->input;
            $where = "";
            /*Busqueda minusiosa*/
            if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
                //$where .= "transactions.request_data like '%{$input['context']}%'";
            } else {
                /*SET DATE RANGE*/
                $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                $where .= "transactions.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' AND ";

                /*SET OWNER*/
                if (!$this->user->hasAccess('superuser')) {
                    if (($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11)) {
                        $where .= "transactions.owner_id = " . $this->user->owner_id . " AND ";
                    } else {
                        $where .= ($input['owner_id'] <> 0) ? "transactions.owner_id = " . $input['owner_id'] . " AND " : "";
                    }
                } else {
                    $where .= ($input['owner_id'] <> 0) ? "transactions.owner_id = " . $input['owner_id'] . " AND " : "";
                }

                $where .= ($input['branch_id'] <> 0) ? "points_of_sale.branch_id = " . $input['branch_id'] . " AND " : "";
                $where .= ($input['pos_id'] <> 0) ? "points_of_sale.id = " . $input['pos_id'] . " AND " : "";
            }

            $where .= "(transactions.transaction_type = 4 OR transactions.transaction_type = 5 OR transactions.transaction_type = 6)";

            $where = trim($where);
            $where = trim($where, 'AND');
            $where = trim($where);

            $transactions = \DB::table('transactions')
                ->select(\DB::raw('transactions.id,transactions.created_at as fecha, transactions.amount as valor, users.username as autorizado_por,atms.code as codigo_cajero, transaction_types.description as tipo,atm_arqueos_hash.user_id as operativo,points_of_sale.description as sede'))
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'transactions.atm_id')
                ->join('atms', 'atms.id', '=', 'transactions.atm_id')
                ->join('transaction_types', 'transaction_types.id', '=', 'transactions.transaction_type')
                ->leftjoin('transactions_x_arqueos', 'transactions_x_arqueos.transaction_id', '=', 'transactions.id')
                ->leftjoin('atm_arqueos_hash', 'transactions_x_arqueos.atm_arqueos_hash_id', '=', 'atm_arqueos_hash.id')
                ->leftjoin('users', 'users.id', '=', 'atm_arqueos_hash.cms_user_id')
                ->whereRaw("$where")
                ->orderBy('transactions.created_at', 'desc')
                ->get();

            foreach ($transactions as $transaction) {
                if (isset($transaction->operativo)) {
                    $autorizado = $transaction->operativo;
                    $autorizado = User::find($autorizado);
                    $username = $autorizado->username;
                    $transaction->autorizado = $username;
                } else {
                    $transaction->autorizado = null;
                }

                $transaction->valor = number_format($transaction->valor, 0, '', '');
                unset($transaction->operativo);
            }

            return $transactions;
        } catch (\Exception $e) {
            \Log::info($e);
        }
    }

    public function atmNotifications($atm_id)
    {
        $notifications = \DB::connection('eglobalt_replica')->table('notifications')
            ->orderBy('created_at', 'DESC')
            ->take(3)
            ->where('notifications.atm_id', '=', $atm_id)
            ->where('notifications.processed', '=', false)
            ->whereIn('notifications.notification_type', [1, 3, 4])
            ->join('notification_types', 'notification_types.id', '=', 'notifications.notification_type')
            ->join('notifications_status', 'notifications_status.id', '=', 'notifications.status')
            ->get();
        $details = '';
        foreach ($notifications as $notification) {
            $details .= '<tr>
                  <td>' . $notification->description . '</td>
                  <td>' . $notification->message . '</td>
                  <td>' . Carbon::parse($notification->created_at)->format('d/m/Y H:i:s') . '</td>
                  <td>' . Carbon::parse($notification->updated_at)->format('d/m/Y H:i:s') . '</td>
                  <td>' . Carbon::parse($notification->updated_at)->diffInMinutes(Carbon::parse($notification->created_at)) . ' minutos </td>
                  </tr>';
        }

        return $details;
    }

    public function oneDayTransactionsReports()
    {
        try {
            //Redes
            $whereGroup = "";
            $whereOwner = "";
            $whereBranch = "";
            $wherePos = "";
            if (!$this->user->hasAccess('superuser')) {
                if ($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11) {
                    $whereOwner = "owners.id = " . $this->user->owner_id;
                    $whereBranch = "branches.owner_id = " . $this->user->owner_id;
                    $wherePos = "points_of_sale.owner_id = " . $this->user->owner_id;
                }
            }
            //Redes
            $groups     = Group::orderBy('business_groups.description')->where(function ($query) use ($whereGroup) {
                if (!empty($whereGroup)) {
                    $query->whereRaw($whereGroup);
                }
            })->get()->pluck('description', 'id')->prepend('Todos', '0')->toArray();

           

            $owners     = Owner::orderBy('owners.name')->where(function ($query) use ($whereOwner) {
                if (!empty($whereOwner)) {
                    $query->whereRaw($whereOwner);
                }
            })->get()->pluck('name', 'id')->prepend('Todos', '0')->toArray();

            $branches   = Branch::orderBy('description')->where(function ($query) use ($whereBranch) {
                if (!empty($whereBranch)) {
                    $query->whereRaw($whereBranch);
                }
            })->get()->pluck('description', 'id')->prepend('Todos', '0')->toArray();

            $pdvs       = Pos::orderBy('description')->where(function ($query) use ($wherePos) {
                if (!empty($wherePos)) {
                    $query->whereRaw($wherePos);
                }
            })->with('Atm')->get();


            $pos = [];
            $item = array();
            $item[0] = 'Todos';

           // dd($pdvs);
    
            foreach ($pdvs  as $key => $pdv) {
                $item[$pdv->id] = $pdv->description . ' - ' . $pdv->Atm->code;
                $pos = $item;
            }

           // dd([$pos]);

            $status = array('0' => 'Todos', 'success' => 'Aprobado', 'canceled' => 'Cancelado', 'error' => 'Error', 'rollback' => 'Reversado', 'iniciated' => 'Iniciado', 'error dispositivo' => 'Error de dispositivo', 'inconsistency' => 'Inconsistencia');
            $atmType = array('0' => 'Todos', 'da' => 'App Billetaje', 'ws' => 'Web Service', 'at' => 'Atm');
            $services   = ServiceProviderProduct::with('WebServiceProvider')->orderBy('service_provider_id', 'DESC')->get();
            $services_data = [];
            $service_item = array();
            $payment_methods = array('0' => 'Todos', 'efectivo' => 'Efectivo', 'canje' => 'Canje', 'QR' => 'Todos QR', 'TC' => 'Tarjeta de crédito', 'TD' => 'Tarjeta de débito');

           

            if ($this->user->hasAccess('ticketea') && !$this->user->hasAccess('superuser')) {
                $service_item[28] = 'Ticketea - Venta de tickets';
                $services_data = $service_item;
            } else {
                $service_item[0] = 'Todos';
                $service_item[-2] = 'Netel';
                $service_item[-4] = 'Pronet';
                $service_item[-6] = 'Practipago';
                $service_item[-7] = 'Infonet';
                $service_item[-8] = 'Toval';
                $service_item[-10] = 'Netel Trex';
                $service_item[-11] = 'Bancard Ventas QR';
                foreach ($services  as $service) {
                    if ($service->id <> 100) {
                        if ($service->id <> 27 && $service->id <> -1 && $service->WebServiceProvider->name <> 'Toval - Integraciones' && $service->WebServiceProvider->name <> 'Momo - Ken') {
                            $service_item[$service->id] = $service->WebServiceProvider->name . ' - ' . $service->description;
                            $services_data = $service_item;
                        }
                    }
                }
            }



            $resultset = array(
                'target'        => 'Transacciones del Dia',
                'groups'        => $groups,
                'payment_methods' => $payment_methods,
                'payment_methods_set' => 0,
                'owners'        => $owners,
                'branches'      => $branches,
                'pos'           => $pos,
                'status'        => $status,
                'type'          => $atmType,
                'services_data' => $services_data,
                'group_id'      => 0,
                'owner_id'      => 0,
                'branch_id'     => 0,
                'pos_id'        => 0,
                'status_set'    => 0,
                'type_set'    => 0,
                'service_id'    => 0,
                'service_request_id'    => '',
            );

           

            return $resultset;
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de reportes" . $e);
            return false;
        }
    }

    public function oneDayTransactionsSearch()
    {

        try {
            $input = $this->input;
            $where = "transactions.transaction_type in (1,7,11,12,13) AND ";
            /*Busqueda minusiosa*/
            if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
                $where .= "transactions.id = {$input['context']} OR ";
                $where .= "transactions.referencia_numer_o1 = '{$input['context']}' AND ";
            } else {
                /*SET DATE RANGE*/
                $fecha = str_replace('/', '-', $input['reservationtime']);
                $desde = date('Y-m-d', strtotime($fecha)) . ' 00:00:00';
                $hasta = date('Y-m-d', strtotime($fecha)) . ' 23:59:59';
                $where .= "transactions.created_at BETWEEN '{$desde}' AND '{$hasta}' AND ";

                $where .= ($input['group_id'] <> 0) ? "branches.group_id = " . $input['group_id'] . " AND " : "";

                /*SET OWNER*/
                if (!$this->user->hasAccess('superuser')) {
                    if (($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11)) {
                        $where .= "transactions.owner_id = " . $this->user->owner_id . " AND ";
                    } else {
                        $where .= ($input['owner_id'] <> 0) ? "transactions.owner_id = " . $input['owner_id'] . " AND " : "";
                    }
                } else {
                    $where .= ($input['owner_id'] <> 0) ? "transactions.owner_id = " . $input['owner_id'] . " AND " : "";
                }

                $where .= ($input['branch_id'] <> 0) ? "points_of_sale.branch_id = " . $input['branch_id'] . " AND " : "";
                $where .= ($input['pos_id'] <> 0) ? "points_of_sale.id = " . $input['pos_id'] . " AND " : "";
                $where .= ($input['status_id'] <> "0") ? "transactions.status =  '{$input['status_id']}' AND " : "";
                $where .= ($input['type'] <> "0") ? "atms.type =  '{$input['type']}' AND " : "";

                if (isset($input['payment_method_id'])) {
                    if ($input['payment_method_id'] == 'QR') {
                        $where .= "p.tipo_pago in ('TC','TD','DC','QR')";
                    } else {
                        $where .= ($input['payment_method_id'] <> '0') ? "p.tipo_pago = '{$input['payment_method_id']}' and " : "";
                    }
                }

                if ($input['service_id'] == -2) {
                    $where .= 'transactions.service_source_id = 1';
                    if ($input['service_request_id'] <> 0) {
                        $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                    }
                } elseif ($input['service_id'] == -4) {
                    $where .= 'transactions.service_source_id = 4';
                    if ($input['service_request_id'] <> 0) {
                        $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                    }
                } elseif ($input['service_id'] == -6) {
                    $where .= 'transactions.service_source_id = 6';
                    if ($input['service_request_id'] <> 0) {
                        $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                    }
                } elseif ($input['service_id'] == -7) {
                    $where .= 'transactions.service_source_id = 7';
                    if ($input['service_request_id'] <> 0) {
                        $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                    }
                } elseif ($input['service_id'] == -8) {
                    $where .= 'transactions.service_source_id = 8';
                    if ($input['service_request_id'] <> 0) {
                        $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                    }
                } elseif ($input['service_id'] == -10) {
                    $where .= 'transactions.service_source_id = 10';
                    if ($input['service_request_id'] <> 0) {
                        $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                    }
                } elseif ($input['service_id'] == -11) {
                    $where .= 'transactions.service_source_id = 11';
                    $where .= ' AND transactions.service_id = 100';
                } elseif ($this->user->hasAccess('ticketea') && !$this->user->hasAccess('superuser')) {
                    $where .= 'transactions.service_id = 28';
                } else {
                    $where .= ($input['service_id'] <> 0) ? "transactions.service_id = " . $input['service_id'] . " AND service_source_id = 0" : "";
                }
            }

            $where = trim($where);
            $where = trim($where, 'AND');
            $where = trim($where);

            $transactions = \DB::table('transactions')
                ->select(\DB::raw('transactions.id,transactions.amount, transactions.service_id, transactions.atm_transaction_id,transactions.service_source_id,transactions.identificador_transaction_id,transaction_tickets.id as ticket_id,transaction_tickets.reprinted,transactions.factura_numero,service_providers.name as provider,service_provider_products.description as servicio,transactions.created_at, transactions.status, transactions.status_description, payments.valor_a_pagar,payments.tipo_pago as forma_pago, payments.valor_recibido,valor_entregado,points_of_sale.description as sede,referencia_numero_1,referencia_numero_2, atms.code as code, payments.id as cod_pago, atms.type'))
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'transactions.atm_id')
                ->join('atms', 'atms.id', '=', 'transactions.atm_id')
                ->leftjoin('service_provider_products', 'service_provider_products.id', '=', 'transactions.service_id')
                ->leftjoin('service_providers', 'service_providers.id', '=', 'service_provider_products.service_provider_id')
                ->leftjoin('transaction_tickets', 'transaction_tickets.transaction_id', '=', 'transactions.id')
                ->leftjoin('transactions_x_payments', 'transactions.id', '=', 'transactions_x_payments.transactions_id')
                ->leftjoin('payments', 'payments.id', '=', 'transactions_x_payments.payments_id')
                ->leftjoin('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->whereRaw("$where")
                ->orderBy('cod_pago', 'desc')
                ->orderBy('transactions.created_at', 'desc')
                ->paginate(20);

            $total_transactions = \DB::table('transactions')
                ->select(\DB::raw('sum(abs(transactions.amount)) as monto'))
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'transactions.atm_id')
                ->join('atms', 'atms.id', '=', 'transactions.atm_id')
                ->leftjoin('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->whereRaw("$where")
                ->get();

            foreach ($transactions as $transaction) {
                if ($transaction->status == 'success') {
                    $transaction->status =  '<span class="label label-success">' . $transaction->status . '</span>';
                } elseif ($transaction->status == 'canceled' || $transaction->status == 'iniciated') {
                    $transaction->status =  '<span class="label label-warning">' . $transaction->status . '</span>';
                } elseif ($transaction->status == 'inconsistency') {
                    $transaction->status =  '<span class="label label-danger">' . 'Inconsistencia' . '</span>';
                } else {
                    $transaction->status =  '<span class="label label-danger">' . $transaction->status . '</span>';
                }

                if ($transaction->service_source_id <> 0) {
                    $serv_provider = \DB::table('services_providers_sources')->where('id', $transaction->service_source_id)->first();
                    $transaction->provider = $serv_provider->description;
                    $service_data = \DB::table('services_ondanet_pairing')->where('service_request_id', $transaction->service_id)->where('service_source_id', $transaction->service_source_id)->first();
                    $transaction->servicio = isset($service_data->service_description) ? $service_data->service_description : '';
                }
            }

            /*Carga datos del formulario*/
            $whereGroup = "";
            $whereOwner = "";
            $whereBranch = "";
            $wherePos = "";
            if (!$this->user->hasAccess('superuser')) {
                if ($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11) {
                    $whereOwner = "owners.id = " . $this->user->owner_id;
                    $whereBranch = "branches.owner_id = " . $this->user->owner_id;
                    $wherePos = "points_of_sale.owner_id = " . $this->user->owner_id;
                }
            }
            //Redes
            $groups     = Group::orderBy('business_groups.description')->where(function ($query) use ($whereGroup) {
                if (!empty($whereGroup)) {
                    $query->whereRaw($whereGroup);
                }
            })->get()->pluck('description', 'id');
            $groups->prepend('Todos', '0');

            $owners     = Owner::orderBy('owners.name')->where(function ($query) use ($whereOwner) {
                if (!empty($whereOwner)) {
                    $query->whereRaw($whereOwner);
                }
            })->get()->pluck('name', 'id');
            $owners->prepend('Todos', '0');
            $branches   = Branch::orderBy('description')->where(function ($query) use ($whereBranch) {
                if (!empty($whereBranch)) {
                    $query->whereRaw($whereBranch);
                }
            })->get()->pluck('description', 'id');
            $branches->prepend('Todos', '0');
            $pdvs       = Pos::orderBy('description')->where(function ($query) use ($wherePos) {
                if (!empty($wherePos)) {
                    $query->whereRaw($wherePos);
                }
            })->with('Atm')->get();
            $pos = [];
            $item = array();
            $item[0] = 'Todos';
            foreach ($pdvs  as $pdv) {
                $item[$pdv->id] = $pdv->description . ' - ' . $pdv->Atm->code;
                $pos = $item;
            }

            $status = array('0' => 'Todos', 'success' => 'Aprobado', 'canceled' => 'Cancelado', 'error' => 'Error', 'rollback' => 'Reversado', 'iniciated' => 'Iniciado', 'error dispositivo' => 'Error de dispositivo', 'inconsistency' => 'Inconsistencia');
            $atmType = array('0' => 'Todos', 'da' => 'App Billetaje', 'at' => 'Atm', 'ws' => 'Web Service'); // 12/01/2021

            $payment_methods = array('0' => 'Todos', 'efectivo' => 'Efectivo', 'canje' => 'Canje', 'QR' => 'Todos QR', 'TC' => 'Tarjeta de crédito', 'TD' => 'Tarjeta de débito');

            $services   = ServiceProviderProduct::with('WebServiceProvider')->orderBy('service_provider_id', 'DESC')->get();
            if ($this->user->hasAccess('ticketea') && !$this->user->hasAccess('superuser')) {
                $service_item[28] = 'Ticketea - Venta de tickets';
                $services_data = $service_item;
            } else {
                $service_item[0] = 'Todos';
                $service_item[-2] = 'Netel';
                $service_item[-4] = 'Pronet';
                $service_item[-6] = 'Practipago';
                $service_item[-7] = 'Infonet';
                $service_item[-8] = 'Toval';
                $service_item[-10] = 'Netel Trex';
                $service_item[-11] = 'Bancard ventas QR';
                foreach ($services  as $service) {
                    if ($service->id <> 100) {
                        if ($service->id <> 27 && $service->id <> -1 && $service->WebServiceProvider->name <> 'Toval - Integraciones' && $service->WebServiceProvider->name <> 'Momo - Ken') {
                            $service_item[$service->id] = $service->WebServiceProvider->name . ' - ' . $service->description;
                            $services_data = $service_item;
                        }
                    }
                }
            }

            $resultset = array(
                'target'        => 'Transacciones del Dia',
                'groups'        => $groups,
                'payment_methods' => (isset($input['payment_method_id']) ? $input['payment_method_id'] : 0),
                'payment_methods_set' => 0,
                'owners'        => $owners,
                'branches'      => $branches,
                'pos'           => $pos,
                'status'        => $status,
                'type'          => $atmType,
                'services_data' => $services_data,
                'transactions'  => $transactions,
                'total_transactions'  => $total_transactions,
                'group_id'      => (isset($input['group_id']) ? $input['group_id'] : 0),
                'owner_id'      => (isset($input['owner_id']) ? $input['owner_id'] : 0),
                'branch_id'     => (isset($input['branch_id']) ? $input['branch_id'] : 0),
                'pos_id'        => (isset($input['pos_id']) ? $input['pos_id'] : 0),
                'status_set'    => (isset($input['status_id']) ? $input['status_id'] : 0),
                'type_set'    => (isset($input['type']) ? $input['type'] : 0),
                'service_id'    => (isset($input['service_id']) ? $input['service_id'] : 0),
                'reservationtime' => (isset($input['reservationtime']) ? $input['reservationtime'] : 0),
                'i'             =>  1,
                'service_request_id' => (isset($input['service_request_id']) ? $input['service_request_id'] : 0),
            );

            return $resultset;
        } catch (\Exception $e) {
            //\Log::info($e);

            $error_detail = [
                'message' => 'Ocurrió un error con el servicio de consultas.',
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];

            \Log::error("\nError en " . __FUNCTION__ . ":\nDetalles: " . json_encode($error_detail) . "\n");


            return false;
        }
    }


    public function oneDayTransactionsSearchExport()
    {
        try {
            $input = $this->input;
            $where = "transactions.transaction_type in (1,7,11,12,13) AND ";
            /*Busqueda minusiosa*/
            if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
                $where .= "transactions.request_data like '%{$input['context']}%'";
            } else {
                /*SET DATE RANGE*/
                $fecha = str_replace('/', '-', $input['reservationtime']);
                $desde = date('Y-m-d', strtotime($fecha)) . ' 00:00:00';
                $hasta = date('Y-m-d', strtotime($fecha)) . ' 23:59:59';
                $where .= "transactions.created_at BETWEEN '{$desde}' AND '{$hasta}' AND ";

                /*SET OWNER*/
                if (!$this->user->hasAccess('superuser')) {
                    if (($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11)) {
                        $where .= "transactions.owner_id = " . $this->user->owner_id . " AND ";
                    } else {
                        $where .= ($input['owner_id'] <> 0) ? "transactions.owner_id = " . $input['owner_id'] . " AND " : "";
                    }
                } else {
                    $where .= ($input['owner_id'] <> 0) ? "transactions.owner_id = " . $input['owner_id'] . " AND " : "";
                }

                if (isset($input['payment_method_id'])) {
                    if ($input['payment_method_id'] == 'QR') {
                        $where .= "p.tipo_pago in ('TC','TD','DC','QR')";
                    } else {
                        $where .= ($input['payment_method_id'] <> '0') ? "p.tipo_pago = '{$input['payment_method_id']}' and " : "";
                    }
                }

                $where .= ($input['branch_id'] <> 0) ? "points_of_sale.branch_id = " . $input['branch_id'] . " AND " : "";
                $where .= ($input['pos_id'] <> 0) ? "points_of_sale.id = " . $input['pos_id'] . " AND " : "";
                $where .= ($input['status_id'] <> "0") ? "transactions.status =  '{$input['status_id']}' AND " : "";
                if ($input['service_id'] == -2) {
                    $where .= 'transactions.service_source_id = 1';
                    if ($input['service_request_id'] <> 0) {
                        $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                    }
                } elseif ($input['service_id'] == -4) {
                    $where .= 'transactions.service_source_id = 4';
                    if ($input['service_request_id'] <> 0) {
                        $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                    }
                } elseif ($input['service_id'] == -6) {
                    $where .= 'transactions.service_source_id = 6';
                    if ($input['service_request_id'] <> 0) {
                        $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                    }
                } elseif ($input['service_id'] == -7) {
                    $where .= 'transactions.service_source_id = 7';
                    if ($input['service_request_id'] <> 0) {
                        $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                    }
                } elseif ($input['service_id'] == -8) {
                    $where .= 'transactions.service_source_id = 8';
                    if ($input['service_request_id'] <> 0) {
                        $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                    }
                } elseif ($input['service_id'] == -10) {
                    $where .= 'transactions.service_source_id = 10';
                    if ($input['service_request_id'] <> 0) {
                        $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                    }
                } elseif ($this->user->hasAccess('ticketea') && !$this->user->hasAccess('superuser')) {
                    $where .= 'transactions.service_id = 28';
                } else {
                    $where .= ($input['service_id'] <> 0) ? "transactions.service_id = " . $input['service_id'] . " AND service_source_id = 0" : "";
                }
            }

            $where = trim($where);
            $where = trim($where, 'AND');
            $where = trim($where);

            $transactions = \DB::connection('eglobalt_replica')->table('transactions')
                ->select(\DB::raw('transactions.id,transactions.service_id, service_providers.name as proveedor, service_provider_products.description as tipo, transactions.status as estado, transactions.status_description as estado_descripcion, transactions.created_at as fecha,transactions.created_at as hora, transactions.amount as valor_transaccion, payments.id as cod_pago,payments.tipo_pago as forma_pago, transactions.identificador_transaction_id as identificador_transaccion, transactions.factura_numero as factura_nro, points_of_sale.description as sede,transactions.owner_id, referencia_numero_1 as ref1, referencia_numero_2 as ref2, atms.code as codigo_cajero, transactions.service_source_id'))
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'transactions.atm_id')
                ->join('atms', 'atms.id', '=', 'transactions.atm_id')
                ->leftjoin('service_provider_products', 'service_provider_products.id', '=', 'transactions.service_id')
                ->leftjoin('service_providers', 'service_providers.id', '=', 'service_provider_products.service_provider_id')
                ->leftjoin('transactions_x_payments', 'transactions.id', '=', 'transactions_x_payments.transactions_id')
                ->leftjoin('payments', 'payments.id', '=', 'transactions_x_payments.payments_id')
                ->whereRaw("$where")
                ->orderBy('cod_pago', 'desc')
                ->orderBy('transactions.created_at', 'desc')
                ->get();


            foreach ($transactions as $transaction) {

                if ($transaction->service_source_id <> 0) {
                    $serv_provider = \DB::table('services_providers_sources')->where('id', $transaction->service_source_id)->first();
                    $transaction->proveedor = $serv_provider->description;
                    $service_data = \DB::table('services_ondanet_pairing')->where('service_request_id', $transaction->service_id)->where('service_source_id', $transaction->service_source_id)->first();
                    //$transaction->proveedor .= isset($service_data->service_description)?$service_data->service_description:'';
                    $transaction->tipo  = isset($service_data->service_description) ? $service_data->service_description : '';
                }
                if ($transaction->valor_transaccion < 0) {
                    //Monto transaccion de efectifizacion es negativo, para el reporte exportado se convierte a positivo
                    $transaction->valor_transaccion = $transaction->valor_transaccion * -1;
                }

                if ($transaction->owner_id == 2) {
                    $transaction->owner_id = 'Antell';
                }
                if ($transaction->owner_id == 11) {
                    $transaction->owner_id = 'Eglobal';
                }

                $transaction->fecha = date('d-m-Y', strtotime($transaction->fecha));
                $transaction->hora = date('H:i:s', strtotime($transaction->hora));

                $transaction->valor_transaccion = number_format($transaction->valor_transaccion, 0, '', '');

                unset($transaction->service_source_id);
                unset($transaction->service_id);
            }
            return $transactions;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    public function oneDaytransactionsSearchExportMovements()
    {
        try {
            $input = $this->input;
            $where = "transactions.transaction_type in (1,7,11,12,13) AND ";
            /*Busqueda minusiosa*/
            if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
                $where .= "transactions.request_data like '%{$input['context']}%'";
            } else {
                /*SET DATE RANGE*/
                $fecha = str_replace('/', '-', $input['reservationtime']);
                $desde = date('Y-m-d', strtotime($fecha)) . ' 00:00:00';
                $hasta = date('Y-m-d', strtotime($fecha)) . ' 23:59:59';
                $where .= "transactions.created_at BETWEEN '{$desde}' AND '{$hasta}' AND ";

                /*SET OWNER*/
                if (!$this->user->hasAccess('superuser')) {
                    if (($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11)) {
                        $where .= "transactions.owner_id = " . $this->user->owner_id . " AND ";
                    } else {
                        $where .= ($input['owner_id'] <> 0) ? "transactions.owner_id = " . $input['owner_id'] . " AND " : "";
                    }
                } else {
                    $where .= ($input['owner_id'] <> 0) ? "transactions.owner_id = " . $input['owner_id'] . " AND " : "";
                }

                $where .= ($input['branch_id'] <> 0) ? "points_of_sale.branch_id = " . $input['branch_id'] . " AND " : "";
                $where .= ($input['pos_id'] <> 0) ? "points_of_sale.id = " . $input['pos_id'] . " AND " : "";
                $where .= ($input['status_id'] <> "0") ? "transactions.status =  '{$input['status_id']}' AND " : "";

                if ($input['service_id'] == -2) {
                    $where .= 'transactions.service_source_id = 1';
                    $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                } elseif ($input['service_id'] == -4) {
                    $where .= 'transactions.service_source_id = 4';
                    $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                } elseif ($input['service_id'] == -6) {
                    $where .= 'transactions.service_source_id = 6';
                    $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                } elseif ($input['service_id'] == -7) {
                    $where .= 'transactions.service_source_id = 7';
                    $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                } elseif ($input['service_id'] == -8) {
                    $where .= 'transactions.service_source_id = 8';
                    $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                } elseif ($input['service_id'] == -10) {
                    $where .= 'transactions.service_source_id = 10';
                    $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                } elseif ($this->user->hasAccess('ticketea') && !$this->user->hasAccess('superuser')) {
                    $where .= 'transactions.service_id = 28';
                } else {
                    $where .= ($input['service_id'] <> 0) ? "transactions.service_id = " . $input['service_id'] . " AND service_source_id = 0" : "";
                }
            }

            $where = trim($where);
            $where = trim($where, 'AND');
            $where = trim($where);

            $transactions = \DB::connection('eglobalt_replica')->table('transactions_movements')
                ->select(\DB::raw('transactions_movements.id,transactions_movements.transactions_id as transaction_id, transactions_movements.atms_parts_id as atm, transactions_movements.accion, transactions_movements.cantidad, transactions_movements.valor, transactions_movements.dinero_virtual, transactions_movements.payments_id, transactions.created_at as fecha, transactions.created_at as hora '))
                ->leftjoin('transactions', 'transactions.id', '=', 'transactions_movements.transactions_id')
                ->leftjoin('atms_parts', 'atms_parts.id', '=', 'transactions_movements.atms_parts_id')
                ->leftjoin('payments', 'payments.id', '=', 'transactions_movements.payments_id')
                ->whereRaw("$where")
                ->orderBy('fecha', 'desc')
                ->get();

            foreach ($transactions as $transaction) {

                $transaction->fecha = date('d-m-Y', strtotime($transaction->fecha));
                $transaction->hora = date('H:i:s', strtotime($transaction->hora));
            };

            return $transactions;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    /***Resumen de las transacciones por atm*/
    public function resumenTransacciones()
    {
        try {
            //Redes
            $whereOwner = "";
            $whereBranch = "";
            $wherePos = "";
            if (!$this->user->hasAccess('superuser')) {
                if ($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11) {
                    $whereOwner = "owners.id = " . $this->user->owner_id;
                    $whereBranch = "branches.owner_id = " . $this->user->owner_id;
                    $wherePos = "points_of_sale.owner_id = " . $this->user->owner_id;
                }
            }
            //Redes
            $owners     = Owner::orderBy('owners.name')->where(function ($query) use ($whereOwner) {
                if (!empty($whereOwner)) {
                    $query->whereRaw($whereOwner);
                }
            })->get()->pluck('name', 'id');
            $owners->prepend('Todos', '0');
            $branches   = Branch::orderBy('description')->where(function ($query) use ($whereBranch) {
                if (!empty($whereBranch)) {
                    $query->whereRaw($whereBranch);
                }
            })->get()->pluck('description', 'id');
            $branches->prepend('Todos', '0');
            $pdvs       = Pos::orderBy('description')->where(function ($query) use ($wherePos) {
                if (!empty($wherePos)) {
                    $query->whereRaw($wherePos);
                }
            })->with('Atm')->get();
            $pos = [];
            $item = array();
            $item[0] = 'Todos';

            $atms = Atm::orderBy('name')->get()->pluck("name", 'id');

            foreach ($pdvs  as $pdv) {
                $item[$pdv->id] = $pdv->description . ' - ' . $pdv->Atm->code;
                $pos = $item;
            }
            $atmType = array('0' => 'Todos', 'da' => 'App Billetaje', 'ws' => 'Web Service', 'at' => 'Atm');
            $status = array('0' => 'Todos', 'success' => 'Aprobado', 'canceled' => 'Cancelado', 'error' => 'Error', 'rollback' => 'Reversado', 'iniciated' => 'Iniciado', 'error dispositivo' => 'Error de dispositivo', 'error dispositivo' => 'Error de dispositivo', 'inconsistency' => 'Inconsistencia');
            $services   = ServiceProviderProduct::with('WebServiceProvider')->orderBy('service_provider_id', 'DESC')->get();

            $services_data = [];
            $service_item = array();

            if ($this->user->hasAccess('ticketea') && !$this->user->hasAccess('superuser')) {
                $service_item[28] = 'Ticketea - Venta de tickets';
                $services_data = $service_item;
            } else {
                $service_item[0] = 'Todos';
                $service_item[-2] = 'Netel';
                $service_item[-4] = 'Pronet';
                $service_item[-6] = 'Practipago';
                $service_item[-7] = 'Infonet';
                $service_item[-8] = 'Toval';
                $service_item[-10] = 'Netel Trex';
                $service_item[-11] = 'Bancard Ventas QR';
                foreach ($services  as $service) {
                    if ($service->id <> 100) {
                        if ($service->id <> 27 && $service->id <> -1 && $service->WebServiceProvider->name <> 'Toval - Integraciones' && $service->WebServiceProvider->name <> 'Momo - Ken') {
                            $service_item[$service->id] = $service->WebServiceProvider->name . ' - ' . $service->description;
                            $services_data = $service_item;
                        }
                    }
                }
            }

            $resultset = array(
                'target'        => 'Resumen',
                'owners'        => $owners,
                'branches'      => $branches,
                'pos'           => $pos,
                'status'        => $status,
                'services_data' => $services_data,
                'atms'          => $atms,
                'reservationtime' => '',
                'owner_id'      => 0,
                'branch_id'     => 0,
                'pos_id'        => 0,
                'status_set'    => 0,
                'service_id'    => 0,
                'atm_id'        => 0,
                'service_request_id'    => '',
                'type'          => $atmType,
                'type_set'    => 0,

            );

            return $resultset;
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de reportes" . $e);
            return false;
        }
    }

    public function resumenSearch()
    {
        try {
            $input = $this->input;
            $where = "transactions.transaction_type in (1,7,12,13) AND ";
            /*SET DATE RANGE*/
            $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
            $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
            $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
            $where .= "transactions.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' AND ";
            $fecha = "transactions.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' ";
            if ($input['atm_id'] <> '288') {
                $where .= "transactions.atm_id = " . $input['atm_id'] . " AND transactions.status = 'success'";
            } else {
                $where .= "transactions.status = 'success'";
            }
            /*Busqueda minusiosa*/
            if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
                $where .= "transactions.id = {$input['context']} OR ";
                $where .= "transactions.referencia_numero_1 = '{$input['context']}' AND ";
            }

            if (isset($input['service_source_id'])) {
                $whereSource = "transactions.service_source_id in (" . $input['service_source_id'] . ")";
            } else if (isset($input['service_id'])) {
                $whereSource = "transactions.service_source_id in (0) and service_id = " . $input['service_id'];
            } else {
                $whereSource = "transactions.service_source_id in (0)";
            }

            if (isset($input['type'])) {
                $where .= ($input['type'] <> "0") ? " AND atms.type =  '{$input['type']}' " : "";
            }

            if (!$this->user->hasAccess('superuser')) {
                if (($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11)) {
                    $where .= "transactions.owner_id = " . $this->user->owner_id . " AND ";
                } else {
                    $where .= ($input['owner_id'] <> 0) ? " AND transactions.owner_id = " . $input['owner_id'] . " AND " : "";
                }
            } else {
                $where .= ($input['owner_id'] <> 0) ? " AND transactions.owner_id = " . $input['owner_id'] . " AND " : "";
            }

            $where = trim($where);
            $where = trim($where, 'AND');
            $where = trim($where);

            // $transactionsProviders = \DB::table('transactions')
            //     ->select(\DB::raw('sum(transactions.amount) as amount, transactions.service_source_id'))
            //     ->join('atms', 'atms.id','=','transactions.atm_id')
            //     ->whereRaw("$where")
            //     ->whereRaw("transactions.service_source_id in (1,4,6,7,8)")
            //     ->groupBy('transactions.service_source_id')
            //     ->paginate(20);                
            $transactionsProvidersMomo = \DB::table('transactions')
                ->select(\DB::raw('sum(transactions.amount) as amount, transactions.service_source_id'))
                ->join('atms', 'atms.id', '=', 'transactions.atm_id')
                ->whereRaw("$where")
                ->whereRaw("transactions.service_source_id = 0")
                ->whereRaw("transactions.service_id in (3,5,7,8,9,12,20,33,44,51)")
                ->groupBy('transactions.service_source_id');
            $transactionsProviders = \DB::table('transactions')
                ->select(\DB::raw('sum(transactions.amount) as amount, transactions.service_source_id'))
                ->join('atms', 'atms.id', '=', 'transactions.atm_id')
                ->whereRaw("$where")
                ->whereRaw("transactions.service_source_id in (1,4,6,7,8)")
                ->groupBy('transactions.service_source_id')
                ->union($transactionsProvidersMomo)
                ->get();

            if (isset($input['service_source_id'])) {
                if ($input['service_source_id'] == 0) {
                    $transactionsEglobalt = \DB::table('transactions')
                        ->select(\DB::raw('sum(transactions.amount) as amount, transactions.service_source_id, transactions.service_id'))
                        ->join('atms', 'atms.id', '=', 'transactions.atm_id')
                        ->whereRaw("$where")
                        ->whereRaw("$whereSource")
                        ->whereRaw("transactions.service_id in (3,5,7,8,9,12,20,33,44,51)")
                        ->groupBy('transactions.service_source_id')
                        ->groupBy('transactions.service_id')
                        ->get();
                } else {
                    $transactionsEglobalt = \DB::table('transactions')
                        ->select(\DB::raw('sum(transactions.amount) as amount, transactions.service_source_id, transactions.service_id'))
                        ->join('atms', 'atms.id', '=', 'transactions.atm_id')
                        ->whereRaw("$where")
                        ->whereRaw("$whereSource")
                        ->groupBy('transactions.service_source_id')
                        ->groupBy('transactions.service_id')
                        ->get();
                }
            } else if (isset($input['service_id'])) {
                $transactionsEglobalt = \DB::table('transactions')
                    ->select(\DB::raw('transactions.amount as amount, transactions.service_source_id, transactions.service_id, transactions.created_at'))
                    ->join('atms', 'atms.id', '=', 'transactions.atm_id')
                    ->whereRaw("$where")
                    ->whereRaw("$whereSource")
                    ->get();
            } else {
                $transactionsEglobalt = \DB::table('transactions')
                    ->select(\DB::raw('sum(transactions.amount) as amount, transactions.service_source_id, transactions.service_id'))
                    ->join('atms', 'atms.id', '=', 'transactions.atm_id')
                    ->whereRaw("$where")
                    ->whereRaw("$whereSource")
                    ->groupBy('transactions.service_source_id')
                    ->groupBy('transactions.service_id')
                    ->paginate(20);
            }

            $dataRed = '';
            $total = 0;
            foreach ($transactionsEglobalt as $transaction) {

                if (isset($input['service_source_id']) && $input['service_source_id'] <> 8) {
                    //Para listar productos de Momo-Antell
                    if (
                        $transaction->service_source_id == 0 && $transaction->service_id  == 3 || $transaction->service_id  == 5 || $transaction->service_id  == 7
                        || $transaction->service_id  == 8 || $transaction->service_id  == 9 || $transaction->service_id  == 12 || $transaction->service_id  == 20
                        || $transaction->service_id  == 33 || $transaction->service_id  == 44 || $transaction->service_id  == 51
                    ) {

                        $serv_provider = \DB::table('service_provider_products')
                            ->join('service_providers', 'service_providers.id', '=', 'service_provider_products.service_provider_id')
                            ->where('service_provider_products.id', $transaction->service_id)
                            ->first();
                        $transaction->nombre_servicio = isset($serv_provider->description) ? $serv_provider->description : '';
                        $total += $transaction->amount;
                        $dataRed .= '<tr>';
                        $dataRed .= '<td>' . $transaction->nombre_servicio . '</td>';
                        $dataRed .= '<td align="right">' . number_format($transaction->amount, 0) . '</td>';
                        $dataRed .= '</tr>';
                    } else {
                        $serv_provider = \DB::table('services_ondanet_pairing')
                            ->where('services_ondanet_pairing.service_request_id', $transaction->service_id)
                            ->where('services_ondanet_pairing.service_source_id', $transaction->service_source_id)
                            ->first();

                        $transaction->nombre_servicio = isset($serv_provider->service_description) ? $serv_provider->service_description : '';
                        $total += $transaction->amount;
                        $dataRed .= '<tr>';
                        $dataRed .= '<td>' . $transaction->nombre_servicio . '</td>';
                        $dataRed .= '<td align="right">' . number_format($transaction->amount, 0) . '</td>';
                        $dataRed .= '</tr>';
                    }
                } else {
                    $service_id = $transaction->service_id;
                    if ($service_id == 14 && $transaction->service_source_id == 8) {
                        $service_id = 66;
                    } elseif ($service_id == 3 && $transaction->service_source_id == 8) {
                        $service_id = 59;
                    } elseif ($service_id == 4 && $transaction->service_source_id == 8) {
                        $service_id = 57;
                    } elseif ($service_id == 5 && $transaction->service_source_id == 8) {
                        $service_id = 58;
                    } elseif ($service_id == 15 && $transaction->service_source_id == 8) {
                        $service_id = 67;
                    }
                    $serv_provider = \DB::table('service_provider_products')
                        ->join('service_providers', 'service_providers.id', '=', 'service_provider_products.service_provider_id')
                        ->where('service_provider_products.id', $service_id)
                        ->first();

                    //$transaction->fecha = Carbon::parse($transaction->created_at)->format('d/m/Y H:i:s');
                    $transaction->proveedor = isset($serv_provider->name) ? $serv_provider->name : '';
                    $transaction->nombre_servicio = isset($serv_provider->description) ? $serv_provider->description : '';
                    $total += $transaction->amount;
                    $dataRed .= '<tr>';
                    $dataRed .= '<td>' . $transaction->nombre_servicio . '</td>';
                    $dataRed .= '<td align="right">' . number_format($transaction->amount, 0) . '</td>';
                    $dataRed .= '</tr>';
                }
            }

            if (isset($input['service_source_id'])) {
                $dataFooter = '<tr>';
                $dataFooter .= '<th>Total</th>';
                $dataFooter .= '<th class="text-right">' . number_format($total, 0) . '</th>';
                $dataFooter .= '</tr>';

                $data = [
                    'modal_contenido' => $dataRed,
                    'modal_footer' => $dataFooter,
                ];

                return $data;
            } else if (isset($input['service_id'])) {

                $dataFooter = '<tr>';
                $dataFooter .= '<th>Total</th>';
                $dataFooter .= '<th class="text-right">' . number_format($total, 0) . '</th>';
                $dataFooter .= '</tr>';

                $data = [
                    'modal_contenido' => $dataRed,
                    'modal_footer' => $dataFooter,
                ];

                return $data;
            }

            foreach ($transactionsProviders as $key => $transaction) {

                $services_providers_sources = \DB::table('services_providers_sources')
                    ->where('services_providers_sources.id', $transaction->service_source_id)
                    ->first();
                $transaction->red = isset($services_providers_sources->description) ? $services_providers_sources->description : '';
            }
            /*Carga datos del formulario*/
            $whereOwner = "";
            if (!$this->user->hasAccess('superuser')) {
                if ($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11) {
                    $whereOwner = "owners.id = " . $this->user->owner_id;
                    //$whereBranch = "branches.owner_id = ".$this->user->owner_id;                    
                    //$wherePos = "points_of_sale.owner_id = ".$this->user->owner_id;                    
                }
            }
            //Redes
            $owners     = Owner::orderBy('owners.name')->where(function ($query) use ($whereOwner) {
                if (!empty($whereOwner)) {
                    $query->whereRaw($whereOwner);
                }
            })->get()->pluck('name', 'id');
            $owners->prepend('Todos', '0');

            $atms = Atm::orderBy('name')->get()->pluck("name", 'id');
            $atmType = array('0' => 'Todos', 'da' => 'App Billetaje', 'ws' => 'Web Service', 'at' => 'Atm');


            $resultset = array(
                'target'                    => 'Resumen',
                'transactionsProviders'     => $transactionsProviders,
                'transactionsEglobalt'      => $transactionsEglobalt,
                'reservationtime'           => (isset($input['reservationtime']) ? $input['reservationtime'] : 0),
                'i'                         =>  1,
                'atms'                      => $atms,
                'atm_id'                    => (isset($input['atm_id']) ? $input['atm_id'] : 0),
                'type'                      => $atmType,
                'type_set'                  => (isset($input['type']) ? $input['type'] : 0),
                'owners'                    => $owners,
                'owner_id'                  => (isset($input['owner_id']) ? $input['owner_id'] : 0)
            );


            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    public function resumenSearchExport()
    {
        try {
            $input = $this->input;
            $where = "transactions.transaction_type in (1,7,12,13) AND ";
            /*SET DATE RANGE*/
            $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
            $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
            $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
            $where .= "transactions.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' AND ";
            $fecha = "transactions.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' ";

            //$where .= "transactions.atm_id = ". $input['atm_id']." AND transactions.status = 'success'";

            if ($input['atm_id'] <> '288') {
                $where .= "transactions.atm_id = " . $input['atm_id'] . " AND transactions.status = 'success'";
            } else {
                $where .= "transactions.status = 'success'";
            }
            /*Busqueda minusiosa*/
            if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
                $where .= "transactions.id = {$input['context']} OR ";
                $where .= "transactions.referencia_numero_1 = '{$input['context']}' AND ";
            }

            // if(isset($input['service_source_id'])){
            //     $whereSource = 'transactions.service_source_id in ('.$input['service_source_id'].')';
            // }else{
            //     $whereSource = 'transactions.service_source_id in (0)';
            // }
            if (isset($input['service_source_id'])) {
                $whereSource = "transactions.service_source_id in (" . $input['service_source_id'] . ")";
            } else if (isset($input['service_id'])) {
                $whereSource = "transactions.service_source_id in (0) and service_id = " . $input['service_id'];
            } else {
                $whereSource = "transactions.service_source_id in (0)";
            }

            if (isset($input['type'])) {
                $where .= ($input['type'] <> "0") ? " AND atms.type =  '{$input['type']}' " : "";
            }

            if (!$this->user->hasAccess('superuser')) {
                if (($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11)) {
                    $where .= "transactions.owner_id = " . $this->user->owner_id . " AND ";
                } else {
                    $where .= ($input['owner_id'] <> 0) ? " AND transactions.owner_id = " . $input['owner_id'] . " AND " : "";
                }
            } else {
                $where .= ($input['owner_id'] <> 0) ? " AND transactions.owner_id = " . $input['owner_id'] . " AND " : "";
            }

            $where = trim($where);
            $where = trim($where, 'AND');
            $where = trim($where);

            $transactionsProvidersMomo = \DB::table('transactions')
                ->select(\DB::raw('sum(transactions.amount) as amount, transactions.service_source_id'))
                ->join('atms', 'atms.id', '=', 'transactions.atm_id')
                ->whereRaw("$where")
                ->whereRaw("transactions.service_source_id = 0")
                ->whereRaw("transactions.service_id in (3,5,7,8,9,12,20,33,44,51)")
                ->groupBy('transactions.service_source_id');
            $transactionsProviders = \DB::table('transactions')
                ->select(\DB::raw('sum(transactions.amount) as amount, transactions.service_source_id'))
                ->join('atms', 'atms.id', '=', 'transactions.atm_id')
                ->whereRaw("$where")
                ->whereRaw("transactions.service_source_id in (1,4,6,7,8)")
                ->groupBy('transactions.service_source_id')
                ->union($transactionsProvidersMomo)
                ->get();



            if (isset($input['service_source_id'])) {
                if ($input['service_source_id'] == 0) {
                    $transactionsEglobalt = \DB::table('transactions')
                        ->select(\DB::raw('sum(transactions.amount) as amount, transactions.service_source_id, transactions.service_id'))
                        ->join('atms', 'atms.id', '=', 'transactions.atm_id')
                        ->whereRaw("$where")
                        ->whereRaw("$whereSource")
                        ->whereRaw("transactions.service_id in (3,5,7,8,9,12,20,33,44,51)")
                        ->groupBy('transactions.service_source_id')
                        ->groupBy('transactions.service_id')
                        ->get();
                } else {
                    $transactionsEglobalt = \DB::table('transactions')
                        ->select(\DB::raw('sum(transactions.amount) as amount, transactions.service_source_id, transactions.service_id'))
                        ->join('atms', 'atms.id', '=', 'transactions.atm_id')
                        ->whereRaw("$where")
                        ->whereRaw("$whereSource")
                        ->groupBy('transactions.service_source_id')
                        ->groupBy('transactions.service_id')
                        ->get();
                }
            } else if (isset($input['service_id'])) {
                $transactionsEglobalt = \DB::table('transactions')
                    ->select(\DB::raw('transactions.amount as amount, transactions.service_source_id, transactions.service_id, transactions.created_at'))
                    ->join('atms', 'atms.id', '=', 'transactions.atm_id')
                    ->whereRaw("$where")
                    ->whereRaw("$whereSource")
                    ->get();
            } else {
                $transactionsEglobalt = \DB::table('transactions')
                    ->select(\DB::raw('sum(transactions.amount) as amount, transactions.service_source_id, transactions.service_id'))
                    ->join('atms', 'atms.id', '=', 'transactions.atm_id')
                    ->whereRaw("$where")
                    ->whereRaw("$whereSource")
                    ->groupBy('transactions.service_source_id')
                    ->groupBy('transactions.service_id')
                    ->get();
            }


            $dataRed = '';
            $total = 0;
            foreach ($transactionsEglobalt as $transaction) {

                // if(isset($input['service_source_id'])){
                //     $serv_provider = \DB::table('services_ondanet_pairing')
                //         ->where('services_ondanet_pairing.service_request_id', $transaction->service_id)
                //         ->first();

                //     $transaction->nombre_servicio = isset($serv_provider->service_description)?$serv_provider->service_description:'';

                //     $total += $transaction->amount;
                //     $dataRed .= '<tr>';
                //         $dataRed .= '<td>'.$transaction->nombre_servicio.'</td>';
                //         $dataRed .= '<td align="right">'.number_format($transaction->amount,0).'</td>';
                //     $dataRed .= '</tr>';
                // }else{
                //     $serv_provider = \DB::table('service_provider_products')
                //         ->join('service_providers', 'service_providers.id','=','service_provider_products.service_provider_id')
                //         ->where('service_provider_products.id', $transaction->service_id)
                //         ->first();

                //     $transaction->proveedor = isset($serv_provider->name)?$serv_provider->name:'';
                //     $transaction->nombre_servicio = isset($serv_provider->description)?$serv_provider->description:'';
                // }

                if (isset($input['service_source_id']) && $input['service_source_id'] <> 8) {
                    //Para listar productos de Momo-Antell
                    if (
                        $transaction->service_source_id == 0 && $transaction->service_id  == 3 || $transaction->service_id  == 5 || $transaction->service_id  == 7
                        || $transaction->service_id  == 8 || $transaction->service_id  == 9 || $transaction->service_id  == 12 || $transaction->service_id  == 20
                        || $transaction->service_id  == 33 || $transaction->service_id  == 44 || $transaction->service_id  == 51
                    ) {

                        $serv_provider = \DB::table('service_provider_products')
                            ->join('service_providers', 'service_providers.id', '=', 'service_provider_products.service_provider_id')
                            ->where('service_provider_products.id', $transaction->service_id)
                            ->first();
                        $transaction->nombre_servicio = isset($serv_provider->description) ? $serv_provider->description : '';
                        $total += $transaction->amount;
                        $dataRed .= '<tr>';
                        $dataRed .= '<td>' . $transaction->nombre_servicio . '</td>';
                        $dataRed .= '<td align="right">' . number_format($transaction->amount, 0) . '</td>';
                        $dataRed .= '</tr>';
                    } else {
                        $serv_provider = \DB::table('services_ondanet_pairing')
                            ->where('services_ondanet_pairing.service_request_id', $transaction->service_id)
                            ->where('services_ondanet_pairing.service_source_id', $transaction->service_source_id)
                            ->first();

                        $transaction->nombre_servicio = isset($serv_provider->service_description) ? $serv_provider->service_description : '';
                        $total += $transaction->amount;
                        $dataRed .= '<tr>';
                        $dataRed .= '<td>' . $transaction->nombre_servicio . '</td>';
                        $dataRed .= '<td align="right">' . number_format($transaction->amount, 0) . '</td>';
                        $dataRed .= '</tr>';
                    }
                } else {
                    $service_id = $transaction->service_id;
                    if ($service_id == 14 && $transaction->service_source_id == 8) {
                        $service_id = 66;
                    } elseif ($service_id == 3 && $transaction->service_source_id == 8) {
                        $service_id = 59;
                    } elseif ($service_id == 4 && $transaction->service_source_id == 8) {
                        $service_id = 57;
                    } elseif ($service_id == 5 && $transaction->service_source_id == 8) {
                        $service_id = 58;
                    } elseif ($service_id == 15 && $transaction->service_source_id == 8) {
                        $service_id = 67;
                    }
                    $serv_provider = \DB::table('service_provider_products')
                        ->join('service_providers', 'service_providers.id', '=', 'service_provider_products.service_provider_id')
                        ->where('service_provider_products.id', $service_id)
                        ->first();

                    //$transaction->fecha = Carbon::parse($transaction->created_at)->format('d/m/Y H:i:s');
                    $transaction->proveedor = isset($serv_provider->name) ? $serv_provider->name : '';
                    $transaction->nombre_servicio = isset($serv_provider->description) ? $serv_provider->description : '';
                    $total += $transaction->amount;
                    $dataRed .= '<tr>';
                    $dataRed .= '<td>' . $transaction->nombre_servicio . '</td>';
                    $dataRed .= '<td align="right">' . number_format($transaction->amount, 0) . '</td>';
                    $dataRed .= '</tr>';
                }
            }

            // if(isset($input['service_source_id'])){
            //     $dataFooter = '<tr>';
            //         $dataFooter .= '<th>Total</th>';
            //         $dataFooter .= '<th class="text-right">'.number_format($total,0).'</th>';
            //     $dataFooter .= '</tr>';

            //     $data = [
            //         'modal_contenido' => $dataRed,
            //         'modal_footer' => $dataFooter,
            //     ];
            // }

            if (isset($input['service_source_id'])) {
                $dataFooter = '<tr>';
                $dataFooter .= '<th>Total</th>';
                $dataFooter .= '<th class="text-right">' . number_format($total, 0) . '</th>';
                $dataFooter .= '</tr>';

                $data = [
                    'modal_contenido' => $dataRed,
                    'modal_footer' => $dataFooter,
                ];

                return $data;
            } else if (isset($input['service_id'])) {

                $dataFooter = '<tr>';
                $dataFooter .= '<th>Total</th>';
                $dataFooter .= '<th class="text-right">' . number_format($total, 0) . '</th>';
                $dataFooter .= '</tr>';

                $data = [
                    'modal_contenido' => $dataRed,
                    'modal_footer' => $dataFooter,
                ];

                return $data;
            }

            foreach ($transactionsProviders as $key => $transaction) {
                if ($transaction->service_source_id == 0) {
                    $transaction->red = 'Momo';
                    unset($transaction->service_source_id);
                } else {
                    $services_providers_sources = \DB::table('services_providers_sources')
                        ->where('services_providers_sources.id', $transaction->service_source_id)
                        ->first();
                    $transaction->red = isset($services_providers_sources->description) ? $services_providers_sources->description : '';
                    unset($transaction->service_source_id);
                }
            }

            $resultset = array(
                'transactionsProviders'     => $transactionsProviders,
                'transactionsEglobalt'      => $transactionsEglobalt,
            );

            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    public function resumenSearchDetalleExport()
    {
        try {
            $input = $this->input;
            //dd($input);
            $where = "transactions.transaction_type in (1,7,12,13) AND ";
            /*SET DATE RANGE*/
            $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
            $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
            $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
            $where .= "transactions.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' AND ";
            $fecha = "transactions.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' ";
            // $where .= "transactions.atm_id = ". $input['atm_id']." AND transactions.status = 'success'";

            if ($input['atm_id'] <> '288') {
                $where .= "transactions.atm_id = " . $input['atm_id'] . " AND transactions.status = 'success'";
            } else {
                $where .= "transactions.status = 'success'";
            }

            /*Busqueda minusiosa*/
            if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
                $where .= "transactions.id = {$input['context']} OR ";
                $where .= "transactions.referencia_numero_1 = '{$input['context']}' AND ";
            }

            // if(!empty($input['service_source_id'])){
            //     $whereSource = 'transactions.service_source_id in ('.$input['service_source_id'].')';
            // }else if(!empty($input['service_id'])){
            //     $whereSource = 'transactions.service_source_id in (0) and transactions.service_id = '.$input['service_id'];
            // }
            if (isset($input['service_source_id'])) {
                $whereSource = "transactions.service_source_id in (" . $input['service_source_id'] . ")";
            } else if (isset($input['service_id'])) {
                $whereSource = "transactions.service_source_id in (0) and service_id = " . $input['service_id'];
            } else {
                $whereSource = "transactions.service_source_id in (0)";
            }

            if (isset($input['type'])) {
                $where .= ($input['type'] <> "0") ? " AND atms.type =  '{$input['type']}' " : "";
            }
            if (!$this->user->hasAccess('superuser')) {
                if (($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11)) {
                    $where .= "transactions.owner_id = " . $this->user->owner_id . " AND ";
                } else {
                    $where .= ($input['owner_id'] <> 0) ? " AND transactions.owner_id = " . $input['owner_id'] . " AND " : "";
                }
            } else {
                $where .= ($input['owner_id'] <> 0) ? " AND transactions.owner_id = " . $input['owner_id'] . " AND " : "";
            }

            $where = trim($where);
            $where = trim($where, 'AND');
            $where = trim($where);
            if (isset($input['service_source_id'])) {
                // dd($input['service_source_id']);

                if ($input['service_source_id'] == 0) {
                    $transactions = \DB::table('transactions')
                        ->select(\DB::raw('sum(transactions.amount) as amount, transactions.service_source_id, transactions.service_id'))
                        ->join('atms', 'atms.id', '=', 'transactions.atm_id')
                        ->whereRaw("$where")
                        ->whereRaw("$whereSource")
                        ->whereRaw("transactions.service_id in (3,5,7,8,9,12,20,33,44,51)")
                        ->groupBy('transactions.service_source_id')
                        ->groupBy('transactions.service_id')
                        ->get();
                } else {
                    $transactions = \DB::table('transactions')
                        ->select(\DB::raw('sum(transactions.amount) as amount, transactions.service_source_id, transactions.service_id'))
                        ->join('atms', 'atms.id', '=', 'transactions.atm_id')
                        ->whereRaw("$where")
                        ->whereRaw("$whereSource")
                        ->groupBy('transactions.service_source_id')
                        ->groupBy('transactions.service_id')
                        ->get();
                }

                $total = 0;
                foreach ($transactions as $transaction) {
                    //Para listar productos de Momo-Antell
                    if (
                        $transaction->service_source_id == 0 && $transaction->service_id  == 3 || $transaction->service_id  == 5 || $transaction->service_id  == 7
                        || $transaction->service_id  == 8 || $transaction->service_id  == 9 || $transaction->service_id  == 12 || $transaction->service_id  == 20
                        || $transaction->service_id  == 33 || $transaction->service_id  == 44 || $transaction->service_id  == 51
                    ) {

                        $serv_provider = \DB::table('service_provider_products')
                            ->join('service_providers', 'service_providers.id', '=', 'service_provider_products.service_provider_id')
                            ->where('service_provider_products.id', $transaction->service_id)
                            ->first();
                        $transaction->nombre_servicio = isset($serv_provider->description) ? $serv_provider->description : '';

                        $total += $transaction->amount;

                        unset($transaction->service_source_id);
                        unset($transaction->service_id);
                    } else {

                        $serv_provider = \DB::table('services_ondanet_pairing')
                            ->where('services_ondanet_pairing.service_request_id', $transaction->service_id)
                            ->first();

                        $transaction->nombre_servicio = isset($serv_provider->service_description) ? $serv_provider->service_description : '';

                        unset($transaction->service_source_id);
                        unset($transaction->service_id);
                    }
                }

                $services_providers_sources = \DB::table('services_providers_sources')
                    ->where('services_providers_sources.id', $input['service_source_id'])
                    ->first();
            } else if (!empty($input['service_id'])) {
                $transactions = \DB::table('transactions')
                    ->select(\DB::raw('transactions.amount as amount, transactions.service_source_id, transactions.service_id, transactions.created_at'))
                    ->whereRaw("$where")
                    ->whereRaw("$whereSource")
                    ->get();

                foreach ($transactions as $transaction) {
                    $serv_provider = \DB::table('service_provider_products')
                        ->join('service_providers', 'service_providers.id', '=', 'service_provider_products.service_provider_id')
                        ->where('service_provider_products.id', $transaction->service_id)
                        ->first();

                    $proveedor = isset($serv_provider->name) ? $serv_provider->name : '';

                    $fecha = Carbon::parse($transaction->created_at)->format('d/m/Y H:i:s');
                    $transaction->nombre_servicio = isset($serv_provider->description) ? $serv_provider->description . ' - ' . $proveedor . ' - ' . $fecha : '';

                    unset($transaction->service_source_id);
                    unset($transaction->service_id);
                    unset($transaction->created_at);
                }

                $services_providers_sources = \DB::table('service_provider_products')
                    ->where('service_provider_products.id', $input['service_id'])
                    ->first();
            }
            //dd($transactions);
            $resultset = array(
                'transactions' => $transactions,
                'nombre' => $services_providers_sources->description
            );


            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    /***Resumen de los estados por atm*/
    public function estadoAtm()
    {
        try {
            //Redes
            $whereOwner = "";
            $whereBranch = "";
            $wherePos = "";
            if (!$this->user->hasAccess('superuser')) {
                if ($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11) {
                    $whereOwner = "owners.id = " . $this->user->owner_id;
                    $whereBranch = "branches.owner_id = " . $this->user->owner_id;
                    $wherePos = "points_of_sale.owner_id = " . $this->user->owner_id;
                }
            }
            //Redes
            $owners     = Owner::orderBy('owners.name')->where(function ($query) use ($whereOwner) {
                if (!empty($whereOwner)) {
                    $query->whereRaw($whereOwner);
                }
            })->get()->pluck('name', 'id');
            $owners->prepend('Todos', '0');

            if ($whereBranch !== "") {
                $whereBranch .= " and ";
            }

            $whereBranch .= " owner_id != 18";

            $branches = Branch::orderBy('description')
                ->where(
                    function ($query) use ($whereBranch) {
                        if (!empty($whereBranch)) {
                            $query->whereRaw($whereBranch);
                        }
                    }
                )
                ->get()
                ->pluck('description', 'id');

            $branches->prepend('Todos', '0');

            //\Log::info('PRIMERA LISTA:');
            //\Log::info($branches);


            //$branches = json_encode($branches);    

            $resultset = array(
                'target'        => 'Disponibilidad ATMs',
                'branches'      => $branches,
                'reservationtime' => '',
                'branch_id'     => 0,
            );

            return $resultset;
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de reportes" . $e);
            return false;
        }
    }

    public function estadoAtmSearch()
    {
        try {
            ################ no olvidar cambiar la conexion en produccion
            $input = $this->input;
            $atms = \DB::connection('eglobalt_replica')->table('points_of_sale')->where(function ($query) use ($input) {
                if ($input['branch_id'] <> 0) {
                    $query->where('branch_id', $input['branch_id']);
                }
            })
                ->where('deleted_at', '=', null)
                ->pluck('atm_id', 'atm_id');

            $where = "notifications.status in (2,3) AND ";
            /*SET DATE RANGE*/
            $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
            $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
            $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
            $where .= "notifications.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";

            $where = trim($where);
            $where = trim($where, 'AND');
            $where = trim($where);
            $notifications = \DB::connection('eglobalt_replica')->table('notifications')
                ->whereRaw("$where")
                ->where('notifications.deleted_at', null)
                ->where('notifications.notification_type', '<>', 4)
                ->whereIn("notifications.atm_id", $atms)
                ->orderByRaw('atm_id asc, created_at asc')
                ->get();

            $data = [];
            $suspendido = 2;
            $offline = 3;
            $online = -1;
            $idAtm = [];

            foreach ($notifications as $key => $notification) {
                $notification->created_at = date('Y-m-d H:i:s', strtotime($notification->created_at));
                $fecha = date('d/m/Y', strtotime($notification->created_at));
                $hora_inicio = date('Y-m-d H:i:s', strtotime($notification->created_at));
                $status = $notification->status;
                $atm_id = $notification->atm_id;
                $idAtm[$atm_id] = $atm_id;

                if (!isset($data[$atm_id])) {
                    $data[$atm_id][$suspendido] = 0;
                    $data[$atm_id][$offline] = 0;
                    $data[$atm_id][$online] = 0;
                    $data[$atm_id]['total_minutos'] = 0;
                    $data[$atm_id]['fecha_anterior'] = $daterange[0];

                    // PARA CALCULAR LOS MINUTOS DEL ESTADO INICIAL DEL ATM SEGUN EL RANGO INGRESADO
                    //Consultamos la notificacion anterior del rango actual del atm
                    $notificacion_anterior_atm[$atm_id] = \DB::connection('eglobalt_replica')->table('notifications')
                        ->where("notifications.created_at", "<", $daterange[0])
                        ->where('notifications.deleted_at', null)
                        ->where('notifications.notification_type', '<>', 4)
                        ->whereIn('notifications.status', [$suspendido, $offline])
                        ->where("notifications.atm_id", $notification->atm_id)
                        ->orderByRaw('atm_id asc, created_at desc')
                        ->first();

                    $start  = Carbon::parse($daterange[0]);
                    $end =  Carbon::parse($notification->created_at);
                    $elasep = $start->diffInMinutes($end);

                    // Si existen registros anteriores
                    if (!empty($notificacion_anterior_atm[$atm_id])) {
                        $ultimo_status = $notificacion_anterior_atm[$atm_id]->status;

                        //Si la notificacion anterior fuera del rango ingresado fue procesada
                        if ($notificacion_anterior_atm[$atm_id]->processed) {
                            //Si fue procesada posterior a la primera fecha del rango
                            if (Carbon::parse($notificacion_anterior_atm[$atm_id]->updated_at) > Carbon::parse($daterange[0])) {
                                //Si fue procesada antes de la primera notificacion dentro del rango
                                if (Carbon::parse($notificacion_anterior_atm[$atm_id]->updated_at) < Carbon::parse($notification->created_at)) {
                                    // Se calcula los minutos entre la primera fecha del rango y la fecha que fue procesada la anterior notificacion
                                    $start  = Carbon::parse($daterange[0]);
                                    $end =  Carbon::parse($notificacion_anterior_atm[$atm_id]->updated_at);
                                    $elasep = $start->diffInMinutes($end);
                                    $data[$atm_id][$ultimo_status] += $elasep;
                                    $data[$atm_id]['total_minutos'] += $elasep;

                                    // Se calcula los minutos entre la fecha que fue procesada la anterior notificacion, y la primera notificacion del rango
                                    $start  = Carbon::parse($notificacion_anterior_atm[$atm_id]->updated_at);
                                    $end =  Carbon::parse($notification->created_at);
                                    $elasep = $start->diffInMinutes($end);
                                    $data[$atm_id][$online] += $elasep;
                                } else {
                                    $data[$atm_id][$ultimo_status] += $elasep;
                                }
                            } else {
                                $data[$atm_id][$online] += $elasep;
                            }
                        } else {
                            $data[$atm_id][$ultimo_status] += $elasep;
                        }
                    } else {
                        $data[$atm_id][$online] += $elasep;
                    }

                    $data[$atm_id]['total_minutos'] += $elasep;

                    //Consultamos la ultima notificacion del rango actual del atm
                    $ultima_notificacion_atm[$atm_id] = \DB::connection('eglobalt_replica')->table('notifications')
                        ->whereRaw("$where")
                        ->where('notifications.deleted_at', null)
                        ->where('notifications.notification_type', '<>', 4)
                        ->whereIn('notifications.status', [$suspendido, $offline])
                        ->where("notifications.atm_id", $notification->atm_id)
                        ->orderByRaw('atm_id asc, created_at desc')
                        ->first();

                    // Verificamos si la ultima notificacion del atm fue procesada
                    if ($ultima_notificacion_atm[$atm_id]->processed) {
                        // Si la notificacion fue procesada posterior al rango de fechas, el tiempo restante queda con el ultimo estado
                        // Caso contrario, queda como ONLINE
                        if (Carbon::parse($ultima_notificacion_atm[$atm_id]->updated_at) < Carbon::parse($daterange[1])) {
                            $estado_final = $online;
                            $start  = Carbon::parse($ultima_notificacion_atm[$atm_id]->updated_at);
                            $end =  Carbon::parse($daterange[1]);
                            $elasep = $start->diffInMinutes($end);
                            $data[$atm_id][$estado_final] += $elasep;
                            $data[$atm_id]['total_minutos'] += $elasep;
                        }
                    }
                } else {
                    //Calculamos el tiempo entre la notificacion actual y la anterior
                    //
                    // Si la notificacion anterior fue procesada, la diferencia de tiempo se toma como ONLINE
                    if ($data[$atm_id]['procesado']) {
                        if ($data[$atm_id]['fecha_anterior'] < $notification->created_at) {
                            $start  = Carbon::parse($data[$atm_id]['fecha_anterior']);
                            $end =  Carbon::parse($notification->created_at);
                            $elasep = $start->diffInMinutes($end);
                            $data[$atm_id][$online] += $elasep;
                        } else {
                            if ($notification->processed) {
                                if ($data[$atm_id]['fecha_anterior'] <= $notification->updated_at && $notification->updated_at <= $daterange[1]) {
                                    $start  = Carbon::parse($data[$atm_id]['fecha_anterior']);
                                    $end =  Carbon::parse($notification->updated_at);
                                    $elasep = $start->diffInMinutes($end);
                                    $data[$atm_id][$status] += $elasep;
                                }
                            }
                        }
                    } else {
                        $start  = Carbon::parse($data[$atm_id]['fecha_anterior']);
                        $end =  Carbon::parse($notification->created_at);
                        $elasep = $start->diffInMinutes($end);
                        $data[$atm_id][$data[$atm_id]['estado_anterior']] += $elasep;
                    }

                    $data[$atm_id]['total_minutos'] += $elasep;
                }

                // Si la notificacion actual ya fue procesado
                if ($notification->processed) {
                    if ($data[$atm_id]['fecha_anterior'] < $notification->created_at) {
                        //Se calcula la diferencia de minutos entre el created y el update
                        $start  = Carbon::parse($notification->created_at);
                        if ($notification->updated_at > $daterange[1]) {
                            $end =  Carbon::parse($daterange[1]);
                            $data[$atm_id]['fecha_anterior'] = $daterange[1];
                        } else {
                            $end =  Carbon::parse($notification->updated_at);
                            $data[$atm_id]['fecha_anterior'] = $notification->updated_at;
                        }
                        $elasep = $start->diffInMinutes($end);

                        $data[$atm_id][$status] += $elasep;
                        $data[$atm_id]['estado_anterior'] = $notification->status;
                        $data[$atm_id]['procesado'] = $notification->processed;
                        $data[$atm_id]['total_minutos'] += $elasep;
                    }
                } else {
                    //Se calcula la diferencia de minutos entre el created y el update
                    $start  = Carbon::parse($notification->created_at);
                    $end =  Carbon::parse($daterange[1]);
                    $elasep = $start->diffInMinutes($end);

                    $data[$atm_id][$status] += $elasep;
                    $data[$atm_id]['fecha_anterior'] = $notification->created_at;
                    $data[$atm_id]['estado_anterior'] = $notification->status;
                    $data[$atm_id]['procesado'] = $notification->processed;
                    $data[$atm_id]['total_minutos'] += $elasep;
                }
            }

            $whereBranch = "";
            $branches   = Branch::orderBy('description')->where(function ($query) use ($whereBranch) {
                if (!empty($whereBranch)) {
                    $query->whereRaw($whereBranch);
                }
            })->get()->pluck('description', 'id');
            $branches->prepend('Todos', '0');

            $atms = \DB::connection('eglobalt_replica')->table('atms')
                ->select('atms.name', 'atms.id', 'branches.id as branch_id', 'branches.description as branch_name')
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'atms.id')
                ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->whereIn('atms.id', $idAtm)
                ->get();

            $datosAtm = [];
            foreach ($atms as $key => $atm) {
                $datosAtm[$atm->branch_id]['nombre'] = $atm->branch_name;
                $datosAtm[$atm->branch_id]['atms'][$atm->id] = $atm->name;
            }

            $resultset = array(
                'target'           => 'Disponibilidad ATMs',
                'atms'             => $datosAtm,
                'datos'            => json_encode($data),
                'reservationtime'  => (isset($input['reservationtime']) ? $input['reservationtime'] : 0),
                'i'                =>  1,
                'branches'         => $branches,
                'branch_id'        => (isset($input['branch_id']) ? $input['branch_id'] : 0),
            );


            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    public function estadoAtmDetalle()
    {
        try {
            $input = $this->input;
            $atms = \DB::connection('eglobalt_replica')->table('points_of_sale')->where(function ($query) use ($input) {
                if ($input['atm_id'] <> 0) {
                    $query->where('atm_id', $input['atm_id']);
                }
            })
                ->where('deleted_at', '=', null)
                ->pluck('atm_id', 'atm_id');

            switch ($input['status']) {
                case 'Offline':
                    $estado = 3;
                    break;
                case 'Suspendido':
                    $estado = 2;
                    break;
            }

            $where = "notifications.status in (" . $estado . ") AND ";
            /*SET DATE RANGE*/
            $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
            $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
            $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
            $where .= "notifications.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";

            $where = trim($where);
            $where = trim($where, 'AND');
            $where = trim($where);
            $notifications = \DB::connection('eglobalt_pro')->table('notifications')
                ->select(\DB::raw('notifications.id as id,notification_types.description as type, points_of_sale.description as pdv, atms.code, notifications.created_at, notifications.updated_at, message, processed, notifications_status.status_description, users.username, notifications.service_id,notifications.service_source_id'))
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'notifications.atm_id')
                ->join('atms', 'atms.id', '=', 'notifications.atm_id')
                ->join('notification_types', 'notification_types.id', '=', 'notifications.notification_type')
                ->join('notifications_status', 'notifications_status.id', '=', 'notifications.status')
                ->leftJoin('users', 'users.id', '=', 'notifications.asigned_to')
                ->whereRaw("$where")
                ->where('notifications.deleted_at', null)
                ->where('notifications.notification_type', '<>', 4)
                ->whereIn("notifications.atm_id", $atms)
                ->orderByRaw('notifications.atm_id asc, notifications.created_at asc')
                ->get();

            $data = '';
            foreach ($notifications as $key => $notification) {
                $data .= '<tr>';
                $data .= '<td>' . $notification->id . '</td>';
                $data .= '<td>' . $notification->code . '</td>';
                $data .= '<td>' . $notification->pdv . '</td>';
                $data .= '<td>' . $notification->status_description . '</td>';
                $data .= '<td>' . $notification->type . '</td>';

                $data .= '<td>' . $notification->message . '</td>';

                $data .= '<td>' . Carbon::parse($notification->created_at)->format('d/m/Y H:i:s') . '</td>';

                if ($notification->updated_at) {
                    $data .= '<td>' . Carbon::parse($notification->updated_at)->format('d/m/Y H:i:s') . '</td>';
                } else {
                    $data .= '<td></td>';
                }

                if ($notification->processed) {
                    $data .= '<td><span id="not-count" class="label label-success">Procesado</span></td>';
                } else {
                    $data .= '<td><span id="not-count" class="label label-danger">Pendiente</span></td>';
                }

                $data .= '<td>' . Carbon::parse($notification->updated_at)->diffInMinutes(Carbon::parse($notification->created_at)) . ' minutos</td>';
                $data .= '<td>' . $notification->username . '</td>';
                $data .= '</tr>';
            }

            $datos_modal = [
                'modal_contenido' => $data,
                'modal_footer' => '',
            ];

            return $datos_modal;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    public function estadoAtmDetalleExport()
    {
        try {
            $input = $this->input;
            $atms = \DB::connection('eglobalt_replica')->table('points_of_sale')->where(function ($query) use ($input) {
                if ($input['atm_id'] <> 0) {
                    $query->where('atm_id', $input['atm_id']);
                }
            })
                ->where('deleted_at', '=', null)
                ->pluck('atm_id', 'atm_id');

            switch ($input['status']) {
                case 'Offline':
                    $estado = 3;
                    break;
                case 'Suspendido':
                    $estado = 2;
                    break;
            }

            $where = "notifications.status in (" . $estado . ") AND ";
            /*SET DATE RANGE*/
            $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
            $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
            $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
            $where .= "notifications.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";

            $where = trim($where);
            $where = trim($where, 'AND');
            $where = trim($where);
            $notifications = \DB::connection('eglobalt_pro')->table('notifications')
                ->select(\DB::raw('notifications.id as id, notifications.service_id, points_of_sale.description as sucursal, notifications_status.status_description as estado,notification_types.description as tipo, message as mensaje, notifications.created_at as fecha_inicio, notifications.updated_at as fecha_fin, users.username as asignado_a,notifications.service_source_id'))
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'notifications.atm_id')
                ->join('atms', 'atms.id', '=', 'notifications.atm_id')
                ->join('notification_types', 'notification_types.id', '=', 'notifications.notification_type')
                ->join('notifications_status', 'notifications_status.id', '=', 'notifications.status')
                ->leftJoin('users', 'users.id', '=', 'notifications.asigned_to')
                ->leftjoin('services_providers_sources', 'services_providers_sources.id', '=', 'notifications.service_source_id')
                ->whereRaw("$where")
                ->where('notifications.deleted_at', null)
                ->where('notifications.notification_type', '<>', 4)
                ->whereIn("notifications.atm_id", $atms)
                ->orderByRaw('notifications.atm_id asc, notifications.created_at asc')
                ->get();

            foreach ($notifications as $notification) {
                if ($notification->service_source_id > 0) {
                    $service_data = \DB::table('services_ondanet_pairing')->where('service_source_id', $notification->service_source_id)->where('service_request_id', $notification->service_id)->first();
                    if ($service_data) {
                        $notification->service_description = $service_data->service_description;
                    } else {
                        $notification->service_description = ' Concepto no registrado en ondanet ';
                    }
                } else {
                    $service_data = \DB::table('service_provider_products')->where('id', $notification->service_id)->first();
                    if ($service_data) {
                        $notification->service_description = $service_data->description;
                    } else {
                        $notification->service_description = '';
                    }
                }
                unset($notification->service_source_id);
            }

            return $notifications;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    /*TRANSATIONS*/
    public function transactionsAmountReports()
    {
        try {

            //Redes
            $whereGroup = "";
            $whereOwner = "";
            $whereBranch = "";
            $wherePos = "";
            if (!$this->user->hasAccess('superuser')) {
                if ($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11) {
                    $whereOwner = "owners.id = " . $this->user->owner_id;
                    $whereBranch = "branches.owner_id = " . $this->user->owner_id;
                    $wherePos = "points_of_sale.owner_id = " . $this->user->owner_id;
                }
            }
            //Redes
            $groups     = Group::orderBy('business_groups.description')->where(function ($query) use ($whereGroup) {
                if (!empty($whereGroup)) {
                    $query->whereRaw($whereGroup);
                }
            })->get()->pluck('description', 'id');
            $groups->prepend('Todos', '0');

            $owners     = Owner::orderBy('owners.name')->where(function ($query) use ($whereOwner) {
                if (!empty($whereOwner)) {
                    $query->whereRaw($whereOwner);
                }
            })->get()->pluck('name', 'id');
            $owners->prepend('Todos', '0');
            $branches   = Branch::orderBy('description')->where(function ($query) use ($whereBranch) {
                if (!empty($whereBranch)) {
                    $query->whereRaw($whereBranch);
                }
            })->get()->pluck('description', 'id');
            $branches->prepend('Todos', '0');
            $pdvs       = Pos::orderBy('description')->where(function ($query) use ($wherePos) {
                if (!empty($wherePos)) {
                    $query->whereRaw($wherePos);
                }
            })->with('Atm')->get();
            $pos = [];
            $item = array();
            $item[0] = 'Todos';

            foreach ($pdvs  as $pdv) {
                $item[$pdv->id] = $pdv->description . ' - ' . $pdv->Atm->code;
                $pos = $item;
            }
            $status = array('0' => 'Todos', 'success' => 'Aprobado', 'canceled' => 'Cancelado', 'error' => 'Error', 'rollback' => 'Reversado', 'iniciated' => 'Iniciado', 'error dispositivo' => 'Error de dispositivo', 'error dispositivo' => 'Error de dispositivo', 'devolucion' => 'Devolución', 'inconsistency' => 'Inconsistencia');
            $atmType = array('0' => 'Todos', 'da' => 'App Billetaje', 'ws' => 'Web Service', 'at' => 'Atm');
            $services   = ServiceProviderProduct::with('WebServiceProvider')->orderBy('service_provider_id', 'DESC')->get();
            $services_data = [];
            $service_item = array();

            if ($this->user->hasAccess('ticketea') && !$this->user->hasAccess('superuser')) {
                $service_item[28] = 'Ticketea - Venta de tickets';
                $services_data = $service_item;
            } else {
                $service_item[-2] = 'Netel';
                $service_item[-4] = 'Pronet';
                $service_item[-6] = 'Practipago';
                $service_item[-7] = 'Infonet';
                $service_item[-8] = 'Toval';
                $service_item[-10] = 'Netel Trex';
                $service_item[-11] = 'Bancar ventas QR';
                foreach ($services  as $service) {
                    if ($service->id <> 100) {
                        if ($service->id <> 27 && $service->id <> -1 && $service->WebServiceProvider->name <> 'Toval - Integraciones' && $service->WebServiceProvider->name <> 'Momo - Ken') {
                            $service_item[$service->id] = $service->WebServiceProvider->name . ' - ' . $service->description;
                            $services_data = $service_item;
                        }
                    }
                }
            }

            $servicesRequestData = [];

            $checkbox = false;
            $checkbox2 = false;

            $resultset = array(
                'target'        => 'Transacciones por Mes',
                'groups'        => $groups,
                'owners'        => $owners,
                'branches'      => $branches,
                'pos'           => $pos,
                'status'        => $status,
                'type'          => $atmType,
                'services_data' => $services_data,
                'group_id'      => 0,
                'owner_id'      => 0,
                'branch_id'     => 0,
                'pos_id'        => 0,
                'status_set'    => 0,
                'type_set'      => 0,
                'service_id'    => 0,
                'checkbox'      => $checkbox,
                'checkbox2'     => $checkbox2,
                'services_request_data' => $servicesRequestData,
                'service_request_id'    => '',
            );

            return $resultset;
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de reportes" . $e);
            return false;
        }
    }

    public function transactionsAmountSearch()
    {
        try {
            $input = $this->input;
            $where = "transactions.transaction_type in (1,7,12,13) AND ";
            /*Busqueda minusiosa*/
            if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
                $where .= "transactions.id = {$input['context']} OR ";
                $where .= "transactions.referencia_numero_1 = '{$input['context']}' AND ";
            } else {


                /*SET DATE RANGE*/
                $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                $where .= "transactions.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' AND ";

                /*SET OWNER*/
                $where .= ($input['group_id'] <> 0) ? "branches.group_id = " . $input['group_id'] . " AND " : "";

                if (!$this->user->hasAccess('superuser')) {
                    if (($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11)) {
                        $where .= "transactions.owner_id = " . $this->user->owner_id . " AND ";
                    } else {
                        $where .= ($input['owner_id'] <> 0) ? "transactions.owner_id = " . $input['owner_id'] . " AND " : "";
                    }
                } else {
                    $where .= ($input['owner_id'] <> 0) ? "transactions.owner_id = " . $input['owner_id'] . " AND " : "";
                }

                $where .= ($input['branch_id'] <> 0) ? "points_of_sale.branch_id = " . $input['branch_id'] . " AND " : "";
                $where .= ($input['pos_id'] <> 0) ? "points_of_sale.id = " . $input['pos_id'] . " AND " : "";
                $where .= ($input['status_id'] <> "0") ? "transactions.status =  '{$input['status_id']}' AND " : "";
                $where .= ($input['type'] <> "0") ? "atms.type =  '{$input['type']}' AND " : "";
                $services_sources = [];
                $services_ids = [];
                if (isset($input['service_id'])) {
                    foreach ($input['service_id'] as $key => $service_id) {
                        if (strstr($service_id, '-')) {
                            if ($service_id == -2) {
                                $services_sources[] = 1;
                            } elseif ($service_id == -4) {
                                $services_sources[] = 4;
                            } elseif ($service_id == -6) {
                                $services_sources[] = 6;
                            } elseif ($service_id == -7) {
                                $services_sources[] = 7;
                            }
                        } else {
                            if (empty($services_ids)) {
                                $services_sources[] = 0;
                            }
                            $services_ids[] = $service_id;
                        }
                    }
                }

                if (isset($input['service_request_id'])) {
                    foreach ($input['service_request_id'] as $key => $service_request_id) {
                        $services_ids[] = $service_request_id;
                    }
                }

                if ($this->user->hasAccess('ticketea') && !$this->user->hasAccess('superuser')) {
                    if (!in_array($services_ids, 28)) {
                        $services_ids[] = 28;
                    }
                }
            }

            $where = trim($where);
            $where = trim($where, 'AND');
            $where = trim($where);

            $transactions = \DB::connection('eglobalt_replica')->table('transactions')
                ->selectRaw('to_char(transactions.created_at,\'MM\') as mon,
                    to_char(transactions.created_at,\'YYYY\') as anho,
                    sum(abs(transactions.amount)) as amount')
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'transactions.atm_id')
                ->leftjoin('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->leftjoin('atms', 'atms.id', '=', 'transactions.atm_id')
                ->whereRaw("$where")
                ->where(function ($query) use ($services_ids, $services_sources) {
                    if (!empty($services_ids)) {
                        $query->whereIn('service_id', $services_ids);
                    }
                    if (!empty($services_sources)) {
                        $query->whereIn('service_source_id', $services_sources);
                    }
                })
                ->groupBy("mon", "anho")
                ->orderBy("anho", "asc")
                ->orderBy("mon", "asc")
                ->get();


            $meses = [
                '01' => 'Ene',
                '02' => 'Feb',
                '03' => 'Mar',
                '04' => 'Abr',
                '05' => 'May',
                '06' => 'Jun',
                '07' => 'Jul',
                '08' => 'Ago',
                '09' => 'Set',
                '10' => 'Oct',
                '11' => 'Nov',
                '12' => 'Dic',
            ];

            $chart_data = [];
            foreach ($transactions as $key => $value) {
                $data_mes = [];
                $data_mes['mes'] = $meses[$value->mon] . ' ' . $value->anho;
                $data_mes['monto'] = $value->amount;
                $data_mes['monto_formateado'] = number_format($value->amount, 0, ',', '.');
                $chart_data[] = $data_mes;
            }

            /*Carga datos del formulario*/
            $whereGroup = "";
            $whereOwner = "";
            $whereBranch = "";
            $wherePos = "";
            if (!$this->user->hasAccess('superuser')) {
                if ($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11) {
                    $whereOwner = "owners.id = " . $this->user->owner_id;
                    $whereBranch = "branches.owner_id = " . $this->user->owner_id;
                    $wherePos = "points_of_sale.owner_id = " . $this->user->owner_id;
                }
            }
            //Redes
            $groups     = Group::orderBy('business_groups.description')->where(function ($query) use ($whereGroup) {
                if (!empty($whereGroup)) {
                    $query->whereRaw($whereGroup);
                }
            })->get()->pluck('description', 'id');
            $groups->prepend('Todos', '0');

            $owners     = Owner::orderBy('owners.name')->where(function ($query) use ($whereOwner) {
                if (!empty($whereOwner)) {
                    $query->whereRaw($whereOwner);
                }
            })->get()->pluck('name', 'id');
            $owners->prepend('Todos', '0');
            $branches   = Branch::orderBy('description')->where(function ($query) use ($whereBranch) {
                if (!empty($whereBranch)) {
                    $query->whereRaw($whereBranch);
                }
            })->get()->pluck('description', 'id');
            $branches->prepend('Todos', '0');
            $pdvs       = Pos::orderBy('description')->where(function ($query) use ($wherePos) {
                if (!empty($wherePos)) {
                    $query->whereRaw($wherePos);
                }
            })->with('Atm')->get();
            $pos = [];
            $item = array();
            $item[0] = 'Todos';
            foreach ($pdvs  as $pdv) {
                $item[$pdv->id] = $pdv->description . ' - ' . $pdv->Atm->code;
                $pos = $item;
            }

            $status = array('0' => 'Todos', 'success' => 'Aprobado', 'canceled' => 'Cancelado', 'error' => 'Error', 'rollback' => 'Reversado', 'iniciated' => 'Iniciado', 'error dispositivo' => 'Error de dispositivo', 'devolucion' => 'Devolución', 'inconsistency' => 'Inconsistencia');
            $atmType = array('0' => 'Todos', 'da' => 'App Billetaje', 'ws' => 'Web Service', 'at' => 'Atm');
            $services = ServiceProviderProduct::with('WebServiceProvider')->orderBy('service_provider_id', 'DESC')->get();
            if ($this->user->hasAccess('ticketea') && !$this->user->hasAccess('superuser')) {
                $service_item[28] = 'Ticketea - Venta de tickets';
                $services_data = $service_item;
            } else {
                $service_item[-2] = 'Netel';
                $service_item[-4] = 'Pronet';
                $service_item[-6] = 'Practipago';
                $service_item[-7] = 'Infonet';
                $service_item[-8] = 'Toval';
                $service_item[-10] = 'Netel Trex';
                $service_item[-11] = 'Bancard Ventas QR';
                foreach ($services  as $service) {
                    if ($service->id <> 100) {
                        if ($service->id <> 27 && $service->id <> -1 && $service->WebServiceProvider->name <> 'Toval - Integraciones' && $service->WebServiceProvider->name <> 'Momo - Ken') {
                            $service_item[$service->id] = $service->WebServiceProvider->name . ' - ' . $service->description;
                            $services_data = $service_item;
                        }
                    }
                }
            }

            if (isset($input['service_request_id'])) {
                $servicesRequestData = \DB::table('services_ondanet_pairing')
                    ->whereIn('service_request_id', $input['service_request_id'])
                    ->pluck('service_request_id', 'service_request_id');
            } else {
                $servicesRequestData = [];
            }

            $resultset = array(
                'target'        => 'Transacciones por Mes',
                'groups'        => $groups,
                'owners'        => $owners,
                'branches'      => $branches,
                'pos'           => $pos,
                'status'        => $status,
                'type'          => $atmType,
                'services_data' => $services_data,
                'transactions'  => $transactions,
                'group_id'      => (isset($input['group_id']) ? $input['group_id'] : 0),
                'chart_data'    => json_encode($chart_data),
                'owner_id'      => (isset($input['owner_id']) ? $input['owner_id'] : 0),
                'branch_id'     => (isset($input['branch_id']) ? $input['branch_id'] : 0),
                'pos_id'        => (isset($input['pos_id']) ? $input['pos_id'] : 0),
                'status_set'    => (isset($input['status_id']) ? $input['status_id'] : 0),
                'type_set'    => (isset($input['type']) ? $input['type'] : 0),
                'service_id'    => (isset($input['service_id']) ? $input['service_id'] : 0),
                'reservationtime' => (isset($input['reservationtime']) ? $input['reservationtime'] : 0),
                'i'             =>  1,
                'services_request_data' => $servicesRequestData,
                'checkbox'    => (isset($input['checkbox']) ? $input['checkbox'] : false),
                'checkbox2'    => (isset($input['checkbox2']) ? $input['checkbox2'] : false),
                'service_request_id' => (isset($input['service_request_id']) ? $input['service_request_id'] : 0),
            );


            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    /*DISPOSITIVOS*/
    public function dispositivosReports()
    {
        try {
            //Redes
            $owners     = Owner::orderBy('name')->get()->pluck('name', 'id');
            $owners->prepend('Todos', '0');
            $branches   = Branch::orderBy('description')->get()->pluck('description', 'id');
            $branches->prepend('Todos', '0');
            $types = array('0' => 'Todos', '1' => 'Estados Atms', '2' => 'Servicios', '4' => 'Saldos');
            $pdvs       = Pos::orderBy('description')->with('Atm')->get();
            $pos = [];
            $item = array();
            $item[0] = 'Todos';
            foreach ($pdvs  as $pdv) {
                $item[$pdv->id] = $pdv->description . ' - ' . $pdv->Atm->code;
                $pos = $item;
            }

            $resultset = $this->dispositivosSearch();


            return $resultset;
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de reportes" . $e->getMessage());
            return false;
        }
    }

    public function dispositivosSearch()
    {
        try {
            if (!$this->input) {
                $reservation_time = Carbon::today() . ' - ' . Carbon::today()->endOfDay();
                $branch_id = 0;
                $pos_id = 0;
            } else {
                $input = $this->input;
                $reservation_time = $input['reservationtime'];
                $branch_id = $input['branch_id'];
                $pos_id = $input['pos_id'];
            }

            $where = "";
            /*SET DATE RANGE*/
            $daterange = explode(' - ',  str_replace('/', '-', $reservation_time));
            $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
            $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
            $where .= "notifications.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' AND ";
            $where .= ($branch_id <> 0) ? "points_of_sale.branch_id = " . $branch_id . " AND " : "";
            $where .= ($pos_id <> 0) ? "points_of_sale.id = " . $pos_id . " AND " : "";


            $where .= " notifications.notification_type in (3) AND ";

            $where = trim($where);
            $where = trim($where, 'AND');
            $where = trim($where);

            $notifications = \DB::connection('eglobalt_pro')->table('notifications')
                ->select(\DB::raw('notifications.id as id,notification_types.description as type, points_of_sale.description as pdv, atms.code, notifications.created_at, notifications.updated_at, message, processed, services_providers_sources.description as provider, services_providers_sources.id as provider_id, notifications.service_id,notifications.service_source_id, error_list.code as error_code, error_list.description as error_description, error_list.mensaje, notifications.transaction_id'))
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'notifications.atm_id')
                ->join('atms', 'atms.id', '=', 'notifications.atm_id')
                ->join('notification_types', 'notification_types.id', '=', 'notifications.notification_type')
                //->leftjoin('users','users.id','=','notifications.asigned_to')
                ->leftjoin('services_providers_sources', 'services_providers_sources.id', '=', 'notifications.service_source_id')
                ->leftjoin('error_list', 'error_list.code', '=', 'notifications.error_code')
                ->whereRaw("$where")
                ->orderBy('notifications.created_at', 'desc')
                ->paginate(20);

            foreach ($notifications as $notification) {
                if ($notification->service_source_id > 0) {
                    $service_data = \DB::table('services_ondanet_pairing')->where('service_source_id', $notification->service_source_id)->where('service_request_id', $notification->service_id)->first();
                    if ($service_data) {
                        $notification->service_description = $service_data->service_description;
                    } else {
                        $notification->service_description = ' Concepto no registrado en ondanet ';
                    }
                } else {
                    $service_data = \DB::table('service_provider_products')->where('id', $notification->service_id)->first();
                    if ($service_data) {
                        $notification->service_description = $service_data->description;
                    } else {
                        $notification->service_description = '';
                    }
                }
            }

            /*Carga datos del formulario*/
            $owners     = Owner::orderBy('name')->pluck('name', 'id');
            $owners->prepend('Todos', '0');
            $branches   = Branch::orderBy('description')->pluck('description', 'id');
            $branches->prepend('Todos', '0');
            $types = array('0' => 'Todos', '1' => 'Estados Atms', '2' => 'Servicios', '4' => 'Saldos');
            $status = array('-1' => 'Todos', 'false' => 'Pendientes', 'true' => 'Procesados');
            $pdvs       = Pos::orderBy('description')->with('Atm')->get();
            $pos = [];
            $item = array();
            $item[0] = 'Todos';
            foreach ($pdvs  as $pdv) {
                $item[$pdv->id] = $pdv->description . ' - ' . $pdv->Atm->code;
                $pos = $item;
            }

            $resultset = array(
                'target'        => 'Dispositivos',
                'owners'        => $owners,
                'branches'      => $branches,
                'pos'           => $pos,
                'types'         => $types,
                'status'        => $status,
                'notifications' => $notifications,
                'owner_id'      => (isset($input['owner_id']) ? $input['owner_id'] : 0),
                'branch_id'     => (isset($input['branch_id']) ? $input['branch_id'] : 0),
                'pos_id'        => (isset($input['pos_id']) ? $input['pos_id'] : 0),
                'reservationtime' => (isset($input['reservationtime']) ? $input['reservationtime'] : $reservation_time),
                'i'             =>  1
            );

            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    public function dispositivosSearchExport()
    {
        try {
            $input = $this->input;
            $reservation_time = $input['reservationtime'];
            $branch_id = $input['branch_id'];
            $pos_id = $input['pos_id'];
            $where = "";

            /*SET DATE RANGE*/
            $daterange = explode(' - ',  str_replace('/', '-', $reservation_time));
            $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
            $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
            $where .= "notifications.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' AND ";
            $where .= ($branch_id <> 0) ? "points_of_sale.branch_id = " . $branch_id . " AND " : "";
            $where .= ($pos_id <> 0) ? "points_of_sale.id = " . $pos_id . " AND " : "";

            $where .= " notifications.notification_type in (3) AND ";

            $where = trim($where);
            $where = trim($where, 'AND');
            $where = trim($where);

            $notifications = \DB::connection('eglobalt_pro')->table('notifications')
                ->select(\DB::raw('notifications.id as id, points_of_sale.description as sucursal, atms.code, message, error_list.code as error_code, error_list.description as error_description, error_list.mensaje, notifications.created_at as fecha_inicio, notifications.updated_at as fecha_fin, users.username as asignado_a, notifications.service_source_id, notifications.service_id, notifications.transaction_id'))
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'notifications.atm_id')
                ->join('atms', 'atms.id', '=', 'notifications.atm_id')
                ->join('notification_types', 'notification_types.id', '=', 'notifications.notification_type')
                ->join('users', 'users.id', '=', 'notifications.asigned_to')
                ->leftjoin('services_providers_sources', 'services_providers_sources.id', '=', 'notifications.service_source_id')
                ->join('error_list', 'error_list.code', '=', 'notifications.error_code')
                ->whereRaw("$where")
                ->orderBy('notifications.created_at', 'desc')
                ->get();

            foreach ($notifications as $notification) {
                if ($notification->service_source_id > 0) {
                    $service_data = \DB::table('services_ondanet_pairing')->where('service_source_id', $notification->service_source_id)->where('service_request_id', $notification->service_id)->first();
                    if ($service_data) {
                        $notification->service_description = $service_data->service_description;
                    } else {
                        $notification->service_description = ' Concepto no registrado en ondanet ';
                    }
                } else {
                    $service_data = \DB::table('service_provider_products')->where('id', $notification->service_id)->first();
                    if ($service_data) {
                        $notification->service_description = $service_data->description;
                    } else {
                        $notification->service_description = '';
                    }
                }
                unset($notification->service_source_id);
            }


            return $notifications;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    /*TRANSATIONS*/
    public function transactionsVueltoReports()
    {
        try {

            //Redes
            $whereGroup = "";
            $whereOwner = "";
            $whereBranch = "";
            $wherePos = "";

            ///////////////////////////////////////////////////////////////
            if (!\Sentinel::getUser()->inRole('mini_terminal') && !\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                $branches   = Branch::orderBy('description')->where(function ($query) use ($whereBranch) {
                    if (!empty($whereBranch)) {
                        $query->whereRaw($whereBranch);
                    }
                })->get()->pluck('description', 'id');
                $branches->prepend('Todos', '0');
            } else if (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {

                if ($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11) {
                    $whereOwner = "owners.id = " . $this->user->owner_id;
                    $whereBranch = "branches.owner_id = " . $this->user->owner_id;
                    $wherePos = "points_of_sale.owner_id = " . $this->user->owner_id;

                    $supervisor = \DB::table('users_x_groups')->where('user_id', $this->user->id)->first();

                    $branchess = \DB::table('branches')
                        ->select(['branches.description', 'users.username', 'users.id'])
                        ->join('users', 'branches.user_id', '=', 'users.id')
                        ->join('role_users', 'users.id', '=', 'role_users.user_id')
                        ->where('role_users.role_id', 22)
                        ->where('branches.group_id', $supervisor->group_id)
                        ->get();
                    $branches = [];
                    foreach ($branchess as $key => $branch) {
                        $branches[$branch->id] = $branch->description . ' | ' . $branch->username;
                    }
                }
            } else {
                $branches = \DB::table('branches')
                    ->select(['branches.description', 'users.username', 'users.id'])
                    ->join('users', 'branches.user_id', '=', 'users.id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->where('branches.user_id', $this->user->id)
                    ->get();
            }

            //Redes
            $groups     = Group::orderBy('business_groups.description')->where(function ($query) use ($whereGroup) {
                if (!empty($whereGroup)) {
                    $query->whereRaw($whereGroup);
                }
            })->get()->pluck('description', 'id');
            $groups->prepend('Todos', '0');

            $owners     = Owner::orderBy('owners.name')->where(function ($query) use ($whereOwner) {
                if (!empty($whereOwner)) {
                    $query->whereRaw($whereOwner);
                }
            })->get()->pluck('name', 'id');
            $owners->prepend('Todos', '0');

            $pdvs       = Pos::orderBy('description')->where(function ($query) use ($wherePos) {
                if (!empty($wherePos)) {
                    $query->whereRaw($wherePos);
                }
            })->with('Atm')->get();
            $pos = [];
            $item = array();
            $item[0] = 'Todos';

            foreach ($pdvs  as $pdv) {
                $item[$pdv->id] = $pdv->description . ' - ' . $pdv->Atm->code;
                $pos = $item;
            }
            $status = array('0' => 'Todos', 'success' => 'Aprobado', 'canceled' => 'Cancelado', 'error' => 'Error', 'rollback' => 'Reversado', 'iniciated' => 'Iniciado', 'error dispositivo' => 'Error de dispositivo', 'error dispositivo' => 'Error de dispositivo', 'devolucion' => 'Devolución', 'inconsistency' => 'Inconsistencia');
            $services   = ServiceProviderProduct::with('WebServiceProvider')->orderBy('service_provider_id', 'DESC')->get();
            $services_data = [];
            $service_item = array();

            if ($this->user->hasAccess('ticketea') && !$this->user->hasAccess('superuser')) {
                $service_item[28] = 'Ticketea - Venta de tickets';
                $services_data = $service_item;
            } else {
                $service_item[0] = 'Todos';
                $service_item[-2] = 'Netel';
                $service_item[-4] = 'Pronet';
                $service_item[-6] = 'Practipago';
                $service_item[-7] = 'Infonet';
                $service_item[-8] = 'Toval';
                $service_item[-10] = 'Netel Trex';
                $service_item[-11] = 'Bancard Ventas QR';
                foreach ($services  as $service) {
                    if ($service->id <> 100) {
                        if ($service->id <> 27 && $service->id <> -1 && $service->WebServiceProvider->name <> 'Toval - Integraciones' && $service->WebServiceProvider->name <> 'Momo - Ken') {
                            $service_item[$service->id] = $service->WebServiceProvider->name . ' - ' . $service->description;
                            $services_data = $service_item;
                        }
                    }
                }
            }

            $resultset = array(
                'target'        => 'Tickets de devolucion',
                'groups'        => $groups,
                'owners'        => $owners,
                'branches'      => $branches,
                'pos'           => $pos,
                'status'        => $status,
                'services_data' => $services_data,
                'group_id'      => 0,
                'owner_id'      => 0,
                'branch_id'     => 0,
                'pos_id'        => 0,
                'status_set'    => 0,
                'service_id'    => 0,
                'service_request_id'    => '',
                'user_id'      => 0,
            );

            return $resultset;
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de reportes" . $e);
            return false;
        }
    }

    public function transactionsVueltoSearch()
    {
        try {
            $input = $this->input;
            $where = "transactions.transaction_type in (1,7,12,13) AND ";
            /*Busqueda minusiosa*/
            if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
                $where .= "transactions.id = {$input['context']} OR ";
                $where .= "transactions.referencia_numero_1 = '{$input['context']}' AND ";
            } else {


                /*SET DATE RANGE*/
                $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                $where .= "transactions.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' AND ";

                /*SET OWNER*/
                if (isset($input['group_id'])) {
                    $where .= ($input['group_id'] <> 0) ? "branches.group_id = " . $input['group_id'] . " AND " : "";
                }

                if (!$this->user->hasAccess('superuser')) {
                    if (($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11)) {
                        $where .= "transactions.owner_id = " . $this->user->owner_id . " AND ";
                    } else {
                        if (isset($input['owner_id'])) {
                            $where .= ($input['owner_id'] <> 0) ? "transactions.owner_id = " . $input['owner_id'] . " AND " : "";
                        }
                    }
                    //USER GET BRANCH    

                    if ($this->user->branch_id <> null) {

                        $input['branch_id'] = $this->user->branch_id;
                    }
                } else {
                    if (isset($input['owner_id'])) {
                        $where .= ($input['owner_id'] <> 0) ? "transactions.owner_id = " . $input['owner_id'] . " AND " : "";
                    }
                }

                if (isset($input['branch_id'])) {
                    $where .= ($input['branch_id'] <> 0) ? "points_of_sale.branch_id = " . $input['branch_id'] . " AND " : "";
                }

                if (isset($input['pos_id'])) {

                    $where .= ($input['pos_id'] <> 0) ? "points_of_sale.id = " . $input['pos_id'] . " AND " : "";
                }
                $where .= ($input['status_id'] <> "0") ? "transactions.status =  '{$input['status_id']}' AND " : "";

                if (\Sentinel::getUser()->inRole('mini_terminal')) {
                    //obtener los branches asociados a un usuario
                    $branches = \DB::table('points_of_sale')
                        ->select('branch_id')
                        ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                        ->where('branches.user_id', '=', $this->user->id)
                        ->first();

                    $where .= ' points_of_sale.branch_id = ' . $branches->branch_id . ' AND ';
                }
                if (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                    $supervisor = \DB::table('users_x_groups')->where('user_id', $this->user->id)->first();
                    $branches = \DB::table('branches')
                        ->select(['branches.description', 'users.username', 'users.id'])
                        ->join('users', 'branches.user_id', '=', 'users.id')
                        ->join('role_users', 'users.id', '=', 'role_users.user_id')
                        ->where('role_users.role_id', 22)
                        ->where('branches.group_id', $supervisor->group_id)
                        ->get();
                    $atms = \DB::table('branches')
                        ->select(['points_of_sale.atm_id'])
                        ->join('users', 'branches.user_id', '=', 'users.id')
                        ->join('role_users', 'users.id', '=', 'role_users.user_id')
                        ->join('points_of_sale', 'branches.id', '=', 'points_of_sale.branch_id')
                        ->where('role_users.role_id', 22)
                        ->where('branches.group_id', $supervisor->group_id)
                        ->whereNotNull('atm_id')
                        ->where(function ($query) use ($input) {
                            if (!empty($input['user_id'])) {
                                $query->where('branches.user_id', $input['user_id']);
                            }
                        })
                        ->pluck('atm_id', 'atm_id');

                    $atm_id = '(';
                    foreach ($atms as $id_atm => $atm) {
                        $atm_id .= $atm . ', ';
                    }

                    $atm_id = rtrim($atm_id, ', ');
                    $atm_id .= ')';

                    //$where .= ' branches.user_id = '.$input['user_id'];
                    $where .= 'atms.id in ' . $atm_id;
                }

                if ($input['service_id'] == -2) {
                    $where .= 'transactions.service_source_id = 1';
                    if ($input['service_request_id'] <> '0') {
                        $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                    }
                } elseif ($input['service_id'] == -4) {
                    $where .= 'transactions.service_source_id = 4';
                    if ($input['service_request_id'] <> '0') {
                        $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                    }
                } elseif ($input['service_id'] == -6) {
                    $where .= 'transactions.service_source_id = 6';
                    if ($input['service_request_id'] <> '0') {
                        $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                    }
                } elseif ($input['service_id'] == -7) {
                    $where .= 'transactions.service_source_id = 7';
                    if ($input['service_request_id'] <> '0') {
                        $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                    }
                } elseif ($input['service_id'] == -8) {
                    $where .= 'transactions.service_source_id = 8';
                    if ($input['service_request_id'] <> '0') {
                        $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                    }
                } elseif ($input['service_id'] == -10) {
                    $where .= 'transactions.service_source_id = 10';
                    if ($input['service_request_id'] <> '0') {
                        $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                    }
                } elseif ($this->user->hasAccess('ticketea') && !$this->user->hasAccess('superuser')) {
                    $where .= 'transactions.service_id = 28';
                } else {
                    $where .= ($input['service_id'] <> 0) ? "transactions.service_id = " . $input['service_id'] . " AND service_source_id = 0" : "";
                }
            }

            $where = trim($where);
            $where = trim($where, 'AND');
            $where = trim($where);

            $transactions = \DB::connection('eglobalt_pro')->table('transactions')
                ->select(\DB::raw('transactions.id,transactions.amount, transactions.service_id, transactions.atm_transaction_id,transactions.service_source_id,transactions.identificador_transaction_id,transactions.factura_numero,service_providers.name as provider,service_provider_products.description as servicio,transactions.created_at, transactions.status,transactions.status as estado, transactions.status_description, payments.valor_a_pagar,payments.tipo_pago as forma_pago, payments.valor_recibido,valor_entregado,points_of_sale.description as sede,referencia_numero_1,referencia_numero_2, atms.code as code, payments.id as cod_pago,transaction_tickets.reprinted, payments.valor_recibido-transactions.amount as vuelto'))
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'transactions.atm_id')
                ->join('atms', 'atms.id', '=', 'transactions.atm_id')
                ->leftjoin('service_provider_products', 'service_provider_products.id', '=', 'transactions.service_id')
                ->leftjoin('service_providers', 'service_providers.id', '=', 'service_provider_products.service_provider_id')
                ->leftjoin('transaction_tickets', 'transaction_tickets.transaction_id', '=', 'transactions.id')
                ->leftjoin('transactions_x_payments', 'transactions.id', '=', 'transactions_x_payments.transactions_id')
                ->leftjoin('payments', 'payments.id', '=', 'transactions_x_payments.payments_id')
                ->leftjoin('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->whereRaw("$where")
                ->orderBy('cod_pago', 'desc')
                ->orderBy('transactions.created_at', 'desc')
                ->whereRaw("(((payments.valor_recibido-payments.valor_entregado) <> payments.valor_a_pagar and transactions.status not in ('error','canceled') and (payments.valor_recibido-payments.valor_entregado) > 0) or ((payments.valor_recibido-payments.valor_entregado) <> 0 and transactions.status in ('error','canceled')))")
                ->paginate(20);

            $total_transactions = \DB::connection('eglobalt_pro')->table('transactions')
                ->select(\DB::raw("sum(abs(transactions.amount)) as monto,
            sum(	case when status in ('error', 'canceled', 'error dispositivo')
                        then  abs(payments.valor_recibido - payments.valor_entregado)
                    else
                        case when (payments.valor_recibido - payments.valor_a_pagar) < payments.valor_entregado
                            then ((payments.valor_recibido - payments.valor_a_pagar - payments.valor_entregado) * (-1))
                            else
                            (payments.valor_recibido - payments.valor_a_pagar - payments.valor_entregado)
                            end
                    end
            ) as vuelto_no_entregado
            "))
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'transactions.atm_id')
                ->join('atms', 'atms.id', '=', 'transactions.atm_id')
                ->leftjoin('transactions_x_payments', 'transactions.id', '=', 'transactions_x_payments.transactions_id')
                ->leftjoin('payments', 'payments.id', '=', 'transactions_x_payments.payments_id')
                ->leftjoin('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->whereRaw("$where")
                ->whereRaw("(((payments.valor_recibido-payments.valor_entregado) <> payments.valor_a_pagar and transactions.status not in ('error','canceled') and (payments.valor_recibido-payments.valor_entregado) > 0) or ((payments.valor_recibido-payments.valor_entregado) <> 0 and transactions.status in ('error','canceled')))")
                ->get();

            foreach ($transactions as &$transaction) {
                //configurar labels de estado
                if ($transaction->status == 'success') {
                    $transaction->status_label =  '<span class="label label-success">' . $transaction->status . '</span>';
                } elseif ($transaction->status == 'canceled' || $transaction->status == 'iniciated') {
                    $transaction->status_label =  '<span class="label label-warning">' . $transaction->status . '</span>';
                } elseif ($transaction->status == 'inconsistency') {
                    $transaction->status =  '<span class="label label-danger">' . 'Inconsistencia' . '</span>';
                } else {
                    $transaction->status_label =  '<span class="label label-danger">' . $transaction->status . '</span>';
                }

                if ($transaction->service_source_id <> 0) {
                    $serv_provider = \DB::table('services_providers_sources')->where('id', $transaction->service_source_id)->first();
                    $transaction->provider = $serv_provider->description;
                    $service_data = \DB::table('services_ondanet_pairing')->where('service_request_id', $transaction->service_id)->where('service_source_id', $transaction->service_source_id)->first();
                    $transaction->servicio = isset($service_data->service_description) ? $service_data->service_description : '';
                }

                if (in_array($transaction->status, ['error', 'canceled', 'error dispositivo'])) {
                    $transaction->vuelto_no_entregado = abs($transaction->valor_recibido - $transaction->valor_entregado);
                } else {
                    $vuelto_esperado = $transaction->valor_recibido - $transaction->valor_a_pagar;
                    $transaction->vuelto_no_entregado = $transaction->valor_recibido - $transaction->valor_a_pagar - $transaction->valor_entregado;
                    if ($vuelto_esperado < $transaction->valor_entregado) {
                        $transaction->vuelto_no_entregado = $transaction->vuelto_no_entregado * (-1);
                    }
                }
            }

            /*Carga datos del formulario*/
            $whereGroup = "";
            $whereOwner = "";
            $whereBranch = "";
            $wherePos = "atm_id is not null";

            if (!\Sentinel::getUser()->inRole('mini_terminal') && !\Sentinel::getUser()->inRole('supervisor_miniterminal')) {

                $branches   = Branch::orderBy('description')->where(function ($query) use ($whereBranch) {
                    if (!empty($whereBranch)) {
                        $query->whereRaw($whereBranch);
                    }
                })->get()->pluck('description', 'id');
                $branches->prepend('Todos', '0');
            } else if (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {

                if ($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11) {
                    $whereOwner = "owners.id = " . $this->user->owner_id;
                    $whereBranch = "branches.owner_id = " . $this->user->owner_id;
                    $wherePos = "points_of_sale.owner_id = " . $this->user->owner_id;

                    $supervisor = \DB::table('users_x_groups')->where('user_id', $this->user->id)->first();

                    $branchess = \DB::table('branches')
                        ->select(['branches.description', 'users.username', 'users.id'])
                        ->join('users', 'branches.user_id', '=', 'users.id')
                        ->join('role_users', 'users.id', '=', 'role_users.user_id')
                        ->where('role_users.role_id', 22)
                        ->where('branches.group_id', $supervisor->group_id)
                        ->get();
                    $branches = [];
                    foreach ($branchess as $key => $branch) {
                        $branches[$branch->id] = $branch->description . ' | ' . $branch->username;
                    }
                }
            } else {
                $branches = \DB::table('branches')
                    ->select(['branches.description', 'users.username', 'users.id'])
                    ->join('users', 'branches.user_id', '=', 'users.id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->where('branches.user_id', $this->user->id)
                    ->get();
            }
            //Redes
            $groups     = Group::orderBy('business_groups.description')->where(function ($query) use ($whereGroup) {
                if (!empty($whereGroup)) {
                    $query->whereRaw($whereGroup);
                }
            })->get()->pluck('description', 'id');
            $groups->prepend('Todos', '0');

            $owners     = Owner::orderBy('owners.name')->where(function ($query) use ($whereOwner) {
                if (!empty($whereOwner)) {
                    $query->whereRaw($whereOwner);
                }
            })->get()->pluck('name', 'id');
            $owners->prepend('Todos', '0');
            $branches   = Branch::orderBy('description')->where(function ($query) use ($whereBranch) {
                if (!empty($whereBranch)) {
                    $query->whereRaw($whereBranch);
                }
            })->get()->pluck('description', 'id');
            $branches->prepend('Todos', '0');
            $pdvs       = Pos::orderBy('description')->where(function ($query) use ($wherePos) {
                if (!empty($wherePos)) {
                    $query->whereRaw($wherePos);
                }
            })->with('Atm')->get();
            $pos = [];
            $item = array();
            $item[0] = 'Todos';
            foreach ($pdvs  as $pdv) {
                $item[$pdv->id] = $pdv->description . ' - ' . $pdv->Atm->code;
                $pos = $item;
            }

            $status = array('0' => 'Todos', 'success' => 'Aprobado', 'canceled' => 'Cancelado', 'error' => 'Error', 'rollback' => 'Reversado', 'iniciated' => 'Iniciado', 'error dispositivo' => 'Error de dispositivo', 'devolucion' => 'Devolución', 'inconsistency' => 'Inconsistencia');

            $services   = ServiceProviderProduct::with('WebServiceProvider')->orderBy('service_provider_id', 'DESC')->get();
            if ($this->user->hasAccess('ticketea') && !$this->user->hasAccess('superuser')) {
                $service_item[28] = 'Ticketea - Venta de tickets';
                $services_data = $service_item;
            } else {
                $service_item[0] = 'Todos';
                $service_item[-2] = 'Netel';
                $service_item[-4] = 'Pronet';
                $service_item[-6] = 'Practipago';
                $service_item[-7] = 'Infonet';
                $service_item[-8] = 'Toval';
                $service_item[-10] = 'Netel Trex';
                $service_item[-11] = 'Bancard Ventas QR';
                foreach ($services  as $service) {
                    if ($service->id <> 100) {
                        if ($service->id <> 27 && $service->id <> -1 && $service->WebServiceProvider->name <> 'Toval - Integraciones' && $service->WebServiceProvider->name <> 'Momo - Ken') {
                            $service_item[$service->id] = $service->WebServiceProvider->name . ' - ' . $service->description;
                            $services_data = $service_item;
                        }
                    }
                }
            }

            $resultset = array(
                'target'        => 'Tickets de devolucion',
                'groups'        => $groups,
                'owners'        => $owners,
                'branches'      => $branches,
                'pos'           => $pos,
                'status'        => $status,
                'services_data' => $services_data,
                'transactions'  => $transactions,
                'total_transactions'  => $total_transactions,
                'group_id'      => (isset($input['group_id']) ? $input['group_id'] : 0),
                'owner_id'      => (isset($input['owner_id']) ? $input['owner_id'] : 0),
                'branch_id'     => (isset($input['branch_id']) ? $input['branch_id'] : 0),
                'pos_id'        => (isset($input['pos_id']) ? $input['pos_id'] : 0),
                'status_set'    => (isset($input['status_id']) ? $input['status_id'] : 0),
                'service_id'    => (isset($input['service_id']) ? $input['service_id'] : 0),
                'reservationtime' => (isset($input['reservationtime']) ? $input['reservationtime'] : 0),
                'i'             =>  1,
                'service_request_id' => (isset($input['service_request_id']) ? $input['service_request_id'] : 0),
            );
            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    public function transactionsVueltoSearchExport()
    {
        try {
            $input = $this->input;
            $where = "transactions.transaction_type in (1,7,12,13) AND ";
            /*Busqueda minusiosa*/
            if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
                $where .= "transactions.request_data like '%{$input['context']}%'";
            } else {
                /*SET DATE RANGE*/
                $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                $where .= "transactions.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' AND ";

                /*SET OWNER*/
                if (isset($input['group_id'])) {
                    $where .= ($input['group_id'] <> 0) ? "branches.group_id = " . $input['group_id'] . " AND " : "";
                }

                if (!$this->user->hasAccess('superuser')) {
                    if (($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11)) {
                        $where .= "transactions.owner_id = " . $this->user->owner_id . " AND ";
                    } else {
                        if (isset($input['owner_id'])) {
                            $where .= ($input['owner_id'] <> 0) ? "transactions.owner_id = " . $input['owner_id'] . " AND " : "";
                        }
                    }
                    //USER GET BRANCH    

                    if ($this->user->branch_id <> null) {

                        $input['branch_id'] = $this->user->branch_id;
                    }
                } else {
                    if (isset($input['owner_id'])) {
                        $where .= ($input['owner_id'] <> 0) ? "transactions.owner_id = " . $input['owner_id'] . " AND " : "";
                    }
                }

                if (isset($input['branch_id'])) {
                    $where .= ($input['branch_id'] <> 0) ? "points_of_sale.branch_id = " . $input['branch_id'] . " AND " : "";
                }

                if (isset($input['pos_id'])) {

                    $where .= ($input['pos_id'] <> 0) ? "points_of_sale.id = " . $input['pos_id'] . " AND " : "";
                }
                $where .= ($input['status_id'] <> "0") ? "transactions.status =  '{$input['status_id']}' AND " : "";

                if (\Sentinel::getUser()->inRole('mini_terminal')) {
                    //obtener los branches asociados a un usuario
                    $branches = \DB::table('points_of_sale')
                        ->select('branch_id')
                        ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                        ->where('branches.user_id', '=', $this->user->id)
                        ->first();

                    $where .= ' points_of_sale.branch_id = ' . $branches->branch_id . ' AND ';
                }
                if (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                    $supervisor = \DB::table('users_x_groups')->where('user_id', $this->user->id)->first();
                    $branches = \DB::table('branches')
                        ->select(['branches.description', 'users.username', 'users.id'])
                        ->join('users', 'branches.user_id', '=', 'users.id')
                        ->join('role_users', 'users.id', '=', 'role_users.user_id')
                        ->where('role_users.role_id', 22)
                        ->where('branches.group_id', $supervisor->group_id)
                        ->get();
                    $atms = \DB::table('branches')
                        ->select(['points_of_sale.atm_id'])
                        ->join('users', 'branches.user_id', '=', 'users.id')
                        ->join('role_users', 'users.id', '=', 'role_users.user_id')
                        ->join('points_of_sale', 'branches.id', '=', 'points_of_sale.branch_id')
                        ->where('role_users.role_id', 22)
                        ->where('branches.group_id', $supervisor->group_id)
                        ->whereNotNull('atm_id')
                        ->where(function ($query) use ($input) {
                            if (!empty($input['user_id'])) {
                                $query->where('branches.user_id', $input['user_id']);
                            }
                        })
                        ->pluck('atm_id', 'atm_id');

                    $atm_id = '(';
                    foreach ($atms as $id_atm => $atm) {
                        $atm_id .= $atm . ', ';
                    }

                    $atm_id = rtrim($atm_id, ', ');
                    $atm_id .= ')';

                    //$where .= ' branches.user_id = '.$input['user_id'];
                    $where .= 'atms.id in ' . $atm_id;
                }

                if ($input['service_id'] == -2) {
                    $where .= 'transactions.service_source_id = 1';
                    if ($input['service_request_id'] <> '0') {
                        $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                    }
                } elseif ($input['service_id'] == -4) {
                    $where .= 'transactions.service_source_id = 4';
                    if ($input['service_request_id'] <> '0') {
                        $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                    }
                } elseif ($input['service_id'] == -6) {
                    $where .= 'transactions.service_source_id = 6';
                    if ($input['service_request_id'] <> '0') {
                        $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                    }
                } elseif ($input['service_id'] == -7) {
                    $where .= 'transactions.service_source_id = 7';
                    if ($input['service_request_id'] <> '0') {
                        $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                    }
                } elseif ($input['service_id'] == -8) {
                    $where .= 'transactions.service_source_id = 8';
                    if ($input['service_request_id'] <> '0') {
                        $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                    }
                } elseif ($input['service_id'] == -10) {
                    $where .= 'transactions.service_source_id = 10';
                    if ($input['service_request_id'] <> '0') {
                        $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                    }
                } elseif ($this->user->hasAccess('ticketea') && !$this->user->hasAccess('superuser')) {
                    $where .= 'transactions.service_id = 28';
                } else {
                    $where .= ($input['service_id'] <> 0) ? "transactions.service_id = " . $input['service_id'] . " AND service_source_id = 0" : "";
                }
            }

            $where = trim($where);
            $where = trim($where, 'AND');
            $where = trim($where);

            $transactions = \DB::table('transactions')
                ->select(\DB::raw('transactions.id,transactions.service_id, service_providers.name as proveedor, service_provider_products.description as tipo, transactions.status as estado, transactions.status_description as estado_descripcion, transactions.created_at as fecha,transactions.created_at as hora, transactions.amount as valor_transaccion, payments.id as cod_pago,payments.tipo_pago as forma_pago, transactions.identificador_transaction_id as identificador_transaccion, points_of_sale.description as sede, atms.code as codigo_cajero,  payments.valor_a_pagar, payments.valor_recibido, payments.valor_entregado, transactions.service_source_id, (payments.valor_recibido - payments.valor_a_pagar) - payments.valor_entregado as no_entregado'))
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'transactions.atm_id')
                ->join('atms', 'atms.id', '=', 'transactions.atm_id')
                ->leftjoin('service_provider_products', 'service_provider_products.id', '=', 'transactions.service_id')
                ->leftjoin('service_providers', 'service_providers.id', '=', 'service_provider_products.service_provider_id')
                ->leftjoin('transaction_tickets', 'transaction_tickets.transaction_id', '=', 'transactions.id')
                ->leftjoin('transactions_x_payments', 'transactions.id', '=', 'transactions_x_payments.transactions_id')
                ->leftjoin('payments', 'payments.id', '=', 'transactions_x_payments.payments_id')
                ->leftjoin('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->whereRaw("$where")
                ->orderBy('cod_pago', 'desc')
                ->orderBy('transactions.created_at', 'desc')
                ->whereRaw("(((payments.valor_recibido-payments.valor_entregado) <> payments.valor_a_pagar and transactions.status not in ('error','canceled') and (payments.valor_recibido-payments.valor_entregado) > 0) or ((payments.valor_recibido-payments.valor_entregado) <> 0 and transactions.status in ('error','canceled')))")
                ->get();

            foreach ($transactions as $transaction) {

                if ($transaction->service_source_id <> 0) {
                    $serv_provider = \DB::table('services_providers_sources')->where('id', $transaction->service_source_id)->first();
                    $transaction->proveedor = $serv_provider->description;
                    $service_data = \DB::table('services_ondanet_pairing')->where('service_request_id', $transaction->service_id)->where('service_source_id', $transaction->service_source_id)->first();
                    //$transaction->proveedor .= isset($service_data->service_description)?$service_data->service_description:'';
                    $transaction->tipo  = isset($service_data->service_description) ? $service_data->service_description : '';
                }
                if ($transaction->valor_transaccion < 0) {
                    //Monto transaccion de efectifizacion es negativo, para el reporte exportado se convierte a positivo
                    $transaction->valor_transaccion = $transaction->valor_transaccion * -1;
                }

                /*if($transaction->owner_id == 2){
                    $transaction->owner_id = 'Antell';
                }
                if($transaction->owner_id == 11){
                    $transaction->owner_id = 'Eglobal';
                }*/

                $transaction->fecha = date('d-m-Y', strtotime($transaction->fecha));
                $transaction->hora = date('H:i:s', strtotime($transaction->hora));

                $transaction->valor_transaccion = number_format($transaction->valor_transaccion, 0, '', '');

                unset($transaction->service_source_id);
                unset($transaction->service_id);
            }

            return $transactions;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    /*TRANSATIONS*/
    public function transactionsAtmReports()
    {
        try {

            //Redes
            $whereGroup = "";
            $whereOwner = "";
            $whereBranch = "";
            $wherePos = "";
            if (!$this->user->hasAccess('superuser')) {
                if ($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11) {
                    $whereOwner = "owners.id = " . $this->user->owner_id;
                    $whereBranch = "branches.owner_id = " . $this->user->owner_id;
                    $wherePos = "points_of_sale.owner_id = " . $this->user->owner_id;
                }
            }
            //Redes
            $groups     = Group::orderBy('business_groups.description')->where(function ($query) use ($whereGroup) {
                if (!empty($whereGroup)) {
                    $query->whereRaw($whereGroup);
                }
            })->get()->pluck('description', 'id');
            $groups->prepend('Todos', '0');

            $owners     = Owner::orderBy('owners.name')->where(function ($query) use ($whereOwner) {
                if (!empty($whereOwner)) {
                    $query->whereRaw($whereOwner);
                }
            })->get()->pluck('name', 'id');
            $owners->prepend('Todos', '0');
            $branches   = Branch::orderBy('description')->where(function ($query) use ($whereBranch) {
                if (!empty($whereBranch)) {
                    $query->whereRaw($whereBranch);
                }
            })->get()->pluck('description', 'id');
            $branches->prepend('Todos', '0');
            $pdvs       = Pos::orderBy('description')->where(function ($query) use ($wherePos) {
                if (!empty($wherePos)) {
                    $query->whereRaw($wherePos);
                }
            })->with('Atm')->get();
            $pos = [];
            $item = array();
            $item[0] = 'Todos';

            foreach ($pdvs  as $pdv) {
                $item[$pdv->id] = $pdv->description . ' - ' . $pdv->Atm->code;
                $pos = $item;
            }
            $status = array('0' => 'Todos', 'success' => 'Aprobado', 'canceled' => 'Cancelado', 'error' => 'Error', 'rollback' => 'Reversado', 'iniciated' => 'Iniciado', 'error dispositivo' => 'Error de dispositivo', 'error dispositivo' => 'Error de dispositivo', 'devolucion' => 'Devolución', 'inconsistency' => 'Inconsistencia');
            $atmType = array('0' => 'Todos', 'da' => 'App Billetaje', 'ws' => 'Web Service', 'at' => 'Atm');
            $services   = ServiceProviderProduct::with('WebServiceProvider')->orderBy('service_provider_id', 'DESC')->get();
            $services_data = [];
            $service_item = array();

            if ($this->user->hasAccess('ticketea') && !$this->user->hasAccess('superuser')) {
                $service_item[28] = 'Ticketea - Venta de tickets';
                $services_data = $service_item;
            } else {
                $service_item[-2] = 'Netel';
                $service_item[-4] = 'Pronet';
                $service_item[-6] = 'Practipago';
                $service_item[-7] = 'Infonet';
                $service_item[-8] = 'Toval';
                $service_item[-10] = 'Netel Trex';
                $service_item[-11] = 'Bancard Ventas QR';
                foreach ($services  as $service) {
                    if ($service->id <> 100) {
                        if ($service->id <> 27 && $service->id <> -1 && $service->WebServiceProvider->name <> 'Toval - Integraciones' && $service->WebServiceProvider->name <> 'Momo - Ken') {
                            $service_item[$service->id] = $service->WebServiceProvider->name . ' - ' . $service->description;
                            $services_data = $service_item;
                        }
                    }
                }
            }

            $servicesRequestData = [];

            $checkbox = false;
            $checkbox2 = false;

            $resultset = array(
                'target'        => 'Transacciones por ATM',
                'groups'        => $groups,
                'owners'        => $owners,
                'branches'      => $branches,
                'pos'           => $pos,
                'status'        => $status,
                'type'          => $atmType,
                'services_data' => $services_data,
                'group_id'      => 0,
                'owner_id'      => 0,
                'branch_id'     => 0,
                'pos_id'        => 0,
                'status_set'    => 0,
                'type_set'    => 0,
                'service_id'    => 0,
                'checkbox'      => $checkbox,
                'checkbox2'     => $checkbox2,
                'services_request_data' => $servicesRequestData,
                'service_request_id'    => '',
            );

            return $resultset;
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de reportes" . $e);
            return false;
        }
    }

    public function transactionsAtmSearch()
    {
        try {
            $input = $this->input;
            $where = "transactions.transaction_type in (1,7,12,13) AND ";
            /*Busqueda minusiosa*/
            if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
                $where .= "transactions.id = {$input['context']} OR ";
                $where .= "transactions.referencia_numero_1 = '{$input['context']}' AND ";
            } else {


                /*SET DATE RANGE*/
                $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                $where .= "transactions.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' AND ";

                /*SET OWNER*/
                $where .= ($input['group_id'] <> 0) ? "branches.group_id = " . $input['group_id'] . " AND " : "";

                if (!$this->user->hasAccess('superuser')) {
                    if (($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11)) {
                        $where .= "transactions.owner_id = " . $this->user->owner_id . " AND ";
                    } else {
                        $where .= ($input['owner_id'] <> 0) ? "transactions.owner_id = " . $input['owner_id'] . " AND " : "";
                    }
                } else {
                    $where .= ($input['owner_id'] <> 0) ? "transactions.owner_id = " . $input['owner_id'] . " AND " : "";
                }

                $where .= ($input['branch_id'] <> 0) ? "points_of_sale.branch_id = " . $input['branch_id'] . " AND " : "";
                $where .= ($input['pos_id'] <> 0) ? "points_of_sale.id = " . $input['pos_id'] . " AND " : "";
                $where .= ($input['status_id'] <> "0") ? "transactions.status =  '{$input['status_id']}' AND " : "";
                $where .= ($input['type'] <> "0") ? "atms.type =  '{$input['type']}' AND " : "";
            }

            $where = trim($where);
            $where = trim($where, 'AND');
            $where = trim($where);

            $serviciosEfectivizacion = 'array[8]';
            $serviciosOmitidos = 'array[8, 20, 43, 12, 40, 39, 21]';
            $transactions = \DB::connection('eglobalt_replica')->table('transactions')
                ->selectRaw("
                    atms.name,
                    COALESCE(sum(
                        CASE
                        WHEN service_id = ALL(" . $serviciosEfectivizacion . ") THEN
                            abs(amount)
                        END
                      ),0) as efectivizacion,
                    COALESCE(sum(
                        CASE
                        WHEN service_id <> ALL(" . $serviciosOmitidos . ") THEN
                            abs(amount)
                        END
                      ),0) as ingreso
                ")
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'transactions.atm_id')
                ->join('atms', 'atms.id', '=', 'transactions.atm_id')
                ->leftjoin('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->whereRaw("$where")
                ->groupBy('atms.name')
                ->get();

            $chart_data = [];
            $chart_data[] = ['Atm', 'Efectivización', 'Ingreso'];
            foreach ($transactions as $key => $value) {
                $chart_data[] = [
                    $value->name,
                    $value->efectivizacion,
                    $value->ingreso,
                ];
            }

            if (empty($transactions)) {
                $chart_data[] = ['', 0, 0];
            }

            /*Carga datos del formulario*/
            $whereGroup = "";
            $whereOwner = "";
            $whereBranch = "";
            $wherePos = "";
            if (!$this->user->hasAccess('superuser')) {
                if ($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11) {
                    $whereOwner = "owners.id = " . $this->user->owner_id;
                    $whereBranch = "branches.owner_id = " . $this->user->owner_id;
                    $wherePos = "points_of_sale.owner_id = " . $this->user->owner_id;
                }
            }
            //Redes
            $groups     = Group::orderBy('business_groups.description')->where(function ($query) use ($whereGroup) {
                if (!empty($whereGroup)) {
                    $query->whereRaw($whereGroup);
                }
            })->get()->pluck('description', 'id');
            $groups->prepend('Todos', '0');

            $owners     = Owner::orderBy('owners.name')->where(function ($query) use ($whereOwner) {
                if (!empty($whereOwner)) {
                    $query->whereRaw($whereOwner);
                }
            })->get()->pluck('name', 'id');
            $owners->prepend('Todos', '0');
            $branches   = Branch::orderBy('description')->where(function ($query) use ($whereBranch) {
                if (!empty($whereBranch)) {
                    $query->whereRaw($whereBranch);
                }
            })->get()->pluck('description', 'id');
            $branches->prepend('Todos', '0');
            $pdvs       = Pos::orderBy('description')->where(function ($query) use ($wherePos) {
                if (!empty($wherePos)) {
                    $query->whereRaw($wherePos);
                }
            })->with('Atm')->get();
            $pos = [];
            $item = array();
            $item[0] = 'Todos';
            foreach ($pdvs  as $pdv) {
                $item[$pdv->id] = $pdv->description . ' - ' . $pdv->Atm->code;
                $pos = $item;
            }

            $status = array('0' => 'Todos', 'success' => 'Aprobado', 'canceled' => 'Cancelado', 'error' => 'Error', 'rollback' => 'Reversado', 'iniciated' => 'Iniciado', 'error dispositivo' => 'Error de dispositivo', 'devolucion' => 'Devolución', 'inconsistency' => 'Inconsistencia');
            $atmType = array('0' => 'Todos', 'da' => 'App Billetaje', 'ws' => 'Web Service', 'at' => 'Atm');

            $resultset = array(
                'target'        => 'Transacciones por ATM',
                'groups'        => $groups,
                'owners'        => $owners,
                'branches'      => $branches,
                'pos'           => $pos,
                'status'        => $status,
                'type'          => $atmType,
                'transactions'  => (array)$transactions,
                'chart_data'    => json_encode($chart_data, JSON_NUMERIC_CHECK),
                'group_id'      => (isset($input['group_id']) ? $input['group_id'] : 0),
                'owner_id'      => (isset($input['owner_id']) ? $input['owner_id'] : 0),
                'branch_id'     => (isset($input['branch_id']) ? $input['branch_id'] : 0),
                'pos_id'        => (isset($input['pos_id']) ? $input['pos_id'] : 0),
                'status_set'    => (isset($input['status_id']) ? $input['status_id'] : 0),
                'type_set'    => (isset($input['type']) ? $input['type'] : 0),
                'reservationtime' => (isset($input['reservationtime']) ? $input['reservationtime'] : 0),
                'i'             =>  1,
                'checkbox'    => (isset($input['checkbox']) ? $input['checkbox'] : false),
                'checkbox2'    => (isset($input['checkbox2']) ? $input['checkbox2'] : false),
                'service_request_id' => (isset($input['service_request_id']) ? $input['service_request_id'] : 0),
            );


            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    /*TRANSATIONS*/
    public function denominacionesAmountReports()
    {
        try {
            //Redes
            $whereGroup = "";
            $whereOwner = "";
            $whereBranch = "";
            $wherePos = "";
            if (!$this->user->hasAccess('superuser')) {
                if ($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11) {
                    $whereOwner = "owners.id = " . $this->user->owner_id;
                    $whereBranch = "branches.owner_id = " . $this->user->owner_id;
                    $wherePos = "points_of_sale.owner_id = " . $this->user->owner_id;
                }
            }
            //Redes
            $groups     = Group::orderBy('business_groups.description')->where(function ($query) use ($whereGroup) {
                if (!empty($whereGroup)) {
                    $query->whereRaw($whereGroup);
                }
            })->get()->pluck('description', 'id');
            $groups->prepend('Todos', '0');

            $owners     = Owner::orderBy('owners.name')->where(function ($query) use ($whereOwner) {
                if (!empty($whereOwner)) {
                    $query->whereRaw($whereOwner);
                }
            })->get()->pluck('name', 'id');
            $owners->prepend('Todos', '0');
            $branches   = Branch::orderBy('description')->where(function ($query) use ($whereBranch) {
                if (!empty($whereBranch)) {
                    $query->whereRaw($whereBranch);
                }
            })->get()->pluck('description', 'id');
            $branches->prepend('Todos', '0');
            $pdvs       = Pos::orderBy('description')->where(function ($query) use ($wherePos) {
                if (!empty($wherePos)) {
                    $query->whereRaw($wherePos);
                }
            })->with('Atm')->get();
            $pos = [];
            $item = array();
            $item[0] = 'Todos';

            foreach ($pdvs  as $pdv) {
                $item[$pdv->id] = $pdv->description . ' - ' . $pdv->Atm->code;
                $pos = $item;
            }
            $status = array('0' => 'Todos', 'success' => 'Aprobado', 'canceled' => 'Cancelado', 'error' => 'Error', 'rollback' => 'Reversado', 'iniciated' => 'Iniciado', 'error dispositivo' => 'Error de dispositivo', 'error dispositivo' => 'Error de dispositivo', 'devolucion' => 'Devolución', 'inconsistency' => 'Inconsistencia');
            $services   = ServiceProviderProduct::with('WebServiceProvider')->orderBy('service_provider_id', 'DESC')->get();
            $services_data = [];
            $service_item = array();

            if ($this->user->hasAccess('ticketea') && !$this->user->hasAccess('superuser')) {
                $service_item[28] = 'Ticketea - Venta de tickets';
                $services_data = $service_item;
            } else {
                $service_item[-2] = 'Netel';
                $service_item[-4] = 'Pronet';
                $service_item[-6] = 'Practipago';
                $service_item[-7] = 'Infonet';
                $service_item[-8] = 'Toval';
                $service_item[-10] = 'Netel Trex';
                $service_item[-11] = 'Bancard Ventas QR';
                foreach ($services  as $service) {
                    if ($service->id <> 100) {
                        if ($service->id <> 27 && $service->id <> -1 && $service->WebServiceProvider->name <> 'Toval - Integraciones' && $service->WebServiceProvider->name <> 'Momo - Ken') {
                            $service_item[$service->id] = $service->WebServiceProvider->name . ' - ' . $service->description;
                            $services_data = $service_item;
                        }
                    }
                }
            }

            $servicesRequestData = [];

            $checkbox = false;
            $checkbox2 = false;

            $resultset = array(
                'target'        => 'Denominaciones Utilizadas',
                'groups'        => $groups,
                'owners'        => $owners,
                'branches'      => $branches,
                'pos'           => $pos,
                'status'        => $status,
                'services_data' => $services_data,
                'group_id'      => 0,
                'owner_id'      => 0,
                'branch_id'     => 0,
                'pos_id'        => 0,
                'status_set'    => 0,
                'service_id'    => 0,
                'checkbox'      => $checkbox,
                'checkbox2'     => $checkbox2,
                'services_request_data' => $servicesRequestData,
                'service_request_id'    => '',
            );

            return $resultset;
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de reportes" . $e);
            return false;
        }
    }

    public function denominacionesAmountSearch()
    {
        try {
            $input = $this->input;
            $where = "transactions.transaction_type in (1,7,12,13) AND ";
            /*Busqueda minusiosa*/
            if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
                $where .= "transactions.id = {$input['context']} OR ";
                $where .= "transactions.referencia_numero_1 = '{$input['context']}' AND ";
            } else {


                /*SET DATE RANGE*/
                $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                $where .= "transactions.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' AND ";

                /*SET OWNER*/
                $where .= ($input['group_id'] <> 0) ? "branches.group_id = " . $input['group_id'] . " AND " : "";

                if (!$this->user->hasAccess('superuser')) {
                    if (($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11)) {
                        $where .= "transactions.owner_id = " . $this->user->owner_id . " AND ";
                    } else {
                        $where .= ($input['owner_id'] <> 0) ? "transactions.owner_id = " . $input['owner_id'] . " AND " : "";
                    }
                } else {
                    $where .= ($input['owner_id'] <> 0) ? "transactions.owner_id = " . $input['owner_id'] . " AND " : "";
                }

                $where .= ($input['branch_id'] <> 0) ? "points_of_sale.branch_id = " . $input['branch_id'] . " AND " : "";
                $where .= ($input['pos_id'] <> 0) ? "points_of_sale.id = " . $input['pos_id'] . " AND " : "";
                $where .= ($input['status_id'] <> "0") ? "transactions.status =  '{$input['status_id']}' AND " : "";
                $services_sources = [];
                $services_ids = [];
                if (isset($input['service_id'])) {
                    foreach ($input['service_id'] as $key => $service_id) {
                        if (strstr($service_id, '-')) {
                            if ($service_id == -2) {
                                $services_sources[] = 1;
                            } elseif ($service_id == -4) {
                                $services_sources[] = 4;
                            } elseif ($service_id == -6) {
                                $services_sources[] = 6;
                            } elseif ($service_id == -7) {
                                $services_sources[] = 7;
                            }
                        } else {
                            if (empty($services_ids)) {
                                $services_sources[] = 0;
                            }
                            $services_ids[] = $service_id;
                        }
                    }
                }

                if (isset($input['service_request_id'])) {
                    foreach ($input['service_request_id'] as $key => $service_request_id) {
                        $services_ids[] = $service_request_id;
                    }
                }

                if ($this->user->hasAccess('ticketea') && !$this->user->hasAccess('superuser')) {
                    if (!in_array($services_ids, 28)) {
                        $services_ids[] = 28;
                    }
                }
            }

            $where = trim($where);
            $where = trim($where, 'AND');
            $where = trim($where);
            $transactions = \DB::connection('eglobalt_replica')->table('transactions_movements')
                ->selectRaw("
                    coalesce(sum(
                        case
                        when accion = 'entrada' then
                            cantidad
                        END
                    ),0) as entrada,
                    coalesce(sum(
                        case
                        when accion = 'salida' then
                            cantidad
                        END
                    ),0) as salida,
                    valor,
                    to_char(transactions.created_at,'DD/MM/YYYY') as fecha
                ")
                ->join('transactions', 'transactions.id', '=', 'transactions_movements.transactions_id')
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'transactions.atm_id')
                ->leftjoin('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->whereRaw("$where")
                ->where(function ($query) use ($services_ids, $services_sources) {
                    if (!empty($services_ids)) {
                        $query->whereIn('service_id', $services_ids);
                    }
                    if (!empty($services_sources)) {
                        $query->whereIn('service_source_id', $services_sources);
                    }
                })
                ->groupBy("valor", "fecha")
                //->orderBy("valor", "asc")
                ->orderBy(\DB::raw("min(transactions.created_at)"))
                ->get();

            $chart_data = [];
            $fechas = [];
            $denominaciones = [];

            // Lista ordenada de denominaciones vigentes

            $denominaciones_aux = [
                '50 Gs',
                '100 Gs',
                '500 Gs',
                '1,000 Gs',
                '2,000 Gs',
                '5,000 Gs',
                '10,000 Gs',
                '20,000 Gs',
                '50,000 Gs',
                '100,000 Gs'
            ];

            $dias = [
                1 => 'Lu',
                2 => 'Ma',
                3 => 'Mi',
                4 => 'Ju',
                5 => 'Vi',
                6 => 'Sa',
                7 => 'Do',
            ];
            foreach ($transactions as $key => $value) {
                $denominacion = number_format($value->valor, 0, '.', ',') . ' Gs';
                $fecha = $value->fecha;
                $letra_dia  = date('N', strtotime(str_replace('/', '-', $fecha)));

                if (!in_array($dias[$letra_dia] . ' ' . $fecha, $fechas)) {
                    $fechas[] = $dias[$letra_dia] . ' ' . $fecha;
                }

                if (!in_array($denominacion, $denominaciones)) {
                    $denominaciones[] = $denominacion;
                    $chart_data[] = [
                        'name' => $denominacion,
                        'type' => 'line',
                        'symbolSize' => 8,
                        'hoverAnimation' => false,
                        'data' => [
                            $value->entrada
                        ]
                    ];
                    $chart_data[] = [
                        'name' => $denominacion,
                        'type' => 'line',
                        'symbolSize' => 8,
                        'hoverAnimation' => false,
                        'xAxisIndex' => 1,
                        'yAxisIndex' => 1,
                        'data' => [
                            $value->salida
                        ]
                    ];
                } else {
                    $denominacionKey = array_keys(array_column($chart_data, 'name'), $denominacion);
                    foreach ($denominacionKey as $billete => $index_billete) {
                        if ($index_billete % 2 == 0) {
                            $chart_data[$index_billete]['data'][] = $value->entrada;
                        } else {
                            $chart_data[$index_billete]['data'][] = $value->salida;
                        }
                    }
                }
            }

            /*Carga datos del formulario*/
            $whereGroup = "";
            $whereOwner = "";
            $whereBranch = "";
            $wherePos = "";
            if (!$this->user->hasAccess('superuser')) {
                if ($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11) {
                    $whereOwner = "owners.id = " . $this->user->owner_id;
                    $whereBranch = "branches.owner_id = " . $this->user->owner_id;
                    $wherePos = "points_of_sale.owner_id = " . $this->user->owner_id;
                }
            }
            //Redes
            $groups     = Group::orderBy('business_groups.description')->where(function ($query) use ($whereGroup) {
                if (!empty($whereGroup)) {
                    $query->whereRaw($whereGroup);
                }
            })->get()->pluck('description', 'id');
            $groups->prepend('Todos', '0');

            $owners     = Owner::orderBy('owners.name')->where(function ($query) use ($whereOwner) {
                if (!empty($whereOwner)) {
                    $query->whereRaw($whereOwner);
                }
            })->get()->pluck('name', 'id');
            $owners->prepend('Todos', '0');
            $branches   = Branch::orderBy('description')->where(function ($query) use ($whereBranch) {
                if (!empty($whereBranch)) {
                    $query->whereRaw($whereBranch);
                }
            })->get()->pluck('description', 'id');
            $branches->prepend('Todos', '0');
            $pdvs       = Pos::orderBy('description')->where(function ($query) use ($wherePos) {
                if (!empty($wherePos)) {
                    $query->whereRaw($wherePos);
                }
            })->with('Atm')->get();
            $pos = [];
            $item = array();
            $item[0] = 'Todos';
            foreach ($pdvs  as $pdv) {
                $item[$pdv->id] = $pdv->description . ' - ' . $pdv->Atm->code;
                $pos = $item;
            }

            $status = array('0' => 'Todos', 'success' => 'Aprobado', 'canceled' => 'Cancelado', 'error' => 'Error', 'rollback' => 'Reversado', 'iniciated' => 'Iniciado', 'error dispositivo' => 'Error de dispositivo', 'devolucion' => 'Devolución', 'inconsistency' => 'Inconsistencia');

            $services = ServiceProviderProduct::with('WebServiceProvider')->orderBy('service_provider_id', 'DESC')->get();
            if ($this->user->hasAccess('ticketea') && !$this->user->hasAccess('superuser')) {
                $service_item[28] = 'Ticketea - Venta de tickets';
                $services_data = $service_item;
            } else {
                $service_item[-2] = 'Netel';
                $service_item[-4] = 'Pronet';
                $service_item[-6] = 'Practipago';
                $service_item[-7] = 'Infonet';
                $service_item[-8] = 'Toval';
                $service_item[-10] = 'Netel Trex';
                $service_item[-11] = 'Bancard Ventas QR';
                foreach ($services  as $service) {
                    if ($service->id <> 100) {
                        if ($service->id <> 27 && $service->id <> -1 && $service->WebServiceProvider->name <> 'Toval - Integraciones' && $service->WebServiceProvider->name <> 'Momo - Ken') {
                            $service_item[$service->id] = $service->WebServiceProvider->name . ' - ' . $service->description;
                            $services_data = $service_item;
                        }
                    }
                }
            }

            if (isset($input['service_request_id'])) {
                $servicesRequestData = \DB::table('services_ondanet_pairing')
                    ->whereIn('service_request_id', $input['service_request_id'])
                    ->pluck('service_request_id', 'service_request_id');
            } else {
                $servicesRequestData = [];
            }

            $resultset = array(
                'target'        => 'Denominaciones Utilizadas',
                'groups'        => $groups,
                'owners'        => $owners,
                'branches'      => $branches,
                'pos'           => $pos,
                'status'        => $status,
                'services_data' => $services_data,
                'transactions'  => $transactions,
                'denominaciones' => json_encode($denominaciones_aux),
                'fechas'        => json_encode($fechas),
                'chart_data'    => json_encode($chart_data),
                'group_id'      => (isset($input['group_id']) ? $input['group_id'] : 0),
                'owner_id'      => (isset($input['owner_id']) ? $input['owner_id'] : 0),
                'branch_id'     => (isset($input['branch_id']) ? $input['branch_id'] : 0),
                'pos_id'        => (isset($input['pos_id']) ? $input['pos_id'] : 0),
                'status_set'    => (isset($input['status_id']) ? $input['status_id'] : 0),
                'service_id'    => (isset($input['service_id']) ? $input['service_id'] : 0),
                'reservationtime' => (isset($input['reservationtime']) ? $input['reservationtime'] : 0),
                'i'             =>  1,
                'services_request_data' => $servicesRequestData,
                'checkbox'    => (isset($input['checkbox']) ? $input['checkbox'] : false),
                'checkbox2'    => (isset($input['checkbox2']) ? $input['checkbox2'] : false),
                'service_request_id' => (isset($input['service_request_id']) ? $input['service_request_id'] : 0),
            );

            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    public function transactionsVueltoCorrectoReports()
    {
        try {

            //Redes
            $whereGroup = "";
            $whereOwner = "";
            $whereBranch = "";
            $wherePos = "";
            if (!$this->user->hasAccess('superuser')) {
                if ($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11) {
                    $whereOwner = "owners.id = " . $this->user->owner_id;
                    $whereBranch = "branches.owner_id = " . $this->user->owner_id;
                    $wherePos = "points_of_sale.owner_id = " . $this->user->owner_id;
                }
            }
            //Redes
            $groups     = Group::orderBy('business_groups.description')->where(function ($query) use ($whereGroup) {
                if (!empty($whereGroup)) {
                    $query->whereRaw($whereGroup);
                }
            })->get()->pluck('description', 'id');
            $groups->prepend('Todos', '0');

            $owners     = Owner::orderBy('owners.name')->where(function ($query) use ($whereOwner) {
                if (!empty($whereOwner)) {
                    $query->whereRaw($whereOwner);
                }
            })->get()->pluck('name', 'id');
            $owners->prepend('Todos', '0');
            $branches   = Branch::orderBy('description')->where(function ($query) use ($whereBranch) {
                if (!empty($whereBranch)) {
                    $query->whereRaw($whereBranch);
                }
            })->get()->pluck('description', 'id');
            $branches->prepend('Todos', '0');
            $pdvs       = Pos::orderBy('description')->where(function ($query) use ($wherePos) {
                if (!empty($wherePos)) {
                    $query->whereRaw($wherePos);
                }
            })->with('Atm')->get();
            $pos = [];
            $item = array();
            $item[0] = 'Todos';

            foreach ($pdvs  as $pdv) {
                $item[$pdv->id] = $pdv->description . ' - ' . $pdv->Atm->code;
                $pos = $item;
            }
            $status = array('0' => 'Todos', 'success' => 'Aprobado', 'canceled' => 'Cancelado', 'error' => 'Error', 'rollback' => 'Reversado', 'iniciated' => 'Iniciado', 'error dispositivo' => 'Error de dispositivo', 'error dispositivo' => 'Error de dispositivo', 'devolucion' => 'Devolución', 'inconsistency' => 'Inconsistencia');
            $services   = ServiceProviderProduct::with('WebServiceProvider')->orderBy('service_provider_id', 'DESC')->get();
            $services_data = [];
            $service_item = array();

            if ($this->user->hasAccess('ticketea') && !$this->user->hasAccess('superuser')) {
                $service_item[28] = 'Ticketea - Venta de tickets';
                $services_data = $service_item;
            } else {
                $service_item[0] = 'Todos';
                $service_item[-2] = 'Netel';
                $service_item[-4] = 'Pronet';
                $service_item[-6] = 'Practipago';
                $service_item[-7] = 'Infonet';
                $service_item[-8] = 'Toval';
                $service_item[-10] = 'Netel Trex';
                $service_item[-11] = 'Bancard Ventas QR';
                foreach ($services  as $service) {
                    if ($service->id <> 100) {
                        if ($service->id <> 27 && $service->id <> -1 && $service->WebServiceProvider->name <> 'Toval - Integraciones' && $service->WebServiceProvider->name <> 'Momo - Ken') {
                            $service_item[$service->id] = $service->WebServiceProvider->name . ' - ' . $service->description;
                            $services_data = $service_item;
                        }
                    }
                }
            }



            $resultset = array(
                'target'        => 'Vueltos Entregados',
                'groups'        => $groups,
                'owners'        => $owners,
                'branches'      => $branches,
                'pos'           => $pos,
                'status'        => $status,
                'services_data' => $services_data,
                'group_id'      => 0,
                'owner_id'      => 0,
                'branch_id'     => 0,
                'pos_id'        => 0,
                'status_set'    => 0,
                'service_id'    => 0,
                'service_request_id'    => '',
            );

            return $resultset;
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de reportes" . $e);
            return false;
        }
    }

    public function transactionsVueltoCorrectoSearch()
    {
        try {
            $input = $this->input;
            $where = "transactions.transaction_type in (1,7,12,13) AND ";
            /*Busqueda minusiosa*/
            if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
                $where .= "transactions.id = {$input['context']} OR ";
                $where .= "transactions.referencia_numero_1 = '{$input['context']}' AND ";
            } else {


                /*SET DATE RANGE*/
                $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                $where .= "transactions.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' AND ";

                /*SET OWNER*/
                $where .= ($input['group_id'] <> 0) ? "branches.group_id = " . $input['group_id'] . " AND " : "";

                if (!$this->user->hasAccess('superuser')) {
                    if (($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11)) {
                        $where .= "transactions.owner_id = " . $this->user->owner_id . " AND ";
                    } else {
                        $where .= ($input['owner_id'] <> 0) ? "transactions.owner_id = " . $input['owner_id'] . " AND " : "";
                    }
                } else {
                    $where .= ($input['owner_id'] <> 0) ? "transactions.owner_id = " . $input['owner_id'] . " AND " : "";
                }

                $where .= ($input['branch_id'] <> 0) ? "points_of_sale.branch_id = " . $input['branch_id'] . " AND " : "";
                $where .= ($input['pos_id'] <> 0) ? "points_of_sale.id = " . $input['pos_id'] . " AND " : "";
                $where .= ($input['status_id'] <> "0") ? "transactions.status =  '{$input['status_id']}' AND " : "";

                if ($input['service_id'] == -2) {
                    $where .= 'transactions.service_source_id = 1';
                    if ($input['service_request_id'] <> '0') {
                        $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                    }
                } elseif ($input['service_id'] == -4) {
                    $where .= 'transactions.service_source_id = 4';
                    if ($input['service_request_id'] <> '0') {
                        $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                    }
                } elseif ($input['service_id'] == -6) {
                    $where .= 'transactions.service_source_id = 6';
                    if ($input['service_request_id'] <> '0') {
                        $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                    }
                } elseif ($input['service_id'] == -7) {
                    $where .= 'transactions.service_source_id = 7';
                    if ($input['service_request_id'] <> '0') {
                        $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                    }
                } elseif ($input['service_id'] == -8) {
                    $where .= 'transactions.service_source_id = 8';
                    if ($input['service_request_id'] <> '0') {
                        $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                    }
                } elseif ($input['service_id'] == -10) {
                    $where .= 'transactions.service_source_id = 10';
                    if ($input['service_request_id'] <> '0') {
                        $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                    }
                } elseif ($this->user->hasAccess('ticketea') && !$this->user->hasAccess('superuser')) {
                    $where .= 'transactions.service_id = 28';
                } else {
                    $where .= ($input['service_id'] <> 0) ? "transactions.service_id = " . $input['service_id'] . " AND service_source_id = 0" : "";
                }
            }

            $where = trim($where);
            $where = trim($where, 'AND');
            $where = trim($where);

            $transactions = \DB::connection('eglobalt_pro')->table('transactions')
                ->select(\DB::raw('transactions.id,transactions.amount, transactions.service_id, transactions.atm_transaction_id,transactions.service_source_id,transactions.identificador_transaction_id,transactions.factura_numero,service_providers.name as provider,service_provider_products.description as servicio,transactions.created_at, transactions.status,transactions.status as estado, transactions.status_description, payments.valor_a_pagar,payments.tipo_pago as forma_pago, payments.valor_recibido,valor_entregado,points_of_sale.description as sede,referencia_numero_1,referencia_numero_2, atms.code as code, payments.id as cod_pago,transaction_tickets.reprinted, payments.valor_recibido-transactions.amount as vuelto'))
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'transactions.atm_id')
                ->join('atms', 'atms.id', '=', 'transactions.atm_id')
                ->leftjoin('service_provider_products', 'service_provider_products.id', '=', 'transactions.service_id')
                ->leftjoin('service_providers', 'service_providers.id', '=', 'service_provider_products.service_provider_id')
                ->leftjoin('transaction_tickets', 'transaction_tickets.transaction_id', '=', 'transactions.id')
                ->leftjoin('transactions_x_payments', 'transactions.id', '=', 'transactions_x_payments.transactions_id')
                ->leftjoin('payments', 'payments.id', '=', 'transactions_x_payments.payments_id')
                ->leftjoin('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->whereRaw("$where")
                ->orderBy('cod_pago', 'desc')
                ->orderBy('transactions.created_at', 'desc')
                ->whereRaw("(((payments.valor_recibido-payments.valor_entregado) = payments.valor_a_pagar and transactions.status not in ('error','canceled') and (payments.valor_recibido-payments.valor_entregado) > 0) or ((payments.valor_recibido-payments.valor_entregado) = 0 and transactions.status in ('error','canceled')))")
                ->paginate(20);

            $total_transactions = \DB::connection('eglobalt_pro')->table('transactions')
                ->select(\DB::raw('sum(abs(transactions.amount)) as monto'))
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'transactions.atm_id')
                ->leftjoin('transactions_x_payments', 'transactions.id', '=', 'transactions_x_payments.transactions_id')
                ->leftjoin('payments', 'payments.id', '=', 'transactions_x_payments.payments_id')
                ->leftjoin('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->whereRaw("$where")
                ->whereRaw("(((payments.valor_recibido-payments.valor_entregado) = payments.valor_a_pagar and transactions.status not in ('error','canceled') and (payments.valor_recibido-payments.valor_entregado) > 0) or ((payments.valor_recibido-payments.valor_entregado) = 0 and transactions.status in ('error','canceled')))")
                ->get();

            foreach ($transactions as &$transaction) {
                //configurar labels de estado
                if ($transaction->status == 'success') {
                    $transaction->status_label =  '<span class="label label-success">' . $transaction->status . '</span>';
                } elseif ($transaction->status == 'canceled' || $transaction->status == 'iniciated') {
                    $transaction->status_label =  '<span class="label label-warning">' . $transaction->status . '</span>';
                } elseif ($transaction->status == 'inconsistency') {
                    $transaction->status =  '<span class="label label-danger">' . 'Inconsistencia' . '</span>';
                } else {
                    $transaction->status_label =  '<span class="label label-danger">' . $transaction->status . '</span>';
                }

                if ($transaction->service_source_id <> 0) {
                    $serv_provider = \DB::table('services_providers_sources')->where('id', $transaction->service_source_id)->first();
                    $transaction->provider = $serv_provider->description;
                    $service_data = \DB::table('services_ondanet_pairing')->where('service_request_id', $transaction->service_id)->where('service_source_id', $transaction->service_source_id)->first();
                    $transaction->servicio = isset($service_data->service_description) ? $service_data->service_description : '';
                }

                if (in_array($transaction->status, ['error', 'canceled', 'error dispositivo'])) {
                    $transaction->vuelto_no_entregado = abs($transaction->valor_recibido - $transaction->valor_entregado);
                } else {
                    $vuelto_esperado = $transaction->valor_recibido - $transaction->valor_a_pagar;
                    $transaction->vuelto_no_entregado = $transaction->valor_recibido - $transaction->valor_a_pagar - $transaction->valor_entregado;
                    if ($vuelto_esperado < $transaction->valor_entregado) {
                        $transaction->vuelto_no_entregado = $transaction->vuelto_no_entregado * (-1);
                    }
                }
            }

            /*Carga datos del formulario*/
            $whereGroup = "";
            $whereOwner = "";
            $whereBranch = "";
            $wherePos = "";
            if (!$this->user->hasAccess('superuser')) {
                if ($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11) {
                    $whereOwner = "owners.id = " . $this->user->owner_id;
                    $whereBranch = "branches.owner_id = " . $this->user->owner_id;
                    $wherePos = "points_of_sale.owner_id = " . $this->user->owner_id;
                }
            }
            //Redes
            $groups     = Group::orderBy('business_groups.description')->where(function ($query) use ($whereGroup) {
                if (!empty($whereGroup)) {
                    $query->whereRaw($whereGroup);
                }
            })->get()->pluck('description', 'id');
            $groups->prepend('Todos', '0');

            $owners     = Owner::orderBy('owners.name')->where(function ($query) use ($whereOwner) {
                if (!empty($whereOwner)) {
                    $query->whereRaw($whereOwner);
                }
            })->get()->pluck('name', 'id');
            $owners->prepend('Todos', '0');
            $branches   = Branch::orderBy('description')->where(function ($query) use ($whereBranch) {
                if (!empty($whereBranch)) {
                    $query->whereRaw($whereBranch);
                }
            })->get()->pluck('description', 'id');
            $branches->prepend('Todos', '0');
            $pdvs       = Pos::orderBy('description')->where(function ($query) use ($wherePos) {
                if (!empty($wherePos)) {
                    $query->whereRaw($wherePos);
                }
            })->with('Atm')->get();
            $pos = [];
            $item = array();
            $item[0] = 'Todos';
            foreach ($pdvs  as $pdv) {
                $item[$pdv->id] = $pdv->description . ' - ' . $pdv->Atm->code;
                $pos = $item;
            }

            $status = array('0' => 'Todos', 'success' => 'Aprobado', 'canceled' => 'Cancelado', 'error' => 'Error', 'rollback' => 'Reversado', 'iniciated' => 'Iniciado', 'error dispositivo' => 'Error de dispositivo', 'devolucion' => 'Devolución', 'inconsistency' => 'Inconsistencia');

            $services   = ServiceProviderProduct::with('WebServiceProvider')->orderBy('service_provider_id', 'DESC')->get();
            if ($this->user->hasAccess('ticketea') && !$this->user->hasAccess('superuser')) {
                $service_item[28] = 'Ticketea - Venta de tickets';
                $services_data = $service_item;
            } else {
                $service_item[0] = 'Todos';
                $service_item[-2] = 'Netel';
                $service_item[-4] = 'Pronet';
                $service_item[-6] = 'Practipago';
                $service_item[-7] = 'Infonet';
                $service_item[-8] = 'Toval';
                $service_item[-10] = 'Netel Trex';
                $service_item[-11] = 'Bancard Ventas QR';
                foreach ($services  as $service) {
                    if ($service->id <> 100) {
                        if ($service->id <> 27 && $service->id <> -1 && $service->WebServiceProvider->name <> 'Toval - Integraciones' && $service->WebServiceProvider->name <> 'Momo - Ken') {
                            $service_item[$service->id] = $service->WebServiceProvider->name . ' - ' . $service->description;
                            $services_data = $service_item;
                        }
                    }
                }
            }

            $resultset = array(
                'target'        => 'Vueltos Entregados',
                'groups'        => $groups,
                'owners'        => $owners,
                'branches'      => $branches,
                'pos'           => $pos,
                'status'        => $status,
                'services_data' => $services_data,
                'transactions'  => $transactions,
                'total_transactions'  => $total_transactions,
                'group_id'      => (isset($input['group_id']) ? $input['group_id'] : 0),
                'owner_id'      => (isset($input['owner_id']) ? $input['owner_id'] : 0),
                'branch_id'     => (isset($input['branch_id']) ? $input['branch_id'] : 0),
                'pos_id'        => (isset($input['pos_id']) ? $input['pos_id'] : 0),
                'status_set'    => (isset($input['status_id']) ? $input['status_id'] : 0),
                'service_id'    => (isset($input['service_id']) ? $input['service_id'] : 0),
                'reservationtime' => (isset($input['reservationtime']) ? $input['reservationtime'] : 0),
                'i'             =>  1,
                'service_request_id' => (isset($input['service_request_id']) ? $input['service_request_id'] : 0),
            );

            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    public function transactionsVueltoCorrectoSearchExport()
    {
        try {
            $input = $this->input;
            $where = "transactions.transaction_type in (1,7,12,13) AND ";
            /*Busqueda minusiosa*/
            if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
                $where .= "transactions.request_data like '%{$input['context']}%'";
            } else {
                /*SET DATE RANGE*/
                $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                $where .= "transactions.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' AND ";

                /*SET OWNER*/
                if (!$this->user->hasAccess('superuser')) {
                    if (($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11)) {
                        $where .= "transactions.owner_id = " . $this->user->owner_id . " AND ";
                    } else {
                        $where .= ($input['owner_id'] <> 0) ? "transactions.owner_id = " . $input['owner_id'] . " AND " : "";
                    }
                } else {
                    $where .= ($input['owner_id'] <> 0) ? "transactions.owner_id = " . $input['owner_id'] . " AND " : "";
                }

                $where .= ($input['branch_id'] <> 0) ? "points_of_sale.branch_id = " . $input['branch_id'] . " AND " : "";
                $where .= ($input['pos_id'] <> 0) ? "points_of_sale.id = " . $input['pos_id'] . " AND " : "";
                $where .= ($input['status_id'] <> "0") ? "transactions.status =  '{$input['status_id']}' AND " : "";
                if ($input['service_id'] == -2) {
                    $where .= 'transactions.service_source_id = 1';
                    if ($input['service_request_id'] <> '0') {
                        $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                    }
                } elseif ($input['service_id'] == -4) {
                    $where .= 'transactions.service_source_id = 4';
                    if ($input['service_request_id'] <> '0') {
                        $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                    }
                } elseif ($input['service_id'] == -6) {
                    $where .= 'transactions.service_source_id = 6';
                    if ($input['service_request_id'] <> '0') {
                        $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                    }
                } elseif ($input['service_id'] == -7) {
                    $where .= 'transactions.service_source_id = 7';
                    if ($input['service_request_id'] <> '0') {
                        $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                    }
                } elseif ($input['service_id'] == -8) {
                    $where .= 'transactions.service_source_id = 8';
                    if ($input['service_request_id'] <> '0') {
                        $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                    }
                } elseif ($input['service_id'] == -10) {
                    $where .= 'transactions.service_source_id = 10';
                    if ($input['service_request_id'] <> '0') {
                        $where .= ' AND transactions.service_id = ' . $input['service_request_id'];
                    }
                } elseif ($this->user->hasAccess('ticketea') && !$this->user->hasAccess('superuser')) {
                    $where .= 'transactions.service_id = 28';
                } else {
                    $where .= ($input['service_id'] <> 0) ? "transactions.service_id = " . $input['service_id'] . " AND service_source_id = 0" : "";
                }
            }

            $where = trim($where);
            $where = trim($where, 'AND');
            $where = trim($where);

            $transactions = \DB::connection('eglobalt_pro')->table('transactions')
                ->select(\DB::raw('transactions.id,transactions.service_id, service_providers.name as proveedor, service_provider_products.description as tipo, transactions.status as estado, transactions.status_description as estado_descripcion, transactions.created_at as fecha,transactions.created_at as hora, transactions.amount as valor_transaccion, payments.id as cod_pago,payments.tipo_pago as forma_pago, transactions.identificador_transaction_id as identificador_transaccion, points_of_sale.description as sede, atms.code as codigo_cajero,  payments.valor_a_pagar, payments.valor_recibido, payments.valor_entregado, transactions.service_source_id'))
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'transactions.atm_id')
                ->join('atms', 'atms.id', '=', 'transactions.atm_id')
                ->leftjoin('service_provider_products', 'service_provider_products.id', '=', 'transactions.service_id')
                ->leftjoin('service_providers', 'service_providers.id', '=', 'service_provider_products.service_provider_id')
                ->leftjoin('transactions_x_payments', 'transactions.id', '=', 'transactions_x_payments.transactions_id')
                ->leftjoin('payments', 'payments.id', '=', 'transactions_x_payments.payments_id')
                ->whereRaw("$where")
                ->orderBy('cod_pago', 'desc')
                ->orderBy('transactions.created_at', 'desc')
                ->whereRaw("(((payments.valor_recibido-payments.valor_entregado) = payments.valor_a_pagar and transactions.status not in ('error','canceled')) or ((payments.valor_recibido-payments.valor_entregado) = 0 and transactions.status in ('error','canceled')))")
                ->get();


            foreach ($transactions as $transaction) {

                if ($transaction->service_source_id <> 0) {
                    $serv_provider = \DB::table('services_providers_sources')->where('id', $transaction->service_source_id)->first();
                    $transaction->proveedor = $serv_provider->description;
                    $service_data = \DB::table('services_ondanet_pairing')->where('service_request_id', $transaction->service_id)->where('service_source_id', $transaction->service_source_id)->first();
                    //$transaction->proveedor .= isset($service_data->service_description)?$service_data->service_description:'';
                    $transaction->tipo  = isset($service_data->service_description) ? $service_data->service_description : '';
                }
                if ($transaction->valor_transaccion < 0) {
                    //Monto transaccion de efectifizacion es negativo, para el reporte exportado se convierte a positivo
                    $transaction->valor_transaccion = $transaction->valor_transaccion * -1;
                }

                /*if($transaction->owner_id == 2){
                    $transaction->owner_id = 'Antell';
                }
                if($transaction->owner_id == 11){
                    $transaction->owner_id = 'Eglobal';
                }*/

                $transaction->fecha = date('d-m-Y', strtotime($transaction->fecha));
                $transaction->hora = date('H:i:s', strtotime($transaction->hora));

                $transaction->valor_transaccion = number_format($transaction->valor_transaccion, 0, '', '');

                unset($transaction->service_source_id);
                unset($transaction->service_id);
            }

            return $transactions;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    public function estadoContableReports()
    {
        try {

            $resultset = array(
                'target' => 'Estado Contable Old',
                'mostrar' => 'todos'
            );

            if (!\Sentinel::getUser()->inRole('mini_terminal') && !\Sentinel::getUser()->inRole('supervisor_miniterminal')) {

                $usersNames = \DB::connection('eglobalt_auth')
                    ->table('users')
                    ->selectRaw('concat(username, \' - \', description) as full_name, id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->pluck('full_name', 'id');

                $usersId = \DB::connection('eglobalt_auth')
                    ->table('users')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->pluck('id', 'id');
                $branches = \DB::table('branches')
                    ->select('branches.*')
                    ->whereIn('branches.user_id', $usersId)
                    ->get();

                $data_select = [];
                foreach ($branches as $key => $branch) {
                    $data_select[$branch->user_id] = $branch->description . ' | ' . $usersNames[$branch->user_id];
                }

                $resultset['usersNames'] = $usersNames;
                $resultset['branches'] = $branches;
                $resultset['data_select'] = $data_select;
                $resultset['user_id'] = '';
            }

            if (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                $supervisor = \DB::table('users_x_groups')->where('user_id', $this->user->id)->first();

                $usersNames = \DB::connection('eglobalt_auth')
                    ->table('users')
                    ->selectRaw('concat(username, \' - \', description) as full_name, id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->pluck('full_name', 'id');

                $usersId = \DB::connection('eglobalt_auth')
                    ->table('users')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->pluck('id', 'id');

                $branches = \DB::table('branches')
                    ->select('branches.*')
                    ->whereIn('branches.user_id', $usersId)
                    ->where('branches.group_id', $supervisor->group_id)
                    ->get();

                $data_select = [];
                foreach ($branches as $key => $branch) {
                    $data_select[$branch->user_id] = $branch->description . ' | ' . $usersNames[$branch->user_id];
                }

                $resultset['usersNames'] = $usersNames;
                $resultset['branches'] = $branches;
                $resultset['data_select'] = $data_select;
                $resultset['user_id'] = '';
            }
            $resultset['activar_resumen'] = '';
            return $resultset;
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de reportes" . $e);
            return false;
        }
    }

    public function estadoContableSearch($request)
    {
        try {
            $input = $this->input;
            $bloqueo_diario = false;
            /*Busqueda minusiosa*/
            /*SET DATE RANGE*/
            if (isset($input['reservationtime']) && $input['reservationtime'] != '0') {
                $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
            }

            /*SET OWNER*/
            if (!\Sentinel::getUser()->inRole('mini_terminal')) {
                $branches = \DB::table('points_of_sale')
                    ->select('points_of_sale.atm_id', 'atms.owner_id')
                    ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                    ->join('atms', 'atms.id', '=', 'points_of_sale.atm_id')
                    ->where('branches.user_id', '=', $input['user_id'])
                    ->whereNull('points_of_sale.deleted_at')
                    ->whereIn('atms.owner_id', [16, 21, 25])
                    ->orderBy('points_of_sale.id', 'desc')
                    ->get();

                $group = \DB::table('business_groups')
                    ->select('business_groups.id')
                    ->join('branches', 'business_groups.id', '=', 'branches.group_id')
                    ->where('branches.user_id', '=', $input['user_id'])
                    ->first();

                $owners = \DB::table('atms')
                    ->selectRaw('atms.owner_id, atms.id, atms.grilla_tradicional')
                    ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                    ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                    ->join('business_groups', 'business_groups.id', '=', 'branches.group_id')
                    ->where('business_groups.id', '=', $group->id)
                    ->whereNull('atms.deleted_at')
                    ->whereNotNull('atms.last_token')
                    ->whereIn('atms.owner_id', [16, 21, 25])
                    ->get();

                $atm_id = '(';
                foreach ($branches as $key => $branch) {
                    $atm_id .= $branch->atm_id . ', ';
                }

                $atm_id = rtrim($atm_id, ', ');
                $atm_id .= ')';
                $user_id = $input['user_id'];
            } else {
                $branches = \DB::table('points_of_sale')
                    ->select('points_of_sale.atm_id', 'atms.owner_id')
                    ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                    ->join('atms', 'atms.id', '=', 'points_of_sale.atm_id')
                    ->where('branches.user_id', '=', $this->user->id)
                    ->whereNull('points_of_sale.deleted_at')
                    ->whereIn('atms.owner_id', [16, 21, 25])
                    ->orderBy('points_of_sale.id', 'desc')
                    ->get();

                $group = \DB::table('business_groups')
                    ->select('business_groups.id')
                    ->join('branches', 'business_groups.id', '=', 'branches.group_id')
                    ->where('branches.user_id', '=', $this->user->id)
                    ->first();

                $owners = \DB::table('atms')
                    ->selectRaw('atms.owner_id, atms.id, atms.grilla_tradicional')
                    ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                    ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                    ->join('business_groups', 'business_groups.id', '=', 'branches.group_id')
                    ->where('business_groups.id', '=', $group->id)
                    ->whereNull('atms.deleted_at')
                    ->whereNotNull('atms.last_token')
                    ->whereIn('atms.owner_id', [16, 21, 25])
                    ->get();

                $atm_id = '(';
                foreach ($branches as $key => $branch) {
                    $atm_id .= $branch->atm_id . ', ';
                }
                $atm_id = rtrim($atm_id, ', ');
                $atm_id .= ')';
                $user_id = $this->user->id;
                $input['user_id'] = $this->user->id;
            }

            if (in_array(21,  array_column($owners, 'owner_id')) || in_array(25,  array_column($owners, 'owner_id')) || in_array(false,  array_column($owners, 'grilla_tradicional'))) {
                $bloqueo_diario = true;
            }

            $baseQuery = [];
            $haber = 0;
            $debe = 0;
            //dd($input);
            if ($atm_id <> '()') {
                if (isset($input['reservationtime'])) {
                    switch ($input['mostrar']) {
                        case 'todos':

                            $baseQuery = \DB::connection('eglobalt_replica')->select(\DB::raw("
                                with balances as (
                                    select
                                        t.created_at as fecha,
                                        case
                                            when t.service_source_id = 0 then
                                                concat(service_providers.name, ' - ', sp.description)
                                            else
                                                concat(sps.description, ' - ', sop.service_description)
                                            end
                                        as concepto,
                                        (
                                            CASE when t.amount > 0 then
                                                abs(t.amount)
                                            else
                                                0
                                            end

                                        ) as debe,
                                        (
                                            CASE 
                                                WHEN status = 'success' and t.amount < 0 and t.service_id != 87 THEN 
                                                    -abs(t.amount)
                                                WHEN status = 'success' and t.amount < 0 and t.service_id = 87 THEN 
                                                    -round(abs(t.amount*0.97), 0)
                                            ELSE
                                                0
                                            END
                                        ) as haber
                                    from
                                        transactions t
                                    left join service_provider_products sp on
                                        t.service_id = sp.id
                                        and t.service_source_id = 0
                                    left join service_providers on
                                        service_providers.id = sp.service_provider_id
                                        and t.service_source_id = 0
                                    left join services_providers_sources sps on
                                        t.service_source_id = sps.id
                                        and t.service_source_id <> 0
                                    left join services_ondanet_pairing sop on
                                        t.service_id = sop.service_request_id
                                        and t.service_source_id = sop.service_source_id
                                        and t.service_source_id <> 0
                                    where
                                    (
                                        atm_id in " . $atm_id . "
                                        and status = 'success'
                                        and t.transaction_type in (1, 7)
                                    )or(
                                        atm_id in " . $atm_id . "
                                        and status = 'error'
                                        and t.service_id in(14, 15)
                                        and t.transaction_type in (1, 7)
                                    )
                                        
                                    union
                                    select
                                        bd.fecha,
                                        concat('Boleta Depósito Nro.',' ', bd.boleta_numero , ' | ', bancos.descripcion, ' | Cta. ', cuentas_bancarias.numero_banco),
                                        0 as debe,
                                        -bd.monto as haber
                                    from
                                        boletas_depositos bd                                    
                                    inner join cuentas_bancarias on
                                        cuentas_bancarias.id = bd.cuenta_bancaria_id
                                    inner join bancos on
                                        bancos.id = cuentas_bancarias.banco_id
                                    where
                                        bd.estado = true and
                                        user_id = " . $user_id . "
                                    union
                                    select
                                        s.fecha as fecha,
                                        concat('Pago a Cliente Venta Nro. ', s.nro_venta),
                                        m.amount as debe,
                                        0 as haber
                                    from
                                        movements m                                  
                                    inner join current_account ca on
                                        m.id = ca.movement_id
                                    inner join miniterminales_sales s on
                                        m.id = s.movements_id
                                    where
                                        m.movement_type_id = 12 and
                                        m.deleted_at is null and
                                        ca.group_id = " . $group->id . "
                                    union
                                        select
                                        s.fecha as fecha,
                                        concat('Pago a Cliente Venta Nro. ', s.nro_venta),
                                        m.amount as debe,
                                        0 as haber
                                    from
                                        movements m                                  
                                    inner join current_account ca on
                                        m.id = ca.movement_id
                                    inner join miniterminales_sales s on
                                        m.id = s.movements_id
                                    where
                                        m.movement_type_id = 12 and
                                        m.deleted_at is null and
                                        ca.group_id = " . $group->id . "
                                    union
                                        select
                                            fecha,
                                            concat('Pago desde Terminal Eglobalt'),
                                            0 as debe,
                                            -mt_cobranzas_mini_x_atm.monto as haber
                                        from
                                            miniterminales_payments_x_atms
                                        inner join
                                            mt_payments_x_atms_details on miniterminales_payments_x_atms.id=mt_payments_x_atms_details.mt_payments_x_atm_id
                                        inner join
                                            mt_cobranzas_mini_x_atm on mt_cobranzas_mini_x_atm.recibo_id=mt_payments_x_atms_details.recibo_id
                                        inner join
                                            business_groups on business_groups.id=miniterminales_payments_x_atms.group_id
                                        inner join
                                            branches on business_groups.id=branches.group_id
                                        where
                                            branches.user_id in (" . $user_id . ") and branches.user_id not in(799, 878)
                                    union
                                        select
                                            rc.created_at as fecha,
                                            concat('Descuento por Comision'),
                                            0 as debe,
                                            -abs(mt_recibos.monto) as haber
                                        from
                                            mt_recibos_cobranzas_x_comision
                                        inner join
                                            mt_recibos on mt_recibos.id=mt_recibos_cobranzas_x_comision.recibo_id
                                        inner join
                                            mt_recibos_comisiones_details on mt_recibos.id=mt_recibos_comisiones_details.recibo_id
                                        inner join
                                            mt_recibos_comisiones rc on rc.id=mt_recibos_comisiones_details.recibo_comision_id
                                        inner join
                                            atms on atms.id=rc.atm_id
                                        --inner join
                                            --branches on business_groups.id=branches.group_id
                                        where
                                            --branches.user_id in (" . $user_id . ") and branches.user_id not in(814)
                                            atm_id in " . $atm_id . "
                                    union
                                    select
                                        t.created_at as fecha,
                                        concat('Reversion de transaccion ',' ', mt_recibos_reversiones.transaction_id),
                                        0 as debe,
                                        -abs(t.amount) as haber
                                    from
                                        mt_recibos_reversiones
                                    inner join 
                                        transactions t on t.id=mt_recibos_reversiones.transaction_id
                                    where
                                        atm_id in " . $atm_id . "
                                        and t.transaction_type in (1,7,12)
                                )
                                select
                                    balances.*,
                                    sum (balances.haber + balances.debe) over (
                                        order by fecha
                                        rows between unbounded preceding and current row
                                    ) as saldo
                                from
                                    balances
                                where
                                    fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'
                                order by
                                    fecha asc;
                            "));
                            break;

                        case 'depositos':
                            $baseQuery = \DB::connection('eglobalt_replica')->select(\DB::raw("
                                with balances as (
                                    select
                                        bd.fecha,
                                        concat('Boleta Depósito Nro.',' ', bd.boleta_numero , ' | ', bancos.descripcion, ' | Cta. ', cuentas_bancarias.numero_banco) as concepto,
                                        0 as debe,
                                        -bd.monto as haber
                                    from
                                        boletas_depositos bd                                    
                                    inner join cuentas_bancarias on
                                        cuentas_bancarias.id = bd.cuenta_bancaria_id
                                    inner join bancos on
                                        bancos.id = cuentas_bancarias.banco_id    
                                    where
                                        bd.estado = true and
                                        user_id = " . $user_id . "
                                    union 
                                    select
                                        fecha,
                                        concat('Pago desde Terminal Eglobalt'),
                                        0 as debe,
                                        -mt_cobranzas_mini_x_atm.monto as haber
                                    from
                                        miniterminales_payments_x_atms
                                    inner join
                                        mt_payments_x_atms_details on miniterminales_payments_x_atms.id=mt_payments_x_atms_details.mt_payments_x_atm_id
                                    inner join
                                        mt_cobranzas_mini_x_atm on mt_cobranzas_mini_x_atm.recibo_id=mt_payments_x_atms_details.recibo_id
                                    inner join
                                        business_groups on business_groups.id=miniterminales_payments_x_atms.group_id
                                    inner join
                                        branches on business_groups.id=branches.group_id
                                    where
                                        branches.user_id in (" . $user_id . ") and branches.user_id not in(799, 878)
                                    union
                                        select
                                            rc.created_at as fecha,
                                            concat('Descuento por Comision'),
                                            0 as debe,
                                            -abs(mt_recibos.monto) as haber
                                        from
                                            mt_recibos_cobranzas_x_comision
                                        inner join
                                            mt_recibos on mt_recibos.id=mt_recibos_cobranzas_x_comision.recibo_id
                                        inner join
                                            mt_recibos_comisiones_details on mt_recibos.id=mt_recibos_comisiones_details.recibo_id
                                        inner join
                                            mt_recibos_comisiones rc on rc.id=mt_recibos_comisiones_details.recibo_comision_id
                                        inner join
                                            atms on atms.id=rc.atm_id
                                        --inner join
                                            --branches on business_groups.id=branches.group_id
                                        where
                                            --branches.user_id in (" . $user_id . ") and branches.user_id not in(814)
                                            atm_id in " . $atm_id . "
                                )
                                select
                                    balances.*,
                                    sum (balances.haber + balances.debe) over (
                                        order by fecha
                                        rows between unbounded preceding and current row
                                    ) as saldo
                                from
                                    balances
                                where
                                    fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'
                                order by
                                    fecha asc;
                            "));

                            break;

                        case 'transacciones':
                            $baseQuery = \DB::connection('eglobalt_replica')->select(\DB::raw("
                                with balances as (
                                    select
                                        t.created_at as fecha,
                                        case
                                            when t.service_source_id = 0 then
                                                concat(service_providers.name, ' - ', sp.description)
                                            else
                                                concat(sps.description, ' - ', sop.service_description)
                                            end
                                        as concepto,
                                        (
                                            CASE when t.amount > 0 then
                                                abs(t.amount)
                                            else
                                                0
                                            end
                                            
                                        ) as debe,
                                        (
                                            CASE 
                                                WHEN status = 'success' and t.amount < 0 and t.service_id != 87 THEN 
                                                    -abs(t.amount)
                                                WHEN status = 'success' and t.amount < 0 and t.service_id = 87 THEN 
                                                    -round(abs(t.amount*0.97), 0)
                                            ELSE
                                                0
                                            END
                                        ) as haber
                                    from
                                        transactions t
                                    left join service_provider_products sp on
                                        t.service_id = sp.id
                                        and t.service_source_id = 0
                                    left join service_providers on
                                        service_providers.id = sp.service_provider_id
                                        and t.service_source_id = 0
                                    left join services_providers_sources sps on
                                        t.service_source_id = sps.id
                                        and t.service_source_id <> 0
                                    left join services_ondanet_pairing sop on
                                        t.service_id = sop.service_request_id
                                        and t.service_source_id = sop.service_source_id
                                        and t.service_source_id <> 0
                                    where
                                    (
                                        atm_id in " . $atm_id . "
                                        and status = 'success'
                                        and t.transaction_type in (1, 7)
                                    )or(
                                        atm_id in " . $atm_id . "
                                        and status = 'error'
                                        and t.service_id in(14, 15)
                                        and t.transaction_type in (1, 7)
                                    )
                                )
                                select
                                    balances.*,
                                    sum (balances.haber + balances.debe) over (
                                        order by fecha
                                        rows between unbounded preceding and current row
                                    ) as saldo
                                from
                                    balances
                                where
                                    fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'
                                order by
                                    fecha asc;
                            "));

                            break;

                        case 'reversiones':
                            $baseQuery = \DB::connection('eglobalt_replica')->select(\DB::raw("
                                with balances as (
                                    select
                                        t.created_at as fecha,
                                        concat('Reversion de transaccion ',' ', mt_recibos_reversiones.transaction_id) as concepto,
                                        0 as debe,
                                        -t.amount as haber
                                    from
                                        mt_recibos_reversiones
                                    inner join 
                                        transactions t on t.id= mt_recibos_reversiones.transaction_id
                                    inner join 
                                        mt_recibos on mt_recibos.id= mt_recibos_reversiones.recibo_id
                                    where
                                        atm_id in " . $atm_id . "
                                        and t.transaction_type in (1,7,12)
                                        and mt_recibos.deleted_at is null
                                )
                                select
                                    balances.*,
                                    sum (balances.haber + balances.debe) over (
                                        order by fecha
                                        rows between unbounded preceding and current row
                                    ) as saldo
                                from
                                    balances
                                where
                                    fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'
                                order by
                                    fecha asc;
                            "));

                            break;
                        default:
                            $baseQuery = [];
                            break;
                    }

                    $total_debe = \DB::connection('eglobalt_replica')->select("
                        select
                            SUM(
                                CASE 
                                    WHEN status = 'success' and t.amount >= 0 THEN 
                                        abs(t.amount)
                                    WHEN status = 'error' and t.service_id in(14, 15) and t.amount >= 0 THEN 
                                        abs(t.amount)
                                    else
                                        0
                                END
                            ) as total
                            --sum(abs(t.amount)) as total
                        from
                            transactions t
                        left join service_provider_products sp on
                            t.service_id = sp.id
                            and t.service_source_id = 0
                        left join service_providers on
                            service_providers.id = sp.service_provider_id
                            and t.service_source_id = 0
                        left join services_providers_sources sps on
                            t.service_source_id = sps.id
                            and t.service_source_id <> 0
                        left join services_ondanet_pairing sop on
                            t.service_id = sop.service_request_id
                            and t.service_source_id = sop.service_source_id
                            and t.service_source_id <> 0
                        where
                            atm_id in " . $atm_id . "
                            --and status = 'success'
                            and t.transaction_type = 1
                            and t.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'
                    ");

                    $total_haber = \DB::connection('eglobalt_replica')->table('boletas_depositos')
                        ->selectRaw('-sum(monto) as total_haber')
                        ->whereRaw("
                            boletas_depositos.estado = true and
                            fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'
                            and user_id = " . $user_id . "
                    ")->first();

                    $total_pago_cashout = \DB::connection('eglobalt_replica')->table('movements as m')
                        ->selectRaw('SUM(CASE WHEN movement_type_id = 12 THEN (m.amount) else 0 END) as total')
                        ->join('current_account as ca', 'm.id', '=', 'ca.movement_id')
                        ->join('miniterminales_sales as s', 'm.id', '=', 's.movements_id')
                        ->whereRaw("
                            s.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'
                            and ca.group_id = " . $group->id . " and
                            m.movement_type_id in (12) and
                            m.deleted_at is null
                    ")->first();

                    $total_pago_mini = \DB::connection('eglobalt_replica')->select("
                        select
                            -sum(mt_cobranzas_mini_x_atm.monto) as total
                        from
                            miniterminales_payments_x_atms
                        inner join
                            mt_payments_x_atms_details on miniterminales_payments_x_atms.id=mt_payments_x_atms_details.mt_payments_x_atm_id
                        inner join
                            mt_cobranzas_mini_x_atm on mt_cobranzas_mini_x_atm.recibo_id=mt_payments_x_atms_details.recibo_id
                        where
                            miniterminales_payments_x_atms.atm_id in " . $atm_id . "
                            and fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'
                    ");

                    $total_descuento = \DB::connection('eglobalt_replica')->select("
                        select
                            -sum(abs(mt_recibos.monto)) as total
                        from
                            mt_recibos_cobranzas_x_comision
                        inner join
                            mt_recibos on mt_recibos.id=mt_recibos_cobranzas_x_comision.recibo_id
                        inner join
                            mt_recibos_comisiones_details on mt_recibos.id=mt_recibos_comisiones_details.recibo_id
                        inner join
                            mt_recibos_comisiones rc on rc.id=mt_recibos_comisiones_details.recibo_comision_id
                        inner join
                            atms on atms.id=rc.atm_id
                        --inner join
                            --branches on business_groups.id=branches.group_id
                        where
                            --branches.user_id in (" . $user_id . ") and branches.user_id not in(814)
                            atm_id in " . $atm_id . "
                            and rc.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'
                    ");


                    $total_reversion = \DB::connection('eglobalt_replica')->select("
                        select
                            -sum(abs(t.amount)) as total
                        from
                            mt_recibos_reversiones
                        inner join 
                                transactions t on t.id= mt_recibos_reversiones.transaction_id
                        inner join 
                            mt_recibos on mt_recibos.id= mt_recibos_reversiones.recibo_id
                        where
                            atm_id in " . $atm_id . "
                            and t.transaction_type in (1,7,12)
                            and t.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'
                            and mt_recibos.deleted_at is null
                    ");

                    $total_cashout = \DB::connection('eglobalt_replica')->select("
                        select
                            -SUM(
                                CASE 
                                    WHEN status = 'success' and t.amount < 0 and t.service_id != 87 THEN 
                                        abs(t.amount)
                                    WHEN status = 'success' and t.amount < 0 and t.service_id = 87 THEN 
                                        round(abs(t.amount*0.97), 0)
                                    else
                                        0
                                END
                            ) as total
                            --sum(abs(t.amount)) as total
                        from
                            transactions t
                        left join service_provider_products sp on
                            t.service_id = sp.id
                            and t.service_source_id = 0
                        left join service_providers on
                            service_providers.id = sp.service_provider_id
                            and t.service_source_id = 0
                        left join services_providers_sources sps on
                            t.service_source_id = sps.id
                            and t.service_source_id <> 0
                        left join services_ondanet_pairing sop on
                            t.service_id = sop.service_request_id
                            and t.service_source_id = sop.service_source_id
                            and t.service_source_id <> 0
                        where
                            atm_id in " . $atm_id . "
                            --and status = 'success'
                            and t.transaction_type in (7)
                            and t.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'
                    ");

                    $haber = $total_haber->total_haber + $total_pago_mini[0]->total + $total_descuento[0]->total;
                    $debe = $total_debe[0]->total + $total_pago_cashout->total;
                    $reversion = $total_reversion[0]->total;
                    $cashout = $total_cashout[0]->total;

                    $input['activar_resumen'] = '';
                } else if ($input['reservationtime'] = '0') {

                    $total_debe = \DB::connection('eglobalt_replica')->select("
                        select
                            SUM(
                                CASE 
                                    WHEN status = 'success' and t.amount > 0 THEN 
                                        abs(t.amount)
                                    WHEN status = 'error' and t.service_id in(14, 15) and t.amount > 0 THEN 
                                        abs(t.amount)
                                    else
                                        0
                                END
                            ) as total
                        from
                            transactions t
                        left join service_provider_products sp on
                            t.service_id = sp.id
                            and t.service_source_id = 0
                        left join service_providers on
                            service_providers.id = sp.service_provider_id
                            and t.service_source_id = 0
                        left join services_providers_sources sps on
                            t.service_source_id = sps.id
                            and t.service_source_id <> 0
                        left join services_ondanet_pairing sop on
                            t.service_id = sop.service_request_id
                            and t.service_source_id = sop.service_source_id
                            and t.service_source_id <> 0
                        where
                            atm_id in " . $atm_id . "
                            --and status = 'success'
                            and t.transaction_type in (1)
                    ");

                    $total_haber = \DB::connection('eglobalt_replica')->table('boletas_depositos')
                        ->selectRaw('-sum(monto) as total_haber')
                        ->whereRaw("
                            estado = true and
                            and user_id = " . $user_id . "
                    ")->first();

                    $total_pago_cashout = \DB::connection('eglobalt_replica')->table('movements as m')
                        ->selectRaw('SUM(CASE WHEN movement_type_id = 12 THEN (m.amount) else 0 END) as total')
                        ->join('current_account as ca', 'm.id', '=', 'ca.movement_id')
                        ->whereRaw("
                            ca.group_id = " . $group->id . " and
                            m.movement_type_id in (12) and
                            m.deleted_at is null
                    ")->first();

                    $total_pago_mini = \DB::connection('eglobalt_replica')->select("
                        select
                            -sum(mt_cobranzas_mini_x_atm.monto) as total
                        from
                            miniterminales_payments_x_atms
                        inner join
                            mt_payments_x_atms_details on miniterminales_payments_x_atms.id=mt_payments_x_atms_details.mt_payments_x_atm_id
                        inner join
                            mt_cobranzas_mini_x_atm on mt_cobranzas_mini_x_atm.recibo_id=mt_payments_x_atms_details.recibo_id
                        where
                            miniterminales_payments_x_atms.atm_id in " . $atm_id . "
                    ");

                    $total_descuento = \DB::connection('eglobalt_replica')->select("
                        select
                            -sum(abs(mt_recibos.monto)) as total
                        from
                            mt_recibos_cobranzas_x_comision
                        inner join
                            mt_recibos on mt_recibos.id=mt_recibos_cobranzas_x_comision.recibo_id
                        inner join
                            mt_recibos_comisiones_details on mt_recibos.id=mt_recibos_comisiones_details.recibo_id
                        inner join
                            mt_recibos_comisiones rc on rc.id=mt_recibos_comisiones_details.recibo_comision_id
                        inner join
                            atms on atms.id=rc.atm_id
                        --inner join
                            --branches on business_groups.id=branches.group_id
                        where
                            --branches.user_id in (" . $user_id . ") and branches.user_id not in(814)
                            atm_id in " . $atm_id . "
                    ");

                    $total_reversion = \DB::connection('eglobalt_replica')->select("
                        select
                            -sum(abs(t.amount)) as total
                        from
                            mt_recibos_reversiones
                        inner join 
                                transactions t on t.id= mt_recibos_reversiones.transaction_id
                        inner join 
                            mt_recibos on mt_recibos.id= mt_recibos_reversiones.recibo_id
                        where
                            t.atm_id in " . $atm_id . "
                            and t.transaction_type in (7)
                            and mt_recibos.deleted_at is null
                    ");

                    $total_cashout = \DB::connection('eglobalt_replica')->select("
                        select
                            -SUM(
                                CASE 
                                    WHEN status = 'success' and t.amount < 0 and t.service_id != 87 THEN 
                                        abs(t.amount)
                                    WHEN status = 'success' and t.amount < 0 and t.service_id = 87 THEN 
                                        round(abs(t.amount*0.97), 0)
                                    else
                                        0
                                END
                            ) as total
                            --sum(abs(t.amount)) as total
                        from
                            transactions t
                        left join service_provider_products sp on
                            t.service_id = sp.id
                            and t.service_source_id = 0
                        left join service_providers on
                            service_providers.id = sp.service_provider_id
                            and t.service_source_id = 0
                        left join services_providers_sources sps on
                            t.service_source_id = sps.id
                            and t.service_source_id <> 0
                        left join services_ondanet_pairing sop on
                            t.service_id = sop.service_request_id
                            and t.service_source_id = sop.service_source_id
                            and t.service_source_id <> 0
                        where
                            atm_id in " . $atm_id . "
                            --and status = 'success'
                            and t.transaction_type in (7)
                    ");

                    $haber = $total_haber->total_haber + $total_pago_mini[0]->total + $total_descuento[0]->total;
                    $debe = $total_debe[0]->total + $total_pago_cashout->total;
                    $reversion = $total_reversion[0]->total;
                    $cashout = $total_cashout[0]->total;

                    $baseQuery = [];

                    $input['activar_resumen'] = '';
                } else {
                    if ($input['activar_resumen'] == 2) {
                        $date = date('N');

                        if ($bloqueo_diario) {
                            $hasta = Carbon::parse(date('Y-m-d 23:59:59'))->modify('-1 days');
                        } else {
                            if ($date == 1 || $date == 3 || $date == 5) {
                                $hasta = Carbon::parse(date('Y-m-d 23:59:59'))->modify('-1 days');
                            } else if ($date == 2 || $date == 4 || $date == 6) {
                                $hasta = Carbon::parse(date('Y-m-d 23:59:59'))->modify('-2 days');
                            } else {
                                $hasta = Carbon::parse(date('Y-m-d 23:59:59'))->modify('-3 days');
                            }
                        }
                    } else {

                        $hasta = date('Y-m-d H:i:s');
                    }
                    $fecha_actual = date('Y-m-d H:i:s');

                    $total_debe = \DB::connection('eglobalt_replica')->select("
                        select
                            SUM(
                                CASE 
                                    WHEN status = 'success' and t.amount >= 0 THEN 
                                        abs(t.amount)
                                    WHEN status = 'error' and t.service_id in(14, 15) and t.amount >= 0 THEN 
                                        abs(t.amount)
                                    else 0 
                                END
                            ) as total
                            --sum(abs(t.amount)) as total
                        from
                            transactions t
                        left join service_provider_products sp on
                            t.service_id = sp.id
                            and t.service_source_id = 0
                        left join service_providers on
                            service_providers.id = sp.service_provider_id
                            and t.service_source_id = 0
                        left join services_providers_sources sps on
                            t.service_source_id = sps.id
                            and t.service_source_id <> 0
                        left join services_ondanet_pairing sop on
                            t.service_id = sop.service_request_id
                            and t.service_source_id = sop.service_source_id
                            and t.service_source_id <> 0
                        where
                            atm_id in " . $atm_id . "
                            --and status = 'success'
                            and t.transaction_type in (1)
                            and t.created_at <= '{$hasta}'
                    ");

                    $total_pago_cashout = \DB::connection('eglobalt_replica')->table('movements as m')
                        ->selectRaw('SUM(CASE WHEN movement_type_id = 12 THEN (m.amount) else 0 END) as total')
                        ->join('current_account as ca', 'm.id', '=', 'ca.movement_id')
                        ->whereRaw("
                            m.created_at <= '{$fecha_actual}' and
                            ca.group_id = " . $group->id . " and
                            m.movement_type_id in (12) and
                            m.deleted_at is null
                    ")->first();

                    $total_haber = \DB::connection('eglobalt_replica')->table('boletas_depositos')
                        ->selectRaw('-sum(monto) as total_haber')
                        ->whereRaw("
                            estado = true and
                            fecha <= '{$fecha_actual}'
                            and user_id = " . $user_id . "
                    ")->first();


                    $total_pago_mini = \DB::connection('eglobalt_replica')->select("
                        select
                            -sum(mt_cobranzas_mini_x_atm.monto) as total
                        from
                            miniterminales_payments_x_atms
                        inner join
                            mt_payments_x_atms_details on miniterminales_payments_x_atms.id=mt_payments_x_atms_details.mt_payments_x_atm_id
                        inner join
                            mt_cobranzas_mini_x_atm on mt_cobranzas_mini_x_atm.recibo_id=mt_payments_x_atms_details.recibo_id
                        where
                            miniterminales_payments_x_atms.atm_id in " . $atm_id . "
                    ");

                    $total_descuento = \DB::connection('eglobalt_replica')->select("
                        select
                            -sum(abs(mt_recibos.monto)) as total
                        from
                            mt_recibos_cobranzas_x_comision
                        inner join
                            mt_recibos on mt_recibos.id=mt_recibos_cobranzas_x_comision.recibo_id
                        inner join
                            mt_recibos_comisiones_details on mt_recibos.id=mt_recibos_comisiones_details.recibo_id
                        inner join
                            mt_recibos_comisiones rc on rc.id=mt_recibos_comisiones_details.recibo_comision_id
                        inner join
                            atms on atms.id=rc.atm_id
                        --inner join
                            --branches on business_groups.id=branches.group_id            
                        where
                            rc.created_at <= '{$fecha_actual}' and
                            --branches.user_id in (" . $user_id . ") and branches.user_id not in(814)
                            atm_id in " . $atm_id . "
                    ");

                    $total_reversion = \DB::connection('eglobalt_replica')->select("
                        select
                            -sum(abs(t.amount)) as total
                        from
                            mt_recibos_reversiones
                        inner join 
                                transactions t on t.id= mt_recibos_reversiones.transaction_id
                        inner join 
                            mt_recibos on mt_recibos.id= mt_recibos_reversiones.recibo_id
                        where
                            atm_id in " . $atm_id . "
                            and t.transaction_type in (1,7,12)
                            and t.created_at <= '{$hasta}'
                            and mt_recibos.deleted_at is null
                    ");

                    $total_cashout = \DB::connection('eglobalt_replica')->select("
                        select
                            -SUM(
                                CASE 
                                    WHEN status = 'success' and t.amount < 0 and t.service_id != 87 THEN 
                                        abs(t.amount)
                                    WHEN status = 'success' and t.amount < 0 and t.service_id = 87 THEN 
                                        round(abs(t.amount*0.97), 0)
                                    else
                                        0
                                END
                            ) as total
                            --sum(abs(t.amount)) as total
                        from
                            transactions t
                        left join service_provider_products sp on
                            t.service_id = sp.id
                            and t.service_source_id = 0
                        left join service_providers on
                            service_providers.id = sp.service_provider_id
                            and t.service_source_id = 0
                        left join services_providers_sources sps on
                            t.service_source_id = sps.id
                            and t.service_source_id <> 0
                        left join services_ondanet_pairing sop on
                            t.service_id = sop.service_request_id
                            and t.service_source_id = sop.service_source_id
                            and t.service_source_id <> 0
                        where
                            atm_id in " . $atm_id . "
                            --and status = 'success'
                            and t.transaction_type in (7)
                            and t.created_at <= '{$fecha_actual}'
                    ");

                    $haber = $total_haber->total_haber + $total_pago_mini[0]->total + $total_descuento[0]->total;
                    $debe = $total_debe[0]->total + $total_pago_cashout->total;
                    $reversion = $total_reversion[0]->total;
                    $cashout = $total_cashout[0]->total;

                    $baseQuery = [];
                }
            }

            $total_saldo = $haber + $debe + $reversion + $cashout;
            $results = $this->arrayPaginator($baseQuery->toArray(), $request);

            $resultset = array(
                'target'        => 'Estado Contable Old',
                'transactions'  => $results,
                'reservationtime' => (isset($input['reservationtime']) ? $input['reservationtime'] : 0),
                'i'             =>  1,
                'total_debe' => number_format($debe),
                'total_haber' => number_format($haber),
                'total_reversion' => number_format($reversion),
                'total_cashout' => number_format($cashout),
                'total_saldo' => number_format($total_saldo),
                'mostrar' => $input['mostrar'],
                'activar_resumen' => $input['activar_resumen'],
                'user_id'   => $input['user_id']
            );

            if (!\Sentinel::getUser()->inRole('mini_terminal') && !\Sentinel::getUser()->inRole('supervisor_miniterminal')) {

                $usersNames = \DB::connection('eglobalt_auth')
                    ->table('users')
                    ->selectRaw('concat(username, \' - \', description) as full_name, id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->pluck('full_name', 'id');

                $usersId = \DB::connection('eglobalt_auth')
                    ->table('users')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->pluck('id', 'id');

                $branches = \DB::table('branches')
                    ->select('branches.*')
                    ->whereIn('branches.user_id', $usersId)
                    ->get();

                $data_select = [];
                foreach ($branches as $key => $branch) {
                    $data_select[$branch->user_id] = $branch->description . ' | ' . $usersNames[$branch->user_id];
                }

                $resultset['usersNames'] = $usersNames;
                $resultset['branches'] = $branches;
                $resultset['data_select'] = $data_select;
                $resultset['user_id'] = $input['user_id'];
            }

            if (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                $supervisor = \DB::table('users_x_groups')->where('user_id', $this->user->id)->first();

                $usersNames = \DB::connection('eglobalt_auth')
                    ->table('users')
                    ->selectRaw('concat(username, \' - \', description) as full_name, id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->pluck('full_name', 'id');

                $usersId = \DB::connection('eglobalt_auth')
                    ->table('users')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->pluck('id', 'id');

                $branches = \DB::table('branches')
                    ->select('branches.*')
                    ->whereIn('branches.user_id', $usersId)
                    ->where('branches.group_id', $supervisor->group_id)
                    ->get();

                $data_select = [];
                foreach ($branches as $key => $branch) {
                    $data_select[$branch->user_id] = $branch->description . ' | ' . $usersNames[$branch->user_id];
                }

                $resultset['usersNames'] = $usersNames;
                $resultset['branches'] = $branches;
                $resultset['data_select'] = $data_select;
                $resultset['user_id'] = $input['user_id'];
            }

            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    public function estadoContableSearchExport()
    {
        try {
            $input = $this->input;
            $bloqueo_diario = false;
            /*Busqueda minusiosa*/
            /*SET DATE RANGE*/
            if (isset($input['reservationtime'])) {
                $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
            }

            /*SET OWNER*/
            if (!\Sentinel::getUser()->inRole('mini_terminal')) {
                $branches = \DB::table('points_of_sale')
                    ->select('points_of_sale.atm_id', 'atms.owner_id', 'branches.group_id')
                    ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                    ->join('atms', 'atms.id', '=', 'points_of_sale.atm_id')
                    ->where('branches.user_id', '=', $input['user_id'])
                    ->whereNull('points_of_sale.deleted_at')
                    ->whereIn('atms.owner_id', [16, 21, 25])
                    ->orderBy('points_of_sale.id', 'desc')
                    ->get();

                $atm_id = '(';
                foreach ($branches as $key => $branch) {
                    $atm_id .= $branch->atm_id . ', ';
                    $owner = $branch->owner_id;
                }

                $atm_id = rtrim($atm_id, ', ');
                $atm_id .= ')';
                $user_id = $input['user_id'];
            } else {
                $branches = \DB::table('points_of_sale')
                    ->select('points_of_sale.atm_id', 'atms.owner_id', 'branches.group_id')
                    ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                    ->join('atms', 'atms.id', '=', 'points_of_sale.atm_id')
                    ->where('branches.user_id', '=', $this->user->id)
                    ->whereNull('points_of_sale.deleted_at')
                    ->whereIn('atms.owner_id', [16, 21, 25])
                    ->orderBy('points_of_sale.id', 'desc')
                    ->get();

                $atm_id = '(';
                foreach ($branches as $key => $branch) {
                    $atm_id .= $branch->atm_id . ', ';
                    $owner = $branch->owner_id;
                }
                $atm_id = rtrim($atm_id, ', ');
                $atm_id .= ')';
                $user_id = $this->user->id;
            }

            $owners = \DB::table('atms')
                ->selectRaw('atms.owner_id, atms.id, atms.grilla_tradicional')
                ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->join('business_groups', 'business_groups.id', '=', 'branches.group_id')
                ->where('business_groups.id', '=', $branches[0]->group_id)
                ->whereNull('atms.deleted_at')
                ->whereNotNull('atms.last_token')
                ->whereIn('atms.owner_id', [16, 21, 25])
                ->get();

            if (in_array(21,  array_column($owners, 'owner_id')) || in_array(25,  array_column($owners, 'owner_id')) || in_array(false,  array_column($owners, 'grilla_tradicional'))) {
                $bloqueo_diario = true;
            }

            $baseQuery = [];
            $haber = 0;
            $debe = 0;
            if ($atm_id <> '()') {
                if (isset($input['reservationtime'])) {
                    switch ($input['mostrar']) {
                        case 'todos':
                            $baseQuery = \DB::connection('eglobalt_replica')->select(\DB::raw("
                                with balances as (
                                    select
                                        t.created_at as fecha,
                                        case
                                            when t.service_source_id = 0 then
                                                concat(service_providers.name, ' - ', sp.description)
                                            else
                                                concat(sps.description, ' - ', sop.service_description)
                                            end
                                        as concepto,
                                        (
                                            CASE when t.amount > 0 then
                                                abs(t.amount)
                                            else
                                                0
                                            end

                                        ) as debe,
                                        (
                                            CASE 
                                                WHEN status = 'success' and t.amount < 0 and t.service_id != 87 THEN 
                                                    -abs(t.amount)
                                                WHEN status = 'success' and t.amount < 0 and t.service_id = 87 THEN 
                                                    -round(abs(t.amount*0.97), 0)
                                                ELSE
                                                    0
                                            END
                                        ) as haber
                                    from
                                        transactions t
                                    left join service_provider_products sp on
                                        t.service_id = sp.id
                                        and t.service_source_id = 0
                                    left join service_providers on
                                        service_providers.id = sp.service_provider_id
                                        and t.service_source_id = 0
                                    left join services_providers_sources sps on
                                        t.service_source_id = sps.id
                                        and t.service_source_id <> 0
                                    left join services_ondanet_pairing sop on
                                        t.service_id = sop.service_request_id
                                        and t.service_source_id = sop.service_source_id
                                        and t.service_source_id <> 0
                                    where
                                    (
                                        atm_id in " . $atm_id . "
                                        and status = 'success'
                                        and t.transaction_type in (1, 7)
                                    )or(
                                        atm_id in " . $atm_id . "
                                        and status = 'error'
                                        and t.service_id in(14, 15)
                                        and t.transaction_type in (1, 7)
                                    )
                                    union
                                    select
                                        bd.fecha,
                                        concat('Boleta Depósito Nro.',' ', bd.boleta_numero , ' | ', bancos.descripcion, ' | Cta. ', cuentas_bancarias.numero_banco),
                                        0 as debe,
                                        -bd.monto as haber
                                    from
                                        boletas_depositos bd                                    
                                    inner join cuentas_bancarias on
                                        cuentas_bancarias.id = bd.cuenta_bancaria_id
                                    inner join bancos on
                                        bancos.id = cuentas_bancarias.banco_id
                                    where
                                        bd.estado = true and
                                        user_id = " . $user_id . "
                                    union
                                        select
                                            s.fecha as fecha,
                                            concat('Pago a Cliente Venta Nro. ', s.nro_venta),
                                            m.amount as debe,
                                            0 as haber
                                        from
                                            movements m                                  
                                        inner join current_account ca on
                                            m.id = ca.movement_id
                                        inner join miniterminales_sales s on
                                            m.id = s.movements_id
                                        where
                                            m.movement_type_id = 12 and
                                            m.deleted_at is null and
                                            ca.group_id = " . $branches[0]->group_id . "
                                    union
                                        select
                                            fecha,
                                            concat('Pago desde Terminal Eglobalt'),
                                            0 as debe,
                                            -mt_cobranzas_mini_x_atm.monto as haber
                                        from
                                            miniterminales_payments_x_atms
                                        inner join
                                            mt_payments_x_atms_details on miniterminales_payments_x_atms.id=mt_payments_x_atms_details.mt_payments_x_atm_id
                                        inner join
                                            mt_cobranzas_mini_x_atm on mt_cobranzas_mini_x_atm.recibo_id=mt_payments_x_atms_details.recibo_id
                                        where
                                            miniterminales_payments_x_atms.atm_id in " . $atm_id . "
                                    union
                                        select
                                            rc.created_at as fecha,
                                            concat('Descuento por Comision'),
                                            0 as debe,
                                            -abs(mt_recibos.monto) as haber
                                        from
                                            mt_recibos_cobranzas_x_comision
                                        inner join
                                            mt_recibos on mt_recibos.id=mt_recibos_cobranzas_x_comision.recibo_id
                                        inner join
                                            mt_recibos_comisiones_details on mt_recibos.id=mt_recibos_comisiones_details.recibo_id
                                        inner join
                                            mt_recibos_comisiones rc on rc.id=mt_recibos_comisiones_details.recibo_comision_id
                                        inner join
                                            atms on atms.id=rc.atm_id
                                        --inner join
                                            --branches on business_groups.id=branches.group_id
                                        where
                                            --branches.user_id in (" . $user_id . ") and branches.user_id not in(814)
                                            atm_id in " . $atm_id . "
                                    union
                                    select
                                        t.created_at as fecha,
                                        concat('Reversion de transaccion ',' ', mt_recibos_reversiones.transaction_id),
                                        0 as debe,
                                        -abs(t.amount) as haber
                                    from
                                        mt_recibos_reversiones
                                    inner join 
                                        transactions t on t.id=mt_recibos_reversiones.transaction_id
                                    inner join 
                                        mt_recibos on mt_recibos.id= mt_recibos_reversiones.recibo_id
                                    where
                                        atm_id in " . $atm_id . "
                                        and t.transaction_type in (1,7,12)
                                        and mt_recibos.deleted_at is null
                                )
                                select
                                    balances.*,
                                    sum (balances.haber + balances.debe) over (
                                        order by fecha
                                        rows between unbounded preceding and current row
                                    ) as saldo
                                from
                                    balances
                                where
                                    fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'
                                order by
                                    fecha asc;
                            "));
                            break;

                        case 'depositos':
                            $baseQuery = \DB::connection('eglobalt_replica')->select(\DB::raw("
                                with balances as (
                                    select
                                        bd.fecha,
                                        concat('Boleta Depósito Nro.',' ', bd.boleta_numero , ' | ', bancos.descripcion, ' | Cta. ', cuentas_bancarias.numero_banco) as concepto,
                                        0 as debe,
                                        bd.monto as haber
                                    from
                                        boletas_depositos bd                                    
                                    inner join cuentas_bancarias on
                                        cuentas_bancarias.id = bd.cuenta_bancaria_id
                                    inner join bancos on
                                        bancos.id = cuentas_bancarias.banco_id    
                                    where
                                        bd.estado = true and
                                        user_id = " . $user_id . "
                                    union 
                                        select
                                            fecha,
                                            concat('Pago desde Terminal Eglobalt'),
                                            0 as debe,
                                            -mt_cobranzas_mini_x_atm.monto as haber
                                        from
                                            miniterminales_payments_x_atms
                                        inner join
                                            mt_payments_x_atms_details on miniterminales_payments_x_atms.id=mt_payments_x_atms_details.mt_payments_x_atm_id
                                        inner join
                                            mt_cobranzas_mini_x_atm on mt_cobranzas_mini_x_atm.recibo_id=mt_payments_x_atms_details.recibo_id
                                        where
                                            miniterminales_payments_x_atms.atm_id in " . $atm_id . "
                                    union
                                        select
                                            rc.created_at as fecha,
                                            concat('Descuento por Comision'),
                                            0 as debe,
                                            -abs(mt_recibos.monto) as haber
                                        from
                                            mt_recibos_cobranzas_x_comision
                                        inner join
                                            mt_recibos on mt_recibos.id=mt_recibos_cobranzas_x_comision.recibo_id
                                        inner join
                                            mt_recibos_comisiones_details on mt_recibos.id=mt_recibos_comisiones_details.recibo_id
                                        inner join
                                            mt_recibos_comisiones rc on rc.id=mt_recibos_comisiones_details.recibo_comision_id
                                        inner join
                                            business_groups on business_groups.id=rc.group_id
                                        inner join
                                            branches on business_groups.id=branches.group_id
                                        where
                                            branches.user_id in (" . $user_id . ") and branches.user_id not in(814)
                                )
                                select
                                    balances.*,
                                    sum (balances.haber + balances.debe) over (
                                        order by fecha
                                        rows between unbounded preceding and current row
                                    ) as saldo
                                from
                                    balances
                                where
                                    fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'
                                order by
                                    fecha asc;
                            "));

                            break;

                        case 'transacciones':
                            $baseQuery = \DB::connection('eglobalt_replica')->select(\DB::raw("
                                with balances as (
                                    select
                                        t.created_at as fecha,
                                        case
                                            when t.service_source_id = 0 then
                                                concat(service_providers.name, ' - ', sp.description)
                                            else
                                                concat(sps.description, ' - ', sop.service_description)
                                            end
                                        as concepto,
                                        (
                                            CASE when t.amount > 0 then
                                                abs(t.amount)
                                            else
                                                0
                                            end

                                        ) as debe,
                                        (
                                            CASE 
                                                WHEN status = 'success' and t.amount < 0 and t.service_id != 87 THEN 
                                                    -abs(t.amount)
                                                WHEN status = 'success' and t.amount < 0 and t.service_id = 87 THEN 
                                                    round(abs(t.amount*0.97), 0)
                                            ELSE
                                                0
                                            END
                                        ) as haber
                                    from
                                        transactions t
                                    left join service_provider_products sp on
                                        t.service_id = sp.id
                                        and t.service_source_id = 0
                                    left join service_providers on
                                        service_providers.id = sp.service_provider_id
                                        and t.service_source_id = 0
                                    left join services_providers_sources sps on
                                        t.service_source_id = sps.id
                                        and t.service_source_id <> 0
                                    left join services_ondanet_pairing sop on
                                        t.service_id = sop.service_request_id
                                        and t.service_source_id = sop.service_source_id
                                        and t.service_source_id <> 0
                                    where
                                    (
                                        atm_id in " . $atm_id . "
                                        and status = 'success'
                                        and t.transaction_type in (1, 7)
                                    )or(
                                        atm_id in " . $atm_id . "
                                        and status = 'error'
                                        and t.service_id in(14, 15)
                                        and t.transaction_type in (1, 7)
                                    )
                                )
                                select
                                    balances.*,
                                    sum (balances.haber + balances.debe) over (
                                        order by fecha
                                        rows between unbounded preceding and current row
                                    ) as saldo
                                from
                                    balances
                                where
                                    fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'
                                order by
                                    fecha asc;
                            "));

                            break;

                        case 'reversiones':
                            $baseQuery = \DB::connection('eglobalt_replica')->select(\DB::raw("
                                with balances as (
                                    select
                                        t.created_at as fecha,
                                        concat('Reversion de transaccion ',' ', mt_recibos_reversiones.transaction_id) as concepto,
                                        0 as debe,
                                        -t.amount as haber
                                    from
                                        mt_recibos_reversiones
                                    inner join 
                                        transactions t on t.id= mt_recibos_reversiones.transaction_id
                                    inner join 
                                        mt_recibos on mt_recibos.id= mt_recibos_reversiones.recibo_id
                                    where
                                        atm_id in " . $atm_id . "
                                        and t.transaction_type in (1,7,12)
                                        and mt_recibos.deleted_at is null
                                )
                                select
                                    balances.*,
                                    sum (balances.haber + balances.debe) over (
                                        order by fecha
                                        rows between unbounded preceding and current row
                                    ) as saldo
                                from
                                    balances
                                where
                                    fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'
                                order by
                                    fecha asc;
                            "));

                            break;
                        default:
                            $baseQuery = [];
                            break;
                    }

                    $total_debe = \DB::connection('eglobalt_replica')->select("
                        select
                            SUM(
                                CASE 
                                    WHEN status = 'success' THEN 
                                        abs(t.amount)
                                    WHEN status = 'error' and t.service_id in(14, 15) THEN 
                                        abs(t.amount)
                                    else 0 
                                END
                            ) as total
                            --sum(abs(t.amount)) as total
                        from
                            transactions t
                        left join service_provider_products sp on
                            t.service_id = sp.id
                            and t.service_source_id = 0
                        left join service_providers on
                            service_providers.id = sp.service_provider_id
                            and t.service_source_id = 0
                        left join services_providers_sources sps on
                            t.service_source_id = sps.id
                            and t.service_source_id <> 0
                        left join mt_recibos_reversiones on
		                    t.id = mt_recibos_reversiones.transaction_id
                        left join services_ondanet_pairing sop on
                            t.service_id = sop.service_request_id
                            and t.service_source_id = sop.service_source_id
                            and t.service_source_id <> 0
                        where
                            atm_id in " . $atm_id . "
                            --and status = 'success'
                            and t.transaction_type in (1)
                            and mt_recibos_reversiones.transaction_id is null
                            and t.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'
                    ");

                    $total_haber = \DB::connection('eglobalt_replica')->table('boletas_depositos')
                        ->selectRaw('-sum(monto) as total_haber')
                        ->whereRaw("
                            boletas_depositos.estado = true and
                            fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'
                            and user_id = " . $user_id . "
                    ")->first();

                    $total_pago_mini = \DB::connection('eglobalt_replica')->select("
                        select
                            -sum(mt_cobranzas_mini_x_atm.monto) as total
                        from
                            miniterminales_payments_x_atms
                        inner join
                            mt_payments_x_atms_details on miniterminales_payments_x_atms.id=mt_payments_x_atms_details.mt_payments_x_atm_id
                        inner join
                            mt_cobranzas_mini_x_atm on mt_cobranzas_mini_x_atm.recibo_id=mt_payments_x_atms_details.recibo_id
                        where
                            fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'
                            and miniterminales_payments_x_atms.atm_id in " . $atm_id . "
                    ");

                    $total_descuento = \DB::connection('eglobalt_replica')->select("
                        select
                            -sum(abs(mt_recibos.monto)) as total
                        from
                            mt_recibos_cobranzas_x_comision
                        inner join
                            mt_recibos on mt_recibos.id=mt_recibos_cobranzas_x_comision.recibo_id
                        inner join
                            mt_recibos_comisiones_details on mt_recibos.id=mt_recibos_comisiones_details.recibo_id
                        inner join
                            mt_recibos_comisiones rc on rc.id=mt_recibos_comisiones_details.recibo_comision_id
                        inner join
                            atms on atms.id=rc.atm_id
                        --inner join
                            --branches on business_groups.id=branches.group_id
                        where
                            --branches.user_id in (" . $user_id . ") and branches.user_id not in(814)
                            atm_id in " . $atm_id . "
                            and rc.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'
                    ");

                    $total_reversion = \DB::connection('eglobalt_replica')->select("
                        select
                            -sum(abs(t.amount)) as total
                        from
                            mt_recibos_reversiones
                        inner join 
                                transactions t on t.id= mt_recibos_reversiones.transaction_id
                        inner join 
                            mt_recibos on mt_recibos.id= mt_recibos_reversiones.recibo_id
                        where
                            atm_id in " . $atm_id . "
                            and t.transaction_type in (1,7,12)
                            and mt_recibos_reversiones.fecha_reversion BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'
                            and mt_recibos.deleted_at is null
                    ");

                    $total_cashout = \DB::connection('eglobalt_replica')->select("
                        select
                            -SUM(
                                CASE 
                                    WHEN status = 'success' and t.amount < 0 and t.service_id != 87 THEN 
                                        abs(t.amount)
                                    WHEN status = 'success' and t.amount < 0 and t.service_id = 87 THEN 
                                        round(abs(t.amount*0.97), 0)
                                    else
                                        0
                                END
                            ) as total
                            --sum(abs(t.amount)) as total
                        from
                            transactions t
                        left join service_provider_products sp on
                            t.service_id = sp.id
                            and t.service_source_id = 0
                        left join service_providers on
                            service_providers.id = sp.service_provider_id
                            and t.service_source_id = 0
                        left join services_providers_sources sps on
                            t.service_source_id = sps.id
                            and t.service_source_id <> 0
                        left join services_ondanet_pairing sop on
                            t.service_id = sop.service_request_id
                            and t.service_source_id = sop.service_source_id
                            and t.service_source_id <> 0
                        where
                            atm_id in " . $atm_id . "
                            and t.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'
                            and t.transaction_type in (7)
                    ");

                    $total_pago_cashout = \DB::connection('eglobalt_replica')->table('movements as m')
                        ->selectRaw('SUM(CASE WHEN movement_type_id = 12 THEN (m.amount) else 0 END) as total')
                        ->join('current_account as ca', 'm.id', '=', 'ca.movement_id')
                        ->join('miniterminales_sales as s', 'm.id', '=', 's.movements_id')
                        ->whereRaw("
                            s.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'
                            and ca.group_id = " . $branches[0]->group_id . " and
                            m.movement_type_id in (12) and
                            m.deleted_at is null
                    ")->first();

                    $haber = $total_haber->total_haber + $total_pago_mini[0]->total + $total_descuento[0]->total;
                    $debe = $total_debe[0]->total + $total_pago_cashout->total;
                    $reversion = $total_reversion[0]->total;
                    $cashout = $total_cashout[0]->total;
                } else {

                    if ($input['activar_resumen'] == 2) {
                        $date = date('N');

                        if ($bloqueo_diario) {
                            $hasta = Carbon::parse(date('Y-m-d 23:59:59'))->modify('-1 days');
                        } else {
                            if ($date == 1 || $date == 3 || $date == 5) {
                                $hasta = Carbon::parse(date('Y-m-d 23:59:59'))->modify('-1 days');
                            } else if ($date == 2 || $date == 4 || $date == 6) {
                                $hasta = Carbon::parse(date('Y-m-d 23:59:59'))->modify('-2 days');
                            } else {
                                $hasta = Carbon::parse(date('Y-m-d 23:59:59'))->modify('-3 days');
                            }
                        }
                    } else {

                        $hasta = date('Y-m-d H:i:s');
                    }

                    $fecha_actual = date('Y-m-d H:i:s');
                    /*$total = \DB::connection('eglobalt_pro')->select(\DB::raw("
                        with balances as (
                            select
                                -SUM(
                                    CASE 
                                        WHEN status = 'success' THEN 
                                            abs(t.amount)
                                        WHEN status = 'error' and t.service_id in(14, 15) THEN 
                                            abs(t.amount)
                                        else 0 
                                    END
                                ) as total
                                --sum(abs(t.amount)) as total
                            from
                                transactions t
                            left join service_provider_products sp on
                                t.service_id = sp.id
                                and t.service_source_id = 0
                            left join service_providers on
                                service_providers.id = sp.service_provider_id
                                and t.service_source_id = 0
                            left join services_providers_sources sps on
                                t.service_source_id = sps.id
                                and t.service_source_id <> 0
                            left join mt_recibos_reversiones on
		                        t.id = mt_recibos_reversiones.transaction_id
                            left join services_ondanet_pairing sop on
                                t.service_id = sop.service_request_id
                                and t.service_source_id = sop.service_source_id
                                and t.service_source_id <> 0
                            where
                                atm_id in ".$atm_id."
                                --and status = 'success'
                                and t.transaction_type in (1,7,12)
                                and mt_recibos_reversiones.transaction_id is null
                                and t.created_at <= '{$hasta}'
                            union
                            select
                                sum(bd.monto) as total
                            from
                                boletas_depositos bd
                            where
                                bd.estado = true and
                                user_id = ".$user_id."
                                and fecha <= '{$fecha_actual}'
                        )
                        select
                            coalesce(sum (balances.total), 0) as total
                        from
                            balances;
                    "));*/

                    $total_debe = \DB::connection('eglobalt_pro')->select("
                        select
                            SUM(
                                CASE 
                                    WHEN status = 'success' THEN 
                                        abs(t.amount)
                                    WHEN status = 'error' and t.service_id in(14, 15) THEN 
                                        abs(t.amount)
                                    else 0 
                                END
                            ) as total
                            --sum(abs(t.amount)) as total
                        from
                            transactions t
                        left join service_provider_products sp on
                            t.service_id = sp.id
                            and t.service_source_id = 0
                        left join service_providers on
                            service_providers.id = sp.service_provider_id
                            and t.service_source_id = 0
                        left join services_providers_sources sps on
                            t.service_source_id = sps.id
                            and t.service_source_id <> 0
                        left join mt_recibos_reversiones on
		                    t.id = mt_recibos_reversiones.transaction_id
                        left join services_ondanet_pairing sop on
                            t.service_id = sop.service_request_id
                            and t.service_source_id = sop.service_source_id
                            and t.service_source_id <> 0
                        where
                            atm_id in " . $atm_id . "
                            --and status = 'success'
                            and mt_recibos_reversiones.transaction_id is null
                            and t.transaction_type in (1)
                            and t.created_at <= '{$hasta}'
                    ");

                    $total_haber = \DB::connection('eglobalt_pro')->table('boletas_depositos')
                        ->selectRaw('-sum(monto) as total_haber')
                        ->whereRaw("
                            estado = true and
                            fecha <= '{$fecha_actual}'
                            and user_id = " . $user_id . "
                    ")->first();

                    $total_pago_mini = \DB::connection('eglobalt_replica')->select("
                        select
                            -sum(mt_cobranzas_mini_x_atm.monto) as total
                        from
                            miniterminales_payments_x_atms
                        inner join
                            mt_payments_x_atms_details on miniterminales_payments_x_atms.id=mt_payments_x_atms_details.mt_payments_x_atm_id
                        inner join
                            mt_cobranzas_mini_x_atm on mt_cobranzas_mini_x_atm.recibo_id=mt_payments_x_atms_details.recibo_id
                        where
                            fecha <= '{$fecha_actual}'
                            and miniterminales_payments_x_atms.atm_id in " . $atm_id . "
                    ");

                    $total_descuento = \DB::connection('eglobalt_replica')->select("
                        select
                            -sum(abs(mt_recibos.monto)) as total
                        from
                            mt_recibos_cobranzas_x_comision
                        inner join
                            mt_recibos on mt_recibos.id=mt_recibos_cobranzas_x_comision.recibo_id
                        inner join
                            mt_recibos_comisiones_details on mt_recibos.id=mt_recibos_comisiones_details.recibo_id
                        inner join
                            mt_recibos_comisiones rc on rc.id=mt_recibos_comisiones_details.recibo_comision_id
                        inner join
                            atms on atms.id=rc.atm_id
                        --inner join
                            --branches on business_groups.id=branches.group_id
                        where
                            rc.created_at <= '{$fecha_actual}' and
                            --branches.user_id in (" . $user_id . ") and branches.user_id not in(814)
                            atm_id in " . $atm_id . "
                    ");

                    $total_reversion = \DB::connection('eglobalt_replica')->select("
                        select
                            -sum(abs(t.amount)) as total
                        from
                            mt_recibos_reversiones
                        inner join 
                                transactions t on t.id= mt_recibos_reversiones.transaction_id
                        inner join 
                            mt_recibos on mt_recibos.id= mt_recibos_reversiones.recibo_id
                        where
                            atm_id in " . $atm_id . "
                            and t.transaction_type in (1,7,12)
                            and mt_recibos_reversiones.fecha_reversion <= '{$fecha_actual}'
                            and mt_recibos.deleted_at is null
                    ");

                    $total_cashout = \DB::connection('eglobalt_replica')->select("
                        select
                            -SUM(
                                CASE 
                                    WHEN status = 'success' and t.amount < 0 and t.service_id != 87 THEN 
                                        abs(t.amount)
                                    WHEN status = 'success' and t.amount < 0 and t.service_id = 87 THEN 
                                        round(abs(t.amount*0.97), 0)
                                    else
                                        0
                                END
                            ) as total
                            --sum(abs(t.amount)) as total
                        from
                            transactions t
                        left join service_provider_products sp on
                            t.service_id = sp.id
                            and t.service_source_id = 0
                        left join service_providers on
                            service_providers.id = sp.service_provider_id
                            and t.service_source_id = 0
                        left join services_providers_sources sps on
                            t.service_source_id = sps.id
                            and t.service_source_id <> 0
                        left join services_ondanet_pairing sop on
                            t.service_id = sop.service_request_id
                            and t.service_source_id = sop.service_source_id
                            and t.service_source_id <> 0
                        where
                            atm_id in " . $atm_id . "
                            and t.transaction_type in (7)
                            and t.created_at <= '{$fecha_actual}'
                    ");

                    $total_pago_cashout = \DB::connection('eglobalt_replica')->table('movements as m')
                        ->selectRaw('SUM(CASE WHEN movement_type_id = 12 THEN (m.amount) else 0 END) as total')
                        ->join('current_account as ca', 'm.id', '=', 'ca.movement_id')
                        ->join('miniterminales_sales as s', 'm.id', '=', 's.movements_id')
                        ->whereRaw("
                            m.created_at <= '{$fecha_actual}'
                            and ca.group_id = " . $branches[0]->group_id . " and
                            m.movement_type_id in (12) and
                            m.deleted_at is null
                    ")->first();

                    $haber = $total_haber->total_haber + $total_pago_mini[0]->total + $total_descuento[0]->total;
                    $debe = $total_debe[0]->total + $total_pago_cashout->total;
                    $reversion = $total_reversion[0]->total;
                    $cashout = $total_cashout[0]->total;

                    $baseQuery = [];
                }
            }
            $total_saldo = $haber + $debe + $reversion + $cashout;

            //$results = $this->arrayPaginator($baseQuery, $request);


            $resultset = array(
                'transactions'  => $baseQuery,
                'total_debe' => number_format($debe),
                'total_haber' => number_format($haber),
                'total_saldo' => number_format($total_saldo),
                'mostrar' => $input['mostrar']
            );


            if (!\Sentinel::getUser()->inRole('mini_terminal') && !\Sentinel::getUser()->inRole('supervisor_miniterminal')) {

                $usersNames = \DB::connection('eglobalt_auth')
                    ->table('users')
                    ->selectRaw('concat(username, \' - \', description) as full_name, id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->pluck('full_name', 'id');

                $usersId = \DB::connection('eglobalt_auth')
                    ->table('users')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->pluck('id', 'id');

                $branches = \DB::table('branches')
                    ->select('branches.*')
                    ->whereIn('branches.user_id', $usersId)
                    ->get();

                $data_select = [];
                foreach ($branches as $key => $branch) {
                    $data_select[$branch->user_id] = $branch->description . ' | ' . $usersNames[$branch->user_id];
                }

                $resultset['usersNames'] = $usersNames;
                $resultset['branches'] = $branches;
                $resultset['data_select'] = $data_select;
                $resultset['user_id'] = '';
            }

            if (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                $supervisor = \DB::table('users_x_groups')->where('user_id', $this->user->id)->first();

                $usersNames = \DB::connection('eglobalt_auth')
                    ->table('users')
                    ->selectRaw('concat(username, \' - \', description) as full_name, id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->pluck('full_name', 'id');

                $usersId = \DB::connection('eglobalt_auth')
                    ->table('users')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->pluck('id', 'id');

                $branches = \DB::table('branches')
                    ->select('branches.*')
                    ->whereIn('branches.user_id', $usersId)
                    ->where('branches.group_id', $supervisor->group_id)
                    ->get();

                $data_select = [];
                foreach ($branches as $key => $branch) {
                    $data_select[$branch->user_id] = $branch->description . ' | ' . $usersNames[$branch->user_id];
                }

                $resultset['usersNames'] = $usersNames;
                $resultset['branches'] = $branches;
                $resultset['data_select'] = $data_select;
                $resultset['user_id'] = '';
            }

            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    public function resumenMiniterminalesReports($request)
    {
        try {

            $resultset = array(
                'target' => 'Resumen Mini Terminales Old',
            );

            $usersNames = \DB::connection('eglobalt_auth')
                ->table('users')
                ->selectRaw('concat(username, \' - \', description) as full_name, id')
                ->join('role_users', 'users.id', '=', 'role_users.user_id')
                ->where('role_users.role_id', 22)
                ->pluck('full_name', 'id');

            $usersId = \DB::connection('eglobalt_auth')
                ->table('users')
                ->join('role_users', 'users.id', '=', 'role_users.user_id')
                ->where('role_users.role_id', 22)
                ->pluck('id', 'id');


            if (!\Sentinel::getUser()->inRole('mini_terminal') && !\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                $branches = \DB::table('branches')
                    ->select('branches.*')
                    ->whereIn('branches.user_id', $usersId)
                    ->get();
                $data_select = [];
                foreach ($branches as $key => $branch) {
                    $data_select[$branch->user_id] = $branch->description . ' | ' . $usersNames[$branch->user_id];
                }

                $date = date('N');

                $whereCobranzas = "WHEN movement_type_id = 2 AND movements.created_at <= now() ";
                $whereReversion = "WHEN movement_type_id = 3 AND movements.created_at <= now() ";
                $whereCashout = "WHEN movement_type_id = 11 AND movements.created_at <= now() ";

                if ($date == 1 || $date == 3 || $date == 5) {
                    $hasta_mini = Carbon::parse(date('Y-m-d 23:59:59'))->modify('-1 days');
                } else if ($date == 2 || $date == 4 || $date == 6) {
                    $hasta_mini = Carbon::parse(date('Y-m-d 23:59:59'))->modify('-2 days');
                } else {
                    $hasta_mini = Carbon::parse(date('Y-m-d 23:59:59'))->modify('-3 days');
                }

                $hasta_nano = Carbon::parse(date('Y-m-d 23:59:59'))->modify('-1 days');

                $whereSalesNano = "WHEN debit_credit = 'de' AND (a.owners LIKE '%21%' OR a.owners LIKE '%25%' OR a.grilla != 'true') AND miniterminales_sales.fecha <= '" . $hasta_nano . "'";
                $whereSalesMini = "WHEN debit_credit = 'de' AND (a.owners NOT LIKE '%21%' AND a.owners NOT LIKE '%25%' AND a.grilla = 'true') AND miniterminales_sales.fecha <= '" . $hasta_mini . "'";

                $resumen_transacciones_groups = \DB::connection('eglobalt_replica')->select(\DB::raw("
                    select
                        current_account.group_id as group_id,
                        concat(business_groups.description,' | ',business_groups.ruc) as grupo,
                        a.owners,
                        SUM(
                            CASE " . $whereSalesNano . " THEN (movements.amount) 
                            " . $whereSalesMini . " THEN (movements.amount) 
                            else 0 END
                        ) as transacciones,
                        SUM(CASE " . $whereCobranzas . " THEN (movements.amount) else 0 END) as depositos,
                        SUM(CASE " . $whereReversion . " THEN (movements.amount) else 0 END) as reversiones,
                        SUM(CASE " . $whereCashout . " THEN (movements.amount) else 0 END) as cashouts,
                        (   (SUM(
                            CASE " . $whereSalesNano . " THEN (movements.amount) 
                            " . $whereSalesMini . " THEN (movements.amount) 
                            else 0 END
                            ))
                            +(SUM(CASE " . $whereCobranzas . " THEN (movements.amount) else 0 END))
                            +(SUM(CASE " . $whereReversion . " THEN (movements.amount) else 0 END))
                            +(SUM(CASE " . $whereCashout . " THEN (movements.amount) else 0 END))
                            + (Case when cuota_a.saldo_alquiler != 0 then cuota_a.saldo_alquiler else 0 end)
                            + (Case when cuota_v.saldo_venta != 0 then cuota_v.saldo_venta else 0 end)
                        ) as saldo,
                        (Case when cuota_a.saldo_alquiler != 0 then cuota_a.saldo_alquiler else 0 end)
                        + (Case when cuota_v.saldo_venta != 0 then cuota_v.saldo_venta else 0 end) as cuotas,
                        a.blocks,
                        a.deleted
                        from movements
                        inner join current_account on movements.id = current_account.movement_id
                        inner join business_groups on business_groups.id = current_account.group_id
                        left join miniterminales_sales on movements.id = miniterminales_sales.movements_id
                        left join ( 
                            select business_groups.id as grupo_id, 
                                    string_agg(DISTINCT 
                                            case when atms.deleted_at is null then
                                            atms.owner_id::text
                                            else
                                            '16'::text end, ', ') as owners,
                                    string_agg(DISTINCT 
                                        case when atms.deleted_at is null then
                                            atms.block_type_id::text
                                            else
                                            '0'::text end, ', ') as blocks,
                                    string_agg(DISTINCT
                                        case when atms.deleted_at is null then 
                                        'online'::text
                                        else
                                        atms.deleted_at::text
                                        end
                                    , ', ') as deleted,
                                    string_agg(DISTINCT
                                        case when atms.deleted_at is NOT null OR atms.owner_id not in(16, 21, 25) then  
                                        'true'::text
                                        else
                                        atms.grilla_tradicional::text
                                        end
                                    , ', ') as grilla
                            from business_groups business_groups
                            inner join branches on business_groups.id = branches.group_id
                            inner join points_of_sale pos on branches.id = pos.branch_id
                            inner join atms on atms.id = pos.atm_id
                            group by grupo_id
                        ) a on a.grupo_id = business_groups.id
                        left join (
                            select sum(saldo_cuota) as saldo_alquiler, group_id 
                            from alquiler
                            Inner join cuotas_alquiler on alquiler.id = cuotas_alquiler.alquiler_id
                            Inner join alquiler_housing on alquiler.id = alquiler_housing.alquiler_id
                            where fecha_vencimiento < now() and saldo_cuota <> 0 and alquiler.deleted_at is null and cod_venta is not null
                            group by group_id order by group_id
                        ) cuota_a on business_groups.id = cuota_a.group_id
                        left join (
                            select sum(saldo_cuota) as saldo_venta, group_id 
                            from venta
                            Inner join cuotas on venta.id = cuotas.credito_venta_id
                            Inner join venta_housing on venta.id = venta_housing.venta_id
                            where fecha_vencimiento < now() and saldo_cuota <> 0 and venta.deleted_at is null and cod_venta is not null
                            group by group_id order by group_id
                        ) cuota_v on business_groups.id = cuota_v.group_id
                        where
                            movements.movement_type_id not in (4, 5, 8, 9, 10)
                            and movements.destination_operation_id not in ('0','1','-2','-3','-4','-5','-6','-9','-10','-11','-12','-13','-14','-16','-16','-17','-21','-23','-26',-27,212, 999)
                            and movements.deleted_at is null
                    group by current_account.group_id, grupo, a.owners, a.blocks, a.deleted, cuota_a.saldo_alquiler, cuota_v.saldo_venta
                "));

                foreach ($resumen_transacciones_groups as $resumen_transaccion_group) {

                    if (str_contains($resumen_transaccion_group->deleted, 'online')) {
                        if (
                            str_contains($resumen_transaccion_group->blocks, '1') ||
                            str_contains($resumen_transaccion_group->blocks, '3') ||
                            str_contains($resumen_transaccion_group->blocks, '5') ||
                            str_contains($resumen_transaccion_group->blocks, '7')
                        ) {
                            $resumen_transaccion_group->estado = 'bloqueado';
                        } else {
                            $resumen_transaccion_group->estado = 'activo';
                        }
                    } else {
                        $resumen_transaccion_group->estado = 'inactivo';
                    }
                }

                $resultset['total_debe_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'transacciones')));
                $resultset['total_haber_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'depositos')));
                $resultset['total_reversion_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'reversiones')));
                $resultset['total_cashout_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'cashouts')));
                $resultset['total_cuota_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'cuotas')));
                $resultset['total_saldo_groups'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'saldo')));

                $resultset['usersNames'] = $usersNames;
                $resultset['branches'] = $branches;
                $resultset['data_select'] = $data_select;
                $resultset['user_id'] = '';

                //$resultset['transactions_groups'] = $results_groups;

                $resultset['transactions_groups'] = $resumen_transacciones_groups;
                $resultset['reservationtime'] = (isset($input['reservationtime']) ? $input['reservationtime'] : 0);
            } else if (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {

                $supervisor = \DB::table('users_x_groups')->where('user_id', $this->user->id)->first();
                $usersNames = \DB::connection('eglobalt_auth')
                    ->table('users')
                    ->selectRaw('concat(username, \' - \', description) as full_name, id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->pluck('full_name', 'id');

                $usersId = \DB::connection('eglobalt_auth')
                    ->table('users')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->pluck('id', 'id');

                $branches = \DB::table('branches')
                    ->select('branches.*')
                    ->whereIn('branches.user_id', $usersId)
                    ->where('branches.group_id', $supervisor->group_id)
                    ->get();

                $data_select = [];
                foreach ($branches as $key => $branch) {
                    $data_select[$branch->user_id] = $branch->description . ' | ' . $usersNames[$branch->user_id];
                }

                $resultset['usersNames'] = $usersNames;
                $resultset['branches'] = $branches;
                $resultset['data_select'] = $data_select;
                $resultset['user_id'] = '';
            } else if (\Sentinel::getUser()->inRole('mini_terminal')) {

                //$supervisor = \DB::table('users_x_groups')->where('user_id',$this->user->id)->first();                
                $usersNames = \DB::connection('eglobalt_auth')
                    ->table('users')
                    ->selectRaw('concat(username, \' - \', description) as full_name, id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->pluck('full_name', 'id');

                $usersId = \DB::connection('eglobalt_auth')
                    ->table('users')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->pluck('id', 'id');

                $branches = \DB::table('branches')
                    ->select('branches.*')
                    ->where('branches.user_id', $this->user->id)
                    ->get();

                $data_select = [];
                foreach ($branches as $key => $branch) {
                    $data_select[$branch->user_id] = $branch->description . ' | ' . $usersNames[$branch->user_id];
                }

                $group_id = $branches[0]->group_id;
                $date = date('N');

                $whereCobranzas = "WHEN movement_type_id = 2 AND movements.created_at <= now() ";
                $whereReversion = "WHEN movement_type_id = 3 AND movements.created_at <= now() ";
                $whereCashout = "WHEN movement_type_id = 11 AND movements.created_at <= now() ";

                if ($date == 1 || $date == 3 || $date == 5) {
                    $hasta_mini = Carbon::parse(date('Y-m-d 23:59:59'))->modify('-1 days');
                } else if ($date == 2 || $date == 4 || $date == 6) {
                    $hasta_mini = Carbon::parse(date('Y-m-d 23:59:59'))->modify('-2 days');
                } else {
                    $hasta_mini = Carbon::parse(date('Y-m-d 23:59:59'))->modify('-3 days');
                }

                $hasta_nano = Carbon::parse(date('Y-m-d 23:59:59'))->modify('-1 days');

                $whereSalesNano = "WHEN debit_credit = 'de' AND (a.owners LIKE '%21%' OR a.owners LIKE '%25%' OR a.grilla != 'true') AND miniterminales_sales.fecha <= '" . $hasta_nano . "'";
                $whereSalesMini = "WHEN debit_credit = 'de' AND (a.owners NOT LIKE '%21%' OR a.owners NOT LIKE '%25%' OR a.grilla = 'true') AND miniterminales_sales.fecha <= '" . $hasta_mini . "'";

                $resumen_transacciones_groups = \DB::connection('eglobalt_replica')->select(\DB::raw("
                    select
                        current_account.group_id as group_id,
                        concat(business_groups.description,' | ',business_groups.ruc) as grupo,
                        a.owners,
                        SUM(
                            CASE " . $whereSalesNano . " THEN (movements.amount) 
                            " . $whereSalesMini . " THEN (movements.amount) 
                            else 0 END
                        ) as transacciones,
                        SUM(CASE " . $whereCobranzas . " THEN (movements.amount) else 0 END) as depositos,
                        SUM(CASE " . $whereReversion . " THEN (movements.amount) else 0 END) as reversiones,
                        SUM(CASE " . $whereCashout . " THEN (movements.amount) else 0 END) as cashouts,
                        (   (SUM(
                            CASE " . $whereSalesNano . " THEN (movements.amount) 
                            " . $whereSalesMini . " THEN (movements.amount) 
                            else 0 END
                            ))
                            +(SUM(CASE " . $whereCobranzas . " THEN (movements.amount) else 0 END))
                            +(SUM(CASE " . $whereReversion . " THEN (movements.amount) else 0 END))
                            +(SUM(CASE " . $whereCashout . " THEN (movements.amount) else 0 END))
                            + (Case when cuota_a.saldo_alquiler != 0 then cuota_a.saldo_alquiler else 0 end)
                            + (Case when cuota_v.saldo_venta != 0 then cuota_v.saldo_venta else 0 end)
                        ) as saldo,
                        (Case when cuota_a.saldo_alquiler != 0 then cuota_a.saldo_alquiler else 0 end)
                        + (Case when cuota_v.saldo_venta != 0 then cuota_v.saldo_venta else 0 end) as cuotas,
                        a.blocks,
                        a.deleted
                        from movements
                        inner join current_account on movements.id = current_account.movement_id
                        inner join business_groups on business_groups.id = current_account.group_id
                        left join miniterminales_sales on movements.id = miniterminales_sales.movements_id
                        left join ( 
                            select business_groups.id as grupo_id, 
                                    string_agg(DISTINCT 
                                            case when atms.deleted_at is null then
                                            atms.owner_id::text
                                            else
                                            '16'::text end, ', ') as owners,
                                    string_agg(DISTINCT 
                                        case when atms.deleted_at is null then
                                            atms.block_type_id::text
                                            else
                                            '0'::text end, ', ') as blocks,
                                    string_agg(DISTINCT
                                        case when atms.deleted_at is null then 
                                        'online'::text
                                        else
                                        atms.deleted_at::text
                                        end
                                    , ', ') as deleted,
                                    string_agg(DISTINCT
                                        case when atms.deleted_at is NOT null OR atms.owner_id not in(16, 21, 25) then  
                                        'true'::text
                                        else
                                        atms.grilla_tradicional::text
                                        end
                                    , ', ') as grilla
                            from business_groups business_groups
                            inner join branches on business_groups.id = branches.group_id
                            inner join points_of_sale pos on branches.id = pos.branch_id
                            inner join atms on atms.id = pos.atm_id
                            group by grupo_id
                        ) a on a.grupo_id = business_groups.id
                        left join (
                            select sum(saldo_cuota) as saldo_alquiler, group_id 
                            from alquiler
                            Inner join cuotas_alquiler on alquiler.id = cuotas_alquiler.alquiler_id
                            Inner join alquiler_housing on alquiler.id = alquiler_housing.alquiler_id
                            where fecha_vencimiento < now() and saldo_cuota <> 0 and alquiler.deleted_at is null and cod_venta is not null
                            group by group_id order by group_id
                        ) cuota_a on business_groups.id = cuota_a.group_id
                        left join (
                            select sum(saldo_cuota) as saldo_venta, group_id 
                            from venta
                            Inner join cuotas on venta.id = cuotas.credito_venta_id
                            Inner join venta_housing on venta.id = venta_housing.venta_id
                            where fecha_vencimiento < now() and saldo_cuota <> 0 and venta.deleted_at is null and cod_venta is not null
                            group by group_id order by group_id
                        ) cuota_v on business_groups.id = cuota_v.group_id
                        where
                            current_account.group_id = $group_id and movements.movement_type_id not in (4, 5, 8, 9, 10)
                            and movements.destination_operation_id not in ('0','1','-2','-3','-4','-5','-6','-9','-10','-11','-12','-13','-14','-16','-16','-17','-21','-23','-26',-27,212, 999)
                            and movements.deleted_at is null
                    group by current_account.group_id, grupo, a.owners, a.blocks, a.deleted, cuota_a.saldo_alquiler, cuota_v.saldo_venta
                "));

                foreach ($resumen_transacciones_groups as $resumen_transaccion_group) {

                    if (str_contains($resumen_transaccion_group->deleted, 'online')) {
                        if (
                            str_contains($resumen_transaccion_group->blocks, '1') ||
                            str_contains($resumen_transaccion_group->blocks, '3') ||
                            str_contains($resumen_transaccion_group->blocks, '5') ||
                            str_contains($resumen_transaccion_group->blocks, '7')
                        ) {
                            $resumen_transaccion_group->estado = 'bloqueado';
                        } else {
                            $resumen_transaccion_group->estado = 'activo';
                        }
                    } else {
                        $resumen_transaccion_group->estado = 'inactivo';
                    }
                }

                $resultset['total_debe_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'transacciones')));
                $resultset['total_haber_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'depositos')));
                $resultset['total_reversion_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'reversiones')));
                $resultset['total_cashout_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'cashouts')));
                $resultset['total_cuota_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'cuotas')));
                $resultset['total_saldo_groups'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'saldo')));

                $resultset['usersNames'] = $usersNames;
                $resultset['branches'] = $branches;
                $resultset['data_select'] = $data_select;
                $resultset['user_id'] = '';

                $resultset['transactions_groups'] = $resumen_transacciones_groups;
                $resultset['reservationtime'] = (isset($input['reservationtime']) ? $input['reservationtime'] : 0);
            }
            $resultset['activar_resumen'] = 2;
            return $resultset;
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de reportes" . $e);
            return false;
        }
    }

    public function resumenMiniterminalesSearch($request)
    {
        try {
            $input = $this->input;
            /*Busqueda minusiosa*/
            /*SET DATE RANGE*/
            $whereMovements = '';
            if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
                if (isset($input['reservationtime']) && $input['reservationtime'] != '0') {
                    $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                    $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                    $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                    //$whereMovements = "movements.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereSales = "
                        CASE 
                            WHEN 
                                debit_credit = 'de' AND miniterminales_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' THEN 
                                (movements.amount)
                        ELSE
                            0
                    END";
                    //$whereSales = "WHEN debit_credit = 'de' AND miniterminales_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereCobranzas = "WHEN movement_type_id = 2 AND boletas_depositos.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $wherePagosMinis = "WHEN movement_type_id = 2 AND mt_cobranzas_mini_x_atm.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereDescuentos = "WHEN movement_type_id = 2 AND mt_recibos_cobranzas_x_comision.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereReversion = "WHEN movement_type_id = 3 AND mt_recibos_reversiones.fecha_reversion BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereCashout = "WHEN movement_type_id = 11 AND movements.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";

                    $input['activar_resumen'] = '';
                } else {
                    $whereCobranzas = "WHEN movement_type_id = 2 AND boletas_depositos.fecha <= now() ";
                    $wherePagosMinis = "WHEN movement_type_id = 2 AND mt_cobranzas_mini_x_atm.fecha <= now() ";
                    $whereDescuentos = "WHEN movement_type_id = 2 AND mt_recibos_cobranzas_x_comision.created_at <= now() ";
                    $whereReversion = "WHEN movement_type_id = 3 AND mt_recibos_reversiones.fecha_reversion <= now() ";
                    $whereCashout   = "WHEN movement_type_id = 11 AND movements.created_at <= now() ";
                    if ($input['activar_resumen'] == 2) {
                        $date = date('N');

                        if ($date == 1 || $date == 3 || $date == 5) {
                            $hasta_mini = Carbon::parse(date('Y-m-d 23:59:59'))->modify('-1 days');
                        } else if ($date == 2 || $date == 4 || $date == 6) {
                            $hasta_mini = Carbon::parse(date('Y-m-d 23:59:59'))->modify('-2 days');
                        } else {
                            $hasta_mini = Carbon::parse(date('Y-m-d 23:59:59'))->modify('-3 days');
                        }

                        $hasta_nano = Carbon::parse(date('Y-m-d 23:59:59'))->modify('-1 days');

                        $whereSales = "
                            CASE 
                                WHEN 
                                    debit_credit = 'de' AND (a.owners LIKE '%21%' OR a.owners LIKE '%25%' OR a.grilla != 'true') AND 
                                    miniterminales_sales.fecha <= '$hasta_nano' THEN 
                                    (movements.amount) 
                                WHEN 
                                    debit_credit = 'de' AND (a.owners NOT LIKE '%21%' AND a.owners NOT LIKE '%25%' AND a.grilla = 'true') AND 
                                    miniterminales_sales.fecha <= '$hasta_mini' THEN 
                                    (movements.amount)
                            ELSE
                                0
                        END";

                        //$whereSales = "WHEN debit_credit = 'de' AND miniterminales_sales.fecha <= '". $hasta . "'";
                    } else {
                        $whereSales = "
                            CASE 
                                WHEN debit_credit = 'de' AND movements.created_at <= now() THEN 
                                    (movements.amount) 
                            ELSE
                                0
                        END";
                        //$whereSales = "WHEN debit_credit = 'de' AND movements.created_at <= now() ";
                    }
                }
                $whereMovements .= " LOWER(business_groups.description) like LOWER('%{$input['context']}%') AND";
            } else {
                if (isset($input['reservationtime']) && $input['reservationtime'] != '0') {
                    $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                    $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                    $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                    //$whereMovements = "movements.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    //$whereSales = "WHEN debit_credit = 'de' AND miniterminales_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereSales = "
                            CASE 
                                WHEN 
                                    debit_credit = 'de' AND miniterminales_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' THEN 
                                    (movements.amount)
                            ELSE
                                0
                            END";
                    $whereCobranzas = "WHEN movement_type_id = 2 AND boletas_depositos.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $wherePagosMinis = "WHEN movement_type_id = 2 AND mt_cobranzas_mini_x_atm.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereDescuentos = "WHEN movement_type_id = 2 AND mt_recibos_cobranzas_x_comision.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereReversion = "WHEN movement_type_id = 3 AND mt_recibos_reversiones.fecha_reversion BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereCashout   = "WHEN movement_type_id = 11 AND movements.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $input['activar_resumen'] = '';
                } else {
                    $whereCobranzas = "WHEN movement_type_id = 2 AND boletas_depositos.fecha <= now() ";
                    $wherePagosMinis = "WHEN movement_type_id = 2 AND mt_cobranzas_mini_x_atm.fecha <= now() ";
                    $whereDescuentos = "WHEN movement_type_id = 2 AND mt_recibos_cobranzas_x_comision.created_at <= now() ";
                    $whereReversion = "WHEN movement_type_id = 3 AND movements.created_at <= now() ";
                    $whereCashout   = "WHEN movement_type_id = 11 AND movements.created_at <= now() ";
                    if ($input['activar_resumen'] == 2) {

                        $date = date('N');

                        if ($date == 1 || $date == 3 || $date == 5) {
                            $hasta_mini = Carbon::parse(date('Y-m-d 23:59:59'))->modify('-1 days');
                        } else if ($date == 2 || $date == 4 || $date == 6) {
                            $hasta_mini = Carbon::parse(date('Y-m-d 23:59:59'))->modify('-2 days');
                        } else {
                            $hasta_mini = Carbon::parse(date('Y-m-d 23:59:59'))->modify('-3 days');
                        }

                        $hasta_nano = Carbon::parse(date('Y-m-d 23:59:59'))->modify('-1 days');

                        $whereSales = "
                            CASE 
                                WHEN 
                                    debit_credit = 'de' AND (a.owners LIKE '%21%' a.owners LIKE '%25%' OR a.grilla != 'true') AND 
                                    miniterminales_sales.fecha <= '$hasta_nano' THEN 
                                    (movements.amount) 
                                WHEN 
                                    debit_credit = 'de' AND (a.owners NOT LIKE '%21%' a.owners NOT LIKE '%25%' AND a.grilla = 'true') AND 
                                    miniterminales_sales.fecha <= '$hasta_mini' THEN 
                                    (movements.amount)
                            ELSE
                                0
                        END";

                        /*$whereSalesNano = "WHEN debit_credit = 'de' AND a.owners LIKE '%21%' AND miniterminales_sales.fecha <= '". $hasta_nano . "'";
                        $whereSalesMini = "WHEN debit_credit = 'de' AND a.owners NOT LIKE '%21%' AND miniterminales_sales.fecha <= '". $hasta_mini . "'";*/
                    } else {
                        //$whereSales = "WHEN debit_credit = 'de' AND movements.created_at <= now() ";

                        $whereSales = "
                            CASE 
                                WHEN debit_credit = 'de' AND movements.created_at <= now() THEN 
                                    (movements.amount) 
                            ELSE
                                0
                        END";
                    }
                }
                if (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                    $supervisor = \DB::table('users_x_groups')->where('user_id', $this->user->id)->first();

                    $whereMovements .= "business_groups.id = " . $supervisor->group_id . " AND";
                }

                if (\Sentinel::getUser()->inRole('mini_terminal')) {
                    $branches = \DB::table('branches')->where('user_id', $this->user->id)->first();

                    $whereMovements .= "business_groups.id = " . $branches->group_id . " AND";
                }
            }
            $resumen_transacciones_groups = \DB::connection('eglobalt_replica')->select(\DB::raw("
                select
                        current_account.group_id,
                        business_groups.description as grupo,
                        SUM($whereSales) as transacciones,
                        SUM(CASE " . $whereCobranzas . " THEN (movements.amount) else 0 END) +
                        SUM(CASE " . $wherePagosMinis . " THEN (movements.amount) else 0 END) +
                        SUM(CASE " . $whereDescuentos . " THEN (movements.amount) else 0 END) as depositos,
                        SUM(CASE " . $whereReversion . " THEN (movements.amount) else 0 END) as reversiones,
                        SUM(CASE " . $whereCashout . " THEN (movements.amount) else 0 END) as cashouts,
                        (Case when cuota_a.saldo_alquiler != 0 then cuota_a.saldo_alquiler else 0 end)
                        + (Case when cuota_v.saldo_venta != 0 then cuota_v.saldo_venta else 0 end) as cuotas,
                        a.blocks,
                        a.deleted,
                        (   (SUM($whereSales))
                            +(SUM(CASE " . $whereCobranzas . " THEN (movements.amount) else 0 END))
                            +(SUM(CASE " . $wherePagosMinis . " THEN (movements.amount) else 0 END))
                            +(SUM(CASE " . $whereDescuentos . " THEN (movements.amount) else 0 END))
                            +(SUM(CASE " . $whereReversion . " THEN (movements.amount) else 0 END))
                            +(SUM(CASE " . $whereCashout . " THEN (movements.amount) else 0 END))
                            + (Case when cuota_a.saldo_alquiler != 0 then cuota_a.saldo_alquiler else 0 end)
                            + (Case when cuota_v.saldo_venta != 0 then cuota_v.saldo_venta else 0 end)
                        ) as saldo
                from movements
                inner join current_account on movements.id = current_account.movement_id
                inner join business_groups on business_groups.id = current_account.group_id
                left join miniterminales_sales on movements.id = miniterminales_sales.movements_id                
                left join mt_recibos on mt_recibos.movements_id = movements.id
                left join mt_recibos_cobranzas on mt_recibos.id = mt_recibos_cobranzas.recibo_id 
                left join boletas_depositos on boletas_depositos.id = mt_recibos_cobranzas.boleta_deposito_id                
                left join mt_recibos_reversiones on mt_recibos.id = mt_recibos_reversiones.recibo_id
                left join mt_cobranzas_mini_x_atm on mt_recibos.id = mt_cobranzas_mini_x_atm.recibo_id 
                left join mt_recibos_cobranzas_x_comision on mt_recibos.id = mt_recibos_cobranzas_x_comision.recibo_id
                left join ( 
                    select business_groups.id as grupo_id, 
                            string_agg(DISTINCT 
                                    case when atms.deleted_at is null then
                                    atms.owner_id::text
                                    else
                                    '16'::text end, ', ') as owners,
                            string_agg(DISTINCT 
                                case when atms.deleted_at is null then
                                    atms.block_type_id::text
                                    else
                                    '0'::text end, ', ') as blocks,
                            string_agg(DISTINCT
                                case when atms.deleted_at is null then 
                                'online'::text
                                else
                                atms.deleted_at::text
                                end
                            , ', ') as deleted,
                            string_agg(DISTINCT
                                        case when atms.deleted_at is NOT null OR atms.owner_id not in(16, 21, 25) then  
                                        'true'::text
                                        else
                                        atms.grilla_tradicional::text
                                        end
                            , ', ') as grilla
                    from business_groups business_groups
                    inner join branches on business_groups.id = branches.group_id
                    inner join points_of_sale pos on branches.id = pos.branch_id
                    inner join atms on atms.id = pos.atm_id
                    group by grupo_id
                ) a on a.grupo_id = business_groups.id
                left join (
                    select sum(saldo_cuota) as saldo_alquiler, group_id 
                    from alquiler
                    Inner join cuotas_alquiler on alquiler.id = cuotas_alquiler.alquiler_id
                    Inner join alquiler_housing on alquiler.id = alquiler_housing.alquiler_id
                    where fecha_vencimiento < now() and saldo_cuota <> 0 and alquiler.deleted_at is null and cod_venta is not null
                    group by group_id order by group_id
                ) cuota_a on business_groups.id = cuota_a.group_id
                left join (
                    select sum(saldo_cuota) as saldo_venta, group_id 
                    from venta
                    Inner join cuotas on venta.id = cuotas.credito_venta_id
                    Inner join venta_housing on venta.id = venta_housing.venta_id
                    where fecha_vencimiento < now() and saldo_cuota <> 0 and venta.deleted_at is null and cod_venta is not null
                    group by group_id order by group_id
                ) cuota_v on business_groups.id = cuota_v.group_id
                where
                    " . $whereMovements . "
                    movements.movement_type_id not in (4, 5, 7, 8, 9, 10)
                    and movements.destination_operation_id not in ('0','1','-2','-3','-4','-5','-6','-9','-10','-11','-12','-13','-14','-16','-16','-17','-21','-23','-26',-27,212, 999)
                    and movements.deleted_at is null
                group by current_account.group_id, grupo, a.owners, a.blocks, a.deleted, cuota_a.saldo_alquiler, cuota_v.saldo_venta
                order by saldo desc
            "));

            foreach ($resumen_transacciones_groups as $resumen_transaccion_group) {

                if (str_contains($resumen_transaccion_group->deleted, 'online')) {
                    if (
                        str_contains($resumen_transaccion_group->blocks, '1') ||
                        str_contains($resumen_transaccion_group->blocks, '3') ||
                        str_contains($resumen_transaccion_group->blocks, '5') ||
                        str_contains($resumen_transaccion_group->blocks, '7')
                    ) {
                        $resumen_transaccion_group->estado = 'bloqueado';
                    } else {
                        $resumen_transaccion_group->estado = 'activo';
                    }
                } else {
                    $resumen_transaccion_group->estado = 'inactivo';
                }
            }

            /*$resumen_transacciones_total_groups = \DB::select(\DB::raw("
                select
                    SUM($whereSales) as transacciones,
                    SUM(CASE ".$whereCobranzas." THEN (movements.amount) else 0 END) +
                    SUM(CASE ".$whereDescuentos." THEN (movements.amount) else 0 END) +
                    SUM(CASE ".$wherePagosMinis." THEN (movements.amount) else 0 END) as depositos,
                    SUM(CASE ".$whereReversion." THEN (movements.amount) else 0 END) as reversiones,
                    SUM(CASE ".$whereCashout." THEN (movements.amount) else 0 END) as cashouts
                from movements
                inner join current_account on movements.id = current_account.movement_id
                inner join business_groups on business_groups.id = current_account.group_id
                left join miniterminales_sales on movements.id = miniterminales_sales.movements_id                                
                left join mt_recibos on mt_recibos.movements_id = movements.id
                left join mt_recibos_cobranzas on mt_recibos.id = mt_recibos_cobranzas.recibo_id 
                left join boletas_depositos on boletas_depositos.id = mt_recibos_cobranzas.boleta_deposito_id                
                left join mt_recibos_reversiones on mt_recibos.id = mt_recibos_reversiones.recibo_id
                left join mt_cobranzas_mini_x_atm on mt_recibos.id = mt_cobranzas_mini_x_atm.recibo_id
                left join mt_recibos_cobranzas_x_comision on mt_recibos.id = mt_recibos_cobranzas_x_comision.recibo_id
                left join ( 
                    select business_groups.id as grupo_id, string_agg(DISTINCT atms.owner_id::text, ', ') as owners
                    from business_groups business_groups
                    inner join branches on business_groups.id = branches.group_id
                    inner join points_of_sale pos on branches.id = pos.branch_id
                    inner join atms on atms.id = pos.atm_id
                    group by grupo_id
                ) a on a.grupo_id = business_groups.id
                where
                    ".$whereMovements."
                    movements.movement_type_id not in (4, 5, 7, 8, 9, 10)
                    and movements.destination_operation_id not in ('0','1','-2','-3','-4','-5','-6','-9','-10','-11','-12','-13','-14','-16','-16','-17','-21','-23','-26',-27,212, 999)
                    and movements.deleted_at is null
            "));

            $resumen_transacciones_total_groups[0]->saldo=$resumen_transacciones_total_groups[0]->transacciones + $resumen_transacciones_total_groups[0]->depositos + $resumen_transacciones_total_groups[0]->reversiones + $resumen_transacciones_total_groups[0]->cashouts;

            $results_groups = $this->arrayPaginator($resumen_transacciones_groups, $request);*/

            $resultset = array(
                'target'        => 'Resumen Mini Terminales Old',
                'transactions_groups'  => $resumen_transacciones_groups,
                'reservationtime' => (isset($input['reservationtime']) ? $input['reservationtime'] : 0),
                'i'             =>  1,
                'activar_resumen' => $input['activar_resumen']
            );

            $resultset['total_debe_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'transacciones')));
            $resultset['total_haber_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'depositos')));
            $resultset['total_reversion_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'reversiones')));
            $resultset['total_cashout_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'cashouts')));
            $resultset['total_cuota_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'cuotas')));
            $resultset['total_saldo_groups'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'saldo')));
            //dd($resultset);
            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    public function getBranchfroGroup($group_id, $fecha)
    {
        \Log::info($fecha);
        if (isset($fecha) && $fecha != '0' && $fecha != '2') {
            $daterange = explode('-',  str_replace('/', '-', $fecha));
            $daterange[0] = date('Y-m-d H:i:s', ($daterange[0] / 1000));
            $daterange[1] = date('Y-m-d H:i:s', ($daterange[1] / 1000));

            $whereTransactions = "t.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
            $whereBoletas = "fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
            $whereMinisPagos = "fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
            $whereMiniDescuento = "rc.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
            $whereVencimiento = "fecha_vencimiento BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
            //$whereTransactions .= " AND branches.group_id = ". $group_id;

            $usersId = \DB::table('users')
                ->join('role_users', 'users.id', '=', 'role_users.user_id')
                ->where('role_users.role_id', 22)
                ->pluck('id', 'id');

            $user_id = '(' . implode(',', $usersId) . ')';

            $total_debe = "
                select
                    sum(abs(t.amount)) as total,
                    branches.description as branch,
                    branches.id as branch_id 
                from
                    transactions t
                inner join points_of_sale on 
                    points_of_sale.atm_id = t.atm_id and points_of_sale.deleted_at is null 
                inner join branches on 
                    branches.id = points_of_sale.branch_id and branches.group_id = " . $group_id . "
                inner join atms on
                    atms.id = points_of_sale.atm_id
                where 
                    ( 
                    status in ('success', 'error') 
                    and t.service_id in(14, 15) 
                    and t.transaction_type in (1)
                    and atms.owner_id in (16, 21, 25)
                    and t.service_source_id=8
                    and t.amount > 0
                    and " . $whereTransactions . "
                )
                or ( 
                    status = 'success'
                    and t.transaction_type in (1)
                    and atms.owner_id in (16, 21, 25)
                    and t.amount > 0
                    and " . $whereTransactions . "
                    )
                group by branches.id
            ";

            $total_depositado = "
                select
                    -sum(bd.monto) as total,
                    branches.description as branch,
                    branches.id as branch_id 
                from
                    boletas_depositos bd
                inner join
                    branches on branches.user_id=bd.user_id
                where
                    bd.estado = true and
                    branches.group_id = $group_id
                    and " . $whereBoletas . "
                group by branches.id
            ";

            $total_pago_mini = "
                select
                    -sum(mt_cobranzas_mini_x_atm.monto) as total,
                    branches.description as branch,
                    branches.id as branch_id 
                from
                    mt_cobranzas_mini_x_atm
                inner join
                    miniterminales_payments_x_atms on mt_cobranzas_mini_x_atm.transaction_id=miniterminales_payments_x_atms.transaction_id
                inner join
                    business_groups on business_groups.id=miniterminales_payments_x_atms.group_id
                inner join
                    branches on business_groups.id=branches.group_id
                where
                    branches.group_id = " . $group_id . "
                    and " . $whereMinisPagos . "
                group by branches.id
            ";

            $total_descuento = "
                select
                    -sum(abs(mt_recibos.monto)) as total,
                    branches.description as branch,
                    branches.id as branch_id
                from
                    mt_recibos_cobranzas_x_comision
                inner join
                    mt_recibos on mt_recibos.id=mt_recibos_cobranzas_x_comision.recibo_id
                inner join
                    mt_recibos_comisiones_details on mt_recibos.id=mt_recibos_comisiones_details.recibo_id
                inner join
                    mt_recibos_comisiones rc on rc.id=mt_recibos_comisiones_details.recibo_comision_id
                inner join
                    business_groups on business_groups.id=rc.group_id
                inner join
                    branches on business_groups.id=branches.group_id
                where
                    branches.group_id = " . $group_id . "
                    and " . $whereMiniDescuento . "
                group by branches.id
            ";

            $total_reversion = "
                select
                    -sum(abs(t.amount)) as total,
                    branches.description as branch,
                    branches.id as branch_id 
                from
                    mt_recibos_reversiones
                inner join 
                        transactions t on t.id= mt_recibos_reversiones.transaction_id
                inner join 
                    mt_recibos on mt_recibos.id= mt_recibos_reversiones.recibo_id
                inner join points_of_sale on 
                    points_of_sale.atm_id = t.atm_id and points_of_sale.deleted_at is null 
                inner join branches on 
                    branches.id = points_of_sale.branch_id and branches.group_id = " . $group_id . "
                inner join atms on
                    atms.id = points_of_sale.atm_id
                where 
                    t.transaction_type in (1,7,12)
                    and mt_recibos.deleted_at is null
                    and " . $whereTransactions . "
                group by branches.id
            ";

            $total_cashout = "
                select
                    -SUM(
                        CASE 
                            WHEN status = 'success' and t.amount < 0 and t.service_id != 87 THEN 
                                abs(t.amount)
                            WHEN status = 'success' and t.amount < 0 and t.service_id = 87 THEN 
                                round(abs(t.amount*0.97), 0)
                            else
                                0
                        END
                    ) as total,
                    branches.description as branch,
                    branches.id as branch_id 
                from
                    transactions t
                inner join points_of_sale on 
                    points_of_sale.atm_id = t.atm_id and points_of_sale.deleted_at is null 
                inner join branches on 
                    branches.id = points_of_sale.branch_id and branches.group_id = " . $group_id . "
                inner join atms on
                    atms.id = points_of_sale.atm_id
                where 
                    status = 'success'
                    and t.transaction_type in (7)
                    and atms.owner_id in (16, 21, 25)
                    and " . $whereTransactions . "
                group by branches.id
            ";

            $resumen_transacciones = \DB::connection('eglobalt_replica')->select(\DB::raw("
                with balances as (
                    " . $total_debe . "
                    union
                    " . $total_depositado . "
                    union
                    " . $total_pago_mini . "
                    union
                    " . $total_descuento . "
                    union
                    " . $total_reversion . "
                    union
                    " . $total_cashout . "
                )
                select
                    atms.id,
                    block_type.description,
                    atms.block_type_id, 
                    balances.branch as name,
                    atms.deleted_at as eliminado,
                    coalesce(sum(
                        balances.total
                    ), 0) +
                    (
                        CASE WHEN cuotas_a.saldo_alquiler <> 0 THEN
                        cuotas_a.saldo_alquiler
                        ELSE
                        0
                        END
                    )+(
                        CASE WHEN cuotas_v.saldo_venta <> 0 THEN
                        cuotas_v.saldo_venta
                        ELSE
                        0
                        END
                    ) as saldo
                from
                    balances
                inner join points_of_sale on balances.branch_id = points_of_sale.branch_id
                inner join atms on atms.id = points_of_sale.atm_id
                inner join block_type on block_type.id = atms.block_type_id
                left join (
                    select sum(saldo_cuota) as saldo_alquiler, atms.id as atm_id from alquiler
                    inner join cuotas_alquiler on alquiler.id = cuotas_alquiler.alquiler_id
                    inner join alquiler_housing on alquiler.id = alquiler_housing.alquiler_id
                    inner join housing on housing.id = alquiler_housing.housing_id
                    inner join atms on housing.id = atms.housing_id
                    where fecha_vencimiento < now() AND saldo_cuota <> 0 AND alquiler.deleted_at is null and cod_venta is not null and alquiler.group_id = $group_id
                    group by atms.id
                ) cuotas_a on atms.id = cuotas_a.atm_id
                left join (
                    select sum(saldo_cuota) as saldo_venta, atms.id as atm_id from venta
                    inner join cuotas on venta.id = cuotas.credito_venta_id
                    inner join venta_housing on venta.id = venta_housing.venta_id
                    inner join housing on housing.id = venta_housing.venta_id
                    inner join atms on housing.id = atms.housing_id
                    where fecha_vencimiento < now() AND saldo_cuota <> 0 AND venta.deleted_at is null and cod_venta is not null and venta.group_id = $group_id
                    group by atms.id
                ) cuotas_v on atms.id = cuotas_v.atm_id
                group by atms.id, block_type.description, balances.branch, cuotas_a.saldo_alquiler, cuotas_v.saldo_venta
            "));
        } else {

            $date = Carbon::now()->format('Y-m-d H:i:s');

            if ($fecha != '0') {
                $transaccionado = "total_transaccionado_cierre";
            } else {
                $transaccionado = "total_transaccionado";
            }

            $resumen_transacciones = \DB::connection('eglobalt_replica')->table('atms')
                /*->selectRaw("atms.id, name, last_request_at, block_type.description, sum(total_depositado + total_transaccionado_cierre + total_reversado + total_cashout + total_pago_cashout) as saldo, block_type_id")*/
                ->selectRaw("atms.id, name, last_request_at, block_type.description, block_type_id, atms.deleted_at as eliminado, (total_depositado + $transaccionado + total_reversado + total_cashout + total_pago_cashout) +
                SUM(
                    CASE WHEN cuotas_alquiler.fecha_vencimiento < '$date' AND cuotas_alquiler.cod_venta is not null AND cuotas_alquiler.saldo_cuota <> 0 AND alquiler.deleted_at is null THEN
                    cuotas_alquiler.saldo_cuota
                    ELSE
                    0
                    END
                ) +
                SUM(
                    CASE WHEN cuotas.fecha_vencimiento < '$date' AND cuotas.saldo_cuota <> 0 AND venta.deleted_at is null THEN
                        cuotas.saldo_cuota
                    ELSE
                        0
                    END
                ) as saldo")
                ->join('block_type', 'block_type.id', '=', 'atms.block_type_id')
                ->join('balance_atms', 'atms.id', '=', 'balance_atms.atm_id')
                ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->leftjoin('alquiler_housing', 'atms.housing_id', '=', 'alquiler_housing.housing_id')
                //->leftjoin('alquiler','alquiler.id','=','alquiler_housing.alquiler_id')
                ->leftjoin('alquiler', function ($join) use ($group_id) {
                    $join->on('alquiler.id', '=', 'alquiler_housing.alquiler_id')
                        ->where('alquiler.group_id', '=', $group_id);
                })
                ->leftjoin('cuotas_alquiler', 'alquiler.id', '=', 'cuotas_alquiler.alquiler_id')
                ->leftjoin('venta_housing', 'atms.housing_id', '=', 'venta_housing.housing_id')
                ->leftjoin('venta', function ($join) use ($group_id) {
                    $join->on('venta.id', '=', 'venta_housing.venta_id')
                        ->where('venta.group_id', '=', $group_id);
                })
                ->leftjoin('cuotas', 'venta.id', '=', 'cuotas.credito_venta_id')
                ->whereIn('atms.owner_id', [16, 21, 25])
                //->where('atms.deleted_at', null)
                ->where('branches.group_id', $group_id)
                ->groupBy('atms.id', 'block_type.description', $transaccionado, 'total_depositado', 'total_reversado', 'total_cashout', 'total_pago_cashout')
                ->orderBy('atms.id', 'asc')
                ->get();
        }


        $details = '';
        $sum = 0;
        foreach ($resumen_transacciones as $transaction) {

            if (is_null($transaction->eliminado)) {
                $fecha = \DB::connection('eglobalt_replica')->table('transactions')
                    ->selectRaw("created_at")
                    ->where('atm_id', $transaction->id)
                    ->orderBy('transactions.id', 'desc')
                    ->first();

                $date = $fecha->created_at;
            } else {
                $date = $transaction->eliminado;
            }

            $sum += $transaction->saldo;

            if (is_null($transaction->eliminado)) {
                if ($transaction->block_type_id == 0) {
                    $descripcion = '<span class="label label-success">Activo</span>';
                } else {
                    $descripcion = '<span class="label label-danger">' . $transaction->description . '</span>';
                }
            } else {
                $descripcion = '<span class="label label-warning">Inactivo</span>';
            }

            if ($transaction->saldo > 0) {
                $style = "color:red";
            } else {
                $style = "color:green";
            }

            $details .= '<tr>
                <td>' . $transaction->id . '</td>
                <td>' . $transaction->name . '</td>
                <td>' . Carbon::parse($date)->format('d/m/Y H:i:s') . '</td>
                <td style=' . $style . '>' . number_format($transaction->saldo, 0) . '</td>
                <td>' . $descripcion . '</td>
                </tr>';
        }

        $details .= "<br><br><tr>
        <td colspan='4'> <h4><label> Saldo total: " . number_format($sum, 0) . ' Gs.</label> <h4> </td>
        </tr>';

        return $details;
    }

    public function getCuotasForGroups($group_id)
    {

        $cuotas_alquiler = \DB::connection('eglobalt_replica')
            ->table('alquiler')
            ->selectRaw(" ('Alquiler #' || alquiler.id) as tipo, bg.description as name, num_cuota, saldo_cuota, fecha_vencimiento")
            ->join('cuotas_alquiler', 'alquiler.id', '=', 'cuotas_alquiler.alquiler_id')
            ->join('business_groups as bg', 'bg.id', '=', 'alquiler.group_id')
            ->join('alquiler_housing', 'alquiler.id', '=', 'alquiler_housing.alquiler_id')
            //->join('atms', 'atms.housing_id', '=', 'alquiler_housing.housing_id')
            ->where('alquiler.group_id', $group_id)
            ->whereRaw('fecha_vencimiento < now()')
            ->where('saldo_cuota', '<>', 0)
            ->whereNull('alquiler.deleted_at')
            ->whereNotNull('cuotas_alquiler.cod_venta')
            ->orderBy('cuotas_alquiler.num_cuota', 'ASC')
        ->get();

        $details = '';

        foreach ($cuotas_alquiler as $cuota_a) {
            $details .= '<tr>
                <td>' . $cuota_a->tipo . '</td>
                <td>' . $cuota_a->name . '</td>
                <td>' .  date('d/m/Y', strtotime($cuota_a->fecha_vencimiento)) . '</td>
                <td>' . $cuota_a->num_cuota . '</td>
                <td>' . number_format($cuota_a->saldo_cuota, 0) . '</td>
                </tr>';
        }

        $cuotas_ventas = \DB::connection('eglobalt_replica')
            ->table('venta')
            ->selectRaw(" ('Venta #' || venta.id) as tipo, bg.description as name, numero_cuota, saldo_cuota, fecha_vencimiento")
            ->join('cuotas', 'venta.id', '=', 'cuotas.credito_venta_id')
            ->join('business_groups as bg', 'bg.id', '=', 'venta.group_id')
            ->join('venta_housing', 'venta.id', '=', 'venta_housing.venta_id')
            //->join('atms', 'atms.housing_id', '=', 'venta_housing.housing_id')
            ->where('venta.group_id', $group_id)
            ->whereRaw('fecha_vencimiento < now()')
            ->where('saldo_cuota', '<>', 0)
            ->whereNull('venta.deleted_at')
            ->whereNotNull('cuotas.cod_venta')
            ->orderBy('cuotas.numero_cuota', 'ASC')
        ->get();

        foreach ($cuotas_ventas as $cuota_v) {

            $details .= '<tr>
                <td>' . $cuota_v->tipo . '</td>
                <td>' . $cuota_v->name . '</td>
                <td>' .  date('d/m/Y', strtotime($cuota_v->fecha_vencimiento)) . '</td>
                <td>' . $cuota_v->numero_cuota . '</td>
                <td>' . number_format($cuota_v->saldo_cuota, 0) . '</td>
                </tr>';
        }

        return $details;
    }

    public function resumenMiniterminalesSearchExport()
    {
        try {
            $input = $this->input;
            /*Busqueda minusiosa*/
            /*SET DATE RANGE*/
            $whereMovements = '';
            if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
                if (isset($input['reservationtime']) && $input['reservationtime'] != '0') {
                    $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                    $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                    $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                    //$whereMovements = "movements.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    //$whereSales = "WHEN debit_credit = 'de' AND miniterminales_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereSales = "
                        CASE 
                            WHEN 
                                debit_credit = 'de' AND miniterminales_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' THEN 
                                (movements.amount)
                        ELSE
                            0
                    END";
                    $whereCobranzas = "WHEN movement_type_id = 2 AND boletas_depositos.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $wherePagosMinis = "WHEN movement_type_id = 2 AND mt_cobranzas_mini_x_atm.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereDescuentos = "WHEN movement_type_id = 2 AND mt_recibos_cobranzas_x_comision.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereReversion = "WHEN movement_type_id = 3 AND mt_recibos_reversiones.fecha_reversion BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereCashout   = "WHEN movement_type_id = 11 AND movements.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereTransactions = "t.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereBoletas = "fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereMinisPagos = "fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereMiniDescuento = "rc.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                } else {
                    //$whereSales = "WHEN debit_credit = 'de' AND movements.created_at <= now() ";
                    $whereSales = "
                        CASE 
                            WHEN debit_credit = 'de' AND movements.created_at <= now() THEN 
                                (movements.amount) 
                        ELSE
                            0
                    END";
                    $whereCobranzas = "WHEN movement_type_id = 2 AND boletas_depositos.fecha <= now()";
                    $wherePagosMinis = "WHEN movement_type_id = 2 AND mt_cobranzas_mini_x_atm.fecha <= now() ";
                    $whereDescuentos = "WHEN movement_type_id = 2 AND mt_recibos_cobranzas_x_comision.created_at <= now() ";
                    $whereReversion = "WHEN movement_type_id = 3 AND mt_recibos_reversiones.fecha_reversion <= now()";
                    $whereCashout   = "WHEN movement_type_id = 11 AND movements.created_at <= now()";
                    $whereTransactions = "t.created_at <= now()";
                    $whereBoletas = "fecha <= now()";
                    $whereMinisPagos = "fecha <= now()";
                    $whereMiniDescuento = "rc.created_at <= now()";
                }
                $whereMovements .= " LOWER(business_groups.description) like LOWER('%{$input['context']}%') AND";
            } else {
                if (isset($input['reservationtime']) && $input['reservationtime'] != '0') {
                    $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                    $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                    $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                    //$whereMovements = "movements.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    //$whereSales = "WHEN debit_credit = 'de' AND miniterminales_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereSales = "
                        CASE 
                            WHEN 
                                debit_credit = 'de' AND miniterminales_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' THEN 
                                (movements.amount)
                        ELSE
                            0
                    END";
                    $whereCobranzas = "WHEN movement_type_id = 2 AND boletas_depositos.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $wherePagosMinis = "WHEN movement_type_id = 2 AND mt_cobranzas_mini_x_atm.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereDescuentos = "WHEN movement_type_id = 2 AND mt_recibos_cobranzas_x_comision.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereReversion = "WHEN movement_type_id = 3 AND mt_recibos_reversiones.fecha_reversion BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereCashout = "WHEN movement_type_id = 11 AND movements.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";

                    $whereTransactions = "t.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereBoletas = "fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereMinisPagos = "fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereMiniDescuento = "rc.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                } else {
                    $whereCobranzas = "WHEN movement_type_id = 2 AND boletas_depositos.fecha <= now() ";
                    $wherePagosMinis = "WHEN movement_type_id = 2 AND mt_cobranzas_mini_x_atm.fecha <= now() ";
                    $whereDescuentos = "WHEN movement_type_id = 2 AND mt_recibos_cobranzas_x_comision.created_at <= now() ";
                    $whereReversion = "WHEN movement_type_id = 3 AND mt_recibos_reversiones.fecha_reversion <= now() ";
                    $whereCashout   = "WHEN movement_type_id = 11 AND movements.created_at <= now() ";
                    if ($input['activar_resumen'] == 2) {
                        $date = date('N');

                        if ($date == 1 || $date == 3 || $date == 5) {
                            $hasta_mini = Carbon::parse(date('Y-m-d 23:59:59'))->modify('-1 days');
                        } else if ($date == 2 || $date == 4 || $date == 6) {
                            $hasta_mini = Carbon::parse(date('Y-m-d 23:59:59'))->modify('-2 days');
                        } else {
                            $hasta_mini = Carbon::parse(date('Y-m-d 23:59:59'))->modify('-3 days');
                        }

                        $hasta_nano = Carbon::parse(date('Y-m-d 23:59:59'))->modify('-1 days');

                        //$whereSales = "WHEN debit_credit = 'de' AND miniterminales_sales.fecha <= '". $hasta . "'";
                        $whereSales = "
                            CASE 
                                WHEN 
                                    debit_credit = 'de' AND (a.owners LIKE '%21%' OR a.owners LIKE '%25%' OR a.grilla != 'true') AND 
                                    miniterminales_sales.fecha <= '$hasta_nano' THEN 
                                    (movements.amount) 
                                WHEN 
                                    debit_credit = 'de' AND (a.owners NOT LIKE '%21%' AND a.owners NOT LIKE '%25%' AND a.grilla = 'true') AND 
                                    miniterminales_sales.fecha <= '$hasta_mini' THEN 
                                    (movements.amount)
                            ELSE
                                0
                        END";
                        $whereTransactions = "t.created_at <= '" . $hasta_mini . "'";
                        $whereMinisPagos = "fecha <= '" . $hasta_mini . "'";
                        $whereMiniDescuento = "rc.created_at <= '" . $hasta_mini . "'";
                        $whereBoletas = "fecha <= '" . $hasta_mini . "'";
                    } else {
                        //$whereSales = "WHEN debit_credit = 'de' AND movements.created_at <= now() ";
                        $whereSales = "
                            CASE 
                                WHEN debit_credit = 'de' AND movements.created_at <= now() THEN 
                                    (movements.amount) 
                            ELSE
                                0
                        END";
                        $whereTransactions = "t.created_at <= now()";
                        $whereMinisPagos = "fecha <= now()";
                        $whereMiniDescuento = "rc.created_at <= now()";
                        $whereBoletas = "fecha <= now()";
                    }
                }
                if (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                    $supervisor = \DB::table('users_x_groups')->where('user_id', $this->user->id)->first();

                    $whereMovements .= "business_groups.id = " . $supervisor->group_id . " AND";
                    $whereTransactions .= " AND branches.group_id = " . $supervisor->group_id;
                    $whereBoletas .= " AND branches.group_id = " . $supervisor->group_id;
                    $whereMinisPagos .= " AND branches.group_id = " . $supervisor->group_id;
                    $whereMiniDescuento .= " AND branches.group_id = " . $supervisor->group_id;
                }
            }

            $usersId = \DB::table('users')
                ->join('role_users', 'users.id', '=', 'role_users.user_id')
                ->where('role_users.role_id', 22)
                ->pluck('id', 'id');

            $user_id = '(' . implode(',', $usersId) . ')';

            $total_debe = "
                select
                    'transacciones' as tipo,
                    sum(abs(t.amount)) as total,
                    branches.description as branch,
                    branches.id as branch_id 
                from
                    transactions t
                left join service_provider_products sp on
                    t.service_id = sp.id 
                    and t.service_source_id = 0
                left join service_providers on
                    service_providers.id = sp.service_provider_id 
                    and t.service_source_id = 0
                left join services_providers_sources sps on 
                    t.service_source_id = sps.id 
                    and t.service_source_id <> 0
                left join services_ondanet_pairing sop on
                    t.service_id = sop.service_request_id 
                    and t.service_source_id = sop.service_source_id 
                    and t.service_source_id <> 0
                inner join points_of_sale on 
                    points_of_sale.atm_id = t.atm_id and points_of_sale.deleted_at is null 
                inner join branches on 
                    branches.id = points_of_sale.branch_id and branches.user_id in " . $user_id . "
                inner join atms on
                    atms.id = points_of_sale.atm_id
                where 
                    ( 
                    status in ('success', 'error') 
                    and t.service_id in(14, 15) 
                    and t.transaction_type in (1)
                    and atms.owner_id in (16, 21, 25)
                    and t.service_source_id=8
                    and t.amount > 0
                    and " . $whereTransactions . "
                )
                or ( 
                    status = 'success'
                    and t.transaction_type in (1)
                    and atms.owner_id in (16, 21, 25)
                    and t.amount > 0
                    and " . $whereTransactions . "
                    )
                group by branches.id
            ";

            $total_depositado = "
                select
                    'deposito' as tipo,
                    -sum(bd.monto) as total,
                    branches.description as branch,
                    branches.id as branch_id 
                from
                    boletas_depositos bd
                inner join
                    branches on branches.user_id=bd.user_id
                where
                    bd.estado = true and
                    branches.user_id in " . $user_id . "
                    and " . $whereBoletas . "
                group by branches.id
            ";

            $total_pago_mini = "
                select
                    'deposito' as tipo,
                    -sum(mt_cobranzas_mini_x_atm.monto) as total,
                    branches.description as branch,
                    branches.id as branch_id 
                from
                    mt_cobranzas_mini_x_atm
                inner join
                    miniterminales_payments_x_atms on mt_cobranzas_mini_x_atm.transaction_id=miniterminales_payments_x_atms.transaction_id
                inner join
                    business_groups on business_groups.id=miniterminales_payments_x_atms.group_id
                inner join
                    branches on business_groups.id=branches.group_id
                where
                    branches.user_id in " . $user_id . "
                    and " . $whereMinisPagos . "
                group by branches.id
            ";

            $total_descuento = "
                select
                    'deposito' as tipo,
                    -sum(abs(mt_recibos.monto)) as total,
                    branches.description as branch,
                    branches.id as branch_id
                from
                    mt_recibos_cobranzas_x_comision
                inner join
                    mt_recibos on mt_recibos.id=mt_recibos_cobranzas_x_comision.recibo_id
                inner join
                    mt_recibos_comisiones_details on mt_recibos.id=mt_recibos_comisiones_details.recibo_id
                inner join
                    mt_recibos_comisiones rc on rc.id=mt_recibos_comisiones_details.recibo_comision_id
                inner join
                    business_groups on business_groups.id=rc.group_id
                inner join
                    branches on business_groups.id=branches.group_id
                where
                    branches.user_id in " . $user_id . "
                    and " . $whereMiniDescuento . "
                group by branches.id
            ";

            $total_reversion = "
                select
                    'reversion' as tipo,
                    -sum(abs(t.amount)) as total,
                    branches.description as branch,
                    branches.id as branch_id 
                from
                    mt_recibos_reversiones
                inner join 
                        transactions t on t.id= mt_recibos_reversiones.transaction_id
                inner join 
                    mt_recibos on mt_recibos.id= mt_recibos_reversiones.recibo_id
                inner join points_of_sale on 
                    points_of_sale.atm_id = t.atm_id and points_of_sale.deleted_at is null 
                inner join branches on 
                    branches.id = points_of_sale.branch_id and branches.user_id in " . $user_id . "
                inner join atms on
                    atms.id = points_of_sale.atm_id
                where 
                    t.transaction_type in (1,7,12)
                    and mt_recibos.deleted_at is null
                    and " . $whereTransactions . "
                group by branches.id
            ";

            $total_cashout = "
                select
                    'cashout' as tipo,
                    -SUM(
                        CASE 
                            WHEN status = 'success' and t.amount < 0 and t.service_id != 87 THEN 
                                abs(t.amount)
                            WHEN status = 'success' and t.amount < 0 and t.service_id = 87 THEN 
                                round(abs(t.amount*0.97), 0)
                            else
                                0
                        END
                    ) as total,
                    branches.description as branch,
                    branches.id as branch_id 
                from
                    transactions t
                left join service_provider_products sp on
                    t.service_id = sp.id 
                    and t.service_source_id = 0
                left join service_providers on
                    service_providers.id = sp.service_provider_id 
                    and t.service_source_id = 0
                left join services_providers_sources sps on 
                    t.service_source_id = sps.id 
                    and t.service_source_id <> 0
                left join services_ondanet_pairing sop on
                    t.service_id = sop.service_request_id 
                    and t.service_source_id = sop.service_source_id 
                    and t.service_source_id <> 0
                inner join points_of_sale on 
                    points_of_sale.atm_id = t.atm_id and points_of_sale.deleted_at is null 
                inner join branches on 
                    branches.id = points_of_sale.branch_id and branches.user_id in " . $user_id . "
                inner join atms on
                    atms.id = points_of_sale.atm_id
                where 
                    status = 'success'
                    and t.transaction_type in (7)
                    and atms.owner_id in (16, 21, 25)
                    and " . $whereTransactions . "
                group by branches.id
            ";

            $resumen_transacciones = \DB::connection('eglobalt_replica')->select(\DB::raw("
                with balances as (
                    " . $total_debe . "
                    union
                    " . $total_depositado . "
                    union
                    " . $total_pago_mini . "
                    union
                    " . $total_descuento . "
                    union
                    " . $total_reversion . "
                    union
                    " . $total_cashout . "
                )
                select
                    block_type.description,
                    atms.block_type_id, 
                    balances.branch as name,
                    atms.deleted_at as eliminado,
                    coalesce(sum(
                        case 
                            when tipo = 'transacciones' then            
                                balances.total
                        end
                    ), 0) as transacciones,
                    coalesce(sum(
                        case 
                            when tipo = 'deposito' then             
                                balances.total
                        end
                    ), 0) as depositos,
                    coalesce(sum(
                        case 
                            when tipo = 'reversion' then             
                                balances.total
                        end
                    ), 0) as reversiones,
                    coalesce(sum(
                        case 
                            when tipo = 'cashout' then             
                                balances.total
                        end
                    ), 0) as cashouts
                from
                    balances
                inner join points_of_sale on balances.branch_id = points_of_sale.branch_id
                inner join atms on atms.id = points_of_sale.atm_id
                inner join block_type on block_type.id = atms.block_type_id
                group by atms.id, block_type.description, balances.branch
            "));

            foreach ($resumen_transacciones as $resumen_transaccion) {

                $resumen_transaccion->saldo = $resumen_transaccion->transacciones + $resumen_transaccion->depositos + $resumen_transaccion->reversiones + $resumen_transaccion->cashouts;

                if (is_null($resumen_transaccion->eliminado)) {
                    if ($resumen_transaccion->block_type_id == 0) {
                        $resumen_transaccion->estado = 'Activo';
                    } else {
                        $resumen_transaccion->estado = 'Bloqueado';
                    }
                } else {
                    $resumen_transaccion->estado = 'Inactivo';
                }

                unset($resumen_transaccion->block_type_id);
                unset($resumen_transaccion->description);
                unset($resumen_transaccion->eliminado);
            }

            $resumen_transacciones_groups = \DB::connection('eglobalt_replica')->select(\DB::raw("
                select
                        current_account.group_id,
                        business_groups.ruc as ruc,
                        business_groups.description as grupo,
                        SUM($whereSales) as transacciones,
                        SUM(CASE " . $whereCobranzas . " THEN (movements.amount) else 0 END) +
                        SUM(CASE " . $wherePagosMinis . " THEN (movements.amount) else 0 END) +
                        SUM(CASE " . $whereDescuentos . " THEN (movements.amount) else 0 END)
                        as depositos,
                        SUM(CASE " . $whereReversion . " THEN (movements.amount) else 0 END) as reversiones,
                        SUM(CASE " . $whereCashout . " THEN (movements.amount) else 0 END) as cashouts,
                        (Case when cuota_a.saldo_alquiler != 0 then cuota_a.saldo_alquiler else 0 end)
                        + (Case when cuota_v.saldo_venta != 0 then cuota_v.saldo_venta else 0 end) as cuotas,
                        (   (SUM($whereSales))
                            +(SUM(CASE " . $whereCobranzas . " THEN (movements.amount) else 0 END))
                            +(SUM(CASE " . $wherePagosMinis . " THEN (movements.amount) else 0 END))
                            +(SUM(CASE " . $whereDescuentos . " THEN (movements.amount) else 0 END))
                            +(SUM(CASE " . $whereReversion . " THEN (movements.amount) else 0 END))
                            +(SUM(CASE " . $whereCashout . " THEN (movements.amount) else 0 END))
                            + (Case when cuota_a.saldo_alquiler != 0 then cuota_a.saldo_alquiler else 0 end)
                            + (Case when cuota_v.saldo_venta != 0 then cuota_v.saldo_venta else 0 end)
                        ) as saldo,
                        a.blocks,
                        a.deleted
                from movements
                inner join current_account on movements.id = current_account.movement_id
                inner join business_groups on business_groups.id = current_account.group_id
                left join miniterminales_sales on movements.id = miniterminales_sales.movements_id 
                left join mt_recibos on mt_recibos.movements_id = movements.id
                left join mt_recibos_cobranzas on mt_recibos.id = mt_recibos_cobranzas.recibo_id 
                left join boletas_depositos on boletas_depositos.id = mt_recibos_cobranzas.boleta_deposito_id                
                left join mt_recibos_reversiones on mt_recibos.id = mt_recibos_reversiones.recibo_id 
                left join mt_cobranzas_mini_x_atm on mt_recibos.id = mt_cobranzas_mini_x_atm.recibo_id
                left join mt_recibos_cobranzas_x_comision on mt_recibos.id = mt_recibos_cobranzas_x_comision.recibo_id 
                left join ( 
                    select business_groups.id as grupo_id, 
                            string_agg(DISTINCT 
                                    case when atms.deleted_at is null then
                                    atms.owner_id::text
                                    else
                                    '16'::text end, ', ') as owners,
                            string_agg(DISTINCT 
                                case when atms.deleted_at is null then
                                    atms.block_type_id::text
                                    else
                                    '0'::text end, ', ') as blocks,
                            string_agg(DISTINCT
                                case when atms.deleted_at is null then 
                                'online'::text
                                else
                                atms.deleted_at::text
                                end
                            , ', ') as deleted,
                            string_agg(DISTINCT
                                        case when atms.deleted_at is NOT null OR atms.owner_id not in(16, 21, 25) then  
                                        'true'::text
                                        else
                                        atms.grilla_tradicional::text
                                        end
                            , ', ') as grilla
                    from business_groups business_groups
                    inner join branches on business_groups.id = branches.group_id
                    inner join points_of_sale pos on branches.id = pos.branch_id
                    inner join atms on atms.id = pos.atm_id
                    group by grupo_id
                ) a on a.grupo_id = business_groups.id
                left join (
                    select sum(saldo_cuota) as saldo_alquiler, group_id 
                    from alquiler
                    Inner join cuotas_alquiler on alquiler.id = cuotas_alquiler.alquiler_id
                    Inner join alquiler_housing on alquiler.id = alquiler_housing.alquiler_id
                    where fecha_vencimiento < now() and saldo_cuota <> 0 and alquiler.deleted_at is null and cod_venta is not null
                    group by group_id order by group_id
                ) cuota_a on business_groups.id = cuota_a.group_id
                left join (
                    select sum(saldo_cuota) as saldo_venta, group_id 
                    from venta
                    Inner join cuotas on venta.id = cuotas.credito_venta_id
                    Inner join venta_housing on venta.id = venta_housing.venta_id
                    where fecha_vencimiento < now() and saldo_cuota <> 0 and venta.deleted_at is null and cod_venta is not null
                    group by group_id order by group_id
                ) cuota_v on business_groups.id = cuota_v.group_id
                where
                    " . $whereMovements . "
                    movements.movement_type_id not in (4, 5, 7, 8, 9, 10)
                    and movements.destination_operation_id not in ('0','1','-2','-3','-4','-5','-6','-9','-10','-11','-12','-13','-14','-16','-16','-17','-21','-23','-26',-27,212, 999)
                    and movements.deleted_at is null
                group by current_account.group_id, ruc, grupo, a.owners, a.blocks, a.deleted, cuota_a.saldo_alquiler, cuota_v.saldo_venta
                order by saldo desc
            "));

            foreach ($resumen_transacciones_groups as $transaction) {

                if (str_contains($transaction->deleted, 'online')) {
                    if (
                        str_contains($transaction->blocks, '1') ||
                        str_contains($transaction->blocks, '3') ||
                        str_contains($transaction->blocks, '5') ||
                        str_contains($transaction->blocks, '7')
                    ) {
                        $transaction->estado = 'Bloqueado';
                    } else {
                        $transaction->estado = 'Activo';
                    }
                } else {
                    $transaction->estado = 'Inactivo';
                }

                unset($transaction->deleted);
                unset($transaction->blocks);
            }
            //dd($resumen_transacciones_groups);
            $resultset = array(
                'transacciones_groups'          => $resumen_transacciones_groups,
                //'saldo_groups'                  => $saldo_groups,
                'transacciones'                 => $resumen_transacciones,
                //'saldo'                         => $saldo,
            );

            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    public function resumenDetalladoReports($request)
    {
        try {

            $resultset = array(
                'target' => 'Resumen Detallado Miniterminales Old',
            );

            $usersNames = \DB::connection('eglobalt_auth')
                ->table('users')
                ->selectRaw('concat(username, \' - \', description) as full_name, id')
                ->join('role_users', 'users.id', '=', 'role_users.user_id')
                ->where('role_users.role_id', 22)
                ->pluck('full_name', 'id');

            $usersId = \DB::connection('eglobalt_auth')
                ->table('users')
                ->join('role_users', 'users.id', '=', 'role_users.user_id')
                ->where('role_users.role_id', 22)
                ->pluck('id', 'id');
            /*
            if(\Sentinel::getUser()->inRole('supervisor_miniterminal')){
                $supervisor = \DB::table('users_x_groups')->where('user_id',$this->user->id)->first();

                $branches = \DB::table('branches')
                    ->select('branches.*')
                    ->whereIn('branches.user_id', $usersId)
                    ->where('branches.group_id', $supervisor->group_id)
                    ->get();
            }else{
                $branches = \DB::table('branches')
                ->select('branches.*')
                ->whereIn('branches.user_id', $usersId)
                ->get();
            }
            */
            if (!\Sentinel::getUser()->inRole('mini_terminal') && !\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                $branches = \DB::table('branches')
                    ->select('branches.*')
                    ->whereIn('branches.user_id', $usersId)
                    ->get();
                $data_select = [];
                foreach ($branches as $key => $branch) {
                    $data_select[$branch->user_id] = $branch->description . ' | ' . $usersNames[$branch->user_id];
                }
                $resumen_transacciones_groups = \DB::select(\DB::raw("
                    select
                        current_account.group_id as group_id,
                        business_groups.description as grupo,
                        SUM(CASE WHEN movement_type_id = 1 THEN (movements.amount) else 0 END) as transacciones,
                        SUM(CASE WHEN movement_type_id = 6 THEN (movements.amount) else 0 END) as paquetigos,
                        SUM(CASE WHEN movement_type_id = 13 THEN (movements.amount) else 0 END) as personal,
                        SUM(CASE WHEN movement_type_id = 14 THEN (movements.amount) else 0 END) as claro,
                        SUM(CASE WHEN movement_type_id = 12 THEN (movements.amount) else 0 END) as pago_cash,
                        SUM(CASE WHEN movement_type_id = 2 THEN (movements.amount) else 0 END) as depositos,
                        SUM(CASE WHEN movement_type_id = 3 THEN (movements.amount) else 0 END) as reversiones,
                        SUM(CASE WHEN movement_type_id = 11 THEN (movements.amount) else 0 END) as cashouts,
                        (
                            (SUM(CASE WHEN debit_credit = 'de' THEN (movements.amount) else 0 END))
                            + (SUM(CASE WHEN debit_credit = 'cr' THEN (movements.amount) else 0 END))
                        ) as saldo
                    from movements
                        inner join current_account on movements.id = current_account.movement_id
                        inner join business_groups on business_groups.id = current_account.group_id
                        where
                            movements.created_at <= now() and
                            movements.movement_type_id not in (4, 5, 7, 8, 9, 10)
                            and movements.destination_operation_id not in ('0','1','-2','-3','-4','-5','-6','-9','-10','-11','-12','-13','-14','-16','-16','-17','-21','-23','-26',-27,212, 999)
                            and movements.deleted_at is null
                        group by group_id, grupo
                        order by saldo desc
                "));

                $resumen_transacciones_total_groups = \DB::select(\DB::raw("
                    select
                        SUM(CASE WHEN movement_type_id = 1 THEN (movements.amount) else 0 END) as transacciones,
                        SUM(CASE WHEN movement_type_id = 6 THEN (movements.amount) else 0 END) as paquetigos,
                        SUM(CASE WHEN movement_type_id = 13 THEN (movements.amount) else 0 END) as personal,
                        SUM(CASE WHEN movement_type_id = 14 THEN (movements.amount) else 0 END) as claro,
                        SUM(CASE WHEN movement_type_id = 12 THEN (movements.amount) else 0 END) as pago_cash,
                        SUM(CASE WHEN movement_type_id = 2 THEN (movements.amount) else 0 END) as depositos,
                        SUM(CASE WHEN movement_type_id = 3 THEN (movements.amount) else 0 END) as reversiones,
                        SUM(CASE WHEN movement_type_id = 11 THEN (movements.amount) else 0 END) as cashouts
                    from movements
                        inner join current_account on movements.id = current_account.movement_id
                        inner join business_groups on business_groups.id = current_account.group_id
                    where
                        movements.created_at <= now() and
                        movements.movement_type_id not in (4, 5, 7, 8, 9, 10)
                        and movements.destination_operation_id not in ('0','1','-2','-3','-4','-5','-6','-9','-10','-11','-12','-13','-14','-16','-16','-17','-21','-23','-26',-27,212, 999)
                        and movements.deleted_at is null
                    
                "));

                $results_groups = $this->arrayPaginator($resumen_transacciones_groups->toArray(), $request);

                $resumen_transacciones_total_groups[0]->saldo = $resumen_transacciones_total_groups[0]->transacciones + $resumen_transacciones_total_groups[0]->paquetigos + $resumen_transacciones_total_groups[0]->depositos + $resumen_transacciones_total_groups[0]->reversiones + $resumen_transacciones_total_groups[0]->cashouts + $resumen_transacciones_total_groups[0]->personal + $resumen_transacciones_total_groups[0]->pago_cash + $resumen_transacciones_total_groups[0]->claro;
                $resultset['usersNames'] = $usersNames;
                $resultset['branches'] = $branches;
                $resultset['data_select'] = $data_select;
                $resultset['user_id'] = '';

                $resultset['transactions_groups'] = $results_groups;
                $resultset['reservationtime'] = (isset($input['reservationtime']) ? $input['reservationtime'] : 0);
                $resultset['total_debe_grupo'] = number_format($resumen_transacciones_total_groups[0]->transacciones);
                $resultset['total_paquetigo_grupo'] = number_format($resumen_transacciones_total_groups[0]->paquetigos);
                $resultset['total_haber_grupo'] = number_format($resumen_transacciones_total_groups[0]->depositos);
                $resultset['total_reversion_grupo'] = number_format($resumen_transacciones_total_groups[0]->reversiones);
                $resultset['total_cashout_grupo'] = number_format($resumen_transacciones_total_groups[0]->cashouts);
                $resultset['total_personal_grupo'] = number_format($resumen_transacciones_total_groups[0]->personal);
                $resultset['total_claro_grupo'] = number_format($resumen_transacciones_total_groups[0]->claro);
                $resultset['total_pago_cash_grupo'] = number_format($resumen_transacciones_total_groups[0]->pago_cash);
                $resultset['total_saldo_groups'] = number_format($resumen_transacciones_total_groups[0]->saldo);
            } else if (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                $supervisor = \DB::table('users_x_groups')->where('user_id', $this->user->id)->first();

                $usersNames = \DB::connection('eglobalt_auth')
                    ->table('users')
                    ->selectRaw('concat(username, \' - \', description) as full_name, id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->pluck('full_name', 'id');

                $usersId = \DB::connection('eglobalt_auth')
                    ->table('users')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->pluck('id', 'id');

                $branches = \DB::table('branches')
                    ->select('branches.*')
                    ->whereIn('branches.user_id', $usersId)
                    ->where('branches.group_id', $supervisor->group_id)
                    ->get();

                $data_select = [];
                foreach ($branches as $key => $branch) {
                    $data_select[$branch->user_id] = $branch->description . ' | ' . $usersNames[$branch->user_id];
                }

                $resultset['usersNames'] = $usersNames;
                $resultset['branches'] = $branches;
                $resultset['data_select'] = $data_select;
                $resultset['user_id'] = '';
            }

            return $resultset;
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de reportes" . $e);
            return false;
        }
    }

    public function resumenDetalladoSearch($request)
    {
        try {
            $input = $this->input;
            /*Busqueda minusiosa*/
            /*SET DATE RANGE*/
            $whereMovements = '';
            if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
                if (isset($input['reservationtime']) && $input['reservationtime'] != '0') {
                    $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                    $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                    $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                    //$whereMovements = "movements.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereSales = "WHEN movement_type_id = 1 AND miniterminales_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $wherePaquetigos = "WHEN movement_type_id = 6 AND miniterminales_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $wherePersonal = "WHEN movement_type_id = 13 AND miniterminales_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereClaro = "WHEN movement_type_id = 14 AND miniterminales_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $wherePagoCash = "WHEN movement_type_id = 12 AND miniterminales_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    //$whereSales = "WHEN debit_credit = 'de' AND miniterminales_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereCobranzas = "WHEN movement_type_id = 2 AND boletas_depositos.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $wherePagosMinis = "WHEN movement_type_id = 2 AND mt_cobranzas_mini_x_atm.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereDescuentos = "WHEN movement_type_id = 2 AND mt_recibos_cobranzas_x_comision.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereReversion = "WHEN movement_type_id = 3 AND mt_recibos_reversiones.fecha_reversion BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereCashout = "WHEN movement_type_id = 11 AND movements.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                } else {
                    $whereSales = "WHEN movement_type_id = 1 AND movements.created_at <= now()";
                    $wherePaquetigos = "WHEN movement_type_id = 6 AND movements.created_at <= now()";
                    $wherePersonal = "WHEN movement_type_id = 13 AND movements.created_at <= now()";
                    $whereClaro = "WHEN movement_type_id = 14 AND movements.created_at <= now()";
                    $wherePagoCash = "WHEN movement_type_id = 12 AND movements.created_at <= now()";
                    //$whereSales = "WHEN debit_credit = 'de' AND movements.created_at <= now() ";
                    $whereCobranzas = "WHEN movement_type_id = 2 AND boletas_depositos.fecha <= now() ";
                    $wherePagosMinis = "WHEN movement_type_id = 2 AND mt_cobranzas_mini_x_atm.fecha <= now() ";
                    $whereDescuentos = "WHEN movement_type_id = 2 AND mt_recibos_cobranzas_x_comision.created_at <= now() ";
                    $whereReversion = "WHEN movement_type_id = 3 AND mt_recibos_reversiones.fecha_reversion <= now() ";
                    $whereCashout = "WHEN movement_type_id = 11 AND movements.created_at <= now() ";
                }
                $whereMovements .= " LOWER(business_groups.description) like LOWER('%{$input['context']}%') AND";
            } else {
                if (isset($input['reservationtime']) && $input['reservationtime'] != '0') {
                    $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                    $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                    $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                    //$whereMovements = "movements.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereSales = "WHEN movement_type_id = 1 AND miniterminales_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $wherePaquetigos = "WHEN movement_type_id = 6 AND miniterminales_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $wherePersonal = "WHEN movement_type_id = 13 AND miniterminales_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereClaro = "WHEN movement_type_id = 14 AND miniterminales_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $wherePagoCash = "WHEN movement_type_id = 12 AND miniterminales_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    //$whereSales = "WHEN debit_credit = 'de' AND miniterminales_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereCobranzas = "WHEN movement_type_id = 2 AND boletas_depositos.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $wherePagosMinis = "WHEN movement_type_id = 2 AND mt_cobranzas_mini_x_atm.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereDescuentos = "WHEN movement_type_id = 2 AND mt_recibos_cobranzas_x_comision.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereReversion = "WHEN movement_type_id = 3 AND mt_recibos_reversiones.fecha_reversion BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereCashout = "WHEN movement_type_id = 11 AND movements.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                } else {
                    $whereSales = "WHEN movement_type_id = 1 AND movements.created_at <= now()";
                    $wherePaquetigos = "WHEN movement_type_id = 6 AND movements.created_at <= now()";
                    $wherePersonal = "WHEN movement_type_id = 13 AND movements.created_at <= now()";
                    $whereClaro = "WHEN movement_type_id = 14 AND movements.created_at <= now()";
                    $wherePagoCash = "WHEN movement_type_id = 12 AND movements.created_at <= now()";
                    //$whereSales = "WHEN debit_credit = 'de' AND movements.created_at <= now() ";
                    $whereCobranzas = "WHEN movement_type_id = 2 AND boletas_depositos.fecha <= now() ";
                    $wherePagosMinis = "WHEN movement_type_id = 2 AND mt_cobranzas_mini_x_atm.fecha <= now() ";
                    $whereDescuentos = "WHEN movement_type_id = 2 AND mt_recibos_cobranzas_x_comision.created_at <= now() ";
                    $whereReversion = "WHEN movement_type_id = 3 AND mt_recibos_reversiones.fecha_reversion <= now() ";
                    $whereCashout = "WHEN movement_type_id = 11 AND movements.created_at <= now() ";
                }
                if (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                    $supervisor = \DB::table('users_x_groups')->where('user_id', $this->user->id)->first();

                    $whereMovements .= "business_groups.id = " . $supervisor->group_id . " AND";
                }
            }

            $resumen_transacciones_groups = \DB::select(\DB::raw("
                select
                        current_account.group_id,
                        business_groups.description as grupo,
                        SUM(CASE " . $whereSales . " THEN (movements.amount) else 0 END) as transacciones,
                        SUM(CASE " . $wherePaquetigos . " THEN (movements.amount) else 0 END) as paquetigos,
                        SUM(CASE " . $wherePersonal . " THEN (movements.amount) else 0 END) as personal,
                        SUM(CASE " . $whereClaro . " THEN (movements.amount) else 0 END) as claro,
                        SUM(CASE " . $wherePagoCash . " THEN (movements.amount) else 0 END) as pago_cash,
                        SUM(CASE " . $whereCobranzas . " THEN (movements.amount) else 0 END) +
                        SUM(CASE " . $wherePagosMinis . " THEN (movements.amount) else 0 END) +
                        SUM(CASE " . $whereDescuentos . " THEN (movements.amount) else 0 END) as depositos,
                        SUM(CASE " . $whereReversion . " THEN (movements.amount) else 0 END) as reversiones,
                        SUM(CASE " . $whereCashout . " THEN (movements.amount) else 0 END) as cashouts,
                        (   (SUM(CASE " . $whereSales . " THEN (movements.amount) else 0 END))
                            +(SUM(CASE " . $wherePaquetigos . " THEN (movements.amount) else 0 END))
                            +(SUM(CASE " . $wherePersonal . " THEN (movements.amount) else 0 END))
                            +(SUM(CASE " . $whereClaro . " THEN (movements.amount) else 0 END))
                            +(SUM(CASE " . $wherePagoCash . " THEN (movements.amount) else 0 END))
                            +(SUM(CASE " . $whereCobranzas . " THEN (movements.amount) else 0 END))
                            +(SUM(CASE " . $wherePagosMinis . " THEN (movements.amount) else 0 END))
                            +(SUM(CASE " . $whereDescuentos . " THEN (movements.amount) else 0 END))
                            +(SUM(CASE " . $whereReversion . " THEN (movements.amount) else 0 END))
                            +(SUM(CASE " . $whereCashout . " THEN (movements.amount) else 0 END))
                        ) as saldo
                from movements
                inner join current_account on movements.id = current_account.movement_id
                inner join business_groups on business_groups.id = current_account.group_id
                left join miniterminales_sales on movements.id = miniterminales_sales.movements_id              
                left join mt_recibos on mt_recibos.movements_id = movements.id
                left join mt_recibos_cobranzas on mt_recibos.id = mt_recibos_cobranzas.recibo_id
                left join boletas_depositos on boletas_depositos.id = mt_recibos_cobranzas.boleta_deposito_id
                left join mt_recibos_reversiones on mt_recibos.id = mt_recibos_reversiones.recibo_id
                left join mt_cobranzas_mini_x_atm on mt_recibos.id = mt_cobranzas_mini_x_atm.recibo_id
                left join mt_recibos_cobranzas_x_comision on mt_recibos.id = mt_recibos_cobranzas_x_comision.recibo_id
                where
                    " . $whereMovements . "
                    movements.movement_type_id not in (4, 5, 7, 8, 9, 10)
                    and movements.destination_operation_id not in ('0','1','-2','-3','-4','-5','-6','-9','-10','-11','-12','-13','-14','-16','-16','-17','-21','-23','-26',-27,212, 999)
                    and movements.deleted_at is null
                group by current_account.group_id, grupo
                order by saldo desc
            "));

            $resumen_transacciones_total_groups = \DB::select(\DB::raw("
                select
                    SUM(CASE " . $whereSales . " THEN (movements.amount) else 0 END) as transacciones,
                    SUM(CASE " . $wherePaquetigos . " THEN (movements.amount) else 0 END) as paquetigos,
                    SUM(CASE " . $wherePersonal . " THEN (movements.amount) else 0 END) as personal,
                    SUM(CASE " . $whereClaro . " THEN (movements.amount) else 0 END) as claro,
                    SUM(CASE " . $wherePagoCash . " THEN (movements.amount) else 0 END) as pago_cash,
                    SUM(CASE " . $whereCobranzas . " THEN (movements.amount) else 0 END) +
                    SUM(CASE " . $wherePagosMinis . " THEN (movements.amount) else 0 END) +
                    SUM(CASE " . $whereDescuentos . " THEN (movements.amount) else 0 END) as depositos,
                    SUM(CASE " . $whereReversion . " THEN (movements.amount) else 0 END) as reversiones, 
                    SUM(CASE " . $whereCashout . " THEN (movements.amount) else 0 END) as cashouts
                from movements
                inner join current_account on movements.id = current_account.movement_id
                inner join business_groups on business_groups.id = current_account.group_id
                left join miniterminales_sales on movements.id = miniterminales_sales.movements_id               
                left join mt_recibos on mt_recibos.movements_id = movements.id
                left join mt_recibos_cobranzas on mt_recibos.id = mt_recibos_cobranzas.recibo_id
                left join boletas_depositos on boletas_depositos.id = mt_recibos_cobranzas.boleta_deposito_id
                left join mt_recibos_reversiones on mt_recibos.id = mt_recibos_reversiones.recibo_id
                left join mt_cobranzas_mini_x_atm on mt_recibos.id = mt_cobranzas_mini_x_atm.recibo_id
                left join mt_recibos_cobranzas_x_comision on mt_recibos.id = mt_recibos_cobranzas_x_comision.recibo_id
                where
                    " . $whereMovements . "
                    movements.movement_type_id not in (4, 5, 7, 8, 9, 10)
                    and movements.destination_operation_id not in ('0','1','-2','-3','-4','-5','-6','-9','-10','-11','-12','-13','-14','-16','-16','-17','-21','-23','-26',-27,212, 999)
                    and movements.deleted_at is null
            "));

            $resumen_transacciones_total_groups[0]->saldo = $resumen_transacciones_total_groups[0]->transacciones + $resumen_transacciones_total_groups[0]->paquetigos + $resumen_transacciones_total_groups[0]->depositos + $resumen_transacciones_total_groups[0]->reversiones + $resumen_transacciones_total_groups[0]->cashouts + $resumen_transacciones_total_groups[0]->personal + $resumen_transacciones_total_groups[0]->pago_cash + $resumen_transacciones_total_groups[0]->claro;

            // $total_saldo_groups = $resumen_transacciones_total_groups[0]->transacciones + $resumen_transacciones_total_groups[0]->depositos;

            $results_groups = $this->arrayPaginator($resumen_transacciones_groups->toArray(), $request);

            $resultset = array(
                'target'        => 'Resumen Detallado Miniterminales Old',
                'transactions_groups'  => $results_groups,
                'reservationtime' => (isset($input['reservationtime']) ? $input['reservationtime'] : 0),
                'i'             =>  1,
                'total_debe_grupo' => number_format($resumen_transacciones_total_groups[0]->transacciones),
                'total_paquetigo_grupo' => number_format($resumen_transacciones_total_groups[0]->paquetigos),
                'total_personal_grupo' => number_format($resumen_transacciones_total_groups[0]->personal),
                'total_claro_grupo' => number_format($resumen_transacciones_total_groups[0]->claro),
                'total_pago_cash_grupo' => number_format($resumen_transacciones_total_groups[0]->pago_cash),
                'total_haber_grupo' => number_format($resumen_transacciones_total_groups[0]->depositos),
                'total_reversion_grupo' => number_format($resumen_transacciones_total_groups[0]->reversiones),
                'total_cashout_grupo' => number_format($resumen_transacciones_total_groups[0]->cashouts),
                'total_saldo_groups' => number_format($resumen_transacciones_total_groups[0]->saldo)
            );
            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    public function resumenDetalladoSearchExport()
    {
        try {
            $input = $this->input;
            /*Busqueda minusiosa*/
            /*SET DATE RANGE*/
            $whereMovements = '';
            if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
                if (isset($input['reservationtime']) && $input['reservationtime'] != '0') {
                    $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                    $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                    $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                    //$whereMovements = "movements.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereSales = "WHEN movement_type_id = 1 AND miniterminales_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $wherePaquetigos = "WHEN movement_type_id = 6 AND miniterminales_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $wherePersonal = "WHEN movement_type_id = 13 AND miniterminales_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereClaro = "WHEN movement_type_id = 14 AND miniterminales_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $wherePagoCash = "WHEN movement_type_id = 12 AND miniterminales_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    //$whereSales = "WHEN debit_credit = 'de' AND miniterminales_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereCobranzas = "WHEN movement_type_id = 2 AND boletas_depositos.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $wherePagosMinis = "WHEN movement_type_id = 2 AND mt_cobranzas_mini_x_atm.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereDescuentos = "WHEN movement_type_id = 2 AND mt_recibos_cobranzas_x_comision.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereReversion = "WHEN movement_type_id = 3 AND mt_recibos_reversiones.fecha_reversion BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereCashout = "WHEN movement_type_id = 11 AND movements.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereTransactions = "t.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                } else {
                    $whereSales = "WHEN movement_type_id = 1 AND movements.created_at <= now()";
                    $wherePaquetigos = "WHEN movement_type_id = 6 AND movements.created_at <= now()";
                    $wherePersonal = "WHEN movement_type_id = 13 AND movements.created_at <= now()";
                    $whereClaro = "WHEN movement_type_id = 14 AND movements.created_at <= now()";
                    $wherePagoCash = "WHEN movement_type_id = 12 AND movements.created_at <= now()";
                    //$whereSales = "WHEN debit_credit = 'de' AND movements.created_at <= now() ";
                    $whereCobranzas = "WHEN movement_type_id = 2 AND boletas_depositos.fecha <= now() ";
                    $wherePagosMinis = "WHEN movement_type_id = 2 AND mt_cobranzas_mini_x_atm.fecha <= now() ";
                    $whereDescuentos = "WHEN movement_type_id = 2 AND mt_recibos_cobranzas_x_comision.created_at <= now() ";
                    $whereReversion = "WHEN movement_type_id = 3 AND mt_recibos_reversiones.fecha_reversion <= now() ";
                    $whereCashout = "WHEN movement_type_id = 11 AND movements.created_at <= now() ";
                    $whereTransactions = "t.created_at <= now()";
                }
                $whereMovements .= " LOWER(business_groups.description) like LOWER('%{$input['context']}%') AND";
            } else {
                if (isset($input['reservationtime']) && $input['reservationtime'] != '0') {
                    $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                    $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                    $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                    //$whereMovements = "movements.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereSales = "WHEN movement_type_id = 1 AND miniterminales_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $wherePaquetigos = "WHEN movement_type_id = 6 AND miniterminales_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $wherePersonal = "WHEN movement_type_id = 13 AND miniterminales_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereClaro = "WHEN movement_type_id = 14 AND miniterminales_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $wherePagoCash = "WHEN movement_type_id = 12 AND miniterminales_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    //$whereSales = "WHEN debit_credit = 'de' AND miniterminales_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereCobranzas = "WHEN movement_type_id = 2 AND boletas_depositos.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $wherePagosMinis = "WHEN movement_type_id = 2 AND mt_cobranzas_mini_x_atm.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereDescuentos = "WHEN movement_type_id = 2 AND mt_recibos_cobranzas_x_comision.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereReversion = "WHEN movement_type_id = 3 AND mt_recibos_reversiones.fecha_reversion BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereCashout = "WHEN movement_type_id = 11 AND movements.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";

                    $whereTransactions = "t.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                } else {
                    $whereSales = "WHEN movement_type_id = 1 AND movements.created_at <= now()";
                    $wherePaquetigos = "WHEN movement_type_id = 6 AND movements.created_at <= now()";
                    $wherePersonal = "WHEN movement_type_id = 13 AND movements.created_at <= now()";
                    $whereClaro = "WHEN movement_type_id = 14 AND movements.created_at <= now()";
                    $wherePagoCash = "WHEN movement_type_id = 12 AND movements.created_at <= now()";
                    //$whereSales = "WHEN debit_credit = 'de' AND movements.created_at <= now() ";
                    $whereCobranzas = "WHEN movement_type_id = 2 AND boletas_depositos.fecha <= now() ";
                    $wherePagosMinis = "WHEN movement_type_id = 2 AND mt_cobranzas_mini_x_atm.fecha <= now() ";
                    $whereDescuentos = "WHEN movement_type_id = 2 AND mt_recibos_cobranzas_x_comision.created_at <= now() ";
                    $whereReversion = "WHEN movement_type_id = 3 AND mt_recibos_reversiones.fecha_reversion <= now() ";
                    $whereCashout = "WHEN movement_type_id = 11 AND movements.created_at <= now() ";
                    $whereTransactions = "t.created_at <= now()";
                }
                if (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                    $supervisor = \DB::table('users_x_groups')->where('user_id', $this->user->id)->first();

                    $whereMovements .= "business_groups.id = " . $supervisor->group_id . " AND";
                    $whereTransactions .= " AND branches.group_id = " . $supervisor->group_id;
                }
            }

            $usersId = \DB::table('users')
                ->join('role_users', 'users.id', '=', 'role_users.user_id')
                ->where('role_users.role_id', 22)
                ->pluck('id', 'id');

            $user_id = '(' . implode(',', $usersId) . ')';

            $resumen_transacciones = \DB::select(\DB::raw("
                select
                    -sum(abs(t.amount)) as total,
                    branches.description as branch_id 
                from
                    transactions t
                left join service_provider_products sp on
                    t.service_id = sp.id 
                    and t.service_source_id = 0
                left join service_providers on
                    service_providers.id = sp.service_provider_id 
                    and t.service_source_id = 0
                left join services_providers_sources sps on 
                    t.service_source_id = sps.id 
                    and t.service_source_id <> 0
                left join services_ondanet_pairing sop on
                    t.service_id = sop.service_request_id 
                    and t.service_source_id = sop.service_source_id 
                    and t.service_source_id <> 0
                inner join points_of_sale on 
                    points_of_sale.atm_id = t.atm_id and points_of_sale.deleted_at is null 
                inner join branches on 
                    branches.id = points_of_sale.branch_id and branches.user_id in " . $user_id . "
                inner join atms on
                    atms.id = points_of_sale.atm_id
                where
                ( 
                    status in ('success', 'error') 
                    and t.service_id in(14, 15) 
                    and t.transaction_type in (1)
                    and atms.owner_id in (16, 21, 25)
                    and t.service_source_id=8
                    and " . $whereTransactions . ")
                or ( 
                    status = 'success'
                    and t.transaction_type in (1)
                    and atms.owner_id in (16, 21, 25)
                    and " . $whereTransactions . "
                    )
                group by branches.id;
            "));

            $resumen_transacciones_groups = \DB::select(\DB::raw("
                select
                        current_account.group_id,
                        business_groups.ruc as ruc,
                        business_groups.description as grupo,
                        SUM(CASE " . $whereSales . " THEN (movements.amount) else 0 END) as transacciones,
                        SUM(CASE " . $wherePaquetigos . " THEN (movements.amount) else 0 END) as paquetigos,
                        SUM(CASE " . $wherePersonal . " THEN (movements.amount) else 0 END) as personal,
                        SUM(CASE " . $whereClaro . " THEN (movements.amount) else 0 END) as claro,
                        SUM(CASE " . $wherePagoCash . " THEN (movements.amount) else 0 END) as pago_cash,
                        SUM(CASE " . $whereCobranzas . " THEN (movements.amount) else 0 END) +
                        SUM(CASE " . $wherePagosMinis . " THEN (movements.amount) else 0 END) +
                        SUM(CASE " . $whereDescuentos . " THEN (movements.amount) else 0 END) as depositos,
                        SUM(CASE " . $whereReversion . " THEN (movements.amount) else 0 END) as reversiones,
                        SUM(CASE " . $whereCashout . " THEN (movements.amount) else 0 END) as cashouts,
                        (   (SUM(CASE " . $whereSales . " THEN (movements.amount) else 0 END))
                            +(SUM(CASE " . $wherePaquetigos . " THEN (movements.amount) else 0 END))
                            +(SUM(CASE " . $wherePersonal . " THEN (movements.amount) else 0 END))
                            +(SUM(CASE " . $whereClaro . " THEN (movements.amount) else 0 END))
                            +(SUM(CASE " . $wherePagoCash . " THEN (movements.amount) else 0 END))
                            +(SUM(CASE " . $whereCobranzas . " THEN (movements.amount) else 0 END))
                            +(SUM(CASE " . $wherePagosMinis . " THEN (movements.amount) else 0 END))
                            +(SUM(CASE " . $whereDescuentos . " THEN (movements.amount) else 0 END))
                            +(SUM(CASE " . $whereReversion . " THEN (movements.amount) else 0 END))
                            +(SUM(CASE " . $whereCashout . " THEN (movements.amount) else 0 END))
                        ) as saldo
                from movements
                inner join current_account on movements.id = current_account.movement_id
                inner join business_groups on business_groups.id = current_account.group_id
                left join miniterminales_sales on movements.id = miniterminales_sales.movements_id              
                left join mt_recibos on mt_recibos.movements_id = movements.id
                left join mt_recibos_cobranzas on mt_recibos.id = mt_recibos_cobranzas.recibo_id 
                left join boletas_depositos on boletas_depositos.id = mt_recibos_cobranzas.boleta_deposito_id
                left join mt_recibos_reversiones on mt_recibos.id = mt_recibos_reversiones.recibo_id
                left join mt_cobranzas_mini_x_atm on mt_recibos.id = mt_cobranzas_mini_x_atm.recibo_id
                left join mt_recibos_cobranzas_x_comision on mt_recibos.id = mt_recibos_cobranzas_x_comision.recibo_id
                where
                    " . $whereMovements . "
                    movements.movement_type_id not in (4, 5, 7, 8)
                    and movements.destination_operation_id not in ('0','1','-2','-3','-4','-5','-6','-9','-10','-11','-12','-13','-14','-16','-16','-17','-21','-23','-26',-27,212, 999)
                    and movements.deleted_at is null
                group by current_account.group_id, ruc, grupo
                order by saldo desc
            "));

            $resultset = array(
                'transacciones_groups'          => $resumen_transacciones_groups,
                //'saldo_groups'                  => $saldo_groups,
                'transacciones'                 => $resumen_transacciones,
                //'saldo'                         => $saldo,
            );

            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    public function boletaDepositosReports($request)
    {
        try {

            $desde = Carbon::today();
            $hasta = Carbon::tomorrow()->modify('-1 seconds');

            $boletas = \DB::table('boletas_depositos')
                ->select([
                    'boletas_depositos.id',
                    'fecha',
                    'bancos.descripcion as banco',
                    'cuentas_bancarias.numero_banco as cuenta_bancaria',
                    'boleta_numero',
                    'monto',
                    'user_id',
                    'tipo_pago.descripcion as tipo_pago',
                    'estado',
                    'boletas_depositos.deleted_at',
                    'boletas_depositos.updated_at',
                    'boletas_depositos.updated_by',
                    'users.username as username',
                    'boletas_depositos.message'
                ])
                ->join('cuentas_bancarias', 'cuentas_bancarias.id', '=', 'boletas_depositos.cuenta_bancaria_id')
                ->join('bancos', 'bancos.id', '=', 'cuentas_bancarias.banco_id')
                ->join('tipo_pago', 'tipo_pago.id', '=', 'boletas_depositos.tipo_pago_id')
                ->leftjoin('users', 'users.id', '=', 'boletas_depositos.updated_by')
                ->whereRaw("fecha BETWEEN '{$desde}' AND '{$hasta}'")
                ->where(function ($query) {
                    if (!\Sentinel::getUser()->inRole('mini_terminal') && !\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                        $query->where('estado', true);
                    } else if (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                        $supervisor = \DB::table('users_x_groups')->where('user_id', $this->user->id)->first();
                        $users = \DB::table('branches')
                            ->select(['users.id'])
                            ->join('users', 'branches.user_id', '=', 'users.id')
                            ->join('role_users', 'users.id', '=', 'role_users.user_id')
                            ->where('role_users.role_id', 22)
                            ->where('branches.group_id', $supervisor->group_id)
                            ->pluck('users.id');

                        $query->whereIn('boletas_depositos.user_id', $users);
                    } else {
                        $query->where('boletas_depositos.user_id', $this->user->id);
                    }
                })
                ->orderBy('boletas_depositos.id', 'desc')
                ->get();
            foreach ($boletas as $key => $boleta) {
                if (!empty(\DB::table('users_x_groups')->where('user_id', $boleta->user_id)->first())) {
                    $deposito = \DB::table('business_groups')
                        ->join('users_x_groups', 'business_groups.id', '=', 'users_x_groups.group_id')
                        ->where('users_x_groups.user_id', $boleta->user_id)
                        ->first();
                    $boleta->supervisor = $deposito->ruc . ' | ' . $deposito->description;
                }
            }

            $results = $this->arrayPaginator($boletas->toArray(), $request);

            $resultset = array(
                'target' => 'Depositos Miniterminales',
                'transactions' => $results,
                'reservationtime' => (isset($input['reservationtime']) ? $input['reservationtime'] : 0)
            );

            $usersNames = \DB::connection('eglobalt_auth')
                ->table('users')
                ->selectRaw('concat(username, \' - \', description) as full_name, id')
                ->join('role_users', 'users.id', '=', 'role_users.user_id')
                ->where('role_users.role_id', 22)
                ->where(function ($query) {
                    if (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                        $supervisor = \DB::table('users_x_groups')->where('user_id', $this->user->id)->first();

                        $users = \DB::table('branches')
                            ->select(['users.id'])
                            ->join('users', 'branches.user_id', '=', 'users.id')
                            ->join('role_users', 'users.id', '=', 'role_users.user_id')
                            ->where('role_users.role_id', 22)
                            ->where('branches.group_id', $supervisor->group_id)
                            ->pluck('users.id');

                        $query->whereIn('users.id', $users);
                    } else if (\Sentinel::getUser()->inRole('mini_terminal')) {
                        $query->where('users.id', $this->user->id);
                    }
                })
                ->pluck('full_name', 'id');

            $usersId = \DB::connection('eglobalt_auth')
                ->table('users')
                ->join('role_users', 'users.id', '=', 'role_users.user_id')
                ->where('role_users.role_id', 22)
                ->where(function ($query) {
                    if (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                        $supervisor = \DB::table('users_x_groups')->where('user_id', $this->user->id)->first();

                        $users = \DB::table('branches')
                            ->select(['users.id'])
                            ->join('users', 'branches.user_id', '=', 'users.id')
                            ->join('role_users', 'users.id', '=', 'role_users.user_id')
                            ->where('role_users.role_id', 22)
                            ->where('branches.group_id', $supervisor->group_id)
                            ->pluck('users.id');

                        $query->whereIn('users.id', $users);
                    } else if (\Sentinel::getUser()->inRole('mini_terminal')) {
                        $query->where('users.id', $this->user->id);
                    }
                })
                ->pluck('id', 'id');

            $branches = \DB::table('branches')
                ->select('branches.*')
                ->whereIn('branches.user_id', $usersId)
                ->get();

            $groups = Group::pluck('description', 'id');

            $data_select = [];
            foreach ($branches as $key => $branch) {
                $data_select[$branch->user_id] = $branch->description . ' | ' . $usersNames[$branch->user_id];
            }

            $status = array('0' => 'Todos', '1' => 'Confirmados', '2' => 'Rechazados', '3' => 'Pendientes');

            $resultset['usersNames']    = $usersNames;
            $resultset['branches']      = $branches;
            $resultset['data_select']   = $data_select;
            $resultset['status']        = $status;
            $resultset['status_set']    = 0;
            $resultset['user_id']       = '';
            $resultset['groups']        = $groups;
            $resultset['group_id']      = 0;

            return $resultset;
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de reportes" . $e);
            return false;
        }
    }

    public function boletaDepositosSearch($request)
    {
        try {
            $input = $this->input;
            //dd($input);
            /*Busqueda minusiosa*/
            if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
                $where = "boleta_numero like '%{$input['context']}%' ";
            } else {
                /*SET DATE RANGE*/
                if (isset($input['reservationtime'])) {
                    $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                    $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                    $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                    $where = "fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                }

                if ($input['status_id'] != 0) {
                    if ($input['status_id'] == '1') {
                        $where .= " AND estado is true";
                    }
                    if ($input['status_id'] == '2') {
                        $where .= " AND estado is false";
                    }
                    if ($input['status_id'] == '3') {
                        $where .= " AND estado is null";
                    }
                }
            }

            if (!empty($input['group_id'])) {

                $group_id = $input['group_id'];


                if ($group_id != 0) {

                    if (!empty($input['user_id'])) {
                        $user_id = '(' . $input['user_id'] . ')';
                    } else {
                        $usersId = \DB::table('branches')
                            ->whereIn('owner_id', [16, 21, 25])
                            ->where('group_id', $group_id)
                            ->pluck('branches.user_id', 'branches.user_id');
                        $user = implode(',', $usersId);

                        $supervisor = \DB::table('users_x_groups')->where('group_id', $group_id)->first();

                        if (!empty($supervisor)) {
                            $user .= ',' . $supervisor->user_id;
                        }

                        $user_id = '(' . $user . ')';
                    }
                } else {
                    if (!empty($input['user_id'])) {
                        $user_id = '(' . $input['user_id'] . ')';
                    } else {

                        $user_id = $input['user_id'];
                    }
                }
            } else {
                if (!empty($input['user_id'])) {
                    $user_id = '(' . $input['user_id'] . ')';
                } else {
                    if (\Sentinel::getUser()->inRole('mini_terminal')) {
                        $user_id = '';
                        $input['user_id'] = $this->user->id;
                    } else {
                        $user_id = $input['user_id'];
                    }
                }
            }

            // $where = trim($where);
            // $where = trim($where, 'AND');
            // $where = trim($where);
            $boletas = \DB::table('boletas_depositos')
                ->select([
                    'boletas_depositos.id',
                    'fecha',
                    'bancos.descripcion as banco',
                    'cuentas_bancarias.numero_banco as cuenta_bancaria',
                    'boleta_numero',

                    //'monto'
                    \DB::raw("(case when monto_anterior is not null then monto_anterior else monto end) as monto"),

                    'boletas_depositos.user_id',
                    'tipo_pago.descripcion as tipo_pago',
                    'estado',
                    'boletas_depositos.deleted_at',
                    'boletas_depositos.updated_at',
                    'boletas_depositos.updated_by',
                    'users.username as username',
                    'boletas_depositos.message'
                ])
                ->join('cuentas_bancarias', 'cuentas_bancarias.id', '=', 'boletas_depositos.cuenta_bancaria_id')
                ->join('bancos', 'bancos.id', '=', 'cuentas_bancarias.banco_id')
                ->join('tipo_pago', 'tipo_pago.id', '=', 'boletas_depositos.tipo_pago_id')
                ->leftjoin('users', 'users.id', '=', 'boletas_depositos.updated_by')
                ->whereRaw("$where")
                ->where(function ($query) use ($user_id) {
                    if (!empty($user_id)) {
                        $query->whereRaw('boletas_depositos.user_id in ' . $user_id);
                    } else {
                        if (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                            $supervisor = \DB::table('users_x_groups')->where('user_id', $this->user->id)->first();
                            $users = \DB::table('branches')
                                ->select(['users.id'])
                                ->join('users', 'branches.user_id', '=', 'users.id')
                                ->join('role_users', 'users.id', '=', 'role_users.user_id')
                                ->where('role_users.role_id', 22)
                                ->where('branches.group_id', $supervisor->group_id)
                                ->pluck('users.id');
                            $users_id = implode(',', $users);
                            $users_id .= ',' . $supervisor->user_id;

                            $query->whereRaw('boletas_depositos.user_id in (' . $users_id . ')');
                        } else if (\Sentinel::getUser()->inRole('mini_terminal')) {
                            $query->where('boletas_depositos.user_id', $this->user->id);
                        }
                    }
                })
                ->orderBy('boletas_depositos.id', 'desc')
                ->get();

            foreach ($boletas as $key => $boleta) {
                if (!empty(\DB::table('users_x_groups')->where('user_id', $boleta->user_id)->first())) {
                    $deposito = \DB::table('business_groups')
                        ->join('users_x_groups', 'business_groups.id', '=', 'users_x_groups.group_id')
                        ->where('users_x_groups.user_id', $boleta->user_id)
                        ->first();
                    $boleta->supervisor = $deposito->ruc . ' | ' . $deposito->description;
                }
            }

            $results = $this->arrayPaginator($boletas->toArray(), $request);

            $resultset = array(
                'target'        => 'Depositos Miniterminales',
                'transactions'  => $results,
                'reservationtime' => (isset($input['reservationtime']) ? $input['reservationtime'] : 0),
                'i'             =>  1,
            );

            $usersNames = \DB::connection('eglobalt_auth')
                ->table('users')
                ->selectRaw('concat(username, \' - \', description) as full_name, id')
                ->join('role_users', 'users.id', '=', 'role_users.user_id')
                ->where('role_users.role_id', 22)
                ->where(function ($query) {
                    if (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                        $supervisor = \DB::table('users_x_groups')->where('user_id', $this->user->id)->first();

                        $users = \DB::table('branches')
                            ->select(['users.id'])
                            ->join('users', 'branches.user_id', '=', 'users.id')
                            ->join('role_users', 'users.id', '=', 'role_users.user_id')
                            ->where('role_users.role_id', 22)
                            ->where('branches.group_id', $supervisor->group_id)
                            ->pluck('users.id');

                        $query->whereIn('users.id', $users);
                    } else if (\Sentinel::getUser()->inRole('mini_terminal')) {
                        $query->where('users.id', $this->user->id);
                    }
                })
                ->pluck('full_name', 'id');

            $usersId = \DB::connection('eglobalt_auth')
                ->table('users')
                ->join('role_users', 'users.id', '=', 'role_users.user_id')
                ->where('role_users.role_id', 22)
                ->where(function ($query) {
                    if (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                        $supervisor = \DB::table('users_x_groups')->where('user_id', $this->user->id)->first();

                        $users = \DB::table('branches')
                            ->select(['users.id'])
                            ->join('users', 'branches.user_id', '=', 'users.id')
                            ->join('role_users', 'users.id', '=', 'role_users.user_id')
                            ->where('role_users.role_id', 22)
                            ->where('branches.group_id', $supervisor->group_id)
                            ->pluck('users.id');

                        $query->whereIn('users.id', $users);
                    } else if (\Sentinel::getUser()->inRole('mini_terminal')) {
                        $query->where('users.id', $this->user->id);
                    }
                })
                ->pluck('id', 'id');

            $branches = \DB::table('branches')
                ->select('branches.*')
                ->whereIn('branches.user_id', $usersId)
                ->get();

            $status = array('0' => 'Todos', '1' => 'Confirmados', '2' => 'Rechazados', '3' => 'Pendientes');

            $data_select = [];
            foreach ($branches as $key => $branch) {
                $data_select[$branch->user_id] = $branch->description . ' | ' . $usersNames[$branch->user_id];
            }

            $groups = Group::pluck('description', 'id');

            $resultset['usersNames'] = $usersNames;
            $resultset['branches'] = $branches;
            $resultset['status'] = $status;
            $resultset['status_set'] = (isset($input['status_id']) ? $input['status_id'] : 0);
            $resultset['data_select'] = $data_select;
            $resultset['user_id'] = $input['user_id'];
            $resultset['groups'] = $groups;
            if (!empty($input['group_id'])) {
                $resultset['group_id'] = $input['group_id'];
            } else {
                $resultset['group_id'] = 0;
            }

            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    public function boletaDepositosSearchExport()
    {
        try {
            $input = $this->input;
            /*Busqueda minusiosa*/
            if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
                $where = "boleta_numero like '%{$input['context']}%' ";
            } else {
                /*SET DATE RANGE*/
                if (isset($input['reservationtime'])) {
                    $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                    $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                    $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                    $where = "fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                }

                if ($input['status_id'] != 0) {
                    if ($input['status_id'] == '1') {
                        $where .= " AND estado is true";
                    }
                    if ($input['status_id'] == '2') {
                        $where .= " AND estado is false";
                    }
                    if ($input['status_id'] == '3') {
                        $where .= " AND estado is null";
                    }
                }
            }

            if (!empty($input['group_id'])) {

                $group_id = $input['group_id'];
                if ($group_id != 0) {

                    if (!empty($input['user_id'])) {
                        $user_id = '(' . $input['user_id'] . ')';
                    } else {
                        $usersId = \DB::table('branches')
                            ->whereIn('owner_id', [16, 21, 25])
                            ->where('group_id', $group_id)
                            ->pluck('branches.user_id', 'branches.user_id');
                        $user = implode(',', $usersId);

                        $supervisor = \DB::table('users_x_groups')->where('group_id', $group_id)->first();

                        if (!empty($supervisor)) {
                            $user .= ',' . $supervisor->user_id;
                        }

                        $user_id = '(' . $user . ')';
                    }
                } else {
                    if (!empty($input['user_id'])) {
                        $user_id = '(' . $input['user_id'] . ')';
                    } else {

                        $user_id = $input['user_id'];
                    }
                }
            } else {
                if (!empty($input['user_id'])) {
                    $user_id = '(' . $input['user_id'] . ')';
                } else {
                    if (\Sentinel::getUser()->inRole('mini_terminal')) {
                        $user_id = '';
                        $input['user_id'] = $this->user->id;
                    } else {
                        $user_id = $input['user_id'];
                    }
                }
            }

            $boletas = \DB::table('boletas_depositos')
                ->select([
                    'boletas_depositos.id',
                    'fecha',
                    'users.description',
                    'tipo_pago.descripcion as tipo_pago',
                    'bancos.descripcion as banco',
                    'cuentas_bancarias.numero_banco as cuenta_bancaria',
                    'boleta_numero',

                    //'monto'
                    \DB::raw("(case when monto_anterior is not null then monto_anterior else monto end) as monto"),

                    'estado',
                    'boletas_depositos.updated_by as username',
                    'boletas_depositos.updated_at as update',
                    'message'
                ])
                ->join('cuentas_bancarias', 'cuentas_bancarias.id', '=', 'boletas_depositos.cuenta_bancaria_id')
                ->join('bancos', 'bancos.id', '=', 'cuentas_bancarias.banco_id')
                ->join('tipo_pago', 'tipo_pago.id', '=', 'boletas_depositos.tipo_pago_id')
                ->leftjoin('users', 'users.id', '=', 'boletas_depositos.user_id')
                ->whereRaw("$where")
                ->where(function ($query) use ($user_id) {
                    if (!empty($user_id)) {
                        $query->whereRaw('boletas_depositos.user_id in ' . $user_id);
                    } else {
                        if (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                            $supervisor = \DB::table('users_x_groups')->where('user_id', $this->user->id)->first();
                            $users = \DB::table('branches')
                                ->select(['users.id'])
                                ->join('users', 'branches.user_id', '=', 'users.id')
                                ->join('role_users', 'users.id', '=', 'role_users.user_id')
                                ->where('role_users.role_id', 22)
                                ->where('branches.group_id', $supervisor->group_id)
                                ->pluck('users.id');
                            $users_id = implode(',', $users);
                            $users_id .= ',' . $supervisor->user_id;

                            $query->whereRaw('boletas_depositos.user_id in (' . $users_id . ')');
                        } else if (\Sentinel::getUser()->inRole('mini_terminal')) {
                            $query->where('boletas_depositos.user_id', $this->user->id);
                        }
                    }
                })
                ->orderBy('boletas_depositos.id', 'desc')
                ->get();

            foreach ($boletas as $boleta) {
                $boleta->fecha = date('d/m/Y', strtotime($boleta->fecha));
                if ($boleta->estado == true) {
                    $boleta->estado = 'Confirmado';
                } else if ($boleta->estado == false) {
                    $boleta->estado = 'Rechazado';
                } else {
                    $boleta->estado = 'Pendiente';
                }
                if (isset($boleta->username)) {
                    $user = \DB::table('users')->where('id', $boleta->username)->first();
                    $boleta->username = $user->username;
                }
                $boleta->update = date('d/m/Y H:i:s', strtotime($boleta->update));
            }

            $resultset = array(
                'transactions'  => $boletas
            );

            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    public function comisionesReports()
    {
        try {
            if (!\Sentinel::getUser()->inRole('mini_terminal') && !\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                $branches = \DB::table('branches')
                    ->select(['branches.description', 'users.username', 'users.id'])
                    ->join('users', 'branches.user_id', '=', 'users.id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->get();
            } else if (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                $supervisor = \DB::table('users_x_groups')->where('user_id', $this->user->id)->first();
                $branches = \DB::table('branches')
                    ->select(['branches.description', 'users.username', 'users.id'])
                    ->join('users', 'branches.user_id', '=', 'users.id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->where('branches.group_id', $supervisor->group_id)
                    ->get();
            } else {
                $branches = \DB::table('branches')
                    ->select(['branches.description', 'users.username', 'users.id'])
                    ->join('users', 'branches.user_id', '=', 'users.id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->where('branches.user_id', $this->user->id)
                    ->get();
            }

            $data_select = [];
            foreach ($branches as $key => $branch) {
                $data_select[$branch->id] = $branch->description . ' | ' . $branch->username;
            }

            $resultset = array(
                'target' => 'Comisiones',
                'users' => $data_select,
                'user_id' => 0,
            );

            return $resultset;
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de reportes" . $e);
            return false;
        }
    }

    public function comisionesSearch($request)
    {
        try {
            $input = $this->input;
            if (!\Sentinel::getUser()->inRole('mini_terminal') && !\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                $branches = \DB::table('branches')
                    ->select(['branches.description', 'users.username', 'users.id'])
                    ->join('users', 'branches.user_id', '=', 'users.id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->get();

                $atms = \DB::table('branches')
                    ->select(['points_of_sale.atm_id'])
                    ->join('users', 'branches.user_id', '=', 'users.id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->join('points_of_sale', 'branches.id', '=', 'points_of_sale.branch_id')
                    ->where('role_users.role_id', 22)
                    ->whereNotNull('atm_id')
                    ->where(function ($query) use ($input) {
                        if (!empty($input['user_id'])) {
                            $query->where('branches.user_id', $input['user_id']);
                        }
                    })
                    ->pluck('atm_id', 'atm_id');
            } else if (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                $supervisor = \DB::table('users_x_groups')->where('user_id', $this->user->id)->first();
                $branches = \DB::table('branches')
                    ->select(['branches.description', 'users.username', 'users.id'])
                    ->join('users', 'branches.user_id', '=', 'users.id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->where('branches.group_id', $supervisor->group_id)
                    ->get();

                $atms = \DB::table('branches')
                    ->select(['points_of_sale.atm_id'])
                    ->join('users', 'branches.user_id', '=', 'users.id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->join('points_of_sale', 'branches.id', '=', 'points_of_sale.branch_id')
                    ->where('role_users.role_id', 22)
                    ->where('branches.group_id', $supervisor->group_id)
                    ->whereNotNull('atm_id')
                    ->where(function ($query) use ($input) {
                        if (!empty($input['user_id'])) {
                            $query->where('branches.user_id', $input['user_id']);
                        }
                    })
                    ->pluck('atm_id', 'atm_id');
            } else {
                $branches = \DB::table('branches')
                    ->select(['branches.description', 'users.username', 'users.id'])
                    ->join('users', 'branches.user_id', '=', 'users.id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->where('branches.user_id', $this->user->id)
                    ->get();

                $atms = \DB::table('branches')
                    ->select(['points_of_sale.atm_id'])
                    ->join('users', 'branches.user_id', '=', 'users.id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->join('points_of_sale', 'branches.id', '=', 'points_of_sale.branch_id')
                    ->where('role_users.role_id', 22)
                    ->where('branches.user_id', $this->user->id)
                    ->whereNotNull('atm_id')
                    ->pluck('atm_id', 'atm_id');
            }

            $atm_id = '(';
            foreach ($atms as $id_atm => $atm) {
                $atm_id .= $atm . ', ';
            }

            $atm_id = rtrim($atm_id, ', ');
            $atm_id .= ')';

            $data_select = [];
            foreach ($branches as $key => $branch) {
                $data_select[$branch->id] = $branch->description . ' | ' . $branch->username;
            }

            /*SET DATE RANGE*/
            $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
            $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
            $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));

            $transactions = \DB::select("
                with transacciones as (
                    select
                        service_source_id,
                        service_id,
                        transactions.atm_id,
                        atms.name as atm,
                        (CASE 
                            WHEN status = 'success' THEN 
                                abs(transactions.amount)
                            WHEN status = 'error' and transactions.service_id in(14, 15) THEN 
                                abs(transactions.amount)
                            else 0 
                        END) as monto
                    from
                        transactions
                    inner join
                        points_of_sale on points_of_sale.atm_id = transactions.atm_id
                    inner join
                        atms on atms.id = transactions.atm_id
                    left join mt_recibos_reversiones on
                        transactions.id = mt_recibos_reversiones.transaction_id
                    where
                        transactions.created_at between '{$daterange[0]}' AND '{$daterange[1]}' and
                        transactions.transaction_type in (1,7,12,13) and
                        mt_recibos_reversiones.transaction_id is null and
                        atms.id in " . $atm_id . "
                )
                select
                    atm,
                    sum(abs(monto)) as monto_total,
                    sum(abs(round(monto*parametros_comisiones.comision/100, 2))) as monto_comision,
                    transacciones.service_id,
                    transacciones.service_source_id,
                    service_providers.name AS provider,
                    service_provider_products.description AS servicio,
                    comision
                from
                    transacciones
                inner join
                    parametros_comisiones
                        on parametros_comisiones.service_id = transacciones.service_id
                        and parametros_comisiones.service_source_id = transacciones.service_source_id
                        and parametros_comisiones.atm_id = transacciones.atm_id
                left join
                    service_provider_products on service_provider_products.id = transacciones.service_id
                left join
                    service_providers on service_providers.id = service_provider_products.service_provider_id
                where
                    parametros_comisiones.deleted_at is null and
                    (
                        (parametros_comisiones.tipo_servicio_id = 1 and transacciones.service_source_id = 0) or
                        (parametros_comisiones.tipo_servicio_id = 0 and parametros_comisiones.service_source_id = transacciones.service_source_id)
                    )
                group by
                    transacciones.service_id,
                    transacciones.service_source_id,
                    service_providers.name,
                    service_provider_products.description,
                    atm,
                    parametros_comisiones.comision;
            ");

            $total_comisiones = \DB::select("
                with transacciones as (
                    select
                        service_source_id,
                        service_id,
                        transactions.atm_id,
                        atms.name as atm,
                        (CASE 
                            WHEN status = 'success' THEN 
                                transactions.amount
                            WHEN status = 'error' and transactions.service_id in(14, 15) THEN 
                                transactions.amount
                            else 0 
                        END) as monto
                        --transactions.amount
                    from
                        transactions
                    inner join
                        points_of_sale on points_of_sale.atm_id = transactions.atm_id
                    inner join
                        atms on atms.id = transactions.atm_id
                    left join
                        transactions_x_payments on transactions.id = transactions_x_payments.transactions_id
                    left join mt_recibos_reversiones on
                        transactions.id = mt_recibos_reversiones.transaction_id
                    left join
                        payments on payments.id = transactions_x_payments.payments_id
                    where
                        mt_recibos_reversiones.transaction_id is null and
                        transactions.created_at between '{$daterange[0]}' AND '{$daterange[1]}' and
                        transactions.transaction_type in (1,7,12,13) and
                        atms.id in " . $atm_id . "
                )
                select
                    sum(abs(monto)) as total_transacciones,
                    sum(abs(round(monto*parametros_comisiones.comision/100, 2))) as total
                from
                    transacciones
                inner join
                    parametros_comisiones
                        on parametros_comisiones.service_id = transacciones.service_id
                        and parametros_comisiones.service_source_id = transacciones.service_source_id
                        and parametros_comisiones.atm_id = transacciones.atm_id
                left join
                    service_provider_products on service_provider_products.id = transacciones.service_id
                left join
                    service_providers on service_providers.id = service_provider_products.service_provider_id
                where
                    parametros_comisiones.deleted_at is null and
                    (
                        (parametros_comisiones.tipo_servicio_id = 1 and transacciones.service_source_id = 0) or
                        (parametros_comisiones.tipo_servicio_id = 0 and parametros_comisiones.service_source_id = transacciones.service_source_id)
                    )
            ");

            foreach ($transactions as $transaction) {
                if ($transaction->service_source_id <> 0) {
                    $serv_provider = \DB::table('services_providers_sources')->where('id', $transaction->service_source_id)->first();
                    $transaction->provider = $serv_provider->description;

                    $service_data = \DB::table('services_ondanet_pairing')->where('service_request_id', $transaction->service_id)->where('service_source_id', $transaction->service_source_id)->first();
                    $transaction->servicio = isset($service_data->service_description) ? $service_data->service_description : '';
                }
            }

            $results = $this->arrayPaginator($transactions, $request);

            /*Carga datos del formulario*/
            $resultset = array(
                'target' => 'Comisiones',
                'total_comisiones' => $total_comisiones[0]->total,
                'total_transacciones' => $total_comisiones[0]->total_transacciones,
                'users' => $data_select,
                'user_id' => (isset($input['user_id'])) ? $input['user_id'] : null,
                'reservationtime' => (isset($input['reservationtime']) ? $input['reservationtime'] : 0),
                'i' => 1,
                'transactions' => $results,
            );

            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    public function comisionesSearchExport()
    {
        try {
            $input = $this->input;
            if (!\Sentinel::getUser()->inRole('mini_terminal') && !\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                $branches = \DB::table('branches')
                    ->select(['branches.description', 'users.username', 'users.id'])
                    ->join('users', 'branches.user_id', '=', 'users.id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->get();

                $atms = \DB::table('branches')
                    ->select(['points_of_sale.atm_id'])
                    ->join('users', 'branches.user_id', '=', 'users.id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->join('points_of_sale', 'branches.id', '=', 'points_of_sale.branch_id')
                    ->where('role_users.role_id', 22)
                    ->whereNotNull('atm_id')
                    ->where(function ($query) use ($input) {
                        if (!empty($input['user_id'])) {
                            $query->where('branches.user_id', $input['user_id']);
                        }
                    })
                    ->pluck('atm_id', 'atm_id');
            } elseif (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                $supervisor = \DB::table('users_x_groups')->where('user_id', $this->user->id)->first();
                $branches = \DB::table('branches')
                    ->select(['branches.description', 'users.username', 'users.id'])
                    ->join('users', 'branches.user_id', '=', 'users.id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('branches.group_id', $supervisor->group_id)
                    ->where('role_users.role_id', 22)
                    ->get();

                $atms = \DB::table('branches')
                    ->select(['points_of_sale.atm_id'])
                    ->join('users', 'branches.user_id', '=', 'users.id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->join('points_of_sale', 'branches.id', '=', 'points_of_sale.branch_id')
                    ->where('role_users.role_id', 22)
                    ->where('branches.group_id', $supervisor->group_id)
                    ->whereNotNull('atm_id')
                    ->where(function ($query) use ($input) {
                        if (!empty($input['user_id'])) {
                            $query->where('branches.user_id', $input['user_id']);
                        }
                    })
                    ->pluck('atm_id', 'atm_id');
            } else {
                $branches = \DB::table('branches')
                    ->select(['branches.description', 'users.username', 'users.id'])
                    ->join('users', 'branches.user_id', '=', 'users.id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->where('branches.user_id', $this->user->id)
                    ->get();

                $atms = \DB::table('branches')
                    ->select(['points_of_sale.atm_id'])
                    ->join('users', 'branches.user_id', '=', 'users.id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->join('points_of_sale', 'branches.id', '=', 'points_of_sale.branch_id')
                    ->where('role_users.role_id', 22)
                    ->where('branches.user_id', $this->user->id)
                    ->whereNotNull('atm_id')
                    ->pluck('atm_id', 'atm_id');
            }

            $atm_id = '(';
            foreach ($atms as $id_atm => $atm) {
                $atm_id .= $atm . ', ';
            }

            $atm_id = rtrim($atm_id, ', ');
            $atm_id .= ')';

            /*SET DATE RANGE*/
            $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
            $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
            $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));

            $transactions = \DB::select("
                with transacciones as (
                    select
                        service_source_id,
                        service_id,
                        transactions.atm_id,
                        atms.name as atm,
                        (CASE 
                            WHEN status = 'success' THEN 
                                abs(transactions.amount)
                            WHEN status = 'error' and transactions.service_id in(14, 15) THEN 
                                abs(transactions.amount)
                            else 0 
                        END) as monto
                        --transactions.amount
                    from
                        transactions
                    inner join
                        points_of_sale on points_of_sale.atm_id = transactions.atm_id
                    inner join
                        atms on atms.id = transactions.atm_id
                    left join
                        transactions_x_payments on transactions.id = transactions_x_payments.transactions_id
                    left join mt_recibos_reversiones on
                        transactions.id = mt_recibos_reversiones.transaction_id
                    where
                        transactions.created_at between '{$daterange[0]}' AND '{$daterange[1]}' and
                        transactions.transaction_type in (1,7,12,13) and
                        mt_recibos_reversiones.transaction_id is null and
                        atms.id in " . $atm_id . "
                )
                select
                    atm,
                    service_providers.name AS provider,
                    service_provider_products.description AS servicio,
                    abs(monto) as monto_total,
                    abs(round(monto*parametros_comisiones.comision/100, 2)) as monto_comision,
                    comision,
                    transacciones.service_id,
                    transacciones.service_source_id
                from
                    transacciones
                inner join
                    parametros_comisiones
                        on parametros_comisiones.service_id = transacciones.service_id
                        and parametros_comisiones.service_source_id = transacciones.service_source_id
                        and parametros_comisiones.atm_id = transacciones.atm_id
                left join
                    service_provider_products on service_provider_products.id = transacciones.service_id
                left join
                    service_providers on service_providers.id = service_provider_products.service_provider_id
                where
                    parametros_comisiones.deleted_at is null and
                    (
                        (parametros_comisiones.tipo_servicio_id = 1 and transacciones.service_source_id = 0) or
                        (parametros_comisiones.tipo_servicio_id = 0 and parametros_comisiones.service_source_id = transacciones.service_source_id)
                    )
                order by service_id desc;
            ");

            foreach ($transactions as $transaction) {

                if ($transaction->service_source_id <> 0) {
                    $serv_provider = \DB::table('services_providers_sources')->where('id', $transaction->service_source_id)->first();
                    $transaction->provider = $serv_provider->description;
                    $service_data = \DB::table('services_ondanet_pairing')->where('service_request_id', $transaction->service_id)->where('service_source_id', $transaction->service_source_id)->first();
                    //$transaction->proveedor .= isset($service_data->service_description)?$service_data->service_description:'';
                    $transaction->servicio  = isset($service_data->service_description) ? $service_data->service_description : '';
                }

                if ($transaction->provider == 'Infonet') {
                    $transaction->provider = 'IC';
                }

                $transaction->monto_total = number_format($transaction->monto_total, 0, '', '');
                $transaction->monto_comision = round($transaction->monto_comision, 2);
                $transaction->comision = round($transaction->comision, 2) . '%';

                unset($transaction->service_source_id);
                unset($transaction->service_id);
                unset($transaction->codigo_cajero);
                // unset($transaction->proveedor);
            }

            return $transactions;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    public function salesReports()
    {
        try {
            if (!\Sentinel::getUser()->inRole('mini_terminal')) {
                $branches = \DB::table('business_groups')
                    ->select(['business_groups.description', 'business_groups.ruc', 'business_groups.id'])
                    ->join('branches', 'branches.group_id', '=', 'business_groups.id')
                    ->join('users', 'branches.user_id', '=', 'users.id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->get();
            }

            $data_select = [];
            foreach ($branches as $key => $branch) {
                $data_select[$branch->id] = $branch->description . ' | ' . $branch->ruc;
            }

            $resultset = array(
                'target' => 'Ventas Old',
                'groups' => $data_select,
                'group_id' => 0,
                'mostrar' => 'todos',
            );

            return $resultset;
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de reportes" . $e);
            return false;
        }
    }

    public function salesSearch($request)
    {
        try {
            $input = $this->input;
            /*SET DATE RANGE*/
            $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
            $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
            $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
            $where = "miniterminales_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
            $where .= " AND movements.destination_operation_id not in ('0','-2','-3','-4','-5','-6','-9','-10','-11','-12','-13','-14','-16','-16','-17','-21','-23','-26','-27','212')";

            /*SET OWNER*/

            if (!\Sentinel::getUser()->inRole('mini_terminal') && !\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                if ($input['group_id'] != "") {
                    $where .= " AND current_account.group_id = " . $input['group_id'] . "";
                }

                if ($input['mostrar'] != "todos") {

                    if ($input['mostrar'] == "transacciones") {
                        $movement_type = 1;
                    }
                    if ($input['mostrar'] == "paquetigos") {
                        $movement_type = 6;
                    }
                    if ($input['mostrar'] == "cashouts") {
                        $movement_type = 12;
                    }
                    if ($input['mostrar'] == "personal") {
                        $movement_type = 13;
                    }
                    if ($input['mostrar'] == "claro") {
                        $movement_type = 14;
                    }

                    $where .= " AND movements.movement_type_id = " . $movement_type . "";
                }

                $branches = \DB::table('business_groups')
                    ->select(['business_groups.description', 'business_groups.ruc', 'business_groups.id'])
                    ->join('branches', 'branches.group_id', '=', 'business_groups.id')
                    ->join('users', 'branches.user_id', '=', 'users.id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->get();

                $data_select = [];
                foreach ($branches as $key => $branch) {
                    $data_select[$branch->id] = $branch->description . ' | ' . $branch->ruc;
                }
            } else if (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                $supervisor = \DB::table('users_x_groups')->where('user_id', $this->user->id)->first();
                $groupId = $supervisor->group_id;
                $where .= " AND current_account.group_id = " . $groupId . " ";

                if ($input['mostrar'] != "todos") {

                    if ($input['mostrar'] == "transacciones") {
                        $movement_type = 1;
                    }
                    if ($input['mostrar'] == "paquetigos") {
                        $movement_type = 6;
                    }
                    if ($input['mostrar'] == "cashouts") {
                        $movement_type = 12;
                    }
                    if ($input['mostrar'] == "personal") {
                        $movement_type = 13;
                    }
                    if ($input['mostrar'] == "claro") {
                        $movement_type = 14;
                    }

                    $where .= " AND movements.movement_type_id = " . $movement_type . "";
                }

                $data_select = [];
            }

            $transactions = \DB::table('movements')
                ->select(['movements.id', 'business_groups.description', 'movements.amount', 'miniterminales_sales.fecha', 'movements.destination_operation_id', 'miniterminales_sales.nro_venta', 'miniterminales_sales.estado', 'miniterminales_sales.monto_por_cobrar'])
                ->join('miniterminales_sales', 'miniterminales_sales.movements_id', '=', 'movements.id')
                ->join('current_account', 'current_account.movement_id', '=', 'movements.id')
                ->join('business_groups', 'business_groups.id', '=', 'current_account.group_id')
                ->whereNull('movements.deleted_at')
                ->whereRaw("$where")
                ->orderBy('miniterminales_sales.fecha', 'desc')
                ->orderBy('miniterminales_sales.nro_venta', 'desc')
                ->get();

            $total_transactions = \DB::table('movements')
                ->select(\DB::raw('sum(abs(movements.amount)) as monto, sum(abs(miniterminales_sales.monto_por_cobrar)) as monto_por_cobrar'))
                ->join('miniterminales_sales', 'miniterminales_sales.movements_id', '=', 'movements.id')
                ->join('current_account', 'current_account.movement_id', '=', 'movements.id')
                ->join('business_groups', 'business_groups.id', '=', 'current_account.group_id')
                ->whereNull('movements.deleted_at')
                ->whereRaw("$where")
                ->get();

            $results = $this->arrayPaginator($transactions->toArray(), $request);

            /*Carga datos del formulario*/
            $resultset = array(
                'target' => 'Ventas Old',
                'total_monto' => $total_transactions[0]->monto,
                'total_monto_por_cobrar' => $total_transactions[0]->monto_por_cobrar,
                'reservationtime' => (isset($input['reservationtime']) ? $input['reservationtime'] : 0),
                'groups' => $data_select,
                'i'             =>  1,
                'group_id' => (isset($input['group_id'])) ? $input['group_id'] : null,
                'transactions' => $results,
                'mostrar' => $input['mostrar'],
            );

            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    public function salesSearchExport()
    {
        try {
            $input = $this->input;

            /*SET DATE RANGE*/
            $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
            $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
            $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));

            $where = "miniterminales_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
            $where .= " AND movements.destination_operation_id not in ('0','-2','-3','-4','-5','-6','-9','-10','-11','-12','-13','-14','-16','-16','-17','-21','-23','-26','-27','212') ";

            /*SET OWNER*/

            if (!\Sentinel::getUser()->inRole('mini_terminal') && !\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                if ($input['group_id'] != "") {
                    $where .= " and current_account.group_id = " . $input['group_id'] . "";
                }

                $branches = \DB::table('business_groups')
                    ->select(['business_groups.description', 'business_groups.ruc', 'business_groups.id'])
                    ->join('branches', 'branches.group_id', '=', 'business_groups.id')
                    ->join('users', 'branches.user_id', '=', 'users.id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->get();

                $data_select = [];
                foreach ($branches as $key => $branch) {
                    $data_select[$branch->id] = $branch->description . ' | ' . $branch->ruc;
                }
            } else if (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                $supervisor = \DB::table('users_x_groups')->where('user_id', $this->user->id)->first();
                $groupId = $supervisor->group_id;
                $where .= " AND current_account.group_id = " . $groupId . " ";

                $data_select = [];
            }

            $transactions = \DB::table('movements')
                ->select(['movements.id', 'business_groups.description', 'movements.amount', 'miniterminales_sales.fecha', 'movements.destination_operation_id', 'miniterminales_sales.nro_venta', 'miniterminales_sales.estado', 'miniterminales_sales.monto_por_cobrar'])
                ->join('miniterminales_sales', 'miniterminales_sales.movements_id', '=', 'movements.id')
                ->join('current_account', 'current_account.movement_id', '=', 'movements.id')
                ->join('business_groups', 'business_groups.id', '=', 'current_account.group_id')
                ->whereNull('movements.deleted_at')
                ->whereRaw("$where")
                ->orderBy('miniterminales_sales.fecha', 'desc')
                ->orderBy('miniterminales_sales.nro_venta', 'desc')
                ->get();


            /*Carga datos del formulario*/
            $resultset = array(
                'transactions' => $transactions
            );

            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    public function cobranzasReports()
    {
        try {
            if (!\Sentinel::getUser()->inRole('mini_terminal')) {
                $branches = \DB::table('business_groups')
                    ->select(['business_groups.description', 'business_groups.ruc', 'business_groups.id'])
                    ->join('branches', 'branches.group_id', '=', 'business_groups.id')
                    ->join('users', 'branches.user_id', '=', 'users.id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->get();
            }

            $data_select = [];
            foreach ($branches as $key => $branch) {
                $data_select[$branch->id] = $branch->description . ' | ' . $branch->ruc;
            }

            $resultset = array(
                'target' => 'Cobranzas',
                'groups' => $data_select,
                'group_id' => 0,
            );
            return $resultset;
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de reportes" . $e);
            return false;
        }
    }



    public function cobranzasSearch($request)
    {
        try {
            $input = $this->input;

            //dd($input);

            /*SET DATE RANGE*/
            $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
            $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
            $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
            $where = "boletas_depositos.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' ";
            /*SET OWNER*/
            if (!\Sentinel::getUser()->inRole('mini_terminal') && !\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                $branches = \DB::table('business_groups')
                    ->select(['business_groups.description', 'business_groups.ruc', 'business_groups.id'])
                    ->join('branches', 'branches.group_id', '=', 'business_groups.id')
                    ->join('users', 'branches.user_id', '=', 'users.id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->get();

                $data_select = [];
                foreach ($branches as $key => $branch) {
                    $data_select[$branch->id] = $branch->description . ' | ' . $branch->ruc;
                }

                if ($input['group_id'] != "") {
                    $where .= "AND current_account.group_id = " . $input['group_id'];
                }
            } elseif (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                $supervisor = \DB::table('users_x_groups')->where('user_id', $this->user->id)->first();
                $groupId = $supervisor->group_id;
                $where .= "AND current_account.group_id =  " . $groupId . " ";

                $data_select = [];
            }

            $where .= "AND movements.destination_operation_id not in ('0','-1','-2','-3','-4','-5','-6','-7','-8','-9','-10', '-23','212')";

            /*$transactions = \DB::table('movements')
                ->select(['movements.id', 'business_groups.description', 'boletas_depositos.boleta_numero', 'movements.created_at', 'movements.destination_operation_id', 'miniterminales_cobranzas.recibo_nro', 'boletas_depositos.monto', 'miniterminales_cobranzas.ventas_cobradas'])
                ->join('miniterminales_cobranzas', 'miniterminales_cobranzas.movements_id', '=', 'movements.id')
                ->join('current_account', 'current_account.movement_id', '=', 'movements.id')
                ->join('business_groups', 'business_groups.id', '=', 'current_account.group_id')
                ->join('boletas_depositos', 'boletas_depositos.id', '=', 'miniterminales_cobranzas.boleta_deposito_id')
                ->whereNull('movements.deleted_at')
                ->whereRaw("$where")
                ->orderBy('movements.created_at','desc')
                ->orderBy('miniterminales_cobranzas.recibo_nro','desc')
                ->get();*/

            $transactions = \DB::table('movements')
                ->select(['movements.id', 'business_groups.description', 'boletas_depositos.boleta_numero', 'movements.created_at', 'movements.destination_operation_id', 'mt_recibos.recibo_nro', 'boletas_depositos.monto', 'mt_recibos_cobranzas.ventas_cobradas'])
                ->join('current_account', 'current_account.movement_id', '=', 'movements.id')
                ->join('mt_recibos', 'mt_recibos.movements_id', '=', 'movements.id')
                ->join('mt_recibos_cobranzas', 'mt_recibos.id', '=', 'mt_recibos_cobranzas.recibo_id')
                ->join('boletas_depositos', 'boletas_depositos.id', '=', 'mt_recibos_cobranzas.boleta_deposito_id')
                //->join('miniterminales_cobranzas', 'miniterminales_cobranzas.movements_id', '=', 'movements.id')                                
                ->join('business_groups', 'business_groups.id', '=', 'current_account.group_id')
                ->whereNull('movements.deleted_at')
                ->whereRaw("$where")
                ->orderBy('movements.created_at', 'desc')
                ->orderBy('mt_recibos.recibo_nro', 'desc')
                ->get();

            //dd($transactions);


            foreach ($transactions as $transaction) {
                \Log::info(implode(', ', explode(';', $transaction->ventas_cobradas)));
            }
            /*$total_transactions = \DB::table('miniterminales_sales')
                    ->select(\DB::raw('sum(abs(miniterminales_sales.amount)) as monto, sum(abs(miniterminales_sales.monto_por_cobrar)) as monto_por_cobrar'))
                    ->join('business_groups', 'business_groups.id', '=', 'miniterminales_sales.group_id')
                    ->whereRaw("$where")
                    ->get();*/


            $results = $this->arrayPaginator($transactions->toArray(), $request);

            /*Carga datos del formulario*/
            $resultset = array(
                'target' => 'Cobranzas',
                /*'total_monto' => $total_transactions[0]->monto,
                'total_monto_por_cobrar' => $total_transactions[0]->monto_por_cobrar,*/
                'reservationtime' => (isset($input['reservationtime']) ? $input['reservationtime'] : 0),
                'groups' => $data_select,
                'i'             =>  1,
                'group_id' => (isset($input['group_id'])) ? $input['group_id'] : '',
                'transactions' => $results,
            );

            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    public function cobranzasSearchExport($request)
    {
        try {
            $input = $this->input;

            //dd($input);

            /*SET DATE RANGE*/
            $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
            $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
            $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
            $where = "boletas_depositos.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' ";
            /*SET OWNER*/
            if (!\Sentinel::getUser()->inRole('mini_terminal') && !\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                $branches = \DB::table('business_groups')
                    ->select(['business_groups.description', 'business_groups.ruc', 'business_groups.id'])
                    ->join('branches', 'branches.group_id', '=', 'business_groups.id')
                    ->join('users', 'branches.user_id', '=', 'users.id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->get();

                $data_select = [];
                foreach ($branches as $key => $branch) {
                    $data_select[$branch->id] = $branch->description . ' | ' . $branch->ruc;
                }

                if ($input['group_id'] != "") {
                    $where .= "AND current_account.group_id = " . $input['group_id'];
                }
            } elseif (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                $supervisor = \DB::table('users_x_groups')->where('user_id', $this->user->id)->first();
                $groupId = $supervisor->group_id;
                $where .= "AND current_account.group_id =  " . $groupId . " ";

                $data_select = [];
            }

            $where .= "AND movements.destination_operation_id not in ('0','-1','-2','-3','-4','-5','-6','-7','-8','-9','-10', '-23','212')";

            /*$transactions = \DB::table('movements')
            ->select(['movements.id', 'business_groups.description', 'boletas_depositos.boleta_numero', 'movements.created_at', 'movements.destination_operation_id', 'miniterminales_cobranzas.recibo_nro', 'boletas_depositos.monto', 'miniterminales_cobranzas.ventas_cobradas'])
            ->join('miniterminales_cobranzas', 'miniterminales_cobranzas.movements_id', '=', 'movements.id')
            ->join('current_account', 'current_account.movement_id', '=', 'movements.id')
            ->join('business_groups', 'business_groups.id', '=', 'current_account.group_id')
            ->join('boletas_depositos', 'boletas_depositos.id', '=', 'miniterminales_cobranzas.boleta_deposito_id')
            ->whereNull('movements.deleted_at')
            ->whereRaw("$where")
            ->orderBy('movements.created_at','desc')
            ->orderBy('miniterminales_cobranzas.recibo_nro','desc')
            ->get();*/

            $transactions = \DB::table('movements')
                ->select(['movements.id', 'business_groups.description', 'boletas_depositos.boleta_numero', 'movements.created_at', 'movements.destination_operation_id', 'mt_recibos.recibo_nro', 'boletas_depositos.monto', 'mt_recibos_cobranzas.ventas_cobradas'])
                ->join('current_account', 'current_account.movement_id', '=', 'movements.id')
                ->join('mt_recibos', 'mt_recibos.movements_id', '=', 'movements.id')
                ->join('mt_recibos_cobranzas', 'mt_recibos_cobranzas.recibo_id', '=', 'mt_recibos')
                ->join('boletas_depositos', 'boletas_depositos.id', '=', 'mt_recibos_cobranzas.boleta_deposito_id')
                ->join('business_groups', 'business_groups.id', '=', 'current_account.group_id')

                ->whereNull('movements.deleted_at')
                ->whereRaw("$where")
                ->orderBy('movements.created_at', 'desc')
                ->orderBy('mt_recibos.recibo_nro', 'desc')
                ->get();


            foreach ($transactions as $transaction) {
                \Log::info(implode(', ', explode(';', $transaction->ventas_cobradas)));
            }

            return $transactions;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    public function arrayPaginator($array, $request)
    {
        $page = $request->input('page', 1);
        $perPage = 20;
        $offset = ($page * $perPage) - $perPage;

        return new \Illuminate\Pagination\LengthAwarePaginator(
            array_slice($array, $offset, $perPage, true),
            count($array),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );
    }

    /** REPORTE CONTROL EFECTIVO CORTE INICIO / FIN DEL DÍA */

    public function historico_saldos_en_linea()
    {
        try {
            $cash_flow = \DB::table("atms_parts")
                ->select(\DB::raw("atm_id, tipo_partes, sum(round(denominacion::DECIMAL * cantidad_actual::DECIMAL)) AS subtotal"))
                ->join("atms", "atms.id", "=", "atms_parts.atm_id")
                ->where("atms.deleted_at", null)
                ->whereNotIn("owner_id", [16, 21, 25])
                ->where("type", "=", "at")
                ->where("atms_parts.activo", "=", true)
                ->whereNotIn("tipo_partes", ["DispTarj", "DispTarjPurga"])
                ->groupBy("atms_parts.atm_id")
                ->groupBy("atms_parts.tipo_partes")
                ->orderBy("atm_id")
                ->get();
            

            $data = array();
            $total = 0;
            $current_atm_id = 0;
            foreach ($cash_flow as $key => $item) {
                if ($current_atm_id == 0) {
                    $current_atm_id = $item->atm_id;
                }
                $tipo_partes = strtolower($item->tipo_partes);
                $data[$item->atm_id][$tipo_partes] = $item->subtotal;
                $data[$item->atm_id]['atm_id'] = $item->atm_id;

                if ($current_atm_id == $item->atm_id) {
                    $total = $total + $item->subtotal;
                } else {
                    $total = 0;
                    $total = $total + $item->subtotal;
                    $current_atm_id = $item->atm_id;
                }

                if (!isset($data[$item->atm_id]['purga'])) {
                    $data[$item->atm_id]['purga'] = 0;
                }

                $data[$item->atm_id]['total'] = $total;
            }

            ksort($data, SORT_NUMERIC);
            
            \DB::table("historico_contable_saldos")->insert($data);
            return $data;
        } catch (\Exception $e) {
            \Log::warning('Error al generar historico_contable_saldos',['result'=>$e]);

            $data = [
                'user_name'    => 'Sistemas',
                'fecha'        => \Carbon::now(),                                                 
            ];     

            Mail::send('mails.alert_historico_saldos',$data,
                function($message){
                    $user_email = 'sistemas@eglobalt.com.py';
                    $user_name  = 'Sistemas';
                    $message->to($user_email, $user_name)->subject('[EGLOBAL] Alerta de Historico de saldos en línea');
            });

            return $e;
        }
    }

    /**
     * Reportes SALDOS EN LÍNEA
     *
     */
    public function saldos_control_contable_search()
    {
        try {
            $input = $this->input;


            /*SET DATE RANGE*/
            $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
            $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
            $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
            $where = "historico_contable_saldos.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";

            $saldos = \DB::table('historico_contable_saldos')
                ->select('deposit_code', 'historico_contable_saldos.atm_id', 'name', 'description', 'box', 'cassette', 'hopper', 'purga', 'total', 'historico_contable_saldos.created_at')
                ->join('atms', 'atms.id', '=', 'historico_contable_saldos.atm_id')
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'atms.id')
                ->whereRaw("$where")
                ->orderBy('description', 'ASC')
                ->paginate(40);

            $resultset = array(
                'target'            => 'Saldos Contable',
                'reservationtime'   => (isset($input['reservationtime']) ? $input['reservationtime'] : 0),
                'saldos'            => $saldos,
                'i'                 => 1
            );
            return $resultset;
        } catch (\Exception $e) {
            \Log::warning('Error en reportes :' . $e);
        }
    }

    public function saldos_control_contable_export()
    {
        try {
            $input = $this->input;


            /*SET DATE RANGE*/
            $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
            $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
            $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
            $where = "historico_contable_saldos.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";

            $saldos = \DB::table('historico_contable_saldos')
                ->select(\DB::raw("concat(deposit_code, ' - ', description) as description , cassette, hopper, box, purga, total, to_char(historico_contable_saldos.created_at,'HH24:MI')  as hora, to_char(historico_contable_saldos.created_at,'DD/MM/YYYY') as fecha"))
                ->join('atms', 'atms.id', '=', 'historico_contable_saldos.atm_id')
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'atms.id')
                ->orderBy('description', 'ASC')
                ->whereRaw("$where")
                ->get();
            return $saldos;
        } catch (\Exception $e) {
            \Log::warning('Error en reportes :' . $e);
        }
    }

    /**
     * Reportes PDV
     *
     */

    public function get_pdv_transactions_list_search($decoded_atm_id, $atm_id)
    {
        try {

            if (!is_numeric($decoded_atm_id)) {
                $resultset = array(
                    'error'        => true,
                );
                return $resultset;
            }

            $atm = Atm::find($decoded_atm_id);

            $owner_id = $atm->owner_id;

            if ($atm->type <> 'da' and ($owner_id !== 16 and $owner_id !== 21 and $owner_id !== 25)) {
                $resultset = array(
                    'error'        => true,
                );
                return $resultset;
            }



            $input = $this->input;
            $where = "transactions.atm_id = " . $decoded_atm_id;

            /*SET DATE RANGE*/
            if (isset($input['reservationtime'])) {
                $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                $where .= " AND transactions.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
            } else {
                $daterange = [
                    0 => date('Y-m-d 00:00:00'),
                    1 => date('Y-m-d 23:59:59')
                ];
                $where .= " AND transactions.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
            }

            $transactions = \DB::table('transactions')
                ->select(\DB::raw('transactions.id, transactions.amount, transactions.service_id, transactions.atm_transaction_id,transactions.service_source_id,transactions.identificador_transaction_id,transactions.factura_numero,  
            (CASE 
                WHEN transactions.service_source_id <> 0 THEN services_providers_sources.description  
                ELSE service_provider_products.description 
            END) as servicio, 
            (CASE 
                WHEN transactions.service_source_id <> 0 THEN services_ondanet_pairing.service_description  
                ELSE service_providers.name
            END) as provider,
            transactions.created_at, transactions.status,transactions.status as estado, transactions.status_description, points_of_sale.description as sede,referencia_numero_1,referencia_numero_2, atms.code as code, rollback_credito_pdv_sync.response_bank_transaction_id'))
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'transactions.atm_id')
                ->join('atms', 'atms.id', '=', 'transactions.atm_id')
                ->leftjoin('rollback_credito_pdv_sync', 'rollback_credito_pdv_sync.backend_transaction_id', '=', 'transactions.id')
                ->leftjoin('service_provider_products', 'service_provider_products.id', '=', 'transactions.service_id')
                ->leftjoin('service_providers', 'service_providers.id', '=', 'service_provider_products.service_provider_id')
                ->leftjoin('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->leftjoin('services_ondanet_pairing', function ($join) {
                    $join->on('services_ondanet_pairing.service_request_id', '=', 'transactions.service_id')
                        ->on('services_ondanet_pairing.service_source_id', '=', 'transactions.service_source_id');
                })
                ->leftjoin('services_providers_sources', 'services_providers_sources.id', '=', 'transactions.service_source_id')
                ->whereRaw("$where")
                ->orderBy('transactions.created_at', 'desc')
                ->paginate(40);

            if ($transactions) {
                $error = false;
            } else {
                $error = true;
            }

            $resultset = array(
                'error'        => $error,
                'target'       => 'transacciones_pdv_da',
                'transactions' => $transactions,
                'pdv'          => $atm->name,
                'atm_id'       => $atm_id,
                'owner_id'       => $owner_id
            );
            return $resultset;
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de reportes" . $e->getMessage());
            $resultset = array(
                'error'        => true,
            );
            return $resultset;
        }
    }

    public function get_pdv_transactions_list_export($decoded_atm_id, $atm_id)
    {
        try {
            if (!is_numeric($decoded_atm_id)) {
                $resultset = array(
                    'error'        => true,
                );
                return $resultset;
            }
            $input = $this->input;
            $atm = Atm::find($decoded_atm_id);
            $where = "transactions.atm_id = " . $decoded_atm_id;

            /*SET DATE RANGE*/
            $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
            $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
            $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
            $where .= " AND transactions.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";


            $transactions = \DB::table('transactions')
                ->select(\DB::raw('transactions.id,  
            (CASE 
                WHEN transactions.service_source_id <> 0 THEN services_providers_sources.description  
                ELSE service_provider_products.description 
            END) as servicio, 
            (CASE 
                WHEN transactions.service_source_id <> 0 THEN services_ondanet_pairing.service_description  
                ELSE service_providers.name
            END) as provider,
            transactions.status as estado,transactions.created_at, transactions.amount, transactions.factura_numero as identificador_debito, rollback_credito_pdv_sync.response_bank_transaction_id as identificador_credito, points_of_sale.description as sede,referencia_numero_1,referencia_numero_2'))
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'transactions.atm_id')
                ->join('atms', 'atms.id', '=', 'transactions.atm_id')
                ->leftjoin('rollback_credito_pdv_sync', 'rollback_credito_pdv_sync.backend_transaction_id', '=', 'transactions.id')
                ->leftjoin('service_provider_products', 'service_provider_products.id', '=', 'transactions.service_id')
                ->leftjoin('service_providers', 'service_providers.id', '=', 'service_provider_products.service_provider_id')
                ->leftjoin('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->leftjoin('services_ondanet_pairing', function ($join) {
                    $join->on('services_ondanet_pairing.service_request_id', '=', 'transactions.service_id')
                        ->on('services_ondanet_pairing.service_source_id', '=', 'transactions.service_source_id');
                })
                ->leftjoin('services_providers_sources', 'services_providers_sources.id', '=', 'transactions.service_source_id')
                ->whereRaw("$where")
                ->orderBy('transactions.created_at', 'desc')
                ->get();
            return $transactions;
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de reportes" . $e->getMessage());
            $resultset = array(
                'error'        => true,
            );
            return $resultset;
        }
    }

    /** ESTADO DE INSTALACIONES */
    public function statusInstallations()
    {
        try {

            $resultset = array(
                'target'        => 'Instalaciones APP-Billetaje',
            );

            return $resultset;
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de reportes" . $e);
            return false;
        }
    }

    public function statusInstallationsSearch()
    {
        try {
            $input = $this->input;
            $where = " atms.type = 'da' AND ";

            /*Busqueda minusiosa*/
            if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
                $where .= "atms.id = {$input['context']} OR ";
                $where .= "atms.name = '{$input['context']}' AND ";
            } else {
                /*SET DATE RANGE*/
                $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                $where .= "atms.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' AND ";
            }

            $where = trim($where);
            $where = trim($where, 'AND');
            $where = trim($where);

            $installations = \DB::table('atms')
                ->select(\DB::raw('atms.id, atms.created_at, atms.name, device.serialnumber,atms.compile_version, branches.latitud, branches.longitud,branches.phone'))
                ->join('housing', 'housing.id', '=', 'atms.housing_id')
                ->join('device', 'device.housing_id', '=', 'housing.id')
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'atms.id')
                ->leftjoin('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->whereRaw("$where")
                ->orderBy('atms.created_at', 'asc')
                ->orderBy('atms.name', 'asc')
                ->paginate(20);
            //->toSql();
            // dd($installations);

            $resultset = array(
                'target'        => 'Instalaciones APP-Billetaje',
                'installations'  => $installations,
                'reservationtime' => (isset($input['reservationtime']) ? $input['reservationtime'] : 0),
                'i'             =>  1,
            );
            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    public function statusInstallationsSearchExport()
    {
        try {
            $input = $this->input;
            $where = " atms.type = 'da' AND ";
            /*Busqueda minusiosa*/
            if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
                $where .= "atms.id = {$input['context']} OR ";
                $where .= "atms.name = '{$input['context']}' AND ";
            } else {
                /*SET DATE RANGE*/
                $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                $where .= "atms.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' AND ";
            }

            $where = trim($where);
            $where = trim($where, 'AND');
            $where = trim($where);

            $installations = \DB::table('atms')
                ->select(\DB::raw('atms.id as id, atms.created_at as fecha, atms.name as sede, device.serialnumber as serial,
                                    branches.phone as numeroPDV, atms.compile_version as version, branches.latitud as latitud,
                                    branches.longitud as longitud'))
                ->join('housing', 'housing.id', '=', 'atms.housing_id')
                ->join('device', 'device.housing_id', '=', 'housing.id')
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'atms.id')
                ->leftjoin('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->whereRaw("$where")
                ->orderBy('atms.created_at', 'asc')
                ->orderBy('atms.name', 'asc')
                ->get();

            foreach ($installations as  $installation) {
                $installation->fecha = date('d/m/Y H:i:s', strtotime($installation->fecha));
            }

            $resultset = array(
                'instalaciones'  => $installations
            );

            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }


    /*** EFECTIVIDAD */
    public function efectividad()
    {
        try {

            //Redes
            $whereGroup = "";
            $whereOwner = "";
            $whereBranch = "";
            $wherePos = "";
            if (!$this->user->hasAccess('superuser')) {
                if ($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11) {
                    $whereOwner = "owners.id = " . $this->user->owner_id;
                    $whereBranch = "branches.owner_id = " . $this->user->owner_id;
                    $wherePos = "points_of_sale.owner_id = " . $this->user->owner_id;
                }
            }
            //Redes

            $owners     = Owner::orderBy('owners.name')->where(function ($query) use ($whereOwner) {
                if (!empty($whereOwner)) {
                    $query->whereRaw($whereOwner);
                }
            })->get()->pluck('name', 'id');
            $owners->prepend('Todos', '0');

            $branches   = Branch::orderBy('description')->where(function ($query) use ($whereBranch) {
                if (!empty($whereBranch)) {
                    $query->whereRaw($whereBranch);
                }
            })->get()->pluck('description', 'id');
            $branches->prepend('Todos', '0');

            $pdvs       = Pos::orderBy('description')->where(function ($query) use ($wherePos) {
                if (!empty($wherePos)) {
                    $query->whereRaw($wherePos);
                }
            })->with('Atm')->get();

            $pos = [];
            $item = array();
            $item[0] = 'Todos';

            foreach ($pdvs  as $pdv) {
                $item[$pdv->id] = $pdv->description . ' - ' . $pdv->Atm->code;
                $pos = $item;
            }
            $status = array('0' => 'Todos', 'success' => 'Aprobado', 'canceled' => 'Cancelado', 'error' => 'Error', 'rollback' => 'Reversado', 'iniciated' => 'Iniciado', 'error dispositivo' => 'Error de dispositivo', 'error dispositivo' => 'Error de dispositivo', 'devolucion' => 'Devolución', 'inconsistency' => 'Inconsistencia');
            $atmType = array('0' => 'Todos', 'da' => 'App Billetaje', 'ws' => 'Web Service', 'at' => 'Atm');
            $services   = ServiceProviderProduct::with('WebServiceProvider')->orderBy('service_provider_id', 'DESC')->get();
            $services_data = [];
            $service_item = array();

            if ($this->user->hasAccess('ticketea') && !$this->user->hasAccess('superuser')) {
                $service_item[28] = 'Ticketea - Venta de tickets';
                $services_data = $service_item;
            } else {
                $service_item[0] = 'Todos';
                $service_item[-2] = 'Netel';
                $service_item[-4] = 'Pronet';
                $service_item[-6] = 'Practipago';
                $service_item[-7] = 'Infonet';
                $service_item[-8] = 'Toval';
                $service_item[-10] = 'Netel Trex';
                $service_item[-11] = 'Bancard Ventas QR';
                foreach ($services  as $service) {
                    if ($service->id <> 100) {
                        if ($service->id <> 27 && $service->id <> -1 && $service->WebServiceProvider->name <> 'Toval - Integraciones' && $service->WebServiceProvider->name <> 'Momo - Ken') {
                            $service_item[$service->id] = $service->WebServiceProvider->name . ' - ' . $service->description;
                            $services_data = $service_item;
                        }
                    }
                }
            }

            //$servicesRequestData = [];
            //$checkbox = false;
            //$checkbox2 = false;

            $resultset = array(
                'target'        => 'Efectividad',
                'owners'        => $owners,
                'branches'      => $branches,
                'pos'           => $pos,
                'status'        => $status,
                'type'          => $atmType,
                'services_data' => $services_data,
                'owner_id'      => 0,
                'branch_id'     => 0,
                'pos_id'        => 0,
                'status_set'    => 0,
                'type_set'    => 0,
                'service_id'    => 0,
                'item_2'        => null,
            );
            return $resultset;
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de reportes" . $e);
            return false;
        }
    }

    public function efectividadSearch()
    {
        try {
            $input = $this->input;
            $where = "transactions.transaction_type in (1,7,12,13) AND ";
            \Log::info($input);
            /*SET DATE RANGE*/
            $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
            $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
            $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
            $where .= "transactions.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' AND ";

            $where .= ($input['type'] <> "0") ? " atms.type =  '{$input['type']}' AND " : "";

            if ($input['service_id'] == -2) {
                $where .= 'transactions.service_source_id = 1';
            } elseif ($input['service_id'] == -4) {
                $where .= 'transactions.service_source_id = 4';
            } elseif ($input['service_id'] == -6) {
                $where .= 'transactions.service_source_id = 6';
            } elseif ($input['service_id'] == -7) {
                $where .= 'transactions.service_source_id = 7';
            } elseif ($input['service_id'] == -8) {
                $where .= 'transactions.service_source_id = 8';
            } elseif ($input['service_id'] == -10) {
                $where .= 'transactions.service_source_id = 10';
            } elseif ($this->user->hasAccess('ticketea') && !$this->user->hasAccess('superuser')) {
                $where .= 'transactions.service_id = 28';
            } else {
                $where .= ($input['service_id'] <> 0) ? "transactions.service_id = " . $input['service_id'] . " AND servicios_x_marca.service_source_id = 0" : "";
            }

            /*SET OWNER*/
            if (!$this->user->hasAccess('superuser')) {
                if (($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11)) {
                    $where .= " AND transactions.owner_id = " . $this->user->owner_id . " AND ";
                } else {
                    $where .= ($input['owner_id'] <> 0) ? " AND transactions.owner_id = " . $input['owner_id'] . " AND " : "";
                }
            } else {
                $where .= ($input['owner_id'] <> 0) ? " AND transactions.owner_id = " . $input['owner_id'] . " AND " : "";
            }

            $where = trim($where);
            $where = trim($where, 'AND');
            $where = trim($where);

            //dd($where);

            $transactions = \DB::table('transactions')
                ->selectRaw("
                    servicios_x_marca.descripcion,
                    transactions.status,
                    COALESCE(count(transactions.status),0) as cantidad
                    ")
                ->leftjoin('servicios_x_marca', 'transactions.service_id', '=', 'servicios_x_marca.service_id')
                ->join('marcas', 'marcas.id', '=', 'servicios_x_marca.marca_id')
                ->join('atms', 'atms.id', '=', 'transactions.atm_id')
                ->whereRaw("$where")
                ->orderBy('transactions.status', 'ASC')
                ->groupBy('servicios_x_marca.descripcion', 'transactions.status')
                ->get();

            //dd($transactions);
            $detalles_transactions = \DB::table('transactions')
                ->select(\DB::raw("
                        transactions.status,
                        transactions.amount as monto,
                        (COALESCE(servicios_x_marca.descripcion, 'Otros') || ' - ' ||marcas.descripcion) as servicio,
                        transactions.status_description,
                        transactions.service_id,
                        transactions.service_source_id,
                        transactions.factura_numero,
                        transactions.created_at
                    "))
                ->leftjoin('servicios_x_marca', 'transactions.service_id', '=', 'servicios_x_marca.service_id')
                ->join('marcas', 'marcas.id', '=', 'servicios_x_marca.marca_id')
                ->join('atms', 'atms.id', '=', 'transactions.atm_id')
                ->whereRaw("$where")
                ->orderBy('transactions.status', 'ASC')
                ->orderBy('servicios_x_marca.descripcion', 'ASC')
                ->get();
            //dd( $detalles_transactions);

            //\Log::info('result' , ['test' => $detalles_transactions]);

            $initial_status = null;
            $initial_service = null;
            $status_counter = 0;
            $service_counter = 0;
            $total_service_counter = 0;
            $item_2 = [];
            $sub_data_item = [];
            $aux = [];
            $i = 0;
            $len = count($detalles_transactions);
            //$current_service = 0;
            foreach ($detalles_transactions as $detalle) {
                if ($initial_status == null) {
                    $current_status = $detalle->status;
                    $initial_status = $detalle->status;
                }
                if ($current_status == $detalle->status) {
                    $status_counter++;
                    if ($initial_service == null) {
                        $current_service = $detalle->servicio;
                        $initial_service = $detalle->servicio;
                    }
                    if ($current_service == $detalle->servicio) {
                        $service_counter++;
                    } else {
                        $aux[] = [
                            'name'   => $current_service,
                            'value'  => $service_counter
                        ];
                        $sub_data_item = array_merge($sub_data_item, $aux);
                        $aux = [];
                        $service_counter = 1;
                        $current_service = $detalle->servicio;
                    }
                } else {
                    $aux[] = [
                        'name'   => $current_service,
                        'value'  => $service_counter
                    ];
                    $sub_data_item = array_merge($sub_data_item, $aux);
                    $aux = [];
                    $item_2[] = [
                        'status'    => $current_status,
                        'cantidad'  => $status_counter,
                        'subData'   =>  $sub_data_item
                    ];
                    $sub_data_item = [];
                    $initial_service = null;
                    $status_counter = 1;
                    $current_status = $detalle->status;
                    $service_counter = 1;
                }
                if ($i == $len - 1) {
                    $sub_data_item[] = [
                        'name'   => $current_service,
                        'value'  => $service_counter
                    ];
                    $item_2[] = [
                        'status'    => $current_status,
                        'cantidad'  => $status_counter,
                        'subData'   => $sub_data_item
                    ];
                }
                $i++;
            }

            //dd($item);
            //dd(json_encode($item));

            /*Carga datos del formulario*/
            $whereOwner = "";
            $whereBranch = "";
            $wherePos = "";
            if (!$this->user->hasAccess('superuser')) {
                if ($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11) {
                    $whereOwner = "owners.id = " . $this->user->owner_id;
                    $whereBranch = "branches.owner_id = " . $this->user->owner_id;
                    $wherePos = "points_of_sale.owner_id = " . $this->user->owner_id;
                }
            }

            $owners     = Owner::orderBy('owners.name')->where(function ($query) use ($whereOwner) {
                if (!empty($whereOwner)) {
                    $query->whereRaw($whereOwner);
                }
            })->get()->pluck('name', 'id');
            $owners->prepend('Todos', '0');
            $branches   = Branch::orderBy('description')->where(function ($query) use ($whereBranch) {
                if (!empty($whereBranch)) {
                    $query->whereRaw($whereBranch);
                }
            })->get()->pluck('description', 'id');
            $branches->prepend('Todos', '0');
            $pdvs       = Pos::orderBy('description')->where(function ($query) use ($wherePos) {
                if (!empty($wherePos)) {
                    $query->whereRaw($wherePos);
                }
            })->with('Atm')->get();
            $pos = [];
            $item = array();
            $item[0] = 'Todos';
            foreach ($pdvs  as $pdv) {
                $item[$pdv->id] = $pdv->description . ' - ' . $pdv->Atm->code;
                $pos = $item;
            }

            $status = array('0' => 'Todos', 'success' => 'Aprobado', 'canceled' => 'Cancelado', 'error' => 'Error', 'rollback' => 'Reversado', 'iniciated' => 'Iniciado', 'error dispositivo' => 'Error de dispositivo', 'devolucion' => 'Devolución', 'inconsistency' => 'Inconsistencia');
            $atmType = array('0' => 'Todos', 'da' => 'App Billetaje', 'at' => 'Atm', 'ws' => 'Web Service'); // 12/01/2021
            $services   = ServiceProviderProduct::with('WebServiceProvider')->orderBy('service_provider_id', 'DESC')->get();
            if ($this->user->hasAccess('ticketea') && !$this->user->hasAccess('superuser')) {
                $service_item[28] = 'Ticketea - Venta de tickets';
                $services_data = $service_item;
            } else {
                $service_item[0] = 'Todos';
                $service_item[-2] = 'Netel';
                $service_item[-4] = 'Pronet';
                $service_item[-6] = 'Practipago';
                $service_item[-7] = 'Infonet';
                $service_item[-8] = 'Toval';
                $service_item[-10] = 'Netel Trex';
                $service_item[-11] = 'Bancard Ventas QR';
                foreach ($services  as $service) {
                    if ($service->id <> 100) {
                        if ($service->id <> 27 && $service->id <> -1 && $service->WebServiceProvider->name <> 'Toval - Integraciones' && $service->WebServiceProvider->name <> 'Momo - Ken') {
                            $service_item[$service->id] = $service->WebServiceProvider->name . ' - ' . $service->description;
                            $services_data = $service_item;
                        }
                    }
                }
            }

            $resultset = array(
                'target'        => 'Efectividad',
                'owners'        => $owners,
                'branches'      => $branches,
                'pos'           => $pos,
                'status'        => $status,
                'type'          => $atmType,
                'services_data' => $services_data,
                'transactions'  => (array)$transactions,
                'item_2'        => json_encode($item_2, JSON_NUMERIC_CHECK),
                'owner_id'      => (isset($input['owner_id']) ? $input['owner_id'] : 0),
                'branch_id'     => (isset($input['branch_id']) ? $input['branch_id'] : 0),
                'pos_id'        => (isset($input['pos_id']) ? $input['pos_id'] : 0),
                'status_set'    => (isset($input['status_id']) ? $input['status_id'] : 0),
                'type_set'    => (isset($input['type']) ? $input['type'] : 0),
                'service_id'    => (isset($input['service_id']) ? $input['service_id'] : 0),
                'reservationtime' => (isset($input['reservationtime']) ? $input['reservationtime'] : 0),
            );
            \Log::info('result', ['test2' => $resultset]);
            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return 'error efectividad';
        }
    }

    public function statusDetalle()
    {
        try {
            $input = $this->input;
            switch ($input['status']) {
                case 'success':
                    $estado = "'success'";
                    break;
                case 'canceled':
                    $estado = "'canceled'";
                    break;
                case 'error':
                    $estado = "'error'";
                    break;
                case 'error dispositivo':
                    $estado = "'error dispositivo'";
                    break;
                case 'iniciated':
                    $estado = "'iniciated'";
                    break;
                case 'rollback':
                    $estado = "'rollback'";
                    break;
            }

            $where = "transactions.status  in (" . $estado . ") AND  transactions.transaction_type in (1,7,12,13) AND ";

            /*SET DATE RANGE*/
            $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
            $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
            $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
            $where .= "transactions.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' AND ";

            $where .= ($input['type'] <> "0") ? " atms.type =  '{$input['type']}' AND " : "";


            if ($input['service_id'] == -2) {
                $where .= ' transactions.service_source_id = 1';
            } elseif ($input['service_id'] == -4) {
                $where .= '  transactions.service_source_id = 4';
            } elseif ($input['service_id'] == -6) {
                $where .= '  transactions.service_source_id = 6';
            } elseif ($input['service_id'] == -7) {
                $where .= '  transactions.service_source_id = 7';
            } elseif ($input['service_id'] == -8) {
                $where .= '  transactions.service_source_id = 8';
            } elseif ($input['service_id'] == -10) {
                $where .= '  transactions.service_source_id = 10';
            } elseif ($this->user->hasAccess('ticketea') && !$this->user->hasAccess('superuser')) {
                $where .= '  transactions.service_id = 28';
            } else {
                $where .= ($input['service_id'] <> 0) ? "  transactions.service_id = " . $input['service_id'] . " AND  servicios_x_marca.service_source_id = 0" : "";
            }

            $where = trim($where);
            $where = trim($where, 'AND');
            $where = trim($where);

            // \Log::info('result', ['where' => $where]);

            $transactions = \DB::table('transactions')
                ->select(\DB::raw("
                        transactions.status,
                        (COALESCE(servicios_x_marca.descripcion, 'Otros') || ' - ' ||marcas.descripcion) as servicio,
                        transactions.status_description,
                        COALESCE(count(transactions.status_description),0) cantidad,
                        sum(transactions.amount) as monto
                        "))
                ->leftjoin('servicios_x_marca', 'transactions.service_id', '=', 'servicios_x_marca.service_id')
                ->join('marcas', 'marcas.id', '=', 'servicios_x_marca.marca_id')
                ->join('atms', 'atms.id', '=', 'transactions.atm_id')
                ->whereRaw("$where")
                ->groupBy('transactions.status', 'servicios_x_marca.descripcion', 'marcas.descripcion', 'transactions.status_description')
                ->orderBy('cantidad', 'DESC')
                ->get();
            // \Log::info($transactions);

            $total = 0;

            foreach ($transactions as $key => $transaction) {
                $total = $total + $transaction->cantidad;
            }

            $data = '';

            foreach ($transactions as $key => $transaction) {

                $data .= '<tr data-id="' . $transaction->status . '">';
                $data .= '<td><i class="info fa fa-info-circle"></i> ' . $transaction->status . '</td>';
                $data .= '<td>' . $transaction->servicio . '</td>';
                $data .= '<td>' . $transaction->status_description . '</td>';
                $data .= '<td>' . number_format($transaction->cantidad, 0, ',', '.') . '</td>';
                $data .= '<td>' . number_format($transaction->monto, 0, ',', '.') . '</td>';
                $data .= '<td>' . number_format(((($transaction->cantidad) * 100) / $total), 2) . ' %' . '</td>';
                $data .= '</tr>';
            }
            $response = [
                'modal_contenido_status' => $data,
                'modal_footer' => $total,
                'error' => false
            ];
            //\Log::debug('result',['result' => $response]);
            return $response;
        } catch (\Exception $e) {
            \Log::info($e);

            $response = [
                'error' => true
            ];
            return $response;
        }
    }

    public function efectividadSearchExport()
    {
        try {
            $input = $this->input;
            $where = "transactions.transaction_type in (1,7,12,13) AND ";

            /*SET DATE RANGE*/
            $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
            $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
            $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
            $where .= "transactions.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' AND ";
            $where .= ($input['type'] <> "0") ? "atms.type ='{$input['type']}' AND " : "";

            if ($input['service_id'] == -2) {
                $where .= 'transactions.service_source_id = 1';
            } elseif ($input['service_id'] == -4) {
                $where .= 'transactions.service_source_id = 4';
            } elseif ($input['service_id'] == -6) {
                $where .= 'transactions.service_source_id = 6';
            } elseif ($input['service_id'] == -7) {
                $where .= 'transactions.service_source_id = 7';
            } elseif ($input['service_id'] == -8) {
                $where .= 'transactions.service_source_id = 8';
            } elseif ($input['service_id'] == -10) {
                $where .= 'transactions.service_source_id = 10';
            } elseif ($this->user->hasAccess('ticketea') && !$this->user->hasAccess('superuser')) {
                $where .= 'transactions.service_id = 28';
            } else {
                $where .= ($input['service_id'] <> 0) ? "transactions.service_id = " . $input['service_id'] . " AND servicios_x_marca.service_source_id = 0" : "";
            }

            $where = trim($where);
            $where = trim($where, 'AND');
            $where = trim($where);

            $transactions = \DB::table('transactions')
                ->select(\DB::raw("
                    transactions.status,
                    (COALESCE(servicios_x_marca.descripcion, 'Otros') || ' - ' ||marcas.descripcion) as servicio,
                    transactions.status_description,
                    COALESCE(count(transactions.status_description),0) cantidad,
                    sum(transactions.amount) as monto
                      "))
                ->leftjoin('servicios_x_marca', 'transactions.service_id', '=', 'servicios_x_marca.service_id')
                ->join('marcas', 'marcas.id', '=', 'servicios_x_marca.marca_id')
                ->join('atms', 'atms.id', '=', 'transactions.atm_id')
                ->whereRaw("$where")
                ->groupBy('transactions.status', 'servicios_x_marca.descripcion', 'marcas.descripcion', 'transactions.status_description')
                ->orderBy('cantidad', 'DESC')
                ->get();

            $resultset = array(
                'transactions'  => $transactions
            );

            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    /** DEPOSITOS DE CUOTAS*/
    public function depositosCuotasReports($request)
    {
        try {
            $desde = Carbon::today();
            $hasta = Carbon::tomorrow()->modify('-1 seconds');

            if (!\Sentinel::getUser()->inRole('mini_terminal')) {

                $boletas = \DB::table('mt_recibos_pagos_miniterminales')
                    ->select([
                        'mt_recibos_pagos_miniterminales.id',
                        'fecha',
                        'bancos.descripcion as banco',
                        'cuentas_bancarias.numero_banco as cuenta_bancaria',
                        'boleta_numero',
                        'mt_recibos_pagos_miniterminales.monto',
                        'user_id',
                        'tipo_pago.descripcion as tipo_pago',
                        'estado',
                        'mt_recibos_pagos_miniterminales.deleted_at',
                        'mt_recibos_pagos_miniterminales.updated_at',
                        'mt_recibos_pagos_miniterminales.updated_by',
                        'users.username as username',
                        'mt_recibos_pagos_miniterminales.message',
                        'reprinted'
                    ])
                    ->join('cuentas_bancarias', 'cuentas_bancarias.id', '=', 'mt_recibos_pagos_miniterminales.cuenta_bancaria_id')
                    ->join('bancos', 'bancos.id', '=', 'cuentas_bancarias.banco_id')
                    ->join('tipo_pago', 'tipo_pago.id', '=', 'mt_recibos_pagos_miniterminales.tipo_pago_id')
                    ->leftjoin('users', 'users.id', '=', 'mt_recibos_pagos_miniterminales.updated_by')
                    ->leftjoin('mt_recibos', 'mt_recibos.id', '=', 'mt_recibos_pagos_miniterminales.recibo_id')
                    ->whereRaw("fecha BETWEEN '{$desde}' AND '{$hasta}'")
                    ->where('estado', true)
                    ->where('mt_recibos_pagos_miniterminales.tipo_recibo_id', 1)
                    ->orderBy('mt_recibos_pagos_miniterminales.id', 'desc')
                    ->get();
            } else {
                $boletas = \DB::table('mt_recibos_pagos_miniterminales')
                    ->select([
                        'mt_recibos_pagos_miniterminales.id',
                        'fecha',
                        'bancos.descripcion as banco',
                        'cuentas_bancarias.numero_banco as cuenta_bancaria',
                        'boleta_numero',
                        'mt_recibos_pagos_miniterminales.monto',
                        'user_id',
                        'tipo_pago.descripcion as tipo_pago',
                        'estado',
                        'mt_recibos_pagos_miniterminales.deleted_at',
                        'mt_recibos_pagos_miniterminales.updated_at',
                        'mt_recibos_pagos_miniterminales.updated_by',
                        'users.username as username',
                        'mt_recibos_pagos_miniterminales.message',
                        'reprinted'
                    ])
                    ->join('cuentas_bancarias', 'cuentas_bancarias.id', '=', 'mt_recibos_pagos_miniterminales.cuenta_bancaria_id')
                    ->join('bancos', 'bancos.id', '=', 'cuentas_bancarias.banco_id')
                    ->join('tipo_pago', 'tipo_pago.id', '=', 'mt_recibos_pagos_miniterminales.tipo_pago_id')
                    ->leftjoin('users', 'users.id', '=', 'mt_recibos_pagos_miniterminales.updated_by')
                    ->leftjoin('mt_recibos', 'mt_recibos.id', '=', 'mt_recibos_pagos_miniterminales.recibo_id')
                    ->where('user_id', $this->user->id)
                    ->whereRaw("fecha BETWEEN '{$desde}' AND '{$hasta}'")
                    ->where('estado', true)
                    ->where('mt_recibos_pagos_miniterminales.tipo_recibo_id', 1)
                    ->orderBy('mt_recibos_pagos_miniterminales.id', 'desc')
                    ->get();
            }

            $usersNames = \DB::connection('eglobalt_auth')
                ->table('users')
                ->selectRaw('concat(username, \' - \', description) as full_name, id')
                ->join('role_users', 'users.id', '=', 'role_users.user_id')
                ->where('role_users.role_id', 22)
                ->pluck('full_name', 'id');

            $usersId = \DB::connection('eglobalt_auth')
                ->table('users')
                ->join('role_users', 'users.id', '=', 'role_users.user_id')
                ->where('role_users.role_id', 22)
                ->pluck('id', 'id');

            $branches = \DB::table('branches')
                ->select('branches.*')
                ->whereIn('branches.user_id', $usersId)
                ->get();

            $data_select = [];
            foreach ($branches as $key => $branch) {
                $data_select[$branch->user_id] = $branch->description . ' | ' . $usersNames[$branch->user_id];
            }
            $results = $this->arrayPaginator($boletas->toArray(), $request);

            $resultset = array(
                'target' => 'Depositos Cuotas Miniterminales',
                'transactions' => $results,
                'reservationtime' => (isset($input['reservationtime']) ? $input['reservationtime'] : 0)
            );
            $status = array('0' => 'Todos', '1' => 'Confirmados', '2' => 'Rechazados', '3' => 'Pendientes');

            $resultset['usersNames']    = $usersNames;
            $resultset['branches']      = $branches;
            $resultset['data_select']   = $data_select;
            $resultset['status']        = $status;
            $resultset['status_set']    = 0;
            $resultset['user_id']       = '';

            return $resultset;
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de reportes" . $e);
            return false;
        }
    }

    public function depositosCuotasSearch($request)
    {
        try {
            $input = $this->input;

            /*Busqueda minusiosa*/
            /*SET DATE RANGE*/

            if (isset($input['reservationtime'])) {
                $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                $where = "fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
            }

            if ($input['status_id'] != 0) {
                if ($input['status_id'] == '1') {
                    $where .= " AND estado is true";
                }
                if ($input['status_id'] == '2') {
                    $where .= " AND estado is false";
                }
                if ($input['status_id'] == '3') {
                    $where .= " AND estado is null";
                }
            }
            if (!\Sentinel::getUser()->inRole('mini_terminal')) {
                $user_id = $input['user_id'];
                $boletas = \DB::table('mt_recibos_pagos_miniterminales')
                    ->select([
                        'mt_recibos_pagos_miniterminales.id',
                        'fecha',
                        'bancos.descripcion as banco',
                        'cuentas_bancarias.numero_banco as cuenta_bancaria',
                        'boleta_numero',
                        'mt_recibos_pagos_miniterminales.monto',
                        'user_id',
                        'tipo_pago.descripcion as tipo_pago',
                        'estado',
                        'mt_recibos_pagos_miniterminales.deleted_at',
                        'mt_recibos_pagos_miniterminales.updated_at',
                        'mt_recibos_pagos_miniterminales.updated_by',
                        'users.username as username',
                        'message',
                        'reprinted'
                    ])
                    ->join('cuentas_bancarias', 'cuentas_bancarias.id', '=', 'mt_recibos_pagos_miniterminales.cuenta_bancaria_id')
                    ->join('bancos', 'bancos.id', '=', 'cuentas_bancarias.banco_id')
                    ->join('tipo_pago', 'tipo_pago.id', '=', 'mt_recibos_pagos_miniterminales.tipo_pago_id')
                    ->leftjoin('users', 'users.id', '=', 'mt_recibos_pagos_miniterminales.updated_by')
                    ->leftjoin('mt_recibos', 'mt_recibos.id', '=', 'mt_recibos_pagos_miniterminales.recibo_id')
                    ->whereRaw("$where")
                    ->where('mt_recibos_pagos_miniterminales.tipo_recibo_id', 1)
                    ->where(function ($query) use ($user_id) {
                        if (!empty($user_id)) {
                            $query->where('mt_recibos_pagos_miniterminales.user_id', $user_id);
                        }
                    })
                    ->orderBy('mt_recibos_pagos_miniterminales.id', 'desc')
                    ->get();
            } else {
                $user_id = $this->user->id;
                $boletas = \DB::table('mt_recibos_pagos_miniterminales')
                    ->select([
                        'mt_recibos_pagos_miniterminales.id',
                        'fecha',
                        'bancos.descripcion as banco',
                        'cuentas_bancarias.numero_banco as cuenta_bancaria',
                        'boleta_numero',
                        'mt_recibos_pagos_miniterminales.monto',
                        'user_id',
                        'tipo_pago.descripcion as tipo_pago',
                        'estado',
                        'mt_recibos_pagos_miniterminales.deleted_at',
                        'mt_recibos_pagos_miniterminales.updated_at',
                        'mt_recibos_pagos_miniterminales.updated_by',
                        'users.username as username',
                        'message',
                        'reprinted'
                    ])
                    ->join('cuentas_bancarias', 'cuentas_bancarias.id', '=', 'mt_recibos_pagos_miniterminales.cuenta_bancaria_id')
                    ->join('bancos', 'bancos.id', '=', 'cuentas_bancarias.banco_id')
                    ->join('tipo_pago', 'tipo_pago.id', '=', 'mt_recibos_pagos_miniterminales.tipo_pago_id')
                    ->leftjoin('users', 'users.id', '=', 'mt_recibos_pagos_miniterminales.updated_by')
                    ->leftjoin('mt_recibos', 'mt_recibos.id', '=', 'mt_recibos_pagos_miniterminales.recibo_id')
                    ->whereRaw("$where")
                    ->where('mt_recibos_pagos_miniterminales.tipo_recibo_id', 1)
                    ->where('mt_recibos_pagos_miniterminales.user_id', $user_id)
                    ->orderBy('mt_recibos_pagos_miniterminales.id', 'desc')
                    ->get();
            }

            $results = $this->arrayPaginator($boletas->toArray(), $request);

            $resultset = array(
                'target'        => 'Depositos Cuotas Miniterminales',
                'transactions'  => $results,
                'reservationtime' => (isset($input['reservationtime']) ? $input['reservationtime'] : 0),
                'i'             =>  1,
            );

            $usersNames = \DB::connection('eglobalt_auth')
                ->table('users')
                ->selectRaw('concat(username, \' - \', description) as full_name, id')
                ->join('role_users', 'users.id', '=', 'role_users.user_id')
                ->where('role_users.role_id', 22)
                ->pluck('full_name', 'id');

            $usersId = \DB::connection('eglobalt_auth')
                ->table('users')
                ->join('role_users', 'users.id', '=', 'role_users.user_id')
                ->where('role_users.role_id', 22)
                ->pluck('id', 'id');

            $branches = \DB::table('branches')
                ->select('branches.*')
                ->whereIn('branches.user_id', $usersId)
                ->get();

            $status = array('0' => 'Todos', '1' => 'Confirmados', '2' => 'Rechazados', '3' => 'Pendientes');

            $data_select = [];
            foreach ($branches as $key => $branch) {
                $data_select[$branch->user_id] = $branch->description . ' | ' . $usersNames[$branch->user_id];
            }

            $resultset['usersNames'] = $usersNames;
            $resultset['branches'] = $branches;
            $resultset['status'] = $status;
            $resultset['status_set'] = (isset($input['status_id']) ? $input['status_id'] : 0);
            $resultset['data_select'] = $data_select;
            $resultset['user_id'] = $user_id;
            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    public function depositosCuotasSearchExport()
    {
        try {
            $input = $this->input;
            /*Busqueda minusiosa*/
            /*SET DATE RANGE*/

            if (isset($input['reservationtime'])) {
                $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                $where = "fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
            }

            if ($input['status_id'] != 0) {
                if ($input['status_id'] == '1') {
                    $where .= " AND estado is true";
                }
                if ($input['status_id'] == '2') {
                    $where .= " AND estado is false";
                }
                if ($input['status_id'] == '3') {
                    $where .= " AND estado is null";
                }
            }

            if (!\Sentinel::getUser()->inRole('mini_terminal')) {
                $user_id = $input['user_id'];
                $boletas = \DB::table('mt_recibos')
                    ->select([
                        'mt_recibos.id',
                        'mt_recibos_pagos_miniterminales.fecha',
                        'users.username',
                        'tipo_pago.descripcion as tipo_pago',
                        'bancos.descripcion as banco',
                        'cuentas_bancarias.numero_banco as cuenta_bancaria',
                        'boleta_numero',
                        'mt_recibos.monto',
                        'estado',
                        'mt_recibos_pagos_miniterminales.updated_by',
                        'mt_recibos.updated_at',
                        'mt_recibos_pagos_miniterminales.message',
                        'mt_recibos.deleted_at',
                        'mt_recibos.reprinted',
                        'user_id'
                    ])
                    ->join('mt_recibos_pagos_miniterminales', 'mt_recibos.id', '=', 'mt_recibos_pagos_miniterminales.recibo_id')
                    ->join('cuentas_bancarias', 'mt_recibos_pagos_miniterminales.cuenta_bancaria_id', '=', 'cuentas_bancarias.banco_id')
                    ->join('bancos', 'bancos.id', '=', 'cuentas_bancarias.banco_id')
                    ->join('tipo_pago', 'tipo_pago.id', '=', 'mt_recibos_pagos_miniterminales.tipo_pago_id')
                    ->leftjoin('users', 'users.id', '=', 'mt_recibos_pagos_miniterminales.updated_by')
                    ->whereRaw("$where")
                    ->where('mt_recibos_pagos_miniterminales.tipo_recibo_id', 1)
                    ->where(function ($query) use ($user_id) {
                        if (!empty($user_id)) {
                            $query->where('mt_recibos_pagos_miniterminales.user_id', $user_id);
                        }
                    })
                    ->orderBy('mt_recibos.id', 'desc')
                    ->get();
            } else {
                $user_id = $this->user->id;
                $boletas = \DB::table('mt_recibos')
                    ->select([
                        'mt_recibos.id',
                        'mt_recibos_pagos_miniterminales.fecha',
                        'users.username',
                        'tipo_pago.descripcion as tipo_pago',
                        'bancos.descripcion as banco',
                        'cuentas_bancarias.numero_banco as cuenta_bancaria',
                        'boleta_numero',
                        'mt_recibos.monto',
                        'estado',
                        'mt_recibos_pagos_miniterminales.updated_by',
                        'mt_recibos.updated_at',
                        'mt_recibos_pagos_miniterminales.message',
                        'mt_recibos.deleted_at',
                        'mt_recibos.reprinted',
                        'user_id'
                    ])
                    ->join('mt_recibos_pagos_miniterminales', 'mt_recibos.id', '=', 'mt_recibos_pagos_miniterminales.recibo_id')
                    ->join('cuentas_bancarias', 'mt_recibos_pagos_miniterminales.cuenta_bancaria_id', '=', 'cuentas_bancarias.banco_id')
                    ->join('bancos', 'bancos.id', '=', 'cuentas_bancarias.banco_id')
                    ->join('tipo_pago', 'tipo_pago.id', '=', 'mt_recibos_pagos_miniterminales.tipo_pago_id')
                    ->leftjoin('users', 'users.id', '=', 'mt_recibos_pagos_miniterminales.updated_by')
                    ->whereRaw("$where")
                    ->where('user_id', $user_id)
                    ->where('mt_recibos_pagos_miniterminales.tipo_recibo_id', 1)
                    ->orderBy('mt_recibos.id', 'desc')
                    ->get();
            }

            foreach ($boletas as $boleta) {
                $boleta->fecha = date('d/m/Y', strtotime($boleta->fecha));
                if ($boleta->estado == true) {
                    $boleta->estado = 'Confirmado';
                } else if ($boleta->estado == false) {
                    $boleta->estado = 'Rechazado';
                } else {
                    $boleta->estado = 'Pendiente';
                }
                if (isset($boleta->username)) {
                    $user = \DB::table('users')->where('id', $boleta->user_id)->first();
                    $boleta->username = $user->username;
                }
                $boleta->updated_at = date('d/m/Y H:i:s', strtotime($boleta->updated_at));
            }

            $resultset = array(
                'transactions'  => $boletas
            );
            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    public function historialBloqueosReports($request)
    {
        try {

            $groups = Group::pluck('description', 'id');
            $groups->prepend('Todos', '0')->toArray();

            $atms = Atm::whereIn('owner_id', [16, 21, 25])->pluck('name', 'id');
            $atms->prepend('Todos', '0');

            $resultset = array(
                'target'    => 'Historial Bloqueos',
                'groups'    => $groups,
                'atms'      => $atms,
                'group_id'  => 0,
                'atm_id'    => 0,
            );

            return $resultset;
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de reportes" . $e);
            return false;
        }
    }

    public function historialBloqueosSearch($request)
    {
        try {
            $input = $this->input;

            /*Busqueda minusiosa*/
            if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
            } else {
                /*SET DATE RANGE*/
                if (isset($input['reservationtime'])) {
                    if ($input['reservationtime'] == '30_dias') {
                        $hasta = Carbon::parse(date('Y-m-d 23:59:59'));
                        $desde = Carbon::parse(date('Y-m-d 00:00:00'))->modify('-30 days');
                        $where = "historial_bloqueos.created_at BETWEEN '{$desde}' AND '{$hasta}'";
                        $input['reservationtime'] = $desde . '/' . $hasta;
                    } else {
                        $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                        $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                        $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                        $where = "historial_bloqueos.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    }
                }
            }

            $group_id = $input['group_id'];

            if (!empty($group_id) || $group_id != 0) {

                if (!empty($input['atm_id']) || $input['atm_id'] != 0) {
                    $atm_id = '(' . $input['atm_id'] . ')';
                } else {
                    $atms = \DB::table('atms')
                        ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                        ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                        ->whereIn('atms.owner_id', [16, 21, 25])
                        ->where('branches.group_id', $group_id)
                        ->pluck('atms.id', 'atms.id');

                    $atms_id = implode(',', $atms);

                    $atm_id = '(' . $atms_id . ')';
                }
            } else {
                if (!empty($input['atm_id']) || $input['atm_id'] != 0) {
                    $atm_id = '(' . $input['atm_id'] . ')';
                } else {
                    $atm_id = null;
                }
            }
            // $where = trim($where);
            // $where = trim($where, 'AND');
            // $where = trim($where);
            $bloqueos = \DB::table('historial_bloqueos')
                ->select([
                    'historial_bloqueos.id',
                    'atms.name as nombre',
                    'historial_bloqueos.created_at as fecha',
                    'historial_bloqueos.saldo_pendiente',
                    'historial_bloqueos.bloqueado',
                    'block_type.description'
                ])
                ->join('atms', 'atms.id', '=', 'historial_bloqueos.atm_id')
                ->join('block_type', 'block_type.id', '=', 'historial_bloqueos.block_type_id')
                ->whereRaw("$where")
                ->where(function ($query) use ($atm_id) {
                    if (!empty($atm_id)) {
                        $query->whereRaw('atms.id in ' . $atm_id);
                    }
                })
                ->orderBy('historial_bloqueos.id', 'desc')
                ->get();

            $results = $this->arrayPaginator($bloqueos->toArray(), $request);

            $resultset = array(
                'target'        => 'Historial Bloqueos',
                'transactions'  => $results,
                'reservationtime' => (isset($input['reservationtime']) ? $input['reservationtime'] : 0),
                'i'             =>  1,
            );

            //$status = array('0'=>'Todos','1'=>'Confirmados','2'=>'Rechazados','3'=>'Pendientes');

            foreach ($bloqueos as $bloqueo) {
                if ($bloqueo->bloqueado) {
                    $bloqueo->bloqueado = 'Bloqueado';
                } else {
                    $bloqueo->bloqueado = 'Desbloqueado';
                }
            }

            $groups = Group::pluck('description', 'id');
            $groups->prepend('Todos', '0');

            $atms = Atm::whereIn('owner_id', [16, 21, 25])->pluck('name', 'id');
            $atms->prepend('Todos', '0');

            $resultset['atms'] = $atms;
            //$resultset['status'] = $status;
            //$resultset['status_set'] = (isset($input['status_id'])?$input['status_id']:0);
            $resultset['atm_id'] = $input['atm_id'];
            $resultset['groups'] = $groups;
            $resultset['group_id'] = $input['group_id'];

            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }


    public function conciliations_detailsReports()
    {
        try {
            //Redes
            $atms     = Atm::orderBy('name')->get()->pluck('name', 'id')->prepend('Todos', '0')->toArray();
            $owners     = Owner::orderBy('name')->get()->pluck('name', 'id')->prepend('Todos', '0')->toArray();
            $branches   = Branch::orderBy('description')->get()->pluck('description', 'id')->prepend('Todos', '0')->toArray();
            $types = array('0' => 'Todos', '1' => 'Estados Atms', '2' => 'Servicios', '4' => 'Saldos');
            $pdvs  = Pos::orderBy('description')->with('Atm')->get();
            $pos = [];
            $item = array();
            $item[0] = 'Todos';
            foreach ($pdvs  as $pdv) {
                $item[$pdv->id] = $pdv->description . ' - ' . $pdv->Atm->code;
                $pos = $item;
            }

            $services   = ServiceProviderProduct::with('WebServiceProvider')->orderBy('service_provider_id', 'DESC')->get();
            $services_data = [];
            $service_item = array();

            if ($this->user->hasAccess('ticketea') && !$this->user->hasAccess('superuser')) {
                $service_item[28] = 'Ticketea - Venta de tickets';
                $services_data = $service_item;
            } else {
                $service_item[0] = 'Todos';
                $service_item[-2] = 'Netel';
                $service_item[-4] = 'Pronet';
                $service_item[-6] = 'Practipago';
                $service_item[-7] = 'Infonet';
                $service_item[-8] = 'Toval';
                $service_item[-10] = 'Netel Trex';
                $service_item[-11] = 'Bancard Ventas QR';
                foreach ($services  as $service) {
                    if ($service->id <> 100) {
                        if ($service->id <> 27 && $service->id <> -1 && $service->WebServiceProvider->name <> 'Toval - Integraciones' && $service->WebServiceProvider->name <> 'Momo - Ken') {
                            $service_item[$service->id] = $service->WebServiceProvider->name . ' - ' . $service->description;
                            $services_data = $service_item;
                        }
                    }
                }
            }

            $resultset = $this->conciliations_detailsSearch();
        
            return $resultset;
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de reportes" . $e->getMessage());
            return false;
        }
    }


    public function conciliations_detailsSearch()
    {
        try {
            $input = $this->input;
         
            if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
                $where = "";
                $where .= "incomes.id = {$input['context']} ";
            } else {
                if (!$this->input) {
                    $reservation_time = Carbon::today() . ' - ' . Carbon::today()->endOfDay();
                    $status_id = -1;
                    $atm_id = 0;
                    $service_id = 0; ///ver
                    $service_request_id = 0;
                } else {
                    $input = $this->input;
                    $reservation_time = $input['reservationtime'];
                    $status_id = $input['status_id'];
                    $atm_id = $input['atm_id'];
                    $service_id = $input['service_id'];
                    $service_request_id = $input['service_request_id'];
                }

                $where = "";
                $fechaInit = '';
                $fechaEnd = '';
                /*SET DATE RANGE*/
                $daterange = explode(' - ',  str_replace('/', '-', $reservation_time));
                $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                $fechaInit = date('Y-m-d H:i:s', strtotime($daterange[0]));
                $fechaEnd = date('Y-m-d H:i:s', strtotime($daterange[1]));
                $where .= "incomes.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' AND ";
                $where .= ($atm_id <> 0) ? "transactions.atm_id = " . $atm_id . " AND " : "";
                $where .= ($status_id <> -1) ? "incomes.destination_operation_id in " . $status_id . " AND " : "";

                // if($service_id==-2){
                //     $where .= 'transactions.service_source_id = 1';
                //     $where .= ' AND transactions.service_id = '.$service_request_id;
                // }elseif($service_id==-4){
                //     $where .= 'transactions.service_source_id = 4';
                //     $where .= ' AND transactions.service_id = '.$service_request_id;
                // }elseif($service_id==-6){
                //     $where .= 'transactions.service_source_id = 6';
                //     $where .= ' AND transactions.service_id = '.$service_request_id;
                // }elseif($service_id==-7){
                //     $where .= 'transactions.service_source_id = 7';
                //     $where .= ' AND transactions.service_id = '.$service_request_id;
                // }elseif($this->user->hasAccess('ticketea') && !$this->user->hasAccess('superuser')){
                //     $where .= 'transactions.service_id = 28';
                // }else{
                //     $where .= ($service_id<>0) ? "transactions.service_id = ". $service_id . " AND service_source_id = 0" : "";
                // }
                if ($service_id == -2) {
                    $where .= 'transactions.service_source_id = 1';
                    if ($service_request_id <> '0') {
                        $where .= ' AND transactions.service_id = ' . $service_request_id;
                    }
                } elseif ($service_id == -4) {
                    $where .= 'transactions.service_source_id = 4';
                    if ($service_request_id <> '0') {
                        $where .= ' AND transactions.service_id = ' . $service_request_id;
                    }
                } elseif ($service_id == -6) {
                    $where .= 'transactions.service_source_id = 6';
                    if ($service_request_id <> '0') {
                        $where .= ' AND transactions.service_id = ' . $service_request_id;
                    }
                } elseif ($service_id == -7) {
                    $where .= 'transactions.service_source_id = 7';
                    if ($service_request_id <> '0') {
                        $where .= ' AND transactions.service_id = ' . $service_request_id;
                    }
                } elseif ($service_id == 66) {
                    $where .= 'transactions.service_source_id = 8 and transactions.service_id= 14';
                    if ($service_request_id <> '0') {
                        $where .= ' AND transactions.service_id = ' . $service_request_id;
                    }
                } elseif ($service_id == 59) {
                    $where .= 'transactions.service_source_id = 8 and transactions.service_id= 3';
                    if ($service_request_id <> '0') {
                        $where .= ' AND transactions.service_id = ' . $service_request_id;
                    }
                } elseif ($service_id == 57) {
                    $where .= 'transactions.service_source_id = 8 and transactions.service_id= 4';
                    if ($service_request_id <> '0') {
                        $where .= ' AND transactions.service_id = ' . $service_request_id;
                    }
                } elseif ($service_id == 58) {
                    $where .= 'transactions.service_source_id = 8 and transactions.service_id= 5';
                    if ($service_request_id <> '0') {
                        $where .= ' AND transactions.service_id = ' . $service_request_id;
                    }
                } elseif ($service_id == 67) {
                    $where .= 'transactions.service_source_id = 8 and transactions.service_id= 15';
                    if ($service_request_id <> '0') {
                        $where .= ' AND transactions.service_id = ' . $service_request_id;
                    }
                } elseif ($this->user->hasAccess('ticketea') && !$this->user->hasAccess('superuser')) {
                    $where .= 'transactions.service_id = 28';
                } 
                else {
                    $where .= ($service_id <> 0) ? "transactions.service_id = " . $service_id . " AND service_source_id = 0" : "";
                }
            }
            $where = trim($where);
            $where = trim($where, 'AND');
            $where = trim($where);

   
           
            $incomes = \DB::table('incomes')
                ->select('incomes.*', 'atms.name', 'transactions.amount as amount', 'transactions.service_id', 'transactions.service_source_id', 'atms.code as atm_code', 'service_provider_products.description as service_description')
                //->whereBetween('incomes.created_at',[$begin,$end])
                ->whereIn('destination_operation_id', ['0', '1', '5', '212'])
                ->join('transactions', 'transactions.id', '=', 'transaction_id')
                ->join('atms', 'atms.id', '=', 'transactions.atm_id')
                ->leftjoin('service_provider_products', 'service_provider_products.id', '=', 'transactions.service_id')
                ->whereRaw("$where")
                ->orderBy('atms.name', 'asc')
                ->orderBy('incomes.created_at', 'asc')
                //->toSql();
                ->paginate(200);

   


            $incomes_error = \DB::table('incomes')
                ->select(\DB::raw("array_to_string(array_agg(incomes.id), ',') as ids"))
                ->where('destination_operation_id', '1')
                ->join('transactions', 'transactions.id', '=', 'transaction_id')
                ->join('atms', 'atms.id', '=', 'transactions.atm_id')
                ->leftjoin('service_provider_products', 'service_provider_products.id', '=', 'transactions.service_id')
                ->whereRaw("$where")
                ->get();

            $incomes_error = $incomes_error[0]->ids;

            foreach ($incomes as $income) {
                if ($income->service_source_id <> 0) {
                    // transaccion de NETEL
                    $service_sources_data = \DB::table('services_ondanet_pairing')
                        ->where('service_request_id', $income->service_id)
                        ->where('service_source_id', $income->service_source_id)
                        ->first();
                    $service_source_description = \DB::table('services_providers_sources')->where('id', $income->service_source_id)->first();
                    $service_description = '';
                    if ($service_sources_data) {
                        $service_description  .= $service_source_description->description . ' | ' . $service_sources_data->service_description;
                    }
                    $income->service_description = $service_description;
                }
                // if($income->response <> null){
                //     $message = json_decode($income->response);
                //     $income->response =  $message->description;
                // }
            }

            $invoices = \DB::table('invoices')
                ->select('invoices.*', 'transactions.amount as amount', 'atms.code as atm_code', 'service_provider_products.description as service_description')
                ->whereIn('status_code', ['0', '1', '5', '212'])
                ->join('transactions', 'transactions.id', '=', 'invoices.transaction_id')
                ->join('atms', 'atms.id', '=', 'transactions.atm_id')
                ->leftjoin('service_provider_products', 'service_provider_products.id', '=', 'transactions.service_id')
                ->get();

            foreach ($invoices as $invoice) {
                if ($invoice->response <> null) {
                    $message = json_decode($invoice->response);
                    $invoice->response = $message->message;
                }
            }
            /*Carga datos del formulario*/
            $atms     = Atm::orderBy('name')->get()->pluck('name', 'id')->prepend('Todos', '0')->toArray();
            $status = array(
                "-1"  => 'Todos',
                " ('0')"        => 'Pendiente',
                " ('1')"        => 'Error',
                " ('5','212')"  => 'Exitoso'
            );

            $services   = ServiceProviderProduct::with('WebServiceProvider')->orderBy('service_provider_id', 'DESC')->get();
            if ($this->user->hasAccess('ticketea') && !$this->user->hasAccess('superuser')) {
                $service_item[28] = 'Ticketea - Venta de tickets';
                $services_data = $service_item;
            } else {
                $service_item[0] = 'Todos';
                $service_item[-2] = 'Netel';
                $service_item[-4] = 'Pronet';
                $service_item[-6] = 'Practipago';
                $service_item[-7] = 'Infonet';
                $service_item[-8] = 'Toval';
                $service_item[-10] = 'Netel Trex';
                $service_item[-11] = 'Bancard Ventas QR';
                foreach ($services  as $service) {
                    if ($service->id <> 100) {
                        if ($service->id <> 27 && $service->id <> -1 && $service->WebServiceProvider->name <> 'Toval - Integraciones' && $service->WebServiceProvider->name <> 'Momo - Ken') {
                            $service_item[$service->id] = $service->WebServiceProvider->name . ' - ' . $service->description;
                            $services_data = $service_item;
                        }
                    }
                }
            }

            $generics =  \DB::table('invoices_generic as ig')
                ->select('ig.*', 't.amount')
                ->join('transactions as t', 't.id', '=', 'ig.transaction_id')
                ->whereIn('ig.status_code', ['-28', '212', '1'])
                ->whereBetween('ig.created_at', [$fechaInit, $fechaEnd])
                ->get();



            $resultset = array(
                'target'        => 'Conciliations Details',
                'atms'          => $atms,
                'status'        => $status,
                'service_id'    => $service_id,
                'services_data' => $services_data,
                'incomes'       => $incomes,
                'incomes_error' => $incomes_error,
                'invoices'       => $invoices,
                'atm_id'        => (isset($input['atm_id']) ? $input['atm_id'] : 0),
                'status_id'     => (isset($input['status_id']) ? $input['status_id'] : -1),
                'service_id'    => (isset($input['service_id']) ? $input['service_id'] : 0),
                'reservationtime' => (isset($input['reservationtime']) ? $input['reservationtime'] : $reservation_time),
                'i'               =>  1,
                'service_request_id' => (isset($input['service_request_id']) ? $input['service_request_id'] : 0),
                'generics' => $generics
            );

            
            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    public function transaction_not_rollback()
    {


        $rollback = \DB::select(
            "
            SELECT t.id,t.amount,
            t.status,
            t.request_data,
            t.status_description,
            a.name
            from transactions t
                left join atms a on t.atm_id = a.id
                left join points_of_sale pos on t.atm_id = pos.atm_id
                left join tdp_billetaje_sync_transactions tbst on t.id = tbst.transaction_id
                left join rollback_credito_pdv_sync rcps on t.id = rcps.backend_transaction_id
                    where (t.status  like '%canceled%' or t.status like '%error%' )
                        and (t.status_description like '%Error en el proceso de d?bito.%' or t.status_description like '%Cancelado por error en carga; codigo de error:%'or t.status_description like '%Inicializando transacción%')
                        and tbst.id is NULL and rcps.id is null
                        and pos.owner_id = 18
                        and date(t.created_at) BETWEEN date(now() - interval '120 hour') and date(now())
                        "
        );

        $datos = collect($rollback);

        $idsTransaction = $datos->pluck('id');

        $rollbacksFail = \DB::select(
            "
                select id,backend_transaction_id ,amount ,message  from rollback_credito_pdv_sync
                where  message not similar to 'Operacion realizada exitosamente' and message not similar to 'No se ha encontrado la transaccion a reversar!' and message not similar to 'No es posible realizar la reversion de la transaccion!' and message not similar to 'Error al procesar la solicitud: Operacion ya reversada' and status = 1 
                            and date(created_at) BETWEEN date(now() - interval '120 hour') and date(now());
                            "
        );

        $datos1 = collect($rollbacksFail);

        $idsRollback = $datos1->pluck('id');


        $resultset = array(
            'target'   => 'Transacciones sin reversa',
            'transactions' => $rollback,
            'fails' => $rollbacksFail,
            'idsTransaction' => json_encode($idsTransaction),
            'idsRollbacks' => json_encode($idsRollback)
        );
        \Log::info($resultset);
        return $resultset;
    }

    //rollback

    public function transaction_not_rollbackSearch()
    {

        try {
            $input = $this->input;

            $fechaInit = '';
            $fechaEnd = '';
            $fechaHtml = '';

            if (isset($input['reservationtime'])) {
                $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                $fechaInit = date('Y-m-d H:i:s', strtotime($daterange[0]));
                $fechaEnd = date('Y-m-d H:i:s', strtotime($daterange[1]));
                $fechaHtml = $input['reservationtime'];
            } else {
                $date = Carbon::now();
                $date = $date->format('Y-m-d');
                $fechaInit = $date . ' 00:00:00';
                $fechaEnd = $date . ' 23:59:59';
                $fechainitCambio = str_replace('-', '/', $fechaInit);
                $fechaendCambio =  str_replace('-', '/', $fechaEnd);
                $fechaHtml = $fechainitCambio . '-' . $fechaendCambio;
            }

            $rollback = \DB::select(
                "
                SELECT t.id,t.amount,
                t.status,
                t.request_data,
                t.status_description,
                a.name
                from transactions t
                    left join atms a on t.atm_id = a.id
                    left join points_of_sale pos on t.atm_id = pos.atm_id
                    left join tdp_billetaje_sync_transactions tbst on t.id = tbst.transaction_id
                    left join rollback_credito_pdv_sync rcps on t.id = rcps.backend_transaction_id
                        where (t.status  like '%canceled%' or t.status like '%error%' )
                            and (t.status_description like '%Error en el proceso de d?bito.%' or t.status_description like '%Cancelado por error en carga; codigo de error:%'or t.status_description like '%Inicializando transacción%')
                            and tbst.id is NULL and rcps.id is null
                            and pos.owner_id = 18
                            and date(t.created_at) BETWEEN date(now()) and date(now())
                            "
            );

            $datos = collect($rollback);

            $idsTransaction = $datos->pluck('idtransaction');


            $resultset = array(
                'target'   => 'Transacciones sin reversa',
                'transactions' => $rollback,
                'reservationtime' => $fechaHtml,
                'idsTransaction' => json_encode($idsTransaction)
            );
            \Log::info($resultset);
            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    public function movements_affecting_extracts()
    {

        try {
            $fechaEnd = date('Y-m-d H:i:s');

           

            $fecha = date('Y-m-d', strtotime($fechaEnd . "- 30 days"));
            $fechaInit = $fecha . ' 00:00:00';

            $movements = \DB::table('mt_movements')
                ->select('mt_movements.id', 'atms.name as atm', 'movement_type.description', 'mt_movements.destination_operation_id', 'mt_movements.amount')
                ->join('movement_type', 'mt_movements.movement_type_id', '=', 'movement_type.id')
                ->join('atms', 'atms.id', '=', 'mt_movements.atm_id')
                ->whereBetween('mt_movements.created_at', [$fechaInit, $fechaEnd])
                ->whereIn('movement_type.id', [1, 6, 13, 14])
                ->whereIn('mt_movements.destination_operation_id', [1, 0])
                ->whereNull('mt_movements.deleted_at')
                ->orderBy('mt_movements.created_at', 'DESC')
                ->get();

            // $movements = \DB::table('movements as m')
            //     ->select('m.id', 'mt.description', 'm.destination_operation_id', 'm.amount')
            //     ->join('movement_type as mt', 'mt.id', '=', 'm.movement_type_id')
            //     ->where('destination_operation_id', 1)
            //     ->whereBetween('created_at', [$fechaInit, $fechaEnd])
            //     ->whereIn('movement_type_id', [1, 6, 13, 14])
            //     ->whereNull('deleted_at')
            //     ->orderBy('created_at', 'DESC')
            //     ->get();

            $resultset = array(
                'target'   => 'Ventas pendientes de afectar extractos',
                'movements' => $movements
            );

            
            \Log::info($resultset);
            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }
    public function transaction_success_amount_zero()
    {

        try {
            // $fechaEnd = date('Y-m-d H:i:s');

            // $fecha = date('Y-m-d',strtotime($fechaEnd."- 30 days"));
            // $fechaInit = $fecha.' 00:00:00';


            // $montos = \DB::table('transactions as t')
            // ->select('t.id','t.amount','t.status','t.status_description','a.name as atm','s.name as service')
            // ->leftjoin('atms as a','a.id','=','t.atm_id')

            // ->where('t.status','success')
            // ->where('t.amount',0)
            // ->whereIn('t.transaction_type',[1,7])
            // ->whereBetween('t.created_at', [$fechaInit,$fechaEnd])
            // ->orderBy('t.created_at', 'DESC')
            // ->get();
            /*$montos  = \DB::select(
                "
                select t.id,a.name as atm,t.status,t.amount ,sxm.descripcion as service, t.created_at,status_description  from transactions t
                inner join atms a ON t.atm_id = a.id
                join public.servicios_x_marca sxm
                on t.service_source_id = (case when sxm.service_source_id = 9 then 0 else sxm.service_source_id end) and t.service_id = sxm.service_id
                where t.amount = 0 and t.status = 'success' and t.transaction_type in (1,7,12) and t.created_at between date(now() - interval '30 day') and date(now())
                and sxm.deleted_at isnull 
                order by t.created_at desc
            "
            );*/

            $montos = \DB::select("
                select 
                    t.id,
                    a.name as atm,
                    t.status,
                    t.amount,
                    sxm.descripcion as service, 
                    t.created_at,
                    status_description  
                from transactions as t
                join atms a on t.atm_id = a.id
                join public.servicios_x_marca sxm on t.service_source_id = sxm.service_source_id and t.service_id = sxm.service_id
                where t.amount = 0 
                and t.status = 'success' 
                and t.transaction_type in (1, 7, 12) 
                and t.created_at between date(now() - interval '30 day') 
                and date(now())
                and sxm.deleted_at is null 
                order by t.created_at desc
            ");

            $resultset = array(
                'target'   => 'Transacciones exitosas con monto cero',
                'montos' => $montos
            );

            \Log::info($resultset);

            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    public function conciliations_detailsSearchExport()
    {
        try {
            $input = $this->input;
            $reservation_time = $input['reservationtime'];
            $status_id = $input['status_id'];
            $atm_id = $input['atm_id'];
            $service_id = $input['service_id'];
            $service_request_id = $input['service_request_id'];

            $where = "";
            /*SET DATE RANGE*/
            $daterange = explode(' - ',  str_replace('/', '-', $reservation_time));
            $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
            $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
            $where .= "incomes.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' AND ";
            $where .= ($atm_id <> 0) ? "transactions.atm_id = " . $atm_id . " AND " : "";
            $where .= ($status_id <> -1) ? "incomes.destination_operation_id in " . $status_id . " AND " : "";

            if ($service_id == -2) {
                $where .= 'transactions.service_source_id = 1';
                if ($service_request_id <> '0') {
                    $where .= ' AND transactions.service_id = ' . $service_request_id;
                }
            } elseif ($service_id == -4) {
                $where .= 'transactions.service_source_id = 4';
                if ($service_request_id <> '0') {
                    $where .= ' AND transactions.service_id = ' . $service_request_id;
                }
            } elseif ($service_id == -6) {
                $where .= 'transactions.service_source_id = 6';
                if ($service_request_id <> '0') {
                    $where .= ' AND transactions.service_id = ' . $service_request_id;
                }
            } elseif ($service_id == -7) {
                $where .= 'transactions.service_source_id = 7';
                if ($service_request_id <> '0') {
                    $where .= ' AND transactions.service_id = ' . $service_request_id;
                }
            } elseif ($service_id == 66) {
                $where .= 'transactions.service_source_id = 8 and transactions.service_id= 14';
                if ($service_request_id <> '0') {
                    $where .= ' AND transactions.service_id = ' . $service_request_id;
                }
            } elseif ($service_id == 59) {
                $where .= 'transactions.service_source_id = 8 and transactions.service_id= 3';
                if ($service_request_id <> '0') {
                    $where .= ' AND transactions.service_id = ' . $service_request_id;
                }
            } elseif ($service_id == 57) {
                $where .= 'transactions.service_source_id = 8 and transactions.service_id= 4';
                if ($service_request_id <> '0') {
                    $where .= ' AND transactions.service_id = ' . $service_request_id;
                }
            } elseif ($service_id == 58) {
                $where .= 'transactions.service_source_id = 8 and transactions.service_id= 5';
                if ($service_request_id <> '0') {
                    $where .= ' AND transactions.service_id = ' . $service_request_id;
                }
            } elseif ($service_id == 67) {
                $where .= 'transactions.service_source_id = 8 and transactions.service_id= 15';
                if ($service_request_id <> '0') {
                    $where .= ' AND transactions.service_id = ' . $service_request_id;
                }
            } elseif ($this->user->hasAccess('ticketea') && !$this->user->hasAccess('superuser')) {
                $where .= 'transactions.service_id = 28';
            } else {
                $where .= ($service_id <> 0) ? "transactions.service_id = " . $service_id . " AND service_source_id = 0" : "";
            }

            $where = trim($where);
            $where = trim($where, 'AND');
            $where = trim($where);

            $incomes = \DB::table('incomes')
                ->select('incomes.id', 'atms.name', 'incomes.transaction_id', 'transactions.amount as amount', 'transactions.service_id', 'transactions.service_source_id', 'atms.code as atm_code', 'service_provider_products.description as service_description', 'incomes.response', 'incomes.created_at', 'incomes.updated_at')
                ->whereIn('destination_operation_id', ['0', '1', '5', '212'])
                ->join('transactions', 'transactions.id', '=', 'transaction_id')
                ->join('atms', 'atms.id', '=', 'transactions.atm_id')
                ->leftjoin('service_provider_products', 'service_provider_products.id', '=', 'transactions.service_id')
                ->whereRaw("$where")
                ->orderBy('atms.name', 'asc')
                ->orderBy('incomes.created_at', 'asc')
                ->get();

            foreach ($incomes as $income) {
                $income->name = $income->atm_code . ' | ' . $income->name;
                $income->amount = number_format($income->amount, 0, '', '');

                if ($income->service_source_id <> 0) {
                    // transaccion de NETEL
                    $service_sources_data = \DB::table('services_ondanet_pairing')
                        ->where('service_request_id', $income->service_id)
                        ->where('service_source_id', $income->service_source_id)
                        ->first();
                    $service_source_description = \DB::table('services_providers_sources')->where('id', $income->service_source_id)->first();
                    $service_description = '';
                    if ($service_sources_data) {
                        $service_description  .= $service_source_description->description . ' - ' . $service_sources_data->service_description;
                    }
                    $income->service_description = $service_description;
                }
                unset($income->service_source_id);
                unset($income->service_id);
                unset($income->atm_code);
            }

            return $incomes;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    /** DEPOSITOS DE CUOTAS*/
    public function depositosAlquileresReports($request)
    {
        try {

            $desde = Carbon::today();
            $hasta = Carbon::tomorrow()->modify('-1 seconds');

            if (!\Sentinel::getUser()->inRole('mini_terminal')) {

                $boletas = \DB::table('mt_recibos_pagos_miniterminales')
                    ->select([
                        'mt_recibos_pagos_miniterminales.id',
                        'fecha',
                        'bancos.descripcion as banco',
                        'cuentas_bancarias.numero_banco as cuenta_bancaria',
                        'boleta_numero',
                        'mt_recibos_pagos_miniterminales.monto',
                        'user_id',
                        'tipo_pago.descripcion as tipo_pago',
                        'estado',
                        'mt_recibos_pagos_miniterminales.deleted_at',
                        'mt_recibos_pagos_miniterminales.updated_at',
                        'mt_recibos_pagos_miniterminales.updated_by',
                        'users.username as username',
                        'mt_recibos_pagos_miniterminales.message',
                        'reprinted'
                    ])
                    ->join('cuentas_bancarias', 'cuentas_bancarias.id', '=', 'mt_recibos_pagos_miniterminales.cuenta_bancaria_id')
                    ->join('bancos', 'bancos.id', '=', 'cuentas_bancarias.banco_id')
                    ->join('tipo_pago', 'tipo_pago.id', '=', 'mt_recibos_pagos_miniterminales.tipo_pago_id')
                    ->leftjoin('users', 'users.id', '=', 'mt_recibos_pagos_miniterminales.updated_by')
                    ->leftjoin('mt_recibos', 'mt_recibos.id', '=', 'mt_recibos_pagos_miniterminales.recibo_id')
                    ->whereRaw("fecha BETWEEN '{$desde}' AND '{$hasta}'")
                    ->where('estado', true)
                    ->where('mt_recibos_pagos_miniterminales.tipo_recibo_id', 2)
                    ->orderBy('mt_recibos_pagos_miniterminales.id', 'desc')
                    ->get();
            } else {
                $boletas = \DB::table('mt_recibos_pagos_miniterminales')
                    ->select([
                        'mt_recibos_pagos_miniterminales.id',
                        'fecha',
                        'bancos.descripcion as banco',
                        'cuentas_bancarias.numero_banco as cuenta_bancaria',
                        'boleta_numero',
                        'mt_recibos_pagos_miniterminales.monto',
                        'user_id',
                        'tipo_pago.descripcion as tipo_pago',
                        'estado',
                        'mt_recibos_pagos_miniterminales.deleted_at',
                        'mt_recibos_pagos_miniterminales.updated_at',
                        'mt_recibos_pagos_miniterminales.updated_by',
                        'users.username as username',
                        'mt_recibos_pagos_miniterminales.message',
                        'reprinted'
                    ])
                    ->join('cuentas_bancarias', 'cuentas_bancarias.id', '=', 'mt_recibos_pagos_miniterminales.cuenta_bancaria_id')
                    ->join('bancos', 'bancos.id', '=', 'cuentas_bancarias.banco_id')
                    ->join('tipo_pago', 'tipo_pago.id', '=', 'mt_recibos_pagos_miniterminales.tipo_pago_id')
                    ->leftjoin('users', 'users.id', '=', 'mt_recibos_pagos_miniterminales.updated_by')
                    ->leftjoin('mt_recibos', 'mt_recibos.id', '=', 'mt_recibos_pagos_miniterminales.recibo_id')
                    ->where('user_id', $this->user->id)
                    ->whereRaw("fecha BETWEEN '{$desde}' AND '{$hasta}'")
                    ->where('estado', true)
                    ->where('mt_recibos_pagos_miniterminales.tipo_recibo_id', 2)
                    ->orderBy('mt_recibos_pagos_miniterminales.id', 'desc')
                    ->get();
            }

            $usersNames = \DB::connection('eglobalt_auth')
                ->table('users')
                ->selectRaw('concat(username, \' - \', description) as full_name, id')
                ->join('role_users', 'users.id', '=', 'role_users.user_id')
                ->where('role_users.role_id', 22)
                ->pluck('full_name', 'id');

            $usersId = \DB::connection('eglobalt_auth')
                ->table('users')
                ->join('role_users', 'users.id', '=', 'role_users.user_id')
                ->where('role_users.role_id', 22)
                ->pluck('id', 'id');

            $branches = \DB::table('branches')
                ->select('branches.*')
                ->whereIn('branches.user_id', $usersId)
                ->get();

            $data_select = [];
            foreach ($branches as $key => $branch) {
                $data_select[$branch->user_id] = $branch->description . ' | ' . $usersNames[$branch->user_id];
            }
            $results = $this->arrayPaginator($boletas->toArray(), $request);

            $resultset = array(
                'target' => 'Depositos Alquileres Miniterminales',
                'transactions' => $results,
                'reservationtime' => (isset($input['reservationtime']) ? $input['reservationtime'] : 0)
            );
            $status = array('0' => 'Todos', '1' => 'Confirmados', '2' => 'Rechazados', '3' => 'Pendientes');

            $resultset['usersNames']    = $usersNames;
            $resultset['branches']      = $branches;
            $resultset['data_select']   = $data_select;
            $resultset['status']        = $status;
            $resultset['status_set']    = 0;
            $resultset['user_id']       = '';
            //dd($resultset);
            return $resultset;
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de reporte Alquiler" . $e);
            return false;
        }
    }

    public function depositosAlquileresSearch($request)
    {
        try {
            $input = $this->input;

            /*Busqueda minusiosa*/
            /*SET DATE RANGE*/

            if (isset($input['reservationtime'])) {
                $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                $where = "fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
            }

            if ($input['status_id'] != 0) {
                if ($input['status_id'] == '1') {
                    $where .= " AND estado is true";
                }
                if ($input['status_id'] == '2') {
                    $where .= " AND estado is false";
                }
                if ($input['status_id'] == '3') {
                    $where .= " AND estado is null";
                }
            }

            $where .= " AND mt_recibos_pagos_miniterminales.tipo_recibo_id = 2";


            if (!\Sentinel::getUser()->inRole('mini_terminal')) {
                $user_id = $input['user_id'];
                $boletas = \DB::table('mt_recibos_pagos_miniterminales')
                    ->select([
                        'mt_recibos_pagos_miniterminales.id',
                        'fecha',
                        'bancos.descripcion as banco',
                        'cuentas_bancarias.numero_banco as cuenta_bancaria',
                        'boleta_numero',
                        'mt_recibos_pagos_miniterminales.monto',
                        'user_id',
                        'tipo_pago.descripcion as tipo_pago',
                        'estado',
                        'mt_recibos_pagos_miniterminales.deleted_at',
                        'mt_recibos_pagos_miniterminales.updated_at',
                        'mt_recibos_pagos_miniterminales.updated_by',
                        'users.username as username',
                        'message',
                        'reprinted'
                    ])
                    ->join('cuentas_bancarias', 'cuentas_bancarias.id', '=', 'mt_recibos_pagos_miniterminales.cuenta_bancaria_id')
                    ->join('bancos', 'bancos.id', '=', 'cuentas_bancarias.banco_id')
                    ->join('tipo_pago', 'tipo_pago.id', '=', 'mt_recibos_pagos_miniterminales.tipo_pago_id')
                    ->leftjoin('users', 'users.id', '=', 'mt_recibos_pagos_miniterminales.updated_by')
                    ->leftjoin('mt_recibos', 'mt_recibos.id', '=', 'mt_recibos_pagos_miniterminales.recibo_id')
                    ->whereRaw("$where")
                    ->where(function ($query) use ($user_id) {
                        if (!empty($user_id)) {
                            $query->where('mt_recibos_pagos_miniterminales.user_id', $user_id);
                        }
                    })
                    ->orderBy('mt_recibos_pagos_miniterminales.id', 'desc')
                    ->get();
            } else {
                $user_id = $this->user->id;
                $boletas = \DB::table('mt_recibos_pagos_miniterminales')
                    ->select([
                        'mt_recibos_pagos_miniterminales.id',
                        'fecha',
                        'bancos.descripcion as banco',
                        'cuentas_bancarias.numero_banco as cuenta_bancaria',
                        'boleta_numero',
                        'mt_recibos_pagos_miniterminales.monto',
                        'user_id',
                        'tipo_pago.descripcion as tipo_pago',
                        'estado',
                        'mt_recibos_pagos_miniterminales.deleted_at',
                        'mt_recibos_pagos_miniterminales.updated_at',
                        'mt_recibos_pagos_miniterminales.updated_by',
                        'users.username as username',
                        'message',
                        'reprinted'
                    ])
                    ->join('cuentas_bancarias', 'cuentas_bancarias.id', '=', 'mt_recibos_pagos_miniterminales.cuenta_bancaria_id')
                    ->join('bancos', 'bancos.id', '=', 'cuentas_bancarias.banco_id')
                    ->join('tipo_pago', 'tipo_pago.id', '=', 'mt_recibos_pagos_miniterminales.tipo_pago_id')
                    ->leftjoin('users', 'users.id', '=', 'mt_recibos_pagos_miniterminales.updated_by')
                    ->leftjoin('mt_recibos', 'mt_recibos.id', '=', 'mt_recibos_pagos_miniterminales.recibo_id')
                    ->whereRaw("$where")
                    ->where('mt_recibos_pagos_miniterminales.user_id', $user_id)
                    ->orderBy('mt_recibos_pagos_miniterminales.id', 'desc')
                    ->get();
            }
            //dd($boletas);
            $results = $this->arrayPaginator($boletas->toArray(), $request);

            $resultset = array(
                'target'        => 'Depositos Alquileres Miniterminales',
                'transactions'  => $results,
                'reservationtime' => (isset($input['reservationtime']) ? $input['reservationtime'] : 0),
                'i'             =>  1,
            );

            $usersNames = \DB::connection('eglobalt_auth')
                ->table('users')
                ->selectRaw('concat(username, \' - \', description) as full_name, id')
                ->join('role_users', 'users.id', '=', 'role_users.user_id')
                ->where('role_users.role_id', 22)
                ->pluck('full_name', 'id');

            $usersId = \DB::connection('eglobalt_auth')
                ->table('users')
                ->join('role_users', 'users.id', '=', 'role_users.user_id')
                ->where('role_users.role_id', 22)
                ->pluck('id', 'id');

            $branches = \DB::table('branches')
                ->select('branches.*')
                ->whereIn('branches.user_id', $usersId)
                ->get();

            $status = array('0' => 'Todos', '1' => 'Confirmados', '2' => 'Rechazados', '3' => 'Pendientes');

            $data_select = [];
            foreach ($branches as $key => $branch) {
                $data_select[$branch->user_id] = $branch->description . ' | ' . $usersNames[$branch->user_id];
            }

            $resultset['usersNames'] = $usersNames;
            $resultset['branches'] = $branches;
            $resultset['status'] = $status;
            $resultset['status_set'] = (isset($input['status_id']) ? $input['status_id'] : 0);
            $resultset['data_select'] = $data_select;
            $resultset['user_id'] = $user_id;

            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    public function depositosAlquileresSearchExport()
    {
        try {
            $input = $this->input;
            /*Busqueda minusiosa*/
            /*SET DATE RANGE*/

            if (isset($input['reservationtime'])) {
                $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                $where = "fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
            }

            if ($input['status_id'] != 0) {
                if ($input['status_id'] == '1') {
                    $where .= " AND estado is true";
                }
                if ($input['status_id'] == '2') {
                    $where .= " AND estado is false";
                }
                if ($input['status_id'] == '3') {
                    $where .= " AND estado is null";
                }
            }

            $where .= " AND mt_recibos_pagos_miniterminales.tipo_recibo_id = 2";

            if (!\Sentinel::getUser()->inRole('mini_terminal')) {
                $user_id = $input['user_id'];
                $boletas = \DB::table('mt_recibos_pagos_miniterminales')
                    ->select([
                        'mt_recibos_pagos_miniterminales.id',
                        'fecha',
                        'users.description',
                        'tipo_pago.descripcion as tipo_pago',
                        'bancos.descripcion as banco',
                        'cuentas_bancarias.numero_banco as cuenta_bancaria',
                        'boleta_numero',
                        'mt_recibos_pagos_miniterminales.monto',
                        'estado',
                        'mt_recibos_pagos_miniterminales.updated_by as username',
                        'mt_recibos_pagos_miniterminales.updated_at as update',
                        'message'
                    ])
                    ->join('cuentas_bancarias', 'cuentas_bancarias.id', '=', 'mt_recibos_pagos_miniterminales.cuenta_bancaria_id')
                    ->join('bancos', 'bancos.id', '=', 'cuentas_bancarias.banco_id')
                    ->join('tipo_pago', 'tipo_pago.id', '=', 'mt_recibos_pagos_miniterminales.tipo_pago_id')
                    ->leftjoin('users', 'users.id', '=', 'mt_recibos_pagos_miniterminales.user_id')
                    ->leftjoin('mt_recibos', 'mt_recibos.id', '=', 'mt_recibos_pagos_miniterminales.recibo_id')
                    ->whereRaw("$where")
                    ->where(function ($query) use ($user_id) {
                        if (!empty($user_id)) {
                            $query->where('mt_recibos_pagos_miniterminales.user_id', $user_id);
                        }
                    })
                    ->orderBy('mt_recibos_pagos_miniterminales.id', 'desc')
                    ->get();
            } else {
                $user_id = $this->user->id;
                $boletas = \DB::table('mt_recibos_pagos_miniterminales')
                    ->select([
                        'mt_recibos_pagos_miniterminales.id',
                        'fecha',
                        'users.description',
                        'tipo_pago.descripcion as tipo_pago',
                        'bancos.descripcion as banco',
                        'cuentas_bancarias.numero_banco as cuenta_bancaria',
                        'boleta_numero',
                        'mt_recibos_pagos_miniterminales.monto',
                        'estado',
                        'mt_recibos_pagos_miniterminales.updated_by as username',
                        'mt_recibos_pagos_miniterminales.updated_at as update',
                        'message'
                    ])
                    ->join('cuentas_bancarias', 'cuentas_bancarias.id', '=', 'mt_recibos_pagos_miniterminales.cuenta_bancaria_id')
                    ->join('bancos', 'bancos.id', '=', 'cuentas_bancarias.banco_id')
                    ->join('tipo_pago', 'tipo_pago.id', '=', 'mt_recibos_pagos_miniterminales.tipo_pago_id')
                    ->leftjoin('users', 'users.id', '=', 'mt_recibos_pagos_miniterminales.user_id')
                    ->leftjoin('mt_recibos', 'mt_recibos.id', '=', 'mt_recibos_pagos_miniterminales.recibo_id')
                    ->whereRaw("$where")
                    ->where('mt_recibos_pagos_miniterminales.user_id', $user_id)
                    ->orderBy('mt_recibos_pagos_miniterminales.id', 'desc')
                    ->get();
            }

            foreach ($boletas as $boleta) {
                $boleta->fecha = date('d/m/Y', strtotime($boleta->fecha));
                if ($boleta->estado == true) {
                    $boleta->estado = 'Confirmado';
                } else if ($boleta->estado == false) {
                    $boleta->estado = 'Rechazado';
                } else {
                    $boleta->estado = 'Pendiente';
                }
                if (isset($boleta->username)) {
                    $user = \DB::table('users')->where('id', $boleta->username)->first();
                    $boleta->username = $user->username;
                }
                $boleta->update = date('d/m/Y H:i:s', strtotime($boleta->update));
            }

            $resultset = array(
                'transactions'  => $boletas
            );

            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    /** REPORTE DE CUOTAS DE ALQUILER **/
    public function cuotasAlquilerReports($request)
    {
        try {

            $groups = Group::join('alquiler', 'business_groups.id', '=', 'alquiler.group_id')
                ->where('alquiler.destination_operation_id', '!=', 0)
                ->pluck('description', 'business_groups.id');
            $groups->prepend('Todos', '0');

            $atms = Atm::join('housing', 'housing.id', '=', 'atms.housing_id')
                ->join('alquiler_housing', 'housing.id', '=', 'alquiler_housing.housing_id')
                ->join('alquiler', 'alquiler.id', '=', 'alquiler_housing.alquiler_id')
                ->whereIn('atms.owner_id', [16, 21, 25])
                ->where('alquiler.destination_operation_id', '!=', 0)
                ->pluck('name', 'atms.id');
            $atms->prepend('Todos', '0');

            $resultset = array(
                'target'    => 'Cuotas Alquiler',
                'groups'    => $groups,
                'atms'      => $atms,
                'group_id'  => 0,
                'atm_id'    => 0,
            );

            return $resultset;
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de reportes" . $e);
            return false;
        }
    }

    public function cuotasAlquilerSearch($request)
    {

        try {
            $input = $this->input;

            if (!\Sentinel::getUser()->inRole('mini_terminal')) {
                $group_id = $input['group_id'];
                if (!empty($group_id) || $group_id != 0) {

                    if (!empty($input['atm_id']) || $input['atm_id'] != 0) {
                        $atm_id = '(' . $input['atm_id'] . ')';
                    } else {
                        $atms = \DB::table('atms')
                            ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                            ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                            ->whereIn('atms.owner_id', [16, 21, 25])
                            ->where('branches.group_id', $group_id)
                            ->pluck('atms.id', 'atms.id');

                        $atms_id = implode(',', $atms->toArray());

                        $atm_id = '(' . $atms_id . ')';
                    }
                } else {
                    if (!empty($input['atm_id']) || $input['atm_id'] != 0) {
                        $atm_id = '(' . $input['atm_id'] . ')';
                    } else {
                        $atm_id = null;
                    }
                }
            } else {
                $atm = \DB::table('atms')
                    ->select([
                        'atms.id'
                    ])
                    ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                    ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                    ->whereIn('atms.owner_id', [16, 21, 25])
                    ->where('branches.user_id', $this->user->id)
                    ->first();

                $atm_id = '(' . $atm->id . ')';

                $input['atm_id'] = $atm->id;
                $input['group_id'] = 0;
            }

            // $where = trim($where);
            // $where = trim($where, 'AND');
            // $where = trim($where);
            $cuotas = \DB::table('cuotas_alquiler')
                ->select([
                    'cuotas_alquiler.id',
                    'atms.name as nombre',
                    'cuotas_alquiler.num_cuota',
                    'cuotas_alquiler.importe',
                    'cuotas_alquiler.saldo_cuota',
                    'cuotas_alquiler.fecha_vencimiento',
                    'cuotas_alquiler.num_venta',
                    'cuotas_alquiler.reprinted'
                ])
                ->join('alquiler', 'alquiler.id', '=', 'cuotas_alquiler.alquiler_id')
                ->join('alquiler_housing', 'alquiler.id', '=', 'alquiler_housing.alquiler_id')
                ->join('atms', 'atms.housing_id', '=', 'alquiler_housing.housing_id')
                ->where(function ($query) use ($atm_id) {
                    if (!empty($atm_id)) {
                        $query->whereRaw('atms.id in ' . $atm_id);
                    }
                })
                ->whereNull('alquiler.deleted_at')
                ->orderBy('cuotas_alquiler.num_cuota', 'asc')
                ->orderBy('cuotas_alquiler.id', 'asc')
                ->get();
            
            $results = $this->arrayPaginator($cuotas->toArray(), $request);

            $resultset = array(
                'target'        => 'Cuotas Alquiler',
                'transactions'  => $results,
                'i'             =>  1,
            );
            
            $groups = Group::join('alquiler', 'business_groups.id', '=', 'alquiler.group_id')
                ->where('alquiler.destination_operation_id', '!=', 0)
                ->pluck('description', 'business_groups.id');
            $groups->prepend('Todos', '0');

            $atms = Atm::join('housing', 'housing.id', '=', 'atms.housing_id')
                ->join('alquiler_housing', 'housing.id', '=', 'alquiler_housing.housing_id')
                ->join('alquiler', 'alquiler.id', '=', 'alquiler_housing.alquiler_id')
                ->whereIn('atms.owner_id', [16, 21, 25])
                ->where('alquiler.destination_operation_id', '!=', 0)
                ->pluck('name', 'atms.id');
            $atms->prepend('Todos', '0');

            $resultset['atms'] = $atms;
            //$resultset['status'] = $status;
            //$resultset['status_set'] = (isset($input['status_id'])?$input['status_id']:0);
            $resultset['atm_id'] = $input['atm_id'];
            $resultset['groups'] = $groups;
            $resultset['group_id'] = $input['group_id'];

            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    /** PAGO DE CLIENTES CON SALDO A FAVOR **/
    public function pagoClientesReports($request)
    {
        try {

            $desde = Carbon::today();
            $hasta = Carbon::tomorrow()->modify('-1 seconds');

            $pagos = \DB::table('mt_pago_clientes as p')
                ->select([
                    'p.id',
                    'p.created_at',
                    'p.monto',
                    'p.created_by',
                    'p.estado',
                    'p.deleted_at',
                    'p.updated_at',
                    'p.updated_by',
                    'bg.description as grupo',
                    'u.description as creado',
                    'b.description as actualizado'
                ])
                ->join('business_groups as bg', 'bg.id', '=', 'p.group_id')
                ->join('users as u', 'u.id', '=', 'p.created_by')
                ->leftJoin('users as b', 'b.id', '=', 'p.updated_by')
                ->whereRaw("p.created_at BETWEEN '{$desde}' AND '{$hasta}'")
                ->where('estado', true)
                ->orderBy('p.id', 'desc')
                ->get();

            $results = $this->arrayPaginator($pagos->toArray(), $request);

            $resultset = array(
                'target' => 'Pagos de Clientes Miniterminales',
                'transactions' => $results,
                'reservationtime' => (isset($input['reservationtime']) ? $input['reservationtime'] : 0)
            );

            $groups = Group::pluck('description', 'id');

            $status = array('0' => 'Todos', '1' => 'Confirmados', '2' => 'Rechazados', '3' => 'Pendientes');

            $resultset['status']        = $status;
            $resultset['status_set']    = 0;
            $resultset['groups']        = $groups;
            $resultset['group_id']      = 0;

            return $resultset;
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de reportes" . $e);
            return false;
        }
    }

    public function pagoClientesSearch($request)
    {
        try {
            $input = $this->input;
         
            /*Busqueda minusiosa*/
            if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
                $where = "boleta_numero like '%{$input['context']}%' ";
            } else {
                /*SET DATE RANGE*/
                if (isset($input['reservationtime'])) {
                    $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                    $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                    $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                    $where = "p.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                }

                if ($input['status_id'] != 0) {
                    if ($input['status_id'] == '1') {
                        $where .= " AND estado is true";
                    }
                    if ($input['status_id'] == '2') {
                        $where .= " AND estado is false";
                    }
                    if ($input['status_id'] == '3') {
                        $where .= " AND estado is null";
                    }
                }
            }

            if (!empty($input['group_id']) && $input['group_id'] != 0) {

                $group_id = $input['group_id'];

                $where .= " AND p.group_id = $group_id";
            }

            // $where = trim($where);
            // $where = trim($where, 'AND');
            // $where = trim($where);
            $boletas = \DB::table('mt_pago_clientes as p')
                ->select([
                    'p.id',
                    'p.created_at',
                    'p.monto',
                    'p.created_by',
                    'p.estado',
                    'p.deleted_at',
                    'p.updated_at',
                    'p.updated_by',
                    'bg.description as grupo',
                    'u.description as creado',
                    'b.description as actualizado'
                ])
                ->join('business_groups as bg', 'bg.id', '=', 'p.group_id')
                ->join('users as u', 'u.id', '=', 'p.created_by')
                ->leftJoin('users as b', 'b.id', '=', 'p.updated_by')
                ->whereRaw("$where")
                ->orderBy('p.id', 'desc')
                ->get();
            //dd($boletas);
            $results = $this->arrayPaginator($boletas->toArray(), $request);

            $resultset = array(
                'target'        => 'Pagos de Clientes Miniterminales',
                'transactions'  => $results,
                'reservationtime' => (isset($input['reservationtime']) ? $input['reservationtime'] : 0),
                'i'             =>  1,
            );

            $groups = Group::pluck('description', 'id');

            $status = array('0' => 'Todos', '1' => 'Confirmados', '2' => 'Rechazados', '3' => 'Pendientes');

            $resultset['status'] = $status;
            $resultset['status_set'] = (isset($input['status_id']) ? $input['status_id'] : 0);
            $resultset['groups'] = $groups;
            if (!empty($input['group_id'])) {
                $resultset['group_id'] = $input['group_id'];
            } else {
                $resultset['group_id'] = 0;
            }

            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    public function pagoClientesSearchExport()
    {
        try {
            $input = $this->input;
            /*Busqueda minusiosa*/
            if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
                $where = "boleta_numero like '%{$input['context']}%' ";
            } else {
                /*SET DATE RANGE*/
                if (isset($input['reservationtime'])) {
                    $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                    $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                    $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                    $where = "p.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                }

                if ($input['status_id'] != 0) {
                    if ($input['status_id'] == '1') {
                        $where .= " AND estado is true";
                    }
                    if ($input['status_id'] == '2') {
                        $where .= " AND estado is false";
                    }
                    if ($input['status_id'] == '3') {
                        $where .= " AND estado is null";
                    }
                }
            }

            if (!empty($input['group_id']) && $input['group_id'] != 0) {

                $group_id = $input['group_id'];

                $where .= " AND p.group_id = $group_id";
            }

            $boletas = \DB::table('mt_pago_clientes as p')
                ->select([
                    'p.id',
                    'p.created_at',
                    'bg.description as grupo',
                    'u.description as creado',
                    'p.monto',
                    'p.estado',
                    'b.description as actualizado',
                    'p.updated_at as update',
                ])
                ->join('business_groups as bg', 'bg.id', '=', 'p.group_id')
                ->join('users as u', 'u.id', '=', 'p.created_by')
                ->leftJoin('users as b', 'b.id', '=', 'p.updated_by')
                ->whereRaw("$where")
                ->orderBy('p.id', 'desc')
                ->get();

            foreach ($boletas as $boleta) {
                $boleta->created_at = date('d/m/Y', strtotime($boleta->created_at));
                if ($boleta->estado == true) {
                    $boleta->estado = 'Confirmado';
                } else if ($boleta->estado == false) {
                    $boleta->estado = 'Rechazado';
                } else {
                    $boleta->estado = 'Pendiente';
                }
                $boleta->update = date('d/m/Y H:i:s', strtotime($boleta->update));
            }

            $resultset = array(
                'transactions'  => $boletas
            );

            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }


    public function contractsReports()
    {
        try {
            //Redes
            $whereGroup = "";
            $whereAtm = "";
            $whereContract = "";

            $groups     = Group::orderBy('business_groups.description')->where(function ($query) use ($whereGroup) {
                if (!empty($whereGroup)) {
                    $query->whereRaw($whereGroup);
                }
            })->get()->pluck('description', 'id')->prepend('Todos', '0')->toArray();

            $atms     = Atmnew::orderBy('atms.name')->where(function ($query) use ($whereAtm) {
                if (!empty($whereAtm)) {
                    $query->whereRaw($whereAtm);
                }
            })->get()->pluck('name', 'id')->prepend('Todos', '0')->toArray();


            // $contracts     = Contract::orderBy('contract.number')->where(function($query) use($whereContract){
            //     if(!empty($whereContract)){
            //         $query->whereRaw($whereContract);
            //     }
            // })->get()->pluck('number','id');
            // $contracts->prepend('Todos','0');

            //dd($contracts);
            $resultset = array(
                'target'        => 'Contratos miniterminales',
                'groups'        => $groups,
                'group_id'      => 0,
                'atms'          => $atms,
                'atm_id'        => 0,
                'status'        => '0'
                // 'contracts'     => $contracts,
                // 'contract_id'   => 0
            );
    
            return $resultset;
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de reportes" . $e);
            return false;
        }
    }

    public function contractsSearch()
    {

        try {
            $input = $this->input;
            $where = " CAST(atms.code AS BIGINT)= contract.number AND ";
            /*Busqueda minusiosa*/
            if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
                //dd($input['context']);
                $where = "";
                $where .= ($input['context'] <> 0) ? "CAST(contract.number AS TEXT) like '%" . $input['context'] . "%' AND " : "";
                //dd($where);
            } else {
                if ($input['reservationtime'] <> 'Todos') {
                    /*SET DATE RANGE*/
                    $fecha = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                    $fecha[0] = date('Y-m-d H:i:s', strtotime($fecha[0]));
                    $fecha[1] = date('Y-m-d H:i:s', strtotime($fecha[1]));
                    $where .= "contract.created_at BETWEEN '{$fecha[0]}' AND '{$fecha[1]}' AND ";
                }
                $where .= ($input['group_id'] <> 0) ? "contract.busines_group_id = " . $input['group_id'] . " AND " : "";
                $where .= ($input['atm_id'] <> 0) ? "atms.id = " . $input['atm_id'] . " AND " : "";
                $where .= ($input['status'] <> 0) ? "CAST(contract.status AS BIGINT) = " . strval($input['status']) . " AND " : "";
            }

            $where = trim($where);
            $where = trim($where, 'AND');
            $where = trim($where);
            //dd($where);
            $contracts = \DB::table('atms')
                ->select(\DB::raw("atms.id,
                                    atms.name, 
                                    atms.code, 
                                    atms.atm_status,
                                    points_of_sale.pos_code as pos_code, points_of_sale.description as pos_description,
                 branches.description as sucursal, business_groups.ruc as group_ruc, business_groups.description as group_description, 
                 contract.id as id_contract, contract.number as number_contract,contract.credit_limit, contract.date_init, contract.date_end, 
                 contract.status,contract.reception_date ,contract_type.description as description_contract_type,  Min(cuotas_alquiler.fecha_vencimiento) as inicio_operacion,
                  contract.signature_date as fecha_aprobacion, DATE_PART('day', contract.date_end - now()) as restantes
                  "))
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'atms.id')
                ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->join('business_groups', 'business_groups.id', '=', 'branches.group_id')
                ->join('contract', 'contract.busines_group_id', '=', 'business_groups.id')
                ->join('contract_type', 'contract_type.id', '=', 'contract.contract_type')
                ->leftJoin('alquiler', 'alquiler.group_id', '=', 'business_groups.id')
                ->leftJoin('cuotas_alquiler', 'cuotas_alquiler.alquiler_id', '=', 'alquiler.id')
                ->whereRaw("$where")
                ->whereNull('atms.deleted_at')
                ->orderBy('contract.number', 'desc')
                ->groupBy(
                    'atms.id',
                    'atms.name',
                    'atms.code',
                    'atms.atm_status',
                    'points_of_sale.pos_code',
                    'points_of_sale.description',
                    'branches.description',
                    'business_groups.ruc',
                    'business_groups.description',
                    'contract.id',
                    'contract.number',
                    'contract.credit_limit',
                    'contract.date_init',
                    'contract.date_end',
                    'contract.status',
                    'contract.reception_date',
                    'contract_type.description',
                    'contract.signature_date'
                )
                ->paginate(20);
            //dd($contracts);

            /*Carga datos del formulario*/
            $whereGroup = "";
            $whereAtm = "";
            //$whereContract = "";

            //Redes
            $groups     = Group::orderBy('business_groups.description')->where(function ($query) use ($whereGroup) {
                if (!empty($whereGroup)) {
                    $query->whereRaw($whereGroup);
                }
            })->get()->pluck('description', 'id');
            $groups->prepend('Todos', '0');

            $atms     = Atmnew::orderBy('atms.name')->where(function ($query) use ($whereAtm) {
                if (!empty($whereAtm)) {
                    $query->whereRaw($whereAtm);
                }
            })->get()->pluck('name', 'id');
            $atms->prepend('Todos', '0');


            $resultset = array(
                'target'            => 'Contratos miniterminales',
                'groups'            => $groups,
                'contracts'         => $contracts,
                'group_id'          => (isset($input['group_id']) ? $input['group_id'] : 0),
                'reservationtime'   => (isset($input['reservationtime']) ? $input['reservationtime'] : ''),
                'i'                 =>  1,
                'atms'              => $atms,
                'atm_id'            => (isset($input['atm_id']) ? $input['atm_id'] : 0),
                'status'            => (isset($input['status']) ? $input['status'] : 0),
                // 'contracts'         => $contracts,
                // 'contract_id'       => (isset($input['contract_id'])?$input['contract_id']:0),

            );

            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    public function contractsSearchExport()
    {
        try {
            $input = $this->input;

            $where = " CAST(atms.code AS BIGINT)= contract.number AND ";
            /*Busqueda minusiosa*/
            if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
                // $where .= "contract.number like'%{$input['context']}%' ";

            } else {
                if ($input['reservationtime'] <> 'Todos') {
                    /*SET DATE RANGE*/
                    $fecha = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                    $fecha[0] = date('Y-m-d H:i:s', strtotime($fecha[0]));
                    $fecha[1] = date('Y-m-d H:i:s', strtotime($fecha[1]));
                    $where .= "contract.created_at BETWEEN '{$fecha[0]}' AND '{$fecha[1]}' AND ";
                }
                //dd($where);
                $where .= ($input['group_id'] <> 0) ? "contract.busines_group_id = " . $input['group_id'] . " AND " : "";
                $where .= ($input['atm_id'] <> 0) ? "atms.id = " . $input['atm_id'] . " AND " : "";
                $where .= ($input['status'] <> 0) ? "CAST(contract.status AS BIGINT) = " . strval($input['status']) . " AND " : "";

                //$where .= ($input['contract_id']<>0) ? "contract.id = ". $input['contract_id']." AND " : "";
            }
            $where = trim($where);
            $where = trim($where, 'AND');
            $where = trim($where);

            $contracts = \DB::table('atms')
                ->select(\DB::raw("contract.id as id_contract, 
                                    contract.number as number_contract, 
                                    contract_type.description as description_contract_type, 
                                    contract.date_init, 
                                    contract.date_end,
                                    DATE_PART('day', contract.date_end - now()) as restantes,
                                    contract.credit_limit,
                                    case 
                                        when CAST(contract.status AS BIGINT) = 1 then 'Recepcionado' 
                                        when CAST(contract.status AS BIGINT) = 2 then 'Activo' 
                                        when CAST(contract.status AS BIGINT) = 3 then 'Inactivo'
                                        when CAST(contract.status AS BIGINT) = 4 then 'Vencido' 
                                    end as status,
                                    contract.reception_date,
                                    contract.signature_date as fecha_aprobacion,
                                    business_groups.description as group_description, 
                                    atms.name, 
                                    Min(cuotas_alquiler.fecha_vencimiento) as inicio_operacion "))
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'atms.id')
                ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->join('business_groups', 'business_groups.id', '=', 'branches.group_id')
                ->join('contract', 'contract.busines_group_id', '=', 'business_groups.id')
                ->join('contract_type', 'contract_type.id', '=', 'contract.contract_type')
                ->leftJoin('alquiler', 'alquiler.group_id', '=', 'business_groups.id')
                ->leftJoin('cuotas_alquiler', 'cuotas_alquiler.alquiler_id', '=', 'alquiler.id')
                ->whereRaw("$where")
                ->whereNull('atms.deleted_at')
                ->orderBy('contract.number', 'desc')
                ->groupBy(
                    'contract.id',
                    'contract.number',
                    'contract_type.description',
                    'contract.date_init',
                    'contract.date_end',
                    'contract.credit_limit',
                    'contract.reception_date',
                    'business_groups.description',
                    'atms.name',
                    'contract.signature_date'
                )
                ->get();

            // dd($contracts);

            return $contracts;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    public function getReversionsForGroups($group_id, $fecha)
    {

        if (isset($fecha) && $fecha != '0' && $fecha != '2') {
            $daterange = explode('-',  str_replace('/', '-', $fecha));
            $daterange[0] = date('Y-m-d H:i:s', ($daterange[0] / 1000));
            $daterange[1] = date('Y-m-d H:i:s', ($daterange[1] / 1000));
            $whereTransactions = "mt_recibos_reversiones.fecha_reversion BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
        } else {
            if ($fecha != '0') {
                $date = date('N');

                if ($date == 1 || $date == 3 || $date == 5) {
                    $hasta = Carbon::parse(date('Y-m-d 23:59:59'))->modify('-1 days');
                } else if ($date == 2 || $date == 4 || $date == 6) {
                    $hasta = Carbon::parse(date('Y-m-d 23:59:59'))->modify('-2 days');
                } else {
                    $hasta = Carbon::parse(date('Y-m-d 23:59:59'))->modify('-3 days');
                }

                $whereTransactions = "mt_recibos_reversiones.fecha_reversion <= '" . $hasta . "'";
            } else {
                $whereTransactions = "mt_recibos_reversiones.fecha_reversion <= now()";
            }
        }
        $whereTransactions .= " AND business_groups.id = " . $group_id;

        $usersId = \DB::table('users')
            ->join('role_users', 'users.id', '=', 'role_users.user_id')
            ->where('role_users.role_id', 22)
            ->pluck('id', 'id');

        $user_id = '(' . implode(',', $usersId) . ')';

        $reversiones = \DB::table('transactions')
            ->selectRaw('sum(transactions.amount) as total, transactions.service_source_id, marcas.descripcion, business_groups.description as group')
            ->join('mt_recibos_reversiones', 'transactions.id', '=', 'mt_recibos_reversiones.transaction_id')
            ->join('mt_recibos', 'mt_recibos.id', '=', 'mt_recibos_reversiones.recibo_id')
            ->join('movements', 'movements.id', '=', 'mt_recibos.movements_id')
            ->join('current_account', 'movements.id', '=', 'current_account.movement_id')
            ->join('business_groups', 'business_groups.id', '=', 'current_account.group_id')
            ->join('servicios_x_marca', function ($join) {
                $join->on('servicios_x_marca.service_id', '=', 'transactions.service_id');
                $join->on('servicios_x_marca.service_source_id', '=', 'transactions.service_source_id');
            })
            ->join('marcas', 'marcas.id', '=', 'servicios_x_marca.marca_id')
            ->whereRaw("$whereTransactions")
            ->groupBy('transactions.service_source_id', 'marcas.descripcion', 'business_groups.description')
            ->get();

        \Log::info(json_decode(json_encode($reversiones), true));

        $details = '';
        foreach ($reversiones as $reversion) {
            $details .= '<tr>
              <td>' . $reversion->descripcion . '</td>
              <td>' . number_format($reversion->total, 0) . '</td>
              </tr>';
        }

        return $details;
    }

    public function getCashoutsForGroups($group_id, $fecha)
    {

        if (isset($fecha) && $fecha != '0' && $fecha != '2') {
            $daterange = explode('-',  str_replace('/', '-', $fecha));
            $daterange[0] = date('Y-m-d H:i:s', ($daterange[0] / 1000));
            $daterange[1] = date('Y-m-d H:i:s', ($daterange[1] / 1000));
            $whereTransactions = "transactions.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
        } else {
            $whereTransactions = "transactions.created_at <= now()";
        }
        $whereTransactions .= " AND business_groups.id = " . $group_id;

        $cashouts = \DB::table('transactions')
            ->selectRaw('sum(transactions.amount) as total, transactions.service_source_id, transactions.service_id, marcas.descripcion, business_groups.description as group')
            ->join('mt_recibos_cashouts', 'transactions.id', '=', 'mt_recibos_cashouts.transaction_id')
            ->join('mt_recibos', 'mt_recibos.id', '=', 'mt_recibos_cashouts.recibo_id')
            ->join('movements', 'movements.id', '=', 'mt_recibos.movements_id')
            ->join('current_account', 'movements.id', '=', 'current_account.movement_id')
            ->join('business_groups', 'business_groups.id', '=', 'current_account.group_id')
            ->leftJoin('servicios_x_marca', function ($join) {
                $join->on('servicios_x_marca.service_id', '=', 'transactions.service_id');
                $join->on('servicios_x_marca.service_source_id', '=', 'transactions.service_source_id');
            })
            ->leftJoin('marcas', 'marcas.id', '=', 'servicios_x_marca.marca_id')
            ->whereRaw("$whereTransactions")
            ->groupBy('transactions.service_source_id', 'transactions.service_id', 'marcas.descripcion', 'business_groups.description')
            ->get();

        \Log::info(json_decode(json_encode($cashouts), true));

        $details = '';
        foreach ($cashouts as $cashout) {
            if (is_null($cashout->descripcion)) {

                if ($cashout->service_source_id == 0) {
                    $cashout->service_source_id = 9;
                }

                $marca = \DB::table('marcas')
                    ->selectRaw('marcas.id, marcas.descripcion')
                    ->join('servicios_x_marca', 'marcas.id', '=', 'servicios_x_marca.marca_id')
                    ->where('servicios_x_marca.service_id', $cashout->service_id)
                    ->where('servicios_x_marca.service_source_id', $cashout->service_source_id)
                    ->first();

                $cashout->descripcion = $marca->descripcion;
            }

            $details .= '<tr>
                            <td>' . $cashout->descripcion . '</td>
                            <td>' . number_format($cashout->total, 0) . '</td>
                        </tr>';
        }



        return $details;
    }


    /*DMS*/
    public function dmsReports()
    {
        try {
            //Redes
            $whereGroup = "";
            $whereOwner = "";
            $whereBranch = "";
            $wherePos = "";
            $whereAtm = "";

            ///////////////////////////////////////////////////////////////
            if (!\Sentinel::getUser()->inRole('mini_terminal') && !\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                $branches   = Branch::orderBy('description')->where(function ($query) use ($whereBranch) {
                    if (!empty($whereBranch)) {
                        $query->whereRaw($whereBranch);
                    }
                })->get()->pluck('description', 'id');
                $branches->prepend('Todos', '0');
            } else if (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {

                if ($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11) {
                    $whereOwner = "owners.id = " . $this->user->owner_id;
                    $whereBranch = "branches.owner_id = " . $this->user->owner_id;
                    $wherePos = "points_of_sale.owner_id = " . $this->user->owner_id;

                    $supervisor = \DB::table('users_x_groups')->where('user_id', $this->user->id)->first();

                    $branchess = \DB::table('branches')
                        ->select(['branches.description', 'users.username', 'users.id'])
                        ->join('users', 'branches.user_id', '=', 'users.id')
                        ->join('role_users', 'users.id', '=', 'role_users.user_id')
                        ->where('role_users.role_id', 22)
                        ->where('branches.group_id', $supervisor->group_id)
                        ->get();
                    $branches = [];
                    foreach ($branchess as $key => $branch) {
                        $branches[$branch->id] = $branch->description . ' | ' . $branch->username;
                    }
                }
            } else {
                $branches = \DB::table('branches')
                    ->select(['branches.description', 'users.username', 'users.id'])
                    ->join('users', 'branches.user_id', '=', 'users.id')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->where('branches.user_id', $this->user->id)
                    ->get();
            }
            ///////////////////////////////////////////////////////////////

            //Redes
            $atms     = Atmnew::orderBy('atms.name')->where(function ($query) use ($whereAtm) {
                if (!empty($whereAtm)) {
                    $query->whereRaw($whereAtm);
                }
            })->get()->pluck('name', 'id');
            $atms->prepend('Todos', '0');

            $groups     = Group::orderBy('business_groups.description')->where(function ($query) use ($whereGroup) {
                if (!empty($whereGroup)) {
                    $query->whereRaw($whereGroup);
                }
            })->get()->pluck('description', 'id');
            $groups->prepend('Todos', '0');

            $owners     = Owner::orderBy('owners.name')->where(function ($query) use ($whereOwner) {
                if (!empty($whereOwner)) {
                    $query->whereRaw($whereOwner);
                }
            })->get()->pluck('name', 'id');
            $owners->prepend('Todos', '0');

            $pdvs       = Pos::orderBy('description')->where(function ($query) use ($wherePos) {
                if (!empty($wherePos)) {
                    $query->whereRaw($wherePos);
                }
            })->with('Atm')->get();
            $pos = [];
            $item = array();
            $item[0] = 'Todos';

            foreach ($pdvs  as $pdv) {
                $item[$pdv->id] = $pdv->description . ' - ' . $pdv->Atm->code;
                $pos = $item;
            }



            $resultset = array(
                'target'        => 'Dms',
                'atms'          => $atms,
                'groups'        => $groups,
                'owners'        => $owners,
                'branches'      => $branches,
                'pos'           => $pos,
                'atm_id'        => 0,
                'group_id'      => 0,
                'owner_id'      => 0,
                'branch_id'     => 0,
                'pos_id'        => 0,
                'user_id'      => 0,
                'show_alert' => 'NO'
            );

            return $resultset;
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de reportes" . $e);
            return false;
        }
    }

    public function dmsSearch()
    {
        try {

            $input = $this->input;

            $where = " 1=1 and ";
            /*Busqueda minusiosa*/
            if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
                $where .= "t.id = {$input['context']} OR ";
                $where .= "t.referencia_numero_1 = '{$input['context']}' and ";
            } else {

                /*SET DATE RANGE*/
                // $daterange = explode(' - ',  str_replace('/','-',$input['reservationtime']));
                // $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                // $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                // $where .= "a.created_at between '{$daterange[0]}' and '{$daterange[1]}' and ";

                if (isset($input['group_id'])) {
                    $where .= ($input['group_id'] <> 0) ? "business_groups.id = " . $input['group_id'] . " and " : "";
                }

                if (isset($input['owner_id'])) {
                    $where .= ($input['owner_id'] <> 0) ? "branches.owner_id = " . $input['owner_id'] . " and " : "";
                }

                if (isset($input['branch_id'])) {
                    $where .= ($input['branch_id'] <> 0) ? "branches.id = " . $input['branch_id'] . " and " : "";
                }

                if (isset($input['pos_id'])) {
                    $where .= ($input['pos_id'] <> 0) ? "points_of_sale.id = " . $input['pos_id'] . " and " : "";
                }

                if (isset($input['atm_id'])) {
                    $where .= ($input['atm_id'] <> 0) ? "atms.id = " . $input['atm_id'] . " and " : "";
                }
            }

            $where = trim($where);
            $where = trim($where, 'and');
            $where = trim($where);

            \Log::info("$where");
            $caracteristicas = \DB::table('atms')
                ->select(
                    'caracteristicas.id as id',
                    'atms.id as atm_id',
                    'atms.code as cod_punto',
                    'business_groups.description as razon_social',
                    'canal.descripcion as canal_red',
                    'business_groups.ruc as ruc',
                    'business_groups.telefono as telefono_grupo',
                    'caracteristicas.dueño as dueño',
                    'caracteristicas.atendido_por',
                    'branches.more_info as horario',
                    'categorias.descripcion as categoria',
                    'departamento.descripcion as departamento',
                    'ciudades.descripcion as ciudad',
                    'caracteristicas.referencia',
                    'business_groups.direccion as direccion_grupo',
                    'branches.latitud',
                    'branches.longitud',
                    'caracteristicas.accesibilidad',
                    'caracteristicas.visibilidad',
                    'caracteristicas.trafico',
                    'business_groups.created_at as fecha_creacion_grupo',
                    'branches.created_at as fecha_creacion_branches',
                    'business_groups.updated_at as fecha_modificacion_grupo',
                    'branches.updated_at  as fecha_modificacion_branches',
                    'caracteristicas.estado_pop',
                    'caracteristicas.permite_pop',
                    'caracteristicas.tiene_pop',
                    'caracteristicas.tiene_bancard',
                    'caracteristicas.tiene_pronet',
                    'caracteristicas.tiene_netel',
                    'caracteristicas.tiene_pos_dinelco',
                    'caracteristicas.tiene_pos_bancard',
                    'caracteristicas.tiene_billetaje',
                    'caracteristicas.tiene_tm_telefonito',
                    'caracteristicas.visicooler',
                    'caracteristicas.bebidas_alcohol',
                    'caracteristicas.bebidas_gasificadas',
                    'caracteristicas.productos_limpieza'

                )
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'atms.id')
                ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->join('business_groups', 'business_groups.id', '=', 'branches.group_id')
                ->join('barrios', 'barrios.id', '=', 'branches.barrio_id')
                ->join('ciudades', 'ciudades.id', '=', 'barrios.ciudad_id')
                ->join('departamento', 'departamento.id', '=', 'ciudades.departamento_id')
                ->leftJoin('caracteristicas', 'caracteristicas.id', '=', 'branches.caracteristicas_id')
                ->leftJoin('clientes_cuentas_bancarias', 'clientes_cuentas_bancarias.id', '=', 'caracteristicas.cta_bancaria_id')
                ->leftJoin('clientes_bancos', 'clientes_bancos.id', '=', 'clientes_cuentas_bancarias.clientes_tipo_cuenta_id')
                ->leftJoin('categorias', 'categorias.id', '=', 'caracteristicas.categoria_id')
                ->leftJoin('canal', 'canal.id', '=', 'caracteristicas.canal_id')
                ->whereRaw("$where")
                ->orderBy('caracteristicas.id', 'asc')
                ->get();

            //Redes
            $whereGroup = "";
            $whereOwner = "";
            $whereBranch = "";
            $wherePos = "";
            $whereAtm = "";

            $groups     = Group::orderBy('business_groups.description')->where(function ($query) use ($whereGroup) {
                if (!empty($whereGroup)) {
                    $query->whereRaw($whereGroup);
                }
            })->get()->pluck('description', 'id');
            $groups->prepend('Todos', '0');

            $owners     = Owner::orderBy('owners.name')->where(function ($query) use ($whereOwner) {
                if (!empty($whereOwner)) {
                    $query->whereRaw($whereOwner);
                }
            })->get()->pluck('name', 'id');
            $owners->prepend('Todos', '0');

            $branches   = Branch::orderBy('description')->where(function ($query) use ($whereBranch) {
                if (!empty($whereBranch)) {
                    $query->whereRaw($whereBranch);
                }
            })->get()->pluck('description', 'id');
            $branches->prepend('Todos', '0');

            $atms     = Atmnew::orderBy('atms.name')->where(function ($query) use ($whereAtm) {
                if (!empty($whereAtm)) {
                    $query->whereRaw($whereAtm);
                }
            })->get()->pluck('name', 'id');
            $atms->prepend('Todos', '0');


            $pdvs       = Pos::orderBy('description')->where(function ($query) use ($wherePos) {
                if (!empty($wherePos)) {
                    $query->whereRaw($wherePos);
                }
            })->with('Atm')->get();
            $pos = [];
            $item = array();
            $item[0] = 'Todos';
            foreach ($pdvs  as $pdv) {
                $item[$pdv->id] = $pdv->description . ' - ' . $pdv->Atm->code;
                $pos = $item;
            }

            $resultset = array(
                'target'        => 'Dms',
                'groups'        => $groups,
                'owners'        => $owners,
                'branches'      => $branches,
                'pos'           => $pos,
                'atms'          => $atms,

                'caracteristicas'  => $caracteristicas,
                'atm_id'      => (isset($input['atm_id']) ? $input['atm_id'] : 0),
                'group_id'      => (isset($input['group_id']) ? $input['group_id'] : 0),
                'owner_id'      => (isset($input['owner_id']) ? $input['owner_id'] : 0),
                'branch_id'     => (isset($input['branch_id']) ? $input['branch_id'] : 0),
                'pos_id'        => (isset($input['pos_id']) ? $input['pos_id'] : 0),
                'reservationtime' => (isset($input['reservationtime']) ? $input['reservationtime'] : 0),
                'i'             =>  1,
            );
            return $resultset;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }


    public function dmsSearchExport()
    {
        try {
            $input = $this->input;

            $where = " 1=1 and ";
            /*Busqueda minusiosa*/
            if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
                $where .= "t.id = {$input['context']} OR ";
                $where .= "t.referencia_numero_1 = '{$input['context']}' and ";
            } else {

                /*SET DATE RANGE*/
                // $daterange = explode(' - ',  str_replace('/','-',$input['reservationtime']));
                // $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                // $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                // $where .= "a.created_at between '{$daterange[0]}' and '{$daterange[1]}' and ";

                if (isset($input['group_id'])) {
                    $where .= ($input['group_id'] <> 0) ? "business_groups.id = " . $input['group_id'] . " and " : "";
                }

                if (isset($input['owner_id'])) {
                    $where .= ($input['owner_id'] <> 0) ? "branches.owner_id = " . $input['owner_id'] . " and " : "";
                }

                if (isset($input['branch_id'])) {
                    $where .= ($input['branch_id'] <> 0) ? "branches.id = " . $input['branch_id'] . " and " : "";
                }

                if (isset($input['pos_id'])) {
                    $where .= ($input['pos_id'] <> 0) ? "points_of_sale.id = " . $input['pos_id'] . " and " : "";
                }

                if (isset($input['atm_id'])) {
                    $where .= ($input['atm_id'] <> 0) ? "atms.id = " . $input['atm_id'] . " and " : "";
                }
            }

            $where = trim($where);
            $where = trim($where, 'and');
            $where = trim($where);

            \Log::info("$where");
            $caracteristicas = \DB::table('atms')
                ->select(
                    'caracteristicas.id as id',
                    'atms.name as name',
                    'atms.code as cod_punto',
                    'business_groups.description as razon_social',
                    'canal.descripcion as canal_red',
                    'business_groups.ruc as ruc',
                    'business_groups.telefono as telefono_grupo',
                    'caracteristicas.dueño as dueño',
                    'caracteristicas.atendido_por',
                    'branches.more_info as horario',
                    'categorias.descripcion as categoria',
                    'departamento.descripcion as departamento',
                    'ciudades.descripcion as ciudad',
                    'caracteristicas.referencia',
                    'business_groups.direccion as direccion_grupo',
                    'branches.latitud',
                    'branches.longitud',
                    'caracteristicas.accesibilidad',
                    'caracteristicas.visibilidad',
                    'caracteristicas.trafico',
                    'business_groups.created_at as fecha_creacion_grupo',
                    'caracteristicas.estado_pop',
                    'caracteristicas.permite_pop',
                    'caracteristicas.tiene_pop',
                    'caracteristicas.tiene_bancard',
                    'caracteristicas.tiene_pronet',
                    'caracteristicas.tiene_netel',
                    'caracteristicas.tiene_pos_dinelco',
                    'caracteristicas.tiene_pos_bancard',
                    'caracteristicas.tiene_billetaje',
                    'caracteristicas.tiene_tm_telefonito',
                    'caracteristicas.visicooler',
                    'caracteristicas.bebidas_alcohol',
                    'caracteristicas.bebidas_gasificadas',
                    'caracteristicas.productos_limpieza'
                )
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'atms.id')
                ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->join('business_groups', 'business_groups.id', '=', 'branches.group_id')
                ->join('barrios', 'barrios.id', '=', 'branches.barrio_id')
                ->join('ciudades', 'ciudades.id', '=', 'barrios.ciudad_id')
                ->join('departamento', 'departamento.id', '=', 'ciudades.departamento_id')
                ->leftJoin('caracteristicas', 'caracteristicas.id', '=', 'branches.caracteristicas_id')
                ->leftJoin('clientes_cuentas_bancarias', 'clientes_cuentas_bancarias.id', '=', 'caracteristicas.cta_bancaria_id')
                ->leftJoin('clientes_bancos', 'clientes_bancos.id', '=', 'clientes_cuentas_bancarias.clientes_tipo_cuenta_id')
                ->leftJoin('categorias', 'categorias.id', '=', 'caracteristicas.categoria_id')
                ->leftJoin('canal', 'canal.id', '=', 'caracteristicas.canal_id')
                ->whereRaw("$where")
                ->orderBy('caracteristicas.id', 'asc')
                ->get();



            return $caracteristicas;
        } catch (\Exception $e) {
            \Log::info($e);
            return false;
        }
    }

    /**
     * Reportería para Claro
     */

     public function claro_transactionsReports()
     {
 
         $branches = [];
 
         try {
             //Redes
             $whereGroup = "";
             $whereOwner = "";
             $whereBranch = "";
             $wherePos = "points_of_sale.deleted_at is null ";
 
 
             $branches   = Branch::orderBy('description')->where(function ($query) use ($whereBranch) {
                 if (!empty($whereBranch)) {
                     $query->whereRaw($whereBranch);
                 }
             })->get()->pluck('description', 'id');

             $branches->prepend('Todos', '0');
 
             $pdvs = Pos::orderBy('description')
                 ->where(function ($query) use ($wherePos) {
                     if (!empty($wherePos)) {
                         $query->whereRaw($wherePos);
 
                         \Log::info("Filtro de pos: $wherePos");
                     }
                 })
                 ->with('Atm')
                 ->get();
 
             $pos = [];
             $item = array();
             $item[0] = 'Todos';
 
             foreach ($pdvs  as $pdv) {
                 $item[$pdv->id] = $pdv->description . ' - ' . $pdv->Atm->code;
                 $pos = $item;
             }

             $status = array('0' => 'Todos', 'success' => 'Aprobado', 'canceled' => 'Cancelado', 'error' => 'Error', 'rollback' => 'Reversado', 'iniciated' => 'Iniciado', 'error dispositivo' => 'Error de dispositivo', 'error dispositivo' => 'Error de dispositivo', 'devolucion' => 'Devolución', 'inconsistency' => 'Inconsistencia');
             $atmType = array('0' => 'Todos', 'da' => 'App Billetaje', 'ws' => 'Web Service', 'at' => 'Atm');
             $payment_methods = array('0' => 'Todos', 'efectivo' => 'Efectivo', 'canje' => 'Canje', 'QR' => 'Todos QR', 'TC' => 'Tarjeta de crédito', 'TD' => 'Tarjeta de débito');
             $service_item = array();
 
             $services_list = array(
                '0' => 'Todos', 
                '1' => 'Claro Recargas', 
                '3' => 'Giros Claro', 
                '4' => 'Carga Billetera Claro', 
                '5' => 'Extracciones Cash Claro',
                '8' => 'Claro Pago de Facturas'
            );
 
             $resultset = array(
                 'target'        => 'Transacciones',
                 'branches'      => $branches,
                 'pos'           => $pos,
                 'status'        => $status,
                 'type'          => $atmType,
                 'services_data' => $services_list,
                 'payment_methods' => $payment_methods,
                 'group_id'      => 0,
                 'owner_id'      => 0,
                 'branch_id'     => 0,
                 'pos_id'        => 0,
                 'status_set'    => 0,
                 'payment_methods_set' => 0,
                 'type_set'    => 0,
                 'service_id'    => 0,
                 'service_request_id'    => '',
                 'user_id'      => 0,
                 'show_alert' => 'NO',
                 'transaction_id' => null,
                 'search' => false,
                 'owner_claro' => 'on'
             );
 
             return $resultset;

         } catch (\Exception $e) {
             $error_detail = [
                 'from' => 'CMS',
                 'message' => '[transactionsReports] Ocurrió un error al realizar la búsqueda.',
                 'exception' => $e->getMessage(),
                 'file' => $e->getFile(),
                 'class' => __CLASS__,
                 'function' => __FUNCTION__,
                 'line' => $e->getLine(),
                 'user' => [
                     'user_id' => $this->user->id,
                     'username' => $this->user->username,
                     'description' => $this->user->description
                 ]
             ];
 
             \Log::error($error_detail['message'], [$error_detail]);
 
             return false;
         }
     }
 
     public function claro_transactionsSearch()
     {

         try {
 
 
             $query_to_export = '';
             $transactions = [];
             $total_transactions = 0;
             $input = $this->input;

             $where = "t.transaction_type in (1,7,11,12,13, 17) and t.owner_id = 11 and ";

             if (isset($input['owner_claro'])) {
                if ($input['owner_claro'] == 'on') {
                    $atms_ids_claro = \DB::table('atms as a')
                        ->select(
                                \DB::raw("trim(array_to_string(array_agg(a.id), ',')) as ids")
                        )
                        ->whereRaw("a.name ilike '%Claro%'")
                        ->get();

                    $atms_ids_claro = $atms_ids_claro[0]->ids;

                    $where .= "t.atm_id in ($atms_ids_claro) and ";
                }
            }
 
             //$where = "t.transaction_type in (1,7,11,12,13, 17) AND ";

             $transaction_id = null;
 
             if (isset($input['transaction_id'])) {
                 if ($input['transaction_id'] !== null and $input['transaction_id'] !== '') {
                     $transaction_id = $input['transaction_id'];
                     $where .= "t.id = $transaction_id and ";
                 }
             }
 
 
             if ($transaction_id == null) {
                 /*Busqueda minusiosa*/
                 if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
                     $where .= "t.id = {$input['context']} OR ";
                     $where .= "t.referencia_numero_1 = '{$input['context']}' and ";
                 } else {
 
                     /*SET DATE RANGE*/
                     $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                     $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                     $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                     $where .= "t.created_at between '{$daterange[0]}' and '{$daterange[1]}' and ";
 
                     if (isset($input['group_id'])) {
                         $where .= ($input['group_id'] <> 0) ? "b.group_id = " . $input['group_id'] . " and " : "";
                     }
 
                     /*SET OWNER*/
                     $where .= "t.owner_id in (11, 16, 21, 25) and ";
 
                     if (isset($input['payment_method_id'])) {
 
                         if ($input['payment_method_id'] == 'QR') {
                             $where .= "p.tipo_pago in ('TC','TD','DC','QR')";
                         } else {
                             //$where .= ($input['payment_method_id'] <> '0')?'p.tipo_pago = '.$input['payment_method_id'].' and ':'';                        
                             $where .= ($input['payment_method_id'] <> '0') ? "p.tipo_pago = '{$input['payment_method_id']}' and " : "";
                         }
                     }
 
 
                     /**
                      * Filtro para Puntos de Ventas
                      */
 
                     if (isset($input['pos_id'])) {
                         $where .= ($input['pos_id'] <> 0) ? "pos.id = " . $input['pos_id'] . " and " : "";
                     }
 
                     $where .= ($input['status_id'] <> "0") ? "t.status =  '{$input['status_id']}' and " : "";
 
                     if (isset($input['type'])) {
                         $where .= ($input['type'] <> "0") ? "a.type =  '{$input['type']}' and " : "";
                     }
 
                     if ($input['service_request_id'] <> '0' and $input['service_request_id'] <> '8') {
                         // Cualquier servicio menos el Claro Pago de factura
                         $where .= "(t.service_source_id = 8 and t.service_id = " . $input['service_request_id'] . ") ";
                     } else if ($input['service_request_id'] == '8') {
                         // Claro Pago de factura
                         $where .= "(t.service_source_id = 10 and t.service_id = 8) ";
                     } else {
                         // Todos los servicios de claro y Claro Pago de factura
                         $where .= "((t.service_source_id = 8 and t.service_id in (1, 3, 4, 5)) or (t.service_source_id = 10 and t.service_id = 8)) ";
                     }
                 }
             }
 
 
             $where = trim($where);
             $where = trim($where, 'and');
             
             $where = trim($where);
 
             \Log::info("$where");
 
             $transactions = \DB::table('transactions as t')
                 ->select(
                     't.id',
                     \DB::raw("trim(replace(to_char(t.amount, '999G999G999G999'), ',', '.')) as amount"),
                     't.service_id',
                     'service_request_id',
                     't.atm_transaction_id',
                     't.status',
                     't.status as estado',
                     't.status_description',
                     't.status_description as estado_descripcion',
                     't.identificador_transaction_id',
                     't.factura_numero',
                     \DB::raw("to_char(t.created_at, 'DD/MM/YYYY HH24:MI:SS') as created_at"),
                     \DB::raw("to_char(t.created_at, 'DD/MM/YYYY') as fecha"),
                     \DB::raw("to_char(t.created_at, 'HH24:MI:SS') as hora"),
                     't.amount as valor_transaccion',
                     \DB::raw("0 as commission_amount"),
                     'p.id as cod_pago',
                     'p.tipo_pago as forma_pago',
                     'p.valor_a_pagar',
                     'p.valor_recibido',
                     'valor_entregado',
                     't.identificador_transaction_id as identificador_transaccion',
                     't.factura_numero as factura_nro',
                     'pos.description as sede',
                     'o.name as owner_id',
                     'referencia_numero_1',
                     'referencia_numero_2',
                     'referencia_numero_1 as ref1',
                     'referencia_numero_2 as ref2',
                     'a.code as codigo_cajero',
                     'a.code as code',
                     't.service_source_id',
                     'sp.name as provider',
                     'spp.description as servicio',
                     \DB::raw("
                          (case when (t.service_source_id <> 0) then
                              (select sps.description 
                              from services_providers_sources sps 
                              where sps.id = t.service_source_id 
                              limit 1)
                          else 
                              sp.name
                          end) as proveedor
                      "),
                     \DB::raw("
                          (case when (t.service_source_id <> 0) then
                              case when (t.service_source_id = 8) then
                                  (select sop.service_description 
                                  from services_ondanet_pairing sop 
                                  where sop.service_request_id = t.service_id 
                                  and sop.service_source_id = t.service_source_id 
                                  limit 1)
                              else 
                                  (select sop.service_description 
                                  from services_ondanet_pairing sop 
                                  where sop.service_request_id = t.service_id 
                                  and sop.service_source_id = t.service_source_id 
                                  limit 1)
                              end
                          else 
                              spp.description
                          end) as tipo
                      "),
                     'mtr.reversion_id'
                 )
                 ->join('points_of_sale as pos', 'pos.atm_id', '=', 't.atm_id')
                 ->join('atms as a', 'a.id', '=', 't.atm_id')
                 ->join('owners as o', 'o.id', '=', 't.owner_id')
                 ->leftjoin('service_provider_products as spp', 'spp.id', '=', 't.service_id')
                 ->leftjoin('service_providers as sp', 'sp.id', '=', 'spp.service_provider_id')
                 ->leftjoin('transactions_x_payments as txp', 't.id', '=', 'txp.transactions_id')
                 ->leftjoin('payments as p', 'p.id', '=', 'txp.payments_id')
                 ->leftjoin('branches as b', 'b.id', '=', 'pos.branch_id')
                 ->leftjoin('mt_recibos_reversiones as mtr', 't.id', '=', 'mtr.transaction_id')
                 ->whereRaw("$where")
                 ->orderBy('t.id', 'desc');
 
 
             if (isset($input['reservationtime'])) {
                 $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                 $to = date('Y-m-d H:i:s', strtotime($daterange[0]));
                 $from = date('Y-m-d H:i:s', strtotime($daterange[1]));
                 $days = \DB::select("select ('{$from}'::date - '{$to}'::date) + 1 as days");
                 $days = $days[0]->days;
             } else {
                 $days = 0;
             }
 
 
             if ($days > 1 and $transaction_id == null) {
                 $query_to_export = $transactions->toSql();
                 $transactions = [];
                 $total_transactions = [];
                 $transactions_total = 0;
             } else {
                 $transactions = $transactions->paginate(20);
                 $transactions_total = $transactions->total();
 
                 $total_transactions = \DB::table('transactions as t')
                     ->select(
                         \DB::raw("
                                  trim(
                                      replace(
                                          to_char(sum(abs(t.amount)), '999G999G999G999G999'), ',', '.'
                                      )
                                  ) as monto
                              ")
                     )
                     ->join('points_of_sale as pos', 'pos.atm_id', '=', 't.atm_id')
                     ->join('atms as a', 'a.id', '=', 't.atm_id')
                     ->leftjoin('transactions_x_payments as txp', 't.id', '=', 'txp.transactions_id')
                     ->leftjoin('payments as p', 'p.id', '=', 'txp.payments_id')
                     ->leftjoin('branches as b', 'b.id', '=', 'pos.branch_id')
                     ->whereRaw("$where");
 
                 //\Log::info('SQL:');
                 //\Log::info($total_transactions->toSql());
 
                 $total_transactions = $total_transactions->get();
 
                 if (count($total_transactions) > 0) {
                     $total_transactions = $total_transactions[0]->monto;
                 } else {
                     $total_transactions = 0;
                 }
             }
 
 

             /*Carga datos del formulario*/
             $whereGroup = "";
             $whereOwner = "";
             $whereBranch = "";
             $wherePos = "points_of_sale.atm_id is not null ";
 
             if (isset($input['branch_id'])) {
                 if ($input['branch_id'] !== null and $input['branch_id'] !== '0') {
                     $wherePos .= "and points_of_sale.branch_id = " . $input['branch_id'] . " ";
                 }
             }
 
             //Grupos
             $groups = Group::orderBy('business_groups.description')->where(function ($query) use ($whereGroup) {
                 if (!empty($whereGroup)) {
                     $query->whereRaw($whereGroup);
                 }
             })->get()->pluck('description', 'id');
             $groups->prepend('Todos', '0');
 
            //Redes
             $owners = Owner::orderBy('owners.name')->where(function ($query) use ($whereOwner) {
                 if (!empty($whereOwner)) {
                     $query->whereRaw($whereOwner);
                 }
             })->get()->pluck('name', 'id');
             $owners->prepend('Todos', '0');
 
            //Sucursales
             $branches = Branch::orderBy('description')->where(function ($query) use ($whereBranch) {
                 if (!empty($whereBranch)) {
                     $query->whereRaw($whereBranch);
                 }
             })->get()->pluck('description', 'id');
             $branches->prepend('Todos', '0');
 
            //Punto de Venta
             $pdvs = Pos::orderBy('description')->where(function ($query) use ($wherePos) {
                 if (!empty($wherePos)) {
                     $query->whereRaw($wherePos);
                 }
             })->with('Atm')->get();

             $pos = [];
             $item = array();
             $item[0] = 'Todos';
             foreach ($pdvs  as $pdv) {
                 $item[$pdv->id] = $pdv->description . ' - ' . $pdv->Atm->code;
                 $pos = $item;
             }
 
             $status = array('0' => 'Todos', 'success' => 'Aprobado', 'canceled' => 'Cancelado', 'error' => 'Error', 'rollback' => 'Reversado', 'iniciated' => 'Iniciado', 'error dispositivo' => 'Error de dispositivo', 'devolucion' => 'Devolución', 'inconsistency' => 'Inconsistencia');
             $atmType = array('0' => 'Todos', 'da' => 'App Billetaje', 'ws' => 'Web Service', 'at' => 'Atm');
             $payment_methods = array('0' => 'Todos', 'efectivo' => 'Efectivo', 'canje' => 'Canje', 'QR' => 'Todos QR', 'TC' => 'Tarjeta de crédito', 'TD' => 'Tarjeta de débito');
             
             $services_list = array(
                '0' => 'Todos', 
                '1' => 'Claro Recargas', 
                '3' => 'Giros Claro', 
                '4' => 'Carga Billetera Claro', 
                '5' => 'Extracciones Cash Claro',
                '8' => 'Claro Pago de Facturas'
            );
 
             $resultset = array(
                 'target'        => 'Transacciones',
                 'groups'        => $groups,
                 'owners'        => $owners,
                 'branches'      => $branches,
                 'payment_methods' => $payment_methods,
                 'pos'           => $pos,
                 'status'        => $status,
                 'type'          => $atmType,
                 'services_data' => $services_list,
                 'transactions'  => $transactions,
                 'total_transactions'  => $total_transactions,
                 'group_id'      => (isset($input['group_id']) ? $input['group_id'] : 0),
                 'owner_id'      => (isset($input['owner_id']) ? $input['owner_id'] : 0),
                 'branch_id'     => (isset($input['branch_id']) ? $input['branch_id'] : 0),
                 'pos_id'        => (isset($input['pos_id']) ? $input['pos_id'] : 0),
                 'status_set'    => (isset($input['status_id']) ? $input['status_id'] : 0),
                 'payment_methods_set' => (isset($input['payment_method_id']) ? $input['payment_method_id'] : 0),
                 'type_set'    => (isset($input['type']) ? $input['type'] : 0),
                 'service_id'    => (isset($input['service_id']) ? $input['service_id'] : 0),
                 'reservationtime' => (isset($input['reservationtime']) ? $input['reservationtime'] : 0),
                 'i'             =>  1,
                 'service_request_id' => (isset($input['service_request_id']) ? $input['service_request_id'] : 0),
                 'query_to_export' => $query_to_export,
                 'transactions_total' => $transactions_total,
 
                 //Nuevos filtros
                 'transaction_id' => (isset($input['transaction_id']) ? $input['transaction_id'] : null),
                 'amount' => (isset($input['amount']) ? $input['amount'] : null),
 
                 //Para mostrar un mensaje al usuario.
                 'search' => true,
                 'owner_claro' => (isset($input['owner_claro']) ? 'on' : '')
             );
 
             //\Log::info('pos:', [$pos]);
             //\Log::info("wherePos: $wherePos");
             //die();
 
             return $resultset;
         } catch (\Exception $e) {
             $error_detail = [
                 'from' => 'CMS',
                 'message' => '[claro_transactionsSearch] Ocurrió un error la querer realizar una búsqueda en CLARO.',
                 'exception' => $e->getMessage(),
                 'file' => $e->getFile(),
                 'class' => __CLASS__,
                 'function' => __FUNCTION__,
                 'line' => $e->getLine(),
                 'user' => [
                     'user_id' => $this->user->id,
                     'username' => $this->user->username,
                     'description' => $this->user->description
                 ]
             ];
 
             \Log::error($error_detail['message'], [$error_detail]);
 
             return false;
         }
     }
 
 
     public function claro_transactionsSearchExport()
     {
         try {
             $input = $this->input;

             $where = "t.transaction_type in (1,7,11,12,13,17) and t.owner_id = 11 and ";

             if (isset($input['owner_claro'])) {
                if ($input['owner_claro'] == 'on') {
                    $atms_ids_claro = \DB::table('atms as a')
                        ->select(
                                \DB::raw("trim(array_to_string(array_agg(a.id), ',')) as ids")
                        )
                        ->whereRaw("a.name ilike '%Claro%'")
                        ->get();

                    $atms_ids_claro = $atms_ids_claro[0]->ids;

                    $where .= "t.atm_id in ($atms_ids_claro) and ";
                }
            }

             $transaction_id = null;
 
             if (isset($input['transaction_id'])) {
                 if ($input['transaction_id'] !== null and $input['transaction_id'] !== '') {
                     $transaction_id = $input['transaction_id'];
                     $where .= "t.id = $transaction_id and ";
                 }
             }
 
 
             if ($transaction_id == null) {
 
                 /*Busqueda minusiosa*/
                 if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
                     $where .= "t.id = {$input['context']} or ";
                     $where .= "t.referencia_numero_1 = '{$input['context']}' and ";
                 } else {
                     /*SET DATE RANGE*/
                     $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                     $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                     $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                     $where .= "t.created_at between '{$daterange[0]}' and '{$daterange[1]}' and ";
 
                     if (isset($input['group_id'])) {
                         $where .= ($input['group_id'] <> 0) ? "b.group_id = " . $input['group_id'] . " and " : "";
                     }
 
                     /*SET OWNER*/
                     $where .= "t.owner_id in (11, 16, 21, 25) and ";
 
                     if (isset($input['branch_id'])) {
                         $where .= ($input['branch_id'] <> 0) ? "pos.branch_id = " . $input['branch_id'] . " and " : "";
                     }
 
                     if (isset($input['pos_id'])) {
                         $where .= ($input['pos_id'] <> 0) ? "pos.id = " . $input['pos_id'] . " and " : "";
                     }
 
                     $where .= ($input['status_id'] <> "0") ? "t.status =  '{$input['status_id']}' and " : "";
 
                     if (isset($input['type'])) {
                         $where .= ($input['type'] <> "0") ? "a.type =  '{$input['type']}' and " : "";
                     }
 
                     if (isset($input['payment_method_id'])) {
                         if ($input['payment_method_id'] == 'QR') {
                             $where .= "p.tipo_pago in ('TC','TD','DC','QR')";
                         } else {
                             $where .= ($input['payment_method_id'] <> '0') ? "p.tipo_pago = '{$input['payment_method_id']}' and " : "";
                         }
                     }
 
 
                     /*if ($input['service_request_id'] <> "0") {
                         $where .= "t.service_id = " . $input['service_request_id'] . " and service_source_id = 8";
                     } else {
                         $where .= "t.service_id in (1, 3, 4, 5) and service_source_id = 8";
                     }*/

                    if ($input['service_request_id'] <> '0' and $input['service_request_id'] <> '8') {
                        // Cualquier servicio menos el Claro Pago de factura
                        $where .= "(t.service_source_id = 8 and t.service_id = " . $input['service_request_id'] . ") ";
                    } else if ($input['service_request_id'] == '8') {
                        // Claro Pago de factura
                        $where .= "(t.service_source_id = 10 and t.service_id = 8) ";
                    } else {
                        // Todos los servicios de claro y Claro Pago de factura
                        //$where .= "(t.service_source_id = 8 and t.service_id in (1, 3, 4, 5)) and (t.service_source_id = 10 and t.service_id = 8) ";

                        $where .= "((t.service_source_id = 8 and t.service_id in (1, 3, 4, 5)) or (t.service_source_id = 10 and t.service_id = 8)) ";
                    }
                 }
             }
 
             $where = trim($where);
             $where = trim($where, 'and');
             $where = trim($where);
 
             $transactions = \DB::table('transactions as t')
                 ->select(
                     't.id',
                     \DB::raw("trim(replace(to_char(t.amount, '999G999G999G999'), ',', '.')) as amount"),
                     't.service_id',
                     'service_request_id',
                     't.atm_transaction_id',
                     't.status',
                     't.status as estado',
                     't.status_description',
                     't.status_description as estado_descripcion',
                     't.identificador_transaction_id',
                     't.factura_numero',
                     \DB::raw("to_char(t.created_at, 'DD/MM/YYYY HH24:MI:SS') as created_at"),
                     \DB::raw("to_char(t.created_at, 'DD/MM/YYYY') as fecha"),
                     \DB::raw("to_char(t.created_at, 'HH24:MI:SS') as hora"),
                     /*\DB::raw(
                          "
                          trim(replace(to_char((
                              case when (t.amount < 0) 
                              then t.amount * -1 
                              else t.amount end), '999G999G999G999'
                          ), ',', '.')) as valor_transaccion"
                      ),*/
                     't.amount as valor_transaccion',
                     \DB::raw("0 as commission_amount"),
                     'p.id as cod_pago',
                     'p.tipo_pago as forma_pago',
                     'p.valor_a_pagar',
                     'p.valor_recibido',
                     'valor_entregado',
                     't.identificador_transaction_id as identificador_transaccion',
                     't.factura_numero as factura_nro',
                     'pos.description as sede',
                     /*\DB::raw("
                          (case 
                          when (t.owner_id = 2) then 'Antell'
                          when (t.owner_id = 11) then 'Eglobal' 
                          else t.owner_id::text end) as owner_id
                      "),*/
                     //'o.name as owner_id',
                     \DB::raw("(select o.name from owners o where o.id = t.owner_id) as owner_id"),
                     'referencia_numero_1',
                     'referencia_numero_2',
                     'referencia_numero_1 as ref1',
                     'referencia_numero_2 as ref2',
                     'a.code as codigo_cajero',
                     'a.code as code',
                     't.service_source_id',
                     'sp.name as provider',
                     'spp.description as servicio',
                     \DB::raw("
                          (case when (t.service_source_id <> 0) then
                              (select sps.description 
                              from services_providers_sources sps 
                              where sps.id = t.service_source_id 
                              limit 1)
                          else 
                              sp.name
                          end) as proveedor
                      "),
                     \DB::raw("
                          (case when (t.service_source_id <> 0) then
                              case when (t.service_source_id = 8) then
                                  (select sop.service_description 
                                  from services_ondanet_pairing sop 
                                  where sop.service_request_id = t.service_id 
                                  and sop.service_source_id = t.service_source_id 
                                  limit 1)
                              else 
                                  (select sop.service_description 
                                  from services_ondanet_pairing sop 
                                  where sop.service_request_id = t.service_id 
                                  and sop.service_source_id = t.service_source_id 
                                  limit 1)
                              end
                          else 
                              spp.description
                          end) as tipo
                      ")
                 )
                 ->join('points_of_sale as pos', 'pos.atm_id', '=', 't.atm_id')
                 ->join('atms as a', 'a.id', '=', 't.atm_id')
                 //->leftjoin('owners as o', 'o.id', '=', 't.owner_id')
                 ->leftjoin('service_provider_products as spp', 'spp.id', '=', 't.service_id')
                 ->leftjoin('service_providers as sp', 'sp.id', '=', 'spp.service_provider_id')
                 ->leftjoin('transactions_x_payments as txp', 't.id', '=', 'txp.transactions_id')
                 ->leftjoin('payments as p', 'p.id', '=', 'txp.payments_id')
                 ->leftjoin('branches as b', 'b.id', '=', 'pos.branch_id')
                 ->whereRaw("$where")
                 ->orderBy('cod_pago', 'desc')
                 ->orderBy('t.created_at', 'desc');
 
             $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
             $to = date('Y-m-d H:i:s', strtotime($daterange[0]));
             $from = date('Y-m-d H:i:s', strtotime($daterange[1]));
             $days = \DB::select("select ('{$from}'::date - '{$to}'::date) + 1 as days");
             \Log::info('[Exportar reporte]', ['días' => $days]);
             $days = $days[0]->days;
 
             if ($days > 1 and $transaction_id == null) {
                 $transactions = trim($transactions->toSql());
             } else {
                 $transactions = $transactions->get();
             }
 
             return $transactions;
         } catch (\Exception $e) {
             $error_detail = [
                 'from' => 'CMS',
                 'message' => '[claro_transactionsSearchExport] Ocurrió un error la querer buscar y exportar transacciones en.',
                 'exception' => $e->getMessage(),
                 'file' => $e->getFile(),
                 'class' => __CLASS__,
                 'function' => __FUNCTION__,
                 'line' => $e->getLine(),
                 'user' => [
                     'user_id' => $this->user->id,
                     'username' => $this->user->username,
                     'description' => $this->user->description
                 ]
             ];
 
             \Log::error($error_detail['message'], [$error_detail]);
 
             return false;
         }
     }
     
}
