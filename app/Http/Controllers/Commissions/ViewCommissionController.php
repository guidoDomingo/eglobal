<?php

namespace App\Http\Controllers\Commissions;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Services\Commissions\ViewCommissionService;

class ViewCommissionController extends Controller
{
    public function __construct()
    {
        //$this->middleware('auth');
        //$this->user = \Sentinel::getUser();
        $this->service = new ViewCommissionService();
    }

    /**
     * Función inicial
     */

    public function commissions_generales(Request $request) {
        return $this->service->commissions_generales($request);
    }

    public function service_detallado(Request $request) {
        return $this->service->service_detallado($request);
    }

    public function service_detallado_nivel3(Request $request) {
        return $this->service->service_detallado_nivel3($request);
    }

    public function service_detallado_nivel4(Request $request) {
        return $this->service->service_detallado_nivel4($request);
    }

    /*
        Factura
    */
    public function comisionFactura(Request $request) {
        $data = [
            "cliente" => false
        ];
        return $this->service->comisionFactura($request,$data);
    }

    public function comisionFacturaCliente(Request $request) {
        $data = [
            "cliente" => true
        ];
        return $this->service->comisionFacturaCliente($request,$data);
    }

    public function generarFacturaQr(Request $request) {
        return $this->service->generarFacturaQr($request);
    }
}