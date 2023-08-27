<?php

namespace App\Http\Controllers;

use Session;
use Carbon\Carbon;
use App\Models\Atm;
use App\Http\Requests;
use App\Models\WebService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\AtmServicesCredentials;
use App\Http\Requests\StoreAtmCredentialRequest;
use App\Http\Requests\UpdateAtmCredentialRequest;
use App\Models\AtmCredentialsOndanet;

class AtmServicesCredentialsNewController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

  
    public function index($id)
    {
       

    }

   
    public function create($id)
    {
        
    }

    
    public function store(Request $request)
    {
        // if (!$this->user->hasAccess('atms_v2_fraude.add|edit')) {
        //     \Log::error('Unauthorized access attempt',
        //         ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
        //     Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
        //     return redirect('/');

        // }
    
        $input = $request->all();

        $credentials = new AtmServicesCredentials;
        
        $credentials->atm_id =$input['atm_id'];
        $credentials->service_id = 6;
        $credentials->user = $request['user'];
        $credentials->password  = $request['password'];

        if($request->ajax()){
            $respuesta = [];
            try{
                if ($credentials->save()) {
                    $data = [];
                    $data['id'] = $credentials->id;
                    \Log::info("Nueva credencial asignada, service_id = 6");

                    \DB::table('atm_services_credentials')->insert([
                        ['atm_id' => $request->atm_id, 'service_id' => 9, 'user' => $request->user, 'password' => NULL, 'created_at' => Carbon::now()]
                    ]);
                    \Log::info("Nueva credencial asignada, service_id = 9");

                     //PROGRESO DE CREACION - ABM V2
                    if ( $request->abm == 'v2'){
                        \DB::table('atms')
                        ->where('id', $input['atm_id'])
                        ->update([
                            'atm_status' => -9,
                        ]);
                        \Log::info("ABM Version 2, Paso 5 - FRAUDE ANTELL. Estado de creacion: -10");
                    }

                    $respuesta['mensaje'] = 'Credenciales asignadas correctamente.';
                    $respuesta['tipo'] = 'success';
                    $respuesta['data'] = $data;
                    $respuesta['url'] = route('atmnew.credentials.update',[$credentials->id]);
                    return $respuesta;
                } 

            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Error al crear las credenciales.';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }
        }else{
            if($credentials->save()){
                $message = 'Credenciales asignadas correctamente';
                Session::flash('message', $message);
                \Log::info("Nueva credenciales creadas");

                return redirect('atmnew.credentials.index');  
            }else{
                \DB::rollback();
                Session::flash('error_message', 'Ocurrio un error al intentar guardar el registro');
                return redirect()->back()->withInput();
                \Log::info('This is some useful information.');
            }

        }




    }

  
    public function show($id)
    {
        
    }

   
    public function edit(Request $request, $id, $credential_id)
    {
       

    }

    
    public function update(UpdateAtmCredentialRequest $request, $id, $credentials_id)
    {
        // if (!$this->user->hasAccess('atms_v2_fraude.add|edit')) {
        //     \Log::error('Unauthorized access attempt',
        //         ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
        //     Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
        //     return redirect('/');
        // }

        try{
            $credentials = AtmServicesCredentials::find($credentials_id);
            $atm = Atm::find($id);
            $source_id = null;
            $service_id = $request['service_id'];
            //Netel
            if($request['service_id'] == 8){
                $source_id = 1;
                $service_id = null;
            }
            //proNET
            if($request['service_id'] == 25){
                $source_id = 4;
                $service_id = null;
            }
            // practipago
            if($request['service_id'] == 27){
                $source_id = 6;
            }
            
            if (!$credentials) {
                \Log::warning("Credenciales not found");
                return redirect()->back()->with('error', 'Credenciales no validas');
            }

            $credentials->atm_id = $atm->id;
            $credentials->service_id = $service_id;
            $credentials->cnb_service_code = $request['cnb_service_code'];
            $credentials->source_id = $source_id;
            $credentials->user = $request['user'];
            $credentials->password  = $request['password'];

            //para pronet
            $credentials->codEntity     = ((isset($request['codEntity']) && $request['codEntity']<> '')?$request['codEntity']:null);
            $credentials->codBranch     = ((isset($request['codBranch']) && $request['codBranch']<> '')?$request['codBranch']:null);
            $credentials->codTerminal   = ((isset($request['codTerminal']) && $request['codTerminal']<> '')?$request['codTerminal']:null);

            if ($credentials->save()) {
                $message = 'Actualizado correctamente';
                Session::flash('message', $message);
                return redirect()->route('atm.credentials.index',$atm->id);
            } else {
                Session::flash('error_message', 'Error al actualizar el registro');
                return redirect()->route('atm.credentials.index',$atm->id);
            }



        }catch (\Exception $e){
            \Log::warning("Credenciales not found - ".$e  );
            return redirect()->back()->with('error', 'Credenciales no validas');
        }


    }

    
    public function destroy($id, $credentials_id)
    {
       
    }
 
    public function store_ondanet(Request $request)
    {
        // if (!$this->user->hasAccess('credenciales_ondanet.add|edit')) {
        //     \Log::error('Unauthorized access attempt',
        //         ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
        //     Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
        //     return redirect('/');

        // }
        \DB::beginTransaction();

        $input                      = $request->all();
        \Log::info($input);
        $credentials                            = new AtmCredentialsOndanet();
        $credentials->atm_id                    = $input['atm_id'];
        $credentials->vendedor                  = $input['vendedor'];
        $credentials->vendedor_cash             = $input['vendedor_cash'];
        $credentials->vendedor_descripcion      = $input['vendedor_descripcion'];
        $credentials->vendedor_descripcion_cash = $input['vendedor_descripcion_cash'];
        $credentials->caja                      = $input['caja'];
        $credentials->caja_cash                 = $input['caja_cash'];
        $credentials->sucursal                  = $input['sucursal'];
        $credentials->sucursal_cash             = $input['sucursal_cash'];
        $credentials->deposito                  = $input['deposito'];
        $credentials->deposito_cash             = $input['deposito_cash'];
        $credentials->created_at                = Carbon::now();
        $credentials->updated_at                = Carbon::now();

        if($request->ajax()){
            $respuesta = [];
            try{
                if ($credentials->save()) {
                    $data = [];
                    $data['id'] = $credentials->id;
                    \Log::info("Nueva credencial de ondanet asignada");

                
                    \Log::info("Nueva credencial  de ondanet asignada");

                     //PROGRESO DE CREACION - ABM V2
                    if ( $request->abm == 'v2'){
                        \DB::table('atms')
                        ->where('id', $input['atm_id'])
                        ->update([
                            'atm_status' => -14,
                            'seller_code' => $input['vendedor'],
                            'deposit_code' => $input['deposito'],
                        ]);
                        \Log::info("ABM Version 2, Paso 5 - SISTEMAS ANTELL. Estado de creacion: -14");
                    }
                    \DB::commit();
                    $respuesta['mensaje']   = 'Credenciales de ondanet asignadas correctamente.';
                    $respuesta['tipo']      = 'success';
                    $respuesta['data']      = $data;
                    $respuesta['url']       = route('atmnew.credentials.update',[$credentials->id]);

                    return $respuesta;
                } 

            }catch (\Exception $e){
                \DB::rollback();
                \Log::critical($e->getMessage());
                $respuesta['mensaje']   = 'Error al crear las credenciales de ondanet.';
                $respuesta['tipo']      = 'error';

                return $respuesta;
            }
        }else{
            if($credentials->save()){
                $message = 'Credenciales de ondanet asignadas correctamente';
                Session::flash('message', $message);
                \Log::info("Nueva credenciales de ondanet creadas");
                \DB::commit();

                return redirect('atmnew.credentials.index');  

            }else{
                \DB::rollback();
                Session::flash('error_message', 'Ocurrio un error al intentar guardar el registro');
                return redirect()->back()->withInput();
                \Log::info('This is some useful information.');
            }

        }





    }

    public function update_ondanet(Request $request, $id, $credentials_id)
    {
        // if (!$this->user->hasAccess('credenciales_ondanet.add|edit')) {
        //     \Log::error('Unauthorized access attempt',
        //         ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
        //     Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
        //     return redirect('/');
        // }

        try{
            $credentials    = AtmCredentialsOndanet::find($credentials_id);
            $atm            = Atm::find($id);
            $source_id      = null;
                  
            
            if (!$credentials) {
                \Log::warning("Credenciales ondanet not found");
                return redirect()->back()->with('error', 'Credenciales de ondanet no validas');
            }

            $credentials->atm_id                    = $atm->id;
            $credentials->vendedor                  = $request['vendedor'];
            $credentials->vendedor_cash             = $request['vendedor_cash'];
            $credentials->vendedor_descripcion      = $request['vendedor_descripcion'];
            $credentials->vendedor_descripcion_cash = $request['vendedor_descripcion_cash'];
            $credentials->caja                      = $request['caja'];
            $credentials->caja_cash                 = $request['caja_cash'];
            $credentials->sucursal                  = $request['sucursal'];
            $credentials->sucursal_cash             = $request['sucursal_cash'];
            $credentials->deposito                  = $request['deposito'];
            $credentials->deposito_cash             = $request['deposito_cash'];
            $credentials->created_at                = null;
            $credentials->updated_at                = Carbon::now();

            if ($credentials->save()) {
                $message = 'Actualizado correctamente';
                Session::flash('message', $message);
                return redirect()->route('atmnew.credentials.update',[$credentials->id]);

            } else {
                Session::flash('error_message', 'Error al actualizar el registro');
                return redirect()->route('atmnew.credentials.update',[$credentials->id]);
            }

        }catch (\Exception $e){
            \Log::warning("Credenciales ondanet not found - ".$e  );
            return redirect()->back()->with('error', 'Credenciales ondanet no validas');
        }


    }



}
