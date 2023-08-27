@extends('layout')

@section('title')
    Parametros de comision #{{ $parametro_comision->id }}
@endsection
@section('content')
    <section class="content-header">
        <h1>
            <small>Modificación de datos de Parametros de comision</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="{{ route('parametros_comisiones.index') }}">Parametros de Comisión</a></li>
            <li><a href="#">{{ $parametro_comision->name }}</a></li>
            <li class="active">Modificar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Modificar {{ $parametro_comision->name }}</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._flashes')
                        @include('partials._messages')
                        {!! Form::model($parametro_comision, ['route' => ['parametros_comisiones.update', $parametro_comision->id ] , 'method' => 'PUT', 'id' => 'editarMarca-form']) !!}
                        @include('parametros_comisiones.partials.fields')

                        <button type="submit" class="btn btn-primary">Guardar</button>
                        {!! Form::close() !!}
                    </div>
                </div>
                <div class="box-footer">
{{--                    @include('marcas.partials.delete')--}}
                </div>
            </div>
        </div>
    </section>
@endsection
@section('js')

<script src="/bower_components/admin-lte/plugins/jquery-validation/dist/jquery.validate.min.js"></script>
<script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>
<script type="text/javascript">
    $('.select2').select2();

    //validacion formulario 
    $('#editarMarca-form').validate({
        rules: {
            "atm_id": {
                required: function(){
                    if($('#owner_id').val() == ''){
                        return true;
                    }else{
                        return false;
                    }
                }
            },
            "service_source_id": {
                required: true,
            },
            "service_id": {
                required: true,
            },
            "comision": {
                required: true,
            },
            "tipo_servicio_id": {
                required: true,
            },
        },
        messages: {
            "atm_id": {
                required: "Seleccione el atm",
            },
            "service_source_id": {
                required: "Seleccione el service source",
            },
            "service_id": {
                required: "Seleccione el servicio",
            },
            "comision": {
                required: "Ingrese el porcentaje de comisión",
            },
            "tipo_servicio_id": {
                required: "Seleccione el tipo de servicio",
            },
        },
        errorPlacement: function (error, element) {
            error.appendTo(element.parent());
        }
    });

    $('#service_source_id').on('select2:select',function(){
        $.get("{{ route('parametros_comisiones.services') }}", {service_source_id: $(this).val()}).done(function(respuesta){
            $('#service_id').empty().trigger('change');
            $('#service_id').select2({data: respuesta});
        });
    });

    $('#tipo_servicio_id').on('select2:select',function(){
        if(this.value == 1){
            $('.proveedores').hide();
            $.get("{{ route('parametros_comisiones.services_products') }}", {}).done(function(respuesta){
                $('#service_id').empty().trigger('change');
                $('#service_id').select2({data: respuesta});
            });
        }else{
            $('.proveedores').show();
        }
    });

    $('#owner_id').on('select2:select',function(){
        $.get("{{ route('parametros_comisiones.atms') }}", {owner_id: $(this).val()}).done(function(respuesta){
            $('#atm_id').empty().trigger('change');
            $('#atm_id').select2({data: respuesta});
        });
    });

</script>
@endsection
@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
@endsection
