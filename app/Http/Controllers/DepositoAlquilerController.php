<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Requests\DepositoAlquilerRequest;
use App\Http\Controllers\Controller;
use App\Models\DepositoAlquiler;
use App\Models\CuentaBancaria;
use App\Services\DepositoAlquilerServices;
use App\Models\mt_recibos_pagos_miniterminales;
use Carbon\Carbon;
use Session;

class DepositoAlquilerController extends Controller
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
        if (!$this->user->hasAccess('depositos_cuotas')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $name = $request->get('name');
        $depositocuotas = mt_recibos_pagos_miniterminales::filterAndPaginate($name);
        //dd($depositocuotas);

        return view('depositos_alquileres.index', compact('depositocuotas', 'name'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!$this->user->hasAccess('depositos_cuotas.add')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if (!\Sentinel::getUser()->inRole('mini_terminal') && !\Sentinel::getUser()->inRole('supervisor_miniterminal')) {

            if ($this->user->hasAccess('depositos_cuotas.add')) {

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
                ->join('alquiler', 'users_x_groups.group_id', '=', 'alquiler.group_id')
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

            $atm = \DB::table('atms')
                ->select('atms.id', 'atms.name', 'bg.id as group_id')
                ->join('atms_per_users', 'atms.id', '=', 'atms_per_users.atm_id')
                ->join('points_of_sale as pos', 'atms.id', '=', 'pos.atm_id')
                ->join('branches', 'branches.id', '=', 'pos.branch_id')
                ->join('business_groups as bg', 'bg.id', '=', 'branches.group_id')
                ->where('atms_per_users.user_id', $this->user->id)
                ->whereIn('atms.owner_id', [16, 21, 25])
                ->orderBy('atms.id', 'asc')
                ->first();
                

            $atms = \DB::table('atms')
                ->select('atms.id', 'atms.name')
                ->join('atms_per_users', 'atms.id', '=', 'atms_per_users.atm_id')
                ->join('alquiler_housing', 'atms.housing_id', '=', 'alquiler_housing.housing_id')
                ->join('alquiler', 'alquiler.id', '=', 'alquiler_housing.alquiler_id')
                ->where('atms_per_users.user_id', $this->user->id)
                ->where('alquiler.group_id', $atm->group_id)
                ->whereIn('atms.owner_id', [16, 21, 25])
                ->orderBy('atms.id', 'asc')
                ->pluck('name', 'id');
        }

        $tipo_pago = \DB::table('tipo_pago')->where('id', '!=', 3)->orderBy('descripcion', 'asc')->pluck('descripcion', 'id');

        $bancos = [];

        $cuentas = [];

        if (empty($atms)) {
            Session::flash('error_message', 'Ha ocurrido un error al intentar encontrar las cuotas a afectar');
            return redirect('depositos_cuotas');
        }

        return view('depositos_alquileres.create', compact('tipo_pago', 'bancos', 'cuentas', 'atms'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(DepositoAlquilerRequest $request)
    {
        if (!$this->user->hasAccess('depositos_cuotas.add')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = $request->all();
        $atm_id = $input['atm_id'];
        $fecha = $input['fecha'];
        $tipo_pago_id = $input['tipo_pago_id'];
        $cuenta_bancaria_id = $input['cuenta_bancaria_id'];
        $boleta_numero = $input['boleta_numero'];
        $monto = $input['monto'];
        $cuota_monto = $input['cuota_monto'];
        $tipo_recibo_id = 2;
        $user_id = $this->user->id;
        $user_description = $this->user->description;
        $message = '';

        // Obtener le id de banco.
        $cuenta_bancaria = \DB::table('cuentas_bancarias')
            ->select(
                'banco_id'
            )
            ->where('id', $input['cuenta_bancaria_id'])
            ->get();

        $cuenta_bancaria = $cuenta_bancaria[0];
        $banco_id = $cuenta_bancaria->banco_id;

        /**
         * Validación para las boletas de depósitos que se encuentan en transacciones:
         * Si hay la misma boleta por transacciones le va saltar la validación
         */
        $deposito = \DB::table('bancos as b')
            ->select(
                'bd.id'
            )
            ->join('cuentas_bancarias as cb', 'b.id', '=', 'cb.banco_id')
            ->join('boletas_depositos as bd', 'cb.id', '=', 'bd.cuenta_bancaria_id')
            ->where('b.id', $banco_id)
            ->where('cb.id', $cuenta_bancaria_id)
            ->where('bd.boleta_numero', $boleta_numero)
            ->where('bd.monto', $monto)
            ->where('bd.estado', true)
            ->whereRaw('bd.deleted_at is null')
            ->get();

        if (count($deposito) <= 0) {

            /**
             * Validación para las boletas de depósitos que se encuentan en cuotas de ventas o alquiler:
             * Si hay la misma boleta por cuotas le va saltar la validación
             */
            $mt_recibos_pagos_miniterminales = \DB::table('bancos as b')
                ->select(
                    'mt.boleta_numero'
                )
                ->join('cuentas_bancarias as cb', 'b.id', '=', 'cb.banco_id')
                ->join('mt_recibos_pagos_miniterminales as mt', 'cb.id', '=', 'mt.cuenta_bancaria_id')
                ->where('b.id', $banco_id)
                ->where('cb.id', $cuenta_bancaria_id)
                ->where('mt.boleta_numero', $boleta_numero)
                ->where('mt.monto', $monto)
                ->where('mt.tipo_recibo_id', $tipo_recibo_id)
                ->whereRaw('mt.deleted_at is null')
                ->get();

            if (count($mt_recibos_pagos_miniterminales) <= 0) {

                $cant_cuotas = $monto / $cuota_monto;

                if ($cant_cuotas === 1) {

                    try {

                        $mt_recibos_pagos_miniterminales_insert = [
                            'boleta_numero' => $boleta_numero,
                            'cuenta_bancaria_id' => $cuenta_bancaria_id,
                            'fecha' => $fecha,
                            'tipo_pago_id' => $tipo_pago_id,
                            'user_id' => $user_id,
                            'monto' => $monto,
                            'tipo_recibo_id' => $tipo_recibo_id,
                            'atm_id' => $atm_id
                        ];

                        \Log::info('[CMS CUOTA DE ALQUILER] mt_recibos_pagos_miniterminales (insert)', [$mt_recibos_pagos_miniterminales_insert]);

                        mt_recibos_pagos_miniterminales::create($mt_recibos_pagos_miniterminales_insert);

                        //PARA ENVIAR CORREO
                        $notifications  = new DepositoAlquilerServices();
                        $response = $notifications::sendAlerts($fecha, $tipo_pago_id, $banco_id, $cuenta_bancaria_id, $boleta_numero, $monto, $user_description);

                        \Log::info('[CMS CUOTA DE ALQUILER] notifications::sendAlerts', [$response]);
                    } catch (\Exception $e) {

                        $message = "Ocurrió un error al intentar guardar el registro.";

                        $error_detail = [
                            'exception' => $e->getMessage(),
                            'file' => $e->getFile(),
                            'class' => __CLASS__,
                            'function' => __FUNCTION__,
                            'line' => $e->getLine()
                        ];

                        \Log::error("Error saving new Deposit Cuota: " . json_encode($error_detail));
                    }
                } else {
                    $message = "El monto colocado es un monto inválido, favor verificar si cubre totalmente UNA SOLA CUOTA";
                }
            } else {
                $message = "Ya hay una boleta de depósito en cuotas con los mismos datos.";
            }
        } else {
            $message = "Ya hay una boleta de depósito en transacciones con los mismos datos.";
        }

        if ($message !== '') {
            \Log::error($message, [$input]);
            Session::flash('error_message', $message);
            return redirect()->back()->withInput();
        } else {
            \Log::info('Deposito de cuota ejecutado correctamente.');
            Session::flash('message', 'Registro creado exitosamente.');
            return redirect('depositos_alquileres');
        }
    }

    public function getCuentasbyBancos($banco_id)
    {
        if (!$this->user->hasAccess('depositos_cuotas.add')) {
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

    public function getBankAccounts($bank_id)
    {

        $bank_accounts = \DB::table('bancos as b')
            ->select('cb.id', \DB::raw("b.descripcion || ' - N°. de Cuenta: ' || cb.numero_banco as numero_banco"))
            ->join('cuentas_bancarias as cb', 'b.id', '=', 'cb.banco_id')
            ->where('b.id', $bank_id)
            ->whereRaw('cb.deleted_at is null')
            ->orderBy('b.descripcion', 'asc')
            ->get();

        return ($bank_accounts);
    }

    public function getPaymentTypePerUser($payment_type_id)
    {

        $bank = [];
        $bank_account = [];

        if ($payment_type_id == "1") { //Transferencia
            $bank = \DB::table('bancos as b')
                ->select('b.id', 'b.descripcion')
                ->join('branches as br', 'b.id', '=', 'br.bank_id')
                ->where('br.user_id', $this->user->id)
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

        if (!$this->user->hasAccess('depositos_cuotas.add')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if ($depo = mt_recibos_pagos_miniterminales::find($id)) {
            \Log::warning($depo);
            try {

                $check = new DepositoAlquilerServices();
                $response = $check->insertCuotas_v2($id);
                \Log::warning($response);

                $error = $response['error'];
                $message = $response['message'];

                if (!$response['error']) {
                    Session::flash('message', "Registro $id confirmado exitosamente");
                } else {
                    Session::flash('error_message', 'Ha ocurrido un error al intentar guardar el registro');
                }
            } catch (\Exception $e) {
                \Log::error("Error sending Cobranzas Cuotas  - {$e->getMessage()}");
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!$this->user->hasAccess('depositos_cuotas.add')) {
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

        if ($deposito = DepositoAlquiler::find($id)) {
            try {

                \DB::table('recibo_alquiler')
                    ->where('id', $id)
                    ->update([
                        'deleted_at' => Carbon::now(),
                        'estado'  => false,
                        'updated_by'   => $this->user->id
                    ]);
                $error = false;
                $message =  'Registro guardado exitosamente';
            } catch (\Exception $e) {
                \Log::error("Error deleting Cuota Alquiler: " . $e->getMessage());
                $message =  'Error al intentar eliminar el registro cuota alquiler';
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
        \Log::info("Intentando eliminar el registro " . $id);

        if ($deposito = mt_recibos_pagos_miniterminales::find($id)) {
            try {

                \DB::table('mt_recibos_pagos_miniterminales')
                    ->where('id', $id)
                    ->update([
                        'deleted_at'    => Carbon::now(),
                        'estado'        => false,
                        'updated_by'    => $this->user->id,
                        'message'       => $description
                    ]);
                $error = false;
                $message =  'Registro guardado exitosamente';
            } catch (\Exception $e) {
                \Log::error("Error deleting Cuota: " . $e->getMessage());
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

    public function get_cuotas($atm_id)
    {
        $atm = \DB::table('atms')
            ->select('atms.id as atm_id', 'atms.name', 'bg.id as group_id')
            ->join('points_of_sale as pos', 'atms.id', '=', 'pos.atm_id')
            ->join('branches', 'branches.id', '=', 'pos.branch_id')
            ->join('business_groups as bg', 'bg.id', '=', 'branches.group_id')
            ->where('atms.id', $atm_id)
            ->whereIn('atms.owner_id', [16, 21, 25])
            ->first();

        $cuota = \DB::table('cuotas_alquiler')
            ->select('cuotas_alquiler.*')
            ->join('alquiler', 'alquiler.id', '=', 'cuotas_alquiler.alquiler_id')
            ->join('alquiler_housing', 'cuotas_alquiler.alquiler_id', '=', 'alquiler_housing.alquiler_id')
            ->join('atms', 'alquiler_housing.housing_id', '=', 'atms.housing_id')
            ->where('atms.id', $atm_id)
            ->where('alquiler.group_id', $atm->group_id)
            ->where('cuotas_alquiler.saldo_cuota', '!=', 0)
            ->orderBy('cuotas_alquiler.num_cuota', 'ASC')
            ->first();

        $data = [
            "cuota_monto" => $cuota->saldo_cuota,
            "cuota_numero" => $cuota->num_cuota
        ];

        \Log::info('Data', $data);

        return $data;
    }
}
