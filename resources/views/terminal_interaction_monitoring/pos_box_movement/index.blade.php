@extends('layout')

@section('title')
Informe de movimientos
@endsection

@section('content')
<section class="content-header">
    <h1>
        Movimientos de caja
        <small>Listado</small>
    </h1>
    <ol class="breadcrumb">
        <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
        <li><a href="#">Movimientos de caja</a></li>
        <li class="active">Lista</li>
    </ol>
</section>

<section class="content">

    <div class="delay_slide_up">
        @include('partials._flashes')
        @include('partials._messages')
    </div>

    <div id="modal_load" class="modal fade" role="dialog" tabindex="-1" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered" role="document" style="background: white; border-radius: 5px">
            <!-- Modal content-->
            <div class="modal-content" style="border-radius: 10px">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <div class="modal-title" style="font-size: 20px;">
                        Cargando transacciones...
                    </div>
                </div>

                <div class="modal-body">
                    <div id="div_load" style="text-align: center; margin-bottom: 10px; font-size: 20px;">
                        <div>
                            <i class="fa fa-spin fa-refresh fa-2x" style="vertical-align: sub;"></i> &nbsp;
                            Cargando...

                            <p id="rows_loaded" title="Filas cargadas"></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="modal" class="modal fade" role="dialog" tabindex="-1" data-backdrop="static" data-keyboard="false">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document" style="background: white; border-radius: 5px">
            <!-- Modal content-->
            <div class="modal-content" style="border-radius: 10px">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <div class="modal-title" style="font-size: 20px;" id="modal_title"></div>
                </div>
            </div>

            <div class="modal-body">
                <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_2">
                    <thead>
                        <tr>
                            <th>ID-Transacción</th>
                            <th>Servicio</th>
                            <th>Monto</th>
                            <th>Fecha-Hora</th>
                        </tr>
                    </thead>

                    <tbody id="datatable_2_tbody"></tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">Búsqueda personalizada</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
            </div>
        </div>
        <div class="box-body">

            <div class="row">
                {!! Form::open(['route' => 'terminal_interaction_monitoring_pos_box_movement', 'method' => 'POST', 'role' => 'form', 'id' => 'form_search']) !!}
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="timestamp">Fecha:</label>
                        <input type="text" class="form-control" style="display:block" id="timestamp" name="timestamp"></input>
                    </div>
                </div>

                <div class="col-md-4">
                    <label for="pos_box_id">ATM - Pos - Box:</label>
                    <div class="form-group">
                        <input type="text" class="form-control" name="pos_box_id" id="pos_box_id"></input>
                    </div>
                </div>

                <!--<div class="col-md-4">
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
                                </select>
                            </div>
                        </div>

                        <div class="col-md-4">
                                <label for="bg_id">Grupo:</label>
                                <div class="form-group">
                                    <input type="text" class="form-control" name="bg_id" id="bg_id"></input>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label for="b_id">Sucursal:</label>
                                <div class="form-group">
                                    <input type="text" class="form-control" name="b_id" id="b_id"></input>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label for="pos_id">Punto de venta:</label>
                                <div class="form-group">
                                    <input type="text" class="form-control" name="pos_id" id="pos_id"></input>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label for="u_id">Usuario:</label>
                                <div class="form-group">
                                    <input type="text" class="form-control" name="u_id" id="u_id"></input>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label for="mt_id">Movimiento:</label>
                                <div class="form-group">
                                    <input type="text" class="form-control" name="mt_id" id="mt_id"></input>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label for="t_id">Turno:</label>
                                <div class="form-group">
                                    <input type="text" class="form-control" name="t_id" id="t_id"></input>
                                </div>
                            </div>-->

                <div class="col-md-2">
                    <label for="search">Buscar...</label>
                    <br />
                    <button type="submit" class="btn btn-primary" title="Buscar según los filtros en los registros." id="search" name="search">
                        <span class="fa fa-search" aria-hidden="true"></span> &nbsp; Búsqueda
                    </button>
                </div>
                {!! Form::close() !!}

                <!--<div class="col-md-2">
                                <label for="clean">Limpiar...</label>
                                <br />
                                <button class="btn btn-default" title="Limpiar filtros." id="clean" name="clean">
                                    <span class="fa fa-clean"></span> &nbsp; Limpiar filtros
                                </button>
                            </div>-->
            </div>
        </div>
    </div>

    <div class="box box-default">
        <div class="box-body">
            <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_1">
                <thead>
                    <tr>
                        <th></th>
                        <th>ATM</th>
                        <th>Apertura</th>
                        <th>Cierre</th>
                        <th>Cantidad de transacciones</th>
                        <th>Total</th>
                        <th>Ver</th>
                    </tr>
                </thead>

                <tbody>
                    @for ($i = 0; $i < count($data['lists']['records_list']); $i++) 
                        <?php
                            $item = $data['lists']['records_list'][$i];
                            $item_json = json_encode($item);
                        ?> 
                        <tr>
                            <td>{{ $item['atm'] }}</td>
                            <td></td>
                            <td>{{ $item['opening_user'] }} <br /> En fecha: {{ $item['opening_date_time'] }}</td>
                            <td>{{ $item['closing_user'] }} <br /> En fecha: {{ $item['closing_date_time'] }}</td>
                            <td>{{ $item['transaction_count'] }}</td>
                            <td>{{ $item['transaction_sum'] }}</td>
                            <td>
                                <button class="btn btn-default" title="Ver transacción" style="border-radius: 3px;" onclick="open_modal_detail({{ $item_json }})">
                                    <i class="fa fa-list"></i>
                                </button>
                            </td>
                        </tr>

                        @endfor
                </tbody>
            </table>
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

