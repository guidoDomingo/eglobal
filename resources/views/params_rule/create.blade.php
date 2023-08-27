@extends('layout')

@section('title')
    Nuevo Parámetro
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Parámetro
            <small>Creación de Reglas de parámetros</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="{{ route('params_rules.index') }}">Reglas de parámetros</a></li>
            <li class="active">agregar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Nuevo Parámetro</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._flashes')
                        @include('partials._messages')
                        {!! Form::open(['route' => 'params_rules.store' , 'method' => 'POST', 'role' => 'form']) !!}
                        @include('params_rule.partials.fields')
                        <a class="btn btn-default" href="{{ route('params_rules.index') }}" role="button">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
