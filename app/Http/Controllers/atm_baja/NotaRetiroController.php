<?php

namespace App\Http\Controllers\atm_baja;

use Session;

use Carbon\Carbon;
use App\Models\Atm;
use App\Models\Group;
use App\Models\Atmnew;
use App\Models\NotaRetiro;
use Illuminate\Http\Request;
use App\Models\InactivateHistory;
use Barryvdh\DomPDF\Facade as PDF;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;

class NotaRetiroController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function index( $group_id, Request $request)
    {
        if (!$this->user->hasAccess('atms.group.retiro')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $grupo = Group::find($group_id);

        $atms = \DB::table('business_groups as bg')
        ->select('ps.atm_id as atm_id')
        ->join('branches as b','b.group_id','=','bg.id')
        ->join('points_of_sale as ps','ps.branch_id','=','b.id')
        ->join('atms as a','a.id', '=','ps.atm_id')
        ->where('bg.id',$group_id)
        ->whereNull('a.deleted_at')
        ->whereNull('bg.deleted_at')
        ->get();

        $atm_ids = array();
        foreach($atms as $item){
            $id = $item->atm_id;
            array_push($atm_ids, $id);
        }
        $atm_list       =  Atmnew::findMany($atm_ids);

        $retiros =  NotaRetiro::where('grupos_nota_retiro.group_id', $grupo->id)
        ->join('business_groups as bg','bg.id','=','grupos_nota_retiro.group_id')
        //->join('atms as a','a.id','=','grupos_nota_retiro.atm_id')
        ->select('grupos_nota_retiro.id as id','bg.description as nombre','grupos_nota_retiro.nombre_comercial','grupos_nota_retiro.fecha')
        ->get();
        //dd($retiros);

        return view('atm_baja.notas_retiros.index', compact('atmId','atm','grupo','retiros','atm_ids'));
    }

    public function create(Request $request)
    {
        if (!$this->user->hasAccess('atms.group.retiro.add')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $atm_ids     = $request->get('atm_list');
        $group_id   = $request->get('group_id');
        $grupo      = Group::find($group_id );
        $atm_list   = Atm::findMany($atm_ids);
        $notas =  NotaRetiro::where(['group_id'=> $group_id])->get();
        $numero  = $notas->count()+1;

        return view('atm_baja.notas_retiros.create',compact('atm_ids','group_id','grupo','atm_list','numero'));
    }

    public function store(Request $request)
    {
        if (!$this->user->hasAccess('atms.group.retiro.add')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();
        $input              = $request->all();
        $group_id           = $input['group_id'];
        $propietario        = $input['propietario'];
        $nombre_comercial   = $input['nombre_comercial'];
        $direccion          = $input['direccion'];
        $fecha              = $input['fecha'];
        $representante_legal= $input['representante_legal'];
        $ruc_representante  = $input['ruc_representante'];
        $referencia         = $input['referencia'];


        try{
            $nota = NotaRetiro::create([                    
                'group_id'          => $group_id,
                'propietario'       => $propietario,
                'nombre_comercial'  => $nombre_comercial,
                'fecha'             => Carbon::createFromFormat('d/m/Y', $fecha)->toDateString(),
                'representante_legal'=> $representante_legal,   
                'ruc_representante' => $ruc_representante,   
                'referencia'        => $referencia,   
                'direccion'         => $direccion,   
                'created_at'        => Carbon::now(),
                'created_by'        => $this->user->id,
                'updated_at'        => NULL,
                'updated_by'        => NULL,
                'deleted_at'        => NULL,
            ]);      

            //Auditoria
            $history = new InactivateHistory();
            $history->group_id    = $nota->group_id;
            $history->operation   = 'NOTA RETIRO - INSERT';
            $history->data        = json_encode($nota);
            $history->created_at  = Carbon::now();
            $history->created_by  = $this->user->id;
            $history->updated_at  = NULL;
            $history->updated_by  = NULL;
            $history->deleted_at  = NULL;
            $history->deleted_by  = NULL;
            $history->save();


            try{
                $propietario        = strtoupper($nota->propietario);
                $nombre_comercial   = strtoupper($nota->nombre_comercial);
                $direccion          = $nota->direccion;
                $referencia         = $nota->referencia;
                $representante_legal= strtoupper($nota->representante_legal);
                $ruc_representante  = $nota->ruc_representante;
                $dia                = date("d", strtotime($nota->fecha));
                $year               = date('Y',strtotime($nota->fecha));
                $month              = date('m',strtotime($nota->fecha));
                $meses              = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
                $mes                = $meses[date($month)-1];
                $fecha              = date('d/m/Y', strtotime($nota->fecha));
                $user_name          = 'LOGISTICAS';
                $grupo              = Group::find($nota->group_id);
                $ruc_cliente        = $grupo->ruc;
                $nombre_cliente     = $grupo->description;
                $identificador      = $nota->id;
                $user_email         = 'jgauto@eglobalt.com.py';
                $user_name          = 'NOTA DE RETIRO';

                $data = [];
                $data['user_email'] = $user_email;
                $data['user_name']  = $user_name;
                $data['body']       = 'Se genero una nota de retiro, favor descargar desde el CMS.';
                $data['ruc_cliente'] = $ruc_cliente;
                $data['nombre_cliente'] = $nombre_cliente;
                $data['identificador'] = $identificador;
                $data['fecha']      = $fecha;
             
                \Log::info("datos a enviar por correo");
                \Log::info($data);

                Mail::send('mails.nota_retiro_baja',$data,
                function($message) use($user_name, $user_email){
                    $message->to($user_email, $user_name)
                    ->cc('jgauto@eglobalt.com.py')
                    ->cc('lvillalba@eglobal.com.py')
                    ->subject('[BAJA] Alerta de BAJA Miniterminales ');
                });

            }catch(JWTException $exception){
                \Log::info("Error al enviar el mail");
                \Log::info($exception->getMessage());
    
            }


          

            \DB::commit();
            return redirect()->to('atm/new/'.$group_id.'/'.$group_id.'/notaretiro')->with('guardar','ok');
        }catch (\Exception $e){
            \DB::rollback();
            \Log::critical($e->getMessage());
            return redirect()->back()->with('error', 'ok');
        }
    }

    public function show($id)
    {
        $nota = NotaRetiro::find($id);
        $grupo = Group::find($nota->group_id);

        $ruc_cliente = $grupo->ruc;
        $nombre_cliente = $grupo->description;


        $propietario        = strtoupper($nota->propietario);
        $nombre_comercial   = strtoupper($nota->nombre_comercial);
        $direccion          = $nota->direccion;
        $referencia         = $nota->referencia;
        $representante_legal= strtoupper($nota->representante_legal);
        $ruc_representante  = $nota->ruc_representante;
        $fecha              = date('d/m/Y', strtotime($nota->fecha));
        $dia                = date("d", strtotime($nota->fecha));
        $year               = date('Y',strtotime($nota->fecha));
        $month              = date('m',strtotime($nota->fecha));
        $meses              = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
        $mes                = $meses[date($month)-1];
        $user_email = 'jgauto@eglobalt.com.py';
        $user_name = 'LOGISTICAS';
        $identificador    = $nota->id;

        $data = [];
        $data = [
            'fecha'                 => $fecha,
            'propietario'           => $propietario,
            'nombre_comercial'      => $nombre_comercial,
            'direccion'             => $direccion,
            'referencia'            => $referencia,
            'representante_legal'   => $representante_legal,
            'user_email'            => $user_email,
            'user_name'             => $user_name,
        ];
        \Log::info('Preview mail');
        \Log::info($data);

        return view('mails.nota_retiro_baja', compact('identificador','fecha','ruc_cliente','nombre_cliente','ruc_cliente','nombre_comercial','direccion','referencia','representante_legal','user_email','fecha','fecha','user_name'));

    }
   
    public function edit($id, Request $request)
    {
        if (!$this->user->hasAccess('atms.group.retiro.edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $input      = $request->all();
        //$atm_id     = $input['atm_id'];
        //$atm        = Atm::find($atm_id );

        if($nota = NotaRetiro::find($id))
        {
            $grupo = Group::find($nota->group_id);
            $atms = \DB::table('business_groups as bg')
            ->select('ps.atm_id as atm_id')
            ->join('branches as b','b.group_id','=','bg.id')
            ->join('points_of_sale as ps','ps.branch_id','=','b.id')
            ->join('atms as a','a.id', '=','ps.atm_id')
            ->where('bg.id',$nota->group_id)
            ->whereNull('a.deleted_at')
            ->whereNull('bg.deleted_at')
            ->get();

            $atm_ids = array();
            foreach($atms as $item){
                $id = $item->atm_id;
                array_push($atm_ids, $id);
            }
            $atm_list   =  Atmnew::findMany($atm_ids);

            $nota->fecha = date("d/m/Y", strtotime($nota->fecha));
            $data = [
                'nota'      => $nota,
                'atm_list'  => $atm_list,
                'grupo'     => $grupo
            ];

            return view('atm_baja.notas_retiros.edit', $data);
        }else{
            Session::flash('error_message', 'Nota de retiro no encontrada.');
            return redirect()->back();
        }

    }

    public function update(Request $request, $id)
    {
        if (!$this->user->hasAccess('atms.group.retiro.edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();
        $input = $request->all();
     
        if ($nota = NotaRetiro::find($id)){
            try{

                $nota->fill([
                    'fecha'               => Carbon::createFromFormat('d/m/Y', $input['fecha'])->toDateString(),
                    'propietario'         => $input['propietario'],
                    'nombre_comercial'    => $input['nombre_comercial'],
                    'direccion'           => $input['direccion'],
                    'referencia'          => $input['referencia'],
                    'representante_legal' => $input['representante_legal'],
                    'ruc_representante'   => $input['ruc_representante'],
                    'updated_at'          => Carbon::now(),
                    'updated_by'          => $this->user->id
                ])->save();
   
                //Auditoria
                $history = new InactivateHistory();
                $history->group_id    = $nota->group_id;
                $history->operation   = 'NOTA RETIRO - UPDATE';
                $history->data        = json_encode($nota);
                $history->created_at  = NULL;
                $history->created_by  = NULL;
                $history->updated_at  = Carbon::now();
                $history->updated_by  = $this->user->id;
                $history->deleted_at  = NULL;
                $history->deleted_by  = NULL;
                $history->save();

                \DB::commit();
                return redirect()->to('atm/new/'.$nota->group_id.'/'.$nota->group_id.'/notaretiro')->with('actualizar','ok');
                
            }catch (\Exception $e){
                \DB::rollback();
                \Log::error("Error updating retiro: " . $e->getMessage());
                return redirect()->to('atm/new/'.$nota->group_id.'/'.$nota->group_id.'/notaretiro')->with('error','ok');
            }
        }else{
            \Log::warning("Nota de retiro not found");
            return redirect()->to('atm/new/'.$nota->group_id.'/'.$nota->group_id.'/notaretiro')->with('error','ok');
        }

    }

    public function destroy($id)
    {
        if (!$this->user->hasAccess('atms.group.retiro.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();
        $message = '';
        $error = '';
        \Log::debug("Intentando elimiar nota_retiro_id ".$id);
        if ($nota = NotaRetiro::find($id)){

            try{
               
                if (NotaRetiro::where('id',$id)->delete()){
                    $message    =  'Nota de retiro eliminada correctamente';
                    $error      = false;
                }
                \Log::debug("Nota de retiro eliminada, nota_id ".$id);

                //Auditoria
                $history = new InactivateHistory();
                $history->group_id    = $nota->group_id;
                $history->operation   = 'NOTA RETIRO - DELETE';
                $history->data        = json_encode($nota);
                $history->created_at  = NULL;
                $history->created_by  = NULL;
                $history->updated_at  = NULL;
                $history->updated_by  = NULL;
                $history->deleted_at  = Carbon::now();
                $history->deleted_by  = $this->user->id;
                $history->save();

                \DB::commit();
            }catch (\Exception $e){
                \DB::rollback();
                \Log::error("Error deleting nota de retiro: " . $e->getMessage());
                $message    =  'Error al intentar eliminar la nota de retiro';
                $error      = true;
            }
        }else{
            $message    =  'Nota de retiro no encontrada';
            $error      = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }

    public function preview_pdf($id)
    {
        $nota = NotaRetiro::find($id);
        $propietario        = strtoupper($nota->propietario);
        $nombre_comercial   = strtoupper($nota->nombre_comercial);
        $direccion          = $nota->direccion;
        $referencia         = $nota->referencia;
        $representante_legal= strtoupper($nota->representante_legal);
        $ruc_representante  = $nota->ruc_representante;
        $dia                = date("d", strtotime($nota->fecha));
        $year               = date('Y',strtotime($nota->fecha));
        $month              = date('m',strtotime($nota->fecha));
        $meses              = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
        $mes                = $meses[date($month)-1];
        return view('atm_baja.notas_retiros.pdf', compact('mes','dia','year','nombre_comercial','referencia','direccion','representante_legal','ruc_representante','propietario'));
    }

    public function pdf($id)
    {
        $nota = NotaRetiro::find($id);
        $propietario        = strtoupper($nota->propietario);
        $nombre_comercial   = strtoupper($nota->nombre_comercial);
        $direccion          = $nota->direccion;
        $referencia         = $nota->referencia;
        $representante_legal= strtoupper($nota->representante_legal);
        $ruc_representante  = $nota->ruc_representante;
        $dia                = date("d", strtotime($nota->fecha));
        $year               = date('Y',strtotime($nota->fecha));
        $month              = date('m',strtotime($nota->fecha));
        $meses              = array("Enero","Febrero","Marzo","Abril","Mayo","Junio","Julio","Agosto","Septiembre","Octubre","Noviembre","Diciembre");
        $mes                = $meses[date($month)-1];
        //$atm = Atm::find($nota->atm_id);
        set_time_limit(300);
        $pdf = PDF::loadView('atm_baja.notas_retiros.pdf', compact('mes','dia','year','nombre_comercial','referencia','direccion','representante_legal','ruc_representante','propietario'));
        
        //para envio de correo
        //     $output = $pdf->output();

        //     $data = [
        //         'user_name'    => 'LOGISTICAS',
        //         'fecha'        => Carbon::now(), 
        //         'nroboleta'    => '001001001',
        //         'monto'        => 7777777,
        //         'boleta'       =>55555
        //     ];     
        //     \Log::info("datos a enviar por correo");
        //     \Log::info($data);

            

        //     try{
        //         Mail::send('mails.alert_cobranzas',$data,
        //         function($message)use($data,$pdf){
        //             $user_email = 'jgauto@eglobalt.com.py';
        //             $user_name  = 'Admin';
        //             $message->to($user_email, $user_name)
        //             ->cc('jorgegauto19@gmail.com.py')
        //             ->subject('[BAJA] Alerta de BAJA Miniterminales ')
        //             ->attachData($pdf->output(), "invoice.pdf");

        //     });
        //     }catch(JWTException $exception){
        //         $this->serverstatuscode = "0";
        //         $this->serverstatusdes = $exception->getMessage();
        //         \Log::info($exception->getMessage());

        //     }

        //     if (Mail::failures()) {
        //         \Log::info("Error al enviar el mail");
        //    }else{
        //         \Log::info("Mail enviado conrrectamente");
        //    }

        // Mail::send('mails.alert_cobranzas',$data,
        //     function($message){
        //         $user_email = 'jgauto@eglobalt.com.py';
        //         $user_name  = 'Admin';
        //         $message->to($user_email, $user_name)
        //         ->cc('jorgegauto19@gmail.com.py')
        //         ->subject('[BAJA] Alerta de BAJA Miniterminales ');
        // });

        return $pdf->download('Nota_retiro_'.$nombre_comercial.'.pdf');
    }

}
