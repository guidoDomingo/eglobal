@extends('app')
@section('title')
    Grupos
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Grupos
            <small>Listado</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Grupos</a></li>
            <li class="active">Lista</li>
        </ol>
    </section>
    <section class="content">
        @include('partials._flashes')
        <div class="box">

            <div class="box-header">
                <h3 class="box-title">
                </h3>
                <a href="{{ route('groups.create') }}" class="btn-sm btn-primary active" role="button">Agregar</a>
                <div class="box-tools">
                    <div class="input-group" style="width:150px;">
                        {!! Form::model(Request::only(['name']),['route' => 'groups.index', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
                        {!! Form::text('description' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Nombre', 'autocomplete' => 'off' ]) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
            <div class="box-body  no-padding">
                <div class="row">
                    <div class="col-xs-12">
                        @if ($groups)
                            <table class="table table-striped">
                                <tbody>
                                <thead>
                                <tr>
                                    <th style="width:10px">#</th>
                                    <th>Nombre</th>
                                    <th>Ruc</th>
                                    <th style="width:300px">Creado</th>
                                    <th style="width:300px">Modificado</th>
                                    <th>Acciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($groups as $group)
                                    <tr data-id="{{ $group->id  }}">
                                        <td>{{ $group->id }}.</td>
                                        <td>{{ $group->description }}</td>
                                        <td>{{ $group->ruc }}</td>
                                        <td>{{ date('d/m/y H:i', strtotime($group->created_at)) }}
                                            @if($group->created_by != null)
                                                - <i>{{ $group->createdBy->username }}</i></td>
                                            @endif
                                        <td>{{ date('d/m/y H:i', strtotime($group->updated_at)) }}
                                            @if($group->updated_by != null)
                                                - <i>{{ $group->updatedBy->username }}</i></td>
                                            @endif
                                        <td>
                                            @if (Sentinel::hasAccess('branches'))
                                                <a class="btn btn-info btn-flat btn-row" title="Sucursales" href="{{ route('groups.branches',['groupId' => $group->id ]) }}"><i class="fa fa-building"></i></a>
                                            @endif
                                            @if (Sentinel::hasAccess('group.add|edit'))
                                            <a class="btn btn-success btn-flat btn-row" title="Editar" href="{{ route('groups.edit',['group' => $group->id])}}"><i class="fa fa-pencil"></i></a>
                                            @endif
                                            @if (Sentinel::hasAccess('group.delete'))
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
                        <div class="dataTables_info" role="status" aria-live="polite">{{ $groups->total() }} registros en total
                        </div>
                    </div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers">
                            {!! $groups->appends(Request::only(['description']))->render() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    {!! Form::open(['route' => ['groups.destroy',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
    {!! Form::close() !!}

@endsection
@section('page_scripts')
    @include('partials._delete_row_js')
@endsection
