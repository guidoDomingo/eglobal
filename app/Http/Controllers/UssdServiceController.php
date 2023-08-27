<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\UssdServiceServices;

class UssdServiceController extends Controller
{
    /**
     * @var class $user: Usuario
     * @global object 
     */
    protected $user;
    private $ussd_service_service;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
        $this->ussd_service_service = new UssdServiceServices();
    }

    /**
     * Informe que muestra los teléfonos con saldo actual
     *
     * @return \Illuminate\Http\Response
     */
    public function ussd_service_report(Request $request)
    {
        $list = $this->ussd_service_service->ussd_service_report();

        $data = [
            "open_modal" => "no",
            "list"       => $list,
            "date"       => null,
            "method"     => "index"
        ];

        return view('ussd.ussd_service_report', compact('data'));
    }

    /**
     * Metodo para modificar el servicio del ussd.
     *
     * @return \Illuminate\Http\Response
     */
    public function ussd_service_set_status(Request $request)
    {
        return $this->ussd_service_service->ussd_service_set_status($request, $this->user->id);
    }
}
