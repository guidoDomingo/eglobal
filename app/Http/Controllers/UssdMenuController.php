<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\UssdMenuServices;

class UssdMenuController extends Controller
{
    /**
     * @var class $user: Usuario
     * @global object 
     */
    protected $user;
    private $ussd_menu_service;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
        $this->ussd_menu_service = new UssdMenuServices();
    }

    /**
     * Informe que muestra el menú ussd.
     *
     * @return \Illuminate\Http\Response
     */
    public function ussd_menu_report(Request $request)
    {
        $list = $this->ussd_menu_service->ussd_menu_report();

        $data = [
            'list' => $list
        ];

        return view('ussd.ussd_menu_report', compact('data'));
    }
}
