@extends('app')
@section('title')
    Roles
@endsection

@section('aditional_css')
    <!-- Bootstrap 3.3.4 -->
    <link rel="stylesheet" href="{{ URL::asset('/bower_components/admin-lte/bootstrap/css/bootstrap.min.css') }}">
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Roles
            <small>Listado</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Roles</a></li>
            <li class="active">lista</li>
        </ol>
    </section>
    <section class="content">
        @include('partials._flashes')
        <div class="">

            <div class="box-header">
                <a href="{{ route('roles.create') }}" class="btn btn-primary mb-2 me-4" role="button">Agregar</a>
                <div class="box-tools">
                    <div class="input-group" style="width:200px;">
                        {!! Form::model(Request::only(['name']), [
                            'route' => 'roles.index',
                            'method' => 'GET',
                            'class' => 'form-horizontal',
                            'role' => 'search',
                        ]) !!}
                        {!! Form::text('name', null, [
                            'class' => 'form-control input-sm pull-right',
                            'placeholder' => 'Nombre',
                            'autocomplete' => 'off',
                        ]) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
            <div class="row layout-top-spacing">
                <div class="col-xl-12 col-lg-12 col-sm-12  layout-spacing">
                    <div class="widget-content widget-content-area br-8">
                        @if ($roles)
                            <table id="zero-config" class="table table-striped dt-table-hover display responsive nowrap"
                                style="width:100%">

                                <thead>
                                    <tr>
                                        <th style="width:10px">#</th>
                                        <th>Nombre</th>
                                        <th>Descripci&oacute;n</th>
                                        <th style="width:300px">Creado</th>
                                        <th style="width:300px">Modificado</th>
                                        <th style="width:250px">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>

                                    @foreach ($roles as $role)
                                        <tr data-id="{{ $role->id }}">
                                            <td>{{ $role->id }}.</td>
                                            <td>{{ $role->name }}</td>
                                            <td>{{ $role->description }}</td>
                                            <td>{{ date('d/m/y H:i', strtotime($role->created_at)) }} </td>
                                            <td>{{ date('d/m/y H:i', strtotime($role->updated_at)) }} </td>
                                            <td>
                                                @if (Sentinel::hasAccess('roles.add|edit'))
                                                    <a class="btn btn-success btn-flat btn-row" title="Editar"
                                                        href="{{ route('roles.edit', ['role' => $role->id]) }}"><i
                                                            class="fa fa-pencil"></i></a>
                                                @endif
                                                @if (Sentinel::hasAccess('roles.delete'))
                                                    <a class="btn-delete btn btn-danger btn-flat btn-row" title="Eliminar"
                                                        href="#"><i class="fa fa-remove"></i></a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            No se encuentran roles
                        @endif
                    </div>
                </div>
            </div>
            <div class="box-footer clearfix">
                <div class="row">
                    <div class="col-sm-5">
                        <div class="dataTables_info" role="status" aria-live="polite">{{ $roles->total() }} registros en
                            total
                        </div>
                    </div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers">
                            {!! $roles->appends(Request::only(['name']))->render() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    {!! Form::open(['route' => ['roles.destroy', ':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}

    {!! Form::close() !!}

@endsection
@section('page_scripts')
    @include('partials._delete_row_js')
@endsection

@section('js')
    <script></script>
@endsection
