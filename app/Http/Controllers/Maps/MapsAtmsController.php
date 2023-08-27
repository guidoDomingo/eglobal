<?php

namespace App\Http\Controllers\Maps;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;

class MapsAtmsController extends Controller
{
    /**
     * @var class $user: Usuario
     * @global object 
     */
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    /**
     * Display a list for all Users
     * @return |Response
     */
    public function index(Request $request)
    {
        $user = \Sentinel::getUser();
        $user_id = $user->id;
        $username = $this->user->username;
        $action = \Request::route()->getActionName();

        if (!$this->user->hasAccess('maps')) {
            \Log::error("El usuario: $username no tiene permisos para la realizar la acción: $action");
            \Session::flash('error_message', 'No posee permisos para realizar esta acción.');
            return redirect('/');
        }


        /*$business_groups = \DB::table('business_groups')
            ->select(
                'id',
                'description'
            )
            ->whereRaw('deleted_at is null')
            ->orderBy('description', 'asc')
            ->get();

        $branches = \DB::table('branches')
            ->select(
                'id',
                'description'
            )
            ->orderBy('description', 'asc')
            ->whereRaw('deleted_at is null')
            ->whereRaw('group_id is not null')
            ->get();

        $points_of_sale = \DB::table('points_of_sale')
            ->select(
                'id',
                'description'
            )
            ->whereRaw('deleted_at is null')
            ->whereRaw('branch_id is not null')
            ->orderBy('description', 'asc')
            ->get();*/

        $item_all = [
            'id' => 'Todos',
            'description' => 'Todos'
        ];

        $promotions_providers = \DB::table('promotions_providers')
            ->select(
                'id',
                'name as description'
            )
            ->get();

        $promotions_providers = json_decode(json_encode($promotions_providers), true);
        array_unshift($promotions_providers, $item_all);

        //\Log::info('promotions_providers:');
        //\Log::info($promotions_providers);

        $business = \DB::table('business')
            ->select(
                'id',
                'description'
            )
            ->get();

        $business = json_decode(json_encode($business), true);
        array_unshift($business, $item_all);

        $promotions_branches = \DB::table('promotions_branches')
            ->select(
                'id',
                'name as description'
            )
            ->get();

        $promotions_branches = json_decode(json_encode($promotions_branches), true);
        array_unshift($promotions_branches, $item_all);

        $departaments = \DB::table('departamento as d')
            ->select(
                'd.id',
                \DB::raw('trim(d.descripcion) as description')
            )
            ->orderBy('d.id', 'ASC')
            ->get();

        $departaments = json_decode(json_encode($departaments), true);
        array_unshift($departaments, $item_all);

        $atms = \DB::table('atms as a')
            ->select(
                'a.id',
                \DB::raw("'#' || a.id || '. ' || a.name as description"),
                'b.address',
                'b.latitud as latitude',
                'b.longitud as longitude'
            )
            ->join('points_of_sale as pos', 'a.id', '=', 'pos.atm_id')
            ->join('branches as b', 'b.id', '=', 'pos.branch_id')
            ->whereRaw("b.latitud is not null")
            ->whereRaw("b.longitud is not null")
            ->whereRaw("b.latitud <> '0'")
            ->whereRaw("b.longitud <> '0'")
            ->whereRaw("length(b.latitud) > 7")
            ->whereRaw("length(b.longitud) > 7")
            ->whereRaw("a.owner_id in (11,16,21,25)")
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

        return view('maps.maps_atms', compact('data'));
    }

    /**
     * Carga las ubicaciones
     */
    public function load_atms_business_locations(Request $request)
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

        \Log::info('ALL:');
        \Log::info($request->all());
        

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
            ->whereRaw("a.owner_id in (11,16,21,25)");

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

        //\Log::info('SQL:');
        //\Log::info($atms_locations->toSql());


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
    public function get_promotions_branches(Request $request)
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

    /**
     * Obtiene la ciudades de un departamento
     */
    public function get_cities(Request $request)
    {
        $cities = \DB::table('ciudades as c')
            ->select(
                'c.id',
                'c.descripcion as description'
            )
            ->join('departamento as d', 'd.id', '=', 'c.departamento_id');

        $departament_id = '';

        if (isset($request['departament_id'])) {
            $departament_id = $request['departament_id'];
        }

        if ($departament_id !== '') {
            $cities = $cities->whereRaw("d.id = $departament_id");
        }

        //\Log::info('SQL:');
        //\Log::info($cities->toSql());

        $cities = $cities->get();

        return $cities;
    }

    /**
     * Obtiene la ciudades de un departamento
     */
    public function get_districts(Request $request)
    {
        $districts = \DB::table('barrios as b')
            ->select(
                'b.id',
                'b.descripcion as description'
            )
            ->join('ciudades as c', 'c.id', '=', 'b.ciudad_id');

        $city_id = '';

        if (isset($request['city_id'])) {
            $city_id = $request['city_id'];
        }

        if ($city_id !== '') {
            $districts = $districts->whereRaw("c.id = $city_id");
        }

        //\Log::info('SQL:');
        //\Log::info($districts->toSql());

        $districts = $districts->get();

        return $districts;
    }
}