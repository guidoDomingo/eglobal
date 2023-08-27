@extends('layout')
@section('title')
    Vouchers
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Campañas/Promociones
            <small>Generar vouchers</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Promociones</a></li>
            <li class="active">Campañas</li>
        </ol>
    </section>
    <section class="content">
        @include('partials._flashes')
        <div class="box">

            <div class="box-header">
                <h3 class="box-title">
                </h3>
                {{-- <a href="{{ route('promotions_vouchers.create') }}" class="btn-sm btn-primary active" role="button">Generar</a> --}}
                {{-- <a href="{{ route('promotions_vouchers.create') }}" class="btn-sm btn-success active" role="button">Importar</a> --}}

                <div class="box-tools">
                    <div class="input-group" style="width:150px;">
                        {!! Form::model(Request::only(['name']),['route' => 'promotions_vouchers.index', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
                        {!! Form::text('name' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Nombre de la campaña...', 'autocomplete' => 'off' ]) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
            <div class="box-body  no-padding">
                <div class="row">
                    <div class="col-xs-12">
                        @if ($campaigns)
                            <table class="table table-hover">
                                <tbody>
                                <thead>
                                <tr>
                                    <th style="width:10px">#</th>
                                    <th style="text-align:center;">Campaña</th>
                                    <th style="text-align:center;">Fecha de inicio</th>
                                    <th style="text-align:center;">Fecha de finalización</th>
                                    <th style="text-align:center;">Tipo de campaña</th>
                                    <th style="text-align:center;">Acciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($campaigns as $campaign)
                                    <tr data-id="{{ $campaign->id  }}">
                                        <td>{{ $campaign->id }}.</td>
                                        <td>{{$campaign->name}}</td>
                                        <td style="text-align:center;">{{ date('d/m/Y', strtotime($campaign->start_date)) }}</td>
                                        <td style="text-align:center;">{{ date('d/m/Y', strtotime($campaign->end_date)) }}</td>
                                        @if ( $campaign->tipoCampaña == 1)
                                            <td>Campaña informativa</td>
                                        @elseif ($campaign->tipoCampaña == 2)                                     
                                            <td>Promoción de productos</td>
                                        @elseif ($campaign->tipoCampaña == 3)
                                            <td>Promoción + venta de productos</td>
                                        @endif
                                        <td style="text-align:center; width: 100px;">
                                            @if (Sentinel::hasAccess('promotions_vouchers.show'))
                                                <a class="btn btn-info btn-flat btn-row" title="Vouchers" href="{{ route('promotions_vouchers.show',['campaignId' => $campaign->id ]) }}"><i class="fa fa-newspaper-o"></i></a>
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
                        {{-- <div class="dataTables_info" role="status" aria-live="polite">{{ $campaigns->total() }} registros en total --}}
                        </div>
                    </div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers">
                            {{-- {!! $campaigns->appends(Request::only(['name']))->render() !!} --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    {!! Form::open(['route' => ['promotions_vouchers.destroy',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
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
