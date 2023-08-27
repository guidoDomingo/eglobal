<?php

namespace App\Http\Controllers;

use App\Exports\ExcelExport;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Venta;
use App\Http\Requests\VentaRequest;
use App\Services\OndanetServices;

use Carbon\Carbon;

use Session;

use Excel;

class VentasController extends Controller
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
        if (!$this->user->hasAccess('ventas')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $num_venta = null;

        if (isset($request['num_venta'])) {
            $num_venta = $request->get('num_venta');
        }

        $ventas = Venta::filterAndPaginate($num_venta);

        $export_list = $this->get_sale_list($num_venta);

        return view('ventas.index', compact('ventas', 'num_venta', 'export_list'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!$this->user->hasAccess('ventas.add')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $grupos = \DB::table('business_groups')
            ->select(['business_groups.description', 'business_groups.ruc', 'business_groups.id'])
            ->get();

        $tipo_ventas = array('0' => 'Contado', '1' => 'Credito', '2' => 'Credito con entrega inicial');

        $vendedores = \DB::connection('ondanet')
            ->table('LISTADO_VENDEDORES_MINITERMINALES')
            ->select(\DB::raw(
                '
                        VENDEDOR, "NRO VENDEDOR" as nro_vendedor'
            ))
            ->pluck('VENDEDOR', 'nro_vendedor');

        $acreedores = \DB::table('acreedor')
            ->pluck('description', 'id');


        $seriales =  \DB::connection('ondanet')
            ->table('LISTADO_SERIALES_MINITERMINALES')
            ->pluck('SERIAL', 'SERIAL');
        //dd($seriales);
        $data_select = [];
        foreach ($grupos as $key => $grupo) {
            $data_select[$grupo->id] = $grupo->description . ' | ' . $grupo->ruc;
        }

        /*foreach ($vendedores as $vendedor) {
            $vendedor->'NRO VENDEDOR' = $vendedor->'VENDEDOR';
        }*/

        $resultset = array(
            'grupos'        => $data_select
        );

        return view('ventas.create', compact('grupos', 'tipo_ventas', 'vendedores', 'acreedores', 'seriales'))->with($resultset);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(VentaRequest $request)
    {
        $input = $request->all();
        
        if (isset($input['tipo_venta_id']) && ($input['tipo_venta_id'] == 1 || $input['tipo_venta_id'] == 2)) {
            $tipo_venta = 'cr';
            //$monto = count($input['serialnumber']) * $input['amount'];
            $monto=$input['amount'];
        } else {
            $input['tipo_venta_id'] = 0;
            $input['num_cuota'] = '';
            $tipo_venta = 'co';
            //$monto = count($input['serialnumber']) * $input['amount'];
            $monto=$input['amount'];
        }
        
        $vendedor = \DB::connection('ondanet')
            ->table('LISTADO_VENDEDORES_MINITERMINALES')
            ->whereRaw(' "NRO VENDEDOR" = ' . $input['id_vendedor'])
            ->first();
        //dd($input);
        \DB::beginTransaction();
        try {
            $venta = new Venta;
            $venta->tipo_venta = $tipo_venta;
            $venta->amount = $monto;
            $venta->created_at = Carbon::now();
            $venta->updated_at = Carbon::now();
            $venta->group_id = $input['group_id'];
            $venta->acreedor_id = $input['id_acreedor'];
            $venta->vendedor = $vendedor->VENDEDOR;

            if ($venta->save()) {

                $group_id = $input['group_id'];
                $fecha = $input['fecha'];
                $tipo_de_venta = $input['tipo_venta_id'];
                $monto = $input['amount'];
                $nombre_vendedor = $input['id_vendedor'];
                $acreedor = $input['id_acreedor'];
                $seriales = $input['serialnumber'];
                $num_cuota = $input['num_cuota'];

                $serial = implode("','", $seriales);

                $response = $this->sendVenta($group_id, $fecha, $tipo_de_venta, $monto, $nombre_vendedor, $acreedor, $serial, $num_cuota);
                \Log::info($response);

                if ($response['error'] == false) {
                    foreach ($seriales as $serial) {

                        $housin = \DB::table('housing')
                        ->where('serialnumber', $serial)
                        ->first();
            
                        if(!is_null($housin)){
                            $last_housing_id=$housin->id;

                            $vent_housing = \DB::table('venta_housing')
                                ->where('housing_id', $last_housing_id)
                            ->first();

                            if(!is_null($vent_housing)){
                                \DB::table('venta_housing')
                                    ->where('housing_id', $last_housing_id)
                                ->update(['venta_id' => $venta->id]);
                            }else{
                                \DB::table('venta_housing')->insert([
                                    'venta_id'      => $venta->id,
                                    'housing_id'   => $last_housing_id
                                ]);
                            }


                            \DB::table('venta_housing')
                                ->where('housing_id', $last_housing_id)
                            ->update(['venta_id' => $venta->id]);
                        }else{
                            $last_housing_id = \DB::table('housing')->insertGetId([
                                'serialnumber'      => $serial,
                                'housing_type_id'   => 2,
                                'installation_date' =>  Carbon::now(),
                            ]);

                            \DB::table('venta_housing')->insert([
                                'venta_id'      => $venta->id,
                                'housing_id'   => $last_housing_id
                            ]);
                        }
 
                    }

                    $saveondanet = Venta::find($venta->id);
                    $saveondanet->fill([

                        'destination_operation_id'  => $response['status'],
                        'num_venta'                 => $response['numero_venta'],
                        'response'                  => json_encode($response)

                    ]);
                    $saveondanet->save();

                    if (isset($input['tipo_venta_id']) && $input['tipo_venta_id'] == 1) {
                        \DB::table('credito')->insert([
                            'venta_id'          => $venta->id,
                            'cantidad_cuotas'   => $input['num_cuota']
                        ]);

                        $cuotas = $response['cuotas'];
                        \Log::info($cuotas);

                        foreach ($cuotas as $cuota) {
                            \DB::table('cuotas')->insert([
                                'credito_venta_id'  => $venta->id,
                                'numero_cuota'      => $cuota['CODNUMEROCUOTA'],
                                'saldo_cuota'       => (int)$cuota['SALDOCUOTA'],
                                'importe'           => (int)$cuota['IMPORTECUOTA'],
                                'fecha_vencimiento' => date("Y-m-d h:i:s", strtotime($cuota['FECHAVCTO'])),
                                'cod_usuario'       => $cuota['CODUSUARIO'],
                                'cod_empresa'       => $cuota['CODEMPRESA'],
                                'fecha_grabacion'   => date("Y-m-d h:i:s", strtotime($cuota['FECGRA'])),
                                'cod_venta'         => $cuota['CODVENTA']
                            ]);
                        }
                    }
                    \DB::commit();
                    Session::flash('message', 'Registro creado exitosamente');
                    return redirect('venta');
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

    /**
     * Validate ruc.
     *
     * @return \Illuminate\Http\Response
     */
    public function checkRuc(Request $request)
    {
        \Log::info("Nuevo grupo creado correctamente");
        if ($request->ajax()) {
            $parametros = $request;
            $data = \DB::table('business_groups')->where(function ($query) use ($parametros) {
                $query->where('ruc', $parametros->get('ruc'));
                if ($parametros->get('id') != null) {
                    $query->where('id', '<>', $parametros->get('id'));
                }
            })->count();

            if ($data < 1) {
                $valido = "true";
            } else {
                $valido = "false";
            }

            return $valido;
        }
    }

    /**
     * REVERSION: Método [dbo].[P_FACTURACION_MINITERMINALES]
     *
     * PARAMETROS
     * @PDV VARCHAR(20) -> Es el código (RUC) del cliente que se encuentra creado tanto en el ADMIN EGLOBAL 
     *                     como en ONDANET para que salga a su nombre la factura y afecte extracto en ONDANET, 
     *                      si no se encuentra creado en ONDANET retornará un mensaje de error (-26)
     * @FECHA1 VARCHAR(10) -> La fecha de la operación
     * @MODALIDAD INTEGER -> La modalidad de Pago que define si es CONTADO o CREDITO:
     *                      0 = CONTADO
     *                      1 = CREDITO
     * @IMPORTE NUMERIC(18,5) -> El importe total de la factura (IVA incluido)
     * @NROCUOTA VARCHAR(5) -> Número de Cuotas la cual se aplicará al extracto del cliente, 
     *                          el importe de cada cuota se calcula @IMPORTE / @NROCUOTA
     * @VENDEDOR NUMERIC(10) -> Número del vendedor (Preventista) que realiza la venta -> se procedió a crear 
     *                      una vista en ONDANET EGLOBAL donde se visualizan los vendedores activos en el sistema
     *                      (SELECT * FROM LISTADO_VENDEDORES_MINITERMINALES)
     * @BANCO NUMERIC(10) -> Número ID del banco utilizado (mismo ID BANCO que los depósitos bancarios)
     * @TIPO1 VARCHAR(100) -> El SERIAL de la mini terminal que se estará facturando.. -> se procedió a crear una 
     *                      vista en ONDANET EGLOBAL donde se visualizan los seriales disponibles en el sistema 
     *                      (SELECT * FROM LISTADO_SERIALES_MINITERMINALES)
     */
    public function sendVenta($group_id, $fecha, $tipo_de_venta, $monto, $nombre_vendedor, $acreedor, $serial, $num_cuota)
    {
        try {

            $grupo = \DB::table('business_groups')->where('id', $group_id)->first();

            $pdv = $grupo->ruc;
            $fecha = date("d/m/Y", strtotime($fecha));
            $modalidad = $tipo_de_venta;
            $importe = $monto;
            $nro_cuota = $num_cuota;
            $vendedor = $nombre_vendedor;
            $banco = $acreedor;
            $tipo1 = $serial;
            //dd($nro_cuota);
            $query = "SET NOCOUNT ON;
            SET ANSI_WARNINGS OFF;
            DECLARE @rv Numeric(25)
            DECLARE @FECHA DATE
            SET @FECHA = (SELECT CONVERT(DATE, '$fecha' , 103))
            EXEC @rv = [DBO].[P_FACTURACION_MINITERMINALES]
            '$pdv', @FECHA, '$modalidad', '$importe', '$nro_cuota', '$vendedor', '$banco', '$tipo1'
            SELECT @rv";

            \Log::info($query);
            $results = $this->get_one($query);
            \Log::info($results);

            if ($modalidad == 0) {
                $response = $results[1];
            } else {
                $response = $results[2];
            }
            $result = '';
            \Log::info($response);
            foreach ($response as $key => $value) {
                $result = $value;
                \Log::info($result);
            }

            //se configura un array con los estados de error conocidos
            $errors = array(
                "-6 | Vendedor no existe en ONDANET"                  => "-6",
                "-7 | VENDEDOR NO PERMITIDO, SOLO PUEDE SER TIPO VENDEDOR PREVENTISTA" => "-7",
                "-8 | Nro. de cuota no permitido, solo puede ser del 1 al 48" => "-8",
                "-15 | Dígito SERIAL insuficiente como mínimo debe ser 5 dígitos" => "-15",
                "-18 | No se encuentra disponible el SERIAL ingresado" => "-18",
                "-20 | Modalidad de pago Incorrecto, solo puede ser 0 CONTADO, 1 CREDITO" => "-20",
                "-21 | Modalidad crédito no permitido para Banco 6 – Interfisa" => "-21",
                "-22 | NRO DE BANCO MAL DEFINIDO, SOLO PUEDEN SER: del 1 al 7)" => "-22",
                "-23 | Caja cerrada en la fecha (si la modalidad es CONTADO)" => "-23",
                "-26 | El Siguiente Cliente es un cliente invalido" => "-26",
                "-27 | El nro. de venta no puede ser cero" => "-27",
                "-250 | Cantidad insuficiente en el depósito" => "-250",
                "212 | otros errores no definidos en el procedimiento"  => "212",
            );


            $check = array_search($result, $errors);
            \Log::info($check);
            if (!$check) {
                if ($modalidad == 0) {
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
                $data['numero_venta'] = $numventa;

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

    /**
     * Lista de venta a exportar.
     */
    private function get_sale_list($num_venta)
    {

        $export_list = [];

        try {
            $export_list = \DB::table('venta as v')
                ->select(
                    'v.id',
                    'h.serialnumber',
                    'bg.description',
                    \DB::raw("case when v.tipo_venta = 'co' then 'Contado' else 'Crédito' end as tipo_venta"),
                    'v.amount',
                    'v.vendedor',
                    \DB::raw("case when v.num_venta is not null then v.num_venta else 'Sin número de venta' end as num_venta"),
                    \DB::raw("to_char(v.created_at, 'DD/MM/YYYY HH24:MI:SS') as created_at")
                )
                ->join('business_groups as bg', 'bg.id', '=', 'v.group_id')
                ->join('venta_housing as vh', 'v.id', '=', 'vh.venta_id')
                ->join('housing as h', 'h.id', '=', 'vh.housing_id');

            if (trim($num_venta) != '') {
                $export_list->where('v.num_venta', $num_venta);
            }

            $export_list = $export_list
                ->orderBy('v.id', 'desc')
                ->get();

            $export_list = json_encode($export_list, true);

            //\Log::debug("VENTA: ", [$export_list]);
        } catch (\Exception $e) {
            \Log::error("Error en listado: {$e->getMessage()}");
        }

        return $export_list;
    }

    /**
     * Exportar la lista a excel
     */
    public function sale_export(Request $request)
    {
        try {
            $json = $request['json'];
            $data_to_excel = json_decode($json, true);

            $data_to_excel_headers = [
                '#',
                'Número de Serie',
                'Grupo',
                'Tipo de Venta',
                'Monto',
                'Vendedor',
                'Número de Venta',
                'Creado'
            ];

            array_unshift($data_to_excel, $data_to_excel_headers);

            $date = date("d/m/Y H:i:s.") . gettimeofday()["usec"];

            $filename = "sale_export_" . time();

            $style_array = [
                'font'  => [
                    'bold'  => true,
                    'color' => ['rgb' => '367fa9'],
                    'size'  => 15,
                    'name'  => 'Verdana'
                ]
            ];

            $columnas = [];

            $excel = new ExcelExport($data_to_excel,$columnas);
            return Excel::download($excel, $filename . '.xls')->send();

            // Excel::create($filename, function ($excel) use ($data_to_excel, $style_array) {
            //     $excel->sheet('Registros del sistema', function ($sheet) use ($data_to_excel, $style_array) {
            //         $range = 'A1:H1';
            //         $sheet->rows($data_to_excel, false); //Cargar los datos
            //         $sheet->getStyle($range)->applyFromArray($style_array); //Aplicar los estilos del array
            //         $sheet->setHeight(1, 50); //Aplicar tamaño de la primera fila
            //         $sheet->cells($range, function ($cells) {
            //             $cells->setAlignment('center'); // Alineamiento horizontal a central
            //             $cells->setValignment('center'); // Alineamiento vertical a central
            //         });
            //     });
            // })->export('xls');

            // exit();

        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }
    }
}
