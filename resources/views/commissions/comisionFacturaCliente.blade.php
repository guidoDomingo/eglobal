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
    $data_invoice_actual = isset($data['lists']['data_invoice'][0]) ? $data['lists']['data_invoice'][0] : [];
    //dd($data_invoice);
    $groups = $data['lists']['group'];
    $meses = $data['lists']['meses'];
    //$fecha = 
    /*
        INPUTS
    */
    $group_input = $data['inputs']['group'];

    /* meses del año */


   
    
    ?>

<section class="content-header">

    <div class="row">
        <div class="col-md-12">
            @include('partials._flashes')
        </div>
    </div>
    @if (count($data_invoice) == 0)
        <div class="sin-factura">
             <h1>No tiene Factura pendiente</h1>
        </div>
    @else
    <div class="box box-default" style="border-radius: 5px;">
        <div class="box-header with-border">
            <h3 class="box-title" style="font-size: 25px;">Reporte de comisiones ventas Qr
            </h3>
            
            {{-- <div class="box-tools pull-right botones">
                <button class="btn " type="button" title="Buscar según los filtros en los registros." style="margin-right: 5px; background-color: #285F6C; color: white" id="search" name="search" onclick="search('search')">
                    <span class="fa fa-search btn_excel"> Buscar</span>
                    <div class="btn_spinn text-center d-flex align-items-end "> Buscando <i class="fa fa-circle-o-notch fa-spin" style="font-size:10px"></i></div>
                </button>
            </div> --}}
        </div>

        <div class="row">
            <div class="col-md-4"></div>

            <div class="col-md-4"></div>
        </div>

        <div class="box-body">

            <div id="graph_spinn" class="text-center d-flex align-items-end " style="margin: 50px 10px"><i class="fa fa-circle-o-notch fa-spin spinners" style="font-size:24px"></i></div>


            <div id="content" style="display: none">

                <div class="box box-default" style="border: 1px solid #d2d6de;">
                    {{-- <div class="box-header with-border">
                        <h3 class="box-title">Filtrar búsqueda:</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
                        </div>
                    </div> --}}
                    <div class="box-body">


                        <div class="row">

                            <div class="col-md-12 factura-qr">
                                <div class="card text-center" style="width: 18rem;">
                                    <h2>Factura actual</h2>
                                    <div class="card-venta">
                                        <div class="factura-cliente-monto">
                                            <h5 class="monto-title">Gs {{number_format($data_invoice_actual['total_comision'])}} </h5>
                                        </div>
                                        <p>Deuda Actual Gs {{number_format($data_invoice_actual['total_comision'])}}</p>
                                        <strong>
                                            <p>Generado {{ date("d/m/Y", strtotime($data_invoice_actual['created_at'])) }}</p>
                                        </strong>

                                        {!! Form::open(['route' => 'comisionFacturaCliente', 'method' => 'POST', 'role' => 'form', 'id' => 'form_search_factura_card', 'autocomplete' => 'off']) !!}

                                        <input type="hidden" name="id_grupo" value="{{$data_invoice_actual['id_grupo_atm']}}">
                                        {{-- <input type="hidden" name="id_invoice" value="{{$data_invoice_actual['invoice_id']}}"> --}}

                                        <div class="ver_detalles desabilitar">

                                            <div class="text-center d-flex align-items-end "><i class="fa fa-circle-o-notch fa-spin btn_spinn_qr" style="font-size:24px; color:red"></i></div>

                                            <span onclick="factura_qr('qr',{{$data_invoice_actual['invoice_id']}} )" class="boton-qr">Descargar factura</span>

                                        </div>
                                        {!! Form::close() !!}
                                    </div>
                                </div>
                            </div>

                            {!! Form::open(['route' => 'comisionFacturaCliente', 'method' => 'POST', 'role' => 'form', 'id' => 'form_search', 'autocomplete' => 'off']) !!}

                            {{-- <div class="col-md-5">
                                    
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

                            </div> --}}



                                {{-- <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Rango de Tiempo & Fecha: </label>
                                        <div class="input-group">
                                            <div class="input-group-addon">
                                                <i class="fa fa-clock-o"></i>
                                            </div>
                                            <input name="reservationtime" type="text" id="reservationtime" class="form-control pull-right" placeholder="" value="{{ $fecha_  or ''}}" />
                                        </div>

                                    </div>
                                </div> --}}

                            {!! Form::close() !!}

            </div>

        </div>
    </div>

    <div class="box box-default collapsed-box" style="border: 1px solid #d2d6de;">
        <div class="box-header with-border">
            <h3 class="box-title">Mostrar / Ocultar columnas</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
            </div>
        </div>
        <div class="box-body" id="hide_show_columns">
        </div>
    </div>

    <table class="table table-bordered table-striped" role="grid" id="datatable_1">
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre</th>
                <th>Importe</th>
                <th>Número de Factura</th>
                <th>Fecha</th>
                <th>Acción</th>
            </tr>
        </thead>
        <tbody>

            @foreach ($data_invoice as $key => $item)

            <tr>
                <td>{{ $key + 1 }}</td>
                <td class="red" style="border-top: 1px solid #ccc">{{ $item['nombre_grupo'] }}</td>
                <td style="border-top: 1px solid #ccc">{{ number_format($item['total_comision']) }}</td>
                <td style="border-top: 1px solid #ccc">{{ str_replace('-','',$item['invoice_number']) }}</td>
                <td style="border-top: 1px solid #ccc">{{ date("d-m-Y", strtotime($item['created_at'])) }}</td>
                <td class="ver_detalles desabilitar" style="border-top: 1px solid #ccc; cursor: pointer">
                    <div class="form-group row justify-content-center">
                        <div class="col-md-3" style="margin-left:5px">
                            {!! Form::open(['route' => 'comisionFacturaCliente', 'method' => 'POST', 'role' => 'form', 'id' => 'form_search_factura', 'autocomplete' => 'off']) !!}

                                <input type="hidden" name="id_grupo" value="{{$item['id_grupo_atm']}}">
                                {{-- <input type="hidden" name="id_invoice" value="{{$item['invoice_id']}}"> --}}

                                <div class="text-center d-flex align-items-end "><i class="fa fa-circle-o-notch fa-spin btn_spinn_qr-lateral" style="font-size:24px; color:red"></i></div>

                                <span onclick="factura_qr('qr_lateral', {{$item['invoice_id']}} )" class="boton-qr-lateral" data-toggle="tooltip" data-placement="right" title="Ver Factura">
                                    <i class="fa fa-sitemap" aria-hidden="true"></i>
                                </span>

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
    @endif



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
<link href="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css" />
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
    
    function factura_qr(button_name,invoice_id) {

            if(button_name == "qr_lateral"){

                $(".btn_spinn_qr-lateral").hide();
                $('.ver_detalles').on('click',function() { 
                    $(this).find(".btn_spinn_qr-lateral").show();
                    $(this).find(".boton-qr-lateral").hide();
                    $('.desabilitar').addClass("disabledbutton");
                });
            

                $('#datatable_1').on( 'draw.dt', function () {
                        $(".btn_spinn_qr-lateral").hide();
                        $('.ver_detalles').on('click',function() { 
                            $(this).find(".btn_spinn_qr-lateral").show();
                            $(this).find(".boton-qr-lateral").hide();
                            $('.desabilitar').addClass("disabledbutton");
                        });
                });

                /*
                    Enviamos los datos
                */

                var input = $('<input>').attr({
                    'type': 'hidden',
                    'id': 'button_name',
                    'name': 'id_invoice',
                    'value': invoice_id
                });

                $('#form_search_factura').append(input);

                var input = $('<input>').attr({
                    'type': 'hidden',
                    'id': 'button_name',
                    'name': 'button_name',
                    'value': 'generate_x'
                });

            
                $('#form_search_factura').append(input);

                $('#form_search_factura').submit();
                

            }else if(button_name == "qr"){

                $(".btn_spinn_qr").hide();
                $('.ver_detalles').on('click',function() { 
                    $(this).find(".btn_spinn_qr").show();
                    $(this).find(".boton-qr").hide();
                    $('.desabilitar').addClass("disabledbutton");
                });
        

                $('#datatable_1').on( 'draw.dt', function () {

                    $(".btn_spinn_qr").hide();
                    $('.ver_detalles').on('click',function() { 
                        $(this).find(".btn_spinn_qr").show();
                        $(this).find(".boton-qr").hide();
                        $('.desabilitar').addClass("disabledbutton");
                    });
                });


                /*
                    Enviamos los datos
                */

                 var input = $('<input>').attr({
                    'type': 'hidden',
                    'id': 'button_name',
                    'name': 'id_invoice',
                    'value': invoice_id
                });

                $('#form_search_factura_card').append(input);

                var input = $('<input>').attr({
                    'type': 'hidden',
                    'id': 'button_name',
                    'name': 'button_name',
                    'value': 'generate_x'
                });
            
                $('#form_search_factura_card').append(input);

                $('#form_search_factura_card').submit();
                
            }
    
            
            
    }

    function search(button_name) {
        $("#graph_spinn").show();
        $("#content").hide();

        $(".btn_excel").hide();
        $(".btn_spinn").show();

        var input = $('<input>').attr({
            'type': 'hidden'
            , 'id': 'button_name'
            , 'name': 'button_name'
            , 'value': button_name
        });


        $('#form_search').append(input);
        $('#form_search').submit();


    }

    function search_excel(button_name) {

        console.log($(".botones .btn_excel_exportar"));

        $(".botones .btn_excel_exportar").hide();
        $(".botones .btn_spinn_excel").show();


        var input = $('<input>').attr({
            'type': 'hidden'
            , 'id': 'button_name'
            , 'name': 'button_name'
            , 'value': button_name
        });

        $('#form_search').append(input);
        $('#form_search').submit();

        setTimeout(function() {
            $(".btn_excel_exportar").show();
            $(".btn_spinn_excel").hide();
        }, 3000);



    }

    $(document).ready(function() {

        $(".btn_spinn_qr").hide();
        $(".btn_spinn_qr-lateral").hide();
        $('.js-select-status').select2();
        $('#mes_select').select2();
        $('[data-toggle="tooltip"]').tooltip();

        $(".btn_excel").show();
        $(".btn_spinn").hide();

        $(".btn_excel_exportar").show();
        $(".btn_spinn_excel").hide();

    });


    //Datatable config
    var data_table_config = {
        orderCellsTop: true
        , fixedHeader: true
        , pageLength: 20
        , lengthMenu: [
            1, 2, 5, 10, 20, 30, 50, 70, 100, 150, 300, 500, 1000, 1500, 2000
        ]
        , dom: '<"pull-left"f><"pull-right"l>tip'
        , language: {
            "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
        }
        , scroller: true
        , processing: true
        , initComplete: function(settings, json) {
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
        delimiter: ','
        , persist: false
        , openOnFocus: true
        , valueField: 'id'
        , labelField: 'description'
        , searchField: 'description'
        , maxItems: 1
        , options: {}
    };


    

</script>
@endsection
