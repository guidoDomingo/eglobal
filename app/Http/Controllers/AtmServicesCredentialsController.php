<?php

namespace App\Http\Controllers;

use App\Models\Atm;
use App\Models\AtmServicesCredentials;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Session;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\WebService;
use App\Http\Requests\StoreAtmCredentialRequest;
use App\Http\Requests\UpdateAtmCredentialRequest;

class AtmServicesCredentialsController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($id)
    {
        if (!$this->user->hasAccess('atms')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }


        $credentials = \DB::table('atm_services_credentials')
            ->select('atm_services_credentials.*','name','atm_id','services_providers_sources.description as provider')
            ->where('atm_services_credentials.atm_id','=',$id)
            ->leftJoin('services','services.id','=','atm_services_credentials.service_id')
            ->leftJoin('services_providers_sources','services_providers_sources.id','=','atm_services_credentials.source_id')
            ->orderBy('atm_services_credentials')
            ->paginate(20);

        foreach ( $credentials as $credential) {
            if($credential->source_id <> 0){
                $credential->name = $credential->provider ;
            }
        }

        $data = [
            'credentials' => $credentials,
            'atm_id'      => $id
        ];

        return view('credentials.index', $data);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($id)
    {
        if (!$this->user->hasAccess('atms.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $webservices = WebService::all()->pluck('name', 'id');
        $data = ['webservices' => $webservices,
        'atm_id' => $id];
        return view('credentials.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreAtmCredentialRequest $request, $id)
    {
        if (!$this->user->hasAccess('atms.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');

        }

        try{
            $atm = Atm::find($id);
            $source_id = null;
            $service_id = $request['service_id'];
            //Netel
            if($request['service_id'] == 8){
                $source_id = 1;
                $service_id = null;
            }
            // proNET
            if($request['service_id'] == 25){
                $source_id = 4;
                $service_id = null;
            }
            // practipago
            if($request['service_id'] == 27){
                $source_id = 6;
            }
            // Netel TREX
            if($request['service_id'] == 67){
                $source_id = 10;
            }
            if($atm){
                $credentials = new AtmServicesCredentials;
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
                    $message = 'Agregado correctamente';
                    Session::flash('message', $message);
                    return redirect()->route('atm.credentials.index',$id);
                } else {
                    Session::flash('error_message', 'Error al guardar el registro');
                    return redirect()->route('atm.credentials.index',$id);
                }
            }
        }catch(\Exception $e){
            Session::flash('error_message', 'Error al guardar el registro');
            \Log::info($e);
            return redirect()->route('atm.credentials.index',$id);
        }




    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!$this->user->hasAccess('atms.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id, $credential_id)
    {
        if (!$this->user->hasAccess('atms.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $credentials = \DB::table('atm_services_credentials')
            ->select('atm_services_credentials.*','name','atm_id')
            ->where('atm_services_credentials.id','=',$credential_id)
            ->leftJoin('services','services.id','=','atm_services_credentials.service_id')
            ->first();
        //para Netel
        if($credentials->source_id == 1){
            $credentials->service_id = 8;
        }
        //para pronet
        if($credentials->source_id == 4){
            $credentials->service_id = 25;
        }

        //para practipago
        if($credentials->source_id == 6){
            $credentials->service_id = 27;
        }

        $webservices = WebService::all()->pluck('name', 'id');
        $atm = Atm::find($id);
        $data = [
            'webservices'   => $webservices,
            'credentials'   => $credentials,
            'atm'        => $atm
        ];

        return view('credentials.edit', $data);

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateAtmCredentialRequest $request, $id, $credentials_id)
    {
        if (!$this->user->hasAccess('atms.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

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

            // Netel TREX
            if($request['service_id'] == 67){
                $source_id = 10;
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, $credentials_id)
    {
        if (!$this->user->hasAccess('atms.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        
        $message = '';
        $error = '';
        if ($credentials = AtmServicesCredentials::find($credentials_id)) {
            try {
                if (AtmServicesCredentials::destroy($credentials_id)) {
                    $message = 'Credencial eliminada correctamente';
                    $error = false;
                }
            } catch (\Exception $e) {
                \Log::error("Error deleting atm credential: " . $e->getMessage());
                $message = 'Error al intentar eliminar credencial del cajero';
                $error = true;
            }
        }else{
            \Log::warning("Credential ".$credentials_id. " not found");
            $message =  'Credencial no encontrada';
            $error = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }
}
