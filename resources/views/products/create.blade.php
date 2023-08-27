@extends('layout')

@section('title')
    Nuevo Producto
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Producto
            <small>Creación de Producto</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Producto</a></li>
            <li class="active">Agregar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                @include('partials._flashes')
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Nuevo Producto</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._messages')
                        {!! Form::open(['route' => 'products.store' , 'method' => 'POST', 'role' => 'form']) !!}
                        @include('products.partials.fields')
                    </div>
                    <div class="box-footer">
                        <a class="btn btn-default" href="{{ route('products.index') }}" role="button">Cancelar</a>
                        <button type="submit" class="btn btn-primary pull-right">Guardar</button>
                    </div>
                    {!! Form::close() !!}
                </div>

            </div>
        </div>
    </section>
@endsection
