@extends('app')
@section('title')
    Parametros Comisiones
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Parametros Comisiones
            <small>Listado</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Parametros Comisiones</a></li>
            <li class="active">Lista</li>
        </ol>
    </section>
    <section class="content">
        @include('partials._flashes')
        <div class="box">

            <div class="box-header">
                <h3 class="box-title">
                </h3>
                <a href="{{ route('parametros_comisiones.create') }}" class="btn-sm btn-primary active" role="button">Agregar</a>
                <div class="box-tools">
                    <div class="input-group" style="width:150px;">
                        {!! Form::model(Request::only(['name']),['route' => 'parametros_comisiones.index', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
                        {!! Form::text('name' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Nombre', 'autocomplete' => 'off' ]) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
            <div class="box-body  no-padding" style="overflow: scroll">
                <div class="row">
                    <div class="col-xs-12">
                        @if ($parametros_comisiones)
                            <table class="table table-striped">
                                <tbody>
                                <thead>
                                <tr>
                                    <th style="width:10px">#</th>
                                    <th>Atm</th>
                                    <th>Tipo de Servicio</th>
                                    <th>Service Source</th>
                                    <th>Descripción</th>
                                    <th>Porcentaje Comisión</th>
                                    <th style="width:100px">Creado</th>
                                    <th style="width:100px">Modificado</th>
                                    <th>Acciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($parametros_comisiones as $parametro)
                                    <tr data-id="{{ $parametro->id  }}">
                                        <td>{{ $parametro->id }}.</td>
                                        @if(isset($parametro->atm->name))
                                        <td>{{ $parametro->atm->name }}</td>
                                        @else 
                                        <td>-</td>
                                        @endif
                                        @if($parametro->tipo_servicio_id == 1)
                                            <td>Integración Propia</td>
                                            <td></td>
                                            <td>{{ $parametro->name }} - {{ $parametro->description }}</td>
                                        @else
                                            <td>Boca de Cobranzas</td>
                                            <td>{{ $parametro->service_source->description }}</td>
                                            <td>{{ $parametro->servicio }}</td>
                                        @endif
                                        <td>{{ number_format($parametro->comision, 4, '.', ',') }}</td>
                                        <td>{{ date('d/m/y H:i', strtotime($parametro->created_at)) }}
                                            </td>
                                        <td>{{ date('d/m/y H:i', strtotime($parametro->updated_at)) }}</td>
                                        <td>
                                            @if (Sentinel::hasAccess('parametros_comisiones.add|edit'))
                                            <a class="btn btn-success btn-flat btn-row" title="Editar" href="{{ route('parametros_comisiones.edit',['parametros_comisione' => $parametro->id])}}"><i class="fa fa-pencil"></i></a>
                                            @endif
                                            @if (Sentinel::hasAccess('parametros_comisiones.delete'))
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
                        <div class="dataTables_info" role="status" aria-live="polite">{{ $parametros_comisiones->total() }} registros en total
                        </div>
                    </div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers">
                            {!! $parametros_comisiones->appends(Request::only(['name']))->render() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    {!! Form::open(['route' => ['parametros_comisiones.destroy',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
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
