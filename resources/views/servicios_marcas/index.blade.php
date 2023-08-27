@extends('layout')
@section('title')
    Servicios Por Marcas
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Servicios Por Marcas
            <small>Listado</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Servicios Por Marcas</a></li>
            <li class="active">Lista</li>
        </ol>
    </section>
    <section class="content">
        @include('partials._flashes')
        <div class="box">

            <div class="box-header">
                <h3 class="box-title">
                </h3>
                <a href="{{ route('servicios_marca.create') }}" class="btn-sm btn-primary active" role="button">Agregar</a>
                <div class="box-tools">
                    <div class="input-group" style="width:150px;">
                        {!! Form::model(Request::only(['name']),['route' => 'servicios_marca.index', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
                        {!! Form::text('name' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Nombre', 'autocomplete' => 'off' ]) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
            <div class="box-body  no-padding" style="overflow: scroll">
                <div class="row">
                    <div class="col-xs-12">
                        @if ($servicios_marcas)
                            <table class="table table-striped">
                                <tbody>
                                <thead>
                                <tr>
                                    <th>Marca</th>
                                    <th>Descripción</th>
                                    <th>Service Source</th>
                                    <th>Imagen Asociada</th>
                                    <th>Service Id</th>
                                    <th>Cód. Ondanet</th>
                                    <th>Nivel</th>
                                    <th>Tipo</th>
                                    <th>Promedio Comisión</th>
                                    <th style="width:100px">Creado</th>
                                    <th style="width:100px">Modificado</th>
                                    <th>Acciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($servicios_marcas as $servicios_marca)
                                    <tr data-service-id="{{ $servicios_marca->service_id }}" data-service-source-id="{{ $servicios_marca->service_source_id  }}">
                                        @if(isset($servicios_marca->marcas->descripcion))
                                        <td>{{ $servicios_marca->marcas->descripcion }}.</td>
                                        @else 
                                        <td>-</td>
                                        @endif 
                                        <td>{{ $servicios_marca->descripcion }}</td>
                                        <td>{{ $servicios_marca->service_sources->description }}</td>
                                        <td>
                                            @if(file_exists(public_path().'/resources'.trim($servicios_marca->imagen_asociada)) && !empty($servicios_marca->imagen_asociada))
                                                <img class="imagen_marcas_servicios" src="{{ url('/resources'.$servicios_marca->imagen_asociada) }}">
                                            @else
                                                {{ $servicios_marca->imagen_asociada }}
                                            @endif
                                        </td>
                                        <td>{{ $servicios_marca->service_id }}</td>
                                        <td>{{ $servicios_marca->ondanet_code }}</td>
                                        <td>{{ $servicios_marca->nivel }}</td>
                                        <td>{{ $servicios_marca->tipo }}</td>
                                        <td>{{ $servicios_marca->promedio_comision }}</td>
                                        <td>{{ date('d/m/y H:i', strtotime($servicios_marca->created_at)) }}
                                            </td>
                                        <td>{{ date('d/m/y H:i', strtotime($servicios_marca->updated_at)) }}</td>
                                        <td>
                                            @if (Sentinel::hasAccess('servicio_marca.add|edit'))
                                            <a class="btn btn-success btn-flat btn-row" title="Editar" href="{{ route('servicios_marca.edit',['service_id' => $servicios_marca->service_id,'service_source_id' => $servicios_marca->service_source_id])}}"><i class="fa fa-pencil"></i></a>
                                            @endif
                                            @if (Sentinel::hasAccess('servicio_marca.delete'))
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
                        <div class="dataTables_info" role="status" aria-live="polite">{{ $servicios_marcas->total() }} registros en total
                        </div>
                    </div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers">
                            {!! $servicios_marcas->appends(Request::only(['name']))->render() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    {!! Form::open(['route' => ['servicios_marca.destroy',':ROW_ID',':SOURCE_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
    {!! Form::close() !!}

@endsection
@section('js')
<script type="text/javascript">
    $('.btn-delete').click(function(e){
        e.preventDefault();
        var row = $(this).parents('tr');
        var service_id = row.data('service-id');
        var service_source_id = row.data('service-source-id');
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
                var url = form.attr('action').replace(':ROW_ID',service_id);
                var url = url.replace(':SOURCE_ID',service_source_id);
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
