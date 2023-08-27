@extends('layout')

@section('title')
Transacciones - Reporte
@endsection
@section('content')

<?php
//Variable que se usa en todo el documento 

$transactions = $data['lists']['transactions'];
$json = $data['lists']['json'];

//Combos
$transaction_status = $data['lists']['transaction_status'];
$services_providers_sources = $data['lists']['services_providers_sources'];

//Valor de campos
$created_at = $data['inputs']['created_at'];
$transaction_id = $data['inputs']['transaction_id'];
$amount = $data['inputs']['amount'];
$transaction_status_id = $data['inputs']['transaction_status_id'];
$service_source_id = $data['inputs']['service_source_id'];
$service_id = $data['inputs']['service_id'];
?>

<section class="content-header">


    <div class="row">
        <div class="col-md-12">
            @include('partials._flashes')
        </div>
    </div>

    <div class="row">
        <div class="col-md-4"></div>

        <div class="col-md-4">
            <div class="box box-default" style="border-radius: 5px; margin-top: 50px" id="div_load">
                <!--<div class="box-header with-border">
                    <h3 class="box-title" style="font-size: 25px;">Cargando...
                    </h3>
                </div>-->

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

    <div class="box box-default" style="border-radius: 5px;" id="content" style="display: none">
        <div class="box-header with-border">
            <h3 class="box-title" style="font-size: 25px;">Transacciones - Reporte
            </h3>
            <div class="box-tools pull-right">
                <button class="btn btn-info" type="button" title="Buscar según los filtros en los registros." style="margin-right: 5px" id="search" name="search" onclick="search('search')">
                    <span class="fa fa-search"></span> Buscar
                </button>

                <!--<button class="btn btn-success" type="button" title="Convertir tabla en archivo excel." id="generate_x" name="generate_x" onclick="search('generate_x')">
                    <span class="fa fa-file-excel-o"></span> Exportar
                </button>-->
            </div>
        </div>

        <div class="box-body">

            <div class="box box-default" style="border: 1px solid #d2d6de;">
                <div class="box-header with-border">
                    <h3 class="box-title">Filtrar búsqueda:</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
                <div class="box-body">
                    {!! Form::open(['route' => 'cms_transactions_index', 'method' => 'POST', 'role' => 'form', 'id' => 'form_search']) !!}
                    <div class="row">

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="created_at">Buscar por Fecha:</label>
                                <input type="text" class="form-control" style="display:block" id="created_at" name="created_at" placeholder="Seleccionar fecha."></input>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="transaction_status_id">Buscar por Estado:</label>
                            <div class="form-group">
                                <select name="transaction_status_id" id="transaction_status_id" class="select2" style="width: 100%"></select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="service_source_id">Buscar por Proveedor:</label>
                            <div class="form-group">
                                <select name="service_source_id" id="service_source_id" class="select2" style="width: 100%"></select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="service_id">Servicio por marca:</label>
                            <div class="form-group">
                                <select name="service_id" id="service_id" class="select2" style="width: 100%"></select>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="transaction_id">Buscar por Transacción:</label>
                                <input type="number" class="form-control" id="transaction_id" name="transaction_id" placeholder="ID de transacción"></input>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="amount">Buscar por Monto:</label>
                                <input type="number" class="form-control" id="amount" name="amount" placeholder="Monto de transacción"></input>
                            </div>
                        </div>


                    </div>

                    <input name="json" id="json" type="hidden">

                    {!! Form::close() !!}
                </div>
            </div>

            <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_1">
                <thead>
                    <tr>
                        <th>Opciones</th>
                        <th>ID</th>
                        <th>Estado</th>
                        <th>Servicio</th>
                        <th>Monto</th>
                        <th>Fecha - Hora</th>
                    </tr>
                </thead>
                <tbody>

                    <!--<tr role="row" class="odd">
                        <td title="Opciones disponibles para gestionar la transacción" class="sorting_1">
                            <buttom class="btn btn-default" onclick="modal_view_options({&quot;transaction_id&quot;:13983216,&quot;owner_id&quot;:16,&quot;amount&quot;:&quot;3.000&quot;,&quot;status&quot;:&quot;success&quot;,&quot;status_description&quot;:&quot;Transaccion aprobada&quot;,&quot;service_source_id&quot;:0,&quot;service_source_id_new&quot;:null,&quot;service_id&quot;:3,&quot;service_id_new&quot;:null,&quot;service&quot;:&quot;Tigo - Minicarga&quot;,&quot;service_new&quot;:null,&quot;level&quot;:1,&quot;sequence&quot;:1,&quot;inputs&quot;:null,&quot;input_amount&quot;:0,&quot;services_list&quot;:null,&quot;commission&quot;:true})">
                                <i class="fa fa-list"></i>
                            </buttom>
                        </td>
                        <td>13983216</td>
                        <td>success</td>
                        <td>Tigo - Minicarga</td>
                        <td>3.000</td>
                        <td>25/08/2022 13:54:14</td>
                    </tr>-->

                    @if (count($transactions) > 0)

                    @foreach ($transactions as $item)

                    <?php
                    /**
                     * Con estos nombres evitamos que 
                     * se mezclen con otras variables de 
                     * los campos de búsqueda.
                     */
                    $item_transaction_id = $item->transaction_id;
                    $item_service_source_id = $item->service_source_id;
                    $item_service_id = $item->service_id;
                    $item_status = $item->status;
                    $item_status_description = $item->status_description;
                    $item_service = $item->service;
                    $item_amount = $item->amount;
                    $item_created_at = $item->created_at;

                    $item_owner_id = $item->owner_id;

                    /**
                     * Solo se le pasa lo que no tiene la función
                     */
                    $parameters = [
                        'transaction_id' => $item_transaction_id,
                        'amount' => $item_amount,
                        'status' => $item_status,
                        'status_description' => $item_status_description,
                        'service_source_id' => $item_service_source_id,
                        'service_id' => $item_service_id,
                        'service' => $item_service,
                        'owner_id' => $item_owner_id
                    ];

                    $parameters = json_encode($parameters);
                    ?>

                    <tr>
                        <td title="Opciones disponibles para gestionar la transacción">
                            <buttom class="btn btn-default" onclick='modal_view_options({!! $parameters !!})'>
                                <i class="fa fa-list"></i>
                            </buttom>
                        </td>
                        <td>{{ $item_transaction_id }}</td>
                        <td>{{ $item_status }}</td>
                        <td>{{ $item_service }}</td>
                        <td>{{ $item_amount }}</td>
                        <td>{{ $item_created_at }}</td>
                    </tr>

                    @endforeach
                    @endif
                </tbody>
            </table>

        </div>
    </div>

    <!-- Modal: modal_view_options para ver todas las opciones disponibles de la transacción -->
    <div id="modal_view_options" class="modal fade" role="dialog" data-backdrop="static" data-keyboard="false" href="#">
        <div class="modal-dialog" role="document" style="background: white; border-radius: 5px; width: 80%;">
            <div class="modal-content" style="border-radius: 10px">
                <div class="modal-header">
                    <!--<button type="button" class="close" data-dismiss="modal">&times;</button>-->
                    <div class="modal-title" style="font-size: 20px; text-align: center" id="modal_view_options_title">
                        Realizar devolución
                    </div>
                </div>

                <div class="modal-body">
                    <div class="box box-default" style="border: 1px solid #d2d6de; text-align: center;">
                        <div class="box-body" id="services_options" style="background: gainsboro; padding: 20px;"></div>
                    </div>
                </div>

                <div class="modal-footer" style="text-align: center">

                    <button class="btn btn-dafault" onclick="modal_history()">
                        <span class="fa fa-table"></span> &nbsp; Ver Historial de Devoluciones
                    </button>

                    <button class="btn btn-danger" onclick="modal_view_options_close()">
                        <span class="fa fa-times"></span> &nbsp; Cerrar ventana
                    </button>

                </div>
            </div>
        </div>
    </div>

    <!-- Modal: modal_view_item para ver los servicios que estarán disponibles de hacer una devolución -->
    <div id="modal_view_item" class="modal fade" role="dialog" data-backdrop="static" data-keyboard="false" href="#">
        <div class="modal-dialog modal-dialog-centered" role="document" style="background: white; border-radius: 5px; width: 700px;">
            <div class="modal-content" style="border-radius: 10px">
                <div class="modal-header">
                    <div class="modal-title" style="font-size: 20px; text-align: center" id="modal_view_item_title"></div>
                </div>
                <div class="modal-body">
                    <div class="box box-default" style="border: 1px solid #d2d6de;">
                        <div class="box-body">
                            <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_services">
                                <thead></thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-danger" style="float:right" onclick="modal_view_item_cancel()">
                        <span class="fa fa-times"></span> &nbsp; Cerrar
                    </button> &nbsp;
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: modal_view_form para ver los campos a completar de cada servicio de nivel 1 o 2 -->
    <div id="modal_view_form" class="modal fade" role="dialog" data-backdrop="static" data-keyboard="false" href="#">
        <div class="modal-dialog modal-dialog-centered" role="document" style="background: white; border-radius: 5px; width: 500px; overflow-x: hidden;" id="modal_view_dialog">
            <div class="modal-content" style="border-radius: 10px">
                <div class="modal-header">
                    <div class="modal-title" style="font-size: 20px; text-align: center" id="modal_view_form_title">
                        Servicios
                    </div>
                </div>
                <div class="modal-body" style="overflow-x: hidden;">
                    <div class="row">

                        <div class="col-md-6" id="modal_view_form_help_and_inputs_devolution">
                            <div class="callout callout-default" style="border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px" title="Ayuda para completar el formulario">
                                <div class="row">
                                    <div class="col-md-12" id="modal_view_form_help_devolution"> Completa los campos del servicio </div>
                                </div>
                            </div>

                            <div class="box box-default" style="border: 1px solid #d2d6de; padding: 10px;" id="modal_view_form_inputs_devolution">
                                Servicios sin campos disponibles
                            </div>
                        </div>

                        <div class="col-md-6" id="modal_view_form_help_and_inputs">
                            <div class="callout callout-default" style="border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px" title="Ayuda para completar el formulario">
                                <div class="row">
                                    <div class="col-md-12" id="modal_view_form_help"> Completa los campos del servicio </div>
                                </div>
                            </div>

                            <div class="box box-default" style="border: 1px solid #d2d6de; padding: 10px;" id="modal_view_form_inputs">
                                Servicios sin campos disponibles
                            </div>
                        </div>

                        <div class="col-md-6" style="display: none" id="modal_view_form_grid">
                            <div class="callout callout-default" style="border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px">
                                <b>Campos obtenidos de la consulta</b>
                            </div>

                            <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_modal_view_form">
                                <thead id="datatable_modal_view_form_thead"></thead>
                                <tbody id="datatable_modal_view_form_tbody"></tbody>
                            </table>
                        </div>

                        <div class="col-md-6" style="display: none" id="div_check_ajustement">
                            <div class="box box-default" style="border: 1px solid #d2d6de; padding: 10px;">
                                <label>
                                    <input type="checkbox" id="check_ajustement" name="check_ajustement" title="Mostrar u ocultar campos de ajuste"></input> &nbsp; Realizar Ajuste
                                </label>

                                <div id="items_ajustement" style="display: none">

                                    <br />

                                    <div class="callout callout-default" style="border: 1px solid #f39c12; border-width: 1px 1px 1px 4px" title="Ayuda para completar el formulario - Ajuste">
                                        <div class="row">
                                            <div class="col-md-12" id="modal_view_form_help_ajustement"></div>
                                        </div>
                                    </div>

                                    <div class="box box-default" style="border: 1px solid #f39c12; padding: 10px;" id="modal_view_form_inputs_ajustement">
                                        Campos de ajuste
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">

                    <button class="btn btn-primary" style="float:right; margin-right: 5px; display: none" id="button_cms_confirm"></button>
                    <button class="btn btn-danger" style="float:right; margin-right: 5px;" onclick="modal_view_form_cancel()" id="button_cancel"></button> &nbsp;
                    <button class="btn btn-primary" style="float:right; margin-right: 5px" id="button_iterate_level"></button>

                </div>
            </div>
        </div>
    </div>

    <!-- Modal: modal_view_form para ver los campos a completar de cada servicio de nivel 1 o 2 -->
    <div id="modal_history" class="modal fade" role="dialog" data-backdrop="static" data-keyboard="false" href="#">
        <div class="modal-dialog modal-dialog-centered" role="document" style="background: white; border-radius: 5px; width: 99%;">
            <div class="modal-content" style="border-radius: 10px;">
                <div class="modal-header">
                    <!--<button type="button" class="close" data-dismiss="modal">&times;</button>-->
                    <div class="modal-title" style="font-size: 20px; text-align: center">
                        Historial de devoluciones para esta transacción
                    </div>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_history">
                                <thead></thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-danger" onclick="modal_history_close()">
                        <span class="fa fa-times"></span> &nbsp; Cerrar ventana
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para el ticket -->
    <div id="modal_ticket" class="modal fade" role="dialog" data-backdrop="static" data-keyboard="false" href="#">
        <div class="modal-dialog modal-dialog-centered" role="document" style="background: white; border-radius: 5px;">
            <div class="modal-content" style="border-radius: 10px">

                <div class="modal-header">
                    <div class="modal-title" style="font-size: 20px; text-align: center">
                        Ticket generado
                    </div>
                </div>

                <div class="modal-body" id="modal_body_ticket" style="display: flex; justify-content: center; height: 80vh"></div>

                <div class="modal-footer" style="text-align: center;">
                    <button class="btn btn-success" onclick="modal_ticket_print()">
                        <span class="fa fa-print"></span> &nbsp; Imprimir Ticket
                    </button>
                    <button class="btn btn-danger" onclick="modal_ticket_close()">
                        <span class="fa fa-times"></span> &nbsp; Cerrar ventana
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de carga.. -->
    <div id="modal_load" class="modal fade" role="dialog" data-backdrop="static" data-keyboard="false" href="#" title="Espere mientras la operación se completa...">
        <div class="modal-dialog modal-dialog-centered" role="document" style="background: white; width: 250px; border-radius: 5px;">
            <div class="modal-content" style="border-radius: 10px">
                <div class="modal-body">
                    <div style="text-align: center; margin: 10px; font-size: 20px;">
                        <div style="margin-bottom: 10px">
                            <i class="fa fa-spin fa-refresh fa-2x" style="vertical-align: sub;"></i> &nbsp;
                            <b id="modal_load_message">Cargando...</b>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="content">

