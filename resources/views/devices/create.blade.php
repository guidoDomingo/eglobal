@extends('layout')

@section('title')
    Nuevo Dispositivo
@endsection
@section('content')

    <section class="content-header">
        <h1>
            Dispositivo
            <small>Creación de nuevo Dispositivo</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Dispositivo</a></li>
            <li class="active">agregar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Nuevo Dispositivo</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._flashes')

                        @include('partials._messages')
                        {!! Form::open(['route' => ['housing.device.store', $housingId] , 'method' => 'POST', 'role' => 'form']) !!}
                        @include('devices.partials.fields')
                        <button type="submit" id='btn-submit' class="btn btn-primary">Guardar</button>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('js')

<script src="/bower_components/admin-lte/plugins/jquery-validation/dist/jquery.validate.min.js"></script>
<script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>
<!-- date-range-picker -->
<link href="/bower_components/admin-lte/plugins/datepicker/datepicker3.css" rel="stylesheet" type="text/css" />
<script src="/bower_components/admin-lte/plugins/daterangepicker/moment.min.js"></script>

<!-- bootstrap datepicker -->
<script src="/bower_components/admin-lte/plugins/datepicker/bootstrap-datepicker.js"></script>
<script src="/bower_components/admin-lte/plugins/datepicker/locales/bootstrap-datepicker.es.js" charset="UTF-8"></script>

<script type="text/javascript">
    $('.btn-submit').click(function(e){
            event.preventDefault();
            Swal.fire({
            title: '¿Seguro de enviar el formulario?',
            type: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Si',
            cancelButtonText: "No",
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',});
        });


</script>
@endsection
@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />


@endsection