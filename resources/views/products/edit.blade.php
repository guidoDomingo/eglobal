@extends('layout')

@section('title')
    Producto {{ $product->description }}
@endsection
@section('content')
    <section class="content-header">
        <h1>
            {{ $product->description }}
            <small>Modificación de datos de Producto</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="{{ route('products.index') }}">Puntos de Ventas</a></li>
            <li><a href="#">{{ $product->description }}</a></li>
            <li class="active">modificar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            {{--            @if(\Sentinel::getUser()->hasAccess('wsproducts.asigntoproduct'))--}}
            {{--<div class="col-md-6">--}}
            {{--@else--}}
            <div class="col-md-12">
                @include('partials._flashes')
                {{--@endif--}}
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Modificar {{ $product->description }}</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._flashes')
                        @include('partials._messages')
                        {!! Form::model($product, ['route' => ['products.update', $product->id ] , 'method' => 'PUT']) !!}
                        @include('products.partials.fields')

                    </div>
                    <div class="box-footer">
                        <a class="btn btn-default" href="{{ route('products.index') }}" role="button">Cancelar</a>
                        <button type="submit" class="btn btn-primary pull-right">Guardar</button>

                    </div>
                </div>
                {!! Form::close() !!}
            </div>
            {{--@if(\Sentinel::getUser()->hasAccess('wsproducts.asigntoproduct'))--}}
            {{--<div class="col-md-6">--}}
            {{--@include('admin.products.partials.wsproduct.form')--}}
            {{--</div>--}}
            {{--@endif--}}
        </div>
    </section>
@endsection
@section('page_scripts')
    {{--@include('partials._delete_form_js')--}}
@endsection

