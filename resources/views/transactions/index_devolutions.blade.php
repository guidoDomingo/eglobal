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
$devolution_status = $data['lists']['devolution_status'];
$users = $data['lists']['users'];

//Valor de campos
$created_at = $data['inputs']['created_at'];
$transaction_id = $data['inputs']['transaction_id'];
$transaction_devolution_id = $data['inputs']['transaction_devolution_id'];
$amount = $data['inputs']['amount'];
$transaction_status_id = $data['inputs']['transaction_status_id'];
$service_source_id = $data['inputs']['service_source_id'];
$service_id = $data['inputs']['service_id'];

$user_id = $data['inputs']['user_id'];

//Variables para totales.

$number_of_transactions = 0;
$total_amount_of_transactions = 0;
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
            <h3 class="box-title" style="font-size: 25px;">Transacciones de Devoluciones - Reporte
            </h3>
            <div class="box-tools pull-right">
                <button class="btn btn-info" type="button" title="Buscar según los filtros en los registros." style="margin-right: 5px" id="search" name="search" onclick="search('search')">
                    <span class="fa fa-search"></span> Buscar
                </button>

                <!--<button class="btn btn-success" type="button" title="Convertir tabla en archivo excel." id="generate_x" name="generate_x" onclick="search('generate_x')">
                    <span class="fa fa-file-excel-o"></span> Exportar
                </button>-->
                <button class="btn btn-success" type="button" title="Convertir tabla en archivo excel." id="generate_x" name="generate_x" onclick="search('generate_x')">
                    <span class="fa fa-file-excel-o"></span> Exportar
                </button>
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
                    {!! Form::open(['route' => 'cms_transactions_index_devolutions', 'method' => 'POST', 'role' => 'form', 'id' => 'form_search']) !!}
                    <div class="row">

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="created_at">Buscar por Fecha:</label>
                                <input type="text" class="form-control" style="display:block" id="created_at" name="created_at" placeholder="Seleccionar fecha."></input>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="transaction_id">Buscar por Transacción ID:</label>
                                <input type="number" class="form-control" id="transaction_id" name="transaction_id" placeholder="Transacción ID"></input>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="transaction_id">Buscar por Transacción Devolución ID:</label>
                                <input type="number" class="form-control" id="transaction_devolution_id" name="transaction_devolution_id" placeholder="Transacción Devolución ID"></input>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="amount">Buscar por Monto:</label>
                                <input type="number" class="form-control" id="amount" name="amount" placeholder="Monto"></input>
                            </div>
                        </div>
                    </div>

                    <div class="row">

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
                    </div>

                    <input name="json" id="json" type="hidden">

                    {!! Form::close() !!}
                </div>
            </div>

            <div class="box box-default" style="border: 1px solid #d2d6de;">
                <div class="box-header with-border">
                    <h3 class="box-title">Resumen de totales:</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-2"></div>
                        <div class="col-md-4">
                            <div class="info-box" style="background-color: aliceblue !important; color: #444;">
                                <span class="info-box-icon"><i class="fa fa-list"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Cantidad de Transacciones</span>
                                    <span class="info-box-number" style="font-size: 30px" id="number_of_transactions">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box" style="background-color: aliceblue !important; color: #444;">
                                <span class="info-box-icon"><i class="fa fa-money"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Monto total de Transacciones</span>
                                    <span class="info-box-number" style="font-size: 30px" id="total_amount_of_transactions">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2"></div>
                    </div>
                </div>
            </div>

            <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_1">
                <thead>
                    <tr>
                        <th colspan="3" style="background: #d2d6de; text-align: center">ID de Transacción</th>
                        <th colspan="4" style="background: #d2d6de; text-align: center">Datos de Transacción</th>
                        <th colspan="1" style="background: #d2d6de; text-align: center">Datos de ONDANET</th>
                    </tr>
                    <tr>
                        <th></th>
                        <th>Principal</th>
                        <th>Devolución</th>
                        <th>Proveedor</th>
                        <th>Servicio</th>
                        <th>Monto</th>
                        <th>Fecha - Hora</th>
                        <th>Detalles</th>
                    </tr>
                </thead>
                <tbody>

                    @if (count($transactions) > 0)

                    <?php
                    $number_of_transactions = count($transactions);
                    $row_ondanet_id = 1;
                    ?>

                    @foreach ($transactions as $item)

                    <?php

                    $item_transaction_id = $item->transaction_id;

                    $item_transaction_id_view = $item->transaction_id_view;
                    $item_transaction_devolution_id_view = $item->transaction_devolution_id_view;

                    $item_status_main = $item->status_main;
                    $item_status_description_main = $item->status_description_main;

                    $item_amount_main_view = $item->amount_main_view;

                    $item_atm_description = $item->atm_description;

                    $item_status = $item->status;
                    $item_status_aux = '';
                    $item_status_class = '';
                    $item_provider = $item->provider;
                    $item_service = $item->service;
                    $item_amount = (int) str_replace('.', '', $item->amount_view);
                    $item_amount_view = $item->amount_view;
                    $item_created_at = $item->created_at;
                    $item_audit = $item->audit;
                    $item_audit_relaunch = $item->audit_relaunch;
                    $item_audit_incomes = $item->audit_incomes;

                    $item_ondanet_detail = json_decode($item->ondanet_detail, true);

                    unset($item->ondanet_detail);

                    $parameters = json_encode($item);

                    //--------------------------------------------------------------------------------------------------------------------

                    $ondanet_detail_aux = "
                        <table class='table table-bordered table-hover dataTable'>
                            <thead>
                                <th>Opciones</th>
                                <th>Código enviado</th>
                                <th>Código recibido</th>
                                <th>Respuesta</th>
                                <th>Fecha y Hora</th>
                            </thead>
                            <tbody>
                    ";

                    foreach ($item_ondanet_detail as $sub_item) {
                        $ondanet_code = $sub_item['ondanet_code'];
                        $ondanet_destination_operation_id = $sub_item['ondanet_destination_operation_id'];
                        $ondanet_response = $sub_item['ondanet_response'];
                        $ondanet_updated_at = $sub_item['ondanet_updated_at'];
                        $ondanet_request_string = $sub_item['ondanet_request_string'];

                        unset($sub_item['ondanet_request_string']);

                        $sub_item['data_ondanet_id'] = "data_ondanet_id_$row_ondanet_id";
                        $sub_item['data_ondanet_relaunch_id'] = "data_ondanet_relaunch_id_$row_ondanet_id";

                        $sub_item_aux = json_encode($sub_item);

                        $button_ondanet_relaunch = '';

                        if ($item_audit_relaunch and ($ondanet_code == '2300' or $ondanet_code == '2302')) {

                            $button_ondanet_relaunch = "
                                <button class='btn-sm btn btn-default' id = 'data_ondanet_relaunch_id_$row_ondanet_id' onclick='relaunch_code_by_change($sub_item_aux)' title='Relanzar a Ondanet.'>
                                    <span class='fa fa-undo'></span>
                                </button>
                            ";
                        }

                        // Si el registro de incomes tiene datos de auditoria el botón se reemplaza por este.
                        if ($item_audit_incomes) {

                            $button_ondanet_relaunch = "
                                <button class='btn-sm btn btn-default' title='Este registro ya fué relanzado.' disabled>
                                    <span class='fa fa-undo'></span>
                                </button>
                            ";
                        }

                        $buttons = "
                            <div class='btn-group' role='group'>
                                <button class='btn-sm btn btn-default' id = 'data_ondanet_id_$row_ondanet_id' onclick='view_ondanet($sub_item_aux)' data-ondanet-query=\"$ondanet_request_string\" title='Ver más información sobre este registro.' style='margin-right: 5px'>
                                    <span class='fa fa-list'></span>
                                </button> 

                                $button_ondanet_relaunch
                            </div>
                        ";

                        $ondanet_detail_aux .= "
                            <tr>
                                <td style='text-align: center; vertical-align: inherit;'> $buttons </td>
                                <td> $ondanet_code </td>
                                <td> $ondanet_destination_operation_id </td>
                                <td> $ondanet_response </td>
                                <td> $ondanet_updated_at </td>
                            </tr>
                        ";

                        $row_ondanet_id += 1; // Para identificar el botón útilizado
                    }

                    $ondanet_detail_aux .= "
                        </tbody>
                        </table>
                    ";

                    $total_amount_of_transactions += $item_amount;

                    ?>

                    <tr>

                        <td>Transacción: {{ $item_transaction_id_view }} <br/> Monto: {{ $item_amount_main_view }} <br/> Estado: {{ $item_status_main }} <br/> Descripción: {{ $item_status_description_main }} <br/> Terminal: {{ $item_atm_description }}</td>

                        <td style="text-align: center">

                            <div class="btn-group" role="group">
                                <button class="btn-sm btn btn-default" onclick='view_info({!! $parameters !!})' title="Ver más información sobre la devolución n°: {{ $item_transaction_devolution_id_view }}" style='margin-right: 5px'>
                                    <span class="fa fa-list"></span>
                                </button>

                                @if ($item_audit)

                                <button class="btn-sm btn btn-default" onclick='view_audit_transaction_devolution({!! $parameters !!})' title="Ver datos de auditoría">
                                    <span class="fa fa-font"></span>
                                </button>

                                @else

                                <button class="btn-sm btn btn-default" onclick='view_update_transaction_devolution({!! $parameters !!})' title="Modificar información">
                                    <span class="fa fa-edit"></span>
                                </button>

                                @endif

                            </div>

                        </td>
                        <td>{{ $item_transaction_devolution_id_view }}</td>
                        <td>{{ $item_provider }}</td>
                        <td>{{ $item_service }}</td>
                        <td>{{ $item_amount_view }}</td>
                        <td>{{ $item_created_at }}</td>
                        <td>{!! $ondanet_detail_aux !!}</td>
                    </tr>

                    @endforeach
                    @endif
                </tbody>
            </table>

        </div>
    </div>


    <!-- Mostrar la información global de la devolución -->
    <div id="modal_view_info" class="modal fade" role="dialog" data-backdrop="static" data-keyboard="false" href="#">
        <div class="modal-dialog modal-dialog-centered" role="document" style="background: white; border-radius: 5px; width: 700px;">
            <div class="modal-content" style="border-radius: 10px;">
                <div class="modal-header">
                    <!--<button type="button" class="close" data-dismiss="modal">&times;</button>-->
                    <div class="modal-title" style="font-size: 20px; text-align: center">
                        Detalles de la Devolución
                    </div>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_view_info">
                                <thead></thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-danger" onclick="close_modal_view_info()">
                        <span class="fa fa-times"></span> &nbsp; Cerrar ventana
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Mostrar la información global de ondanet -->
    <div id="modal_view_ondanet" class="modal fade" role="dialog" data-backdrop="static" data-keyboard="false" href="#">
        <div class="modal-dialog modal-dialog-centered" role="document" style="background: white; border-radius: 5px; width: 700px;">
            <div class="modal-content" style="border-radius: 10px;">
                <div class="modal-header">
                    <!--<button type="button" class="close" data-dismiss="modal">&times;</button>-->
                    <div class="modal-title" style="font-size: 20px; text-align: center">
                        Detalles de ONDANET
                    </div>
                </div>

                <div class="modal-body" id="modal_detail_ondanet">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_view_ondanet">
                                <thead></thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-danger" onclick="close_modal_view_ondanet()">
                        <span class="fa fa-times"></span> &nbsp; Cerrar ventana
                    </button>
                </div>
            </div>
        </div>
    </div>



    <!-- Modificar  -->
    <div id="modal_view_update_transaction_devolution" class="modal fade" role="dialog" data-backdrop="static" data-keyboard="false" href="#">
        <div class="modal-dialog modal-dialog-centered" role="document" style="background: white; border-radius: 5px; width: 1100px;">
            <div class="modal-content" style="border-radius: 10px;">

                <div class="modal-header">
                    <div class="modal-title" style="font-size: 20px; text-align: center" id="modal_view_update_transaction_devolution_title">
                        Modificar devolución
                    </div>
                </div>

                <div class="modal-body">

                    <div class="row">

                        <div class="col-md-4">
                            <div class="box box-default" style="border: 1px solid #d2d6de;">
                                <div class="box-header with-border">
                                    <h3 class="box-title"><b>Anterior:</b></h3>
                                </div>
                                <div class="box-body">
                                    <div class="form-group">
                                        <label for="created_at_old">Fecha y Hora:</label>
                                        <input type="text" class="form-control" id="created_at_old" name="created_at_old" disabled></input>
                                    </div>

                                    <div class="form-group">
                                        <label for="transaction_id_old">Transacción Principal:</label>
                                        <input type="number" class="form-control" id="transaction_id_old" name="transaction_id_old" disabled></input>
                                    </div>

                                    <label for="devolution_status_id_old">Estado de Devolución:</label>
                                    <div class="form-group">
                                        <select name="devolution_status_id_old" id="devolution_status_id_old" class="select2" style="width: 100%" disabled></select>
                                    </div>

                                    <label for="user_id_old">Usuario:</label>
                                    <div class="form-group">
                                        <select name="user_id_old" id="user_id_old" class="select2" style="width: 100%" disabled></select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="box box-default" style="border: 1px solid #d2d6de;">
                                <div class="box-header with-border">
                                    <h3 class="box-title"><b>Modificación:</b></h3>
                                </div>
                                <div class="box-body">
                                    <div class="form-group">
                                        <label for="created_at_new">Fecha y Hora:</label>
                                        <input type="text" class="form-control" id="created_at_new" name="created_at_new" disabled></input>
                                    </div>

                                    <div class="form-group">
                                        <label for="transaction_id_new">Transacción Principal (Modificable):</label>
                                        <input type="number" class="form-control" id="transaction_id_new" name="transaction_id_new" placeholder="Nuevo ID de transacción" disabled></input>
                                    </div>

                                    <label for="devolution_status_id_new">Estado de Devolución (Modificable):</label>
                                    <div class="form-group">
                                        <select name="devolution_status_id_new" id="devolution_status_id_new" class="select2" style="width: 100%" disabled></select>
                                    </div>

                                    <label for="user_id_new">Usuario:</label>
                                    <div class="form-group">
                                        <select name="user_id_new" id="user_id_new" class="select2" style="width: 100%" disabled></select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="transaction_devolution_id_aux">Transacción Devolución:</label>
                                        <input type="number" class="form-control" id="transaction_devolution_id_aux" name="transaction_devolution_id_aux" disabled></input>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <label for="comment_new">Comentario (Modificable):</label>
                                    <textarea class="form-control" placeholder="Ingresar un comentario del cambio realizado." id="comment_new" name="comment_new" rows="4" style="resize:none; width: 100%; max-width: 100%" disabled></textarea>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>

                <div class="modal-footer">

                    <button class="btn btn-info" onclick="update_transaction_devolution()" id="button_save">
                        <span class="fa fa-save"></span> &nbsp; Guardar
                    </button>

                    <button class="btn btn-danger" onclick="close_modal_update_transaction_devolution()" id="button_close">
                        <span class="fa fa-times"></span> &nbsp; Cerrar
                    </button>

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
    $('.select2').select2();

    var transaction_devolution_id_aux = null;

    var relaunch_running = false;

    function close_modal_view_ondanet() {
        $("#modal_view_ondanet").modal('hide');
    }

    function close_modal_view_info() {
        $("#modal_view_info").modal('hide');
    }

    function close_modal_update_transaction_devolution() {
        $("#modal_view_update_transaction_devolution").modal('hide');
    }

    function view_ondanet(parameters) {

        console.log('parameters:', parameters);

        var ondanet_code = parameters.ondanet_code;
        var ondanet_destination_operation_id = parameters.ondanet_destination_operation_id;
        var ondanet_response = parameters.ondanet_response;
        var ondanet_updated_at = parameters.ondanet_updated_at;
        var data_ondanet_id = parameters.data_ondanet_id;
        var ondanet_request_string = $('#' + data_ondanet_id).attr('data-ondanet-query');

        var inputs_and_values = {
            'Código enviado': ondanet_code,
            'Código recibido': ondanet_destination_operation_id,
            'Respuesta': ondanet_response,
            'Fecha y Hora de envío': ondanet_updated_at
        };

        var datatable_id = '#datatable_view_ondanet';

        /*if ($.fn.DataTable.isDataTable(datatable_id)) {
            $(datatable_id).DataTable().destroy();
        }*/

        $(datatable_id + ' thead').empty();
        $(datatable_id + ' tbody').empty();

        var thead = '<tr>';
        thead += '<th>Dato</td>';
        thead += '<th>Valor</td>';
        thead += '</tr>';

        var tbody = '';

        for (var key in inputs_and_values) {
            console.log(key + ':' + inputs_and_values[key]);

            var tr = '<tr>';
            tr += '<td><b>' + key + ':</b></td>';
            tr += '<td>' + inputs_and_values[key] + '</td>';
            tr += '</tr>';

            tbody += tr;
        }

        tbody += '<tr style="text-align: center;"><td colspan="2"><b>Cadena envíada:</b></td></tr>';
        tbody += '<tr style="text-align: center;"><td colspan="2">' + ondanet_request_string + '</td></tr>';

        $(datatable_id + ' thead').append(thead);
        $(datatable_id + ' tbody').append(tbody);

        //Datatable config
        /*var data_table_config = {
            //custom
            orderCellsTop: false,
            fixedHeader: true,
            pageLength: 30,
            lengthMenu: [
                1, 2, 5, 10, 20, 30, 50, 70, 100, 150, 300, 500, 1000, 1500, 2000
            ],
            dom: '<"pull-left"f><"pull-right"l>tip',
            language: {
                "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
            },
            scroller: true,
            processing: true,
            ordering: false
        }

        $(datatable_id).DataTable(data_table_config);*/

        $("#modal_view_ondanet").modal();
    }

    function relaunch_code_by_change(parameters) {

        console.log('parameters de ondanet a relanzar por cambio:', parameters);

        var data_ondanet_relaunch_id = parameters.data_ondanet_relaunch_id;
        $('#' + data_ondanet_relaunch_id).html('<span class="fa fa-spin fa-cog"></span>');

        var transaction_devolution_id = parameters.transaction_devolution_id;
        var income_id = parameters.income_id;
        var transaction_id_old = parameters.transaction_id_old;
        var transaction_id_new = parameters.transaction_id_new;

        var enabled = false;

        @if(\Sentinel::getUser()->hasAccess('cms_ondanet_relaunch_code_ondanet'))
        enabled = true;
        @endif

        if (enabled) {

            if (relaunch_running == false) {

                swal({
                        title: 'Atención',
                        text: 'Este registro se volverá a relanzar a ONDANET, quiere continuar?',
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

                            relaunch_running = true;

                            var url = '/relaunch_code_by_change/';

                            var json = {
                                _token: token,
                                transaction_devolution_id: transaction_devolution_id,
                                income_id: income_id,
                                transaction_id_old: transaction_id_old,
                                transaction_id_new: transaction_id_new
                            };

                            $.post(url, json, function(data, status) {

                                $('#' + data_ondanet_relaunch_id).html('<span class="fa fa-undo"></span>');

                                var error = data.error;
                                var message = data.message;
                                var type = 'success';

                                if (error) {
                                    type = 'error';
                                }

                                swal({
                                        title: 'Atención',
                                        text: message,
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
                                            document.location.href = 'cms_transactions_index_devolutions';
                                        }
                                    }
                                );
                            });

                        }
                    }
                );

            } else {
                swal('Atención', 'Se está relanzando un registro ahora mismo.', 'info');
            }

        } else {
            $('#' + data_ondanet_relaunch_id).html('<span class="fa fa-undo"></span>');

            swal('Atención', 'Este usuario no tiene permiso para relanzar a ONDANET.', 'info');
        }

    }

    function view_info(parameters) {

        var item_transaction_id = parameters.transaction_id;
        var item_transaction_devolution_id = parameters.transaction_devolution_id;

        var item_service_source_id = parameters.service_source_id;
        var item_service_id = parameters.service_id;

        var item_status = parameters.status;
        var item_status_description = parameters.status_description;

        var item_provider = parameters.provider;
        var item_service = parameters.service;
        var item_amount_view = parameters.amount_view;
        var item_created_at = parameters.created_at;

        var item_devolution_amount = parameters.devolution_amount;
        var item_devolution_reason = parameters.devolution_reason;
        var item_devolution_type = parameters.devolution_type;
        var item_devolution_status = parameters.devolution_status;

        var item_ajustement = parameters.ajustement;
        var item_ajustement_reason = parameters.ajustement_reason;
        var item_ajustement_amount = parameters.ajustement_amount;
        var item_ajustement_percentage = parameters.ajustement_percentage;
        var item_user_description = parameters.user_description;
        var item_comment = parameters.comment;

        console.log('parameters:', parameters);

        item_transaction_id = $.number(item_transaction_id, 0, ',', '.');
        item_transaction_devolution_id = $.number(item_transaction_devolution_id, 0, ',', '.');

        item_devolution_amount = $.number(item_devolution_amount, 0, ',', '.');
        item_ajustement_amount = $.number(item_ajustement_amount, 0, ',', '.');

        var inputs_and_values = {
            'Transacción': item_transaction_id,
            'Devolución': item_transaction_devolution_id,
            'Estado de transacción': item_status,
            'Estado - Descripción de la transacción': item_status_description,
            'Proveedor': item_provider,
            'Servicio': item_service,
            'Monto de transacción': item_amount_view,
            'Fecha - Hora': item_created_at,

            'Monto devolución': item_devolution_amount,
            'Motivo': item_devolution_reason,
            'Tipo': item_devolution_type,
            'Estado': item_devolution_status,

            'Ajuste': item_ajustement,
            'Monto de ajuste': item_ajustement_amount,
            'Porcentaje de ajuste': item_ajustement_percentage,
            'Motivo de ajuste': item_ajustement_reason,

            'Nombre del Usuario': item_user_description,
            'Comentario': item_comment
        };

        var datatable_id = '#datatable_view_info';

        if ($.fn.DataTable.isDataTable(datatable_id)) {
            $(datatable_id).DataTable().destroy();
        }

        $(datatable_id + ' thead').empty();
        $(datatable_id + ' tbody').empty();

        var thead = '<tr>';
        thead += '<th>Datos</td>';
        thead += '</tr>';

        var tbody = '';

        for (var key in inputs_and_values) {
            console.log(key + ':' + inputs_and_values[key]);

            var key_aux = '';

            if (key == 'Monto devolución') {
                key_aux = 'Datos de Devolución';
            } else if (key == 'Ajuste') {
                key_aux = 'Datos de Ajuste';
            } else if (key == 'Nombre del Usuario') {
                key_aux = 'Datos de Usuario';
            }

            if (key_aux !== '') {
                var tr = '<tr>';
                tr += '<td style="text-align: center; font-weight: bold;">' + key_aux + '</td>';
                tr += '</tr>';

                tbody += tr;
            }

            var tr = '<tr>';
            tr += '<td><b>' + key + ':</b> ' + inputs_and_values[key] + '</td>';
            tr += '</tr>';

            tbody += tr;
        }

        $(datatable_id + ' thead').append(thead);
        $(datatable_id + ' tbody').append(tbody);

        //Datatable config
        var data_table_config = {
            //custom
            orderCellsTop: false,
            fixedHeader: true,
            pageLength: 30,
            lengthMenu: [
                1, 2, 5, 10, 20, 30, 50, 70, 100, 150, 300, 500, 1000, 1500, 2000
            ],
            dom: '<"pull-left"f><"pull-right"l>tip',
            language: {
                "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
            },
            scroller: true,
            processing: true,
            ordering: false
        }

        $(datatable_id).DataTable(data_table_config);


        $("#modal_view_info").modal();
    }

    function view_update_transaction_devolution(parameters) {

        var enabled = false;

        @if(\Sentinel::getUser()->hasAccess('cms_transactions_devolution_update'))
        enabled = true;
        @endif

        if (enabled) {

            $('#modal_view_update_transaction_devolution_title').html('Modificar devolución:');

            transaction_devolution_id_aux = parameters.transaction_devolution_id; // Parámetro global

            var transaction_id = parameters.transaction_id;
            var devolution_status_id = parameters.devolution_status_id;
            var created_at_old = parameters.created_at;
            var user_id_old = parameters.user_id;
            var user_id_new = "{{ $user_id }}";

            console.log('user_id_new', user_id_new);
            console.log('parameters', parameters);

            $('#transaction_devolution_id_aux').val(null);
            $('#transaction_devolution_id_aux').val(transaction_devolution_id_aux);

            $('#created_at_old').val(null);
            $('#created_at_old').val(created_at_old);

            $('#created_at_new').val(null);
            $('#created_at_new').attr('placeholder', 'En este momento.');

            $('[for="comment_new"]').html('Comentario (Agregar):');
            $('#comment_new').val(null);
            $('#comment_new').prop('disabled', false);

            $('#transaction_id_old').val(null);
            $('#transaction_id_old').val(transaction_id);

            $('[for="transaction_id_new"]').html('Transacción Principal (Modificable):');
            $('#transaction_id_new').val(null);
            $('#transaction_id_new').prop('disabled', false);
            $('#transaction_id_new').attr('placeholder', 'Nuevo ID de transacción');

            $('#devolution_status_id_old').select2('enable', true);
            $('#devolution_status_id_old').val(null).trigger('change');
            $('#devolution_status_id_old').val(devolution_status_id).trigger('change');
            $('#devolution_status_id_old').select2('enable', false);

            $('[for="devolution_status_id_new"]').html('Estado de Devolución (Modificable):');

            $('#devolution_status_id_new').select2('enable', true);
            $('#devolution_status_id_new').val(null).trigger('change');


            $('#user_id_old').select2('enable', true);
            $('#user_id_old').val(null).trigger('change');
            $('#user_id_old').val(user_id_old).trigger('change');
            $('#user_id_old').select2('enable', false);

            $('#user_id_new').select2('enable', true);
            $('#user_id_new').val(null).trigger('change');
            $('#user_id_new').val(user_id_new).trigger('change');
            $('#user_id_new').select2('enable', false);

            $("#button_save").css('display', 'inline-table');

            $("#modal_view_update_transaction_devolution").modal();

        } else {
            swal('Atención', 'Este usuario no tiene permiso para modificar devoluciones.', 'info');
        }

    }

    function view_audit_transaction_devolution(parameters) {

        $('#modal_view_update_transaction_devolution_title').html('Datos de Auditoría:');

        var transaction_id = parameters.transaction_id;
        var transaction_devolution_id_aux = parameters.transaction_devolution_id;
        var created_at_old = parameters.created_at;
        var user_id_old = parameters.user_id;
        var audit_detail = JSON.parse(parameters.audit_detail)[0];

        console.log('parameters:', parameters);
        console.log('audit_detail:', audit_detail);

        var created_at_new = audit_detail.created_at_new;
        var user_id_new = audit_detail.user_id_new;
        var comment_new = audit_detail.comment_new;


        var transaction_id_old = audit_detail.transaction_id_old;

        if (transaction_id_old == null) {
            transaction_id_old = parameters.transaction_id_old;
        }

        var devolution_status_id_old = audit_detail.devolution_status_id_old;

        if (devolution_status_id_old == null) {
            devolution_status_id_old = parameters.devolution_status_id;
        }

        var transaction_id_new = audit_detail.transaction_id_new;
        var devolution_status_id_new = audit_detail.devolution_status_id_new;

        $('#created_at_old').val(null);
        $('#created_at_old').val(created_at_old);

        $('#created_at_new').val(null);
        $('#created_at_new').val(created_at_new);

        $('[for="comment_new"]').html('Comentario (Agregado):');
        $('#comment_new').val(null);
        $('#comment_new').val(comment_new);
        $('#comment_new').prop('disabled', true);

        $('#transaction_devolution_id_aux').val(null);
        $('#transaction_devolution_id_aux').val(transaction_devolution_id_aux);

        $('#transaction_id_old').val(null);
        $('#transaction_id_old').val(transaction_id_old);

        if (transaction_id_new == null) {
            $('#transaction_id_new').attr('placeholder', 'Sin cambios.');
            $('[for="transaction_id_new"]').html('Transacción Principal (Sin cambios):');
        } else {
            $('[for="transaction_id_new"]').html('Transacción Principal (Modificado):');
        }

        $('#transaction_id_new').val(null);
        $('#transaction_id_new').val(transaction_id_new);
        $('#transaction_id_new').prop('disabled', true);

        $('#devolution_status_id_old').select2('enable', true);
        $('#devolution_status_id_old').val(null).trigger('change');
        $('#devolution_status_id_old').val(devolution_status_id_old).trigger('change');
        $('#devolution_status_id_old').select2('enable', false);

        if (devolution_status_id_new == null) {
            $('#devolution_status_id_new').attr('placeholder', 'Sin cambios.');
            $('[for="devolution_status_id_new"]').html('Estado de Devolución (Sin cambios):');
        } else {
            $('[for="devolution_status_id_new"]').html('Estado de Devolución (Modificado):');
        }

        $('#devolution_status_id_new').select2('enable', true);
        $('#devolution_status_id_new').val(null).trigger('change');
        $('#devolution_status_id_new').val(devolution_status_id_new).trigger('change');
        $('#devolution_status_id_new').select2('enable', false);

        $('#user_id_old').select2('enable', true);
        $('#user_id_old').val(null).trigger('change');
        $('#user_id_old').val(user_id_old).trigger('change');
        $('#user_id_old').select2('enable', false);

        $('#user_id_new').select2('enable', true);
        $('#user_id_new').val(null).trigger('change');
        $('#user_id_new').val(user_id_new).trigger('change');
        $('#user_id_new').select2('enable', false);

        $("#button_save").css('display', 'none');

        $("#modal_view_update_transaction_devolution").modal();
    }

    function update_transaction_devolution() {

        $("#modal_load").modal();

        var transaction_id_old = $('#transaction_id_old').val();
        var transaction_id_new = $('#transaction_id_new').val();

        var devolution_status_id_old = $('#devolution_status_id_old :selected').val();
        var devolution_status_id_new = $('#devolution_status_id_new :selected').val();

        var comment_new = $('#comment_new').val();


        var url = '/update_transaction_devolution/';

        var json = {
            _token: token,

            transaction_devolution_id: transaction_devolution_id_aux,

            transaction_id_old: transaction_id_old,
            transaction_id_new: transaction_id_new,

            devolution_status_id_old: devolution_status_id_old,
            devolution_status_id_new: devolution_status_id_new,

            comment_new: comment_new
        };

        $.post(url, json, function(data, status) {

            $("#modal_load").modal('hide');

            var error = data.error;
            var message = data.message;
            var type = 'success';

            if (error) {
                type = 'error';
            }

            //swal('Atención', message, type);

            swal({
                    title: 'Atención',
                    text: message,
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
                            document.location.href = 'cms_transactions_index_devolutions';
                        }
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

    var groupColumn = 0;

    var table = $('#datatable_1').DataTable({
        orderCellsTop: true,
        fixedHeader: true,
        pageLength: 10,
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
                .each(function(transaction, i) {

                    //transaction = $.number(transaction, 0, ',', '.');

                    if (last !== transaction) {

                        var color = '#d2d6de';

                        var td = $('<td>');
                        td.attr({
                            'colspan': '11',
                            'style': 'color: #333 !important'
                        }).append(transaction);

                        var tr = $('<tr>');
                        tr.attr({
                            'class': 'group',
                            'style': 'background-color:' + color + ' !important; font-weight: bold; cursor: pointer'
                        }).append(td);

                        $(rows).eq(i).before(tr);

                        last = transaction;
                    }
                });
        },
        initComplete: function(settings, json) {
            $('#div_load').css('display', 'none');
            $('#content').css('display', 'block');
        }
    });

    // Order by the grouping
    $('#datatable_1 tbody').on('click', 'tr.group', function() {
        var currentOrder = table.order()[0];
        if (currentOrder[0] === groupColumn && currentOrder[1] === 'asc') {
            table.order([groupColumn, 'desc']).draw();
        } else {
            table.order([groupColumn, 'asc']).draw();
        }
    });

    //-----------------------------------------------------------------------------------------------


    $('#created_at').val("{{ $created_at }}");
    $('#transaction_id').val("{{ $transaction_id }}");
    $('#transaction_devolution_id').val("{{ $transaction_devolution_id }}");
    $('#amount').val("{{ $amount }}");

    var number_of_transactions = $.number("{{ $number_of_transactions }}", 0, ',', '.');
    var total_amount_of_transactions = $.number("{{ $total_amount_of_transactions }}", 0, ',', '.');

    $('#number_of_transactions').html(number_of_transactions);
    $('#total_amount_of_transactions').html(total_amount_of_transactions);

    var json = {!!$json!!};
    json = JSON.stringify(json);
    $('#json').val(json);

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

    $('#created_at').attr({
        'onkeydown': 'return false'
    });

    $('#created_at').hover(function() {
        $('#created_at').attr({
            'title': 'El filtro de fecha es: ' + $('#created_at').val()
        });
    }, function() {

    });

    window.onload = function() {

        var devolution_status = '{!! $devolution_status !!}';
        devolution_status = JSON.parse(devolution_status);

        for (var i = 0; i < devolution_status.length; i++) {
            var item = devolution_status[i];
            var id = item.id;
            var description = item.description;
            var option1 = new Option(description, id, false, false);
            var option2 = new Option(description, id, false, false);
            $('#devolution_status_id_old').append(option1);
            $('#devolution_status_id_new').append(option2);
        }

        $('#devolution_status_id_old').val(null).trigger('change');
        $('#devolution_status_id_new').val(null).trigger('change');

        var users = '{!! $users !!}';
        users = JSON.parse(users);

        for (var i = 0; i < users.length; i++) {
            var item = users[i];
            var id = item.id;
            var description = item.description;
            var option3 = new Option(description, id, false, false);
            var option4 = new Option(description, id, false, false);
            $('#user_id_old').append(option3);
            $('#user_id_new').append(option4);
        }

        $('#user_id_old').val(null).trigger('change');
        $('#user_id_new').val(null).trigger('change');

        //-----------------------------------------------

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