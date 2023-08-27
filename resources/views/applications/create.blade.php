@extends('layout')

@section('title')
    Nueva Aplicacción
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Aplicación
            <small>Creación de Aplicación</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Aplicaciones</a></li>
            <li class="active">Agregar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @include('partials._flashes')
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Nueva Aplicación</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._messages')
                        {!! Form::open(['route' => 'applications.store' , 'method' => 'POST']) !!}
                        <div class="form-group">
                            {!! Form::label('template', 'Usar plantilla base?') !!}
                            {!! Form::select('from_app_id',$applications , $applications , ['class' => 'form-control chosen-select','placeholder' => 'NO']) !!}
                        </div>
                        @include('applications.partials.fields')
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        {!! Form::close() !!}
                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection
