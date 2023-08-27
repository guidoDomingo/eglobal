@extends('layout')

@section('title')
    Tablas - Reporte
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
            max-height: 150px;
            min-height: 100px;
            width: 100%
        }

    </style>

    <?php
    //Variables que se usan en todo el blade.
    $information_schema_columns = $data['lists']['information_schema_columns'];
    $primary_keys_count = $data['primary_keys_count'];
    $query_generated = $data['query_generated'];
    $records = $data['lists']['records'];
    $columns_one_hide = $data['lists']['columns_one_hide'];
    $columns_in_form = [];
    $columns_in_form_search = [];
    ?>

    <section class="content-header">

        <div class="row">
            <div class="col-md-12">
                @include('partials._flashes')
            </div>
        </div>

        <div class="box box-default" style="border-radius: 5px;">
            <div class="box-header with-border">
                <h3 class="box-title" style="font-size: 25px;">Tabla - Reporte
                </h3>
                <div class="box-tools pull-right">
                    <button class="btn btn-info" type="button" title="Buscar según los filtros en los registros."
                        style="margin-right: 5px" id="search" name="search" onclick="search()">
                        <span class="fa fa-search"></span> Buscar
                    </button>

                    @if (count($information_schema_columns) > 0)
                        <button class="btn btn-default" type="button" title="Agregar un nuevo registro a la tabla."
                            style="margin-right: 5px" id="add" name="add" onclick="add()">
                            <span class="fa fa-plus"></span> Agregar
                        </button>
                    @endif

                    @if (count($records) > 0)
                        <button class="btn btn-success" type="button" title="Convertir tabla en archivo excel."
                            id="generate_x" name="generate_x">
                            <span class="fa fa-file-excel-o"></span> Exportar
                        </button>
                    @endif

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

                    {!! Form::open(['route' => 'info_table', 'method' => 'POST', 'role' => 'form', 'id' => 'form_search']) !!}
                    <div class="row">
                        <div class="col-md-6">
                            <label for="tabla_id">Seleccionar Tabla:</label>
                            <div class="form-group">
                                <select name="table_id" id="table_id" class="select2" style="width: 100%"></select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label><b>Observación:</b></label>
                            <div class="form-group">
                                Solo se obtendrá un máximo de 1000 registros, para obtener otros registros utilizar
                                los
                                filtros de columnas
                            </div>
                        </div>
                    </div>

                    @if ($query_generated !== null)
                        <div class="box box-default collapsed-box" style="border: 1px solid #d2d6de;">
                            <div class="box-header with-border">
                                <h3 class="box-title">Consulta generada</h3>
                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                            class="fa fa-plus"></i></button>
                                </div>
                            </div>
                            <div class="box-body">
                                {{ $query_generated }}
                            </div>
                        </div>
                    @endif


                    @if (count($information_schema_columns) > 0)
                        <div class="box box-default collapsed-box" style="border: 1px solid #d2d6de;">
                            <div class="box-header with-border">
                                <h3 class="box-title">Buscar por las columnas de la tabla</h3>
                                <div class="box-tools pull-right">
                                    <button type="button" class="btn btn-box-tool" data-widget="collapse"
                                        id="search_open"><i class="fa fa-plus"></i></button>
                                </div>
                            </div>
                            <div class="box-body">
                                <div class="row">
                                    @foreach ($information_schema_columns as $item)
                                        <?php
                                        $icon = '';
                                        $text = '';
                                        $required = '';
                                        $disabled = '';
                                        $placeholder = '';
                                        $class = 'form-control';
                                        $col_md_number = '4';
                                        
                                        $column_name = $item->column_name;
                                        $id = "search_by_$item->column_name";
                                        $data_type = $item->data_type;
                                        $character_maximum_length = $item->character_maximum_length;
                                        $numeric_precision = $item->numeric_precision;
                                        $is_nullable = $item->is_nullable;
                                        $key_type = $item->key_type;
                                        $ordinal_position = $item->ordinal_position;
                                        
                                        $reference_table = $item->reference_table;
                                        
                                        $reference_rows_count = $item->reference_rows_count;
                                        
                                        if ($numeric_precision == 0) {
                                            $numeric_precision = 'N';
                                        }
                                        
                                        if ($character_maximum_length == 0) {
                                            $character_maximum_length = 'N';
                                        }
                                        
                                        if ($is_nullable) {
                                            $required = 'required';
                                        }
                                        
                                        if ($data_type == 'smallint' or $data_type == 'integer' or $data_type == 'numeric' or $data_type == 'bigint' or $data_type == 'double precision') {
                                            $data_type = 'number';
                                            $placeholder = "Númerico de 1 a $numeric_precision dígitos.";
                                            $text = 'N';
                                        } elseif ($data_type == 'character') {
                                            $data_type = 'text';
                                            $icon = 'fa fa-pencil-square-o';
                                            $placeholder = "Texto de 1 a $character_maximum_length alfanuméricos.";
                                        } elseif ($data_type == 'character varying') {
                                            $data_type = 'text';
                                            $icon = 'fa fa-pencil-square-o';
                                            $placeholder = "Texto de 1 a $character_maximum_length alfanuméricos.";
                                        } elseif ($data_type == 'text') {
                                            $data_type = 'text_area';
                                            $icon = 'fa fa-pencil-square-o';
                                            $placeholder = "Texto de 1 a $character_maximum_length alfanuméricos.";
                                        } elseif ($data_type == 'timestamp without time zone') {
                                            $data_type = 'text'; //tipo text para convertir en dos calendarios
                                            $icon = 'fa fa-calendar';
                                            $col_md_number = '4';
                                            $placeholder = 'Rangos desde - hasta para fecha y hora .';
                                            $class .= ' timestamp';
                                        } elseif ($data_type == 'date') {
                                            $data_type = 'date';
                                            $icon = 'fa fa-calendar';
                                        } elseif ($data_type == 'time without time zone') {
                                            $data_type = 'time';
                                            $icon = 'fa fa-clock-o';
                                        } elseif ($data_type == 'boolean') {
                                            $data_type = 'checkbox';
                                            $icon = 'fa fa-check';
                                            $class = '';
                                        } elseif ($data_type == 'ARRAY') {
                                            $data_type = 'text';
                                            $icon = 'fa fa-list';
                                            $placeholder = "Introduce una lista por ejemplo: {1,2,3} o {'A','B','C'}.";
                                        } elseif ($data_type == 'json') {
                                            $data_type = 'text';
                                            $icon = 'fa fa-json';
                                            $placeholder = 'Introduce un json.';
                                            $text = '{}';
                                        }
                                        
                                        $color = 'black';
                                        
                                        //$key_type = '';
                                        
                                        /*if ($key_type !== 'NO_KEY') {
                                                                                    if ($key_type == 'PRIMARY KEY') {
                                                                                        //$disabled = 'disabled';
                                                                                        $key_type = '( PK )';
                                                                                        $icon = 'fa fa-key';
                                                                                        $text = '';
                                                                                        $color = '#f39c12';
                                                                                    } elseif ($key_type == 'FOREIGN KEY') {
                                                                                        $key_type = '( FK )';
                                                                                        $icon = 'fa fa-key';
                                                                                        $text = '';
                                                                                        $color = '#337ab7';
                                                                                    } elseif ($key_type == 'UNIQUE') {
                                                                                        $key_type = '( UNIQUE )';
                                                                                    }
                                                                                } else {
                                                                                    $key_type = '';
                                                                                }*/
                                        
                                        ?>

                                        @if (!in_array($column_name, $columns_in_form_search))
                                            <div class="col-md-{{ $col_md_number }}" style="margin-top: 20px"
                                                ordinal-position="ordinal_position_{{ $ordinal_position }}">

                                                <label for="{{ $id }}">Buscar por
                                                    <b>{{ $column_name }} </b>:</label>

                                                @if ($key_type !== 'FOREIGN KEY')
                                                    <div class="input-group" id="input_group_{{ $column_name }}">
                                                        <span class="input-group-addon"
                                                            id="input_group_addon_{{ $column_name }}"
                                                            style="color: {{ $color }}">
                                                            <b>
                                                                <i class="{{ $icon }}"></i>{{ $text }}
                                                            </b>
                                                        </span>

                                                        @if ($data_type == 'checkbox')
                                                            <div style="border: 1px solid #d2d6de; padding: 5px"
                                                                id="div_{{ $id }}">
                                                                <input id="{{ $id }}" name="{{ $id }}"
                                                                    type="checkbox"
                                                                    class="{{ $class }} checkbox icheck">
                                                                &nbsp; Activo / Inactivo
                                                            </div>
                                                        @elseif ($data_type == 'text_area')
                                                            <textarea rows="4" cols="30"
                                                                class="{{ $class }}" id="{{ $id }}"
                                                                name="{{ $id }}"
                                                                placeholder="{{ $placeholder }}"
                                                                value=""></textarea>
                                                            @else
                                                                <input type="{{ $data_type }}"
                                                                    class="{{ $class }}" id="{{ $id }}"
                                                                    name="{{ $id }}"
                                                                    placeholder="{{ $placeholder }}"></input>
                                                        @endif
                                                    </div>
                                                @elseif ($key_type == 'FOREIGN KEY')
                                                    @if ($reference_rows_count > 0)
                                                        <small> {{ $reference_rows_count }} registros
                                                            disponibles.</small>
                                                        <input type="text" class="{{ $class }}"
                                                            id="{{ $id }}" name="{{ $id }}"
                                                            placeholder="Opciones de {{ $reference_table }}"
                                                            title="Seleccionar una opción de {{ $reference_table }}"></input>
                                                    @else
                                                        <small> {{ $reference_rows_count }} registros.</small>
                                                        <input type="text" class="{{ $class }}"
                                                            id="{{ $id }}" name="{{ $id }}"
                                                            placeholder="Ingresar id de la tabla {{ $reference_table }}"
                                                            title="Se obtuvo la clave foranea pero la tabla no cuenta con registros, las razones pueden ser por los filtros: 1) status = true 2) deleted_at != null o 3) La tabla no tiene registros."></input>
                                                    @endif
                                                @endif
                                            </div>

                                            <?php array_push($columns_in_form_search, $column_name); ?>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                    {!! Form::close() !!}

                    @if (count($information_schema_columns) > 0)
                        <div class="box box-default collapsed-box" style="border: 1px solid #d2d6de;">
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

                        <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_1">
                            <thead>
                                <tr>
                                    <th>Opción</th>

                                    @foreach ($information_schema_columns as $item)
                                        <th>{{ $item->column_name }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($records as $sub_key)
                                    <?php
                                    $sub_key_aux = json_encode($sub_key);
                                    ?>

                                    <tr>
                                        <td>
                                            <button class="btn btn-default" title="Editar registro."
                                                style="border-radius: 3px;" onclick="edit({{ $sub_key_aux }})">
                                                <i class="fa fa-pencil"></i>
                                            </button>
                                        </td>
                                        @foreach ($information_schema_columns as $key => $value)
                                            <?php
                                            if ($value->data_type == 'boolean') {
                                                $sub_key[$value->column_name] = $sub_key[$value->column_name] ? 'Activo' : 'Inactivo';
                                            } elseif ($value->data_type == 'timestamp without time zone' and $sub_key[$value->column_name] !== null) {
                                                $sub_key[$value->column_name] = date('d/m/Y h:i:s', strtotime($sub_key[$value->column_name]));
                                            } elseif ($value->data_type == 'date') {
                                                $sub_key[$value->column_name] = date('d/m/Y', strtotime($sub_key[$value->column_name]));
                                            }
                                            ?>

                                            <td> {{ $sub_key[$value->column_name] }} </td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="content">

        <!-- Modal -->
        <div id="modal" class="modal fade" role="dialog" tabindex="-1" data-backdrop="static" data-keyboard="false">
            <div class="modal-dialog modal-dialog-centered" role="document"
                style="background: white; border-radius: 5px; width: 99%; heigth: 400px">
                <!-- Modal content-->
                <div class="modal-content" style="border-radius: 10px">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                        <div class="modal-title" style="font-size: 20px;" id="modal_title">

                        </div>
                    </div>
                </div>

                <div class="modal-body">
                    @if (count($information_schema_columns) > 0)
                        <div class="row">
                            <div class="col-md-12">
                                <label><b>Observación:</b></label>
                                <div class="form-group">
                                    1) Las columnas: created_at, updated_at, deleted_at, created_by, updated_by,
                                    deleted_by no aparecerán aquí. <br />
                                    2) Las columnas serán actualizados automaticamente según sea el caso: created_at y
                                    created_by (insert), updated_at y updated_by (update), deleted_at y (deleted_by
                                    soft-delete). <br />
                                    3) Si la tabla tiene PK (Primary key) no aparecerá aquí, al menos que sea una primary
                                    key compuesta si aparecerá.
                                </div>
                            </div>
                        </div>
                        <div class="row" id="form_modal">
                            @foreach ($information_schema_columns as $item)
                                <?php
                                $icon = '';
                                $text = '';
                                $required = '';
                                $disabled = '';
                                $placeholder = '';
                                $class = 'form-control';
                                $col_md_number = '4';
                                
                                $column_name = $item->column_name;
                                $data_type = $item->data_type;
                                $character_maximum_length = $item->character_maximum_length;
                                $numeric_precision = $item->numeric_precision;
                                $is_nullable = $item->is_nullable;
                                $key_type = $item->key_type;
                                $ordinal_position = $item->ordinal_position;
                                
                                $reference_table = $item->reference_table;
                                
                                $reference_rows_count = $item->reference_rows_count;
                                
                                if ($numeric_precision == 0) {
                                    $numeric_precision = 'N';
                                }
                                
                                if ($character_maximum_length == 0) {
                                    $character_maximum_length = 'N';
                                }
                                
                                if ($is_nullable == 'NO') {
                                    $required = 'required';
                                    $is_nullable = ' (Requerido)';
                                } else {
                                    $is_nullable = '';
                                }
                                
                                if ($key_type == 'PRIMARY KEY') {
                                    //$disabled = 'disabled';
                                    //$key_type = '( PK )';
                                } elseif ($key_type == 'FOREIGN KEY') {
                                }
                                
                                if ($data_type == 'smallint' or $data_type == 'integer' or $data_type == 'numeric' or $data_type == 'bigint' or $data_type == 'double precision') {
                                    $data_type = 'number';
                                    $placeholder = "Númerico de 1 a $numeric_precision digitos.";
                                    $text = 'N';
                                } elseif ($data_type == 'character') {
                                    $data_type = 'text';
                                    $icon = 'fa fa-pencil-square-o';
                                    $placeholder = "Texto de 1 a $character_maximum_length alfanuméricos.";
                                } elseif ($data_type == 'character varying') {
                                    $data_type = 'text';
                                    $icon = 'fa fa-pencil-square-o';
                                    $placeholder = "Texto de 1 a $character_maximum_length alfanuméricos.";
                                } elseif ($data_type == 'text') {
                                    $data_type = 'text_area';
                                    $icon = 'fa fa-pencil-square-o';
                                    $placeholder = "Texto de 1 a $character_maximum_length alfanuméricos.";
                                } elseif ($data_type == 'timestamp without time zone') {
                                    $data_type = 'datetime-local';
                                    $icon = 'fa fa-calendar';
                                } elseif ($data_type == 'date') {
                                    $data_type = 'date';
                                    $icon = 'fa fa-calendar';
                                } elseif ($data_type == 'time without time zone') {
                                    $data_type = 'time';
                                    $icon = 'fa fa-clock-o';
                                } elseif ($data_type == 'boolean') {
                                    $data_type = 'checkbox';
                                    $icon = 'fa fa-check';
                                    $class = '';
                                } elseif ($data_type == 'ARRAY') {
                                    $data_type = 'text';
                                    $icon = 'fa fa-list';
                                    $placeholder = 'Introduce una lista.';
                                } elseif ($data_type == 'json') {
                                    $data_type = 'text';
                                    $icon = 'fa fa-json';
                                    $placeholder = 'Introduce un json.';
                                    $text = '{ }';
                                }
                                
                                ?>

                                @if (($key_type !== 'PRIMARY KEY' or $primary_keys_count > 1) and !in_array($column_name, $columns_one_hide) and !in_array($column_name, $columns_in_form))
                                    <div class="col-md-{{ $col_md_number }}" style="margin-top: 20px"
                                        ordinal-position="ordinal_position_{{ $ordinal_position }}">

                                        <label for="{{ $column_name }}">{{ $column_name }} <small
                                                style="color: red">{{ $is_nullable }}</small>:</label>

                                        @if ($key_type !== 'FOREIGN KEY')
                                            <div class="input-group">
                                                <span class="input-group-addon">
                                                    <b id="icon_text_{{ $column_name }}">
                                                        <i class="{{ $icon }}"></i>{{ $text }}
                                                    </b>
                                                </span>

                                                @if ($data_type == 'checkbox')
                                                    <div style="border: 1px solid #d2d6de; padding: 5px">
                                                        <input id="{{ $column_name }}" name="{{ $column_name }}"
                                                            type="checkbox" class="checkbox icheck"> &nbsp; Activar /
                                                        Inactivar
                                                    </div>
                                                @elseif ($data_type == 'text_area')
                                                    <textarea rows="4" cols="30" class="{{ $class }}" id="{{ $column_name }}" name="{{ $column_name }}"
                                                        placeholder="{{ $placeholder }}" {{ $required }}
                                                        {{ $disabled }} value=""></textarea>
                                                @else
                                                    <input type="{{ $data_type }}" class="{{ $class }}"
                                                        id="{{ $column_name }}" name="{{ $column_name }}"
                                                        placeholder="{{ $placeholder }}" {{ $required }}
                                                        {{ $disabled }} value=""></input>
                                                @endif
                                            </div>
                                        @elseif ($key_type == 'FOREIGN KEY')
                                            @if ($reference_rows_count > 0)
                                                <small> {{ $reference_rows_count }} registros disponibles.</small>
                                                <div class="form-group">
                                                    <select class="form-control select2" class="{{ $class }}" id="{{ $column_name }}"
                                                    name="{{ $column_name }}"
                                                    placeholder="Opciones de {{ $reference_table }}"
                                                    title="Seleccionar una opción de {{ $reference_table }}"
                                                    sub_type="combo" {{ $required }} {{ $disabled }}></select>
                                                </div>
                                            @else
                                                <small> {{ $reference_rows_count }} registros.</small>
                                                <input type="text" class="{{ $class }}" id="{{ $column_name }}"
                                                    name="{{ $column_name }}"
                                                    placeholder="Ingresar id de la tabla {{ $reference_table }}"
                                                    title="Se obtuvo la clave foranea pero la tabla no cuenta con registros, las razones pueden ser por los filtros: 1) status = true 2) deleted_at != null o 3) La tabla no tiene registros."
                                                    {{ $required }} {{ $disabled }}></input>
                                            @endif
                                        @endif
                                    </div>

                                    <?php array_push($columns_in_form, $column_name); // Validación extra para las columnas que tienen dos tipos de claves. Ejemplo (FOREIGN KEY y UNIQUE) ?>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
                <div class="modal-footer">
                    <div style="float:right">
                        <div class="btn-group mr-2" role="group">
                            <button class="btn btn-success" title="Confirma la lista de registros a actualizar."
                                id="confirm" onclick="save()" style="margin-right: 5px" title="Guardar datos">
                                <span class="fa fa-save" aria-hidden="true"></span>
                                &nbsp; Guardar
                            </button>

                            &nbsp;

                            <button class="btn btn-danger" title="Salir de esta ventana." data-dismiss="modal">
                                <span class="fa fa-close" aria-hidden="true"></span>
                                &nbsp; Cancelar
                            </button>
                        </div>
                    </div>
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

    <!-- Iniciar objetos -->
    <script type="text/javascript">
        var ids = [];
        var add_new = false;
        var table_id = null;

        @if (count($information_schema_columns) > 0)
            ids = {!! json_encode($information_schema_columns) !!};
        @endif

        function search() {

            var table_id_aux = $('#table_id').val();

            if (table_id_aux !== '') {
                $('#content').css('display', 'none');
                $('#div_load').css('display', 'block');

                console.log(ids.length, table_id, $('#table_id').val());

                if (ids.length > 0 && table_id == $('#table_id').val()) {

                    //no hacer ninguna conversión aqui, simplemente enviar todo como está y convertir en el backend

                    for (var i = 0; i < ids.length; i++) {
                        var item = ids[i];
                        var column_name = item.column_name;
                        var id = '#search_by_' + column_name;
                        var data_type = item.data_type;

                        if (data_type == 'boolean') {
                            if ($(id).val() == 'selected') {
                                ids[i].search_value = $(id).is(':checked');
                            }
                        } else {
                            ids[i].search_value = $(id).val();
                        }

                        if (data_type == 'text') {
                            console.log(ids[i]);
                        }
                    }

                    

                    ids = JSON.stringify(ids);

                    var input = $('<input>').attr({
                        'type': 'hidden',
                        'id': 'ids',
                        'name': 'ids'
                    });

                    $('#form_search').append(input);

                    $('#ids').val(ids);
                }

                $('#form_search').submit();
            } else {
                swal({
                        title: 'Atención',
                        text: 'Seleccionar la tabla',
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

        function save() {

            var table_id_aux = $('#table_id').val();

            if (table_id_aux !== '') {

                console.log(ids.length, table_id, $('#table_id').val());

                if (ids.length > 0 && table_id == $('#table_id').val()) {

                    //no hacer ninguna conversión aqui, simplemente enviar todo como está y convertir en el backend

                    for (var i = 0; i < ids.length; i++) {
                        var item = ids[i];
                        var column_name = item.column_name;
                        var id = '#' + column_name;
                        var data_type = item.data_type;

                        /*if () {

                        }*/

                        if (data_type == 'boolean') {
                            if ($(id).val() == 'selected') {
                                ids[i].new_value = $(id).is(':checked');
                            }
                        } else {
                            ids[i].new_value = $(id).val();
                        }

                        console.log(column_name, ids[i].new_value);
                    }

                    //ids = JSON.stringify(ids);

                    var input = $('<input>').attr({
                        'type': 'hidden',
                        'id': 'ids',
                        'name': 'ids'
                    });

                    console.log('IDS:', ids);

                    var url = '/info_table_save/';

                    var json = {
                        _token: token,
                        table_id: table_id,
                        ids: ids
                    };

                    $.post(url, json, function(data, status) {
                        var error = data.error;
                        var text = data.message;
                        var title = '';
                        var type = '';

                        if (error == true) {
                            type = 'error';
                            title = 'Ocurrió un error.';
                        } else {
                            type = 'success';
                            title = 'Acción exitosa.';
                        }

                        swal({
                                title: title,
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
                                    $('#modal').modal('hide');
                                }
                            }
                        );
                    }).error(function(error) {
                        console.log('ERROR AL AGREGAR:', error);
                    });
                }
            } else {
                swal({
                        title: 'Atención',
                        text: 'Seleccionar la tabla',
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

        function add() {

            add_new = true;

            for (var i = 0; i < ids.length; i++) {
                var item = ids[i];
                var column_name = item.column_name;
                var combo = item.combo;
                var id = '#' + column_name;

                $(id).val(null);

                if ((combo) && 
                    (column_name !== 'created_by' && 
                     column_name !== 'updated_by' && 
                     column_name !== 'deleted_by')) {
                    $(id).selectize()[0].selectize.setValue("", false);
                }
            }

            var now = new Date();
            var day = ('0' + now.getDate()).slice(-2);
            var month = ('0' + (now.getMonth() + 1)).slice(-2);
            var date = now.getFullYear() + '-' + (month) + '-' + (day);
            var time = ('0' + (now.getHours())).slice(-2) + ':' + ('0' + (now.getMinutes())).slice(-2) + ':' + ('0' + (now
                .getSeconds())).slice(-2);

            //console.log('date:', date);
            //console.log('time:', time);
            //console.log('datetime-local:', date + 'T' + time);

            $('#form_modal :input[type="checkbox"]').iCheck('check');

            $('#form_modal :input[type="date"]').val(date);

            $('#form_modal :input[type="time"]').val(time);

            $('#form_modal :input[type="datetime-local"]').val(date + 'T' + time);

            $('#modal_title').html('Agregar nuevo registro a <b>' + table_id + '</b>: ');
            $('#modal').modal('show');
        }
        // Función que se ejecuta al oprimir el botón de editar,
        // agrega los valores de la fila a los campos respectivos.
        function edit(item_row) {

            $('#modal_title').html('Modificar el registro:');

            //console.log('FUNCIONA.');

            /*for (var i = 0; i < item_row.length; i++) {
                //console.log('item:', item_row[i]);
            }

            $.each(item_row, function(column, value) {
                //display the key and value pair
                //console.log(column + ': ' + value);

                //$('#' + column).val(value);

                for (var i = 0; i < ids.length; i++) {

                    var item = ids[i];
                    var column_name = item.column_name;
                    var id = '#' + column_name;
                    var data_type = item.data_type;

                    if (column == ids[i].column_name) {
                        //console.log('item:', item);
                        // $(id).val(ids[i].old_value);
                    }
                }
            });*/

            /*for (var i = 0; i < ids.length; i++) {
                var id = '#' + ids[i].column_name;

                ids[i].new_value = $(id).val();

                $(id).val(ids[i].old_value);

                console.log('item:', ids[i].column_name, ids[i].old_value, ids[i].new_value);
            }*/


            for (var i = 0; i < ids.length; i++) {
                var item = ids[i];
                var column_name = item.column_name;
                var id = '#' + column_name;
                var data_type = item.data_type;

                if (column == ids[i].column_name) {
                    console.log('item:', item);
                    $(id).val(ids[i].old_value);
                }
            }

            $('input[type="time"]').val('00:00:00.10');

            $('input[type="datetime-local"]').val(new Date().toJSON().slice(0, 19));

            $('#modal').modal('show');
        }

        function save1() {

            var mode = 'insert';

            if (add_new) {
                add_new = false;


            } else {
                mode = 'update';
            }

            //console.log('NUEVOS VALORES:');

            for (var i = 0; i < ids.length; i++) {
                var item = ids[i];
                var column_name = item.column_name;
                var id = '#' + column_name;
                var data_type = item.data_type;

                if (data_type == 'boolean') {
                    ids[i].new_value = $(id).is(':checked');
                } else if (data_type == 'timestamp without time zone') {
                    ids[i].new_value = $(id).val().replace('T', ' ');
                } else {
                    ids[i].new_value = $(id).val();
                }

                //console.log(ids[i].column_name + ' = ' + ids[i].new_value);
            }

            swal({
                    title: 'Atención',
                    text: 'Está apunto de modificar el registro... Continuar?',
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#0073b7',
                    confirmButtonText: 'Aceptar',
                    cancelButtonText: 'Cancelar',
                    closeOnClickOutside: false,
                    showLoaderOnConfirm: false
                },
                function(isConfirmMessage) {
                    if (isConfirmMessage) {

                        var url = 'info_table_save/';
                        var json = {
                            _token: token,
                            table_id: $('#table_id').val(),
                            mode: mode,
                            ids: ids
                        };

                        $.post(url, json, function(data, status) {
                            var error = data.error;
                            var message = data.message;
                            var type = '';
                            var text = '';

                            if (error == true) {
                                type = 'error';
                                text = 'Ocurrió un problema al modificar el registro.';
                            } else {
                                type = 'success';
                            }

                            //console.log('data:', data);

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
                                function(isConfirmSearch) {
                                    if (isConfirmSearch) {
                                        location.reload();
                                    }
                                }
                            );
                        }).error(function(error) {
                            //console.log('ERROR AL MODIFICAR REGISTRO:', error);
                        });
                    }
                }
            );
        }


        //Esconder la alerta después de 5 segundos. 
        $(".alert").delay(5000).slideUp(300);
        $('[data-toggle="popover"]').popover();

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

        @for ($i = 0; $i < count($data['combos']); $i++)
            <?php
            $id = $data['combos'][$i]['id'];
            $list = $data['combos'][$i]['list'];
            $value = $data['combos'][$i]['value'];
            
            /**
             * Si campo inicialmente quedó como númerico, aquí se transforma en combo dependiendo si tiene registros la tabla FK
             */
            
            ?>

            @for ($j = 0; $j < count($list); $j++)
                <?php
                $sub_item = $list[$j];
                $sub_item_id = $sub_item->id;
                $sub_item_description = $sub_item['description'];
                
                /**
                 * Si campo inicialmente quedó como númerico, aquí se transforma en combo dependiendo si tiene registros la tabla FK
                 */
                
                ?>
            

                console.log('sub_item description:', "{{ $sub_item_description }}");
            @endfor
        @endfor

        console.log('COMBOS:');

        @foreach ($information_schema_columns as $item)
            <?php
            $id = $item->column_name;
            $list = $item->list;
            $search_value = $item->search_value;
            $combo = $item->combo;
            
            /**
             * Si campo inicialmente quedó como númerico, aquí se transforma en combo dependiendo si tiene registros la tabla FK
             */
            
            ?>
        
            @if ($combo)
                @if ($id !== 'created_by' and $id !== 'updated_by' and $id !== 'deleted_by')
                    $('#{{ $id }}').selectize(selective_config)[0].selectize.addOption({!! $list !!});
            
                    $('#{{ $id }}').selectize()[0].selectize.setValue("{{ $search_value }}", false);
                @endif
            
                $('#search_by_{{ $id }}').selectize(selective_config)[0].selectize.addOption({!! $list !!});
            
                $('#search_by_{{ $id }}').selectize()[0].selectize.setValue("{{ $search_value }}", false);
            @endif
        @endforeach

        var data_table_config = {
            //custom
            //orderCellsTop: true,
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
            //processing: true,
            //ordering: false,
            //order: [[ 5, "asc" ]],
            initComplete: function(settings, json) {
                $('#content').css('display', 'block');
                $('#div_load').css('display', 'none');
                $('body > div.wrapper > header > nav > a').trigger('click');
            }
        }

        var table = $('#datatable_1').DataTable(data_table_config);

        @if (count($information_schema_columns) <= 0)
            $('#content').css('display', 'block');
            $('#div_load').css('display', 'none');
            $('body > div.wrapper > header > nav > a').trigger('click');
        @endif

        $('#hide_show_columns').append('Ocultar columna/s de la tabla: <br/>');

        var hide_show_columns = [];

        var ths = $("#datatable_1").find("th");

        var index = 0;

        for (var i = index; i < ths.length; i++) {
            hide_show_columns.push(ths[i].innerHTML);
        }

        for (var i = index; i < hide_show_columns.length; i++) {

            var description = hide_show_columns[i];

            if (description !== 'Opción') {
                $('#hide_show_columns').append(
                    '<a class="toggle-vis btn btn-default btn-sm" data-column="' + i + '" id="toggle-vis-' + i +
                    '" value="' + description + '" state="on" title="Mostrar / Ocultar columna: ' + description +
                    '" style="margin-top: 3px">' +
                    '<i class="fa fa-eye"></i> &nbsp;' + description +
                    '</a> '
                );
            }
        }

        /*$('#hide_show_columns').append('<br/><br/> Ocultar todas las columnas que son: <br/>');

        var hide_show_columns_end = hide_show_columns.length;

        //Columnas que son llaves
        for (var i = 0; i < ids.length; i++) {
            var item = ids[i];
            var key_type = item.key_type;
            var exists_key_type = false;

            for (var j = index; j < hide_show_columns.length; j++) {
                if (key_type == hide_show_columns[j]) {
                    exists_key_type = true;
                }
            }

            if (exists_key_type == false && key_type !== 'NO_KEY') {
                hide_show_columns.push(key_type);
            }
        }

        //Columnas que no son llaves
        for (var i = 0; i < ids.length; i++) {

            //console.log('FUNCIONA');
            var item = ids[i];
            var data_type = item.data_type;
            var exists_data_type = false;

            for (var j = index; j < hide_show_columns.length; j++) {
                if (data_type == hide_show_columns[j]) {
                    exists_data_type = true;
                }
            }

            if (exists_data_type == false) {
                hide_show_columns.push(data_type);
            }
        }

        for (var i = hide_show_columns_end; i < hide_show_columns.length; i++) {

            var description = hide_show_columns[i];

            $('#hide_show_columns').append(
                '<a class="toggle-vis btn btn-info btn-sm" data-column="' + i + '" id="toggle-vis-' + i +
                '" value="' + description + '" state="on" title="Mostrar / Ocultar columna: ' + description +
                '" style="margin-top: 3px">' +
                '<i class="fa fa-eye"></i> &nbsp;' + description +
                '</a> '
            );
        }*/


        $('a.toggle-vis').on('click', function(e) {
            e.preventDefault();

            var data_column = $(this).attr('data-column');
            var column_description = $(this).attr('value');
            var state = $(this).attr('state');
            var column_visible = false;
            var special = true;

            for (var i = 0; i < ids.length; i++) {
                var item = ids[i];
                var column_name = item.column_name;

                if (column_description == column_name) {
                    special = false;
                    break;
                }
            }

            if (special) {

                if (state == 'on') {
                    state = 'off';
                    column_visible = false;
                } else {
                    state = 'on';
                    column_visible = true;
                }

                //console.log('state:', state, column_visible);

                $(this).attr('state', state);

                for (var i = 0; i < ids.length; i++) {
                    var item = ids[i];
                    var column_name = item.column_name;
                    var data_type = item.data_type;
                    var key_type = item.key_type;

                    for (var j = index; j < hide_show_columns.length; j++) {
                        if (column_name == hide_show_columns[j] && (key_type == column_description || data_type ==
                                column_description)) {
                            var column = table.column(j);
                            column_visible_aux = column.visible();
                            column.visible(column_visible);

                            if (column_visible_aux) {
                                $('#toggle-vis-' + j).css('display', 'none');
                            } else {
                                $('#toggle-vis-' + j).css('display', 'inline-block');
                            }

                            //var fa = (column_visible_aux) ? 'eye-slash' : 'eye';
                            //$('#toggle-vis-' + j).html('<i class="fa fa-' + fa + '"></i> &nbsp;' + column_name);
                        }
                    }
                }

                if (column_visible) {
                    column_visible = false;
                } else {
                    column_visible = true;
                }
            } else {
                var column = table.column(data_column);
                column_visible = column.visible();
                column.visible(!column_visible);

                for (var i = 0; i < ids.length; i++) {
                    var item = ids[i];
                    var column_name = item.column_name;
                    var data_type = item.data_type;
                    var key_type = item.key_type;

                    //El botón oprimido que tipo es ? 
                    if (column_name == column_description) {



                        for (var j = 0; j < ids.length; j++) {
                            var item = ids[j];
                            var column_name_aux = item.column_name;
                            var data_type_aux = item.data_type;
                            var key_type_aux = item.key_type;

                            if (key_type == key_type_aux) {

                            }
                        }

                        for (var j = index; j < hide_show_columns.length; j++) {

                            if (data_type_aux == hide_show_columns[j]) {
                                /*var column = table.column(j);
                                                    column_visible_aux = column.visible();
                                                    column.visible(column_visible);
                        
                                                    if (column_visible_aux) {
                                                        $('#toggle-vis-' + j).css('display', 'none');
                                                    } else {
                                                        $('#toggle-vis-' + j).css('display', 'inline-block');
                                                    }*/

                                //var fa = (column_visible_aux) ? 'eye-slash' : 'eye';
                                //$('#toggle-vis-' + j).html('<i class="fa fa-' + fa + '"></i> &nbsp;' + column_name);
                            }

                        }

                        for (var j = index; j < hide_show_columns.length; j++) {


                            if (data_type_aux == hide_show_columns[j]) {
                                /*var column = table.column(j);
                                                    column_visible_aux = column.visible();
                                                    column.visible(column_visible);
                        
                                                    if (column_visible_aux) {
                                                        $('#toggle-vis-' + j).css('display', 'none');
                                                    } else {
                                                        $('#toggle-vis-' + j).css('display', 'inline-block');
                                                    }*/

                                //var fa = (column_visible_aux) ? 'eye-slash' : 'eye';
                                //$('#toggle-vis-' + j).html('<i class="fa fa-' + fa + '"></i> &nbsp;' + column_name);
                            }

                        }
                    }
                }
            }

            //console.log('COLUMNA VISIBLE', column_visible, column_description, state);

            var fa = (column_visible) ? 'eye-slash' : 'eye';

            console.log(column_visible, column_description, state, fa);

            $(this).html('<i class="fa fa-' + fa + '"></i> &nbsp;' + column_description);
        });

        var daterangepicker_config = {
            'format': 'DD/MM/YYYY HH:mm:ss',
            'startDate': moment().startOf('month'),
            'endDate': moment().endOf('month'),
            'timePicker': true,
            'opens': 'center',
            'drops': 'down',
            'ranges': {
                'Hoy': [moment().startOf('day').toDate(), moment().endOf('day').toDate()],
                'Ayer': [moment().startOf('day').subtract(1, 'days'), moment().endOf('day').subtract(1, 'days')],
                'Semana': [moment().startOf('week'), moment().endOf('week')],
                'Mes': [moment().startOf('month'), moment().endOf('month')],
                'Año': [moment().startOf('year'), moment().endOf('year')]
            },
            'locale': {
                'applyLabel': 'Aplicar',
                'fromLabel': 'Desde',
                'toLabel': 'Hasta',
                'customRangeLabel': 'Rango Personalizado',
                'daysOfWeek': ['Dom', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie', 'Sáb'],
                'monthNames': ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto',
                    'Setiembre', 'Octubre', 'Noviembre', 'Diciembre'
                ],
                'firstDay': 1
            }
        }

        //$("[data-widget='collapse']").click();

        $('.timestamp').daterangepicker(daterangepicker_config);

        $('.timestamp').attr({
            'onkeydown': 'return false',
        });

        $('input[type="checkbox"]').iCheck({
            checkboxClass: 'icheckbox_square-grey',
            radioClass: 'iradio_square-grey'
        });

        $('input[type="checkbox"]').on('ifChecked', function(e) {
            $(this).val('selected');
        });

        //console.log('information_schema_columns:');

        var search_open = false;

        for (var i = 0; i < ids.length; i++) {
            var item = ids[i];
            var column_name = item.column_name;
            var id = '#search_by_' + column_name;
            var id_input_group = '#input_group_' + column_name;
            var data_type = item.data_type;
            var value = item.search_value;

            if (value !== null && value !== '') {
                if (data_type == 'boolean') {
                    if (value == true) {
                        $(id).iCheck('check');
                    }

                    value = 'selected';
                }

                $(id).val(value);

                ids[i].search_value = value;

                $(id_input_group).css({
                    'border': '1px solid #00c0ef'
                });

                console.log('CAMPO A SETEAR: ', data_type, value);

                search_open = true;
            }
        }

        //console.log('search_open:', search_open);
        console.log('ids:', ids);

        $(document).ready(function() {

            table_id = $('#table_id').val();

            // console.log('TABLA ELEGIDA:', $('#table_id').val());

            if (search_open) {
                $('#search_open').trigger('click');

                $("html, body").animate({ scrollTop: $(document).height() }, 1000);
            }
        });

        //console.log('IDS:', ids);
    </script>
@endsection
