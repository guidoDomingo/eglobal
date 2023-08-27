@extends('app')

@section('title')
    Rol {{ $role->name }}
@endsection
@section('content')
    <section class="content-header">
        <h1>
            {{ $role->name }}
            <small>Modificación de datos de Rol</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="{{ route('roles.index') }}">Redes</a></li>
            <li><a href="#">{{ $role->name }}</a></li>
            <li class="active">modificar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Modificar {{ $role->name }}</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._flashes')
                        @include('partials._messages')
                        {!! Form::model($role, ['route' => ['roles.update', $role ] , 'method' => 'PUT']) !!}
                        <div class="d-flex">
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
