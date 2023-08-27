<?php

/**
 * User: avisconte
 * Date: 25/08/2022
 * Time: 09:20
 */

namespace App\Http\Controllers\Info;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Info\PlantUmlServices;

class PlantUmlController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
        $this->service = new PlantUmlServices();
    }

    /**
     * Función inicial
     */
    public function index(Request $request)
    {
        return $this->service->index($request);
    }

    /**
     * Función guardar
     */
    public function save(Request $request)
    {
        return $this->service->save($request);
    }
}
