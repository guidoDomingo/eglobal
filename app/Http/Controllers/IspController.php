<?php

namespace App\Http\Controllers;

use Session;

use Carbon\Carbon;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Isp;

class IspController extends Controller
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
                if ($isp = Isp::create($input)) {
                    \Log::info("Proveedor de servicios de internet (ISP) creado correctamente");
                    $respuesta['mensaje'] = 'Proveedor de servicios de internet (ISP) creado correctamente';
                    $respuesta['tipo'] = 'success';
                    $respuesta['data'] = $isp;
                    return $respuesta;
                }
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Error al crear Proveedor de servicios de internet (ISP)';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }
        }else{
            try{
                if ($isp = Isp::create($input)) {
                    \Log::info("Proveedor de servicios de internet (ISP) creado correctamente");
                    Session::flash('message', 'Proveedor de servicios de internet (ISP) creado');
                    return redirect('isp.index');
                }
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                Session::flash('error_message', 'Error al crear Proveedor de servicios de internet (ISP)');
                return redirect()->back()->with('error', 'Error al crear Proveedor de servicios de internet (ISP)');
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
