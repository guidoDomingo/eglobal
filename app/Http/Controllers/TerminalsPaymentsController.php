<?php

/**
 * User: avisconte
 * Date: 28/06/2022
 * Time: 15:50
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\TerminalsPaymentsServices;

/**
 * Controlador para transacciones
 */
class TerminalsPaymentsController  extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->service = new TerminalsPaymentsServices();
    }

    /**
     * Informe de transacciones
     */
    public function index(Request $request)
    {
        return $this->service->index($request);
    }

    /**
     * Obtener terminales por grupo
     */
    public function get_atms_per_group(Request $request)
    {
        return $this->service->get_atms_per_group($request);
    }

    public function terminals_payments_relaunch_receipt(Request $request)
    {
        return $this->service->terminals_payments_relaunch_receipt($request);
    }
}
