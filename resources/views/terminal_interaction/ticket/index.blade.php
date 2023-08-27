@extends('layout')

@section('title')
    Tickets de Transacciones - Reporte
@endsection

@section('content')
    <section class="content-header">
        <h1>
            Tickets de Transacciones - Reporte
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Tickets de Transacciones - Reporte</a></li>
        </ol>
    </section>

    <section class="content">

        <div class="delay_slide_up">
            @include('partials._flashes')
            @include('partials._messages')
        </div>

        <!-- <div class="row">
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
            </div> -->

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
                        {!! Form::open(['route' => 'ticket_index', 'method' => 'POST', 'role' => 'form', 'id' => 'form_search']) !!}
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="timestamp">Fecha:</label>
                                    <input type="text" class="form-control" style="display:block" id="timestamp"
                                        name="timestamp" ></input>
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
                        <h3 class="box-title">Resumen de totales por estado</h3> &nbsp;

                        <button class="btn btn-primary" title="Ver u ocultar estados con cantidad o monto cero."
                            id="show_hide">
                            <span class="fa fa-eye"></span> &nbsp; Mostrar/Ocultar
                        </button>

                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                    class="fa fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <?php
                            $list = $data['totals_status'];
                            $days = $data['days'];
                            $total_transactions = $data['totals']['total'];
                            $total_transactions_amount = $data['totals']['total_amount'];
                            $total_transactions_amount = number_format($total_transactions_amount, 0, ',', '.');
                            ?>

                            @for ($i = 0; $i < count($list); $i++)

                                <?php
                                $item = $list[$i];
                                
                                $status = $item['status'];
                                $description = $item['description'];
                                $total_status = $item['total'];
                                $total_amount = $item['total_amount'];
                                $total_amount = number_format($total_amount, 0, ',', '.');
                                $percentage = 0;
                                
                                $color = '#d2d6de';
                                
                                if ($status == 'success') {
                                    $color = '#00a65a';
                                } elseif ($status == 'pendiente' or $status == 'procesando') {
                                    $color = '#00c0ef';
                                } elseif ($status == 'iniciated' or $status == 'nulled' or $status == 'reprocesando' or $status == 'canceled' or $status == 'cancelled') {
                                    $color = '#f39c12';
                                } elseif ($status == 'error' or $status == 'rollback' or $status == 'error dispositivo' or $status == 'devolucion' or $status == 'inconsistency') {
                                    $color = '#dd4b39';
                                }
                                
                                $class = '';
                                
                                if ($total_status !== 0 and $total_transactions !== 0) {
                                    $percentage = ($total_status * 100) / $total_transactions;
                                    $percentage = round($percentage, 1);
                                } else {
                                    $class = 'show_hide_div';
                                }
                                
                                ?>

                                <div class="{{ $class }}">
                                    <div class="col-md-3">
                                        <div class="callout callout-default"
                                            style="border: 1px solid {{ $color }}; border-width: 1px 1px 1px 4px">
                                            <h4> {{ $description }}</h4>
                                            <h5><b>{{ $total_status }} transacciones </b> </h5>
                                            <h5> {{ $total_amount }} Gs. en total. </h5>
                                            <h5> Representa el <b>{{ $percentage }}% </b> </h5>
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
                                    <b>{{ $total_transactions }} </b> transacciones.
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
                        @if (isset($data['list']))
                            <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_1">
                                <thead>
                                    <tr role="row">
                                        <th>Proveedor</th>
                                        <th>Servicio</th>
                                        <th>Transacción</th>
                                        <th>Monto</th>

                                        <th>Monto-a-pagar</th>
                                        <th>Monto-ingresado</th>
                                        <th>Vuelto-entregado</th>
                                        <th>Vuelto-no-entregado</th>

                                        <th>Entrada</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $list = $data['list']; ?>

                                    @for ($i = 0; $i < count($list); $i++)

                                        <?php
                                        $item = $list[$i];
                                        $status = $item['status'];
                                        $label_class = 'default';
                                        
                                        if ($status == 'success') {
                                            $label_class = 'success';
                                        } elseif ($status == 'pendiente' or $status == 'procesando') {
                                            $label_class = 'info';
                                        } elseif ($status == 'iniciated' or $status == 'nulled' or $status == 'reprocesando' or $status == 'canceled' or $status == 'cancelled') {
                                            $label_class = 'warning';
                                        } elseif ($status == 'error' or $status == 'rollback' or $status == 'error dispositivo' or $status == 'devolucion' or $status == 'inconsistency') {
                                            $label_class = 'danger';
                                        }
                                        
                                        ?>

                                        <tr>
                                            <td>{{ $item['provider'] }}</td>
                                            <td>{{ $item['service'] }}</td>
                                            <td>{{ $item['transaction_id'] }}</td>
                                            <td>{{ $item['amount'] }}</td>

                                            <td>{{ $item['amount_to_paid'] }}</td>
                                            <td>{{ $item['received_value'] }}</td>
                                            <td>{{ $item['delivered_value'] }}</td>
                                            <td>{{ $item['returned_not_delivered'] }}</td>

                                            <td>{{ $item['created_at'] }}</td>
                                            <td>
                                                <span class="label label-{{ $label_class }}">{{ $item['status'] }}</span>
                                            </td>
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

        <!-- Modal ayuda-->
        <div id="modal_help" class="modal fade" role="dialog" tabindex="-1" data-backdrop="static" data-keyboard="false">
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
                                            estado, el total en guaranies y el porcentaje que representa en el periodo de
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
                                        por el sistema ussd para que se pueda cargar el saldo que no se pudo transferir al
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
                                        Sirve para filtrar la búsqueda ingresando el número de teléfono del cliente. <br />

                                        <b>Buscar:</b>
                                        Haciendo click en el botón buscar ejecuta la acción para traer los registros
                                        según los filtros ingresados por el usuario. <br />

                                        <b>Exportar:</b>
                                        Haciendo click en el botón exportar el sistema va a generar un excel con los filtros
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

                                        <b>Estado:</b> Muestra en que estado se encuentra la transacción, la tabla tiene los
                                        registros agrupados por estado. <br />

                                        <b>Sucursal:</b>
                                        Es la sucursal relacionada a la transacción. <br />

                                        <b>Terminal:</b>
                                        Es la terminal relacionada a la transacción. <br />

                                        <b>Transacción:</b>
                                        Número de transacción. <br />

                                        <b>Servicio:</b>
                                        Es el paquete que contiene las opciones del menú por ejemplo: Paquete de Internet o
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
                                            <li><b> <i class="fa fa-eye"></i> Vista: </b> El botón de vista solo permite ver
                                                más
                                                datos sobre la transacción.</li>

                                            <li><b> <i class="fa fa-pencil"></i> Edición: </b> El botón de edición permite
                                                ver
                                                más
                                                datos sobre la transacción y también editar el registro.</li>
                                            <li><b> <i class="fa fa-rotate-left"></i> Relanzar: </b> El botón de
                                                relanzamiento permite volver a ingresar
                                                la transacción en la cola de transacciones para poder enviar el saldo.</li>
                                        </ul>
                                    </div>

                                    <div class="callout callout-default">
                                        <h5><b>Observación:</b></h5>
                                        <ul>
                                            <li> Las transacciones con estado: <b>Pendiente, exitosa, relanzada, anulado</b>
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

                                                        <b>Número de transacción:</b> Es el número de transacción asignado a
                                                        el
                                                        registro. <br />

                                                        <b>Mensaje de operadora:</b>
                                                        Es el mensaje obtenido de la operadora.
                                                        <br />

                                                        <b>Entrada:</b>
                                                        Es la fecha y hora que la solicitud de carga entró al sistema.
                                                        <br />

                                                        <b>Ejecución:</b>
                                                        Es la fecha y hora en cual el servicio ussd ejecutó la solicitud.
                                                    </div>
                                                </div>

                                                <div class="tab-pane fade" id="tab_form_2">

                                                    <div class="callout callout-default">
                                                        <h5><b>Definición de Campos Editables:</b> </h5>
                                                        Son los campos que están solo para ser editados por el usuario.
                                                    </div>

                                                    <div class="callout callout-default">
                                                        <b>Número de Recarga:</b> Es el número de la carga identificable del
                                                        registro. <br />

                                                        <b>Nuevo número de teléfono:</b> Es el nuevo número comunicado por
                                                        el
                                                        cliente. <br />

                                                        <b>Razón:</b>
                                                        Es la razón por la cual la carga no pudo ser realizada.
                                                        <br />

                                                        <b>Tipo de recarga:</b>
                                                        Es el tipo de recarga que podría elegir el cliente o el usuario.
                                                        Las opciones serian: El paquete seleccionado por el cliente o carga
                                                        normal de saldo.
                                                        <br />

                                                        <b>Comentario:</b>
                                                        En este campo se puede ingresar una descripción más amplia sobre la
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
                                                            <li><b> <i class="fa fa-remove"></i> Cancelar: </b> Este botón
                                                                sirve
                                                                para cancelar la operación y cerrar la ventana del
                                                                formulario.
                                                            </li>

                                                            <li><b> <i class="fa fa-save"></i> Confirmar: </b> Este botón
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

        <style>
            .invisible {}

        </style>
    </section>
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

    <!-- select2 -->
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
    <script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/PrintArea/2.4.1/jquery.PrintArea.min.js"></script>

    <!-- Iniciar objetos -->
    <script type="text/javascript">
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

        var timestamp = "{{ $data['filters']['timestamp'] }}";
        $('#timestamp').val(timestamp);

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
    </script>
@endsection
