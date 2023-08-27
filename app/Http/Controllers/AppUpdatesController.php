<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Owner;
use App\Models\Updates;
use App\Http\Requests\AppUpdateRequest;
use Session;

class AppUpdatesController extends Controller
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
    public function index()
    {
        if (!$this->user->hasAccess('applications')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $updates = \DB::table('app_updates')
        ->select('app_updates.id as id','version','app_updates.created_at','app_updates.updated_at','app_updates.deleted_at','username','owners.name as owner')
        ->join('users','users.id','=','app_updates.user_id')
        ->join('owners','owners.id','=','app_updates.owner_id')
        ->whereNull('app_updates.deleted_at')
        ->orderby('id', 'DESC')
        ->paginate(20);
        
        return view('app_updates.index',compact('updates'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        if (!$this->user->hasAccess('applications.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $owners = Owner::orderBy('name')->get()->pluck('name','id')->toArray();
        $owners[0] = 'Todos';
        ksort($owners);

        return view('app_updates.create',compact('owners'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!$this->user->hasAccess('applications.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = $request->all();
        $version = str_replace('.','_',$input['version']);
        
        if ($request->hasFile('file')) {
            $file_path = $request->file('file')->getClientOriginalName();
            
            $file_name = pathinfo($file_path, PATHINFO_FILENAME);
            $extension = pathinfo($file_path, PATHINFO_EXTENSION);
            
            $file = $request->file('file');            
            $file->move(public_path().'/resources/updates',$file_name.'_v'.$version.'.'.$extension);
        } else {
            Session::flash('error_message', 'Debe adjuntar un archivo');
            return redirect('app_updates');
        }

        
        $input['user_id']   = $this->user->id;
        $input['file_path'] = '/resources/updates/'.$file_name.'_v'.$version.'.'.$extension;  
        try
        {
           if($update = Updates::create($input))
           {
            \Log::info("Se publicó una nueva actualización");
            Session::flash('message', 'Nueva aplicación publicada correctamente');
                return redirect('app_updates');
           }     
        }
        catch (\Exception $e)
        {
            \Log::warning($e->getMessage());
            Session::flash('error_message', 'Error al publicar actualización');
            return redirect()->back()->with('error', 'Error al publicar actualización');
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
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!$this->user->hasAccess('applications.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $message = '';
        $error = '';
        try
        {
            if($updates = Updates::find($id)){
                Updates::where('id',$id)->delete();
                $error      = false;
                $message    = 'Registro eliminado exitosamente';     
                
                //ELIMINAR ARCHIVO RELACIONADO
                $file = public_path().$updates->file_path;
                if (!unlink($file)) {                      
                    \Log::warning('Deleting app_update file '.$updates->file_path.' failed. ID:'.$id);
                }  
                else {                      
                    \Log::info('Deleting app_update file '.$updates->file_path.' success. ID:'.$id);
                }  

            }else{
                $error      = true;
                $message    = 'No se encontró el registro';
            }                            
        }
        catch
        (\Exception $e)
        {
           \Log::warning('Error al eliminar app_update',['result'=>$e]); 
           
           $error      = true;
           $message    = 'No se pudo eliminar el registro';
        }                        

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }
}
