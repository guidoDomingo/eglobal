@extends('layout')

@section('title')
    Producto {{ $product->description }}
@endsection
@section('content')
    <section class="content-header">
        <h1>
            {{ $product->description }}
            <small>Datos de Producto</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="{{ route('products.index') }}">Puntos de Ventas</a></li>
            <li><a href="#">{{ $product->description }}</a></li>
            <li class="active">Ver</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Datos de Producto</h3>
                    </div>
                    <div class="box-body">
                        <div class="form-group">
                            <p><strong>Descripción: </strong>{{ $product->description }}</p>
                            <p><strong>Costo: </strong>{{ number_format($product->cost, 0, ',', '.') }}</p>
                            <p><strong>Precio de venta: </strong>{{ number_format($product->sell_price, 0, ',', '.') }}</p>
                            <p><strong>Proveedor: </strong>{{ $product->provider->business_name }}</p>
                            <p><strong>Tipo IVA: </strong>{{ $product->taxType->description }}</p>
                            <p><strong>Moneda: </strong>{{ $product->currency }}</p>
                            <p><strong>Codigo Ondanet: </strong>{{ $product->ondanet_code }}</p>
                            @if($product->createdBy != null)
                                <p><strong>Creado por: </strong>{{  $product->createdBy->username }}  el {{ date('d/m/y H:i', strtotime($product->created_at)) }}</p>
                            @endif
                            @if($product->updatedBy != null)
                                <p><strong>Modificado por: </strong>{{  $product->updatedBy->username }}  el {{ date('d/m/y H:i', strtotime($product->updated_at)) }}</p>
                            @endif
                        </div>
                        <div class="box-footer">
                            <a class="btn btn-default" href="{{ route('products.index') }}" role="button">Volver</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@section('page_scripts')
{{--    @include('partials._delete_form_js')--}}
@endsection
