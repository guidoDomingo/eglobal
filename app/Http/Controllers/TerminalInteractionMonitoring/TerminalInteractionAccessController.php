<?php

namespace App\Http\Controllers\TerminalInteractionMonitoring;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use App\Services\TerminalInteractionMonitoring\TerminalInteractionAccessServices;

class TerminalInteractionAccessController extends Controller
{
    /**
     * @var class $user: Usuario
     * @global object 
     */
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
        $this->service = new TerminalInteractionAccessServices();
    }

    public function terminal_interaction_access($user_id)
    {
        return $this->service->terminal_interaction_access($user_id);
    }

    public function terminal_interaction_access_edit(Request $request)
    {
        return $this->service->terminal_interaction_access_edit($request, $this->user);
    }

    public function terminal_interaction_assign_atm(Request $request)
    {
        return $this->service->terminal_interaction_assign_atm($request, $this->user);
    }

    public function terminal_interaction_login_add(Request $request)
    {
        return $this->service->terminal_interaction_login_add($request, $this->user);
    }

    public function terminal_interaction_save_pin(Request $request)
    {
        return $this->service->terminal_interaction_save_pin($request);
    }
}