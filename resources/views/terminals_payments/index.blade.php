@extends('layout')

@section('title')
Pagos por Terminal - Reporte
@endsection

@section('content')

<?php
//Variable que se usa en todo el documento 

$records = $data['lists']['records'];
$json = $data['lists']['json'];

//Combos
$business_groups = $data['lists']['business_groups'];

//Valor de campos
$created_at = $data['inputs']['created_at'];
$transaction_id = $data['inputs']['transaction_id'];
$receipt_id = $data['inputs']['receipt_id'];
$amount = $data['inputs']['amount'];
$group_id = $data['inputs']['group_id'];
$atm_id = $data['inputs']['atm_id'];

$search_type = $data['inputs']['search_type'];

$total_number_of_transactions = $data['totals']['total_number_of_transactions'];
$total_amount_of_transactions = $data['totals']['total_amount_of_transactions'];

$cantidad_de_recibos = 0;
$monto_total_de_recibos = 0;
$receipt_ids = '[]';

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
                            <i class="fa fa-spin fa-refresh fa-2x" style="vertical-align: sub;"></i> &nbsp;
                            Cargando...
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4"></div>
    </div>

    {!! Form::open(['route' => 'terminals_payments', 'method' => 'POST', 'role' => 'form', 'id' => 'form_search']) !!}

    <div class="box box-default" style="border-radius: 5px;" id="content" style="display: none">
        <div class="box-header with-border">
            <h3 class="box-title" style="font-size: 25px;">Pagos por Terminal - Reporte
            </h3>
            <div class="box-tools pull-right">

                <div class="btn-toolbar">
                    <div class="btn-group" style="margin-top: 3px !important;">
                        <select name="search_type" id="search_type" class="select2" style="width: 400px !important;">
                            <option value="1">Tipo de búsqueda: Recibos Pendientes de Migrar</option>
                            <option value="2">Tipo de búsqueda: Detalle de Ventas</option>
                            <option value="3">Tipo de búsqueda: Detalle completo de Pagos</option>
                            <!--<option value="3">Tipo de búsqueda: Gráfico de pagos</option>-->
                        </select>
                    </div>
                    <div class="input-group">
                        <button type="button" class="btn btn-info" title="Buscar según los filtros en los registros." style="margin-right: 5px" id="search" name="search">
                            <span class="fa fa-search"></span> Buscar
                        </button>
                    </div>
                </div>

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

                    <div class="row">

                        <div class="col-md-4">
                            <label for="created_at">Buscar por Fecha:</label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-clock-o"></i>
                                </div>
                                <input name="created_at" type="text" id="created_at" class="form-control pull-right" onkeydown="return false">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="group_id">Buscar por Grupo:</label>
                            <select name="group_id" id="group_id" class="select2" style="width: 100%"></select>
                        </div>

                        <div class="col-md-4">
                            <label for="atm_id">Buscar por Terminal:</label>
                            <select name="atm_id" id="atm_id" class="select2" style="width: 100%"></select>
                        </div>

                    </div>

                    <br />

                    <div class="row">

                        <div class="col-md-4">
                            <label for="transaction_id">Buscar por Transacción ID:</label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <b>ID</b>
                                </div>
                                <input type="number" name="transaction_id" id="transaction_id" class="form-control pull-right" placeholder="Transacción ID">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="receipt_id">Recibo ID:</label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <b>ID</b>
                                </div>
                                <input type="number" name="receipt_id" id="receipt_id" class="form-control pull-right" placeholder="Recibo ID">
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="amount">Buscar por Monto:</label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-money"></i>
                                </div>
                                <input type="number" name="amount" id="amount" class="form-control pull-right" placeholder="Monto de transacción">
                            </div>
                        </div>
                    </div>

                    <input name="json" id="json" type="hidden">
                </div>
            </div>

        </div>
    </div>

    {!! Form::close() !!}


    @if (count($records) > 0)

    @if ($search_type == '1')

    <?php
    $cantidad_de_recibos = count($records);
    $monto_total_de_recibos = number_format(array_sum(array_column($records, 'monto')), 0, '.', ',');
    $receipt_ids = json_encode(array_column($records, 'receipt_id')); // Para relanzar todos los recibos

    if ($receipt_ids == null) {
        $receipt_ids = 0;
    }
    ?>

    <div class="box box-default" style="border: 1px solid #d2d6de;">
        <div class="box-header with-border">
            <h3 class="box-title">Resumen de Recibos Pendientes:</h3>
        </div>
        <div class="box-body">
            <div class="row">
                <div class="col-md-4" style="cursor: pointer" onclick="relaunch_all()" id="div_relaunch_all">
                    <div class="info-box" style="background-color: aliceblue !important; color: #444;">
                        <span class="info-box-icon" style="background-color: #285f6c; color: white;" id="relaunch_all_icon"><i class="fa fa-refresh"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text" style="margin-top: 28px; font-weight: bold" id="relaunch_all_title">Relanzar todos los recibos</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box" style="background-color: aliceblue !important; color: #444;">
                        <span class="info-box-icon"><i class="fa fa-list"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Cantidad de Recibos</span>
                            <span class="info-box-number" style="font-size: 30px" id="">{{$cantidad_de_recibos}}</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box" style="background-color: aliceblue !important; color: #444;">
                        <span class="info-box-icon"><i class="fa fa-money"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Monto total de Recibos</span>
                            <span class="info-box-number" style="font-size: 30px" id="">{{$monto_total_de_recibos}}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @else
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
                            <span class="info-box-number" style="font-size: 30px" id="total_number_of_transactions"></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="info-box" style="background-color: aliceblue !important; color: #444;">
                        <span class="info-box-icon"><i class="fa fa-money"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Monto total de Transacciones</span>
                            <span class="info-box-number" style="font-size: 30px" id="total_amount_of_transactions"></span>
                        </div>
                    </div>
                </div>
                <div class="col-md-2"></div>
            </div>
        </div>
    </div>
    @endif


    @if ($search_type == '1')

    <div class="box box-default" style="border: 1px solid #d2d6de;">
        <div class="box-header with-border">
            <h3 class="box-title">Listado:</h3>
        </div>
        <div class="box-body">

            <table class="table table-bordered dataTable" role="grid" id="datatable_recibos">
                <thead>
                    <tr style="background: #285f6c; color: white;">
                        <th style="text-align: center;">Relanzar</th>
                        <th>Transacción-ID</th>
                        <th style="max-width: 150px">Recibo-ID</th>
                        <th>Tipo</th>
                        <th>Monto</th>
                        <th>Fecha-Hora</th>
                        <th>Tiempo transcurrido</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach($records as $recibo)

                    <?php
                        $transaction_id_aux = $recibo['transaction_id'];
                        $receipt_id = $recibo['receipt_id'];
                        $mt_recibo_id = $recibo['mt_recibo_id'];
                        $mt_recibo_tipo = $recibo['mt_recibo_tipo'];
                        $mt_recibo_nro = $recibo['mt_recibo_nro'];
                        $mt_recibo_monto_view = $recibo['mt_recibo_monto_view'];
                        $mt_recibo_created_at = $recibo['mt_recibo_created_at'];
                        $in_favor_view = $recibo['in_favor_view'];
                        $request = $recibo['request'];
                        $response = $recibo['response'];
                        $tiempo_transcurrido = $recibo['tiempo_transcurrido'];
                        $tiempo_transcurrido_aux = $recibo['tiempo_transcurrido_aux'];

                        $tiempo_transcurrido_color = '#333';

                        if ($tiempo_transcurrido_aux >= 180) {

                            $tiempo_transcurrido_color = '#dd4b39';

                        } else if ($tiempo_transcurrido_aux >= 90 and $tiempo_transcurrido_aux <= 179) {

                            $tiempo_transcurrido_color = '#f39c12';

                        }

                    ?>

                    <tr id="row_id_{{$receipt_id}}" style="background: whitesmoke;">
                        
                        <td>
                            <div style="width: 100%; text-align: center" id="div_id_{{$receipt_id}}">

                                <div class="btn-group" role="group">
                                    <button class="btn btn-default btn-sm btn-resend" type="button" style="background-color: #285f6c; color:white; margin-right: 2px; width: 35px;" title="Reenviar cadena" onclick="relaunch_receipt({{$receipt_id}})" id="btn_id_{{$receipt_id}}">
                                        <span class="fa fa-refresh"></span>
                                    </button>
                                </div>

                            </div>
                        </td>
                        <td> {{ $transaction_id_aux }} </td>
                        <td> {{ $mt_recibo_id }} </td>
                        <td> {{ $mt_recibo_tipo }} </td>
                        <td> {{ $mt_recibo_monto_view }} </td>
                        <td> {{ $mt_recibo_created_at }} </td>
                        <td style="color: {{$tiempo_transcurrido_color}}; font-weight: bold"> {{ $tiempo_transcurrido }} </td>

                    </tr>

                    <tr style="border-bottom: 1px solid gray">
                        <td style="max-width: 150px" id="td_message_id_{{$receipt_id}}">
                            <b>Mensaje:</b> <br/>
                            Cadena pendiente de enviar. 
                        </td>
                        <td style="max-width: 150px" colspan="3">
                            <b>Cadena envíada:</b> <br/>
                            {{ $request }} 
                        </td>
                        <td style="display: none"></td>
                        <td style="display: none"></td>
                        <td style="max-width: 150px" colspan="3"> 
                            <b>Respuesta recibida:</b> <br/>
                            {{ $response }} 
                        </td>
                        <td style="display: none"></td>
                        <td style="display: none"></td>
                    </tr>

                    @endforeach

                </tbody>
            </table>

        </div>
    </div>

    @elseif ($search_type == '2')

    <div class="box box-default" style="border: 1px solid #d2d6de;">
        <div class="box-header with-border">
            <h3 class="box-title">Listado:</h3>
        </div>
        <div class="box-body">

            <table class="table table-bordered table-hover dataTable searchable_datatables" role="grid">
                <thead>
                    <tr>
                        <th>Grupos</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach($records as $record_item)

                    <?php
                    $group_id_aux = $record_item['group_id'];
                    $group_description = $record_item['group_description'];
                    $balance_rules = $record_item['balance_rules'];
                    $transactions = $record_item['transactions'];
                    $transactions_count = count($transactions);
                    ?>

                    <tr>
                        <td>

                            <div class="box box-default" style="border: 1px solid #d2d6de;">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Grupo: {{ $group_id_aux }} - {{ $group_description }} </h3>
                                    <div class="box-tools pull-right">
                                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                                    </div>
                                </div>
                                <div class="box-body">

                                    <div class="box box-default" style="border: 1px solid #d2d6de;">
                                        <div class="box-header with-border">
                                            <h3 class="box-title">Transacciones</h3>
                                            <div class="box-tools pull-right">
                                                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                                            </div>
                                        </div>
                                        <div class="box-body">

                                            <table class="table table-bordered table-hover dataTable searchable_datatables" role="grid">
                                                <thead>
                                                    <tr>
                                                        <th>Transacciones ({{ $transactions_count }} transacciones)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                    @foreach($transactions as $transactions_item)

                                                    <?php
                                                    $transaction_id_ = $transactions_item['transaction_id'];
                                                    $created_at_view = $transactions_item['created_at_view'];
                                                    $amount_view = $transactions_item['amount_view'];
                                                    $request_data = $transactions_item['request_data'];

                                                    $transaction_atm_id = $transactions_item['transaction_atm_id'];
                                                    $transaction_atm_description = $transactions_item['transaction_atm_description'];
                                                    $atms = $transactions_item['atms'];
                                                    $atms_count = count($transactions_item['atms']);

                                                    // Calculos de agrupamiento que no se encuentran en el select: 
                                                    $transaction_summary = $transactions_item['transaction_summary'];

                                                    $count_atms_total = $transaction_summary['counts']['atms']; // Terminales en total
                                                    $count_receipts_total = $transaction_summary['counts']['receipts']; // Recibos generados en total
                                                    $count_quotes_total = $transaction_summary['counts']['quotes']; // Cuotas afectadas en total
                                                    $count_sales_total = $transaction_summary['counts']['sales']; // Ventas afectadas en total

                                                    $amount_receipts_total = $transaction_summary['amounts']['receipts'];
                                                    $amount_quotes_total = $transaction_summary['amounts']['quotes'];
                                                    $amount_sales_total = $transaction_summary['amounts']['sales'];

                                                    ?>

                                                    <tr>
                                                        <td>

                                                            <div class="box box-default" style="border: 1px solid #d2d6de;">
                                                                <div class="box-header with-border">
                                                                    <h3 class="box-title"><b>Transacción-ID:</b> {{ $transaction_id_ }}</h3>
                                                                    <div class="box-tools pull-right">
                                                                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                                                                    </div>
                                                                </div>
                                                                <div class="box-body">
                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <div class="callout callout-default" style="background: white; border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px" title="Movimiento">
                                                                                <b>Transacción-ID:</b> {{ $transaction_id_ }} <br />
                                                                                <b>Transacción-Fecha-Hora:</b> {{ $created_at_view }} <br />
                                                                                <b>Transacción-Monto:</b> {{ $amount_view }} <br />
                                                                                <b>Transacción-Terminal-Pago:</b> #{{ $transaction_atm_id }} - {{ $transaction_atm_description }} <br /><br />
                                                                                <b>Transacción-JSON-Consulta:</b> <br />

                                                                                <div class="to_beautify_json">{{ $request_data }}</div>

                                                                                <br /><br />
                                                                            </div>
                                                                        </div>

                                                                        <div class="col-md-6">
                                                                            <div class="callout callout-default" style="background: white; border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px" title="Factores afectados">
                                                                                <h4>Factores Afectados: </h4>
                                                                                <b>Terminales:</b> {{ $count_atms_total }} en total.<br />
                                                                                <b>Recibos:</b> {{ $count_receipts_total }} en total.<br />
                                                                                <b>Transacciones-Ventas:</b> {{ $count_sales_total }} en total.<br />
                                                                            </div>

                                                                            <div class="callout callout-default" style="background: white; border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px" title="Monto afectados">
                                                                                <h4>Monto afectados: </h4>
                                                                                <b>Recibos:</b> {{ $amount_receipts_total }} en total.<br />
                                                                                <b>Transacciones-Ventas:</b> {{ $amount_sales_total }} en total.<br />
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row">
                                                                        <div class="col-md-12">

                                                                            <div class="box box-default" style="border: 1px solid #d2d6de;">
                                                                                <div class="box-header with-border">
                                                                                    <h3 class="box-title">Total depósitado</h3>
                                                                                    <div class="box-tools pull-right">
                                                                                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="box-body">

                                                                                    <table class="table table-bordered table-hover dataTable searchable_datatables" role="grid">
                                                                                        <thead>
                                                                                            <tr>
                                                                                                <th>Recibo-ID</th>
                                                                                                <th>Monto-Antes</th>
                                                                                                <th>Monto-Afectado</th>
                                                                                                <th>Monto-Después</th>
                                                                                                <th>Fecha-Hora</th>
                                                                                            </tr>
                                                                                        </thead>
                                                                                        <tbody>

                                                                                            @foreach($atms as $atm)

                                                                                            <?php
                                                                                            $total_deposited = $atm['total_deposited'];
                                                                                            ?>

                                                                                            @if($total_deposited !== null)

                                                                                            @foreach($total_deposited as $total_deposited_item)

                                                                                            <tr>
                                                                                                <td> {{ $total_deposited_item['receipt_id'] }} &nbsp; {{ $total_deposited_item['in_favor_view'] }}</td>
                                                                                                <td> {{ $total_deposited_item['total_deposited_before'] }} </td>
                                                                                                <td> {{ $total_deposited_item['total_deposited'] }} </td>
                                                                                                <td> {{ $total_deposited_item['total_deposited_after'] }} </td>
                                                                                                <td> {{ $total_deposited_item['created_at'] }} </td>
                                                                                            </tr>

                                                                                            @endforeach

                                                                                            @endif

                                                                                            @endforeach

                                                                                        </tbody>
                                                                                    </table>

                                                                                </div>
                                                                            </div>

                                                                        </div>
                                                                    </div>

                                                                    <div class="row">
                                                                        <div class="col-md-12">

                                                                            <div class="box box-default" style="border: 1px solid #d2d6de;">
                                                                                <div class="box-header with-border">
                                                                                    <h3 class="box-title">Ventas cobradas</h3>
                                                                                    <div class="box-tools pull-right">
                                                                                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="box-body">

                                                                                    <table class="table table-bordered table-hover dataTable searchable_datatables" role="grid">
                                                                                        <thead>
                                                                                            <tr>
                                                                                                <th>Ventas cobradas</th>
                                                                                            </tr>
                                                                                        </thead>
                                                                                        <tbody>

                                                                                            @foreach($atms as $atm)

                                                                                            <?php
                                                                                            $atm_id_aux = $atm['atm_id'];
                                                                                            $atm_description = $atm['atm_description'];
                                                                                            $atm_recibos = $atm['recibos'];
                                                                                            $atm_recibos_count = count($atm_recibos);
                                                                                            $historial_bloqueos = $atm['historial_bloqueos'];
                                                                                            $payments_x_atm = $atm['payments_x_atm'];
                                                                                            ?>

                                                                                            @foreach($atm_recibos as $recibo)

                                                                                            <?php
                                                                                            $cuota_detalle = $recibo['cuota_detalle'];
                                                                                            $cuota_detalle_count = count($cuota_detalle);
                                                                                            $transacciones_detalle = $recibo['transacciones_detalle'];
                                                                                            $transacciones_detalle_count = count($transacciones_detalle);
                                                                                            ?>

                                                                                            @if($transacciones_detalle !== null)

                                                                                            @foreach($transacciones_detalle as $transaccion)

                                                                                            <?php
                                                                                            $detalles = $transaccion['detalles'];
                                                                                            $detalles_count = count($detalles);
                                                                                            $mt_sales_count = 1;
                                                                                            ?>

                                                                                            @if($detalles !== null)

                                                                                            @foreach($detalles as $detalles_item)

                                                                                            <tr>
                                                                                                <td>
                                                                                                    <div class="callout callout-default" style="background: white; border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px" title="Venta">
                                                                                                        <h4>{{ $mt_sales_count }}) Venta-ID: {{ $detalles_item['mt_sales_id'] }} </h4> <br />
                                                                                                        <b>Venta-Fecha-Hora:</b> {{ $detalles_item['mt_sales_fecha'] }} <br />
                                                                                                        <b>Venta-Estado:</b> {{ $detalles_item['mt_sales_estado'] }} <br />
                                                                                                        <b>Venta-Monto-Importe-Inicial:</b> {{ $detalles_item['mt_movements_amount'] }} <br />
                                                                                                        <b>Venta-Monto-Saldo-Actual:</b> {{ $detalles_item['mt_sales_monto_por_cobrar'] }} <br /> <br />

                                                                                                        <b>Recibos que afectaron esta venta:</b> <br />

                                                                                                        @if ($detalles_item['mt_sales_detail'] !== null)
                                                                                                        <table class="table table-bordered table-hover dataTable sub_datatables" role="grid">
                                                                                                            <thead>
                                                                                                                <tr>
                                                                                                                    <th>Recibo-ID</th>
                                                                                                                    <th>Monto-Antes</th>
                                                                                                                    <th>Monto-Afectado</th>
                                                                                                                    <th>Monto-Después</th>
                                                                                                                    <th>Fecha-Creación</th>
                                                                                                                </tr>
                                                                                                            </thead>
                                                                                                            <tbody>

                                                                                                                @foreach($detalles_item['mt_sales_detail'] as $mt_sales_detail)
                                                                                                                <tr title="{{ $mt_sales_detail['description'] }}">
                                                                                                                    <td> {{ $mt_sales_detail['receipt_id'] }} &nbsp; {{ $mt_sales_detail['in_favor_view'] }}</td>
                                                                                                                    <td> {{ $mt_sales_detail['sales_amount_before'] }} </td>
                                                                                                                    <td> {{ $mt_sales_detail['sales_amount_affected_view'] }} </td>
                                                                                                                    <td> {{ $mt_sales_detail['sales_amount_after'] }} </td>
                                                                                                                    <td> {{ $mt_sales_detail['created_at'] }} </td>
                                                                                                                </tr>
                                                                                                                @endforeach

                                                                                                            </tbody>
                                                                                                        </table>
                                                                                                        @endif

                                                                                                    </div>
                                                                                                </td>
                                                                                            </tr>

                                                                                            <?php
                                                                                            $mt_sales_count += 1;
                                                                                            ?>
                                                                                            @endforeach

                                                                                            @endif

                                                                                            @endforeach

                                                                                            @endif

                                                                                            @endforeach

                                                                                            @endforeach

                                                                                        </tbody>
                                                                                    </table>
                                                                                </div>
                                                                            </div>

                                                                        </div>
                                                                    </div>

                                                                </div>
                                                            </div>

                                                        </td>
                                                    </tr>
                                                    @endforeach

                                                </tbody>
                                            </table>

                                        </div>
                                    </div>

                                </div>
                            </div>

                        </td>
                    </tr>

                    @endforeach

                </tbody>
            </table>
        </div>
    </div>

    @elseif ($search_type == '3')
    <div class="box box-default" style="border: 1px solid #d2d6de;">
        <div class="box-header with-border">
            <h3 class="box-title">Listado:</h3>
        </div>
        <div class="box-body">

            <table class="table table-bordered table-hover dataTable searchable_datatables" role="grid">
                <thead>
                    <tr>
                        <th>Grupos</th>
                    </tr>
                </thead>
                <tbody>

                    @foreach($records as $record_item)

                    <?php
                    $group_id_aux = $record_item['group_id'];
                    $group_description = $record_item['group_description'];
                    $balance_rules = $record_item['balance_rules'];
                    $transactions = $record_item['transactions'];
                    $transactions_count = count($transactions);
                    ?>

                    <tr>
                        <td>

                            <div class="box box-default" style="border: 1px solid #d2d6de;">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Grupo: {{ $group_id_aux }} - {{ $group_description }} </h3>
                                    <div class="box-tools pull-right">
                                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                                    </div>
                                </div>
                                <div class="box-body">

                                    <div class="box box-default collapsed-box" style="border: 1px solid #d2d6de;">
                                        <div class="box-header with-border">
                                            <h3 class="box-title">Reglas de Grupo</h3>
                                            <div class="box-tools pull-right">
                                                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
                                            </div>
                                        </div>
                                        <div class="box-body">

                                            <table class="table table-bordered table-hover dataTable searchable_datatables" role="grid">
                                                <thead>
                                                    <tr>
                                                        <th>Balance-Tipo-Control</th>
                                                        <th>Balance-Día</th>
                                                        <th>Balance-Días-Previos</th>
                                                        <th>Balance-Días-Saldo-Mínimo</th>
                                                        <th>Balance-Fecha-Creación</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($balance_rules as $balance_rules_item)
                                                    <tr>
                                                        <td>{{ $balance_rules_item['tipo_control'] }}</td>
                                                        <td>{{ $balance_rules_item['dia'] }}</td>
                                                        <td>{{ $balance_rules_item['dias_previos'] }}</td>
                                                        <td>{{ $balance_rules_item['saldo_minimo'] }}</td>
                                                        <td>{{ $balance_rules_item['created_at'] }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>

                                        </div>
                                    </div>

                                    <div class="box box-default" style="border: 1px solid #d2d6de;">
                                        <div class="box-header with-border">
                                            <h3 class="box-title">Transacciones</h3>
                                            <div class="box-tools pull-right">
                                                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                                            </div>
                                        </div>
                                        <div class="box-body">

                                            <table class="table table-bordered table-hover dataTable searchable_datatables" role="grid">
                                                <thead>
                                                    <tr>
                                                        <th>Transacciones ({{ $transactions_count }} transacciones)</th>
                                                    </tr>
                                                </thead>
                                                <tbody>

                                                    @foreach($transactions as $transactions_item)

                                                    <?php
                                                    $transaction_id_ = $transactions_item['transaction_id'];
                                                    $created_at_view = $transactions_item['created_at_view'];
                                                    $amount_view = $transactions_item['amount_view'];
                                                    $request_data = $transactions_item['request_data'];

                                                    $transaction_atm_id = $transactions_item['transaction_atm_id'];
                                                    $transaction_atm_description = $transactions_item['transaction_atm_description'];
                                                    $atms = $transactions_item['atms'];
                                                    $atms_count = count($transactions_item['atms']);

                                                    // Calculos de agrupamiento que no se encuentran en el select: 
                                                    $transaction_summary = $transactions_item['transaction_summary'];

                                                    $count_atms_total = $transaction_summary['counts']['atms']; // Terminales en total
                                                    $count_receipts_total = $transaction_summary['counts']['receipts']; // Recibos generados en total
                                                    $count_quotes_total = $transaction_summary['counts']['quotes']; // Cuotas afectadas en total
                                                    $count_sales_total = $transaction_summary['counts']['sales']; // Ventas afectadas en total

                                                    $amount_receipts_total = $transaction_summary['amounts']['receipts'];
                                                    $amount_quotes_total = $transaction_summary['amounts']['quotes'];
                                                    $amount_sales_total = $transaction_summary['amounts']['sales'];

                                                    ?>

                                                    <tr>
                                                        <td>

                                                            <div class="box box-default collapsed-box" style="border: 1px solid #d2d6de;">
                                                                <div class="box-header with-border">
                                                                    <h3 class="box-title"><b>Transacción-ID:</b> {{ $transaction_id_ }}</h3>
                                                                    <div class="box-tools pull-right">
                                                                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
                                                                    </div>
                                                                </div>
                                                                <div class="box-body">
                                                                    <div class="row">
                                                                        <div class="col-md-6">
                                                                            <div class="callout callout-default" style="background: white; border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px" title="Movimiento">
                                                                                <b>Transacción-ID:</b> {{ $transaction_id_ }} <br />
                                                                                <b>Transacción-Fecha-Hora:</b> {{ $created_at_view }} <br />
                                                                                <b>Transacción-Monto:</b> {{ $amount_view }} <br />
                                                                                <b>Transacción-Terminal-Pago:</b> #{{ $transaction_atm_id }} - {{ $transaction_atm_description }} <br /><br />
                                                                                <b>Transacción-JSON-Consulta:</b> <br />

                                                                                <div class="to_beautify_json">{{ $request_data }}</div>

                                                                                <br /><br />
                                                                            </div>
                                                                        </div>

                                                                        <div class="col-md-6">

                                                                            <div class="callout callout-default" style="background: white; border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px" title="Factores afectados">
                                                                                <h4>Factores Afectados: </h4>
                                                                                <b>Terminales:</b> {{ $count_atms_total }} en total.<br />
                                                                                <b>Recibos:</b> {{ $count_receipts_total }} en total.<br />
                                                                                <b>Transacciones-Ventas:</b> {{ $count_sales_total }} en total.<br />
                                                                            </div>

                                                                            <div class="callout callout-default" style="background: white; border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px" title="Monto afectados">
                                                                                <h4>Monto afectados: </h4>
                                                                                <b>Recibos:</b> {{ $amount_receipts_total }} en total.<br />
                                                                                <b>Transacciones-Ventas:</b> {{ $amount_sales_total }} en total.<br />
                                                                            </div>
                                                                        </div>
                                                                    </div>

                                                                    <div class="row">
                                                                        <div class="col-md-12">

                                                                            <div class="box box-default" style="border: 1px solid #d2d6de;">
                                                                                <div class="box-header with-border">
                                                                                    <h3 class="box-title">Total depósitado</h3>
                                                                                    <div class="box-tools pull-right">
                                                                                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                                                                                    </div>
                                                                                </div>
                                                                                <div class="box-body">

                                                                                    <table class="table table-bordered table-hover dataTable searchable_datatables" role="grid">
                                                                                        <thead>
                                                                                            <tr>
                                                                                                <th>Recibo-ID</th>
                                                                                                <th>Monto-Antes</th>
                                                                                                <th>Monto-Afectado</th>
                                                                                                <th>Monto-Después</th>
                                                                                                <th>Fecha-Hora</th>
                                                                                            </tr>
                                                                                        </thead>
                                                                                        <tbody>

                                                                                            @foreach($atms as $atm)

                                                                                            <?php
                                                                                            $total_deposited = $atm['total_deposited'];
                                                                                            ?>

                                                                                            @if($total_deposited !== null)

                                                                                            @foreach($total_deposited as $total_deposited_item)

                                                                                            <tr>
                                                                                                <td> {{ $total_deposited_item['receipt_id'] }} &nbsp; {{ $total_deposited_item['in_favor_view'] }}</td>
                                                                                                <td> {{ $total_deposited_item['total_deposited_before'] }} </td>
                                                                                                <td> {{ $total_deposited_item['total_deposited'] }} </td>
                                                                                                <td> {{ $total_deposited_item['total_deposited_after'] }} </td>
                                                                                                <td> {{ $total_deposited_item['created_at'] }} </td>
                                                                                            </tr>

                                                                                            @endforeach

                                                                                            @endif

                                                                                            @endforeach

                                                                                        </tbody>
                                                                                    </table>

                                                                                </div>
                                                                            </div>

                                                                        </div>
                                                                    </div>

                                                                    <div class="row">
                                                                        <div class="col-md-12">

                                                                            <table class="table table-bordered table-hover dataTable searchable_datatables" role="grid">
                                                                                <thead>
                                                                                    <tr>
                                                                                        <th>Terminales ({{ $atms_count }} afectado/s)</th>
                                                                                    </tr>
                                                                                </thead>
                                                                                <tbody>

                                                                                    @foreach($atms as $atm)

                                                                                    <tr>
                                                                                        <td>

                                                                                            <?php
                                                                                            $atm_id_aux = $atm['atm_id'];
                                                                                            $atm_description = $atm['atm_description'];
                                                                                            $atm_recibos = $atm['recibos'];
                                                                                            $atm_recibos_count = count($atm_recibos);
                                                                                            $historial_bloqueos = $atm['historial_bloqueos'];
                                                                                            $payments_x_atm = $atm['payments_x_atm'];
                                                                                            ?>

                                                                                            <div class="box box-default collapsed-box" style="border: 1px solid #d2d6de;">
                                                                                                <div class="box-header with-border">
                                                                                                    <h3 class="box-title"><b>Terminal:</b> {{ $atm_description }} </h3>
                                                                                                    <div class="box-tools pull-right">
                                                                                                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
                                                                                                    </div>
                                                                                                </div>
                                                                                                <div class="box-body">
                                                                                                    <div class="box box-default" style="border: 1px solid #d2d6de;">
                                                                                                        <div class="box-header with-border">
                                                                                                            <h3 class="box-title">Recibos ({{ $atm_recibos_count }} recibo/s generado/s)</h3>
                                                                                                            <div class="box-tools pull-right">
                                                                                                                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                        <div class="box-body">

                                                                                                            <table class="table table-bordered table-hover dataTable searchable_datatables" role="grid">
                                                                                                                <thead>
                                                                                                                    <tr>
                                                                                                                        <th>Recibos ({{ $atm_recibos_count }} recibo/s generado/s)</th>
                                                                                                                    </tr>
                                                                                                                </thead>
                                                                                                                <tbody>

                                                                                                                    @foreach($atm_recibos as $recibo)

                                                                                                                    <tr>
                                                                                                                        <td>

                                                                                                                            <div class="box-body">
                                                                                                                                <div class="box box-default collapsed-box" style="border: 1px solid #d2d6de;">
                                                                                                                                    <div class="box-header with-border">
                                                                                                                                        <h3 class="box-title"><b>Recibo-ID:</b> {{ $recibo['mt_recibo_id'] }} &nbsp; {{ $recibo['in_favor_view'] }}</h3>
                                                                                                                                        <div class="box-tools pull-right">
                                                                                                                                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
                                                                                                                                        </div>
                                                                                                                                    </div>
                                                                                                                                    <div class="box-body">

                                                                                                                                        <?php
                                                                                                                                        $cuota_detalle = $recibo['cuota_detalle'];
                                                                                                                                        $cuota_detalle_count = count($cuota_detalle);
                                                                                                                                        $transacciones_detalle = $recibo['transacciones_detalle'];
                                                                                                                                        $transacciones_detalle_count = count($transacciones_detalle);
                                                                                                                                        ?>

                                                                                                                                        @if($cuota_detalle !== null or $transacciones_detalle !== null)

                                                                                                                                        <div class="row">
                                                                                                                                            <div class="col-md-6">
                                                                                                                                                <div class="callout callout-default" style="background: white; border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px" title="Recibo">
                                                                                                                                                    <b>Recibo-Tipo:</b> {{ $recibo['mt_recibo_tipo'] }} <br />
                                                                                                                                                    <b>Recibo-Número:</b> {{ $recibo['mt_recibo_nro'] }} <br />
                                                                                                                                                    <b>Recibo-Monto:</b> {{ $recibo['mt_recibo_monto_view'] }} <br />
                                                                                                                                                    <b>Recibo-Fecha-Hora:</b> {{ $recibo['mt_recibo_created_at'] }} <br />
                                                                                                                                                </div>
                                                                                                                                            </div>

                                                                                                                                            <div class="col-md-6">
                                                                                                                                                <div class="callout callout-default" style="background: white; border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px" title="Movimiento">
                                                                                                                                                    <b>Movimiento-ID:</b> {{ $recibo['mt_movements_id'] }} <br />
                                                                                                                                                    <b>Movimiento-Tipo:</b> {{ $recibo['mt_movements_type'] }} <br />
                                                                                                                                                    <b>Movimiento-Destino-Operación-ID:</b> {{ $recibo['mt_movements_destination_operation_id'] }} <br />
                                                                                                                                                    <b>Movimiento-Débito-Crédito:</b> {{ $recibo['mt_movements_debit_credit'] }} <br />
                                                                                                                                                    <b>Movimiento-Monto:</b> {{ $recibo['mt_movements_amount'] }} <br />
                                                                                                                                                    <b>Movimiento-Balance-Antes:</b> {{ $recibo['mt_movements_balance_antes'] }} <br />
                                                                                                                                                    <b>Movimiento-Balance-Después:</b> {{ $recibo['mt_movements_balance'] }} <br />
                                                                                                                                                    <b>Movimiento-Fecha-Hora-Creación:</b> {{ $recibo['mt_movements_created_at'] }} <br />
                                                                                                                                                    <b>Movimiento-Fecha-Hora-Actualización:</b> {{ $recibo['mt_movements_updated_at'] }} <br />
                                                                                                                                                    <b>Movimiento-Respuesta:</b> {{ $recibo['mt_movements_response'] }} <br />
                                                                                                                                                </div>
                                                                                                                                            </div>
                                                                                                                                        </div>

                                                                                                                                        <div class="box box-default collapsed-box" style="border: 1px solid #d2d6de;">
                                                                                                                                            <div class="box-header with-border">
                                                                                                                                                <h3 class="box-title">Cuotas-Cobradas ({{ $cuota_detalle_count }} cuota/s cobrada/s)</h3>
                                                                                                                                                <div class="box-tools pull-right">
                                                                                                                                                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
                                                                                                                                                </div>
                                                                                                                                            </div>
                                                                                                                                            <div class="box-body">

                                                                                                                                                @if($cuota_detalle !== null)

                                                                                                                                                <table class="table table-bordered table-hover dataTable sub_datatables" role="grid">
                                                                                                                                                    <thead>
                                                                                                                                                        <tr>
                                                                                                                                                            <th>Cuota</th>
                                                                                                                                                            <th>Movimiento</th>
                                                                                                                                                        </tr>
                                                                                                                                                    </thead>
                                                                                                                                                    <tbody>

                                                                                                                                                        @foreach($cuota_detalle as $cuota)
                                                                                                                                                        <tr>
                                                                                                                                                            <td>
                                                                                                                                                                <div class="callout callout-default" style="background: white; border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px" title="Cuota">
                                                                                                                                                                    <b>Cuota-Cabecera-ID:</b> {{ $cuota['cuota_cabecera_id'] }} <br />
                                                                                                                                                                    <b>Cuota-Número:</b> {{ $cuota['cuota_numero'] }} <br />
                                                                                                                                                                    <b>Cuota-Importe:</b> {{ $cuota['cuota_importe'] }} <br />
                                                                                                                                                                    <b>Cuota-Saldo:</b> {{ $cuota['cuota_saldo'] }} <br />
                                                                                                                                                                    <b>Cuota-Fecha-Vencimiento:</b> {{ $cuota['cuota_fecha_vencimiento'] }} <br />
                                                                                                                                                                </div>
                                                                                                                                                            </td>

                                                                                                                                                            <td>
                                                                                                                                                                <div class="callout callout-default" style="background: white; border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px" title="Movimiento">
                                                                                                                                                                    <h4> Movimiento-ID: {{ $cuota['mt_movements_id'] }} </h4> <br />
                                                                                                                                                                    <b>Movimiento-Tipo:</b> {{ $cuota['mt_movements_type'] }} <br />
                                                                                                                                                                    <b>Movimiento-Destino-Operación-ID:</b> {{ $cuota['mt_movements_destination_operation_id'] }} <br />
                                                                                                                                                                    <b>Movimiento-Débito-Crédito:</b> {{ $cuota['mt_movements_debit_credit'] }} <br />
                                                                                                                                                                    <b>Movimiento-Respuesta:</b> {{ $cuota['mt_movements_response'] }} <br />
                                                                                                                                                                    <b>Movimiento-Monto:</b> {{ $cuota['mt_movements_amount'] }} <br />
                                                                                                                                                                    <b>Movimiento-Balance-Antes:</b> {{ $cuota['mt_movements_balance_antes'] }} <br />
                                                                                                                                                                    <b>Movimiento-Balance-Después:</b> {{ $cuota['mt_movements_balance'] }} <br />
                                                                                                                                                                    <b>Movimiento-Fecha-Hora-Creación:</b> {{ $cuota['mt_movements_created_at'] }} <br />
                                                                                                                                                                    <b>Movimiento-Fecha-Hora-Actualización:</b> {{ $cuota['mt_movements_updated_at'] }} <br />
                                                                                                                                                                </div>
                                                                                                                                                            </td>
                                                                                                                                                        </tr>
                                                                                                                                                        @endforeach
                                                                                                                                                    </tbody>
                                                                                                                                                </table>

                                                                                                                                                @else

                                                                                                                                                <b>No hay cuotas cobradas.</b>

                                                                                                                                                @endif
                                                                                                                                            </div>
                                                                                                                                        </div>

                                                                                                                                        <div class="box box-default collapsed-box" style="border: 1px solid #d2d6de;">
                                                                                                                                            <div class="box-header with-border">
                                                                                                                                                <h3 class="box-title">Transacciones-Cobradas ({{ $transacciones_detalle_count }} cobrada/s)</h3>
                                                                                                                                                <div class="box-tools pull-right">
                                                                                                                                                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
                                                                                                                                                </div>
                                                                                                                                            </div>
                                                                                                                                            <div class="box-body">
                                                                                                                                                @if($transacciones_detalle !== null)

                                                                                                                                                @foreach($transacciones_detalle as $transaccion)

                                                                                                                                                <?php
                                                                                                                                                $detalles = $transaccion['detalles'];
                                                                                                                                                $detalles_count = count($detalles);
                                                                                                                                                $mt_sales_count = 1;
                                                                                                                                                ?>

                                                                                                                                                <div class="row">
                                                                                                                                                    <div class="col-md-6">
                                                                                                                                                        <div class="callout callout-default" style="background: white; border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px" title="Cobranza">
                                                                                                                                                            <b>Cobranza-ID:</b> {{ $transaccion['mt_cobranzas_mini_x_atm_id'] }} <br />
                                                                                                                                                            <b>Cobranza-Recibo-ID:</b> {{ $transaccion['mt_cobranzas_mini_x_atm_recibo_id'] }} <br />
                                                                                                                                                            <b>Cobranza-Tipo-Pago:</b> {{ $transaccion['mt_cobranzas_mini_x_atm_tipo_pago'] }} <br />
                                                                                                                                                            <b>Cobranza-Monto</b> {{ $transaccion['mt_cobranzas_mini_x_atm_monto'] }} <br />
                                                                                                                                                            <b>Cobranza-Fecha-Hora</b> {{ $transaccion['mt_cobranzas_mini_x_atm_fecha'] }} <br />
                                                                                                                                                        </div>
                                                                                                                                                    </div>


                                                                                                                                                    <div class="col-md-6">
                                                                                                                                                        <div class="callout callout-default" style="background: white; border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px" title="Cobranza-Recibo">
                                                                                                                                                            <b>Recibo-Cobranza-ID:</b> {{ $transaccion['mt_recibos_cobranzas_x_atm_mt_cobranzas_mini_x_atm_id'] }} <br />
                                                                                                                                                            <b>Recibo-Cobranza-Recibo-ID:</b> {{ $transaccion['mt_recibos_cobranzas_x_atm_recibo_id'] }} <br />
                                                                                                                                                            <b>Recibo-Cobranza-Ventas-Cobradas:</b> {{ $transaccion['mt_recibos_cobranzas_x_atm_ventas_cobradas'] }} <br />
                                                                                                                                                            <b>Recibo-Cobranza-Saldo-Pendiente:</b> {{ $transaccion['mt_recibos_cobranzas_x_atm_saldo_pendiente'] }} <br />
                                                                                                                                                        </div>
                                                                                                                                                    </div>
                                                                                                                                                </div>

                                                                                                                                                <div class="row">
                                                                                                                                                    <div class="col-md-12">

                                                                                                                                                        <div class="box box-default" style="border: 1px solid #d2d6de;">
                                                                                                                                                            <div class="box-header with-border">
                                                                                                                                                                <h3 class="box-title">Ventas-Cobradas ({{ $detalles_count }} cobrada/s)</h3>
                                                                                                                                                                <div class="box-tools pull-right">
                                                                                                                                                                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                                                                                                                                                                </div>
                                                                                                                                                            </div>
                                                                                                                                                            <div class="box-body">

                                                                                                                                                                @if($detalles !== null)

                                                                                                                                                                <table class="table table-bordered table-hover dataTable searchable_datatables" role="grid">
                                                                                                                                                                    <thead>
                                                                                                                                                                        <tr>
                                                                                                                                                                            <th>Venta</th>
                                                                                                                                                                            <th>Movimiento</th>
                                                                                                                                                                        </tr>
                                                                                                                                                                    </thead>
                                                                                                                                                                    <tbody>
                                                                                                                                                                        @foreach($detalles as $detalles_item)

                                                                                                                                                                        <tr>
                                                                                                                                                                            <td style="width: 300px;">
                                                                                                                                                                                <div class="callout callout-default" style="background: white; border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px" title="Venta">
                                                                                                                                                                                    <h4>{{ $mt_sales_count }}) Venta-ID: {{ $detalles_item['mt_sales_id'] }} </h4> <br />
                                                                                                                                                                                    <b>Venta-Fecha-Hora:</b> {{ $detalles_item['mt_sales_fecha'] }} <br />
                                                                                                                                                                                    <b>Venta-Estado:</b> {{ $detalles_item['mt_sales_estado'] }} <br />
                                                                                                                                                                                    <b>Venta-Monto-Importe-Inicial:</b> {{ $detalles_item['mt_movements_amount'] }} <br />
                                                                                                                                                                                    <b>Venta-Monto-Saldo-Actual:</b> {{ $detalles_item['mt_sales_monto_por_cobrar'] }} <br /> <br />

                                                                                                                                                                                    <b>Recibos que afectaron esta venta:</b> <br />

                                                                                                                                                                                    @if ($detalles_item['mt_sales_detail'] !== null)
                                                                                                                                                                                    <table class="table table-bordered table-hover dataTable sub_datatables" role="grid">
                                                                                                                                                                                        <thead>
                                                                                                                                                                                            <tr>
                                                                                                                                                                                                <th>Recibo-ID</th>
                                                                                                                                                                                                <th>Monto-Antes</th>
                                                                                                                                                                                                <th>Monto-Afectado</th>
                                                                                                                                                                                                <th>Monto-Después</th>
                                                                                                                                                                                                <th>Fecha-Creación</th>
                                                                                                                                                                                            </tr>
                                                                                                                                                                                        </thead>
                                                                                                                                                                                        <tbody>

                                                                                                                                                                                            @foreach($detalles_item['mt_sales_detail'] as $mt_sales_detail)
                                                                                                                                                                                            <tr title="{{ $mt_sales_detail['description'] }}">
                                                                                                                                                                                                <td> {{ $mt_sales_detail['receipt_id'] }} &nbsp; {{ $mt_sales_detail['in_favor_view'] }}</td>
                                                                                                                                                                                                <td> {{ $mt_sales_detail['sales_amount_before'] }} </td>
                                                                                                                                                                                                <td> {{ $mt_sales_detail['sales_amount_affected_view'] }} </td>
                                                                                                                                                                                                <td> {{ $mt_sales_detail['sales_amount_after'] }} </td>
                                                                                                                                                                                                <td> {{ $mt_sales_detail['created_at'] }} </td>
                                                                                                                                                                                            </tr>
                                                                                                                                                                                            @endforeach

                                                                                                                                                                                        </tbody>
                                                                                                                                                                                    </table>
                                                                                                                                                                                    @endif

                                                                                                                                                                                </div>
                                                                                                                                                                            </td>

                                                                                                                                                                            <td>
                                                                                                                                                                                <div class="callout callout-default" style="background: white; border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px" title="Movimiento">
                                                                                                                                                                                    <h4> Movimiento-ID: {{ $detalles_item['mt_movements_id'] }} </h4> <br />
                                                                                                                                                                                    <b>Movimiento-Tipo:</b> {{ $detalles_item['mt_movements_type'] }} <br />
                                                                                                                                                                                    <b>Movimiento-Destino-Operación-ID:</b> {{ $detalles_item['mt_movements_destination_operation_id'] }} <br />
                                                                                                                                                                                    <b>Movimiento-Débito-Crédito:</b> {{ $detalles_item['mt_movements_debit_credit'] }} <br />
                                                                                                                                                                                    <b>Movimiento-Respuesta:</b> {{ $detalles_item['mt_movements_response'] }} <br />
                                                                                                                                                                                    <b>Movimiento-Monto:</b> {{ $detalles_item['mt_movements_amount'] }} <br />
                                                                                                                                                                                    <b>Movimiento-Balance-Antes:</b> {{ $detalles_item['mt_movements_balance_antes'] }} <br />
                                                                                                                                                                                    <b>Movimiento-Balance-Después:</b> {{ $detalles_item['mt_movements_balance'] }} <br />
                                                                                                                                                                                    <b>Movimiento-Fecha-Hora-Creación:</b> {{ $detalles_item['mt_movements_created_at'] }} <br />
                                                                                                                                                                                    <b>Movimiento-Fecha-Hora-Actualización:</b> {{ $detalles_item['mt_movements_updated_at'] }} <br />
                                                                                                                                                                                </div>
                                                                                                                                                                            </td>
                                                                                                                                                                        </tr>

                                                                                                                                                                        <?php
                                                                                                                                                                        $mt_sales_count += 1;
                                                                                                                                                                        ?>
                                                                                                                                                                        @endforeach
                                                                                                                                                                    </tbody>
                                                                                                                                                                </table>

                                                                                                                                                                @else

                                                                                                                                                                <b>No hay ventas cobradas.</b>

                                                                                                                                                                @endif
                                                                                                                                                            </div>
                                                                                                                                                        </div>

                                                                                                                                                    </div>
                                                                                                                                                </div>

                                                                                                                                                @endforeach

                                                                                                                                                @else

                                                                                                                                                <b>No hay transacciones cobradas.</b>

                                                                                                                                                @endif
                                                                                                                                            </div>
                                                                                                                                        </div>



                                                                                                                                        @else
                                                                                                                                        <b> Sin detalles. </b>
                                                                                                                                        @endif
                                                                                                                                    </div>
                                                                                                                                </div>
                                                                                                                            </div>

                                                                                                                        </td>
                                                                                                                    </tr>

                                                                                                                    @endforeach
                                                                                                                </tbody>
                                                                                                            </table>

                                                                                                        </div>
                                                                                                    </div>

                                                                                                    <div class="box box-default" style="border: 1px solid #d2d6de;">
                                                                                                        <div class="box-header with-border">
                                                                                                            <h3 class="box-title">Pago por Terminal - Envío a Ondanet</h3>
                                                                                                            <div class="box-tools pull-right">
                                                                                                                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                        <div class="box-body">

                                                                                                            @if($payments_x_atm !== null)

                                                                                                            <table class="table table-bordered table-hover dataTable sub_datatables" role="grid">
                                                                                                                <thead>
                                                                                                                    <tr>
                                                                                                                        <th>Pago-Por-Terminal-ID</th>
                                                                                                                        <th>Estado</th>
                                                                                                                        <th>Estado-Mensaje</th>
                                                                                                                        <th style="width: 200px;">Detalle de Pago</th>
                                                                                                                    </tr>
                                                                                                                </thead>
                                                                                                                <tbody>
                                                                                                                    @foreach($payments_x_atm as $payments_x_atm_item)
                                                                                                                    <tr>
                                                                                                                        <td>{{ $payments_x_atm_item['id'] }}</td>
                                                                                                                        <td>{{ $payments_x_atm_item['status'] }}</td>
                                                                                                                        <td>{{ $payments_x_atm_item['status_message'] }}</td>
                                                                                                                        <td class="to_beautify_json">{{ $payments_x_atm_item['payment_details'] }}</td>
                                                                                                                    </tr>
                                                                                                                    @endforeach
                                                                                                                </tbody>
                                                                                                            </table>

                                                                                                            @else
                                                                                                            <b> No encuentra ningún detalle del pago</b>
                                                                                                            @endif
                                                                                                        </div>
                                                                                                    </div>

                                                                                                    <div class="box box-default" style="border: 1px solid #d2d6de;">
                                                                                                        <div class="box-header with-border">
                                                                                                            <h3 class="box-title">Historial de bloqueo</h3>
                                                                                                            <div class="box-tools pull-right">
                                                                                                                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                                                                                                            </div>
                                                                                                        </div>
                                                                                                        <div class="box-body">

                                                                                                            @if($historial_bloqueos !== null)

                                                                                                            <table class="table table-bordered table-hover dataTable sub_datatables" role="grid">
                                                                                                                <thead>
                                                                                                                    <tr>
                                                                                                                        <th>Historial-ID</th>
                                                                                                                        <th>Historial-Saldo-Pendiente</th>
                                                                                                                        <th>Historial-Bloqueado</th>
                                                                                                                        <th>Historial-Tipo-Bloqueo</th>
                                                                                                                        <th>Historial-Fecha-Hora</th>

                                                                                                                    </tr>
                                                                                                                </thead>
                                                                                                                <tbody>
                                                                                                                    @foreach($historial_bloqueos as $bloqueo)
                                                                                                                    <tr>
                                                                                                                        <td>{{ $bloqueo['historial_bloqueos_id'] }}</td>
                                                                                                                        <td>{{ $bloqueo['historial_bloqueos_saldo_pendiente'] }}</td>
                                                                                                                        <td>{{ $bloqueo['historial_bloqueos_bloqueado'] }}</td>
                                                                                                                        <td>{{ $bloqueo['historial_bloqueos_block_type'] }}</td>
                                                                                                                        <td>{{ $bloqueo['historial_bloqueos_created_at'] }}</td>
                                                                                                                    </tr>
                                                                                                                    @endforeach
                                                                                                                </tbody>
                                                                                                            </table>

                                                                                                            @else
                                                                                                            <b> No encuentra historial en el rango de fecha del pago. </b>
                                                                                                            @endif
                                                                                                        </div>
                                                                                                    </div>
                                                                                                </div>
                                                                                            </div>

                                                                                        </td>
                                                                                    </tr>

                                                                                    @endforeach

                                                                                </tbody>
                                                                            </table>

                                                                        </div>
                                                                    </div>

                                                                </div>
                                                            </div>

                                                        </td>
                                                    </tr>
                                                    @endforeach

                                                </tbody>
                                            </table>

                                        </div>
                                    </div>



                                </div>
                            </div>

                        </td>
                    </tr>




                    @endforeach

                </tbody>
            </table>
        </div>
    </div>

    @endif

    @endif

