<?php
use Carbon\Carbon;
/*
|----------------------------------------------------------------------------------------------------------------------+
| Authentication & Authorization Routes - Sentinel Implementation                                                      |
|----------------------------------------------------------------------------------------------------------------------+
*/

use App\Services\DepositosTerminalesServices;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;

Route::get('login', ['as' => 'login.page', 'uses' => 'Controllers\Auth\AuthController@loginPage']);
Route::post('login', ['as' => 'login', 'uses' => 'Controllers\Auth\AuthController@login']);
Route::get('logout', ['as' => 'logout', 'uses' => 'Controllers\Auth\AuthController@logout']);
Route::get('logout/{id}', ['as'   => 'users.force.logout', 'uses' => 'Controllers\Auth\AuthController@forceLogout']);
Route::get('reset/{id}/request', ['as' => 'reset.password.request', 'uses' => 'Controllers\Auth\AuthController@resetPasswordRequest']);
Route::get('reset/{id}/{code}', ['as' => 'reset.password.page', 'uses' => 'Controllers\Auth\AuthController@resetPasswordPage']);
Route::post('reset', ['as' => 'reset.password', 'uses' => 'Controllers\Auth\AuthController@resetPassword']);
Route::post('baneo', ['as'   => 'users.baneo', 'uses' => 'Controllers\UsersController@banuser']);

/*
|----------------------------------------------------------------------------------------------------------------------+
| Departamentos Routes - Zonas Geográficas                                                                               |
+--------+----------+------------------------------+----------------------+--------------------------------------------+
*/
Route::resource('departamentos', 'Controllers\DepartamentoController');
Route::resource('ciudades', 'Controllers\CiudadesController');
Route::resource('barrios', 'Controllers\BarriosController');

/*
|----------------------------------------------------------------------------------------------------------------------+
| Roles Routes - Sentinel Implementation                                                                               |
+--------+----------+------------------------------+----------------------+--------------------------------------------+
*/
Route::resource('roles', 'Controllers\RolesController');

/**
 * Ruta para informe de Roles
 */
Route::match(['get', 'post'], 'roles_report', [
    'as' => 'roles_report',
    'uses' => 'Controllers\RolesReportController@index_report'
]);

Route::post('get_roles_permissions', ['as' => 'get_roles_permissions', 'uses' => 'Controllers\RolesReportController@get_roles_permissions']);

/*
|----------------------------------------------------------------------------------------------------------------------+
| Permissions Routes - Sentinel Implementation                                                                         |
+--------+----------+------------------------------+--------------------------+----------------------------------------+
*/
Route::resource('permissions', 'Controllers\PermissionsController');
/*
|----------------------------------------------------------------------------------------------------------------------+
| Home and Dashboard Routes                                                                                            |
|----------------------------------------------------------------------------------------------------------------------+
*/
Route::get('/', ['as' => 'home', 'uses' => 'Controllers\HomeController@index']);

/*
|----------------------------------------------------------------------------------------------------------------------+
| ANALITICA NUEVA PLANTILLA                                                                                          |
|----------------------------------------------------------------------------------------------------------------------+
*/
Route::get('/analitica', [HomeController::class, 'analitica'])->name('analitica');




/*
|----------------------------------------------------------------------------------------------------------------------+
| User Routes                                                                                                          |
|----------------------------------------------------------------------------------------------------------------------+
*/

Route::resource('users', 'Controllers\UsersController');
Route::get('users/{id}/profile', ['as' => 'users.showProfile', 'uses' => 'Controllers\UsersController@show']);
Route::get('activate/{id}/{code}', ['as'   => 'users.activate', 'uses' => 'Controllers\UsersController@activate']);
Route::get('resend/{id}/activation', ['as' => 'resend.activation.request', 'uses' => 'Controllers\UsersController@resendActivation']);

/**
 * Funcion para reseteo de claves masivo
 */
//use App\Models\User;
//use Illuminate\Support\Facades\Mail;

/*Route::get('reset_all_password',function(){
    $users = \DB::table('users')
    ->select('users.id','username', 'email')
    ->join('role_users','users.id','=','role_users.user_id')
    ->whereNotIn('role_id',[22,24])
    ->whereNull('deleted_at')
    ->where('suspended',false)
    ->where('banned',false)
    //->whereIn('user_id',[3,4,14,131,132])
    //->whereIn('user_id',[3])
    ->groupBy('users.id','username','email')
    ->orderBy('users.id','asc')    
    ->get();
    
    $i = 0;

    foreach($users as $user)
    {
        $unique = substr(base64_encode(mt_rand()), 0, 15);

        $usuario = User::find($user->id);
        $usuario->password = $unique;
        $usuario->save();

        $i++;

        $resetCode = $usuario->getResetPasswordCode();

        try
        {
            \Log::info('Usuarios',['email'=>$usuario->email, 'username'=>$usuario->username]);
            Mail::send(
                'mails.reset_password',
                [
                    'user' => $usuario,
                    'link' => route('reset.password.page', [
                        'id'   => $usuario->id,
                        'code' => $resetCode
                    ])
                ],
                function ($message) use ($usuario) {
                    $message
                        ->to($usuario->email, ucfirst($usuario->username))
                        ->subject('[EGLOBALT] Reestablecer Contraseña');
                }
            );
        }catch(\Exception $e)
        {
            \Log::warning('Error',['result'=>$e]);
        }
        
    }

    return "$i filas afectadas";
});*/
/*
|----------------------------------------------------------------------------------------------------------------------+
| ATM Management Routes                                                                                                |
|----------------------------------------------------------------------------------------------------------------------+
*/
// Gooddeal last update
Route::get('atm/gooddeals', ['as' => 'gooddeals.update', 'uses' => 'Controllers\AtmController@updateGooddeals']);
Route::get('atm/downloadImagesPromotions', ['as' => 'gooddeals.downloadImages', 'uses' => 'Controllers\AtmController@downloadImagesPromotions']);
Route::get('atm/get_last_update', ['as' => 'gooddeals.get_last_update', 'uses' => 'Controllers\AtmController@getLastUpdateGooddeals']);
Route::post('atm/last_update_gooddeals', ['as' => 'gooddeals.last_update', 'uses' => 'Controllers\AtmController@lastUpdateGooddeals']);
Route::get('atm/form_step', ['as' => 'atm.form_step', 'uses' => 'Controllers\AtmController@formStep']);
Route::get('atm/ciudades', ['as' => 'atm.ciudades', 'uses' => 'Controllers\AtmController@getCiudades']);
Route::get('atm/barrios', ['as' => 'atm.barrios', 'uses' => 'Controllers\AtmController@getBarrios']);
Route::get('atm/check_code', ['as' => 'atm.check_code', 'uses' => 'Controllers\AtmController@checkCode']);
Route::get('atm/{id}/params', ['as' => 'atm.params', 'uses' => 'Controllers\AtmController@params']);
Route::get('atm/{id}/parts', ['as' => 'atm.parts', 'uses' => 'Controllers\AtmController@parts']);
Route::post('atm/{id}/param_store', ['as' => 'atm.param_store', 'uses' => 'Controllers\AtmController@paramStore']);
Route::post('atm/{id}/parts_update', ['as' => 'atm.parts_update', 'uses' => 'Controllers\AtmController@partsUpdate']);
Route::get('atm/{id}/check_key', ['as' => 'atm.check_key', 'uses' => 'Controllers\AtmController@checkKey']);
Route::resource('atm', 'Controllers\AtmController');

//ATMs Listado de ATMS

Route::match(['get', 'post'], 'atm_index', [
    'as' => 'atm_index',
    'uses' => 'Controllers\AtmController@index'
]);

Route::post('atm/newhash', ['as' => 'atm.newhash', 'uses' => 'Controllers\AtmController@generateHash']);
Route::get('atm/{id}/screens', ['as' => 'atm.flows', 'uses' => 'Controllers\AtmController@getApplicationInterface']);
Route::get('atm/{id}/housing', ['as' => 'atm.housing', 'uses' => 'Controllers\AtmController@housing']);
Route::post('atm/{id}/housing/store', ['as' => 'atm.housing.store', 'uses' => 'Controllers\AtmController@store_housing']);

Route::post('atm/reactivate', 'Controllers\AtmController@Procesar_reactivacion');
Route::post('atm/arqueo_remoto', 'Controllers\AtmController@enable_arqueo_remoto');
Route::post('atm/grilla_tradicional', 'Controllers\AtmController@enable_grilla_tradicional');

//Eliminar atms
Route::get('atm/{id}/delete', ['as' => 'atm.delete', 'uses' => 'Controllers\AtmController@delete']);

//Modificar el Block-Type del ATM
Route::post('atm/block_type_change', 'Controllers\AtmController@block_type_change');

//ATM Credentials manager

Route::resource('atm.credentials', 'Controllers\AtmServicesCredentialsController');

/*
|----------------------------------------------------------------------------------------------------------------------+
| CRUD UPDATES DE APLICACION DE ATM
|----------------------------------------------------------------------------------------------------------------------+
 */

Route::resource('app_updates', 'Controllers\AppUpdatesController');



/*
|----------------------------------------------------------------------------------------------------------------------+
| Marcas Management Routes                                                                                              |
|----------------------------------------------------------------------------------------------------------------------+
*/
Route::get('marca/grilla_servicios', ['as' => 'marca.grilla_servicios', 'uses' => 'Controllers\MarcasController@grilla_servicios']);
Route::post('marca/grilla_servicios', ['as' => 'marca.grilla_servicios_store', 'uses' => 'Controllers\MarcasController@grilla_servicios_store']);
Route::post('marca/grilla_servicios_atm', ['as' => 'marca.grilla_servicios_atm_store', 'uses' => 'Controllers\MarcasController@grilla_servicios_atm_store']);
Route::get('marca/quitar_marca_atm/{marca_id}/{atm_id}', ['as' => 'marca.quitar_marca_atm', 'uses' => 'Controllers\MarcasController@quitarMarcaAtm']);
Route::post('marca/activar_marca', ['as' => 'marca.activar_marca', 'uses' => 'Controllers\MarcasController@activar_marca']);
Route::get('marca/order', ['as' => 'marca.orderget', 'uses' => 'Controllers\MarcasController@order']);
Route::post('marca/order', ['as' => 'marca.order', 'uses' => 'Controllers\MarcasController@order']);
Route::get('marca/get_by_category', ['as' => 'marca.get_by_category', 'uses' => 'Controllers\MarcasController@get_by_category']);
Route::resource('marca', 'Controllers\MarcasController');

/*
|----------------------------------------------------------------------------------------------------------------------+
| Usuarios Bahia Management Routes                                                                                              |
|----------------------------------------------------------------------------------------------------------------------+
*/
Route::resource('usuarios_bahia', 'Controllers\UsuariosBahiaController');

/*
|----------------------------------------------------------------------------------------------------------------------+
| Servicios por marcas Management Routes                                                                                              |
|----------------------------------------------------------------------------------------------------------------------+
*/

Route::get('servicios_marca/{service_id}/{service_source_id}/edit', ['as' => 'servicios_marca.edit', 'uses' => 'Controllers\ServiciosMarcasController@edit']);
Route::put('servicios_marca/{service_id}/{service_source_id}/update', ['as' => 'servicios_marca.update', 'uses' => 'Controllers\ServiciosMarcasController@update']);
Route::delete('servicios_marca/{service_id}/{service_source_id}/destroy', ['as' => 'servicios_marca.destroy', 'uses' => 'Controllers\ServiciosMarcasController@destroy']);
Route::resource('servicios_marca', 'Controllers\ServiciosMarcasController')->except(['edit','update','destroy']);



/*
|----------------------------------------------------------------------------------------------------------------------+
| Parametros de Notificaciones Management Routes                                                                                              |
|----------------------------------------------------------------------------------------------------------------------+
*/
Route::resource('notifications_params', 'Controllers\NotificationsParamsController');
//Route::get('notifications_params/{id}/edit', ['as' => 'notifications_params.edit', 'uses' => 'NotificationsParamsController@edit']);
//Route::put('notifications_params/{id}/update', ['as' => 'notifications_params.update', 'uses' => 'NotificationsParamsController@update']);
Route::get('notifications_params/{id}/duplicate', ['as' => 'notifications_params.duplicate', 'uses' => 'Controllers\NotificationsParamsController@duplicate']);
//Route::delete('notifications_params/{id}/destroy', ['as' => 'notifications_params.destroy', 'uses' => 'NotificationsParamsController@destroy']);


/*
|----------------------------------------------------------------------------------------------------------------------+
| Owner Management Routes                                                                                              |
|----------------------------------------------------------------------------------------------------------------------+
*/
Route::resource('owner', 'Controllers\OwnerController');

/*
|----------------------------------------------------------------------------------------------------------------------+
| Branches Management Routes                                                                                           |
|----------------------------------------------------------------------------------------------------------------------+
*/
Route::resource('branches', 'Controllers\BranchController');
Route::resource('owner.branches', 'Controllers\BranchController');

/*
|----------------------------------------------------------------------------------------------------------------------+
| Pos Management Routes                                                                                                |
|----------------------------------------------------------------------------------------------------------------------+
*/
Route::resource('pos', 'Controllers\PosController');
Route::get('pos/{id}/atm', ['as' => 'pos.atm.show.assign', 'uses' => 'Controllers\PosController@showAssign']);
Route::post('pos/{id}/atm', ['as' => 'pos.atm.assign', 'uses' => 'Controllers\PosController@assignAtm']);

/*
|----------------------------------------------------------------------------------------------------------------------+
| Pos Voucher Types Management Routes                                                                                  |
|----------------------------------------------------------------------------------------------------------------------+
*/
Route::resource('vouchers', 'Controllers\VoucherTypeController');
Route::resource('pointsofsale.vouchertypes', 'Controllers\PointOfSaleVoucherTypeController');
Route::resource('pointsofsale.vouchers', 'Controllers\PointOfSaleVoucherController');
/*
|----------------------------------------------------------------------------------------------------------------------+
| Providers Management Routes                                                                                          |
|----------------------------------------------------------------------------------------------------------------------+
*/
Route::resource('providers', 'Controllers\ProvidersController');

