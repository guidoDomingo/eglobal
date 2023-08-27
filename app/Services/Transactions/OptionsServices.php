<?php

/**
 * User: avisconte
 * Date: 28/06/2022
 * Time: 15:51
 */

namespace App\Services\Transactions;

use Carbon\Carbon;
use HttpClient;

class OptionsServices
{

    /**
     * Inicializar valores para usar en todas las funciones
     */
    public function __construct()
    {
        $this->url = 'http://eglobaltws.local/';
        $this->atm_id = 2042; // Cajerito del CMS para devoluciones
        $this->owner_id = 24; // CMS

        $this->env = env('APP_ENV');

        if ($this->env == 'local') {
            $this->url = 'http://eglobaltws.local/';
            $this->app_key = 'pzRFvqFSTxGaTvq8NR5uf00CcCFNJ5wHWfMT72Yd';
            $this->public_key = 'CEaqVRie6WcBN7hJpSbgvNoNbVJFy8PppvguawuK';
        } else if ($this->env == 'remote') {
            $this->url = 'https://api.eglobalt.com.py/'; // Uno de los cambios luego de subir a producción
            $this->app_key = 'srteFHCgHiWBzdzMOM59rUYLhS9MozrBRSjWBTAI';
            $this->public_key = 'hHU8c6EWijaZkAARKQOkNvYc1kmRXX9liTSjdcLn';
        }

        $this->user = \Sentinel::getUser();
    }

    /**
     * Obtener los servicios para las devoluciones
     */
    public function get_services_for_returns($request)
    {

        $class = __CLASS__;
        $function = __FUNCTION__;
        $inputs = json_encode($request->all());

        \Log::info("\n\nCampos obtenidos en $class \ $function:\n\n$inputs\n\n");

        $response = [
            'error' => false,
            'message' => '',
            'response' => []
        ];

        try {

            $option = $request['option'];
            $category_id = $request['category_id'];

            $service_source_id = null;
            $service_id = null;

            /*switch ($option) {

                case 'NETEL':
                    $category_id = '4,19';
                    $service_source_id = '10';
                    break;

                case 'INFONET':
                    $category_id = '4,19';
                    $service_source_id = '7';
                    break;

                case 'APOSTALA':
                    $category_id = '18';
                    $service_source_id = '0';
                    $service_id = '50';
                    break;

                case 'VISION':
                    $category_id = '9';
                    $service_source_id = '0';
                    $service_id = '14';
                    break;

                case 'ANTELL':
                    $category_id = '4';
                    $service_source_id = '0';
                    $service_id = '33,5,12';
                    break;

                case 'BILLETAJE':
                    $category_id = '35';
                    $service_source_id = '7';
                    $service_id = '485,768';
                    break;

                case 'SERVICIOS PÚBLICOS':
                    $category_id = '3';
                    $service_source_id = '7';
                    $service_id = '1,2,93,4';
                    break;
            }*/

            /**
                3	Servicios Publicos
                4	Telefonia Movil
                9	Bancos Y Financieras
                17	Otros Servicios
                15  Casas comerciales
                18	Ocio y Entretenimiento
                19	Remesas
                35	Servicios de transporte
             */

            //1298	VOX - CARGA FACIL	10		1		193
            //1298	VOX - MICHIMI	    10		1		194
            //1298	VOX - FACTURA	    10		1		357

            switch ($category_id) {

                case 3:
                    $service_source_id = '7';
                    $service_id = '1,2,93,4';
                    break;

                case 4:
                    $service_source_id = '0,7,10';
                    $service_id = '33,5,12,3,193,194,357,4,387,390,8,7,34,394,251';
                    break;

                case 9:
                    $service_source_id = '0,10';
                    $service_id = '14,226,220';
                    break;

                case 15:
                    $service_source_id = '0,10';
                    $service_id = '84,715';
                    break;

                case 18:
                    $service_source_id = '0';
                    $service_id = '50';
                    break;

                case 19:
                    $category_id = '4,19';
                    $service_source_id = '0,7,10';
                    $service_id = '436,242,435,241,511,591,592,145,33,624';
                    break;

                case 35:
                    $service_source_id = '7';
                    $service_id = '485,768';
                    break;
            }

            $services = \DB::table('servicios_x_marca as sxm')
                ->select(
                    //ID único
                    'sxm.service_by_brand_id as id',

                    //Proveedor
                    'sxm.service_source_id',
                    'sps.description as provider',

                    //Categoría
                    'ac.id as service_category_id',
                    'ac.name as service_category',

                    //Servicio
                    'sxm.service_id',
                    \DB::raw("upper(m.descripcion || ' - ' || sxm.descripcion) as description"),

                    //Nivel
                    'sxm.nivel as level',

                    'm.imagen_asociada as associated_image'
                )
                ->join('marcas as m', 'm.id', '=', 'sxm.marca_id')
                ->join('app_categories as ac', 'ac.id', '=', 'm.categoria_id')
                ->join('services_providers_sources as sps', 'sps.id', '=', 'sxm.service_source_id');

            $services = $services->whereRaw("ac.id in ($category_id)");

            if ($service_source_id !== null) {
                $services = $services->whereRaw("sxm.service_source_id in ($service_source_id)");
            }

            if ($service_id !== null) {
                $services = $services->whereRaw("sxm.service_id in ($service_id)");
            }

            \Log::info("\n\nQUERY DE SERVICIOS:\n\n" . $services->toSql());

            $services = $services
                ->orderBy('sxm.service_source_id', 'ASC')
                ->orderBy('sxm.descripcion', 'ASC')
                ->get();

            $response['response'] = $services;
        } catch (\Exception $e) {
            $error_detail = [
                'from' => 'CMS',
                'message' => 'Ocurrió un error al querer obtener la lista de servicios.',
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => $class,
                'function' => $function,
                'line' => $e->getLine()
            ];

            $response['error'] = true;
            $response['message'] = $error_detail['message'];
            $response['error_detail'] = $error_detail;

            $error_detail = json_encode($error_detail);

            \Log::error("\n\nError en $class \ $function:\nDetalles:\n\n$error_detail\n\n");
        }

        /**
         * ---------------------------------------------------------------------------------------------------
         * Para mostrar todos los detalles en el log.
         */
        $response_aux = json_encode($response);

        \Log::info("\n\nRespuesta de $class \ $function:\n\n$response_aux\n\n");

        return $response;
    }

