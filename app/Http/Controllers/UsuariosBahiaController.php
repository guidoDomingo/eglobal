<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Http\Requests\OwnerRequest;
use App\Models\UsuariosBahia;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;

class UsuariosBahiaController extends Controller
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
        if (!$this->user->hasAccess('usuarios_bahia')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $name = $request->get('name');
        $usuarios_bahia = UsuariosBahia::filterAndPaginate($name);

        //$marcas = Owner::paginate(10);
        return view('usuarios_bahia.index', compact('usuarios_bahia', 'name'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!$this->user->hasAccess('usuarios_bahia.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        return view('usuarios_bahia.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param OwnerRequest|Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!$this->user->hasAccess('usuarios_bahia.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $input = $request->all();

        if($request->ajax()){
            /*$respuesta = [];
            try{
                if ($owner = Owner::create($input)){
                    \Log::info("New Owner on the house !");
                    $respuesta['mensaje'] = 'Nueva red creada correctamente';
                    $respuesta['tipo'] = 'success';
                    $respuesta['data'] = $owner;
                    return $respuesta;
                }
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Error al crear la red';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }*/
        }else{
            try{
                if ($usuario =  UsuariosBahia::create($input)){
                    \Log::info("New User Bahia on the house !");
                    Session::flash('message', 'Nuevo usuario bahia creado correctamente');
                    return redirect('usuarios_bahia');
                }
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                Session::flash('error_message', 'Error al crear el usuario bahia');
                return redirect()->back()->with('error', 'Error al crear usuario ');
            }
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
        if (!$this->user->hasAccess('usuarios_bahia.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if($usuario_bahia = UsuariosBahia::find($id)){
            $data = [
                'usuario_bahia' => $usuario_bahia,
            ];
            return view('usuarios_bahia.edit', $data);
        }else{
            Session::flash('error_message', 'Usuario Bahia no encontrada');
            return redirect('usuarios_bahia');
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
        if (!$this->user->hasAccess('usuarios_bahia.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if ($usuario_bahia = UsuariosBahia::find($id)){
            $input = $request->all();
            try{

                $usuario_bahia->fill($input);
                if($usuario_bahia->update()){
                    Session::flash('message', 'Usuario Bahia actualizada exitosamente');
                    return redirect('usuarios_bahia');
                }
            }catch (\Exception $e){
                \Log::error("Error updating network: " . $e->getMessage());
                Session::flash('error_message','Error al intentar actualizar el usuario bahia');
                return redirect('usuarios_bahia');
            }
        }else{
            \Log::warning("Usuario Bahia not found");
            Session::flash('error_message', 'Usuario Bahia no encontrada');
            return redirect('usuarios_bahia');
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
        if (!$this->user->hasAccess('usuarios_bahia.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $message = '';
        $error = '';
        \Log::debug("Intentando elimiar usuario bahia ".$id);
        if ($usuario_bahia = UsuariosBahia::find($id)){
            try{
                if(UsuariosBahia::where('id',$id)->delete()){
                    $message =  'Usuario Bahia eliminada correctamente';
                    $error = false;
                }
            }catch (\Exception $e){
                \Log::error("Error deleting network: " . $e->getMessage());
                $message =  'Error al intentar eliminar el usuario bahia';
                $error = true;
            }
        }else{
            $message =  'Usuario Bahia no encontrada';
            $error = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }
    
}