/*
|----------------------------------------------------------------------------------------------------------------------+
| Products Management Routes                                                                                           |
|----------------------------------------------------------------------------------------------------------------------+
*/
Route::resource('products', 'Controllers\ProductsController');

/*
|----------------------------------------------------------------------------------------------------------------------+
| Outcomes Management Routes                                                                                           |
|----------------------------------------------------------------------------------------------------------------------+
*/
Route::resource('outcome', 'Controllers\OutcomeController');

/*
|----------------------------------------------------------------------------------------------------------------------+
| Outcomes Management Routes                                                                                           |
|----------------------------------------------------------------------------------------------------------------------+
*/
Route::resource('applications', 'Controllers\ApplicationsController');
Route::post('applications/{id}/assign_atm', [
    'as' => 'applications.assign_atm',
    'uses' => 'Controllers\ApplicationsController@assignAtm'
]);
Route::delete('applications/{id}/delete_assign_atm', [
    'as' => 'applications.delete_assigned_atm',
    'uses' => 'Controllers\ApplicationsController@removeAssignedAtm'
]);

/*
|----------------------------------------------------------------------------------------------------------------------+
| Applications Screen Management Routes                                                                                |
|----------------------------------------------------------------------------------------------------------------------+
*/
Route::resource('screens', 'Controllers\ScreenController');
Route::resource('applications.screens', 'Controllers\ScreenController');
Route::resource('applications.versions', 'Controllers\AppVersionController');
Route::get('providers_group/{group}', 'Controllers\AppVersionController@getproviderGroups');
Route::get('/applications/versions/update/{id}', ['as' => 'app_current_version',  'uses' => 'Controllers\AppVersionController@UpdateCurrentVersion', 'permission' => 'update']);

/*
|----------------------------------------------------------------------------------------------------------------------+
| Applications params Management routes                                                                                |
|----------------------------------------------------------------------------------------------------------------------+
*/
Route::resource('applications.params', 'Controllers\ParamController');



/*
|----------------------------------------------------------------------------------------------------------------------+
| Applications ScreenObjects Management Routes                                                                         |
|----------------------------------------------------------------------------------------------------------------------+
*/
Route::resource('screens_objects', 'Controllers\ScreenObjectsController');
Route::resource('screens.screens_objects', 'Controllers\ScreenObjectsController');
/*
|----------------------------------------------------------------------------------------------------------------------+
| ObjectType properties                                                                                                |
|----------------------------------------------------------------------------------------------------------------------+
*/
Route::get('object_properties/{object_id}/', [
    'as' => 'object_types.properties',
    'uses' => 'Controllers\ObjectTypeController@properties'
]);

/*
|----------------------------------------------------------------------------------------------------------------------+
| Webservices Management routes
|----------------------------------------------------------------------------------------------------------------------+
 */
Route::resource('wsproviders', 'Controllers\ServiceProviderController');
Route::resource('webservices', 'Controllers\WebServiceController');
Route::resource('webservicerequests', 'Controllers\WebServiceRequestController');
Route::get('webservices/status/{id}', ['as' => 'services_status', 'uses' => 'Controllers\WebServiceController@UpdateServiceStatus', 'permission' => 'update']);


/*
|----------------------------------------------------------------------------------------------------------------------+
| Webservices Products / operations Management routes
|----------------------------------------------------------------------------------------------------------------------+
 */
Route::resource('wsproducts', 'Controllers\ServiceProviderProductController');
Route::resource('wsproducts.wsbuilder', 'Controllers\WebServiceBuilderController');
Route::resource('wsproducts.models', 'Controllers\WebServiceModelController')->except(['delete']);
//Route::resource('wsproducts.models', 'Controllers\WebServiceModelController');
Route::post('wsproducts/models/delete/{collection}', ['as' => 'wsproducts.models.delete', 'uses' => 'Controllers\WebServiceModelController@destroy']);
Route::resource('wsproducts.wsbuilder.views', 'Controllers\WebServiceViewsBuilderController');


/*
|----------------------------------------------------------------------------------------------------------------------+
| Centralita de monitoreo / Graylog Dashboard
|----------------------------------------------------------------------------------------------------------------------+
 */
Route::resource('monitoreo', 'Controllers\MonitoringController');

/*
|----------------------------------------------------------------------------------------------------------------------+
| Centralita de monitoreo / Red Eglobal Dashboard
|----------------------------------------------------------------------------------------------------------------------+
 */
Route::post('dashboard/{collection}', 'Controllers\DashboardController@monitoringCollections');
Route::post('notifications/get-notifications', 'Controllers\DashboardController@getNotificationsbyUsers');
Route::get('dashboard/balances', ['as' => 'dashboard.balances', 'uses' => 'Controllers\DashboardController@balancesDetails']);
//Route::get('dashboard/alerts','Controllers\DashboardController@alertsDetails');
Route::get('dashboard/conciliations', 'Controllers\DashboardController@conciliationsDetails');
Route::get('dashboard/atms_detalles/{status}/{red}', ['as' => 'dashboard.atms_detalle', 'uses' => 'Controllers\DashboardController@getAtmDetalles']);
Route::get('dashboard/detalle_cantidad_minima/{atm_id}', ['as' => 'dashboard.detalle_cantidad_minima', 'uses' => 'Controllers\DashboardController@getDetallesCantidades']);
 

/*
|----------------------------------------------------------------------------------------------------------------------+
| Reportes
|----------------------------------------------------------------------------------------------------------------------+
 */
/** Transacciones*/
Route::get('reports/transactions', ['as' => 'reports.transactions', 'uses' => 'Controllers\ReportingController@transactionsReports']);
Route::post('reports/transactions/procesar_devolucion', 'Controllers\ReportingController@Procesar_devolucion');
Route::post('reports/transactions/reprocesar_transaccion', 'Controllers\ReportingController@Reprocesar_transaccion');
Route::post('reports/transactions/inconsistencia', 'Controllers\ReportingController@generar_inconsistencia');
Route::post('reports/transactions/reversion', 'Controllers\ReportingController@generar_reversion');
Route::get('reports/transactions/search', ['as' => 'reports.transactions.search', 'uses' => 'Controllers\ReportingController@transactionSearch']);
/** Transacciones Batch **/
Route::get('reports/batch_transactions', ['as' => 'reports.batch_transactions', 'uses' => 'Controllers\ReportingController@batchTransactionsReports']);
Route::get('reports/batch_transactions/search', ['as' => 'reports.batch_transactions.search', 'uses' => 'Controllers\ReportingController@batchTransactionSearch']);
Route::post('reports/batch_transactions/reprocess', 'Controllers\ReportingController@batchTransactionReprocess');
Route::post('reports/batch_transactions/reprocess_manually', 'Controllers\ReportingController@batchTransactionManualReprocess');
Route::get('reports/get_service_request', ['as' => 'reports.get_service_request', 'uses' => 'Controllers\ReportingController@getServiceRequest']);
Route::get('reports/get_service_request_all', ['as' => 'reports.get_service_request_all', 'uses' => 'Controllers\ReportingController@getServiceRequestAll']);
Route::get('reports/get_service_request_param', ['as' => 'reports.get_service_request_param', 'uses' => 'Controllers\ReportingController@getServiceRequestParam']);

/** REPORTS CLARO */
Route::get('claro/transactions', ['as' => 'claro.transactions', 'uses' => 'Controllers\ReportingController@claro_transactionsReports']);
Route::get('claro/transactions/search', ['as' => 'claro.transactions.search', 'uses' => 'Controllers\ReportingController@claro_transactionSearch']);
/** Fin reports claro */

Route::post('reports/transactions/get_points_of_sale', 'Controllers\ReportingController@get_points_of_sale');


Route::post('reports/transactions/jsons_transaction', 'Controllers\ReportingController@jsons_transaction');
Route::post('reports/transactions/jsons_transaction_requests', 'Controllers\ReportingController@jsons_transaction_requests');
Route::post('reports/transactions/jsons_service', 'Controllers\ReportingController@jsons_service');
Route::post('reports/transactions/transaction_ticket', 'Controllers\ReportingController@transaction_ticket');


/** One Day Transactions */
Route::get('reports/one_day_transactions', ['as' => 'reports.one_day_transactions', 'uses' => 'Controllers\ReportingController@oneDayTransactionsReports']);
Route::get('reports/one_day_transactions/search', ['as' => 'reports.one_day_transactions.search', 'uses' => 'Controllers\ReportingController@oneDayTransactionsSearch']);

/** Resumen por Atm*/
Route::get('reports/resumen_transacciones', ['as' => 'reports.resumen_transacciones', 'uses' => 'Controllers\ReportingController@resumenTransacciones']);
Route::get('reports/resumen_search', ['as' => 'reports.resumen.search', 'uses' => 'Controllers\ReportingController@resumenSearch']);
Route::get('reports/resumen_detalle_export', ['as' => 'reports.resumen.detalle_export', 'uses' => 'Controllers\ReportingController@resumenSearchDetalleExport']);

/** One Day Transactions */
Route::get('reports/transactions_vuelto', ['as' => 'reports.transactions_vuelto', 'uses' => 'Controllers\ReportingController@transactionsVueltoReports']);
Route::get('reports/transactions_vuelto/search', ['as' => 'reports.transactions_vuelto.search', 'uses' => 'Controllers\ReportingController@transactionVueltoSearch']);


/** Estado por Atm*/
Route::get('reports/estado_atm', ['as' => 'reports.estado_atm', 'uses' => 'Controllers\ReportingController@estadoAtm']);
Route::get('reports/estado_atm_search', ['as' => 'reports.estado_atm.search', 'uses' => 'Controllers\ReportingController@estadoAtmSearch']);
// Route::get('reports/resumen_detalle_export',['as'=>'reports.resumen.detalle_export','uses' =>'Controllers\ReportingController@resumenSearchDetalleExport']);

/** Transactions amount*/
Route::get('reports/transactions_amount', ['as' => 'reports.transactions_amount', 'uses' => 'Controllers\ReportingController@transactionsAmountReports']);
Route::get('reports/transactions_amount_search', ['as' => 'reports.transactions_amount.search', 'uses' => 'Controllers\ReportingController@transactionsAmountSearch']);

/** Transactions atm*/
Route::get('reports/transactions_atm', ['as' => 'reports.transactions_atm', 'uses' => 'Controllers\ReportingController@transactionsAtmReports']);
Route::get('reports/transactions_atm_search', ['as' => 'reports.transactions_atm.search', 'uses' => 'Controllers\ReportingController@transactionsAtmSearch']);

/** Denominaciones Amount*/
Route::get('reports/denominaciones_amount', ['as' => 'reports.denominaciones_amount', 'uses' => 'Controllers\ReportingController@denominacionesAmountReports']);
Route::get('reports/denominaciones_amount_search', ['as' => 'reports.denominaciones_amount.search', 'uses' => 'Controllers\ReportingController@denominacionesAmountSearch']);

/** Payments Reports*/
Route::get('reports/payments', ['as' => 'reports.payments', 'uses' => 'Controllers\ReportingController@paymentsReports']);
Route::get('reports/payments/search', ['as' => 'reports.payments.search', 'uses' => 'Controllers\ReportingController@paymentsSearch']);

/** Arqueos*/
Route::get('reports/arqueos', ['as' => 'reports.arqueos', 'uses' => 'Controllers\ReportingController@arqueosReports']);
Route::get('reports/arqueos/search', ['as' => 'reports.arqueos.search', 'uses' => 'Controllers\ReportingController@arqueosSearch']);
/** Cargas*/
Route::get('reports/cargas', ['as' => 'reports.cargas', 'uses' => 'Controllers\ReportingController@cargasReports']);
Route::get('reports/cargas/search', ['as' => 'reports.cargas.search', 'uses' => 'Controllers\ReportingController@cargasSearch']);
/** Saldos*/
Route::get('reports/saldos', ['as' => 'reports.saldos', 'uses' => 'Controllers\ReportingController@saldosReports']);
Route::get('reports/saldos/search', ['as' => 'reports.saldos.search', 'uses' => 'Controllers\ReportingController@saldosSearch']);
Route::get('reports/saldos/detalles/{owner_id}/{branch_id}', ['as' => 'reports.saldos.details', 'uses' => 'Controllers\ReportingController@saldosDetails']);
Route::post('reports/saldos/export', ['as' => 'reports.saldos.export', 'uses' => 'Controllers\ReportingController@saldosexport']);

/** Historico saldos en línea */
Route::get('saldos/contable', ['as' => 'saldos.contable', 'uses' => 'Controllers\ReportingController@saldos_control_contable']);
Route::get('saldos/contable/search', ['as' => 'saldos.contable.search', 'uses' => 'Controllers\ReportingController@saldos_control_contable_search']);

/** Notificaciones*/
Route::get('reports/notifications', ['as' => 'reports.notifications', 'uses' => 'Controllers\ReportingController@notificationsReports']);
Route::get('reports/notifications/search', ['as' => 'reports.notifications.search', 'uses' => 'Controllers\ReportingController@notificationsSearch']);

/** Dispositivos Report*/
Route::get('reports/dispositivos', ['as' => 'reports.dispositivos', 'uses' => 'Controllers\ReportingController@dispositivosReports']);
Route::get('reports/dispositivos/search', ['as' => 'reports.dispositivos.search', 'uses' => 'Controllers\ReportingController@dispositivosSearch']);

/*Cascading dropdowns for reports*/
Route::get('/reports/ddl/owners/{group_id}', 'Controllers\ReportingController@getOwnersbyGroups');
Route::get('/reports/ddl/branches/{group_id}', 'Controllers\ReportingController@getBranchesbyGroups');
Route::get('/reports/ddl/branches/{group_id}/{owner_id}', 'Controllers\ReportingController@getBranchesbyOwners');
Route::get('/reports/ddl/pdv/{branch_id}', 'Controllers\ReportingController@getPdvsbyBranches');
/*Printing tickets from reports*/
Route::get('/reports/info/tickets/{id}', 'Controllers\ReportingController@getTransactionsTickets');
Route::get('/get_report_claro', 'Controllers\ReportingController@getTransactions');
Route::get('/reports/info/details/{id}', 'Controllers\ReportingController@getTransactionsDetails');
/*Geting data for payments details*/
Route::get('/reports/info/payments_data/{id}', 'Controllers\ReportingController@getPaymentsDetails');
Route::get('/reports/info/reversion_data/{id}', 'Controllers\ReportingController@getReversionDetails');
/*Geting data for batch transactions details*/
Route::get('/reports/info/batch_transaction_data/{id}', 'Controllers\ReportingController@getBatchDetails');
/*Geting data for payments details*/
Route::get('/reports/info/payment_data/{id}', 'Controllers\ReportingController@getPaymentDetails');
/*Geting data for atm notifications*/
Route::get('/reports/info/atm_notification/{atm_id}', 'Controllers\ReportingController@getAtmNotification');

