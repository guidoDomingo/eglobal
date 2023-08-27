@extends('app')
@section('title')
    Configuración de Alertas
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Configuración de Alertas
            <small>Listado</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Configuraciones</a></li>
            <li class="active">Lista</li>
        </ol>
    </section>
    <section class="content">
        @include('partials._flashes')
        <div class="box">

            <div class="box-header">
                <h3 class="box-title">
                </h3>
                {{-- <a href="{{ route('notifications_params.create') }}" class="btn-sm btn-primary active" role="button">Agregar</a> --}}
                <div class="box-tools">
                    <div class="input-group" style="width:150px;">
                        {!! Form::model(Request::only(['name']),['route' => 'notifications_params.index', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
                        {!! Form::text('name' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Nombre', 'autocomplete' => 'off' ]) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
            <div class="box-body  no-padding">
                <div class="row">
                    <div class="col-xs-12">
                        @if ($notifications_params)
                            <table class="table table-striped">
                                <tbody>
                                <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Tipo de Notificación</th>
                                    <th>Alerta</th>
                                    <th>Mensaje</th>
                                    <th>Red</th>
                                    {{-- <th>Servicios</th> --}}
                                    <th>Valor</th>
                                    <th style="width:100px">Creado</th>
                                    <th style="width:100px">Modificado</th>
                                    <th>Acciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($notifications_params as $notification)
                                    <tr data-service-id="{{ $notification->id }}" data-service-source-id="{{ $notification->service_source_id  }}">
                                        <td>{{ $notification->id }}</td>
                                        <td>{{ $notification->tipo_notificacion }}</td>
                                        <td>{{ $notification->prefix }}</td>
                                        <td>{{ $notification->mensaje }}</td>
                                        <td>{{ $notification->services_sources }}</td>
                                        {{-- <td>{{ $notification->service_id }}</td> --}}
                                        <td>{{ $notification->valor }}</td>
                                        <td>{{ date('d/m/y H:i', strtotime($notification->created_at)) }}
                                            </td>
                                        <td>{{ date('d/m/y H:i', strtotime($notification->updated_at)) }}</td>
                                        <td>
                                            @if (Sentinel::hasAccess('notifications_params.add|edit'))
                                            <a class="btn btn-success btn-flat btn-row" title="Editar" href="{{ route('notifications_params.edit',['notifications_param' => $notification->id])}}"><i class="fa fa-pencil"></i></a>
                                            <a class="btn btn-info btn-flat btn-row" title="Duplicar Alerta" href="{{ route('notifications_params.duplicate',['id' => $notification->id])}}"><i class="fa fa-copy"></i></a>
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
                        <div class="dataTables_info" role="status" aria-live="polite">{{ $notifications_params->total() }} registros en total
                        </div>
                    </div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers">
                            {!! $notifications_params->appends(Request::only(['name']))->render() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    {!! Form::open(['route' => ['notifications_params.destroy',':ROW_ID',':SOURCE_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
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
