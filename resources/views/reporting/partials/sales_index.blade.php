<section class="content">
    <!-- Modal -->
    <div id="myModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Detalles - Transaccion Nro : <label class="idTransaccion"></label></h4>
                </div>
                <div class="modal-body">
                    <table id="detalles" class="table table-bordered table-hover dataTable" role="grid" aria-describedby="Table1_info">                                             <thead>
                        <tr role="row">
                            <th style="display:none;" class="sorting_disabled" rowspan="1" colspan="1"></th>
                            <th style="display:none;" class="sorting_disabled" rowspan="1" colspan="1"></th>
                            <th class="sorting_disabled" rowspan="1" colspan="1">Parte</th>
                            <th class="sorting_disabled" rowspan="1" colspan="1">Tipo</th>
                            <th class="sorting_disabled" rowspan="1" colspan="1">Denominacion</th>
                            <th class="sorting_disabled" rowspan="1" colspan="1">Cantidad</th>
                        </tr>
                        </thead>
                        <tbody id="modal-contenido">

                        </tbody>
                        <tfoot>
                        <tr>
                            <th style="display:none;" rowspan="1" colspan="1"></th>
                            <th style="display:none;" rowspan="1" colspan="1"></th>
                            <th rowspan="1" colspan="1">Parte</th>
                            <th rowspan="1" colspan="1">Tipo</th>
                            <th rowspan="1" colspan="1">Denominacion</th>
                            <th rowspan="1" colspan="1">Cantidad</th>
                        </tr>
                        </tfoot>
                    </table>
                    <div id="status_description"></div>
                    <table id="payment_details" style="display: none;" class="table table-bordered table-hover dataTable" role="grid" aria-describedby="Table2_info">                                             <thead>
                        <tr role="row">
                            <th style="display:none;" class="sorting_disabled" rowspan="1" colspan="1"></th>
                            <th style="display:none;" class="sorting_disabled" rowspan="1" colspan="1"></th>
                            <th class="sorting_disabled" rowspan="1" colspan="1">Valor a pagar</th>
                            <th class="sorting_disabled" rowspan="1" colspan="1">Valor recibido</th>
                            <th class="sorting_disabled" rowspan="1" colspan="1">Valor devuelto</th>
                            <th class="sorting_disabled" rowspan="1" colspan="1">Fecha</th>
                        </tr>
                        </thead>
                        <tbody id="modal-contenido-payments">

                        </tbody>
                        <tfoot>
                        <tr>
                            <th style="display:none;" rowspan="1" colspan="1"></th>
                            <th style="display:none;" rowspan="1" colspan="1"></th>
                            <th rowspan="1" colspan="1">Valor a pagar</th>
                            <th rowspan="1" colspan="1">Valor recibido</th>
                            <th rowspan="1" colspan="1">Valor devuelto</th>
                            <th rowspan="1" colspan="1">Fecha</th>
                        </tr>
                        </tfoot>
                    </table>
                    <div id="devoluciones" style="display: none">
                        <div id="keys_spinn" class="text-center" style="margin: 50px 10px; display: none;"><i class="fa fa-circle-o-notch fa-spin" style="font-size:24px"></i></div>
                        <div id="message_box" class="display: none;"></div>
                        <form role="form" id="devolucion-form" enctype="multipart/form-data">
                        <div class="box-body">
                            <div class="form-group">
                                <label for="txtDescription">Descripción</label>
                                <textarea id="txtDescription" name="txtDescription" class="form-control" rows="3" placeholder="Describa brevemente el caso ..."></textarea>
                                <input type="hidden" id="txttransaction_id">
                            </div>
                            <div class="form-group">
                                <label for="fuComprobante">Adjunte un comprobante</label>
                                <input type="file" id="fuComprobante" name="fuComprobante">

                                <p class="help-block">El archivo debe ser una imagen.</p>
                            </div>
                        </div>
                        <!-- /.box-body -->
                    </form>
                    </div>
                    <div id="reprocesos" style="display: none">
                        <div id="reprocesar-info">
                            <p><b>Servicio  :</b> <span id="service_description"></span></p>
                            <p><b>Monto:</b>      <span id="transaction_amount"></span></p>
                            <p><b>Referencia:</b> <span id="transaction_referece"></span></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <!--para activar modals con formularios para reproceso y devolución respectivamente -->
                    <button type="button" style="display: none" class="reprocesar btn btn-primary pull-left">Reprocesar</button>
                    <button type="buttom" style="display: none" class="devolucion btn btn-primary pull-left">Devolución</button>

                    <!--para ejecutar tareas de reproceso o devolucion -->
                    <button type="buttom" style="display: none" id="process_devolucion" class="btn btn-primary pull-left">Enviar a devolución</button>
                    <button type="button" style="display: none" id="run_reprocesar"class="btn btn-primary pull-left">Enviar a Reprocesar</button>
                    <!--para Cancelar sin hacer nada -->
                    <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cerrar</button>
                </div>
            </div>

        </div>
    </div>
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
                <form action="{{route('reporting.sales.search')}}" method="GET">
                    <div class="box-body" style="display: block;">
                        <div class="row">
                            @if(\Sentinel::getUser()->inRole('supervisor_miniterminal'))
                                <!-- /.col -->
                                <div class="col-md-6">
                                    <!-- Date and time range -->
                                    <div class="form-group">
                                        <label>Rango de Tiempo & Fecha:</label>
                                        <div class="input-group">
                                            <div class="input-group-addon">
                                                <i class="fa fa-clock-o"></i>
                                            </div>
                                            <input name="reservationtime" type="text" id="reservationtime" class="form-control pull-right" value="{{ old('reservationtime', $reservationtime ?? '') }}" />
                                        </div>
                                    </div>
                                    <br>
                            @elseif( !\Sentinel::getUser()->inRole('mini_terminal') && !\Sentinel::getUser()->inRole('supervisor_miniterminal'))
                                <div class="col-md-6">
                                    <div class="form-group">
                                        {!! Form::label('group', 'Grupo Mini Terminal') !!}
                                        {!! Form::select('group_id', $groups, $group_id, ['id' => 'group_id','class' => 'form-control select2', 'placeholder' => 'Seleccione el Grupo']) !!}
                                    </div>
                                    <div class="form-group">
                                        {!! Form::label('mostrar', 'Tipo de Venta') !!}
                                        {!! Form::select('mostrar', ['todos' => 'Todos', 'transacciones' => 'Transacciones', 'paquetigos' => 'Paquetigos', 'personal' => 'Personal', 'claro' => 'Claro', 'cashouts' => 'Pago de Cashouts', 'multa' => 'Multas'], $mostrar, ['id' => 'traer','class' => 'form-control select2']) !!}
                                    </div>
                                </div>
                                <!-- /.col -->
                                <div class="col-md-6">
                                    <!-- Date and time range -->
                                    <div class="form-group">
                                        <label>Rango de Tiempo & Fecha:</label>
                                        <div class="input-group">
                                            <div class="input-group-addon">
                                                <i class="fa fa-clock-o"></i>
                                            </div>
                                            <input name="reservationtime" type="text" id="reservationtime" class="form-control pull-right" value="{{ old('reservationtime', $reservationtime ?? '') }}" />
                                        </div>
                                    </div>
                                    <!-- /.form group -->
                                    <br>
                            @endif                                
                                    <div class="row">
                                        <div class="col-md-4">
                                            <button type="submit" class="btn btn-block btn-primary" name="search" value="search">BUSCAR</button>
                                        </div>
                                        <div class="col-md-4">
                                            <button type="submit" class="btn btn-block btn-success" name="download" value="download">EXPORTAR</button>
                                        </div>
                                    </div>
                                </div>
                        </div>
                    </div>
                    <!-- /.box-body -->
                    <div class="box-footer" style="display: block;">
                    </div>
                </form>
            </div>
        </div>
    </div>
    @if(isset($transactions))
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Resultados</h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body  no-padding" style="overflow: scroll">
                        <div class="row">
                            <div class="col-sm-12">
                                <table class="table table-striped" role="grid">
                                    <tbody>
                                    <thead>
                                    <tr>
                                        <th style="width:10px">#ID</th>
                                        <th>Grupo</th>
                                        <th>Monto</th>
                                        <th>Fecha</th>
                                        <th>ID Ondanet</th>
                                        <th>Nro Venta</th>
                                        <th>Estado</th>
                                        <th>Monto Por Cobrar</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($transactions as $transaction)
                                        <tr>
                                            <td class="{{$transaction->id}}">
                                                {{ $transaction->id }}
                                            </td>
                                            <td>{{ $transaction->description }}</td>
                                            <td>{{ number_format($transaction->amount, 0, '.', ',') }}</td>
                                            <td>{{ Carbon\Carbon::parse($transaction->fecha)->format('d/m/Y') }}</td>
                                            <td>{{ $transaction->destination_operation_id }}</td>
                                            <td>{{ $transaction->nro_venta }}</td>
                                            @if($transaction->estado == 'pendiente')
                                                <td><span class="label label-warning">Pendiente</span></td>
                                            @else
                                                <td><span class="label label-success">Cancelado</span></td>
                                            @endif
                                            <td>{{ number_format($transaction->monto_por_cobrar, 0, '.', ',') }}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                                <div class="row">
                                    <div class="col-sm-6 col-xs-6">
                                        <div class="description-block border-right">
                                            {{-- <span class="description-percentage text-green"><i class="fa fa-caret-up"></i> 17%</span> --}}
                                            <h2 class="description-header">{{ number_format($total_monto, 0, '.', ',') }}</h2>
                                            <span class="description-text">TOTAL VENTAS</span>
                                        </div>
                                    </div>
                                    <div class="col-sm-6 col-xs-6">
                                        <div class="description-block border-right">
                                            {{-- <span class="description-percentage text-green"><i class="fa fa-caret-up"></i> 17%</span> --}}
                                            <h2 class="description-header">{{ number_format($total_monto_por_cobrar, 0, '.', ',') }}</h2>
                                            <span class="description-text">TOTAL MONTO POR COBRAR</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer clearfix">
                        <div class="row">
                            <div class="col-sm-5">
                                <div class="dataTables_info" role="status" aria-live="polite">{{ $transactions->total() }} registros en total</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-7">
                                <div class="dataTables_paginate paging_simple_numbers">
                                    {!! $transactions->appends(['group_id' => $group_id, 'reservationtime' => $reservationtime])->render() !!}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
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
    <script>
        //Cascading dropdown list de redes / sucursales
        $('.select2').select2();

        $('.mostrar').hide();

        $('.status').on('click',function(e){
            //Setea de cero elementos html del modal
            e.preventDefault();
            var row = $(this).parents('tr');
            var status_description = row.data('status');
            var estado = row.data('estado');
            var id = row.data('id');
            var transaction_id = row.data('transaction');
            $(".idTransaccion").html(transaction_id);

            if(estado == 'devolucion'){
                status_description += '</br> <img style="max-width:550px;" src="/comprobantes_devoluciones/'+id+'.jpg"/>';
            }

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
            $('#run_reprocesar').hide();

            $("#myModal").modal();

        });

        $('.info').on('click',function(e){
            e.preventDefault();
            var row = $(this).parents('tr');
            var id = row.data('id');
            var transaction_id = row.data('transaction');
            $.get('{{ url('reports') }}/info/details/' + id, function(data) {
                $(".idTransaccion").html(transaction_id);
                $("#modal-contenido").html(data);
                $("#status_description").hide();
                $("#payment_details").hide();
                $("#detalles").show();
                $('#devoluciones').hide();
                $('#reprocesos').hide();
                $("#myModal").modal();
                //botones
                $('.devolucion').hide();
                $('.reprocesar').hide();
                $('#process_devolucion').hide();
                $('#run_reprocesar').hide();
            });
        });

        $('.pay-info').on('click',function(e){
            e.preventDefault();
            var row = $(this).parents('tr');
            var payid = row.data('payid');
            var transaction_id = row.data('transaction');
            var transaction = row.data('id');
            $.get('{{ url('reports') }}/info/payments_data/' + payid, function(data) {
                $(".idTransaccion").html(transaction_id);
                $('#txttransaction_id').val(transaction);
                $("#status_description").hide();
                $("#detalles").hide();
                $("#modal-contenido-payments").html(data['payment_info']);
                $("#payment_details").show();
                $('#devoluciones').hide();
                $('#reprocesos').hide();
                console.log(data);
                //botones
                if(data['reprocesable'] == true){
                    $('.reprocesar').show();
                }else{
                    $('.reprocesar').hide();
                }

                if(data['devolucion'] == true){
                    $('.devolucion').show();
                }else{
                    $('.devolucion').hide();
                }


                $('#process_devolucion').hide();
                $('#run_reprocesar').hide();

                $("#myModal").modal();
            });
        });

        $('.print').on('click',function(e){
            e.preventDefault();
            var row = $(this).parents('tr');
            var id = row.data('id');
            $("#printSection").html('');
            $.get('{{ url('reports') }}/info/tickets/' + id, function(data) {
                $("#printSection").html(data);
                if(data){
                    window.print();
                    $("#printSection").html('');
                    $tag = '.'+id;
                    $($tag).html(id);
                }
            });
        });

        $('.devolucion').on('click',function(e){
            e.preventDefault();
            $('#detalles').hide();
            $('#payment_details').hide();
            $('#reprocesos').hide();
            $('#devoluciones').show();
            $('#devolucion-form')[0].reset();
            //botones
            $('.devolucion').hide();
            $('.reprocesar').hide();
            $('#run_reprocesar').hide();
            $('#process_devolucion').show();
        });

        $('#process_devolucion').on('click',function(e){
            e.preventDefault();
            $('#keys_spinn').show();
            $('#devolucion-form').hide();
            $('#message_box').html('');
            let form = $('#devolucion-form')[0];
            let data = new FormData(form);
            let transaction_id = $('#txttransaction_id').val();
            console.log(transaction_id);
            data.append("_token", token);
            data.append("_transaction_id", transaction_id);
            $('#process_devolucion').hide();
            $.ajax({
                type: "POST",
                enctype: 'multipart/form-data',
                url: "procesar_devolucion",
                data: data,
                processData: false,
                contentType: false,
                cache: false,
                timeout: 600000,
                success: function (data) {
                    console.log("SUCCESS : ", data);
                    $('#message_box').html(data);
                    $('#message_box').show();
                    $('#keys_spinn').hide();
                    $('#process_devolucion').hide();
                },
                error: function (e) {
                    $('#message_box').html('Lo sentimos, se produjo un error al procesar la devolucion');
                    $('#message_box').show();
                    $('#keys_spinn').hide();
                    $('#process_devolucion').hide();
                    //console.log("ERROR : ", e);
                }
            });

            setTimeout(function(){
                $('#myModal').modal('hide')
                location.reload();
            }, 5000);
        });


        //Reprocesar
        $('.reprocesar').on('click',function(e){
            e.preventDefault();
            let transaction_id = $('#txttransaction_id').val();
            let transaction_amount = $('#txttransaction_amount').val();
            let ref1   = '';//$('#txtref1').val();
            let ref2   = '';//$('#txtref2').val();
            let service_desc = '';//$('#txtreServDescription').val();

            $('#transaction_amount').html(transaction_amount);
            $('#transaction_referece').html(ref1);
            $('#service_description').html(service_desc);

            $('.reprocesar_transaction_id').html(transaction_id);
            $('#detalles').hide();
            $('#payment_details').hide();
            $('#devoluciones').hide();
            $('#reprocesos').show();

            //botones
            $('.devolucion').hide();
            $('.reprocesar').hide();
            $('#process_devolucion').hide();
            $('#run_reprocesar').show();
        });

        $('#run_reprocesar').on('click',function(e){
            e.preventDefault();
            $('#keys_spinn_2').show();
            $('#devolucion-form').hide();
            $('#message_box_2').html('');
            let transaction_id = $('#txttransaction_id').val();
            $.post("reprocesar_transaccion", {_token: token, _transaction_id : transaction_id }, function( data ) {
                if(data.error == false){
                    $('#message_box_2').html('La transacción será reprocesada en apróx. 5 min');
                    $('#message_box_2').show();
                    $('#keys_spinn_2').hide();
                    $('#reprocesar-info').hide();
                    $('#run_reprocesar').hide();
                }else{
                    $('#message_box').html('No se pudo realizar el reproceso');
                    $('#message_box').show();
                    $('#keys_spinn').hide();
                    $('#reprocesar-info').hide();
                    $('#run_reprocesar').hide();
                }
            }).error(function(){
                $('#message_box').html('No se pudo realizar el reproceso');
                $('#message_box').show();
                $('#keys_spinn').hide();
                $('#reprocesar-info').hide();
                $('#run_reprocesar').hide();
            });

            setTimeout(function(){
                $('#myModal').modal('hide')
                location.reload();
            }, 5000);
        });

        $('#owner_id').on('change', function(e){
            var owner_id = e.target.value;
            $.get('{{ url('reports') }}/ddl/branches/' + owner_id, function(branches) {
                $('#branch_id').empty();
                $.each(branches, function(i,item){
                    $('#branch_id').append($('<option>', {
                        value: i,
                        text : item
                    }));
                });
            });
        });

        $('#branch_id').on('change', function(e){
            var branch_id = e.target.value;
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

        //Datemask dd/mm/yyyy
        $("#datemask").inputmask("dd/mm/yyyy", {"placeholder": "dd/mm/yyyy"});
        //Datemask2 mm/dd/yyyy
        $("#datemask2").inputmask("mm/dd/yyyy", {"placeholder": "mm/dd/yyyy"});
        //reservation date preset
        $('#reservationtime').val()
        if($('#reservationtime').val() == '' || $('#reservationtime').val() == 0){
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
            ranges: {
                'Hoy': [moment(), moment()],
                'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Ultimos 7 Dias': [moment().subtract(6, 'days'), moment()],
                'Ultimos 30 Dias': [moment().subtract(29, 'days'), moment()],
                'Mes': [moment().startOf('month'), moment().endOf('month')],
                'Mes Pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            },
            locale: {
                applyLabel: 'Aplicar',
                fromLabel: 'Desde',
                toLabel: 'Hasta',
                customRangeLabel: 'Rango Personalizado',
                daysOfWeek: ['Dom', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie','Sab'],
                monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Setiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                firstDay: 1
            },

            format: 'DD/MM/YYYY HH:mm:ss',
            startDate: moment().startOf('month'),
            endDate: moment().endOf('month'),
        });

        $(document).on('change','#serviceId',function(){
            var valor = this.value;
            var urlGetServices = "{{ route('reports.get_service_request') }}";

            if(valor.search('-') != -1){
                $.get(urlGetServices, {id: valor}).done(function(data){
                    $('.mostrar').show();
                    $('#servicioRequestId').empty().trigger('change');
                    $('#servicioRequestId').select2({data: data});
                    if(servicioSeleccionado != ''){
                        $('#servicioRequestId').val(servicioSeleccionado).trigger('change');
                    }
                });
            }else{
                $('#servicioRequestId').select2('data', null);
                $('.mostrar').hide();
            }
        });

        $('#serviceId').trigger('change');

    </script>
@endsection
@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
    <style type="text/css">
        @media print {
            body * {
                visibility:hidden;

            }
            #printSection, #printSection * {
                visibility:visible;
            }



            #printSection {
                font-size: 11px;
                width: 100%;
                height: 100%;
                margin: 0;
                padding: 0;
                left:0;
                top:0;
            }
        }
    </style>
@endsection