<?php

namespace App\Http\Controllers\TerminalInteraction;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\TerminalInteraction\TransactionServices;

class TransactionController extends Controller
{
    /**
     * @var class $user: Usuario
     * @global object 
     */
    protected $user;
    private $service;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
        $this->service = new TransactionServices();
    }

    /**
     * Búsqueda inicial para la pantalla de trancciones.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        return $this->service->index($request, $this->user);
    }
}
