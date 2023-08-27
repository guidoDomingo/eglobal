@extends('layout')
@section('title')
    Vistas - Flujo de pantallas de servicio
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Flujo del servicio
            <small>Listado de objetos</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Flujo</a></li>
            <li class="active">Pantallas</li>
            <li class="active">Objetos</li>
        </ol>
    </section>

    <section class="content">
        <div class="row">
            <div class="col-md-12">

                <div class="box">

                    <div class="box-header">
                        <h3 class="box-title">
                        </h3>
                        <a href="{{ route('wsproducts.wsbuilder.views.create',[$wsproduct_id, $wsscreen_id]) }}" class="btn-sm btn-primary active" role="button">Agregar</a>

                    </div>

                    <div class="box-body  no-padding">
                        <div class="row">

                            <div class="col-xs-12">
                                <table class="table table-striped">
                                    <tbody><thead>
                                    <tr>
                                        <th style="width:10px">#</th>
                                        <th>Objeto</th>
                                        <th>valor</th>
                                        <th>Acciones</th>
                                    </tr>
                                    </thead>
                                    @foreach($objects_controls as $objects_control)
                                        <tr data-id="{{ $objects_control->id  }}">
                                            <td>{{ $objects_control->id }}</td>
                                            <td>{{ $objects_control->name }}</td>
                                            <td>{{ $objects_control->value }}</td>
                                            <td>
                                                <a class="btn btn-success btn-flat btn-row" title="Editar" href="{{ route('wsproducts.wsbuilder.views.edit',[$wsproduct_id, $wsscreen_id,$objects_control->id]) }}"><i class="fa fa-pencil"></i></a>
                                                <a class="btn btn-danger btn-flat btn-row btn-delete" href="#" title="Eliminar"><i class="fa fa-remove"></i> </a>
                                            </td>
                                        </tr>
                                        @endforeach
                                        </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@section('page_scripts')
    @include('partials._delete_row_js')
@endsection
