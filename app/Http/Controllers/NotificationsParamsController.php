<?php

namespace App\Http\Controllers;

use App\Models\NotificationsParams;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use Carbon\Carbon;


use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class NotificationsParamsController extends Controller
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
        if (!$this->user->hasAccess('notifications_params')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $name = $request->get('name');
        $notifications_params = NotificationsParams::filterAndPaginate($name);

        return view('notifications_params.index', compact('notifications_params', 'name'));
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
        if (!$this->user->hasAccess('notifications_params.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if($notifications_params = \DB::table('notification_params')->where('id',$id)->first()){
            $service_sources = \DB::table('services_providers_sources')->orderBy('description','asc')->pluck('description','id');
            $tipo_notificacion = \DB::table('notification_types')->where('id', $notifications_params->notification_type)->first();

            $servicios_guardados = (array) json_decode($notifications_params->service_id);

            if($notifications_params->service_source_id <> 1){
                $servicios = \DB::table('servicios_x_marca')->where('service_source_id', $notifications_params->service_source_id)->pluck('descripcion', 'service_id');
            }else{
                $servicios = \DB::table('service_provider_products')->where('service_provider_id', $notifications_params->service_source_id)->pluck('description', 'id');
            }

            $data = [
                'notifications_params' => $notifications_params,
                'service_sources' => $service_sources,
                'tipo_notificacion' => $tipo_notificacion,
                'servicios' => $servicios,
                'servicios_guardados' => $servicios_guardados,
            ];

            return view('notifications_params.edit', $data);
        }else{
            Session::flash('error_message', 'Servicio Por Marca no encontrada');
            return redirect('servicios_marca');
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
        if (!$this->user->hasAccess('notifications_params.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if ($servicio_marca = NotificationsParams::where('id', $id)->first()){
            $input = $request->all();
            if(!empty($input['service_id'])){
                $input['service_id'] = json_encode($input['service_id']);
            }

            if(empty($input['service_source_id'])){
                unset($input['service_source_id']);
            }
            
            try{
                $input['updated_at'] = Carbon::now();

                unset($input['_token']);
                unset($input['_method']);
                if($servicio_marca->where('id', $id)->update($input)){
                    Session::flash('message', 'Configuracion de la alerta modificada exitosamente');
                    return redirect('notifications_params');
                }
            }catch (\Exception $e){
                \Log::error("Error updating network: " . $e->getMessage());
                Session::flash('error_message','Error al intentar actualizar la configuracion de la alerta');
                return redirect('notifications_params');
            }
        }else{
            \Log::warning("Notification Params not found");
            Session::flash('error_message', 'Configuración de la alerta no encontrada');
            return redirect('notifications_params');
        }

    }


    public function duplicate($id){
        try {
            $notification_param = NotificationsParams::find($id);

            $newParam = $notification_param->replicate();
            $newParam->created_at = date('Y-m-d h:i:s');
            $newParam->updated_at = date('Y-m-d h:i:s');
            $newParam->save();

            Session::flash('message', 'Configuracion de la alerta duplicada exitosamente');
            return redirect('notifications_params');
            
        } catch (Exception $e) {
            \Log::error("Error duplicating record: " . $e->getMessage());
            Session::flash('error_message','Error al intentar duplicar la configuracion de la alerta');
            return redirect('notifications_params');
        }
    }
    
}
