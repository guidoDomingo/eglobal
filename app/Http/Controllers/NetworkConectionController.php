<?php

namespace App\Http\Controllers;

use Session;

use Carbon\Carbon;
use App\Models\Atm;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Models\NetworkConection;
use App\Http\Controllers\Controller;

class NetworkConectionController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }


    public function index(Request $request)
    {

    }


    public function create()
    {

    }


    public function store(Request $request)
    {
        $input = $request->all();
        \DB::beginTransaction();

        $networkConection = new NetworkConection();
        $networkConection->internet_service_contract_id         = $input['internet_service_contract_id'];
        $networkConection->description                          = $input['description'];
        $networkConection->network_technology_id                = $input['network_technology_id'];
        $networkConection->bandwidth                            = $input['bandwidth'];
        //$networkConection->installation_date                    = date("Y-m-d h:i:s", strtotime($input['installation_date']));
        $networkConection->installation_date                    = Carbon::createFromFormat('d/m/Y', $request->installation_date)->toDateString();
        //$housing_id                                             = $input['housing_id'];
        $atm_id                                                 = $input['atm_id'];
        //$branch_id                                               = $input['branch_id'];
        $networkConection->remote_access                        = $input['remote_access'];

        if($request->ajax()){
            $respuesta = [];
            try{
                if ($networkConection->save()){
                    $data = [];
                    $data['id'] = $networkConection->id;
                    \Log::info("Nuevo network connection creado");


                    /////////////Asignar contrato de internet a branch
                    \Log::info("Asociando el internet_service_contract_id=".$input['internet_service_contract_id']." al branch id=".$input['branch_id']);
                    try{
                        \DB::table('branches')
                            ->where('id',$input['branch_id'])
                            // ->update(['internet_service_contract_id' => $input['internet_service_contract_id']]);
                            ->update(['network_connection_id' => $networkConection->id]);

                       \Log::info("Conexion de internet #id".$networkConection->id." asignado al branch #id".$input['branch_id']." correctamente");

                    }catch (\Exception $e){
                        \Log::critical($e->getMessage());
                        $respuesta['mensaje'] = 'Error al asignar contrato de internet al branch';
                        $respuesta['tipo'] = 'error';
                    }



                    //PROGRESO DE CREACION - ABM V2
                    if ( $request->abm == 'v2'){
                        \DB::table('atms')
                        ->where('id', $input['atm_id'])
                        ->update([
                            'atm_status' => -11,
                        ]);
                        \Log::info("ABM Version 2, Paso 7 - NETWORK. Estado de creacion: -11");
                    };

                    $respuesta['mensaje'] = 'Conexión de red agregado correctamente.';
                    $respuesta['tipo'] = 'success';
                    $respuesta['data'] = $data;
                    $respuesta['url'] = route('netconections.update',[$networkConection->id]);


                     //////////////Actualizar housing
                     if(isset($request->housing_id)){

                        $housing_id  = $input['housing_id'];

                        //////////////asignar housing
                    //  try{
                            $housing_asignado=Atm::where('housing_id', $housing_id)->first();
                            if(empty($housing_asignado)){
                                $Atm = Atm::find($atm_id);
                                $Atm->housing_id = $housing_id;
                                $Atm->save();

                                \Log::info("Housing #".$housing_id." asignado al atm #".$atm_id." correctamente2");
                            }
                        // }catch (\Exception $e){
                        //         \Log::critical($e->getMessage());
                        //         $respuesta['mensaje'] = 'Error al asignar housing al Atm';
                        //         $respuesta['tipo'] = 'error';
                        //         \Log::info($respuesta);
                        //         // Session::flash('error_message', 'Ocurrio un error al intentar asignar el housing al ATM');
                        // }
                    }

                    \DB::commit();

                    return $respuesta;
                }
            }catch (\Exception $e){
                \DB::rollback();

                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Error al crear network connection';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }
        }else{
            if ($networkConection->save()) {
                $message = 'Agregado correctamente';
                Session::flash('message', $message);
                //create resources directory
                if (!$this->createDirectoryResources($networkConection->id)) {
                    Session::flash('message', "Directorio de Recursos no creado.");
                }
                return redirect()->route('netconections.index');
            } else {
                Session::flash('error_message', 'Error al guardar el registro');
                return redirect()->route('netconections.index');
            }
        }


    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        $networkConection = NetworkConection::find($id);

        if (!$networkConection) {
            \Log::warning("networkConection not found");
            return redirect()->back()->with('error', 'networkConection no valido');
        }

        $networkConection->internet_service_contract_id         = $request->internet_service_contract_id;
        $networkConection->description                          = $request->description;
        $networkConection->network_technology_id                = $request->network_technology_id;
        $networkConection->bandwidth                            = $request->bandwidth;
        $networkConection->installation_date                    = date("Y-m-d h:i:s", strtotime($request->installation_date));
        $atm_id                                                 = $request->atm_id;

        if($request->ajax()){
            $respuesta = [];
            try{
                $dataAnterior = NetworkConection::find($id);

                if ($networkConection->save()){
                    $data = [];
                    $data['id'] = $networkConection->id;

                    //    //PROGRESO DE CREACION - ABM V2
                    //    if ( $request->abm == 'v2'){
                    //     \DB::table('atms')
                    //     ->where('id', $request['atm_id'])
                    //     ->update([
                    //         'atm_status' => -11,
                    //     ]);
                    //     \Log::info("ABM Version 2, Paso 7 - NETWORK. Estado de actualizacion: -11");
                    // };

                    \Log::info("network connection actualizado correctamente");
                    $respuesta['mensaje'] = 'Conexión de red actualizado correctamente.';
                    $respuesta['tipo'] = 'success';
                    $respuesta['data'] = $data;
                    $respuesta['url'] = route('netconections.update',[$networkConection->id]);

                }
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Error al actualizar network connection';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }
        }else{
            if (!$networkConection->save()) {
                \Log::warning("Error updating the networkConection data. Id: {$networkConection->id}");
                Session::flash('message', 'Error al actualziar el registro');
                return redirect('netconections');
            }
            $message = 'Actualizado correctamente';
            Session::flash('message', $message);
            return redirect('netconections');
        }
    }

    public function destroy($id)
    {
        //
    }

}
