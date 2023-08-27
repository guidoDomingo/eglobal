<?php

namespace App\Http\Controllers\Commissions;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Commissions\PaidServices;

class PaidController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
        $this->service = new PaidServices();
    }

    /**
     * Función inicial
     */
    public function index(Request $request) {
        return $this->service->index($request);
    }
}