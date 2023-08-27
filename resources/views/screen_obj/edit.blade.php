@extends('layout')

@section('title')
    {{ $screenObject->name }} - Objeto de Pantalla
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Objeto de Pantalla
            <small>Modificación de Objeto de Pantalla</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="{{ route('atm.index') }}">Atms</a></li>
            <li><a href="{{ route('applications.index') }}">Apps</a></li>
            <li><a href="{{ route('screens.index') }}">Pantallas</a></li>
            <li><a href="{{ route('screens_objects.index') }}">Objetos</a></li>
            <li><a href="#">{{ $screenObject->name }}</a></li>
            <li class="active">Modificar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Modificar "{{ $screenObject->name }}"</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._flashes')
                        @include('partials._messages')
                        {!! Form::model($screenObject, ['route' => ['screens_objects.update', $screenObject->id ] ,
                         'id'=>'object_form' ,'method' => 'PUT','files'=>true]) !!}
                        @include('screen_obj.partials.fields')
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        {!! Form::close() !!}
                        <script src="{{ asset('/bower_components/admin-lte/plugins/ckeditor_full/ckeditor.js') }}"></script>
                        <script>
                            CKEDITOR.replace( 'html' );
                        </script>
                    </div>
                </div>
                <div class="box-footer">
                    {{--@include('screen_obj.partials.delete')--}}
                </div>
            </div>
        </div>
    </section>
@endsection
@section('page_scripts')
    <script>
        $( "#object_form" ).submit(function() {
            var content = CKEDITOR.instances['html'].getData();
            $('.hdn_html').val(content);
        });
    </script>
    {{--@include('partials._delete_form_js')--}}
@endsection
