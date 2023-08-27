<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\UssdOperatorServices;

class UssdOperatorController extends Controller
{
    /**
     * @var class $user: Usuario
     * @global object 
     */
    protected $user;
    private $ussd_operator_service;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
        $this->ussd_operator_service = new UssdOperatorServices();
    }

    public function ussd_operator_report() {
        return $this->ussd_operator_service->ussd_operator_report();
    }

    /**
     * Metodo para modificar el operador.
     *
     * @return \Illuminate\Http\Response
     */
    public function ussd_operator_set_status(Request $request)
    {
        return $this->ussd_operator_service->ussd_operator_set_status($request, $this->user->id);
    }

    /**
     * Metodo para modificar el operador.
     *
     * @return \Illuminate\Http\Response
     */
    public function ussd_operator_get_by_description($description)
    {
        return $this->ussd_operator_service->ussd_operator_get_by_description($description);
    }
}