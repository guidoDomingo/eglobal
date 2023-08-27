@extends('layout')

@section('title')
    Nuevo Punto de Venta
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Punto de Venta
            <small>Creación de Punto de Venta</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Punto de Venta</a></li>
            <li class="active">Agregar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Nuevo Punto de Venta</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._messages')
                        {!! Form::open(['route' => 'pos.store' , 'method' => 'POST', 'role' => 'form']) !!}
                        @include('pos.partials.fields')
                    </div>
                    <div class="box-footer">
                        <a class="btn btn-default" href="{{ route('pos.index') }}" role="button">Cancelar</a>
                        <button type="submit" class="btn btn-primary pull-right">Guardar</button>
                    </div>
                    {!! Form::close() !!}
                </div>

            </div>
        </div>
    </section>
@endsection
