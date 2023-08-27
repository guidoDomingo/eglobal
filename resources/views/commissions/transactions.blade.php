@extends('layout')

@section('title')
Comisiones de Transacciones - Reporte
@endsection
@section('content')
<?php
//Variable que se usa en todo el documento 
$commissions_transactions = $data['lists']['commissions_transactions'];
$atms = $data['lists']['atms'];
$services_providers_sources = $data['lists']['services_providers_sources'];
$json = $data['lists']['json'];

//inputs
$timestamp = $data['inputs']['timestamp'];
$services_providers_sources_id = $data['inputs']['services_providers_sources_id'];
$atm_id = $data['inputs']['atm_id'];

?>

<section class="content-header">

    <div class="row">
        <div class="col-md-12">
            @include('partials._flashes')
        </div>
    </div>

</section>

<section class="content">


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
            <h3 class="box-title" style="font-size: 25px;">Comisiones de Transacciones - Reporte
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
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
                    </div>
                </div>
                <div class="box-body">
                    {!! Form::open(['route' => 'commissions_transactions', 'method' => 'POST', 'role' => 'form', 'id' => 'form_search']) !!}
                    <div class="row">

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="timestamp">Fecha:</label>
                                <input type="text" class="form-control" style="display:block" id="timestamp" name="timestamp" placeholder="Seleccionar fecha."></input>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="services_providers_sources_id">Buscar por Proveedor:</label>
                            <div class="form-group">
                                <select name="services_providers_sources_id" id="services_providers_sources_id" class="select2" style="width: 100%"></select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="atm_id">Buscar por Terminal:</label>
                            <div class="form-group">
                            <select name="atm_id" id="atm_id" class="select2" style="width: 100%"></select>
                            </div>
                        </div>
                    </div>

                    <input name="json" id="json" type="hidden">

                    {!! Form::close() !!}
                </div>
            </div>


            <div class="box box-default collapsed-box" style="border: 1px solid #d2d6de;">
                <div class="box-header with-border">
                    <h3 class="box-title">Mostrar / Ocultar columnas</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
                    </div>
                </div>
                <div class="box-body" id="hide_show_columns">
                </div>
            </div>

            <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_1">
                <thead>
                    <tr>
                        <th>Terminal</th>
                        <th>Proveedor</th>
                        <th>Marca</th>
                        <th>Servicio</th>
                        <th>ID - Transacción</th>
                        <th>Fecha - Hora</th>
                        <th>Monto</th>
                        <th>Tipo de Comisión</th>
                        <th>Valor de contrato para Eglobalt</th>
                        <th>Valor bruto a repartir</th>
                        <th>Valor de contrato para el punto</th>
                        <th>Valor neto para el punto</th>
                        <th>Calculo parametrizado</th>
                    </tr>
                </thead>
                <tbody>
                    @if (count($commissions_transactions) > 0)
                    @foreach ($commissions_transactions as $item)
                    <tr>
                        <td>{{ $item['terminal'] }}</td>
                        <td>{{ $item['provider'] }}</td>
                        <td>{{ $item['brand'] }}</td>
                        <td>{{ $item['service'] }}</td>
                        <td>{{ $item['transaction_id'] }}</td>
                        <td>{{ $item['timestamp'] }}</td>
                        <td>{{ $item['amount'] }}</td>
                        <td>{{ $item['commission_type'] }}</td>
                        <td>{{ $item['contract_value_for_eglobalt'] }}</td>
                        <td>{{ $item['gross_value_to_distribute'] }}</td>
                        <td>{{ $item['net_worth_to_eglobalt'] }}</td>
                        <td>{{ $item['contract_value_for_the_point'] }}</td>
                        <td>{{ $item['parameterized_calculation'] }}</td>
                    </tr>
                    @endforeach
                    @endif
                </tbody>
            </table>
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
    var data_table_config = {
        //custom
        orderCellsTop: true,
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
        processing: true,
        initComplete: function(settings, json) {
            $('#content').css('display', 'block');
            $('#div_load').css('display', 'none');
            //$('body > div.wrapper > header > nav > a').trigger('click');
        }
    }

    // Order by the grouping
    $('#datatable_1 tbody').on('click', 'tr.group', function() {
        var currentOrder = table.order()[0];
        if (currentOrder[0] === groupColumn && currentOrder[1] === 'asc') {
            table.order([groupColumn, 'desc']).draw();
        } else {
            table.order([groupColumn, 'asc']).draw();
        }
    });

    var table = $('#datatable_1').DataTable(data_table_config);

    var hide_show_columns = [];

    var ths = $("#datatable_1").find("th");

    var index = 0;

    for (var i = index; i < ths.length; i++) {
        hide_show_columns.push(ths[i].innerHTML);
    }

    for (var i = index; i < hide_show_columns.length; i++) {

        var description = hide_show_columns[i];

        $('#hide_show_columns').append(
            '<a class="toggle-vis btn btn-default btn-sm" data-column="' + i + '" id="toggle-vis-' + i +
            '" value="' + description + '" state="on" title="Mostrar / Ocultar columna: ' + description +
            '" style="margin-top: 3px">' +
            '<i class="fa fa-eye"></i> &nbsp;' + description +
            '</a> '
        );
    }

    $('a.toggle-vis').on('click', function(e) {
        e.preventDefault();

        var column = table.column($(this).attr('data-column'));
        column.visible(!column.visible());

        var fa = (!column.visible()) ? 'eye-slash' : 'eye';
        $(this).html('<i class="fa fa-' + fa + '"></i> &nbsp;' + $(this).attr('value'));
    });

    //-----------------------------------------------------------------------------------------------

    $('#timestamp').val("{{ $timestamp }}");

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
    var json_aux = JSON.stringify(json);
    $('#json').val(json_aux);

    console.log('JSON:', $('#json').val());

    //-----------------------------------------------------------------------------------------------

    var atms = {!! $atms !!};
    //atms = JSON.parse(atms);

    //console.log('atms:', atms);

    var services_providers_sources = {!!$services_providers_sources!!}; // La única forma que no se ven los caracteres raros
    //services_providers_sources = JSON.parse(services_providers_sources);
    //-----------------------------------------------------------------------------------------------

    $('.select2').select2();

    window.onload = function() {

        $('#atm_id').val(null).trigger('change');
        $('#atm_id').empty().trigger("change");

        var option = new Option('Todos', 'Todos', false, false);
        $('#atm_id').append(option);

        for (var i = 0; i < atms.length; i++) {
            var item = atms[i];
            var id = item.id;
            var description = item.description;
            var option = new Option(description, id, false, false);
            $('#atm_id').append(option);
        }

        $('#atm_id').val(null).trigger('change');
        $('#atm_id').val("{{ $atm_id }}").trigger('change');



        $('#services_providers_sources_id').val(null).trigger('change');
        $('#services_providers_sources_id').empty().trigger("change");

        var option = new Option('Todos', 'Todos', false, false);
        $('#services_providers_sources_id').append(option);

        for (var i = 0; i < services_providers_sources.length; i++) {
            var item = services_providers_sources[i];
            var id = item.id;
            var description = item.description;
            var option = new Option(description, id, false, false);
            $('#services_providers_sources_id').append(option);
        }

        $('#services_providers_sources_id').val(null).trigger('change');
        $('#services_providers_sources_id').val("{{ $services_providers_sources_id }}").trigger('change');

    }
</script>
@endsection