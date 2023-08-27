<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Requests\HousingRequest;
use App\Models\Device;
use App\Models\Housing;
use Session;
use Excel;
use DB;
use Carbon\Carbon;

use App\Models\Miniterminal;

class HousingController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function index(Request $request)
    {
        if (!$this->user->hasAccess('housing')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $housing_type =\DB::table('housing_type')->select('description')->get();
        $name = $request->get('name');
        $miniterminales = Housing::filterAndPaginate($name);
        return view('device_housing.index', compact('miniterminales','name','housing_type'));
    }
    
    public function create()
    {
        if (!$this->user->hasAccess('housing.add')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        return view('device_housing.create');
    }

    public function import()
    {
        $housing= \DB::table('housing')->get();
        return view('device_housing.import', compact('housing'));
    }

    public function store_import(Request $request)
    {
        try{
            $this->validate($request, [
                'select_file'  => 'required|mimes:xls,xlsx'
               ]);
    
            $path = $request->file('select_file')->getRealPath();
            $devices = Excel::load($path)->get();
            if($devices->count() > 0){
                foreach($devices as $device){
                        $insert_data[] = array(
                        //'id'                  => $device->id,
                        'serialnumber'        => (string)$device->serialnumber,
                        'housing_type_id'     => intval($device->housing_type_id),
                        'installation_date'   => (string)Carbon::now(),
                        );
                }
                foreach($insert_data as $insert){
                    if(!empty($insert)){

                        \DB::table('housing')->insert($insert);
                    }
                }
                \Log::info("Fila Housing ingresada!");
                Session::flash('message', 'Nuevas miniterminales creadas correctamente');
                return redirect('miniterminales');
            }
        }catch (\Exception $e){
            \Log::critical($e);
            Session::flash('error_message', 'Error al crear la miniterminal');
            return redirect()->back()->with('error', 'Error al crear la miniterminal');
        }
        
    }

    public function store(HousingRequest $request)
    { 
        if (!$this->user->hasAccess('housing.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        } 
        $input = $request->all();
        if($request->ajax()){
            $respuesta = [];
            try{
                if ($housing = Housing::create($input)){
                    \Log::info("New Housing on the house !");
                    $respuesta['mensaje'] = 'Nuevo Housing creado correctamente';
                    $respuesta['tipo'] = 'success';
                    $respuesta['data'] = $housing;
                    return $respuesta;
                }
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Error al crear el housing';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }
        }else{
            try{
                if ($housing = Housing::create($input)){
                    \Log::info("Housing ingresado correctamente!");
                    Session::flash('message', 'Nuevo Housing creado correctamente');
                    return redirect('miniterminales');
                }
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                Session::flash('error_message', 'Error al crear el Housing');
                return redirect()->back()->with('error', 'Error al crear Housing');
            }
        } 

    }
 
    public function edit($id)
    {   
        if (!$this->user->hasAccess('housing.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }  

        if($housing = Housing::find($id)){
            $data = ['housing' => $housing];
            return view('device_housing.edit', $data);
        }else{
            Session::flash('error_message', 'Red no encontrada');
            return redirect('miniterminales');
        }

    }

    public function update(HousingRequest $request, $id)
    {   
        if (!$this->user->hasAccess('housing.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if ($housing = Housing::find($id)){
            $input = $request->all();
            try{
                $housing->fill($input);
                // $housing->fill(['updated_by' => $this->user->id]);
                if($housing->update()){
                    \Log::info("Housing actualizada exitosamente");

                    Session::flash('message', 'Housing actualizada exitosamente');
                    return redirect('miniterminales');
                }
            }catch (\Exception $e){
                \Log::error("Error updating network: " . $e->getMessage());
                Session::flash('error_message','Error al intentar actualizar el Housing');
                return redirect('miniterminales');
            }
        }else{
            \Log::warning("Housing not found");
            Session::flash('error_message', 'Housing no encontrada');
            return redirect('miniterminales');
        }
    }

    public function destroy($id)
    {  
        if (!$this->user->hasAccess('housing.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        } 
        $message = '';
        $error = '';
        \Log::debug("Attempting to delete a given housing");
        if (Housing::find($id)){
            $activeDevices = Device::where('housing_id', $id)->get();
            if (count($activeDevices) == 0){
                try{
                    if (Housing::destroy($id)){
                        $message =  'Housing eliminado correctamente';
                        $error = false;
                    }
                }catch (\Exception $e){
                    \Log::error("Error deleting Housing: " . $e->getMessage());
                    $message =  'Error al intentar eliminar el Housing';
                    $error = true;
                }
            }else{
                \Log::warning("Housing {$id} have still active devices");
                $message =  'Este Housing aun cuenta con devices activos';
                $error = true;
            }
        }else{
            $message =  'Housing no encontrada';
            $error = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }

   
}