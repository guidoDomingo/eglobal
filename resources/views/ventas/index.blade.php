@extends('app')
@section('title')
    Venta de Miniterminal
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Ventas
            <small>Listado</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Ventas</a></li>
            <li class="active">Lista</li>
        </ol>
    </section>
    <section class="content">
        @include('partials._flashes')
        <div class="box">

            <div class="box-header">

                <div class="row">
                    <div class="col-md-1">
                        <a href="{{ route('venta.create') }}" class="btn btn-primary btn-sm" role="button">
                            <span class="fa fa-plus"></span> &nbsp; Agregar
                        </a>
                    </div>
                    <div class="col-md-1">
                        <button type="submit" class="btn btn-success btn-sm" title="Convertir tabla en archivo excel."
                            id="export">
                            <span class="fa fa-file-excel-o"></span> &nbsp; Exportar
                        </button>
                    </div>
                </div>

                {!! Form::open(['route' => 'sale_export', 'method' => 'POST', 'role' => 'form', 'id' => 'form_export']) !!}
                <input name="json" id="json" type="hidden" value='{!! $export_list !!}'>
                {!! Form::close() !!}

                <div class="box-tools">
                    <div class="input-group" style="width:150px;">
                        {!! Form::model(Request::only(['name']), ['route' => 'venta.index', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
                        {!! Form::text('num_venta', null, ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Numero de Venta', 'autocomplete' => 'off']) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
            <div class="box-body  no-padding">
                <div class="row">
                    <div class="col-xs-12">
                        @if ($ventas)
                            <table class="table table-striped">
                                <tbody>
                                    <thead>
                                        <tr>
                                            <th style="width:10px">#</th>
                                            <th>Número de Serie</th>
                                            <th>Grupo</th>
                                            <th>Tipo de Venta</th>
                                            <th>Monto</th>
                                            <th>Vendedor</th>
                                            <th>Número de Venta</th>
                                            <th style="width:150">Creado</th>
                                        </tr>
                                    </thead>
                                <tbody>
                                    @foreach ($ventas as $venta)
                                        <tr data-id="{{ $venta->id }}">
                                            <td>{{ $venta->id }}.</td>
                                            <td>{{ $venta->num_serie }}</td>
                                            <td>{{ $venta->group->description ?? "" }}</td>
                                            @if ($venta->tipo_venta == 'co')
                                                <td>Contado</td>
                                            @else
                                                <td>Credito</td>
                                            @endif
                                            <td>{{ number_format($venta->amount, 0) }}</td>
                                            @if ($venta->vendedor != null)
                                                <td>{{ $venta->vendedor }}</td>
                                            @else
                                                <td> </td>
                                            @endif
                                            @if ($venta->num_venta != null)
                                                <td>{{ $venta->num_venta }}</td>
                                            @else
                                                <td> </td>
                                            @endif
                                            <td>{{ date('d/m/y H:i', strtotime($venta->created_at)) }}</td>
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
                        <div class="dataTables_info" role="status" aria-live="polite">{{ $ventas->total() }} registros en
                            total
                        </div>
                    </div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers">
                            {!! $ventas->appends(Request::only(['id']))->render() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    {!! Form::open(['route' => ['venta.destroy', ':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
    {!! Form::close() !!}

@endsection

@section('page_scripts')
    @include('partials._delete_row_js')
@endsection

@section('js')
    <script type="text/javascript">
        $("#export").click(function() {
            if ($('#json').val() !== null && $('#json').val() !== '') {
                $('#form_export').submit();
            } else {
                swal({
                    title: 'Atención',
                    text: 'La lista no tiene registros para exportar.',
                    type: 'warning',
                    showCancelButton: false,
                    closeOnConfirm: true,
                    closeOnCancel: false,
                    confirmButtonColor: '#2778c4',
                    confirmButtonText: 'Aceptar'
                });
            }
        });
    </script>
@endsection
