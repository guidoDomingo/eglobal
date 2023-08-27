<?php

namespace App\Http\Controllers;

use Session;

use App\Models\Tag;
use App\Models\Ticket;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TagsController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function index(Request $request)
    {
        // if (!$this->user->hasAccess('tags')) {
        //     \Log::error('Unauthorized access attempt',
        //         ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
        //     Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
        //     return redirect('/');
        // }

        $ticket_id      = $request->get('ticket_id');
        $ticket         = Ticket::find($ticket_id);
        $tags           = Tag::where('tickets_campaigns_id',$ticket_id)->get();
        $campaign_id    = $request->get('campaign_id');

        return view('tags.index', compact('ticket', 'ticket_id','tags','campaign_id'));
    }

   
    public function create(Request $request)
    {
        // if (!$this->user->hasAccess('tags.add|edit')) {
        //     \Log::error('Unauthorized access attempt',
        //         ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
        //     Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
        //     return redirect('/');
        // }
        $input        = $request->all();
        $ticket_id    = $request->get('ticket_id');
        $campaign_id  = $input['campaign_id'];


        return view('tags.create',compact('ticket_id','campaign_id'));
    }

    public function store(Request $request)
    {
        // if (!$this->user->hasAccess('tags.add|edit')) {
        //     \Log::error('Unauthorized access attempt',
        //         ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
        //     Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
        //     return redirect('/');
        // }
        \DB::beginTransaction();

        $input      = $request->all();
        $ticket_id  = $input['tickets_campaigns_id'];
        $campaign_id  = $input['campaign_id'];

        if($request->ajax())
        {
           //
        }else{
            try{

                if ($tag =  Tag::create($input)){
                    \DB::commit();
                    \Log::info("Nueva etiqueta agregada correctamente");
                    Session::flash('message', 'Nueva etiqueta agregada correctamente');
                    return redirect()->to('tags/?ticket_id='.$ticket_id.'&campaign_id='.$campaign_id)->withInput();
                }
            }catch (\Exception $e){
                \DB::rollback();
                \Log::critical($e->getMessage());
                Session::flash('error_message', 'Error al crear la etiqueta');
                return redirect()->back()->with('error', 'Error al crear la etiqueta');
            }
        }
    }

    public function show($id)
    {
        //
    }

    public function edit(Request $request, $id)
    {
        // if (!$this->user->hasAccess('tags.add|edit')) {
        //     \Log::error('Unauthorized access attempt',
        //         ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
        //     Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
        //     return redirect('/');
        // }
        $input      = $request->all();
        \Log::info($input);

        if($tag = Tag::find($id)){
            $data = ['tag' => $tag ];
            $campaign_id  = $input['campaign_id'];

            return view('tags.edit', $data, compact('campaign_id') );
        }else{
            Session::flash('error_message', 'Etiqueta no encontrada.');
            return redirect('tags');
        }

    }

    public function update(Request $request, $id)
    {
        // if (!$this->user->hasAccess('tags.add|edit')) {
        //     \Log::error('Unauthorized access attempt',
        //         ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
        //     Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
        //     return redirect('/');
        // }
        $input          = $request->all();
        \Log::info($input);
        $campaign_id    = $input['campaign_id'];
        $ticket_id      = $input['tickets_campaigns_id'];

        if ($tag = Tag::find($id)){
            $input = $request->all();
            try{
                $tag->fill($input);
                if($tag->update()){
                    Session::flash('message', 'Etiqueta actualizada exitosamente');
                    // return redirect('tickets');
                    //return redirect()->to('tags/?ticket_id='.$ticket_id);
                    return redirect()->to('tags/?ticket_id='.$ticket_id.'&campaign_id='.$campaign_id)->withInput();

                }
            }catch (\Exception $e){
                \Log::error("Error updating tags: " . $e->getMessage());
                Session::flash('error_message','Error al intentar actualizar la etiqueta');
                return redirect('tags');
                
            }
        }else{
            \Log::warning("Tag not found");
            Session::flash('error_message', 'Etiqueta no encontrada');
            return redirect('tags');
        }

    }


    public function destroy($id)
    {
        // if (!$this->user->hasAccess('tags.delete')) {
        //     \Log::error('Unauthorized access attempt',
        //         ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
        //     Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
        //     return redirect('/');
        // }
        $message    = '';
        $error      = '';
        \Log::debug("Intentando elimiar tag_id ".$id);
        if ($tag = Tag::find($id)){
            try{
               
                if (Tag::where('id',$id)->delete()){
                    $message =  'Etiqueta eliminada correctamente';
                    $error = false;
                }
            }catch (\Exception $e){
                \Log::error("Error deleting tag: " . $e->getMessage());
                $message =  'Error al intentar eliminar la etiqueta';
                $error = true;
            }
        }else{
            $message =  'Etiqueta no encontrado';
            $error = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }
   

}