/** One Day Transactions */
Route::get('reports/vuelto_entregado', ['as' => 'reports.vuelto_entregado', 'uses' => 'Controllers\ReportingController@transactionsVueltoCorrectoReports']);
Route::get('reports/vuelto_entregado/search', ['as' => 'reports.vuelto_entregado.search', 'uses' => 'Controllers\ReportingController@transactionVueltoCorrectoSearch']);

/** Estado de Instalaciones / APP BILLETAJE */
Route::get('reports/installations', ['as' => 'reports.installations', 'uses' => 'Controllers\ReportingController@statusInstallations']);
Route::get('reports/installations/search', ['as' => 'reports.installations.search', 'uses' => 'Controllers\ReportingController@statusInstallationsSearch']);

Route::get('practica',['as' => 'practica', 'uses' => 'Controllers\practicaController@lista']);

/*
|----------------------------------------------------------------------------------------------------------------------+
| Deposito Boleta Management Routes                                                                                  |
|----------------------------------------------------------------------------------------------------------------------+
*/
Route::resource('depositos_boletas', 'Controllers\DepositoBoletaController');
Route::get('/reports/deposito_boletas/cuentas/{banco_id}', 'Controllers\DepositoBoletaController@getCuentasbyBancos');
Route::post('/reports/deposito_boletas/migrate', ['as'   => 'depositos_boletas.migrate', 'uses' => 'Controllers\DepositoBoletaController@migrate']);
Route::post('/reports/deposito_boletas/delete', ['as'   => 'depositos_boletas.delete', 'uses' => 'Controllers\DepositoBoletaController@delete']);
//Deposito Boleta Conciliaciones

Route::get('boletas/conciliations', ['as' => 'boletas.conciliations', 'uses' => 'Controllers\DepositoBoletaController@conciliationsDetails']);


Route::get('/bank_accounts/{bank_id}', 'Controllers\DepositoBoletaController@getBankAccounts');
Route::get('/payment_type/{payment_type_id}/{atm_id}', 'Controllers\DepositoBoletaController@getPaymentTypePerUser');
Route::get('/get_cuotas/{atm_id}', 'Controllers\DepositoCuotaController@get_cuotas');
Route::get('/get_cuotas_alquiler/{atm_id}', 'Controllers\DepositoAlquilerController@get_cuotas');


Route::get('/get_atms_group/{group_id}', 'Controllers\DepositoBoletaController@getAtmPerGroup');


/** Reporting Mini Terminal */
Route::get('reporting/estado_contable', ['as' => 'reporting.estado_contable', 'uses' => 'Controllers\ExtractosController@estadoContableReports']);
Route::get('reporting/estado_contable/search', ['as' => 'reporting.estado_contable.search', 'uses' => 'Controllers\ExtractosController@estadoContableSearch']);

Route::get('reporting/estado_contable_old', ['as' => 'reporting.estado_contable_old', 'uses' => 'Controllers\ReportingController@estadoContableReports']);
Route::get('reporting/estado_contable_old/search', ['as' => 'reporting.estado_contable_old.search', 'uses' => 'Controllers\ReportingController@estadoContableSearch']);
# Resumen de Miniterminales
Route::get('reporting/resumen_miniterminales', ['as' => 'reporting.resumen_miniterminales', 'uses' => 'Controllers\ExtractosController@resumenMiniterminalesReports']);
Route::get('reporting/resumen_miniterminales/search', ['as' => 'reporting.resumen_miniterminales.search', 'uses' => 'Controllers\ExtractosController@resumenMiniterminalesSearch']);

# Resumen de Miniterminales
Route::get('reporting/resumen_miniterminales_old', ['as' => 'reporting.resumen_miniterminales_old', 'uses' => 'Controllers\ReportingController@resumenMiniterminalesReports']);
Route::get('reporting/resumen_miniterminales_old/search', ['as' => 'reporting.resumen_miniterminales_old.search', 'uses' => 'Controllers\ReportingController@resumenMiniterminalesSearch']);
/*Geting branches for Group*/
Route::get('/reports/info/get_branch_groups/{group_id}/{day}', 'Controllers\ExtractosController@getBranchesfroGroups');
Route::get('/reports/info/get_branch_groups_old/{group_id}/{day}', 'Controllers\ReportingController@getBranchesfroGroups');
/*Geting Cuotas for Group*/
Route::get('/reports/info/get_cuotas_groups/{group_id}', 'Controllers\ReportingController@getCuotasforGroups');

# Boletas de depositos de Miniterminales
/*Route::get('reporting/boletas_depositos', ['as' => 'reporting.boletas_depositos', 'uses' => 'Controllers\ReportingController@boletasDepositosReports']);
Route::get('reporting/boletas_depositos/search', ['as' => 'reporting.boletas_depositos.search', 'uses' => 'Controllers\ReportingController@boletasDepositosSearch']);*/
Route::get('reporting/boletas_depositos', ['as' => 'reporting.boletas_depositos', 'uses' => 'Controllers\ExtractosController@boletasDepositosReports']);
Route::get('reporting/boletas_depositos/search', ['as' => 'reporting.boletas_depositos.search', 'uses' => 'Controllers\ExtractosController@boletasDepositosSearch']);
Route::get('/reports/info/details_boleta/{id}', 'Controllers\ReportingController@getBoletasDetails');
Route::get('/reports/info/details_recibos/{id}', 'Controllers\ExtractosController@getBoletasDetails');
Route::get('/reports/info/details_imagen/{id}', 'Controllers\ExtractosController@getImagenDetails');

# Comisiones de Miniterminales
Route::get('reporting/comisiones', ['as' => 'reporting.comisiones', 'uses' => 'Controllers\ReportingController@comisionesReports']);
Route::get('reporting/comisiones/search', ['as' => 'reporting.comisiones.search', 'uses' => 'Controllers\ReportingController@comisionesSearch']);

# Ventas de Miniterminales
Route::get('reporting/sales', ['as' => 'reporting.sales', 'uses' => 'Controllers\ExtractosController@salesReports']);
Route::get('reporting/sales/search', ['as' => 'reporting.sales.search', 'uses' => 'Controllers\ExtractosController@salesSearch']);

# Ventas de Miniterminales
Route::get('reporting/sales_old', ['as' => 'reporting.sales_old', 'uses' => 'Controllers\ReportingController@salesReports']);
Route::get('reporting/sales_old/search', ['as' => 'reporting.sales_old.search', 'uses' => 'Controllers\ReportingController@salesSearch']);

# Cobranzas de Miniterminales
Route::get('reporting/cobranzas', ['as' => 'reporting.cobranzas', 'uses' => 'Controllers\ExtractosController@cobranzasReports']);
Route::get('reporting/cobranzas/search', ['as' => 'reporting.cobranzas.search', 'uses' => 'Controllers\ExtractosController@cobranzasSearch']);

# Conciliaciones de Miniterminales
Route::get('reporting/conciliations', ['as' => 'reporting.conciliaciones', 'uses' => 'Controllers\ExtractosController@conciliationsDetails']);
Route::post('/reports/conciliations/relanzar_cobranza', ['as'   => 'conciliaciones.relanzar', 'uses' => 'Controllers\ExtractosController@relanzarCobranza']);
Route::post('/reports/conciliations/relanzar_cashout', ['as'   => 'conciliaciones.relanzar_cashout', 'uses' => 'Controllers\ExtractosController@relanzarCashout']);

# Conciliaciones de Miniterminales
Route::get('reporting/miniterminales_bloqueadas', ['as' => 'reporting.bloqueados', 'uses' => 'Controllers\ReportingController@atms_bloqueadas']);
Route::get('reporting/miniterminales_bloqueadas/search', ['as' => 'reporting.bloqueados_search', 'uses' => 'Controllers\ReportingController@atms_bloqueadas_search']);
Route::get('reporting/info/get_atm_balance/{atm_id}', 'Controllers\ReportingController@get_atm_balance');
Route::get('reporting/info/get_bloqueos/{atm_id}', 'Controllers\ReportingController@get_atm_bloqueos');

/*
|----------------------------------------------------------------------------------------------------------------------+
| Parametros de Comunicaciones Management Routes                                                                       |
|----------------------------------------------------------------------------------------------------------------------+
*/
Route::get('parametros_comisiones/services/:service_source_id', ['as' => 'parametros_comisiones.services', 'uses' => 'Controllers\ParametrosComisionesController@getServices']);
Route::get('parametros_comisiones/service_products', ['as' => 'parametros_comisiones.services_products', 'uses' => 'Controllers\ParametrosComisionesController@getServicesProducts']);
Route::get('parametros_comisiones/atms', ['as' => 'parametros_comisiones.atms', 'uses' => 'Controllers\ParametrosComisionesController@getAtms']);
Route::resource('parametros_comisiones', 'Controllers\ParametrosComisionesController');

# Grupos
Route::resource('groups', 'Controllers\GroupController');

/*
|----------------------------------------------------------------------------------------------------------------------+
| Branches Management Routes                                                                                           |
|----------------------------------------------------------------------------------------------------------------------+
*/
Route::get('groups/{groupId}/branches', ['as' => 'groups.branches', 'uses' => 'Controllers\BranchController@index_group']);
Route::get('groups/{groupId}/branches/create', ['as' => 'groups.branches.create', 'uses' => 'Controllers\BranchController@create_group']);
Route::delete('groups/{groupId}/branches/delete', ['as' => 'groups.branches.destroy', 'uses' => 'Controllers\BranchController@destroy_group']);
Route::post('groups/{groupId}/branches/store', ['as' => 'groups.branches.store', 'uses' => 'Controllers\BranchController@store_group']);
Route::post('groups/{branch_id}/branches/store_branch', ['as' => 'groups.store_branch', 'uses' => 'Controllers\GroupController@store_branch']);
Route::put('groups/branches/{branch_id}/update_branch/', ['as' => 'groups.update_branch', 'uses' => 'Controllers\GroupController@update_branch']);

/*
|----------------------------------------------------------------------------------------------------------------------+
| Efectividad                                                                                         |
|----------------------------------------------------------------------------------------------------------------------+
*/
Route::get('reports/efectividad', ['as' => 'reports.efectividad', 'uses' => 'Controllers\ReportingController@efectividad']);
Route::get('reports/efectividad_search', ['as' => 'reports.efectividad.search', 'uses' => 'Controllers\ReportingController@efectividadSearch']);
//Route::get('reports/efectividad_search/{status}/{reservationtime}/{service_id}',['as'=>'reports.efectividad.search.detalle','uses' =>'ReportingController@efectividadSearch']);
//Route::get('reports/efectividad/info/details/{status}/{reservationtime}/{service_id}/{type}',['as'=>'reports.efectividad.info.detalle','uses' =>'ReportingController@getStatusInfoDetails']);

/*
| Formulario de Ventas de Miniterminales                                                                                         |
|----------------------------------------------------------------------------------------------------------------------+
*/

Route::resource('venta', 'Controllers\VentasController');
Route::get('venta/check_ruc', ['as' => 'venta.check_ruc', 'uses' => 'Controllers\VentasController@checkRuc']);
Route::post('groups/store_venta', ['as' => 'groups.store_venta', 'uses' => 'Controllers\GroupController@store_venta']);

/**
 * Exportar a excel los registros de ventas
 */
Route::post('sale_export', ['as' => 'sale_export', 'uses' => 'Controllers\VentasController@sale_export']);

# Estado Contable Detallado
Route::get('reporting/resumen_detallado_miniterminal', ['as' => 'reporting.resumen_detallado_miniterminal', 'uses' => 'Controllers\ExtractosController@resumenDetalladoReports']);
Route::get('reporting/resumen_detallado_miniterminal/search', ['as' => 'reporting.resumen_detallado_miniterminal.search', 'uses' => 'Controllers\ExtractosController@resumenDetalladoSearch']);

# Estado Contable Detallado Old
Route::get('reporting/resumen_detallado_miniterminal_old', ['as' => 'reporting.resumen_detallado_miniterminal_old', 'uses' => 'Controllers\ReportingController@resumenDetalladoReports']);
Route::get('reporting/resumen_detallado_miniterminal_old/search', ['as' => 'reporting.resumen_detallado_miniterminal_old.search', 'uses' => 'Controllers\ReportingController@resumenDetalladoSearch']);

