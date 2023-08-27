<?php

namespace App\Http\Controllers;

use App\Models\Notifications;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Facades\DashService;
use Carbon\Carbon;
use Session;

class DashboardController extends Controller
{
    protected $user;

    public function __construct()
    {
       $this->middleware('auth');
       $this->user = \Sentinel::getUser();
    }

    public $message = array(
        "params_error"		=> "Faltan parametros.",
        "success"			=> "Operacion finalizada exitosamente.",
        "error"				=> "Error en la operación",
        "error_null"		=> "Error, datos nulos",
        "null_data"		    => "Datos nulos",
    );

    public function monitoringCollections($collection, Request $request){
        if (!$this->user->hasAccess('monitoreo')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        try{
            ini_set('memory_limit', '512M');
            switch($collection){
                case 'atms':
                    $response = DashService::getAtmsStatus();
                    if(empty($response)){
                        return response()->json([
                            "status"    => false,
                            "message"   => $this->message["error_null"],
                            "result"    => null
                        ], 200);
                    }

                    if(is_null($response)){
                        return response()->json([
                            "status"    => false,
                            "message"   => $this->message["error_null"],
                            "result"    => null
                        ], 200);
                    }

                    if($response['error'] == true){
                        return response()->json([
                            "status"    => false,
                            "message"   => $this->message["error"],
                            "result"    => null
                        ], 200);
                    }
                    return response()->json([
                        "status"    => true,
                        "message"   => $this->message["success"],
                        "result"    => $response
                    ], 200);
                    break;
                case 'services':
                    $response = DashService::getServicesStatus();
                    if(empty($response)){
                        return response()->json([
                            "status"    => false,
                            "message"   => $this->message["error_null"],
                            "result"    => null
                        ], 200);
                    }

                    if(is_null($response)){
                        return response()->json([
                            "status"    => false,
                            "message"   => $this->message["error_null"],
                            "result"    => null
                        ], 200);
                    }

                    if($response['error'] == true){
                        return response()->json([
                            "status"    => false,
                            "message"   => $this->message["error"],
                            "result"    => null
                        ], 200);
                    }

                    return response()->json([
                        "status"    => true,
                        "message"   => $this->message["success"],
                        "result"    => $response
                    ], 200);
                    break;
                case 'balances':
                    $response = DashService::getBalanceStatus();
                    if(empty($response)){
                        return response()->json([
                            "status"    => false,
                            "message"   => $this->message["error_null"],
                            "result"    => null
                        ], 200);
                    }

                    if(is_null($response)){
                        return response()->json([
                            "status"    => false,
                            "message"   => $this->message["error_null"],
                            "result"    => null
                        ], 200);
                    }

                    if($response['error'] == true){
                        return response()->json([
                            "status"    => false,
                            "message"   => $this->message["error"],
                            "result"    => null
                        ], 200);
                    }

                    return response()->json([
                        "status"    => true,
                        "message"   => $this->message["success"],
                        "result"    => $response
                    ], 200);
                    break;
                case 'warnings':
                    $response = DashService::getWarningStatus();
                    if(empty($response)){
                        return response()->json([
                            "status"    => false,
                            "message"   => $this->message["error_null"],
                            "result"    => null
                        ], 200);
                    }

                    if(is_null($response)){
                        return response()->json([
                            "status"    => false,
                            "message"   => $this->message["error_null"],
                            "result"    => null
                        ], 200);
                    }

                    if($response['error'] == true){
                        return response()->json([
                            "status"    => false,
                            "message"   => $this->message["error"],
                            "result"    => null
                        ], 200);
                    }

                    return response()->json([
                        "status"    => true,
                        "message"   => $this->message["success"],
                        "result"    => $response
                    ], 200);
                    break;
                case 'conciliations':
                    $response = DashService::getConciliationsStatus();
                    if(empty($response)){
                        return response()->json([
                            "status"    => false,
                            "message"   => $this->message["error_null"],
                            "result"    => null
                        ], 200);
                    }

                    if(is_null($response)){
                        return response()->json([
                            "status"    => false,
                            "message"   => $this->message["error_null"],
                            "result"    => null
                        ], 200);
                    }

                    if($response['error'] == true){
                        return response()->json([
                            "status"    => false,
                            "message"   => $this->message["error"],
                            "result"    => null
                        ], 200);
                    }

                    return response()->json([
                        "status"    => true,
                        "message"   => $this->message["success"],
                        "result"    => $response
                    ], 200);
                    break;
                case 'transactions':
                    
                    $response = DashService::getTransactionsHistory($request->_frecuency);                    
                    if(empty($response)){
                        return response()->json([
                            "status"    => false,
                            "message"   => $this->message["error_null"],
                            "result"    => null
                        ], 200);
                    }

                    if(is_null($response)){
                        return response()->json([
                            "status"    => false,
                            "message"   => $this->message["error_null"],
                            "result"    => null
                        ], 200);
                    }

                    if($response['error'] == true){
                        return response()->json([
                            "status"    => false,
                            "message"   => $this->message["error"],
                            "result"    => null
                        ], 200);
                    }

                    return response()->json([
                        "status"    => true,
                        "message"   => $this->message["success"],
                        "result"    => $response
                    ], 200);
                    break;
                case 'keys':
                    $response = DashService::getMaintenanceKeys();
                    if(empty($response)){
                        return response()->json([
                            "status"    => false,
                            "message"   => $this->message["error_null"],
                            "result"    => null
                        ], 200);
                    }

                    if(is_null($response)){
                        return response()->json([
                            "status"    => false,
                            "message"   => $this->message["error_null"],
                            "result"    => null
                        ], 200);
                    }

                    if($response['error'] == true){
                        return response()->json([
                            "status"    => false,
                            "message"   => $this->message["error"],
                            "result"    => null
                        ], 200);
                    }

                    return response()->json([
                        "status"    => true,
                        "message"   => $this->message["success"],
                        "result"    => $response
                    ], 200);
                    break;
                case 'atmsView':
                        $response = DashService::atmsView($request->id);
                        if(empty($response)){
                            return response()->json([
                                "status"    => false,
                                "message"   => $this->message["error_null"],
                                "result"    => null
                            ], 200);
                        }
    
                        if(is_null($response)){
                            return response()->json([
                                "status"    => false,
                                "message"   => $this->message["error_null"],
                                "result"    => null
                            ], 200);
                        }
    
                        if($response['error'] == true){
                            return response()->json([
                                "status"    => false,
                                "message"   => $this->message["error"],
                                "result"    => null
                            ], 200);
                        }
    
                        return response()->json([
                            "status"    => true,
                            "message"   => $this->message["success"],
                            "result"    => $response
                        ], 200);
                        break;
                case 'show_keys':
                    $response = DashService::showKeys($request->_key_id);
                    if(empty($response)){
                        return response()->json([
                            "status"    => false,
                            "message"   => $this->message["error_null"],
                            "result"    => null
                        ], 200);
                    }

                    if(is_null($response)){
                        return response()->json([
                            "status"    => false,
                            "message"   => $this->message["error_null"],
                            "result"    => null
                        ], 200);
                    }

                    if($response['error'] == true){
                        return response()->json([
                            "status"    => false,
                            "message"   => $this->message["error"],
                            "result"    => null
                        ], 200);
                    }

                    return response()->json([
                        "status"    => true,
                        "message"   => $this->message["success"],
                        "result"    => $response
                    ], 200);
                    break;
                case 'atms_general': 
                   $response = DashService::estadoGeneral($request->_redes);

                    if(empty($response)){
                        return response()->json([
                            "status"    => false,
                            "message"   => $this->message["error_null"],
                            "result"    => null
                        ], 200);
                    }

                    if(is_null($response)){
                        return response()->json([
                            "status"    => false,
                            "message"   => $this->message["error_null"],
                            "result"    => null
                        ], 200);
                    }

                    if($response['error'] == true){
                        return response()->json([
                            "status"    => false,
                            "message"   => $this->message["error"],
                            "result"    => null
                        ], 200);
                    }

                    return response()->json([
                        "status"    => true,
                        "message"   => $this->message["success"],
                        "result"    => $response
                    ], 200);
                    break;
                case 'rollback': 

                    $response = DashService::getNotRollback();
                    if(empty($response)){
                        return response()->json([
                            "status"    => false,
                            "message"   => $this->message["error_null"],
                            "result"    => null
                        ], 200);
                    }

                    if(is_null($response)){
                        return response()->json([
                            "status"    => false,
                            "message"   => $this->message["error_null"],
                            "result"    => null
                        ], 200);
                    }

                    if($response['error'] == true){
                        return response()->json([
                            "status"    => false,
                            "message"   => $this->message["error"],
                            "result"    => null
                        ], 200);
                    }

                    return response()->json([
                        "status"    => true,
                        "message"   => $this->message["success"],
                        "result"    => $response
                    ], 200);
                    break; 

                    case 'montoCero': 
                        $response = DashService::montoCero();
                        if(empty($response)){
                            return response()->json([
                                "status"    => false,
                                "message"   => $this->message["error_null"],
                                "result"    => null
                            ], 200);
                        }
    
                        if(is_null($response)){
                            return response()->json([
                                "status"    => false,
                                "message"   => $this->message["error_null"],
                                "result"    => null
                            ], 200);
                        }
    
                        if($response['error'] == true){
                            return response()->json([
                                "status"    => false,
                                "message"   => $this->message["error"],
                                "result"    => null
                            ], 200);
                        }
    
                        return response()->json([
                            "status"    => true,
                            "message"   => $this->message["success"],
                            "result"    => $response
                        ], 200);
                        break; 
                        case 'pendiente': 
                            $response = DashService::pendiente();
                            if(empty($response)){
                                return response()->json([
                                    "status"    => false,
                                    "message"   => $this->message["error_null"],
                                    "result"    => null
                                ], 200);
                            }
        
                            if(is_null($response)){
                                return response()->json([
                                    "status"    => false,
                                    "message"   => $this->message["error_null"],
                                    "result"    => null
                                ], 200);
                            }
        
                            if($response['error'] == true){
                                return response()->json([
                                    "status"    => false,
                                    "message"   => $this->message["error"],
                                    "result"    => null
                                ], 200);
                            }
        
                            return response()->json([
                                "status"    => true,
                                "message"   => $this->message["success"],
                                "result"    => $response
                            ], 200);
                            break; 
                    case 'balance_online': 

                        $response = DashService::balanceOnline();
                        
                        if(empty($response)){
                            return response()->json([
                                "status"    => false,
                                "message"   => $this->message["error_null"],
                                "result"    => null
                            ], 200);
                        }
    
                        if(is_null($response)){
                            return response()->json([
                                "status"    => false,
                                "message"   => $this->message["error_null"],
                                "result"    => null
                            ], 200);
                        }
    
                        if($response['error'] == true){
                            return response()->json([
                                "status"    => false,
                                "message"   => $this->message["error"],
                                "result"    => null
                            ], 200);
                        }
    
                        return response()->json([
                            "status"    => true,
                            "message"   => $this->message["success"],
                            "result"    => $response
                        ], 200);
                        break;             
            }
        }catch (\Exception $e){
            \Log::error("Error en la consulta de servicio dash: " . $e->getMessage());
            return response()->json([
                "status"	=> false,
                "message"	=> $this->message["error"],
                "result"	=> null
            ], 200);
        }
    }

    public function balancesDetails(Request $request){
        if (!$this->user->hasAccess('monitoreo')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $target = 'balances';

        if($request->ajax()){
            try{
                // Columnas en el orden el cual estan en la vista 
                $columnas = [
                    'detalle',
                    'atms.code',
                    'atms.name',
                    'points_of_sale.description',
                    'owners.name',
                    'atms_parts.nombre_parte',
                    'diferencia',
                    'atms_parts.denominacion',
                    'atms_parts.cantidad_actual',
                ];

                $atms = \DB::table('atms')
                    ->select('atms.id','atms.code as atm_code','points_of_sale.description as pdv','owners.name as red', 'atms.name as atm_name')
                    ->join('points_of_sale','points_of_sale.atm_id','=','atms.id')
                    ->join('owners','owners.id','=','atms.owner_id')
                    ->whereIn('atms.id', function($query){
                        $query->select('atms_parts.atm_id')
                            ->from('atms_parts')
                            ->where('atms_parts.cantidad_alarma','>','0')
                            ->whereRaw('atms_parts.cantidad_alarma >= atms_parts.cantidad_actual');
                    })
                    ->where('atms.deleted_at',null)
                    ->where('atms.type', 'at')
                    ->where('atms.owner_id', '!=', 18);

                $cantidad = $atms->count();
                if(!empty($request->search)){
                    $buscar = $request->search['value'];
                    $atms->where(function($q) use($columnas, $buscar){
                        foreach($columnas as $columnKey => $column){
                            if(in_array($column, ['atms.code','atms.name','points_of_sale.description','owners.name'])){
                                $q->orWhere($column,'ilike','%'.$buscar.'%');
                            }
                        }
                    });
                }

                $cantidadFiltrada = $atms->count();

                foreach($request->order as $key => $order){
                    if($order['column'] == 2){
                        $atms = $atms->orderBy($columnas[$order['column']], $order['dir']);
                    }
                }

                $atms = $atms->offset($request->start)
                    ->limit($request->length)
                    ->get();

                $results['data'] = [];
                foreach($atms as $atmKey => $atm){
                    $results['data'][$atmKey]['atm_code'] = $atm->atm_code;
                    $results['data'][$atmKey]['atm_name'] = $atm->atm_name;
                    $results['data'][$atmKey]['pdv'] = $atm->pdv;
                    $results['data'][$atmKey]['red'] = $atm->red;
                    $results['data'][$atmKey]['nombre_parte'] = '';
                    $results['data'][$atmKey]['diferencia'] = '';
                    $results['data'][$atmKey]['DT_RowId'] = $atm->atm_code;

                    $balances = \DB::table('atms_parts')
                        ->select('*',\DB::raw('cantidad_actual-cantidad_minima as diferencia'))
                        ->where('atms_parts.atm_id','=',$atm->id)
                        ->where('cantidad_alarma','>','0')
                        ->whereRaw('cantidad_alarma >= cantidad_actual');

                    foreach($request->order as $key => $order){
                        if(in_array($order['column'], [5,6])){ //El array en el cual se busca, es el array que contiene el numero de la posicion de la columna en la vista
                            $balances = $balances->orderBy($columnas[$order['column']], $order['dir']);
                        }
                    }

                    $balances = $balances->get();
                    $results['data'][$atmKey]['detalle'] = [];
                    foreach ($balances as $balanceKey => $balance){
                        $results['data'][$atmKey]['detalle'][$balanceKey]['nombre_parte'] = $balance->nombre_parte;
                        $results['data'][$atmKey]['detalle'][$balanceKey]['denominacion'] = $balance->denominacion;
                        $results['data'][$atmKey]['detalle'][$balanceKey]['cantidad'] = $balance->cantidad_minima.'/'.$balance->cantidad_actual;
                        $results['data'][$atmKey]['detalle'][$balanceKey]['status'] = 'info';
                        $results['data'][$atmKey]['detalle'][$balanceKey]['label'] = '<span class="pull-right badge bg-green"><i class="fa fa-check"></i></span>';
                        
                        if($balance->cantidad_actual <= $balance->cantidad_minima){
                            $results['data'][$atmKey]['detalle'][$balanceKey]['status'] = 'red';
                            $results['data'][$atmKey]['detalle'][$balanceKey]['label'] = '<span class="pull-right badge bg-red"><i class="fa fa-times-circle"></i> Cantidad mínima alcanzada</span>';
                        }

                        if($balance->cantidad_actual > $balance->cantidad_minima && $balance->cantidad_actual <= $balance->cantidad_alarma){
                            $results['data'][$atmKey]['detalle'][$balanceKey]['status'] = 'yellow';
                            $results['data'][$atmKey]['detalle'][$balanceKey]['label'] = '<span class="pull-right badge bg-yellow"><i class="fa fa-exclamation-triangle"></i> Cantidad actual cercana al mínimo</span>';
                        }
                    }
                }

                $results['draw'] = $request->draw;
                $results['recordsTotal'] = $cantidad;
                $results['recordsFiltered'] = $cantidadFiltrada;

                return json_encode($results);
            }catch (\Exception $e){
                \Log::error("Error en la consulta de servicio dash / Balances: " . $e->getMessage());
            }
        }

        return view('dashboard.index', compact('target'));
    }
    
    
    public function getNotificationsbyUsers(){
        try{
            if (!$this->user->hasAccess('monitoreo')) {
                \Log::error('Unauthorized access attempt',
                    ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
                Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
                return redirect('/');
            }

            if($this->user->hasAccess('reporting.all')){
                $response = \DB::table('notifications')
                    ->select('notifications.id as id','code','description','notification_types.id as type_id')
                    ->join('notification_types','notification_types.id','=','notifications.notification_type')
                    ->join('atms','atms.id','=','notifications.atm_id')
                    ->where('processed',false)
                    ->orderby('notification_type','DESC')
                    ->get();
            }else{
                $response = \DB::table('notifications')
                    ->select('notifications.id as id','code','description','notification_types.id as type_id')
                    ->join('notification_types','notification_types.id','=','notifications.notification_type')
                    ->join('atms','atms.id','=','notifications.atm_id')
                    ->where('processed',false)
                    ->where('asigned_to',$this->user->id)
                    ->orderby('notification_type','DESC')
                    ->get();
            }



            return response()->json([
                "status"    => true,
                "count"     => count($response),
                "message"   => $this->message["success"],
                "result"    => $response
            ], 200);

        }catch (\Exception $e){
            \Log::error("Error en la consulta de servicio notificaciones: " . $e->getMessage());
            return response()->json([
                "status"	=> false,
                "message"	=> $this->message["error"],
                "result"	=> null
            ], 200);
        }
    }

    public function conciliationsDetails(){
        if (!$this->user->hasAccess('monitoreo')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        try{
            $begin = new Carbon('first day of this month 00:00:00');
            $end   = new Carbon('last day of this month 23:59:59');

            $invoices = \DB::table('invoices')
                ->select('invoices.*','transactions.amount as amount','atms.code as atm_code','service_provider_products.description as service_description')
                //->whereBetween('invoices.created_at',[$begin,$end])
                ->whereIn('status_code',['0','1','5','212'])
                ->join('transactions','transactions.id','=','invoices.transaction_id')
                ->join('atms','atms.id','=','transactions.atm_id')
                ->leftjoin('service_provider_products', 'service_provider_products.id','=','transactions.service_id')
                ->get();

            foreach ($invoices as $invoice){
               if($invoice->response <> null){
                   $message = json_decode($invoice->response);
                   $invoice->response = $message->message;
               }
            }

            $incomes = \DB::table('incomes')
                ->select('incomes.*','atms.name','transactions.amount as amount','transactions.service_id','transactions.service_source_id','atms.code as atm_code','service_provider_products.description as service_description')
                //->whereBetween('incomes.created_at',[$begin,$end])
                ->whereIn('destination_operation_id',['0','1','5','212'])
                ->join('transactions','transactions.id','=','transaction_id')
                ->join('atms','atms.id','=','transactions.atm_id')
                ->leftjoin('service_provider_products', 'service_provider_products.id','=','transactions.service_id')
                ->orderBy('atms.name','asc')
                ->orderBy('incomes.created_at','asc')
                ->get();
            foreach ($incomes as $income){
                if($income->service_source_id <> 0){
                    // transaccion de NETEL
                    $service_sources_data = \DB::table('services_ondanet_pairing')
                        ->where('service_request_id', $income->service_id)
                        ->where('service_source_id', $income->service_source_id)
                        ->first();

                    $service_source_description = \DB::table('services_providers_sources')->where('id',$income->service_source_id)->first();
                    $service_description = '';
                    if($service_sources_data){
                        $service_description  .= $service_source_description->description . ' | '. $service_sources_data->service_description;
                    }

                    $income->service_description = $service_description;
                }

                if($income->response <> null){
                    $message = json_decode($income->response);
                    $income->response = $message->description;
                }
            }

        }catch (\Exception $e){
            \Log::error("Error en la consulta de servicio dash / Conciliaciones: " . $e);
        }
        $target = 'conciliations';
        return view('dashboard.index', compact('target','incomes','invoices'));
    }

    public function getAtmDetalles(Request $request){
        $input = \Request::all();

        if($request->ajax()){
            $result = DashService::atmsDetalle($input);
            return $result;
        }
    }

    public function getDetallesCantidades($atm_id){
        $input = \Request::all();
        if(\Request::ajax()){
            $result = DashService::getDetallesCantidades($atm_id);
            return $result;
        }
    }
}
