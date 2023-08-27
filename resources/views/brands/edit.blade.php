@extends('layout')

@section('title')
        Marca  {{ $brand->description  }}
@endsection
@section('content')
    <section class="content-header">
        <h1>
            {{-- {{ $owner->name }} --}}
            <small>Modificación de datos de la Marca</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Marca</a></li>
            {{-- <li><a href="#">{{ $owner->name }}</a></li> --}}
            <li class="active">Modificar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                         <h3 class="box-title">Modificar:    {{ $brand->description }}</h3> 
                    </div>
                    <div class="box-body">
                        @include('partials._flashes')
                        @include('partials._messages')
                        {!! Form::model($brand, ['route' => ['brands.update', $brand->id ] , 'method' => 'PUT']) !!}
                        @include('brands.partials.fields')

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
