<?php

/**
 * User: avisconte
 * Date: 28/01/2021
 * Time: 14:00
 * Description: Controlador para rutas de menu's ussd's.
 */

namespace App\Http\Controllers\Ussd;

use Illuminate\Http\Request;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Ussd\FarmController;
use App\WebService;
use App\Transaction;
use App\TransactionRequest;
use App\Services\Ondanet;
use App\Services\NotificationsServices;

use HttpClient;
use Carbon\Carbon;
use Cache;

/**
 * Controlador para ruta: ussd/
 * Modelo MenuUssdDetailClient para crear el registro de los comandos a ejecutar
 * 
 * @author Alejandro Visconte
 * @package app\Services\
 * @subpackage MenuUssdServices
 * @version 1
 */
class MenuController extends Controller
{
    private $service_requests = null;
    //public $farm_controller = null;

    public function __construct()
    {
        /**
         * Parametros por default para el servicio y la factura.
         */
        $this->webservicekey = config('webservices.rest.apitoval.api_prefix');
        $expiresAt = Carbon::now()->addMinutes(60);
        Cache::forget('webservice_config_' . $this->webservicekey);

        $WebServiceConfig = Cache::remember('webservice_config_' . $this->webservicekey, $expiresAt, function () {
            return WebService::with('webservicerequests')->where('api_prefix', $this->webservicekey)->first();
        });

        $this->app_key = $WebServiceConfig->api_key;
        $this->public_key = $WebServiceConfig->user_name;
        $this->serviceId = $WebServiceConfig->id;
        $this->url = $WebServiceConfig->url;
        $this->endpoint = $WebServiceConfig->endpoint;
        $this->apiKey = $WebServiceConfig->api_key;

        foreach ($WebServiceConfig->webservicerequests as $request) {
            $requests[$this->serviceId] = $request;
        }

        $this->service_requests = $requests;

        //------------------------------------------------------------------------------------------------

        /**
         * Parametros para enviar los comandos a Dinstar.
         */
        $this->webservicekey = config('webservices.rest.apiussddinstar.api_prefix');
        $expiresAt = Carbon::now()->addMinutes(60);
        Cache::forget('webservice_config_' . $this->webservicekey);

        $WebServiceConfig = Cache::remember('webservice_config_' . $this->webservicekey, $expiresAt, function () {
            return WebService::with('webservicerequests')->where('api_prefix', $this->webservicekey)->first();
        });

        $parameters = [
            'url' => $WebServiceConfig->url,
            'user' => $WebServiceConfig->user_name,
            'password' => $WebServiceConfig->password,
        ];

        //\Log::info('Parametros para Dinstar:');
        //\Log::info($parameters);

        $this->farm_controller = new FarmController($parameters);
    }

    /**
     * Muestra de forma personalizada la excepción detectada y envía una notificación.
     */
    private function custom_error($parameters)
    {
        $e = $parameters['e'];
        $function = $parameters['function'];

        $error_detail = [];
        $error_detail['message'] = $e->getMessage();
        $error_detail['file'] = $e->getFile();
        $error_detail['class'] = __CLASS__;
        $error_detail['function'] = $function;
        $error_detail['line'] = $e->getLine();
        $error_detail['status'] = $e->getCode();


        $error_detail_aux = json_encode($error_detail);

        \Log::error("\n" . $parameters['function'] . " generó un error: \n" . $error_detail_aux);

        if ($parameters['send_notification']) {
            /**
             * Enviar notificación en caso de excepción
             */
            NotificationsServices::sendNotifications(
                $parameters['atm_id'],
                $parameters['service_id'],
                $parameters['service_source_id'],
                2, // notification_type
                $parameters['url'],
                4, //status
                $parameters['url'] . ' - ' . $error_detail['message'],
                'ussd_exception', // error_code
                $parameters['transaction_id'] // backend_transaction_id
            );
        }

        /**
         * Se retorna el mensaje detectado
         */
        return $error_detail;
    }

