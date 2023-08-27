@extends('layout')

@section('title')
    Ayuda - Plantillas
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Plantillas
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Ayuda - Generador de Ayuda</a></li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">Agregar ayuda</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                    class="fa fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="callout callout-default"
                            style="border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px">
                            <h4> Pantalla: </h4>

                            <div class="row">
                                <div class="col-md-3">
                                    <label for="module_id">Módulo:</label>
                                    <div class="form-group">
                                        <select class="form-control select2" id="module_id" name="module_id"></select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="key">Identificador:</label>
                                        <input type="text" class="form-control" style="display:block" id="key" name="key"
                                            placeholder="Identificador de pantalla"></input>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label for="key">Descripción:</label>
                                        <input type="text" class="form-control" style="display:block" id="key"
                                            name="description" placeholder="Descripción de pantalla"></input>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label for="template_id">Plantilla de elementos:</label>
                                    <div class="form-group">
                                        <select class="form-control select2" id="template_id" name="template_id"></select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="box box-default">
                            <div class="box-header with-border">
                                <h3 class="box-title">Elementos:
                                </h3>
                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-box-tool"><i class="fa fa-plus-square fa-2x"
                                            title="Agregar nuevo contenido."></i></button>
                                    <button type="button" class="btn btn-box-tool"><i class="fa fa-times fa-2x"
                                            title="Eliminar elementos."></i></button>
                                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                            class="fa fa-minus" title="Colapsar elementos."></i></button>
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="box box-default">
                                    <div class="box-header with-border">
                                        <h3 class="box-title">
                                            Elemento n° 1 - Nivel 1
                                        </h3>
                                        <div class="box-tools pull-right">
                                            <button type="button" class="btn btn-box-tool"><i
                                                    class="fa fa-plus-square fa-2x"
                                                    title="Agregar nuevo contenido."></i></button>
                                            <button type="button" class="btn btn-box-tool"><i class="fa fa-times fa-2x"
                                                    title="Eliminar elementos."></i></button>
                                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                                    class="fa fa-minus" title="Colapsar elementos."></i></button>
                                        </div>
                                    </div>
                                    <div class="box-body">
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="callout callout-default"
                                                    style="border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div class="form-group">
                                                                <select class="form-control select2"></select>
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <input type="checkbox" title="Deshabilitar : Paquetigo Internet"
                                                                value="1" onclick="" style="cursor: pointer"
                                                                id="checkbox_service_1" checked="">
                                                            &nbsp; Activar /
                                                            Inactivar
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="row">
                                            <div class="col-md-12">
                                                <div class="box box-default">
                                                    <div class="box-header with-border">
                                                        <h3 class="box-title">Contenido</h3>
                                                        <div class="box-tools pull-right">
                                                            <button type="button" class="btn btn-box-tool"><i
                                                                    class="fa fa-plus-square fa-2x"
                                                                    title="Agregar nuevo contenido."></i></button>
                                                            <button type="button" class="btn btn-box-tool"><i
                                                                    class="fa fa-times fa-2x"
                                                                    title="Eliminar elementos."></i></button>
                                                            <button type="button" class="btn btn-box-tool"
                                                                data-widget="collapse"><i class="fa fa-minus"
                                                                    title="Colapsar elementos."></i></button>
                                                        </div>
                                                    </div>
                                                    <div class="box-body">
                                                        <div class="row">
                                                            <div class="col-md-3">
                                                                <div class="callout callout-default"
                                                                    style="border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px">
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <div class="form-group">
                                                                                <input type="text" class="form-control"
                                                                                    style="display:block"
                                                                                    placeholder="Descripción de contenido"></input>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <div class="form-group">
                                                                                <select class="form-control select2"
                                                                                    ></select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                    <div class="row">
                                                                        <div class="col-md-12">
                                                                            <div class="form-group">
                                                                                <select class="form-control select2"
                                                                                    ></select>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </section>
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

    <!-- select2 -->
    <script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>

    <!-- Iniciar objetos -->
    <script type="text/javascript">
        //Datatable config
        var data_table_config = {
            //custom
            orderCellsTop: true,
            fixedHeader: true,
            pageLength: 20,
            lengthMenu: [5, 10, 20, 30, 50, 70, 100, 250, 500, 1000],
            dom: '<"pull-left"f><"pull-right"l>tip',
            language: {
                "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
            },
            scroller: true,
        }

        $('.dataTable').DataTable(data_table_config);

        $.get("/help/help_module", function(data) {
            $('#module_id').append($('<option>', {
                value: '',
                text: 'Seleccionar módulo.'
            }));

            for (var i in data) {
                var id = data[i].id;
                var description = data[i].description;

                $('#module_id').append($('<option>', {
                    value: id,
                    text: description
                }));
            }
        });

        $.get("/help/help_template", function(data) {
            $('#template_id').append($('<option>', {
                value: '',
                text: 'Seleccionar plantilla.'
            }));

            for (var i in data) {
                var id = data[i].id;
                var description = data[i].description;

                $('#template_id').append($('<option>', {
                    value: id,
                    text: description
                }));
            }
        });

    </script>
@endsection
