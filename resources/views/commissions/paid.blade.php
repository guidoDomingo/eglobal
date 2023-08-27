@extends('layout')

@section('title')
Comisiones pagadas - Reporte
@endsection
@section('content')
<?php
//Variable que se usa en todo el documento 
$payments_detail_aux = $data['lists']['payments_detail_aux'];
//var_dump($payments_detail_aux);
//die();
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
            <h3 class="box-title" style="font-size: 25px;">Comisiones pagadas - Reporte
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
                    {!! Form::open(['route' => 'commissions_paid', 'method' => 'POST', 'role' => 'form', 'id' => 'form_search']) !!}
                    <div class="row">
                        <div class="col-md-6">
                            <label for="payments_id">Buscar por Cabecera de Pago:</label>
                            <div class="form-group">
                                <input type="text" class="form-control" id="payments_id" name="payments_id" placeholder="Buscar por pago"></input>
                            </div>
                        </div>

                        <div class="col-md-6" style="display: none">
                            <label for="services_providers_sources_id">Buscar por Proveedor:</label>
                            <div class="form-group">
                                <input type="text" class="form-control" id="services_providers_sources_id" name="services_providers_sources_id" placeholder="Buscar por proveedor"></input>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="atm_id">Buscar por Terminal:</label>
                            <div class="form-group">
                                <input type="text" class="form-control" id="atm_id" name="atm_id" placeholder="Buscar por terminal"></input>
                            </div>
                        </div>
                    </div>
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
                        <th>Proveedor</th>
                        <th>Terminal</th>
                        <th>Servicio</th>
                        <th>Comisión total para el punto</th>
                        <th>Periodo</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($payments_detail_aux as $item)
                    <tr>
                        <td>{{ $item['provider'] }}</td>
                        <td>{{ $item['terminal'] }}</td>
                        <td>{{ $item['service'] }}</td>
                        <td>{{ $item['total_commission_for_the_point'] }}</th>
                        <td>{{ $item['period'] }}</th>
                    </tr>
                    @endforeach
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

<!-- iCheck -->
<link rel="stylesheet" href="/bower_components/admin-lte/plugins/iCheck/square/grey.css">
<script src="/bower_components/admin-lte/plugins/iCheck/icheck.min.js"></script>

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
            $('#div_load').css('display', 'none');
            $('#content').css('display', 'block');
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

    //$('#hide_show_columns').append('Ocultar columna/s de la tabla: <br/>');

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

        // Get the column API object
        var column = table.column($(this).attr('data-column'));

        // Toggle the visibility
        column.visible(!column.visible());
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

    $('#payments_id').selectize(selective_config)[0].selectize.addOption({!!$data['lists']['payments'] !!});
    $('#atm_id').selectize(selective_config)[0].selectize.addOption({!!$data['lists']['atms'] !!});
    $('#services_providers_sources_id').selectize(selective_config)[0].selectize.addOption({!!$data['lists']['services_providers_sources'] !!});

    $('#payments_id').selectize()[0].selectize.setValue("{{ $data['inputs']['payments_id'] }}", false);
    $('#atm_id').selectize()[0].selectize.setValue("{{ $data['inputs']['atm_id'] }}", false);
</script>
@endsection