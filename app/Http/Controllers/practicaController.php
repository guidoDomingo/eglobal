<?php

namespace App\Http\Controllers;

use Session;
use Carbon\Carbon;
use App\Models\Atm;
use App\Models\Pos;
use App\Http\Requests;
use App\Models\Branch;

use App\Models\PosDeposits;
use Illuminate\Http\Request;
use App\Http\Requests\PosRequest;
use App\Http\Controllers\Controller;

class practicaController extends Controller
{
    // protected $user;

    // public function __construct()
    // {
    //     $this->middleware('auth');
    //     $this->user = \Sentinel::getUser();
    // }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function lista(Request $request)
    {
        $name = 'hola';

        $pdf = \PDF::loadView('practica', compact('name'));  
        return $pdf->stream('primer.pdf');

    }

}