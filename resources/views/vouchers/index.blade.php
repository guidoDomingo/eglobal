@extends('layout')
@section('title')
    Tipo de Comprobantes
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Tipos de Comprobantes
            <small>Listado</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Tipos de Comprobantes</a></li>
            <li class="active">Lista</li>
        </ol>
    </section>
    <section class="content">
        @include('partials._flashes')
        <div class="box">

            <div class="box-header">
                <h3 class="box-title">
                </h3>
                <a href="{{ route('vouchers.create') }}" class="btn-sm btn-primary active" role="button">Agregar</a>
                <div class="box-tools">
                    <div class="input-group" style="width:150px;">
                        {!! Form::model(Request::only(['name']),['route' => 'vouchers.index', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
                        {!! Form::text('name' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Nombre', 'autocomplete' => 'off' ]) !!}
                        {!! Form::close() !!}
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
                                <th>Nombre</th>
                                <th>Código</th>
                                <th style="width:300px">Creado</th>
                                <th style="width:300px">Modificado</th>
                                <th style="width:250px">Acciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($vouchertypes as $vouchertype)
                                <tr data-id="{{ $vouchertype->id  }}">
                                    <td>{{ $vouchertype->id }}.</td>
                                    <td>{{ $vouchertype->description }}</td>
                                    <td>{{ $vouchertype->voucher_type_code }}</td>
                                    <td>{{ date('d/m/y H:i', strtotime($vouchertype->created_at)) }}</td>
                                    <td>{{ date('d/m/y H:i', strtotime($vouchertype->updated_at)) }}</td>
                                    <td>
                                        @if (Sentinel::hasAnyAccess('vouchers.add|edit'))
                                        <a class="btn btn-success btn-flat btn-row" title="Editar" href="{{ route('vouchers.edit',$vouchertype)}}"><i class="fa fa-pencil"></i> </a>
                                        @endif
                                        @if (Sentinel::hasAnyAccess('vouchers.delete'))
                                        <a class="btn-delete btn btn-danger btn-flat btn-row" title="Eliminar" href="#" ><i class="fa fa-remove"></i> </a>
                                        @endif
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
                        <div class="dataTables_info" role="status" aria-live="polite">{{ $vouchertypes->total() }}
                        registros en total</div>
                    </div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers">
                            {!! $vouchertypes->appends(Request::only(['name']))->render() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </section>

    {!! Form::open(['route' => ['vouchers.destroy',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
    {!! Form::close() !!}


@endsection
@section('page_scripts')
    @include('partials._delete_row_js')
@endsection
