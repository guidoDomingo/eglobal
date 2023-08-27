<?php

namespace App\Http\Controllers\Promociones;

use Session;

use Illuminate\Http\Request;
use App\Models\BranchPromotion;
use App\Models\CampaignDetails;
use App\Models\PromotionCategory;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Models\BusinessPromotion;
use Illuminate\Support\Facades\Storage;


class BranchPromotionController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function index(Request $request)
    {
        if (!$this->user->hasAccess('branches_providers')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $name       = $request->get('name');
        $branches   = BranchPromotion::filterAndPaginate($name);
        $business   = BusinessPromotion::get();


        return view('promociones.branches_promotions.index', compact('branches', 'name','business'));
    }
   
    public function create()
    {
        if (!$this->user->hasAccess('branches_providers.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
    
        $business      = \DB::table('business')->pluck('description','id');
        $providers     = \DB::table('promotions_providers')->pluck('name','id');

        return view('promociones.branches_promotions.create', compact('business','providers'));
    }

    public function store(Request $request)
    {
        if (!$this->user->hasAccess('branches_providers.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $input = $request->all();
        \DB::beginTransaction();

        if($request->ajax())
        {
           //
        }else{
            try{
                if(!empty($input['custom_image'])){
                    $imagen         = $input['custom_image'];
                    $data_imagen    = json_decode($imagen);
                    $nombre_imagen  = $data_imagen->name;
                    $urlHost        = request()->getSchemeAndHttpHost();
                    Storage::disk('branches_promociones')->put($nombre_imagen,  base64_decode($data_imagen->data));
                    $input['custom_image'] = $urlHost.'/resources/images/branches_promotions/'.$nombre_imagen;
                }else{
                    $input['custom_image'] = '';
                }
                if ($branch_promotion  =  BranchPromotion::create($input+[
                    'start_time'    => date("H:i:s", strtotime($input['start_time'])),
                    'end_time'      => date("H:i:s", strtotime($input['end_time'])),
                ])){
                    \Log::info("Sucursal agregada correctamente");
                    \DB::commit();
                    return redirect('branches_providers')->with('guardar', 'ok');
                }
            }catch (\Exception $e){
                \DB::rollback();
                \Log::critical($e->getMessage());
                return redirect()->back()->withInput()->with('error', 'ok');
            }
        }
    }

    public function show($id)
    {
        
    }
   
    public function edit($id)
    {
        if (!$this->user->hasAccess('branches_providers.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if($branches = BranchPromotion::find($id)){
       
            $business      = \DB::table('business')->pluck('description','id');
            $providers     = \DB::table('promotions_providers')->pluck('name','id');
           
            if(!empty($branches)){
                $branch_start_time = date("h:i:s a",strtotime($branches->start_time));
                $branch_end_time = date("h:i:s a",strtotime($branches->end_time));
            }
           // dd($branches);

            $data = [
                'branchPromotion'   => $branches,     
                'business'          => $business, 
                'providers'         => $providers, 
                'start_time'        => $branch_start_time, 
                'end_time'          => $branch_end_time, 
            ];

            return view('promociones.branches_promotions.edit', $data);
        }else{
            Session::flash('error_message', 'Sucursal no encontrada.');
            return redirect('branches_providers');
        }

    }

    public function update(Request $request, $id)
    {
        if (!$this->user->hasAccess('branches_providers.add|edit')) 
        {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        \DB::beginTransaction();
        if ($branch = BranchPromotion::find($id)){
            $input = $request->all();
             try{
           
                if(!empty($input['custom_image'])){

                    $imagen         = $input['custom_image'];
                    $data_imagen    = json_decode($imagen);
                    $nombre_imagen  = $data_imagen->name;
                    $urlHost        = request()->getSchemeAndHttpHost();
                    $input['custom_image'] = $urlHost.'/resources/images/branches_promotions/'.$nombre_imagen;

                    if($branch->image != $input['custom_image'] && $branch->image != null){
                        if(file_exists(public_path().'/resources'.trim($branch->image))){
                            unlink(public_path().'/resources'.trim($branch->image));
                        }
                        Storage::disk('branches_promociones')->put($nombre_imagen,  base64_decode($data_imagen->data));
                    }else{
                        // unset($input['image']);
                        Storage::disk('branches_promociones')->put($nombre_imagen,  base64_decode($data_imagen->data));
                        $input['custom_image'] = $urlHost.'/resources/images/branches_promotions/'.$nombre_imagen;

                    }
                }
                $branch->fill($input);
                if($branch->update()){
                    \DB::commit();

                    Session::flash('message', 'Sucursal actualizada exitosamente');
                    return redirect('branches_providers')->with('actualizar','ok');
                }
            }catch (\Exception $e){
                \DB::rollback();
                \Log::error("Error updating sucursal: " . $e->getMessage());
                Session::flash('error_message','Error al intentar actualizar la sucursal');
                return redirect()->back()->withInput()->with('error', 'ok');

            }
        
        }
    }

    public function destroy($id)
    {
        if (!$this->user->hasAccess('branches_providers.delete')) 
        {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $message    = '';
        $error      = '';
        \Log::debug("Intentando elimiar sucursal ".$id);

        if ($branch = BranchPromotion::find($id))
        {
            try{
                \DB::beginTransaction();
                if (BranchPromotion::where('id',$id)->delete()){
                    $message  =  'Sucursal eliminada correctamente';
                    $error    = false;
                }
                \DB::commit();
            }catch (\Exception $e){
                    \DB::rollback();
                    \Log::error("Error deleting Sucursal: " . $e->getMessage());
                    $message  =  'Error al intentar eliminar la sucursal';
                    $error    = true;
                }

        
        }else{
            $message =  'Sucursal no encontrada';
            $error = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }
   
       

}
