@extends('layout')

@section('title')
    {{ $screen->name }} - Pantalla
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Pantalla
            <small>Modificación de Pantalla</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="{{ route('applications.index') }}">Aplicaciones</a></li>
            <li><a href="{{ route('screens.index') }}">Pantallas</a></li>
            <li><a href="#">{{ $screen->name }}</a></li>
            <li class="active">Modificar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Modificar {{ $screen->name }}</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._flashes')
                        @include('partials._messages')
                        {!! Form::model($screen, ['route' => ['screens.update', $screen->id ] , 'method' => 'PUT']) !!}
                        @include('screens.partials.fields')
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        {!! Form::close() !!}
                    </div>
                </div>
                <div class="box-footer">
{{--                    @include('screens.partials.delete')--}}
                </div>
            </div>
        </div>
    </section>
@endsection
@section('page_scripts')
{{--    @include('partials._delete_form_js')--}}
@endsection
