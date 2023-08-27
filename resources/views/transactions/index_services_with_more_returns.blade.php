@extends('layout')

@section('title')
Servicios que requieren más devoluciones - Reporte
@endsection
@section('content')

<?php
//Variable que se usa en todo el documento 

$transactions = $data['lists']['transactions'];
$json = $data['lists']['json'];

//Combos
$transaction_status = $data['lists']['transaction_status'];
$services_providers_sources = $data['lists']['services_providers_sources'];
$devolution_status = $data['lists']['devolution_status'];
$users = $data['lists']['users'];

//Valor de campos
$created_at = $data['inputs']['created_at'];
$transaction_id = $data['inputs']['transaction_id'];
$transaction_devolution_id = $data['inputs']['transaction_devolution_id'];
$amount = $data['inputs']['amount'];
$transaction_status_id = $data['inputs']['transaction_status_id'];
$service_source_id = $data['inputs']['service_source_id'];
$service_id = $data['inputs']['service_id'];
$user_id = $data['inputs']['user_id'];

//Variables para totales.
$transactions_count_total = 0;
$transactions_amount_total = 0;
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
            <h3 class="box-title" style="font-size: 25px;">Servicios que requieren más devoluciones - Reporte
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
                    {!! Form::open(['route' => 'cms_services_with_more_returns_index', 'method' => 'POST', 'role' => 'form', 'id' => 'form_search']) !!}
                    <div class="row">

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="created_at">Buscar por Fecha:</label>
                                <input type="text" class="form-control" style="display:block" id="created_at" name="created_at" placeholder="Seleccionar fecha."></input>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="service_source_id">Buscar por Proveedor:</label>
                            <div class="form-group">
                                <select name="service_source_id" id="service_source_id" class="select2" style="width: 100%"></select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="service_id">Servicio por marca:</label>
                            <div class="form-group">
                                <select name="service_id" id="service_id" class="select2" style="width: 100%"></select>
                            </div>
                        </div>
                    </div>

                    <div class="row" style="display: none">

                        <div class="col-md-3" style="display: none">
                            <div class="form-group">
                                <label for="transaction_id">Buscar por Transacción ID:</label>
                                <input type="number" class="form-control" id="transaction_id" name="transaction_id" placeholder="Transacción ID"></input>
                            </div>
                        </div>

                        <div class="col-md-3" style="display: none">
                            <div class="form-group">
                                <label for="transaction_id">Buscar por Transacción Devolución ID:</label>
                                <input type="number" class="form-control" id="transaction_devolution_id" name="transaction_devolution_id" placeholder="Transacción Devolución ID"></input>
                            </div>
                        </div>

                        <div class="col-md-2" style="display: none">
                            <div class="form-group">
                                <label for="amount">Buscar por Monto:</label>
                                <input type="number" class="form-control" id="amount" name="amount" placeholder="Monto"></input>
                            </div>
                        </div>


                        <div class="col-md-4" >
                            <label for="transaction_status_id">Buscar por Estado:</label>
                            <div class="form-group">
                                <select name="transaction_status_id" id="transaction_status_id" class="select2" style="width: 100%"></select>
                            </div>
                        </div>
                    </div>

                    <input name="json" id="json" type="hidden">

                    {!! Form::close() !!}
                </div>
            </div>

            <div class="box box-default" style="border: 1px solid #d2d6de;">
                <div class="box-header with-border">
                    <h3 class="box-title">Resumen de totales:</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-2"></div>
                        <div class="col-md-4">
                            <div class="info-box" style="background-color: aliceblue !important; color: #444;">
                                <span class="info-box-icon"><i class="fa fa-list"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Cantidad de Transacciones</span>
                                    <span class="info-box-number" style="font-size: 30px" id="number_of_transactions">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box" style="background-color: aliceblue !important; color: #444;">
                                <span class="info-box-icon"><i class="fa fa-money"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Monto total de Transacciones</span>
                                    <span class="info-box-number" style="font-size: 30px" id="total_amount_of_transactions">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-2"></div>
                    </div>
                </div>
            </div>

            <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_1">
                <thead>
                    <tr>
                        <th>Proveedor</th>
                        <th>Servicio</th>
                        <th>Cantidad de transacciones</th>
                        <th>Monto total de transacciones</th>
                        <th>Promedio de monto</th>
                    </tr>
                </thead>
                <tbody>

                    @if (count($transactions) > 0)

                    <?php
                    $transactions_count_total = 0;
                    $transactions_amount_total = 0;
                    ?>

                    @foreach ($transactions as $item)

                    <?php
                    $provider = $item->provider;
                    $service = $item->service;
                    $transactions_count = $item->transactions_count;
                    $transactions_amount = $item->transactions_amount;
                    $transactions_amount_avg = $item->transactions_amount_avg;

                    $transactions_count_total += $transactions_count;
                    $transactions_amount_total += $transactions_amount;
                    ?>

                    <tr>
                        <td>{{ $provider }}</td>
                        <td>{{ $service }}</td>
                        <td>{{ $transactions_count }}</td>
                        <td>{{ $transactions_amount }}</td>
                        <td>{{ $transactions_amount_avg }}</td>
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

<!-- iCheck -->
<link rel="stylesheet" href="/bower_components/admin-lte/plugins/iCheck/square/grey.css">
<link rel="stylesheet" href="/bower_components/admin-lte/plugins/iCheck/square/orange.css">
<script src="/bower_components/admin-lte/plugins/iCheck/icheck.min.js"></script>

<!-- jQuery Number Format -->
<script src="/js/number_format/jquery.number.js"></script>

