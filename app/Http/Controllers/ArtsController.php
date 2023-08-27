<?php

namespace App\Http\Controllers;

use Session;

use App\Models\Art;
use App\Models\Campaign;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;


class ArtsController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function index(Request $request)
    {
        if (!$this->user->hasAccess('arts')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
     
        $campaign_id    = $request->get('campaign_id');
        $campaign       = Campaign::find($campaign_id);
        $arts           = Art::where('campaigns_id',$campaign_id)->get();
        return view('arts.index', compact('arts','campaign','campaign_id'));
    }

    public function create(Request $request)
    {
        if (!$this->user->hasAccess('arts.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $campaign_id = $request->get('campaign_id');
        return view('arts.create',compact('campaign_id'));
    }

    public function store(Request $request)
    {
        if (!$this->user->hasAccess('arts.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();
        $input          = $request->all();
        \Log::info($input);

        $campaign_id    = $input['campaigns_id'];
   
        try{
            if(!empty($input['image'])){
                $imagen = $input['image'];
                $data_imagen = json_decode($imagen);
                $nombre_imagen = $data_imagen->name;
                $urlHost = request()->getSchemeAndHttpHost();
                \Log::info('Guardar imagen', ['path'=>$urlHost.'/resources/1/images/arts/'.$nombre_imagen]);
                Storage::disk('artes')->put($nombre_imagen,  base64_decode($data_imagen->data));
                $input['image'] = $urlHost.'/resources/images/arts/'.$nombre_imagen;
            }else{
                $input['image'] = '';
            }

            if ($art =  Art::create($input)){
                \DB::commit();
                \Log::info("Nuevo arte agregado correctamente");
                Session::flash('message', 'Nuevo arte agregado correctamente');
                return redirect()->to('arts/?campaign_id='.$campaign_id);

            }
        }catch (\Exception $e){
            \DB::rollback();
            \Log::critical($e->getMessage());
            Session::flash('error_message', 'Error al crear el arte');
            return redirect()->back()->with('error', 'Error al crear el arte');
        }
    }

    public function show($id)
    {
        //
    }
   
    public function edit($id)
    {
        if (!$this->user->hasAccess('arts.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if($art = Art::find($id))
        {
            $campaigns = \DB::table('campaigns')->orderBy('id','asc')->pluck('name','id');
            
            if(isset($art->campaigns_id)){
                $campaign_id  = $art->campaigns_id;
            }else {
                $campaign_id  = null;
            }

            $data = [
                'art' => $art,
                'campaigns'=> $campaigns,
                'campaign_id ' => $campaign_id ,
            ];
            return view('arts.edit', $data, compact('campaign_id'));
        }else{
            Session::flash('error_message', 'Arte no encontrado.');
            return redirect('arts');
        }

    }

    public function update(Request $request, $id)
    {
        if (!$this->user->hasAccess('arts.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $input          = $request->all();
        $campaigns_id   = $input['campaigns_id'];

        if ($art = Art::find($id)){
            $input = $request->all();
            try{
                if(!empty($input['image'])){
                    $imagen = $input['image'];
                    $data_imagen = json_decode($imagen);
                    $nombre_imagen = $data_imagen->name;
                    $urlHost = request()->getSchemeAndHttpHost();
                    \Log::info('Guardar imagen', ['path'=>$urlHost.'/resources/images/arts/'.$nombre_imagen]);
                    $input['image'] = $urlHost.'/resources/images/arts/'.$nombre_imagen;
                    if($art->image != $input['image']){
                        if(file_exists(public_path().'/resources'.trim($art->image))){
                            \Log::info(public_path().'/resources'.trim($art->image));
                            unlink(public_path().'/resources'.trim($art->image));
                        }
                        Storage::disk('artes')->put($nombre_imagen,  base64_decode($data_imagen->data));
                    }else{
                        unset($input['image']);
                    }
                }

                $art->fill($input);
                if($art->update()){
                    Session::flash('message', 'Arte actualizado exitosamente');
                    //return redirect('arts');
                    return redirect()->to('arts/?campaign_id='.$campaigns_id);

                }
            }catch (\Exception $e){
                \Log::error("Error updating arts: " . $e->getMessage());
                Session::flash('error_message','Error al intentar actualizar el arte');
                return redirect('arts');
            }
        }else{
            \Log::warning("Content not found");
            Session::flash('error_message', 'Arte no encontrado');
            return redirect('arts');
        }

    }

    public function destroy($id)
    {
        if (!$this->user->hasAccess('arts.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $message = '';
        $error = '';
        \Log::debug("Intentando elimiar arte_id ".$id);
        if ($art = Art::find($id)){
            try{
               
                if (Art::where('id',$id)->delete()){
                    $message    =  'Arte eliminado correctamente';
                    $error      = false;
                }
            }catch (\Exception $e){
                \Log::error("Error deleting art: " . $e->getMessage());
                $message    =  'Error al intentar eliminar el arte';
                $error      = true;
            }
        }else{
            $message    =  'Arte no encontrado';
            $error      = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }
   

}
