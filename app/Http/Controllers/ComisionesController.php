<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Comision;
use App\Services\DepositoBoletaServices;
use Carbon\Carbon;
use Session;

class ComisionesController extends Controller
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
        if (!$this->user->hasAccess('descuento_comision')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $name = $request->get('name');
        $comisiones = Comision::filterAndPaginate($name);
        //$reversiones = Owner::paginate(10);
        return view('recibos_comisiones.index', compact('comisiones', 'name'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    /*public function create()
    {
        if (!$this->user->hasAccess('descuento_comision.add')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $grupos = \DB::table('business_groups')
            ->select(['business_groups.description', 'business_groups.ruc', 'business_groups.id'])
            ->whereNotNull('business_groups.ruc')
        ->get();

        $data_select = [];
        foreach ($grupos as $key => $grupo) {
            $data_select[$grupo->id] = $grupo->ruc . ' | ' . $grupo->description ;
        }

        $resultset = array(
            'grupos'        => $data_select
        );

        return view('recibos_comisiones.create', compact('grupos'))->with($resultset);

    }*/

    public function create()
    {
        if (!$this->user->hasAccess('descuento_comision.add')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $atms = \DB::table('atms')
            ->select(['atms.id', 'atms.name', 'business_groups.ruc'])
            ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
            ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
            ->join('business_groups', 'business_groups.id', '=', 'branches.group_id')
            ->whereIn('atms.owner_id',[16, 21, 25])
            ->whereNotNull('business_groups.ruc')
        ->get();

        $data_select = [];
        foreach ($atms as $key => $atm) {
            $data_select[$atm->id] = $atm->ruc . ' | ' . $atm->name ;
        }

        $resultset = array(
            'atms'        => $data_select
        );
        //dd($grupos);
        return view('recibos_comisiones.create', compact('atms'))->with($resultset);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $input = $request->all();
        \DB::beginTransaction();
        try{

            $total_monto=$input['total_monto'];
            $total_pagar_alquiler=0;
            $total_pagar_venta=0;
            $total_pagar_transacciones=0;

            if($input['total_alquiler'] != 0){
                if($input['total_alquiler'] >= $total_monto){
                    $total_pagar_alquiler= (int)$total_monto;
                    $total_monto=0;
                }else{
                    $total_pagar_alquiler= (int)$input['total_alquiler'];
                    $total_monto -= $input['total_alquiler'];
                }
            }

            if($input['total_venta'] != 0 && $total_monto !=0){
                if($input['total_venta'] >= $total_monto){
                    $total_pagar_venta= (int)$total_monto;
                    $total_monto=0;
                }else{
                    $total_pagar_venta= (int)$input['total_venta'];
                    $total_monto -= $input['total_venta'];
                }
            }

            if($input['total_transacciones'] != 0 && $total_monto !=0){
                if($input['total_transacciones'] >= $total_monto){
                    $total_pagar_transacciones= (int)$total_monto;
                    $total_monto=0;
                }else{
                    $total_pagar_transacciones= (int)$input['total_transacciones'];
                    $total_monto -= $input['total_transacciones'];
                }
            }

            $group = \DB::table('business_groups')
                ->select('business_groups.*')
                ->join('branches', 'business_groups.id', '=', 'branches.group_id')
                ->join('points_of_sale', 'branches.id', '=', 'points_of_sale.branch_id')
                ->where('points_of_sale.atm_id', $input['id_atm'])
            ->first();

            if($total_pagar_transacciones != 0 && $total_pagar_venta == 0 && $total_pagar_alquiler == 0){
                $tipo_recibo=1;
            }else if($total_pagar_transacciones == 0 && $total_pagar_venta != 0 && $total_pagar_alquiler == 0){
                $tipo_recibo=2;
            }else if($total_pagar_transacciones == 0 && $total_pagar_venta == 0 && $total_pagar_alquiler != 0){
                $tipo_recibo=3;
            }else if($total_pagar_transacciones != 0 && $total_pagar_venta != 0 && $total_pagar_alquiler == 0){
                $tipo_recibo=4;
            }else if($total_pagar_transacciones != 0 && $total_pagar_venta == 0 && $total_pagar_alquiler != 0){
                $tipo_recibo=5;
            }else if($total_pagar_transacciones == 0 && $total_pagar_venta != 0 && $total_pagar_alquiler != 0){
                $tipo_recibo=6;
            }else{
                $tipo_recibo=7;
            }

            $details['total_debitar_alquiler']         =$total_pagar_alquiler;
            $details['total_debitar_venta']            =$total_pagar_venta;
            $details['total_debitar_transacciones']    =$total_pagar_transacciones;

            /*$comision['tipo_recibo_comision_id']    = $tipo_recibo;
            $comision['monto']                      = $input['total_monto'];
            $comision['created_by']                 = $this->user->id;
            $comision['group_id']                   = $input['grupo_id'];
            $comision['created_at']                 = Carbon::now();
            $comision['details']                    = $details;

            dd($comision);*/

            $comision = new Comision;
            $comision->tipo_recibo_comision_id  = $tipo_recibo;
            $comision->monto                    = $input['total_monto'];
            $comision->created_by               = $this->user->id;
            $comision->group_id                 = $group->id;
            $comision->atm_id                   = $input['id_atm'];
            $comision->created_at               = Carbon::now();
            $comision->details                  = json_encode($details);
            $comision->save();

            $process = $this->procesar_pagos_comision_v2($comision->id);

            \DB::commit();
            Session::flash('message', 'Registro creado exitosamente');
            return redirect('recibos_comisiones');

        }catch (\Exception $e) {
            \DB::rollback();
            \Log::error("Error saving new Recibo de comision - {$e->getMessage()}");
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

    //Obtener la deuda del cliente con el monto

    public function getBalance($atm_id, $amount)
    {

        $atms = \DB::table('atms')
            ->select(['atms.id', 'atms.name', 'business_groups.ruc', 'business_groups.id as group_id'])
            ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
            ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
            ->join('business_groups', 'business_groups.id', '=', 'branches.group_id')
            ->where('atms.id', $atm_id)
        ->first();

        $amount=(int)str_replace(",","",$amount);
        //1-Checkear monto adeudado por alquiler
        $alquier = $this->obtener_resumen_alquileres_v2($atm_id, $atms->group_id);    
        //2-Checkear monto adeudado por cuotas de venta
        $ventas = $this->obtener_resumen_cuotas_venta_v2($atm_id, $atms->group_id); 
        //3-Checkear monto adeudado por transacciones
        $transacciones = $this->obtener_resumen_transacciones_v2($atm_id);              
        $total_amount = (int)$alquier['amount']+(int)$ventas['amount']+(int)$transacciones['amount'];
        \Log::info($total_amount);
        
        $descontar_deuda=true;
        if((int)$alquier['amount'] == 0 && (int)$ventas['amount'] == 0 && (int)$transacciones['amount'] != 0){
            //Solo si tiene deuda de Transacciones
            //\Log::info('Tiene deuda por Transacciones');
            \Log::info('[Recibos Comisiones] Deuda Transacciones: ' . (int)$transacciones['amount']  . ' y comision ' . $amount);

            if((int)$transacciones['amount'] >= $amount){
                \Log::info('Transacciones');
                $deudas[0]['tipo']= 'Transacciones';
                $deudas[0]['descripcion']= 'Deuda';
                $deudas[0]['total_a_debitar']= $amount;
                $deudas[0]['total_actual']= (int)$transacciones['amount'];
                $deudas[0]['total_pendiente']= (int)$transacciones['amount'] - $amount;
            }else{
                //No tiene deuda suficiente para cobrar la comision
                $descontar_deuda=false;
                \Log::info('[Recibos Comisiones] No tiene deuda suficiente');
            }
        }else if((int)$alquier['amount'] == 0 && (int)$ventas['amount'] != 0 && (int)$transacciones['amount'] == 0){
            //Solo si tiene deuda de Ventas
            //\Log::info('[Recibos Comisiones] Tiene deuda por Cuotas de Venta');
            if((int)$ventas['amount'] >= $amount){
                \Log::info('[Recibos Comisiones] Cuota de Venta');

                $i=0;
                $sum=0;
                $debitar=$amount;
                $cuotas=json_decode(json_encode($ventas['cuotas']), true);
                //\Log::info($cuotas);
                do{

                    $sum += $cuotas[$i]['importe'];
                    $deudas[$i]['tipo']= 'Cuota de Venta';
                    $deudas[$i]['descripcion']= 'Cuota Numero '. $cuotas[$i]['numero_cuota'];
                    $deudas[$i]['total_a_debitar']= $debitar;
                    $deudas[$i]['total_actual']= $cuotas[$i]['saldo_cuota'];
                    $saldo= $cuotas[$i]['saldo_cuota'] - $debitar;
                    $saldo_pendiente= ( ($saldo < 0) ? 0 : $saldo);
                    $deudas[$i]['total_pendiente']= $saldo_pendiente;
                    $debitar -= $cuotas[$i]['saldo_cuota'];
                    $i++;
                    
                }while($sum < $amount);
            }else{
                //No tiene deuda suficiente para cobrar la comision
                $descontar_deuda=false;
                \Log::info('[Recibos Comisiones] No tiene deuda suficiente');
            }
        }else if((int)$alquier['amount'] != 0 && (int)$ventas['amount'] == 0 && (int)$transacciones['amount'] == 0){
            //Solo si tiene deuda de Alquiler
            //\Log::info('Tiene deuda por Cuotas de Alquiler');
            \Log::info('[Recibos Comisiones]  Deuda alquiler: ' . (int)$alquier['amount']  . ' y comision ' . $amount);
            if((int)$alquier['amount'] >= $amount){
                
                \Log::info('Cuota de Alquier');

                $i=0;
                $sum=0;
                $debitar=$amount;
                $cuotas=json_decode(json_encode($alquier['cuotas']), true);
                //\Log::info($cuotas);
                do{

                    $sum += $cuotas[$i]['importe'];
                    $deudas[$i]['tipo']= 'Cuota de Alquiler';
                    $deudas[$i]['descripcion']= 'Cuota Numero '. $cuotas[$i]['num_cuota'];
                    $deudas[$i]['total_a_debitar']= $debitar;
                    $deudas[$i]['total_actual']= $cuotas[$i]['saldo_cuota'];
                    $saldo= $cuotas[$i]['saldo_cuota'] - $debitar;
                    $saldo_pendiente= ( ($saldo < 0) ? 0 : $saldo);
                    $deudas[$i]['total_pendiente']= $saldo_pendiente;
                    $debitar -= $cuotas[$i]['saldo_cuota'];
                    $i++;
                    
                }while($sum < $amount);
                
            }else{
                //No tiene deuda suficiente para cobrar la comision
                $descontar_deuda=false;
                \Log::info('[Recibos Comisiones] No tiene deuda suficiente');
            }
        }else if((int)$alquier['amount'] == 0 && (int)$ventas['amount'] != 0 && (int)$transacciones['amount'] != 0){
            //Solo si tiene deuda de Cuota de Venta y Transacciones
            //\Log::info('Tiene deuda por Transacciones y Cuota de Venta');
            if((int)$ventas['amount'] >= $amount){
                
                //Solo cobra cuota de Venta
                \Log::info('[Recibos Comisiones] Cuota de Venta');

                $i=0;
                $sum=0;
                $debitar=$amount;
                $cuotas=json_decode(json_encode($ventas['cuotas']), true);
                //\Log::info($cuotas);
                do{

                    $sum += $cuotas[$i]['importe'];
                    $deudas[$i]['tipo']= 'Cuota de Venta';
                    $deudas[$i]['descripcion']= 'Cuota Numero '. $cuotas[$i]['numero_cuota'];
                    $deudas[$i]['total_a_debitar']= $debitar;
                    $deudas[$i]['total_actual']= $cuotas[$i]['saldo_cuota'];
                    $saldo= $cuotas[$i]['saldo_cuota'] - $debitar;
                    $saldo_pendiente= ( ($saldo < 0) ? 0 : $saldo);
                    $deudas[$i]['total_pendiente']= $saldo_pendiente;
                    $debitar -= $cuotas[$i]['saldo_cuota'];
                    $i++;
                    
                }while($sum < $amount);
                
            }else{
                //Cobra cuota de Venta Y transacciones
                $monto_ventas_tran= (int)$ventas['amount'] + (int)$transacciones['amount'];
                if($monto_ventas_tran >= $amount){
                    
                    \Log::info('[Recibos Comisiones] Cuota de Venta y transacciones');

                    $i=0;
                    $sum=0;
                    $debitar=$amount;
                    $cuotas=json_decode(json_encode($ventas['cuotas']), true);
                    
                    do{

                        $sum += $cuotas[$i]['importe'];
                        $deudas[$i]['tipo']= 'Cuota de Venta';
                        $deudas[$i]['descripcion']= 'Cuota Numero '. $cuotas[$i]['numero_cuota'];
                        $deudas[$i]['total_a_debitar']= $debitar;
                        $deudas[$i]['total_actual']= $cuotas[$i]['saldo_cuota'];
                        $saldo= $cuotas[$i]['saldo_cuota'] - $debitar;
                        $saldo_pendiente= ( ($saldo < 0) ? 0 : $saldo);
                        $deudas[$i]['total_pendiente']= $saldo_pendiente;
                        $debitar -= $cuotas[$i]['saldo_cuota'];
                        $i++;
                        
                    }while($i < count($cuotas));

                    $deudas[$i+1]['tipo']= 'Transacciones';
                    $deudas[$i+1]['descripcion']= 'Deuda';
                    $deudas[$i+1]['total_a_debitar']= $debitar;
                    $deudas[$i+1]['total_actual']= (int)$transacciones['amount'];
                    $deudas[$i+1]['total_pendiente']= (int)$transacciones['amount'] - $debitar;
                }else{
                    //No tiene deuda suficiente para cobrar la comision
                    $descontar_deuda=false;
                    \Log::info('[Recibos Comisiones] No tiene deuda suficiente');
                }
            }
        }else if((int)$alquier['amount'] != 0 && (int)$ventas['amount'] == 0 && (int)$transacciones['amount'] != 0){
            //Solo si tiene deuda de Cuota de Alquiler y Transacciones
            //\Log::info('Tiene deuda por Transacciones y Cuota de Alquiler');
            if((int)$alquier['amount'] >= $amount){
                //Solo cobra cuota de Alquiler
                \Log::info('[Recibos Comisiones] Cuota de Alquier');

                $i=0;
                $sum=0;
                $debitar=$amount;
                $cuotas=json_decode(json_encode($alquier['cuotas']), true);
                //\Log::info($cuotas);
                do{

                    $sum += $cuotas[$i]['importe'];
                    $deudas[$i]['tipo']= 'Cuota de Alquiler';
                    $deudas[$i]['descripcion']= 'Cuota Numero '. $cuotas[$i]['num_cuota'];
                    $deudas[$i]['total_a_debitar']= $debitar;
                    $deudas[$i]['total_actual']= $cuotas[$i]['saldo_cuota'];
                    $saldo= $cuotas[$i]['saldo_cuota'] - $debitar;
                    $saldo_pendiente= ( ($saldo < 0) ? 0 : $saldo);
                    $deudas[$i]['total_pendiente']= $saldo_pendiente;
                    $debitar -= $cuotas[$i]['saldo_cuota'];
                    $i++;
                    
                }while($sum < $amount);
            }else{
                $monto_alquiler_tran= (int)$alquier['amount'] + (int)$transacciones['amount'];
                if($monto_alquiler_tran >= $amount){
                    //Cobra cuota de Alquiler Y transacciones
                    \Log::info('[Recibos Comisiones] Cuota de Alquier y transacciones');

                    $i=0;
                    $sum=0;
                    $debitar=$amount;
                    $cuotas=json_decode(json_encode($alquier['cuotas']), true);
                    
                    do{

                        $sum += $cuotas[$i]['importe'];
                        $deudas[$i]['tipo']= 'Cuota de Alquiler';
                        $deudas[$i]['descripcion']= 'Cuota Numero '. $cuotas[$i]['num_cuota'];
                        $deudas[$i]['total_a_debitar']= $debitar;
                        $deudas[$i]['total_actual']= $cuotas[$i]['saldo_cuota'];
                        $saldo= $cuotas[$i]['saldo_cuota'] - $debitar;
                        $saldo_pendiente= ( ($saldo < 0) ? 0 : $saldo);
                        $deudas[$i]['total_pendiente']= $saldo_pendiente;
                        $debitar -= $cuotas[$i]['saldo_cuota'];
                        $i++;
                        
                    }while($i < count($cuotas));

                    $deudas[$i+1]['tipo']= 'Transacciones';
                    $deudas[$i+1]['descripcion']= 'Deuda';
                    $deudas[$i+1]['total_a_debitar']= $debitar;
                    $deudas[$i+1]['total_actual']= (int)$transacciones['amount'];
                    $deudas[$i+1]['total_pendiente']= (int)$transacciones['amount'] - $debitar;
                }else{
                    //No tiene deuda suficiente para cobrar la comision
                    $descontar_deuda=false;
                    \Log::info('[Recibos Comisiones] No tiene deuda suficiente');
                }
            }
        }else if((int)$alquier['amount'] != 0 && (int)$ventas['amount'] != 0 && (int)$transacciones['amount'] == 0){
            //Solo si tiene deuda de Cuota de Alquiler y Cuota de Venta
            //\Log::info('Tiene deuda por Cuotas de Venta y Cuota de Alquiler');
            if((int)$alquier['amount'] >= $amount){
                //Solo cobra cuota de Alquiler
                \Log::info('[Recibos Comisiones] Cuota de Alquier');

                $i=0;
                $sum=0;
                $debitar=$amount;
                $cuotas=json_decode(json_encode($alquier['cuotas']), true);
                //\Log::info($cuotas);
                do{

                    $sum += $cuotas[$i]['importe'];
                    $deudas[$i]['tipo']= 'Cuota de Alquiler';
                    $deudas[$i]['descripcion']= 'Cuota Numero '. $cuotas[$i]['num_cuota'];
                    $deudas[$i]['total_a_debitar']= $debitar;
                    $deudas[$i]['total_actual']= $cuotas[$i]['saldo_cuota'];
                    $saldo= $cuotas[$i]['saldo_cuota'] - $debitar;
                    $saldo_pendiente= ( ($saldo < 0) ? 0 : $saldo);
                    $deudas[$i]['total_pendiente']= $saldo_pendiente;
                    $debitar -= $cuotas[$i]['saldo_cuota'];
                    $i++;
                    
                }while($sum < $amount);
                
            }else{
                $monto_alquiler_venta= (int)$alquier['amount'] + (int)$ventas['amount'];
                if($monto_alquiler_venta >= $amount){
                    //Cobra cuota de Alquiler Y Venta
                    \Log::info('[Recibos Comisiones] Cuota de Alquier y cuota de Venta');

                    $i=0;
                    $sum=0;
                    $debitar=$amount;
                    $cuotas=json_decode(json_encode($alquier['cuotas']), true);
                    //\Log::info($cuotas);
                    do{

                        $sum += $cuotas[$i]['importe'];
                        $deudas[$i]['tipo']= 'Cuota de Alquiler';
                        $deudas[$i]['descripcion']= 'Cuota Numero '. $cuotas[$i]['num_cuota'];
                        $deudas[$i]['total_a_debitar']= $debitar;
                        $deudas[$i]['total_actual']= $cuotas[$i]['saldo_cuota'];
                        $saldo= $cuotas[$i]['saldo_cuota'] - $debitar;
                        $saldo_pendiente= ( ($saldo < 0) ? 0 : $saldo);
                        $deudas[$i]['total_pendiente']= $saldo_pendiente;
                        $debitar -= $cuotas[$i]['saldo_cuota'];
                        $i++;
                        
                    }while($sum < count($cuotas));

                    $sum=0;
                    $debitar=$amount;
                    $cuotas=json_decode(json_encode($alquier['cuotas']), true);
                    //\Log::info($cuotas);
                    do{

                        $sum += $cuotas[$i]['importe'];
                        $deudas[$i]['tipo']= 'Cuota de Venta';
                        $deudas[$i]['descripcion']= 'Cuota Numero '. $cuotas[$i]['num_cuota'];
                        $deudas[$i]['total_a_debitar']= $debitar;
                        $deudas[$i]['total_actual']= $cuotas[$i]['saldo_cuota'];
                        $saldo= $cuotas[$i]['saldo_cuota'] - $debitar;
                        $saldo_pendiente= ( ($saldo < 0) ? 0 : $saldo);
                        $deudas[$i]['total_pendiente']= $saldo_pendiente;
                        $debitar -= $cuotas[$i]['saldo_cuota'];
                        $i++;
                        
                    }while($sum < $amount);
                }else{
                    //No tiene deuda suficiente para cobrar la comision
                    $descontar_deuda=false;
                    \Log::info('[Recibos Comisiones] No tiene deuda suficiente');
                }
            }
        }else if((int)$alquier['amount'] != 0 && (int)$ventas['amount'] != 0 && (int)$transacciones['amount'] != 0){
            //\Log::info('Tiene deuda por Cuotas de Venta y Cuota de Alquiler y transacciones');
            //Solo si tiene deuda de Cuota de Alquiler y Cuota de Venta y Transacciones
            if((int)$alquier['amount'] >= $amount){
                //Solo cobra cuota de Alquiler
                \Log::info('[Recibos Comisiones] Cuota de Alquier');

                $i=0;
                $sum=0;
                $debitar=$amount;
                $cuotas=json_decode(json_encode($alquier['cuotas']), true);
                //\Log::info($cuotas);
                do{

                    $sum += $cuotas[$i]['importe'];
                    $deudas[$i]['tipo']= 'Cuota de Alquiler';
                    $deudas[$i]['descripcion']= 'Cuota Numero '. $cuotas[$i]['num_cuota'];
                    $deudas[$i]['total_a_debitar']= $debitar;
                    $deudas[$i]['total_actual']= $cuotas[$i]['saldo_cuota'];
                    $saldo= $cuotas[$i]['saldo_cuota'] - $debitar;
                    $saldo_pendiente= ( ($saldo < 0) ? 0 : $saldo);
                    $deudas[$i]['total_pendiente']= $saldo_pendiente;
                    $debitar -= $cuotas[$i]['saldo_cuota'];
                    $i++;
                    
                }while($sum < $amount);
            }else{
                $monto_alquiler_venta= (int)$alquier['amount'] + (int)$ventas['amount'];
                //dd($monto_alquiler_venta);
                if($monto_alquiler_venta >= $amount){
                    //Cobra cuota de Alquiler Y Venta
                    \Log::info('[Recibos Comisiones] Cuota de Alquier y cuota de Venta');

                    $i=0;
                    $sum=0;
                    $debitar=$amount;
                    $cuotas=json_decode(json_encode($alquier['cuotas']), true);
                    //dd((int)$ventas['amount']);
                    do{

                        $sum += $cuotas[$i]['importe'];
                        $deudas[$i]['tipo']= 'Cuota de Alquiler';
                        $deudas[$i]['descripcion']= 'Cuota Numero '. $cuotas[$i]['num_cuota'];
                        $deudas[$i]['total_a_debitar']= $debitar;
                        $deudas[$i]['total_actual']= $cuotas[$i]['saldo_cuota'];
                        $saldo= $cuotas[$i]['saldo_cuota'] - $debitar;
                        $saldo_pendiente= ( ($saldo < 0) ? 0 : $saldo);
                        $deudas[$i]['total_pendiente']= $saldo_pendiente;
                        $debitar -= $cuotas[$i]['saldo_cuota'];
                        $i++;
                        
                    }while($sum < count($cuotas));
                    //dd($deudas);
                    $sum=0;
                    $saldo_debitar=$debitar;
                    $cuotas_ventas=json_decode(json_encode($ventas['cuotas']), true);
                    //dd($cuotas_ventas);
                    do{

                        $sum += $cuotas_ventas[$i]['importe'];
                        $deudas[$i]['tipo']= 'Cuota de Venta';
                        $deudas[$i]['descripcion']= 'Cuota Numero '. $cuotas_ventas[$i]['numero_cuota'];
                        $deudas[$i]['total_a_debitar']= $debitar;
                        $deudas[$i]['total_actual']= $cuotas_ventas[$i]['saldo_cuota'];
                        $saldo= $cuotas_ventas[$i]['saldo_cuota'] - $debitar;
                        $saldo_pendiente= ( ($saldo < 0) ? 0 : $saldo);
                        $deudas[$i]['total_pendiente']= $saldo_pendiente;
                        $debitar -= $cuotas_ventas[$i]['saldo_cuota'];
                        $i++;
                        \Log::info($deudas);
                    }while($sum < $saldo_debitar);

                    //dd($deudas);
                }else{
                    $monto_alquiler_venta_t= (int)$alquier['amount'] + (int)$ventas['amount'] + (int)$transacciones['amount'];
                    if($monto_alquiler_venta_t > $amount){
                        //Cobra Cuota de Alquiler y Cuota de Venta y Transacciones
                        \Log::info('[Recibos Comisiones] Cuota de Alquier y cuota de Venta y Transacciones');


                        $i=0;
                        $sum=0;
                        $debitar=$amount;
                        $cuotas=json_decode(json_encode($alquier['cuotas']), true);
                        dd($cuotas);
                        do{

                            $sum += $cuotas[$i]['importe'];
                            $deudas[$i]['tipo']= 'Cuota de Alquiler';
                            $deudas[$i]['descripcion']= 'Cuota Numero '. $cuotas[$i]['num_cuota'];
                            $deudas[$i]['total_a_debitar']= $debitar;
                            $deudas[$i]['total_actual']= $cuotas[$i]['saldo_cuota'];
                            $saldo= $cuotas[$i]['saldo_cuota'] - $debitar;
                            $saldo_pendiente= ( ($saldo < 0) ? 0 : $saldo);
                            $deudas[$i]['total_pendiente']= $saldo_pendiente;
                            $debitar -= $cuotas[$i]['saldo_cuota'];
                            $i++;
                            
                        }while($sum < count($cuotas));

                        $sum=0;
                        $saldo_debitar=$debitar;
                        $cuotas=json_decode(json_encode($ventas['cuotas']), true);

                        do{

                            $sum += $cuotas[$i]['importe'];
                            $deudas[$i]['tipo']= 'Cuota de Venta';
                            $deudas[$i]['descripcion']= 'Cuota Numero '. $cuotas[$i]['numero_cuota'];
                            $deudas[$i]['total_a_debitar']= $debitar;
                            $deudas[$i]['total_actual']= $cuotas[$i]['saldo_cuota'];
                            $saldo= $cuotas[$i]['saldo_cuota'] - $debitar;
                            $saldo_pendiente= ( ($saldo < 0) ? 0 : $saldo);
                            $deudas[$i]['total_pendiente']= $saldo_pendiente;
                            $debitar -= $cuotas[$i]['saldo_cuota'];
                            $i++;
                            
                        }while($i < count($cuotas));

                        $deudas[$i+1]['tipo']= 'Transacciones';
                        $deudas[$i+1]['descripcion']= 'Deuda';
                        $deudas[$i+1]['total_a_debitar']= $debitar;
                        $deudas[$i+1]['total_actual']= (int)$transacciones['amount'];
                        $deudas[$i+1]['total_pendiente']= (int)$transacciones['amount'] - $debitar;
                    }else{
                        //No tiene deuda suficiente para cobrar la comision
                        $descontar_deuda=false;
                        \Log::info('[Recibos Comisiones] No tiene deuda suficiente');
                    }
                }
            }
        }else{
            //No tiene deuda
            $descontar_deuda=false;
            \Log::info('[Recibos Comisiones] No tiene deuda'); 
        }

        if($descontar_deuda){
            \Log::info($deudas);
            $details['payment_info']='';
            foreach ($deudas as $deuda) {
                $details['payment_info'] .=
                '<tr><td>'.$deuda['tipo'].'</td>
                <td>'.$deuda['descripcion'].'</td>
                <td>'.number_format($deuda['total_a_debitar']).'</td>
                <td>'.number_format($deuda['total_actual']).'</td>
                <td>'.number_format($deuda['total_pendiente']).'</td></tr>';
            }
        }
        
        $details['details']['alquiler']         = $alquier['amount'];
        $details['details']['ventas']           = $ventas['amount'];
        $details['details']['transaccionado']   = $transacciones['amount'];
        $details['total_amount']                = $total_amount;
        $details['total_comision']              = $amount;
        $details['grupo']                       = $atms->ruc . ' | '. $atms->name;
        $details['atm_id']                      = $atms->id;
        $details['descontar_deuda']             = $descontar_deuda;

        \Log::info($details);

        return $details;
    }

    private function obtener_resumen_alquileres($group_id)
    {
        
        $date = Carbon::now()->format('Y-m-d H:i:s');        
        $cuotas_alquiler = \DB::table('alquiler')
        ->select('alquiler.id','cuotas_alquiler.num_cuota','saldo_cuota','fecha_vencimiento','cuotas_alquiler.*')
        ->join('cuotas_alquiler','alquiler.id','=','cuotas_alquiler.alquiler_id')
        ->where('group_id','=',$group_id)
        ->where('fecha_vencimiento','<',$date)
        ->where('saldo_cuota','<>',0)
        ->whereNull('alquiler.deleted_at')
        ->orderBy('alquiler.id','asc')
        ->orderBy('cuotas_alquiler.num_cuota','asc')
        ->get();                
        
        $monto_alquiler = 0;        
        $id_alquiler = null;
        foreach($cuotas_alquiler as $cuota)
        {            
            $id_alquiler = $cuota->id;
            $monto_alquiler = $monto_alquiler + $cuota->saldo_cuota;                        
        }
        
        
        $alquiler = [
            'id'        => $id_alquiler,
            'amount'    => $monto_alquiler,
            'cuotas'    => $cuotas_alquiler
        ];
        
        return $alquiler;
    }

    private function obtener_resumen_cuotas_venta($group_id)
    {
        
        $date = Carbon::now()->format('Y-m-d H:i:s');        
        $cuotas_ventas = \DB::table('venta')
        ->select('id','fecha_vencimiento','saldo_cuota','cuotas.numero_cuota','cuotas.*')
        ->join('cuotas','venta.id','=','cuotas.credito_venta_id')
        ->where('group_id','=',$group_id)
        ->where('fecha_vencimiento','<',$date)
        ->where('saldo_cuota','<>',0)
        ->whereNull('venta.deleted_at')
        ->orderBy('id','asc')
        ->orderBy('cuotas.numero_cuota','asc')
        ->get();        
        
        $monto_venta = 0;   
        $num_cuotas = array();
        $id_venta = null;     
        foreach($cuotas_ventas as $cuota)
        {
            $id_venta       = $cuota->id;
            $monto_venta    = $monto_venta + $cuota->saldo_cuota;            
        }
        
        $venta = [
            'id'        => $id_venta,
            'amount'    => $monto_venta,
            'cuotas'    => $cuotas_ventas
        ];

        return $venta;
    }

    private function obtener_resumen_transacciones($group_id)
    {
        
        try
        {
            $whereCobranzas = "WHEN movement_type_id = 2 AND movements.created_at <= now() ";
            $whereReversion = "WHEN movement_type_id = 3 AND movements.created_at <= now() ";
            $whereCashout   = "WHEN movement_type_id = 11 AND movements.created_at <= now() ";
            $whereMovements= '';
    
            /*$date=date('N');            
            if($date == 1 || $date==3 ||$date==5){
                $hasta=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-1 days');                
            }else if($date == 2 || $date==4 ||$date==6){
                $hasta=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-2 days');
            }else{
                $hasta=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-3 days');
            }*/

            $hasta=Carbon::parse(date('Y-m-d 23:59:59'))->modify('-1 days');   
    
            $whereSales = "WHEN debit_credit = 'de' AND miniterminales_sales.fecha <= '". $hasta . "'";
            $whereMovements .= "business_groups.id = ". $group_id . " AND";                                
            $resumen_transacciones_groups = \DB::select(\DB::raw("
                    select
                            current_account.group_id,
                            business_groups.description as grupo,
                            SUM(CASE ".$whereSales." THEN (movements.amount) else 0 END) as transacciones,
                            SUM(CASE ".$whereCobranzas." THEN (movements.amount) else 0 END) as depositos,
                            SUM(CASE ".$whereReversion." THEN (movements.amount) else 0 END) as reversiones,
                            SUM(CASE ".$whereCashout." THEN (movements.amount) else 0 END) as cashouts,
                            (   (SUM(CASE ".$whereSales." THEN (movements.amount) else 0 END))
                                +(SUM(CASE ".$whereCobranzas." THEN (movements.amount) else 0 END))
                                +(SUM(CASE ".$whereReversion." THEN (movements.amount) else 0 END))
                                +(SUM(CASE ".$whereCashout." THEN (movements.amount) else 0 END))
                            ) as saldo
                    from movements
                    inner join current_account on movements.id = current_account.movement_id
                    inner join business_groups on business_groups.id = current_account.group_id                    
                    left join miniterminales_sales on movements.id = miniterminales_sales.movements_id
                    where
                        ".$whereMovements."
                        movements.movement_type_id not in (4, 5, 7, 8, 9, 10)
                        and movements.destination_operation_id not in ('0','1','-2','-3','-4','-5','-6','-9','-10','-11','-12','-13','-14','-16','-16','-17','-21','-23','-26',-27,212, 999)
                        and movements.deleted_at is null
                    group by current_account.group_id, grupo
                    order by saldo desc
                "));
            
            $saldo = 0;                                    
            foreach($resumen_transacciones_groups as $resumen)
            {
                $saldo = $saldo + $resumen->saldo;
            }    
    
            $transactions = [
                'amount' => $saldo
            ];
    
            return  $transactions;
        }catch(\Exception $e){
            \Log::debug($e);
        }        
    }

    private function obtener_resumen_alquileres_v2($atm_id, $group_id)
    {
        
        $date = Carbon::now()->format('Y-m-d H:i:s');        
        $cuotas_alquiler = \DB::table('alquiler')
        ->select('alquiler.id','cuotas_alquiler.num_cuota','saldo_cuota','fecha_vencimiento','cuotas_alquiler.*')
        ->join('cuotas_alquiler','alquiler.id','=','cuotas_alquiler.alquiler_id')
        ->join('alquiler_housing','alquiler.id','=','alquiler_housing.alquiler_id')
        ->join('atms','alquiler_housing.housing_id','=','atms.housing_id')
        ->where('atms.id','=',$atm_id)
        ->where('alquiler.group_id','=',$group_id)
        ->where('fecha_vencimiento','<',$date)
        ->where('saldo_cuota','<>',0)
        ->whereNotNull('cod_venta')
        ->whereNull('alquiler.deleted_at')
        ->orderBy('alquiler.id','asc')
        ->orderBy('cuotas_alquiler.num_cuota','asc')
        ->get();                
        
        $monto_alquiler = 0;        
        $id_alquiler = null;
        foreach($cuotas_alquiler as $cuota)
        {            
            $id_alquiler = $cuota->id;
            $monto_alquiler = $monto_alquiler + $cuota->saldo_cuota;                        
        }
        
        
        $alquiler = [
            'id'        => $id_alquiler,
            'amount'    => $monto_alquiler,
            'cuotas'    => $cuotas_alquiler
        ];
        
        return $alquiler;
    }

    private function obtener_resumen_cuotas_venta_v2($atm_id, $group_id)
    {
        
        $date = Carbon::now()->format('Y-m-d H:i:s');        
        $cuotas_ventas = \DB::table('venta')
        ->select('venta.id','fecha_vencimiento','saldo_cuota','cuotas.numero_cuota','cuotas.*')
        ->join('cuotas','venta.id','=','cuotas.credito_venta_id')
        ->join('venta_housing','venta.id','=','venta_housing.venta_id')
        ->join('atms','venta_housing.housing_id','=','atms.housing_id')
        ->where('atms.id','=',$atm_id)
        ->where('venta.group_id','=',$group_id)
        ->where('fecha_vencimiento','<',$date)
        ->where('saldo_cuota','<>',0)
        ->whereNull('venta.deleted_at')
        ->orderBy('id','asc')
        ->orderBy('cuotas.numero_cuota','asc')
        ->get();        
        
        $monto_venta = 0;   
        $num_cuotas = array();
        $id_venta = null;     
        foreach($cuotas_ventas as $cuota)
        {
            $id_venta       = $cuota->id;
            $monto_venta    = $monto_venta + $cuota->saldo_cuota;            
        }
        
        $venta = [
            'id'        => $id_venta,
            'amount'    => $monto_venta,
            'cuotas'    => $cuotas_ventas
        ];

        return $venta;
    }

    private function obtener_resumen_transacciones_v2($atm_id)
    {
        
        try
        {
            $total = \DB::table('balance_atms')
                ->selectRaw("SUM(total_transaccionado_cierre) as transaccionado, SUM(total_depositado) as depositado,
                        SUM(total_reversado) as reversado, SUM(total_cashout) as cashout, SUM(total_pago_cashout) as pago_cashout, SUM(total_pago_qr) as qr,  SUM(total_multa) as multa")
                ->where('atm_id', $atm_id)
            ->first();
            
            $haber = -abs($total->depositado);
            $debe = abs($total->transaccionado) + abs($total->pago_cashout) + abs($total->multa);
            $reversion = -abs($total->reversado);
            $cashout = -abs($total->cashout);
            $qr = -abs($total->qr);
            
            $total_saldo = $haber + $debe + $reversion + $cashout + $qr; 
    
            $response = [
                'message'           => 'Consulta exitosa',
                'error'             => false,
                'amount'        => $total_saldo,
                'transaccionado'    => $debe,
                'depositado'        => $haber,
                'reversado'         => $reversion,
                'cashout'           => $cashout,
                'qr'                => $qr
            ];

            \Log::warning('[CONSULTA TRANSACCIONES COMISION]', $response);
            return $response;
        }catch(\Exception $e){
            \Log::debug($e);
        }        
    }

    //TAREA PROGRAMADA PARA PROCESAR RECIBO DE COMISION
    public function procesar_pagos_comision(){
        
        try{
            $comisiones = \DB::table('mt_recibos_comisiones')->where('estado', 'pendiente')->take(20)->orderBy('id', 'DESC')->get();
            //dd($comisiones);
            foreach($comisiones as $comision){
                \DB::beginTransaction();
                $details=json_decode($comision->details, true);
                //dd($comision);
                if($details['total_debitar_alquiler'] != 0){
                    $alquiler_payment = $this->alquiler_comision_process($comision->group_id, $details['total_debitar_alquiler'], $comision->id);
                }else{
                    $alquiler_payment['error']=false;
                }

                if($details['total_debitar_venta'] != 0){
                    $venta_payment = $this->venta_comision_process($comision->group_id, $details['total_debitar_venta'], $comision->id);
                }else{
                    $venta_payment['error']=false;
                }

                if($details['total_debitar_transacciones'] != 0){
                    $transaccion_payment = $this->transacciones_comision_process($comision->group_id, $details['total_debitar_transacciones'], $comision->id);
                }else{
                    $transaccion_payment['error']=false;
                }

                if($alquiler_payment['error'] == false && $venta_payment['error'] == false && $transaccion_payment['error'] == false){
                    \DB::commit();
                    \DB::table('mt_recibos_comisiones')
                        ->where('id', $comision->id)
                        ->update([
                            'estado' => 'procesado',
                    ]);
                }else{
                    \DB::rollback();
                    \DB::table('mt_recibos_comisiones')
                        ->where('id', $comision->id)
                        ->update([
                            'estado' => 'error',
                    ]);

                }
            }

            $response['message']    = 'Se han procesado todos los descuentos';
            $response['error']      = false;
            return $response;

        }catch(\Exception $e){
            \Log::warning($e);
            $response['message']    = 'Ocurrio un error al procesar transacciones';
            $response['error']      = true;
            return $response;
        }   
    }

    //TAREA PROGRAMADA PARA PROCESAR RECIBO DE COMISION
    public function procesar_pagos_comision_v2($comision_id){
        
        try{

            $comisiones = \DB::table('mt_recibos_comisiones')->where('id', $comision_id)->get();
            
            foreach($comisiones as $comision){
                \DB::beginTransaction();
                $details=json_decode($comision->details, true);
                
                if($details['total_debitar_alquiler'] != 0){
                    $alquiler_payment = $this->alquiler_comision_process_v3($comision->atm_id, $comision->group_id, $details['total_debitar_alquiler'], $comision->id);
                }else{
                    $alquiler_payment['error']=false;
                }

                if($details['total_debitar_venta'] != 0){
                    $venta_payment = $this->venta_comision_process_v3($comision->atm_id, $comision->group_id, $details['total_debitar_venta'], $comision->id);
                }else{
                    $venta_payment['error']=false;
                }

                //dd($comisiones);

                if($details['total_debitar_transacciones'] != 0){
                    $transaccion_payment = $this->transacciones_comision_process_v2($comision->group_id, $details['total_debitar_transacciones'], $comision->id, $comision->atm_id);
                }else{
                    $transaccion_payment['error']=false;
                }

                if($alquiler_payment['error'] == false && $venta_payment['error'] == false && $transaccion_payment['error'] == false){
                    \DB::commit();
                    \DB::table('mt_recibos_comisiones')
                        ->where('id', $comision->id)
                        ->update([
                            'estado' => 'procesado',
                    ]);
                }else{
                    \DB::rollback();
                    \DB::table('mt_recibos_comisiones')
                        ->where('id', $comision->id)
                        ->update([
                            'estado' => 'error',
                    ]);

                }
            }

            $response['message']    = 'Se han procesado todos los descuentos';
            $response['error']      = false;
            return $response;

        }catch(\Exception $e){
            \Log::warning($e);
            $response['message']    = 'Ocurrio un error al procesar transacciones';
            $response['error']      = true;
            return $response;
        }   
    }

    //Proceso para afectar la cuota el recibo de comision
    private function alquiler_comision_process_v2($atm_id, $group_id, $monto, $comision_id){

        \DB::beginTransaction();
        try{

            $housing = \DB::table('cuotas_alquiler')
                    ->select('cuotas_alquiler.*', 'branches.group_id', 'alquiler.id as alquiler_id')
                    ->join('alquiler', 'alquiler.id', '=', 'cuotas_alquiler.alquiler_id')
                    ->join('alquiler_housing', 'alquiler.id', '=', 'alquiler_housing.alquiler_id')
                    ->join('atms', 'atms.housing_id', '=', 'alquiler_housing.housing_id')
                    ->join('branches', 'branches.group_id', '=', 'alquiler.group_id')
                    ->where('cuotas_alquiler.saldo_cuota', '!=', 0)
                    ->whereNotNull('cuotas_alquiler.cod_venta')
                    ->where('atms.id', $atm_id)
                    ->where('branches.group_id', $group_id)
                    ->orderBy('cuotas_alquiler.cod_venta', 'ASC')
                    ->orderBy('cuotas_alquiler.num_cuota', 'ASC')
            ->get();
            
            $i=0;
            $sum=0;
            $cuotas=json_decode(json_encode($housing), true);
            \Log::info($cuotas);
            do{

                $sum += $cuotas[$i]['saldo_cuota'];
                $i++;
                
            }while($sum < $monto);

            \Log::info("[Recibo Comision - Cuota de Alquiler] La sumatoria es: " . $sum . " Y las veces que sumo fue: " . $i . "<p>");

            //diferencia del monto y la sumatoria total de las cuotas
            $dif=$sum-$monto;

            \Log::info("[Recibo Comision - Cuota de Alquiler] La diferencia es: " . $dif . "<p>");

            $movement_id=\DB::table('movements')->insertGetId([
                'movement_type_id'          => 8,
                'destination_operation_id'  => 0,
                'amount'                    => -(int)$monto,
                'debit_credit'              =>  'cr',
                'created_at'                => Carbon::now(),
                'updated_at'                => Carbon::now()        

            ]);

            $last_balance = \DB::table('current_account')->where('group_id',$group_id)->orderBy('id','desc')->first();
            if(isset($last_balance)){
                $balance= $last_balance->balance -(int)$monto;
            }else{
                $balance= -(int)$monto;
            }

            \DB::table('current_account')->insert([
                'movement_id'               => $movement_id,    
                'group_id'                  => $group_id,
                'amount'                    => -(int)$monto,
                'balance'                   => $balance, 
            ]);

            $recibo_id=\DB::table('mt_recibos')->insertGetId([
                'movements_id'               => $movement_id,    
                'monto'                     => (int)$monto,
                'created_at'                => Carbon::now(),
                'updated_at'                => Carbon::now(),
                'tipo_recibo_id'            => 2
            ]);

            $cuotas = \DB::table('cuotas_alquiler')
                ->select('cuotas_alquiler.*')
                ->join('alquiler', 'alquiler.id', '=', 'cuotas_alquiler.alquiler_id')
                ->join('alquiler_housing', 'alquiler.id', '=', 'alquiler_housing.alquiler_id')
                ->join('atms', 'atms.housing_id', '=', 'alquiler_housing.housing_id')
                ->join('branches', 'branches.group_id', '=', 'alquiler.group_id')
                ->where('cuotas_alquiler.saldo_cuota', '!=', 0)
                ->where('atms.id', $atm_id)
                ->where('branches.group_id', $group_id)
                ->orderBy('cuotas_alquiler.cod_venta', 'ASC')
                ->orderBy('cuotas_alquiler.num_cuota', 'ASC')
                ->take($i)
            ->get();

            $recibo_details=\DB::table('mt_recibos_comisiones_details')->insert([
                'recibo_id'             => $recibo_id,
                'recibo_comision_id'    => $comision_id
            ]);

            $total_debitar=$monto;
            foreach($cuotas as $cuota){
                \Log::info('[Recibo Comision - Cuota de Alquiler] En proceso de insertar la cuota #'. $cuota->num_cuota . ' para el codigo de venta '.$cuota->cod_venta);                
                //$consulta_cuota=\DB::table('cuotas')->where('numero_cuota',$cuota->numero_cuota)->where('cod_venta',$cuota->cod_venta)->first();

                $movement_id=\DB::table('movements')->insertGetId([
                    'movement_type_id'          => 7,
                    'destination_operation_id'  => $cuota->cod_venta,
                    'amount'                    => $cuota->saldo_cuota,
                    'debit_credit'              =>  'de',
                    'created_at'                => Carbon::now(),
                    'updated_at'                => Carbon::now()        
                ]);
                
                $last_balance = \DB::table('current_account')->where('group_id',$group_id)->orderBy('id','desc')->first();
                if(isset($last_balance)){
                    $balance= $last_balance->balance + $cuota->saldo_cuota;
                }else{
                    $balance= $cuota->saldo_cuota;
                }

                \DB::table('current_account')->insert([
                    'movement_id'               => $movement_id,    
                    'group_id'                  => $group_id,
                    'amount'                    => (int)$cuota->saldo_cuota,
                    'balance'                   => $balance, 
                ]);

                \DB::table('mt_recibo_alquiler_x_cuota')->insert([
                    'recibo_id'         => $recibo_id,    
                    'alquiler_id'       => $cuota->alquiler_id,
                    'numero_cuota'      => $cuota->num_cuota
                ]);

                $total_debitar -= $cuota->saldo_cuota;
                
                if($total_debitar >= 0){
                    $saldo=0;
                }else{
                    $saldo=abs($total_debitar);
                }
                //dd($cuota->cod_venta);
                \DB::table('cuotas_alquiler')
                        ->where('num_cuota', $cuota->num_cuota)
                        ->where('cod_venta', $cuota->cod_venta)
                        ->update([
                            'movements_id'  => $movement_id,
                            'saldo_cuota'   => $saldo
                        ]);
                \Log::info('Se insertaron los siguientes movimientos de la cuota con el movement_id: '.$movement_id);
            
            }

            $response_block= $this->checkBlockAlquiler_v2($recibo_id, $atm_id);
            \Log::warning($response_block);
            
            \DB::commit();
            $response = [
                'error' => false,
                'message' => '[Recibo Alquiler - Cuota de Alquiler] Se ha ejecutado correctamente el proceso',
                'message_user' => ''
            ];

            return $response;

        }catch(\Exception $e){
            \DB::rollback();
            \Log::error("[Recibo Comision - Cuota de Alquiler]  - {$e->getMessage()}");
            $response = [
                'error' => true,
                'message' => '[Recibo Alquiler - Cuota de Alquiler] Error al consultar cuota de miniterminal',
                'message_user' => ''
            ];

            return $response;
        }
    }

    //Proceso para afectar la cuota el recibo de comision
    private function alquiler_comision_process_v3($atm_id, $group_id, $monto, $comision_id){

        \DB::beginTransaction();
        try{

            $housing = \DB::table('cuotas_alquiler')
                    ->select('cuotas_alquiler.*', 'alquiler.group_id', 'alquiler.id as alquiler_id')
                    ->join('alquiler', 'alquiler.id', '=', 'cuotas_alquiler.alquiler_id')
                    ->join('alquiler_housing', 'alquiler.id', '=', 'alquiler_housing.alquiler_id')
                    ->join('atms', 'atms.housing_id', '=', 'alquiler_housing.housing_id')
                    ->where('cuotas_alquiler.saldo_cuota', '!=', 0)
                    ->whereNotNull('cuotas_alquiler.cod_venta')
                    ->where('atms.id', $atm_id)
                    ->where('alquiler.group_id', $group_id)
                    ->orderBy('cuotas_alquiler.cod_venta', 'ASC')
                    ->orderBy('cuotas_alquiler.num_cuota', 'ASC')
            ->get();
            //dd($housing);
            $i=0;
            $sum=0;
            $cuotas=json_decode(json_encode($housing), true);
            \Log::info($cuotas);
            do{

                $sum += $cuotas[$i]['saldo_cuota'];
                $i++;
                
            }while($sum < $monto);

            \Log::info("[Recibo Comision - Cuota de Alquiler] La sumatoria es: " . $sum . " Y las veces que sumo fue: " . $i . "<p>");

            //diferencia del monto y la sumatoria total de las cuotas
            $dif=$sum-$monto;

            \Log::info("[Recibo Comision - Cuota de Alquiler] La diferencia es: " . $dif . "<p>");

            $last_balance = \DB::table('mt_movements')->where('atm_id',$atm_id)->orderBy('id','desc')->first();

            if(isset($last_balance)){
                $balance= $last_balance->balance -(int)$monto;
                $balance_antes= $last_balance->balance;
            }else{
                $balance= -(int)$monto;
                $balance_antes=0;
            }

            $movement_id=\DB::table('mt_movements')->insertGetId([
                'movement_type_id'          => 8,
                'destination_operation_id'  => 0,
                'amount'                    => -(int)$monto,
                'debit_credit'              =>  'cr',
                'created_at'                => Carbon::now(),
                'updated_at'                => Carbon::now(),
                'group_id'                  => $group_id,
                'atm_id'                    => $atm_id,
                'balance_antes'             => $balance_antes,
                'balance'                   => $balance      

            ]);

            $recibo_id=\DB::table('mt_recibos')->insertGetId([
                'mt_movements_id'           => $movement_id,    
                'monto'                     => (int)$monto,
                'created_at'                => Carbon::now(),
                'updated_at'                => Carbon::now(),
                'tipo_recibo_id'            => 2
            ]);

            $cuotas = \DB::table('cuotas_alquiler')
                ->select('cuotas_alquiler.*')
                ->join('alquiler', 'alquiler.id', '=', 'cuotas_alquiler.alquiler_id')
                ->join('alquiler_housing', 'alquiler.id', '=', 'alquiler_housing.alquiler_id')
                ->join('atms', 'atms.housing_id', '=', 'alquiler_housing.housing_id')
                //->join('branches', 'branches.group_id', '=', 'alquiler.group_id')
                ->where('cuotas_alquiler.saldo_cuota', '!=', 0)
                ->where('atms.id', $atm_id)
                ->where('alquiler.group_id', $group_id)
                ->orderBy('cuotas_alquiler.cod_venta', 'ASC')
                ->orderBy('cuotas_alquiler.num_cuota', 'ASC')
                ->take($i)
            ->get();

            $recibo_details=\DB::table('mt_recibos_comisiones_details')->insert([
                'recibo_id'             => $recibo_id,
                'recibo_comision_id'    => $comision_id
            ]);

            $total_debitar=$monto;
            foreach($cuotas as $cuota){
                \Log::info('[Recibo Comision - Cuota de Alquiler] En proceso de insertar la cuota #'. $cuota->num_cuota . ' para el codigo de venta '.$cuota->cod_venta);

                $last_balance = \DB::table('mt_movements')->where('atm_id',$atm_id)->orderBy('id','desc')->first();

                if(isset($last_balance)){
                    $balance= $last_balance->balance + $cuota->saldo_cuota;
                    $balance_antes= $last_balance->balance;
                }else{
                    $balance= $cuota->saldo_cuota;
                    $balance_antes=0;
                }

                $movement_id=\DB::table('mt_movements')->insertGetId([
                    'movement_type_id'          => 7,
                    'destination_operation_id'  => $cuota->cod_venta,
                    'amount'                    => $cuota->saldo_cuota,
                    'debit_credit'              =>  'de',
                    'created_at'                => Carbon::now(),
                    'updated_at'                => Carbon::now(),
                    'group_id'                  => $group_id,
                    'atm_id'                    => $atm_id,
                    'balance_antes'             => $balance_antes,
                    'balance'                   => $balance      

                ]);

                \DB::table('mt_recibo_alquiler_x_cuota')->insert([
                    'recibo_id'         => $recibo_id,    
                    'alquiler_id'       => $cuota->alquiler_id,
                    'numero_cuota'      => $cuota->num_cuota
                ]);

                $total_debitar -= $cuota->saldo_cuota;
                
                if($total_debitar >= 0){
                    $saldo=0;
                }else{
                    $saldo=abs($total_debitar);
                }
                //dd($cuota->cod_venta);
                \DB::table('cuotas_alquiler')
                        ->where('num_cuota', $cuota->num_cuota)
                        ->where('cod_venta', $cuota->cod_venta)
                        ->update([
                            'movements_id'  => $movement_id,
                            'saldo_cuota'   => $saldo
                        ]);
                \Log::info('Se insertaron los siguientes movimientos de la cuota con el movement_id: '.$movement_id);
            
            }

            $response_block= $this->checkBlockAlquiler_v3($recibo_id, $atm_id, $group_id);
            \Log::warning($response_block);
            
            \DB::commit();
            $response = [
                'error' => false,
                'message' => '[Recibo Alquiler - Cuota de Alquiler] Se ha ejecutado correctamente el proceso',
                'message_user' => ''
            ];

            return $response;

        }catch(\Exception $e){
            \DB::rollback();
            \Log::error("[Recibo Comision - Cuota de Alquiler]  - {$e->getMessage()}");
            $response = [
                'error' => true,
                'message' => '[Recibo Alquiler - Cuota de Alquiler] Error al consultar cuota de miniterminal',
                'message_user' => ''
            ];

            return $response;
        }
    }

    //Proceso para afectar la cuota de venta el recibo de comision
    private function venta_comision_process_v2($atm_id, $group_id, $monto, $comision_id){

        \DB::beginTransaction();
        try{

            $housing = \DB::table('cuotas')
                    ->select('cuotas.*', 'branches.group_id')
                    ->join('venta', 'venta.id', '=', 'cuotas.credito_venta_id')
                    ->join('venta_housing', 'venta.id', '=', 'venta_housing.venta_id')
                    ->join('atms', 'atms.housing_id', '=', 'venta_housing.housing_id')
                    ->join('branches', 'branches.group_id', '=', 'alquiler.group_id')
                    ->where('venta.tipo_venta', 'cr')
                    ->where('cuotas.saldo_cuota', '!=', 0)
                    ->where('branches.group_id', $group_id)
                    ->where('atms.id', $atm_id)
                    ->orderBy('cuotas.cod_venta', 'ASC')
                    ->orderBy('cuotas.numero_cuota', 'ASC')
            ->get();

            $i=0;
            $sum=0;
            $cuotas=json_decode(json_encode($housing), true);
            \Log::info($cuotas);
            do{

                $sum += $cuotas[$i]['saldo_cuota'];
                $i++;
                
            }while($sum < $monto);

            \Log::info("[Recibo Comision - Cuota de Venta] La sumatoria es: " . $sum . " Y las veces que sumo fue: " . $i . "<p>");

            //diferencia del monto y la sumatoria total de las cuotas
            $dif=$sum-$monto;

            \Log::info("[Recibo Comision - Cuota de Venta] La diferencia es: " . $dif . "<p>");

            $movement_id=\DB::table('movements')->insertGetId([
                'movement_type_id'          => 5,
                'destination_operation_id'  => 0,
                'amount'                    => -(int)$monto,
                'debit_credit'              =>  'cr',
                'created_at'                => Carbon::now(),
                'updated_at'                => Carbon::now()        

            ]);

            $last_balance = \DB::table('current_account')->where('group_id',$group_id)->orderBy('id','desc')->first();
            if(isset($last_balance)){
                $balance= $last_balance->balance -(int)$monto;
            }else{
                $balance= -(int)$monto;
            }

            \DB::table('current_account')->insert([
                'movement_id'               => $movement_id,    
                'group_id'                  => $group_id,
                'amount'                    => -(int)$monto,
                'balance'                   => $balance, 
            ]);

            $recibo_id=\DB::table('mt_recibos')->insertGetId([
                'movements_id'               => $movement_id,    
                'monto'                     => (int)$monto,
                'created_at'                => Carbon::now(),
                'updated_at'                => Carbon::now(),
                'tipo_recibo_id'            => 1
            ]);

            $cuotas = \DB::table('cuotas')
                ->select('cuotas.*')
                ->join('venta', 'venta.id', '=', 'cuotas.credito_venta_id')
                ->join('venta_housing', 'venta.id', '=', 'venta_housing.venta_id')
                ->join('atms', 'atms.housing_id', '=', 'venta_housing.housing_id')
                ->join('branches', 'branches.group_id', '=', 'alquiler.group_id')
                ->where('venta.tipo_venta', 'cr')
                ->where('cuotas.saldo_cuota', '!=', 0)
                ->where('branches.group_id', $group_id)
                ->where('atms.id', $atm_id)
                ->orderBy('cuotas.cod_venta', 'ASC')
                ->orderBy('cuotas.numero_cuota', 'ASC')
                ->take($i)
            ->get();

            $recibo_details=\DB::table('mt_recibos_comisiones_details')->insert([
                'recibo_id'             => $recibo_id,
                'recibo_comision_id'    => $comision_id
            ]);

            $total_debitar=$monto;
            foreach($cuotas as $cuota){
                \Log::info('[Recibo Comision - Cuota de Venta] En proceso de insertar la cuota #'. $cuota->numero_cuota . ' para el codigo de venta '.$cuota->cod_venta);                
                //$consulta_cuota=\DB::table('cuotas')->where('numero_cuota',$cuota->numero_cuota)->where('cod_venta',$cuota->cod_venta)->first();

                $movement_id=\DB::table('movements')->insertGetId([
                    'movement_type_id'          => 4,
                    'destination_operation_id'  => $cuota->cod_venta,
                    'amount'                    => $cuota->saldo_cuota,
                    'debit_credit'              =>  'de',
                    'created_at'                => Carbon::now(),
                    'updated_at'                => Carbon::now()        
                ]);
                
                $last_balance = \DB::table('current_account')->where('group_id',$group_id)->orderBy('id','desc')->first();
                if(isset($last_balance)){
                    $balance= $last_balance->balance + $cuota->saldo_cuota;
                }else{
                    $balance= $cuota->saldo_cuota;
                }

                \DB::table('current_account')->insert([
                    'movement_id'               => $movement_id,    
                    'group_id'                  => $group_id,
                    'amount'                    => (int)$cuota->saldo_cuota,
                    'balance'                   => $balance, 
                ]);

                \DB::table('mt_recibo_x_cuota')->insert([
                    'recibo_id'         => $recibo_id,    
                    'credito_venta_id'  => $cuota->credito_venta_id,
                    'numero_cuota'      => $cuota->numero_cuota
                ]);

                $total_debitar -= $cuota->saldo_cuota;
                
                if($total_debitar >= 0){
                    $saldo=0;
                }else{
                    $saldo=abs($total_debitar);
                }
                //dd($cuota->cod_venta);
                \DB::table('cuotas')
                        ->where('numero_cuota', $cuota->numero_cuota)
                        ->where('cod_venta', $cuota->cod_venta)
                        ->update([
                            'movements_id'  => $movement_id,
                            'saldo_cuota'   => $saldo
                        ]);
                \Log::info('Se insertaron los siguientes movimientos de la cuota con el movement_id: '.$movement_id);
            
            }

            $response_block= $this->checkBlockVenta($recibo_id, $cuota->cod_venta);
            \Log::warning($response_block);
            
            \DB::commit();
            $response = [
                'error' => false,
                'message' => '[Recibo Comision - Cuota de Venta] Se ha ejecutado correctamente el proceso',
                'message_user' => ''
            ];

            return $response;

        }catch(\Exception $e){
            \DB::rollback();
            \Log::error("[Recibo Comision - Cuota de Venta]  - {$e->getMessage()}");
            $response = [
                'error' => true,
                'message' => '[Recibo Comision - Cuota de Venta] Error al consultar cuota de miniterminal',
                'message_user' => ''
            ];

            return $response;
        }
    }

    //Proceso para afectar la cuota de venta el recibo de comision
    private function venta_comision_process_v3($atm_id, $group_id, $monto, $comision_id){

        \DB::beginTransaction();
        try{

            $housing = \DB::table('cuotas')
                    ->select('cuotas.*', 'venta.group_id')
                    ->join('venta', 'venta.id', '=', 'cuotas.credito_venta_id')
                    ->join('venta_housing', 'venta.id', '=', 'venta_housing.venta_id')
                    ->join('atms', 'atms.housing_id', '=', 'venta_housing.housing_id')
                    ->where('venta.tipo_venta', 'cr')
                    ->where('cuotas.saldo_cuota', '!=', 0)
                    ->where('venta.group_id', $group_id)
                    ->where('atms.id', $atm_id)
                    ->orderBy('cuotas.cod_venta', 'ASC')
                    ->orderBy('cuotas.numero_cuota', 'ASC')
            ->get();

            $i=0;
            $sum=0;
            $cuotas=json_decode(json_encode($housing), true);
            \Log::info($cuotas);
            do{

                $sum += $cuotas[$i]['saldo_cuota'];
                $i++;
                
            }while($sum < $monto);

            \Log::info("[Recibo Comision - Cuota de Venta] La sumatoria es: " . $sum . " Y las veces que sumo fue: " . $i . "<p>");

            //diferencia del monto y la sumatoria total de las cuotas
            $dif=$sum-$monto;

            \Log::info("[Recibo Comision - Cuota de Venta] La diferencia es: " . $dif . "<p>");

            $last_balance = \DB::table('mt_movements')->where('atm_id',$atm_id)->orderBy('id','desc')->first();

            if(isset($last_balance)){
                $balance= $last_balance->balance -(int)$monto;
                $balance_antes= $last_balance->balance;
            }else{
                $balance= -(int)$monto;
                $balance_antes=0;
            }

            $movement_id=\DB::table('mt_movements')->insertGetId([
                'movement_type_id'          => 5,
                'destination_operation_id'  => 0,
                'amount'                    => -(int)$monto,
                'debit_credit'              =>  'cr',
                'created_at'                => Carbon::now(),
                'updated_at'                => Carbon::now(),
                'group_id'                  => $group_id,
                'atm_id'                    => $atm_id,
                'balance_antes'             => $balance_antes,
                'balance'                   => $balance        

            ]);

            $recibo_id=\DB::table('mt_recibos')->insertGetId([
                'mt_movements_id'               => $movement_id,    
                'monto'                     => (int)$monto,
                'created_at'                => Carbon::now(),
                'updated_at'                => Carbon::now(),
                'tipo_recibo_id'            => 1
            ]);

            $cuotas = \DB::table('cuotas')
                ->select('cuotas.*')
                ->join('venta', 'venta.id', '=', 'cuotas.credito_venta_id')
                ->join('venta_housing', 'venta.id', '=', 'venta_housing.venta_id')
                ->join('atms', 'atms.housing_id', '=', 'venta_housing.housing_id')
                ->where('venta.tipo_venta', 'cr')
                ->where('cuotas.saldo_cuota', '!=', 0)
                ->where('venta.group_id', $group_id)
                ->where('atms.id', $atm_id)
                ->orderBy('cuotas.cod_venta', 'ASC')
                ->orderBy('cuotas.numero_cuota', 'ASC')
                ->take($i)
            ->get();

            $recibo_details=\DB::table('mt_recibos_comisiones_details')->insert([
                'recibo_id'             => $recibo_id,
                'recibo_comision_id'    => $comision_id
            ]);

            $total_debitar=$monto;
            foreach($cuotas as $cuota){
                \Log::info('[Recibo Comision - Cuota de Venta] En proceso de insertar la cuota #'. $cuota->numero_cuota . ' para el codigo de venta '.$cuota->cod_venta);                
                //$consulta_cuota=\DB::table('cuotas')->where('numero_cuota',$cuota->numero_cuota)->where('cod_venta',$cuota->cod_venta)->first();

                $last_balance = \DB::table('mt_movements')->where('atm_id',$atm_id)->orderBy('id','desc')->first();

                if(isset($last_balance)){
                    $balance= $last_balance->balance + $cuota->saldo_cuota;
                    $balance_antes= $last_balance->balance;
                }else{
                    $balance= $cuota->saldo_cuota;
                    $balance_antes=0;
                }

                $movement_id=\DB::table('mt_movements')->insertGetId([
                    'movement_type_id'          => 4,
                    'destination_operation_id'  => $cuota->cod_venta,
                    'amount'                    => $cuota->saldo_cuota,
                    'debit_credit'              =>  'de',
                    'created_at'                => Carbon::now(),
                    'updated_at'                => Carbon::now(),
                    'group_id'                  => $group_id,
                    'atm_id'                    => $atm_id,
                    'balance_antes'             => $balance_antes,
                    'balance'                   => $balance        
                ]);

                \DB::table('mt_recibo_x_cuota')->insert([
                    'recibo_id'         => $recibo_id,    
                    'credito_venta_id'  => $cuota->credito_venta_id,
                    'numero_cuota'      => $cuota->numero_cuota
                ]);

                $total_debitar -= $cuota->saldo_cuota;
                
                if($total_debitar >= 0){
                    $saldo=0;
                }else{
                    $saldo=abs($total_debitar);
                }
                //dd($cuota->cod_venta);
                \DB::table('cuotas')
                        ->where('numero_cuota', $cuota->numero_cuota)
                        ->where('cod_venta', $cuota->cod_venta)
                        ->update([
                            'movements_id'  => $movement_id,
                            'saldo_cuota'   => $saldo
                        ]);
                \Log::info('Se insertaron los siguientes movimientos de la cuota con el movement_id: '.$movement_id);
            
            }

            $response_block= $this->checkBlockVenta_v2($recibo_id, $atm_id, $group_id);
            \Log::warning($response_block);
            
            \DB::commit();
            $response = [
                'error' => false,
                'message' => '[Recibo Comision - Cuota de Venta] Se ha ejecutado correctamente el proceso',
                'message_user' => ''
            ];

            return $response;

        }catch(\Exception $e){
            \DB::rollback();
            \Log::error("[Recibo Comision - Cuota de Venta]  - {$e->getMessage()}");
            $response = [
                'error' => true,
                'message' => '[Recibo Comision - Cuota de Venta] Error al consultar cuota de miniterminal',
                'message_user' => ''
            ];

            return $response;
        }
    }


    //Proceso para afectar las transacciones el recibo de comision
    private function transacciones_comision_process($group_id, $monto, $comision_id, $atm_id){

        \DB::beginTransaction();
        try{

            //Consulta para traer las ventas que deben ser cobradas
            $sales = \DB::table('miniterminales_sales')
            ->select('movements.id', 'current_account.group_id', 'movements.amount', 'miniterminales_sales.estado','miniterminales_sales.monto_por_cobrar')
            ->join('movements', 'movements.id', '=', 'miniterminales_sales.movements_id')
            ->join('current_account', 'movements.id', '=', 'current_account.movement_id')
            ->where('current_account.group_id', $group_id)
            ->where('miniterminales_sales.estado', 'pendiente')
            ->whereNull('movements.deleted_at')
            ->whereRaw("movements.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-15','-16','-17','-21','-23','-26','-27','212', '999')")
            ->orderBy('movements.destination_operation_id','ASC')
            ->get();
            //dd($sales);
            $array=json_decode(json_encode($sales), true);
            \Log::info($array);
            $i=0;
            $sum=0;

            //algoritmo para traer cuantas ventas van a ser cobradas
            do{

                $sum += $array[$i]['monto_por_cobrar'];
                $i++;
                
            }while($sum < $monto);

            \Log::info("[Recibo Comision - Transacciones] La sumatoria es: " . $sum . " Y las veces que sumo fue: " . $i . "<p>");

            //diferencia de del deposito y la sumatoria total de las ventas
            $dif=$sum-$monto;
            
            \Log::info("[Recibo Comision - Transacciones] La diferencia es: " . $dif . "<p>");
            
            $idventasondanet = \DB::table('miniterminales_sales')
                ->select('movements.destination_operation_id')
                ->join('movements', 'movements.id', '=', 'miniterminales_sales.movements_id')
                ->join('current_account', 'movements.id', '=', 'current_account.movement_id')
                ->where('current_account.group_id', $group_id)
                ->where('miniterminales_sales.estado', 'pendiente')
                ->whereNull('movements.deleted_at')
                ->whereRaw("movements.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-15','-16','-17','-21','-23','-26','-27','212','999')")
                ->orderBy('movements.destination_operation_id','ASC')
                ->take($i)
            ->pluck('movements.destination_operation_id');

            $ventasondanet = implode(';', $idventasondanet);
            \Log::info('[Recibo Comision - Transacciones] Ventas a cobrar', ['ventasondanet' => $ventasondanet]);
            
            $movement_id=\DB::table('movements')->insertGetId([
                'movement_type_id'          => 2,
                'destination_operation_id'  => 0,
                'amount'                    => -(int)$monto,
                'debit_credit'              =>  'cr',
                'created_at'                => Carbon::now(),
                'updated_at'                => Carbon::now()        

            ]);

            $last_balance = \DB::table('current_account')->where('group_id',$group_id)->orderBy('id','desc')->first();
            if(isset($last_balance)){
                $balance= $last_balance->balance -(int)$monto;
            }else{
                $balance= -(int)$monto;
            }

            \DB::table('current_account')->insert([
                'movement_id'               => $movement_id,    
                'group_id'                  => $group_id,
                'amount'                    => -(int)$monto,
                'balance'                   => $balance, 
            ]);

            $recibo_id=\DB::table('mt_recibos')->insertGetId([
                'tipo_recibo_id'    => 3,
                'movements_id'       => $movement_id,
                'monto'             => (int)$monto,
                'created_at'        => Carbon::now(), 
                'updated_at'        => Carbon::now()
            ]);

            $recibo_id_deuda=\DB::table('mt_recibos_cobranzas_x_comision')->insert([
                'recibo_id'             => $recibo_id,
                'ventas_cobradas'       => $ventasondanet,
                'saldo_pendiente'       => 0
            ]);

            $recibo_details=\DB::table('mt_recibos_comisiones_details')->insert([
                'recibo_id'             => $recibo_id,
                'recibo_comision_id'    => $comision_id
            ]);

            if($i==1){

                $idventas = \DB::table('miniterminales_sales')
                ->select('movements.id')
                ->join('movements', 'movements.id', '=', 'miniterminales_sales.movements_id')
                ->join('current_account', 'movements.id', '=', 'current_account.movement_id')
                ->where('current_account.group_id', $group_id)
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
                    ->where('current_account.group_id', $group_id)
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
                    ->where('current_account.group_id', $group_id)
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
                        ->where('current_account.group_id', $group_id)
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

                \Log::info('[Recibo Comision - Transacciones] Ventas cobradas exitosamente');
            }

            $response_balance= $this->updateBalanceDepositado($atm_id, $monto, $recibo_id);
            \Log::warning($response_balance);

            $response_block= $this->checkBlock($group_id);
            \Log::warning($response_block);
            
            $response['error'] = false;
            $response['message'] = 'Registro guardado exitosamente'; 
            \DB::commit();
            return $response;
        }catch(\Exception $e){
            \DB::rollback();
            \Log::error("[Recibo Comision - Transacciones]  - {$e->getMessage()}");
            $response = [
                'error' => true,
                'message' => 'Error al consultar saldo de miniterminal',
                'message_user' => ''
            ];

            return $response;
        }
        
    }

    //Proceso para afectar las transacciones el recibo de comision
    private function transacciones_comision_process_v2($group_id, $monto, $comision_id, $atm_id){

        \DB::beginTransaction();
        try{

            //Consulta para traer las ventas que deben ser cobradas
            $sales = \DB::table('mt_sales')
                ->select('m.id', 'm.group_id', 'm.amount', 'mt_sales.estado','mt_sales.monto_por_cobrar')
                ->join('mt_movements as m', 'm.id', '=', 'mt_sales.movements_id')
                ->where('m.atm_id', $atm_id)
                ->where('mt_sales.estado', 'pendiente')
                ->whereNull('m.deleted_at')
                ->whereRaw("m.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-15','-16','-17','-21','-23','-26','-27','212', '999')")
                ->orderBy('m.destination_operation_id','ASC')
            ->get();
            //dd($sales);
            $array=json_decode(json_encode($sales), true);
            \Log::info($array);
            $i=0;
            $sum=0;

            //algoritmo para traer cuantas ventas van a ser cobradas
            do{

                $sum += $array[$i]['monto_por_cobrar'];
                $i++;
                
            }while($sum < $monto);

            \Log::info("[Recibo Comision - Transacciones] La sumatoria es: " . $sum . " Y las veces que sumo fue: " . $i . "<p>");

            //diferencia de del deposito y la sumatoria total de las ventas
            $dif=$sum-$monto;
            
            \Log::info("[Recibo Comision - Transacciones] La diferencia es: " . $dif . "<p>");
            
            /*$idventasondanet = \DB::table('mt_sales')
                ->select('m.destination_operation_id')
                ->join('mt_movements as m', 'm.id', '=', 'mt_sales.movements_id')
                ->where('m.atm_id', $atm_id)
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
                ->where('m.atm_id', $atm_id)
                ->where('mt_sales.estado', 'pendiente')
                ->whereNull('m.deleted_at')
                ->whereRaw("m.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-15','-16','-17','-21','-23','-26','-27','212','999')")
                ->orderBy('m.destination_operation_id','ASC')
                ->take($i)
            ->get();

            //dd($idventasondanet);

            $ventasondanet = implode(';', array_column($idventasondanet->toArray(), 'destination_operation_id'));
    
            \Log::info('[Recibo Comision - Transacciones] Ventas a cobrar', ['ventasondanet' => $ventasondanet]);

            $last_balance = \DB::table('mt_movements')->where('atm_id',$atm_id)->orderBy('id','desc')->first();

            if(isset($last_balance)){
                $balance= $last_balance->balance -(int)$monto;
                $balance_antes= $last_balance->balance;
            }else{
                $balance= -(int)$monto;
                $balance_antes=0;
            }

            $movement_id=\DB::table('mt_movements')->insertGetId([
                'movement_type_id'          => 2,
                'destination_operation_id'  => 0,
                'amount'                    => -(int)$monto,
                'debit_credit'              =>  'cr',
                'created_at'                => Carbon::now(),
                'updated_at'                => Carbon::now(),
                'group_id'                  => $group_id,
                'atm_id'                    => $atm_id,
                'balance_antes'             => $balance_antes,
                'balance'                   => $balance

            ]);

            $recibo_id=\DB::table('mt_recibos')->insertGetId([
                'tipo_recibo_id'    => 3,
                'mt_movements_id'   => $movement_id,
                'monto'             => (int)$monto,
                'created_at'        => Carbon::now(), 
                'updated_at'        => Carbon::now()
            ]);

            $recibo_id_deuda=\DB::table('mt_recibos_cobranzas_x_comision')->insert([
                'recibo_id'             => $recibo_id,
                'ventas_cobradas'       => $ventasondanet,
                'saldo_pendiente'       => 0
            ]);

            $recibo_details=\DB::table('mt_recibos_comisiones_details')->insert([
                'recibo_id'             => $recibo_id,
                'recibo_comision_id'    => $comision_id
            ]);

            $service=new DepositoBoletaServices;
            $recibo_id      = $recibo_id;
            $now            = Carbon::now();
            $description    = 'Recibo Comision insertado desde la Funcion: transacciones_comision_process_v2';

            if($i==1){

                /*$idventas = \DB::table('mt_sales')
                    ->select('m.id')
                    ->join('mt_movements as m', 'm.id', '=', 'mt_sales.movements_id')
                    ->where('m.atm_id', $atm_id)
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
                $sales_amount_affected  = $monto;
                $sales_amount_pendding  = $dif;
                
                $service->insert_mt_sales_affected_by_receipts($recibo_id, $sale_id, $sales_amount, $sales_amount_affected, $sales_amount_pendding, $now, $description);

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

                    /*$idventas = \DB::table('mt_sales')
                        ->select('m.id')
                        ->join('mt_movements as m', 'm.id', '=', 'mt_sales.movements_id')
                        ->where('m.atm_id', $atm_id)
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

                    foreach($idventasondanet as $idventa){

                        $sale_id                = $idventa->sale_id;
                        $sales_amount           = $idventa->monto_por_cobrar;
                        $sales_amount_affected  = $idventa->monto_por_cobrar;
                        $sales_amount_pendding  = $dif;

                        $service->insert_mt_sales_affected_by_receipts($recibo_id, $sale_id, $sales_amount, $sales_amount_affected, $sales_amount_pendding, $now, $description);

                        \DB::table('mt_sales')
                            ->where('movements_id', $idventa->movement_id)
                            ->update([
                                'estado'            => 'cancelado',
                                'monto_por_cobrar'   => 0
                        ]);

                        \DB::table('mt_movements')->where('id', $idventa->movement_id)->update(['updated_at' => Carbon::now()]);
                    }
                    
                }else{
                    /*$cobradostotal=$i-1;

                    $salescobradas = \DB::table('mt_sales')
                        ->select('m.id')
                        ->join('mt_movements as m', 'm.id', '=', 'mt_sales.movements_id')
                        ->where('m.atm_id', $atm_id)
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

                            $service->insert_mt_sales_affected_by_receipts($recibo_id, $sale_id, $sales_amount, $sales_amount_affected, $sales_amount_pendding, $now, $description);

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
                            ->where('m.atm_id', $atm_id)
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

                        $service->insert_mt_sales_affected_by_receipts($recibo_id, $sale_id, $sales_amount, $sales_amount_affected, $sales_amount_pendding, $now, $description);

                        \DB::table('mt_sales')
                            ->where('movements_id', $sobrante->movement_id)
                            ->update([
                                'estado' => 'pendiente',
                                'monto_por_cobrar'   => $dif
                        ]);
                        \DB::table('mt_movements')->where('id', $sobrante->movement_id)->update(['updated_at' => Carbon::now()]);
                    }
                }

                \Log::info('[Recibo Comision - Transacciones] Ventas cobradas exitosamente');
            }

            $response_balance= $this->updateBalanceDepositado($atm_id, $monto, $recibo_id);
            \Log::warning($response_balance);

            $response_block= $this->checkBlock($group_id);
            \Log::warning($response_block);
            
            $response['error'] = false;
            $response['message'] = 'Registro guardado exitosamente'; 
            \DB::commit();
            return $response;
        }catch(\Exception $e){
            \DB::rollback();
            \Log::error("[Recibo Comision - Transacciones]  - {$e->getMessage()}");
            $response = [
                'error' => true,
                'message' => 'Error al consultar saldo de miniterminal',
                'message_user' => ''
            ];

            return $response;
        }
        
    }

    public function updateBalanceDepositado($atm_id, $monto, $recibo_id){
        \DB::beginTransaction();
        try{

            \Log::info('[updateBalanceDepositado] Se procede a afectar la deuda del atm_id '. $atm_id);
            
            $balance=\DB::table('balance_atms')->where('atm_id', $atm_id)->first();

            if(isset($balance)){
                \Log::info('[updateBalanceDepositado] Ultimo monto total depositado del atm_id '. $atm_id. ': '. $balance->total_depositado);

                $depositado= $balance->total_depositado - abs($monto);
            
                \DB::table('balance_atms')
                    ->where('atm_id', $atm_id)
                    ->update([
                        'total_depositado'  => (int)$depositado
                ]);
            }else{
                \Log::info('[updateBalanceDepositado] Se procede a crear el primer total depositado del atm_id'. $atm_id);

                $depositado= -abs($monto);

                \DB::table('balance_atms')->insert([
                    'atm_id'            => $atm_id,
                    'total_depositado'  => (int)$depositado
                ]);
            }

            \Log::info('[updateBalanceDepositado] Monto actualizado del total depositado del atm_id '. $atm_id . ': '. $depositado);
            \DB::table('mt_recibos_cobranzas_x_comision')
                    ->where('recibo_id', $recibo_id)
                    ->update([
                        'balance_affected'  => true,
                        'date_affected'     => date('Y-m-d H:i:s')
            ]);

            $response['message'] = '[updateBalanceDepositado] El saldo de los atms han sido actualizadas';
            $response['error'] = false;
            
            \DB::commit();
            return $response;
        }catch (\Exception $e) {
            \DB::rollback();
            \Log::error("[updateBalanceDepositado] Error  - {$e->getMessage()}");
            \Log::warning($e);
            $response['error'] = true;
            $response['message'] = $e->getMessage();

            \Log::error("[updateBalanceDepositado] Error  - {$response}");
            return $response;
        }
        
    }

    public function checkBlock($group_id){
        \DB::beginTransaction();
        try{

            $bloqueo=\DB::table('branches')
                ->join('points_of_sale', 'branches.id', '=', 'points_of_sale.branch_id')
                ->join('atms', 'atms.id', '=', 'points_of_sale.atm_id')
                ->where('branches.deleted_at', null)
                ->where('atms.deleted_at', null)
                ->where('branches.group_id', $group_id)
                ->whereRaw("block_type_id in (4, 5, 6, 7)")
            ->first();

            if(isset($bloqueo)){
                //PARA INSERTAR EN HISTORIAL BLOQUEOS CHECKUSERLIMITE
                \Log::warning('El siguiente atm '.$bloqueo->atm_id.' esta bloqueado por Limite');
                $response= $this->checkUserLimite($group_id);
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
                        $response= $this->checkUserBalance($group_id);
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
                                            $estado = 4;
                                            break;
                                        case 5:
                                            $estado = 4;
                                            break;
                                        case 6:
                                            $estado = 6;
                                            break;
                                        case 7:
                                            $estado = 6;
                                            break;
                                    }
                                    if(\DB::table('atms')->where('id', $atm)->update(['block_type_id' => $estado])){
                                        \DB::table('historial_bloqueos')->insert([
                                            'atm_id' => $atm,
                                            'bloqueado' => true,
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
            }else{
                \Log::warning('El siguiente atm no esta bloqueado por Limite');

                //PARA INSERTAR EN HISTORIAL BLOQUEOS CHECKUSERBALANCE
                $response= $this->checkUserBalance($group_id);
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
            }
            

            $response['error']=false;
            $response['message']='[updateBalanceDepositado] Se ha ejecutado correctamente el metodo de Desbloqueo de Puntos de Ventas';
            \DB::commit();
            return $response;
        }
        catch(\Exception $e){
            \DB::rollback();
            \Log::error("Error sending checkBlock  - {$e->getMessage()}");
            $response['error']=true;
            $response['message']='[updateBalanceDepositado] Ocurrio un error al intentar ejecutar el metodo de Desbloqueo de Puntos de Ventas';

            return $response;
        }
    }

    /* Funcion que verifica si el limite de la miniterminal fue pagada */
    public static function checkUserLimite($group_id){
        try {

            \Log::info('La regla por user_id no existe');
            $parametro_control = \DB::table('balance_rules')
            ->where([
                'group_id' => $group_id,
                'tipo_control' => 4,
                'deleted_at' => null
            ])
            ->first();

            $where = "branches.group_id = ". $group_id; 

            $atms = \DB::table('atms')
                    ->select('atms.id as id_atm')
                    ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'atms.id')
                    ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                    ->whereIn('atms.owner_id', [16,21,25])
                    //->where('atms.owner_id', 44) //desarrollo
                    ->whereRaw($where)
                    ->whereNotNull('branches.user_id')
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
            
            $total_saldo = $debe + $haber + $reversado + $cashout;

            \Log::info('El saldo es '. $total_saldo);

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
                $response['message'] = '[Recibo Comision - Transacciones] El siguiente ATM no tiene reglas de Limite';
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
            \Log::debug('Error al consultar saldo de miniterminal - [Recibo Comision - Transacciones] : '. $e);
            $response = [
                'error' => true,
                'message' => 'Error al consultar saldo limite de miniterminal',
                'message_user' => ''
            ];

            return $response;
        }
    }

    /* Funcion que verifica el balance de la deuda de la miniterminal */
    public static function checkUserBalance($group_id){
        try {

            $parametro_control = \DB::table('balance_rules')
                ->where([
                    'group_id' => $group_id,
                    'dia' => date('N'),
                    'deleted_at' => null
                ])
                ->whereRaw("tipo_control not in (3, 4)")
            ->first();

            $where = "branches.group_id = ". $group_id;
            
            \Log::info(json_decode(json_encode($parametro_control), true));

            $atms = \DB::table('atms')
                    ->select('atms.id as id_atm')
                    ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'atms.id')
                    ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                    ->whereIn('atms.owner_id', [16,21,25])
                    //->where('atms.owner_id', 44) //desarrollo
                    ->whereRaw($where)
                    ->whereNotNull('branches.user_id')
                    ->whereNull('atms.deleted_at')
                    ->whereNull('points_of_sale.deleted_at')
            ->pluck('id_atm')->toArray();
            
            \Log::info(json_decode(json_encode($atms), true));

            if(empty($atms)){
                $response['error'] = true;
                $response['deuda'] = false;
                return $response;
            }

            $atm_id = implode(', ', $atms);
            
            $fecha_actual = Carbon::now();
            # Si hay parametros asignados al usuario y al dia actual
            if(!empty($parametro_control)){
                $dias_previos = $parametro_control->dias_previos;
                $fecha_actual = Carbon::now()->modify('-'.$dias_previos .' days');
            }else{     
                $fecha_actual = Carbon::now()->modify('-2 days');
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
                ->where('m.group_id',$group_id)
                ->whereNotIn('m.movement_type_id',[4, 5, 7, 8, 9, 10])
                ->whereNull('m.deleted_at')
            ->first();
            
            \Log::info(json_decode(json_encode($baseQuery), true));
            
            \Log::info('El saldo es '. $baseQuery->total);

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

    public function checkBlockVenta($recibo_id, $cod_venta){
        \DB::beginTransaction();
        try{
            $branch = \DB::table('venta')
                ->select('branches.user_id')
                ->join('venta_housing', 'venta.id', '=', 'venta_housing.venta_id')
                ->join('atms', 'atms.housing_id', '=', 'venta_housing.housing_id')
                ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->where('venta.destination_operation_id',$cod_venta)
            ->first();
            $user_id    = $branch->user_id;

            //PARA INSERTAR EN HISTORIAL BLOQUEOS CHECKUSERBALANCE
            $response= $this->checkVenta($user_id, $recibo_id);
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

    public function checkBlockVenta_v2($recibo_id, $atm_id, $group_id){
        \DB::beginTransaction();
        try{

            //PARA INSERTAR EN HISTORIAL BLOQUEOS CHECKUSERBALANCE
            $response= $this->checkVenta_v2($atm_id, $recibo_id, $group_id);
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

    /* Funcion que alerta los servicios que no tengan transacciones en los atms en el rango */
    public static function checkVenta($user_id = null, $recibo_id){
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
                    ->whereIn('atms.owner_id', [16, 21,25])
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

            $cuotas=\DB::table('mt_recibo_x_cuota')->where('recibo_id', $recibo_id)->pluck('numero_cuota', 'numero_cuota');

            $recibo_x_cuota=\DB::table('mt_recibo_x_cuota')->where('recibo_id', $recibo_id)->first();

            $boleta = \DB::table('mt_recibos')->where('id', $recibo_id)->first();

            $total_deuda = \DB::table('cuotas')
            ->selectRaw('sum(importe) as monto_cuota')
            ->where('credito_venta_id', $recibo_x_cuota->credito_venta_id)
            ->whereIn('numero_cuota', $cuotas)
            ->first();

            $baseQuery=$total_deuda->monto_cuota - $boleta->monto;

            \Log::info('El saldo con es '. $baseQuery);

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

    /* Funcion que alerta los servicios que no tengan transacciones en los atms en el rango */
    public static function checkVenta_v2($atm_id, $recibo_id, $group_id){
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

            $cuotas=\DB::table('mt_recibo_x_cuota')->where('recibo_id', $recibo_id)->pluck('numero_cuota', 'numero_cuota');

            $recibo_x_cuota=\DB::table('mt_recibo_x_cuota')->where('recibo_id', $recibo_id)->first();

            $boleta = \DB::table('mt_recibos')->where('id', $recibo_id)->first();

            $total_deuda = \DB::table('cuotas')
            ->selectRaw('sum(importe) as monto_cuota')
            ->where('credito_venta_id', $recibo_x_cuota->credito_venta_id)
            ->whereIn('numero_cuota', $cuotas)
            ->first();

            $baseQuery=$total_deuda->monto_cuota - $boleta->monto;

            \Log::info('El saldo con es '. $baseQuery);

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

    public function checkBlockAlquiler_v2($recibo_id, $atm_id){
        \DB::beginTransaction();
        try{

            $users = \DB::table('branches')
                ->selectRaw('DISTINCT branches.user_id')
                ->join('points_of_sale', 'branches.id', '=', 'points_of_sale.branch_id')
                ->join('mt_recibos_comisiones', 'mt_recibos_comisiones.atm_id', '=', 'points_of_sale.atm_id')
                ->join('mt_recibos_comisiones_details', 'mt_recibos_comisiones.id', '=', 'mt_recibos_comisiones_details.recibo_comision_id')
                ->join('mt_recibo_alquiler_x_cuota', 'mt_recibo_alquiler_x_cuota.recibo_id', '=', 'mt_recibos_comisiones_details.recibo_id')
                ->where('mt_recibos_comisiones.atm_id',$atm_id)
                ->where('mt_recibo_alquiler_x_cuota.recibo_id',$recibo_id)
            ->pluck('branches.user_id');

            foreach($users as $user){
                //PARA INSERTAR EN HISTORIAL BLOQUEOS CHECKUSERBALANCE
                $response= $this->checkAlquiler($user, $recibo_id);
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

    public function checkBlockAlquiler_v3($recibo_id, $atm_id, $group_id){
        \DB::beginTransaction();
        try{

            //PARA INSERTAR EN HISTORIAL BLOQUEOS CHECKUSERBALANCE
            $response= $this->checkAlquiler_v2($atm_id, $recibo_id, $group_id);
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

    /* Funcion que alerta los servicios que no tengan transacciones en los atms en el rango */
    public static function checkAlquiler($user_id = null, $recibo_id){
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

            $cuotas=\DB::table('mt_recibo_alquiler_x_cuota')->where('recibo_id', $recibo_id)->pluck('numero_cuota', 'numero_cuota');

            $recibo_x_cuota=\DB::table('mt_recibo_alquiler_x_cuota')->where('recibo_id', $recibo_id)->first();

            $boleta = \DB::table('mt_recibos')->where('id', $recibo_id)->first();

            $total_deuda = \DB::table('cuotas_alquiler')
            ->selectRaw('sum(importe) as monto_cuota')
            ->where('alquiler_id', $recibo_x_cuota->alquiler_id)
            ->whereIn('num_cuota', $cuotas)
            ->first();

            $baseQuery=$total_deuda->monto_cuota - $boleta->monto;

            \Log::info('El saldo con es '. $baseQuery);

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

    /* Funcion que alerta los servicios que no tengan transacciones en los atms en el rango */
    public static function checkAlquiler_v2($atm_id = null, $recibo_id, $group_id){
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
                ->where('id', $atm_id)
                ->whereNull('atms.deleted_at')
            ->pluck('id_atm')->toArray();

            if(empty($atms)){
                $response['error'] = false;
                $response['deuda'] = false;
                return $response;
            }

            $atm_id = implode(', ', $atms);

            $cuotas=\DB::table('mt_recibo_alquiler_x_cuota')->where('recibo_id', $recibo_id)->pluck('numero_cuota', 'numero_cuota');

            $recibo_x_cuota=\DB::table('mt_recibo_alquiler_x_cuota')->where('recibo_id', $recibo_id)->first();

            $boleta = \DB::table('mt_recibos')->where('id', $recibo_id)->first();

            $total_deuda = \DB::table('cuotas_alquiler')
            ->selectRaw('sum(importe) as monto_cuota')
            ->where('alquiler_id', $recibo_x_cuota->alquiler_id)
            ->whereIn('num_cuota', $cuotas)
            ->first();

            $baseQuery=$total_deuda->monto_cuota - $boleta->monto;

            \Log::info('El saldo con es '. $baseQuery);

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

    public function getInfo($comision_id){
        try{

            $comision = \DB::table('mt_recibos_comisiones')
                ->select('mt_recibos_comisiones.*', 'business_groups.description', 'business_groups.ruc')
                ->join('business_groups','business_groups.id','=','mt_recibos_comisiones.group_id')
                ->where('mt_recibos_comisiones.id', $comision_id)
            ->first();

            //Consulta si tiene deuda de alquiler
            $alquiler_cuotas = \DB::table('mt_recibos')
                ->select('mt_recibos.id', 'mt_recibos.id', 'mt_recibo_alquiler_x_cuota.alquiler_id', 'mt_recibo_alquiler_x_cuota.numero_cuota', 'mt_recibos.recibo_nro')
                ->join('mt_recibo_alquiler_x_cuota','mt_recibos.id','=','mt_recibo_alquiler_x_cuota.recibo_id')
                ->join('mt_recibos_comisiones_details','mt_recibos.id','=','mt_recibos_comisiones_details.recibo_id')
                ->join('mt_recibos_comisiones','mt_recibos_comisiones.id','=','mt_recibos_comisiones_details.recibo_comision_id')
                ->where('mt_recibos.tipo_recibo_id', 2)
                ->where('mt_recibos_comisiones.id', $comision_id)
                ->orderBy('numero_cuota','ASC')
            ->get();
            //dd($alquiler_cuotas);
            $status_ventas=null;
            $i=0;
            if(!empty($alquiler_cuotas)){
                
                foreach($alquiler_cuotas as $alquiler_cuota){

                    $alquiler = \DB::table('cuotas_alquiler')
                    ->where('alquiler_id', $alquiler_cuota->alquiler_id)
                    ->where('num_cuota', $alquiler_cuota->numero_cuota)
                    ->first();
                    
                    if(is_null($status_ventas)){
                        $status_ventas=$alquiler->cod_venta;
                    }else{
                        $status_ventas=$status_ventas . ';' . $alquiler->cod_venta;
                    }

                    $infos[$i]['tipo']='Cuota de Alquiler Nro. '. $alquiler->num_cuota;
                    $infos[$i]['status']= $alquiler->cod_venta;
                    $i++;
                    $recibo_nro=$alquiler_cuota->recibo_nro;
                }

            }

            //Consulta si tiene deuda de cuota de venta
            $ventas_cuotas = \DB::table('mt_recibos')
                ->select('mt_recibos.id', 'mt_recibo_x_cuota.credito_venta_id', 'mt_recibo_x_cuota.numero_cuota', 'mt_recibos.recibo_nro')
                ->join('mt_recibo_x_cuota','mt_recibos.id','=','mt_recibo_x_cuota.recibo_id')
                ->join('mt_recibos_comisiones_details','mt_recibos.id','=','mt_recibos_comisiones_details.recibo_id')
                ->join('mt_recibos_comisiones','mt_recibos_comisiones.id','=','mt_recibos_comisiones_details.recibo_comision_id')
                ->where('mt_recibos.tipo_recibo_id', 1)
                ->where('mt_recibos_comisiones.id', $comision_id)
                ->orderBy('numero_cuota','ASC')
            ->first();
                
            if(!empty($ventas_cuotas)){
                $venta = \DB::table('cuotas')
                    ->where('credito_venta_id', $ventas_cuotas->credito_venta_id)
                ->first();

                if(is_null($status_ventas)){
                    $status_ventas=$venta->cod_venta;
                }else{
                    $status_ventas=$status_ventas . ';' . $venta->cod_venta;
                }

                $infos[$i]['tipo']='Cuota de Venta Nro. '. $venta->numero_cuota;
                $infos[$i]['status']= $venta->cod_venta;
                $i++;

                $recibo_nro=$ventas_cuotas->recibo_nro;
            }

            //Consulta si tiene deuda de cobraza de Venta
            $cobranzas = \DB::table('mt_recibos_cobranzas_x_comision')
                ->select('mt_recibos_cobranzas_x_comision.recibo_id', 'mt_recibos_cobranzas_x_comision.ventas_cobradas', 'mt_recibos.recibo_nro')
                ->join('mt_recibos','mt_recibos.id','=','mt_recibos_cobranzas_x_comision.recibo_id')
                ->join('mt_recibos_comisiones_details','mt_recibos.id','=','mt_recibos_comisiones_details.recibo_id')
                ->join('mt_recibos_comisiones','mt_recibos_comisiones.id','=','mt_recibos_comisiones_details.recibo_comision_id')
                ->where('mt_recibos_comisiones.id', $comision_id)
            ->first();
                
            if(!empty($cobranzas) && !is_null($cobranzas->ventas_cobradas)){

                if(is_null($status_ventas)){
                    $status_ventas=$cobranzas->ventas_cobradas;
                }else{
                    $status_ventas=$status_ventas . ';' . $cobranzas->ventas_cobradas;
                }

                $infos[$i]['tipo']='Deudas de Transaccion';
                $infos[$i]['status']= $cobranzas->ventas_cobradas;
                $i++;

                $recibo_nro=$cobranzas->recibo_nro;
            }

            $result['payment_info']='';

            foreach ($infos as $info) {
            $result['payment_info'] .=
            '<tr><td>'.$info['tipo'].'</td>
            <td>'.$info['status'].'</td>';
            }

            $result['grupo'] = $comision->ruc . ' | '. $comision->description;
            $result['recibo_nro'] = $recibo_nro;
            $result['success'] = true;
            //dd($result);
            return $result;
        }catch(\Exception $e){
            $result =
                [
                    'success' => false,
                    'message' => 'Ha ocurrido un error',
                ];
            \Log::warning('[Info Descuentos por Comision] Error - Al mostrar la informacion del descuento de comision', ['result' => $e]);
            return $result;
        }
    }
}
