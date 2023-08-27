@extends('layout')

@section('title')
    Agregar objeto
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Agregar Objeto de pantalla
            <small>Flujo Nombre Servicio | Marca</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Configuración de servicios </a></li>
            <li class="active">Editar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Editar Objetos de pantallas</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._messages')
                        {!! Form::model($service_view,['route' => ['wsproducts.wsbuilder.views.update', $wsproduct_id, $wsscreen_id, $objects_control] , 'id' =>  'object_form','method' => 'PUT']) !!}
                        @include('webservicesbuilderviews.partials.fields')
                        <a class="btn btn-default" href="{{ URL::previous() }}" role="button">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </section>

@endsection