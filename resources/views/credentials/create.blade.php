@extends('layout')

@section('title')
    Nuevas Credenciales para ATM
@endsection
@section('content')
    <section class="content-header">
        <h1>
            ATM
            <small>Asignación de credenciales</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="/"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="{{ route('atm.index') }}">Atms</a></li>
            <li><a href="{{ route('atm.credentials.index', $atm_id) }}"> Credenciales</a></li>
            <li class="active">Agregar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Nueva Credencial</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._messages')
                        {!! Form::open(['route' => ['atm.credentials.store', $atm_id], 'method' => 'POST', 'role' => 'form']) !!}
                        @include('credentials.partials.fields')
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