<!-- Iniciar objetos -->
<script type="text/javascript">
    /**
     * Abre el modal para abrir las transacciones.
     */
    function open_modal_detail(parameters) {

        //datatable_2_tbody

        var atm_id = parameters['atm_id'];
        var opening_date_time = parameters['opening_date_time'];
        var closing_date_time = parameters['closing_date_time'];
        var opening_date_time_filter = parameters['opening_date_time_filter'];
        var closing_date_time_filter = parameters['closing_date_time_filter'];

        var url = "{{ route('terminal_interaction_get_transactions_by_atm') }}";

        var json = {
            _token: token,
            atm_id: atm_id,
            opening_date_time_filter: opening_date_time_filter,
            closing_date_time_filter: closing_date_time_filter
        };

        //console.log('json:', json);
        //var table = $('#datatable_2').DataTable();
        //table.clear().draw();

        $('#datatable_2').DataTable().destroy();
        $('#datatable_2_tbody').html('');

        $("#modal_title").html('Lista de transacciones de la apertura el: <b> ' + opening_date_time + '</b> hasta el cierre: <b> ' + closing_date_time + '</b>')

        $("#modal_load").modal('show');

        $.post(url, json, function(data, status) {

            if (data.length > 0) {

                for (var i = 0; i < data.length; i++) {
                    var item = data[i];

                    var transaction_id = item.transaction_id;
                    var service = item.service;
                    var created_at = item.created_at;
                    var amount = item.amount;

                    $('#datatable_2_tbody')
                        .append(
                            $('<tr>')
                            .append($('<td>').append(transaction_id))
                            .append($('<td>').append(service))
                            .append($('<td>').append(amount))
                            .append($('<td>').append(created_at))
                        );
                }

                //Datatable config
                var data_table_config = {
                    //custom
                    orderCellsTop: true,
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
                    processing: true
                }

                //if (!$.fn.DataTable.isDataTable('#datatable_2')) {
                $('#datatable_2').DataTable(data_table_config);
                //}

                $("#modal_load").modal('hide');
                $("#modal").modal('show');
            } else {
                $("#modal_load").modal('hide');

                swal({
                    title: 'Fecha de apertura-cierre deben estar completas.',
                    text: 'Obs. No se puede mostrar las transacciones porque falta una fecha delimitante.',
                    type: 'info',
                    showCancelButton: false,
                    closeOnConfirm: true,
                    closeOnCancel: false,
                    confirmButtonColor: "#2778c4",
                    confirmButtonText: "Aceptar"
                });
            }

            console.log('data:', data);
        }).error(function(error) {

            console.log('ERROR:', error);
            
            swal({
                title: 'Atención',
                text: 'Ocurrió un error al obtener los datos.',
                type: 'error',
                showCancelButton: false,
                closeOnConfirm: true,
                closeOnCancel: false,
                confirmButtonColor: "#2778c4",
                confirmButtonText: "Aceptar"
            });
        });
    }

    $(".delay_slide_up").delay(5000).slideUp(300);

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

    $('#timestamp').attr({
        'onkeydown': 'return false'
    });

    $('#timestamp').hover(function() {
        $('#timestamp').attr({
            'title': 'El filtro de fecha es: ' + $('#timestamp').val()
        })
    }, function() {

    });

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
                            'colspan': '11',
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







    var selective_config = {
        delimiter: ',',
        persist: false,
        openOnFocus: true,
        valueField: 'id',
        labelField: 'description',
        searchField: 'description',
        maxItems: 1,
        options: {}
    };

    $('#pos_box_id').selectize(selective_config)[0].selectize.addOption({!! $data['lists']['pos_box'] !!});

    var inputs = {!! $data['inputs'] !!};

    if (inputs !== null) {
        $("#timestamp").val(inputs.timestamp);
        $('#pos_box_id').selectize()[0].selectize.setValue(inputs.pos_box_id, false);
    }
</script>
@endsection