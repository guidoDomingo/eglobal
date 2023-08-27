<?php

namespace App\Http\Controllers;

use Session;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\BancoAltas;
class BancosController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function index(Request $request)
    {
        if (!$this->user->hasAccess('bancos')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $bancos   = BancoAltas::all();

        return view('bancos_abm.index', compact('bancos'));
    }
   
    public function create()
    {
        if (!$this->user->hasAccess('bancos.add')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        return view('bancos_abm.create');
    }

    public function store(Request $request)
    {
        if (!$this->user->hasAccess('bancos.add')) {
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
                if ($banco      =  BancoAltas::create($input)){

                    \DB::commit();
                    return redirect('bancos')->with('guardar','ok');
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
        if (!$this->user->hasAccess('bancos.edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if($banco = BancoAltas::find($id)){
          
            $data = [
                'banco'           => $banco,     
            ];

            return view('bancos_abm.edit', $data);
        }else{
            Session::flash('error_message', 'Banco no encontrado.');
            return redirect('bancos');
        }

    }

    public function update(Request $request, $id)
    {
        if (!$this->user->hasAccess('bancos.edit')) 
        {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        \DB::beginTransaction();
        if ($banco = BancoAltas::find($id)){
            $input = $request->all();
            try{
                    
                $banco->fill($input);
                if($banco->update()){
                    \DB::commit();
                    return redirect('bancos')->with('actualizar','ok');;
                }
            }catch (\Exception $e){
                \DB::rollback();

                \Log::error("Error updating bancos: " . $e->getMessage());
                return redirect()->back()->withInput()->with('error', 'ok');
            }
        }else{
            \Log::warning("bancos not found");
            return redirect()->back()->withInput()->with('error', 'ok');
        }
        
    }

    public function destroy($id)
    {
        if (!$this->user->hasAccess('bancos.delete')) 
        {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $message    = '';
        $error      = '';
        \Log::debug("Intentando elimiar banco ".$id);

        if ($banco = BancoAltas::find($id))
        {
            try{
               
                if (BancoAltas::where('id',$id)->delete()){
                    $message  =  'Banco eliminado correctamente';
                    $error      = false;
                }
            }catch (\Exception $e){
                \Log::error("Error deleting banco: " . $e->getMessage());
                $message    =  'Error al intentar eliminar el banco';
                $error      = true;
            }
          
            \DB::commit();
        }else{
            $message =  'Banco no encontrado';
            $error = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }
   

}
