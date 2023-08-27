@extends('layout')
@section('title')
    Artes
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Artes
            <small>Listado</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Promociones</a></li>
            <li class="active">Artes</li>
        </ol>
    </section>
    <section class="content">
        @include('partials._flashes')
        <div class="box">

            <div class="box-header">
                <h3 class="box-title">
                </h3>
                {{-- <a href="{{ route('arts.create') }}" class="btn-sm btn-primary active" role="button">Agregar</a> --}}
                <a class="btn-sm btn-default" href="{{ route('campaigns.index') }}" role="button">Atrás</a>
                <a href="{{ route('arts.create',['campaign_id' => $campaign_id]) }}" class="btn-sm btn-primary active" role="button">Agregar</a>
                <div class="box-tools">
                    <div class="input-group" style="width:150px;">
                        {{-- {!! Form::model(Request::only(['name']),['route' => 'arts.index', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
                        {!! Form::text('name' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Titulo', 'autocomplete' => 'off' ]) !!}
                        {!! Form::close() !!} --}}
                    </div>
                </div>
            </div>
            <div class="box-body  no-padding">
                <div class="row">
                    <div class="col-xs-12">
                        @if ($arts)
                            <table class="table table-hover">
                                <thead>
                                <tr>
                                    <th style="width:10px">#</th>
                                    <th style="text-align:center;">Titulo</th>
                                    <th style="text-align:center; width: 200px;">Imagen</th>
                                    <th style="text-align:center;">Duración de reproducción</th>
                                    <th style="text-align:center;">Duración de la pausa</th>
                                    <th style="text-align:center;">Acciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($arts as $art)
                                    <tr data-id="{{ $art->id  }}">
                                        <td style="text-align:center; vertical-align:middle;">{{ $art->id }}.</td>
                                        <td style="text-align:center; vertical-align:middle; width: 150px">{{ $art->title }}</td>
                                        <td style="text-align:center; vertical-align:middle;  max-width: 250px;">
                                            @if(base64_encode(base64_decode($art->image, true)) === $art->image)
                                                <img class="imagen_marcas_servicios" src="data:image/png;base64,{{ $art->image }}" width="80" height="80">
                                            @else
                                                @if(file_exists(public_path().'/resources/images/arts/'.trim($art->image)) && !empty($art->image))
                                                    <img class="imagen_marcas_servicios" src="{{ url('/resources/images/arts/'.$art->image) }}" width="80" height="80">
                                                @elseif(strstr($art->image, 'http'))
                                                    <img class="imagen_marcas_servicios" src="{{ $art->image }}" width="80" height="80">
                                                @else
                                                    {{ $art->image }}
                                                @endif
                                            @endif
                                        </td>
                                        <td style="vertical-align:middle; text-align:center; width: 150px">{{ $art->duracionReprodu }} Seg.</td>
                                        <td style="vertical-align:middle;  text-align:center; width: 150px">{{ $art->duracionPausa }} Seg.</td>
                                        <td style="vertical-align:middle; text-align:center; width: 100px;">
                                            @if (Sentinel::hasAccess('arts.add|edit'))
                                                <a class="btn btn-success btn-flat btn-row" title="Editar" href="{{ route('arts.edit',['id' => $art->id])}}"><i class="fa fa-pencil"></i></a>
                                            @endif
                                            @if (Sentinel::hasAccess('arts.delete'))
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
                        {{-- <div class="dataTables_info" role="status" aria-live="polite">{{ $arts->total() }} registros en total </div>--}}
                    </div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers">
                            {{-- {!! $arts->appends(Request::only(['name']))->render() !!} --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    {!! Form::open(['route' => ['arts.destroy',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
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
