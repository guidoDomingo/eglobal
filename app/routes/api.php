<?php

use App\Http\Controllers\PermissionsController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/pruebalaravel', function(){
    // $connection = \DB::connection();
    // $databaseName = $connection->getDatabaseName();
    // $config = $connection->getConfig();
    // $host = $config['host'];

    // return  "La conexión actual está apuntando a la base de datos: " . $databaseName . " server: ". $host;

    $user = \Cartalyst\Sentinel\Laravel\Facades\Sentinel::getUser();

    return [$user];
});

Route::get('/userpermissions', [PermissionsController::class, 'userpermissions']);