    /**
     * Validar las denominaciones para terminales
     */
    public function validate_denominations($atm_id)
    {
        //Obtener atm
        $atms = \DB::table('atms')
            ->select('owner_id')
            ->where('id', $atm_id)
            ->get();

        $list_type = 'completa';
        $contains_payment_type = 'NO';
        $owner_id = null;

        if (count($atms) > 0) {
            $owner_id = $atms[0]->owner_id;

            if ($owner_id == 16) {
                //Verificar el parametro del atm
                $atm_param = \DB::table('atm_param')
                    ->select('key', 'value')
                    ->where('atm_id', $atm_id)
                    ->get();

                if (count($atm_param) > 0) {
                    foreach ($atm_param as $item) {

                        $key = $item->key;
                        $value = $item->value;

                        if ($key == 'pagoDatos') {
                            $contains_payment_type = 'SI';
                        }

                        if ($key == 'pagoDatos' and $value == 'pagoUnico') {
                            $list_type = 'simple';
                        }

                        if ($key == 'pagoPaquetigo') {
                            $contains_payment_type = 'SI';
                        }

                        if ($key == 'pagoPaquetigo' and $value == 'pagoUnico') {
                            $list_type = 'simple';
                        }
                    }

                    //Opción por default (No tiene el parametros de tipo de pago definido)
                    if ($contains_payment_type == 'NO') {
                        $list_type = 'simple';
                    }
                } else {
                    //Opción por default (No tiene ningún parametro).
                    $list_type = 'simple';
                }
            }
        }

        return $list_type;
    }

    /**
     * Valida el servicio y las opciones.
     */
    public function validate_service($service_id, $list_type)
    {

        $list = [
            'error' => true,
            'message' => '',
            'message_user' => '',
            'end_point' => null,
            'menu_ussd_operator_id' => null,
            'operator' => null,
            'service_id' => null,
            'service' => null,
            'menu_ussd_id' => null,
            'list_type' => $list_type,
            'response' => null,
            'error_detail' => null
        ];

        try {
            //Obtener el servico por id
            $menu_ussd_service = \DB::table('ussd.menu_ussd_service')
                ->select(
                    'menu_ussd_operator_id',
                    'service_id',
                    'description as service',
                    'end_point'
                )
                ->where('service_id', $service_id)
                ->where('status', true)
                ->get();

            if (count($menu_ussd_service) > 0) {

                $list['menu_ussd_operator_id'] = $menu_ussd_service[0]->menu_ussd_operator_id;
                $list['service_id'] = $menu_ussd_service[0]->service_id;
                $list['service'] = $menu_ussd_service[0]->service;
                $list['end_point'] = $menu_ussd_service[0]->end_point;

                //Verificar si el operador del servicio está activo.
                $menu_ussd_operator = \DB::table('ussd.menu_ussd_operator')
                    ->select(
                        'description as operator'
                    )
                    ->where('id', $list['menu_ussd_operator_id'])
                    ->where('status', true)
                    ->get();

                if (count($menu_ussd_operator) > 0) {

                    $list['operator'] = $menu_ussd_operator[0]->operator;

                    //Verificar si hay algún telefono activo para ejecutar los comandos.
                    $menu_ussd_phone = \DB::table('ussd.menu_ussd_phone')
                        ->select(
                            'id'
                        )
                        ->where('menu_ussd_operator_id', $list['menu_ussd_operator_id'])
                        ->where('menu_ussd_task_id', 2)
                        ->where('status', true)
                        ->get();

                    if (count($menu_ussd_phone) > 0) {
                        //Obtener el menu_ussd actual del operador
                        $menu_ussd = \DB::table('ussd.menu_ussd')
                            ->select(
                                'id'
                            )
                            ->where('menu_ussd_operator_id', $list['menu_ussd_operator_id'])
                            ->where('status', true)
                            ->get();

                        if (count($menu_ussd) > 0) {
                            $list['menu_ussd_id'] = $menu_ussd[0]->id;

                            //Obtener las sub opciones de la opción principal
                            $records_list = \DB::table('ussd.menu_ussd_detail')
                                ->select(
                                    'id',
                                    \DB::raw('option::integer as option'),
                                    'description',
                                    'amount'
                                )
                                ->where('menu_ussd_id', $list['menu_ussd_id'])
                                ->where('service_id', $list['service_id'])
                                ->whereNotNull('option')
                                ->whereNotNull('amount')
                                ->whereNotNull('command')
                                ->where('menu_ussd_type_id', 2)
                                ->where('status', true);

                            if ($list_type == 'simple') {
                                $denominations = '2000, 5000, 10000, 20000, 50000, 100000';
                                $records_list = $records_list->whereRaw("amount = any(array[$denominations])");
                            }

                            $records_list = $records_list->orderBy('option', 'asc')->get();

                            if (count($records_list) > 0) {

                                $type = '';

                                if ($list['menu_ussd_operator_id'] == 1) {
                                    $type = 'paquetigo';
                                } else if ($list['menu_ussd_operator_id'] == 2) {
                                    $type = 'maxicarga';
                                }

                                $list['error'] = false;
                                $list['message'] = 'Consulta exitosa.';
                                $list['message_user'] = "Seleccionar opción de $type: ";
                                $list['response'] = $records_list;

                                \Log::info("\nConsulta exitosa. Items: \n" . json_encode($list['response']));
                            } else {
                                $list['message'] = 'El menú ussd seleccionado no cuenta con una lista de opciones disponible.';
                            }
                        } else {
                            $list['message'] = 'Menú ussd no disponible.';
                        }
                    } else {
                        $list['message'] = 'Telefono/s no disponibles.';
                    }
                } else {
                    $list['message'] = 'Operador no disponible.';
                }
            } else {
                $list['message'] = 'Servicio no disponible.';
            }
        } catch (\Exception $e) {
            /**
             * Obtener el mensaje de error del servicio y mostrarlo
             */
            $parameters = [
                'e' => $e,
                'function' => __FUNCTION__,
                'send_notification' => false
            ];

            $list['error_detail'] = $this->custom_error($parameters);
            $list['message'] = $list['error_detail']['message'];
            $list['message_user'] = 'Ocurrió un problema con el servicio.';
            $list['end_point'] = null;
            $list['menu_ussd_operator_id'] = null;
            $list['operator'] = null;
            $list['service_id'] = null;
            $list['service'] = null;
            $list['menu_ussd_id'] = null;
            $list['response'] = null;
        }

        return $list;
    }

