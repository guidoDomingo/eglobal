@extends('layout')

@section('title')
    Verificador para conciliación de transacciones
@endsection

@section('content')
    <section class="content-header">
        <h1>
            Verificador para conciliación de transacciones
            <small>Lectura de archivo y comparación de registros</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Conciliadores</a></li>
            <li class="active">Verificador</li>
        </ol>
    </section>
    <section class="content">

        @include('partials._flashes')
        @include('partials._messages')

        <div id="div_load" style="text-align: center; margin-bottom: 10px; font-size: 20px; height: 350px;">
            <div>
                <i class="fa fa-spin fa-refresh fa-2x" style="vertical-align: sub;"></i> &nbsp;
                Cargando...

                <p id="rows_loaded" title="Filas cargadas"></p>
            </div>
        </div>

        <div id="filters_and_files" style="display: none">
            <div class="row">
                <div class="col-md-12">

                    <div class="box box-default">
                        <div class="box-header with-border">
                            <h3 class="box-title">Filtros de comparación:</h3>
                            <div class="box-tools pull-right">
                                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                        class="fa fa-minus"></i></button>
                            </div>
                        </div>

                        <div class="box-body">
                            <div class="row">

                                {!! Form::open(['route' => 'transaction_conciliator_validate', 'method' => 'POST', 'role' => 'form', 'id' => 'form_validate', 'enctype' => 'multipart/form-data']) !!}
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="timestamp">Rango de fecha:</label>
                                        <input type="text" class="form-control" style="display:block" id="timestamp"
                                            name="timestamp" placeholder="Ingrese la fecha"></input>
                                    </div>
                                </div>

                                <div class="col-md-2">
                                    <label for="record_limit">Límite:</label>
                                    <div class="form-group">
                                        <select class="form-control select2" id="record_limit" name="record_limit">
                                            <option value="" selected>Sin límite</option>
                                            <option value="1">1 Registro</option>
                                            <option value="2">2 Registros</option>
                                            <option value="5">5 Registros</option>
                                            <option value="10">10 Registros</option>
                                            <option value="20">20 Registros</option>
                                            <option value="30">30 Registros</option>
                                            <option value="50">50 Registros</option>
                                            <option value="70">70 Registros</option>
                                            <option value="100">100 Registros</option>
                                            <option value="150">150 Registros</option>
                                            <option value="200">200 Registros</option>
                                            <option value="250">250 Registros</option>
                                            <option value="300">300 Registros</option>
                                            <option value="500">500 Registros</option>
                                            <option value="700">700 Registros</option>
                                            <option value="1000">1000 Registros</option>
                                            <option value="1500">1500 Registros</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <label for="files">Seleccionar archivo
                                        <small> (Máximo de 1 archivo, extensiones adminitidas: .xlsx, .xls, .csv) </small>

                                    </label>
                                    <div class="form-group">
                                        <input type="file" class="form-control" id="files" name="files[]" multiple=true
                                            accept=".xls,.xlsx,.csv" placeholder="Seleccione archivo."></input>
                                    </div>
                                </div>

                                {!! Form::close() !!}

                                <div class="col-md-4"></div>

                                <div class="col-md-2">
                                    <button class="btn btn-primary" style="float: right" id="validate">
                                        <i class="fa fa-check"></i> Realizar comparación
                                    </button>
                                </div>

                                <div class="col-md-2">
                                    {!! Form::open(['route' => 'transaction_conciliator_export', 'method' => 'POST', 'role' => 'form', 'id' => 'form_export', 'enctype' => 'multipart/form-data']) !!}
                                    <input name="json" id="json" type="hidden">
                                    {!! Form::close() !!}

                                    <button class="btn btn-success" title="Convertir tabla en archivo excel." id="export">
                                        <span class="fa fa-file-excel-o " aria-hidden="true"></span> &nbsp; Exportar
                                    </button>
                                </div>

                                <div class="col-md-4"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @if ($data['open_modal'] == 'si')
                <!-- TAB PANNEL -->
                <div class="panel with-nav-tabs">
                    <div class="panel-heading">
                        <ul class="nav nav-tabs" id="myTabs">
                            <li class="active" title="Lista de los registros en el rango de fecha seleccionado.">
                                <a href="#tab1primary" data-toggle="tab">Registros del sistema</a>
                            </li>
                            <li title="Archivo seleccionado.">
                                <a href="#tab2primary" data-toggle="tab">Archivo comparado</a>
                            </li>
                        </ul>
                    </div>
                    <div class="panel-body">
                        <div class="tab-content">
                            <div class="tab-pane fade in active" id="tab1primary">
                                <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_1">
                                    <thead>
                                        <tr role="row">
                                            <th>Proveedor</th>
                                            <th>Marca</th>
                                            <th>Servicio</th>
                                            <th>Creación</th>
                                            <th>Actualización</th>
                                            <th>Monto</th>
                                            <th>Transacción</th>
                                            <th>Estado(Sistema)</th>
                                            <th>Ingreso(Incomes)</th>
                                            <th>Datos(Archivo)</th>
                                        </tr>
                                    </thead>
                                    <tbody id="datatable_1_tbody">

                                    </tbody>
                                </table>
                            </div>
                            <div class="tab-pane fade" id="tab2primary">
                                @if (isset($data['list']))
                                    <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_2">
                                        <thead>
                                            <tr role="row">
                                                <th>Descripción</th>
                                                <th>Nombre</th>
                                                <th>Extensión</th>
                                                <th>Tamaño</th>
                                                <th>Estado</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $list = json_decode($data['list'], true); ?>

                                            @foreach ($list as $key => $value)
                                                <?php $sub_list = $list[$key]['childs']; ?>

                                                @foreach ($sub_list as $sub_value)
                                                    <?php $files = $sub_value['files']; ?>

                                                    @for ($j = 0; $j < count($files); $j++)
                                                        <?php
                                                        $item = $files[$j];
                                                        $parent = $item['parent'];
                                                        $child = $item['child'];
                                                        $name = $item['name'];
                                                        $extension = $item['extension'];
                                                        $size = $item['size'];
                                                        
                                                        $valid = $item['valid'] ? 'Valido' : 'Invalido';
                                                        $valid_class = $valid == 'Valido' ? 'success' : 'danger';
                                                        $valid_icon = $valid == 'Valido' ? 'fa fa-check' : 'fa fa-times';
                                                        ?>

                                                        <tr color="red">
                                                            <td>{{ $parent }} / {{ $child }}</td>
                                                            <td>{{ $name }}</td>
                                                            <td>{{ $extension }}</td>
                                                            <td>{{ $size }}</td>
                                                            <td>
                                                                <small class="label label-{{ $valid_class }}">
                                                                    <i class="{{ $valid_icon }}"></i>
                                                                    {{ $valid }}
                                                                </small>
                                                            </td>
                                                        </tr>
                                                    @endfor
                                                @endforeach
                                            @endforeach
                                        </tbody>
                                    </table>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>

        <!-- Modal load -->
        <div id="modal_load" class="modal fade in" role="dialog" tabindex="-1" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog" style="background: white; border-radius: 5px">
                <div class="modal-content" style="border-radius: 10px">
                    <div class="modal-body" style="text-align: center; font-size: 20px;">

                        <p id="execution_time" title="Tiempo de ejecución durante la comparación.">Tiempo de
                            ejecución: </p>

                        <p><b>7.500 registros: </b> de 25 a 30 segundos de ejecución.</p>
                        <p><b>35.000 registros: </b> de 3 a 4 minutos de ejecución.</p>

                        <button class="btn btn-danger" id="cancel" title="Se cancela la comparación."
                            onclick="cancel_operation()">
                            <i class="fa fa-spin fa-refresh"></i> &nbsp; Cancelar comparación
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <style>
            .modal {
                text-align: center;
                padding: 0 !important;
            }

            .modal:before {
                content: '';
                display: inline-block;
                height: 100%;
                vertical-align: middle;
                margin-right: -4px;
                /* Adjusts for spacing */

            }

            .modal-dialog {
                display: inline-block;
                text-align: left;
                vertical-align: middle;
            }

        </style>
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
        //Obtener los valores del backend
        var json = <?php echo json_encode($data); ?>;
        var method = json.method;
        var open_modal = json.open_modal;
        var list = json.list; //Lista que se exporta...
        var data = JSON.parse(list);

        //console.log('Data:', data);

        function cancel_operation() {
            swal({
                    title: 'Atención',
                    text: 'La comparación será finalizada.',
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
                        window.location.href = '/conciliators/transaction/transaction_conciliator';
                    }
                }
            );
        }

        $(document).ready(function() {
            $('#timestamp').val("{{ $data['filters']['timestamp'] }}");
            $('#record_limit').val("{{ $data['filters']['record_limit'] }}");
            $('#json').val(list); //Lista de datos para convertir a excel
            //console.log("JSON:", $('#json').val());

            if (data !== null) {
                $.each(data, function(index, contents) {
                    var d = contents.data;
                    for (var i = 0; i < d.length; i++) {
                        //console.log("d:", d[i]);

                        var status = d[i].status;
                        var incomes = d[i].incomes;
                        var file = d[i].file;
                        var correct = d[i].correct;
                        var incorrect = d[i].incorrect;

                        var view = true;

                        var incomes_class = '';
                        var file_class = '';
                        var status_class = '';
                        var row_style = '';

                        if (incomes == 'Existe') {
                            incomes_class = 'success';
                        } else {
                            incomes_class = 'danger';
                        }

                        if (file == 'Existe') {
                            file_class = 'success';
                        } else {
                            file_class = 'danger';
                            row_style = 'color: #842029; background-color: #f8d7da; border-color: #f5c2c7;';
                        }

                        if (status == 'success') {
                            status_class = 'success';
                            status = 'Aprobado';
                        } else if (status == 'canceled') {
                            status_class = 'warning';
                            status = 'Cancelado';
                        } else if (status == 'rollback') {
                            status_class = 'danger';
                            status = 'Reversado';
                        } else if (status == 'iniciated') {
                            status_class = 'warning';
                            status = 'Iniciado';
                        } else if (status == 'error dispositivo') {
                            status_class = 'danger';
                            status = 'Error de dispositivo';
                        } else if (status == 'inconsistency') {
                            status_class = 'danger';
                            status = 'Inconsistencia';
                        } else {
                            status_class = 'default';
                            status = 'Sin estado';
                        }

                        if (status == 'success') {
                            if (incomes == 'Existe' && file == 'Existe') {
                                view = false;
                            }
                        } else {
                            if (incomes == 'No existe' && file == 'No existe') {
                                view = false;
                            }
                        }

                        if (view) {
                            $('#datatable_1_tbody').append(
                                $('<tr>').attr('style', row_style)
                                .append($('<td>').append(index))
                                .append($('<td>').append(d[i].brand))
                                .append($('<td>').append(d[i].service))
                                .append($('<td>').append(d[i].created_at_view))
                                .append($('<td>').append(d[i].updated_at_view))
                                .append($('<td>').append(d[i].amount))
                                .append($('<td>').append(d[i].transaction_id))
                                .append($('<td>').append(
                                    '<span class="label label-' + status_class + '">' +
                                    status +
                                    '</span>'
                                )).append($('<td>').append(
                                    '<span class="label label-' + incomes_class + '">' +
                                    incomes +
                                    '</span>'
                                )).append($('<td>').append(
                                    '<span class="label label-' + file_class + '">' + file +
                                    '</span>'
                                ))
                            );
                        }
                    };
                });
            }

            $("#export").click(function() {
                if ($('#json').val() !== null && $('#json').val() !== '') {
                    $('#form_export').submit();
                } else {
                    swal({
                        title: 'Atención',
                        text: 'Realizar la comparación primero.',
                        type: 'warning',
                        showCancelButton: false,
                        closeOnConfirm: true,
                        closeOnCancel: false,
                        confirmButtonColor: '#2778c4',
                        confirmButtonText: 'Aceptar'
                    });
                }
            });

            $('#timestamp').daterangepicker({
                'format': 'DD/MM/YYYY HH:mm:ss',
                'startDate': moment().startOf('month'),
                'endDate': moment().endOf('month'),
                'timePicker': true,
                'opens': 'center',
                'drops': 'down',
                'ranges': {
                    'Hoy': [moment().startOf('day').toDate(), moment().endOf('day')
                        .toDate()
                    ],
                    'Ayer': [moment().startOf('day').subtract(1, 'days'), moment().endOf(
                        'day').subtract(1,
                        'days')],
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
                    'monthNames': ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                        'Julio', 'Agosto',
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

            //Duración del alert antes de cerrarse
            $(".alert").delay(5000).slideUp(300);

            //Cuerpo en donde se inserta la lista de archivos
            var file_body = $('#file_body').css({
                "max-height": "400px",
                "overflow-y": "scroll"
            });

            //Evento que detecta la selección de archivos
            var input_files = document.getElementById("files");
            input_files.addEventListener("change",
                function(event) {
                    let files = event.target.files;
                    var files_length = files.length;
                    var message = "";

                    if (files_length > 0) {
                        if (files_length <= 1) {

                        } else {
                            message = "Solo se permite un 1 archivo a la vez,\nse seleccionó " +
                                files_length +
                                " archivos,\nvolver a seleccionar archivos.";
                        }
                    } else {
                        message = "No seleccionaste ningún archivo.";
                    }

                    if (message !== "") {
                        $("#files").val(null);
                        swal({
                            title: "Atención",
                            text: message,
                            type: "warning",
                            showCancelButton: false,
                            closeOnConfirm: true,
                            closeOnCancel: false,
                            confirmButtonColor: "#2778c4",
                            confirmButtonText: "Aceptar"
                        });
                    }
                }, false);

            var seconds = 1;
            var minutes = 0;

            function second() {
                var text = '';
                var sub_text = '';

                if (minutes > 0) {
                    if (minutes == 1) {
                        text = '1 minuto';
                    } else {
                        text = minutes + ' minutos';
                    }
                }

                if (minutes > 0 && seconds > 0) {
                    text = text + ' y ';
                }

                if (seconds > 0) {
                    if (seconds == 1) {
                        text = text + '1 segundo.';
                    } else {
                        text = text + seconds + ' segundos.';
                    }
                }

                $('#execution_time').html('Tiempo de ejecución: ' + text);

                if (seconds == 59) {
                    minutes++;
                    seconds = -1;
                    //console.log('Minutos: ', minutes);
                }

                seconds++;
            }

            //Para mostrar el gif de loading
            $('#validate').click(function(event) {

                var message = '';

                if (input_files.files.length > 0) {
                    setInterval(second, 1000);
                    $('#modal_load').modal('show');
                    $('form#form_validate').submit();
                } else {
                    message = 'Seleccionar archivo.'
                }

                if (message !== '') {
                    swal({
                        title: 'Atención',
                        text: message,
                        type: "warning",
                        showCancelButton: false,
                        closeOnConfirm: true,
                        closeOnCancel: false,
                        confirmButtonColor: "#2778c4",
                        confirmButtonText: "Aceptar"
                    });
                }
            });

            if (open_modal == 'si') {
                var data_table_config = {
                    orderCellsTop: true,
                    fixedHeader: true,
                    pageLength: 20,
                    lengthMenu: [5, 10, 20, 30, 50, 70, 100, 250, 500, 1000],
                    dom: '<"pull-left"f><"pull-right"l>tip',
                    language: {
                        "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
                    },
                    scroller: true,
                    processing: true,
                    initComplete: function(settings, json) {

                        var rows = $('#datatable_1 tr').length;

                        //console.log("Filas:", rows);

                        if (rows > 0) {
                            $('.btn-box-tool').trigger('click');
                        }

                        $(".alert").hide();

                        $('#filters_and_files').css('display', 'block');
                        $('#div_load').css('display', 'none');
                    }
                }

                $('#datatable_1').DataTable(data_table_config);
                $('#datatable_2').DataTable(data_table_config);
            } else {
                $('#modal_load').modal('hide');
                $('#filters_and_files').css('display', 'block');
                $('#div_load').css('display', 'none');
            }
        });
    </script>
@endsection
