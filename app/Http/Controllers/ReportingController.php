<?php

namespace App\Http\Controllers;

use App\Exports\ExcelExport;
use App\Models\Branch;
use App\Models\Owner;
use App\Models\Pos;
use App\Models\ServiceProviderProduct;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Services\ReportServices;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Controllers\ReversionesController;
use Excel;
use Milon\Barcode\DNS1D;
use NumberFormatter;
use Session;
use HttpClient;
use Illuminate\Support\Facades\Storage;

class ReportingController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth', ['except' => 'dapdv_transactions']);
        $this->user = \Sentinel::getUser();
    }

    /** TRANSACTIONS*/
    public function transactionsReports()
    {

        if (!$this->user->hasAnyAccess('reporting', 'ticketea', 'reporting_mini_terminal')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        try {

            $report = new ReportServices('');
            $result = $report->transactionsReports();

            if ($result == false) {
                $data = [
                    'mode' => 'message',
                    'type' => 'error',
                    'title' => 'Ocurrió un error',
                    'explanation' => 'No se pudieron obtener los datos del informe.'
                ];

                return view('messages.index', compact('data'));
            } else {
                return view(
                    'reporting.index')->with($result);
            }
        } catch (\Exception $e) {
            $error_detail = [
                'from' => 'CMS',
                'message' => '[transactionsReports] Ocurrió un error al iniciar historico de transacciones.',
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine(),
                'user' => [
                    'user_id' => $this->user->id,
                    'username' => $this->user->username,
                    'description' => $this->user->description
                ]
            ];

            \Log::error($error_detail['message'], [$error_detail]);

            $data = [
                'mode' => 'message',
                'type' => 'error',
                'title' => 'Ocurrió un error',
                'explanation' => $error_detail['exception']
            ];

            return view('messages.index', compact('data'));
        }
    }

    public function transactionSearch()
    {

        if (!$this->user->hasAnyAccess('reporting', 'ticketea', 'reporting_mini_terminal')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        try {

            $input = \Request::all();

            $transaction_id = null;

            if (isset($input['transaction_id'])) {
                if ($input['transaction_id'] !== null and $input['transaction_id'] !== '') {
                    $transaction_id = $input['transaction_id'];
                }
            }

            if (isset($input['search']) || isset($input['context']) || isset($input['page'])) {
                $report = new ReportServices($input);
                $result = $report->transactionsSearch();

                if ($result == false) {
                    $data = [
                        'mode' => 'message',
                        'type' => 'error',
                        'title' => 'Ocurrió un error',
                        'explanation' => 'No se pudieron obtener los datos de la búsqueda.'
                    ];

                    return view('messages.index', compact('data'));
                } else {

                    if (isset($input['reservationtime'])) {
                        $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                        $to = date('Y-m-d H:i:s', strtotime($daterange[0]));
                        $from = date('Y-m-d H:i:s', strtotime($daterange[1]));
                        $days = \DB::select("select ('{$from}'::date - '{$to}'::date) + 1 as days");
                        $days = $days[0]->days;
                    } else {
                        $days = 0;
                    }

                    if ($days > 1 and $transaction_id == null) {
                        //\Log::info('Búsqueda con rango amplio.');

                        $query = json_encode([$result['query_to_export']]);

                        \DB::table('select_to_manage')->insert([
                            'description' => $query,
                            'user_id' => $this->user->id,
                            'created_at' => Carbon::now(),
                            'status' => false
                        ]);

                        $mail = $this->user->email;

                        Session::flash(
                            'message',
                            "El rango de fecha que seleccionaste es muy amplio, 
                            por lo tanto el link del reporte será enviado a su correo: $mail 
                            dentro de 5 minutos."
                        );
                        return redirect()->back();
                    } else {
                        if ($result) {

                            //\Log::info('result:');
                            //\Log::info($result);
                            //die();

                            return view(
                                'reporting.index'
                                
                            )->with($result);
                        } else {
                            Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                            return redirect()->back();
                        }
                    }
                }
            } else if (isset($input['download'])) {

                //\Log::info('EXPORTAR...');

                ini_set('max_execution_time', 300);
                $report = new ReportServices($input);
                $result = $report->transactionsSearchExport();

                if ($result == false) {
                    $data = [
                        'mode' => 'message',
                        'type' => 'error',
                        'title' => 'Ocurrió un error',
                        'explanation' => 'No se pudieron obtener los datos de la búsqueda.'
                    ];

                    return view('messages.index', compact('data'));
                } else {

                    if (is_array($result)) {
                        $result = json_decode(json_encode($result), true);
                    }

                    $list_ids = [23]; //Lista de ids excepcionales. Id de usuario Wilson Bazan

                    if (!\Sentinel::getUser()->inRole('mini_terminal') or in_array($this->user->id, $list_ids, true)) {
                        $result2 = $report->transactionsSearchExportMovements();

                        if (is_array($result2)) {
                            $result2 = json_decode(json_encode($result2), true);
                        }
                    } else {
                        $result2 = null;
                    }

                    $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                    $to = date('Y-m-d H:i:s', strtotime($daterange[0]));
                    $from = date('Y-m-d H:i:s', strtotime($daterange[1]));
                    $days = \DB::select("select ('{$from}'::date - '{$to}'::date) + 1 as days");
                    $days = $days[0]->days;

                    if ($days > 1 and $transaction_id == null) {
                        $query = json_encode([$result, $result2]);

                        \DB::table('select_to_manage')->insert([
                            'description' => $query,
                            'user_id' => $this->user->id,
                            'created_at' => Carbon::now(),
                            'status' => false
                        ]);

                        $mail = $this->user->email;

                        Session::flash(
                            'message',
                            "El rango de fecha que seleccionaste es muy amplio, 
                            por lo tanto el link del reporte será enviado a su correo: $mail 
                            dentro de 5 minutos."
                        );
                        return redirect()->back();
                    } else {

                        //\Log::info('GENERANDO EXCEL...');
                        //\Log::info('Convirtiendo lista de result para el excel... result:', [$result]);

                        $filename = 'transacciones_' . time();
                        $columna1 = array(
                            'id', 'Proveedor', 'Tipo', 'Estado', 'Descripcion', 'Fecha', 'Hora', 'Valor Transaccion', 'Cod. Pago', 'Forma pago',
                            'Identificador transaccion', 'Factura Nro', 'Sede', 'Red', 'Ref 1', 'Ref 2', 'Codigo Cajero'
                        );
                        
                        $columna2 = array(
                            'id', 'transactions_id', 'atms_parts_id', 'accion', 'cantidad', 'valor', 'dinero_virtual', 'payments_id', 'fecha', 'hora'
                        );

                        if ($result) {

                            //Excel::create($filename, function ($excel) use ($result, $result2) {

                                $result_aux = [];

                                foreach ($result as $result_item) {

                                    if ($result_item['service_source_id'] == 0) {
                                        $result_item['proveedor'] = $result_item['provider'];
                                        $result_item['tipo'] = $result_item['servicio'];
                                    }

                                    $row = [
                                        $result_item['id'],
                                        $result_item['proveedor'],
                                        $result_item['tipo'],
                                        $result_item['estado'],
                                        $result_item['estado_descripcion'],
                                        $result_item['fecha'],
                                        $result_item['hora'],
                                        $result_item['valor_transaccion'],
                                        $result_item['cod_pago'],
                                        $result_item['forma_pago'],
                                        $result_item['identificador_transaccion'],
                                        $result_item['factura_nro'],
                                        $result_item['sede'],
                                        $result_item['owner_id'],
                                        $result_item['ref1'],
                                        $result_item['ref2'],
                                        $result_item['codigo_cajero']
                                    ];

                                    array_push($result_aux, $row);
                                }


                                $excel = new ExcelExport($result_aux,$columna1,$result2->toArray(),$columna2);
                                return Excel::download($excel, $filename . '.xls')->send();

                                // $excel->sheet('Transacciones', function ($sheet) use ($result_aux) {
                                //     $sheet->rows($result_aux, false);
                                //     $sheet->prependRow(array(
                                //         'id', 'Proveedor', 'Tipo', 'Estado', 'Descripcion', 'Fecha', 'Hora', 'Valor Transaccion', 'Cod. Pago', 'Forma pago',
                                //         'Identificador transaccion', 'Factura Nro', 'Sede', 'Red', 'Ref 1', 'Ref 2', 'Codigo Cajero'
                                //     ));
                                // });

                                // if (!\Sentinel::getUser()->inRole('mini_terminal')) {
                                //     $excel->sheet('Movimientos', function ($sheet) use ($result2) {
                                //         $sheet->rows($result2, false);
                                //         $sheet->prependRow(array(
                                //             'id', 'transactions_id', 'atms_parts_id', 'accion', 'cantidad', 'valor', 'dinero_virtual', 'payments_id', 'fecha', 'hora'
                                //         ));
                                //     });
                                // }
                            //})->export('xls');
                           // exit();
                        } else {
                            Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                            return redirect()->back();
                        };
                    }
                }
            }
        } catch (\Exception $e) {
            $error_detail = [
                'from' => 'CMS',
                'message' => '[transactionSearch] Ocurrió un error al realizar la búsqueda.',
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine(),
                'user' => [
                    'user_id' => $this->user->id,
                    'username' => $this->user->username,
                    'description' => $this->user->description
                ]
            ];

            \Log::error($error_detail['message'], [$error_detail]);

            $aux_exception = '';

            if (str_contains($error_detail['exception'], 'SQLSTATE')) {
                $aux_exception = 'Ocurrio un error al querer consultar los datos.';
            } else {
                $aux_exception = $error_detail['exception'];
            }

            $data = [
                'mode' => 'message',
                'type' => 'error',
                'title' => 'Ocurrió un error',
                'explanation' => $aux_exception,
                'error_detail' => $error_detail
            ];

            return view('messages.index', compact('data'));
        }
    }

    /**
     * Para obtener los puntos de ventas
     */
    public function get_points_of_sale(Request $request)
    {

        if (!$this->user->hasAnyAccess('reporting', 'ticketea', 'reporting_mini_terminal')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        //\Log::info("Campos:", [$request->all()]);

        $pos_active = $request['pos_active'];
        $owner_id = $request['owner_id'];
        $branch_id = $request['branch_id'];

        $wherePos = "points_of_sale.atm_id is not null ";

        if ($pos_active == 'on') {
            $wherePos .= "and points_of_sale.deleted_at is null ";
        }

        if ($branch_id !== null and $branch_id !== '0') {
            $wherePos .= "and points_of_sale.branch_id = $branch_id ";
        }

        if ($owner_id == '0') {
            $owner_id = null;
        }

        if (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
            if ($owner_id <> null && $owner_id <> 2 && $owner_id <> 11) {
                $wherePos = "and points_of_sale.owner_id = $owner_id";
            }
        }

        \Log::info("wherePos: $wherePos");

        $pdvs = Pos::orderBy('description')
            ->where(
                function ($query) use ($wherePos) {
                    if (!empty($wherePos)) {
                        $query->whereRaw($wherePos);
                    }
                }
            )
            ->with('Atm')
            ->get();

        $pos = [];
        $item = array();
        $item[0] = 'Todos';
        foreach ($pdvs  as $pdv) {
            $item[$pdv->id] = $pdv->description . ' - ' . $pdv->Atm->code;
            $pos = $item;
        }

        return $pos;
    }



    /**
     * Traer los JSON's que tiene una transacción
     */
    public function jsons_transaction(Request $request)
    {

        if (!(\Sentinel::getUser()->inRole('superuser'))) {
            return [];
        }

        //\Log::info("Campos:", [$request->all()]);

        $transaction_id = $request['transaction_id'];

        $jsons = \DB::table('transactions as t')
            ->select(
                \DB::raw("coalesce(t.request_data, 'Sin JSON.') as request_data"),
                \DB::raw("coalesce(t.response_data, 'Sin JSON.') as response_data")
            )
            ->where('t.id', $transaction_id)
            ->get();

        $jsons  = $jsons[0];

        //\Log::info('jsons->request_data:', [$jsons->request_data]);

        /**
         * Ocultar datos.
         */
        if ($jsons->request_data !== 'Sin JSON.') {
            $jsons->request_data = json_decode($jsons->request_data);

            if (isset($jsons->request_data->username)) {
                $jsons->request_data->username = "";
            }

            if (isset($jsons->request_data->password)) {
                $jsons->request_data->password = "";
            }

            $jsons->request_data = json_encode($jsons->request_data);
        }

        $jsons_aux = [
            'request_data' => $jsons->request_data,
            'response_data' => $jsons->response_data
        ];

        return $jsons_aux;
    }

    /**
     * Traer los JSON's que tiene una transacción en transaction_requests
     */
    public function jsons_transaction_requests(Request $request)
    {

        if (!(\Sentinel::getUser()->inRole('superuser'))) {
            return [];
        }

        //\Log::info("Campos:", [$request->all()]);

        $transaction_id = $request['transaction_id'];

        $jsons = \DB::table('transaction_requests as tr')
            ->select(
                'tr.id as transaction_requests_id',
                \DB::raw("coalesce(tr.get_fields_data, 'Sin JSON.') as get_fields_data"),
                \DB::raw("coalesce(tr.post_fields_data, 'Sin JSON.') as post_fields_data"),
                \DB::raw("coalesce(tr.response_fields_data, 'Sin JSON.') as response_fields_data"),
                \DB::raw("to_char(tr.created_at, 'DD/MM/YYYY HH24:MI:SS') as created_at"),
                \DB::raw("to_char(tr.updated_at, 'DD/MM/YYYY HH24:MI:SS') as updated_at")
            )
            ->where('tr.transaction_id', $transaction_id)
            ->get();

        $jsons = json_decode(json_encode($jsons), true);

        if (count($jsons) == 0) {
            $jsons = [$jsons];
        }

        if (count($jsons[0]) == 0) {
            $jsons = [];
        }

        for ($i = 0; $i < count($jsons); $i++) {

            if ($jsons[$i]['post_fields_data'] !== 'Sin JSON.') {
                $jsons[$i]['post_fields_data'] = json_decode($jsons[$i]['post_fields_data']);

                //\Log::info('jsons[$i][post_fields_data]:', [$jsons[$i]['post_fields_data']]);

                if (isset($jsons[$i]['post_fields_data']->username)) {
                    $jsons[$i]['post_fields_data']->username = "";
                }

                if (isset($jsons[$i]['post_fields_data']->password)) {
                    $jsons[$i]['post_fields_data']->password = "";
                }

                $jsons[$i]['post_fields_data'] = json_encode($jsons[$i]['post_fields_data']);
            }
        }

        //\Log::info('transaction_requests (JSON):', $jsons);

        return $jsons;
    }

    /**
     * Traer los JSON's que tiene una transacción en transaction_requests
     */
    public function jsons_service(Request $request)
    {

        if (!(\Sentinel::getUser()->inRole('superuser') or \Sentinel::getUser()->inRole('ATC') or \Sentinel::getUser()->inRole('accounting.admin'))) {
            return [];
        }

        //\Log::info("Campos:", [$request->all()]);

        $transaction_id = $request['transaction_id'];
        $service_source_id = $request['service_source_id'];
        $service_id = $request['service_id'];

        if ($service_source_id == '0' and $service_id == '49') {

            $jsons = \DB::table('tdp_billetaje_sync_transactions as tbst')
                ->select(
                    'tbst.id',
                    'tbst.response_status_code',
                    'tbst.transaction_type',
                    \DB::raw("coalesce(tbst.json, 'Sin JSON.') as post_fields_data"),
                    \DB::raw("coalesce(tbst.response_data, 'Sin JSON.') as response_fields_data"),
                    \DB::raw("to_char(tbst.created_at, 'DD/MM/YYYY HH24:MI:SS') as created_at")
                )
                ->where('tbst.transaction_id', $transaction_id)
                ->get();

            $jsons = json_decode(json_encode($jsons), true);

            if (count($jsons) == 0) {
                $jsons = [$jsons];
            }

            if (count($jsons[0]) == 0) {
                $jsons = [];
            }
        } else {
            $jsons = [];
        }

        //\Log::info('jsons_service (JSON):', $jsons);

        return $jsons;
    }

    /**
     * Traer los JSON's que tiene una transacción en transaction_requests
     */
    public function transaction_ticket(Request $request)
    {

        if (!(\Sentinel::getUser()->inRole('superuser') or \Sentinel::getUser()->inRole('ATC') or \Sentinel::getUser()->inRole('accounting.admin'))) {
            return '';
        }

        try {

            $class = __CLASS__;
            $function = __FUNCTION__;
            $html = null;

            $env = env('APP_ENV');

            if ($env == 'local') {

                $url = 'http://eglobaltws.local/cms_generate_ticket';
                $app_key = 'pzRFvqFSTxGaTvq8NR5uf00CcCFNJ5wHWfMT72Yd';
                $public_key = 'CEaqVRie6WcBN7hJpSbgvNoNbVJFy8PppvguawuK';
            } else if ($env == 'remote') {

                $url = 'https://api.eglobalt.com.py/cms_generate_ticket';
                $app_key = 'srteFHCgHiWBzdzMOM59rUYLhS9MozrBRSjWBTAI';
                $public_key = 'hHU8c6EWijaZkAARKQOkNvYc1kmRXX9liTSjdcLn';
            }

            $transaction_id = $request['transaction_id'];

            $transaction = \DB::table('transactions')
                ->select(
                    'service_source_id',
                    'service_id',
                    'atm_id'
                )
                ->where('id', $transaction_id)
                ->get();

            $transaction = $transaction[0];

            $service_source_id = $transaction->service_source_id;
            $service_id = $transaction->service_id;
            $atm_id = $transaction->atm_id;

            $json = [
                'transaction_id' => "$transaction_id",
                'atm_id' => "$atm_id",
                'service_source_id' => "$service_source_id",
                'service_id' => "$service_id",
                'service_source_id_new' => "$service_source_id",
                'service_id_new' => "$service_id"
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
            $html = $petition->getBody();

            \Log::info('HTML:', [$html]);
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

        return $html;
    }

    /// Estado de instalaciones APP BILLETAJE
    public function statusInstallations()
    {
        if (!$this->user->hasAnyAccess('reporting', 'ticketea')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $report = new ReportServices('');
        $result = $report->statusInstallations();
        return view('reporting.index')->with($result);
    }
    public function statusInstallationsSearch()
    {
        if (!$this->user->hasAnyAccess('reporting', 'ticketea')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = \Request::all();
        if (isset($input['search']) || isset($input['context']) || isset($input['page'])) {
            $report = new ReportServices($input);
            $result = $report->statusInstallationsSearch();
            if ($result) {
                return view('reporting.index')->with($result);
            } else {
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();
            }
        }

        if (isset($input['download'])) {
            ini_set('max_execution_time', 300);
            $report = new ReportServices($input);
            $result = $report->statusInstallationsSearchExport();
            $result = json_decode(json_encode($result), true);
            $filename = 'instalaciones_' . time();
            $columna1 = array(
                'id', 'Fecha Instalación', 'Sede', 'Serial del validador', 'Numero PDV', 'App Versions', 'Latitud', 'Longitud'
            );

            $excel = new ExcelExport($result['instalaciones'],$columna1);
            return Excel::download($excel, $filename . '.xls')->send();

            // Excel::create($filename, function ($excel) use ($result) {
            //     $excel->sheet('sheet1', function ($sheet) use ($result) {
            //         $sheet->rows($result['instalaciones'], false);
            //         $sheet->prependRow(array(
            //             'id', 'Fecha Instalación', 'Sede', 'Serial del validador', 'Numero PDV', 'App Versions', 'Latitud', 'Longitud'
            //         ));
            //     });
            // })->export('xls');
            // exit();
        }
    }

    public function getPaymentsDetails($id)
    {

        if (!$this->user->hasAccess('reporting')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $details = array();

        // get payment details

        /*$payments_details = \DB::table('payments')
            ->where('payments.id', '=', $id)
            ->get();*/

        $payments_details = \DB::table('payments')
            ->select(
                'id',
                'valor_a_pagar',
                'valor_recibido',
                'valor_entregado',
                \DB::raw("coalesce(to_char(created_at, 'DD/MM/YYYY HH24:MI:SS'), '') as created_at")
            )
            ->where('payments.id', '=', $id)
            ->get();

        //get movements details
        /*$movements_details = \DB::table('transactions_movements')
            ->where('payments_id', '=', $id)
            ->get();*/
        
        $movements_details = \DB::table('transactions_movements')
            ->select(
                'transactions_id',
                'accion',
                'valor',
                'cantidad'
            )
            ->where('payments_id', '=', $id)
            ->get();

        $salida_billete = false;
        $total_entrada  = 0;
        $total_salida   = 0;

        $transaction_id = false;

        foreach ($movements_details as $movement) {

            $transaction_id = $movement->transactions_id;
            if ($movement->accion == 'entrada') {
                //procesar entradas
                $entrada = $movement->valor * $movement->cantidad;
                $total_entrada = $total_entrada + $entrada;
            } elseif ($movement->accion  == 'salida') {
                //procesar salidas
                $salida = $movement->valor * $movement->cantidad;
                $total_salida = $total_salida + $salida;
                $salida_billete = true;
            }

        }

        //si momement diff es igual a amout: el cajero retuvo el dinero
        $movement_diff = $total_entrada - $total_salida;
        $devolucion = false;
        
        //obtener datos de la transaccion para validar que sea reprocesable
        if ($transaction_id) {

            $transaction_data = \DB::table('transactions')
                ->select(
                    'status',
                    'amount',
                    'service_source_id',
                    'service_id',
                    'owner_id'
                )
                ->where('id', $transaction_id)
                ->first();

            $rep_netel_services = array(7, 33, 194);
            $rep_infonet_services = array(10, 6, 12);
            $rep_eglobalt_services = array(3, 5, 7, 9, 33);
            $eg_reprocesable = false;
            $nt_reprocesable = false;
            $infonet_reprocesable = false;
            $monto =  number_format($transaction_data->amount, 0, '.', '');

            //verificar si es reprocesable
            if ($monto == $movement_diff && in_array($transaction_data->service_id, $rep_eglobalt_services) && $transaction_data->service_source_id == 0 && $transaction_data->status <> 'success') {
                $eg_reprocesable = true;
            }

            if ($monto == $movement_diff && in_array($transaction_data->service_id, $rep_netel_services) && $transaction_data->service_source_id == 1 && $transaction_data->status <> 'success') {
                $nt_reprocesable = true;
            }

            if ($monto == $movement_diff && in_array($transaction_data->service_id, $rep_infonet_services) && $transaction_data->service_source_id == 7 && $transaction_data->status <> 'success') {
                $infonet_reprocesable = true;
            }
            // verificar si se puede hacer devolucion

            if ($monto == $movement_diff  && $transaction_data->status <> 'success') {
                $devolucion = true;
            }

            $details['inconsistencia'] = false;

            if ($salida_billete == false && ($transaction_data->status == 'error' || $transaction_data->status == 'iniciated') && ($transaction_data->owner_id == 11 || $transaction_data->owner_id == 14) &&  $transaction_data->service_id == 8) {
                $details['inconsistencia'] = true;
            }

            if (($transaction_data->service_id == 7 || $transaction_data->service_id == 9 || $transaction_data->service_id == 33) && $transaction_data->status == 'error' && ($transaction_data->owner_id == 11 || $transaction_data->owner_id == 14)) {
                $details['inconsistencia'] = true;
            }


            if ($nt_reprocesable == false && $eg_reprocesable == false && $infonet_reprocesable == false) {
                $details['reprocesable'] = false;
            } else {
                $details['reprocesable'] = true;
            }

            \Log::info('Informacion de transaccion Reprocesable: Transaction_id: ' . $transaction_id . ', amount:' . $monto . ', Transaction_status: ' . $transaction_data->status . ', Diff: ' . $movement_diff . ', Servicio: -' . $transaction_data->service_source_id . ' - ' . $transaction_data->service_id);
        } else {
            $details['reprocesable'] = false;

            $transaction = \DB::table('transactions_x_payments')
                ->select(
                    'transactions.id', 
                    'transactions.service_id', 
                    'transactions.status', 
                    'transactions.service_source_id', 
                    'transactions.response_data', 
                    'transactions.owner_id'
                )
                ->join('transactions', 'transactions_x_payments.transactions_id', '=', 'transactions.id')
                ->where('payments_id', $payments_details[0]->id)
                ->first();


            $if_debited = strpos($transaction->response_data, 'Extraccion realizada con exito');
            
            if ($if_debited === false) {
                $if_debited = 0;
            }

            \Log::info('isdebited: ' . $if_debited . ' Si es <> 0 la transaccion de Efectivización es habilitada para devolucion');

            if ($transaction->status <> 'success' &&  $transaction->service_id == 8 && $transaction->service_source_id == 0 && $if_debited <> 0) {
                $devolucion = true;
            }

            if ($salida_billete == false && ($transaction->status == 'error' || $transaction->status == 'iniciated') && ($transaction->owner_id == 11 || $transaction->owner_id == 14) && $transaction->service_id == 8) {
                $details['inconsistencia'] = true;
            }

            if (($transaction->service_id == 7 || $transaction->service_id == 9 || $transaction->service_id == 33) && $transaction->status == 'error' && ($transaction->owner_id == 11 || $transaction->owner_id == 14)) {
                $details['inconsistencia'] = true;
            }
        }

        $details['devolucion'] = $devolucion;

        if (!$this->user->hasAccess('reporting.devolucion')) {
            $details['devolucion'] = false;
            $details['reprocesable'] = false;
            $details['inconsistencia'] = false;
        }

        foreach ($payments_details as $payments_detail) {
            $details['payment_info'] = '<tr><td style="display:none;"></td>
              <td style="display:none;"></td>
              <td>' . number_format($payments_detail->valor_a_pagar) . '</td>
              <td>' . number_format($payments_detail->valor_recibido) . '</td>
              <td>' . number_format($payments_detail->valor_entregado) . '</td>
              <td>' . $payments_detail->created_at . '</td></tr>';
        }

        return $details;
    }

    public function getReversionDetails($transaction_id)
    {
        if (!$this->user->hasAccess('reporting')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        //obtener datos de la transaccion para validar que sea reprocesable
        $transaction_data = \DB::table('transactions')
            ->select('transactions.*', 'mtr.reversion_id', 'mtr.created_by', 'mtr.fecha_reversion')
            ->leftjoin('mt_recibos_reversiones as mtr', 'transactions.id', '=', 'mtr.transaction_id')
            ->where('id', $transaction_id)
            ->first();

        $monto =  number_format($transaction_data->amount, 0, '.', '');

        $details['reversion'] = false; //Transaccion que se puede reversar
        $details['id_reversion'] = false; //Para mostrar el detalle de las reversiones que ya se hicieron
        $details['transaction_id'] = $transaction_id;

        if (is_null($transaction_data->reversion_id)) {
            if (($transaction_data->service_id == 85 || $transaction_data->service_id == 74) && $transaction_data->status == 'success' && $transaction_data->service_source_id == 8 && ($transaction_data->owner_id == 16 || $transaction_data->owner_id == 21 || $transaction_data->owner_id == 11 || $transaction_data->owner_id == 14 || $transaction_data->owner_id == 23 || $transaction_data->owner_id == 25)) {
                $details['reversion'] = true;
            }

            if ($transaction_data->status == 'success' && ($transaction_data->service_source_id == 7 || $transaction_data->service_source_id == 10 || $transaction_data->service_source_id == 1 || $transaction_data->service_source_id == 4)) {
                $details['reversion'] = true;
            }

            if (($transaction_data->service_id == 13 || $transaction_data->service_id == 14 || $transaction_data->service_id == 36 || $transaction_data->service_id == 50) && $transaction_data->status == 'success' && $transaction_data->service_source_id == 0) {
                $details['reversion'] = true;
            }

            if ($transaction_data->service_id == 49 && $transaction_data->status == 'success' && $transaction_data->service_source_id == 0 && ($transaction_data->owner_id == 16 || $transaction_data->owner_id == 21 || $transaction_data->owner_id == 25)) {
                $details['reversion'] = true;
            }

            if (!$this->user->hasAccess('reporting.reversion_ken')) {
                $details['reversion'] = false;
            }
        } else {
            $details['id_reversion'] = true;
            $details['fecha'] = date('d/m/Y H:i', strtotime($transaction_data->fecha_reversion));
            $user = \DB::table('users')
                ->select('description')
                ->where('id', $transaction_data->created_by)
                ->first();

            if (!empty($user)) {
                $details['user'] = $user->description;
            } else {
                $details['user'] = '';
            }
        }

        return $details;
    }

    /**
     * Obtiene los movimientos de una transacción junto con las partes del atm y el número de precinto
     */
    public function getTransactionsDetails($id)
    {

        if (!$this->user->hasAnyAccess('reporting', 'reporting_mini_terminal')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $transaction_details = \DB::table('transactions_movements as tm')
            ->select(
                'ap.nombre_parte',
                'tm.accion',
                'tm.valor',
                'tm.cantidad',
                'appn.precinct_number'
            )
            ->join('atms_parts as ap', 'ap.id', '=', 'tm.atms_parts_id')
            //->leftjoin('atms_parts_precinct_number as appn', 'ap.id', '=', 'appn.atms_parts_id')
            ->leftJoin('atms_parts_precinct_number as appn', function ($join) {
                $join->on('ap.id', '=', 'appn.atms_parts_id');
                $join->on('tm.transactions_id', '=', 'appn.transaction_id');
            })
            ->where('tm.transactions_id', '=', $id)
            ->get();

        //\Log::debug('transaction_details:', [ $transaction_details ]);

        $details = '';

        foreach ($transaction_details as $item) {

            $nombre_parte = $item->nombre_parte;
            $accion = $item->accion;
            $valor = number_format($item->valor);
            $cantidad = $item->cantidad;
            $precinct_number = $item->precinct_number;

            $details .= "
                <tr>
                    <td>$nombre_parte</td>
                    <td>$accion</td>
                    <td>$valor</td>
                    <td>$cantidad</td>
                    <td>$precinct_number</td>
                </tr>
            ";
        }

        return $details;
    }

    public function getTransactionsTickets($id)
    {
        if (!$this->user->hasAccess('reporting.print') && $this->user->owner_id <> 45) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if (!is_numeric($id)) {
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $transaction_ticket = \DB::table('transaction_tickets')
            ->where('transaction_id', '=', $id)
            ->first();

        /** Traer datos de la transaccion para obtener el response */
        $transaction = \DB::table('transactions')->where('id', $id)->first();
        $response_data = json_decode($transaction->response_data);
        $service_id = $transaction->service_id;



        if ($service_id == 28 || $service_id == 36) {
            // por el momento, solo estos servicios imprimen código de barras
            if ($service_id == 28) {
                //ticketea
                $barcode_type   = $response_data->type_bar_cod;
                $barcode        = $response_data->bar_cod;
                $barcodegen = $this->getBarcode($barcode, $barcode_type, 2.5, 50);

                $ticket = (str_replace('height:25px;', '', $transaction_ticket->value));
                $ticket .= '<div style="width: 250px; text-align:center"><span style="color:#000000; font-family:Arial; font-size:10pt">Este ticket es una reimpresión</span></div>';
                $ticket .= '<div style="width: 250px; text-align:center">' . $barcodegen . '<br/><p>' . $barcode . '</p></div>';
                $ticket = str_replace('<img src="C:\barcode.png" /></div>', '', $ticket);
                $ticket = str_replace('oacute;', 'ó', $ticket);
            } elseif ($service_id == 36) {
                //apostala
                $barcode_type   = 'C128';
                $barcode        = $response_data->code;
                $barcodegen = $this->getBarcode($barcode, $barcode_type, 1.4, 50);
                $ticket = $transaction_ticket->value;
                $ticket = str_replace('<img alt="" src="c://barcode.png" style="height:70px; width:240px; margin-top: 5px">', $barcodegen, $ticket);
            }
        } else {
            $ticket = (str_replace('height:25px;', '', $transaction_ticket->value));
            $ticket .= '<div style="width: 250px; text-align:center"><span style="color:#000000; font-family:Arial; font-size:10pt">Este ticket es una reimpresión</span></div>';
        }

        //update tickted reprinted status
        \DB::table('transaction_tickets')
            ->where('transaction_id', $id)
            ->update(['reprinted' => true, 'user_id' => $this->user->id]);
        return $ticket;
    }

    public function getBoletasDetails($id)
    {

        $transaction_details = \DB::table('boletas_depositos')
            ->where('id', '=', $id)
            ->get();

        $details = '';
        foreach ($transaction_details as $transaction_detail) {
            $details .= '<tr><td style="display:none;"></td>
              <td style="display:none;"></td>
              <td>' . $transaction_detail->message . '</td>';
        }

        return $details;
    }

    public function insert_alquiler(Request $request)
    {
        $id = $request->_id;

        if (!$this->user->hasAccess('reprocesar_alquiler')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \Log::info("$id");
        try {

            $error  =   "Registro $id confirmado exitosamente";
            $message =   "Registro $id confirmado exitosamente";
            \Log::info("Registro $id confirmado exitosamente");

            Session::flash('message', "Registro $id confirmado exitosamente");

            $service  = new AlquilerController();
            $cobranzas = $service->reprocesarVencimiento($id);

            \Log::info('miniterminales:InsertCuotasAlquiler', ['cuota_alquiler_id' => $id, 'result' => $cobranzas]);

            $error  =   "Registro $id confirmado exitosamente";
            $message =   "Registro $id confirmado exitosamente";
            \Log::info("Registro $id confirmado exitosamente");

            Session::flash('message', "Registro $id confirmado exitosamente");
        } catch (\Exception $e) {
            \Log::error("Error sending Cobranzas Miniterminales  - {$e->getMessage()}");
            $error = true;
            $message =  'Error al intentar migrar la operacion';
            Session::flash('error_message', 'Ha ocurrido un error al intentar guardar el registro');
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }

    public function getCuotasDetails($id)
    {

        $transaction_details = \DB::table('mt_recibos_pagos_miniterminales')
            //->join('mt_recibos_pagos_miniterminales')
            ->where('mt_recibos_pagos_miniterminales.id', '=', $id)
            ->get();

        $details = '';
        foreach ($transaction_details as $transaction_detail) {
            $details .= '<tr><td style="display:none;"></td>
              <td style="display:none;"></td>
              <td>' . $transaction_detail->message . '</td>';
        }

        return $details;
    }

    public function getAlquileresDetails($id)
    {

        $transaction_details = \DB::table('mt_recibos_pagos_miniterminales')
            ->where('id', '=', $id)
            ->get();

        $details = '';
        foreach ($transaction_details as $transaction_detail) {
            $details .= '<tr><td style="display:none;"></td>
              <td style="display:none;"></td>
              <td>' . $transaction_detail->message . '</td>';
        }

        return $details;
    }

    /*public function insert_alquiler(Request $request)
    {
        $id = $request->_id;

        if (!$this->user->hasAccess('reprocesar_alquiler')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \Log::info("$id");
        try {

            $error  =   "Registro $id confirmado exitosamente";
            $message=   "Registro $id confirmado exitosamente";
            \Log::info("Registro $id confirmado exitosamente");

            Session::flash('message', "Registro $id confirmado exitosamente");

            $service  = new AlquilerController();
            $cobranzas = $service->reprocesarVencimiento($id);

            \Log::info('miniterminales:InsertCuotasAlquiler',['cuota_alquiler_id' => $id, 'result'=> $cobranzas]);

            $error  =   "Registro $id confirmado exitosamente";
            $message=   "Registro $id confirmado exitosamente";
            \Log::info("Registro $id confirmado exitosamente");

            Session::flash('message', "Registro $id confirmado exitosamente");         
        }catch(\Exception $e){
            \Log::error("Error sending Cobranzas Miniterminales  - {$e->getMessage()}");
            $error = true;
            $message =  'Error al intentar migrar la operacion';
            Session::flash('error_message', 'Ha ocurrido un error al intentar guardar el registro');
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }*/

    /** BATCH TRANSACTIONS */

    public function batchTransactionsReports()
    {
        if (!$this->user->hasAnyAccess('reporting')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $report = new ReportServices('');
        $result = $report->batchTransactionsReports();
        return view('reporting.index')->with($result);
    }

    public function batchTransactionSearch()
    {
        if (!$this->user->hasAnyAccess('reporting')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $input = \Request::all();
        if (isset($input['search']) || isset($input['context'])  || isset($input['page'])) {
            $report = new ReportServices($input);
            $result = $report->batchTransactionsSearch();
            if ($result) {
                return view('reporting.index')->with($result);
            } else {
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();
            }
        }

        if (isset($input['download'])) {
            ini_set('max_execution_time', 300);
            $report = new ReportServices($input);
            $result = $report->batchTransactionsExport();
            $result = json_decode(json_encode($result), true);
            $filename = 'transacciones_batch_' . time();
            $columnas = array(
                'id', 'Proveedor', 'Servicio', 'Referencia', 'monto', 'ID Transacción', 'Estado', 'Fecha', 'Hora', 'PDV',
                'Codigo Cajero'
            );

            $excel = new ExcelExport($result,$columnas);
            return Excel::download($excel, $filename . '.xls')->send();

            // Excel::create($filename, function ($excel) use ($result) {
            //     $excel->sheet('sheet1', function ($sheet) use ($result) {
            //         $sheet->rows($result, false);
            //         $sheet->prependRow(array(
            //             'id', 'Proveedor', 'Servicio', 'Referencia', 'monto', 'ID Transacción', 'Estado', 'Fecha', 'Hora', 'PDV',
            //             'Codigo Cajero'
            //         ));
            //     });
            // })->export('xls');
            // exit();
        }
    }

    public function batchTransactionReprocess(Request $request)
    {
        if (!$this->user->hasAnyAccess('reporting')) {
            \Log::warning(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            $response = [
                'error'     => true,
                'message'   => 'Acceso no Autorizado'
            ];
            return $response;
        }
        $batchID = $request->_batchID;
        $batchTransaction = new ReportServices(false);
        $reprocess_batch_transaction = $batchTransaction->batchReprocessTransaction($batchID);

        return $reprocess_batch_transaction;
    }

    public function batchTransactionManualReprocess(Request $request)
    {
        if (!$this->user->hasAnyAccess('reporting')) {
            \Log::warning(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            $response = [
                'error'     => true,
                'message'   => 'Acceso no Autorizado'
            ];
            return $response;
        }

        $batchID = $request->_batchID;
        $parentID = $request->_parentID;
        $batchTransaction = new ReportServices(false);
        $reprocess_manually_transaction = $batchTransaction->batchReprocessTransactionManually($batchID, $parentID);

        return $reprocess_manually_transaction;
    }

    public function getBatchDetails($id)
    {
        if (!$this->user->hasAccess('reporting')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $transaction_details = \DB::table('transactions')
            ->select(\DB::raw('transactions.id, service_id, status, transactions.created_at, transactions.amount, transactions_x_payments.payments_id, factura_numero, transactions.service_source_id,service_providers.name as proveedor, service_provider_products.description as servicio'))
            ->leftjoin('transactions_x_payments', 'transactions_x_payments.transactions_id', '=', 'transactions.id')
            ->leftjoin('service_provider_products', 'service_provider_products.id', '=', 'transactions.service_id')
            ->leftjoin('service_providers', 'service_providers.id', '=', 'service_provider_products.service_provider_id')
            ->where('transactions.id', '=', $id)
            ->get();
        $details = '';
        foreach ($transaction_details as $transaction_detail) {
            if ($transaction_detail->status == 'success') {
                $transaction_detail->status =  '<span class="label label-success">' . $transaction_detail->status . '</span>';
            } elseif ($transaction_detail->status == 'canceled' || $transaction_detail->status == 'iniciated') {
                $transaction_detail->status =  '<span class="label label-warning">' . $transaction_detail->status . '</span>';
            } else {
                $transaction_detail->status =  '<span class="label label-danger">' . $transaction_detail->status . '</span>';
            }

            if ($transaction_detail->service_source_id <> 0) {
                $serv_provider = \DB::table('services_providers_sources')->where('id', $transaction_detail->service_source_id)->first();
                $transaction_detail->proveedor = $serv_provider->description;

                $service_data = \DB::table('services_ondanet_pairing')->where('service_request_id', $transaction_detail->service_id)->where('service_source_id', $transaction_detail->service_source_id)->first();

                $transaction_detail->servicio = isset($service_data->service_description) ? $service_data->service_description : '';
            }

            if ($transaction_detail->payments_id == '') {
                $transaction_detail->payments_id = '<i style="color:red;" class="pay-info fa fa-warning" ></i>';
            }

            $details .= '<tr><td style="display:none;"></td>
              <td style="display:none;"></td>
              <td>' . $transaction_detail->proveedor . ' - ' . $transaction_detail->servicio . '</td>
              <td>' . $transaction_detail->id . '</td>
              <td>' . $transaction_detail->status . '</td>
              <td>' . Carbon::parse($transaction_detail->created_at)->format('d/m/Y H:i:s') . '</td>
              <td>' . number_format($transaction_detail->amount) . '</td>
              <td align="center">' . $transaction_detail->payments_id . '</td>
              <td>' . $transaction_detail->factura_numero . '</td></tr>';
        }

        return $details;
    }

    /** PAYMENTS*/
    public function paymentsReports()
    {
        if (!$this->user->hasAnyAccess('reporting')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $report = new ReportServices('');
        $result = $report->paymentsReports();
        return view('reporting.index')->with($result);
    }

    public function paymentsSearch()
    {
        if (!$this->user->hasAnyAccess('reporting')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $input = \Request::all();
        $report = new ReportServices($input);
        $result = $report->paymentsSearch();
        if ($result) {
            return view('reporting.index')->with($result);
        } else {
            Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
            return redirect()->back();
        }
    }

    public function getPaymentDetails($id)
    {
        if (!$this->user->hasAccess('reporting')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $transaction_details = \DB::table('transactions')
            ->select(\DB::raw('transactions.id, service_id, status, transactions.created_at, transactions.amount, transactions_x_payments.payments_id, factura_numero, transactions.service_source_id,service_providers.name as proveedor, service_provider_products.description as servicio'))
            ->leftjoin('transactions_x_payments', 'transactions_x_payments.transactions_id', '=', 'transactions.id')
            ->leftjoin('service_provider_products', 'service_provider_products.id', '=', 'transactions.service_id')
            ->leftjoin('service_providers', 'service_providers.id', '=', 'service_provider_products.service_provider_id')
            ->where('transactions_x_payments.payments_id', '=', $id)
            ->get();
        $details = '';
        foreach ($transaction_details as $transaction_detail) {
            if ($transaction_detail->status == 'success') {
                $transaction_detail->status =  '<span class="label label-success">' . $transaction_detail->status . '</span>';
            } elseif ($transaction_detail->status == 'canceled' || $transaction_detail->status == 'iniciated') {
                $transaction_detail->status =  '<span class="label label-warning">' . $transaction_detail->status . '</span>';
            } else {
                $transaction_detail->status =  '<span class="label label-danger">' . $transaction_detail->status . '</span>';
            }

            if ($transaction_detail->service_source_id <> 0) {
                $serv_provider = \DB::table('services_providers_sources')->where('id', $transaction_detail->service_source_id)->first();
                $transaction_detail->proveedor = $serv_provider->description;

                $service_data = \DB::table('services_ondanet_pairing')->where('service_request_id', $transaction_detail->service_id)->where('service_source_id', $transaction_detail->service_source_id)->first();

                $transaction_detail->servicio = isset($service_data->service_description) ? $service_data->service_description : '';
            }

            if ($transaction_detail->payments_id == '') {
                $transaction_detail->payments_id = '<i style="color:red;" class="pay-info fa fa-warning" ></i>';
            }

            $details .= '<tr><td style="display:none;"></td>
              <td style="display:none;"></td>
              <td>' . $transaction_detail->proveedor . ' - ' . $transaction_detail->servicio . '</td>
              <td>' . $transaction_detail->status . '</td>
              <td>' . Carbon::parse($transaction_detail->created_at)->format('d/m/Y H:i:s') . '</td>
              <td>' . $transaction_detail->id . '</td>
              <td>' . number_format($transaction_detail->amount) . '</td>
              <td align="center">' . $transaction_detail->payments_id . '</td>
              <td>' . $transaction_detail->factura_numero . '</td></tr>';
        }

        return $details;
    }

    /** * NOTIFICACIONES*/
    public function notificationsReports()
    {
        if (!$this->user->hasAccess('reporting')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $report = new ReportServices('');
        $result = $report->notificationsReports();
        return view('reporting.index')->with($result);
    }

    public function notificationsSearch()
    {
        if (!$this->user->hasAccess('reporting')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $input = \Request::all();


        if (isset($input['search']) || isset($input['page'])) {
            $report = new ReportServices($input);
            $result = $report->notificationsSearch();
            return view('reporting.index')->with($result);
        }

        if (isset($input['download'])) {
            $report = new ReportServices($input);

            ini_set('max_execution_time', 300);
            $result = $report->notificationsSearchExport();
            $result = json_decode(json_encode($result), true);
            $i = 0;
            foreach ($result as $r) {
                if ($r['fecha_fin']) {
                    $from_time = strtotime($r['fecha_inicio']);
                    $to_time = strtotime($r['fecha_fin']);
                    $mins = ceil(($to_time - $from_time) / 60) . ' minutos';
                } else {
                    $from_time = strtotime($r['fecha_inicio']);
                    $to_time = time();
                    $mins = ceil(($to_time - $from_time) / 60) . ' minutos';
                }
                array_push($r, $mins);
                $r['transcurrido'] = $r[0];
                unset($r[0]);
                $result[$i] = $r;
                $i++;
            }

            $filename = 'notificaciones_' . time();
            $columnas = [];

            $excel = new ExcelExport($result,$columnas);
            return Excel::download($excel, $filename . '.xls')->send();

            // Excel::create($filename, function ($excel) use ($result) {
            //     $excel->sheet('sheet1', function ($sheet) use ($result) {
            //         $sheet->fromArray($result);
            //     });
            // })->export('xls');
            // exit();
        }
    }

    /** ARQUEOS*/
    public function arqueosReports()
    {
        if (!$this->user->hasAnyAccess('reporting', 'reporting_mini_terminal')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $report = new ReportServices('');
        $result = $report->arqueosReports();
        return view('reporting.index')->with($result);
    }

    public function arqueosSearch()
    {
        if (!$this->user->hasAnyAccess('reporting', 'reporting_mini_terminal')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $input = \Request::all();

        if (isset($input['search']) || isset($input['page'])) {
            $report = new ReportServices($input);
            $result = $report->arqueosSearch();
            if ($result) {
                return view('reporting.index')->with($result);
            } else {
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();
            }
        }

        if (isset($input['download'])) {
            $report = new ReportServices($input);
            $result = $report->arqueosSearchExport();
            $result = json_decode(json_encode($result), true);
            $columnas = array(
                'ID', 'Fecha', 'Valor', 'Autorizado por', 'Cod Cajero', 'Tipo', 'Sede', 'Autorizado'
            );

            if ($result) {
                $filename = 'arqueos_' . time();

                $excel = new ExcelExport($result,$columnas);
                return Excel::download($excel, $filename . '.xls')->send();

                // Excel::create($filename, function ($excel) use ($result) {
                //     $excel->sheet('sheet1', function ($sheet) use ($result) {
                //         $sheet->rows($result, false);
                //         //set colum names
                //         $sheet->prependRow(array(
                //             'ID', 'Fecha', 'Valor', 'Autorizado por', 'Cod Cajero', 'Tipo', 'Sede', 'Autorizado'
                //         ));
                //     });
                // })->export('xls');
                // exit();
            } else {
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();
            }
        }
    }
    /** CARGAS*/

    public function cargasReports()
    {
        if (!$this->user->hasAccess('reporting')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $report = new ReportServices('');
        $result = $report->cargasReports();
        return view('reporting.index')->with($result);
    }

    public function cargasSearch()
    {
        if (!$this->user->hasAccess('reporting')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $input = \Request::all();

        if (isset($input['search']) || isset($input['page'])) {
            $report = new ReportServices($input);
            $result = $report->cargasSearch();
            if ($result) {
                return view('reporting.index')->with($result);
            } else {
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();
            }
        }

        if (isset($input['download'])) {
            $report = new ReportServices($input);
            $result = $report->cargasSearchExport();
            $result = json_decode(json_encode($result), true);
            $columnas = array(
                'ID', 'Fecha', 'Valor', 'Autorizado por', 'Cod Cajero', 'Tipo', 'Sede', 'Autorizado'
            );

            if ($result) {
                $filename = 'cargas_' . time();

                $excel = new ExcelExport($result,$columnas);
                return Excel::download($excel, $filename . '.xls')->send();

                // Excel::create($filename, function ($excel) use ($result) {
                //     $excel->sheet('sheet1', function ($sheet) use ($result) {
                //         //$sheet->fromArray($result);
                //         $sheet->rows($result, false);
                //         //set colum names
                //         $sheet->prependRow(array(
                //             'ID', 'Fecha', 'Valor', 'Autorizado por', 'Cod Cajero', 'Tipo', 'Sede', 'Autorizado'
                //         ));
                //     });
                // })->export('xls');
                // exit();
            } else {
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();
            }
        }
    }


    /** SALDOS*/

    public function saldosReports()
    {
        if (!$this->user->hasAccess('saldos_linea')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        try {

            //Redes
            $whereOwner = "";
            $whereBranch = "";
            $wherePos = "";
            if (!$this->user->hasAccess('superuser')) {
                if ($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11) {
                    $whereOwner = "owners.id = " . $this->user->owner_id;
                    $whereBranch = "branches.owner_id = " . $this->user->owner_id;
                    $wherePos = "points_of_sale.owner_id = " . $this->user->owner_id;
                }
            }

            //Redes
            /*$owners     = Owner::orderBy('owners.name')->where(function($query) use($whereOwner){
                if(!empty($whereOwner)){
                    $query->whereRaw($whereOwner);
                }
            })->whereNotIn('owners.id',[16, 21])->get()->pluck('name','id');
            $owners->prepend('Todos','0');*/

            $excluded_owners = [18, 21, 23, 25];

            //Redes
            $owners = Owner::orderBy('owners.name')->where(function ($query) use ($whereOwner) {
                if (!empty($whereOwner)) {
                    $query->whereRaw($whereOwner);
                }
            })
                ->whereNotIn('owners.id', $excluded_owners) // Redes excluidas
                ->get()
                ->pluck('name', 'id');


            $branches   = Branch::orderBy('description')->where(function ($query) use ($whereBranch) {
                if (!empty($whereBranch)) {
                    $query->whereRaw($whereBranch);
                }
            })
                ->whereNotIn('owner_id', $excluded_owners)
                ->whereIn('id', function ($query) {
                    $query->select('branch_id')
                        ->from('points_of_sale')
                        ->join('atms', 'atms.id', '=', 'points_of_sale.atm_id')
                        ->where('atms.deleted_at', null);
                })->get()->pluck('description', 'id');
            $branches->prepend('Todos', '0');
            $pdvs       = Pos::orderBy('description')->where(function ($query) use ($wherePos) {
                if (!empty($wherePos)) {
                    $query->whereRaw($wherePos);
                }
            })->with('Atm')->get();
            $pos = [];
            $item = array();
            $item[0] = 'Todos';
            foreach ($pdvs  as $pdv) {
                $item[$pdv->id] = $pdv->description . ' - ' . $pdv->Atm->code;
                $pos = $item;
            }
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de reportes" . $e->getMessage());
            return redirect('/');
        }
        $target = 'Saldos';
        return view('reporting.index', compact('target', 'owners', 'branches', 'pos'));
    }

    public function saldosSearch()
    {
        if (!$this->user->hasAccess('reporting')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }



        try {

            $excluded_owners = [18, 21, 23, 25];

            $input = \Request::all();
            $where = "transactions.transaction_type = 4 AND ";
            /*Busqueda minusiosa*/
            if (isset($input['context']) && $input['context'] <> '' && $input['context'] <> null) {
                //$where .= "transactions.request_data like '%{$input['context']}%'";
            } else {
                /*SET DATE RANGE*/
                $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                $daterange[0] = date('Y-m-d H:i:s', strtotime($daterange[0]));
                $daterange[1] = date('Y-m-d H:i:s', strtotime($daterange[1]));
                $where .= "transactions.created_at::date BETWEEN '{$daterange[0]}' AND '{$daterange[1]}' AND ";

                //$where .= ($input['owner_id']<>0) ? "transactions.owner_id = ". $input['owner_id'] ." AND " : "";
                $where .= ($input['branch_id'] <> 0) ? "points_of_sale.branch_id = " . $input['branch_id'] . " AND " : "";
                $where .= ($input['pos_id'] <> 0) ? "points_of_sale.id = " . $input['pos_id'] . " AND " : "";
            }

            $where = trim($where);
            $where = trim($where, 'AND');
            $where = trim($where);

            $transactions = \DB::table('transactions')
                ->select(\DB::raw('transactions.id,transactions.amount,transactions.atm_transaction_id,transaction_types.description as transaction_type ,transactions.created_at,points_of_sale.description as sede, atms.code as code'))
                ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'transactions.atm_id')
                ->join('atms', 'atms.id', '=', 'transactions.atm_id')
                ->join('transaction_types', 'transaction_types.id', '=', 'transactions.transaction_type')
                ->whereRaw("$where")
                ->whereNotIn('atms.owner_id', $excluded_owners)
                ->orderBy('transactions.created_at', 'desc')
                ->paginate(20);

            /*Carga datos del formulario*/
            $owners     = Owner::all()->pluck('name', 'id');
            $owners->prepend('Todos', '0');
            $branches   = Branch::all()->pluck('description', 'id');
            $branches->prepend('Todos', '0');
            $pdvs       = Pos::with('Atm')->get();
            $pos = [];
            $item = array();
            $item[0] = 'Todos';
            foreach ($pdvs  as $pdv) {
                $item[$pdv->id] = $pdv->description . ' - ' . $pdv->Atm->code;
                $pos = $item;
            }

            $target     = 'Cargas';
            $owner_id   = (isset($input['owner_id']) ? $input['owner_id'] : 0);
            $branch_id  = (isset($input['branch_id']) ? $input['branch_id'] : 0);
            $pos_id     = (isset($input['pos_id']) ? $input['pos_id'] : 0);
            $reservationtime = (isset($input['reservationtime']) ? $input['reservationtime'] : 0);

            return view('reporting.index', compact('target', 'owners', 'branches', 'pos', 'transactions', 'owner_id', 'branch_id', 'pos_id', 'reservationtime'));
        } catch (\Exception $e) {
            \Log::info($e);
        }
    }

    public function saldosDetails($owner_id, $branch_id)
    {
        if (!$this->user->hasAccess('reporting')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        try {

            $excluded_owners = [18, 21, 23, 25];

            if ($branch_id <> 0) {
                $branches = Branch::where('id', $branch_id)
                    ->whereIn('id', function ($query) {
                        $query->select('branch_id')
                            ->from('points_of_sale')
                            ->join('atms', 'atms.id', '=', 'points_of_sale.atm_id')
                            ->where('atms.deleted_at', null);
                    })
                    //->whereRaw('owner_id not in (16, 21)')
                    ->whereNotIn('owner_id', $excluded_owners)
                    ->get();
            } else {
                if ($owner_id == 0) {
                    $branches = Branch::where(function ($q) {
                        if ($this->user->branch_id <> null && !$this->user->hasAccess('superuser')) {
                            $q->where('id', $this->user->branch_id);
                        }
                    })
                        ->whereIn('id', function ($query) {
                            $query->select('branch_id')
                                ->from('points_of_sale')
                                ->join('atms', 'atms.id', '=', 'points_of_sale.atm_id')
                                ->where('atms.deleted_at', null);
                        })
                        ->whereNotIn('owner_id', $excluded_owners)->get();
                } else {
                    $branches = Branch::where('owner_id', $owner_id)->where(function ($q) {
                        if ($this->user->branch_id <> null && !$this->user->hasAccess('superuser')) {
                            $q->where('id', $this->user->branch_id);
                        }
                    })
                        ->whereIn('id', function ($query) {
                            $query->select('branch_id')
                                ->from('points_of_sale')
                                ->join('atms', 'atms.id', '=', 'points_of_sale.atm_id')
                                ->where('atms.deleted_at', null);
                        })
                        ->whereNotIn('owner_id', $excluded_owners)->get();
                }
            }

            $item = [];
            foreach ($branches as $branch) {
                $item[$branch->id]['branch_name'] = $branch->description;
                $item[$branch->id]['branch_id'] = $branch->id;
                $pdvs = Pos::with(['atm' => function ($query) {
                    $query->where('deleted_at', null);
                }])->where('branch_id', '=', $branch->id)->get();
                $pos = [];
                foreach ($pdvs as $pdv) {
                    if (isset($pdv->atm->code)) {
                        $pos[$pdv->atm->code]['atm_code'] = $pdv->atm->code;
                        /** listar atm parts*/
                        $atm_parts = \DB::table('atms_parts')->orderBy('tipo_partes', 'desc')->orderBy('atm_id', 'desc')->where('atm_id', '=', $pdv->atm->id)->get();
                        $parts = [];
                        foreach ($atm_parts as $atm_part) {
                            $parts[$atm_part->id]['tipo_partes'] = $atm_part->tipo_partes;
                            $parts[$atm_part->id]['denominacion'] = $atm_part->denominacion;
                            $parts[$atm_part->id]['cantidad'] = $atm_part->cantidad_actual;
                            $parts[$atm_part->id]['cantidad_min'] = $atm_part->cantidad_minima;
                            $parts[$atm_part->id]['cantidad_max'] = $atm_part->cantidad_maxima;
                            $parts[$atm_part->id]['cantidad_alarma'] = $atm_part->cantidad_alarma;
                            $parts[$atm_part->id]['subtotal'] = $atm_part->denominacion * $atm_part->cantidad_actual;
                        }

                        $item[$branch->id]['pdvs'][$pdv->atm->code]['atm_code'] = $pdv->atm->code;
                        $item[$branch->id]['pdvs'][$pdv->atm->code]['parts'] = $parts;
                    }
                }
            }
            $response['error'] = false;
            $response['branches'] = $item;
            return $response;
        } catch (\Exception $e) {
            $response['error'] = true;
            \Log::warning($e);
            return $response;
        }
    }

    public function saldosexport(Request $request)
    {

        $result = $request->_resumen;
        $array = [];
        $data = [];
        $cassettes = '';
        $hoppers = '';
        $box =  '';
        foreach ($result as $key => $value) {
            $value = explode(',', $value);
            $value  = str_replace('[object Object]', '', $value);
            foreach ($value as $index => $val) {
                if ($index <> 5) {
                    $val = str_replace('.', '', $val);
                    if ($index == 0)
                        $data['ATM'] = $val;
                    if ($index == 1)
                        $data['Total'] = $val;
                    if ($index == 2)
                        $cassettes = str_replace('Cassettes:', '', $val);
                    $cassettes = trim($cassettes);
                    $data['Cassettes'] = $cassettes;
                    if ($index == 3)
                        $hoppers = str_replace('Hoppers', '', $val);
                    $hoppers = trim($hoppers);
                    $data['Hoppers'] = $hoppers;
                    if ($index == 4)
                        $box = str_replace('Box:', '', $val);
                    $box = trim($box);
                    $data['Box'] = $box;
                }
            }
            $array[$key] = $data;
        }

        $filename = 'resumen_saldos_' . time() . '.xls';

        $columnas = array(
            'ATM', 'Cassetes', 'Hoppers', 'Box', 'Total'
        );

        $excel = new ExcelExport($array, $columnas);
        $excelFile = Excel::download($excel, $filename)->getFile();

        $fileContents = $excelFile->getContent();
        $base64File = base64_encode($fileContents);

        $response = [
            'status' => true,
            'name' => $filename,
            'file' => 'data:application/vnd.ms-excel;base64,' . $base64File
        ];

        return response()->json($response);



        // $filename = 'resumen_saldos_' . time() . '.xls';
        // $myFile = Excel::create($filename, function ($excel) use ($array) {
        //     $excel->setTitle('title');
        //     $excel->sheet('sheet 1', function ($sheet) use ($array) {
        //         $sheet->setColumnFormat(array('B' => '0'));
        //         $sheet->setColumnFormat(array('C' => '0'));
        //         $sheet->setColumnFormat(array('D' => '0'));
        //         $sheet->setColumnFormat(array('E' => '0'));
        //         $sheet->rows($array, false);
        //         //set colum names
        //         $sheet->prependRow(array(
        //             'ATM', 'Cassetes', 'Hoppers', 'Box', 'Total'
        //         ));
        //     });
        // });

        // $myFile = $myFile->string('xls'); //change xlsx for the format you want, default is xls
        // $response =  array(
        //     'status'    => true,
        //     'name'      => $filename, //no extention needed
        //     'file'      => "data:application/vnd.openxmlformats-officedocument.spreadsheetml.sheet;base64," . base64_encode($myFile) //mime type of used format
        // );
        // return response()->json($response);
    }


    /** GENERAL*/

    public function getOwnersbyGroups($group_id)
    {
        if (!$this->user->hasAccess('reporting')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        if ($group_id == 0) {
            $owners = Owner::orderBy('name', 'ASC')->get()->pluck();
        } else {
            //$branches = Branch::where('owner_id',$owner_id,'')->pluck('description','id');
            $owners = \DB::table('branches')
                ->select('owners.id', 'owners.name')
                ->join('owners', 'owners.id', '=', 'branches.owner_id')
                ->where('group_id', '=', $group_id)
                ->orderBy('owners.name', 'ASC')
                ->get();
        }

        $own = [];
        $item = array();
        $item[0] = 'Todos';
        foreach ($owners  as $owner) {
            $item[$owner->id] = $owner->name;
            $own = $item;
        }

        return ($own);
    }

    public function getBranchesbyGroups($group_id)
    {
        if (!$this->user->hasAccess('reporting')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        if ($group_id == 0) {
            $branches = Branch::all()->pluck('description', 'id');
        } else {
            $branches = \DB::table('branches')
                ->select('description', 'id')
                ->where('group_id', '=', $group_id)
                ->orderBy('description', 'ASC')
                ->get();
        }
        $branch = [];
        $item = array();
        $item[0] = 'Todos';
        foreach ($branches  as $branche) {
            $item[$branche->id] = $branche->description;
            $branch = $item;
        }
        return ($branch);
    }

    public function getBranchesbyOwners($group_id, $owner_id)
    {
        if (!$this->user->hasAccess('reporting')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if ($group_id == 0) {
            if ($owner_id == 0) {
                //$branches = Branch::all()->pluck('description','id');
                $branches = Branch::orderBy('description', 'ASC')->get()->pluck();
            } else {
                //$branches = Branch::where('owner_id',$owner_id,'')->pluck('description','id');
                $branches = Branch::orderBy('description', 'ASC')->where('owner_id', $owner_id, '')->pluck('description', 'id');
            }
            $branches->prepend('Todos', '0');
            return ($branches);
        } else {
            if ($owner_id == 0) {
                //$branches = Branch::all()->pluck('description','id');
                $branches = \DB::table('branches')
                    ->select('id', 'description')
                    ->where('group_id', '=', $group_id)
                    ->get();
                //$branches = Branch::orderBy('description','ASC')->where('group_id',$group_id,'')->pluck('description','id');
            } else {
                $branches = \DB::table('branches')
                    ->select('id', 'description')
                    ->where('group_id', '=', $group_id)
                    ->where('owner_id', '=', $owner_id)
                    ->orderBy('description', 'ASC')
                    ->get();
                //$branches = Branch::orderBy('description','ASC')->where('group_id',$group_id,'')->where('owner_id', $owner_id, '')->pluck('description','id');
            }
            $branch = [];
            $item = array();
            $item[0] = 'Todos';
            foreach ($branches  as $branche) {
                $item[$branche->id] = $branche->description;
                $branch = $item;
            }
            return ($branch);
        }
    }

    public function getPdvsbyBranches($branch_id)
    {
        /*if (!$this->user->hasAccess('reporting') || !$this->user->hasRole('red_claro')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }*/

        $owner_id = null;

        if (!$this->user->hasAccess('superuser')) {
            if (($this->user->owner_id <> null && $this->user->owner_id <> 2 && $this->user->owner_id <> 11)) {
                $owner_id = $this->user->owner_id;
            } else {
                $owner_id = false;
            }
        }

        if ($branch_id == 0) {
            if (!$owner_id) {
                $pdvs = \DB::table('points_of_sale')
                    ->select('points_of_sale.id', 'points_of_sale.description', 'atms.code')
                    ->join('atms', 'atms.id', '=', 'points_of_sale.atm_id')
                    ->orderBy('description', 'ASC')
                    ->get();
            } else {
                $pdvs = \DB::table('points_of_sale')
                    ->select('points_of_sale.id', 'points_of_sale.description', 'atms.code')
                    ->join('atms', 'atms.id', '=', 'points_of_sale.atm_id')
                    ->where('points_of_sale.owner_id', '=', $owner_id)
                    ->orderBy('description', 'ASC')
                    ->get();
            }
        } else {
            if (!$owner_id) {
                $pdvs = \DB::table('points_of_sale')
                    ->select('points_of_sale.id', 'points_of_sale.description', 'atms.code')
                    ->join('atms', 'atms.id', '=', 'points_of_sale.atm_id')
                    ->where('branch_id', '=', $branch_id)
                    ->orderBy('description', 'ASC')
                    ->get();
            } else {
                $pdvs = \DB::table('points_of_sale')
                    ->select('points_of_sale.id', 'points_of_sale.description', 'atms.code')
                    ->join('atms', 'atms.id', '=', 'points_of_sale.atm_id')
                    ->where('branch_id', '=', $branch_id)
                    ->where('points_of_sale.owner_id', '=', $owner_id)
                    ->orderBy('description', 'ASC')
                    ->get();
            }
        }

        $pos = [];
        $item = array();
        foreach ($pdvs  as $pdv) {
            $item[$pdv->id] = $pdv->description . ' - ' . $pdv->code;
            $pos = $item;
        }
        return ($pos);
    }

    public function getUsersbyGroups($group_id)
    {

        $usersNames = \DB::connection('eglobalt_auth')
            ->table('users')
            ->selectRaw('concat(username, \' - \', description) as full_name, id')
            ->join('role_users', 'users.id', '=', 'role_users.user_id')
            ->where('role_users.role_id', 22)
            ->pluck('full_name', 'id');

        if ($group_id == 0) {
            $users = \DB::table('users')
                ->join('role_users', 'users.id', '=', 'role_users.user_id')
                ->where('role_users.role_id', 22)
                ->pluck('id', 'id');
        } else {
            //$branches = Branch::where('owner_id',$owner_id,'')->pluck('description','id');
            $users = \DB::table('users')
                ->join('branches', 'users.id', '=', 'branches.user_id')
                ->where('branches.group_id', '=', $group_id)
                ->whereIn('branches.owner_id', [16, 21, 25])
                ->pluck('users.id', 'users.id');
        }

        $branches = \DB::table('branches')
            ->select('branches.*')
            ->whereIn('branches.user_id', $users)
            ->get();

        $usuario = [];
        $item = array();
        $item[0] = 'Todos';
        foreach ($branches as $branch) {
            //$item[$user->id] = $user->description;
            $item[$branch->user_id] = $branch->description . ' | ' . $usersNames[$branch->user_id];
            $usuario = $item;
        }

        return ($usuario);
    }

    public function getAtmsbyGroups($group_id)
    {
        if ($group_id == 0) {
            $atms = Atm::whereIn('owner_id', [16, 21, 25])->pluck('name', 'id');
        } else {
            //$branches = Branch::where('owner_id',$owner_id,'')->pluck('description','id');
            $atms = \DB::table('atms')
                ->select('atms.id as atm_id', 'atms.name as nombre')
                ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->where('branches.group_id', '=', $group_id)
                ->whereIn('atms.owner_id', [16, 21, 25])
            ->get();
        }

        $cajero = [];
        $item = array();
        $item[0] = 'Todos';
        foreach ($atms as $atm) {
            $item[$atm->atm_id] = $atm->nombre;
            //$item[$branch->user_id] = $branch->description.' | '.$usersNames[$branch->user_id];
            $cajero = $item;
        }
        return ($cajero);
    }

    private function getBarcode($barcode, $barcode_type, $width = 2, $height = 66)
    {

        switch ($barcode_type) {
            case 'EAN-13':
                $type = 'EAN13';
                break;
            case 'EAN-128':
                $type = 'C128';
                break;
            case 'EAN':
                $type = 'EAN13';
                break;
            case 'EAN-IPC':
                $type = 'EAN13';
                break;
            case 'CODE-39':
                $type = 'C93';
                break;
            case 'CODE-128':
                $type = 'C128';
                break;
            case 'UPC-A':
                $type = 'UPCA';
                break;
            case 'PDF417':
                $type = 'PDF417';
                break;
            case 'QR-CODE':
                $type = 'QRCODE';
                break;
            case 'INTERLEAVED 2OF5':
                $type = 'I25';
                break;
            default:
                $type = 'C128';
        }


        $barcodegen   = \DNS1D::getBarcodeSVG($barcode, $type, $width, $height);
        return $barcodegen;
    }

    public function getAtmNotification($atm_id)
    {
        if (!$this->user->hasAccess('reporting')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $report = new ReportServices('');
        $result = $report->atmNotifications($atm_id);

        return $result;
    }

    /** BATCH TRANSACTIONS */

    public function oneDayTransactionsReports()
    {
        if (!$this->user->hasAnyAccess('reporting')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $report = new ReportServices('');
        $result = $report->oneDayTransactionsReports();
        return view('reporting.index')->with($result); // 12/01/2021
    }

    public function oneDayTransactionsSearch()
    {
        if (!$this->user->hasAnyAccess('reporting', 'ticketea')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = \Request::all();
        if (isset($input['search']) || isset($input['context']) || isset($input['page'])) {
            $report = new ReportServices($input);
            $result = $report->oneDayTransactionsSearch();
            if ($result) {
                return view('reporting.index')->with($result); // 12/01/2021
            } else {
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();
            }
        }

        if (isset($input['download'])) {
            ini_set('max_execution_time', 300);
            $report = new ReportServices($input);
            $result = $report->oneDayTransactionsSearchExport();
            $result = json_decode(json_encode($result), true);
            $report2 = new ReportServices($input);
            $result2 = $report2->oneDaytransactionsSearchExportMovements();
            $result2 = json_decode(json_encode($result2), true);
            $filename = 'transacciones_' . time();
            $columna1 = array(
                'id', 'Proveedor', 'Tipo', 'Estado', 'Descripcion', 'Fecha', 'Hora', 'Valor Transaccion', 'Cod. Pago', 'Forma pago',
                'Identificador transaccion', 'Factura Nro', 'Sede', 'Red', 'Ref 1', 'Ref 2', 'Codigo Cajero'
            );
            $columna2 = array(
                'id', 'transactions_id', 'atms_parts_id', 'accion', 'cantidad', 'valor', 'dinero_virtual', 'payments_id', 'fecha', 'hora'
            );

            if ($result) {

                $excel = new ExcelExport($result,$columna1,$result2,$columna2);
                return Excel::download($excel, $filename . '.xls')->send();

                // Excel::create($filename, function ($excel) use ($result, $result2) {
                //     $excel->sheet('Página 1', function ($sheet) use ($result) {
                //         $sheet->rows($result, false);
                //         $sheet->prependRow(array(
                //             'id', 'Proveedor', 'Tipo', 'Estado', 'Descripcion', 'Fecha', 'Hora', 'Valor Transaccion', 'Cod. Pago', 'Forma pago',
                //             'Identificador transaccion', 'Factura Nro', 'Sede', 'Red', 'Ref 1', 'Ref 2', 'Codigo Cajero'
                //         ));
                //     });
                //     $excel->sheet('Página 2', function ($sheet) use ($result2) {
                //         $sheet->rows($result2, false);
                //         $sheet->prependRow(array(
                //             'id', 'transactions_id', 'atms_parts_id', 'accion', 'cantidad', 'valor', 'dinero_virtual', 'payments_id', 'fecha', 'hora'
                //         ));
                //     });
                // })->export('xls');
                // exit();
            } else {
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();
            }
        }
    }

    /** getServiceRequest */

    public function getServiceRequest(Request $request)
    {
        switch ($request->get('id')) {
            case '-2':
                $serviceSourceId = 1;
                break;
            case '-4':
                $serviceSourceId = 4;
                break;
            case '-6':
                $serviceSourceId = 6;
                break;
            case '-7':
                $serviceSourceId = 7;
                break;
            case '-8':
                $serviceSourceId = 8;
                break;
            case '-10':
                $serviceSourceId = 10;
                break;
            case '-11':
                $serviceSourceId = 11;
                break;
            default:
                break;
        }

        $services = \DB::table('services_ondanet_pairing')
            ->where('service_source_id', $serviceSourceId)
            ->orderBy('service_request_id', 'DESC')
            ->get();

        $data = [
            '0' => [
                'id' => '0',
                'text' => '-- Todos --'
            ]
        ];
        foreach ($services as $serviceRequestId => $service) {
            $valor = [];
            $valor['id'] = $service->service_request_id;
            $valor['text'] = $service->service_description;
            $data[] = $valor;
        }

        return $data;
    }

    /** getServiceRequest */

    public function getServiceRequestAll(Request $request)
    {
        $ids = str_replace('[', '', $request->get('id'));
        $ids = str_replace(']', '', $ids);
        $ids = str_replace('"', '', $ids);
        $ids = explode(',', $ids);
        $data = [];
        foreach ($ids as $key => $id) {
            if (strstr($id, '-')) {
                switch ($id) {
                    case '-2':
                        $serviceSourceId = 1;
                        break;
                    case '-4':
                        $serviceSourceId = 4;
                        break;
                    case '-6':
                        $serviceSourceId = 6;
                        break;
                    case '-7':
                        $serviceSourceId = 7;
                        break;
                    case '-8':
                        $serviceSourceId = 8;
                    default:
                        break;
                }

                $services = \DB::table('services_ondanet_pairing')
                    ->where('service_source_id', $serviceSourceId)
                    ->get();

                foreach ($services as $serviceRequestId => $service) {
                    $valor = [];
                    $valor['id'] = $service->service_request_id;
                    $valor['text'] = $service->service_description;
                    $data[] = $valor;
                }
            }
        }

        return $data;
    }

    /** TRANSACTIONS*/
    public function resumenTransacciones()
    {
        if (!$this->user->hasAnyAccess('reporting')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $report = new ReportServices('');
        $result = $report->resumenTransacciones();
        return view('reporting.index')->with($result);
    }

    public function resumenSearch(Request $request)
    {
        if (!$this->user->hasAnyAccess('reporting')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = \Request::all();

        if ($request->ajax()) {
            $report = new ReportServices($input);
            $result = $report->resumenSearch();
            return $result;
        }

        if (isset($input['search']) || isset($input['context']) || isset($input['page'])) {
            $report = new ReportServices($input);
            $result = $report->resumenSearch();
            return view('reporting.index')->with($result);
        }

        if (isset($input['download'])) {
            ini_set('max_execution_time', 300);
            $report = new ReportServices($input);
            $result = $report->resumenSearchExport();
            $result = json_decode(json_encode($result), true);
            $filename = 'resumen_por_atm_' . time();
            $columna1 = array(
                'Valor', 'Proveedor', 'Servicio'
            );
            $columna2 = array(
                'Valor', 'Red'
            );

            $excel = new ExcelExport($result['transactionsEglobalt'],$columna1,$result['transactionsProviders'],$columna2);
            return Excel::download($excel, $filename . '.xls')->send();

            // Excel::create($filename, function ($excel) use ($result) {
            //     $excel->sheet('Eglobalt', function ($sheet) use ($result) {
            //         $sheet->rows($result['transactionsEglobalt'], false);
            //         $sheet->prependRow(array(
            //             'Valor', 'Proveedor', 'Servicio'
            //         ));
            //     });

            //     $excel->sheet('Otras Redes', function ($sheet) use ($result) {
            //         $sheet->rows($result['transactionsProviders'], false);
            //         $sheet->prependRow(array(
            //             'Valor', 'Red'
            //         ));
            //     });
            // })->export('xls');
            // exit();
        }
    }

    public function resumenSearchDetalleExport(Request $request)
    {
        if (!$this->user->hasAnyAccess('reporting')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = \Request::all();

        ini_set('max_execution_time', 300);
        $report = new ReportServices($input);
        $result = $report->resumenSearchDetalleExport();
        $result = json_decode(json_encode($result), true);
        $filename = 'resumen_por_atm_' . time();
        $columnas = array(
            'Valor', 'Servicio'
        );

        $excel = new ExcelExport($result['transactions'],$columnas);
        return Excel::download($excel, $filename . '.xls')->send();

        // Excel::create($filename, function ($excel) use ($result) {
        //     $excel->sheet($result['nombre'], function ($sheet) use ($result) {
        //         $sheet->rows($result['transactions'], false);
        //         $sheet->prependRow(array(
        //             'Valor', 'Servicio'
        //         ));
        //     });
        // })->export('xls');
        // exit();
    }

    public function Procesar_devolucion(Request $request)
    {

        if (!$this->user->hasAnyAccess('reporting.devolucion')) {
            \Log::warning(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            $response = [
                'error'     => true,
                'message'   => 'Acceso no Autorizado'
            ];
            return $response;
        }
        $transaction_id = $request->_transaction_id;

        if ($request->hasFile('fuComprobante')) {
            $destinationPath = './comprobantes_devoluciones';
            $file = $request->file('fuComprobante')->getClientOriginalName();
            $extension = pathinfo($file, PATHINFO_EXTENSION);

            $request->file('fuComprobante')->move($destinationPath, $transaction_id . '.' . $extension);
        }

        //Transaction DATA
        $transaction = \DB::table('transactions')
            ->select('transactions.id', 'transactions.status', 'transactions.service_id', 'transactions.service_source_id', 'points_of_sale.id as pos_id')
            ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'transactions.atm_id')
            ->where('transactions.id', $transaction_id)
            ->first();

        if (!$transaction) {
            return 'Transacción no existe';
        }

        if ($transaction->status == 'iniciated' || $transaction->status == 'error' || $transaction->status == 'error dispositivo') {

            if ($transaction->service_id == 8 && $transaction->service_source_id == 0) {
                //cambiar a estado devolucion
                \DB::table('transactions')
                    ->where('id', $transaction_id)
                    ->update(['status' => 'success', 'status_description' => $request->txtDescription]);
                //guardar comprobante de devolucion y comentario
            } else {
                //cambiar a estado devolucion
                \DB::table('transactions')
                    ->where('id', $transaction_id)
                    ->update(['status' => 'devolucion', 'status_description' => $request->txtDescription]);
                //guardar comprobante de devolucion y comentario
            }
            //Migrar concepto de devolucion a ONDANET

            $register_movement = \DB::table('incomes')->insert(
                [
                    'operation'                 => 'register_devolucion',
                    'origin_operation_id'       => $transaction_id,
                    'destination_operation_id'  => 0,
                    'ondanet_code'              => 704,
                    'transaction_type'          => 1,
                    'response'                  => '',
                    'pos_id'                    => $transaction->pos_id,
                    'client_id'                 => 4,
                    'transaction_id'            => $transaction_id
                ]
            );
        }

        return 'Devolución procesada para la transacción: ' . $transaction_id;
    }

    public function Reprocesar_transaccion(Request $request)
    {
        if (!$this->user->hasAnyAccess('reporting.devolucion')) {
            \Log::warning(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            $response = [
                'error'     => true,
                'message'   => 'Acceso no Autorizado'
            ];
            return $response;
        }

        //Transaction DATA
        $transaction_id = $request->_transaction_id;

        $transaction = \DB::table('transactions')
            ->where('id', $transaction_id)
            ->first();

        if (!$transaction) {
            return 'Transacción no existe';
        }

        $service_id         = $transaction->service_id;
        $service_source_id  = $transaction->service_source_id;
        $referencia_1       = $transaction->referencia_numero_1;
        $referencia_2       = $transaction->referencia_numero_2;
        $monto_transaction  = number_format($transaction->amount, 0, '', '');

        $data = [
            'service_id'        => $service_id,
            'service_source_id' => $service_source_id,
            'referencia_nro_1'  => $referencia_1,
            'referencia_nro_2'  => $referencia_2,
            'monto_transaction' => $monto_transaction,

        ];


        \Log::info(json_encode($data));

        //CONSULTA SI ES MINITERMINAL GESTIONADA POR EGLOBALT

        if ($transaction->status == 'iniciated' || $transaction->status == 'error' || $transaction->status == 'error dispositivo') {

            //se agrega la transacción a la cola de reproceso
            $reprocesar_transaccion = \DB::table('transactions_reprocessed_log')->insertGetId(
                [
                    'amount'            => number_format($monto_transaction, 0, '', ''),
                    'atm_id'            => $transaction->atm_id,
                    'atm_transaction_id' => $transaction->atm_transaction_id,
                    'autorized_by_user' => $this->user->id,
                    'owner_id'          => $transaction->owner_id,
                    'status'            => 'pendiente',
                    'transaction_id'    => $transaction->id,
                    'referencia_numero_1'   => $referencia_1,
                    'referencia_numero_2'   => $referencia_2,
                    'request_data'      => json_encode($data),
                    'service_id'        => $service_id,
                    'service_source_id' => $service_source_id,
                ]
            );

            //cambiar el estado de la transacción a procesado = false para que el reproceso pueda efectuarce

            //cambiar a estado devolucion
            \DB::table('transactions')
                ->where('id', $transaction_id)
                ->update(['status' => 'reprocesando', 'processed' => 0]);
        } else {

            if (($transaction->owner_id == 16 || $transaction->owner_id == 21 || $transaction->owner_id == 25) && $transaction->status == 'canceled') {
                if ($this->get_atm_managers($transaction->atm_id) == true) {
                    //se agrega la transacción a la cola de reproceso
                    /*
                    *  ACLARATORIA IMPORTANTE: Para owner 16 (miniterminales)
                    * En la tabla transactions para los servicios de carga de billeteras y giros, el nro de destino se guarda en el campo
                    * 
                    */
                    if (($transaction->service_id == 9 || $transaction->service_id == 7) && $transaction->service_source_id == 0) {
                        $reprocesar_transaccion = \DB::table('transactions_reprocessed_log')->insertGetId(
                            [
                                'amount'            => number_format($monto_transaction, 0, '', ''),
                                'atm_id'            => $transaction->atm_id,
                                'atm_transaction_id' => $transaction->atm_transaction_id,
                                'autorized_by_user' => $this->user->id,
                                'owner_id'          => $transaction->owner_id,
                                'status'            => 'pendiente',
                                'transaction_id'    => $transaction->id,
                                'referencia_numero_1'   => '0984728600',
                                'referencia_numero_2'   => $referencia_1,
                                'request_data'      => json_encode($data),
                                'service_id'        => $service_id,
                                'service_source_id' => $service_source_id,
                            ]
                        );
                    } else {
                        $reprocesar_transaccion = \DB::table('transactions_reprocessed_log')->insertGetId(
                            [
                                'amount'            => number_format($monto_transaction, 0, '', ''),
                                'atm_id'            => $transaction->atm_id,
                                'atm_transaction_id' => $transaction->atm_transaction_id,
                                'autorized_by_user' => $this->user->id,
                                'owner_id'          => $transaction->owner_id,
                                'status'            => 'pendiente',
                                'transaction_id'    => $transaction->id,
                                'referencia_numero_1'   => $referencia_1,
                                'referencia_numero_2'   => $referencia_2,
                                'request_data'      => json_encode($data),
                                'service_id'        => $service_id,
                                'service_source_id' => $service_source_id,
                            ]
                        );
                    }



                    //cambiar el estado de la transacción a procesado = false para que el reproceso pueda efectuarce

                    //cambiar a estado devolucion
                    \DB::table('transactions')
                        ->where('id', $transaction_id)
                        ->update(['status' => 'reprocesando', 'processed' => 0]);
                } else {
                    $response = [];
                    $response['error'] = true;
                    return $response;
                }
            } else {
                $response = [];
                $response['error'] = true;
                return $response;
            }
        }

        //return $transaction;
        $response = [];
        $response['error'] = false;
        return $response;
    }

    /** GET MINITERMINALES  MANAGERS */

    public function get_atm_managers($atm_id)
    {
        $atm = \DB::table('atms')
            ->select('atms.id', 'atms.name', 'manager_id')
            ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'atms.id')
            ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
            ->join('business_groups', 'business_groups.id', '=', 'branches.group_id')
            ->where('atms.id', '=', $atm_id)
            ->whereIn('atms.owner_id', [16, 21, 25])
            ->first();

        if ($atm && $atm->manager_id == 1) {
            return true;
        } else {
            return false;
        }
    }

    /** Estado por atm*/
    public function estadoAtm()
    {
        if (!$this->user->hasAnyAccess('reporting.disponibilidad')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $report = new ReportServices('');
        $result = $report->estadoAtm();
        return view('reporting.index')->with($result);
    }

    /** Estado por atm*/
    public function estadoAtmSearch(Request $request)
    {
        if (!$this->user->hasAnyAccess('reporting.disponibilidad')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = \Request::all();

        if ($request->ajax()) {
            $report = new ReportServices($input);
            $result = $report->estadoAtmDetalle();
            return $result;
        }

        if (isset($input['search']) || isset($input['context']) || isset($input['page'])) {
            $report = new ReportServices($input);
            $result = $report->estadoAtmSearch();
            return view('reporting.index')->with($result);
        }

        if (isset($input['export'])) {
            $report = new ReportServices($input);
            $result = $report->estadoAtmDetalleExport();
            $result = json_decode(json_encode($result), true);
            $i = 0;
            foreach ($result as $r) {
                if ($r['fecha_fin']) {
                    $from_time = strtotime($r['fecha_inicio']);
                    $to_time = strtotime($r['fecha_fin']);
                    $mins = ceil(($to_time - $from_time) / 60) . ' minutos';
                } else {
                    $from_time = strtotime($r['fecha_inicio']);
                    $to_time = time();
                    $mins = ceil(($to_time - $from_time) / 60) . ' minutos';
                }
                array_push($r, $mins);
                $r['transcurrido'] = $r[0];
                unset($r[0]);
                $result[$i] = $r;
                $i++;
            }
            $filename = 'notificaciones_' . time();
            $columnas = [];

            $excel = new ExcelExport($result,$columnas);
            return Excel::download($excel, $filename . '.xls')->send();

            // Excel::create($filename, function ($excel) use ($result) {
            //     $excel->sheet('sheet1', function ($sheet) use ($result) {
            //         $sheet->fromArray($result);
            //     });
            // })->export('xls');
            // exit();
        }
    }

    /** TRANSACTIONS*/
    public function transactionsAmountReports()
    {
        if (!$this->user->hasAnyAccess('reporting', 'transactions_amount')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $report = new ReportServices('');
        $result = $report->transactionsAmountReports();
        return view('reporting.index')->with($result);
    }

    public function transactionsAmountSearch()
    {
        if (!$this->user->hasAnyAccess('reporting', 'transactions_amount')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = \Request::all();
        if (isset($input['search']) || isset($input['context']) || isset($input['page'])) {
            $report = new ReportServices($input);
            $result = $report->transactionsAmountSearch();
            return view('reporting.index')->with($result);
        }

        if (isset($input['download'])) {
            ini_set('max_execution_time', 300);
            $report = new ReportServices($input);
            $result = $report->transactionsSearchExport();
            $result = json_decode(json_encode($result), true);
            $filename = 'transacciones_' . time();
            $columnas = array(
                'id', 'Proveedor', 'Tipo', 'Estado', 'Descripcion', 'Fecha', 'Hora', 'Valor Transaccion', 'Cod. Pago', 'Forma pago',
                'Identificador transaccion', 'Factura Nro', 'Sede', 'Red', 'Ref 1', 'Ref 2', 'Codigo Cajero'
            );

            $excel = new ExcelExport($result,$columnas);
            return Excel::download($excel, $filename . '.xls')->send();

            // Excel::create($filename, function ($excel) use ($result) {
            //     $excel->sheet('sheet1', function ($sheet) use ($result) {
            //         $sheet->rows($result, false);
            //         $sheet->prependRow(array(
            //             'id', 'Proveedor', 'Tipo', 'Estado', 'Descripcion', 'Fecha', 'Hora', 'Valor Transaccion', 'Cod. Pago', 'Forma pago',
            //             'Identificador transaccion', 'Factura Nro', 'Sede', 'Red', 'Ref 1', 'Ref 2', 'Codigo Cajero'
            //         ));
            //     });
            // })->export('xls');
            // exit();
        }
    }

    /** * NOTIFICACIONES*/
    public function dispositivosReports()
    {
        if (!$this->user->hasAccess('reporting')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $report = new ReportServices('');
        $result = $report->dispositivosReports();
        return view('reporting.index')->with($result);
    }

    public function dispositivosSearch()
    {

        if (!$this->user->hasAccess('reporting')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $input = \Request::all();

        if (isset($input['search']) || isset($input['page'])) {
            $report = new ReportServices($input);
            $result = $report->dispositivosSearch();
            return view('reporting.index')->with($result);
        }

        if (isset($input['download'])) {
            $report = new ReportServices($input);
            $result = $report->dispositivosSearchExport();
            $result = json_decode(json_encode($result), true);
            $i = 0;
            foreach ($result as $r) {
                if ($r['fecha_fin']) {
                    $from_time = strtotime($r['fecha_inicio']);
                    $to_time = strtotime($r['fecha_fin']);
                    $mins = ceil(($to_time - $from_time) / 60) . ' minutos';
                } else {
                    $from_time = strtotime($r['fecha_inicio']);
                    $to_time = time();
                    $mins = ceil(($to_time - $from_time) / 60) . ' minutos';
                }
                array_push($r, $mins);
                $r['transcurrido'] = $r[0];
                unset($r[0]);
                $result[$i] = $r;
                $i++;
            }
            $filename = 'dispositivos_' . time();
            $columnas = [];

            $excel = new ExcelExport($result,$columnas);
            return Excel::download($excel, $filename . '.xls')->send();
            // Excel::create($filename, function ($excel) use ($result) {
            //     $excel->sheet('sheet1', function ($sheet) use ($result) {
            //         $sheet->fromArray($result);
            //     });
            // })->export('xls');
            // exit();
        }
    }

    /** TRANSACTIONS*/
    public function transactionsVueltoReports()
    {
        if (!$this->user->hasAnyAccess('reporting', 'ticketea', 'reporting_mini_terminal')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $report = new ReportServices('');
        $result = $report->transactionsVueltoReports();
        return view('reporting.index')->with($result);
    }

    public function transactionVueltoSearch()
    {
        if (!$this->user->hasAnyAccess('reporting', 'ticketea', 'reporting_mini_terminal')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = \Request::all();
        if (isset($input['search']) || isset($input['context']) || isset($input['page'])) {
            $report = new ReportServices($input);
            $result = $report->transactionsVueltoSearch();
            if ($result) {
                return view('reporting.index')->with($result);
            } else {
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();
            }
        }

        if (isset($input['download'])) {
            ini_set('max_execution_time', 300);
            $report = new ReportServices($input);
            $result = $report->transactionsVueltoSearchExport();
            $result = json_decode(json_encode($result), true);
            $filename = 'transacciones_vueltos_' . time();
            $columnas = array(
                'id', 'Proveedor', 'Tipo', 'Estado', 'Descripcion', 'Fecha', 'Hora', 'Valor Transaccion', 'Cod. Pago', 'Forma pago',
                'Identificador transaccion', 'Sede', 'Codigo Cajero', 'Valor A Pagar', 'Valor Recibido', 'Valor Entregado', 'No Entregado'
            );

            if ($result) {
                $excel = new ExcelExport($result,$columnas);
                return Excel::download($excel, $filename . '.xls')->send();
                // Excel::create($filename, function ($excel) use ($result) {
                //     $excel->sheet('sheet1', function ($sheet) use ($result) {
                //         $sheet->rows($result, false);
                //         $sheet->prependRow(array(
                //             'id', 'Proveedor', 'Tipo', 'Estado', 'Descripcion', 'Fecha', 'Hora', 'Valor Transaccion', 'Cod. Pago', 'Forma pago',
                //             'Identificador transaccion', 'Sede', 'Codigo Cajero', 'Valor A Pagar', 'Valor Recibido', 'Valor Entregado', 'No Entregado'
                //         ));
                //     });
                // })->export('xls');
                // exit();
            } else {
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();
            }
        }
    }

    /** TRANSACTIONS*/
    public function transactionsAtmReports()
    {
        if (!$this->user->hasAnyAccess('reporting.transactions_atm')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $report = new ReportServices('');
        $result = $report->transactionsAtmReports();
        return view('reporting.index')->with($result);
    }

    public function transactionsAtmSearch()
    {
        if (!$this->user->hasAnyAccess('reporting.transactions_atm')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = \Request::all();
        if (isset($input['search']) || isset($input['context']) || isset($input['page'])) {
            $report = new ReportServices($input);
            $result = $report->transactionsAtmSearch();
            return view('reporting.index')->with($result);
        }

        if (isset($input['download'])) {
            ini_set('max_execution_time', 300);
            $report = new ReportServices($input);
            $result = $report->transactionsSearchExport();
            $result = json_decode(json_encode($result), true);
            $filename = 'transacciones_' . time();
            $columnas = array(
                'id', 'Proveedor', 'Tipo', 'Estado', 'Descripcion', 'Fecha', 'Hora', 'Valor Transaccion', 'Cod. Pago', 'Forma pago',
                'Identificador transaccion', 'Factura Nro', 'Sede', 'Red', 'Ref 1', 'Ref 2', 'Codigo Cajero'
            );

            $excel = new ExcelExport($result,$columnas);
            return Excel::download($excel, $filename . '.xls')->send();

            // Excel::create($filename, function ($excel) use ($result) {
            //     $excel->sheet('sheet1', function ($sheet) use ($result) {
            //         $sheet->rows($result, false);
            //         $sheet->prependRow(array(
            //             'id', 'Proveedor', 'Tipo', 'Estado', 'Descripcion', 'Fecha', 'Hora', 'Valor Transaccion', 'Cod. Pago', 'Forma pago',
            //             'Identificador transaccion', 'Factura Nro', 'Sede', 'Red', 'Ref 1', 'Ref 2', 'Codigo Cajero'
            //         ));
            //     });
            // })->export('xls');
            // exit();
        }
    }

    /** TRANSACTIONS*/
    public function denominacionesAmountReports()
    {
        if (!$this->user->hasAnyAccess('reporting.denominaciones')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $report = new ReportServices('');
        $result = $report->denominacionesAmountReports();
        return view('reporting.index')->with($result);
    }

    public function denominacionesAmountSearch()
    {
        if (!$this->user->hasAnyAccess('reporting.denominaciones')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = \Request::all();
        if (isset($input['search']) || isset($input['context']) || isset($input['page'])) {
            $report = new ReportServices($input);
            $result = $report->denominacionesAmountSearch();
            return view('reporting.index')->with($result);
        }

        if (isset($input['download'])) {
            ini_set('max_execution_time', 300);
            $report = new ReportServices($input);
            $result = $report->denominacionesAmountSearchExport();
            $result = json_decode(json_encode($result), true);
            $filename = 'transacciones_' . time();
            $columnas = array(
                'id', 'Proveedor', 'Tipo', 'Estado', 'Descripcion', 'Fecha', 'Hora', 'Valor Transaccion', 'Cod. Pago', 'Forma pago',
                'Identificador transaccion', 'Factura Nro', 'Sede', 'Red', 'Ref 1', 'Ref 2', 'Codigo Cajero'
            );

            $excel = new ExcelExport($result,$columnas);
            return Excel::download($excel, $filename . '.xls')->send();
            // Excel::create($filename, function ($excel) use ($result) {
            //     $excel->sheet('sheet1', function ($sheet) use ($result) {
            //         $sheet->rows($result, false);
            //         $sheet->prependRow(array(
            //             'id', 'Proveedor', 'Tipo', 'Estado', 'Descripcion', 'Fecha', 'Hora', 'Valor Transaccion', 'Cod. Pago', 'Forma pago',
            //             'Identificador transaccion', 'Factura Nro', 'Sede', 'Red', 'Ref 1', 'Ref 2', 'Codigo Cajero'
            //         ));
            //     });
            // })->export('xls');
            // exit();
        }
    }
    public function transactionsVueltoCorrectoReports()
    {
        if (!$this->user->hasAnyAccess('reporting', 'reporting.vuelto_entregado')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $report = new ReportServices('');
        $result = $report->transactionsVueltoCorrectoReports();
        return view('reporting.index')->with($result);
    }

    public function transactionVueltoCorrectoSearch()
    {
        if (!$this->user->hasAnyAccess('reporting', 'reporting.vuelto_entregado')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = \Request::all();
        if (isset($input['search']) || isset($input['context']) || isset($input['page'])) {
            $report = new ReportServices($input);
            $result = $report->transactionsVueltoCorrectoSearch();
            if ($result) {
                return view('reporting.index')->with($result);
            } else {
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();
            }
        }

        if (isset($input['download'])) {
            ini_set('max_execution_time', 300);
            $report = new ReportServices($input);
            $result = $report->transactionsVueltoCorrectoSearchExport();
            $result = json_decode(json_encode($result), true);
            $filename = 'transacciones_vueltos_correctos_' . time();
            $columnas = array(
                'id', 'Proveedor', 'Tipo', 'Estado', 'Descripcion', 'Fecha', 'Hora', 'Valor Transaccion', 'Cod. Pago', 'Forma pago',
                'Identificador transaccion', 'Sede', 'Codigo Cajero', 'Valor A Pagar', 'Valor Recibido', 'Valor Entregado'
            );

            if ($result) {

                $excel = new ExcelExport($result,$columnas);
                return Excel::download($excel, $filename . '.xls')->send();
                // Excel::create($filename, function ($excel) use ($result) {
                //     $excel->sheet('sheet1', function ($sheet) use ($result) {
                //         $sheet->rows($result, false);
                //         $sheet->prependRow(array(
                //             'id', 'Proveedor', 'Tipo', 'Estado', 'Descripcion', 'Fecha', 'Hora', 'Valor Transaccion', 'Cod. Pago', 'Forma pago',
                //             'Identificador transaccion', 'Sede', 'Codigo Cajero', 'Valor A Pagar', 'Valor Recibido', 'Valor Entregado'
                //         ));
                //     });
                // })->export('xls');
                // exit();
            } else {
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();
            }
        }
    }

    public function getServiceRequestParam(Request $request)
    {
        $service_source_id = $request->get('id');
        if ($service_source_id <> 1) {
            $servicios = \DB::table('servicios_x_marca')->where('service_source_id', $service_source_id)->get();
        } else {
            $servicios = \DB::table('service_provider_products')->where('service_provider_id', $service_source_id)->get();
        }

        $data = [
            /*'0' => [
                'id' => '0',
                'text' => '-- Todos --'
            ]*/];

        foreach ($servicios as $serviceRequestId => $service) {
            if ($service_source_id <> 1) {
                $descripcion = $service->descripcion;
                $id_servicio = $service->service_id;
            } else {
                $descripcion = $service->description;
                $id_servicio = $service->id;
            }

            $valor = [];
            $valor['id'] = $id_servicio;
            $valor['text'] = $descripcion;
            $data[] = $valor;
        }

        return $data;
    }

    /** ESTADO CONTABLE*/
    public function estadoContableReports()
    {
        if (!$this->user->hasAnyAccess('reporting_mini_terminal')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $report = new ReportServices('');
        $result = $report->estadoContableReports();
        return view('reporting.index')->with($result);
    }

    public function estadoContableSearch(Request $request)
    {
        if (!$this->user->hasAnyAccess('reporting_mini_terminal')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = \Request::all();
        if (isset($input['search']) || isset($input['context']) || isset($input['page'])) {
            $report = new ReportServices($input);
            $result = $report->estadoContableSearch($request);
            //dd($result);
            return view('reporting.index')->with($result);
        }

        if (isset($input['download'])) {
            ini_set('max_execution_time', 300);
            $report = new ReportServices($input);
            $result = $report->estadoContableSearchExport();
            $result = json_decode(json_encode($result), true);
            $columnas = array(
                'Fecha', 'Concepto', 'Debe', 'Haber', 'Saldo'
            );
            if ($result) {
                $filename = 'transacciones_' . time();
                $excel = new ExcelExport($result['transactions'],$columnas);
                return Excel::download($excel, $filename . '.xls')->send();
                // Excel::create($filename, function ($excel) use ($result) {
                //     $excel->sheet('sheet1', function ($sheet) use ($result) {
                //         $sheet->rows($result['transactions'], false);
                //         $sheet->prependRow(array(
                //             'Fecha', 'Concepto', 'Debe', 'Haber', 'Saldo'
                //         ));
                //     });
                // })->export('xls');
                // exit();
            } else {
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();
            }
        }
    }

    /** RESUMEN MINITERMINALES*/
    public function resumenMiniterminalesReports(Request $request)
    {
        if (!$this->user->hasAnyAccess('reporting_resumen_mini_terminal')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $report = new ReportServices('');
        $result = $report->resumenMiniterminalesReports($request);
        return view('reporting.index')->with($result);
    }

    public function resumenMiniterminalesSearch(Request $request)
    {
        if (!$this->user->hasAnyAccess('reporting_resumen_mini_terminal')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = \Request::all();
        if (isset($input['download'])) {
            ini_set('max_execution_time', 300);
            $report = new ReportServices($input);
            $result = $report->resumenMiniterminalesSearchExport();
            $result = json_decode(json_encode($result), true);
            $columna1 = array(
                'Id', 'Ruc', 'Grupo', 'Total Transaccionado', 'Total Depositado', 'Total Reversado', 'Total Cashout', 'Total Cuotas', 'Saldo', 'Estado'
            );
            $columna2 = array(
                'Sucursal', 'Total Transaccionado', 'Total Depositado', 'Total Reversado', 'Total Cashout', 'Saldo', 'Estado'
            );

            if ($result) {
                $filename = 'resumen_miniterminales' . time();
                $excel = new ExcelExport($result['transacciones_groups'],$columna1,$result['transacciones'],$columna2);
                return Excel::download($excel, $filename . '.xls')->send();

                // Excel::create($filename, function ($excel) use ($result) {
                //     $excel->sheet('sheet1', function ($sheet) use ($result) {
                //         $sheet->rows($result['transacciones_groups'], false);
                //         $sheet->prependRow(array(
                //             'Id', 'Ruc', 'Grupo', 'Total Transaccionado', 'Total Depositado', 'Total Reversado', 'Total Cashout', 'Total Cuotas', 'Saldo', 'Estado'
                //         ));
                //     });
                //     $excel->sheet('Por Sucursal', function ($sheet) use ($result) {
                //         $sheet->rows($result['transacciones'], false);
                //         $sheet->prependRow(array(
                //             'Sucursal', 'Total Transaccionado', 'Total Depositado', 'Total Reversado', 'Total Cashout', 'Saldo', 'Estado'
                //         ));
                //     });
                // })->export('xls');
                // exit();
            } else {
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();
            }
        }

        if (isset($input['search']) || isset($input['context']) || isset($input['page'])) {
            $report = new ReportServices($input);
            $result = $report->resumenMiniterminalesSearch($request);
            return view('reporting.index')->with($result);
        }
    }

    /** ESTADO CONTABLE DETALLADO*/
    public function resumenDetalladoReports(Request $request)
    {
        if (!$this->user->hasAnyAccess('reporting_resumen_mini_terminal')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $report = new ReportServices('');
        $result = $report->resumenDetalladoReports($request);
        return view('reporting.index')->with($result);
    }

    public function resumenDetalladoSearch(Request $request)
    {
        if (!$this->user->hasAnyAccess('reporting_resumen_mini_terminal')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $input = \Request::all();
        if (isset($input['download'])) {
            ini_set('max_execution_time', 300);
            $report = new ReportServices($input);
            $result = $report->resumenDetalladoSearchExport();
            $result = json_decode(json_encode($result), true);
            $columna1 = array(
                'Id', 'Ruc', 'Grupo', 'Total Transaccionado', 'Total Paquetigo', 'Total Personal', 'Total Claro', 'Total Pago Cashout', 'Total Depositado', 'Total Reversado', 'Total Cashout', 'Saldo'
            );
            $columna2 = array(
                'Total Transaccionado', 'Sucursal'
            );

            if ($result) {
                $filename = 'resumen_miniterminales' . time();
                $excel = new ExcelExport($result['transacciones_groups'],$columna1,$result['transacciones'],$columna2);
                return Excel::download($excel, $filename . '.xls')->send();

                // Excel::create($filename, function ($excel) use ($result) {
                //     $excel->sheet('sheet1', function ($sheet) use ($result) {
                //         $sheet->rows($result['transacciones_groups'], false);
                //         $sheet->prependRow(array(
                //             'Id', 'Ruc', 'Grupo', 'Total Transaccionado', 'Total Paquetigo', 'Total Personal', 'Total Claro', 'Total Pago Cashout', 'Total Depositado', 'Total Reversado', 'Total Cashout', 'Saldo'
                //         ));
                //     });
                //     $excel->sheet('Por Sucursal', function ($sheet) use ($result) {
                //         $sheet->rows($result['transacciones'], false);
                //         $sheet->prependRow(array(
                //             'Total Transaccionado', 'Sucursal'
                //         ));
                //     });
                // })->export('xls');
                // exit();
            } else {
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();
            }
        }

        if (isset($input['search']) || isset($input['context']) || isset($input['page'])) {
            $report = new ReportServices($input);
            $result = $report->resumenDetalladoSearch($request);
            return view('reporting.index')->with($result);
        }
    }

    public function getBranchesfroGroups($group_id, $day)
    {
        if (!$this->user->hasAnyAccess('reporting_resumen_mini_terminal')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $report = new ReportServices('');
        $result = $report->getBranchfroGroup($group_id, $day);

        return $result;
    }

    public function getCuotasforGroups($group_id)
    {
        if (!$this->user->hasAnyAccess('reporting_resumen_mini_terminal')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $report = new ReportServices('');
        $result = $report->getCuotasForGroups($group_id);

        return $result;
    }

    /** BOLETAS DEPOSITOS*/
    public function boletasDepositosReports(Request $request)
    {
        if (!$this->user->hasAnyAccess('reporting_boleta_depositos')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $report = new ReportServices('');
        $result = $report->boletaDepositosReports($request);
        return view('reporting.index')->with($result);
    }

    public function boletasDepositosSearch(Request $request)
    {
        if (!$this->user->hasAnyAccess('reporting_boleta_depositos')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = \Request::all();

        if (isset($input['download'])) {
            ini_set('max_execution_time', 300);
            $report = new ReportServices($input);
            $result = $report->boletaDepositosSearchExport();
            $result = json_decode(json_encode($result), true);
            $columnas = array(
                'ID', 'Fecha', 'Usuario', 'Concepto', 'Banco', 'Cuenta Bancaria', 'Nro Boleta', 'Monto', 'Estado', 'Modificado por', 'Fecha Modificacion', 'Mensaje'
            );

            if ($result) {
                $filename = 'depositos_boletas_' . time();
                $excel = new ExcelExport($result['transactions'],$columnas);
                return Excel::download($excel, $filename . '.xls')->send();

                // Excel::create($filename, function ($excel) use ($result) {
                //     $excel->sheet('sheet1', function ($sheet) use ($result) {
                //         $sheet->rows($result['transactions'], false);
                //         $sheet->prependRow(array(
                //             'ID', 'Fecha', 'Usuario', 'Concepto', 'Banco', 'Cuenta Bancaria', 'Nro Boleta', 'Monto', 'Estado', 'Modificado por', 'Fecha Modificacion', 'Mensaje'
                //         ));
                //     });
                // })->export('xls');
                // exit();
            } else {
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();
            }
        }

        if (isset($input['search']) || isset($input['context']) || isset($input['page'])) {
            $report = new ReportServices($input);
            $result = $report->boletaDepositosSearch($request);
            return view('reporting.index')->with($result);
        }
    }

    /** COMISIONES*/
    public function comisionesReports()
    {
        /*if (!$this->user->hasAnyAccess('reporte.comisiones')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }*/
        $report = new ReportServices('');
        $result = $report->comisionesReports();
        return view('reporting.index')->with($result);
    }

    public function comisionesSearch(Request $request)
    {
        /* if (!$this->user->hasAnyAccess('reporte.comisiones')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }*/

        $input = \Request::all();
        if (isset($input['search']) || isset($input['context']) || isset($input['page'])) {
            $report = new ReportServices($input);
            $result = $report->comisionesSearch($request);
            if ($result) {
                return view('reporting.index')->with($result);
            } else {
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();
            }
        }

        if (isset($input['download'])) {
            ini_set('max_execution_time', 300);
            $report = new ReportServices($input);
            $result = $report->comisionesSearchExport();
            $result = json_decode(json_encode($result), true);
            $columnas = array(
                'ATM', 'Tipo', 'Servicio', 'Valor Transacción', 'Comisión', '% Comisión'
            );

            if ($result) {
                $filename = 'comisiones_transacciones_' . time();
                $excel = new ExcelExport($result,$columnas);
                return Excel::download($excel, $filename . '.xls')->send();
                // Excel::create($filename, function ($excel) use ($result) {
                //     $excel->sheet('sheet1', function ($sheet) use ($result) {
                //         $sheet->rows($result, false);
                //         $sheet->prependRow(array(
                //             'ATM', 'Tipo', 'Servicio', 'Valor Transacción', 'Comisión', '% Comisión'
                //         ));
                //     });
                // })->export('xls');
                // exit();
            } else {
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();
            }
        }
    }

    /** VENTAS MINITERMINALES*/
    public function salesReports()
    {
        /*if (!$this->user->hasAnyAccess('reporte.sales')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }*/
        $report = new ReportServices('');
        $result = $report->salesReports();
        return view('reporting.index')->with($result);
    }

    public function salesSearch(Request $request)
    {
        /*if (!$this->user->hasAnyAccess('reporte.sales')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }*/

        $input = \Request::all();
        if (isset($input['search']) || isset($input['context']) || isset($input['page'])) {
            $report = new ReportServices($input);
            $result = $report->salesSearch($request);
            if ($result) {
                return view('reporting.index')->with($result);
            } else {
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();
            }
        }

        if (isset($input['download'])) {
            ini_set('max_execution_time', 300);
            $report = new ReportServices($input);
            $result = $report->salesSearchExport();
            $result = json_decode(json_encode($result), true);
            $columnas = array(
                'ID', 'Grupo', 'Monto', 'Fecha', 'ID Ondanet', 'Nro Venta', 'Estado', 'Monto por cobrar'
            );

            if ($result) {
                $filename = 'ventas_miniterminales' . time();

                $excel = new ExcelExport($result['transactions'],$columnas);
                return Excel::download($excel, $filename . '.xls')->send();

                // Excel::create($filename, function ($excel) use ($result) {
                //     $excel->sheet('sheet1', function ($sheet) use ($result) {
                //         $sheet->rows($result['transactions'], false);
                //         $sheet->prependRow(array(
                //             'ID', 'Grupo', 'Monto', 'Fecha', 'ID Ondanet', 'Nro Venta', 'Estado', 'Monto por cobrar'
                //         ));
                //     });
                // })->export('xls');
                // exit();
            } else {
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();
            }
        }
    }

    /** VENTAS MINITERMINALES*/
    public function cobranzasReports()
    {
        /*if (!$this->user->hasAnyAccess('reporte.sales')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }*/
        $report = new ReportServices('');
        $result = $report->cobranzasReports();
        return view('reporting.index')->with($result);
    }

    public function cobranzasSearch(Request $request)
    {
        /*if (!$this->user->hasAnyAccess('reporte.sales')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }*/

        $input = \Request::all();
        if (isset($input['search']) || isset($input['context']) || isset($input['page'])) {
            $report = new ReportServices($input);
            $result = $report->cobranzasSearch($request);
            if ($result) {
                return view('reporting.index')->with($result);
            } else {
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();
            }
        }

        /*if(isset($input['download'])){
            ini_set('max_execution_time', 300);
            $report = new ReportServices($input);
            $result = $report->comisionesSearchExport();
            $result = json_decode(json_encode($result),true);
            if($result){
                $filename = 'comisiones_transacciones_'.time();
                Excel::create($filename, function($excel) use ($result) {
                    $excel->sheet('sheet1', function($sheet) use ($result) {
                        $sheet->rows($result,false);
                        $sheet->prependRow(array(
                            'ATM', 'Tipo','Servicio','Valor Transacción','Comisión','% Comisión'
                        ));        
                    });
                })->export('xls');
                exit();
            }else{
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();   
            }
            
        }*/

        if (isset($input['download'])) {
            ini_set('max_execution_time', 300);
            $report = new ReportServices($input);
            $result = $report->cobranzasSearchExport();
            $result = json_decode(json_encode($result), true);
            $columnas = array(
                'ID', 'description', 'boleta_numero', 'created_at', 'operation_id', 'recibo_nro', 'monto', 'ventas_cobradas'
            );

            if ($result) {
                $filename = 'comisiones_transacciones_' . time();

                $excel = new ExcelExport($result,$columnas);
                return Excel::download($excel, $filename . '.xls')->send();

                // Excel::create($filename, function ($excel) use ($result) {
                //     $excel->sheet('sheet1', function ($sheet) use ($result) {
                //         $sheet->rows($result, false);
                //         $sheet->prependRow(array(
                //             'ID', 'description', 'boleta_numero', 'created_at', 'operation_id', 'recibo_nro', 'monto', 'ventas_cobradas'
                //         ));
                //     });
                // })->export('xls');
                // exit();
            } else {
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();
            }
        }
    }

    /**
     * SALDOS EN LINEA
     * Reporte de saldos contables
     */

    public function saldos_control_contable()
    {
        $target = 'Saldos Contable';
        return view('reporting.index', compact('target'));
    }

    public function saldos_control_contable_search()
    {
        if (!$this->user->hasAccess('reporting')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $input = \Request::all();

        if (isset($input['download'])) {
            $report = new ReportServices($input);
            $result = $report->saldos_control_contable_export();
            $result = json_decode(json_encode($result), true);
            $filename = 'control_contable_' . time();
            $columnas = array(
                'ATM', 'Cassettes', 'Hoppers', 'Box', 'Purga', 'Total', 'Hora Consulta', 'Fecha'
            );

            $excel = new ExcelExport($result,$columnas);
            return Excel::download($excel, $filename . '.xls')->send();

            // Excel::create($filename, function ($excel) use ($result) {
            //     $excel->sheet('sheet1', function ($sheet) use ($result) {
            //         //$sheet->fromArray($result);
            //         $sheet->rows($result, false);
            //         $sheet->getStyle('B1:B9999')->getNumberFormat()->setFormatCode('#,##0');
            //         $sheet->getStyle('C1:C9999')->getNumberFormat()->setFormatCode('#,##0');
            //         $sheet->getStyle('D1:D9999')->getNumberFormat()->setFormatCode('#,##0');
            //         $sheet->getStyle('E1:E9999')->getNumberFormat()->setFormatCode('#,##0');
            //         $sheet->getStyle('F1:F9999')->getNumberFormat()->setFormatCode('#,##0');
            //         //set colum names
            //         $sheet->prependRow(array(
            //             'ATM', 'Cassettes', 'Hoppers', 'Box', 'Purga', 'Total', 'Hora Consulta', 'Fecha'
            //         ));
            //     });
            // })->export('xls');
            // exit();
        }

        $report = new ReportServices($input);
        $result = $report->saldos_control_contable_search();
        return view('reporting.index')->with($result);
    }

    /**ESTADOS MINITERMINALES*/

    public function atms_bloqueadas(Request $request)
    {
        try {

            $input = \Request::all();

            $estado = ($request->get('estados')) ? $request->get('estados') : 0;

            $estados = array('0' => 'Todos', '1' => 'Activos', '2' => 'Bloqueados', '3' => 'Inactivos');

            if ($request->get('estados') <> 0) {

                if ($request->get('estados') == '1') {
                    $where = "block_type_id = 0 and atms.deleted_at is null";
                }

                if ($request->get('estados') == '2') {
                    $where = "block_type_id != 0 and atms.deleted_at is null";
                }

                if ($request->get('estados') == '3') {
                    $where = "atms.deleted_at is not null";
                }

                $atms = \DB::table('atms')
                    //->select(['atms.id', 'name', 'last_request_at', 'block_type.description'])
                    ->selectRaw("atms.id, name, last_request_at, block_type.description, atms.deleted_at as eliminado, block_type_id")
                    //->select('block_type_id')
                    ->join('block_type', 'block_type.id', '=', 'atms.block_type_id')
                    ->whereIn('owner_id', [16, 21, 25])
                    //->where('atms.deleted_at', null)
                    ->whereRaw("$where")
                    /*->where('cuotas_alquiler.fecha_vencimiento','<',$date)
                ->where('cuotas_alquiler.saldo_cuota','<>',0)*/
                    ->groupBy('atms.id', 'block_type.description')
                    ->orderBy('atms.id', 'asc')
                    ->get();
            } else {

                $atms = \DB::table('atms')
                    //->select(['atms.id', 'name', 'last_request_at', 'block_type.description'])
                    ->selectRaw("atms.id, name, last_request_at, block_type.description, block_type_id, atms.deleted_at as eliminado")
                    //->select('block_type_id')
                    ->join('block_type', 'block_type.id', '=', 'atms.block_type_id')
                    ->join('balance_atms', 'atms.id', '=', 'balance_atms.atm_id')
                    ->whereIn('owner_id', [16, 21, 25])
                    ->where('atms.deleted_at', null)
                    /*->where('cuotas_alquiler.fecha_vencimiento','<',$date)
                ->where('cuotas_alquiler.saldo_cuota','<>',0)*/
                    ->groupBy('atms.id', 'block_type.description')
                    ->orderBy('atms.id', 'asc')
                    ->get();
            }

            if (isset($input['download'])) {
                ini_set('max_execution_time', 300);
                foreach ($atms as $atm) {
                    $atm->block_type_id = '';
                }
                $result = json_decode(json_encode($atms), true);
                $filename = 'estados_atms_' . time();
                $columnas = array(
                    'ID', 'ATM', 'Ultimo Uso', 'Estado'
                );

                $excel = new ExcelExport($result,$columnas);
                return Excel::download($excel, $filename . '.xls')->send();

                // Excel::create($filename, function ($excel) use ($result) {
                //     $excel->sheet('sheet1', function ($sheet) use ($result) {
                //         $sheet->rows($result, false);
                //         $sheet->prependRow(array(
                //             'ID', 'ATM', 'Ultimo Uso', 'Estado'
                //         ));
                //     });
                // })->export('xls');
                // exit();
            }
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de estados de Miniterminales bloqueadas: " . $e);
        }
        $target = 'Bloqueados';
        return view('reporting.index', compact('target', 'atms', 'estados', 'estado'));
    }

    public function get_atm_bloqueos($atm_id)
    {

        $atms = \DB::table('historial_bloqueos')
            ->selectRaw("atm_id, saldo_pendiente, created_at, bloqueado, block_type_id, description")
            ->join('block_type', 'block_type.id', '=', 'historial_bloqueos.block_type_id')
            ->where('atm_id', $atm_id)
            ->orderBy('historial_bloqueos.id', 'DESC')
            ->take(10)
            ->get();

        $details = '';
        foreach ($atms as $atm) {

            $atm->created_at = date('d/m/Y H:i:s', strtotime($atm->created_at));

            $details .= '<tr>
            <td>' . $atm->created_at . '</td>
            <td>' . $atm->saldo_pendiente . '</td>
            <td>' . $atm->description . '</td>
            </tr>';
        }

        return $details;
    }
    /**
     * Reportes de transacciones de PDV - TDP | para ventas atravez de la aplicacin DA
     *  
     */

    public function dapdv_transactions($atm_id, Request $request)
    {
        $input = \Request::all();
        $decodec_atm_id = base64_decode($atm_id);

        if (isset($input['download'])) {
            $report = new ReportServices($input);
            $result = $report->get_pdv_transactions_list_export($decodec_atm_id, $atm_id);
            $result = json_decode(json_encode($result), true);
            $filename = 'reportes_pdv_' . time();
            $columnas = array(
                'ID', 'Tipo', 'Proveedor', 'Estado', 'Fecha', 'Monto', 'Identificador Debito', 'Identificador Credito', 'Sede', 'Ref1', 'Ref2'
            );

            $excel = new ExcelExport($result,$columnas);
            return Excel::download($excel, $filename . '.xls')->send();

            // Excel::create($filename, function ($excel) use ($result) {
            //     $excel->sheet('sheet1', function ($sheet) use ($result) {
            //         //$sheet->fromArray($result);
            //         $sheet->rows($result, false);
            //         $sheet->getStyle('F1:F9999')->getNumberFormat()->setFormatCode('#,##0');
            //         //set colum names
            //         $sheet->prependRow(array(
            //             'ID', 'Tipo', 'Proveedor', 'Estado', 'Fecha', 'Monto', 'Identificador Debito', 'Identificador Credito', 'Sede', 'Ref1', 'Ref2'
            //         ));
            //     });
            // })->export('xls');
            // exit();
        }

        $report = new ReportServices($input);
        $result = $report->get_pdv_transactions_list_search($decodec_atm_id, $atm_id);
        if ($result['error'] == false) {
            return view('reporting.index_pdv')->with($result);
        } else {
            return ('Acceso negado');
        }
    }

    /** EFECTIVIDAD*/

    public function efectividad()
    {
        if (!$this->user->hasAnyAccess('reporting')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $report = new ReportServices('');
        $result = $report->efectividad();
        return view('reporting.index')->with($result);
    }

    public function efectividadSearch(Request $request)
    {
        if (!$this->user->hasAnyAccess('reporting')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = \Request::all();
        //dd($input);
        if ($request->ajax()) {
            $report = new ReportServices($input);
            $result =  $report->statusDetalle();
            return $result;
        }

        if (isset($input['search']) || isset($input['context']) || isset($input['page'])) {
            $report = new ReportServices($input);
            $result = $report->efectividadSearch();
            return view('reporting.index')->with($result);
        }

        if (isset($input['download'])) {
            ini_set('max_execution_time', 300);
            $report = new ReportServices($input);
            $result = $report->efectividadSearchExport();
            $result = json_decode(json_encode($result), true);
            $filename = 'efectividad_' . time();
            $columnas = array(
                'Status', 'Servicio', 'Descripcion', 'Cantidad', 'Monto'
            );

            $excel = new ExcelExport($result['transactions'],$columnas);
            return Excel::download($excel, $filename . '.xls')->send();

            // Excel::create($filename, function ($excel) use ($result) {
            //     $excel->sheet('sheet1', function ($sheet) use ($result) {
            //         $sheet->rows($result['transactions'], false);
            //         $sheet->prependRow(array(
            //             'Status', 'Servicio', 'Descripcion', 'Cantidad', 'Monto'
            //         ));
            //     });
            // })->export('xls');
            // exit();
        }
    }

    public function getStatusInfoDetails($id)
    {
        if (!$this->user->hasAccess('reporting')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        // $transaction_details = \DB::table('transactions_movements')
        //     ->join('atms_parts','atms_parts.id','=','transactions_movements.atms_parts_id')
        //     ->where('transactions_id','=',$id)
        //     ->get();
        $transaction_details = \DB::table('transactions')
            ->select(\DB::raw(
                " 
            transactions.id as transaction_id,
            points_of_sale.description as sede,
            service_providers.name as provider,
            COALESCE(service_provider_products.description, 'Otros') as servicio,
            transactions.status, 
            transactions.status_description,
            transactions.created_at,
            transactions.amount as monto, 
            referencia_numero_1,
            referencia_numero_2"
            ))
            ->join('points_of_sale', 'points_of_sale.atm_id', '=', 'transactions.atm_id')
            ->leftjoin('service_provider_products', 'service_provider_products.id', '=', 'transactions.service_id')
            ->leftjoin('service_providers', 'service_providers.id', '=', 'service_provider_products.service_provider_id')
            ->leftjoin('transaction_tickets', 'transaction_tickets.transaction_id', '=', 'transactions.id')
            ->leftjoin('transactions_x_payments', 'transactions.id', '=', 'transactions_x_payments.transactions_id')
            ->leftjoin('payments', 'payments.id', '=', 'transactions_x_payments.payments_id')
            ->leftjoin('branches', 'branches.id', '=', 'points_of_sale.branch_id')
            ->where('transactions_id', '=', $id)
            //->whereRaw("$where")
            ->orderBy('transactions.status', 'ASC')
            ->orderBy('transactions.status', 'ASC')
            ->orderBy('service_provider_products.description', 'ASC')
            ->get();


        $details = '';
        foreach ($transaction_details as $transaction_detail) {
            $details .= '<tr><td style="display:none;"></td>
              <td style="display:none;"></td>
              <td>' . $transaction_detail->transaction_id . '</td>
              <td>' . $transaction_detail->sede . '</td>
              <td>' . $transaction_detail->provider . '</td>
              <td>' . $transaction_detail->servicio . '</td>
              <td>' . $transaction_detail->status . '</td>
              <td>' . $transaction_detail->status_description . '</td>
              <td>' . $transaction_detail->created_at . '</td>
              <td>' . $transaction_detail->status . '</td>
              <td>' . number_format($transaction_detail->monto) . '</td>
              <td>' . $transaction_detail->referencia_numero_1 . '</td>
              <td>' . $transaction_detail->referencia_numero_2 . '</td></tr>';
        }

        return $details;
    }

    /** Reporte de Depositos Cuotas **/
    public function depositosCuotasReports(Request $request)
    {
        if (!$this->user->hasAnyAccess('reporting_depositos_cuotas')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $report = new ReportServices('');
        $result = $report->depositosCuotasReports($request);
        return view('reporting.index')->with($result);
    }

    public function depositosCuotasSearch(Request $request)
    {
        if (!$this->user->hasAnyAccess('reporting_depositos_cuotas')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = \Request::all();

        if (isset($input['search']) || isset($input['context']) || isset($input['page'])) {
            $report = new ReportServices($input);
            $result = $report->depositosCuotasSearch($request);
            return view('reporting.index')->with($result);
        }

        if (isset($input['download'])) {
            ini_set('max_execution_time', 300);
            $report = new ReportServices($input);
            $result = $report->depositosCuotasSearchExport();
            $result = json_decode(json_encode($result), true);
            $columnas = array(
                'ID', 'Fecha', 'Usuario', 'Concepto', 'Banco', 'Cuenta Bancaria', 'Nro Boleta', 'Monto', 'Estado', 'Modificado por', 'Fecha Modificacion', 'Mensaje'
            );

            if ($result) {
                $filename = 'depositos_boletas_' . time();

                $excel = new ExcelExport($result['transactions'],$columnas);
                return Excel::download($excel, $filename . '.xls')->send();

                // Excel::create($filename, function ($excel) use ($result) {
                //     $excel->sheet('sheet1', function ($sheet) use ($result) {
                //         $sheet->rows($result['transactions'], false);
                //         $sheet->prependRow(array(
                //             'ID', 'Fecha', 'Usuario', 'Concepto', 'Banco', 'Cuenta Bancaria', 'Nro Boleta', 'Monto', 'Estado', 'Modificado por', 'Fecha Modificacion', 'Mensaje'
                //         ));
                //     });
                // })->export('xls');
                // exit();
            } else {
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();
            }
        }
    }

    public function comprobante_cuota($recibo_id)
    {
        if (!$this->user->hasAnyAccess('reporting_depositos_cuotas')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if (!is_numeric($recibo_id)) {
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        //dd($recibo_id);
        $recibo = \DB::table('mt_recibos')
            ->select('mt_recibos.id as recibo_id', 'mt_recibos_pagos_miniterminales.user_id', 'mt_recibos_pagos_miniterminales.tipo_pago_id', 'mt_recibos_pagos_miniterminales.fecha', 'mt_recibos_pagos_miniterminales.cuenta_bancaria_id', 'mt_recibos_pagos_miniterminales.monto', 'mt_recibos.recibo_nro', 'mt_recibos_pagos_miniterminales.boleta_numero')
            ->join('mt_recibos_pagos_miniterminales', 'mt_recibos_pagos_miniterminales.recibo_id', '=', 'mt_recibos.id')
            ->where('mt_recibos_pagos_miniterminales.id', $recibo_id)
            ->first();

        $grupo = \DB::table('business_groups')
            ->select('business_groups.*')
            ->join('branches', 'business_groups.id', '=', 'branches.group_id')
            ->where('branches.user_id', $recibo->user_id)
            ->first();


        $get_cuotas = \DB::table('mt_recibo_x_cuota')
            ->select('numero_cuota')
            ->where('recibo_id', $recibo->recibo_id)
            ->pluck('numero_cuota');

        $cuotas = implode(', ', $get_cuotas);

        $venta_cuota = \DB::table('mt_recibo_x_cuota')->where('recibo_id', $recibo->recibo_id)->first();

        $venta = \DB::table('venta')->where('id', $venta_cuota->credito_venta_id)->first();

        $tipo_pago = \DB::table('tipo_pago')->where('id', $recibo->tipo_pago_id)->first();

        $banco = \DB::table('bancos')
            ->select('bancos.*')
            ->join('cuentas_bancarias', 'bancos.id', '=', 'cuentas_bancarias.banco_id')
            ->where('cuentas_bancarias.id', $recibo->cuenta_bancaria_id)
            ->first();

        $cuotas_name = implode('- ', $get_cuotas);
        $fecha = date('d/m/Y', strtotime($recibo->fecha));
        $nombre = 'Comprobante-cuota-' . $cuotas_name . '-' . $fecha . '.pdf';

        $view =  \View::make('depositos_cuotas.comprobante', compact('recibo', 'grupo', 'cuotas', 'venta', 'tipo_pago', 'banco'))->render();
        $pdf = \App::make('dompdf.wrapper');
        \DB::table('mt_recibos')
            ->where('id', $recibo->recibo_id)
            ->update([
                'reprinted' => true
            ]);
        $pdf->loadHTML($view);
        return $pdf->download($nombre);
    }

    public function comprobante_alquiler($recibo_id)
    {
        if (!$this->user->hasAnyAccess('reporting_depositos_cuotas')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if (!is_numeric($recibo_id)) {
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $recibo = \DB::table('mt_recibos_pagos_miniterminales')->where('id', $recibo_id)->first();

        $recibo_pago = \DB::table('mt_recibos')->where('id', $recibo->recibo_id)->first();

        $grupo = \DB::table('business_groups')
            ->select('business_groups.*')
            ->join('branches', 'business_groups.id', '=', 'branches.group_id')
            ->where('branches.user_id', $recibo->user_id)
            ->first();

        $get_cuotas = \DB::table('mt_recibo_alquiler_x_cuota')
            ->select('numero_cuota')
            ->where('recibo_id', $recibo->recibo_id)
            ->pluck('numero_cuota');

        $cuotas = implode(', ', $get_cuotas);

        $alquiler_cuota = \DB::table('mt_recibo_alquiler_x_cuota')->where('recibo_id', $recibo->recibo_id)->first();

        $alquiler = \DB::table('cuotas_alquiler')
            ->where('alquiler_id', $alquiler_cuota->alquiler_id)
            ->where('num_cuota', $alquiler_cuota->numero_cuota)
            ->first();
        //dd($recibo_pago);
        $tipo_pago = \DB::table('tipo_pago')->where('id', $recibo->tipo_pago_id)->first();

        $banco = \DB::table('bancos')
            ->select('bancos.*')
            ->join('cuentas_bancarias', 'bancos.id', '=', 'cuentas_bancarias.banco_id')
            ->where('cuentas_bancarias.id', $recibo->cuenta_bancaria_id)
            ->first();

        $cuotas_name = implode('- ', $get_cuotas);
        $fecha = date('d/m/Y', strtotime($recibo->fecha));
        $nombre = 'Comprobante-cuota-' . $cuotas_name . '-' . $fecha . '.pdf';

        $view =  \View::make('depositos_alquileres.comprobante', compact('recibo', 'grupo', 'cuotas', 'alquiler', 'tipo_pago', 'banco', 'recibo_pago'))->render();
        $pdf = \App::make('dompdf.wrapper');
        \DB::table('mt_recibos')
            ->where('id', $recibo->recibo_id)
            ->update([
                'reprinted' => true
            ]);
        $pdf->loadHTML($view);
        return $pdf->download($nombre);
    }

    /** Historial de bloqueos*/
    public function historialBloqueosReports(Request $request)
    {
        if (!$this->user->hasAnyAccess('reporting_boleta_depositos')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $report = new ReportServices('');
        $result = $report->historialBloqueosReports($request);
        return view('reporting.index')->with($result);
    }

    public function historialBloqueosSearch(Request $request)
    {
        if (!$this->user->hasAnyAccess('reporting_boleta_depositos')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = \Request::all();

        if (isset($input['download'])) {
            ini_set('max_execution_time', 300);
            $report = new ReportServices($input);
            $result = $report->boletaDepositosSearchExport();
            $result = json_decode(json_encode($result), true);
            $columnas = array(
                'ID', 'Fecha', 'Usuario', 'Concepto', 'Banco', 'Cuenta Bancaria', 'Nro Boleta', 'Monto', 'Estado', 'Modificado por', 'Fecha Modificacion'
            );

            if ($result) {
                $filename = 'depositos_boletas_' . time();

                $excel = new ExcelExport($result['transactions'],$columnas);
                return Excel::download($excel, $filename . '.xls')->send();

                // Excel::create($filename, function ($excel) use ($result) {
                //     $excel->sheet('sheet1', function ($sheet) use ($result) {
                //         $sheet->rows($result['transactions'], false);
                //         $sheet->prependRow(array(
                //             'ID', 'Fecha', 'Usuario', 'Concepto', 'Banco', 'Cuenta Bancaria', 'Nro Boleta', 'Monto', 'Estado', 'Modificado por', 'Fecha Modificacion'
                //         ));
                //     });
                // })->export('xls');
                // exit();
            } else {
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();
            }
        }

        if (isset($input['search']) || isset($input['context']) || isset($input['page'])) {
            $report = new ReportServices($input);
            $result = $report->historialBloqueosSearch($request);
            return view('reporting.index')->with($result);
        }
    }

    //transaction que no tienen reversa
    public function transaction_not_rollback()
    {

        $report = new ReportServices('');
        $result = $report->transaction_not_rollback();

        return view('reporting.index')->with($result);
    }

    public function transaction_not_rollbackSearch()
    {
        $input = \Request::all();

        $report = new ReportServices($input);

        $result = $report->transaction_not_rollbackSearch();

        return view('reporting.index')->with($result);
    }

    // ventas pendientes de afectar extractos.

    public function movements_affecting_extracts()
    {
        $report = new ReportServices('');

        $result = $report->movements_affecting_extracts();

        return view('reporting.index')->with($result);
    }

    public function movements_affecting_extracts_update_destination(Request $request)
    {
        $id = $request->_id;
        try {
            \DB::table('mt_movements')
                ->where('id', $id)
                ->update([
                    'destination_operation_id' => 0,
                    'updated_at' => Carbon::now(),

                ]);
            $error  =   "Registro, Id: $id relanzado exitosamente";
            $message =   "Registro, Id: $id relanzado exitosamente";
            Session::flash('message', "Registro, Id: $id Reversado exitosamente");
        } catch (\Exception $e) {
            $error_detail = $this->custom_error($e, __FUNCTION__);
            $message = 'Error al reversar la transaccion.';
            $error = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }

    public function transaction_success_amount_zero()
    {
        $report = new ReportServices('');

        $result = $report->transaction_success_amount_zero();

        return view('reporting.index')->with($result);
    }
    public function updateReversa(Request $request)
    {
        $id = $request->_id;
        try {
            \DB::table('rollback_credito_pdv_sync')
                ->where('id', $id)
                ->update([
                    'status' => 0,
                    'updated_at' => Carbon::now(),
                ]);

            $error  =   "Registro, Id: $id realizado exitosamente";
            $message =   "Registro, Id: $id realizado exitosamente";
            Session::flash('message', "Registro, Id: $id Reversado exitosamente");
        } catch (\Exception $e) {
            $error_detail = $this->custom_error($e, __FUNCTION__);
            $message = 'Error al reversar la transaccion.';
            $error = true;
        }


        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }
    public function updateReversaAll(Request $request)
    {
        $ids = $request->_ids;
        try {
            \DB::table('rollback_credito_pdv_sync')
                ->whereRaw("id = any(array[$ids])")
                ->update([
                    'status' => 0,
                    'updated_at' => Carbon::now(),
                ]);
            $error  =   "Registro, Id: $ids realizado exitosamente";
            $message =   "Registro, Id: $ids realizado exitosamente";
            Session::flash('message', "Registro, Id: $ids Reversado exitosamente");
        } catch (\Exception $e) {

            $error_detail = $this->custom_error($e, __FUNCTION__);
            $message = 'Error al reversar la transaccion.';
            $error = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }

    public function reversaTransaction(Request $request)
    {

        $id = $request->_id;

        $transaction = \DB::table('transactions')
            ->where('id', $id)
            ->get();

        try {
            \DB::table('transactions')
                ->where('id', $id)
                ->update([
                    'status' => 'rollback',
                    'updated_at' => Carbon::now(),
                ]);
            \DB::table('rollback_credito_pdv_sync')->insert([
                'backend_transaction_id'  => $transaction[0]->id,
                'amount'   => $transaction[0]->amount,
                'created_at' => Carbon::now(),
            ]);
            $error  =   "Registro, Id: $id realizado exitosamente";
            $message =   "Registro, Id: $id realizado exitosamente";
            Session::flash('message', "Registro, Id: $id Reversado exitosamente");
        } catch (\Exception $e) {
            $error_detail = $this->custom_error($e, __FUNCTION__);
            $message = 'Error al reversar la transaccion.';
            $error = true;
        }
        //     $ids = $transaction[0]->id;
        //     $error  =   "Registro, Id:$ids  realizado exitosamente";
        //    $message=   "Registro, Id: $id realizado exitosamente";

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }

    public function reversaTransactionAll(Request $request)
    {

        $ids = $request->_ids;
        $transactions = \DB::table('transactions')
            ->select('id', 'amount')
            ->whereRaw("id = any(array[$ids])")
            ->get();
        if (isset($transactions)) {
            try {

                foreach ($transactions as $transaction) {
                    \DB::table('transactions')
                        ->where('id', $transaction->id)
                        ->update([
                            'status' => 'rollback',
                            'updated_at' => Carbon::now(),
                        ]);
                    \DB::table('rollback_credito_pdv_sync')
                        ->insert([
                            'backend_transaction_id'  => $transaction->id,
                            'amount'   => $transaction->amount,
                            'created_at' => Carbon::now(),
                        ]);
                }

                $error  =   "Registro, Id: $ids realizado exitosamente";
                $message =   "Registro, Id: $ids realizado exitosamente";
                Session::flash('message', "Registro, Id: $ids Reversado exitosamente");
            } catch (\Exception $e) {
                $error_detail = $this->custom_error($e, __FUNCTION__);
                $message = 'Error al reversar la transaccion.';
                $error = true;
            }
        } else {
            \log::error('no se encontro la transaccion | Id: ' . $ids);
            $message =  'error al intentar reversar la operacion';
            $error = true;
            Session::flash('error_message', 'no se encontro la transaccion | Id: ' . $ids);
        }


        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }

    /** Conciliaciones Pendientes */
    public function conciliations_detailsReports()
    {
        $report = new ReportServices('');
        $result = $report->conciliations_detailsReports();
        return view('reporting.index')->with($result);
    }

    public function conciliations_detailsSearch()
    {
        $input = \Request::all();

        if (isset($input['search']) || isset($input['page'])) {
            $report = new ReportServices($input);
            $result = $report->conciliations_detailsSearch();
            //return view('reporting.index', compact('target','notifications','incomes','incomes_error','atm_id','reservationtime','i','services_data','service_id','service_request_id'))->with($result);
            return view('reporting.index')->with($result);
        }

        if (isset($input['download'])) {
            $report = new ReportServices($input);
            $result = $report->conciliations_detailsSearchExport();
            $result = json_decode(json_encode($result), true);
            $filename = 'conciliaciones_detalles_' . time();
            $columnas = array(
                'Id', 'ATM', 'ID TRANSACCION', 'MONTO', 'SERVICIO', 'MENSAJE', 'CREADO', 'MODIFICADO'
            );

            if ($result) {
                $excel = new ExcelExport($result,$columnas);
                return Excel::download($excel, $filename . '.xls')->send();
                // Excel::create($filename, function ($excel) use ($result) {
                //     $excel->sheet('sheet1', function ($sheet) use ($result) {
                //         $sheet->rows($result, false);
                //         $sheet->prependRow(array(
                //             'Id', 'ATM', 'ID TRANSACCION', 'MONTO', 'SERVICIO', 'MENSAJE', 'CREADO', 'MODIFICADO'
                //         ));
                //     });
                // })->export('xls');
                // exit();
            } else {
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();
            }
        }
    }

    public function relaunch_conciliation_detail(Request $request)
    {
        $id = $request->_id;
        $income_details = \DB::table('incomes')
            ->where('id', $id)
            ->get();
        if (isset($income_details)) {
            try {
                \DB::table('incomes')
                    ->select(\DB::raw(" 
                    incomes.id,
                    incomes.destination_operation_id,
                    incomes.transaction_id"))
                    ->where('incomes.id', '=', $id)
                    ->update([
                        'destination_operation_id' => '0',
                    ]);
                $error  =   "Registro, Id: $id relanzado exitosamente";
                $message =   "Registro, Id: $id relanzado exitosamente";
                Session::flash('message', "Registro, Id: $id relanzado exitosamente");
            } catch (\Exception $e) {
                $error_detail = $this->custom_error($e, __FUNCTION__);
                $message = 'Error al relanzar transacciones.';
                $error = true;
            }
        } else {
            \log::error('no se encontro la conciliacion | Id: ' . $id);
            $message =  'error al intentar relanzar la operacion';
            $error = true;
            Session::flash('error_message', 'no se encontro la conciliacion | Id: ' . $id);
        }
        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }
    public function relaunch_conciliation_all(Request $request)
    {
        $ids = $request['_ids'];

        $incomes_errores = \DB::table('incomes')
            //->whereIn('id',$ids['ids'])
            ->whereRaw("incomes.id = any(array[$ids])")
            ->get();
        \Log::info($ids);

        if (isset($incomes_errores)) {
            try {
                \DB::table('incomes')
                    ->select(\DB::raw(" 
                    incomes.id,
                    incomes.destination_operation_id,
                    incomes.transaction_id"))
                    ->whereRaw("incomes.id = any(array[$ids])")
                    ->update([
                        'destination_operation_id' => '0',
                    ]);
                $error  =   "Registro, Id: $ids relanzado exitosamente";
                $message =   "Registro, Id: $ids relanzado exitosamente";
                Session::flash('message', "Registro, Id: $ids relanzado exitosamente");
            } catch (\Exception $e) {
                $error_detail = $this->custom_error($e, __FUNCTION__);
                $message = 'Error al relanzar transacciones.';
                $error = true;
            }
        } else {
            \log::error('no se encontro la conciliacion | Id: ' . $ids);
            $message =  'error al intentar relanzar la operacion';
            $error = true;
            Session::flash('error_message', 'no se encontro la conciliacion | Id: ' . $ids);
        }
        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }

    public function generar_inconsistencia(Request $request)
    {
        if (!$this->user->hasAnyAccess('reporting.devolucion')) {
            \Log::warning(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            $response = [
                'error'     => true,
                'message'   => 'Acceso no Autorizado'
            ];
            return $response;
        }

        //Transaction DATA
        $transaction_id = $request->_transaction_id;

        $transaction = \DB::table('transactions')
            ->where('id', $transaction_id)
            ->first();

        if (!$transaction) {
            return 'Transacción no existe';
        }

        $pos = \DB::table('points_of_sale')
            ->where('atm_id', $transaction->atm_id)
            ->first();

        $service_id         = $transaction->service_id;
        $service_source_id  = $transaction->service_source_id;
        $owner_id       = $transaction->owner_id;
        $monto_transaction  = number_format($transaction->amount, 0, '', '');

        $data = [
            'service_id'        => $service_id,
            'service_source_id' => $service_source_id,
            'owner_id'          => $owner_id,
            'monto_transaction' => $monto_transaction,

        ];


        \Log::info(json_encode($data));

        if ($transaction->status == 'error') {

            if ($transaction->service_id == 8) {
                $ondanet_code = '0507';
            } elseif ($transaction->service_id == 7 || $transaction->service_id == 9 || $transaction->service_id == 33) {
                $ondanet_code = '0508';
            }

            //se agrega la transacción a la cola de reproceso
            $reprocesar_transaccion = \DB::table('incomes')->insertGetId(
                [
                    'operation'                 => 'register_inconsistency',
                    'origin_operation_id'       => $transaction->id,
                    'destination_operation_id'  => 0,
                    'created_at'                => $transaction->created_at,
                    'pos_id'                    => $pos->id,
                    'transaction_id'            => $transaction->id,
                    'client_id'                 => 4,
                    'ondanet_code'              => $ondanet_code,
                    'transaction_type'          => 0,
                ]
            );

            //cambiar el estado de la transacción a procesado = false para que el reproceso pueda efectuarce

            //cambiar a estado devolucion
            \DB::table('transactions')
                ->where('id', $transaction_id)
                ->update(['status' => 'inconsistency', 'processed' => 0]);
        } else {
            $response = [];
            $response['error'] = true;
            return $response;
        }

        //return $transaction;
        $response = [];
        $response['error'] = false;
        return $response;
    }

    public function generar_reversion(Request $request)
    {
        if (!$this->user->hasAnyAccess('reporting.reversion_ken')) {
            \Log::warning(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            $response = [
                'error'     => true,
                'message'   => 'Acceso no Autorizado'
            ];
            return $response;
        }

        $transaction_id = $request->_transaction_id;

        $transaction = \DB::table('transactions')
            ->where('id', $transaction_id)
            ->first();

        if (!$transaction) {
            return 'Transacción no existe';
        }

        if ($transaction->owner_id == 16 || $transaction->owner_id == 21 || $transaction->owner_id == 25) {
            if (\DB::table('mt_recibos_reversiones')->where('transaction_id', $transaction_id)->count() == 0) {
                //Transaction DATA
                $fecha = Carbon::now();
                $nro_factura = $transaction->id;

                $report = new ReversionesController();
                $result = $report->reversar_v2($transaction, $fecha, $nro_factura);

                $response = [];
                if ($result['success']) {
                    $response['error'] = false;
                } else {
                    $response['error'] = true;
                }
            } else {
                $response['error'] = true;
            }
        } else {

            $pos = \DB::table('points_of_sale')->where('atm_id', $transaction->atm_id)->first();

            if ($transaction->service_source_id == 8) {
                \DB::table('incomes')->insert([
                    'operation'                 => 'REGISTER_INCOME_REVERSION_KEN',
                    'origin_operation_id'       => $transaction->id,
                    'destination_operation_id'  => 0,
                    'created_at'                => Carbon::now(),
                    'pos_id'                    => $pos->id,
                    'transaction_id'            => $transaction->id,
                    'client_id'                 => 4,
                    'ondanet_code'              => '0613',
                    'transaction_type'          => 0,
                    'incomes_pairing_id'        => 1,
                ]);

                \DB::table('incomes')->insert([
                    'operation'                 => 'REGISTER_INCOME_REVERSION_KEN',
                    'origin_operation_id'       => $transaction->id,
                    'destination_operation_id'  => 0,
                    'created_at'                => Carbon::now(),
                    'pos_id'                    => $pos->id,
                    'transaction_id'            => $transaction->id,
                    'client_id'                 => 4,
                    'ondanet_code'              => '0613',
                    'transaction_type'          => 0
                ]);
            } else {

                switch ($transaction->service_source_id) {
                    case 0:
                        if ($transaction->service_id == 13 || $transaction->service_id == 14) {
                            $ondanet_code = 'REVIS';
                        } else {
                            $ondanet_code = 'REDAR';
                        }
                        break;
                    case 1:
                        $ondanet_code = 'RENET';
                        break;
                    case 4:
                        $ondanet_code = 'REPRO';
                        break;
                    case 7:
                        $ondanet_code = '2050';
                        break;
                    case 10:
                        $ondanet_code = 'RENET';
                        break;
                    default:
                        $response['error'] = true;
                        $response['reversado'] = false;
                        $response['message'] = "Ha ocurrido un error al intentar reversar";
                        return $response;
                        break;
                }

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
            }

            \DB::table('transactions')
                ->where('id', $transaction_id)
                ->update([
                    'status'        => 'rollback',
                    'updated_at'    => Carbon::now()
                ]);

            $response['error'] = false;
        }

        return $response;
    }

    /** Reporte de Depositos Cuotas **/
    public function depositosAlquileresReports(Request $request)
    {
        if (!$this->user->hasAnyAccess('reporting_depositos_cuotas')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $report = new ReportServices('');
        $result = $report->depositosAlquileresReports($request);
        return view('reporting.index')->with($result);
    }

    public function depositosAlquileresSearch(Request $request)
    {
        if (!$this->user->hasAnyAccess('reporting_depositos_cuotas')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = \Request::all();

        if (isset($input['search']) || isset($input['context']) || isset($input['page'])) {
            $report = new ReportServices($input);
            $result = $report->depositosAlquileresSearch($request);
            return view('reporting.index')->with($result);
        }

        if (isset($input['download'])) {
            ini_set('max_execution_time', 300);
            $report = new ReportServices($input);
            $result = $report->depositosAlquileresSearchExport();
            $result = json_decode(json_encode($result), true);
            $columnas = array(
                'ID', 'Fecha', 'Usuario', 'Concepto', 'Banco', 'Cuenta Bancaria', 'Nro Boleta', 'Monto', 'Estado', 'Modificado por', 'Fecha Modificacion', 'Mensaje'
            );

            if ($result) {
                $filename = 'depositos_boletas_' . time();
                $excel = new ExcelExport($result['transactions'],$columnas);
                return Excel::download($excel, $filename . '.xls')->send();
                // Excel::create($filename, function ($excel) use ($result) {
                //     $excel->sheet('sheet1', function ($sheet) use ($result) {
                //         $sheet->rows($result['transactions'], false);
                //         $sheet->prependRow(array(
                //             'ID', 'Fecha', 'Usuario', 'Concepto', 'Banco', 'Cuenta Bancaria', 'Nro Boleta', 'Monto', 'Estado', 'Modificado por', 'Fecha Modificacion', 'Mensaje'
                //         ));
                //     });
                // })->export('xls');
                // exit();
            } else {
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();
            }
        }
    }

    /** REPORTE DE CUOTAS DE ALQUILER */
    public function cuotasAlquilerReports(Request $request)
    {
        if (!$this->user->hasAnyAccess('reporting_boleta_depositos')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $report = new ReportServices('');
        $result = $report->cuotasAlquilerReports($request);
        //return view('reporting.index', compact('target'))->with($result);
        return view('reporting.index')->with($result);
    }

    public function cuotasAlquilerSearch(Request $request)
    {
        if (!$this->user->hasAnyAccess('reporting_boleta_depositos')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = \Request::all();

        if (isset($input['download'])) {
            ini_set('max_execution_time', 300);
            $report = new ReportServices($input);
            $result = $report->boletaDepositosSearchExport();
            $result = json_decode(json_encode($result), true);
            $columnas = array(
                'ID', 'Fecha', 'Usuario', 'Concepto', 'Banco', 'Cuenta Bancaria', 'Nro Boleta', 'Monto', 'Estado', 'Modificado por', 'Fecha Modificacion'
            );

            if ($result) {
                $filename = 'depositos_boletas_' . time();

                $excel = new ExcelExport($result['transactions'],$columnas);
                return Excel::download($excel, $filename . '.xls')->send();

                // Excel::create($filename, function ($excel) use ($result) {
                //     $excel->sheet('sheet1', function ($sheet) use ($result) {
                //         $sheet->rows($result['transactions'], false);
                //         $sheet->prependRow(array(
                //             'ID', 'Fecha', 'Usuario', 'Concepto', 'Banco', 'Cuenta Bancaria', 'Nro Boleta', 'Monto', 'Estado', 'Modificado por', 'Fecha Modificacion'
                //         ));
                //     });
                // })->export('xls');
                // exit();
            } else {
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();
            }
        }

        if (isset($input['search']) || isset($input['context']) || isset($input['page'])) {
            $report = new ReportServices($input);
            $result = $report->cuotasAlquilerSearch($request);
            //return view('reporting.index', compact('target', 'reservationtime'))->with($result);
            return view('reporting.index')->with($result);
        }
    }

    public function factura_alquiler($alquiler_id)
    {
        if (!$this->user->hasAnyAccess('reporting_depositos_cuotas')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if (!is_numeric($alquiler_id)) {
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $alquiler = \DB::table('cuotas_alquiler')->where('id', $alquiler_id)->first();

        $timbrado = \DB::table('points_of_sale_vouchers_generic')
            ->where('owner_id', 11)
            ->whereNull('deleted_at')
            ->orderBy('id', 'DESC')
            ->first();

        $grupo = \DB::table('business_groups')
            ->select('business_groups.*')
            ->join('alquiler', 'business_groups.id', '=', 'alquiler.group_id')
            ->join('cuotas_alquiler', 'alquiler.id', '=', 'cuotas_alquiler.alquiler_id')
            ->where('cuotas_alquiler.alquiler_id', $alquiler->alquiler_id)
            ->first();

        $get_cuotas = \DB::table('cuotas_alquiler')
            ->select('num_cuota')
            ->where('id', $alquiler_id)
            ->pluck('num_cuota');

        $cuotas = implode(', ', $get_cuotas->toArray());

        $cuotas_name = implode('- ', $get_cuotas->toArray());
        $fecha = date('d/m/Y', strtotime($alquiler->fecha_grabacion));

        $azaz = new NumberFormatter("es-PY", NumberFormatter::SPELLOUT);
        $text = preg_replace('~\x{00AD}~u', '', $azaz->format($alquiler->importe));

        $porcentaje = $alquiler->importe / 11;

        $nombre = 'Factura-cuota-' . $cuotas_name . '-' . $fecha . '.pdf';

        $view =  \View::make('depositos_alquileres.factura', compact('alquiler', 'grupo', 'text', 'porcentaje', 'timbrado'))->render();
        $pdf = \App::make('dompdf.wrapper');
        \DB::table('cuotas_alquiler')
            ->where('id', $alquiler_id)
            ->update([
                'reprinted' => true
            ]);
        $pdf->loadHTML($view);
        return $pdf->download($nombre);
    }

    /**PAGO A CLIENTES MINITERMINALES*/
    /*public function pagoClientesReports(Request $request)
    {
        if (!$this->user->hasAnyAccess('pago_clientes')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $report = new ReportServices('');
        $result = $report->pagoClientesReports($request);
        return view('pago_cliente.index')->with($result);
    }

    public function pagoClientesSearch(Request $request){
        if (!$this->user->hasAnyAccess('pago_clientes.create_txt')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $input = \Request::all();
        if(isset($input['download'])){
            ini_set('max_execution_time', 300);
            $report = new ReportServices($input);
            $result = $report->resumenMiniterminalesSearchExport();
            $result = json_decode(json_encode($result),true);
            if($result){
                $filename = 'resumen_miniterminales'.time();
                Excel::create($filename, function($excel) use ($result) {
                    $excel->sheet('sheet1', function($sheet) use ($result) {    
                        $sheet->rows($result['transacciones_groups'],false);
                         $sheet->prependRow(array(
                             'Id','Grupo', 'Total Transaccionado','Total Depositado','Total Reversado','Saldo'
                        ));             
                    });
                    $excel->sheet('Por Sucursal', function($sheet) use ($result) {    
                        $sheet->rows($result['transacciones'],false);
                        $sheet->prependRow(array(
                            'Sucursal', 'Total Transaccionado', 'Total Depositado', 'Saldo'
                        ));        
                    }); 
                })->export('xls');
                exit();
            }else{
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();   
            }
            
        }

        if(isset($input['search']) || isset($input['context']) || isset($input['page'])){
            $report = new ReportServices($input);
            $result = $report->resumenMiniterminalesSearch($request);
            return view('reporting.index', compact('target', 'reservationtime'))->with($result);
        }  
    }*/

    public function generarTxt(Request $request)
    {
        if (!$this->user->hasAnyAccess('pago_clientes.create_txt')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = \Request::all();

        $grupo = $input['group'];
        $grupos = implode(', ', $grupo);

        $whereMovements = "current_account.group_id in(" . $grupos . ") AND";

        $hasta = Carbon::parse(date('Y-m-d 23:59:59'))->modify('-1 days');

        $whereSales = "WHEN debit_credit = 'de' AND miniterminales_sales.fecha <= '" . $hasta . "'";
        $whereCobranzas = "WHEN movement_type_id = 2 AND boletas_depositos.fecha <= '" . $hasta . "'";
        $whereReversion = "WHEN movement_type_id = 3 AND mt_recibos_reversiones.fecha_reversion <= '" . $hasta . "'";
        $whereCashout   = "WHEN movement_type_id = 11 AND movements.created_at <= '" . $hasta . "'";
        //$whereMovements .= " LOWER(business_groups.description) like LOWER('%{$input['context']}%') AND";
        /*$whereCobranzas = "WHEN movement_type_id = 2 AND movements.created_at <= '". $hasta . "'";
        $whereReversion = "WHEN movement_type_id = 3 AND movements.created_at <= '". $hasta . "'";
        $whereCashout = "WHEN movement_type_id = 11 AND movements.created_at <= '". $hasta . "'";
        $whereSales = "WHEN debit_credit = 'de' AND miniterminales_sales.fecha <= '". $hasta . "'";*/
        $date = "'" . date('Y-m-d H:i:s') . "'";

        $resumen_transacciones_groups = \DB::select(\DB::raw("
            select
                    current_account.group_id,
                    business_groups.description as grupo,
                    business_groups.ruc as ruc,
                    SUM(CASE " . $whereSales . " THEN (movements.amount) else 0 END) as Debito,
                    SUM(CASE " . $whereCobranzas . " THEN (movements.amount) else 0 END) +
                    SUM(CASE " . $whereReversion . " THEN (movements.amount) else 0 END) +
                    SUM(CASE " . $whereCashout . " THEN (movements.amount) else 0 END) as Credito,
                    (   (SUM(CASE " . $whereSales . " THEN (movements.amount) else 0 END))
                        +(SUM(CASE " . $whereCobranzas . " THEN (movements.amount) else 0 END))
                        +(SUM(CASE " . $whereReversion . " THEN (movements.amount) else 0 END))
                        +(SUM(CASE " . $whereCashout . " THEN (movements.amount) else 0 END))
                    ) as saldo
            from movements
            inner join current_account on movements.id = current_account.movement_id
            inner join business_groups on business_groups.id = current_account.group_id
            left join miniterminales_sales on movements.id = miniterminales_sales.movements_id
            left join mt_recibos on mt_recibos.movements_id = movements.id
            left join mt_recibos_cobranzas on mt_recibos.id = mt_recibos_cobranzas.recibo_id 
            left join boletas_depositos on boletas_depositos.id = mt_recibos_cobranzas.boleta_deposito_id
            left join mt_recibos_reversiones on mt_recibos.id = mt_recibos_reversiones.recibo_id
            where
                " . $whereMovements . "
                movements.movement_type_id not in (4, 5, 7, 8, 9, 10)
                and movements.destination_operation_id not in ('0','1','-2','-3','-4','-5','-6','-9','-10','-11','-12','-13','-14','-16','-16','-17','-21','-23','-26',-27,212, 999)
                and movements.deleted_at is null
            group by current_account.group_id, grupo, ruc
            having 
            (   
                (SUM(CASE " . $whereSales . " THEN (movements.amount) else 0 END))
                +(SUM(CASE " . $whereCobranzas . " THEN (movements.amount) else 0 END))
                +(SUM(CASE " . $whereReversion . " THEN (movements.amount) else 0 END))
                +(SUM(CASE " . $whereCashout . " THEN (movements.amount) else 0 END))
            ) < 0
            order by saldo desc
        "));

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

        $i = 0;
        foreach ($resumen_transacciones_groups as $resumen_transacciones_group) {
            $ruc = explode('-', $resumen_transacciones_group->ruc);
            $documento[$i] = $ruc[0];
            $saldo[$ruc[0]] = $resumen_transacciones_group->saldo;
            $i++;
        }

        $documentos = implode(', ', $documento);

        $clientes =  \DB::connection('ondanet')
            ->table('CATASTRO_PROVEEDORES as ct')
            ->select(
                'ct.MODO_ACREDITACION',
                'ct.TIPO_DOCUMENTO',
                'ct.NUMERO_DOCUMENTO',
                'ct.APELLIDO_PATERNO',
                'ct.APELLIDO_MATERNO',
                'ct.PRIMER_NOMBRE',
                'ct.SEGUNDO_NOMBRE',
                'ct.NUMERO_CUENTA',
                'ct.CUENTA_SIPAP',
                'BANCOS_CATASTRO.BIC',
                'BANCOS_CATASTRO.DESCRIPCION'
            )
            ->leftJoin('BANCOS_CATASTRO', 'BANCOS_CATASTRO.DESCRIPCION', '=', 'ct.CODIGO_ENTIDAD')
            ->whereRaw("NUMERO_DOCUMENTO  in('" . $documentos . "')")
            ->get();
        //dd($clientes);
        $x = 0;
        $total = 0;
        /*foreach($clientes as $cliente){
            $pago[$x]['CODOPERACION']        = 13;
            $pago[$x]['CODSECUENCIA']        = 1;
            $pago[$x]['COD_TIPO_DOCUMENTO']  = 1;
            $pago[$x]['DOCUMENTO']           = $cliente->NUMERO_DOCUMENTO;
            $pago[$x]['APELLIDO_PATERNO']    = $cliente->APELLIDO_PATERNO;
            $pago[$x]['APELLIDO_MATERNO']    = $cliente->APELLIDO_MATERNO;
            $pago[$x]['PRIMER_NOMBRE']       = $cliente->PRIMER_NOMBRE;
            $pago[$x]['SEGUNDO_NOMBRE']      = $cliente->SEGUNDO_NOMBRE;
            $pago[$x]['IMPORTE']             = abs($saldo[$cliente->NUMERO_DOCUMENTO]);
            $pago[$x]['FACTURA_COMPRA']      = 0;
            $pago[$x]['NUMERO_CUENTA']       = (empty($cliente->NUMERO_CUENTA) ? 0 : $cliente->NUMERO_CUENTA);
            $pago[$x]['CUENTA_SIPAP']        = strval($cliente->CUENTA_SIPAP);
            $pago[$x]['BIC']                 = $cliente->BIC;
            $pago[$x]['CODIGO_ENTIDAD']      = (empty($cliente->DESCRIPCION) ? 'SUDAMERIS' : $cliente->DESCRIPCION);
            
            $total= $total + $pago[$x]['IMPORTE'];
            $x++;
        }*/

        $date_val = date('Ymd');
        header('Content-Type: application/txt');
        header("Content-Disposition: attachment; filename=Pago_Vía_ELO $date_val " . $date[0]->concat . ".txt");
        //header("Content-Transfer-Encoding: UTF-8");

        //$url_temp = storage_path('excel/exports');

        foreach ($clientes as $cliente) {
            $pago[$x]['IMPORTE']    = abs($saldo[$cliente->NUMERO_DOCUMENTO]);

            $total = $total + $pago[$x]['IMPORTE'];
            $x++;
        }

        $num = $date[0]->concat;
        $cant = $x;

        $row = array(
            'H', '699', '', '6900', $cant, $total, 'N', date('d/m/Y'),
            $num, 'D', '2603496', '25', '20', '6900', '0', '0', '0', '0', '//', '//', '0', '0'
        );

        /*$filename = 'transaccion_'.date('Ym', strtotime('-1 months'));*/

        //$file = fopen("holoa.txt", 'w');
        $file = fopen('php://output', 'a');

        fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

        fputcsv($file, $row, ";");

        foreach ($clientes as $cliente) {

            if ($cliente->MODO_ACREDITACION == 'Transf._SIPAP') {
                $modulo_cuenta = 0;
                $modalidad_pago = 62;
            } else if ($cliente->MODO_ACREDITACION == 'Credito_en_Cta.Cte.') {
                $modulo_cuenta = 20;
                $modalidad_pago = 20;
            } else if ($cliente->MODO_ACREDITACION == 'Credito_en_Caja_de_Ahorros') {
                $modulo_cuenta = 21;
                $modalidad_pago = 21;
            }

            \Log::info($cliente->MODO_ACREDITACION);

            if ($cliente->TIPO_DOCUMENTO == 'CEDULA DE IDENTIDAD') {
                $documento_codigo = 1;
            } else if ($cliente->TIPO_DOCUMENTO == 'R.U.C.') {
                $documento_codigo = 3;
            }
            \Log::info($cliente->TIPO_DOCUMENTO);
            //dd($documento_codigo);


            /*$pago[$x]['TIPO_REGISTRO']          = 'D';
            $pago[$x]['CONCEPTO_PAGO']          = $cliente->MODO_ACREDITACION;
            $pago[$x]['NRO_REFERENCIA']         = $date[0]->concat;
            $pago[$x]['CODIGO_PAIS_DOCUMENTO']  = null;
            $pago[$x]['COD_TIPO_DOCUMENTO']     = $documento_codigo;
            $pago[$x]['NUMERO_DOCUMENTO']       = $cliente->NUMERO_DOCUMENTO;
            $pago[$x]['APELLIDO_PATERNO']       = $cliente->APELLIDO_PATERNO;
            $pago[$x]['APELLIDO_MATERNO']       = $cliente->APELLIDO_MATERNO;
            $pago[$x]['PRIMER_NOMBRE']          = $cliente->PRIMER_NOMBRE;
            $pago[$x]['SEGUNDO_NOMBRE']         = $cliente->SEGUNDO_NOMBRE;
            $pago[$x]['MONEDA']                 = 6900;
            $pago[$x]['IMPORTE']                = abs($saldo[$cliente->NUMERO_DOCUMENTO]);
            $pago[$x]['FACTURA_COMPRA']         = 0;
            $pago[$x]['EMAIL']                  = null;
            $pago[$x]['TELEFONO']               = null;
            $pago[$x]['SUCURSAL_CUENTA']        = 25;
            $pago[$x]['MODULO_CUENTA']          = $modulo_cuenta;
            $pago[$x]['MONEDA_CUENTA']          = 6900;
            $pago[$x]['PAPEL_CUENTA']           = 0;
            $pago[$x]['NUMERO_CUENTA']          = (empty($cliente->NUMERO_CUENTA) ? 0 : $cliente->NUMERO_CUENTA);
            $pago[$x]['OPERACION']              = 0;
            $pago[$x]['SUB_OPERACION']          = 0;
            $pago[$x]['TIPO_OPERACION']         = 0;
            $pago[$x]['NRO_TARJETA_PREPAGA']    = null;
            $pago[$x]['NUMERO_CUENTA2']         = (empty($cliente->NUMERO_CUENTA) ? 0 : $cliente->NUMERO_CUENTA);
            $pago[$x]['CODIGO_ENTIDAD']         = (empty($cliente->BIC) ? null : $cliente->BIC);
            $pago[$x]['PAIS_AUTORIZADO']        = null;
            $pago[$x]['COD_TIPO_DOCUMENTO_AUTORIZADO']  = $documento_codigo;
            $pago[$x]['NRO_DOCUMENTO_AUTORIZADO']   = null;
            $pago[$x]['NOMBRE_AUTORIZADO']          = null;
            $pago[$x]['COD_MODALIDAD_PAGO']         = $modalidad_pago;*/
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
            fputcsv($file, $row, ";");
        }
        fclose($file);
        die();
        /*$resultset = array(
            'resumen_transacciones_groups'  => $pago,
        );
        $result = json_decode(json_encode($resultset),true);
        $cant=$x;
        if($result){

            $date_val= date('Ymd');
            
            $filename = 'Pago_Vía_ELO '.$date_val.' '.$date[0]->concat;
            $num=$date[0]->concat;

            Excel::create($filename, function($excel) use ($result, $cant, $total, $num) {
                $excel->sheet('sheet1', function($sheet) use ($result, $cant, $total, $num) {    
                    $sheet->rows($result['resumen_transacciones_groups'],false);
                        $sheet->prependRow(array(
                            'H','699', '','6900', $cant, $total, 'N', date('d/m/Y'), 
                            $num, 'D', '2603496', '25', '20', 
                            '6900', '0', '0', '0', '0', '//', '//', '0', '0'
                        ));        
                    });
            })->export('txt');
            Session::flash('message', 'txt Generado exitosamente');
            return redirect('pago_clientes');
            //exit();
        }else{
            Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
            return redirect()->back();   
        }*/
    }

    public function importPagoClientes()
    {
        if (!$this->user->hasAnyAccess('pago_clientes.import_pago')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        /*$this->validate($request, [
            'select_file'  => 'required|mimes:xls,xlsx'
           ]);*/
        $data = [
            'open_modal' => 'no',
            'list' => null,
            'date' => null,
            'method' => 'index'
        ];
        //$sales= \DB::table('miniterminales_sales')->get();
        return view('pago_cliente.import', compact('data'));
    }

    public function store_import_Cliente(Request $request)
    {
        if (!$this->user->hasAnyAccess('pago_clientes.import_pago')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        /*$input = \Request::all();

        $json=json_decode($input['json_xls'], true);
        \Log::critical($json);
        $i=3;
        $max=count($json) - 1;
        
        try{
            
            do{
                \Log::info($i);
                \Log::critical($json[$i]);
                dd($json);
                $clientes =  \DB::connection('ondanet')
                    ->table('CATASTRO_PROVEEDORES')
                    ->where('')
                ->get();
                $i++;
                
                
            }while($i < $max);
            
            dd('exito');
        }catch (\Exception $e){
            \Log::critical($e);
            Session::flash('error_message', 'Error al crear la reversion');
            return redirect()->back()->with('error', 'Error al crear reversion');
        }*/
        \DB::beginTransaction();
        try {

            $this->validate($request, [
                'select_file'  => 'required|mimes:xls,xlsx'
            ]);

            $path = $request->file('select_file')->getRealPath();
            $pagos = Excel::load($path)->get();

            if ($pagos->count() > 0) {
                foreach ($pagos as $pago) {

                    $last_row = \DB::table('miniterminales_sales')
                        ->join('movements', 'movements.id', '=', 'miniterminales_sales.movements_id')
                        ->orderBy('nro_venta', 'desc')
                        ->first();

                    if (isset($last_row)) {
                        $toInt = (int)$last_row->nro_venta;
                        $number = $toInt + 1;
                    } else {
                        $number = 30000000000001;
                    }

                    $numero_venta = strval($number);

                    $movement_id = \DB::table('movements')->insertGetId([
                        'movement_type_id'          => 12,
                        'destination_operation_id'  => 0,
                        'amount'                    => (int)$pago->importe,
                        'debit_credit'              =>  'de',
                        'created_at'                => Carbon::now(),
                        'updated_at'                => Carbon::now()

                    ]);

                    $group = \DB::table('business_groups')->where('ruc', $pago->ruc)->first();

                    $last_balance = \DB::table('current_account')->where('group_id', $group->id)->orderBy('id', 'desc')->first();
                    if (isset($last_balance)) {
                        $balance = $last_balance->balance + (int)$pago->importe;
                    } else {
                        $balance = (int)$pago->importe;
                    }

                    \DB::table('current_account')->insert([
                        'movement_id'               => $movement_id,
                        'group_id'                  => $group->id,
                        'amount'                    => (int)$pago->importe,
                        'balance'                   => $balance,
                    ]);

                    \DB::table('miniterminales_sales')->insert([
                        'movements_id'       => $movement_id,
                        'estado'            => 'pendiente',
                        'nro_venta'         => $numero_venta,
                        'monto_por_cobrar'  => (int)$pago->importe,
                        'fecha'             => date('Y-m-d', strtotime(str_replace('/', '-', $pago->fecha))),
                        'balance_affected'  => false,
                        'date_affected'     => Carbon::now(),
                    ]);
                }
                \DB::commit();
                \Log::info("New Pagos de Clientes on the house !");
                Session::flash('message', 'Nuevas Pagos de Clientes procesados correctamente');
                return redirect('pago_clientes');
            }
        } catch (\Exception $e) {
            \DB::rollback();
            \Log::critical($e);
            Session::flash('error_message', 'Error al crear la reversion');
            return redirect()->back()->with('error', 'Error al crear reversion');
        }
    }

    public function contractsReports()
    {
        if (!$this->user->hasAnyAccess('reporting')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $report = new ReportServices('');
        $result = $report->contractsReports();
        return view('reporting.index')->with($result);
    }

    public function contractsSearch()
    {
        if (!$this->user->hasAnyAccess('reporting')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = \Request::all();
        if (isset($input['search']) || isset($input['context']) || isset($input['page'])) {
            $report = new ReportServices($input);
            $result = $report->contractsSearch();
            if ($result) {
                return view('reporting.index')->with($result);
            } else {
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();
            }
        }

        if (isset($input['download'])) {
            ini_set('max_execution_time', 300);
            $report = new ReportServices($input);
            $result = $report->contractsSearchExport();
            $result = json_decode(json_encode($result), true);
            $filename = 'contratos_' . time();
            $columnas = array(
                'ID', 'Contrato N°', 'Tipo de contrato', 'Fecha de inicio de vigencia', 'Fecha de finalización de vigencia', 'Dias restantes', 'Limite de crédito', 'Estado del contrato', 'Fecha de recepción', 'Fecha de aprobación', 'Grupo', 'ATM', 'Inicio de operación'
            );

            if ($result) {
                $excel = new ExcelExport($result,$columnas);
                return Excel::download($excel, $filename . '.xls')->send();
                // Excel::create($filename, function ($excel) use ($result) {
                //     $excel->sheet('Página 1', function ($sheet) use ($result) {
                //         $sheet->rows($result, false);
                //         $sheet->prependRow(array(
                //             'ID', 'Contrato N°', 'Tipo de contrato', 'Fecha de inicio de vigencia', 'Fecha de finalización de vigencia', 'Dias restantes', 'Limite de crédito', 'Estado del contrato', 'Fecha de recepción', 'Fecha de aprobación', 'Grupo', 'ATM', 'Inicio de operación'
                //         ));
                //     });
                // })->export('xls');
                // exit();
            } else {
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();
            }
        }
    }

    public function getReversionsForGroups($group_id, $day)
    {
        if (!$this->user->hasAnyAccess('reporting_resumen_mini_terminal')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $report = new ReportServices('');
        $result = $report->getReversionsForGroups($group_id, $day);

        return $result;
    }

    public function getCashoutsForGroups($group_id, $day)
    {
        if (!$this->user->hasAnyAccess('reporting_resumen_mini_terminal')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $report = new ReportServices('');
        $result = $report->getCashoutsForGroups($group_id, $day);

        return $result;
    }

    public function getTransactions()
    {
        try {

            $desde = Carbon::now()->startOfMonth()->subMonth();
            $hasta = Carbon::now()->today()->modify('-1 days');
            $where = "transactions.created_at BETWEEN '{$desde}' AND '{$hasta}' ";

            $transactions = \DB::connection('eglobalt_replica')->select("
                select 
                    transactions.created_at as fecha,transactions.created_at as hora, 
                    points_of_sale.description as sede, departamento.descripcion as departamento, 
                    ciudades.descripcion as ciudad, servicios_x_marca.descripcion as servicio_descripcion, 
                    transactions.status as estado, transactions.id, referencia_numero_1 as ref1, 
                    referencia_numero_2 as ref2, 
                    transactions.identificador_transaction_id as identificador_transaccion,
                    case when transactions.service_id =3 then
                        trim(replace(to_char(round(abs(transactions.amount)/1.05, 3), '999G999G999G999'), ',', '.'))
                    else 
                        trim(replace(to_char(abs(transactions.amount), '999G999G999G999'), ',', '.'))
                    end as valor_sin_comision
                from transactions
                inner join points_of_sale on points_of_sale.atm_id = transactions.atm_id
                left join servicios_x_marca on servicios_x_marca.service_id = transactions.service_id and servicios_x_marca.service_source_id = 8
                left join branches on branches.id = points_of_sale.branch_id
                left join barrios on barrios.id = branches.barrio_id
                left join ciudades on ciudades.id = barrios.ciudad_id
                left join departamento on departamento.id = ciudades.departamento_id 
                where 
                    $where 
                    and transactions.status = 'success' and transactions.service_id in(3, 4, 5) 
                    and transactions.service_source_id=8
                order by 
                    transactions.id desc, transactions.created_at desc
            ");

            $url_temp = storage_path('excel/exports');

            $row = array(
                'Fecha', 'Hora', 'Sucursal', 'Departamento', 'Ciudad', 'Servicio', 'Estado',
                'ID', 'Ref 1', 'Ref 2', 'Identificador transaccion', 'Monto'
            );

            $filename = 'transacciones_' . date('Ym', strtotime('-1 months'));

            $file = fopen("$url_temp/$filename.csv", 'w');

            fputs($file, $bom = (chr(0xEF) . chr(0xBB) . chr(0xBF)));

            fputcsv($file, $row, ";");

            foreach ($transactions as $transaction) {

                $row = array(
                    $transaction->fecha,
                    $transaction->hora,
                    $transaction->sede,
                    $transaction->departamento,
                    $transaction->ciudad,
                    $transaction->servicio_descripcion,
                    $transaction->estado,
                    $transaction->id,
                    $transaction->ref1,
                    $transaction->ref2,
                    $transaction->identificador_transaccion,
                    //$transaction->valor_con_comision,
                    $transaction->valor_sin_comision
                );
                fputcsv($file, $row, ";");
            }

            fclose($file);

            $fecha = Carbon::now();
            \Log::info('Se ejecutó tarea programada transactions:sendTransactionsClaro | A las : ' . $fecha);

            $response['error'] = false;
            $response['message'] = 'Reporte de Transacciones Claro ejecutado correctamente';
            \Log::info($response);
            return $reponse;
        } catch (\Exception $e) {
            \Log::info($e->getMessage());
            $fecha = Carbon::now();
            $response['error'] = true;
            $response['message'] = 'Ha ocurrido un error en el Reporte de Transacciones Claro';
            \Log::info('Se ejecutó tarea programada transactions:checkTransactions  | pero se reportaron inconvenientes a las: ' . $fecha, ['result' => $e]);
            return $response;
        }
    }

    /** Pago de Clientes*/
    public function pagoClientesReports(Request $request)
    {
        if (!$this->user->hasAnyAccess('reporting_boleta_depositos')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $report = new ReportServices('');
        $result = $report->pagoClientesReports($request);
        return view('reporting.index')->with($result);
    }

    public function pagoClientesSearch(Request $request)
    {
        if (!$this->user->hasAnyAccess('reporting_boleta_depositos')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = \Request::all();

        if (isset($input['download'])) {
            ini_set('max_execution_time', 300);
            $report = new ReportServices($input);
            $result = $report->pagoClientesSearchExport();
            $result = json_decode(json_encode($result), true);
            $columnas = array(
                'ID', 'Fecha', 'Grupo', 'Generado Por', 'Monto', 'Estado', 'Modificado por', 'Fecha Modificacion'
            );

            if ($result) {
                $filename = 'depositos_boletas_' . time();

                $excel = new ExcelExport($result['transactions'],$columnas);
                return Excel::download($excel, $filename . '.xls')->send();

                // Excel::create($filename, function ($excel) use ($result) {
                //     $excel->sheet('sheet1', function ($sheet) use ($result) {
                //         $sheet->rows($result['transactions'], false);
                //         $sheet->prependRow(array(
                //             'ID', 'Fecha', 'Grupo', 'Generado Por', 'Monto', 'Estado', 'Modificado por', 'Fecha Modificacion'
                //         ));
                //     });
                // })->export('xls');
                // exit();
            } else {
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();
            }
        }

        if (isset($input['search']) || isset($input['context']) || isset($input['page'])) {
            $report = new ReportServices($input);
            $result = $report->pagoClientesSearch($request);
            //dd($result);
            return view('reporting.index')->with($result);
        }
    }


    /** DMS*/
    public function dmsReports()
    {
        if (!$this->user->hasAnyAccess('reports_dms')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $report = new ReportServices('');
        $result = $report->dmsReports();
        return view('reporting.index')->with($result);
    }

    public function dmsSearch()
    {
        if (!$this->user->hasAnyAccess('reports_dms')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = \Request::all();
        if (isset($input['search']) || isset($input['context']) || isset($input['page'])) {
            $report = new ReportServices($input);
            $result = $report->dmsSearch();

            // if(isset($input['reservationtime'])) {
            //     $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
            //     $to = date('Y-m-d H:i:s', strtotime($daterange[0]));
            //     $from = date('Y-m-d H:i:s', strtotime($daterange[1]));
            //     $days = \DB::select("select ('{$from}'::date - '{$to}'::date) + 1 as days");
            //     $days = $days[0]->days;
            // } else {
            //     $days = 0;
            // }


            if ($result) {

                return view('reporting.index')->with($result);
            } else {
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();
            }
        } else if (isset($input['download'])) {

            ini_set('max_execution_time', 300);
            $report = new ReportServices($input);
            $result = $report->dmsSearchExport();

            if (is_array($result)) {
                $result = json_decode(json_encode($result), true);
            }


            $filename = 'clientes_' . time();
            $columnas = array(
                'id',
                'ATM',
                'Atm code',
                'Cliente',
                'Canal',
                'Ruc',
                'Telefono',
                'Dueño',
                'Atendido por',
                'Horario',
                'Categoria',
                'Departamento',
                'Ciudad',
                'Referencia',
                'Direccion',
                'Latitud',
                'Longitud',
                'Accesibilidad',
                'Visibilidad',
                'Trafico',
                'Fecha de creacion',
                'Estado POP',
                'Permite POP',
                'Tiene POP',
                'Tiene Bancard',
                'Tiene Pronet',
                'Tiene Netel',
                'Tiene POS Dinelco',
                'Tiene POS Bancard',
                'Tiene Billetaje',
                'Tiene tigo money',
                'Tiene Visicooler',
                'Vende bebidas con alcohol',
                'Vende bebidas gasificadas',
                'Vende productos de limpieza'
            );

            if ($result) {
                
                $excel = new ExcelExport($result->toArray(),$columnas);
                return Excel::download($excel, $filename . '.xls')->send();
                // Excel::create($filename, function ($excel) use ($result) {
                //     $excel->sheet('sheet1', function ($sheet) use ($result) {
                //         $sheet->rows($result, false);
                //         $sheet->prependRow(array(
                //             'id',
                //             'ATM',
                //             'Atm code',
                //             'Cliente',
                //             'Canal',
                //             'Ruc',
                //             'Telefono',
                //             'Dueño',
                //             'Atendido por',
                //             'Horario',
                //             'Categoria',
                //             'Departamento',
                //             'Ciudad',
                //             'Referencia',
                //             'Direccion',
                //             'Latitud',
                //             'Longitud',
                //             'Accesibilidad',
                //             'Visibilidad',
                //             'Trafico',
                //             'Fecha de creacion',
                //             'Estado POP',
                //             'Permite POP',
                //             'Tiene POP',
                //             'Tiene Bancard',
                //             'Tiene Pronet',
                //             'Tiene Netel',
                //             'Tiene POS Dinelco',
                //             'Tiene POS Bancard',
                //             'Tiene Billetaje',
                //             'Tiene tigo money',
                //             'Tiene Visicooler',
                //             'Vende bebidas con alcohol',
                //             'Vende bebidas gasificadas',
                //             'Vende productos de limpieza'
                //         ));
                //     });
                // })->export('xls');
                // exit();
            } else {
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();
            };
        }
    }


    /**
     * REPORTERÍA PARA CLARO
     */

    /** TRANSACTIONS*/
    public function claro_transactionsReports()
    {

        if (!$this->user->hasRole('red_claro')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        try {

            $report = new ReportServices('');
            $result = $report->claro_transactionsReports();

            if ($result == false) {
                $data = [
                    'mode' => 'message',
                    'type' => 'error',
                    'title' => 'Ocurrió un error',
                    'explanation' => 'No se pudieron obtener los datos del informe.'
                ];

                return view('messages.index', compact('data'));
            } else {
                return view(
                    'reporting.claro.index'
                )->with($result);
            }
        } catch (\Exception $e) {
            $error_detail = [
                'from' => 'CMS',
                'message' => '[claro_transactionsReports] Ocurrió un error al iniciar historico de transacciones de CLARO.',
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine(),
                'user' => [
                    'user_id' => $this->user->id,
                    'username' => $this->user->username,
                    'description' => $this->user->description
                ]
            ];

            \Log::error($error_detail['message'], [$error_detail]);

            $data = [
                'mode' => 'message',
                'type' => 'error',
                'title' => 'Ocurrió un error',
                'explanation' => $error_detail['exception']
            ];

            return view('messages.index', compact('data'));
        }
    }

    public function claro_transactionSearch()
    {

        if (!$this->user->hasRole('red_claro')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        try {

            $input = \Request::all();

            $transaction_id = null;

            if (isset($input['transaction_id'])) {
                if ($input['transaction_id'] !== null and $input['transaction_id'] !== '') {
                    $transaction_id = $input['transaction_id'];
                }
            }

            if (isset($input['search']) || isset($input['context']) || isset($input['page'])) {
                $report = new ReportServices($input);
                $result = $report->claro_transactionsSearch();

                if ($result == false) {
                    $data = [
                        'mode' => 'message',
                        'type' => 'error',
                        'title' => 'Ocurrió un error',
                        'explanation' => 'No se pudieron obtener los datos de la búsqueda.'
                    ];

                    return view('messages.index', compact('data'));
                } else {

                    if (isset($input['reservationtime'])) {
                        $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                        $to = date('Y-m-d H:i:s', strtotime($daterange[0]));
                        $from = date('Y-m-d H:i:s', strtotime($daterange[1]));
                        $days = \DB::select("select ('{$from}'::date - '{$to}'::date) + 1 as days");
                        $days = $days[0]->days;
                    } else {
                        $days = 0;
                    }

                    if ($days > 1 and $transaction_id == null) {
                        //\Log::info('Búsqueda con rango amplio.');

                        $query = json_encode([$result['query_to_export']]);

                        \DB::table('select_to_manage')->insert([
                            'description' => $query,
                            'user_id' => $this->user->id,
                            'created_at' => Carbon::now(),
                            'status' => false
                        ]);

                        $mail = $this->user->email;

                        Session::flash(
                            'message',
                            "El rango de fecha que seleccionaste es muy amplio, 
                            por lo tanto el link del reporte será enviado a su correo: $mail 
                            dentro de 5 minutos."
                        );
                        return redirect()->back();
                    } else {
                        if ($result) {

                            //\Log::info('result:');
                            //\Log::info($result);
                            //die();

                            return view(
                                'reporting.claro.index'
                            )->with($result);
                        } else {
                            Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                            return redirect()->back();
                        }
                    }
                }
            } else if (isset($input['download'])) {

                //\Log::info('EXPORTAR...');

                ini_set('max_execution_time', 300);
                $report = new ReportServices($input);
                $result = $report->claro_transactionsSearchExport();

                if ($result == false) {
                    $data = [
                        'mode' => 'message',
                        'type' => 'error',
                        'title' => 'Ocurrió un error',
                        'explanation' => 'No se pudieron obtener los datos de la búsqueda.'
                    ];

                    return view('messages.index', compact('data'));
                } else {

                    if (is_array($result)) {
                        $result = json_decode(json_encode($result), true);
                    }

                    $list_ids = [23]; //Lista de ids excepcionales. Id de usuario Wilson Bazan                        
                    $result2 = null;
                    $daterange = explode(' - ',  str_replace('/', '-', $input['reservationtime']));
                    $to = date('Y-m-d H:i:s', strtotime($daterange[0]));
                    $from = date('Y-m-d H:i:s', strtotime($daterange[1]));
                    $days = \DB::select("select ('{$from}'::date - '{$to}'::date) + 1 as days");
                    $days = $days[0]->days;

                    if ($days > 1 and $transaction_id == null) {
                        $query = json_encode([$result, $result2]);

                        \DB::table('select_to_manage')->insert([
                            'description' => $query,
                            'user_id' => $this->user->id,
                            'created_at' => Carbon::now(),
                            'status' => false
                        ]);

                        $mail = $this->user->email;

                        Session::flash(
                            'message',
                            "El rango de fecha que seleccionaste es muy amplio, 
                            por lo tanto el link del reporte será enviado a su correo: $mail 
                            dentro de 5 minutos."
                        );
                        return redirect()->back();
                    } else {

                        //\Log::info('GENERANDO EXCEL...');
                        //\Log::info('Convirtiendo lista de result para el excel... result:', [$result]);

                        $filename = 'transacciones_' . time();
                        $columnas = array(
                            'id', 'Proveedor', 'Tipo', 'Estado', 'Descripcion', 'Fecha', 'Hora', 'Valor Transaccion', 'Cod. Pago', 'Forma pago',
                            'Identificador transaccion', 'Factura Nro', 'Sede', 'Red', 'Ref 1', 'Ref 2', 'Codigo Cajero'
                        );

                        if ($result) {

                                $result_aux = [];

                                foreach ($result as $result_item) {

                                    if ($result_item['service_source_id'] == 0) {
                                        $result_item['proveedor'] = $result_item['provider'];
                                        $result_item['tipo'] = $result_item['servicio'];
                                    }

                                    $row = [
                                        $result_item['id'],
                                        $result_item['proveedor'],
                                        $result_item['tipo'],
                                        $result_item['estado'],
                                        $result_item['estado_descripcion'],
                                        $result_item['fecha'],
                                        $result_item['hora'],
                                        $result_item['valor_transaccion'],
                                        $result_item['cod_pago'],
                                        $result_item['forma_pago'],
                                        $result_item['identificador_transaccion'],
                                        $result_item['factura_nro'],
                                        $result_item['sede'],
                                        $result_item['owner_id'],
                                        $result_item['ref1'],
                                        $result_item['ref2'],
                                        $result_item['codigo_cajero']
                                    ];

                                    array_push($result_aux, $row);
                                }

                                // $excel->sheet('Transacciones', function ($sheet) use ($result_aux) {
                                //     $sheet->rows($result_aux, false);
                                //     $sheet->prependRow(array(
                                //         'id', 'Proveedor', 'Tipo', 'Estado', 'Descripcion', 'Fecha', 'Hora', 'Valor Transaccion', 'Cod. Pago', 'Forma pago',
                                //         'Identificador transaccion', 'Factura Nro', 'Sede', 'Red', 'Ref 1', 'Ref 2', 'Codigo Cajero'
                                //     ));
                                // });

                                $excel = new ExcelExport($result_aux,$columnas);
                                return Excel::download($excel, $filename . '.xls')->send();
                          
                        } else {
                            Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                            return redirect()->back();
                        };
                    }
                }
            }
        } catch (\Exception $e) {
            $error_detail = [
                'from' => 'CMS',
                'message' => '[claro_transactionSearch] Ocurrió un error al realizar la búsqueda en CLARO.',
                'exception' => $e->getMessage(),
                'file' => $e->getFile(),
                'class' => __CLASS__,
                'function' => __FUNCTION__,
                'line' => $e->getLine(),
                'user' => [
                    'user_id' => $this->user->id,
                    'username' => $this->user->username,
                    'description' => $this->user->description
                ]
            ];

            \Log::error($error_detail['message'], [$error_detail]);

            $aux_exception = '';

            if (str_contains($error_detail['exception'], 'SQLSTATE')) {
                $aux_exception = 'Ocurrio un error al querer consultar los datos.';
            } else {
                $aux_exception = $error_detail['exception'];
            }

            $data = [
                'mode' => 'message',
                'type' => 'error',
                'title' => 'Ocurrió un error',
                'explanation' => $aux_exception,
                'error_detail' => $error_detail
            ];

            return view('messages.index', compact('data'));
        }
    }
}