    /**
     * Generar la factura de toval.
     */
    private function generate_voucher($parameters)
    {

        $list = [
            'success' => false,
            'post_data' => null,
            'response_data' => null
        ];

        try {

            $owner_id = $parameters['owner_id'];
            $service_id = $parameters['service_id'];
            $transaction_id = $parameters['transaction_id'];
            $amount = $parameters['amount'];
            $ruc = $parameters['ruc'];
            $client_name = $parameters['client_name'];
            $msisdn = $parameters['msisdn'];
            $env = $parameters['env'];
            $description = $parameters['description']; //menu_ussd_detail: description

            $url =  $this->url . '/toval/generate_voucher';
            $end_point = '/toval/generate_voucher';

            $app_key = '';
            $public_key = '';

            if ($env == 'local') {
                $atm_id_toval = 4;
                $app_key = 'pzRFvqFSTxGaTvq8NR5uf00CcCFNJ5wHWfMT72Yd';
                $public_key = 'CEaqVRie6WcBN7hJpSbgvNoNbVJFy8PppvguawuK';
            } else if ($env == 'remote') {
                if ($owner_id == 16) {
                    $atm_id_toval = 24;
                    $app_key = 'srteFHCgHiWBzdzMOM59rUYLhS9MozrBRSjWBTAI';
                    $public_key = 'hHU8c6EWijaZkAARKQOkNvYc1kmRXX9liTSjdcLn';
                } else if ($owner_id == 18) {
                    $atm_id_toval = 26;
                    $app_key = 'tghIagO2EZwHnQbncywgfzNIja0JykPUedXLLKjL';
                    $public_key = 'VmDwLlD6wF6NRo8QdbTnvy8wbPPPJxAZzjBb4CTs';
                } else {
                    $atm_id_toval = 23;
                }
            }

            $list['post_data'] = [
                'atm_id' => $atm_id_toval,
                'service_id' => $service_id,
                'backend_transaction_id' => $transaction_id,
                'referencia1' => $msisdn,
                'amount' => $amount,
                'ruc' => $ruc,
                'client_name' => $client_name
            ];

            $date = Carbon::now();
            $params = array($date->format('D, d M Y H:i:s T00'), 'POST', $end_point, json_encode($list['post_data']));
            $params_hash = hash_hmac("sha1", implode("\n", $params), $app_key);

            $petition = HttpClient::post(
                $url,
                [
                    'auth' => [$public_key, $params_hash],
                    'headers' => [
                        'service-id' => $service_id,
                        'service-source-id' => 0,
                        'Timestamp' => $date->format('D, d M Y H:i:s T00')
                    ],
                    'json' => $list['post_data'],
                    'connect_timeout' => 180,
                    'verify' => false
                ]
            );

            $result = json_decode($petition->getBody()->getContents());

            \Log::info("Resultado de generar factura: " . json_encode($result));

            if ((bool) $result->error !== true) {
                //Agregar puntos a el monto:
                $amount_without_points = $result->voucher_data->comprobante_total_a_pagar;
                $amount_without_points = intval($amount_without_points);
                $amount_without_points = number_format($amount_without_points, 0, ',', '.');
                $result->voucher_data->comprobante_total_a_pagar = $amount_without_points;
                $product_name = $result->voucher_data->comprobante_detalle[0]->producto_nombre . ' - ' . $description;

                //Descripción detallada del producto
                $result->voucher_data->comprobante_detalle[0]->producto_nombre = $product_name;

                $list['success'] = true;
            }

            $list['response_data'] = $result;
        } catch (\Exception $e) {
            $parameters = [
                'e' => $e,
                'function' => __FUNCTION__,
                'send_notification' => false
            ];

            $this->custom_error($parameters);
        }

        return $list;
    }

