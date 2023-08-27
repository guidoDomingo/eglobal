@extends('layout')
@section('title')
    Marcas
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Marcas
            <small>Listado</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Marcas</a></li>
            <li class="active">Lista</li>
        </ol>
    </section>
    <section class="content">
        @include('partials._flashes')
        <div class="box">

            <div class="box-header">
                <h3 class="box-title">
                </h3>
                <a href="{{ route('marca.create') }}" class="btn-sm btn-primary active" role="button">Agregar</a>
                <div class="box-tools">
                    <div class="input-group" style="width:150px;">
                        {!! Form::model(Request::only(['name']),['route' => 'marca.index', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
                        {!! Form::text('name' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Nombre', 'autocomplete' => 'off' ]) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
            <div class="box-body  no-padding" style="overflow: scroll">
                <div class="row">
                    <div class="col-xs-12">
                        @if ($marcas)
                            <table class="table table-striped">
                                <tbody>
                                <thead>
                                <tr>
                                    <th style="width:10px">#</th>
                                    <th>Descripción</th>
                                    <th>Service Source</th>
                                    <th>Imagen Asociada</th>
                                    <th>Categoría</th>
                                    <th style="width:100px">Creado</th>
                                    <th style="width:100px">Modificado</th>
                                    <th>Acciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($marcas as $marca)
                                    <tr data-id="{{ $marca->id  }}">
                                        <td>{{ $marca->id }}.</td>
                                        <td>{{ $marca->descripcion }}</td>
                                        <td>{{ $marca->service_sources->description }}</td>
                                        <td>
                                            @if(base64_encode(base64_decode($marca->imagen_asociada, true)) === $marca->imagen_asociada)
                                                <img class="imagen_marcas_servicios" src="data:image/png;base64,{{ $marca->imagen_asociada }}">
                                            @else
                                                @if(file_exists(public_path().'/resources'.trim($marca->imagen_asociada)) && !empty($marca->imagen_asociada))
                                                    <img class="imagen_marcas_servicios" src="{{ url('/resources'.$marca->imagen_asociada) }}">
                                                @elseif(strstr($marca->imagen_asociada, 'http'))
                                                    <img class="imagen_marcas_servicios" src="{{ $marca->imagen_asociada }}">
                                                @else
                                                    {{ $marca->imagen_asociada }}
                                                @endif
                                            @endif
                                        </td>
                                        @if(isset($marca->categorias->name))
                                            <td>{{ $marca->categorias->name }}</td>
                                        @else
                                            <td style="font-style: italic;"> Sin categoría asociada </td>
                                        @endif
                                        <td>{{ date('d/m/y H:i', strtotime($marca->created_at)) }}
                                            </td>
                                        <td>{{ date('d/m/y H:i', strtotime($marca->updated_at)) }}</td>
                                        <td>
                                            @if (Sentinel::hasAccess('marca.add|edit'))
                                            <a class="btn btn-success btn-flat btn-row" title="Editar" href="{{ route('marca.edit',['marca' => $marca->id])}}"><i class="fa fa-pencil"></i></a>
                                            @endif
                                            @if (Sentinel::hasAccess('marca.delete'))
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
            <div class="box-footer clearfix">
                <div class="row">
                    <div class="col-sm-5">
                        <div class="dataTables_info" role="status" aria-live="polite">{{ $marcas->total() }} registros en total
                        </div>
                    </div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers">
                            {!! $marcas->appends(Request::only(['name']))->render() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    {!! Form::open(['route' => ['marca.destroy',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
    {!! Form::close() !!}

@endsection
@section('js')
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
    {{-- @include('partials._delete_row_js') --}}
@endsection
