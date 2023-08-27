@extends('layout')

@section('title')
    Comisiones de ventas Qr
@endsection
@section('content')
    <?php
    //error_reporting(0);
    //Variable que se usa en todo el documento

    //listas
    //dd($data);
    $data_invoice = $data['lists']['data_invoice'];
    //dd($data_invoice);
    $groups = $data['lists']['group'];
    $meses = $data['lists']['meses'];
    //$fecha = 
    /*
        INPUTS
    */
    $group_input = $data['inputs']['group'];
    $fecha_ = $data['inputs']['fecha'];

    /* meses del año */

    $total_monto = 0;
    $total_transaccion = 0;
    $total_comision_tc = 0;
    $total_comision_td = 0;
    $total_comision_dc = 0;


   
    
    ?>

    <section class="content-header">

        <div class="row">
            <div class="col-md-12">
                @include('partials._flashes')
            </div>
        </div>

        <div class="box box-default" style="border-radius: 5px;">
            <div class="box-header with-border">
                <h3 class="box-title" style="font-size: 25px;">Reporte de comisiones ventas Qr
                </h3>
                <div class="box-tools pull-right botones">
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
                            
                            {!! Form::open(['route' => 'comisionFactura', 'method' => 'POST', 'role' => 'form', 'id' => 'form_search', 'autocomplete' => 'off']) !!}
                            <div class="row">
                                <div class="col-md-8">
                                    
                                    <div class="form-group d-flex flex-column" id="options_owners">
                                        <label for="group_id">Buscar por grupo:</label>
                                        <div class="form-group">
                                            <select style="width:50%" name="group" id="group_id" >
                                                <option id="group_id_options" value="">Buscar el grupo</option>
                                            </select>
                                        </div>
                                    </div>
                                  
                                </div> 

                                
                                {{-- <div class="col-md-8">
                                    
                                    <div class="form-group d-flex flex-column" id="mes_year">
                                        <label for="group_id">Buscar por mes:</label>
                                        <div class="form-group">
                                            <select style="width:50%" name="group" id="mes_select" >

                                                @foreach ($meses as $mes )
                                                
                                                    <option id="option_mes" value="{{$mes['id']}}">{{ $mes['mes'].' - '. $mes['year']   }}</option>
                                                    
                                                @endforeach
                                                
                                            </select>
                                        </div>
                                    </div>
                                  
                                </div>  --}}
                                 
                                
                              
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
                                            value="{{ old('reservationtime', $fecha_ ?? '') }}" />
                                        </div>
                                
                                    </div>
                                </div>

                            </div>     
                            {!! Form::close() !!}
                        </div>
                    </div>

                    <div class="info-box" style="background-color: #285f6c !important;color: white">

                        @foreach ($data_invoice as $item)    
                            <?php 
                                $total_monto += $item['total_comision'];
                                $total_transaccion += $item['total_transaccion'];
                                $total_comision_tc += $item['total_comision_tc'];
                                $total_comision_td += $item['total_comision_td'];
                                $total_comision_dc += $item['total_comision_dc'];
                            ?>
                        @endforeach            

                        <span class="info-box-icon"><i class="fa fa-bar-chart" aria-hidden="true"></i></span>

                        <div style="display: flex">

                            <div class="info-box-content" style="margin-left: 15px;">
                                <span class="info-box-text">Total transacción </span>
                                <span class="info-box-number">{{ number_format($total_transaccion) }}</span>
                            </div>

                            <span class="info-box-icon"><i class="fa fa-line-chart" aria-hidden="true"></i></span>

                            <div class="info-box-content" style="margin-left: 15px;">
                                <span class="info-box-text">Total Comisión</span>
                                <span class="info-box-number">{{ number_format($total_monto) }}</span>
                            </div>

                            <span class="info-box-icon"><i class="fa fa-arrow-down" aria-hidden="true"></i></span>

                            <div class="info-box-content" style="margin-left: 15px;">
                                <span class="info-box-text">Total TC </span>
                                <span class="info-box-number">{{ number_format($total_comision_tc) }}</span>
                            </div>

                            <span class="info-box-icon"><i class="fa fa-arrow-down" aria-hidden="true"></i></span>

                            <div class="info-box-content" style="margin-left: 15px;">
                                <span class="info-box-text">Total TD </span>
                                <span class="info-box-number">{{ number_format($total_comision_td) }}</span>
                            </div>

                            <span class="info-box-icon"><i class="fa fa-arrow-down" aria-hidden="true"></i></span>

                            <div class="info-box-content" style="margin-left: 15px;">
                                <span class="info-box-text">Total DC </span>
                                <span class="info-box-number">{{ number_format($total_comision_dc) }}</span>
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

                        <table class="table table-bordered table-striped" role="grid" id="datatable_1">
                            <thead>
                                <tr>
                                    <th>Nombre grupo</th>
                                    <th>Ruc grupo</th>
                                    <th>Producto descripción</th>
                                    <th>Número factura</th>
                                    <th>Total comisión</th>
                                    <th>Total comisión Td</th>
                                    <th>Total comisión Dc</th>
                                    <th>Total comisión Tc</th>
                                    <th>Mes</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                
                                @foreach ($data_invoice as $item)
                                    
                                    <?php 
                                        $voucher_data = json_decode($item['voucher_data'],false);
                                    ?>

                                    <tr>
                                        <td  class="red"  style="border-top: 1px solid #ccc">{{ $item['nombre_grupo'] }}</td>
                                        <td  style="border-top: 1px solid #ccc">{{ $item['ruc_cliente'] }}</td>
                                        @if (($item['total_comision_td'] + $item['total_comision_dc']) > 0 and $item['total_comision_tc'] == 0)
                                            <td  style="border-top: 1px solid #ccc"> INT151 - {{$item['description'] }}</td>
                                        @elseif (($item['total_comision_td'] + $item['total_comision_dc']) == 0 and $item['total_comision_tc'] > 0)
                                            <td  style="border-top: 1px solid #ccc"> INT150 - {{$item['description'] }}</td>
                                        @elseif (($item['total_comision_td'] + $item['total_comision_dc']) > 0 and $item['total_comision_tc'] > 0)
                                            <td  style="border-top: 1px solid #ccc"> INT150 - INT151 {{$item['description'] }}</td>
                                        @endif
                                        <td  style="border-top: 1px solid #ccc">{{ str_replace('-','',$voucher_data->comprobante_numero) }}</td>
                                        <td  style="border-top: 1px solid #ccc">{{ number_format($item['total_comision']) }}</td>
                                        <td  style="border-top: 1px solid #ccc">{{ number_format($item['total_comision_td']) }}</td>
                                        <td  style="border-top: 1px solid #ccc">{{ number_format($item['total_comision_dc']) }}</td>
                                        <td  style="border-top: 1px solid #ccc">{{ number_format($item['total_comision_tc']) }}</td>
                                        <td  style="border-top: 1px solid #ccc">{{ date("m/Y", strtotime($item['created_at'])) }}</td>
                                       
                                        <td class="ver_detalles desabilitar" style="border-top: 1px solid #ccc; cursor: pointer">
                                            <div class="form-group row justify-content-center">
                                                <div class="col-md-3" style="margin-left:5px">
                                                    {!! Form::open(['route' => 'comisionFactura', 'method' => 'POST', 'role' => 'form', 'id' => 'form_search_factura', 'autocomplete' => 'off']) !!}

                                                        <input type="hidden" name="id_grupo" value="{{$item['id_grupo_atm']}}">
                                                        <input type="hidden" name="id_invoice" value="{{$item['invoice_id']}}">
                                                        <input type="hidden" name="button_name_pdf" value="generate_pdf">

                                                        <div  class="btn_spinn text-center d-flex align-items-end "><i class="fa fa-circle-o-notch fa-spin spinners" style="font-size:24px"></i></div>

                                                        <button  class="btn_spinn_detalle btn btn-secondary" data-toggle="tooltip" data-placement="right" title="Ver Factura">
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
             
             console.log($(".botones .btn_excel_exportar"));
           
            $(".botones .btn_excel_exportar").hide();
            $(".botones .btn_spinn_excel").show();
            

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

        $( document ).ready(function() {
            $('.js-select-status').select2();
            $('#mes_select').select2();
            $('[data-toggle="tooltip"]').tooltip();
            $('#group_id').selectize()[0].selectize.setValue("{{ $group_input }}", false); 

            $(".btn_excel").show();
            $(".btn_spinn").hide();

            $(".btn_excel_exportar").show();
            $(".btn_spinn_excel").hide();

        });

        $(document).ready(function() {
            $(".btn_spinn").hide();
            $('.ver_detalles').on('click',function() { 
                $(this).find(".btn_spinn").show();
                $(this).find(".btn_spinn_detalle").hide();
                $('.desabilitar').addClass("disabledbutton");
                
                $('#form_search_factura').submit();
                
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

        
        /*************************************GROUP*******************************************************/

            var listItems = [
                    <?php 
                        foreach($groups as $key => $value){
                            echo "{id: '".$value['id']."', value: '".$value['description']."'},";
                        }
                    ?>
                    
                ];
            listItems.unshift({id: '', value: 'Sin filtro'});
            
            /* Initialize select*/
            var $select = $('#group_id').selectize();
            var control = $select[0].selectize;
            control.clear()
            control.clearOptions();

            /* Fill options and item list*/
            var optionsList = [];
            var itemsList = [];
            $.each(listItems, function() {
            optionsList.push( {
                            value: this.id,
                            text: this.value
                    });
            });
            
            /* Add options and item and then refresh state*/                    
            control.addOption(optionsList)
            control.refreshState();

        /*************************************END GROUP*******************************************************/
         

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

        $(document).ready(function() {
            var table = $('#datatable_1').DataTable(data_table_config)
        });
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
        
        /*
         $(function () {
           
                $('#reservationtime').datepicker({
                    changeMonth: true,
                    changeYear: true,
                    showButtonPanel: true,
                    dateFormat: "m/d/yy"
                });
        });
        */

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

            format: 'YYYY/MM',
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



