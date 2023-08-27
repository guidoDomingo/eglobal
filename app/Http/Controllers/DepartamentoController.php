<?php

namespace App\Http\Controllers;

use App\Models\Departamento;
use Response;
use Session;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class DepartamentoController extends Controller
{
    /**
     * Currently logged in User
     * @var |Cartalyst|Sentinel|Users|UserInterface
     */
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    /**
     * Display a list of roles available
     * @return Response
     */
    public function index(Request $request)
    {
        if (!$this->user->hasAccess('departamentos')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            return redirect('/')->with('error', 'No posee permisos para realizar esta accion.');
        }

        $description = $request->get('name');
        $departamentos = Departamento::filterAndPaginate($description);
        return view('departamentos.index', compact('departamentos'));
    }

    /**
     * Show the form for creating a new role
     * @return Response
     */
    public function create()
    {
        if (!$this->user->hasAccess('departamentos.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            return redirect('/')->with('error', 'No posee permisos para realizar esta accion.');
        }

        return view('departamentos.create');
    }

    /**
     * Store a new role
     * @param RoleRequest $request
     * @return Response
     */
    public function store(Request $request)
    {
        

        $input = $request->all();

        if($request->ajax()){
            $respuesta = []; ///del
            try{
                if ($departamento = Departamento::create($input)) {
                    \Log::info('Departamento creado.', $departamento->toArray());
                    $respuesta['mensaje'] = 'Departamento creado exitosamente';
                    $respuesta['tipo'] = 'success';
                    $respuesta['data'] = $departamento;
                    return $respuesta;
                }
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Error al crear el departamento';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }
    
        }else{
            if (!$this->user->hasAccess('departamentos.add|edit')) {
                \Log::error('Unauthorized access attempt',
                    ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
                return redirect('/')->with('error', 'No posee permisos para realizar esta accion.');
            }
            
            if ($departamento = Departamento::create($input)) {
                \Log::info('Departamento creado.', $departamento->toArray());
    
                Session::flash('message', 'Departamento creado exitosamente');
                return redirect()
                    ->route('departamentos.index')
                    ->with('success', 'Departamento creado correctamente');
            }
    
            \Log::error('Creacion de Departamento.', $input);
            Session::flash('error_message', 'Problemas al crear el Departamento');
            return redirect()
                ->route('departamentos.create')
                ->withInput()
                ->with('error', 'Problemas al crear el Departamento');

        }
       
    }

    /**
     * Show the form for editing the given role
     * @param int $id
     * @return Response
     */
    public function edit($id)
    {
        if (!$this->user->hasAccess('departamentos.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            return redirect('/')->with('error', 'No posee permisos para realizar esta accion.');
        }

        if ($departamento = Departamento::find($id)){
            return view('departamentos.edit', compact(['departamento']));
        }

        return redirect()->back()->with('error', 'Departamento no encontrado.');
    }

    /**
     * Update the specific role
     * @param RoleRequest $request
     * @param int $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        if (!$this->user->hasAccess('departamentos.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            return redirect('/')->with('error', 'No posee permisos para realizar esta accion.');
        }

        if (!$departamento = Departamento::find($id)){
            Session::flash('error_message', 'Departamento no encontrado');
            return redirect('departamentos');
        }
        
        $input = $request->all();

        $departamento->fill($input);

        if ($departamento->save()) {

            \Log::info('Departamento actualizado.', $departamento->toArray());

            #$departamento->killUsersSession();
            Session::flash('message', 'Departamento actualizada exitosamente');
            return redirect()
                ->route('departamentos.index')
                ->with('success', 'Departamento actualizado correctamente');
        }


        \Log::error('Actualizacion de Departamento.', $departamento->toArray());
        Session::flash('error_message', 'Problemas al actualizar Departamento');
        return redirect()
            ->route('departamentos.index')
            ->with('error', 'Problemas al actualizar Departamento');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        if (!$this->user->hasAccess('departamentos.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            return redirect('/')->with('error', 'No posee permisos para realizar esta accion.');
        }
        $message = '';
        $error = '';
        if ($departamento = Departamento::find($id)) {
            try {
                $ciudades = \DB::table('ciudades')->where('departamento_id',$id)->count();

                if($ciudades <= 0){
                    if (Departamento::where('id',$id)->delete()) {
                        $message = 'Departamento eliminado correctamente';
                        $error = false;
                    }
                }else{
                    $message = 'El registro tiene ciudades asociadas al mismo.';
                    $error = true;
                }

            } catch (\Exception $e) {
                \Log::error("Error deleting departamento: " . $e->getMessage());
                $message = 'Error al intentar eliminar el departamento';
                $error = true;
            }
        }else{
            \Log::warning("Departamento {$id} not found");
            $message =  'Departamento no encontrado';
            $error = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }
}