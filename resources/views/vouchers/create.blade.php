@extends('layout')

@section('title')
    Nuevo Tipo de Comprobante
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Tipo de Comprobante
            <small>Creación de Tipo de Comprobante</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Tipo de Comprobante</a></li>
            <li class="active">Agregar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Nuevo Tipo de Comprobante</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._messages')
                        {!! Form::open(['route' => 'vouchers.store' , 'method' => 'POST', 'role' => 'form']) !!}
                        @include('vouchers.partials.fields')
                    </div>
                    <div class="box-footer">
                        <a class="btn btn-default" href="{{ route('vouchers.index') }}" role="button">Cancelar</a>
                        <button type="submit" class="btn btn-primary pull-right">Guardar</button>
                    </div>
                    {!! Form::close() !!}
                </div>

            </div>
        </div>
    </section>
@endsection
