<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use App\Http\Requests\OwnerRequest;
use App\Models\Ciudad;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class CiudadesController extends Controller
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
        if (!$this->user->hasAccess('ciudades')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $name = $request->get('name');
        $ciudades = Ciudad::filterAndPaginate($name);

        //$ciudads = Owner::paginate(10);
        return view('ciudades.index', compact('ciudades', 'name'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!$this->user->hasAccess('ciudades.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $departamentos = \DB::table('departamento')->orderBy('id','asc')->pluck('descripcion','id');

        return view('ciudades.create', compact('departamentos'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param OwnerRequest|Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
       
        $input = $request->all();


        if($request->ajax()){
            $respuesta = [];
            try{
                if ($ciudad = Ciudad::create($input)) {
                    \Log::info('Ciudad creada.', $ciudad->toArray());
                    $respuesta['mensaje'] = 'Ciudad creada exitosamente';
                    $respuesta['tipo'] = 'success';
                    $respuesta['data'] = $ciudad;
                    return $respuesta;
                }
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Error al crear la ciudad';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }
        }else{
            if (!$this->user->hasAccess('ciudades.add|edit')) {
                \Log::error('Unauthorized access attempt',
                    ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
                Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
                return redirect('/');
            }
            try{
                if ($ciudad =  Ciudad::create($input)){
                    \Log::info("New Ciudad on the house !");
                    Session::flash('message', 'Nueva ciudad creada correctamente');
                    return redirect('ciudades');
                }
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                Session::flash('error_message', 'Error al crear la ciudad');
                return redirect()->back()->with('error', 'Error al crear ciudad');
            }

        }


    //     if($request->ajax()){
    //         \Log::info("Ciudad Ajax");
    //         try{
    //             if ($ciudad =  Ciudad::create($input)){
    //                 \Log::info("New Ciudad on the house !");
    //                 Session::flash('message', 'Nueva ciudad creada correctamente');
    //                 return redirect('ciudades');
    //             }
    //         }catch (\Exception $e){
    //             \Log::critical($e->getMessage());
    //             Session::flash('error_message', 'Error al crear la ciudad');
    //             return redirect()->back()->with('error', 'Error al crear ciudad');
    //         }
    //     }else{
    //         try{
    //             if ($ciudad =  Ciudad::create($input)){
    //                 \Log::info("New Ciudad on the house !");
    //                 Session::flash('message', 'Nueva ciudad creada correctamente');
    //                 return redirect('ciudades');
    //             }
    //         }catch (\Exception $e){
    //             \Log::critical($e->getMessage());
    //             Session::flash('error_message', 'Error al crear la ciudad');
    //             return redirect()->back()->with('error', 'Error al crear ciudad');
    //         }
    //     }
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
        if (!$this->user->hasAccess('ciudades.add|edit')) {
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

    /**
     * Update the specified resource in storage.
     *
     * @param OwnerRequest|Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        if (!$this->user->hasAccess('ciudades.add|edit')) {
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!$this->user->hasAccess('ciudades.delete')) {
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
    
}
