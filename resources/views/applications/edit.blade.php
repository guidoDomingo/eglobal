@extends('layout')

@section('title')
    {{ $application->name }} - Aplicación
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Aplicación
            <small>Modificación de Aplicación</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="{{ route('applications.index') }}">Aplicaciones</a></li>
            <li><a href="#">{{ $application->name }}</a></li>
            <li class="active">Modificar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Modificar {{ $application->name }}</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._flashes')
                        @include('partials._messages')
                        {!! Form::model($application, ['route' => ['applications.update', $application->id ] , 'method' => 'PUT']) !!}
                        @include('applications.partials.fields')
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        {!! Form::close() !!}
                    </div>
                </div>
                <div class="box-footer">
                    {{--@include('applications.partials.delete')--}}
                </div>
            </div>
            <div class="col-md-6">
                @include('applications.partials.atm_form')
                @include('applications.partials.atm_list')
            </div>
        </div>
    </section>
@endsection
@section('page_scripts')
    {{--@include('partials._delete_form_js')--}}
@endsection
