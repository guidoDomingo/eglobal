@extends('layout')
@section('title')
    Asociaciones
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Asociaciones de ATMs y Sucursales
            <small>Listado</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Promociones</a></li>
            <li><a href="#">Campañas</a></li>
            <li class="active">Asociaciones</li>
        </ol>
    </section>
    <section class="content">
        @include('partials._flashes')
        <div class="box">

            <div class="box-header">
                <h3 class="box-title">
                </h3>
                <a class="btn-sm btn-default" href="{{ route('campaigns.index') }}" role="button">Atrás</a>
                @if (Sentinel::hasAccess('asociar.add|edit'))
                <a href='#' id="nuevaAsociacion" data-toggle="modal" data-target="#modalNuevaAsociacion" class="btn-sm btn-primary active" role="button"><small>Agregar <i class="fa fa-plus"></i></small></a>
                @endif
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-xs-12">
                        @if ($asociaciones)
                            <table id="detalles" class="table table-bordered table-striped table-condensed">
                                <thead>
                                    <tr>
                                        <th style="width:10px">#</th>
                                        <th style="text-align:center;">ATM</th>
                                        <th style="text-align:center;">Sucursal para retirar</th>
                                        <th style="text-align:center;">Eliminar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($asociaciones as $item)
                                        <tr data-id="{{ $item->id  }}">
                                            <td style="text-align:center; vertical-align:middle;">{{ $item->id }}</td>
                                            <td style="text-align:center; vertical-align:middle;">{{ $item->atm_name }}</td>
                                            <td style="vertical-align:middle; text-align:center;">{{ $item->branch_name }}</td>
                                            <td style="vertical-align:middle; text-align:center; width: 100px;">
                                                {{-- @if (Sentinel::hasAccess('arts.add|edit')) --}}
                                                    {{-- <a class="btn btn-success btn-flat btn-row" title="Editar" href="{{ route('atmhascampagins.edit',['id' => $item->id])}}"><i class="fa fa-pencil"></i></a> --}}
                                                {{-- @endif --}}
                                                @if (Sentinel::hasAccess('asociar.delete'))
                                                    <a class="btn-delete btn btn-danger btn-flat btn-row" title="Eliminar" href="#" ><i class="fa fa-remove"></i> </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
           
        </div>
    </section>
    {!! Form::open(['route' => ['atmhascampagins.destroy',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
    {!! Form::close() !!}

@endsection

@section('js')

    <link rel="stylesheet" href="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.css">
    <script src="/bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.min.js"></script>
    <script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />

    <script type="text/javascript">            
        var data_table_config = {
            //custom
            fixedHeader: true,
            order: [[0, 'desc']],
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
            processing: true,
            initComplete: function(settings, json) {
                $('#content').css('display', 'block');
                $('#div_load').css('display', 'none');
                // $('body > div.wrapper > header > nav > a').trigger('click');
            }
        }
        $('#detalles').DataTable(data_table_config);
    </script>

    <script type="text/javascript">

        $('.select2').select2();

        $('#btnAsociar').click(function (e) {
            e.preventDefault();
            $(this).html('Asociar');
            $.ajax({
                data: $('#nuevaAsociacion-form').serialize(),
                url: "{{ route('atmhascampagins.store') }}",
                type: "POST",
                dataType: 'json',
                success: function (data) {
                    $('#nuevaAsociacion-form').trigger("reset");
                    $('#modalNuevaAsociacion').modal('hide');
                    location.reload();
                },
                error: function (data) {
                    console.log('Error:', data);
                    $('#saveBtn').html('Save Changes');
                }
            });
        });

        $('.btn-delete').click(function(e){
            e.preventDefault();
            var row = $(this).parents('tr');
            var id = row.data('id');
            swal({
                title: "Atención!",
                text: "Está a punto de borrar el registro, está seguro?.",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Si, eliminar!",
                cancelButtonText: "No, cancelar!",
                closeOnConfirm: true,
                closeOnCancel: true
            },
            function(isConfirm){
                if (isConfirm) {
                    var form = $('#form-delete');
                    var url = form.attr('action').replace(':ROW_ID',id);
                    var data = form.serialize();
                    var type = "";
                    var title = "";
                    $.post(url,data, function(result){
                        if(result.error == false){
                            row.fadeOut();
                            type = "success";
                            title = "Operación realizada!";
                        }else{
                            type = "error";
                            title =  "No se pudo realizar la operación"
                        }
                        swal({   title: title,   text: result.message,   type: type,   confirmButtonText: "Aceptar" });
                    }).fail(function (){
                        swal('No se pudo realizar la petición.');
                    });
                }
            });
        });
    </script>



@endsection
@if (session('guardar') == 'ok')
<script>
    Swal.fire({
            position: 'top-end',
            icon: 'success',
            title: 'El registro ha sido guardado existosamente.',
            showConfirmButton: false,
            timer: 1500
            })
</script>
@endif
@include('campaigns.modal_asociacion')
@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
    <style>
       /* START - CONF SPINNER */
       table.dataTable thead {background-color:rgb(179, 179, 184)}
       
    </style>
@endsection


