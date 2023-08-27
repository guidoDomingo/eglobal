<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\UssdPhoneServices;

class UssdPhoneController extends Controller
{
    /**
     * @var class $user: Usuario
     * @global object 
     */
    protected $user;
    private $ussd_phone_service;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
        $this->ussd_phone_service = new UssdPhoneServices();
    }

    /**
     * Informe que muestra los teléfonos con saldo actual
     *
     * @return \Illuminate\Http\Response
     */
    public function ussd_phone_report(Request $request)
    {
        $list = $this->ussd_phone_service->ussd_phone_report();

        $data = [
            'list' => $list,
        ];

        return view('ussd.ussd_phone_report', compact('data'));
    }

    /**
     * Metodo para modificar el servicio del ussd.
     *
     * @return \Illuminate\Http\Response
     */
    public function ussd_phone_set_status(Request $request)
    {
        return $this->ussd_phone_service->ussd_phone_set_status($request, $this->user->id);
    }
}
