<?php

/**
 * User: avisconte
 * Date: 05/11/2021
 * Time: 15:20
 */

namespace App\Http\Controllers\Ussd;

use App\Http\Controllers\Controller;

class FarmController extends Controller
{

    /**
     * Los parametros provienen desde MenuUssdServices
     */
    public function __construct($parameters)
    {
        $this->url = $parameters['url'];
        $this->user = $parameters['user'];
        $this->password = $parameters['password'];
    }


    /**
     * Obtiener la información del puerto y el chip.
     */
    public function get_port_info($port, $info_type)
    {
        /*
            Opciones:
            imei,imsi,iccid,smsc,type,number,reg,slot,callstate,signal,gprs,rem
            ain_credit,remain_monthly_credit,remain_daily_credit:,remain_d
            aily_calltime,remain_hourly_calltime,remain_daily_connect
        */

        $parameters = [
            'action' => __FUNCTION__,
            'request' => 'GET',
            'url' => $this->url . "get_port_info?port=$port&info_type=$info_type",
            'json' => null
        ];

        return $this->curl($parameters);
    }

    /**
     * Envíar el comando ussd.
     */
    public function send_ussd($port, $command, $text)
    {

        $correct = false;

        $parameters = [
            'action' => __FUNCTION__,
            'request' => 'POST',
            'url' => $this->url . "send_ussd/",
            'json' => '{"port":[' . $port . '],"command":"' . $command . '","text":"' . $text . '"}'
        ];

        $response = $this->curl($parameters);

        if (isset($response['result:'][0])) {
            $result = $response['result:'][0];

            $is_valid = (isset($result['port']) and isset($result['status'])) ? true : false;

            if ($is_valid) {
                if ($result['port'] == $port and $result['status'] == 200) {
                    $correct = true;
                }
            }
        }

        if ($correct) {
            \Log::info("El comando [$text][$command] fué ejecutado correctamente.");
        } else {
            \Log::warning("El comando [$text][$command] no se ejecutó.");
        }

        return $correct;
    }

    /**
     * Obtiene la respuesta tras enviar un comando ussd.
     */
    public function query_ussd_reply($port)
    {
        sleep(4); //Si no se espera unos segundos este comando retorna vacío.

        $text = '';

        $parameters = [
            'action' => __FUNCTION__,
            'request' => 'GET',
            'url' => $this->url . "query_ussd_reply?port=$port",
            'json' => null
        ];

        $response = $this->curl($parameters);

        if (isset($response['reply'][0])) {
            $reply = $response['reply'][0];

            $is_valid = (isset($reply['port']) and isset($reply['text'])) ? true : false;

            if ($is_valid) {
                $text = $reply['text'];
            }
        }

        return $text;
    }

    /**
     * Mastica la respuesta para obtener la señal.
     */
    public function signal($port)
    {
        $list = [
            'status' => false,
            'value' => 0
        ];

        $response = $this->get_port_info($port, 'signal');

        if (isset($response['info'][0]['signal'])) {
            $list['value'] = $response['info'][0]['signal'];

            if ($list['value'] >= 10) {
                $list['status'] = true;
            }

            \Log::info("La señal del dispositivo con puerto $port es " . $list['value']);
        }

        return $list;
    }

    /**
     * Mastica la respuesta de obtener el registro
     */
    public function reg($port)
    {
        $list = [
            'status' => false,
            'value' => 'NO_SIM'
        ];

        $response = $this->get_port_info($port, 'reg');

        if (isset($response['info'][0]['reg'])) {
            $list['value'] = $response['info'][0]['reg'];

            if ($list['value'] == 'REGISTER_OK') {
                $list['status'] = true;
            } else if ($list['value'] == 'NO_SIM') {
            }
        }

        \Log::info("La tarjeta sim del puerto: $port tiene el reg con valor: " . $list['value']);

        return $list;
    }

