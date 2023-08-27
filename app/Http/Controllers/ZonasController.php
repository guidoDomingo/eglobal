<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Http\Requests\OwnerRequest;
use App\Models\Zona;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class ZonasController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    
    public function index()
    {
        if (!$this->user->hasAccess('zonas')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $zonas = Zona::all();

        //$ciudads = Owner::paginate(10);
        return view('zonas.index', compact('zonas'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!$this->user->hasAccess('zonas.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $departamentos = \DB::table('departamento')->orderBy('id','asc')->pluck('descripcion','id');

        return view('zonas.create', compact('departamentos'));
    }


    public function store(Request $request)
    {
        
        $input = $request->all();
                         

        if($request->ajax()){
            $respuesta = []; 

            try{

                if ($zona =  Zona::create($input)){
                    \Log::info('Zona creada.', $zona->toArray());
                    $respuesta['mensaje'] = 'Zona creada exitosamente';
                    $respuesta['tipo'] = 'success';
                    $respuesta['data'] = $zona;
 
                    return $respuesta;
                }
            }catch (\Exception $e){

                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Error al crear la zona';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }
        }else{
            if (!$this->user->hasAccess('zonas.add|edit')) {
                \Log::error('Unauthorized access attempt',
                    ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
                Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
                return redirect('/');
            }
            try{
                if ($zona =  Zona::create($input)){                                        
                    \Log::info('Zona creada.');
                    Session::flash('message', 'Nueva zona creada correctamente');
                   
                
                    return redirect('zonas');

                   
                }
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                Session::flash('error_message', 'Error al crear la zona');
                return redirect()->back()->with('error', 'Error al crear zona');
            }
        }
    }

   
    public function show($id)
    {
        //
    }

   
    public function edit($id)
    {
        if (!$this->user->hasAccess('zonas.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if($ciudad = Ciudad::find($id)){
            $departamentos = \DB::table('departamento')->orderBy('id','asc')->pluck('descripcion','id');
            $data = [
                'ciudad' => $ciudad,
                'departamentos' => $departamentos,
            ];
            return view('ciudades.edit', $data);
        }else{
            Session::flash('error_message', 'Ciudad no encontrada');
            return redirect('ciudades');
        }

    }

 
    public function update(Request $request, $id)
    {
        if (!$this->user->hasAccess('zonas.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if ($ciudad = Ciudad::find($id)){
            $input = $request->all();
            try{

                $ciudad->fill($input);
                if($ciudad->update()){
                    Session::flash('message', 'Ciudad actualizada exitosamente');
                    return redirect('ciudades');
                }
            }catch (\Exception $e){
                \Log::error("Error updating network: " . $e->getMessage());
                Session::flash('error_message','Error al intentar actualizar la ciudad');
                return redirect('ciudades');
            }
        }else{
            \Log::warning("Ciudad not found");
            Session::flash('error_message', 'Ciudad no encontrada');
            return redirect('ciudades');
        }

    }

   
    public function destroy($id)
    {
        if (!$this->user->hasAccess('zonas.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $message = '';
        $error = '';
        \Log::debug("Intentando elimiar ciudad ".$id);
        if ($ciudad = Ciudad::find($id)){
            try{
                $barrios = \DB::table('barrios')->where('ciudad_id',$id)->count();

                if($barrios <= 0){
                    if(Ciudad::where('id',$id)->delete()){
                        $message =  'Ciudad eliminada correctamente';
                        $error = false;
                    }
                }else{
                    $message = 'El registro tiene barrios asociadas al mismo.';
                    $error = true;
                }
            }catch (\Exception $e){
                \Log::error("Error deleting network: " . $e->getMessage());
                $message =  'Error al intentar eliminar la ciudad';
                $error = true;
            }
        }else{
            $message =  'Ciudad no encontrada';
            $error = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }


    public function asociar(Request $request)
    {
        
        if($request->ajax()){
            $respuesta = []; 
            try{
                \DB::table('ciudad_zona')->insert(
                        ['ciudades_id' => $request->ciudad_id, 
                            'zona_id' =>  $request->zona_id]);
                \Log::info('Zona id: '.$request->zona_id.' asignada a ciudad id: '.$request->ciudad_id);
                $zona = \DB::table('ciudad_zona')
                            ->join('zona', 'zona.id', '=', 'ciudad_zona.zona_id')
                            ->select('zona.id', 'zona.descripcion')
                            ->where('ciudad_zona.ciudades_id', $request->ciudad_id)
                            ->where('ciudad_zona.zona_id',  $request->zona_id)
                            ->get();
                            \Log::info($zona);



                $respuesta['mensaje'] = 'Zona-Ciudad asignada exitosamente';
                $respuesta['tipo'] = 'success';
                $respuesta['data'] = $zona;

                return $respuesta;
            }catch (\Exception $e){

                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Error al Asociar la zona con la ciudad';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }
        }else{
            try{
                \DB::table('ciudad_zona')->insert(
                    ['ciudades_id' => $request->ciudad_id, 
                        'zona_id' =>  $request->zona_id]);
                \Log::info('Zona id: '.$request->zona_id.' asignada a ciudad id: '.$request->ciudad_id);
                 
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                Session::flash('error_message', 'Error al Asociar la zona con la ciudad');
                return redirect()->back()->with('error', 'Error al Asociar la zona con la ciudad');
            }
        }

    }
    
}
