<?php

namespace App\Http\Controllers;

use Session;
use Carbon\Carbon;
use App\Models\Atm;
use App\Models\Pos;
use App\Http\Requests;
use App\Models\Branch;

use App\Models\PosDeposits;
use Illuminate\Http\Request;
use App\Http\Requests\PosRequest;
use App\Http\Controllers\Controller;

class PosController extends Controller
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
        if (!$this->user->hasAccess('pos')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        //$pointsofsale = Pos::paginate(20);
        $name = $request->get('name');
        $pointsofsale = Pos::filterAndPaginate($name);
        return view('pos.index', compact('pointsofsale','name'));

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        if (!$this->user->hasAccess('pos.add')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $branches = Branch::pluck('description', 'id');
        // TODO get seller type fron ONDANET
        $sellerType['1'] = 'Testing Seller type';
        $data = [
            'branches' => $branches,
            'ondanet_seller_types' => $sellerType,
            'selected_seller_type' => null,
            'selected_branch' => null
        ];

        return view('pos.create', $data);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param PosRequest|Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(PosRequest $request)
    {
        if (!$this->user->hasAccess('pos.add') && !\Sentinel::getUser()->inRole('atms_v2.area_comercial')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if($request->ajax()){
            $respuesta = [];

            try{
                $input = $request->all();
                if ($branch = Branch::find($input['branch_id'])) {
                    $input['created_by'] = $this->user->id;
                    $input['seller_type'] = 0;
                    //if ($pos = Pos::create($input)) {
                    if ($pos = Pos::create([
                        'pos_code'      => $input['pos_code'],
                        'ondanet_code'  => $input['ondanet_code'],
                        'description'   => $input['description'],
                        'seller_type'   => $input['seller_type'],
                        'created_by'    => $input['created_by'],
                        'branch_id'     => $input['branch_id'],
                        'atm_id'        => $input['atm_id'],
                        'created_at'    => Carbon::now(),
                        'update_at'     => Carbon::now(),
                        'owner_id'      => $input['owner_id'] ])) {

                        \DB::table('atms')
                            ->where('id', $input['atm_id'])
                            ->update(['deposit_code' => $input['ondanet_code']]);

                        //PROGRESO DE CREACION - ABM V2
                        if ( $request->abm == 'v2'){
                            \DB::table('atms')
                            ->where('id', $input['atm_id'])
                            ->update([
                                'atm_status' => -6,
                            ]);
                            \Log::info("ABM Version 2, Paso 2 - POS. Estado de creacion: -6");
                        }else{
                            \DB::table('atms')
                            ->where('id', $input['atm_id'])
                            ->update([
                                'atm_status' => -2,
                            ]);
                            \Log::info("ABM Version 1, Paso 2 - POS. Estado de creacion: -2");
                        }

                        $data = [];
                        $data['id'] = $pos->id;
                        $data['branch_id'] = $input['branch_id'];

                        \Log::info("Nuevo punto de venta creado");
                        $respuesta['mensaje']   = 'Punto de venta creado correctamente';
                        $respuesta['tipo']      = 'success';
                        $respuesta['data']      = $data;
                        $respuesta['url']       = route('pos.update',[$pos->id]);
               
                        return $respuesta;
                    } else {
                       \Log::critical($e->getMessage());
                        $respuesta['mensaje']   = 'Error al crear atm';
                        $respuesta['tipo']      = 'error';
                        return $respuesta;
                    }
                } else {
                    \Log::critical($e->getMessage());
                    $respuesta['mensaje']   = 'Sucursal no encontrada';
                    $respuesta['tipo']      = 'error';
                    return $respuesta;
                }
            }catch (\Exception $e){

                \Log::critical($e->getMessage());
                $respuesta['mensaje']   = 'Error al crear atm';
                $respuesta['tipo']      = 'error';
                return $respuesta;
            }
        }else{
            if (!$this->user->hasAccess('pos.add') || $this->user->hasAccess('pos_v2.add')) {
                        \Log::error('Unauthorized access attempt',
                            ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
                        Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
                        return redirect('/');
                    }

            try{
                $input = $request->all();

                // dd($input);
                /*if (!$pos = Pos::find($input['pos_code'])) {*/
                    if ($branch = Branch::find($input['branch_id'])) {
                        $input['created_by'] = $this->user->id;
                        $input['seller_type'] = 0;
                        if (Pos::create($input)) {
                            Session::flash('message', 'Punto de venta creado correctamente');
                            return redirect('pos');
                        } else {
                            Session::flash('error_message', 'Error al intentar crear un nuevo punto');
                            return redirect('pos');
                        }
                    } else {
                        Session::flash('error_message', 'Sucursal no encontrada');
                        return redirect('pos');
                    }
                    /*} else {
                        Session::flash("error_message", "Ya existe un punto de venta con el mismo codigo {$input['pos_code']}");
                        return redirect('pos');
                    }*/
            }catch (\Exception $e){

                \Log::notice($e);
                Session::flash("error_message", "Ha ocurrido un error al registrar PDV");
                return redirect('pos');
            }
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
        if (!$this->user->hasAccess('pos.edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if ($pos = Pos::find($id)) {
            $branches = Branch::pluck('description', 'id');
            $deposits = PosDeposits::where('point_of_sale_id', $id)->get();

            // TODO retrive salesman types from ondanet
            $ondanet_seller_response['success'] = false;
            $seller_types['1'] = 'Testing Seller type';
            if ($ondanet_seller_response['success'] == true) {
                $seller_types = [];
                $ondanet_seller_types = $ondanet_seller_response['salesman_types'];
                foreach ($ondanet_seller_types as $value) {
                    $seller_types[$value['salesman_type_code']] = $value['description'];
                }
            }
            $selected_seller_type = $pos->seller_type;
            $selected_branch = $pos->branch_id;

            $data = [
                'branches' => $branches,
                'ondanet_seller_types' => $seller_types,
                'pointofsale' => $pos,
                'selected_seller_type' => $selected_seller_type,
                'selected_branch' => $selected_branch,
            ];
            if (count($deposits) > 0) {
                $data['deposits'] = $deposits;
            }

            return view('pos.edit', $data);
        } else {
            Session::flash('error_message', 'Punto de venta no encontrasdo');
            return redirect('pos');
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param PosRequest|Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(PosRequest $request, $id)
    {

        if($request->ajax()){
            $respuesta = [];
            if (!$this->user->hasAccess('pos.edit') && !\Sentinel::getUser()->inRole('atms_v2.area_comercial')) {

                \Log::error('Unauthorized access attempt',
                    ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
                $respuesta['mensaje'] = 'No tiene los permisos para realizar esta operacion';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }
            try{
                $input = $request->all();
                if ($pos = Pos::find($id)) {
                    $input = $request->all();
                    $input['updated_by'] = $this->user->id;
                    $pos->fill($input);
                    try{
                        if ($pos->save()) {
                            \DB::table('atms')
                                ->where('id', $input['atm_id'])
                                ->update(['deposit_code' => $input['ondanet_code']]);
                                

                        // PROGRESO DE CREACION - ABM V2
                        // if ( $request->abm == 'v2'){
                        //     \DB::table('atms')
                        //     ->where('id', $input['atm_id'])
                        //     ->update([
                        //         'atm_status' => -6,
                        //     ]);
                        //     \Log::info("ABM Version 2, Paso 2 - POS. Estado de actualizacion: -6");
                        // }else{
                        //     \DB::table('atms')
                        //     ->where('id', $input['atm_id'])
                        //     ->update([
                        //         'atm_status' => -2,
                        //     ]);
                        //     \Log::info("ABM Version 1, Paso 2 - POS. Estado de actualizacion: -2");
                        // }

                            $data = [];
                            $data['id'] = $pos->id;
                            
                            \Log::info("Punto de venta actualizado correctamente");
                            $respuesta['mensaje'] = 'Punto de venta actualizado correctamente';
                            $respuesta['tipo'] = 'success';
                            $respuesta['data'] = $data;
                            $respuesta['url'] = route('pos.update',[$id]);
                            return $respuesta;
                        } else {
                            \Log::critical("Error al actualizar punto de venta");
                            $respuesta['mensaje'] = 'No se ha podido realizar la operacion';
                            $respuesta['tipo'] = 'error';
                            $respuesta['data'] = $data;
                            $respuesta['url'] = route('pos.update',[$id]);
                            return $respuesta;
                        }    
                    }catch (\Exception $e){
                        \Log::critical($e);
                        $respuesta['mensaje'] = 'No se ha podido realizar la operacion';
                        $respuesta['tipo'] = 'error';
                        $respuesta['url'] = route('pos.update',[$id]);
                        return $respuesta;
                    }
                    
                } else {
                    Session::flash('error_message', 'Punto de venta no encontrado');
                    \Log::warning('Error attempting to update pos - Pos not found');
                    return redirect('pos');
                }
                if ($branch = Branch::find($input['branch_id'])) {
                    $input['created_by'] = $this->user->id;
                    $input['seller_type'] = 0;
                    if ($pos = Pos::create($input)) {
                        $data = [];
                        $data['id'] = $pos->id;
                        
                        \Log::info("Punto de venta actualizado correctamente");
                        $respuesta['mensaje'] = 'Punto de venta actualizado correctamente';
                        $respuesta['tipo'] = 'success';
                        $respuesta['data'] = $data;
                        $respuesta['url'] = route('pos.update',[$pos->id]);
                        return $respuesta;
                    } else {
                       \Log::critical($e->getMessage());
                        $respuesta['mensaje'] = 'Error al crear atm';
                        $respuesta['tipo'] = 'error';
                        return $respuesta;
                    }
                } else {
                    \Log::critical($e->getMessage());
                    $respuesta['mensaje'] = 'Sucursal no encontrada';
                    $respuesta['tipo'] = 'error';
                    return $respuesta;
                }
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Error al crear atm';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }
        }else{
            if (!$this->user->hasAccess('pos.edit')) {
                \Log::error('Unauthorized access attempt',
                    ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
                Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
                return redirect('/');
            }

            if ($pos = Pos::find($id)) {
                $input = $request->all();
                $input['updated_by'] = $this->user->id;
                $pos->fill($input);
                try{
                    if ($pos->save()) {
                        \DB::table('atms')
                            ->where('id', $pos->atm_id)
                            ->update(['deposit_code' => $input['ondanet_code']]);
                        Session::flash('message', 'Punto de venta modificado exitosamente');
                        return redirect('pos');
                    } else {
                        Session::flash('error_message', 'Hubo un problema al actualizar el registro, 
                    intenten nuevamente mas tarde');
                        return redirect()->back();
                    }    
                }catch (\Exception $e){
                    Session::flash('error_message', 'Ocurrio un error al actualizar el Punto de venta');
                    \Log::warning('Error attempting to update pos - Pos not found'. $e);
                    return redirect()->back();
                }
                
            } else {
                Session::flash('error_message', 'Punto de venta no encontrado');
                \Log::warning('Error attempting to update pos - Pos not found');
                return redirect('pos');
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
        if (!$this->user->hasAccess('pos.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $message = '';
        $error = true;
        if ($pos = Pos::find($id)) {
            if (is_null($pos->atm_id) || empty($pos->atm_id) || $pos->atm_id == 0) {
                if (Pos::destroy($id)) {
                    $message = 'Punto de venta eliminado exitosamente';
                    $error = false;
                } else {
                    $message = 'Ocurrio un error al intentar eliminar el cajero';
                    \Log::warning('Error attempting to destroy pos');
                }
            } else {
                $message = 'Punto de venta cuenta con un cajero asignado';
                \Log::warning('Error attempting to destroy pos - Pos has an active ATM');
            }
        } else {
            $message = 'Punto de venta no encontrasdo';
            \Log::warning('Error attempting to destroy pos - Pos not found');
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }

    public function showAssign($id)
    {
        if (!$this->user->hasAccess('pos.assign.atm')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if ($pos = Pos::find($id)) {
            if ($pos->branch_id) {
                if ($atm = Atm::where('owner_id', '=', $pos->branch->owner->id)->get()) {
                    if (count($atm) > 0) {
                        $atms = $atm->pluck('name_code', 'id');
                        return view('pos.assign', compact('atms', 'pos'));
                    } else {
                        Session::flash('error_message', 'No existen cajeros asignados en su red');
                        return redirect()->back();
                    }
                }
            } else {
                Session::flash('error_message', 'No tiene sucursales asignadas');
                return redirect()->back();
            }
        } else {
            Session::flash('error_message', 'Punto de venta no encontrado');
            return redirect()->back();
        }
        return view('pos.assign');
    }

    public function assignAtm($id, Request $request)
    {

        if (!$this->user->hasAccess('pos.assign.atm')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if ($request->get('atm_id')) {
            if ($pos = Pos::find($id)) {
                if ($pos->branch_id) {
                    if ($atm = Atm::find($request->get('atm_id'))) {
                        $pos->fill(['atm_id' => $request->get('atm_id')]);
                        try{
                            if ($pos->save()) {
                                \DB::table('atms')
                                    ->where('id', $request->get('atm_id'))
                                    ->update(['deposit_code' => $pos->deposit_code]);
                                Session::flash('message', 'Punto de venta actualizado exitosamente');
                                return redirect('pos');
                            } else {
                                Session::flash('atm_form_error_message', 'Ocurrio un error al intentar guardar el registro');
                                return redirect()->back();
                            }
                        }catch (\Exception $e){
                            \Log::error("Error assigning atm to pos {$e->getMessage()}");
                            Session::flash('atm_form_error_message', 'Ocurrio un error al intentar guardar el registro');
                            return redirect()->back();
                        }

                    } else {
                        Session::flash('atm_form_error_message', 'Cajero no encontrado');
                        return redirect()->back();
                    }
                } else {
                    Session::flash('atm_form_error_message', 'No tiene sucursales asignadas');
                    return redirect()->back();
                }
            } else {
                Session::flash('atm_form_error_message', 'Punto de venta no encontrado');
                return redirect()->back();
            }
        } else {
            if ($pos = Pos::find($id)) {
                $pos->fill(['atm_id' => null]);
                try{
                    if ($pos->save()) {
                        Session::flash('message', 'Punto de venta actualizado exitosamente');
                        return redirect('pos');
                    } else {
                        Session::flash('atm_form_error_message', 'Ocurrio un error al intentar guardar el registro');
                        return redirect()->back();
                    }
                }catch (\Exception $e){
                    \Log::error("Error assigning atm to pos {$e->getMessage()}");
                    Session::flash('atm_form_error_message', 'Ocurrio un error al intentar guardar el registro');
                    return redirect()->back();
                }
            } else {
                Session::flash('atm_form_error_message', 'Punto de venta no encontrado');
                return redirect()->back();
            }

        }
    }
}
