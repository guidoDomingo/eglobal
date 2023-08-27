@extends('layout')

@section('title')
    Conciliación Automática
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Conciliación Automática
            <small>Lectura de archivos y actualización de registros</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Conciliación Automática</a></li>
            <li class="active">Selección de Archivos</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-5">
                <div class="callout callout-default"
                    style="border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px; background:white">
                    <h4>Información y Mensajes</h4>

                    @include('partials._flashes')
                    @include('partials._messages')

                    <ul>
                        <li>
                            Máximo de &nbsp;
                            <small class="label label-default">15 archivos.</small>
                        </li>
                        <li>
                            Extensiones admitidas &nbsp;
                            <small class="label label-default">.xlsx, .xls</small>
                        </li>
                    </ul>


                </div>

                <div class="callout callout-default"
                    style="border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px; background:white">
                    <h4>Seleccionar archivos</h4><small>Máximo 15 archivos.</small>

                    {!! Form::open(['route' => 'ballot_conciliator.create', 'method' => 'POST', 'role' => 'form', 'id' => 'ballot_conciliator_form_create', 'enctype' => 'multipart/form-data']) !!}
                    <div class="row">
                        <div class="col-md-12">
                            <div class="form-group">
                                {!! Form::file('files[]', [ 'class' => 'form-control', 'id' => 'files', 'multiple' => true, 'accept' => '.xls,.xlsx', 'required' => 'required', 'style' => 'display:inline-block; width: 350px' ]) !!}
                            </div>

                            <div class="form-group">
                                {!! Form::Label('timestamp', 'Rango de fecha a Conciliar:', [ 'style' => 'float:left' ]) !!}
                                <div class="input-group" style="display: inline-table">
                                    <div class="input-group-addon">
                                        <i class="fa fa-clock-o"></i>
                                    </div>

                                    {!! Form::text('timestamp', null, [ 'class' => 'form-control', 'id' => 'timestamp', 'type' => 'text', 'style' => 'display:block' ]) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <button type="submit" class="btn btn-primary" style="float: right">
                                <i class="fa fa-check"></i> Validar documento/s
                            </button>
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
            <div class="col-md-7">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">Archivos seleccionados <small>(Extensiones adminitidas .xlsx, .xls)</small>
                        </h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                    class="fa fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="box-body" id="file_body"> </div>
                    <!--Aquí se carga la lista por javascript -->
                </div>
            </div>
        </div>

        @if ($data['open_modal'] == 'si')
            <!-- Modal -->
            <div id="modal" class="modal fade" role="dialog" tabindex="-1" data-backdrop="static" data-keyboard="false">
                <div class="modal-dialog modal-dialog-centered modal-lg" role="document"
                    style="width: 98%; background: white; border-radius: 5px">
                    <!-- Modal content-->
                    <div class="modal-content" style="border-radius: 10px">
                        <div class="modal-header">
                            <div class="modal-title" style="font-size: 20px;">
                                <div style="float:left">
                                    Rango de fecha a conciliar: &nbsp; <small> <b> {{ $data['date'] }} </b> </small>
                                </div>
                                <div style="float:right;">
                                    Registros a conciliar: &nbsp;
                                    <b> Procesado: </b> <small class="label label-danger">No</small> &nbsp;
                                    <b> Datos Correctos en archivo: </b> <small class="label label-success"> Si</small>
                                    &nbsp;
                                </div>
                            </div>
                        </div>

                        <br />

                        <!-- TAB PANNEL -->
                        <div class="panel with-nav-tabs">
                            <div class="panel-heading">
                                <ul class="nav nav-tabs" id="myTabs">
                                    <li class="active" title="Lista de los registros en el rango de fecha seleccionado.">
                                        <a href="#tab1primary" data-toggle="tab">Registros</a>
                                    </li>
                                    <li title="Lista de los archivos seleccionados.">
                                        <a href="#tab2primary" data-toggle="tab">Archivos</a>
                                    </li>
                                </ul>
                            </div>
                            <div class="panel-body">
                                <div class="tab-content">
                                    <div class="tab-pane fade in active" id="tab1primary">
                                        @if (isset($data['list']))
                                            <table class="table table-bordered table-hover dataTable" role="grid"
                                                id="datatable_1">
                                                <thead>
                                                    <tr>
                                                        <th colspan="5">Información</th>
                                                        <th colspan="2">Estado</th>
                                                    </tr>
                                                    <tr role="row">
                                                        <th title="Banco de la boleta.">
                                                            Banco <i class="fa fa-bank"></i> </th>
                                                        <th title="Fecha de la boleta.">
                                                            Fecha <i class="fa fa-calendar"></i> </th>
                                                        <th title="Número de la boleta.">
                                                            Número <b> </b> </th>
                                                        <th title="Monto de la boleta.">
                                                            Monto <i class="fa fa-tag"></i>
                                                        </th>
                                                        <th title="Tipo de pago de la boleta.">
                                                            Tipo de Pago <i class="fa fa-money"></i>
                                                        </th>
                                                        <th title="Indica si el registro se encuentra procesado.">
                                                            Procesado <i class="fa fa-check"></i>
                                                        </th>
                                                        <th
                                                            title="Indica que el Banco, Fecha, Número y Monto coinciden en los registros y en el archivo.">
                                                            Datos Correctos en archivo <i class="fa fa-check"></i>
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $list = json_decode($data['list'], true); ?>

                                                    @foreach ($list as $key => $name)

                                                        <?php $sub_list = $list[$key]['data']; ?>

                                                        @for ($j = 0; $j < count($sub_list); $j++)

                                                            <?php
                                                            $item = $sub_list[$j];
                                                            $bank = $item['bank'];
                                                            $payment_type = $item['payment_type'];
                                                            $ballot_number = $item['ballot_number'];
                                                            $amount = $item['amount'];
                                                            $status = $item['status'];
                                                            $date = $item['date'];
                                                            $correct_data = $item['correct_data'];

                                                            $conciliate = $status == null ? 'No' : 'Si';
                                                            $conciliate_label = $status == null ? 'danger' : 'success';

                                                            $correct = $correct_data ? 'Si' : 'No';
                                                            $correct_label = $correct_data ? 'success' : 'danger';
                                                            ?>

                                                            <tr>
                                                                <td>{{ $bank }}</td>
                                                                <td>{{ $date }}</td>
                                                                <td>{{ $ballot_number }}</td>
                                                                <td>{{ $amount }}</td>
                                                                <td>{{ $payment_type }}</td>
                                                                <td><small
                                                                        class="label label-{{ $conciliate_label }}">{{ $conciliate }}</small>
                                                                </td>
                                                                <td><small
                                                                        class="label label-{{ $correct_label }}">{{ $correct }}</small>
                                                                </td>
                                                            </tr>
                                                        @endfor
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @else
                                            <div class="alert alert-danger" role="alert">
                                                El filtro de fecha <b> no retornó ningún registro</b>.
                                            </div>
                                        @endif
                                    </div>
                                    <div class="tab-pane fade" id="tab2primary">
                                        @if (isset($data['list']))
                                            <table class="table table-bordered table-hover dataTable" role="grid"
                                                id="datatable_2">
                                                <thead>
                                                    <tr>
                                                        <th colspan="4">Archivo</th>
                                                        <th colspan="1">Estado</th>
                                                    </tr>
                                                    <tr role="row">
                                                        <th>Banco</th>
                                                        <th>Nombre</th>
                                                        <th>Extensión</th>
                                                        <th>Tamaño</th>
                                                        <th>Válido</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php $list = json_decode($data['list'], true); ?>

                                                    @foreach ($list as $key => $name)
                                                        <?php
                                                        $sub_list = $list[$key]['files'];
                                                        $bank = $key;
                                                        ?>

                                                        @for ($j = 0; $j < count($sub_list); $j++)
                                                            <?php
                                                            $item = $sub_list[$j];
                                                            $name = $item['name'];
                                                            $extension = $item['extension'];
                                                            $size = $item['size'];
                                                            $valid = $item['valid'];

                                                            $correct = $valid ? 'Si' : 'No';
                                                            $correct_label = $valid ? 'success' : 'danger';
                                                            ?>

                                                            <tr>
                                                                <td>{{ $bank }}</td>
                                                                <td>{{ $name }}</td>
                                                                <td>{{ $extension }}</td>
                                                                <td>{{ $size }}</td>
                                                                <td><small
                                                                        class="label label-{{ $correct_label }}">{{ $correct }}</small>
                                                                </td>
                                                            </tr>
                                                        @endfor
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        @else
                                            <div class="alert alert-danger" role="alert">
                                                No se pudo detectar los <b> archivos</b>.
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- FIN TAB PANNEL -->

                    </div>
                    <div class="modal-footer">
                        <div class="btn-group mr-2" role="group">
                            {!! Form::open(['route' => 'ballot_conciliator.cancel', 'method' => 'POST', 'role' => 'form', 'id' => 'ballot_conciliator_form_cancel']) !!}
                            <button type="submit" class="btn btn-danger pull-right"
                                title="Cancela totalmente la transacción." style="margin-right: 10px">
                                <span class="fa fa-remove" aria-hidden="true"></span> &nbsp; Cancelar
                            </button>
                            {!! Form::close() !!}
                        </div>

                        <div class="btn-group mr-2" role="group">
                            {!! Form::open(['route' => 'ballot_conciliator.store', 'method' => 'POST', 'role' => 'form', 'id' => 'ballot_conciliator_form_store']) !!}
                            {!! Form::hidden('json', null, ['id' => 'json']) !!}
                            <button type="submit" class="btn btn-primary pull-right"
                                title="Confirma la lista de registros a actualizar." id="confirm">
                                <span class="fa fa-save" aria-hidden="true"></span> &nbsp; Confirmar
                            </button>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
            </div>
            </div>
        @endif
    </section>
@endsection

@section('css')
    <style>

    </style>
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
        //Configuración del campo fecha y hora
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
            'format': 'DD/MM/YYYY HH:mm:ss',
            'startDate': moment().startOf('month'),
            'endDate': moment().endOf('month'),
            'timePicker': true,
            'opens': 'center',
            'drops': 'up',
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

        //Duración del alert antes de cerrarse
        $(".alert").delay(5000).slideUp(300);

        //Cuerpo en donde se inserta la lista de archivos
        var file_body = $('#file_body').css({
            "max-height": "400px",
            "overflow-y": "scroll"
        });

        //Inicialización del gif loading
        var $loading = $('#loading');
        $loading.hide();

        //Evento que detecta la selección de archivos
        var input_files = document.getElementById("files");
        input_files.addEventListener("change", function(event) {
            let files = event.target.files;
            var files_length = files.length;
            var message = "";

            if (files_length > 0) {
                if (files_length <= 15) {
                    file_body.html("");

                    for (let i = 0; i < files_length; i++) {
                        var name = files[i].name;
                        var extension = name.split('.').pop();
                        var size = files[i].size;
                        var webkit_relative_path = files[i].webkitRelativePath;
                        var last_modified_date_time;

                        if (files[i].lastModifiedDate == undefined) {
                            const date = new Date(files[i].lastModified).toLocaleString("es-PY", {
                                timeZone: "America/Asuncion"
                            });
                            last_modified_date_time = "El " + date;
                        } else {
                            last_modified_date_time = "El " + files[i].lastModifiedDate.toLocaleDateString() +
                                " a las " + files[i].lastModifiedDate.toLocaleTimeString();
                        }

                        if (extension == "xls" || extension == "xlsx") {
                            var _size = (size < 1000000) ? Math.floor(size / 1000) + ' KB' : Math.floor(size /
                                1000000) + ' MB';

                            file_body.append(
                                $('<div>').attr({
                                    "class": "info-box bg-green"
                                }).append(
                                    $('<span>').attr({
                                        "class": "info-box-icon"
                                    }).append(
                                        $('<i>').attr({
                                            "class": "fa fa-file-excel-o"
                                        })
                                    )
                                ).append(
                                    $('<div>').attr({
                                        "class": "info-box-content"
                                    }).append(
                                        $('<span>').attr({
                                            "class": "info-box-text"
                                        }).html(last_modified_date_time)
                                    ).append(
                                        $('<span>').attr({
                                            "class": "info-box-number"
                                        }).html(name)
                                    ).append(
                                        $('<div>').attr({
                                            "class": "progress"
                                        }).append(
                                            $('<div>').attr({
                                                "class": "progress-bar"
                                            })
                                        )
                                    ).append(
                                        $('<div>').attr({
                                            "class": "progress-description"
                                        }).html(_size)
                                    )
                                )
                            );

                            console.log('File:', files[i]);
                        } else {
                            message =
                                "Se concontró archivo/s con extensión incorrecta,\nvolver a seleccionar archivos.";
                            break;
                        }
                    };
                } else {
                    message = "Solo se permite un máximo de 15 archivos,\nse seleccionó " + files_length +
                        " archivos,\nvolver a seleccionar archivos.";
                }
            } else {
                message = "No seleccionaste ningún archivo.";
            }

            if (message !== "") {
                $("#files").val(null);
                file_body.html("");
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

        //Para mostrar el gif de loading
        $('#save').click(function(event) {
            if (input_files.files.length > 0) {
                $loading.show();
            }
        });

        var method = "{{ $data['method'] }}";
        var open_modal = "{{ $data['open_modal'] }}";
        var list = '{!! $data['list'] !!}';

        if (open_modal == 'si') {
            $('#modal').modal('show');

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

            $('#datatable_1').DataTable(data_table_config);
            $('#datatable_2').DataTable(data_table_config);

            $('#json').val(list);

            console.log("Valor del campo: ", $('#json').val());
        }

    </script>
@endsection
