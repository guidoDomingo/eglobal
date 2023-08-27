@extends('layout')
@section('title')
    Puntos de Venta
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Puntos de Venta
            <small>Listado</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Puntos de Venta</a></li>
            <li class="active">lista</li>
        </ol>
    </section>
    <section class="content">
        @include('partials._flashes')
        <div class="box">

            <div class="box-header">
                <h3 class="box-title">
                </h3>
                <a href="{{ route('pos.create') }}" class="btn-sm btn-primary active" role="button">Agregar</a>
                <div class="box-tools">
                    <div class="input-group" style="width:150px;">
                        {!! Form::model(Request::only(['name']),['route' => 'pos.index', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
                        {!! Form::text('name' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Nombre', 'autocomplete' => 'off' ]) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
            <div class="box-body  no-padding">
                <div class="row">
                    <div class="col-xs-12">
                        <table class="table table-striped">
                            <tbody><thead>
                            <tr>
                                <th style="width:10px">#</th>
                                <th style="width:200px">Nombre</th>
                                <th style="width:100px">Código</th>
                                <th style="width:200px">Red</th>
                                <th style="width:200px">Sucursal</th>
                                <th style="width:150px">Creado</th>
                                <th style="width:150px">Modificado</th>
                                <th style="width:300px">Acciones</th>
                                <th style="width:200px">Comprobantes</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($pointsofsale as $pos)
                                <tr data-id="{{ $pos->id  }}">
                                    <td>{{ $pos->id }}.</td>
                                    <td>{{ $pos->description }}</td>
                                    <td>{{ $pos->pos_code }}</td>
                                    <td>{{ $pos->branch->owner->name }}</td>
                                    <td style="width:200px">{{ $pos->branch->description }}</td>
                                    <td>{{ date('d/m/y H:i', strtotime($pos->created_at)) }}</td>
                                    <td>{{ date('d/m/y H:i', strtotime($pos->updated_at)) }} @if($pos->updatedBy != null) - <i>{{ $pos->createdBy->username }}</i> @endif </td>
                                    <td>
                                        @if (Sentinel::hasAccess('pos.edit'))
                                        <a class="btn btn-success btn-flat btn-row" title="Editar" href="{{ route('pos.edit',$pos)}}"><i class="fa fa-pencil"></i></a>
                                        @endif
                                        @if (Sentinel::hasAccess('pos.assign.atm'))
                                        <a class="btn btn-info btn-flat btn-row" title="Asignar ATM" href="{{ route('pos.atm.show.assign', ['id' => $pos->id]) }}"><i class="fa fa-building"></i></a>
                                        @endif
                                        @if (Sentinel::hasAccess('pos.delete'))
                                        <a class="btn-delete btn btn-danger btn-flat btn-row" title="Eliminar" href="#" ><i class="fa fa-remove"></i></a>
                                        @endif
                                    </td>
                                    <td>
                                        @if (Sentinel::hasAccess('vouchers.add|edit'))
                                            <a class="btn btn-warning btn-flat btn-row" title="Tipos Comprobantes" href="{{ route('pointsofsale.vouchertypes.index',$pos->id) }}"><i class="fa fa-book"></i></a>
                                        @endif
                                        @if (Sentinel::hasAccess('vouchers.add|edit'))
                                            <a class="btn btn-info btn-flat btn-row" title="Asignar Tipos Comprobantes" href="{{ route('pointsofsale.vouchers.index',$pos->id) }}"><i class="fa fa-list-alt"></i></a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                                </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="box-footer clearfix">
                <div class="row">
                    <div class="col-sm-5">
                        <div class="dataTables_info" role="status" aria-live="polite">{{ $pointsofsale->total() }} registros en total</div>
                    </div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers">
                            {!! $pointsofsale->appends(Request::only(['name']))->render() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {!! Form::open(['route' => ['pos.destroy',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
    {!! Form::close() !!}


@endsection
@section('page_scripts')
    @include('partials._delete_row_js')
@endsection