Route::group(['prefix' => 'test/'], function () {

    Route::get('/pruebaa2', function () {
        \DB::beginTransaction();
        try {
            $id = 2742;
            $insert_ondanet  = new \App\Services\DepositoBoletaServices();

            //$response_sales = $insert_ondanet->insertCobranzas($id);
            $response = $insert_ondanet->insertCobranzas($id);
            \Log::warning($response);

            $error = $response['error'];
            $message = $response['message'];

            if (!$response['error']) {
                \DB::commit();
                dd("Registro $id confirmado exitosamente");
            } else {
                \DB::rollback();
                dd('Ha ocurrido un error al intentar guardar el registro');
            }


            dd('Registro creado exitosamente');
        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error("Error  - {$e->getMessage()}");
        }
    });

    Route::get('/pruebafirst', function () {
        \DB::beginTransaction();
        try {
            $movement_id = 34;
            $insert_ondanet  = new \App\Services\OndanetServices();

            $response_sales = $insert_ondanet->sendVentaMini($movement_id);
            \Log::info(json_decode(json_encode($response_sales), true));

            if ($response_sales['error'] == false) {
                \Log::info("[Miniterminales-sales] Exporting Miniterminales Sales to Ondanet ", ['result' => $response_sales['error'], 'toval rowID' => $response_sales['status']]);

                \DB::table('movements')
                    ->where('id', $movement_id)
                    ->update([
                        'destination_operation_id' => $response_sales['status'],
                        'response' => json_encode($response_sales),
                        'updated_at' => Carbon::now()
                    ]);
            } else {
                \Log::warning("[Miniterminales-sales] Exporting Miniterminales Sales to Ondanet", ['result' => $response_sales]);

                \DB::table('movements')
                    ->where('id', $movement_id)
                    ->update([
                        'destination_operation_id' => $response_sales['code'],
                        'response' => json_encode($response_sales),
                        'updated_at' => Carbon::now()
                    ]);
            }

            \DB::commit();
            dd('Registro creado exitosamente');
        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error("Error  - {$e->getMessage()}");
        }
    });

    Route::get('/insert_rules', function(){
        \DB::beginTransaction();
        try{
            $group_id=211;
            
            \DB::table('balance_rules')->insert([
                ['created_at'   => Carbon::now(),'updated_at'   => Carbon::now(), 'dias_previos' => 1, 'saldo_minimo' => 100, 'tipo_control' => 1, 'dia' => 1, 'group_id' => $group_id],
                ['created_at'   => Carbon::now(),'updated_at'   => Carbon::now(), 'dias_previos' => 1, 'saldo_minimo' => 100, 'tipo_control' => 1, 'dia' => 3, 'group_id' => $group_id],
                ['created_at'   => Carbon::now(),'updated_at'   => Carbon::now(), 'dias_previos' => 1, 'saldo_minimo' => 100, 'tipo_control' => 1, 'dia' => 5, 'group_id' => $group_id],
                ['created_at'   => Carbon::now(),'updated_at'   => Carbon::now(), 'dias_previos' => 1, 'saldo_minimo' => 5000000, 'tipo_control' => 4, 'dia' => 1, 'group_id' => $group_id],
            ]);

            \DB::commit();
            dd('Registro creado exitosamente');  

        } catch (\Exception $e) {
            \DB::rollback();
            \Log::error("Error  - {$e->getMessage()}");
        }
    });

    Route::get('/checkBalance', function(){
        try{
            $transaction = \DB::table('transactions')->where('id', 9676613)->first();
            $fecha='2021-12-29 00:00:00';
            $nro=99700;

            $service = new  App\Http\Controllers\ReversionesController();
            $response_block= $service->reversar($transaction, $fecha, $nro);
            \Log::warning($response_block);

            dd($response_block);
        } catch (\Exception $e) {
            \Log::error("Error  - {$e->getMessage()}");
        }
    });

    Route::get('/alquiler', function(){
        try{

            $service = new  App\Http\Controllers\AlquilerController();
            $response_block= $service->checkVencimiento();
            \Log::warning($response_block);

            dd($response_block);
        } catch (\Exception $e) {
            \Log::error("Error  - {$e->getMessage()}");
        }
    });

    Route::get('/comision_prueba', function(){
        try{

            $service = new  App\Http\Controllers\ComisionesController();
            $response_block= $service->procesar_pagos_comision();
            \Log::warning($response_block);

            dd($response_block);
        } catch (\Exception $e) {
            \Log::error("Error  - {$e->getMessage()}");
        }
    });

    Route::get('/boletas', function(){
        try 
        {
            $boletas = \DB::table('boletas_depositos')            
                ->where('conciliado','=', false)
                ->where('estado','=',true)
                ->take(30)
                ->get();

            //dd($boletas);
            $deposito_boleta_services = new App\Services\DepositoBoletaServices();    
            foreach($boletas as $boleta)
            {
                $cobranzas = $deposito_boleta_services->insertCobranzas($boleta->id);
                \Log::info('miniterminales:insertCobranzas',['boleta_id' => $boleta->id, 'result'=> $cobranzas]);
                
                if($cobranzas['error'] == false)
                {
                    //update de campo conciliado
                    $conciliado = \DB::table('boletas_depositos')
                    ->where('id', $boleta->id)
                    ->update(['conciliado' => true]);

                }
                else
                {
                    $data = [
                        'user_name'    => 'Tesorería',
                        'fecha'        => $boleta->fecha, 
                        'nroboleta'    => $boleta->boleta_numero,
                        'monto'        => number_format($boleta->monto, 0),
                        'boleta'       => $boleta->id
                    ];
                }                                                        
            }   
            
           dd('Miniterminales insertCobranzas | Tarea ejecutada exitosamente.'); 
        }
        catch(\Exception $e)
        {
            $fecha = Carbon::now();
            \Log::info('Se ejecutó tarea programada miniterminales:insertCobranzas  | pero se reportaron inconvenientes a las: '. $fecha,['result'=>$e] );
            //$this->info('Miniterminales insertCobranzas | Error al ejecutar la tarea. Se registra evento en el log');
        }
    });

    Route::get('/boletas_cuotas_v2', function(){
        try 
        {
            $boleta_id= 3147;

            $deposito_boleta_services = new App\Services\DepositoCuotaServices();    

            $cobranzas = $deposito_boleta_services->insertCuotas_v2($boleta_id);
                \Log::info('miniterminales:insertCobranzas',['boleta_id' => $boleta_id, 'result'=> $cobranzas]);
                
            if($cobranzas['error'] == false)
            {
                //update de campo conciliado
                $conciliado = \DB::table('boletas_depositos')
                ->where('id', $boleta_id)
                ->update(['conciliado' => true]);

            }

           dd('Miniterminales insertCobranzas | Tarea ejecutada exitosamente.'); 
        }
        catch(\Exception $e)
        {
            $fecha = Carbon::now();
            \Log::info('Se ejecutó tarea programada miniterminales:insertCobranzas  | pero se reportaron inconvenientes a las: '. $fecha,['result'=>$e] );
            //$this->info('Miniterminales insertCobranzas | Error al ejecutar la tarea. Se registra evento en el log');
        }
    });

    Route::get('/boletas_v2', function(){
        try 
        {
            $boleta_id= 51680;

            $deposito_boleta_services = new App\Services\DepositoBoletaServices();    

            $cobranzas = $deposito_boleta_services->insertCobranzas_V2($boleta_id);
                \Log::info('miniterminales:insertCobranzas',['boleta_id' => $boleta_id, 'result'=> $cobranzas]);
                
            if($cobranzas['error'] == false)
            {
                //update de campo conciliado
                $conciliado = \DB::table('boletas_depositos')
                ->where('id', $boleta_id)
                ->update(['conciliado' => true]);

            }

           dd('Miniterminales insertCobranzas | Tarea ejecutada exitosamente.'); 
        }
        catch(\Exception $e)
        {
            $fecha = Carbon::now();
            \Log::info('Se ejecutó tarea programada miniterminales:insertCobranzas  | pero se reportaron inconvenientes a las: '. $fecha,['result'=>$e] );
            //$this->info('Miniterminales insertCobranzas | Error al ejecutar la tarea. Se registra evento en el log');
        }
    });

    Route::get('/reglas', function(){
        try 
        {
            $boleta_id= 51684;

            $deposito_boleta_services = new App\Services\DepositoBoletaServices();    

            $cobranzas = $deposito_boleta_services->checkBlock($boleta_id);
                \Log::info('miniterminales:insertCobranzas',['boleta_id' => $boleta_id, 'result'=> $cobranzas]);
                
            if($cobranzas['error'] == false)
            {
                //update de campo conciliado
                $conciliado = \DB::table('boletas_depositos')
                ->where('id', $boleta_id)
                ->update(['conciliado' => true]);

            }

           dd('Miniterminales insertCobranzas | Tarea ejecutada exitosamente.'); 
        }
        catch(\Exception $e)
        {
            $fecha = Carbon::now();
            \Log::info('Se ejecutó tarea programada miniterminales:insertCobranzas  | pero se reportaron inconvenientes a las: '. $fecha,['result'=>$e] );
            //$this->info('Miniterminales insertCobranzas | Error al ejecutar la tarea. Se registra evento en el log');
        }
    });

    Route::get('/recibo_migrar', function(){
        try 
        {
            $movement_id= 258418;

            $deposito_boleta_services = new App\Services\DepositoBoletaServices();    

            $cobranzas = $deposito_boleta_services->registerCobranzasToOndanet($movement_id);
            \Log::info('miniterminales:insertCobranzas',['boleta_id' => $movement_id, 'result'=> $cobranzas]);

           dd('Miniterminales insertCobranzas | Tarea ejecutada exitosamente.'); 
        }
        catch(\Exception $e)
        {
            $fecha = Carbon::now();
            \Log::info('Se ejecutó tarea programada miniterminales:insertCobranzas  | pero se reportaron inconvenientes a las: '. $fecha,['result'=>$e] );
            //$this->info('Miniterminales insertCobranzas | Error al ejecutar la tarea. Se registra evento en el log');
        }
    });

    Route::get('/epin_reporte', function(){
        
        try 
        {
            $numbers='14420000179,14420000180,130470004306,130470004307,130470004308,130470004309,130470004310,130470004311,130470004312,130470004313,130470004314,130470004315,130470004316,130470004317,130470004318,130470004319,130470004320,130470004321,130470004322,130470004323,130470004324,130470004325,130470004326,130470004327,130470004328,130470004329,130470004330,130470004331,130470004332,130470004333,130470004334,130470004335,130470004336,130470004337,130470004338,130470004339,130470004340,130470004341,130470004342,130470004343,130470004344,130470004345,130470004346,130470004347,130470004348,130470004349,130470004350,130470004351,130470004352,130470004353,130470004354,130470004355,130470004356,130470004357,130470004358,130470004359,130470004360,130470004361,130470004362,130470004363,130470004364,130470004365,130470004366,130470004367,130470004368,130470004369,130470004370,130470004371,130470004372,130470004373,130470004374,130470004375,130470004376,130470004377,130470004378,130470004379,130470004379,130470004380,130470004381,130470004382,130470004383,130470004384,130470004385,130470004386,130470004387,130470004388,130470004389,130470004390,130470004391,130470004392,130470004393,130470004394,130470004395,130470004396,130470004397,130470004398,130470004399,130470004400,130470004401,130470004402,130470004403,130470004404,130470004405,130470004406,130470004407,130470004408,130470004411,130470004412,130470004413,130470004414,130470004415,130470004416,130470004417,130470004418,130470004419,130470004420,130470004421,130470004422,130470004429,130470004430,130470004432,130470004433,130470004434,130470004435,130470004436,130470004437,130470004438,130470004439,130470004440,130470004441,130470004442,130470004443,130470004444,130470004445,130470004446,130470004447,130470004448,130470004449,130470004450,130470004451,130470004452,130470004453,130470004454,130470004455,130470004456,130470004457,130470004458,130470004459,130470004460,130470004461,130470004462,130470004463,1390190000046,1390190000047,1390190000048,1390190000049,1390190000050,1390190000051,1390190000052,1390190000053,1390190000054,1390190000055,1390190000056,1390190000057,1390190000058,1390190000059';

            $number_exp=explode(",", $numbers);

            /*foreach($number_exp as $num){
                dd('hola'.$num);
            }*/

            dd("like '%".implode("%','%",$number_exp));

           dd('Miniterminales insertCobranzas | Tarea ejecutada exitosamente.'); 
        }
        catch(\Exception $e)
        {
            $fecha = Carbon::now();
            \Log::info('Se ejecutó tarea programada miniterminales:insertCobranzas  | pero se reportaron inconvenientes a las: '. $fecha,['result'=>$e] );
            //$this->info('Miniterminales insertCobranzas | Error al ejecutar la tarea. Se registra evento en el log');
        }
    });

    Route::get('/guardar_users', function(){
        \DB::beginTransaction();
        try 
        {
            $users = \DB::table('atms')
                ->select('atms.id as atm_id', 'branches.user_id')
                ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->whereIn('atms.owner_id',[16, 21])
                ->whereNotNull('branches.user_id')
                ->whereNotNull('atms.id')
            ->get();

            foreach($users as $user){
                //dd($user->user_id);
                $recibo_id_deuda=\DB::table('atms_per_users')->insert([
                    'user_id'       => $user->user_id,
                    'atm_id'        => $user->atm_id,
                    'status'        => true,
                    'created_at'    => Carbon::now(),
                    'created_by'    => 131
                ]);
            }
            \DB::commit();
            dd('Listo'); 
        }
        catch(\Exception $e)
        {
            \DB::rollback();
            $fecha = Carbon::now();
            \Log::info('Se ejecutó tarea programada miniterminales:insertCobranzas  | pero se reportaron inconvenientes a las: '. $fecha,['result'=>$e] );
            //$this->info('Miniterminales insertCobranzas | Error al ejecutar la tarea. Se registra evento en el log');
        }
    });

    Route::get('/guardar_users_balance_rules', function(){
        \DB::beginTransaction();
        try 
        {
            $users = \DB::table('atms')
                ->select('atms.id as atm_id', 'branches.user_id')
                ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                ->whereIn('atms.owner_id',[16, 21])
                ->whereNotNull('branches.user_id')
                ->whereNotNull('atms.id')
            ->get();
            //dd($users);
            foreach($users as $user){

                \DB::table('balance_rules')
                    ->where('user_id', $user->user_id)
                    ->update([
                        'atm_id'    => $user->atm_id,
                ]);
            }
            \DB::commit();
            dd('Listo'); 
        }
        catch(\Exception $e)
        {
            \DB::rollback();
            $fecha = Carbon::now();
            \Log::info('Se ejecutó tarea programada miniterminales:insertCobranzas  | pero se reportaron inconvenientes a las: '. $fecha,['result'=>$e] );
            //$this->info('Miniterminales insertCobranzas | Error al ejecutar la tarea. Se registra evento en el log');
        }
    });

    Route::get('/check_alquiler', function(){
        try{

            $service = new  App\Http\Controllers\AlquilerController();
            $response_block= $service->checkTransaction();
            \Log::warning($response_block);

            dd($response_block);
        } catch (\Exception $e) {
            dd($e);
        }
    });
});

/*
|----------------------------------------------------------------------------------------------------------------------+
| Acceso a reportes PDV TDP                                                                                |
|----------------------------------------------------------------------------------------------------------------------+
*/
Route::group(['prefix' => 'pdv'], function () {
    Route::get('transactions/{atm_id}', ['as' => 'reports.pdvda.search', 'uses' => 'Controllers\ReportingController@dapdv_transactions']);
});

