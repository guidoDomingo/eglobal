<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Requests\StoreServiceProviderProductRequest;
use App\Http\Requests\UpdateServiceProviderProductRequest;
use App\Http\Controllers\Controller;
use App\Models\ServiceProviderProduct;
use App\Models\WebServiceProvider;
use Session;


class ServiceProviderProductController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $name = $request->get('name');
        $wsproviderId = $request->get('wsprovider');
        $wsproducts = ServiceProviderProduct::filterAndPaginate($wsproviderId, $name);
        return view('wsproducts.index', compact('wsproducts', 'name', 'wsproviderId'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $wsproviders = WebServiceProvider::all()->pluck('name', 'id');
        $data = ['wsproviders' => $wsproviders];
        return view('wsproducts.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreServiceProviderProductRequest $request)
    {
        $input = $request->all();
        $this->user = \Sentinel::getUser()->id;
        $username = \Sentinel::getUser()->username;
        $wsproduct = new ServiceProviderProduct;
        $wsproduct->description = $input['description'];
        $wsproduct->created_by = $this->user;
        $wsproduct->service_provider_id = $input['service_provider_id'];

        if ($wsproduct->save()) {
            $message = 'Agregado correctamente';
            Session::flash('message', $message);
            return redirect()->route('wsproducts.index');
        } else {
            Session::flash('error_message', 'Error al guardar el registro');
            return redirect()->route('wsproducts.index');
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
        $wsproviders = WebServiceProvider::all()->pluck('name', 'id');
        $wsproduct = ServiceProviderProduct::find($id);
        $data = [
            'wsproviders' => $wsproviders,
            'wsproduct' => $wsproduct,
            'wsrequests' => $wsproduct->webservicerequests,
            //accounting products
            'products' => $wsproduct->products,
        ];
        return view('wsproducts.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($id, UpdateServiceProviderProductRequest $request)
    {
        $this->user = \Sentinel::getUser()->id;
        $username = \Sentinel::getUser()->username;
        $wsproduct = ServiceProviderProduct::find($id);

        $wsproduct->description = $request->description;
        $wsproduct->service_provider_id = $request->service_provider_id;
        $wsproduct->updated_by = $this->user;

        if ($wsproduct->save()) {
            $message = 'Actualizado correctamente';
            Session::flash('message', $message);
            return redirect()->back();
        } else {
            Session::flash('error_message', 'Error al guardar el registro');
            return redirect()->back();
        }
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
