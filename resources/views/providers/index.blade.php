@extends('layout')
@section('title')
    Proveedores
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Proveedores
            <small>Listado</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Proveedores</a></li>
            <li class="active">Lista</li>
        </ol>
    </section>
    <section class="content">
        @include('partials._flashes')
        <div class="box">

            <div class="box-header">
                <h3 class="box-title">
                </h3>
                <a href="{{ route('providers.create') }}" class="btn-sm btn-primary active"
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
                                <th>Razón Social</th>
                                <th>RUC</th>
                                <th style="width:200px">Creado</th>
                                <th style="width:200px">Modificado</th>
                                <th style="width:250px">Acciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($providers as $provider)
                                <tr data-id="{{ $provider->id  }}">
                                    <td>{{ $provider->id }}.</td>
                                    <td>{{ $provider->business_name }}</td>
                                    <td>{{ $provider->ruc }}</td>
                                    <td>{{ date('d/m/y H:i', strtotime($provider->created_at)) }} -
                                        <i>{{ $provider->createdBy->username }}</i></td>
                                    <td>{{ date('d/m/y H:i', strtotime($provider->updated_at)) }} @if($provider->updatedBy != null)
                                            - <i>{{ $provider->createdBy->username }}</i> @endif </td>
                                    <td>
                                        <a class="btn btn-success btn-flat btn-row" title="Editar" href="{{ route('providers.edit',$provider)}}"><i
                                                    class="fa fa-pencil"></i> </a>
                                        <a class="btn-delete btn btn-danger btn-flat btn-row" title="Eliminar" href="#" ><i class="fa fa-remove"></i> </a>
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
                        {{--<div class="dataTables_info" role="status" aria-live="polite">{{ $providers->total() }}--}}
                            {{--registros en total--}}
                        {{--</div>--}}
                    </div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers">
                            {!! $providers->render() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </section>

    {!! Form::open(['route' => ['providers.destroy',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
    {!! Form::close() !!}


@endsection
@section('page_scripts')
    @include('partials._delete_row_js')
@endsection
