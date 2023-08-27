@extends('app')
@section('title')
    Usuarios
@endsection


@section('aditional_css')
       <!-- Bootstrap 3.3.4 -->
    <link rel="stylesheet" href="{{ URL::asset('/bower_components/admin-lte/bootstrap/css/bootstrap.min.css') }}">

@endsection

@section('content')
    <section class="content-header">
        <h1>
            Usuarios
            <small>Listado</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Usuarios</a></li>
            <li class="active">lista</li>
        </ol>
    </section>
    <section class="content">
        @include('partials._flashes')
        <div class="">
            <div class="box-header">
                <a href="{{ route('users.create') }}" class="btn btn-primary mb-2 me-4" role="button">Agregar</a>
                <div class="box-tools">
                    <div class="input-group" style="width:200px;">
                        {!! Form::model(Request::only(['name']),['route' => ['users.index'], 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
                        {!! Form::text('name' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Ingresar Nombre', 'autocomplete' => 'off' ]) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
            
                <div class="row layout-top-spacing">
                    <div class="col-xl-12 col-lg-12 col-sm-12  layout-spacing">
                        <div class="widget-content widget-content-area br-8">
                            <table id="zero-config" class="table table-striped dt-table-hover display responsive nowrap" style="width:100%">
                                <thead>
                                <tr>
                                    <th style="width:10px">N°</th>
                                    <th style="width:10px">#</th>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Rol</th>
                                    <th>Creado</th>
                                    <th>Modificado</th>
                                    <th data-width="16" class="hidden-xs hidden-sm">Estado</th>
                                    <th>Acciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @foreach($users as $key => $user)
                                        <tr data-id="{{ $user->id  }}" data-banned="{{ $user->isBanned()  }}">
                                            <td>{{ $key + 1 }}</td>
                                            <td>{{ $user->id }}</td>
                                            <td>{{ $user->description}}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>{{ explode(',', $user->roles->implode('name', ','))[0] }}</td>
                                            <td>{{ $user->created_at }}</td>
                                            <td>{{ $user->updated_at }}</td>
                                            <td style="text-align: center" class="hidden-xs hidden-sm">
                                                @if( ! $user->persistences->isEmpty())
                                                    <span><i class="fa fa-circle text-success"></i> Online</span>
                                                @else
                                                    <span><i class="fa fa-circle text-gray"></i> Offline</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if (Sentinel::hasAccess('users.add|edit'))
                                                    <a class="btn btn-info btn-flat btn-row" title="Ver"
                                                    href="{{ route('users.showProfile', ['id' => $user->id]) }}"><i class="fa fa-search"></i></a>
                                                    <a class="btn btn-success btn-flat btn-row" title="Editar"
                                                    href="{{ route('users.edit',$user->id)}}"><i
                                                                class="fa fa-pencil"></i></a>
                                                @endif

                                                @if(Sentinel::hasAccess('users.force_logout'))
                                                        {{-- Logout User --}}
                                                        <a class="btn btn-warning btn-flat btn-row" title="Forzar Cierre de Sesión"
                                                        href="{{ route('users.force.logout', ['id' => $user->id]) }}"><i
                                                                    class="fa fa-frown-o"></i></a>
                                                        @if($user->isBanned())
                                                            <a class=" btn-baneo btn btn-danger btn-flat btn-row" title="Desbloquear Usuario" href="#" ><i class="fa fa-ban fa-align-center"></i> </a>
                                                        @else
                                                            <a class=" btn-baneo btn btn-success btn-flat btn-row" title="Bloquear Usuario" href="#" ><i class="fa fa-check-circle-o fa-align-center"></i> </a>
                                                        @endif
                                                @endif

                                                @if (Sentinel::hasAccess('users.delete'))
                                                    <a class=" btn-delete btn btn-danger btn-flat btn-row" title="Eliminar" href="#" ><i class="fa fa-remove"></i> </a></td>
                                                @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            
            <div class="clearfix">
                <div class="row">
                    <div class="col-sm-5">
                        <div class="dataTables_info" role="status" aria-live="polite">{{ $users->total() }} registros en total</div>
                    </div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers">
                            {!! $users->appends(Request::only(['name']))->render() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    {!! Form::open(['route' => ['users.destroy',':ROW_ID'], 'method' => 'DELETE','id' => 'form-delete']) !!}
@endsection
@section('page_scripts')
    @include('partials._delete_row_js')
    @include('partials._user_baneo_js')
@endsection

@section('js')

@endsection
