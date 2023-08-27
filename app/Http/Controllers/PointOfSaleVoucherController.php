<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use App\Http\Requests\StorePointOfSaleVoucherRequest;
use App\Http\Requests\UpdatePointOfSaleVoucherRequest;
use App\Models\Pos;
use App\Models\PosSaleVoucher;
use App\Models\PosSaleVoucherType;
use Carbon\Carbon;

use Session;

use function GuzzleHttp\json_decode;

class PointOfSaleVoucherController extends Controller
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
            $voucherType = PosSaleVoucher::filterAndPaginate($posId);
            return view('posvouchers.index', compact('voucherType', 'name', 'posId'));
        } else {
            $message = 'Punto de venta especificado no existe!';
            Session::flash('error_message', $message);
            return redirect()->route('pos.index');
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

        $voucherTypes = PosSaleVoucherType::where('point_of_sale_id', $posId)->with('VoucherType')->get()->pluck('VoucherType.description', 'id');

        $data = [
            'voucherTypes' => $voucherTypes,
            'posId' => $posId,
        ];
        return view('posvouchers.create', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store($posId, StorePointOfSaleVoucherRequest $request)
    {
        if (!$this->user->hasAccess('vouchers.add|edit') && !\Sentinel::getUser()->inRole('atms_v2.area_contabilidad')) {

            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $this->user = \Sentinel::getUser()->id;
        $posVoucherType = PosSaleVoucherType::find($request->pos_voucher_type_id);
        $expedition_point = $posVoucherType->expedition_point;

        $PosVoucherType = new PosSaleVoucher;
        $PosVoucherType->created_by = $this->user;
        $PosVoucherType->pos_voucher_type_id = $request->pos_voucher_type_id;
        $PosVoucherType->stamping = $request->stamping;
        $PosVoucherType->voucher_code = $expedition_point;
        $PosVoucherType->last_used_number = 0;
        $PosVoucherType->from_number = $request->from_number;
        $PosVoucherType->to_number = $request->to_number;
        $PosVoucherType->valid_from = Carbon::createFromFormat('d/m/Y', $request->valid_from)->toDateString();
        $PosVoucherType->valid_until = Carbon::createFromFormat('d/m/Y', $request->valid_until)->toDateString();
        $PosVoucherType->point_of_sale_id = $posId;

        if($request->ajax()){
            if ($PosVoucherType->save()) {
                $pos = \DB::table('points_of_sale')
                    ->where('id', $posId)
                    ->first();
                \Log::info("Nuevo comprobante creado correctamente");

                //PROGRESO DE CREACION - ABM V2
                if ( $request->abm == 'v2'){
                    \DB::table('atms')
                    ->where('id', $pos->atm_id)
                    ->update(['atm_status' => -10]);
                    
                    \Log::info("ABM Version 2, Paso 6 - CONTABILIDAD. Estado de creacion: -10");
                }else{
                    \DB::table('atms')
                    ->where('id', $pos->atm_id)
                    ->update([
                        'atm_status' => -3,
                    ]);
                    \Log::info("ABM Version 1, Paso 3 - CONTABILIDAD. Estado de creacion: -3");
                }

                    
                $data = [];
                $data['id'] = $PosVoucherType->id;
                
                $respuesta['mensaje'] = 'Nuevo comprobante creado correctamente';
                $respuesta['tipo'] = 'success';
                $respuesta['data'] = $data;
                $respuesta['url'] = route('pointsofsale.vouchers.update',[$posId,$PosVoucherType->id]);
                return $respuesta;
            }else{

                $message = 'No se pudo insertar el registro';
                \Log::critical($message);
                $respuesta['mensaje'] = 'No se ha podido realizar la operación';
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

            if ($PosVoucherType->save()) {
                $message = 'Agregado correctamente';
                Session::flash('message', $message);
                return redirect()->route('pointsofsale.vouchers.index', $posId);
            }else{
                $message = 'No se pudo insertar el registro';
                Session::flash('error_message', $message);
                return redirect()->route('pointsofsale.vouchers.index', $posId);
            }
        }




    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($posid, $posvoucherid)
    {
        if (!$this->user->hasAccess('vouchers.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $posVoucherType = PosSaleVoucher::find($posvoucherid);
        $pos = Pos::find($posid);
        $posVoucher = PosSaleVoucher::with('voucherType')->find($posvoucherid);
        $voucherTypes = PosSaleVoucherType::where('point_of_sale_id', $posid)->where('voucher_type_id', $posVoucher->voucherType->voucher_type_id)->with('VoucherType')->first();
        $posVoucherTypeDes = $voucherTypes->VoucherType->description;
        $data = [
            'posVoucherType' => $posVoucherType,
            'posVoucher' => $posVoucher,
            'posId' => $posid,
            'pos' => $pos,
            'posVoucherTypeDesc' => $posVoucherTypeDes,
        ];

        return view('posvouchers.show', $data);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id, $pos_voucher_type_id)
    {
        if (!$this->user->hasAccess('vouchers.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $voucherTypes = PosSaleVoucherType::where('point_of_sale_id', $id)->with('VoucherType')->get()->pluck('VoucherType.description', 'id');
        $posVoucher = PosSaleVoucher::with('voucherType')->find($pos_voucher_type_id);
        $pos    = Pos::find($id);


        //dd(strtotime($posVoucher->valid_from));
        $posVoucher->valid_from = date("d/m/Y", strtotime($posVoucher->valid_from));
        $posVoucher->valid_until = date("d/m/Y", strtotime($posVoucher->valid_until));
        $data = [
            'voucherTypes' => $voucherTypes,
            'posVoucher' => $posVoucher,
            'posId' => $id,
            'pos' => $pos,
        ];
        return view('posvouchers.edit', $data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update($posid, $posvoucherid, UpdatePointOfSaleVoucherRequest $request)
    {
        if($request->ajax()){
            if (!$this->user->hasAccess('vouchers.add|edit') && !\Sentinel::getUser()->inRole('atms_v2.area_contabilidad')) {
                \Log::error('Unauthorized access attempt',
                    ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
                $respuesta['mensaje'] = 'No tiene los permisos para realizar esta operacion';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }

            $this->user = \Sentinel::getUser()->id;
            try{
                $PosVoucher = PosSaleVoucher::find($posvoucherid);
                $PosVoucher->updated_by = $this->user;
                $PosVoucher->stamping = $request->stamping;
                $PosVoucher->from_number = $request->from_number;
                $PosVoucher->to_number = $request->to_number;
                $PosVoucher->valid_from = Carbon::createFromFormat('d/m/Y', $request->valid_from)->toDateString();
                $PosVoucher->valid_until = Carbon::createFromFormat('d/m/Y', $request->valid_until)->toDateString();
                $PosVoucher->save();
        
                // //PROGRESO DE CREACION - ABM V2
                // if ( $request->abm == 'v2'){
                //     \DB::table('atms')
                //     ->where('id', $request->atm_id)
                //     ->update([
                //         'atm_status' => -10,
                //     ]);
                //     \Log::info("ABM Version 2, Paso 6 - CONTABILIDAD. Estado de actualizacion: -10");
                // }else{
                //     \DB::table('atms')
                //     ->where('id', $request->atm_id)
                //     ->update([
                //         'atm_status' => -3,
                //     ]);
                //     \Log::info("ABM Version 1, Paso 3 - CONTABILIDAD. Estado de actualizacion: -3");
                // }

                $data = [];
                $data['id'] = $PosVoucher->id;
                
                \Log::info("Comprobante actualizado correctamente");
                $respuesta['mensaje'] = 'Comprobante actualizado correctamente';
                $respuesta['tipo'] = 'success';
                $respuesta['data'] = $data;
                $respuesta['url'] = route('pointsofsale.vouchers.update',[$posid,$PosVoucher->id]);
                return $respuesta;

            }catch (\Exception $e){
                \Log::error($e);
                $respuesta['mensaje'] = 'No se pudo realizar la operacion';
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

            $this->user = \Sentinel::getUser()->id;
            try{
                $PosVoucher = PosSaleVoucher::find($posvoucherid);
                $PosVoucher->updated_by = $this->user;
                $PosVoucher->stamping = $request->stamping;
                $PosVoucher->from_number = $request->from_number;
                $PosVoucher->to_number = $request->to_number;
                $PosVoucher->valid_from = Carbon::createFromFormat('d/m/Y', $request->valid_from)->toDateString();
                $PosVoucher->valid_until = Carbon::createFromFormat('d/m/Y', $request->valid_until)->toDateString();
                $PosVoucher->save();

                $message = 'Actualizado correctamente';
                Session::flash('message', $message);
                return redirect()->route('pointsofsale.vouchers.index', $posid);


            }catch (\Exception $e){
                Session::flash('error_message', 'Ocurrio un error al ingresar el comprobante del Punto de venta');
                \Log::warning('Error attempting to update posvoucher - Pos not found'. $e);
                return redirect()->back();
            }
        }


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($posid, $posvoucherid, Request $request)
    {
        if (!$this->user->hasAccess('vouchers.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $posSaleVoucher = PosSaleVoucher::find($posvoucherid);
        if($posSaleVoucher == null){
            $error = true;
            $message = "No se encontró comprobante asignado, no se pudo eliminar";
        }else{
            if ($posSaleVoucher->delete()) {
                $error = false;
                $message = 'Comprobante asignado eliminado correctamente';
            }else{
                $error = false;
                $message = 'No se pudo eliminar comprobante asignado a PDV';
            }
        }

        if ($request->ajax()) {
            return response()->json([
                'error' => $error,
                'message' => $message,
            ]);
        }else{
            Session::flash('message', $message);
            return redirect()->route('pointsofsale.voucher.index', $posid);
        }
    }
}
