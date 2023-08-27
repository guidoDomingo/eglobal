<?php

namespace App\Http\Controllers;

use Session;

use Carbon\Carbon;
use App\Http\Requests;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Branch;
use App\Models\InternetServiceContract;

class InternetServiceContractController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function index(Request $request)
    {
        //
    }

    public function create()
    {
        //
    }
   
    public function store(Request $request)
    {
        // if (!$this->user->hasAccess('')) {
        //     \Log::error('Unauthorized access attempt',
        //         ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
        //     Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
        //     return redirect('/');
        // }
        $input = $request->all();
      

        $internetService = new InternetServiceContract();
        $internetService->isp_id                   = $input['isp_id'];
        $internetService->contract_cod             = $input['contract_cod'];
        //$internetService->date_init                = date("Y-m-d h:i:s", strtotime($input['date_init']));
        //$internetService->date_end                 = date("Y-m-d h:i:s", strtotime($input['date_end']));
        $internetService->date_init                = Carbon::createFromFormat('d/m/Y', $request->date_init)->toDateString();
        $internetService->date_end                 = Carbon::createFromFormat('d/m/Y', $request->date_end)->toDateString();
        $internetService->status                   = $input['status'];
        $internetService->created_at               = Carbon::now();
        $internetService->updated_at               = Carbon::now();
        $internetService->isp_acount_number        = $input['isp_acount_number'];          
        $internetService->created_by               = $this->user->id;
       
        $branch_id =  $input['branch_id'];

        if($request->ajax()){
            $respuesta = [];
            try{
                if ($internetService->save()) {
            
                    $data = [];
                    $data['id'] = $internetService->id;
                    $data['description'] = $internetService->isp_acount_number;////eliminar
                    
                    /////////////Asignar contrato de internet a branch
                    // \Log::info("Asociando el internet service contract id=".$internetService->id." al branch id=".$branch_id);
                    // try{
                    //     \DB::table('branches')
                    //         ->where('id',$branch_id)
                    //         ->update(['internet_service_contract_id' => $internetService->id]);
                    //    \Log::info("Contrato de internet #id".$internetService->id." asignado al branch #id".$branch_id." correctamente");
                    
                    // }catch (\Exception $e){
                    //     \Log::critical($e->getMessage());
                    //     $respuesta['mensaje'] = 'Error al asignar contrato de internet al branch';
                    //     $respuesta['tipo'] = 'error';
                    // }

                    \Log::info("Nuevo internet service contract creada correctamente");
                    $respuesta['mensaje'] = 'Contrato de servicio de internet creada correctamente';
                    $respuesta['tipo'] = 'success';
                    $respuesta['data'] = $data;
                    return $respuesta;
                } else {
                   \Log::critical($e->getMessage());
                    $respuesta['mensaje'] = 'Error al crear el contrato de servicio de internet';
                    $respuesta['tipo'] = 'error';
                    return $respuesta;
                }
            }catch (\Exception $e){
                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Error al crear el contrato de servicio de internet';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }
        }else{
            if ($internetService->save()) {
                $message = 'Agregado correctamente';
                Session::flash('message', $message);
                return redirect()->route('internet.contract.index');
            }
        }

    }
  
    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        //
    }

    public function update(Request $request, $id)
    {
        //
    }
 
    public function destroy($id)
    {
        //
    }

}
