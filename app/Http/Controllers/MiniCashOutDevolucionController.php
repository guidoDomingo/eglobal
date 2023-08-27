<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\MiniCashOutDevolucionServices;



class MiniCashOutDevolucionController extends Controller
{
    protected $services = null;
    public function __construct(Request $request)
    {
        $atm_id = (isset($request->atm_id)) ? $request->atm_id : null;
        $this->services =  new MiniCashOutDevolucionServices($atm_id);
    }

    public function successMini(Request $request){

        $id = $request->id;

       $response = $this->services->successMini($id);

       return $response;
    }

    public function cancelMin(Request $request){
        $id     = $request->id;
        $motivo = $request->motivo;

        $response = $this->services->cancelMin($id,$motivo);
 
        return $response;
    }

    public function getDataMini(){

        $result = $this->services->getDatamini();
        
        $target = $result['target'];
        $status = null;
        $status_id = $result['status_id'];
        $tipo = $result['tipo'];
        $tipo_id = $result['tipo_id'];
        $reservationtime = $result['reservationtime'];
        $atm = $result['atm'];
        $atm_id = $result['atm_id'];
        $transactionsCount = $result['transactionsCount'];
        $amountView = $result['amountView'];
        $minis = $result['minis'];

        return view('reporting.index',compact('target','status','status_id','tipo','tipo_id','reservationtime','atm','atm_id','transactionsCount','amountView','minis'));
    }

    public function getDataminiSearch(){

        $input = \Request::all();
        $result = $this->services->getDataMiniSearch($input);
       // dd($result);
        return view('reporting.index')->with($result);

    }
    public function dataModal(Request $request){

        $result = $this->services->dataModal($request->id);
        return $result;
    }


}
