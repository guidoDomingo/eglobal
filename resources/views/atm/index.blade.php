@extends('layout')
@section('title')
    ATMS
@endsection
@section('refresh')
    <meta http-equiv="refresh" content="900">
@endsection
@section('content')
    <section class="content-header">
        <h1>
            ATMs
            <small>Listado de ATMS</small>
        </h1>

        @if (isset($owner))
            <h4>
                Última Versión de la App
                <small>{{ $owner->app_last_version }}</small>
            </h4>
        @endif

        <div class="box-body">
            <div class="row">
                <div class="col-md-12">
                    @include('partials._flashes')
                    @include('partials._messages')
                </div>
            </div>

            <div id="div_load" style="text-align: center; margin-bottom: 10px; font-size: 20px;">
                <div>
                    <i class="fa fa-spin fa-refresh fa-2x" style="vertical-align: sub;"></i> &nbsp;
                    Cargando...

                    <p id="rows_loaded" title="Filas cargadas"></p>
                </div>
            </div>
        </div>
    </section>

    <section class="content">

        <style>
            .modal {
                background: rgba(0, 0, 0, 0.7);
                text-align: center;
            }

            @media screen and (min-width: 768px) {
                .modal:before {
                    display: inline-block;
                    vertical-align: middle;
                    content: " ";
                    height: 100%;
                }
            }

            .modal-dialog {
                display: inline-block;
                text-align: left;
                vertical-align: middle;
                margin-bottom: 50px;
            }

            textarea {
                resize: vertical;
                max-height: 150px;
                min-height: 100px;
                width: 100%
            }

        </style>

        <div id="content" style="display: none">

            <div class="box box-default" style="margin-top: -15px">
                <div class="box-header with-border">
                    <h3 class="box-title">Búsqueda personalizada</h3>
                    <div class="box-tools pull-right">

                    </div>
                </div>
                <div class="box-body">

                    {!! Form::open(['route' => 'atm_index', 'method' => 'POST', 'role' => 'form', 'id' => 'atmSearch']) !!}

                    <div class="col-md-4">

                        <div class="row">

                            <div class="col-md-12">
                                {!! Form::select('owner_id', $owners, $owner_id, ['id' => 'ownerId', 'class' => 'select2', 'style' => 'width:100%']) !!}
                            </div>

                        </div>

                        <div class="row" style="margin-top: 10px">

                            <div class="col-md-12" id="div">
                                {!! Form::select('tipo_id', ['0' => 'Tipo de terminal: Todos', '1'=>'Manejado por Eglobal', '2'=>'Manejado por Cliente'], $tipo_id , ['class' => 'form-control select2', 'style' => 'width:100%', 'id' => 'tipo_id']) !!}
                            </div>

                        </div>

                    </div>

                    <div class="col-md-4">

                        <div class="row">

                            <div class="col-md-12">
                                {!! Form::select('group_id', $groups, $group_id, ['id' => 'groupId', 'class' => 'select2', 'style' => 'width:100%']) !!}
                            </div>

                        </div>

                        <div class="row" style="margin-top: 10px">

                            <div class="col-md-12">
                            {!! Form::text('name', $name, ['id' => 'name', 'class' => 'form-control input-sm pull-right', 'placeholder' => 'Nombre del terminal', 'autocomplete' => 'off']) !!}
                            </div>

                        </div>

                    </div>


                    <div class="col-md-4">

                        <div class="row" style="margin-bottom: 15px;">
                            <div class="col-md-12">
                                <div title="Filtrar Solo Activos / Inactivos">
                                    <input type="checkbox" id="atm_active" name="atm_active"></input> &nbsp; <b style="vertical-align: middle;"> Solo Activos</b> &nbsp;
                                </div>
                            </div>
                        </div>
                        
                        <div class="btn-group" role="group" aria-label="Basic example">

                            @if (\Sentinel::getUser()->inRole('superuser') || \Sentinel::getUser()->inRole('atms_v2.area_comercial') || \Sentinel::getUser()->inRole('atms_v2.area_eglobalt'))
           
                                <a href="{{ route('atm.form_step') }}" class="btn btn-primary" role="button" style="margin-right: 5px;">
                                    <span class="fa fa-plus"></span> Agregar
                                </a>

                            @endif

                            &nbsp;

                            <button type="button" class="btn btn-info" value="false" name="search" id='search' style="margin-right: 5px;">
                                <span class="fa fa-search"></span> Buscar
                            </button>

                            &nbsp;

                            <button type="button" class="btn btn-success" value="false" name="export" id='export' style="margin-right: 5px;">
                                <span class="fa fa-file-excel-o"></span> Exportar
                            </button>
                        </div>
                    </div>

                    <!--
                    <div class="col-md-2" style="display: none">
                        <div class="form-group">
                            <select class="form-control select2" id="record_limit" name="record_limit">
                                <option value="TODOS">Todos</option>
                                <option value="1">1 Registro</option>
                                <option value="2">2 Registros</option>
                                <option value="5">5 Registros</option>
                                <option value="10">10 Registros</option>
                                <option value="20">20 Registros</option>
                                <option value="30">30 Registros</option>
                                <option value="50">50 Registros</option>
                                <option value="70">70 Registros</option>
                                <option value="100">100 Registros</option>
                                <option value="150">150 Registros</option>
                                <option value="200">200 Registros</option>
                                <option value="250">250 Registros</option>
                                <option value="300">300 Registros</option>
                                <option value="500">500 Registros</option>
                                <option value="700">700 Registros</option>
                                <option value="1000">1000 Registros</option>
                                <option value="1500">1500 Registros</option>
                                <option value="2000">2000 Registros</option>
                                <option value="5000">5000 Registros</option>
                            </select>
                        </div>
                    </div>
                    -->

                    <div style="display: none">
                        {!! Form::radio('download', 'false', ['id' => 'download']) !!}
                    </div>


                    {!! Form::close() !!}
                </div>
            </div>

            <div class="box">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-12" style="overflow-x: scroll;">
                            <div id="hide_show_columns"></div>

                            <br />

                            <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_1"
                                style="font-size: 13px">
                                <thead>
                                    <tr>
                                        <th>Ver opciones</th>
                                        <th>Grupo</th>
                                        <th class="no-sort">#</th>
                                        <th>Nombre</th>
                                        <th>Identificador</th>
                                        <th>Red</th>
                                        <th>Creado</th>
                                        <th>Estado</th>
                                        <th>Ultima Actualización</th>
                                        <th>Tiempo Transcurrido</th>
                                        @if (Sentinel::hasAccess('mantenimiento.arqueo_remoto'))
                                            <th>Arqueo Remoto</th>
                                        @endif
                                        @if (Sentinel::hasAccess('marca.add|edit'))
                                            <th>Grilla Tradicional</th>
                                        @endif
                                        <th>App Versions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($atms as $atm)
                                        @if ($atm->status == 'Online')
                                            <tr class="success" data-id="{{ $atm->id }}"
                                                id="row_{{ $atm->id }}">
                                            @elseif($atm->status == 'Suspendido')
                                            <tr class="default" data-id="{{ $atm->id }}"
                                                id="row_{{ $atm->id }}">
                                            @elseif($atm->status == 'Acceso no autorizado')
                                            <tr class="danger" data-id="{{ $atm->id }}"
                                                id="row_{{ $atm->id }}">
                                            @elseif($atm->status == 'Offline')
                                            <tr class="warning" data-id="{{ $atm->id }}"
                                                id="row_{{ $atm->id }}">
                                        @endif

                                        @if ($atm->atm_status != 80)
                                            <td>
                                                <a class="btn btn-default" title="Ver opciones"
                                                    onclick="view_options({{ $atm->id }}, {{ $atm->block_type_id }})"><i
                                                        class="fa fa-eye"></i></a>
                                            </td>
                                        @else
                                            <td>
                                                @if (Sentinel::hasAccess('atms.add|edit'))
                                                    <button class="reactivar">Reactivar</button>
                                                @endif
                                            </td>
                                        @endif

                                        <td>{{ $atm->business_group }}</td>
                                        <td>{{ $atm->id }}.</td>
                                        <td>{{ $atm->name }}</td>
                                        <td>
                                            {{ $atm->code }} -
                                            @if (Sentinel::hasAccess('atms.add|edit'))
                                                <a href="{{ route('atm.credentials.index', ['atm' => $atm->id]) }}"><i
                                                        class="fa fa-key"></i></a>
                                            @endif
                                        </td>
                                        <td>{{ $atm->owner_name }}</td>
                                        <td>
                                            @if ($atm->atm_status == -1)
                                                <div class="progress">
                                                    <div class="progress-bar progress-bar-primary progress-bar-striped"
                                                        role="progressbar" aria-valuenow="25" aria-valuemin="0"
                                                        aria-valuemax="100" style="width: 25%">
                                                        <span class="">25%</span>
                                                    </div>
                                                </div>
                                            @endif
                                            @if ($atm->atm_status == -2)
                                                <div class="progress">
                                                    <div class="progress-bar progress-bar-primary progress-bar-striped"
                                                        role="progressbar" aria-valuenow="50" aria-valuemin="0"
                                                        aria-valuemax="100" style="width: 50%">
                                                        <span class="">50%</span>
                                                    </div>
                                                </div>
                                            @endif
                                            @if ($atm->atm_status == -3)
                                                <div class="progress">
                                                    <div class="progress-bar progress-bar-primary progress-bar-striped"
                                                        role="progressbar" aria-valuenow="75" aria-valuemin="0"
                                                        aria-valuemax="100" style="width: 75%">
                                                        <span class="">75%</span>
                                                    </div>
                                                </div>
                                            @endif
                                            @if ($atm->atm_status == -4)
                                                <div class="progress">
                                                    <div class="progress-bar progress-bar-success progress-bar-striped"
                                                        role="progressbar" aria-valuenow="100" aria-valuemin="0"
                                                        aria-valuemax="100" style="width: 100%">
                                                        <span class="">100%</span>
                                                    </div>
                                                </div>
                                            @endif
                                        </td>

                                        @if ($atm->status == 'Online')
                                            <td><span><i class="fa fa-circle text-success"></i> Online </span></td>
                                        @elseif($atm->status == 'Suspendido')
                                            <td><span><i class="fa fa-circle text-red" style="cursor:pointer"></i>
                                                    Suspendido <i class="pay-info fa fa-info-circle" data-toggle="tooltip"
                                                        title="Detalle"></i> Información </span></td>
                                        @elseif($atm->status == 'Acceso no autorizado')
                                            <td><small style="font-weight: bold; color: #dd4b39">ACCESO NO
                                                    AUTORIZADO</small></td>
                                        @elseif($atm->status == 'Offline')
                                            <td><span><i class="fa fa-circle text-yellow"></i> Offline </span></td>
                                        @endif

                                        <td>{{ $atm->last_request_at }}</td>

                                        <td title="{{ $atm->minutes_to_data_time }}">{{ $atm->minutes }} minutos</td>

                                        @if (Sentinel::hasAccess('mantenimiento.arqueo_remoto'))
                                            <td>
                                                @if ($atm->arqueo_remoto == true)
                                                    <label class="switch">
                                                        <input type="checkbox" class="arqueo_remoto" checked>
                                                        <span class="slider round"></span>
                                                    </label>
                                                @else
                                                    <label class="switch">
                                                        <input type="checkbox" class="arqueo_remoto">
                                                        <span class="slider round"></span>
                                                    </label>
                                                @endif
                                            </td>
                                        @endif

                                        @if (Sentinel::hasAccess('marca.add|edit'))
                                            <td>
                                                @if ($atm->grilla_tradicional == true)
                                                    <label class="switch">
                                                        <input type="checkbox" class="grilla_tradicional" checked>
                                                        <span class="slider round"></span>
                                                    </label>
                                                @else
                                                    <label class="switch">
                                                        <input type="checkbox" class="grilla_tradicional">
                                                        <span class="slider round"></span>
                                                    </label>
                                                @endif
                                            </td>
                                        @endif

                                        <td>{{ $atm->compile_version }}</td>

                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal -->
    <div id="myModal" class="modal fade" role="dialog">
        <div class="modal-dialog">

            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Detalles - ATM <label class="idAtm"></label></h4>
                </div>
                <div class="modal-body">
                    <table id="detalles" class="table table-bordered table-hover dataTable" role="grid"
                        aria-describedby="Table1_info">
                        <thead>
                            <tr role="row">
                                <th class="sorting_disabled" rowspan="1" colspan="1">Dispositivo</th>
                                <th class="sorting_disabled" rowspan="1" colspan="1">Mensaje</th>
                                <th class="sorting_disabled" rowspan="1" colspan="1">Fecha Inicio</th>
                                <th class="sorting_disabled" rowspan="1" colspan="1">Fecha Fin</th>
                                <th class="sorting_disabled" rowspan="1" colspan="1">Tiempo Transcurrido</th>
                            </tr>
                        </thead>
                        <tbody id="modal-contenido">

                        </tbody>
                        <tfoot>
                            <tr>
                                <th class="sorting_disabled" rowspan="1" colspan="1">Dispositivo</th>
                                <th class="sorting_disabled" rowspan="1" colspan="1">Mensaje</th>
                                <th class="sorting_disabled" rowspan="1" colspan="1">Fecha Inicio</th>
                                <th class="sorting_disabled" rowspan="1" colspan="1">Fecha Fin</th>
                                <th class="sorting_disabled" rowspan="1" colspan="1">Tiempo Transcurrido</th>
                            </tr>
                        </tfoot>
                    </table>
                    <div id="acciones">
                        <div id="message_box" class="text-center alert  display: none;"></div>
                        <div id="keys_spinn" class="text-center" style="margin: 50px 10px; display: none;"><i
                                class="fa fa-circle-o-notch fa-spin" style="font-size:24px"></i></div>
                        <form role="form" id="reactivation-form">
                            <div class="box-body">
                                <div class="form-group">
                                    <label for="txtDescription">Descripción</label>
                                    <textarea id="txtDescription" name="txtDescription" class="form-control" rows="3"
                                        placeholder="Describa brevemente el caso ..."></textarea>
                                    <input type="hidden" id="txtatm_id">
                                </div>
                            </div>
                            <!-- /.box-body -->
                        </form>
                    </div>
                </div>
                <div class="modal-footer">
                    <button disabled type="buttom" style="display: none;" id="process-reactivacion"
                        class="btn btn-primary pull-left">Reactivar</button>
                    <button type="button" class="btn btn-default" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    <!-- End Modal -->

    <!-- Modal -->
    <div id="modal_view_options" class="modal fade" role="dialog">

        <div class="modal-dialog modal-dialog-centered" role="document" style="background: white; border-radius: 5px">

            <!-- Modal content-->
            <div class="modal-content" style="border-radius: 10px">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <div class="modal-title" style="font-size: 20px; text-align: center">
                        Opciones disponibles para el cajero
                    </div>
                </div>
                <div class="modal-body">
                    <div class="btn-group" role="group" style="display: grid">
                        @if (Sentinel::hasAccess('atms.add|edit'))
                            <a class="btn-sm btn btn-success" title="Editar" id="atm_edit" ><i class="fa fa-pencil"></i>
                                Modificar</a> &nbsp;
                        @endif
                        @if (Sentinel::hasAccess('atms.delete'))
                            <a class="btn-sm btn btn-danger" title="Eliminar" id="atm_delete">
                                <i class="fa fa-remove"></i> Eliminar
                            </a> &nbsp;
                        @endif
                        @if (Sentinel::hasAccess('atms.params'))
                            <a class="btn-sm btn btn-primary" title="Parametros" id="atm_params"><i class="fa fa-gear"></i>
                                Parametros</a> &nbsp;
                        @endif
                        @if (Sentinel::hasAccess('atms.parts'))
                            <a class="btn-sm btn btn-warning" title="Partes" id="atm_parts"><i class="fa fa-wrench"></i>
                                Partes</a> &nbsp;
                        @endif
                        @if (Sentinel::hasAccess('atms.add|edit'))
                            <a class="btn-sm btn btn-default" title="Editar Housing" id="atm_housing"><i
                                    class="fa fa-list"></i> Housing</a> &nbsp;
                        @endif
                        @if (Sentinel::hasAccess('atm_block_type_change'))
                            <a class="btn-sm btn btn-default" title="Modificar el Block-Type" id="atm_block_type_change" atm_id=""
                                block_type_id="" onclick="open_modal_atm_block_type_change()"><i
                                    class="fa fa-cube"></i> Block-Type</a> &nbsp;
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal  -->
    <div id="modal_atm_block_type_change" class="modal fade" role="dialog">
        <div class="modal-dialog modal-dialog-centered" role="document" style="background: white; border-radius: 5px">
            <!-- Modal content-->
            <div class="modal-content" style="border-radius: 10px">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <div class="modal-title" style="font-size: 20px; text-align: center">
                        Cambiar el Block-Type:
                    </div>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-12">
                            <!--<label for="block_type_id">Block Type:</label>-->
                            <div class="form-group">
                                <input type="text" class="form-control" id="block_type_id" name="block_type_id"
                                    placeholder="Cambiar el Block-Type"></input>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12">
                            <!--<label for="commentary">Agregar un comentario:</label>-->

                            <div class="input-group">
                                <span class="input-group-addon">
                                    <b>
                                        <i class="fa fa-pencil"></i>
                                    </b>
                                </span>

                                <textarea rows="4" cols="30" class="form-control" id="commentary" name="commentary"
                                    placeholder="Agregar un comentario" value=""></textarea>
                            </div>
                        </div>
                    </div>

                    <br/>

                    <div class="row">
                        <div class="col-md-12" style="font-size: 12px">
                            <label style="color: #dd4b39">Obs. Los datos del atm se guardan en la tabla: </label> <b>audit.atms</b> <br/>
                            <label style="color: #dd4b39">Por cada actualización de <b>block_type</b> se guardará un registro en la tabla: </label> <b>public.historial_bloqueos</b>
                        </div>
                    </div>

                    <br/>

                    <div class="row">
                        <div class="col-md-12" style="text-align: right">
                            <button class="btn btn-info" title="Modificar el Block-Type"
                                onclick="save_atm_block_type_change()"><i class="fa fa-save"></i>
                                Guardar</button>
                        </div>
                    </div>

                    <br/>
                </div>
            </div>
        </div>
    </div>
    <!-- End Modal -->
