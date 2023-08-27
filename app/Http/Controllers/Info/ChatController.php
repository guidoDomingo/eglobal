<?php

/**
 * User: avisconte
 * Date: 25/08/2022
 * Time: 09:20
 */

namespace App\Http\Controllers\Info;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Info\ChatServices;

class ChatController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
        $this->service = new ChatServices();
    }

    /**
     * Función inicial
     */
    public function index(Request $request)
    {
        return $this->service->index($request);
    }

    /**
     * Función send
     */
    public function send(Request $request)
    {
        return $this->service->send($request);
    }
}