/*
|----------------------------------------------------------------------------------------------------------------------+
| Deposito Boleta de Cuotas Management Routes                                                                                  |
|----------------------------------------------------------------------------------------------------------------------+
*/
Route::resource('depositos_cuotas', 'Controllers\DepositoCuotaController');
Route::get('/reports/depositos_cuotas/cuentas/{banco_id}', 'Controllers\DepositoCuotaController@getCuentasbyBancos');
Route::post('/reports/depositos_cuotas/migrate', ['as'   => 'depositos_cuotas.migrate', 'uses' => 'Controllers\DepositoCuotaController@migrate']);
Route::post('/reports/depositos_cuotas/delete', ['as'   => 'depositos_cuotas.delete', 'uses' => 'Controllers\DepositoCuotaController@delete']);

# Boletas de depositos de Miniterminales
/*Route::get('reporting/depositos_cuotas', ['as' => 'reporting.depositos_cuotas', 'uses' => 'Controllers\ReportingController@DepositosCuotasReports']);
Route::get('reporting/depositos_cuotas/search', ['as' => 'reporting.depositos_cuotas.search', 'uses' => 'Controllers\ReportingController@DepositosCuotasSearch']);*/
Route::get('reporting/depositos_cuotas', ['as' => 'reporting.depositos_cuotas', 'uses' => 'Controllers\ExtractosController@DepositosCuotasReports']);
Route::get('reporting/depositos_cuotas/search', ['as' => 'reporting.depositos_cuotas.search', 'uses' => 'Controllers\ExtractosController@DepositosCuotasSearch']);
Route::get('reporting/depositos_cuotas/comprobante/{id}', ['as' => 'reporting.boleta.comprobante', 'uses' => 'Controllers\ReportingController@comprobante_cuota']);
Route::get('/reports/info/details_cuota/{id}', 'Controllers\ReportingController@getCuotasDetails');
/*
|----------------------------------------------------------------------------------------------------------------------+
| Marcas - Modelos                                                                                    |
|----------------------------------------------------------------------------------------------------------------------+
*/
Route::resource('brands', 'Controllers\BrandController');
Route::resource('model.brand', 'Controllers\ModelBrandController');
Route::resource('models', 'Controllers\ModelBrandController');

/*
|----------------------------------------------------------------------------------------------------------------------+
| Housing - Devices                                                                                        |
|----------------------------------------------------------------------------------------------------------------------+
*/
Route::resource('miniterminales', 'Controllers\HousingController');
Route::get('miniterminal/import/', ['as' => 'miniterminales.import', 'uses' => 'Controllers\HousingController@import']);
Route::post('miniterminal/store_import/', ['as' => 'miniterminales.store_import', 'uses' => 'Controllers\HousingController@store_import']);

Route::resource('devices', 'Controllers\DeviceController');
Route::resource('housing.device', 'Controllers\DeviceController');
Route::get('devices/show/', ['as' => 'devices.showGet', 'uses' => 'Controllers\DeviceController@show']);
Route::get('housing/device/import/{housingId}', ['as' => 'housing.device.import', 'uses' => 'Controllers\DeviceController@import']);
Route::post('housing/device/store_import/{housingId}', ['as' => 'housing.device.store_import', 'uses' => 'Controllers\DeviceController@store_import']);

/**
 * ---------------------------------------------------------------------------------------------------------------------
 * Conciliadores
 * ---------------------------------------------------------------------------------------------------------------------
 */
Route::group(['prefix' => 'conciliators'], function () {
    Route::group(['prefix' => 'ballot'], function () {
        Route::get('ballot_conciliator', ['as' => 'ballot_conciliator', 'uses' => 'Controllers\Conciliators\BallotConciliatorController@ballot_conciliator']);
        Route::post('ballot_conciliator_create', ['as' => 'ballot_conciliator_create', 'uses' => 'Controllers\Conciliators\BallotConciliatorController@ballot_conciliator_create']);
        Route::post('ballot_conciliator_store', ['as' => 'ballot_conciliator_store', 'uses' => 'Controllers\Conciliators\BallotConciliatorController@ballot_conciliator_store']);
        Route::post('ballot_conciliator_cancel', ['as' => 'ballot_conciliator_cancel', 'uses' => 'Controllers\Conciliators\BallotConciliatorController@ballot_conciliator_cancel']);
    });

    Route::group(['prefix' => 'transaction'], function () {
        Route::get('transaction_conciliator', ['as' => 'transaction_conciliator', 'uses' => 'Controllers\Conciliators\TransactionConciliatorController@transaction_conciliator']);
        Route::post('transaction_conciliator_validate', ['as' => 'transaction_conciliator_validate', 'uses' => 'Controllers\Conciliators\TransactionConciliatorController@transaction_conciliator_validate']);
        Route::post('transaction_conciliator_export', ['as' => 'transaction_conciliator_export', 'uses' => 'Controllers\Conciliators\TransactionConciliatorController@transaction_conciliator_export']);
    });
});

/**
 * ----------------------
 * ussd
 * ----------------------
 */
Route::group(['prefix' => 'ussd'], function () {
    Route::group(['prefix' => 'transaction'], function () {

        Route::match(['get', 'post'], 'ussd_transaction_report', [
            'as' => 'ussd_transaction_report',
            'uses' => 'Controllers\Ussd\UssdTransactionController@ussd_transaction_report'
        ]);

        Route::post('ussd_transaction_search', ['as' => 'ussd_transaction_search', 'uses' => 'Controllers\Ussd\UssdTransactionController@ussd_transaction_search']);
        Route::post('ussd_transaction_edit', ['as' => 'ussd_transaction_edit', 'uses' => 'Controllers\Ussd\UssdTransactionController@ussd_transaction_edit']);
        Route::post('ussd_transaction_relaunch', ['as' => 'ussd_transaction_relaunch', 'uses' => 'Controllers\Ussd\UssdTransactionController@ussd_transaction_relaunch']);
    });

    Route::group(['prefix' => 'operator'], function () {
        Route::get('ussd_operator_report', ['as' => 'ussd_operator_report', 'uses' => 'Controllers\UssdOperatorController@ussd_operator_report']);
        Route::get('ussd_operator_get_by_description/{description}', ['as' => 'ussd_phone_get_by_description', 'uses' => 'Controllers\UssdOperatorController@ussd_operator_get_by_description']);
        Route::post('ussd_operator_set_status', ['as' => 'ussd_operator_set_status', 'uses' => 'Controllers\UssdOperatorController@ussd_operator_set_status']);
    });

    Route::group(['prefix' => 'phone'], function () {
        Route::get('ussd_phone_report', ['as' => 'ussd_phone_report', 'uses' => 'Controllers\Ussd\UssdPhoneController@ussd_phone_report']);
        Route::post('ussd_phone_set_status', ['as' => 'ussd_phone_set_status', 'uses' => 'Controllers\Ussd\UssdPhoneController@ussd_phone_set_status']);
    });

    Route::group(['prefix' => 'service'], function () {
        Route::get('ussd_service_report', ['as' => 'ussd_service_report', 'uses' => 'Controllers\UssdServiceController@ussd_service_report']);
        Route::get('ussd_service_get_by_description/{description}', ['as' => 'ussd_service_get_by_description', 'uses' => 'Controllers\UssdServiceController@ussd_service_get_by_description']);
        Route::post('ussd_service_set_status', ['as' => 'ussd_service_set_status', 'uses' => 'Controllers\UssdServiceController@ussd_service_set_status']);
    });

    Route::group(['prefix' => 'option'], function () {
        Route::get('ussd_option_report', ['as' => 'ussd_option_report', 'uses' => 'Controllers\UssdOptionController@ussd_option_report']);
        Route::post('ussd_option_set_status', ['as' => 'ussd_option_set_status', 'uses' => 'Controllers\UssdOptionController@ussd_option_set_status']);
    });

    Route::group(['prefix' => 'menu'], function () {
        Route::get('ussd_menu_report', ['as' => 'ussd_menu_report', 'uses' => 'Controllers\UssdMenuController@ussd_menu_report']);
        Route::post('ussd_menu_set_status', ['as' => 'ussd_menu_set_status', 'uses' => 'Controllers\UssdMenuController@ussd_menu_set_status']);
    });

    Route::group(['prefix' => 'black_list'], function () {
        Route::get('ussd_black_list_reason', 'Controllers\UssdBlackListController@ussd_black_list_reason');
        Route::get('ussd_black_list_operador', 'Controllers\UssdBlackListController@ussd_black_list_operador');
        Route::get('ussd_black_list_report', ['as' => 'ussd_black_list_report', 'uses' => 'Controllers\UssdBlackListController@ussd_black_list_report']);
        Route::post('ussd_black_list_search', ['as' => 'ussd_black_list_search', 'uses' => 'Controllers\UssdBlackListController@ussd_black_list_search']);
        Route::post('ussd_black_list_add', ['as' => 'ussd_black_list_add', 'uses' => 'Controllers\UssdBlackListController@ussd_black_list_add']);
        Route::post('ussd_black_list_edit', ['as' => 'ussd_black_list_edit', 'uses' => 'Controllers\UssdBlackListController@ussd_black_list_edit']);
    });
});


/** Historial Bloqueos Miniterminales */
Route::get('reporting/historial_bloqueos', ['as' => 'reporting.historial_bloqueos', 'uses' => 'Controllers\ReportingController@historialBloqueosReports']);
Route::get('reporting/historial_bloqueos/search', ['as' => 'reporting.historial_bloqueos.search', 'uses' => 'Controllers\ReportingController@historialBloqueosSearch']);
Route::get('/reports/ddl/users/{group_id}', 'Controllers\ReportingController@getUsersbyGroups');
Route::get('/reports/ddl/atms/{group_id}', 'Controllers\ReportingController@getAtmsbyGroups');

/** conciliaciones detalles*/
Route::get('reports/conciliations_details', ['as' => 'reports.conciliations_details', 'uses' => 'Controllers\ReportingController@conciliations_detailsReports']);
Route::get('reports/conciliations_details/search', ['as' => 'reports.conciliations_details.search', 'uses' => 'Controllers\ReportingController@conciliations_detailsSearch']);
Route::post('/reports/conciliations_details/relaunch_transaction', ['as'   => 'conciliations_details.relaunch_transaction', 'uses' => 'Controllers\ReportingController@relaunch_conciliation_detail']);
Route::post('/reports/conciliations_details/relaunch_transaction_all', ['as'   => 'conciliations_details.relaunch_transaction_all', 'uses' => 'Controllers\ReportingController@relaunch_conciliation_all']);

Route::get('reports/atm_status_history',['as' => 'reports.atm_status_history','uses' => 'Controllers\AtmController@atm_status_history']);
Route::get('reports/atm_status_history_search',['as' => 'reports.atm_status_history_search','uses' => 'Controllers\AtmController@atm_status_history_search']);
Route::post('reports/atm_status_history/get_branches',['as' => 'reports.atm_status_history.get_branches','uses' => 'Controllers\AtmController@searchBranches']);
Route::post('reports/atm_status_history/get_atms',['as' => 'reports.atm_status_history.get_atms','uses' => 'Controllers\AtmController@searchAtm']);

Route::get('reports/mini_retiro',['as' => 'reports.mini_retiro','uses' => 'Controllers\MiniCashOutDevolucionController@getDataMini']);
Route::get('reports/mini_retiro_search',['as' => 'reports.mini_retiro.search','uses' => 'Controllers\MiniCashOutDevolucionController@getDataminiSearch']);
Route::post('transactionDataModal',['as' => 'transactionDataModal','uses' => 'Controllers\MiniCashOutDevolucionController@dataModal']);


//Transaction Rollback
Route::get('reports/rollback', ['as' => 'reports.rollback', 'uses' => 'Controllers\ReportingController@transaction_not_rollback']);
Route::get('reports/rollback/search', ['as' => 'reports.rollback.search', 'uses' => 'Controllers\ReportingController@transaction_not_rollbackSearch']);
Route::post('reports/rollback/reversa_transaction', ['as' => 'reports.rollback.reversa_transaction', 'uses' => 'Controllers\ReportingController@reversaTransaction']);
Route::post('/reports/rollback/reversa_transactionAll', ['as' => 'reports.rollback.reversa_transactionAll', 'uses' => 'Controllers\ReportingController@reversaTransactionAll']);
Route::post('/reports/rollback/reversaUpdateAll', ['as' => 'reports.rollback.reversaUpdateAll', 'uses' => 'Controllers\ReportingController@updateReversaAll']);
Route::post('/reports/rollback/reversaUpdate', ['as' => 'reports.rollback.reversaUpdate', 'uses' => 'Controllers\ReportingController@updateReversa']);

//  ventas pendientes de afectar extractos.
Route::get('reports/movements_affecting_extracts', ['as' => 'reports.movements_affecting_extracts', 'uses' => 'Controllers\ReportingController@movements_affecting_extracts']);
Route::post('reports/movements_affecting_extracts_update', ['as' => 'reports.movements_affecting_extracts_update', 'uses' => 'Controllers\ReportingController@movements_affecting_extracts_update_destination']);
// transactiones success con monto cero
Route::get('reports/success_zero', ['as' => 'reports.success_zero', 'uses' => 'Controllers\ReportingController@transaction_success_amount_zero']);


/** # Reglas de Parametros **/
Route::resource('params_rules', 'Controllers\ParamsRuleController');
Route::resource('services_rules', 'Controllers\ServicesRuleController');

Route::get('references/{idparam_rules}/{current_params_rule_id}/{reference}/edit', ['as' => 'references.edit', 'uses' => 'Controllers\ReferenceLimitedController@edit']);
Route::put('references/{idparam_rules}/{current_params_rule_id}/{reference}/update', ['as' => 'references.update', 'uses' => 'Controllers\ReferenceLimitedController@update']);
Route::delete('references/{idparam_rules}/{current_params_rule_id}/{reference}/destroy', ['as' => 'references.destroy', 'uses' => 'Controllers\ReferenceLimitedController@destroy']);
Route::resource('references', 'Controllers\ReferenceLimitedController')->except(['edit','update','destroy']);


/*
|----------------------------------------------------------------------------------------------------------------------+
| Formulario de Alquiler de Miniterminales                                                                                         |
|----------------------------------------------------------------------------------------------------------------------+
*/

