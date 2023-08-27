@extends('app')

@section('title')
    Nuevo Rol
@endsection

@section('aditional_css')
     
     <style>
        .roles-create {
            display: flex;
            justify-content: space-around !important;
        }
     </style>

@endsection

@section('content')
    <section class="content-header">
        <h1>
            <small>Crear nuevo Rol</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="{{ route('roles.index') }}">Redes</a></li>
            <li class="active">Nuevo</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Nuevo Rol </h3>
                    </div>
                    <div class="box-body">
                        @include('partials._flashes')
                        @include('partials._messages')
                        {!! Form::open(['route' => ['roles.store'] , 'method' => 'POST']) !!}
                        <div class="roles-create">
                             <div class="col-md-6">
                                @include('roles.partials.fields')
                            </div>
                            <div class="col-md-6 p-5">
                                <a class="btn btn-default" href="{{ route('roles.index') }}" role="button">Cancelar</a>
                                <button type="submit" class="btn btn-primary">Guardar</button>
                            </div>
                        </div>
                        <div class="col-md-12 mt-5">
                            @include('roles.partials.permissions')
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
                <div class="box-footer">
                    {{--@include('roles.partials.delete')--}}
                </div>
            </div>
        </div>
    </section>
@endsection
@section('page_scripts')
    @parent
    {{--@include('roles.partials.scripts')--}}
    {{--    @include('partials._delete_form_js')--}}
@endsection
