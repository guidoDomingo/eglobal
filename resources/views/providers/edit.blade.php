@extends('layout')

@section('title')
    Proveedor {{ $provider->business_name }}
@endsection
@section('content')
    <section class="content-header">
        <h1>
            {{ $provider->business_name }}
            <small>Modificación de datos de Proveedor</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="{{ route('providers.index') }}">Puntos de Ventas</a></li>
            <li><a href="#">{{ $provider->business_name }}</a></li>
            <li class="active">Modificar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Modificar {{ $provider->business_name }}</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._flashes')
                        @include('partials._messages')
                        {!! Form::model($provider, ['route' => ['providers.update', $provider->id ] , 'method' => 'PUT']) !!}
                        @include('providers.partials.fields')

                    </div>
                    <div class="box-footer">
                        <a class="btn btn-default" href="{{ route('providers.index') }}" role="button">Cancelar</a>
                        <button type="submit" class="btn btn-primary pull-right">Guardar</button>

                    </div>
                </div>
                {!! Form::close() !!}
            </div>
        </div>
        </div>
    </section>
@endsection
@section('page_scripts')
{{--    @include('partials._delete_form_js')--}}
@endsection
