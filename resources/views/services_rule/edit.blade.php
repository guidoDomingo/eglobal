@extends('layout')

@section('title')
        Reglas de Servicios  {{ $servicio->description  }}
@endsection
@section('content')
    <section class="content-header">
        <h1>
            {{-- {{ $owner->name }} --}}
            <small>Modificación de datos de la Regla de Servicios</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="{{ route('services_rules.index') }}">Reglas</a></li>
            <li class="active">Modificar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                         <h3 class="box-title">Modificar: {{ $servicio->description  }}   </h3> 
                    </div>
                    <div class="box-body">
                        @include('partials._flashes')
                        @include('partials._messages')
                        {!! Form::model($servicio, ['route' => ['services_rules.update', $servicio->idservice_rule ] , 'method' => 'PUT']) !!}
                        @include('services_rule.partials.fields')

                        <a class="btn btn-default" href="{{ route('services_rules.index') }}" role="button">Cancelar</a>
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
