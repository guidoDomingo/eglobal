<?php

namespace App\Http\Controllers;

use DB;
use Excel;
use Session;

use HttpClient;
use Carbon\Carbon;
use App\Models\User;
use App\Http\Requests;
use App\Models\Device;
use App\Models\Housing;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Requests\DeviceRequest;
use App\Http\Requests\UpdateDeviceRequest;
use App\Models\Brand;
use App\Models\ModelBrand;

class DeviceController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function index($housingId, Request $request)
    {
        if (!$this->user->hasAccess('devices')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $tipo_housing = \DB::table('housing_type')
            ->select('id', 'description')
            ->get();
        $name = $request->get('name');
        $devices = Device::filterAndPaginate($housingId, $name);
        $modelos =  ModelBrand::pluck('id', 'description');

        return view('devices.index', compact('devices', 'name', 'housingId', 'tipo_housing', 'modelos'));
    }

    public function create($housingId)
    {  
        if (!$this->user->hasAccess('devices.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $users = User::all()->pluck('description','id');
        $users->prepend('Asignar usuario','0');
        $user_id = 0;
        $marcas =  Brand::pluck('description', 'id');
        $modelos =  ModelBrand::pluck('description', 'id');
        $housings = Housing::pluck('serialnumber', 'id');
        return view('devices.create', compact('housingId', 'housings', 'modelos', 'marcas'));
    }


    public function store($housingId, DeviceRequest $request)
    {
        if (!$this->user->hasAccess('devices.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $ahora = Carbon::now();
        $device = new Device;
        $device->fill($request->all());
        $device->fill(['housing_id' => $housingId]);

        if ($device->activo == 'TRUE') {
            $device->fill(['activated_at' => $ahora]);
        }

        if ($request->ajax()) {
            $respuesta = [];
            try {
                if ($device->save()) {
                    $data = [];
                    $data['id'] = $device->id;
                    $data['serialnumber'] = $device->serialnumber;
                    $data['descripcion'] = $device->descripcion;
                    $data['activo'] = $device->activo;

                    \Log::info("Nuevo dispositivo creado");
                    $respuesta['mensaje'] = 'Dispositivo creado correctamente';
                    $respuesta['tipo'] = 'success';
                    $respuesta['data'] = $data;
                    return $respuesta;

                } else {
                    \Log::critical($e->getMessage());
                    $respuesta['mensaje'] = 'Error al crear Dispositivo';
                    $respuesta['tipo'] = 'error';
                    return $respuesta;
                }
            } catch (\Exception $e) {
                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Error al crear Dispositivo';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }
            
        } else {
            try{
                if ($device->save()) {
                    $message = 'Agregado correctamente el dispositivo';
                    Session::flash('message', $message);
                    return redirect()->route('housing.device.index', $housingId);
                }
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                Session::flash('error_message', 'Error al crear el Housing');
                return redirect()->back()->with('error', 'Error al crear Housing');
            }
        }
    }

    public function show(Request $request)
    {
        $name = $request->get('name') ?? '';
   
        $tipo_housing = \DB::table('housing_type')
            ->select('id', 'description')
            ->get();

        $dispositivos = Device::orderBy('installation_date', 'desc')
            ->name($name)
            ->paginate(20);
        
       // dd($dispositivos);
        $devices = Device::orderBy('installation_date', 'desc')->paginate(20);

        return view('devices.show', compact('devices', 'name', 'tipo_housing', 'dispositivos'));
    }


    public function edit($id, $device)
    {  
        if (!$this->user->hasAccess('devices.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $device = Device::find($device);
        $housings = Housing::all()->pluck('serialnumber', 'id');
        $modelos = ModelBrand::all()->pluck('description', 'id');

        $data = [
            'device'       => $device,
            'housingId'    => $id,
            'housings'     => $housings,
            'modelos'       => $modelos
        ];
        return view('devices.edit', $data);
    }

    public function update(UpdateDeviceRequest $request, $id, $device)
    {  
        if (!$this->user->hasAccess('devices.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        } 

        $housingId = $id;
        $ahora = Carbon::now();

        if ($device = Device::find($device)) {
            $input = $request->all();
            try {
                $device->fill($input);
                if ($device->activo == 'FALSE') {
                    $device->fill(['activated_at' => NULL]);
                } elseif ($device->activo == 'TRUE') {
                    $device->fill(['activated_at' => $ahora]);
                }
                if ($device->update()) {
                    Session::flash('message', 'Dispositivo actualizado exitosamente');
                    $devices = Device::where('housing_id', $housingId)->paginate(10);
                    return view('devices.index', compact('devices', 'housingId'));
                }
            } catch (\Exception $e) {
                \Log::error("Error updating Device: " . $e->getMessage());
                Session::flash('error_message', 'Error al intentar actualizar el dispositivo');
                $devices = Device::orderBy('id', 'desc')->paginate(10);
                return view('devices.index', compact('devices', 'housingId'));
            }
        } else {
            \Log::warning("Device not found");
            Session::flash('error_message', 'Dispositivo no encontrado');
            $devices = Device::orderBy('id', 'desc')->paginate(20);
            return view('devices.index', compact('devices', 'housingId'));
        }
    }

    public function destroy($housingId, $device_id)
    {  
        if (!$this->user->hasAccess('devices.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $message = '';
        $error = '';
        \Log::debug("Attempting to delete a given Devices");
        if (Device::find($device_id)) {
            try {
                if (Device::destroy($device_id)) {
                    $message =  'Dispositivo eliminado correctamente';
                    $error = false;
                }
            } catch (\Exception $e) {
                \Log::error("Error deleting device: " . $e->getMessage());
                $message =  'Error al intentar eliminar el Dispositivo';
                $error = true;
            }
        } else {
            $message =  'Dispositivo no encontrado';
            $error = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }


    public function import($housingId)
    {
        if (!$this->user->hasAccess('devices.import')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $device = \DB::table('device')->get();
        return view('devices.import', compact('device', 'housingId'));
    }


    public function store_import(Request $request, $housingId)
    {
        if (!$this->user->hasAccess('devices.import')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        try {
            $this->validate($request, [
                'select_file'  => 'required|mimes:xls,xlsx'
            ]);
            $path = $request->file('select_file')->getRealPath();
            $devices = Excel::load($path)->get();
            if ($devices->count() > 0) {
                foreach ($devices as $device) {
                    $insert_data[] = array(
                        //'id'                  => $device->id,
                        'serialnumber'        => (string)$device->serialnumber,
                        'descripcion'         => '',
                        'installation_date'   => (string)date("Y-m-d h:i:s", strtotime($device->installation_date)),
                        'housing_id'          => $housingId,
                        'activo'              => 'True',
                        'activated_at'        => (string)Carbon::now(),
                        'model_id'            => (string)$device->model_id,
                    );
                }

                try{
                    foreach ($insert_data as $insert) {
                        if (!empty($insert)) {
                            //\Log::info($insert);
                            \DB::table('device')->insert($insert);
                        }
                    }
                    \Log::info("Fila Device ingresada!");
                    Session::flash('message', 'Nuevos dispositivos importados correctamente');
                    //return redirect('miniterminales');
                    return redirect()->route('housing.device.index', $housingId);

                }catch (\Exception $e){
                    \Log::critical($e->getMessage());
                    Session::flash('error_message', 'Seriales duplicados, Favor verificar.');
                    return redirect()->back()->with('error', 'Error general 2 al insertar el dispositivo');
                }
               
            }
        } catch (\Exception $e) {
            \Log::critical($e);
            Session::flash('error_message', 'Error al crear el dispositivo');
            return redirect()->back()->with('error', 'Error al crear el dispositivo');
        }
    }
}
