@extends('layout')

@section('title')
    Comisiones agrupados
@endsection
@section('content')
    <?php
    //error_reporting(0);
    //Variable que se usa en todo el documento

    //listas
    $commission_view = $data['lists']['commission_agrupado'];
    $commission_view_ajax = [];
    $owners = $data['lists']['owners'];

    //inputs
    $fecha_ = $data['inputs']['fecha'];
    $owners_input = $data['inputs']['owners'];
    $get_info = $data['inputs']['get_info'];

    //variables
    $total_monto = 0;
    $total_transaccion = 0;
    $total_eglobal = 0;
    $total_punto = 0;
    $total_bruto = 0;
    //dd($owners);

    
    
    
    ?>

    <section class="content-header">

        <div class="row">
            <div class="col-md-12">
                @include('partials._flashes')
            </div>
        </div>

        <div class="box box-default" style="border-radius: 5px;">
            <div class="box-header with-border">
                <h3 class="box-title" style="font-size: 25px;">Reporte de comisiones generadas por RED 
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
                            
                            {!! Form::open(['route' => 'commissions_generales', 'method' => 'POST', 'role' => 'form', 'id' => 'form_search', 'autocomplete' => 'off']) !!}
                            <div class="row">
                                <div class="col-md-8">
                                    
                                    <div class="form-group d-flex flex-column" id="options_owners">
                                        <label for="owners_id">Buscar por red:</label>
                                        <div class="form-group">
                                            <select style="width:50%" name="owners" id="owners_id" >
                                                <option id="owners_id_options" value="">Buscar la red</option>
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

                        @foreach ($commission_view as $item)    
                            <?php 
                                $total_monto += $item['monto'];
                                $total_transaccion += $item['cantidad_transaccion'];
                                $total_eglobal += $item['comision_eglobal'] == null ? 0 : $item['comision_eglobal'];
                                $total_punto += $item['comision_punto'] == null ? 0 : $item['comision_punto'];
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

                        <table class="table table-bordered table-striped" role="grid" id="datatable_1">
                            <thead>
                                <tr>
                                    <th>Red</th>
                                    <th>Monto total</th>
                                    <th>Cantidad transacción</th>
                                    <th>Comisión bruta</th>
                                    <th>Comisión neta nivel 1</th>
                                    <th>Comisión para el punto nivel 1</th>
                                    <th>Inicio periodo</th>
                                    <th>Fin periodo</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                
                                @foreach ($commission_view as $item)
                                    
                                    <tr>
                                        <td  class="red"  style="border-top: 1px solid #ccc">{{ $item['red'] }}</td>
                                        <td  style="border-top: 1px solid #ccc">{{ number_format($item['monto']) }}</td>
                                        <td  style="border-top: 1px solid #ccc">{{ number_format($item['cantidad_transaccion']) }}</td>
                                        <td  style="border-top: 1px solid #ccc">{{ number_format($item['comision_bruta']) }}</td>
                                        <td  style="border-top: 1px solid #ccc">{{ number_format($item['comision_eglobal']) }}</td>
                                        <td  style="border-top: 1px solid #ccc">{{ number_format($item['comision_punto']) }}</td>
                                        <td  style="border-top: 1px solid #ccc">{{ date("d/m/Y", strtotime($item['fecha_min'])) }}</td>
                                        <td  style="border-top: 1px solid #ccc">{{ date("d/m/Y", strtotime($item['fecha_max'])) }}</td>
                                       
                                        <td class="ver_detalles desabilitar" style="border-top: 1px solid #ccc; cursor: pointer">
                                            <div class="form-group row justify-content-center">
                                                <div class="col-md-3" style="margin-left:5px">
                                                    {!! Form::open(['route' => 'service_detallado', 'method' => 'POST', 'role' => 'form', 'id' => 'form_search', 'autocomplete' => 'off']) !!}
                                                        <input type="hidden" name="red" value="{{ $item['red'] }}">
                                                        <input type="hidden" name="id" value="{{ $item['id'] }}">
                                                        <input type="hidden" name="fecha" value="{{ $fecha_ }}">

                                                        <div  class="btn_spinn text-center d-flex align-items-end "><i class="fa fa-circle-o-notch fa-spin spinners" style="font-size:24px"></i></div>

                                                        <button type="submit" class="btn_spinn_detalle btn btn-secondary" data-toggle="tooltip" data-placement="right" title="Ver detalle">
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
              
                          {{--  MODAL --}}

                            <!-- Scrollable modal -->

                            <div class="modal fullscreen-modal fade" id="staticBackdrop" data-backdrop="static" data-keyboard="false" tabindex="-1" aria-labelledby="staticBackdropLabel" aria-hidden="true">

                                <div class="modal-dialog modal-dialog-scrollable">

                                    <div class="modal-content">

                                        <div class="modal-header">
                                            <h2 class="modal-title" id="staticBackdropLabel"></h2>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                            <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>

                                        <div class="modal-body">

                                            <table class="table table-bordered table-hover dataTable" role="grid" id="test">
                                            <thead>
                                                <tr>
                                                    {{-- <th>Monto</th> --}}
                                                    <th>Red</th>
                                                    <th>cantidad_transaccion</th>
                                                    <th>monto_red</th>
                                                    <th>comi_neta_eglobal</th>
                                                    <th>comi_neta_punto</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                
                                            </tbody id="modal_body">

                                            </table>  
                                            
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                                            {{-- <button type="button" class="btn btn-primary">Ver</button> --}}
                                        </div>

                                    </div>

                                </div>

                            </div>    

                          {{-- END  MODAL --}}
                    
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
            $('[data-toggle="tooltip"]').tooltip();
            $('#owners_id').selectize()[0].selectize.setValue("{{ $owners_input }}", false); 

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

        
        /*************************************Red*******************************************************/

            var listItems = [
                    <?php 
                        foreach($owners as $key => $value){
                            echo "{id: '".$value['id']."', value: '".$value['name']."'},";
                        }
                    ?>
                    
                ];
            listItems.unshift({id: '', value: 'Sin filtro'});
            
            /* Initialize select*/
            var $select = $('#owners_id').selectize();
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

        /*************************************END RED*******************************************************/
         

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



