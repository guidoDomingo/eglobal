<?php

namespace App\Http\Controllers;

use Session;
use App\Models\Content;
use Illuminate\Http\Request;
use App\Models\CampaignDetails;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\CanalCliente;
use App\Models\PromotionCategory;
use Illuminate\Support\Facades\Storage;
class CanalClienteController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function index(Request $request)
    {
        if (!$this->user->hasAccess('canales')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $canales   = CanalCliente::all();

        return view('canal_clientes.index', compact('canales'));
    }
   
    public function create()
    {
        if (!$this->user->hasAccess('canales.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        return view('canal_clientes.create');
    }

    public function store(Request $request)
    {
        if (!$this->user->hasAccess('canales.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = $request->all();
        \DB::beginTransaction();

        if($request->ajax())
        {
           //
        }else{
            try{
          
                if ($canal      =  CanalCliente::create($input)){

                    \DB::commit();
                    return redirect('canales')->with('guardar','ok');
                }
            }catch (\Exception $e){
                \DB::rollback();
                \Log::critical($e->getMessage());
                return redirect()->back()->withInput()->with('error', 'ok');
            }
        }
    }

    public function show($id)
    {
        //
    }
   
    public function edit($id)
    {
        if (!$this->user->hasAccess('canales.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if($canal = CanalCliente::find($id)){

            $data = [
                'canal'   => $canal
            ];

            return view('canal_clientes.edit', $data);
        }else{
            Session::flash('error_message', 'Canal no encontrado.');
            return redirect('canales');
        }

    }

    public function update(Request $request, $id)
    {
        if (!$this->user->hasAccess('canales.add|edit')) 
        {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        \DB::beginTransaction();
        if ($canal = CanalCliente::find($id)){
            
            $input = $request->all();

            try{
            
                $canal->fill($input);
                if($canal->update()){
                    \DB::commit();
                    return redirect('canales')->with('actualizar','ok');;
                }
            }catch (\Exception $e){
                \DB::rollback();

                \Log::error("Error updating canales: " . $e->getMessage());
                return redirect()->back()->withInput()->with('error', 'ok');
            }
        }else{
            \Log::warning("Canales not found");
            return redirect()->back()->withInput()->with('error', 'ok');
        }
        
    }

    public function destroy($id)
    {
        if (!$this->user->hasAccess('canales.delete')) 
        {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $message    = '';
        $error      = '';

        if ($canal = CanalCliente::find($id))
        {
           
            try{
                \DB::beginTransaction();
                    
                if (CanalCliente::where('id',$id)->delete()){
                    $message  =  'Canal eliminado correctamente';
                    $error    = false;
                }
                \DB::commit();

            }catch (\Exception $e){
                \DB::rollback();

                \Log::error("Error deleting content: " . $e->getMessage());
                $message  =  'Error al intentar eliminar el contenido';
                $error    = true;
            }

           
        }else{
            $message =  'Canal no encontrado';
            $error = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }
   

}
