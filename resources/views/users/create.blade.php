@extends('app')

@section('title')
    Usuarios
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Usuarios
            <small>Nuevo Usuario</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Sucursal</a></li>
            <li class="active">agregar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Nuevo Usuario</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._messages')
                        {!! Form::open(['route' => ['users.store'], 'method' => 'POST', 'role' => 'form']) !!}

                        <div class="panel with-nav-tabs">
                            <div class="panel-heading">
                                <ul class="nav nav-tabs">
                                    <li class="active"><a href="#tab_help_0" data-toggle="tab">
                                            Datos de usuario </a>
                                    </li>
                                    <li><a href="#tab_help_1" data-toggle="tab">
                                            Permisos </a>
                                    </li>
                                </ul>
                            </div>
                            <div class="panel-body">
                                <div class="tab-content">
                                    <div class="tab-pane fade in active" id="tab_help_0">
                                        @include('users.partials.fields')
                                    </div>
                                    <div class="tab-pane fade" id="tab_help_1">
                                        @include('users.partials.permissions')
                                    </div>
                                </div>
                            </div>
                        </div>

                        <a class="btn btn-default" href="{{ route('users.index') }}" role="button">Cancelar</a>
                        {!! Form::submit('Enviar', ['class' => 'btn btn-primary']) !!}

                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>

    </section>
@endsection
