<?php

namespace App\Http\Controllers;

use Session;

use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\NetworkTechnology;

class NetworkTechnologyController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function index(Request $request)
    {
        //
    }

    public function create()
    {
        //
    }
   
    public function store(Request $request)
    {
        // if (!$this->user->hasAccess('departamentos.add|edit')) {
        //     \Log::error('Unauthorized access attempt',
        //         ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
        //     return redirect('/')->with('error', 'No posee permisos para realizar esta accion.');
        // }
        $input = $request->all();

        if($request->ajax()){
            $respuesta = [];
            try{
                if ($network_technology = NetworkTechnology::create($input)) {
                    \Log::info("Network technology creado correctamente");
                    $respuesta['mensaje'] = 'Nueva tecnología de redes creada';
                    $respuesta['tipo'] = 'success';
                    $respuesta['data'] = $network_technology;
                    return $respuesta;
                }
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Error al crear tecnología de redes';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }

        }else{
            try{
                if ($network_technology = NetworkTechnology::create($input)) {
                    \Log::info("Nueva tecnología de redes creada");
                    Session::flash('message', 'Nueva tecnología de redes creada');
                    return redirect('network.technologies.index');
                }
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                Session::flash('error_message', 'Error al crear la tecnologia de re');
                return redirect()->back()->with('error', 'Error al crear la tecnología de red');
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
        //
    }

 
    public function destroy($id)
    {
        //
    }

}
