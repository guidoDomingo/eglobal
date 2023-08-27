<?php

namespace App\Http\Controllers\atm_baja;

use Session;

use Carbon\Carbon;
use App\Models\Atm;
use App\Models\Group;
use App\Models\Atmnew;
use App\Models\Pagare;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\InactivateHistory;
use App\Models\Multa;
use App\Models\Presupuesto;

class PresupuestoController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function index( $group_id, Request $request)
    {
        if (!$this->user->hasAccess('atms.group.presupuesto')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $grupo = Group::find($group_id);
        $atms = \DB::table('business_groups as bg')
        ->select('ps.atm_id as atm_id')
        ->join('branches as b','b.group_id','=','bg.id')
        ->join('points_of_sale as ps','ps.branch_id','=','b.id')
        ->join('atms as a','a.id', '=','ps.atm_id')
        ->where('bg.id',$group_id)
        ->whereNull('a.deleted_at')
        ->whereNull('bg.deleted_at')
        ->get();
        $atm_ids = array();
            foreach($atms as $item){
                $id = $item->atm_id;
                array_push($atm_ids, $id);
            }
        $atm_list  =  Atmnew::findMany($atm_ids);

        $presupuestos =  Presupuesto::where('group_id', $group_id)->get();

        return view('atm_baja.presupuestos.index', compact('group_id','atm_list','grupo','presupuestos','atm_ids'));
    }

    public function create(Request $request)
    {
        if (!$this->user->hasAccess('atms.group.presupuesto.add')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $atm_ids    = $request->get('atm_list');
        $group_id   = $request->get('group_id');
        $grupo      = Group::find($group_id);
        $atm_list   = Atmnew::findMany($atm_ids);
        $notas =  Presupuesto::where(['group_id'=> $group_id])->get();
        $numero  = $notas->count()+1;

        $last_multa = \DB::table('multas_alquiler')->orderBy('id', 'desc')->first();
        if (isset($last_multa)) {
            $multa_id = $last_multa->id + 1;
        } else {
            $multa_id = 1;
        }
        $idtransaccion       = $multa_id;

        //listar la factura generada
        $multas = Multa::where('group_id',$group_id)->whereNull('deleted_at')->get();
        if (empty($multas)) {
            $multas = null;
        }

        return view('atm_baja.presupuestos.create',compact('atm_list','atm_ids','group_id','grupo','numero','idtransaccion','multas'));
    }

    public function store(Request $request)
    {
        if (!$this->user->hasAccess('atms.group.presupuesto.add')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
       // \DB::beginTransaction();
        
        $input          = $request->all();
        $group_id       = $input['group_id'];
  
        try{
            //creacion del presupuesto
            $presupuesto = new Presupuesto();
            $presupuesto->group_id       = $input['group_id'];
            $presupuesto->numero         = $input['numero'];
            $presupuesto->concepto       = $input['concepto'];
            $presupuesto->fecha          = Carbon::createFromFormat('d/m/Y', $input['fecha'])->toDateString();
            $presupuesto->monto          = str_replace('.', '', $input['monto']);
            $presupuesto->comentario     = $input['comentario'];
            $presupuesto->request_data   = NULL;
            $presupuesto->status_ondanet = NULL;
            $presupuesto->num_venta      = NULL;
            $presupuesto->created_at     = Carbon::now();
            $presupuesto->created_by     = $this->user->id;;
            $presupuesto->updated_at     = Carbon::now();
            $presupuesto->deleted_at     = NULL;
         
            if ($presupuesto->save()) {
     
                //Auditoria
                $history = new InactivateHistory();
                $history->group_id    = $presupuesto->group_id;
                $history->operation   = 'PRESUPUESTO - INSERT';
                $history->data        = json_encode($presupuesto);
                $history->created_at  = Carbon::now();
                $history->created_by  = $this->user->id;
                $history->updated_at  = NULL;
                $history->updated_by  = NULL;
                $history->deleted_at  = NULL;
                $history->deleted_by  = NULL;
                $history->save();


                 /*datos para ondanet*/ 
                $presupuesto_id = $presupuesto->id;
                $group_id       = $presupuesto->group_id;
                $fecha          = date("d/m/Y", strtotime($presupuesto->fecha));
                $importe        = $presupuesto->monto;
                $housing_id     = null;
                $num_cuota      = null;
                $idtransaccion  = $input['idtransaccion'];
                $idproducto     = $input['concepto'];

                $response       = $this->sendMulta($group_id, $fecha, $importe, $housing_id, $num_cuota, $idtransaccion, $idproducto, $presupuesto_id);

               // $response['error'] = false;

                if ($response['error'] == false) {

                    // $saveondanet = Multa::find($multa->id);
                    // $saveondanet->fill([
                    //     'destination_operation_id'  => $response['status'],
                    //     'num_venta'                 => $response['numero_venta'],
                    //     'response'                  => json_encode($response)
                    // ]);
                    // $saveondanet->save();

                    $presupuesto_ondanet = Presupuesto::find($presupuesto_id);
                    $presupuesto_ondanet->fill([
                        'status_ondanet'            => $response['status'],
                        'num_venta'                 => $response['numero_venta'],
                    ]);
                    $presupuesto_ondanet->save();
                   
                 //  \DB::commit();
                   return redirect()->to('atm/new/'.$group_id.'/'.$group_id.'/presupuesto')->with('guardar','ok');
                } else {


                    $presupuesto_ondanet = Presupuesto::find($presupuesto_id);
                    $presupuesto_ondanet->fill([
                        'status_ondanet'            => $response['code'],
                        'num_venta'                 => NULL,
                    ]);
                    $presupuesto_ondanet->save();

                    //\DB::rollback();
                    return redirect()->to('atm/new/'.$group_id.'/'.$group_id.'/presupuesto')->with('error_ondanet','ok')->with($response);
                }
            }else {
                //\DB::rollback();
                // Session::flash('error_message', 'Ocurrio un error al intentar guardar el registro');
                return redirect()->to('atm/new/'.$group_id.'/'.$group_id.'/presupuesto')->with('error','ok');
            }
             
            
        }catch (\Exception $e){
           // \DB::rollback();
            \Log::critical($e->getMessage());
            Session::flash('error_message', 'Error al registrar el Presupuesto');
            return redirect()->back()->withInput();

        }
    }

    public function show($id)
    {
        //
    }
   
    public function edit($id, Request $request)
    {
        if (!$this->user->hasAccess('atms.group.presupuesto.edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $input      = $request->all();

        if($presupuesto = Presupuesto::find($id))
        {
            $presupuesto->fecha  = date("d/m/Y", strtotime($presupuesto->fecha));
            $grupo         = Group::find($presupuesto->group_id);
            $atms = \DB::table('business_groups as bg')
                ->select('ps.atm_id as atm_id')
                ->join('branches as b','b.group_id','=','bg.id')
                ->join('points_of_sale as ps','ps.branch_id','=','b.id')
                ->join('atms as a','a.id', '=','ps.atm_id')
                ->where('bg.id',$presupuesto->group_id)
                ->whereNull('a.deleted_at')
                ->whereNull('bg.deleted_at')
                ->get();
    
            $atm_ids = array();
            foreach($atms as $item){
                $id = $item->atm_id;
                array_push($atm_ids, $id);
            }
            $atm_list   =  Atmnew::findMany($atm_ids);

            $data = [
                'presupuesto' => $presupuesto,
                'grupo'  => $grupo,
                'atm_list'  => $atm_list
            ];
            return view('atm_baja.presupuestos.edit', $data);
        }else{
            Session::flash('error_message', 'Presupuesto no encontrado.');
            return redirect()->back();
        }

    }

    public function update(Request $request, $id)
    {
        if (!$this->user->hasAccess('atms.group.presupuesto.edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();
        $input  = $request->all();

        if ($presupuesto = Presupuesto::find($id)){
            try{
                $presupuesto->fill([
                                'fecha'      => Carbon::createFromFormat('d/m/Y', $input['fecha'])->toDateString(),
                                'monto'      => str_replace('.', '', $input['monto']),
                                'updated_at' => Carbon::now()
                            ])->save();

                //Auditoria
                $history = new InactivateHistory();
                $history->group_id    = $presupuesto->group_id;
                $history->operation   = 'PRESUPUESTO - UPDATE';
                $history->data        = json_encode($presupuesto);
                $history->created_at  = NULL;
                $history->created_by  = NULL;
                $history->updated_at  = Carbon::now();
                $history->updated_by  = $this->user->id;
                $history->deleted_at  = NULL;
                $history->deleted_by  = NULL;
                $history->save();

                \DB::commit();
                return redirect()->to('atm/new/'.$presupuesto->group_id.'/'.$presupuesto->group_id.'/presupuesto')->with('actualizar','ok');
                
            }catch (\Exception $e){
                \DB::rollback();
                \Log::error("Error updating presupuesto: " . $e->getMessage());
                Session::flash('error_message','Error al intentar actualizar el presupuesto');
                return redirect()->to('atm/new/'.$presupuesto->group_id.'/'.$presupuesto->group_id.'/presupuesto')->with('error','ok');
            }
        }else{
            \Log::warning("presupuesto not found");
            Session::flash('error_message', 'Presupuesto no encontrado');
            return redirect()->to('atm/new/'.$presupuesto->group_id.'/'.$presupuesto->group_id.'/presupuesto')->with('error','ok');
        }

    }

    public function destroy($id)
    {
        if (!$this->user->hasAccess('atms.group.presupuesto.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();
        $message = '';
        $error = '';
        if ($presupuesto = Presupuesto::find($id)){

            try{
               
                if (Presupuesto::where('id',$id)->delete()){
                    $message    =  'Presupuesto eliminado correctamente';
                    $error      = false;
                }
                //Auditoria
                $history = new InactivateHistory();
                $history->group_id    = $presupuesto->group_id;
                $history->operation   = 'PRESUPUESTO - DELETE';
                $history->data        = json_encode($presupuesto);
                $history->created_at  = NULL;
                $history->created_by  = NULL;
                $history->updated_at  = NULL;
                $history->updated_by  = NULL;
                $history->deleted_at  = Carbon::now();
                $history->deleted_by  =  $this->user->id;
                $history->save();
                
                \DB::commit();
            }catch (\Exception $e){
                \DB::rollback();
                \Log::error("Error deleting presupuesto: " . $e->getMessage());
                $message   =  'Error al intentar eliminar el presupuesto';
                $error     = true;
            }
        }else{
            $message  =  'Presupuesto no encontrado';
            $error    = true;
        }

        return response()->json([
            'error'     => $error,
            'message'   => $message,
        ]);
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

    public function sendMulta($group_id, $fecha, $monto, $housing_id, $num_cuota, $idtransaccion, $idproducto, $presupuesto_id)
    {
        try {
            //\DB::beginTransaction();
            $grupo          = \DB::table('business_groups')->where('id', $group_id)->first();
            $pdv            = $grupo->ruc;
            $importe        = $monto;
            $idproducto1    = $idproducto;
            $imei           = '';
            $idtransaccion  =  $idtransaccion;
            $concepto       = $idproducto;

            $query  =  "SET NOCOUNT ON;
                        SET ANSI_WARNINGS OFF;
                        DECLARE @rv Numeric(25)
                        DECLARE @FECHA DATE
                        SET @FECHA = (SELECT CONVERT(DATE, '$fecha', 103))
                        EXEC @rv = [DBO].[P_FACTURA_ALQUILER]
                        '$pdv', @FECHA, '$idproducto1','$importe', '$idtransaccion', '$imei'
                        SELECT @rv";

            \Log::info("[Baja - generar multa] Prodecimiento a ejecutar en Ondanet ", ['query' => $query]);
            $update_presupuesto = Presupuesto::find($presupuesto_id);
            $update_presupuesto->fill(['request_data' => $query]);
            $update_presupuesto->save();
           

            //Auditoria
            $history = new InactivateHistory();
            $history->group_id    = $group_id;
            $history->operation   = 'PRESUPUESTO FACTURA - REQUEST';
            $history->data        = $query;
            $history->created_at  = Carbon::now();
            $history->created_by  = $this->user->id;
            $history->updated_at  = NULL;
            $history->updated_by  = NULL;
            $history->deleted_at  = NULL;
            $history->deleted_by  = NULL;
            $history->save();

            $results = $this->get_one($query);
            \Log::info("[Baja - generar multa] Respuesta de Ondanet ", ['response' => $results]);
            
            //Auditoria, insertar respuesta de ondanet
            $history = new InactivateHistory();
            $history->group_id    = $group_id;
            $history->operation   = 'PRESUPUESTO FACTURA - RESPONSE';
            $history->data        = json_encode($results);
            $history->created_at  = Carbon::now();
            $history->created_by  = $this->user->id;
            $history->updated_at  = NULL;
            $history->updated_by  = NULL;
            $history->deleted_at  = NULL;
            $history->deleted_by  = NULL;
            $history->save();

           // \DB::commit();
            $tamano = sizeof($results);
          
            if($tamano == 1){
                $response   = $results[0];           
                $result     = '';
                $result_aux     = '';
                foreach ($response as $key => $value) {
                    $result_aux = $value;
                }

                foreach ($result_aux as $key1 => $value2) {
                    $result = $value2;
                }

            }else{
                $response   = $results[2];
                $result     = '';
                foreach ($response as $key => $value) {
                    $result = $value;
                }
            }

            //se configura un array con los estados de errores conocidos
            $errors = array(
                "-6 | Vendedor no existe en ONDANET"                        => "-6",
                "-7 | El producto solo pueden ser los siguientes: (ALQMES1, ALQMES2, ALQMES3, ALQMES4, ALQMES5, ALQMES6, ALQMES7, ALQMES8, ALQMES9, ALQMES10, ALQMES11, ALQMES12)" => "-7",
                "-10 | El importe no puede ser <= 0"                        => "-10",
                "-14 | Nro. de venta ya existe con el mismo IDTRANSACCION"  => "-14",
                "-16 | Producto no agregado en ONDANET"                     => "-16",
                "-24 | FECHA VENTA NO DEFINIDA"                             => "-24",
                "-26 | No se encuentra creado el cliente en ONDANET"        => "-26",
                "212 | otros errores no definidos en el procedimiento"      => "212",
            );
 

            $check = array_search($result, $errors);

            if (!$check) {

                $cuotas             = $results[0];
                $numventa           = $results[1][0]['NUMVENTA'];
                $response_id        = $results[2][0][''];
                $data['cuotas']     = $cuotas;
                $data['error']      = false;
                $data['status']     = $response_id;
                $data['numero_venta'] = $numventa;
                $data['concepto']     = $concepto;
                \Log::info('data');
                \Log::info($data);

                
                $presupuesto = Presupuesto::find($presupuesto_id);
                //creacion de la multa
                $multa = new Multa();
                $multa->alquiler_id                 = 0;
                $multa->concepto                    = $idproducto;
                $multa->destination_operation_id    = $response_id;
                $multa->response                    = json_encode($cuotas->CODNUMEROCUOTA,$cuotas->CODVENTA, $cuotas->FECHAVCTO, $cuotas->SALDOCUOTA, $cuotas->IMPORTECUOTA, $cuotas->FECGRA, $cuotas->CODUSUARIO);
                $multa->group_id                    = $group_id;
                $multa->importe                     = str_replace('.', '', $monto);
                $multa->saldo                       = str_replace('.', '', $monto);
                $multa->num_venta                   = strval($numventa);
                $multa->fecha_vencimiento           = $presupuesto->fecha; //ver la fecha de vencimiento en el dia o el plazo
                $multa->created_at                  = Carbon::now();
                $multa->created_by                  = $this->user->id;;
                $multa->updated_at                  = Carbon::now();
                $multa->deleted_at                  = NULL;
                $multa->save();
                \Log::info('multa');
                \Log::info($multa);

     
                return $data;
            } else {
                $message            = explode("|", $check);
                $data['error']      = true;
                $data['status']     = $check;
                $data['code']       = $message[0];

                \Log::info('data');
                \Log::info($data);
                return $data;
            }
        } catch (\Exception $e) {
           // \DB::rollback();
            $response['error']      = true;
            $response['status']     = '212';
            $response['code']       = '212';
            \Log::warning('[Eglobal - Cliente]', ['result' => $e]);
            return $response;
        }
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


    public function relanzar($presupuesto_id)
    {
        try {
            \DB::beginTransaction();
            //$retiro     = RetiroDispositivo::find($retiro_id);
            $presupuesto = Presupuesto::find($presupuesto_id);
            $query      = $presupuesto->request_data;
            \Log::info("[Baja - transferencia] Prodecimiento a ejecutar en Ondanet ", ['query relanzar' => $query]);
            
            $results = $this->get_one($query);
            \Log::info("[Baja - transferencia] Respuesta de Ondanet ", ['response relanzar' => $results]);

            $tamano = sizeof($results);

            if($tamano == 1){
                $response   = $results[0];
                $result     = '';
                $result_aux = '';
                foreach ($response as $key => $value) {
                    $result_aux = $value;
                }
                foreach ($result_aux as $key1 => $value2) {
                    $result = $value2;
                }
            }else{
                $response   = $results[1];
                $result     = '';
                foreach ($response as $key => $value) {
                    $result = $value;
                }
            }
            //se configura un array con los estados de errores conocidos
            $errors = array(
                "-1 | IMEI no encontrado en el deposito de ONDANET"                                                 => "-1",
                "-2 | Sucursal destino no existe"                                                                   => "-2",
                "-3 | El IMEI ingresado ya se encuentra en el deposito: 1010 - PRODUCTOS TERMINADOS MINITERMINALES" => "-3",
                "-9 | Numero de transferencia no habilitado en Rangos activos"                                      => "-9",
                "-11 | Verifique, el producto que se quiere transferir tiene que ser de la LINEA 'EQUIPOS'"         => "-11",
                "-14 | Número de TRANSFERENCIA ya existe con el mismo Tipo de Comprobante Y TIMBRADO"               => "-14",
                "-16 | Producto no agregado en ONDANET"                                                             => "-16",
                "-18 | IMEI no disponible"                                                                          => "-18",
	            "-19 | Dos veces ingresado el mismo IMEI"                                                           => "-19",
	            "-25 | Código (STATUS) de transferencia Duplicado, reexportar nuevamente."                          => "-25",
	            "-250 | Cantidad insuficiente"                                                                      => "-250",
	            "212 | Otros errores no definidos ni citados en el procedimiento."                                  => "212"
            );

            $check = array_search($result, $errors);
   
            if (!$check) {

                $cuotas             = $results[0];
                $numventa           = $results[1][0]['NUMVENTA'];
                $response_id        = $results[2][0]['']; //PRODUCCION VACIO
                $data['cuotas']     = $cuotas;
                $data['error']      = false;
                $data['status']     = $response_id;
                $data['numero_venta'] = $numventa;
                //$data['concepto']     = $concepto;

                $presupuesto->fill([
                    'status_ondanet'  => $data['status'],
                    'num_venta'       => $data['numero_venta']]);
                $presupuesto->save();

                \Log::info('data');
                \Log::info($data);

                \DB::commit();
                return redirect()->back()->with('guardar_relanzar','ok');
            } else {

                $message  = explode("|", $check);
                $presupuesto->fill([
                    'status_ondanet'  => $message[0],
                    'num_venta'       => $message[1]]);
                $presupuesto->save();

                \DB::commit();
                return redirect()->back()->with('error_relanzar','ok');
            }
        } catch (\Exception $e) {
            \DB::rollback();
            $response['error']   = true;
            $response['status']  = '212';
            $response['code']    = '212';
            \Log::warning('[Eglobal - Cliente]', ['result' => $e]);
            return redirect()->back();
        }
    }



}
