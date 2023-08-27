<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Http\Requests\OwnerRequest;
use App\Models\ParametroComision;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;

class ParametrosComisionesController extends Controller
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
    public function index(Request $request)
    {
        if (!$this->user->hasAccess('parametros_comisiones')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $name = $request->get('name');
        $parametros_comisiones = ParametroComision::filterAndPaginate($name);
        return view('parametros_comisiones.index', compact('parametros_comisiones', 'name'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!$this->user->hasAccess('parametros_comisiones.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $owners['todos'] = 'Todos';
        $owners += \DB::table('owners')->where('deleted_at', null)->pluck('name', 'id')->toArray();
        $atms['todos'] = 'Todos';
        $atms += \DB::table('atms')->where('deleted_at', null)->pluck('name', 'id')->toArray();
        $service_sources = \DB::table('services_providers_sources')->orderBy('description','asc')->pluck('description','id');

        $tipo_servicio = [
            0 => 'Bocas de Cobranzas',
            1 => 'Integración Directa'
        ];

        return view('parametros_comisiones.create', compact('atms', 'service_sources', 'owners', 'tipo_servicio'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param OwnerRequest|Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!$this->user->hasAccess('parametros_comisiones.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }


        $input = $request->all();

        try{
            $input['service_source_id'] = ($input['tipo_servicio_id'] == 1) ? null : $input['service_source_id'];

            if(empty($input['owner_id'])){
                if ($parametro_comision =  ParametroComision::create($input)){
                    \Log::info("New ParametroComision on the house !");
                    Session::flash('message', 'Nuevo parametro de comision creado correctamente');
                    return redirect('parametros_comisiones');
                }
            }else{
                if($input['owner_id'] == 'todos'){
                    $owners = \DB::table('owners')->where('deleted_at', null)->pluck('id', 'id');
                    $input['owner_id'] = $owners;
                }else{
                    $input['owner_id'] = [$input['owner_id']];
                }

                $atms_con_comision = \DB::table('parametros_comisiones')
                    ->select('atm_id')
                    ->groupBy('atm_id')
                    ->where('service_id', $input['service_id'])
                    ->where('service_source_id', $input['service_source_id'])
                    ->whereNull('deleted_at')
                    ->pluck('atm_id','atm_id');

                $atms = \DB::table('atms')
                    ->whereIn('owner_id', $input['owner_id'])
                    ->whereNull('deleted_at')
                    ->where(function($query) use($atms_con_comision, $input){
                        if(!empty($atms_con_comision)){
                            $query->whereNotIn('id', $atms_con_comision);
                        }

                        if(!empty($input['atm_id']) && $input['atm_id'] <> 'todos'){
                            $query->where('id', $input['atm_id']);
                        }
                    })
                    ->pluck('id','id');

                $data = [];
                foreach ($atms as $key => $atm_id) {
                    $data[] = [
                        'atm_id' => $atm_id,
                        'comision' => $input['comision'],
                        'tipo_servicio_id' => $input['tipo_servicio_id'],
                        'service_source_id' => $input['service_source_id'],
                        'service_id' => $input['service_id'],
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                        'created_by' => $this->user->id
                    ];
                }

                if ($parametro_comision =  ParametroComision::insert($data)){
                    \Log::info("New ParametroComision on the house !");
                    Session::flash('message', 'Nuevo parametro de comision creado correctamente');
                    return redirect('parametros_comisiones');
                }
            }
        }catch (\Exception $e){
            \Log::critical($e->getMessage());
            Session::flash('error_message', 'Error al crear el parametro de comision');
            return redirect()->back()->with('error', 'Error al crear el parametro de comision');
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!$this->user->hasAccess('parametros_comisiones.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if($parametro_comision = ParametroComision::find($id)){
            $owners['todos'] = 'Todos';
            $owners += \DB::table('owners')->where('deleted_at', null)->pluck('name', 'id')->toArray();
            $atms['todos'] = 'Todos';
            $atms += \DB::table('atms')->where('deleted_at', null)->pluck('name', 'id')->toArray();
            $service_sources = \DB::table('services_providers_sources')->orderBy('description','asc')->pluck('description','id');
            $servicios = \DB::table('services_ondanet_pairing')
                ->where('service_source_id', $parametro_comision->service_source_id)
                ->pluck('service_description','service_request_id');

            $tipo_servicio = [
                0 => 'Bocas de Cobranzas',
                1 => 'Integración Directa'
            ];
            $servicios_propios = \DB::table('service_provider_products')
                ->select(\DB::raw("concat(service_providers.name, ' - ' ,service_provider_products.description) as servicio"), 'service_provider_products.id')
                ->whereNull('service_provider_products.deleted_at')
                ->join('service_providers', 'service_provider_products.service_provider_id', '=', 'service_providers.id')
                ->pluck('servicio', 'service_provider_products.id');

            $data = [
                'parametro_comision' => $parametro_comision,
                'atms' => $atms,
                'service_sources' => $service_sources,
                'servicios' => $servicios,
                'servicios_propios' => $servicios_propios,
                'owners' => $owners,
                'tipo_servicio' => $tipo_servicio,
            ];
            return view('parametros_comisiones.edit', $data);
        }else{
            Session::flash('error_message', 'Parametro de Comision no encontrado');
            return redirect('parametros_comisiones');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param OwnerRequest|Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!$this->user->hasAccess('parametros_comisiones.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = $request->all();
        $input['service_source_id'] = ($input['tipo_servicio_id'] == 1) ? null : $input['service_source_id'];

        if(!empty($input['owner_id'])){
            if($input['owner_id'] == 'todos'){
                $owners = \DB::table('owners')->where('deleted_at', null)->pluck('id', 'id');
                $input['owner_id'] = $owners;
            }else{
                $input['owner_id'] = [$input['owner_id']];
            }

            $atms = \DB::table('atms')
                ->whereIn('owner_id', $input['owner_id'])
                ->whereNull('deleted_at')
                ->where(function($query) use($input){
                    if(!empty($input['atm_id']) && $input['atm_id'] <> 'todos'){
                        $query->where('id', $input['atm_id']);
                    }
                })
                ->pluck('id','id');

            if(!empty($atms)){
                $actualizar = \DB::table('parametros_comisiones')
                    ->whereIn('atm_id', $atms)
                    ->where('service_id', $input['service_id'])
                    ->where('service_source_id', $input['service_source_id'])
                    ->update([
                        'service_id' => $input['service_id'],
                        'service_source_id' => $input['service_source_id'],
                        'comision' => $input['comision'],
                        'tipo_servicio_id' => $input['tipo_servicio_id'],
                        'updated_by' => $this->user->id,
                    ]);

                if($actualizar){
                    Session::flash('message', 'Parametro de comision actualizado exitosamente');
                    return redirect('parametros_comisiones');
                }
            }

            \Log::warning("ParametroComision not found");
            Session::flash('error_message', 'Parametro de Comision no encontrado');
            return redirect('parametros_comisiones');
        }else{
            if ($parametro_comision = ParametroComision::find($id)){
                try{
                    $parametro_comision->fill($input);
                    if($parametro_comision->update()){
                        Session::flash('message', 'Parametro de comision actualizado exitosamente');
                        return redirect('parametros_comisiones');
                    }
                }catch (\Exception $e){
                    \Log::error("Error updating network: " . $e->getMessage());
                    Session::flash('error_message','Error al intentar actualizar el parametro comision');
                    return redirect('parametros_comisiones');
                }
            }else{
                \Log::warning("ParametroComision not found");
                Session::flash('error_message', 'Parametro de Comision no encontrado');
                return redirect('parametros_comisiones');
            }
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!$this->user->hasAccess('parametros_comisiones.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $message = '';
        $error = '';
        \Log::debug("Intentando elimiar parametro de comision ".$id);
        if ($parametro_comision = ParametroComision::find($id)){
            try{
                if(ParametroComision::where('id',$id)->delete()){
                    $message =  'Parametro Comision eliminado correctamente';
                    $error = false;
                }
            }catch (\Exception $e){
                \Log::error("Error deleting network: " . $e->getMessage());
                $message =  'Error al intentar eliminar la marca';
                $error = true;
            }
        }else{
            $message =  'Parametro de Comision no encontrada';
            $error = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }

    /*
    *
    * Get services json
    */
    public function getServices(Request $request){
        if($request->ajax()){
            $services = \DB::table('services_ondanet_pairing')
                ->where('service_source_id', $request->get('service_source_id'))
                ->pluck('service_description','service_request_id');

            $data = [];
            foreach ($services as $serviceId => $texto) {
                $valor = [];
                $valor['id'] = $serviceId;
                $valor['text'] = $texto;
                $data[] = $valor; 
            }

            return $data;
        }
    }

    /*
    *
    * Get services products json
    */
    public function getServicesProducts(Request $request){
        if($request->ajax()){
            $services = \DB::table('service_provider_products')
                ->select(\DB::raw("concat(service_providers.name, ' - ' ,service_provider_products.description) as servicio"), 'service_provider_products.id')
                ->whereNull('service_provider_products.deleted_at')
                ->join('service_providers', 'service_provider_products.service_provider_id', '=', 'service_providers.id')
                ->pluck('servicio', 'service_provider_products.id');

            $data = [];
            foreach ($services as $serviceId => $texto) {
                $valor = [];
                $valor['id'] = $serviceId;
                $valor['text'] = $texto;
                $data[] = $valor; 
            }

            return $data;
        }
    }

    /*
    *
    * Get atms json
    */
    public function getAtms(Request $request){
        if($request->ajax()){
            $atms = \DB::table('atms')
                ->where(function($query) use($request){
                    if(!empty($request->get('owner_id')) && $request->get('owner_id') != 'todos'){
                        $query->where('owner_id', $request->get('owner_id'));
                    }
                })
                ->whereNull('deleted_at')
                ->pluck('name','id');
            $data = [];

            if(empty($request->get('owner_id'))){
                $data += [
                    [
                        'id' => 'todos',
                        'text' => 'Todos'
                    ]
                ];
            }

            $data += [
                [
                    'id' => 'todos',
                    'text' => 'Todos'
                ]
            ];
            foreach ($atms as $atmId => $texto) {
                $valor = [];
                $valor['id'] = $atmId;
                $valor['text'] = $texto;
                $data[] = $valor; 
            }

            return $data;
        }
    }
}
