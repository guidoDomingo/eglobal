@extends('layout')
@section('title')
    ATMS - Credenciales por cajero
@endsection
@section('content')
    <section class="content-header">
        <h1>
            ATMs
            <small>Credenciales de ATMS</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="/"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="{{ route('atm.index') }}">Atms</a></li>
            <li class="active">Credenciales</li>
        </ol>
    </section>
    <section class="content">
        @include('partials._flashes')
        <div class="box">

            <div class="box-header">
                <h3 class="box-title">
                </h3>
                <a href="{{ route('atm.credentials.create', $atm_id) }}" class="btn-sm btn-primary active" role="button">Agregar</a>
            </div>
            <div class="box-body  no-padding">
                <div class="row">
                    <div class="col-md-8">
                        <table class="table table-striped">
                            <tbody>
                            <thead>
                            <tr>
                                <th style="width:10px">#</th>
                                <th>Servicio</th>
                                <th style="width:150px">Creado</th>
                                <th style="width:150px">Modificado</th>
                                <th style="width:200px">Acciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($credentials as $credential)
                                <tr data-id="{{ $credential->id  }}">
                                    <td>{{ $credential->id }}.</td>
                                    <td>{{ $credential->name }}</td>
                                    <td>{{ $credential->created_at }}</td>
                                    <td>{{ $credential->updated_at }}</td>
                                    <td>
                                        @if (Sentinel::hasAccess('atms.add|edit'))
                                        <a class="btn btn-success btn-flat btn-row" title="Editar" href="{{ route('atm.credentials.edit',['atm' => $credential->atm_id, 'credential' =>$credential->id])}}"><i class="fa fa-pencil"></i></a>

                                        @endif
                                        @if (Sentinel::hasAccess('atms.delete'))
                                        <a class="btn-delete btn-delete btn btn-danger btn-flat btn-row" title="Eliminar" href="#" ><i class="fa fa-remove"></i> </a>
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
                        <div class="dataTables_info" role="status" aria-live="polite">{{ $credentials->total() }} registros en total</div>
                    </div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers">
                            {!! $credentials->render() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        </div>
    </section>

    {!! Form::open(['route' => ['atm.credentials.destroy',$atm_id,':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
    {!! Form::close() !!}
@endsection
@section('page_scripts')
    @include('partials._delete_row_js')
@endsection
