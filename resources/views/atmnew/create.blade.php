@extends('layout')

@section('title')
    Nuevo ATM
@endsection
@section('content')
    <section class="content-header">
        <h1>
            ATM
            <small>Creación de ATM</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Atms</a></li>
            <li class="active">agregar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Nuevo ATM</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._messages')
                        {!! Form::open(['route' => 'atm.store' , 'method' => 'POST', 'role' => 'form']) !!}
                        @include('atm.partials.fields')
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        {!! Form::close() !!}
                    </div>
                </div>
                @include('atm.partials.generate_hash')

            </div>
        </div>
    </section>
@endsection
