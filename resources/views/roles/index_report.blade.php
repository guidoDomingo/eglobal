@extends('app')

@section('title')
Roles - Reporte
@endsection
@section('content')

<?php

//Variable que se usa en todo el documento 
$records = $data['lists']['records'];
$json = $data['lists']['json'];

//Combos
$roles = $data['lists']['roles'];
$permissions = $data['lists']['permissions'];
$users = $data['lists']['users'];

//Valor de campos
$rol_id = $data['inputs']['rol_id'];
$permission_id = $data['inputs']['permission_id'];
$user_id = $data['inputs']['user_id'];

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
            <h3 class="box-title" style="font-size: 25px;">Roles - Reporte
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
                    {!! Form::open(['route' => 'roles_report', 'method' => 'POST', 'role' => 'form', 'id' => 'form_search']) !!}
                    <div class="row">

                        <div class="col-md-4">
                            <label for="rol_id">Buscar por Rol:</label>
                            <div class="form-group">
                                <select name="rol_id" id="rol_id" class="select2" style="width: 100%"></select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="permission_id">Buscar por permiso:</label>
                            <div class="form-group">
                                <select name="permission_id" id="permission_id" class="select2" style="width: 100%"></select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="user_id">Buscar por usuario:</label>
                            <div class="form-group">
                                <select name="user_id" id="user_id" class="select2" style="width: 100%"></select>
                            </div>
                        </div>
                    </div>

                    <input name="json" id="json" type="hidden">

                    {!! Form::close() !!}
                </div>
            </div>

            <div class="box box-default" style="border: 1px solid #d2d6de;">
                <div class="box-header with-border">
                    <h3 class="box-title">Listado:</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
                <div class="box-body">
                    <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_1">
                        <thead>
                            <tr>
                                <th style="max-width: 400px;">Usuario</th>
                                <th>Permisos</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($records as $item)

                            <?php
                            $item_roles_per_user = $item['roles_per_user'];
                            $item_permissions = $item['permissions'];
                            ?>

                            <tr style="border: 1px solid black !important">
                                <td>

                                    <b> {{ $item['description'] }} </b>

                                    <br /> <br />

                                    @if($item_roles_per_user !== null)

                                    <b> Roles del usuario: </b>

                                    <br />

                                    @foreach($item_roles_per_user as $sub_item)
                                    <small class="label label-xs label-default"><i class="fa fa-cube"></i> &nbsp; {{ $sub_item['name'] }} ( {{ $sub_item['slug'] }} )</small> <br />
                                    @endforeach

                                    @else
                                    <b style="color: #dd4b39"> Sin Roles.</b>
                                    @endif

                                </td>

                                <td>

                                    @if($item_permissions !== null)

                                    <table class="table table-bordered table-hover dataTable sub_datatables">
                                        <thead>
                                            <tr>
                                                <th>Rol</th>
                                                <th>Permiso</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            @foreach($item_permissions as $sub_item)

                                            <?php
                                            $value_view = 'Activo';
                                            $label_color = 'success';

                                            if ($sub_item['status'] == false) {
                                                $value_view = 'Inactivo';
                                                $label_color = 'danger';
                                            }
                                            ?>

                                            <tr>
                                                <td>Custom</td>
                                                <td>{{ $sub_item['description'] }} ( {{ $sub_item['permission'] }} )</td>
                                                <td><small class="label label-{{ $label_color }}"> {{ $value_view }} </small></td>
                                            </tr>

                                            @endforeach


                                            @foreach($item_roles_per_user as $sub_item)

                                            <?php
                                            $roles_per_user_permissions = $sub_item['permissions'];
                                            //\Log::info('roles_per_user_permissions:', [$roles_per_user_permissions]);
                                            //die();
                                            ?>

                                            @if($roles_per_user_permissions !== null)

                                            @foreach($roles_per_user_permissions as $sub_item_item)

                                            <?php
                                            $value_view = 'Activo';
                                            $label_color = 'success';

                                            if ($sub_item_item['status'] == false) {
                                                $value_view = 'Inactivo';
                                                $label_color = 'danger';
                                            }
                                            ?>

                                            <tr>
                                                <td>{{ $sub_item['name'] }}</td>
                                                <td>{{ $sub_item_item['description'] }} ( {{ $sub_item_item['permission'] }} )</td>
                                                <td><small class="label label-{{ $label_color }}"> {{ $value_view }} </small></td>
                                            </tr>
                                            @endforeach

                                            @endif

                                            @endforeach
                                        </tbody>
                                    </table>

                                    @else
                                    <b style="color: #dd4b39"> Sin Permisos.</b>
                                    @endif

                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
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

    var roles = {!!$roles!!};
    var permissions = {!!$permissions!!};
    var users = {!!$users!!};

    var json = {!!$json!!};
    json = JSON.stringify(json);
    $('#json').val(json);

    //window.onload = function() {

    $('#rol_id').val(null).trigger('change');
    $('#rol_id').empty().trigger('change');

    var option = new Option('Todos', 'Todos', false, false);
    $('#rol_id').append(option);

    for (var i = 0; i < roles.length; i++) {
        var item = roles[i];
        var id = item.id;
        var description = item.description;
        var option = new Option(description, id, false, false);
        $('#rol_id').append(option);
    }

    $('#rol_id').val(null).trigger('change');
    $('#rol_id').val("{{ $rol_id }}").trigger('change');

    //----------------------------------------------------------------------------

    $('#permission_id').val(null).trigger('change');
    $('#permission_id').empty().trigger('change');

    var option = new Option('Todos', 'Todos', false, false);
    $('#permission_id').append(option);

    for (var i = 0; i < permissions.length; i++) {
        var item = permissions[i];
        var id = item.id;
        var description = item.description;
        var option = new Option(description, id, false, false);
        $('#permission_id').append(option);
    }

    $('#permission_id').val(null).trigger('change');
    $('#permission_id').val("{{ $permission_id }}").trigger('change');

    //----------------------------------------------------------------------------

    $('#user_id').val(null).trigger('change');
    $('#user_id').empty().trigger('change');

    var option = new Option('Todos', 'Todos', false, false);
    $('#user_id').append(option);

    for (var i = 0; i < users.length; i++) {
        var item = users[i];
        var id = item.id;
        var description = item.description;
        var option = new Option(description, id, false, false);
        $('#user_id').append(option);
    }

    $('#user_id').val(null).trigger('change');
    $('#user_id').val("{{ $user_id }}").trigger('change');

    //----------------------------------------------------------------------------

    $('.select2').on('select2:select', function(e) {

        var id = e.currentTarget.id;

        switch (id) {
            case 'rol_id':
                $('#permission_id').val('Todos').trigger('change');
                $('#user_id').val('Todos').trigger('change');
                break;

            case 'permission_id':
                $('#rol_id').val('Todos').trigger('change');
                $('#user_id').val('Todos').trigger('change');
                break;

            case 'user_id':
                $('#rol_id').val('Todos').trigger('change');
                $('#permission_id').val('Todos').trigger('change');
                break;
        }
    });


    //-----------------------------------------------------------------------------------------------

    function get_roles_permissions(rol_id) {

        var url = '/get_roles_permissions/';

        var rol_id = parseInt($('#rol_id').val());
        rol_id = (Number.isNaN(rol_id)) ? 'Todos' : rol_id;

        var json = {
            _token: token,
            rol_id: rol_id
        };

        $.post(url, json, function(data, status) {

            console.log('data:', data);

            $('#permission_id').val(null).trigger('change');
            $('#permission_id').empty().trigger("change");

            var option = new Option('Todos', 'Todos', false, false);
            $('#permission_id').append(option);

            for (var i = 0; i < data.length; i++) {
                var item = data[i];
                var id = item.id;
                var description = item.description;
                var option = new Option(description, id, false, false);
                $('#permission_id').append(option);
            }

            $('#permission_id').val(null).trigger('change');
            $('#permission_id').val('Todos').trigger("change");
        });
    }

    //-----------------------------------------------------------------------------------------------

    function search(button_name) {

        if ($('#rol_id').val() == 'Todos' && $('#permission_id').val() == 'Todos' && $('#user_id').val() == 'Todos') {
            swal({
                    title: 'Atención',
                    text: 'Seleccionar un filtro para la búsqueda.',
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
        } else {
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
        order: [
            [groupColumn, 'asc']
        ],
        displayLength: 5,
        drawCallback: function(settings) {
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

    $('.sub_datatables').DataTable({
        orderCellsTop: true,
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
        displayLength: 5
    });

    //-----------------------------------------------------------------------------------------------
</script>
@endsection