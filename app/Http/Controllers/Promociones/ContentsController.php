<?php

namespace App\Http\Controllers\Promociones;

use Session;
use App\Models\Content;
use Illuminate\Http\Request;
use App\Models\CampaignDetails;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\PromotionCategory;
use Illuminate\Support\Facades\Storage;
class ContentsController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function index(Request $request)
    {
        if (!$this->user->hasAccess('content')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $contents   = Content::all();

        return view('promociones.contenidos.index', compact('contents'));
    }
   
    public function create()
    {
        if (!$this->user->hasAccess('content.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $categoriasList     =  \DB::table('promotions_categories')
            ->select(\DB::raw("id, name || ' | ' ||start_time || ' - ' || end_time as descripcion"))
            ->get();
        $categoriesJson   = json_encode($categoriasList);
        $categorias       = \DB::table('promotions_categories')->pluck('name','id');
    
        return view('promociones.contenidos.create', compact('categoriesJson','categorias'));
    }

    public function store(Request $request)
    {
        if (!$this->user->hasAccess('content.add|edit')) {
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
                if(!empty($input['image'])){
                    $imagen         = $input['image'];
                    $data_imagen    = json_decode($imagen);
                    $nombre_imagen  = $data_imagen->name;
                    $urlHost        = request()->getSchemeAndHttpHost();
                    Storage::disk('contenidos')->put($nombre_imagen,  base64_decode($data_imagen->data));
                    $input['image'] = $urlHost.'/resources/images/contents/'.$nombre_imagen;
                }else{
                    $input['image'] = '';
                }

                if ($content      =  Content::create($input)){

                    //para multi categorias
                    // $receivedCategories     = $input['categories'];
                    // $array_cotegories         = explode(",",$receivedCategories );
                    // \Log::info('ID de categorias: '.$receivedCategories);
                    // foreach($array_cotegories as $one_category){
                    //     DB::table('contents_has_categories')->insert(['contents_id' => $content->id, 'promotions_categories_id' => $one_category]);
                    // }
                    //catgoria unica
                    $categoria_id = $input['categoria_id'];
                    DB::table('contents_has_categories')->insert(['contents_id' => $content->id, 'promotions_categories_id' => $categoria_id]);
                    \Log::info("Nuevo contenido agregado correctamente");

                    \DB::commit();
                    return redirect('contents')->with('guardar','ok');
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
        //
    }
   
    public function edit($id)
    {
        if (!$this->user->hasAccess('content.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if($contenido = Content::find($id)){

            $categories_asociado =  \DB::table('contents_has_categories')
            ->select(['promotions_categories_id'])
            ->where('contents_id',$id)
            ->get();

            $categoriesList=[];
            foreach($categories_asociado as $asoc){
                $categoriesList[] = $asoc->promotions_categories_id;
            }

            $categoryListAll    = \DB::table('promotions_categories')->select(\DB::raw("id, name || ' | ' ||start_time || ' - ' || end_time as descripcion"))->get();
            $categoriesList     = PromotionCategory::whereIn('id',$categoriesList)->select(\DB::raw("id, name || ' | ' ||start_time || ' - ' || end_time as descripcion"))->get();
            $categoriesIds      = $categoriesList->implode('id', ',');
            $categoriesJson     = json_encode($categoriesList);
            $categoriesJsonAll  = json_encode($categoryListAll);
            $category_id        = null;

            $data = [
                'content'           => $contenido,
                'categoriesJson'    => $categoriesJson,
                'category_id'       => $category_id,
                'categoriesIds'     => $categoriesIds,
                'categoriesJsonAll' => $categoriesJsonAll,       
            ];

            return view('promociones.contenidos.edit', $data);
        }else{
            Session::flash('error_message', 'Contenido no encontrado.');
            return redirect('contents');
        }

    }

    public function update(Request $request, $id)
    {
        if (!$this->user->hasAccess('content.add|edit')) 
        {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if( $request['categories'] == ''){
            return redirect()->back()->with('error_categoria', 'ok');
        }else{
            \DB::beginTransaction();
            if ($content = Content::find($id)){
                $input = $request->all();

                try{
                    //Get categories
                    $receivedCategories = $input['categories'];
                    \Log::info('Categories Ids recibidos: '.$receivedCategories);

                    $categories_asociados= DB::table('contents_has_categories')
                    ->where('contents_id', '=', $id)
                    ->delete();

                    $categories_cadena = explode(",",$receivedCategories );

                    foreach($categories_cadena as $one_category){
                        DB::table('contents_has_categories')->insert(['promotions_categories_id' => $one_category, 'contents_id' => $id]);
                        \Log::info('category_id insertado:' . $one_category.' contenido; '. $content->name);
                    }

                    if(!empty($input['image'])){

                        $imagen         = $input['image'];
                        $data_imagen    = json_decode($imagen);
                        $nombre_imagen  = $data_imagen->name;
                        $urlHost        = request()->getSchemeAndHttpHost();
                        $input['image'] = $urlHost.'/resources/images/contents/'.$nombre_imagen;

                        if($content->image != $input['image'] && $content->image != null){
                            if(file_exists(public_path().'/resources'.trim($content->image))){
                                unlink(public_path().'/resources'.trim($content->image));
                            }
                            Storage::disk('contenidos')->put($nombre_imagen,  base64_decode($data_imagen->data));
                        }else{
                            // unset($input['image']);
                            Storage::disk('contenidos')->put($nombre_imagen,  base64_decode($data_imagen->data));
                            $input['image'] = $urlHost.'/resources/images/contents/'.$nombre_imagen;

                        }
                    }

                    $content->fill($input);
                    if($content->update()){
                        \DB::commit();
                        return redirect('contents')->with('actualizar','ok');;
                    }
                }catch (\Exception $e){
                    \DB::rollback();

                    \Log::error("Error updating content: " . $e->getMessage());
                    return redirect()->back()->withInput()->with('error', 'ok');
                }
            }else{
                \Log::warning("Content not found");
                return redirect()->back()->withInput()->with('error', 'ok');
            }
        }
    }

    public function destroy($id)
    {
        if (!$this->user->hasAccess('content.delete')) 
        {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $message    = '';
        $error      = '';
        \Log::debug("Intentando elimiar contenido ".$id);

        if ($content = Content::find($id))
        {

            $campaña = CampaignDetails::where('contents_id',$id)->count();
            if ($campaña == 0){
                try{
                    \DB::beginTransaction();

                    $contents_has_categories= DB::table('contents_has_categories')
                    ->where('contents_id', '=', $id)
                    ->delete();
                    \Log::info("Contenido id:".$id. " tbl_contents_has_categories, eliminando relacion con categoria");
        
                        
                    if (Content::where('id',$id)->delete()){
                        $message  =  'Contenido eliminado correctamente';
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
                $message   =  'El Contenido se encuentra asociado a  una campaña';
                $error     = true;
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