Route::resource('alquiler', 'Controllers\AlquilerController');

/**
 * Exportar a excel los registros de alquileres
 */
Route::post('rental_export', ['as' => 'rental_export', 'uses' => 'Controllers\AlquilerController@rental_export']);

/*
|----------------------------------------------------------------------------------------------------------------------+
| Deposito Boleta de Alquiler Management Routes                                                                                  |
|----------------------------------------------------------------------------------------------------------------------+
*/
Route::resource('depositos_alquileres', 'Controllers\DepositoAlquilerController');
Route::get('/reports/depositos_alquileres/cuentas/{banco_id}', 'Controllers\DepositoAlquilerController@getCuentasbyBancos');
Route::post('/reports/depositos_alquileres/migrate', ['as'   => 'depositos_alquileres.migrate', 'uses' => 'Controllers\DepositoAlquilerController@migrate']);
Route::post('/reports/depositos_alquileres/delete', ['as'   => 'depositos_alquileres.delete', 'uses' => 'Controllers\DepositoAlquilerController@delete']);

# Boletas de depositos Alquiler de Miniterminales
/*Route::get('reporting/depositos_alquileres', ['as' => 'reporting.depositos_alquileres', 'uses' => 'ReportingController@depositosAlquileresReports']);
Route::get('reporting/depositos_alquileres/search', ['as' => 'reporting.depositos_alquileres.search', 'uses' => 'ReportingController@depositosAlquileresSearch']);*/
Route::get('reporting/depositos_alquileres', ['as' => 'reporting.depositos_alquileres', 'uses' => 'Controllers\ExtractosController@depositosAlquileresReports']);
Route::get('reporting/depositos_alquileres/search', ['as' => 'reporting.depositos_alquileres.search', 'uses' => 'Controllers\ExtractosController@depositosAlquileresSearch']);
Route::get('reporting/depositos_alquileres/comprobante/{id}', ['as' => 'reporting.boleta_alquiler.comprobante', 'uses' => 'Controllers\ReportingController@comprobante_alquiler']);
Route::get('/reports/info/details_alquiler/{id}', 'Controllers\ReportingController@getAlquileresDetails');


/**
 * ---------------------------------------------------------------------------------------------------------------------
 * Rutas para los usuarios que interactuan con la terminal.
 * 
 * Permisos:
 * 1) terminal_interaction
 * 2) terminal_interaction.manage.users
 * 3) terminal_interaction.manage.users.create
 * 4) terminal_interaction.manage.users.store
 * 5) terminal_interaction.reports.pos_box_movement
 * 
 * Roles:
 * 1) Administrador para interacciones en la terminal.
 * 2) Usuario que realiza apertura y cierre de caja.
 * ---------------------------------------------------------------------------------------------------------------------
 */

Route::group(['prefix' => 'terminal_interaction'], function () {
    Route::group(['prefix' => 'manage'], function () {
        Route::group(['prefix' => 'users'], function () {
            Route::get('terminal_interaction_users', ['as' => 'terminal_interaction_users', 'uses' => 'Controllers\TerminalInteraction\UsersController@index']);
            Route::get('terminal_interaction_users_create', ['as' => 'terminal_interaction_users_create', 'uses' => 'Controllers\TerminalInteraction\UsersController@create']);
            Route::post('terminal_interaction_users_store', ['as' => 'terminal_interaction_users_store', 'uses' => 'Controllers\TerminalInteraction\UsersController@store']);
        });
    });

    Route::group(['prefix' => 'reports'], function () {
        Route::group(['prefix' => 'pos_box_movement'], function () {
            Route::match(['get', 'post'], 'pos_box_movement_index', ['as' => 'pos_box_movement_index', 'uses' => 'Controllers\TerminalInteraction\PosBoxMovementController@index']);
        });

        Route::group(['prefix' => 'transaction'], function () {
            Route::match(['get', 'post'], 'transaction_index', ['as' => 'transaction_index', 'uses' => 'Controllers\TerminalInteraction\TransactionController@index']);
        });

        Route::group(['prefix' => 'ticket'], function () {
            Route::match(['get', 'post'], 'ticket_index', ['as' => 'ticket_index', 'uses' => 'Controllers\TerminalInteraction\TicketController@index']);
        });

        Route::group(['prefix' => 'accounting_statement'], function () {
            Route::match(['get', 'post'], 'accounting_statement_index', ['as' => 'accounting_statement_index', 'uses' => 'Controllers\TerminalInteraction\AccountingStatementController@index']);
        });
    });
});

/**
 * ---------------------------------------------------------------------------------------------------------------------
 * Rutas para monitoreo del los usuarios que interactuan con la terminal
 * 
 * Permisos:
 * 1) terminal_interaction_monitoring
 * 2) terminal_interaction_monitoring.pos_box_movement
 * 
 * Roles:
 * 1) Administrador para interacciones en la terminal.
 * 2) Usuario que realiza apertura y cierre de caja.
 * ---------------------------------------------------------------------------------------------------------------------
 */

//Route::match(['get', 'post'], 'terminal_interaction_monitoring_pos_box_movement', 'PosBoxMovementController@index');

/*Route::match(['get', 'post'], 'terminal_interaction_monitoring_pos_box_movement', function () {
    return 'hola';
});*/

Route::match(['get', 'post'], 'terminal_interaction_monitoring_change_pin', [
    'as' => 'terminal_interaction_monitoring_change_pin',
    'uses' => 'Controllers\TerminalInteractionMonitoring\ChangePinController@index'
]);

Route::match(['get', 'post'], 'terminal_interaction_monitoring_change_pin_edit', [
    'as' => 'terminal_interaction_monitoring_change_pin_edit',
    'uses' => 'Controllers\TerminalInteractionMonitoring\ChangePinController@edit'
]);

Route::match(['get', 'post'], 'terminal_interaction_monitoring_pos_box', [
    'as' => 'terminal_interaction_monitoring_pos_box',
    'uses' => 'Controllers\TerminalInteractionMonitoring\PosBoxController@index'
]);

Route::post('terminal_interaction_monitoring_pos_box_edit', ['as' => 'terminal_interaction_monitoring_pos_box_edit', 'uses' => 'Controllers\TerminalInteractionMonitoring\PosBoxController@edit']);

Route::match(['get', 'post'], 'terminal_interaction_monitoring_pos_box_movement', [
    'as' => 'terminal_interaction_monitoring_pos_box_movement',
    'uses' => 'Controllers\TerminalInteractionMonitoring\PosBoxMovementController@index'
]);

Route::match(['get', 'post'], 'terminal_interaction_get_transactions_by_atm', [
    'as' => 'terminal_interaction_get_transactions_by_atm',
    'uses' => 'Controllers\TerminalInteractionMonitoring\PosBoxMovementController@get_transactions_by_atm'
]);

Route::post('terminal_interaction_login_add', ['as' => 'terminal_interaction_login_add', 'uses' => 'Controllers\TerminalInteractionMonitoring\TerminalInteractionAccessController@terminal_interaction_login_add']);
Route::post('terminal_interaction_access_edit', ['as' => 'terminal_interaction_access_edit', 'uses' => 'Controllers\TerminalInteractionMonitoring\TerminalInteractionAccessController@terminal_interaction_access_edit']);
Route::post('terminal_interaction_assign_atm', ['as' => 'terminal_interaction_assign_atm', 'uses' => 'Controllers\TerminalInteractionMonitoring\TerminalInteractionAccessController@terminal_interaction_assign_atm']);
Route::post('terminal_interaction_save_pin', ['as' => 'terminal_interaction_save_pin', 'uses' => 'Controllers\TerminalInteractionMonitoring\TerminalInteractionAccessController@terminal_interaction_save_pin']);


#Reporte de Cuotas de Alquiler
Route::get('reporting/cuotas_alquiler', ['as' => 'reporting.cuotas_alquiler', 'uses' => 'Controllers\ReportingController@cuotasAlquilerReports']);
Route::get('reporting/cuotas_alquiler/search', ['as' => 'reporting.cuotas_alquiler.search', 'uses' => 'Controllers\ReportingController@cuotasAlquilerSearch']);
Route::get('reporting/cuotas_alquiler/factura/{id}', ['as' => 'reporting.cuotas_alquiler.factura', 'uses' => 'Controllers\ReportingController@factura_alquiler']);
Route::post('/reporting/cuotas_alquiler/insert', ['as'   => 'reporting.insert_alquiler', 'uses' => 'Controllers\ReportingController@insert_alquiler']);

/*
 |-----------------------------------------------------------------------------------------------------------------------+
 | REGISTRO DE BOLETAS DE ARQUEOS
 |-----------------------------------------------------------------------------------------------------------------------+
 */

Route::resource('depositos_arqueos','Controllers\DepositosTerminalesController');
Route::get('/depositos_arqueos/ddl/recaudadores/{ci}', 'Controllers\DepositosTerminalesController@getTransactions');
Route::get('reporting/depositos_alquileres/comprobante/{id}', ['as' => 'reporting.boleta_alquiler.comprobante', 'uses' => 'Controllers\ReportingController@comprobante_alquiler']);



/*
|----------------------------------------------------------------------------------------------------------------------+
| FORMULARIO ATM V2
|----------------------------------------------------------------------------------------------------------------------+
 */
Route::resource('atmnew', 'Controllers\AtmnewController');
Route::get('atm/new/form_step_new', ['as' => 'atmnew.form_step_new', 'uses' => 'Controllers\AtmnewController@formStep']);
Route::get('atm/new/prueba', ['as' => 'atmnew.prueba', 'uses' => 'Controllers\AtmnewController@prueba']);
Route::get('atm/new/ciudades', ['as' => 'atmnew.ciudades', 'uses' => 'Controllers\AtmnewController@getCiudades']);
Route::get('atm/new/barrios', ['as' => 'atmnew.barrios', 'uses' => 'Controllers\AtmnewController@getBarrios']);
Route::get('atm/new/zonas', ['as' => 'atmnew.zonas', 'uses' => 'Controllers\AtmnewController@getZonas']);
Route::post('atm/new/newhash', ['as' => 'atmnew.newhash', 'uses' => 'Controllers\AtmnewController@generateHash']);
Route::resource('zonas', 'ZonasController');
Route::get('atm/new/check_code', ['as' => 'atmnew.check_code', 'uses' => 'Controllers\AtmnewController@checkCode']);
Route::get('atm/new/{id}/params', ['as' => 'atmnew.params', 'uses' => 'Controllers\AtmnewController@params']);
Route::get('atm/new/{id}/parts', ['as' => 'atmnew.parts', 'uses' => 'Controllers\AtmnewController@parts']);
Route::post('atm/new/{id}/param_store', ['as' => 'atmnew.param_store', 'uses' => 'Controllers\AtmnewController@paramStore']);
Route::post('atm/new/{id}/parts_update', ['as' => 'atmnew.parts_update', 'uses' => 'Controllers\AtmnewController@partsUpdate']);
Route::get('atm/new/{id}/check_key', ['as' => 'atmnew.check_key', 'uses' => 'Controllers\AtmnewController@checkKey']);
Route::get('atm/new/{id}/screens', ['as' => 'atmnew.flows', 'uses' => 'Controllers\AtmnewController@getApplicationInterface']);
Route::get('atm/new/{id}/housing', ['as' => 'atmnew.housing', 'uses' => 'Controllers\AtmnewController@housing']);
Route::post('atm/new/{id}/housing/store', ['as' => 'atmnew.housing.store', 'uses' => 'Controllers\AtmnewController@store_housing']);
Route::post('atm/new/reactivate', 'Controllers\AtmnewController@Procesar_reactivacion');
Route::post('atm/new/arqueo_remoto', 'Controllers\AtmnewController@enable_arqueo_remoto');
Route::post('atm/new/grilla_tradicional', 'Controllers\AtmnewController@enable_grilla_tradicional');
Route::get('/getciudades','Controllers\AtmnewController@getCiudadesAll')->name('ciudades.getCiudadesAll');
Route::post('atm/new/caracteristicas/store', ['as' => 'atmnew.caracteristicas.store', 'uses' => 'Controllers\BranchController@store_caracteristicas']);
Route::resource('bancos', 'Controllers\BancosController');

//Eliminar minis atms
Route::get('atm/new/{id}/delete', ['as' => 'atmnew.delete', 'uses' => 'Controllers\AtmnewController@delete']);

/// ÁREA LEGALES
//Contratos
Route::resource('contracts', 'Controllers\ContractController');
/** Reporte de Contratos miniterminales */
Route::get('reporte/contrato', ['as' => 'reports.contratos', 'uses' => 'Controllers\ContractController@contracts_reports']);
Route::get('reporte/contrato/search', ['as' => 'reports.contratos.search', 'uses' => 'Controllers\ContractController@contractsSearch']);

Route::resource('contract.types', 'Controllers\ContractTypeController');
Route::resource('contracts.insurances', 'Controllers\ContractInsuranceController');
//Polizas
Route::resource('insurances', 'Controllers\InsurancePolicyController');
Route::resource('policy.types', 'Controllers\PolicyTypeController');

//ÁREA DE LOGISTICAS
//Conexión de red
Route::resource('netconections', 'Controllers\NetworkConectionController');
//Tecnología de red
Route::resource('network.technologies', 'Controllers\NetworkTechnologyController');
// Contrato de servicio de internet
Route::resource('internet.contract', 'Controllers\InternetServiceContractController');
///Proveedor de servicios de internet (ISP)
Route::resource('isp', 'IspController');

Route::get('atm/new/{id}/housing', ['as' => 'atmnew.housing', 'uses' => 'Controllers\AtmnewController@housing']);
Route::post('atm/new/{id}/housing/store', ['as' => 'atmnew.housing.store', 'uses' => 'Controllers\AtmnewController@store_housing']);

//Asignacion de aplicaciones - atms version 2
Route::resource('applicationsnew', 'Controllers\ApplicationsnewController');
Route::post('applicationsnew/{id}/assign_atm', [
    'as' => 'applicationsnew.assign_atm',
    'uses' => 'Controllers\ApplicationsnewController@assignAtm'
]);
Route::delete('applicationsnew/{id}/delete_assign_atm', [
    'as' => 'applicationsnew.delete_assigned_atm',
    'uses' => 'Controllers\ApplicationsnewController@removeAssignedAtm'
]);