<!-- Iniciar objetos -->
<script type="text/javascript">

    $('.select2').select2();

    //-----------------------------------------------------------------------------------------------

    function get_services_by_brand_for_transactions(service_id) {

        var url = '/get_services_by_brand_for_transactions/';

        var service_source_id = parseInt($('#service_source_id').val());
        service_source_id = (Number.isNaN(service_source_id)) ? 'Todos' : service_source_id;

        var json = {
            _token: token,
            service_source_id: service_source_id
        };

        $.post(url, json, function(data, status) {

            $('#service_id').val(null).trigger('change');
            $('#service_id').empty().trigger("change");

            var option = new Option('Todos', 'Todos', false, false);
            $('#service_id').append(option);

            for (var i = 0; i < data.length; i++) {
                var item = data[i];
                var id = item.id;
                var description = item.description;
                var option = new Option(description, id, false, false);
                $('#service_id').append(option);
            }

            if (service_id !== null) {
                $('#service_id').val(service_id).trigger('change');
            }
        });
    }

    //-----------------------------------------------------------------------------------------------

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


    $('#created_at').val("{{ $created_at }}");
    $('#transaction_id').val("{{ $transaction_id }}");
    $('#transaction_devolution_id').val("{{ $transaction_devolution_id }}");
    $('#amount').val("{{ $amount }}");

    var number_of_transactions = $.number("{{ $transactions_count_total }}", 0, ',', '.');
    var total_amount_of_transactions = $.number("{{ $transactions_amount_total }}", 0, ',', '.');

    $('#number_of_transactions').html(number_of_transactions);
    $('#total_amount_of_transactions').html(total_amount_of_transactions);

    var json = {!!$json!!};
    json = JSON.stringify(json);
    $('#json').val(json);

    //Fecha
    if ($('#created_at').val() == '' || $('#created_at').val() == 0) {
        var d = new Date();
        var date = new Date(d.getFullYear(), d.getMonth(), d.getDate());
        var day = ('0' + date.getDate()).slice(-2);
        var month = ('0' + (date.getMonth() + 1)).slice(-2);
        var year = date.getFullYear()
        var init = day + '/' + month + '/' + year + ' 00:00:00';
        var end = day + '/' + month + '/' + year + ' 23:59:59';
        var aux = init + ' - ' + end;

        $('#created_at').val(aux);
    }

    $('#created_at').daterangepicker({
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

    $('#created_at').attr({
        'onkeydown': 'return false'
    });

    $('#created_at').hover(function() {
        $('#created_at').attr({
            'title': 'El filtro de fecha es: ' + $('#created_at').val()
        });
    }, function() {

    });

    window.onload = function() {

        var devolution_status = '{!! $devolution_status !!}';
        devolution_status = JSON.parse(devolution_status);

        for (var i = 0; i < devolution_status.length; i++) {
            var item = devolution_status[i];
            var id = item.id;
            var description = item.description;
            var option1 = new Option(description, id, false, false);
            var option2 = new Option(description, id, false, false);
            $('#devolution_status_id_old').append(option1);
            $('#devolution_status_id_new').append(option2);
        }

        $('#devolution_status_id_old').val(null).trigger('change');
        $('#devolution_status_id_new').val(null).trigger('change');

        var users = '{!! $users !!}';
        users = JSON.parse(users);

        for (var i = 0; i < users.length; i++) {
            var item = users[i];
            var id = item.id;
            var description = item.description;
            var option3 = new Option(description, id, false, false);
            var option4 = new Option(description, id, false, false);
            $('#user_id_old').append(option3);
            $('#user_id_new').append(option4);
        }

        $('#user_id_old').val(null).trigger('change');
        $('#user_id_new').val(null).trigger('change');

        //-----------------------------------------------

        $('#transaction_status_id').val(null).trigger('change');
        $('#transaction_status_id').empty().trigger('change');

        var option = new Option('Todos', 'Todos', false, false);
        $('#transaction_status_id').append(option);

        var transaction_status = '{!! $transaction_status !!}';
        transaction_status = JSON.parse(transaction_status);

        for (var i = 0; i < transaction_status.length; i++) {
            var item = transaction_status[i];
            var id = item.id;
            var description = item.description;
            var option = new Option(description, id, false, false);
            $('#transaction_status_id').append(option);
        }

        $('#transaction_status_id').val(null).trigger('change');
        $('#transaction_status_id').val("{{ $transaction_status_id }}").trigger('change');

        //-----------------------------------------------

        $('#service_source_id').val(null).trigger('change');
        $('#service_source_id').empty().trigger('change');

        var option = new Option('Todos', 'Todos', false, false);
        $('#service_source_id').append(option);

        var services_providers_sources = '{!! $services_providers_sources !!}';
        services_providers_sources = JSON.parse(services_providers_sources);

        for (var i = 0; i < services_providers_sources.length; i++) {
            var item = services_providers_sources[i];
            var id = item.id;
            var description = item.description;
            var option = new Option(description, id, false, false);
            $('#service_source_id').append(option);
        }

        $('#service_source_id').val(null).trigger('change');
        $('#service_source_id').val("{{ $service_source_id }}").trigger('change');

        //-----------------------------------------------

        get_services_by_brand_for_transactions("{{ $service_id }}");

        $('.select2').on('select2:select', function(e) {

            var id = e.currentTarget.id;

            var value_all_selected = 'Todos';

            switch (id) {
                case 'service_source_id':
                    get_services_by_brand_for_transactions('Todos');
                    break;
            }
        });
    };
</script>
@endsection