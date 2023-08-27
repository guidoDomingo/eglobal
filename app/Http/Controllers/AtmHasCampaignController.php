<?php

namespace App\Http\Controllers;

use Session;

use App\Models\Art;
use App\Models\Campaign;
use Illuminate\Http\Request;
use App\Models\AtmHasCampaign;
use App\Models\PromotionBranch;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;


class AtmHasCampaignController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function index(Request $request)
    {
        if (!$this->user->hasAccess('asociar')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
     
        $campaign_id    = $request->get('campaign_id');
        $campaign       = Campaign::find($campaign_id);
        $atms_list      = \DB::table('atms')->orderBy('id','asc')->whereNull('deleted_at')->pluck('name','id');   //listado de atm
        // $providers      = \DB::table('promotions_providers')->pluck('name','id');
        $business_list       = \DB::table('business')->pluck('description','id');

        $branches       = \DB::table('promotions_branches')->pluck('name','id');
        $asociaciones   = AtmHasCampaign::where('atm_has_campaigns.campaigns_id',$campaign_id)
        ->select(['atm_has_campaigns.id','atm_has_campaigns.atm_id as atm_id','atms.name as atm_name','atm_has_campaigns.promotions_branches_id as id_branch','promotions_branches.name as branch_name'])
        ->join('atms', 'atms.id', '=', 'atm_has_campaigns.atm_id')
        ->join('promotions_branches', 'promotions_branches.id', '=', 'atm_has_campaigns.promotions_branches_id')
        ->get();    

        // MAPS

        $item_all = [
            'id' => 'Todos',
            'description' => 'Todos'
        ];

        $promotions_providers = \DB::table('promotions_providers')->select('id','name as description')->get();

        $promotions_providers = json_decode(json_encode($promotions_providers), true);
        array_unshift($promotions_providers, $item_all);

        //\Log::info('promotions_providers:');
        //\Log::info($promotions_providers);

        $business = \DB::table('business')->select('id','description')->get();

        $business = json_decode(json_encode($business), true);
        array_unshift($business, $item_all);

        $promotions_branches = \DB::table('promotions_branches')->select('id','name as description')->get();
        $promotions_branches = json_decode(json_encode($promotions_branches), true);
        array_unshift($promotions_branches, $item_all);

        $departaments = \DB::table('departamento as d')
            ->select('d.id',\DB::raw('trim(d.descripcion) as description'))->orderBy('d.id', 'ASC')->get();

        $departaments = json_decode(json_encode($departaments), true);
        array_unshift($departaments, $item_all);

        $atms = \DB::table('atms as a')
            ->select(
                'a.id',
                \DB::raw("'#' || a.id || '. ' || a.name as description"),
                'b.address',
                'b.latitud as latitude',
                'b.longitud as longitude')
            ->join('points_of_sale as pos', 'a.id', '=', 'pos.atm_id')
            ->join('branches as b', 'b.id', '=', 'pos.branch_id')
            ->whereRaw("b.latitud is not null")
            ->whereRaw("b.longitud is not null")
            ->whereRaw("b.latitud <> '0'")
            ->whereRaw("b.longitud <> '0'")
            ->whereRaw("length(b.latitud) > 7")
            ->whereRaw("length(b.longitud) > 7")
            ->whereRaw("a.owner_id in (11,21,16,25)")
            ->get();

        $item_all = [
            'id' => 'Todos',
            'description' => 'Todos',
            'address' => null,
            'latitude' => null,
            'longitude' => null
        ];

        $atms = json_decode(json_encode($atms), true);
        array_unshift($atms, $item_all);

        $data = [
            'lists' => json_encode([
                'records_list' => [],
                'business_locations' => [],
                'atms' => $atms,
                'departaments' => $departaments,
                'promotions_providers' => $promotions_providers,
                'business' => $business,
                'promotions_branches' => $promotions_branches,
                //'business_groups' => $business_groups,
                //'branches' => $branches,
                //'points_of_sale' => $points_of_sale
            ]),
            'inputs' => []
        ];
        return view('campaigns.asociaciones', compact('data','atms_list','branches','business_list','asociaciones','campaign','campaign_id'));
    }

    public function create(Request $request)
    {
        //
    }

    public function store(Request $request)
    {
        try{
            \DB::beginTransaction();

            $verificar = AtmHasCampaign::where('campaigns_id',$request->campaign_id)
                ->where('atm_id', $request->atm_id)
                ->where('promotions_branches_id', $request->branch_id)
                ->first();
            \Log::info($verificar);

            if($verificar != null){
                Session::flash('error_message', 'El ATM ya se encuentra asociada a la sucursal indicada.');
                \Log::info('ATM_id: '.$request->atm_id.' ya se encuentra asociada a la sucursal_id: '.$request->branch_id);

                return response()->json(['error'=>'ok']);

            }else{
                AtmHasCampaign::create(['atm_id' => $request->atm_id, 'campaigns_id' => $request->campaign_id, 'promotions_branches_id'  => $request->branch_id]);        
                \Log::info('ATM asociado correctamente');
                \DB::commit();
                Session::flash('message', 'ATM asociado correctamente');
                return response()->json(['guardar'=>'ok']);

            }

        }catch (\Exception $e){
            \DB::rollback();
            \Log::critical($e->getMessage());
            Session::flash('error_message', 'Error al asociar el atm con la sucursal');
            return redirect()->back()->with('error', 'Error al asociar el ATM');
        }
    }

    public function show($id)
    {
        //
    }
   
    public function edit($id)
    {
     
    }

    public function update(Request $request, $id)
    {
    
    }

    public function destroy($id)
    {
        if (!$this->user->hasAccess('asociar.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $message = '';
        $error = '';
        \Log::debug("Intentando elimiar asociacion_id:  ".$id);
        if ($art = AtmHasCampaign::find($id)){
            try{
               
                if (AtmHasCampaign::where('id',$id)->delete()){
                    $message    =  'Asociacion eliminada correctamente';
                    $error      = false;
                }
            }catch (\Exception $e){
                \Log::error("Error deleting la asociacion: " . $e->getMessage());
                $message    =  'Error al intentar eliminar la asociacion';
                $error      = true;
            }
        }else{
            $message    =  'Asociacion no encontrada';
            $error      = true;
        }

        return response()->json([
            'error' => $error,
            'message' => $message,
        ]);
    }
   
    /**
     * Carga las ubicaciones
     */
    public function load_atms_business_locations_promotions(Request $request)
    {

        /**
         * Valores iniciales
         */
        $record_limit = 1000;
        $atm_id = '';
        $promotions_providers_id = '';
        $provider_branch_id = '';
        $business_id = '';
        $departament_id = '';
        $city_id = '';
        $district_id = '';

        $atms_locations = \DB::table('atms as a')
            ->select(
                'a.id',
                \DB::raw("'#' || a.id || '. ' || a.name as description"),
                'b.address',
                'ba.descripcion as district',
                'c.descripcion as city',
                'd.descripcion as departament',
                'b.latitud as latitude',
                'b.longitud as longitude',
                'd.id as departament_id'
            )
            ->join('points_of_sale as pos', 'a.id', '=', 'pos.atm_id')
            ->join('branches as b', 'b.id', '=', 'pos.branch_id')
            ->join('barrios as ba', 'ba.id', '=', 'b.barrio_id')
            ->join('ciudades as c', 'c.id', '=', 'ba.ciudad_id')
            ->join('departamento as d', 'd.id', '=', 'c.departamento_id')
            ->whereRaw("b.latitud is not null")
            ->whereRaw("b.longitud is not null")
            ->whereRaw("b.latitud <> '0'")
            ->whereRaw("b.longitud <> '0'")
            ->whereRaw("length(b.latitud) > 7")
            ->whereRaw("length(b.longitud) > 7")
            ->whereRaw("a.owner_id in (11,21,25)");

        if (isset($request['atm_id'])) {
            $atm_id = $request['atm_id'];
        }

        if ($atm_id !== '') {
            $atms_locations = $atms_locations->whereRaw("a.id = $atm_id");
        }

        if (isset($request['departament_id'])) {
            $departament_id = $request['departament_id'];
        }

        if ($departament_id !== '') {
            $atms_locations = $atms_locations->whereRaw("d.id = $departament_id");
        }

        if (isset($request['city_id'])) {
            $city_id = $request['city_id'];
        }

        if ($city_id !== '') {
            $atms_locations = $atms_locations->whereRaw("c.id = $city_id");
        }

        if (isset($request['district_id'])) {
            $district_id = $request['district_id'];
        }

        if ($district_id !== '') {
            $atms_locations = $atms_locations->whereRaw("pp.id = $district_id");
        }


        if (isset($request['record_limit'])) {
            $record_limit = $request['record_limit'];
        }

        if ($record_limit !== '') {
            $atms_locations = $atms_locations->take(intval($record_limit));
        }


        $atms_locations = $atms_locations->get();

        //-----------------------------------------------------------------------------------------------

        $business_locations = \DB::table('promotions_branches as pb')
            ->select(
                'pb.id',
                'pb.name as description',
                'pb.address',
                'pb.latitud as latitude',
                'pb.longitud as longitude',
                'pb.address',
                'b.description as business',
                'b.image',
                \DB::raw("coalesce(pb.phone, 'Sin teléfono') as phone"),
                \DB::raw("coalesce(pp.name, 'Sin proveedor') as provider")
            )
            ->join('promotions_providers as pp', 'pp.id', '=', 'pb.promotions_providers_id')
            ->join('business as b', 'b.id', '=', 'pb.business_id');

        if (isset($request['promotions_providers_id'])) {
            $promotions_providers_id = $request['promotions_providers_id'];
        }

        if ($promotions_providers_id !== '') {
            $business_locations = $business_locations->whereRaw("pp.id = $promotions_providers_id");
        }

        if (isset($request['business_id'])) {
            $business_id = $request['business_id'];
        }

        if ($business_id !== '') {
            $business_locations = $business_locations->whereRaw("b.id = $business_id");
        }

        //\Log::info('SQL:');
        //\Log::info($business_locations->toSql());

        $business_locations = $business_locations
            ->orderBy('b.id', 'ASC')
            ->get();

        return [
            'atms_locations' => $atms_locations,
            'business_locations' => $business_locations
        ];
    }

    /**
     * Obtiene las sucursales de la empresa
     */
    public function get_promotions_branches_promotions(Request $request)
    {

        $promotions_branches = \DB::table('promotions_branches')
            ->select(
                'id',
                'name as description'
            );

        $promotions_providers_id = '';
        $business_id = '';

        if (isset($request['promotions_providers_id'])) {
            $promotions_providers_id = $request['promotions_providers_id'];
        }

        if ($promotions_providers_id !== '') {
            $promotions_branches = $promotions_branches->whereRaw("promotions_providers_id = $promotions_providers_id");
        }

        if (isset($request['business_id'])) {
            $business_id = $request['business_id'];
        }

        if ($business_id !== '') {
            $promotions_branches = $promotions_branches->whereRaw("business_id = $business_id");
        }


        \Log::info('promotions_branches:');
        \Log::info($promotions_branches->toSql());

        $promotions_branches = $promotions_branches->get();

        //\Log::info("business_id: $business_id");

        return $promotions_branches;
    }



}
