<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

class WebServiceModelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($service_id)
    {
        $servicesmodels = \DB::table('services_model')->where('service_id',$service_id)->get();
        return view('webservicesmodels.index', compact('service_id','servicesmodels'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request, $service_id)
    {
        try{
            \DB::table('services_model')->insert(
                ['service_id' => $service_id,'key' => $request->key, 'value' => $request->value]

            );

            $servicesmodels = \DB::table('services_model')->where('service_id',$service_id)->get();
            return view('webservicesmodels.index', compact('service_id','servicesmodels'));

        }catch (Exception $e){
            \Log::warning('No se inserto el modelo '.$e);

            $servicesmodels = \DB::table('services_model')->where('service_id',$service_id)->get();
            return view('webservicesmodels.index', compact('service_id','servicesmodels'));

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
        $data = explode('-',$id);
        $id = $data[0];
        $key = $data[1];

        if(is_numeric($id)){
            try{
                $WebServiceModel = \DB::table('services_model')->where('service_id', '=', $id)->where('key', '=', $key)->delete();
                if($WebServiceModel){
                    $message = 'Modelo eliminado correctamente';
                    $error = false;
                }
            }catch (\Exception $e){
                \Log::error("Error deleting Model: " . $e->getMessage());
                $message = 'Error al intentar eliminar el modelo';
                $error = true;
            }

        }else{
            \Log::error("Error deleting Model: ID no es numerico ");
            $message = 'Error al intentar eliminar el modelo - Id no es numerico';
            $error = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);


    }
}
