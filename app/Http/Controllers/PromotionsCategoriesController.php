<?php

namespace App\Http\Controllers;

use Session;

use App\Models\Content;
use Illuminate\Http\Request;
use App\Models\CampaignDetails;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\PromotionCategory;
use Illuminate\Support\Facades\Storage;


class PromotionsCategoriesController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function index(Request $request)
    {
        // if (!$this->user->hasAccess('content')) {
        //     \Log::error('Unauthorized access attempt',
        //         ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
        //     Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
        //     return redirect('/');
        // }

        $name       = $request->get('name');
        $contents   = PromotionCategory::filterAndPaginate($name);

        return view('contenidos.index', compact('contents', 'name'));
    }
   
    public function create()
    {
        // if (!$this->user->hasAccess('content.add|edit')) {
        //     \Log::error('Unauthorized access attempt',
        //         ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
        //     Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
        //     return redirect('/');
        // }

        return view('contenidos.create');
    }

    public function store(Request $request)
    {
        // if (!$this->user->hasAccess('content.add|edit')) {
        //     \Log::error('Unauthorized access attempt',
        //         ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
        //     Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
        //     return redirect('/');
        // }

        $input = $request->all();
        
        \DB::beginTransaction();

        if($request->ajax())
        {
            $respuesta = []; 
            try{
                if ($categoria = PromotionCategory::create([
                    'name' => $input['name'],
                    'start_time' => date("H:i:s", strtotime($input['start_time'])),
                    'end_time' => date("H:i:s", strtotime($input['end_time'])),
                ])) {
                    \Log::info('Categoria de promocion creada.', $categoria->toArray());
                    $respuesta['mensaje'] = 'Departamento creado exitosamente';
                    $respuesta['tipo'] = 'success';
                    $respuesta['data'] = $categoria;
                    \DB::commit();
                    return $respuesta;
                }
            }catch (\Exception $e){
                \DB::rollback();
                \Log::critical($e->getMessage());
                $respuesta['mensaje'] = 'Error al crear la categoria';
                $respuesta['tipo'] = 'error';
                return $respuesta;
            }
        }else{
            // if (!$this->user->hasAccess('departamentos.add|edit')) {
            //     \Log::error('Unauthorized access attempt',
            //         ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            //     return redirect('/')->with('error', 'No posee permisos para realizar esta accion.');
            // }
            
            if ($categoria = PromotionCategory::create($input)) {
                \Log::info('Categoria creada.', $categoria->toArray());
    
                Session::flash('message', 'Categoria creada exitosamente');
                return redirect()
                    ->route('departamentos.index')
                    ->with('success', 'Departamento creado correctamente');
            }
    
            \Log::error('Creacion de categoria.', $input);
            Session::flash('error_message', 'Problemas al crear la categoria');
            return redirect()
                ->route('departamentos.create')
                ->withInput()
                ->with('error', 'Problemas al crear el Departamento');

        }
    }

    public function show($id)
    {
        //
    }
   
    public function edit($id)
    {
        // if (!$this->user->hasAccess('content.add|edit')) {
        //     \Log::error('Unauthorized access attempt',
        //         ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
        //     Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
        //     return redirect('/');
        // }

        if($contenido = PromotionCategory::find($id)){

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

            return view('contenidos.edit', $data);
        }else{
            Session::flash('error_message', 'Contenido no encontrado.');
            return redirect('contents');
        }

    }

    public function update(Request $request, $id)
    {
        // if (!$this->user->hasAccess('content.add|edit')) 
        // {
        //     \Log::error('Unauthorized access attempt',
        //         ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
        //     Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
        //     return redirect('/');
        // }

        if( $request['categories'] == ''){
            Session::flash('error_message', 'Error al modificar el contenido. Debe seleccionar al menos una categoria.');
            return redirect()->back()->with('error', 'Error al modificar el contenido. Debe seleccionar al menos una categoria.');
        }else{
            \DB::beginTransaction();
            if ($content = PromotionCategory::find($id)){
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
                        \DB::commit();

                        Session::flash('message', 'Contenido actualizado exitosamente');
                        return redirect('contents');
                    }
                }catch (\Exception $e){
                    \DB::rollback();

                    \Log::error("Error updating content: " . $e->getMessage());
                    Session::flash('error_message','Error al intentar actualizar el contenido');
                    return redirect('contents');
                }
            }else{
                \Log::warning("Content not found");
                Session::flash('error_message', 'Contenido no encontrado');
                return redirect('contents');
            }
        }
    }

    public function destroy($id)
    {
        // if (!$this->user->hasAccess('content.delete')) 
        // {
        //     \Log::error('Unauthorized access attempt',
        //         ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
        //     Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
        //     return redirect('/');
        // }

        $message    = '';
        $error      = '';
        \Log::debug("Intentando elimiar contenido ".$id);

        if ($content = PromotionCategory::find($id))
        {

            $campaña = CampaignDetails::where('contents_id',$id)->count();
            if ($campaña == 0){
                try{
                    \DB::beginTransaction();

                    $contents_has_categories= DB::table('contents_has_categories')
                    ->where('contents_id', '=', $id)
                    ->delete();
                    \Log::info("Contenido id:".$id. " tbl_contents_has_categories, eliminando relacion con categoria");
        
                        
                    if (PromotionCategory::where('id',$id)->delete()){
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