    /**
     * Obtener los servicios para las devoluciones
     */
    public function get_categories($request)
    {

        $class = __CLASS__;
        $function = __FUNCTION__;
        $inputs = json_encode([]);

        \Log::info("\n\nCampos obtenidos en $class \ $function:\n\n$inputs\n\n");

        $response = [
            'error' => false,
            'message' => '',
            'response' => []
        ];

        try {

            $categories = \DB::table('app_menu as am')
                ->select(
                    'am.categoria_id as category_id',
                    'ac.name as description',
                    'am.image'
                )
                ->join('app_categories as ac', 'ac.id', '=', 'am.categoria_id')
                ->whereRaw('am.categoria_id in (3, 4, 9, 15, 18, 19, 35)')
                ->orderBy('am.categoria_id', 'ASC')
                ->get();

            $response['response'] = $categories;
        } catch (\Exception $e) {
            $error_detail = [
                'from' => 'CMS',
                'message' => 'Ocurrió un error al querer obtener la lista de servicios.',
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => $class,
                'function' => $function,
                'line' => $e->getLine()
            ];

            $response['error'] = true;
            $response['message'] = $error_detail['message'];
            $response['error_detail'] = $error_detail;

            $error_detail = json_encode($error_detail);

            \Log::error("\n\nError en $class \ $function:\nDetalles:\n\n$error_detail\n\n");
        }

        /**
         * ---------------------------------------------------------------------------------------------------
         * Para mostrar todos los detalles en el log.
         */
        $response_aux = json_encode($response);

        \Log::info("\n\nRespuesta de $class \ $function:\n\n$response_aux\n\n");

        return $response;
    }

