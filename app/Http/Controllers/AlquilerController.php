<?php

namespace App\Http\Controllers;

use App\Exports\ExcelExport;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Alquiler;
use App\Http\Requests\AlquilerRequest;

use Carbon\Carbon;

use Session;

use Excel;
use Exception;

class AlquilerController extends Controller
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
        if (!$this->user->hasAccess('alquiler')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $description = $request->get('description');

        $alquileres = Alquiler::filterAndPaginate($description);

        $export_list = $this->get_rental_list($description);

        return view('alquiler.index', compact('alquileres', 'description', 'export_list'));
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
            ->whereNull('deleted_at')
            ->whereNotNull('ruc')
        ->get();

        $num_cuotas = array(12 => 12, 18 => 18, 24 => 24);

        $seriales = [];
        $data_select = [];
        $select = [];



        $seriales =  \DB::connection('ondanet')
        ->select("
                select *, 'Miniterminal' as nombre FROM LISTADO_SERIALES_MINITERMINALES_ALQUILADOS
                union all
                select *, 'Nanoterminal' as nombre from LISTADO_SERIALES_NANOTERMINALES_ALQUILADOS
                union all
                select SERIAL, CLIENTE, DEPOSITO, 'FK' as nombre from LISTADO_SERIALES_PROYECTOFK_ALQUILADOS
        ");


        //dd($seriales);

        foreach ($grupos as $key => $grupo) {
            $data_select[$grupo->id] = $grupo->description . ' | ' . $grupo->ruc;
        }

        foreach ($seriales as $key => $serial) {
            $select[$serial->SERIAL] = $serial->nombre . ' | ' . $serial->SERIAL . ' | ' . $serial->CLIENTE;
        }
        
        $resultset = array(
            'grupos'        => $data_select,
            'seriales'      => $select
        );

        return view('alquiler.create', compact('grupos', 'num_cuotas', 'seriales'))->with($resultset);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AlquilerRequest $request)
    {
        $input = $request->all();

        if(isset($input['serialnumber'][0])){
            $first_serial= $input['serialnumber'][0];

            $ondanet_serial =  \DB::connection('ondanet')
                ->select("
                select * from LISTADO_SERIALES_PROYECTOFK_ALQUILADOS where SERIAL = '$first_serial'
            ");
        }else{
            if(!isset($input['checkbox'])){
                Session::flash('error_message', 'Favor seleccion el Codigo de Maquina correspondiente.');
                return redirect()->back()->withInput();
                \Log::info('This is some useful information.');
            }

            $input['serialnumber'][0] = 'FK-SDCM-'.date('YmdHis');
        }
        //dd($input);
        if(!isset($ondanet_serial[0])){
            \DB::beginTransaction();
            try {
                $alquiler = new Alquiler;
                $alquiler->destination_operation_id = 0;
                $alquiler->group_id                 = $input['group_id'];
                $alquiler->importe                  = $input['amount'];
                $alquiler->created_at               = Carbon::now();
                $alquiler->updated_at               = Carbon::now();
                $alquiler->cant_alquiler            = $input['num_cuota'];
    
                if ($alquiler->save()) {
    
                    $seriales = $input['serialnumber'];
                    //dd($seriales);
                    $serial = implode("','", $seriales);
    
                    foreach ($seriales as $serial) {
    
                        $housin = \DB::table('housing')
                            ->where('serialnumber', $serial)
                            ->first();
    
                        if(!is_null($housin)){
                            $last_housing_id=$housin->id;
                            //update de campo conciliado
                            $alq_housing = \DB::table('alquiler_housing')
                                ->where('housing_id', $last_housing_id)
                            ->first();
    
                            if(!is_null($alq_housing)){
                                \DB::table('alquiler_housing')
                                    ->where('housing_id', $last_housing_id)
                                ->update(['alquiler_id' => $alquiler->id]);
                            }else{
                                \DB::table('alquiler_housing')->insert([
                                    'alquiler_id'      => $alquiler->id,
                                    'housing_id'   => $last_housing_id
                                ]);
                            }
    
                            
                        }else{

                            $housing_type_id = (str_contains($serial, 'FK-SDCM-')) ? 1 : 2;

                            $last_housing_id = \DB::table('housing')->insertGetId([
                                'serialnumber'      => $serial,
                                'housing_type_id'   => $housing_type_id,
                                'installation_date' =>  Carbon::now(),
                            ]);
    
                            \DB::table('alquiler_housing')->insert([
                                'alquiler_id'      => $alquiler->id,
                                'housing_id'   => $last_housing_id
                            ]);
                        }    
                    }
    
                    \DB::commit();
                    Session::flash('message', 'Registro creado exitosamente');
                    return redirect('alquiler');
                } else {
                    \DB::rollback();
                    Session::flash('error_message', 'Ocurrio un error al intentar guardar el registro');
                    return redirect()->back()->withInput();
                    \Log::info('This is some useful information.');
                }
            } catch (\Exception $e) {
                \DB::rollback();
                \Log::error("Error saving new Alquiler - {$e->getMessage()}");
                Session::flash('error_message', 'Ocurrio un error al intentar guardar el registro :(');
                return redirect()->back()->withInput();
            }
        }else{

            $serialnumbers = $input['serialnumber'];
            $serial_ondanet = array();
            foreach($serialnumbers as $serialnumber){
                $nro_serial = \DB::connection('ondanet')
                    ->table('LISTADO_SERIALES_PROYECTOFK_ALQUILADOS')
                    ->where("SERIAL", $serialnumber)
                ->first();

                if(!empty($nro_serial)){
                    array_push($serial_ondanet, $nro_serial->SERIAL);
                }
            }
            //dd($serialnumbers);
            if($serialnumbers !== $serial_ondanet){
                Session::flash('error_message', 'Error - Hubo un error cuando seleccionaste los seriales');
                return redirect()->back()->withInput();
                \Log::info('This is some useful information.');
            }
            \DB::beginTransaction();
            try {
                $alquiler = new Alquiler;
                $alquiler->destination_operation_id = 0;
                $alquiler->group_id                 = $input['group_id'];
                $alquiler->importe                  = $input['amount'];
                $alquiler->created_at               = Carbon::now();
                $alquiler->updated_at               = Carbon::now();
                $alquiler->cant_alquiler            = $input['num_cuota'];

                if ($alquiler->save()) {

                    $grupo = \DB::table('business_groups')
                        ->where("id", $input['group_id'])
                    ->first();

                    $name_housing='FK-'.$grupo->ruc . ltrim($nro_serial->SERIAL, "0");;

                    $housing = \DB::table('housing')
                        ->where('serialnumber', $name_housing)
                    ->first();

                    if(!is_null($housing)){
                        $last_housing_id=$housing->id;
                        //update de campo conciliado
                        $alq_housing = \DB::table('alquiler_housing')
                            ->where('housing_id', $last_housing_id)
                        ->first();

                        if(!is_null($alq_housing)){
                            \DB::table('alquiler_housing')
                                ->where('housing_id', $last_housing_id)
                            ->update(['alquiler_id' => $alquiler->id]);
                        }else{
                            \DB::table('alquiler_housing')->insert([
                                'alquiler_id'      => $alquiler->id,
                                'housing_id'   => $last_housing_id
                            ]);
                        }
                    }else{
                        $last_housing_id = \DB::table('housing')->insertGetId([
                            'serialnumber'      => $name_housing,
                            'housing_type_id'   => 1,
                            'installation_date' =>  Carbon::now(),
                        ]);

                        \DB::table('alquiler_housing')->insert([
                            'alquiler_id'      => $alquiler->id,
                            'housing_id'   => $last_housing_id
                        ]);
                    }

                    $seriales = $input['serialnumber'];
                    //dd($seriales);
                    $serial = implode("','", $seriales);

                    foreach ($seriales as $serial) {

                        $device = \DB::table('device')
                            ->where('serialnumber', $serial)
                        ->first();

                        if(!is_null($device)){
                            $last_device_id=$device->id;
                            //update de campo conciliado
                            $history_device = \DB::table('device_history')
                                ->where('device_id', $last_device_id)
                            ->first();

                            if(!is_null($history_device)){
                                \DB::table('device_history')->insert([
                                    'device_serialnumber'   => $device->serialnumber,
                                    'device_id'             => $device->id,
                                    'installation_date'     => Carbon::now(),
                                    'housing_serialnumber'  => $name_housing,
                                    'housing_from'          => $history_device->housing_to,
                                    'housing_to'            => $last_housing_id
                                ]);
                            }else{
                                \DB::table('device_history')->insert([
                                    'device_serialnumber'   => $device->serialnumber,
                                    'device_id'             => $device->id,
                                    'installation_date'     => Carbon::now(),
                                    'housing_serialnumber'  => $name_housing,
                                    'housing_to'            => $last_housing_id
                                ]);
                            }

                            \DB::table('device')
                                ->where('device_id', $last_device_id)
                            ->update(['activo' => true]);

                        }else{

                            $nro_serial = \DB::connection('ondanet')
                                ->table('LISTADO_SERIALES_PROYECTOFK_ALQUILADOS')
                                ->where("SERIAL", $serial)
                            ->first();

                            if($nro_serial->PRODUCTO == 'RDR-ACR1252U-NFC'){
                                $model_id=5;
                            }else{
                                $model = \DB::table('model')
                                    ->where("description", $nro_serial->PRODUCTO)
                                ->first();

                                if(!empty($model)){
                                    $model_id= $model->id;
                                }else{
                                    $model_id= null;
                                }
                                
                            }

                            $last_device_id = \DB::table('device')->insertGetId([
                                'serialnumber'      => $serial,
                                'descripcion'       => $nro_serial->PRODUCTO,
                                'installation_date' => Carbon::now(),
                                'housing_id'        => $last_housing_id,
                                'activo'            => true,
                                'activated_at'      => Carbon::now(),
                                'model_id'             => $model_id
                            ]);

                            \DB::table('device_history')->insert([
                                'device_serialnumber'   => $serial,
                                'device_id'             => $last_device_id,
                                'installation_date'     => Carbon::now(),
                                'housing_serialnumber'  => $name_housing,
                                'housing_to'            => $last_housing_id
                            ]);
                        }    
                    }

                    \DB::commit();
                    Session::flash('message', 'Registro creado exitosamente');
                    return redirect('alquiler');
                } else {
                    \DB::rollback();
                    Session::flash('error_message', 'Ocurrio un error al intentar guardar el registro');
                    return redirect()->back()->withInput();
                    \Log::info('This is some useful information.');
                }
            } catch (\Exception $e) {
                \DB::rollback();
                \Log::error("Error saving new Alquiler - {$e->getMessage()}");
                Session::flash('error_message', 'Ocurrio un error al intentar guardar el registro :(');
                return redirect()->back()->withInput();
            }
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
     * 
     * Validate first transaction alquiler.
     *
     */
    public function checkTransaction()
    {

        try {
            $alquileres = \DB::table('alquiler')
                ->where('destination_operation_id', 0)
            ->get();

            foreach ($alquileres as $key => $alquiler) {
                //\DB::beginTransaction();
                $housings = \DB::table('alquiler_housing')
                    ->where('alquiler_id', $alquiler->id)
                    ->get();

                foreach ($housings as $key => $housing) {
                    $atm = \DB::table('atms')
                        ->where('housing_id', $housing->housing_id)
                        ->first();

                    if (!empty($atm)) {
                        $first_transaction = \DB::table('transactions')
                            ->where('atm_id', $atm->id)
                            ->where('status', 'success')
                            ->orderBy('created_at', 'ASC')
                            ->first();
                        if (!empty($first_transaction)) {

                            $group_id = $alquiler->group_id;
                            $fecha = $first_transaction->created_at;
                            $importe = $alquiler->importe;
                            $housing_id = $housing->housing_id;
                            $num_cuota = null;
                            $cuota_alquiler_id = null;
                            $response = $this->sendAlquiler($group_id, $fecha, $importe, $housing_id, $num_cuota, $cuota_alquiler_id);
                            \Log::info($response);

                            if ($response['error'] == false) {

                                $saveondanet = Alquiler::find($alquiler->id);
                                $saveondanet->fill([

                                    'destination_operation_id'  => $response['status'],
                                    'num_venta'                 => $response['numero_venta'],
                                    'response'                  => json_encode($response)

                                ]);
                                $saveondanet->save();

                                $cuotas = $response['cuotas'];
                                \Log::info($cuotas);

                                foreach ($cuotas as $cuota) {
                                    \DB::table('cuotas_alquiler')->insert([
                                        'alquiler_id'       => $alquiler->id,
                                        'num_cuota'         => $cuota['CODNUMEROCUOTA'],
                                        'cod_venta'         => $cuota['CODVENTA'],
                                        'importe'           => (int)$cuota['IMPORTECUOTA'],
                                        'saldo_cuota'       => (int)$cuota['SALDOCUOTA'],
                                        'fecha_vencimiento' => date("Y-m-d h:i:s", strtotime($cuota['FECHAVCTO'])),
                                        'fecha_grabacion'   => date("Y-m-d h:i:s", strtotime($cuota['FECGRA'])),
                                        'num_venta'         => $response['numero_venta']
                                    ]);
                                }

                                $i = 1;
                                $cant_cuotas = $alquiler->cant_alquiler;

                                //algoritmo para insertar con fechas
                                do {
                                    $dia = date("d", strtotime($fecha));
                                    if ($dia == 31 || $dia == 30 || $dia == 29) {
                                        $year = date('Y',strtotime($fecha));
                                        $month = date('m',strtotime($fecha));

                                        $date = Carbon::parse($fecha);
                                        $days = $i * 30;
                                        $mes = $date->addDay($days)->format('F');
                                        if ($dia == 31) {
                                            //$date = new Carbon('last day of ' . $mes);
                                            $date= date('Y-m-t H:i:s', mktime( 0, 0, 0, $month + $i, 1, $year ));
                                        } else {
                                            if ($mes == 'February') {
                                                //$date = new Carbon('last day of ' . $mes);
                                                $date= date('Y-m-t H:i:s', mktime( 0, 0, 0, $month + $i, 1, $year ) );
                                            } else {
                                                $date = Carbon::parse($fecha)->modify('+' . $i . 'month');
                                            }
                                        }
                                    } else {
                                        $date = Carbon::parse($fecha)->modify('+' . $i . 'month');
                                    }

                                    $i++;

                                    \DB::table('cuotas_alquiler')->insert([
                                        'alquiler_id'       => $alquiler->id,
                                        'num_cuota'         => $i,
                                        'importe'           => $alquiler->importe,
                                        'saldo_cuota'       => $alquiler->importe,
                                        'fecha_vencimiento' => $date,
                                        'fecha_grabacion'   => $date,
                                    ]);

                                    \Log::info($date);
                                } while ($i < $cant_cuotas);

                                \DB::table('balance_rules')->insert([
                                    ['created_at'   => Carbon::now(), 'updated_at'   => Carbon::now(), 'dias_previos' => 1, 'saldo_minimo' => 100, 'tipo_control' => 3, 'dia' => 1, 'atm_id' => $atm->id],
                                    ['created_at'   => Carbon::now(), 'updated_at'   => Carbon::now(), 'dias_previos' => 1, 'saldo_minimo' => 100, 'tipo_control' => 3, 'dia' => 3, 'atm_id' => $atm->id],
                                    ['created_at'   => Carbon::now(), 'updated_at'   => Carbon::now(), 'dias_previos' => 1, 'saldo_minimo' => 100, 'tipo_control' => 3, 'dia' => 5, 'atm_id' => $atm->id]
                                ]);

                                //\DB::commit();
                                \Log::info('Registro creado exitosamente');
                            } else {
                                //\DB::rollback();
                                \Log::info('Ocurrio un error al intentar guardar el Alquiler.');
                            }
                        } else {
                            //\DB::rollback();
                            \Log::error('[ALQUILER Miniterminal] El siguiente ATM ' . $atm->id . ' aun no tiene una transaccion');
                        }
                    } else {
                        //\DB::rollback();
                        \Log::error('[ALQUILER Miniterminal] El siguiente housing_id ' . $housing->housing_id . ' no tiene un ATM asignado');
                    }
                }
            }

            $response['error'] = false;
            $response['message'] = 'Control de Primera transaccion de Alquiler ejecutado sin errores';
            \Log::warning($response);

            return $response;
        } catch (\Exception $e) {
            \Log::error("Error saving new Alquiler - {$e->getMessage()}");
        }
    }

    /**
     * 
     * Validate first transaction alquiler.
     *
     */
    public function checkVencimiento()
    {

        try {
            $desde = Carbon::tomorrow();
            $hasta = Carbon::tomorrow()->modify('1 day')->modify('-1 second');

            $cuotas = \DB::table('cuotas_alquiler')
                ->select('cuotas_alquiler.*', 'alquiler.group_id')
                ->join('alquiler', 'alquiler.id', '=', 'cuotas_alquiler.alquiler_id')
                ->where('cuotas_alquiler.saldo_cuota', '!=', 0)
                ->whereBetween('fecha_vencimiento', [$desde, $hasta])
                ->where('alquiler.activo', true)
                ->whereNull('cuotas_alquiler.cod_venta')
                ->whereNull('cuotas_alquiler.movements_id')
            ->get();

            //dd($cuotas);

            foreach ($cuotas as $key => $cuota) {

                $housings = \DB::table('alquiler_housing')
                    ->where('alquiler_id', $cuota->alquiler_id)
                    ->get();

                foreach ($housings as $key => $housing) {
                    $atm = \DB::table('atms')
                        ->where('housing_id', $housing->housing_id)
                        ->first();
                    //\DB::beginTransaction();
                    if (!empty($atm)) {
                        $group_id = $cuota->group_id;
                        $fecha = $cuota->fecha_vencimiento;
                        $importe = $cuota->importe;
                        $housing_id = $housing->housing_id;
                        $num_cuota = $cuota->num_cuota;
                        $cuota_alquiler_id = $cuota->id;
                        $response = $this->sendAlquiler($group_id, $fecha, $importe, $housing_id, $num_cuota, $cuota_alquiler_id);
                        \Log::info($response);

                        if ($response['error'] == false) {

                            \DB::table('cuotas_alquiler')
                            ->where('id', $cuota->id)
                            ->update([
                                'cod_venta'         => $response['cuotas'][0]['CODVENTA'],
                                'fecha_vencimiento' => date("Y-m-d h:i:s", strtotime($response['cuotas'][0]['FECHAVCTO'])),
                                'fecha_grabacion'   => date("Y-m-d h:i:s", strtotime($response['cuotas'][0]['FECGRA'])),
                                'num_venta'         => $response['numero_venta']
                            ]);
        
                            $cuotas=$response['cuotas'];
                            \Log::info($cuotas);

                            //\DB::commit();
                            \Log::info('[ALQUILER Miniterminal] La siguiente cuota ha sido cargada a ondanet');
                        } else {
                            //\DB::rollback();
                            \Log::info('Ocurrio un error al intentar guardar el Alquiler.');
                        }
                    } else {
                        //\DB::rollback();
                        \Log::error('[ALQUILER Miniterminal] El siguiente housing_id ' . $housing->housing_id . ' no tiene un ATM asignado');
                    }
                }
            }

            $response['error'] = false;
            $response['message'] = 'Control de Vencimiento de Alquiler ejecutado sin errores';
            \Log::warning($response);

            return $response;
        } catch (\Exception $e) {
            \Log::error("Error saving new Cuota Alquiler - {$e->getMessage()}");
        }
    }

    public function reprocesarVencimiento($cuota_id)
    {

        try {

            $cuotas = \DB::table('cuotas_alquiler')
                ->select('cuotas_alquiler.*', 'alquiler.group_id')
                ->join('alquiler', 'alquiler.id', '=', 'cuotas_alquiler.alquiler_id')
                ->where('cuotas_alquiler.saldo_cuota', '!=', 0)
                ->where('alquiler.activo', true)
                ->where('cuotas_alquiler.id', $cuota_id)
                ->whereNull('cuotas_alquiler.cod_venta')
                ->whereNull('cuotas_alquiler.movements_id')
            ->get();

            \Log::info($cuotas);

            foreach ($cuotas as $key => $cuota) {

                $housings = \DB::table('alquiler_housing')
                    ->where('alquiler_id', $cuota->alquiler_id)
                    ->get();

                foreach ($housings as $key => $housing) {
                    $atm = \DB::table('atms')
                        ->where('housing_id', $housing->housing_id)
                        ->first();
                    //\DB::beginTransaction();
                    if (!empty($atm)) {
                        $group_id = $cuota->group_id;
                        //$fecha = $cuota->fecha_vencimiento;
                        $fecha = Carbon::now();
                        $importe = $cuota->importe;
                        $housing_id = $housing->housing_id;
                        $num_cuota = $cuota->num_cuota;
                        $cuota_alquiler_id = $cuota->id;
                        $response = $this->sendAlquiler($group_id, $fecha, $importe, $housing_id, $num_cuota, $cuota_alquiler_id);
                        \Log::info($response);

                        if ($response['error'] == false) {

                            \DB::table('cuotas_alquiler')
                            ->where('id', $cuota->id)
                            ->update([
                                'cod_venta'         => $response['cuotas'][0]['CODVENTA'],
                                'fecha_vencimiento' => date("Y-m-d h:i:s", strtotime($response['cuotas'][0]['FECHAVCTO'])),
                                'fecha_grabacion'   => date("Y-m-d h:i:s", strtotime($response['cuotas'][0]['FECGRA'])),
                                'num_venta'         => $response['numero_venta']
                            ]);
        
                            $cuotas=$response['cuotas'];
                            \Log::info($cuotas);

                            //\DB::commit();
                            \Log::info('[ALQUILER Miniterminal] La siguiente cuota ha sido cargada a ondanet');
                        } else {
                            //\DB::rollback();
                            \Log::info('Ocurrio un error al intentar guardar el Alquiler.');
                        }
                    } else {
                        //\DB::rollback();
                        \Log::error('[ALQUILER Miniterminal] El siguiente housing_id ' . $housing->housing_id . ' no tiene un ATM asignado');
                    }
                }
            }

            $response['error'] = false;
            $response['message'] = 'Control de Vencimiento de Alquiler ejecutado sin errores';
            \Log::warning($response);

            return $response;
        } catch (\Exception $e) {
            \Log::error("Error saving new Cuota Alquiler - {$e->getMessage()}");
        }
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

    public function sendAlquiler($group_id, $fecha, $monto, $housing_id, $num_cuota, $cuota_alquiler_id)
    {
        try {

            $grupo = \DB::table('business_groups')->where('id', $group_id)->first();

            $housing = \DB::table('housing')->where('id', $housing_id)->first();

            $last_alquiler = \DB::table('cuotas_alquiler')->orderBy('id', 'desc')->first();
            if (isset($last_alquiler)) {
                $alquiler_id = $last_alquiler->id + 1;
            } else {
                $alquiler_id = 2;
            }

            $devices = \DB::table('device')
                ->selectRaw("device.descripcion, device.serialnumber")
                ->join('housing as h', 'h.id', '=', 'device.housing_id')
                ->where('device.housing_id', $housing_id)
                ->where('h.housing_type_id', 1)
            ->get();

            $productos = '';

            if(!empty($devices)){
                foreach($devices as $device){
                    $productos .= ", '".$device->descripcion."', '".$device->serialnumber."'";
                }
            }

            $pdv = $grupo->ruc;
            $fecha = date("d/m/Y", strtotime($fecha));
            if (is_null($num_cuota)) {
                $idproducto1 = 'ALQMES1';
            } else {
                $idproducto1 = 'ALQMES' . $num_cuota;
                $alquiler_id = $cuota_alquiler_id;
            }
            $importe = $monto;
            $idtransaccion = $alquiler_id;
            $imei = $housing->serialnumber;

            $query = "SET NOCOUNT ON;
            SET ANSI_WARNINGS OFF;
            DECLARE @rv Numeric(25)
            DECLARE @FECHA DATE
            SET @FECHA = (SELECT CONVERT(DATE, '$fecha', 103))
            EXEC @rv = [DBO].[P_FACTURA_ALQUILER]
            '$pdv', @FECHA, '$idproducto1','$importe', '$idtransaccion', '$imei' $productos
            SELECT @rv";

            \Log::info($query);
            $results = $this->get_one($query);
            \Log::info($results);

            $response = $results[2];

            $result = '';
            \Log::info($response);
            foreach ($response as $key => $value) {
                $result = $value;
                \Log::info($result);
            }

            //se configura un array con los estados de error conocidos
            $errors = array(
                "-6 | Vendedor no existe en ONDANET"                  => "-6",
                "-7 | El producto solo pueden ser los siguientes: (ALQMES1, ALQMES2, ALQMES3, ALQMES4, ALQMES5, ALQMES6, ALQMES7, ALQMES8, ALQMES9, ALQMES10, ALQMES11, ALQMES12)" => "-7",
                "-10 | El importe no puede ser <= 0" => "-10",
                "-14 | Nro. de venta ya existe con el mismo IDTRANSACCION" => "-14",
                "-24 | FECHA VENTA NO DEFINIDA" => "-24",
                "-26 | No se encuentra creado el cliente en ONDANET" => "-26",
                "212 | otros errores no definidos en el procedimiento"  => "212",
            );


            $check = array_search($result, $errors);
            \Log::info($check);
            if (!$check) {

                $cuotas = $results[0];
                $numventa = $results[1][0]['NUMVENTA'];
                $response_id = $results[2][0]['']; 
                $data['cuotas'] = $cuotas;
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
    private function get_rental_list($description)
    {

        $export_list = [];

        try {
            $export_list = \DB::table('alquiler as a')
                ->select(
                    'a.id',
                    'bg.description',
                    'h.serialnumber',
                    'a.importe',
                    \DB::raw("to_char(a.created_at, 'DD/MM/YYYY HH24:MI:SS') as created_at")
                )
                ->join('business_groups as bg', 'bg.id', '=', 'a.group_id')
                ->join('alquiler_housing as ah', 'a.id', '=', 'ah.alquiler_id')
                ->join('housing as h', 'h.id', '=', 'ah.housing_id');

            if (trim($description) != '') {
                $export_list->whereRaw("lower(description) ilike '%' || lower('$description') || '%'");
            }

            $export_list = $export_list->orderBy('a.id', 'desc');

            //\Log::info('Query:');
            //\Log::info($export_list->toSql());

            $export_list = $export_list->get();

            $export_list = json_encode($export_list, true);

            /*\Log::info('Lista:');
            \Log::info($export_list);*/
        } catch (\Exception $e) {
            \Log::error("Error en listado: {$e->getMessage()}");
        }

        return $export_list;
    }

    /**
     * Exportar la lista a excel
     */
    public function rental_export(Request $request)
    {
        try {
            $json = $request['json'];
            $data_to_excel = json_decode($json, true);

            //\Log::info("Lista");
            //\Log::info($data_to_excel);

            //cargar datos.

            $data_to_excel_headers = [
                '#',
                'Grupo',
                'Número de Serie',
                'Monto',
                'Creado'
            ];

            $columnas = [
                '#',
                'Grupo',
                'Número de Serie',
                'Monto',
                'Creado'
            ];

            //array_unshift($data_to_excel, $data_to_excel_headers);

            $date = date("d/m/Y H:i:s.") . gettimeofday()["usec"];

            $filename = "rental_export_" . time();

            $style_array = [
                'font'  => [
                    'bold'  => true,
                    'color' => ['rgb' => '367fa9'],
                    'size'  => 15,
                    'name'  => 'Verdana'
                ]
            ];

            // Excel::create($filename, function ($excel) use ($data_to_excel, $style_array) {
            //     $excel->sheet('Registros del sistema', function ($sheet) use ($data_to_excel, $style_array) {
            //         $range = 'A1:E1';
            //         $sheet->rows($data_to_excel, false); //Cargar los datos
            //         $sheet->getStyle($range)->applyFromArray($style_array); //Aplicar los estilos del array
            //         $sheet->setHeight(1, 50); //Aplicar tamaño de la primera fila
            //         $sheet->cells($range, function ($cells) {
            //             $cells->setAlignment('center'); // Alineamiento horizontal a central
            //             $cells->setValignment('center'); // Alineamiento vertical a central
            //         });
            //     });
            // })->export('xls');

            $excel = new ExcelExport($data_to_excel,$columnas);
            return Excel::download($excel, $filename . '.xls')->send();

        } catch (\Exception $e) {
            $this->custom_error($e, __FUNCTION__);
        }
    }
}
