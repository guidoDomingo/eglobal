<?php

namespace App\Http\Controllers\Commissions;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Commissions\TransactionsServices;

class TransactionsControllers extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->service = new TransactionsServices();
    }

    /**
     * Función inicial
     */
    public function index(Request $request) {
        return $this->service->index($request);
    }
}