    /**
     * Obtiene el saldo actual del teléfono
     */
    public function current_amount($port, $phone_number, $command)
    {
        $number = 0;

        /** Enviar comando ussd  */
        if ($this->send_ussd($port, 'send', $command)) {

            /** Obtener mensaje de operadora con el monto del saldo  */
            $text = $this->query_ussd_reply($port);

            /** Quitar salto de linea */
            $text = str_replace("\n", '', $text);

            $number = preg_replace('/[^0-9]/', '', $text);

            $number = (is_numeric($number)) ? intval($number) : 0;

            /** Mostrar el saldo disponible  */
            \Log::info("Mensaje de Operadora: $text, saldo actual de $phone_number: $number");
        }

        return $number;
    }

    /**
     * Ejecutar con curl.
     */
    public function curl($parameters)
    {

        $response = [
            'action' => null,
            'response' => null
        ];

        try {

            //\Log::info('Ejecutando... parametros:');
            //\Log::info($parameters);

            /**
             * default
             */
            $user = $this->user;
            $password = $this->password;
            $user_password = "$user:$password";

            //\Log::info("user_password: $user_password");

            /**
             * vars
             */
            $action = $parameters['action'];
            $request = $parameters['request'];
            $url = $parameters['url'];
            $json = $parameters['json'];

            $curl = curl_init();

            $curl_items = [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_CUSTOMREQUEST => $request,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_HEADER => 0,
                CURLOPT_HTTPHEADER => array('Content-Type: application/json'),
                CURLOPT_HTTPAUTH => CURLAUTH_ANY,
                CURLOPT_USERPWD => $user_password
            ];

            if ($request == 'POST') {
                $curl_items[CURLOPT_POSTFIELDS] = $json;
            }

            curl_setopt_array($curl, $curl_items);

            $response = curl_exec($curl);
            $response = json_decode($response, true);

            curl_close($curl);

            \Log::info(
                "\nAcción: $action.
                \nRespuesta: " . json_encode($response)
            );
        } catch (\Exception $e) {
            $error_detail = [
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine()
            ];

            \Log::error("Error el ejecutar CURL en acción: $action, Detalles: " . json_encode($error_detail));
        }

        return $response;
    }

    /**
     * Ejecuta el select que determina que puerto está libre
     */
    public function menu_ussd_phone_balancer($menu_ussd_operator_id, $transaction_id)
    {
        $query = "
            select * from ussd.function_menu_ussd_phone_balancer($menu_ussd_operator_id, $transaction_id);
        ";

        $menu_ussd_phone = \DB::select($query);

        \Log::info("\nQuery de balanceador ejecutado correctamente:\n $query");

        return $menu_ussd_phone;
    }

    /**
     * Valida todo sobre el puerto
     */
    public function validate_port($port)
    {
        $correct = false;

        /** ¿El puerto contiene una tarjeta sim REGISTER_OK? */
        if ($this->reg($port)) {
            /** ¿La tarjeta sim tiene una señal decente >= 18? */
            if ($this->signal($port)) {
                $correct = true;
            }
        }

        return $correct;
    }

