@extends('layout')

@section('title')
Comisiones en Línea - Reporte
@endsection
@section('content')

<?php
//Variable que se usa en todo el documento 
$commissions_transactions_client = $data['lists']['commissions_transactions_client'];
$totals_in_brands_and_services = $data['lists']['totals_in_brands_and_services'];
$totals_in_brands_and_services_aux = $data['lists']['totals_in_brands_and_services_aux'];
$atms = $data['lists']['atms'];
$services = $data['lists']['services'];
$json = $data['lists']['json'];
$total = $data['total'];

$timestamp = $data['inputs']['timestamp'];
$atm_id = $data['inputs']['atm_id'];
$service_id = $data['inputs']['service_id'];
?>

<section class="content-header">
    <div class="row">
        <div class="col-md-12">
            @include('partials._flashes')
        </div>
    </div>
</section>

<section class="content">

    <style>
        .on_hover {}
    </style>

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
            <h3 class="box-title" style="font-size: 25px;">Comisiones en Línea - Reporte
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
                    {!! Form::open(['route' => 'commissions_transactions_client', 'method' => 'POST', 'role' => 'form', 'id' => 'form_search']) !!}
                    <div class="row">

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="timestamp">Fecha:</label>
                                <input type="text" class="form-control" style="display:block" id="timestamp" name="timestamp" placeholder="Seleccionar fecha."></input>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="atm_id">Buscar por Terminal:</label>
                            <div class="form-group">
                                <select name="atm_id" id="atm_id" class="select2" style="width: 100%"></select>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="service_id">Buscar por Servicio:</label>
                            <div class="form-group">
                                <select name="service_id" id="service_id" class="select2" style="width: 100%"></select>
                            </div>
                        </div>
                    </div>

                    @if (count($totals_in_brands_and_services) > 0)
                    <div class="row">
                        <div class="col-md-4"></div>

                        <div class="col-md-4">
                            <h3>Total de comisión para el punto: {{ $total }}</h3>
                        </div>

                        <div class="col-md-4"></div>
                    </div>
                    @endif

                    <input name="json" id="json" type="hidden">
                    <input name="totals_in_brands_and_services_aux" id="totals_in_brands_and_services_aux" type="hidden">

                    {!! Form::close() !!}
                </div>
            </div>


            @if (count($totals_in_brands_and_services) > 0)

            <div class="box box-default" style="border: 1px solid #d2d6de;">
                <div class="box-header with-border">
                    <h3 class="box-title">Totales por Terminal</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row">
                        @foreach ($totals_in_brands_and_services as $key => $value)

                        <?php
                        $parameters = [
                            "description" => $key,
                            "total" => $value['total']
                        ];

                        $parameters = json_encode($parameters);
                        ?>

                        <div class="col-md-4" onclick="filter_datatable({{ $parameters }})" style="cursor: pointer">
                            <div class="callout callout-default" style="border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px">
                                <div class="row">
                                    <div class="col-md-8">
                                        <h4 class="on_hover">{{ $key }}</h4>
                                    </div>
                                    <div class="col-md-4">
                                        <h5 style="float:right">Total de comisión: <b>{{ $value['total'] }}</b></h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>


            @if (count($totals_in_brands_and_services) <= 8) <div class="box box-default" style="border: 1px solid #d2d6de;">
                <div class="box-header with-border">
                    <h3 class="box-title">Totales por Terminal y Servicios</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row">
                        @foreach ($totals_in_brands_and_services as $key => $value)
                        <div class="col-md-6">
                            <div class="callout callout-default" style="border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px">
                                <h4>{{ $key }}</h4>

                                <hr />

                                <h5>Servicios con comisión: </h5>

                                @foreach ($value as $sub_key => $sub_value)
                                <?php
                                $parameters = [
                                    "description" => $sub_key,
                                    "total" => $sub_value
                                ];

                                $parameters = json_encode($parameters);
                                ?>

                                @if ($sub_key !== 'total')
                                <h5 onclick="filter_datatable({{ $parameters }})" style="cursor: pointer" class="on_hover">{{ $sub_key }}: <b style="float: right">{{ $sub_value }}</b> </h5>
                                @endif
                                @endforeach

                                <hr />

                                <h5 style="float:right">Total de comisión: <b>{{ $value['total'] }}</b></h5> <br />
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
        </div>

        @else

        <div class="row">
            <div class="col-md-12">
                <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_2">
                    <thead>
                        <tr>
                            <th>Terminal</th>
                            <th>Servicio</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($totals_in_brands_and_services as $key => $value)

                        @foreach ($value as $sub_key => $sub_value)
                        @if ($sub_key !== 'total')

                        <tr>
                            <td>{{ $key }}</td>
                            <td>{{ $sub_key }}</td>
                            <td><b>{{ $sub_value }}</b></td>
                        </tr>

                        @endif
                        @endforeach

                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>


        @endif

        @endif

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
                    <th>Servicio</th>
                    <th>ID - Transacción</th>
                    <th>Fecha - Hora</th>
                    <th>Monto</th>
                    <th>Valor neto para el punto</th>
                </tr>
            </thead>
            <tbody>
                @if (count($commissions_transactions_client) > 0)
                @foreach ($commissions_transactions_client as $item)
                <tr>
                    <td>{{ $item['terminal'] }}</td>
                    <td>{{ $item['service'] }}</td>
                    <td>{{ $item['transaction_id'] }}</th>
                    <td>{{ $item['timestamp'] }}</th>
                    <td>{{ $item['amount'] }}</th>
                    <td> <b> {{ $item['net_worth_for_the_point'] }} </b> </th>
                </tr>
                @endforeach
                @endif
            </tbody>
            <!--<tfoot>
                <tr>
                    <th colspan="6" style="text-align:right">
                        <h4><b>Total:</b></h4>
                    </th>
                    <th>
                        <h4><b>{{ $total }}</b></h4>
                    </th>
                </tr>
            </tfoot>-->
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

        $('#form_search').append(input);
        $('#form_search').submit();
    }

    //-----------------------------------------------------------------------------------------------

    function filter_datatable(parameters) {
        var status = parameters['description'];
        var amount = parameters['total'];

        if (amount > 0) {
            var datatable_1_filter = $('#datatable_1_filter > label > input');
            datatable_1_filter.val(status);

            var e = $.Event("keyup", {
                which: 13
            });
            datatable_1_filter.trigger(e);

            $('html, body').animate({
                scrollTop: datatable_1_filter.offset().top
            }, 500);
        } else {
            status = (status == '') ? 'Total' : status;

            swal({
                    title: status + ': \n 0 TRANSACCIONES',
                    text: '0 Gs. en total \n Representa el 0% \n \n Obs: Los filtros de búsquedas \n delimitan los resultados.',
                    type: 'info',
                    showCancelButton: false,
                    closeOnClickOutside: false,
                    confirmButtonColor: '#3c8dbc',
                    confirmButtonText: 'Aceptar'
                },
                function(isConfirm) {
                    if (isConfirm) {

                    }
                }
            );
        }
    }

    //-----------------------------------------------------------------------------------------------

    /*function input_search_clean() {
        //$('#datatable_1_filter > label > input').val('');

        var e = jQuery.Event("keydown");
        e.which = 8; // # Some key code value
        $("#datatable_1_filter > label > input").trigger(e);
    }

    var button_html = '&nbsp; <button class="btn btn-default"';
    button_html += 'onclick="input_search_clean()">';
    button_html += '<i class="fa fa-eye"></i > &nbsp; Ver todos las filas';
    button_html += '</button>';*/

    //Datatable config
    var data_table_config_1 = {
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

            //$('#datatable_1_filter').append(button_html);
        }
    }

    //Datatable config
    var data_table_config_2 = {
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
        processing: true
    }

    var table = $('#datatable_1').DataTable(data_table_config_1);

    $('#datatable_2').DataTable(data_table_config_2);

    // Order by the grouping
    $('#datatable_1 tbody').on('click', 'tr.group', function() {
        var currentOrder = table.order()[0];
        if (currentOrder[0] === groupColumn && currentOrder[1] === 'asc') {
            table.order([groupColumn, 'desc']).draw();
        } else {
            table.order([groupColumn, 'asc']).draw();
        }
    });

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

    $(".on_hover").mouseenter(function() {
        $(this).css({
            'cursor': 'pointer',
            'color': '#0073b7'
        });
    }).mouseleave(function() {
        $(this).css({
            'color': 'black'
        });
    });

    //-----------------------------------------------------------------------------------------------

    // Asignación de lo que se exportará luego.
    var json = '{!! $json !!}';
    json = JSON.parse(json);
    json = JSON.stringify(json);
    $('#json').val(json);
    //console.log('JSON:', $('#json').val());

    //-----------------------------------------------------------------------------------------------

    // Asignación de lo que se exportará luego.
    var totals_in_brands_and_services_aux = '{!! $totals_in_brands_and_services_aux !!}';
    totals_in_brands_and_services_aux = JSON.parse(totals_in_brands_and_services_aux);
    totals_in_brands_and_services_aux = JSON.stringify(totals_in_brands_and_services_aux);
    $('#totals_in_brands_and_services_aux').val(totals_in_brands_and_services_aux);
    //console.log('totals_in_brands_and_services_aux:', $('#totals_in_brands_and_services_aux').val());

    //-----------------------------------------------------------------------------------------------

    var atms = '{!! $atms !!}';
    atms = JSON.parse(atms);

    //console.log('atms:', atms);

    var services = {!! $services !!}; // La única forma que no se ven los caracteres raros
    //services = JSON.parse(services);

    //console.log('services:', services);

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



        $('#service_id').val(null).trigger('change');
        $('#service_id').empty().trigger("change");

        var option = new Option('Todos', 'Todos', false, false);
        $('#service_id').append(option);

        for (var i = 0; i < services.length; i++) {
            var item = services[i];
            var id = item.id;
            var description = item.description;
            var option = new Option(description, id, false, false);
            $('#service_id').append(option);
        }

        $('#service_id').val(null).trigger('change');
        $('#service_id').val("{{ $service_id }}").trigger('change');

    }
</script>
@endsection