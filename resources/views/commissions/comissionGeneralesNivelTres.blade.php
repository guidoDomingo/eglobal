@extends('layout')

@section('title')
    Comisiones agrupados
@endsection
@section('content')
    <?php
    
    //Variable que se usa en todo el documento
    //listas
    $resultado = $data['lists']['resultado'];
    //dd($resultado);
    $services = $data['lists']['services'];
   
    //$proveedor_services = $data['lists']['proveedor_services'];

    //inputs
    $red = $data['inputs']['red'];
    $id = $data['inputs']['id'];
    $fecha_ = $data['inputs']['fecha'];
    $atm = $data['inputs']['input_atm'];
    $atm_id = $data['inputs']['input_atm_id'];
    $input_descripcion = $data['inputs']['input_descripcion'];
    $input_descripcion = json_decode($input_descripcion,true);
    $input_descripcion_name = $input_descripcion['name'];
    //dd($input_descripcion_name);
    $proveedor_service = $data['inputs']['proveedor_service'];

    //variables
    $total_monto = 0;
    $total_transaccion = 0;
    $total_eglobal = 0;
    $total_punto = 0;
    $total_bruto = 0;
    //dd($resultado);
    
    ?>

    <section class="content-header">

        <div class="row">
            <div class="col-md-12">
                @include('partials._flashes')
            </div>
        </div>

        <div class="box box-default" style="border-radius: 5px;">
            <div class="box-header with-border">
                <h3 class="box-title" style="font-size: 25px;">Comisiones detallado de la red <strong>{{ $red }}</strong> y atm <strong>{{ $atm }}</strong>
                </h3>
                <div class="box-tools pull-right">
                    <button class="btn " type="button" title="Buscar según los filtros en los registros."
                        style="margin-right: 5px; background-color: #285F6C; color: white" id="search" name="search" onclick="search('search')">
                        <span class="fa fa-search btn_excel"> Buscar</span> 
                        <div  class="btn_spinn text-center d-flex align-items-end "> Buscando <i class="fa fa-circle-o-notch fa-spin" style="font-size:10px"></i></div>
                    </button>

                    <button class="btn" type="button" title="Convertir tabla en archivo excel." id="generate_x" style=" background-color: #285F6C; color: white"
                        name="generate_x" onclick="search_excel('generate_x')">
                        <span class="fa fa-file-excel-o btn_excel_exportar"> Exportar</span>  
                        <div  class="btn_spinn_excel text-center d-flex align-items-end "> Exportando <i class="fa fa-circle-o-notch fa-spin" style="font-size:10px"></i></div>
                    </button>
                </div>
            </div>

            <div class="box-body">
                
                <div id="graph_spinn" class="text-center d-flex align-items-end " style="margin: 50px 10px"><i class="fa fa-circle-o-notch fa-spin spinners" style="font-size:24px"></i></div>

                <div id="content" style="display: none">

                    <div class="box box-default" style="border: 1px solid #d2d6de;">
                        <div class="box-header with-border">
                            <h3 class="box-title">Filtrar búsqueda:</h3>
                            <div class="box-tools pull-right">
                                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                        class="fa fa-plus"></i></button>
                            </div>
                        </div>
                        <div class="box-body">
                            
                            {!! Form::open(['route' => 'service_detallado_nivel3', 'method' => 'POST', 'role' => 'form', 'id' => 'form_search', 'autocomplete' => 'off']) !!}
                            <div class="row"> 


                                <div class="col-md-8">

                                    <input type="hidden" name="id" value="{{ $id }}">
                                    <input type="hidden" name="red" value="{{ $red }}">
                                    <input type="hidden" name="atm_name" value="{{ $atm }}">
                                    <input type="hidden" name="atm_id" value="{{ $atm_id }}">
                                    <input type="hidden" name="lista_service" value="{{ json_encode($services) }}">

                                    <div class="form-group d-flex flex-column">
                                        @if (!empty($input_descripcion_name))
                                            <div>
                                                <label>Servicio <strong>{{ $input_descripcion_name }}</strong></label>
                                            </div>
                                        @else
                                            <div>
                                                <label>Buscar por Servicio</label>
                                            </div>
                                        @endif
                                        
                                        <div>
                                            <select class="js-select-status" style="width:50%" name="input_descripcion" id="service_atm_id_" >
                                                    <option>Filtra tu búsqueda</option>
                                                @foreach ( $services as $key => $service )
                                                    <?php 
                                                        $objeto = [
                                                            "service_id" => $service['service_id'],
                                                            "service_source_id" => $service['service_source_id'],
                                                            "name" => $service['name']
                                                        ];
                                                    ?>

                                                    <option value="{{ json_encode($objeto) }}">{{ $service['name'] }}</option>

                                                @endforeach  
                                            </select>
                                        </div>
                                    </div>
                                    
                                </div>
                                 
                              
                                <div class="col-md-4">

                                    <div class="form-group">
                                        <label>Rango de Tiempo & Fecha: </label>
                                        <div class="input-group">
                                            <div class="input-group-addon">
                                                <i class="fa fa-clock-o"></i>
                                            </div>
                                            <input name="reservationtime" type="text" id="reservationtime"
                                            class="form-control pull-right"
                                            placeholder=""
                                            value="{{ $fecha_  or ''}}" />
                                        </div>
                                
                                    </div>
                               
                                </div>

                            </div>   

                            {!! Form::close() !!}
                        </div>
                    </div>

                    <div class="info-box" style="background-color: #285f6c !important;color: white">

                        @foreach ($resultado as $item)    
                            <?php 
                                $total_monto += $item['monto'];
                                $total_transaccion += $item['cantidad_transaccion'];
                                $total_eglobal += $item['comi_neta_eglobal'] == null ? 0 : $item['comi_neta_eglobal'];
                                $total_punto += $item['comi_neta_punto'] == null ? 0 : $item['comi_neta_punto'];
                                $total_bruto += $item['comision_bruta'] == null ? 0 : $item['comision_bruta'];
                            ?>
                        @endforeach            

                        <span class="info-box-icon"><i class="fa fa-bar-chart" aria-hidden="true"></i></span>

                        <div style="display: flex">
                            <div class="info-box-content" style="margin-left: 15px;">
                                <span class="info-box-text">Total monto</span>
                                <span class="info-box-number">{{ number_format($total_monto) }}</span>
                            </div>

                            <span class="info-box-icon"><i class="fa fa-line-chart" aria-hidden="true"></i></span>

                            <div class="info-box-content" style="margin-left: 15px;">
                                <span class="info-box-text">Total comisión bruta </span>
                                <span class="info-box-number">{{ number_format($total_bruto) }}</span>
                            </div>

                            <span class="info-box-icon"><i class="fa fa-arrow-up" aria-hidden="true"></i></span>

                            <div class="info-box-content" style="margin-left: 15px;">
                                <span class="info-box-text">Total para eglobal </span>
                                <span class="info-box-number">{{ number_format($total_eglobal) }}</span>
                            </div>

                            <span class="info-box-icon"><i class="fa fa-arrow-down" aria-hidden="true"></i></span>

                            <div class="info-box-content" style="margin-left: 15px;">
                                <span class="info-box-text">Total para el punto </span>
                                <span class="info-box-number">{{ number_format($total_punto) }}</span>
                            </div>
                        </div>

                    </div>

                    <div class="box box-default collapsed-box" style="border: 1px solid #d2d6de;">
                        <div class="box-header with-border">
                            <h3 class="box-title">Mostrar / Ocultar columnas</h3>
                            <div class="box-tools pull-right">
                                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                        class="fa fa-plus"></i></button>
                            </div>
                        </div>
                        <div class="box-body" id="hide_show_columns">
                        </div>
                    </div>

                      <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_1">
                            <thead>
                                <tr>
                                    <th>Red</th>
                                    <th>Atm</th>
                                    <th>Servicio</th>
                                    <th>Total Transaccion</th>
                                    <th>Monto</th>
                                    <th>Comisión bruta</th>
                                    <th>Comisión neta nivel 3</th>
                                    <th>Comisión neta para el punto nivel 3</th>
                                    <th>Inicio periodo</th>
                                    <th>Fin periodo</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                

                                @foreach ($resultado as $item)
                                
                                    <tr>
                                        <td class="red"  style="border-top: 1px solid #ccc">{{ $item['red'] }}</td>
                                        <td class="red"  style="border-top: 1px solid #ccc">{{ $item['atm'] }}</td>
                                        <td class="red" style="border-top: 1px solid #ccc">{{ $item['service'] }}</td>
                                        <td  style="border-top: 1px solid #ccc">{{ $item['cantidad_transaccion'] }}</td>
                                        <td  style="border-top: 1px solid #ccc">{{ number_format($item['monto']) }}</td>
                                        <td  style="border-top: 1px solid #ccc">{{ number_format($item['comision_bruta']) }}</td>
                                        <td  style="border-top: 1px solid #ccc">{{ number_format($item['comi_neta_eglobal']) }}</td>
                                        <td  style="border-top: 1px solid #ccc">{{ number_format($item['comi_neta_punto']) }}</td>
                                        <td  style="border-top: 1px solid #ccc">{{ date("d/m/Y", strtotime($item['inicio'])) }}</td>
                                        <td  style="border-top: 1px solid #ccc">{{ date("d/m/Y", strtotime($item['fin'])) }}</td>
                            
                                        <td class="ver_detalles desabilitar" style="border-top: 1px solid #ccc; cursor: pointer">
                                            <div class="form-group row justify-content-center">

                                                <div class="col-md-3" style="margin-left:5px">
                                                    {!! Form::open(['route' => 'service_detallado_nivel4', 'method' => 'POST', 'role' => 'form', 'id' => 'form_boton', 'autocomplete' => 'off']) !!}
                                                        <input type="hidden" name="fecha" value="{{ $fecha_ }}">
                                                        <input type="hidden" name="id" value="{{ $id }}">
                                                        <input type="hidden" name="red" value="{{ $red }}">
                                                        <input type="hidden" name="atm_name" value="{{ $atm }}">
                                                        <input type="hidden" name="atm_id" value="{{ $atm_id }}">

                                                        <?php 
                                                            $objeto = [
                                                                "service_id" => $item['service_id'],
                                                                "service_source_id" => $item['service_source_id'],
                                                                "name" => $item['service'],
                                                            ];
                                                        ?>
                                                        <input type="hidden" name="input_descripcion" value="{{ json_encode($objeto) }}">

                                                        <div  class="btn_spinn text-center d-flex align-items-end "><i class="fa fa-circle-o-notch fa-spin spinners" style="font-size:24px"></i></div>

                                                        <button  type="submit" class="btn_spinn_detalle btn btn-secondary" data-toggle="tooltip" data-placement="right" title="Ver detalle">
                                                            <i class="fa fa-sitemap" aria-hidden="true"></i>
                                                        </button>
                                                    {!! Form::close() !!}

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

    <!-- iCheck -->
    <link rel="stylesheet" href="/bower_components/admin-lte/plugins/iCheck/square/grey.css">
    <script src="/bower_components/admin-lte/plugins/iCheck/icheck.min.js"></script>

    <!-- Select2 -->
    <link rel="stylesheet" href="/bower_components/admin-lte/plugins/select2/select2.min.css">

    <!-- Select2 -->
    <script src="/bower_components/admin-lte/plugins/select2/select2.full.min.js"></script>

    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{"https://maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css"}}">
    
    <!-- Iniciar objetos -->
    <script type="text/javascript">
        function search(button_name) {
            $("#graph_spinn").show();
            $("#content").hide();

            $(".btn_excel").hide();
            $(".btn_spinn").show();
            var input = $('<input>').attr({
                'type': 'hidden',
                'id': 'button_name',
                'name': 'button_name',
                'value': button_name
            });

            $('#form_search').append(input);
            $('#form_search').submit();
        }

        function search_excel(button_name) {
             
           
            $(".btn_excel_exportar").hide();
            $(".btn_spinn_excel").show();
            

            var input = $('<input>').attr({
                'type': 'hidden',
                'id': 'button_name',
                'name': 'button_name',
                'value': button_name
            });

            $('#form_search').append(input);
            $('#form_search').submit();

            setTimeout(function(){
                $(".btn_excel_exportar").show();
                $(".btn_spinn_excel").hide();
            },3000);
        }

       
            $(document).ready(function() { // <-- Esto hace que se ejecute el código hasta que se cargó el DOM

                //document.querySelector('#service_atm_id_').value = '{{ $input_descripcion_name }}';
                $(document).ready(function(){
                    $("#service_atm_id_").val('holAAA');
                });
                console.log('{{ $input_descripcion_name }}');

                $('[data-toggle="tooltip"]').tooltip();
                $('.js-select-status').select2(); 

                $(".btn_excel").show();
                $(".btn_spinn").hide();

                $(".btn_excel_exportar").show();
                $(".btn_spinn_excel").hide();
            });

            /***********************************ATMS********************************************************/

            //document.querySelector('#proveedor_service_id').value = '{{ $data['inputs']['proveedor_service'] != "" ? $data['inputs']['proveedor_service'] : "proveedor" }}';

 


            /****************select 2*****************/
            $(document).ready(function() {
                $(".btn_spinn").hide();
                 $('.ver_detalles').on('click',function() { 
                    $(this).find(".btn_spinn").show();
                    $(this).find(".btn_spinn_detalle").hide();
                    $('.desabilitar').addClass("disabledbutton");
                });
            });

            $('#datatable_1').on( 'draw.dt', function () {
                $(".btn_spinn").hide();
                 $('.ver_detalles').on('click',function() { 
                    $(this).find(".btn_spinn").show();
                    $(this).find(".btn_spinn_detalle").hide();
                    $('.desabilitar').addClass("disabledbutton");
                });
            });
            /******************************/
             
         

        //Datatable config
        var data_table_config = {
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
            processing: true,
            initComplete: function(settings, json) {
                $("#graph_spinn").hide();
                $("#content").show();
                //$('body > div.wrapper > header > nav > a').trigger('click');
            }
        }

        // Order by the grouping
        $('#datatable_1 tbody').on('click', 'tr.group', function() {
            var currentOrder = table.order()[0];
            if (currentOrder[0] === groupColumn && currentOrder[1] === 'asc') {
                table.order([groupColumn, 'desc']).draw();
                $(".btn_spinn").hide();
            } else {
                table.order([groupColumn, 'asc']).draw();
                $(".btn_spinn").hide();
            }
        });

        var table = $('#datatable_1').DataTable(data_table_config);

        //$('#hide_show_columns').append('Ocultar columna/s de la tabla: <br/>');

        var hide_show_columns = [];

        var ths = $("#datatable_1").find("th");

        var index = 0;

        for (var i = index; i < ths.length; i++) {
            hide_show_columns.push(ths[i].innerHTML);
        }

        for (var i = index; i < hide_show_columns.length; i++) {

            var description = hide_show_columns[i];

            $('#hide_show_columns').append(
                '<a class="toggle-vis btn btn-default btn-sm" data-column="' + i + '" id="toggle-vis-' + i +
                '" value="' + description + '" state="on" title="Mostrar / Ocultar columna: ' + description +
                '" style="margin-top: 3px">' +
                '<i class="fa fa-eye"></i> &nbsp;' + description +
                '</a> '
            );
        }

        $('a.toggle-vis').on('click', function(e) {
            e.preventDefault();

            // Get the column API object
            var column = table.column($(this).attr('data-column'));

            // Toggle the visibility
            column.visible(!column.visible());
        });

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

       
        //reservation date preset
        $('#reservationtime').val();
        $(document).ready(function(){
            $('.daterangepicker .ranges .range_inputs .cancelBtn').on('click', function(e) {
                var date = new Date();
                var init = new Date(date.getFullYear(), date.getMonth(), date.getDate());
                var end = new Date(date.getFullYear(), date.getMonth(), date.getDate());

                var initWithSlashes = (init.getDate()) + '/' + (init.getMonth() + 1) + '/' + init.getFullYear() + ' 00:00:00';
                var endDayWithSlashes = (end.getDate()) + '/' + (end.getMonth() + 1) + '/' + end.getFullYear() + ' 23:59:59';
                $('#reservationtime').val("");
                $('#reservationtime').attr('placeholder',initWithSlashes + ' - ' + endDayWithSlashes);
            });
        });

        if ($('#reservationtime').val() == '' || $('#reservationtime').val() == 0) {
            var date = new Date();
            var init = new Date(date.getFullYear(), date.getMonth(), date.getDate());
            var end = new Date(date.getFullYear(), date.getMonth(), date.getDate());

            var initWithSlashes = (init.getDate()) + '/' + (init.getMonth() + 1) + '/' + init.getFullYear() + ' 00:00:00';
            var endDayWithSlashes = (end.getDate()) + '/' + (end.getMonth() + 1) + '/' + end.getFullYear() + ' 23:59:59';

           $('#reservationtime').attr('placeholder',initWithSlashes + ' - ' + endDayWithSlashes);
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
            showDropdowns: true,
            dateLimit: {
                    'months': 1,
                    'days': -1,
                },
            minDate: new Date(2000, 1 - 1, 1),
            maxDate:new Date(),
            startDate: moment().startOf('month'),
            endDate: moment().endOf('month')
        });

        $('#reservationtime').attr({
            'onkeydown': 'return false'
        });

        var fechaIncio = $('#reservationtime').val().substr(0, 10);
        var fechaFin = $('#reservationtime').val().substr(22, 10);
        var fecha1 = moment(fechaIncio, "MM-DD-YYYY");
        var fecha2 = moment(fechaFin, "MM-DD-YYYY");
        const diferencia = fecha2.diff(fecha1, 'days');
        var rsultadoDif = Math.round(diferencia / (24));
        
    </script>
@endsection



