@extends('layout')

@section('title')
    {{ $atm->name }} ATM
@endsection
@section('content')
    <section class="content-header">
        <h1>
            ATM
            <small>Modificación de ATM</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="{{ route('atmnew.index') }}">Atms</a></li>
            <li><a href="#">{{ $atm->name }}</a></li>
            <li class="active">Modificar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Modificar {{ $atm->name }}</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._flashes')
                        @include('partials._messages')
                        {!! Form::model($atm, ['route' => ['atmnew.update', 'id' => $atm->id ] , 'method' => 'PUT']) !!}
                        @include('atmnew.partials.fields')
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        {!! Form::close() !!}
                    </div>
                </div>
                @include('atmnew.partials.generate_hash')

            </div>
        </div>
    </section>
@endsection
