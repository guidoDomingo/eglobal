<?php


namespace App\Services;

use App\Exports\ExcelExport;
use App\Models\Atm;
use App\Models\MiniCashoutType;
use Carbon\Carbon;
use HttpClient;
use Session;

class MiniCashOutDevolucionServices
{

    protected $user;

    public function __construct($atm_id)
    {
        $this->user = \Sentinel::getUser();

        \Log::info('atm_id',['result'=>$atm_id]);

        if ($atm_id != null && $atm_id <> 0) {
            \Log::info('entramos aca');
            $atm = Atm::select('public_key', 'private_key')->where('id', $atm_id)->first();
           // \Log::info('atm',['result'=> response()->json($atm)]);
            $this->public_key = $atm->public_key;
            $this->app_key    = $atm->private_key;

            $this->env = env('APP_ENV');

            if ($this->env == 'local') {
                //* Testing
                //$this->url = 'http://192.168.3.33:85';
                //* Desarrollo
                $this->url = 'http://eglobaltws.local';
            } else if ($this->env == 'remote') {
                $this->url = 'https://api.eglobalt.com.py'; // Uno de los cambios luego de subir a producción
            }
        }
    }



    public function successMini($id)
    {
        try {

            $message = 'Se realizo correctamente la entrega del dinero';
            $amountVista = 0;
            $error   = false;
            Session::flash('message', $message);


            $mini = \DB::table('mini_cashout_devolution_details')
                ->where('id', $id)
                ->first();
            $data = json_decode($mini->parameters);
            $parameters = json_decode($data);

            $atm_id             = $mini->atm_id;
            $service_id         = $mini->services_id;
            $atm_transaction_id = $mini->atm_transaction_id;
            $transaction_type   = 7;
            $service_source_id  = $mini->services_source_id;
            $generated_key_id   = 0;

            $endPoint = '';
            $json = '';



            if ($mini->type_id == 1) {

                \Log::info($this->user);
                $json = array(
                    'atm_id' => $atm_id,
                    'service_id' => $service_id,
                    'atm_transaction_id' => $atm_transaction_id,
                    'transaction_type' => $transaction_type,
                    'referencia1' => '',
                    'referencia2' => '',
                    'amount' => 0,
                    'source_id' => $service_source_id,
                    'generated_key_id' => $generated_key_id
                );
                \Log::info('valor', ['json' => $json]);
                //Todo: Empezamos con la creacion de la transactions
                $endPoint = '/transactions/create';
                \Log::info('json', ['result' => $json]);
                $transaction = $this->httpPostAll($endPoint, $json, $service_id, $service_source_id);
                \Log::info('respuesta transaccion', ['result' => $transaction]);
                if ($transaction['error'] == true) {
                    \Log::info('Error en crear transactions', ['result' => $transaction]);
                    $message = 'Error inesperado vuelva a intentar en unos instantes.';
                    $error  = true;
                    return response()->json([
                        'error' => $error,
                        'message' => $message,
                        'amount' => $amountVista
                    ]);
                }

                //todo: Camnbiar para produccion los services_source_id!!!!!! ojo!!

                if ($service_id == 8 && $service_source_id == 0) {
                    //Todo: Tigo!
                    $amount = $parameters->amount;
                    $referencia1 = $parameters->msisdn;
                    $referencia2 = '';
                    //todo:actualizamos la transaction con su valor amount y referencias
                    $updateTransaction = $this->updateTransaction($transaction['backend_transaction_id'], $amount, $referencia1, $referencia2);
                    if ($updateTransaction['error'] == true) {
                        $this->updateMiniDetails($mini->id, $transaction['backend_transaction_id'], 'error');
                        \Log::info('Error al tratar de Actualizar la transaccion con Tigo', ['transaction_id' => $transaction['backend_transaction_id']]);
                        $message = 'Ocurrio un error inesperado.';
                        $error  = true;
                        Session::flash('message', $message);
                    }
                    $json = array(
                        'msisdn' => $parameters->msisdn,
                        'amount' => $amount,
                        'client_name' => $parameters->client_name,
                        'idnum' =>  $parameters->idnum,
                        'address' => isset($parameters->address) ? $parameters->address : null,
                        'birthday' => $parameters->birthday,
                        'atm_id' => $atm_id,
                        'backend_transaction_id' => $transaction['backend_transaction_id'],
                        'atm_transaction_id' => $atm_transaction_id,
                        'service_id' => $service_id
                    );

                    $endPoint = '/app/girostigo/cash';
                    //todo: Respectiva peticion
                    $responseTigo = $this->httpPostAll($endPoint, $json, $service_id, $service_source_id);
                    \Log::info('Respuesta tigo', ['result' => $responseTigo]);
                    if ($responseTigo['error'] == true) {
                        $this->updateMiniDetails($mini->id, $transaction['backend_transaction_id'], 'error');
                        $message = 'Error en la entrega del dinero.';
                        $error  = true;
                        Session::flash('message', $message);
                    } else {
                        $this->updateMiniDetails($mini->id, $transaction['backend_transaction_id'], 'procesado');
                        //*actualizar transaccion con identificador de transaccion y ticket
                        $this->updateTransactionfinish($transaction['backend_transaction_id'],$responseTigo['transaction_id'],$responseTigo['numero boleta']);

                    }
                } elseif ($service_id == 92 && $service_source_id == 8) {
                    //todo: Personal!
                    $amount = $parameters->amount;
                    $referencia1 = $parameters->source_msisdn;
                    $referencia2 = '';
                    //todo:actualizamos la transaction con su valor amount y referencias
                    $updateTransaction = $this->updateTransaction($transaction['backend_transaction_id'], $amount, $referencia1, $referencia2);
                    if ($updateTransaction['error'] == true) {
                        $this->updateMiniDetails($mini->id, $transaction['backend_transaction_id'], 'error');
                        \Log::info('Error al tratar de Actualizar la transaccion con Personal', ['transaction_id' => $transaction['backend_transaction_id']]);
                        $message = 'Ocurrio un error inesperado.';
                        $error  = true;
                        Session::flash('message', $message);
                    }

                    $json = array(
                        'backend_transaction_id' => $transaction['backend_transaction_id'],
                        'service_id' => $service_id,
                        'atm_id' => $atm_id,
                        'external_id' => $transaction['backend_transaction_id'],
                        'amount' => $amount,
                        'source_msisdn' => $parameters->source_msisdn,
                        'source_id' => $transaction['backend_transaction_id'],
                        'source_name' => $parameters->source_name,
                        'source_address' => $parameters->source_address
                    );
                    $endPoint = '/momo_personal_payments/cashout_confirm';

                    $responsePersonal = $this->httpPostAll($endPoint, $json, $service_id, $service_source_id);
                    \Log::info('Respuesta Personal', ['result' => $responsePersonal]);

                    if ($responsePersonal['error'] == true) {
                        $this->updateMiniDetails($mini->id, $transaction['backend_transaction_id'], 'error');
                        $message = 'Error en la entrega del dinero.';
                        $error  = true;
                        Session::flash('message', $message);
                    } else {
                        $this->updateMiniDetails($mini->id, $transaction['backend_transaction_id'], 'procesado');
                        //*actualizar transaccion con identificador de transaccion y ticket
                        //*revisar cuales serian los parametros a recibir
                        //$this->updateTransactionfinish($transaction['backend_transaction_id'],$responseTigo['transaction_id'],$responseTigo['numero boleta']);
                    }
                } elseif ($service_id == 5 && $service_source_id == 8) {

                    //todo: Claro!
                    $amount = $parameters->monto;
                    $referencia1 = $parameters->numero_origen;
                    $referencia2 = $parameters->documento;

                    //todo:actualizamos la transaction con su valor amount y referencias
                    $updateTransaction = $this->updateTransaction($transaction['backend_transaction_id'], $amount, $referencia1, $referencia2);
                    if ($updateTransaction['error'] == true) {
                        $this->updateMiniDetails($mini->id, $transaction['backend_transaction_id'], 'error');
                        \Log::info('Error al tratar de Actualizar la transaccion con Claro', ['transaction_id' => $transaction['backend_transaction_id']]);
                        $message = 'Ocurrio un error inesperado.';
                        $error  = true;
                        Session::flash('message', $message);
                    }
                    $json = array(
                        'atm_id' => $atm_id,
                        'documento' => $referencia2,
                        'numero_origen' => $referencia1,
                        'monto' => $amount,
                        'otp' => isset($parameters->otp) ? $parameters->otp : null,
                        'service_id' => $service_id,
                        'atm_transaction_id' => $atm_transaction_id,
                        'backend_transaction_id' => $transaction['backend_transaction_id'],
                        'service_source_id' => $service_source_id
                    );
                    $endPoint = '/toval/claro/efectivizacion/confirmacion';

                    $responseClaro = $this->httpPostAll($endPoint, $json, $service_id, $service_source_id);
                    \Log::info('Respuesta Claro', ['result' => $responseClaro]);

                    if ($responseClaro['error'] == true) {
                        $this->updateMiniDetails($mini->id, $transaction['backend_transaction_id'], 'error');
                        $message = 'Error en la entrega del dinero.';
                        $error  = true;
                        Session::flash('message', $message);
                    } else {
                        $this->updateMiniDetails($mini->id, $transaction['backend_transaction_id'], 'procesado');
                    }
                } elseif ($service_id == 98 && $service_source_id == 0) {
                    //todo: Telebingo!
                    //todo: revisar cuales serian los parametros si estan bien o no
                    $amount = $parameters->amount;
                    $referencia1 = $parameters->doc;
                    $referencia2 = '';

                    //todo:actualizamos la transaction con su valor amount y referencias
                    $updateTransaction = $this->updateTransaction($transaction['backend_transaction_id'], $amount, $referencia1, $referencia2);
                    if ($updateTransaction['error'] == true) {
                        $this->updateMiniDetails($mini->id, $transaction['backend_transaction_id'], 'error');
                        \Log::info('Error al tratar de Actualizar la transaccion con Telebingo', ['transaction_id' => $transaction['backend_transaction_id']]);
                        $message = 'Ocurrio un error inesperado.';
                        $error  = true;
                        Session::flash('message', $message);
                    }
                    $json = array(
                        'Clienteid' => $parameters->Clienteid,
                        'Djid' => $parameters->Djid,
                        'Jjugadas' => $parameters->Jjugadas,
                        'Ppremio' => $parameters->Ppremio,
                        'Rcaridout' => $parameters->Rcaridout,
                        'nombre' => 'Nombre',
                        'numero_doc' => $parameters->doc,
                        'atm_id' => $atm_id,
                        'backend_transaction_id' => $transaction['backend_transaction_id'],
                        'atm_transaction_id' => $atm_transaction_id,
                        'amount' => $amount,
                    );
                    $endPoint = '/talisman/exchange_cash';

                    $responseTelebingo = $this->httpPostAll($endPoint, $json, $service_id, $service_source_id);
                    \Log::info('Respuesta Telebingo', ['result' => $responseTelebingo]);

                    if ($responseTelebingo['error'] == true) {
                        $this->updateMiniDetails($mini->id, $transaction['backend_transaction_id'], 'error');
                        $message = 'Error en la entrega del dinero.';
                        $error  = true;
                        Session::flash('message', $message);
                    } else {
                        $this->updateMiniDetails($mini->id, $transaction['backend_transaction_id'], 'procesado');
                    }

                    /*}elseif($service_id == 86 && $service_source_id == 9){
                    todo: Quiniela
                    $json = [
                        'atm_id' => $atm_id,
                        'service_id' => $service_id,
                        'atm_transaction_id' =>$atm_transaction_id,
                        'backend_transaction_id' => $transaction['backend_transaction_id'],
                        'serie' =>$parameters->serie,
                        'ticket' => $parameters->ticket,
                        'fecha' => $parameters->fecha,
                        'token' => $parameters->token,
                    ];
                    $endPoint = '/tdp/v2/cashout_quiniela';

                    $responseQuiniela = $this->httpPostAll($endPoint,$json);
                    \Log::info('Respuesta Quiniela',['result'=>$responseQuiniela]);

                    if($responseQuiniela['error'] == true){
                        $message = 'Error en la entrega del dinero.';
                        $error  = true;
                        Session::flash('message', $message);
                    }
                    $data = $responseQuiniela['data'];
                    $pagoPremio = $data['pagarPremio'];
                    $amount = $pagoPremio['monto_ganado'];
                    $referencia1 = $parameters->ticket;
                    $referencia2 = $parameters->serie;
        
                    todo:actualizamos la transaction con su valor amount y referencias
                    $updateTransaction = $this->updateTransaction($transaction['backend_transaction_id'],$amount,$referencia1,$referencia2);
                    if($updateTransaction['error'] == true){
                        $this->updateMiniDetails($mini->id,$transaction['backend_transaction_id'],'error');
                        \Log::info('Error al tratar de Actualizar la transaccion con Claro',['transaction_id' => $transaction['backend_transaction_id']]);
                        $message = 'Ocurrio un error inesperado.';
                        $error  = true;
                        Session::flash('message', $message);
                    }
                        todo: Actualizamos el details
                    if($responseQuiniela['error'] == true){
                        $this->updateMiniDetails($mini->id,$transaction['backend_transaction_id'],'error');
                        $message = 'Error en la entrega del dinero.';
                        $error  = true;
                        Session::flash('message', $message);
                    }else{
                        $this->updateMiniDetails($mini->id,$transaction['backend_transaction_id'],'procesado');
                        $amountVista = number_format($amount);
                        
                    }

               */
                } elseif ($service_id == 87 && $service_source_id == 0) {
                    //todo: Apostala  
                    $amount = $parameters->amount;
                    $referencia1 = $parameters->ci;
                    $referencia2 = '';

                    //todo:actualizamos la transaction con su valor amount y referencias
                    $updateTransaction = $this->updateTransaction($transaction['backend_transaction_id'], $amount, $referencia1, $referencia2);
                    if ($updateTransaction['error'] == true) {
                        $this->updateMiniDetails($mini->id, $transaction['backend_transaction_id'], 'error');
                        \Log::info('Error al tratar de Actualizar la transaccion con Apostala', ['transaction_id' => $transaction['backend_transaction_id']]);
                        $message = 'Ocurrio un error inesperado.';
                        $error  = true;
                        Session::flash('message', $message);
                    }
                    $json = array(
                        'transaction_id' => $transaction['backend_transaction_id'],
                        'amount'         => $amount,
                        'code'           => $parameters->code,
                        'ci'             => $parameters->ci,
                    );
                    $endPoint = '/apostala_payments/extraction/confirm';

                    $responseApostala = $this->httpPostAll($endPoint, $json, $service_id, $service_source_id);
                    \Log::info('Respuesta Apostala', ['result' => $responseApostala]);

                    if ($responseApostala['error'] == true) {
                        $this->updateMiniDetails($mini->id, $transaction['backend_transaction_id'], 'error');
                        $message = 'Error en la entrega del dinero.';
                        $error  = true;
                        Session::flash('message', $message);
                    } else {
                        $this->updateMiniDetails($mini->id, $transaction['backend_transaction_id'], 'procesado');
                    }
                } else {
                    //todo: aca debemos de actualizar la transaccion en error!!

                    $class = __CLASS__;
                    $function = __FUNCTION__;
                    $messageData = ' Error no existe cashOut para ese servicio';

                    \Log::info('Clase' . $class . ' Funcion' . $function . $messageData);
                    $message = 'Error en la entrega del dinero servicio no disponible.';
                    $error  = true;

                    $this->updateMiniDetails($mini->id, $transaction['backend_transaction_id'], 'error');

                    $updateTransaction = \DB::table('transactions')
                        ->where('id', $transaction['backend_transaction_id'])
                        ->update([
                            'status' => 'error',
                            'status_description' => $messageData,
                            'response_data' => '',
                            'processed' => 1
                        ]);
                    Session::flash('message', $message);
                }
            } else {

                \Log::info('entramos en VuelDev');

                $response = $this->updateVuelDev($id);

                \Log::info($response);
                if ($response['error'] == true) {
                    $message = 'Error en la entrega del dinero.';
                    $error  = true;
                    Session::flash('message', $message);
                }
            }
        } catch (\Exception $e) {
            $error_detail = [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];

            \Log::error("Error, Detalles: " . json_encode($error_detail));
            $message = 'Error en la entrega del dinero.';
            $error = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
            'amount' => $amountVista
        ]);
    }

    public function updateMiniDetails($id, $transaction_id, $status)
    {
        $response = [
            'error' => false
        ];

        try {

            $updateMinis = \DB::table('mini_cashout_devolution_details')
                ->where('id', $id)
                ->update([
                    'transaction_id' => $transaction_id,
                    'status' => $status,
                    'updated_at' => Carbon::now()
                ]);
        } catch (\Exception $e) {
            $error_detail = [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];

            \Log::error("Error, Detalles: " . json_encode($error_detail));
            $response['error'] = true;
        }

        return $response;
    }
    //  todo Hay que mirar si la actualizacion va ser de esta manera
    public function updateVuelDev($id)
    {

        $response = [
            'error' => false
        ];

        try {

            $updateMinis = \DB::table('mini_cashout_devolution_details')
                ->where('id', $id)
                ->update([
                    'status' => 'procesado',
                    'updated_at' => Carbon::now()
                ]);

            \Log::info($updateMinis);

            // $payment = \DB::table('transactions_x_payments')
            //     ->select('payments.id')
            //     ->join('payments', 'payments.id', '=', 'transactions_x_payments.payments_id')
            //     ->where('transactions_x_payments.transactions_id', $id)
            //     ->get();

            //  \Log::info('edit', ['payment'=> json_encode($payment)]);

            // $updatePayments = \DB::table('payments')
            //     ->where('id', $payment[0]->id)
            //     ->update([
            //         'valor_entregado' => intval($amount),
            //         'updated_at' => Carbon::now()
            //     ]);
            //     \Log::info($updatePayments);

        } catch (\Exception $e) {
            $error_detail = [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];

            \Log::error("Error, Detalles: " . json_encode($error_detail));
            $response['error'] = true;
        }

        return $response;
    }

    public function httpPostAll($endPoint, $json, $service_id, $source_id)
    {

        $class = __CLASS__;
        $function = __FUNCTION__;

        \Log::info("\n\nCampos obtenidos en $class \ $function:\n\n\n\n");

        // ----------------------------------------------------------------------

        $url = $this->url . $endPoint;
        $app_key = $this->app_key;
        $public_key = $this->public_key;

        //----------------------------------------------------------------------
        try {
            \Log::info('json', ['result' => $json]);

            $date = Carbon::now();
            $params = array($date->format('D, d M Y H:i:s T00'), 'POST', $endPoint, json_encode($json));
            $params_hash = hash_hmac("sha1", implode("\n", $params), $app_key);
            \Log::info('date', ['result' => $date]);
            \Log::info('params_hash', ['result' => $params_hash]);
            \Log::info('public_key', ['result' => $public_key]);
            \Log::info('url', ['result' => $url]);

            $petition = HttpClient::post(
                $url,
                [
                    'auth' => [$public_key, $params_hash],
                    'headers' => [
                        'service-id' => $service_id,
                        'service-source-id' => $source_id,
                        'Timestamp' => $date->format('D, d M Y H:i:s T00')
                    ],
                    'json' => $json,
                    'connect_timeout' => 180,
                    'verify' => false
                ]
            );
            $response = json_decode($petition->getBody()->getContents(), true);
            \Log::info($response);
        } catch (\Exception $e) {
            $error_detail = [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];

            \Log::error("Error, Detalles: " . json_encode($error_detail));
            $response['error'] = true;
        }
        return $response;
    }

    public function updateTransactionfinish($id,$identificador,$factura_num){

        $response = [
            'error' => false
        ];

        try {

            $updateTransaction = \DB::table('transactions')
                ->where('id', $id)
                ->update([
                    'identificador_transaction_id' => $identificador,
                    'factura_numero' => $factura_num,
                    'updated_at' => Carbon::now()
                ]);
        } catch (\Exception $e) {
            $error_detail = [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];

            \Log::error("Error, Detalles: " . json_encode($error_detail));
            $response['error'] = true;
        }

        return $response;

    }

    public function updateTransaction($id, $amount, $referencia1, $referencia2)
    {

        $response = [
            'error' => false
        ];

        $amountNeg = $amount * (-1);

        try {

            $updateTransaction = \DB::table('transactions')
                ->where('id', $id)
                ->update([
                    'amount' => $amountNeg,
                    'referencia_numero_1' => $referencia1,
                    'referencia_numero_2' => $referencia2,
                    'updated_at' => Carbon::now()
                ]);
        } catch (\Exception $e) {
            $error_detail = [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];

            \Log::error("Error, Detalles: " . json_encode($error_detail));
            $response['error'] = true;
        }

        return $response;
    }

    public function cancelMin($id, $motivo)
    {

        try {
            $message = 'Se cancelo correctamente la peticion';
            $error   = false;
            Session::flash('message', $message);

            $updateMinis = \DB::table('mini_cashout_devolution_details')
                ->where('id', $id)
                ->update([
                    'status' => 'cancelado',
                    'detailscancel' => $motivo,
                    'updated_at' => Carbon::now()
                ]);

            \Log::info($updateMinis);
        } catch (\Exception $e) {
            $error_detail = [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];

            \Log::error("Error, Detalles: " . json_encode($error_detail));
            $message = 'Error en la entrega del dinero.';
            $error = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }

    public function getDatamini()
    {
        $data = null;
        $response = $this->getDataMiniSearch($data);
        return $response;
    }

    public function getDataMiniSearch($input)
    {

        try {
            if (empty($input)) {
                $fechaActual     = Carbon::now();
                $desde           = date("Y-m-d 00:00:00", strtotime($fechaActual));
                $hasta           = date("Y-m-d 23:59:59", strtotime($fechaActual));
                $reservationtime = $desde . ' - ' . $hasta;

                $status_id = 'todos';
                $tipo_id   = 0;
                $atm_id    = 0;
            } else {
                $status_id       = $input['status_id'];
                $reservationtime = $input['reservationtime'];
                $tipo_id         = $input['tipo_id'];
                $atm_id          = $input['atm_id'];

                $daterange    = explode(' - ',  str_replace('/', '-', $reservationtime));
                $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                $desde        = date('Y-m-d H:i:s', strtotime($daterange[0]));
                $hasta        = date('Y-m-d H:i:s', strtotime($daterange[1]));
            }

            $tipo = MiniCashoutType::pluck('description', 'id')->prepend('Todos', '0')->toArray();
            $atm  = Atm::join('atms_per_users', 'atms_per_users.atm_id', '=', 'atms.id')
                ->where('atms_per_users.user_id', $this->user->id)
                ->pluck('atms.name', 'atms.id')->prepend('Todos', '0')->toArray();
            \Log::info($atm);
   
            $minisData = \DB::table('mini_cashout_devolution_details')
                ->select('atms.name', 'mini_cashout_devolution_details.id', 'marcas.descripcion as marca', 'mini_cashout_devolution_details.transaction_id', 'servicios_x_marca.descripcion', 'mini_cashout_devolution_details.parameters', 'mini_cashout_devolution_type.description as tipo', 'mini_cashout_devolution_details.hash_table', 'mini_cashout_devolution_details.status', 'mini_cashout_devolution_details.created_at')
                ->join('mini_cashout_devolution_type', 'mini_cashout_devolution_type.id', '=', 'mini_cashout_devolution_details.type_id')
                ->join('servicios_x_marca', function ($join) {
                    $join->on('mini_cashout_devolution_details.services_id', '=', 'servicios_x_marca.service_id');
                    $join->on('mini_cashout_devolution_details.services_source_id', '=', 'servicios_x_marca.service_source_id');
                })
                ->join('atms', 'mini_cashout_devolution_details.atm_id', '=', 'atms.id')
                ->join('atms_per_users', 'atms_per_users.atm_id', '=', 'atms.id')
                ->join('marcas', 'marcas.id', '=', 'servicios_x_marca.marca_id')
                ->where(function ($query) use ($status_id) {
                    if (!empty($status_id) &&  $status_id  <> 'todos') {
                        $query->where('mini_cashout_devolution_details.status', '=', $status_id);
                    }
                })
                ->where(function ($query) use ($tipo_id) {
                    if (!empty($tipo_id) &&  $tipo_id  <> 0) {
                        $query->where('mini_cashout_devolution_details.type_id', '=', $tipo_id);
                    }
                })
                ->where(function ($query) use ($atm_id) {
                    if (!empty($atm_id) &&  $atm_id  <> 0) {
                        $query->where('atms.id', '=', $atm_id);
                    }
                })
                ->whereBetween('mini_cashout_devolution_details.created_at', [$desde, $hasta])
                ->where('atms_per_users.user_id', $this->user->id)
                ->get();
            $minis = [];
            $amountView = 0;
            $transactionsCount = 0;
       
            
            foreach ($minisData as $mini) {
            
                $transactionsCount++;
                $data1 =  json_decode($mini->parameters);
                $data = json_decode($data1);
                $amount = 0;
            
                if ($mini->marca == 'Claro Billetera') {
                    $amount =  $data->monto ?? 0;
                    $amountView = $amountView + ($data->monto ?? 0);
                } elseif ($mini->tipo == 'Devolucion' || $mini->tipo == 'Vuelto') {
                    $amount = $data->valor_entrega;
                    $amountView = $amountView + $data->valor_entrega;
                } elseif ($mini->marca == 'Apostala') {
                    $amount = $data->subtraction;
                    $amountView = $amountView + $data->subtraction;
                } else {
                    $amount = $data->amount;
                    $amountView = $amountView + $data->amount;
                }

            

                $valor = [
                    'hash_table'     => $mini->hash_table,
                    'id_transaction' => $mini->transaction_id,
                    'atmName'        => $mini->name,
                    'service'        => $mini->marca . ' - ' . $mini->descripcion,
                    'status'         => $mini->status,
                    'tipo'           => $mini->tipo,
                    'amount'         => $amount,
                    'created_at'     => $mini->created_at,
                    'id_transaction' => $mini->transaction_id
                ];

            
                array_push($minis, $valor);
            
            }
            
 

            if (isset($input['search'])) {
        
                if($input['search'] == 'download'){
                    $excelData = json_decode(json_encode($minis), true);

                    $filename = 'EntregaDeDinero_' . time();
                    $columnas = array(
                        '#', 'Nombre ATM', 'Servicio', 'Estado', 'Tipo Transaccion', 'Monto', 'Fecha', 'Transaccion'
                    );
                    if (!empty($excelData)) {
                        $excel = new ExcelExport($excelData,$columnas);
                        return \Excel::download($excel, $filename . '.xls')->send();
                        // \Excel::create($filename, function ($excel) use ($excelData) {
                        //     $excel->sheet('Estados', function ($sheet) use ($excelData) {
                        //         $sheet->rows($excelData, false);
                        //         $sheet->prependRow(array(
                        //             '#', 'Nombre ATM', 'Servicio', 'Estado', 'Tipo Transaccion', 'Monto', 'Fecha', 'Transaccion'

                        //         ));
                        //     });
                        // })->export('xls');
                        // exit();
                    }
               }
            }
  
       
            $result = [
                'target'            => 'Transacciones Retiros',
                'tipo'              => $tipo,
                'reservationtime'   => $reservationtime,
                'status_id'         => $status_id,
                'minis'             => $minis,
                'tipo_id'           => $tipo_id,
                'amountView'        => number_format($amountView),
                'transactionsCount' => number_format($transactionsCount),
                'atm'               => $atm,
                'atm_id'            => $atm_id
            ];

            return $result;

        } catch (\Exception $e) {
            $error_detail = [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];

            \Log::error("Ocurrió un error. Detalles:");
            \Log::error($error_detail);
        }
    }

    public function dataModal($id)
    {
        try {

            $error   = false;

            $transaction = \DB::table('transactions')
                ->select('transactions.id', 'marcas.descripcion as marca', 'servicios_x_marca.descripcion as servicio', 'transactions.status', 'amount', 'transactions.created_at', 'payments.valor_recibido', 'payments.valor_entregado', 'mini_cashout_devolution_details.parameters')
                ->join('servicios_x_marca', function ($join) {
                    $join->on('transactions.service_id', '=', 'servicios_x_marca.service_id');
                    $join->on('transactions.service_source_id', '=', 'servicios_x_marca.service_source_id');
                })
                ->join('marcas', 'marcas.id', '=', 'servicios_x_marca.marca_id')
                ->leftjoin('transactions_x_payments', 'transactions.id', '=', 'transactions_x_payments.transactions_id')
                ->leftjoin('payments', 'transactions_x_payments.payments_id', '=', 'payments.id')
                ->leftjoin('mini_cashout_devolution_details', 'mini_cashout_devolution_details.transaction_id', '=', 'transactions.id')
                ->where('transactions.id', $id)
                ->first();
        } catch (\Exception $e) {
            $error_detail = [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];

            \Log::error("Error, Detalles: " . json_encode($error_detail));
            $message = null;
            $error = true;
        }

        return response()->json([
            'error' => $error,
            'data' => $transaction,
        ]);
    }
}
