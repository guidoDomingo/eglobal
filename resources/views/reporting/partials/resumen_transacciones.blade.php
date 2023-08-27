<section class="content">
    <!-- Modal -->
    <div id="modalDetalleRedes" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Detalles - Transacciones : <label class="labelRed"></label></h4>
                </div>
                <div class="modal-body">
                    <form action="{{route('reports.resumen.detalle_export')}}" method="GET">
                        {!! Form::hidden('atm_id',$atm_id, ['id' => 'atmId']) !!}
                        {!! Form::hidden('reservationtime',$reservationtime, ['id' => 'datepicker']) !!}
                        {!! Form::hidden('service_source_id',null, ['id' => 'serviceRequestId']) !!}
                        {!! Form::hidden('service_id',null, ['id' => 'serviceId']) !!}
                        {!! Form::hidden('owner_id',$owner_id, ['id' => 'ownerId']) !!}

                        <div class="row">
                            <div class="col-md-12">
                                <button type="submit" class="btn btn-block btn-success pull-right" name="export" value="export">
                                Exportar</button>
                            </div>
                        </div>
                    </form>
                    <table id="detalles" class="table table-bordered table-hover dataTable" role="grid" aria-describedby="Table1_info">
                        <thead>
                            <tr role="row">
                                <th class="sorting_disabled" rowspan="1" colspan="1">Servicio</th>
                                <th class="sorting_disabled" align="right">Valor Transacción</th>
                            </tr>
                        </thead>
                        <tbody id="modal-contenido">

                        </tbody>
                        <tfoot id="modal-footer">
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
                <form action="{{route('reports.resumen.search')}}" method="GET">
                    <div class="box-body" style="display: block;">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('tipoAtm', 'Canal') !!}
                                    {!! Form::select('type', $type, $type_set, ['id' => 'typeSet','class' => 'form-control select2']) !!}
                                </div>
                                <div class="form-group">
                                    {!! Form::label('redes', 'Redes') !!}
                                    {!! Form::select('owner_id', $owners, $owner_id , ['id' => 'owner_id','class' => 'form-control select2',  'style' => 'width:100%']) !!}
                                </div>
                                <div class="form-group">
                                    {!! Form::label('atm', 'ATMs') !!}
                                    {!! Form::select('atm_id', $atms, $atm_id , ['id' => 'atm_id','class' => 'form-control select2']) !!}
                                </div>
                                
                            </div>
                            <!-- /.col -->
                            <div class="col-md-6">
                                <!-- Date and time range -->
                                <!-- Date and time range -->
                                <div class="form-group">
                                    <label>Rango de Tiempo & Fecha:</label>
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
                    </div>
                    <!-- /.box-body -->
                    <div class="box-footer" style="display: block;">
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="row">
        @if(isset($transactionsEglobalt))
        <div class="col-md-6">
            <div class="box box-primary">
                <div class="overlay" id="cargando"> {{-- clase para bloquear el div y mostrar el loading --}}
                    <i class="fa fa-refresh fa-spin"></i>
                </div>
                <div class="box-header with-border">
                    <h3 class="box-title">Resultados Eglobalt</h3>
                    <div class="box-tools">
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
                                    <th>Servicio</th>
                                    <th>Valor Transacción</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($transactionsEglobalt as $transaction)
                                    <tr >
                                        <td align="left" >
                                            <a href="#" class="servicio" data-id="{{ $transaction->service_id }}">{{ $transaction->nombre_servicio }} - {{ $transaction->proveedor }}</a>
                                        </td>
                                        <td align="right">{{ number_format($transaction->amount,0) }} <i class="fa fa-money"></i> </td>
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
                            <div class="dataTables_info" role="status" aria-live="polite">{{ $transactionsEglobalt->total() }} registros en total</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-7">
                            <div class="dataTables_paginate paging_simple_numbers">
                                {!! $transactionsEglobalt->appends(['atm_id' => $atm_id, 'reservationtime' => $reservationtime])->render() !!}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    @if(isset($transactionsProviders))
        <div class="col-md-6">
            <div class="box box-primary">
                <div class="overlay" id="cargandoRed"> {{-- clase para bloquear el div y mostrar el loading --}}
                    <i class="fa fa-refresh fa-spin"></i>
                </div>
                <div class="box-header with-border">
                    <h3 class="box-title">Otras redes</h3>
                    <div class="box-tools">
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body  no-padding" style="overflow: scroll">
                    <div class="row">
                        <div class="col-xs-12">
                            <table class="table table-striped"  width="500">
                                <tbody>
                                <thead>
                                <tr>
                                    <th>Red</th>
                                    <th>Valor Transacción</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($transactionsProviders as $transaction)
                                    <tr >
                                        <td align="left" >
                                            @if($transaction->red == 'Eglobal')
                                                <a href="#" class="red" data-id="{{ $transaction->service_source_id }}">Momo</a>
                                            @else
                                                <a href="#" class="red" data-id="{{ $transaction->service_source_id }}">{{ $transaction->red }}</a>
                                            @endif
                                        </td>
                                        <td align="right" width="250px">{{ number_format($transaction->amount,0) }} <i class="fa fa-money"></i> </td>
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
                             <div class="dataTables_info" role="status" aria-live="polite"> {{ count($transactionsProviders) }} registros en total</div>                              
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-7">
                            <div class="dataTables_paginate paging_simple_numbers">
                                {{-- {!! $transactionsProviders->appends(['atm_id' => $atm_id, 'reservationtime' => $reservationtime])->render() !!} --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
    
    </div>
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

        $('.overlay').hide();

        $('.red').on('click',function(e){
            e.preventDefault();
            var id = $(this).attr('data-id');
            $('#serviceRequestId').val(id);
            $('#serviceId').val(null);
            $('#cargandoRed').show();
            console.log(id);
            $.get('{{ route('reports.resumen.search') }}', 
                {
                    service_source_id: id,
                    reservationtime: $('#reservationtime').val(),
                    atm_id: $('#atm_id').val(),
                    owner_id: $('#owner_id').val(),
                    type: $('#typeSet').val(),


                },
            function(data) {
                $("#modal-contenido").html(data.modal_contenido);
                $("#modal-footer").html(data.modal_footer);
                $('.overlay').hide();
                $("#modalDetalleRedes").modal('show');
            });
        });

        $('.servicio').on('click',function(e){
            e.preventDefault();
            var id = $(this).attr('data-id');
            $('#serviceRequestId').val(null);
            $('#serviceId').val(id);
            $('#cargando').show();

            $.get('{{ route('reports.resumen.search') }}', 
                {
                    service_id: id,
                    reservationtime: $('#reservationtime').val(),
                    atm_id: $('#atm_id').val(),
                    owner_id: $('#owner_id').val(),

                },
            function(data) {
                $("#modal-contenido").html(data.modal_contenido);
                $("#modal-footer").html(data.modal_footer);
                $('#cargando').hide();
                $("#modalDetalleRedes").modal('show');
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