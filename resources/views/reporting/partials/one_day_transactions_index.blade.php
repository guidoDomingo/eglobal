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
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
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
                <form action="{{route('reports.one_day_transactions.search')}}" method="GET">
                    <div class="box-body" style="display: block;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('groups', 'Grupos') !!}
                                    {!! Form::select('group_id', $groups, $group_id , ['id' => 'group_id','class' => 'form-control select2']) !!}
                                </div>

                                <div class="form-group">
                                    {!! Form::label('redes', 'Redes') !!}
                                    {!! Form::select('owner_id', $owners, $owner_id , ['id' => 'owner_id','class' => 'form-control select2']) !!}
                                </div>
                                <div class="form-group">
                                    {!! Form::label('tipoAtm', 'Canal') !!}
                                    {!! Form::select('type', $type, $type_set, ['class' => 'form-control select2']) !!}
                                </div>
                                <!-- /.form-group -->
                                <div class="form-group">
                                    {!! Form::label('sucursales', 'Sucursales') !!}
                                    {!! Form::select('branch_id', $branches,  $branch_id , ['id' => 'branch_id','class' => 'form-control select2']) !!}
                                </div>
                                <!-- /.form-group -->
                                <div class="form-group">
                                    {!! Form::label('pdv', 'Puntos de venta') !!}
                                    {!! Form::select('pos_id', $pos, $pos_id, ['id' => 'pos_id','class' => 'form-control select2']) !!}
                                </div>
                                <!-- /.form-group -->
                            </div>
                            <!-- /.col -->
                            <div class="col-md-6">
                                <!-- Date and time range -->
                                <!-- Date and time range -->
                                <div class="form-group">
                                    <label>Fecha:</label>
                                    <div class="input-group">
                                        <div class="input-group-addon">
                                            <i class="fa fa-clock-o"></i>
                                        </div>
                                        <input name="reservationtime" type="text" id="reservationtime" class="form-control pull-right" value="{{old('reservationtime', $reservationtime ?? '')}}" />
                                    </div>
                                    <!-- /.input group -->
                                </div>
                                <!-- /.form group -->
                                <br>
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
                        <!-- /.row -->
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    {!! Form::label('estados', 'Estados') !!}
                                    {!! Form::select('status_id', $status, $status_set, ['class' => 'form-control select2']) !!}
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>Tipo de transacción</label>
                                    {!! Form::select('service_id', $services_data, $service_id, ['class' => 'form-control select2', 'id' => 'serviceId']) !!}
                                </div>
                            </div>

                            <div class="col-md-3 mostrar">
                                <div class="form-group">
                                    <label>Serivicio</label>
                                    {!! Form::select('service_request_id', [], $service_request_id, ['class' => 'form-control select2', 'id' => 'servicioRequestId']) !!}
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
                        <div class="box-tools">
                            <div class="input-group" style="width:150px;">
                                {!! Form::model(Request::only(['context']),['route' => 'reports.one_day_transactions.search', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
                                {!! Form::text('context' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Buscar', 'autocomplete' => 'off' ]) !!}
                                {!! Form::close()!!}
                            </div>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body  no-padding" style="overflow: scroll">
                        <div class="row">
                            <div class="col-xs-12">
                                <table class="table table-striped">
                                    <tbody>
                                    <thead>
                                    <tr>
                                        <th style="width:10px">#ID</th>
                                        <th></th>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                        <th>Fecha</th>
                                        <th>Valor Transacción</th>
                                        <th style="min-width:100px;">Cód. Pago</th>
                                        <!--<th>Valor Pago</th>
                                        <th>Valor Ingresado</th>
                                        <th>Vuelto</th>-->
                                        <th>Identificador de transacción</th>
                                        <th>Factura nro</th>
                                        <th>Sede</th>
                                        <th>Ref 1</th>
                                        <th>Ref 2</th>
                                        <th>Codigo Cajero</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($transactions as $transaction)
                                        <tr  data-id="{{ $transaction->id  }}" data-payid="{{ $transaction->cod_pago  }}" data-status="{{$transaction->status_description}}"  data-transaction="{{ $transaction->atm_transaction_id }}">
                                            <td align="right" class="{{$transaction->id}}">
                                                {{ $transaction->id }}
                                                @if($transaction->reprinted <> true && \Sentinel::getUser()->hasAccess('reporting.print'))
                                                    <i class="print fa fa-print"></i>
                                                @endif
                                            </td>
                                            <td>
                                                <i class="info fa fa-info-circle" style="cursor:pointer"></i>
                                            </td>
                                            <td>{{ $transaction->provider }} - {{ $transaction->servicio }}</td>
                                            <td class="status" style="cursor:pointer">{!! $transaction->status !!} </td>
                                            <td>{{ Carbon\Carbon::parse($transaction->created_at)->format('d/m/Y H:i:s') }}</td>
                                            @if($transaction->forma_pago == 'efectivo')
                                                <td align="right">{{ number_format($transaction->amount,0) }} <i class="fa fa-money"></i> </td>
                                            @elseif($transaction->forma_pago == 'canje')
                                                <td align="right">{{ number_format($transaction->amount,0) }} <i class="fa fa-tags"></i></td>
                                            @else
                                                <td align="right"> {{ number_format($transaction->amount,0) }} | {{$transaction->forma_pago}}</td>
                                            @endif
                                            @if($transaction->cod_pago == '')
                                                <td align="right" style="color: red"><i class="pay-info fa fa-warning" ></i></td>
                                            @else()
                                                <td align="right">{{ $transaction->cod_pago  }} <i class="pay-info fa fa-eye" style="cursor:pointer"></i></td>
                                            @endif
                                            <!--<td>{{ number_format($transaction->valor_a_pagar,0) }}</td>
                                            <td>{{ number_format($transaction->valor_recibido,0) }}</td>
                                            <td>{{ number_format($transaction->valor_entregado,0) }}</td> -->
                                            <td align="right">{{ $transaction->identificador_transaction_id}}</td>
                                            <td align="right">{{ $transaction->factura_numero}}</td>
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
                            <div class="col-sm-5">
                                <div class="dataTables_info" role="status" aria-live="polite">{{ $transactions->total() }} registros en total</div>
                            </div>
                            <div class="col-sm-6">
                                @foreach($total_transactions as $total_transaction)
                                <div class="dataTables_info" role="status" aria-live="polite">Monto total: <b>{{ number_format($total_transaction->monto, 0) }}</b> <i class="fa fa-money"></i> </td></div>
                                @endforeach
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-7">
                                <div class="dataTables_paginate paging_simple_numbers">
                                    {!! $transactions->appends(['type' => $type_set, 'group_id' => $group_id, 'owner_id' => $owner_id, 'branch_id' => $branch_id, 'pos_id' => $pos_id, 'status_id' => $status_set, 'service_id' => $service_id, 'reservationtime' => $reservationtime, 'service_request_id' => $service_request_id])->render() !!}
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
    <link href="/bower_components/admin-lte/plugins/datepicker/datepicker3.css" rel="stylesheet" type="text/css" />
    <script src="/bower_components/admin-lte/plugins/daterangepicker/moment.min.js"></script>

    <!-- bootstrap datepicker -->
    <script src="/bower_components/admin-lte/plugins/datepicker/bootstrap-datepicker.js"></script>
    <script src="/bower_components/admin-lte/plugins/datepicker/locales/bootstrap-datepicker.es.js" charset="UTF-8"></script>

    <script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>
    <script>
        $('.select2').select2();
        var servicioSeleccionado = '{{ $service_request_id }}';

        $('.mostrar').hide();
        //Cascading dropdown list de redes / sucursales

        $('.status').on('click',function(e){
            e.preventDefault();
            var row = $(this).parents('tr');
            var status_description = row.data('status');
            var transaction_id = row.data('transaction');
            $(".idTransaccion").html(transaction_id);
            $("#status_description").html(status_description);
            $("#status_description").show();
            $("#detalles").hide();
            $("#payment_details").hide();
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
                $("#myModal").modal();
            });
        });

        $('.pay-info').on('click',function(e){
            e.preventDefault();
            var row = $(this).parents('tr');
            var payid = row.data('payid');
            var transaction_id = row.data('transaction');
            $.get('{{ url('reports') }}/info/payments_data/' + payid, function(data) {
                $(".idTransaccion").html(transaction_id);
                $("#status_description").hide();
                $("#detalles").hide();
                $("#modal-contenido-payments").html(data);
                $("#payment_details").show();
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

        $('#group_id').on('change', function(e){
            var group_id = e.target.value;            
            $.get('{{ url('reports') }}/ddl/owners/' + group_id, function(owners) {
                $('#owner_id').empty();
                $.each(owners, function(i,item){
                    $('#owner_id').append($('<option>', {
                        value: i,
                        text : item
                    }));
                });
            });
            
            $.get('{{ url('reports') }}/ddl/branches/' + group_id, function(branches) {
                $('#branch_id').empty();
                $.each(branches, function(i,item){
                    $('#branch_id').append($('<option>', {
                        value: i,
                        text : item
                    }));
                });
            });
        });

        $('#owner_id').on('change', function(e){
            var group_id = $( "#group_id" ).val();
            var owner_id = e.target.value;
            $.get('{{ url('reports') }}/ddl/branches/' + group_id + '/' + owner_id, function(branches) {
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
        $('#reservationtime').val();
        if($('#reservationtime').val() == '' || $('#reservationtime').val() == 0){
            var date = new Date();
            var init = new Date(date.getFullYear(), date.getMonth(), date.getDate());
            var today = (init.getDate()) + '/' + (init.getMonth() + 1) + '/' + init.getFullYear();
            $('#reservationtime').val(today);
        }
        //Date range picker
        $('#reservationtime').datepicker({
            language: 'es',
            format: 'dd/mm/yyyy',
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