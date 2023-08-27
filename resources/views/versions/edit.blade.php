@extends('layout')

@section('title')
    Versiones Editar - {{$application->name}}
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Versiones
            <small>Configurar servicios para {{$application->name}}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Versiones</a></li>
            <li class="active">agregar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{$application->name}}</h3>
                    </div>
                    <div class="box-body">
                        {!! Form::model($app_version, ['route' => ['applications.versions.update', $application->id, $version_id] , 'id' =>  'object_form','method' => 'PUT']) !!}
                        @include('partials._messages')
                        @include('versions.partials.fields')
                        <a class="btn btn-default" href="{{ URL::previous() }}" role="button">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        {!! Form::close() !!}

                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection