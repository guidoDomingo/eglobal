
@extends('layout')
@section('title')
    Canales
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Canales
            <small>Listado</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Clientes</a></li>
            <li class="active">Canales</li>
        </ol>
    </section>
    <section class="content">
        {{-- @include('partials._flashes') --}}
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">
                </h3>
                @if (Sentinel::hasAccess('canales.add|edit'))
                    <a href="{{ route('canales.create') }}" class="btn-sm btn-primary active" role="button">Agregar</a>
                @endif
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-xs-12">
                            <table id="contents" role="grid" class="table table-bordered table-condensed table-hover">
                                <thead>
                                    <tr>
                                        <th style="text-align:center; vertical-align:middle;width:10px">#</th>
                                        <th style="text-align:center; vertical-align:middle;">Nombre</th>
                                        <th style="text-align:center; vertical-align:middle; width:100px">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($canales as $item)
                                    <tr data-id="{{ $item->id  }}">
                                        <td style="text-align:center; vertical-align:middle;">{{ $item->id }}.</td>
                                        <td style="text-align:center; vertical-align:middle;">{{ $item->descripcion }}</td>
                                        <td style="text-align:center; vertical-align:middle;">
                                            @if (Sentinel::hasAccess('canales.add|edit'))
                                                <a class="btn btn-success btn-flat btn-row" title="Editar" href="{{ route('canales.edit',['canale' => $item->id])}}"><i class="fa fa-pencil"></i></a>
                                            @endif
                                            @if (Sentinel::hasAccess('canales.delete'))
                                                <a class="btn-delete btn btn-danger btn-flat btn-row" title="Eliminar" href="#" ><i class="fa fa-remove"></i> </a>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                    </div>
                </div>
            </div>

        </div>
    </section>
    {!! Form::open(['route' => ['canales.destroy',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
    {!! Form::close() !!}

@endsection
@section('js')

<link rel="stylesheet" href="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.css">
<script src="/bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.min.js"></script>

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
    $('#contents').DataTable(data_table_config);
</script>

<script type="text/javascript">
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

@if (session('actualizar') == 'ok')
<script>
    swal({
            type: 'success',
            title: 'El registro ha sido actualizado existosamente.',
            showConfirmButton: false,
            timer: 1500
            });
</script>
@endif
@if (session('guardar') == 'ok')
<script>
    swal({
        type: 'success',
            title: 'El registro ha sido guardado existosamente.',
            showConfirmButton: false,
            timer: 1500
            });
</script>
@endif
@if (session('error') == 'ok')
<script>
    swal({
            type: "error",
            title: 'Ocurrió un error al intentar registrar el contenido',
            showConfirmButton: true,
            // timer: 1500
            });
</script>
@endif
@endsection

@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
    <style>
       /* START - CONF SPINNER */
       table.dataTable thead {background-color:rgb(179, 179, 184)}
       
    </style>
@endsection