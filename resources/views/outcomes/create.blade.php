@extends('layout')

@section('title')
    Nuevo Tipo de Egreso
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Entidades Externas
            <small>Nuevo Servicio</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Entidades Externas</a></li>
            <li class="active">Agregar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @include('partials._flashes')
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Nuevo Servicio Externo</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._messages')
                        {!! Form::open(['route' => 'outcome.store' , 'method' => 'POST', 'role' => 'form']) !!}
                        @include('outcomes.partials.fields')
                    </div>
                    <div class="box-footer">
                        <a class="btn btn-default" href="{{ route('outcome.index') }}" role="button">Cancelar</a>
                        <button type="submit" class="btn btn-primary pull-right">Guardar</button>
                    </div>
                    {!! Form::close() !!}
                </div>

            </div>
        </div>
    </section>
@endsection
