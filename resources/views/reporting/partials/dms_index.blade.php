<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-primary">
                <div class="box-header with-border">
                    <h3 class="box-title">Filtros de búsqueda</h3>
                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    </div>
                </div>
                <form action="{{ route('reports.dms.search') }}" method="GET">
                    <div class="box-body" style="display: block;">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('groups', 'Clientes') !!}
                                    {!! Form::select('group_id', $groups, $group_id, ['id' => 'group_id', 'class' => 'form-control select2']) !!}
                                </div>
                                                             
                                <div class="form-group">
                                    {!! Form::label('pdv', 'Puntos de venta') !!}
                                    {!! Form::select('pos_id', $pos, $pos_id, ['id' => 'pos_id', 'class' => 'form-control select2']) !!}
                                </div>
                                <div class="form-group">
                                    {!! Form::label('atms', 'Atm') !!}
                                    {!! Form::select('atm_id', $atms, $atm_id, ['id' => 'atm_id', 'class' => 'form-control select2']) !!}
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    {!! Form::label('redes', 'Redes') !!}
                                    {!! Form::select('owner_id', $owners, $owner_id, ['id' => 'owner_id', 'class' => 'form-control select2']) !!}
                                </div>
                                <div class="form-group">
                                    {!! Form::label('sucursales', 'Sucursales') !!}
                                    {!! Form::select('branch_id', $branches, $branch_id, ['id' => 'branch_id', 'class' => 'form-control select2']) !!}
                                </div>
                                {{-- <div class="form-group">
                                    <label>Rango de Tiempo & Fecha:</label>
                                    <div class="input-group">
                                        <div class="input-group-addon">
                                            <i class="fa fa-clock-o"></i>
                                        </div>
                                        <input name="reservationtime" type="text" id="reservationtime"
                                            class="form-control pull-right"
                                            value="{{ $reservationtime or '' }}" />
                                    </div>
                                </div> --}}
                                <br>
                                <div class="row">
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-block btn-primary" name="search"
                                            value="search" id="buscar">BUSCAR</button>
                                    </div>
                                    <div class="col-md-4">
                                        <button type="submit" class="btn btn-block btn-success" name="download"
                                            value="download">EXPORTAR</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                 
                </form>
            </div>

            <div id="div_load" style="text-align: center; margin-bottom: 10px; font-size: 20px;">
                <div>
                    <i class="fa fa-spin fa-refresh fa-2x" style="vertical-align: sub;"></i> &nbsp;
                    Cargando...

                    <p id="rows_loaded" title="Filas cargadas"></p>
                </div>
            </div>

            <div  class="box box-primary col-xs-12">               

                <div id="content"  class="box-footer" style="display: none;">
                    <table id="detalles" class="table table-bordered table-condensed table-hover" style="width:100%">
                        <thead>
                        <tr>
                            <th style="text-align:center; vertical-align:middle; width:10px">#</th>
                            <th style="text-align:center; vertical-align:middle;">COD PUNTO</th>
                            <th style="text-align:center; vertical-align:middle;">RAZON SOCIAL</th>
                            <th style="text-align:center; vertical-align:middle;">TELEFONO</th>
                            <th style="text-align:center; vertical-align:middle;">CANAL</th>
                            <th style="text-align:center; vertical-align:middle;">HORARIO</th>
                            <th style="text-align:center; vertical-align:middle;">DUEÑO</th>
                            <th style="text-align:center; vertical-align:middle;">ATENDIDO POR</th>
                            <th style="text-align:center; vertical-align:middle;">CATEGORIA</th>
                            <th style="text-align:center; vertical-align:middle;">DEPARTAMENTO</th>
                            <th style="text-align:center; vertical-align:middle;">CIUDAD</th>
                            <th style="text-align:center; vertical-align:middle;">REFERENCIA</th>
                            <th style="text-align:center; vertical-align:middle;">DIRECCION</th>
                            <th style="text-align:center; vertical-align:middle;">LATITUD</th>
                            <th style="text-align:center; vertical-align:middle;">LONGITUD</th>
                            <th style="text-align:center; vertical-align:middle;">ACCESIBILIDAD</th>
                            <th style="text-align:center; vertical-align:middle;">VISIBILIDAD</th>
                            <th style="text-align:center; vertical-align:middle;">TRAFICO</th>
                            <th style="text-align:center; vertical-align:middle;">ESTADO_POP</th>
                            <th style="text-align:center; vertical-align:middle;">PERMITE_POP</th>
                            <th style="text-align:center; vertical-align:middle;">TIENE_POP</th>
                            <th style="text-align:center; vertical-align:middle;">TIENE BANCARD</th>
                            <th style="text-align:center; vertical-align:middle;">TIENE PRONET</th>
                            <th style="text-align:center; vertical-align:middle;">TIENE NETEL</th>
                            <th style="text-align:center; vertical-align:middle;">TIENE POS DINELCO</th>
                            <th style="text-align:center; vertical-align:middle;">TIENE POS BANCARD</th>
                            <th style="text-align:center; vertical-align:middle;">TIENE BILLETAJE</th>
                            <th style="text-align:center; vertical-align:middle;">TIENE TIGO MONEY</th>
                            <th style="text-align:center; vertical-align:middle;">TIENE VISICOOLER</th>
                            <th style="text-align:center; vertical-align:middle;">VENDE BEBIDAS ALCOHOLICAS</th>
                            <th style="text-align:center; vertical-align:middle;">VENDE BEBIDAS GASIFICADAS</th>
                            <th style="text-align:center; vertical-align:middle;">VENDE PRODUCTOS DE LIMPIEZA</th>
                            <th style="text-align:center; vertical-align:middle;">FECHA CREACION DEL CLIENTE</th>
                            <th style="text-align:center; vertical-align:middle;">FECHA CREACION DE LA SUCURSAL</th>
                        </tr>
                        </thead>
                        <tbody>
                            @if (isset($caracteristicas))
                                
                                @foreach($caracteristicas as $item)
                                    <tr data-id="{{ $item->id  }}">
                                        <td style="text-align:center; vertical-align:middle;">{{ $item->id }}</td>
                                        <td style="text-align:center; vertical-align:middle;">{{ $item->cod_punto }}</td>
                                        <td style="text-align:center; vertical-align:middle;">{{ $item->ruc }} - {{ $item->razon_social }}</td>
                                        <td style="text-align:center; vertical-align:middle;">{{ $item->telefono_grupo }}</td>
                                        <td style="text-align:center; vertical-align:middle;">{{ $item->canal_red }}</td>
                                        <td style="text-align:center; vertical-align:middle;">{{ $item->horario }}</td>
                                        <td style="text-align:center; vertical-align:middle;">{{ $item->dueño }}</td>
                                        <td style="text-align:center; vertical-align:middle;">{{ $item->atendido_por }}</td>
                                        <td style="text-align:center; vertical-align:middle;">{{ $item->categoria }}</td>
                                        <td style="text-align:center; vertical-align:middle;">{{ $item->departamento }}</td>
                                        <td style="text-align:center; vertical-align:middle;">{{ $item->ciudad }}</td>
                                        <td style="text-align:center; vertical-align:middle;">{{ $item->referencia }}</td>
                                        <td style="text-align:center; vertical-align:middle;">{{ $item->direccion_grupo }}</td>
                                        <td style="text-align:center; vertical-align:middle;">{{ $item->latitud }}</td>
                                        <td style="text-align:center; vertical-align:middle;">{{ $item->longitud }}</td>
                                        <td style="text-align:center; vertical-align:middle;">{{ $item->accesibilidad }}</td>
                                        <td style="text-align:center; vertical-align:middle;">{{ $item->visibilidad }}</td>
                                        <td style="text-align:center; vertical-align:middle;">{{ $item->trafico }}</td>
                                        <td style="text-align:center; vertical-align:middle;">{{ $item->estado_pop }}</td>
                                        @if ($item->permite_pop == true)
                                            <td style="text-align:center; vertical-align:middle;"> Sí </td>
                                        @else
                                            <td style="text-align:center; vertical-align:middle;">No</td>
                                        @endif

                                        @if ($item->tiene_pop == true)
                                            <td style="text-align:center; vertical-align:middle;"> Sí </td>
                                        @else
                                            <td style="text-align:center; vertical-align:middle;">No</td>
                                        @endif

                                        @if ($item->tiene_bancard == true)
                                            <td style="text-align:center; vertical-align:middle;"> Sí </td>
                                        @else
                                            <td style="text-align:center; vertical-align:middle;">No</td>
                                        @endif

                                        @if ($item->tiene_pronet == true)
                                            <td style="text-align:center; vertical-align:middle;"> Sí </td>
                                        @else
                                            <td style="text-align:center; vertical-align:middle;">No</td>
                                        @endif

                                        @if ($item->tiene_netel == true)
                                            <td style="text-align:center; vertical-align:middle;"> Sí </td>
                                        @else
                                            <td style="text-align:center; vertical-align:middle;">No</td>
                                        @endif

                                        @if ($item->tiene_pos_dinelco == true)
                                            <td style="text-align:center; vertical-align:middle;"> Sí </td>
                                        @else
                                            <td style="text-align:center; vertical-align:middle;">No</td>
                                        @endif

                                        @if ($item->tiene_pos_bancard == true)
                                            <td style="text-align:center; vertical-align:middle;"> Sí </td>
                                        @else
                                            <td style="text-align:center; vertical-align:middle;">No</td>
                                        @endif

                                        @if ($item->tiene_billetaje == true)
                                            <td style="text-align:center; vertical-align:middle;"> Sí </td>
                                        @else
                                            <td style="text-align:center; vertical-align:middle;">No</td>
                                        @endif

                                        @if ($item->tiene_tm_telefonito == true)
                                            <td style="text-align:center; vertical-align:middle;"> Sí </td>
                                        @else
                                            <td style="text-align:center; vertical-align:middle;">No</td>
                                        @endif

                                        @if ($item->visicooler == true)
                                            <td style="text-align:center; vertical-align:middle;"> Sí </td>
                                        @else
                                            <td style="text-align:center; vertical-align:middle;">No</td>
                                        @endif
                                        
                                        @if ($item->bebidas_alcohol == true)
                                            <td style="text-align:center; vertical-align:middle;"> Sí </td>
                                        @else
                                            <td style="text-align:center; vertical-align:middle;">No</td>
                                        @endif
                                        
                                        @if ($item->bebidas_gasificadas == true)
                                            <td style="text-align:center; vertical-align:middle;"> Sí </td>
                                        @else
                                            <td style="text-align:center; vertical-align:middle;">No</td>
                                        @endif
                                        
                                        @if ($item->productos_limpieza == true)
                                            <td style="text-align:center; vertical-align:middle;"> Sí </td>
                                        @else
                                            <td style="text-align:center; vertical-align:middle;">No</td>
                                        @endif

                                        <td style="text-align:center; vertical-align: middle;">{{ date('d/m/Y', strtotime($item->fecha_creacion_grupo)) }}</td>
                                        <td style="text-align:center; vertical-align: middle;">{{ date('d/m/Y', strtotime($item->fecha_creacion_branches)) }}</td>
                                    </tr>
                                @endforeach

                            @endif
                        
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
    



