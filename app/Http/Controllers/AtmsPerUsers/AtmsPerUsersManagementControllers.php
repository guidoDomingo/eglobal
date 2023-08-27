<?php

namespace App\Http\Controllers\AtmsPerUsers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\AtmsPerUsers\AtmsPerUsersManagementServices;

class AtmsPerUsersManagementControllers extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->service = new AtmsPerUsersManagementServices();
    }

    /**
     * Función inicial
     */
    public function index(Request $request)
    {
        return $this->service->index($request);
    }

    /**
     * Función para agregar y editar usuarios
     */
    public function management(Request $request)
    {
        return $this->service->management($request);
    }

    /**
     * Función para re - enviar correo y teléfono
     */
    public function send(Request $request)
    {
        return $this->service->send($request);
    }

    /**
     * Obtener las terminales del usuario y las que no están activas de otros usuarios
     */
    public function get_atms_per_user(Request $request)
    {
        return $this->service->get_atms_per_user($request);
    }
}
