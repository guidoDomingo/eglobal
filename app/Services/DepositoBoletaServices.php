<?php
/**
 * Created by PhpStorm.
 * User: thavo
 * Date: 15/02/17
 * Time: 11:12 AM
 */

namespace App\Services;

use App\Models\DepositoBoleta;
use App\Models\TipoPago;
use App\Models\Banco;
use App\Models\CuentaBancaria;
use Carbon\Carbon;
use App\Services\OndanetServices;
use Mail;

class DepositoBoletaServices
{
    public static function sendAlerts($fecha, $tipo_pago, $banco, $cuenta, $nroboleta, $monto, $depositado){
        try{

            $tipopago=TipoPago::where('id', $tipo_pago)->pluck('descripcion');
            $banconombre=Banco::where('id', $banco)->pluck('descripcion');
            $nrocuenta=CuentaBancaria::where('id', $cuenta)->pluck('numero_banco');

            $data = [
                'user_name'             => 'Tesoreria',
                'fecha'                 => $fecha,
                'tipopago'              => $tipopago,
                'banco'                 => $banconombre,
                'nrocuenta'             => $nrocuenta,
                'nroboleta'             => $nroboleta,
                'monto'                 => number_format($monto, 0),
                'depositado'            => $depositado
            ];

            Mail::send('mails.deposito_boleta', $data,
                function ($message){
                    $user_email = 'guissela@antell.com.py';
                    $user_name = 'Admin';
                    $message->to($user_email, $user_name)
                    ->cc('ainsfran@eglobalt.com.py')
                    ->cc('cgamarra@antell.com.py')
                    ->cc('wescobar@antell.com.py')
                    ->cc('fmartinez@antell.com.py')
                    ->cc('ncristaldo@eglobalt.com.py')
                    ->cc('jrebull@eglobalt.com.py')
                    ->cc('pmartinez@antell.com.py')
                    ->subject('[EGLOBAL] NUEVO DEPOSITO DE ALQUILER REGISTRADO');
                    //$message->to($user_email, $user_name)->cc('vittone.daniel@gmail.com')->subject('[EGLOBAL - DESARROLLO] ACCESO NO AUTORIZADO - PUERTA PRINCIPAL ABIERTA');
                    //$message->to($user_email, $user_name)->subject('[EGLOBAL - DESARROLLO] ACCESO NO AUTORIZADO - PUERTA PRINCIPAL ABIERTA');
                });

            $response = [
                    'error' => false,
                    'message' => 'Notificacion enviada con exito',
                    'message_user' => ''
            ];

            return $response;
        }catch (\Exception $e){
            \Log::debug('result: '. $e);

            $response = [
                'error' => true,
                'message' => 'Error al procesar envio de correo, se genero reporte en log',
                'message_user' => ''
            ];

            return $response;
        }
    }

    /* Funcion que alerta los servicios que no tengan transacciones en los atms en el rango */
    public function balanceControlMini($user_id = null, $fecha = null){
        try {
            $desde = date("Y-m-d 23:59:59", strtotime($fecha));
            $fecha_actual=Carbon::now();

            $usersId = \DB::table('users')
                        ->join('role_users', 'users.id', '=', 'role_users.user_id')
                        ->join('branches', 'users.id', '=', 'branches.user_id')
                        ->where('role_users.role_id', 22)
                        ->where('branches.user_id', $user_id)
                        ->first();

            if(isset($usersId)){
                $group = \DB::table('branches')->where('user_id',$user_id)->first();
            }else{
                $group = \DB::table('users_x_groups')->where('user_id',$user_id)->first();
            }

            $baseQuery = \DB::table('movements')
                ->selectRaw("SUM(CASE WHEN debit_credit = 'de' and movements.created_at <= '" .$desde ."' THEN (movements.amount) else 0 END) -
                abs(SUM(CASE WHEN debit_credit = 'cr' and movements.created_at <= '" .$fecha_actual."' THEN (movements.amount) else 0 END)) as total")
                ->join('current_account', 'movements.id', '=', 'current_account.movement_id')
                ->where('current_account.group_id',$group->group_id)
                ->whereRaw("movements.destination_operation_id not in ('1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-15','-16','-17','-21','-23','-26','-27','212', '999')")
                ->first();

            $total_deuda = \DB::table('movements')
            ->selectRaw("SUM(CASE WHEN debit_credit = 'de' and movements.created_at <= " ."'" .$desde."'" ." THEN (movements.amount) else 0 END) as total")
            ->join('current_account', 'movements.id', '=', 'current_account.movement_id')
            ->where('current_account.group_id',$group->group_id)
            ->whereRaw("movements.destination_operation_id not in ('1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-15','-16','-17','-21','-23','-26','-27','212', '999')")
            ->first();

            $total_depositado = \DB::table('movements')
            ->selectRaw("SUM(CASE WHEN debit_credit = 'cr' and movements.created_at <= " ."'" .$fecha_actual."'" ." THEN (movements.amount) else 0 END) as total")
            ->join('current_account', 'movements.id', '=', 'current_account.movement_id')
            ->where('current_account.group_id',$group->group_id)
            ->whereRaw("movements.destination_operation_id not in ('1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-15','-16','-17','-21','-23','-26','-27','212', '999')")
            ->first();

            $response = [
                'message' => 'Consulta exitosa',
                'error' => false,
                'saldo' => $baseQuery->total,
                'transaccionado' => $total_deuda->total,
                'depositado' => $total_depositado->total,
            ];
            return $response;

        } catch(\Exception $e){
            \Log::debug('[balanceControlMini]', ['e' => $e]);
            $response = [
                'error' => true,
                'message' => 'Error al consultar saldo de miniterminal',
                'message_user' => ''
            ];

            return $response;
        }
    }

    /* Funcion que verifica el balance de la deuda de la miniterminal */
    public static function checkUserBalance($atm_id = null, $boleta_deposito_id){
        try {

            $rule = \DB::table('balance_rules')->where('atm_id', $atm_id)->whereIn('tipo_control', [1, 2])->whereNull('deleted_at')->first();
            
            $group = \DB::table('atms')
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'atms.id')
                ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->where('atms.id', $atm_id)
            ->first();
            
            if(!empty($rule)){
                $where = "atms.id = ". $atm_id; 
            }else{

                $parametro_control = \DB::table('balance_rules')
                ->where([
                    'group_id' => $group->group_id,
                    'dia' => date('N'),
                    'deleted_at' => null
                ])
                ->whereRaw("tipo_control not in (3, 4)")
                ->first();

                $where = "branches.group_id = ". $group->group_id; 
            }
            
            \Log::info(json_decode(json_encode($parametro_control), true));

            $atms = \DB::table('atms')
                    ->select('atms.id as id_atm')
                    ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'atms.id')
                    ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                    ->whereIn('atms.owner_id', [16,21,25])
                    //->where('atms.owner_id', 44) //desarrollo
                    ->whereRaw($where)
                    ->whereNull('atms.deleted_at')
                    ->whereNull('points_of_sale.deleted_at')
            ->pluck('id_atm');
            
            \Log::info(json_decode(json_encode($atms), true));

            if(empty($atms)){
                $response['error'] = true;
                $response['deuda'] = false;
                return $response;
            }

            $atm_id = implode(', ', $atms);
            
            /*$fecha_actual = Carbon::now();
            # Si hay parametros asignados al usuario y al dia actual
            if(!empty($parametro_control)){
                $dias_previos = $parametro_control->dias_previos;
                $fecha_actual = Carbon::now()->modify('-'.$dias_previos .' days');
            }else{     
                $fecha_actual = Carbon::now()->modify('-2 days');
            }*/

            $date=date('N');
                        
            if( $nanos = \DB::table('atms')->whereIn('id',$atms)->whereIn('owner_id', [21, 25])->count() <= 0){
                if($date == 1 || $date==3 ||$date==5){
                    $fecha_actual=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-1 days');
                }else if($date == 2 || $date==4 ||$date==6){
                    $fecha_actual=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-2 days');
                }else{
                    $fecha_actual=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-3 days');
                }
            }else{
                if($date==6){
                    $fecha_actual=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-2 days');
                }else if($date==7){
                    $fecha_actual=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-3 days');
                }else{
                    $fecha_actual=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-1 days');
                }
            }

            $now = Carbon::now();
            $desde = date("Y-m-d 23:59:59", strtotime($fecha_actual));
            \Log::info('Desde: '.$desde);

            $whereSales = "WHEN debit_credit = 'de' AND mt_sales.fecha <= '". $desde . "'";
            $whereCredito = "WHEN movement_type_id in (2, 3) AND m.created_at <= '". $now . "'";
            $whereCashout = "WHEN movement_type_id = 11 AND m.created_at <= '". $now . "'";

            $baseQuery = \DB::table('mt_movements as m')
                ->selectRaw(
                    "SUM(CASE ".$whereSales." THEN (m.amount) else 0 END) as transaccionado,
                    - abs(SUM(CASE ".$whereCredito." THEN (m.amount) else 0 END)) -
                    abs(SUM(CASE ".$whereCashout." THEN (m.amount) else 0 END)) as depositado,
                    SUM(CASE ".$whereSales." THEN (m.amount) else 0 END) -
                    abs(SUM(CASE ".$whereCredito." THEN (m.amount) else 0 END)) -
                    abs(SUM(CASE ".$whereCashout." THEN (m.amount) else 0 END)) as total")
                ->leftJoin('mt_sales', 'm.id', '=', 'mt_sales.movements_id')
                ->where('m.group_id',$group->group_id)
                ->whereNull('m.deleted_at')
                ->whereNotIn('m.movement_type_id',[4, 5, 7, 8, 9, 10])
            ->first();
            
            \Log::info(json_decode(json_encode($baseQuery), true));
            
            \Log::info('El saldo con la boleta #'.$boleta_deposito_id.' es '. $baseQuery->total);

            $control = [
                'message' => 'Consulta exitosa',
                'error' => false,
                'saldo' => $baseQuery->total,
                'transaccionado' => $baseQuery->transaccionado,
                'depositado' => $baseQuery->depositado,
            ];

            # Si hay parametros asignados
            $deuda = false;
            if(!empty($parametro_control)){
                # solo si el saldo es menor a 0
                if($control['saldo'] > 0){
                    if($parametro_control->tipo_control == 2){ //monto fijo
                        if($control['saldo'] > $parametro_control->saldo_minimo){
                            $deuda = true;
                        }
                    }else{ # sino, porcentual
                        # si el monto depositado es mayor a cero
                        if($control['depositado'] < 0){
                            $porcentaje_pagado = round(abs($control['depositado'])*100/$control['transaccionado'], 2);
                            if($porcentaje_pagado < $parametro_control->saldo_minimo){
                                $deuda = true;
                            }
                        }else{
                            $deuda = true;
                        }
                    }
                }
            }else{
                if($control['saldo'] > 0){
                    $deuda = true;
                }
            }

            $response['error'] = false;
            $response['deuda'] = $deuda;
            $response['atm_id'] = $atm_id;
            $response['saldo_depositado'] = $control['saldo'];

            return $response;

        } catch(\Exception $e){
            \Log::debug('Error al consultar saldo de miniterminal - balanceControlMini : '. $e);
            $response = [
                'error' => true,
                'message' => 'Error al consultar saldo de miniterminal',
                'message_user' => ''
            ];

            return $response;
        }
    }

