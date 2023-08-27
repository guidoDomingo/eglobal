@extends('layout')
@section('title')
    Vistas - Flujo de pantallas de servicio
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Flujo del servicio
            <small>Listado de pantallas</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Flujo</a></li>
            <li class="active">lista</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <h2>Vistas</h2>
                <div class="box">
                    <div class="box-header">
                        {!! Form::open(array('route' => ['wsproducts.wsbuilder.store', 'wsproduct' => $wsproduct_id], 'method' => 'POST')) !!}
                        <div class="form-group">
                            {!! Form::label('description', 'Descripción') !!}
                            {!! Form::text('description', null , ['class' => 'form-control', 'placeholder' => 'Elemento para mostrar ayuda en pantalla' ]) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::label('screens', 'Pantalla') !!}
                            {!! Form::select('screen_id',$screens , null , ['class' => 'form-control chosen-select','placeholder' => 'Seleccione una pantalla']) !!}
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        {!! Form::close() !!}
                    </div>
                    <div class="box-body  no-padding">
                        <div class="row">
                            <div class="col-xs-12">
                                <table class="table table-striped">
                                    <tbody><thead>
                                    <tr>
                                        <th style="width:10px">#</th>
                                        <th>Descripción</th>
                                        <th>Id Pantalla</th>
                                        <th>Acciones</th>
                                    </tr>
                                    </thead>
                                    @foreach($servicesviews as $servicesview)
                                        <tr data-id="{{ $servicesview->id  }}">
                                            <td>{{ $servicesview->id }}.</td>
                                            <td>{{ $servicesview->description }}</td>
                                            <td>{{ $servicesview->name }}</td>
                                            <td>
                                                <a class="btn btn-info btn-flat btn-row" href="{{ route('wsproducts.wsbuilder.views.index',[$wsproduct_id,$servicesview->id]) }}" title="Flujo"><i class="fa fa-desktop"></i></a>
                                                <a class="btn btn-danger btn-flat btn-row btn-delete" href="#" title="Eliminar" ><i class="fa fa-remove"></i> </a>
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