@endsection
@section('page_scripts')
    @include('partials._selectize')
@endsection

@section('js')
    <!-- datatables -->
    <link rel="stylesheet" href="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.css">
    <script src="/bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.min.js"></script>
    <script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>

    <!-- iCheck -->
    <link rel="stylesheet" href="/bower_components/admin-lte/plugins/iCheck/square/grey.css">
    <script src="/bower_components/admin-lte/plugins/iCheck/icheck.min.js"></script>

    <script type="text/javascript">

        var block_type = false;

        function view_options(atm_id, block_type_id) {

            $('#atm_edit').attr('href', 'atm/' + atm_id + '/edit');
            $('#atm_delete').attr('onclick', 'delete_atm(' + atm_id + ')');
            $('#atm_params').attr('href', 'atm/' + atm_id + '/params');
            $('#atm_parts').attr('href', 'atm/' + atm_id + '/parts');
            $('#atm_housing').attr('href', 'atm/' + atm_id + '/housing');

            $('#atm_block_type_change').attr({
                'atm_id': atm_id,
                'block_type_id': block_type_id
            });

            $("#modal_view_options").modal();
        }

        function open_modal_atm_block_type_change() {

            var atm_id = $('#atm_block_type_change').attr('atm_id');
            var block_type_id = $('#atm_block_type_change').attr('block_type_id');

            console.log('atm_id:', atm_id, 'block_type_id:', block_type_id);

            $('#block_type_id').selectize()[0].selectize.setValue(block_type_id, false);

            $("#modal_atm_block_type_change").modal();
        }

        function save_atm_block_type_change() {

            var atm_id = $('#atm_block_type_change').attr('atm_id');
            var block_type_id = $('#block_type_id').val();
            var block_type_id_aux = $('#atm_block_type_change').attr('block_type_id');
            var commentary = $('#commentary').val();

            console.log(block_type_id, block_type_id_aux, 'comentario:', commentary);

            if (block_type_id !== block_type_id_aux) {

                if (commentary !== '') {

                    $('.sweet-alert button.cancel').css('background', '#dd4b39');

                    swal({
                            title: 'Atención',
                            text: 'Está a punto de modificar el Block-Type, Continuar ?',
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
                                var url = '/atm/block_type_change/';
                                var json = {
                                    _token: token,
                                    atm_id: atm_id,
                                    block_type_id: block_type_id,
                                    commentary: commentary
                                };
        
                                $.post(url, json, function(data, status) {
                                    var error = data.error;
                                    var message = data.message;
                                    var type = '';
                                    var title = '';
        
                                    if (error == true) {
                                        type = 'error';
                                        title = 'Ocurrió un error al modificar el Block-Type del atm.';
                                    } else {
                                        type = 'success';
                                        title = 'El Block-Type del atm a sido modificado con éxito.';
                                    }
        
                                    swal({
                                            title: title,
                                            //text: message,
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

                                    console.log('data:', data);
                                }).error(function(error) {
                                    console.log('Error:', error);
                                });
                            }
                        }
                    );
                } else {
                    swal('Agregar un comentario!');
                }
            } else {
                swal('El Block-Type no es diferente!');
            }
        }

        function delete_atm(atm_id) {
            var url = "atm/" + atm_id + "/delete";

            //console.log('atm_id', atm_id);
            //console.log('URL', url);

            $.get(url, function(data) {
                console.log('data', data);

                var error = data.error;
                var message = data.message;

                var type = (data.error == true) ? 'error' : 'info';

                if (type == 'info') {

                    //document.getElementById("row_" + atm_id).remove();

                    document.getElementById("row_" + atm_id).remove();

                    $("#modal_view_options").modal('hide');
                }

                swal({
                        title: 'Información:',
                        text: message,
                        type: type,
                        showCancelButton: false,
                        closeOnClickOutside: false,
                        confirmButtonColor: '#3c8dbc',
                        confirmButtonText: 'Aceptar'
                    },
                    function(isConfirm) {
                        if (isConfirm) {}
                    }
                );
            });
        }

        $('.select2').select2();

        $('.pay-info').on('click', function(e) {
            e.preventDefault();
            var row = $(this).parents('tr');
            var atm_id = row.data('id');
            $.get('{{ url('reports') }}/info/atm_notification/' + atm_id, function(data) {
                $(".idAtm").html(atm_id);
                $("#modal-contenido").html(data);
                $("#detalles").show();
                $('#keys_spinn').hide();
                $('#process-reactivacion').hide();
                $('#message_box').hide();
                $("#myModal").modal();
            });
        });

        $('.reactivar').on('click', function(e) {
            e.preventDefault();
            var row = $(this).parents('tr');
            var atm_id = row.data('id');

            $(".idAtm").html(atm_id);
            $("#detalles").hide();
            $('#process-reactivacion').show();
            $('#reactivation-form').show();
            $('#keys_spinn').hide();
            $('#message_box').hide();
            $('#txtatm_id').val(atm_id);
            $('#txtDescription').val('');
            $("#myModal").modal();
        });

        $('#txtDescription').on('keyup', function(e) {
            if ($(this).val() != '') {
                document.getElementById("process-reactivacion").disabled = false;
            } else {
                document.getElementById("process-reactivacion").disabled = true;
            }
        });

        $('#process-reactivacion').on('click', function(e) {
            $('#keys_spinn').show();
            $('#reactivation-form').hide();
            $('#message_box').html('Procesando reactivación');
            $('#message_box').addClass('alert-warning');
            $('#process-reactivacion').hide();
            $('#message_box').show();

            let form = $('#reactivation-form')[0];
            let data = new FormData(form);
            let atm_id = $('#txtatm_id').val();
            data.append("_token", token);
            data.append("_atm_id", atm_id);

            $.ajax({
                type: "POST",
                url: "atm/reactivate",
                data: data,
                processData: false,
                contentType: false,
                cache: false,
                timeout: 600000,
                success: function(data) {
                    //console.log("SUCCESS : ", data);
                    if (data.error == false) {
                        $('#message_box').html(data.message);
                        $('#message_box').addClass('alert-success');
                    } else {
                        $('#message_box').html(data.message);
                        $('#message_box').addClass('alert-danger');
                    }

                    $('#message_box').show();
                    $('#keys_spinn').hide();
                },
                error: function(e) {
                    $('#message_box').html(
                        'Lo sentimos, se produjo un error al procesar la reactivación');
                    $('#message_box').addClass('alert-danger');
                    $('#message_box').show();
                    $('#keys_spinn').hide();
                }
            });

            setTimeout(function() {
                $('#myModal').modal('hide')
                location.reload();
            }, 5000);

        });

        $('.arqueo_remoto').on('change', function(e) {
            e.preventDefault();
            let row = $(this).parents('tr');
            let atm_id = row.data('id');
            let value = null;
            let mensaje = '';
            $(".idAtm").html(atm_id);

            if ($(this).is(':checked')) {
                // Hacer algo si el checkbox ha sido seleccionado
                value = true;
                mensaje = 'Arqueo Remoto habilitado'
            } else {
                // Hacer algo si el checkbox ha sido deseleccionado
                value = false;
                mensaje = 'Arqueo Remoto desactivado'
            }

            $.post("atm/arqueo_remoto", {
                _token: token,
                _atm_id: atm_id,
                _value: value
            }, function(data) {
                console.log('Solicitud procesada ' + mensaje);
            });
        });

        $('.grilla_tradicional').on('change', function(e) {
            e.preventDefault();
            let row = $(this).parents('tr');
            let atm_id = row.data('id');
            let value = null;
            let mensaje = '';
            var checkeado = $(this).is(':checked');
            var thisAtm = $(this);

            if ($(this).is(':checked')) {
                // Hacer algo si el checkbox ha sido seleccionado
                value = true;
                mensaje = 'Grilla tradicional habilitada'
            } else {
                // Hacer algo si el checkbox ha sido deseleccionado
                value = false;
                mensaje = 'Grilla tradicional desactivada'
            }

            $.post("atm/grilla_tradicional", {
                _token: token,
                _atm_id: atm_id,
                _value: value
            }, function(data) {
                if (data.error) {
                    thisAtm.prop('checked', !checkeado);
                    alert('Ha ocurrido un error');
                }

                console.log('Solicitud procesada ' + mensaje);
            });
        });

        /*$(document).on('change', '#ownerId', function() {
            $('#atmSearch').submit();
        });*/

        //$(document).on('change', '#groupId', function() {
            //$('#atmSearch').submit();
        //});

        //var=document.getElementById('download');
        //console.log($('input[name="download"]:checked').val());

        $('#export').on('click', function(e) {
            e.preventDefault();

            $('input[name="download"]:checked').val('download');

            //document.getElementById("download").value="download"; 
            $('#atmSearch').submit();
            /*console.log('entro aca 2');
            e.preventDefault(); 
            document.getElementById("export").value="download"; */
        });

        $('#search').on('click', function(e) {
            e.preventDefault();
            $('input[name="download"]:checked').val('');
            $('#atmSearch').submit(); // Hacemos submit al formulario.
        });

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
                //$('body > div.wrapper > header > nav > a').trigger('click');
            }
        }

        var table = $('#datatable_1').DataTable(data_table_config);

        $('#hide_show_columns').append('Ocultar columna/s: &nbsp;');

        var hide_show_columns = [];

        var ths = $("#datatable_1").find("th");

        for (var i = 0; i < ths.length; i++) {
            //console.log('ITEM:', ths[i].innerHTML);
            hide_show_columns.push(ths[i].innerHTML);
        }

        //hide_show_columns.push('Todas las columnas');

        for (var i = 0; i < hide_show_columns.length; i++) {

            var description = hide_show_columns[i];

            $('#hide_show_columns').append(
                '<a class="toggle-vis btn btn-default btn-sm" data-column="' + i + '" id="toggle-vis-' + i +
                '" value="' + description + '" title="Mostrar / Ocultar columna: ' + description +
                '" style="margin-top: 5px">' +
                '<i class="fa fa-eye"></i> &nbsp;' + description +
                '</a> &nbsp;'
            );
        }

        $('a.toggle-vis').on('click', function(e) {
            e.preventDefault();

            var data_column = $(this).attr('data-column');
            var column_description = $(this).attr('value');
            var column_visible = false;
            var hide_show_columns_length = hide_show_columns.length - 1;

            if (column_description == 'Todas las columnas') {
                for (var i = 0; i < hide_show_columns_length; i++) {
                    var column = table.column(i);
                    column_visible = column.visible();
                    column.visible(!column_visible);
                }
            } else {
                var column = table.column(data_column);
                column_visible = column.visible();
                column.visible(!column_visible);
                column_description = $(this).attr('value');
            }

            var fa = (column_visible) ? 'eye-slash' : 'eye';
            $(this).html('<i class="fa fa-' + fa + '"></i> &nbsp;' + column_description);
        });

        $(".alert").delay(5000).slideUp(300);


        var selective_config = {
            delimiter: ',',
            persist: false,
            openOnFocus: true,
            valueField: 'id',
            labelField: 'description',
            searchField: 'description',
            maxItems: 1,
            options: {!! $block_types !!},
            onChange: function(value) {
                //2: Carga normal de saldo, desplegar lista de saldos normales

                if (value == 2) {
                    $('.nav-tabs a[href="#tab_list"]').css({
                        'display': 'block'
                    });
                } else {
                    $('.nav-tabs a[href="#tab_list"]').css({
                        'display': 'none'
                    });
                }
            }
        };

        $('#block_type_id').selectize(selective_config);

        var owner_id = "{{ $owner_id }}";

        if(owner_id == '16') {
            $("#div").show(); 
        } else {
            $('#tipo_id').val('0').trigger('change');
            $("#div").hide();
        }  


        $('#ownerId').on('select2:select', function(e) {
            var value_selected = e.currentTarget.value;
            if(value_selected == 16) {
                $("#div").show(); 
            } else {
                $('#tipo_id').val('0').trigger('change');
                $("#div").hide();
            } 
            
            $('#name').val(null);
        });

        $('#atm_active').iCheck({
            checkboxClass: 'icheckbox_square-grey',
            radioClass: 'iradio_square-grey'
        });

        var atm_active = "{{ $atm_active }}";

        console.log('active:', atm_active);

        if (atm_active == 'on') {
            $('#atm_active').iCheck('check');
        } else {
            $('#atm_active').iCheck('uncheck');
        }

    </script>
@endsection
@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
    <style>
        /* The switch - the box around the slider */
        .switch {
            position: relative;
            display: inline-block;
            width: 30px;
            height: 17px;
        }

        /* Hide default HTML checkbox */
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        /* The slider */
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            -webkit-transition: .4s;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 13px;
            width: 13px;
            left: 2px;
            bottom: 2px;
            background-color: white;
            -webkit-transition: .4s;
            transition: .4s;
        }

        input:checked+.slider {
            background-color: #2196F3;
        }

        input:focus+.slider {
            box-shadow: 0 0 1px #2196F3;
        }

        input:checked+.slider:before {
            -webkit-transform: translateX(13px);
            -ms-transform: translateX(13px);
            transform: translateX(13px);
        }

        /* Rounded sliders */
        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }

    </style>
@endsection