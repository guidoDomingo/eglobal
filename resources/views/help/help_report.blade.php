@extends('layout')

@section('title')
    USSD - Transacciones - Reporte
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Ayuda - Reporte
            <small>Recargas</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Ayuda - Reporte</a></li>
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
                <div class="callout callout-default"
                    style="border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px; background: white">
                    {!! Form::open(['route' => 'help_report', 'method' => 'POST', 'role' => 'form', 'id' => 'help_report']) !!}
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="timestamp">Fecha:</label>
                                <input type="text" class="form-control" style="display:block" id="timestamp"
                                    name="timestamp"></input>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="module_id">Módulo:</label>
                            <div class="form-group">
                                <select class="form-control select2" id="module_id" name="module_id"></select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="search">Buscar...</label>
                            <br />
                            <button type="submit" class="btn btn-primary" title="Buscar según los filtros en los registros."
                                id="search" name="search">
                                <span class="fa fa-search" aria-hidden="true"></span> &nbsp; Búsqueda
                            </button>
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-12">
                <div class="callout callout-default"
                    style="border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px; background: white">
                    @if (isset($data['list']))
                        <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_1">
                            <thead>
                                <tr role="row">
                                    <th>Módulo</th>
                                    <th>Número</th>
                                    <th>Descripción</th>
                                    <th>Creación</th>
                                    <th>Autor</th>
                                    <th>Estado</th>
                                    <th>Opciones</th>
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
                                    $description = $item['description'];
                                    $module = $item['module'];
                                    $created_at = $item['created_at'];
                                    $user = $item['user'];
                                    $status = $item['status'];
                                    ?>

                                    <tr>
                                        <td></td>
                                        <td>{{ $id }}</td>
                                        <td>{{ $description }}</td>
                                        <td>{{ $created_at }}</td>
                                        <td>{{ $user }}</td>
                                        <td>{{ $status }}</td>
                                        <td>{{ $module }}</td>

                                        <td style="text-align: center">
                                            @if (Sentinel::hasAccess('help_edit'))
                                                <button class="btn btn-default" title="Ver y Editar transacción"
                                                    style="border-radius: 3px;"
                                                    onclick="click_in_view_and_edit({{ $parameters }});">
                                                    <i class="fa fa-pencil"></i>
                                                </button>
                                                <button class="btn btn-default" title="Ver transacción"
                                                    style="border-radius: 3px;"
                                                    onclick="click_in_view_and_edit({{ $parameters }});">
                                                    <i class="fa fa-eye"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @endfor
                            </tbody>
                        </table>
                    @endif
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
        }, function() {

        });

        //Obtener los registros de atms
        $.get("/help/help_module", function(data) {
            $('#module_id').append($('<option>', {
                value: '',
                text: 'Todos'
            }));

            for (var i in data) {
                var id = data[i].id;
                var description = data[i].description;

                $('#module_id').append($('<option>', {
                    value: id,
                    text: description
                }));
            }

            var value = "{{ $data['filters']['module_id'] }}";
            $('#module_id').val(value).trigger('change');
        });

        var column_count = $("#datatable_1").find("tr:first th").length;
        var groupColumn = column_count - 2; //Estado y Opciones restan 2

        //Datatable config
        var data_table_config = {
            //custom
            responsive: true,
            orderCellsTop: true,
            fixedHeader: true,
            pageLength: 20,
            lengthMenu: [5, 10, 20, 30, 50, 70, 100, 250, 500, 1000],
            dom: '<"pull-left"f><"pull-right"l>tip',
            language: {
                "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
            },
            scroller: true,

            //Agrupador
            "columnDefs": [{
                "visible": false,
                "targets": groupColumn
            }],
            "order": [
                [groupColumn, 'asc']
            ],
            "displayLength": 25,
            "drawCallback": function(settings) {
                var api = this.api();
                var rows = api.rows({
                    page: 'current'
                }).nodes();
                var last = null;

                api.column(groupColumn, {
                    page: 'current'
                }).data().each(function(group, i) {
                    if (last !== group) {
                        var color = 'dimgray';

                        var td = $('<td>');
                        td.attr({
                            'colspan': (groupColumn + 1).toString(),
                            'style': 'color: white !important'
                        }).append(group);

                        var tr = $('<tr>');
                        tr.attr({
                            'class': 'group',
                            'style': 'background-image: linear-gradient(to right, ' +
                                color + ', #ffffff) !important; '
                        }).append(td);

                        $(rows).eq(i).before(tr);

                        last = group;
                    }
                });
            }
        }

        var table = $('#datatable_1').DataTable(data_table_config);

        // Order by the grouping
        $('#datatable_1 tbody').on('click', 'tr.group', function() {
            var currentOrder = table.order()[0];
            if (currentOrder[0] === groupColumn && currentOrder[1] === 'asc') {
                table.order([groupColumn, 'desc']).draw();
            } else {
                table.order([groupColumn, 'asc']).draw();
            }
        });

        //Esconder la alerta después de 5 segundos. 
        $(".alert").delay(5000).slideUp(300);

        $('[data-toggle="popover"]').popover();

        $('.select2').select2({
            width: '99%'
        });

    </script>
@endsection
