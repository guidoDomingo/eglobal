<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\UssdBlackListServices;

class UssdBlackListController extends Controller
{
    /**
     * @var class $user: Usuario
     * @global object 
     */
    protected $user;
    private $ussd_black_list_service;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
        $this->ussd_black_list_service = new UssdBlackListServices();
    }

    /**
     * Informe que muestra los teléfonos que están en la lista negra
     *
     * @return \Illuminate\Http\Response
     */
    public function ussd_black_list_report(Request $request)
    {
        return $this->ussd_black_list_service->ussd_black_list_report();
    }

    /**
     * Metodo para modificar el item de la lista negra
     *
     * @return \Illuminate\Http\Response
     */
    public function ussd_black_list_search(Request $request)
    {
        return $this->ussd_black_list_service->ussd_black_list_search($request);
    }


    /**
     * Para traer todos los motivos de la lista negra
     *
     * @return \Illuminate\Http\Response
     */
    public function ussd_black_list_reason(Request $request)
    {
        return $this->ussd_black_list_service->ussd_black_list_reason($request);
    }

    /**
     * Para traer todos los operadores
     *
     * @return \Illuminate\Http\Response
     */
    public function ussd_black_list_operador(Request $request)
    {
        return $this->ussd_black_list_service->ussd_black_list_operador($request);
    }


    /**
     * Agregar nuevo registro
     *
     * @return \Illuminate\Http\Response
     */
    public function ussd_black_list_add(Request $request)
    {
        return $this->ussd_black_list_service->ussd_black_list_add($request, $this->user->id);
    }

    /**
     * Metodo para modificar el item de la lista negra
     *
     * @return \Illuminate\Http\Response
     */
    public function ussd_black_list_edit(Request $request)
    {
        return $this->ussd_black_list_service->ussd_black_list_edit($request, $this->user->id);
    }
}
