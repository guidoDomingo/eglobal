@extends('layout')

@section('title')
    Punto de Venta {{ $pointofsale->description }}
@endsection
@section('content')
    <section class="content-header">
        <h1>
            {{ $pointofsale->description }}
            <small>Modificación de datos de Punto de Venta</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="{{ route('pos.index') }}">Puntos de Ventas</a></li>
            <li><a href="#">{{ $pointofsale->description }}</a></li>
            <li class="active">Modificar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            @if(!\Sentinel::getUser()->hasAccess('atm.assign'))
                <div class="col-md-6">
                    @else
                        <div class="col-md-12">
                            @endif
                            <div class="box box-primary">
                                <div class="box-header with-border">
                                    <h3 class="box-title">Modificar {{ $pointofsale->description }}</h3>
                                </div>
                                <div class="box-body">
                                    @include('partials._flashes')
                                    @include('partials._messages')
                                    {!! Form::model($pointofsale, ['route' => ['pos.update', $pointofsale->id ] , 'method' => 'PUT']) !!}
                                    @include('pos.partials.fields')

                                </div>
                                <div class="box-footer">
                                    <a class="btn btn-default" href="{{ route('pos.index') }}"
                                       role="button">Cancelar</a>
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
    {{--@include('partials._delete_form_js')--}}
@endsection