</section>

<section class="content">

</section>

<style>
    table.table-bordered tbody th, table.table-bordered tbody td {
        vertical-align: middle;
        text-align: left;
    }
</style>
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


<!-- json_data_viewer -->
<link rel="stylesheet" href="/js/simple-beautify-json/beautify-json.css">
</link>
<script src="/js/simple-beautify-json/jquery.beautify-json.js"></script>


<!-- Iniciar objetos -->
<script type="text/javascript">

    var receipt_ids = {{$receipt_ids}};
    
    console.log('receipt_ids', receipt_ids);

    $('.select2').select2();

    //-----------------------------------------------------------------------------------------------

    function get_atms_per_group(atm_id) {

        var url = '/get_atms_per_group/';

        var group_id = parseInt($('#group_id').val());
        group_id = (Number.isNaN(group_id)) ? 'Todos' : group_id;

        var json = {
            _token: token,
            group_id: group_id
        };

        $.post(url, json, function(data, status) {

            $('#atm_id').val(null).trigger('change');
            $('#atm_id').empty().trigger("change");

            var option = new Option('Todos', 'Todos', false, false);
            $('#atm_id').append(option);

            for (var i = 0; i < data.length; i++) {
                var item = data[i];
                var id = item.id;
                var description = item.description;
                var option = new Option(description, id, false, false);
                $('#atm_id').append(option);
            }

            $('#atm_id').val(atm_id).trigger('change');
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

    $('#search').click(function(e) {
        e.preventDefault();
        search('search');
    });

    //-----------------------------------------------------------------------------------------------

    function relaunch_receipt(receipt_id) {

        console.log('se dió click...');

        var row_id = '#row_id_' + receipt_id;
        var div_id = '#div_id_' + receipt_id;
        var btn_id = '#btn_id_' + receipt_id;
        var td_message_id = '#td_message_id_' + receipt_id;

        $(div_id).attr({
            'title': 'Relanzando recibo id: ' + receipt_id,
            'disabled': 'disabled'
        });

        $(div_id).html('<i class="fa fa-spin fa-refresh"></i>');

        $(div_id).css({
            'color': '#333',
            'font-size': '20px'
        });

        $(row_id).css({
            'font-weight': 'bold',
            'background': '#f4f4f4'
        });


        var url = '/terminals_payments_relaunch_receipt/';

        var json = {
            _token: token,
            receipt_id: receipt_id
        };

        $.post(url, json, function(data, status) {

            var error = data.error;
            var message = data.message;
            var icon = '';
            var color = '';
            var border_color = '';
            var style = '';

            if (error) {
                icon = 'exclamation-circle';
                color = '#dd4b39'
                border_color = 'transparent';
                style = 'font-size: 25px;';
            } else {
                icon = 'check';
                color = '#00a65a';
                border_color = color;
            }

            $(div_id).html('<i class="fa fa-' + icon + '" style="color: ' + color + '; border: 1px solid  ' + border_color + '; border-radius: 100%; padding: 5px; ' + style + '"></i>');

            if (message !== '') {
                $(td_message_id).html('<b>Mensaje:</b> <br/> ' + message);
            }

        });

    }

    async function relaunch_all() {

        $('#div_relaunch_all').attr('onclick', '');
        $('#div_relaunch_all').attr('disabled', 'disabled');
        $('#div_relaunch_all').css('cursor', 'auto');

        $('#relaunch_all_icon').html('<i class="fa fa-spin fa-refresh"></i>');

        for (var i = 0; i < receipt_ids.length; i++) {

            var receipt_id = receipt_ids[i];
            var row_id = '#row_id_' + receipt_id;
            var div_id = '#div_id_' + receipt_id;
            var td_message_id = '#td_message_id_' + receipt_id;

            $('#relaunch_all_title').html('Relanzando recibo: ' + receipt_id + ' ...');

            console.log('Relanzando recibo: ' + receipt_id + ' ...');

            $(div_id).attr({
                'title': 'Relanzando recibo id: ' + receipt_id
            });

            $(div_id).html('<i class="fa fa-spin fa-refresh"></i>');

            $(div_id).css({
                'color': '#333',
                'font-size': '20px'
            });

            $(row_id).css({
                'font-weight': 'bold',
                'background': '#f4f4f4'
            });

            var url = '/terminals_payments_relaunch_receipt/';

            var json = {
                _token: token,
                receipt_id: receipt_id
            };

            await new Promise(function(resolve, reject) {

                $.post(url, json, function(data, status) {

                    var error = data.error;
                    var message = data.message;
                    var icon = '';
                    var color = '';
                    var border_color = '';

                    if (error) {
                        icon = 'exclamation-circle';
                        color = '#dd4b39'
                        border_color = 'transparent';
                    } else {
                        icon = 'check';
                        color = '#00a65a';
                        border_color = color;
                    }

                    $(div_id).html('<i class="fa fa-' + icon + '" style="color: ' + color + '; border: 1px solid  ' + border_color + '; border-radius: 100%; padding: 5px;"></i>');

                    if (message !== '') {
                        $(td_message_id).html('<b>Mensaje:</b> <br/> ' + message);
                    }

                    console.log('i, receipt_ids.length:', i, receipt_ids.length);

                    if (i == receipt_ids.length - 1) {

                        $('#relaunch_all_icon').html('<i class="fa fa-check"></i>');
                        $('#relaunch_all_title').html('Se relanzaron los recibos.');

                        console.log('Los recibos fueron relanzados:', receipt_ids);
                        receipt_ids = [];
                    }

                    resolve(); // Resolvemos la promesa
                });

            });

        }

    }

    //-----------------------------------------------------------------------------------------------

    var groupColumn = 0;

    $('.searchable_datatables').DataTable({
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
        ]
    });

    $('.sub_datatables').DataTable({
        orderCellsTop: true,
        fixedHeader: true,
        pageLength: 5,
        lengthMenu: [
            1, 5, 10, 20
        ],
        dom: '',
        language: {
            "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
        },
        scroller: true,
        processing: true,
        displayLength: 5,
        order: [
            [groupColumn, 'asc']
        ]
    });

    //-----------------------------------------------------------------------------------------------


    $('#created_at').val("{{ $created_at }}");
    $('#transaction_id').val("{{ $transaction_id }}");
    $('#receipt_id').val("{{ $receipt_id }}");
    $('#amount').val("{{ $amount }}");

    $('#search_type').val("{{ $search_type }}").trigger('change');

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

    var total_number_of_transactions = $.number("{{ $total_number_of_transactions }}", 0, ',', '.');
    var total_amount_of_transactions = $.number("{{ $total_amount_of_transactions }}", 0, ',', '.');

    $('#total_number_of_transactions').html(total_number_of_transactions);
    $('#total_amount_of_transactions').html(total_amount_of_transactions);

    window.onload = function() {

        var business_groups = '{!! $business_groups !!}';
        business_groups = JSON.parse(business_groups);

        $('#group_id').val(null).trigger('change');
        $('#group_id').empty().trigger("change");

        var option = new Option('Todos', 'Todos', false, false);
        $('#group_id').append(option);

        for (var i = 0; i < business_groups.length; i++) {
            var item = business_groups[i];
            var id = item.id;
            var description = item.description;
            var option1 = new Option(description, id, false, false);
            $('#group_id').append(option1);
        }

        $('#group_id').val("{{ $group_id }}").trigger('change');


        get_atms_per_group("{{ $atm_id }}");

        //-----------------------------------------------

        $('.select2').on('select2:select', function(e) {

            var id = e.currentTarget.id;

            switch (id) {
                case 'group_id':
                    get_atms_per_group('Todos')
                    break;
            }
        });


        $('#div_load').css('display', 'none');
        $('#content').css('display', 'block');
    };


    $(document).ready(function() {

        $('.to_beautify_json').beautifyJSON({
            type: "strict"
        });

        var table = $('#datatable_recibos').DataTable({
            orderCellsTop: true,
            fixedHeader: true,
            pageLength: (receipt_ids.length * 2),
            lengthMenu: [
                1, 2, 5, 10, 20, 30, 50, 70, 100, 150, 300, 500, 1000, 1500, 2000, 5000, 10000
            ],
            dom: '<"pull-left"f>',
            language: {
                "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
            },
            scroller: true,
            processing: true,
            ordering: false
            /*order: [
                [1, 'asc']
            ]*/
        });

        // Configurar el botón para mostrar la subtabla
        /*$('#datatable_recibos tbody').on('click', '.btn-details', function() {

            var fila_id = $(this).closest('tr').attr('id');
            var group_id = $(this).closest('tr').attr('group_id');
            var case_ = 'reglas';
            var row = table.row('#' + fila_id);

        
        });*/

    });
</script>
@endsection