</section>
@endsection

@section('page_scripts')
@include('partials._selectize')
@endsection

@section('js')
<!-- datatables -->
<link rel="stylesheet" href="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.css">
<script src="/bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.min.js"></script>

<!-- date-range-picker -->
<link href="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css" />
<script src="/bower_components/admin-lte/plugins/daterangepicker/moment.min.js"></script>
<script src="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker.js"></script>

<!-- bootstrap datepicker -->
<script src="/bower_components/admin-lte/plugins/datepicker/bootstrap-datepicker.js"></script>

<!-- select2 -->
<script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>
<link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />

<!-- iCheck -->
<link rel="stylesheet" href="/bower_components/admin-lte/plugins/iCheck/square/grey.css">
<link rel="stylesheet" href="/bower_components/admin-lte/plugins/iCheck/square/orange.css">
<script src="/bower_components/admin-lte/plugins/iCheck/icheck.min.js"></script>

<!-- jQuery Number Format -->
<script src="/js/number_format/jquery.number.js"></script>

<!-- Iniciar objetos -->
<script type="text/javascript">
    // ------------------------------------------------------------------------------------------------------------ 

    var global_parameters_transaction = null;

    // ------------------------------------------------------------------------------------------------------------ 

    function modal_ticket_print() {

        setInterval("document.location.href = 'cms_transactions_index'", 5000);

        var element_id = document.getElementById('modal_body_ticket');
        var windows_print = window.open(' ', '_parent'); //_parent
        windows_print.document.write(element_id.innerHTML);
        windows_print.document.close();
        windows_print.print();
        windows_print.close();

    }

    function modal_ticket_open(transaction_devolution_id) {

        $('#modal_load_message').html('Espere mientras se genera el ticket...');

        $("#modal_load").modal();

        //var transaction_id = 10478846;

        var url = '/cms_get_ticket/';

        var json = {
            _token: token,
            transaction_devolution_id: transaction_devolution_id,
        };

        $.post(url, json, function(data, status) {

            $("#modal_load").modal('hide');

            console.log('data:', data);

            var message = '';

            if (data !== '') {

                var div_container = data;

                $('#modal_body_ticket').html(div_container);
                $("#modal_ticket").modal();
            } else {
                message = 'Ticket no encontrado';
            }

            if (message !== '') {
                swal({
                        title: 'Error con Ticket',
                        text: message,
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
                            document.location.href = 'cms_transactions_index';
                        }
                    }
                );
            }

            global_parameters_transaction = null;

        });

    }

    // Cierra la ventana del ticket
    function modal_ticket_close() {
        $("#modal_ticket").modal('hide');
        setInterval("document.location.href = 'cms_transactions_index'", 1000);
        $("#modal_load").modal();
    }

    function modal_history() {

        var transaction_id = global_parameters_transaction.transaction_id;

        var url = '/get_history_transaction/';

        var json = {
            _token: token,
            transaction_id: transaction_id
        };

        $.post(url, json, function(data, status) {
            var error = data.error;
            var text = data.message;
            var response = data.response;
            var title = '';
            var type = '';

            if (error == true) {
                type = 'error';
                title = 'Ocurrió un error.';
            } else {
                type = 'success';
                title = 'Acción exitosa.';
            }

            var history_list = response;

            if (history_list.length > 0) {

                $("#modal_history").modal();

                var datatable_id = '#datatable_history';
                var th_keys = Object.keys(history_list[0]);

                var thead = '<tr>';

                for (var i = 0; i < th_keys.length; i++) {
                    var th = th_keys[i];
                    thead += '<th>' + th + '</th>';
                }

                thead += '</tr>';

                var tbody = '';

                for (var i = 0; i < history_list.length; i++) {
                    var row = history_list[i];

                    tbody += '<tr>';

                    for (key in row) {
                        var column = row[key];
                        tbody += '<td>' + column + '</td>';
                    }

                    tbody += '</tr>';
                }

                if ($.fn.DataTable.isDataTable(datatable_id)) {
                    $(datatable_id).DataTable().destroy();
                }

                $(datatable_id + ' thead').empty();
                $(datatable_id + ' tbody').empty();

                $(datatable_id + ' thead').append(thead);
                $(datatable_id + ' tbody').append(tbody);

                //Datatable config
                var data_table_config = {
                    //custom
                    orderCellsTop: true,
                    fixedHeader: true,
                    pageLength: 20,
                    lengthMenu: [
                        1, 2, 5, 10, 20, 30, 50, 70, 100, 150, 300, 500, 1000, 1500, 2000
                    ],
                    dom: '<"pull-left"f><"pull-right"l>tip',
                    language: {
                        "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
                    },
                    scroller: true,
                    processing: true
                }

                $(datatable_id).DataTable(data_table_config);
            } else {
                swal('Atención', 'No hay registros de devoluciones\n para esta transacción.', 'warning');
                $("#modal_history").modal('hide');
            }
        }).error(function(error) {
            swal({
                    title: 'Error al querer obtener los datos.',
                    text: 'La sesión a expirado.',
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
                        //location.reload();
                        document.location.href = 'cms_transactions_index';
                    }
                }
            );
        });
    }

    function modal_view_options(parameters) {

        var enabled = false;

        @if (\Sentinel::getUser()->hasAccess('cms_transactions_devolution_send'))
            enabled = true;
        @endif

        if (enabled) {
            global_parameters_transaction = {

            _token: token,
            transaction_id: parameters.transaction_id,
            amount: parameters.amount,
            status: parameters.status,
            status_description: parameters.status_description,

            service_source_id: parameters.service_source_id,
            service_category_id: null,
            service_id: parameters.service_id,
            service: parameters.service,

            owner_id: parameters.owner_id,

            service_category_id_new: null,
            service_source_id_new: null,
            service_id_new: null,
            service_new: null,

            service_id_selected: null,
            services_list: null,

            level: 1,
            sequence: 1, // Por defecto

            fields: null, // Campos
            fields_devolution: null, // Campos de devolución
            fields_adjust: null, // Campos de ajuste

            values: null,
            values_devolution: null,
            values_adjustement: null,

            input_amount: 0, // Monto ingresado por el usuario, o que viene en el servicio de nivel 2,

            commission: false,

            make_ajustement: false,

            image_service: null
            };

            deselect_rows();

            //transaction_id_aux = $.number(global_parameters_transaction.transaction_id, 0, ',', '.');

            $('#modal_view_options_title').html('Opciones:');
            $("#modal_view_options").modal();
        } else {
            swal('Atención', 'Este usuario no tiene permiso para realizar devoluciones.', 'info');
        }
    }

    // Cierra la ventana del historial
    function modal_history_close() {
        $("#modal_history").modal('hide');
    }

    // Cierra la ventana de las opcione disponibles
    function modal_view_options_close() {
        global_parameters_transaction = null;

        $('#modal_view_options').modal('hide');
    }

    // Cierra la ventana que tiene la lista de servicios
    function modal_view_item_cancel() {

        $('#select_service_row_' + global_parameters_transaction.service_id_selected).css({
            'background': 'white'
        });

        $('#current_service').html('');
        $('#current_amount').html('');
        $('#current_commission').html('');
        $('#new_service').html('');

        $('#modal_view_item').modal('hide');

        global_parameters_transaction.service_id_selected = null;
    }

    // Cierra la ventana que tiene la lista de servicios
    function modal_view_form_cancel() {
        deselect_rows();
        $('#modal_view_form').modal('hide');
    }
    // Selecciona una fila de servicio para mostrar sus campos
    function get_inputs(fields, form) {

        var index = 0;

        var inputs = '<div class="row">';

        var is_amount_aux = false;

        for (var i = 0; i < fields.length; i++) {
            var input = fields[i];

            var id = 'input_' + input.id;
            var id_ = input.id;
            var description = input.description;
            var type = input.type;
            var label = input.label;
            var min_length = input.min_length;
            var max_length = input.max_length;
            var value = input.value;
            var visible = input.visible;
            var required = input.required;
            var send = input.send;
            var is_amount = input.is_amount;
            var hint = input.hint;
            var placeholder = hint;

            var input_ajustement = input.ajustement;

            if (send == true) {

                var fa_class = '';
                var fa_style = '';
                var input_group_addon_style = '';
                var col_md = 'col-md-12';

                var input = '';
                var disabled = '';

                if (type == 'TEXT' || type == 'NUMBER') {
                    fa_class = 'fa fa-edit';
                } else if (type == 'SELECT') {
                    fa_class = 'fa fa-list-ul';
                } else {
                    fa_class = 'fa fa-pencil';
                }

                if (is_amount == true) {

                    is_amount_aux = is_amount;

                    fa_class = 'fa fa-money';
                    fa_style = 'color: white; font-weigh: ';
                    input_group_addon_style = 'background: #00a65a;';

                    if (form == 'fields') {
                        disabled = 'disabled';
                    }
                }

                if (input_ajustement == true) {
                    fa_style = 'color: white; font-weigh: ';
                    input_group_addon_style = 'background: #f39c12;';
                }

                if (type == 'TEXT' || type == 'NUMBER') {

                    input = '<input type="' + type + '" class="form-control" placeholder="' + placeholder + '" ';
                    input += 'id="' + id + '" name = "' + id + '" index = "' + index + '" description = "' + description + '" type = "' + type + '" ';
                    input += 'minlength="' + min_length + '" maxlength="' + max_length + '" min="' + min_length + '" max="' + max_length + '"';
                    input += 'title="' + hint + '" is_amount="' + is_amount + '" ' + disabled + '/>';

                    //col_md = 'col-md-6';

                } else if (type == 'SELECT') {

                    input = '<select class="select2 select_index" style="width: 100%" id="' + id + '" name = "' + id + '" index = "' + index + '" title="' + hint + '" is_amount="' + is_amount + '">';
                    input += '<option value="not_selected" selected>' + hint + '</option>';

                    for (var j = 0; j < value.length; j++) {

                        var item = value[j];
                        var item_id = item.id;
                        var item_description = item.description;
                        var item_index = (j + 1);

                        input += '<option value="' + item_id + '" title="' + item_description + '">' + item_description + '</option>';
                    }

                    input += '</select>';

                } else if (type == 'CHECKBOX') {

                    input = '<label>';
                    input += '<input type="checkbox" id="' + id + '" name = "' + id + '" index = "' + index + '" title="' + hint + '" is_amount="' + is_amount + '"></input> &nbsp; ' + label;
                    input += '</label>';

                } else if (type == 'TEXT_AREA') {

                    input = '<textarea class="form-control custom-control" placeholder="' + placeholder + '" id="' + id + '" name = "' + id + '" index = "' + index + '" rows="4" style="resize:none; width: 100%; max-width: 100%" title="' + hint + '" is_amount="' + is_amount + '"></textarea>';

                }

                if (type !== 'CHECKBOX') {

                    inputs += '<div class="' + col_md + '">';
                    //inputs += '<label for="' + id + '" id="label_' + id + '">' + label + '</label>';
                    inputs += '<div class="form-group">';
                    inputs += '<div class="input-group">';
                    inputs += '<div class="input-group-addon" style="' + input_group_addon_style + '">';
                    inputs += '<i class="' + fa_class + '" style = "' + fa_style + '"></i>';
                    inputs += '</div>';
                    inputs += input;
                    inputs += '</div>';
                    inputs += '</div>';
                    inputs += '</div>';

                } else {

                    inputs += '<div class="' + col_md + '">';
                    inputs += input;
                    inputs += '</div>';

                }

                index++;
            }
        }

        inputs += '</div>';

        // Si no hay campo de monto del servicio, bloqueamos el de input_devolution_amount
        if (is_amount_aux == false) {
            $('#input_devolution_amount').val(0);
            $('#input_devolution_amount').prop('disabled', true);
        }

        return inputs;
    }

    function set_values_numeric() {
        for (var i = 0; i < global_parameters_transaction.fields.length; i++) {
            var input_aux = global_parameters_transaction.fields[i];

            var input_id_aux = '#input_' + input_aux.id;
            var is_amount_aux = input_aux.is_amount;

            if (is_amount_aux == true) {

                var input_devolution_amount = parseInt($('#input_devolution_amount').val()) || null;
                var input_ajustement_percentage = parseInt($('#input_ajustement_percentage').val()) || null;
                var input_amount = null;

                $('#input_devolution_amount').val(input_devolution_amount);
                $('#input_ajustement_percentage').val(input_ajustement_percentage);

                if (input_devolution_amount !== null && input_ajustement_percentage !== null) {
                    var calculation = (input_devolution_amount * input_ajustement_percentage) / 100;
                    var calculation_aux = input_devolution_amount + calculation;

                    input_amount = calculation_aux;
                } else {
                    input_amount = input_devolution_amount;
                }

                $(input_id_aux).val(input_amount);

                break;
            }
        }
    }

    function validate_fields_lengths(fields, fields_type) {

        var service_source_id = global_parameters_transaction.service_source_id_new;

        var not_allowed = [7, '7', 10, '10'];

        //Validamos para que no meta números mayores al limite
        for (var i = 0; i < fields.length; i++) {
            var input = fields[i];

            var id = '#input_' + input.id;
            var description = input.description;
            var type = input.type;

            if (type == 'TEXT' || type == 'NUMBER' || type == 'TEXT_AREA') {

                $(id).on('mouseup keyup', function() {

                    var input_id = '#' + $(this).attr('id');
                    var description = $(this).attr('description');
                    var type = $(this).attr('type');
                    var max = parseInt($(this).attr('max'));
                    var is_amount = $(this).attr('is_amount');
                    var value = $(this).val();
                    var value_aux = null;

                    if (type == 'NUMBER') {

                        value_aux = parseInt(value) || 0;

                        if (fields_type == 'fields_devolution' || fields_type == 'fields_ajustement') {
                            if (value_aux > max) {
                                swal('Atención', 'El monto máximo para ' + description + ' es: ' + max, 'warning');
                                $(this).val(null);
                            }
                        } else {

                            //console.log('service_source_id evaluado:', service_source_id, not_allowed.indexOf(service_source_id));

                            if (not_allowed.indexOf(service_source_id) <= -1) {
                                /*if (value_aux > max) {
                                    swal('Atención', 'El monto máximo para ' + description + ' es: ' + max, 'warning');
                                    $(this).val(null);
                                }*/
                            } else {
                                value_aux = value.length;

                                if (value_aux > max) {
                                    swal('Atención', 'La cantidad de caracteres máxima para ' + description + ' es: ' + max, 'warning');
                                    $(this).val(null)
                                }

                            }
                        }

                        if (is_amount == 'true' && (input_id == '#input_devolution_amount' || input_id == '#input_ajustement_percentage')) {

                            set_values_numeric();
                        }
                    } else if (type == 'TEXT' || type == 'TEXT_AREA') {

                        value_aux = value.length;

                        if (value_aux > max) {
                            swal('Atención', 'La cantidad de caracteres máxima para ' + description + ' es: ' + max, 'warning');
                            $(this).val(null)
                        }
                    }

                });

            }
        }
    }

    /**
     * Seleccionar un servicio en especifico
     */
    function select_service_row(parameters) {

        $("#modal_load").modal();

        $('#modal_view_form_help_and_inputs').css('display', 'block');
        $('#modal_view_form_grid').css('display', 'none');

        $('#button_iterate_level').css('display', 'block');
        $('#button_iterate_level').html('<i class="fa fa-check"></i> &nbsp; Aceptar');
        $('#button_iterate_level').prop('disabled', false);

        $('#button_cancel').css('display', 'block');
        $('#button_cancel').html('<i class="fa fa-times"></i> &nbsp; Cancelar');

        $('#button_cms_confirm').css('display', 'none');

        deselect_rows();

        var id = parameters.id;
        var description = parameters.description;

        global_parameters_transaction.service_source_id_new = parameters.service_source_id;
        global_parameters_transaction.service_category_id_new = parameters.service_category_id;
        global_parameters_transaction.service_id_new = parameters.service_id;
        global_parameters_transaction.service_new = parameters.description;
        global_parameters_transaction.image_service = parameters.image_service;

        global_parameters_transaction.level = null;
        global_parameters_transaction.fields = [];
        global_parameters_transaction.values = [];

        // ------------------------------------------------------------------------

        if (global_parameters_transaction.service_id_selected !== null) {
            $('#select_service_row_' + global_parameters_transaction.service_id_selected).css({
                'background': 'white'
            });
        }

        global_parameters_transaction.service_id_selected = id;

        $('#select_service_row_' + id).css({
            'background': 'lightgray'
        });

        $('#new_service').html('Servicio: <b>' + description + '</b>');

        // ------------------------------------------------------------------------

        var url = '/cms_get_service_info/';

        var transaction_id = global_parameters_transaction.transaction_id;
        var amount = global_parameters_transaction.amount;

        var service_source_id = global_parameters_transaction.service_source_id;
        var service_category_id = global_parameters_transaction.service_category_id;
        var service_id = global_parameters_transaction.service_id;

        var service_category_id_new = global_parameters_transaction.service_category_id_new;
        var service_source_id_new = global_parameters_transaction.service_source_id_new;
        var service_id_new = global_parameters_transaction.service_id_new;
        var service_new = global_parameters_transaction.service_new;

        var image_service = global_parameters_transaction.image_service;

        console.log('image_service:', image_service);

        var json = {
            _token: token,
            transaction_id: transaction_id,
            amount: amount,

            service_source_id: service_source_id,
            service_category_id: service_category_id,
            service_id: service_id,

            service_source_id_new: service_source_id_new,
            service_category_id_new: service_category_id_new,
            service_id_new: service_id_new
        };


        //console.log('service_category_id_new ENVIADO', service_category_id_new);

        //console.log(json);

        $.post(url, json, function(data, status) {

            //console.log('data:', data);

            var error = data.error; // Error proveniente del CMS
            var message = data.message; // Mensaje proveniente del CMS
            var response = data.response; // Contenido proveniente del CMS

            var error_provider = data.response.error; //Error proveniente del WS y servicio de NETEL O INFONET
            var message_provider = data.response.message; //Mensaje proveniente del WS y servicio de NETEL O INFONET

            var title = '';
            var type = '';

            //------------------------------------------------------------------------------------------------------------------
            //Error proveniente del cms o de trex
            //------------------------------------------------------------------------------------------------------------------

            if (error || error_provider) {
                type = 'error';
                title = 'Ocurrió un error.';
            } else {
                type = 'success';
                title = 'Acción exitosa.';
            }

            //------------------------------------------------------------------------------------------------------------------
            //Se asigna el mensaje del cms o de trex
            //------------------------------------------------------------------------------------------------------------------

            message = (message == '') ? message_provider : message;

            if (type == 'success') {

                var index = 1;
                var commission = response.commission;
                var ajustement = response.ajustement;
                var level = response.level;
                var sequence = response.sequence;

                var help = response.help;
                var fields = response.fields;
                var values = response.values;

                var help_devolution = response.help_devolution;
                var fields_devolution = response.fields_devolution;
                var values_devolution = response.values_devolution;

                var help_ajustement = response.help_ajustement;
                var fields_ajustement = response.fields_ajustement;
                var values_ajustement = response.values_ajustement;

                //------------------------------------------------------------------------------------------------------------------

                global_parameters_transaction.commission = commission;
                global_parameters_transaction.ajustement = ajustement
                global_parameters_transaction.level = level;
                global_parameters_transaction.sequence = sequence;

                global_parameters_transaction.fields = fields;
                global_parameters_transaction.values = values;

                global_parameters_transaction.fields_devolution = fields_devolution
                global_parameters_transaction.values_devolution = values_devolution;

                global_parameters_transaction.fields_ajustement = fields_ajustement;
                global_parameters_transaction.values_ajustement = values_ajustement;

                //------------------------------------------------------------------------------------------------------------------

                $('#modal_view_form_help_devolution').html('');
                $('#modal_view_form_inputs_devolution').html('');

                var inputs = get_inputs(global_parameters_transaction.fields_devolution, 'fields_devolution');

                if (global_parameters_transaction.fields_devolution.length > 0) {

                    $('#modal_view_form_help_devolution').html('<b>' + help_devolution + '</b>');
                    $('#modal_view_form_inputs_devolution').append(inputs);

                    validate_fields_lengths(global_parameters_transaction.fields_devolution, 'fields_devolution');

                    /*$('.select_index').on('select2:select', function(e) {
                        var id = e.currentTarget.id;
                        var value = e.currentTarget.value;

                        console.log('ID:', id, 'VALUE:', value);
                    });*/

                }

                //------------------------------------------------------------------------------------------------------------------

                $('#modal_view_form_help').html('');
                $('#modal_view_form_inputs').html('');

                var inputs = get_inputs(global_parameters_transaction.fields, 'fields');

                if (global_parameters_transaction.fields.length > 0) {

                    var title_html = "<img src='" + image_service + "' style='width: 30px; height: 30px;'> <b>" + service_new + "</b>: ";

                    $("#modal_view_form_title").html(title_html);

                    $('#modal_view_form_help').html('<b>' + help + '</b>');
                    $('#modal_view_form_inputs').append(inputs);

                    $('.select2').select2();

                    /*$('.select_index').on('select2:select', function(e) {
                        var id = e.currentTarget.id;
                        var value = e.currentTarget.value;

                        var description = $('#' + id + ' :selected').text();
                        var index = $('#' + id).attr('index');
                        var index_next = parseInt(index) + 1;
                        var id_next = $("[index=" + index_next + "]").attr('id');
                        var default_ = $("[index=" + index_next + "]").attr('default');

                        if (value !== '-1') {
                            $("[index=" + index_next + "]").attr('placeholder', 'Ingresar ' + description);
                            $("#lavel_" + id_next).html(description + ': ');
                        } else {
                            $("[index=" + index_next + "]").attr('placeholder', 'Ingresar ' + default_);
                            $("#lavel_" + id_next).html(default_ + ': ');
                        }
                    });*/

                    $('input[type="checkbox"]').iCheck({
                        checkboxClass: 'icheckbox_square-grey',
                        radioClass: 'iradio_square-grey'
                    });

                    $('input[type="checkbox"]').iCheck('check');

                    validate_fields_lengths(global_parameters_transaction.fields, 'fields');

                } else {

                    swal({
                            title: 'Atención',
                            text: 'El servicio no tiene campos disponibles para completar',
                            type: 'warning',
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
                                $('#modal_view_form').modal('hide');
                                $('#modal_view_item').modal('hide');
                                $('#modal_view_options').modal('hide');
                            }
                        }
                    );
                }


                //------------------------------------------------------------------------------------------------------------------


                $('#div_check_ajustement').css({
                    'display': 'none'
                });

                $('#items_ajustement').css({
                    'display': 'none'
                });

                $('#modal_view_dialog').css({
                    'width': '900px'
                });

                var col_md_1 = 'col-md-4';
                var col_md_2 = 'col-md-6';

                $('#modal_view_form_help_ajustement').html('');
                $('#modal_view_form_inputs_ajustement').html('');

                if (ajustement) {

                    $('#modal_view_dialog').css({
                        'width': '90%'
                    });

                    col_md_1 = 'col-md-6';
                    col_md_2 = 'col-md-4';

                    $('#modal_view_form_grid').removeClass('col-md-12');
                    $('#modal_view_form_grid').addClass('col-md-6');

                    $('#div_check_ajustement').css({
                        'display': 'block'
                    });

                    $('#modal_view_form_help_ajustement').html('<b>' + help_ajustement + '</b>');

                    var inputs = get_inputs(global_parameters_transaction.fields_ajustement, 'fields_ajustement');

                    if (global_parameters_transaction.fields_ajustement.length > 0) {

                        $('#modal_view_form_inputs_ajustement').append(inputs);

                        $('.select2').select2();

                        $('#check_ajustement').iCheck({
                            checkboxClass: 'icheckbox_square-orange',
                            radioClass: 'iradio_square-orange'
                        });

                        $('#check_ajustement').iCheck('enable');
                        $('#check_ajustement').iCheck('uncheck');

                        $('#check_ajustement').on('ifChanged', function(event) {
                            if ($('#check_ajustement').is(":checked")) {

                                $('#items_ajustement').css({
                                    'display': 'block'
                                });

                                global_parameters_transaction.make_ajustement = true;

                                //console.log('SE VA REALIZAR EL AJUSTE.');

                            } else {

                                $('#items_ajustement').css({
                                    'display': 'none'
                                });

                                global_parameters_transaction.make_ajustement = false;

                                //console.log('NO SE VA REALIZAR EL AJUSTE.');

                            }
                        });

                        validate_fields_lengths(global_parameters_transaction.fields_ajustement, 'fields_ajustement');
                    }

                    /*var inputs_ids_aux = inputs_ids.join(", ")

                    for (var i = 0; i < inputs_ids.length; i++) {

                        var id = inputs_ids[i];

                        console.log('id', id);

                        $(id).css('border', "solid 1px #dd4b39 !important;");
                    }*/


                }



                if ($('#modal_view_form_help_and_inputs_devolution').attr('class') == col_md_1) {
                    $('#modal_view_form_help_and_inputs_devolution').removeClass(col_md_1);
                    $('#modal_view_form_help_and_inputs_devolution').addClass(col_md_2);
                }

                if ($('#modal_view_form_help_and_inputs').attr('class') == col_md_1) {
                    $('#modal_view_form_help_and_inputs').removeClass(col_md_1);
                    $('#modal_view_form_help_and_inputs').addClass(col_md_2);
                }

                if ($('#modal_view_form_grid').attr('class') == col_md_1) {
                    $('#modal_view_form_grid').removeClass(col_md_1);
                    $('#modal_view_form_grid').addClass(col_md_2);
                }

                if ($('#div_check_ajustement').attr('class') == col_md_1) {
                    $('#div_check_ajustement').removeClass(col_md_1);
                    $('#div_check_ajustement').addClass(col_md_2);
                }


                //------------------------------------------------------------------------------------------------------------------

                $("#modal_load").modal('hide');

                $("#modal_view_form").modal();

            } else {

                $("#modal_load").modal('hide');

                swal({
                        title: title,
                        text: message,
                        type: type,
                        showCancelButton: false,
                        confirmButtonColor: '#3c8dbc',
                        confirmButtonText: 'Aceptar',
                        cancelButtonText: 'No.',
                        closeOnClickOutside: false
                    },
                    function(isConfirm) {
                        if (isConfirm) {}
                    }
                );
            }


        }).error(function(xhr, textStatus, error) {
            console.log('xhr.statusText:', xhr.statusText);
            console.log('textStatus:', textStatus);
            console.log('error:', error);

            $("#modal_load").modal('hide');

            $('#button_iterate_level').css('display', 'block');
            $('#button_iterate_level').html('<i class="fa fa-check"></i> &nbsp; Aceptar');
            $('#button_iterate_level').prop('disabled', false);
            $('#button_cancel').css('display', 'block');

            swal({
                    title: 'Error al querer obtener datos del formulario',
                    text: 'La sesión a expirado.',
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
                        //location.reload();
                        document.location.href = 'cms_transactions_index';
                        /*$('#modal_view_form').modal('hide');
                        $('#modal_view_item').modal('hide');
                        $('#modal_view_options').modal('hide');*/
                    }
                }
            );
        });
    }

    /**
     * Confirmación de nivel 2
     */
    function select_service_row_option() {
        swal({
                title: 'Atención',
                text: 'Opción seleccionada ¿Confirmar transacción?',
                type: 'info',
                showCancelButton: true,
                confirmButtonColor: '#3c8dbc',
                confirmButtonText: 'Aceptar',
                cancelButtonText: 'Cancelar.',
                closeOnClickOutside: false,
                closeOnConfirm: false,
                closeOnCancel: false
            },
            function(isConfirm) {
                if (isConfirm) {

                    swal.close();

                    cms_confirm(2);

                } else {
                    swal('Atención', 'Transacción cancelada', 'error');
                }
            }
        );

    }

    //Función que se llama cuando el servicio es de nivel 2
    function cms_get_more_service_info() {

        $('#button_iterate_level').html('<i class="fa fa-spin fa-refresh"></i> &nbsp; Cargando...');
        $('#button_iterate_level').prop('disabled', true);
        $('#button_cancel').css('display', 'none');

        var transaction_id = global_parameters_transaction.transaction_id;
        var service_source_id = global_parameters_transaction.service_source_id;
        var service_id = global_parameters_transaction.service_id;
        var service_source_id_new = global_parameters_transaction.service_source_id_new;
        var service_id_new = global_parameters_transaction.service_id_new;
        var service_new = global_parameters_transaction.service_new;
        var commission = global_parameters_transaction.commission;
        var ajustement = global_parameters_transaction.ajustement;

        var fields = global_parameters_transaction.fields;
        var values = global_parameters_transaction.values;

        var fields_devolution = global_parameters_transaction.fields_devolution;
        var values_devolution = global_parameters_transaction.values_devolution;

        var fields_ajustement = global_parameters_transaction.fields_ajustement;
        var values_ajustement = global_parameters_transaction.values_ajustement;

        var image_service = global_parameters_transaction.image_service;


        //console.log('fields:', fields);
        //console.log('values:', values);

        //console.log('fields_devolution:', fields_devolution);
        //console.log('values_devolution:', values_devolution);

        //console.log('fields_ajustement:', fields_ajustement);
        //console.log('values_ajustement:', values_ajustement);

        var url = '/cms_get_more_service_info/';

        var json = {
            _token: token,
            transaction_id: transaction_id,
            service_source_id: service_source_id,
            service_id: service_id,
            service_source_id_new: service_source_id_new,
            service_id_new: service_id_new,
            commission: commission,

            fields: fields,
            values: values,

            fields_devolution: fields_devolution,
            values_devolution: values_devolution,

            fields_ajustement: fields_ajustement,
            values_ajustement: values_ajustement
        };

        $.post(url, json, function(data, status) {

            //console.log('data:', data);

            var error = data.error; // Error proveniente del CMS
            var message = data.message; // Mensaje proveniente del CMS
            var response = data.response; // Contenido proveniente del CMS

            if (response !== null) {
                var error_provider = data.response.error; //Error proveniente del WS y servicio de NETEL O INFONET
                var message_provider = data.response.message; //Mensaje proveniente del WS y servicio de NETEL O INFONET

                var level = response.level;
                var sequence = response.sequence;
                var fields = response.fields;
                var values = response.values;
                var help = response.help;
                var commission = response.commission;

                var title = '';
                var type = '';

                if (error || error_provider) {
                    type = 'error';
                    title = 'Ocurrió un error.';
                } else {
                    type = 'success';
                    title = 'Acción exitosa.';
                }

                message = (message == '') ? message_provider : message;

                if (type == 'success') {

                    global_parameters_transaction.level = level;
                    global_parameters_transaction.sequence = sequence;
                    global_parameters_transaction.fields = fields;
                    global_parameters_transaction.values = values;
                    global_parameters_transaction.input_amount = null;

                    var parameters = {
                        level: level,
                        sequence: sequence,
                        fields: fields,
                        values: values,
                        input_amount: null,
                        commission: commission
                    };

                    parameters = JSON.stringify(parameters);

                    //$("#button_cms_confirm").prop('onclick', null);
                    //$('#button_cms_confirm').attr("onclick", "").unbind("click");
                    //console.log('CLICK DE button_cms_confirm 1:', $('#button_cms_confirm').attr("onclick"));

                    /*$('#button_cms_confirm').attr({
                        'onclick': "select_service_row_option(" + parameters + ")"
                    });

                    console.log('CLICK DE button_cms_confirm 3:', $('#button_cms_confirm').attr("onclick"));*/

                    $('#button_cms_confirm').css({
                        'display': 'none'
                    });

                    $('#button_cms_confirm').html('<i class="fa fa-check"></i> &nbsp; Confirmar');

                    var partial_payments_amount = false;

                    if (fields !== null) {

                        //console.log('ajustement:', ajustement);

                        var col_md = 'col-md-6';

                        // Si el servicio de nivel 2 trae campos que mostrar también mostramos el check de ajuste.
                        if (ajustement) {

                            $('#div_check_ajustement').css({
                                'display': 'block'
                            });

                            /*$('#modal_view_dialog').css({
                                'width': '1000px'
                            });*/

                            col_md = 'col-md-6';
                        } else {

                            $('#div_check_ajustement').css({
                                'display': 'none'
                            });

                            /*$('#modal_view_dialog').css({
                                'width': '580px'
                            });*/

                            col_md = 'col-md-12';
                        }

                        if (fields.length > 0) {

                            var headers_html = '';
                            var rows_html = '';

                            var title_html = "<img src='" + image_service + "' style='width: 30px; height: 30px;'> <b>Confirmar los datos:</b>";

                            $('#modal_view_form_title').html(title_html);

                            $('#modal_view_form_help_and_inputs').css('display', 'none');
                            $('#modal_view_form_grid').css('display', 'block');

                            $('#button_iterate_level').css('display', 'none');
                            $('#button_iterate_level').html('<i class="fa fa-check"></i> &nbsp; Aceptar');

                            $('#button_iterate_level').css({
                                'display': 'none'
                            }).html('<i class="fa fa-check"></i> &nbsp; Aceptar');

                            $('#button_cancel').css({
                                'display': 'block'
                            }).html('<i class="fa fa-arrow-left"></i> &nbsp; Atras');

                            $('#button_cms_confirm').css({
                                'display': 'block'
                            }).html('<i class="fa fa-check"></i> &nbsp; Confirmar');

                            /*console.log('event onclick button_cms_confirm:', $('#button_cms_confirm').attr("onclick"));

                            $('#button_cms_confirm').attr("onclick", "").unbind("click");

                            console.log('event onclick button_cms_confirm:', $('#button_cms_confirm').attr("onclick"));

                            $('#button_cms_confirm').attr('onclick', "select_service_row_option()");*/

                            /*headers_html += '<tr>';

                            for (var i = 0; i < fields.length; i++) {
                                var input = fields[i];
                                var description = input.description;
                                var label = input.label;
                                var visible = input.visible;
                                var required = input.required;

                                if (visible == true) {
                                    headers_html += '<th>' + description + '</th>';
                                }
                            }

                            headers_html += '</tr>';*/

                            headers_html += '<tr>';
                            headers_html += '<th>Campo</th>';
                            headers_html += '<th>Valor</th>';
                            headers_html += '</tr>';

                            /*rows_html += "<tr style='cursor: pointer' title='Seleccionar opción' onclick='select_service_row_option(" + parameters + ")'>";

                            for (var i = 0; i < fields.length; i++) {
                                var input = fields[i];
                                var value = input.value;
                                var visible = input.visible;
                                var required = input.required;

                                if (visible == true) {
                                    rows_html += '<td>' + value + '</td>';
                                }
                            }

                            rows_html += '</tr>';*/

                            rows_html += "<tr>";

                            for (var i = 0; i < fields.length; i++) {
                                var input = fields[i];

                                var id = input.id;
                                var description = input.description;
                                var type = input.type;
                                var label = input.label;
                                var min_length = input.min_length;
                                var max_length = input.max_length;
                                var value = input.value;
                                var visible = input.visible;
                                var required = input.required;
                                var send = input.send;
                                var is_amount = input.is_amount;
                                var hint = input.hint;
                                var editable = input.editable;
                                var placeholder = hint;

                                var value_column = '';
                                var style = '';
                                var style_column = '';
                                var fa_icon = '';

                                console.log('description:', description, 'is_amount:', is_amount, 'value', value, 'type', type);

                                if (is_amount) {

                                    $('#input_devolution_amount').val(value);

                                    var value_aux = $.number(value, 0, ',', '.');

                                    style = 'background-color: #eee;';
                                    style_column = 'font-weight: 700';
                                    value_column = '<span class="label label-success" style="font-size: 15px; font-weight: 400; padding: 5px;">' + value_aux + ' &nbsp; <i class="fa fa-money" style="color: white;"></i></span>';

                                    /*if (id == 'partial_payments_amount') {

                                        style = 'background-color: #eee;';
                                        style_column = 'font-weight: 700';

                                        value_column = '<input type="' + type + '" class="form-control" placeholder="' + placeholder + '" ';
                                        value_column += 'id="' + id + '" name = "' + id + '" description = "' + description + '" type = "' + type + '" ';
                                        value_column += 'minlength="' + min_length + '" maxlength="' + max_length + '" min="' + min_length + '" max="' + max_length + '"';
                                        value_column += 'title="' + hint + '" is_amount="' + is_amount + '" value="' + value + '" disabled/>';

                                        partial_payments_amount = true;

                                    } else {

                                        var value_aux = $.number(value, 0, ',', '.');

                                        style = 'background-color: #eee;';
                                        style_column = 'font-weight: 700';
                                        value_column = '<span class="label label-success" style="font-size: 15px; font-weight: 400; padding: 5px;">' + value_aux + ' &nbsp; <i class="fa fa-money" style="color: white;"></i></span>';
                                    }*/


                                } else {

                                    if (type == 'DETAIL') {
                                        value_column = '<ol>';

                                        for (var j = 0; j < value.length; j++) {
                                            var item = value[j];
                                            var item_id = item.id;
                                            var item_description = item.description;

                                            value_column += '<li>' + item_id + ': ' + item_description + '</li>';

                                            console.log('item column:', value_column);
                                        }

                                        value_column += '</ol>';

                                        console.log('value_column complete:', value_column);
                                    } else {
                                        value_column = '<span style="padding: 5px; vertical-align: middle;">' + value + ' &nbsp; </span>';
                                    }
                                }

                                if (visible == true) {
                                    rows_html += '<tr style="' + style + '">';
                                    rows_html += '<td style="' + style_column + '">' + description + ':</td>';
                                    rows_html += '<td>' + value_column + '</td>';
                                    rows_html += '</tr>';
                                }
                            }

                            console.log('partial_payments_amount:', partial_payments_amount);


                            $('#datatable_modal_view_form thead').html('');
                            $('#datatable_modal_view_form tbody').html('');

                            $('#datatable_modal_view_form thead').append(headers_html);
                            $('#datatable_modal_view_form tbody').append(rows_html);

                            // La función de pago parcial debe generarse al agregarse el componente
                            /*if (partial_payments_amount) {

                                console.log('se generó la función mouseup');

                                $('#partial_payments_amount').on('mouseup keyup', function() {

                                    var max = parseInt($(this).attr('max'));
                                    var value = $(this).val();
                                    var value_aux = null;

                                    var value_aux = parseInt(value) || 0;

                                    if (value_aux <= max) {
                                        $('#input_devolution_amount').val(value_aux);
                                    } else {
                                        swal('Atención', 'El monto a pagar debe ser menor o igual al de la deuda.\n\nDeuda total: ' + max, 'warning');
                                        $('#input_devolution_amount').val(null);
                                    }

                                });
                            }*/

                            /*
                            if ($.fn.DataTable.isDataTable('#datatable_modal_view_form')) {
                                $('#datatable_modal_view_form').DataTable().destroy();
                            }

                            $('#datatable_modal_view_form thead').empty();
                            $('#datatable_modal_view_form tbody').empty();

                            $('#datatable_modal_view_form thead').append(headers_html);
                            $('#datatable_modal_view_form tbody').append(rows_html);

                            var data_table_config = {
                                fixedHeader: true,
                                pageLength: 5,
                                lengthMenu: [
                                    1, 2, 5, 10, 20, 30, 50, 70, 100, 150, 300, 500, 1000, 1500, 2000, 5000, 10000
                                ],
                                dom: '',
                                language: {
                                    "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
                                },
                                scroller: true,
                                displayLength: 10,
                                order: [],
                                columnDefs: [{
                                    targets: 'no-sort',
                                    orderable: false,
                                }],
                                processing: true,
                                bPaginate: false
                            }

                            $('#datatable_modal_view_form').DataTable(data_table_config);
                            */

                            for (var i = 0; i < fields_devolution.length; i++) {
                                var input = fields_devolution[i];
                                var id = '#input_' + input.id;
                                var type = input.type;
                                var is_amount = input.is_amount;

                                //console.log('fields_devolution:', input);

                                if (is_amount == true) {
                                    $(id).prop('disabled', true);
                                }
                            }

                            $('#check_ajustement').iCheck('disable');

                            for (var i = 0; i < fields_ajustement.length; i++) {
                                var input = fields_ajustement[i];
                                var id = '#input_' + input.id;
                                var type = input.type;
                                var send = input.send;

                                //console.log('fields_ajustement:', input);

                                if (send == true) {

                                    if (type == 'CHECKBOX') {
                                        $(id).iCheck('disable');
                                    } else {
                                        $(id).prop('disabled', true);
                                    }
                                }
                            }

                        } else {
                            $('#button_iterate_level').css('display', 'block');
                            $('#button_iterate_level').html('<i class="fa fa-check"></i> &nbsp; Aceptar');
                            $('#button_iterate_level').prop('disabled', false);
                            $('#button_cancel').css('display', 'block');

                            swal({
                                    title: 'Atención',
                                    text: 'La consulta no retornó ningún dato.',
                                    type: 'warning',
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
                                        $('#modal_view_form').modal('hide');
                                        $('#modal_view_item').modal('hide');
                                        $('#modal_view_options').modal('hide');
                                    }
                                }
                            );
                        }

                    } else {

                        $('#button_iterate_level').css('display', 'block');
                        $('#button_iterate_level').html('<i class="fa fa-check"></i> &nbsp; Aceptar');
                        $('#button_iterate_level').prop('disabled', false);
                        $('#button_cancel').css('display', 'block');

                        swal({
                                title: 'Atención',
                                text: 'El servicio no retornó ninguna opción.',
                                type: 'warning',
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
                                    $('#modal_view_form').modal('hide');
                                    $('#modal_view_item').modal('hide');
                                    $('#modal_view_options').modal('hide');
                                }
                            }
                        );
                    }

                } else {

                    $("#modal_load").modal('hide');

                    $('#button_iterate_level').css('display', 'block');
                    $('#button_iterate_level').html('<i class="fa fa-check"></i> &nbsp; Aceptar');
                    $('#button_iterate_level').prop('disabled', false);
                    $('#button_cancel').css('display', 'block');

                    swal({
                            title: title,
                            text: message,
                            type: type,
                            showCancelButton: false,
                            confirmButtonColor: '#3c8dbc',
                            confirmButtonText: 'Aceptar',
                            cancelButtonText: 'No.',
                            closeOnClickOutside: false
                        },
                        function(isConfirm) {
                            if (isConfirm) {}
                        }
                    );
                }
            } else {

                $('#button_iterate_level').css('display', 'block');
                $('#button_iterate_level').html('<i class="fa fa-check"></i> &nbsp; Aceptar');
                $('#button_iterate_level').prop('disabled', false);
                $('#button_cancel').css('display', 'block');

                swal({
                        title: 'Error en la consulta',
                        text: 'No se pudo obtener los datos.',
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

                        }
                    }
                );
            }


        }).error(function(error) {
            swal({
                    title: 'Error al querer obtener más datos del servicio',
                    text: 'La sesión a expirado.',
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
                        //location.reload();
                        document.location.href = 'cms_transactions_index';
                        /*$('#modal_view_form').modal('hide');
                        $('#modal_view_item').modal('hide');
                        $('#modal_view_options').modal('hide');*/
                    }
                }
            );
        });
    }

    function deselect_rows() {

        //console.log('global_parameters_transaction.services_list', global_parameters_transaction.services_list);

        if (global_parameters_transaction.services_list !== null) {

            for (var i = 0; i < global_parameters_transaction.services_list.length; i++) {
                var item = global_parameters_transaction.services_list[i];
                var select_service_row_id = item.id;

                $('#select_service_row_' + select_service_row_id).css({
                    'background': 'white'
                });
            }

        }
    }

    function cms_confirm(context /*Contexto para saber que mostrar/esconder*/ ) {
        //console.log('cms_confirm');


        $("#modal_load").modal();

        if (context == 1) {
            $('#button_iterate_level').html('<i class="fa fa-spin fa-refresh"></i> &nbsp; Cargando...');
            $('#button_iterate_level').prop('disabled', true);
            $('#button_cancel').css('display', 'none');
        } else if (context == 2) {
            // $('#modal_view_form').modal('hide');
            // $('#modal_view_item').modal('hide');
            // $('#modal_view_options').modal('hide');
        }

        var url = '/cms_confirm/';

        var transaction_id = global_parameters_transaction.transaction_id;
        var amount = global_parameters_transaction.amount;
        var service_source_id = global_parameters_transaction.service_source_id;
        var service_id = global_parameters_transaction.service_id;
        var service_source_id_new = global_parameters_transaction.service_source_id_new;
        var service_id_new = global_parameters_transaction.service_id_new;
        var owner_id = global_parameters_transaction.owner_id;

        var level = global_parameters_transaction.level;
        var sequence = global_parameters_transaction.sequence;
        var input_amount = global_parameters_transaction.input_amount;

        var commission = global_parameters_transaction.commission;
        var ajustement = global_parameters_transaction.ajustement;
        var make_ajustement = global_parameters_transaction.make_ajustement;


        var fields = global_parameters_transaction.fields;
        var values = global_parameters_transaction.values;

        var fields_devolution = global_parameters_transaction.fields_devolution;
        var values_devolution = global_parameters_transaction.values_devolution;

        var fields_ajustement = global_parameters_transaction.fields_ajustement;
        var values_ajustement = global_parameters_transaction.values_ajustement;

        if (typeof amount === 'string') {
            amount = parseInt(amount.replace('.', ''));
        }

        if (typeof input_amount === 'string') {
            input_amount = parseInt(input_amount.replace('.', ''));
        }

        var json = {
            _token: token,
            transaction_id: transaction_id,
            amount: amount,

            owner_id: owner_id,
            service_source_id: service_source_id,
            service_id: service_id,

            service_source_id_new: service_source_id_new,
            service_id_new: service_id_new,
            level: level,
            sequence: sequence,
            commission: commission,

            ajustement: ajustement,
            make_ajustement: make_ajustement,

            fields: fields,
            values: values,

            fields_devolution: fields_devolution,
            values_devolution: values_devolution,

            fields_ajustement: fields_ajustement,
            values_ajustement: values_ajustement
        };

        $.post(url, json, function(data, status) {

            console.log('data de confirm:', data);

            var error = data.error; // Error proveniente del CMS
            var text = data.message; // Mensaje proveniente del CMS
            var transaction_devolution_id = data.transaction_devolution_id; // Mensaje proveniente del CMS
            var detail = data.detail; // Mensaje proveniente del CMS

            var title = '';
            var type = '';

            if (error == true) {
                type = 'error';
                title = 'Ocurrió un error.';
            } else {
                type = 'success';
                title = 'Acción exitosa.';
            }

            if (context == 1) {
                $('#button_iterate_level').css('display', 'block');
                $('#button_iterate_level').html('<i class="fa fa-check"></i> &nbsp; Aceptar');
                $('#button_iterate_level').prop('disabled', false);
                $('#button_cancel').css('display', 'block');
            }

            $("#modal_load").modal('hide');

            swal({
                    title: title,
                    text: text,
                    type: type,
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
                        if (type == 'success') {
                            $('#modal_view_form').modal('hide');
                            $('#modal_view_item').modal('hide');
                            $('#modal_view_options').modal('hide');

                            //modal_ticket_open(transaction_devolution_id);

                            document.location.href = 'cms_transactions_index';
                        }
                    }
                }
            );

        }).error(function(error) {

            $("#modal_load").modal('hide');

            swal({
                    title: 'Error al confirmar',
                    text: 'La sesión a expirado.',
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
                        //location.reload();
                        document.location.href = 'cms_transactions_index';
                        /*$('#modal_view_form').modal('hide');
                        $('#modal_view_item').modal('hide');
                        $('#modal_view_options').modal('hide');*/
                    }
                }
            );
        });
    }

    function validate_fields(fields) {

        var result = {
            incomplete_field: false,
            id_field: null,
            description_field: null,
            fields: fields
        };

        if (result.fields !== null) {

            for (var i = 0; i < result.fields.length; i++) {
                var item = result.fields[i];
                var id = '#input_' + item.id;
                var description = item.description;
                var type = item.type;
                var send = item.send;
                var is_amount = item.is_amount;
                var value = null;

                if (send) {
                    if (type == 'SELECT') {
                        value = $(id + ' :selected').val();
                    } else {
                        value = $(id).val();
                    }

                    if (value !== null && value !== '' && value !== 'not_selected') {

                        result.fields[i].value = value;

                    } else {

                        console.log('CAMPO INCOMPLETO:', description, id, value);

                        result.incomplete_field = true;
                        result.id_field = id;
                        result.description_field = description;
                        break;
                    }
                }

                console.log('INPUT:', description, id, value);
            }

        } else {
            swal({
                    title: 'Atención.',
                    text: 'Los campos a validar no existen.',
                    type: 'warning',
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
                        //location.reload();
                        document.location.href = 'cms_transactions_index';
                    }
                }
            );
        }


        //console.log('Resultado final en validate_fields:', result);

        return result;
    }

    function iterate_level() {

        //console.log('fields_devolution:', global_parameters_transaction.fields_devolution);
        //console.log('fields:', global_parameters_transaction.fields);
        //console.log('fields_ajustement:', global_parameters_transaction.fields_ajustement);

        var result_1 = validate_fields(global_parameters_transaction.fields_devolution);

        var incomplete_field_1 = result_1.incomplete_field;
        var id_field_1 = result_1.id_field;
        var description_field_1 = result_1.description_field;
        global_parameters_transaction.fields_devolution = result_1.fields;

        //--------------------------------------------------------------------------------------------

        var result_2 = validate_fields(global_parameters_transaction.fields);

        var incomplete_field_2 = result_2.incomplete_field;
        var id_field_2 = result_2.id_field;
        var description_field_2 = result_2.description_field;
        global_parameters_transaction.fields = result_2.fields;

        //--------------------------------------------------------------------------------------------

        var ajustement = global_parameters_transaction.ajustement;
        var make_ajustement = global_parameters_transaction.make_ajustement;

        var result_3 = null;
        var incomplete_field_3 = false;
        var id_field_3 = null;
        var description_field_3 = null;

        //console.log('ajustement:', ajustement);
        //console.log('make_ajustement:', make_ajustement);

        if (ajustement && make_ajustement) {
            result_3 = validate_fields(global_parameters_transaction.fields_ajustement);
            incomplete_field_3 = result_3.incomplete_field;
            id_field_3 = result_3.id_field;
            description_field_3 = result_3.description_field;
            global_parameters_transaction.fields_ajustement = result_3.fields;
        }


        console.log('incomplete_field_1:', incomplete_field_1);
        console.log('incomplete_field_2:', incomplete_field_2);
        console.log('incomplete_field_3:', incomplete_field_3);

        //--------------------------------------------------------------------------------------------

        if (incomplete_field_1 == false && incomplete_field_2 == false && incomplete_field_3 == false) {

            if (global_parameters_transaction.level == 1) {

                cms_confirm(1);

            } else if (global_parameters_transaction.level == 2) {

                cms_get_more_service_info();

            }

        } else {

            var id_field = null;
            var description_field = null;

            if (incomplete_field_3) {
                id_field = id_field_3;
                description_field = description_field_3;
            }

            if (incomplete_field_2) {
                id_field = id_field_2;
                description_field = description_field_2;
            }

            if (incomplete_field_1) {
                id_field = id_field_1;
                description_field = description_field_1;
            }

            swal({
                    title: 'Atención.',
                    text: 'Debe completar el campo: ' + description_field,
                    type: 'warning',
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
                        //console.log('ID:', id_field);
                        //$(id_field).get(0).focus();
                        //$(id_field).trigger("click");
                    }
                }
            );
        }

        //console.log('fields_devolution:', global_parameters_transaction.fields_devolution);
        //console.log('fields:', global_parameters_transaction.fields);
        //console.log('fields_ajustement:', global_parameters_transaction.fields_ajustement);
    }

    // Abre la ventana para seleccionar un servicio
    function through_a_service(parameters) {

        var category_id = parameters.category_id;
        var category_description = parameters.category_description;

        var transaction_id = global_parameters_transaction.transaction_id;
        var current_service = global_parameters_transaction.service;
        var amount = global_parameters_transaction.amount;

        // Mostrando datos en pantalla:
        $('#modal_view_item_title').html('Seleccionar servicio de <b>' + category_description + '</b> para la transacción N° <b>' + transaction_id + '</b>: ');

        // Limpiar tabla para cargarla nuevamente

        if ($.fn.DataTable.isDataTable('#datatable_services')) {
            $('#datatable_services').DataTable().destroy();
        }

        $('#datatable_services thead').html('');
        $('#datatable_services tbody').html('');

        var service_source_id = null;
        var service_id = null;

        // Obtener lista de servicios del Proveedor

        var url = '/get_services_for_returns/';

        var json = {
            _token: token,
            option: category_description,
            category_id: category_id
        };

        //console.log('global_parameters_transaction json', json);

        $.post(url, json, function(data, status) {

            global_parameters_transaction.services_list = data.response;

            var row = "<tr>";
            row += '<th></th>';
            row += '<th>Proveedor</th>';
            row += '<th>Marca - Servicio </th>';
            row += '</tr>';

            $('#datatable_services thead').append(row);

            for (var i = 0; i < global_parameters_transaction.services_list.length; i++) {

                var item = global_parameters_transaction.services_list[i];

                var service_source_id = item.service_source_id;
                var provider = item.provider;

                var service_category_id = item.service_category_id;
                var service_category = item.service_category;

                var service_id = item.service_id;
                var description = item.description;

                var id = service_source_id + '_' + service_id;

                var associated_image = item.associated_image;

                var img = '';

                if (associated_image !== null && associated_image !== '') {
                    img = "<img src='" + associated_image + "' width='30' height='30' />";
                }

                console.log('Imagen:', associated_image);

                var json = '{';
                json += '"id": "' + id + '",';
                json += '"service_source_id": "' + service_source_id + '",';
                json += '"service_category_id": "' + service_category_id + '",';
                json += '"service_id": "' + service_id + '",';
                json += '"description": "' + description + '",';
                json += '"image_service": "' + associated_image + '"';
                json += '}';

                //console.log('json:', json);

                var row = "<tr onclick='select_service_row(" + json + ")' style='cursor: pointer' id='select_service_row_" + id + "' >";
                row += '<td>' + provider + '</td>';
                row += '<td></td>';
                row += '<td>' + img + '&nbsp; &nbsp;' + description + '</td>';
                row += '</tr>';

                $('#datatable_services tbody').append(row);

                //console.log('item: ', item);
            }

            /*var data_table_config = {
                fixedHeader: true,
                pageLength: 5,
                lengthMenu: [
                    1, 2, 5, 10, 20, 30, 50, 70, 100, 150, 300, 500, 1000, 1500, 2000, 5000, 10000
                ],
                dom: '<"pull-left"f><"pull-right"l>tip',
                language: {
                    "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
                },
                scroller: true,
                displayLength: 10,
                order: [],
                columnDefs: [{
                    targets: 'no-sort',
                    orderable: false,
                }],
                processing: true
            }

            var table = $('#datatable_services').DataTable(data_table_config).column(1)
                .data()
                .unique();*/

            var groupColumn = 0;

            var table = $('#datatable_services').DataTable({
                fixedHeader: true,
                pageLength: 5,
                lengthMenu: [
                    1, 2, 5, 10, 20, 30, 50, 70, 100, 150, 300, 500, 1000, 1500, 2000, 5000, 10000
                ],
                dom: '<"pull-left"f><"pull-right"l>tip',
                language: {
                    "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
                },
                scroller: true,
                processing: true,
                columnDefs: [{
                    visible: false,
                    targets: groupColumn
                }],
                order: [
                    [groupColumn, 'asc']
                ],
                displayLength: 5,
                drawCallback: function(settings) {
                    var api = this.api();
                    var rows = api.rows({
                        page: 'current'
                    }).nodes();
                    var last = null;

                    api
                        .column(groupColumn, {
                            page: 'current'
                        })
                        .data()
                        .each(function(group, i) {

                            if (last !== group) {

                                var color = '#ddd';

                                /*if (group == 'Medicina Prepaga') {
                                    color = '#117864';
                                } else if (group == 'Colegios Y Universidades') {
                                    color = '#21618C';
                                } else if (group == 'Servicios Publicos') {
                                    color = '#873600';
                                } else if (group == 'Telefonia Movil') {
                                    color = '#6495ED';
                                } else if (group == 'Bancos Y Financieras') {
                                    color = '#633974';
                                } else if (group == 'Cooperativas') {
                                    color = '#1D8348';
                                } else if (group == 'Seguros') {
                                    color = '#873600';
                                } else if (group == 'Tv Cable E Internet') {
                                    color = '#008080';
                                } else if (group == 'Servicios Financieros') {
                                    color = '#196F3D';
                                } else if (group == 'Inmobiliarias') {
                                    color = '#935116';
                                } else if (group == 'Electrodomesticos') {
                                    color = '#0000FF';
                                } else if (group == 'Editoriales') {
                                    color = '#0E6655';
                                } else if (group == 'Otros Servicios') {
                                    color = '#5F6A6A';
                                } else if (group == 'Ocio y Entretenimiento') {
                                    color = '#7B241C';
                                } else if (group == 'Remesas') {
                                    color = '#943126';
                                } else if (group == 'Servicios de transporte') {
                                    color = '#4DCFD3';
                                }*/

                                if (group == 'Eglobal') {
                                    color = '#ff5a16';
                                } else if (group == 'Infonet') {
                                    color = '#00377c';
                                } else if (group == 'Netel TREX') {
                                    color = '#8E1414';
                                }

                                var td = $('<td>');
                                td.attr({
                                    'colspan': '3',
                                    'style': 'color: white !important'
                                }).append(group);

                                var tr = $('<tr>');
                                tr.attr({
                                    'class': 'group',
                                    'style': 'background-color:' + color + ' !important; cursor: pointer'
                                }).append(td);

                                $(rows).eq(i).before(tr);

                                last = group;
                            }
                        });
                },
            });

            table.column(0).data().unique();

            // Order by the grouping
            $('#datatable_services tbody').on('click', 'tr.group', function() {
                var currentOrder = table.order()[0];
                if (currentOrder[0] === groupColumn && currentOrder[1] === 'asc') {
                    table.order([groupColumn, 'desc']).draw();
                } else {
                    table.order([groupColumn, 'asc']).draw();
                }
            });

            $("#modal_view_item").modal();

        }).error(function(error) {
            swal({
                    title: 'Error al querer obtener servicios.',
                    text: 'La sesión a expirado.',
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
                        //location.reload();
                        document.location.href = 'cms_transactions_index';
                        /*$('#modal_view_form').modal('hide');
                        $('#modal_view_item').modal('hide');
                        $('#modal_view_options').modal('hide');*/
                    }
                }
            );
        });


    }

    //-----------------------------------------------------------------------------------------------

    function get_services_by_brand_for_transactions(service_id) {

        var url = '/get_services_by_brand_for_transactions/';

        var service_source_id = parseInt($('#service_source_id').val());
        service_source_id = (Number.isNaN(service_source_id)) ? 'Todos' : service_source_id;

        var json = {
            _token: token,
            service_source_id: service_source_id
        };

        $.post(url, json, function(data, status) {

            $('#service_id').val(null).trigger('change');
            $('#service_id').empty().trigger("change");

            var option = new Option('Todos', 'Todos', false, false);
            $('#service_id').append(option);

            for (var i = 0; i < data.length; i++) {
                var item = data[i];
                var id = item.id;
                var description = item.description;
                var option = new Option(description, id, false, false);
                $('#service_id').append(option);
            }

            if (service_id !== null) {
                $('#service_id').val(service_id).trigger('change');
            }
        });
    }

    //-----------------------------------------------------------------------------------------------

    function search(button_name) {

        var input = $('<input>').attr({
            'type': 'hidden',
            'id': 'button_name',
            'name': 'button_name',
            'value': button_name
        });

        if (button_name == 'search') {
            $('#content').css('display', 'none');
            $('#div_load').css('display', 'block');
        }

        $('#form_search').append(input);
        $('#form_search').submit();
    }

    //-----------------------------------------------------------------------------------------------

    //Datatable config
    var data_table_config = {
        //custom
        orderCellsTop: true,
        fixedHeader: true,
        pageLength: 20,
        lengthMenu: [
            1, 2, 5, 10, 20, 30, 50, 70, 100, 150, 300, 500, 1000, 1500, 2000
        ],
        dom: '<"pull-left"f><"pull-right"l>tip',
        language: {
            "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
        },
        scroller: true,
        processing: true,
        initComplete: function(settings, json) {
            $('#div_load').css('display', 'none');
            $('#content').css('display', 'block');
        }
    }

    // Order by the grouping
    $('#datatable_1 tbody').on('click', 'tr.group', function() {
        var currentOrder = table.order()[0];
        if (currentOrder[0] === groupColumn && currentOrder[1] === 'asc') {
            table.order([groupColumn, 'desc']).draw();
        } else {
            table.order([groupColumn, 'asc']).draw();
        }
    });

    var table = $('#datatable_1').DataTable(data_table_config);

    //-----------------------------------------------------------------------------------------------


    $('#created_at').val("{{ $created_at }}");
    $('#transaction_id').val("{{ $transaction_id }}");
    $('#amount').val("{{ $amount }}");

    //Fecha
    if ($('#created_at').val() == '' || $('#created_at').val() == 0) {
        var d = new Date();
        var date = new Date(d.getFullYear(), d.getMonth(), d.getDate());
        var day = ('0' + date.getDate()).slice(-2);
        var month = ('0' + (date.getMonth() + 1)).slice(-2);
        var year = date.getFullYear()
        var init = day + '/' + month + '/' + year + ' 00:00:00';
        var end = day + '/' + month + '/' + year + ' 23:59:59';
        var aux = init + ' - ' + end;

        $('#created_at').val(aux);
    }

    $('#created_at').daterangepicker({
        'timePicker': true,
        'timePicker24Hour': true,
        'timePickerIncrement': 1,
        'format': 'DD/MM/YYYY HH:mm:ss',
        'startDate': moment().startOf('month'),
        'endDate': moment().endOf('month'),
        'opens': 'center',
        'drops': 'down',
        'ranges': {
            'Hoy': [moment().startOf('day').toDate(), moment().endOf('day').toDate()],
            'Ayer': [moment().startOf('day').subtract(1, 'days'), moment().endOf('day').subtract(1, 'days')],
            'Antes de ayer': [moment().startOf('day').subtract(2, 'days'), moment().endOf('day').subtract(2, 'days')],
            'Semana': [moment().startOf('week'), moment().endOf('week')],
            'Semana pasada': [moment().startOf('week').subtract(1, 'week'), moment().endOf('week').subtract(1, 'week')],
            'Mes': [moment().startOf('month'), moment().endOf('month')],
            'Mes pasado': [moment().startOf('month').subtract(1, 'month'), moment().endOf('month').subtract(1, 'month')],
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

    $('#created_at').attr({
        'onkeydown': 'return false'
    });

    $('#created_at').hover(function() {
        $('#created_at').attr({
            'title': 'El filtro de fecha es: ' + $('#created_at').val()
        });
    }, function() {

    });

    //-----------------------------------------------------------------------------------------------

    var url = '/get_categories/';

    var json = {
        _token: token
    };

    $.post(url, json, function(data, status) {

        var item = '<div class="row">';

        var services_options = data.response;

        for (var i = 0; i < services_options.length; i++) {
            var option = services_options[i];
            var category_id = option.category_id;
            var category_description = option.description;
            var image = option.image;

            var json = '{';
            json += '"category_id": "' + category_id + '",';
            json += '"category_description": "' + category_description + '"';
            json += '}';

            if (image == null) {
                image = '';
            }

            var sub_item = '<div class="col-md-3">';

            sub_item += "<img src='" + image + "' style='width: 100%; cursor: pointer; margin-top: 10px; margin-bottom: 10px;' onclick='through_a_service(" + json + ")'>";

            sub_item += '</div>';

            item += sub_item;
        }

        item += '</div>';

        $('#services_options').append(item);

        //console.log('data:', data);
    });

    /*var services_options = [
        'NETEL',
        'INFONET',
        'APOSTALA',
        'VISION',
        'ANTELL',
        'BILLETAJE',
        'SERVICIOS PÚBLICOS'
    ];

    for (var i = 0; i < services_options.length; i++) {
        var option = services_options[i];

        var item = '<a class="btn-sm btn btn-default" title="Mediante un servicio de ' + option + '"';
        item += 'style="border-radius: 1px; margin-bottom: 5px; width: 100%; text-align: left;" onclick="through_a_service(\'' + option + '\')">';
        item += '<i class="fa fa-exchange" style="margin-left:35px"></i>';
        item += '&nbsp; <b>' + option + '</b></a>';

        $('#services_options').append(item);
    }*/

    /*var option = 'Historial';

    var item = '<a class="btn-sm btn btn-default" title="Mediante un servicio de ' + option + '"';
    item += 'style="border-radius: 1px; margin-bottom: 5px; width: 100%; text-align: left;" onclick="modal_history()">';
    item += '<i class="fa fa-table" style="margin-left:35px"></i>';
    item += '&nbsp; Historial de la transacción</a>';

    $('#services_options').append(item);*/

    //-----------------------------------------------------------------------------------------------


    $('#button_cms_confirm').on('click', function(e) {
        e.preventDefault();

        select_service_row_option();
    });

    $('#button_iterate_level').on('click', function(e) {
        e.preventDefault();

        iterate_level();
    });

    //var span = document.createElement("div");
    //span.innerHTML = "<input type='button' value='cerrar'>";

    /*swal({
        title: "asdfasdfasdfasdf",
        content: span,
        confirmButtonText: "V redu",
        allowOutsideClick: "true"
    });

    $('body > div.sweet-alert.showSweetAlert.visible > fieldset').html('ñalksjdfñasfd');*/

    //modal_ticket_open(15342500);

    $('.select2').select2();

    window.onload = function() {

        $('#transaction_status_id').val(null).trigger('change');
        $('#transaction_status_id').empty().trigger('change');

        var option = new Option('Todos', 'Todos', false, false);
        $('#transaction_status_id').append(option);

        var transaction_status = '{!! $transaction_status !!}';
        transaction_status = JSON.parse(transaction_status);

        for (var i = 0; i < transaction_status.length; i++) {
            var item = transaction_status[i];
            var id = item.id;
            var description = item.description;
            var option = new Option(description, id, false, false);
            $('#transaction_status_id').append(option);
        }

        $('#transaction_status_id').val(null).trigger('change');
        $('#transaction_status_id').val("{{ $transaction_status_id }}").trigger('change');

        //-----------------------------------------------

        $('#service_source_id').val(null).trigger('change');
        $('#service_source_id').empty().trigger('change');

        var option = new Option('Todos', 'Todos', false, false);
        $('#service_source_id').append(option);

        var services_providers_sources = '{!! $services_providers_sources !!}';
        services_providers_sources = JSON.parse(services_providers_sources);

        for (var i = 0; i < services_providers_sources.length; i++) {
            var item = services_providers_sources[i];
            var id = item.id;
            var description = item.description;
            var option = new Option(description, id, false, false);
            $('#service_source_id').append(option);
        }

        $('#service_source_id').val(null).trigger('change');
        $('#service_source_id').val("{{ $service_source_id }}").trigger('change');

        //-----------------------------------------------

        get_services_by_brand_for_transactions("{{ $service_id }}");

        $('.select2').on('select2:select', function(e) {

            var id = e.currentTarget.id;

            var value_all_selected = 'Todos';

            switch (id) {
                case 'service_source_id':
                    get_services_by_brand_for_transactions('Todos');
                    break;
            }
        });
    };
</script>
@endsection

@section('aditional_css')
<style type="text/css">
    .modal {
        background: rgba(0, 0, 0, 0.7);
        text-align: center;
    }

    @media screen and (min-width: 768px) {
        .modal:before {
            display: inline-block;
            vertical-align: middle;
            content: " ";
            height: 100%;
        }
    }

    .modal-dialog {
        display: inline-block;
        text-align: left;
        vertical-align: middle;
        margin-bottom: 50px;
    }

    .select_index {}
</style>
@endsection