<section class="content">
    <!--  Modal Status detalle -->
    <div id="modalStatus" class="modal fade" role="dialog" style="overflow: scroll">
        <div class="modal-dialog modal-lg">
            <!-- Modal content-->
            <div class="modal-content" >
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Estados de las transacciones: {{ $services_data[$service_id] }}<label class="labelRed"></label></h4>
                </div>
                <div class="modal-body" style="overflow:scroll;width:100%;overflow:auto">
                
                    <div class="row">
                        <div class="col-md-2 pull-right">
                            <button type="submit" class="btn btn-block btn-success pull-right" name="export" value="export" onclick="exportTableToExcel('detalles', 'Efectividad')">
                            <i class="fa fa-file-excel-o"></i> Exportar</button>
                        </div>
                    </div>

                    <table id="detalles" class="table table-bordered table-hover dataTable" role="grid" aria-describedby="Table1_info" style="font-size: 14px;">
                        <thead>
                            <tr>
                                <th>Status</th>
                                <th>Servicio</th>
                                <th>Descripción</th>
                                <th>Cantidad</th>
                                <th style="width:150px">Monto</th>
                                <th style="width:150px">Porcentaje</th>
                            </tr>
                        </thead>
                        
                        <tbody id="modal-contenido_status">
                        </tbody>

                    </table>

                    <div class="text-center" id="cargando" style="margin: 50px 10px"> {{-- clase para bloquear el div y mostrar el loading --}}
                        <i class="fa fa-refresh fa-spin" style="font-size:24px"></i>
                    </div>
                    
                </div>
                <div class="modal-footer">
                    <tr>
                        <h3 class="modal-title">Total<h3 class="modal-title" id="modal-footer"></h3><label class="labelRed"></label></h3>
                    </tr>

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
                <form action="{{route('reports.efectividad.search')}}" method="GET">
                    <div class="box-body" style="display: block;">
                        <div class="row">          
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Proveedor</label>
                                    {!! Form::select('service_id', $services_data, $service_id, ['class' => 'form-control select2', 'id' => 'serviceId']) !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="form-group">
                                        {!! Form::label('tipoAtm', 'Canal') !!}
                                        {!! Form::select('type', $type, $type_set, ['class' => 'form-control select2', 'id' => 'type']) !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">          
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Rango de Tiempo & Fecha:</label>
                                    <div class="input-group">
                                        <div class="input-group-addon">
                                            <i class="fa fa-clock-o"></i>
                                        </div>
                                        <input name="reservationtime" type="text" id="reservationtime" class="form-control pull-right" value="{{old('reservationtime', $reservationtime ?? '')}}" />
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('redes', 'Redes') !!}
                                    {!! Form::select('owner_id', $owners, $owner_id , ['id' => 'owner_id','class' => 'form-control select2']) !!}
                                </div>
                            </div>
                            <br>
                            <div class="row">
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-block btn-primary" name="search" value="search">BUSCAR</button>
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
                        {{-- <h3 class="box-title">Resultados</h3> --}}
                        <h3 class="box-title"><strong>Proveedor {{ $services_data[$service_id] }}</strong></h3>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body  no-padding" style="overflow: scroll">                        
                        <div class="row">
                            <div class="col-sm-12">
                                <div id="chartdiv"></div>
                                {{-- <div id="legenddiv"></div> --}}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</section>
<link type="text/css" href="/dashboard/plugins/amcharts/plugins/export/export.css" rel="stylesheet">
<style>
    #chartdiv {
      width : 100%;
      height    : 500px;
    }
   
   /* #legenddiv {
        width: 100px;
        height: 200px;
        border: 1px solid rgb(206, 46, 46);
        margin: 1em 0;
        float: center;

        } */

    body {
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji", "Segoe UI Symbol";
        }

        #chartdiv {
        width: 100%;
        height: 500px;
        font-size: 11px;
        border: 1px solid #eee;
        float: left;
        }

        #legend {
        width: 200px;
        height: 450px;
        border: 1px solid #eee;
        margin-left: 10px;
        float: left;
        }

        #legend .legend-item {
        margin: 10px;
        font-size: 15px;
        font-weight: bold;
        cursor: pointer;
        }

        #legend .legend-item .legend-value {
        font-size: 12px;
        font-weight: normal;
        margin-left: 22px;
        }

        #legend .legend-item .legend-marker {
        display: inline-block;
        width: 12px;
        height: 12px;
        border: 1px solid #ccc;
        margin-right: 10px;
        }

        #legend .legend-item.disabled .legend-marker {
        opacity: 0.5;
        background: #ddd;
        }
        
