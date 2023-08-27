@extends('layout')

@section('title')
Usuarios por terminal
@endsection
@section('content')

<?php

$records = $data['lists']['records'];
$json = $data['lists']['json'];

$atms_per_users_free = $data['lists']['atms_per_users_free'];

//Combos
$users = $data['lists']['users'];
$atms = $data['lists']['atms'];

$user_id = $data['inputs']['user_id'];
$atm_id = $data['inputs']['atm_id'];
?>

<section class="content-header">

    <div class="row">
        <div class="col-md-12">
            @include('partials._flashes')
        </div>
    </div>

    <div class="box box-default" style="border-radius: 5px;" id="div_load">
        <div class="box-header with-border">
            <h3 class="box-title" style="font-size: 25px;">Cargando...
            </h3>
        </div>

        <div class="box-body">
            <div style="text-align: center; margin-bottom: 10px; font-size: 20px;">
                <div>
                    <i class="fa fa-spin fa-refresh fa-2x" style="vertical-align: sub;"></i> &nbsp;
                    Cargando...
                </div>
            </div>
        </div>
    </div>

    <div class="box box-default" style="border-radius: 5px;" id="content" style="display: none">
        <div class="box-header with-border">
            <h3 class="box-title" style="font-size: 25px;">Usuarios por terminal - Reporte
            </h3>
            <div class="box-tools pull-right">

                <button class="btn btn-info" type="button" title="Buscar según los filtros en los registros." style="margin-right: 5px" id="search" name="search" onclick="search('search')">
                    <span class="fa fa-search"></span> Buscar
                </button>

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
                    {!! Form::open(['route' => 'atms_per_users', 'method' => 'POST', 'role' => 'form', 'id' => 'form_search']) !!}
                    <div class="row">
                        <div class="col-md-6">
                            <label for="user_id">Buscar por Usuario:</label>
                            <div class="form-group">
                                <select name="user_id" id="user_id" class="select2" style="width: 100%"></select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="atm_id">Buscar por Terminal:</label>
                            <div class="form-group">
                                <select name="atm_id" id="atm_id" class="select2" style="width: 100%"></select>
                            </div>
                        </div>
                    </div>

                    <input name="json" id="json" type="hidden">

                    {!! Form::close() !!}
                </div>
            </div>

            @if (count($atms_per_users_free) > 0)
            <div class="box box-default" style="border: 1px solid #d2d6de;">
                <div class="box-header with-border">
                    <h3 class="box-title">Terminales sin encargado:</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
                <div class="box-body">
                    @foreach ($atms_per_users_free as $item)
                    <?php
                    $atm_id_view = $item->atm_id;
                    $description_view = $item->description;
                    ?>

                    <small class="label label-default"><i class="fa fa-cube"></i> &nbsp; #{{$atm_id_view}} {{$description_view}}</small> &nbsp;

                    @endforeach
                </div>
            </div>
            @endif

            <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_1">
                <thead>
                    <!--<tr>
                        <th>Opciones</th>
                        <th colspan="2">Usuario</th>
                        <th colspan="2">Terminal</th>
                        <th colspan="2">Fecha y Hora</th>
                    </tr>-->
                    <tr>
                        <th>Estado</th>
                        <!--<th>ID</th>
                        <th>Descripción</th>
                        <th>ID</th>
                        <th>Descripción</th>
                        <th>Creación</th>-->

                        <th>Usuario</th>
                        <th>Terminal</th>
                        <th>Actualización</th>
                    </tr>
                </thead>
                <tbody>
                    @if (count($records) > 0)
                    @foreach ($records as $item)

                    <?php

                    $atm_per_user_id = $item['atm_per_user_id'];
                    $user_id_view = $item['user_id'];
                    $user = $item['user'];
                    $atm_id_view = $item['atm_id'];
                    $atm = $item['atm'];
                    $created_at = $item['created_at'];
                    $updated_at = $item['updated_at'];
                    $status = $item['status'];
                    $status_view = $item['status_view'];

                    $checked = '';

                    if ($item['status']) {
                        $checked = 'checked';
                    }

                    $parameters = [
                        'atm_per_user_id' => $atm_per_user_id,
                        'user_id' => $user_id_view,
                        'atm_id' => $atm_id_view,
                        'status_view' => $status_view
                    ];

                    $parameters = json_encode($parameters);

                    ?>

                    <tr>
                        <td>
                            <input type='checkbox' onclick="alert_view({{ $parameters }})" style='cursor: pointer' {{ $checked }}> &nbsp; {{ $status_view }}
                        </td>
                        <td>{{ $user }}</td>
                        <td>{{ $atm }}</td>
                        <td>{{ $updated_at }}</td>
                    </tr>
                    @endforeach
                    @endif
                </tbody>
            </table>

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

