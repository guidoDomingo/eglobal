<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Session;

class AppTokenDropboxController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function index()
    {
        if (!$this->user->hasAccess('token_dropbox')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $token = \DB::table('apps_versions')
        ->select('id','name','hash','created_at')
        ->where('id','=',-1)
        ->get();
        
        return view('token_dropbox.index',compact('token'));
    }

    public function create()
    {
       //
    }

    public function store(Request $request)
    {
       //
    }

    public function show($id)
    {
        //
    }


    public function edit($id)
    {
        if (!$this->user->hasAccess('token_dropbox.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $dropbox =  $token = \DB::table('apps_versions')
        ->select('id','name','hash','created_at')
        ->where('id','=',$id)
        ->get();
        
        return view('token_dropbox.edit', compact('dropbox'));
    }

    
    public function update(Request $request, $id)
    {
                    
        $update_token= \DB::table('apps_versions')
           ->where('id','=',$id)
           ->update(['name' =>  $request->name, 
                    'hash' =>  $request->hash,
                    'updated_at' => Carbon::now()]);
           \Log::info("Actualizacion del token exitosa");
            
        Session::flash('message', 'Actualizacion del token existosa');
        //return redirect('token_dropbox');
        return redirect()->back()->withInput();
    }

    
    public function destroy($id)
    {
        //
    }
       
}
