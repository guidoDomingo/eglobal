@extends('layout')

@section('title')
    Convertir archivo a tabla
@endsection
@section('content')
    <style>
        input:invalid+span:after {
            content: '✖';
            padding-left: 5px;
        }

        input:valid+span:after {
            content: '✓';
            padding-left: 5px;
        }

        textarea {
            resize: vertical;
            height: 400px;
            max-height: 500px;
            min-height: 250px;
            width: 100%
        }

        tr {
            height: 15px;
        }

        .points {
            max-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

    </style>

    <?php
    //Variables que se usan en todo el blade.
    $headers = $data['lists']['headers'];
    $records = $data['lists']['records'];
    $file = $data['inputs']['file'];
    $table_name = $data['inputs']['table_name'];
    
    //var_dump($query);
    
    ?>

    <section class="content-header">

        <div class="row">
            <div class="col-md-12">
                @include('partials._flashes')
            </div>
        </div>

        <div class="box box-default" style="border-radius: 5px;">
            <div class="box-header with-border">
                <h3 class="box-title" style="font-size: 25px;">Convertir archivo a tabla
                </h3>
                <div class="box-tools pull-right">
                    <button class="btn btn-info" type="button" title="Convertir el archivo seleccionado a tabla."
                        style="margin-right: 5px" id="search" name="search" onclick="search('search')">
                        <span class="fa fa-table"></span> Convertir archivo a tabla
                    </button>

                    <!--
                    @if (count($records) > 0)
                        <button class="btn btn-success" type="button" title="Convertir tabla en archivo excel."
                            id="generate_x" name="generate_x" onclick="modal_generate_x()">
                            <span class="fa fa-file-text"></span> Exportar
                        </button>
                    @endif
                    -->
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

                    {!! Form::open(['route' => 'info_file_to_table', 'method' => 'POST', 'role' => 'form', 'id' => 'form_search', 'enctype' => 'multipart/form-data']) !!}
                    <div class="row">
                        <div class="col-md-6">
                            <label for="files">Seleccionar archivo
                                <small> (Solo 1 archivo, extensiones: .xlsx, .xls, .csv) </small>

                            </label>
                            <div class="form-group">
                                <input type="file" class="form-control" id="file" name="file"
                                    accept=".xls,.xlsx,.csv" placeholder="Seleccione archivo."></input>
                            </div>
                        </div>
                        <!--<div class="col-md-2">
                            <label for="schema">Esquema:</label>
                            <div class="form-group">
                                <input type="text" class="form-control" id="schema" name="schema"
                                    placeholder="Seleccionar"></input>
                            </div>
                        </div>-->
                        <div class="col-md-4">
                            <label for="schema">Nombre de la tabla:</label>
                            <div class="form-group">
                                <input type="text" class="form-control" id="table_name" name="table_name"
                                    placeholder="Ingresar el nombre de la tabla"></input>
                            </div>
                        </div>
                    </div>
                    {!! Form::close() !!}


                    @if (count($headers) > 0)
                        <div class="box box-default" style="border: 1px solid #d2d6de;">
                            <div class="box-header with-border">
                                <h3 class="box-title">Mostrar / Ocultar columnas</h3>
                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-box-tool" data-widget="collapse"
                                        id="search_open"><i class="fa fa-plus"></i></button>
                                </div>
                            </div>
                            <div class="box-body" id="hide_show_columns">
                            </div>
                        </div>

                        <br />

                        <?php //var_dump($records); ?>

                        <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_1"
                            style="font-size: 12px; font-weight: bold">
                            <thead>
                                <tr>
                                    @foreach ($headers as $key => $value)
                                        <th title="La columna se llama: {{ $key }}">{{ $key }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($records as $item)
                                    <tr>
                                        @foreach ($headers as $key => $value)
                                            <td> {{ $item[$key] }} </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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
@endsection

@section('js')
    <!-- datatables -->
    <link rel="stylesheet" href="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.css">
    <script src="/bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.min.js"></script>

    <!-- Iniciar objetos -->
    <script type="text/javascript">
        //search('generate_x');

        function modal_generate_x() {
            $("#modal").modal(); //Abre la ventana que tiene los botones a exportar
        }

        function search(button_name) {

            var file = $("#file").val();
            //var schema = $("#schema").val();
            var table_name = $("#table_name").val();

            console.log(file, table_name);

            if (file !== '' && table_name !== '') {
                if (button_name == 'search') {
                    $('#content').css('display', 'none');
                    $('#div_load').css('display', 'block');
                }

                $('#form_search').append('<input type="hidden" name="button_name" value="' + button_name + '" />');
                $('#form_search').submit();
            } else {
                swal({
                        title: 'Atención',
                        text: 'Completar todos los campos!',
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
                $('body > div.wrapper > header > nav > a').trigger('click');
            }
        }

        @if (count($records) <= 0)
            $('#content').css('display', 'block');
            $('#div_load').css('display', 'none');
            $('body > div.wrapper > header > nav > a').trigger('click');
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
                '" style="margin-top: 3px">' +
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

        /*var selective_config = {
            delimiter: ',',
            persist: false,
            openOnFocus: true,
            valueField: 'id',
            labelField: 'description',
            searchField: 'description',
            maxItems: 1,
            options: {}
        };

        $('#schema').selectize(selective_config)[0].selectize.addOption();

        $('#schema').selectize()[0].selectize.setValue('', false);*/

        $('#table_name').val('{{ $table_name }}');

        //Evento que detecta la selección de archivos
        var input_files = document.getElementById("file");
        input_files.addEventListener("change",
            function(event) {
                let files = event.target.files;
                var files_length = files.length;
                var message = "";

                if (files_length > 0) {
                    if (files_length <= 1) {

                    } else {
                        message = "Solo se permite un 1 archivo a la vez,\nse seleccionó " +
                            files_length +
                            " archivos,\nvolver a seleccionar archivos.";
                    }
                } else {
                    message = "No seleccionaste ningún archivo.";
                }

                if (message !== "") {
                    $("#file").val(null);
                    swal({
                        title: "Atención",
                        text: message,
                        type: "warning",
                        showCancelButton: false,
                        closeOnConfirm: true,
                        closeOnCancel: false,
                        confirmButtonColor: "#2778c4",
                        confirmButtonText: "Aceptar"
                    });
                }
            }, false);
    </script>
@endsection
