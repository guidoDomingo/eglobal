<section class="content">
    <!-- Modal -->
    <div id="myModal" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Balance detallado del atm : <label class="name"></label></h4>
                </div>
                <div class="modal-body">
                    <table id="detalles" class="table table-bordered table-hover dataTable" role="grid" aria-describedby="Table1_info">
                        <thead>
                        <tr role="row">
                            <th style="display:none;" class="sorting_disabled" rowspan="1" colspan="1"></th>
                            <th style="display:none;" class="sorting_disabled" rowspan="1" colspan="1"></th>
                            <th class="sorting_disabled" rowspan="1" colspan="1">Descripción</th>
                            <th class="sorting_disabled" rowspan="1" colspan="1">Saldo</th>
                        </tr>
                        </thead>
                        <tbody id="modal-contenido">

                        </tbody>
                    </table>

                    <div class="text-center" id="cargando" style="margin: 50px 10px"> {{-- clase para bloquear el div y mostrar el loading --}}
                        <i class="fa fa-refresh fa-spin" style="font-size:24px"></i>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cerrar</button>
                </div>
            </div>

        </div>
    </div>
    <div id="modalBloqueos" class="modal fade" role="dialog">
        <div class="modal-dialog">
            <!-- Modal content-->
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                    <h4 class="modal-title">Balance detallado del atm : <label class="name"></label></h4>
                </div>
                <div class="modal-body">
                    <table id="detalles_bloqueos" class="table table-bordered table-hover dataTable" role="grid" aria-describedby="Table1_info">
                        <thead>
                        <tr role="row">
                            <th style="display:none;" class="sorting_disabled" rowspan="1" colspan="1"></th>
                            <th style="display:none;" class="sorting_disabled" rowspan="1" colspan="1"></th>
                            <th class="sorting_disabled" rowspan="1" colspan="1">Fecha</th>
                            <th class="sorting_disabled" rowspan="1" colspan="1">Saldo</th>
                            <th class="sorting_disabled" rowspan="1" colspan="1">Descripción</th>
                        </tr>
                        </thead>
                        <tbody id="bloqueo-contenido">

                        </tbody>
                    </table>
                    <div id='ver'>

                    </div>
                </div>
                <div class="modal-footer">
                    <form name='bloqueo' method="get" action="{{route('reporting.historial_bloqueos.search')}}">
                        <input style="display: none" value='0' name='group_id' />
                        <input type='text' style="display: none" id='atm_id' value='0' name='atm_id' />
                        <input style="display: none" value='30_dias' name='reservationtime' />
                        <input style="display: none" value='search' name='search' />
                        <button type="submit" class="btn btn-primary pull-left">Continue</button>
                    </form>
                    <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cerrar</button>
                </div>
            </div>

        </div>
    </div>
    <!-- Info boxes -->
    <div class="row">
        <div class="col-md-12 col-sm-6 col-xs-12">
            <div class="box">
                <div class="box-header">
                    <h3 class="box-title">MINITERMINALES / Estados</h3>
                    <div class="box-tools">
                        <div class="input-group" style="width:800px;">
                            {!! Form::model(Request::only(['name']),['route' => 'reporting.bloqueados', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search', 'id' => 'estadoSearch']) !!}
                            <div class="row">
                                {{--<div class="col-md-5">
                                    <input type="text" class="form-control input-sm" id="search" placeholder="BUSCAR ATM" name="search">
                                 </div>--}}
                                <div class="col-md-4">
                                    {!! Form::select('estados', $estados, $estado, ['id' => 'estado','class' => 'select2', 'style' => 'width:100%']) !!}
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-block btn-success" name="download" value="download">EXPORTAR</button>
                                </div>
                            </div>
                            {!! Form::close() !!}
                        </div>
                    </div>
                </div>
                <br>
                <div class="box-body  no-padding">
                    <div class="row">
                        <div class="col-md-12">
                            <table class="table table-striped dataTable" id="datatable_1">
                                <thead>
                                <tr>
                                    <th style="width:10px">#</th>
                                    <th>Miniterminal</th>
                                    {{--<th>Identificador</th>--}}
                                    <th>Ultima Fecha de uso</th>
                                    <th class="no-sort">Estado</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($atms as $atm)
                                <tr data-id="{{ $atm->id  }}" data-name="{{ $atm->name }}">
                                    <td>{{$atm->id}}</td>
                                    <td>{{$atm->name}}</td>
                                    {{--<td>{{$atm->code}}</td>--}}
                                    <td>{{ Carbon\Carbon::parse($atm->last_request_at)->format('d/m/Y H:i') }}</td>
                                    <td>
                                    @if(is_null($atm->eliminado))
                                        @if($atm->block_type_id == 0)
                                            <span class="label label-success">Activo</span>
                                        @else
                                            <span class="label label-danger">{{$atm->description}}</span>
                                        @endif
                                    @else
                                        <span class="label label-warning">Inactivo</span>
                                    @endif
                                    <div class="btn-group">
                                        <buttom class="btn btn-default btn-xs" title="Ultimos Bloqueos">
                                            <i class="bloqueos fa fa-info-circle" style="cursor:pointer"></i>
                                        </buttom>
                                    </div>
                                    </td>
                                    
                                </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
    
                <div class="box-footer clearfix">
                    <div class="row">
                        <div class="col-sm-5">
                            <div class="dataTables_info" role="status" aria-live="polite"> {{count($atms)}} registros en total</div>
                        </div>
                        <div class="col-sm-7">
                            <div class="dataTables_paginate paging_simple_numbers">
    
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    
        </div>
    </div>
    <!-- Info boxes -->
</section>

@section('page_scripts')
    <script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>
    @include('partials._delete_row_js')
@endsection

@section('js')
    <link rel="stylesheet" href="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.css">
    <script src="/bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.min.js"></script>
    <script type="text/javascript">
        $('.select2').select2();
        $(document).on('change','#estado',function(){
            $('#estadoSearch').submit();
        });

        
        $('#search').on('keyup',function(){
            $value=$(this).val();
            $.ajax({
            type : 'get',
            url : '{{ route("reporting.bloqueados_search") }}',
            data:{'search':$value},
            success:function(data){
                $('tbody').html(data.output);
                $('.dataTables_info').html(data.atms);
            }
            });
        })

        $('.pay-info').on('click',function(e){
            e.preventDefault();
            var row = $(this).parents('tr');
            var atm_id = row.data('id');

            var atm_name = row.data('name');
            console.log(atm_id);
            console.log(atm_name);
            $('#modal-contenido').html('');
            $('#cargando').show();

            $.get('{{ url('reporting') }}/info/get_atm_balance/' + atm_id , 
            
                function(data) {
                    $(".name").html(atm_name);
                    $("#modal-contenido").html(data);
                    $("#detalles").show();
                    $('#cargando').hide();
                    $("#myModal").modal('show');
            });
            $("#myModal").modal('show');

        });

        $('.bloqueos').on('click',function(e){
            e.preventDefault();
            var row = $(this).parents('tr');
            var atm_id = row.data('id');
            
            var atm_name = row.data('name');

            document.getElementById("atm_id").value=atm_id;

            $('#bloqueo-contenido').html('');
            $('#cargando').show();

            $.get('{{ url('reporting') }}/info/get_bloqueos/' + atm_id , 
            
                function(data) {
                    $(".name").html(atm_name);
                    $("#bloqueo-contenido").html(data);
                    $("#detalles_bloqueos").show();
                    $('#cargando').hide();
                    $("#modalBloqueos").modal('show');
            });
            $("#modalBloqueos").modal('show');

        });

        function get_balance(atm_id, atm_name) {
            console.log(atm_id);
            console.log(atm_name);

            $('#modal-contenido').html('');

            $.get('{{ url('reporting') }}/info/get_atm_balance/' + atm_id , 
            
                function(data) {
                    $(".name").html(atm_name);
                    $("#modal-contenido").html(data['data']);
                    $("#detalles").show();
                    $("#myModal").modal('show');
            });
            $("#modalBloqueos").modal('show');
        }

        var table = $('#datatable_1').DataTable({
            "paging":       true,
            "ordering":     true,
            "info":         true,
            "searching":    true,
            dom: '<"pull-left"f><"pull-right"l>tip',
            columnDefs: [{
                targets: 'no-sort',
                orderable: false,
            }],
        });

        var ths = $("#datatable_1").find("th");

        var data_table_config = {
            //custom
            //orderCellsTop: true,
            fixedHeader: true,
            pageLength: 20,
            searching: false,
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
            ordering: false,
            //order: [[ 5, "asc" ]],
            initComplete: function(settings, json) {
                $('#content').css('display', 'block');
                $('#div_load').css('display', 'none');
                $('body > div.wrapper > header > nav > a').trigger('click');
            }
        }

        $.ajaxSetup({ headers: { 'csrftoken' : '{{ csrf_token() }}' } });
    </script>
@endsection
@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
    <style>
        /* The switch - the box around the slider */
        .switch {
            position: relative;
            display:  inline-block;
            width:    30px;
            height:   17px;
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

        input:checked + .slider {
            background-color: #2196F3;
        }

        input:focus + .slider {
            box-shadow: 0 0 1px #2196F3;
        }

        input:checked + .slider:before {
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