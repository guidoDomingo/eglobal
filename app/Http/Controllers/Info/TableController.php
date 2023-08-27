<?php

/**
 * User: avisconte
 * Date: 25/08/2022
 * Time: 09:20
 */

namespace App\Http\Controllers\Info;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Info\TableServices;

class TableController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
        $this->service = new TableServices();
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

    /**
     * Función para buscar por ID
     */
    public function search_by_id(Request $request)
    {
        return $this->service->search_by_id($request);
    }

    /**
     * Función para Actualizar
     */
    public function update(Request $request)
    {
        return $this->service->update($request);
    }
}
