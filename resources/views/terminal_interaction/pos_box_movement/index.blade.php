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
        </div>

        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">Búsqueda personalizada</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                            class="fa fa-minus"></i></button>
                </div>
            </div>
            <div class="box-body">

                <div class="row">
                    {!! Form::open(['route' => 'pos_box_movement_index', 'method' => 'POST', 'role' => 'form', 'id' => 'form_search']) !!}
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="timestamp">Fecha:</label>
                            <input type="text" class="form-control" style="display:block" id="timestamp"
                                name="timestamp"></input>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="movement_type_id">Movimiento:</label>
                        <div class="form-group">
                            <input type="text" class="form-control" name="movement_type_id"
                                id="movement_type_id"></input>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="turn_id">Turno:</label>
                        <div class="form-group">
                            <input type="text" class="form-control" name="turn_id" id="turn_id"></input>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label for="turn_id">Usuario:</label>
                        <div class="form-group">
                            <input type="text" class="form-control" name="user_id" id="user_id"></input>
                        </div>
                    </div>
                    <div class="col-md-4">
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

                    <div class="col-md-2">
                        <label for="search">Buscar...</label>
                        <br />
                        <button type="submit" class="btn btn-primary" title="Buscar según los filtros en los registros."
                            id="search" name="search">
                            <span class="fa fa-search" aria-hidden="true"></span> &nbsp; Búsqueda
                        </button>
                    </div>
                    {!! Form::close() !!}

                    <div class="col-md-2">
                        <label for="clean">Limpiar...</label>
                        <br />
                        <button class="btn btn-default" title="Limpiar filtros."
                            id="clean" name="clean">
                            <span class="fa fa-clean"></span> &nbsp; Limpiar filtros
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="box box-default">
            <div class="box-body">
                <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_1">
                    <thead>
                        <tr>
                            <th>Número</th>
                            <th>Movimiento</th>
                            <th>Turno</th>
                            <th>Transacción</th>
                            <th>Monto</th>
                            <th>Usuario</th>
                            <th>Creación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data['lists']['records_list'] as $item)
                            <tr>
                                <td>{{ $item->id }}</td>
                                <td>{{ $item->movement }}</td>
                                <td>{{ $item->turn }}</td>
                                <td>{{ $item->transaction_id }}</td>
                                <td>{{ $item->amount }}</td>
                                <td>{{ $item->user }}</td>
                                <td>{{ $item->created_at }}</td>
                            </tr>
                        @endforeach
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
    <link href="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker-bs3.css" rel="stylesheet"
        type="text/css" />
    <script src="/bower_components/admin-lte/plugins/daterangepicker/moment.min.js"></script>
    <script src="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker.js"></script>

    <!-- bootstrap datepicker -->
    <script src="/bower_components/admin-lte/plugins/datepicker/bootstrap-datepicker.js"></script>

    <!-- Iniciar objetos -->
    <script type="text/javascript">
        $(".delay_slide_up").delay(5000).slideUp(300);

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

        $('#user_id').selectize({
            delimiter: ',',
            persist: false,
            openOnFocus: true,
            valueField: 'id',
            labelField: 'description',
            searchField: 'description',
            maxItems: 1,
            options: {!! $data['lists']['users'] !!}
        });

        $('#movement_type_id').selectize({
            delimiter: ',',
            persist: false,
            openOnFocus: true,
            valueField: 'id',
            labelField: 'description',
            searchField: 'description',
            maxItems: 1,
            options: {!! $data['lists']['movement_types'] !!}
        });

        $('#turn_id').selectize({
            delimiter: ',',
            persist: false,
            openOnFocus: true,
            valueField: 'id',
            labelField: 'description',
            searchField: 'description',
            maxItems: 1,
            options: {!! $data['lists']['turns'] !!}
        });

        var inputs = {!! $data['inputs'] !!};

        if (inputs !== null) {
            $("#timestamp").val(inputs.timestamp);
            $('#movement_type_id').selectize()[0].selectize.setValue(inputs.movement_type_id, false);
            $('#turn_id').selectize()[0].selectize.setValue(inputs.turn_id, false);
            $('#user_id').selectize()[0].selectize.setValue(inputs.user_id, false);
            $('#record_limit').val(inputs.record_limit);
        }

        $("#clean").click(function() {
            $("#timestamp").val(null);
            $('#movement_type_id').selectize()[0].selectize.setValue(null, false);
            $('#turn_id').selectize()[0].selectize.setValue(null, false);
            $('#user_id').selectize()[0].selectize.setValue(null, false);
            $('#record_limit').val(null);
        });
    </script>
@endsection
