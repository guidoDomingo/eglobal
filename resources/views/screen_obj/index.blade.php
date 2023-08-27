@extends('layout')

@section('title')
    Objetos de Pantalla
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Objetos
            <small>Listado de Objetos de Pantallas</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="{{ route('applications.index') }}">Apps</a></li>
            <li><a href="{{ route('screens.index') }}">Pantallas</a></li>
            <li><a href="{{ route('screens_objects.index') }}">Objetos</a></li>
            <li class="active">lista</li>
        </ol>
    </section>
    <section class="content">
        @include('partials._flashes')
        <div class="box">

            <div class="box-header">
                <h3 class="box-title">
                </h3>
                <a href="{{ route('screens_objects.create', ['screen_id' => $screen_id ]) }}"
                   class="btn-sm btn-primary active" role="button">Agregar</a>
                <div class="box-tools">
                    <div class="input-group" style="width:150px;">
                        {!! Form::model(Request::only(['name']),['route' => ['screens.screens_objects.index',$screen_id], 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
                        {!! Form::text('name' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Nombre', 'autocomplete' => 'off' ]) !!}
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
                                <th>Nombre</th>
                                <th>Tipo de Objeto</th>
                                <th>Posición X</th>
                                <th>Posición Y</th>
                                <th style="width:150px">Creado</th>
                                <th style="width:150px">Modificado</th>
                                <th style="width:150px">Acciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($objects as $object)
                                <tr data-id="{{ $object->id  }}">
                                    <td>{{ $object->id }}.</td>
                                    <td>{{ $object->name }}</td>
                                    <td>{{ $object->objectType->name }}</td>
                                    <td>{{ $object->location_x }}</td>
                                    <td>{{ $object->location_y }}</td>
                                    <td>{{ $object->created_at }}</td>
                                    <td>{{ $object->updated_at }}</td>
                                    <td>
                                        @if (Sentinel::hasAccess('applications.screens.objects.add|edit'))
                                        <a class="btn btn-success btn-flat btn-row" title="Editar" href="{{ route('screens_objects.edit',$object)}}"><i class="fa fa-pencil"></i></a>
                                        @endif
                                        @if (Sentinel::hasAccess('applications.screens.objects.delete'))
                                        <a href="#" class="btn btn-danger btn-flat btn-row btn-delete" title="Eliminar" ><i class="fa fa-remove"></i> </a>
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
                        <div class="dataTables_info" role="status" aria-live="polite">{{ $objects->total() }} registros en total

                        </div>
                    </div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers">
                            {!! $objects->appends(Request::only(['name']))->render() !!}
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    {!! Form::open(['route' => ['screens_objects.destroy',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
    {!! Form::close() !!}


@endsection
@section('page_scripts')
    @include('partials._delete_row_js')
@endsection
