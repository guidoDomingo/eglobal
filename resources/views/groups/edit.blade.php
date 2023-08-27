@extends('layout')

@section('title')
    Grupo {{ $group->description }}
@endsection
@section('content')
    <section class="content-header">
        <h1>
            {{ $group->description  }}
            <small>Modificación de datos del grupo</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="{{ route('groups.index') }}">Grupos</a></li>
            <li><a href="#">{{ $group->description }}</a></li>
            <li class="active">Modificar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Modificar {{ $group->description }}</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._flashes')
                        @include('partials._messages')
                        {!! Form::model($group, ['route' => ['groups.update', $group->id ] , 'method' => 'PUT']) !!}
                        @include('groups.partials.fields')

                        <button type="submit" class="btn btn-primary">Guardar</button>
                        {!! Form::close() !!}
                    </div>
                </div>
                <div class="box-footer">
{{--                    @include('owners.partials.delete')--}}
                </div>
            </div>
        </div>
    </section>
@endsection
@section('page_scripts')
    {{--@include('partials._delete_form_js')--}}
@endsection
