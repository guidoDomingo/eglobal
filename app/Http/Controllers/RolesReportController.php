<?php

/**
 * User: avisconte
 * Date: 16/02/2023
 * Time: 16:20
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\RolesReportServices;

/**
 * Controlador para transacciones
 */
class RolesReportController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->service = new RolesReportServices();
    }

    /**
     * Informe de transacciones
     */
    public function index_report(Request $request)
    {
        return $this->service->index_report($request);
    }

    public function get_roles_permissions(Request $request) 
    {
        return $this->service->get_roles_permissions($request);
    }

}
