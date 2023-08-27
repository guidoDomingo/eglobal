<section class="content">

    <!-- Modal -->
    <div id="myModal" class="modal fade" role="dialog" data-backdrop="static" data-keyboard="false" href="#">

        <div class="modal-dialog modal-dialog-centered" id="modal_dialog" role="document" style="background: white; border-radius: 5px; width: 700px;">
            
            <!-- Modal content-->
            <div class="modal-content" style="border-radius: 10px;">

                <div class="modal-header">
                    <!--<button type="button" class="close" data-dismiss="modal" id="button_modal_close_x">&times;</button>-->

                    <!--<i id="button_modal_close_x" class="fa fa-times" class="pull-right" title="Cerrar Ventana" data-dismiss="modal" style="background: white; border: none; color: gray; float: right; cursor: pointer"></i>-->

                    <button type="button" class="btn btn-danger btn-sm pull-right" data-dismiss="modal" id="button_modal_close_x" title="Cerrar Ventana"> 
                        <i class="fa fa-times"></i>
                    </button>


                    <h4 class="modal-title">Detalles - Transaccion Nro : <label class="idTransaccion"></label></h4>
                </div>

                <div class="modal-body">

                <?php

                    /**
                     * Variables para mostrar / ocultar los tabs dependiendo del ROL
                     */
                    $super_user = false;
                    $valid_rol = false;

                    $aria_expanded_super_user = false;
                    $aria_expanded_valid_rol = false;

                    $tab_super_user = '';
                    $tab_valid_rol = '';

                    if (\Sentinel::getUser()->inRole('superuser')) {
                        $super_user = true;
                        $aria_expanded_super_user = true;
                        $tab_super_user = 'active';
                    }

                    if (\Sentinel::getUser()->inRole('superuser') or \Sentinel::getUser()->inRole('ATC') or \Sentinel::getUser()->inRole('accounting.admin')) {
                        $valid_rol = true;

                        if (\Sentinel::getUser()->inRole('ATC') or \Sentinel::getUser()->inRole('accounting.admin')) {
                            $tab_valid_rol = 'active';
                            $aria_expanded_valid_rol = true;
                        }
                    }
                ?>

                    <div class="nav-tabs-custom">
                        <ul class="nav nav-tabs">
                            <li class="active" id="li_tab_1">
                                <a href="#tab_1" data-toggle="tab" aria-expanded="true">
                                    <i class="fa fa-info"></i> &nbsp Información
                                </a>
                            </li>

                            @if ($valid_rol)
                                <li class="" id="li_tab_2">
                                    <a href="#tab_2" data-toggle="tab" aria-expanded="false">
                                        <i class="fa fa-ticket"></i> &nbsp Ticket
                                    </a>
                                </li>

                                <li class="" id="li_tab_3">
                                    <a href="#tab_3" data-toggle="tab" aria-expanded="false">
                                     <i class="fa fa-list-ul"></i> &nbsp JSON's
                                    </a>
                                </li>
                            @endif
                        </ul>
                        <div class="tab-content">
                            <div class="tab-pane active" id="tab_1">
                                <table class="table table-bordered table-hover dataTable" role="grid" id="detalles">

                                    <thead>
                                        <tr>
                                            <th>Parte</th>
                                            <th>Tipo</th>
                                            <th>Denominación</th>
                                            <th>Cantidad</th>
                                            <th>Precinto</th>
                                        </tr>
                                    </thead>

                                    <tbody id="modal-contenido" style="text-align: center">

                                    </tbody>
                                </table>

                                <div id="status_description"></div>

                                <table id="payment_details" style="display: none;" class="table table-bordered table-hover dataTable" role="grid" aria-describedby="Table2_info">
                                    <thead>
                                        <tr role="row">
                                            <th style="display:none;" class="sorting_disabled" rowspan="1" colspan="1"></th>
                                            <th style="display:none;" class="sorting_disabled" rowspan="1" colspan="1"></th>
                                            <th class="sorting_disabled" rowspan="1" colspan="1">Valor a pagar</th>
                                            <th class="sorting_disabled" rowspan="1" colspan="1">Valor recibido</th>
                                            <th class="sorting_disabled" rowspan="1" colspan="1">Valor devuelto</th>
                                            <th class="sorting_disabled" rowspan="1" colspan="1">Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody id="modal-contenido-payments">

                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th style="display:none;" rowspan="1" colspan="1"></th>
                                            <th style="display:none;" rowspan="1" colspan="1"></th>
                                            <th rowspan="1" colspan="1">Valor a pagar</th>
                                            <th rowspan="1" colspan="1">Valor recibido</th>
                                            <th rowspan="1" colspan="1">Valor devuelto</th>
                                            <th rowspan="1" colspan="1">Fecha</th>
                                        </tr>
                                    </tfoot>
                                </table>

                                <div id="devoluciones" style="display: none">
                                    <div id="keys_spinn" class="text-center" style="margin: 50px 10px; display: none;"><i class="fa fa-circle-o-notch fa-spin" style="font-size:24px"></i></div>
                                    <div id="message_box" class="display: none;"></div>
                                    <form role="form" id="devolucion-form" enctype="multipart/form-data">
                                        <div class="box-body">
                                            <div class="form-group">
                                                <label for="txtDescription">Descripción</label>
                                                <textarea id="txtDescription" name="txtDescription" class="form-control" rows="3" placeholder="Describa brevemente el caso ..."></textarea>
                                                <input type="hidden" id="txttransaction_id">
                                            </div>
                                            <div class="form-group">
                                                <label for="fuComprobante">Adjunte un comprobante</label>
                                                <input type="file" id="fuComprobante" name="fuComprobante">

                                                <p class="help-block">El archivo debe ser una imagen.</p>
                                            </div>
                                        </div>
                                        <!-- /.box-body -->
                                    </form>
                                </div>

                                <div id="reprocesos" style="display: none">
                                    <div id="reprocesar-info">
                                        <p><b>Servicio:</b> <span id="service_description"></span></p>
                                        <p><b>Monto:</b> <span id="transaction_amount"></span></p>
                                        <p><b>Referencia:</b> <span id="transaction_referece"></span></p>
                                    </div>
                                </div>

                                <div id="inconsistencias" style="display: none">
                                    <div id="inconsistencia-info">
                                        <p><b>Transaccion ID :</b> <span id="transaction_id"></span></p>
                                    </div>
                                </div>

                                <div id="reversiones" style="display: none">
                                    <div id="reversion-info">
                                        <p><b>Transaccion ID :</b> <span id="id_transaccion"></span></p>
                                    </div>
                                </div>

                                <div id="reversion_description" style="display: none">
                                    <div id="reversion-info">
                                        <br>
                                        <p><b>Fecha de Reversion:</b> <span id="fecha_reversion"></span></p>
                                        <p><b>Reversado por:</b> <span id="rever_user"></span></p>
                                    </div>
                                </div>
                            </div>

                            @if ($valid_rol)
                                <div class="tab-pane" id="tab_2">

                                    <div class="row" id="div_load_transaction_ticket">
                                        <div class="col-md-4"></div>

                                        <div class="col-md-4">
                                            <div style="text-align: center; font-size: 20px;">
                                                <button type="button" class="btn btn-primary btn-block" style="vertical-align: sub;" id="button_load_transaction_ticket">
                                                    <i class="fa fa-ticket"></i> &nbsp; Ver Ticket de Transacción
                                                </button>
                                            </div>
                                        </div>

                                        <div class="col-md-4"></div>
                                    </div>

                                    <div class="alert alert-error alert-dismissable" style="display: none;" id="alert_error_transaction_ticket">
                                        <h4><i class="icon fa fa-times"></i> Ticket no encontrado ... </h4>
                                    </div>

                                    <div class="row" id="div_transaction_ticket" style="display: none; overflow-y: scroll;">

                                        <div class="col-md-12" id="view_transaction_ticket" 
                                            style="
                                                text-align: center; 
                                                max-height: 50vh; 
                                                pointer-events: none;
                                                cursor: none;
                                                -webkit-user-select: none;
                                                -ms-user-select: none;
                                                user-select: none
                                            ">

                                                <div id="contenedor_aux"></div> 

                                                <div class="watermark_ticket" 
                                                    style="
                                                        text-align: center; 
                                                        font-size: 4em; 
                                                        color: #dd4b39; 
                                                        opacity: .7; 
                                                        position: fixed; 
                                                        top: 35%; 
                                                        left: 33%; 
                                                        transform: translate(-50%, -50%); 
                                                        -webkit-transform: rotate(-40deg); 
                                                        -o-transform: rotate(-40deg); 
                                                        transform: rotate(-40deg)
                                                    "> 
                                                    
                                                    <h1> <i class="fa fa-copy"></i> <br/> Es una copia <br/> Es una copia <br/> Es una copia </h1> 
                                                </div>

                                        </div>

                                    </div>

                                </div>

                                <div class="tab-pane" id="tab_3">

                                    <br/>

                                    <div class="nav-tabs-custom">
                                        <ul class="nav nav-tabs">

                                            @if ($super_user)

                                                <li class="{{ $tab_super_user }}" id="li_tab_request_data">
                                                    <a href="#tab_json_request_data" data-toggle="tab" aria-expanded="{{ $aria_expanded_super_user }}">
                                                        Consulta y Respuesta ( transactions )
                                                    </a>
                                                </li>

                                                <!--<li class="" id="li_tab_response_data">
                                                    <a href="#tab_json_response_data" data-toggle="tab" aria-expanded="false">
                                                        Respuesta ( response_data )
                                                    </a>
                                                </li>-->

                                                <li class="" id="li_tab_transaction_requests">
                                                    <a href="#tab_json_transaction_requests" data-toggle="tab" aria-expanded="false">
                                                        Consulta y Respuesta ( transaction_requests )
                                                    </a>
                                                </li>

                                            @endif

                                            @if ($valid_rol)

                                                <li class="{{ $tab_valid_rol }}" id="li_tab_json_service">
                                                    <a href="#tab_json_service" data-toggle="tab" aria-expanded="{{ $aria_expanded_valid_rol }}">
                                                        Consulta y Respuesta ( Servicio )
                                                    </a>
                                                </li>

                                            @endif


                                        </ul>

                                        <div class="tab-content">

                                            @if ($super_user)

                                                <div class="tab-pane {{ $tab_super_user }}" id="tab_json_request_data">

                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="box box-default" style="border: 1px solid #d2d6de;">
                                                                <div class="box-header with-border">
                                                                    <h3 class="box-title">Consulta ( request_data )</h3>
                                                                </div>
                                                                <div class="box-body">
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            
                                                                            <div class="alert alert-error alert-dismissable" style="display: none;" id="alert_error_request_data">
                                                                                <h4><i class="icon fa fa-times"></i> La transacción no tiene request_data en transactions.</h4>
                                                                            </div>

                                                                            <pre id="json_renderer_request_data" style="background: white;"></pre>

                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-6">

                                                            <div class="box box-default" style="border: 1px solid #d2d6de;">
                                                                <div class="box-header with-border">
                                                                    <h3 class="box-title">Respuesta ( response_data )</h3>
                                                                </div>
                                                                <div class="box-body">
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            
                                                                            <div class="alert alert-error alert-dismissable" style="display: none;" id="alert_error_response_data">
                                                                                <h4><i class="icon fa fa-times"></i> La transacción no tiene response_data en transactions.</h4>
                                                                            </div>

                                                                            <pre id="json_renderer_response_data" style="background: white;"></pre>

                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                    </div>
                                                </div>

                                                <!--<div class="tab-pane" id="tab_json_response_data">

                                                    <div class="alert alert-error alert-dismissable" style="display: none;" id="alert_error_response_data">
                                                        <h4><i class="icon fa fa-times"></i> La transacción no tiene response_data en transactions.</h4>
                                                    </div>

                                                    <pre id="json_renderer_response_data" style="background: white;"></pre>
                                                </div>-->

                                                <div class="tab-pane" id="tab_json_transaction_requests">

                                                    <div class="row" id="div_load_transaction_requests">
                                                        <div class="col-md-4"></div>

                                                        <div class="col-md-4">
                                                            <div style="text-align: center; font-size: 20px;">
                                                                <button type="button" class="btn btn-primary btn-block" style="vertical-align: sub;" id="button_load_transaction_requests">
                                                                    <i class="fa fa-list"></i> &nbsp; Ver Requests de Transacción
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-4"></div>
                                                    </div>

                                                    <div class="alert alert-error alert-dismissable" style="display: none;" id="alert_error_transaction_requests">
                                                        <h4><i class="icon fa fa-times"></i> La transacción no tiene ningún request en transaction_requests.</h4>
                                                    </div>

                                                    <div class="row" id="div_datatable_transaction_requests" style="display: none">
                                                        <div class="col-md-12">
                                                            <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_transaction_requests">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Campos ( get_fields_data )</th>
                                                                        <th>Consulta ( post_fields_data )</th>
                                                                        <th>Respuesta ( response_fields_data )</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody></tbody>
                                                            </table>
                                                        </div>
                                                    </div>

                                                </div>

                                            @endif

                                            @if ($valid_rol)

                                                <div class="tab-pane {{ $tab_valid_rol }}" id="tab_json_service">

                                                    <div class="row" id="div_load_jsons_service">
                                                        <div class="col-md-4"></div>

                                                        <div class="col-md-4">
                                                            <div class="box box-default" style="border-radius: 5px; margin-top: 50px">
                                                                <div class="box-body">
                                                                    <div style="text-align: center; font-size: 20px;">
                                                                        <div>
                                                                            <i class="fa fa-spin fa-cog fa-2x" style="vertical-align: sub;"></i> &nbsp;
                                                                            Cargando...
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        <div class="col-md-4"></div>
                                                    </div>

                                                    <div class="alert alert-error alert-dismissable" style="display: none;" id="alert_error_jsons_service">
                                                        <h4><i class="icon fa fa-times"></i> La transacción no tiene ningún JSON del servicio.</h4>
                                                    </div>

                                                    <div class="row" id="div_datatable_jsons_service" style="display: none">
                                                        <div class="col-md-12">
                                                            <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_jsons_service">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Datos</th>
                                                                        <th>Consulta ( json )</th>
                                                                        <th>Respuesta ( response_data )</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody></tbody>
                                                            </table>
                                                        </div>
                                                    </div>

                                                </div>

                                            @endif
                                        </div>
                                    </div>
                                    
                                </div>
                            @endif

                        </div>

                    </div>

                </div>
                <div class="modal-footer">
                    <!--para activar modals con formularios para reproceso y devolución respectivamente -->
                    <button type="button" style="display: none" class="reprocesar btn btn-primary pull-left">Reprocesar</button>
                    <button type="button" style="display: none" class="devolucion btn btn-primary pull-left">Devolución</button>

                    <!--para ejecutar tareas de reproceso o devolucion -->
                    <button type="button" style="display: none" id="process_devolucion" class="btn btn-primary pull-left">Enviar a devolución</button>
                    <button type="button" style="display: none" id="run_reprocesar" class="btn btn-primary pull-left">Enviar a Reprocesar</button>

                    <!--para ejecutar inconsistencia -->
                    <button type="button" style="display: none" class="inconsistencia btn btn-primary pull-left">Generar
                        inconsistencia</button>
                    <button type="button" style="display: none" id="process_inconsistencia" class="btn btn-primary pull-left">Generar inconsistencia</button>

                    <!--para ejecutar reversiones -->
                    <button type="button" style="display: none" class="reversion btn btn-primary pull-left">Generar
                        Reversion</button>
                    <button type="button" style="display: none" id="process_reversion" class="btn btn-primary pull-left">Generar Reversion</button>
                    <!--para Cancelar sin hacer nada -->
                    <button type="button" class="btn btn-danger pull-right" data-dismiss="modal" id="button_modal_close" title="Cerrar Ventana"> 
                        <i class="fa fa-close"></i> &nbsp; Cerrar Ventana
                    </button>
                </div>
            </div>

        </div>
    </div>
    <!-- Print Section -->
    <div id="printSection" class="printSection" style="visibility:hidden;"></div>
    <div class="row">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Filtros de búsqueda</h3>

                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div>
                </div>

                <!-- /.box-header -->
                <form action="{{ route('reports.transactions.search') }}" method="GET">
                    <div class="box-body" style="display: block;">

                        @if (!\Sentinel::getUser()->inRole('mini_terminal') && !\Sentinel::getUser()->inRole('supervisor_miniterminal'))

                        <div class="row">
                            <div class="col-md-6">

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            {!! Form::label('groups', 'Grupos') !!}
                                            {!! Form::select('group_id', $groups, $group_id, ['id' => 'group_id', 'class' => 'form-control select2']) !!}
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {!! Form::label('redes', 'Redes') !!}
                                            {!! Form::select('owner_id', $owners, $owner_id, ['id' => 'owner_id', 'class' => 'form-control select2']) !!}
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {!! Form::label('tipoAtm', 'Canal') !!}
                                            {!! Form::select('type', $type, $type_set, ['class' => 'form-control select2']) !!}
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            {!! Form::label('sucursales', 'Sucursales') !!}
                                            {!! Form::select('branch_id', $branches, $branch_id, ['id' => 'branch_id', 'class' => 'form-control select2']) !!}
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div style="border: 1px solid #d2d6de; border-radius: 5px; background-color: #ecf0f5; padding: 5px">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    {!! Form::label('pdv', 'Puntos de venta') !!}
                                                    {!! Form::select('pos_id', $pos, $pos_id, ['id' => 'pos_id', 'class' => 'select2 form-control']) !!}
                                                </div>

                                                <div class="col-md-4">
                                                    <br />
                                                    <div style="padding: 5px;" title="Filtrar Puntos de ventas inactivos">
                                                        <input type="checkbox" id="pos_active" name="pos_active"></input> &nbsp; <b>Filtrar Inactivos</b> &nbsp;
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <br />

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {!! Form::label('estados', 'Estados') !!}
                                            {!! Form::select('status_id', $status, $status_set, ['class' => 'form-control select2']) !!}
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Medios de pago</label>
                                            {!! Form::select('payment_method_id', $payment_methods, $payment_methods_set, ['class' => 'form-control select2', 'id' => 'serviceId']) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Rango de Tiempo & Fecha:</label>
                                            <div class="input-group">
                                                <div class="input-group-addon">
                                                    <i class="fa fa-clock-o"></i>
                                                </div>
                                                <input name="reservationtime" type="text" id="reservationtime" class="form-control pull-right" value="{{ old('reservationtime', $reservationtime ?? '') }}" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>ID de transacción:</label>
                                            <div class="input-group">
                                                <div class="input-group-addon">
                                                    ID
                                                </div>
                                                <input type="number" id="transaction_id" name="transaction_id" class="form-control" placeholder="Buscar por ID" />
                                            </div>
                                        </div>
                                    </div>

                                    <!--<div class="col-md-6">
                                            <div class="form-group">
                                                <label>Monto de transacción:</label>
                                                <div class="input-group">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-money"></i>
                                                    </div>
                                                    <input type="number" id="amount" name="amount" class="form-control" placeholder="Buscar por monto"/>
                                                </div>
                                            </div>
                                        </div>-->
                                </div>

                                <br>

                                <div class="row">
                                    <div class="col-md-2"></div>

                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-primary btn-block" name="search" value="search" id="buscar">
                                            <i class="fa fa-search"></i> &nbsp; Buscar
                                        </button>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-success btn-block" name="download" value="download">
                                            <i class="fa fa-file-excel-o"></i> &nbsp; Exportar
                                        </button>
                                    </div>

                                    <div class="col-md-2"></div>
                                </div>
                            </div>
                        </div>


                        <div style="border: 1px solid #d2d6de; border-radius: 5px; background-color: #ecf0f5; padding: 5px">
                            <div class="row">
                                <div class="col-md-3">
                                    <label>Tipo de transacción</label>
                                    {!! Form::select('service_id', $services_data, $service_id, ['class' => 'select2 form-control', 'id' => 'serviceId']) !!}
                                </div>
                                <div class="col-md-3 mostrar">
                                    <label>Servicio</label>
                                    {!! Form::select('service_request_id', [0], $service_request_id, ['class' => 'select2 form-control', 'id' => 'servicioRequestId']) !!}
                                </div>
                                <div class="col-md-3 mostrar">
                                    <div class="form-group">
                                        <label>Monto de transacción:</label>
                                        <div class="input-group">
                                            <div class="input-group-addon">
                                                <i class="fa fa-money"></i>
                                            </div>
                                            <input type="number" id="amount" name="amount" class="form-control" placeholder="Buscar por monto" min="0" />
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!--
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {!! Form::label('groups', 'Grupos') !!}
                                        {!! Form::select('group_id', $groups, $group_id, ['id' => 'group_id', 'class' => 'form-control select2']) !!}
                                    </div>
                                    <div class="form-group">
                                        {!! Form::label('redes', 'Redes') !!}
                                        {!! Form::select('owner_id', $owners, $owner_id, ['id' => 'owner_id', 'class' => 'form-control select2']) !!}
                                    </div>
                                    <div class="form-group">
                                        {!! Form::label('tipoAtm', 'Canal') !!}
                                        {!! Form::select('type', $type, $type_set, ['class' => 'form-control select2']) !!}
                                    </div>
                          
                                    <div class="form-group">
                                        {!! Form::label('sucursales', 'Sucursales') !!}
                                        {!! Form::select('branch_id', $branches, $branch_id, ['id' => 'branch_id', 'class' => 'form-control select2']) !!}
                                    </div>
                        

                                    <div class="input-group" style="border: 1px solid #d2d6de; border-radius: 5px; background-color: #ecf0f5; padding: 5px">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <div class="form-group">
                                                    {!! Form::label('pdv', 'Puntos de venta') !!}
                                                    {!! Form::select('pos_id', $pos, $pos_id, ['id' => 'pos_id', 'class' => 'form-control select2']) !!}
                                                </div>
                                            </div>

                                            <div class="col-md-4">
                                                <br/>
                                                <div style="padding: 5px;" title="Filtrar Puntos de ventas inactivos">
                                                    <input type="checkbox" id="pos_active" name="pos_active"></input> &nbsp; <b>Filtrar Inactivos</b> &nbsp;
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <br/>

                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Rango de Tiempo & Fecha:</label>
                                        <div class="input-group">
                                            <div class="input-group-addon">
                                                <i class="fa fa-clock-o"></i>
                                            </div>
                                            <input name="reservationtime" type="text" id="reservationtime"
                                                class="form-control pull-right"
                                                value="{{ old('reservationtime', $reservationtime ?? '') }}" />
                                        </div>
                                    </div>
                                    <br>
                                    <div class="row">
                                        <div class="col-md-4">
                                            <button type="submit" class="btn btn-block btn-primary" name="search"
                                                value="search" id="buscar">BUSCAR</button>
                                        </div>
                                        <div class="col-md-4">
                                            <button type="submit" class="btn btn-block btn-success" name="download"
                                                value="download">EXPORTAR</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
  
                            <div class="row">
                                <div class="col-md-2">
                                    <div class="form-group">
                                        {!! Form::label('estados', 'Estados') !!}
                                        {!! Form::select('status_id', $status, $status_set, ['class' => 'form-control select2']) !!}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Medios de pago</label>
                                        {!! Form::select('payment_method_id', $payment_methods, $payment_methods_set, ['class' => 'form-control select2', 'id' => 'serviceId']) !!}
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>Tipo de transacción</label>
                                        {!! Form::select('service_id', $services_data, $service_id, ['class' => 'form-control select2', 'id' => 'serviceId']) !!}
                                    </div>
                                </div>

                                <div class="col-md-2 mostrar">
                                    <div class="form-group">
                                        <label>Servicio</label>
                                        {!! Form::select('service_request_id', [0], $service_request_id, ['class' => 'form-control select2', 'id' => 'servicioRequestId']) !!}
                                    </div>
                                </div>
                            </div>
                        -->


                        @elseif (\Sentinel::getUser()->inRole('mini_terminal'))
                        <!-- /.row -->
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    {!! Form::label('estados', 'Estados') !!}
                                    {!! Form::select('status_id', $status, $status_set, ['class' => 'form-control select2']) !!}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Tipo de transacción</label>
                                    {!! Form::select('service_id', $services_data, $service_id, ['class' => 'form-control select2', 'id' => 'serviceId']) !!}
                                </div>
                            </div>
                            <div class="col-md-3 mostrar">
                                <div class="form-group">
                                    <label>Servicio</label>
                                    {!! Form::select('service_request_id', [0], $service_request_id, ['class' => 'form-control select2', 'id' => 'servicioRequestId']) !!}
                                </div>
                            </div>
                        </div>
                        <!-- /.col -->
                        <div class="col-md-6">
                            <!-- Date and time range -->
                            <!-- Date and time range -->
                            <div class="form-group">
                                <label>Rango de Tiempo & Fecha:</label>
                                <div class="input-group">
                                    <div class="input-group-addon">
                                        <i class="fa fa-clock-o"></i>
                                    </div>
                                    <input name="reservationtime" type="text" id="reservationtime" class="form-control pull-right" value="{{ old('reservationtime', $reservationtime ?? '') }}" />
                                </div>
                                <!-- /.input group -->
                            </div>
                            <!-- /.form group -->
                            <br>
                            <div class="row">
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-block btn-primary" name="search" value="search">BUSCAR</button>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-block btn-success" name="download" value="download">EXPORTAR</button>
                                </div>
                            </div>
                        </div>
                        @elseif (\Sentinel::getUser()->inRole('supervisor_miniterminal'))
                        <!-- /.row -->
                        <div class="row">
                            <div class="col-md-3">
                                {{-- <div class="form-group">
                                        {!! Form::label('sucursales', 'Sucursales') !!}
                                        {!! Form::select('branch_id', $branches,  $branch_id , ['id' => 'branch_id','class' => 'form-control select2']) !!}
                                    </div> --}}
                                <div class="form-group">
                                    {!! Form::label('user', 'Sucursal') !!}
                                    {!! Form::select('user_id', $branches, $branch_id, ['id' => 'user_id', 'class' => 'form-control select2', 'placeholder' => 'Seleccione el usuario']) !!}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    {!! Form::label('estados', 'Estados') !!}
                                    {!! Form::select('status_id', $status, $status_set, ['class' => 'form-control select2']) !!}
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Tipo de transacción</label>
                                    {!! Form::select('service_id', $services_data, $service_id, ['class' => 'form-control select2', 'id' => 'serviceId']) !!}
                                </div>
                            </div>
                            <div class="col-md-3 mostrar">
                                <div class="form-group">
                                    <label>Servicio</label>
                                    {!! Form::select('service_request_id', [0], $service_request_id, ['class' => 'form-control select2', 'id' => 'servicioRequestId']) !!}
                                </div>
                            </div>
                        </div>
                        <!-- /.col -->
                        <div class="col-md-6">
                            <!-- Date and time range -->
                            <!-- Date and time range -->
                            <div class="form-group">
                                <label>Rango de Tiempo & Fecha:</label>
                                <div class="input-group">
                                    <div class="input-group-addon">
                                        <i class="fa fa-clock-o"></i>
                                    </div>
                                    <input name="reservationtime" type="text" id="reservationtime" class="form-control pull-right" value="{{ old('reservationtime', $reservationtime ?? '') }}" />
                                </div>
                                <!-- /.input group -->
                            </div>
                            <!-- /.form group -->
                            <br>
                            <div class="row">
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-block btn-primary" name="search" value="search">BUSCAR</button>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-block btn-success" name="download" value="download">EXPORTAR</button>
                                </div>
                            </div>
                        </div>
                        @endif



                    </div>
                </form>
            </div>
        </div>
    </div>

    @if (isset($transactions) and $transactions_total > 0)

    <div class="row">
        <div class="col-md-8">
            <div class="box box-default" style="border: 1px solid #d2d6de;">
                <div class="box-header with-border">
                    <h3 class="box-title">Resumen de totales:</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box" style="background-color: aliceblue !important; color: #444;">
                                <span class="info-box-icon"><i class="fa fa-list"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Cantidad de Transacciones</span>
                                    <span class="info-box-number" style="font-size: 30px" id="number_of_transactions">{{ $transactions_total }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box" style="background-color: aliceblue !important; color: #444;">
                                <span class="info-box-icon"><i class="fa fa-money"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Monto total de Transacciones</span>
                                    <span class="info-box-number" style="font-size: 30px" id="total_amount_of_transactions"><b>{{ $total_transactions }}</b></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!--Si es mayor a 19 que es el limite por página mostramos el páginador-->
            @if (count($transactions) > 19)
            <div class="box box-default" style="border: 1px solid #d2d6de;">
                <div class="box-header with-border">
                    <h3 class="box-title">Ir a la página:</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="dataTables_paginate paging_simple_numbers">

                                {!! $transactions->appends(
                                ['group_id' => $group_id, 'owner_id' => $owner_id, 'type' => $type_set,
                                'branch_id' => $branch_id, 'pos_id' => $pos_id, 'status_id' => $status_set, 'service_id' => $service_id,
                                'reservationtime' => $reservationtime, 'service_request_id' => $service_request_id
                                ])->render() !!}

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>

    <!--<div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Totales</h3>
                    </div>

                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="dataTables_info" role="status" aria-live="polite">
                                    {{ $transactions_total }} registros en total</div>
                            </div>
                            <div class="col-md-6">
                                <div class="dataTables_info" role="status" aria-live="polite">
                                    Monto total: <b>{{ $total_transactions }}</b> <i class="fa fa-money"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>-->

    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Resultados</h3>
                    <div class="box-tools">
                        <div class="input-group" style="width:150px;">
                            {!! Form::model(Request::only(['context']), ['route' => 'reports.transactions.search', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
                            {!! Form::text('context', null, ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Buscar', 'autocomplete' => 'off']) !!}
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body" style="overflow: scroll">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_1">
                                <thead>
                                    <tr>
                                        <th style="max-width:50px;">ID</th>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                        <th>Valor Transacción</th>
                                        @if (\Sentinel::getUser()->inRole('superuser'))
                                        <th>Monto Comisión</th>
                                        @endif
                                        <th style="max-width:100px;">Cód. Pago</th>
                                        <th>Identificador de transacción</th>
                                        <th>Factura nro</th>
                                        <th>Sede</th>
                                        <th>Ref 1</th>
                                        <th>Ref 2</th>
                                        <th>Codigo Cajero</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($transactions as $transaction)
                                    <?php
                                    if ($transaction->status == 'success') {
                                        if (is_null($transaction->reversion_id)) {
                                            $transaction->status = '<span class="label label-success">' . $transaction->status . '</span>';
                                        } else {
                                            $transaction->status = '<span class="label label-primary label_reversion">' . $transaction->status . '</span>';
                                        }
                                    } elseif ($transaction->status == 'canceled' || $transaction->status == 'iniciated') {
                                        $transaction->status = '<span class="label label-warning">' . $transaction->status . '</span>';
                                    } elseif ($transaction->status == 'inconsistency') {
                                        $transaction->status = '<span class="label label-danger">' . 'Inconsistencia' . '</span>';
                                    } else {
                                        $transaction->status = '<span class="label label-danger">' . $transaction->status . '</span>';
                                    }
                                    ?>


                                    <tr 
                                        data-id="{{ $transaction->id }}" 
                                        data-description="{{ $transaction->provider }} - {{ $transaction->servicio }}" 
                                        data-amount="{{ $transaction->amount }}" 
                                        data-ref1="{{ $transaction->referencia_numero_1 }}" 
                                        data-ref2="{{ $transaction->referencia_numero_2 }}" 
                                        data-estado="{{ $transaction->estado }}" 
                                        data-payid="{{ $transaction->cod_pago }}" 
                                        data-status="{{ $transaction->status_description }}" 
                                        data-transaction="{{ $transaction->atm_transaction_id }}"
                                        data-service_source_id="{{ $transaction->service_source_id }}" 
                                        data-service_id="{{ $transaction->service_id }}">
                                        
                                    
                                        <td align="left" class="{{ $transaction->id }}">
                                            ID: {{ $transaction->id }} <br>
                                            <div class="btn-group">

                                                <button class="info btn btn-default btn-xs" title="Mostrar info">
                                                    <i class="fa fa-info-circle" style="cursor:pointer"></i>
                                                </button>

                                                @if (\Sentinel::getUser()->hasAccess('reporting.print'))
                                                <button class="btn btn-default btn-xs" title="Reimprimir Ticket">
                                                    <i class="print fa fa-print"></i>
                                                </button>
                                                @endif
                                                @if (\Sentinel::getUser()->owner_id == 45)
                                                <button class="btn btn-default btn-xs" title="Reimprimir Ticket">
                                                    <i class="print fa fa-print"></i>
                                                </button>
                                                @endif
                                            </div>
                                        </td>

                                        @if ($transaction->service_source_id == 0)
                                        <td>{{ $transaction->provider }} -
                                            {{ $transaction->servicio }}
                                        </td>
                                        @else
                                        <td>{{ $transaction->proveedor }} - {{ $transaction->tipo }}
                                        </td>
                                        @endif

                                        <td class="status" style="cursor:pointer">
                                            {!! $transaction->status !!}
                                        </td>
                                        <td>{{ $transaction->created_at }}</td>
                                        @if ($transaction->forma_pago == 'efectivo')
                                        <td align="right">{{ $transaction->amount }} <i title="Efectivo" class="fa fa-money"></i> </td>
                                        @elseif($transaction->forma_pago == 'canje')
                                        <td align="right">{{ $transaction->amount }} <i title="Canje" class="fa fa-tags"></i></td>
                                        @elseif($transaction->forma_pago == 'TC')
                                        <td align="right">{{ $transaction->amount }} <i title="TC" class="fa fa-credit-card"></i></td>
                                        @elseif($transaction->forma_pago == 'TD')
                                        <td align="right">{{ $transaction->amount }} <i title="TD" class="fa fa-credit-card"></i></td>
                                        @elseif($transaction->forma_pago == 'DC')
                                        <td align="right">{{ $transaction->amount }} <i title="DC" class="fa fa-credit-card"></i></td>
                                        @elseif($transaction->transaction_type == 12 || $transaction->transaction_type == 13 || $transaction->transaction_type == 17)
                                        <td align="right">{{ $transaction->amount }} <i title="DC" class="fa fa-barcode"> - Qr</i></td>
                                        @else
                                        <td align="right"> {{ $transaction->amount }} |
                                            {{ $transaction->forma_pago }}
                                        </td>
                                        @endif
                                        @if (\Sentinel::getUser()->inRole('superuser'))
                                        <td>{{ $transaction->commission_amount }}</td>
                                        @endif
                                        @if ($transaction->cod_pago == '')
                                        <td align="right" style="color: red">
                                            <button class="btn btn-danger btn-xs" title="Transacción no posee cod Pago">
                                                <i class="pay-info fa fa-warning"></i>
                                            </button>
                                        </td>
                                        @else
                                        <td align="right">{{ $transaction->cod_pago }}
                                            <button class="btn btn-default btn-xs" title="Mostrar info">
                                                <i class="pay-info fa fa-eye" style="cursor:pointer"></i>
                                            </button>
                                        </td>
                                        @endif

                                        <td align="right">{{ $transaction->identificador_transaction_id }}
                                        </td>
                                        <td align="right">{{ $transaction->factura_numero }}</td>
                                        <td>{{ $transaction->sede }}</td>
                                        <td align="right">{{ $transaction->referencia_numero_1 }}</td>
                                        <td align="right">{{ $transaction->referencia_numero_2 }}</td>
                                        <td align="right">{{ $transaction->code }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="box-footer clearfix">
                    <div class="row">
                        <div class="col-sm-7">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @elseif($search)
    <div class="box box-danger">
        <div class="box-header with-border">
            <h1 class="box-title">Sin resultados en la búsqueda.</h1>
        </div>
    </div>
    @endif
</section>

@section('js')
<!-- InputMask -->
<script src="/bower_components/admin-lte/plugins/input-mask/jquery.inputmask.js"></script>
<script src="/bower_components/admin-lte/plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
<script src="/bower_components/admin-lte/plugins/input-mask/jquery.inputmask.extensions.js"></script>
<!-- date-range-picker -->
<link href="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css" />
<script src="/bower_components/admin-lte/plugins/daterangepicker/moment.min.js"></script>
<script src="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker.js"></script>

<!-- bootstrap datepicker -->
<script src="/bower_components/admin-lte/plugins/datepicker/bootstrap-datepicker.js"></script>

<script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>

<!-- datatables -->
<link rel="stylesheet" href="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.css">
<script src="/bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.min.js"></script>


<!-- iCheck -->
<link rel="stylesheet" href="/bower_components/admin-lte/plugins/iCheck/square/grey.css">
<script src="/bower_components/admin-lte/plugins/iCheck/icheck.min.js"></script>


<!-- json-formatter -->
<link rel="stylesheet" href="/js/json-formatter/jquery.json-viewer.css">
<script src="/js/json-formatter/jquery.json-viewer.js"></script>

<script>

    var transaction_id_aux = null;

    //Cascading dropdown list de redes / sucursales
    $('.select2').select2();
    var servicioSeleccionado = '{{ $service_request_id }}';

    $('.mostrar').hide();

    $('.status').on('click', function(e) {
        //Setea de cero elementos html del modal
        e.preventDefault();
        var row = $(this).parents('tr');
        var status_description = row.data('status');
        var estado = row.data('estado');
        var id = row.data('id');
        var transaction_id = row.data('transaction');
        $(".idTransaccion").html(transaction_id);

        if (estado == 'devolucion') {
            status_description += '</br> <img style="max-width:550px;" src="/comprobantes_devoluciones/' + id +
                '.jpg"/>';
        }

        $.get('{{ url('reports') }}/info/reversion_data/' + id,
            function(data) {
                console.log(data);
                if (data['reversion'] == true) {
                    console.log('abriendo reversion');
                    $('.reversion').show();
                    $('#id_transaccion').html(id);
                    //$('#reversiones').show();
                } else {
                    $('.reversion').hide();
                }

                if (data['id_reversion'] == true) {
                    console.log('abriendo contenido reversion');
                    $('#reversion_description').show();
                    $('#fecha_reversion').html(data['fecha']);
                    $('#rever_user').html(data['user']);
                } else {
                    $('#reversion_description').hide();
                }
        });

        $("#status_description").html(status_description);
        $("#status_description").show();
        $("#detalles").hide();
        $("#payment_details").hide();
        $('#devoluciones').hide();
        $('#reprocesos').hide();

        //botones
        $('.devolucion').hide();
        $('.reprocesar').hide();
        $('#process_devolucion').hide();
        $('#process_inconsistencia').hide();
        $('.inconsistencia').hide();
        $('#process_reversion').hide();
        $('.reversion').show();
        $('#reversion_description').hide();
        $('#run_reprocesar').hide();

        $('#li_tab_1 > a').trigger('click');
        $("#myModal").modal();

    });

    @if ($super_user)

    function jsons_transaction(id) {

        $('#json_renderer_request_data').html('');
        $('#json_renderer_response_data').html('');

        $('#alert_error_request_data').hide();
        $('#alert_error_response_data').hide();

        var url_aux = '/reports/transactions/jsons_transaction/';

        var json = {
            _token: token,
            transaction_id: id
        };

        $.post(url_aux, json, function(data, status) {

            //console.log('data:', data);

            var jsons = data;
            var request_data = jsons.request_data;
            var response_data = jsons.response_data;

            //-----------------------------------------------------------

            request_data = request_data.replace(/\\/g, '');
            response_data = response_data.replace(/\\/g, '');

            //-----------------------------------------------------------

            if (request_data.charAt(0) == '"') {
                request_data = request_data.substring(1);
            }

            if (request_data.charAt(request_data.length - 1) == '"') {
                request_data = request_data.substring(0, request_data.length - 1)
            }

            if (response_data.charAt(0) == '"') {
                response_data = response_data.substring(1);
            }

            if (response_data.charAt(response_data.length - 1) == '"') {
                response_data = response_data.substring(0, response_data.length - 1)
            }

            //-----------------------------------------------------------

            if (request_data.indexOf('""') > -1) {
                request_data = request_data.replace(/""/g, '"');
            }

            if (response_data.indexOf('""') > -1) {
                response_data = response_data.replace(/""/g, '"');
            }

            //-----------------------------------------------------------

            if (request_data == '') {
                request_data = 'Sin JSON.';
            }

            if (response_data == '') {
                response_data = 'Sin JSON.';
            }

            //console.log('response_data', response_data);

            if (request_data == 'Sin JSON.') {
                $('#json_renderer_request_data').css('display', 'none');
                $('#alert_error_request_data').show();
            } else {
                $('#json_renderer_request_data').css('display', 'block');

                try {
                    var request_data = eval('(' + request_data + ')');
                    $('#json_renderer_request_data').jsonViewer(request_data, {rootCollapsable:false, collapsed: true});
                }
                catch (error) {
                    console.log('El json de request_data no se pudo transformar:', error);
                    $('#json_renderer_request_data').html(request_data);
                }
            }


            if (response_data == 'Sin JSON.') {
                $('#json_renderer_response_data').css('display', 'none');
                $('#alert_error_response_data').show();
            } else {
                $('#json_renderer_response_data').css('display', 'block');

                try {
                    var response_data = eval('(' + response_data + ')');
                    $('#json_renderer_response_data').jsonViewer(response_data, {rootCollapsable:false, collapsed: true});
                }
                catch (error) {
                    console.log('El json de response_data no se pudo transformar:', error);
                    $('#json_renderer_response_data').html(response_data);
                }
            }

        });
    }

    function jsons_transaction_requests(id) {

        $('#button_load_transaction_requests').html('<i class="fa fa-spin fa-refresh"></i> &nbsp; Cargando...');
        $('#button_load_transaction_requests').prop('disabled', true);
        $('#button_modal_close_x').prop('disabled', true);
        $('#button_modal_close').prop('disabled', true);

        var datatable_id = '#datatable_transaction_requests';

        var url_aux = '/reports/transactions/jsons_transaction_requests/';

        var json = {
            _token: token,
            transaction_id: id
        };

        $.post(url_aux, json, function(data, status) {

            //console.log('jsons_transaction_requests:', data.length);

            if (data.length > 0) {
                var tbody_aux = "";

                for (var i = 0; i < data.length; i++) {
                    var item = data[i];
                    var transaction_requests_id = item.transaction_requests_id;
                    var get_fields_data = item.get_fields_data;
                    var post_fields_data = item.post_fields_data;
                    var response_fields_data = item.response_fields_data;
                    var created_at = item.created_at;
                    var updated_at = item.updated_at;

                    tbody_aux += '<tr>';

                    //tbody_aux += '<b>Creación:</b> ' + created_at + ' <br/> <b>Actualización:</b> ' + updated_at + ' <br/><br/>';

                    tbody_aux += '<td> <pre id="json_renderer_get_fields_data_' + transaction_requests_id + '" style="background: white; width: 15vw !important;">' + get_fields_data + '</pre> </td>';

                    tbody_aux += '<td> <pre id="json_renderer_post_fields_data_' + transaction_requests_id + '" style="background: white; width: 35 !important;">' + post_fields_data + '</pre> </td>';

                    tbody_aux += '<td> <pre id="json_renderer_response_fields_data_' + transaction_requests_id + '" style="background: white; width: 35vw !important;">' + response_fields_data + '</pre> </td>';

                    tbody_aux += '</tr>';

                }

                $(datatable_id + ' tbody').html('');

                if (tbody_aux !== "") {
                    $(datatable_id + ' tbody').html(tbody_aux);
                }

                for (var i = 0; i < data.length; i++) {
                    var item = data[i];
                    var transaction_requests_id = item.transaction_requests_id;
                    var get_fields_data = item.get_fields_data;
                    var post_fields_data = item.post_fields_data;
                    var response_fields_data = item.response_fields_data;
                    var created_at = item.created_at;

                    //-----------------------------------------------------------

                    get_fields_data = get_fields_data.replace(/\\/g, '');
                    post_fields_data = post_fields_data.replace(/\\/g, '');
                    response_fields_data = response_fields_data.replace(/\\/g, '');

                    //-----------------------------------------------------------

                    if (get_fields_data.charAt(0) == '"') {
                        get_fields_data = get_fields_data.substring(1);
                    }

                    if (get_fields_data.charAt(get_fields_data.length - 1) == '"') {
                        get_fields_data = get_fields_data.substring(0, get_fields_data.length - 1)
                    }

                    if (post_fields_data.charAt(0) == '"') {
                        post_fields_data = post_fields_data.substring(1);
                    }

                    if (post_fields_data.charAt(post_fields_data.length - 1) == '"') {
                        post_fields_data = post_fields_data.substring(0, post_fields_data.length - 1)
                    }

                    if (response_fields_data.charAt(0) == '"') {
                        response_fields_data = response_fields_data.substring(1);
                    }

                    if (response_fields_data.charAt(response_fields_data.length - 1) == '"') {
                        response_fields_data = response_fields_data.substring(0, response_fields_data.length - 1)
                    }

                    //-----------------------------------------------------------

                    if (get_fields_data == '') {
                        get_fields_data = 'Sin JSON.';
                    }

                    if (post_fields_data == '') {
                        post_fields_data = 'Sin JSON.';
                    }

                    if (response_fields_data == '') {
                        response_fields_data = 'Sin JSON.';
                    }

                    try {
                        var get_fields_data = eval('(' + get_fields_data + ')');
                        $('#json_renderer_get_fields_data_' + transaction_requests_id).jsonViewer(get_fields_data, {rootCollapsable:false, collapsed: true});
                    } catch (error) {
                        $('#json_renderer_get_fields_data_' + transaction_requests_id).html(get_fields_data);
                    }

                    try {
                        var post_fields_data = eval('(' + post_fields_data + ')');
                        $('#json_renderer_post_fields_data_' + transaction_requests_id).jsonViewer(post_fields_data, {rootCollapsable:false, collapsed: true});
                    } catch (error) {
                        $('#json_renderer_post_fields_data_' + transaction_requests_id).html(post_fields_data);
                    }

                    try {
                        var response_fields_data = eval('(' + response_fields_data + ')');
                        $('#json_renderer_response_fields_data_' + transaction_requests_id).jsonViewer(response_fields_data, {rootCollapsable:false, collapsed: true});
                    } catch (error) {
                        $('#json_renderer_response_fields_data_' + transaction_requests_id).html(response_fields_data);
                    }
                }

                $('#div_datatable_transaction_requests').css('display', 'block');
            } else {
                $('#alert_error_transaction_requests').show();
            }

            $('#button_load_transaction_requests').html('<i class="fa fa-list"></i> &nbsp; Ver Requests de Transacción');
            $('#button_load_transaction_requests').css('display', 'none');

            $('#button_load_transaction_requests').prop('disabled', false);
            $('#button_modal_close_x').prop('disabled', false);
            $('#button_modal_close').prop('disabled', false);

        }).error(function(error) {
            swal({
                    title: 'Error',
                    text: 'Error al querer obtener las Solicitudes y Respuestas',
                    type: 'error',
                    showCancelButton: false,
                    confirmButtonColor: '#3c8dbc',
                    confirmButtonText: 'Aceptar',
                    cancelButtonText: 'Cancelar.',
                    closeOnClickOutside: false,
                    closeOnConfirm: true,
                    closeOnCancel: true
                },
                function(isConfirm) {
                    if (isConfirm) {
                        $('#button_load_transaction_requests').html('<i class="fa fa-list"></i> &nbsp; Ver Requests de Transacción');
                        $('#button_load_transaction_requests').css('display', 'block');

                        $('#button_load_transaction_requests').prop('disabled', false);
                        $('#button_modal_close_x').prop('disabled', false);
                        $('#button_modal_close').prop('disabled', false);
                    }
                }
            );
        });
    }

    @endif


    @if ($valid_rol)

    function jsons_service(id, service_source_id, service_id) {

        $('#div_load_jsons_service').css('display', 'block');
        $('#div_datatable_jsons_service').css('display', 'none');

        var datatable_id = '#datatable_jsons_service';

        $(datatable_id + ' tbody').html('');

        var url_aux = '/reports/transactions/jsons_service/';

        var json = {
            _token: token,
            transaction_id: id,
            service_source_id: service_source_id,
            service_id: service_id
        };

        $.post(url_aux, json, function(data, status) {

            if (data.length > 0) {
                var tbody_aux = "";

                for (var i = 0; i < data.length; i++) {
                    var item = data[i];

                    var id_aux = item.id;
                    var response_status_code = item.response_status_code;
                    var transaction_type = item.transaction_type;

                    var post_fields_data = item.post_fields_data;
                    var response_fields_data = item.response_fields_data;
                    var created_at = item.created_at;

                    tbody_aux += '<tr>';
                    tbody_aux += '<td style="width: 200px"> <b>Creación:</b> ' + created_at + ' <br/> <b>Código de Estado:</b> ' + response_status_code + ' <br/> <b>Tipo de Transacción:</b> ' + transaction_type + ' <br/>';
                    tbody_aux += '<td> <pre id="json_renderer_post_fields_data_service_' + id_aux + '" style="background: white; max-width: 400px">' + post_fields_data + '</pre> </td>';
                    tbody_aux += '<td> <pre id="json_renderer_response_fields_data_service_' + id_aux + '" style="background: white; max-width: 400px">' + response_fields_data + '</pre> </td>';
                    tbody_aux += '</tr>';
                    
                }

                if (tbody_aux !== "") {
                    $(datatable_id + ' tbody').html(tbody_aux);
                }

                for (var i = 0; i < data.length; i++) {
                    var item = data[i];

                    var id_aux = item.id;
                    var post_fields_data = item.post_fields_data;
                    var response_fields_data = item.response_fields_data;

                    if (post_fields_data == '') {
                        post_fields_data = 'Sin JSON.';
                    }

                    if (response_fields_data == '') {
                        response_fields_data = 'Sin JSON.';
                    }

                    try {
                        var post_fields_data = eval('(' + post_fields_data + ')');
                        $('#json_renderer_post_fields_data_service_' + id_aux).jsonViewer(post_fields_data, {rootCollapsable:false, collapsed: true});
                    }
                    catch (error) {
                        $('#json_renderer_post_fields_data_service_' + id_aux).html(post_fields_data);
                    }

                    try {
                        var response_fields_data = eval('(' + response_fields_data + ')');
                        $('#json_renderer_response_fields_data_service_' + id_aux).jsonViewer(response_fields_data, {rootCollapsable:false, collapsed: true});
                    }
                    catch (error) {
                        $('#json_renderer_response_fields_data_service_' + id_aux).html(response_fields_data);
                    }
                }

                $('#div_datatable_jsons_service').css('display', 'block');
            } else {
                $('#alert_error_jsons_service').show();
            }

            $('#div_load_jsons_service').css('display', 'none');

        });
    }

    function transaction_ticket(id) {

        //AGREGAR EL REEMPLAZO DE PUNTOS PARA LOS OTROS SERVICIOS QUE TIENEN CÓDIGOS DE BARRAS.

        $('#button_load_transaction_ticket').html('<i class="fa fa-spin fa-refresh"></i> &nbsp; Cargando...');
        $('#button_load_transaction_ticket').prop('disabled', true);
        $('#button_modal_close_x').prop('disabled', true);
        $('#button_modal_close').prop('disabled', true);

        $("#div_transaction_ticket").animate( { scrollTop : 0 }, 800 );

        var url_aux = '/reports/transactions/transaction_ticket/';

        var json = {
            _token: token,
            transaction_id: id
        };

        $.post(url_aux, json, function(data, status) {

            //console.log('transaction_ticket:', data.length);

            if (data !== "" && !~data.indexOf("Ticket no encontrado ...") && !~data.indexOf('error')) {

                data = data.replace('css/estilos.css', '/css/transaction/ticket/estilos.css');
                
                $('#contenedor_aux').html('');

                $('#contenedor_aux').html(data);

                var html_contenedor = $('#contenedor').html();

                $('#contenedor_aux').html(html_contenedor);

                $('#contenedor_aux').css({
                    'width': 'auto',
                    'height': 'auto',
                    'display': 'inline-block',
                    'border': '1px solid #d2d6de',
                    'border-radius': '10px',
                    'background': 'floralwhite',
                    'padding': '10px',
                    'margin': '20px',
                    //'margin-bottom': '20px',
                    'pointer-events': 'none',
                    'cursor': 'none',

                    '-webkit-user-select': 'none',
                    '-ms-user-select': 'none',
                    'user-select': 'none'
                });

                $('#contenedor_aux').prepend('<b id="b_prepend">Es una copia</b> <br/>');

                //$('#div_transaction_ticket').append('<div class="centrado"> <h1> <i class="fa fa-copy"></i> <br/> Es una copia <br/> Es una copia <br/> Es una copia </h1> </div>');

                $('#contenedor_aux').append('<b id="b_prepend">Es una copia</b> <br/>');

                //$('#view_transaction_ticket').append('<div class="centrado2"> <h1> <i class="fa fa-copy"></i> <br/> Es una copia <br/> Es una copia <br/> Es una copia </h1> </div> <b>Es una copia</b>');

                //$('#view_transaction_ticket').append('<b>Es una copia</b> <br/>');

                /*$('.centrado').css({
                    'text-align': 'center',
                    'font-size': '4em',
                    'color': '#dd4b39',
                    'opacity': '.7',
                    'position': 'absolute',
                    'top': '35%',
                    'left': '33%',
                    'transform': 'translate(-50%, -50%)',
                    '-webkit-transform': 'rotate(-40deg)',
                    '-o-transform': 'rotate(-40deg)',
                    'transform': 'rotate(-40deg)'
                });*/

                $('#div_transaction_ticket').css('display', 'block');
            } else {
                $('#alert_error_transaction_ticket').show();
            }

            $('#button_load_transaction_ticket').html('<i class="fa fa-ticket"></i> &nbsp; Ver Ticket de Transacción');
            $('#button_load_transaction_ticket').css('display', 'none');

            $('#button_load_transaction_ticket').prop('disabled', false);
            $('#button_modal_close_x').prop('disabled', false);
            $('#button_modal_close').prop('disabled', false);

        });
    }
    
    @endif


    $('.info').on('click', function(e) {
        e.preventDefault();
        var row = $(this).parents('tr');
        var id = row.data('id');
        var transaction_id = row.data('transaction');
        var service_source_id = row.data('service_source_id');
        var service_id = row.data('service_id');
        var atm_transaction_id = row.data('atm_transaction_id');

        transaction_id_aux = id;

        $('#li_tab_1 > a').trigger('click');

        $.get('{{ url('reports') }}/info/details/' + id,
            function(data) {

                $(".idTransaccion").html(transaction_id);
                $("#status_description").hide();
                $("#payment_details").hide();
                $('#devoluciones').hide();
                $('#reprocesos').hide();

                //botones
                $('.devolucion').hide();
                $('.reprocesar').hide();
                $('#process_devolucion').hide();
                $('.inconsistencia').hide();
                $('#process_inconsistencia').hide();
                $('.reversion').hide();
                $('#reversion_description').hide();
                $('#process_reversion').hide();
                $('#run_reprocesar').hide();

                $("#detalles").show();

                $("#modal-contenido").html(data);

                $("#myModal").modal();

                if (data == '') {
                    $("#modal-contenido").html('<tr> <td colspan="5"> <b style="color: red;">La consulta no retornó registros. </b> </td> </tr>');
                } else {
                    $("#modal-contenido").html(data);
                }

            });

        @if ($super_user)

            $('#li_tab_request_data > a').trigger('click');

            $('#datatable_transaction_requests tbody').html('');

            $('#alert_error_request_data').hide();
            $('#alert_error_response_data').hide();
            $('#alert_error_transaction_requests').hide();

            //----------------------------------------------------------------------------------------------------------------
            // Ver todos los JSON que tiene la transacción
            //----------------------------------------------------------------------------------------------------------------

            jsons_transaction(id);

            //----------------------------------------------------------------------------------------------------------------
            // Ver todos los JSON que tiene la transacción en transaction_requests
            //----------------------------------------------------------------------------------------------------------------

            //jsons_transaction_requests(id);

            $('#button_load_transaction_requests').html('<i class="fa fa-list"></i> &nbsp; Ver Requests de Transacción');
            $('#button_load_transaction_requests').css('display', 'block');
            $('#div_datatable_transaction_requests').css('display', 'none');

        @endif

        @if ($valid_rol)

            //$('#li_tab_json_service > a').trigger('click');

            //$('#view_transaction_ticket').html('');

            $('#alert_error_jsons_service').hide();
            $('#alert_error_transaction_ticket').hide();

            // -----------------------------------------------------------------------------------
            // JSON de Carga Saldo MAS
            // -----------------------------------------------------------------------------------

            jsons_service(id, service_source_id, service_id);

            // -----------------------------------------------------------------------------------
            // Obtener el ticket de transacción
            // -----------------------------------------------------------------------------------

            //transaction_ticket(id);

            $('#button_load_transaction_ticket').html('<i class="fa fa-ticket"></i> &nbsp; Ver Ticket de Transacción');
            $('#button_load_transaction_ticket').css('display', 'block');
            $('#div_transaction_ticket').css('display', 'none');
        @endif

    });

    @if ($super_user)

    $('#button_load_transaction_requests').on('click', function(e) {
        e.preventDefault();

        if (transaction_id_aux !== null) {
            jsons_transaction_requests(transaction_id_aux);
        }
    }); 

    @endif

    @if ($valid_rol)

    $('#button_load_transaction_ticket').on('click', function(e) {
        e.preventDefault();

        if (transaction_id_aux !== null) {
            transaction_ticket(transaction_id_aux);
        }
    }); 

    $('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
        e.preventDefault();

        var target = $(e.target).attr("href");
        if (target == '#tab_1' || target == '#tab_2') {
            $('#modal_dialog').css({
                'width': '700px'
            });
        } else if (target == '#tab_3') {
            $('#modal_dialog').css({
                'width': '99%'
            });
        }

    });

    @endif




    $('.pay-info').on('click', function(e) {
        e.preventDefault();
        var row = $(this).parents('tr');
        var payid = row.data('payid');
        var transaction_id = row.data('transaction');
        var transaction = row.data('id');
        $.get('{{ url('reports') }}/info/payments_data/' + payid,
            function(data) {
                $(".idTransaccion").html(transaction_id);
                $('#txttransaction_id').val(transaction);
                $("#status_description").hide();
                $("#detalles").hide();
                $("#modal-contenido-payments").html(data['payment_info']);
                $("#payment_details").show();
                $('#devoluciones').hide();
                $('#reprocesos').hide();
                console.log(data);
                //botones
                if (data['reprocesable'] == true) {
                    $('.reprocesar').show();
                } else {
                    $('.reprocesar').hide();
                }

                if (data['devolucion'] == true) {
                    $('.devolucion').show();
                } else {
                    $('.devolucion').hide();
                }
                //console.log(data['inconsistencia']);
                if (data['inconsistencia'] == true) {
                    console.log('abriendo inconsistencia');
                    $('.inconsistencia').show();
                } else {
                    $('.inconsistencia').hide();
                }

                $('.reversion').hide();
                $('#reversion_description').hide();
                $('#process_inconsistencia').hide();
                $('#process_reversion').hide();
                $('#run_reprocesar').hide();

                $('#li_tab_1 > a').trigger('click');
                $("#myModal").modal();
            });
    });

    $('.print').on('click', function(e) {
        e.preventDefault();
        var row = $(this).parents('tr');
        var id = row.data('id');
        $("#printSection").html('');
        $.get('{{ url('reports') }}/info/tickets/' + id,
            function(data) {
                $("#printSection").html(data);
                if (data) {
                    window.print();
                    $("#printSection").html('');
                    $tag = '.' + id;
                    $($tag).html(id);
                }
            });
    });

    $('.devolucion').on('click', function(e) {
        e.preventDefault();
        $('#detalles').hide();
        $('#payment_details').hide();
        $('#reprocesos').hide();
        $('#devoluciones').show();
        $('#devolucion-form')[0].reset();
        //botones
        $('.devolucion').hide();
        $('.reprocesar').hide();
        $('.inconsistencia').hide();
        $('.reversion').hide();
        $('#reversion_description').hide();
        $('#run_reprocesar').hide();
        $('#process_inconsistencia').hide();
        $('#process_reversion').hide();
        $('#process_devolucion').show();
    });

    $('#process_devolucion').on('click', function(e) {
        e.preventDefault();
        $('#keys_spinn').show();
        $('#devolucion-form').hide();
        $('#message_box').html('');
        let form = $('#devolucion-form')[0];
        let data = new FormData(form);
        let transaction_id = $('#txttransaction_id').val();
        console.log(transaction_id);
        data.append("_token", token);
        data.append("_transaction_id", transaction_id);
        $('#process_devolucion').hide();
        $('.inconsistencia').hide();
        $('#process_inconsistencia').hide();
        $('.reversion').hide();
        $('#reversion_description').hide();
        $('#process_reversion').hide();
        $.ajax({
            type: "POST",
            enctype: 'multipart/form-data',
            url: "procesar_devolucion",
            data: data,
            processData: false,
            contentType: false,
            cache: false,
            timeout: 600000,
            success: function(data) {
                console.log("SUCCESS : ", data);
                $('#message_box').html(data);
                $('#message_box').show();
                $('#keys_spinn').hide();
                $('#process_devolucion').hide();
                $('.inconsistencia').hide();
                $('.reversion').hide();
                $('#reversion_description').hide();
            },
            error: function(e) {
                $('#message_box').html(
                    'Lo sentimos, se produjo un error al procesar la devolucion');
                $('#message_box').show();
                $('#keys_spinn').hide();
                $('#process_devolucion').hide();
                $('.inconsistencia').hide();
                $('.reversion').hide();
                $('#reversion_description').hide();
                //console.log("ERROR : ", e);
            }
        });

        setTimeout(function() {
            $('#myModal').modal('hide')
            location.reload();
        }, 5000);
    });


    //Reprocesar
    $('.reprocesar').on('click', function(e) {
        e.preventDefault();
        let transaction_id = $('#txttransaction_id').val();
        let transaction_amount = $('#txttransaction_amount').val();
        let ref1 = ''; //$('#txtref1').val();
        let ref2 = ''; //$('#txtref2').val();
        let service_desc = ''; //$('#txtreServDescription').val();

        $('#transaction_amount').html(transaction_amount);
        $('#transaction_referece').html(ref1);
        $('#service_description').html(service_desc);

        $('.reprocesar_transaction_id').html(transaction_id);
        $('#detalles').hide();
        $('#payment_details').hide();
        $('#devoluciones').hide();
        $('#reprocesos').show();

        //botones
        $('.devolucion').hide();
        $('.reprocesar').hide();
        $('#process_devolucion').hide();
        $('.inconsistencia').hide();
        $('#process_inconsistencia').hide();
        $('.reversion').hide();
        $('#reversion_description').hide();
        $('#process_reversion').hide();
        $('#run_reprocesar').show();
    });

    $('#run_reprocesar').on('click', function(e) {
        e.preventDefault();
        $('#keys_spinn_2').show();
        $('#devolucion-form').hide();
        $('#message_box_2').html('');
        let transaction_id = $('#txttransaction_id').val();
        $.post("reprocesar_transaccion", {
            _token: token,
            _transaction_id: transaction_id
        }, function(data) {
            if (data.error == false) {
                $('#message_box_2').html('La transacción será reprocesada en apróx. 5 min');
                $('#message_box_2').show();
                $('#keys_spinn_2').hide();
                $('#reprocesar-info').hide();
                $('#run_reprocesar').hide();
            } else {
                $('#message_box').html('No se pudo realizar el reproceso');
                $('#message_box').show();
                $('#keys_spinn').hide();
                $('#reprocesar-info').hide();
                $('#run_reprocesar').hide();
            }
        }).error(function() {
            $('#message_box').html('No se pudo realizar el reproceso');
            $('#message_box').show();
            $('#keys_spinn').hide();
            $('#reprocesar-info').hide();
            $('#run_reprocesar').hide();
        });

        setTimeout(function() {
            $('#myModal').modal('hide')
            location.reload();
        }, 5000);
    });

    //Inconsistencia
    $('.inconsistencia').on('click', function(e) {
        e.preventDefault();
        let transaction_id = $('#txttransaction_id').val();
        $('#id_transaccion').html(transaction_id);
        $('.inconsistencia_transaction_id').html(transaction_id);
        $('#detalles').hide();
        $('#payment_details').hide();
        $('#devoluciones').hide();
        $('#inconsistencias').show();

        //botones
        $('.devolucion').hide();
        $('.reprocesar').hide();
        $('#process_devolucion').hide();
        $('.inconsistencia').hide();
        $('#process_inconsistencia').show();
    });

    $('#process_inconsistencia').on('click', function(e) {
        e.preventDefault();
        $('#keys_spinn_2').show();
        $('#devolucion-form').hide();
        $('#message_box_2').html('');
        let transaction_id = $('#txttransaction_id').val();
        $.post("inconsistencia", {
            _token: token,
            _transaction_id: transaction_id
        }, function(data) {
            if (data.error == false) {
                $('#message_box_2').html('La inconsistencia se generara en apróx. 5 min');
                $('#message_box_2').show();
                $('#keys_spinn_2').hide();
                $('#reprocesar-info').hide();
                $('#process_inconsistencia').hide();
            } else {
                $('#message_box').html('No se pudo realizar el reproceso');
                $('#message_box').show();
                $('#keys_spinn').hide();
                $('#reprocesar-info').hide();
                $('#process_inconsistencia').hide();
            }
        }).error(function() {
            $('#message_box').html('No se pudo realizar el reproceso');
            $('#message_box').show();
            $('#keys_spinn').hide();
            $('#reprocesar-info').hide();
            $('#process_inconsistencia').hide();
        });

        setTimeout(function() {
            $('#myModal').modal('hide')
            location.reload();
        }, 5000);
    });

    //Reversiones
    $('.reversion').on('click', function(e) {
        e.preventDefault();
        let transaction_id = $('#id_transaccion').text();
        console.log(transaction_id);
        $('#detalles').hide();
        $('#payment_details').hide();
        $('#devoluciones').hide();
        $('#reversiones').show();

        //botones
        $('.devolucion').hide();
        $('.reprocesar').hide();
        $('#process_devolucion').hide();
        $('.reversion').hide();
        $('#reversion_description').hide();
        $('#process_reversion').show();
    });

    $('#process_reversion').on('click', function(e) {
        e.preventDefault();
        $('#keys_spinn_2').show();
        $('#devolucion-form').hide();
        $('#message_box_2').html('');

        let transaction_id = $('#id_transaccion').text();
        console.log(transaction_id);
        $.post("reversion", {
            _token: token,
            _transaction_id: transaction_id
        }, function(data) {
            if (data.error == false) {
                $('#message_box_2').html('La Reversion se generara en apróx. 10 min');
                $('#message_box_2').show();
                $('#keys_spinn_2').hide();
                $('#reprocesar-info').hide();
                $('#process_reversion').hide();
                $('#reversion_description').hide();
            } else {
                $('#message_box').html('No se pudo realizar el reproceso');
                $('#message_box').show();
                $('#keys_spinn').hide();
                $('#reprocesar-info').hide();
                $('#process_reversion').hide();
                $('#reversion_description').hide();
            }
        }).error(function() {
            $('#message_box').html('No se pudo realizar el reproceso');
            $('#message_box').show();
            $('#keys_spinn').hide();
            $('#reprocesar-info').hide();
            $('#process_reversion').hide();
        });

        setTimeout(function() {
            $('#myModal').modal('hide')
            location.reload();
        }, 5000);
    });

    $('#group_id').on('change', function(e) {
        var group_id = e.target.value;
        $.get('{{ url('reports') }}/ddl/owners/' + group_id,
            function(owners) {
                $('#owner_id').empty();
                $.each(owners, function(i, item) {
                    $('#owner_id').append($('<option>', {
                        value: i,
                        text: item
                    }));
                });
            });

        $.get('{{ url('reports') }}/ddl/branches/' + group_id,
            function(branches) {
                $('#branch_id').empty();
                $.each(branches, function(i, item) {
                    $('#branch_id').append($('<option>', {
                        value: i,
                        text: item
                    }));
                });
            });
    });

    $('#owner_id').on('change', function(e) {
        var group_id = $("#group_id").val();
        var owner_id = e.target.value;
        $.get('{{ url('reports') }}/ddl/branches/' + group_id + '/' + owner_id,
            function(branches) {
                $('#branch_id').empty();
                $.each(branches, function(i, item) {
                    $('#branch_id').append($('<option>', {
                        value: i,
                        text: item
                    }));
                });
            });
    });

    /*$('#branch_id').on('change', function(e) {
        var branch_id = e.target.value;
        console.log(branch_id)
        $.get('{{ url('reports') }}/ddl/pdv/' + branch_id, function(data) {
            $('#pos_id').empty();
            $.each(data, function(i, item) {
                $('#pos_id').append($('<option>', {
                    value: i,
                    text: item
                }));
            });
        });
    });*/


    //Datemask dd/mm/yyyy
    $("#datemask").inputmask("dd/mm/yyyy", {
        "placeholder": "dd/mm/yyyy"
    });
    //Datemask2 mm/dd/yyyy
    $("#datemask2").inputmask("mm/dd/yyyy", {
        "placeholder": "mm/dd/yyyy"
    });
    //reservation date preset
    $('#reservationtime').val()



    if ($('#reservationtime').val() == '' || $('#reservationtime').val() == 0) {
        var date = new Date();
        var init = new Date(date.getFullYear(), date.getMonth(), date.getDate());
        var end = new Date(date.getFullYear(), date.getMonth(), date.getDate());

        var initWithSlashes = (init.getDate()) + '/' + (init.getMonth() + 1) + '/' + init.getFullYear() + ' 00:00:00';
        var endDayWithSlashes = (end.getDate()) + '/' + (end.getMonth() + 1) + '/' + end.getFullYear() + ' 23:59:59';

        $('#reservationtime').val(initWithSlashes + ' - ' + endDayWithSlashes);
    }
    //Date range picker
    $('#reservation').daterangepicker();

    /*$('#reservationtime').daterangepicker({
        ranges: {
            'Hoy': [moment(), moment()],
            'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            'Ultimos 7 Dias': [moment().subtract(6, 'days'), moment()],
            'Ultimos 30 Dias': [moment().subtract(29, 'days'), moment()],
            'Mes': [moment().startOf('month'), moment().endOf('month')],
            'Mes Pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf(
                'month')]
        },
        locale: {
            applyLabel: 'Aplicar',
            fromLabel: 'Desde',
            toLabel: 'Hasta',
            customRangeLabel: 'Rango Personalizado',
            daysOfWeek: ['Dom', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab'],
            monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Setiembre',
                'Octubre', 'Noviembre', 'Diciembre'
            ],
            firstDay: 1
        },

        format: 'DD/MM/YYYY HH:mm:ss',
        startDate: moment().startOf('month'),
        endDate: moment().endOf('month'),
    });*/

    $('#reservationtime').daterangepicker({
        'timePicker': true,
        'timePicker24Hour': true,
        'timePickerIncrement': 1,
        'format': 'DD/MM/YYYY HH:mm:ss',
        'startDate': moment().startOf('month'),
        'endDate': moment().endOf('month'),
        'opens': 'left',
        'drops': 'down',
        'ranges': {
            'Hoy': [moment().startOf('day').toDate(), moment().endOf('day').toDate()],
            'Ayer': [moment().startOf('day').subtract(1, 'days'), moment().endOf('day').subtract(1, 'days')],
            'Antes de ayer': [moment().startOf('day').subtract(2, 'days'), moment().endOf('day').subtract(2, 'days')],
            'Semana': [moment().startOf('week'), moment().endOf('week')],
            'Semana pasada': [moment().startOf('week').subtract(1, 'week'), moment().endOf('week').subtract(1, 'week')],
            'Semana ante pasada': [moment().startOf('week').subtract(2, 'week'), moment().endOf('week').subtract(2, 'week')],
            'Mes': [moment().startOf('month'), moment().endOf('month')],
            'Mes pasado': [moment().startOf('month').subtract(1, 'month'), moment().endOf('month').subtract(1, 'month')],
            'Mes ante pasado': [moment().startOf('month').subtract(2, 'month'), moment().endOf('month').subtract(2, 'month')],
            'Año': [moment().startOf('year'), moment().endOf('year')],
            'Año pasado': [moment().startOf('year').subtract(1, 'year'), moment().endOf('year').subtract(1, 'year')]
        },
        'locale': {
            'applyLabel': 'Aplicar',
            'fromLabel': 'Desde',
            'toLabel': 'Hasta',
            'customRangeLabel': 'Rango Personalizado',
            'daysOfWeek': ['Dom', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sáb'],
            'monthNames': ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto',
                'Setiembre', 'Octubre', 'Noviembre', 'Diciembre'
            ],
            'firstDay': 1
        }
    });


    $('#reservationtime').attr({
        'onkeydown': 'return false'
    });

    var fechaIncio = $('#reservationtime').val().substr(0, 10);
    var fechaFin = $('#reservationtime').val().substr(22, 10);
    var fecha1 = moment(fechaIncio, "MM-DD-YYYY");
    var fecha2 = moment(fechaFin, "MM-DD-YYYY");
    const diferencia = fecha2.diff(fecha1, 'days');
    var rsultadoDif = Math.round(diferencia / (24));
    console.log(rsultadoDif);



    $(document).on('change', '#serviceId', function() {
        var valor = this.value;
        var urlGetServices = "{{ route('reports.get_service_request') }}";

        if (valor.search('-') != -1) {
            $.get(urlGetServices, {
                id: valor
            }).done(function(data) {
                $('.mostrar').show();
                $('#servicioRequestId').empty().trigger('change');
                $('#servicioRequestId').select2({
                    data: data
                });
                if (servicioSeleccionado != '') {
                    $('#servicioRequestId').val(servicioSeleccionado).trigger('change');
                }
            });
        } else {
            $('#servicioRequestId').select2('data', null);
            $('.mostrar').hide();
        }
    });

    $('#serviceId').trigger('change');

    var text = $('.alert.alert-success.alert-dismissable').text();

    if (text !== '') {
        if (text.includes('link')) {

            text = text.replace('×', '');
            text = text.replace('Operación Exitosa', '');
            text = text.trim();

            swal({
                    title: 'Atención',
                    text: text,
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#0073b7',
                    confirmButtonText: 'Aceptar',
                    cancelButtonText: 'Cancelar',
                    closeOnClickOutside: false,
                    showLoaderOnConfirm: false
                },
                function(isConfirmMessage) {
                    if (isConfirmMessage) {}
                }
            );
        }
    }



    $('input[type="checkbox"]').iCheck({
        checkboxClass: 'icheckbox_square-grey',
        radioClass: 'iradio_square-grey'
    });

    var pos_active = "{{ $pos_active }}";

    if (pos_active == 'on') {
        $('#pos_active').iCheck('check');
    }

    function get_points_of_sale() {
        var pos_active = 'on';
        var owner_id = $('#owner_id').val();
        var branch_id = $('#branch_id').val();

        if ($('#pos_active').is(":checked") == false) {
            pos_active = '';
        }

        var url = '/reports/transactions/get_points_of_sale/';

        var json = {
            _token: token,
            owner_id: owner_id,
            branch_id: branch_id,
            pos_active: pos_active
        };

        $.post(url, json, function(data, status) {

            console.log('data:', data);

            $('#pos_id').val(null).trigger('change');
            $('#pos_id').empty().trigger("change");

            /*$.each(data, function(k, v) {
                console.log(k, v);
                var option = new Option(v, k, false, false);
                $('#pos_id').append(option);
            });*/

            for (var prop in data) {
                var option = new Option(data[prop], prop, false, false);
                $('#pos_id').append(option);
            }

            $('#pos_id').val('0').trigger('change');
        });
    }

    $("#pos_active").on('ifChanged', function(e) {
        get_points_of_sale();
    });


    $('.select2').on('select2:select', function(e) {

        var id = e.currentTarget.id;

        switch (id) {
            case 'branch_id':
                get_points_of_sale();
                break;
        }
    });

    var table = $('#datatable_1').DataTable({
        orderCellsTop: true,
        fixedHeader: true,
        pageLength: 20,
        lengthMenu: [
            1, 2, 5, 10, 20, 30, 50, 70, 100, 150, 300, 500, 1000, 1500, 2000, 5000, 10000
        ],
        dom: '',
        language: {
            "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
        },
        scroller: true,
        processing: true,
        order: [
            [0, 'desc']
        ],
        displayLength: 20
    });

</script>
@endsection
@section('aditional_css')
<link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
<style type="text/css">
    @media print {
        body * {
            visibility: hidden;

        }

        #printSection,
        #printSection * {
            visibility: visible;
        }



        #printSection {
            font-size: 11px;
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            left: 0;
            top: 0;
        }
</style>
@endsection