<?php

namespace App\Http\Controllers;

use Session;
use App\Models\Content;
use Illuminate\Http\Request;
use App\Models\CampaignDetails;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\CanalCliente;
use App\Models\CategoriaCliente;
use App\Models\PromotionCategory;
use Illuminate\Support\Facades\Storage;
class CategoriaClienteController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function index(Request $request)
    {
        if (!$this->user->hasAccess('categorias')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $categorias   = CategoriaCliente::all();

        return view('categoria_clientes.index', compact('categorias'));
    }
   
    public function create()
    {
        if (!$this->user->hasAccess('categorias.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        return view('categoria_clientes.create');
    }

    public function store(Request $request)
    {
        if (!$this->user->hasAccess('categorias.add|edit')) {
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
          
                if ($categoria      =  CategoriaCliente::create($input)){

                    \DB::commit();
                    return redirect('categorias')->with('guardar','ok');
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
        if (!$this->user->hasAccess('categorias.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if($categoria = CategoriaCliente::find($id)){

            $data = [
                'categoria'   => $categoria
            ];

            return view('categoria_clientes.edit', $data);
        }else{
            Session::flash('error_message', 'Canal no encontrado.');
            return redirect('categorias');
        }

    }

    public function update(Request $request, $id)
    {
        if (!$this->user->hasAccess('categorias.add|edit')) 
        {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        \DB::beginTransaction();
        if ($categoria = CategoriaCliente::find($id)){
            
            $input = $request->all();

            try{
            
                $categoria->fill($input);
                if($categoria->update()){
                    \DB::commit();
                    return redirect('categorias')->with('actualizar','ok');;
                }
            }catch (\Exception $e){
                \DB::rollback();

                \Log::error("Error updating categorias: " . $e->getMessage());
                return redirect()->back()->withInput()->with('error', 'ok');
            }
        }else{
            \Log::warning("Categorias not found");
            return redirect()->back()->withInput()->with('error', 'ok');
        }
        
    }

    public function destroy($id)
    {
        if (!$this->user->hasAccess('categorias.delete')) 
        {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $message    = '';
        $error      = '';

        if ($categoria = CategoriaCliente::find($id))
        {
           
            try{
                \DB::beginTransaction();
                    
                if (CategoriaCliente::where('id',$id)->delete()){
                    $message  =  'Categoria eliminado correctamente';
                    $error    = false;
                }
                \DB::commit();

            }catch (\Exception $e){
                \DB::rollback();

                \Log::error("Error deleting categoria: " . $e->getMessage());
                $message  =  'Error al intentar eliminar el contenido';
                $error    = true;
            }

           
        }else{
            $message =  'Categoria no encontrada';
            $error = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }
   

}
