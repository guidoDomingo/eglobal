<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Http\Requests\OwnerRequest;
use App\Models\Owner;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;

class OwnerController extends Controller
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
        if (!$this->user->hasAccess('owner')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $name = $request->get('name');
        $owners = Owner::filterAndPaginate($name);
        //$owners = Owner::paginate(10);
        return view('owners.index', compact('owners', 'name'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!$this->user->hasAccess('owner.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        return view('owners.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param OwnerRequest|Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(OwnerRequest $request)
    {
        if (!$this->user->hasAccess('owner.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $input = $request->all();
        $input['created_by'] = $this->user->id;

        if($request->ajax()){
            $respuesta = [];
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
            }
        }else{
            try{
                if ($owner = Owner::create($input)){
                    \Log::info("New Owner on the house !");
                    Session::flash('message', 'Nueva red creada correctamente');
                    return redirect('owner');
                }
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                Session::flash('error_message', 'Error al crear la red');
                return redirect()->back()->with('error', 'Error al crear red');
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
        if (!$this->user->hasAccess('owner.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if($owner = Owner::find($id)){
            $data = ['owner' => $owner];
            return view('owners.edit', $data);
        }else{
            Session::flash('error_message', 'Red no encontrada');
            return redirect('owner');
        }

    }

    /**
     * Update the specified resource in storage.
     *
     * @param OwnerRequest|Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(OwnerRequest $request, $id)
    {
        if (!$this->user->hasAccess('owner.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if ($owner = Owner::find($id)){
            $input = $request->all();
            try{
                $owner->fill($input);
                $owner->fill(['updated_by' => $this->user->id]);
                if($owner->update()){
                    Session::flash('message', 'Red actualizada exitosamente');
                    return redirect('owner');
                }
            }catch (\Exception $e){
                \Log::error("Error updating network: " . $e->getMessage());
                Session::flash('error_message','Error al intentar actualizar la red');
                return redirect('owner');
            }
        }else{
            \Log::warning("Owner not found");
            Session::flash('error_message', 'Red no encontrada');
            return redirect('owner');
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
        if (!$this->user->hasAccess('owner.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $message = '';
        $error = '';
        \Log::debug("Attempting to delete a given network");
        if (Owner::find($id)){
            $activeBranches = Branch::where('owner_id', $id)->get();
            if (count($activeBranches) == 0){
                try{
                    if (Owner::destroy($id)){
                        $message =  'Red eliminada correctamente';
                        $error = false;
                    }
                }catch (\Exception $e){
                    \Log::error("Error deleting network: " . $e->getMessage());
                    $message =  'Error al intentar eliminar la red';
                    $error = true;
                }
            }else{
                \Log::warning("Network {$id} have still active branches");
                $message =  'Esta red aun cuenta con sucursales activas';
                $error = true;
            }
        }else{
            $message =  'Red no encontrada';
            $error = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }
    
}