<!-- Iniciar objetos -->
<script type="text/javascript">
    function alert_view(parameters) {
        var atm_per_user_id = parameters.atm_per_user_id;
        var user_id = parameters.user_id;
        var atm_id = parameters.atm_id;
        var status_view = parameters.status_view;

        var status = null;
        var message = '';

        if (status_view == 'Activo') {
            status = false;
            message = 'Está apunto de cambiar el estado: Activo a Inactivo.';
        } else if (status_view == 'Inactivo') {
            status = true;
            message = 'Está apunto de cambiar el estado: Inactivo a Activo.';
        }

        swal({
                title: 'Atención',
                text: message,
                type: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3c8dbc',
                confirmButtonText: 'Aceptar',
                cancelButtonText: 'Cancelar',
                closeOnClickOutside: false
            },
            function(isConfirm) {
                if (isConfirm) {

                    var url = '/atms_per_users_save/';

                    var json = {
                        _token: token,
                        atm_per_user_id: atm_per_user_id,
                        user_id: user_id,
                        atm_id: atm_id,
                        status: status
                    }

                    console.log('Campos a enviar:', json);

                    $.post(url, json, function(data) {
                        console.log('data: ', data);

                        var error = data.error;
                        var text = data.message;
                        var type = '';

                        if (error == true) {
                            type = 'error';
                        } else {
                            type = 'success';
                        }

                        swal('Atención', text, type);

                        new Promise(resolve => setTimeout(resolve, 2000)).then(() => {
                            location.reload();
                        });
                    });
                }
            }
        );
    }

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
        initComplete: function(settings, json) {
            $('#div_load').css('display', 'none');
            $('#content').css('display', 'block');
        }
    }

    var table = $('#datatable_1').DataTable(data_table_config);

    //-----------------------------------------------------------------------------------------------

    //Fecha
    if ($('#timestamp').val() == '' || $('#timestamp').val() == 0) {
        var d = new Date();
        var date = new Date(d.getFullYear(), d.getMonth(), d.getDate());
        var day = ('0' + date.getDate()).slice(-2);
        var month = ('0' + (date.getMonth() + 1)).slice(-2);
        var year = date.getFullYear()
        var init = day + '/' + month + '/' + year + ' 00:00:00';
        var end = day + '/' + month + '/' + year + ' 23:59:59';
        var aux = init + ' - ' + end;

        $('#timestamp').val(aux);
    }

    $('#timestamp').daterangepicker({
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
            'Semana': [moment().startOf('week'), moment().endOf('week')],
            'Mes': [moment().startOf('month'), moment().endOf('month')],
            'Año': [moment().startOf('year'), moment().endOf('year')]
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

    $('#timestamp').attr({
        'onkeydown': 'return false'
    });

    $('#timestamp').hover(function() {
        $('#timestamp').attr({
            'title': 'El filtro de fecha es: ' + $('#timestamp').val()
        })
    }, function() {

    });

    //-----------------------------------------------------------------------------------------------

    // Asignación de lo que se exportará luego.

    var json = {!!$json!!};
    var users = {!!$users!!};
    var atms = {!!$atms!!};

    //var json = {!!$json!!};
    //var users = {!!$users!!};
    //var atms = {!!$atms!!};


    //json = JSON.parse(json);
    json = JSON.stringify(json);
    $('#json').val(json);

    //-----------------------------------------------------------------------------------------------

    window.onload = function() {

        $('.select2').select2();

        $('#user_id').val(null).trigger('change');
        $('#user_id').empty().trigger("change");

        var option = new Option('Todos', 'Todos', false, false);
        $('#user_id').append(option);

        //users = JSON.parse(users);

        for (var i = 0; i < users.length; i++) {
            var item = users[i];
            var id = item.id;
            var description = item.description;
            var option = new Option(description, id, false, false);
            $('#user_id').append(option);
        }

        $('#user_id').val(null).trigger('change');
        $('#user_id').val("{{ $user_id }}").trigger('change');

        //----------------------------------------------------------

        $('#atm_id').val(null).trigger('change');
        $('#atm_id').empty().trigger("change");

        var option = new Option('Todos', 'Todos', false, false);
        $('#atm_id').append(option);


        //atms = JSON.parse(atms);

        for (var i = 0; i < atms.length; i++) {
            var item = atms[i];
            var id = item.id;
            var description = item.description;
            var option = new Option(description, id, false, false);
            $('#atm_id').append(option);
        }

        $('#atm_id').val(null).trigger('change');
        $('#atm_id').val("{{ $atm_id }}").trigger('change');

        //----------------------------------------------------------


        $('.select2').on('select2:select', function(e) {

        });
    };
</script>
@endsection