    /**
     * Guarda el ultimo estado de la transacción
     */
    public function save_records($parameters)
    {

        $message = '';

        try {
            $atm_id = $parameters['atm_id'];
            $transaction_id = $parameters['transaction_id'];
            $service_id = $parameters['service_id'];
            $service_request_id = $parameters['service_request_id'];
            $status = $parameters['status'];
            $status_description = $parameters['status_description'];
            $msisdn = $parameters['msisdn'];
            $post_data = json_encode($parameters['post_data']);
            $response_data = json_encode($parameters['response_data']);

            $transaction_request = TransactionRequest::create([
                'transaction_id' => $transaction_id,
                'get_fields_data' => '',
                'post_fields_data' => $post_data,
                'response_fields_data' => $response_data
            ]);

            $transaction = Transaction::find($transaction_id);

            $transaction->fill([
                'status' => $status,
                'status_description' => $status_description,
                'service_request_id' => $service_request_id,
                'request_data' => $post_data,
                'response_data' => $response_data,
                'processed' => 1,
                'referencia_numero_1' => $msisdn
            ]);

            $transaction->save();

            if ($status == 'success') {
                $save_ondanet = new Ondanet();
                $save_ondanet->saveOndanet(
                    '44444401-7',
                    'Cliente Ocasional',
                    $service_id,
                    $atm_id,
                    $transaction_id,
                    8
                );
            }

            $items = [
                'transaction_request' => json_encode($transaction_request),
                'transaction' => json_encode($transaction)
            ];

            \Log::info("\nTransacción ussd actualizada. Items: \n" . json_encode($items));
        } catch (\Exception $e) {
            $parameters = [
                'e' => $e,
                'function' => __FUNCTION__,
                'send_notification' => false
            ];

            $this->custom_error($parameters);
        }

        return $message;
    }

    /**
     * Validar si el número de teléfono se encuentra en lista negra.
     */
    private function validate_menu_ussd_black_list($menu_ussd_operator_id, $msisdn)
    {
        $response = [
            'is_blacklisted' => false,
            'menu_ussd_black_list_reason' => null
        ];

        //Verifica si el número está en la lista negra.
        $menu_ussd_black_list = \DB::table('ussd.menu_ussd_black_list as mubl')
            ->select(
                'mublr.description as menu_ussd_black_list_reason'
            )
            ->join('ussd.menu_ussd_black_list_reason as mublr', 'mublr.id', '=', 'mubl.menu_ussd_black_list_reason_id')
            ->where('mubl.menu_ussd_operator_id', $menu_ussd_operator_id)
            ->where('mubl.phone_number', $msisdn)
            ->where('mubl.status', true)
            ->get();

        if (count($menu_ussd_black_list) > 0) {
            $response['is_blacklisted'] = true;
            $response['menu_ussd_black_list_reason'] = $menu_ussd_black_list[0]->menu_ussd_black_list_reason;
        }

        return $response;
    }

