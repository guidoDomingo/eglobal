<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\CompraTarex;
use App\Http\Requests\CompraTarexRequest;

use Carbon\Carbon;
use Session;

class CompraTarexController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!$this->user->hasAccess('compra_tarex')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $description = $request->get('description');
        
        $compras = CompraTarex::filterAndPaginate($description);

        $reservationtime = "";
        //dd($compras);
        return view('compra_tarex.index', compact('compras', 'description',"reservationtime"));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!$this->user->hasAccess('compra_tarex.add')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $forma_pago = array('ATL' => 'Anticipo a Telecel', 'CHEMI' => 'Cheques Emitidos');
        $selected_fp='ATL';

        $modalidades = array(0 => 'Contado', 1 => 'Credito');
        $selected_modalidad=0;

        return view('compra_tarex.create', compact('forma_pago', 'selected_fp', 'modalidades', 'selected_modalidad'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CompraTarexRequest $request)
    {
        $input = $request->all();
        \DB::beginTransaction();

        try {
            $factura= $input['factura1'] . $input['factura2'] . $input['factura3'];

            if($input['modalidad'] =='0'){
                $modalidad='contado';
            }else{
                $modalidad='credito';
            }

            if(empty($input['desde'])){
                $desde=null;
            }else{
                $desde=$input['desde'];
            }

            $tarex = new CompraTarex;
            $tarex->numero_factura = $factura;
            $tarex->fecha = $input['fecha'];
            $tarex->timbrado = $input['timbrado'];
            $tarex->forma_pago = $input['forma_pago'];
            $tarex->modalidad = $modalidad;
            $tarex->producto = $input['producto'];
            $tarex->costo = (double)$input['costo'];
            $tarex->desde = $desde;
            $tarex->cantidad = (double)$input['cantidad'];
            $tarex->created_by = $this->user->id;
            $tarex->created_at = Carbon::now();
            $tarex->updated_at = Carbon::now();

            if ($tarex->save()) {

                $fecha              = $input['fecha'];
                $timbrado           = $input['timbrado'];
                $forma_pago         = $input['forma_pago'];
                $modalidad_factura  = $input['modalidad'];
                $cantidad           = $input['cantidad'];
                $costo              = $input['costo'];

                $response = $this->sendTarex($factura, $fecha, $timbrado, $forma_pago, $modalidad_factura, $cantidad, $costo);
                \Log::info($response);

                if ($response['error'] == false) {

                    $saveondanet = CompraTarex::find($tarex->id);
                    $saveondanet->fill([
                        'status_ondanet'  => $response['status']
                    ]);

                    $saveondanet->save();
                    \DB::commit();
                    Session::flash('message', 'Registro creado exitosamente');
                    return redirect('compra_tarex');
                } else {
                    \DB::rollback();
                    Session::flash('error_message', 'Ocurrio un error al intentar guardar el registro: ' . $response['status']);
                    return redirect()->back()->withInput();
                    \Log::info('Ocurrio un error al intentar guardar la Venta.');
                }
            } else {
                \DB::rollback();
                Session::flash('error_message', 'Ocurrio un error al intentar guardar el registro');
                return redirect()->back()->withInput();
                \Log::info('This is some useful information.');
            }
        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error("Error saving new Venta - {$e->getMessage()}");
            Session::flash('error_message', 'Ocurrio un error al intentar guardar el registro :(');
            return redirect()->back()->withInput();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function search_tarex(Request $request){
        $input = $request->all();

        $daterange = explode(' - ',  str_replace('/','-',$input['reservationtime']));
        $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
        $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));

        //$description = $request->get('description');

        $compras=CompraTarex::where(function($query) use($input){
            if($input['modalidad']!= 2){
                if($input['modalidad'] == 0 ){
                    $modalidad='contado';
                }else if($input['modalidad'] == 1){
                    $modalidad='credito';
                }
                $query->where('modalidad', $modalidad);
            }
        })
        ->whereBetween('fecha',[$daterange[0], $daterange[1]])
        ->paginate(20);

        $reservationtime=(isset($input['reservationtime'])?$input['reservationtime']:0);

        return view('compra_tarex.index', compact('compras', 'reservationtime'));
    }

    /**
     * TAREX-EGLOBAL: Método [dbo].[P_COMPRAS_MOMO]
     *
     * PARAMETROS
     * @TIPOENVIO NUMERIC(9) -> Tipo de operación enviada, por el momento siempre mantendremos con el número 20.
     * 
     * @IDDEPOSITO VARCHAR(10) -> Es el N.º de depósito donde se efectúa la operación, utilizaremos 
     *                          el número: 200 (TAREX-EGLOBAL)
     * 
     * @IDPROVEEDOR VARCHAR(30) -> Es el código(RUC) Proveedor que se encuentra creado en ONDANET para que 
     *                              salga a su nombre la factura de compra, utilizaremos 80000519-8 
     *                              (TELEFONICA CELULAR DEL PARAGUAY S.A.E)
     * 
     * @TIPOCOMPROBANTE VARCHAR(5) -> El código del comprobante utilizado, por el momento mantendremos 
     *                              siempre FAE – (FACTURA ELECTRONICA)
     * 
     * @NUMERO VARCHAR(20) -> Es la numeración real de la factura.
     * 
     * @FECHA1 VARCHAR(20) -> La fecha de la factura. 
     * 
     * @TIMBRADO VARCHAR(8) -> El timbrado de la factura.
     * 
     * @FP VARCHAR(10) -> La forma de PAGO de la factura, por el momento puede ser ATL (Anticipo a Telecel) 
     *                      o CHEMI (Cheques Emitidos)
     * 
     * @MODALIDAD TINYINT -> La modalidad Pago de la factura, 0 = CONTADO, 1 = CREDITO 
     * 
     * @IDPRODUCTO1 VARCHAR(25) -> Es el código del producto, utilizaremos TAREX-EGLOBAL (CARGA EXPRESS EGLOBAL)
     * 
     * @CANTIDAD1 NUMERIC(18,5) -> Es la cantidad del producto solicitado, en unidades, ejemplo se envía 1000, 
     *                      en ONDANET ingresa por 933.400gs (la cantidad multiplicada por el costo 933.40)
     * 
     * @COSTO1 NUMERIC(18,5) -> El Costo del producto TAREX-EGLOBAL, por el momento siempre 
     *                          se mantendrá en 933.40 GS  
     * 
     * @TIPO1 VARCHAR(100) -> Mantendremos siempre el valor 0
     */
    public function sendTarex($factura, $fecha, $timbrado, $forma_pago, $modalidad_factura, $cantidad, $costo)
    {
        try {

            $tipo_envio=20;
            $deposito=200;
            $proveedor= '80000519-8';
            $tipo_comprobante='FAE';
            //$producto="TAREX";
            $tipo1=0;
            $date = date("d/m/Y", strtotime($fecha));

            /*$query = "SET NOCOUNT ON;
            SET ANSI_WARNINGS OFF;
            DECLARE @rv Numeric(25)
            DECLARE @FECHA DATE
            SET @FECHA = (SELECT CONVERT(DATE, '$date' , 103))
            EXEC @rv = [DBO].[P_FACTURACION_MINITERMINALES]
            '$pdv', @FECHA, '$modalidad', '$importe', '$nro_cuota', '$vendedor', '$banco', '$tipo1'
            SELECT @rv";*/

            $query = "SET NOCOUNT ON;
            SET ANSI_WARNINGS OFF;
            SET DATEFORMAT mdy;
            DECLARE @rv Numeric(25)
            DECLARE @FECHA DATE
            SET @FECHA = (SELECT CONVERT(DATE, '$date', 103))
            EXEC @rv = [DBO].[P_COMPRAS_EGLOBAL]
            '$tipo_envio', '$deposito', '$proveedor', '$tipo_comprobante', '$factura', @FECHA, '$timbrado',
            '$forma_pago', '$modalidad_factura', " ."'".'"'."TAREX".'"'. "'" .", '$cantidad', '$costo', '$tipo1'
            SELECT @rv";
            //dd($query);
            \Log::info($query);
            $results = $this->get_one($query);
            \Log::info($results);
            //dd($modalidad_factura);
            if ($modalidad_factura == '0') {
                $response = $results[0];
            } else {
                $response = $results[1];
            }
            $result = '';
            \Log::info($response);
            
            foreach ($response as $key => $value) {
                $result = $value[''];
                \Log::info($result);
            }
            //dd($result);
            //se configura un array con los estados de error conocidos
            $errors = array(
                "-1  | No se encuentra creado en ONDANET el Tipo de PAGO"                       => "-1",
                "-2  | Deposito no se encuentra creado en ONDANET, o se encuentra inactivo"     => "-2",
                "-3  | Proveedor no creado en ONDANET"                                          => "-3",
                "-4  | Tipo de Comprobante no se encuentra creado en ONDANET"                   => "-4",
                "-7  | Número de COMPRA ya existe en ONDANET con el mismo Tipo de Comprobante"  => "-7",
                "-8  | El producto no se encuentra creado en ONDANET"                           => "-8",
                "-10 | El importe no puede ser <= 0"                                            => "-10",
                "-12 | La Cantidad no puede ser menor o igual a 0"                              => "-12",
                "-13 | El Nro. de COMPRA no puede ser cero."                                    => "-13",
                "-14 | Timbrado no definido"                                                    => "-14",
                "-16 | El producto solo puede ser TAREX"                                        => "-16",
                "212 | otros errores no definidos en el procedimiento"                          => "212",
            );


            $check = array_search($result, $errors);
            //dd($check);
            \Log::info($check);
            if (!$check) {
                /*if ($modalidad_factura == 0) {
                    $numventa = $results[0][0]['NUMVENTA'];
                    $response_id = $results[1][0][''];
                } else {
                    $cuotas = $results[0];
                    $numventa = $results[1][0]['NUMVENTA'];
                    $response_id = $results[2][0][''];
                    $data['cuotas'] = $cuotas;
                }
                $data['error'] = false;
                $data['status'] = $response_id;
                $data['numero_venta'] = $numventa;*/

                $data['error'] = false;
                $data['status'] = $result;
                return $data;
            } else {
                $message = explode("|", $check);
                $data['error'] = true;
                $data['status'] = $check;
                $data['code'] = $message[0];
                return $data;
            }
        } catch (\Exception $e) {
            $response['error']   = true;
            $response['status']  = '212';
            $response['code']    = '212';
            \Log::warning('[Eglobal - Cliente]', ['result' => $e]);
            return $response;
        }
    }

    /** FUNCIONES PRIVADAS COMUNES*/

    public function get_one($query)
    {

        try {
            \DB::beginTransaction();
            $db     = \DB::connection('ondanet_antell')->getPdo();
            
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
}
