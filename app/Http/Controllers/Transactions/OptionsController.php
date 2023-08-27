<?php

/**
 * User: avisconte
 * Date: 28/06/2022
 * Time: 15:50
 */

namespace App\Http\Controllers\Transactions;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Transactions\OptionsServices;

/**
 * Opciones de botonera
 */
class OptionsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->service = new OptionsServices();
    }

    /**
     * Obtener los servicios para las devoluciones
     */
    public function get_services_for_returns(Request $request)
    {
        return $this->service->get_services_for_returns($request);
    }

    /**
     * Obtener el historial de devoluciones para 1 transacción
     */
    public function get_history_transaction(Request $request)
    {
        return $this->service->get_history_transaction($request);
    }

        /**
     * Obtiene las categorias
     */
    public function get_categories(Request $request)
    {
        return $this->service->get_categories($request);
    }

    /**
     * Obtiene la información del servicio.
     */
    public function cms_get_service_info(Request $request)
    {
        return $this->service->cms_get_service_info($request);
    }

    /**
     * Obtiene la más información del servicio.
     */
    public function cms_get_more_service_info(Request $request)
    {
        return $this->service->cms_get_more_service_info($request);
    }

    /**
     * Confirmación para nivel 1 y 2
     */
    public function cms_confirm(Request $request)
    {
        return $this->service->cms_confirm($request);
    }

    /**
     * Obtener ticket
     */
    public function cms_get_ticket(Request $request)
    {
        return $this->service->cms_get_ticket($request);
    }
}