</section>

@section('js')
    {{-- <!-- InputMask -->
    <script src="/bower_components/admin-lte/plugins/input-mask/jquery.inputmask.js"></script>
    <script src="/bower_components/admin-lte/plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
    <script src="/bower_components/admin-lte/plugins/input-mask/jquery.inputmask.extensions.js"></script>
    <!-- date-range-picker -->
    <link href="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker-bs3.css" rel="stylesheet"
        type="text/css" />
    <script src="/bower_components/admin-lte/plugins/daterangepicker/moment.min.js"></script>
    <script src="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker.js"></script>

    <!-- bootstrap datepicker -->
    <script src="/bower_components/admin-lte/plugins/datepicker/bootstrap-datepicker.js"></script> --}}
<!-- datatables -->
<link rel="stylesheet" href="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.css">
<script src="/bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.min.js"></script>

<!-- date-range-picker -->
<link href="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css" />
<script src="/bower_components/admin-lte/plugins/daterangepicker/moment.min.js"></script>
<script src="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker.js"></script>
<script src="/bower_components/admin-lte/plugins/jquery-validation/dist/jquery.validate.min.js"></script>
<script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>

    <script type="text/javascript">
        //Datatable config
        var data_table_config = {
                //custom
                //bAutoWidth: true,
                // columnDefs : [
                //     { Width: "3%", "targets": 0, className: "text-center"},
                //     { Width: "30%", "targets": 1, className: "text-center"},
                //     { Width: "100%", "targets": 2, className: "text-center"},
                //     { sWidth: "10%", "targets": 2},
                //     { sWidth: "40%", "targets": 3},
                //     { sWidth: "10%", "targets": 4, className: "text-center"},
                //     { sWidth: "10%", "targets": 5, className: "text-center"},
                //     { sWidth: "10%", "targets": 6, className: "text-center"},
                //     { sWidth: "10%", "targets": 7, className: "text-center"},
                //     { sWidth: "10%", "targets": 8, className: "text-center"},
                //     { sWidth: "10%", "targets": 9, className: "text-center"},
        
                //],
                
                orderCellsTop: true,
                fixedHeader: true,
                pageLength: 5,
                lengthMenu: [
                    1, 2, 5, 10, 20, 30, 50, 70, 100, 150, 300, 500, 1000, 1500, 2000
                ],
                dom: '<"pull-left"f><"pull-right"l>tip',
                language: {
                    "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
                },
                scrollX: true,
                processing: true,
                initComplete: function(settings, json) {
                    $('#content').css('display', 'block');
                    $('#div_load').css('display', 'none');
                    // $('body > div.wrapper > header > nav > a').trigger('click');
                }
                
            }
        
        var table = $('#detalles').DataTable(data_table_config); 

        $('.select2').select2();

    </script>

