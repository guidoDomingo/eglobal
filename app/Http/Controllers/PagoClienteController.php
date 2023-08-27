<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\PagoCliente;
use Carbon\Carbon;
use Session;

class PagoClienteController extends Controller
{

    protected $user;

    public function __construct()
    {
        $this->middleware('auth',['except' => 'dapdv_transactions']);
        $this->user = \Sentinel::getUser();
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        if (!$this->user->hasAccess('pago_clientes.import_pago')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $name = $request->get('name');
        $pagos = PagoCliente::filterAndPaginate($name);

        return view('pago_cliente.register_pago', compact('pagos', 'name'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!$this->user->hasAnyAccess('pago_clientes')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        try{

            $whereMovements = '';
            $hasta=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-1 days');

            $whereSales = "WHEN debit_credit = 'de' AND mt_sales.fecha <= '". $hasta . "'";
            $whereCobranzas = "WHEN movement_type_id = 2 AND m.created_at <= '". $hasta . "'";
            $whereReversion = "WHEN movement_type_id = 3 AND m.created_at <= '". $hasta . "'";
            $whereCashout   = "WHEN movement_type_id = 11 AND m.created_at <= '". $hasta . "'";
            $wherePagoQr = "WHEN movement_type_id = 17 AND m.created_at <= '". $hasta . "'";


            $hoy = Carbon::today();
            $tomorrow = Carbon::tomorrow();

            $group =  \DB::table('mt_pago_clientes')
            ->selectRaw('DISTINCT group_id')
            ->whereRaw("created_at BETWEEN '$hoy' AND '$tomorrow'")
            ->pluck('group_id');

            $groups = implode(', ', $group->toArray());

            if(!empty($groups)){
                $whereMovements = "bg.id not in ($groups) and";
            }

            $resumen_transacciones_groups = \DB::select("
                select
                        bg.id as group_id,
                        concat(bg.description,' | ',bg.ruc) as grupo,
                        SUM(CASE ".$whereSales." THEN (m.amount) else 0 END) as Debito,
                        SUM(CASE ".$whereCobranzas." THEN (m.amount) else 0 END) +
                        SUM(CASE ".$whereReversion." THEN (m.amount) else 0 END) +
                        SUM(CASE ".$wherePagoQr." THEN (m.amount) else 0 END) +
                        SUM(CASE ".$whereCashout." THEN (m.amount) else 0 END) as Credito,
                        (Case when cuota_a.saldo_alquiler != 0 then cuota_a.saldo_alquiler else 0 end)
                        + (Case when cuota_v.saldo_venta != 0 then cuota_v.saldo_venta else 0 end) as cuotas,
                        (   (SUM(CASE ".$whereSales." THEN (m.amount) else 0 END))
                            +(SUM(CASE ".$whereCobranzas." THEN (m.amount) else 0 END))
                            +(SUM(CASE ".$whereReversion." THEN (m.amount) else 0 END))
                            +(SUM(CASE ".$whereCashout." THEN (m.amount) else 0 END))
                            +(SUM(CASE ".$wherePagoQr." THEN (m.amount) else 0 END)) 
                            + (Case when cuota_a.saldo_alquiler != 0 then cuota_a.saldo_alquiler else 0 end)
                            + (Case when cuota_v.saldo_venta != 0 then cuota_v.saldo_venta else 0 end)
                        ) as saldo
                from mt_movements as m
                inner join business_groups as bg on bg.id = m.group_id
                left join mt_sales on m.id = mt_sales.movements_id
                left join mt_recibos on mt_recibos.mt_movements_id = m.id
                left join (
                    select sum(saldo_cuota) as saldo_alquiler, group_id 
                    from alquiler
                    Inner join cuotas_alquiler on alquiler.id = cuotas_alquiler.alquiler_id
                    Inner join alquiler_housing on alquiler.id = alquiler_housing.alquiler_id
                    where fecha_vencimiento < now() and saldo_cuota <> 0 and alquiler.deleted_at is null and cod_venta is not null
                    group by group_id order by group_id
                ) cuota_a on bg.id = cuota_a.group_id
                left join (
                    select sum(saldo_cuota) as saldo_venta, group_id 
                    from venta
                    Inner join cuotas on venta.id = cuotas.credito_venta_id
                    Inner join venta_housing on venta.id = venta_housing.venta_id
                    where fecha_vencimiento < now() and saldo_cuota <> 0 and venta.deleted_at is null and cod_venta is not null
                    group by group_id order by group_id
                ) cuota_v on bg.id = cuota_v.group_id
                where
                    ".$whereMovements."
                    m.movement_type_id not in (4, 5, 7, 8, 9, 10)
                    and m.deleted_at is null
                group by bg.id, cuota_a.saldo_alquiler, cuota_v.saldo_venta
                having 
                (   
                    (SUM(CASE ".$whereSales." THEN (m.amount) else 0 END))
                    +(SUM(CASE ".$whereCobranzas." THEN (m.amount) else 0 END))
                    +(SUM(CASE ".$whereReversion." THEN (m.amount) else 0 END))
                    +(SUM(CASE ".$whereCashout." THEN (m.amount) else 0 END))
                    +(SUM(CASE ".$wherePagoQr." THEN (m.amount) else 0 END))
                    + (Case when cuota_a.saldo_alquiler != 0 then cuota_a.saldo_alquiler else 0 end)
                    + (Case when cuota_v.saldo_venta != 0 then cuota_v.saldo_venta else 0 end)
                ) < 0
                order by saldo desc
            ");

            $resultset['transactions_groups'] = $resumen_transacciones_groups;

            return view('pago_cliente.index')->with($resultset);

        }catch (\Exception $e){
            \Log::error("Error en la consulta de reportes" . $e);
            return false;
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request){
        if (!$this->user->hasAnyAccess('pago_clientes.create_txt')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        //header("Refresh:0");
        $input=\Request::all();
        //dd($input);
        $grupo=$input['group'];
        $grupos=implode(', ', $grupo);
        
        $whereMovements = "bg.id in(".$grupos.") AND";
        
        $hasta=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-1 days');

        $whereSales = "WHEN debit_credit = 'de' AND mt_sales.fecha <= '". $hasta . "'";
        $whereCobranzas = "WHEN movement_type_id = 2 AND m.created_at <= '". $hasta . "'";
        $whereReversion = "WHEN movement_type_id = 3 AND m.created_at <= '". $hasta . "'";
        $whereCashout   = "WHEN movement_type_id = 11 AND m.created_at <= '". $hasta . "'";
        $wherePagoQr = "WHEN movement_type_id = 17 AND m.created_at <= '". $hasta . "'";

        $date= "'".date('Y-m-d H:i:s')."'";

        $resumen_transacciones_groups = \DB::select("
            select
                    bg.id as group_id,
                    concat(bg.description,' | ',bg.ruc) as grupo,
                    bg.ruc as ruc,
                    SUM(CASE ".$whereSales." THEN (m.amount) else 0 END) as Debito,
                    SUM(CASE ".$whereCobranzas." THEN (m.amount) else 0 END) +
                    SUM(CASE ".$whereReversion." THEN (m.amount) else 0 END) +
                    SUM(CASE ".$wherePagoQr." THEN (m.amount) else 0 END) +
                    SUM(CASE ".$whereCashout." THEN (m.amount) else 0 END) as Credito,
                    (Case when cuota_a.saldo_alquiler != 0 then cuota_a.saldo_alquiler else 0 end)
                    + (Case when cuota_v.saldo_venta != 0 then cuota_v.saldo_venta else 0 end) as cuotas,
                    (   (SUM(CASE ".$whereSales." THEN (m.amount) else 0 END))
                        +(SUM(CASE ".$whereCobranzas." THEN (m.amount) else 0 END))
                        +(SUM(CASE ".$whereReversion." THEN (m.amount) else 0 END))
                        +(SUM(CASE ".$whereCashout." THEN (m.amount) else 0 END))
                        +(SUM(CASE ".$wherePagoQr." THEN (m.amount) else 0 END))
                        + (Case when cuota_a.saldo_alquiler != 0 then cuota_a.saldo_alquiler else 0 end)
                        + (Case when cuota_v.saldo_venta != 0 then cuota_v.saldo_venta else 0 end)
                    ) as saldo
            from mt_movements as m
            inner join business_groups as bg on bg.id = m.group_id
            left join mt_sales on m.id = mt_sales.movements_id
            left join mt_recibos on mt_recibos.mt_movements_id = m.id
            left join (
                select sum(saldo_cuota) as saldo_alquiler, group_id 
                from alquiler
                Inner join cuotas_alquiler on alquiler.id = cuotas_alquiler.alquiler_id
                Inner join alquiler_housing on alquiler.id = alquiler_housing.alquiler_id
                where fecha_vencimiento < now() and saldo_cuota <> 0 and alquiler.deleted_at is null and cod_venta is not null
                group by group_id order by group_id
            ) cuota_a on bg.id = cuota_a.group_id
            left join (
                select sum(saldo_cuota) as saldo_venta, group_id 
                from venta
                Inner join cuotas on venta.id = cuotas.credito_venta_id
                Inner join venta_housing on venta.id = venta_housing.venta_id
                where fecha_vencimiento < now() and saldo_cuota <> 0 and venta.deleted_at is null and cod_venta is not null
                group by group_id order by group_id
            ) cuota_v on bg.id = cuota_v.group_id
            where
                ".$whereMovements."
                m.movement_type_id not in (4, 5, 7, 8, 9, 10)
                and m.deleted_at is null
            group by bg.id, cuota_a.saldo_alquiler, cuota_v.saldo_venta
            having 
            (   
                (SUM(CASE ".$whereSales." THEN (m.amount) else 0 END))
                +(SUM(CASE ".$whereCobranzas." THEN (m.amount) else 0 END))
                +(SUM(CASE ".$whereReversion." THEN (m.amount) else 0 END))
                +(SUM(CASE ".$whereCashout." THEN (m.amount) else 0 END))
                +(SUM(CASE ".$wherePagoQr." THEN (m.amount) else 0 END))
                + (Case when cuota_a.saldo_alquiler != 0 then cuota_a.saldo_alquiler else 0 end)
                + (Case when cuota_v.saldo_venta != 0 then cuota_v.saldo_venta else 0 end)
            ) < 0
            order by saldo desc
        ");

        $date = \DB::select("
            select concat(TO_CHAR( now(), 'yyyymm'), 
            CASE 
                WHEN 
                    length( EXTRACT(DAY FROM TIMESTAMP $date)::varchar(255) ) > 1 
                THEN 
                    substring( EXTRACT(DAY FROM TIMESTAMP $date)::varchar(255) from 2 for 1)
                ELSE 
                    EXTRACT(DAY FROM TIMESTAMP $date)::varchar(255) 
            END,
            CASE 
                WHEN 
                    length( EXTRACT(hour FROM TIMESTAMP $date)::varchar(255) ) > 1 
                THEN 
                    substring( EXTRACT(hour FROM TIMESTAMP $date)::varchar(255) from 2 for 1)
                ELSE 
                    EXTRACT(hour FROM TIMESTAMP $date)::varchar(255) 
            END,
            CASE 
                WHEN 
                    length( EXTRACT(month FROM TIMESTAMP $date)::varchar(255) ) = 1 
                THEN 
                    concat('0', EXTRACT(month FROM TIMESTAMP $date)::varchar(255) ) 
                ELSE 
                    EXTRACT(month FROM TIMESTAMP $date)::varchar(255) 
            END,
            CASE 
                WHEN 
                    length( EXTRACT(second FROM TIMESTAMP $date)::varchar(255) ) = 1 
                THEN 
                    concat('0', EXTRACT(second FROM TIMESTAMP $date)::varchar(255)) 
                ELSE 
                    EXTRACT(second FROM TIMESTAMP $date)::varchar(255)
            END,
            '305', '699')
        ");
        
        $i=0;
        foreach($resumen_transacciones_groups as $resumen_transacciones_group){

            $ruc= explode('-', $resumen_transacciones_group->ruc );
            $documento[$i]= $ruc[0];
            $saldo[$ruc[0]]= $resumen_transacciones_group->saldo;
            $i++;
        }

        $documentos=implode(', ', $documento);
        
        $clientes =  \DB::connection('ondanet')
            ->table('CATASTRO_PROVEEDORES as ct')
            ->select('ct.MODO_ACREDITACION', 'ct.TIPO_DOCUMENTO', 'ct.NUMERO_DOCUMENTO', 'ct.APELLIDO_PATERNO', 'ct.APELLIDO_MATERNO', 
            'ct.PRIMER_NOMBRE', 'ct.SEGUNDO_NOMBRE', 'ct.NUMERO_CUENTA', 'ct.CUENTA_SIPAP', 'BANCOS_CATASTRO.BIC', 'BANCOS_CATASTRO.DESCRIPCION')
            ->leftJoin('BANCOS_CATASTRO', 'BANCOS_CATASTRO.DESCRIPCION', '=', 'ct.CODIGO_ENTIDAD')
            ->whereRaw("NUMERO_DOCUMENTO  in('".$documentos."')")
        ->get();

        $x=0;
        $total=0;
        //dd($clientes);
        $date_val= date('Ymd');

        header('Content-Type: application/txt');
        header("Content-Disposition: attachment; filename=Pago_Vía_ELO $date_val ".$date[0]->concat.".txt");
        
        foreach($clientes as $cliente){
            $pago[$x]['IMPORTE']    = abs($saldo[$cliente->NUMERO_DOCUMENTO]);

            $total= $total + $pago[$x]['IMPORTE'];
            $x++;
        }

        $num=$date[0]->concat;
        $cant=$x;

        $row = array ('H', '699','','6900',$cant,$total,'N',date('d/m/Y'),
            $num,'D','2603496', '25', '20', '6900', '0', '0', '0', '0', '//', '//', '0', '0'
        );
        
        $txt=implode(";", $row);
        $txt.="\n";

        $file = fopen('php://output', 'a');

        fputs($file, $bom =( chr(0xEF) . chr(0xBB) . chr(0xBF) ));

        fputcsv($file, $row, ";");
        
        foreach($clientes as $cliente){
            
            if($cliente->MODO_ACREDITACION == 'Transf._SIPAP'){
                $modulo_cuenta= 0;
                $modalidad_pago=62;
            }else if($cliente->MODO_ACREDITACION == 'Credito_en_Cta.Cte.'){
                $modulo_cuenta= 20;
                $modalidad_pago=20;
            }else if($cliente->MODO_ACREDITACION == 'Credito_en_Caja_de_Ahorros'){
                $modulo_cuenta= 21;
                $modalidad_pago=21;
            }

            \Log::info($cliente->MODO_ACREDITACION);

            if($cliente->TIPO_DOCUMENTO == 'CEDULA DE IDENTIDAD'){
                $documento_codigo=1;
            }else if($cliente->TIPO_DOCUMENTO == 'R.U.C.'){
                $documento_codigo=3;
            }
            \Log::info($cliente->TIPO_DOCUMENTO);

            $row = array(
                    'D',
                    $cliente->MODO_ACREDITACION,
                    $date[0]->concat,
                    null,
                    $documento_codigo,
                    $cliente->NUMERO_DOCUMENTO,
                    $cliente->APELLIDO_PATERNO,
                    $cliente->APELLIDO_MATERNO,
                    $cliente->PRIMER_NOMBRE,
                    $cliente->SEGUNDO_NOMBRE,
                    6900,
                    abs($saldo[$cliente->NUMERO_DOCUMENTO]),
                    0,
                    null,
                    null,
                    25,
                    $modulo_cuenta,
                    6900,
                    0,
                    (empty($cliente->NUMERO_CUENTA) ? $cliente->CUENTA_SIPAP : $cliente->NUMERO_CUENTA),
                    0,
                    0,
                    0,
                    null,
                    (empty($cliente->NUMERO_CUENTA) ? $cliente->CUENTA_SIPAP : $cliente->NUMERO_CUENTA),
                    (empty($cliente->BIC) ? null : $cliente->BIC),
                    null,
                    $documento_codigo,
                    null,
                    null,
                    $modalidad_pago
            );

            $group =  \DB::table('business_groups')
            ->whereRaw("ruc like '%$cliente->NUMERO_DOCUMENTO%' ")
            ->first();

            $txt.=implode(";", $row);
            \Log::info($group->ruc);

            $pago = new PagoCliente;
            $pago->monto            = abs($saldo[$cliente->NUMERO_DOCUMENTO]);
            $pago->group_id         = $group->id;
            $pago->txt_generated    = $txt;
            $pago->created_by       = $this->user->id;
            $pago->created_at       = Carbon::now();
            $pago->updated_at       = Carbon::now();
            $pago->save();
            fputcsv($file, $row, ";");
        }
        fclose($file);
        //return redirect('/pago_clientes');
        //header("Location: /pago_clientes");
        exit();
        
        //return redirect('/pago_clientes');
        
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

    public function get_atms($id){

        $pago = \DB::table('mt_pago_clientes')
            ->where('id', $id)
        ->first();

        $atms = \DB::table('atms')
        ->select('atms.id as atm_id', 'atms.name', 'business_groups.description as grupo')
        ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
        ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
        ->join('business_groups', 'business_groups.id', '=', 'branches.group_id')
        ->where('business_groups.id', $pago->group_id)
        ->get();

        $details['pago_id']=$id;

        $details['payment_info']='';
        
        $details['payment_info'] .="<select class='form-control select2' id='atm_select'>";
        $details['payment_info'] .="<option value=0 selected>Seleccionar una opción</option>";
        foreach ($atms as $atm) {
            $details['payment_info'] .=
            "<option value=".$atm->atm_id.">".$atm->name.'</option>';
            $details['grupo']=$atm->grupo;
        }
        $details['payment_info'] .='</select>';
        \Log::info($details);
        return $details;
    }

    public function migrate(Request $request){
        if (!$this->user->hasAnyAccess('pago_clientes.create_txt')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $input=\Request::all();
        \DB::beginTransaction();

        try{

            $atm_id=$input['id_atm'];
            $pago_id=$input['id_pago'];

            if($atm_id != 0){

                $pago = \DB::table('mt_pago_clientes')
                    ->where('id', $pago_id)
                ->first();

                $last_balance = \DB::table('mt_movements')->where('atm_id',$atm_id)->orderBy('id','desc')->first();
                if(isset($last_balance)){
                    $balance= $last_balance->balance +(int)$pago->monto;
                    $balance_antes= $last_balance->balance;
                }else{
                    $balance= (int)$pago->monto;
                    $balance_antes=0;
                }

                $movement_id=\DB::table('mt_movements')->insertGetId([
                    'movement_type_id'          => 12,
                    'destination_operation_id'  => 0,
                    'amount'                    => (int)$pago->monto,
                    'debit_credit'              => 'de',
                    'created_at'                => Carbon::now(),
                    'updated_at'                => Carbon::now(),
                    'group_id'                  => $pago->group_id,
                    'atm_id'                    => $atm_id,
                    'balance_antes'             => $balance_antes,
                    'balance'                   => $balance       

                ]);
                
                \DB::table('mt_sales')->insert([
                    'movements_id'       => $movement_id,
                    'estado'            => 'pendiente', 
                    'monto_por_cobrar'  => (int)$pago->monto,
                    'fecha'             => date('Y-m-d 00:00:00', strtotime($pago->created_at)),
                    'fecha_vencimiento' => Carbon::tomorrow(),
                    'balance_affected'  => true,
                    'date_affected'     => Carbon::now(),
                ]);

                \DB::table('mt_pago_clientes')
                ->where('id', $pago_id)
                ->update([
                    'estado' => true,
                    'atm_id' => $atm_id,
                    'updated_by' => $this->user->id,
                    'fecha_proceso' => Carbon::now()
                ]);

                $update_balance_atms = $this->updateBalanceAtms($atm_id, $pago->monto);
                \Log::info($update_balance_atms);

                \DB::commit();
                Session::flash('message', 'Nuevo Pago de Cliente procesados correctamente');
                return redirect('pago_clientes/register_pago');
            }else{
                \DB::rollback();
                Session::flash('error_message', 'Error al seleccionar el atm. Favor intentar nuevamente');
                return redirect()->back();
            }
        }catch (\Exception $e){
            \DB::rollback();
            \Log::critical($e);
            Session::flash('error_message', 'Error al confirmar el pago. Favor intentar nuevamente');
            return redirect()->back()->with('error', 'Error al confirmar el pago. Favor intentar nuevamente');
        }
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

    public function delete(Request $request)
    {

        $id= $request->_id;
        \Log::info($request->_id);

        $message = '';
        $error = '';
        \Log::debug("Intentando eliminar el registro " . $id);

        if ($pago = PagoCliente::find($id)) {
            try {

                \DB::table('mt_pago_clientes')
                    ->where('id', $id)
                    ->update([
                        'deleted_at'    => Carbon::now(),
                        'estado'        => false,
                        'updated_by'    => $this->user->id,
                ]);
                $error = false;
                $message =  'Registro guardado exitosamente';
            } catch (\Exception $e) {
                \Log::error("Error deleting network: " . $e->getMessage());
                $message =  'Error al intentar eliminar el registro';
                $error = true;
            }
        } else {
            $message =  'Registro no encontrado';
            $error = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }

    public function updateBalanceAtms($atm_id, $amount)
    {

        try {

            \Log::info('[updateBalanceAtms] Se procede a afectar el atm ' . $atm_id);

            $balance = \DB::table('balance_atms')->where('atm_id', $atm_id)->first();

            if (isset($balance)) {
                \Log::info('[updateBalanceAtms] Monto a afectar del atm_id ' . $atm_id . ': ' . $amount);

                $transaccionado = $balance->total_transaccionado + $amount;

                \DB::table('balance_atms')
                    ->where('atm_id', $atm_id)
                    ->update([
                        'total_transaccionado'  => (int)$transaccionado
                    ]);
            } else {
                \Log::info('[updateBalanceAtms] Se procede a crear el primer total transaccionado del atm_id' . $atm_id);

                $transaccionado = $amount;

                \DB::table('balance_atms')->insert([
                    'atm_id'                => $atm_id,
                    'total_transaccionado'  => (int)$transaccionado
                ]);
            }

            $response['message'] = '[updateBalanceAtms] El saldo de los atms han sido actualizadas';
            $response['error'] = false;

            return $response;
        } catch (\Exception $e) {
            \Log::error("[updateBalanceAtms] Error  - {$e->getMessage()}");
            \Log::warning($e);
            $response['error'] = true;
            $response['message'] = $e->getMessage();

            \Log::error("[updateBalanceAtms] Error  - {$response}");
            return $response;
        }
    }
}
