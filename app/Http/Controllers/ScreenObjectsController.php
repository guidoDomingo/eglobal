<?php

namespace App\Http\Controllers;

use App\Http\Requests\ScreenObjectRequest;
use App\Http\Requests\UpdateScreenObjectRequest;
use App\Models\ObjectPropertyValue;
use App\Models\ObjectType;
use App\Models\ScreenObjects;
use App\Models\Screens;
//use App\Utils\Sanitize;
use Illuminate\Http\Request;
use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Str;
use Input;
use Session;
use Validator;

class ScreenObjectsController extends Controller
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
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function index($screenId, Request $request)
    {
        if (!$this->user->hasAccess('applications.screens.objects')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $name = $request->get('name');
        if ($screenId > -1){
            $screenObjects = ScreenObjects::filterAndPaginate($screenId, $name);
            $data = [
                'objects' => $screenObjects,
                'name' => $name,
                'screen_id' => $screenId,
            ];
            return view('screen_obj.index', $data);
        }else{
            Session::flash('error_message', 'Faltan parametros');
            return redirect('/');
        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        if (!$this->user->hasAccess('applications.screens.objects.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        $screenId = $request->get('screen_id');
        if ($screenId > -1){
            $screens = Screens::where('id', $screenId)->pluck('name', 'id');
            $objects = ObjectType::all()->pluck('name', 'id');
            $data = [
                'screens' => $screens,
                'objects' => $objects,
                'screen_id' => $screenId
            ];
            return view('screen_obj.create', $data);
        }else{
            Session::flash('error_message', 'Faltan parametros');
            return redirect('/');
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param ScreenObjectRequest|Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(ScreenObjectRequest $request)
    {
        if (!$this->user->hasAccess('applications.screens.objects.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        $inputObj = $request->all();
        $inputObj['html'] = $inputObj['hdn_html'];
        $inputObj['version_hash'] = Str::random(40);
        $screenObject = ScreenObjects::create($inputObj);

        /********** DATA FOR RESOURCES PATH **************/
        $screen_id = $request->screen_id;
        $screen = Screens::find($screen_id);
        //La aplicación a la cual pertenece el Objeto
        $app_id = $screen->application_id;
        $object_type_id = $request->get('object_type_id');
        $objectType = ObjectType::find($object_type_id);
        //the object key Ex. button, screen, image
        $objectTypeKey = $objectType->key;
        //the Object's Screen
        $properties = $objectType->objectProperties;

        //Create ObjectPropertyValues
        foreach ($properties as $key => $value) {

            $object_property_id = $value->id;
            $newPropertyValue = [
                'screen_object_id' => $screenObject->id,
                'object_property_id' => $object_property_id,
                'key' => $value->key,
                'value' => "",
            ];


            $objectPropertyValue = ObjectPropertyValue::create($newPropertyValue);
            $properties[$key]['object_property_value_id'] = $objectPropertyValue->id;
        }

        foreach ($properties as $key => $value) {
            if ($value->html_input_type == 'file' /*&&  Input::file($input_name) !== */) {
                $input_name = $value->key;
                $object_property_id = $value->id;
                /**** RESOURCES PATH ****/
                $relative_file_path = $value->file_path . $objectTypeKey . '/';
                $file_path = 'resources/' . $app_id . '/' . $relative_file_path;

                /** VALIDATE INPUT FILE BY MIME **/
                $file = array($input_name => Input::file($input_name));
                // setting up get_required_files()
                $rules = array($input_name => $value->file_input_mime);
                // doing the validation, passing post data, rules and the messages
                $validator = Validator::make($file, $rules);

                if ($validator->fails()) {
                    // send back to the page with the input data and errors
                    return redirect()->route('admin.screenobjs.edit', ['screen_object_id' => $screenObject->id])
                        ->withInput()->withErrors($validator);
                } else {
                    // checking file is valid.
                    if (Input::file($input_name) && Input::file($input_name)->isValid()) {
                        $destinationPath = $file_path; // upload path
                        $extension = Input::file($input_name)->getClientOriginalExtension(); // getting image extension
                        //$friendlyName = Sanitize::string($request->name);
                        $friendlyName = filter_var($request->name, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);;//Sanitize::string($request->name);
                        $friendlyName = strtolower(str_replace(" ", "_", $friendlyName));
                        $fileName = $screen_id . '_' . $screenObject->id . '_' . $objectTypeKey . '_' . 
                            $friendlyName . rand(11111, 99999) . '.' . $extension; // renameing image

                        Input::file($input_name)->move(public_path($destinationPath), $fileName); // uploading file to given path
                        $newPropertyValue = ['value' => $relative_file_path . $fileName];
                        $objectPropertyValue = ObjectPropertyValue::find($value->object_property_value_id);
                        $objectPropertyValue->fill($newPropertyValue);
                        $objectPropertyValue->save();

                    } /*else {
                        // sending back with error message.
                        //Session::flash('error', 'El archivo especificado no es válido.');
                        return redirect()->route('admin.screenobjs.edit', ['screen_object_id' => $screenObject->id]);
                    }*/
                }
            } elseif ($value->html_input_type == 'text') {
                $newPropertyValue = ['value' => $request[$value->key]];
                $objectPropertyValue = ObjectPropertyValue::find($value->object_property_value_id);
                $objectPropertyValue->fill($newPropertyValue);
                $objectPropertyValue->save();

            }
            elseif ($value->html_input_type == 'textarea') {

                $newPropertyValue = ['value' => $inputObj['html']];
                $objectPropertyValue = ObjectPropertyValue::find($value->object_property_value_id);
                $objectPropertyValue->fill($newPropertyValue);
                $objectPropertyValue->save();

            }
            elseif ($value->html_input_type == 'checkbox') {
                if ($request[$value->key] == 'checked') {
                    $checked = 'true';
                } else {
                    $checked = 'false';
                }
                $newPropertyValue = ['value' => $checked];
                $objectPropertyValue = ObjectPropertyValue::find($value->object_property_value_id);
                $objectPropertyValue->fill($newPropertyValue);
                $objectPropertyValue->save();
            }
        }

        Session::flash('success', 'Upload successfully');
        return redirect()->route('screens.screens_objects.index', ['screen_id' => $screen_id]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        if (!$this->user->hasAccess('applications.screens.objects.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }

        if ($screenObject = ScreenObjects::find($id)){
            $objectTypeId = $screenObject->object_type_id;
            $screens = Screens::where('id', $screenObject->screen_id)->pluck('name', 'id');
            $screen = Screens::find($screenObject->screen_id);
            $object = ObjectType::find($objectTypeId);

            $resources_path = '/resources/' . $screen->application_id . '/';

            $data = [
                'screen_name' => $screen->name, //Screen List on Dropdown
                'object_name' => $object->name, //Object List on Dropdown
                'screenObject' => $screenObject, //Current Screen Object
                'properties' => $screenObject->properties, //Property Values from Screen Object
                'resources_path' => $resources_path, //Resources Path of Current Application
                'screen_id' => $screenObject->screen_id
            ];

            return view('screen_obj.edit', $data);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateScreenObjectRequest|Request $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateScreenObjectRequest $request, $id)
    {
        if (!$this->user->hasAccess('applications.screens.objects.add|edit')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }


        $inputObj = $request->all();
        $inputObj['html'] = $inputObj['hdn_html'];
        $inputObj['version_hash'] = Str::random(40);
        if ($screenObject = ScreenObjects::find($id)){
            $screenObject->fill($inputObj);
            $screenObject->save();

            //TODO: Se guarda en la tabla de actualizaciones el objeto a ser cambiado.

            /********** DATA FOR RESOURCES PATH **************/
            //Get Screen Data for building resources Path
            $screen_id = $screenObject->screen_id;
            $screen = Screens::find($screen_id);
            //Screen App ID
            $app_id = $screen->application_id;
            //Getting ObjectType->key
            $object_type_id = $screenObject->object_type_id;
            $objectType = ObjectType::find($object_type_id);
            //the object key Ex. button, screen, image, para armar el path
            $objectTypeKey = $objectType->key;
            /********* DATA FOR RESOURCES PATH ************/

            //objectPropertiesValues from screenObject
            $properties = $screenObject->properties;


            foreach ($properties as $key => $value) {
                \Log::debug('update :'. $request[$value->objectProperty->key]);
                // Asks for html component type [file,text,checkbox]
                if ($value->objectProperty->html_input_type == 'file') {
                    //the input html "name"
                    $input_name = $value->objectProperty->key;
                    //the corresponding objectProperty->id
                    $object_property_id = $value->objectProperty->id;

                    /**** RESOURCES PATH ****/
                    $relative_file_path = $value->objectProperty->file_path . $objectTypeKey . '/';
                    $file_path = 'resources/' . $app_id . '/' . $relative_file_path;

                    /** VALIDATE INPUT FILE BY MIME **/
                    $file = array($input_name => Input::file($input_name));
                    // setting up get_required_files()
                    $rules = array($input_name => $value->objectProperty->file_input_mime);

                    // doing the validation, passing post data, rules and the messages
                    $validator = Validator::make($file, $rules);
                    if ($validator->fails()) {
                        // send back to the page with the input data and errors
                        Session::flash('error', 'El archivo especificado no es válido.');
                        return redirect()->route('admin.screenobjs.edit', ['screen_object_id' => $screenObject->id])->withInput()->withErrors($validator);
                    } elseif (Input::file($input_name)) {
                        // checking file is valid.
                        if (Input::file($input_name)->isValid()) {
                            $destinationPath = $file_path; // upload path
                            $extension = Input::file($input_name)->getClientOriginalExtension(); // getting image extension
                            $friendlyName = filter_var($request->name, FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);;//Sanitize::string($request->name);
                            $friendlyName = strtolower(str_replace(" ", "_", $friendlyName));
                            /** BUILDING FILENAME **/
                            $fileName = $screen_id . '_' . $screenObject->id . '_' . $objectTypeKey . '_' . $friendlyName . rand(11111, 99999) . '.' . $extension;
                            // uploading file to given path
                            Input::file($input_name)->move(public_path($destinationPath), $fileName);
                            //DELETE OLD FILE
                            $old_file_path = 'resources/' . $app_id . '/' . $value->value;
                            if (file_exists($old_file_path) and $value->value != "") {
                                unlink($old_file_path);
                            }
                            /** UPDATE object_properties_values **/
                            $updatedPropertyValue = ['value' => $relative_file_path . $fileName];
                            $objectPropertyValue = ObjectPropertyValue::find($value->id);
                            $objectPropertyValue->fill($updatedPropertyValue);
                            $objectPropertyValue->save();



                        } else {
                            // sending back with error message.
                            Session::flash('error', 'El archivo especificado no es válido.');
                            return redirect()->route('screens_objects.edit', ['screen_object_id' => $screenObject->id]);
                        }

                    }
                } elseif ($value->objectProperty->html_input_type == 'text') {
                    //TODO: Validar por $value->objectProperty->constrains con regex
                    /*if(strlen($value->objectProperty->constraints) >0){
                    if($pos = strpos($value->objectProperty->constraints, 'regex');)
                    $file = array($input_name => Input::file($input_name));
                    // setting up get_required_files()
                    $rules = array($input_name => $value->objectProperty->file_input_mime);
    
                    // doing the validation, passing post data, rules and the messages
                    $validator = Validator::make($file, $rules);
                    if ($validator->fails()) {
    
                    }else{
    
                    }
                    }else{
    
                    }
                    }
                     */

                    $newPropertyValue = ['value' => $request[$value->objectProperty->key]];
                    $objectPropertyValue = ObjectPropertyValue::find($value->id);
                    $objectPropertyValue->fill($newPropertyValue);
                    $objectPropertyValue->save();
                }
                elseif ($value->objectProperty->html_input_type == 'textarea') {

                    $newPropertyValue = ['value' => $inputObj['html']];
                    $objectPropertyValue = ObjectPropertyValue::find($value->id);
                    $objectPropertyValue->fill($newPropertyValue);
                    $objectPropertyValue->save();
                }
                elseif ($value->objectProperty->html_input_type == 'checkbox') {
                    if ($request[$value->objectProperty->key] == 'checked') {
                        $checked = 'true';
                    } else {
                        $checked = 'false';
                    }
                    $newPropertyValue = ['value' => $checked];
                    $objectPropertyValue = ObjectPropertyValue::find($value->id);
                    $objectPropertyValue->fill($newPropertyValue);
                    $objectPropertyValue->save();
                }
            }
            $message = 'Actualizado correctamente';
            Session::flash('message', $message);
            //return redirect()->route('screens_objects.edit', ['screen_object_id' => $screenObject->id]);
            return redirect()->route('screens.screens_objects.index', ['screen_id' => $screen_id]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function destroy($id, Request $request)
    {
        if (!$this->user->hasAccess('applications.screens.objects.delete')) {
            \Log::error('Unauthorized access attempt',
                ['user' => $this->user->username, 'route' => \Request::route()->getActionName()]);
            Session::flash('error_message', 'No tiene los permisos para realizar esta operacion');
            return redirect('/');
        }
        
        if ($screenObject = ScreenObjects::find($id)){
            $screen_id = $screenObject->screen_id;
            $screen = Screens::find($screen_id);
            //Screen App ID
            $app_id = $screen->application_id;
            //Getting ObjectType->key
            $object_type_id = $screenObject->object_type_id;
            $objectType = ObjectType::find($object_type_id);
            //the object key Ex. button, screen, image, para armar el path
            $objectTypeKey = $objectType->key;
            /********* DATA FOR RESOURCES PATH ************/

            //objectPropertiesValues from screenObject
            $properties = $screenObject->properties;
            foreach ($properties as $key => $value) {
                // Asks for html component type [file,text,checkbox]
                if ($value->objectProperty->html_input_type == 'file') {
                    $old_file_path = 'resources/' . $app_id . '/' . $value->value;
                    if (file_exists($old_file_path) and $value->value != "") {
                        @unlink($old_file_path);
                    }
                }
            }
            if ($screenObject->delete()) {
                $error = false;
                $message = $screenObject->name . ' ha sido eliminado correctamente';
            } else {
                $error = true;
                $message = $screenObject->name . " no se pudo eliminar";
            }
            if ($request->ajax()) {
                return response()->json([
                    'error' => $error,
                    'message' => $message,
                ]);
            } else {
                Session::flash('message', $message);
                return redirect()->route('admin.screens.index');
            }
        }
    }
}