    /**
     * Obtener los servicios para las devoluciones
     */
    public function get_history_transaction($request)
    {

        $class = __CLASS__;
        $function = __FUNCTION__;
        $inputs = json_encode($request->all());

        \Log::info("\n\nCampos obtenidos en $class \ $function:\n\n$inputs\n\n");

        $response = [
            'error' => false,
            'message' => '',
            'response' => []
        ];

        try {

            $transaction_id = $request['transaction_id'];

            $transaction_devolution = \DB::table('transaction_devolution as td')
                ->select(
                    \DB::raw("td.transaction_id as \"ID - Transacción\""),
                    \DB::raw("td.transaction_devolution_id as \"ID - Transacción - Devolución\""),
                    \DB::raw("to_char(td.created_at, 'DD/MM/YYYY HH24:MI:SS') as \"Fecha y Hora\""),

                    \DB::raw("td.devolution_amount as \"Monto de devolución\""),
                    \DB::raw("dm.description as \"Método\""),
                    \DB::raw("dr.description as \"Razón\""),
                    \DB::raw("dt.description as \"Tipo\""),
                    \DB::raw("ds.description as \"Estado\""),

                    \DB::raw("(case when ajustement = true then 'Con Ajuste' else 'Sin Ajuste' end) as \"Ajuste\""),

                    \DB::raw("coalesce(td.ajustement_amount, 0) as \"Monto de Ajuste\""),
                    \DB::raw("coalesce(td.ajustement_percentage, 0) as \"Porcentaje de Ajuste\""),

                    \DB::raw("u.description as \"Usuario\""),
                    \DB::raw("comment as \"Comentario\"")
                )
                ->join('devolution_method as dm', 'dm.id', '=', 'td.devolution_method_id')
                ->join('devolution_reason as dr', 'dr.id', '=', 'td.devolution_reason_id')
                ->join('devolution_type as dt', 'dt.id', '=', 'td.devolution_type_id')
                ->join('devolution_status as ds', 'ds.id', '=', 'td.devolution_status_id')

                ->join('users as u', 'u.id', '=', 'td.user_id')
                ->where('td.transaction_id', $transaction_id)
                ->get();

            if (count($transaction_devolution) > 0) {
                $response['response'] = $transaction_devolution;
            } else {
                $response['message'] = "No hay historial para la transacción número: $transaction_id";
                $response['response'] = [];
            }
        } catch (\Exception $e) {
            $error_detail = [
                'from' => 'CMS',
                'message' => 'Ocurrió un error al querer obtener el historial de devoluciones',
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => $class,
                'function' => $function,
                'line' => $e->getLine()
            ];

            $response['message'] = $error_detail['message'];
            $response['error_detail'] = $error_detail;

            $error_detail = json_encode($error_detail);

            \Log::error("\n\nError en $class \ $function:\nDetalles:\n\n$error_detail\n\n");
        }

        if ($response['message'] !== '') {
            $response['error'] = true;
        }

        /**
         * ---------------------------------------------------------------------------------------------------
         * Para mostrar todos los detalles en el log.
         */
        $response_aux = json_encode($response);

        \Log::info("\n\nRespuesta de $class \ $function:\n\n$response_aux\n\n");

        return $response;
    }

    /**
     * Rutas que apuntan al web service de Eglobalt
     */

