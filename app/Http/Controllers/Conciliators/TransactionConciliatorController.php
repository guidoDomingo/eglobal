<?php

namespace App\Http\Controllers\Conciliators;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Conciliators\TransactionConciliatorServices;

class TransactionConciliatorController extends Controller
{
    /**
     * @var class $user: Usuario
     * @global object 
     */
    protected $user;

    /**
     * @var class $bcs: Conciliador de transacciones
     * @global object 
     */
    protected $service;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
        $this->service = new TransactionConciliatorServices();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function transaction_conciliator()
    {
        return $this->service->transaction_conciliator();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function transaction_conciliator_validate(Request $request)
    {
        return $this->service->transaction_conciliator_validate($request, $this->user->username);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function transaction_conciliator_export(Request $request)
    {
        return $this->service->transaction_conciliator_export($request, $this->user->username);
    }
}