//ATM Credentials manager version 2
Route::resource('atmnew.credentials', 'Controllers\AtmServicesCredentialsNewController');
Route::post('atmnew/asociar/zona', ['as' => 'zonas.asociar', 'uses' => 'Controllers\ZonasController@asociar']);
Route::get('/getzonas','Controllers\AtmnewController@getZonasAll')->name('zonas.getZonasAll');
Route::post('atmnew/credentials/ondanet', ['as' => 'credentials.ondanet', 'uses' => 'Controllers\AtmServicesCredentialsNewController@store_ondanet']);
Route::post('atmnew/credentials/ondanet/update', ['as' => 'credentials.ondanet.update', 'uses' => 'Controllers\AtmServicesCredentialsNewController@update_ondanet']);
Route::post('atmnew/credentials/ondanet/update/{id}', ['as' => 'credentials.ondanet.update.id', 'uses' => 'Controllers\AtmServicesCredentialsNewController@update_ondanet']);

/**
 * Rutas que apuntan a los controladores de mapas
 */
Route::match(['get', 'post'], 'maps_atms', [
    'as' => 'maps_atms',
    'uses' => 'Controllers\Maps\MapsAtmsController@index'
]);


/**
 * REPORTE DE DMS
 */
Route::get('reports/dms', ['as' => 'reports.dms', 'uses' => 'Controllers\ReportingController@dmsReports']);
Route::get('reports/dms/search', ['as' => 'reports.dms.search', 'uses' => 'Controllers\ReportingController@dmsSearch']);
Route::get('caracteristicas/clientes', ['as' => 'caracteristicas.clientes', 'uses' => 'Controllers\CaracteristicasClientesController@index']);
Route::resource('caracteristicas.clientes', 'Controllers\CaracteristicasClientesController');
Route::get('caracteristicas/clientes/{id}/show', ['as' => 'caracteristicas.show', 'uses' => 'Controllers\CaracteristicasClientesController@show']);
Route::get('caracteristicas/clientes/{id}/edit', ['as' => 'caracteristicas.edit', 'uses' => 'Controllers\CaracteristicasClientesController@edit']);
Route::put('caracteristicas/clientes/{id}/update', ['as' => 'caracteristicas.update', 'uses' => 'Controllers\CaracteristicasClientesController@update']);
Route::post('caracteristicas/clientes/delete', ['as'   => 'caracteristicas.delete', 'uses' => 'Controllers\CaracteristicasClientesController@destroy']);



//CLIENTES CON SALDOS A FAVOR PARA GENERAR TXT
//Route::resource('pago_clientes', 'PagoClienteController');
Route::get('pago_clientes', ['as' => 'pago_clientes', 'uses' => 'Controllers\PagoClienteController@create']);
Route::get('pago_clientes/store', ['as' => 'pago_clientes.store', 'uses' => 'Controllers\PagoClienteController@store']);
Route::post('/pago_clientes/delete', ['as'   => 'pago_clientes.delete', 'uses' => 'Controllers\PagoClienteController@delete']);
Route::get('pago_clientes/register_pago', ['as' => 'pago_clientes.register_pago', 'uses' => 'Controllers\PagoClienteController@index']);
Route::post('pago_clientes/migrate', ['as' => 'pago_clientes.migrate', 'uses' => 'Controllers\PagoClienteController@migrate']);
Route::get('/pago_clientes/get_atms/{id}/', 'Controllers\PagoClienteController@get_atms');

# Reporte Pagos de Clientes
Route::get('pago_clientes/reporte', ['as' => 'reporting.pago_cliente', 'uses' => 'Controllers\ReportingController@pagoClientesReports']);
Route::get('pago_clientes/reporte/search', ['as' => 'reporting.pago_cliente.search', 'uses' => 'Controllers\ReportingController@pagoClientesSearch']);

/** Contratos de miniterminales */
Route::get('reports/contracts', ['as' => 'reports.contracts', 'uses' => 'Controllers\ReportingController@contractsReports']);
Route::get('reports/contracts/search', ['as' => 'reports.contracts.search', 'uses' => 'Controllers\ReportingController@contractsSearch']);



/**
 * Rutas para reglas
 */
Route::match(['get', 'post'], 'service_rule_params/index', [
    'as' => 'service_rule_params.index',
    'uses' => 'ServiceRuleParams\Controllers\ServiceRuleParamsController@index'
]);

Route::match(['get', 'post'], 'service_rule_params/save', [
    'as' => 'service_rule_params.save',
    'uses' => 'ServiceRuleParams\Controllers\ServiceRuleParamsController@save'
]);

/*
|----------------------------------------------------------------------------------------------------------------------+
| Reversiones Routes                                                                               |
+--------+----------+------------------------------+----------------------+--------------------------------------------+
*/
Route::resource('reversiones', 'Controllers\ReversionesController');
/*Geting reversions for Group*/
Route::get('/reports/info/get_reversions_groups/{group_id}/{day}', 'Controllers\ExtractosController@getReversionsForGroups');
Route::get('/reports/info/get_reversions_groups_old/{group_id}/{day}', 'Controllers\ReportingController@getReversionsForGroups');

/*Geting cashouts for Group*/
Route::get('/reports/info/get_cashouts_groups/{group_id}/{day}', 'Controllers\ExtractosController@getCashoutsForGroups');
Route::get('/reports/info/get_cashouts_groups_old/{group_id}/{day}', 'Controllers\ReportingController@getCashoutsForGroups');


/**
 * Rutas para atms_parts
 */
Route::match(['get', 'post'], 'atms_parts', [
    'as' => 'atms_parts',
    'uses' => 'Controllers\AtmsPartsController@index'
]);

/*
|----------------------------------------------------------------------------------------------------------------------+
| Recibos de Comisiones Routes                                                                               |
+--------+----------+------------------------------+----------------------+--------------------------------------------+
*/
Route::resource('recibos_comisiones', 'Controllers\ComisionesController');
/*Geting reversions for Group*/
Route::get('/recibos_comisiones/balance/{atm_id}/{monto}', 'Controllers\ComisionesController@getBalance');
Route::get('/recibos_comisiones/info/{comision_id}', 'Controllers\ComisionesController@getInfo');


/**
 * Rutas para buscar o exportar datos de una tabla seleccionada.
 */
Route::match(['get', 'post'], 'info_table', [
    'as' => 'info_table',
    'uses' => 'Controllers\Info\TableController@index'
]);

/**
 * Rutas para guardar un dato de la tabla
 */
Route::post('info_table_search_by_id', ['as' => 'info_table_search_by_id', 'uses' => 'Controllers\Info\TableController@search_by_id']);

/**
 * Actualizar el registro desde info table
 */
Route::post('info_table_update', ['as' => 'info_table_update', 'uses' => 'Controllers\Info\TableController@update']);

/**
 * Rutas para guardar un dato de la tabla
 */
Route::post('info_table_save', ['as' => 'info_table_save', 'uses' => 'Controllers\Info\TableController@save']);

/**
 * Ruta para exportar una consulta de la db a excel.
 */
Route::match(['get', 'post'], 'info_query_to_export', [
    'as' => 'info_query_to_export',
    'uses' => 'Controllers\Info\QueryToExportController@index'
]);

/**
 * Ruta para convertir un archivo excel a tabla.
 */
Route::match(['get', 'post'], 'info_file_to_table', [
    'as' => 'info_file_to_table',
    'uses' => 'Controllers\Info\FileToTableController@index'
]);

/**
 * Ruta para ver las consultas activas y matarlas
 */
Route::match(['get', 'post'], 'info_stat_activity', [
    'as' => 'info_stat_activity',
    'uses' => 'Controllers\Info\StatActivityController@index'
]);

/**
 * Ruta para el chat
 */
Route::match(['get', 'post'], 'info_chat', [
    'as' => 'info_chat',
    'uses' => 'Controllers\Info\ChatController@index'
]);

Route::post('info_chat_send', ['as' => 'info_chat_send', 'uses' => 'Controllers\Info\ChatController@send']);

/**
 * Ruta para generar diagramas UML
 */
Route::match(['get', 'post'], 'info_plant_uml', [
    'as' => 'info_plant_uml',
    'uses' => 'Controllers\Info\PlantUmlController@index'
]);


/**
 * Ruta para guardar diagramas UML
 */
Route::post('info_plant_uml_save', ['as' => 'info_plant_uml_save', 'uses' => 'Controllers\Info\PlantUmlController@save']);

/*
|----------------------------------------------------------------------------------------------------------------------+
| Formulario de Compra de Saldo                                                                                     |
|----------------------------------------------------------------------------------------------------------------------+
*/

Route::resource('compra_tarex', 'Controllers\CompraTarexController');
Route::get('/reporting/compra_tarex/search_tarex', ['as' => 'compra_tarex.search', 'uses' => 'Controllers\CompraTarexController@search_tarex']);

/*
Route::get('/test', function(){
    try {
        $branches = \DB::table('branches')
            ->select(
                \DB::raw("('{ ' || string_agg('\"' || id || '\" : \"' || description || '\"', ', ') || ' }')::json::text")
            )
            //->groupBy('description');
            //->orderBy('description', 'ASC')
            ->get();

            \Log::info('SEGUNDA LISTA:');
            \Log::info($branches);
    } catch(\Exception $e) {
        $error_detail = [
            'message' => 'Error con query.',
            'error' => $e->getMessage(),
            'file' => $e->getFile(),
            'class' => __CLASS__,
            'function' => __FUNCTION__,
            'line' => $e->getLine()
        ];

        \Log::error($error_detail);
    }
});
*/

/**
 * Rutas para el Tarifario de Comisiones
 */
Route::match(['get', 'post'], 'commissions_parameters_values', [
    'as' => 'commissions_parameters_values',
    'uses' => 'Controllers\Commissions\ParametersValuesControllers@index'
]);

/**
 * Rutas para el Tarifario de Comisiones
 */
Route::post('get_services_by_brand', ['as' => 'get_services_by_brand', 'uses' => 'Controllers\Commissions\ParametersValuesControllers@get_services_by_brand']);

/**
 * Rutas para las Comisiones pagadas
 */
Route::match(['get', 'post'], 'commissions_paid', [
    'as' => 'commissions_paid',
    'uses' => 'Controllers\Commissions\PaidController@index'
]);

/**
 * Rutas para las Comisiones de transacciones para monitoreo
 */
Route::match(['get', 'post'], 'commissions_transactions', [
    'as' => 'commissions_transactions',
    'uses' => 'Controllers\Commissions\TransactionsControllers@index'
]);

/**
 * Rutas para las Comisiones de transacciones para los clientes
 */
Route::match(['get', 'post'], 'commissions_transactions_client', [
    'as' => 'commissions_transactions_client',
    'uses' => 'Controllers\Commissions\TransactionsClientControllers@index'
]);

/**
 * Rutas de Comisiones para los Clientes.
 */
Route::match(['get', 'post'], 'commissions_for_clients', [
    'as' => 'commissions_for_clients',
    'uses' => 'Controllers\Commissions\ForClientsController@index'
]);



/**
 * Rutas para las Comisiones generales por niveles
 */

Route::match(['get', 'post'], 'commissions_generales', [
    'as' => 'commissions_generales',
    'uses' => 'Controllers\Commissions\ViewCommissionController@commissions_generales'
]);

/**
 * Rutas para las Comisiones Facturas
 */

Route::match(['get', 'post'], 'comisionFacturaCliente', [
    'as' => 'comisionFacturaCliente',
    'uses' => 'Controllers\Commissions\ViewCommissionController@comisionFacturaCliente'
]);

Route::match(['get', 'post'], 'comisionFactura', [
    'as' => 'comisionFactura',
    'uses' => 'Controllers\Commissions\ViewCommissionController@comisionFactura'
]);

Route::match(['get', 'post'], 'generarFacturaQr', [
    'as' => 'generarFacturaQr',
    'uses' => 'Controllers\Commissions\ViewCommissionController@generarFacturaQr'
]);


Route::match(['get', 'post'], 'service_detallado', [
    'as' => 'service_detallado',
    'uses' => 'Controllers\Commissions\ViewCommissionController@service_detallado'
]);

Route::match(['get', 'post'], 'service_detallado_nivel3', [
    'as' => 'service_detallado_nivel3',
    'uses' => 'Controllers\Commissions\ViewCommissionController@service_detallado_nivel3'
]);

Route::match(['get', 'post'], 'service_detallado_nivel4', [
    'as' => 'service_detallado_nivel4',
    'uses' => 'Controllers\Commissions\ViewCommissionController@service_detallado_nivel4'
]);



/**
 * Rutas que apuntan a los controladores de mapas
 */
Route::match(['get', 'post'], 'maps_atms', [
    'as' => 'maps_atms',
    'uses' => 'Controllers\Maps\MapsAtmsController@index'
]);

Route::post('load_atms_business_locations', ['as' => 'load_atms_business_locations', 'uses' => 'Controllers\Maps\MapsAtmsController@load_atms_business_locations']);

Route::post('get_promotions_branches', ['as' => 'get_promotions_branches', 'uses' => 'Controllers\Maps\MapsAtmsController@get_promotions_branches']);

Route::post('get_cities', ['as' => 'get_cities', 'uses' => 'Controllers\Maps\MapsAtmsController@get_cities']);

Route::post('get_districts', ['as' => 'get_districts', 'uses' => 'Controllers\Maps\MapsAtmsController@get_districts']);

/*
|----------------------------------------------------------------------------------------------------------------------+
| Modulo de promociones y campañas
|----------------------------------------------------------------------------------------------------------------------+
 */
