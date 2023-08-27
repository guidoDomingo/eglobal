@extends('layout')

@section('title')
    USSD - Lista negra - Reporte
@endsection
@section('content')
    <section class="content-header">
        <h1>
            USSD - Lista negra - Reporte
            <small>Números de teléfonos</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">USSD - Lista negra - Reporte</a></li>
        </ol>
    </section>

    <section class="content">

        <div class="row">
            <div class="col-md-12">
                @include('partials._flashes')
                @include('partials._messages')
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">Búsqueda personalizada</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                    class="fa fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="box-body">
                        {!! Form::open(['route' => 'ussd_black_list_search', 'method' => 'POST', 'role' => 'form', 'id' => 'form_search']) !!}
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="timestamp">Fecha:</label>
                                    <input type="text" class="form-control" style="display:block" id="timestamp"
                                        name="timestamp" placeholder="Ingrese la fecha"></input>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="phone_number">Teléfono:</label>
                                    <input type="number" class="form-control" id="phone_number" name="phone_number"
                                        placeholder="Ejemplo: 0981123456"></input>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label for="menu_ussd_black_list_reason_id">Motivo:</label>
                                <div class="form-group">
                                    <select class="form-control select2" id="menu_ussd_black_list_reason_id"
                                        name="menu_ussd_black_list_reason_id"></select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-4">
                                <label for="menu_ussd_operator_id">Lista negra para:</label>
                                <div class="form-group">
                                    <select class="form-control select2" id="menu_ussd_operator_id"
                                        name="menu_ussd_operator_id"></select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <label for="search">Buscar...</label>
                                <br />
                                <button type="submit" class="btn btn-primary"
                                    title="Buscar según los filtros en los registros." id="search" name="search">
                                    <span class="fa fa-search" aria-hidden="true"></span> &nbsp; Búsqueda
                                </button>
                            </div>

                            <div class="col-md-6">

                            </div>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">
                            Lista negra &nbsp; &nbsp;
                            <a class="btn-sm btn-primary active" role="button" title="Agregar nuevo teléfono a lista negra."
                                data-toggle="modal" data-target="#modal_add">
                                <span class="fa fa-plus" aria-hidden="true"></span> Agregar teléfono
                            </a>
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                    class="fa fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="box-body">
                        @if (isset($data['list']))
                            <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_1">
                                <thead>
                                    <tr role="row">
                                        <th>Número</th>
                                        <th>Teléfono</th>
                                        <th>Operadora</th>
                                        <th>Motivo</th>
                                        <th>Entrada</th>
                                        <th>Actualización</th>
                                        <th>Estado</th>
                                        <th>Opciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $list = $data['list']; ?>

                                    @for ($i = 0; $i < count($list); $i++)

                                        <?php
                                        $item = $list[$i];
                                        $parameters = json_encode($item);

                                        $id = $item['id'];
                                        $phone_number = $item['phone_number'];
                                        $status = $item['status'];
                                        $menu_ussd_black_list_reason_id = $item['menu_ussd_black_list_reason_id'];
                                        $menu_ussd_black_list_reason = $item['menu_ussd_black_list_reason'];
                                        $created_at = $item['created_at'];
                                        $updated_at = $item['updated_at'];
                                        $user = $item['user'];
                                        $menu_ussd_operator_id = $item['menu_ussd_operator_id'];
                                        $menu_ussd_operator = $item['menu_ussd_operator'];
                                        ?>

                                        <tr>
                                            <td>{{ $id }}</td>
                                            <td>{{ $phone_number }}</td>
                                            <td>{{ $menu_ussd_operator }}</td>
                                            <td>{{ $menu_ussd_black_list_reason }}</td>
                                            <td>{{ $created_at }}</td>
                                            <td>{{ $updated_at }}</td>
                                            <td>{{ $status }}</td>

                                            <td style="text-align: center">
                                                @if (Sentinel::hasAccess('ussd_black_list_edit'))
                                                    <button class="btn btn-default" title="Editar registro"
                                                        style="border-radius: 3px;"
                                                        onclick="click_in_view_and_edit({{ $parameters }});">
                                                        <i class="fa fa-pencil"></i>
                                                @endif
                                            </td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        @else
                            <div class="alert alert-danger" role="alert">
                                No hay registros de <b> teléfonos</b>.
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal add-->
        <div id="modal_add" class="modal fade" role="dialog" tabindex="-1" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document"
                style="width: 500px; background: white; border-radius: 5px">
                <!-- Modal content-->
                <div class="modal-content" style="border-radius: 10px">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <div class="modal-title" style="font-size: 20px;">
                            Agregar teléfono &nbsp; <small> <b> </b> </small>
                        </div>
                    </div>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="phone_number_add">Teléfono:</label>
                                <input type="text" class="form-control" style="display:block" id="phone_number_add"
                                    name="phone_number_add" placeholder="Ingrese el nuevo número de teléfono"></input>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label for="menu_ussd_black_list_reason_id_add">Motivo:</label>
                            <div class="form-group">
                                <select class="form-control select2" id="menu_ussd_black_list_reason_id_add"
                                    name="menu_ussd_black_list_reason_id_add"></select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label for="menu_ussd_operator_id_add">Lista negra para:</label>
                            <div class="form-group">
                                <select class="form-control select2" id="menu_ussd_operator_id_add"
                                    name="menu_ussd_operator_id_add"></select>
                            </div>
                        </div>

                        <br />
                        <div class="col-md-12">
                            <div style="float:right">
                                @if (Sentinel::hasAccess('ussd_black_list_add'))
                                    <div class="btn-group mr-2" role="group">
                                        <button class="btn btn-danger pull-right" title="Cancela totalmente la transacción."
                                            style="margin-right: 10px" id="cancel_add">
                                            <span class="fa fa-remove" aria-hidden="true"></span>
                                            &nbsp; Cancelar
                                        </button>
                                    </div>
                                    <div class="btn-group mr-2" role="group">
                                        <button class="btn btn-primary pull-right"
                                            title="Confirma para agregar el registro." id="confirm_add"
                                            onclick="add_black_list()">
                                            <span class="fa fa-save" aria-hidden="true"></span>
                                            &nbsp; Confirmar
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div id="modal_edit" class="modal fade" role="dialog" tabindex="-1" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-dialog-centered modal-lg" role="document"
                style="width: 500px; background: white; border-radius: 5px">
                <!-- Modal content-->
                <div class="modal-content" style="border-radius: 10px">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <div class="modal-title" style="font-size: 20px;">
                            Modificar registro &nbsp; <small> <b> </b> </small>
                        </div>
                    </div>
                </div>

                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                <label for="phone_number_edit">Teléfono:</label>
                                <input type="text" class="form-control" style="display:block" id="phone_number_edit"
                                    name="phone_number_edit" placeholder="Número de teléfono"></input>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label for="menu_ussd_black_list_reason_id_edit">Motivo:</label>
                            <div class="form-group">
                                <select class="form-control select2" id="menu_ussd_black_list_reason_id_edit"
                                    name="menu_ussd_black_list_reason_id_edit"></select>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label for="menu_ussd_operator_id_edit">Lista negra para:</label>
                            <div class="form-group">
                                <select class="form-control select2" id="menu_ussd_operator_id_edit"
                                    name="menu_ussd_operator_id_edit"></select>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <label>Estado:</label> <br>
                            <input type="checkbox" title="Activar o Inactivar de lista negra." style="cursor: pointer"
                                id="status_edit"> &nbsp; Activar / Inactivar
                        </div>

                        <br />
                        <div class="col-md-12">
                            <div style="float:right">
                                @if (Sentinel::hasAccess('ussd_black_list_edit'))
                                    <div class="btn-group mr-2" role="group">
                                        <button class="btn btn-danger pull-right" title="Cancela totalmente la transacción."
                                            style="margin-right: 10px" id="cancel_edit">
                                            <span class="fa fa-remove" aria-hidden="true"></span>
                                            &nbsp; Cancelar
                                        </button>
                                    </div>
                                    <div class="btn-group mr-2" role="group">
                                        <button class="btn btn-primary pull-right"
                                            title="Confirma la modificación el registro." id="confirm_edit"
                                            onclick="edit_black_list()">
                                            <span class="fa fa-save" aria-hidden="true"></span>
                                            &nbsp; Confirmar
                                        </button>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>
