<?php

namespace App\Http\Controllers;

use App\Http\Requests\DepositoBoletaRequest;
use App\Services\DepositoBoletaServices;
use App\Models\DepositoBoleta;
use App\Models\Permission;
use App\Models\TipoPago;
use App\Models\CuentaBancaria;
use App\Services\OndanetServices;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Session;
use Illuminate\Support\Facades\Storage;

class DepositoBoletaController extends Controller
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
        if (!$this->user->hasAccess('depositos_boletas')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $name = $request->get('name');
        $depositoboletas = DepositoBoleta::filterAndPaginate($name);

        return view('depositos_boletas.index', compact('depositoboletas', 'name'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!$this->user->hasAccess('depositos_boletas.add')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $tipo_pago = \DB::table('tipo_pago')
            ->where('id', '!=', 3)
            ->orderBy('id', 'asc')
        ->pluck('descripcion', 'id');

        $bancos = [];
        $cuentas = [];
        $atms = [];

        if (!\Sentinel::getUser()->inRole('mini_terminal') && !\Sentinel::getUser()->inRole('supervisor_miniterminal')) {

            if ($this->user->hasAccess('depositos_boletas.add')) {

                $atms = \DB::table('atms')
                ->selectRaw("atms.id, ('#' || atms.id || ' | ' || atms.name) as name")
                ->whereIn('atms.owner_id', [16, 21, 25])
                ->orderBy('atms.id', 'asc')
                ->pluck('name', 'id');
                
            }else{
                \Log::error(
                    'Unauthorized access attempt',
                    ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
                );
                Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
                return redirect('depositos_boletas');
            }
            
        } else if (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {

            $atms = \DB::table('atms')
                ->select('atms.id', 'atms.name')
                ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->join('users_x_groups', 'branches.group_id', '=', 'users_x_groups.group_id')
                ->where('users_x_groups.user_id', $this->user->id)
                ->whereIn('atms.owner_id', [16, 21, 25])
                ->orderBy('atms.id', 'asc')
                ->pluck('name', 'id');

            if (!isset($atms)) {
                \Log::error(
                    'Unauthorized access attempt',
                    ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
                );
                Session::flash('error_message', 'EL cliente no tiene ATMs asignados');
                return redirect('depositos_boletas');
            }
        } else {
            $atms = \DB::table('atms')
                ->select('atms.id', 'atms.name')
                ->join('atms_per_users', 'atms.id', '=', 'atms_per_users.atm_id')
                ->where('atms_per_users.user_id', $this->user->id)
                ->whereIn('atms.owner_id', [16, 21, 25])
                ->orderBy('atms.id', 'asc')
                ->pluck('name', 'id');
        }

        return view('depositos_boletas.create', compact('tipo_pago', 'bancos', 'cuentas', 'atms'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(DepositoBoletaRequest $request)
    {
        if (!$this->user->hasAccess('depositos_boletas.add')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
        }

        $input = $request->all();

        //\Log::info('inputs banks:', [$input]);

        $bancos = \DB::table('bancos')
            ->select('bancos.descripcion')
            ->join('branches', 'bancos.id', '=', 'branches.bank_id')
            ->where('branches.user_id', $this->user->id)
            ->orderBy('descripcion', 'asc')->pluck('descripcion', 'bancos.id');

        if (!empty($bancos)) {
            $banco = \DB::table('bancos')
                ->select('bancos.*')
                ->join('branches', 'bancos.id', '=', 'branches.bank_id')
                ->where('branches.user_id', $this->user->id)
                ->orderBy('descripcion', 'asc')->first();

            $input['banco_id'] = $banco->id;
        }

        $date1 = date_create(Carbon::today());
        $date2 = date_create($input['fecha']);
        $diff = date_diff($date1, $date2);

        if ($diff->days <= 10) {

            //if ($deposito = DepositoBoleta::where('boleta_numero', $input['boleta_numero'])->count() == 0) {

            $deposito = \DB::table('bancos as b')
                ->select(
                    'bd.id'
                )
                ->join('cuentas_bancarias as cb', 'b.id', '=', 'cb.banco_id')
                ->join('boletas_depositos as bd', 'cb.id', '=', 'bd.cuenta_bancaria_id')
                ->where('b.id', $input['banco_id'])
                ->where('cb.id', $input['cuenta_bancaria_id'])
                ->where('bd.boleta_numero', $input['boleta_numero'])
                ->where('bd.monto', $input['monto'])
                ->where('bd.estado', true)
                ->whereRaw('bd.deleted_at is null')
                ->get();

            if (count($deposito) <= 0) {

                $mt_recibos_pagos_miniterminales = \DB::table('bancos as b')
                    ->select(
                        'mt.boleta_numero'
                    )
                    ->join('cuentas_bancarias as cb', 'b.id', '=', 'cb.banco_id')
                    ->join('mt_recibos_pagos_miniterminales as mt', 'cb.id', '=', 'mt.cuenta_bancaria_id')
                    ->where('b.id', $input['banco_id'])
                    ->where('cb.id', $input['cuenta_bancaria_id'])
                    ->where('mt.boleta_numero', $input['boleta_numero'])
                    ->where('mt.monto', $input['monto'])
                    ->whereRaw('mt.deleted_at is null')
                    ->whereRaw('mt.tipo_recibo_id in (1,2)')
                    ->get();

                if (count($mt_recibos_pagos_miniterminales) <= 0) {

                    //Se procede a procesar la imagen
                    if (!empty($input['imagen_asociada'])) {
                        $imagen = $input['imagen_asociada'];
                        $data_imagen = json_decode($imagen);

                        if ($data_imagen->type != 'image/png' || $data_imagen->type != 'image/jpg' || $data_imagen->type != 'image/jpeg') {
                            Session::flash('error_message', 'El formato de la imagen es invalido');
                            return redirect()->back()->withInput();
                            \Log::info('El formato de la imagen es invalido.');
                        }
                        //$nombre_imagen = $data_imagen->name;
                        $fecha_imagen = date('d_m_Y_H_i_s');
                        $nombre_imagen = $fecha_imagen . '_' . $data_imagen->name; //NOMBRE DEL ARCHIVO

                        //Se guarda el archivo en la ruta eglobaltCMS/public/resources/images/boleta_deposito
                        Storage::disk('boleta_deposito')->put($nombre_imagen,  base64_decode($data_imagen->data));

                        //Nos conectamos al FTP
                        $boletas_egl_sftp_server = env('BOLETAS_EGL_SFTP_SERVER');
                        $boletas_egl_sftp_port = env('BOLETAS_EGL_SFTP_PORT');
                        $boletas_egl_sftp_user_name = env('BOLETAS_EGL_SFTP_USER_NAME');
                        $boletas_egl_sftp_user_password = env('BOLETAS_EGL_SFTP_USER_PASSWORD');
                        $boletas_egl_sftp_folder = env('BOLETAS_EGL_SFTP_FOLDER');

                        //Ubicacion del archivo
                        $url_temp = public_path() . '/resources/images/boleta_deposito';

                        $file_path = "$url_temp/$nombre_imagen";

                        $base_name_data_file = basename($file_path);

                        $sftp_url = "sftp://$boletas_egl_sftp_server:$boletas_egl_sftp_port/$boletas_egl_sftp_folder/$base_name_data_file";
                        $sftp_user_and_password = "$boletas_egl_sftp_user_name:$boletas_egl_sftp_user_password";

                        $ch = curl_init($sftp_url);

                        /**
                         * Se abre al archivo para ser leido
                         */

                        $fh = fopen($file_path, 'r');

                        if ($fh) {

                            curl_setopt($ch, CURLOPT_USERPWD, $sftp_user_and_password);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
                            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
                            curl_setopt($ch, CURLOPT_UPLOAD, true);
                            curl_setopt($ch, CURLOPT_PROTOCOLS, CURLPROTO_SFTP);
                            curl_setopt($ch, CURLOPT_INFILE, $fh);
                            curl_setopt($ch, CURLOPT_INFILESIZE, filesize($file_path));
                            curl_setopt($ch, CURLOPT_VERBOSE, true);

                            $verbose = fopen('php://temp', 'w+');
                            curl_setopt($ch, CURLOPT_STDERR, $verbose);

                            $response = curl_exec($ch);
                            $error = curl_errno($ch);

                            curl_close($ch);

                            if ($response) {
                                $date = date('d/m/Y');
                                $time = date('H:i:s');
                                $now = "el $date a las $time.";

                                \Log::info("Archivo guardado en el sftp de boleta eglobalt $now");
                                fclose($fh);
                                \Log::info("Archivo cerrado $now");
                                unlink($file_path);
                                \Log::info("Archivo eliminado $now");
                            } else {
                                rewind($verbose);
                                $verbose_log = stream_get_contents($verbose);

                                $error_detail = [
                                    'message' => 'Archivo no guardado en el sftp de boleta eglobalt.',
                                    'verbose_log' => $verbose_log,
                                    'response_ftp' => $response,
                                    'error' => $error
                                ];

                                \Log::error("\nError en " . __FUNCTION__ . ": \nDetalles: " . json_encode($error_detail));
                            }

                            \Log::info("Archivo procesado el $now");
                        }


                        $input['imagen_asociada'] = $nombre_imagen;
                    }

                    if (\Sentinel::getUser()->inRole('supervisor_miniterminal')) {
                        $users = \DB::table('users_x_groups')->where('user_id', $this->user->id)->first();
                    } else {
                        $users = \DB::table('branches')->where('user_id', $this->user->id)->first();
                    }

                    $input['user_id'] = $this->user->id;
                    $input['atm_id'] = (int)$input['atm_id'];
                    //dd($input);
                    \DB::beginTransaction();
                    try {
                        // TODO Ondanet
                        if (DepositoBoleta::create($input)) {

                            try {

                                //PARA ENVIAR CORREO

                                $fecha = $input['fecha'];
                                $tipo_pago = $input['tipo_pago_id'];
                                $getbanco = \DB::table('cuentas_bancarias')->where('id', $input['cuenta_bancaria_id'])->first();
                                $banco = $getbanco->banco_id;
                                $cuenta = $input['cuenta_bancaria_id'];
                                $nroboleta = $input['boleta_numero'];
                                $monto = $input['monto'];
                                $atm_id = $input['atm_id'];
                                $depositado = $this->user->description;
                                $notifications  = new DepositoBoletaServices();

                                $response = $notifications::sendAlerts($fecha, $tipo_pago, $banco, $cuenta, $nroboleta, $monto, $depositado);
                                \Log::info($response);

                                \DB::commit();
                                Session::flash('message', 'Registro creado exitosamente');
                                return redirect('depositos_boletas');
                            } catch (\Exception $e) {
                                \DB::rollback();
                                \Log::error("Error sending email  - {$e->getMessage()}");
                                Session::flash('message', 'Registro creado exitosamente');
                                return redirect('depositos_boletas');
                            }
                        } else {
                            \DB::rollback();
                            Session::flash('error_message', 'Ocurrio un error al intentar guardar el registro');
                            return redirect()->back()->withInput();
                            \Log::info('This is some useful information.');
                        }
                    } catch (\Exception $e) {
                        \DB::rollback();
                        \Log::error("Error saving new Deposit Ticket - {$e->getMessage()}");
                        Session::flash('error_message', 'Ocurrio un error al intentar guardar el registro');
                        return redirect()->back()->withInput();
                    }
                } else {
                    Session::flash('error_message', 'La combinación de banco, número de boleta y monto ya se encuentra en depósito de cuota.');
                    return redirect()->back()->withInput();
                }
            } else {
                Session::flash('error_message', 'La combinación de banco, número de boleta y monto ya se encuentra en depósito de transacciones.');
                return redirect()->back()->withInput();
            }
        } else {
            Session::flash('error_message', 'Favor colocar una fecha de boleta valida (No mas de 5 dias de diferencia con la fecha de hoy)');
            return redirect()->back()->withInput();
        }
    }

    public function getCuentasbyBancos($banco_id)
    {
        if (!$this->user->hasAccess('depositos_boletas.add')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        //$branches = Branch::where('owner_id',$owner_id,'')->pluck('description','id');
        $cuentas = CuentaBancaria::orderBy('numero_banco', 'ASC')->where('banco_id', $banco_id, '')->pluck('numero_banco', 'id');
        $cuentas->prepend('Seleccione una opcion', '0');


        return ($cuentas);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */


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
    public function migrate(Request $request)
    {
        $id = $request->_id;

        if (!$this->user->hasAccess('depositos_boletas.add')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if ($depo = DepositoBoleta::find($id)) {

            try {
                \DB::table('boletas_depositos')
                    ->where('id', $id)
                    ->update([
                        'updated_at' => Carbon::now(),
                        'estado'  => true,
                        'conciliado' => true,
                        'updated_by'   => $this->user->id
                    ]);

                $error  =   "Registro $id confirmado exitosamente";
                $message =   "Registro $id confirmado exitosamente";
                \Log::info("Registro $id confirmado exitosamente");

                Session::flash('message', "Registro $id confirmado exitosamente");

                $service  = new DepositoBoletaServices();
                $cobranzas = $service->insertCobranzas_V2($id);
                \Log::info('miniterminales:insertCobranzas', ['boleta_id' => $id, 'result' => $cobranzas]);
                if ($cobranzas['error'] == false) {
                    //update de campo conciliado
                    $conciliado = \DB::table('boletas_depositos')
                        ->where('id', $id)
                        ->update(['conciliado' => true]);
                } else {
                    $conciliado = \DB::table('boletas_depositos')
                        ->where('id', $id)
                        ->update(['conciliado' => false]);

                    $boleta = \DB::table('boletas_depositos')->where('id', $id)->first();
                    $data = [
                        'user_name'    => 'Tesorería',
                        'fecha'        => $boleta->fecha,
                        'nroboleta'    => $boleta->boleta_numero,
                        'monto'        => number_format($boleta->monto, 0),
                        'boleta'       => $boleta->id
                    ];

                    Mail::send(
                        'mails.alert_cobranzas',
                        $data,
                        function ($message) {
                            $user_email = 'sistemas@eglobalt.com.py';
                            $user_name  = 'Admin';
                            $message->to($user_email, $user_name)->subject('[EGLOBAL] Alerta de Miniterminales Cobranzas');
                        }
                    );
                }
            } catch (\Exception $e) {
                \Log::error("Error sending Cobranzas Miniterminales  - {$e->getMessage()}");
                $error = true;
                $message =  'Error al intentar migrar la operacion';
                Session::flash('error_message', 'Ha ocurrido un error al intentar guardar el registro');
            }
        } else {
            Log::error('No se encontro la operacion | Id: ' . $id);
            $message =  'Error al intentar migrar la operacion';
            $error = true;
            Session::flash('error_message', 'No se encontro la operacion | Id: ' . $id);
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }

    public function conciliationsDetails(Request $request)
    {
       
        if (!$this->user->hasAccess('depositos_boletas.conciliations')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        try {
            $begin = new Carbon('first day of this month 00:00:00');
            $end   = new Carbon('last day of this month 23:59:59');

            $deposits = \DB::table('deposits')
                ->select('deposits.*', 'boletas_depositos.monto as monto', 'boletas_depositos.boleta_numero as nroboleta', 'tipo_pago.descripcion as description', 'users.username as username', 'branches.description as sucursal')
                //->whereBetween('invoices.created_at',[$begin,$end])
                ->whereIn('destination_operation_id', ['0', '-1', '-2', '-3', '-14', '-23', '212'])
                ->join('boletas_depositos', 'boletas_depositos.id', '=', 'deposits.boleta_deposito_id')
                ->leftjoin('tipo_pago', 'tipo_pago.id', '=', 'boletas_depositos.tipo_pago_id')
                ->leftjoin('users', 'users.id', '=', 'boletas_depositos.user_id')
                ->leftjoin('branches', 'branches.user_id', '=', 'users.id')
                ->get();

            foreach ($deposits as $deposit) {
                if ($deposit->response <> null) {
                    $message = json_decode($deposit->response);
                    $deposit->response = $message->message;
                }
            }

            $name = $request->get('name');
            $depositoboletas = DepositoBoleta::filterAndPaginate($name);
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de servicio dash / Conciliaciones: " . $e);
        }

        return view('depositos_boletas.conciliations', compact('deposits', 'depositoboletas', 'name'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!$this->user->hasAccess('depositos_boletas.add')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $message = '';
        $error = '';
        \Log::debug("Intentando eliminar el registro " . $id);

        if ($deposito = DepositoBoleta::find($id)) {
            try {

                \DB::table('boletas_depositos')
                    ->where('id', $id)
                    ->update([
                        'deleted_at' => Carbon::now(),
                        'estado'  => false,
                        'updated_by'   => $this->user->id
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

    public function delete(Request $request)
    {
        if (!$this->user->hasAccess('depositos_boletas.add')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $id = $request->_id;
        $description = $request->_description;

        if (empty($description)) {
            return response()->json([
                'error' => true,
                'message' => 'El mensaje de rechazo esta vacio'
            ]);
        }

        \Log::info($request->_id);
        \Log::info($request->_description);

        $message = '';
        $error = '';
        \Log::debug("Intentando eliminar el registro " . $id);

        if ($deposito = DepositoBoleta::find($id)) {
            try {

                $monto = 0;
                $monto_anterior = $deposito->monto;

                $boletas_depositos_update = [
                    'deleted_at' => Carbon::now(),
                    'estado' => false,
                    'updated_by' => $this->user->id,
                    'message' => $description,

                    'monto' => $monto,
                    'monto_anterior' => $monto_anterior
                ];

                \Log::info("El depósito de boleta con ID: $id fué actualizado con los siguientes datos:", [$boletas_depositos_update]);

                \DB::table('boletas_depositos')
                    ->where('id', $id)
                    ->update($boletas_depositos_update);

                $error = false;
                $message =  'Registro guardado exitosamente';
            } catch (\Exception $e) {
                \Log::error("Error deleting network: " . $e->getMessage());
                $message = 'Error al intentar eliminar el registro';
                $error = true;
            }
        } else {
            $message = 'Registro no encontrado';
            $error = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }

    public function getBankAccounts($bank_id)
    {
        if (!$this->user->hasAccess('depositos_boletas.add')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $bank_accounts = \DB::table('bancos as b')
            ->select('cb.id', \DB::raw("b.descripcion || ' - N°. de Cuenta: ' || cb.numero_banco as numero_banco"))
            ->join('cuentas_bancarias as cb', 'b.id', '=', 'cb.banco_id')
            ->where('b.id', $bank_id)
            ->whereRaw('cb.deleted_at is null')
            ->orderBy('b.descripcion', 'asc')
            ->get();

        return ($bank_accounts);
    }

    public function getPaymentTypePerUser($payment_type_id, $atm_id)
    {
        if (!$this->user->hasAccess('depositos_boletas.add')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $bank = [];
        $bank_account = [];

        if ($payment_type_id == "1") { //Transferencia
            $bank = \DB::table('bancos as b')
                ->select('b.id', 'b.descripcion')
                ->join('branches as br', 'b.id', '=', 'br.bank_id')
                ->join('points_of_sale as pos', 'b.id', '=', 'pos.branch_id')
                ->where('pos.atm_id', $atm_id)
                ->orderBy('b.descripcion', 'asc')
                ->get();

            if (count($bank) > 0) {
                $bank_id = $bank[0]->id;

                $bank_account = \DB::table('cuentas_bancarias as cb')
                    ->select('cb.id', 'cb.numero_banco')
                    ->join('bancos as b', 'b.id', '=', 'cb.banco_id')
                    ->where('b.id', $bank_id)
                    ->whereRaw('cb.deleted_at is null')
                    ->orderBy('b.descripcion', 'asc')
                    ->get();
            } else {
                $bank = \DB::table('bancos')
                    ->select('id', 'descripcion')
                    ->orderBy('descripcion', 'asc')
                    ->get();
            }
        } else if ($payment_type_id == "2") {  //Depósito En Cuenta
            $bank = \DB::table('bancos')
                ->select('id', 'descripcion')
                ->orderBy('descripcion', 'asc')
                ->get();
        }

        $data = [
            "bank" => $bank,
            "bank_account" => $bank_account
        ];

        \Log::info('Data', $data);

        return $data;
    }

    public function getAtmPerGroup($group_id)
    {
        // if (!$this->user->hasAccess('depositos_boletas.add')) {
        //     \Log::error(
        //         'Unauthorized access attempt',
        //         ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
        //     );
        //     Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
        //     return redirect('/');
        // }

        \Log::info('GRUPO ID: ' . $group_id);

        $atms = \DB::table('atms as a')
            ->select('a.id', 'a.name')
            ->join('points_of_sale as pos', 'pos.atm_id', '=', 'a.id')
            ->join('branches as b', 'b.id', '=', 'pos.branch_id')
            ->join('business_groups as bg', 'bg.id', '=', 'b.group_id')
            ->whereNull('a.deleted_at')
            ->whereNotNull('a.last_token')
            ->whereNull('bg.deleted_at')
            ->where('bg.id', $group_id)
            ->orderBy('a.name', 'asc')
            ->get();

        $data = [
            "atms" => $atms,
        ];

        \Log::info('Data atms', $data);

        return $data;
    }
}
