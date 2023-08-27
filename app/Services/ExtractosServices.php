<?php
/**
 * User: jportillo
 * Date: 22/07/22
 * Time: 02:13 PM
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

class ExtractosServices
{
    protected $input;

    public function __construct($var){
        $this->input= $var;
        ini_set('memory_limit', '1024M');
        $this->user = \Sentinel::getUser();
    }

    public function arrayPaginator($array, $request)
    {
        $page = $request->input('page', 1);
        $perPage = 20;
        $offset = ($page * $perPage) - $perPage;

        return new \Illuminate\Pagination\LengthAwarePaginator(array_slice($array, $offset, $perPage, true), count($array), $perPage, $page,
            ['path' => $request->url(), 'query' => $request->query()]);
    }

    public function estadoContableReports(){
        try{

            $resultset = array(
                'target' => 'Estado Contable',
                'mostrar' => 'todos'
            );

            if(!\Sentinel::getUser()->inRole('mini_terminal') && !\Sentinel::getUser()->inRole('supervisor_miniterminal')){
                 
                $atms = \DB::table('atms')
                    ->selectRaw('atms.id as atm_id, atms.name, bg.*')
                    ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                    ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                    ->join('business_groups as bg', 'bg.id', '=', 'branches.group_id')
                    ->whereIn('atms.owner_id', [16, 21, 25])
                    ->whereNull('bg.deleted_at')
                ->get();

                $data_select = [];
                /*foreach ($branches as $key => $branch) {
                    $data_select[$branch->user_id] = $branch->description.' | '.$usersNames[$branch->user_id];
                }*/

                foreach ($atms as $key => $atm) {
                    $data_select[$atm->atm_id] = $atm->name.' | '.$atm->ruc . ' | ' .$atm->description;
                }
                
                $resultset['data_select'] = $data_select;
                $resultset['atm_id'] = '';
            }

            if(\Sentinel::getUser()->inRole('supervisor_miniterminal')){
                $supervisor = \DB::table('users_x_groups')->where('user_id',$this->user->id)->first();

                $atms = \DB::table('atms')
                    ->selectRaw('atms.id as atm_id, atms.name, bg.*')
                    ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                    ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                    ->join('business_groups as bg', 'bg.id', '=', 'branches.group_id')
                    ->where('bg.id', $supervisor->group_id)
                    ->whereIn('atms.owner_id', [16, 21, 25])
                    ->whereNull('bg.deleted_at')
                ->get();

                $data_select = [];

                foreach ($atms as $key => $atm) {
                    $data_select[$atm->atm_id] = $atm->name.' | '.$atm->ruc . ' | ' .$atm->description;
                }
                
                $resultset['data_select'] = $data_select;

                $resultset['atm_id'] = '';
            }

            if(\Sentinel::getUser()->inRole('mini_terminal')){

                $atms = \DB::table('atms')
                    ->selectRaw('DISTINCT atms.id as atm_id, atms.name, bg.*')
                    ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                    ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                    ->join('business_groups as bg', 'bg.id', '=', 'branches.group_id')
                    ->join('atms_per_users', 'atms.id', '=', 'atms_per_users.atm_id')
                    ->where('atms_per_users.user_id', $this->user->id)
                    ->whereIn('atms.owner_id', [16, 21, 25])
                    ->whereNull('bg.deleted_at')
                ->get();

                $data_select = [];

                foreach ($atms as $key => $atm) {
                    $data_select[$atm->atm_id] = $atm->name.' | '.$atm->ruc . ' | ' .$atm->description;
                }
                
                $resultset['data_select'] = $data_select;

                $resultset['atm_id'] = '';
            }
            $resultset['activar_resumen'] = '';
            return $resultset;

        }catch (\Exception $e){
            \Log::error("Error en la consulta de reportes" . $e);
            return false;
        }
    }

    public function estadoContableSearch($request){
        try{
            $input = $this->input;
            //dd($input);
            $bloqueo_diario = false;
            /*Busqueda minusiosa*/
            /*SET DATE RANGE*/
            if(isset($input['reservationtime']) && $input['reservationtime'] != '0'){
                $daterange = explode(' - ',  str_replace('/','-',$input['reservationtime']));
                $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
            }

            /*SET OWNER*/
            //if(!\Sentinel::getUser()->inRole('mini_terminal')){

            $group = \DB::table('business_groups')
                ->select('business_groups.id')
                ->join('branches', 'business_groups.id', '=', 'branches.group_id')
                ->join('points_of_sale', 'branches.id', '=', 'points_of_sale.branch_id')
                ->where('points_of_sale.atm_id' ,'=', $input['atm_id'])
            ->first();

            $owners = \DB::table('atms')
                    ->selectRaw('atms.owner_id, atms.id, atms.grilla_tradicional')
                    ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                    ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                    ->join('business_groups', 'business_groups.id', '=', 'branches.group_id')
                    ->where('business_groups.id' ,'=', $group->id)
                    ->whereNull('atms.deleted_at')
                    ->whereNotNull('atms.last_token')
                    ->whereIn('atms.owner_id',[16, 21, 25])
            ->get();
            
            $atm_id = $input['atm_id'];

            if(in_array(21,  array_column($owners, 'owner_id')) || in_array(25,  array_column($owners, 'owner_id')) || in_array(false,  array_column($owners, 'grilla_tradicional'))){
                $bloqueo_diario = true;
            }

            $baseQuery = [];
            $haber = 0;
            $debe = 0;
            //dd($input);
            if($atm_id <> '()'){
                if(isset($input['reservationtime']) ){

                    $fecha_hasta = Carbon::parse(date('Y-m-d H:i:s', strtotime($daterange[0])))->modify('-1 seconds');

                    $last_balance_mensual=\DB::table('balance_mensual_atms')
                            ->selectRaw('balance_mensual_atms.*')
                            ->where('atm_id' ,'=', $atm_id)
                            ->where('fecha_hasta' ,'<=', $fecha_hasta)
                            ->orderBy('id', 'DESC')
                    ->first();

                    if(isset($last_balance_mensual)){
                        $desde=Carbon::parse(date('Y-m-d H:i:s', strtotime($last_balance_mensual->fecha_hasta)))->modify('+1 seconds');
                        $debe_antes = $last_balance_mensual->total_transaccionado;
                        $haber_antes = $last_balance_mensual->total_pagado;
                        $reversado_antes = $last_balance_mensual->total_reversado;
                        $cashout_antes = $last_balance_mensual->total_cashout;
                        $pago_qr_antes = $last_balance_mensual->total_pago_qr;
                    }else{
                        $desde=date('2020-01-01 00:00:00');
                        $debe_antes = 0;
                        $haber_antes = 0;
                        $reversado_antes = 0;
                        $cashout_antes = 0;
                        $pago_qr_antes = 0;
                    }

                    $total_debe = \DB::connection('eglobalt_replica')->select("
                        select
                            SUM(
                                CASE 
                                    WHEN status = 'success' and t.amount >= 0 and t.service_id not in(100) THEN 
                                        abs(t.amount)
                                    WHEN status = 'error' and t.service_id in(14, 15) and t.service_source_id=8 and t.amount >= 0 THEN 
                                        abs(t.amount)
                                    else
                                        0
                                END
                            ) as total
                        from
                            transactions t
                        where
                            atm_id in (".$atm_id.")
                            and t.transaction_type in(1, 12, 13)
                            and t.created_at BETWEEN '{$desde}' AND '{$fecha_hasta}'
                    ");

                    $total_pago_cashout = \DB::connection('eglobalt_replica')->table('mt_movements as m')
                        ->selectRaw('sum(amount) as total')
                        ->join('mt_sales as s', 'm.id', '=', 's.movements_id')
                        ->whereRaw("
                            s.fecha BETWEEN '{$desde}' AND '{$fecha_hasta}'
                            and m.atm_id = (".$atm_id.") and
                            m.movement_type_id in (12) and
                            m.deleted_at is null
                    ")->first();
    
                    $total_haber = \DB::connection('eglobalt_replica')->table('mt_movements as m')
                        ->selectRaw('sum(amount) as total_haber')
                        ->whereRaw("
                            m.movement_type_id = 2 and
                            m.deleted_at is null and
                            created_at BETWEEN '{$desde}' AND '{$fecha_hasta}'
                            and atm_id in (".$atm_id.")
                    ")->first();
                    
                    $total_reversion = \DB::connection('eglobalt_replica')->table('mt_movements as m')
                        ->selectRaw('sum(amount) as total')
                        ->whereRaw("
                            m.movement_type_id = 3 and
                            m.deleted_at is null and
                            created_at BETWEEN '{$desde}' AND '{$fecha_hasta}'
                            and atm_id in (".$atm_id.")
                    ")->first();

                    $total_cashout = \DB::connection('eglobalt_replica')->table('mt_movements as m')
                        ->selectRaw('sum(amount) as total')
                        ->whereRaw("
                            m.movement_type_id = 11 and
                            m.deleted_at is null and
                            created_at BETWEEN '{$desde}' AND '{$fecha_hasta}'
                            and atm_id in (".$atm_id.")
                    ")->first();

                    $total_pago_qr = \DB::connection('eglobalt_replica')->table('mt_movements as m')
                        ->selectRaw('sum(amount) as total')
                        ->whereRaw("
                            m.movement_type_id = 17 and
                            m.deleted_at is null and
                            created_at BETWEEN '{$desde}' AND '{$fecha_hasta}'
                            and atm_id in (".$atm_id.")
                    ")->first();
                        
                    $haber = $haber_antes + $total_haber->total_haber;
                    $debe = $debe_antes + $total_debe[0]->total + $total_pago_cashout->total;
                    $reversion = $reversado_antes + $total_reversion->total;
                    $cashout = $cashout_antes + $total_cashout->total;
                    $pago_qr = $pago_qr_antes + $total_pago_qr->total;

                    $total_saldo = $haber + $debe + $reversion + $cashout + $pago_qr;

                    if($total_saldo >= 0){
                        $debe_total=$total_saldo;
                        $haber_total=0;
                    }else{
                        $debe_total=0;
                        $haber_total=$total_saldo;
                    }
                    
                    switch ($input['mostrar']) {
                        case 'todos':
                            $baseQuery = \DB::connection('eglobalt_replica')->select("
                            with balances as (
                                select 
                                    '{$daterange[0]}' as fecha, 
                                    'Balance Anterior' as concepto, $debe_total as debe, $haber_total as haber
                            union
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
                                        CASE 
                                            when t.amount > 0 then
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
                                    atm_id in ($atm_id)
                                    and status = 'success'
                                    and t.transaction_type in (1, 7, 12, 13)
                                    and t.service_id not in(100)
                                )or(
                                    atm_id in ($atm_id)
                                    and status = 'error'
                                    and t.service_id in(14, 15)
                                    and t.service_source_id=8
                                    and t.transaction_type in (1, 7)
                                )   
                            union
                                select
                                    bd.fecha,
                                    concat('Boleta Depósito Nro.',' ', bd.boleta_numero , ' | ', bancos.descripcion),
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
                                    atm_id = ($atm_id)
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
                                    transactions on transactions.id=miniterminales_payments_x_atms.transaction_id
                                where
                                    miniterminales_payments_x_atms.atm_id in ($atm_id) and transactions.status = 'success'
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
                                where
                                    atm_id in ($atm_id)
                            union
                                select
                                    m.created_at as fecha,
                                    concat('Pago a Cliente'),
                                    abs(m.amount) as debe,
                                    0 as haber
                                from
                                    mt_movements as m
                                where
                                    atm_id in ($atm_id) and m.movement_type_id in(12) 
                                    and m.deleted_at is null
                            union
                                select
                                    m.created_at as fecha,
                                    concat('Pago QR transacción #', mqr.transaction_id),
                                    0 as debe,
                                    -abs(m.amount) as haber
                                from
                                    mt_movements as m
                                    inner join mt_recibos mr on m.id = mr.mt_movements_id
                                    inner join mt_recibos_qr mqr on mr.id = mqr.recibo_id
                                where
                                    atm_id in ($atm_id) and m.movement_type_id in(17) 
                                    and m.deleted_at is null
                            union
                                select
                                    m.created_at as fecha,
                                    concat('Reversion de transaccion ',' ', mr.transaction_id),
                                    0 as debe,
                                    -abs(m.amount) as haber
                                from
                                    mt_recibos_reversiones mr
                                inner join 
                                    mt_recibos mt on mt.id = mr.recibo_id
                                inner join 
                                    mt_movements m on m.id = mt.mt_movements_id
                                where
                                    atm_id in ($atm_id)
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
                            ");
                            break;

                        case 'depositos':
                            $baseQuery = \DB::connection('eglobalt_replica')->select("
                            with balances as (
                                select 
                                    '{$daterange[0]}' as fecha, 
                                    'Balance Anterior' as concepto, $debe_total as debe, $haber_total as haber
                            union
                                select
                                    bd.fecha,
                                    concat('Boleta Depósito Nro.',' ', bd.boleta_numero , ' | ', bancos.descripcion),
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
                                    atm_id = ($atm_id)
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
                                    transactions on transactions.id=miniterminales_payments_x_atms.transaction_id
                                where
                                    miniterminales_payments_x_atms.atm_id in ($atm_id) and transactions.status = 'success'
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
                                where
                                    atm_id in ($atm_id)
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
                            ");
                            break;

                        case 'transacciones':
                            $baseQuery = \DB::connection('eglobalt_replica')->select("
                            with balances as (
                                select 
                                    '{$daterange[0]}' as fecha, 
                                    'Balance Anterior' as concepto, $debe_total as debe, $haber_total as haber
                            union
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
                                    atm_id in ($atm_id)
                                    and status = 'success'
                                    and t.transaction_type in (1, 7, 12, 13)
                                    and t.service_id not in(100)
                                )or(
                                    atm_id in ($atm_id)
                                    and status = 'error'
                                    and t.service_id in(14, 15)
                                    and t.service_source_id=8
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
                            ");
                            break;

                        case 'reversiones':
                            $baseQuery = \DB::connection('eglobalt_replica')->select("
                            with balances as (
                                select 
                                    '{$daterange[0]}' as fecha, 
                                    'Balance Anterior' as concepto, $debe_total as debe, $haber_total as haber
                            union
                                select
                                    m.created_at as fecha,
                                    concat('Reversion de transaccion ',' ', mr.transaction_id),
                                    0 as debe,
                                    -abs(m.amount) as haber
                                from
                                    mt_recibos_reversiones mr
                                inner join 
                                    mt_recibos mt on mt.id = mr.recibo_id
                                inner join 
                                    mt_movements m on m.id = mt.mt_movements_id
                                where
                                    atm_id in ($atm_id)
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
                            ");
                            break;
                        default:
                            $baseQuery = [];
                            break;
                    }

                    $total_debe = \DB::connection('eglobalt_replica')->select("
                        select
                            SUM(
                                CASE 
                                    WHEN status = 'success' and t.amount >= 0 and t.service_id not in(100) THEN
                                        abs(t.amount)
                                    WHEN status = 'error' and t.service_id in(14, 15) and t.service_source_id=8 and t.amount >= 0 THEN 
                                        abs(t.amount)
                                    else
                                        0
                                END
                            ) as total
                        from
                            transactions t
                        where
                            atm_id in (".$atm_id.")
                            and t.transaction_type in(1, 12, 13)
                            and t.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'
                    ");
    
                    $total_haber = \DB::connection('eglobalt_replica')->table('mt_movements as m')
                        ->selectRaw('sum(amount) as total_haber')
                        ->whereRaw("
                            m.movement_type_id = 2 and
                            m.deleted_at is null and
                            created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'
                            and atm_id in (".$atm_id.")
                    ")->first();
                    
                    $total_reversion = \DB::connection('eglobalt_replica')->table('mt_movements as m')
                        ->selectRaw('sum(amount) as total')
                        ->whereRaw("
                            m.movement_type_id = 3 and
                            m.deleted_at is null and
                            created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'
                            and atm_id in (".$atm_id.")
                    ")->first();

                    $total_cashout = \DB::connection('eglobalt_replica')->table('mt_movements as m')
                        ->selectRaw('sum(amount) as total')
                        ->whereRaw("
                            m.movement_type_id = 11 and
                            m.deleted_at is null and
                            created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'
                            and atm_id in (".$atm_id.")
                    ")->first();

                    $total_pago_cashout = \DB::connection('eglobalt_replica')->table('mt_movements as m')
                        ->selectRaw('sum(amount) as total')
                        ->join('mt_sales as s', 'm.id', '=', 's.movements_id')
                        ->whereRaw("
                            s.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'
                            and m.atm_id = (".$atm_id.") and
                            m.movement_type_id in (12) and
                            m.deleted_at is null
                    ")->first();

                    $total_pago_qr = \DB::connection('eglobalt_replica')->table('mt_movements as m')
                        ->selectRaw('sum(amount) as total')
                        ->whereRaw("
                            m.movement_type_id = 17 and
                            m.deleted_at is null and
                            created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'
                            and atm_id in (".$atm_id.")
                    ")->first();

                    //dd($total_cashout);

                    $haber = $haber + $total_haber->total_haber;
                    $debe = $debe + $total_debe[0]->total + $total_pago_cashout->total;
                    $reversion = $reversion + $total_reversion->total;
                    $cashout = $cashout + $total_cashout->total;
                    $pago_qr = $pago_qr + $total_pago_qr->total;

                    $input['activar_resumen']='';
                }else{
                    if( $input['reservationtime'] = '0'){

                        $input['activar_resumen']='';

                        $fecha_actual = date('Y-m-d H:i:s');
                        $hasta = date('Y-m-d H:i:s');
                    }else{
                        if($input['activar_resumen'] ==2){
                            $date=date('N');
    
                            if( $bloqueo_diario ){
                                $hasta=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-1 days');
                            }else{
                                if($date == 1 || $date==3 ||$date==5){
                                    $hasta=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-1 days');
                                }else if($date == 2 || $date==4 ||$date==6){
                                    $hasta=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-2 days');
                                }else{
                                    $hasta=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-3 days');
                                }
                            }
    
                        }else{
                            $hasta = date('Y-m-d H:i:s');
                        }
                        $fecha_actual = date('Y-m-d H:i:s');
                    }

                    $last_balance_mensual=\DB::table('balance_mensual_atms')
                            ->selectRaw('balance_mensual_atms.*')
                            ->where('atm_id' ,'=', $atm_id)
                            ->orderBy('id', 'DESC')
                    ->first();
                    
                    if(isset($last_balance_mensual)){
                        $desde=Carbon::parse(date('Y-m-d 00:00:00', strtotime($last_balance_mensual->fecha_hasta)))->modify('+1 days');
                        $debe_antes = $last_balance_mensual->total_transaccionado;
                        $haber_antes = $last_balance_mensual->total_pagado;
                        $reversado_antes = $last_balance_mensual->total_reversado;
                        $cashout_antes = $last_balance_mensual->total_cashout;
                        //$pago_cashout_antes = $last_balance_mensual->total_cashout;
                        $pago_qr_antes = $last_balance_mensual->total_pago_qr;
                    }else{
                        $desde=date('2020-01-01 00:00:00');
                        $debe_antes = 0;
                        $haber_antes = 0;
                        $reversado_antes = 0;
                        $cashout_antes = 0;
                        $pago_qr_antes = 0;
                    }

                    $total_debe = \DB::connection('eglobalt_replica')->select("
                        select
                            SUM(
                                CASE 
                                    WHEN status = 'success' and t.amount >= 0 and t.service_id not in(100) THEN 
                                        abs(t.amount)
                                    WHEN status = 'error' and t.service_id in(14, 15) and t.service_source_id=8 and t.amount >= 0 THEN 
                                        abs(t.amount)
                                    else
                                        0
                                END
                            ) as total
                        from
                            transactions t
                        where
                            atm_id in (".$atm_id.")
                            and t.transaction_type in (1, 12, 13)
                            and t.created_at BETWEEN '{$desde}' AND '{$hasta}'
                    ");
    
                    $total_haber = \DB::connection('eglobalt_replica')->table('mt_movements as m')
                        ->selectRaw('sum(amount) as total_haber')
                        ->whereRaw("
                            m.movement_type_id = 2 and
                            m.deleted_at is null and
                            created_at BETWEEN '{$desde}' AND '{$fecha_actual}'
                            and atm_id in (".$atm_id.")
                    ")->first();

                    $total_pago_cashout = \DB::connection('eglobalt_replica')->table('mt_movements as m')
                        ->selectRaw('sum(amount) as total')
                        ->join('mt_sales as s', 'm.id', '=', 's.movements_id')
                        ->whereRaw("
                            s.fecha BETWEEN '{$desde}' AND '{$fecha_actual}'
                            and m.atm_id = (".$atm_id.") and
                            m.movement_type_id in (12) and
                            m.deleted_at is null
                    ")->first();
                    
                    $total_reversion = \DB::connection('eglobalt_replica')->table('mt_movements as m')
                        ->selectRaw('sum(amount) as total')
                        ->whereRaw("
                            m.movement_type_id = 3 and
                            m.deleted_at is null and
                            created_at BETWEEN '{$desde}' AND '{$fecha_actual}'
                            and atm_id in (".$atm_id.")
                    ")->first();

                    $total_cashout = \DB::connection('eglobalt_replica')->table('mt_movements as m')
                        ->selectRaw('sum(amount) as total')
                        ->whereRaw("
                            m.movement_type_id = 11 and
                            m.deleted_at is null and
                            created_at BETWEEN '{$desde}' AND '{$fecha_actual}'
                            and atm_id in (".$atm_id.")
                    ")->first();

                    $total_pago_qr = \DB::connection('eglobalt_replica')->table('mt_movements as m')
                        ->selectRaw('sum(amount) as total')
                        ->whereRaw("
                            m.movement_type_id = 17 and
                            m.deleted_at is null and
                            created_at BETWEEN '{$desde}' AND '{$fecha_actual}'
                            and atm_id in (".$atm_id.")
                    ")->first();
                        
                    $haber = $haber_antes + $total_haber->total_haber;
                    $debe = $debe_antes + $total_debe[0]->total + $total_pago_cashout->total;
                    $reversion = $reversado_antes + $total_reversion->total;
                    $cashout = $cashout_antes + $total_cashout->total;
                    $pago_qr = $pago_qr_antes + $total_pago_qr->total;

                    $baseQuery = [];
                }
            }     

            $total_saldo = $haber + $debe + $reversion + $cashout + $pago_qr;
            $results = $this->arrayPaginator($baseQuery, $request);

            $resultset = array(
                'target'        => 'Estado Contable',
                'transactions'  => $results,
                'reservationtime' => (isset($input['reservationtime'])?$input['reservationtime']:0),
                'i'             =>  1,
                'total_debe' => number_format($debe),
                'total_haber' => number_format($haber),
                'total_reversion' => number_format($reversion),
                'total_cashout' => number_format($cashout),
                'total_pago_qr' => number_format($pago_qr),
                'total_saldo' => number_format($total_saldo),
                'mostrar' => $input['mostrar'],
                'activar_resumen' => $input['activar_resumen'],
                'atm_id'   => $input['atm_id']
            );
            
            if(!\Sentinel::getUser()->inRole('mini_terminal') && !\Sentinel::getUser()->inRole('supervisor_miniterminal')){

                $atms = \DB::table('atms')
                    ->selectRaw('atms.id as atm_id, atms.name, bg.*')
                    ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                    ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                    ->join('business_groups as bg', 'bg.id', '=', 'branches.group_id')
                    ->whereIn('atms.owner_id', [16, 21, 25])
                    ->whereNull('bg.deleted_at')
                ->get();

                $data_select = [];

                foreach ($atms as $key => $atm) {
                    $data_select[$atm->atm_id] = $atm->name.' | '.$atm->ruc . ' | ' .$atm->description;
                }

                $resultset['data_select'] = $data_select;
                $resultset['atm_id'] = $input['atm_id'];
            }

            if(\Sentinel::getUser()->inRole('supervisor_miniterminal')){
                $supervisor = \DB::table('users_x_groups')->where('user_id',$this->user->id)->first();

                $atms = \DB::table('atms')
                    ->selectRaw('atms.id as atm_id, atms.name, bg.*')
                    ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                    ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                    ->join('business_groups as bg', 'bg.id', '=', 'branches.group_id')
                    ->where('bg.id', $supervisor->group_id)
                    ->whereIn('atms.owner_id', [16, 21, 25])
                    ->whereNull('bg.deleted_at')
                ->get();

                $data_select = [];

                foreach ($atms as $key => $atm) {
                    $data_select[$atm->atm_id] = $atm->name.' | '.$atm->ruc . ' | ' .$atm->description;
                }
                
                $resultset['data_select'] = $data_select;

                $resultset['atm_id'] = $input['atm_id'];
            }

            if(\Sentinel::getUser()->inRole('mini_terminal')){

                $atms = \DB::table('atms')
                    ->selectRaw('DISTINCT atms.id as atm_id, atms.name, bg.*')
                    ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                    ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                    ->join('business_groups as bg', 'bg.id', '=', 'branches.group_id')
                    ->join('atms_per_users', 'atms.id', '=', 'atms_per_users.atm_id')
                    ->where('atms_per_users.user_id', $this->user->id)
                    ->whereIn('atms.owner_id', [16, 21, 25])
                    ->whereNull('bg.deleted_at')
                ->get();

                $data_select = [];

                foreach ($atms as $key => $atm) {
                    $data_select[$atm->atm_id] = $atm->name.' | '.$atm->ruc . ' | ' .$atm->description;
                }
                
                $resultset['data_select'] = $data_select;

                $resultset['atm_id'] = $input['atm_id'];
            }

            return $resultset;

        }catch (\Exception $e){
            \Log::info($e);
                return false;
        }
    }

    public function estadoContableSearchExport(){
        try{
            $input = $this->input;
            $bloqueo_diario = false;
            /*Busqueda minusiosa*/
            /*SET DATE RANGE*/
            if(isset($input['reservationtime']) && $input['reservationtime'] != '0'){
                $daterange = explode(' - ',  str_replace('/','-',$input['reservationtime']));
                $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
            }

            /*SET OWNER*/
            //if(!\Sentinel::getUser()->inRole('mini_terminal')){
            $group = \DB::table('business_groups')
                ->select('business_groups.id')
                ->join('branches', 'business_groups.id', '=', 'branches.group_id')
                ->join('points_of_sale', 'branches.id', '=', 'points_of_sale.branch_id')
                ->where('points_of_sale.atm_id' ,'=', $input['atm_id'])
            ->first();

            $owners = \DB::table('atms')
                    ->selectRaw('atms.owner_id, atms.id, atms.grilla_tradicional')
                    ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                    ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                    ->join('business_groups', 'business_groups.id', '=', 'branches.group_id')
                    ->where('business_groups.id' ,'=', $group->id)
                    ->whereNull('atms.deleted_at')
                    ->whereNotNull('atms.last_token')
                    ->whereIn('atms.owner_id',[16, 21, 25])
            ->get();
            
            $atm_id = $input['atm_id'];

            if(in_array(21,  array_column($owners, 'owner_id')) || in_array(25,  array_column($owners, 'owner_id')) || in_array(false,  array_column($owners, 'grilla_tradicional'))){
                $bloqueo_diario = true;
            }

            $baseQuery = [];
            $haber = 0;
            $debe = 0;
            if($atm_id <> '()'){
                if(isset($input['reservationtime']) ){

                    $fecha_hasta = Carbon::parse(date('Y-m-d H:i:s', strtotime($daterange[0])))->modify('-1 seconds');

                    $last_balance_mensual=\DB::table('balance_mensual_atms')
                            ->selectRaw('balance_mensual_atms.*')
                            ->where('atm_id' ,'=', $atm_id)
                            ->where('fecha_hasta' ,'<=', $fecha_hasta)
                            ->orderBy('id', 'DESC')
                    ->first();

                    if(isset($last_balance_mensual)){
                        $desde=Carbon::parse(date('Y-m-d H:i:s', strtotime($last_balance_mensual->fecha_hasta)))->modify('+1 seconds');
                        $debe_antes = $last_balance_mensual->total_transaccionado;
                        $haber_antes = $last_balance_mensual->total_pagado;
                        $reversado_antes = $last_balance_mensual->total_reversado;
                        $cashout_antes = $last_balance_mensual->total_cashout;
                        $pago_qr_antes = $last_balance_mensual->total_pago_qr;
                    }else{
                        $desde=date('2020-01-01 00:00:00');
                        $debe_antes = 0;
                        $haber_antes = 0;
                        $reversado_antes = 0;
                        $cashout_antes = 0;
                        $pago_qr_antes = 0;
                    }

                    $total_debe = \DB::connection('eglobalt_replica')->select("
                        select
                            SUM(
                                CASE 
                                    WHEN status = 'success' and t.amount >= 0 and t.service_id not in(100) THEN 
                                        abs(t.amount)
                                    WHEN status = 'error' and t.service_id in(14, 15) and t.amount >= 0 THEN 
                                        abs(t.amount)
                                    else
                                        0
                                END
                            ) as total
                        from
                            transactions t
                        where
                            atm_id in (".$atm_id.")
                            and t.transaction_type in (1, 12, 13)
                            and t.created_at BETWEEN '{$desde}' AND '{$fecha_hasta}'
                    ");
    
                    $total_haber = \DB::connection('eglobalt_replica')->table('mt_movements as m')
                        ->selectRaw('sum(amount) as total_haber')
                        ->whereRaw("
                            m.movement_type_id = 2 and
                            m.deleted_at is null and
                            created_at BETWEEN '{$desde}' AND '{$fecha_hasta}'
                            and atm_id in (".$atm_id.")
                    ")->first();
                    
                    $total_reversion = \DB::connection('eglobalt_replica')->table('mt_movements as m')
                        ->selectRaw('sum(amount) as total')
                        ->whereRaw("
                            m.movement_type_id = 3 and
                            m.deleted_at is null and
                            created_at BETWEEN '{$desde}' AND '{$fecha_hasta}'
                            and atm_id in (".$atm_id.")
                    ")->first();

                    $total_cashout = \DB::connection('eglobalt_replica')->table('mt_movements as m')
                        ->selectRaw('sum(amount) as total')
                        ->whereRaw("
                            m.movement_type_id = 11 and
                            m.deleted_at is null and
                            created_at BETWEEN '{$desde}' AND '{$fecha_hasta}'
                            and atm_id in (".$atm_id.")
                    ")->first();

                    $total_pago_cashout = \DB::connection('eglobalt_replica')->table('mt_movements as m')
                        ->selectRaw('sum(amount) as total')
                        ->join('mt_sales as s', 'm.id', '=', 's.movements_id')
                        ->whereRaw("
                            s.fecha BETWEEN '{$desde}' AND '{$fecha_hasta}'
                            and m.atm_id = (".$atm_id.") and
                            m.movement_type_id in (12) and
                            m.deleted_at is null
                    ")->first();

                    $total_pago_qr = \DB::connection('eglobalt_replica')->table('mt_movements as m')
                        ->selectRaw('sum(amount) as total')
                        ->whereRaw("
                            m.movement_type_id = 17 and
                            m.deleted_at is null and
                            created_at BETWEEN '{$desde}' AND '{$fecha_hasta}'
                            and atm_id in (".$atm_id.")
                    ")->first();
                        
                    $haber = $haber_antes + $total_haber->total_haber;
                    $debe = $debe_antes + $total_debe[0]->total + $total_pago_cashout->total;;
                    $reversion = $reversado_antes + $total_reversion->total;
                    $cashout = $cashout_antes + $total_cashout->total;
                    $pago_qr = $pago_qr_antes + $total_pago_qr->total;

                    $total_saldo = $haber + $debe + $reversion + $cashout + $pago_qr;

                    if($total_saldo >= 0){
                        $debe_total=$total_saldo;
                        $haber_total=0;
                    }else{
                        $debe_total=0;
                        $haber_total=$total_saldo;
                    }
                    
                    switch ($input['mostrar']) {
                        case 'todos':
                            $baseQuery = \DB::connection('eglobalt_replica')->select("
                            with balances as (
                                select 
                                    '{$daterange[0]}' as fecha, 
                                    'Balance Anterior' as concepto, $debe_total as debe, $haber_total as haber
                            union
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
                                    atm_id in ($atm_id)
                                    and status = 'success'
                                    and t.transaction_type in (1, 7, 12, 13)
                                    and t.service_id not in(100)
                                )or(
                                    atm_id in ($atm_id)
                                    and status = 'error'
                                    and t.service_id in(14, 15)
                                    and t.transaction_type in (1, 7)
                                )   
                            union
                                select
                                    bd.fecha,
                                    concat('Boleta Depósito Nro.',' ', bd.boleta_numero , ' | ', bancos.descripcion),
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
                                    atm_id = ($atm_id)
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
                                    transactions on transactions.id=miniterminales_payments_x_atms.transaction_id
                                where
                                    miniterminales_payments_x_atms.atm_id in ($atm_id) and transactions.status='success'
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
                                where
                                    atm_id in ($atm_id)
                            union
                                select
                                    m.created_at as fecha,
                                    concat('Pago a Cliente'),
                                    abs(m.amount) as debe,
                                    0 as haber
                                from
                                    mt_movements as m
                                where
                                    atm_id in ($atm_id) and m.movement_type_id in(12) 
                                    and m.deleted_at is null
                            union
                                select
                                    m.created_at as fecha,
                                    concat('Pago QR transacción #', mqr.transaction_id),
                                    0 as debe,
                                    -abs(m.amount) as haber
                                from
                                    mt_movements as m
                                    inner join mt_recibos mr on m.id = mr.mt_movements_id
                                    inner join mt_recibos_qr mqr on mr.id = mqr.recibo_id
                                where
                                    atm_id in ($atm_id) and m.movement_type_id in(17) 
                                    and m.deleted_at is null
                            union
                                select
                                    m.created_at as fecha,
                                    concat('Reversion de transaccion ',' ', mr.transaction_id),
                                    0 as debe,
                                    -abs(m.amount) as haber
                                from
                                    mt_recibos_reversiones mr
                                inner join 
                                    mt_recibos mt on mt.id = mr.recibo_id
                                inner join 
                                    mt_movements m on m.id = mt.mt_movements_id
                                where
                                    atm_id in ($atm_id)
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
                            ");
                            break;

                        case 'depositos':
                            $baseQuery = \DB::connection('eglobalt_replica')->select("
                            with balances as (
                                select 
                                    '{$daterange[0]}' as fecha, 
                                    'Balance Anterior' as concepto, $debe_total as debe, $haber_total as haber
                            union
                                select
                                    bd.fecha,
                                    concat('Boleta Depósito Nro.',' ', bd.boleta_numero , ' | ', bancos.descripcion),
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
                                    atm_id = ($atm_id)
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
                                    transactions on transactions.id=miniterminales_payments_x_atms.transaction_id
                                where
                                    miniterminales_payments_x_atms.atm_id in ($atm_id) and transactions.status='success'
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
                                where
                                    atm_id in ($atm_id)
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
                            ");
                            break;

                        case 'transacciones':
                            $baseQuery = \DB::connection('eglobalt_replica')->select("
                            with balances as (
                                select 
                                    '{$daterange[0]}' as fecha, 
                                    'Balance Anterior' as concepto, $debe_total as debe, $haber_total as haber
                            union
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
                                    atm_id in ($atm_id)
                                    and status = 'success'
                                    and t.transaction_type in (1, 7, 12, 13)
                                    and t.service_id not in(100)
                                )or(
                                    atm_id in ($atm_id)
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
                            ");
                            break;

                        case 'reversiones':
                            $baseQuery = \DB::connection('eglobalt_replica')->select("
                            with balances as (
                                select 
                                    '{$daterange[0]}' as fecha, 
                                    'Balance Anterior' as concepto, $debe_total as debe, $haber_total as haber
                            union
                                select
                                    m.created_at as fecha,
                                    concat('Reversion de transaccion ',' ', mr.transaction_id),
                                    0 as debe,
                                    -abs(m.amount) as haber
                                from
                                    mt_recibos_reversiones mr
                                inner join 
                                    mt_recibos mt on mt.id = mr.recibo_id
                                inner join 
                                    mt_movements m on m.id = mt.mt_movements_id
                                where
                                    atm_id in ($atm_id)
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
                            ");
                            break;
                        default:
                            $baseQuery = [];
                            break;
                    }

                    $total_debe = \DB::connection('eglobalt_replica')->select("
                        select
                            SUM(
                                CASE 
                                    WHEN status = 'success' and t.amount >= 0 and t.service_id not in(100) THEN 
                                        abs(t.amount)
                                    WHEN status = 'error' and t.service_id in(14, 15) and t.amount >= 0 THEN 
                                        abs(t.amount)
                                    else
                                        0
                                END
                            ) as total
                        from
                            transactions t
                        where
                            atm_id in (".$atm_id.")
                            and t.transaction_type in(1, 12, 13)
                            and t.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'
                    ");
    
                    $total_haber = \DB::connection('eglobalt_replica')->table('mt_movements as m')
                        ->selectRaw('sum(amount) as total_haber')
                        ->whereRaw("
                            m.movement_type_id = 2 and
                            m.deleted_at is null and
                            created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'
                            and atm_id in (".$atm_id.")
                    ")->first();
                    
                    $total_reversion = \DB::connection('eglobalt_replica')->table('mt_movements as m')
                        ->selectRaw('sum(amount) as total')
                        ->whereRaw("
                            m.movement_type_id = 3 and
                            m.deleted_at is null and
                            created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'
                            and atm_id in (".$atm_id.")
                    ")->first();

                    $total_cashout = \DB::connection('eglobalt_replica')->table('mt_movements as m')
                        ->selectRaw('sum(amount) as total')
                        ->whereRaw("
                            m.movement_type_id = 11 and
                            m.deleted_at is null and
                            created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'
                            and atm_id in (".$atm_id.")
                    ")->first();

                    $total_pago_cashout = \DB::connection('eglobalt_replica')->table('mt_movements as m')
                        ->selectRaw('sum(amount) as total')
                        ->join('mt_sales as s', 'm.id', '=', 's.movements_id')
                        ->whereRaw("
                            s.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'
                            and m.atm_id = (".$atm_id.") and
                            m.movement_type_id in (12) and
                            m.deleted_at is null
                    ")->first();

                    $total_pago_qr = \DB::connection('eglobalt_replica')->table('mt_movements as m')
                        ->selectRaw('sum(amount) as total')
                        ->whereRaw("
                            m.movement_type_id = 17 and
                            m.deleted_at is null and
                            created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'
                            and atm_id in (".$atm_id.")
                    ")->first();

                    $haber = $haber + $total_haber->total_haber;
                    $debe = $debe + $total_debe[0]->total + $total_pago_cashout->total;
                    $reversion = $reversion + $total_reversion->total;
                    $cashout = $cashout + $total_cashout->total;
                    $pago_qr = $pago_qr + $total_pago_qr->total;

                    $input['activar_resumen']='';
                }else{
                    if( $input['reservationtime'] = '0'){

                        $input['activar_resumen']='';

                        $fecha_actual = date('Y-m-d H:i:s');
                        $hasta = date('Y-m-d H:i:s');
                    }else{
                        if($input['activar_resumen'] ==2){
                            $date=date('N');
    
                            if( $bloqueo_diario ){
                                $hasta=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-1 days');
                            }else{
                                if($date == 1 || $date==3 ||$date==5){
                                    $hasta=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-1 days');
                                }else if($date == 2 || $date==4 ||$date==6){
                                    $hasta=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-2 days');
                                }else{
                                    $hasta=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-3 days');
                                }
                            }
    
                        }else{
                            $hasta = date('Y-m-d H:i:s');
                        }
                        $fecha_actual = date('Y-m-d H:i:s');
                    }

                    $last_balance_mensual=\DB::table('balance_mensual_atms')
                            ->selectRaw('balance_mensual_atms.*')
                            ->where('atm_id' ,'=', $atm_id)
                            ->orderBy('id', 'DESC')
                    ->first();
                    
                    if(isset($last_balance_mensual)){
                        $desde=Carbon::parse(date('Y-m-d 00:00:00', strtotime($last_balance_mensual->fecha_hasta)))->modify('+1 days');
                        $debe_antes = $last_balance_mensual->total_transaccionado;
                        $haber_antes = $last_balance_mensual->total_pagado;
                        $reversado_antes = $last_balance_mensual->total_reversado;
                        $cashout_antes = $last_balance_mensual->total_cashout;
                        $pago_qr_antes = $last_balance_mensual->total_pago_qr;
                    }else{
                        $desde=date('2020-01-01 00:00:00');
                        $debe_antes = 0;
                        $haber_antes = 0;
                        $reversado_antes = 0;
                        $cashout_antes = 0;
                        $pago_qr_antes = 0;
                    }

                    $total_debe = \DB::connection('eglobalt_replica')->select("
                        select
                            SUM(
                                CASE 
                                    WHEN status = 'success' and t.amount >= 0 and t.service_id not in(100) THEN 
                                        abs(t.amount)
                                    WHEN status = 'error' and t.service_id in(14, 15) and t.amount >= 0 THEN 
                                        abs(t.amount)
                                    else
                                        0
                                END
                            ) as total
                        from
                            transactions t
                        where
                            atm_id in (".$atm_id.")
                            and t.transaction_type = 1
                            and t.created_at BETWEEN '{$desde}' AND '{$hasta}'
                    ");
    
                    $total_haber = \DB::connection('eglobalt_replica')->table('mt_movements as m')
                        ->selectRaw('sum(amount) as total_haber')
                        ->whereRaw("
                            m.movement_type_id = 2 and
                            m.deleted_at is null and
                            created_at BETWEEN '{$desde}' AND '{$fecha_actual}'
                            and atm_id in (".$atm_id.")
                    ")->first();

                    $total_pago_cashout = \DB::connection('eglobalt_replica')->table('mt_movements as m')
                        ->selectRaw('sum(amount) as total')
                        ->join('mt_sales as s', 'm.id', '=', 's.movements_id')
                        ->whereRaw("
                            s.fecha BETWEEN '{$desde}' AND '{$fecha_actual}'
                            and m.atm_id = (".$atm_id.") and
                            m.movement_type_id in (12) and
                            m.deleted_at is null
                    ")->first();
                    
                    $total_reversion = \DB::connection('eglobalt_replica')->table('mt_movements as m')
                        ->selectRaw('sum(amount) as total')
                        ->whereRaw("
                            m.movement_type_id = 3 and
                            m.deleted_at is null and
                            created_at BETWEEN '{$desde}' AND '{$fecha_actual}'
                            and atm_id in (".$atm_id.")
                    ")->first();

                    $total_cashout = \DB::connection('eglobalt_replica')->table('mt_movements as m')
                        ->selectRaw('sum(amount) as total')
                        ->whereRaw("
                            m.movement_type_id = 11 and
                            m.deleted_at is null and
                            created_at BETWEEN '{$desde}' AND '{$fecha_actual}'
                            and atm_id in (".$atm_id.")
                    ")->first();

                    $total_pago_qr = \DB::connection('eglobalt_replica')->table('mt_movements as m')
                        ->selectRaw('sum(amount) as total')
                        ->whereRaw("
                            m.movement_type_id = 17 and
                            m.deleted_at is null and
                            created_at BETWEEN '{$desde}' AND '{$fecha_actual}'
                            and atm_id in (".$atm_id.")
                    ")->first();
                        
                    $haber = $haber_antes + $total_haber->total_haber;
                    $debe = $debe_antes + $total_debe[0]->total + $total_pago_cashout->total;
                    $reversion = $reversado_antes + $total_reversion->total;
                    $cashout = $cashout_antes + $total_cashout->total;
                    $pago_qr = $pago_qr_antes + $total_pago_qr->total;

                    $baseQuery = [];
                }
            }     

            $total_saldo = $haber + $debe + $reversion + $cashout + $pago_qr;

            //$results = $this->arrayPaginator($baseQuery, $request);


            $resultset = array(
                'transactions'  => $baseQuery,
                'total_debe' => number_format($debe),
                'total_haber' => number_format($haber),
                'total_saldo' => number_format($total_saldo),
                'mostrar' => $input['mostrar']
            );


            if(!\Sentinel::getUser()->inRole('mini_terminal') && !\Sentinel::getUser()->inRole('supervisor_miniterminal')){

                $atms = \DB::table('atms')
                    ->selectRaw('atms.id as atm_id, atms.name, bg.*')
                    ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                    ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                    ->join('business_groups as bg', 'bg.id', '=', 'branches.group_id')
                    ->whereIn('atms.owner_id', [16, 21, 25])
                    ->whereNull('bg.deleted_at')
                ->get();

                $data_select = [];

                foreach ($atms as $key => $atm) {
                    $data_select[$atm->atm_id] = $atm->name.' | '.$atm->ruc . ' | ' .$atm->description;
                }

                $resultset['data_select'] = $data_select;
                $resultset['atm_id'] = $input['atm_id'];
            }

            if(\Sentinel::getUser()->inRole('supervisor_miniterminal')){
                $supervisor = \DB::table('users_x_groups')->where('user_id',$this->user->id)->first();

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
                    $data_select[$branch->user_id] = $branch->description.' | '.$usersNames[$branch->user_id];
                }

                $resultset['usersNames'] = $usersNames;
                $resultset['branches'] = $branches;
                $resultset['data_select'] = $data_select;
                $resultset['user_id'] = '';
            }

            return $resultset;

        }catch (\Exception $e){
            \Log::info($e);
                return false;
        }
    }

    public function resumenMiniterminalesReports($request){
        try{

            $resultset = array(
                'target' => 'Resumen Mini Terminales',
            );  
            

            if(!\Sentinel::getUser()->inRole('mini_terminal') && !\Sentinel::getUser()->inRole('supervisor_miniterminal')){                
                $date=date('N');

                $whereCobranzas = "WHEN movement_type_id = 2 AND m.created_at <= now() ";
                $whereReversion = "WHEN movement_type_id = 3 AND m.created_at <= now() ";
                $whereCashout = "WHEN movement_type_id = 11 AND m.created_at <= now() ";
                $wherePagoQr = "WHEN movement_type_id = 17 AND m.created_at <= now() ";

                if($date == 1 || $date==3 ||$date==5){
                    $hasta_mini=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-1 days');
                }else if($date == 2 || $date==4 ||$date==6){
                    $hasta_mini=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-2 days');
                }else{
                    $hasta_mini=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-3 days');
                }

                $hasta_nano=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-1 days');

                $whereSalesNano = "WHEN debit_credit = 'de' AND (a.owners LIKE '%21%' OR a.owners LIKE '%25%' OR a.grilla != 'true') AND mt_sales.fecha <= '". $hasta_nano . "'";
                $whereSalesMini = "WHEN debit_credit = 'de' AND (a.owners NOT LIKE '%21%' AND a.owners NOT LIKE '%25%' AND a.grilla = 'true') AND mt_sales.fecha <= '". $hasta_mini . "'";

                $resumen_transacciones_groups = \DB::connection('eglobalt_replica')->select("
                    select
                        bg.id as group_id,
                        concat(bg.description,' | ',bg.ruc) as grupo,
                        a.owners,
                        SUM(
                            CASE ".$whereSalesNano." THEN (m.amount) 
                            ".$whereSalesMini." THEN (m.amount) 
                            else 0 END
                        ) as transacciones,
                        SUM(CASE ".$whereCobranzas." THEN (m.amount) else 0 END) as depositos,
                        SUM(CASE ".$whereReversion." THEN (m.amount) else 0 END) as reversiones,
                        SUM(CASE ".$whereCashout." THEN (m.amount) else 0 END) as cashouts,
                        SUM(CASE ".$wherePagoQr." THEN (m.amount) else 0 END) as pago_qr,
                        (   (SUM(
                            CASE ".$whereSalesNano." THEN (m.amount) 
                            ".$whereSalesMini." THEN (m.amount) 
                            else 0 END
                            ))
                            +(SUM(CASE ".$whereCobranzas." THEN (m.amount) else 0 END))
                            +(SUM(CASE ".$whereReversion." THEN (m.amount) else 0 END))
                            +(SUM(CASE ".$whereCashout." THEN (m.amount) else 0 END))
                            +(SUM(CASE ".$wherePagoQr." THEN (m.amount) else 0 END))
                            + (Case when cuota_a.saldo_alquiler != 0 then cuota_a.saldo_alquiler else 0 end)
                            + (Case when cuota_v.saldo_venta != 0 then cuota_v.saldo_venta else 0 end)
                        ) as saldo,
                        (Case when cuota_a.saldo_alquiler != 0 then cuota_a.saldo_alquiler else 0 end)
                        + (Case when cuota_v.saldo_venta != 0 then cuota_v.saldo_venta else 0 end) as cuotas,
                        a.blocks,
                        a.deleted
                        from mt_movements m
                        inner join business_groups bg on bg.id = m.group_id
                        left join mt_sales on m.id = mt_sales.movements_id
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
                        ) a on a.grupo_id = bg.id
                        left join (
                            select sum(saldo_cuota) as saldo_alquiler, group_id 
                            from alquiler
                            Inner join cuotas_alquiler on alquiler.id = cuotas_alquiler.alquiler_id
                            Inner join alquiler_housing on alquiler.id = alquiler_housing.alquiler_id
                            where fecha_vencimiento < now() and saldo_cuota <> 0 and alquiler.deleted_at is null and cod_venta is not null
                            group by group_id order by group_id
                        ) cuota_a on bg.id = cuota_a.group_id
                        left join (
                            select sum(saldo_cuota) as saldo_venta, group_id 
                            from venta
                            Inner join cuotas on venta.id = cuotas.credito_venta_id
                            Inner join venta_housing on venta.id = venta_housing.venta_id
                            where fecha_vencimiento < now() and saldo_cuota <> 0 and venta.deleted_at is null and cod_venta is not null
                            group by group_id order by group_id
                        ) cuota_v on bg.id = cuota_v.group_id
                        where
                            m.movement_type_id not in (4, 5, 8, 9, 10)
                            and m.deleted_at is null
                    group by bg.id, grupo, a.owners, a.blocks, a.deleted, cuota_a.saldo_alquiler, cuota_v.saldo_venta
                ");

                foreach($resumen_transacciones_groups as $resumen_transaccion_group){

                    if(str_contains($resumen_transaccion_group->deleted, 'online')){
                        if( str_contains($resumen_transaccion_group->blocks, '1') || 
                        str_contains($resumen_transaccion_group->blocks, '3') || 
                        str_contains($resumen_transaccion_group->blocks, '5') || 
                        str_contains($resumen_transaccion_group->blocks, '7') )
                        {
                            $resumen_transaccion_group->estado='bloqueado';
                        }else{
                            $resumen_transaccion_group->estado='activo';
                        }
                    }else{
                        $resumen_transaccion_group->estado='inactivo';
                    }  

                }

                $resultset['total_debe_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'transacciones')));
                $resultset['total_haber_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'depositos')));
                $resultset['total_reversion_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'reversiones')));
                $resultset['total_cashout_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'cashouts')));
                $resultset['total_pago_qr_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'pago_qr')));
                $resultset['total_cuota_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'cuotas')));
                $resultset['total_saldo_groups'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'saldo')));

                //$resultset['transactions_groups'] = $results_groups;

                $resultset['transactions_groups'] = $resumen_transacciones_groups;
                $resultset['reservationtime'] = (isset($input['reservationtime'])?$input['reservationtime']:0);
                

            }else if(\Sentinel::getUser()->inRole('supervisor_miniterminal')){                
                
                $supervisor = \DB::table('users_x_groups')->where('user_id',$this->user->id)->first();                
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
                    $data_select[$branch->user_id] = $branch->description.' | '.$usersNames[$branch->user_id];
                }
              
                $resultset['usersNames'] = $usersNames;
                $resultset['branches'] = $branches;
                $resultset['data_select'] = $data_select;
                $resultset['user_id'] = '';
            }else if(\Sentinel::getUser()->inRole('mini_terminal')){                
                
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
                    $data_select[$branch->user_id] = $branch->description.' | '.$usersNames[$branch->user_id];
                }

                $group_id =$branches[0]->group_id;
                $date=date('N');

                $whereCobranzas = "WHEN movement_type_id = 2 AND movements.created_at <= now() ";
                $whereReversion = "WHEN movement_type_id = 3 AND movements.created_at <= now() ";
                $whereCashout = "WHEN movement_type_id = 11 AND movements.created_at <= now() ";
                $wherePagoQr = "WHEN movement_type_id = 17 AND movements.created_at <= now() ";

                if($date == 1 || $date==3 ||$date==5){
                    $hasta_mini=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-1 days');
                }else if($date == 2 || $date==4 ||$date==6){
                    $hasta_mini=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-2 days');
                }else{
                    $hasta_mini=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-3 days');
                }

                $hasta_nano=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-1 days');

                $whereSalesNano = "WHEN debit_credit = 'de' AND (a.owners LIKE '%21%' OR a.owners LIKE '%25%' OR a.grilla != 'true') AND mt_sales.fecha <= '". $hasta_nano . "'";
                $whereSalesMini = "WHEN debit_credit = 'de' AND (a.owners NOT LIKE '%21%' OR a.owners NOT LIKE '%25%' OR a.grilla = 'true') AND mt_sales.fecha <= '". $hasta_mini . "'";

                $resumen_transacciones_groups = \DB::connection('eglobalt_replica')->select("
                    select
                        current_account.group_id as group_id,
                        concat(business_groups.description,' | ',business_groups.ruc) as grupo,
                        a.owners,
                        SUM(
                            CASE ".$whereSalesNano." THEN (movements.amount) 
                            ".$whereSalesMini." THEN (movements.amount) 
                            else 0 END
                        ) as transacciones,
                        SUM(CASE ".$whereCobranzas." THEN (movements.amount) else 0 END) as depositos,
                        SUM(CASE ".$whereReversion." THEN (movements.amount) else 0 END) as reversiones,
                        SUM(CASE ".$whereCashout." THEN (movements.amount) else 0 END) as cashouts,
                        SUM(CASE ".$wherePagoQr." THEN (movements.amount) else 0 END) as pago_qr,
                        (   (SUM(
                            CASE ".$whereSalesNano." THEN (movements.amount) 
                            ".$whereSalesMini." THEN (movements.amount) 
                            else 0 END
                            ))
                            +(SUM(CASE ".$whereCobranzas." THEN (movements.amount) else 0 END))
                            +(SUM(CASE ".$whereReversion." THEN (movements.amount) else 0 END))
                            +(SUM(CASE ".$whereCashout." THEN (movements.amount) else 0 END))
                            +(SUM(CASE ".$wherePagoQr." THEN (movements.amount) else 0 END))
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
                        left join mt_sales on movements.id = mt_sales.movements_id
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
                ");

                foreach($resumen_transacciones_groups as $resumen_transaccion_group){

                    if(str_contains($resumen_transaccion_group->deleted, 'online')){
                        if( str_contains($resumen_transaccion_group->blocks, '1') || 
                        str_contains($resumen_transaccion_group->blocks, '3') || 
                        str_contains($resumen_transaccion_group->blocks, '5') || 
                        str_contains($resumen_transaccion_group->blocks, '7') )
                        {
                            $resumen_transaccion_group->estado='bloqueado';
                        }else{
                            $resumen_transaccion_group->estado='activo';
                        }
                    }else{
                        $resumen_transaccion_group->estado='inactivo';
                    }  

                }

                $resultset['total_debe_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'transacciones')));
                $resultset['total_haber_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'depositos')));
                $resultset['total_reversion_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'reversiones')));
                $resultset['total_cashout_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'cashouts')));
                $resultset['total_pago_qr_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'cashouts')));
                $resultset['total_cuota_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'cuotas')));
                $resultset['total_saldo_groups'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'saldo')));

                $resultset['usersNames'] = $usersNames;
                $resultset['branches'] = $branches;
                $resultset['data_select'] = $data_select;
                $resultset['user_id'] = '';

                $resultset['transactions_groups'] = $resumen_transacciones_groups;
                $resultset['reservationtime'] = (isset($input['reservationtime'])?$input['reservationtime']:0);
            }
            $resultset['activar_resumen'] = 2;

            return $resultset;

        }catch (\Exception $e){
            \Log::error("Error en la consulta de reportes" . $e);
            return false;
        }
    }

    public function resumenMiniterminalesSearch($request){



        \Log::info('request:', [$request]);

        try{
            $input = $this->input;
            /*Busqueda minusiosa*/
            /*SET DATE RANGE*/
            $whereMovements= '';
            if(isset($input['context']) && $input['context'] <> '' && $input['context']<> null){
                if(isset($input['reservationtime']) && $input['reservationtime'] != '0' ){
                    $daterange = explode(' - ',  str_replace('/','-',$input['reservationtime']));
                    $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                    $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                    //$whereMovements = "movements.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereSales = "
                        CASE 
                            WHEN 
                                debit_credit = 'de' AND mt_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' THEN 
                                (m.amount)
                        ELSE
                            0
                    END";
                    $whereCobranzas = "WHEN movement_type_id = 2 AND m.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    //$wherePagosMinis = "WHEN movement_type_id = 2 AND mt_cobranzas_mini_x_atm.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    //$whereDescuentos = "WHEN movement_type_id = 2 AND mt_recibos_cobranzas_x_comision.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereReversion = "WHEN movement_type_id = 3 AND m.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereCashout = "WHEN movement_type_id = 11 AND m.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $wherePagoQr = "WHEN movement_type_id = 17 AND m.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";

                    $input['activar_resumen']='';
                }else{
                    $whereCobranzas = "WHEN movement_type_id = 2 AND m.created_at <= now() ";
                    //$wherePagosMinis = "WHEN movement_type_id = 2 AND mt_cobranzas_mini_x_atm.fecha <= now() ";
                    //$whereDescuentos = "WHEN movement_type_id = 2 AND mt_recibos_cobranzas_x_comision.created_at <= now() ";
                    $whereReversion = "WHEN movement_type_id = 3 AND m.created_at <= now() ";
                    $whereCashout   = "WHEN movement_type_id = 11 AND m.created_at <= now() ";
                    $wherePagoQr   = "WHEN movement_type_id = 17 AND m.created_at <= now() ";
                    if($input['activar_resumen'] ==2){
                        $date=date('N');
                        
                        if($date == 1 || $date==3 ||$date==5){
                            $hasta_mini=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-1 days');
                        }else if($date == 2 || $date==4 ||$date==6){
                            $hasta_mini=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-2 days');
                        }else{
                            $hasta_mini=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-3 days');
                        }

                        $hasta_nano=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-1 days');

                        $whereSales = "
                            CASE 
                                WHEN 
                                    debit_credit = 'de' AND (a.owners LIKE '%21%' OR a.owners LIKE '%25%' OR a.grilla != 'true') AND 
                                    mt_sales.fecha <= '$hasta_nano' THEN 
                                    (m.amount) 
                                WHEN 
                                    debit_credit = 'de' AND (a.owners NOT LIKE '%21%' AND a.owners NOT LIKE '%25%' AND a.grilla = 'true') AND 
                                    mt_sales.fecha <= '$hasta_mini' THEN 
                                    (m.amount)
                            ELSE
                                0
                        END";

                    }else{
                        $whereSales = "
                            CASE 
                                WHEN debit_credit = 'de' AND m.created_at <= now() THEN 
                                    (m.amount) 
                            ELSE
                                0
                        END";
                        //$whereSales = "WHEN debit_credit = 'de' AND movements.created_at <= now() ";
                    }
                }
                $whereMovements .= " LOWER(bg.description) like LOWER('%{$input['context']}%') AND";
            }else{
                if(isset($input['reservationtime']) && $input['reservationtime'] != '0' ){
                    $daterange = explode(' - ',  str_replace('/','-',$input['reservationtime']));
                    $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                    $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                    //$whereMovements = "movements.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereSales = "
                            CASE 
                                WHEN 
                                    debit_credit = 'de' AND mt_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' THEN 
                                    (m.amount)
                            ELSE
                                0
                            END";
                    $whereCobranzas = "WHEN movement_type_id = 2 AND m.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    //$wherePagosMinis = "WHEN movement_type_id = 2 AND mt_cobranzas_mini_x_atm.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    //$whereDescuentos = "WHEN movement_type_id = 2 AND mt_recibos_cobranzas_x_comision.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereReversion = "WHEN movement_type_id = 3 AND m.created_at  BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereCashout   = "WHEN movement_type_id = 11 AND m.created_at  BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $wherePagoQr   = "WHEN movement_type_id = 17 AND m.created_at  BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $input['activar_resumen']='';
                }else{
                    $whereCobranzas = "WHEN movement_type_id = 2 AND m.created_at <= now() ";
                    //$wherePagosMinis = "WHEN movement_type_id = 2 AND mt_cobranzas_mini_x_atm.fecha <= now() ";
                    //$whereDescuentos = "WHEN movement_type_id = 2 AND mt_recibos_cobranzas_x_comision.created_at <= now() ";
                    $whereReversion = "WHEN movement_type_id = 3 AND m.created_at <= now() ";
                    $whereCashout   = "WHEN movement_type_id = 11 AND m.created_at <= now() ";
                    $wherePagoQr = "WHEN movement_type_id = 17 AND m.created_at <= now() ";
                    if($input['activar_resumen'] ==2){

                        $date=date('N');
                        
                        if($date == 1 || $date==3 ||$date==5){
                            $hasta_mini=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-1 days');
                        }else if($date == 2 || $date==4 ||$date==6){
                            $hasta_mini=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-2 days');
                        }else{
                            $hasta_mini=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-3 days');
                        }

                        $hasta_nano=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-1 days');

                        $whereSales = "
                            CASE 
                                WHEN 
                                    debit_credit = 'de' AND (a.owners LIKE '%21%' OR a.owners LIKE '%25%' OR a.grilla != 'true') AND 
                                    mt_sales.fecha <= '$hasta_nano' THEN 
                                    (m.amount) 
                                WHEN 
                                    debit_credit = 'de' AND (a.owners NOT LIKE '%21%' AND a.owners NOT LIKE '%25%' AND a.grilla = 'true') AND 
                                    mt_sales.fecha <= '$hasta_mini' THEN 
                                    (m.amount)
                            ELSE
                                0
                        END";
                    }else{
                        //$whereSales = "WHEN debit_credit = 'de' AND movements.created_at <= now() ";

                        $whereSales = "
                            CASE 
                                WHEN debit_credit = 'de' AND m.created_at <= now() THEN 
                                    (m.amount) 
                            ELSE
                                0
                        END";
                    }

                    
                }
                if(\Sentinel::getUser()->inRole('supervisor_miniterminal')){
                    $supervisor = \DB::table('users_x_groups')->where('user_id',$this->user->id)->first();

                    $whereMovements .= "bg.id = ". $supervisor->group_id . " AND";

                }

                if(\Sentinel::getUser()->inRole('mini_terminal')){
                    $branches = \DB::table('branches')->where('user_id',$this->user->id)->first();

                    $whereMovements .= "bg.id = ". $branches->group_id . " AND";

                }
            }


            $resumen_transacciones_groups_query = "
                select
                        bg.id as group_id,
                        concat(bg.description,' | ',bg.ruc) as grupo,
                        SUM($whereSales) as transacciones,
                        SUM(CASE ".$whereCobranzas." THEN (m.amount) else 0 END) as depositos,
                        SUM(CASE ".$whereReversion." THEN (m.amount) else 0 END) as reversiones,
                        SUM(CASE ".$whereCashout." THEN (m.amount) else 0 END) as cashouts,
                        SUM(CASE ".$wherePagoQr." THEN (m.amount) else 0 END) as pago_qr,
                        (Case when cuota_a.saldo_alquiler != 0 then cuota_a.saldo_alquiler else 0 end)
                        + (Case when cuota_v.saldo_venta != 0 then cuota_v.saldo_venta else 0 end) as cuotas,
                        a.blocks,
                        a.deleted,
                        (   (SUM($whereSales))
                            +(SUM(CASE ".$whereCobranzas." THEN (m.amount) else 0 END))
                            +(SUM(CASE ".$whereReversion." THEN (m.amount) else 0 END))
                            +(SUM(CASE ".$whereCashout." THEN (m.amount) else 0 END))
                            +(SUM(CASE ".$wherePagoQr." THEN (m.amount) else 0 END))
                            + (Case when cuota_a.saldo_alquiler != 0 then cuota_a.saldo_alquiler else 0 end)
                            + (Case when cuota_v.saldo_venta != 0 then cuota_v.saldo_venta else 0 end)
                        ) as saldo
                from mt_movements m
                inner join business_groups bg on bg.id = m.group_id
                left join mt_sales on m.id = mt_sales.movements_id
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
                    from business_groups
                    inner join branches on business_groups.id = branches.group_id
                    inner join points_of_sale pos on branches.id = pos.branch_id
                    inner join atms on atms.id = pos.atm_id
                    group by grupo_id
                ) a on a.grupo_id = bg.id
                left join (
                    select sum(saldo_cuota) as saldo_alquiler, group_id 
                    from alquiler
                    Inner join cuotas_alquiler on alquiler.id = cuotas_alquiler.alquiler_id
                    Inner join alquiler_housing on alquiler.id = alquiler_housing.alquiler_id
                    where fecha_vencimiento < now() and saldo_cuota <> 0 and alquiler.deleted_at is null and cod_venta is not null
                    group by group_id order by group_id
                ) cuota_a on bg.id = cuota_a.group_id
                left join (
                    select sum(saldo_cuota) as saldo_venta, group_id 
                    from venta
                    Inner join cuotas on venta.id = cuotas.credito_venta_id
                    Inner join venta_housing on venta.id = venta_housing.venta_id
                    where fecha_vencimiento < now() and saldo_cuota <> 0 and venta.deleted_at is null and cod_venta is not null
                    group by group_id order by group_id
                ) cuota_v on bg.id = cuota_v.group_id
                where
                    ".$whereMovements."
                    m.movement_type_id not in (4, 5, 7, 8, 9, 10)
                    and m.deleted_at is null
                group by bg.id, grupo, a.owners, a.blocks, a.deleted, cuota_a.saldo_alquiler, cuota_v.saldo_venta
                order by saldo desc
            ";

            \Log::info("QUERY:\n\n$resumen_transacciones_groups_query");

            $resumen_transacciones_groups = \DB::connection('eglobalt_replica')
                ->select(
                    $resumen_transacciones_groups_query
                );
            
            foreach($resumen_transacciones_groups as $resumen_transaccion_group){

                if(str_contains($resumen_transaccion_group->deleted, 'online')){
                    if( str_contains($resumen_transaccion_group->blocks, '1') || 
                    str_contains($resumen_transaccion_group->blocks, '3') || 
                    str_contains($resumen_transaccion_group->blocks, '5') || 
                    str_contains($resumen_transaccion_group->blocks, '7') )
                    {
                        $resumen_transaccion_group->estado='bloqueado';
                    }else{
                        $resumen_transaccion_group->estado='activo';
                    }
                }else{
                    $resumen_transaccion_group->estado='inactivo';
                }  

            }
            
            $resultset = array(
                'target'        => 'Resumen Mini Terminales',
                'transactions_groups'  => $resumen_transacciones_groups,
                'reservationtime' => (isset($input['reservationtime'])?$input['reservationtime']:0),
                'i'             =>  1,
                'activar_resumen' => $input['activar_resumen']
            );

            $resultset['total_debe_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'transacciones')));
            $resultset['total_haber_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'depositos')));
            $resultset['total_reversion_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'reversiones')));
            $resultset['total_cashout_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'cashouts')));
            $resultset['total_pago_qr_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'pago_qr')));
            $resultset['total_cuota_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'cuotas')));
            $resultset['total_saldo_groups'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'saldo')));
            //dd($resultset);
            return $resultset;

        }catch (\Exception $e){
            \Log::info($e);
                return false;
        }
    }

    public function getBranchfroGroup($group_id, $fecha){
        \Log::info($fecha);
        if(isset($fecha) && $fecha != '0' && $fecha != '2' ){
            $daterange = explode('-',  str_replace('/','-',$fecha));
            $daterange[0] = date('Y-m-d H:i:s', ($daterange[0]/1000));
            $daterange[1] = date('Y-m-d H:i:s',  ($daterange[1]/1000));

            $whereTransactions = "t.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
            $whereCobranzas = "m.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";

            $total_debe = "
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
                    ) as total,
                    atms.name as atm_name,
                    atms.id as atm_id
                from
                    transactions t
                inner join atms on
                    atms.id = t.atm_id
                inner join points_of_sale on 
                    points_of_sale.atm_id = t.atm_id and points_of_sale.deleted_at is null 
                inner join branches on 
                    branches.id = points_of_sale.branch_id and branches.group_id = ".$group_id."
                where 
                t.transaction_type = 1 and
                atms.owner_id in(16, 21, 25) and
                ".$whereTransactions."
                group by atms.id
            ";

            $total_depositado="
                select
                -sum(abs(m.amount)) as total,
                    atms.name as atm_name,
                    atms.id as atm_id 
                from
                    mt_movements m
                inner join atms on
                    atms.id = m.atm_id
                where
                    m.movement_type_id = 2 and
                    m.deleted_at is null and
                    m.group_id = $group_id
                    and ".$whereCobranzas."
                group by atms.id
            ";
      
            $total_reversion = "
                select
                    -sum(abs(m.amount)) as total,
                    atms.name as atm_name,
                    atms.id as atm_id 
                from
                    mt_movements m
                inner join atms on
                    atms.id = m.atm_id
                where 
                    m.movement_type_id = 3 and
                    m.deleted_at is null and
                    m.group_id = $group_id
                    and ".$whereCobranzas."
                group by atms.id
            ";

            $total_cashout = "
                select
                    -sum(abs(m.amount)) as total,
                    atms.name as atm_name,
                    atms.id as atm_id 
                from
                    mt_movements m
                inner join atms on
                    atms.id = m.atm_id
                where 
                    m.movement_type_id = 11 and
                    m.deleted_at is null and
                    m.group_id = $group_id
                    and ".$whereCobranzas."
                group by atms.id
            ";

            $resumen_transacciones_query = "
                with balances as (
                    ".$total_debe."
                    union
                    ".$total_depositado."
                    union
                    ".$total_reversion."
                    union
                    ".$total_cashout."
                )
                select
                    atms.id,
                    block_type.description,
                    atms.block_type_id, 
                    atms.name,
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
                inner join atms on atms.id = balances.atm_id and atms.owner_id in (16, 21, 25)
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
                group by atms.id, block_type.description, cuotas_a.saldo_alquiler, cuotas_v.saldo_venta
            ";

            $resumen_transacciones = \DB::connection('eglobalt_replica')
                ->select(
                    \DB::raw($resumen_transacciones_query)
                );

        }else{

            $date = Carbon::now()->format('Y-m-d H:i:s');

            if($fecha != '0'){
                $transaccionado = "total_transaccionado_cierre";

            }else{
                $transaccionado = "total_transaccionado";
            }

            $resumen_transacciones = \DB::connection('eglobalt_replica')->table('atms')
                /*->selectRaw("atms.id, name, last_request_at, block_type.description, sum(total_depositado + total_transaccionado_cierre + total_reversado + total_cashout + total_pago_cashout) as saldo, block_type_id")*/
                ->selectRaw("atms.id, name, last_request_at, block_type.description, block_type_id, atms.deleted_at as eliminado, (total_depositado + $transaccionado + total_reversado + total_cashout + total_pago_cashout + total_pago_qr + total_multa) +
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
                ->join('block_type','block_type.id','=','atms.block_type_id')
                ->join('balance_atms','atms.id','=','balance_atms.atm_id')
                ->join('points_of_sale','atms.id','=','points_of_sale.atm_id')
                ->join('branches','branches.id','=','points_of_sale.branch_id')
                ->leftjoin('alquiler_housing','atms.housing_id','=','alquiler_housing.housing_id')
                //->leftjoin('alquiler','alquiler.id','=','alquiler_housing.alquiler_id')
                ->leftjoin('alquiler', function($join) use($group_id){
                    $join->on('alquiler.id','=','alquiler_housing.alquiler_id')
                    ->where('alquiler.group_id','=',$group_id);
                })
                ->leftjoin('cuotas_alquiler','alquiler.id','=','cuotas_alquiler.alquiler_id')
                ->leftjoin('venta_housing','atms.housing_id','=','venta_housing.housing_id')
                ->leftjoin('venta', function($join) use($group_id){
                    $join->on('venta.id','=','venta_housing.venta_id')
                    ->where('venta.group_id','=',$group_id);
                })
                ->leftjoin('cuotas','venta.id','=','cuotas.credito_venta_id')
                ->whereIn('atms.owner_id', [16, 21, 25])
                //->where('atms.deleted_at', null)
                ->where('branches.group_id', $group_id)
                ->groupBy('atms.id', 'block_type.description', $transaccionado, 'total_depositado', 'total_reversado', 'total_cashout', 'total_pago_cashout', 'total_pago_qr', 'total_multa')
                ->orderBy('atms.id','asc');



            $resumen_transacciones_query = $resumen_transacciones->toSql();

            $resumen_transacciones = $resumen_transacciones->get();
        }
           
        \Log::info("QUERY:\n\n$resumen_transacciones_query");

        $details = '';
        $sum=0;
        foreach ($resumen_transacciones as $transaction) {

            if(is_null($transaction->eliminado)){
                $fecha = \DB::connection('eglobalt_replica')->table('transactions')
                    ->selectRaw("created_at")
                    ->where('atm_id', $transaction->id)
                    ->orderBy('transactions.id', 'desc')
                ->first();

                $date=$fecha->created_at;
            }else{
                $date = $transaction->eliminado;
            }
            
            $sum += $transaction->saldo;

            if(is_null($transaction->eliminado)){
                if($transaction->block_type_id == 0){
                    $descripcion='<span class="label label-success">Activo</span>';
                }else{
                    $descripcion='<span class="label label-danger">'.$transaction->description.'</span>';
                }
            }else{
                $descripcion='<span class="label label-warning">Inactivo</span>';
            }

            if( $transaction->saldo > 0 ){
                $style="color:red";
            }else{
                $style="color:green";
            }

            $details .='<tr>
                <td>'. $transaction->id .'</td>
                <td>'. $transaction->name .'</td>
                <td>'. Carbon::parse($date)->format('d/m/Y H:i:s') .'</td>
                <td style='.$style.'>'. number_format($transaction->saldo, 0) .'</td>
                <td>'. $descripcion .'</td>
                </tr>';
        }

        $details .= "<br><br><tr>
        <td colspan='4'> <h4><label> Saldo total: ". number_format($sum, 0) .' Gs.</label> <h4> </td>
        </tr>';

        return $details;
    }

    public function getCuotasForGroups($group_id){
            
        $cuotas_alquiler = \DB::connection('eglobalt_replica')
            ->table('alquiler')
            ->select('atms.name', 'num_cuota', 'saldo_cuota', 'fecha_vencimiento')
            ->join('cuotas_alquiler', 'alquiler.id', '=', 'cuotas_alquiler.alquiler_id')
            ->join('alquiler_housing', 'alquiler.id', '=', 'alquiler_housing.alquiler_id')
            ->join('atms', 'atms.housing_id', '=', 'alquiler_housing.housing_id')
            ->where('alquiler.group_id', $group_id)
            ->whereRaw('fecha_vencimiento < now()')
            ->where('saldo_cuota', '<>' ,0)
            ->whereNull('alquiler.deleted_at')
            ->whereNotNull('cuotas_alquiler.cod_venta')
            ->orderBy('cuotas_alquiler.num_cuota', 'ASC')
        ->get();

        $details='';

        foreach ($cuotas_alquiler as $cuota_a) {
            $details .='<tr>
                <td>'. $cuota_a->name .'</td>
                <td>'.  date('d/m/Y', strtotime($cuota_a->fecha_vencimiento)) .'</td>
                <td>'. $cuota_a->num_cuota .'</td>
                <td>'. number_format($cuota_a->saldo_cuota, 0) .'</td>
                </tr>';
        }

        $cuotas_ventas = \DB::connection('eglobalt_replica')
            ->table('venta')
            ->select('atms.name', 'numero_cuota', 'saldo_cuota', 'fecha_vencimiento')
            ->join('cuotas', 'venta.id', '=', 'cuotas.credito_venta_id')
            ->join('venta_housing', 'venta.id', '=', 'venta_housing.venta_id')
            ->join('atms', 'atms.housing_id', '=', 'venta_housing.housing_id')
            ->where('venta.group_id', $group_id)
            ->whereRaw('fecha_vencimiento < now()')
            ->where('saldo_cuota', '<>', 0)
            ->whereNull('venta.deleted_at')
            ->whereNotNull('cuotas.cod_venta')
            ->orderBy('cuotas.numero_cuota', 'ASC')
        ->get();

        foreach ($cuotas_ventas as $cuota_v) {

            $details .='<tr>
                <td>'. $cuota_v->name .'</td>
                <td>'.  date('d/m/Y', strtotime($cuota_v->fecha_vencimiento)) .'</td>
                <td>'. $cuota_v->numero_cuota .'</td>
                <td>'. number_format($cuota_v->saldo_cuota, 0) .'</td>
                </tr>';
        }

        return $details;
    }

    public function resumenMiniterminalesSearchExport(){
        try{
            $input = $this->input;
            /*Busqueda minusiosa*/
            /*SET DATE RANGE*/
            $whereMovements= '';
            if(isset($input['context']) && $input['context'] <> '' && $input['context']<> null){
                if(isset($input['reservationtime']) && $input['reservationtime'] != '0' ){
                    $daterange = explode(' - ',  str_replace('/','-',$input['reservationtime']));
                    $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                    $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                    $whereSales = "
                        CASE 
                            WHEN 
                                debit_credit = 'de' AND mt_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' THEN 
                                (m.amount)
                        ELSE
                            0
                    END";
                    $whereCobranzas = "WHEN movement_type_id = 2 AND m.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    //$wherePagosMinis = "WHEN movement_type_id = 2 AND mt_cobranzas_mini_x_atm.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    //$whereDescuentos = "WHEN movement_type_id = 2 AND mt_recibos_cobranzas_x_comision.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereReversion = "WHEN movement_type_id = 3 AND m.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereCashout = "WHEN movement_type_id = 11 AND m.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $wherePagoQr = "WHEN movement_type_id = 17 AND m.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";

                    $whereTransactions = "t.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereCobros = "m.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                }else{
                    //$whereSales = "WHEN debit_credit = 'de' AND movements.created_at <= now() ";
                    $whereSales = "
                        CASE 
                            WHEN debit_credit = 'de' AND m.created_at <= now() THEN 
                                (m.amount) 
                        ELSE
                            0
                    END";

                    $whereTransactions = "
                        CASE 
                            WHEN debit_credit = 'de' AND m.created_at <= now() THEN 
                                (m.amount) 
                        ELSE
                            0
                    END";
                    $whereCobranzas = "WHEN movement_type_id = 2 AND m.created_at <= now() ";
                    //$wherePagosMinis = "WHEN movement_type_id = 2 AND mt_cobranzas_mini_x_atm.fecha <= now() ";
                    //$whereDescuentos = "WHEN movement_type_id = 2 AND mt_recibos_cobranzas_x_comision.created_at <= now() ";
                    $whereReversion = "WHEN movement_type_id = 3 AND m.created_at <= now() ";
                    $whereCashout   = "WHEN movement_type_id = 11 AND m.created_at <= now() ";
                    $wherePagoQr   = "WHEN movement_type_id = 17 AND m.created_at <= now() ";

                    //$whereTransactions = "t.created_at <= now()";
                    $whereCobros = "m.created_at <= now()";
                }
                $whereMovements .= " LOWER(bg.description) like LOWER('%{$input['context']}%') AND";
            }else{
                if(isset($input['reservationtime']) && $input['reservationtime'] != '0' ){
                    $daterange = explode(' - ',  str_replace('/','-',$input['reservationtime']));
                    $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                    $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                    $whereSales = "
                        CASE 
                            WHEN 
                                debit_credit = 'de' AND mt_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' THEN 
                                (m.amount)
                        ELSE
                            0
                    END";
                    $whereCobranzas = "WHEN movement_type_id = 2 AND m.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    //$wherePagosMinis = "WHEN movement_type_id = 2 AND mt_cobranzas_mini_x_atm.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    //$whereDescuentos = "WHEN movement_type_id = 2 AND mt_recibos_cobranzas_x_comision.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereReversion = "WHEN movement_type_id = 3 AND m.created_at  BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereCashout   = "WHEN movement_type_id = 11 AND m.created_at  BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $wherePagoQr   = "WHEN movement_type_id = 17 AND m.created_at  BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";

                    $whereTransactions = "t.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereCobros = "m.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                }else{
                    $whereCobranzas = "WHEN movement_type_id = 2 AND m.created_at <= now() ";
                    //$wherePagosMinis = "WHEN movement_type_id = 2 AND mt_cobranzas_mini_x_atm.fecha <= now() ";
                    //$whereDescuentos = "WHEN movement_type_id = 2 AND mt_recibos_cobranzas_x_comision.created_at <= now() ";
                    $whereReversion = "WHEN movement_type_id = 3 AND m.created_at <= now() ";
                    $whereCashout   = "WHEN movement_type_id = 11 AND m.created_at <= now() ";
                    $wherePagoQr = "WHEN movement_type_id = 17 AND m.created_at <= now() ";

                    if($input['activar_resumen'] ==2){
                        $date=date('N');
                        
                        if($date == 1 || $date==3 ||$date==5){
                            $hasta_mini=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-1 days');
                        }else if($date == 2 || $date==4 ||$date==6){
                            $hasta_mini=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-2 days');
                        }else{
                            $hasta_mini=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-3 days');
                        }

                        $hasta_nano=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-1 days');

                        $whereSales = "
                            CASE 
                                WHEN 
                                    debit_credit = 'de' AND (a.owners LIKE '%21%' OR a.owners LIKE '%25%' OR a.grilla != 'true') AND 
                                    mt_sales.fecha <= '$hasta_nano' THEN 
                                    (m.amount) 
                                WHEN 
                                    debit_credit = 'de' AND (a.owners NOT LIKE '%21%' AND a.owners NOT LIKE '%25%' AND a.grilla = 'true') AND 
                                    mt_sales.fecha <= '$hasta_mini' THEN 
                                    (m.amount)
                            ELSE
                                0
                        END";

                        $whereTransactions = "
                            CASE 
                                WHEN 
                                    debit_credit = 'de' AND (atms.owner_id = 21 OR atms.owner_id = 25 OR atms.grilla_tradicional != 'true') AND 
                                    mt_sales.fecha <= '$hasta_nano' THEN 
                                    (m.amount) 
                                WHEN 
                                    debit_credit = 'de' AND (atms.owner_id != 21 AND atms.owner_id != 25 AND atms.grilla_tradicional = 'true') AND 
                                    mt_sales.fecha <= '$hasta_mini' THEN 
                                    (m.amount)
                            ELSE
                                0
                        END";
                        
                        //$whereTransactions = "t.created_at <= '". $hasta_mini . "'";
                        //$whereMinisPagos = "fecha <= '". $hasta_mini . "'";
                        //$whereMiniDescuento = "rc.created_at <= '". $hasta_mini . "'";
                        //$whereBoletas = "fecha <= '". $hasta_mini . "'";
                        $whereCobros = "m.created_at <= '". $hasta_mini . "'";
                    }else{
                        //$whereSales = "WHEN debit_credit = 'de' AND movements.created_at <= now() ";
                        $whereSales = "
                            CASE 
                                WHEN debit_credit = 'de' AND m.created_at <= now() THEN 
                                    (m.amount) 
                            ELSE
                                0
                        END";
                        $whereTransactions = "t.created_at <= now()";
                        $whereCobros = "m.created_at <= now()";
                    }
                    
                }
                if(\Sentinel::getUser()->inRole('supervisor_miniterminal')){
                    $supervisor = \DB::table('users_x_groups')->where('user_id',$this->user->id)->first();

                    $whereMovements .= "bg.id = ". $supervisor->group_id . " AND";
                    $whereTransactions .= " AND branches.group_id = ". $supervisor->group_id;
                    //$whereBoletas .= " AND branches.group_id = ". $supervisor->group_id;
                    //$whereMinisPagos .= " AND branches.group_id = ". $supervisor->group_id;
                    //$whereMiniDescuento .= " AND branches.group_id = ". $supervisor->group_id;
                    $whereCobros .= " AND bg.group_id = ". $supervisor->group_id;

                }
            }

            if(isset($input['reservationtime']) && $input['reservationtime'] != '0' ){
                $total_debe = "
                    select
                        'transacciones' as tipo,
                        SUM(
                            CASE 
                                WHEN status = 'success' and t.amount >= 0 THEN 
                                    abs(t.amount)
                                WHEN status = 'error' and t.service_id in(14, 15) and t.amount >= 0 THEN 
                                    abs(t.amount)
                                else
                                    0
                            END
                        ) as total,
                        atms.name as atm_name,
                        atms.id as atm_id
                    from
                        transactions t
                    inner join atms on
                        atms.id = t.atm_id
                    where 
                    t.transaction_type = 1 and
                    atms.owner_id in(16, 21, 25) and
                    t.service_id not in(100) and
                    ".$whereTransactions."
                    group by atms.id
                ";

                $total_depositado="
                    select
                        'deposito' as tipo,
                        -sum(abs(m.amount)) as total,
                        atms.name as atm_name,
                        atms.id as atm_id 
                    from
                        mt_movements m
                    inner join atms on
                        atms.id = m.atm_id
                    where
                        m.movement_type_id = 2 and
                        m.deleted_at is null
                        and ".$whereCobros."
                    group by atms.id
                ";
      
                $total_reversion = "
                    select
                        'reversion' as tipo,
                        -sum(abs(m.amount)) as total,
                        atms.name as atm_name,
                        atms.id as atm_id 
                    from
                        mt_movements m
                    inner join atms on
                        atms.id = m.atm_id
                    where 
                        m.movement_type_id = 3 and
                        m.deleted_at is null
                        and ".$whereCobros."
                    group by atms.id
                ";

                $total_cashout = "
                    select
                        'cashout' as tipo,
                        -sum(abs(m.amount)) as total,
                        atms.name as atm_name,
                        atms.id as atm_id 
                    from
                        mt_movements m
                    inner join atms on
                        atms.id = m.atm_id
                    where 
                        m.movement_type_id = 11 and
                        m.deleted_at is null
                        and ".$whereCobros."
                    group by atms.id
                ";

                $resumen_transacciones = \DB::connection('eglobalt_replica')->select("
                    with balances as (
                        ".$total_debe."
                        union
                        ".$total_depositado."
                        union
                        ".$total_reversion."
                        union
                        ".$total_cashout."
                    )
                    select
                        atms.id as atm_id,
                        block_type.description,
                        atms.block_type_id, 
                        atms.name as atm_name,
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
                    inner join atms on atms.id = balances.atm_id and atms.owner_id in(16, 21, 25)
                    inner join block_type on block_type.id = atms.block_type_id
                    group by atms.id, block_type.description
                ");
            }else{

                $resumen_transacciones = \DB::connection('eglobalt_replica')->select("
                    select
                        atms.id as atm_id,
                        bt.description,
                        bt.id as block_type_id,
                        atms.name as atm_name,
                        atms.deleted_at as eliminado,
                        (SUM($whereTransactions)) as transacciones,
                        SUM(CASE $whereCobranzas THEN (m.amount) else 0 END) as depositos,
                        SUM(CASE $whereReversion THEN (m.amount) else 0 END) as reversiones,
                        SUM(CASE $whereCashout THEN (m.amount) else 0 END) as cashouts,
                        SUM(CASE $wherePagoQr THEN (m.amount) else 0 END) as pago_qr
                    from mt_movements m
                        inner join atms on atms.id = m.atm_id
                        inner join block_type bt on bt.id = atms.block_type_id
                        left join mt_sales on m.id = mt_sales.movements_id
                    where
                        m.movement_type_id not in (4, 5, 7, 8, 9, 10)
                        and m.deleted_at is null
                    group by atms.id, bt.id, bt.description
                ");
            }
            

            foreach($resumen_transacciones as $resumen_transaccion){

                $resumen_transaccion->saldo = $resumen_transaccion->transacciones + $resumen_transaccion->depositos + $resumen_transaccion->reversiones + $resumen_transaccion->cashouts;
                
                if(is_null($resumen_transaccion->eliminado)){
                    if($resumen_transaccion->block_type_id == 0){
                        $resumen_transaccion->estado ='Activo';
                    }else{
                        $resumen_transaccion->estado ='Bloqueado';
                    }
                }else{
                    $resumen_transaccion->estado ='Inactivo';
                }

                unset($resumen_transaccion->block_type_id);
                unset($resumen_transaccion->description);
                unset($resumen_transaccion->eliminado);
            }

            $resumen_transacciones_groups = \DB::connection('eglobalt_replica')->select("
                select
                        bg.id as group_id,
                        bg.description as grupo,
                        bg.ruc as ruc,
                        SUM($whereSales) as transacciones,
                        SUM(CASE ".$whereCobranzas." THEN (m.amount) else 0 END) as depositos,
                        SUM(CASE ".$whereReversion." THEN (m.amount) else 0 END) as reversiones,
                        SUM(CASE ".$whereCashout." THEN (m.amount) else 0 END) as cashouts,
                        SUM(CASE ".$wherePagoQr." THEN (m.amount) else 0 END) as pago_qr,
                        (Case when cuota_a.saldo_alquiler != 0 then cuota_a.saldo_alquiler else 0 end)
                        + (Case when cuota_v.saldo_venta != 0 then cuota_v.saldo_venta else 0 end) as cuotas,
                        a.blocks,
                        a.deleted,
                        (   (SUM($whereSales))
                            +(SUM(CASE ".$whereCobranzas." THEN (m.amount) else 0 END))
                            +(SUM(CASE ".$whereReversion." THEN (m.amount) else 0 END))
                            +(SUM(CASE ".$whereCashout." THEN (m.amount) else 0 END))
                            +(SUM(CASE ".$wherePagoQr." THEN (m.amount) else 0 END))
                            + (Case when cuota_a.saldo_alquiler != 0 then cuota_a.saldo_alquiler else 0 end)
                            + (Case when cuota_v.saldo_venta != 0 then cuota_v.saldo_venta else 0 end)
                        ) as saldo
                from mt_movements m
                inner join business_groups bg on bg.id = m.group_id
                left join mt_sales on m.id = mt_sales.movements_id
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
                    from business_groups
                    inner join branches on business_groups.id = branches.group_id
                    inner join points_of_sale pos on branches.id = pos.branch_id
                    inner join atms on atms.id = pos.atm_id
                    group by grupo_id
                ) a on a.grupo_id = bg.id
                left join (
                    select sum(saldo_cuota) as saldo_alquiler, group_id 
                    from alquiler
                    Inner join cuotas_alquiler on alquiler.id = cuotas_alquiler.alquiler_id
                    Inner join alquiler_housing on alquiler.id = alquiler_housing.alquiler_id
                    where fecha_vencimiento < now() and saldo_cuota <> 0 and alquiler.deleted_at is null and cod_venta is not null
                    group by group_id order by group_id
                ) cuota_a on bg.id = cuota_a.group_id
                left join (
                    select sum(saldo_cuota) as saldo_venta, group_id 
                    from venta
                    Inner join cuotas on venta.id = cuotas.credito_venta_id
                    Inner join venta_housing on venta.id = venta_housing.venta_id
                    where fecha_vencimiento < now() and saldo_cuota <> 0 and venta.deleted_at is null and cod_venta is not null
                    group by group_id order by group_id
                ) cuota_v on bg.id = cuota_v.group_id
                where
                    ".$whereMovements."
                    m.movement_type_id not in (4, 5, 7, 8, 9, 10)
                    and m.deleted_at is null
                group by bg.id, grupo, a.owners, a.blocks, a.deleted, cuota_a.saldo_alquiler, cuota_v.saldo_venta
                order by saldo desc
            ");

            foreach($resumen_transacciones_groups as $transaction){

                if(str_contains($transaction->deleted, 'online')){
                    if( str_contains($transaction->blocks, '1') || 
                    str_contains($transaction->blocks, '3') || 
                    str_contains($transaction->blocks, '5') || 
                    str_contains($transaction->blocks, '7') )
                    {
                        $transaction->estado='Bloqueado';
                    }else{
                        $transaction->estado='Activo';
                    }
                }else{
                    $transaction->estado='Inactivo';
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

        }catch (\Exception $e){
            \Log::info($e);
                return false;
        }
    }

    public function resumenDetalladoReports($request){
        try{

            $resultset = array(
                'target' => 'Resumen Detallado Miniterminales',
            );            

            if(!\Sentinel::getUser()->inRole('mini_terminal') && !\Sentinel::getUser()->inRole('supervisor_miniterminal')){                

                $resumen_transacciones_groups = \DB::select("
                    select
                        bg.id as group_id,
                        bg.description as grupo,
                        SUM(CASE WHEN movement_type_id = 1 THEN (m.amount) else 0 END) as transacciones,
                        SUM(CASE WHEN movement_type_id = 6 THEN (m.amount) else 0 END) as paquetigos,
                        SUM(CASE WHEN movement_type_id = 13 THEN (m.amount) else 0 END) as personal,
                        SUM(CASE WHEN movement_type_id = 14 THEN (m.amount) else 0 END) as claro,
                        SUM(CASE WHEN movement_type_id = 12 THEN (m.amount) else 0 END) as pago_cash,
                        SUM(CASE WHEN movement_type_id = 2 THEN (m.amount) else 0 END) as depositos,
                        SUM(CASE WHEN movement_type_id = 3 THEN (m.amount) else 0 END) as reversiones,
                        SUM(CASE WHEN movement_type_id = 11 THEN (m.amount) else 0 END) as cashouts,
                        (
                            (SUM(CASE WHEN debit_credit = 'de' THEN (m.amount) else 0 END))
                            + (SUM(CASE WHEN debit_credit = 'cr' THEN (m.amount) else 0 END))
                        ) as saldo
                    from mt_movements m
                        inner join business_groups bg on bg.id = m.group_id
                        where
                            m.created_at <= now() and
                            m.movement_type_id not in (4, 5, 7, 8, 9, 10)
                            and m.deleted_at is null
                    group by bg.id
                    order by saldo desc
                ");

                $results_groups = $this->arrayPaginator($resumen_transacciones_groups, $request);
                
                $resultset['transactions_groups'] = $results_groups;
                $resultset['reservationtime'] = (isset($input['reservationtime'])?$input['reservationtime']:0);
                $resultset['total_debe_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'transacciones')));
                $resultset['total_paquetigo_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'paquetigos')));
                $resultset['total_haber_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'depositos')));
                $resultset['total_reversion_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'reversiones')));
                $resultset['total_cashout_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'cashouts')));
                $resultset['total_personal_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'personal')));
                $resultset['total_claro_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'claro')));
                $resultset['total_pago_cash_grupo'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'pago_cash')));
                $resultset['total_saldo_groups'] = number_format(array_sum(array_column($resumen_transacciones_groups, 'saldo')));

            }
            
            return $resultset;

        }catch (\Exception $e){
            \Log::error("Error en la consulta de reportes" . $e);
            return false;
        }
    }

    public function resumenDetalladoSearch($request){
        try{
            $input = $this->input;            
            /*Busqueda minusiosa*/
            /*SET DATE RANGE*/
            $whereMovements= '';
            if(isset($input['context']) && $input['context'] <> '' && $input['context']<> null){
                if(isset($input['reservationtime']) && $input['reservationtime'] != '0' ){
                    $daterange = explode(' - ',  str_replace('/','-',$input['reservationtime']));
                    $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                    $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                    //$whereMovements = "movements.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereSales = "WHEN movement_type_id = 1 AND mt_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $wherePaquetigos = "WHEN movement_type_id = 6 AND mt_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $wherePersonal = "WHEN movement_type_id = 13 AND mt_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereClaro = "WHEN movement_type_id = 14 AND mt_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $wherePagoCash = "WHEN movement_type_id = 12 AND mt_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";

                    $whereCobranzas = "WHEN movement_type_id = 2 AND m.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereReversion = "WHEN movement_type_id = 3 AND m.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereCashout = "WHEN movement_type_id = 11 AND m.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                }else{
                    $whereSales = "WHEN movement_type_id = 1 AND m.created_at <= now()";
                    $wherePaquetigos = "WHEN movement_type_id = 6 AND m.created_at <= now()";
                    $wherePersonal = "WHEN movement_type_id = 13 AND m.created_at <= now()";
                    $whereClaro = "WHEN movement_type_id = 14 AND m.created_at <= now()";
                    $wherePagoCash = "WHEN movement_type_id = 12 AND m.created_at <= now()";
                    //$whereSales = "WHEN debit_credit = 'de' AND movements.created_at <= now() ";
                    $whereCobranzas = "WHEN movement_type_id = 2 AND m.created_at <= now() ";
                    $whereReversion = "WHEN movement_type_id = 3 AND m.created_at <= now() ";
                    $whereCashout = "WHEN movement_type_id = 11 AND m.created_at <= now() ";
                }
                $whereMovements .= " LOWER(bg.description) like LOWER('%{$input['context']}%') AND";
            }else{
                if(isset($input['reservationtime']) && $input['reservationtime'] != '0' ){
                    $daterange = explode(' - ',  str_replace('/','-',$input['reservationtime']));
                    $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                    $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                    //$whereMovements = "movements.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereSales = "WHEN movement_type_id = 1 AND mt_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $wherePaquetigos = "WHEN movement_type_id = 6 AND mt_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $wherePersonal = "WHEN movement_type_id = 13 AND mt_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereClaro = "WHEN movement_type_id = 14 AND mt_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $wherePagoCash = "WHEN movement_type_id = 12 AND mt_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";

                    $whereCobranzas = "WHEN movement_type_id = 2 AND m.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereReversion = "WHEN movement_type_id = 3 AND m.created_at  BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereCashout = "WHEN movement_type_id = 11 AND m.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                }else{
                    $whereSales = "WHEN movement_type_id = 1 AND m.created_at <= now()";
                    $wherePaquetigos = "WHEN movement_type_id = 6 AND m.created_at <= now()";
                    $wherePersonal = "WHEN movement_type_id = 13 AND m.created_at <= now()";
                    $whereClaro = "WHEN movement_type_id = 14 AND m.created_at <= now()";
                    $wherePagoCash = "WHEN movement_type_id = 12 AND m.created_at <= now()";
                    //$whereSales = "WHEN debit_credit = 'de' AND movements.created_at <= now() ";
                    $whereCobranzas = "WHEN movement_type_id = 2 AND m.created_at <= now() ";
                    $whereReversion = "WHEN movement_type_id = 3 AND m.created_at <= now() ";
                    $whereCashout   = "WHEN movement_type_id = 11 AND m.created_at <= now() ";
                }
                if(\Sentinel::getUser()->inRole('supervisor_miniterminal')){
                    $supervisor = \DB::table('users_x_groups')->where('user_id',$this->user->id)->first();

                    $whereMovements .= "bg.id = ". $supervisor->group_id . " AND";

                }
            }

            $resumen_transacciones_groups = \DB::select("
                select
                        bg.id as group_id,
                        bg.description as grupo,
                        SUM(CASE ".$whereSales." THEN (m.amount) else 0 END) as transacciones,
                        SUM(CASE ".$wherePaquetigos." THEN (m.amount) else 0 END) as paquetigos,
                        SUM(CASE ".$wherePersonal." THEN (m.amount) else 0 END) as personal,
                        SUM(CASE ".$whereClaro." THEN (m.amount) else 0 END) as claro,
                        SUM(CASE ".$wherePagoCash." THEN (m.amount) else 0 END) as pago_cash,
                        SUM(CASE ".$whereCobranzas." THEN (m.amount) else 0 END) as depositos,
                        SUM(CASE ".$whereReversion." THEN (m.amount) else 0 END) as reversiones,
                        SUM(CASE ".$whereCashout." THEN (m.amount) else 0 END) as cashouts,
                        (   (SUM(CASE ".$whereSales." THEN (m.amount) else 0 END))
                            +(SUM(CASE ".$wherePaquetigos." THEN (m.amount) else 0 END))
                            +(SUM(CASE ".$wherePersonal." THEN (m.amount) else 0 END))
                            +(SUM(CASE ".$whereClaro." THEN (m.amount) else 0 END))
                            +(SUM(CASE ".$wherePagoCash." THEN (m.amount) else 0 END))
                            +(SUM(CASE ".$whereCobranzas." THEN (m.amount) else 0 END))
                            +(SUM(CASE ".$whereReversion." THEN (m.amount) else 0 END))
                            +(SUM(CASE ".$whereCashout." THEN (m.amount) else 0 END))
                        ) as saldo
                from mt_movements m
                inner join business_groups bg on bg.id = m.group_id
                left join mt_sales on m.id = mt_sales.movements_id
                where
                    ".$whereMovements."
                    m.movement_type_id not in (4, 5, 7, 8, 9, 10)
                    and m.deleted_at is null
                group by bg.id
                order by saldo desc
            ");            

            $results_groups = $this->arrayPaginator($resumen_transacciones_groups, $request);

            $resultset = array(
                'target'        => 'Resumen Detallado Miniterminales',
                'transactions_groups'  => $results_groups,
                'reservationtime' => (isset($input['reservationtime'])?$input['reservationtime']:0),
                'i'             =>  1,
                'total_debe_grupo' => number_format(array_sum(array_column($resumen_transacciones_groups, 'transacciones'))),
                'total_paquetigo_grupo' => number_format(array_sum(array_column($resumen_transacciones_groups, 'paquetigos'))),
                'total_personal_grupo' => number_format(array_sum(array_column($resumen_transacciones_groups, 'personal'))),
                'total_claro_grupo' => number_format(array_sum(array_column($resumen_transacciones_groups, 'claro'))),
                'total_pago_cash_grupo' => number_format(array_sum(array_column($resumen_transacciones_groups, 'pago_cash'))),
                'total_haber_grupo' => number_format(array_sum(array_column($resumen_transacciones_groups, 'depositos'))),
                'total_reversion_grupo' => number_format(array_sum(array_column($resumen_transacciones_groups, 'reversiones'))),
                'total_cashout_grupo' => number_format(array_sum(array_column($resumen_transacciones_groups, 'cashouts'))),
                'total_saldo_groups' => number_format(array_sum(array_column($resumen_transacciones_groups, 'saldo')))
            );
            return $resultset;

        }catch (\Exception $e){
            \Log::info($e);
                return false;
        }
    }

    public function resumenDetalladoSearchExport(){
        try{
           
            $input = $this->input;
           
            /*Busqueda minusiosa*/
            /*SET DATE RANGE*/
            $whereMovements= '';
            if(isset($input['context']) && $input['context'] <> '' && $input['context']<> null){
                if(isset($input['reservationtime']) && $input['reservationtime'] != '0' ){
                    $daterange = explode(' - ',  str_replace('/','-',$input['reservationtime']));
                    $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                    $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                    //$whereMovements = "movements.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereSales = "WHEN movement_type_id = 1 AND mt_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $wherePaquetigos = "WHEN movement_type_id = 6 AND mt_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $wherePersonal = "WHEN movement_type_id = 13 AND mt_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereClaro = "WHEN movement_type_id = 14 AND mt_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $wherePagoCash = "WHEN movement_type_id = 12 AND mt_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";

                    $whereCobranzas = "WHEN movement_type_id = 2 AND m.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereReversion = "WHEN movement_type_id = 3 AND m.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereCashout = "WHEN movement_type_id = 11 AND m.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereTransactions = "t.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereCobros = "m.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                }else{
                    $whereSales = "WHEN movement_type_id = 1 AND m.created_at <= now()";
                    $wherePaquetigos = "WHEN movement_type_id = 6 AND m.created_at <= now()";
                    $wherePersonal = "WHEN movement_type_id = 13 AND m.created_at <= now()";
                    $whereClaro = "WHEN movement_type_id = 14 AND m.created_at <= now()";
                    $wherePagoCash = "WHEN movement_type_id = 12 AND m.created_at <= now()";
                    //$whereSales = "WHEN debit_credit = 'de' AND movements.created_at <= now() ";
                    $whereCobranzas = "WHEN movement_type_id = 2 AND m.created_at <= now() ";
                    $whereReversion = "WHEN movement_type_id = 3 AND m.created_at <= now() ";
                    $whereCashout   = "WHEN movement_type_id = 11 AND m.created_at <= now() ";

                    $whereTransactions = "WHEN debit_credit = 'de' AND m.created_at <= now() ";
                }
                $whereMovements .= " LOWER(bg.description) like LOWER('%{$input['context']}%') AND";
            }else{
                if(isset($input['reservationtime']) && $input['reservationtime'] != '0' ){
                    $daterange = explode(' - ',  str_replace('/','-',$input['reservationtime']));
                    $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                    $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                    //$whereMovements = "movements.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereSales = "WHEN movement_type_id = 1 AND mt_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $wherePaquetigos = "WHEN movement_type_id = 6 AND mt_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $wherePersonal = "WHEN movement_type_id = 13 AND mt_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereClaro = "WHEN movement_type_id = 14 AND mt_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $wherePagoCash = "WHEN movement_type_id = 12 AND mt_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";

                    $whereCobranzas = "WHEN movement_type_id = 2 AND m.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereReversion = "WHEN movement_type_id = 3 AND m.created_at  BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereCashout   = "WHEN movement_type_id = 11 AND m.created_at  BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";

                    $whereTransactions = "t.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                    $whereCobros = "m.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                }else{
                    $whereSales = "WHEN movement_type_id = 1 AND m.created_at <= now()";
                    $wherePaquetigos = "WHEN movement_type_id = 6 AND m.created_at <= now()";
                    $wherePersonal = "WHEN movement_type_id = 13 AND m.created_at <= now()";
                    $whereClaro = "WHEN movement_type_id = 14 AND m.created_at <= now()";
                    $wherePagoCash = "WHEN movement_type_id = 12 AND m.created_at <= now()";

                    $whereCobranzas = "WHEN movement_type_id = 2 AND m.created_at <= now() ";
                    $whereReversion = "WHEN movement_type_id = 3 AND m.created_at <= now() ";
                    $whereCashout   = "WHEN movement_type_id = 11 AND m.created_at <= now() ";

                    $whereTransactions = "WHEN debit_credit = 'de' AND m.created_at <= now() ";
                }
                if(\Sentinel::getUser()->inRole('supervisor_miniterminal')){
                    $supervisor = \DB::table('users_x_groups')->where('user_id',$this->user->id)->first();

                    $whereMovements .= "business_groups.id = ". $supervisor->group_id . " AND";
                    $whereTransactions .= " AND branches.group_id = ". $supervisor->group_id;

                }
            }

            if(isset($input['reservationtime']) && $input['reservationtime'] != '0' ){
                $resumen_transacciones = \DB::select("
                    select
                        -sum(abs(t.amount)) as total,
                        atms.name as atm_id 
                    from
                        transactions t
                    inner join atms on
                        atms.id = t.atm_id
                    where
                    ( 
                        status in ('success', 'error') 
                        and t.service_id in(14, 15) 
                        and t.transaction_type in (1)
                        and atms.owner_id in (16, 21, 25)
                        and t.service_source_id=8
                        and ".$whereTransactions.")
                    or ( 
                        status = 'success'
                        and t.transaction_type in (1)
                        and atms.owner_id in (16, 21, 25)
                        and ".$whereTransactions."
                        )
                    group by atms.id;
                ");
            }else{

                $resumen_transacciones = \DB::select("
                    select
                        atms.id as atm_id,
                        atms.name as atm_name,
                        SUM(CASE ".$whereTransactions." THEN (m.amount) else 0 END) as transacciones
                    from mt_movements m
                    inner join atms on atms.id = m.atm_id
                    where
                        ".$whereMovements."
                        m.movement_type_id not in (4, 5, 7, 8)
                        and m.deleted_at is null
                    group by atms.id
                ");
            }
            

            $resumen_transacciones_groups = \DB::select("
                select
                        bg.id as group_id,
                        bg.ruc as ruc,
                        bg.description as grupo,
                        SUM(CASE ".$whereSales." THEN (m.amount) else 0 END) as transacciones,
                        SUM(CASE ".$wherePaquetigos." THEN (m.amount) else 0 END) as paquetigos,
                        SUM(CASE ".$wherePersonal." THEN (m.amount) else 0 END) as personal,
                        SUM(CASE ".$whereClaro." THEN (m.amount) else 0 END) as claro,
                        SUM(CASE ".$wherePagoCash." THEN (m.amount) else 0 END) as pago_cash,
                        SUM(CASE ".$whereCobranzas." THEN (m.amount) else 0 END) as depositos,
                        SUM(CASE ".$whereReversion." THEN (m.amount) else 0 END) as reversiones,
                        SUM(CASE ".$whereCashout." THEN (m.amount) else 0 END) as cashouts,
                        (   (SUM(CASE ".$whereSales." THEN (m.amount) else 0 END))
                            +(SUM(CASE ".$wherePaquetigos." THEN (m.amount) else 0 END))
                            +(SUM(CASE ".$wherePersonal." THEN (m.amount) else 0 END))
                            +(SUM(CASE ".$whereClaro." THEN (m.amount) else 0 END))
                            +(SUM(CASE ".$wherePagoCash." THEN (m.amount) else 0 END))
                            +(SUM(CASE ".$whereCobranzas." THEN (m.amount) else 0 END))
                            +(SUM(CASE ".$whereReversion." THEN (m.amount) else 0 END))
                            +(SUM(CASE ".$whereCashout." THEN (m.amount) else 0 END))
                        ) as saldo
                from mt_movements m
                inner join business_groups bg on bg.id = m.group_id
                left join mt_sales on m.id = mt_sales.movements_id
                where
                    ".$whereMovements."
                    m.movement_type_id not in (4, 5, 7, 8)
                    and m.deleted_at is null
                group by bg.id
                order by saldo desc
            ");

            $resultset = array(
                'transacciones_groups'          => $resumen_transacciones_groups,
                //'saldo_groups'                  => $saldo_groups,
                'transacciones'                 => $resumen_transacciones,
                //'saldo'                         => $saldo,
            );

            return $resultset;

        }catch (\Exception $e){
            \Log::info($e);
                return false;
        }
    }

    public function getReversionsForGroups($group_id, $fecha){

        if(isset($fecha) && $fecha != '0' && $fecha != '2' ){
            $daterange = explode('-',  str_replace('/','-',$fecha));
            $daterange[0] = date('Y-m-d H:i:s', ($daterange[0]/1000));
            $daterange[1] = date('Y-m-d H:i:s',  ($daterange[1]/1000));
            $whereTransactions = "mt_recibos_reversiones.fecha_reversion BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
        }else{
            if($fecha != '0'){
                $date=date('N');
                        
                if($date == 1 || $date==3 ||$date==5){
                    $hasta=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-1 days');
                }else if($date == 2 || $date==4 ||$date==6){
                    $hasta=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-2 days');
                }else{
                    $hasta=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-3 days');
                }

                $whereTransactions = "mt_recibos_reversiones.fecha_reversion <= '". $hasta . "'";
            }else{
                $whereTransactions = "mt_recibos_reversiones.fecha_reversion <= now()";
            }
        }
            $whereTransactions .= " AND business_groups.id = ". $group_id;

            $usersId = \DB::table('users')
                    ->join('role_users', 'users.id', '=', 'role_users.user_id')
                    ->where('role_users.role_id', 22)
                    ->pluck('id', 'id');

            $user_id = '('.implode(',', $usersId).')';

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
            ->groupBy('transactions.service_source_id','marcas.descripcion', 'business_groups.description')
            ->get();

            \Log::info(json_decode(json_encode($reversiones), true));

        $details = '';
        foreach ($reversiones as $reversion) {
            $details .='<tr>
              <td>'. $reversion->descripcion .'</td>
              <td>'. number_format($reversion->total, 0) .'</td>
              </tr>';
        }

        return $details;
    }

    public function getCashoutsForGroups($group_id, $fecha){

        if(isset($fecha) && $fecha != '0' && $fecha != '2' ){
            $daterange = explode('-',  str_replace('/','-',$fecha));
            $daterange[0] = date('Y-m-d H:i:s', ($daterange[0]/1000));
            $daterange[1] = date('Y-m-d H:i:s',  ($daterange[1]/1000));
            $whereTransactions = "transactions.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
        }else{
            $whereTransactions = "transactions.created_at <= now()";
        }
            $whereTransactions .= " AND business_groups.id = ". $group_id;

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
            ->groupBy('transactions.service_source_id','transactions.service_id', 'marcas.descripcion', 'business_groups.description')
            ->get();

            \Log::info(json_decode(json_encode($cashouts), true));

        $details = '';
        foreach ($cashouts as $cashout) {
            if(is_null($cashout->descripcion)){

                if($cashout->service_source_id == 0){
                    $cashout->service_source_id=9;
                }

                $marca = \DB::table('marcas')
                ->selectRaw('marcas.id, marcas.descripcion')
                ->join('servicios_x_marca', 'marcas.id', '=', 'servicios_x_marca.marca_id')           
                ->where('servicios_x_marca.service_id', $cashout->service_id)
                ->where('servicios_x_marca.service_source_id', $cashout->service_source_id)
                ->first();

                $cashout->descripcion= $marca->descripcion;
            }
            
            $details .='<tr>
                            <td>'. $cashout->descripcion .'</td>
                            <td>'. number_format($cashout->total, 0) .'</td>
                        </tr>';
            
        }

        

        return $details;
    }

    public function cobranzasReports(){
        try{

            if(!\Sentinel::getUser()->inRole('mini_terminal') ){
                $groups = \DB::table('business_groups')
                    ->select(['business_groups.description', 'business_groups.ruc', 'business_groups.id'])
                    ->whereNull('deleted_at')
                    ->whereNotNull('ruc')
                ->get();
            }

            $receipts_types = \DB::table('movement_type')
                ->select(['description', 'id'])
                ->whereIn('id', [2, 3, 5, 8, 11, 17])
                ->orderBy('id', 'asc')
            ->pluck('description', 'id');

            $data_select = [];
            foreach ($groups as $key => $group) {
                $data_select[$group->id] = $group->description.' | '.$group->ruc;
            }

            $resultset = array(
                'target' => 'Cobranzas',
                'groups' => $data_select,
                'group_id' => 0,
                'receipts_types' => $receipts_types,
                'receipt_type' => 0
            );
            //dd($resultset);
            return $resultset;
        }catch (\Exception $e){
            \Log::error("Error en la consulta de reportes" . $e);
            return false;
        }
    }

    public function cobranzasSearch($request){
        try{
            $input = $this->input;
     

            /*SET DATE RANGE*/
            $daterange = explode(' - ',  str_replace('/','-',$input['reservationtime']));
            $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
            $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
            $where = "m.debit_credit = 'cr' AND ";
            $where .= "m.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";

            $receipts_types = \DB::table('movement_type')
                ->select(['description', 'id'])
                ->whereIn('id', [2, 3, 5, 8, 11, 17])
                ->orderBy('id', 'asc')
            ->pluck('description', 'id')->toArray();
      
             /*SET OWNER*/
            if(!\Sentinel::getUser()->inRole('mini_terminal') && !\Sentinel::getUser()->inRole('supervisor_miniterminal')){
                
                $groups = \DB::table('business_groups')
                    ->select(['business_groups.description', 'business_groups.ruc', 'business_groups.id'])
                    ->whereNull('deleted_at')
                    ->whereNotNull('ruc')
                ->get();

                $data_select = [];
                foreach ($groups as $key => $group) {
                    $data_select[$group->id] = $group->description.' | '.$group->ruc;
                }

                if( $input['group_id'] != "" ){
                    $where .= "AND m.group_id = ".$input['group_id'] ; 
                }

            }elseif (\Sentinel::getUser()->inRole('supervisor_miniterminal')){
                $supervisor = \DB::table('users_x_groups')->where('user_id',$this->user->id)->first();
                $groupId = $supervisor->group_id;
                $where .= "AND m.group_id =  ". $groupId." ";

                $data_select = [];

            }

            if( $input['receipt_type'] != "" ){
                $where .= " AND m.movement_type_id = ".$input['receipt_type'] ; 
            }

            //dd($where);
            
            $transactions = \DB::table('mt_movements as m')
                ->select(['m.id', 'bg.description', 'm.created_at', 'm.destination_operation_id', 'mr.recibo_nro', 'm.amount', 'mt.description as tipo_recibo', 'm.response'])
                ->join('mt_recibos as mr','mr.mt_movements_id','=','m.id')
                ->join('business_groups as bg', 'bg.id', '=', 'm.group_id')
                ->join('movement_type as mt', 'mt.id', '=', 'm.movement_type_id')         
                ->whereNull('m.deleted_at')
                ->whereRaw("$where")
                ->orderBy('m.created_at','desc')
                ->orderBy('mr.recibo_nro','desc')
            ->get();
            //dd($transactions);

            foreach($transactions as $transaction){
                if($transaction->destination_operation_id < 1000){
                    $transaction->response = (isset($transaction->response)) ? json_decode($transaction->response,  true) : null;
                    if(!is_null($transaction->response)){
                        $status=(isset($transaction->response['status'])) ? $transaction->response['status']: null;
                        $transaction->destination_operation_id = (!is_null($status)) ? $status : 'Pendiente';
                    }else{
                        $transaction->destination_operation_id= 'Pendiente';
                    }
                }
                //dd($transaction->destination_operation_id);
            }
            
            $results = $this->arrayPaginator($transactions->toArray(), $request);
            
            /*Carga datos del formulario*/
            $resultset = array(
                'target' => 'Cobranzas',
                'reservationtime' => (isset($input['reservationtime'])?$input['reservationtime']:0),
                'groups' => $data_select,
                'i'             =>  1,
                'group_id' => (isset($input['group_id'])) ? $input['group_id'] : '',
                'receipts_types' => $receipts_types,
                'receipt_type' => (isset($input['receipt_type'])) ? $input['receipt_type'] : '',
                'transactions' => $transactions->toArray(),
            );

   
            return $resultset;

        }catch (\Exception $e){
            \Log::info($e);
                return false;
        }

    }

    public function cobranzasSearchExport(){
        try{

            $input = $this->input;
                     
            /*SET DATE RANGE*/
            $daterange = explode(' - ',  str_replace('/','-',$input['reservationtime']));
            $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
            $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
            $where = "m.debit_credit = 'cr' AND ";
            $where .= "m.created_at BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
             /*SET OWNER*/
            if(!\Sentinel::getUser()->inRole('mini_terminal') && !\Sentinel::getUser()->inRole('supervisor_miniterminal')){

                if( $input['group_id'] != "" ){
                    $where .= "AND m.group_id = ".$input['group_id'] ; 
                }

            }elseif (\Sentinel::getUser()->inRole('supervisor_miniterminal')){
                $supervisor = \DB::table('users_x_groups')->where('user_id',$this->user->id)->first();
                $groupId = $supervisor->group_id;
                $where .= "AND m.group_id =  ". $groupId." ";

            }

            if( $input['receipt_type'] != "" ){
                $where .= " AND m.movement_type_id = ".$input['receipt_type'] ; 
            }

            $transactions = \DB::table('mt_movements as m')
                ->select(['m.id', 'bg.description', 'm.created_at', 'm.destination_operation_id', 'mr.recibo_nro', 'm.amount', 'mt.description as tipo_recibo', 'm.response'])
                ->join('mt_recibos as mr','mr.mt_movements_id','=','m.id')
                ->join('business_groups as bg', 'bg.id', '=', 'm.group_id')
                ->join('movement_type as mt', 'mt.id', '=', 'm.movement_type_id')         
                ->whereNull('m.deleted_at')
                ->whereRaw("$where")
                ->orderBy('m.created_at','desc')
                ->orderBy('mr.recibo_nro','desc')
            ->get();

            foreach($transactions as $transaction){
                if($transaction->destination_operation_id < 1000){
                    $transaction->response = (isset($transaction->response)) ? json_decode($transaction->response,  true) : null;
                    if(!is_null($transaction->response)){
                        $status=(isset($transaction->response['status'])) ? $transaction->response['status']: null;
                        $transaction->destination_operation_id = (!is_null($status)) ? $status : 'Pendiente';
                    }else{
                        $transaction->destination_operation_id= 'Pendiente';
                    }
                }
                //dd($transaction->destination_operation_id);
                unset($transaction->response);
            }

            $resultset = array(
                'transactions' => $transactions
            );

            return $resultset;

        }catch (\Exception $e){
            \Log::info($e);
                return false;
        }

    }

    public function salesReports(){

        try{
            if(!\Sentinel::getUser()->inRole('mini_terminal')){
                $groups = \DB::table('business_groups')
                    ->select(['business_groups.description', 'business_groups.ruc', 'business_groups.id'])
                    ->whereNotNull('ruc')
                    ->whereNull('deleted_at')
                ->get();
            }

            $data_select = [];
            foreach ($groups as $key => $group) {
                $data_select[$group->id] = $group->description.' | '.$group->ruc;
            }

            $resultset = array(
                'target' => 'Ventas',
                'groups' => $data_select,
                'group_id' => 0,
                'mostrar' => 'todos',
            );

            return $resultset;

        }catch (\Exception $e){
            \Log::error("Error en la consulta de reportes" . $e);
            return false;
        }
    }

    public function salesSearch($request){
        try{
            $input = $this->input;
             /*SET DATE RANGE*/
            $daterange = explode(' - ',  str_replace('/','-',$input['reservationtime']));
            $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
            $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
            $where = "mt_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
            
            /*SET OWNER*/

            if(!\Sentinel::getUser()->inRole('mini_terminal') && !\Sentinel::getUser()->inRole('supervisor_miniterminal')){
                if( $input['group_id'] != "" ){
                    $where .= " AND m.group_id = ".$input['group_id'].""; 
                }

                if( $input['mostrar'] != "todos" ){
                    
                    if($input['mostrar'] == "transacciones"){
                        $movement_type=1;
                    }
                    if($input['mostrar'] == "paquetigos"){
                        $movement_type=6;
                    }
                    if($input['mostrar'] == "cashouts"){
                        $movement_type=12;
                    }
                    if($input['mostrar'] == "personal"){
                        $movement_type=13;
                    }
                    if($input['mostrar'] == "claro"){
                        $movement_type=14;
                    }
                    if($input['mostrar'] == "multa"){
                        $movement_type=18;
                    }

                    $where .= " AND m.movement_type_id = ".$movement_type.""; 
                }

                $groups = \DB::table('business_groups')
                    ->select(['business_groups.description', 'business_groups.ruc', 'business_groups.id'])
                    ->whereNotNull('ruc')
                    ->whereNull('deleted_at')
                ->get();
                
                $data_select = [];
                foreach ($groups as $key => $group) {
                    $data_select[$group->id] = $group->description.' | '.$group->ruc;
                }

            }else if(\Sentinel::getUser()->inRole('supervisor_miniterminal')){
                $supervisor = \DB::table('users_x_groups')->where('user_id',$this->user->id)->first();
                $groupId = $supervisor->group_id;
                $where .= " AND m.group_id = ". $groupId." ";

                if( $input['mostrar'] != "todos" ){
                    
                    if($input['mostrar'] == "transacciones"){
                        $movement_type=1;
                    }
                    if($input['mostrar'] == "paquetigos"){
                        $movement_type=6;
                    }
                    if($input['mostrar'] == "cashouts"){
                        $movement_type=12;
                    }
                    if($input['mostrar'] == "personal"){
                        $movement_type=13;
                    }
                    if($input['mostrar'] == "claro"){
                        $movement_type=14;
                    }
                    if($input['mostrar'] == "multa"){
                        $movement_type=18;
                    }

                    $where .= " AND m.movement_type_id = ".$movement_type.""; 
                }

                $data_select = [];
            }
            
            $transactions = \DB::table('mt_movements as m')
                ->select(['m.id', 'bg.description', 'm.amount', 'mt_sales.fecha', 'm.destination_operation_id', 'mt_sales.nro_venta', 'mt_sales.estado', 'mt_sales.monto_por_cobrar'])
                ->join('mt_sales', 'mt_sales.movements_id', '=', 'm.id')
                ->join('business_groups as bg', 'bg.id', '=', 'm.group_id')
                ->whereNull('m.deleted_at')
                ->whereRaw("$where")
                ->orderBy('mt_sales.fecha','desc')
                ->orderBy('mt_sales.nro_venta','desc')
            ->get();

            $total_monto=array_sum(array_column($transactions->toArray(), 'amount'));
            $total_monto_por_cobrar=array_sum(array_column($transactions->toArray(), 'monto_por_cobrar'));
            //dd($total_monto);
            $results = $this->arrayPaginator($transactions->toArray(), $request);

            /*Carga datos del formulario*/
            $resultset = array(
                'target' => 'Ventas',
                'total_monto' => $total_monto,
                'total_monto_por_cobrar' => $total_monto_por_cobrar,
                'reservationtime' => (isset($input['reservationtime'])?$input['reservationtime']:0),
                'groups' => $data_select,
                'i'             =>  1,
                'group_id' => (isset($input['group_id'])) ? $input['group_id'] : null,
                'transactions' => $results,
                'mostrar' => $input['mostrar'],
            );

            return $resultset;

        }catch (\Exception $e){
            \Log::info($e);
                return false;
        }

    }

    public function salesSearchExport(){
        try{
            $input = $this->input;

            /*SET DATE RANGE*/
           $daterange = explode(' - ',  str_replace('/','-',$input['reservationtime']));
           $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
           $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));

           $where = "mt_sales.fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";

           /*SET OWNER*/

           if( $input['mostrar'] != "todos" ){
                    
                if($input['mostrar'] == "transacciones"){
                    $movement_type=1;
                }
                if($input['mostrar'] == "paquetigos"){
                    $movement_type=6;
                }
                if($input['mostrar'] == "cashouts"){
                    $movement_type=12;
                }
                if($input['mostrar'] == "personal"){
                    $movement_type=13;
                }
                if($input['mostrar'] == "claro"){
                    $movement_type=14;
                }
                if($input['mostrar'] == "multa"){
                    $movement_type=18;
                }

                $where .= " AND m.movement_type_id = ".$movement_type.""; 
            }

           if(!\Sentinel::getUser()->inRole('mini_terminal') && !\Sentinel::getUser()->inRole('supervisor_miniterminal')){
               if( $input['group_id'] != "" ){
                   $where .= " and bg.id = ".$input['group_id'].""; 
               }

               $groups = \DB::table('business_groups')
                    ->select(['business_groups.description', 'business_groups.ruc', 'business_groups.id'])
                    ->whereNotNull('ruc')
                    ->whereNull('deleted_at')
                ->get();
                
                $data_select = [];
                foreach ($groups as $key => $group) {
                    $data_select[$group->id] = $group->description.' | '.$group->ruc;
                }

           }else if(\Sentinel::getUser()->inRole('supervisor_miniterminal')){
               $supervisor = \DB::table('users_x_groups')->where('user_id',$this->user->id)->first();
               $groupId = $supervisor->group_id;
               $where .= " AND bg.id = ". $groupId." ";

               $data_select = [];
           }

           $transactions = \DB::table('mt_movements as m')
               ->select(['m.id', 'bg.description', 'm.amount', 'mt_sales.fecha', 'm.destination_operation_id', 'mt_sales.nro_venta', 'mt_sales.estado', 'mt_sales.monto_por_cobrar'])
               ->join('mt_sales', 'mt_sales.movements_id', '=', 'm.id')
               ->join('business_groups as bg', 'bg.id', '=', 'm.group_id')
               ->whereNull('m.deleted_at')
               ->whereRaw("$where")
               ->orderBy('mt_sales.fecha','desc')
               ->orderBy('mt_sales.nro_venta','desc')
               ->get();

            //dd($transactions);
            /*Carga datos del formulario*/
            $resultset = array(
                'transactions' => $transactions
            );

            return $resultset;

        }catch (\Exception $e){
            \Log::info($e);
                return false;
        }

    }

    public function boletaDepositosReports($request){
        try{

            $desde=Carbon::today();
            $hasta=Carbon::tomorrow()->modify('-1 seconds');
        
            $boletas = \DB::table('boletas_depositos')
                ->select([
                    'boletas_depositos.id',
                    'fecha',
                    'bancos.descripcion as banco',
                    'cuentas_bancarias.numero_banco as cuenta_bancaria',
                    'boleta_numero',
         
                    //'monto'
                    \DB::raw("(case when monto_anterior is not null then monto_anterior else monto end) as monto"),

                    'user_id',
                    'tipo_pago.descripcion as tipo_pago',
                    'estado',
                    'boletas_depositos.deleted_at',
                    'boletas_depositos.updated_at',
                    'boletas_depositos.updated_by',
                    'users.username as username',
                    'boletas_depositos.message',
                    'boletas_depositos.atm_id',
                    'boletas_depositos.imagen_asociada',
                    'atms.name as name_atm'
                ])                
                ->join('cuentas_bancarias', 'cuentas_bancarias.id', '=', 'boletas_depositos.cuenta_bancaria_id')
                ->join('bancos','bancos.id','=','cuentas_bancarias.banco_id')
                ->join('tipo_pago', 'tipo_pago.id', '=', 'boletas_depositos.tipo_pago_id')
                ->leftjoin('atms','atms.id','=','boletas_depositos.atm_id')
                ->leftjoin('users', 'users.id', '=', 'boletas_depositos.updated_by')
                ->whereRaw("fecha BETWEEN '{$desde}' AND '{$hasta}'")
                ->where(function($query){
                    if(!\Sentinel::getUser()->inRole('mini_terminal') && !\Sentinel::getUser()->inRole('supervisor_miniterminal') ){
                        $query->where('estado', true);
                    }else if(\Sentinel::getUser()->inRole('supervisor_miniterminal')){

                        $atms = \DB::table('atms')
                            ->select('atms.id')
                            ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                            ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                            ->join('users_x_groups', 'branches.group_id', '=', 'users_x_groups.group_id')
                            ->where('users_x_groups.user_id', $this->user->id)
                            ->whereIn('atms.owner_id', [16, 21, 25])
                            ->orderBy('atms.id', 'asc')
                        ->pluck('atms.id');

                        $query->whereIn('boletas_depositos.atm_id', $atms);
                    }else{
                        $atms = \DB::table('atms')
                            ->select('atms.id')
                            ->join('atms_per_users', 'atms.id', '=', 'atms_per_users.atm_id')
                            ->where('atms_per_users.user_id', $this->user->id)
                            ->whereIn('atms.owner_id', [16, 21, 25])
                            ->orderBy('atms.id', 'asc')
                        ->pluck('atms.id');
                        //$query->where('boletas_depositos.user_id', $this->user->id);
                        $query->whereIn('boletas_depositos.atm_id', $atms);
                    }
                })
                ->orderBy('boletas_depositos.id', 'desc')
            ->get();
       
            $results = $this->arrayPaginator($boletas, $request);
         
            $resultset = array(
                'target' => 'Depositos Miniterminales',
                'transactions' => $results,
                'reservationtime'=> (isset($input['reservationtime'])?$input['reservationtime']:0)
            );

       

            if(!\Sentinel::getUser()->inRole('mini_terminal') && !\Sentinel::getUser()->inRole('supervisor_miniterminal')){
                 
                $atms = \DB::table('atms')
                    ->selectRaw('atms.id as atm_id, atms.name, bg.*')
                    ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                    ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                    ->join('business_groups as bg', 'bg.id', '=', 'branches.group_id')
                    ->whereIn('atms.owner_id', [16, 21, 25])
                    ->whereNull('bg.deleted_at')
                ->get();

                $data_select = [];

                foreach ($atms as $key => $atm) {
                    $data_select[$atm->atm_id] = $atm->name.' | '.$atm->ruc . ' | ' .$atm->description;
                }
                
                $resultset['data_select'] = $data_select;
                
            }else{
                if(\Sentinel::getUser()->inRole('supervisor_miniterminal')){
                    $supervisor = \DB::table('users_x_groups')->where('user_id',$this->user->id)->first();
    
                    $atms = \DB::table('atms')
                        ->selectRaw('atms.id as atm_id, atms.name, bg.*')
                        ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                        ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                        ->join('business_groups as bg', 'bg.id', '=', 'branches.group_id')
                        ->where('bg.id', $supervisor->group_id)
                        ->whereIn('atms.owner_id', [16, 21, 25])
                        ->whereNull('bg.deleted_at')
                    ->get();

                    $data_select = [];

                    foreach ($atms as $key => $atm) {
                        $data_select[$atm->atm_id] = $atm->name.' | '.$atm->ruc . ' | ' .$atm->description;
                    }
                    
                    $resultset['data_select'] = $data_select;
                }else{
    
                    $atms = \DB::table('atms')
                        ->selectRaw('atms.id as atm_id, atms.name')
                        ->join('atms_per_users', 'atms.id', '=', 'atms_per_users.atm_id')
                        ->where('atms_per_users.user_id', $this->user->id)
                        ->whereIn('atms.owner_id', [16, 21, 25])
                        ->orderBy('atms.id', 'asc')
                    ->get();

                    $data_select = [];

                    foreach ($atms as $key => $atm) {
                        $data_select[$atm->atm_id] = $atm->name;
                    }
                    
                    $resultset['data_select'] = $data_select;
                }
            }

            $groups = Group::pluck('description', 'id')->toArray();
            
            $status = array('0'=>'Todos','1'=>'Confirmados','2'=>'Rechazados','3'=>'Pendientes');

            /*$resultset['usersNames']    = $usersNames;
            $resultset['branches']      = $branches;*/
            $resultset['data_select']   = $data_select;
            $resultset['groups']        = $groups;
            $resultset['group_id']      = 0;
            $resultset['status']        = $status;
            $resultset['status_set']    = 0;
            //$resultset['user_id']       = '';
            $resultset['atm_id']        = '';
            $resultset['groups']        = $groups;
            $resultset['group_id']      = 0;
            
            return $resultset;

        }catch (\Exception $e){
            \Log::error("Error en la consulta de reportes" . $e);
            return false;
        }
    }

    public function boletaDepositosSearch($request){
        try{
            $input = $this->input;
            //dd($input);
                /*Busqueda minusiosa*/
            if(isset($input['context']) && $input['context'] <> '' && $input['context']<> null){
                $where = "boleta_numero like '%{$input['context']}%' ";
            }else{
                /*SET DATE RANGE*/
                if(isset($input['reservationtime'])){
                    $daterange = explode(' - ',  str_replace('/','-',$input['reservationtime']));
                    $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                    $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                    $where = "fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                }

                if($input['status_id'] != 0){
                    if($input['status_id'] == '1'){
                        $where .= " AND estado is true";
                    }
                    if($input['status_id'] == '2'){
                        $where .= " AND estado is false";
                    }
                    if($input['status_id'] == '3'){
                        $where .= " AND estado is null";
                    }
                }
            }
            
            if(!empty($input['group_id'])){

                $group_id = $input['group_id'];

                
                if( $group_id !=0 ){

                    if(!empty($input['atm_id'])){
                        $atm_id = $input['atm_id'];
                    }else{

                        $atms = \DB::table('atms')
                            ->select('atms.id')
                            ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                            ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                            ->where('branches.group_id', $group_id)
                            ->whereIn('atms.owner_id', [16, 21, 25])
                            ->orderBy('atms.id', 'asc')
                        ->pluck('atms.id');

                        $atm=implode(',', $atms);

                        $atm_id= $atm;
                    }

                }else{
                    if(!empty($input['atm_id'])){
                        $atm_id = $input['atm_id'];
                    }else{
                        
                        $atm_id=$input['atm_id'];
                    }
                }
            }else{
                if(!empty($input['atm_id'])){
                    $atm_id = $input['atm_id'];
                }else{
                    if(\Sentinel::getUser()->inRole('mini_terminal')){

                        $atms = \DB::table('atms')
                            ->selectRaw('DISTINCT atms.id')
                            ->join('atms_per_users', 'atms.id', '=', 'atms_per_users.atm_id')
                            ->where('atms_per_users.user_id', $this->user->id)
                            ->whereIn('atms.owner_id', [16, 21, 25])
                            ->orderBy('atms.id', 'asc')
                        ->pluck('atms.id');
                        //dd($atms);
                        $atm_id=implode(',', $atms);
                    }else{
                        $atm_id= $input['atm_id'];
                    }
                }
            }

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
                    'boletas_depositos.message',
                    'boletas_depositos.atm_id',
                    'boletas_depositos.imagen_asociada',
                    'atms.name as name_atm'
                ])                               
                ->join('cuentas_bancarias', 'cuentas_bancarias.id', '=', 'boletas_depositos.cuenta_bancaria_id')
                ->join('bancos','bancos.id','=','cuentas_bancarias.banco_id')
                ->join('tipo_pago', 'tipo_pago.id', '=', 'boletas_depositos.tipo_pago_id')
                ->leftjoin('atms','atms.id','=','boletas_depositos.atm_id')
                ->leftjoin('users', 'users.id', '=', 'boletas_depositos.updated_by')
                ->whereRaw("$where")
                ->where(function($query) use($atm_id){
                    if(!empty($atm_id)){
                        $query->whereRaw('boletas_depositos.atm_id in ('. $atm_id.')');
                    }else{
                        if(\Sentinel::getUser()->inRole('supervisor_miniterminal')){
                            $atms = \DB::table('atms')
                                ->select('atms.id')
                                ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                                ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                                ->join('users_x_groups', 'branches.group_id', '=', 'users_x_groups.group_id')
                                ->where('users_x_groups.user_id', $this->user->id)
                                ->whereIn('atms.owner_id', [16, 21, 25])
                                ->orderBy('atms.id', 'asc')
                            ->pluck('atms.id');

                            $query->whereIn('boletas_depositos.atm_id', $atms);

                        }else if(\Sentinel::getUser()->inRole('mini_terminal')){
                            $atms = \DB::table('atms')
                                ->select('atms.id')
                                ->join('atms_per_users', 'atms.id', '=', 'atms_per_users.atm_id')
                                ->where('atms_per_users.user_id', $this->user->id)
                                ->whereIn('atms.owner_id', [16, 21, 25])
                                ->orderBy('atms.id', 'asc')
                            ->pluck('atms.id');
                            //$query->where('boletas_depositos.user_id', $this->user->id);
                            $query->whereIn('boletas_depositos.atm_id', $atms);
                        }
                    }
                })
                ->orderBy('boletas_depositos.id', 'desc')
            ->get();
            
            $results = $this->arrayPaginator($boletas, $request);

            $resultset = array(
                'target'        => 'Depositos Miniterminales',
                'transactions'  => $results,
                'reservationtime' => (isset($input['reservationtime'])?$input['reservationtime']:0),
                'i'             =>  1,
            );

            if(!\Sentinel::getUser()->inRole('mini_terminal') && !\Sentinel::getUser()->inRole('supervisor_miniterminal')){
                 
                $atms = \DB::table('atms')
                    ->selectRaw('atms.id as atm_id, atms.name, bg.*')
                    ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                    ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                    ->join('business_groups as bg', 'bg.id', '=', 'branches.group_id')
                    ->whereIn('atms.owner_id', [16, 21, 25])
                    ->whereNull('bg.deleted_at')
                ->get();

                $data_select = [];

                foreach ($atms as $key => $atm) {
                    $data_select[$atm->atm_id] = $atm->name.' | '.$atm->ruc . ' | ' .$atm->description;
                }
                
                $resultset['data_select'] = $data_select;
                
            }else{
                if(\Sentinel::getUser()->inRole('supervisor_miniterminal')){
                    $supervisor = \DB::table('users_x_groups')->where('user_id',$this->user->id)->first();
    
                    $atms = \DB::table('atms')
                        ->selectRaw('atms.id as atm_id, atms.name, bg.*')
                        ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                        ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                        ->join('business_groups as bg', 'bg.id', '=', 'branches.group_id')
                        ->where('bg.id', $supervisor->group_id)
                        ->whereIn('atms.owner_id', [16, 21, 25])
                        ->whereNull('bg.deleted_at')
                    ->get();

                    $data_select = [];

                    foreach ($atms as $key => $atm) {
                        $data_select[$atm->atm_id] = $atm->name.' | '.$atm->ruc . ' | ' .$atm->description;
                    }
                    
                    $resultset['data_select'] = $data_select;
                }else{
    
                    $atms = \DB::table('atms')
                        ->selectRaw('atms.id as atm_id, atms.name')
                        ->join('atms_per_users', 'atms.id', '=', 'atms_per_users.atm_id')
                        ->where('atms_per_users.user_id', $this->user->id)
                        ->whereIn('atms.owner_id', [16, 21, 25])
                        ->orderBy('atms.id', 'asc')
                    ->get();

                    $data_select = [];

                    foreach ($atms as $key => $atm) {
                        $data_select[$atm->atm_id] = $atm->name;
                    }
                    
                    $resultset['data_select'] = $data_select;
                }
            }
            
            $status = array('0'=>'Todos','1'=>'Confirmados','2'=>'Rechazados','3'=>'Pendientes');

            $groups = Group::pluck('description', 'id');

            /*$resultset['usersNames'] = $usersNames;
            $resultset['branches'] = $branches;*/
            $resultset['status'] = $status;
            $resultset['status_set'] = (isset($input['status_id'])?$input['status_id']:0);
            $resultset['data_select'] = $data_select;
            //$resultset['user_id'] = $input['user_id'];
            if(!empty($input['group_id'])){
                $resultset['atm_id']= $input['atm_id'];
            }else{
                $resultset['atm_id']= 0;
            }
            
            $resultset['groups'] = $groups;
            if(!empty($input['group_id'])){
                $resultset['group_id'] = $input['group_id'];
            }else{
                $resultset['group_id']= 0;
            }
            
            return $resultset;

        }catch (\Exception $e){
            \Log::info($e);
            return false;
        }
    }

    public function boletaDepositosSearchExport(){
        try{
            $input = $this->input;
            //dd($input);

              /*Busqueda minusiosa*/
            if(isset($input['context']) && $input['context'] <> '' && $input['context']<> null){
                $where = "boleta_numero like '%{$input['context']}%' ";
            }else{
                /*SET DATE RANGE*/
                if(isset($input['reservationtime'])){
                    $daterange = explode(' - ',  str_replace('/','-',$input['reservationtime']));
                    $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                    $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                    $where = "fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
                }

                if($input['status_id'] != 0){
                    if($input['status_id'] == '1'){
                        $where .= " AND estado is true";
                    }
                    if($input['status_id'] == '2'){
                        $where .= " AND estado is false";
                    }
                    if($input['status_id'] == '3'){
                        $where .= " AND estado is null";
                    }
                }
            }
            
            if(!empty($input['group_id'])){

                $group_id = $input['group_id'];
                if( $group_id !=0 ){

                    if(!empty($input['atm_id'])){
                        $atm_id = '('.$input['atm_id'].')';
                    }else{
                        $atms = \DB::table('atms')
                            ->selectRaw('DISTINCT atms.id')
                            ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                            ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                            ->where('branches.group_id', $group_id)
                            ->whereIn('atms.owner_id', [16, 21, 25])
                            ->orderBy('atms.id', 'asc')
                        ->pluck('atms.id');

                        $atm=implode(',', $atms);

                        $atm_id='('.$atm.')';
                    }

                }else{
                    if(!empty($input['atm_id'])){
                        $atm_id = '('.$input['atm_id'].')';
                    }else{
                        
                        $atm_id=$input['atm_id'];
                    }
                }
            }else{
                if(!empty($input['atm_id'])){
                    $atm_id = '('.$input['atm_id'].')';
                }else{
                    if(\Sentinel::getUser()->inRole('mini_terminal')){

                        $atms = \DB::table('atms')
                            ->selectRaw('DISTINCT atms.id')
                            ->join('atms_per_users', 'atms.id', '=', 'atms_per_users.atm_id')
                            ->where('atms_per_users.user_id', $this->user->id)
                            ->whereIn('atms.owner_id', [16, 21, 25])
                            ->orderBy('atms.id', 'asc')
                        ->pluck('atms.id');

                        $atm_id=implode(',', $atms);
                    }else{
                        $atm_id= $input['atm_id'];
                    }
                }
            }

            $boletas = \DB::table('boletas_depositos')
                ->select([
                    'boletas_depositos.id',
                    'fecha',
                    'atms.name as name_atm',
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
                ->join('bancos','bancos.id','=','cuentas_bancarias.banco_id')
                ->join('tipo_pago', 'tipo_pago.id', '=', 'boletas_depositos.tipo_pago_id')
                ->leftjoin('users', 'users.id', '=', 'boletas_depositos.user_id')
                ->leftjoin('atms','atms.id','=','boletas_depositos.atm_id')
                ->whereRaw("$where")
                ->where(function($query) use($atm_id){
                    if(!empty($atm_id)){
                        $query->whereRaw('boletas_depositos.atm_id in ('. $atm_id.')');
                    }else{
                        if(\Sentinel::getUser()->inRole('supervisor_miniterminal')){
                            $atms = \DB::table('atms')
                                ->select('atms.id')
                                ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                                ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                                ->join('users_x_groups', 'branches.group_id', '=', 'users_x_groups.group_id')
                                ->where('users_x_groups.user_id', $this->user->id)
                                ->whereIn('atms.owner_id', [16, 21, 25])
                                ->orderBy('atms.id', 'asc')
                            ->pluck('atms.id');

                            $query->whereIn('boletas_depositos.atm_id', $atms);

                        }else if(\Sentinel::getUser()->inRole('mini_terminal')){
                            $atms = \DB::table('atms')
                                ->select('atms.id')
                                ->join('atms_per_users', 'atms.id', '=', 'atms_per_users.atm_id')
                                ->where('atms_per_users.user_id', $this->user->id)
                                ->whereIn('atms.owner_id', [16, 21, 25])
                                ->orderBy('atms.id', 'asc')
                            ->pluck('atms.id');
                            //$query->where('boletas_depositos.user_id', $this->user->id);
                            $query->whereIn('boletas_depositos.atm_id', $atms);
                        }
                    }
                })
                ->orderBy('boletas_depositos.id', 'desc')
            ->get();

            foreach ($boletas as $boleta) {
                $boleta->fecha = date('d/m/Y', strtotime($boleta->fecha));
                if($boleta->estado == true){
                    $boleta->estado = 'Confirmado';
                }else if ($boleta->estado == false){
                    $boleta->estado = 'Rechazado';
                }else{
                    $boleta->estado = 'Pendiente';
                }
                if(isset($boleta->username)){
                    $user=\DB::connection('eglobalt_auth')
                    ->table('users')->where('id',$boleta->username)->first();
                    $boleta->username= $user->username;
                }
                $boleta->update = date('d/m/Y H:i:s', strtotime($boleta->update));
            }

            $transaction_details = \DB::table('boletas_depositos as bd')
                ->select('bd.id as id_boleta','bd.boleta_numero','mr.recibo_nro', 'mtc.ventas_cobradas')
                ->join('mt_recibos_cobranzas as mtc', 'bd.id', '=', 'mtc.boleta_deposito_id')
                ->join('mt_recibos as mr', 'mr.id', '=', 'mtc.recibo_id')
                ->whereRaw("$where")
                ->where(function($query) use($atm_id){
                    if(!empty($atm_id)){
                        $query->whereRaw('bd.atm_id in ('. $atm_id.')');
                    }else{
                        if(\Sentinel::getUser()->inRole('supervisor_miniterminal')){
                            $atms = \DB::table('atms')
                                ->select('atms.id')
                                ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                                ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                                ->join('users_x_groups', 'branches.group_id', '=', 'users_x_groups.group_id')
                                ->where('users_x_groups.user_id', $this->user->id)
                                ->whereIn('atms.owner_id', [16, 21, 25])
                                ->orderBy('atms.id', 'asc')
                            ->pluck('atms.id');

                            $query->whereIn('bd.atm_id', $atms);

                        }else if(\Sentinel::getUser()->inRole('mini_terminal')){
                            $atms = \DB::table('atms')
                                ->select('atms.id')
                                ->join('atms_per_users', 'atms.id', '=', 'atms_per_users.atm_id')
                                ->where('atms_per_users.user_id', $this->user->id)
                                ->whereIn('atms.owner_id', [16, 21, 25])
                                ->orderBy('atms.id', 'asc')
                            ->pluck('atms.id');
                            //$query->where('boletas_depositos.user_id', $this->user->id);
                            $query->whereIn('bd.atm_id', $atms);
                        }
                    }
                })
            ->get();

            $resultset = array(
                'transactions'          => $boletas,
                'transaction_details'   => $transaction_details
            );

            return $resultset;

        }catch (\Exception $e){
            \Log::info($e);
            return false;
        }
    }

    /** DEPOSITOS DE CUOTAS*/
    public function depositosCuotasReports($request){
        try{            
            $desde=Carbon::today();
            $hasta=Carbon::tomorrow()->modify('-1 seconds');
            
            if(!\Sentinel::getUser()->inRole('mini_terminal')){
                
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
                        'reprinted',
                        'atms.id as atm_id',
                        'atms.name as name_atm'
                    ])                
                    ->join('cuentas_bancarias', 'cuentas_bancarias.id', '=', 'mt_recibos_pagos_miniterminales.cuenta_bancaria_id')
                    ->join('bancos','bancos.id','=','cuentas_bancarias.banco_id')
                    ->join('tipo_pago', 'tipo_pago.id', '=', 'mt_recibos_pagos_miniterminales.tipo_pago_id')
                    ->leftjoin('users', 'users.id', '=', 'mt_recibos_pagos_miniterminales.updated_by')
                    ->leftjoin('atms', 'atms.id', '=', 'mt_recibos_pagos_miniterminales.atm_id')
                    ->leftjoin('mt_recibos', 'mt_recibos.id', '=', 'mt_recibos_pagos_miniterminales.recibo_id')
                    ->whereRaw("fecha BETWEEN '{$desde}' AND '{$hasta}'")
                    ->where('estado', true)
                    ->where('mt_recibos_pagos_miniterminales.tipo_recibo_id', 1)
                    ->orderBy('mt_recibos_pagos_miniterminales.id', 'desc')
                ->get();            
                //dd($boletas);
            }else{
                $atms = \DB::table('atms')
                    ->select('atms.id')
                    ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                    ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                    ->join('users_x_groups', 'branches.group_id', '=', 'users_x_groups.group_id')
                    ->where('users_x_groups.user_id', $this->user->id)
                    ->whereIn('atms.owner_id', [16, 21, 25])
                    ->orderBy('atms.id', 'asc')
                ->pluck('atms.id');

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
                        'reprinted',
                        'atms.id as atm_id',
                        'atms.name as name_atm'
                    ])                
                    ->join('cuentas_bancarias', 'cuentas_bancarias.id', '=', 'mt_recibos_pagos_miniterminales.cuenta_bancaria_id')
                    ->join('bancos','bancos.id','=','cuentas_bancarias.banco_id')
                    ->join('tipo_pago', 'tipo_pago.id', '=', 'mt_recibos_pagos_miniterminales.tipo_pago_id')
                    ->leftjoin('users', 'users.id', '=', 'mt_recibos_pagos_miniterminales.updated_by')
                    ->leftjoin('atms', 'atms.id', '=', 'mt_recibos_pagos_miniterminales.atm_id')
                    ->leftjoin('mt_recibos', 'mt_recibos.id', '=', 'mt_recibos_pagos_miniterminales.recibo_id')
                    ->whereIn('mt_recibos_pagos_miniterminales.atm_id', $atms)
                    ->whereRaw("fecha BETWEEN '{$desde}' AND '{$hasta}'")
                    ->where('estado', true)
                    ->where('mt_recibos_pagos_miniterminales.tipo_recibo_id', 1)
                    ->orderBy('mt_recibos_pagos_miniterminales.id', 'desc')
                ->get();
            }

            $atms = \DB::table('atms')
                ->selectRaw('atms.id as atm_id, atms.name, bg.*')
                ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->join('business_groups as bg', 'bg.id', '=', 'branches.group_id')
                ->whereIn('atms.owner_id', [16, 21, 25])
                ->whereNull('bg.deleted_at')
            ->get();

            $data_select = [];

            foreach ($atms as $key => $atm) {
                $data_select[$atm->atm_id] = $atm->name.' | '.$atm->ruc . ' | ' .$atm->description;
            }

            $results = $this->arrayPaginator($boletas, $request);

            $resultset = array(
                'target' => 'Depositos Cuotas Miniterminales',
                'transactions' => $results,
                'reservationtime'=> (isset($input['reservationtime'])?$input['reservationtime']:0)
            );            
            $status = array('0'=>'Todos','1'=>'Confirmados','2'=>'Rechazados','3'=>'Pendientes');

            $resultset['data_select']   = $data_select;
            $resultset['status']        = $status;
            $resultset['status_set']    = 0;
            //$resultset['user_id']       = '';
            $resultset['atm_id']       = '';
            
            return $resultset;

        }catch (\Exception $e){
            \Log::error("Error en la consulta de reportes" . $e);            
            return false;
        }
    }

    public function depositosCuotasSearch($request){
        try{
            $input = $this->input;
            
            /*Busqueda minusiosa*/
            /*SET DATE RANGE*/
            //dd($input);
            if(isset($input['reservationtime'])){
                $daterange = explode(' - ',  str_replace('/','-',$input['reservationtime']));
                $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                $where = "fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
            }

            if($input['status_id'] != 0){
                if($input['status_id'] == '1'){
                    $where .= " AND estado is true";
                }
                if($input['status_id'] == '2'){
                    $where .= " AND estado is false";
                }
                if($input['status_id'] == '3'){
                    $where .= " AND estado is null";
                }
            }
            if(!\Sentinel::getUser()->inRole('mini_terminal')){
                $atm_id = $input['atm_id'];
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
                    'reprinted',
                    'atms.id as atm_id',
                    'atms.name as name_atm'
                    ])                
                    ->join('cuentas_bancarias', 'cuentas_bancarias.id', '=', 'mt_recibos_pagos_miniterminales.cuenta_bancaria_id')
                    ->join('bancos','bancos.id','=','cuentas_bancarias.banco_id')
                    ->join('tipo_pago', 'tipo_pago.id', '=', 'mt_recibos_pagos_miniterminales.tipo_pago_id')
                    ->leftjoin('users', 'users.id', '=', 'mt_recibos_pagos_miniterminales.updated_by')
                    ->leftjoin('mt_recibos', 'mt_recibos.id', '=', 'mt_recibos_pagos_miniterminales.recibo_id')
                    ->leftjoin('atms', 'atms.id', '=', 'mt_recibos_pagos_miniterminales.atm_id')
                    ->whereRaw("$where")
                    ->where('mt_recibos_pagos_miniterminales.tipo_recibo_id', 1)   
                    ->where(function($query) use($atm_id){
                        if(!empty($atm_id)){
                            $query->where('mt_recibos_pagos_miniterminales.atm_id', $atm_id);
                        }
                    })
                    ->orderBy('mt_recibos_pagos_miniterminales.id', 'desc')
                ->get();
            }else{
                $atms = \DB::table('atms')
                    ->select('atms.id')
                    ->join('atms_per_users', 'atms.id', '=', 'atms_per_users.atm_id')
                    ->where('atms_per_users.user_id', $this->user->id)
                    ->whereIn('atms.owner_id', [16, 21, 25])
                    ->orderBy('atms.id', 'asc')
                ->pluck('atms.id');
                //$user_id = $this->user->id;
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
                    'reprinted',
                    'atms.id as atm_id',
                    'atms.name as name_atm'
                    ])                
                    ->join('cuentas_bancarias', 'cuentas_bancarias.id', '=', 'mt_recibos_pagos_miniterminales.cuenta_bancaria_id')
                    ->join('bancos','bancos.id','=','cuentas_bancarias.banco_id')
                    ->join('tipo_pago', 'tipo_pago.id', '=', 'mt_recibos_pagos_miniterminales.tipo_pago_id')
                    ->leftjoin('users', 'users.id', '=', 'mt_recibos_pagos_miniterminales.updated_by')
                    ->leftjoin('mt_recibos', 'mt_recibos.id', '=', 'mt_recibos_pagos_miniterminales.recibo_id')
                    ->leftjoin('atms', 'atms.id', '=', 'mt_recibos_pagos_miniterminales.atm_id')
                    ->whereRaw("$where")
                    ->where('mt_recibos_pagos_miniterminales.tipo_recibo_id', 1)   
                    ->whereIn('mt_recibos_pagos_miniterminales.atm_id', $atms)
                    ->orderBy('mt_recibos_pagos_miniterminales.id', 'desc')
                ->get();
            }

            $results = $this->arrayPaginator($boletas, $request);

            $resultset = array(
                'target'        => 'Depositos Cuotas Miniterminales',
                'transactions'  => $results,
                'reservationtime' => (isset($input['reservationtime'])?$input['reservationtime']:0),
                'i'             =>  1,
            );
            
            $status = array('0'=>'Todos','1'=>'Confirmados','2'=>'Rechazados','3'=>'Pendientes');

            $atms = \DB::table('atms')
                ->selectRaw('atms.id as atm_id, atms.name, bg.*')
                ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->join('business_groups as bg', 'bg.id', '=', 'branches.group_id')
                ->whereIn('atms.owner_id', [16, 21, 25])
                ->whereNull('bg.deleted_at')
            ->get();

            $data_select = [];

            foreach ($atms as $key => $atm) {
                $data_select[$atm->atm_id] = $atm->name.' | '.$atm->ruc . ' | ' .$atm->description;
            }
            
            $resultset['data_select'] = $data_select;

            $resultset['status'] = $status;
            $resultset['status_set'] = (isset($input['status_id'])?$input['status_id']:0);
            $resultset['data_select'] = $data_select;
            if(isset($input['atm_id'])){
                $resultset['atm_id'] = $atm_id;   
            }else{
                $resultset['atm_id'] = '';   
            }
                     
            return $resultset;

        }catch (\Exception $e){
            \Log::info($e);
            return false;
        }
    }

    public function depositosCuotasSearchExport(){
        try{
            $input = $this->input;
            /*Busqueda minusiosa*/
            /*SET DATE RANGE*/

            if(isset($input['reservationtime'])){
                $daterange = explode(' - ',  str_replace('/','-',$input['reservationtime']));
                $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                $where = "fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
            }

            if($input['status_id'] != 0){
                if($input['status_id'] == '1'){
                    $where .= " AND estado is true";
                }
                if($input['status_id'] == '2'){
                    $where .= " AND estado is false";
                }
                if($input['status_id'] == '3'){
                    $where .= " AND estado is null";
                }
            }

            if(!\Sentinel::getUser()->inRole('mini_terminal')){
                $atm_id = $input['atm_id'];
                $boletas = \DB::table('mt_recibos')
                ->select([
                    'mt_recibos.id', 
                    'mt_recibos_pagos_miniterminales.fecha', 
                    'atms.name as name_atm',
                    'tipo_pago.descripcion as tipo_pago',
                    'bancos.descripcion as banco',
                    'cuentas_bancarias.numero_banco as cuenta_bancaria',
                    'boleta_numero',                    
                    'mt_recibos.monto',                     
                    'estado',
                    'users.username',
                    'mt_recibos.updated_at',
                    'mt_recibos_pagos_miniterminales.message',                    
                    'mt_recibos.deleted_at',                                        
                    'mt_recibos.reprinted'
                ]) 
                ->join('mt_recibos_pagos_miniterminales', 'mt_recibos.id', '=', 'mt_recibos_pagos_miniterminales.recibo_id')
                ->join('cuentas_bancarias','mt_recibos_pagos_miniterminales.cuenta_bancaria_id','=','cuentas_bancarias.banco_id')
                ->join('bancos', 'bancos.id', '=', 'cuentas_bancarias.banco_id')
                ->join('tipo_pago','tipo_pago.id','=','mt_recibos_pagos_miniterminales.tipo_pago_id')
                ->leftjoin('users', 'users.id', '=', 'mt_recibos_pagos_miniterminales.updated_by')
                ->leftjoin('atms', 'atms.id', '=', 'mt_recibos_pagos_miniterminales.atm_id')
                ->whereRaw("$where")
                ->where('mt_recibos_pagos_miniterminales.tipo_recibo_id', 1)
                ->where(function($query) use($atm_id){
                    if(!empty($atm_id)){
                        $query->where('mt_recibos_pagos_miniterminales.atm_id', $atm_id);
                    }
                })
                ->orderBy('mt_recibos.id', 'desc')
                ->get();
            }else{
                $atms = \DB::table('atms')
                    ->select('atms.id')
                    ->join('atms_per_users', 'atms.id', '=', 'atms_per_users.atm_id')
                    ->where('atms_per_users.user_id', $this->user->id)
                    ->whereIn('atms.owner_id', [16, 21, 25])
                    ->orderBy('atms.id', 'asc')
                ->pluck('atms.id');
                
                $boletas = \DB::table('mt_recibos')
                    ->select([
                        'mt_recibos.id', 
                        'mt_recibos_pagos_miniterminales.fecha', 
                        'atms.name as name_atm',
                        'tipo_pago.descripcion as tipo_pago',
                        'bancos.descripcion as banco',
                        'cuentas_bancarias.numero_banco as cuenta_bancaria',
                        'boleta_numero',                    
                        'mt_recibos.monto',                     
                        'estado',
                        'users.username',
                        'mt_recibos.updated_at',
                        'mt_recibos_pagos_miniterminales.message',                    
                        'mt_recibos.deleted_at',                                        
                        'mt_recibos.reprinted'
                    ])
                    ->join('mt_recibos_pagos_miniterminales', 'mt_recibos.id', '=', 'mt_recibos_pagos_miniterminales.recibo_id')
                    ->join('cuentas_bancarias','mt_recibos_pagos_miniterminales.cuenta_bancaria_id','=','cuentas_bancarias.banco_id')
                    ->join('bancos', 'bancos.id', '=', 'cuentas_bancarias.banco_id')
                    ->join('tipo_pago','tipo_pago.id','=','mt_recibos_pagos_miniterminales.tipo_pago_id')
                    ->leftjoin('users', 'users.id', '=', 'mt_recibos_pagos_miniterminales.updated_by')
                    ->leftjoin('atms', 'atms.id', '=', 'mt_recibos_pagos_miniterminales.atm_id')
                    ->whereRaw("$where")
                    ->whereIn('mt_recibos_pagos_miniterminales.atm_id', $atms)
                    ->where('mt_recibos_pagos_miniterminales.tipo_recibo_id', 1)
                    ->orderBy('mt_recibos.id', 'desc')
                ->get();
            }

            foreach ($boletas as $boleta) {
                $boleta->fecha = date('d/m/Y', strtotime($boleta->fecha));
                if($boleta->estado == true){
                    $boleta->estado = 'Confirmado';
                }else if ($boleta->estado == false){
                    $boleta->estado = 'Rechazado';
                }else{
                    $boleta->estado = 'Pendiente';
                }
                /*if(isset($boleta->username)){
                    $user=\DB::table('users')->where('id',$boleta->user_id)->first();                        
                    $boleta->username= $user->username;
                }*/
                $boleta->updated_at = date('d/m/Y H:i:s', strtotime($boleta->updated_at));
            }
                                    
            $resultset = array(
                'transactions'  => $boletas
            );            
            return $resultset;

        }catch (\Exception $e){
            \Log::info($e);
            return false;
        }
    }

    /** DEPOSITOS DE CUOTAS*/
    public function depositosAlquileresReports($request){
        try{

            $desde=Carbon::today();
            $hasta=Carbon::tomorrow()->modify('-1 seconds');

            if(!\Sentinel::getUser()->inRole('mini_terminal')){

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
                        'reprinted',
                        'atms.id as atm_id',
                        'atms.name as name_atm'
                    ])                
                    ->join('cuentas_bancarias', 'cuentas_bancarias.id', '=', 'mt_recibos_pagos_miniterminales.cuenta_bancaria_id')
                    ->join('bancos','bancos.id','=','cuentas_bancarias.banco_id')
                    ->join('tipo_pago', 'tipo_pago.id', '=', 'mt_recibos_pagos_miniterminales.tipo_pago_id')
                    ->leftjoin('users', 'users.id', '=', 'mt_recibos_pagos_miniterminales.updated_by')
                    ->leftjoin('mt_recibos', 'mt_recibos.id', '=', 'mt_recibos_pagos_miniterminales.recibo_id')
                    ->leftjoin('atms', 'atms.id', '=', 'mt_recibos_pagos_miniterminales.atm_id')
                    ->whereRaw("fecha BETWEEN '{$desde}' AND '{$hasta}'")
                    ->where('estado', true)
                    ->where('mt_recibos_pagos_miniterminales.tipo_recibo_id', 2)
                    ->orderBy('mt_recibos_pagos_miniterminales.id', 'desc')
                ->get();             
            }else{

                $atms = \DB::table('atms')
                    ->select('atms.id')
                    ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                    ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                    ->join('users_x_groups', 'branches.group_id', '=', 'users_x_groups.group_id')
                    ->where('users_x_groups.user_id', $this->user->id)
                    ->whereIn('atms.owner_id', [16, 21, 25])
                    ->orderBy('atms.id', 'asc')
                ->pluck('atms.id');

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
                        'reprinted',
                        'atms.id as atm_id',
                        'atms.name as name_atm'
                    ])                
                    ->join('cuentas_bancarias', 'cuentas_bancarias.id', '=', 'mt_recibos_pagos_miniterminales.cuenta_bancaria_id')
                    ->join('bancos','bancos.id','=','cuentas_bancarias.banco_id')
                    ->join('tipo_pago', 'tipo_pago.id', '=', 'mt_recibos_pagos_miniterminales.tipo_pago_id')
                    ->leftjoin('users', 'users.id', '=', 'mt_recibos_pagos_miniterminales.updated_by')
                    ->leftjoin('mt_recibos', 'mt_recibos.id', '=', 'mt_recibos_pagos_miniterminales.recibo_id')
                    ->leftjoin('atms', 'atms.id', '=', 'mt_recibos_pagos_miniterminales.atm_id')
                    ->whereIn('mt_recibos_pagos_miniterminales.atm_id', $atms)
                    ->whereRaw("fecha BETWEEN '{$desde}' AND '{$hasta}'")
                    ->where('estado', true)
                    ->where('mt_recibos_pagos_miniterminales.tipo_recibo_id', 2)
                    ->orderBy('mt_recibos_pagos_miniterminales.id', 'desc')
                ->get();
            }

            $atms = \DB::table('atms')
                ->selectRaw('atms.id as atm_id, atms.name, bg.*')
                ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->join('business_groups as bg', 'bg.id', '=', 'branches.group_id')
                ->whereIn('atms.owner_id', [16, 21, 25])
                ->whereNull('bg.deleted_at')
            ->get();

            $data_select = [];

            foreach ($atms as $key => $atm) {
                $data_select[$atm->atm_id] = $atm->name.' | '.$atm->ruc . ' | ' .$atm->description;
            }
            
            $results = $this->arrayPaginator($boletas, $request);

            $resultset = array(
                'target' => 'Depositos Alquileres Miniterminales',
                'transactions' => $results,
                'reservationtime'=> (isset($input['reservationtime'])?$input['reservationtime']:0)
            );
            $status = array('0'=>'Todos','1'=>'Confirmados','2'=>'Rechazados','3'=>'Pendientes');
            
            $resultset['data_select']   = $data_select;
            $resultset['status']        = $status;
            $resultset['status_set']    = 0;
            //$resultset['user_id']       = '';
            $resultset['atm_id']       = '';
            //dd($resultset);
            return $resultset;

        }catch (\Exception $e){
            \Log::error("Error en la consulta de reporte Alquiler" . $e);
            return false;
        }
    }

    public function depositosAlquileresSearch($request){
        try{
            $input = $this->input;
            //dd($input);
            /*Busqueda minusiosa*/
            /*SET DATE RANGE*/

            if(isset($input['reservationtime'])){
                $daterange = explode(' - ',  str_replace('/','-',$input['reservationtime']));
                $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                $where = "fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
            }

            if($input['status_id'] != 0){
                if($input['status_id'] == '1'){
                    $where .= " AND estado is true";
                }
                if($input['status_id'] == '2'){
                    $where .= " AND estado is false";
                }
                if($input['status_id'] == '3'){
                    $where .= " AND estado is null";
                }
            }

            $where .= " AND mt_recibos_pagos_miniterminales.tipo_recibo_id = 2";

            
            if(!\Sentinel::getUser()->inRole('mini_terminal')){
                $atm_id = $input['atm_id'];
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
                            'reprinted',
                            'atms.id as atm_id',
                            'atms.name as name_atm'
                        ])                
                        ->join('cuentas_bancarias', 'cuentas_bancarias.id', '=', 'mt_recibos_pagos_miniterminales.cuenta_bancaria_id')
                        ->join('bancos','bancos.id','=','cuentas_bancarias.banco_id')
                        ->join('tipo_pago', 'tipo_pago.id', '=', 'mt_recibos_pagos_miniterminales.tipo_pago_id')
                        ->leftjoin('users', 'users.id', '=', 'mt_recibos_pagos_miniterminales.updated_by')
                        ->leftjoin('mt_recibos', 'mt_recibos.id', '=', 'mt_recibos_pagos_miniterminales.recibo_id')
                        ->leftjoin('atms', 'atms.id', '=', 'mt_recibos_pagos_miniterminales.atm_id')
                        ->whereRaw("$where")
                        ->where(function($query) use($atm_id){
                            if(!empty($atm_id)){
                                $query->where('mt_recibos_pagos_miniterminales.atm_id', $atm_id);
                            }
                        })
                        ->orderBy('mt_recibos_pagos_miniterminales.id', 'desc')
                ->get();
            }else{
                $atms = \DB::table('atms')
                    ->select('atms.id')
                    ->join('atms_per_users', 'atms.id', '=', 'atms_per_users.atm_id')
                    ->where('atms_per_users.user_id', $this->user->id)
                    ->whereIn('atms.owner_id', [16, 21, 25])
                    ->orderBy('atms.id', 'asc')
                ->pluck('atms.id');
                //$user_id = $this->user->id;
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
                            'reprinted',
                            'atms.id as atm_id',
                            'atms.name as name_atm'
                        ])                
                        ->join('cuentas_bancarias', 'cuentas_bancarias.id', '=', 'mt_recibos_pagos_miniterminales.cuenta_bancaria_id')
                        ->join('bancos','bancos.id','=','cuentas_bancarias.banco_id')
                        ->join('tipo_pago', 'tipo_pago.id', '=', 'mt_recibos_pagos_miniterminales.tipo_pago_id')
                        ->leftjoin('users', 'users.id', '=', 'mt_recibos_pagos_miniterminales.updated_by')
                        ->leftjoin('mt_recibos', 'mt_recibos.id', '=', 'mt_recibos_pagos_miniterminales.recibo_id')
                        ->leftjoin('atms', 'atms.id', '=', 'mt_recibos_pagos_miniterminales.atm_id')
                        ->whereRaw("$where")
                        ->whereIn('mt_recibos_pagos_miniterminales.atm_id', $atms)
                        ->orderBy('mt_recibos_pagos_miniterminales.id', 'desc')
                ->get();
            }
            //dd($boletas);
            $results = $this->arrayPaginator($boletas, $request);

            $resultset = array(
                'target'        => 'Depositos Alquileres Miniterminales',
                'transactions'  => $results,
                'reservationtime' => (isset($input['reservationtime'])?$input['reservationtime']:0),
                'i'             =>  1,
            );

            $atms = \DB::table('atms')
                ->selectRaw('atms.id as atm_id, atms.name, bg.*')
                ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->join('business_groups as bg', 'bg.id', '=', 'branches.group_id')
                ->whereIn('atms.owner_id', [16, 21, 25])
                ->whereNull('bg.deleted_at')
            ->get();

            $data_select = [];

            foreach ($atms as $key => $atm) {
                $data_select[$atm->atm_id] = $atm->name.' | '.$atm->ruc . ' | ' .$atm->description;
            }
            
            $status = array('0'=>'Todos','1'=>'Confirmados','2'=>'Rechazados','3'=>'Pendientes');

            $data_select = [];

            $resultset['status'] = $status;
            $resultset['status_set'] = (isset($input['status_id'])?$input['status_id']:0);
            $resultset['data_select'] = $data_select;
            if(isset($input['atm_id'])){
                $resultset['atm_id'] = $atm_id;   
            }else{
                $resultset['atm_id'] = '';   
            }

            return $resultset;

        }catch (\Exception $e){
            \Log::info($e);
            return false;
        }
    }

    public function depositosAlquileresSearchExport(){
        try{
            $input = $this->input;
            /*Busqueda minusiosa*/
            /*SET DATE RANGE*/

            if(isset($input['reservationtime'])){
                $daterange = explode(' - ',  str_replace('/','-',$input['reservationtime']));
                $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                $where = "fecha BETWEEN '{$daterange[0]}' AND '{$daterange[1]}'";
            }

            if($input['status_id'] != 0){
                if($input['status_id'] == '1'){
                    $where .= " AND estado is true";
                }
                if($input['status_id'] == '2'){
                    $where .= " AND estado is false";
                }
                if($input['status_id'] == '3'){
                    $where .= " AND estado is null";
                }
            }

            $where .= " AND mt_recibos_pagos_miniterminales.tipo_recibo_id = 2";

            if(!\Sentinel::getUser()->inRole('mini_terminal')){
                $atm_id = $input['atm_id'];
                $boletas = \DB::table('mt_recibos_pagos_miniterminales')
                ->select([
                    'mt_recibos_pagos_miniterminales.id',
                    'fecha',
                    'atms.name as name_atm',
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
                ->join('bancos','bancos.id','=','cuentas_bancarias.banco_id')
                ->join('tipo_pago', 'tipo_pago.id', '=', 'mt_recibos_pagos_miniterminales.tipo_pago_id')
                ->leftjoin('users', 'users.id', '=', 'mt_recibos_pagos_miniterminales.user_id')
                ->leftjoin('mt_recibos', 'mt_recibos.id', '=', 'mt_recibos_pagos_miniterminales.recibo_id')
                ->leftjoin('atms', 'atms.id', '=', 'mt_recibos_pagos_miniterminales.atm_id')
                ->whereRaw("$where")
                ->where(function($query) use($atm_id){
                    if(!empty($atm_id)){
                        $query->where('mt_recibos_pagos_miniterminales.atm_id', $atm_id);
                    }
                })
                ->orderBy('mt_recibos_pagos_miniterminales.id', 'desc')
                ->get();
            }else{
                $atms = \DB::table('atms')
                    ->select('atms.id')
                    ->join('atms_per_users', 'atms.id', '=', 'atms_per_users.atm_id')
                    ->where('atms_per_users.user_id', $this->user->id)
                    ->whereIn('atms.owner_id', [16, 21, 25])
                    ->orderBy('atms.id', 'asc')
                ->pluck('atms.id');

                $boletas = \DB::table('mt_recibos_pagos_miniterminales')
                    ->select([
                        'mt_recibos_pagos_miniterminales.id',
                        'fecha',
                        'atms.name as name_atm',
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
                    ->join('bancos','bancos.id','=','cuentas_bancarias.banco_id')
                    ->join('tipo_pago', 'tipo_pago.id', '=', 'mt_recibos_pagos_miniterminales.tipo_pago_id')
                    ->leftjoin('users', 'users.id', '=', 'mt_recibos_pagos_miniterminales.user_id')
                    ->leftjoin('mt_recibos', 'mt_recibos.id', '=', 'mt_recibos_pagos_miniterminales.recibo_id')
                    ->leftjoin('atms', 'atms.id', '=', 'mt_recibos_pagos_miniterminales.atm_id')
                    ->whereRaw("$where")
                    ->whereIn('mt_recibos_pagos_miniterminales.atm_id', $atms)
                    ->orderBy('mt_recibos_pagos_miniterminales.id', 'desc')
                ->get();
            }

                foreach ($boletas as $boleta) {
                    $boleta->fecha = date('d/m/Y', strtotime($boleta->fecha));
                    if($boleta->estado == true){
                        $boleta->estado = 'Confirmado';
                    }else if ($boleta->estado == false){
                        $boleta->estado = 'Rechazado';
                    }else{
                        $boleta->estado = 'Pendiente';
                    }
                    $boleta->update = date('d/m/Y H:i:s', strtotime($boleta->update));
                }

            $resultset = array(
                'transactions'  => $boletas
            );

            return $resultset;

        }catch (\Exception $e){
            \Log::info($e);
            return false;
        }
    }

    /**Conciliaciones MINITERMINALES*/

    public function conciliationsDetails()
    {
        $target = 'Conciliations';
        try {

            $sales = \DB::table('mt_sales')
                ->select(['m.id', 'bg.description as descripcion', 'm.amount', 'mt_sales.fecha', 'm.destination_operation_id', 'mt_sales.nro_venta', 'm.response', 'mt_sales.estado', 'mt_sales.monto_por_cobrar'])
                ->whereIn('m.destination_operation_id', ['0', '1', '-2', '-3', '-4', '-5', '-6', '-9', '-10', '-11', '-12', '-13', '-14', '-16', '-16', '-17', '-21', '-23', '-26', '-27', '212'])
                ->join('mt_movements as m', 'm.id', '=', 'mt_sales.movements_id')
                ->join('business_groups as bg', 'bg.id', '=', 'm.group_id')
                ->orderBy('mt_sales.fecha', 'desc')
                ->orderBy('mt_sales.nro_venta', 'desc')
            ->get();

            foreach ($sales as $sale) {

                /*if ($sale->response <> null) {
                    $message = json_decode($sale->response);
                    $sale->response = $message->status;
                }*/
            }

            $cobranzas = \DB::table('mt_recibos_cobranzas as mtc')
                ->select(['m.id', 'bg.description as descripcion', 'bd.boleta_numero', 'm.created_at', 'm.destination_operation_id', 'm.response', 'mt_recibos.recibo_nro', 'mt_recibos.monto', 'mtc.ventas_cobradas'])
                ->whereIn('m.destination_operation_id', ['0', '1', '-1', '-2', '-3', '-4', '-5', '-6', '-7', '-8', '-9', '-10', '-23', '212', '777'])
                ->whereNotIn('bg.id', ['272', '393', '441'])
                ->whereNull('m.deleted_at')
                ->join('mt_recibos', 'mt_recibos.id', '=', 'mtc.recibo_id')
                ->join('mt_movements as m', 'm.id', '=', 'mt_recibos.mt_movements_id')
                ->join('business_groups as bg', 'bg.id', '=', 'm.group_id')
                ->join('boletas_depositos as bd', 'bd.id', '=', 'mtc.boleta_deposito_id')
                ->orderBy('m.created_at', 'desc')
                ->orderBy('mt_recibos.recibo_nro', 'desc')
            ->get();

            foreach ($cobranzas as $cobranza) {

                /*if ($cobranza->response <> null) {
                    $message = json_decode($cobranza->response);
                    if(!empty($message->status)){
                        $cobranza->response = $message->status;
                    }else{
                        if(!empty($message->status_code)){
                            $cobranza->response = $message->status_code;
                        }else{
                            $cobranza->response = '';
                        }
                    }
                }*/
            }

            $cashouts = \DB::table('mt_recibos_cashouts as mtc')
                ->select(['m.id', 'bg.description as descripcion', 'mtc.transaction_id', 'm.created_at', 'm.destination_operation_id', 'm.response', 'mt_recibos.recibo_nro', 'mt_recibos.monto', 'mtc.ventas_cobradas'])
                ->whereIn('m.destination_operation_id', ['0', '1', '-1', '-2', '-3', '-4', '-5', '-6', '-7', '-8', '-9', '-10', '-23', '212', '777'])
                ->whereNull('m.deleted_at')
                ->join('mt_recibos', 'mt_recibos.id', '=', 'mtc.recibo_id')
                ->join('mt_movements as m', 'm.id', '=', 'mt_recibos.mt_movements_id')
                ->join('business_groups as bg', 'bg.id', '=', 'm.group_id')
                ->orderBy('m.created_at', 'desc')
                ->orderBy('mt_recibos.recibo_nro', 'desc')
            ->get();

            foreach ($cashouts as $cashout) {

                /*if ($cobranza->response <> null) {
                    $message = json_decode($cobranza->response);
                    if(!empty($message->status)){
                        $cobranza->response = $message->status;
                    }else{
                        if(!empty($message->status_code)){
                            $cobranza->response = $message->status_code;
                        }else{
                            $cobranza->response = '';
                        }
                    }
                }*/
            }

            $resultset['sales'] = $sales;
            $resultset['cobranzas'] = $cobranzas;
            $resultset['cashouts'] = $cashouts;
            $resultset['target'] = $target;

        } catch (\Exception $e) {
            \Log::error("Error en la consulta de servicio Conciliaciones: " . $e);
            return false;
        }
        return $resultset;
    }

    /** Metodo para obtener DATOS DEL BALANCE */
    public function getBalanceCierre($group_id){

        $date = Carbon::now()->format('Y-m-d H:i:s');

        $transaccionado = "total_transaccionado_cierre";

        $resumen_transacciones = \DB::connection('eglobalt_replica')->table('balance_atms')
            ->selectRaw("branches.group_id, (total_depositado + $transaccionado + total_reversado + total_cashout + total_pago_cashout + total_pago_qr + total_multa) +
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
            ) as saldo, total_transaccionado_cierre, total_depositado, total_reversado, total_cashout, total_pago_cashout, total_pago_qr, total_multa,
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
            ) as saldo_cuota")
            ->join('atms','atms.id','=','balance_atms.atm_id')
            ->join('points_of_sale','atms.id','=','points_of_sale.atm_id')
            ->join('branches','branches.id','=','points_of_sale.branch_id')
            ->leftjoin('alquiler','alquiler.group_id','=','branches.group_id')
            ->leftjoin('cuotas_alquiler','alquiler.id','=','cuotas_alquiler.alquiler_id')
            ->leftjoin('venta','venta.group_id','=','branches.group_id')
            ->leftjoin('cuotas','venta.id','=','cuotas.credito_venta_id')
            ->whereIn('atms.owner_id', [16, 21, 25])
            ->where('branches.group_id', $group_id)
            ->whereNull('alquiler.deleted_at')
            ->whereNull('venta.deleted_at')
            ->groupBy('branches.group_id', $transaccionado, 'total_depositado', 'total_reversado', 'total_cashout', 'total_pago_cashout', 'total_pago_qr', 'total_multa')
        ->orderBy('branches.group_id','asc');

        $resumen_transacciones_query = $resumen_transacciones->toSql();

        $resumen_transacciones = $resumen_transacciones->get();
        //dd($resumen_transacciones);
        \Log::info("QUERY:\n\n$resumen_transacciones_query");

        $response = [];
        $saldo_sum=0;
        $total_transaccionado_sum = 0;
        $total_depositado_sum = 0;
        $total_reversado_sum = 0;
        $total_cashout_sum = 0;
        $total_pago_cashout_sum = 0;
        $total_pago_qr_sum = 0;
        $total_multa_sum = 0;
        $saldo_cuota_sum = 0;

        foreach ($resumen_transacciones as $transaction) {

            $saldo_sum += $transaction->saldo;
            $total_transaccionado_sum += $transaction->total_transaccionado_cierre;
            $total_depositado_sum += $transaction->total_depositado;
            $total_reversado_sum += $transaction->total_reversado;
            $total_cashout_sum += $transaction->total_cashout;
            $total_pago_cashout_sum += $transaction->total_pago_cashout;
            $total_pago_qr_sum += $transaction->total_pago_qr;
            $total_multa_sum += $transaction->total_multa;
            $saldo_cuota_sum += $transaction->saldo_cuota;
        }

        $response['group_id'] = $group_id;
        $response['total_saldo'] = $saldo_sum;
        $response['total_transaccionado_cierre'] = $total_transaccionado_sum;
        $response['total_depositado'] = $total_depositado_sum;
        $response['total_reversado'] = $total_reversado_sum;
        $response['total_cashout'] = $total_cashout_sum;
        $response['total_pago_cashout'] = $total_pago_cashout_sum;
        $response['total_pago_qr'] = $total_pago_qr_sum;
        $response['total_multa'] = $total_multa_sum;
        $response['saldo_cuota'] = $saldo_cuota_sum;
        
        return $response;
    }
    
}