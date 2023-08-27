@extends('layout')
@section('title')
Configuración de Promociones
@endsection
@section('content')
<section class="content-header">
  <h1>
    Promociones
    <small>Gooddeals</small>
  </h1>
  <ol class="breadcrumb">
    <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
    <li><a href="{{ route('atm.index') }}">Atms</a></li>
    <li><a href="#">Configuración de Promociones</a></li>
    <li class="active">lista</li>
  </ol>
</section>
<section class="content">
  @include('partials._flashes')
<div class="box">
    <div class="box-header">
        <h3 class="box-title">
            Editar
        </h3>
        <div class="box-tools">
                
        </div>
    </div>
    {!! Form::open(['route' => ['gooddeals.last_update'] , 'method' => 'POST', 'role' => 'form', 'id'=>'promociones-form']) !!}
    <input name="_token" value="{{ csrf_token() }}" type="hidden">
    <div class="box-body">
        <div class="row">
            <div class="col-md-4">
                <div class="form-group">
                    {!! Form::label('instancia', 'Instancia') !!} 
                    {!! Form::select('instancia', $data['instancias'], null, ['id' => 'instancia_id','class' => 'form-control select2', 'style' => 'width: 100%', 'placeholder' => 'Seleccione una instancia']) !!}
                </div>
            </div>
            <div class="col-md-4">
                <!-- Date and time range -->
                <div class="form-group">
                    <label>Fecha de última actualización</label>
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-clock-o"></i>
                        </div>
                        <input name="last_update" type="text" id="last_update" class="form-control pull-right" />
                    </div>
                    <!-- /.input group -->
                </div>
            </div>
        </div>
    </div>
    <div class="box-footer">
        <a class="btn btn-default" href="{{ route('atm.index')}}" role="button">Cancelar</a>
        <button type="submit" class="btn btn-primary pull-right">Guardar</button>
    </div>
    {!! Form::close() !!}
</div>
</section>

@endsection
@section('page_scripts')
    <script src="/bower_components/admin-lte/plugins/jquery-validation/dist/jquery.validate.min.js"></script>
    <script src="/bower_components/admin-lte/plugins/autoNumeric/autoNumeric.js"></script>
    <script src="/bower_components/admin-lte/plugins/iCheck/icheck.min.js"></script>
    <script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>
     <!-- date-range-picker -->
    <link href="/bower_components/admin-lte/plugins/datepicker/datepicker3.css" rel="stylesheet" type="text/css" />
    <script src="/bower_components/admin-lte/plugins/daterangepicker/moment.min.js"></script>

    <!-- bootstrap datepicker -->
    <script src="/bower_components/admin-lte/plugins/datepicker/bootstrap-datepicker.js"></script>
    <script src="/bower_components/admin-lte/plugins/datepicker/locales/bootstrap-datepicker.es.js" charset="UTF-8"></script>
<script>
    $(function(){
        $('.select2').select2();
        //Date range picker
        $('#last_update').datepicker({
            language: 'es',
            format: 'dd/mm/yyyy',
        });

        // validacion del formulario
        $('#promociones-form').validate({
            rules: {
                "instancia": {
                    required: true,
                },
                "last_update": {
                    required: true,
                }
            },
            messages: {
                "instancia": {
                    required: "Seleccione la instancia",
                },
                "last_update": {
                    required: "Ingrese la fecha",
                }
            },
            errorPlacement: function (error, element) {
                if(element.attr('name') == "last_update"){
                    error.appendTo(element.parent().parent());
                }else{
                    error.appendTo(element.parent());
                }
            },
            submitHandler: function(form){
                $.ajax({
                    'type': 'post',
                    'url': $(form).attr('action'),
                    'data': $(form).serialize(),
                });

                swal({   title: 'Atención',   text: 'Se ha enviado exitosamente la solicitud de actualización de las promociones. Recibira una notificación en su email, con caso de error o de éxito',   type: 'success',   confirmButtonText: "Aceptar" });
            }
        });

        $('#instancia_id').on('select2:select',function(){
            $.ajax({
                'type': 'get',
                'url': "{{ route('gooddeals.get_last_update') }}",
                'data': {instancia_id: $(this).val()},
            }).success(function(response){
                $('#last_update').val(response);
            });
        });
    });
</script>

@endsection
@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/iCheck/all.css" rel="stylesheet" type="text/css" />
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
@endsection
<iframe id="descarga" style="display:none;">
</iframe>