{{-- 
    <script>
        //Cascading dropdown list de redes / sucursales

        $('#group_id').on('change', function(e) {
            var group_id = e.target.value;
            $.get('{{ url('reports') }}/ddl/owners/' + group_id, function(owners) {
                $('#owner_id').empty();
                $.each(owners, function(i, item) {
                    $('#owner_id').append($('<option>', {
                        value: i,
                        text: item
                    }));
                });
            });

            $.get('{{ url('reports') }}/ddl/branches/' + group_id, function(branches) {
                $('#branch_id').empty();
                $.each(branches, function(i, item) {
                    $('#branch_id').append($('<option>', {
                        value: i,
                        text: item
                    }));
                });
            });
        });

        $('#owner_id').on('change', function(e) {
            var group_id = $("#group_id").val();
            var owner_id = e.target.value;
            $.get('{{ url('reports') }}/ddl/branches/' + group_id + '/' + owner_id, function(branches) {
                $('#branch_id').empty();
                $.each(branches, function(i, item) {
                    $('#branch_id').append($('<option>', {
                        value: i,
                        text: item
                    }));
                });
            });
        });

        $('#branch_id').on('change', function(e) {
            var branch_id = e.target.value;
            console.log(branch_id)
            $.get('{{ url('reports') }}/ddl/pdv/' + branch_id, function(data) {
                $('#pos_id').empty();
                $.each(data, function(i, item) {
                    $('#pos_id').append($('<option>', {
                        value: i,
                        text: item
                    }));
                });
            });
        });


        //Datemask dd/mm/yyyy
        $("#datemask").inputmask("dd/mm/yyyy", {
            "placeholder": "dd/mm/yyyy"
        });
        //Datemask2 mm/dd/yyyy
        $("#datemask2").inputmask("mm/dd/yyyy", {
            "placeholder": "mm/dd/yyyy"
        });
        //reservation date preset
        $('#reservationtime').val()



        if ($('#reservationtime').val() == '' || $('#reservationtime').val() == 0) {
            var date = new Date();
            var init = new Date(date.getFullYear(), date.getMonth(), date.getDate());
            var end = new Date(date.getFullYear(), date.getMonth(), date.getDate());

            var initWithSlashes = (init.getDate()) + '/' + (init.getMonth() + 1) + '/' + init.getFullYear() + ' 00:00:00';
            var endDayWithSlashes = (end.getDate()) + '/' + (end.getMonth() + 1) + '/' + end.getFullYear() + ' 23:59:59';

            $('#reservationtime').val(initWithSlashes + ' - ' + endDayWithSlashes);
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
            startDate: moment().startOf('month'),
            endDate: moment().endOf('month'),
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
        console.log(rsultadoDif);



        var text = $('.alert.alert-success.alert-dismissable').text();

        if (text !== '') {
            if (text.includes('link')) {

                text = text.replace('×', '');
                text = text.replace('Operación Exitosa', '');
                text = text.trim();

                swal({
                        title: 'Atención',
                        text: text,
                        type: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#0073b7',
                        confirmButtonText: 'Aceptar',
                        cancelButtonText: 'Cancelar',
                        closeOnClickOutside: false,
                        showLoaderOnConfirm: false
                    },
                    function(isConfirmMessage) {
                        if (isConfirmMessage) {}
                    }
                );
            }
        }
    </script> --}}
@endsection
@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />

    <style type="text/css">
        @media print {
            body * {
                visibility: hidden;

            }
/* 
            #printSection,
            #printSection * {
                visibility: visible;
            }



            #printSection {
                font-size: 11px;
                width: 100%;
                height: 100%;
                margin: 0;
                padding: 0;
                left: 0;
                top: 0;
            } */

    </style>
        <style>
        /* START - CONF SPINNER */
        table.dataTable thead {background-color:rgb(179, 179, 184)}
        
     </style>
@endsection