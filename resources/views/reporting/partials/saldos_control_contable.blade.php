<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-header with-border">                    
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>                        
                    </div>
                    <form action="{{route('saldos.contable.search')}}" method="GET">
                        <div class="row">
                            <div class="col-md-8">
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
                            </div>
                        </div>
                        <div class="row">                                    
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-block btn-primary" name="search" value="search">BUSCAR</button>
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-block btn-success" name="download" value="download">EXPORTAR</button>
                            </div>
                        </div>                      
                    </form>
                </div>                                                                    
            </div>
        </div>
    </div>
    @if(isset($saldos))
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Resultados</h3>                    
                </div>
                <!-- /.box-header -->
                <div class="box-body  no-padding" style="overflow: scroll">
                    <div class="row">
                        <div class="col-xs-12">
                            <table class="table table-striped">
                                <tbody>
                                <thead>
                                <tr>                                   
                                    <th align="center">ATM</th>
                                    <th align="center">Cassettes</th>
                                    <th align="center">Hoppers</th>
                                    <th align="center">Box</th>
                                    <th align="center">Purga</th>
                                    <th align="center">Total</th>
                                    <th align="center">Hora Consulta</th>                                    
                                    <th align="center">Fecha</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($saldos as $saldo)
                                    <tr>
                                        <td>{{$saldo->deposit_code.' - '.$saldo->description}}</td>
                                        <td align="right">{{number_format($saldo->cassette,0,',','.')}}</td>
                                        <td align="right">{{number_format($saldo->hopper,0,',','.')}}</td>
                                        <td align="right">{{number_format($saldo->box,0,',','.')}}</td>
                                        <td align="right">{{number_format($saldo->purga,0,',','.')}}</td>
                                        <td align="right">{{number_format($saldo->total,0,',','.')}}</td>
                                        <td align="right">{{Carbon\Carbon::parse($saldo->created_at)->format('H:i')}}</td>
                                        <td align="right">{{Carbon\Carbon::parse($saldo->created_at)->format('d/m/Y')}}</td>
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
                            <div class="dataTables_info" role="status" aria-live="polite">{{ $saldos->total() }} registros en total</div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-7">
                            <div class="dataTables_paginate paging_simple_numbers">
                                {!! $saldos->appends(['reservationtime' => $reservationtime])->render() !!}
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