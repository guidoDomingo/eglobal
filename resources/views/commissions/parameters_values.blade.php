@extends('layout')

@section('title')
Tarifario de Comisiones - Reporte
@endsection
@section('content')

<?php
//Variable que se usa en todo el documento 
$message = $data['message'];
$total = $data['total'];

$parameters_values = $data['lists']['parameters_values'];
$totals_by_type_of_commission = $data['lists']['totals_by_type_of_commission'];
$total_by_providers = $data['lists']['total_by_providers'];
$json = $data['lists']['json'];

//Combos
$services_providers_sources = $data['lists']['services_providers_sources'];
$services_providers_sources_id = $data['inputs']['services_providers_sources_id'];
$service_by_brand_id = $data['inputs']['service_by_brand_id'];
?>

<section class="content-header">

    <div class="row">
        <div class="col-md-12">
            @include('partials._flashes')
        </div>
    </div>

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
            <h3 class="box-title" style="font-size: 25px;">Tarifario de Comisiones - Reporte
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
                    {!! Form::open(['route' => 'commissions_parameters_values', 'method' => 'POST', 'role' => 'form', 'id' => 'form_search']) !!}
                    <div class="row">
                        <div class="col-md-3">
                            <label for="services_providers_sources_id">Buscar por Proveedor:</label>
                            <div class="form-group">
                                <select name="services_providers_sources_id" id="services_providers_sources_id" class="select2" style="width: 100%"></select>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="service_by_brand_id">Servicio por marca:</label>
                            <div class="form-group">
                                <select name="service_by_brand_id" id="service_by_brand_id" class="select2" style="width: 100%"></select>
                            </div>
                        </div>
                    </div>

                    <input name="json" id="json" type="hidden">

                    {!! Form::close() !!}
                </div>
            </div>

      
            <!--
            <div class="row">
                <div class="col-md-6">

                    <div class="box box-default" style="border: 1px solid #d2d6de;">
                        <div class="box-header with-border">
                            <h3 class="box-title">Cantidad total por tipo Comisión</h3>
                            <div class="box-tools pull-right">
                                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="box-body">
                            <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_2">
                                <thead>
                                    <tr>
                                        <th>Tipo de Comisión</th>
                                        <th>Registros</th>
                                    </tr>
                                </thead>
                                <tbody>




                                    @foreach ($totals_by_type_of_commission as $key => $value)

                                    <?php
                                    $parameters = [
                                        "description" => $key,
                                        "total" => $value
                                    ];

                                    $parameters = json_encode($parameters);
                                    ?>

                                    <tr onclick="filter_datatable({{ $parameters }})" style="cursor: pointer">
                                        <td>{{ $key }}</td>
                                        <td>{{ $value }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <div class="col-md-6">
                    <div class="box box-default" style="border: 1px solid #d2d6de;">
                        <div class="box-header with-border">
                            <h3 class="box-title">Cantidad total por Proveedor</h3>
                            <div class="box-tools pull-right">
                                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                            </div>
                        </div>
                        <div class="box-body">
                            <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_3">
                                <thead>
                                    <tr>
                                        <th>Servicio</th>
                                        <th>Registros</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($total_by_providers as $key => $value)

                                    <?php
                                    $parameters = [
                                        "description" => $key,
                                        "total" => $value
                                    ];

                                    $parameters = json_encode($parameters);
                                    ?>

                                    <tr onclick="filter_datatable({{ $parameters }})" style="cursor: pointer">
                                        <td>{{ $key }}</td>
                                        <td>{{ $value }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            -->

            <!--
            @if (count($totals_by_type_of_commission) > 0)

            <div class="box box-default" style="border: 1px solid #d2d6de;">
                <div class="box-header with-border">
                    <h3 class="box-title">Total por Tipo de Comisión:</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row">
                        @foreach ($totals_by_type_of_commission as $key => $value)

                        <?php
                        $parameters = [
                            "description" => $key,
                            "total" => $value
                        ];

                        $parameters = json_encode($parameters);
                        ?>

                        <div class="col-md-4" onclick="filter_datatable({{ $parameters }})" style="cursor: pointer">
                            <div class="callout callout-default" style="border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px">
                                <h4>{{ $key }}</h4>

                                <hr />

                                <h4 style="float:right">Total: <b>{{ $value }} registros</b></h4> <br />
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            @if (count($total_by_providers) > 0)
            
            <div class="box box-default" style="border: 1px solid #d2d6de;">
                <div class="box-header with-border">
                    <h3 class="box-title">Total por Proveedor:</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row">
                        @foreach ($total_by_providers as $key => $value)

                        <?php
                        $parameters = [
                            "description" => $key,
                            "total" => $value
                        ];

                        $parameters = json_encode($parameters);
                        ?>

                        <div class="col-md-4" onclick="filter_datatable({{ $parameters }})" style="cursor: pointer">
                            <div class="callout callout-default" style="border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px">
                                <div class="row">
                                    <div class="col-md-6">
                                        <h4>{{ $key }}</h4>
                                    </div>
                                    <div class="col-md-6">
                                        <h4 style="float:right">Total: <b>{{ $value }} registros</b></h4> <br />
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        -->

            <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_1">
                <thead>
                    <tr>
                        <th colspan="3">Información del Servicio</th>
                        <th>Parámetros de Comisiones</th>
                    </tr>
                    <tr>
                        <th></th>
                        <th>Proveedor</th>
                        <th>Servicio</th>
                        <th>Lista de parámetros</th>
                    </tr>
                </thead>
                <tbody>

                    @if (count($parameters_values) > 0)

                    @foreach ($parameters_values as $item)

                    <?php

                    $provider = $item->provider;
                    $service = $item->service;
                    $details = json_decode($item->parameters_values_details);
                    $details_count = [];

                    $table_details = '<table class="table table-responsive table-bordered table-hover dataTable" data="datatable_detail">';

                    $table_details .= "<thead>";
                    $table_details .= "<th></th>";
                    $table_details .= "<th>Vigencia &nbsp;&nbsp;</th>";
                    $table_details .= "<th>Tipo</th>";
                    $table_details .= "<th>Fijo</th>";
                    $table_details .= "<th>Porcentual</th>";
                    $table_details .= "<th>Mínimo</th>";
                    $table_details .= "<th>Máximo</th>";
                    $table_details .= "<th>Punto</th>";
                    $table_details .= "<th>Estándar</th>";
                    $table_details .= "</thead>";

                    $table_details .= "<tbody>";

                    foreach ($details as $sub_item) {

                        $parameters_id = $sub_item->parameters_id;
                        $validity = $sub_item->validity;
                        $commission_type = $sub_item->commission_type;
                        $value_fixed = $sub_item->value_fixed;
                        $value_percentage = $sub_item->value_percentage;
                        $value_min = $sub_item->value_min;
                        $value_max = $sub_item->value_max;
                        $contract_value_for_the_point = $sub_item->contract_value_for_the_point;
                        $standard_calculation = $sub_item->standard_calculation;

                        $table_details .= '<tr>';
                        $table_details .= "<td> $validity </td>";
                        $table_details .= "<td></td>";
                        $table_details .= "<td> $commission_type </td>";
                        $table_details .= "<td> $value_fixed </td>";
                        $table_details .= "<td> $value_percentage </td>";
                        $table_details .= "<td> $value_min </td>";
                        $table_details .= "<td> $value_max </td>";
                        $table_details .= "<td> $contract_value_for_the_point </td>";
                        $table_details .= "<td> $standard_calculation </td>";
                        $table_details .= '</tr>';

                        if (!isset($details_count["$parameters_id"])) {
                            $details_count["$parameters_id"] = 1;
                        } else {
                            $details_count["$parameters_id"] .= 1;
                        }
                    }

                    $table_details .= "</tbody>";

                    $table_details .= '</table>';

                    
                    ?>

                    <tr>
                        <td>{{ $provider }}</td>
                        <td></td>
                        <td><b>{{ $service }} </b><br/><br/>

                        Cabeceras: <br/>

                        @foreach ($details_count as $details_count_key => $details_count_value)

                            #{{ $details_count_key }}: {{$details_count_value }} parámetro/s <br/>

                        @endforeach
                        </td>
                        <td>{!! $table_details !!}</th>
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

<!-- Iniciar objetos -->
<script type="text/javascript">
    function get_services_by_brand(service_by_brand_id) {

        var url = '/get_services_by_brand/';

        var services_providers_sources_id = parseInt($('#services_providers_sources_id').val());
        services_providers_sources_id = (Number.isNaN(services_providers_sources_id)) ? 'Todos' : services_providers_sources_id;

        var json = {
            _token: token,
            services_providers_sources_id: services_providers_sources_id
        };

        $.post(url, json, function(data, status) {

            $('#service_by_brand_id').val(null).trigger('change');
            $('#service_by_brand_id').empty().trigger("change");

            var option = new Option('Todos', 'Todos', false, false);
            $('#service_by_brand_id').append(option);

            for (var i = 0; i < data.length; i++) {
                var item = data[i];
                var id = item.id;
                var description = item.description;
                var option = new Option(description, id, false, false);
                $('#service_by_brand_id').append(option);
            }

            if (service_by_brand_id !== null) {
                $('#service_by_brand_id').val(service_by_brand_id).trigger('change');
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
        order: [
            [1, 'asc']
        ]
    }

    $('#datatable_2').DataTable(data_table_config);
    $('#datatable_3').DataTable(data_table_config);

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
                .each(function(column_data, i) {

                    if (last !== column_data) {

                        var color = '#d2d6de';

                        var td = $('<td>');
                        td.attr({
                            'colspan': '3',
                            'style': 'color: #333 !important'
                        }).append(column_data);

                        var tr = $('<tr>');
                        tr.attr({
                            'class': 'group',
                            'style': 'background-color:' + color + ' !important; font-weight: bold; cursor: pointer'
                        }).append(td);

                        $(rows).eq(i).before(tr);

                        last = column_data;
                    }
                });
        },
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

    $('[data="datatable_detail"]').DataTable({

        bPaginate: false,
        bFilter: false,
        bInfo: false,

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
                .each(function(column_data, i) {

                    if (last !== column_data) {

                        var color = '#d2d6de';

                        var td = $('<td>');
                        td.attr({
                            'colspan': '8',
                            'style': 'color: #333 !important'
                        }).append(column_data);

                        var tr = $('<tr>');
                        tr.attr({
                            'class': 'group',
                            'style': 'background-color:' + color + ' !important; font-weight: bold; cursor: pointer'
                        }).append(td);

                        $(rows).eq(i).before(tr);

                        last = column_data;
                    }
                });
        }
    });


    //-----------------------------------------------------------------------------------------------

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
    json = JSON.stringify(json);
    $('#json').val(json);

    //-----------------------------------------------------------------------------------------------

    window.onload = function() {

        $('.select2').select2();

        $('#services_providers_sources_id').val(null).trigger('change');
        $('#services_providers_sources_id').empty().trigger("change");

        var option = new Option('Todos', 'Todos', false, false);
        $('#services_providers_sources_id').append(option);

        var services_providers_sources = '{!! $services_providers_sources !!}';
        services_providers_sources = JSON.parse(services_providers_sources);

        for (var i = 0; i < services_providers_sources.length; i++) {
            var item = services_providers_sources[i];
            var id = item.id;
            var description = item.description;
            var option = new Option(description, id, false, false);
            $('#services_providers_sources_id').append(option);
        }

        $('#services_providers_sources_id').val(null).trigger('change');
        $('#services_providers_sources_id').val("{{ $services_providers_sources_id }}").trigger('change');

        get_services_by_brand("{{ $service_by_brand_id }}");

        $('.select2').on('select2:select', function(e) {

            var id = e.currentTarget.id;

            var value_all_selected = 'Todos';

            switch (id) {
                case 'services_providers_sources_id':
                    get_services_by_brand('Todos');
                    break;
            }
        });
    };
</script>
@endsection