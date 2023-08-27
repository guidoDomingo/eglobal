<?php

namespace App\Http\Controllers;

use Session;
use Carbon\Carbon;
use App\Models\Group;
use App\Models\Atmnew;
use App\Models\Content;
use Illuminate\Http\Request;
use App\Models\CampaignDetails;
use App\Models\PromotionCategory;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\CaracteristicaSucursal;
use Illuminate\Support\Facades\Storage;

class CaracteristicasClientesController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function index(Request $request)
    {
        if (!$this->user->hasAccess('caracteristicas_clientes.index')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $caracteristicas = CaracteristicaSucursal::join('branches', 'branches.caracteristicas_id', '=', 'caracteristicas.id')
        ->join('business_groups', 'business_groups.id', '=', 'branches.group_id')
        ->join('canal', 'canal.id', '=', 'caracteristicas.canal_id')
        ->join('categorias', 'categorias.id', '=', 'caracteristicas.categoria_id')

        ->select(['caracteristicas.id as id',
                'business_groups.ruc as ruc',
                'business_groups.description as description_group',
                'branches.description as description_branch',
                'canal.descripcion as canal',
                'categorias.descripcion as categoria'
                ])
        ->whereIn('branches.owner_id',[16,21,25])
        ->get();   

        $clientes = Group::all(); //borrar
        
        $grupos = \DB::table('business_groups')
        ->select(['business_groups.description', 'business_groups.ruc', 'business_groups.id'])
        ->whereNull('deleted_at')
        ->get();

        return view('caracteristicas_clientes.index', compact('caracteristicas', 'clientes','grupos'));
    }
   
    public function create($group_id)
    {
        if (!$this->user->hasAccess('caracteristicas_clientes.add')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $bancos          = \DB::table('clientes_bancos')->pluck('descripcion','id');
        $banco_id        = 0;
        $tipo_cuentas    = \DB::table('clientes_tipo_cuenta')->pluck('descripcion','id');
        $tipo_cuentas_id = 0;
        $canales         = \DB::table('canal')->pluck('descripcion','id');
        $canal_id        = 0;
        $categorias      = \DB::table('categorias')->pluck('descripcion','id');
        $categoria_id    = 0;

        return view('caracteristicas_clientes.create', compact('bancos','banco_id','tipo_cuentas','tipo_cuentas_id','canales','canal_id','categorias','categoria_id','group_id'));
    }

    public function store(Request $request)
    {
        if (!$this->user->hasAccess('caracteristicas_clientes.add')) {
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
              
                if(!empty($request->nro_cuenta)){

                    $cuenta_bancaria_id = \DB::table('clientes_cuentas_bancarias')->insertGetId([
                        'clientes_bancos_id'      => $request->banco_id,
                        'clientes_tipo_cuenta_id' => $request->tipo_cuenta,
                        'numero'                  => $request->nro_cuenta,
                        'created_at'              =>  Carbon::now(),
                        'updated_at'              =>  Carbon::now(),
                        'deleted_at'              =>  null
                    ]);
                }else{
                    $cuenta_bancaria_id = null;
                }
        
                $caracteristica = new CaracteristicaSucursal;
                $caracteristica->group_id           = $input['group_id'];
                $caracteristica->canal_id           = $input['canal_id'];
                $caracteristica->categoria_id       = $input['categoria_id'];
                $caracteristica->cta_bancaria_id    = $cuenta_bancaria_id;
                $caracteristica->referencia         = $input['referencia'];
                $caracteristica->accesibilidad      = $input['accesibilidad'];
                $caracteristica->visibilidad        = $input['visibilidad'];
                $caracteristica->trafico            = $input['trafico'];
                $caracteristica->dueño              = $input['dueño'];
                $caracteristica->atendido_por       = $input['atendido_por'];
                $caracteristica->estado_pop         = $input['estado_pop'];
                $caracteristica->correo             = $input['correo'];
                $caracteristica->permite_pop        = (isset($input['permite_pop'])? true : false);
                $caracteristica->tiene_pop          = (isset($input['tiene_pop'])? true : false);
                $caracteristica->tiene_bancard      = (isset($input['tiene_bancard'])? true : false);
                $caracteristica->tiene_pronet       = (isset($input['tiene_pronet'])? true : false);
                $caracteristica->tiene_netel        = (isset($input['tiene_netel'])? true : false);
                $caracteristica->tiene_pos_dinelco  = (isset($input['tiene_pos_dinelco'])? true : false);
                $caracteristica->tiene_pos_bancard  = (isset($input['tiene_pos_bancard'])? true : false);
                $caracteristica->tiene_billetaje    = (isset($input['tiene_billetaje'])? true : false);
                $caracteristica->tiene_tm_telefonito =(isset($input['tiene_tm_telefonito'])? true : false);
                $caracteristica->visicooler          = (isset($input['visicooler'])? true : false);
                $caracteristica->bebidas_alcohol     = (isset($input['bebidas_alcohol'])? true : false);
                $caracteristica->bebidas_gasificadas = (isset($input['bebidas_gasificadas'])? true : false);
                $caracteristica->productos_limpieza  = (isset($input['productos_limpieza'])? true : false);

                if ($caracteristica->save()) {

                    
                    \Log::info("Caracteristicas agregada correctamente");

                    \DB::commit();
                    return redirect('caracteristicas/clientes/'.$input['group_id'].'/show')->with('guardar','ok');
                    // return redirect()->to('caracteristicas/clientes/'.$input['group_id'].'/show');

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
        $caracteristicas = CaracteristicaSucursal::join('business_groups', 'business_groups.id', '=', 'caracteristicas.group_id')
        ->join('canal', 'canal.id', '=', 'caracteristicas.canal_id')
        ->join('categorias', 'categorias.id', '=', 'caracteristicas.categoria_id')
        ->join('clientes_cuentas_bancarias', 'clientes_cuentas_bancarias.id','=','caracteristicas.cta_bancaria_id')
        ->join('clientes_bancos', 'clientes_bancos.id','=','clientes_cuentas_bancarias.clientes_bancos_id')

        ->select(['caracteristicas.id as id',
                'caracteristicas.referencia as referencia',
                'canal.descripcion as canal',
                'categorias.descripcion as categoria',
                'clientes_cuentas_bancarias.numero as numero_cuenta',
                'clientes_bancos.descripcion as banco',
                'caracteristicas.permite_pop',
                'caracteristicas.tiene_pop',
                'caracteristicas.tiene_netel',
                'caracteristicas.tiene_bancard',
                'caracteristicas.tiene_pronet',
                'caracteristicas.tiene_pos_dinelco',
                'caracteristicas.tiene_pos_bancard',
                'caracteristicas.tiene_billetaje'
                ])
        // ->whereIn('branches.owner_id',[16,21,25])
        ->where('caracteristicas.group_id',$id)
        ->get();  

        $group_id = $id; 
        $grupo = Group::where('id',$group_id)->first();

        return view('caracteristicas_clientes.show', compact('caracteristicas','group_id','grupo'));
    }
   
    public function edit($id)
    {
        if (!$this->user->hasAccess('caracteristicas_clientes.edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if($caracteristica = CaracteristicaSucursal::find($id)){
            


            $nro_cuenta = \DB::table('clientes_cuentas_bancarias')
            ->select('clientes_cuentas_bancarias.numero as nro_cuenta', 'clientes_bancos.descripcion as banco')
            ->join('clientes_bancos', 'clientes_bancos.id','=','clientes_cuentas_bancarias.clientes_bancos_id')
            ->where('clientes_cuentas_bancarias.id','=',$caracteristica->cta_bancaria_id)
            ->get();
            
            $grupos = \DB::table('business_groups')
            ->select(['business_groups.description', 'business_groups.ruc', 'business_groups.id'])
            ->whereNull('deleted_at')
            ->get();

            $group_id = $id; 
            $grupo = Group::where('id',$caracteristica->group_id)->first();

            $bancos          = \DB::table('clientes_bancos')->pluck('descripcion','id');
            $tipo_cuentas    = \DB::table('clientes_tipo_cuenta')->pluck('descripcion','id');
            $canales         = \DB::table('canal')->pluck('descripcion','id');
            $categorias      = \DB::table('categorias')->pluck('descripcion','id');

            $data = [
                'grupo'          =>$grupo,
                'nro_cuenta'     =>$nro_cuenta,
                'caracteristica' => $caracteristica,
                'bancos'         => $bancos,
                'tipo_cuentas'   => $tipo_cuentas,
                'canales'        => $canales,
                'categorias'     => $categorias,       
            ];
            return view('caracteristicas_clientes.edit', compact('grupo','nro_cuenta','caracteristica','bancos','tipo_cuentas','canales','categorias'));
        }else{
            Session::flash('error_message', 'Contenido no encontrado.');
            return redirect('contents');
        }

    }

    public function update(Request $request, $id)
    {
        if (!$this->user->hasAccess('caracteristicas_clientes.edit')) 
        {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \Log::info($request);
     
        \DB::beginTransaction();
        if ($caracteristica = CaracteristicaSucursal::find($id)){
            $input = $request->all();
            $group_id = $caracteristica->group_id;
             try{

                $caracteristica->fill($input+[
                    'permite_pop' => (isset($input['permite_pop'])? true : false),
                    'tiene_pop' => (isset($input['tiene_pop'])? true : false),
                    'tiene_bancard' => (isset($input['tiene_bancard'])? true : false),
                    'tiene_pronet' => (isset($input['tiene_pronet'])? true : false),
                    'tiene_netel' => (isset($input['tiene_netel'])? true : false),
                    'tiene_pos_dinelco' => (isset($input['tiene_pos_dinelco'])? true : false),
                    'tiene_pos_bancard' => (isset($input['tiene_pos_bancard'])? true : false),
                    'tiene_billetaje' => (isset($input['tiene_billetaje'])? true : false),
                    'tiene_tm_telefonito' => (isset($input['tiene_tm_telefonito'])? true : false),
                    'visicooler' => (isset($input['visicooler'])? true : false),
                    'bebidas_alcohol' => (isset($input['bebidas_alcohol'])? true : false),
                    'bebidas_gasificadas' => (isset($input['bebidas_gasificadas'])? true : false),
                    'productos_limpieza' => (isset($input['productos_limpieza'])? true : false),

                ]);
                if($caracteristica->update()){
              
                    
                    \DB::commit();
                    return redirect('caracteristicas/clientes/'.$caracteristica->group_id.'/show')->with('actualizar','ok');
                }
            }catch (\Exception $e){
                \DB::rollback();

                \Log::error("Error updating caracteristicas: " . $e->getMessage());
                return redirect()->back()->withInput()->with('error', 'ok');
            }
        }else{
            \Log::warning("Caracteristicas not found");
            return redirect()->back()->withInput()->with('error', 'ok');
        }
        
    }

    public function destroy(Request $request)
    {
        if (!$this->user->hasAccess('caracteristicas_clientes.delete')) 
        {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $id= $request->_id;

        $message    = '';
        $error      = '';
        \Log::debug("Intentando elimiar caracteristica ".$id);

        if ($caracteristica = CaracteristicaSucursal::find($id))
        {

            try{
                \DB::beginTransaction();

                    
                        
                    if (CaracteristicaSucursal::where('id',$id)->delete()){
                        $message  =  'Caracteristica eliminado correctamente';
                        $error    = false;
                    }
                    \DB::commit();

                }catch (\Exception $e){
                    \DB::rollback();

                    \Log::error("Error deleting content: " . $e->getMessage());
                    $message  =  'Error al intentar eliminar el contenido';
                    $error    = true;
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
   

}
