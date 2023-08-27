@extends('layout')

@section('title')
Gestión de Usuarios - Reporte
@endsection
@section('content')

<?php

$records = $data['lists']['records'];
$json = $data['lists']['json'];

//Combos
$users = $data['lists']['users'];
$atms = $data['lists']['atms'];
$supervisors = $data['lists']['supervisors'];

//Campos
$user_id = $data['inputs']['user_id'];
$user_supervisor_id = $data['inputs']['user_supervisor_id']; 
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
            <h3 class="box-title" style="font-size: 25px;">Gestión de Usuarios - Reporte
            </h3>
            <div class="box-tools pull-right">

                <button class="btn btn-info" type="button" title="Agregar un nuevo usuario" style="margin-right: 5px" id="add_user" name="add_user" onclick="modal_view('add', null)">
                    <i class="fa fa-user"></i> Agregar usuario
                </button>

                <button class="btn btn-info" type="button" title="Buscar según los filtros en los registros." style="margin-right: 5px" id="search" name="search" onclick="search('search')">
                    <i class="fa fa-search"></i> Buscar
                </button>

                <button class="btn btn-success" type="button" title="Convertir tabla en archivo excel." id="generate_x" name="generate_x" onclick="search('generate_x')">
                    <i class="fa fa-file-excel-o"></i> Exportar
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
                    {!! Form::open(['route' => 'atms_per_users_management', 'method' => 'POST', 'role' => 'form', 'id' => 'form_search']) !!}
                    <div class="row">
                        <div class="col-md-6">
                            <label for="user_id">Buscar por Usuario:</label>
                            <div class="form-group">
                                <select name="user_id" id="user_id" class="select2" style="width: 100%"></select>
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
                        <th></th>
                        <th style="width: 300px;">Supervisor</th>
                        <th>Usuario</th>
                    </tr>
                </thead>
                <tbody>
                    @if (count($records) > 0)
                        @foreach ($records as $item)

                        <?php

                        $id = $item['id'];
                        $description = $item['description'];
                        $doc_number = $item['doc_number'];
                        $username = $item['username'];
                        $email = $item['email'];
                        $phone_number = $item['phone_number'];
                        $user = $item['user'];
                        $created_at = $item['created_at'];
                        $updated_at = $item['updated_at'];
                        $atms_per_user = json_decode($item['atms_per_user'], true);
                        $atms_per_user_count = count($atms_per_user);

                        $item_user_supervisor_id = $item['user_supervisor_id'];
                        $item_user_supervisor_description = $item['user_supervisor_description'];
                        $item_user_supervisor_group = $item['user_supervisor_group'];

                        if ($item_user_supervisor_description == null) {
                            $item_user_supervisor_id = $id;
                            $item_user_supervisor_description = $user;
                        }


                        $parameters = [
                            'id' => $id,
                            'description' => $description,
                            'doc_number' => $doc_number,
                            'username' => $username,
                            'email' => $email,
                            'phone_number' => $phone_number,
                            'user' => $user,
                            'created_at' => $created_at,
                            'atms_per_user' => $atms_per_user,
                            'user_supervisor_id' => $item_user_supervisor_id
                        ];

                        $parameters = json_encode($parameters);


                        // Agregar sub tabla para ver los terminales por usuarios.
                        if ($atms_per_user[0]['atm_id'] == null) {
                            $atms_per_user = [];
                            $atms_per_user_count = 0;
                            $detail_aux = 'Sin terminales';
                        } else {
                            // Sub tabla de terminal

                            $detail_aux = "
                                <table class='table table-bordered table-hover dataTable'>
                                    <thead>
                                        <th>Estado</th>
                                        <th>ID</th>
                                        <th>Terminal</th>
                                        <th>Asignado el</th>
                                    </thead>
                                    <tbody>
                            ";

                            foreach ($atms_per_user as $sub_item) {
                                $sub_item_atm_id = $sub_item['atm_id'];
                                $sub_item_description = $sub_item['description'];
                                $sub_item_created_at = $sub_item['created_at'];
                                $sub_item_status_view = $sub_item['status_view'];

                                if ($sub_item_status_view == 'Activo') {
                                    $sub_item_status_color = 'green';
                                } else if ($sub_item_status_view == 'Inactivo') {
                                    $sub_item_status_color = 'red';
                                }

                            
                                $detail_aux .= "
                                    <tr>
                                        <td> <small class='center-block label bg-$sub_item_status_color'>$sub_item_status_view</small></td>
                                        <td> $sub_item_atm_id </td>
                                        <td> $sub_item_description </td>
                                        <td> $sub_item_created_at </td>
                                    </tr>
                                ";
                            }

                            $detail_aux .= "
                                </tbody>
                                </table>
                            ";
                        }

                        ?>

                        <tr>
                            <td>#{{ $item_user_supervisor_id }} {{ $item_user_supervisor_description }} <br/> {{ $item_user_supervisor_group }}</td>
                            <td></td>
                            <td>
                                <button class="btn btn-info" type="button" title="Agregar un nuevo usuario" style="margin-right: 5px" onclick="modal_view('edit', {{$parameters}})" id="button_atm_per_user_{{ $id }}">
                                    <i class="fa fa-pencil"></i>
                                </button>
                                #{{ $id }} {{ $user }} <br/><br/> {!! $detail_aux !!}
                            </td>
                        </tr>
                        @endforeach
                    @endif
                </tbody>
            </table>

        </div>
    </div>


    <div id="modal" class="modal fade" role="dialog" tabindex="-1" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document" style="background: white; border-radius: 5px; width: 1100px">
            <!-- Modal content-->
            <div class="modal-content" style="border-radius: 10px">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <div class="modal-title" style="font-size: 20px;" id="modal_title"></div>
                </div>
            </div>

            <div class="modal-body">

                <div class="row">
                    <div class="col-md-5">
                        <div class="box box-default" style="border: 1px solid #d2d6de;">
                            <div class="box-header with-border">
                                <h3 class="box-title">Información del usuario</h3>
                            </div>
                            <div class="box-body" style="overflow-x: hidden; overflow-y: scroll; max-height: 300px">
                                <div class="row">

                                    <div class="col-md-12">
                                        <label for="user_supervisor_id">Supervisor:</label>
                                        <div class="form-group">
                                            <select name="user_supervisor_id" id="user_supervisor_id" class="select2" style="width: 100%"></select>
                                        </div>
                                    </div>

                                    <div class="col-md-12">
                                        <label for="description">Nombre y Apellido:</label>

                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-pencil-square-o"></i></span>
                                            <input type="text" class="form-control" name="description" id="description" placeholder="Ingresar nombre y apellido"></input>
                                        </div> <br />
                                    </div>

                                    <div class="col-md-12">
                                        <label for="doc_number">Número de documento:</label>

                                        <div class="input-group">
                                            <span class="input-group-addon"><b>N</b></span>
                                            <input type="text" class="form-control" name="doc_number" id="doc_number" placeholder="Ingresar número de documento"></input>
                                        </div> <br />
                                    </div>

                                    <div class="col-md-12">

                                        <label for="username">Nombre de Usuario:</label>

                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-user"></i></span>
                                            <input type="text" class="form-control" name="username" id="username" placeholder="Ingresar nombre del usuario"></input>
                                        </div> <br />
                                    </div>

                                    <!--<div class="col-md-12">
                                        <label for="password">Contraseña:</label>
                                        <div class="form-group">
                                            <input type="password" class="form-control" name="password" id="password" placeholder="Ingresar contraseña del usuario"></input>
                                        </div>
                                    </div>-->

                                    <div class="col-md-12" id="email_or_phone_number_div" style="display: none">
                                        <label for="email">Enviar link a:</label>
                                        <div class="form-group">
                                            <!--<input type='checkbox' onclick="email_or_phone_number('email')" style='cursor: pointer' checked id="checkbox_email"> &nbsp; Correo electrónico <br />
                                            <input type='checkbox' onclick="email_or_phone_number('phone_number')" style='cursor: pointer' id="checkbox_phone_number"> &nbsp; Número de Teléfono-->

                                            <div class="checkbox">
                                                <label>
                                                    <input type='checkbox' onclick="email_or_phone_number('email')" style='cursor: pointer' checked id="checkbox_email"> Correo electrónico
                                                </label>
                                            </div>

                                            <div class="checkbox">
                                                <label>
                                                    <input type='checkbox' onclick="email_or_phone_number('phone_number')" style='cursor: pointer' id="checkbox_phone_number"> Número de Teléfono
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <!--<div class="col-md-12" id="email_div">
                                        <label for="email">Correo electrónico:</label>

                                        <div class="input-group">
                                            <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                                            <input type="email" class="form-control" name="email" id="email" placeholder="Ingresar correo electrónico"></input>
                                        </div> <br/>
                                    </div>-->

                                    <div class="col-md-12" id="email_div">
                                        <label for="email">Correo electrónico:</label>
                                        <div class="input-group input-group-lg" style="width: 100%;">
                                            <span class="input-group-addon"><i class="fa fa-envelope"></i></span>
                                            <input type="email" class="form-control" name="email" id="email" placeholder="Ingresar correo electrónico"></input>
                                            <span class="input-group-btn" id="span_send_email">
                                                <button type="button" class="btn btn-info btn-flat" title="Enviar link a correo ingresado." onclick="send('email')" id="button_send_email"><i class="fa fa-send"></i></button>
                                            </span>
                                        </div>
                                        <br />
                                    </div>

                                    <div class="col-md-12" id="phone_number_div">
                                        <label for="phone_number">Número de Teléfono:</label>
                                        <div class="input-group input-group-lg" style="width: 100%;">
                                            <span class="input-group-addon"><i class="fa fa-phone"></i></span>
                                            <input type="text" class="form-control" name="phone_number" id="phone_number" placeholder="Ingresar número de teléfono"></input>
                                            <span class="input-group-btn" id="span_send_phone_number">
                                                <button type="button" class="btn btn-info btn-flat" title="Enviar link al número de teléfono ingresado." onclick="send('phone_number')" id="button_send_phone_number"><i class="fa fa-send"></i></button>
                                            </span>
                                        </div>
                                        <br />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-7">
                        <div class="box box-default" style="border: 1px solid #d2d6de;">
                            <div class="box-header with-border">
                                <h3 class="box-title">Asignar o desasignar Terminales</h3>
                            </div>
                            <div class="box-body">

                                <div style="text-align: center; margin-bottom: 10px; font-size: 20px; display: none" id="assign_or_unassign_terminals_load">
                                    <div>
                                        <i class="fa fa-spin fa-refresh fa-2x" style="vertical-align: sub;"></i> &nbsp;
                                        Cargando...
                                    </div>
                                </div>

                                <div class="row" id="assign_or_unassign_terminals_datatable">
                                    <div class="col-md-12">
                                        <table class="table table-bordered table-hover dataTable" role="grid" id="modal_datatable">
                                            <thead>
                                                <tr>
                                                    <th>ID</th>
                                                    <th>Terminal</th>
                                                    <th>Asignar / Desasignar</th>
                                                </tr>
                                            </thead>
                                            <tbody>

                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>


            </div>

            <div class="modal-footer">
                <div class="btn-group">
                    <button type="button" class="btn btn-danger" id="modal_close" data-dismiss="modal" style="margin-right: 5px;"><i class="fa fa-times"></i> Cerrar</button>
                    <button type="button" class="btn btn-primary" id="modal_save" onclick="modal_save()"><i class="fa fa-save"></i> Guardar</button>
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

