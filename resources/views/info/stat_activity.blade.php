@extends('layout')

@section('title')
    Consultas Activas
@endsection
@section('content')

<?php
//Variables que se usan en todo el blade.
$message = $data['message'];
$headers = $data['lists']['headers'];
$records = $data['lists']['records'];
$json = json_encode($data['lists']['records']);

//var_dump($query);

?>

<section class="content-header">

    <style>
        
        .pagination>.active>a, .pagination>.active>a:focus, .pagination>.active>a:hover, .pagination>.active>span, .pagination>.active>span:focus, .pagination>.active>span:hover {
            background: #285f6c; 
            color: white;
            border-color: #285f6c;
        }
        
    </style>

    <div class="row">
        <div class="col-md-12">
            @include('partials._flashes')
        </div>
    </div>

    <div class="box box-default" style="border-radius: 5px;">
        <div class="box-header with-border">
            <h3 class="box-title" style="font-size: 25px;">Consultas activas 
            </h3>
            <div class="box-tools pull-right">
                <button class="btn btn-default" type="button" title="Refrescar las consultas activas" style="background: #285f6c; color: white; border: none" id="search" name="search" onclick="search('search')">
                    <span class="fa fa-refresh"></span> Actualizar
                </button>
            </div>
        </div>

        <div class="box-body">
            <div id="div_load" style="text-align: center; margin-bottom: 10px; font-size: 20px;">
                <div>
                    <i class="fa fa-spin fa-refresh fa-2x" style="vertical-align: sub;"></i> &nbsp;
                    Cargando...

                    <p id="rows_loaded" title="Filas cargadas"></p>
                </div>
            </div>

            <div id="content" style="display: none">

                {!! Form::open(['route' => 'info_stat_activity', 'method' => 'POST', 'role' => 'form', 'id' => 'form_search']) !!}

                <input name="json" id="json" type="hidden">

                {!! Form::close() !!}

                @if (count($headers) > 0)
                <div class="box box-default" style="border: 1px solid #d2d6de;">
                    <div class="box-header with-border">
                        <h3 class="box-title">Mostrar / Ocultar columnas</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse" id="search_open"><i class="fa fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="box-body" id="hide_show_columns">
                    </div>
                </div>
                
                <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_1" style="font-size: 12px; font-weight: bold">
                    <thead>
                        <tr style="background: #285f6c; color: white; border: none; margin-top: 3px">
                            @foreach ($headers as $item)
                            <th>{{ $item }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($records as $sub_key)
                        <?php
                        $sub_key_aux = json_encode($sub_key);
                        ?>

                        <tr>
                            @foreach ($headers as $item)

                            <?php 
                                $color = '#333';
                                $background = 'white';
                                $font_weight = 'normal';

                                if ($item == 'Procesos') {

                                    $font_weight = 'bold';

                                    if ((int) $sub_key[$item] >= 5) {
                                        $color = 'white';
                                        $background = '#dd4b39';
                                    } else if ((int) $sub_key[$item] >= 3 and (int) $sub_key[$item] < 5) {
                                        $color = 'white';
                                        $background = '#f39c12';
                                    }

                                } else if ($item == 'Duración (Segundos)') {

                                    $font_weight = 'bold';

                                    if ((int) $sub_key[$item] >= 20) {
                                        $color = 'white';
                                        $background = '#dd4b39';
                                    } else if ((int) $sub_key[$item] >= 10 and (int) $sub_key[$item] < 20) {
                                        $color = 'white';
                                        $background = '#f39c12';
                                    }

                                }
                            ?>

                            <td title="{{ $sub_key[$item] }}" style="color: {{$color}}; background: {{$background}}; font-weight: {{$font_weight}}"> {{ $sub_key[$item] }} </td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>

                @else 
                    <h4 style="color: #dd4b39">{{ $message }}</h4>
                @endif

            </div>
        </div>
    </div>

    <!-- Modal -->
    <div id="modal" class="modal fade" role="dialog">

        <div class="modal-dialog modal-dialog-centered" role="document" style="background: white; border-radius: 5px">

            <!-- Modal content-->
            <div class="modal-content" style="border-radius: 10px">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <div class="modal-title" style="font-size: 20px; text-align: center">
                        Opciones disponibles para exportar
                    </div>
                </div>
                <div class="modal-body" style="text-align: center">
                    <div class="btn-group" role="group">

                        <button class="btn btn-success" type="button" title="Exportar a .xls" onclick="search('xls')">
                            <span class="fa fa-file-excel-o"></span> xls
                        </button>

                        &nbsp;

                        <button class="btn btn-success" type="button" title="Exportar a .xlsx" onclick="search('xlsx')">
                            <span class="fa fa-file-excel-o"></span> xlsx
                        </button>

                        &nbsp;

                        <button class="btn btn-success" type="button" title="Exportar a .csv" onclick="search('csv')">
                            <span class="fa fa-file-excel-o"></span> csv
                        </button>

                        <!--<a class="btn btn-warning" title="Exportar a .xls"><i class="fa fa-file-excel-o"></i>
                                                        .json</a> &nbsp;-->
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="content">

</section>

@endsection

@section('page_scripts')
@include('partials._selectize')
@include('partials._sql_formatter')
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

<script type="text/javascript" src=""></script>

<!-- Iniciar objetos -->
<script type="text/javascript">
    //search('generate_x');


    function modal_generate_x() {
        $("#modal").modal(); //Abre la ventana que tiene los botones a exportar
    }

    function search(button_name) {

        var query = $('#query').val();

        if (query !== '') {
            if (button_name == 'search') {
                $('#content').css('display', 'none');
                $('#div_load').css('display', 'block');
            }

            $('#form_search').append('<input type="hidden" name="button_name" value="' + button_name + '" />');
            $('#form_search').submit();
        } else {
            swal({
                    title: 'Atención',
                    text: 'Ingresar la consulta.',
                    type: 'warning',
                    showCancelButton: false,
                    closeOnClickOutside: false,
                    confirmButtonColor: '#00c0ef',
                    confirmButtonText: 'Aceptar'
                },
                function(isConfirm) {}
            );

        }
    }

    var data_table_config = {
        //custom
        //orderCellsTop: true,
        //scrollX: true,
        //scrollCollapse: true,
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
        displayLength: 10,
        order: [],
        columnDefs: [{
            targets: 'no-sort',
            orderable: false,
        }],
        "columnDefs": [{
            "height": "25",
            "targets": 0
        }],
        //processing: true,
        //ordering: false,
        //order: [[ 5, "asc" ]],
        initComplete: function(settings, json) {
            $('#content').css('display', 'block');
            $('#div_load').css('display', 'none');
            //$('body > div.wrapper > header > nav > a').trigger('click');
        }
    }

    @if(count($records) <= 0)
    $('#content').css('display', 'block');
    $('#div_load').css('display', 'none');
    //$('body > div.wrapper > header > nav > a').trigger('click');
    @endif

    var table = $('#datatable_1').DataTable(data_table_config);

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
            '" style="background: #285f6c; color: white; border: none; margin-top: 3px">' +
            '<i class="fa fa-eye"></i> &nbsp;' + description +
            '</a> '
        );
    }

    $('a.toggle-vis').on('click', function(e) {
        e.preventDefault();

        var column = table.column($(this).attr('data-column'));
        column.visible(!column.visible());

        var fa = (!column.visible()) ? 'eye-slash' : 'eye';
        $(this).html('<i class="fa fa-' + fa + '"></i> &nbsp;' + $(this).attr('value'));
    });

    //Esconder la alerta después de 5 segundos. 
    $(".alert").delay(5000).slideUp(300);
    $('[data-toggle="popover"]').popover();

    // Asignación de lo que se exportará luego.
    var json = {!!$json!!};
    json = JSON.stringify(json);
    $('#json').val(json);
    console.log('JSON:', $('#json').val());
</script>
@endsection