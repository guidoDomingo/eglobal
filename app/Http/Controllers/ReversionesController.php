<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Reversion;
use Excel;
use Carbon\Carbon;
use Session;

class ReversionesController extends Controller
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
        if (!$this->user->hasAccess('reversiones_bancard')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $name = $request->get('name');
        $reversiones = Reversion::filterAndPaginate($name);
       
        //$reversiones = Owner::paginate(10);
        return view('reversiones.index', compact('reversiones', 'name'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        if (!$this->user->hasAccess('reversiones_bancard.add')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }


        $reversiones= \DB::table('mt_recibos_reversiones')->get();
        return view('reversiones.import');
       
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        if (!$this->user->hasAccess('reversiones_bancard.add')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        
        try{
            $this->validate($request, [
                'select_file'  => 'required|mimes:xls,xlsx'
               ]);
    
            $path = $request->file('select_file')->getRealPath();
            $revers = Excel::load($path)->get();
            \DB::beginTransaction();
            if($revers->count() > 0){
                foreach($revers as $rever){
                    
                    if(!empty($rever) && !is_null($rever->id_admin)){
                        //dd($rever);
                        if(\DB::table('mt_recibos_reversiones')->where('transaction_id', $rever->id_admin)->count() == 0){
                            \Log::info($rever);
                            $transaction = \DB::table('transactions')->where('id', $rever->id_admin)->first();
                            //dd($transaction);
                            if(isset($transaction) && $transaction->service_source_id == 7){
                                $migracion_toval = $this->reversar_v2($transaction, $rever->fecha_anulada, $rever->nro_transaccion);

                                if($migracion_toval['success'] && $migracion_toval['reversado']){
                                    \Log::info($migracion_toval['message']);
                                }else{
                                    \DB::rollback();
                                    Session::flash('error_message', 'La transaction_id#'.$rever->id_admin.' no puede ser reversada debido a que no tiene ventas suficientes a afectar. Favor volver a ingresar el Excel sin la transaccion para ingresar las demas.');
                                    return redirect('reversiones');
                                }
                            }else{
                                \DB::rollback();
                                Session::flash('error_message', 'La transaction_id#'.$rever->id_admin.' no es una transacction de Bancard. Favor volver a ingresar el Excel sin la transaccion para ingresar las demas.');
                                return redirect('reversiones');
                            }
                        }else{
                            \DB::rollback();
                            Session::flash('error_message', 'La transaccion#'.$rever->id_admin.' ya se encuentra reversada. Favor volver a ingresar el Excel sin la transaccion para ingresar las demas.');
                            return redirect('reversiones');
                        }
                        
                    }
                }

                /*foreach($insert_data as $insert){
                    \DB::beginTransaction();
                    if(!empty($insert)){
                        \Log::info($insert);

                        $transaction = \DB::table('transactions')->where('id',$id)->first();

                        if ($id=\DB::table('reversiones')->insertGetId($insert)){

                            $migracion_toval = $this->migrar($id);
                            \Log::info('Migracion toval');
                            \Log::info($migracion_toval);
                            if(!$migracion_toval['error']){

                                $toval_id = \DB::table('reversiones')->where('id',$id)->first();
                                $toval_id->reversion_toval_id= $migracion_toval['migracion_toval']['status'];

                                if($toval_id->reversion_toval_id !== -7){
                                    \DB::table('reversiones')->where('id', $id)->update([
                                        'reversion_toval_id' => $toval_id->reversion_toval_id,
                                    ]);
                                    \DB::commit();
                                }else{
                                    \DB::rollback();
                                    Session::flash('error_message', 'Error al crear la reversion');
                                    return redirect()->back()->with('error', 'Error al crear reversion');
                                }

                                
                            }else{
                                \DB::rollback();
                                Session::flash('error_message', 'Ocurrio un error al querer ingresar Reversion #'.$insert['transaction_id'].'. Status Error '.$migracion_toval['message']);
                                return redirect('reversiones');
                            }
                        }
                    }
                }*/
                \DB::commit();
                \Log::info("New reversions on the house !");
                Session::flash('message', 'Nuevas reversiones creadas correctamente');
                return redirect('reversiones');
            }
        }catch (\Exception $e){
            \Log::critical($e);
            Session::flash('error_message', 'Error al crear la reversion');
            return redirect()->back()->with('error', 'Error al crear reversion');
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
     * Migrate revertion to toval
     *
     * @return \Illuminate\Http\Response
     */
    public function reversar($transaction, $fecha_reversion, $nro_transaccion){

        try {
            //dd($nro_transaccion);
            $group=\DB::table('branches')
                ->selectRaw('branches.group_id')
                ->join('points_of_sale', 'branches.id', '=', 'points_of_sale.branch_id')
                ->where('points_of_sale.atm_id',$transaction->atm_id)
            ->first();
            //dd($group);
            $total_deuda = \DB::table('miniterminales_sales')
                    ->selectRaw('sum(miniterminales_sales.monto_por_cobrar) as deuda')
                    ->join('movements', 'movements.id', '=', 'miniterminales_sales.movements_id')
                    ->join('current_account', 'movements.id', '=', 'current_account.movement_id')
                    ->where('current_account.group_id',$group->group_id)
                    ->where('miniterminales_sales.estado', 'pendiente')
                    ->whereNull('movements.deleted_at')
                    ->whereRaw("movements.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-16','-17','-21','-23','-26','-27','212', '999')")
            ->first();

            //reversion a ser cobrado
            $monto_reversion=abs($transaction->amount);
            //dd($monto_reversion);
            \Log::info(json_decode(json_encode($total_deuda), true));
            $diferencia=$total_deuda->deuda - $monto_reversion;
            \Log::info('[Reversiones Bancard] Diferencia: '. $diferencia);

            if($diferencia >=0){
                
                $pos=\DB::table('points_of_sale')->where('atm_id', $transaction->atm_id)->first();

                if($transaction->service_source_id == 8){
                    \DB::table('incomes')->insert([
                        'operation'                 => 'REGISTER_INCOME_REVERSION_KEN',
                        'origin_operation_id'       => $transaction->id,
                        'destination_operation_id'  => 0,
                        'created_at'                => Carbon::now(),
                        'pos_id'                    => $pos->id,
                        'transaction_id'            => $transaction->id,
                        'client_id'                 => 4,
                        'ondanet_code'              => '026',
                        'transaction_type'          => 0,
                        'incomes_pairing_id'        => 1,  
                    ]);
                }else if($transaction->service_source_id != 8){

                    switch($transaction->service_source_id){
                        case 0: 
                                if($transaction->service_id == 13 || $transaction->service_id == 14){
                                    $ondanet_code='REVIS';
                                }else{
                                    $ondanet_code='REDAR';
                                } break;
                        case 1: $ondanet_code = 'RENET'; break;
                        case 4: $ondanet_code = 'REPRO'; break;
                        case 7: $ondanet_code = '2050'; break;
                        case 10: $ondanet_code = 'RENET'; break;
                        default: 
                                $response['error'] = true;
                                return $response; break;
                    }

                    \Log::info($ondanet_code);

                    \DB::table('incomes')->insert([
                        'operation'                 => 'REGISTER_INCOME_REVERSION',
                        'origin_operation_id'       => $transaction->id,
                        'destination_operation_id'  => 0,
                        'created_at'                => Carbon::now(),
                        'pos_id'                    => $pos->id,
                        'transaction_id'            => $transaction->id,
                        'client_id'                 => 4,
                        'ondanet_code'              => $ondanet_code,
                        'transaction_type'          => 0 
                    ]);
                }else{
                    $response['success'] = false;
                    $response['reversado']= false;
                    $response['message'] = "La siguiente transaccion $transaction->id no ha sido reversada ya que el proveedor es invalido";
                }

                
                \Log::info('ID: ' .$transaction->atm_id . 'Y monto: ' . $transaction->amount);
                
                //Consulta para traer las ventas que deben ser cobradas
                $sales = \DB::table('miniterminales_sales')
                ->select('movements.id', 'current_account.group_id', 'movements.amount', 'miniterminales_sales.estado','miniterminales_sales.monto_por_cobrar')
                ->join('movements', 'movements.id', '=', 'miniterminales_sales.movements_id')
                ->join('current_account', 'movements.id', '=', 'current_account.movement_id')
                ->where('current_account.group_id', $group->group_id)
                ->where('miniterminales_sales.estado', 'pendiente')
                ->whereNull('movements.deleted_at')
                ->whereRaw("movements.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-16','-16','-17','-21','-23','-26','-27','212', '999')")
                ->orderBy('movements.destination_operation_id','ASC')
                ->get();

                $array=json_decode(json_encode($sales), true);
                \Log::info($array);
                $i=0;
                $sum=0;
                //deposito a ser cobrado
                $deposito=$transaction->amount;

                //algoritmo para traer cuantas ventas van a ser cobradas
                do{
    
                    $sum += $array[$i]['monto_por_cobrar'];
                    $i++;
                    
                }while($sum < $deposito);

                //diferencia de del deposito y la sumatoria total de las ventas
                $dif=$sum-$deposito;

                $idventasondanet = \DB::table('miniterminales_sales')
                ->select('movements.destination_operation_id')
                ->join('movements', 'movements.id', '=', 'miniterminales_sales.movements_id')
                ->join('current_account', 'movements.id', '=', 'current_account.movement_id')
                ->where('current_account.group_id', $group->group_id)
                ->where('miniterminales_sales.estado', 'pendiente')
                ->whereNull('movements.deleted_at')
                ->whereRaw("movements.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-16','-16','-17','-21','-23','-26','-27','212','999')")
                ->orderBy('movements.destination_operation_id','ASC')
                ->take($i)
                ->pluck('movements.destination_operation_id');
    
                $ventasondanet = implode(';', $idventasondanet);
                
                $fecha = date('Y-m-d', strtotime($fecha_reversion));

                $movement_id=\DB::table('movements')->insertGetId([
                    'movement_type_id'          => 3,
                    'destination_operation_id'  => 0,
                    'amount'                    => -(int)$deposito,
                    'debit_credit'              =>  'cr',
                    'created_at'                => Carbon::now(),
                    'updated_at'                => Carbon::now()        

                ]);

                $last_balance = \DB::table('current_account')->where('group_id',$group->group_id)->orderBy('id','desc')->first();
                if(isset($last_balance)){
                    $balance= $last_balance->balance -(int)$deposito;
                }else{
                    $balance= -(int)$deposito;
                }

                \DB::table('current_account')->insert([
                    'movement_id'               => $movement_id,    
                    'group_id'                  => $group->group_id,
                    'amount'                    => -(int)$deposito,
                    'balance'                   => $balance, 
                ]);

                $recibo_id=\DB::table('mt_recibos')->insertGetId([
                    'tipo_recibo_id'    => 5,
                    'movements_id'       => $movement_id,
                    'monto'             => (int)$deposito,
                    'created_at'        => Carbon::now(), 
                    'updated_at'        => Carbon::now()
                ]);

                $recibo_id_deuda=\DB::table('mt_recibos_reversiones')->insert([
                    'recibo_id'         => $recibo_id,
                    'transaction_id'    => $transaction->id,
                    'ventas_cobradas'   => $ventasondanet,
                    'fecha_reversion'   => $fecha,
                    'reversion_id'      => $nro_transaccion,
                    'created_by'        => $this->user->id,
                ]);

                if($i==1){
                    //trae las ventas que tiene en cuenta la cobranza

                    $idventas = \DB::table('miniterminales_sales')
                    ->select('movements.id')
                    ->join('movements', 'movements.id', '=', 'miniterminales_sales.movements_id')
                    ->join('current_account', 'movements.id', '=', 'current_account.movement_id')
                    ->where('current_account.group_id', $group->group_id)
                    ->where('miniterminales_sales.estado', 'pendiente')
                    ->whereNull('movements.deleted_at')
                    ->whereRaw("movements.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-16','-16','-17','-21','-23','-26','-27','212','999')")
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
                    \Log::info('Reversion insertada con el numero de recibo'. $recibo_id );
                }else{
                    if($dif==0){
    
                        //trae las ventas que tiene en cuenta la cobranza
                        $idventas = \DB::table('miniterminales_sales')
                        ->select('movements.id')
                        ->join('movements', 'movements.id', '=', 'miniterminales_sales.movements_id')
                        ->join('current_account', 'movements.id', '=', 'current_account.movement_id')
                        ->where('current_account.group_id', $group->group_id)
                        ->where('miniterminales_sales.estado', 'pendiente')
                        ->whereNull('movements.deleted_at')
                        ->whereRaw("movements.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-16','-16','-17','-21','-23','-26','-27','212','999')")
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
                        ->where('current_account.group_id', $group->group_id)
                        ->where('miniterminales_sales.estado', 'pendiente')
                        ->whereNull('movements.deleted_at')
                        ->whereRaw("movements.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-16','-16','-17','-21','-23','-26','-27','212','999')")
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
                            ->where('current_account.group_id', $group->group_id)
                            ->where('miniterminales_sales.estado', 'pendiente')
                            ->whereNull('movements.deleted_at')
                            ->whereRaw("movements.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-16','-16','-17','-21','-23','-26','-27','212','999')")
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
    
                    \Log::info('Reversion insertada con el numero de recibo'. $recibo_id );
                }
                $response['success'] = true;
                $response['reversado']= true;
                $response['message'] = "La transaccion $transaction->id ha sido reversada exitosamente";
            }else{

                $response['success'] = true;
                $response['reversado']= false;
                $response['message'] = "La siguiente transaccion $transaction->id no ha sido reversada ya que no tiene ventas suficientes";
                
            }

            \Log::info($response);

            return $response;
        } catch (Exception $e) {
            \Log::info($e);
            $response['success'] = false; 
            $response['reversado'] = false;            
            $response['message'] = 'Ha ocurrido un error al migrar la reversion';
            \Log::info($response);
            return $response;       
        }
    }

    public function reversar_v2($transaction, $fecha_reversion, $nro_transaccion){

        try {
            //dd($nro_transaccion);
            $group=\DB::table('branches')
                ->selectRaw('branches.group_id')
                ->join('points_of_sale', 'branches.id', '=', 'points_of_sale.branch_id')
                ->where('points_of_sale.atm_id',$transaction->atm_id)
            ->first();
            //dd($group);
            $total_deuda = \DB::table('mt_sales')
                    ->selectRaw('sum(mt_sales.monto_por_cobrar) as deuda')
                    ->join('mt_movements as m', 'm.id', '=', 'mt_sales.movements_id')
                    ->where('m.atm_id', $transaction->atm_id)
                    ->where('mt_sales.estado', 'pendiente')
                    ->whereNull('m.deleted_at')
                    ->whereRaw("m.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-16','-17','-21','-23','-26','-27','212', '999')")
            ->first();

            //reversion a ser cobrado
            $monto_reversion=abs($transaction->amount);
            //dd($monto_reversion);
            \Log::info(json_decode(json_encode($total_deuda), true));
            $diferencia=$total_deuda->deuda - $monto_reversion;
            \Log::info('[Reversiones Bancard] Diferencia: '. $diferencia);

            $pos=\DB::table('points_of_sale')->where('atm_id', $transaction->atm_id)->first();

            if($transaction->service_source_id == 8){
                \DB::table('incomes')->insert([
                    'operation'                 => 'REGISTER_INCOME_REVERSION_KEN',
                    'origin_operation_id'       => $transaction->id,
                    'destination_operation_id'  => 0,
                    'created_at'                => Carbon::now(),
                    'pos_id'                    => $pos->id,
                    'transaction_id'            => $transaction->id,
                    'client_id'                 => 4,
                    'ondanet_code'              => '026',
                    'transaction_type'          => 0,
                    'incomes_pairing_id'        => 1,  
                ]);
            }else if($transaction->service_source_id != 8){

                switch($transaction->service_source_id){
                    case 0: 
                            if($transaction->service_id == 13 || $transaction->service_id == 14){
                                $ondanet_code='REVIS';
                            }else{
                                if($transaction->service_id == 49){
                                    $ondanet_code='REBIL';
                                }else{
                                    $ondanet_code='REDAR';
                                }
                            } break;
                    case 1: $ondanet_code = 'RENET'; break;
                    case 4: $ondanet_code = 'REPRO'; break;
                    case 7: $ondanet_code = '2050'; break;
                    case 10: $ondanet_code = 'RENET'; break;
                    default: 
                            $response['error'] = true;
                            return $response; break;
                }

                \Log::info($ondanet_code);

                \DB::table('incomes')->insert([
                    'operation'                 => 'REGISTER_INCOME_REVERSION',
                    'origin_operation_id'       => $transaction->id,
                    'destination_operation_id'  => 0,
                    'created_at'                => Carbon::now(),
                    'pos_id'                    => $pos->id,
                    'transaction_id'            => $transaction->id,
                    'client_id'                 => 4,
                    'ondanet_code'              => $ondanet_code,
                    'transaction_type'          => 0 
                ]);
            }else{
                $response['success'] = false;
                $response['reversado']= false;
                $response['message'] = "La siguiente transaccion $transaction->id no ha sido reversada ya que el proveedor es invalido";
            }

            $fecha = date('Y-m-d', strtotime($fecha_reversion));

            if($diferencia >=0){
                
                \Log::info('ID: ' .$transaction->atm_id . 'Y monto: ' . $transaction->amount);
                
                //Consulta para traer las ventas que deben ser cobradas
                $sales = \DB::table('mt_sales')
                    ->select('m.id', 'm.group_id', 'm.amount', 'mt_sales.estado','mt_sales.monto_por_cobrar')
                    ->join('mt_movements as m', 'm.id', '=', 'mt_sales.movements_id')
                    ->where('m.atm_id', $transaction->atm_id)
                    ->where('mt_sales.estado', 'pendiente')
                    ->whereNull('m.deleted_at')
                    ->whereRaw("m.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-16','-16','-17','-21','-23','-26','-27','212', '999')")
                    ->orderBy('m.destination_operation_id','ASC')
                ->get();

                $array=json_decode(json_encode($sales), true);
                \Log::info($array);
                $i=0;
                $sum=0;
                //deposito a ser cobrado
                $deposito=$transaction->amount;

                //algoritmo para traer cuantas ventas van a ser cobradas
                do{
    
                    $sum += $array[$i]['monto_por_cobrar'];
                    $i++;
                    
                }while($sum < $deposito);

                //diferencia de del deposito y la sumatoria total de las ventas
                $dif=$sum-$deposito;

                $idventasondanet = \DB::table('mt_sales')
                    ->select('m.destination_operation_id')
                    ->join('mt_movements as m', 'm.id', '=', 'mt_sales.movements_id')
                    ->where('m.atm_id', $transaction->atm_id)
                    ->where('mt_sales.estado', 'pendiente')
                    ->whereNull('m.deleted_at')
                    ->whereRaw("m.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-16','-16','-17','-21','-23','-26','-27','212','999')")
                    ->orderBy('m.destination_operation_id','ASC')
                    ->take($i)
                ->pluck('m.destination_operation_id');
    
                $ventasondanet = implode(';', $idventasondanet);

                $last_balance = \DB::table('mt_movements')->where('atm_id', $transaction->atm_id)->orderBy('id','desc')->first();

                if(isset($last_balance)){
                    $balance= $last_balance->balance -(int)$deposito;
                    $balance_antes= $last_balance->balance;
                }else{
                    $balance= -(int)$deposito;
                    $balance_antes=0;
                }

                $movement_id=\DB::table('mt_movements')->insertGetId([
                    'movement_type_id'          => 3,
                    'destination_operation_id'  => 0,
                    'amount'                    => -(int)$deposito,
                    'debit_credit'              =>  'cr',
                    'created_at'                => Carbon::now(),
                    'updated_at'                => Carbon::now(),
                    'group_id'                  => $group->group_id,
                    'atm_id'                    => $transaction->atm_id,
                    'balance_antes'             => $balance_antes,
                    'balance'                   => $balance

                ]);

                $recibo_id=\DB::table('mt_recibos')->insertGetId([
                    'tipo_recibo_id'    => 5,
                    'mt_movements_id'       => $movement_id,
                    'monto'             => (int)$deposito,
                    'created_at'        => Carbon::now(), 
                    'updated_at'        => Carbon::now()
                ]);

                $recibo_id_deuda=\DB::table('mt_recibos_reversiones')->insert([
                    'recibo_id'         => $recibo_id,
                    'transaction_id'    => $transaction->id,
                    'ventas_cobradas'   => $ventasondanet,
                    'fecha_reversion'   => $fecha,
                    'reversion_id'      => $nro_transaccion,
                    'created_by'        => $this->user->id,
                    'saldo_pendiente'       => 0
                ]);

                if($i==1){
                    //trae las ventas que tiene en cuenta la cobranza

                    $idventas = \DB::table('mt_sales')
                        ->select('m.id')
                        ->join('mt_movements as m', 'm.id', '=', 'mt_sales.movements_id')
                        ->where('m.atm_id', $transaction->atm_id)
                        ->where('mt_sales.estado', 'pendiente')
                        ->whereNull('m.deleted_at')
                        ->whereRaw("m.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-16','-16','-17','-21','-23','-26','-27','212','999')")
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
                    }
                    \Log::info('Reversion insertada con el numero de recibo'. $recibo_id );
                }else{
                    if($dif==0){
    
                        //trae las ventas que tiene en cuenta la cobranza
                        $idventas = \DB::table('mt_sales')
                            ->select('m.id')
                            ->join('mt_movements as m', 'm.id', '=', 'mt_sales.movements_id')
                            ->where('m.atm_id', $transaction->atm_id)
                            ->where('mt_sales.estado', 'pendiente')
                            ->whereNull('m.deleted_at')
                            ->whereRaw("m.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-16','-16','-17','-21','-23','-26','-27','212','999')")
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
                        }
                        
                    }else{
                        $cobradostotal=$i-1;
    
                        $salescobradas = \DB::table('mt_sales')
                            ->select('m.id')
                            ->join('mt_movements as m', 'm.id', '=', 'mt_sales.movements_id')
                            ->where('m.atm_id', $transaction->atm_id)
                            ->where('mt_sales.estado', 'pendiente')
                            ->whereNull('m.deleted_at')
                            ->whereRaw("m.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-16','-16','-17','-21','-23','-26','-27','212','999')")
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
                        }
    
                        if($result = true){
                            $salefaltante = \DB::table('mt_sales')
                                ->select('m.id')
                                ->join('mt_movements as m', 'm.id', '=', 'mt_sales.movements_id')
                                ->where('m.atm_id', $transaction->atm_id)
                                ->where('mt_sales.estado', 'pendiente')
                                ->whereNull('m.deleted_at')
                                ->whereRaw("m.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-16','-16','-17','-21','-23','-26','-27','212','999')")
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
                            \DB::table('mt_movements')->where('id', $sale)->update([ 'updated_at' => Carbon::now() ]);
                        }
                    }
    
                    \Log::info('Reversion insertada con el numero de recibo'. $recibo_id );
                }
                $response['success'] = true;
                $response['reversado']= true;
                $response['message'] = "La transaccion $transaction->id ha sido reversada exitosamente";
            }else{

                $response['success'] = true;
                $response['reversado']= true;
                $response['message'] = "El monto de la siguiente transaccion $transaction->id es mayor que las ventas del grupo, no se procedera a cobrar ventas";
                
                //if($total_deuda->deuda < 0 || empty($total_deuda->deuda)){
                                
                    $last_balance = \DB::table('mt_movements')->where('atm_id',$transaction->atm_id)->orderBy('id','desc')->first();
                    
                    if(isset($last_balance)){
                        $balance= $last_balance->balance -(int)$transaction->amount;
                        $balance_antes= $last_balance->balance;
                    }else{
                        $balance= -(int)$transaction->amount;
                        $balance_antes=0;
                    }

                    $movement_id=\DB::table('mt_movements')->insertGetId([
                        'movement_type_id'          => 3,
                        'destination_operation_id'  => 666,
                        'amount'                    => -(int)$transaction->amount,
                        'debit_credit'              =>  'cr',
                        'created_at'                => Carbon::now(),
                        'updated_at'                => Carbon::now(),
                        'group_id'                  => $group->group_id,
                        'atm_id'                    => $transaction->atm_id,
                        'balance_antes'             => $balance_antes,
                        'balance'                   => $balance

                    ]);

                    $recibo_id=\DB::table('mt_recibos')->insertGetId([
                        'tipo_recibo_id'    => 5,
                        'mt_movements_id'       => $movement_id,
                        'monto'             =>(int)$transaction->amount,
                        'created_at'        => Carbon::now(), 
                        'updated_at'        => Carbon::now()
                    ]);

                    $recibo_id_deuda=\DB::table('mt_recibos_reversiones')->insert([
                        'recibo_id'         => $recibo_id,
                        'transaction_id'    => $transaction->id,
                        'fecha_reversion'   => $fecha,
                        'reversion_id'      => $nro_transaccion,
                        'created_by'        => $this->user->id,
                        'saldo_pendiente'   => $transaction->amount
                    ]);
                    
                //}
                
            }

            \Log::info($response);

            return $response;
        } catch (Exception $e) {
            \Log::info($e);
            $response['success'] = false; 
            $response['reversado'] = false;            
            $response['message'] = 'Ha ocurrido un error al migrar la reversion';
            \Log::info($response);
            return $response;       
        }
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
}
