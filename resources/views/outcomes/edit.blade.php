@extends('layout')

@section('title')
    Servicio Externo {{ $outcome->description }}
@endsection
@section('content')
    <section class="content-header">
        <h1>
            {{ $outcome->description }}
            <small>Modificación de datos del Servicio Externo</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="{{ route('outcome.index') }}">Puntos de Ventas</a></li>
            <li><a href="#">{{ $outcome->description }}</a></li>
            <li class="active">Modificar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Modificar {{ $outcome->description }}</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._flashes')
                        @include('partials._messages')
                        {!! Form::model($outcome, ['route' => ['outcome.update', $outcome->id ] , 'method' => 'PUT']) !!}
                        @include('outcomes.partials.fields')

                    </div>
                    <div class="box-footer">
                        <a class="btn btn-default" href="{{ route('outcome.index') }}" role="button">Cancelar</a>
                        <button type="submit" class="btn btn-primary pull-right">Guardar</button>

                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
    </section>
@endsection
@section('page_scripts')
{{--    @include('partials._delete_form_js')--}}
@endsection
