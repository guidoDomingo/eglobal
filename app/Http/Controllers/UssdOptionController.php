<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\UssdOptionServices;

class UssdOptionController extends Controller
{
    /**
     * @var class $user: Usuario
     * @global object 
     */
    protected $user;
    private $ussd_option_service;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
        $this->ussd_option_service = new UssdOptionServices();
    }

    /**
     * Informe que muestra los teléfonos con saldo actual
     *
     * @return \Illuminate\Http\Response
     */
    public function ussd_option_report(Request $request)
    {
        $list = $this->ussd_option_service->ussd_option_report();

        $data = [
            'list' => $list
        ];

        return view('ussd.ussd_option_report', compact('data'));
    }

    /**
     * Metodo para modificar el servicio del ussd.
     *
     * @return \Illuminate\Http\Response
     */
    public function ussd_option_set_status(Request $request)
    {
        return $this->ussd_option_service->ussd_option_set_status($request, $this->user->id);
    }
}
