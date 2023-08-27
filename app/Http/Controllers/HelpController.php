<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\HelpServices;

class HelpController extends Controller
{
    /**
     * @var class $user: Usuario
     * @global object 
     */
    protected $user;
    private $help_service;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
        $this->help_service = new HelpServices();
    }

    /**
     * Página inicial de ayuda.
     *
     * @return \Illuminate\Http\Response
     */
    public function help_index(Request $request)
    {
        return $this->help_service->help_index($request);
    }

    /**
     * Página inicial de ayuda.
     *
     * @return \Illuminate\Http\Response
     */
    public function help_report(Request $request)
    {
        return $this->help_service->help_report($request);
    }

    /**
     * Obtener los módulos
     *
     * @return \Illuminate\Http\Response
     */
    public function help_module()
    {
        return $this->help_service->help_module();
    }

    /**
     * Obtener las plantillas
     *
     * @return \Illuminate\Http\Response
     */
    public function help_template()
    {
        return $this->help_service->help_template();
    }

     /**
     * Obtener los tipos de vistas
     *
     * @return \Illuminate\Http\Response
     */
    public function help_view()
    {
        return $this->help_service->help_view();
    }

    /**
     * Obtener los tipos de item
     *
     * @return \Illuminate\Http\Response
     */
    public function help_item_type()
    {
        return $this->help_service->help_item_type();
    }

    /**
     * Obtener las atributos
     *
     * @return \Illuminate\Http\Response
     */
    public function help_attribute()
    {
        return $this->help_service->help_attribute();
    }

    /**
     * Obtener las posiciones siguientes
     *
     * @return \Illuminate\Http\Response
     */
    public function help_content_position_next()
    {
        return $this->help_service->help_content_position_next();
    }


    /**
     * Agregar una nueva ayuda
     *
     * @return \Illuminate\Http\Response
     */
    public function help_add(Request $request)
    {
        return $this->help_service->help_add($request, $this->user->id);
    }

    /**
     * Agregar un nuevo item
     *
     * @return \Illuminate\Http\Response
     */
    public function item_add(Request $request)
    {
        return $this->help_service->item_add($request, $this->user->id);
    }

    /**
     * Agregar un nuevo contenido al item
     *
     * @return \Illuminate\Http\Response
     */
    public function item_detail_add(Request $request)
    {
        return $this->help_service->item_detail_add($request, $this->user->id);
    }
}
