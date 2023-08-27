@extends('layout')
@section('title')
    Servicios
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Servicios
            <small>Listado</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Servicios</a></li>
            <li class="active">Lista</li>
        </ol>
    </section>
    <section class="content">
        @include('partials._flashes')
        <div class="box">

            <div class="box-header">
                <h3 class="box-title">
                </h3>
                <a href="{{ route('services_rules.create') }}" class="btn-sm btn-primary active" role="button">Agregar</a>
                <div class="box-tools">
                    <div class="input-group" style="width:150px;">
                        {!! Form::model(Request::only(['name']),['route' => 'services_rules.index', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
                        {!! Form::text('name' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'Descripción', 'autocomplete' => 'off' ]) !!}
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
            <div class="box-body  no-padding">
                <div class="row">
                    <div class="col-xs-12">
                        @if ($servicesRules)
                            <table class="table table-striped">
                                <tbody>
                                <thead>
                                <tr>
                                    <th style="width:10px">#</th>
                                    <th style="width:300px">Descripción</th>
                                    <th style="width:300px">Message</th>
                                    <th>Red</th>
                                    <th>Servicio</th>
                                    <th>Acciones</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($servicesRules as $serviceRule)
                                    <tr data-id="{{ $serviceRule->idservice_rule  }}">
                                        <td>{{ $serviceRule->idservice_rule }}.</td>
                                        <td>{{ $serviceRule->description }}</td>
                                        <td>{{ $serviceRule->message_user }}</td>
                                        
                                        
                                        @if ($serviceRule->owner_id == null)
                                            <td> - </td>
                                        @else
                                            <td>{{ $serviceRule->owner['name'] }}</td>
                                        @endif

                                        @if ($serviceRule->service_id == null && $serviceRule->service_source_id == null)
                                            <td> - </td>                                       
                                        @else
                                            @foreach ($servicios as $servicio )
                                                @if ($serviceRule->service_id == $servicio->service_id && $serviceRule->service_source_id == $servicio->service_source_id)
                                                    <td>{{ $servicio->description }} - {{ $servicio->descripcion }}</td>
                                                @endif
                                            @endforeach
                                        @endif
                                        
                                        <td>
                                            @if (Sentinel::hasAccess('services_rules.add|edit'))
                                            <a class="btn btn-success btn-flat btn-row" title="Editar" href="{{ route('services_rules.edit',['services_rule' => $serviceRule->idservice_rule])}}"><i class="fa fa-pencil"></i></a>
                                            @endif
                                            @if (Sentinel::hasAccess('services_rules.delete'))
                                            <a class="btn-delete btn btn-danger btn-flat btn-row" title="Eliminar" href="{{ route('services_rules.destroy',['services_rule' => $serviceRule->idservice_rule])}}" ><i class="fa fa-remove"></i> </a>
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
                        <div class="dataTables_info" role="status" aria-live="polite">{{ $servicesRules->total() }} registros en total
                        </div>
                    </div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers">
                            {!! $servicesRules->appends(Request::only(['description']))->render() !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    {!! Form::open(['route' => ['services_rules.destroy',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
    {!! Form::close() !!}

@endsection
@section('page_scripts')
    @include('partials._delete_row_js')
@endsection
