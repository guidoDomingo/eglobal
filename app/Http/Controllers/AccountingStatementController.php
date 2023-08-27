<?php

/**
 * User: avisconte
 * Date: 25/08/2022
 * Time: 09:20
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\AccountingStatementServices;

class AccountingStatementController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
        $this->service = new AccountingStatementServices();
    }

    /**
     * Función inicial
     */
    public function index(Request $request)
    {
        return $this->service->index($request);
    }

    /**
     * Función obtener detalles del grupo
     */
    public function get_details_per_group(Request $request)
    {
        return $this->service->get_details_per_group($request);
    }

}
