@extends('layout')

@section('title')
    Editar Credenciales para ATM
@endsection
@section('content')
    <section class="content-header">
        <h1>
            ATM
            <small>Modificación de ATM {{$atm->code}}</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="{{ route('atm.index') }}">Atms</a></li>
            <li><a href="{{ route('atm.credentials.index', $atm->id) }}">Credenciales</a></li>
            <li class="active">Modificar </li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Modificar Credenciales {{$credentials->name}}</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._flashes')
                        @include('partials._messages')
                        {!! Form::model($credentials, ['route' => ['atm.credentials.update', 'atm' => $atm->id, 'credential' => $credentials->id ] , 'method' => 'PUT']) !!}
                        @include('credentials.partials.fields')
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
