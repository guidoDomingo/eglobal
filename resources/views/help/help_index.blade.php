@extends('layout')

@section('title')
    Generador de Ayuda
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Generador de Ayuda
            <small>Agregar nueva ayuda</small>
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
                            <button type="button" class="btn btn-box-tool" title="Agregar nueva ayuda.">
                                <i class="fa fa-plus-square fa-2x"></i>
                            </button>
                            <button type="button" class="btn btn-box-tool" title="Guardar.">
                                <i class="fa fa-save fa-2x"></i>
                            </button>
                            <button type="button" class="btn btn-box-tool" title="Vista previa.">
                                <i class="fa fa-eye fa-2x"></i>
                            </button>
                            <button type="button" class="btn btn-box-tool" data-widget="collapse">
                                <i class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="callout callout-default"
                            style="border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px">
                            <h4> Pantalla: </h4>

                            <div class="row">
                                <div class="col-md-4">
                                    <label for="module_id">Módulo:</label>
                                    <div class="form-group">
                                        <select class="form-control select2" id="module_id" name="module_id"></select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="key">Identificador:</label>
                                        <input type="text" class="form-control" style="display:block" id="key" name="key"
                                            placeholder="Identificador de pantalla"></input>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="description">Descripción:</label>
                                        <input type="text" class="form-control" style="display:block" id="description"
                                            name="description" placeholder="Descripción de pantalla"></input>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <label for="view_id">Tipo de Vista:</label>
                                    <div class="form-group">
                                        <select class="form-control select2" id="view_id" name="view_id"></select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label for="template_id">Plantilla:</label>
                                    <div class="form-group">
                                        <select class="form-control select2" id="template_id" name="template_id"></select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="box box-default" style="display:none" id="div_add_elements">
                            <div class="box-header with-border">
                                <h3 class="box-title">Elementos:
                                </h3>
                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-box-tool"><i class="fa fa-plus-square fa-2x"
                                            title="Agregar nuevo elemento." onclick="add_element('initial')"></i></button>
                                    <button type="button" class="btn btn-box-tool"><i class="fa fa-times fa-2x"
                                            title="Eliminar elementos."></i></button>
                                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                            class="fa fa-minus" title="Colapsar elementos."></i></button>
                                </div>
                            </div>
                            <div class="box-body" id="initial"></div>
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
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
    <script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>

    <!-- iCheck -->
    <link rel="stylesheet" href="/bower_components/admin-lte/plugins/iCheck/square/grey.css">
    <script src="/bower_components/admin-lte/plugins/iCheck/icheck.min.js"></script>

    <!-- Iniciar objetos -->
    <script type="text/javascript">
        //ID principal
        var help_id = 1;
        var item_number = 1;
        var type_list = [];
        var attribute_list = [];
        var template_list = [];
        var content_position_next_list = [];

        function load_combo(combo_id, message, list, selected_id) {
            //Cargar las siguientes posiciones
            $(combo_id).append($('<option>', {
                value: '',
                text: message
            }));

            for (var i in list) {
                $(combo_id).append($('<option>', {
                    value: list[i].id,
                    text: list[i].description
                }));
            }

            $(combo_id).val(selected_id).trigger('change');
        }

        function load_add_elements(id) {
            console.log('Id de la ayuda:', id);
            $('#div_add_elements').css('display', 'block');
        }

        function set_position(item_detail_id, position) {

        }

        function add_content(item_id) {

            //console.log('html: ' + $('#content_' + item_id).html());

            if ($('#content_' + item_id).html() == 'Sin contenido') {
                $('#content_' + item_id).html('');
            }

            var url = '/help/item_detail_add/';
            var json = {
                _token: token,
                item_id: item_id
            };

            $.post(url, json, function(data, status) {
                var item_detail_id = data.item_detail_id;
                var position = data.position;
                var message = data.message;
                var error = data.error;
                var type = '';
                var text = '';

                if (error == false) {
                    type = 'success';

                    $('#content_' + item_id).append(
                        $('<div>').attr({
                            'class': 'col-md-3'
                        }).append(
                            $('<div>').attr({
                                'class': 'row'
                            }).append(
                                $('<div>').attr({
                                    'class': 'col-md-12'
                                }).append(
                                    '<label>Posición: ' + position + '</label>'
                                )
                            )
                        ).append(
                            $('<div>').attr({
                                'class': 'row'
                            }).append(
                                $('<div>').attr({
                                    'class': 'col-md-12'
                                }).append(
                                    $('<div>').attr({
                                        'class': 'form-group',
                                    }).append(
                                        $('<textarea>').attr({
                                            'id': 'description_' + item_detail_id,
                                            'class': 'form-control',
                                            'placeholder': 'Descripción del contenido.',
                                            'style': 'resize: vertical;'
                                        })
                                    )
                                )
                            )
                        ).append(
                            $('<div>').attr({
                                'class': 'row'
                            }).append(
                                $('<div>').attr({
                                    'class': 'col-md-12'
                                }).append(
                                    $('<div>').attr({
                                        'class': 'form-group'
                                    }).append(
                                        $('<select>').attr({
                                            'id': 'item_detail_attribute_id_' + item_detail_id,
                                            'class': 'form-control select2',
                                        })
                                    )
                                )
                            )
                        ).append(
                            $('<div>').attr({
                                'class': 'row'
                            }).append(
                                $('<div>').attr({
                                    'class': 'col-md-12'
                                }).append(
                                    $('<div>').attr({
                                        'class': 'form-group'
                                    }).append(
                                        $('<select>').attr({
                                            'id': 'item_detail_template_id_' + item_detail_id,
                                            'class': 'form-control select2',
                                        })
                                    )
                                )
                            )
                        ).append(
                            $('<div>').attr({
                                'class': 'row'
                            }).append(
                                $('<div>').attr({
                                    'class': 'col-md-12'
                                }).append(
                                    $('<div>').attr({
                                        'class': 'form-group'
                                    }).append(
                                        $('<select>').attr({
                                            'id': 'item_detail_content_position_next_id_' + item_detail_id,
                                            'class': 'form-control select2',
                                        })
                                    )
                                )
                            )
                        ).append(
                            $('<div>').attr({
                                'class': 'row'
                            }).append(
                                $('<div>').attr({
                                    'class': 'col-md-12',
                                }).append(
                                    $('<div>').attr({
                                        'class': 'btn-group',
                                        'style': 'margin-bottom: 20px'
                                    }).append(
                                        $('<button>').attr({
                                            'type': 'button',
                                            'class': 'btn btn-default btn-xs',
                                            'title': 'Guardar.',
                                            'onclick': ''
                                        }).append('<i class="fa fa-save"></i>')
                                    ).append(
                                        $('<button>').attr({
                                            'type': 'button',
                                            'class': 'btn btn-default btn-xs',
                                            'title': 'Vista previa.',
                                            'onclick': ''
                                        }).append('<i class="fa fa-eye"></i>')
                                    ).append(
                                        $('<button>').attr({
                                            'type': 'button',
                                            'class': 'btn btn-default btn-xs',
                                            'title': 'Eliminar contenido',
                                            'onclick': ''
                                        }).append('<i class="fa fa-times"></i>')
                                    ).append(
                                        $('<button>').attr({
                                            'type': 'button',
                                            'class': 'btn btn-default btn-xs',
                                            'title': 'Mover contenido a la izquierda.',
                                            'onclick': ''
                                        }).append('<i class="fa fa-long-arrow-left"></i>')
                                    ).append(
                                        $('<button>').attr({
                                            'type': 'button',
                                            'class': 'btn btn-default btn-xs',
                                            'title': 'Mover contenido a la derecha.',
                                            'onclick': ''
                                        }).append('<i class="fa fa-long-arrow-right"></i>')
                                    )
                                )
                            )
                        )
                    );

                    //Cargar combo de atributos
                    load_combo(
                        '#item_detail_attribute_id_' + item_detail_id,
                        'Seleccionar atributo.',
                        attribute_list,
                        ''
                    );

                    //Cargar combo de plantillas
                    load_combo(
                        '#item_detail_template_id_' + item_detail_id,
                        'Seleccionar plantilla.',
                        template_list,
                        ''
                    );

                    //Cargar las siguientes posiciones
                    load_combo(
                        '#item_detail_content_position_next_id_' + item_detail_id,
                        'Seleccionar posición del siguiente elemento.',
                        content_position_next_list,
                        '1'
                    );
                } else {
                    type = 'error';
                    text = 'Ocurrió un problema al procesar el registro.';
                }
            });
        }

        function add_element(parent_id) {

            var url = '/help/item_add/';
            var json = {
                _token: token,
                help_id: help_id,
                parent_id: parent_id,
                item_type_id: 1
            };

            $.post(url, json, function(data, status) {
                var help_id = data.help_id;
                //var parent_id = data.parent_id;
                var item_id = data.item_id;
                var level_id = data.level_id;
                var item_number = data.item_number;
                var message = data.message;
                var error = data.error;
                var type = '';
                var text = '';

                //$query->getBindings()

                if (error == false) {
                    type = 'success';

                    $('#' + parent_id).append(
                        $('<div>').attr({
                            'class': 'box box-default'
                        }).append(
                            $('<div>').attr({
                                'class': 'box-header with-border'
                            }).append(
                                $('<h3>').attr({
                                    'class': 'box-title'
                                }).append('Elemento n° ' + item_number + ' - Nivel ' + level_id)
                            ).append(
                                $('<div>').attr({
                                    'class': 'box-tools pull-right'
                                }).append(
                                    $('<button>').attr({
                                        'type': 'button',
                                        'class': 'btn btn-box-tool',
                                        'title': 'Agregar elemento a este.',
                                        'onclick': 'add_element(' + item_id +
                                            ')' //div_element_add + level + item
                                    }).append('<i class="fa fa-plus-square fa-2x"></i>')
                                ).append(
                                    $('<button>').attr({
                                        'type': 'button',
                                        'class': 'btn btn-box-tool',
                                        'title': 'Guardar cambios.',
                                        'onclick': 'edit_element(' + item_id +
                                            ')'
                                    }).append('<i class="fa fa-save fa-2x"></i>')
                                ).append(
                                    $('<button>').attr({
                                        'type': 'button',
                                        'class': 'btn btn-box-tool',
                                        'title': 'Eliminar elementos.',
                                        'onclick': ''
                                    }).append('<i class="fa fa-times fa-2x"></i>')
                                ).append(
                                    $('<button>').attr({
                                        'type': 'button',
                                        'class': 'btn btn-box-tool',
                                        'title': 'Colapsar elementos.',
                                        'data-widget': 'collapse'
                                    }).append('<i class="fa fa-minus"></i>')
                                )
                            )
                        ).append(
                            $('<div>').attr({
                                'id': item_id,
                                'class': 'box-body'
                            }).append(
                                $('<div>').attr({
                                    'class': 'row',
                                }).append(
                                    $('<div>').attr({
                                        'class': 'col-md-12'
                                    }).append(
                                        $('<div>').attr({
                                            'class': 'callout callout-default',
                                            'style': 'border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px'
                                        }).append(
                                            $('<div>').attr({
                                                'class': 'row',
                                            }).append(
                                                $('<div>').attr({
                                                    'class': 'col-md-3',
                                                }).append(
                                                    $('<label>').append(
                                                        $('<input>').attr({
                                                            'type': 'checkbox',
                                                        })
                                                    ).append(
                                                        '&nbsp;&nbsp; Activar / Inactivar'
                                                    )
                                                )
                                            ).append(
                                                $('<div>').attr({
                                                    'class': 'col-md-3',
                                                }).append(
                                                    $('<div>').attr({
                                                        'class': 'form-group',
                                                    }).append(
                                                        $('<select>').attr({
                                                            'id': 'select_' + item_id,
                                                            'class': 'form-control select2',
                                                        })
                                                    )
                                                )
                                            )
                                        )
                                    )
                                )
                            ).append(
                                $('<div>').attr({
                                    'class': 'row',
                                }).append(
                                    $('<div>').attr({
                                        'class': 'col-md-12'
                                    }).append(
                                        $('<div>').attr({
                                            'class': 'box box-default'
                                        }).append(
                                            $('<div>').attr({
                                                'class': 'box-header with-border'
                                            }).append(
                                                $('<h3>').attr({
                                                    'class': 'box-title'
                                                }).append('Contenido:')
                                            ).append(
                                                $('<div>').attr({
                                                    'class': 'box-tools pull-right'
                                                }).append(
                                                    $('<button>').attr({
                                                        'type': 'button',
                                                        'class': 'btn btn-box-tool',
                                                        'title': 'Agregar contenido a este elemento.',
                                                        'onclick': 'add_content(' + item_id +
                                                            ')'
                                                    }).append('<i class="fa fa-plus-square fa-2x"></i>')
                                                ).append(
                                                    $('<button>').attr({
                                                        'type': 'button',
                                                        'class': 'btn btn-box-tool',
                                                        'title': 'Colapsar elementos.',
                                                        'data-widget': 'collapse'
                                                    }).append('<i class="fa fa-minus"></i>')
                                                )
                                            )
                                        ).append(
                                            $('<div>').attr({
                                                'class': 'box-body'
                                            }).append(
                                                $('<div>').attr({
                                                    'id': 'content_' + item_id,
                                                    'class': 'row'
                                                }).append(

                                                )
                                            )
                                        )
                                    )
                                )
                            )
                        )
                    );


                    //Cargar combo de tipo
                    load_combo(
                        '#select_' + item_id,
                        'Seleccionar tipo.',
                        type_list,
                        ''
                    );

                    //Todo una odisea para el icheck jaja
                    $('#' + item_id).find('input[type="checkbox"]').iCheck({
                        checkboxClass: 'icheckbox_square-grey',
                        radioClass: 'iradio_square-grey'
                    });
                } else {
                    type = 'error';
                    text = 'Ocurrió un problema al procesar el registro.';
                }
            }).error(function(error) {
                console.log('ERROR AL AGREGAR:', error);
            });
        }

        //Modulos
        $.get("/help/help_module", function(data) {
            load_combo(
                '#module_id',
                'Seleccionar módulo.',
                data,
                ''
            );
        });

        //Plantillas
        $.get("/help/help_template", function(data) {
            template_list = data;

            load_combo(
                '#template_id',
                'Seleccionar plantilla.',
                data,
                ''
            );
        });

        //Vistas
        $.get("/help/help_view", function(data) {
            load_combo(
                '#view_id',
                'Seleccionar vista.',
                data,
                ''
            );
        });

        //Traer los tipos de item de una para 
        //no traer cada vez que se agrega un item
        $.get("/help/help_item_type", function(data) {
            type_list = data;
        });

        //Traer los atributos.
        $.get("/help/help_attribute", function(data) {
            attribute_list = data;
        });

        //Traer las posiciones siguientes.
        $.get("/help/help_content_position_next", function(data) {
            content_position_next_list = data;
        });

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

        //Esconder la alerta después de 5 segundos. 
        $(".alert").delay(5000).slideUp(300);

        $('[data-toggle="popover"]').popover();

        //select2 config
        $('.select2').select2({
            width: '99%'
        });

        //Click
        $("#save").click(function() {
            var url = '/help/help_add/';

            var json = {
                _token: token,
                module_id: $('#module_id').val(),
                key: $('#key').val(),
                description: $('#description').val(),
                template_id: $('#template_id').val(),
                view_id: $('#view_id').val()
            };

            $.post(url, json, function(data, status) {
                help_id = data.id;
                var error = data.error;
                var message = data.message;
                var type = '';
                var text = '';

                if (error == true) {
                    type = 'error';
                    text = 'Ocurrió un problema al procesar el registro.';
                } else {
                    type = 'success';
                }

                swal({
                        title: message,
                        text: text,
                        type: type,
                        showCancelButton: false,
                        confirmButtonColor: '#3c8dbc',
                        confirmButtonText: 'Aceptar',
                        cancelButtonText: 'No.',
                        closeOnClickOutside: false
                    },
                    function(isConfirm) {
                        if (isConfirm) {
                            load_add_elements(help_id);
                        }
                    }
                );
            }).error(function(error) {
                console.log('ERROR AL AGREGAR:', error);
            });
        });

        load_add_elements(1);

        

    </script>
@endsection
