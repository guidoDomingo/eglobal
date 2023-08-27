<?php

namespace App\Http\Controllers\atm_baja;

use Excel;

use Session;
use Carbon\Carbon;
use App\Models\Group;
use App\Models\Multa;
use App\Http\Requests;
use App\Models\Atmnew;
use App\Models\Alquiler;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\AlquilerRequest;
use App\Services\ExtractosServices;

class MultaController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function index($groupId, Request $request)
    {
        if (!$this->user->hasAccess('atms.group.penalizacion')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $grupo = Group::find($groupId);

        $atms = \DB::table('business_groups as bg')
            ->select('ps.atm_id as atm_id')
            ->join('branches as b', 'b.group_id', '=', 'bg.id')
            ->join('points_of_sale as ps', 'ps.branch_id', '=', 'b.id')
            ->join('atms as a', 'a.id', '=', 'ps.atm_id')
            ->where('bg.id', $groupId)
            ->whereNull('a.deleted_at')
            ->whereNull('bg.deleted_at')
            ->get();

        $atm_ids = array();
        foreach ($atms as $item) {
            $id = $item->atm_id;
            array_push($atm_ids, $id);
        }
        $atm_list =  Atmnew::findMany($atm_ids);

        $multas  = Multa::all();

        return view('atm_baja.penalizacion', compact('multas', 'groupId', 'grupo', 'atm_list'));
    }

    public function create($groupId, Request $request)
    {
        if (!$this->user->hasAccess('atms.group.penalizacion.add')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $grupo      = Group::find($groupId);
        // $atm_ids    = $request->get('atm_list');

        //Se lista todos los atms del grupo
        $atms = \DB::table('business_groups as bg')
            ->select('a.id', 'a.name')
            ->join('branches as b', 'b.group_id', '=', 'bg.id')
            ->join('points_of_sale as ps', 'ps.branch_id', '=', 'b.id')
            ->join('atms as a', 'a.id', '=', 'ps.atm_id')
            ->where('bg.id', $groupId)
        ->get();

        $data_select = [];
        $data_select[0] = 'Selecciona una opción';
        foreach ($atms as $key => $atm) {
            $data_select[$atm->id] = $atm->name ;
        }

        //Se selecciona el atm unico en caso que el grupo solo tenga 1 atm
        $atm_id = (count($atms) == 1) ? $atms[0]->id : 0;

        //$atm_list   = Atmnew::findMany($atm_ids);

        //$multas     =  Multa::where(['group_id' => $groupId])->get();

        //Se trae un reporte de las multas 
        $multas = \DB::table('mt_penaltys as mp')
            ->select('mp.id', 'mp.observation', 'mp.created_at as creado', 'mp.amount_total_to_pay as total_multa', 'ms.nro_venta', 'm.destination_operation_id as status', 'atms.name as atm', 'mpt.description as tipo_multa')
            ->join('mt_sales as ms', 'ms.id', '=', 'mp.sale_id')
            ->join('mt_movements as m', 'm.id', '=', 'ms.movements_id')
            ->join('mt_penalty_type as mpt', 'mpt.id', '=', 'mp.penalty_type_id')
            ->join('atms', 'atms.id', '=', 'm.atm_id')
            ->where('m.group_id', $groupId)
            ->whereNull('mp.deleted_at')
            ->orderBy('mp.id', 'asc')
        ->get();

        //dd($multas);

        /*$numero     = $multas->count() + 1;

        $last_multa = \DB::table('multas_alquiler')->orderBy('id', 'desc')->first();
        if (isset($last_multa)) {
            $multa_id = $last_multa->id + 1;
        } else {
            $multa_id = 1;
        }
        $idtransaccion       = $multa_id;*/

        //Lista de los tipos de multa
        $penalty_types = \DB::table('mt_penalty_type')->orderBy('id', 'asc')->get();
        
        $service = new ExtractosServices('');

        //Se trae el balance del grupo
        $balance = $service->getBalanceCierre($groupId);

        //El saldo total del cliente
        $saldo_cliente = $balance['total_saldo'];

        //Se trae la diferencia de meses para saber cuanto debe cobrarle
        $last_sale = $this->last_sale($groupId);

        $ar_porcentaje = array(2, 3); //Los que tienen que considerarse como porcentaje
        $debit_fijo = array(5, 6); //Los que tienen monto fijo

        foreach($penalty_types as $penalty_type){
            
            $penalty_type->amount_original = $penalty_type->amount_to_affected;
            $penalty_type->punitorio = 30;
            $penalty_type->amount_punitorio = 0;

            //Si el tipo de multa es con porcentaje
            if(in_array($penalty_type->id, $ar_porcentaje)){
                $penalty_type->amount_original = $penalty_type->amount_to_affected;
                $penalty_type->amount_to_affected = $penalty_type->amount_to_affected * $last_sale;
            }

            if($penalty_type->percent_amount == 'pe'){ //Si es porcentaje se redondea hacia abajo
                $penalty_type->amount_penalty = round((($saldo_cliente * $penalty_type->amount_to_affected)/100), 0, PHP_ROUND_HALF_DOWN);
            }else{
                $penalty_type->amount_penalty = $penalty_type->amount_to_affected;
            }

            $penalty_type->total_to_pay = $penalty_type->amount_penalty;

            if($penalty_type->id == 3){ //Si es tipo de multa 3 se redondea
                $interes_punitorio = round((($penalty_type->punitorio * $penalty_type->amount_penalty)/100), 0, PHP_ROUND_HALF_DOWN);
                $penalty_type->amount_punitorio = $penalty_type->amount_penalty + $interes_punitorio;
                $penalty_type->total_to_pay = $penalty_type->amount_punitorio;
            }
            
        }
        
        //return view('atm_baja.penalizacion', compact('groupId', 'grupo', 'atm_list', 'atm_ids', 'numero', 'idtransaccion', 'multas'));
        return view('atm_baja.penalizacion', compact('groupId', 'grupo', 'data_select', 'atm_id', 'multas', 'saldo_cliente', 'penalty_types', 'last_sale', 'ar_porcentaje', 'debit_fijo'));
    }

    public function store(Request $request)
    {
        //\DB::beginTransaction(); //VER PARA DESCOMENTAR EL COMMIT DENTRO DEL STORE Y NO DEL SEND
        $input      = $request->all();

        try {

            //Se trae un array de las facturas que se quiere generar
            $facturas=json_decode($input['cadena'], true);

            foreach($facturas as $factura){
                //dd(json_encode($factura['detail_penalty']));

                $group_id = $factura['group_id'];
                $atm_id = $factura['atm_id'];

                //Se trae todos los balances
                $last_balance = \DB::table('mt_movements')->where('atm_id',$factura['atm_id'])->orderBy('id','desc')->first();
                if(isset($last_balance)){
                    $balance= $last_balance->balance +(int)$factura['amount_total_to_pay'];
                    $balance_antes= $last_balance->balance;
                }else{
                    $balance= (int)$factura['amount_total_to_pay'];
                    $balance_antes=0;
                }

                //Se inserta en mt_movements
                $movement_id=\DB::table('mt_movements')->insertGetId([
                    'movement_type_id'          => 18,
                    'destination_operation_id'  => 0,
                    'amount'                    => $factura['amount_total_to_pay'],
                    'debit_credit'              =>  'de',
                    'created_at'                => Carbon::now(),
                    'updated_at'                => Carbon::now(),
                    'group_id'                  => $group_id,
                    'atm_id'                    => $factura['atm_id'],
                    'balance_antes'             => $balance_antes,
                    'balance'                   => $balance

                ]);

                //Se inserta en mt_sales
                $sale_id=\DB::table('mt_sales')->insertGetId([
                    'movements_id'       => $movement_id,
                    'estado'            => 'pendiente',  
                    'monto_por_cobrar'  => (int)$factura['amount_total_to_pay'],
                    'fecha'             => Carbon::today(),
                    'fecha_vencimiento' => Carbon::today()
                ]);

                /*$multa = new Multa();
                $multa->alquiler_id                 = 0;
                $multa->destination_operation_id    = NULL;
                $multa->response                    = NULL;
                $multa->group_id                    = $input['group_id'];
                $multa->importe                     = str_replace('.', '', $input['importe']);
                $multa->saldo                       = str_replace('.', '', $input['importe']);
                $multa->num_venta                   = NULL;
                $multa->fecha_vencimiento           = $input['fecha_vencimiento'];
                $multa->created_at                  = Carbon::now();
                $multa->created_by                  = $this->user->id;
                $multa->updated_at                  = Carbon::now();

                if ($multa->save()) {*/

                //Se inserta la multa
                $penalty_id=\DB::table('mt_penaltys')->insertGetId([
                    'sale_id'                   => $sale_id,
                    'penalty_type_id'           => $factura['penalty_type_id'],  
                    'observation'               => $factura['observacion'],
                    'created_by'                => $this->user->id,
                    'created_at'                => Carbon::now(),
                    'updated_at'                => Carbon::now(),
                    'amount_balance_pending'    => $factura['saldo_cliente'],
                    'amount_penalty'            => $factura['amount_penalty'],
                    'amount_discount'           => $factura['descuento'],
                    'amount_total_to_pay'       => $factura['amount_total_to_pay'],
                    'details'                   => json_encode($factura['detail_penalty']),
                ]);

                \Log::info('[Penaltys] Se procede a afectar el atm ' . $atm_id);

                //Se inserta el balance
                $balance = \DB::table('balance_atms')->where('atm_id', $atm_id)->first();

                if (isset($balance)) {
                    \Log::info('[Penaltys] Monto a afectar del atm_id ' . $atm_id . ': ' . $factura['amount_total_to_pay']);
    
                    $multa = $balance->total_multa + $factura['amount_total_to_pay'];
    
                    \DB::table('balance_atms')
                        ->where('atm_id', $atm_id)
                        ->update([
                            'total_multa'  => $multa
                        ]);
                } else {
                    \Log::info('[Penaltys] Se procede a crear el primer total transaccionado del atm_id' . $atm_id);
    
                    $multa = $factura['amount_total_to_pay'];
    
                    \DB::table('balance_atms')->insert([
                        'atm_id'                => $atm_id,
                        'total_multa'  => (int)$multa
                    ]);
                }

                //\DB::commit();
                /*datos para ondanet*/
                $group_id       = $group_id;
                $fecha          = date("d/m/Y", strtotime(Carbon::now()));
                $importe        = $factura['amount_total_to_pay'];
                $housing_id     = null;
                $num_cuota      = null;
                $idtransaccion  = $penalty_id;
                $penalty_type   = \DB::table('mt_penalty_type')->where('id',$factura['penalty_type_id'])->first();
                $idproducto     = $penalty_type->cod_product;
                $product_description = $penalty_type->description;

                $response = $this->sendMulta($group_id, $fecha, $importe, $housing_id, $num_cuota, $idtransaccion, $idproducto, $product_description);
                \Log::info('REsponse');
                \Log::info($response);
                if ($response['error'] == false) {

                    $status = $response['status'];
                    $numventa = $response['numero_venta'];
                    $cuotas = $response['cuotas'];
                    $data = [
                        'status' => $status,
                        'numventa' => $numventa,
                        'cuotas' => $cuotas,
                    ];

                    \Log::info("[ondanet] Exporting miniterminales sales claro to ondanet ",['result' => $response['error'], 'ondanet_rowId' => $response['status'],'Numero de Venta' => $numventa]);

                    \DB::table('mt_movements')
                        ->where('id', $movement_id)
                        ->update([
                            'destination_operation_id' => $response['status'],
                            'response' => json_encode($response),
                            'updated_at' => Carbon::now()
                    ]);

                    \DB::table('mt_sales')
                        ->where('id', $sale_id)
                        ->update([
                            'nro_venta' => $numventa
                    ]);

                    /*$saveondanet = Multa::find($multa->id);
                    $saveondanet->fill([
                        'destination_operation_id'  => $response['status'],
                        'num_venta'                 => strval($response['numero_venta'])
                    ]);
                    $saveondanet->save();*/
                     
                } else {

                    \DB::table('mt_movements')
                        ->where('id', $movement_id)
                        ->update([
                            'destination_operation_id' => 1,
                            'response' => json_encode($response),
                            'updated_at' => Carbon::now()
                    ]);

                    //\DB::rollback();
                    
                }

                /*} else {
                    //\DB::rollback();
                    // Session::flash('error_message', 'Ocurrio un error al intentar guardar el registro');
                    return redirect()->to('atm/new/' . $group_id . '/' . $group_id . '/penalizacion')->with('error', 'ok');
                }*/

            }

            return redirect()->to('atm/new/' . $group_id . '/' . $group_id . '/penalizacion')->with('guardar', 'ok');
            
        } catch (\Exception $e) {
           // \DB::rollback();
            \Log::error("Error saving new Multa - {$e->getMessage()}");
            Session::flash('error_message', 'Ocurrio un error al intentar guardar el registro :(');
            return redirect()->back()->withInput();
        }
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }

    public function destroy($id)
    {
        //
    }


    /**
     * ALQUILER: Método [dbo].[P_FACTURA_ALQUILER]
     *
     * PARAMETROS
     * @PDV VARCHAR(20) -> El código cliente que se encuentra creado tanto en el ADMIN como en ONDANET para 
     *                      que salga a su nombre la factura y afecte extracto en ONDANET, si no se encuentra creado en ONDANET 
     *                      retornará un mensaje de error (-26)
     * 
     * @FECHA1 VARCHAR(10) -> Para la primera factura ingresada de un cliente sería la fecha de la primera 
     *                          transacción que genera el billetero, para los próximos meses sería la fecha 
     *                          del mes actual. (1 mes después de la factura anterior), la fecha de 
     *                          vencimiento por cada factura generada = @FECHA1
     * 
     * @IDPRODUCTO1 VARCHAR(25) -> El código del producto que se detallará en la factura, se crearon los 
     *                              siguientes códigos para utilizar: ALQMES1, ALQMES2, ALQMES3, ALQMES4, 
     *                              ALQMES5, ALQMES6, ALQMES7, ALQMES8, ALQMES9, ALQMES10, ALQMES11, ALQMES12
     * 
     * @IMPORTE NUMERIC(18,5) -> El importe del alquiler por cada mes.
     * 
     * @IDDTRANSACCION VARCHAR(100) -> El ID de Transacción Único por cada movimiento (proveído del ADMIN EGLOBAL, 
     *                                  para evitar que se duplique en ONDANET las ventas.
     * 
     * @IMEI VARCHAR(100) -> El serial de la MINITERMINAL
     */

    public function sendMulta($group_id, $fecha, $monto, $housing_id, $num_cuota, $idtransaccion, $idproducto, $product_description)
    {

        $class = __CLASS__;
        $function = __FUNCTION__;

        $parameters = [
            'group_id' => $group_id,
            'fecha' => $fecha,
            'monto' => $monto,
            'housing_id' => $housing_id,
            'num_cuota' => $num_cuota,
            'idtransaccion' => $idtransaccion,
            'idproducto' => $idproducto
        ];

        $inputs = json_encode($parameters);
        \Log::info("\n\nCampos obtenidos en $class \ $function:\n\n$inputs\n\n");

        try {

            \DB::beginTransaction();

            $grupo = \DB::table('business_groups')->where('id', $group_id)->first();
            $pdv = $grupo->ruc;
            $importe = $monto;
            $idproducto1 = $idproducto;
            $imei = '';
            $idtransaccion = $idtransaccion;
            //$idtransaccion  =  2323270;

            $query = "
                SET NOCOUNT ON;
                SET ANSI_WARNINGS OFF;
                DECLARE @rv Numeric(25)
                DECLARE @FECHA DATE
                SET @FECHA = (SELECT CONVERT(DATE, '$fecha', 103))
                EXEC @rv = [DBO].[P_FACTURA_ALQUILER] '$pdv', @FECHA, '$idproducto1','$importe', '$idtransaccion', '$imei'
                SELECT @rv
            ";

            \Log::info("\n\n[Baja - generar multa] Prodecimiento a ejecutar en Ondanet: \n\n$query\n\n");

            //Auditoria, insertar request a ondanet

            $atm_inactivate_history_insert = [
                'atm_id' => NULL,
                'group_id' => $group_id,
                'operation' => "FACTURA $product_description - REQUEST",
                'data' => $query,
                'created_at' => Carbon::now(),
                'created_by' => $this->user->id,
                'updated_at' => NULL,
                'updated_by' => NULL,
                'deleted_at' => NULL,
                'deleted_by' => NULL
            ];

            $factura_request = \DB::table('atm_inactivate_history')->insert($atm_inactivate_history_insert);

            \Log::info("\n\nInsert en atm_inactivate_history:\n\n", $atm_inactivate_history_insert);

            $results = $this->get_one($query);

            \Log::info("\n\n[Baja - generar multa] Respuesta de Ondanet:\n\n", ['response' => $results]);

            $factura_response_insert = [
                'atm_id' => NULL,
                'group_id' => $group_id,
                'operation' => "FACTURA $product_description - RESPONSE",
                'data' => json_encode($results),
                'created_at' => Carbon::now(),
                'created_by' => $this->user->id,
                'updated_at' => NULL,
                'updated_by' => NULL,
                'deleted_at' => NULL,
                'deleted_by' => NULL
            ];

            //Auditoria, insertar respuesta de ondanet
            $factura_response = \DB::table('atm_inactivate_history')->insert($factura_response_insert);

            \Log::info("\n\n[Baja - generar multa] factura_response_insert:\n\n", $factura_response_insert);

            \DB::commit();

            $tamano = sizeof($results);

            if ($tamano == 1) {

                $response = $results[0];
                $result = '';
                $result_aux = '';

                foreach ($response as $key => $value) {
                    $result_aux = $value;
                }

                foreach ($result_aux as $key1 => $value2) {
                    $result = $value2;
                }

            } else {

                $response = $results[2];
                $result = '';

                foreach ($response as $key => $value) {
                    $result = $value;
                }
            }

            //se configura un array con los estados de errores conocidos
            $errors = array(
                "-6 | Vendedor no existe en ONDANET" => "-6",
                "-7 | El producto solo pueden ser los siguientes: (ALQMES1, ALQMES2, ALQMES3, ALQMES4, ALQMES5, ALQMES6, ALQMES7, ALQMES8, ALQMES9, ALQMES10, ALQMES11, ALQMES12)" => "-7",
                "-10 | El importe no puede ser <= 0" => "-10",
                "-14 | Nro. de venta ya existe con el mismo IDTRANSACCION" => "-14",
                "-19 | Proceso no identificado." => "-19",
                "-24 | FECHA VENTA NO DEFINIDA" => "-24",
                "-26 | No se encuentra creado el cliente en ONDANET" => "-26",
                "212 | otros errores no definidos en el procedimiento"  => "212",
            );

            $check = array_search($result, $errors);

            if (!$check) {

                $cuotas = $results[0];
                $numventa = $results[1][0]['NUMVENTA'];
                $response_id = $results[2][0]['']; //PRODUCCION VACIO
                $data['cuotas'] = $cuotas;
                $data['error'] = false;
                $data['status'] = $response_id;
                $data['numero_venta'] = $numventa;

                //return $data;
            } else {
                $message = explode("|", $check);
                $data['error'] = true;
                $data['status'] = $check;
                $data['code'] = $message[0];

                //return $data;
            }

            $response = $data;

            return $response;

        } catch (\Exception $e) {

            \DB::rollback();

            $response['error'] = true;
            $response['status'] = '212';
            $response['code'] = '212';

            $error_detail = [
                'from' => 'CMS',
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => $class,
                'function' => $function,
                'line' => $e->getLine()
            ];

            \Log::error("[Baja - generar multa] ERROR: " . json_encode($error_detail));
        }

        $response_aux = json_encode($response);

        \Log::info("\n\nRespuesta de $class \ $function:\n\n$response_aux\n\n");

        return $response;
    }

    /** FUNCIONES PRIVADAS COMUNES*/

    public function get_one($query)
    {
        try {
            \DB::beginTransaction();
            $db     = \DB::connection('ondanet')->getPdo();
            $stmt   = $db->prepare($query);
            $stmt->execute();

            $register = array();
            $i = 0;
            do {
                $register[$i] = $stmt->fetchAll(\PDO::FETCH_ASSOC);
                if (!empty($register[$i])) {
                    $i++;
                }
            } while ($stmt->nextRowset());
            \DB::commit();
            return $register;
        } catch (\PDOException $e) {
            \DB::rollback();
            return $e;
        }
    }

    public function add_penalty(){

        $penalty_types = \DB::table('mt_penalty_type')
            ->select('id', 'description')
            ->orderBy('id', 'ASC')
        ->get();

        $details['payment_info']='';
        
        $details['payment_info'] .="<select class='form-control select2' id='penalty_select'>";
        $details['payment_info'] .="<option value=0 selected>Seleccionar una opción</option>";
        foreach ($penalty_types as $penalty_type) {
            $details['payment_info'] .=
            "<option value=".$penalty_type->id.">".$penalty_type->description.'</option>';
        }
        $details['payment_info'] .='</select>';
        \Log::info($details);
        return $details;
    }

    public function last_sale($group_id){

        $today = Carbon::today();

        $last_sale = \DB::table('mt_movements as m')
            ->selectRaw('m.*')
            ->join('mt_sales as ms', 'm.id', '=', 'ms.movements_id')
            ->where('ms.estado', 'pendiente')
            ->where('m.group_id', $group_id)
            ->whereNull('m.deleted_at')
            ->orderBy('m.id', 'ASC')
        ->first();

        $today = new \DateTime($today);
        $diff_sales = 1;

        if(!empty($last_sale)){
            $last_date_sale = new \DateTime($last_sale->created_at); 
            $months_sales = $today->diff($last_date_sale); 
            $diff_sales = $months_sales->m;
        }

        $last_cuota = \DB::table('cuotas_alquiler as ca')
            ->selectRaw('ca.*')
            ->join('alquiler as a', 'a.id', '=', 'ca.alquiler_id')
            ->where('a.group_id', $group_id)
            ->whereNull('a.deleted_at')
            ->whereNotNull('ca.num_venta')
            ->where('ca.saldo_cuota', '<>',0)
            ->orderBy('ca.fecha_vencimiento', 'ASC')
        ->first();

        $diff_cuotas = 1;

        if(!empty($last_cuota)){
            $last_date_cuota = new \DateTime($last_cuota->fecha_vencimiento); 
            $months_cuotas = $today->diff($last_date_cuota); 
            $diff_cuotas = $months_cuotas->m;
        }

        $diff_month_sale = ($diff_cuotas >= $diff_sales) ? $diff_cuotas : $diff_sales;

        //$prueba = Carbon::parse(date('2022-10-01 00:00:00'));
        //$prueba = new \DateTime($prueba);  
        //$months_prueba = $prueba->diff($today); 

        return $diff_month_sale;
    }
}