    /**
     * Verificar por regex si el número es correcto.
     * 0981888999 Número ejemplo a validar.
     * ?([0][9][6-9][1-9]) Validar 096, 097, 098, 099 seguido de un número del 1 al 9
     * ?([0-9]{6}) 6 números del 0 al 9
     */
    private function validate_pattern_phone_number($msisdn)
    {
        $is_incorrect = false;

        $pattern = '/^\(?([0][9][6-9][1-9])?([0-9]{6})$/';
        $correct_pattern = preg_match($pattern, $msisdn);

        //preg_match retorna 1 si cumple con las condiciones de regex y 0 si no cumple.
        if ($correct_pattern == 1) {
            $is_incorrect = false;
        } else {
            $is_incorrect = true;
        }

        return $is_incorrect;
    }

    /**
     * Obtiene los detalles de la opción seleccionada
     */
    private function get_menu_ussd_detail($menu_ussd_detail_id, $phone_number)
    {
        $menu_ussd_detail = \DB::table('ussd.menu_ussd_detail as mud')
            ->select(
                'mu.menu_ussd_operator_id',
                'mud.id',
                'mud.option',
                'mud.description',
                'mud.amount',
                'mud.menu_ussd_id',
                \DB::raw("replace(mud.command, '[phone_number]', '$phone_number') || '#' as command"),
                \DB::raw('array_to_json(mud.multiple_pack) as multiple_pack')
            )
            ->join('ussd.menu_ussd as mu', 'mu.id', '=', 'mud.menu_ussd_id')
            ->where('mud.id', $menu_ussd_detail_id)
            ->where('mud.status', true);

        //\Log::info("menu_ussd_detail query:");
        //\Log::info($menu_ussd_detail->toSql());

        $menu_ussd_detail =  $menu_ussd_detail->get();

        return $menu_ussd_detail;
    }

    /**
     * Libera un teléfono ocupado
     */
    private function unlock_phone($menu_ussd_phone_id, $current_transaction_id)
    {

        /**
         * Consultamos si el teléfono a liberar es de la transacción actual o de otra transacción
         */
        $menu_ussd_phone = \DB::table('ussd.menu_ussd_phone')
            ->select(
                'phone_number',
                'port',
                'occupied',
                'current_transaction_id'
            )
            ->where('id', $menu_ussd_phone_id)
            ->get();

        if (count($menu_ussd_phone) > 0) {

            $phone_number = $menu_ussd_phone[0]->phone_number;
            $port = $menu_ussd_phone[0]->port;
            $occupied = $menu_ussd_phone[0]->occupied;
            $current_transaction_id_old = $menu_ussd_phone[0]->current_transaction_id;

            if ($occupied and $current_transaction_id == $current_transaction_id_old) {
                $updated_at = Carbon::now();

                \DB::table('ussd.menu_ussd_phone')
                    ->where('id', $menu_ussd_phone_id)
                    ->update([
                        'current_transaction_id' => null,
                        'occupied' => false,
                        'updated_at' => $updated_at
                    ]);

                $updated_at = $updated_at->format('d-m-Y H:i:s');

                \Log::info("El número de teléfono: $phone_number ubicado en el puerto: $port fué liberado a las: $updated_at");
            }
        } else {
            \Log::error("El teléfono no existe.");
        }
    }

    /**
     * Obtiene 1 o varias opciones dependiendo del paquete.
     */
    private function get_one_or_multiple_ussd_detail($option_id, $msisdn)
    {
        $list = [
            'menu_ussd_operator_id' => null,
            'description' => null,
            'list' => []
        ];

        $menu_ussd_detail = $this->get_menu_ussd_detail($option_id, $msisdn);

        if (count($menu_ussd_detail) > 0) {

            $list['menu_ussd_operator_id'] = $menu_ussd_detail[0]->menu_ussd_operator_id;
            $list['description'] = $menu_ussd_detail[0]->description;

            $multiple_pack = json_decode($menu_ussd_detail[0]->multiple_pack);

            if ($multiple_pack == null) {
                //pack simple
                array_push($list['list'], $menu_ussd_detail);
            } else {
                //pack multiple
                foreach ($multiple_pack as $menu_ussd_detail_id) {

                    $menu_ussd_detail = $this->get_menu_ussd_detail($menu_ussd_detail_id, $msisdn);

                    if (count($menu_ussd_detail) > 0) {
                        array_push($list['list'], $menu_ussd_detail);
                    } else {
                        \Log::error('Una de las opciones del paquete multiple no es válida');
                        $list['list'] = [];
                        break;
                    }
                }
            }
        }

        return $list;
    }


