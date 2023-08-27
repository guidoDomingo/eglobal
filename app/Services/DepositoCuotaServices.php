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
use Mail;
use App\Http\Controllers\Controller;

class DepositoCuotaServices extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

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

            Mail::send('mails.deposito_cuota', $data,
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
    public static function checkUserBalance($user_id = null, $boleta_deposito_id){
        try {

            $rule = \DB::table('balance_rules')->where('user_id', $user_id)->first();

            $group = \DB::table('branches')->where('user_id',$user_id)->first();

            if(!empty($rule)){
                $parametro_control = \DB::table('balance_rules')
                ->where([
                    'user_id' => $user_id,
                    'dia' => date('N'),
                    'deleted_at' => null
                ])
                ->where('tipo_control', 3)
                ->first();
            }else{

                $parametro_control = \DB::table('balance_rules')
                ->where([
                    'group_id' => $group->group_id,
                    'dia' => date('N'),
                    'deleted_at' => null
                ])
                ->where('tipo_control', 3)
                ->first();
            }

            \Log::info(json_decode(json_encode($parametro_control), true));

            $atms = \DB::table('atms')
                    ->select('atms.id as id_atm')
                    ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'atms.id')
                    ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                    ->whereIn('atms.owner_id', [16, 21, 25])
                    //->where('atms.owner_id', 44)
                    ->where('branches.user_id', $user_id)
                    ->whereNotNull('branches.user_id')
                    ->whereNull('atms.deleted_at')
                    ->whereNull('points_of_sale.deleted_at')
                    ->pluck('id_atm');

            if(empty($atms)){
                $response['error'] = false;
                $response['deuda'] = false;
                return $response;
            }

            $atm_id = implode(', ', $atms);

            $cuotas=\DB::table('mt_recibo_x_cuota')->where('recibo_id', $boleta_deposito_id)->pluck('numero_cuota', 'numero_cuota');

            $recibo_x_cuota=\DB::table('mt_recibo_x_cuota')->where('recibo_id', $boleta_deposito_id)->first();

            $boleta = \DB::table('mt_recibos')->where('id', $boleta_deposito_id)->first();

            $total_deuda = \DB::table('cuotas')
            ->selectRaw('sum(importe) as monto_cuota')
            ->where('credito_venta_id', $recibo_x_cuota->credito_venta_id)
            ->whereIn('numero_cuota', $cuotas)
            ->first();

            $baseQuery=$total_deuda->monto_cuota - $boleta->monto;

            /*$baseQuery = \DB::table('movements')
            ->selectRaw("SUM(CASE WHEN debit_credit = 'de' and movements.movement_type_id = 4 THEN (movements.amount) else 0 END) -
            abs(SUM(CASE WHEN debit_credit = 'cr' and movements.movement_type_id = 5 THEN (movements.amount) else 0 END)) as total")
            ->join('current_account', 'movements.id', '=', 'current_account.movement_id')
            ->where('current_account.group_id',$group->group_id)
            ->whereRaw("movements.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-16','-16','-17','-21','-23','-26','-27','212', '999')")
            ->first();

            \Log::info(json_decode(json_encode($baseQuery), true));

            $total_deuda = \DB::table('movements')
            ->selectRaw("SUM(CASE WHEN debit_credit = 'de' and movements.movement_type_id = 4 THEN (movements.amount) else 0 END) as total")
            ->join('current_account', 'movements.id', '=', 'current_account.movement_id')
            ->where('current_account.group_id',$group->group_id)
            ->whereRaw("movements.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-16','-16','-17','-21','-23','-26','-27','212', '999')")
            ->first();

            \Log::info(json_decode(json_encode($total_deuda), true));

            $total_depositado = \DB::table('movements')
            ->selectRaw("SUM(CASE WHEN debit_credit = 'cr' and movements.movement_type_id = 5 THEN (movements.amount) else 0 END) as total")
            ->join('current_account', 'movements.id', '=', 'current_account.movement_id')
            ->where('current_account.group_id',$group->group_id)
            ->whereRaw("movements.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-16','-16','-17','-21','-23','-26','-27','212', '999')")
            ->first();

            \Log::info(json_decode(json_encode($total_depositado), true));

            $baseQuery->total= $baseQuery->total-$boleta->monto;
            $total_depositado->total= $total_depositado->total-$boleta->monto;*/

            \Log::info('El saldo con la boleta #'.$boleta_deposito_id.' es '. $baseQuery);

            $control = [
                'message' => 'Consulta exitosa',
                'error' => false,
                'saldo' => $baseQuery,
                'monto_cuota' => $total_deuda->monto_cuota,
                'depositado' => $boleta->monto,
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
                            $porcentaje_pagado = round(abs($control['depositado'])*100/$control['monto_cuota'], 2);
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
            $response['saldo'] = $control['saldo'];

            return $response;

        } catch(\Exception $e){
            \Log::debug('Error al consultar las cuotas de miniterminal - balanceControlMini : '. $e);
            $response = [
                'error' => true,
                'message' => 'Error al consultar cuotas de miniterminal',
                'message_user' => ''
            ];

            return $response;
        }
    }

    public static function checkUserBalance_v2($boleta_id, $atm_id, $group_id){
        try {

            $rule = \DB::table('balance_rules')->where('atm_id', $atm_id)->first();

            if(!empty($rule)){
                $parametro_control = \DB::table('balance_rules')
                ->where([
                    'atm_id' => $atm_id,
                    'dia' => date('N'),
                    'deleted_at' => null
                ])
                ->where('tipo_control', 3)
                ->first();
            }else{

                $parametro_control = \DB::table('balance_rules')
                ->where([
                    'group_id' => $group_id,
                    'dia' => date('N'),
                    'deleted_at' => null
                ])
                ->where('tipo_control', 3)
                ->first();
            }

            \Log::info(json_decode(json_encode($parametro_control), true));

            $atms = \DB::table('atms')
                ->select('atms.id as id_atm')
                ->whereIn('atms.owner_id', [16, 21, 25])
                //->where('atms.owner_id', 44)
                ->where('atms.id', $atm_id)
                ->whereNull('atms.deleted_at')
            ->pluck('id_atm');

            if(empty($atms)){
                $response['error'] = false;
                $response['deuda'] = false;
                return $response;
            }

            $atm_id = implode(', ', $atms);

            $recibo=\DB::table('mt_recibos_pagos_miniterminales')->where('id', $boleta_id)->first();

            $cuotas=\DB::table('mt_recibo_x_cuota')->where('recibo_id', $recibo->recibo_id)->pluck('numero_cuota', 'numero_cuota');

            $recibo_x_cuota=\DB::table('mt_recibo_x_cuota')->where('recibo_id', $recibo->recibo_id)->first();

            $boleta = \DB::table('mt_recibos')->where('id', $recibo->recibo_id)->first();

            $total_deuda = \DB::table('cuotas')
            ->selectRaw('sum(importe) as monto_cuota')
            ->where('credito_venta_id', $recibo_x_cuota->credito_venta_id)
            ->whereIn('numero_cuota', $cuotas)
            ->first();

            $baseQuery=$total_deuda->monto_cuota - $boleta->monto;

            \Log::info('El saldo con la boleta #'.$boleta_id.' es '. $baseQuery);

            $control = [
                'message' => 'Consulta exitosa',
                'error' => false,
                'saldo' => $baseQuery,
                'monto_cuota' => $total_deuda->monto_cuota,
                'depositado' => $boleta->monto,
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
                            $porcentaje_pagado = round(abs($control['depositado'])*100/$control['monto_cuota'], 2);
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
            $response['saldo'] = $control['saldo'];

            return $response;

        } catch(\Exception $e){
            \Log::debug('Error al consultar las cuotas de miniterminal - balanceControlMini : '. $e);
            $response = [
                'error' => true,
                'message' => 'Error al consultar cuotas de miniterminal',
                'message_user' => ''
            ];

            return $response;
        }
    }

    public function insertCuotas($boleta_id){
        \DB::beginTransaction();
        try{
            $boleta = \DB::table('mt_recibos_pagos_miniterminales')->where('id',$boleta_id)->first();            
            $atm = \DB::table('atms')
                    ->select('atms.housing_id')
                    ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                    ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                    ->join('venta_housing', 'atms.housing_id', '=', 'venta_housing.housing_id')
                    ->join('venta', 'venta.id', '=', 'venta_housing.venta_id')
                    ->where('branches.user_id', $boleta->user_id)
                    ->where('venta.tipo_venta', 'cr')
                    ->first();

            $housing = \DB::table('cuotas')
                    ->select('cuotas.*', 'branches.group_id')
                    ->join('venta', 'venta.id', '=', 'cuotas.credito_venta_id')
                    ->join('branches', 'branches.group_id', '=', 'venta.group_id')
                    ->join('venta_housing', 'venta.id', '=', 'venta_housing.venta_id')
                    ->where('branches.user_id', $boleta->user_id)
                    ->where('venta.tipo_venta', 'cr')
                    ->where('cuotas.saldo_cuota', '!=', 0)
                    ->where('venta_housing.housing_id', $atm->housing_id)
                    ->orderBy('cuotas.numero_cuota', 'ASC')
                    ->first();   
            \Log::info('[Deposito de Cuota]',['housing'=>$housing]);            

            $cant_cuota=$boleta->monto/$housing->importe;
            \Log::info('Cantidad de cuotas a cobrar: '.$cant_cuota.' para la boleta #'.$boleta->id);

            if(is_null($housing->cod_venta)){
                \DB::rollback();
                \Log::error("[Deposito de cuotas]  - Ha ocurrido un error");
                $response = [
                    'error' => true,
                    'message' => 'Error al consultar cuota de miniterminal',
                    'message_user' => ''
                ];

                return $response;
            }
            
            $cuotas = \DB::table('cuotas')
            ->select('cuotas.*')
            ->join('venta', 'venta.id', '=', 'cuotas.credito_venta_id')
            ->join('branches', 'branches.group_id', '=', 'venta.group_id')
            ->join('venta_housing', 'venta.id', '=', 'venta_housing.venta_id')
            ->where('branches.user_id', $boleta->user_id)
            ->where('venta.tipo_venta', 'cr')
            ->where('cuotas.saldo_cuota', '!=' ,0)
            ->where('venta_housing.housing_id', $atm->housing_id)     
            ->orderBy('cuotas.numero_cuota', 'ASC')
            ->orderBy('cuotas.fecha_vencimiento', 'ASC')
            ->take($cant_cuota)
            ->pluck('cuotas.numero_cuota');
                        
            $cuotas_pendientes = implode(';', $cuotas);
            \Log::info('[Deposito de Cuota] Cuotas a cobrar', ['cuotas' => $cuotas_pendientes, 'housing_id' => $housing]);    
            
            $movement_id=\DB::table('movements')->insertGetId([
                'movement_type_id'          => 5,
                'destination_operation_id'  => 0,
                'amount'                    => -(int)$boleta->monto,
                'debit_credit'              =>  'cr',
                'created_at'                => Carbon::now(),
                'updated_at'                => Carbon::now()        

            ]);

            $last_balance = \DB::table('current_account')->where('group_id',$housing->group_id)->orderBy('id','desc')->first();
            if(isset($last_balance)){
                $balance= $last_balance->balance -(int)$boleta->monto;
            }else{
                $balance= -(int)$boleta->monto;
            }

            \DB::table('current_account')->insert([
                'movement_id'               => $movement_id,    
                'group_id'                  => $housing->group_id,
                'amount'                    => -(int)$boleta->monto,
                'balance'                   => $balance, 
            ]);

            $recibo_id=\DB::table('mt_recibos')->insertGetId([
                'movements_id'               => $movement_id,    
                'monto'                     => (int)$boleta->monto,
                'created_at'                => Carbon::now(),
                'updated_at'                => Carbon::now(),
                'tipo_recibo_id'            => 1
            ]);

            \DB::table('mt_recibos_pagos_miniterminales')
            ->where('id', $boleta->id)
            ->update([
                'recibo_id'     => $recibo_id,
                'updated_at'    => Carbon::now(),
                'estado'        => true,
                'updated_by'    => $this->user->id
            ]);
            
            foreach($cuotas as $cuota){

                \Log::info('[Deposito de Cuota] En proceso de insertar la cuota #'. $cuota . ' para el codigo de venta '.$housing->cod_venta);                
                $consulta_cuota=\DB::table('cuotas')->where('numero_cuota',$cuota)->where('cod_venta',$housing->cod_venta)->first();
                           
                $movement_id=\DB::table('movements')->insertGetId([
                    'movement_type_id'          => 4,
                    'destination_operation_id'  => $housing->cod_venta,
                    'amount'                    => $housing->importe,
                    'debit_credit'              =>  'de',
                    'created_at'                => Carbon::now(),
                    'updated_at'                => Carbon::now()        
                ]);
        
                    $last_balance = \DB::table('current_account')->where('group_id',$housing->group_id)->orderBy('id','desc')->first();
                    if(isset($last_balance)){
                        $balance= $last_balance->balance + $housing->importe;
                    }else{
                        $balance= $housing->importe;
                    }
        
                    \DB::table('current_account')->insert([
                        'movement_id'               => $movement_id,    
                        'group_id'                  => $housing->group_id,
                        'amount'                    => (int)$housing->importe,
                        'balance'                   => $balance, 
                    ]);

                    \DB::table('mt_recibo_x_cuota')->insert([
                        'recibo_id'         => $recibo_id,    
                        'credito_venta_id'  => $consulta_cuota->credito_venta_id,
                        'numero_cuota'  => $cuota
                    ]);
                    
                    \DB::table('cuotas')
                    ->where('numero_cuota', $cuota)
                    ->where('cod_venta', $housing->cod_venta)
                    ->update([
                        'movements_id'  => $movement_id,
                        'saldo_cuota'   => 0
                    ]);
                    \Log::info('Se insertaron los siguientes movimientos de la cuota con el movement_id: '.$movement_id);
                
            }
            \Log::info('Se actualizaron las cuotas correspondientes');                                                                      

            /*$last_balance = \DB::table('current_account')->where('group_id',$housing->group_id)->orderBy('id','desc')->first();
            if(isset($last_balance)){
                $balance= $last_balance->balance -(int)$boleta->monto;
            }else{
                $balance= -(int)$boleta->monto;
            }

            \DB::table('current_account')->insert([
                'movement_id'               => $movement_id,    
                'group_id'                  => $housing->group_id,
                'amount'                    => -(int)$boleta->monto,
                'balance'                   => $balance, 
            ]);                                   

            \DB::table('mt_recibos_pagos_miniterminales')
            ->where('id', $boleta->id)
            ->update([
               'estado'     => true,
               'recibo_id'  => $recibo_id,
               'updated_at'    => Carbon::now(),
               'updated_by'    => $this->user->id,
            ]);*/ 

            //se reemplaza con mt_deposits
            \DB::table('mt_deposits')->insert([
                'ondanet_code'  =>  '026',
                'created_at'  =>  Carbon::now(),
                'updated_at'  =>  Carbon::now(),
                'recibo_id' => $recibo_id,
                'destination_operation_id' => 0

            ]);

            \Log::info('[Deposito de Cuota] El movimiento de cobro de cuota se realizo correctamente');

            $response_block= $this->checkBlock($boleta->id);
            \Log::warning($response_block);

            $response['error'] = false;
            $response['message'] = 'Registro guardado exitosamente';
            \DB::commit();
            return $response;
        }catch(\Exception $e){
            \DB::rollback();
            \Log::error("[Deposito de Cuota]  - {$e->getMessage()}");
            $response = [
                'error' => true,
                'message' => 'Error al consultar cuota de miniterminal',
                'message_user' => ''
            ];

            return $response;
        }
    }

    public function insertCuotas_v2($boleta_id){
        \DB::beginTransaction();
        try{
            $boleta = \DB::table('mt_recibos_pagos_miniterminales')->where('id',$boleta_id)->first();    
            

            $group = \DB::table('business_groups as bg')
                ->select('bg.id')
                ->join('branches', 'bg.id', '=', 'branches.group_id')
                ->join('points_of_sale', 'branches.id', '=', 'points_of_sale.branch_id')
                ->join('atms', 'atms.id', '=', 'points_of_sale.atm_id')
                ->where('atms.id', $boleta->atm_id)
            ->first();
            
            $atm = \DB::table('atms')
                ->select('atms.housing_id')
                ->where('atms.id', $boleta->atm_id)
            ->first();
            
            $housing = \DB::table('cuotas')
                ->select('cuotas.*', 'venta.group_id')
                ->join('venta', 'venta.id', '=', 'cuotas.credito_venta_id')
                ->join('venta_housing', 'venta.id', '=', 'venta_housing.venta_id')
                ->where('venta.group_id', $group->id)
                ->where('venta.tipo_venta', 'cr')
                ->where('cuotas.saldo_cuota', '!=', 0)
                ->where('venta_housing.housing_id', $atm->housing_id)
                ->orderBy('cuotas.numero_cuota', 'ASC')
            ->first(); 
            
            \Log::info('[Deposito de Cuota]',['housing'=>$housing]);            

            $cant_cuota=$boleta->monto/$housing->importe;
            \Log::info('Cantidad de cuotas a cobrar: '.$cant_cuota.' para la boleta #'.$boleta->id);

            if(is_null($housing->cod_venta)){
                \DB::rollback();
                \Log::error("[Deposito de cuotas]  - Ha ocurrido un error");
                $response = [
                    'error' => true,
                    'message' => 'Error al consultar cuota de miniterminal',
                    'message_user' => ''
                ];

                return $response;
            }
            
            $cuotas = \DB::table('cuotas')
                ->select('cuotas.*')
                ->join('venta', 'venta.id', '=', 'cuotas.credito_venta_id')
                ->join('venta_housing', 'venta.id', '=', 'venta_housing.venta_id')
                ->where('venta.group_id', $group->id)
                ->where('venta.tipo_venta', 'cr')
                ->where('cuotas.saldo_cuota', '!=' ,0)
                ->where('venta_housing.housing_id', $atm->housing_id)     
                ->orderBy('cuotas.numero_cuota', 'ASC')
                ->orderBy('cuotas.fecha_vencimiento', 'ASC')
                ->take($cant_cuota)
            ->pluck('cuotas.numero_cuota');
                        
            $cuotas_pendientes = implode(';', $cuotas);
            \Log::info('[Deposito de Cuota] Cuotas a cobrar', ['cuotas' => $cuotas_pendientes, 'housing_id' => $housing]);    
            
            $last_balance = \DB::table('mt_movements')->where('atm_id',$boleta->atm_id)->orderBy('id','desc')->first();
              
            if(isset($last_balance)){
                $balance= $last_balance->balance -(int)$boleta->monto;
                $balance_antes= $last_balance->balance;
            }else{
                $balance= -(int)$boleta->monto;
                $balance_antes=0;
            }
  
            $movement_id=\DB::table('mt_movements')->insertGetId([
                'movement_type_id'          => 5,
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
                'mt_movements_id'           => $movement_id,    
                'monto'                     => (int)$boleta->monto,
                'created_at'                => Carbon::now(),
                'updated_at'                => Carbon::now(),
                'tipo_recibo_id'            => 1
            ]);

            \DB::table('mt_recibos_pagos_miniterminales')
            ->where('id', $boleta->id)
            ->update([
                'recibo_id'     => $recibo_id,
                'updated_at'    => Carbon::now(),
                'estado'        => true,
                'updated_by'    => $this->user->id
            ]);

            foreach($cuotas as $cuota){

                \Log::info('[Deposito de Cuota] En proceso de insertar la cuota #'. $cuota . ' para el codigo de venta '.$housing->cod_venta);                
                $consulta_cuota=\DB::table('cuotas')->where('numero_cuota',$cuota)->where('cod_venta',$housing->cod_venta)->first();
                //dd($consulta_cuota);
                $last_balance = \DB::table('mt_movements')->where('atm_id',$boleta->atm_id)->orderBy('id','desc')->first();

                if(isset($last_balance)){
                    $balance= $last_balance->balance + $housing->importe;
                    $balance_antes= $last_balance->balance;
                }else{
                    $balance= $housing->importe;
                    $balance_antes=0;
                }

                $movement_id=\DB::table('mt_movements')->insertGetId([
                    'movement_type_id'          => 4,
                    'destination_operation_id'  => $housing->cod_venta,
                    'amount'                    => $housing->importe,
                    'debit_credit'              =>  'de',
                    'created_at'                => Carbon::now(),
                    'updated_at'                => Carbon::now(),
                    'group_id'                  => $group->id,
                    'atm_id'                    => $boleta->atm_id,
                    'balance_antes'             => $balance_antes,
                    'balance'                   => $balance      
                ]);

                \DB::table('mt_recibo_x_cuota')->insert([
                    'recibo_id'         => $recibo_id,    
                    'credito_venta_id'  => $consulta_cuota->credito_venta_id,
                    'numero_cuota'  => $cuota
                ]);
                    
                \DB::table('cuotas')
                ->where('numero_cuota', $cuota)
                ->where('cod_venta', $housing->cod_venta)
                ->update([
                    'movements_id'  => $movement_id,
                    'saldo_cuota'   => 0
                ]);
                \Log::info('Se insertaron los siguientes movimientos de la cuota con el movement_id: '.$movement_id);
                
            }
            \Log::info('Se actualizaron las cuotas correspondientes');                                                                      

            \DB::table('mt_deposits')->insert([
                'ondanet_code'  =>  '026',
                'created_at'  =>  Carbon::now(),
                'updated_at'  =>  Carbon::now(),
                'recibo_id' => $recibo_id,
                'destination_operation_id' => 0

            ]);

            \Log::info('[Deposito de Cuota] El movimiento de cobro de cuota se realizo correctamente');

            $response_block= $this->checkBlock_V2($boleta->id, $boleta->atm_id, $group->id);
            \Log::warning($response_block);

            $response['error'] = false;
            $response['message'] = 'Registro guardado exitosamente';
            \DB::commit();
            return $response;
        }catch(\Exception $e){
            \DB::rollback();
            \Log::error("[Deposito de Cuota]  - {$e->getMessage()}");
            $response = [
                'error' => true,
                'message' => 'Error al consultar cuota de miniterminal',
                'message_user' => ''
            ];

            return $response;
        }
    }

    public function checkBlock($boleta_id){
        \DB::beginTransaction();
        try{
            $recibo_pago_mini_data = \DB::table('mt_recibos_pagos_miniterminales')->where('id',$boleta_id)->first();
            $user_id    = $recibo_pago_mini_data->user_id;
            $recibo_id  = $recibo_pago_mini_data->recibo_id;
            //PARA INSERTAR EN HISTORIAL BLOQUEOS CHECKUSERBALANCE
            $response= $this->checkUserBalance($user_id, $recibo_id);
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
                                break;
                            case 1:
                                $estado = 1;
                                break;
                            case 2:
                                $estado = 0;
                                break;
                            case 3:
                                $estado = 1;
                                break;
                            case 4:
                                $estado = 4;
                                break;
                            case 5:
                                $estado = 5;
                                break;
                            case 6:
                                $estado = 4;
                                break;
                            case 7:
                                $estado = 5;
                                break;
                        }

                        if(\DB::table('atms')->where('id', $atm)->update(['block_type_id' => $estado]))
                        \DB::table('historial_bloqueos')->insert([
                            'atm_id' => $atm,
                            'bloqueado' => false,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                            'saldo_pendiente' => $response['saldo'],
                            'block_type_id' => $estado
                        ]);
                    }
                }

                \Log::info($response['atm_id']. ' Desbloqueado exitosamente');
            }

            $response['error']=false;
            $response['message']='[Deposito de Cuota] Se ha ejecutado correctamente el metodo de Desbloqueo de Puntos de Ventas';
            \DB::commit();
            return $response;
        }
        catch(\Exception $e){
            \DB::rollback();
            \Log::error("Error sending checkBlock  - {$e->getMessage()}");
            $response['error']=true;
            $response['message']='[Deposito de Cuota] Ocurrio un error al intentar ejecutar el metodo de Desbloqueo de Puntos de Ventas';

            return $response;
        }
    }

    public function checkBlock_V2($boleta_id, $atm_id, $group_id){
        \DB::beginTransaction();
        try{
            
            $response= $this->checkUserBalance_v2($boleta_id, $atm_id, $group_id);
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
                                break;
                            case 1:
                                $estado = 1;
                                break;
                            case 2:
                                $estado = 0;
                                break;
                            case 3:
                                $estado = 1;
                                break;
                            case 4:
                                $estado = 4;
                                break;
                            case 5:
                                $estado = 5;
                                break;
                            case 6:
                                $estado = 4;
                                break;
                            case 7:
                                $estado = 5;
                                break;
                        }

                        if(\DB::table('atms')->where('id', $atm)->update(['block_type_id' => $estado]))
                        \DB::table('historial_bloqueos')->insert([
                            'atm_id' => $atm,
                            'bloqueado' => false,
                            'created_at' => date('Y-m-d H:i:s'),
                            'updated_at' => date('Y-m-d H:i:s'),
                            'saldo_pendiente' => $response['saldo'],
                            'block_type_id' => $estado
                        ]);
                    }
                }

                \Log::info($response['atm_id']. ' Desbloqueado exitosamente');
            }

            $response['error']=false;
            $response['message']='[Deposito de Cuota] Se ha ejecutado correctamente el metodo de Desbloqueo de Puntos de Ventas';
            \DB::commit();
            return $response;
        }
        catch(\Exception $e){
            \DB::rollback();
            \Log::error("Error sending checkBlock  - {$e->getMessage()}");
            $response['error']=true;
            $response['message']='[Deposito de Cuota] Ocurrio un error al intentar ejecutar el metodo de Desbloqueo de Puntos de Ventas';

            return $response;
        }
    }
}