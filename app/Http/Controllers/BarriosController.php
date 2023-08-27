<?php

namespace App\Http\Controllers;

use App\Models\Ciudad;
use App\Http\Requests\OwnerRequest;
use App\Models\Barrio;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class BarriosController extends Controller
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
        if (!$this->user->hasAccess('barrios')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $name = $request->get('name');
        $barrios = Barrio::filterAndPaginate($name);

        //$ciudads = Owner::paginate(10);
        return view('barrios.index', compact('barrios', 'name'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!$this->user->hasAccess('barrios.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $ciudades = \DB::table('ciudades')->orderBy('id','asc')->pluck('descripcion','id');

        return view('barrios.create', compact('ciudades'));
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
                if ($barrio = Barrio::create($input)) {
                    \Log::info('Barrio creado.', $barrio->toArray());
                    $respuesta['mensaje'] = 'Barrio creado exitosamente';
                    $respuesta['tipo'] = 'success';
                    $respuesta['data'] = $barrio;
                    return $respuesta;
                }
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Error al crear el barrio';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }
        }else{
            if (!$this->user->hasAccess('barrios.add|edit')) {
                \Log::error('Unauthorized access attempt',
                    ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
                Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
                return redirect('/');
            }
            try{
                if ($barrio =  Barrio::create($input)){
                    \Log::info("New BARRIO on the house !");
                    Session::flash('message', 'Nuevo barrio creada correctamente');
                    return redirect('barrios');
                }
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                Session::flash('error_message', 'Error al crear el barrio');
                return redirect()->back()->with('error', 'Error al crear el barrio');
            }

        }


        // if($request->ajax()){
        //     \Log::info("barrios ajax");
        //     try{
        //         if ($barrio =  Barrio::create($input)){
        //             \Log::info("New Barrio on the house !");
        //             Session::flash('message', 'Nuevo barrio creada correctamente');
        //             return redirect('barrios');
        //         }
        //     }catch (\Exception $e){
        //         \Log::critical($e->getMessage());
        //         Session::flash('error_message', 'Error al crear el barrio');
        //         return redirect()->back()->with('error', 'Error al crear barrio');
        //     }
        //     /*$respuesta = [];
        //     try{
        //         if ($owner = Owner::create($input)){
        //             \Log::info("New Owner on the house !");
        //             $respuesta['mensaje'] = 'Nueva red creada correctamente';
        //             $respuesta['tipo'] = 'success';
        //             $respuesta['data'] = $owner;
        //             return $respuesta;
        //         }
        //     }catch (\Exception $e){
        //         \Log::critical($e->getMessage());
        //         $respuesta['mensaje'] = 'Error al crear la red';
        //         $respuesta['tipo'] = 'error';
        //         return $respuesta;
        //     }*/
        // }else{
        //     try{
        //         if ($barrio =  Barrio::create($input)){
        //             \Log::info("New Barrio on the house !");
        //             Session::flash('message', 'Nuevo barrio creada correctamente');
        //             return redirect('barrios');
        //         }
        //     }catch (\Exception $e){
        //         \Log::critical($e->getMessage());
        //         Session::flash('error_message', 'Error al crear el barrio');
        //         return redirect()->back()->with('error', 'Error al crear barrio');
        //     }
        // }
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
        if (!$this->user->hasAccess('barrios.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if($barrio = Barrio::find($id)){
            $ciudades = \DB::table('ciudades')->orderBy('id','asc')->pluck('descripcion','id');
            $data = [
                'barrio' => $barrio,
                'ciudades' => $ciudades,
            ];
            return view('barrios.edit', $data);
        }else{
            Session::flash('error_message', 'Barrio no encontrada');
            return redirect('barrios');
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
        if (!$this->user->hasAccess('barrios.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if ($barrio = Barrio::find($id)){
            $input = $request->all();
            try{

                $barrio->fill($input);
                if($barrio->update()){
                    Session::flash('message', 'Barrio actualizada exitosamente');
                    return redirect('barrios');
                }
            }catch (\Exception $e){
                \Log::error("Error updating network: " . $e->getMessage());
                Session::flash('error_message','Error al intentar actualizar el barrio');
                return redirect('barrios');
            }
        }else{
            \Log::warning("Barrio not found");
            Session::flash('error_message', 'Barrio no encontrado');
            return redirect('barrios');
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
        if (!$this->user->hasAccess('barrios.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $message = '';
        $error = '';
        \Log::debug("Intentando eliminar barrio ".$id);
        if ($barrio = Barrio::find($id)){
            try{
                $sucursales = \DB::table('branches')->where('barrio_id',$id)->count();

                if($sucursales <= 0){
                    if(Barrio::where('id',$id)->delete()){
                        $message =  'Barrio eliminada correctamente';
                        $error = false;
                    }
                }else{
                    $message = 'El registro tiene sucursales asociadas al mismo.';
                    $error = true;
                }
            }catch (\Exception $e){
                \Log::error("Error deleting network: " . $e->getMessage());
                $message =  'Error al intentar eliminar el barrio';
                $error = true;
            }
        }else{
            $message =  'Barrio no encontrada';
            $error = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }
    
}
