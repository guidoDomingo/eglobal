@extends('layout')

@section('title')
    Transacciones USSD - Reporte
@endsection
@section('content')
    <style>
        .modal {
            background: rgba(0, 0, 0, 0.7);
            text-align: center;
        }

        @media screen and (min-width: 768px) {
            .modal:before {
                display: inline-block;
                vertical-align: middle;
                content: " ";
                height: 100%;
            }
        }

        .modal-dialog {
            display: inline-block;
            text-align: left;
            vertical-align: middle;
            margin-bottom: 50px;
        }

    </style>


    <section class="content-header">
        <div class="box box-default" style="border-radius: 5px;">
            <div class="box-header with-border">
                <h3 class="box-title" style="font-size: 25px;">Transacciones USSD - Reporte
                    <small>Listado de todos los paquetes y sus respectivos estados</small>
                </h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-default" title="Ayuda e información" data-toggle="modal"
                        data-target="#modal_help" style="border-radius: 5px; margin-botton: 5px; float: right">
                        <span class="fa fa-question" aria-hidden="true"></span> Ayuda
                    </button>
                </div>
            </div>


            <div class="box-body">
                <div class="row">
                    <div class="col-md-12">
                        <ol class="breadcrumb">
                            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
                            <li><a href="#">Transacciones USSD - Reporte</a></li>
                        </ol>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        @include('partials._flashes')
                        @include('partials._messages')
                    </div>
                </div>

                <div id="div_load" style="text-align: center; margin-bottom: 10px; font-size: 20px;">
                    <div>
                        <i class="fa fa-spin fa-refresh fa-2x" style="vertical-align: sub;"></i> &nbsp;
                        Cargando...

                        <p id="rows_loaded" title="Filas cargadas"></p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div id="content" style="display: none">

            <div class="box box-default" style="margin-top: -15px">
                <div class="box-header with-border">
                    <h3 class="box-title">Búsqueda personalizada</h3>
                    <div class="box-tools pull-right">
                        <!--<div class="row">
                                                                                                                                                <div class="col-md-4">
                                                                                                                                                    <button class="btn btn-primary" title="Buscar según los filtros en los registros."
                                                                                                                                                        id="search" name="search">
                                                                                                                                                        <span class="fa fa-search"></span> &nbsp; Búsqueda
                                                                                                                                                    </button>
                                                                                                                                                </div>

                                                                                                                                                <div class="col-md-4">
                                                                                                                                                    <button class="btn btn-default" title="Limpiar filtros." id="clean" name="clean">
                                                                                                                                                        <span class="fa fa-eraser"></span> &nbsp; Limpiar filtros
                                                                                                                                                    </button>
                                                                                                                                                </div>

                                                                                                                                                <div class="col-md-4">
                                                                                                                                                    <button type="submit" class="btn btn-success" title="Convertir tabla en archivo excel."
                                                                                                                                                        id="generate_x" name="generate_x">
                                                                                                                                                        <span class="fa fa-file-excel-o "></span> &nbsp; Exportar
                                                                                                                                                    </button>
                                                                                                                                                </div>
                                                                                                                                            </div>
                                                                                                                                        -->

                        <div class="btn-group btn-group-sm" role="group">
                            <button class="btn btn-info" type="button" title="Buscar según los filtros en los registros."
                                style="margin-right: 5px" id="search" name="search">
                                <span class="fa fa-search"></span> Buscar
                            </button>

                            <button class="btn btn-default" type="button" title="Limpiar filtros." style="margin-right: 5px"
                                id="clean" name="clean">
                                <span class="fa fa-eraser"></span> Limpiar
                            </button>

                            <button class="btn btn-success" type="button" title="Convertir tabla en archivo excel."
                                id="generate_x" name="generate_x">
                                <span class="fa fa-file-excel-o"></span> Exportar
                            </button>
                        </div>
                    </div>
                </div>
                <div class="box-body">
                    {!! Form::open(['route' => 'ussd_transaction_report', 'method' => 'POST', 'role' => 'form', 'id' => 'form_search']) !!}
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="timestamp">Fecha:</label>
                                <input type="text" class="form-control" style="display:block" id="timestamp"
                                    name="timestamp" placeholder="Seleccionar fecha."></input>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="phone_number">Teléfono:</label>
                                <input type="number" class="form-control" id="phone_number" name="phone_number"
                                    placeholder="Ejemplo: 0981123456"></input>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="transaction_id">Transacción:</label>
                                <input type="number" class="form-control" id="transaction_id" name="transaction_id"
                                    placeholder="Ejemplo: 1, 2, etc"></input>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="branch_id">Sucursal:</label>
                            <div class="form-group">
                                <input type="text" class="form-control" id="branch_id" name="branch_id"></input>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="atm_id">Terminal:</label>
                            <div class="form-group">
                                <input type="text" class="form-control" id="atm_id" name="atm_id"></input>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="pos_id">Punto de venta:</label>
                            <div class="form-group">
                                <input type="text" class="form-control" id="pos_id" name="pos_id"></input>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <label for="record_limit">Límite:</label>
                            <div class="form-group">
                                <select class="form-control" id="record_limit" name="record_limit"></select>
                            </div>
                        </div>

                        <div class="col-md-2" style="display: none">
                            <label for="send">Estado de envío:</label>
                            <div class="form-group">
                                <select class="form-control" id="send" name="send"></select>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <label for="service_id">Estado USSD:</label>
                            <div class="form-group">
                                <input type="text" class="form-control" id="status_id" name="status_id"></input>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <label for="transaction_status_id">Estado de transacción:</label>
                            <div class="form-group">
                                <input type="text" class="form-control" id="transaction_status_id"
                                    name="transaction_status_id"></input>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <label for="historic">Histórico:</label>
                            <div class="form-group">
                                <input type="text" class="form-control" id="historic" name="historic"></input>
                            </div>
                        </div>

                        <div class="col-md-2">
                            <label for="operator_id">Operadora:</label>
                            <div class="form-group">
                                <input type="text" class="form-control" id="operator_id" name="operator_id"></input>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="service_id">Servicio:</label>
                            <div class="form-group">
                                <input type="text" class="form-control" id="service_id" name="service_id"></input>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <label for="service_id">Paquete (Solo los paquetes activos de la Operadora):</label>
                            <div class="form-group">
                                <input type="text" class="form-control" id="option_id" name="option_id"></input>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <label for="service_id">Canal:</label>
                            <div class="form-group">
                                <input type="text" class="form-control" id="channel_id" name="channel_id"></input>
                            </div>
                        </div>

                        <div class="col-md-8">
                            <label for="final_transaction_message_id">Mensaje final de transacción:</label>
                            <div class="form-group">
                                <input type="text" class="form-control" id="final_transaction_message_id"
                                    name="final_transaction_message_id"></input>
                            </div>
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>

            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Resumen de totales por estado</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                class="fa fa-minus"></i></button>
                    </div>
                </div>
                <div class="box-body">
                    <div class="row">
                        <?php
                        $list = $data['lists']['totals_status'];
                        $days = $data['days'];
                        $total_transactions = $data['totals']['total'];
                        $total_transactions_amount = $data['totals']['total_amount'];
                        $total_transactions_amount = number_format($total_transactions_amount, 0, ',', '.');
                        ?>

                        @for ($i = 0; $i < count($list); $i++)

                            <?php
                            $item = $list[$i];
                            $parameters = json_encode($item);
                            
                            $id = $item['id'];
                            $description = $item['description'];
                            $total_status = $item['total'];
                            $total_amount = $item['total_amount'];
                            $total_amount = number_format($total_amount, 0, ',', '.');
                            $percentage = 0;
                            
                            if ($total_status !== 0 and $total_transactions !== 0) {
                                $percentage = ($total_status * 100) / $total_transactions;
                                $percentage = round($percentage, 1);
                            }
                            
                            $bg = '';
                            $fa = '';
                            $button = '';
                            
                            if ($id == 1) {
                                $bg = 'aqua';
                                $fa = 'clock-o';
                            } elseif ($id == 2) {
                                $bg = 'green';
                                $fa = 'check';
                            } elseif ($id == 3) {
                                $bg = 'red';
                                $fa = 'remove';
                            
                                $style = 'cursor: pointer; border: 1px solid #d2d6de; padding-left: 5px; padding-right: 5px; border-radius: 5px; margin-left: 5px;';
                            
                                //$button = "<a style='$style' title='Relanzar todas las transacciones con estado fallida.' onclick='relaunch_transactions($parameters)'>Relanzar transacciones</a>";
                            } elseif ($id == 4) {
                                $bg = 'teal';
                                $fa = 'question';
                            } elseif ($id == 5) {
                                $bg = 'blue';
                                $fa = 'rotate-left';
                            } elseif ($id == 6) {
                                $bg = 'orange';
                                $fa = 'ban';
                            }
                            ?>

                            <div class="col-md-6">
                                <div class="info-box">
                                    <span class="info-box-icon bg-{{ $bg }}"
                                        onclick="filter_datatable({{ $parameters }})" style="cursor:pointer"
                                        title="Ver transacciones de este estado.">
                                        <i class="fa fa-{{ $fa }}"></i>
                                    </span>
                                    <div class="info-box-content">
                                        <span class="info-box-text">{{ $description }}:
                                            <b>{{ $total_status }} transacciones ussd</b> <?php echo $button; ?> </span>
                                        <span class="info-box-number">{{ $total_amount }} Gs. en total. </span>
                                        <div class="progress">
                                            <div class="progress-bar"
                                                style="width: {{ $percentage }}%; background: dimgray"></div>
                                        </div>
                                        <span class="progress-description">
                                            Representa el <b>{{ $percentage }}%</b>
                                            <!--en {{ $days }} días. -->
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endfor
                    </div>

                    <div class="row" style="text-align: center;">
                        <div class="col-md-12">
                            <h3>Totales: </h3>
                        </div>
                    </div>

                    <div class="row" style="text-align: center;">
                        <div class="col-md-4">
                            <h4>
                                <a onclick='filter_datatable({"description":"","total":{{ $total_transactions }}})'
                                    style="cursor: pointer">
                                    <b>{{ $total_transactions }} </b> transacciones.
                                </a>
                            </h4>
                        </div>
                        <div class="col-md-4">
                            <h4>
                                <i class="fa fa-money"></i> <b>{{ $total_transactions_amount }} </b> guaranies.
                            </h4>
                        </div>
                        <div class="col-md-4">
                            <h4>
                                <b>{{ $days }}</b> días.
                            </h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6">
                    <div class="box box-default collapsed-box">
                        <div class="box-header with-border">
                            <h3 class="box-title">Cantidad de transacciones ussd por operadora</h3>
                            <div class="box-tools pull-right">
                                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_3">
                                        <thead>
                                            <tr role="row">
                                                <th></th>
                                                <th>Operador</th>
                                                <th>Cantidad</th>
                                                <th>Opción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $list = $data['lists']['total_by_operator'];
                                            ?>

                                            @foreach ($list as $item)

                                                <?php
                                                $id = $item->id;
                                                $operator = $item->operator;
                                                $count = $item->count;
                                                $list_of_ids = $item->list_of_ids;
                                                
                                                ?>

                                                <tr>
                                                    <td>{{ $id }})</td>
                                                    <td>{{ $operator }}</td>
                                                    <td>{{ $count }}</td>
                                                    <td>
                                                        <button class="btn btn-default" title="Ver estas transacciones"
                                                            style="border-radius: 3px;"
                                                            onclick="filter_by_list_of_ids('{{ $list_of_ids }}')">
                                                            <i class="fa fa-eye"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="box box-default collapsed-box">
                        <div class="box-header with-border">
                            <h3 class="box-title">Cantidad de tipo de respuesta por operadora</h3>
                            <div class="box-tools pull-right">
                                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_4">
                                        <thead>
                                            <tr role="row">
                                                <th></th>
                                                <th>Operador</th>
                                                <th>Mensaje</th>
                                                <th>Cantidad</th>
                                                <th>Opción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $list = $data['lists']['messages_by_operator'];
                                            $i = 1;
                                            ?>

                                            @foreach ($list as $item)

                                                <?php
                                                $operator = $item->operator;
                                                $final_transaction_message = $item->final_transaction_message;
                                                $count = $item->count;
                                                $list_of_ids = $item->list_of_ids;
                                                
                                                ?>

                                                <tr>
                                                    <td>{{ $i }})</td>
                                                    <td>{{ $operator }}</td>
                                                    <td>{{ $final_transaction_message }}</td>
                                                    <td>{{ $count }}</td>
                                                    <td>
                                                        <button class="btn btn-default" title="Ver estas transacciones"
                                                            style="border-radius: 3px;"
                                                            onclick="filter_by_list_of_ids('{{ $list_of_ids }}')">
                                                            <i class="fa fa-eye"></i>
                                                        </button>
                                                    </td>
                                                </tr>

                                                <?php $i++; ?>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="box box-default collapsed-box">
                        <div class="box-header with-border">
                            <h3 class="box-title">Cantidad de transacciones ussd por punto de venta</h3>
                            <div class="box-tools pull-right">
                                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_5">
                                        <thead>
                                            <tr role="row">
                                                <th>Punto de venta</th>
                                                <th>Cantidad</th>
                                                <th>Opción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $list = $data['lists']['total_by_points_of_sale'];
                                            ?>

                                            @foreach ($list as $item)

                                                <?php
                                                $description = $item->description;
                                                $count = $item->count;
                                                $list_of_ids = $item->list_of_ids;
                                                ?>

                                                <tr>
                                                    <td>{{ $description }}</td>
                                                    <td>{{ $count }}</td>
                                                    <td>
                                                        <button class="btn btn-default" title="Ver estas transacciones"
                                                            style="border-radius: 3px;"
                                                            onclick="filter_by_list_of_ids('{{ $list_of_ids }}')">
                                                            <i class="fa fa-eye"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="box box-default collapsed-box">
                        <div class="box-header with-border">
                            <h3 class="box-title">Cantidad de transacciones ussd por canal</h3>
                            <div class="box-tools pull-right">
                                <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                    <i class="fa fa-plus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="box-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_6">
                                        <thead>
                                            <tr role="row">
                                                <th>Canal</th>
                                                <th>Cantidad</th>
                                                <th>Opción</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            $list = $data['lists']['total_by_channels'];
                                            ?>

                                            @foreach ($list as $item)

                                                <?php
                                                $description = $item->channel;
                                                $count = $item->count;
                                                $list_of_ids = $item->list_of_ids;
                                                ?>

                                                <tr>
                                                    <td>{{ $description }}</td>
                                                    <td>{{ $count }}</td>
                                                    <td>
                                                        <button class="btn btn-default" title="Ver estas transacciones"
                                                            style="border-radius: 3px;"
                                                            onclick="filter_by_list_of_ids('{{ $list_of_ids }}')">
                                                            <i class="fa fa-eye"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Transacciones USSD</h3>

                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                class="fa fa-minus"></i></button>
                    </div>
                </div>
                <div class="box-body">
                    @if (isset($data['lists']))

                        <div id="hide_show_columns"></div>

                        <br />

                        <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_1">
                            <thead>
                                <tr role="row">
                                    <th title="El último estado en el cual se encuentra el paquete">Estado</th>
                                    <th title="Número de transacción seguido de su estado actual">Transacción</th>
                                    <th title="Punto de Venta">P.D.V.</th>
                                    <th title="Canal">Canal</th>
                                    <th title="Compañia teléfonica que provee el servicio ussd">Operador</th>
                                    <th title="Nombre del grupo de paquetes">Servicio</th>
                                    <th title="Opción seleccionada por el usuario">Opción</th>
                                    <th title="Monto de paquete-opción no de la transacción">Monto</th>
                                    <th title="Número de la persona a la cual se debe enviar el saldo">Teléfono</th>
                                    <th title="Fecha en la cual entró al sistema el registro">Entrada</th>
                                    <th title="Fecha de la última actualización / ejecución de comando del paquete">
                                        Actualización</th>
                                    <th title="Si se envío o no el saldo">Enviado</th>
                                    <th title="Cantidad de relanzamientos realizados por el sistema o el usuario en total">
                                        Relanzamientos</th>
                                    <th title="El último estado en el cual se encuentra el paquete.">Estado</th>
                                    <th title="Opciones de gestión para el paquete de la transacción">Opciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $list = $data['lists']['transactions']; ?>

                                @foreach ($list as $item)

                                    <?php
                                    //$item = $list[$i];
                                    $parameters = json_encode($item);
                                    
                                    $operator = $item->operator;
                                    $option = $item->option;
                                    $sub_option = $item->sub_option;
                                    $amount = $item->amount;
                                    $phone_number = $item->phone_number;
                                    $operation = $item->operation;
                                    $transaction_id = $item->transaction_id;
                                    $transaction_status = $item->transaction_status;
                                    $final_transaction_message = $item->final_transaction_message;
                                    $status_id = $item->status_id;
                                    $status = $item->menu_ussd_status;
                                    $wrong_run_counter = $item->wrong_run_counter;
                                    $atm = $item->atm;
                                    $branch = $item->branch;
                                    $created_at = $item->created_at;
                                    $updated_at = $item->updated_at;
                                    $sent = $item->sent;
                                    $channel = $item->channel;
                                    $points_of_sale = $item->points_of_sale;
                                    $relaunch_amount = $item->relaunch_amount;
                                    
                                    $color = '#ddd';
                                    
                                    if ($status == 'Fallida') {
                                        $color = '#dd4b39';
                                    } elseif ($status == 'Pendiente') {
                                        $color = '#00c0ef';
                                    } elseif ($status == 'Exitosa') {
                                        $color = '#00a65a';
                                    } elseif ($status == 'Desconocido') {
                                        $color = '#008080';
                                    } elseif ($status == 'Relanzada') {
                                        $color = '#0073b7';
                                    } elseif ($status == 'Anulado') {
                                        $color = '#ff851b';
                                    }
                                    
                                    $transaction_label = 'default';
                                    
                                    if ($transaction_status == 'success') {
                                        $transaction_label = 'success';
                                    } elseif ($transaction_status == 'pendiente' or $transaction_status == 'procesando') {
                                        $transaction_label = 'info';
                                    } elseif ($transaction_status == 'iniciated' or $transaction_status == 'nulled' or $transaction_status == 'reprocesando' or $transaction_status == 'canceled' or $transaction_status == 'cancelled') {
                                        $transaction_label = 'warning';
                                    } elseif ($transaction_status == 'error' or $transaction_status == 'rollback' or $transaction_status == 'error dispositivo' or $transaction_status == 'devolucion' or $transaction_status == 'inconsistency') {
                                        $transaction_label = 'danger';
                                    }
                                    
                                    ?>

                                    <tr title="Mensaje de operadora: {{ $final_transaction_message }}">
                                        <td title="El paquete está con estado: {{ $status }}"
                                            style="cursor: pointer; color: {{ $color }}">
                                            <!--<i class="fa fa-cube"></i>--> {{ $status }}
                                        </td>

                                        <td
                                            title="La transacción número: {{ $transaction_id }} está con estado: {{ $transaction_status }}">
                                            <span class="label label-{{ $transaction_label }}">
                                                {{ $transaction_id }}: {{ $transaction_status }}
                                            </span>
                                        </td>
                                        <td>{{ $points_of_sale }}</td>
                                        <td>{{ $channel }}</td>
                                        <td>{{ $operator }}</td>
                                        <td>{{ $option }}</td>
                                        <td>{{ $sub_option }}</td>
                                        <td>{{ $amount }}</td>
                                        <td>{{ $phone_number }}</td>
                                        <td>{{ $created_at }}</td>
                                        <td>{{ $updated_at }}</td>
                                        <td>{{ $sent }}</td>
                                        <td>{{ $relaunch_amount }}</td>
                                        <td>{{ $status }}</td>

                                        <td style="text-align: center">
                                           <!-- @if (Sentinel::hasAccess('ussd_transaction_edit'))
                                                @if ($status_id == 3 or $status_id == 4)
                                                    <button class="btn btn-default" title="Ver y Editar transacción"
                                                        style="border-radius: 3px;"
                                                        onclick="click_in_view_and_edit({{ $parameters }});">
                                                        <i class="fa fa-pencil"></i>
                                                    </button>
                                                    <button class="btn btn-default" title="Relanzar transacción"
                                                        style="border-radius: 3px;"
                                                        onclick="relaunch({{ $parameters }})">
                                                        <i class="fa fa-rotate-left"></i>
                                                    </button>
                                                @endif
                                            @endif

                                            @if (Sentinel::hasAccess('ussd_transaction_view'))
                                                @if ($status_id == 2)
                                                    <button class="btn btn-default" title="Ver transacción"
                                                        style="border-radius: 3px;"
                                                        onclick="click_in_view_and_edit({{ $parameters }});">
                                                        <i class="fa fa-eye"></i>
                                                    </button>
                                                @endif
                                            @endif -->
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="alert alert-danger" role="alert">
                            No hay registros de <b> transacciones</b>.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Modal -->
            <div id="modal" class="modal fade" role="dialog" tabindex="-1" data-backdrop="static"
                data-keyboard="false">
                <div class="modal-dialog modal-dialog-centered" role="document"
                    style="background: white; border-radius: 5px">
                    <!-- Modal content-->
                    <div class="modal-content" style="border-radius: 10px">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <div class="modal-title" style="font-size: 20px;">
                                Transacción: &nbsp; <small> <b> </b> </small>
                            </div>
                        </div>
                    </div>

                    <div class="modal-body">
                        <!-- TAB PANNEL -->
                        <div class="panel with-nav-tabs">
                            <div class="panel-heading">
                                <ul class="nav nav-tabs" id="myTabs">
                                    <li class="active">
                                        <a href="#tab_1" data-toggle="tab"><i class="fa fa-eye"></i> Datos</a>
                                    </li>

                                    <li>
                                        <a href="#tab_2" data-toggle="tab"><i class="fa fa-rotate-left"></i>
                                            Modificar</a>
                                    </li>

                                    <!--<li><a href="#tab_3" data-toggle="tab"><i class="fa fa-pencil"></i>
                                                                                                                                Igualar estados</a></li>-->
                                </ul>
                            </div>
                            <div class="panel-body">
                                <div class="tab-content">
                                    <div class="tab-pane fade in active" id="tab_1">
                                        <table class="table table-bordered table-hover dataTable" role="grid"
                                            id="datatable_2">
                                            <thead>
                                                <tr role="row">
                                                    <th colspan="2">Datos de transacción</th>
                                                </tr>
                                            </thead>
                                            <tbody id="datatable_2_table_body"></tbody>
                                        </table>
                                    </div>

                                    <div class="tab-pane fade" id="tab_2">

                                        <!-- TAB PANNEL -->
                                        <div class="panel with-nav-tabs">
                                            <div class="panel-heading">
                                                <ul class="nav nav-tabs" id="myTabs">
                                                    <li class="active">
                                                        <a href="#tab_form" data-toggle="tab"><i
                                                                class="fa fa-form"></i> Modificar USSD</a>
                                                    </li>

                                                    <li>
                                                        <a href="#tab_list" data-toggle="tab"><i
                                                                class="fa fa-list"></i>
                                                            Lista</a>
                                                    </li>
                                                </ul>
                                            </div>
                                            <div class="panel-body">
                                                <div class="tab-content">
                                                    <div class="tab-pane fade in active" id="tab_form">
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="replacement_number">Nuevo número de
                                                                        teléfono:</label>
                                                                    <input type="number" class="form-control"
                                                                        style="width: 100%"
                                                                        placeholder="Ingrese el nuevo número de teléfono"
                                                                        id="replacement_number" name="replacement_number"
                                                                        required></input>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="reason">Razón:</label>
                                                                    <div class="form-group">
                                                                        <input type="text" class="form-control"
                                                                            id="reason" name="reason" required></input>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="recharge_type">Tipo de recarga a
                                                                        relanzar:</label>
                                                                    <div class="form-group">
                                                                        <input type="text" class="form-control"
                                                                            id="recharge_type" name="recharge_type"
                                                                            required></input>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="col-md-6">
                                                                <div class="form-group">
                                                                    <label for="commentary">Comentario:</label>
                                                                    <textarea class="form-control" rows="5"
                                                                        placeholder="Agrega un comentario opcional sobre la carga..."
                                                                        style="max-width: 100%; max-height: 150px"
                                                                        id="commentary" name="commentary"></textarea>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="tab-pane fade" id="tab_list">
                                                        <div class="row">

                                                            <div class="col-md-12">
                                                                <div class="form-group">
                                                                    <label id="recharge_name" name="recharge_name"></label>
                                                                </div>
                                                            </div>

                                                            <!--<div class="col-md-12">
                                                                                            <div class="form-group">
                                                                                                <label id="recharge_amount"
                                                                                                    name="recharge_amount"></label>
                                                                                            </div>
                                                                                        </div>-->

                                                            <div class="col-md-12">
                                                                <table class="table table-bordered table-hover dataTable"
                                                                    role="grid" id="datatable_7">
                                                                    <thead>
                                                                        <tr role="row">
                                                                            <!--<th>Operador</th>-->
                                                                            <th>Monto</th>
                                                                            <th>Cantidad</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody id="datatable_7_table_body"></tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <!--<label for="id">Número de Recarga:</label>-->
                                            <input type="hidden" class="form-control" style="width: 100%" id="id"
                                                name="id" readonly></input>
                                        </div>

                                        <div style="float:right">
                                            <div class="btn-group mr-2" role="group">
                                                <button class="btn btn-danger pull-right"
                                                    title="Cancela totalmente la transacción." style="margin-right: 10px"
                                                    id="cancel">
                                                    <span class="fa fa-remove" aria-hidden="true"></span>
                                                    &nbsp; Cancelar
                                                </button>
                                            </div>
                                            <div class="btn-group mr-2" role="group">
                                                <button class="btn btn-primary pull-right"
                                                    title="Confirma la lista de registros a actualizar." id="confirm"
                                                    onclick="edit_transaction()">
                                                    <span class="fa fa-save" aria-hidden="true"></span>
                                                    &nbsp; Confirmar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- FIN TAB PANNEL -->
                    </div>
                </div>
            </div>

            <!-- Modal ayuda-->
            <div id="modal_help" class="modal fade" role="dialog" tabindex="-1" data-backdrop="static"
                data-keyboard="false">
                <div class="modal-dialog modal-dialog-centered modal-lg" role="document"
                    style="width: 800px; background: white; border-radius: 5px">
                    <!-- Modal content-->
                    <div class="modal-content" style="border-radius: 10px">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal">&times;</button>
                            <div class="modal-title" style="font-size: 20px;">
                                Ayuda e información &nbsp; <small> <b> </b> </small>
                            </div>
                        </div>
                    </div>

                    <div class="modal-body">
                        <!-- TAB PANNEL -->
                        <div class="panel with-nav-tabs">
                            <div class="panel-heading">
                                <ul class="nav nav-tabs">
                                    <li class="active"><a href="#tab_help_0" data-toggle="tab">
                                            Resumen por totales </a>
                                    </li>
                                    <li><a href="#tab_help_1" data-toggle="tab">
                                            Búsqueda personalizada </a>
                                    </li>
                                    <li><a href="#tab_help_2" data-toggle="tab">
                                            Transacciones</a></li>
                                    <li><a href="#tab_help_3" data-toggle="tab">
                                            Formulario</a></li>
                                </ul>
                            </div>
                            <div class="panel-body">
                                <div class="tab-content">
                                    <!-- Solamente el contenido se cambia -->
                                    <div class="tab-pane fade in active" id="tab_help_0">
                                        <div class="callout callout-info">
                                            <h5><b>Definición de Resumen por totales:</b></h5>
                                            <p>El resumen por totales muestra un resumen de la cantidad de transacciones por
                                                estado, el total en guaranies y el porcentaje que representa en el periodo
                                                de
                                                tiempo. </p>
                                        </div>

                                        <div class="callout callout-default">
                                            <b>Pendiente:</b> Son transacciones que aún no han sido procesadas
                                            por el
                                            serivicio ussd. <br />

                                            <b>Exitosa:</b>
                                            Son transacciones que ya han sido procesadas por el serivicio ussd y se envió el
                                            saldo
                                            al cliente. <br />

                                            <b>Fallida:</b>
                                            Son transacciones que ya han sido procesadas por el serivicio ussd y la
                                            operadora
                                            respondió con un mensaje de error.
                                            <br />

                                            <b>Desconocida:</b>
                                            Son transacciones que ya han sido procesadas por el serivicio ussd y no pudo
                                            detectar el
                                            origen del error. En este caso no se sabe si el saldo llego al cliente.
                                            Es necesario una gestión del usuario con el cliente<br />

                                            <b>Relanzada:</b>
                                            Son transacciones que se relanzaron por el usuario manualmente o
                                            por el sistema ussd para que se pueda cargar el saldo que no se pudo transferir
                                            al
                                            cliente.<br />

                                            <b>Anulado:</b>
                                            Son transacciones que se anularon por el usuario. <br />
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="tab_help_1">
                                        <div class="callout callout-info">
                                            <h5><b>Definición de búsqueda personalizada:</b> </h5>
                                            Los filtros de búsqueda personalizada
                                            sirven para traer registros
                                            guardados según los filtros elegidos.
                                        </div>

                                        <div class="callout callout-default">
                                            <b>Teléfono:</b> Sirve para filtrar la búsqueda ingresando el número de teléfono
                                            del cliente. <br />

                                            <b>Fecha:</b>
                                            Sirve para filtrar la búsqueda ingresando una fecha especifica o un rango de
                                            fecha personalizado. <br />

                                            <b>Sucursal:</b>
                                            Sirve para filtrar por la sucursal de donde se compró el saldo. <br />

                                            <b>Terminal:</b>
                                            Sirve para filtrar por la terminal de donde se compró el saldo. <br />

                                            <b>Estado:</b>
                                            Sirve para filtrar la búsqueda por el estado de la transacción. <br />

                                            <b>Límite:</b>
                                            Sirve para filtrar la búsqueda ingresando el número de teléfono del cliente.
                                            <br />

                                            <b>Buscar:</b>
                                            Haciendo click en el botón buscar ejecuta la acción para traer los registros
                                            según los filtros ingresados por el usuario. <br />

                                            <b>Exportar:</b>
                                            Haciendo click en el botón exportar el sistema va a generar un excel con los
                                            filtros
                                            activos.
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="tab_help_2">
                                        <div class="callout callout-info">
                                            <h5><b>Definición de transacciones:</b> </h5>
                                            En la sección de transacciones se muestra una lista de todas las transacciones
                                            existentes según los filtros de busqueda previamente seleccionados.

                                        </div>

                                        <div class="callout callout-default">
                                            <h5><b>Columnas de la tabla:</b></h5>

                                            <b>Estado:</b> Muestra en que estado se encuentra la transacción, la tabla tiene
                                            los
                                            registros agrupados por estado. <br />

                                            <b>Sucursal:</b>
                                            Es la sucursal relacionada a la transacción. <br />

                                            <b>Terminal:</b>
                                            Es la terminal relacionada a la transacción. <br />

                                            <b>Transacción:</b>
                                            Número de transacción. <br />

                                            <b>Servicio:</b>
                                            Es el paquete que contiene las opciones del menú por ejemplo: Paquete de
                                            Internet o
                                            llamadas <br />

                                            <b>Opción:</b>
                                            Es la opción de los paquetes que el cliente seleccionó desde el cajero.
                                            <br />

                                            <b>Monto:</b>
                                            Es el precio que tiene la opción seleccionada por el cliente. <br />

                                            <b>Teléfono:</b>
                                            Es el teléfono que el cliente seleccionó para cargar el paquete. <br />

                                            <b>Fecha:</b>
                                            Es la fecha y hora en la que se registró la transacción. <br />

                                            <b>Opciones:</b>
                                            En la columna de opciones hay 2 tipo de botones posibles:

                                            <ul>
                                                <li><b> <i class="fa fa-eye"></i> Vista: </b> El botón de vista solo
                                                    permite ver
                                                    más
                                                    datos sobre la transacción.</li>

                                                <li><b> <i class="fa fa-pencil"></i> Edición: </b> El botón de edición
                                                    permite
                                                    ver
                                                    más
                                                    datos sobre la transacción y también editar el registro.</li>
                                                <li><b> <i class="fa fa-rotate-left"></i> Relanzar: </b> El botón de
                                                    relanzamiento permite volver a ingresar
                                                    la transacción en la cola de transacciones para poder enviar el saldo.
                                                </li>
                                            </ul>
                                        </div>

                                        <div class="callout callout-default">
                                            <h5><b>Observación:</b></h5>
                                            <ul>
                                                <li> Las transacciones con estado: <b>Pendiente, exitosa, relanzada,
                                                        anulado</b>
                                                    solo pueden
                                                    <b>visualizarse</b>.
                                                </li>

                                                <li> Solo las transacciones con estado: <b>Fallido, desconocido</b> son
                                                    posibles
                                                    <b>visualizar y editar</b>.
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="tab_help_3">
                                        <div class="callout callout-info">
                                            <h5><b>Definición de formulario:</b> </h5>
                                            En el formulario podemos ver los datos del registro o editarlo.
                                        </div>


                                        <div class="panel with-nav-tabs">
                                            <div class="panel-heading">
                                                <ul class="nav nav-tabs">
                                                    <li class="active"><a href="#tab_form_1" data-toggle="tab">
                                                            Campos Visibles </a>
                                                    </li>
                                                    <li><a href="#tab_form_2" data-toggle="tab">
                                                            Campos Editables</a></li>

                                                    <li><a href="#tab_form_3" data-toggle="tab">
                                                            Botones</a></li>
                                                </ul>
                                            </div>
                                            <div class="panel-body">
                                                <div class="tab-content">
                                                    <div class="tab-pane fade in active" id="tab_form_1">
                                                        <div class="callout callout-default">
                                                            <h5><b>Definición de Campos Visibles:</b></h5>
                                                            Son los campos que están solo para visualización y no pueden
                                                            editarse.
                                                        </div>

                                                        <div class="callout callout-default">
                                                            <b>Número de carga:</b> Es el número de la carga asignado a el
                                                            registro.
                                                            <br />

                                                            <b>Número de transacción:</b> Es el número de transacción
                                                            asignado a
                                                            el
                                                            registro. <br />

                                                            <b>Mensaje de operadora:</b>
                                                            Es el mensaje obtenido de la operadora.
                                                            <br />

                                                            <b>Entrada:</b>
                                                            Es la fecha y hora que la solicitud de carga entró al sistema.
                                                            <br />

                                                            <b>Ejecución:</b>
                                                            Es la fecha y hora en cual el servicio ussd ejecutó la
                                                            solicitud.
                                                        </div>
                                                    </div>

                                                    <div class="tab-pane fade" id="tab_form_2">

                                                        <div class="callout callout-default">
                                                            <h5><b>Definición de Campos Editables:</b> </h5>
                                                            Son los campos que están solo para ser editados por el usuario.
                                                        </div>

                                                        <div class="callout callout-default">
                                                            <b>Número de Recarga:</b> Es el número de la carga identificable
                                                            del
                                                            registro. <br />

                                                            <b>Nuevo número de teléfono:</b> Es el nuevo número comunicado
                                                            por
                                                            el
                                                            cliente. <br />

                                                            <b>Razón:</b>
                                                            Es la razón por la cual la carga no pudo ser realizada.
                                                            <br />

                                                            <b>Tipo de recarga:</b>
                                                            Es el tipo de recarga que podría elegir el cliente o el usuario.
                                                            Las opciones serian: El paquete seleccionado por el cliente o
                                                            carga
                                                            normal de saldo.
                                                            <br />

                                                            <b>Comentario:</b>
                                                            En este campo se puede ingresar una descripción más amplia sobre
                                                            la
                                                            situación ocurrida. <br />
                                                        </div>
                                                    </div>

                                                    <div class="tab-pane fade" id="tab_form_3">
                                                        <div class="callout callout-default">
                                                            <h5><b>Definición de Botones:</b></h5>
                                                            En el formulario de vista o edición se puede encontrar dos
                                                            botones
                                                            que son: Cancelar y Confirmar.
                                                        </div>

                                                        <div class="callout callout-default">
                                                            <ul>
                                                                <li><b> <i class="fa fa-remove"></i> Cancelar: </b> Este
                                                                    botón
                                                                    sirve
                                                                    para cancelar la operación y cerrar la ventana del
                                                                    formulario.
                                                                </li>

                                                                <li><b> <i class="fa fa-save"></i> Confirmar: </b> Este
                                                                    botón
                                                                    sirve
                                                                    para confirmar la transacción editada.</li>
                                                            </ul>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>
                            <!-- FIN TAB PANNEL -->
                        </div>

                        <div class="modal-footer">
                            <div style="float:right">
                                <div class="btn-group mr-2" role="group">
                                    <button class="btn btn-danger pull-right" title="Cerrar ayuda e información."
                                        style="margin-right: 10px" data-dismiss="modal">
                                        <span class="fa fa-remove" aria-hidden="true"></span>
                                        &nbsp; Cerrar ayuda
                                    </button>
                                </div>
                            </div>
                        </div>
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
    <link href="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker-bs3.css" rel="stylesheet"
        type="text/css" />
    <script src="/bower_components/admin-lte/plugins/daterangepicker/moment.min.js"></script>
    <script src="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker.js"></script>

    <!-- bootstrap datepicker -->
    <script src="/bower_components/admin-lte/plugins/datepicker/bootstrap-datepicker.js"></script>

    <!--<script src="https://cdnjs.cloudflare.com/ajax/libs/PrintArea/2.4.1/jquery.PrintArea.min.js"></script>-->

    <!-- Iniciar objetos -->
    <script type="text/javascript">
        var automatic_search = false;
        var is_expand = false;
        var detail_list = [];
        var data_table_config;
        var amount_total = 0;

        function print() {
            /*var ficha = document.getElementById('resume');
            var ventimp = window.open(' ', 'popimpr');
            ventimp.document.write(ficha.innerHTML);
            ventimp.document.close();
            ventimp.print();
            ventimp.close();*/

            var options = {
                mode: 'popup',
                popClose: true,
                //extraCss: '/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.css',
                retainAttr: true,
                extraHead: '<meta charset="utf-8" />,<meta http-equiv="X-UA-Compatible" content="IE=edge"/>',
                strict: true
            };

            $("#resume").printArea(options);

            /*var doc = new jsPDF()
            doc.text(10, 10, 'This is a test')
            doc.autoPrint()
            doc.save('autoprint.pdf')*/

            // location.href = 'print'
        }

        function contract_expand() {
            $('.btn-box-tool').trigger('click');

            if (is_expand) {
                $('#contract_expand').html('<i class="fa fa-minus"></i> Contraer');
                is_expand = false;
            } else {
                $('#contract_expand').html('<i class="fa fa-plus"></i> Expandir');
                is_expand = true;
            }
        }

        function relaunch_transactions(parameters) {
            var amount = parameters['total'];

            if (amount > 0) {

                $('.sweet-alert button.cancel').css('background', '#dd4b39');

                swal({
                        title: 'Atención',
                        text: 'Las transacciones con estado fallida pasarán a ser relanzadas.',
                        type: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#0073b7',
                        confirmButtonText: 'Aceptar',
                        cancelButtonText: 'Cancelar',
                        closeOnClickOutside: false,
                        showLoaderOnConfirm: false
                    },
                    function(isConfirmMessage) {
                        if (isConfirmMessage) {
                            //Hacer actualización
                            var url = '/ussd/transaction/ussd_transaction_relaunch/';
                            var json = {
                                _token: token,
                                phone_number: $('#phone_number').val(),
                                timestamp: $('#timestamp').val(),
                                branch: $('#branch').val(),
                                atm_id: $('#atm_id').val(),
                                menu_ussd_status_id: $('#menu_ussd_status_id').val(),
                                record_limit: $('#record_limit').val(),
                                service_id: $('#service_id').val(),
                            };

                            $.post(url, json, function(data, status) {
                                var error = data.error;
                                var message = data.message;
                                var type = '';
                                var text = '';

                                if (error == true) {
                                    type = 'error';
                                    text = 'Ocurrió un problema al relanzar transacciones.';
                                } else {
                                    type = 'success';
                                }

                                swal({
                                        title: message,
                                        text: text,
                                        type: type,
                                        showCancelButton: false,
                                        confirmButtonColor: '#3c8dbc',
                                        confirmButtonText: 'Aceptar',
                                        cancelButtonText: 'No.',
                                        closeOnClickOutside: false
                                    },
                                    function(isConfirmSearch) {
                                        if (isConfirmSearch) {
                                            $('#search').trigger('click');
                                        }
                                    }
                                );
                            }).error(function(error) {
                                console.log('ERROR AL RELANZAR TRANSACCIONES:', error);
                            });
                        }
                    }
                );
            } else {
                filter_datatable(parameters);
            }
        }

        function filter_by_list_of_ids(list_of_ids) {

            $('html, body').animate({
                scrollTop: 1
            }, 500);

            $('#form_search').append('<input type="hidden" name="list_of_ids" value="' + list_of_ids + '" />');
            $('#form_search').submit();
            $('#content').css('display', 'none');
            $('#div_load').css('display', 'block');

        }

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

        function validate_replacement_number() {
            var text = $("#replacement_number").val();

            if (text.match(regex)) {
                $("#replacement_number").css({
                    "border": "1px solid green"
                });

                $("#confirm").css({
                    "display": "block"
                });
            } else {
                $("#replacement_number").css({
                    "border": "1px solid red"
                });

                $("#confirm").css({
                    "display": "none"
                });
            }
        }

        function edit_transaction() {

            var save = true;
            var message_text = '';

            if ($('#recharge_type').val() == '') {
                save = false;
                message_text = 'Tipo de recarga (campo obligatorio)';
            }

            if ($('#reason').val() == '') {
                save = false;
                message_text = 'Razón (campo obligatorio)';
            } else {
                if ($('#reason').val() == 3) { //Otros motivos.
                    if ($('#commentary').val() == '') {
                        save = false;
                        message_text = 'Comentario (campo obligatorio)';
                    }
                }
            }

            if ($('#replacement_number').val() == '') {
                save = false;
                message_text = 'Número de teléfono (campo obligatorio)';
            }

            var ids = [];

            if ($('#recharge_type').val() == 2) {
                for (var i = 0; i < detail_list.length; i++) {
                    var item = detail_list[i];
                    var menu_ussd_detail_id = item.menu_ussd_detail_id;

                    var input_amount_value = $('#input_amount_' + menu_ussd_detail_id).val();
                    var input_count_value = $('#input_count_' + menu_ussd_detail_id).val();

                    if (input_amount_value > 0 && input_count_value > 0) {
                        ids.push(menu_ussd_detail_id);
                    }
                }

                if (ids.length <= 0) {
                    save = false;
                    message_text = 'Lista de saldo normal (campo obligatorio)';
                }
            }

            console.log('IDS:', ids);

            if (save) {

                $("#modal").modal('hide');

                var url = '/ussd/transaction/ussd_transaction_edit/';

                var json = {
                    _token: token,
                    phone_number: $('#phone_number').val(),
                    timestamp: $('#timestamp').val(),
                    branch: $('#branch').val(),
                    atm_id: $('#atm_id').val(),
                    menu_ussd_status_id: $('#menu_ussd_status_id').val(),
                    record_limit: $('#record_limit').val(),
                    service_id: $('#service_id').val(),

                    id: $('#id').val(),
                    replacement_number: $('#replacement_number').val(),
                    reason: $('#reason').val(),
                    recharge_type: $('#recharge_type').val(),
                    commentary: $('#commentary').val(),
                    ids: ids
                };

                $.post(url, json, function(data, status) {
                    var error = data.error;
                    var message = data.message;
                    var type = '';
                    var text = '';

                    if (error == true) {
                        type = 'error';
                        text = 'Ocurrió un problema al procesar la transacción';
                    } else {
                        type = 'success';
                    }

                    swal({
                            title: message,
                            text: text,
                            type: type,
                            showCancelButton: false,
                            confirmButtonColor: '#3c8dbc',
                            confirmButtonText: 'Aceptar',
                            cancelButtonText: 'No.',
                            closeOnClickOutside: false
                        },
                        function(isConfirm) {
                            if (isConfirm) {
                                $('#search').trigger('click');
                            }
                        }
                    );
                }).error(function(error) {
                    console.log('ERROR AL EDITAR:', error);
                });
            } else {
                swal({
                        title: message_text,
                        text: 'Debes completar todos los campos.',
                        type: 'error',
                        showCancelButton: false,
                        confirmButtonColor: '#3c8dbc',
                        confirmButtonText: 'Aceptar',
                        cancelButtonText: 'No.',
                        closeOnClickOutside: false
                    },
                    function(isConfirm) {
                        if (isConfirm) {

                        }
                    }
                );
            }
        }

        function relaunch(parameters) {
            var id = parameters['operation'];
            var replacement_number = parameters['phone_number'];

            var url = '/ussd/transaction/ussd_transaction_edit/';

            var json = {

                _token: token,
                phone_number: $('#phone_number').val(),
                timestamp: $('#timestamp').val(),
                branch: $('#branch').val(),
                atm_id: $('#atm_id').val(),
                menu_ussd_status_id: $('#menu_ussd_status_id').val(),
                record_limit: $('#record_limit').val(),
                service_id: $('#service_id').val(),

                id: id,
                replacement_number: replacement_number, //El mismo número.
                reason: 4, //Error del sistema
                recharge_type: 1, //Tipo de recarga seleccionada por el cliente
                commentary: null
            };

            $.post(url, json, function(data, status) {
                var error = data.error;
                var message = data.message;
                var type = '';
                var text = '';

                if (error == true) {
                    type = 'error';
                    text = 'Ocurrió un problema al procesar la transacción';
                } else {
                    type = 'success';
                }

                swal({
                        title: message,
                        text: text,
                        type: type,
                        showCancelButton: false,
                        confirmButtonColor: '#3c8dbc',
                        confirmButtonText: 'Aceptar',
                        cancelButtonText: 'No.',
                        closeOnClickOutside: false
                    },
                    function(isConfirm) {
                        if (isConfirm) {
                            $('#search').trigger('click');
                        }
                    }
                );
            }).error(function(error) {
                console.log('ERROR AL EDITAR:', error);
            });
        }

        function get_values_from_checkbox(value) {
            if ($('#checkbox_500').is(":checked") && $('#checkbox_1000').is(":checked")) {
                if (value == 500) {
                    $('#checkbox_500').prop('checked', false);
                    $('#checkbox_1000').prop('checked', true);
                } else if (value == 1500) {
                    $('#checkbox_500').prop('checked', true);
                    $('#checkbox_1000').prop('checked', false);
                }
            }
        }

        function get_value_amount(ids, amount) {

            var amount_total = 0;
            var amount_aux = 0;
            var amount_equals = false;
            var message = '';

            for (var i = 0; i < ids.length; i++) {
                var id = ids[i];

                var input_amount_value = $('#input_amount_' + id).val();
                var input_count_value = $('#input_count_' + id).val();

                if (amount == input_amount_value) {
                    amount_equals = true;
                    amount_total = amount_total + (input_count_value * input_amount_value);
                    break;
                } else {
                    if (input_amount_value > 0 && input_count_value > 0) {
                        amount_total = amount_total + (input_count_value * input_amount_value);
                    }
                }

                //console.log('Monto: ' + input_amount_value, (input_amount_value % 2));
            }

            /*if ($('#checkbox_500').is(":checked")) {
                amount_aux = 500;
            } else if ($('#checkbox_1000').is(":checked")) {
                amount_aux = 1000;
            }*/


            var title = '';
            var text = '';
            var amount_aux_1 = (amount + 500);
            var amount_aux_2 = (amount + 1000);
            var show_confirm = false;

            $('#confirm').css({
                'display': 'none'
            });

            //console.log('amount: ' + amount + ', amount_total: ' + amount_total);

            /*
                Existe un monto igual y el monto fué superado ?
            */
            if (amount_equals) {
                if (amount == amount_total) {
                    show_confirm = true;
                } else {
                    title = 'Existe un monto de saldo normal disponible.';
                    text = 'Hay una opción de ' + amount + ' disponible en la lista.';
                }
            } else {
                if (amount == amount_total) {
                    show_confirm = true;
                } else if (amount_total <= amount_aux_1 || amount_total <= amount_aux_2) {
                    if (amount_total == amount_aux_1 || amount_total == amount_aux_2) {
                        show_confirm = true;
                    }
                } else {
                    title = 'El monto a acreditar supera el limite.';
                    text = 'El monto debe ser igual a las siguientes variantes: \n a) (' + amount + ' + 500) \n b) (' +
                        amount + ' + 1000)';
                }
            }

            var color = 'red';

            if (show_confirm) {
                $('#confirm').css({
                    'display': 'block'
                });

                color = 'green';
            }

            if (title !== '' && text !== '') {
                swal({
                        title: title,
                        text: text,
                        type: 'error',
                        showCancelButton: false,
                        confirmButtonColor: '#3c8dbc',
                        confirmButtonText: 'Aceptar',
                        cancelButtonText: 'No.',
                        closeOnClickOutside: false
                    },
                    function(isConfirm) {
                        if (isConfirm) {

                        }
                    }
                );
            }

            //console.log('monto: ' + amount_total);

            //$('#recharge_amount').html('Monto alcanzado: <b><font color="' + color + '">' + amount_total + '</font></b>');
        }

        function add_amount_item(parameters) {
            var item = parameters;
            var menu_ussd_detail_id = item['menu_ussd_detail_id'];
            var operator = item['operator'];
            var option = item['option'];
            var amount = item['amount'];
            //var amount_aux = item['amount_aux'];
            //var amount_description = item['amount_description'];
            var count = item['count'];
            //var ids_to_json = item['ids_to_json'];

            var style = 'border: 1px solid green';

            var input_amount = $('<input>');
            input_amount.attr({
                'id': 'input_amount_' + menu_ussd_detail_id,
                'type': 'text',
                'value': amount,
                'title': 'El monto más cercano para la operación',
                'style': 'float: left; cursor: pointer; ' + style,
                'disabled': 'disabled'
            });

            var input_count = $('<input>');
            input_count.attr({
                'id': 'input_count_' + menu_ussd_detail_id,
                'type': 'number',
                'value': count,
                'title': 'Cantidad designada',
                'style': 'float: left; cursor: pointer; ' + style,
                //'onkeyup': 'get_value_amount(' + ids_to_json + ', ' + amount + ')',
                //'onmouseup': 'get_value_amount(' + ids_to_json + ', ' + amount + ')',
            });

            input_count.prop('disabled', true);

            $('#datatable_7_table_body')
                .append(
                    $('<tr>')
                    //.append($('<td>').append(operator))
                    .append($('<td>').append(input_amount))
                    .append($('<td>').append(input_count))
                );
        }

        //Función para mostrar la vista y edición 0981234123
        function click_in_view_and_edit(parameters) {

            var id = parameters['operation'];
            var transaction_id = parameters['transaction_id'];
            var replacement_number = parameters['phone_number'];
            var final_transaction_message = parameters['final_transaction_message'];
            var created_at = parameters['created_at'];
            var updated_at = parameters['updated_at'];
            var status = parameters['status_id'];
            var wrong_run_counter = parameters['wrong_run_counter'];
            var menu_ussd_operator_id = parameters['menu_ussd_operator_id'];
            var sub_option = parameters['sub_option'];
            var amount = parameters['amount'];

            $('#id').val(id);
            $("#replacement_number").val(replacement_number);
            $("#commentary").val('');
            $("#recharge_name").text('Opción original: ' + sub_option + ' por valor de: ' + amount);
            //$('#recharge_amount').html('Monto alcanzado: <b><font color="red">0</font></b>');

            $('#reason').selectize()[0].selectize.setValue('', false);
            $('#recharge_type').selectize()[0].selectize.setValue('', false);

            validate_replacement_number();

            $('#confirm').css({
                'display': 'block'
            });

            if (status == 3 || status == 4) {

                $('#datatable_7_table_body').html('');

                //var table = $('#datatable_7').DataTable();
                //var rows = table.rows().remove().draw();

                /*var ids = [];

                for (var i = 0; i < detail_list.length; i++) {
                    var item = detail_list[i];
                    var menu_ussd_detail_id = item.menu_ussd_detail_id;
                    ids.push(menu_ussd_detail_id);
                }

                var ids_to_json = JSON.stringify(ids);*/

                //console.log('Items:', ids_to_json);

                //Determinar primeramente si en la lista de opciones hay un monto que es igual.

                var amount_item = [];
                var amount_found = false;
                var amount_multiple = false;
                var amount_near = false;
                var amount_description = '';
                var item_list = [];

                for (var i = 0; i < detail_list.length; i++) {
                    var item = detail_list[i];

                    if (menu_ussd_operator_id == item.menu_ussd_operator_id) {
                        var amount_aux = item.amount;

                        if (amount == amount_aux) {
                            amount_found = true;
                            item['count'] = 1;
                            item_list.push(item);

                            console.log('Montos iguales encontrados.');
                            break;
                        }
                    }
                }

                //console.log('amount_found:', amount_found);
                //console.log('amount :::', amount);

                if (amount_found !== true) {

                    $amount_multiple_max = 0;

                    for (var i = 0; i < detail_list.length; i++) {
                        var item = detail_list[i];

                        if (menu_ussd_operator_id == item.menu_ussd_operator_id) {
                            var amount_aux = item.amount;

                            if (amount % amount_aux == 0) {

                                amount_multiple = true;

                                console.log('Monto multiplo encontrado.', amount_aux);

                                if (amount > amount_aux) {
                                    item['count'] = amount / amount_aux;
                                }

                                item_list = [];
                                item_list.push(item); //dejar que haga hasta el final.
                            }
                        }
                    }

                    for (var i = 0; i < detail_list.length; i++) {
                        var item = detail_list[i];

                        if (menu_ussd_operator_id == item.menu_ussd_operator_id) {
                            var amount_aux = item.amount;

                            if (amount / amount_aux == 2 && amount == (amount_aux * 2)) {

                                amount_multiple = true;

                                item['count'] = 2;
                                item_list = [];
                                item_list.push(item);

                                console.log('Monto dividido encontrado.');
                                break;
                            }
                        }
                    }

                    if (amount_found !== true) {
                        for (var i = 0; i < detail_list.length; i++) {
                            var item = detail_list[i];

                            if (menu_ussd_operator_id == item.menu_ussd_operator_id) {
                                var amount_aux = item.amount;

                                for (var j = 0; j < detail_list.length; j++) {

                                    var item_aux_1 = detail_list[j];
                                    var item_aux_1_amount = item_aux_1.amount;

                                    if (menu_ussd_operator_id == item_aux_1.menu_ussd_operator_id) {

                                        for (var k = 0; k < detail_list.length; k++) {

                                            var item_aux_2 = detail_list[k];
                                            var item_aux_2_amount = item_aux_2.amount;

                                            if (menu_ussd_operator_id == item_aux_2.menu_ussd_operator_id) {

                                                item_list = [];

                                                if (amount == amount_aux) {
                                                    amount_multiple = true;
                                                    console.log('Opción 1: 1 montos es igual.');
                                                } else if ((item_aux_1_amount + item_aux_2_amount) == (amount + 500)) {
                                                    amount_multiple = true;
                                                    console.log('Opción 2: 2 montos sumados superan por 500 el paquete.');
                                                } else if ((item_aux_1_amount + item_aux_2_amount) == (amount + 1000)) {
                                                    amount_multiple = true;
                                                    console.log('Opción 3: 2 montos sumados superan por 1000 el paquete.');
                                                }

                                                if (amount_multiple) {

                                                    console.log('amount', amount);
                                                    console.log('item_aux_1_amount', item_aux_1_amount);
                                                    console.log('item_aux_2_amount', item_aux_2_amount);

                                                    if (item_aux_1_amount == item_aux_2_amount) {
                                                        item_aux_1['count'] = 2;
                                                        item_list.push(item_aux_1);
                                                    } else {
                                                        item_aux_1['count'] = 1;
                                                        item_list.push(item_aux_1);
                                                        item_aux_2['count'] = 1;
                                                        item_list.push(item_aux_2);
                                                    }

                                                    break;
                                                } else {

                                                    console.log('No detectado con 2 montos, probando con 3.');

                                                    //break;

                                                    for (var l = 0; l < detail_list.length; l++) {

                                                        var item_aux_3 = detail_list[l];
                                                        var item_aux_3_amount = item_aux_3.amount;

                                                        if (menu_ussd_operator_id == item_aux_3.menu_ussd_operator_id) {

                                                            if (amount == (item_aux_1_amount + item_aux_2_amount +
                                                                    item_aux_3_amount)) {

                                                                amount_multiple = true;
                                                                item_list = [];

                                                                if (item_aux_1_amount == item_aux_2_amount &&
                                                                    item_aux_1_amount !== item_aux_3_amount) {
                                                                    item_aux_1['count'] = 2;
                                                                    item_list.push(item_aux_1);
                                                                    item_aux_3['count'] = 1;
                                                                    item_list.push(item_aux_3);
                                                                } else if (item_aux_2_amount == item_aux_3_amount &&
                                                                    item_aux_1_amount !== item_aux_3_amount) {
                                                                    item_aux_1['count'] = 1;
                                                                    item_list.push(item_aux_1);
                                                                    item_aux_2['count'] = 2;
                                                                    item_list.push(item_aux_2);
                                                                } else if (item_aux_1_amount == item_aux_2_amount &&
                                                                    item_aux_2_amount == item_aux_3_amount) {
                                                                    item_aux_1['count'] = 3;
                                                                    item_list.push(item_aux_2);
                                                                }

                                                                console.log('La convinación de 3 fué encontrada.');

                                                                break;
                                                            }
                                                        }

                                                        if (item_list !== []) {
                                                            break;
                                                        }
                                                    }
                                                }
                                            }

                                            if (item_list !== []) {
                                                break;
                                            }
                                        }
                                    }

                                    if (item_list !== []) {
                                        break;
                                    }
                                }

                                if (item_list !== []) {
                                    break;
                                }
                            }
                        }
                    }

                    //console.log('amount_found:', amount_found);
                }

                if (amount_multiple !== true) {
                    for (var i = 0; i < detail_list.length; i++) {
                        var item = detail_list[i];

                        if (menu_ussd_operator_id == item.menu_ussd_operator_id) {
                            var amount_aux = item.amount;

                            if (amount_aux == (amount + 500) || amount_aux == (amount + 1000)) {
                                amount_near = true;
                                item_list = [];
                                item_list.push(item);

                                console.log('Monto original:', amount);
                                console.log('Monto cercano:', amount_aux);
                                break;
                            }
                        }
                    }
                }

                if (item_list !== []) {

                    for (var i = 0; i < item_list.length; i++) {
                        var item = item_list[i];
                        add_amount_item(item);
                    }

                    /*if (amount_found) {

                    } else if (amount_multiple) {

                    } else if (amount_near) {
                        count = 1;
                        amount_description = 'El monto de saldo supera por 500 o 1000, debido a que no hay paquetes para realizar la operación.';
                    }*/

                    console.log('item_list:', item_list);

                    /*var count = 0;
                    var amount_aux = amount_item.amount;

                    if (amount_found) {
                        count = 1;
                        amount_description = 'El monto de saldo fué encontrado.';

                    } else if (amount_multiple) {
                        if (amount > amount_aux) {
                            count = amount / amount_aux;
                        } else if (amount_aux > amount) {
                            count = amount / amount_aux;
                        }

                        amount_description = 'El monto de saldo es múltiplo: (' + count + ' por ' + amount_aux + ' = ' + amount + ')';
                    } else if (amount_near) {
                        count = 1;
                        amount_description = 'El monto de saldo supera por 500 o 1000, debido a que no hay paquetes para realizar la operación.';
                    }

                    var parameters = {
                        'menu_ussd_detail_id': amount_item.menu_ussd_detail_id,
                        'operator': amount_item.menu_ussd_operator,
                        'option': amount_item.option,
                        'amount': amount,
                        'amount_aux': amount_item.amount,
                        'amount_description': amount_description,
                        'count': count
                    }

                    console.log('parameters:', parameters);

                    add_amount_item(parameters);*/

                    //get_value_amount(ids, amount);
                } else {
                    $('#confirm').css({
                        'display': 'none'
                    });
                }


                $('.nav-tabs a[href="#tab_2"]').css({
                    'display': 'block'
                });

                $('.nav-tabs a[href="#tab_form"]').tab('show');

                $('.nav-tabs a[href="#tab_list"]').css({
                    'display': 'none'
                });

            } else {
                $('.nav-tabs a[href="#tab_2"]').css({
                    'display': 'none'
                });
            }

            $('#datatable_2_table_body').html('');

            $('#datatable_2_table_body').append(
                $('<tr>').append(
                    $('<td>').append('Mensaje de operadora:')
                ).append(
                    $('<td>').append(final_transaction_message)
                )
            ).append(
                $('<tr>').append(
                    $('<td>').append('Ejecución:')
                ).append(
                    $('<td>').append(updated_at)
                )
            ).append(
                $('<tr>').append(
                    $('<td>').append('Fallas:')
                ).append(
                    $('<td>').append(wrong_run_counter)
                )
            );

            $('.nav-tabs a[href="#tab_1"]').tab('show');
            $('#modal').modal('show');
        }

        var column_count = $("#datatable_1").find("tr:first th").length;
        var groupColumn = column_count - 2; //Estado y Opciones restan 2

        //console.log('column_count:', column_count);
        //console.log('groupColumn:', groupColumn);

        //Datatable config
        data_table_config = {
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

            //Agrupador
            "columnDefs": [{
                "visible": false,
                "targets": groupColumn
            }],
            "order": [
                [groupColumn, 'asc']
            ],
            "displayLength": 25,
            "drawCallback": function(settings) {
                var api = this.api();
                var rows = api.rows({
                    page: 'current'
                }).nodes();
                var last = null;

                api.column(groupColumn, {
                    page: 'current'
                }).data().each(function(group, i) {
                    if (last !== group) {

                        var status = group;
                        var color = '#ddd';

                        if (status == 'Fallida') {
                            color = '#dd4b39';
                        } else if (status == 'Pendiente') {
                            color = '#00c0ef';
                        } else if (status == 'Exitosa') {
                            color = '#00a65a';
                        } else if (status == 'Desconocido') {
                            color = '#008080';
                        } else if (status == 'Relanzada') {
                            color = '#0073b7';
                        } else if (status == 'Anulado') {
                            color = '#ff851b';
                        }

                        var td = $('<td>');
                        td.attr({
                            'colspan': (groupColumn + 1).toString(),
                            'style': 'color: white !important'
                        }).append(status);

                        var tr = $('<tr>');
                        tr.attr({
                            'class': 'group',
                            'style': 'background-color:' + color + ' !important; cursor: pointer'
                        }).append(td);

                        $(rows).eq(i).before(tr);

                        last = group;
                    }
                });
            },
            processing: true,
            initComplete: function(settings, json) {
                $('#content').css('display', 'block');
                $('#div_load').css('display', 'none');
                $('body > div.wrapper > header > nav > a').trigger('click');
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

        data_table_config = {
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
            displayLength: 10,
            processing: true,
            initComplete: function(settings, json) {
                $('#content').css('display', 'block');
                $('#div_load').css('display', 'none');
            }
        }

        $('#datatable_3').DataTable(data_table_config);
        $('#datatable_4').DataTable(data_table_config);
        $('#datatable_5').DataTable(data_table_config);
        $('#datatable_6').DataTable(data_table_config);
        //$('#datatable_7').DataTable(data_table_config);


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

        //Número de reemplazo
        $("#replacement_number").keydown(function(event) {
            if (event.which == 69) {
                return false;
            }
        });

        var regex = /^\(?([0][9][6-9][1-9])([0-9]){6}$/;

        $("#replacement_number").keyup(function(event) {
            validate_replacement_number();
        });



        //Evento click en cancel
        $("#cancel").click(function() {
            $("#modal").modal('hide');
        });

        //Esconder la alerta después de 5 segundos. 
        $(".alert").delay(5000).slideUp(300);

        $('[data-toggle="popover"]').popover();

        //----------------------------------------------------

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

        $('#operator_id').selectize(selective_config)[0].selectize.addOption({!! $data['lists']['operators'] !!});
        $('#service_id').selectize(selective_config)[0].selectize.addOption({!! $data['lists']['services'] !!});
        $('#status_id').selectize(selective_config)[0].selectize.addOption({!! $data['lists']['states'] !!});
        $('#branch_id').selectize(selective_config)[0].selectize.addOption({!! $data['lists']['branches'] !!});
        $('#option_id').selectize(selective_config)[0].selectize.addOption({!! $data['lists']['options'] !!});
        $('#atm_id').selectize(selective_config)[0].selectize.addOption({!! $data['lists']['atms'] !!});
        $('#channel_id').selectize(selective_config)[0].selectize.addOption({!! $data['lists']['channels'] !!});
        $('#record_limit').selectize(selective_config)[0].selectize.addOption({!! $data['lists']['record_limits'] !!});
        $('#reason').selectize(selective_config)[0].selectize.addOption({!! $data['lists']['reasons'] !!});
        $('#pos_id').selectize(selective_config)[0].selectize.addOption({!! $data['lists']['points_of_sale'] !!});
        $('#final_transaction_message_id').selectize(selective_config)[0].selectize.addOption({!! $data['lists']['final_transaction_messages'] !!});
        $('#send').selectize(selective_config)[0].selectize.addOption({!! $data['lists']['sends'] !!});
        $('#transaction_status_id').selectize(selective_config)[0].selectize.addOption({!! $data['lists']['transaction_status'] !!});
        $('#historic').selectize(selective_config)[0].selectize.addOption({!! $data['lists']['historics'] !!});


        var selective_config = {
            delimiter: ',',
            persist: false,
            openOnFocus: true,
            valueField: 'id',
            labelField: 'description',
            searchField: 'description',
            maxItems: 1,
            options: {!! $data['lists']['recharge_types'] !!},
            onChange: function(value) {
                //2: Carga normal de saldo, desplegar lista de saldos normales

                if (value == 2) {
                    $('.nav-tabs a[href="#tab_list"]').css({
                        'display': 'block'
                    });
                } else {
                    $('.nav-tabs a[href="#tab_list"]').css({
                        'display': 'none'
                    });
                }
            }
        };

        $('#recharge_type').selectize(selective_config);

        /*$('#recharge_type').selectize(selective_config)[0].selectize.addOption({!! $data['lists']['recharge_types'] !!});

        console.log($('#recharge_type').selectize(selective_config)[0].selectize);

        $('#recharge_type').selectize({
            onChange: function(value) {
               alert('añlsdfjñalskjdf');
            }
        });*/

        //Asignar nuevamente el valor al campo de búsqueda

        var inputs = {!! $data['filters'] !!};

        if (inputs !== null) {
            $("#timestamp").val(inputs.timestamp);
            $("#phone_number").val(inputs.phone_number);
            $('#transaction_id').val(inputs.transaction_id);

            $('#operator_id').selectize()[0].selectize.setValue(inputs.operator_id, false);
            $('#service_id').selectize()[0].selectize.setValue(inputs.service_id, false);
            $('#status_id').selectize()[0].selectize.setValue(inputs.status_id, false);
            $('#branch_id').selectize()[0].selectize.setValue(inputs.branch_id, false);
            $('#option_id').selectize()[0].selectize.setValue(inputs.option_id, false);
            $('#atm_id').selectize()[0].selectize.setValue(inputs.atm_id, false);
            $('#channel_id').selectize()[0].selectize.setValue(inputs.channel_id, false);
            $('#record_limit').selectize()[0].selectize.setValue(inputs.record_limit, false);
            $('#pos_id').selectize()[0].selectize.setValue(inputs.pos_id, false);
            $('#final_transaction_message_id').selectize()[0].selectize.setValue(inputs.final_transaction_message_id,
                false);
            $('#send').selectize()[0].selectize.setValue(inputs.send, false);
            $('#transaction_status_id').selectize()[0].selectize.setValue(inputs.transaction_status_id, false);
            $('#historic').selectize()[0].selectize.setValue(inputs.historic, false);
        }

        $("#search").click(function() {
            //$('#search').submit();
            $('#form_search').append('<input type="hidden" name="button_name" value="search" />');
            $('#form_search').submit();
            $('#content').css('display', 'none');
            $('#div_load').css('display', 'block');
        });

        $("#generate_x").click(function() {
            $('#form_search').append('<input type="hidden" name="button_name" value="generate_x" />');
            $('#form_search').submit();
        });

        $("#clean").click(function() {
            $("#phone_number").val(null);
            $('#transaction_id').val(null);

            $('#operator_id').selectize()[0].selectize.setValue(null, false);
            $('#service_id').selectize()[0].selectize.setValue(null, false);
            $('#status_id').selectize()[0].selectize.setValue(null, false);
            $('#branch_id').selectize()[0].selectize.setValue(null, false);
            $('#option_id').selectize()[0].selectize.setValue(null, false);
            $('#atm_id').selectize()[0].selectize.setValue(null, false);
            $('#channel_id').selectize()[0].selectize.setValue(null, false);
            $('#record_limit').selectize()[0].selectize.setValue(null, false);
            $('#reason').selectize()[0].selectize.setValue(null, false);
            $('#recharge_type').selectize()[0].selectize.setValue(null, false);
            $('#pos_id').selectize()[0].selectize.setValue(null, false);
            $('#final_transaction_message_id').selectize()[0].selectize.setValue(null, false);
            $('#send').selectize()[0].selectize.setValue(null, false);
            $('#transaction_id').selectize()[0].selectize.setValue(null, false);
            $('#transaction_status_id').selectize()[0].selectize.setValue(null, false);
            $('#historic').selectize()[0].selectize.setValue(null, false);
        });

        detail_list = {!! $data['lists']['menu_ussd_detail_list'] !!};

        //console.log('Lista:', detail_list);

        var hide_show_columns = [
            'Estado',
            'Transacción',
            'Punto de venta',
            'Canal',
            'Operador',
            'Servicio',
            'Opción',
            'Monto',
            'Teléfono',
            'Entrada',
            'Actualización',
            'Enviado',
            'Relanzamientos'
        ];

        $('#hide_show_columns').append('1) Ocultar columna/s: &nbsp;');

        for (var i = 0; i < hide_show_columns.length; i++) {
            $('#hide_show_columns').append(
                '<a class="toggle-vis" data-column="' + i + '" style="cursor: pointer">' + hide_show_columns[i] +
                '</a> &nbsp;'
            );
        }

        $('#hide_show_columns').append(
            '<br/> <br/> 2) <a id="hide_show_columns_button" style="cursor: pointer">Mostrar / Ocultar todas las columnas</a>'
        );

        $('#hide_show_columns_button').on('click', function(e) {
            var list = $('#hide_show_columns').find('a.toggle-vis');

            for (var i = 0; i < list.length; i++) {
                var column = table.column($(list[i]).attr('data-column'));
                column.visible(!column.visible());
            }
        });

        $('a.toggle-vis').on('click', function(e) {
            e.preventDefault();

            // Get the column API object
            var column = table.column($(this).attr('data-column'));

            // Toggle the visibility
            column.visible(!column.visible());
        });


        var parameters = {
            'operation': 1,
            'transaction_id': 1,
            'phone_number': '0981123456',
            'final_transaction_message': 'Tu hermana',
            'created_at': '2021-12-27 10:09',
            'updated_at': null,
            'status_id': 3,
            'wrong_run_counter': 3,
            'menu_ussd_operator_id': 1,
            'sub_option': 'Tu hermana en tanga',
            'amount': 6000
        };

        //click_in_view_and_edit(parameters);
    </script>
@endsection
