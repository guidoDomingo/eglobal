<?php

namespace App\Http\Controllers\AtmsPerUsers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\AtmsPerUsers\AtmsPerUsersServices;

class AtmsPerUsersControllers extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->service = new AtmsPerUsersServices();
    }

    /**
     * Función inicial
     */
    public function index(Request $request) {
        return $this->service->index($request);
    }

    /**
     * Función para guardar el estado
     */
    public function atms_per_users_save(Request $request) {
        return $this->service->atms_per_users_save($request);
    }
}