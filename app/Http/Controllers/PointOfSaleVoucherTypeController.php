<?php

namespace App\Http\Controllers;

use App\Models\PosSaleVoucherType;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePointOfSaleVoucherTypeRequest;
use App\Http\Requests\UpdatePointOfSaleVoucherTypeRequest;
use App\Models\Pos;
use App\Models\PointOfSaleVoucherType;
use App\Models\VoucherType;

use Session;

class PointOfSaleVoucherTypeController extends Controller
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
    public function index($posId, Request $request)
    {
        if (!$this->user->hasAccess('vouchers')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $name = $request->get('name');
        if (Pos::find($posId)) {
            $voucherType = PointOfSaleVoucherType::filterAndPaginate($posId, $name);
            return view('posvouchertypes.index', compact('voucherType', 'name', 'posId'));
        } else {
            $message = 'Punto de venta especificado no existe!';
            Session::flash('error_message', $message);
            return redirect()->route('admin.pointsofsale.index');
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($posId)
    {
        if (!$this->user->hasAccess('vouchers.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }


        $voucherTypes = VoucherType::pluck('description', 'id');
        $data = [
            'voucherTypes' => $voucherTypes,
            'posId' => $posId,
        ];
        return view('posvouchertypes.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($posId, StorePointOfSaleVoucherTypeRequest $request)
    {
        // if (!$this->user->hasAccess('vouchers.add|edit')) {
        //     \Log::error('Unauthorized access attempt',
        //         ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
        //     Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
        //     return redirect('/');
        // }
 
        if($request->ajax()){
            $respuesta = [];
            try{
                $this->user = \Sentinel::getUser()->id;
                $PosVoucherType = new PointOfSaleVoucherType;
                $PosVoucherType->created_by = $this->user;
                $PosVoucherType->expedition_point = $request->expedition_point;
                $PosVoucherType->voucher_type_id = $request->voucher_type_id;
                $PosVoucherType->point_of_sale_id = $posId;
                $PosVoucherType->save();
                $message = 'Agregado correctamente';

                $voucherTypes = \DB::table('point_of_sale_voucher_types')
                    ->select(\DB::raw("point_of_sale_voucher_types.id, CONCAT(voucher_types.description, ' ', point_of_sale_voucher_types.expedition_point) as description"))
                    ->join('voucher_types','voucher_types.id','=','point_of_sale_voucher_types.voucher_type_id')
                    ->whereRaw("point_of_sale_voucher_types.id = ".$PosVoucherType->id)
                    ->get();
                
                $data = [];
                $data['id'] = $PosVoucherType->id;
                $data['description'] = $voucherTypes[0]->description;

                \Log::info("Nuevo tipo de comprobante creado");
                $respuesta['mensaje'] = $message;
                $respuesta['tipo'] = 'success';
                $respuesta['data'] = $data;
                return $respuesta;

            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Ocurrio un error al ingresar el comprobante del Punto de venta';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }
        }else{
            if (!$this->user->hasAccess('vouchers.add|edit')) {
                        \Log::error('Unauthorized access attempt',
                            ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
                        Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
                        return redirect('/');
                    }

            try{
                $this->user = \Sentinel::getUser()->id;
                $PosVoucherType = new PointOfSaleVoucherType;
                $PosVoucherType->created_by = $this->user;
                $PosVoucherType->expedition_point = $request->expedition_point;
                $PosVoucherType->voucher_type_id = $request->voucher_type_id;
                $PosVoucherType->point_of_sale_id = $posId;
                $PosVoucherType->save();
                $message = 'Agregado correctamente';
                Session::flash('message', $message);
                return redirect()->route('pointsofsale.vouchertypes.index', $posId);

            }catch (\Exception $e){
                Session::flash('error_message', 'Ocurrio un error al ingresar el comprobante del Punto de venta');
                \Log::warning('Error attempting to update posvoucherType - Pos not found'. $e);
                return redirect()->back();
            }
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
    public function edit($posid, $posvouchertypeid)
    {
        if (!$this->user->hasAccess('vouchers.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $voucherType = VoucherType::where('id', $posvouchertypeid)->get();
        $posVoucherType = PointOfSaleVoucherType::find($posvouchertypeid);

        $voucherTypes = VoucherType::pluck('description', 'id');

        $data = [
            'voucherType' => $voucherType[0],
            'voucherTypes' => $voucherTypes,
            'posVoucherType' => $posVoucherType,
            'posId' => $posid,
        ];

        return view('posvouchertypes.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($posId,$posVoucherTypeId, UpdatePointOfSaleVoucherTypeRequest $request)
    {
        if (!$this->user->hasAccess('vouchers.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        try{
            $this->user = \Sentinel::getUser()->id;
            $PosVoucherType = PosSaleVoucherType::find($posVoucherTypeId);
            $PosVoucherType->updated_by = $this->user;
            $PosVoucherType->expedition_point = $request->expedition_point;
            $PosVoucherType->voucher_type_id = $request->voucher_type_id;
            $PosVoucherType->save();
            $message = 'Actualizado correctamente';
            Session::flash('message', $message);
            return redirect()->back();
        }catch (\Exception $e){
            Session::flash('error_message', 'Ocurrio un error al ingresar el comprobante del Punto de venta');
            \Log::warning('Error attempting to update posvoucherType - Pos not found'. $e);
            return redirect()->back();
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($posid, $posVoucherTypeId, Request $request)
    {

        if (!$this->user->hasAccess('vouchers.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            return response()->json([
                'error' => true,
                'message' => "No tiene los permisos para realizar esta operacion",
            ]);
        }

        $PosVoucherType = PosSaleVoucherType::find($posVoucherTypeId);
        if($PosVoucherType == null){
            $error = true;
            $message = "No se encontro tipo de comprobante no se pudo eliminar";


        }else{
            if ($PosVoucherType->delete()) {
                $error = false;
                $message = 'Tipo de comprobante eliminado correctamente';
            }else{
                $error = false;
                $message = 'No se pudo eliminar tipo de comprobante';
            }

        }

        if ($request->ajax()) {
            return response()->json([
                'error' => $error,
                'message' => $message,
            ]);
        }else{
            Session::flash('message', $message);
            return redirect()->route('pointsofsale.vouchertypes.index', $posid);
        }

    }
}
