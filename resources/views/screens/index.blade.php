@extends('layout')

@section('title')
    Pantallas
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Pantallas
            <small>Listado de pantallas de Aplicación</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="{{ route('applications.index') }}">Aplicaciones</a></li>
            <li><a href="{{ route('screens.index') }}">Pantallas</a></li>
            <li class="active">Lista</li>
        </ol>
    </section>
    <section class="content">
        @include('partials._flashes')
        <div class="box">

            <div class="box-header">
                <h3 class="box-title">
                </h3>
                <a href="{{ route('screens.create', ['app_id' => $app_id ]) }}" class="btn-sm btn-primary active" role="button">Agregar</a>
                <div class="box-tools">
                    <div class="input-group" style="width:150px;">
                        {!! Form::model(Request::only(['name']),['route' => ['applications.screens.index',$app_id], 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
                        {!! Form::text('name' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Nombre', 'autocomplete' => 'off' ]) !!}
                        {!! Form::close()!!}
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
                                <th>Nombre</th>
                                <th style="width:150px">Creado</th>
                                <th style="width:150px">Modificado</th>
                                <th style="width:230px">Acciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($screens as $screen)
                                <tr data-id="{{ $screen->id  }}">
                                    <td>{{ $screen->id }}.</td>
                                    <td>{{ $screen->name }}</td>
                                    <td>{{ $screen->created_at }}</td>
                                    <td>{{ $screen->updated_at }}</td>
                                    <td>
                                        @if (Sentinel::hasAccess('applications.screens.add|edit'))
                                        <a class="btn btn-success btn-flat btn-row" title="Editar" href="{{ route('screens.edit',['id' => $screen->id])}}"><i class="fa fa-pencil"></i></a>
                                        @endif
                                        @if (Sentinel::hasAccess('applications.screens.delete'))
                                        <a class="btn-delete btn btn-danger btn-flat btn-row" title="Eliminar" href="#" ><i class="fa fa-remove"></i> </a>
                                        @endif
                                        @if (Sentinel::hasAccess('applications.screens.objects'))
                                        <a class="btn btn-info btn-flat btn-row" title="Objetos" href="{{ route('screens.screens_objects.index',['screen_id' => $screen->id ]) }}"><i class="fa fa-object-ungroup"></i></a>
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
                        <div class="dataTables_info" role="status" aria-live="polite">{{ $screens->total() }} registros en total</div>
                    </div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers">
                            {!! $screens->appends(Request::only(['name']))->render() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {!! Form::open(['route' => ['screens.destroy',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
    {!! Form::close() !!}


@endsection
@section('page_scripts')
    @include('partials._delete_row_js')
@endsection
