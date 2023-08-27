<?php
/**
 * Author: Gustavo Herrera
 * Desciption: Formulario para altas de boletas de depósito de arqueos en terminales
 * Date: 2021/08/13
 */
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Session;
use App\Http\Requests;
use App\Http\Controllers\Controller;

class DepositosTerminalesController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function index(Request $request)
    {
        if (!$this->user->hasAccess('depositos_arqueos')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $filtro = $request->get('filtro');  
        $option = [
            'value'=> '',
            'description'=> ''
        ];

        $datos = \DB::table('depositos_arqueos')
        ->select('fecha_boleta','tipo_credito_id','descripcion','boleta_nro','amount','recaudadores.username as recaudador','ondanet_id','response_data','users.username')
        ->join('bancos','bancos.id','=','banco_id')
        ->leftjoin('users as recaudadores','recaudadores.doc_number','=','depositos_arqueos.recaudador_id')
        ->join('users','users.id','=','depositos_arqueos.updated_by')
        ->orderby('depositos_arqueos.id','desc');

        if(empty($filtro)){
            $deposits = $datos->get();
            $option['description'] = 'Todos';
            return view('depositos_arqueos.index', compact('deposits','option'));
        }

        if($filtro === 'Exitoso'){

            $deposits = $datos->whereRaw('CAST (ondanet_id AS INTEGER) > 999')
                                ->get();

            $option['value'] = 'Exitoso';
            $option['description'] = 'Exitoso';

             return view('depositos_arqueos.index', compact('deposits','option'));
        }

        if($filtro === 'Pendiente'){
            $deposits = $datos->whereRaw('CAST (ondanet_id AS INTEGER) < 999')
                              ->get();

            $option['value'] = 'Pendiente';
            $option['description'] = 'Pendiente';
            return view('depositos_arqueos.index', compact('deposits','option'));
        }
              
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        if (!$this->user->hasAccess('depositos_arqueos')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        
        $comboTipoCreditos = [];
        $tipo_creditos = \DB::connection('ondanet')->table('TIPO_CREDITO_DEPOSITO_TERMINAL')->get();        

        foreach ($tipo_creditos as $key => $tipo_credito) {
            $comboTipoCreditos[trim($tipo_credito->{'NUMERO TIPO CREDITO'})] = $tipo_credito->{'DESCRIPCION TIPO CREDITO'};
        }

        $comboRecaudadores = [];
        $recaudadores = \DB::connection('ondanet')->table('RECAUDADOR')->get();
        foreach ($recaudadores as $key => $recaudador) {
            $comboRecaudadores[trim($recaudador->{'C.I. RECAUDADOR'})] = $recaudador->{'C.I. RECAUDADOR'}.' - '.$recaudador->{'NOMBRE RECAUDADOR'};
        }
        

        $bancos = \DB::table('bancos')->pluck('descripcion', 'id');        
        
        return view('depositos_arqueos.create', compact('comboRecaudadores', 'comboTipoCreditos', 'bancos'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!$this->user->hasAccess('depositos_arqueos')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = $request->all();        
        $transactions = json_decode($input['transactions'],true);        
        try
        {
            $deposit_id = \DB::table('depositos_arqueos')->insertGetId(
                [
                    'amount'            => $input['total_amount'],
                    'banco_id'          => $input['banco_id'],
                    'boleta_nro'        => $input['boleta_numero'],
                    'fecha_boleta'      => $input['fecha'],
                    'recaudador_id'     => $input['recaudador'],
                    'tipo_credito_id'   => $input['tipo_credito'],
                    'updated_by'        => $this->user->id
                ]
            );

            if($deposit_id)
            {
                $detalles   = [];
                $item       = [];
                foreach($transactions as $key => $transaction) {                
                    $item['deposit_id'] = $deposit_id;
                    $item['transaction_id'] = $key;
                    $item['custom_amount'] = str_replace('.','',$transaction[0]);
                    $item['custom_motivo'] = str_replace('.','',$transaction[1]);
                
                    $detalles[] = $item;
                }  
                
                $deposit_detalles = \DB::table('depositos_arqueos_detalle')->insert($detalles);
            }                        

            return redirect()->route('depositos_arqueos.index')->with('success', 'Depósito registrado exitosamente');
        }catch (\Exception $e){
                \Log::critical($e->getMessage());                
                return redirect()->route('depositos_arqueos.index')->with('error', 'Error al registrar depósito');
        }

        
    }

      
    public function getTransactions($ci)
    {
        $total_amount = 0;
        $transactions = \DB::table('transactions')
        ->select('transactions.id','amount','transactions.status','transactions.created_at','points_of_sale.description','users.username','users.doc_number')
        ->join('points_of_sale','points_of_sale.atm_id','=','transactions.atm_id')
        ->join('transactions_x_arqueos','transactions_x_arqueos.transaction_id','=','transactions.id')
        ->join('atm_arqueos_hash','atm_arqueos_hash.id','=','atm_arqueos_hash_id')
        ->join('users','users.id','=','atm_arqueos_hash.user_id')
        ->leftjoin('depositos_arqueos_detalle','depositos_arqueos_detalle.transaction_id','=','transactions.id')
        ->whereNull('depositos_arqueos_detalle.transaction_id')
        ->whereIn('transaction_type',[2,3,8,9,10])
        ->whereIn('transactions.owner_id',[11,14,19])
        ->where('transactions.amount','<>',0)
        ->where('doc_number','=',$ci)
        ->where('transactions.created_at','>=','2021-08-22')
        ->orderBy('transactions.id','DESC')        
        ->get();

        foreach($transactions as $transaction)
        {
            
            $total_amount = $total_amount + $transaction->amount;
            $transaction->amount = number_format($transaction->amount,0,',','.');
        }
        
                
        $response = [
            'count'     => count($transactions),
            'data'      => $transactions,
            'total_amount' => number_format($total_amount,0,',','.')
        ];

        return $response;
    }
}
