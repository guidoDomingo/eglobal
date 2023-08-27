<section class="content">

   
    <!-- Print Section -->
    <div id="printSection" class="printSection" style="visibility:hidden;"></div>
    <div class="row">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Filtros de búsqueda</h3>

                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div>
                </div>

                <!-- /.box-header -->
                <form action="{{ route('claro.transactions.search') }}" method="GET">
                    <div class="box-body" style="display: block;">
                        
                        <div class="row">

                            <div class="row">
                                <div class="col-md-4">
                                    <div style="margin: 20px" title="Filtrar Solo Red Claro">
                                        <input type="checkbox" id="owner_claro" name="owner_claro"></input> &nbsp; <b style="vertical-align: middle;"> Solo Red Claro</b> &nbsp;
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">                                                                
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            {!! Form::label('sucursales', 'Sucursales') !!}
                                            {!! Form::select('branch_id', $branches, $branch_id, ['id' => 'branch_id', 'class' => 'form-control select2']) !!}
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div style="border: 1px solid #d2d6de; border-radius: 5px; background-color: #ecf0f5; padding: 5px">
                                            <div class="row">
                                                <div class="col-md-8">
                                                    {!! Form::label('pdv', 'Puntos de venta') !!}
                                                    {!! Form::select('pos_id', $pos, $pos_id, ['id' => 'pos_id', 'class' => 'select2 form-control']) !!}
                                                </div> 
                                                                                              
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <br />

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {!! Form::label('estados', 'Estados') !!}
                                            {!! Form::select('status_id', $status, $status_set, ['class' => 'form-control select2']) !!}
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Medios de pago</label>
                                            {!! Form::select('payment_method_id', $payment_methods, $payment_methods_set, ['class' => 'form-control select2', 'id' => 'serviceId']) !!}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>Rango de Tiempo & Fecha:</label>
                                            <div class="input-group">
                                                <div class="input-group-addon">
                                                    <i class="fa fa-clock-o"></i>
                                                </div>
                                                <input name="reservationtime" type="text" id="reservationtime" class="form-control pull-right" value="{{ $reservationtime or '' }}" />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>ID de transacción:</label>
                                            <div class="input-group">
                                                <div class="input-group-addon">
                                                    ID
                                                </div>
                                                <input type="number" id="transaction_id" name="transaction_id" class="form-control" placeholder="Buscar por ID" />
                                            </div>
                                        </div>
                                    </div>                                    
                                </div>

                                <br>

                                <div class="row">
                                    <div class="col-md-2"></div>

                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-primary btn-block" name="search" value="search" id="buscar">
                                            <i class="fa fa-search"></i> &nbsp; Buscar
                                        </button>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-success btn-block" name="download" value="download">
                                            <i class="fa fa-file-excel-o"></i> &nbsp; Exportar
                                        </button>
                                    </div>

                                    <div class="col-md-2"></div>
                                </div>
                            </div>
                        </div>

                        <div style="border: 1px solid #d2d6de; border-radius: 5px; background-color: #ecf0f5; padding: 5px">
                            <div class="row">                                
                                <div class="col-md-3">
                                    <label>Servicio</label>
                                    {!! Form::select('service_request_id', $services_data, $service_request_id, ['class' => 'select2 form-control', 'id' => 'servicioRequestId']) !!}
                                </div>                                
                            </div>
                        </div>                                                
                    </div>
                </form>
            </div>
        </div>
    </div>

    @if (isset($transactions) and $transactions_total > 0)

    <div class="row">
        <div class="col-md-8">
            <div class="box box-default" style="border: 1px solid #d2d6de;">
                <div class="box-header with-border">
                    <h3 class="box-title">Resumen de totales:</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box" style="background-color: aliceblue !important; color: #444;">
                                <span class="info-box-icon"><i class="fa fa-list"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Cantidad de Transacciones</span>
                                    <span class="info-box-number" style="font-size: 30px" id="number_of_transactions">{{ $transactions_total }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box" style="background-color: aliceblue !important; color: #444;">
                                <span class="info-box-icon"><i class="fa fa-money"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Monto total de Transacciones</span>
                                    <span class="info-box-number" style="font-size: 30px" id="total_amount_of_transactions"><b>{{ $total_transactions }}</b></span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <!--Si es mayor a 19 que es el limite por página mostramos el páginador-->
            @if (count($transactions) > 19)
            <div class="box box-default" style="border: 1px solid #d2d6de;">
                <div class="box-header with-border">
                    <h3 class="box-title">Ir a la página:</h3>
                </div>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="dataTables_paginate paging_simple_numbers">

                                {!! $transactions->appends(
                                ['group_id' => $group_id, 'owner_id' => $owner_id, 'type' => $type_set,
                                'branch_id' => $branch_id, 'pos_id' => $pos_id, 'status_id' => $status_set, 'service_id' => $service_id,
                                'reservationtime' => $reservationtime, 'service_request_id' => $service_request_id
                                ])->render() !!}

                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>    

    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Resultados</h3>
                    <div class="box-tools">
                        <div class="input-group" style="width:150px;">
                            {!! Form::model(Request::only(['context']), ['route' => 'reports.transactions.search', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
                            {!! Form::text('context', null, ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Buscar', 'autocomplete' => 'off']) !!}
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body" style="overflow: scroll">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_1">
                                <thead>
                                    <tr>
                                        <th style="max-width:50px;">ID</th>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                        <th>Valor Transacción</th>
                                        @if (\Sentinel::getUser()->inRole('superuser'))
                                        <th>Monto Comisión</th>
                                        @endif
                                        <th style="max-width:100px;">Cód. Pago</th>
                                        <th>Identificador de transacción</th>
                                        <th>Factura nro</th>
                                        <th>Sede</th>
                                        <th>Ref 1</th>
                                        <th>Ref 2</th>
                                        <th>Codigo Cajero</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($transactions as $transaction)
                                    <?php
                                    if ($transaction->status == 'success') {
                                        if (is_null($transaction->reversion_id)) {
                                            $transaction->status = '<span class="label label-success">' . $transaction->status . '</span>';
                                        } else {
                                            $transaction->status = '<span class="label label-primary label_reversion">' . $transaction->status . '</span>';
                                        }
                                    } elseif ($transaction->status == 'canceled' || $transaction->status == 'iniciated') {
                                        $transaction->status = '<span class="label label-warning">' . $transaction->status . '</span>';
                                    } elseif ($transaction->status == 'inconsistency') {
                                        $transaction->status = '<span class="label label-danger">' . 'Inconsistencia' . '</span>';
                                    } else {
                                        $transaction->status = '<span class="label label-danger">' . $transaction->status . '</span>';
                                    }
                                    ?>


                                    <tr 
                                        data-id="{{ $transaction->id }}" 
                                        data-description="{{ $transaction->provider }} - {{ $transaction->servicio }}" 
                                        data-amount="{{ $transaction->amount }}" 
                                        data-ref1="{{ $transaction->referencia_numero_1 }}" 
                                        data-ref2="{{ $transaction->referencia_numero_2 }}" 
                                        data-estado="{{ $transaction->estado }}" 
                                        data-payid="{{ $transaction->cod_pago }}" 
                                        data-status="{{ $transaction->status_description }}" 
                                        data-transaction="{{ $transaction->atm_transaction_id }}"
                                        data-service_source_id="{{ $transaction->service_source_id }}" 
                                        data-service_id="{{ $transaction->service_id }}">
                                        
                                    
                                        <td align="left" class="{{ $transaction->id }}">
                                            ID: {{ $transaction->id }} <br>                                            
                                        </td>

                                        @if ($transaction->service_source_id == 0)
                                        <td>{{ $transaction->provider }} -
                                            {{ $transaction->servicio }}
                                        </td>
                                        @else
                                        <td>{{ $transaction->proveedor }} - {{ $transaction->tipo }}
                                        </td>
                                        @endif

                                        <td class="status" style="cursor:pointer">
                                            {!! $transaction->status !!}
                                        </td>
                                        <td>{{ $transaction->created_at }}</td>
                                        @if ($transaction->forma_pago == 'efectivo')
                                        <td align="right">{{ $transaction->amount }} <i title="Efectivo" class="fa fa-money"></i> </td>
                                        @elseif($transaction->forma_pago == 'canje')
                                        <td align="right">{{ $transaction->amount }} <i title="Canje" class="fa fa-tags"></i></td>
                                        @elseif($transaction->forma_pago == 'TC')
                                        <td align="right">{{ $transaction->amount }} <i title="TC" class="fa fa-credit-card"></i></td>
                                        @elseif($transaction->forma_pago == 'TD')
                                        <td align="right">{{ $transaction->amount }} <i title="TD" class="fa fa-credit-card"></i></td>
                                        @elseif($transaction->forma_pago == 'DC')
                                        <td align="right">{{ $transaction->amount }} <i title="DC" class="fa fa-credit-card"></i></td>
                                        @else
                                        <td align="right"> {{ $transaction->amount }} |
                                            {{ $transaction->forma_pago }}
                                        </td>
                                        @endif
                                        @if (\Sentinel::getUser()->inRole('superuser'))
                                        <td>{{ $transaction->commission_amount }}</td>
                                        @endif                                        
                                        <td align="right">{{ $transaction->cod_pago }}</td>                                        
                                        <td align="right">{{ $transaction->identificador_transaction_id }}
                                        </td>
                                        <td align="right">{{ $transaction->factura_numero }}</td>
                                        <td>{{ $transaction->sede }}</td>
                                        <td align="right">{{ $transaction->referencia_numero_1 }}</td>
                                        <td align="right">{{ $transaction->referencia_numero_2 }}</td>
                                        <td align="right">{{ $transaction->code }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="box-footer clearfix">
                    <div class="row">
                        <div class="col-sm-7">

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @elseif($search)
    <div class="box box-danger">
        <div class="box-header with-border">
            <h1 class="box-title">Sin resultados en la búsqueda.</h1>
        </div>
    </div>
    @endif
</section>

@section('js')
<!-- InputMask -->
<script src="/bower_components/admin-lte/plugins/input-mask/jquery.inputmask.js"></script>
<script src="/bower_components/admin-lte/plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
<script src="/bower_components/admin-lte/plugins/input-mask/jquery.inputmask.extensions.js"></script>
<!-- date-range-picker -->
<link href="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css" />
<script src="/bower_components/admin-lte/plugins/daterangepicker/moment.min.js"></script>
<script src="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker.js"></script>

<!-- bootstrap datepicker -->
<script src="/bower_components/admin-lte/plugins/datepicker/bootstrap-datepicker.js"></script>

<script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>

<!-- datatables -->
<link rel="stylesheet" href="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.css">
<script src="/bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.min.js"></script>


<!-- iCheck -->
<link rel="stylesheet" href="/bower_components/admin-lte/plugins/iCheck/square/grey.css">
<script src="/bower_components/admin-lte/plugins/iCheck/icheck.min.js"></script>


<!-- json-formatter -->
<link rel="stylesheet" href="/js/json-formatter/jquery.json-viewer.css">
<script src="/js/json-formatter/jquery.json-viewer.js"></script>

<script>

    var transaction_id_aux = null;
    
    //Cascading dropdown list de redes / sucursales
    $('.select2').select2();

    $('.mostrar').hide();

    $('.status').on('click', function(e) {
        //Setea de cero elementos html del modal
        e.preventDefault();
        var row = $(this).parents('tr');
        var status_description = row.data('status');
        var estado = row.data('estado');
        var id = row.data('id');
        var transaction_id = row.data('transaction');
        $(".idTransaccion").html(transaction_id);

        if (estado == 'devolucion') {
            status_description += '</br> <img style="max-width:550px;" src="/comprobantes_devoluciones/' + id +
                '.jpg"/>';
        }

        $.get('{{ url('reports') }}/info/reversion_data/' + id,
            function(data) {
                console.log(data);
                if (data['reversion'] == true) {
                    console.log('abriendo reversion');
                    $('.reversion').show();
                    $('#id_transaccion').html(id);
                    //$('#reversiones').show();
                } else {
                    $('.reversion').hide();
                }

                if (data['id_reversion'] == true) {
                    console.log('abriendo contenido reversion');
                    $('#reversion_description').show();
                    $('#fecha_reversion').html(data['fecha']);
                    $('#rever_user').html(data['user']);
                } else {
                    $('#reversion_description').hide();
                }
        });

        $("#status_description").html(status_description);
        $("#status_description").show();
        $("#detalles").hide();
        $("#payment_details").hide();
        $('#devoluciones').hide();
        $('#reprocesos').hide();

        //botones
        $('.devolucion').hide();
        $('.reprocesar').hide();
        $('#process_devolucion').hide();
        $('#process_inconsistencia').hide();
        $('.inconsistencia').hide();
        $('#process_reversion').hide();
        $('.reversion').show();
        $('#reversion_description').hide();
        $('#run_reprocesar').hide();

        $('#li_tab_1 > a').trigger('click');
        $("#myModal").modal();

    });
                                                           

    //Datemask dd/mm/yyyy
    $("#datemask").inputmask("dd/mm/yyyy", {
        "placeholder": "dd/mm/yyyy"
    });
    //Datemask2 mm/dd/yyyy
    $("#datemask2").inputmask("mm/dd/yyyy", {
        "placeholder": "mm/dd/yyyy"
    });
    //reservation date preset
    $('#reservationtime').val()

    if ($('#reservationtime').val() == '' || $('#reservationtime').val() == 0) {
        var date = new Date();
        var init = new Date(date.getFullYear(), date.getMonth(), date.getDate());
        var end = new Date(date.getFullYear(), date.getMonth(), date.getDate());

        var initWithSlashes = (init.getDate()) + '/' + (init.getMonth() + 1) + '/' + init.getFullYear() + ' 00:00:00';
        var endDayWithSlashes = (end.getDate()) + '/' + (end.getMonth() + 1) + '/' + end.getFullYear() + ' 23:59:59';

        $('#reservationtime').val(initWithSlashes + ' - ' + endDayWithSlashes);
    }
    //Date range picker
    $('#reservation').daterangepicker();   

    $('#reservationtime').daterangepicker({
        'timePicker': true,
        'timePicker24Hour': true,
        'timePickerIncrement': 1,
        'format': 'DD/MM/YYYY HH:mm:ss',
        'startDate': moment().startOf('month'),
        'endDate': moment().endOf('month'),
        'opens': 'left',
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

    $('#reservationtime').attr({
        'onkeydown': 'return false'
    });

    var fechaIncio = $('#reservationtime').val().substr(0, 10);
    var fechaFin = $('#reservationtime').val().substr(22, 10);
    var fecha1 = moment(fechaIncio, "MM-DD-YYYY");
    var fecha2 = moment(fechaFin, "MM-DD-YYYY");
    const diferencia = fecha2.diff(fecha1, 'days');
    var rsultadoDif = Math.round(diferencia / (24));                          


    $('#branch_id').on('change', function(e){
        var branch_id = e.target.value;
        console.log(branch_id);
        $.get('{{ url('reports') }}/ddl/pdv/' + branch_id, function(data) {
            $('#pos_id').empty();            
            $.each(data, function(i,item){
                $('#pos_id').append($('<option>', {
                    value: i,
                    text : item
                }));
            });
        });
    });

    var table = $('#datatable_1').DataTable({
        orderCellsTop: true,
        fixedHeader: true,
        pageLength: 20,
        lengthMenu: [
            1, 2, 5, 10, 20, 30, 50, 70, 100, 150, 300, 500, 1000, 1500, 2000, 5000, 10000
        ],
        dom: '',
        language: {
            "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
        },
        scroller: true,
        processing: true,
        order: [
            [0, 'desc']
        ],
        displayLength: 20
    });

    $('input[type="checkbox"]').iCheck({
        checkboxClass: 'icheckbox_square-grey',
        radioClass: 'iradio_square-grey'
    });

    var owner_claro = "{{ $owner_claro }}";

    if (owner_claro == 'on') {
        $('#owner_claro').iCheck('check');
    }

</script>
@endsection
@section('aditional_css')
<link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
<style type="text/css">
    @media print {
        body * {
            visibility: hidden;

        }

        #printSection,
        #printSection * {
            visibility: visible;
        }



        #printSection {
            font-size: 11px;
            width: 100%;
            height: 100%;
            margin: 0;
            padding: 0;
            left: 0;
            top: 0;
        }
</style>
@endsection