    /**
     * Obtiene la información del servicio.
     */
    public function cms_get_service_info($request)
    {

        $class = __CLASS__;
        $function = __FUNCTION__;
        $inputs = json_encode($request->all());

        \Log::info("\n\nCampos obtenidos en $class \ $function:\n\n$inputs\n\n");

        $response = [
            'error' => false,
            'message' => '',
            'error_detail' => null,
            'response' => []
        ];

        // ----------------------------------------------------------------------

        $url = $this->url . 'cms_get_service_info';
        $atm_id = $this->atm_id;
        $app_key = $this->app_key;
        $public_key = $this->public_key;

        // ----------------------------------------------------------------------

        try {
            $transaction_id = $request['transaction_id'];
            $service_source_id = $request['service_source_id'];
            $service_id = $request['service_id'];

            $service_source_id_new = $request['service_source_id_new'];
            $service_category_id_new = $request['service_category_id_new'];
            $service_id_new = $request['service_id_new'];

            $json = [
                'transaction_id' => $transaction_id,
                'service_source_id' => $service_source_id,
                'service_id' => $service_id,
                'service_source_id_new' => $service_source_id_new,
                'service_category_id_new' => $service_category_id_new,
                'service_id_new' => $service_id_new,
                'atm_id' => $atm_id,
                'level' => null
            ];

            $date = Carbon::now();
            $params = array($date->format('D, d M Y H:i:s T00'), 'POST', $url, json_encode($json));
            $params_hash = hash_hmac("sha1", implode("\n", $params), $app_key);

            $petition = HttpClient::post(
                $url,
                [
                    'auth' => [$public_key, $params_hash],
                    'headers' => [
                        'Timestamp' => $date->format('D, d M Y H:i:s T00')
                    ],
                    'json' => $json,
                    'connect_timeout' => 180,
                    'verify' => false
                ]
            );

            /**
             * Tanto para Netel e Infonet se estandariza la forma de retorno
             * para evitar uwus
             */
            $response['response'] = json_decode($petition->getBody()->getContents(), true);

            \Log::info('response:');
            \Log::info($response['response']);
            $response['error'] = $response['response']['error'];
            $response['message'] = $response['response']['message'];
        } catch (\Exception $e) {
            $error_detail = [
                'from' => 'CMS',
                'message' => 'Ocurrió un error al querer obtener datos del servicio.',
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => $class,
                'function' => $function,
                'line' => $e->getLine()
            ];

            $response['message'] = 'Ocurrió un error al querer obtener datos del servicio.';
            $response['error'] = true;
            $response['error_detail'] = $error_detail;

            $error_detail = json_encode($error_detail);

            \Log::error("\n\nError en $class \ $function:\nDetalles:\n\n$error_detail\n\n");
        }

        /**
         * ---------------------------------------------------------------------------------------------------
         * Para mostrar todos los detalles en el log.
         */
        $response_aux = json_encode($response);

        \Log::info("\n\nRespuesta de $class \ $function:\n\n$response_aux\n\n");

        return $response;
    }


    /**
     * Obtiene la información del servicio.
     */
    public function cms_get_more_service_info($request)
    {

        $class = __CLASS__;
        $function = __FUNCTION__;
        $inputs = json_encode($request->all());

        \Log::info("\n\nCampos obtenidos en $class \ $function:\n\n$inputs\n\n");

        $response = [
            'error' => false,
            'message' => '',
            'response' => []
        ];

        // ----------------------------------------------------------------------

        $url = $this->url . 'cms_get_more_service_info';
        $atm_id = $this->atm_id;
        $app_key = $this->app_key;
        $public_key = $this->public_key;

        // ----------------------------------------------------------------------

        try {

            $transaction_id = $request['transaction_id'];
            $service_source_id = $request['service_source_id'];
            $service_id = $request['service_id'];
            $service_source_id_new = $request['service_source_id_new'];
            $service_id_new = $request['service_id_new'];

            $level = $request['level'];
            $fields = $request['fields'];
            $values = $request['values'];
            $commission = $request['commission'];

            $json = [
                'transaction_id' => $transaction_id,
                'service_source_id' => $service_source_id,
                'service_id' => $service_id,
                'service_source_id_new' => $service_source_id_new,
                'service_id_new' => $service_id_new,
                'atm_id' => $atm_id,
                'level' => $level,
                'fields' => $fields,
                'values' => $values,
                'commission' => $commission
            ];

            \Log::info('JSON ENVIADO:', $json);

            $date = Carbon::now();
            $params = array($date->format('D, d M Y H:i:s T00'), 'POST', $url, json_encode($json));
            $params_hash = hash_hmac("sha1", implode("\n", $params), $app_key);

            $user = 'cms_atm_user';
            $password = '@cms_atm_password_99';
            $basic = "$user:$password";
            $base_64 = base64_encode($basic);

            $body = [
                'auth' => [$public_key, $params_hash],
                'headers' => [
                    'Authorization' => "Basic $base_64",
                    'Timestamp' => $date->format('D, d M Y H:i:s T00')
                ],
                'json' => $json,
                'connect_timeout' => 180,
                'verify' => false
            ];

            $petition = HttpClient::post($url, $body);

            $response['response'] = json_decode($petition->getBody()->getContents());
        } catch (\Exception $e) {
            $error_detail = [
                'from' => 'CMS',
                'message' => 'Ocurrió un error al querer obtener más detalles del servicio.',
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => $class,
                'function' => $function,
                'line' => $e->getLine()
            ];

            $response['message'] = 'Ocurrió un error al querer obtener más detalles del servicio.';
            $response['error'] = true;
            $response['error_detail'] = $error_detail;

            $error_detail = json_encode($error_detail);

            \Log::error("\n\nError en $class \ $function:\nDetalles:\n\n$error_detail\n\n");
        }

        /**
         * ---------------------------------------------------------------------------------------------------
         * Para mostrar todos los detalles en el log.
         */
        $response_aux = json_encode($response);

        \Log::info("\n\nRespuesta de $class \ $function:\n\n$response_aux\n\n");

        return $response;
    }


