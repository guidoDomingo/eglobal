<?php

namespace App\Http\Controllers;


use App\Http\Requests\VoucherTypeRequest;
use App\Models\VoucherType;
use Illuminate\Http\Request;
use Session;

class VoucherTypeController extends Controller
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
    public function index(Request $request)
    {
        if (!$this->user->hasAccess('vouchers')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $description = $request->get('name');
        $vouchertypes = VoucherType::filterAndPaginate($description);


        return view('vouchers.index', compact('vouchertypes','description'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!$this->user->hasAccess('vouchers.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        return view('vouchers.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param VoucherTypeRequest|Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(VoucherTypeRequest $request)
    {
        if (!$this->user->hasAccess('vouchers.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \Log::info($request);
        $input = $request->except('_token');
        if (VoucherType::where('voucher_type_code', $input['voucher_type_code'])->count() == 0){
            $input['created_by'] = $this->user->id;
            try{
                // TODO Ondanet
                if (VoucherType::create($input)){
                    Session::flash('message', 'Registro creado exitosamente');
                    return redirect('vouchers');
                }else{
                    Session::flash('error_message', 'Ocurrio un error al intentar guardar el registro');
                    return redirect()->back()->withInput();
                }
            }catch (\Exception $e){
                \Log::error("Error saving new voucherType - {$e->getMessage()}");
                Session::flash('error_message', 'Ocurrio un error al intentar guardar el registro');
                return redirect()->back()->withInput();
            }

        }else{
            Session::flash('error_message', 'Este codigo ya se encuentra registrado en el sistema');
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
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!$this->user->hasAccess('vouchers.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if($voucher = VoucherType::find($id)){
            $data = ['vouchertype' => $voucher];
            return view('vouchers.edit', $data);
        }else{
            Session::flash('error_message', 'Comprobante no encontrado');
            return redirect()->back();
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param VoucherTypeRequest|Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(VoucherTypeRequest $request, $id)
    {
        if (!$this->user->hasAccess('vouchers.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if ($voucher = VoucherType::find($id)){
            $input = $request->except('_token');
            $input['updated_by'] = $this->user->id;
            try{
                if ($voucher->update($input)){
                    Session::flash('message', 'Comprobante actualizado correctamente');
                    return redirect('vouchers');
                }else{
                    Session::flash('error_message', 'Ocurrio un error al actualziar el registro');
                    return redirect('vouchers');
                }
            }catch (\Exception $e){
                \Log::error("Error on update voucherType - {$e->getMessage()}");
                Session::flash('error_message', 'Ocurrio un erro ral intentar eliminar el registro');
                return redirect('vouchers');
            }
        }else{
            Session::flash('error_message', 'Tipo de comprobante no encontrado');
            return redirect('vouchers');
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
        if (!$this->user->hasAccess('vouchers.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $error = true;
        $message = '';
        if ($voucher = VoucherType::find($id)){
            try{
                VoucherType::destroy($id);
                \Log::info("VoucherType destroy.{$voucher->id} ");
                $message = 'Tipo de comprobante eliminado';
                $error = false;
            }catch (\Exception $e){
                \Log::error("Error on delete voucherType - {$e->getMessage()}");
                $message = 'Ocurrio un erro ral intentar eliminar el registro';
            }
        }else{
            $message =  'Tipo de comprobante no encontrado';
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }
}