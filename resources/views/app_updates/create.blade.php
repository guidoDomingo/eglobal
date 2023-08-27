@extends('layout')
@section('title')
Nueva actualización
@endsection
@section('content')
<section class="content-header">
  <h1>
    Actualización
    <small>Publicar nueva</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
    <li><a href="#">Aplicaciones</a></li>
    <li class="active">Publicar</li>
  </ol>
</section>
<section class="content">
<div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Nueva Actualización</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._messages')
                        {!! Form::open(['route' => 'app_updates.store' , 'method' => 'POST', 'role' => 'form', 'files' => true]) !!}
                        @include('app_updates.partials.fields')
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        {!! Form::close() !!}
                    </div>
                </div>                
            </div>
        </div>
</section>
@endsection