<!-- Iniciar objetos -->
<script type="text/javascript">
    // Identificar si es ADD O EDIT 
    var global_context = null;
    // ID del usuario a modificar
    var global_user_id = null;
    // Saber si va enviar por email o pho_number
    var global_email_or_phone_number = null;
    // Lista de atms
    var global_atm = [];

    // Habilitar opciones y listas para poder asignar atms .
    var global_super_user = @if (\Sentinel::getUser()->inRole('superuser')) true @else false @endif;

    function send(context) {

        //<i class="fa fa-send"></i>

        var email = $('#email').val();
        var phone_number = $('#phone_number').val();
        var description = $('#description').val();
        var message = '';

        $('#modal_close').prop('disabled', true);
        $('#modal_save').prop('disabled', true);

        $('#button_send_' + context).html('<i class="fa fa-spin fa-refresh"></i>');
        $('#button_send_' + context).prop('disabled', true);

        if (context == 'email') {

            if (email == '') {
                message = 'Ingresar correo electrónico para enviar link.';
            }

        } else if (context == 'phone_number') {

            if (phone_number == '') {
                message = 'Ingresar correo número de teléfono para enviar link.';
            }

        }

        if (message == '') {
            var url = '/atms_per_users_management_send/';

            var json = {
                _token: token,
                user_id: global_user_id,
                description: description,
                email_or_phone_number: context,
                email: email,
                phone_number: phone_number
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

                $('#modal_close').prop('disabled', false);
                $('#modal_save').prop('disabled', false);

                $('#button_send_' + context).html('<i class="fa fa-send"></i>');
                $('#button_send_' + context).prop('disabled', false);

                swal({
                        title: 'Atención',
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
                            if (error == false) {
                                location.reload();
                            }
                        }
                    }
                );
            });
        } else {

            $('#modal_close').prop('disabled', false);
            $('#modal_save').prop('disabled', false);

            $('#button_send_' + context).html('<i class="fa fa-send"></i>');
            $('#button_send_' + context).prop('disabled', false);

            swal({
                    title: 'Atención',
                    text: message,
                    type: 'warning',
                    showCancelButton: false,
                    confirmButtonColor: '#3c8dbc',
                    confirmButtonText: 'Aceptar',
                    cancelButtonText: 'No.',
                    closeOnClickOutside: false
                },
                function(isConfirm) {
                    if (isConfirm) {

                    }
                }
            );
        }
    }

    function email_or_phone_number(context) {

        global_email_or_phone_number = context;

        $('#email_or_phone_number_div').css('display', 'block');

        if (context == 'email') {

            $('#checkbox_email').prop('checked', true);
            $('#checkbox_phone_number').prop('checked', false);

            $('#email').val(null);
            $('#phone_number').val(null);

            $('#email_div').css('display', 'block');
            $('#phone_number_div').css('display', 'none');

        } else if (context == 'phone_number') {

            $('#checkbox_email').prop('checked', false);
            $('#checkbox_phone_number').prop('checked', true);

            $('#email').val(null);
            $('#phone_number').val(null);

            $('#email_div').css('display', 'none');
            $('#phone_number_div').css('display', 'block');
        }

        console.log('Validación por:', global_email_or_phone_number);
    }

    function modal_check_atm(id) {

        if ($('#modal_checkbox_' + id).is(':checked')) {
            $('#modal_checkbox_' + id).html('&nbsp; Seleccionado');
        } else {
            $('#modal_checkbox_' + id).html('&nbsp; No seleccionado');
        }

    }

    //AGREGAR LAS VALIDACIONES PARA 

    function modal_save() {

        $('#modal_save').html('<i class="fa fa-spin fa-refresh"></i>');
        $('#modal_save').prop('disabled', true);
        $('#modal_close').css('display', 'none');

        var description = $('#description').val();
        var doc_number = $('#doc_number').val();
        var username = $('#username').val();
        var email = $('#email').val();
        var phone_number = $('#phone_number').val();
        var user_supervisor_id = $('#user_supervisor_id').val()

        console.log('user_supervisor_id a guardar', user_supervisor_id);

        var message = '';

        if (user_supervisor_id !== '' && user_supervisor_id !== null) {
            if (description !== '') {
                if (doc_number !== '') {
                    if (username !== '') {
                        if (email !== '' || phone_number !== '') {

                            var atms_aux = [];
                            var atms_selected = true;

                            for (var i = 0; i < global_atm.length; i++) {

                                var item = global_atm[i];
                                var atm_id = item.atm_id;
                                var status = false;

                                if ($('#modal_checkbox_' + atm_id).is(':checked')) {
                                    status = true;
                                    atms_selected = true;
                                }

                                global_atm[i].status = status;
                            }


                            if (atms_selected) {
                                var url = '/atms_per_users_management_save/';

                                var json = {
                                    _token: token,

                                    context: global_context,

                                    user_id: global_user_id,
                                    user_supervisor_id: user_supervisor_id,

                                    description: description,
                                    doc_number: doc_number,
                                    username: username,
                                    //password: password,
                                    email: email,
                                    phone_number: phone_number,

                                    email_or_phone_number: global_email_or_phone_number,

                                    atms_selected: global_atm
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

                                    $('#modal_save').html('<i class="fa fa-save"></i> Guardar');
                                    $('#modal_save').prop('disabled', false);
                                    $('#modal_close').css('display', 'block');

                                    swal({
                                            title: 'Atención',
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
                                                if (error == false) {
                                                    document.location.href = 'atms_per_users_management';
                                                }
                                            }
                                        }
                                    );
                                });
                            } else {
                                message = 'Seleccionar por lo menos una terminal.';
                            }
                        } else {
                            message = 'Ingresar metodo de validación: Correo o Teléfono.';
                        }
                    } else {
                        message = 'Ingresar nombre de usuario.';
                    }
                } else {
                    message = 'Ingresar número de documento.';
                }
            } else {
                message = 'Ingresar nombre y apellido.';
            }
        } else {
            message = 'Seleccionar supervisor.';
        }

        // Si el mensaje tiene algo no pasó todas las validaciones
        if (message !== '') {
            $('#modal_save').html('<i class="fa fa-save"></i> Guardar');
            $('#modal_save').prop('disabled', false);
            $('#modal_close').css('display', 'block');

            //swal('Atención', message, 'warning');

            swal({
                    title: 'Atención',
                    text: message,
                    type: 'warning',
                    showCancelButton: false,
                    confirmButtonColor: '#3c8dbc',
                    confirmButtonText: 'Aceptar',
                    cancelButtonText: 'No.',
                    closeOnClickOutside: false
                },
                function(isConfirm) {
                    if (isConfirm) {

                    }
                }
            );
        }
    }


    function modal_view(context, parameters) {

        console.log('context:', context);
        console.log('parameters modal_view:', parameters);

        global_context = context;
        global_user_id = -1;
        global_email_or_phone_number = 'email';

        //--------------------------------------------------------------------------------------------------

        var data_table_config = {
            fixedHeader: true,
            pageLength: 5,
            lengthMenu: [
                1, 2, 5, 10, 20, 30, 50, 70, 100, 150, 300, 500, 1000, 1500, 2000
            ],
            dom: '<"pull-left"f><"pull-right"l>tip',
            language: {
                "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
            },
            scroller: true,
            displayLength: 5,
            order: [],
            columnDefs: [{
                targets: 'no-sort',
                orderable: false,
            }]
        }

        if ($.fn.DataTable.isDataTable('#modal_datatable')) {
            $('#modal_datatable').DataTable().destroy();
        }

        $('#modal_datatable tbody').html('');

        $('#modal').modal();

        //--------------------------------------------------------------------------------------------------

        if (global_context == 'add') {

            $('#modal_title').html('Agregar nuevo usuario:');
            $('#button_send_email').css('display', 'none');
            $('#button_send_phone_number').css('display', 'none');

            email_or_phone_number('email');

            $('#description').val(null);
            $('#doc_number').val(null);
            $('#username').val(null);
            $('#email').val(null);
            $('#phone_number').val(null);

            $('#user_supervisor_id').val(null).trigger('change');
            $('#user_supervisor_id').prop('disabled', false);

            var table = $('#modal_datatable').DataTable(data_table_config);
            table.column(0).data().unique();

        } else if (global_context == 'edit') {

            $('#modal_title').html('Modificar usuario:');
            $('#button_send_email').css('display', 'block');
            $('#button_send_phone_number').css('display', 'block');
            $('#email_or_phone_number_div').css('display', 'none');
            $('#email_div').css('display', 'block');
            $('#phone_number_div').css('display', 'block');

            var id = parameters.id; // Id del USUARIO
            global_user_id = id; // Asignación del id de usuario

            var description = parameters.description;
            var doc_number = parameters.doc_number;
            var username = parameters.username;
            var email = parameters.email;
            var phone_number = parameters.phone_number;
            var atms_per_user = parameters.atms_per_user;

            var user_supervisor_id_aux = parameters.user_supervisor_id; // Asignamos el del registro
        
            $('#button_atm_per_user_' + id).html('<i class="fa fa-spin fa-refresh"></i>');
            $('#description').val(description);
            $('#doc_number').val(doc_number);
            $('#username').val(username);
            $('#email').val(email);
            $('#phone_number').val(phone_number);

            $('#user_supervisor_id').val(null).trigger('change');
            $('#user_supervisor_id').val(user_supervisor_id_aux).trigger('change');

            if (global_super_user == false) {
                //Si es un usuario común le bloqueamos el campo de supervisor.
                $('#user_supervisor_id').prop('disabled', true);
            }

            /*for (var i = 0; i < atms_per_user.length; i++) {

                var item = atms_per_user[i];
                var atm_id = item.atm_id;
                var status = item.status;

                console.log('atm_id', atm_id, 'status', status);

                if (status) {
                    $('#modal_checkbox_' + atm_id).prop('checked', true);
                }
            }*/

            //--------------------------------------------------------------------------------------------------

            var url = '/get_atms_per_user/';

            var json = {
                _token: token,
                user_id: global_user_id,
                user_supervisor_id: user_supervisor_id_aux
            }

            console.log('Campos a enviar a get_atms_per_user:', json);

            $.post(url, json, function(data) {
                console.log('data: ', data);

                var error = data.error;
                var text = data.message;
                var response = data.response;

                global_atm = response;

                var type = '';

                if (error == true) {
                    type = 'error';
                } else {
                    type = 'success';
                }

                //---------------------------------------------------------

                var rows = '';

                var atms_per_user = response;

                for (var i = 0; i < atms_per_user.length; i++) {

                    var item = response[i];
                    var atm_id = item.atm_id;
                    var description = item.description;
                    var status = item.status;
                    var checked = '';

                    if (status) {
                        //$('#modal_checkbox_' + atm_id).prop('checked', true);
                        checked = 'checked';
                    }

                    rows += '<tr>';
                    rows += '<td>#' + atm_id + '</td>';
                    rows += '<td>' + description + '</td>';
                    rows += '<td><input type="checkbox" style="cursor: pointer" id="modal_checkbox_' + atm_id + '" ' + checked + '></td>';
                    rows += '</tr>';
                }

                $('#modal_datatable tbody').append(rows);

                var table = $('#modal_datatable').DataTable(data_table_config);
                table.column(0).data().unique();

                $('#button_atm_per_user_' + id).html('<i class="fa fa-pencil"></i>');

                //Si el usuario ya tiene asignado terminales no le dejamos modificar.
                if (atms_per_user.length > 0) {
                    $('#user_supervisor_id').prop('disabled', true);
                }
                

            }).error(function(error) {
                swal({
                        title: 'Error al querer datos.',
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
                            document.location.href = 'atms_per_users_management';
                        }
                    }
                );
            });
        }

        console.log('Valor del global_user_id:', global_user_id);
        console.log('Valor del user_supervisor_id_aux:', user_supervisor_id_aux);

    }

    function get_atms_per_user(user_supervisor_id_aux) {

        $('#assign_or_unassign_terminals_load').css('display', 'block');
        $('#assign_or_unassign_terminals_datatable').css('display', 'none');

        var data_table_config = {
            fixedHeader: true,
            pageLength: 5,
            lengthMenu: [
                1, 2, 5, 10, 20, 30, 50, 70, 100, 150, 300, 500, 1000, 1500, 2000
            ],
            dom: '<"pull-left"f><"pull-right"l>tip',
            language: {
                "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
            },
            scroller: true,
            displayLength: 5,
            order: [],
            columnDefs: [{
                targets: 'no-sort',
                orderable: false,
            }]
        }

        if ($.fn.DataTable.isDataTable('#modal_datatable')) {
            $('#modal_datatable').DataTable().destroy();
        }

        $('#modal_datatable tbody').html('');


        var url = '/get_atms_per_user/';

        var json = {
            _token: token,
            user_id: global_user_id,
            user_supervisor_id: user_supervisor_id_aux
        }

        console.log('Campos a enviar:', json);

        $.post(url, json, function(data) {
            console.log('data: ', data);

            var error = data.error;
            var text = data.message;
            var response = data.response;

            global_atm = response;

            //---------------------------------------------------------

            var rows = '';

            for (var i = 0; i < response.length; i++) {

                var item = response[i];
                var atm_id = item.atm_id;
                var description = item.description;
                var status = item.status;
                var checked = '';

                if (status) {
                    checked = 'checked';
                }

                rows += '<tr>';
                rows += '<td>#' + atm_id + '</td>';
                rows += '<td>' + description + '</td>';
                rows += '<td><input type="checkbox" style="cursor: pointer" id="modal_checkbox_' + atm_id + '" ' + checked + '></td>';
                rows += '</tr>';
            }

            $('#modal_datatable tbody').append(rows);

            var table = $('#modal_datatable').DataTable(data_table_config);
            table.column(0).data().unique();

            $('#assign_or_unassign_terminals_load').css('display', 'none');
            $('#assign_or_unassign_terminals_datatable').css('display', 'block');

        }).error(function(error) {
            swal({
                    title: 'Error al querer datos.',
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
                        document.location.href = 'atms_per_users_management';
                    }
                }
            );
        });
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
    /*var data_table_config = {
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

    var table = $('#datatable_1').DataTable(data_table_config);*/


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
                .each(function(data, i) {

                    //transaction = $.number(transaction, 0, ',', '.');

                    if (last !== data) {

                        var color = '#d2d6de';

                        var td = $('<td>');
                        td.attr({
                            'colspan': '3',
                            'style': 'color: #333 !important'
                        }).append(data);

                        var tr = $('<tr>');
                        tr.attr({
                            'class': 'group',
                            'style': 'background-color:' + color + ' !important; font-weight: bold; cursor: pointer'
                        }).append(td);

                        $(rows).eq(i).before(tr);

                        last = data;
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

    var data_table_config = {
        fixedHeader: true,
        pageLength: 5,
        lengthMenu: [
            1, 2, 5, 10, 20, 30, 50, 70, 100, 150, 300, 500, 1000, 1500, 2000
        ],
        dom: '<"pull-left"f><"pull-right"l>tip',
        language: {
            "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
        },
        scroller: true,
        displayLength: 5,
        order: [],
        columnDefs: [{
            targets: 'no-sort',
            orderable: false,
        }]
    }

    $('#modal_datatable').DataTable(data_table_config);

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
    var supervisors = {!!$supervisors!!};

    //var json = {!!$json!!};
    //var users = {!!$users!!};
    //var atms = {!!$atms!!};

    //json = JSON.parse(json);
    json = JSON.stringify(json);
    $('#json').val(json);

    //-----------------------------------------------------------------------------------------------

    window.onload = function() {

        $('.select2').select2();

        //----------------------------------------------------------

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

        // Para seleccionar un supervisor en la alta y modificación

        $('#user_supervisor_id').val(null).trigger('change');
        $('#user_supervisor_id').empty().trigger("change");

        for (var i = 0; i < supervisors.length; i++) {
            var item = supervisors[i];
            var id = item.id;
            var description = item.description;
            var option = new Option(description, id, false, false);
            $('#user_supervisor_id').append(option);
        }

        $('#user_supervisor_id').val(null).trigger('change');
        $('#user_supervisor_id').val("{{ $user_supervisor_id }}").trigger('change');

        //----------------------------------------------------------

        $('.select2').on('select2:select', function(e) {
            var id = e.currentTarget.id;

            switch (id) {
                case 'user_supervisor_id':
                    get_atms_per_user($('#user_supervisor_id').val());
                    break;
            }
        });
    };
</script>
@endsection

<style>
    .modal-footer .btn-group .btn+.btn {
        /* margin-left: -1px; */
    }
</style>