@endsection

@section('js')

    <!-- datatables -->
    <link rel="stylesheet" href="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.css">
    <script src="/bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.min.js"></script>

    <!-- date-range-picker -->
    <link href="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker-bs3.css" rel="stylesheet"
        type="text/css" />
    <script src="/bower_components/admin-lte/plugins/daterangepicker/moment.min.js"></script>
    <script src="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker.js"></script>

    <!-- bootstrap datepicker -->
    <script src="/bower_components/admin-lte/plugins/datepicker/bootstrap-datepicker.js"></script>

    <!-- select2 -->
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
    <script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>

    <!-- Iniciar objetos -->
    <script type="text/javascript">
        var regex = /^\(?([0][9][6-9][1-9])([0-9]){6}$/;
        var id = null;

        //Agrega el registro
        function add_black_list() {

            var save = 'yes';
            var message_text = '';

            if ($('#menu_ussd_operator_id_add').val() == '') {
                save = 'not';
                message_text = 'Operadora (campo obligatorio)';
            }

            if ($('#menu_ussd_black_list_reason_id_add').val() == '') {
                save = 'not';
                message_text = 'Motivo (campo obligatorio)';
            }

            if ($('#phone_number_add').val() == '') {
                save = 'not';
                message_text = 'Teléfono (campo obligatorio)';
            }

            if (save == 'yes') {
                $("#modal_add").modal('hide');

                var url = '/ussd/black_list/ussd_black_list_add/';

                var json = {
                    _token: token,

                    timestamp: $('#timestamp').val(),
                    phone_number: $('#phone_number').val(),
                    menu_ussd_black_list_reason_id: $('#menu_ussd_black_list_reason_id').val(),
                    menu_ussd_operator_id: $('#menu_ussd_operator_id').val(),

                    phone_number_add: $('#phone_number_add').val(),
                    menu_ussd_black_list_reason_id_add: $('#menu_ussd_black_list_reason_id_add').val(),
                    menu_ussd_operator_id_add: $('#menu_ussd_operator_id_add').val(),
                };

                $.post(url, json, function(data, status) {
                    var error = data.error;
                    var message = data.message;
                    var type = '';
                    var text = '';

                    if (error == true) {
                        type = 'error';
                        text = 'Ocurrió un problema al agregar el registro';
                    } else {
                        type = 'success';
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
                                $('#search').trigger('click');
                            }
                        }
                    );
                }).error(function(error) {
                    console.log('ERROR AL AGREGAR:', error);
                });
            } else {
                swal({
                        title: message_text,
                        text: 'Completar todos los campos.',
                        type: 'error',
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

        function validate_phone(input, button) {
            var text = input.val();

            if (text.match(regex)) {
                input.css({
                    "border": "1px solid green"
                });

                button.css({
                    "display": "block"
                });
            } else {
                input.css({
                    "border": "1px solid red"
                });

                button.css({
                    "display": "none"
                });
            }
        }

        function click_in_view_and_edit(parameters) {

            id = parameters['id'];
            var phone_number_edit = parameters['phone_number'];
            var menu_ussd_black_list_reason_id_edit = parameters['menu_ussd_black_list_reason_id'];
            var menu_ussd_operator_id_edit = parameters['menu_ussd_operator_id'];
            var status_edit = parameters['status'];

            $("#phone_number_edit").val(phone_number_edit);

            $('#menu_ussd_black_list_reason_id_edit').val(
                menu_ussd_black_list_reason_id_edit
            ).trigger('change');

            $('#menu_ussd_operator_id_edit').val(
                menu_ussd_operator_id_edit
            ).trigger('change');

            if (status_edit == 'Activo') {
                $('#status_edit').prop('checked', true);
            } else {
                $('#status_edit').prop('checked', false);
            }

            validate_phone($("#phone_number_edit"), $("#confirm_edit"));

            $('#modal_edit').modal('show');
        }

        //Modifica el registro
        function edit_black_list() {

            var save = 'yes';
            var message_text = '';

            if ($('#menu_ussd_operator_id_edit').val() == '') {
                save = 'not';
                message_text = 'Operadora (campo obligatorio)';
            }

            if ($('#menu_ussd_black_list_reason_id_edit').val() == '') {
                save = 'not';
                message_text = 'Motivo (campo obligatorio)';
            }

            if ($('#phone_number_edit').val() == '') {
                save = 'not';
                message_text = 'Teléfono (campo obligatorio)';
            }

            if (save == 'yes') {
                $("#modal_edit").modal('hide');

                var url = '/ussd/black_list/ussd_black_list_edit/';

                var json = {
                    _token: token,

                    timestamp: $('#timestamp').val(),
                    phone_number: $('#phone_number').val(),
                    menu_ussd_black_list_reason_id: $('#menu_ussd_black_list_reason_id').val(),
                    menu_ussd_operator_id: $('#menu_ussd_operator_id').val(),

                    id: id,
                    phone_number_edit: $('#phone_number_edit').val(),
                    menu_ussd_black_list_reason_id_edit: $('#menu_ussd_black_list_reason_id_edit').val(),
                    menu_ussd_operator_id_edit: $('#menu_ussd_operator_id_edit').val(),
                    status_edit: $('#status_edit').is(':checked')
                };

                $.post(url, json, function(data, status) {
                    var error = data.error;
                    var message = data.message;
                    var type = '';
                    var text = '';

                    if (error == true) {
                        type = 'error';
                        text = 'Ocurrió un problema al modificar el registro';
                    } else {
                        type = 'success';
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
                                $('#search').trigger('click');
                            }
                        }
                    );
                }).error(function(error) {
                    console.log('ERROR AL MODIFICAR:', error);
                });
            } else {
                swal({
                        title: message_text,
                        text: 'Completar todos los campos.',
                        type: 'error',
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

        //Obtener los operadores
        $.get("/ussd/black_list/ussd_black_list_operador", function(data) {
            $('#menu_ussd_operator_id').append($('<option>', {
                value: '',
                text: 'Todos'
            }));

            $('#menu_ussd_operator_id_add').append($('<option>', {
                value: '',
                text: 'Seleccionar opción'
            }));

            $('#menu_ussd_operator_id_edit').append($('<option>', {
                value: '',
                text: 'Seleccionar opción'
            }));

            for (var i in data) {
                var id = data[i].id;
                var description = data[i].description;

                $('#menu_ussd_operator_id').append($('<option>', {
                    value: id,
                    text: description
                }));

                $('#menu_ussd_operator_id_add').append($('<option>', {
                    value: id,
                    text: description
                }));

                $('#menu_ussd_operator_id_edit').append($('<option>', {
                    value: id,
                    text: description
                }));
            }

            $('#menu_ussd_operator_id').val(
                "{{ $data['filters']['menu_ussd_operator_id'] }}"
            ).trigger('change');

            $('#menu_ussd_operator_id_add').val('').trigger('change');

            $('#menu_ussd_operator_id_edit').val('').trigger('change');
        });

        //Obtener los registros de atms
        $.get("/ussd/black_list/ussd_black_list_reason", function(data) {
            $('#menu_ussd_black_list_reason_id').append($('<option>', {
                value: '',
                text: 'Todos'
            }));

            $('#menu_ussd_black_list_reason_id_add').append($('<option>', {
                value: '',
                text: 'Seleccionar opción'
            }));

            $('#menu_ussd_black_list_reason_id_edit').append($('<option>', {
                value: '',
                text: 'Seleccionar opción'
            }));

            for (var i in data) {
                var id = data[i].id;
                var description = data[i].description;

                $('#menu_ussd_black_list_reason_id').append($('<option>', {
                    value: id,
                    text: description
                }));

                $('#menu_ussd_black_list_reason_id_add').append($('<option>', {
                    value: id,
                    text: description
                }));

                $('#menu_ussd_black_list_reason_id_edit').append($('<option>', {
                    value: id,
                    text: description
                }));
            }

            $('#menu_ussd_black_list_reason_id').val(
                "{{ $data['filters']['menu_ussd_black_list_reason_id'] }}"
            ).trigger('change');

            $('#menu_ussd_black_list_reason_id_add').val('').trigger('change');

            $('#menu_ussd_black_list_reason_id_edit').val('').trigger('change');
        });


        //Asignar nuevamente el valor al campo de búsqueda
        $('#phone_number').val("{{ $data['filters']['phone_number'] }}");
        $('#timestamp').val("{{ $data['filters']['timestamp'] }}");

        $('#timestamp').daterangepicker({
            'format': 'DD/MM/YYYY HH:mm:ss',
            'startDate': moment().startOf('month'),
            'endDate': moment().endOf('month'),
            'timePicker': true,
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
        }, function() {});

        //Datatable config
        var data_table_config = {
            //custom
            orderCellsTop: true,
            fixedHeader: true,
            pageLength: 20,
            lengthMenu: [5, 10, 20, 30, 50, 70, 100, 250, 500, 1000],
            dom: '<"pull-left"f><"pull-right"l>tip',
            language: {
                "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
            },
            scroller: true
        }

        var table = $('#datatable_1').DataTable(data_table_config);

        //Evento click en cancel
        $("#cancel_add").click(function() {
            $("#modal_add").modal('hide');
        });

        $("#cancel_edit").click(function() {
            $("#modal_edit").modal('hide');
        });

        //Esconder la alerta después de 5 segundos. 
        $(".alert").delay(5000).slideUp(300);

        $('[data-toggle="popover"]').popover();

        //Estilos del combo
        $('.select2').select2({
            width: '99%'
        });

        //Validación del combo.
        $("#phone_number_add").keyup(function(event) {
            validate_phone($("#phone_number_add"), $("#confirm_add"));
        });

        $("#phone_number_edit").keyup(function(event) {
            validate_phone($("#phone_number_edit"), $("#confirm_edit"));
        });

    </script>
@endsection