    /**
     * Esta función es para confirmar la transacción USSD.
     *
     * @method set_records
     */
    public function confirm_v2(Request $request)
    {
        $list = [
            'error' => false,
            'message' => '',
            'message_user' => '',

            'atm_id' => $request->atm_id,
            'transaction_id' => $request->backend_transaction_id,
            'service_id' => $request->service_id,
            'service_request_id' => $request->service_id,
            'option_id' => $request->option_id,
            'msisdn' => $request->msisdn,
            'ruc' => $request->ruc,
            'client_name' => $request->client_name,
            'amount' => $request->amount,

            'menu_ussd_operator_id' => null,
            'description' => null,
            'menu_ussd_phone_id' => null,
            'phone_number' => null,
            'port' => null,
            'command_amount' => null,

            'reg' => 'NO_SIM',
            'signal' => 0,
            'current_amount' => 0,

            'status' => null,
            'status_description' => null,
            'post_data' => null,
            'response_data' => null,

            'call_generate_invoice' => false,
            'invoice' => null,

            'url' => $this->url,

            'e' => null,
            'function' => __FUNCTION__,
            'send_notification' => false,
            'error_detail' => null,

            'env' => env('APP_ENV'),

            'amounts_success' => 0
        ];

        try {

            $transactions = \DB::table('transactions')
                ->select(
                    'service_request_id',
                    'service_source_id'
                )
                ->where('id', $list['transaction_id'])
                ->get();

            if (count($transactions) > 0) {
                $list['service_request_id'] = $transactions[0]->service_request_id;
                $list['service_source_id'] = $transactions[0]->service_source_id;
            }

            $atms = \DB::table('atms')
                ->select(
                    'owner_id'
                )
                ->where('id', $list['atm_id'])
                ->get();

            if (count($atms) > 0) {
                $list['owner_id'] = $atms[0]->owner_id;
            }

            if ($this->validate_pattern_phone_number($list['msisdn']) == false) {

                $validate_menu_ussd_black_list = $this->validate_menu_ussd_black_list($list['menu_ussd_operator_id'], $list['msisdn']);

                if ($validate_menu_ussd_black_list['is_blacklisted'] == false) {

                    $menu_ussd_detail_list = $this->get_one_or_multiple_ussd_detail($list['option_id'], $list['msisdn']);

                    if (count($menu_ussd_detail_list) > 0) {

                        $list['menu_ussd_operator_id'] = $menu_ussd_detail_list['menu_ussd_operator_id'];
                        $list['description'] = $menu_ussd_detail_list['description'];

                        $menu_ussd_phone = $this->farm_controller->menu_ussd_phone_balancer($list['menu_ussd_operator_id'], $list['transaction_id']);

                        if (count($menu_ussd_phone) > 0) {

                            $list['menu_ussd_phone_id'] = $menu_ussd_phone[0]->id;
                            $list['phone_number'] = $menu_ussd_phone[0]->phone_number;
                            $list['port'] = $menu_ussd_phone[0]->port;
                            $list['command_amount'] = $menu_ussd_phone[0]->command_amount;
                            $list['minimum_amount'] = $menu_ussd_phone[0]->minimum_amount;

                            $response = $this->farm_controller->reg($list['port']);
                            $status = $response['status'];
                            $list['reg'] = $response['value'];

                            if ($status) {

                                $response = $this->farm_controller->signal($list['port']);
                                $status = $response['status'];
                                $list['signal'] = $response['value'];

                                if ($status) {

                                    $list['current_amount'] = $this->farm_controller->current_amount(
                                        $list['port'],
                                        $list['phone_number'],
                                        $list['command_amount']
                                    );

                                    if ($list['current_amount'] > 0) {
                                        if ($list['current_amount'] < $list['minimum_amount']) {
                                            /**
                                             * Enviar notificación en caso de excepción
                                             */
                                            NotificationsServices::sendNotifications(
                                                $list['atm_id'],
                                                $list['service_id'],
                                                $list['service_source_id'],
                                                2, // notification_type
                                                "El teléfono con número: " . $list['phone_number'] . " tiene el saldo por debajo del mínimo.",
                                                4, //status
                                                'Saldo mínimo.',
                                                'ussd_minimum_amount', // error_code
                                                $list['transaction_id'] // backend_transaction_id
                                            );
                                        }

                                        if ($list['current_amount'] >= $list['amount']) {

                                            $i = 1;

                                            if (count($menu_ussd_detail_list['list']) > 0) {
                                                foreach ($menu_ussd_detail_list['list'] as $item) {

                                                    $item = $item[0];
                                                    $menu_ussd_detail_id = $item->id;
                                                    $menu_ussd_detail_amount = $item->amount;
                                                    $menu_ussd_detail_command = $item->command;
                                                    $menu_ussd_detail_description = $item->description;

                                                    $parameters = [
                                                        'menu_ussd_operator_id' => $list['menu_ussd_operator_id'],
                                                        'port' => $list['port'],
                                                        'command' => $menu_ussd_detail_command
                                                    ];

                                                    $response = $this->farm_controller->send_command($parameters);
                                                    $messages = $response['messages'];
                                                    $final_transaction_message = $response['final_transaction_message'];
                                                    $menu_ussd_status_id = $response['menu_ussd_status_id'];

                                                    \log::info("DATOS DE TRANSACCIÓN USSD EJECUTADA:" . json_encode($response));

                                                    //Solamente si el primero registro falla parar
                                                    if ($i == 1 and $menu_ussd_status_id == 3) {
                                                        \log::error("
                                                            \nEl número " . $list['msisdn'] . " tiene paquete fallido.
                                                            \nEl paquete es: $menu_ussd_detail_description.
                                                            \nLa operadora respondió: $final_transaction_message.
                                                        ");

                                                        $list['message'] = 'Falló la ejecución del paquete ussd.';
                                                        $list['message_user'] = 'Ocurrió un problema con la operadora, no se pudo envíar el saldo.';
                                                        break;
                                                    } else {
                                                        /**
                                                         * Si el primero esta bien seguir insertando sin importar el estado.
                                                         */
                                                        $list['call_generate_invoice'] = true;

                                                        $data = [
                                                            'phone_number' => $list['msisdn'],
                                                            'amount' => $menu_ussd_detail_amount,
                                                            'command' => $menu_ussd_detail_command,
                                                            'messages' => $messages,
                                                            'final_transaction_message' => $final_transaction_message,
                                                            'wrong_run_counter' => 0,
                                                            'menu_ussd_operator_id' => $list['menu_ussd_operator_id'],
                                                            'menu_ussd_detail_id' => $menu_ussd_detail_id,
                                                            'menu_ussd_status_id' => $menu_ussd_status_id,
                                                            'transaction_id' => $list['transaction_id'],
                                                            'created_at' => Carbon::now(),
                                                            'updated_at' => null,
                                                            'menu_ussd_phone_id' => $list['menu_ussd_phone_id']
                                                        ];

                                                        //Insert conectado al esquema ussd
                                                        $menu_ussd_detail_client_id = \DB::table('ussd.menu_ussd_detail_client')
                                                            ->insertGetId($data);

                                                        if ($menu_ussd_detail_client_id !== null) {
                                                            \log::info("Inserción correcta en tabla menu_ussd_detail_client id = $menu_ussd_detail_client_id.");
                                                        } else {
                                                            \log::error('No se pudo insertar en la tabla menu_ussd_detail_client.');
                                                            $list['message'] = 'Falló la inserción del paquete ussd.';
                                                        }

                                                        $list['amounts_success'] = $list['amounts_success'] + $menu_ussd_detail_amount;
                                                    }

                                                    $i++;
                                                }
                                            } else {
                                                $list['message'] = "La opción: " . $list['description'] . " tiene una o varias opciones no válidas";
                                                $list['message_user'] = 'Ocurrió un problema con la operadora, paquete no disponible.';
                                            }
                                        } else {
                                            $list['message'] = "El saldo del teléfono " . $list['phone_number'] . " no tiene saldo suficiente para la transacción."; ;
                                            $list['message_user'] = 'Ocurrió un problema con la operadora.';
                                        }
                                    } else {
                                        $list['message'] = "El teléfono con número: " . $list['phone_number'] . " no tiene saldo.";
                                        $list['message_user'] = 'Ocurrió un problema con la operadora.';
                                    }
                                } else {
                                    $list['message'] = 'La señal del teléfono obtenido no es óptima para transaccionar';
                                    $list['message_user'] = 'Ocurrió un problema con la operadora.';
                                }
                            } else {
                                $list['message'] = 'El puerto obtenido no contiene una tarjeta sim para transaccionar';
                                $list['message_user'] = 'Ocurrió un problema con la operadora.';
                            }



                            if ($list['current_amount'] == $list['amounts_success']) {
                                $list['current_amount'] = 0;
                            } else {
                                if ($list['current_amount'] > $list['amounts_success']) {
                                    $list['current_amount'] = $list['current_amount'] - $list['amounts_success'];
                                } else if ($list['current_amount'] < $list['amounts_success']) {
                                    $list['current_amount'] = $list['amounts_success'] - $list['current_amount'];
                                }
                            }

                            \DB::table('ussd.menu_ussd_phone')
                                ->where('id', $list['menu_ussd_phone_id'])
                                ->update([
                                    'reg' => $list['reg'],
                                    'signal' => $list['signal'],
                                    'current_amount' => $list['current_amount'],
                                    'updated_at' => Carbon::now()
                                ]);

                        } else {
                            $list['message'] = 'No hay teléfono disponible para transaccionar.';
                            $list['message_user'] = 'Ocurrió un problema con la operadora.';
                        }
                    } else {
                        $list['message'] = 'Una o varias opciones ussd no es válida.';
                        $list['message_user'] = 'La opción seleccionada no es válida.';
                    }
                } else {
                    $list['message'] = 'El número de teléfono se encuentra en lista negra.';
                    $list['message_user'] = 'El número de teléfono se encuentra en lista negra.';
                }
            } else {
                $list['message'] = 'El número de teléfono no es correcto.';
                $list['message_user'] = 'El número de teléfono no es correcto.';
            }

            /**
             * Luego de terminar de correr los comandos liberar el teléfono
             */
            $this->unlock_phone($list['menu_ussd_phone_id'], $list['transaction_id']);
        } catch (\Exception $e) {
            /**
             * Liberar teléfono en caso de excepción
             */
            $this->unlock_phone($list['menu_ussd_phone_id'], $list['transaction_id']);

            $parameters = [
                'e' => $e,
                'function' => __FUNCTION__,
                'send_notification' => true
            ];

            $list['error_detail'] = $this->custom_error($list);
            $list['message'] = $list['error_detail']['message'];
            $list['message_user'] = 'Ocurrió un problema con el servicio.';
        }

        /**
         * Generar factura en el caso que uno de los comandos sea success
         */

        if ($list['call_generate_invoice']) {
            $response = $this->generate_voucher($list);

            if ($response['success']) {
                $list['invoice'] = $response['response_data'];
            }

            $list['post_data'] = $response['post_data'];
            $list['response_data'] = $response['response_data'];

            $generate_voucher = [
                'post_data' => $list['post_data'],
                'response_data' => $list['response_data']
            ];

            \log::info("\nPOST Y RESPONSE de generate_voucher: \n" . json_encode($generate_voucher));
        }

        /**
         * Definir como quedó la transacción
         */
        if ($list['message'] == '') {

            $list['status'] = 'success';
            $list['status_description'] = 'Transacción exitosa.';
            $list['message'] = $list['status_description'];
            $list['message_user'] = $list['status_description'];

            \log::info('Transacción ussd exitosa!');
        } else {

            $list['error'] = true;

            if ($list['error_detail'] !== null) {
                $list['status'] = 'error';
                $list['status_description'] = 'Transacción con error.';
                \log::error("\nTransacción ussd con error:\n" . $list['message']);
            } else {
                $list['status'] = 'canceled';
                $list['status_description'] = 'Transacción cancelada.';
                \log::warning("\nTransacción ussd cancelada:\n" . $list['message']);
            }
        }

        /**
         * Guardar transacción
         */
        $this->save_records($list);

        /**
         * Retornar items al front-end
         */
        $response = [
            'error' => $list['error'],
            'message' => $list['message'],
            'message_user' => $list['message_user'],
            'env' => $list['env'],
            'invoice' => $list['invoice'],
            'error_detail' => $list['error_detail']
        ];

        return $response;
    }
}
