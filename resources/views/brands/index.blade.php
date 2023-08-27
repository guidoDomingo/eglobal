@extends('app')
@section('title')
    Marcas
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Marcas
            <small>Listado</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Marcas</a></li>
            <li class="active">Lista</li>
        </ol>
    </section>
    <section class="content">
        @include('partials._flashes')
        <div class="box">
            <div class="box-header">
                <h3 class="box-title">
                </h3>
                <a href="{{ route('brands.create') }}" class="btn-sm btn-primary active" role="button">Agregar Marca</a>
                 <div class="box-tools">
                    <div class="input-group" style="width:150px;">
                        {!! Form::model(Request::only(['name']),['route' => 'brands.index', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
                        {!! Form::text('name' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Marca', 'autocomplete' => 'off' ]) !!}
                        {!! Form::close() !!}
                    </div>
                </div> 
            </div>
            <div class="box-body  no-padding">
                <div class="row">
                    <div class="col-xs-12">
                        @if ($brands)
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th style="width:30px">#ID</th>
                                        <th style="width:900px">Descripción</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($brands as $brand)
                                    <tr data-id="{{ $brand->id  }}">
                                        <td>{{ $brand->id }}.</td>
                                        <td>{{ $brand->description }}</td>
                                        <td>
                                            <a class="btn btn-info btn-flat btn-row" title="Modelos" href="{{ route('model.brand.index',['model' => $brand->id ]) }}"><i class="fa fa-building"></i></a>
                                            @if (Sentinel::hasAccess('brands.add|edit'))
                                                <a class="btn btn-success btn-flat btn-row" title="Editar" href="{{ route('brands.edit',['brand' => $brand->id])}}"><i class="fa fa-pencil"></i></a>
                                            @endif
                                            @if (Sentinel::hasAccess('brands.delete'))
                                                <a class="btn-delete btn btn-danger btn-flat btn-row" title="Eliminar" href="#" ><i class="fa fa-remove"></i> </a>
                                            @endif

                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @endif
                    </div>
                </div>
            </div>
            <div class="box-footer clearfix">
                <div class="row">
                    <div class="col-sm-5">
                        <div class="dataTables_info" role="status" aria-live="polite">{{ $brands->total() }} registros en total
                        </div>
                    </div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers">
                            {!! $brands->appends(Request::only(['name']))->render() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
   
    {!! Form::open(['route' => ['brands.destroy',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
    {!! Form::close() !!}
@endsection

@section('page_scripts')
    @include('partials._delete_row_js')
@endsection

