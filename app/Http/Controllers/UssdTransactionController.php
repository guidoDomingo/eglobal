<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\UssdTransactionServices;

class UssdTransactionController extends Controller
{
    /**
     * @var class $user: Usuario
     * @global object 
     */
    protected $user;
    private $ussd_transaction_service;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
        $this->ussd_transaction_service = new UssdTransactionServices();
    }

    /**
     * Búsqueda inicial para la pantalla de trancciones de ussd.
     *
     * @return \Illuminate\Http\Response
     */
    public function ussd_transaction_report(Request $request)
    {
        return $this->ussd_transaction_service->ussd_transaction_report($request);
    }

    /**
     * Relanza todas las transacciones fallidas.
     *
     * @return \Illuminate\Http\Response
     */
    public function ussd_transaction_relaunch(Request $request)
    {
        return $this->ussd_transaction_service->ussd_transaction_relaunch($request, $this->user->id);
    }

    /**
     * Búsqueda personalizada
     *
     * @return \Illuminate\Http\Response
     */
    public function ussd_transaction_search(Request $request)
    {
        return $this->ussd_transaction_service->ussd_transaction_search($request);
    }

    /**
     * Metodo para modificar la transacción
     *
     * @return \Illuminate\Http\Response
     */
    public function ussd_transaction_edit(Request $request)
    {
        return $this->ussd_transaction_service->ussd_transaction_edit($request, $this->user->id);
    }

    /**
     * Obtener lista de razones para volver hacer la carga
     *
     * @return \Illuminate\Http\Response
     */
    public function ussd_reason()
    {
        return $this->ussd_transaction_service->ussd_reason();
    }

    /**
     * Obtener las sucursales
     *
     * @return \Illuminate\Http\Response
     */
    public function ussd_branch()
    {
        return $this->ussd_transaction_service->ussd_branch();
    }

    /**
     * Obtener lista de tipo recargas.
     *
     * @return \Illuminate\Http\Response
     */
    public function ussd_recharge_type()
    {
        return $this->ussd_transaction_service->ussd_recharge_type();
    }

    /**
     * Lista de estados
     *
     * @return \Illuminate\Http\Response
     */
    public function ussd_status()
    {
        return $this->ussd_transaction_service->ussd_status();
    }

    /**
     * Lista de atms
     *
     * @return \Illuminate\Http\Response
     */
    public function ussd_atms()
    {
        return $this->ussd_transaction_service->ussd_atms();
    }

    /**
     * Lista de atms
     *
     * @return \Illuminate\Http\Response
     */
    public function ussd_service()
    {
        return $this->ussd_transaction_service->ussd_service();
    }
}