</style>
@section('js')
    <!-- InputMask -->
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
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
    <script src="/dashboard/plugins/amcharts/themes/light.js"></script>
    <script src="/dashboard/plugins/amcharts/lang/es.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/core.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/charts.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/themes/animated.js"></script>
    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/themes/dataviz.js"></script>
    <script src="https://cdn.amcharts.com/lib/4/themes/animated.js"></script>

    <script type="text/javascript">
        //Cascading dropdown list de redes / sucursales
        $('.select2').select2();
        
        var servicioSeleccionado = '507';
        $('.overlay').hide();

        $('.mostrar').hide();

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

            $(document).on('select2:select','#serviceId',function(){
                var valor = $(this).val();
                var urlGetServices = "{{ route('reports.get_service_request_all') }}";
                valor = JSON.stringify(valor);
                if(valor != '' && valor != 'null'){
                    $.get(urlGetServices, {id: valor}).done(function(data){
                        $('.mostrar').show();
                        servicioSeleccionado = $('#servicioRequestId').val();
                        $('#servicioRequestId').empty().trigger('change');
                        $('#servicioRequestId').select2({data: data});
                        if(servicioSeleccionado != ''){
                            $('#servicioRequestId').val(servicioSeleccionado).trigger('change');
                        }
                        if($("#checkbox2").is(':checked') ){
                            $("#servicioRequestId > option").prop("selected","selected");// Select All Options
                            $("#servicioRequestId").trigger("change");// Trigger change to select 2
                        }
                    });
                }else{
                    $('#servicioRequestId').select2('data', null);
                    $('.mostrar').hide();
                }
            });

            $(document).on('select2:clear select2:unselect','#serviceId',function(e){
                $("#serviceId").trigger("select2:select");
                if (!e.params.originalEvent) {
                    return
                }

                e.params.originalEvent.stopPropagation();
            });

            $(document).on('select2:clear select2:unselect','#servicioRequestId',function(e){
                if (!e.params.originalEvent) {
                    return
                }

                e.params.originalEvent.stopPropagation();
            });

            $("#checkbox").click(function(){
                if($("#checkbox").is(':checked') ){
                    $.when($("#serviceId > option").prop("selected","selected")).done(function(){
                        $("#serviceId").trigger("change");// Trigger change to select 2
                        $("#serviceId").trigger("select2:select");// Trigger change to select 2
                    })
                }else{
                    $.when($("#serviceId > option").removeAttr("selected")).done(function(){
                        $("#serviceId").trigger("change");// Trigger change to select 2
                        $("#serviceId").trigger("select2:select");// Trigger change to select 2
                    })
                }
            });

            $("#checkbox2").click(function(){
                if($("#checkbox2").is(':checked') ){
                    $("#servicioRequestId > option").prop("selected","selected");// Select All Options
                    $("#servicioRequestId").trigger("change");// Trigger change to select 2
                }else{
                    $("#servicioRequestId > option").removeAttr("selected");
                    $("#servicioRequestId").trigger("change");// Trigger change to select 2
                }
            });

            $('#serviceId').trigger('select2:select');

            // $('.info').on('click',function(e){
            //     e.preventDefault();
            //     var row = $(this).parents('tr');
            //     var id = row.data('id');
            //     var transaction_id = row.data('transaction');

            //     var reservationtime = $( "#reservationtime" ).val();
            //     var service_id = $( "#serviceId" ).val();
            //     var status = e.target._dataItem._dataContext.status;
            //     console.log(status);


            //     $.get('{{ url('reports') }}/efectividad/info/details/'+status+reservationtime+service_id, function(data) {
            //         //$(".idTransaccion").html(transaction_id);
            //         $("#modal-contenido").html(data);
            //         //$("#status_description").hide();
            //         //$("#payment_details").hide();
            //         //$("#detalles").show();
            //         $("#statusInfoDetails").modal();
            //     });
            // });

            
            $('.status').on('click',function(e){
                e.preventDefault();
                var row = $(this).parents('tr');
                var status_description = row.data('status');
                $("#status_description").html(status_description);
                $("#status_description").show();
                $("#detalles").hide();
                $("#myModal").modal();
            });


    </script>

    <!-- exploding pie chart -->
    <script>
         
        am4core.ready(function() {
                  
            // Themes begin
            am4core.useTheme(am4themes_dataviz);
            am4core.useTheme(am4themes_animated);            
            // Themes end
            
            var container = am4core.create("chartdiv", am4core.Container);
            container.width = am4core.percent(100);
            container.height = am4core.percent(100);
            container.layout = "horizontal";
                        
            var chart = container.createChild(am4charts.PieChart);
            var items = {!! $item_2 !!};
           // console.log(items);   
           for(var i = 0; i < items.length; i++){
                var item = items[i];
                var status = items[i].status;

                if (status == "canceled"){
                    $.extend( true, items[i], { color: am4core.color("#FFD100")} );//Amarillo
                } else if (status == "iniciated"){
                    $.extend( true, items[i], { color: am4core.color("#000000")} );//Negro
                }else if (status == "error dispositivo"){
                    $.extend( true, items[i], { color: am4core.color("#D02A2A")} );//Rojo claro
                }else if (status == "rollback"){
                    $.extend( true, items[i], { color: am4core.color("#F34706")} );//Naranja
                }else if (status == "error"){
                    $.extend( true, items[i], { color: am4core.color("#FF0000")} );//Rojo
                }else if (status == "success"){
                    $.extend( true, items[i], { color: am4core.color("#19A700")} ); //Verde
                }
            }
            //console.log(items);
            //Add Legend
            chart.legend = new am4charts.Legend();
            console.log(chart.legend);
 
           // Add data
            chart.data = items;

            // Add and configure Series
            var pieSeries = chart.series.push(new am4charts.PieSeries());
            pieSeries.dataFields.value = "cantidad";
            pieSeries.dataFields.category = "status";
            pieSeries.slices.template.propertyFields.fill = "color";// propiedad para colores
            pieSeries.slices.template.states.getKey("active").properties.shiftRadius = 0;
            //pieSeries.labels.template.text = "{category}\n{value.percent.formatNumber('#.#')}%";

            pieSeries.slices.template.events.on("hit", function(event) {
                selectSlice(event.target.dataItem);
            })
            
            var chart2 = container.createChild(am4charts.PieChart);
            chart2.width = am4core.percent(30);
            chart2.radius = am4core.percent(80);
            
            // Add and configure Series
            var pieSeries2 = chart2.series.push(new am4charts.PieSeries());
            pieSeries2.dataFields.value = "value";
            pieSeries2.dataFields.category = "name";
            pieSeries2.slices.template.states.getKey("active").properties.shiftRadius = 0;
            //pieSeries2.labels.template.radius = am4core.percent(50);
            //pieSeries2.labels.template.inside = true;
            //pieSeries2.labels.template.fill = am4core.color("#ffffff");//colores para los nombres
            pieSeries2.slices.template.stroke = am4core.color("#FFFFFF");

            pieSeries2.labels.template.disabled = true;
            pieSeries2.ticks.template.disabled = true;
            pieSeries2.alignLabels = false;
            pieSeries2.events.on("positionchanged", updateLines);
            
            var interfaceColors = new am4core.InterfaceColorSet();
            
            var line1 = container.createChild(am4core.Line);
            line1.strokeDasharray = "2,2";
            line1.strokeOpacity = 0.5;
            line1.stroke = interfaceColors.getFor("alternativeBackground");
            line1.isMeasured = false;
            
            var line2 = container.createChild(am4core.Line);
            line2.strokeDasharray = "2,2";
            line2.strokeOpacity = 0.5;
            line2.stroke = interfaceColors.getFor("alternativeBackground");
            line2.isMeasured = false;
            
            var selectedSlice;
            
            function selectSlice(dataItem) {

                selectedSlice = dataItem.slice;

                var fill = selectedSlice.fill;

                var count = dataItem.dataContext.subData.length;
                pieSeries2.colors.list = [];
                for (var i = 0; i < count; i++) {
                    pieSeries2.colors.list.push(fill.brighten(i * 2 / count));
                }
            
                chart2.data = dataItem.dataContext.subData;
                pieSeries2.appear();
                
                var middleAngle = selectedSlice.middleAngle;
                var firstAngle = pieSeries.slices.getIndex(0).startAngle;
                var animation = pieSeries.animate([{ property: "startAngle", to: firstAngle - middleAngle }, { property: "endAngle", to: firstAngle - middleAngle + 360 }], 600, am4core.ease.sinOut);
                animation.events.on("animationprogress", updateLines);
                
                selectedSlice.events.on("transformed", updateLines);
                
                var animation = chart2.animate({property:"dx", from:-container.pixelWidth / 2, to:0}, 2000, am4core.ease.elasticOut)
                animation.events.on("animationprogress", updateLines)//ANIMATION

            }
            
            function updateLines() {
                if (selectedSlice) {
                    var p11 = { x: selectedSlice.radius * am4core.math.cos(selectedSlice.startAngle), y: selectedSlice.radius * am4core.math.sin(selectedSlice.startAngle) };
                    var p12 = { x: selectedSlice.radius * am4core.math.cos(selectedSlice.startAngle + selectedSlice.arc), y: selectedSlice.radius * am4core.math.sin(selectedSlice.startAngle + selectedSlice.arc) };
                
                    p11 = am4core.utils.spritePointToSvg(p11, selectedSlice);
                    p12 = am4core.utils.spritePointToSvg(p12, selectedSlice);
                
                    var p21 = { x: 0, y: -pieSeries2.pixelRadius };
                    var p22 = { x: 0, y: pieSeries2.pixelRadius };
                
                    p21 = am4core.utils.spritePointToSvg(p21, pieSeries2);
                    p22 = am4core.utils.spritePointToSvg(p22, pieSeries2);
                
                    line1.x1 = p11.x;
                    line1.x2 = p21.x;
                    line1.y1 = p11.y;
                    line1.y2 = p21.y;
                
                    line2.x1 = p12.x;
                    line2.x2 = p22.x;
                    line2.y1 = p12.y;
                    line2.y2 = p22.y;
                }
            }
        
            chart.events.on("datavalidated", function() {
                setTimeout(function() {
                    selectSlice(pieSeries.dataItems.getIndex(0));
                }, 1000);
            });

            //modal grafico general
             pieSeries.slices.template.events.on("hit", function(ev){

                var urlGetDetalle = '{{ route('reports.efectividad.search') }}';
                //console.log("Clicked on ",  '/reports/efectividad_search/'+ev.target._dataItem._dataContext.status+'/'+reservationtime+'/'+service_id);
                $("#modal-contenido_status").html('');
                $("#modal-footer").html('');
                $('#cargando').show();
                $.get(urlGetDetalle, 
                    {
                        status: ev.target._dataItem._dataContext.status,
                        reservationtime: $('#reservationtime').val(),
                        service_id: $('#serviceId').val(),
                        type: $('#type').val(),
                    },
            
                    function(data) {
                        $("#modal-contenido_status").html(data.modal_contenido_status);
                        $("#modal-footer").html(data.modal_footer);
                        $('#cargando').hide();
                        $("#modalStatus").modal('show');
                    }
                 );
                $("#modalStatus").modal('show');
             }, this);
        }); // end am4core.ready()
    </script>
    <script>
        function exportTableToExcel(tableID, filename = ''){
            var downloadLink;
            var dataType = 'application/vnd.ms-excel';
            var tableSelect = document.getElementById(tableID);
            var tableHTML = tableSelect.outerHTML.replace(/ /g, '%20');
            
            // nombre de archivo
            filename = filename?filename+'.xls':'excel_data.xls';
            
            // referencia agregada
            downloadLink = document.createElement("a");
            
            document.body.appendChild(downloadLink);
            
            if(navigator.msSaveOrOpenBlob){
                var blob = new Blob(['\ufeff', tableHTML], {
                    type: dataType
                });
                navigator.msSaveOrOpenBlob( blob, filename);
            }else{
                // link de archivo
                downloadLink.href = 'data:' + dataType + ', ' + tableHTML;
            
                //el nombre archivo a link
                downloadLink.download = filename;
                
                //ejecutando la descarga
                downloadLink.click();
            }
        }
    </script>    
@endsection
@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
    <style type="text/css">
        .select2-selection--multiple{
            overflow: hidden !important;
            height: auto !important;
        }
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