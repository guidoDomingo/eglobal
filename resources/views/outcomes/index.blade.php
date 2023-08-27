@extends('layout')
@section('title')
    Tipos de Egresos
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Entidades Externas
            <small>Listado de Servicios</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Entidades Externas</a></li>
            <li class="active">Lista</li>
        </ol>
    </section>
    <section class="content">
        @include('partials._flashes')
        <div class="box">

            <div class="box-header">
                <h3 class="box-title">
                </h3>
                <a href="{{ route('outcome.create') }}" class="btn-sm btn-primary active" role="button">Agregar</a>
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
                                <th>Nombre</th>
                                <th>Proveedor</th>
                                <th style="width:300px">Creado</th>
                                <th style="width:300px">Modificado</th>
                                <th style="width:250px">Acciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($outcomes as $outcome)
                                <tr data-id="{{ $outcome->id  }}">
                                    <td>{{ $outcome->id }}.</td>
                                    <td>{{ $outcome->description }}</td>
                                    <td>{{ $outcome->provider->business_name }}</td>
                                    <td>{{ date('d/m/y H:i', strtotime($outcome->created_at)) }} -
                                        <i>{{ $outcome->createdBy->username }}</i></td>
                                    <td>{{ date('d/m/y H:i', strtotime($outcome->updated_at)) }} @if($outcome->updatedBy != null)
                                            - <i>{{ $outcome->createdBy->username }}</i> @endif </td>
                                    <td>
                                        <a href="{{ route('outcome.edit',$outcome)}}"><i
                                                    class="fa fa-edit"></i> Editar</a> |
                                        <a href="#" class="btn-delete"><i class="fa fa-remove"></i> Eliminar</a>
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
                        {{--<div class="dataTables_info" role="status" aria-live="polite">{{ $outcomes->total() }}--}}
                            {{--registros en total--}}
                        {{--</div>--}}
                    </div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers">
                            {!! $outcomes->render() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </section>

    {!! Form::open(['route' => ['outcome.destroy',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
    {!! Form::close() !!}


@endsection
@section('page_scripts')
    @include('partials._delete_row_js')
@endsection
