<?php

/**
 * Created by PhpStorm.
 * User: thavo
 * Date: 15/02/17
 * Time: 11:12 AM
 */

namespace App\Services;

use Carbon\Carbon;
use App\Models\Atm;
use App\Models\WebService;
use App\Facades\HttpClient;
use App\Models\Notifications;
use App\Models\alerts_tickets;
use Illuminate\Support\Facades\Mail;
use SebastianBergmann\Environment\Console;

class DashServices
{
    public function getAtmsStatus()
    {
        try {
            $atms = Atm::all();
            $offline = 0;
            foreach ($atms as $atm) {
                $now  = Carbon::now();
                $end =  Carbon::parse($atm->last_request_at);
                $elasep = $now->diffInMinutes($end);
                if ($elasep >= 20 || $atm->atm_status <> 0) {
                    $offline++;
                }
            }
            $active_atm_count = $atms->count() - $offline;
            $response['error'] = false;
            $response['message'] = "<small>" . $active_atm_count . " Online</small> / <small>" . $offline . " Offline</small><br/>";
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de datos Dash Atms: " . $e->getMessage());
            return -213;
        }

        return $response;
    }

    public function getMaintenanceKeys()
    {
        try {
            $keys = \DB::table('atm_arqueos_hash')
                ->select('atm_arqueos_hash.id', 'atm_arqueos_hash.hash', 'atms.code', 'points_of_sale.description as name', 'users.username')
                ->join('atms', 'atms.id', '=', 'atm_arqueos_hash.atm_id')
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'atm_arqueos_hash.atm_id')
                ->join('users', 'users.id', '=', 'atm_arqueos_hash.user_id')
                ->orderBy('atm_arqueos_hash.id', 'desc')
                ->where('status', 0)
                ->where('atm_arqueos_hash.created_at', '>=', Carbon::now()->subMinutes(5))
                ->get();
                $content = '<ul id="keys_list" style="width:330px" class="list-group"> ';
                foreach ($keys as $key) {
                    $content .= ' <li style="cursor:pointer"  data-id="' . $key->id . '" class="list-group-item"><b>' . $key->code . ' ' . $key->name . '</b><br> <small>Solicitado por : ' . $key->username . '</small>  | <b>Clave: <span id="pass_' . $key->id . '">******<span></b>  <i id="eye_' . $key->id . '" style="margin:0px 15px;" class=" keys fa fa-eye" ></i> <i id="forb_' . $key->id . '" style="margin:0px 15px; display:none" class=" keys fa fa-warning" ></i> </li> ';
                }
            $content .= ' </ul>';
            $response['error'] = false;
            $response['message'] = $content;
        } catch (\Exception $e) {
            $error_detail = [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];
            \Log::info('Error', ['result' => $error_detail]);
            \Log::error("Error en la consulta de datos Dash Keys: " . $e->getMessage());
            return -213;
        }
        return $response;
    }

    public function atmsView($idAtm)
    {

        try {
            $id = (empty($idAtm)) ? 0 : $idAtm;

            $user = \Sentinel::getUser();

            $userAtms = \DB::table('atms_per_users')
                ->select('atms.id', 'atms.name')
                ->join('atms', 'atms_per_users.atm_id', '=', 'atms.id')
                ->where('atms_per_users.user_id', $user->id)
                ->where('atms_per_users.atm_id', '<>', $id)
                ->get();

            \Log::info('userAtms',['result',$userAtms]);

            $userFirstSelect = \DB::table('atms_per_users')
                ->select('atms.id', 'atms.name')
                ->join('atms', 'atms_per_users.atm_id', '=', 'atms.id')
                ->where('atms_per_users.user_id', $user->id)
                ->where(function ($query) use ($id ) {
                    if (!empty($id ) &&  $id  <> 0) {
                        $query->where('atms_per_users.atm_id', '=', $id);
                    }
                })
                ->first();

                \Log::info('userFirstSelect',['result'=>$userFirstSelect]);

            if (!empty($userAtms) || is_null($userAtms)) { 

                $minis = \DB::table('mini_cashout_devolution_details')
                ->select('mini_cashout_devolution_details.id', 'marcas.descripcion as marca', 'mini_cashout_devolution_details.transaction_id', 'servicios_x_marca.descripcion', 'mini_cashout_devolution_details.parameters', 'mini_cashout_devolution_type.description as tipo', 'mini_cashout_devolution_details.hash_table','mini_cashout_devolution_details.atm_id')
                ->join('mini_cashout_devolution_type', 'mini_cashout_devolution_type.id', '=', 'mini_cashout_devolution_details.type_id')
                ->join('servicios_x_marca', function ($join) {
                    $join->on('mini_cashout_devolution_details.services_id', '=', 'servicios_x_marca.service_id');
                    $join->on('mini_cashout_devolution_details.services_source_id', '=', 'servicios_x_marca.service_source_id');
                })
                ->join('marcas', 'marcas.id', '=', 'servicios_x_marca.marca_id')
                ->join('atms','mini_cashout_devolution_details.atm_id','=','atms.id')
                ->join('atms_per_users', 'atms_per_users.atm_id', '=', 'atms.id')
                ->where('mini_cashout_devolution_details.status', 'pendiente')
                ->where(function ($query) use ($id ) {
                    if (!empty($id ) &&  $id  <> 0) {
                        $query->where('mini_cashout_devolution_details.atm_id', '=', $id);
                    }
                })
                ->where('atms_per_users.user_id', $user->id)
                ->get();
                
                 $content = '<select class="form-control select2" id="atms" onchange="obtenerAtm()" style="width:330px"> ';
                 $content.= ($id == 0) ? '<option value="">Seleccione una sucursal</option>' : '<option value="' . $userFirstSelect->id . '">' . $userFirstSelect->name . '</option>';
                    

                foreach ($userAtms as $userAtm) {
                    $content  .= '<option value="' . $userAtm->id . '">' . $userAtm->name . '</option>';
                }
                $content .= '</select>';
                $content .= '<ul id="keys_list" style="width:330px" class="list-group"> ';
            } else {
                $content = '<ul id="keys_list" style="width:330px" class="list-group"> ';
            }

            foreach ($minis as $mini) {

                //Todo: al final esperar tener todas las respuestas de cada servicio para ver como le llamamos al  monto 
                $data1 =  json_decode($mini->parameters);
                $data = json_decode($data1);

                $codigo = ($mini->tipo == 'CashOut') ? $mini->hash_table : $mini->transaction_id;

                    if ($mini->tipo == 'Vuelto' || $mini->tipo == 'Devolucion') $amount = $data->valor_entrega;
                    if ($mini->marca == 'Claro Billetera') $amount = $data->monto;
                    if ($mini->marca == 'Telebingo' || $mini->marca == 'Billetera Personal' || $mini->marca == 'Tigo Money' || $mini->marca == 'Apostala') $amount = $data->amount;
                    if ($mini->marca == 'Apostala') $amount = $data->subtraction;
                $json = json_encode($mini);

                $content .= ' <li style="cursor:pointer" data-id="' . $mini->id . '" class="list-group-item"><b><a class="label label-warning" style="color:black !important;">Código: ' . $codigo . '</a> ' . $mini->marca . ' ' . $mini->descripcion . '  ' . number_format($amount) . ' ' . $mini->tipo . '</b> <br><br> <div style="display: flex; justify-content: space-between;"><button type="button" class="btn btn-success" onclick=\'modalView(' . $json . ')\' data-target="#modal_detalle_mini">Procesar</button><button type="button" class="btn btn-danger" onclick=\'modalViewCancel(' .$mini->id. ')\' data-target="#modal_detalle_mini"><i class="fa fa-remove"></i></button></div> </li> ';
            }
            $content .= ' </ul>';

           \Log::info('valor',['conten'=>$content]);
            $response['error'] = false;
            $response['message'] = $content;
        } catch (\Exception $e) {
            $error_detail = [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];
            \Log::info('Error', ['result' => $error_detail]);
            \Log::error("Error en la consulta de datos Dash Keys: " . $e->getMessage());
            return -213;
        }
        return $response;
    }

    public function showKeys($key_id)
    {
        try {
            $keys = \DB::table('atm_arqueos_hash')
                ->where('id', $key_id)
                ->get();
            //Actualizar usuario autorizante
            $user_id = \Sentinel::getUser()->id;

            $hash = '';
            foreach ($keys as $key) {
                $hash = $key->hash;
                if ($user_id == $key->user_id) {
                    //usuario solicitante es igual a usuario autorizador, no permitir visualizar clave
                    \Log::error("usuario solicitante es igual a usuario autorizador - No es posible activar clave");
                    return -213;
                }
            }

            $auth = \DB::table('atm_arqueos_hash')
                ->where('id', $key_id)
                ->update(['status' => 1, 'cms_user_id' => $user_id]);

            $response['error'] = false;
            $response['message'] = $hash;
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de datos Dash Keys: " . $e->getMessage());
            return -213;
        }

        return $response;
    }

    public function getServicesStatus()
    {
        try {
            $services = WebService::all();
            $offline = 0;
            foreach ($services as $service) {
                //status > 0 Son servicios desconectados por algun error o por desuso
                if ($service->status > 0) {
                    $offline++;
                }
            }
            $active_services  = $services->count() - $offline;
            $response['error'] = false;
            $response['message'] = "<small> " . $active_services . " Online</small> / <small> " . $offline . " Offline</small><br/>";
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de datos Dash Services: " . $e->getMessage());
            return -213;
        }

        return $response;
    }

    public function getBalanceStatus()
    {
        try {
            $atms = \DB::table('atms')
                ->select('atms.id', 'atms.code as atm_code', 'points_of_sale.description as pdv', 'owners.name as red', 'atms.name as atm_name')
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'atms.id')
                ->join('owners', 'owners.id', '=', 'atms.owner_id')
                ->whereIn('atms.id', function ($query) {
                    $query->select('atms_parts.atm_id')
                        ->from('atms_parts')
                        ->where('atms_parts.cantidad_alarma', '>', '0')
                        ->whereRaw('atms_parts.cantidad_alarma >= atms_parts.cantidad_actual');
                })
                ->where('atms.deleted_at', null)
                ->where('atms.type', 'at')
                ->where('atms.owner_id', '!=', 18)
                ->get();

            $balances = \DB::table('atms_parts')
                ->where('cantidad_minima', '>', 0)
                ->get();

            $moderate = 0;
            $critic =  0;
            $estado = [];
            foreach ($atms as $atm) {
                $balances = \DB::table('atms_parts')
                    ->select('*', \DB::raw('cantidad_actual-cantidad_minima as diferencia'))
                    ->where('atms_parts.atm_id', '=', $atm->id)
                    ->where('cantidad_alarma', '>', '0')
                    ->whereRaw('cantidad_alarma >= cantidad_actual')
                    ->get();
                $estado[$atm->id]['critic'] = 0;
                $estado[$atm->id]['moderate'] = 0;

                foreach ($balances as $balance) {
                    if ($balance->cantidad_actual <= $balance->cantidad_minima) {
                        $estado[$atm->id]['critic'] += 1;
                    }

                    if ($balance->cantidad_actual > $balance->cantidad_minima && $balance->cantidad_actual <= $balance->cantidad_alarma) {
                        $estado[$atm->id]['moderate'] += 1;
                    }
                }

                if ($estado[$atm->id]['critic'] > 0) {
                    $critic++;
                }

                if ($estado[$atm->id]['moderate'] > 0) {
                    $moderate++;
                }
            }

            $response['error'] = false;
            $response['message'] = "<small><span><i class=\"fa fa-circle text-yellow\"></i> " . $moderate . " Moderado</span> / <span><i class=\"fa fa-circle text-red\"></i> " . $critic . " Crítico</span></small>";
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de datos Dash Balances: " . $e->getMessage());
            return -213;
        }

        return $response;
    }

    public function getWarningStatus()
    {
        try {

            //select count(*) as aggregate from "notifications" where "processed" = $1 and "asigned_to" = $2

            $user = \Sentinel::getUser();

            if ($user->hasAccess('reporting.all')) {

                /*$notifications = Notifications::where('processed', false)
                    ->count();*/

                $notifications = \DB::table('notifications')
                    ->select(
                        'id'
                    )
                    ->where('processed', false)
                    ->get();

            } else {
                /*$notifications = Notifications::where('processed', false)
                    ->where('asigned_to', $user->id)
                    ->count();*/

                $notifications = \DB::table('notifications')
                    ->select(
                        'id'
                    )
                    ->where('processed', false)
                    ->where('asigned_to', $user->id)
                    ->get();
                    
            }

            $notifications = count($notifications);

            if ($notifications == 0) {
                $result = 'Sin exepciones';
            } else if ($notifications == 1) {
                $result = $notifications . ' Excepción';
            } else {
                $result = $notifications . ' Excepciones';
            }
            $response['error'] = false;
            $response['message'] = "<small>" . $result . "</small>";
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de datos Dash Warnings: " . $e->getMessage());
            return -213;
        }

        return $response;
    }

    public function getConciliationsStatus()
    {
        try {
            /*$incomes = \DB::table('incomes')
                ->whereIn('destination_operation_id', [0, 1, 5, 212])
                ->count();*/

            $incomes = \DB::table('incomes as i')
                ->select(
                    'i.id'
                )
                //->whereIn('destination_operation_id', [0, 1, 5, 212])
                ->whereRaw("i.destination_operation_id = any(array['0', '1', '5', '212'])")
                ->get();

            $total_pending_incomes = count($incomes);

            /*$invoices = \DB::table('invoices')
                ->whereIn('status_code', [0, 1, 5, 212])
                ->count();*/

            $invoices = \DB::table('invoices as i')
                ->select(
                    'i.id'
                )
                //->whereIn('i.status_code', [0, 1, 5, 212])
                ->whereRaw("i.status_code = any(array[0, 1, 5, 212])") 
                ->get();
                
            $total_pending_invoices = count($invoices);

            $fechaEnd = date('Y-m-d H:i:s');
            $fecha = date('Y-m-d', strtotime($fechaEnd . "- 30 days"));
            $fechaInit = $fecha . ' 00:00:00';

            /*$generics =  \DB::table('invoices_generic')
                ->whereIn('status_code', ['-28', '212', '1'])
                //->whereBetween('created_at', [$fechaInit,$fechaEnd])
                ->count();*/

            $generics =  \DB::table('invoices_generic as ig')
                ->select(
                    'ig.id'
                )
                //->whereIn('ig.status_code', ['-28', '212', '1'])
                //->whereBetween('created_at', [$fechaInit,$fechaEnd])
                ->whereRaw("ig.status_code = any(array['-28', '212', '1'])")
                ->get();

            $generics = count($generics);   

            $response = [];
            $response['error'] = false;
            $response['message'] = "<small>" . $total_pending_incomes . " Ingresos</small> / <small>" . $total_pending_invoices . " Facturas</small> / <small>" . $generics . " A generar</small>";
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de datos Dash Conciliations: " . $e->getMessage());
            return -213;
        }

        return $response;
    }

    public function getNotRollback()
    {
        try {
            /*$rollbacks = \DB::select(
                "
                SELECT count(*)
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
            
            $total_rollback_not = $rollbacks[0]->count;
            $rollbacksFail = \DB::select(
                "
                        select count(*) from rollback_credito_pdv_sync
                        where  message not similar to 'Operacion realizada exitosamente' and message not similar to 'No se ha encontrado la transaccion a reversar!' and message not similar to 'No es posible realizar la reversion de la transaccion!' and message not similar to 'Error al procesar la solicitud: Operacion ya reversada' and status = 1 
                                    and date(created_at) BETWEEN date(now() - interval '120 hour') and date(now());
                                    "
            );

            $total_rollback_fail = $rollbacksFail[0]->count;*/

            $rollbacks = \DB::select("
                select 
                    count(t.id)
                from transactions t
                left join atms a on t.atm_id = a.id
                left join points_of_sale pos on t.atm_id = pos.atm_id
                left join tdp_billetaje_sync_transactions tbst on t.id = tbst.transaction_id
                left join rollback_credito_pdv_sync rcps on t.id = rcps.backend_transaction_id
                where (t.status  like '%canceled%' or t.status like '%error%' )
                and (t.status_description like '%Error en el proceso de d?bito.%' 
                or t.status_description like '%Cancelado por error en carga; codigo de error:%'or t.status_description like '%Inicializando transacción%')
                and tbst.id is null and rcps.id is null
                and pos.owner_id = 18
                and date(t.created_at) between date(now() - interval '120 hour') and date(now())
            ");

            $total_rollback_not = $rollbacks[0]->count;

            $rollbacksFail = \DB::select("
                select 
                    count(id) 
                from rollback_credito_pdv_sync
                where message not similar to 'Operacion realizada exitosamente' 
                and message not similar to 'No se ha encontrado la transaccion a reversar!' 
                and message not similar to 'No es posible realizar la reversion de la transaccion!' 
                and message not similar to 'Error al procesar la solicitud: Operacion ya reversada' 
                and status = 1 
                and date(created_at) between date(now() - interval '120 hour') 
                and date(now());
            ");

            $total_rollback_fail = $rollbacksFail[0]->count;

            $response['error'] = false;
            $response['message'] = "<small>" . $total_rollback_not . " Sin reversa</small> / <small>" . $total_rollback_fail . "  Errores en Reversa</small>";
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de datos Dash sin reversa: " . $e->getMessage());
            return -213;
        }

        return $response;
    }
    public function montoCero()
    {

        try {
            // $fechaEnd = date('Y-m-d H:i:s');

            // $fecha = date('Y-m-d',strtotime($fechaEnd."- 30 days"));
            // $fechaInit = $fecha.' 00:00:00';


            // $monto = \DB::table('transactions as t')
            // // ->join('atms as a','a.id','=','t.atm_id')
            // // ->join('services as s','s.id','=','t.service_id')
            // ->where('status','success')
            // ->where('amount',0)
            // ->whereIn('transaction_type',[1,7])
            // ->whereRaw('created_at', [$fechaInit,$fechaEnd])
            // ->count();
            
            /*$monto  = \DB::select(
                "
                    select count(*) from transactions t
                    inner join atms a ON t.atm_id = a.id
                    join public.servicios_x_marca sxm
                    on t.service_source_id = (case when sxm.service_source_id = 9 then 0 else sxm.service_source_id end) and t.service_id = sxm.service_id
                    where t.amount = 0 and t.status = 'success' and t.transaction_type in (1,7) and t.created_at between date(now() - interval '30 day') and date(now())
                    and sxm.deleted_at isnull 
                "
            );*/

            $monto = \DB::select("
                select 
                    count(t.id) 
                from transactions as t
                join atms a on a.id = t.atm_id
                join public.servicios_x_marca sxm on t.service_source_id = sxm.service_source_id and t.service_id = sxm.service_id
                where t.amount = 0 
                and t.status = 'success' 
                and t.transaction_type in (1, 7) 
                and t.created_at between date(now() - interval '30 day') 
                and date(now())
                and sxm.deleted_at is null 
            ");

            $response['error'] = false;
            $response['message'] = "<small>" . $monto[0]->count . " Exitosas con monto cero</small>";
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de datos Dash Transaccion exitosa monto cero: " . $e->getMessage());
            return -213;
        }

        return $response;
    }
    public function pendiente()
    {
        try {
            $fechaEnd = date('Y-m-d H:i:s');

            $fecha = date('Y-m-d', strtotime($fechaEnd . "- 30 days"));
            $fechaInit = $fecha . ' 00:00:00';

            \Log::info('movements',['fechaInit'=> $fechaInit,'fechafin'=>$fechaEnd]);

            $movements = \DB::table('mt_movements')
            ->select('mt_movements.id','atms.name as atm', 'movement_type.description', 'mt_movements.destination_operation_id', 'mt_movements.amount')
            ->join('movement_type','mt_movements.movement_type_id','=','movement_type.id')
            ->join('atms','atms.id','=','mt_movements.atm_id')
            ->whereBetween('mt_movements.created_at',[$fechaInit,$fechaEnd])
            ->whereIn('movement_type.id',[1, 6, 13, 14])
            ->whereIn('mt_movements.destination_operation_id',[1,0])
            ->whereNull('mt_movements.deleted_at')
            ->count();

            \Log::info('movements',['result'=> $movements]);

            $response['error'] = false;
            $response['message'] = "<small>" . $movements . " Pendientes de afectar extractos</small>";
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de datos Dash Transaccion exitosa monto cero: " . $e->getMessage());
            return -213;
        }

        return $response;
    }

    public function getTransactionsHistory($frecuency)
    {

        try {
            $response['error'] = false;
            $response['message'] = $frecuency;

            $date = Carbon::now();
            $date_start = $date->format('Y-m-d 00:00:00');
            $date_end = Carbon::now();


            if ($frecuency == 'daily') {

                $response['dates'] = "<b>Estados de transacciones del " . $date->format('d-m-Y') . "</b>";

            } elseif ($frecuency == 'weekly') {

                $date->startOfWeek()->subDays(1);
                $response['dates'] = "<b>Estados de transacciones desde " . $date->format("d") . " hasta el " . $date_end->format('d-m-Y') . "<b>";
                $date_start = $date->format('Y-m-d 00:00:00');

            } else {

                if ($frecuency == 'monthly') {

                    $date->startOfMonth();
                    $response['dates'] = "<b>Estados de transacciones desde " . $date->format("d") . " hasta el " . $date_end->format('d-m-Y') . "<b>";
                    $date_start = $date->format('Y-m-d 00:00:00');

                } else {
                    $daterange = explode(' - ',  str_replace('/', '-', $frecuency));
                    $start = Carbon::parse(date('Y-m-d H:i:s', strtotime($daterange[0])));
                    $end = Carbon::parse(date('Y-m-d H:i:s', strtotime($daterange[1])));
                    $datediff = $start->diffInDays($end);

                    if ($datediff > 31) {
                        $response['error'] = true;
                        $response['message'] = 'Fechas fuera de rango - Disponible max 31 dias';
                        return $response;
                    }

                    if ($datediff > 0) {
                        $frecuency = 'monthly';
                    } else {
                        $frecuency = 'daily';
                    }

                    $date_start = $start->format('Y-m-d 00:00:00');
                    $date_end = $end->format('Y-m-d 23:59:59');
                    $response['dates'] = "<b>Estados de transacciones desde " . $start->format("d-m") . " hasta el " . $end->format("d-m-Y") . "<b>";
                }

            }

            \Log::debug("Contador de transacciones por estado y fecha:", ['result' => $response]);

            $response['data'] = $this->prepare_transaction_graph($frecuency, $date_start, $date_end);
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de datos Dash transactions: " . $e->getMessage());
            return -213;
        }

        return $response;
    }

    public function prepare_transaction_graph($frecuency, $start, $end)
    {
        try {

            $transactions = \DB::table('transactions as t')
                ->select(
                    't.id', 
                    't.status', 
                    't.created_at'
                )
                ->whereRaw("t.created_at between '$start' and '$end'")
                ->orderBy('t.id', 'ASC')
                ->get();

            // $transactions =  \DB::select('select * from get_graphic_transaction(? ,?)', [$start,$end]);

            $lists = array();
            foreach ($transactions as $transaction) {
                $created_at = Carbon::parse($transaction->created_at);
                if ($frecuency == 'daily') {
                    $lists[$created_at->format('H') . 'hs'][] = $transaction; // 00hs
                } else {
                    $lists[$created_at->format('d-m')][] = $transaction; // 09-04
                }
            }

            //\Log::info("Fecha rara:", [$lists]);

            $categories = array();
            foreach ($lists as $key => $value) {
                $transactions = $value;
                $i = 0; // values from success
                $j = 0; // values from error
                $k = 0; // values from initiated
                $l = 0; // values from canceled
                foreach ($transactions as $transaction) {
                    if ($transaction->status == 'success') {
                        $i++;
                    }

                    if ($transaction->status == 'error') {
                        $j++;
                    }

                    if ($transaction->status == 'iniciated') {
                        $k++;
                    }

                    if ($transaction->status == 'canceled') {
                        $l++;
                    }
                }

                $categories[] = [
                    "category" => $key,
                    "Exitosas" => $i,
                    "Canceladas" => $l,
                    "Error" => $j,
                    "Iniciadas" => $k
                ];
            }

            //\Log::info($categories);
            return $categories;
        } catch (\Exception $e) {
            \Log::debug('Dashboar transactions error', ['result' => $e]);
        }
    }

    public function estadoGeneral($redes)
    {

        /*$response = [
            'data' => [],
            'error' => false
        ];*/

        $response = [];

        //\Log::info("RED: $redes");

        try {

            if ($redes == 'miniterminales') {
                $atms_status = \DB::connection('eglobalt_replica')->table('atms')
                    ->selectRaw("
                        count( 
                            CASE 
                                WHEN atms.atm_status = 0 
                                and (bloqueo_ventas = false or bloqueo_ventas isnull) AND(bloqueo_cuota = false or bloqueo_cuota isnull) THEN
                                    1
                                END
                        ) AS online,
                        count( 
                            CASE 
                                WHEN 
                                (bloqueo_ventas = true or bloqueo_cuota = true)
                                then 1 
                                END) AS bloqueados,
                        count( 
                            CASE 
                                WHEN 
                                    atms.atm_status NOT IN (0, 80) 
                                THEN
                                    1
                                END
                        ) AS suspendido,
                        count( 
                            CASE 
                                WHEN 
                                    atms.atm_status IN (80) 
                                    AND TRUNC(EXTRACT(EPOCH FROM (now()::TIMESTAMP - atms.last_request_at::TIMESTAMP))/60) > 26 
                                THEN
                                    1
                                END
                        ) AS no_autorizado,
                        count( 
                            CASE 
                                WHEN 
                                    atms.atm_status IN (0) 
                                    and TRUNC(EXTRACT(EPOCH FROM (now()::TIMESTAMP - atms.last_request_at::TIMESTAMP))/60) >= 26 
                                THEN
                                    1
                                END
                        ) as offline 
                    ")
                    ->where('atms.deleted_at', null)
                    ->where('atms.type', 'at')
                    ->whereIn('atms.owner_id', [16, 21, 25])
                    ->first();

                //$atms_status = $atms_status[0];

                //$atms_status = json_encode($atms_status);
                //$atms_status = json_decode($atms_status);
                //\Log::info("Items: ");
                //\Log::info($atms_status);



                $capacidad_maxima = \DB::connection('eglobalt_replica')->select("
                    select
                    count(
                        case
                            when porcentaje >= 80 then
                                1
                            END
                    ) as valor
                    from
                    (
                        select 
                            sum(cantidad_actual)*100/max(cantidad_maxima) as porcentaje,
                            atm_id
                        from 
                            atms_parts
                        inner join atms on atms.id = atms_parts.atm_id
                        where
                            atms.deleted_at is null
                            and atms.type = 'at'
                            and atms.owner_id not in (16, 21, 25) 
                            and tipo_partes = 'Box'
                            AND NOT EXISTS(SELECT * FROM atms_parts WHERE atms.id = atms_parts.atm_id AND atms_parts.tipo_partes = 'Cassette' )

                        group by 
                            atm_id
                        order by 
                            atm_id desc
                    ) partes");



                $cantidad_minima = \DB::connection('eglobalt_replica')
                    ->table('atms_parts as ap')
                    ->select(
                        \DB::raw("count(distinct(ap.atm_id)) as valor")
                    )
                    ->join('atms as a', 'a.id', '=', 'ap.atm_id')
                    ->whereRaw('ap.cantidad_alarma >= ap.cantidad_actual')
                    ->whereRaw("ap.tipo_partes <> 'Box'")
                    ->where('ap.cantidad_alarma', '>', 0)
                    ->where('a.deleted_at', null)
                    ->where('a.type', 'at')
                    ->whereRaw('a.owner_id in (16, 21, 25)')
                    ->take(1)
                    ->get();

                if (count($cantidad_minima) > 0) {
                    $cantidad_minima = $cantidad_minima[0]->valor;
                } else {
                    $cantidad_minima = 0;
                }

                //\Log::info("cantidad minima: $cantidad_minima");
                //die();
                //$query = $cantidad_minima->toSql();
                //\Log::info("QUERY: \n$query");
                //$cantidad_minima = $cantidad_minima->get(); 


            } elseif ($redes == 'terminales') {
                $atms_status = \DB::connection('eglobalt_replica')->table('atms')
                    ->selectRaw("
                        count( 
                            CASE 
                                WHEN atms.atm_status = 0 AND TRUNC(EXTRACT(EPOCH FROM (now()::TIMESTAMP - atms.last_request_at::TIMESTAMP))/60) <= 26 THEN
                                    1
                                END
                        ) AS online,
                        count( 
                            CASE 
                                WHEN atms.atm_status = 0 
                                AND TRUNC(EXTRACT(EPOCH FROM (now()::TIMESTAMP - atms.last_request_at::TIMESTAMP))/60) <= 26 
                                and (bloqueo_ventas = true or bloqueo_cuota = true)
                                then 1 
                                END) AS bloqueados,
                        count( 
                            CASE 
                                WHEN 
                                    atms.atm_status NOT IN (0, 80) 
                                THEN
                                    1
                                END
                        ) AS suspendido,
                        count( 
                            CASE 
                                WHEN 
                                    atms.atm_status IN (80) 
                                    AND TRUNC(EXTRACT(EPOCH FROM (now()::TIMESTAMP - atms.last_request_at::TIMESTAMP))/60) > 26 
                                THEN
                                    1
                                END
                        ) AS no_autorizado,
                        count( 
                            CASE 
                                WHEN 
                                    atms.atm_status IN (0) 
                                    and TRUNC(EXTRACT(EPOCH FROM (now()::TIMESTAMP - atms.last_request_at::TIMESTAMP))/60) >= 26 
                                THEN
                                    1
                                END
                        ) as offline 
                    ")
                    ->where('atms.deleted_at', null)
                    ->where('atms.type', 'at')
                    ->whereNotIn('atms.owner_id', [16, 18, 21, 25])
                    ->first();

                $capacidad_maxima = \DB::connection('eglobalt_replica')->select("
                    select
                    count(
                        case
                            when porcentaje >= 80 then
                                1
                            END
                    ) as valor
                    from
                    (
                        select 
                            sum(cantidad_actual)*100/max(cantidad_maxima) as porcentaje,
                            atm_id
                        from 
                            atms_parts
                        inner join atms on atms.id = atms_parts.atm_id
                        where
                            atms.deleted_at is null
                            and atms.type = 'at'
                            and atms.owner_id not in (16,18,21,25) 
                            and tipo_partes = 'Box'
                        group by 
                            atm_id
                        order by 
                            atm_id desc
                    ) partes");

                $cantidad_minima = \DB::connection('eglobalt_replica')->table('atms_parts')
                    ->selectRaw('count(distinct(atm_id)) as valor')
                    ->join('atms', 'atms.id', '=', 'atms_parts.atm_id')
                    ->whereRaw('cantidad_alarma >= cantidad_actual')
                    ->where('tipo_partes', '<>', "'Box'")
                    ->where('cantidad_alarma', '>', 0)
                    ->where('atms.deleted_at', null)
                    ->where('atms.type', 'at')
                    //->whereNotIn('atms.owner_id', [16, 18, 21])    
                    ->whereRaw('atms.owner_id not in (16, 18, 21,25)')
                    ->take(1)
                    ->get();

                if (count($cantidad_minima) > 0) {
                    $cantidad_minima = $cantidad_minima[0]->valor;
                } else {
                    $cantidad_minima = 0;
                }
            } else {
                $atms_status = \DB::connection('eglobalt_replica')->table('atms')
                    ->selectRaw("
                            count( 
                                CASE 
                                    WHEN atms.atm_status = 0 AND TRUNC(EXTRACT(EPOCH FROM (now()::TIMESTAMP - atms.last_request_at::TIMESTAMP))/60) <= 26 THEN
                                        1
                                    END
                            ) AS online,
                            count( 
                                CASE 
                                    WHEN (bloqueo_ventas = true or bloqueo_cuota = true)
                                    then 1 
                                    END) AS bloqueados,
                            count( 
                                CASE 
                                    WHEN 
                                        atms.atm_status NOT IN (0, 80) 
                                    THEN
                                        1
                                    END
                            ) AS suspendido,
                            count( 
                                CASE 
                                    WHEN 
                                        atms.atm_status IN (80) 
                                        AND TRUNC(EXTRACT(EPOCH FROM (now()::TIMESTAMP - atms.last_request_at::TIMESTAMP))/60) > 26 
                                    THEN
                                        1
                                    END
                            ) AS no_autorizado,
                            count( 
                                CASE 
                                    WHEN 
                                        atms.atm_status IN (0) 
                                        and TRUNC(EXTRACT(EPOCH FROM (now()::TIMESTAMP - atms.last_request_at::TIMESTAMP))/60) >= 26 
                                    THEN
                                        1
                                    END
                            ) as offline 
                        ")
                    ->where('atms.deleted_at', null)
                    ->where('atms.type', 'at')
                    ->where('atms.owner_id', '!=', 18)
                    ->first();

                $capacidad_maxima = \DB::connection('eglobalt_replica')->select("
                        select
                        count(
                            case
                                when porcentaje >= 80 then
                                    1
                                END
                        ) as valor
                        from
                        (
                            select 
                                sum(cantidad_actual)*100/max(cantidad_maxima) as porcentaje,
                                atm_id
                            from 
                                atms_parts
                            inner join atms on atms.id = atms_parts.atm_id
                            where
                                atms.deleted_at is null
                                and atms.type = 'at'
                                and atms.owner_id  not in (18,16, 21, 25)
                                and tipo_partes = 'Box'
                            group by 
                                atm_id
                            order by 
                                atm_id desc
                        ) partes");

                $cantidad_minima = \DB::connection('eglobalt_replica')->table('atms_parts')
                    ->selectRaw('count(distinct(atm_id)) as valor')
                    ->join('atms', 'atms.id', '=', 'atms_parts.atm_id')
                    ->whereRaw('cantidad_alarma >= cantidad_actual')
                    ->where('tipo_partes', '<>', "'Box'")
                    ->where('cantidad_alarma', '>', 0)
                    ->where('atms.deleted_at', null)
                    ->where('atms.type', 'at')
                    //->whereNotIn('atms.owner_id', [18, 21])    
                    ->whereRaw('atms.owner_id not in (18, 21, 25)')
                    ->take(1)
                    ->get();

                if (count($cantidad_minima) > 0) {
                    $cantidad_minima = $cantidad_minima[0]->valor;
                } else {
                    $cantidad_minima = 0;
                }
            }

            //die();

            $data = [
                'online' => $atms_status->online,
                'bloqueados' => $atms_status->bloqueados,
                'suspendido' => $atms_status->suspendido,
                'offline' => $atms_status->offline,
                'capacidad_maxima' => $capacidad_maxima[0]->valor,
                'cantidad_minima' => $cantidad_minima,
            ];

            $response['data'] = $data;
            $response['error'] = false;
        } catch (\Exception $e) {
            $error_detail = [
                'message' => 'Ocurrió un error.',
                'error' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];

            \Log::error($error_detail);
        }

        return $response;
    }


    public function atmsDetalle($input)
    {
        try {
            if ($input['redes'] == 'miniterminales') {
                $atms = \DB::connection('eglobalt_replica')->table('atms')
                    ->select(['atms.*', 'owners.name as owner'])
                    ->join('owners', 'owners.id', '=', 'atms.owner_id')
                    ->where('atms.type', 'at')
                    ->orderBy('atms.last_request_at', 'asc');

                $label = '';

                switch ($input['status']) {
                    case 'online':
                        $atms = $atms //->where('atms.atm_status', 0)
                            ->whereIn('atms.owner_id', [16, 21, 25])
                            //->whereRaw('TRUNC(EXTRACT(EPOCH FROM (now()::TIMESTAMP - atms.last_request_at::TIMESTAMP))/60) <= 26')
                            ->whereRaw('(bloqueo_ventas = false or bloqueo_ventas isnull) AND(bloqueo_cuota = false or bloqueo_cuota isnull)')
                            ->where('atms.deleted_at', null)
                            ->get();
                        $label = '<span><i class="fa fa-circle text-green"></i> Online<span>';
                        break;
                    case 'bloqueados':
                        $atms = $atms->whereIn('atms.owner_id', [16, 21, 25])
                            //->whereRaw('TRUNC(EXTRACT(EPOCH FROM (now()::TIMESTAMP - atms.last_request_at::TIMESTAMP))/60) <= 26')
                            ->whereRaw('(atms.bloqueo_ventas = true or atms.bloqueo_cuota=true)')
                            ->where('atms.deleted_at', null)
                            ->get();
                        break;
                    case 'offline':
                        $atms = $atms->where('atms.atm_status', 0)
                            ->whereIn('atms.owner_id', [16, 21, 25])
                            ->whereRaw('TRUNC(EXTRACT(EPOCH FROM (now()::TIMESTAMP - atms.last_request_at::TIMESTAMP))/60) > 26')
                            ->where('atms.deleted_at', null)
                            ->get();

                        $label = '<span><i class="fa fa-circle text-yellow"></i> Offline<span>';
                        break;
                    case 'suspendido':
                        $atms = $atms->whereNotIn('atms.atm_status', [0, 80])
                            ->where('atms.owner_id', '=', 16)
                            ->where('atms.deleted_at', null)
                            ->get();
                        $label = '<span><i class="fa fa-circle text-red"></i> Suspendido<i class="pay-info fa fa-info-circle" style="cursor:pointer" data-toggle="tooltip" title="Detalle"></i><span>';
                        break;
                    case 'capacidad_maxima':
                        $atms = $atms->whereNotIn('atms.owner_id', [16, 21, 25])
                            ->whereIn('atms.id', function ($query) {
                                $query->select("atm_id")
                                    ->from(\DB::raw("
                                        (
                                            SELECT 
                                            sum(cantidad_actual)*100/max(cantidad_maxima) AS porcentaje,
                                            atm_id
                                            FROM 
                                            atms_parts
                                            INNER JOIN atms ON atms.id = atms_parts.atm_id
                                            WHERE atms.deleted_at IS NULL
                                            AND atms.owner_id NOT IN (16, 21, 25)
                                            AND tipo_partes = 'Box'
                                            AND NOT EXISTS(SELECT * FROM atms_parts WHERE atms.id = atms_parts.atm_id AND atms_parts.tipo_partes = 'Cassette' )
                                            GROUP BY atm_id
                                            ORDER BY atm_id DESC
                                        ) partes
                                    "))
                                    ->whereRaw('porcentaje >= 80');
                            })
                            ->where('atms.deleted_at', null)
                            ->get();

                        $label = '<span><i class="fa fa-circle text-light-blue"></i> Capacidad Maxima<span>';
                        break;
                    case 'cantidad_minima':
                        $atms = $atms->whereIn('atms.owner_id', [16, 21, 25])
                            ->whereIn('atms.id', function ($query) {
                                $query->select('atms_parts.atm_id')
                                    ->from('atms_parts')
                                    ->where('atms_parts.cantidad_alarma', '>', '0')
                                    ->whereRaw('atms_parts.cantidad_alarma >= atms_parts.cantidad_actual')
                                    ->whereRaw("atms_parts.tipo_partes <> 'Box'");
                            })
                            ->where('atms.deleted_at', null)
                            ->get();

                        $label = '<span><i class="fa fa-circle text-aqua"></i> Cantidad Mínima<i class="detalle_minimo fa fa-info-circle" style="cursor:pointer" data-toggle="tooltip" title="Detalle"></i><span><span>';
                        break;
                }
            } elseif ($input['redes']  == 'terminales') {
                $atms = \DB::connection('eglobalt_replica')->table('atms')
                    ->select(['atms.*', 'owners.name as owner'])
                    ->join('owners', 'owners.id', '=', 'atms.owner_id')
                    ->where('atms.type', 'at')
                    ->whereNotIn('atms.owner_id', [16, 18, 21, 25])
                    ->orderBy('atms.last_request_at', 'asc');
                $label = '';

                switch ($input['status']) {
                    case 'online':
                        $atms = $atms->where('atms.atm_status', 0)
                            ->whereRaw(
                                'TRUNC(EXTRACT(EPOCH FROM (now()::TIMESTAMP - atms.last_request_at::TIMESTAMP))/60) <= 26'
                            )
                            ->where('atms.deleted_at', null)
                            ->get();
                        $label = '<span><i class="fa fa-circle text-green"></i> Online<span>';
                        break;
                    case 'offline':
                        $atms = $atms->where('atms.atm_status', 0)
                            ->whereRaw(
                                'TRUNC(EXTRACT(EPOCH FROM (now()::TIMESTAMP - atms.last_request_at::TIMESTAMP))/60) > 26'
                            )
                            ->where('atms.deleted_at', null)
                            ->get();

                        $label = '<span><i class="fa fa-circle text-yellow"></i> Offline<span>';
                        break;
                    case 'suspendido':
                        $atms = $atms->whereNotIn('atms.atm_status', [0, 80])
                            ->where('atms.deleted_at', null)
                            ->get();
                        $label = '<span><i class="fa fa-circle text-red"></i> Suspendido<i class="pay-info fa fa-info-circle" style="cursor:pointer" data-toggle="tooltip" title="Detalle"></i><span>';
                        break;
                    case 'capacidad_maxima':
                        $atms = $atms->whereIn('atms.id', function ($query) {
                            $query->select("atm_id")
                                ->from(\DB::raw("
                                        (
                                            SELECT 
                                            sum(cantidad_actual)*100/max(cantidad_maxima) AS porcentaje,
                                            atm_id
                                            FROM 
                                            atms_parts
                                            INNER JOIN atms ON atms.id = atms_parts.atm_id
                                            WHERE atms.deleted_at IS NULL

                                            AND atms.owner_id not in (16,18,21,25)

                                            AND tipo_partes = 'Box'
                                            GROUP BY atm_id
                                            ORDER BY atm_id DESC
                                        ) partes
                                    "))
                                ->whereRaw('porcentaje >= 80');
                        })
                            ->where('atms.deleted_at', null)
                            ->get();
                        $label = '<span><i class="fa fa-circle text-light-blue"></i> Capacidad Maxima<span>';
                        break;
                    case 'cantidad_minima':
                        $atms = $atms->whereIn('atms.id', function ($query) {
                            $query->select('atms_parts.atm_id')
                                ->from('atms_parts')
                                ->where('atms_parts.cantidad_alarma', '>', '0')
                                ->whereRaw('atms_parts.cantidad_alarma >= atms_parts.cantidad_actual')
                                ->whereRaw("atms_parts.tipo_partes <> 'Box'");
                        })
                            ->where('atms.deleted_at', null)
                            ->get();
                        $label = '<span><i class="fa fa-circle text-aqua"></i> Cantidad Mínima<i class="detalle_minimo fa fa-info-circle" style="cursor:pointer" data-toggle="tooltip" title="Detalle"></i><span><span>';
                        break;
                }
            } else {
                $atms = \DB::connection('eglobalt_replica')->table('atms')
                    ->select(['atms.*', 'owners.name as owner'])
                    ->join('owners', 'owners.id', '=', 'atms.owner_id')
                    ->where('atms.type', 'at')
                    ->where('atms.owner_id', '!=', 18)
                    ->orderBy('atms.last_request_at', 'asc');

                $label = '';

                switch ($input['status']) {
                    case 'online':
                        $atms = $atms->where('atms.atm_status', 0)
                            ->whereRaw('TRUNC(EXTRACT(EPOCH FROM (now()::TIMESTAMP - atms.last_request_at::TIMESTAMP))/60) <= 26')
                            ->where('atms.deleted_at', null)
                            ->get();
                        $label = '<span><i class="fa fa-circle text-green"></i> Online<span>';
                        break;
                    case 'bloqueados':
                        $atms = $atms->where('atms.owner_id', '=', 16)
                            //->whereRaw('TRUNC(EXTRACT(EPOCH FROM (now()::TIMESTAMP - atms.last_request_at::TIMESTAMP))/60) <= 26')
                            ->whereRaw('(atms.bloqueo_ventas = true or atms.bloqueo_cuota=true)')
                            ->where('atms.deleted_at', null)
                            ->get();
                        break;
                    case 'offline':
                        $atms = $atms->where('atms.atm_status', 0)
                            ->whereRaw('TRUNC(EXTRACT(EPOCH FROM (now()::TIMESTAMP - atms.last_request_at::TIMESTAMP))/60) > 26')
                            ->where('atms.deleted_at', null)
                            ->get();
                        $label = '<span><i class="fa fa-circle text-yellow"></i> Offline<span>';
                        break;
                    case 'suspendido':
                        $atms = $atms->whereNotIn('atms.atm_status', [0, 80])
                            ->where('atms.deleted_at', null)
                            ->get();
                        $label = '<span><i class="fa fa-circle text-red"></i> Suspendido<i class="pay-info fa fa-info-circle" style="cursor:pointer" data-toggle="tooltip" title="Detalle"></i><span>';
                        break;
                    case 'capacidad_maxima':
                        $atms = $atms->whereIn('atms.id', function ($query) {
                            $query->select("atm_id")
                                ->from(\DB::raw("
                                        (
                                            SELECT 
                                            sum(cantidad_actual)*100/max(cantidad_maxima) AS porcentaje,
                                            atm_id
                                            FROM 
                                            atms_parts
                                            INNER JOIN atms ON atms.id = atms_parts.atm_id
                                            WHERE atms.deleted_at IS NULL
                                            AND atms.owner_id  not in (18,16,21,25)
                                            AND tipo_partes = 'Box'
                                            GROUP BY atm_id
                                            ORDER BY atm_id DESC
                                        ) partes
                                    "))
                                ->whereRaw('porcentaje >= 80');
                        })
                            ->where('atms.deleted_at', null)
                            ->whereNotIn('atms.owner_id', [16, 21, 25])
                            ->get();
                        $label = '<span><i class="fa fa-circle text-light-blue"></i> Capacidad Maxima<span>';
                        break;
                    case 'cantidad_minima':
                        $atms = $atms->whereIn('atms.id', function ($query) {
                            $query->select('atms_parts.atm_id')
                                ->from('atms_parts')
                                ->where('atms_parts.cantidad_alarma', '>', '0')
                                ->whereRaw('atms_parts.cantidad_alarma >= atms_parts.cantidad_actual')
                                ->whereRaw("atms_parts.tipo_partes <> 'Box'");
                        })
                            ->where('atms.deleted_at', null)
                            ->get();

                        $label = '<span><i class="fa fa-circle text-aqua"></i> Cantidad Mínima<i class="detalle_minimo fa fa-info-circle" style="cursor:pointer" data-toggle="tooltip" title="Detalle"></i><span><span>';
                        break;
                }
            }


            $data = '';
            foreach ($atms as $key => $atm) {
                $now  = Carbon::now();
                $end =  Carbon::parse($atm->last_request_at);
                $elasep = $now->diffInMinutes($end);
                $atm->elasep = $elasep;

                $data .= '<tr data-id="' . $atm->id . '">';
                $data .= '<td>' . $atm->id . '</td>';
                $data .= '<td>' . $atm->name . '</td>';
                $data .= '<td>' . $atm->code . '</td>';
                $data .= '<td>' . $atm->owner . '</td>';
                if ($input['status'] == 'bloqueados') {
                    if ($atm->bloqueo_ventas == true and $atm->bloqueo_cuota != true) {
                        \Log::info('bloqueado por ventas');
                        $label = '<span><i class="fa fa-circle text-red" ></i> Bloqueado por Venta<span>';
                        $data .= '<td style="width:190px">' . $label . '</td>';
                    } elseif ($atm->bloqueo_ventas != true and $atm->bloqueo_cuota == true) {
                        \Log::info('bloqueado por cuotas');
                        $label = '<span><i class="fa fa-circle text-red" ></i> Bloqueado por Cuota<span>';
                        $data .= '<td style="width:190px">' . $label . '</td>';
                    } elseif ($atm->bloqueo_ventas == TRUE and $atm->bloqueo_cuota == TRUE) {
                        \Log::info('bloqueado por ventas + cuotas');
                        $label = '<span><i class="fa fa-circle text-red" ></i> Bloqueado por Venta + Cuota<span>';
                        $data .= '<td style="width:190px">' . $label . '</td>';
                    }
                } else {
                    $data .= '<td style="width:100px">' . $label . '</td>';
                }
                $data .= '<td>' . Carbon::parse($atm->last_request_at)->format('d/m/Y H:i:s') . '</td>';
                $data .= '<td>' . $elasep . ' min.</td>';

                if ($input['status'] == 'capacidad_maxima') {
                    $atm_part = \DB::connection('eglobalt_replica')->select("
                            select 
                                actual,
                                maximo
                            from
                                (
                                    SELECT 
                                    sum(cantidad_actual) as actual,
                                    max(cantidad_maxima) as maximo,
                                    sum(cantidad_actual)*100/max(cantidad_maxima) AS porcentaje,
                                    atm_id
                                    FROM 
                                    atms_parts
                                    INNER JOIN atms ON atms.id = atms_parts.atm_id
                                    WHERE atms.deleted_at IS NULL
                                    AND tipo_partes = 'Box'
                                    GROUP BY atm_id
                                    ORDER BY atm_id DESC
                                ) partes
                            where atm_id = " . $atm->id . " and porcentaje >= 80
                            ");

                    if (isset($atm_part[0])) {
                        $data .= '<td>' . $atm_part[0]->actual . '</td>';
                        $data .= '<td>' . $atm_part[0]->maximo . '</td>';
                    } else {
                        $data .= '<td></td>';
                        $data .= '<td></td>';
                    }
                }

                $data .= '<td>' . $atm->compile_version . '</td>';

                $data .= '</tr>';
            }

            $response = [
                'modal_contenido' => $data,
                'modal_footer' => '',
                'error' => false
            ];

            return $response;
        } catch (\Exception $e) {
            \Log::info($e);

            $response = [
                'error' => true
            ];

            return $response;;
        }
    }

    public function getDetallesCantidades($atm_id)
    {
        try {
            $partes = \DB::connection('eglobalt_replica')->table('atms_parts')
                ->select('*')
                ->where('atms_parts.cantidad_alarma', '>', '0')
                ->where('atms_parts.atm_id', $atm_id)
                ->whereRaw('atms_parts.cantidad_alarma >= atms_parts.cantidad_actual')
                ->whereRaw("atms_parts.tipo_partes <> 'Box'")
                ->get();

            $data = '';
            foreach ($partes as $key => $parte) {
                $data .= '<tr data-id="' . $parte->id . '">';
                $data .= '<td>' . $parte->nombre_parte . '</td>';
                $data .= '<td>' . $parte->denominacion . '</td>';
                $data .= '<td>' . $parte->cantidad_minima . '/' . $parte->cantidad_actual . '</td>';

                if ($parte->cantidad_actual <= $parte->cantidad_minima) {
                    $data .= '<td><span class="pull-right badge bg-red"><i class="fa fa-times-circle"></i> Cantidad mínima alcanzada</span></td>';
                }

                if ($parte->cantidad_actual > $parte->cantidad_minima && $parte->cantidad_actual <= $parte->cantidad_alarma) {
                    $data .= '<td><span class="pull-right badge bg-yellow"><i class="fa fa-exclamation-triangle"></i> Cantidad actual cercana al mínimo</span></td>';
                }

                $data .= '</tr>';
            }

            $response = [
                'modal_contenido' => $data,
                'modal_footer' => '',
                'error' => false
            ];

            return $response;
        } catch (\Exception $e) {
            \Log::info($e);

            $response = [
                'error' => true
            ];

            return $response;;
        }
    }

    public function balanceOnline()
    {

        // $endpoint = '192.168.3.33:85/epin/consulta_saldo'; // DEV
        // $apiKey = 'HJmjJ7RqtiDxraZWBMD3CIqjM4gT0sI4'; //DEV

        $endpoint = 'https://api.eglobalt.com.py/epin/consulta_saldo'; // PRO
        $apiKey = 'WrXgCSsrbC4O2lHGxVWhAcZZQd9mwtLZ'; // PRO

        try {
            $petition = HttpClient::post(
                $endpoint,
                [
                    'headers' => ['api-key' => $apiKey],
                    'verify' => false,
                    'connect_timeout' => 240
                ]
            );

            $petition = json_decode($petition->getBody()->getContents(), true);
            \Log::info($petition);
            $response    = [];
            if ($petition['error'] <> true) {
                $credit      = $petition['data']['credit'];
                $moneda      = $petition['data']['moneda'];
                $description = $petition['data']['description'];

                $data = [
                    'credit'      => $credit,
                    'valor'       => intval(str_replace(".", "", $credit)),
                    //'valor'       => 51000000,
                    'moneda'      => $moneda,
                    'description' => $description
                ];
                $response['error'] = false;
                $response['data'] = $data;

                $valor = intval(str_replace(".", "", $credit));

                switch ($valor) {
                    case ($valor <= 5000000):
                        \Log::info('Sin saldo en la billetera EPIN-EGLOBAL');

                        Mail::send(
                            'mails.alert_saldo',
                            $data,
                            function ($message) {
                                $user_email = 'tesoreria@antell.com.py';
                                //$user_email = 'jorgegauto19@gmail.com.py';
                                $user_name  = 'Admin';
                                $message->to($user_email, $user_name)
                                    ->cc('jgauto@eglobalt.com.py')
                                    ->cc('avillar@eglobalt.com.py')
                                    ->cc('sistemas@eglobalt.com.py')
                                    ->subject('[EGLOBAL] - Sin saldo en la linea EPIN-EGLOBAL');
                            }
                        );

                        break;
                    case ($valor > 5000000 && $valor <= 20000000):
                        \Log::info('Saldo crítico en la lÍnea EPIN-EGLOBAL');

                        Mail::send(
                            'mails.alert_saldo',
                            $data,
                            function ($message) {
                                $user_email = 'tesoreria@antell.com.py';
                                $user_name  = 'Admin';
                                $message->to($user_email, $user_name)
                                    ->cc('jgauto@eglobalt.com.py')
                                    ->cc('sistemas@eglobalt.com.py')
                                    ->cc('avillar@eglobalt.com.py')
                                    ->subject('[EGLOBAL] - Saldo crítico en la linea EPIN-EGLOBAL');
                            }
                        );

                        break;
                    case ($valor > 20000000 && $valor <= 30000000):
                        \Log::info('Saldo bajo en la línea EPIN-EGLOBAL');

                        Mail::send(
                            'mails.alert_saldo',
                            $data,
                            function ($message) {
                                $user_email = 'tesoreria@antell.com.py';
                                $user_name  = 'Admin';
                                $message->to($user_email, $user_name)
                                    ->cc('jgauto@eglobalt.com.py')
                                    ->cc('sistemas@eglobalt.com.py')
                                    ->cc('avillar@eglobalt.com.py')
                                    ->subject('[EGLOBAL] - Saldo bajo en la linea EPIN -Eglobal');
                            }
                        );

                        break;
                }
            } else {
                $data = [
                    'credit'      => "Sin información",
                    'moneda'      => "Sin información",
                    'description' => "Sin información"
                ];
                $response['error']  = true;
                $response['data']   = $data;
            }








            return $response;
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de datos Dash Services: " . $e->getMessage());
            return -999;
        }
    }

}