    /* Funcion que verifica si el limite de la miniterminal fue pagada */
    public static function checkUserLimite($atm_id = null, $boleta_deposito_id){
        try {

            $rule = \DB::table('balance_rules')->where('atm_id', $atm_id)->whereIn('tipo_control', [4])->whereNull('deleted_at')->first();
            //dd($rule);
            $group = \DB::table('atms')
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'atms.id')
                ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->where('atms.id', $atm_id)
            ->first();

            if(!empty($rule)){
                $where = "atms.id = ". $atm_id; 

                $parametro_control = \DB::table('balance_rules')
                ->where([
                    'atm_id' => $atm_id,
                    'tipo_control' => 4,
                    'deleted_at' => null
                ])
                ->first();
            }else{
                \Log::info('La regla por atm_id no existe');
                $parametro_control = \DB::table('balance_rules')
                ->where([
                    'group_id' => $group->group_id,
                    'tipo_control' => 4,
                    'deleted_at' => null
                ])
                ->first();

                $where = "branches.group_id = ". $group->group_id; 
            }

            \Log::info( $where);

            $atms = \DB::table('atms')
                    ->select('atms.id as id_atm')
                    ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'atms.id')
                    ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                    ->whereIn('atms.owner_id', [16,21,25])
                    //->where('atms.owner_id', 44) //desarrollo
                    ->whereRaw($where)
                    ->whereNull('atms.deleted_at')
                    ->whereNull('points_of_sale.deleted_at')
            ->pluck('id_atm');
            
            if(empty($atms)){
                $response['message'] = 'El siguiente ATM no tiene reglas de Limite';
                $response['parametro_control'] = $parametro_control;
                $response['atms'] = $atms;
                $response['error'] = false;
                $response['deuda'] = false;
                \Log::warning($response);
                return $response;
            }

            $atm_id = implode(', ', $atms);

            $fecha_actual = Carbon::now();
            # Query para traer el total, debe y a pagar de la miniterminal

            /*$total_debe = \DB::select("
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
                left join mt_recibos_reversiones on
                    t.id = mt_recibos_reversiones.transaction_id
                left join services_ondanet_pairing sop on
                    t.service_id = sop.service_request_id
                    and t.service_source_id = sop.service_source_id
                    and t.service_source_id <> 0
                where
                    atm_id in (".$atm_id.")
                    --and status = 'success'
                    and mt_recibos_reversiones.transaction_id is null
                    and t.transaction_type = 1
                    and t.created_at <= '{$fecha_actual}'
            ");


            $total_haber = \DB::table('boletas_depositos')
                ->selectRaw('sum(monto) as total_haber')
                ->join('branches', 'branches.user_id', '=', 'boletas_depositos.user_id')
                ->whereRaw("
                    estado = true and
                    fecha <= '{$fecha_actual}'
                    and branches.group_id = ".$group->group_id."
            ")->first();

            $total_cashout = \DB::select("
                select
                -SUM(
                    CASE 
                        WHEN status = 'success' and t.amount < 0 THEN 
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
                    atm_id in (".$atm_id.")
                    --and status = 'success'
                    and t.transaction_type in (7)
                
            ");*/

