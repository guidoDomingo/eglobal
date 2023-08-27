@extends('layout')

@section('title')
        Housing
@endsection
@section('content')
    <section class="content-header">
        <h1>
            <small>Modificación de datos del Housing</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Housing</a></li>
            <li class="active">Modificar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                         <h3 class="box-title">Modificar:    {{ $housing->serialnumber }}</h3> 
                    </div>
                    <div class="box-body">
                        @include('partials._flashes')
                        @include('partials._messages')
                        {!! Form::model($housing, ['route' => ['miniterminales.update', $housing->id ] , 'method' => 'PUT']) !!}
                        @include('device_housing.partials.fields')

                        <button type="submit" class="btn btn-primary">Guardar</button>
                        {!! Form::close() !!}
                    </div>
                </div>
                <div class="box-footer">
{{--                    @include('owners.partials.delete')--}}
                </div>
            </div>
        </div>
    </section>
@endsection
@section('page_scripts')
    {{--@include('partials._delete_form_js')--}}
@endsection
