<div class="form-group">
    {!! Form::label('serialnumber', 'Serial') !!}
    {!! Form::text('serialnumber', null , ['class' => 'form-control', 'placeholder' => 'Serial' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('housing_type_id', 'Tipo Housing') !!}
    {!! Form::select('housing_type_id', ['2' => 'Miniterminal','1' => 'virtual'], old ('housing_type_id'), ['class' => 'form-control']) !!}

</div>
<div class="form-group">
    {!! Form::label('installation_date', 'Fecha de Instalacion') !!}
     {!! Form::text('installation_date', old('installation_date'), ['class' => 'form-control', 'placeholder' => 'Fecha de Instalacion' ,'id'=>'reservationtime']) !!}
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
</script>
@endsection
