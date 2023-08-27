@extends('layout')

@section('title')
Comisiones para el Cliente - Reporte
@endsection
@section('content')

<?php
//Variable que se usa en todo el documento 
$message = $data['message'];
$records = $data['lists']['records'];
$json = $data['lists']['json'];

//Combos
$atms = $data['lists']['atms'];
$services = $data['lists']['services'];
$services_providers_sources = $data['lists']['services_providers_sources'];
$services_providers_sources_id = $data['inputs']['services_providers_sources_id'];
$service_by_brand_id = $data['inputs']['service_by_brand_id'];

//Inputs

$timestamp = $data['inputs']['timestamp'];
$amount = $data['inputs']['amount'];

$equal_amount = $data['inputs']['equal_amount'];
$lesser_amount = $data['inputs']['lesser_amount'];
$higher_amount = $data['inputs']['higher_amount'];

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
        /** Para el auto incremento de la altura en el combo múltiple */
        .select2-selection--multiple {
            overflow: hidden !important;
            height: auto !important;
        }

        /** Color del item seleccionado */
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #285f6c !important;
        }
    </style>


    <div class="box box-default" style="border-radius: 5px; margin-bottom: 2000px; color: #285f6c;" id="div_load">
        <div class="box-header with-border">
            <h3 class="box-title" style="font-size: 25px;">Cargando...
            </h3>
        </div>

        <div class="box-body">
            <div style="text-align: center; font-size: 20px;">
                <div>
                    <i class="fa fa-spin fa-refresh fa-2x" style="vertical-align: sub;"></i> &nbsp;
                    <label id="label_load">Cargando datos...</label>
                </div>
            </div>
        </div>
    </div>

    <div class="box box-default" style="border-radius: 5px; display: none" id="content">
        <div class="box-header with-border">
            <h3 class="box-title" style="font-size: 25px;">Comisiones para el Cliente - Reporte</h3>
            <div class="box-tools pull-right">
                <button class="btn btn-default" type="button" title="Buscar según los filtros en los registros." style="background-color: #285f6c; color:white; margin-right: 5px;" id="search" name="search" onclick="search('search')">
                    <span class="fa fa-search"></span> Buscar
                </button>

                <button class="btn btn-default" type="button" title="Convertir tabla en archivo excel." style="background-color: #285f6c; color:white; margin-right: 5px; display: none" id="generate_x" name="generate_x" onclick="search('generate_x')">
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
                    {!! Form::open(['route' => 'commissions_for_clients', 'method' => 'POST', 'role' => 'form', 'id' => 'form_search']) !!}
                    <div class="row">
                        <div class="col-md-6">

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="timestamp">Buscar por Fecha y Hora:</label>
                                        <div class="input-group" style="border: 1px solid #285f6c;">
                                            <div class="input-group-addon" style="background-color: #285f6c; border: 1px solid #285f6c; color:white;">
                                                <i class="fa fa-calendar fa-2x"></i>
                                            </div>
                                            <input type="text" class="form-control" id="timestamp" name="timestamp" placeholder="Seleccionar fecha." style="display:block; height: 50px; border: 0 !important; font-size: 15px; font-weight: bold"></input>
                                        </div>
                                    </div>
                                </div>
                            </div>



                            <div class="row" id="div_amount">
                                <div class="col-md-12">
                                    <div class="form-group">
                                        <label for="amount">Buscar por monto de Transacción</label>
                                        <div class="input-group" style="border: 1px solid #285f6c;">
                                            <div class="input-group-addon" style="background-color: #285f6c; border: 1px solid #285f6c; color:white;">
                                                <i class="fa fa-money fa-2x"></i>
                                            </div>
                                            <input type="number" class="form-control" id="amount" name="amount" placeholder="Monto de transacción" style="display:block; height: 50px; border: 0 !important; font-size: 15px; font-weight: bold; border-bottom: 1px solid gray !important; margin-bottom: 5px;"></input>

                                            <div style="padding: 5px; text-align: right;">
                                                &nbsp; <b>Condiciones:</b> &nbsp;
                                                <input type="checkbox" id="equal_amount" name="equal_amount"></input> &nbsp; Iguales &nbsp;
                                                <input type="checkbox" id="lesser_amount" name="lesser_amount"></input> &nbsp; Menores &nbsp;
                                                <input type="checkbox" id="higher_amount" name="higher_amount"></input> &nbsp; Mayores
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>


                        </div>

                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-12">
                                    <label for="atm_id">Buscar por Terminal:</label>
                                    <div class="form-group">
                                        <select name="atm_id[]" id="atm_id" class="select2" style="width: 100%" multiple="multiple"></select>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <label for="service_id">Buscar por Servicio:</label>
                                    <div class="form-group">
                                        <select name="service_id[]" id="service_id" class="select2" style="width: 100%" multiple="multiple"></select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <input name="json" id="json" type="hidden">

                    {!! Form::close() !!}
                </div>
            </div>

            <div class="box box-default" style="border: 1px solid #d2d6de;" id="div_summary_of_totals">
                <div class="box-header with-border">
                    <h3 class="box-title">Resumen de totales:</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-4">
                            <div class="info-box" style="background-color: #285f6c !important;color: white">
                                <span class="info-box-icon"><i class="fa fa-list"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Cantidad de Transacciones</span>
                                    <span class="info-box-number" style="font-size: 30px" id="number_of_transactions">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box" style="background-color: #285f6c !important;color: white">
                                <span class="info-box-icon"><i class="fa fa-money"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Monto total de Transacciones</span>
                                    <span class="info-box-number" style="font-size: 30px" id="total_amount_of_transactions">0</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="info-box" style="background-color: #285f6c !important;color: white">
                                <span class="info-box-icon"><i class="fa fa-arrow-up"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Monto total de Comisión</span>
                                    <span class="info-box-number" style="font-size: 30px" id="total_amount_of_commissions">0</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="box box-default" style="border: 1px solid #d2d6de;" id="div_detail_by_atm">
                <div class="box-header with-border">
                    <h3 class="box-title">Resumen por terminal:</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse">
                            <i class="fa fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row" id="total_commission_per_terminal"></div>
                </div>
            </div>

            <div class="box box-default" style="border: 1px solid #d2d6de;" id="div_datatable_1">
                <div class="box-header with-border">
                    <h3 class="box-title">Detalle de Transacciones:</h3>
                </div>
                <div class="box-body">
                    <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_1">
                        <thead style="background-color: #285f6c; border: 1px solid #285f6c; color:white;" id="datatable_1_thead"></thead>
                        <tbody id="datatable_1_tbody"></tbody>
                    </table>
                </div>
            </div>

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

