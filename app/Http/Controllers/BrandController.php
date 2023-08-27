<?php

namespace App\Http\Controllers;

use Session;

use App\Models\Brand;
use App\Http\Requests;
use App\Models\ModelBrand;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\BrandRequest;
use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;

class BrandController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function index(Request $request)
    {    
        if (!$this->user->hasAccess('brands')) {
            \Log::error(
                'Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]
            );
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $name = $request->get('name');
        $brands = Brand::filterAndPaginate($name);
        return view('brands.index', compact('brands','name'));
    }

    
    public function create()
    {
        if (!$this->user->hasAccess('brands.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        return view('brands.create');
    }

 
    public function store(Request $request)
    {
        if (!$this->user->hasAccess('brands.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $input = $request->all();
        if($request->ajax()){
            $respuesta = [];
            try{
                if ($brands = Brand::create($input)){
                    \Log::info("New Brand on the house !");
                    $respuesta['mensaje'] = 'Nueva Marca creada correctamente';
                    $respuesta['tipo'] = 'success';
                    $respuesta['data'] = $brands;
                    return $respuesta;
                }
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Error al crear la marca';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }
        }else{
            try{
                if ($brands = Brand::create($input)){
                    \Log::info("Brand ingresado correctamente!");
                    Session::flash('message', 'Nueva Marca creada correctamente');
                    return redirect('brands');
                }
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                Session::flash('error_message', 'Error al crear la marca');
                return redirect()->back()->with('error', 'Error al crear la marca');
            }
        } 

    }

    
    public function show($id)
    {
        //
    }

   
    public function edit($id)
    {
        if (!$this->user->hasAccess('brands.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        if($brand = Brand::find($id)){
            $data = ['brand' => $brand];
            return view('brands.edit', $data);
        }else{
            Session::flash('error_message', 'Marca no encontrada');
            return redirect('brands');
        }

    }

    public function update(BrandRequest $request, $id)
    {
        if (!$this->user->hasAccess('brands.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        } 
        if ($brand = Brand::find($id)){
            $input = $request->all();
            //dd($input);
            try{
                $brand->fill($input);
                // $brand->fill(['updated_by' => $this->user->id]);
                if($brand->update()){
                    \Log::info("Marca actualizada exitosamente");

                    Session::flash('message', 'Marca actualizada exitosamente');
                    return redirect('brands');
                }
            }catch (\Exception $e){
                \Log::error("Error updating network: " . $e->getMessage());
                Session::flash('error_message','Error al intentar actualizar la marca');
                return redirect('brands');
            }
        }else{
            \Log::warning("Brand not found");
            Session::flash('error_message', 'Marca no encontrada');
            return redirect('brands');
        }
    }

   
    public function destroy($id)
    {
        if (!$this->user->hasAccess('brands.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $message = '';
        $error = '';
        \Log::debug("Attempting to delete a given brand");
        if (Brand::find($id)){
            $activeBrands = ModelBrand::where('brand_id', $id)->get();
            if (count($activeBrands) == 0){
                try{
                    if (Brand::destroy($id)){
                        $message =  'Marca eliminada correctamente';
                        $error = false;
                    }
                }catch (\Exception $e){
                    \Log::error("Error deleting Brand: " . $e->getMessage());
                    $message =  'Error al intentar eliminar la marca';
                    $error = true;
                }
            }else{
                \Log::warning("Brand {$id} have still active models");
                $message =  'Esta Marca aun cuenta con modelos activos';
                $error = true;
            }
        }else{
            $message =  'Marca no encontrada';
            $error = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }
}
