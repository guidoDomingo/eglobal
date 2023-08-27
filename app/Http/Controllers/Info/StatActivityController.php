<?php

/**
 * User: avisconte
 * Date: 29/07/2022
 * Time: 17:20
 */

namespace App\Http\Controllers\Info;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Info\StatActivityServices;

class StatActivityController extends Controller
{

    //3 sub tareas:
    //1) Crear el informe de stat activity
    //2) Generar combos
    //3) Matar proceso

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
        $this->service = new StatActivityServices();
    }

    /**
     * Función inicial
     */
    public function index(Request $request) {
        return $this->service->index($request);
    }
}
