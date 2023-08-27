<?php

/**
 * User: avisconte
 * Date: 28/06/2022
 * Time: 15:50
 */

namespace App\Http\Controllers\Transactions;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Transactions\TransactionsServices;

/**
 * Controlador para transacciones
 */
class TransactionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->service = new TransactionsServices();
    }

    /**
     * Informe de transacciones
     */
    public function index(Request $request)
    {
        return $this->service->index($request);
    }

    /**
     * Informe de transacciones
     */
    public function index_devolutions(Request $request)
    {
        return $this->service->index_devolutions($request);
    }

    /**
     * Informe de servicios que requieren más devoluciones.
     */
    public function index_services_with_more_returns(Request $request)
    {
        return $this->service->index_services_with_more_returns($request);
    }


    /**
     * Trae los servicios por proveedor
     */
    public function get_services_by_brand_for_transactions(Request $request)
    {
        return $this->service->get_services_by_brand_for_transactions($request);
    }


    /**
     * Actualizar la transacción de devolución
     */
    public function update_transaction_devolution(Request $request)
    {
        return $this->service->update_transaction_devolution($request);
    }

    /**
     * Relanzar transacción de ONDANET
     */
    public function relaunch_code_by_change(Request $request)
    {
        return $this->service->relaunch_code_by_change($request);
    }
}
