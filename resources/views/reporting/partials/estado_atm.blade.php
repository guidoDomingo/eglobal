<section class="content">
    <!-- Modal -->
    <div id="modalDetalleNotificaciones" class="modal fade" role="dialog">
        <div class="modal-dialog modal-lg">

            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Detalles - Transacciones : <label class="labelRed"></label></h4>
                </div>
                <div class="modal-body">
                    <form action="{{route('reports.estado_atm.search')}}" method="GET">
                        {!! Form::hidden('reservationtime',$reservationtime, ['id' => 'datepicker']) !!}
                        {!! Form::hidden('atm_id',null, ['id' => 'atm_id']) !!}
                        {!! Form::hidden('status',null, ['id' => 'status']) !!}
                        <div class="row">
                            <div class="col-md-2 pull-right">
                                <button type="submit" class="btn btn-block btn-success pull-right" name="export" value="export">
                                <i class="fa fa-file-excel-o"></i> Exportar</button>
                            </div>
                        </div>
                    </form>
                    <table id="detalles" class="table table-bordered table-hover dataTable" role="grid" aria-describedby="Table1_info">
                        <thead>
                            <tr>
                                <th style="width:10px">#</th>
                                <th>ATM</th>
                                <th>Sucursal</th>
                                <th>Estado</th>
                                <th>Tipo</th>
                                <th>Mensaje</th>
                                <th>Fecha Inicio</th>
                                <th>Fecha Fin</th>
                                <th>Tiempo transcurrido</th>
                                <th>Procesado</th>
                                <th>Asignado a</th>
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
        <div class="col-md-4">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Filtros de búsqueda</h3>

                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
                <!-- /.box-header -->
                <form action="{{route('reports.estado_atm.search')}}" method="GET">
                    <div class="box-body" style="display: block;">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    {!! Form::label('branch', 'Sucursales') !!}
                                    {!! Form::select('branch_id', $branches, $branch_id , ['id' => 'branch_id','class' => 'form-control select2']) !!}
                                </div>
                            </div>
                            <!-- /.col -->
                            <div class="col-md-12">
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
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-block btn-primary" name="search" value="search">BUSCAR</button>
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
        <div class="col-md-8">
            <div class="row">
                @if(isset($atms))
                    @foreach($atms as $sucursal_id => $sucursal)
                        <div class="col-md-12">
                            <div class="box box-default">
                                <div class="box-header with-border">
                                    <h3 class="box-title"><strong>Sucursal #{{ $sucursal_id }} {{ $sucursal['nombre'] }}</strong></h3>
                                </div>
                                <div class="box-body">
                                    @if(isset($sucursal['atms']))
                                        @foreach($sucursal['atms'] as $atm_id => $atm_name)
                                            <div class="col-md-12" style="margin-top: 50px">
                                                <h5><b> N° {{ $atm_id }} {{ $atm_name }} <b/> </h5>
                                                <div class="chartDiv" id="chartdiv{{ $atm_id }}" 
                                                    style="width: 100%; height: 500px; font-size: 12px; margin-top: -80px;">
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</section>

<link type="text/css" href="/dashboard/plugins/amcharts/plugins/export/export.css" rel="stylesheet">

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
    {{-- amcharts plugins --}}
    <script src="/dashboard/plugins/amcharts/amcharts.js"></script>
    <script src="/dashboard/plugins/amcharts/pie.js"></script>
    <script src="/dashboard/plugins/amcharts/serial.js"></script>
    <script src="/dashboard/plugins/amcharts/plugins/export/export.min.js"></script>
    <script src="/dashboard/plugins/amcharts/themes/dark.js"></script>
    <script src="/dashboard/plugins/amcharts/lang/es.js"></script>
    
    <script>
        //Cascading dropdown list de redes / sucursales
        $('.select2').select2();

        $('.overlay').hide();

        @if(isset($datos))
        var datos = '{!! $datos !!}';
        var urlGetDetalle = '{{ route('reports.estado_atm.search') }}';

        function minutes_to_time(minutes) {

            var seconds = minutes * 60;

            var hour = Math.floor(seconds / 3600);
            hour = (hour < 10)? '0' + hour : hour;

            var minute = Math.floor((seconds / 60) % 60);
            minute = (minute < 10)? '0' + minute : minute;

            var second = seconds % 60;
            second = (second < 10)? '0' + second : second;

            return hour + ' horas y ' + minute + ' minutos';
        }

        $.each(JSON.parse(datos), function(index, valor){

            var chart = AmCharts.makeChart("chartdiv"+index, {
                // "language": "es",
                "type": "pie",
                "startDuration": 0,
                "theme": "none",
                "addClassNames": true,
                "legend":{
                    "position":"bottom",
                    "marginRight":500,
                    "autoMargins":false
                },
                "colorField": "color",
                "innerRadius": "20%",
                "defs": {
                    "filter": [{
                        "id": "shadow",
                        "width": "200%",
                        "height": "200%",
                        "feOffset": {
                            "result": "offOut",
                            "in": "SourceAlpha",
                            "dx": 0,
                            "dy": 0
                        },
                        "feGaussianBlur": {
                            "result": "blurOut",
                            "in": "offOut",
                            "stdDeviation": 5
                        },
                        "feBlend": {
                            "in": "SourceGraphic",
                            "in2": "blurOut",
                            "mode": "normal"
                        }
                    }]
                },
                "dataProvider": [
                    {
                        "estado": "Online (" + minutes_to_time(valor['-1']) + ")",
                        "minutos": valor['-1'],
                        "color": "#0A8B19",
                        "atm_id": index,
                    }, 
                    {
                        "estado": "Offline (" + minutes_to_time(valor['3']) + ")",
                        "minutos": valor['3'],
                        "color": "#FDB504",
                        "atm_id": index,
                    }, 
                    {
                        "estado": "Suspendido (" + minutes_to_time(valor['2']) + ")",
                        "minutos": valor['2'],
                        "color": "#FD0404",
                        "atm_id": index,
                    }
                ],
                "valueField": "minutos",
                "titleField": "estado",
                "export": {
                    "enabled": true,
                    "label": "Exportar",
                    /*menu: [
                        {
                            class: "export-main",
                            label: "Export",
                            menu: [
                                {
                                    "label": "Imagen",
                                    "menu": [
                                        { "type": "png", "label": "PNG" },
                                        { "type": "jpg", "label": "JPG" },
                                        { "type": "svg", "label": "SVG" },
                                        { "type": "pdf", "label": "PDF" }
                                    ]
                                    
                                },
                                {
                                    "label": "Data",
                                    "menu": [
                                        { "type": "json", "label": "JSON" },
                                        { "type": "csv", "label": "CSV" },
                                        { "type": "xlsx", "label": "XLSX" }
                                    ]
                                }, 
                                {
                                    "label": "Print", "type": "print"
                                }
                            ]
                        },
                    ]*/
                }
            });

            chart.addListener("clickSlice", handleClick);

            function handleClick(e)
            {
                $('#atm_id').val(e.dataItem.dataContext.atm_id);
                $('#status').val(e.dataItem.dataContext.estado);
                if(e.dataItem.dataContext.estado != "Online"){
                    $.get(urlGetDetalle, 
                    {
                        atm_id: e.dataItem.dataContext.atm_id,
                        status: e.dataItem.dataContext.estado,
                        reservationtime: $('#reservationtime').val(),
                    },
                    function(data) {
                        $("#modal-contenido").html(data.modal_contenido);
                        $("#modal-footer").html(data.modal_footer);
                        $("#modalDetalleNotificaciones").modal('show');
                    });
                }
            }
        });
        @endif

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