            $total = \DB::table('balance_atms')
            ->selectRaw("SUM(total_transaccionado) as transaccionado, SUM(total_depositado) as depositado,
                        SUM(total_reversado) as reversado, SUM(total_cashout) as cashout, SUM(total_pago_cashout) as pago_cashout, SUM(total_pago_qr) as pago_qr, SUM(total_multa) as multa")
            ->whereIn('atm_id',$atms)
            ->first();

            $haber = -abs($total->depositado);
            $debe = abs($total->transaccionado) + abs($total->pago_cashout) + abs($total->multa);
            $reversado = -abs($total->reversado);
            $cashout = -abs($total->cashout);
            $pago_qr = -abs($total->pago_qr);
            
            $total_saldo = $debe + $haber + $reversado + $cashout + $pago_qr;

            \Log::info('El saldo con la boleta #'.$boleta_deposito_id.' es '. $total_saldo);

            $control = [
                'message'           => 'Consulta exitosa',
                'error'             => false,
                'saldo'             => $total_saldo,
                'transaccionado'    => $debe,
                'depositado'        => $haber,
                'reversado'         => $reversado,
                'cashout'           => $cashout
            ];
            \Log::info($control);
            # Si hay parametros asignados
            $deuda = false;
            if(!empty($parametro_control)){
                # solo si el saldo es menor a 0
                if($control['saldo'] > 0){
                        $deuda = true;
                }
            }else{
                $response['message'] = 'El siguiente ATM no tiene reglas de Limite';
                $response['parametro_control'] = $parametro_control;
                $response['atms'] = $atms;
                $response['error'] = true;
                $response['deuda'] = false;
                \Log::warning($response);
                return $response;
            }

            $response['error'] = false;
            $response['deuda'] = $deuda;
            $response['atm_id'] = $atm_id;
            $response['saldo_depositado'] = $control['saldo'];

            return $response;

        } catch(\Exception $e){
            \Log::debug('Error al consultar saldo de miniterminal - BalanceControlLimite : '. $e);
            $response = [
                'error' => true,
                'message' => 'Error al consultar saldo limite de miniterminal',
                'message_user' => ''
            ];

            return $response;
        }
    }

    public function insertCobranzas($boleta_deposito_id){
        
        \DB::beginTransaction();
        try{

            if(\DB::table('mt_recibos_cobranzas')->where('boleta_deposito_id', $boleta_deposito_id)->count() == 0){
                $boleta = \DB::table('boletas_depositos')->where('id',$boleta_deposito_id)->first();
            
                $usersId = \DB::table('users')
                        ->join('role_users', 'users.id', '=', 'role_users.user_id')
                        ->join('branches', 'users.id', '=', 'branches.user_id')
                        ->where('role_users.role_id', 22)
                        ->where('branches.user_id', $boleta->user_id)
                        ->first();
            
                if(isset($usersId)){
                    $group = \DB::table('branches')->where('user_id',$boleta->user_id)->first();
                }else{
                    $group = \DB::table('users_x_groups')->where('user_id',$boleta->user_id)->first();
                }
            
                \Log::info(json_decode(json_encode($group), true));

                $total_deuda = \DB::table('miniterminales_sales')
                ->selectRaw('sum(miniterminales_sales.monto_por_cobrar) as deuda')
                ->join('movements', 'movements.id', '=', 'miniterminales_sales.movements_id')
                ->join('current_account', 'movements.id', '=', 'current_account.movement_id')
                ->where('current_account.group_id',$group->group_id)
                ->where('miniterminales_sales.estado', 'pendiente')
                ->whereNull('movements.deleted_at')
                ->whereRaw("movements.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-16','-17','-21','-23','-26','-27','212', '999')")
                ->first();
                \Log::info(json_decode(json_encode($total_deuda), true));
                $diferencia=$total_deuda->deuda - $boleta->monto;
                \Log::info('[Deposito de Boleta] Diferencia: '. $diferencia);
            
                if($diferencia >=0){
                    \Log::info('[Deposito de Boleta] La diferencia es mayor a cero');
                    //Consulta para traer las ventas que deben ser cobradas
                    $sales = \DB::table('miniterminales_sales')
                    ->select('movements.id', 'current_account.group_id', 'movements.amount', 'miniterminales_sales.estado','miniterminales_sales.monto_por_cobrar')
                    ->join('movements', 'movements.id', '=', 'miniterminales_sales.movements_id')
                    ->join('current_account', 'movements.id', '=', 'current_account.movement_id')
                    ->where('current_account.group_id', $group->group_id)
                    ->where('miniterminales_sales.estado', 'pendiente')
                    ->whereNull('movements.deleted_at')
                    ->whereRaw("movements.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-15','-16','-17','-21','-23','-26','-27','212', '999')")
                    ->orderBy('movements.destination_operation_id','ASC')
                    ->get();

                    $array=json_decode(json_encode($sales), true);
                    \Log::info($array);
                    $i=0;
                    $sum=0;
                    //deposito a ser cobrado
                    $deposito=$boleta->monto;

                    //algoritmo para traer cuantas ventas van a ser cobradas
                    do{

                        $sum += $array[$i]['monto_por_cobrar'];
                        $i++;
                        
                    }while($sum < $deposito);

                    \Log::info("[Deposito de Boleta] La sumatoria es: " . $sum . " Y las veces que sumo fue: " . $i . "<p>");

                    //diferencia de del deposito y la sumatoria total de las ventas
                    $dif=$sum-$deposito;

                    \Log::info("[Deposito de Boleta] La diferencia es: " . $dif . "<p>");

                    $idventasondanet = \DB::table('miniterminales_sales')
                        ->select('movements.destination_operation_id')
                        ->join('movements', 'movements.id', '=', 'miniterminales_sales.movements_id')
                        ->join('current_account', 'movements.id', '=', 'current_account.movement_id')
                        ->where('current_account.group_id', $group->group_id)
                        ->where('miniterminales_sales.estado', 'pendiente')
                        ->whereNull('movements.deleted_at')
                        ->whereRaw("movements.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-15','-16','-17','-21','-23','-26','-27','212','999')")
                        ->orderBy('movements.destination_operation_id','ASC')
                        ->take($i)
                    ->pluck('movements.destination_operation_id');

                    $ventasondanet = implode(';', $idventasondanet);
                    \Log::info('[Deposito de Boleta] Ventas a cobrar', ['ventasondanet' => $ventasondanet]);
                    
                    //Para enviar el numero de recibo y insertar en miniterminales cobranzas
                    /*$last_row = \DB::table('miniterminales_cobranzas')->orderBy('recibo_nro','desc')->first();
                    $toInt=(int)$last_row->recibo_nro;
                    $number=$toInt + 1;
                    $length = 7;
                    $numero_recibo = substr(str_repeat(0, $length).$number, - $length);*/

                    $movement_id=\DB::table('movements')->insertGetId([
                        'movement_type_id'          => 2,
                        'destination_operation_id'  => 0,
                        'amount'                    => -(int)$boleta->monto,
                        'debit_credit'              =>  'cr',
                        'created_at'                => Carbon::now(),
                        'updated_at'                => Carbon::now()        

                    ]);

                    $last_balance = \DB::table('current_account')->where('group_id',$group->group_id)->orderBy('id','desc')->first();
                    if(isset($last_balance)){
                        $balance= $last_balance->balance -(int)$boleta->monto;
                    }else{
                        $balance= -(int)$boleta->monto;
                    }

                    \DB::table('current_account')->insert([
                        'movement_id'               => $movement_id,    
                        'group_id'                  => $group->group_id,
                        'amount'                    => -(int)$boleta->monto,
                        'balance'                   => $balance, 
                    ]);

                    $recibo_id=\DB::table('mt_recibos')->insertGetId([
                        'tipo_recibo_id'    => 3,
                        'movements_id'       => $movement_id,
                        'monto'             => (int)$boleta->monto,
                        'created_at'        => Carbon::now(), 
                        'updated_at'        => Carbon::now()
                    ]);

                    $recibo_id_deuda=\DB::table('mt_recibos_cobranzas')->insert([
                        'recibo_id'             => $recibo_id,
                        'boleta_deposito_id'    => $boleta_deposito_id,
                        'ventas_cobradas'       => $ventasondanet,
                        'saldo_pendiente'       => 0
                    ]);

                    /*$alerts = \DB::table('miniterminales_cobranzas')->insert([
                        'movements_id'          => $movement_id,
                        'boleta_deposito_id'    => $boleta_deposito_id,
                        'ventas_cobradas'       => $ventasondanet,
                        'recibo_nro'            => $numero_recibo,
                        'saldo_pendiente'       => 0
                    ]);*/

                    if($i==1){

                        $idventas = \DB::table('miniterminales_sales')
                        ->select('movements.id')
                        ->join('movements', 'movements.id', '=', 'miniterminales_sales.movements_id')
                        ->join('current_account', 'movements.id', '=', 'current_account.movement_id')
                        ->where('current_account.group_id', $group->group_id)
                        ->where('miniterminales_sales.estado', 'pendiente')
                        ->whereNull('movements.deleted_at')
                        ->whereRaw("movements.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-15','-16','-17','-21','-23','-26','-27','212','999')")
                        ->orderBy('movements.destination_operation_id','ASC')
                        ->take($i)
                        ->pluck('movements.id');

                        $venta = implode(', ', $idventas);

                        if($dif==0){
                            \DB::table('miniterminales_sales')
                                ->where('movements_id', $venta)
                                ->update([
                                    'estado'            => 'cancelado',
                                    'monto_por_cobrar'   => 0
                                ]);

                            \DB::table('movements')->where('id', $venta)->update([ 'updated_at' => Carbon::now() ]);

                        }else{
                            \DB::table('miniterminales_sales')
                                ->where('movements_id', $venta)
                                ->update([
                                    'estado' => 'pendiente',
                                    'monto_por_cobrar'   => $dif
                                ]);

                            \DB::table('movements')->where('id', $venta)->update([ 'updated_at' => Carbon::now() ]);
                        }
                        \Log::info('Ventas cobradas exitosamente');
                    }else{
                        if($dif==0){

                            $idventas = \DB::table('miniterminales_sales')
                            ->select('movements.id')
                            ->join('movements', 'movements.id', '=', 'miniterminales_sales.movements_id')
                            ->join('current_account', 'movements.id', '=', 'current_account.movement_id')
                            ->where('current_account.group_id', $group->group_id)
                            ->where('miniterminales_sales.estado', 'pendiente')
                            ->whereNull('movements.deleted_at')
                            ->whereRaw("movements.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-15','-16','-17','-21','-23','-26','-27','212','999')")
                            ->orderBy('movements.destination_operation_id','ASC')
                            ->take($i)
                            ->pluck('movements.id');

                            foreach($idventas as $venta){
                                \DB::table('miniterminales_sales')
                                ->where('movements_id', $venta)
                                ->update([
                                    'estado'            => 'cancelado',
                                    'monto_por_cobrar'   => 0
                                ]);

                                \DB::table('movements')->where('id', $venta)->update([ 'updated_at' => Carbon::now() ]);
                            }
                            
                        }else{
                            $cobradostotal=$i-1;

                            $salescobradas = \DB::table('miniterminales_sales')
                            ->select('movements.id')
                            ->join('movements', 'movements.id', '=', 'miniterminales_sales.movements_id')
                            ->join('current_account', 'movements.id', '=', 'current_account.movement_id')
                            ->where('current_account.group_id', $group->group_id)
                            ->where('miniterminales_sales.estado', 'pendiente')
                            ->whereNull('movements.deleted_at')
                            ->whereRaw("movements.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-15','-16','-17','-21','-23','-26','-27','212','999')")
                            ->orderBy('movements.destination_operation_id','ASC')
                            ->take($cobradostotal)
                            ->pluck('movements.id');

                            foreach($salescobradas as $salecobrada){
                                $result=\DB::table('miniterminales_sales')
                                ->where('movements_id', $salecobrada)
                                ->update([
                                    'estado' => 'cancelado',
                                    'monto_por_cobrar'   => 0
                                ]);

                                \DB::table('movements')->where('id', $salecobrada)->update([ 'updated_at' => Carbon::now() ]);
                            }

                            if($result = true){

                                $salefaltante = \DB::table('miniterminales_sales')
                                ->select('movements.id')
                                ->join('movements', 'movements.id', '=', 'miniterminales_sales.movements_id')
                                ->join('current_account', 'movements.id', '=', 'current_account.movement_id')
                                ->where('current_account.group_id', $group->group_id)
                                ->where('miniterminales_sales.estado', 'pendiente')
                                ->whereNull('movements.deleted_at')
                                ->whereRaw("movements.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-15','-16','-17','-21','-23','-26','-27','212','999')")
                                ->orderBy('movements.destination_operation_id','ASC')
                                ->take(1)
                                ->pluck('movements.id');

                                $sale = implode(', ', $salefaltante);

                                \DB::table('miniterminales_sales')
                                ->where('movements_id', $sale)
                                ->update([
                                    'estado' => 'pendiente',
                                    'monto_por_cobrar'   => $dif
                                ]);
                                \DB::table('movements')->where('id', $sale)->update([ 'updated_at' => Carbon::now() ]);
                            }
                        }

                        \Log::info('[Deposito de Boleta] Ventas cobradas exitosamente');
                    }
                }else{
                    \Log::info('[Deposito de Boleta] El monto del recibo es mayor que las ventas del grupo, no se procedera a cobrar ventas');
                    //Para enviar el numero de recibo y insertar en miniterminales cobranzas
                    /*$last_row = \DB::table('miniterminales_cobranzas')->orderBy('recibo_nro','desc')->first();
                    $toInt=(int)$last_row->recibo_nro;
                    $number=$toInt + 1;
                    $length = 7;
                    $numero_recibo = substr(str_repeat(0, $length).$number, - $length);*/

                    if($total_deuda->deuda > 0){
                        \Log::info('[Deposito de Boleta] Se procedera a crear 2 recibos, un recibo que salda la deuda y otro recibo a favor');

                        $idventasondanet = \DB::table('miniterminales_sales')
                        ->select('movements.destination_operation_id')
                        ->join('movements', 'movements.id', '=', 'miniterminales_sales.movements_id')
                        ->join('current_account', 'movements.id', '=', 'current_account.movement_id')
                        ->where('current_account.group_id', $group->group_id)
                        ->where('miniterminales_sales.estado', 'pendiente')
                        ->whereNull('movements.deleted_at')
                        ->whereRaw("movements.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-15','-16','-17','-21','-23','-26','-27','212','999')")
                        ->orderBy('movements.destination_operation_id','ASC')
                        ->pluck('movements.destination_operation_id');
                        $ventasondanet = implode(';', $idventasondanet);
                        \Log::info('[Deposito de Boleta] Ventas a cobrar', ['ventasondanet' => $ventasondanet]);

                        $saldo_a_favor=$boleta->monto - $total_deuda->deuda;

                        $movement_id=\DB::table('movements')->insertGetId([
                            'movement_type_id'          => 2,
                            'destination_operation_id'  => 0,
                            'amount'                    => -(int)$total_deuda->deuda,
                            'debit_credit'              =>  'cr',
                            'created_at'                => Carbon::now(),
                            'updated_at'                => Carbon::now()        
        
                        ]);

                        $last_balance = \DB::table('current_account')->where('group_id',$group->group_id)->orderBy('id','desc')->first();
                        if(isset($last_balance)){
                            $balance= $last_balance->balance -(int)$total_deuda->deuda;
                        }else{
                            $balance= -(int)$total_deuda->deuda;
                        }

                        \DB::table('current_account')->insert([
                            'movement_id'               => $movement_id,    
                            'group_id'                  => $group->group_id,
                            'amount'                    => -(int)$total_deuda->deuda,
                            'balance'                   => $balance, 
                        ]);

                        $recibo_id=\DB::table('mt_recibos')->insertGetId([
                            'tipo_recibo_id'    => 3,
                            'movements_id'       => $movement_id,
                            'monto'             => (int)$total_deuda->deuda,
                            'created_at'        => Carbon::now(), 
                            'updated_at'        => Carbon::now()
                        ]);

                        $recibo_id_deuda=\DB::table('mt_recibos_cobranzas')->insert([
                            'recibo_id'             => $recibo_id,
                            'boleta_deposito_id'    => $boleta_deposito_id,
                            'ventas_cobradas'       => $ventasondanet,
                            'saldo_pendiente'       => 0
                        ]);

                        $idventas = \DB::table('miniterminales_sales')
                            ->select('movements.id')
                            ->join('movements', 'movements.id', '=', 'miniterminales_sales.movements_id')
                            ->join('current_account', 'movements.id', '=', 'current_account.movement_id')
                            ->where('current_account.group_id', $group->group_id)
                            ->where('miniterminales_sales.estado', 'pendiente')
                            ->whereNull('movements.deleted_at')
                            ->whereRaw("movements.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-15','-16','-17','-21','-23','-26','-27','212','999')")
                            ->orderBy('movements.destination_operation_id','ASC')
                        ->pluck('movements.id');

                        foreach($idventas as $venta){
                            \DB::table('miniterminales_sales')
                            ->where('movements_id', $venta)
                            ->update([
                                'estado'            => 'cancelado',
                                'monto_por_cobrar'   => 0
                            ]);

                            \DB::table('movements')->where('id', $venta)->update([ 'updated_at' => Carbon::now() ]);
                        }

                        if($recibo_id_deuda){
                            $movement_id=\DB::table('movements')->insertGetId([
                                'movement_type_id'          => 2,
                                'destination_operation_id'  => 666,
                                'amount'                    => -(int)$saldo_a_favor,
                                'debit_credit'              =>  'cr',
                                'created_at'                => Carbon::now(),
                                'updated_at'                => Carbon::now()        
            
                            ]);
        
                            $last_balance = \DB::table('current_account')->where('group_id',$group->group_id)->orderBy('id','desc')->first();
                            if(isset($last_balance)){
                                $balance= $last_balance->balance -(int)$saldo_a_favor;
                            }else{
                                $balance= -(int)$saldo_a_favor;
                            }
        
                            \DB::table('current_account')->insert([
                                'movement_id'               => $movement_id,    
                                'group_id'                  => $group->group_id,
                                'amount'                    => -(int)$saldo_a_favor,
                                'balance'                   => $balance, 
                            ]);
        
                            $recibo_id=\DB::table('mt_recibos')->insertGetId([
                                'tipo_recibo_id'    => 3,
                                'movements_id'       => $movement_id,
                                'monto'             => (int)$saldo_a_favor,
                                'created_at'        => Carbon::now(), 
                                'updated_at'        => Carbon::now()
                            ]);
        
                            $recibo_cobranza=\DB::table('mt_recibos_cobranzas')->insert([
                                'recibo_id'             => $recibo_id,
                                'boleta_deposito_id'    => $boleta_deposito_id,
                                'saldo_pendiente'       => (int)$saldo_a_favor
                            ]);
                        }

                        
                    }else{
                        $movement_id=\DB::table('movements')->insertGetId([
                            'movement_type_id'          => 2,
                            'destination_operation_id'  => 666,
                            'amount'                    => -(int)$boleta->monto,
                            'debit_credit'              =>  'cr',
                            'created_at'                => Carbon::now(),
                            'updated_at'                => Carbon::now()        
        
                        ]);

                        $last_balance = \DB::table('current_account')->where('group_id',$group->group_id)->orderBy('id','desc')->first();
                        if(isset($last_balance)){
                            $balance= $last_balance->balance -(int)$boleta->monto;
                        }else{
                            $balance= -(int)$boleta->monto;
                        }

                        \DB::table('current_account')->insert([
                            'movement_id'               => $movement_id,    
                            'group_id'                  => $group->group_id,
                            'amount'                    => -(int)$boleta->monto,
                            'balance'                   => $balance, 
                        ]);

                        $recibo_id=\DB::table('mt_recibos')->insertGetId([
                            'tipo_recibo_id'    => 3,
                            'movements_id'       => $movement_id,
                            'monto'             =>(int)$boleta->monto,
                            'created_at'        => Carbon::now(), 
                            'updated_at'        => Carbon::now()
                        ]);

                        $recibo_cobranza=\DB::table('mt_recibos_cobranzas')->insert([
                            'recibo_id'             => $recibo_id,
                            'boleta_deposito_id'    => $boleta_deposito_id,
                            'saldo_pendiente'       => (int)$boleta->monto
                        ]);
                    }
                    
                }

                \DB::table('mt_deposits')->insert([
                    'ondanet_code'              =>  '026',
                    'created_at'                =>  Carbon::now(),
                    'updated_at'                =>  Carbon::now(),
                    'recibo_id'                 => $recibo_id,
                    'destination_operation_id'  => 0
                ]);

                $response_balance= $this->updateBalanceBoletas($boleta_deposito_id);
                \Log::warning($response_balance);

                $response_block= $this->checkBlock($boleta_deposito_id);
                \Log::warning($response_block);
                
                $response['error'] = false;
                $response['message'] = 'Registro guardado exitosamente';
            }else{
                $response['error'] = false;
                $response['message'] = 'No se procedio a crear Recibo';
            }    
            \DB::commit();
            return $response;
        }catch(\Exception $e){
            \DB::rollback();
            \Log::error("[Deposito de Boleta]  - {$e->getMessage()}");
            $response = [
                'error' => true,
                'message' => 'Error al consultar saldo de miniterminal',
                'message_user' => ''
            ];

            return $response;
        }
    }

    public function insertCobranzas_V2($boleta_deposito_id){
        
        \DB::beginTransaction();
        try{

            $movimiento=0;
            if(\DB::table('mt_recibos_cobranzas')->where('boleta_deposito_id', $boleta_deposito_id)->count() == 0){
                $boleta = \DB::table('boletas_depositos')->where('id',$boleta_deposito_id)->first();
                
                $group = \DB::table('business_groups as bg')
                    ->selectRaw('bg.*')
                    ->join('branches', 'bg.id', '=', 'branches.group_id')
                    ->join('points_of_sale as pos', 'branches.id', '=', 'pos.branch_id')
                    ->where('pos.atm_id', $boleta->atm_id)
                ->first();
                        
                \Log::info(json_decode(json_encode($group), true));

                $total_deuda = \DB::table('mt_sales')
                    ->selectRaw('sum(mt_sales.monto_por_cobrar) as deuda')
                    ->join('mt_movements as m', 'm.id', '=', 'mt_sales.movements_id')
                    ->where('m.group_id', $group->id)
                    ->where('mt_sales.estado', 'pendiente')
                    ->whereNull('m.deleted_at')
                    ->whereRaw("m.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-16','-17','-21','-23','-26','-27','212', '999')")
                ->first();

                \Log::info(json_decode(json_encode($total_deuda), true));
                $diferencia=$total_deuda->deuda - $boleta->monto;
                \Log::info('[Deposito de Boleta] Diferencia: '. $diferencia);

                if($diferencia >=0){
                    \Log::info('[Deposito de Boleta] La diferencia es mayor a cero');
                    //Consulta para traer las ventas que deben ser cobradas
                    $sales = \DB::table('mt_sales')
                        ->select('m.id', 'm.group_id', 'm.amount', 'mt_sales.estado','mt_sales.monto_por_cobrar')
                        ->join('mt_movements as m', 'm.id', '=', 'mt_sales.movements_id')
                        ->where('m.group_id', $group->id)
                        ->where('mt_sales.estado', 'pendiente')
                        ->whereNull('m.deleted_at')
                        ->whereRaw("m.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-15','-16','-17','-21','-23','-26','-27','212', '999')")
                        ->orderBy('m.destination_operation_id','ASC')
                    ->get();

                    $array=json_decode(json_encode($sales), true);
                    \Log::info($array);
                    $i=0;
                    $sum=0;
                    //deposito a ser cobrado
                    $deposito=$boleta->monto;

                    //algoritmo para traer cuantas ventas van a ser cobradas
                    do{

                        $sum += $array[$i]['monto_por_cobrar'];
                        $i++;
                        
                    }while($sum < $deposito);

                    \Log::info("[Deposito de Boleta] La sumatoria es: " . $sum . " Y las veces que sumo fue: " . $i . "<p>");

                    //diferencia de del deposito y la sumatoria total de las ventas
                    $dif=$sum-$deposito;

                    \Log::info("[Deposito de Boleta] La diferencia es: " . $dif . "<p>");

                    /*$idventasondanet = \DB::table('mt_sales')
                        ->select('m.destination_operation_id')
                        ->join('mt_movements as m', 'm.id', '=', 'mt_sales.movements_id')
                        ->where('m.group_id', $group->id)
                        ->where('mt_sales.estado', 'pendiente')
                        ->whereNull('m.deleted_at')
                        ->whereRaw("m.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-15','-16','-17','-21','-23','-26','-27','212','999')")
                        ->orderBy('m.destination_operation_id','ASC')
                        ->take($i)
                    ->pluck('m.destination_operation_id');

                    $ventasondanet = implode(';', $idventasondanet);*/

                    $idventasondanet = \DB::table('mt_sales')
                        ->selectRaw('m.destination_operation_id, mt_sales.monto_por_cobrar, mt_sales.id as sale_id, m.id as movement_id')
                        ->join('mt_movements as m', 'm.id', '=', 'mt_sales.movements_id')
                        ->where('m.group_id', $group->id)
                        ->where('mt_sales.estado', 'pendiente')
                        ->whereNull('m.deleted_at')
                        ->whereRaw("m.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-15','-16','-17','-21','-23','-26','-27','212','999')")
                        ->orderBy('m.destination_operation_id','ASC')
                        ->take($i)
                    ->get();

                    $ventasondanet = implode(';', array_column($idventasondanet, 'destination_operation_id'));
                    \Log::info('[Deposito de Boleta] Ventas a cobrar', ['ventasondanet' => $ventasondanet]);

                    $last_balance = \DB::table('mt_movements')->where('atm_id',$boleta->atm_id)->orderBy('id','desc')->first();

                    if(isset($last_balance)){
                        $balance= $last_balance->balance -(int)$boleta->monto;
                        $balance_antes= $last_balance->balance;
                    }else{
                        $balance= -(int)$boleta->monto;
                        $balance_antes=0;
                    }

                    $movement_id=\DB::table('mt_movements')->insertGetId([
                        'movement_type_id'          => 2,
                        'destination_operation_id'  => 0,
                        'amount'                    => -(int)$boleta->monto,
                        'debit_credit'              =>  'cr',
                        'created_at'                => Carbon::now(),
                        'updated_at'                => Carbon::now(),
                        'group_id'                  => $group->id,
                        'atm_id'                    => $boleta->atm_id,
                        'balance_antes'             => $balance_antes,
                        'balance'                   => $balance

                    ]);

                    $recibo_id=\DB::table('mt_recibos')->insertGetId([
                        'tipo_recibo_id'    => 3,
                        'mt_movements_id'   => $movement_id,
                        'monto'             => (int)$boleta->monto,
                        'created_at'        => Carbon::now(), 
                        'updated_at'        => Carbon::now()
                    ]);

                    $recibo_id_deuda=\DB::table('mt_recibos_cobranzas')->insert([
                        'recibo_id'             => $recibo_id,
                        'boleta_deposito_id'    => $boleta_deposito_id,
                        'ventas_cobradas'       => $ventasondanet,
                        'saldo_pendiente'       => 0
                    ]);

                    $recibo_id      = $recibo_id;
                    $now            = Carbon::now();
                    $description    = 'Recibo Cobranza de Boleta insertado desde la Funcion: insertCobranzas_V2';

                    if($i==1){

                        /*$idventas = \DB::table('mt_sales')
                            ->select('m.id')
                            ->join('mt_movements as m', 'm.id', '=', 'mt_sales.movements_id')
                            ->where('m.group_id', $group->id)
                            ->where('mt_sales.estado', 'pendiente')
                            ->whereNull('m.deleted_at')
                            ->whereRaw("m.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-15','-16','-17','-21','-23','-26','-27','212','999')")
                            ->orderBy('m.destination_operation_id','ASC')
                            ->take($i)
                        ->pluck('m.id');

                        $venta = implode(', ', $idventas);

                        if($dif==0){
                            \DB::table('mt_sales')
                                ->where('movements_id', $venta)
                                ->update([
                                    'estado'            => 'cancelado',
                                    'monto_por_cobrar'   => 0
                                ]);

                            \DB::table('mt_movements')->where('id', $venta)->update([ 'updated_at' => Carbon::now() ]);

                        }else{
                            \DB::table('mt_sales')
                                ->where('movements_id', $venta)
                                ->update([
                                    'estado' => 'pendiente',
                                    'monto_por_cobrar'   => $dif
                                ]);

                            \DB::table('mt_movements')->where('id', $venta)->update([ 'updated_at' => Carbon::now() ]);
                        }*/

                        $venta = $idventasondanet[0];

                        $sale_id                = $venta->sale_id;
                        $sales_amount           = $venta->monto_por_cobrar;
                        $sales_amount_affected  = $deposito;
                        $sales_amount_pendding  = $dif;
                        
                        $this->insert_mt_sales_affected_by_receipts($recibo_id, $sale_id, $sales_amount, $sales_amount_affected, $sales_amount_pendding, $now, $description);

                        if ($dif == 0) {
                            \DB::table('mt_sales')
                            ->where('movements_id', $venta->movement_id)
                            ->update([
                                'estado'            => 'cancelado',
                                'monto_por_cobrar'   => 0
                            ]);

                            \DB::table('mt_movements')->where('id', $venta->movement_id)->update(['updated_at' => Carbon::now()]);
                        } else {
                            \DB::table('mt_sales')
                                ->where('movements_id', $venta->movement_id)
                                ->update([
                                'estado' => 'pendiente',
                                'monto_por_cobrar'   => $dif
                            ]);

                            \DB::table('mt_movements')->where('id', $venta->movement_id)->update(['updated_at' => Carbon::now()]);
                        }
                        \Log::info('Ventas cobradas exitosamente');
                    }else{
                        if($dif==0){

                            foreach($idventasondanet as $idventa){

                                $sale_id                = $idventa->sale_id;
                                $sales_amount           = $idventa->monto_por_cobrar;
                                $sales_amount_affected  = $idventa->monto_por_cobrar;
                                $sales_amount_pendding  = $dif;

                                $this->insert_mt_sales_affected_by_receipts($recibo_id, $sale_id, $sales_amount, $sales_amount_affected, $sales_amount_pendding, $now, $description);

                                \DB::table('mt_sales')
                                    ->where('movements_id', $idventa->movement_id)
                                    ->update([
                                        'estado'            => 'cancelado',
                                        'monto_por_cobrar'   => 0
                                ]);

                                \DB::table('mt_movements')->where('id', $idventa->movement_id)->update(['updated_at' => Carbon::now()]);
                            }

                            /*$idventas = \DB::table('mt_sales')
                                ->select('m.id')
                                ->join('mt_movements as m', 'm.id', '=', 'mt_sales.movements_id')
                                ->where('m.group_id', $group->id)
                                ->where('mt_sales.estado', 'pendiente')
                                ->whereNull('m.deleted_at')
                                ->whereRaw("m.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-15','-16','-17','-21','-23','-26','-27','212','999')")
                                ->orderBy('m.destination_operation_id','ASC')
                                ->take($i)
                            ->pluck('m.id');

                            foreach($idventas as $venta){
                                \DB::table('mt_sales')
                                ->where('movements_id', $venta)
                                ->update([
                                    'estado'            => 'cancelado',
                                    'monto_por_cobrar'   => 0
                                ]);

                                \DB::table('mt_movements')->where('id', $venta)->update([ 'updated_at' => Carbon::now() ]);
                            }*/
                            
                        }else{
                            /*$cobradostotal=$i-1;

                            $salescobradas = \DB::table('mt_sales')
                                ->select('m.id')
                                ->join('mt_movements as m', 'm.id', '=', 'mt_sales.movements_id')
                                ->where('m.group_id', $group->id)
                                ->where('mt_sales.estado', 'pendiente')
                                ->whereNull('m.deleted_at')
                                ->whereRaw("m.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-15','-16','-17','-21','-23','-26','-27','212','999')")
                                ->orderBy('m.destination_operation_id','ASC')
                                ->take($cobradostotal)
                            ->pluck('m.id');

                            foreach($salescobradas as $salecobrada){
                                $result=\DB::table('mt_sales')
                                ->where('movements_id', $salecobrada)
                                ->update([
                                    'estado' => 'cancelado',
                                    'monto_por_cobrar'   => 0
                                ]);

                                \DB::table('mt_movements')->where('id', $salecobrada)->update([ 'updated_at' => Carbon::now() ]);
                            }*/

                            $sobrante = end($idventasondanet);

                            foreach ($idventasondanet as $idventa) {

                                if($sobrante !== $idventa){

                                    $sale_id                = $idventa->sale_id;
                                    $sales_amount           = $idventa->monto_por_cobrar;
                                    $sales_amount_affected  = $idventa->monto_por_cobrar;
                                    $sales_amount_pendding  = 0;

                                    $this->insert_mt_sales_affected_by_receipts($recibo_id, $sale_id, $sales_amount, $sales_amount_affected, $sales_amount_pendding, $now, $description);

                                    $result = \DB::table('mt_sales')
                                        ->where('movements_id', $idventa->movement_id)
                                        ->update([
                                            'estado' => 'cancelado',
                                            'monto_por_cobrar'   => 0
                                    ]);

                                    \DB::table('mt_movements')->where('id', $idventa->movement_id)->update(['updated_at' => Carbon::now()]);
                                }
                                
                            }

                            if($result = true){

                                /*$salefaltante = \DB::table('mt_sales')
                                    ->select('m.id')
                                    ->join('mt_movements as m', 'm.id', '=', 'mt_sales.movements_id')
                                    ->where('m.group_id', $group->id)
                                    ->where('mt_sales.estado', 'pendiente')
                                    ->whereNull('m.deleted_at')
                                    ->whereRaw("m.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-15','-16','-17','-21','-23','-26','-27','212','999')")
                                    ->orderBy('m.destination_operation_id','ASC')
                                    ->take(1)
                                ->pluck('m.id');

                                $sale = implode(', ', $salefaltante);

                                \DB::table('mt_sales')
                                    ->where('movements_id', $sale)
                                    ->update([
                                    'estado' => 'pendiente',
                                    'monto_por_cobrar'   => $dif
                                ]);
                                \DB::table('mt_movements')->where('id', $sale)->update([ 'updated_at' => Carbon::now() ]);*/

                                $sale_id                = $sobrante->sale_id;
                                $sales_amount           = $sobrante->monto_por_cobrar;
                                $sales_amount_affected  = $sobrante->monto_por_cobrar - $dif;
                                $sales_amount_pendding  = $dif;

                                $this->insert_mt_sales_affected_by_receipts($recibo_id, $sale_id, $sales_amount, $sales_amount_affected, $sales_amount_pendding, $now, $description);

                                \DB::table('mt_sales')
                                    ->where('movements_id', $sobrante->movement_id)
                                    ->update([
                                        'estado' => 'pendiente',
                                        'monto_por_cobrar'   => $dif
                                ]);
                                \DB::table('mt_movements')->where('id', $sobrante->movement_id)->update(['updated_at' => Carbon::now()]);
                            }
                        }

                        \Log::info('[Deposito de Boleta] Ventas cobradas exitosamente');
                    }

                    $movimiento=$movement_id;
                }else{
                    \Log::info('[Deposito de Boleta] El monto del recibo es mayor que las ventas del grupo, no se procedera a cobrar ventas');

                    if($total_deuda->deuda > 0){
                        \Log::info('[Deposito de Boleta] Se procedera a crear 2 recibos, un recibo que salda la deuda y otro recibo a favor');

                        /*$idventasondanet = \DB::table('mt_sales')
                            ->select('m.destination_operation_id')
                            ->join('mt_movements as m', 'm.id', '=', 'mt_sales.movements_id')
                            ->where('m.group_id', $group->id)
                            ->where('mt_sales.estado', 'pendiente')
                            ->whereNull('m.deleted_at')
                            ->whereRaw("m.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-15','-16','-17','-21','-23','-26','-27','212','999')")
                            ->orderBy('m.destination_operation_id','ASC')
                        ->pluck('m.destination_operation_id');
                        $ventasondanet = implode(';', $idventasondanet);*/

                        $idventasondanet = \DB::table('mt_sales')
                        ->selectRaw('m.destination_operation_id, mt_sales.monto_por_cobrar, mt_sales.id as sale_id, m.id as movement_id')
                            ->join('mt_movements as m', 'm.id', '=', 'mt_sales.movements_id')
                            ->where('m.group_id', $group->id)
                            ->where('mt_sales.estado', 'pendiente')
                            ->whereNull('m.deleted_at')
                            ->whereRaw("m.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-15','-16','-17','-21','-23','-26','-27','212','999')")
                            ->orderBy('m.destination_operation_id','ASC')
                        ->get();
                        $ventasondanet = implode(';', array_column($idventasondanet, 'destination_operation_id'));
                        \Log::info('[Deposito de Boleta] Ventas a cobrar', ['ventasondanet' => $ventasondanet]);

                        $saldo_a_favor=$boleta->monto - $total_deuda->deuda;

                        $last_balance = \DB::table('mt_movements')->where('atm_id',$boleta->atm_id)->orderBy('id','desc')->first();

                        if(isset($last_balance)){
                            $balance= $last_balance->balance -(int)$total_deuda->deuda;
                            $balance_antes= $last_balance->balance;
                        }else{
                            $balance= -(int)$total_deuda->deuda;
                            $balance_antes=0;
                        }

                        $movement_id=\DB::table('mt_movements')->insertGetId([
                            'movement_type_id'          => 2,
                            'destination_operation_id'  => 0,
                            'amount'                    => -(int)$total_deuda->deuda,
                            'debit_credit'              =>  'cr',
                            'created_at'                => Carbon::now(),
                            'updated_at'                => Carbon::now(),
                            'group_id'                  => $group->id,
                            'atm_id'                    => $boleta->atm_id,
                            'balance_antes'             => $balance_antes,
                            'balance'                   => $balance
    
                        ]);

                        $recibo_id=\DB::table('mt_recibos')->insertGetId([
                            'tipo_recibo_id'    => 3,
                            'mt_movements_id'       => $movement_id,
                            'monto'             => (int)$total_deuda->deuda,
                            'created_at'        => Carbon::now(), 
                            'updated_at'        => Carbon::now()
                        ]);

                        $recibo_id_deuda=\DB::table('mt_recibos_cobranzas')->insert([
                            'recibo_id'             => $recibo_id,
                            'boleta_deposito_id'    => $boleta_deposito_id,
                            'ventas_cobradas'       => $ventasondanet,
                            'saldo_pendiente'       => 0
                        ]);

                        /*$idventas = \DB::table('mt_sales')
                            ->select('m.id')
                            ->join('mt_movements as m', 'm.id', '=', 'mt_sales.movements_id')
                            ->where('m.group_id', $group->id)
                            ->where('mt_sales.estado', 'pendiente')
                            ->whereNull('m.deleted_at')
                            ->whereRaw("m.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-15','-16','-17','-21','-23','-26','-27','212','999')")
                            ->orderBy('m.destination_operation_id','ASC')
                        ->pluck('m.id');

                        foreach($idventas as $venta){
                            \DB::table('mt_sales')
                            ->where('movements_id', $venta)
                            ->update([
                                'estado'            => 'cancelado',
                                'monto_por_cobrar'   => 0
                            ]);

                            \DB::table('mt_movements')->where('id', $venta)->update([ 'updated_at' => Carbon::now() ]);
                        }*/

                        $service = new TerminalsPaymentsServices();

                        $recibo_id      = $recibo_id;
                        $now            = Carbon::now();
                        $description    = 'Recibo Cobranza de Boleta insertado desde la Funcion: insertCobranzas_V2';

                        foreach ($idventasondanet as $idventa) {

                            $sale_id                = $idventa->sale_id;
                            $sales_amount           = $idventa->monto_por_cobrar;
                            $sales_amount_affected  = $idventa->monto_por_cobrar;
                            $sales_amount_pendding  = 0;

                            $this->insert_mt_sales_affected_by_receipts($recibo_id, $sale_id, $sales_amount, $sales_amount_affected, $sales_amount_pendding, $now, $description);

                            \DB::table('mt_sales')
                                ->where('movements_id', $idventa->movement_id)
                                ->update([
                                    'estado'            => 'cancelado',
                                    'monto_por_cobrar'   => 0
                                ]);

                            \DB::table('mt_movements')->where('id', $idventa->movement_id)->update(['updated_at' => Carbon::now()]);
                        }

                        $movimiento=$movement_id;

                        if($recibo_id_deuda){

                            $last_balance = \DB::table('mt_movements')->where('atm_id',$boleta->atm_id)->orderBy('id','desc')->first();
                            if(isset($last_balance)){
                                $balance= $last_balance->balance -(int)$saldo_a_favor;
                                $balance_antes= $last_balance->balance;
                            }else{
                                $balance= -(int)$saldo_a_favor;
                                $balance_antes=0;
                            }

                            $movement_id=\DB::table('mt_movements')->insertGetId([
                                'movement_type_id'          => 2,
                                'destination_operation_id'  => 666,
                                'amount'                    => -(int)$saldo_a_favor,
                                'debit_credit'              =>  'cr',
                                'created_at'                => Carbon::now(),
                                'updated_at'                => Carbon::now(),
                                'group_id'                  => $group->id,
                                'atm_id'                    => $boleta->atm_id,
                                'balance_antes'             => $balance_antes,
                                'balance'                   => $balance
        
                            ]);
        
                            $recibo_id=\DB::table('mt_recibos')->insertGetId([
                                'tipo_recibo_id'    => 3,
                                'mt_movements_id'   => $movement_id,
                                'monto'             => (int)$saldo_a_favor,
                                'created_at'        => Carbon::now(), 
                                'updated_at'        => Carbon::now()
                            ]);
        
                            $recibo_cobranza=\DB::table('mt_recibos_cobranzas')->insert([
                                'recibo_id'             => $recibo_id,
                                'boleta_deposito_id'    => $boleta_deposito_id,
                                'saldo_pendiente'       => (int)$saldo_a_favor
                            ]);
                        }

                        
                    }else{

                        $last_balance = \DB::table('mt_movements')->where('atm_id',$boleta->atm_id)->orderBy('id','desc')->first();

                        if(isset($last_balance)){
                            $balance= $last_balance->balance -(int)$boleta->monto;
                            $balance_antes= $last_balance->balance;
                        }else{
                            $balance= -(int)$boleta->monto;
                            $balance_antes=0;
                        }

                        $movement_id=\DB::table('mt_movements')->insertGetId([
                            'movement_type_id'          => 2,
                            'destination_operation_id'  => 666,
                            'amount'                    => -(int)$boleta->monto,
                            'debit_credit'              =>  'cr',
                            'created_at'                => Carbon::now(),
                            'updated_at'                => Carbon::now(),
                            'group_id'                  => $group->id,
                            'atm_id'                    => $boleta->atm_id,
                            'balance_antes'             => $balance_antes,
                            'balance'                   => $balance

                        ]);

                        $recibo_id=\DB::table('mt_recibos')->insertGetId([
                            'tipo_recibo_id'    => 3,
                            'mt_movements_id'       => $movement_id,
                            'monto'             =>(int)$boleta->monto,
                            'created_at'        => Carbon::now(), 
                            'updated_at'        => Carbon::now()
                        ]);

                        $recibo_cobranza=\DB::table('mt_recibos_cobranzas')->insert([
                            'recibo_id'             => $recibo_id,
                            'boleta_deposito_id'    => $boleta_deposito_id,
                            'saldo_pendiente'       => (int)$boleta->monto
                        ]);
                    }
                    
                }

                $role = \DB::table('role_users')            
                    ->where('user_id', $boleta->user_id)
                ->first();

                if($role->role_id == 22 || $role->role_id == 25){
                    $ondanet_code = '026';
                }else{
                    $ondanet_code = '1018';
                }

                \DB::table('mt_deposits')->insert([
                    'ondanet_code'              =>  $ondanet_code,
                    'created_at'                =>  Carbon::now(),
                    'updated_at'                =>  Carbon::now(),
                    'recibo_id'                 => $recibo_id,
                    'destination_operation_id'  => 0
                ]);

                $response_balance= $this->updateBalanceBoletas_v2($boleta_deposito_id);
                \Log::warning($response_balance);

                $response_block= $this->checkBlock($boleta_deposito_id);
                \Log::warning($response_block);
                
                $response['error'] = false;
                $response['message'] = 'Registro guardado exitosamente';
            }else{
                $response['error'] = false;
                $response['message'] = 'No se procedio a crear Recibo';
            }    
            \DB::commit();

            if($movimiento != 0){
                $response_migrate= $this->registerCobranzasToOndanet($movimiento);
                \Log::warning($response_migrate);
            }

            return $response;
        }catch(\Exception $e){
            \DB::rollback();
            \Log::error("[Deposito de Boleta]  - {$e->getMessage()}");
            $response = [
                'error' => true,
                'message' => 'Error al consultar saldo de miniterminal',
                'message_user' => ''
            ];

            return $response;
        }
    }

    public function checkBlock($boleta_deposito_id){
        \DB::beginTransaction();
        try{
            $user = \DB::table('boletas_depositos')->where('id',$boleta_deposito_id)->first();
            $atm_id=$user->atm_id;

            $bloqueo=\DB::table('atms')
                ->select('atms.id as atm_id', 'atms.*')
                ->where('atms.id', $atm_id)
                ->whereRaw("block_type_id in (4, 5, 6, 7)")
            ->first();

            if(isset($bloqueo)){
                //PARA INSERTAR EN HISTORIAL BLOQUEOS CHECKUSERLIMITE
                //dd($bloqueo->atm_id);
                \Log::warning('El siguiente atm '.$bloqueo->atm_id.' esta bloqueado por Limite');
                $response= $this->checkUserLimite($atm_id, $boleta_deposito_id);
                \Log::warning($response);

                if(isset($response['atm_id'])){
        
                    \Log::info('CheckUserLimite atms: '. $response['atm_id']);

                    $atms=explode(',', $response['atm_id']);

                    if($response['deuda'] == false){
                        foreach($atms as $atm){
                            $block = \DB::table('atms')
                            ->where('id', $atm)
                            ->first();

                            switch ($block->block_type_id) {
                                case 4:
                                    $estado = 0;
                                    $bloqueado=false;
                                    break;
                                case 5:
                                    $estado = 0;
                                    $bloqueado=false;
                                    break;
                                case 6:
                                    $estado = 2;
                                    $bloqueado=true;
                                    break;
                                case 7:
                                    $estado = 2;
                                    $bloqueado=true;
                                    break;
                            }
                            if(\DB::table('atms')->where('id', $atm)->update(['block_type_id' => $estado])){
                                \DB::table('historial_bloqueos')->insert([
                                    'atm_id' => $atm,
                                    'bloqueado' => $bloqueado,
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s'),
                                    'saldo_pendiente' => $response['saldo_depositado'],
                                    'block_type_id' => $estado,
                                ]);
                            }
                        }
                        \Log::info($response['atm_id']. ' Desbloqueado/s exitosamente');
                    }else{
                        //PARA INSERTAR EN HISTORIAL BLOQUEOS CHECKUSERBALANCE
                        $response= $this->checkUserBalance($atm_id, $boleta_deposito_id);
                        \Log::warning($response);

                        if(isset($response['atm_id'])){
                
                            \Log::info('CheckUserBalance atms: '. $response['atm_id']);

                            $atms=explode(',', $response['atm_id']);

                            if($response['deuda'] == false){
                                foreach($atms as $atm){
                                    $block = \DB::table('atms')
                                    ->where('id', $atm)
                                    ->first();

                                    switch ($block->block_type_id) {
                                        case 4:
                                            $estado = 0;
                                            $bloqueado=false;
                                            break;
                                        case 5:
                                            $estado = 0;
                                            $bloqueado=false;
                                            break;
                                        case 6:
                                            $estado = 2;
                                            $bloqueado=true;
                                            break;
                                        case 7:
                                            $estado = 2;
                                            $bloqueado=true;
                                            break;
                                    }
                                    if(\DB::table('atms')->where('id', $atm)->update(['block_type_id' => $estado])){
                                        \DB::table('historial_bloqueos')->insert([
                                            'atm_id' => $atm,
                                            'bloqueado' => $bloqueado,
                                            'created_at' => date('Y-m-d H:i:s'),
                                            'updated_at' => date('Y-m-d H:i:s'),
                                            'saldo_pendiente' => $response['saldo_depositado'],
                                            'block_type_id' => $estado
                                        ]);
                                    }
                                }
                                \Log::info($response['atm_id']. ' Desbloqueado exitosamente');
                            }
                        }
                    }
                    
                }
            }/*else{
                \Log::warning('El siguiente atm no esta bloqueado por Limite');

                //PARA INSERTAR EN HISTORIAL BLOQUEOS CHECKUSERBALANCE
                $response= $this->checkUserBalance($user_id, $boleta_deposito_id);
                \Log::warning($response);

                if(isset($response['atm_id'])){
        
                    \Log::info('CheckUserBalance atms: '. $response['atm_id']);

                    $atms=explode(',', $response['atm_id']);

                    if($response['deuda'] == false){
                        foreach($atms as $atm){
                            $block = \DB::table('atms')
                                    ->where('id', $atm)
                                    ->first();

                            switch ($block->block_type_id) {
                                case 0:
                                    $estado = 0;
                                    $bloqueado=false;
                                    break;
                                case 1:
                                    $estado = 0;
                                    $bloqueado=false;
                                    break;
                                case 2:
                                    $estado = 2;
                                    $bloqueado=true;
                                    break;
                                case 3:
                                    $estado = 2;
                                    $bloqueado=true;
                                    break;
                            }

                            if(\DB::table('atms')->where('id', $atm)->update(['block_type_id' => $estado])){
                                \DB::table('historial_bloqueos')->insert([
                                    'atm_id' => $atm,
                                    'bloqueado' => $bloqueado,
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s'),
                                    'saldo_pendiente' => $response['saldo_depositado'],
                                    'block_type_id' => $estado,
                                ]);
                            }
                        }
                    }

                    \Log::info($response['atm_id']. ' Desbloqueado exitosamente');
                }
            }*/
            

            $response['error']=false;
            $response['message']='[Deposito de Boleta] Se ha ejecutado correctamente el metodo de Desbloqueo de Puntos de Ventas';
            \DB::commit();
            return $response;
        }
        catch(\Exception $e){
            \DB::rollback();
            \Log::error("Error sending checkBlock  - {$e->getMessage()}");
            $response['error']=true;
            $response['message']='[Deposito de Boleta] Ocurrio un error al intentar ejecutar el metodo de Desbloqueo de Puntos de Ventas';

            return $response;
        }
    }

    public function updateBalanceBoletas($boleta_deposito_id){
        //\DB::beginTransaction();
        try{

            $boleta = \DB::table('boletas_depositos')->where('id', $boleta_deposito_id)->first();

            $usersId = \DB::table('users')
                        ->join('role_users', 'users.id', '=', 'role_users.user_id')
                        ->join('branches', 'users.id', '=', 'branches.user_id')
                        ->where('role_users.role_id', 22)
                        ->where('branches.user_id', $boleta->user_id)
                        ->first();
            
            if(isset($usersId)){
                $branch = \DB::table('branches')->where('user_id',$boleta->user_id)->first();
            }else{
                $group = \DB::table('users_x_groups')->where('user_id',$boleta->user_id)->first();
                $branch = \DB::table('branches')->where('group_id',$group->group_id)->first();
            }

            $atm = \DB::table('atms')->select('atms.id')
                    ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                    ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                    ->where('branches.id', $branch->id)
            ->first();

            \Log::info('[updateBalanceBoletas] Se procede a afectar la boleta #'. $boleta->id . ' del atm '. $atm->id);
            
            $balance=\DB::table('balance_atms')->where('atm_id', $atm->id)->first();

            if(isset($balance)){
                \Log::info('[updateBalanceBoletas] Ultimo monto total depositado del atm_id '. $atm->id . ': '. $balance->total_depositado);

                $depositado= $balance->total_depositado - abs($boleta->monto);
            
                \DB::table('balance_atms')
                    ->where('atm_id', $atm->id)
                    ->update([
                        'total_depositado'  => (int)$depositado
                ]);
            }else{
                \Log::info('[updateBalanceBoletas] Se procede a crear el primer total depositado del atm_id'. $atm->id);

                $depositado= -abs($boleta->monto);

                \DB::table('balance_atms')->insert([
                    'atm_id'            => $atm->id,
                    'total_depositado'  => (int)$depositado
                ]);
            }

            \Log::info('[updateBalanceBoletas] Monto actualizado del total depositado del atm_id '. $atm->id . ': '. $depositado);
            \DB::table('boletas_depositos')
                    ->where('id', $boleta->id)
                    ->update([
                        'date_affected'     => date('Y-m-d H:i:s')
            ]);

            $response['message'] = '[updateBalanceBoletas] El saldo de los atms han sido actualizadas';
            $response['error'] = false;
            
            //\DB::commit();
            return $response;
        }catch (\Exception $e) {
            //\DB::rollback();
            \Log::error("[updateBalanceBoletas] Error  - {$e->getMessage()}");
            \Log::warning($e);
            $response['error'] = true;
            $response['message'] = $e->getMessage();

            \Log::error("[updateBalanceBoletas] Error  - {$response}");
            return $response;
        }
        
    }

    public function updateBalanceBoletas_v2($boleta_deposito_id){
        //\DB::beginTransaction();
        try{

            $boleta = \DB::table('boletas_depositos')->where('id', $boleta_deposito_id)->first();

            \Log::info('[updateBalanceBoletas] Se procede a afectar la boleta #'. $boleta->id . ' del atm '. $boleta->atm_id);
            
            $balance=\DB::table('balance_atms')->where('atm_id', $boleta->atm_id)->first();

            if(isset($balance)){
                \Log::info('[updateBalanceBoletas] Ultimo monto total depositado del atm_id '. $boleta->atm_id . ': '. $balance->total_depositado);

                $depositado= $balance->total_depositado - abs($boleta->monto);
            
                \DB::table('balance_atms')
                    ->where('atm_id', $boleta->atm_id)
                    ->update([
                        'total_depositado'  => (int)$depositado
                ]);
            }else{
                \Log::info('[updateBalanceBoletas] Se procede a crear el primer total depositado del atm_id'. $boleta->atm_id);

                $depositado= -abs($boleta->monto);

                \DB::table('balance_atms')->insert([
                    'atm_id'            => $boleta->atm_id,
                    'total_depositado'  => (int)$depositado
                ]);
            }

            \Log::info('[updateBalanceBoletas] Monto actualizado del total depositado del atm_id '. $boleta->atm_id . ': '. $depositado);
            
            \DB::table('boletas_depositos')
                    ->where('id', $boleta->id)
                    ->update([
                        'date_affected'     => date('Y-m-d H:i:s')
            ]);

            $response['message'] = '[updateBalanceBoletas] El saldo de los atms han sido actualizadas';
            $response['error'] = false;
            
            //\DB::commit();
            return $response;
        }catch (\Exception $e) {
            //\DB::rollback();
            \Log::error("[updateBalanceBoletas] Error  - {$e->getMessage()}");
            \Log::warning($e);
            $response['error'] = true;
            $response['message'] = $e->getMessage();

            \Log::error("[updateBalanceBoletas] Error  - {$response}");
            return $response;
        }
        
    }

    public function registerCobranzasToOndanet($movement_id){
        try
        {
            $cobranza = \DB::table('mt_recibos_cobranzas as mtc')
                ->select('m.id as id_cobranza', 'mr.recibo_nro as recibo', 'bd.fecha as fecha', 'mr.monto as importe','mtc.ventas_cobradas as ventas')
                ->join('mt_recibos as mr', 'mr.id', '=', 'mtc.recibo_id')
                ->join('mt_movements as m', 'm.id', '=', 'mr.mt_movements_id')
                ->join('boletas_depositos as bd', 'bd.id', '=', 'mtc.boleta_deposito_id')
                ->where('m.id', $movement_id)
                ->whereNotNull('mtc.ventas_cobradas')
                ->orderBy('m.id','ASC')
            ->first();
            //dd($cobranza);
            $recibo     =  $cobranza->recibo;
            $fecha      =  date("d-m-Y", strtotime($cobranza->fecha));
            $importe    =  $cobranza->importe;
            $forcobro   =  'EFE';
            $ventas     =  $cobranza->ventas;

            //proceed to export deposits to ondanet
            
            $service = new OndanetServices();

            $result = $service->registerCobranzas($recibo, $fecha, $importe, $forcobro, $ventas);

            if(!$result['error']){
                \Log::info("[ondanet] Exporting miniterminales sales to ondanet ",['result' => $result['error'], 'status' => $result['status'],'Numero de Recibo' => $recibo]);
                
                \DB::table('mt_movements')
                    ->where('id', $cobranza->id_cobranza)
                    ->update([
                        'destination_operation_id' => $result['status'],
                        'response' => json_encode($result),
                        'updated_at' => Carbon::now()
                ]);

            }else{
                \Log::warning("[ondanet] Error - Exporting miniterminales sales to ondanet ",['result' => $result]);
                
                \DB::table('mt_movements')
                    ->where('id', $cobranza->id_cobranza)
                    ->update([
                        'destination_operation_id' => 1,
                        'response' => json_encode($result),
                        'updated_at' => Carbon::now()
                ]);
            }
    
            $user_message =
            [
                'success' => true,
                'message' => 'Miniterminales Cobranzas procesadas correctamente',
            ];
            return $user_message;

        }
        catch(\Exception $e)
        {
            $error_message =
                [
                    'success' => false,
                    'message' => 'Cobranzas exported FAILED',
                ];
            \Log::warning('[ondanet] Error - Exporting miniterminales cobranzas to ondanet', ['result' => $e]);
            return $error_message;
        }
    }

    /**
     * insertar el registro en mt_sales_affected_by_receipts
     */
    public function insert_mt_sales_affected_by_receipts($receipt_id, $sales_id, $sales_amount, $sales_amount_affected, $sales_amount_pendding, $now, $description)
    {
        $mt_sales_affected_by_receipts_insert = [
            'receipt_id' => $receipt_id,
            'sales_id' => $sales_id,
            'sales_amount_before' => $sales_amount,
            'sales_amount_affected' => $sales_amount_affected,
            'sales_amount_after' => $sales_amount_pendding,
            'description' => $description,
            'created_at' => $now
        ];

        \Log::info("\n\nmt_sales_affected_by_receipts(receipt_id = $receipt_id, sales_id = $sales_id):\n\n", [$mt_sales_affected_by_receipts_insert]);

        \DB::table('mt_sales_affected_by_receipts')
            ->insert($mt_sales_affected_by_receipts_insert);

        if ($sales_amount !== $sales_amount_affected) {
            $message = "\n\nEl saldo a pagar y afectado es diferente \n\nMonto del mt_sales: $sales_amount, monto afectado: $sales_amount_affected \n\n(receipt_id = $receipt_id, sales_id = $sales_id)\n\n";
            \Log::info($message);
        }
    }
}