    /**
     * Enviar los datos a trex para la confirmación
     */
    public function cms_confirm($request)
    {

        $class = __CLASS__;
        $function = __FUNCTION__;
        $inputs = json_encode($request->all());

        \Log::info("\n\nCampos obtenidos en $class \ $function:\n\n$inputs\n\n");

        $response = [
            'error' => false,
            'message' => '',
            'transaction_devolution_id' => null,
            'details' => null
        ];

        // ----------------------------------------------------------------------

        /**
         * Valores parametrizados no dinámicos.
         */
        $url = $this->url . 'cms_confirm';
        $atm_id = $this->atm_id;
        $owner_id = $this->owner_id;
        $app_key = $this->app_key;
        $public_key = $this->public_key;
        $user_id = $this->user->id;

        // ----------------------------------------------------------------------

        try {

            /**
             * Provienen del front end
             */
            $transaction_main_id = $request['transaction_id'];

            $owner_main_id = $request['owner_id'];

            $service_source_id = $request['service_source_id'];
            $service_id = $request['service_id'];

            $service_source_id_new = $request['service_source_id_new'];
            $service_id_new = $request['service_id_new'];
            $amount = $request['amount'];

            $level = $request['level'];
            $sequence = $request['sequence'];

            $commission = $request['commission'];
            $ajustement = $request['ajustement'];
            $make_ajustement = $request['make_ajustement'];

            $fields = $request['fields'];
            $values = $request['values'];

            $fields_devolution = $request['fields_devolution'];
            $values_devolution = $request['values_devolution'];

            $fields_ajustement = $request['fields_ajustement'];
            $values_ajustement = $request['values_ajustement'];

            \Log::info("\n\nVALORES:\n\n", [$values]);

            /**
             * Creados aquí para la transacción de devolución
             */
            $transaction_type = 11; // Devolución
            $status = 'iniciated';
            $status_description = 'Iniciando transacción desde el CMS';
            $created_at = date('Y-m-d H:i:s');

            $timestamp_big_int = \DB::select("select cast(to_char((current_timestamp)::timestamp,'ddmmyyyyyhhmiss') as bigint) as timestamp_big_int");
            $timestamp_big_int = $timestamp_big_int[0]->timestamp_big_int;
            $atm_transaction_id = "$atm_id$timestamp_big_int";

            $request_data = json_encode([
                'level' => $level,
                'sequence' => $sequence,
                'fields' => $fields,
                'values' => $values
            ]);

            $json = [
                'transaction_main_id' => $transaction_main_id,
                'owner_main_id' => $owner_main_id,

                'owner_id' => $owner_id,
                'atm_id' => $atm_id,

                'service_source_id' => $service_source_id,
                'service_id' => $service_id,

                'service_source_id_new' => $service_source_id_new,
                'service_id_new' => $service_id_new,

                'amount' => $amount,
                'transaction_type' => $transaction_type,
                'status' => $status,
                'status_description' => $status_description,
                'created_at' => $created_at,
                'request_data' => $request_data,
                'atm_transaction_id' => $atm_transaction_id,
                'user_id' => $user_id,

                'level' => $level,
                'sequence' => $sequence,

                'commission' => $commission,
                'ajustement' => $ajustement,
                'make_ajustement' => $make_ajustement,

                'fields' => $fields,
                'values' => $values,

                'fields_devolution' => $fields_devolution,
                'values_devolution' => $values_devolution,

                'fields_ajustement' => $fields_ajustement,
                'values_ajustement' => $values_ajustement
            ];

            $date = Carbon::now()->format('D, d M Y H:i:s T00');
            $params = array($date, 'POST', $url, json_encode($json));
            $params_hash = hash_hmac("sha1", implode("\n", $params), $app_key);

            $body = [
                'auth' => [$public_key, $params_hash],
                'headers' => [
                    'Timestamp' => $date
                ],
                'json' => $json,
                'connect_timeout' => 180,
                'verify' => false
            ];

            $petition = HttpClient::post($url, $body);
            $web_service_response = $petition->getBody()->getContents();
            $web_service_response = json_decode($web_service_response, true); // Viene StdClass

            $response['error'] = $web_service_response['error'];
            $response['message'] = $web_service_response['message'];
            $response['transaction_devolution_id'] = $web_service_response['transaction_devolution_id'];
            $response['details'] = $web_service_response['details'];
        } catch (\Exception $e) {
            $error_detail = [
                'from' => 'CMS',
                'message' => 'Ocurrió un error al querer confirmar la transacción.',
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => $class,
                'function' => $function,
                'line' => $e->getLine()
            ];

            $response['error'] = true;
            $response['message'] = $error_detail['message'];
            $response['error_detail'] = $error_detail;

            $error_detail = json_encode($error_detail);

            \Log::error("\n\nError en $class \ $function:\nDetalles:\n\n$error_detail\n\n");
        }

        /**
         * ---------------------------------------------------------------------------------------------------
         * Para mostrar todos los detalles en el log.
         */
        $response_aux = json_encode($response);

        \Log::info("\n\nRespuesta de $class \ $function:\n\n$response_aux\n\n");

        return $response;
    }



