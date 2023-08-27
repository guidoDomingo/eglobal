<?php

namespace App\Http\Controllers;

use App\Models\ObjectType;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Routing\Route;

class ObjectTypeController extends Controller
{
    protected $user;

    public function __construct()
    {
        $this->middleware('auth');
        $this->user = \Sentinel::getUser();
    }

    public function properties(Route $route, Request $request)
    {
        $message = 'No se encontó el registro solicitado';
        if (!$this->object = ObjectType::findOrFail($route->getParameter('object_id'))) {
            if ($request->ajax()) {
                return response()->json([
                    'error' => "true",
                    'message' => $message,
                ]);
            }
        }
        return $this->object->objectProperties;
    }
}
