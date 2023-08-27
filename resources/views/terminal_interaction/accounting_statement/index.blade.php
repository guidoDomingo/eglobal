@extends('terminal_interaction.layout')

@section('title')
    Estado contable - Reporte
@endsection

@section('content')
    <section class="content-header">
        <h1>
            Estado contable - Reporte
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Estado contable - Reporte</a></li>
        </ol>
    </section>

    <section class="content">

        <div class="delay_slide_up">
            @include('partials._flashes')
            @include('partials._messages')
        </div>

        <!-- 
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="box box-default">
                                            <div class="box-body" title='Opciones'>
                                                <button type="button" class="btn btn-default" title="Ayuda e información" data-toggle="modal"
                                                    data-target="#modal_help" style="border-radius: 5px; margin-botton: 5px; float: right">
                                                    <span class="fa fa-question" aria-hidden="true"></span> Ayuda
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div> 
                            -->

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
                        {!! Form::open(['route' => 'accounting_statement_index', 'method' => 'POST', 'role' => 'form', 'id' => 'form_search']) !!}
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="timestamp">Fecha:</label>
                                    <input type="text" class="form-control" id="timestamp" name="timestamp"></input>
                                </div>
                            </div>

                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="timestamp">Grupo / Sucursal / Usuario:</label>
                                    <input type="text" class="form-control" id="group_branch_user"
                                        name="group_branch_user"></input>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="search_type">
                                        Tipo de búsqueda
                                    </label> <br/>
                                    <input type="text" class="form-control" id="search_type"
                                        name="search_type" disabled></input>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="show">
                                        Ver
                                    </label>
                                    <select class="form-control" id="show" name="show">
                                        <option value="todos">Todos</option>
                                        <option value="depositos">Depósitos</option>
                                        <option value="transacciones">Transacciones</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <label for="activate_summary">
                                    Resumen
                                </label>
                                <div class="row">
                                    <div class="col-md-6">
                                        <label class="switch" style="margin-top: 8px">
                                            <input type="checkbox" name="activate_summary" id="activate_summary">
                                            <span class="slider round"></span>
                                        </label> &nbsp;
                                        <label for="activate_summary">
                                            Al dia de hoy
                                        </label>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="switch" style="margin-top: 8px">
                                            <input type="checkbox" name="activate_closing" id="activate_closing">
                                            <span class="slider round"></span>
                                        </label> &nbsp;
                                        <label for="activate_closing">
                                            Al cierre
                                        </label>
                                    </div>
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

                            <!-- <div class="col-md-2">
                                                        <label for="generate_x">Exportar...</label>
                                                        <br />
                                                        <button type="submit" class="btn btn-success" title="Convertir tabla en archivo excel."
                                                            id="generate_x" name="generate_x">
                                                            <span class="fa fa-file-excel-o " aria-hidden="true"></span> &nbsp; Exportar
                                                        </button>
                                                    </div> -->
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>

        <div class="row" id="resume">
            <div class="col-md-12">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">Resumen de totales</h3> &nbsp;

                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                    class="fa fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row" style="text-align: center;">
                            <div class="col-md-3">
                                <h4>
                                    En transacciones: 
                                    <b> {{ $data['totals']['transactions'] }} </b>
                                </h4>
                            </div>
                            <div class="col-md-3">
                                <h4>
                                    Depositado: 
                                    @if ($data['totals']['deposited'] > 0)
                                        <b style="color: #00a65a"> {{ $data['totals']['deposited'] }} </b>
                                    @else
                                        <b style="color: #dd4b39"> {{ $data['totals']['deposited'] }} </b>
                                    @endif
                                </h4>
                            </div>
                            <div class="col-md-3">
                                <h4>
                                    Saldo: 
                                    @if ($data['totals']['balance'] >= 0)
                                        <b style="color: #00a65a"> {{ $data['totals']['balance'] }} </b>
                                    @else
                                        <b style="color: #dd4b39"> {{ $data['totals']['balance'] }} </b>
                                    @endif
                                </h4>
                            </div>
                            <div class="col-md-3">
                                <h4>
                                    En <b> {{ $data['totals']['days'] }} </b> días.
                                </h4>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        <div class="row">
            <div class="col-md-12">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">Transacciones</h3>

                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                    class="fa fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="box-body">
                        @if (isset($data['lists']['list']))
                            <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_1">
                                <thead>
                                    <tr role="row">
                                        <th>Fecha</th>
                                        <th>Concepto</th>
                                        <th>Debe</th>
                                        <th>Haber</th>
                                        <th>Saldo</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $list = $data['lists']['list']; ?>

                                    @for ($i = 0; $i < count($list); $i++)

                                        <?php
                                        $item = $list[$i];
                                        ?>

                                        <tr>
                                            <td>{{ $item['created_at_view'] }}</td>
                                            <td>{{ $item['concept'] }}</td>
                                            <td>{{ $item['debe'] }}</td>
                                            <td>{{ $item['haber'] }}</td>
                                            <td>{{ $item['saldo'] }}</td>
                                        </tr>
                                    @endfor
                                </tbody>
                            </table>
                        @else
                            <div class="alert alert-danger" role="alert">
                                No hay registros
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <style>
            /* The switch - the box around the slider */
            .switch {
                position: relative;
                display: inline-block;
                width: 30px;
                height: 17px;
            }

            /* Hide default HTML checkbox */
            .switch input {
                opacity: 0;
                width: 0;
                height: 0;
            }

            /* The slider */
            .slider {
                position: absolute;
                cursor: pointer;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background-color: #ccc;
                -webkit-transition: .4s;
                transition: .4s;
            }

            .slider:before {
                position: absolute;
                content: "";
                height: 13px;
                width: 13px;
                left: 2px;
                bottom: 2px;
                background-color: white;
                -webkit-transition: .4s;
                transition: .4s;
            }

            input:checked+.slider {
                background-color: #2196F3;
            }

            input:focus+.slider {
                box-shadow: 0 0 1px #2196F3;
            }

            input:checked+.slider:before {
                -webkit-transform: translateX(13px);
                -ms-transform: translateX(13px);
                transform: translateX(13px);
            }

            /* Rounded sliders */
            .slider.round {
                border-radius: 34px;
            }

            .slider.round:before {
                border-radius: 50%;
            }

            .resumen {
                margin-top: 7px;
                margin-bottom: -28px;
            }

        </style>
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
                'Mes pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf(
                    'month')],
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
            scroller: true,
        }

        var table = $('#datatable_1').DataTable(data_table_config);

        $(".delay_slide_up").delay(5000).slideUp(300);

        var invisible = 'si';

        $('#show_hide').click(function() {
            if (invisible == 'si') {
                $('.show_hide_div').css('display', 'none');
                invisible = 'no';
            } else {
                $('.show_hide_div').css('display', 'block');
                invisible = 'si';
            }
        });

        $('#activate_summary').click(function() {
            if ($('#activate_summary').is(":checked")) {
                $('#activate_closing').prop('checked', false);
            }
        });

        $('#activate_closing').click(function() {
            if ($('#activate_closing').is(":checked")) {
                $('#activate_summary').prop('checked', false);
            }
        });

        $('#group_branch_user').selectize({
            delimiter: ',',
            persist: false,
            openOnFocus: true,
            valueField: 'id',
            labelField: 'description',
            searchField: 'description',
            maxItems: 1,
            options: {!! $data['lists']['group_branch_user_list'] !!}
        });

        var inputs = {!! $data['filters'] !!};

        $('#timestamp').val(inputs.timestamp);
        $('#search_type').val(inputs.search_type);
        $('#show').val(inputs.show).change();
        $('#group_branch_user').selectize()[0].selectize.setValue(inputs.group_branch_user, false);

        $('#activate_summary').prop('checked', (inputs.activate_summary == 'on') ? true : false);
        $('#activate_closing').prop('checked', (inputs.activate_closing == 'on') ? true : false);
    </script>
@endsection
