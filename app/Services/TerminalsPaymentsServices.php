<?php

/**
 * User: avisconte
 * Date: 28/06/2022
 * Time: 15:51
 */

namespace App\Services;

use Excel;
use Carbon\Carbon;

class TerminalsPaymentsServices
{

    /**
     * Inicializar valores para usar en todas las funciones
     */
    public function __construct()
    {
        ini_set('max_execution_time', 0);
        ini_set('client_max_body_size', '20M');
        ini_set('max_input_vars', 10000);
        ini_set('upload_max_filesize', '20M');
        ini_set('post_max_size', '20M');
        ini_set('memory_limit', '-1');
        set_time_limit(3600);

        $this->user = \Sentinel::getUser();
    }

    /**
     * Lista de transacciones a devolver.
     */
    public function index($request)
    {

        if (!$this->user->hasAccess('superuser')) {
            \Log::error(
                'No tienes permiso para acceder a esta pantalla',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );

            $data = [
                'mode' => 'message',
                'type' => 'error',
                'title' => 'Sin permiso',
                'explanation' => 'El usuario necesita tener el permiso asignado para acceder a esta pantalla.'
            ];

            return view('messages.index', compact('data'));
        }

        $user_id = $this->user->id;

        $message = '';
        $error_detail = [];
        $records = [];
        $business_groups = [];
        $atms = [];

        $get_info = true;

        $total_number_of_transactions = 0;
        $total_amount_of_transactions = 0;

        try {

            if (isset($request['button_name'])) {

                if ($request['button_name'] == 'search') {

                    if ($request['search_type'] == '1') {

                        $where = "";

                        if (isset($request['created_at'])) {

                            $created_at = $request['created_at'];
                            $aux = explode(' - ', str_replace('/', '-', $created_at));
                            $from = date('Y-m-d H:i:s', strtotime($aux[0]));
                            $to = date('Y-m-d H:i:s', strtotime($aux[1]));
                        } else {
                            // Si no hay filtro de fecha se trae lo de hoy.
                            $from = date('Y-m-d H:i:s');
                            $to = date('Y-m-d H:i:s');
                        }

                        $where = " mr.created_at between '$from' and '$to'";

                        if (isset($request['group_id'])) {
                            if ($request['group_id'] !== '' and $request['group_id'] !== 'Todos') {
                                $where .= " and mpxa.group_id = " . $request['group_id'];
                            }
                        }

                        if (isset($request['atm_id'])) {
                            if ($request['atm_id'] !== '' and $request['atm_id'] !== 'Todos') {
                                $where .= " and a.id = " . $request['atm_id'];
                            }
                        }

                        if (isset($request['amount'])) {
                            if ($request['amount'] !== '') {
                                $where .= " and mr.monto = " . $request['amount'];
                            }
                        }

                        if (isset($request['transaction_id'])) {
                            if ($request['transaction_id'] !== '') {
                                $where = " and t.id = " . $request['transaction_id'];
                            }
                        }

                        $records = \DB::table('transactions as t')
                            ->select(
                                't.id as transaction_id',
                                'mr.id as receipt_id',
                                'mr.id as mt_recibo_id',
                                \DB::raw("trim(replace(to_char(mr.recibo_nro, '999G999G999G999'), '.', ',')) as mt_recibo_nro"),
                                \DB::raw("trim(replace(to_char(mr.monto, '999G999G999G999'), '.', ',')) as mt_recibo_monto_view"),
                                'mtr.tipo_recibo as mt_recibo_tipo',
                                'mr.monto',
                                \DB::raw("to_char(mr.created_at, 'DD/MM/YYYY HH24:MI:SS') as mt_recibo_created_at"),
                                \DB::raw("(case when mr.in_favor = true then 'A favor' else 'No' end) as in_favor_view"),
                                'mr.request',
                                'mr.response',
                                \DB::raw("
                                    case
                                        when age(now(), mr.created_at) < interval '1 day' then
                                            case
                                                when extract(hour from age(now(), mr.created_at)) = 0 
                                                then extract(minute from age(now(), mr.created_at)) || ' minutos'
                                                else extract(hour from age(now(), mr.created_at)) || 'hs y ' || extract(minute from age(now(), mr.created_at)) || ' minutos'
                                            end
                                        else 
                                            case
                                                when extract(hour from age(now(), mr.created_at)) = 0 
                                                then extract(day from age(now(), mr.created_at)) || ' día(s) '
                                                else extract(day from age(now(), mr.created_at)) || ' día(s) y ' || extract(hour from age(now(), mr.created_at)) || 'hs'
                                            end
                                    end as tiempo_transcurrido
                                "),

                                \DB::raw("extract(hour from age(now(), mr.created_at)) * 60 as tiempo_transcurrido_aux")
                            )
                            ->join('miniterminales_payments_x_atms as mpxa', 't.id', '=', 'mpxa.transaction_id')
                            ->join('mt_payments_x_atms_details as mpxad', 'mpxa.id', '=', 'mpxad.mt_payments_x_atm_id')
                            ->join('mt_recibos as mr', 'mr.id', '=', 'mpxad.recibo_id')
                            ->join('mt_movements as mm', 'mm.id', '=', 'mr.mt_movements_id')
                            ->join('mt_tipo_recibo as mtr', 'mtr.id', '=', 'mr.tipo_recibo_id')
                            ->where('t.service_source_id', 0)
                            ->where('t.service_id', 84)
                            ->where('mpxa.status', 'procesado')
                            ->whereRaw('mm.destination_operation_id = 0')
                            ->whereRaw('mr.in_favor = false')
                            ->whereRaw('mtr.id = any(array[1, 2, 3])')
                            ->whereNotNull('mr.request')
                            //->whereNull('mr.response')
                            ->whereRaw('extract(min from now() - mr.created_at) >= 10')
                            ->whereRaw($where)
                            ->orderBy('mr.id', 'asc')
                            ->orderBy('mpxa.group_id', 'asc')
                            ->take(10)
                            ->get();

                        $records = json_decode(json_encode($records), true);
                    } else {

                        /**
                         * Trae el detalle de pago ordenado por proveedor, terminal, servicio y comisión 
                         */
                        $records = \DB::table('business_groups as bg')
                            ->select(

                                'bg.id as group_id',
                                'bg.description as group_description',

                                't.id as transaction_id',

                                't.amount',
                                \DB::raw("trim(replace(to_char(t.amount, '999G999G999G999'), ',', '.')) as amount_view"),

                                't.created_at',
                                \DB::raw("to_char(t.created_at, 'DD/MM/YYYY HH24:MI:SS') as created_at_view"),

                                't.request_data',

                                'a2.id as transaction_atm_id',
                                'a2.name as transaction_atm_description',

                                \DB::raw("
                                    (select 
                                        json_agg(
                                            json_build_object(
                                                'id', br.id,
                                                'tipo_control', (case 
                                                                    when br.tipo_control = 1 then 'Control por porcentaje'
                                                                    when br.tipo_control = 2 then 'Control por monto fijo'
                                                                    when br.tipo_control = 3 then 'Control de cuota'
                                                                    when br.tipo_control = 4 then 'Control de Límite'
                                                                end),
                                                'dia', (case 
                                                            when br.dia::text = '1' then 'Lunes'
                                                            when br.dia::text = '2' then 'Martes'
                                                            when br.dia::text = '3' then 'Miercoles'
                                                            when br.dia::text = '4' then 'Jueves'
                                                            when br.dia::text = '5' then 'Viernes'
                                                            when br.dia::text = '6' then 'Sábado'
                                                            when br.dia::text = '7' then 'Domingo'
                                                        end),
                                                'saldo_minimo', trim(replace(to_char(br.saldo_minimo, '999G999G999G999'), ',', '.')),
                                                'dias_previos', br.dias_previos,
                                                'created_at', to_char(br.created_at, 'DD/MM/YYYY HH24:MI:SS'),
                                                'updated_at', to_char(br.updated_at, 'DD/MM/YYYY HH24:MI:SS')
                                            ) order by br.dia, tipo_control asc
                                        )
                                    from balance_rules as br
                                    where bg.id = br.group_id) as balance_rules
                                "),

                                \DB::raw("
                                    coalesce(
                                        json_agg(
                                            json_build_object(
                                                'atm_id', a.id,
                                                'atm_description', a.name,
                                                
                                                'recibos', (
                                                    
                                                    select 
                                                        json_agg(
                                                            json_build_object(
                                                            
                                                                'mt_recibo_id', mr.id,
                                                                'mt_recibo_tipo', mtr.tipo_recibo,
                                                                'mt_recibo_nro', mr.recibo_nro,
                                                                'mt_recibo_monto', mr.monto,
                                                                'mt_recibo_monto_view', trim(replace(to_char(mr.monto, '999G999G999G999'), ',', '.')),
                                                                'mt_recibo_created_at', to_char(mr.created_at, 'DD/MM/YYYY HH24:MI:SS'),
                                                                'in_favor_view', (case 
                                                                                    when mr.in_favor = true then '(A favor)'
                                                                                    else ''
                                                                                end),
                                                                
                                                                'mt_movements_id', mm.id,
                                                                'mt_movements_type', mt.description,
                                                                'mt_movements_destination_operation_id', mm.destination_operation_id,
                                                                'mt_movements_amount', trim(replace(to_char(mm.amount, '999G999G999G999'), ',', '.')),
                                                                'mt_movements_debit_credit', (case 
                                                                                                when mm.debit_credit = 'de' then 'DÉBITO'
                                                                                                when mm.debit_credit = 'cr' then 'CRÉDITO'
                                                                                            end),
                                                                'mt_movements_response', coalesce(mm.response, 'Sin respuesta.'),
                                                                'mt_movements_balance_antes', trim(replace(to_char(mm.balance_antes, '999G999G999G999'), ',', '.')),
                                                                'mt_movements_balance', trim(replace(to_char(mm.balance, '999G999G999G999'), ',', '.')),
                                                                'mt_movements_created_at', to_char(mm.created_at, 'DD/MM/YYYY HH24:MI:SS'),
                                                                'mt_movements_updated_at', to_char(mm.updated_at, 'DD/MM/YYYY HH24:MI:SS'),

                                                                'cuota_detalle', (
                                                                    case 
                                                                        
                                                                        when mtr.id = 1 then (
                                            
                                                                            select 
                                                                                json_agg(
                                                                                    json_build_object(

                                                                                        'cuota_cabecera_id', c.credito_venta_id,
                                                                                        'cuota_numero', c.numero_cuota,
                                                                                        'cuota_importe', trim(replace(to_char(c.importe, '999G999G999G999'), ',', '.')),
                                                                                        'cuota_saldo', trim(replace(to_char(c.saldo_cuota, '999G999G999G999'), ',', '.')),
                                                                                        'cuota_fecha_vencimiento', to_char(c.fecha_vencimiento, 'DD/MM/YYYY HH24:MI:SS'),
                                                                                            
                                                                                        'mt_movements_id', mm2.id,
                                                                                        'mt_movements_type', mt2.description,
                                                                                        'mt_movements_destination_operation_id', mm2.destination_operation_id,
                                                                                        'mt_movements_amount', trim(replace(to_char(mm2.amount, '999G999G999G999'), ',', '.')),
                                                                                        'mt_movements_debit_credit', (case 
                                                                                                                        when mm2.debit_credit = 'de' then 'DÉBITO'
                                                                                                                        when mm2.debit_credit = 'cr' then 'CRÉDITO'
                                                                                                                    end),
                                                                                        'mt_movements_response', coalesce(mm2.response, 'Sin respuesta.'),
                                                                                        'mt_movements_balance_antes', trim(replace(to_char(mm2.balance_antes, '999G999G999G999'), ',', '.')),
                                                                                        'mt_movements_balance', trim(replace(to_char(mm2.balance, '999G999G999G999'), ',', '.')),
                                                                                        'mt_movements_created_at', to_char(mm2.created_at, 'DD/MM/YYYY HH24:MI:SS'),
                                                                                        'mt_movements_updated_at', to_char(mm2.updated_at, 'DD/MM/YYYY HH24:MI:SS')
                                                                                    ) order by c.numero_cuota asc
                                                                                )
                                                                            from mt_recibo_x_cuota as mrxc
                                                                            join cuotas as c on c.credito_venta_id = mrxc.credito_venta_id and c.numero_cuota = mrxc.numero_cuota
                                                                            join mt_movements as mm2 on mm2.id = c.movements_id 
                                                                            join movement_type as mt2 on mt2.id = mm2.movement_type_id
                                                                            where mrxc.recibo_id = mr.id
                                                                            and a.id = mm2.atm_id
                                                                        )
                                                                        
                                                                        when mtr.id = 2 then (
                                                                            select 
                                                                                json_agg(
                                                                                    json_build_object(

                                                                                        'cuota_cabecera_id', ca.alquiler_id,
                                                                                        'cuota_numero', ca.num_cuota,
                                                                                        'cuota_importe', ca.importe,
                                                                                        'cuota_saldo', trim(replace(to_char(ca.saldo_cuota, '999G999G999G999'), ',', '.')),
                                                                                        'cuota_fecha_vencimiento', to_char(ca.fecha_vencimiento, 'DD/MM/YYYY HH24:MI:SS'),
                                                                                            
                                                                                        'mt_movements_id', mm2.id,
                                                                                        'mt_movements_type', mt2.description,
                                                                                        'mt_movements_destination_operation_id', mm2.destination_operation_id,
                                                                                        'mt_movements_amount', trim(replace(to_char(mm2.amount, '999G999G999G999'), ',', '.')),
                                                                                        'mt_movements_debit_credit', (case 
                                                                                                                        when mm2.debit_credit = 'de' then 'DÉBITO'
                                                                                                                        when mm2.debit_credit = 'cr' then 'CRÉDITO'
                                                                                                                    end),
                                                                                        'mt_movements_response', coalesce(mm2.response, 'Sin respuesta.'),
                                                                                        'mt_movements_balance_antes', trim(replace(to_char(mm2.balance_antes, '999G999G999G999'), ',', '.')),
                                                                                        'mt_movements_balance', trim(replace(to_char(mm2.balance, '999G999G999G999'), ',', '.')),
                                                                                        'mt_movements_created_at', to_char(mm2.created_at, 'DD/MM/YYYY HH24:MI:SS'),
                                                                                        'mt_movements_updated_at', to_char(mm2.updated_at, 'DD/MM/YYYY HH24:MI:SS')
                                                                                    ) order by ca.num_cuota asc
                                                                                )
                                                                            from mt_recibo_alquiler_x_cuota as mraxc
                                                                            join cuotas_alquiler as ca on ca.alquiler_id = mraxc.alquiler_id and ca.num_cuota = mraxc.numero_cuota 
                                                                            join mt_movements as mm2 on mm2.id = ca.movements_id
                                                                            join movement_type as mt2 on mt2.id = mm2.movement_type_id
                                                                            where mraxc.recibo_id = mr.id
                                                                            and a.id = mm2.atm_id
                                                                        )
                                                                        
                                                                    end
                                                                ),	
                                                                
                                                                'transacciones_detalle', (
                                                                        case 
                                                                            when mtr.id = 3 then (
                                                                            
                                                                                    select 
                                                                                        json_agg(
                                                                                            json_build_object(

                                                                                                'mt_cobranzas_mini_x_atm_id', mcmxa.id,
                                                                                                'mt_cobranzas_mini_x_atm_recibo_id', mcmxa.recibo_id,
                                                                                                'mt_cobranzas_mini_x_atm_transaction_id', mcmxa.transaction_id,
                                                                                                'mt_cobranzas_mini_x_atm_tipo_pago_id', tp.id,
                                                                                                'mt_cobranzas_mini_x_atm_tipo_pago', tp.descripcion,
                                                                                                'mt_cobranzas_mini_x_atm_monto', trim(replace(to_char(mcmxa.monto, '999G999G999G999'), ',', '.')),
                                                                                                'mt_cobranzas_mini_x_atm_fecha', to_char(mcmxa.fecha, 'DD/MM/YYYY HH24:MI:SS'),
                                                                                                                    
                                                                                                'mt_recibos_cobranzas_x_atm_mt_cobranzas_mini_x_atm_id', mrcxa.cobranzas_mini_x_atm_id,
                                                                                                'mt_recibos_cobranzas_x_atm_recibo_id', mrcxa.recibo_id,
                                                                                                'mt_recibos_cobranzas_x_atm_ventas_cobradas', mrcxa.ventas_cobradas,
                                                                                                'mt_recibos_cobranzas_x_atm_saldo_pendiente', trim(replace(to_char(mrcxa.saldo_pendiente, '999G999G999G999'), ',', '.')),
                                                                                                
                                                                                                'detalles', (
                                                                                                    select 
                                                                                                        json_agg(
                                                                                                            json_build_object(
                                                                                                                'mt_sales_id', ms.id,
                                                                                                                'mt_sales_fecha', to_char(ms.fecha, 'DD/MM/YYYY HH24:MI:SS'),
                                                                                                                'mt_sales_estado', ms.estado,
                                                                                                                'mt_sales_monto_por_cobrar', trim(replace(to_char(ms.monto_por_cobrar, '999G999G999G999'), ',', '.')),

                                                                                                                'mt_sales_detail', (
                                                                                                                    select 
                                                                                                                        json_agg(
                                                                                                                            json_build_object(
                                                                                                                                'receipt_id', msabr.receipt_id,
                                                                                                                                'in_favor_view', (case 
                                                                                                                                                    when mr.in_favor = true then '(A favor)'
                                                                                                                                                    else ''
                                                                                                                                                end),
                                                                                                                                'sales_amount_before', trim(replace(to_char(msabr.sales_amount_before, '999G999G999G999'), ',', '.')),
                                                                                                                                'sales_amount_affected', msabr.sales_amount_affected,
                                                                                                                                'sales_amount_affected_view', trim(replace(to_char(msabr.sales_amount_affected, '999G999G999G999'), ',', '.')),
                                                                                                                                'sales_amount_after', trim(replace(to_char(msabr.sales_amount_after, '999G999G999G999'), ',', '.')),
                                                                                                                                'description', msabr.description,
                                                                                                                                'created_at', to_char(msabr.created_at, 'DD/MM/YYYY HH24:MI:SS')
                                                                                                                            ) order by msabr.created_at asc
                                                                                                                        )
                                                                                                                    from mt_sales_affected_by_receipts as msabr
                                                                                                                    join mt_recibos as mr on mr.id = msabr.receipt_id
                                                                                                                    where ms.id = msabr.sales_id    
                                                                                                                ),
                                                                                                                                    
                                                                                                                'mt_movements_id', mm2.id,
                                                                                                                'mt_movements_type', mt2.description,
                                                                                                                'mt_movements_destination_operation_id', mm2.destination_operation_id,
                                                                                                                'mt_movements_amount', trim(replace(to_char(mm2.amount, '999G999G999G999'), ',', '.')),
                                                                                                                'mt_movements_debit_credit', (case 
                                                                                                                                                when mm2.debit_credit = 'de' then 'DÉBITO'
                                                                                                                                                when mm2.debit_credit = 'cr' then 'CRÉDITO'
                                                                                                                                            end),
                                                                                                                'mt_movements_response', coalesce(mm2.response, 'Sin respuesta.'),
                                                                                                                'mt_movements_balance_antes', trim(replace(to_char(mm2.balance_antes, '999G999G999G999'), ',', '.')),
                                                                                                                'mt_movements_balance', trim(replace(to_char(mm2.balance, '999G999G999G999'), ',', '.')),
                                                                                                                'mt_movements_created_at', to_char(mm2.created_at, 'DD/MM/YYYY HH24:MI:SS'),
                                                                                                                'mt_movements_updated_at', to_char(mm2.updated_at, 'DD/MM/YYYY HH24:MI:SS')
                                                                                                            )
                                                                                                        )
                                                                                                    from mt_sales as ms
                                                                                                    join mt_movements as mm2 on mm2.id = ms.movements_id 
                                                                                                    join movement_type as mt2 on mt2.id = mm2.movement_type_id
                                                                                                    where a.id = mm2.atm_id 
                                                                                                    and (mrcxa.ventas_cobradas ilike '%' || mm2.destination_operation_id::text || '%')
                                                                                                
                                                                                                )
                                                                                            )
                                                                                        )
                                                                                from mt_cobranzas_mini_x_atm as mcmxa
                                                                                join mt_recibos_cobranzas_x_atm as mrcxa on mcmxa.id = mrcxa.cobranzas_mini_x_atm_id
                                                                                join tipo_pago as tp on tp.id = mcmxa.tipo_pago_id
                                                                                where mr.id = mcmxa.recibo_id
                                                                            )
                                                                            
                                                                        end
                                                                )				             	  		
                                                            )
                                                        )
                                                    from mt_payments_x_atms_details as mpxad
                                                    join mt_recibos as mr on mr.id = mpxad.recibo_id 
                                                    join mt_tipo_recibo as mtr on mtr.id = mr.tipo_recibo_id
                                                    join mt_movements as mm on mm.id = mr.mt_movements_id
                                                    join movement_type as mt on mt.id = mm.movement_type_id
                                                    where mpxa.id = mpxad.mt_payments_x_atm_id
                                                    and t.id = mpxa.transaction_id
                                                
                                                ),

                                                'historial_bloqueos', (
                                                    select 
                                                        json_agg(
                                                            json_build_object(

                                                                'historial_bloqueos_id', trim(replace(to_char(hb.id, '999G999G999G999'), ',', '.')),
                                                                'historial_bloqueos_saldo_pendiente', trim(replace(to_char(hb.saldo_pendiente, '999G999G999G999'), ',', '.')),
                                                                'historial_bloqueos_created_at', to_char(hb.created_at, 'DD/MM/YYYY HH24:MI:SS'),
                                                                'historial_bloqueos_bloqueado', (case when bloqueado = true then 'Bloqueado' else 'Desbloqueado' end),
                                                                'historial_bloqueos_block_type', bt.description

                                                            ) order by hb.created_at asc
                                                        )
                                                    from historial_bloqueos as hb
                                                    join block_type as bt on bt.id = hb.block_type_id  
                                                    where a.id = hb.atm_id
                                                    and hb.created_at between t.created_at and mpxa.created_at
                                                ),

                                                'payments_x_atm', (
                                                    select 
                                                        json_agg(
                                                            json_build_object(

                                                                'id', mpxa2.id,
                                                                'status', mpxa2.status,
                                                                'status_message', mpxa2.status_message,
                                                                'payment_details', mpxa2.payment_details::text

                                                            )
                                                        )
                                                    from miniterminales_payments_x_atms as mpxa2
                                                    where a.id = mpxa2.atm_id
                                                    and bg.id = mpxa2.group_id
                                                    and t.id = mpxa2.transaction_id
                                                ),

                                                'total_deposited', (
                                                    select 
                                                        json_agg(
                                                            json_build_object(
                                                                'receipt_id', tdabr.receipt_id,
                                                                'in_favor_view', (case 
                                                                                    when mr.in_favor = true then '(A favor)'
                                                                                    else ''
                                                                                end),
                                                                'total_deposited_before', trim(replace(to_char(tdabr.total_deposited_before, '999G999G999G999'), ',', '.')),
                                                                'total_deposited', trim(replace(to_char(tdabr.total_deposited, '999G999G999G999'), ',', '.')),
                                                                'total_deposited_after', trim(replace(to_char(tdabr.total_deposited_after, '999G999G999G999'), ',', '.')),
                                                                'created_at', to_char(tdabr.created_at, 'DD/MM/YYYY HH24:MI:SS')
                                                            ) order by tdabr.id asc
                                                        )
                                                    from total_deposited_affected_by_receipts as tdabr
                                                    join mt_payments_x_atms_details as mpxad on mpxad.recibo_id = tdabr.receipt_id
                                                    join mt_recibos as mr on mr.id = mpxad.recibo_id 
                                                    where mpxa.id = mpxad.mt_payments_x_atm_id
                                                    and t.id = mpxa.transaction_id
                                                    and a.id = tdabr.atm_id
                                                )
                                                
                                            )
                                        ), '[]'::json 
                                    ) as atms
                                ")
                            )
                            ->join('branches as b', 'bg.id', '=', 'b.group_id')
                            ->join('points_of_sale as pos', 'b.id', '=', 'pos.branch_id')
                            ->join('atms as a', 'a.id', '=', 'pos.atm_id')
                            ->join('miniterminales_payments_x_atms as mpxa', 'a.id', '=', 'mpxa.atm_id')
                            ->join('transactions as t', 't.id', '=', 'mpxa.transaction_id')
                            ->join('atms as a2', 'a2.id', '=', 't.atm_id')
                            ->whereRaw('t.service_source_id = 0')
                            ->whereRaw('t.service_id = 84');



                        $ignore_filters = false;

                        if (isset($request['transaction_id'])) {
                            if ($request['transaction_id'] !== '') {
                                $records = $records->whereRaw("t.id = " . $request['transaction_id']);
                                $ignore_filters = true;
                            }
                        }

                        if ($ignore_filters == false) {

                            if (isset($request['receipt_id'])) {
                                if ($request['receipt_id'] !== '') {

                                    $sub_query = "
                                    select 
                                        mpxa2.transaction_id 
                                    from miniterminales_payments_x_atms as mpxa2
                                    join mt_payments_x_atms_details as mpxad2 on mpxa2.id = mpxad2.mt_payments_x_atm_id 
                                    join mt_recibos as mr2 on mr2.id = mpxad2.recibo_id
                                    where mr2.id = " . $request['receipt_id'] . "
                                ";

                                    $records = $records->whereRaw("t.id = ($sub_query)");

                                    $ignore_filters = true;
                                }
                            }
                        }

                        if ($ignore_filters == false) {

                            if (isset($request['created_at'])) {

                                $created_at = $request['created_at'];
                                $aux = explode(' - ', str_replace('/', '-', $created_at));
                                $from = date('Y-m-d H:i:s', strtotime($aux[0]));
                                $to = date('Y-m-d H:i:s', strtotime($aux[1]));
                                $records = $records->whereRaw("t.created_at between '{$from}' and '{$to}'");

                                $from_view = date('d/m/Y', strtotime($aux[0])) . ' 00:00:00';
                                $to_view = date('d/m/Y', strtotime($aux[1])) . ' 23:59:59';
                                $request['created_at'] = "$from_view - $to_view";
                            } else {
                                // Si no hay filtro de fecha se trae lo de hoy.
                                $from = date('Y-m-d H:i:s');
                                $to = date('Y-m-d H:i:s');
                                //$records = $records->whereRaw("t.created_at between '{$from}' and '{$to}'");
                            }

                            if (isset($request['group_id'])) {
                                if ($request['group_id'] !== '' and $request['group_id'] !== 'Todos') {
                                    $records = $records->where('bg.id', $request['group_id']);
                                }
                            }

                            if (isset($request['atm_id'])) {
                                if ($request['atm_id'] !== '' and $request['atm_id'] !== 'Todos') {
                                    $records = $records->where('a.id', $request['atm_id']);
                                }
                            }

                            if (isset($request['amount'])) {
                                if ($request['amount'] !== '') {
                                    $records = $records->where('t.amount', $request['amount']);
                                }
                            }
                        }

                        $records = $records
                            ->groupBy(
                                \DB::raw("
                                bg.id,
                                t.id,
                                a2.id
                            ")
                            )
                            ->orderBy('t.id');



                        //\Log::info("query:" . $records->toSql());

                        $records = $records->get();

                        $records = json_decode(json_encode($records), true);

                        //\Log::info("records:", [$records]);

                        $records_aux = [];

                        $total_number_of_transactions = count($records);
                        $total_amount_of_transactions = 0;

                        /**
                         * Agrupación de transacciones por grupo.
                         */
                        for ($i = 0; $i < count($records); $i++) {

                            $item = $records[$i];

                            $group_id = $item['group_id'];
                            $group_description = $item['group_description'];
                            $balance_rules = json_decode($item['balance_rules'], true);

                            $transaction_id = $item['transaction_id'];
                            $amount = $item['amount'];
                            $amount_view = $item['amount_view'];
                            $created_at = $item['created_at'];
                            $created_at_view = $item['created_at_view'];
                            $request_data = $item['request_data'];

                            $transaction_atm_id = $item['transaction_atm_id'];
                            $transaction_atm_description = $item['transaction_atm_description'];

                            $atms = json_decode($item['atms'], true);


                            $count_atms_total = count($atms); // Terminales en total
                            $count_generated_receipts_total = 0; // Recibos generados en total
                            $count_generated_quotes_total = 0; // Cuotas afectadas en total
                            $count_generated_sales_total = 0; // Ventas afectadas en total
                            $count_generated_sales_total_aux = 0;

                            $mt_recibos_cobranzas_x_atm_ventas_cobradas_aux = '';

                            $amount_generated_receipts_total = 0;
                            $amount_generated_quotes_total = 0;
                            $amount_generated_sales_total = 0;

                            foreach ($atms as $atm_aux) {

                                if ($atm_aux !== null) {

                                    $atm_recibos_count = count($atm_aux['recibos']);
                                    $count_generated_receipts_total += $atm_recibos_count;

                                    foreach ($atm_aux['recibos'] as $recibo_aux) {

                                        $cuota_detalle = $recibo_aux['cuota_detalle'];

                                        $amount_generated_receipts_total += $recibo_aux['mt_recibo_monto'];

                                        $count_generated_quotes_total += count($cuota_detalle);

                                        if ($recibo_aux['transacciones_detalle'] !== null) {

                                            foreach ($recibo_aux['transacciones_detalle'] as $transaccion_aux) {

                                                $detalles = $transaccion_aux['detalles'];
                                                $count_generated_sales_total += count($detalles);

                                                $mt_recibos_cobranzas_x_atm_ventas_cobradas_aux .= $transaccion_aux['mt_recibos_cobranzas_x_atm_ventas_cobradas'] . ';';

                                                if ($transaccion_aux['detalles'] !== null) {
                                                    foreach ($transaccion_aux['detalles'] as $detalles_item) {
                                                        if ($detalles_item['mt_sales_detail'] !== null) {
                                                            foreach ($detalles_item['mt_sales_detail'] as $mt_sales_detail) {
                                                                $amount_generated_sales_total += $mt_sales_detail['sales_amount_affected'];
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        }

                                        if ($count_generated_sales_total == 0) {
                                            $mt_recibos_cobranzas_x_atm_ventas_cobradas_aux = trim($mt_recibos_cobranzas_x_atm_ventas_cobradas_aux, ';');
                                            $count_generated_sales_total_aux = explode(';', $mt_recibos_cobranzas_x_atm_ventas_cobradas_aux);
                                            $count_generated_sales_total_aux = count($count_generated_sales_total_aux);
                                            $count_generated_sales_total = $count_generated_sales_total_aux;
                                        }
                                    }
                                }
                            }

                            $records_aux[$group_id]['group_id'] = $group_id;
                            $records_aux[$group_id]['group_description'] = $group_description;
                            $records_aux[$group_id]['balance_rules'] = $balance_rules;
                            $records_aux[$group_id]['transactions'][$transaction_id] = [
                                'transaction_id' => $transaction_id,
                                'amount' => $amount,
                                'amount_view' => $amount_view,
                                'created_at' => $created_at,
                                'created_at_view' => $created_at_view,
                                'transaction_atm_id' => $transaction_atm_id,
                                'transaction_atm_description' => $transaction_atm_description,
                                'created_at_view' => $created_at_view,
                                'request_data' => $request_data,
                                'atms' => $atms,
                                'transaction_summary' => [
                                    'counts' => [
                                        'atms' => $count_atms_total,
                                        'receipts' => $count_generated_receipts_total,
                                        'quotes' => $count_generated_quotes_total,
                                        'sales' => $count_generated_sales_total,
                                    ],
                                    'amounts' => [
                                        'receipts' => $amount_generated_receipts_total,
                                        'quotes' => $amount_generated_quotes_total,
                                        'sales' => $amount_generated_sales_total
                                    ]
                                ]
                            ];

                            $total_amount_of_transactions += $amount;
                        }

                        $records = $records_aux;
                    }


                    if (count($records) <= 0) {
                        $data = [
                            'mode' => 'alert',
                            'type' => 'info',
                            'title' => 'Consulta sin registros',
                            'explanation' => 'La consulta no retornó ningún registro.'
                        ];

                        return view('messages.index', compact('data'));
                    }
                }
            }

            if ($get_info) {

                $business_groups = \DB::table('business_groups as bg')
                    ->select(
                        'bg.id',
                        \DB::raw("('#' || bg.id || ' - ' || bg.description || ' - ' || coalesce(bg.ruc, 'Sin número de documento')) as description")
                    )
                    ->orderBy('bg.description', 'ASC')
                    ->get();
            }
        } catch (\Exception $e) {
            $error_detail = [
                'from' => 'CMS',
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];

            \Log::error("Error en el informe de pagos por terminal: " . json_encode($error_detail));
        }

        $data = [
            'message' => $message,
            'lists' => [
                'records' => $records,
                'json' => json_encode($records, JSON_UNESCAPED_UNICODE),
                'business_groups' => json_encode($business_groups, JSON_UNESCAPED_UNICODE)
            ],
            'totals' => [
                'total_number_of_transactions' => $total_number_of_transactions,
                'total_amount_of_transactions' => $total_amount_of_transactions
            ],
            'inputs' => [
                'created_at' => isset($request['created_at']) ? $request['created_at'] : null,
                'transaction_id' => isset($request['transaction_id']) ? $request['transaction_id'] : null,
                'receipt_id' => isset($request['receipt_id']) ? $request['receipt_id'] : null,
                'amount' => isset($request['amount']) ? $request['amount'] : null,
                'group_id' => isset($request['group_id']) ? $request['group_id'] : 'Todos',
                'atm_id' => isset($request['atm_id']) ? $request['atm_id'] : 'Todos',
                'search_type' => isset($request['search_type']) ? $request['search_type'] : '1' // Tipo de búsqueda 1
            ],
            'error_detail' => $error_detail
        ];

        return view('terminals_payments.index', compact('data'));
    }


    /**
     * Terminales por grupo
     */
    public function get_atms_per_group($request)
    {
        if (!$this->user->hasAccess('superuser')) {
            \Log::error(
                'No tienes permiso para acceder a esta pantalla',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );

            $data = [
                'mode' => 'message',
                'type' => 'error',
                'title' => 'Sin permiso',
                'explanation' => 'El usuario necesita tener el permiso asignado para acceder a esta pantalla.'
            ];

            return view('messages.index', compact('data'));
        }

        $group_id = $request['group_id'];

        $records = [];

        if ($group_id !== null and $group_id !== 'Todos') {
            $records = \DB::table('business_groups as bg')
                ->select(
                    'a.id as id',
                    \DB::raw("('#' || a.id || ' - ' || a.name) as description")
                )
                ->join('branches as b', 'bg.id', '=', 'b.group_id')
                ->join('points_of_sale as pos', 'b.id', '=', 'pos.branch_id')
                ->join('atms as a', 'a.id', '=', 'pos.atm_id')
                ->where('bg.id', $group_id)
                ->orderBy('a.name', 'ASC')
                ->get();
        }

        return $records;
    }

    /**
        Gestionar recibos pendientes y recibos a favor de pagos por terminales
     */
    public function manage_receipts($receipt_id)
    {

        $class = __CLASS__;
        $function = __FUNCTION__;

        $response = [
            'message' => 'Recibo relanzado.',
            'error' => false,
            'error_detail' => []
        ];


        /**
         * Diccionario de posibles errores al
         * envíar consultas a ONDANET
         */
        $error_code_list = [
            '' => 'El código está vacío, No se pudo obtener ningún código de ONDANET.',
            '1' => '1 | Pendiente de enviar.',

            '-1' => '-1 | Nro. de recibo ya existe',
            '-2' => '-2 | Vendedor no existe',
            '-3' => '-3 | Cobrador no existe',
            '-4' => '-4 | Caja del vendedor no se encuentra',
            '-6' => '-6 | Sin saldo pendiente',
            '-7' => '-7 | El saldo es menor a lo cobrado',
            '-8' => '-8 | Mas de un cliente para las facturas',
            '-9' => '-9 | Ciente no existe',
            '-10' => '-10 | Deposito no existe',
            '-23' => '-23 | Caja cerrada en la fecha',
            '212' => '212 | Otros errores no definidos en el procedimiento'
        ];

        try {

            /**
             * Traer los recibos que se generaron por Pagos por Terminal.
             */

            $records = \DB::table('transactions as t')
                ->select(
                    'mpxa.id as terminals_payments_x_atms_id',
                    'mpxa.transaction_id',
                    'mpxa.group_id',
                    'mr.id as receipt_id',
                    'mr.request as receipt_query',
                    'mr.mt_movements_id as receipt_movements_id'
                )
                ->join('miniterminales_payments_x_atms as mpxa', 't.id', '=', 'mpxa.transaction_id')
                ->join('mt_payments_x_atms_details as mpxad', 'mpxa.id', '=', 'mpxad.mt_payments_x_atm_id')
                ->join('mt_recibos as mr', 'mr.id', '=', 'mpxad.recibo_id')
                ->join('mt_movements as mm', 'mm.id', '=', 'mr.mt_movements_id')
                ->join('mt_tipo_recibo as mtr', 'mtr.id', '=', 'mr.tipo_recibo_id')
                ->where('t.service_source_id', 0)
                ->where('t.service_id', 84)
                ->where('mpxa.status', 'procesado')
                ->whereRaw('mm.destination_operation_id = 0')
                ->whereRaw('mr.in_favor = false')
                ->whereRaw('mtr.id = any(array[1, 2, 3])')
                ->whereNotNull('mr.request')
                ->whereRaw("mr.id = $receipt_id")
                //->whereNull('mr.response')
                ->whereRaw('extract(min from now() - mr.created_at) >= 10')
                ->orderBy('mr.id', 'asc')
                ->orderBy('mpxa.group_id', 'asc')
                ->get();

            \Log::info("Recibo a relanzar:", [$records]);

            if (count($records) > 0) {

                $records = json_decode(json_encode($records), true);

                \Log::info("\n\nCampos obtenidos en $class \ $function: Lista:\n\n", [$records]);

                /**
                    CONEXIÓN A ONDANET
                    DESARROLLO: 'testing'
                    PRODUCCIÓN: 'ondanet'
                    Conexión utilizada para enviar los recibos pendientes a ONDANET
                 */

                $connection_to_ondanet = null;

                try {
                    $connection_to_ondanet = \DB::connection('ondanet')->getPdo(); // Se instancia una vez la conexión, no varias veces en el ciclo.

                    \Log::info("Conexión correcta a ONDANET, ahora a enviar los recibos pendientes.", [$records]);
                } catch (\Exception $e) {
                    $error_detail_aux = [
                        'connection_to_ondanet' => $connection_to_ondanet,
                        'exception' => $e->getMessage()
                    ];
                    \Log::info("\n\nOcurrió un error al querer conectarse ONDANET, Detalles:", $error_detail_aux);

                    $ondanet['error'] = true;
                    $ondanet['message'] = $e->getMessage();

                    $response['error'] = true;
                    $response['message'] = "No fué posible conectarse a ONDANET, Error: " . $e->getMessage();
                }

                if ($connection_to_ondanet !== null) {

                    for ($i = 0; $i < count($records); $i++) {

                        $item = $records[$i];
                        $terminals_payments_x_atms_id = $item['terminals_payments_x_atms_id'];
                        $receipt_id = $item['receipt_id'];
                        $receipt_query = $item['receipt_query'];
                        $receipt_movements_id = $item['receipt_movements_id'];

                        $ondanet = [
                            'error' => false,
                            'message' => null,
                            'send' => false,
                            'code' => 1,
                            'response' => []
                        ];

                        try {

                            $prepare = $connection_to_ondanet->prepare($receipt_query);
                            $prepare->execute();

                            $ondanet['send'] = true;

                            do {
                                $ondanet['response'] = $prepare->fetchAll(\PDO::FETCH_ASSOC);

                                \Log::info("Obteniendo datos de ondanet para recibo id: $receipt_id ... ", [$ondanet['response']]);
                            } while ($prepare->nextRowset());

                            if (isset($ondanet['response'][0])) {

                                foreach ($ondanet['response'][0] as $key => $value) {
                                    \Log::info("Items obtenidos de ONDANET: $key => $value");
                                    $ondanet['code'] = $value;
                                }
                            }

                            $code = $ondanet['code'];

                            if (isset($error_code_list["$code"])) {
                                $ondanet['error'] = true;
                                $ondanet['message'] = $error_code_list["$code"];
                            }
                        } catch (\PDOException $e) {

                            $ondanet['error'] = true;
                            $ondanet['message'] = $e->getMessage();

                            $response['error'] = true;
                            $response['message'] = "Ocurrió un error al querer enviar la cadena, Error: " . $e->getMessage();
                        }

                        //die();

                        //Actualización de Recibos.

                        \DB::table('mt_recibos')
                            ->where('id', $receipt_id)
                            ->update([
                                'response' => json_encode($ondanet),
                                'updated_at' => Carbon::now()
                            ]);

                        //Actualización de Movimiento.

                        \DB::table('mt_movements')
                            ->where('id', $receipt_movements_id)
                            ->update([
                                'destination_operation_id' => $ondanet['code'],
                                'response' => json_encode([
                                    'error' => $ondanet['error'],
                                    'status' => $ondanet['code']
                                ]),
                                'updated_at' => Carbon::now()
                            ]);

                        //Actualización del Pago por Terminal.

                        $terminals_payments_x_atms = \DB::table('miniterminales_payments_x_atms')
                            ->select(
                                'payment_details'
                            )
                            ->where('id', $terminals_payments_x_atms_id)
                            ->get();

                        $terminals_payments_x_atms = $terminals_payments_x_atms[0];
                        $payment_details = json_decode($terminals_payments_x_atms->payment_details, true);

                        for ($j = 0; $j < count($payment_details); $j++) {

                            $receipt_movements_id_aux = $payment_details['receipts'][$j]['movements_id'];

                            if ($receipt_movements_id == $receipt_movements_id_aux) {

                                $query_item = [
                                    'send' => $ondanet['send'],
                                    'error' => $ondanet['error'],
                                    'code' => $ondanet['code'],
                                    'code_description' => $ondanet['message'],
                                    'query' => $receipt_query,
                                    'result' => $ondanet['response']
                                ];

                                $payment_details['receipts'][$j]['ondanet'] = $query_item;
                                $payment_details['updated_at'] = Carbon::now()->format('Y-m-d H:i:s.u');

                                \DB::table('miniterminales_payments_x_atms')
                                    ->where('id', $terminals_payments_x_atms_id)
                                    ->update([
                                        'status' => 'procesado',
                                        'status_message' => "El pago fué procesado. Último ID de recibo procesado: $receipt_id",
                                        'payment_details' => json_encode($payment_details)
                                    ]);

                                break;
                            }
                        }

                        \Log::info("Cadena gestionada...", [$ondanet]);
                    }
                }
            } else {
                \Log::info("\n\nNo hay recibos de pagos pendientes por enviar!");

                $response['error'] = true;
                $response['message'] = "No se pudo obtener y gestionar el recibo.";
            }
        } catch (\Exception $e) {

            $error_detail = [
                'from' => 'CMS',
                'message' => 'Ocurrió un error al querer gestionar el recibo desde el CMS.',
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => $class,
                'function' => $function,
                'line' => $e->getLine()
            ];

            $response['error'] = true;
            $response['message'] = $error_detail['message'];
            $response['error_detail'] = $error_detail;

            $error_detail = json_encode($error_detail);

            \Log::error("\n\nError en $class \ $function:\nDetalles:\n\n$error_detail\n\n");
        }

        return $response;
    }

    public function terminals_payments_relaunch_receipt($request)
    {

        $response = [
            'error' => false,
            'message' => 'Recibo relanzado.'
        ];

        try {

            sleep(1); // Dormir un rato para no sobre cargar a ondanet.

            $receipt_id = $request['receipt_id'];

            $response = $this->manage_receipts($receipt_id);

        } catch (\Exception $e) {
            $error_detail = [
                'from' => 'CMS',
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];

            \Log::error("Error en el informe de pagos por terminal: " . json_encode($error_detail));
        }

        return $response;
    }
}
