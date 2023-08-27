@extends('layout')

@section('title')
    Configuración de servicios
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Configurar servicio
            <small>Minicarga | Tigo</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Configuración de servicios </a></li>
            <li class="active">agregar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Parametros del servicio</h3>
                    </div>
                    <div class="box-body">
                        @include('admin.partials._messages')
                        <!-- aqui va el formulario -->
                            {!! Form::open(['route' => ['admin.wsproducts.wsbuilder.store', $wsproduct_id] , 'method' => 'POST', 'role' => 'form']) !!}
                                @include('admin.webservicesbuilder.partials.fields')
                                <button type="submit" class="btn btn-primary">Guardar</button>
                            {!! Form::close() !!}
                    </div>
                </div>

            </div>

            <div class="col-md-6">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Flujo de operaciones</h3>
                    </div>
                    <div class="box-body">
                        @include('admin.webservicesbuilder.partials.parameters_fields')
                        <a class="btn btn-default" href="{{ URL::previous() }}" role="button">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </div>
                </div>


                @include('admin.webservicesbuilder.partials.parameters_fields_lists')


            </div>
        </div>
    </section>
@endsection