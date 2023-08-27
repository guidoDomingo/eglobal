<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProductsRequest;
use App\Models\Owner;
use App\Models\Products;
use App\Models\Provider;
use App\Models\TaxType;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Session;

class ProductsController extends Controller
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
        if (!$this->user->hasAccess('products')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if ($this->user->hasRole('security_admin') || $this->user->hasRole('superuser')) {
            $products = Products::paginate(20);
        }else{
            $products = Products::where('owner_id', $this->user->owner_id)->paginate(20);
        }
        return view('products.index', compact('products'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!$this->user->hasAccess('products.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $data = array();
        if ($this->user->hasRole('security_admin')) {
            $providers = Provider::where('owner_id', $this->user->owner_id)->pluck('business_name', 'id');
            $owners = null;
        } else {
            $providers = Provider::pluck('business_name', 'id');
            $owners = Owner::pluck('name', 'id');
        }

        if (!$providers) {
            \Log::warning("Owner dont have Providers");
            Session::flash('error_message', 'No posee proveedores asignados');
            return redirect()->back();
        }
        $data['providers'] = $providers;


        // TODO ondanet
        if ($tax = TaxType::pluck('description', 'id')) {
            $data['ondanet_tax_types'] = $tax;
            $data['ondanet_currencies'] = array('Gs' => 'Gs.');
        } else {
            \Log::warning("Taxes not found");
            Session::flash('error_message', 'No posee impuestos asignados');
            return redirect()->back();

        }
        $data['selected_currency_type'] = null;
        $data['selected_tax_type'] = null;
        $data['selected_provider'] = null;
        $data['owners'] = $owners;
        $data['selected_owner'] = null;
        return view('products.create', $data);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ProductsRequest|Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(ProductsRequest $request)
    {
        if (!$this->user->hasAccess('products.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = $request->except('_token');
        if ($this->user->hasRole('security_admin') || $this->user->hasRole('superuser')) {
            if ($input['owner_id'] == '') {
                Session::flash('error_message', 'Debe elegir una red');
                return redirect()->back()->withInput();
            }
        } else {
            $input['owner_id'] = \Sentinel::getUser()->owner_id;
        }
        $input['created_by'] = \Sentinel::getUser()->id;
        try {
            Products::create($input);
            // Todo ondanet
            Session::flash('message', 'Producto creado correctamente');
            return redirect('products');
        } catch (\Exception $e) {
            \Log::error("Error creating a new Product - {$e->getMessage()}");
            Session::flash('error_message', 'Error al intentar crear el registro, intente nuevamente');
            return redirect()->back()->withInput();
        }


    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        if (!$this->user->hasAccess('products.view')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $product = array();
        if ($product = Products::find($id)) {
            $data['product'] = $product;
            return view('products.view', $data);
        } else {
            Session::flash('error_message', 'Producto no encontrado');
            return redirect()->back();
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!$this->user->hasAccess('products.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if ($product = Products::find($id)) {
            $data = array();
            $data['product'] = $product;
            if ($this->user->hasRole('security_admin')) {
                $providers = Provider::where('owner_id', $this->user->owner_id)->pluck('business_name', 'id');
                $owners = null;
            } else {
                $providers = Provider::pluck('business_name', 'id');
                $owners = Owner::pluck('name', 'id');
            }

            if (!$providers) {
                \Log::warning("Owner dont have Providers");
                Session::flash('error_message', 'No posee proveedores asignados');
                return redirect()->back();
            }

            // TODO ondanet
            if ($tax = TaxType::pluck('description', 'id')) {
                $data['ondanet_tax_types'] = $tax;
                $data['ondanet_currencies'] = array('Gs' => 'Gs.');
            } else {
                \Log::warning("Taxes not found");
                Session::flash('error_message', 'No posee impuestos asignados');
                return redirect()->back();

            }

            $data['providers'] = $providers;
            $data['selected_currency_type'] = $product->currency;
            $data['selected_tax_type'] = $product->tax_type_id;
            $data['selected_provider'] = $product->provider_id;
            $data['owners'] = $owners;
            $data['selected_owner'] = $product->owner_id;

            return view('products.edit', $data);
        } else {
            \Log::warning("Product not found");
            Session::flash('error_message', 'Producto no encontrado');
            return redirect('products');
        }


    }

    /**
     * Update the specified resource in storage.
     *
     * @param ProductsRequest|Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(ProductsRequest $request, $id)
    {
        if (!$this->user->hasAccess('products.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if ($product = Products::find($id)) {
            $input = $request->except(['_token', '_method']);
            $input['updated_by'] = $this->user->id;
            try {
                $product->update($input);
                Session::flash('message', 'Producto actualizado correctamente');
                return redirect('products');
            } catch (\Exception $e) {
                \Log::warning("Error on update product Id: {$product->id} | {$e->getMessage()}");
                Session::flash('error_message', 'Ha ocurrido un error al intentar actualziar el registro');
                return redirect('products');
            }
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if (!$this->user->hasAccess('products.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $message = '';
        $error = true;
        if ($product = Products::find($id)) {
            try {
                Products::destroy($id);
                $message = 'Producto eliminado exitosamente';
                $error = false;
            } catch (\Exception $e) {
                \Log::warning("Error attempting to destroy product Id: {$id} - {$e->getMessage()}");
                $message = 'Ocurrio un error al intentar eliminar el producto';
            }
        } else {
            $message = 'Producto no encontrado ';
            \Log::warning('Error attempting to destroy product - Product not found');
        }
        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }
}
