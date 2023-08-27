<div class="form-group">
    {!! Form::label('serialnumber', 'Serial') !!}
    {!! Form::text('serialnumber', null , ['class' => 'form-control', 'placeholder' => 'Serial number' ]) !!}
</div>

<div class="form-group" style="display:none">
    {!! Form::label('descripcion', 'Descripcion del dispositivo') !!}
    {!! Form::text('descripcion', null , ['class' => 'form-control', 'placeholder' => 'Descripcion del dispositivo' ]) !!}
</div>
{{-- <div class="form-group">
    {!! Form::label('brand_id', 'Marca') !!}
    {!! Form::select('brand_id',$marcas,null, ['class' => 'form-control','placeholder' => 'Seleccione una Marca...']) !!}
</div>  --}}
<div class="form-group">
    {!! Form::label('model_id', 'Modelo') !!}
    {!! Form::select('model_id',$modelos,null, ['class' => 'form-control select2','style' => 'width: 100%','placeholder' => 'Seleccione un Modelo...']) !!}
</div> 
<div class="form-group">
    {!! Form::label('installation_date', 'Fecha de Instalacion') !!}
    {!! Form::text('installation_date', old('installation_date'),['class' => 'form-control', 'placeholder' => 'Fecha de Instalacion' ,'id'=>'reservationtime']) !!}
</div>

{{-- <div class="form-group">
{!! Form::label('housing_id', 'Housing') !!}
{!! Form::text('housing_id', old('housing_id') , ['class' => 'form-control', 'placeholder' => 'Housing' ]) !!}
</div>  --}}
  {{-- <div class="form-group">
    {!! Form::label('housing_id', 'Housing') !!}
    {!! Form::select('housing_id',$housings,null, ['class' => 'form-control select2','style' => 'width: 100%','placeholder' => 'Seleccione un Housing...']) !!}
</div>  --}}

<div class="form-group">
    {!! Form::label('activo', 'Activo') !!}
    {!! Form::select('activo', ['FALSE' => 'NO','TRUE' => 'SI'], old('activo'), ['class' => 'form-control select2','style' => 'width: 100%']) !!}
</div>


<a class="btn btn-default" href="{{ route('miniterminales.index') }}" role="button">Cancelar</a>

@section('js')
    <!-- InputMask -->
    <script src="/bower_components/admin-lte/plugins/input-mask/jquery.inputmask.js"></script>
    <script src="/bower_components/admin-lte/plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
    <script src="/bower_components/admin-lte/plugins/input-mask/jquery.inputmask.extensions.js"></script>
    <!-- date-range-picker -->
    <link href="/bower_components/admin-lte/plugins/datepicker/datepicker3.css" rel="stylesheet" type="text/css" />
    <script src="/bower_components/admin-lte/plugins/daterangepicker/moment.min.js"></script>

    <!-- bootstrap datepicker -->
    <script src="/bower_components/admin-lte/plugins/datepicker/bootstrap-datepicker.js"></script>
    <script src="/bower_components/admin-lte/plugins/datepicker/locales/bootstrap-datepicker.es.js" charset="UTF-8"></script>

    <script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>
    <script>
            //Date range picker
            $('#reservationtime').datepicker({
                changeMonth: true,
                changeYear: true,
                language: 'es',
                format: 'yyyy/mm/dd',
                firstDay: 1
            });//.datepicker("setDate", 'now');

         //Cascading dropdown list de redes / sucursales
        $('.select2').select2();
   
    </script>
@endsection
<link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
