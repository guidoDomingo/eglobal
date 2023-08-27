@extends('layout')
@section('title')
    Modelos - Parametros de Servicios
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Modelos
            <small>Parametros de servicios</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Modelos</a></li>
            <li class="active">lista</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <h2>Vistas</h2>
                <div class="box">
                    <div class="box-header">
                       {!! Form::open(array('route' => ['wsproducts.models.store', 'wsproduct' => $service_id], 'method' => 'POST')) !!}
                        <div class="form-group">
                            {!! Form::label('description', 'Nombre') !!}
                            {!! Form::text('key', null , ['class' => 'form-control', 'placeholder' => 'Nombre', 'required' ]) !!}
                        </div>
                        <div class="form-group">
                            {!! Form::label('valor', 'Valor') !!}
                            {!! Form::text('value', null , ['class' => 'form-control', 'placeholder' => 'Valor' ]) !!}
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
                                        <th>Nombre</th>
                                        <th>Valor</th>
                                        <th>Acciones</th>
                                    </tr>
                                    </thead>
                                    @foreach($servicesmodels as $servicesmodel)
                                        <tr data-id="{{ $servicesmodel->service_id  }}-{{ $servicesmodel->key  }}">
                                            <td>{{ $servicesmodel->service_id }}.</td>
                                            <td>{{ $servicesmodel->key }}</td>
                                            <td>{{ $servicesmodel->value }}</td>
                                            <td>
                                                <a class="btn btn-danger btn-flat btn-row btn-delete" href="#" title="Eliminar" class="btn-delete"> <i class="fa fa-remove"></i> </a>
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
    {!! Form::open(['route' => ['wsproducts.models.delete',':ROW_ID'], 'id' => 'form-delete']) !!}
    {!! Form::close() !!}
@endsection
@section('page_scripts')
    @include('partials._delete_row_js')
@endsection
