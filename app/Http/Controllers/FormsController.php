<?php

namespace App\Http\Controllers;

use Session;

use App\Models\Forms;
use App\Models\Campaign;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;


class FormsController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function index(Request $request)
    {
        if (!$this->user->hasAccess('forms')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $campaign_id    = $request->get('campaign_id');
        $campaign       = Campaign::find($campaign_id);
        $forms          = Forms::where('campaigns_id',$campaign_id)->get();
        return view('forms.index', compact('forms','campaign','campaign_id'));
    }

    public function create(Request $request)
    {
        if (!$this->user->hasAccess('forms.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $campaign_id = $request->get('campaign_id');
        return view('forms.create',compact('campaign_id'));
    }

    public function store(Request $request)
    {
        if (!$this->user->hasAccess('forms.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();

        $input          = $request->all();
        $campaign_id    = $input['campaigns_id'];

        if($request->ajax())
        {
           //
        }else{
            try{

                if ($form =  Forms::create($input)){
                    \DB::commit();
                    \Log::info("Nuevo formulario agregado correctamente");
                    Session::flash('message', 'Nuevo formulario agregado correctamente');
                    return redirect()->to('forms/?campaign_id='.$campaign_id);

                }
            }catch (\Exception $e){
                \DB::rollback();
                \Log::critical($e->getMessage());
                Session::flash('error_message', 'Error al crear el formulario');
                return redirect()->back()->with('error', 'Error al crear el formulario');
            }
        }
    }

    public function show($id)
    {
        //
    }

   
    public function edit($id)
    {
        if (!$this->user->hasAccess('forms.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if($form = Forms::find($id)){

            $campaigns = \DB::table('campaigns')->orderBy('id','asc')->pluck('name','id');
            
            if(isset($form->campaigns_id)){
                $campaign_id = $form->campaigns_id;
            }else {
                $campaign_id = null;
            }

            $data = [
                'form'          => $form,
                'campaigns'     => $campaigns,
                'campaign_id'  => $campaign_id,
            ];
            return view('forms.edit', $data, compact('campaign_id'));
        }else{
            Session::flash('error_message', 'Formulario no encontrado.');
            return redirect('forms');
        }

    }

    public function update(Request $request, $id)
    {
        if (!$this->user->hasAccess('forms.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $input = $request->all();
        $campaigns_id = $input['campaigns_id'];

        if ($form = Forms::find($id)){
            $input = $request->all();
            try{
                $form->fill($input);
                if($form->update()){
                    Session::flash('message', 'Formulario actualizado exitosamente');
                    return redirect()->to('forms/?campaign_id='.$campaigns_id);
                }
            }catch (\Exception $e){
                \Log::error("Error updating forms: " . $e->getMessage());
                Session::flash('error_message','Error al intentar actualizar el formulario');
                return redirect('forms');
            }
        }else{
            \Log::warning("Form not found");
            Session::flash('error_message', 'Formulario no encontrado');
            return redirect('forms');
        }

    }


    public function destroy($id)
    {
        if (!$this->user->hasAccess('forms.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $message    = '';
        $error      = '';
        \Log::debug("Intentando elimiar form_id ".$id);
        if ($form = Forms::find($id)){
            try{
               
                if (Forms::where('id',$id)->delete()){
                    $message =  'Formualrio eliminado correctamente';
                    $error = false;
                }
            }catch (\Exception $e){
                \Log::error("Error deleting form: " . $e->getMessage());
                $message =  'Error al intentar eliminar el formulario';
                $error = true;
            }
        }else{
            $message =  'Formulario no encontrado';
            $error = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }

}