Route::resource('contents', 'Controllers\Promociones\ContentsController');
Route::resource('campaigns', 'Controllers\CampaignsController');
Route::resource('arts', 'Controllers\ArtsController');
Route::resource('forms', 'Controllers\FormsController');
Route::resource('promotions_vouchers', 'Controllers\PromotionVouchersController');
Route::resource('promotions_vouchers.generate', 'Controllers\PromotionVouchersController');
Route::get('promotions_vouchers/import/{campaingId}', ['as' => 'promotions.vouchers.generate.import', 'uses' => 'Controllers\PromotionVouchersController@import']);
Route::post('promotions_vouchers/store_import/{campaingId}', ['as' => 'promotions.vouchers.generate.store_import', 'uses' => 'Controllers\PromotionVouchersController@store_import']);
Route::resource('tickets', 'Controllers\TicketsController');
Route::resource('tags', 'Controllers\TagsController');
Route::get('campaigns/branches/{provider_id}', ['as' => 'campaigns.branches', 'uses' => 'Controllers\CampaignsController@getBranches']);
Route::post('campaigns/status_campaigns', 'Controllers\CampaignsController@enable_status_campaign');
Route::resource('promotions_categories', 'Controllers\PromotionsCategoriesController');
Route::get('campaigns/{campaign_id}/get_details', ['as' => 'campaigns.get_details', 'uses' => 'Controllers\CampaignsController@getDetails']);
Route::get('campaignTabla', ['as' => 'campaigns.tabla', 'uses' => 'Controllers\CampaignsController@tabla']);
Route::resource('branches_providers', 'Controllers\Promociones\BranchPromotionController');
Route::resource('atmhascampagins', 'Controllers\AtmHasCampaignController');




//--------------------------------------------------------------------------------------------------------------------------------------------------------------------

/**
 * Gestión de usuarios por terminal
 */
Route::match(['get', 'post'], 'atms_per_users_management', [
    'as' => 'atms_per_users_management',
    'uses' => 'Controllers\AtmsPerUsers\AtmsPerUsersManagementControllers@index'
]);

/**
 * Guardar el usuario
 */
Route::post('atms_per_users_management_save', ['as' => 'atms_per_users_management_save', 'uses' => 'Controllers\AtmsPerUsers\AtmsPerUsersManagementControllers@management']);

/**
 * Enviar correo o número de teléfono
 */
Route::post('atms_per_users_management_send', ['as' => 'atms_per_users_management_send', 'uses' => 'Controllers\AtmsPerUsers\AtmsPerUsersManagementControllers@send']);


/**
 * Obtener atms del usuario
 */
Route::post('get_atms_per_user', ['as' => 'get_atms_per_user', 'uses' => 'Controllers\AtmsPerUsers\AtmsPerUsersManagementControllers@get_atms_per_user']);

/**
 * Usuarios por terminal
 */
Route::match(['get', 'post'], 'atms_per_users', [
    'as' => 'atms_per_users',
    'uses' => 'Controllers\AtmsPerUsers\AtmsPerUsersControllers@index'
]);


/**
 * Guardar el estado
 */
Route::post('atms_per_users_save', ['as' => 'atms_per_users_save', 'uses' => 'Controllers\AtmsPerUsers\AtmsPerUsersControllers@atms_per_users_save']);

//--------------------------------------------------------------------------------------------------------------------------------------------------------------------


/*
|----------------------------------------------------------------------------------------------------------------------+
| GENERAR TOKEN DE DROPBOX
|----------------------------------------------------------------------------------------------------------------------+
 */

Route::resource('token_dropbox', 'Controllers\AppTokenDropboxController');


/**
 * Ruta para informe de transacciones
 */
Route::match(['get', 'post'], 'cms_transactions_index', [
    'as' => 'cms_transactions_index',
    'uses' => 'Controllers\Transactions\TransactionsController@index'
]);

/**
 * Ruta para informe de transacciones - devoluciones
 */
Route::match(['get', 'post'], 'cms_transactions_index_devolutions', [
    'as' => 'cms_transactions_index_devolutions',
    'uses' => 'Controllers\Transactions\TransactionsController@index_devolutions'
]);

/**
 * Ruta para informe de servicios que requieren más devoluciones.
 */
Route::match(['get', 'post'], 'cms_services_with_more_returns_index', [
    'as' => 'cms_services_with_more_returns_index',
    'uses' => 'Controllers\Transactions\TransactionsController@index_services_with_more_returns'
]);


// Actualizar la devolución

Route::post('update_transaction_devolution', ['as' => 'update_transaction_devolution', 'uses' => 'Controllers\Transactions\TransactionsController@update_transaction_devolution']);

// Relanzamiento por cambio de ID principal de 

Route::post('relaunch_code_by_change', ['as' => 'relaunch_code_by_change', 'uses' => 'Controllers\Transactions\TransactionsController@relaunch_code_by_change']);

/**
 * Obtener datos para el informe transacciones y devoluciones
 */

Route::post('get_services_by_brand_for_transactions', ['as' => 'get_services_by_brand_for_transactions', 'uses' => 'Controllers\Transactions\TransactionsController@get_services_by_brand_for_transactions']);
Route::post('get_services_for_returns', ['as' => 'get_services_for_returns', 'uses' => 'Controllers\Transactions\OptionsController@get_services_for_returns']);
Route::post('get_history_transaction', ['as' => 'get_history_transaction', 'uses' => 'Controllers\Transactions\OptionsController@get_history_transaction']);
Route::post('get_categories', ['as' => 'get_categories', 'uses' => 'Controllers\Transactions\OptionsController@get_categories']);

/**
 * Rutas que apuntan al web service de Eglobalt
 */
Route::post('cms_get_service_info', ['as' => 'cms_get_service_info', 'uses' => 'Controllers\Transactions\OptionsController@cms_get_service_info']);
Route::post('cms_get_more_service_info', ['as' => 'cms_get_more_service_info', 'uses' => 'Controllers\Transactions\OptionsController@cms_get_more_service_info']);
Route::post('cms_confirm', ['as' => 'cms_confirm', 'uses' => 'Controllers\Transactions\OptionsController@cms_confirm']);
Route::post('cms_get_ticket', ['as' => 'cms_get_ticket', 'uses' => 'Controllers\Transactions\OptionsController@cms_get_ticket']);

/**
 * Configuracion de clientes
 */

Route::resource('canales', 'Controllers\CanalClienteController');
Route::resource('categorias', 'Controllers\CategoriaClienteController');

Route::post('successMiniMoney',['as' => 'successMiniMoney', 'uses' => 'Controllers\MiniCashOutDevolucionController@successMini']);
Route::post('cancelMiniMoney',['as' => 'cancelMiniMoney', 'uses' => 'Controllers\MiniCashOutDevolucionController@cancelMin']);



/*
|----------------------------------------------------------------------------------------------------------------------+
| PROCEDIMIENTO DE BAJA - ATMS
|----------------------------------------------------------------------------------------------------------------------+
 */
Route::get('atm/new/baja', ['as' => 'atms.baja', 'uses' => 'Controllers\AtmnewController@index_baja']);
Route::get('atm/new/{id}/groups_atms', ['as' => 'atms.groups', 'uses' => 'Controllers\AtmnewController@atms_x_group']);
// Route::get('atm/new/{id}/change_status', ['as' => 'change.status.group', 'uses' => 'Controllers\AtmnewController@change_status_group']);
Route::get('atm/new/{id}/{group}/change_status', ['as' => 'change.status.group', 'uses' => 'Controllers\AtmnewController@change_status_group']);
Route::post('atm/new/{id}/update', ['as' => 'change.status.group.update', 'uses' => 'Controllers\AtmnewController@change_status_group_update']);

/**
 * Pagare
 */
// Route::get('atm/new/{id}/pagare', ['as' => 'atm.group.pagare', 'uses' => 'atm_baja\PagaresController@index']);
Route::get('atm/new/{id}/{group}/pagare', ['as' => 'atm.group.pagare', 'uses' => 'Controllers\atm_baja\PagaresController@index']);
Route::resource('pagares', 'Controllers\atm_baja\PagaresController');

/**
 * Nota de rescision
 */
Route::get('atm/new/{id}/{group}/rescision', ['as' => 'atm.group.rescision', 'uses' => 'Controllers\atm_baja\NotaRescisionController@index']);
Route::resource('notarescision', 'Controllers\atm_baja\NotaRescisionController');

/**
 * Nota de retiro
 */
Route::get('atm/new/{id}/{group}/notaretiro', ['as' => 'atm.group.retiro', 'uses' => 'Controllers\atm_baja\NotaRetiroController@index']);
Route::resource('notaretiro', 'Controllers\atm_baja\NotaRetiroController');
Route::get('notaretiro/preview_pdf/{id}', 'Controllers\atm_baja\NotaRetiroController@preview_pdf')->name('notaretiro.preview_pdf');
Route::get('notaretiro/pdf/{id}', 'Controllers\atm_baja\NotaRetiroController@pdf')->name('notaretiro.pdf');
/**
 * Genarar factura por penalizacion
 */
Route::get('atm/new/{id}/{group}/penalizacion', ['as' => 'atm.group.penalizacion', 'uses' => 'Controllers\atm_baja\MultaController@create']);
Route::get('/penalizacion/add_penalty', ['as' => 'penalizacion.add_penalty', 'uses' => 'Controllers\atm_baja\MultaController@add_penalty']);
Route::resource('multas', 'Controllers\atm_baja\MultaController');

/**
 * Retiro de dispositivo
 */
Route::get('atm/new/{id}/{group}/retiro_dispositivo', ['as' => 'atm.group.retiro.dispositivo', 'uses' => 'Controllers\atm_baja\RetiroDispositivoController@index']);
Route::resource('retiro_dispositivos', 'Controllers\atm_baja\RetiroDispositivoController');

Route::get('atm/new/relaunch/procedure/{id}', ['as' => 'atm.group.retiro.relaunch', 'uses' => 'Controllers\atm_baja\RetiroDispositivoController@relanzar']);

/**
 * Presupuesto de reparacion
 */
Route::get('atm/new/{id}/{group}/presupuesto', ['as' => 'atm.group.presupuesto', 'uses' => 'Controllers\atm_baja\PresupuestoController@index']);
Route::resource('presupuestos', 'Controllers\atm_baja\PresupuestoController');

Route::get('atm/new/relaunch/presupuesto/{id}', ['as' => 'atm.group.presupuesto.relaunch', 'uses' => 'Controllers\atm_baja\PresupuestoController@relanzar']);

/**
 * Compromiso de pago
 */
Route::get('atm/new/{id}/{group}/compromiso', ['as' => 'atm.group.compromiso', 'uses' => 'Controllers\atm_baja\CompromisoPagoController@index']);
Route::resource('compromisos', 'Controllers\atm_baja\CompromisoPagoController');

/**
 * Intimacion
 */
Route::get('atm/new/{id}/{group}/intimacion', ['as' => 'atm.group.intimacion', 'uses' => 'Controllers\atm_baja\IntimacionController@index']);
Route::resource('intimaciones', 'Controllers\atm_baja\IntimacionController');


/**
 * Remision de pagare
 */
Route::get('atm/new/{id}/{group}/remision', ['as' => 'atm.group.pagare.remision', 'uses' => 'Controllers\atm_baja\RemisionPagareController@index']);
Route::resource('remisiones', 'Controllers\atm_baja\RemisionPagareController');
/**
 * Recibo de perdida
 */
Route::get('atm/new/{id}/{group}/recibo_perdida', ['as' => 'atm.group.recibo.perdida', 'uses' => 'Controllers\atm_baja\ReciboPerdidaController@index']);
Route::resource('recibos_perdida', 'Controllers\atm_baja\ReciboPerdidaController');
/**
 * Recibo de ganancia
 */
Route::get('atm/new/{id}/{group}/recibo_ganancia', ['as' => 'atm.group.recibo.ganancia', 'uses' => 'Controllers\atm_baja\ReciboGananciaController@index']);
Route::resource('recibos_ganancia', 'Controllers\atm_baja\ReciboGananciaController');
/**
 * Gasto administrativos
 */
Route::get('atm/new/{id}/{group}/gasto_administrativo', ['as' => 'atm.group.recibo.ganancia.gasto', 'uses' => 'Controllers\atm_baja\GastoAdministrativoController@index']);
Route::resource('gastos_administrativo', 'Controllers\atm_baja\GastoAdministrativoController');

/**
 * Imputacion de deudas
 */
Route::get('atm/new/{id}/{group}/imputacion', ['as' => 'atm.group.imputacion', 'uses' => 'Controllers\atm_baja\ImputacionDeudaController@index']);
Route::resource('imputaciones', 'Controllers\atm_baja\ImputacionDeudaController');

/**
 * Cobranzas
 */
// Route::get('atm/new/{id}/{group}/cobranzas', ['as' => 'atm.group.cobranzas', 'uses' => 'atm_baja\CobranzaController@index']);
Route::resource('cobranzas', 'Controllers\atm_baja\CobranzaController');


/**
 * Informe pagos por terminal
 */
Route::match(['get', 'post'], 'terminals_payments', [
    'as' => 'terminals_payments',
    'uses' => 'Controllers\TerminalsPaymentsController@index'
]);

/**
 * Obtener terminales por grupo
 */
Route::post('get_atms_per_group', ['as' => 'get_atms_per_group', 'uses' => 'Controllers\TerminalsPaymentsController@get_atms_per_group']);

Route::get('generar_token_telegram', ['as' => 'generar_token_telegram', 'uses' => 'Controllers\TelegramController@index']);
Route::post('guardar_bot_telegram', ['as' => 'guardar_bot_telegram', 'uses' => 'Controllers\TelegramController@guardar_bot_telegram']);
Route::get('bots/{id}', 'Controllers\TelegramController@show')->name('bots.show');
Route::post('updated_bot_telegram', ['as' => 'updated_bot_telegram', 'uses' => 'Controllers\TelegramController@update']);
Route::delete('/bots/{id}', 'Controllers\TelegramController@destroy');


use App\Services\ReportServices;

Route::get('/test_super_select', function(){
    
    $s = new ReportServices('SI');

    $request = [
        'atm_id' => 2491,
        'context' => 'search',
        'reservationtime' => '11/04/2023 00:00:00 - 11/04/2023 23:59:59',
        'activar_resumen' => 2,
    ];

    return $s->resumenMiniterminalesSearch($request);
});

/**
 * Informe de estado contable unificado
 */
Route::match(['get', 'post'], 'accounting_statement', [
    'as' => 'accounting_statement',
    'uses' => 'Controllers\AccountingStatementController@index'
]);

/**
 * Informe de estado contable unificado
 */
Route::match(['get', 'post'], 'get_details_per_group', [
    'as' => 'get_details_per_group',
    'uses' => 'Controllers\AccountingStatementController@get_details_per_group'
]);