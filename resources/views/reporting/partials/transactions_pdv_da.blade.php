<section class="content">
    <div class="row">
        <h3 style="margin-left: 15px">Reporte de transacciones | {{ $pdv }}</h3>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Filtros de búsqueda</h3>

                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                class="fa fa-minus"></i></button>
                    </div>
                </div>
                <!-- /.box-header -->
                <form action="{{ route('reports.pdvda.search', ['id' => $atm_id]) }}" method="GET">
                    <div class="box-body" style="display: block;">
                        <div class="col-md-6">
                            <!-- Date and time range -->
                            <!-- Date and time range -->
                            <div class="form-group">
                                <label>Rango de Tiempo & Fecha:</label>
                                <div class="input-group">
                                    <div class="input-group-addon">
                                        <i class="fa fa-clock-o"></i>
                                    </div>
                                    <input name="reservationtime" type="text" id="reservationtime"
                                        class="form-control pull-right" value="{{ old('reservationtime', $reservationtime ?? '') }}" />
                                </div>
                                <!-- /.input group -->
                            </div>
                            <!-- /.form group -->
                            <br>
                            <div class="row">
                                <div class="col-md-4">
                                    <button type="submit" class="btn btn-block btn-primary" name="search"
                                        value="search">BUSCAR</button>
                                </div>
                                <div class="col-md-4">
                                    @if ($owner_id !== 16 and $owner_id !== 21 and $owner_id !== 25)
                                        <button type="submit" class="btn btn-block btn-success" name="download"
                                            value="download">EXPORTAR</button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
    @if (isset($transactions))
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Resultado</h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body  no-padding" style="overflow: scroll">
                        <div class="row">
                            <div class="col-xs-12">
                                <table class="table table-striped" role="grid">
                                    <tbody>
                                        <thead>
                                            <tr>
                                                <th style="min-width:80px;">#</th>
                                                <th>Tipo</th>
                                                <th>Estado</th>
                                                <th>Fecha</th>
                                                <th>Monto</th>
                                                @if ($owner_id !== 16 and $owner_id !== 21 and $owner_id !== 25)
                                                    <th>Identificador Débito</th>
                                                    <th>Identificador Crédito</th>
                                                @endif
                                                <th>Sede</th>
                                                <th>Ref 1</th>
                                                <th>Ref 2</th>
                                            </tr>
                                        </thead>
                                    <tbody>
                                        @foreach ($transactions as $transaction)
                                            <tr>
                                                <td align="left" class="{{ $transaction->id }}">
                                                    ID: {{ $transaction->id }}
                                                </td>

                                                <td>{{ $transaction->provider }} - {{ $transaction->servicio }}</td>

                                                <td class="status" style="cursor:pointer">
                                                    @if ($transaction->status == 'success')
                                                        <span class="label label-success">{!! $transaction->status !!}</span>
                                                    @else
                                                        <span class="label label-warning">{!! $transaction->status !!}</span>
                                                    @endif
                                                </td>

                                                <td>{{ Carbon\Carbon::parse($transaction->created_at)->format('d/m/Y H:i:s') }}
                                                </td>

                                                <td align="right"> {{ number_format($transaction->amount, 0) }} </td>

                                                @if ($owner_id !== 16 and $owner_id !== 21 and $owner_id !== 25)
                                                    <td align="right">{{ $transaction->factura_numero }}</td>
                                                    <td align="right">
                                                        {{ $transaction->response_bank_transaction_id }}
                                                    </td>
                                                @endif

                                                <td>{{ $transaction->sede }}</td>
                                                <td align="right">{{ $transaction->referencia_numero_1 }}</td>
                                                <td align="right">{{ $transaction->referencia_numero_2 }}</td>
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
                                <div class="dataTables_info" role="status" aria-live="polite">
                                    {{ $transactions->total() }} registros en total</div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-7">
                                <div class="dataTables_paginate paging_simple_numbers">
                                    {!! $transactions->appends([])->render() !!}
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
    <link href="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker-bs3.css" rel="stylesheet"
        type="text/css" />
    <script src="/bower_components/admin-lte/plugins/daterangepicker/moment.min.js"></script>
    <script src="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker.js"></script>

    <!-- bootstrap datepicker -->
    <script src="/bower_components/admin-lte/plugins/datepicker/bootstrap-datepicker.js"></script>
    <script>
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
            ranges: {
                'Hoy': [moment(), moment()],
                'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Ultimos 7 Dias': [moment().subtract(6, 'days'), moment()],
                'Ultimos 30 Dias': [moment().subtract(29, 'days'), moment()],
                'Mes': [moment().startOf('month'), moment().endOf('month')],
                'Mes Pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf(
                    'month')]
            },
            locale: {
                applyLabel: 'Aplicar',
                fromLabel: 'Desde',
                toLabel: 'Hasta',
                customRangeLabel: 'Rango Personalizado',
                daysOfWeek: ['Dom', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sab'],
                monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Setiembre',
                    'Octubre', 'Noviembre', 'Diciembre'
                ],
                firstDay: 1
            },

            format: 'DD/MM/YYYY HH:mm:ss',
            startDate: moment().startOf('month'),
            endDate: moment().endOf('month'),
        });
    </script>
@endsection