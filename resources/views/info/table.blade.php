@extends('layout')

@section('title')
Tablas
@endsection
@section('content')

<?php
//Variables que se usan en todo el blade.

$message = $data['message'];

$tables = $data['lists']['tables'];
$table_querys_actives = $data['lists']['table_querys_actives'];
$table_querys_actives_headers = $data['lists']['table_querys_actives_headers'];
$table_meta_data = $data['lists']['table_meta_data'];
$table_meta_data_headers = $data['lists']['table_meta_data_headers'];
$table_uses = $data['lists']['table_uses'];
$table_uses_headers = $data['lists']['table_uses_headers'];

$table_form = $data['lists']['table_form'];
$table_form_aux = json_encode($data['lists']['table_form']);

$latest_changes = $data['lists']['latest_changes'];
$latest_changes_headers = $data['lists']['latest_changes_headers'];
$latest_changes_aux = json_encode($data['lists']['latest_changes']);

$json = json_encode($data['lists']['table_meta_data']);

$data_aux = json_encode($data);

//Inputs
$table_id = $data['inputs']['table_id'];
$table_primary_key_name = $data['inputs']['table_primary_key_name'];
$user_id = $data['inputs']['user_id'];

?>

<section class="content-header">

    <style>
        .pagination>.active>a,
        .pagination>.active>a:focus,
        .pagination>.active>a:hover,
        .pagination>.active>span,
        .pagination>.active>span:focus,
        .pagination>.active>span:hover {
            background-color: #285f6c;
            border-color: #285f6c;
            color: white;
        }
    </style>

    <div class="row">
        <div class="col-md-12">
            @include('partials._flashes')
        </div>
    </div>

    <div class="box box-default" style="border-radius: 5px;">
        <div class="box-header with-border">
            <h3 class="box-title" style="font-size: 25px;">Tablas
            </h3>
            <div class="box-tools pull-right">
                <!--<button class="btn btn-warning" type="button" title="Ver los meda datos de la tabla" style="margin-right: 5px" id="search" name="search" onclick="search('search')">
                    <span class="fa fa-table"></span> Ver datos de la tabla
                </button>-->
            </div>
        </div>

        <div class="box-body">
            <div id="div_load" style="text-align: center; margin-bottom: 10px; font-size: 20px;">
                <div>
                    <i class="fa fa-spin fa-refresh fa-2x" style="vertical-align: sub;"></i> &nbsp;
                    Cargando...

                    <p id="rows_loaded" title="Filas cargadas"></p>
                </div>
            </div>

            <div id="content" style="display: none">

                {!! Form::open(['route' => 'info_table', 'method' => 'POST', 'role' => 'form', 'id' => 'form_search']) !!}
                <div class="row">

                    <div class="col-md-6">
                        <label for="tabla_id">Seleccionar Tabla:</label>
                        <div class="form-group">
                            <select name="table_id" id="table_id" class="select2" style="width: 100%">
                                <option value="" selected>Seleccionar tabla</option>

                                @foreach ($tables as $item)
                                <?php
                                $item_table_id = $item['id'];
                                $item_table_description = $item['description'];
                                ?>
                                <option value="{{$item_table_id}}">{{$item_table_description}}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-md-6">
                        @if (count($table_form) > 0)

                            @if ($table_primary_key_name !== null)
                            <label for="primary_key_search_id">Buscar por ID:</label>
                            <div class="input-group">
                                <input type="number" id="primary_key_search_id" name="primary_key_search_id" class="form-control" placeholder="Buscar en {{$table_id}} por ID"></input>
                                <span class="input-group-btn">
                                    <button type="button" class="btn btn-info btn-flat" style="background-color: #285f6c; border: 1px solid #285f6c; color: white;" id="button_search_and_set">
                                        <span class="fa fa-search"></span> Buscar
                                    </button>
                                </span>
                            </div>
                            @else
                            <br />
                            <h4 style="color: #dd4b39">La tabla: {{ $table_id }} no tiene PRIMARY KEY.</h4>
                            @endif

                        @endif
                    </div>
                </div>

                <input name="json" id="json" type="hidden">

                {!! Form::close() !!}

                @if (count($table_meta_data_headers) > 0)

                <div class="row" id="div_forms" style="display: none">
                    <div class="col-md-12">

                        <div class="box box-default" style="border: 1px solid #d2d6de;">
                            <div class="box-header with-border">
                                <h3 class="box-title">Modificar el registro:</h3>
                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-box-tool" data-widget="collapse" id="search_open"><i class="fa fa-minus"></i></button>
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="callout callout-default" style="border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px">

                                            <h4>Registro Actual:</h4>

                                            @foreach ($table_form as $table_form_item)
                                                <?php
                                                $column_name = $table_form_item['column_name'];
                                                $data_type = $table_form_item['data_type'];
                                                $precision = $table_form_item['precision'];
                                                $constraint_type = $table_form_item['constraint_type'];
                                                ?>

                                                @if(strstr($constraint_type, 'PRIMARY KEY') == false)
                                                <div class="form-group">
                                                    <label for="input_old_{{$column_name}}">{{$column_name}}:</label>
                                                    <input type="text" class="form-control" id="input_old_{{$column_name}}" placeholder="{{$column_name}}" disabled>
                                                </div>
                                                @endif

                                            @endforeach

                                        </div>
                                    </div>

                                    <div class="col-md-8">
                                        <div class="callout callout-default" style="border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px">

                                            <h4>Actualización:</h4>

                                            <?php 
                                                $lista_numericos = array(
                                                    "smallint",
                                                    "integer",
                                                    "bigint",
                                                    "decimal",
                                                    "numeric",
                                                    "real",
                                                    "double precision",
                                                    "serial",
                                                    "bigserial"
                                                );

                                                $lista_fechas_horas = array(
                                                    "timestamp",
                                                    "timestamp with time zone",
                                                    "timestamp without time zone"
                                                );

                                                $lista_booleanos = array(
                                                    "boolean",
                                                    "bool"
                                                );
                                            ?>

                                            @foreach ($table_form as $table_form_item)
                                            <?php
                                            $column_name = $table_form_item['column_name'];
                                            $data_type = $table_form_item['data_type'];
                                            $precision = $table_form_item['precision'];
                                            $constraint_type = $table_form_item['constraint_type'];
                                            //$column_default = $table_form_item['column_default'];
                                            
                                            $data_type_in_html = 'text';

                                            if (in_array($data_type, $lista_numericos)) {
                                                
                                                $data_type_in_html = 'number';

                                            } else if (in_array($data_type, $lista_fechas_horas)) {

                                                $data_type_in_html = 'timestamp';

                                            } else if (in_array($data_type, $lista_booleanos)) {

                                                $data_type_in_html = 'boolean';

                                            }

                                            $max_html = str_repeat('9', $precision);

                                            ?>

                                            @if(strstr($constraint_type, 'PRIMARY KEY') == false)
                                            <div class="form-group">
                                                <label for="input_new_{{$column_name}}">{{$column_name}}:</label>

                                                @if($data_type_in_html == 'number')

                                                    <input type="{{$data_type_in_html}}" class="form-control" id="input_new_{{$column_name}}" placeholder="Ingresa {{$column_name}}" 
                                                        oninput="validar_rango(this)">
                                                
                                                @elseif($data_type_in_html == 'timestamp') 

                                                    <input type="text" class="form-control timestamp" id="input_new_{{$column_name}}" placeholder="Ingresa {{$column_name}}" readonly>

                                                @elseif($data_type_in_html == 'boolean') 

                                                    <br/>

                                                    <select class="select2" id="input_new_{{$column_name}}" style="width: 100%;">
                                                        <option value="">Vacío (null)</option>
                                                        <option value="true">Verdadero (true)</option>
                                                        <option value="false">Falso (false)</option>
                                                    </select>

                                                @else 

                                                    <input type="text" class="form-control" id="input_new_{{$column_name}}" placeholder="Ingresa {{$column_name}}">

                                                @endif
                                            </div>
                                            @endif

                                            @endforeach

                                            <div class="input-group">
                                                <span class="input-group-addon">
                                                    <b>
                                                        <i class="fa fa-pencil"></i>
                                                    </b>
                                                </span>

                                                <textarea rows="4" cols="30" class="form-control" id="commentary" name="commentary" placeholder="Agregar un comentario sobre la modificación." value=""></textarea>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                <div class="btn-group" style="float: right">
                                    <button type="button" class="btn btn-default btn-flat" style="background-color: #285f6c; color: white; margin-right: 5px;" id="button_update">
                                        <span class="fa fa-save"></span> Guardar
                                    </button>

                                    <button type="button" class="btn btn-default btn-flat" style="background-color: #dd4b39; color: white;" id="button_cancel">
                                        <span class="fa fa-times"></span> Cancelar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">

                        <div class="box box-default" style="border: 1px solid #d2d6de;">
                            <div class="box-header with-border">
                                <h3 class="box-title">Últimos cambios en la tabla: <b> {{$table_id}} </b> </h3>
                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-box-tool" data-widget="collapse" id="search_open"><i class="fa fa-minus"></i></button>
                                </div>
                            </div>
                            <div class="box-body">
                                
                                @if (count($latest_changes) > 0)

                                <table class="table table-bordered table-hover dataTable sub_datatables" role="grid" style="font-size: 12px; font-weight: bold">
                                    <thead>
                                        <tr style="background-color: #285f6c; border: 1px solid #285f6c; color: white;">
                                            @foreach ($latest_changes_headers as $item)
                                            <th style="max-width: 200px">{{ $item }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($latest_changes as $sub_key)
                                        <?php
                                        $sub_key_aux = json_encode($sub_key);
                                        ?>

                                        <tr>
                                            @foreach ($latest_changes_headers as $item)

                                                @if ($item == 'Datos') 
                                                    <td> 
                                                        <pre id="data_{{ $sub_key['ID'] }}" style="background: white;"></pre>
                                                    </td>
                                                @else
                                                    <td> {{ $sub_key[$item] }} </td>
                                                @endif

                                            @endforeach
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                                @else

                                <h4 style="color: #dd4b39">No hay cambios sobre la tabla: <b> {{ $table_id }} </b> </h4>

                                @endif

                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">

                        <div class="box box-default" style="border: 1px solid #d2d6de;">
                            <div class="box-header with-border">
                                <h3 class="box-title">Consultas activas en la tabla</h3>
                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-box-tool" data-widget="collapse" id="search_open"><i class="fa fa-minus"></i></button>
                                </div>
                            </div>
                            <div class="box-body">

                                @if (count($table_querys_actives_headers) > 0)

                                <table class="table table-bordered table-hover dataTable" role="grid" style="font-size: 12px; font-weight: bold">
                                    <thead>
                                        <tr style="background-color: #285f6c; border: 1px solid #285f6c; color: white;">
                                            @foreach ($table_querys_actives_headers as $item)
                                            <th>{{ $item }}</th>
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($table_querys_actives as $sub_key)
                                        <?php
                                        $sub_key_aux = json_encode($sub_key);
                                        ?>

                                        <tr>
                                            @foreach ($table_querys_actives_headers as $item)
                                            <td> {{ $sub_key[$item] }} </td>
                                            @endforeach
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                                @else

                                <h4 style="color: #dd4b39">No hay consultas activas sobre la tabla: <b> {{ $table_id }} </b> </h4>

                                @endif

                            </div>
                        </div>

                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">


                        <div class="box box-default" style="border: 1px solid #d2d6de;">
                            <div class="box-header with-border">
                                <h3 class="box-title">Meta Datos</h3>
                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-box-tool" data-widget="collapse" id="search_open"><i class="fa fa-minus"></i></button>
                                </div>
                            </div>
                            <div class="box-body">

                                <div class="box box-default" style="border: 1px solid #d2d6de;">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">Mostrar / Ocultar columnas</h3>
                                    </div>
                                    <div class="box-body" id="hide_show_columns">
                                    </div>
                                </div>

                                <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_2" style="font-size: 12px; font-weight: bold">
                                    <thead>
                                        <tr style="background-color: #285f6c; border: 1px solid #285f6c; color: white;">
                                            @foreach ($table_meta_data_headers as $item)
                                            @if($item != 'schema_and_table' and $item != 'table_schema' and $item != 'table_name')
                                            <th>{{ $item }}</th>
                                            @endif
                                            @endforeach
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($table_meta_data as $sub_key)
                                        <?php
                                        $sub_key_aux = json_encode($sub_key);
                                        ?>

                                        <tr>
                                            @foreach ($table_meta_data_headers as $item)
                                            @if($item != 'schema_and_table' and $item != 'table_schema' and $item != 'table_name')
                                            <td title="{{ $sub_key[$item] }}"> {{ $sub_key[$item] }} </td>
                                            @endif
                                            @endforeach
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                            </div>
                        </div>

                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">

                        <div class="box box-default collapsed-box" style="border: 1px solid #d2d6de;">
                            <div class="box-header with-border">
                                <h3 class="box-title">Usos y Análisis</h3>
                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-box-tool" data-widget="collapse" id="search_open"><i class="fa fa-plus"></i></button>
                                </div>
                            </div>
                            <div class="box-body">
                                <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_1" style="font-size: 12px; font-weight: bold">
                                    <thead>
                                        <tr style="background-color: #285f6c; border: 1px solid #285f6c; color: white;">
                                            <th style="max-width: 150px">Información</th>
                                            <th style="max-width: 200px">Descripción</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($table_uses as $row)
                                        <tr>
                                            <td> {{ $row['title'] }} : <br>
                                                <h4>{{ $row['value'] }}</h4>
                                            </td>
                                            <td> {{ $row['description'] }} </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>

                            </div>
                        </div>

                    </div>
                </div>

                @else
                    <h4>{{ $message }}</h4>
                @endif

            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="modal" class="modal fade" role="dialog">

        <div class="modal-dialog modal-dialog-centered" role="document" style="background: white; border-radius: 5px">

            <!-- Modal content-->
            <div class="modal-content" style="border-radius: 10px">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <div class="modal-title" style="font-size: 20px; text-align: center">
                        Opciones disponibles para exportar
                    </div>
                </div>
                <div class="modal-body" style="text-align: center">
                    <div class="btn-group" role="group">

                        <button class="btn btn-success" type="button" title="Exportar a .xls" onclick="search('xls')">
                            <span class="fa fa-file-excel-o"></span> xls
                        </button>

                        &nbsp;

                        <button class="btn btn-success" type="button" title="Exportar a .xlsx" onclick="search('xlsx')">
                            <span class="fa fa-file-excel-o"></span> xlsx
                        </button>

                        &nbsp;

                        <button class="btn btn-success" type="button" title="Exportar a .csv" onclick="search('csv')">
                            <span class="fa fa-file-excel-o"></span> csv
                        </button>

                        <!--<a class="btn btn-warning" title="Exportar a .xls"><i class="fa fa-file-excel-o"></i>
                                                        .json</a> &nbsp;-->
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="content">

</section>

@endsection


@section('js')
<!-- datatables -->
<link rel="stylesheet" href="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.css">
<script src="/bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.min.js"></script>

<!-- select2 -->
<script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>
<link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />

<!-- json-formatter -->
<link rel="stylesheet" href="/js/json-formatter/jquery.json-viewer.css">
<script src="/js/json-formatter/jquery.json-viewer.js"></script>

<!-- date-range-picker -->
<link href="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css" />
<script src="/bower_components/admin-lte/plugins/daterangepicker/moment.min.js"></script>
<script src="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker.js"></script>

<!-- bootstrap datepicker -->
<script src="/bower_components/admin-lte/plugins/datepicker/bootstrap-datepicker.js"></script>

<!-- Iniciar objetos -->
<script type="text/javascript">

$(document).ready(function() {

    var primary_key_search_id = null;

    function validar_rango(input) {
        /*var valor = parseFloat(input.value);
        var min = parseFloat(input.min);
        var max = parseFloat(input.max);

        if (valor < min || isNaN(valor)) {
            input.value = min;
        } else if (valor > max) {
            input.value = max;
        }*/

        input.value = input.value.replace(/e/gi, '');
    }

    function search(button) {

        var table_id = $('#table_id').val();

        if (table_id !== '') {
            $('#form_search').submit();
        } else {
            swal({
                    title: 'Atención',
                    text: 'Seleccionar la tabla',
                    type: 'warning',
                    showCancelButton: false,
                    closeOnClickOutside: false,
                    confirmButtonColor: '#00c0ef',
                    confirmButtonText: 'Aceptar'
                },
                function(isConfirm) {}
            );

        }
    }

    function search_and_set() {

        var table = "{{$table_id}}";
        var table_primary_key_name = "{{$table_primary_key_name}}";
        primary_key_search_id = $('#primary_key_search_id').val();

        if (table_primary_key_name !== '') {

            if (primary_key_search_id !== '') {

                $('#button_search_and_set').html('<span class="fa fa-spin fa-refresh"></span> Buscando...');

                var url = '/info_table_search_by_id/';

                var json = {
                    _token: token,
                    table_id: table,
                    table_primary_key_name: table_primary_key_name,
                    primary_key_search_id: primary_key_search_id
                };

                $.post(url, json, function(data, status) {

                    $('#button_search_and_set').html('<span class="fa fa-search"></span> Buscar');

                    if (data.length > 0) {

                        data = data[0];

                        for (var key in data) {
                            var value = data[key];

                            if (table_primary_key_name !== key) {

                                $('#input_old_' + key).val(value);
                                $('#input_new_' + key).val(value);

                                var type = $('#input_new_' + key).prop('type');

                                if (type == 'select-one') {
                                    $('#input_new_' + key).select2("val", '' + value + '');
                                }

                            }

                        }

                        $('#div_forms').css('display', 'block');

                    } else {
                        swal('El registro con ID: ' + primary_key_search_id + ' no existe.');
                    }

                });

            } else {
                swal('Ingresar un ID para buscar en la tabla: ' + table_id);
            }
        } else {
            swal('La tabla: ' + table_id + ' no tiene PRIMARY KEY!');
        }
    }

    function update() {

        var table = "{{$table_id}}";
        var table_primary_key_name = "{{$table_primary_key_name}}";
        var user_id = "{{$user_id}}";
        var table_form = {!!$table_form_aux!!}
        var table_form_update = false;

        var commentary = $('#commentary').val();

        if (commentary !== null && commentary !== '') {

            if (table_primary_key_name !== null) {

                if (primary_key_search_id !== null) {

                    console.log('table_form:', table_form);

                    var message = '';

                    for (var i = 0; i < table_form.length; i++) {

                        var constraint_type = table_form[i].constraint_type;
                        var column_name = table_form[i].column_name;
                        var is_nullable = table_form[i].is_nullable;

                        table_form[i].old_value = 'sin cambios';
                        table_form[i].new_value = 'sin cambios';

                        if (table_primary_key_name !== column_name) {

                            var old_value = $('#input_old_' + column_name).val();
                            var new_value = $('#input_new_' + column_name).val();

                            table_form[i].old_value = old_value;
                            table_form[i].new_value = new_value;

                            if (old_value !== new_value) {

                                if (is_nullable == 'NO' && new_value == '') {
                                    message = 'El campo: ' + column_name + ' no puede quedar vacío.';
                                    break;
                                }

                                table_form[i].update_value = true;
                                table_form_update = true;

                                console.log('item cambiado:', table_form[i]);
                            }

                        }
                    }

                    if (message == '') {

                        if (table_form_update) {

                            $('#button_update').html('<span class="fa fa-spin fa-refresh"></span> Actualizando...');

                            var url = '/info_table_update/';

                            var json = {
                                _token: token,
                                table_id: table,
                                table_primary_key_name: table_primary_key_name,
                                primary_key_search_id: primary_key_search_id,
                                table_form: table_form,
                                commentary: commentary,
                                user_id: user_id
                            };

                            $.post(url, json, function(data, status) {

                                $('#button_update').html('<span class="fa fa-save"></span> Guardar');

                                console.log('data:', data);

                                var error = data.error;
                                var message = data.message;
                                var type = '';

                                if (error == true) {
                                    type = 'error';
                                } else {
                                    type = 'success';
                                }

                                swal({
                                        title: 'Mensaje:',
                                        text: message,
                                        type: type,
                                        showCancelButton: false,
                                        confirmButtonColor: '#3c8dbc',
                                        confirmButtonText: 'Aceptar',
                                        cancelButtonText: 'No.',
                                        closeOnClickOutside: false
                                    },
                                    function(isConfirm) {
                                        if (isConfirm) {
                                            close_update();
                                        }
                                    }
                                );

                            });
                        } else {
                            swal('No hiciste ninguna modifición.');
                        }
                    } else {
                        swal(message);
                    }   
                } else {
                    swal('Ingresar un ID para buscar en la tabla: ' + table_id);
                }
            } else {
                swal('La tabla: ' + table_id + ' no tiene PRIMARY KEY!');
            }
        } else {
            swal('Ingresar un comentario sobre el cambio.');
        }
    }

    function close_update() {

        var table_form = {!!$table_form_aux!!}

        for (var i = 0; i < table_form.length; i++) {

            var column_name = table_form[i].column_name;

            $('#input_old_' + column_name).val(null);
            $('#input_new_' + column_name).val(null);

            table_form[i].old_value = null;
            table_form[i].new_value = null;
            table_form[i].update_value = false;
        }

        $('#primary_key_search_id').val(null);
        $('#commentary').val(null);

        $('#div_forms').css('display', 'none');
    }

        $('#button_search_and_set').click(function(e) {
            e.preventDefault();
            search_and_set();
        });

        $('#button_update').click(function(e) {
            e.preventDefault();
            update();
        });

        $('#button_cancel').click(function(e) {
            e.preventDefault();
            close_update();
        });

        $('.select2').select2();

        $('#table_id').on('select2:select', function(e) {
            e.preventDefault();
            search('search');
        });

        $('#content').css('display', 'block');
        $('#div_load').css('display', 'none');

        var data_table_config = {
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
            displayLength: 10,
            order: [],
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
            }
        }

        $('#datatable_1').DataTable(data_table_config);

        var table = $('#datatable_2').DataTable(data_table_config);

        var hide_show_columns = [];

        var ths = $("#datatable_2").find("th");

        var index = 0;

        for (var i = index; i < ths.length; i++) {
            hide_show_columns.push(ths[i].innerHTML);
        }

        for (var i = index; i < hide_show_columns.length; i++) {

            var description = hide_show_columns[i];

            $('#hide_show_columns').append(
                '<a class="toggle-vis btn btn-default btn-sm" data-column="' + i + '" id="toggle-vis-' + i +
                '" value="' + description + '" state="on" title="Mostrar / Ocultar columna: ' + description +
                '" style="background-color: #285f6c; border: 1px solid #285f6c; color: white;">' +
                '<i class="fa fa-eye"></i> &nbsp;' + description +
                '</a> '
            );
        }

        $('a.toggle-vis').on('click', function(e) {
            e.preventDefault();

            var column = table.column($(this).attr('data-column'));
            column.visible(!column.visible());

            var fa = (!column.visible()) ? 'eye-slash' : 'eye';
            $(this).html('<i class="fa fa-' + fa + '"></i> &nbsp;' + $(this).attr('value'));
        });

        //Esconder la alerta después de 5 segundos. 
        $(".alert").delay(5000).slideUp(300);
        $('[data-toggle="popover"]').popover();

        $("#table_id").select2("val", "{{$table_id}}");

        console.log('table_id:', "{{$table_id}}");

        var latest_changes = {!!$latest_changes_aux!!};

        for (var i = 0; i < latest_changes.length; i++) {

            var item = latest_changes[i];
            var id = item.ID;
            var data = item.Datos;

            var data = eval('(' + data + ')');
            $('#data_' + id).jsonViewer(data, {collapsed: true});
        }

        console.log('latest_changes:', latest_changes);

        $('.sub_datatables').DataTable({
            fixedHeader: true,
            pageLength: 5,
            lengthMenu: [
                1, 2, 5, 10, 20
            ],
            dom: '<"pull-left"f><"pull-right"l>tip',
            language: {
                "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
            },
            scroller: true,
            displayLength: 5,
            order: []
        });


        $('.timestamp').daterangepicker({
            singleDatePicker: true,
            timePicker: true,
            timePicker24Hour: true,
            timePickerIncrement: 1,
            format: 'YYYY-MM-DD HH:mm:ss'
        });

    });
</script>
@endsection