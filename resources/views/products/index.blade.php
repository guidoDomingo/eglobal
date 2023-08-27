@extends('layout')
@section('title')
    Productos
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Productos
            <small>Listado</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Productos</a></li>
            <li class="active">Lista</li>
        </ol>
    </section>
    <section class="content">
        @include('partials._flashes')
        <div class="box">

            <div class="box-header">
                <h3 class="box-title">
                </h3>
                <a href="{{ route('products.create') }}" class="btn-sm btn-primary active"
                   role="button">Agregar</a>
                <div class="box-tools">
                    <div class="input-group" style="width:150px;">

                    </div>
                </div>
            </div>
            <div class="box-body  no-padding">
                <div class="row">
                    <div class="col-xs-12">
                        <table class="table table-striped">
                            <tbody>
                            <thead>
                            <tr>
                                <th style="width:10px">#</th>
                                <th>Descripción</th>
                                <th>Proveedor</th>
                                <th>Costo</th>
                                <th style="width:200px">Creado</th>
                                <th style="width:200px">Modificado</th>
                                <th style="width:300px">Acciones</th>
                                <th style="width:100px">Precio</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($products as $product)
                                <tr data-id="{{ $product->id  }}">
                                    <td>{{ $product->id }}.</td>
                                    <td>{{ $product->description }}</td>
                                    <td>{{ $product->provider->business_name }}</td>
                                    <td>{{ $product->currency . number_format($product->cost, '0', ',', '.')}}</td>

                                    <td>{{ date('d/m/y H:i', strtotime($product->created_at)) }} -
                                        <i>{{ $product->createdBy->username }}</i></td>
                                    <td>{{ date('d/m/y H:i', strtotime($product->updated_at)) }} @if($product->updatedBy != null)
                                            - <i>{{ $product->createdBy->username }}</i> @endif </td>
                                    <td>
                                        <a class="btn btn-info btn-flat btn-row" title="Ver" href="{{ route('products.show',$product) }}"><i class="fa fa-search"></i></a>
                                        <a class="btn btn-success btn-flat btn-row" title="Editar" href="{{ route('products.edit',$product)}}"><i class="fa fa-pencil"></i></a>
                                        <a class="btn-delete btn btn-danger btn-flat btn-row" title="Eliminar" href="#" ><i class="fa fa-remove"></i> </a>
                                    </td>
                                    <td>
                                        {{ $product->currency . ' '. number_format($product->sell_price, '0', ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="box-footer clearfix">
                <div class="row">
                    <div class="col-sm-5">
                        {{--<div class="dataTables_info" role="status" aria-live="polite">{{ $products->total() }} registros--}}
                        {{--en total--}}
                    </div>
                </div>
                <div class="col-sm-7">
                    <div class="dataTables_paginate paging_simple_numbers">
                        {!! $products->render() !!}
                    </div>
                </div>
            </div>
        </div>
    </section>

    {!! Form::open(['route' => ['products.destroy',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
    {!! Form::close() !!}


@endsection
@section('page_scripts')
    @include('partials._delete_row_js')
@endsection
