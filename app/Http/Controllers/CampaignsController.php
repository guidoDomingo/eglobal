<?php

namespace App\Http\Controllers;

use Session;

use DateTime;
use Carbon\Carbon;
use App\Models\Art;
use App\Models\Atm;
use App\Models\Content;
use App\Models\Campaign;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\AtmHasCampaign;
use App\Models\AtmsHasCampaigns;
use App\Models\PromotionBranch;
use Illuminate\Support\Facades\Storage;


class CampaignsController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function index(Request $request)
    {
        if (!$this->user->hasAccess('campaigns')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $name       = $request->get('name');
        $campaigns  = Campaign::filterAndPaginate($name);
        return view('campaigns.index', compact('campaigns', 'name'));
    }
   
    public function create()
    {
        if (!$this->user->hasAccess('campaigns.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $atms           = \DB::table('atms')->orderBy('id','asc')->whereNull('deleted_at')->pluck('name','id');
        $contents       = \DB::table('contents')->orderBy('id','asc')->pluck('name','id');
        $contentsList   = Content::all(['id', 'name']);
        $contentsJson   = json_encode($contentsList);
        $contentsJsonAll  = json_encode($contentsList);

        //Get all atms
        $atmsList =  \DB::table('atms')
        ->select(['id','name'])
        ->whereNull('deleted_at')
        ->get();
        $atms_list=[];
        // foreach($atmsList as $asocAtm){
        //     $atms_list[] = $asocAtm->id;
        //     $atms_list[] = $asocAtm->name;

        // }
        $atmsJsonAll   = json_encode($atmsList);

        //Get all departamentos
        $departamentosList =  \DB::table('departamento')
        ->select(['id','descripcion'])
        ->get();
        $departamentos_list=[];
        // foreach($departamentosList as $asocAtm){
        //     $departamentos_list[] = $asocAtm->id;
        //     $departamentos_list[] = $asocAtm->descripcion;
        // }
        $departamentosJson   = json_encode($departamentosList);


        //Get all ciudades
        $ciudadesList =  \DB::table('ciudades')
        ->select(['id','descripcion'])
        ->get();
        $ciudadesJson   = json_encode($ciudadesList);

        //Get all barrios/zonas
        $barriosList =  \DB::table('barrios')
        ->select(['id','descripcion'])
        ->get();
        $barriosJson   = json_encode($barriosList);

        //Get all barrios/zonas
        $zonasList =  \DB::table('zona')
        ->select(['id','descripcion'])
        ->get();
        $zonasJson   = json_encode($zonasList);


         //Get all barrios/zonas
        $branchesList =  \DB::table('promotions_branches')
        ->select(['id','name as descripcion'])
        ->get();
        $branchesJson   = json_encode($branchesList);

        $atm_id         = null;
        $content_id     = null;
        $flow_id        = null;
        $campaign       = null;
        $providers      = \DB::table('promotions_providers')->pluck('name','id');
        $branches       = \DB::table('promotions_branches')->pluck('name','id');

        //mapa

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
            ->whereRaw("a.owner_id in (11,21,25)")
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

        return view('campaigns.create', compact('data','atms','atm_id', 'contents', 'content_id', 'contentsJson','flow_id','campaign','contentsJsonAll','atmsJsonAll','departamentosJson','ciudadesJson','barriosJson','zonasJson','providers','branches','branchesJson'));
    }

    public function store(Request $request)
    {
        if (!$this->user->hasAccess('campaigns.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        } 
        \DB::beginTransaction();

        $input  = $request->all();  

        // if( $request['contents'] == ''){
        //     Session::flash('error_message', 'Error al crear la campaña. Debe seleccionar al menos un contenido.');
        //     return redirect()->back()->with('error', 'Error al crear la campaña.Debe seleccionar al menos un contenido.');
        // }else{
      
            $name           = $input['name'];
            $daterange      = explode(' - ',  str_replace('/','-',$input['reservationtime']));
            $start_date     = date('Y-m-d H:i:s.u', strtotime($daterange[0]));
            $end_date       = date('Y-m-d 23:59:59', strtotime($daterange[1]));
            $fecha_inicio   = new DateTime($daterange[0]);
            $fecha_final    = new DateTime($daterange[1]);
            $diferencia     = $fecha_inicio->diff($fecha_final);
            $duration       = $diferencia->days;
            $flow           = $input['flow'];
            $tipoCampaña    = $input['tipoCampaña'];
            $code_generate  = $input['code_generate'];

            if(isset($input['perpetuity'])){
                $perpetuity = true;
            }else{
                $perpetuity = false;
            }
    
            if($request->ajax())
            {
                //
            }else{
                try{
                    $campaign_id= DB::table('campaigns')->insertGetId(
                        [
                            'name'          => $name,
                            'duration'      => $duration,
                            'flow'          => $flow,
                            'tipoCampaña'   => $tipoCampaña,
                            'start_date'    => $start_date,
                            'end_date'      => $end_date,
                            'perpetuity'    => $perpetuity,
                            'code_generate' => $code_generate,
                            'status'        => false
                        ]
                    );
                    \Log::info("Nueva campaña agregada correctamente, campaign_id:".$campaign_id);

                    //Asociar atm con campaña y sucursal

                    // $array_atms = $input['atm_id'];
                    // $array_sucursal = $input['branch_id'];

                    // foreach($array_atms as $keyAtm => $atm){
                    //     foreach($array_sucursal as $keySuc => $sursal){
                    //         if($keyAtm == $keySuc){
                    //             $atm_has_campaign = DB::table('atm_has_campaigns')->insert(
                    //                 [
                    //                     'atm_id'                 => $array_atms[$keyAtm],
                    //                     'campaigns_id'           => $campaign_id,
                    //                     'promotions_branches_id' => $array_sucursal[$keySuc]
                    //                 ]
                    //             );
                    //             \Log::info("Campaña id: ".$campaign_id. "asociada con Atm id: ".$array_atms[$keyAtm].' sucursal con id: '.$array_sucursal[$keySuc]);
                    //         }
                    //     }
                    // }
                    ///Contenidos asociados a una campaña
                    if( $request['contents'] !== ''){
                        $campaign           = Campaign::find($campaign_id);
                        $receivedContents   = $input['contents'];
                        $array_contents     = explode(",",$receivedContents );
                        \Log::info('ID de Contenidos recibidos: '.$receivedContents);
                        foreach($array_contents as $one_content){
                            DB::table('campaigns_details')->insert(['contents_id' => $one_content, 'campaigns_id' => $campaign_id]);
                            \Log::info('Content_id insertado:' . $one_content.' campaña: '. $campaign->name);
                        }

                    }
                  
                  
                    \DB::commit();
                    Session::flash('message', 'Nueva campaña agregada correctamente');
                    return redirect('campaigns');
                    
                }catch (\Exception $e){
                    \DB::rollback();
                    \Log::critical($e->getMessage());
                    Session::flash('error_message', 'Error al crear la campaña');
                    return redirect()->back()->with('error', 'Error al crear la campaña');
                }
            }
           
        //}
    }

    public function show($id)
    {
        //
    }

    public function edit($id)
    {
        if (!$this->user->hasAccess('campaigns.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if($campaign = Campaign::find($id)){

            $content_asociado   =  \DB::table('campaigns_details')->select(['contents_id'])->where('campaigns_id',$id)->get(); 

            $contenido=[];
            foreach($content_asociado as $asoc){
                $contenido[] = $asoc->contents_id;
            }

            $contentsListAll    = Content::all(['id', 'name']);
            $contentsList       = Content::whereIn('id',$contenido)->select(['id', 'name'])->get();
            $contentsIds        = $contentsList->implode('id', ',');
            $contentsJson       = json_encode($contentsList);
            $contentsJsonAll    = json_encode($contentsListAll);
            $content_id         = null;
            $flow_id            = null;
            $atm_id             = null;
                                    
            if(empty($campaign)){
                $reservationtime    = '';
            }else{
                $campaign_date_ini  = ($campaign->start_date)->format('d-m-Y');
                $campaign_date_end  = ($campaign->end_date)->format('d-m-Y');
                $reservationtime    = $campaign_date_ini .' - '.$campaign_date_end;
            }

            $data = [
                'campaign'              => $campaign,
                'atm_id'                => $atm_id,
                'contentsJson'          => $contentsJson,
                'content_id'            => $content_id,
                'flow_id'               => $flow_id,
                'datetime'              => $reservationtime,
                'contentsIds'           => $contentsIds,
                'contentsJsonAll'       => $contentsJsonAll,
            ];
      
            return view('campaigns.edit', $data);
        }else{
            Session::flash('error_message', 'Campaña no encontrada.');
            return redirect('campaigns');
        }

    }

    public function update(Request $request, $id)
    {
        if (!$this->user->hasAccess('campaigns.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        \DB::beginTransaction();

        $input = $request->all();
        
        // if( $request['contents'] == ''){
        //     Session::flash('error_message', 'Error al modificar la campaña. Debe seleccionar al menos un contenido.');
        //     return redirect()->back()->with('error', 'Error al modificar la campaña.Debe seleccionar al menos un contenido.');
        // }else{
            if ($campaign = Campaign::find($id))
            {
                try{
                    //Get atms
                    // $receivedAtms = $input['atms'];
                    // \Log::info('ATMs Ids recibidos: '.$receivedAtms);

                    // $atms_asociados= DB::table('atm_has_campaigns')
                    // ->where('campaigns_id', '=', $id)
                    // ->delete();

                    // $atms_cadena = explode(",",$receivedAtms );

                    // foreach($atms_cadena as $one_atm){
                    //     DB::table('atm_has_campaigns')->insert(['atm_id' => $one_atm, 'campaigns_id' => $id]);
                    //     \Log::info('atm_id insertado:' . $one_atm.' campaña; '. $campaign->name);
                    // }
                            
                    $campaign->fill($input);
                    if(isset($input['perpetuity'])){
                        $perpetuity = true;
                    }else{
                        $perpetuity = false;
                    }
                    $campaign->fill(['perpetuity' =>  $perpetuity]);
                    $campaign->update();
                    //set daterange
                    $daterange      = explode(' - ',  str_replace('/','-',$input['reservationtime']));
                    $start_date     = date('Y-m-d H:i:s.u', strtotime($daterange[0]));
                    $end_date       = date('Y-m-d H:i:s.u', strtotime($daterange[1]));
                    $fecha_inicio   = new DateTime($daterange[0]);
                    $fecha_final    = new DateTime($daterange[1]);
                    $diferencia     = $fecha_inicio->diff($fecha_final);
                    $duration       = $diferencia->days;

                    //update daterange
                    DB::table('campaigns')
                    ->where('id', $id)
                    ->update(['start_date' => $start_date,'end_date' => $end_date,'duration' => $duration]);

                    $campaign_details= DB::table('campaigns_details')
                    ->where('campaigns_id', '=', $id)
                    ->delete();

                    if( $request['contents'] !== ''){
                            
    
                        $receivedContents = $input['contents'];
                        $array_contents = explode(",",$receivedContents );
                        \Log::info('ID de Contenidos recibidos: '.$receivedContents);
    
                        foreach($array_contents as $one_content){
                            DB::table('campaigns_details')->insert(['contents_id' => $one_content, 'campaigns_id' => $id]);
                            \Log::info('Content_id insertado:' . $one_content.' campaña; '. $campaign->name);
                        }
                

                    }


                   
                    \DB::commit();
                    Session::flash('message', 'Campaña actualizada correctamente.');
                    return redirect('campaigns');

                }catch (\Exception $e){
                    \DB::rollback();

                    \Log::error("Error updating content: " . $e->getMessage());
                    Session::flash('error_message','Error al intentar actualizar la campaña');
                    return redirect('campaigns');
                }
            }else{
                \Log::warning("Campaign not found");
                Session::flash('error_message', 'Campaña no encontrada');
                return redirect('campaigns');
            }
        //}
    }

    public function destroy($id)
    {
        if (!$this->user->hasAccess('campaigns.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $message = '';
        $error = '';
        \Log::debug("Intentando elimiar la campaña ".$id);
        \DB::beginTransaction();

        if ($campaign = Campaign::find($id)){
            try{
               
                $atms_has_campaigns= DB::table('atm_has_campaigns')
                    ->where('campaigns_id', '=', $id)
                    ->delete();
                \Log::info("Campaña id:".$id. " tbl_atm_has_campaigns, eliminando relacion con atm");

                $campaign_details= DB::table('campaigns_details')
                    ->where('campaigns_id', '=', $id)
                    ->delete();
                \Log::info("Campaña id:".$id. " tbl_campaigns_details, eliminando detalles");
                
                if (Campaign::where('id',$id)->delete()){
                    $message =  'Campaña eliminada correctamente';
                    $error   = false;
                }

                \DB::commit();
            }catch (\Exception $e){
                \DB::rollback();
                \Log::error("Error deleting campaign: " . $e->getMessage());
                $message    =  'Error al intentar eliminar la campaña';
                $error      = true;
            }
        }else{
            $message    =  'Campaña no encontrada';
            $error      = true;
        }

        return response()->json([
            'error'     => $error,
            'message'   => $message,
        ]);
    }

    /*
    *
    * Get branches json
    */
    public function getBranches(Request $request){
        if($request->ajax()){
            $branches = \DB::table('promotions_branches')
                ->where('business_id', $request->get('business_id'))
                ->pluck('name','id');

            $branches_select = '<option value="">Seleccione una opción</option>';
            foreach($branches as $branch_id => $branch){
                $branches_select .= '<option value="'.$branch_id.'">'.$branch.'</option>';
            }

            return $branches_select;
        }
    }

    public function enable_status_campaign(Request $request){
        if (!$this->user->hasAnyAccess('status_campaigns')) {
            \Log::warning('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            $response = [
                'error'     => true,
                'message'   => 'Acceso no Autorizado'
            ];
            return $response;
        }
        $campaign_id        = $request->_campaign_id;
        $value              = $request->_value;
        $campaign           = Campaign::find($campaign_id);
        $campaign->status   = $value;
        $campaign->save();

        if($value == true){
            \Log::info('[CAMPAÑA] habiliTada: '.$campaign_id.' - Autorizado por: '.$this->user->username .' el '.Carbon::now());
        }

        if($value == false){
            \Log::info('[CAMPAÑA]  deshabilitada: '.$campaign_id.' - Autorizado por: '.$this->user->username .' el '.Carbon::now());
        }

        $response['error'] = false;
        return $response;

    }

    //mapa
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
