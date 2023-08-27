<?php

namespace App\Http\Controllers\Commissions;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Commissions\ForClientsServices;

class ForClientsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->service = new ForClientsServices();
    }

    /**
     * Función inicial
     */
    public function index(Request $request) {
        return $this->service->index($request);
    }

    /**
     * Obtener servicios por marca por el id del proveedor
     */
    public function get_services_by_brand(Request $request) {
        return $this->service->get_services_by_brand($request);
    }
}