<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Models\WebservicesModels;
use App\Models\ServiceProviderProduct;
use App\Models\WebservicesViews;
use DB;
use Carbon\Carbon;

class WebServiceBuilderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($wsproduct_id)
    {
        $servicesviews  = DB::table('services_views_description')
            ->select(DB::raw('services_views_description.id, services_views_description.description, name'))
            ->join('screens','screens.id','=','services_views_description.screen_id')
            ->where('service_id', $wsproduct_id)
            ->orderBy('services_views_description.id')
            ->get();

        $servicesmodels = WebservicesModels::where('service_id',$wsproduct_id)->get();

        $wsproduct_provider =  ServiceProviderProduct::find($wsproduct_id);

        $screens = DB::table("screens")
            ->where('service_provider_id',$wsproduct_provider->service_provider_id)
            ->orwhere('service_provider_id',0)
            ->pluck('name','id');

        return view('webservicesbuilder.index', compact('wsproduct_id','servicesviews','servicesmodels','screens'));
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
    public function store($wsproduct_id, Request $request)
    {
        //
        $screen_view_description = new WebservicesViews;
        $screen_view_description->description   = $request->description;
        $screen_view_description->screen_id     = $request->screen_id;
        $screen_view_description->service_id    = $wsproduct_id;
        $screen_view_description->created_at    = Carbon::now();
        $screen_view_description->updated_at    = Carbon::now();;
        $screen_view_description->save();


        return redirect()->route('wsproducts.wsbuilder.index', ['wsproduct' => $wsproduct_id]);
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
        return view('webservicesbuilder.edit');
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
        //
    }
}
