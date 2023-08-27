@extends('layout')
@section('title')
    Dispositivos
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Dispositivos
            <small>Listado</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Dispositivos</a></li>
            <li class="active">lista</li>
        </ol>
    </section>
    <section class="content">
        @include('partials._flashes')
        <div class="box">

            <div class="box-header">
                <h3 class="box-title">
                </h3>
                @if (Sentinel::hasAccess('devices.import'))
                    <a href="{{ route('housing.device.import', [$housingId]) }}" class="btn-sm btn-primary active" role="button">Importar</a>
                @endif
                @if (Sentinel::hasAccess('devices.add|edit'))
                    <a href="{{ route('housing.device.create', ['housing' => $housingId]) }}" class="btn-sm btn-primary active" role="button">Agregar</a>
                @endif
                    <a href="{{ route('miniterminales.index') }}" class="btn-sm btn-default active" role="button">Volver</a>
                <p>
                <div class="box-tools">
                    <div class="input-group" style="width:150px;">
                        {!! Form::model(Request::only(['name']), ['route' => ['housing.device.index', $housingId], 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
                        {!! Form::text('name', null, ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Serial Number', 'autocomplete' => 'off']) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
            <div class="box-body  no-padding">
                <div class="row">
                    <div class="col-xs-12">
                        <table class="table table-striped">
                            <tbody>
                                <thead>
                                    <tr>
                                        <th style="width:10px">#</th>
                                        <th>Device serial</th>
                                        <th>Modelo</th>
                                        <th>Fecha de Instalación</th>
                                        <th>Activo</th>
                                        <th>Fecha de Activación</th>
                                        <th style="width:200px">Acciones</th>
                                    </tr>
                                </thead>
                                @foreach ($devices as $device)
                                    <tr data-id="{{ $device->id }}">
                                        <td>{{ $device->id }}.</td>
                                        <td>{{ $device->serialnumber }}</td>
                                        <td>{{ $device->model['description'] }}</td>
                                        <td>{{ date('d/m/y H:i', strtotime($device->installation_date)) }} </td>
                                        @if ($device->activo == 1)
                                            <td>Sí</td>
                                        @else
                                            <td>No</td>
                                        @endif
                                        <td>{{ $device->activated_at }} </td>
                                        <td>
                                            @if (Sentinel::hasAccess('devices.add|edit')) 
                                                <a class="btn btn-success btn-flat btn-row" title="Editar" href="{{ route('housing.device.edit', [$housingId, $device]) }}"><i class="fa fa-pencil"></i></a>
                                            @endif
                                            @if (Sentinel::hasAccess('devices.delete'))
                                                <a class="btn-delete btn btn-danger btn-flat btn-row" title="Eliminar" href="#"><i class="fa fa-remove"></i></a>
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
                        <div class="dataTables_info" role="status" aria-live="polite">{{ $devices->total() }} registros
                            en total</div>
                    </div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers">
                            {!! $devices->appends(Request::only(['name']))->render() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </section>
    {!! Form::open(['route' => ['housing.device.destroy', $housingId, ':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
    {!! Form::close() !!}

@endsection
@section('page_scripts')
    @include('partials._delete_row_js')
@endsection
