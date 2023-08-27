@extends('layout')
@section('title')
    Formularios
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Captura de datos
            <small>Listado</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Promociones</a></li>
            <li class="active">Formularios</li>
        </ol>
    </section>
    <section class="content">
        @include('partials._flashes')
        <div class="box">

            <div class="box-header">
                <h3 class="box-title">
                </h3>
                {{-- <a href="{{ route('forms.create') }}" class="btn-sm btn-primary active" role="button">Agregar</a> --}}
                <a class="btn-sm btn-default" href="{{ route('campaigns.index') }}" role="button">Atrás</a>
                <a href="{{ route('forms.create',['campaign_id' => $campaign_id]) }}" class="btn-sm btn-primary active" role="button">Agregar</a>
                <div class="box-tools">
                    <div class="input-group" style="width:150px;">
                        {{-- {!! Form::model(Request::only(['name']),['route' => 'forms.index', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
                        {!! Form::text('name' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Nombre de Campaña', 'autocomplete' => 'off' ]) !!}
                        {!! Form::close() !!} --}}
                    </div>
                </div>
            </div>
            <div class="box-body no-padding">
                <div class="row">
                    <div class="col-xs-12">
                        @if ($forms)
                            <table class="table table-hover">
                                <tbody>
                                <thead>
                                <tr>
                                    <th style="width:10px">#</th>
                                    <th style="text-align:center;">Título a desplegar</th>
                                    <th style="text-align:center;">Tipo de dato</th>
                                    <th style="text-align:center;">Valor mínimo</th>
                                    <th style="text-align:center;">Valor máximo</th>
                                    <th style="text-align:center;">Acciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($forms as $form)
                                    <tr data-id="{{ $form->id  }}">
                                        <td>{{ $form->id }}</td>
                                        <td style="text-align:center; vertical-align:middle;">{{ $form->label }}</td>
                                        <td style="text-align:center; vertical-align:middle;">{{ $form->data_type }}</td>
                                        <td style="text-align:center; vertical-align:middle;">{{ $form->valorminimo }}</td>
                                        <td style="text-align:center; vertical-align:middle;">{{ $form->valormaximo }}</td>
                    
                                        <td style="text-align:center; vertical-align:middle; width: 100px;">
                                            @if (Sentinel::hasAccess('forms.add|edit'))
                                            <a class="btn btn-success btn-flat btn-row" title="Editar" href="{{ route('forms.edit',['id' => $form->id])}}"><i class="fa fa-pencil"></i></a>
                                            @endif
                                            @if (Sentinel::hasAccess('forms.delete'))
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
                        {{-- <div class="dataTables_info" role="status" aria-live="polite">{{ $forms->total() }} registros en total --}}
                        </div>
                    </div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers">
                            {{-- {!! $forms->appends(Request::only(['name']))->render() !!} --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    {!! Form::open(['route' => ['forms.destroy',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
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
@endsection
