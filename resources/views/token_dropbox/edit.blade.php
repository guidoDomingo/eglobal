@extends('layout')

@section('title')
    Gestor de Token: {{ $dropbox[0]->name }}
@endsection
@section('content')
    <section class="content-header">
        <h1>
            {{ $dropbox[0]->name }}
            <small>Modificación de datos del token para dropbox</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Aplicaciones</a></li>
            <li><a href="{{ route('token_dropbox.index') }}">Gestor de token</a></li>
            <li><a href="#">{{ $dropbox[0]->name }}</a></li>
            <li class="active">Modificar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Modificar datos del token: {{ $dropbox[0]->name }}</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._flashes')
                        @include('partials._messages')
                        {!! Form::model($dropbox[0], ['route' => ['token_dropbox.update', $dropbox[0]->id ] , 'method' => 'PUT', 'id' => 'editaToken-form']) !!}
                            <div class="form-row">
                                <div class="form-group col-md-2">
                                    <div class="form-group">
                                        {!! Form::label('id', 'ID') !!}
                                        {!! Form::text('id', null , ['class' => 'form-control', 'disabled' => 'disabled' ]) !!}
                                    </div>
                                </div>
                                <div class="form-group col-md-10">
                                    <div class="form-group">
                                        {!! Form::label('name', 'Descripción') !!}
                                        {!! Form::text('name', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el nombre del token' ]) !!}
                                    </div>
                                </div>
                                <div class="form-group col-md-12">
                                    <div class="form-group">
                                        {!! Form::label('hash', 'Token') !!}
                                        {{-- {!! Form::text('hash', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el token' ]) !!} --}}
                                        <textarea id="hash" name="hash" rows="4" cols="50" class="form-control">{{$dropbox[0]->hash}}</textarea>
                                    </div>
                                </div>
                            </div>  
                        
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <div class="form-group">
                                        <div class="form-group col-md-3" style="margin-top: 25px;">
                                            <div class="form-group">
                                                <button type="submit" class="btn btn-primary">Actualizar</button>
                                                <a class="btn btn-default" href="{{ url('/') }}" role="button">Salir</a>
                                            </div> 
                                        </div> 
                                    </div> 
                                </div> 
                            </div>

                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@include('partials._selectize')

@section('js')

<script src="/bower_components/admin-lte/plugins/jquery-validation/dist/jquery.validate.min.js"></script>
<script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>
<script type="text/javascript">
    $('.select2').select2();
           
</script>
@endsection

@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
  
@endsection