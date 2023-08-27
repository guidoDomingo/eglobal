<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;
use Sentinel;

class HomeController extends Controller
{

    private $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function index()
    {
        $user = $this->user;
        $blade = '';

        if ($user->hasAccess('monitoreo')) {
            $blade = 'dashboard.main';
        } else {
            $blade = 'public';
        }

        \Log::info("Blade designado: $blade");

        return view($blade);
    }

    public function analitica()
    {
        return view('dashboard.analitica');
    }
}
