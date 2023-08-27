<?php

namespace App\Http\Controllers;

use App\Exports\ExcelExport;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Services\ExtractosServices;
use App\Services\OndanetServices;
use Session;
use Carbon\Carbon;
use App\Services\DepositoBoletaServices;
use Excel;

class ExtractosController extends Controller
{
    protected $user;

    public function __construct()
    {
       $this->middleware('auth',['except' => 'dapdv_transactions']);
        $this->user = \Sentinel::getUser();
    }

    /** ESTADO CONTABLE*/
    public function estadoContableReports()
    {
        if (!$this->user->hasAnyAccess('reporting_mini_terminal')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $report = new ExtractosServices('');
        $result = $report->estadoContableReports();
        return view('reporting.index')->with($result);
    }

    public function estadoContableSearch(Request $request){
        if (!$this->user->hasAnyAccess('reporting_mini_terminal')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = \Request::all();
        if(isset($input['search']) || isset($input['context']) || isset($input['page'])){
            $report = new ExtractosServices($input);
            $result = $report->estadoContableSearch($request);
            //dd($result);
            return view('reporting.index', compact('target', 'reservationtime'))->with($result);
        }

        if(isset($input['download'])){
            ini_set('max_execution_time', 300);
            $report = new ExtractosServices($input);
            $result = $report->estadoContableSearchExport();
            $result = json_decode(json_encode($result),true);
            $columnas = array(
                'Fecha', 'Concepto','Debe','Haber','Saldo'
            );
            if($result){
                $filename = 'transacciones_'.time();
                $excel = new ExcelExport($result,$columnas);
                return Excel::download($excel, $filename . '.xls')->send();
                // Excel::create($filename, function($excel) use ($result) {
                //     $excel->sheet('sheet1', function($sheet) use ($result) {
                //         $sheet->rows($result['transactions'],false);
                //         $sheet->prependRow(array(
                //             'Fecha', 'Concepto','Debe','Haber','Saldo'
                //         ));        
                //     });
                // })->export('xls');
                // exit();
            }else{
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();   
            }
        }

    }

    /** RESUMEN MINITERMINALES*/
    public function resumenMiniterminalesReports(Request $request)
    {
        if (!$this->user->hasAnyAccess('reporting_resumen_mini_terminal')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $report = new ExtractosServices('');
        $result = $report->resumenMiniterminalesReports($request);                
        return view('reporting.index')->with($result);
    }

    public function resumenMiniterminalesSearch(Request $request){
        if (!$this->user->hasAnyAccess('reporting_resumen_mini_terminal')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        
        $input = \Request::all();        
        if(isset($input['download'])){
            ini_set('max_execution_time', 300);
            $report = new ExtractosServices($input);
            $result = $report->resumenMiniterminalesSearchExport();
            $result = json_decode(json_encode($result),true);
            $columna1 = array(
                'Id', 'Ruc', 'Grupo', 'Total Transaccionado','Total Depositado','Total Reversado','Total Cashout', 'Total Pago QR','Total Cuotas','Saldo', 'Estado'
            );
            $columna2 = array(
                'ID', 'ATM', 'Total Transaccionado', 'Total Depositado', 'Total Reversado','Total Cashout','Saldo', 'Estado'
            );

            if($result){
                $filename = 'resumen_miniterminales'.time();

                $excel = new ExcelExport($result,$columna1,$result['transacciones'],$columna2);
                return Excel::download($excel, $filename . '.xls')->send();

                // Excel::create($filename, function($excel) use ($result) {
                //     $excel->sheet('sheet1', function($sheet) use ($result) {    
                //         $sheet->rows($result['transacciones_groups'],false);
                //         $sheet->prependRow(array(
                //             'Id', 'Ruc', 'Grupo', 'Total Transaccionado','Total Depositado','Total Reversado','Total Cashout', 'Total Pago QR','Total Cuotas','Saldo', 'Estado'
                //         ));        
                //      });
                //     $excel->sheet('Por Sucursal', function($sheet) use ($result) {    
                //         $sheet->rows($result['transacciones'],false);
                //         $sheet->prependRow(array(
                //             'ID', 'ATM', 'Total Transaccionado', 'Total Depositado', 'Total Reversado','Total Cashout','Saldo', 'Estado'
                //         ));        
                //     }); 
                // })->export('xls');
                // exit();
            }else{
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();   
            }
            
        }

        if(isset($input['search']) || isset($input['context']) || isset($input['page'])){
            $report = new ExtractosServices($input);
            $result = $report->resumenMiniterminalesSearch($request);
            return view('reporting.index')->with($result);
        }  
    }

    /** ESTADO CONTABLE DETALLADO*/
    public function resumenDetalladoReports(Request $request)
    {
        if (!$this->user->hasAnyAccess('reporting_resumen_mini_terminal')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }        
        $report = new ExtractosServices('');
        $result = $report->resumenDetalladoReports($request);
        return view('reporting.index')->with($result);
    }

    public function resumenDetalladoSearch(Request $request){
        if (!$this->user->hasAnyAccess('reporting_resumen_mini_terminal')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $input = \Request::all();
 
        if(isset($input['download'])){
            ini_set('max_execution_time', 300);
            $report = new ExtractosServices($input);
            $result = $report->resumenDetalladoSearchExport();
            $result = json_decode(json_encode($result),true);
            $columna1 = array(
                'Id','Ruc','Grupo', 'Total Transaccionado','Total Paquetigo','Total Personal', 'Total Claro','Total Pago Cashout','Total Depositado','Total Reversado','Total Cashout','Saldo'
            );
            $columna2 = array(
                'ID Atm', 'ATM', 'Total Transaccionado'
            );

            if($result){
                $filename = 'resumen_miniterminales'.time();

                $excel = new ExcelExport($result,$columna1,$result['transacciones'],$columna2);
                return Excel::download($excel, $filename . '.xls')->send();

                // Excel::create($filename, function($excel) use ($result) {
                //     $excel->sheet('sheet1', function($sheet) use ($result) {    
                //         $sheet->rows($result['transacciones_groups'],false);
                //          $sheet->prependRow(array(
                //              'Id','Ruc','Grupo', 'Total Transaccionado','Total Paquetigo','Total Personal', 'Total Claro','Total Pago Cashout','Total Depositado','Total Reversado','Total Cashout','Saldo'
                //          ));        
                //      });
                //     $excel->sheet('Por Sucursal', function($sheet) use ($result) {    
                //         $sheet->rows($result['transacciones'],false);
                //         $sheet->prependRow(array(
                //             'ID Atm', 'ATM', 'Total Transaccionado'
                //         ));        
                //     }); 
                // })->export('xls');
                // exit();
            }else{
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();   
            }
            
        }

        if(isset($input['search']) || isset($input['context']) || isset($input['page'])){
            $report = new ExtractosServices($input);
            $result = $report->resumenDetalladoSearch($request);
            return view('reporting.index')->with($result);
        }  
    }

    public function getBranchesfroGroups($group_id, $day){
        if (!$this->user->hasAnyAccess('reporting_resumen_mini_terminal')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $report = new ExtractosServices('');
        $result = $report->getBranchfroGroup($group_id, $day);

        return $result;

    }

    public function getReversionsForGroups($group_id, $day){
        if (!$this->user->hasAnyAccess('reporting_resumen_mini_terminal')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $report = new ExtractosServices('');
        $result = $report->getReversionsForGroups($group_id, $day);

        return $result;

    }

    public function getCashoutsForGroups($group_id, $day){
        if (!$this->user->hasAnyAccess('reporting_resumen_mini_terminal')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $report = new ExtractosServices('');
        $result = $report->getCashoutsForGroups($group_id, $day);

        return $result;

    }

    /** VENTAS MINITERMINALES*/
    public function cobranzasReports()
    {
        $report = new ExtractosServices('');
        $result = $report->cobranzasReports();
        return view('reporting.index')->with($result);
    }

    public function cobranzasSearch(Request $request){
        $input = \Request::all();
     
        if(isset($input['search']) || isset($input['context']) || isset($input['page'])){
            $report = new ExtractosServices($input);
            $result = $report->cobranzasSearch($request);
        
            if($result){
                return view('reporting.index')->with($result);
            }else{
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();   
            }
            
        }

        if(isset($input['download'])){
            ini_set('max_execution_time', 300);
            $report = new ExtractosServices($input);
            $result = $report->cobranzasSearchExport();
            $result = json_decode(json_encode($result),true);
            $columnas = array(
                'ID', 'Grupo', 'Fecha','ID Ondanet','Nro Recibo', 'Monto de la Boleta', 'Tipo de Recibo'
            );

            if($result){
                $filename = 'cobranzas_miniterminales'.time();

                $excel = new ExcelExport($result,$columnas);
                return Excel::download($excel, $filename . '.xls')->send();
                
                // Excel::create($filename, function($excel) use ($result) {
                //     $excel->sheet('sheet1', function($sheet) use ($result) {
                //         //dd($result['transactions']);
                //         $sheet->rows($result['transactions'],false);
                //         $sheet->prependRow(array(
                //             'ID', 'Grupo', 'Fecha','ID Ondanet','Nro Recibo', 'Monto de la Boleta', 'Tipo de Recibo'
                //         ));
                //     });
                // })->export('xls');
                // exit();
            }else{
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();   
            }
            
        }

    }

    /** VENTAS MINITERMINALES*/
    public function salesReports()
    {
        $report = new ExtractosServices('');
        $result = $report->salesReports();
        return view('reporting.index')->with($result);
    }

    public function salesSearch(Request $request){

        $input = \Request::all();
        if(isset($input['search']) || isset($input['context']) || isset($input['page'])){
            $report = new ExtractosServices($input);
            $result = $report->salesSearch($request);
            if($result){
                return view('reporting.index')->with($result);
            }else{
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();   
            }
            
        }

        if(isset($input['download'])){
            ini_set('max_execution_time', 300);
            $report = new ExtractosServices($input);
            $result = $report->salesSearchExport();
            $result = json_decode(json_encode($result),true);
            $columnas = array(
                'ID', 'Grupo','Monto','Fecha','ID Ondanet','Nro Venta', 'Estado', 'Monto por cobrar'
            );

            if($result){
                //dd($result['transactions']);
                $filename = 'ventas_miniterminales'.time();

                $excel = new ExcelExport($result,$columnas);
                return Excel::download($excel, $filename . '.xls')->send();

                // Excel::create($filename, function($excel) use ($result) {
                //     $excel->sheet('sheet1', function($sheet) use ($result) {
                //         $sheet->rows($result['transactions'],false);
                //         $sheet->prependRow(array(
                //             'ID', 'Grupo','Monto','Fecha','ID Ondanet','Nro Venta', 'Estado', 'Monto por cobrar'
                //         ));        
                //     });
                // })->export('xls');
                // exit();
            }else{
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();   
            }
            
        }

    }
    /** BOLETAS DEPOSITOS*/
    public function boletasDepositosReports(Request $request)
    {
        if (!$this->user->hasAnyAccess('reporting_boleta_depositos')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $report = new ExtractosServices('');
        $result = $report->boletaDepositosReports($request);
        return view('reporting.index')->with($result);
    }

    public function boletasDepositosSearch(Request $request){
        if (!$this->user->hasAnyAccess('reporting_boleta_depositos')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = \Request::all();

        if(isset($input['download'])){
            ini_set('max_execution_time', 300);
            $report = new ExtractosServices($input);
            $result = $report->boletaDepositosSearchExport();
            $result = json_decode(json_encode($result),true);
            $columna1 = array(
                'ID', 'Fecha','ATM','Concepto','Banco','Cuenta Bancaria', 'Nro Boleta', 'Monto', 'Estado', 'Modificado por', 'Fecha Modificacion', 'Mensaje'
            );
            $columna2 = array(
                'ID Boleta', 'Numero de boleta', 'Numero de Recibo', 'Deudas Afectadas'
            );

            if($result){
                $filename = 'depositos_boletas_'.time();

                $excel = new ExcelExport($result,$columna1,$result['transaction_details'],$columna2);
                return Excel::download($excel, $filename . '.xls')->send();

                // Excel::create($filename, function($excel) use ($result) {
                //     $excel->sheet('boletas', function($sheet) use ($result) {
                //         $sheet->rows($result['transactions'],false);
                //         $sheet->prependRow(array(
                //             'ID', 'Fecha','ATM','Concepto','Banco','Cuenta Bancaria', 'Nro Boleta', 'Monto', 'Estado', 'Modificado por', 'Fecha Modificacion', 'Mensaje'
                //         ));        
                //     });
                //     $excel->sheet('detalles_numeros_recibos', function($sheet) use ($result) {
                //         $sheet->rows($result['transaction_details'],false);
                //         $sheet->prependRow(array(
                //             'ID Boleta', 'Numero de boleta', 'Numero de Recibo', 'Deudas Afectadas'
                //         ));        
                //     });
                // })->export('xls');
                // exit();
            }else{
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();   
            }
            
        }
        
        if(isset($input['search']) || isset($input['context']) || isset($input['page'])){
            $report = new ExtractosServices($input);
            $result = $report->boletaDepositosSearch($request);
            return view('reporting.index')->with($result);
        }
    }

    public function getBoletasDetails($id){

        $transaction_details = \DB::table('boletas_depositos as bd')
            ->select('mr.recibo_nro', 'mtc.ventas_cobradas')
            ->join('mt_recibos_cobranzas as mtc', 'bd.id', '=', 'mtc.boleta_deposito_id')
            ->join('mt_recibos as mr', 'mr.id', '=', 'mtc.recibo_id')
            ->join('mt_movements as m', 'm.id', '=', 'mr.mt_movements_id')
            ->where('bd.id','=',$id)
            ->whereNull('m.deleted_at')
        ->get();

        $details = '<tr><th style="display:none;"></th>
        <th>Numero de Recibo</th>
        <th>Ventas afectadas</th></tr>';

        foreach ($transaction_details as $transaction_detail) {
            $details .='
                <tr><td style="display:none;"></td>
                <td>'.$transaction_detail->recibo_nro.'</td>
                <td>'.$transaction_detail->ventas_cobradas.'</td></tr>';
        }
        \Log::info($details);

        return $details;
    }

    public function getImagenDetails($id){

        \Log::info('Se procede a buscar la imagen de la boleta #'. $id);
        $boleta = \DB::table('boletas_depositos')->where('id','=',$id)->first();

        //Se coloca bandera de imagen
        $imagen = false;

        if(!is_null($boleta->imagen_asociada)){
            //nombre del archivo
            $file = $boleta->imagen_asociada;
            //Ubicacion del archivo
            $url = public_path().'/resources/images/boleta_deposito/';

            //Si ya existe el archivo se pega directamente la url
            if (file_exists($url.$file)) {
                \Log::info('La siguiente imagen ya existe con el nombre de archivo '. $file);
                $imagen=true;
            } else { //Si no existe el archivo, se descarga del sftp
                \Log::info('La siguiente imagen no existe localmente, se procede a buscar en el sftp ');
                //Nos conectamos al FTP
                $boletas_egl_sftp_server = env('BOLETAS_EGL_SFTP_SERVER');
                $boletas_egl_sftp_port = env('BOLETAS_EGL_SFTP_PORT');
                $boletas_egl_sftp_user_name = env('BOLETAS_EGL_SFTP_USER_NAME');
                $boletas_egl_sftp_user_password = env('BOLETAS_EGL_SFTP_USER_PASSWORD');
                $boletas_egl_sftp_folder = env('BOLETAS_EGL_SFTP_FOLDER');

                //BUSCAMOS EL ARCHIVO EN EL SFTP
                //$sftp_url = "sftp://$boletas_egl_sftp_server:$boletas_egl_sftp_port/$boletas_egl_sftp_folder/$base_name_data_file";
                $sftp_url = "sftp://$boletas_egl_sftp_server:$boletas_egl_sftp_port/$boletas_egl_sftp_folder/$file";
                $sftp_user_and_password = "$boletas_egl_sftp_user_name:$boletas_egl_sftp_user_password";

                //Se pone la url donde se va a guardar el archibo
                $url_temp = $url.$file;

                //$ch = curl_init($sftp_url);
                $curl = curl_init();

                curl_setopt($curl, CURLOPT_URL, $sftp_url);
                curl_setopt($curl, CURLOPT_PROTOCOLS, CURLPROTO_SFTP);
                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
                curl_setopt($curl, CURLOPT_USERPWD, $sftp_user_and_password);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
                //curl_setopt($curl, CURLOPT_INFILE, $sftp_url);
                //curl_setopt($curl, CURLOPT_INFILESIZE, filesize($sftp_url));
                curl_setopt($curl, CURLOPT_VERBOSE, true);
                $d=file_put_contents($url_temp, curl_exec($curl));
                curl_close($curl);

                if($d > 0){
                    \Log::info('Imagen descargada del SFTP correctamente');
                    $imagen=true;
                }else{
                    \Log::info('La imagen ya no se encuentra disponible en el SFTP');
                    unlink($url_temp);
                    $imagen=false;
                }
                
            }
        }

        $details['imagen'] = '';
        $details['url_imagen'] = '';

        if($imagen){
            \Log::info(url('/resources/images/boleta_deposito/'. $file));

            $details['imagen'] = "
            
            <img id='pic' src='" . url('/resources/images/boleta_deposito/'. $file) ."' width='100%' height='100%'>
            <br>
            <button onclick='zoomIn()' style='width: 70px; text-align:center'>Acercar</button>
            <button onclick='zoomOut()' style='width: 70px; text-align:center'>Alejar</button>
            
            <script>
                function zoomIn() {
                    var pic = document.getElementById('pic');
                    var width = pic.clientWidth;
                    pic.style.width = width + 100 + 'px';
                }

                function zoomOut() {
                    var pic = document.getElementById('pic');
                    var width = pic.clientWidth;
                    pic.style.width = width - 100 + 'px';
                }
            
            </script>";

            //\Log::info($details['imagen']);
            $details['url_imagen'] = $file;
        }else{
            $details['imagen'] = "<h2>La imagen ya no esta disponible</h2>";
        }

        return $details;
    }

    public function deleteImage($file){

        $file=public_path().'/resources/images/boleta_deposito/'. $file;

        unlink($file);

        $details['error']=false;

        return $details;
    }

    /** Reporte de Depositos Cuotas **/
    public function depositosCuotasReports(Request $request)
    {        
        if (!$this->user->hasAnyAccess('reporting_depositos_cuotas')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $report = new ExtractosServices('');
        $result = $report->depositosCuotasReports($request);        
        return view('reporting.index')->with($result);
    }

    public function depositosCuotasSearch(Request $request){
        if (!$this->user->hasAnyAccess('reporting_depositos_cuotas')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = \Request::all();

        if(isset($input['search']) || isset($input['context']) || isset($input['page'])){
            $report = new ExtractosServices($input);
            $result = $report->depositosCuotasSearch($request);
            return view('reporting.index')->with($result);
        }

        if(isset($input['download'])){
            ini_set('max_execution_time', 300);
            $report = new ExtractosServices($input);
            $result = $report->depositosCuotasSearchExport();
            $result = json_decode(json_encode($result),true);
            $columnas = array(
                'ID', 'Fecha','ATM','Concepto','Banco','Cuenta Bancaria', 'Nro Boleta', 'Monto', 'Estado', 'Modificado por', 'Fecha Modificacion', 'Mensaje'
            );

            if($result){
                $filename = 'depositos_boletas_'.time();

                $excel = new ExcelExport($result,$columnas);
                return Excel::download($excel, $filename . '.xls')->send();

                // Excel::create($filename, function($excel) use ($result) {
                //     $excel->sheet('sheet1', function($sheet) use ($result) {
                //         $sheet->rows($result['transactions'],false);
                //         $sheet->prependRow(array(
                //             'ID', 'Fecha','ATM','Concepto','Banco','Cuenta Bancaria', 'Nro Boleta', 'Monto', 'Estado', 'Modificado por', 'Fecha Modificacion', 'Mensaje'
                //         ));        
                //     });
                // })->export('xls');
                // exit();
            }else{
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();   
            }
            
        }
    }

    /** Reporte de Depositos Cuotas **/
    public function depositosAlquileresReports(Request $request)
    {
        if (!$this->user->hasAnyAccess('reporting_depositos_cuotas')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $report = new ExtractosServices('');
        $result = $report->depositosAlquileresReports($request);
        return view('reporting.index')->with($result);
    }

    public function depositosAlquileresSearch(Request $request){
        if (!$this->user->hasAnyAccess('reporting_depositos_cuotas')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = \Request::all();

        if(isset($input['search']) || isset($input['context']) || isset($input['page'])){
            $report = new ExtractosServices($input);
            $result = $report->depositosAlquileresSearch($request);
            return view('reporting.index')->with($result);
        }

        if(isset($input['download'])){
            ini_set('max_execution_time', 300);
            $report = new ExtractosServices($input);
            $result = $report->depositosAlquileresSearchExport();
            $result = json_decode(json_encode($result),true);
            $columnas = array(
                'ID', 'Fecha','ATM','Concepto','Banco','Cuenta Bancaria', 'Nro Boleta', 'Monto', 'Estado', 'Modificado por', 'Fecha Modificacion', 'Mensaje'
            );

            if($result){
                $filename = 'depositos_boletas_'.time();

                $excel = new ExcelExport($result,$columnas);
                return Excel::download($excel, $filename . '.xls')->send();
                // Excel::create($filename, function($excel) use ($result) {
                //     $excel->sheet('sheet1', function($sheet) use ($result) {
                //         $sheet->rows($result['transactions'],false);
                //         $sheet->prependRow(array(
                //             'ID', 'Fecha','ATM','Concepto','Banco','Cuenta Bancaria', 'Nro Boleta', 'Monto', 'Estado', 'Modificado por', 'Fecha Modificacion', 'Mensaje'
                //         ));        
                //     });
                // })->export('xls');
                // exit();
            }else{
                Session::flash('error_message', 'No existen registros para este criterio de búsqueda');
                return redirect()->back();   
            }
            
        }
    }

    /**Conciliaciones MINITERMINALES*/

    public function conciliationsDetails()
    {
        try {

            $report = new ExtractosServices('');
            $result = $report->conciliationsDetails();
            return view('reporting.index')->with($result);
            
        } catch (\Exception $e) {
            \Log::error("Error en la consulta de servicio Conciliaciones: " . $e);
        }
    }

    public function relanzarCobranza(Request $request)
    {
        $id = $request->_id;

        /*if (!$this->user->hasAccess('conciliations.relanzar')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }*/

        $movement = \DB::table('mt_movements')->where('id', $id)->first();

        $result = $this->verificar_cobranza($movement->id);

        \Log::info('[Relanzar Recibos]', ['movement_id' => $movement->id, 'result' => $result]);

        //Actualizar ventas ondanet
        $update_sale = $this->update_sales($movement->group_id, $result['ruc']);
        \Log::info('[Update Sales]', $update_sale);

        $response['error'] = false;

        if($update_sale['error']){
            $result['message'] .=", pero hubo un error al actualizar las ventas";
        }

        if(!$result['error']){
            Session::flash('message', $result['message']);
        }else{
            Session::flash('error_message', $result['message']);
        }

        return response()->json([
            'error' => $result['error'],
            'message' => $result['message'],
        ]);
    }

    public function verificar_cobranza($movement_id){

        try{
            $movement=\DB::table('mt_movements as m')
                ->selectRaw('m.*, bg.id as group_id, bg.description, bg.ruc, mt_recibos.recibo_nro, mt_recibos.id as id_recibo, mt_recibos.monto, bd.fecha as fecha_boleta, bd.id as boleta_id')
                ->join('business_groups as bg', 'bg.id', '=', 'm.group_id')
                ->join('mt_recibos', 'm.id', '=', 'mt_recibos.mt_movements_id')
                ->join('mt_recibos_cobranzas as mtc', 'mt_recibos.id', '=', 'mtc.recibo_id')
                ->join('boletas_depositos as bd', 'bd.id', '=', 'mtc.boleta_deposito_id')
                ->where('m.id', $movement_id)   
            ->first();

            $status     = $movement->destination_operation_id;
            $ruc        = $movement->ruc;
            $nro_recibo = $movement->recibo_nro;
            $recibo_id  = $movement->id_recibo;
            $monto      = $movement->monto;
            $boleta_id  = $movement->boleta_id;
            $fecha      = $movement->fecha_boleta;
            $group_id   = $movement->group_id;
            $response['ruc'] = $ruc;

            $service = new OndanetServices();

            if($status != 777){
                //Se consulta si el recibo existe en ondanet
                $recibo = \DB::connection('ondanet')
                    ->table('LISTADO_VENTAS_COBRANZAS_SERVICIOS_MINITERMINALES')
                    ->selectRaw('*, convert(int, saldo_recibo) as saldo ')
                    ->whereRaw("cliente like '%". $ruc . "%'")
                    ->where("nro_recibo", $nro_recibo)
                ->first();

                $ventas_pendiente = \DB::connection('ondanet')
                    ->table('LISTADO_VENTAS_COBRANZAS_SERVICIOS_MINITERMINALES')
                    ->selectRaw('distinct STATUS_VENTA, NRO_VENTA, TOTAL_VENTA, FECHA_VENTA, saldo_venta, convert(int, saldo_venta) as saldo')
                    ->whereRaw("cliente like '%". $ruc . "%'")
                    ->where('SALDO_VENTA', '>', 0)
                    ->orderBy('STATUS_VENTA', 'ASC')
                ->get();

                if(empty($recibo)){
                    //Si no tiene venta pendientes
                    if(empty($ventas_pendiente)){
                        //Si no tiene venta pendiente
                        $boleta=\DB::table('mt_recibos as mr')
                            ->selectRaw('bd.*')
                            ->join('mt_recibos_cobranzas as mtc', 'mr.id', '=', 'mtc.recibo_id')
                            ->join('boletas_depositos as bd', 'bd.id', '=', 'mtc.boleta_deposito_id')
                            ->where('mr.id', $recibo_id)   
                        ->first();
                        
                        $recibo         =  $nro_recibo;
                        $fecha          =  date("d-m-Y", strtotime($boleta->fecha));
                        $importe        =  $monto;
                        $pdv            =  $ruc;
                        $comprobante    =  'REC';
                        $forcobro       =  'EFE';

                        $result= $service->registerRecibosaFavor($recibo, $fecha, $importe, $pdv, $comprobante, $forcobro);
                        \Log::info('[Recibo A Favor]', $result);
                        if(!$result['error']){
                            \Log::info("[Recibo A Favor] Exporting recibos a favor to ondanet ",['result_error' => $result['error'], 'ondanet_rowId' => $result['status']]);

                            \DB::table('mt_movements')
                                ->where('id', $movement_id)
                                ->update([
                                    'destination_operation_id' => 888,
                                    'response' => json_encode($result),
                                    'updated_at' => Carbon::now()
                            ]);
                            
                            $response['error'] = false;
                            $response['message']="El recibo_id $recibo_id se actualizo el status a 888 debido a que tiene saldo pero no ventas a afectar.";

                        }else{
                            \Log::warning("[ondanet] Error - Exporting miniterminales cobranzas to ondanet ",['result' => $result]);

                            \DB::table('mt_movements')
                                ->where('id', $movement_id)
                                ->update([
                                    'destination_operation_id' => 1,
                                    'response' => json_encode($result),
                                    'updated_at' => Carbon::now()
                            ]);

                            $response['error'] = true;
                            $response['message']="Ocurrio un error al intentar migrar a ondanet el recibo a favor.";
                        }

                        \DB::table('mt_recibos_cobranzas')
                            ->where('recibo_id', $recibo_id)
                            ->update([
                                'ventas_cobradas' => null,
                                'saldo_pendiente' => $monto
                        ]);

                        \DB::table('mt_sales_affected_by_receipts')
                            ->where('receipt_id', $recibo_id)
                            ->update([
                                'deleted_at' => Carbon::now()
                        ]);
    
                        return $response;
                    }

                    $total_venta=array_sum(array_column($ventas_pendiente, 'saldo')); //Monto pendiente de ventas

                    $dif = $total_venta - $monto;
                    if($dif >= 0){//Si el total de ventas es mayor que el saldo del recibo
                        //Se procede a afectar el total del recibo

                        $array=json_decode(json_encode($ventas_pendiente), true);
                        \Log::info('[Recibo Error] Ventas pendientes en ondanet: ', $array);
                        $i=0;
                        $sum=0;
                        //algoritmo para traer cuantas ventas van a ser cobradas
                        do{

                            $sum += $array[$i]['saldo'];
                            $i++;
                            
                        }while($sum < $monto);

                        \Log::info("[Recibo Error] La sumatoria es: " . $sum . " Y las veces que sumo fue: " . $i . "<p>");

                        $ventas_seleccionadas = array_slice($array, 0, $i); //Se trae la cantidad de ventas a cobrar

                        $ventasondanet = implode(';', array_column($ventas_seleccionadas, 'STATUS_VENTA'));
                        \Log::info('[Recibo Error] Ventas a cobrar', ['ventasondanet' => $ventasondanet]);

                        //proceed to export deposits to ondanet
                        $recibo     =  $nro_recibo;
                        $fecha      =  date("d-m-Y", strtotime($fecha));
                        $importe    =  $monto;
                        $forcobro   =  'EFE';
                        $ventas     =  $ventasondanet;

                        $result= $service->registerCobranzas($recibo, $fecha, $importe, $forcobro, $ventas);
                        \Log::info('[Recibo Error]', $result);

                        \DB::table('mt_sales_affected_by_receipts')
                            ->where('receipt_id', $recibo_id)
                            ->update([
                                'deleted_at' => Carbon::now()
                        ]);

                        $service = new DepositoBoletaServices();

                        $recibo_id      = $recibo_id;
                        $now            = Carbon::now();
                        $description    = 'Recibo Cobranza RELANZADO insertado desde la Funcion: EctractosCOntroller@verificar_cobranza';

                        if($result['error'] == false){
                            \Log::info("[Recibo Error] Exporting miniterminales cobranzas to ondanet ",['result_error' => $result['error'], 'ondanet_rowId' => $result['status']]);

                            \DB::table('mt_movements')
                                    ->where('id', $movement_id)
                                    ->update([
                                        'destination_operation_id' => $result['status'],
                                        'response' => json_encode($result),
                                        'updated_at' => Carbon::now()
                            ]);

                            \DB::table('mt_recibos_cobranzas')
                                ->where('recibo_id', $recibo_id)
                                ->update([
                                    'ventas_cobradas' => $ventas,
                                    'saldo_pendiente' => 0
                            ]);

                            $response['error'] = false;
                            $response['message']="Se ha Relanzado el recibo_id $recibo_id y actualizado las ventas correctamente";

                            $sales_affected = \DB::connection('ondanet')
                                ->table('LISTADO_VENTAS_COBRANZAS_SERVICIOS_MINITERMINALES')
                                ->selectRaw('DISTINCT STATUS_VENTA, convert(int, TOTAL_VENTA) as total_venta, convert(int, SALDO_VENTA) as saldo_venta, 
                                status_cobranza, nro_recibo, convert(int, IMPORTE_AFECTADO) as importe_afectado,
                                convert(int, TOTAL_RECIBO) as total_recibo, convert(int, saldo_recibo) as saldo ')
                                ->whereRaw("cliente like '%". $ruc . "%'")
                                ->where("nro_recibo", $nro_recibo)
                            ->get();

                            $status_afected=json_decode(json_encode($sales_affected), true);

                            $idventasondanet = \DB::table('mt_sales')
                                ->select('mt_sales.id as sale_id')
                                ->join('mt_movements as m', 'm.id', '=', 'mt_sales.movements_id')
                                ->where('m.group_id', $group_id )
                                ->whereNull('m.deleted_at')
                                ->whereRaw("m.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-16','-16','-17','-21','-23','-26','-27','212','999')")
                                ->whereIn('m.destination_operation_id', array_column($status_afected, 'STATUS_VENTA') )
                                ->orderBy('m.destination_operation_id', 'ASC')
                            ->get();

                            foreach($sales_affected as $key=>$sale){
                                $sale_id                = $idventasondanet[$key]->sale_id;
                                $sales_amount           = $sale->importe_afectado + $sale->saldo_venta;
                                $sales_amount_affected  = $sale->importe_afectado;
                                $sales_amount_pendding  = $sale->saldo_venta;

                                $service->insert_mt_sales_affected_by_receipts($recibo_id, $sale_id, $sales_amount, $sales_amount_affected, $sales_amount_pendding, $now, $description);
                            }
                        }else{
                            \Log::warning("[Recibo Error] Error - Exporting miniterminales cobranzas a favor to ondanet ",['result' => $result]);

                            \DB::table('mt_movements')
                                ->where('id', $movement_id)
                                ->update([
                                    'destination_operation_id' => 1,
                                    'response' => json_encode($result),
                                    'updated_at' => Carbon::now()
                            ]);

                            $response['error'] = true;
                            $response['message']="Hubo un error al relanzar el recibo_id : $recibo_id";
                        }
            
                        return $response;
                    }else{//Si el saldo del reibo es mayor que el total de las ventas
                        //Se afecta parcialmente el recibo
                        $boleta=\DB::table('mt_recibos as mr')
                            ->selectRaw('bd.*')
                            ->join('mt_recibos_cobranzas as mtc', 'mr.id', '=', 'mtc.recibo_id')
                            ->join('boletas_depositos as bd', 'bd.id', '=', 'mtc.boleta_deposito_id')
                            ->where('mr.id', $recibo_id)   
                        ->first();
                        
                        $recibo         =  $nro_recibo;
                        $fecha          =  date("d-m-Y", strtotime($boleta->fecha));
                        $importe        =  $monto;
                        $pdv            =  $ruc;
                        $comprobante    =  'REC';
                        $forcobro       =  'EFE';

                        $result= $service->registerRecibosaFavor($recibo, $fecha, $importe, $pdv, $comprobante, $forcobro);
                        \Log::info('[Recibo A Favor]', $result);
                        if(!$result['error']){
                            \Log::info("[Recibo A Favor] Exporting recibos a favor to ondanet ",['result_error' => $result['error'], 'ondanet_rowId' => $result['status']]);

                            \DB::table('mt_movements')
                                ->where('id', $movement_id)
                                ->update([
                                    'destination_operation_id' => 888,
                                    'response' => json_encode($result),
                                    'updated_at' => Carbon::now()
                            ]);
                            
                            $response['error'] = false;
                            $response['message']="El recibo_id $recibo_id se actualizo el status a 888 debido a que tiene saldo pero no ventas a afectar.";

                        }else{
                            \Log::warning("[ondanet] Error - Exporting miniterminales sales to ondanet ",['result' => $result]);

                            \DB::table('mt_movements')
                                ->where('id', $movement_id)
                                ->update([
                                    'destination_operation_id' => 1,
                                    'response' => json_encode($result),
                                    'updated_at' => Carbon::now()
                            ]);

                            $response['error'] = true;
                            $response['message']="Ocurrio un error al intentar migrar a ondanet el recibo a favor.";
                        }

                        \DB::table('mt_recibos_cobranzas')
                            ->where('recibo_id', $recibo_id)
                            ->update([
                                'ventas_cobradas' => null,
                                'saldo_pendiente' => $monto
                        ]);

                        \DB::table('mt_sales_affected_by_receipts')
                            ->where('receipt_id', $recibo_id)
                            ->update([
                                'deleted_at' => Carbon::now()
                        ]);

                        $service = new DepositoBoletaServices();

                        $recibo_id      = $recibo_id;
                        $now            = Carbon::now();
                        $description    = 'Recibo Cobranza RELANZADO insertado desde la Funcion: EctractosCOntroller@verificar_cobranza';

                        $sales_affected = \DB::connection('ondanet')
                            ->table('LISTADO_VENTAS_COBRANZAS_SERVICIOS_MINITERMINALES')
                            ->selectRaw('DISTINCT STATUS_VENTA, convert(int, TOTAL_VENTA) as total_venta, convert(int, SALDO_VENTA) as saldo_venta, 
                            status_cobranza, nro_recibo, convert(int, IMPORTE_AFECTADO) as importe_afectado,
                            convert(int, TOTAL_RECIBO) as total_recibo, convert(int, saldo_recibo) as saldo ')
                            ->whereRaw("cliente like '%". $ruc . "%'")
                            ->where("nro_recibo", $nro_recibo)
                        ->get();

                        if(!is_null($sales_affected[0]->STATUS_VENTA)){
                            $status_afected=json_decode(json_encode($sales_affected), true);

                            $idventasondanet = \DB::table('mt_sales')
                                ->select('mt_sales.id as sale_id')
                                ->join('mt_movements as m', 'm.id', '=', 'mt_sales.movements_id')
                                ->where('m.group_id', $group_id )
                                ->whereNull('m.deleted_at')
                                ->whereRaw("m.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-16','-16','-17','-21','-23','-26','-27','212','999')")
                                ->whereIn('m.destination_operation_id', array_column($status_afected, 'STATUS_VENTA') )
                                ->orderBy('m.destination_operation_id', 'ASC')
                            ->get();
        
                            foreach($sales_affected as $key=>$sale){
                                $sale_id                = $idventasondanet[$key]->sale_id;
                                $sales_amount           = $sale->importe_afectado + $sale->saldo_venta;
                                $sales_amount_affected  = $sale->importe_afectado;
                                $sales_amount_pendding  = $sale->saldo_venta;
        
                                $service->insert_mt_sales_affected_by_receipts($recibo_id, $sale_id, $sales_amount, $sales_amount_affected, $sales_amount_pendding, $now, $description);
                            }
                        }
                    }
                }else{

                    $saldo_recibo=$recibo->saldo; //Saldo pendiente del recibo
                    if($saldo_recibo  == 0){
                        //Si no tiene venta pendiente
                        $result['error']=false;
                        $result['message']='El siguiente numero de recibo no tiene saldo pendiente';

                        \Log::warning("[Recibo ERROR] Error - El numero de recibo no tiene saldo pendiente. ",['result' => $result]);

                        \DB::table('mt_movements')
                            ->where('id', $movement_id)
                            ->update([
                                'destination_operation_id' => $recibo->STATUS_COBRANZA,
                                'response' => json_encode($result),
                                'updated_at' => Carbon::now()
                        ]);

                        $response['error'] = false;
                        $response['message']="El recibo_id $recibo_id no tiene saldo pendiente, se actualizo con el status de ondanet.";

                        return $response;
                    }

                    if(empty($ventas_pendiente)){
                        //Si no tiene venta pendiente
                        $result['error']=false;
                        $result['status']=$recibo->STATUS_COBRANZA;
                        $result['message']='La siguiente venta se encontraba con saldo pendiente';
                        $saldo = $recibo->saldo;
    
                        \Log::warning("[Recibo Error] Error - Intentando relanzar el recibo. ",['result' => $result]);
    
                        \DB::table('mt_movements')
                            ->where('id', $movement_id)
                            ->update([
                                'destination_operation_id' => 888,
                                'response' => json_encode($result),
                                'updated_at' => Carbon::now()
                        ]);
    
                        \DB::table('mt_recibos_cobranzas')
                            ->where('recibo_id', $recibo_id)
                            ->update([
                                'saldo_pendiente' => $saldo
                        ]);

                        \DB::table('mt_sales_affected_by_receipts')
                            ->where('receipt_id', $recibo_id)
                            ->update([
                                'deleted_at' => Carbon::now()
                        ]);

                        $service = new DepositoBoletaServices();

                        $recibo_id      = $recibo_id;
                        $now            = Carbon::now();
                        $description    = 'Recibo Cobranza RELANZADO insertado desde la Funcion: EctractosCOntroller@verificar_cobranza';

                        \DB::table('mt_sales_affected_by_receipts')
                            ->where('receipt_id', $recibo_id)
                            ->update([
                                'deleted_at' => Carbon::now()
                        ]);

                        $sales_affected = \DB::connection('ondanet')
                            ->table('LISTADO_VENTAS_COBRANZAS_SERVICIOS_MINITERMINALES')
                            ->selectRaw('DISTINCT STATUS_VENTA, convert(int, TOTAL_VENTA) as total_venta, convert(int, SALDO_VENTA) as saldo_venta, 
                            status_cobranza, nro_recibo, convert(int, IMPORTE_AFECTADO) as importe_afectado,
                            convert(int, TOTAL_RECIBO) as total_recibo, convert(int, saldo_recibo) as saldo ')
                            ->whereRaw("cliente like '%". $ruc . "%'")
                            ->where("nro_recibo", $nro_recibo)
                        ->get();

                        if(!is_null($sales_affected[0]->STATUS_VENTA)){
                            $status_afected=json_decode(json_encode($sales_affected), true);

                            $idventasondanet = \DB::table('mt_sales')
                                ->select('mt_sales.id as sale_id')
                                ->join('mt_movements as m', 'm.id', '=', 'mt_sales.movements_id')
                                ->where('m.group_id', $group_id )
                                ->whereNull('m.deleted_at')
                                ->whereRaw("m.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-16','-16','-17','-21','-23','-26','-27','212','999')")
                                ->whereIn('m.destination_operation_id', array_column($status_afected, 'STATUS_VENTA') )
                                ->orderBy('m.destination_operation_id', 'ASC')
                            ->get();

                            foreach($sales_affected as $key=>$sale){
                                $sale_id                = $idventasondanet[$key]->sale_id;
                                $sales_amount           = $sale->importe_afectado + $sale->saldo_venta;
                                $sales_amount_affected  = $sale->importe_afectado;
                                $sales_amount_pendding  = $sale->saldo_venta;

                                $service->insert_mt_sales_affected_by_receipts($recibo_id, $sale_id, $sales_amount, $sales_amount_affected, $sales_amount_pendding, $now, $description);
                            }
                        }  
    
                        $response['error'] = false;
                        $response['message']="El recibo_id $recibo_id se actualizo el status a 888 debido a que tiene saldo pero no ventas a afectar.";
    
                        return $response;
    
                    }

                    $total_venta=array_sum(array_column($ventas_pendiente, 'saldo')); //Monto pendiente de ventas

                    $dif = $total_venta - $saldo_recibo;

                    if($dif >= 0){ //Si el total de ventas es mayor que el saldo del recibo
                        //Se procede a afectar el total del recibo
    
                        $array=json_decode(json_encode($ventas_pendiente), true);
                        \Log::info('[Recibo Error] Ventas pendientes en ondanet: ', $array);
                        $i=0;
                        $sum=0;
                        //algoritmo para traer cuantas ventas van a ser cobradas
                        do{
    
                            $sum += $array[$i]['saldo'];
                            $i++;
                            
                        }while($sum < $saldo_recibo);
    
                        \Log::info("[Recibo a Error] La sumatoria es: " . $sum . " Y las veces que sumo fue: " . $i . "<p>");
    
                        $ventas_seleccionadas = array_slice($array, 0, $i); //Se trae la cantidad de ventas a cobrar
    
                        $ventasondanet = implode(';', array_column($ventas_seleccionadas, 'STATUS_VENTA'));
                        \Log::info('[Recibo Error] Ventas a cobrar', ['ventasondanet' => $ventasondanet]);
    
                    }else{//Si el saldo del reibo es mayor que el total de las ventas
                        //Se afecta parcialmente el recibo
                        $array=json_decode(json_encode($ventas_pendiente), true);
                        $ventasondanet = implode(';', array_column($array, 'STATUS_VENTA'));
                        \Log::info('[Recibo Error] Ventas a cobrar', ['ventasondanet' => $ventasondanet]);
    
                        $saldo_recibo=$total_venta; //Se cambia el saldo recibo por el valor pendiente
    
                    }
                    
                    //proceed to export deposits to ondanet
                    $id_ondanet =  $recibo->STATUS_COBRANZA;
                    $importe    =  $saldo_recibo;
                    $ventas     =  $ventasondanet;
    
                    $result= $service->registerCobranzasaFavor($id_ondanet, $importe, $ventas);
                    \Log::info('[Recibo error', $result);

                    $service = new DepositoBoletaServices();

                    $recibo_id      = $recibo_id;
                    $now            = Carbon::now();
                    $description    = 'Recibo Cobranza RELANZADO insertado desde la Funcion: EctractosCOntroller@verificar_cobranza';
    
                    if($result['error'] == false){
                        \Log::info("[Recibo Error] Exporting miniterminales cobranzas a favor to ondanet ",['result_error' => $result['error'], 'ondanet_rowId' => $result['status']]);
    
                        if($dif >= 0){
                            \DB::table('mt_movements')
                                ->where('id', $movement_id)
                                ->update([
                                    'destination_operation_id' => $result['status'],
                                    'response' => json_encode($result),
                                    'updated_at' => Carbon::now()
                            ]);
    
                            \DB::table('mt_recibos_cobranzas')
                                ->where('recibo_id', $recibo_id)
                                ->update([
                                    'ventas_cobradas' => $ventas,
                                    'saldo_pendiente' => 0
                            ]);
                        }else{
    
                            \DB::table('mt_movements')
                                ->where('id', $movement_id)
                                ->update([
                                    'destination_operation_id' => 888,
                                    'response' => json_encode($result),
                                    'updated_at' => Carbon::now()
                            ]);
    
                            \DB::table('mt_recibos_cobranzas')
                                ->where('recibo_id', $recibo_id)
                                ->update([
                                    'ventas_cobradas' => $ventas,
                                    'saldo_pendiente' => abs($dif)
                            ]);
                        }

                        \DB::table('mt_sales_affected_by_receipts')
                            ->where('receipt_id', $recibo_id)
                            ->update([
                                'deleted_at' => Carbon::now()
                        ]);

                        $sales_affected = \DB::connection('ondanet')
                            ->table('LISTADO_VENTAS_COBRANZAS_SERVICIOS_MINITERMINALES')
                            ->selectRaw('DISTINCT STATUS_VENTA, convert(int, TOTAL_VENTA) as total_venta, convert(int, SALDO_VENTA) as saldo_venta, 
                            status_cobranza, nro_recibo, convert(int, IMPORTE_AFECTADO) as importe_afectado,
                            convert(int, TOTAL_RECIBO) as total_recibo, convert(int, saldo_recibo) as saldo ')
                            ->whereRaw("cliente like '%". $ruc . "%'")
                            ->where("nro_recibo", $nro_recibo)
                        ->get();

                        if(!is_null($sales_affected[0]->STATUS_VENTA)){
                            $status_afected=json_decode(json_encode($sales_affected), true);

                            $idventasondanet = \DB::table('mt_sales')
                                ->select('mt_sales.id as sale_id')
                                ->join('mt_movements as m', 'm.id', '=', 'mt_sales.movements_id')
                                ->where('m.group_id', $group_id )
                                ->whereNull('m.deleted_at')
                                ->whereRaw("m.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-16','-16','-17','-21','-23','-26','-27','212','999')")
                                ->whereIn('m.destination_operation_id', array_column($status_afected, 'STATUS_VENTA') )
                                ->orderBy('m.destination_operation_id', 'ASC')
                            ->get();

                            foreach($sales_affected as $key=>$sale){
                                $sale_id                = $idventasondanet[$key]->sale_id;
                                $sales_amount           = $sale->importe_afectado + $sale->saldo_venta;
                                $sales_amount_affected  = $sale->importe_afectado;
                                $sales_amount_pendding  = $sale->saldo_venta;

                                $service->insert_mt_sales_affected_by_receipts($recibo_id, $sale_id, $sales_amount, $sales_amount_affected, $sales_amount_pendding, $now, $description);
                            }
                        }
    
                    }else{
                        $result['status']=$id_ondanet;
                        \Log::warning("[Recibo a Favor 777] Error - Exporting miniterminales cobranzas a favor to ondanet ",['result' => $result]);
    
                        \DB::table('mt_movements')
                            ->where('id', $movement_id)
                            ->update([
                                'destination_operation_id' => 1,
                                'response' => json_encode($result),
                                'updated_at' => Carbon::now()
                        ]);
                    }
                }
            }else{
                $recibo = \DB::connection('ondanet')
                    ->table('LISTADO_VENTAS_COBRANZAS_SERVICIOS_MINITERMINALES')
                    ->selectRaw('*, convert(int, saldo_recibo) as saldo ')
                    ->whereRaw("cliente like '%". $ruc . "%'")
                    ->where("nro_recibo", $nro_recibo)
                ->first();

                if(empty($recibo)){
                    //Si no tiene venta pendiente
                    $result['error']=true;
                    $result['message']='El siguiente numero de recibo no existe en ondanet';

                    \Log::warning("[Recibo a Favor 777] Error - Intentando relanzar el recibo. ",['result' => $result]);

                    \DB::table('mt_movements')
                        ->where('id', $movement_id)
                        ->update([
                            'destination_operation_id' => 1,
                            'response' => json_encode($result),
                            'updated_at' => Carbon::now()
                    ]);

                    $response['error'] = false;
                    $response['message']='Se actualizo el status a error debido a que el recibo no existe.';

                    return $response;

                }

                $saldo_recibo=$recibo->saldo; //Saldo pendiente del recibo

                if($saldo_recibo  == 0){
                    //Si no tiene venta pendiente
                    $result['error']=false;
                    $result['message']='El siguiente numero de recibo no tiene saldo pendiente';

                    \Log::warning("[Recibo a Favor 777] Error - El numero de recibo no tiene saldo pendiente. ",['result' => $result]);

                    \DB::table('mt_movements')
                        ->where('id', $movement_id)
                        ->update([
                            'destination_operation_id' => $recibo->STATUS_COBRANZA,
                            'response' => json_encode($result),
                            'updated_at' => Carbon::now()
                    ]);

                    $response['error'] = false;
                    $response['message']="El recibo_id $recibo_id no tiene saldo pendiente, se actualizo con el status de ondanet.";

                    return $response;

                }

                $ventas_pendiente = \DB::connection('ondanet')
                    ->table('LISTADO_VENTAS_COBRANZAS_SERVICIOS_MINITERMINALES')
                    ->selectRaw('distinct STATUS_VENTA, NRO_VENTA, TOTAL_VENTA, FECHA_VENTA, saldo_venta, convert(int, saldo_venta) as saldo')
                    ->whereRaw("cliente like '%". $ruc . "%'")
                    ->where('SALDO_VENTA', '!=', 0)
                ->get();
                
                if(empty($ventas_pendiente)){
                    //Si no tiene venta pendiente
                    $result['error']=false;
                    $result['status']=$recibo->STATUS_COBRANZA;
                    $result['message']='La siguiente venta se encontraba con saldo pendiente';

                    \Log::warning("[Recibo a Favor 777] Error - Intentando relanzar el recibo. ",['result' => $result]);

                    \DB::table('mt_movements')
                        ->where('id', $movement_id)
                        ->update([
                            'destination_operation_id' => 888,
                            'response' => json_encode($result),
                            'updated_at' => Carbon::now()
                    ]);

                    \DB::table('mt_recibos_cobranzas')
                        ->where('recibo_id', $recibo_id)
                        ->update([
                            'saldo_pendiente' => $saldo_recibo
                    ]);

                    \DB::table('mt_sales_affected_by_receipts')
                        ->where('receipt_id', $recibo_id)
                        ->update([
                            'deleted_at' => Carbon::now()
                    ]);

                    $service = new DepositoBoletaServices();

                    $recibo_id      = $recibo_id;
                    $now            = Carbon::now();
                    $description    = 'Recibo Cobranza RELANZADO insertado desde la Funcion: EctractosCOntroller@verificar_cobranza';

                    $sales_affected = \DB::connection('ondanet')
                        ->table('LISTADO_VENTAS_COBRANZAS_SERVICIOS_MINITERMINALES')
                        ->selectRaw('DISTINCT STATUS_VENTA, convert(int, TOTAL_VENTA) as total_venta, convert(int, SALDO_VENTA) as saldo_venta, 
                        status_cobranza, nro_recibo, convert(int, IMPORTE_AFECTADO) as importe_afectado,
                        convert(int, TOTAL_RECIBO) as total_recibo, convert(int, saldo_recibo) as saldo ')
                        ->whereRaw("cliente like '%". $ruc . "%'")
                        ->where("nro_recibo", $nro_recibo)
                    ->get();

                    if(!is_null($sales_affected[0]->STATUS_VENTA)){
                        $status_afected=json_decode(json_encode($sales_affected), true);

                        $idventasondanet = \DB::table('mt_sales')
                            ->select('mt_sales.id as sale_id')
                            ->join('mt_movements as m', 'm.id', '=', 'mt_sales.movements_id')
                            ->where('m.group_id', $group_id )
                            ->whereNull('m.deleted_at')
                            ->whereRaw("m.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-16','-16','-17','-21','-23','-26','-27','212','999')")
                            ->whereIn('m.destination_operation_id', array_column($status_afected, 'STATUS_VENTA') )
                            ->orderBy('m.destination_operation_id', 'ASC')
                        ->get();
    
                        foreach($sales_affected as $key=>$sale){
                            $sale_id                = $idventasondanet[$key]->sale_id;
                            $sales_amount           = $sale->importe_afectado + $sale->saldo_venta;
                            $sales_amount_affected  = $sale->importe_afectado;
                            $sales_amount_pendding  = $sale->saldo_venta;
    
                            $service->insert_mt_sales_affected_by_receipts($recibo_id, $sale_id, $sales_amount, $sales_amount_affected, $sales_amount_pendding, $now, $description);
                        }
                    }

                    $response['error'] = false;
                    $response['message']="El recibo_id $recibo_id se actualizo el status a 888 debido a que tiene saldo pero no ventas a afectar.";

                    return $response;

                }

                $total_venta=array_sum(array_column($ventas_pendiente, 'saldo')); //Monto pendiente de ventas

                $dif = $total_venta - $saldo_recibo;

                if($dif >= 0){ //Si el total de ventas es mayor que el saldo del recibo
                    //Se procede a afectar el total del recibo

                    $array=json_decode(json_encode($ventas_pendiente), true);
                    \Log::info('[Recibo a Favor 777] Ventas pendientes en ondanet: ', $array);
                    $i=0;
                    $sum=0;
                    //algoritmo para traer cuantas ventas van a ser cobradas
                    do{

                        $sum += $array[$i]['saldo'];
                        $i++;
                        
                    }while($sum < $saldo_recibo);

                    \Log::info("[Recibo a Favor 777] La sumatoria es: " . $sum . " Y las veces que sumo fue: " . $i . "<p>");

                    $ventas_seleccionadas = array_slice($array, 0, $i); //Se trae la cantidad de ventas a cobrar

                    /*$idventasondanet = \DB::table('mt_sales')
                        ->select('mt_sales.id as sale_id')
                        ->join('mt_movements as m', 'm.id', '=', 'mt_sales.movements_id')
                        ->where('m.group_id', $group_id )
                        ->whereNull('m.deleted_at')
                        ->whereRaw("m.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-16','-16','-17','-21','-23','-26','-27','212','999')")
                        ->whereIn('m.destination_operation_id', array_column($ventas_seleccionadas, 'STATUS_VENTA') )
                        ->orderBy('m.destination_operation_id', 'ASC')
                    ->get();

                    \Log::info('[Recibo a Favor 777] Ventas de nuestro lado: ', json_decode(json_encode($idventasondanet), true));*/

                    $ventasondanet = implode(';', array_column($ventas_seleccionadas, 'STATUS_VENTA'));
                    \Log::info('[Recibo a Favor 777] Ventas a cobrar', ['ventasondanet' => $ventasondanet]);

                }else{//Si el saldo del reibo es mayor que el total de las ventas
                    //Se afecta parcialmente el recibo
                    $array=json_decode(json_encode($ventas_pendiente), true);

                    /*$idventasondanet = \DB::table('mt_sales')
                        ->select('mt_sales.id as sale_id')
                        ->join('mt_movements as m', 'm.id', '=', 'mt_sales.movements_id')
                        ->where('m.group_id', $group_id )
                        ->whereNull('m.deleted_at')
                        ->whereRaw("m.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-16','-16','-17','-21','-23','-26','-27','212','999')")
                        ->whereIn('m.destination_operation_id', array_column($array, 'STATUS_VENTA') )
                        ->orderBy('m.destination_operation_id', 'ASC')
                    ->get();

                    \Log::info('[Recibo a Favor 777] Ventas de nuestro lado: ', json_decode(json_encode($idventasondanet), true));*/

                    $ventasondanet = implode(';', array_column($array, 'STATUS_VENTA'));
                    \Log::info('[Recibo a Favor 777] Ventas a cobrar', ['ventasondanet' => $ventasondanet]);

                    $saldo_recibo=$total_venta; //Se cambia el saldo recibo por el valor pendiente

                }
                
                //proceed to export deposits to ondanet
                $id_ondanet =  $recibo->STATUS_COBRANZA;
                $importe    =  $saldo_recibo;
                $ventas     =  $ventasondanet;

                $result= $service->registerCobranzasaFavor($id_ondanet, $importe, $ventas);
                \Log::info('[Recibo a Favor 777]', $result);

                $service = new DepositoBoletaServices();

                $recibo_id      = $recibo_id;
                $now            = Carbon::now();
                $description    = 'Recibo Cobranza RELANZADO insertado desde la Funcion: EctractosCOntroller@verificar_cobranza';

                if($result['error'] == false){
                    \Log::info("[Recibo a Favor 777] Exporting miniterminales cobranzas a favor to ondanet ",['result_error' => $result['error'], 'ondanet_rowId' => $result['status']]);

                    if($dif >= 0){
                        \DB::table('mt_movements')
                            ->where('id', $movement_id)
                            ->update([
                                'destination_operation_id' => $result['status'],
                                'response' => json_encode($result),
                                'updated_at' => Carbon::now()
                        ]);

                        \DB::table('mt_recibos_cobranzas')
                            ->where('recibo_id', $recibo_id)
                            ->update([
                                'ventas_cobradas' => $ventas,
                                'saldo_pendiente' => 0
                        ]);

                        /*if($i == 1){
                            $venta = $ventas_seleccionadas[0];
                            $movimiento = $idventasondanet[0];
                            $sale_id                = $movimiento->sale_id;
                            $sales_amount           = $venta['saldo'];
                            $sales_amount_affected  = $monto;
                            $sales_amount_pendding  = $dif;
                            
                            $service->insert_mt_sales_affected_by_receipts($recibo_id, $sale_id, $sales_amount, $sales_amount_affected, $sales_amount_pendding, $now, $description);
                        }else{
                            if($dif == 0){
                                foreach($ventas_seleccionadas as $key=>$venta_seleccionada){
                                    $sale_id                = $idventasondanet[$key]->sale_id;
                                    $sales_amount           = $venta_seleccionada['saldo'];
                                    $sales_amount_affected  = $venta_seleccionada['saldo'];
                                    $sales_amount_pendding  = $dif;

                                    $service->insert_mt_sales_affected_by_receipts($recibo_id, $sale_id, $sales_amount, $sales_amount_affected, $sales_amount_pendding, $now, $description);
                                }
                            }else{
                                $sobrante = end($ventas_seleccionadas);
                                $sobrante_id_sale = end($idventasondanet);

                                foreach($ventas_seleccionadas as $key=>$venta_seleccionada){
                                    if($sobrante !== $venta_seleccionada){
                                        $sale_id                = $idventasondanet[$key]->sale_id;
                                        $sales_amount           = $venta_seleccionada['saldo'];
                                        $sales_amount_affected  = $venta_seleccionada['saldo'];
                                        $sales_amount_pendding  = 0;

                                        $service->insert_mt_sales_affected_by_receipts($recibo_id, $sale_id, $sales_amount, $sales_amount_affected, $sales_amount_pendding, $now, $description);
                                    }
                                }

                                $sale_id                = $sobrante_id_sale->sale_id;
                                $sales_amount           = $sobrante->monto_por_cobrar;
                                $sales_amount_affected  = $sobrante->monto_por_cobrar - $dif;
                                $sales_amount_pendding  = $dif;

                                $service->insert_mt_sales_affected_by_receipts($recibo_id, $sale_id, $sales_amount, $sales_amount_affected, $sales_amount_pendding, $now, $description);                                    
                            }
                        }*/
                    }else{

                        \DB::table('mt_movements')
                            ->where('id', $movement_id)
                            ->update([
                                'destination_operation_id' => 888,
                                'response' => json_encode($result),
                                'updated_at' => Carbon::now()
                        ]);

                        \DB::table('mt_recibos_cobranzas')
                            ->where('recibo_id', $recibo_id)
                            ->update([
                                'ventas_cobradas' => $ventas,
                                'saldo_pendiente' => abs($dif)
                        ]);

                        /*foreach($ventas_seleccionadas as $key=>$venta_seleccionada){
                            $sale_id                = $idventasondanet[$key]->sale_id;
                            $sales_amount           = $venta_seleccionada->saldo;
                            $sales_amount_affected  = $venta_seleccionada->saldo;
                            $sales_amount_pendding  = 0;

                            $service->insert_mt_sales_affected_by_receipts($recibo_id, $sale_id, $sales_amount, $sales_amount_affected, $sales_amount_pendding, $now, $description);
                        } */
                    }

                    \DB::table('mt_sales_affected_by_receipts')
                        ->where('receipt_id', $recibo_id)
                        ->update([
                            'deleted_at' => Carbon::now()
                    ]);

                    $sales_affected = \DB::connection('ondanet')
                        ->table('LISTADO_VENTAS_COBRANZAS_SERVICIOS_MINITERMINALES')
                        ->selectRaw('DISTINCT STATUS_VENTA, convert(int, TOTAL_VENTA) as total_venta, convert(int, SALDO_VENTA) as saldo_venta, 
                        status_cobranza, nro_recibo, convert(int, IMPORTE_AFECTADO) as importe_afectado,
                        convert(int, TOTAL_RECIBO) as total_recibo, convert(int, saldo_recibo) as saldo ')
                        ->whereRaw("cliente like '%". $ruc . "%'")
                        ->where("nro_recibo", $nro_recibo)
                    ->get();

                    $status_afected=json_decode(json_encode($sales_affected), true);

                    $idventasondanet = \DB::table('mt_sales')
                        ->select('mt_sales.id as sale_id')
                        ->join('mt_movements as m', 'm.id', '=', 'mt_sales.movements_id')
                        ->where('m.group_id', $group_id )
                        ->whereNull('m.deleted_at')
                        ->whereRaw("m.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-16','-16','-17','-21','-23','-26','-27','212','999')")
                        ->whereIn('m.destination_operation_id', array_column($status_afected, 'STATUS_VENTA') )
                        ->orderBy('m.destination_operation_id', 'ASC')
                    ->get();

                    foreach($sales_affected as $key=>$sale){
                        $sale_id                = $idventasondanet[$key]->sale_id;
                        $sales_amount           = $sale->importe_afectado + $sale->saldo_venta;
                        $sales_amount_affected  = $sale->importe_afectado;
                        $sales_amount_pendding  = $sale->saldo_venta;

                        $service->insert_mt_sales_affected_by_receipts($recibo_id, $sale_id, $sales_amount, $sales_amount_affected, $sales_amount_pendding, $now, $description);
                    }

                }else{
                    $result['status']=$id_ondanet;
                    \Log::warning("[Recibo a Favor 777] Error - Exporting miniterminales cobranzas a favor to ondanet ",['result' => $result]);

                    \DB::table('mt_movements')
                        ->where('id', $movement_id)
                        ->update([
                            'destination_operation_id' => 1,
                            'response' => json_encode($result),
                            'updated_at' => Carbon::now()
                    ]);
                }
            }
            $response['error'] = false;
            $response['message']="Se ha Relanzado el recibo_id $recibo_id y actualizado las ventas correctamente";

            return $response;
        }catch(\Exception $e){
            $error_message =
                [
                    'error' => true,
                    'message' => "Ha ocurrido un error al intentar relanzar recibo_id $recibo_id.",
                ];
            \Log::warning('[Recibo a Favor 777] Error - Exporting miniterminales cobranzas a favor to ondanet', ['result' => $e]);
            return $error_message;
        }

        

    }

    public function update_sales($group_id, $ruc){
        \DB::beginTransaction();
        try{

            \Log::info('[UPDATE SALES] Inicio procedimiento de actualizar ventas para el grupo: '. $group_id);
            //Se consulta las ventas pendientes en ondanet
            $ventas_pendiente = \DB::connection('ondanet')
                ->table('LISTADO_VENTAS_COBRANZAS_SERVICIOS_MINITERMINALES')
                ->selectRaw('distinct STATUS_VENTA, NRO_VENTA, TOTAL_VENTA, FECHA_VENTA, saldo_venta, convert(int, saldo_venta) as saldo')
                ->whereRaw("cliente like '%". $ruc . "%'")
                ->where('SALDO_VENTA', '!=', 0)
            ->get();

            //Se consultan todas las ventas de eglobalt
            $idventas = \DB::table('mt_sales')
                ->select('m.id', 'mt_sales.monto_por_cobrar')
                ->join('mt_movements as m', 'm.id', '=', 'mt_sales.movements_id')
                ->where('m.group_id', $group_id)
                ->where('mt_sales.estado', 'pendiente')
                ->whereNull('m.deleted_at')
                ->whereRaw("m.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-15','-16','-17','-21','-23','-26','-27','212','999')")
                ->orderBy('m.destination_operation_id','ASC')
            ->get();
            
            $total_venta_ondanet=array_sum(array_column($ventas_pendiente, 'saldo')); //Monto pendiente de ventas
            $total_venta_eglobalt=array_sum(array_column($idventas, 'monto_por_cobrar')); //Monto pendiente de ventas

            $dif=($total_venta_ondanet - $total_venta_eglobalt);

            \Log::info('[UPDATE SALES] La diferencia de ventas que habia es: '. $dif);

            if($dif != 0){ //Si la diferencia es diferente de cero significa que se debe actualizar las ventas

                foreach($idventas as $venta){ //Se cancelan todas las ventas para actualizarlas correctamente
                    \DB::table('mt_sales')
                    ->where('movements_id', $venta->id)
                    ->update([
                        'estado'            => 'cancelado',
                        'monto_por_cobrar'   => 0
                    ]);
                }

                foreach($ventas_pendiente as $venta_pendiente){

                    $status_venta=(int)$venta_pendiente->STATUS_VENTA;
    
                    $venta=\DB::table('mt_sales')
                        ->select('mt_sales.*')
                        ->join('mt_movements as m', 'm.id', '=', 'mt_sales.movements_id')
                        ->where('m.group_id', $group_id)
                        ->where('m.destination_operation_id', $status_venta)
                        ->whereNull('m.deleted_at')
                    ->first();
    
                    if(!empty($venta)){ //Se actualian las ventas con el saldo correspondiente
                        \DB::table('mt_sales')
                        ->where('movements_id', $venta->movements_id)
                        ->update([
                            'estado'            => 'pendiente',
                            'monto_por_cobrar'   => $venta_pendiente->saldo
                        ]);
                    }
    
                }
            }

            $result['error'] = false;
            $result['message'] = '[UPDATE SALES] Ventas sincronizadas con ondanet correctamente.';
            \DB::commit();
            return $result;

        }catch(\Exception $e){
            \DB::rollback();
            $error_message =
                [
                    'error' => true,
                    'message' => '[UPDATE SALES] Ha ocurrido un error al sincronizar las ventas.',
                ];
            \Log::warning('[UPDATE SALES] Ha ocurrido un error al sincronizar las ventas.', ['result' => $e]);
            return $error_message;
        }
    }

    public function relanzarCashout(Request $request)
    {
        $id = $request->_id;
        
        $movement = \DB::table('mt_movements')->where('id', $id)->first();

        $result = $this->verificar_cashout($movement->id);

        \Log::info('[Relanzar Recibos Cashouts]', ['movement_id' => $movement->id, 'result' => $result]);

        //Actualizar ventas ondanet
        $update_sale = $this->update_sales($movement->group_id, $result['ruc']);
        \Log::info('[Update Sales]', $update_sale);

        $response['error'] = false;

        if($update_sale['error']){
            $result['message'] .=", pero hubo un error al actualizar las ventas";
        }

        if(!$result['error']){
            Session::flash('message', $result['message']);
        }else{
            Session::flash('error_message', $result['message']);
        }

        return response()->json([
            'error' => $result['error'],
            'message' => $result['message'],
        ]);
    }

    public function verificar_cashout($movement_id){
        try{
            $movement=\DB::table('mt_movements as m')
                ->selectRaw('m.*, bg.description, bg.ruc, bg.id, mt_recibos.recibo_nro, mt_recibos.id as id_recibo, mt_recibos.monto, mtc.transaction_id')
                ->join('business_groups as bg', 'bg.id', '=', 'm.group_id')
                ->join('mt_recibos', 'm.id', '=', 'mt_recibos.mt_movements_id')
                ->join('mt_recibos_cashouts as mtc', 'mt_recibos.id', '=', 'mtc.recibo_id')
                ->where('m.id', $movement_id)   
            ->first();

            $status     = $movement->destination_operation_id;
            $ruc        = $movement->ruc;
            $nro_recibo = $movement->recibo_nro;
            $recibo_id  = $movement->id_recibo;
            $monto      = $movement->monto;
            $fecha      = $movement->created_at;
            $group_id   = $movement->group_id;
            $response['ruc'] = $ruc;

            $service = new OndanetServices();

            //Se consulta si el recibo existe en ondanet
            $recibo = \DB::connection('ondanet')
                ->table('LISTADO_VENTAS_COBRANZAS_SERVICIOS_MINITERMINALES')
                ->selectRaw('*, convert(int, saldo_recibo) as saldo ')
                ->whereRaw("cliente like '%". $ruc . "%'")
                ->where("nro_recibo", $nro_recibo)
            ->first();

            $ventas_pendiente = \DB::connection('ondanet')
                ->table('LISTADO_VENTAS_COBRANZAS_SERVICIOS_MINITERMINALES')
                ->selectRaw('distinct STATUS_VENTA, NRO_VENTA, TOTAL_VENTA, FECHA_VENTA, saldo_venta, convert(int, saldo_venta) as saldo')
                ->whereRaw("cliente like '%". $ruc . "%'")
                ->where('SALDO_VENTA', '>', 0)
            ->get();

            if(empty($recibo)){
                //Si no existe el recibo
                $transaction=\DB::table('mt_recibos as mr')
                    ->selectRaw('t.created_at as fecha, abs(t.amount) as importe, t.service_id, t.service_source_id')
                    ->join('mt_recibos_cashouts as mtc', 'mr.id', '=', 'mtc.recibo_id')
                    ->join('transactions as t', 't.id', '=', 'mtc.transaction_id')
                    ->where('mr.id', $recibo_id)   
                ->first();
            
                $recibo         =  $nro_recibo;
                $fecha          =  date("d-m-Y", strtotime($transaction->fecha));
                $importe        =  $transaction->importe;
                $pdv            =  $ruc;

                if($transaction->service_source_id == 8 ){
                    if($transaction->service_id == 92){
                        $comprobante    = 'REC';
                        $forcobro       = 'EFEPE';
                    }else{
                        $comprobante    = 'RCASH';
                        $forcobro       = 'CASTO';
                    }
                }else{
                    if($transaction->service_id == 87 && $transaction->service_source_id == 0){
                        $comprobante    = 'RCASH';
                        $forcobro       = 'EAPOS';
                        $importe        = (int)($importe * 0.97);
                    }else{
                        $comprobante    = 'RCASH';
                        $forcobro       = 'RCASH';
                    }
                }

                $result= $service->registerRecibosaFavor($recibo, $fecha, $importe, $pdv, $comprobante, $forcobro);
                \Log::info('[Recibo A Favor Cashout]', $result);

                if(!$result['error']){
                    \Log::info("[Recibo A Favor Cashout] Exporting recibos a favor to ondanet ",['result_error' => $result['error'], 'ondanet_rowId' => $result['status']]);

                    \DB::table('mt_movements')
                        ->where('id', $movement_id)
                        ->update([
                            'destination_operation_id' => 888,
                            'response' => json_encode($result),
                            'updated_at' => Carbon::now()
                    ]);
                    
                    $response['error'] = false;
                    $response['message']="El recibo_id $recibo_id se actualizo el status a 888 debido a que tiene saldo pero no ventas a afectar.";

                }else{
                    \Log::warning("[ondanet Cashout] Error - Exporting miniterminales cashouts to ondanet ",['result' => $result]);

                    \DB::table('mt_movements')
                        ->where('id', $movement_id)
                        ->update([
                            'destination_operation_id' => 1,
                            'response' => json_encode($result),
                            'updated_at' => Carbon::now()
                    ]);

                    $response['error'] = true;
                    $response['message']="Ocurrio un error al intentar migrar a ondanet el recibo a favor.";
                }

                \DB::table('mt_recibos_cashouts')
                    ->where('recibo_id', $recibo_id)
                    ->update([
                        'ventas_cobradas' => null,
                        'saldo_pendiente' => $monto
                ]);

                \DB::table('mt_sales_affected_by_receipts')
                    ->where('receipt_id', $recibo_id)
                    ->update([
                        'deleted_at' => Carbon::now()
                ]);

                if(empty($ventas_pendiente)){
                    return $response;
                }else{
                    return $this->verificar_cashout($movement_id);
                }
            }else{

                $saldo_recibo=$recibo->saldo; //Saldo pendiente del recibo
                if($saldo_recibo  == 0){
                    //Si no tiene venta pendiente
                    $result['error']=false;
                    $result['message']='El siguiente numero de recibo no tiene saldo pendiente';

                    \Log::warning("[Recibo CASHOUT] Error - El numero de recibo no tiene saldo pendiente. ",['result' => $result]);

                    \DB::table('mt_movements')
                        ->where('id', $movement_id)
                        ->update([
                            'destination_operation_id' => $recibo->STATUS_COBRANZA,
                            'response' => json_encode($result),
                            'updated_at' => Carbon::now()
                    ]);

                    $response['error'] = false;
                    $response['message']="El recibo_id $recibo_id no tiene saldo pendiente, se actualizo con el status de ondanet.";

                    return $response;
                }

                if(empty($ventas_pendiente)){
                    //Si no tiene venta pendiente
                    $result['error']=false;
                    $result['status']=$recibo->STATUS_COBRANZA;
                    $result['message']='La siguiente venta se encontraba con saldo pendiente';
                    $saldo = $recibo->saldo;

                    \Log::warning("[Recibo Cashout] Error - Intentando relanzar el recibo. ",['result' => $result]);

                    \DB::table('mt_movements')
                        ->where('id', $movement_id)
                        ->update([
                            'destination_operation_id' => 888,
                            'response' => json_encode($result),
                            'updated_at' => Carbon::now()
                    ]);

                    \DB::table('mt_recibos_cashouts')
                        ->where('recibo_id', $recibo_id)
                        ->update([
                            'saldo_pendiente' => $saldo
                    ]);

                    \DB::table('mt_sales_affected_by_receipts')
                        ->where('receipt_id', $recibo_id)
                        ->update([
                            'deleted_at' => Carbon::now()
                    ]);

                    $service = new DepositoBoletaServices();

                    $recibo_id      = $recibo_id;
                    $now            = Carbon::now();
                    $description    = 'Recibo Cobranza RELANZADO insertado desde la Funcion: EctractosCOntroller@verificar_cobranza';

                    \DB::table('mt_sales_affected_by_receipts')
                        ->where('receipt_id', $recibo_id)
                        ->update([
                            'deleted_at' => Carbon::now()
                    ]);

                    $sales_affected = \DB::connection('ondanet')
                        ->table('LISTADO_VENTAS_COBRANZAS_SERVICIOS_MINITERMINALES')
                        ->selectRaw('DISTINCT STATUS_VENTA, convert(int, TOTAL_VENTA) as total_venta, convert(int, SALDO_VENTA) as saldo_venta, 
                        status_cobranza, nro_recibo, convert(int, IMPORTE_AFECTADO) as importe_afectado,
                        convert(int, TOTAL_RECIBO) as total_recibo, convert(int, saldo_recibo) as saldo ')
                        ->whereRaw("cliente like '%". $ruc . "%'")
                        ->where("nro_recibo", $nro_recibo)
                    ->get();

                    if(!is_null($sales_affected[0]->STATUS_VENTA)){
                        $status_afected=json_decode(json_encode($sales_affected), true);

                        $idventasondanet = \DB::table('mt_sales')
                            ->select('mt_sales.id as sale_id')
                            ->join('mt_movements as m', 'm.id', '=', 'mt_sales.movements_id')
                            ->where('m.group_id', $group_id )
                            ->whereNull('m.deleted_at')
                            ->whereRaw("m.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-16','-16','-17','-21','-23','-26','-27','212','999')")
                            ->whereIn('m.destination_operation_id', array_column($status_afected, 'STATUS_VENTA') )
                            ->orderBy('m.destination_operation_id', 'ASC')
                        ->get();

                        foreach($sales_affected as $key=>$sale){
                            $sale_id                = $idventasondanet[$key]->sale_id;
                            $sales_amount           = $sale->importe_afectado + $sale->saldo_venta;
                            $sales_amount_affected  = $sale->importe_afectado;
                            $sales_amount_pendding  = $sale->saldo_venta;

                            $service->insert_mt_sales_affected_by_receipts($recibo_id, $sale_id, $sales_amount, $sales_amount_affected, $sales_amount_pendding, $now, $description);
                        }
                    }

                    $response['error'] = false;
                    $response['message']="El recibo_id $recibo_id se actualizo el status a 888 debido a que tiene saldo pero no ventas a afectar.";

                    return $response;

                }

                $total_venta=array_sum(array_column($ventas_pendiente, 'saldo')); //Monto pendiente de ventas

                $dif = $total_venta - $saldo_recibo;

                if($dif >= 0){ //Si el total de ventas es mayor que el saldo del recibo
                    //Se procede a afectar el total del recibo

                    $array=json_decode(json_encode($ventas_pendiente), true);
                    \Log::info('[Recibo Error Cashout] Ventas pendientes en ondanet: ', $array);
                    $i=0;
                    $sum=0;
                    //algoritmo para traer cuantas ventas van a ser cobradas
                    do{

                        $sum += $array[$i]['saldo'];
                        $i++;
                        
                    }while($sum < $saldo_recibo);

                    \Log::info("[Recibo a Error Cashout] La sumatoria es: " . $sum . " Y las veces que sumo fue: " . $i . "<p>");

                    $ventas_seleccionadas = array_slice($array, 0, $i); //Se trae la cantidad de ventas a cobrar

                    $ventasondanet = implode(';', array_column($ventas_seleccionadas, 'STATUS_VENTA'));
                    \Log::info('[Recibo Error Cashout] Ventas a cobrar', ['ventasondanet' => $ventasondanet]);

                }else{//Si el saldo del reibo es mayor que el total de las ventas
                    //Se afecta parcialmente el recibo
                    $array=json_decode(json_encode($ventas_pendiente), true);
                    $ventasondanet = implode(';', array_column($array, 'STATUS_VENTA'));
                    \Log::info('[Recibo Error Cashout] Ventas a cobrar', ['ventasondanet' => $ventasondanet]);

                    $saldo_recibo=$total_venta; //Se cambia el saldo recibo por el valor pendiente

                }
                
                //proceed to export deposits to ondanet
                $id_ondanet =  $recibo->STATUS_COBRANZA;
                $importe    =  $saldo_recibo;
                $ventas     =  $ventasondanet;

                $result= $service->registerCobranzasaFavor($id_ondanet, $importe, $ventas);
                \Log::info('[Recibo error Cashout', $result);

                $service = new DepositoBoletaServices();

                $recibo_id      = $recibo_id;
                $now            = Carbon::now();
                $description    = 'Recibo Cobranza RELANZADO insertado desde la Funcion: EctractosCOntroller@verificar_cobranza';

                if($result['error'] == false){
                    \Log::info("[Recibo Error Cashout] Exporting miniterminales cobranzas a favor to ondanet ",['result_error' => $result['error'], 'ondanet_rowId' => $result['status']]);

                    if($dif >= 0){
                        \DB::table('mt_movements')
                            ->where('id', $movement_id)
                            ->update([
                                'destination_operation_id' => $result['status'],
                                'response' => json_encode($result),
                                'updated_at' => Carbon::now()
                        ]);

                        \DB::table('mt_recibos_cashouts')
                            ->where('recibo_id', $recibo_id)
                            ->update([
                                'ventas_cobradas' => $ventas,
                                'saldo_pendiente' => 0
                        ]);
                    }else{

                        \DB::table('mt_movements')
                            ->where('id', $movement_id)
                            ->update([
                                'destination_operation_id' => 888,
                                'response' => json_encode($result),
                                'updated_at' => Carbon::now()
                        ]);

                        \DB::table('mt_recibos_cashouts')
                            ->where('recibo_id', $recibo_id)
                            ->update([
                                'ventas_cobradas' => $ventas,
                                'saldo_pendiente' => abs($dif)
                        ]);
                    }

                    \DB::table('mt_sales_affected_by_receipts')
                        ->where('receipt_id', $recibo_id)
                        ->update([
                            'deleted_at' => Carbon::now()
                    ]);

                    $sales_affected = \DB::connection('ondanet')
                        ->table('LISTADO_VENTAS_COBRANZAS_SERVICIOS_MINITERMINALES')
                        ->selectRaw('DISTINCT STATUS_VENTA, convert(int, TOTAL_VENTA) as total_venta, convert(int, SALDO_VENTA) as saldo_venta, 
                        status_cobranza, nro_recibo, convert(int, IMPORTE_AFECTADO) as importe_afectado,
                        convert(int, TOTAL_RECIBO) as total_recibo, convert(int, saldo_recibo) as saldo ')
                        ->whereRaw("cliente like '%". $ruc . "%'")
                        ->where("nro_recibo", $nro_recibo)
                    ->get();

                    if(!is_null($sales_affected[0]->STATUS_VENTA)){
                        $status_afected=json_decode(json_encode($sales_affected), true);

                        $idventasondanet = \DB::table('mt_sales')
                            ->select('mt_sales.id as sale_id')
                            ->join('mt_movements as m', 'm.id', '=', 'mt_sales.movements_id')
                            ->where('m.group_id', $group_id )
                            ->whereNull('m.deleted_at')
                            ->whereRaw("m.destination_operation_id not in ('0','1','-2','-3','-4','-5','6','-9','-10','-11','-12','-13','-14','-16','-16','-17','-21','-23','-26','-27','212','999')")
                            ->whereIn('m.destination_operation_id', array_column($status_afected, 'STATUS_VENTA') )
                            ->orderBy('m.destination_operation_id', 'ASC')
                        ->get();

                        foreach($sales_affected as $key=>$sale){
                            $sale_id                = $idventasondanet[$key]->sale_id;
                            $sales_amount           = $sale->importe_afectado + $sale->saldo_venta;
                            $sales_amount_affected  = $sale->importe_afectado;
                            $sales_amount_pendding  = $sale->saldo_venta;

                            $service->insert_mt_sales_affected_by_receipts($recibo_id, $sale_id, $sales_amount, $sales_amount_affected, $sales_amount_pendding, $now, $description);
                        }
                    }

                }else{
                    $result['status']=$id_ondanet;
                    \Log::warning("[Recibo a Favor 777 Cashouts] Error - Exporting miniterminales cashouts a favor to ondanet ",['result' => $result]);

                    \DB::table('mt_movements')
                        ->where('id', $movement_id)
                        ->update([
                            'destination_operation_id' => 1,
                            'response' => json_encode($result),
                            'updated_at' => Carbon::now()
                    ]);
                }
            }

            $response['error'] = false;
            $response['message']="Se ha Relanzado el recibo_id $recibo_id y actualizado las ventas correctamente";

            return $response;
        }catch(\Exception $e){
            $error_message =
                [
                    'error' => true,
                    'message' => "Ha ocurrido un error al intentar relanzar recibo_id $recibo_id.",
                ];
            \Log::warning('[Recibo a Favor 777 Cashout] Error - Exporting miniterminales cobranzas a favor to ondanet', ['result' => $e]);
            return $error_message;
        }

        

    }
}
