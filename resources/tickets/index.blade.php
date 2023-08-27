@extends('layout')
@section('title')
    Tickets
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Tickets
            <small>Listado</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Promociones</a></li>
            <li class="active">Tickets</li>
        </ol>
    </section>
    <section class="content">
        @include('partials._flashes')
        <div class="box">

            <div class="box-header">
                <h3 class="box-title">
                </h3>
                {{-- <a href="{{ route('tickets.create') }}" class="btn-sm btn-primary active" role="button">Agregar</a> --}}
                <a class="btn-sm btn-default" href="{{ route('campaigns.index') }}" role="button">Atrás</a>
                <a href="{{ route('tickets.create',['campaign_id' => $campaign_id]) }}" class="btn-sm btn-primary active" role="button">Agregar</a>
                <div class="box-tools">
                    {{-- <div class="input-group" style="width:150px;">
                        {!! Form::model(Request::only(['name']),['route' => 'tickets.index', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
                        {!! Form::text('name' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Nombre de Campaña', 'autocomplete' => 'off' ]) !!}
                        {!! Form::close() !!}
                    </div> --}}
                </div>
            </div>
            <div class="box-body  no-padding">
                <div class="row">
                    <div class="col-xs-12">
                        @if ($tickets)
                            <table class="table table-hover">
                                <tbody>
                                <thead>
                                <tr>
                                    <th style="width:10px">#</th>
                                    {{-- <th style="text-align:center;">Campaña asociada</th> --}}
                                    <th style="text-align:center;">Header</th>
                                    <th style="text-align:center;">Footer</th>
                                    <th style="text-align:center;">Acciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($tickets as $ticket)
                                    <tr data-id="{{ $ticket->id  }}">
                                        <td>{{ $ticket->id }}</td>
                                        {{-- @foreach ($campaigns as $campaign )
                                            @if ($ticket->campaigns_id == $campaign->id)
                                                <td>{{$campaign->name}}</td>
                                            @endif
                                        @endforeach --}}
                                        <td style="text-align:center;">{{ $ticket->header }}</td>
                                        <td style="text-align:center;">{{ $ticket->footer }}</td>                    
                                        <td style="text-align:center; width: 150px;">
                                            <a class="btn btn-warning btn-flat btn-row" title="Etiquetas" href="{{ route('tags.index',['ticket_id' => $ticket->id, 'campaign_id' => $campaign_id ])}}"><i class="fa fa-code"></i></a>

                                            @if (Sentinel::hasAccess('tickets.add|edit'))
                                            <a class="btn btn-success btn-flat btn-row" title="Editar" href="{{ route('tickets.edit',['id' => $ticket->id])}}"><i class="fa fa-pencil"></i></a>
                                            @endif
                                            @if (Sentinel::hasAccess('tickets.delete'))
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
                        {{-- <div class="dataTables_info" role="status" aria-live="polite">{{ $tickets->total() }} registros en total --}}
                        </div>
                    </div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers">
                            {{-- {!! $tickets->appends(Request::only(['name']))->render() !!} --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    {!! Form::open(['route' => ['tickets.destroy',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
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
