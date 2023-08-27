<?php

namespace App\Http\Controllers;

use Session;

use Carbon\Carbon;
use App\Models\Content;
use App\Models\Campaign;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\PromotionVoucher;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;


class PromotionVouchersController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function index(Request $request)
    {
        if (!$this->user->hasAccess('promotions_vouchers')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input          = $request->all();
        $campaign_id    = $input['campaign_id'];

        if(isset($input['name']) && $input['name'] <> '' ){
            $name           = $request->get('name');
            $vouchers       = PromotionVoucher::filterAndPaginate($name);
            $campaigns      = Campaign::filterAndPaginate($name);

        }else{
            $name           = $request->get('name');
            $vouchers       = PromotionVoucher::where('campaigns_id', '=',$campaign_id)->orderby('transaction_id', 'ASC')->paginate(20);        
        }
        $transactions   = \DB::table('transactions')->get();

        return view('promotion_vouchers.show', compact('vouchers', 'name','campaigns','campaign_id','transactions'));
    }

   
    public function create($campaignId)
    {
        if (!$this->user->hasAccess('promotions_vouchers.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $campaigns      = \DB::table('campaigns')->orderBy('id','asc')->pluck('name','id');
        $campaigns_id   = null;

        return view('promotion_vouchers.create',compact('campaigns_id', 'campaigns','campaignId'));
    }

    public function store(Request $request)
    {
        if (!$this->user->hasAccess('promotions_vouchers.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        \DB::beginTransaction();
        $input              = $request->all();
        $campaigns_id       = $input['campaigns_id'];
        $name               = $input['name'];
        $description        = $input['description'];
        $image              = isset($input['image'])?$input['image']:NULL;
        $cantidad           = (int)$input['cantidad'];

        try{
            $codigo = Str::random(8);
            for($i = 0; $i < $cantidad; ++$i) {

                if(PromotionVoucher::where('coupon_code', '=',$codigo)->exists()){
                    $codigo = Str::random(8);
                    \Log::info('cupon duplicado');

                }else{
                    DB::table('promotions_contents')->insert(
                        ['campaigns_id' => $campaigns_id, 
                        'coupon_code'   => Str::random(8),
                        'transaction_id'=> NULL,
                        'status'        => 0,
                        'created_at'    => Carbon::now(),
                        'updated_at'    => Carbon::now(),
                        'name'          => $name,
                        'description'   => $description,
                        'image'         => $image 
                        ]
                    );
                    \Log::info('voucher generado');
                }
                $codigo = Str::random(8);
            }

            \DB::commit();
            Session::flash('message', 'Vouchers generados correctamente');
            // return redirect('promotions_vouchers');
            return redirect()->route('promotions_vouchers.show',$campaigns_id);

        }catch (\Exception $e){
            \DB::rollback();
            \Log::critical($e->getMessage());
            Session::flash('error_message', 'Error al crear generar los vouchers');
            return redirect()->back()->with('error', 'Error al generar los vouchers');
        }
        
    }

    public function show(Request $request, $id)
    {
         if (!$this->user->hasAccess('promotions_vouchers.show')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
   
        $campaign_id     = $id;
        $name           = $request->get('name');
        $vouchers       = PromotionVoucher::where('campaigns_id', '=',$campaign_id)
        //->select(['campaigns_id', 'coupon_code', 'transaction_id', 'status','name','description','image'])
        ->orderby('transaction_id', 'ASC')->get();
        //$transactions   = \DB::table('transactions')->get();

        return view('promotion_vouchers.show', compact('vouchers','name','campaign_id'));
    }

   
    public function edit($id)
    {
        if (!$this->user->hasAccess('promotions_vouchers.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if($contenido = Content::find($id)){
            $data = [
                'content' => $contenido,
            ];
            return view('contenidos.edit', $data);
        }else{
            Session::flash('error_message', 'Voucher no encontrado.');
            return redirect('promotions_vouchers');
        }

    }

    public function update(Request $request, $id)
    {
        if (!$this->user->hasAccess('promotions_vouchers.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if ($content = Content::find($id)){
            $input = $request->all();
            try{
                if(!empty($input['image'])){
                    $imagen = $input['image'];
                    $data_imagen = json_decode($imagen);
                    $nombre_imagen = $data_imagen->name;
                    $urlHost = request()->getSchemeAndHttpHost();
                    $input['image'] = $urlHost.'/resources/1/images/contents/'.$nombre_imagen;
                    if($content->image != $input['image']){
                        if(file_exists(public_path().'/resources'.trim($content->image))){
                            unlink(public_path().'/resources'.trim($content->image));
                        }
                        Storage::disk('contenidos')->put($nombre_imagen,  base64_decode($data_imagen->data));
                    }else{
                        unset($input['image']);
                    }
                }

                $content->fill($input);
                if($content->update()){
                    Session::flash('message', 'Contenido actualizo exitosamente');
                    return redirect('promotions_vouchers');
                }
            }catch (\Exception $e){
                \Log::error("Error updating content: " . $e->getMessage());
                Session::flash('error_message','Error al intentar actualizar el contenido');
                return redirect('promotions_vouchers');
            }
        }else{
            \Log::warning("Content not found");
            Session::flash('error_message', 'Contenido no encontrado');
            return redirect('promotions_vouchers');
        }

    }


    public function destroy($id)
    {
        if (!$this->user->hasAccess('promotions_vouchers.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $message = '';
        $error = '';
        \Log::debug("Intentando elimiar contenido ".$id);
        if ($content = Content::find($id)){
            try{
               
                if (Content::where('id',$id)->delete()){
                    $message =  'Contenido eliminado correctamente';
                    $error = false;
                }
            }catch (\Exception $e){
                \Log::error("Error deleting content: " . $e->getMessage());
                $message =  'Error al intentar eliminar el contenido';
                $error = true;
            }
        }else{
            $message =  'Contenido no encontrado';
            $error = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }

    public function import($campaignId)
    {
        if (!$this->user->hasAccess('promotions_vouchers.import')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        return view('promotion_vouchers.import', compact('campaignId'));
    }

    public function store_import(Request $request, $campaignId)
    {
        if (!$this->user->hasAccess('promotions_vouchers.import')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();

        try {
            $this->validate($request, ['select_file'  => 'required|mimes:xls,xlsx']);
            $path       = $request->file('select_file')->getRealPath();
            $vouchers   = Excel::load($path)->get();
            
            if ($vouchers->count() > 0) {
                foreach ($vouchers as $voucher) {
                    if(PromotionVoucher::where('coupon_code', '=',$voucher->voucher)->exists()){
                        Session::flash('error_message', 'Códigos de vouchers duplicados, Favor verificar.');
                        \Log::info('Códigos de vouchers duplicados');
                        return redirect()->route('promotions_vouchers.show',$campaignId)->with('error', 'Error general al importar el listado.');
                    }else{
                        $insert_data[] = array(
                            'campaigns_id'      => $campaignId,
                            'coupon_code'       => $voucher->voucher,
                            'transaction_id'    => NULL,
                            'status'            => 0,
                            'created_at'        => (string)Carbon::now(),
                            'updated_at'        => (string)Carbon::now(),
                            'name'              => (string)$voucher->name,
                            'description'       => (string)$voucher->description,
                            'image'             => NULL
                        );
                    }
                }
                try{
                    foreach ($insert_data as $insert) {
                        if (!empty($insert)) {
                            \DB::table('promotions_contents')->insert($insert);
                        }
                    }
                    \DB::commit();
                    \Log::info("Fila voucher ingresada correctamente.");
                    Session::flash('message', 'Vouchers importados correctamente.');
                     return redirect()->route('promotions_vouchers.show',$campaignId)->with('error', 'Error al importar el listado de vouchers.');
                }catch (\Exception $e){
                    \DB::rollback();
                    \Log::critical($e->getMessage());
                    Session::flash('error_message', 'No se ha podido importar el listado, Favor verificar.');
                    return redirect()->back()->with('error', 'Error al importar el listado de vouchers.');
                }
            }
        } catch (\Exception $e) {
            \Log::critical($e);
            Session::flash('error_message', 'Error al importar el listado de vouchers');
            return redirect()->back()->with('error', 'Error al importar listado de vouchers');
        }
    }
   

}