    /**
     * Enviar comando USSD a TIGO
     */
    public function send_command_to_tigo($parameters)
    {
        $list = [
            'success' => false,
            'messages' => null,
            'final_transaction_message' => null,
            'menu_ussd_status_id' => 3 //error por default hasta que se demuestre lo contrario
        ];

        $port = $parameters['port']; // Ejemplo: 1
        $command = $parameters['command']; // Ejemplo: '*888*1122*1*0985651534*1#';


        /** Enviar cadena para limpiar cualquier comando anterior. */
        if ($this->send_ussd($port, 'cancel', '')) {

            /** ¿Esa ejecución retorna una cadena vacía?  */
            if ($this->query_ussd_reply($port) == '') {

                /** Enviar comando ussd  */
                if ($this->send_ussd($port, 'send', $command)) {

                    /** Obtener respuesta  */
                    $list['messages'] = $this->query_ussd_reply($port);

                    /** Quitar salto de linea y concatenar con |  */
                    $list['messages'] = str_replace("\n", ' | ', $list['messages']);

                    /** Imprime: Transfiere Gs 2000 a 0981123123? (|:separador) 1.SI, 2.NO  */
                    \Log::info("messages: " . $list['messages']);

                    /** ¿La confirmación retorna vacío o ussd terminado?  */
                    if ($list['messages'] !== '' and $list['messages'] !== 'USSD terminated by network') {

                        \Log::info("Confirmando transacción ussd de TIGO...");

                        /** Enviar confirmación  */
                        if ($this->send_ussd($port, 'send', '1')) {

                            /** Obtener respuesta  */
                            $list['final_transaction_message'] = $this->query_ussd_reply($port);

                            /** Quitar salto de linea y concatenar con |  */
                            $list['final_transaction_message'] = str_replace("\n", ' | ', $list['final_transaction_message']);

                            /** Imprime: Transaccion exitosa | Gracias por utilizar el sistema de Mini Carga.  */
                            \Log::info("final_transaction_message: " . $list['final_transaction_message']);

                            /** ¿La respuesta final es correcta?  */
                            if ($list['final_transaction_message'] !== '') {
                                if ($list['final_transaction_message'] == 'Transaccion exitosa | Gracias por utilizar el sistema de Mini Carga') {
                                    $list['menu_ussd_status_id'] = 2; //El mensaje esperado
                                } else {
                                    $list['menu_ussd_status_id'] = 3; //Cualquier otra cosa distinta del mensaje exitoso.
                                }
                            } else {
                                $list['menu_ussd_status_id'] = 4; //La operadora respondió vacío.
                            }
                        }
                    }
                }
            }
        }

        if ($list['menu_ussd_status_id'] == 2) {
            $list['success'] = true;
        }

        return $list;
    }

    /**
     * Enviar comando USSD a PERSONAL
     */
    public function send_command_to_personal($parameters)
    {
        $list = [
            'success' => false,
            'messages' => null,
            'final_transaction_message' => null,
            'menu_ussd_status_id' => 3 //error por default hasta que se demuestre lo contrario.
        ];

        $port = $parameters['port']; // 1
        $command = $parameters['command']; // '*888*1122*1*0985651534*1#';

        /** Enviar cadena para limpiar cualquier comando anterior. */
        if ($this->send_ussd($port, 'cancel', '')) {

            /** ¿Esa ejecución retorna una cadena vacía?  */
            if ($this->query_ussd_reply($port) == '') {

                \Log::info("Confirmando transacción ussd de PERSONAL...");

                if ($this->send_ussd($port, 'send', $command)) {

                    /** Obtener respuesta  */
                    $list['messages'] = $this->query_ussd_reply($port);
                    $list['messages'] = str_replace("\n", ' | ', $list['messages']);
                    $list['final_transaction_message'] = $list['messages'];

                    /** Imprime: Venta Exitosa  */
                    \Log::info("messages: " . $list['messages']);

                    /** ¿La respuesta final retorna vacío o ussd terminado?  */

                    if ($list['final_transaction_message'] !== '') {
                        if ($list['final_transaction_message'] == 'Venta Exitosa') {
                            $list['menu_ussd_status_id'] = 2; //El mensaje esperado
                        } else {
                            $list['menu_ussd_status_id'] = 3; //Cualquier otra cosa distinta del mensaje exitoso.
                        }
                    } else {
                        $list['menu_ussd_status_id'] = 4; //La operadora respondió vacío.
                    }
                }
            }
        }

        if ($list['menu_ussd_status_id'] == 2) {
            $list['success'] = true;
        }
    }

    /**
     * @method send_command: Servicio para enviar comandos ussd dependiendo de la operadora.
     * @access public
     * @category Controller
     * @param $parameters
     * @return $response 
     */
    public function send_command($parameters)
    {
        $response = [];

        $menu_ussd_operator_id = $parameters['menu_ussd_operator_id'];

        if ($menu_ussd_operator_id == 1) {

            $response = $this->send_command_to_tigo($parameters);
        } else if ($menu_ussd_operator_id == 2) {

            $response = $this->send_command_to_personal($parameters);
        }

        return $response;
    }
}