    /**
     * Enviar los datos a trex para la confirmación
     */
    public function cms_get_ticket($request)
    {

        $class = __CLASS__;
        $function = __FUNCTION__;
        $inputs = json_encode($request->all());

        \Log::info("\n\nCampos obtenidos en $class \ $function:\n\n$inputs\n\n");

        $response = [
            'error' => true,
            'message' => 'No se pudo obtener le ticket',
            'response' => []
        ];

        // ----------------------------------------------------------------------

        /**
         * Valores parametrizados no dinámicos.
         */
        $url = $this->url . 'cms_get_ticket';
        $app_key = $this->app_key;
        $public_key = $this->public_key;

        // ----------------------------------------------------------------------

        try {

            /**
             * Provienen del front end
             */
            $transaction_devolution_id = $request['transaction_devolution_id'];

            $json = [
                'atm_id' => 'print',
                'transaction_id' => $transaction_devolution_id,
                'service_source_id' => 'print',
                'service_id' => 'print',
                'service_source_id_new' => 'print',
                'service_id_new' => 'print'
            ];

            $date = Carbon::now()->format('D, d M Y H:i:s T00');
            $params = array($date, 'POST', $url, json_encode($json));
            $params_hash = hash_hmac("sha1", implode("\n", $params), $app_key);

            $body = [
                'auth' => [$public_key, $params_hash],
                'headers' => [
                    'Timestamp' => $date
                ],
                'json' => $json,
                'connect_timeout' => 180,
                'verify' => false
            ];

            $petition = HttpClient::post($url, $body);
            $web_service_response = $petition->getBody();

            /*$filename = 'get_ticket.pdf';

            return response()->make($web_service_response, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="' . $filename . '"'
            ]);*/

           return $web_service_response;

        } catch (\Exception $e) {
            $error_detail = [
                'from' => 'CMS',
                'message' => 'Ocurrió un error al ge
                nerar el ticket.',
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => $class,
                'function' => $function,
                'line' => $e->getLine()
            ];

            $response['error'] = true;
            $response['message'] = $error_detail['message'];
            $response['error_detail'] = $error_detail;

            $error_detail = json_encode($error_detail);

            \Log::error("\n\nError en $class \ $function:\nDetalles:\n\n$error_detail\n\n");
        }

        /**
         * ---------------------------------------------------------------------------------------------------
         * Para mostrar todos los detalles en el log.
         */
        $response_aux = json_encode($response);

        \Log::info("\n\nRespuesta de $class \ $function:\n\n$response_aux\n\n");

        return $response;
    }
}
