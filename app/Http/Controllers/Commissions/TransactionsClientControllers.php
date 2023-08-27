<?php

namespace App\Http\Controllers\Commissions;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Commissions\TransactionsClientServices;

class TransactionsClientControllers extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->service = new TransactionsClientServices();
    }

    /**
     * Función inicial
     */
    public function index(Request $request) {
        return $this->service->index($request);
    }
}