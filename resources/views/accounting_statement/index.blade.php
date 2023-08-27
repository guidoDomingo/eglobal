@extends('layout')

@section('title')
Estado Contable Unificado - Reporte
@endsection
@section('content')

<?php

//Variable que se usa en todo el documento 
$message = $data['message'];
$records = $data['lists']['records'];

//Combos
$owners = $data['lists']['owners'];
$groups = $data['lists']['groups'];
$atms = $data['lists']['atms'];
$block_types = $data['lists']['block_types'];
$managers = $data['lists']['managers'];

//Inputs

$user_id = $data['inputs']['user_id'];
$timestamp = $data['inputs']['timestamp'];
$summary_to_date = $data['inputs']['summary_to_date'];
$summary_closing = $data['inputs']['summary_closing'];

$owner_id = $data['inputs']['owner_id'];
$group_id = $data['inputs']['group_id'];
$atm_id = $data['inputs']['atm_id'];
$manager_id = $data['inputs']['manager_id'];

$totals_list = $data['totals'];
?>

<section class="content-header">

    <div class="row">
        <div class="col-md-12">
            @include('partials._flashes')
        </div>
    </div>
</section>

<section class="content">

    <style>
        /** Para el auto incremento de la altura en el combo múltiple */
        .select2-selection--multiple {
            overflow: hidden !important;
            height: auto !important;
        }

        /** Color del item seleccionado */
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #285f6c !important;
        }

        textarea {
            max-width: 432px;
            /* Limita el ancho máximo a 400px */
            max-height: 150px;
            /* Limita la altura máxima a 200px */
        }
    </style>


    <div class="box box-default" style="border-radius: 5px; margin-bottom: 2000px; color: #285f6c;" id="div_load">
        <div class="box-header with-border">
            <h3 class="box-title" style="font-size: 25px;">Cargando...
            </h3>
        </div>

        <div class="box-body">
            <div style="text-align: center; font-size: 20px;">
                <div>
                    <i class="fa fa-spin fa-refresh fa-2x" style="vertical-align: sub;"></i> &nbsp;
                    <label id="label_load">Cargando datos...</label>
                </div>
            </div>
        </div>
    </div>

    <div class="box box-default" style="border-radius: 5px; display: none" id="content">
        <div class="box-header with-border">
            <h3 class="box-title" style="font-size: 25px;">Estado Contable Unificado - Reporte</h3>
            <div class="box-tools pull-right">
                <button class="btn btn-default" type="button" title="Buscar según los filtros en los registros." style="background-color: #285f6c; color:white; margin-right: 5px;" id="search" name="search" onclick="search('search')">
                    <span class="fa fa-search"></span> Buscar
                </button>

                <button class="btn btn-default" type="button" title="Convertir tabla en archivo excel." style="background-color: #285f6c; color:white; margin-right: 5px;" id="generate_x" name="generate_x" onclick="search('generate_x')">
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
                    {!! Form::open(['route' => 'accounting_statement', 'method' => 'POST', 'role' => 'form', 'id' => 'form_search']) !!}
                    <div class="row">
                        <div class="col-md-5">

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="amount">Buscar por Fecha y Hora</label>
                                        <div class="input-group" style="border: 1px solid #285f6c;">

                                            <div class="input-group-addon" style="background-color: #285f6c; border: 1px solid #285f6c; color:white;">
                                                <i class="fa fa-calendar fa-2x"></i>
                                            </div>

                                            <input type="text" class="form-control" id="timestamp" name="timestamp" placeholder="Seleccionar fecha." style="display:block; height: 50px; border: 0 !important; font-size: 15px; font-weight: bold; text-align: center; margin-bottom: 5px;"></input>

                                            <br />
                                            <div style="padding: 5px; text-align: center; margin-top: 5px">
                                                Resumen al dia de hoy &nbsp; <input type="checkbox" id="summary_to_date" name="summary_to_date"></input> &nbsp;
                                                Resumen al cierre &nbsp; <input type="checkbox" id="summary_closing" name="summary_closing"></input>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-7">

                            <div class="row">
                                <div class="col-md-12">
                                    <label for="owner_id">Buscar por Red:</label>
                                    <div class="form-group">
                                        <select name="owner_id" id="owner_id" class="select2" style="width: 100%"></select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <label for="group_id">Buscar por Grupo:</label>
                                    <div class="form-group">
                                        <select name="group_id" id="group_id" class="select2" style="width: 100%"></select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <label for="atm_id">Buscar por Terminal:</label>
                                    <div class="form-group">
                                        <select name="atm_id" id="atm_id" class="select2" style="width: 100%"></select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <label for="manager_id">Buscar por Manager:</label>
                                    <div class="form-group">
                                        <select name="manager_id" id="manager_id" class="select2" style="width: 100%"></select>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <input name="json" id="json" type="hidden">

                    {!! Form::close() !!}
                </div>
            </div>

            @if(count($records) > 0)
            <div class="box box-default" style="border: 1px solid #d2d6de;" id="div_summary_of_totals">
                <div class="box-header with-border">
                    <h3 class="box-title">Resumen:</h3>
                </div>
                <div class="box-body">
                    <div class="row">

                        @foreach($totals_list as $key => $value)

                        <?php

                        $key_display = 'block';
                        $value_color = '#333';

                        if ($value == null) {
                            $key_display = 'none';
                        }

                        if ($key == 'SALDO' and count($records) == 1) {

                            //$value .= '<span class="info-box-text"> asdfasdfasdf</span>';

                            $value_aux = (int) str_replace('.', '', $value);

                            if ($value_aux <= 0) {
                                $value_color = '#00a65a';
                            } else {
                                $value_color = '#dd4b39';
                            }
                        } else if ($key == 'ESTADO') {
                            if ($value == 'Activo') {
                                $value_color = 'success';
                            } else if (strpos($value, 'Bloqueado') !== false) {
                                $value_color = 'danger';
                            } else if ($value == 'Inactivo') {
                                $value_color = 'warning';
                            }
                        }

                        ?>

                        <div class="col-md-4" style="display: {{ $key_display }}">
                            <div class="box box-default" style="border: 1px solid #d2d6de; text-align: center;">
                                <div class="box-body">
                                    <span class="info-box-text">{{ $key }}</span>

                                    @if($key == 'ESTADO')
                                    <span class="info-box-number">
                                        <div class="label label-{{ $value_color }}" style="font-size: 15px; display: block; margin-top: 12px">{{ $value }}</div>
                                    </span>
                                    @else
                                    <span class="info-box-number" style="font-size: 25px; color: {{ $value_color }}">{{ $value }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach

                    </div>
                </div>
            </div>
            @endif



            @if(count($records) > 1)
            <div class="row">
                <div class="col-md-12">

                    <div class="box box-default" style="border: 1px solid #d2d6de;" id="div_summary_of_totals">
                        <div class="box-header with-border" style="text-align: center">
                            <h3 class="box-title">Mostrar - Ocultar columnas del detalle / Filtrar filas del detalle</h3>
                        </div>
                        <div class="box-body">

                            <div class="row">
                                <div class="col-md-8">
                                    <table class="table table-bordered dataTable" role="grid">
                                        <thead style="background-color: #285f6c; border: 1px solid #285f6c; color: white;">
                                            <tr>
                                                <th style="width: 100px;">Mostrar Columnas</th>
                                                <th>Filtrar por Multa</th>
                                                <th>Filtrar por Tipo de Deudas</th>
                                                <th>Filtrar por Estados</th>
                                                <th>Filtrar Regla</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td>
                                                    <input type="checkbox" id="check_balance_and_status" name="check_balance_and_status"></input> &nbsp; Saldo y Estado
                                                </td>

                                                <td>
                                                    <input type="checkbox" id="check_group_with_fine" name="check_group_with_fine"></input> &nbsp; Grupos con Multa
                                                </td>

                                                <td>
                                                    <input type="checkbox" id="check_balance_positive" name="check_balance_positive"></input> &nbsp; Deuda
                                                </td>

                                                <td>
                                                    <input type="checkbox" id="check_filter_actives" name="check_filter_actives"></input> &nbsp; <span class="label label-success">Activos</span>
                                                </td>

                                                <td>
                                                    <input type="checkbox" id="check_group_with_rule" name="check_group_with_rule"></input> &nbsp; Grupos con Regla
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>
                                                    <input type="checkbox" id="check_totals" name="check_totals"></input> &nbsp; Totales
                                                </td>

                                                <td>
                                                    <input type="checkbox" id="check_group_without_fine" name="check_group_without_fine"></input> &nbsp; Grupos sin Multa
                                                </td>

                                                <td>
                                                    <input type="checkbox" id="check_balance_negative" name="check_balance_negative"></input> &nbsp; Saldo a favor
                                                </td>

                                                <td>
                                                    <input type="checkbox" id="check_filter_blockeds" name="check_filter_blockeds"></input> &nbsp; <span class="label label-danger">Bloqueados</span>
                                                </td>

                                                <td>
                                                    <input type="checkbox" id="check_group_no_rule" name="check_group_no_rule"></input> &nbsp; Grupos sin Regla
                                                </td>
                                            </tr>

                                            <tr>
                                                <td></td>
                                                <td></td>

                                                <td>
                                                    <input type="checkbox" id="check_balance_cero" name="check_balance_cero"></input> &nbsp; Saldo cero
                                                </td>

                                                <td>
                                                    <input type="checkbox" id="check_filter_inactives" name="check_filter_inactives"></input> &nbsp; <span class="label label-warning">Inactivos</span>
                                                </td>

                                                <td>
                                                    <input type="checkbox" id="check_atm_with_rule" name="check_atm_with_rule" title="Grupos que tienen terminales con Regla"></input> &nbsp; Terminales con Regla
                                                </td>
                                            </tr>

                                            <tr>
                                                <td></td>
                                                <td></td>
                                                <td></td>

                                                <td>
                                                    <input type="checkbox" id="check_filter_no_status" name="check_filter_no_status"></input> &nbsp; <span class="label label-info">Sin estado</span>
                                                </td>

                                                <td>
                                                    <input type="checkbox" id="check_atm_no_rule" name="check_atm_no_rule" title="Grupos que tienen terminales sin Regla"></input> &nbsp; Terminales sin Regla
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <div class="col-md-4">

                                    <table class="table table-bordered dataTable" role="grid">
                                        <thead style="background-color: #285f6c; border: 1px solid #285f6c; color: white; text-align: right;">
                                            <tr>
                                                <th>Total por Deudas</th>
                                                <th>Total por Multa</th>
                                            </tr>
                                        </thead>
                                        <tbody style="text-align: right;">
                                            <tr>
                                                <td>
                                                    <b id="num_filas_mayor_cero"></b>
                                                </td>

                                                <td>
                                                    <b id="num_filas_con_multa"></b>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>
                                                    <b id="num_filas_menor_cero"></b>
                                                </td>

                                                <td>
                                                    <b id="num_filas_sin_multa"></b>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>
                                                    <b id="num_filas_igual_cero"></b>
                                                </td>

                                                <td>

                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    <table class="table table-bordered dataTable" role="grid">
                                        <thead style="background-color: #285f6c; border: 1px solid #285f6c; color: white; text-align: right;">
                                            <tr>
                                                <th>Total por Estados</th>
                                                <th>Total por Regla</th>
                                            </tr>
                                        </thead>
                                        <tbody style="text-align: right;">

                                            <tr>
                                                <td>
                                                    <b id="actives_count"></b>
                                                </td>

                                                <td>
                                                    <b id="num_filas_grupos_con_regla"></b>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>
                                                    <b id="blocks_count"></b>
                                                </td>

                                                <td>
                                                    <b id="num_filas_grupos_sin_regla"></b>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>
                                                    <b id="inactives_count"></b>
                                                </td>

                                                <td>
                                                    <b id="num_terminales_con_regla"></b>
                                                </td>
                                            </tr>

                                            <tr>
                                                <td>
                                                    <b id="no_status_count"></b>
                                                </td>

                                                <td>
                                                    <b id="num_terminales_sin_regla"></b>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>


                        </div>
                    </div>

                </div>
            </div>

            @endif

            @if(count($records) > 0)

            <div class="box box-default" style="border: 1px solid #d2d6de;" id="div_datatable_1">
                <div class="box-body" style="overflow-x: scroll;">
                    <table class="table table-bordered table-hover table-responsive" role="grid" id="datatable_1">
                        <thead style="background-color: #285f6c; border: 1px solid #285f6c; color: white;" id="datatable_1_thead"></thead>
                        <tbody id="datatable_1_tbody"></tbody>
                    </table>
                </div>
            </div>

            @endif

        </div>
    </div>

    <!-- Modal  -->
    <div id="modal_atm_block_type_change" class="modal fade in" role="dialog" data-backdrop="static" data-keyboard="false" href="#" style="display: block; padding-right: 17px; display: none" aria-hidden="false">
        <div class="modal-dialog" role="document" style="background: white; border-radius: 5px; width: 500px;">
            <div class="modal-content" style="border-radius: 10px">
                <div class="modal-header">
                    <div class="modal-title" style="font-size: 20px; text-align: center">
                        Cambiar el Block-Type:
                    </div>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <!--<label for="block_type_id">Block Type:</label>-->
                            <div class="form-group">
                                <input type="text" class="form-control" id="block_type_id" name="block_type_id" placeholder="Cambiar el Block-Type"></input>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <!--<label for="commentary">Agregar un comentario:</label>-->

                            <div class="input-group">
                                <span class="input-group-addon">
                                    <b>
                                        <i class="fa fa-pencil"></i>
                                    </b>
                                </span>

                                <textarea rows="4" cols="30" class="form-control" id="commentary" name="commentary" placeholder="Agregar un comentario" value=""></textarea>
                            </div>
                        </div>
                    </div>

                    <br />

                    <div class="row">
                        <div class="col-md-12" style="font-size: 12px">
                            <label style="color: #dd4b39">Se hará una inserción en <b>historial_bloqueos</b> por cada terminal del grupo </label>
                        </div>
                    </div>

                    <br />

                    <div class="row">
                        <div class="col-md-12" style="text-align: right">
                            <div class="btn-group">

                                <button class="btn btn-success" title="Modificar el Block-Type" style="margin-right: 5px" id="save_block_type">
                                    <i class="fa fa-save"></i> Guardar
                                </button>

                                <button class="btn btn-danger" title="Cerrar ventana" data-dismiss="modal"><i class="fa fa-times"></i>
                                    Cerrar</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
<script src="/bower_components/admin-lte/plugins/iCheck/icheck.min.js"></script>

<!-- jQuery Number Format -->
<script src="/js/number_format/jquery.number.js"></script>

<!-- Iniciar objetos -->
<script type="text/javascript">
    var global_owner_id = "{{$owner_id}}"; // Para filtro
    var global_group_id = null; // Para modificación
    var global_user_id = "{{$user_id}}"; // Para modificación

    var table = null; // Variable de datatable.

    //-----------------------------------------------------------------------------------------------

    var data_aux = <?php echo json_encode($data); ?>;
    data_aux = JSON.stringify(data_aux);
    $('#json').val(data_aux);
    //console.log('JSON data_aux:', $('#json').val());

    //-----------------------------------------------------------------------------------------------

    function remove_row_shown() {
        for (var i = 0; i < count; i++) {
            var item = json[i];
            var fila_id = item.group_id;

            var row = table.row('#' + fila_id);

            if (row.child.isShown()) {
                row.child.hide();
                $('#' + fila_id).removeClass('shown');
            }
        }
    }

    // Cerrar la sub tabla
    function close_table(group_id) {
        var row = table.row('#row_' + group_id);

        if (row.child.isShown()) {
            row.child.hide();
            $('#row_' + group_id).removeClass('shown');
        }
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

            $('#label_load').html('Cargando...');
            $('#content').css('display', 'none');

            $('#div_load').css({
                'display': 'block',
                'margin-bottom': '2000px',
                'color': 'black'
            });
        }

        $('#form_search').append(input);
        $('#form_search').submit();
    }

    //-----------------------------------------------------------------------------------------------

    function save_atm_block_type_change() {

        var block_type_id = $('#block_type_id').val();
        var commentary = $('#commentary').val();

        console.log(block_type_id, 'comentario:', commentary);

        if (commentary !== '') {

            $('.sweet-alert button.cancel').css('background', '#dd4b39');

            swal({
                    title: 'Atención',
                    text: 'El block-type de cada terminal de este grupo será modificado, Continuar?',
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#0073b7',
                    confirmButtonText: 'Aceptar',
                    cancelButtonText: 'Cancelar',
                    closeOnClickOutside: false,
                    showLoaderOnConfirm: false
                },
                function(isConfirmMessage) {

                    if (isConfirmMessage) {

                        var url = '/get_details_per_group/';
                        var case_ = 'block_type';

                        var json = {
                            _token: token,
                            case: case_,
                            user_id: global_user_id,
                            group_id: global_group_id,
                            block_type_id: block_type_id,
                            commentary: commentary
                        };

                        $.post(url, json, function(data, status) {

                            var error = data.error;
                            var message = data.message;
                            var type = '';
                            var text = '';

                            if (error == true) {
                                type = 'error';
                                text = 'Ocurrió un problema al actualizar los block-types del grupo.';
                            } else {
                                type = 'success';

                                var estado_class = '';
                                var estado = $('#block_type_id').selectize()[0].selectize.options[block_type_id].description

                                if (block_type_id == 0) {
                                    estado_class = 'success';
                                } else {
                                    estado_class = 'danger';
                                }

                                var estado_html = '<span class="label label-' + estado_class + '">' + estado + '</span> <br> <b>(Modificado Manual)</b> ';

                                $('#col_status_' + global_group_id).html(estado_html);

                                console.log('html:', estado_html);

                            }

                            swal({
                                    title: message,
                                    text: text,
                                    type: type,
                                    showCancelButton: false,
                                    confirmButtonColor: '#3c8dbc',
                                    confirmButtonText: 'Aceptar',
                                    cancelButtonText: 'No.',
                                    closeOnClickOutside: false
                                },
                                function(isConfirm) {
                                    if (isConfirm) {

                                        $('#modal_atm_block_type_change').modal('hide');


                                    }
                                }
                            );

                        });
                    }

                }
            );
        } else {
            swal('Agregar un comentario!');
        }

    }

    $('#save_block_type').click(
        function(e) {
            e.preventDefault();
            save_atm_block_type_change();
        }
    );

    //-----------------------------------------------------------------------------------------------

    var selective_config = {
        delimiter: ',',
        persist: false,
        openOnFocus: true,
        valueField: 'id',
        labelField: 'description',
        searchField: 'description',
        maxItems: 1,
        options: {!!$block_types!!}
    };

    $('#block_type_id').selectize(selective_config);


    //-----------------------------------------------------------------------------------------------

    function filter_datatable(parameters) {
        var status = parameters['description'];
        var amount = parameters['total'];

        if (amount > 0) {
            var datatable_1_filter = $('#datatable_1_filter > label > input');
            datatable_1_filter.val(status);

            var e = $.Event("keyup", {
                which: 13
            });
            datatable_1_filter.trigger(e);

            $('html, body').animate({
                scrollTop: datatable_1_filter.offset().top
            }, 500);
        } else {
            status = (status == '') ? 'Total' : status;

            swal({
                    title: status + ': \n 0 TRANSACCIONES',
                    text: '0 Gs. en total \n Representa el 0% \n \n Obs: Los filtros de búsquedas \n delimitan los resultados.',
                    type: 'info',
                    showCancelButton: false,
                    closeOnClickOutside: false,
                    confirmButtonColor: '#3c8dbc',
                    confirmButtonText: 'Aceptar'
                },
                function(isConfirm) {
                    if (isConfirm) {

                    }
                }
            );
        }
    }

    //-----------------------------------------------------------------------------------------------

    $('#timestamp').daterangepicker({
        timePicker: true,
        timePicker24Hour: true,
        timePickerIncrement: 1,
        format: 'DD/MM/YYYY HH:mm:ss',
        startDate: moment().startOf('month'),
        endDate: moment().endOf('month'),
        opens: 'center',
        drops: 'down',
        showDropdowns: true,

        /*dateLimit: {
            'months': 1,
            'days': -1,
        },*/

        minDate: moment().startOf('year').subtract(5, 'year'),
        maxDate: new Date(),

        ranges: {
            'Hoy': [moment().startOf('day').toDate(), moment().endOf('day').toDate()],
            'Ayer': [moment().startOf('day').subtract(1, 'days'), moment().endOf('day').subtract(1, 'days')],
            'Semana': [moment().startOf('week'), moment().endOf('week')],
            'Semana pasada': [moment().startOf('week').subtract(1, 'week'), moment().endOf('week').subtract(1, 'week')],
            'Mes': [moment().startOf('month'), moment().endOf('month')],
            'Mes pasado': [moment().startOf('month').subtract(1, 'month'), moment().endOf('month').subtract(1, 'month')],
            'Año': [moment().startOf('year'), moment().endOf('year')],
            'Año pasado': [moment().startOf('year').subtract(1, 'year'), moment().endOf('year').subtract(1, 'year')]
        },

        locale: {
            applyLabel: 'Aplicar',
            fromLabel: 'Desde',
            toLabel: 'Hasta',
            customRangeLabel: 'Rango Personalizado',
            daysOfWeek: ['Dom', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sáb'],
            monthNames: [
                'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto',
                'Setiembre', 'Octubre', 'Noviembre', 'Diciembre'
            ],
            firstDay: 1
        }
    });

    $('#timestamp').attr({
        'onkeydown': 'return false'
    });

    $('#timestamp').hover(function() {
        $('#timestamp').attr({
            'title': 'El filtro de fecha es: ' + $('#timestamp').val()
        })
    }, function() {});

    $('#timestamp').val('{{ $timestamp }}');

    //-----------------------------------------------------------------------------------------------

    $('input[type="checkbox"]').iCheck({
        checkboxClass: 'icheckbox_square-grey',
        radioClass: 'iradio_square-grey'
    });

    var summary_to_date = "{{ $summary_to_date }}";
    var summary_closing = "{{ $summary_closing }}";

    if (summary_to_date !== '') {

        $('#summary_to_date').iCheck('check');

    } else if (summary_closing !== '') {

        $('#summary_closing').iCheck('check');

    }

    if (summary_to_date !== '' || summary_closing !== '') {

        $('#timestamp').prop('disabled', true);

    }


    $('#summary_to_date').on('ifChanged', function(event) {

        if ($('#summary_closing').is(':checked')) {
            $('#summary_closing').prop('checked', false);
            $('#summary_closing').iCheck('update');
        }

        if ($('#summary_to_date').is(':checked') == false && $('#summary_closing').is(':checked') == false) {
            $('#timestamp').prop('disabled', false);
        } else {
            $('#timestamp').prop('disabled', true);
        }
    });

    $('#summary_closing').on('ifChanged', function(event) {

        if ($('#summary_to_date').is(':checked')) {
            $('#summary_to_date').prop('checked', false);
            $('#summary_to_date').iCheck('update');
        }

        if ($('#summary_to_date').is(':checked') == false && $('#summary_closing').is(':checked') == false) {
            $('#timestamp').prop('disabled', false);
        } else {
            $('#timestamp').prop('disabled', true);
        }
    });

    //-----------------------------------------------------------------------------------------------

    var owners = {!!$owners!!};
    var groups = {!!$groups!!};
    var atms = {!!$atms!!};
    var managers = {!!$managers!!};

    //-----------------------------------------------------------------------------------------------

    $('.select2').select2({
        placeholder: "Seleccionar",
        allowClear: true
    });

    $('#owner_id').val(null).trigger('change');
    $('#owner_id').empty().trigger("change");

    $('#group_id').val(null).trigger('change');
    $('#group_id').empty().trigger("change");

    $('#atm_id').val(null).trigger('change');
    $('#atm_id').empty().trigger("change");

    $('#manager_id').val(null).trigger('change');
    $('#manager_id').empty().trigger("change");

    //--------------------------------------------------------------------------------------

    var option = new Option('Todos', 'Todos', false, false);
    $('#owner_id').append(option);

    for (var i = 0; i < owners.length; i++) {
        var item = owners[i];
        var id = item.id;
        var description = item.description;
        var option = new Option(description, id, false, false);
        $('#owner_id').append(option);
    }

    var owner_id = '{!! $owner_id !!}';
    $('#owner_id').val(owner_id).trigger('change');

    //--------------------------------------------------------------------------------------

    var option = new Option('Todos', 'Todos', false, false);
    $('#group_id').append(option);

    for (var i = 0; i < groups.length; i++) {
        var item = groups[i];
        var id = item.id;
        var description = item.description;
        var option = new Option(description, id, false, false);
        $('#group_id').append(option);
    }

    var group_id = '{!! $group_id !!}';
    $('#group_id').val(group_id).trigger('change');

    //--------------------------------------------------------------------------------------

    for (var i = 0; i < atms.length; i++) {
        var item = atms[i];
        var id = item.id;
        var description = item.description;
        var option = new Option(description, id, false, false);
        $('#atm_id').append(option);
    }

    var atm_id = '{!! $atm_id !!}';
    $('#atm_id').val(null).trigger('change');
    $('#atm_id').val(atm_id);

    //--------------------------------------------------------------------------------------

    var option = new Option('Todos', 'Todos', false, false);
    $('#manager_id').append(option);

    for (var i = 0; i < managers.length; i++) {
        var item = managers[i];
        var id = item.id;
        var description = item.description;
        var option = new Option(description, id, false, false);
        $('#manager_id').append(option);
    }

    var manager_id = '{!! $manager_id !!}';
    $('#manager_id').val(null).trigger('change');
    $('#manager_id').val(manager_id).trigger('change');

    console.log('manager_id:', manager_id);

    //--------------------------------------------------------------------------------------

    function load_atms() {
        var group_id = $('#group_id').val();

        console.log('group_id', group_id);

        $('#atm_id').val(null).trigger('change');
        $('#atm_id').empty().trigger("change");

        var option = new Option('Todos', 'Todos', false, false);
        $('#atm_id').append(option);

        for (var i = 0; i < atms.length; i++) {
            var item = atms[i];

            var group_id_aux = item.group_id;
            var atm_id = item.atm_id;
            var atm_description = item.atm_description;

            if (group_id == group_id_aux) {
                var option = new Option(atm_description, atm_id, false, false);
                $('#atm_id').append(option);
            }
        }

        //var atm_id = '{!! $atm_id !!}';
        $('#atm_id').val('Todos').trigger('change');
        //$('#atm_id').val(atm_id);
    }

    $('.select2').on('select2:select', function(e) {

        var id = e.currentTarget.id;

        var value_all_selected = 'Todos';

        switch (id) {
            case 'owner_id':

                $('#group_id').val('Todos').trigger('change');
                $('#atm_id').val(null).trigger('change');
                $('#atm_id').empty().trigger("change");

                break;

            case 'group_id':

                $('#owner_id').val('Todos').trigger('change');

                load_atms();

                break;
        }
    });

    load_atms();

    var atm_id = '{!! $atm_id !!}';
    $('#atm_id').val(atm_id).trigger('change');

    //--------------------------------------------------------------------------------------


    $(document).ready(function() {

        var json = <?php echo json_encode($records); ?>;

        var count = json.length;
        console.log('json:', json);

        if (count > 0) {

            var atms_details = {};

            $('#datatable_1_thead').append(
                $('<tr>')
                //.append($('<th>').append(''))
                .append($('<th style="max-width: 100px;">').append('Opciones'))
                .append($('<th style="max-width: 100px;">').append('Cliente'))
                .append($('<th style="max-width: 100px">').append('Saldo'))
                .append($('<th style="max-width: 200px">').append('Estado'))
                .append($('<th style="max-width: 100px">').append('Regla'))
                .append($('<th style="display: none">').append('Conteo ATMS Con Regla'))
                .append($('<th style="display: none">').append('Conteo ATMS Sin Regla'))
                .append($('<th style="display: none">').append('Descripción ATMS Con Regla'))
                .append($('<th style="display: none">').append('Descripción ATMS Sin Regla'))
                .append($('<th style="max-width: 200px">').append('Total-Multa'))
                .append($('<th>').append('Total-Transaccionado'))
                .append($('<th>').append('Total-Pagado'))
                .append($('<th>').append('Total-Reversado'))
                .append($('<th>').append('Total-Cashout'))
                .append($('<th>').append('Total-Pago-QR'))
                .append($('<th>').append('Total-Cuotas'))
                .append($('<th style="display: none;" class="datatable_totales">').append('Totales'))
            );

            for (var i = 0; i < count; i++) {
                var item = json[i];

                var group_id = item.group_id;
                var group_description = item.group_description;

                var total_transaccionado = item.total_transaccionado;
                var total_depositos = item.total_depositos;
                var total_reversiones = item.total_reversiones;
                var total_cashouts = item.total_cashouts;
                var total_pago_qr = item.total_pago_qr;
                var total_cuota = item.total_cuota;
                var total_multa = item.total_multa;

                var estado = item.estado;
                var regla = item.regla;
                var atms_con_regla = item.atms_con_regla;
                var atms_sin_regla = item.atms_sin_regla;

                var atms_con_regla_descripcion = item.atms_con_regla_descripcion;
                var atms_sin_regla_descripcion = item.atms_sin_regla_descripcion;

                var saldo = item.saldo;
                var saldo_aux = parseInt(saldo.replace(/.,/g, ''));

                var estado_class = '';
                var saldo_color = '';
                var regla_color = '';

                if (saldo_aux <= 0) {
                    saldo_color = '#00a65a';
                } else {
                    saldo_color = '#dd4b39';
                }


                if (estado == 'Activo') {
                    estado_class = 'success';
                } else if (estado == 'Inactivo') {
                    estado_class = 'warning';
                } else if (estado.includes('Bloqueado')) {
                    estado_class = 'danger';
                } else {
                    estado_class = 'info';
                }

                var estado_html = '<span class="label label-' + estado_class + '">' + estado + '</span>';

                if (regla == 'Con Regla') {
                    regla_color = '#00a65a';
                } else {
                    regla_color = '#dd4b39';
                }

                var regla_title = atms_con_regla + ' terminales con Regla y ' + atms_sin_regla + ' terminales sin Regla.';

                var botones_html = '';

                //botones_html += '<div class="btn-group-vertical" role="group">';

                botones_html += '<div class="dropdown">';

                botones_html += '   <span data-toggle="dropdown">';
                botones_html += '   <button class="btn btn-block btn-default btn-xs" type="button" style="background-color: #285f6c; color:white;" title="Ver Opciones">';
                botones_html += '       <span class="fa fa-info"></span>';
                botones_html += '   </button>';
                botones_html += '   </span>';

                botones_html += '   <ul class="dropdown-menu" role="menu" style="padding: 5px;">';

                botones_html += '   Opciones para el grupo:';

                botones_html += '   <li>';
                botones_html += '   <button class="btn btn-block btn-default btn-xs btn-terminales" type="button" style="background-color: #285f6c; color:white;" title="Ver terminales del grupo">';
                botones_html += '       <span class="fa fa-cubes"></span> Terminales';
                botones_html += '   </button>';
                botones_html += '   </li>';

                botones_html += '   <li>';
                botones_html += '   <button class="btn btn-block btn-default btn-xs btn-cuotas" type="button" style="background-color: #285f6c; color:white;" title="Ver cuotas del grupo">';
                botones_html += '       <span class="fa fa-list-ul"></span> Cuotas';
                botones_html += '   </button>';
                botones_html += '   </li>';

                botones_html += '   <li>';
                botones_html += '   <button class="btn btn-block btn-default btn-xs btn-regla" type="button" style="background-color: #285f6c; color:white;" title="Ver reglas del grupo">';
                botones_html += '       <span class="fa fa-unlock-alt"></span> Reglas';
                botones_html += '   </button>';
                botones_html += '   </li>';

                botones_html += '   <li>';
                botones_html += '   <button class="btn btn-block btn-default btn-xs btn-multa" type="button" style="background-color: #285f6c; color:white;" title="Ver multas del grupo">';
                botones_html += '       <span class="fa fa-exclamation-circle"></span> Multas';
                botones_html += '   </button>';
                botones_html += '   </li>';

                @if(\Sentinel::getUser()->hasAccess('accounting_statement_update_block_type'))
                botones_html += '   <li>';
                botones_html += '   <button class="btn btn-block btn-default btn-xs btn-block_type" type="button" style="background-color: #285f6c; color:white;" title="Cambiar Estado del grupo">';
                botones_html += '       <span class="fa fa-edit"></span> Cambiar Estado';
                botones_html += '   </button>';
                botones_html += '   </li>';
                @endif

                //botones_html += '       <li><a href="#">Botón 1</a></li>';
                //botones_html += '       <li><a href="#">Botón 2</a></li>';
                //botones_html += '       <li><a href="#">Botón 3</a></li>';

                botones_html += '   </ul>';

                botones_html += '</div>';

                //botones_html += '</div>';

                var totales_html = '';

                totales_html += '<b>Total-Multa:</b> ' + total_multa + ' <br/>';
                totales_html += '<b>Total-Transaccionado:</b> ' + total_transaccionado + ' <br/>';
                totales_html += '<b>Total-Pagado:</b> ' + total_depositos + ' <br/>';
                totales_html += '<b>Total-Reversado:</b> ' + total_reversiones + ' <br/>';
                totales_html += '<b>Total-Cashout:</b> ' + total_cashouts + ' <br/>';
                totales_html += '<b>Total-Pago-QR:</b> ' + total_pago_qr + ' <br/>';
                totales_html += '<b>Total-Cuotas:</b> ' + total_cuota + ' <br/>';

                $('#datatable_1_tbody').append(
                    $('<tr id="row_' + group_id + '" group_id="' + group_id + '" style="border-bottom: 1px solid gray">')
                    .append($('<td>').append(botones_html))
                    .append($('<td>').append(group_description))
                    .append($('<td style="color: ' + saldo_color + '; font-weight: bold; background: #f4f4f4;">').append(saldo))
                    .append($('<td style="background: #f4f4f4;" id="col_status_' + group_id + '">').append(estado_html))
                    .append($('<td style="color: ' + regla_color + '; font-weight: bold; background: #f4f4f4;" title="' + regla_title + '">').append(regla))
                    .append($('<td style="display: none">').append(atms_con_regla))
                    .append($('<td style="display: none">').append(atms_sin_regla))
                    .append($('<td style="display: none">').append(atms_con_regla_descripcion))
                    .append($('<td style="display: none">').append(atms_sin_regla_descripcion))
                    .append($('<td>').append(total_multa))
                    .append($('<td>').append(total_transaccionado))
                    .append($('<td>').append(total_depositos))
                    .append($('<td>').append(total_reversiones))
                    .append($('<td>').append(total_cashouts))
                    .append($('<td>').append(total_pago_qr))
                    .append($('<td>').append(total_cuota))
                    .append($('<td style="display: none" class="datatable_totales">').append(totales_html))
                );

            }

            //-----------------------------------------------------------------------------------------------

            var groupColumn = 0;

            table = $('#datatable_1').DataTable({
                fixedHeader: true,
                pageLength: 20,
                lengthMenu: [
                    1, 2, 5, 10, 20, 30, 50, 70, 100, 150, 300, 500, 1000, 1500, 2000
                ],
                dom: '<"pull-left"f><"pull-left"p><"pull-right"l>',
                language: {
                    "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
                },
                scroller: true,
                order: [2, 'desc'],
                columnDefs: [{
                    targets: 'no-sort',
                    orderable: false,
                }],
                "columnDefs": [{
                    "height": "25",
                    "targets": 0
                }],
                initComplete: function(settings, json) {
                    $('#content').css('display', 'block');
                    $('#div_load').css('display', 'none');
                    //$('body > div.wrapper > header > nav > a').trigger('click');

                    $('#datatable_1_wrapper').css({
                        'padding': '10px',
                        //'margin': '10px'
                    });

                    $('#datatable_1_wrapper > div:nth-child(2)').css({
                        'margin-left': '10px'
                    });

                    $('#datatable_1_filter > label > input').attr({
                        'placeholder': 'Ingresar búsqueda...'
                    });
                },
                "drawCallback": function(settings) {

                    console.log('dibujando...');

                    if ($('#check_balance_and_status').is(':checked') && $('#check_totals').is(':checked') == false) {
                        $('.datatable_totales').css('display', 'block');
                    } else {
                        $('.datatable_totales').css('display', 'none');
                    }
                }
            });

            // Configurar el botón para mostrar la subtabla
            $('#datatable_1 tbody').on('click', '.btn-terminales', function() {

                var fila_id = $(this).closest('tr').attr('id');
                var group_id = $(this).closest('tr').attr('group_id');
                var case_ = 'terminales';
                var row = table.row('#' + fila_id);

                if (row.child.isShown()) {
                    row.child.hide();
                    $('#' + fila_id).removeClass('shown');

                } else {

                }

                var atm_id_aux = "{{$atm_id}}"

                var url = '/get_details_per_group/';

                var json = {
                    _token: token,
                    case: case_,
                    owner_id: global_owner_id,
                    group_id: group_id,
                    atm_id: atm_id_aux,
                    timestamp: $('#timestamp').val(),
                    summary_to_date: "{{$summary_to_date}}",
                    summary_closing: "{{$summary_closing}}",
                };

                $.post(url, json, function(data, status) {

                    var list = data.list;

                    console.log('list:', list);

                    var datatable_id = 'sub_datatable_terminales_' + group_id;

                    var dth = '';

                    dth += '<div class="box box-default" style="border: 1px solid #d2d6de;">';

                    dth += '<div class="box-body" style="overflow-x: scroll;">';

                    dth += '   <button class="btn btn-danger btn-xs" type="button" onclick="close_table(' + group_id + ')">';
                    dth += '       <span class="fa fa-times"></span> Cerrar Tabla';
                    dth += '   </button>';

                    dth += '<br/><br/>';

                    dth += '<table class="table table-bordered table-hover dataTable" id="' + datatable_id + '">';
                    dth += '<thead>';
                    dth += '   <tr>';
                    dth += '       <th>Opciones</th>';
                    dth += '       <th>Terminal</th>';
                    dth += '       <th>Saldo</th>';
                    //dth += '       <th>Saldo-Cierre</th>';
                    dth += '       <th>Estado</th>';
                    dth += '       <th>Total-Multa</th>';
                    dth += '       <th>Total-Transaccionado</th>';
                    dth += '       <th>Total-Pagado</th>';
                    dth += '       <th>Total-Reversado</th>';
                    dth += '       <th>Total-Cashout</th>';
                    dth += '       <th>Total-Pago-QR</th>';
                    dth += '       <th>Total-Cuotas</th>';
                    dth += '   </tr>';
                    dth += '</thead>';

                    dth += '<tbody>';

                    for (var i = 0; i < list.length; i++) {
                        var item = list[i];

                        var atm_id = item.atm_id;
                        var atm_description = item.atm_description;

                        var total_transaccionado = item.total_transaccionado;
                        var total_depositos = item.total_depositos;
                        var total_reversiones = item.total_reversiones;
                        var total_cashouts = item.total_cashouts;
                        var total_pago_qr = item.total_pago_qr;
                        var total_cuotas = item.total_cuota;
                        var total_multa = item.total_multa;

                        var saldo = item.saldo;
                        var saldo_aux = parseInt(saldo.replace(/.,/g, ''));

                        var saldo_color = '';

                        if (saldo_aux <= 0) {
                            saldo_color = '#00a65a';
                        } else {
                            saldo_color = '#dd4b39';
                        }

                        //console.log('estado:', estado);

                        var estado = item.estado;
                        var estado_class = '';

                        if (estado == 'Activo') {
                            estado_class = 'success';
                        } else if (estado == 'Inactivo') {
                            estado_class = 'warning';
                        } else if (estado.includes('Bloqueado')) {
                            estado_class = 'danger';
                        } else {
                            estado_class = 'info';
                        }

                        var estado_html = '<span class="label label-' + estado_class + '">' + estado + '</span>';

                        var fila_atm_background_color = 'white';

                        if (atm_id_aux == atm_id) {
                            fila_atm_background_color = '#f4f4f4';
                        }

                        var botones_html = '';

                        botones_html += '   <button class="btn btn-block btn-default btn-xs btn-regla-terminal" type="button" title="Ver reglas del terminal" style="background-color: #285f6c; color:white;">';
                        botones_html += '       <span class="fa fa-unlock-alt"></span> Reglas';
                        botones_html += '   </button>';

                        botones_html += '   <button class="btn btn-block btn-default btn-xs btn-multa-terminal" type="button" title="Ver multas del terminal" style="background-color: #285f6c; color:white;">';
                        botones_html += '       <span class="fa fa-exclamation-circle"></span> Multas';
                        botones_html += '   </button>';

                        dth += '<tr id="row_atm_' + atm_id + '" atm_id="' + atm_id + '" style="background: ' + fila_atm_background_color + '">';
                        dth += '   <td>' + botones_html + '</td>';
                        dth += '   <td>' + atm_description + '</td>';
                        dth += '   <td style="color: ' + saldo_color + '; font-weight: bold;">' + saldo + '</td>';
                        dth += '   <td>' + estado_html + '</td>';
                        dth += '   <td>' + total_multa + '</td>';
                        dth += '   <td>' + total_transaccionado + '</td>';
                        dth += '   <td>' + total_depositos + '</td>';
                        dth += '   <td>' + total_reversiones + '</td>';
                        dth += '   <td>' + total_cashouts + '</td>';
                        dth += '   <td>' + total_pago_qr + '</td>';
                        dth += '   <td>' + total_cuota + '</td>';
                        dth += '</tr>';
                    }

                    dth += '</tbody>';

                    dth += '</table>';

                    dth += '</div>';

                    dth += '</div>';

                    row.child(dth).show();
                    $('#' + fila_id).addClass('shown');

                    if ($.fn.DataTable.isDataTable('#' + datatable_id)) {
                        $('#' + datatable_id).DataTable().destroy();
                    }

                    var sub_table = $('#' + datatable_id).DataTable({
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
                        order: [
                            [2, 'desc']
                        ]
                    });

                    console.log('data terminales:', data);

                    //-----------------------------------------------------------------------------------------------------

                    // Sub tabla en la sub tabla: Regla de terminales
                    $('#' + datatable_id + ' tbody').on('click', '.btn-regla-terminal', function() {

                        var fila_id = $(this).closest('tr').attr('id');
                        var atm_id = $(this).closest('tr').attr('atm_id');
                        var case_ = 'reglas_atm';
                        var row = sub_table.row('#' + fila_id);

                        if (row.child.isShown()) {
                            row.child.hide();
                            $('#' + fila_id).removeClass('shown');
                        } else {

                        }

                            var url = '/get_details_per_group/';

                            var json = {
                                _token: token,
                                case: case_,
                                atm_id: atm_id
                            };

                            $.post(url, json, function(data, status) {

                                var list = data.list;

                                var dth = '';

                                if (list.length > 0) {

                                    dth += '<div class="box box-default" style="border: 1px solid #d2d6de;">';

                                    dth += '<div class="box-body" style="overflow-x: scroll;">';

                                    dth += '   <button class="btn btn-danger btn-xs" type="button" onclick="close_table(' + group_id + ')">';
                                    dth += '       <span class="fa fa-times"></span> Cerrar Tabla';
                                    dth += '   </button>';

                                    dth += '<br/><br/>';

                                    dth += '<table class="table table-bordered table-hover dataTable">';
                                    dth += '<thead>';
                                    dth += '   <tr>';
                                    dth += '       <th>Tipo-Control</th>';
                                    dth += '       <th>Estado</th>';
                                    dth += '       <th>Día</th>';
                                    dth += '       <th>Días-Previos</th>';
                                    dth += '       <th>Saldo-Mínimo</th>';
                                    dth += '       <th>Fecha-Creación</th>';
                                    dth += '       <th>Fecha-Actualización</th>';
                                    dth += '   </tr>';
                                    dth += '</thead>';

                                    dth += '<tbody>';

                                    for (var i = 0; i < list.length; i++) {
                                        var item = list[i];

                                        var tipo_control = item.tipo_control;
                                        var dia = item.dia;
                                        var saldo_minimo = item.saldo_minimo;
                                        var dias_previos = item.dias_previos;
                                        var created_at = item.created_at;
                                        var updated_at = item.updated_at;
                                        var estado = item.estado;

                                        var tipo_control_style = '';

                                        if (tipo_control == 'Control de Límite') {
                                            tipo_control_style = 'background: #dd4b39; color: white;';
                                        }

                                        var estado_class = '';

                                        if (estado == 'Activo') {
                                            estado_class = 'success';
                                        } else if (estado == 'Eliminado') {
                                            estado_class = 'danger';
                                        }

                                        var estado_html = '<span class="label label-' + estado_class + '" style="display: grid; margin-top: 2px;">' + estado + '</span>';

                                        dth += '<tr style="' + tipo_control_style + '">';
                                        dth += '   <td>' + tipo_control + '</td>';
                                        dth += '   <td style="background: white; text-align: center;">' + estado_html + '</td>';
                                        dth += '   <td>' + dia + '</td>';
                                        dth += '   <td>' + dias_previos + '</td>';
                                        dth += '   <td>' + saldo_minimo + '</td>';
                                        dth += '   <td>' + created_at + '</td>';
                                        dth += '   <td>' + updated_at + '</td>';
                                        dth += '</tr>';

                                    }

                                    dth += '</tbody>';

                                    dth += '</table>';

                                    dth += '</div>';

                                    dth += '</div>';
                                } else {
                                    dth = '<span class="label label-danger" style="display: grid; margin-top: 2px; font-size: 15px; padding: 5px; text-align: left; max-width: 80%;">Terminal Sin Reglas.</span>';
                                }

                                row.child(dth).show();
                                $('#' + fila_id).addClass('shown');

                                console.log('reglas:', data);
                            });
                    });

                    // Sub tabla en la sub tabla: Multa de terminales
                    $('#' + datatable_id + ' tbody').on('click', '.btn-multa-terminal', function() {

                        var fila_id = $(this).closest('tr').attr('id');
                        var atm_id = $(this).closest('tr').attr('atm_id');
                        var case_ = 'multas_atm';
                        var row = sub_table.row('#' + fila_id);

                        if (row.child.isShown()) {
                            row.child.hide();
                            $('#' + fila_id).removeClass('shown');
                        } else {

                        }

                        var url = '/get_details_per_group/';

                        var json = {
                            _token: token,
                            case: case_,
                            atm_id: atm_id
                        };

                        $.post(url, json, function(data, status) {

                            var list = data.list;

                            var dth = '';

                            if (list.length > 0) {

                                dth += '<div class="box box-default" style="border: 1px solid #d2d6de;">';

                                dth += '<div class="box-body" style="overflow-x: scroll;">';

                                dth += '   <button class="btn btn-danger btn-xs" type="button" onclick="close_table(' + group_id + ')">';
                                dth += '       <span class="fa fa-times"></span> Cerrar Tabla';
                                dth += '   </button>';

                                dth += '<br/><br/>';

                                dth += '<table class="table table-bordered table-hover dataTable">';
                                dth += '<thead>';
                                dth += '   <tr>';
                                dth += '       <th>Tipo</th>';
                                dth += '       <th>Multa-ID</th>';
                                dth += '       <th>Venta-ID</th>';
                                dth += '       <th>Monto</th>';
                                dth += '       <th>Monto-Descuento</th>';
                                dth += '       <th>Monto-Cobrado</th>';
                                dth += '       <th>Fecha-Hora-Creación</th>';
                                dth += '       <th>Observación</th>';
                                dth += '   </tr>';
                                dth += '</thead>';

                                dth += '<tbody>';

                                for (var i = 0; i < list.length; i++) {
                                    var item = list[i];

                                    var penalty_type = item.penalty_type;
                                    var penalty_id = item.penalty_id;
                                    var sale_id = item.sale_id;
                                    var amount_penalty = item.amount_penalty;
                                    var amount_discount = item.amount_discount;
                                    var amount_total_to_pay = item.amount_total_to_pay;
                                    var created_at = item.created_at;
                                    var observation = item.observation;

                                    dth += '<tr>';
                                    dth += '   <td>' + penalty_type + '</td>';
                                    dth += '   <td>' + penalty_id + '</td>';
                                    dth += '   <td>' + sale_id + '</td>';
                                    dth += '   <td>' + amount_penalty + '</td>';
                                    dth += '   <td>' + amount_discount + '</td>';
                                    dth += '   <td>' + amount_total_to_pay + '</td>';
                                    dth += '   <td>' + created_at + '</td>';
                                    dth += '   <td>' + observation + '</td>';
                                    dth += '</tr>';

                                }

                                dth += '</tbody>';

                                dth += '</table>';

                                dth += '</div>';

                                dth += '</div>';
                            } else {
                                dth = '<span class="label label-danger" style="display: grid; margin-top: 2px; font-size: 15px; padding: 5px; text-align: left; max-width: 80%;">Terminal Sin Multas.</span>';
                            }

                            row.child(dth).show();
                            $('#' + fila_id).addClass('shown');

                            console.log('multas:', data);
                        });
                    });
                });

            });

            // Configurar el botón para mostrar la subtabla
            $('#datatable_1 tbody').on('click', '.btn-cuotas', function() {

                var fila_id = $(this).closest('tr').attr('id');
                var group_id = $(this).closest('tr').attr('group_id');
                var case_ = 'cuotas';
                var row = table.row('#' + fila_id);

                if (row.child.isShown()) {
                    row.child.hide();
                    $('#' + fila_id).removeClass('shown');
                } else {

                }

                var url = '/get_details_per_group/';

                var json = {
                    _token: token,
                    case: case_,
                    group_id: group_id,
                    timestamp: $('#timestamp').val(),
                    summary_to_date: "{{$summary_to_date}}",
                    summary_closing: "{{$summary_closing}}",
                };

                $.post(url, json, function(data, status) {

                    var list = data.list;

                    var datatable_id = 'sub_datatable_cuotas_' + group_id;

                    var dth = '';

                    dth += '<div class="box box-default" style="border: 1px solid #d2d6de;">';

                    dth += '<div class="box-body" style="overflow-x: scroll;">';

                    dth += '   <button class="btn btn-danger btn-xs" type="button" onclick="close_table(' + group_id + ')">';
                    dth += '       <span class="fa fa-times"></span> Cerrar Tabla';
                    dth += '   </button>';

                    dth += '<br/><br/>';

                    dth += '<table class="table table-bordered table-hover dataTable" id="' + datatable_id + '">';
                    dth += '<thead>';
                    dth += '   <tr>';
                    dth += '       <th>Terminal</th>';
                    dth += '       <th>Cuota-Cabecera-ID</th>';
                    dth += '       <th>Cuota-Número</th>';
                    dth += '       <th>Cuota-Importe</th>';
                    dth += '       <th>Cuota-Saldo</th>';
                    dth += '       <th>Cuota-Vencimiento</th>';
                    dth += '   </tr>';
                    dth += '</thead>';

                    dth += '<tbody>';

                    for (var i = 0; i < list.length; i++) {
                        var item = list[i];

                        var atm_description = item.atm_description;
                        var cabecera_id = item.cabecera_id;
                        var numero_cuota = item.numero_cuota;
                        var importe = item.importe;
                        var saldo_cuota = item.saldo_cuota;
                        var fecha_vencimiento = item.fecha_vencimiento;

                        dth += '<tr>';
                        dth += '   <td>' + atm_description + '</td>';
                        dth += '   <td>' + cabecera_id + '</td>';
                        dth += '   <td>' + numero_cuota + '</td>';
                        dth += '   <td>' + importe + '</td>';
                        dth += '   <td>' + saldo_cuota + '</td>';
                        dth += '   <td>' + fecha_vencimiento + '</td>';
                        dth += '</tr>';

                    }

                    dth += '</tbody>';

                    dth += '</table>';

                    dth += '</div>';

                    dth += '</div>';

                    row.child(dth).show();
                    $('#' + fila_id).addClass('shown');

                    if ($.fn.DataTable.isDataTable('#' + datatable_id)) {
                        $('#' + datatable_id).DataTable().destroy();
                    }

                    $('#' + datatable_id).DataTable({
                        orderCellsTop: true,
                        fixedHeader: true,
                        pageLength: 10,
                        lengthMenu: [
                            1, 2, 5, 10, 20, 30, 50, 70, 100, 150, 300, 500, 1000, 1500, 2000, 5000, 10000
                        ],
                        dom: '',
                        language: {
                            "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
                        },
                        scroller: true,
                        processing: true,
                        displayLength: 10
                    });

                    console.log('cuotas:', data);
                });


            });

            // Configurar el botón para mostrar la subtabla
            $('#datatable_1 tbody').on('click', '.btn-regla', function() {

                var fila_id = $(this).closest('tr').attr('id');
                var group_id = $(this).closest('tr').attr('group_id');
                var case_ = 'reglas';
                var row = table.row('#' + fila_id);

                if (row.child.isShown()) {
                    row.child.hide();
                    $('#' + fila_id).removeClass('shown');
                } else {

                }

                var url = '/get_details_per_group/';

                var json = {
                    _token: token,
                    case: case_,
                    group_id: group_id,
                    timestamp: $('#timestamp').val(),
                    summary_to_date: "{{$summary_to_date}}",
                    summary_closing: "{{$summary_closing}}",
                };

                $.post(url, json, function(data, status) {

                    var list = data.list;

                    var dth = '';

                    if (list.length > 0) {

                        var dth = '';

                        dth += '<div class="box box-default" style="border: 1px solid #d2d6de;">';

                        dth += '<div class="box-body" style="overflow-x: scroll;">';

                        dth += '   <button class="btn btn-danger btn-xs" type="button" onclick="close_table(' + group_id + ')">';
                        dth += '       <span class="fa fa-times"></span> Cerrar Tabla';
                        dth += '   </button>';

                        dth += '<br/><br/>';

                        dth += '<table class="table table-bordered table-hover dataTable">';
                        dth += '<thead>';
                        dth += '   <tr>';
                        dth += '       <th>Tipo-Control</th>';
                        dth += '       <th>Estado</th>';
                        dth += '       <th>Día</th>';
                        dth += '       <th>Días-Previos</th>';
                        dth += '       <th>Saldo-Mínimo</th>';
                        dth += '       <th>Fecha-Creación</th>';
                        dth += '       <th>Fecha-Actualización</th>';
                        dth += '   </tr>';
                        dth += '</thead>';

                        dth += '<tbody>';

                        for (var i = 0; i < list.length; i++) {
                            var item = list[i];

                            var tipo_control = item.tipo_control;
                            var dia = item.dia;
                            var saldo_minimo = item.saldo_minimo;
                            var dias_previos = item.dias_previos;
                            var created_at = item.created_at;
                            var updated_at = item.updated_at;
                            var estado = item.estado;

                            var tipo_control_style = '';

                            if (tipo_control == 'Control de Límite') {
                                tipo_control_style = 'background: #dd4b39; color: white;';
                            }

                            var estado_class = '';

                            if (estado == 'Activo') {
                                estado_class = 'success';
                            } else if (estado == 'Eliminado') {
                                estado_class = 'danger';
                            }

                            var estado_html = '<span class="label label-' + estado_class + '" style="display: grid; margin-top: 2px;">' + estado + '</span>';

                            dth += '<tr style="' + tipo_control_style + '">';
                            dth += '   <td>' + tipo_control + '</td>';
                            dth += '   <td style="background: white; text-align: center;">' + estado_html + '</td>';
                            dth += '   <td>' + dia + '</td>';
                            dth += '   <td>' + dias_previos + '</td>';
                            dth += '   <td>' + saldo_minimo + '</td>';
                            dth += '   <td>' + created_at + '</td>';
                            dth += '   <td>' + updated_at + '</td>';
                            dth += '</tr>';

                        }

                        dth += '</tbody>';

                        dth += '</table>';

                        dth += '</div>';

                        dth += '</div>';

                    } else {
                        dth = '<span class="label label-danger" style="display: grid; margin-top: 2px; font-size: 15px; padding: 5px; text-align: left; max-width: 80%;">Grupo Sin Reglas.</span>';
                    }

                    row.child(dth).show();
                    $('#' + fila_id).addClass('shown');

                    console.log('reglas:', data);
                });


            });

            $('#datatable_1 tbody').on('click', '.btn-multa', function() {

                var fila_id = $(this).closest('tr').attr('id');
                var group_id = $(this).closest('tr').attr('group_id');
                var case_ = 'multas';
                var row = table.row('#' + fila_id);

                if (row.child.isShown()) {
                    row.child.hide();
                    $('#' + fila_id).removeClass('shown');
                } else {

                }

                var url = '/get_details_per_group/';

                var json = {
                    _token: token,
                    case: case_,
                    group_id: group_id,
                    timestamp: $('#timestamp').val(),
                    summary_to_date: "{{$summary_to_date}}",
                    summary_closing: "{{$summary_closing}}",
                };

                $.post(url, json, function(data, status) {

                    var list = data.list;

                    var dth = '';

                    if (list.length > 0) {

                        dth += '<div class="box box-default" style="border: 1px solid #d2d6de;">';

                        dth += '<div class="box-body" style="overflow-x: scroll;">';

                        dth += '   <button class="btn btn-danger btn-xs" type="button" onclick="close_table(' + group_id + ')">';
                        dth += '       <span class="fa fa-times"></span> Cerrar Tabla';
                        dth += '   </button>';

                        dth += '<br/><br/>';

                        dth += '<table class="table table-bordered table-hover dataTable">';
                        dth += '<thead>';
                        dth += '   <tr>';
                        dth += '       <th>Tipo</th>';
                        dth += '       <th>Multa-ID</th>';
                        dth += '       <th>Venta-ID</th>';
                        dth += '       <th>Monto</th>';
                        dth += '       <th>Monto-Descuento</th>';
                        dth += '       <th>Monto-Cobrado</th>';
                        dth += '       <th>Fecha-Hora-Creación</th>';
                        dth += '       <th>Observación</th>';
                        dth += '   </tr>';
                        dth += '</thead>';

                        dth += '<tbody>';

                        for (var i = 0; i < list.length; i++) {
                            var item = list[i];

                            var penalty_type = item.penalty_type;
                            var penalty_id = item.penalty_id;
                            var sale_id = item.sale_id;
                            var amount_penalty = item.amount_penalty;
                            var amount_discount = item.amount_discount;
                            var amount_total_to_pay = item.amount_total_to_pay;
                            var created_at = item.created_at;
                            var observation = item.observation;

                            dth += '<tr>';
                            dth += '   <td>' + penalty_type + '</td>';
                            dth += '   <td>' + penalty_id + '</td>';
                            dth += '   <td>' + sale_id + '</td>';
                            dth += '   <td>' + amount_penalty + '</td>';
                            dth += '   <td>' + amount_discount + '</td>';
                            dth += '   <td>' + amount_total_to_pay + '</td>';
                            dth += '   <td>' + created_at + '</td>';
                            dth += '   <td>' + observation + '</td>';
                            dth += '</tr>';

                        }

                        dth += '</tbody>';

                        dth += '</table>';

                        dth += '</div>';

                        dth += '</div>';
                    } else {
                        dth = '<span class="label label-danger" style="display: grid; margin-top: 2px; font-size: 15px; padding: 5px; text-align: left; max-width: 80%;">Grupo Sin Multas.</span>';
                    }

                    row.child(dth).show();
                    $('#' + fila_id).addClass('shown');

                    console.log('multas:', data);
                });


            });


            @if(\Sentinel::getUser()->hasAccess('accounting_statement_update_block_type'))

            // Configurar el botón para mostrar la subtabla
            $('#datatable_1 tbody').on('click', '.btn-block_type', function() {

                global_group_id = null;
                global_group_id = $(this).closest('tr').attr('group_id');

                $('#block_type_id').selectize()[0].selectize.setValue(null, false);
                $('#commentary').val(null);
                $("#modal_atm_block_type_change").modal();

            });

            @endif

            // Función para contar el número de filas con monto mayor a 0
            function contar_filas() {

                var num_filas_con_multa = 0;
                var num_filas_sin_multa = 0;

                var actives_count = 0;
                var blocks_count = 0;
                var inactives_count = 0;
                var no_status_count = 0;

                var num_filas_mayor_cero = 0;
                var num_filas_menor_cero = 0;
                var num_filas_igual_cero = 0;

                var num_filas_grupos_con_regla = 0;
                var num_filas_grupos_sin_regla = 0;

                var num_terminales_con_regla = 0;
                var num_terminales_sin_regla = 0;

                var html_datatable_counts = '';

                table.rows({
                    search: 'applied'
                }).every(function() {

                    var data = this.data();

                    //console.log('data:', data);

                    var monto = parseFloat(data[2]);
                    var estado = data[3];
                    var regla = data[4];

                    var atms_con_regla = parseInt(data[5]);
                    var atms_sin_regla = parseInt(data[6]);

                    var atms_con_regla_descripcion = data[7];
                    var atms_sin_regla_descripcion = data[8];

                    var multa_monto = parseFloat(data[9]);

                    if (monto > 0) {
                        num_filas_mayor_cero++;
                    } else if (monto < 0) {
                        num_filas_menor_cero++;
                    } else if (monto == 0) {
                        num_filas_igual_cero++;
                    }


                    if (estado.includes("Activo")) {
                        actives_count++;
                    } else if (estado.includes("Bloqueado")) {
                        blocks_count++;
                    } else if (estado.includes("Inactivo")) {
                        inactives_count++;
                    } else if (estado.includes("Sin")) {
                        no_status_count++;
                    }


                    if (regla.includes("Con")) {
                        num_filas_grupos_con_regla++;
                    } else {
                        num_filas_grupos_sin_regla++;
                    }


                    if (atms_con_regla_descripcion.includes("Con") && atms_sin_regla_descripcion.includes("Sin")) {

                        num_terminales_con_regla += atms_con_regla;
                        num_terminales_sin_regla += atms_sin_regla;

                    } else if (atms_con_regla_descripcion.includes("Con")) {

                        num_terminales_con_regla += atms_con_regla;

                    } else if (atms_sin_regla_descripcion.includes("Sin")) {

                        num_terminales_sin_regla += atms_sin_regla;

                    }


                    if (multa_monto > 0) {
                        num_filas_con_multa++;
                    } else {
                        num_filas_sin_multa++;
                    }

                });

                $('#num_filas_mayor_cero').html('Con deuda: ' + num_filas_mayor_cero);
                $('#num_filas_menor_cero').html('Con saldo a favor: ' + num_filas_menor_cero);
                $('#num_filas_igual_cero').html('Sin deuda: ' + num_filas_igual_cero);

                $('#actives_count').html('Activos: ' + actives_count);
                $('#blocks_count').html('Bloqueados: ' + blocks_count);
                $('#inactives_count').html('Inactivos: ' + inactives_count);
                $('#no_status_count').html('Sin estado: ' + no_status_count);

                $('#num_filas_grupos_con_regla').html('Grupos con Regla: ' + num_filas_grupos_con_regla);
                $('#num_filas_grupos_sin_regla').html('Grupos sin Regla: ' + num_filas_grupos_sin_regla);
                $('#num_terminales_con_regla').html('Terminales con Regla: ' + num_terminales_con_regla);
                $('#num_terminales_sin_regla').html('Terminales sin Regla: ' + num_terminales_sin_regla);

                $('#num_filas_con_multa').html('Grupos con multa: ' + num_filas_con_multa);
                $('#num_filas_sin_multa').html('Grupos sin multa: ' + num_filas_sin_multa);

            }

            // Contar filas al cargar la página
            contar_filas();

            // Contar filas al cambiar el filtro de búsqueda
            table.on('draw.dt', function() {
                contar_filas();
            });



            //----------------------------------------------------------

            /*var actives_count = table.column(2).nodes().to$().filter(':contains("Activo")').length;
            var blocks_count = table.column(2).nodes().to$().filter(':contains("Bloqueado")').length;
            var inactives_count = table.column(2).nodes().to$().filter(':contains("Inactivo")').length;

            var html_datatable_counts = "";

            html_datatable_counts += 'Activos: ' + actives_count + '<br/>';
            html_datatable_counts += 'Bloqueados: ' + blocks_count + '<br/>';
            html_datatable_counts += 'Inactivos: ' + inactives_count + '<br/>';

            var columna_monto = table.column(1).data();

            var num_filas_mayor_cero = 0;
            var num_filas_menor_cero = 0;
            var num_filas_igual_cero = 0;

            table.rows().every(function() {
                var data = this.data();

                var monto = parseInt(data[1]);
                if (monto > 0) {
                    num_filas_mayor_cero++;
                } else if (monto < 0) {
                    num_filas_menor_cero++;
                } else if (monto == 0) {
                    num_filas_igual_cero++;
                }
            });

            html_datatable_counts += 'Clientes con deuda: ' + num_filas_mayor_cero + '<br/>';
            html_datatable_counts += 'Clientes con saldo a favor: ' + num_filas_menor_cero + '<br/>';
            html_datatable_counts += 'Clientes con saldo cero: ' + num_filas_igual_cero + '<br/>';

            $('#datatable_counts').html(html_datatable_counts);*/



            //-----------------------------------------------------------------------------------------------

            $('#check_balance_and_status').iCheck('check');
            $('#check_totals').iCheck('check');

            $('#check_balance_and_status').on('ifChanged', function(event) {

                //Columnas del 1 al 2 son Saldo y Estado
                for (var i = 2; i < 5; i++) {
                    var column = table.column(i);
                    column.visible(!column.visible());
                }

                if ($('#check_balance_and_status').is(':checked') && $('#check_totals').is(':checked') == false) {
                    $('.datatable_totales').css('display', 'block');
                } else {
                    $('.datatable_totales').css('display', 'none');
                }

            });

            $('#check_totals').on('ifChanged', function(event) {

                //Columnas de totales
                for (var i = 5; i < 16; i++) {
                    var column = table.column(i);
                    column.visible(!column.visible());
                }

                if ($('#check_balance_and_status').is(':checked') && $('#check_totals').is(':checked') == false) {
                    $('.datatable_totales').css('display', 'block');
                } else {
                    $('.datatable_totales').css('display', 'none');
                }

            });

            function check_status() {
                var valores = [];
                var valores_aux = '';
                var valor_bloqueado = '';

                if ($('#check_filter_actives').is(':checked')) {
                    valores.push('Activo');
                }

                if ($('#check_filter_inactives').is(':checked')) {
                    valores.push('Inactivo');
                }

                if ($('#check_filter_blockeds').is(':checked')) {
                    valores.push('Bloqueado');
                }

                if ($('#check_filter_no_status').is(':checked')) {
                    valores.push('Sin');
                }

                valores_aux = valores.join("|");

                if (valores_aux !== '') {
                    valores_aux = '^(' + valores_aux + ')';
                }

                //$('#datatable_1').DataTable().columns(3).search('^(Activo|Inactivo|Bloqueado)', true, true).draw();
                table.columns(3).search(valores_aux, true, true).draw();
                table.order([3, 'asc']).draw();

                console.log('VALORES FILTRADOS:', valores_aux);
            }

            $('#check_filter_actives').on('ifChanged', function(event) {
                check_status();
            });

            $('#check_filter_blockeds').on('ifChanged', function(event) {
                check_status();
            });

            $('#check_filter_inactives').on('ifChanged', function(event) {
                check_status();
            });

            $('#check_filter_no_status').on('ifChanged', function(event) {
                check_status();
            });

            function check_balance(check_name) {

                var valores_aux = '';

                switch (check_name) {

                    case 'check_balance_positive':

                        if ($('#check_balance_positive').is(':checked')) {

                            $('#check_balance_negative').prop('checked', false);
                            $('#check_balance_negative').iCheck('update');

                            $('#check_balance_cero').prop('checked', false);
                            $('#check_balance_cero').iCheck('update');

                            valores_aux = '(^(?!0*(\.0+)?$)(?!.*-).*$)';

                        }

                        break;

                    case 'check_balance_negative':


                        if ($('#check_balance_negative').is(':checked')) {

                            $('#check_balance_positive').prop('checked', false);
                            $('#check_balance_positive').iCheck('update');

                            $('#check_balance_cero').prop('checked', false);
                            $('#check_balance_cero').iCheck('update');

                            valores_aux = '(-)';

                        }

                        break;

                    case 'check_balance_cero':

                        if ($('#check_balance_cero').is(':checked')) {

                            $('#check_balance_negative').prop('checked', false);
                            $('#check_balance_negative').iCheck('update');

                            $('#check_balance_positive').prop('checked', false);
                            $('#check_balance_positive').iCheck('update');

                            valores_aux = '^(0)$';

                        }

                        break;
                    default:
                        valores_aux = '';

                }

                table.columns(2).search(valores_aux, true, true).draw();

                if (check_name == 'check_balance_negative') {
                    table.order([2, 'asc']).draw();
                } else {
                    table.order([2, 'desc']).draw();
                }

                //var info = table.page.info();
                //var num_filtradas = info.recordsDisplay;
                //console.log('Número de filas filtradas: ' + num_filtradas);

                //console.log('check_name:', check_name);
                //console.log('valores_aux:', valores_aux);

            }

            $('#check_balance_positive').on('ifChanged', function(event) {
                check_balance('check_balance_positive');
            });

            $('#check_balance_negative').on('ifChanged', function(event) {
                check_balance('check_balance_negative');
            });

            $('#check_balance_cero').on('ifChanged', function(event) {
                check_balance('check_balance_cero');
            });


            function check_regla() {
                var valores = [];
                var valores_aux = '';

                if ($('#check_group_with_rule').is(':checked')) {
                    valores.push('Con');
                }

                if ($('#check_group_no_rule').is(':checked')) {
                    valores.push('Sin');
                }

                valores_aux = valores.join("|");

                if (valores_aux !== '') {
                    valores_aux = '^(' + valores_aux + ')';
                }

                table.columns(4).search(valores_aux, true, true).draw();
                console.log('VALORES FILTRADOS:', valores_aux);
            }

            $('#check_group_with_rule').on('ifChanged', function(event) {
                check_regla();
            });

            $('#check_group_no_rule').on('ifChanged', function(event) {
                check_regla();
            });

            function check_multa(check_name) {
                var valores = [];
                var valores_aux = '';

                switch (check_name) {

                    case 'check_group_with_fine':

                        if ($('#check_group_with_fine').is(':checked')) {

                            $('#check_group_without_fine').prop('checked', false);
                            $('#check_group_without_fine').iCheck('update');

                            valores_aux = '^(?!0*(\.0+)?$)(?!.*-).*$';
                        }

                        break;

                    case 'check_group_without_fine':

                        if ($('#check_group_without_fine').is(':checked')) {

                            $('#check_group_with_fine').prop('checked', false);
                            $('#check_group_with_fine').iCheck('update');

                            valores_aux = '^(0)$';
                        }

                        break;
                    default:
                        valores_aux = '';

                }

                table.columns(9).search(valores_aux, true, true).draw();
                table.order([9, 'desc']).draw();
            }

            $('#check_group_with_fine').on('ifChanged', function(event) {
                check_multa('check_group_with_fine');
            });

            $('#check_group_without_fine').on('ifChanged', function(event) {
                check_multa('check_group_without_fine');
            });

            /**
             * 
             * 
             * //.append($('<th>').append(''))
                .append($('<th style="max-width: 100px;">').append('Opciones')) 0 
                .append($('<th>').append('Cliente')) 1 
                .append($('<th style="max-width: 100px">').append('Saldo')) 2
                .append($('<th style="max-width: 100px">').append('Estado')) 3
                .append($('<th>').append('Regla')) 4 
                .append($('<th style="display: none">').append('Conteo ATMS Con Regla')) 5
                .append($('<th style="display: none">').append('Conteo ATMS Sin Regla')) 6
                .append($('<th style="display: none">').append('Descripción ATMS Con Regla')) 7
                .append($('<th style="display: none">').append('Descripción ATMS Sin Regla')) 8
                .append($('<th>').append('Total-Transaccionado'))
                .append($('<th>').append('Total-Pagado'))
                .append($('<th>').append('Total-Reversado'))
                .append($('<th>').append('Total-Cashout'))
                .append($('<th>').append('Total-Pago-QR'))
                .append($('<th>').append('Total-Cuotas'))
             */

            function check_regla_atm() {

                if ($('#check_atm_with_rule').is(':checked')) {
                    table.columns(7).search('^(Con)', true, true).draw();
                } else {
                    table.columns(7).search('', true, true).draw();
                }

                if ($('#check_atm_no_rule').is(':checked')) {
                    table.columns(8).search('^(Sin)', true, true).draw();
                } else {
                    table.columns(8).search('', true, true).draw();
                }
            }

            $('#check_atm_with_rule').on('ifChanged', function(event) {
                check_regla_atm();
            });

            $('#check_atm_no_rule').on('ifChanged', function(event) {
                check_regla_atm();
            });

        } else {
            /*$('#div_summary_of_totals').css({
                'display': 'none'
            });

            $('#div_detail_by_atm').css({
                'display': 'none'
            });

            $('#div_datatable_1').css({
                'display': 'none'
            });*/

            $('#div_load').css('display', 'none');
            $('#content').css('display', 'block');
        }

        $(".alert").delay(5000).slideUp(300);
        $('[data-toggle="popover"]').popover();

        /*$('.dropdown-toggle').dropdown();

        $('.dropdown-toggle').hover(function() {
            $(this).next('.dropdown-menu').stop(true, true).slideDown();
        }, function() {
            $(this).next('.dropdown-menu').stop(true, true).slideUp();
        });*/

    });
</script>
@endsection