<?php

namespace App\Http\Controllers;

use Session;

use App\Models\Ticket;
use App\Models\Campaign;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class TicketsController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function index(Request $request)
    {
        if (!$this->user->hasAccess('tickets')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $campaign_id    = $request->get('campaign_id');
        $campaign       = Campaign::find($campaign_id);
        $tickets        = Ticket::where('campaigns_id',$campaign_id)->get();
        return view('tickets.index', compact('tickets','campaign','campaign_id'));
    }

    public function create(Request $request)
    {
        if (!$this->user->hasAccess('tickets.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $campaign_id = $request->get('campaign_id');
        return view('tickets.create',compact('campaign_id'));
    }

    public function store(Request $request)
    {
        if (!$this->user->hasAccess('tickets.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();

        $input          = $request->all();
        $campaigns_id   = $input['campaigns_id'];

        if($request->ajax())
        {
           //
        }else{
            try{

                if ($ticket =  Ticket::create($input)){
                    \DB::commit();

                    \Log::info("Nuevo ticket agregado correctamente");
                    Session::flash('message', 'Nuevo ticket agregado correctamente');
                    return redirect()->to('tickets/?campaign_id='.$campaigns_id);

                }
            }catch (\Exception $e){
                \DB::rollback();
                \Log::critical($e->getMessage());
                Session::flash('error_message', 'Error al crear el ticket');
                return redirect()->back()->with('error', 'Error al crear el ticket');
            }
        }
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        if (!$this->user->hasAccess('tickets.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if($ticket = Ticket::find($id)){

            $campaigns = \DB::table('campaigns')->orderBy('id','asc')->pluck('name','id');
            
            if(isset($ticket->campaigns_id)){
                $campaign_id = $ticket->campaigns_id;
            }else {
                $campaign_id = null;
            }

            $data = [
                'ticket'        => $ticket,
                'campaigns'     => $campaigns,
                'campaign_id'  => $campaign_id,
            ];
            return view('tickets.edit', $data, compact('campaign_id'));
        }else{
            Session::flash('error_message', 'Ticket no encontrado.');
            return redirect('tickets');
        }

    }

    public function update(Request $request, $id)
    {
        if (!$this->user->hasAccess('tickets.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $input = $request->all();
        $campaigns_id = $input['campaigns_id'];

        if ($ticket = Ticket::find($id)){
            $input = $request->all();
            try{
                $ticket->fill($input);
                if($ticket->update()){
                    Session::flash('message', 'Ticket actualizado exitosamente');
                    return redirect()->to('tickets/?campaign_id='.$campaigns_id);

                }
            }catch (\Exception $e){
                \Log::error("Error updating tickets: " . $e->getMessage());
                Session::flash('error_message','Error al intentar actualizar el ticket');
                return redirect('tickets');

            }
        }else{
            \Log::warning("Form not found");
            Session::flash('error_message', 'Ticket no encontrado');
            return redirect('tickets');
        }

    }


    public function destroy($id)
    {
        if (!$this->user->hasAccess('tickets.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $message    = '';
        $error      = '';
        \Log::debug("Intentando elimiar ticket_id ".$id);
        if ($ticket = Ticket::find($id)){
            try{
               
                if (Ticket::where('id',$id)->delete()){
                    $message =  'Ticket eliminado correctamente';
                    $error = false;
                }
            }catch (\Exception $e){
                \Log::error("Error deleting ticket: " . $e->getMessage());
                $message =  'Error al intentar eliminar el ticket';
                $error = true;
            }
        }else{
            $message =  'Ticket no encontrado';
            $error = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }
    

}