<!-- iCheck -->
<link rel="stylesheet" href="/bower_components/admin-lte/plugins/iCheck/square/grey.css">
<script src="/bower_components/admin-lte/plugins/iCheck/icheck.min.js"></script>

<!-- jQuery Number Format -->
<script src="/js/number_format/jquery.number.js"></script>

<!-- Iniciar objetos -->
<script type="text/javascript">
    var data_aux = <?php echo json_encode($data); ?>;

    console.log('Datos obtenidos:', data_aux);

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

            $('#label_load').html('Cargando...');
            $('#content').css('display', 'none');

            $('#div_load').css({
                'display': 'block',
                'margin-bottom': '2000px',
                'color': 'black'
            });
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

    $('#timestamp').daterangepicker({
        timePicker: true,
        timePicker24Hour: true,
        timePickerIncrement: 1,
        format: 'DD/MM/YYYY HH:mm:ss',
        startDate: moment().startOf('month'),
        endDate: moment().endOf('month'),
        opens: 'center',
        drops: 'down',
        showDropdowns: true,

        /*dateLimit: {
            'months': 1,
            'days': -1,
        },*/
        
        minDate: moment().startOf('year').subtract(5, 'year'),
        maxDate: new Date(),

        ranges: {
            'Hoy': [moment().startOf('day').toDate(), moment().endOf('day').toDate()],
            'Ayer': [moment().startOf('day').subtract(1, 'days'), moment().endOf('day').subtract(1, 'days')],
            'Semana': [moment().startOf('week'), moment().endOf('week')],
            'Semana pasada': [moment().startOf('week').subtract(1, 'week'), moment().endOf('week').subtract(1, 'week')],
            'Mes': [moment().startOf('month'), moment().endOf('month')],
            'Mes pasado': [moment().startOf('month').subtract(1, 'month'), moment().endOf('month').subtract(1, 'month')],
            'Año': [moment().startOf('year'), moment().endOf('year')],
            'Año pasado': [moment().startOf('year').subtract(1, 'year'), moment().endOf('year').subtract(1, 'year')]
        },

        locale: {
            applyLabel: 'Aplicar',
            fromLabel: 'Desde',
            toLabel: 'Hasta',
            customRangeLabel: 'Rango Personalizado',
            daysOfWeek: ['Dom', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sáb'],
            monthNames: [
                'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto',
                'Setiembre', 'Octubre', 'Noviembre', 'Diciembre'
            ],
            firstDay: 1
        }
    });

    $('#timestamp').attr({
        'onkeydown': 'return false'
    });

    $('#timestamp').hover(function() {
        $('#timestamp').attr({
            'title': 'El filtro de fecha es: ' + $('#timestamp').val()
        })
    }, function() {});

    //-----------------------------------------------------------------------------------------------

    // Asignación de lo que se exportará luego.
    var json = {};
    json = JSON.stringify(json);
    $('#json').val(json);

    //-----------------------------------------------------------------------------------------------

    $('input[type="checkbox"]').iCheck({
        checkboxClass: 'icheckbox_square-grey',
        radioClass: 'iradio_square-grey'
    });

    var equal_amount = "{{ $equal_amount }}";
    var lesser_amount = "{{ $lesser_amount }}";
    var higher_amount = "{{ $higher_amount }}";

    $('#timestamp').val('{{ $timestamp }}');
    $('#amount').val('{{ $amount }}');

    if (equal_amount !== '' || lesser_amount !== '' || higher_amount !== '') {
        if (equal_amount !== '') {
            $('#equal_amount').iCheck('check');
        }

        if (lesser_amount !== '') {
            $('#lesser_amount').iCheck('check');
        }

        if (higher_amount !== '') {
            $('#higher_amount').iCheck('check');
        }

    } else {
        $('#equal_amount').iCheck('check');
        $('#lesser_amount').iCheck('check');
        $('#higher_amount').iCheck('check');
    }

    $('.select2').select2({
        placeholder: "Seleccionar",
        allowClear: true
    });

    $('#atm_id').val(null).trigger('change');
    $('#atm_id').empty().trigger("change");

    $('#service_id').val(null).trigger('change');
    $('#service_id').empty().trigger("change");

    //--------------------------------------------------------------------------------------

    var atms = {!!$atms!!};

    for (var i = 0; i < atms.length; i++) {
        var item = atms[i];
        var id = item.id;
        var description = item.description;
        var option = new Option(description, id, false, false);
        $('#atm_id').append(option);
    }

    var atms_ids = '{!! $atm_id !!}';
    atms_ids = JSON.parse(atms_ids);

    $('#atm_id').val(null).trigger('change');
    $('#atm_id').select2('val', atms_ids);

    //--------------------------------------------------------------------------------------

    var services = {!!$services!!};

    for (var i = 0; i < services.length; i++) {
        var item = services[i];
        var id = item.id;
        var description = item.description;
        var option = new Option(description, id, false, false);
        $('#service_id').append(option);
    }

    var service_ids = '{!! $service_id !!}';
    service_ids = JSON.parse(service_ids);

    $('#service_id').val(null).trigger('change');
    $('#service_id').select2('val', service_ids);


    $(document).ready(function() {

        var json = <?php echo json_encode($records); ?>;

        var count = json.length;
        console.log('count:', count);

        if (count > 0) {
            var number_of_transactions = count;
            var total_amount_of_transactions = 0;
            var total_amount_of_commissions = 0;

            //var atms_commissions = {};
            //var atms_transactions = {};

            var atms_details = {};

            $('#datatable_1_thead').append(
                $('<tr>')
                .append($('<th>').append(''))
                .append($('<th>').append('Terminal'))
                .append($('<th>').append('Servicio'))
                .append($('<th>').append('Transacción'))
                .append($('<th>').append('Fecha y Hora'))
                .append($('<th>').append('Monto'))
                .append($('<th>').append('Comisión'))
            );

            for (var i = 0; i < count; i++) {
                var item = json[i];

                var atm_description = item.atm_description;
                var service_source_id = item.service_source_id;
                var service_id = item.service_id;
                var service = item.service;
                var transaction_id = item.transaction_id;
                var created_at = item.created_at;
                var amount = item.amount;
                var commission_net_level_1 = item.commission_net_level_1;

                var amount_view = item.amount_view;
                var commission_net_level_1_view = item.commission_net_level_1_view;

                $('#datatable_1_tbody').append(
                    $('<tr>')
                    .append($('<td>').append(atm_description))
                    .append($('<td>').append(''))
                    .append($('<td>').append(service))
                    .append($('<td>').append(transaction_id))
                    .append($('<td>').append(created_at))
                    .append($('<td>').append(amount_view))
                    .append($('<td>').append(commission_net_level_1_view))
                );

                total_amount_of_transactions += amount;
                total_amount_of_commissions += commission_net_level_1;

                /*if(!(atm_description in atms_transactions)) {
                    atms_transactions[atm_description] = 0;
                }

                if(!(atm_description in atms_commissions)) {
                    atms_commissions[atm_description] = 0;
                }

                atms_transactions[atm_description] += amount;
                atms_commissions[atm_description] += commission_net_level_1;*/

                if (!(atm_description in atms_details)) {

                    atms_details[atm_description] = {
                        transaction_count: 0,
                        transaction_total: 0,
                        commission_total: 0
                    };
                }

                atms_details[atm_description]['transaction_count'] += 1;
                atms_details[atm_description]['transaction_total'] += amount;
                atms_details[atm_description]['commission_total'] += commission_net_level_1;
            }

            console.log('atms_details:', atms_details);

            for (var key in atms_details) {

                //console.log(key + " es " + atms_commissions[key]);

                var item = atms_details[key];

                var background_color = '#285f6c';
                var commission_text = '';
                var commission_title = '';

                if (item.commission_total <= 0) {
                    background_color = 'brown';
                    commission_text = '(Sin comisión)';
                    commission_title = 'Terminal sin comisión.';
                }

                var transaction_count = $.number(item.transaction_count, 0, ',', '.');
                var transaction_total = $.number(item.transaction_total, 0, ',', '.');
                var commission_total = $.number(item.commission_total, 0, ',', '.');

                var html = '<div class="col-md-6" title="' + commission_title + '">';
                html += '<div class="info-box" style="background-color: ' + background_color + ' !important; color: white">';
                html += '<span class="info-box-icon"><i class="fa fa-server"></i></span>';
                html += '<div class="info-box-content">';
                html += '<span class="info-box-text"><b>' + key + '</b></span>';
                html += '<span class="info-box-text">Cantidad de Transacciones: <b style="float: right">' + transaction_count + '</b></span>';
                html += '<span class="info-box-text">Monto total de transacciones: <b style="float: right">' + transaction_total + '</b></span>';
                html += '<span class="info-box-text">Monto total de comisión: <b style="float: right">' + commission_total + ' ' + commission_text + '</b></span>';
                html += '</div>';
                html += '</div>';
                html += '</div>';

                $('#total_commission_per_terminal').append(html);
            }

            number_of_transactions = $.number(number_of_transactions, 0, ',', '.');
            total_amount_of_transactions = $.number(total_amount_of_transactions, 0, ',', '.');
            total_amount_of_commissions = $.number(total_amount_of_commissions, 0, ',', '.');

            $('#number_of_transactions').html(number_of_transactions);
            $('#total_amount_of_transactions').html(total_amount_of_transactions);
            $('#total_amount_of_commissions').html(total_amount_of_commissions);


            //-----------------------------------------------------------------------------------------------

            var groupColumn = 0;

            var table = $('#datatable_1').DataTable({
                orderCellsTop: true,
                fixedHeader: true,
                pageLength: 10,
                lengthMenu: [
                    1, 2, 5, 10, 20, 30, 50, 70, 100, 150, 300, 500, 1000, 1500, 2000, 5000, 10000
                ],
                //dom: '<"pull-left"f><"pull-right"l>tip',
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
                        .each(function(transaction, i) {

                            if (last !== transaction) {

                                var color = '#d2d6de';

                                var td = $('<td>');
                                td.attr({
                                    'colspan': '11',
                                    'style': 'color: #333 !important'
                                }).append(transaction);

                                var tr = $('<tr>');
                                tr.attr({
                                    'class': 'group',
                                    'style': 'background-color:' + color + ' !important; font-weight: bold; cursor: pointer'
                                }).append(td);

                                $(rows).eq(i).before(tr);

                                last = transaction;
                            }
                        });
                },
                initComplete: function(settings, json) {

                }
            });

            $('#datatable_1').on('processing.dt', function(e, settings, processing) {
                    if (processing === true) {
                        //console.log('Tabla en proceso');
                    } else {
                        //console.log('Tabla completamente procesada.');

                        $('#div_load').css('display', 'none');
                        $('#content').css('display', 'block');
                        $('body > div.wrapper > footer').css('display', 'block');

                        if (total_amount_of_transactions > 0) {
                            var scroll_item = $('#div_datatable_1');
                            $('html, body').animate({
                                scrollTop: scroll_item.offset().top
                            }, 1000);
                        }
                    }
                })
                .dataTable();


        } else {
            $('#div_summary_of_totals').css({
                'display': 'none'
            });

            $('#div_detail_by_atm').css({
                'display': 'none'
            });

            $('#div_datatable_1').css({
                'display': 'none'
            });

            $('#div_load').css('display', 'none');
            $('#content').css('display', 'block');
        }

        $(".alert").delay(5000).slideUp(300);
        $('[data-toggle="popover"]').popover();

    });
</script>
@endsection