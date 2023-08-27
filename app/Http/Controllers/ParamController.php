<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\Applications;
use App\Http\Requests\StoreParamToApplicationPivotRequest;
use App\Http\Requests\UpdateParamToApplicationPivotRequest;
use App\Models\Param;
use Carbon\Carbon;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Session;
use Mockery\CountValidator\Exception;


class ParamController extends Controller
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
    public function index($appId,Request $request)
    {
        //
        if (!$this->user->hasAccess('applications.params')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $where = "application_id = ".$appId;
        if($request->get('name')){
           $where .= " AND params.description LIKE '%". $request->get('name') ."%'";
        }

        $appconfigs = \DB::table('application_param')
            ->join("params","params.id","=","application_param.param_id")
            ->whereRaw($where)
            ->paginate(20);

        return view('params.applications.index', compact('appId','appconfigs'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($appId)
    {

        if (!$this->user->hasAccess('applications.params.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $app = Applications::find($appId);
        $params = Param::all()->pluck('description', 'id');

        $data = [
            'application' => $app,
            'params' => $params,
        ];
        return view('params.applications.add', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($appId, StoreParamToApplicationPivotRequest $request)
    {

        if (!$this->user->hasAccess('applications.params.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        try{
            \DB::table('application_param')->insert(
                [
                    'value' => $request['value'],
                    'application_id' => $appId,
                    'param_id' => $request['param_id'],
                    'created_by' => $this->user->id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now()
                ]
            );

            $message = 'Agregado correctamente';
            Session::flash('message', $message);
            return redirect()->route('applications.params.index', $appId);

        }catch (Exception $e){
            $message = 'No se agrego parametro a esta aplicación';
            Session::flash('error_message', $message);
            return redirect()->route('applications.params.index', $appId);
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
    public function edit($appId, $param_id)
    {

        if (!$this->user->hasAccess('applications.params.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $app = Applications::find($appId);
        $app_params = \DB::table('application_param')
            ->join('params','params.id','=','application_param.param_id')
            ->where('application_id', $appId)->where('param_id',$param_id)
            ->first();
        $params = Param::all()->pluck('description', 'id');
        $data = [
            'appId' => $appId,
            'app_params' => $app_params,
            'application' => $app,
            'params' => $params,
        ];
        return view('params.applications.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($appId, $param_id, UpdateParamToApplicationPivotRequest $request)
    {
        if (!$this->user->hasAccess('applications.params.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        try{
            $this->user = \Sentinel::getUser()->id;
            \DB::table('application_param')
                ->where('application_id', $appId)
                ->where('param_id', $param_id)
                ->update([
                    'value' => $request['value'],
                    'updated_by' => $this->user,
                    'updated_at' => Carbon::now(),
                ]);


            $message = 'Actualizado correctamente';
            Session::flash('message', $message);
            return redirect()->route('applications.params.index', $appId);

        }catch (Exception $e){
            $message = 'No se agrego parametro a esta aplicación';
            Session::flash('error_message', $message);
            return redirect()->route('applications.params.index', $appId);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($appId, $paramId, Request $request)
    {
        if (!$this->user->hasAccess('applications.params.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        try{
             \DB::table('application_param')->where('application_id', '=', $appId)->where('param_id', '=', $paramId)->delete();
             $error = false;
             $message = 'El parámetro ha sido eliminado correctamente';

            if ($request->ajax()) {
                return response()->json([
                    'error' => $error,
                    'message' => $message,
                ]);
            } else {
                Session::flash('message', $message);
                return redirect()->route('applications.params.index', $appId);
            }

        }catch (Exception $e){
            $error = true;
            $message = "El parámetro no se pudo eliminar";

            if ($request->ajax()) {
                return response()->json([
                    'error' => $error,
                    'message' => $message,
                ]);
            } else {
                Session::flash('message', $message);
                return redirect()->route('applications.params.index', $request->route('applications'));
            }

        }
    }
}
