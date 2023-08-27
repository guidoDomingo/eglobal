{{-- <div class="form-group">
    {!! Form::label('id', 'Id') !!}
    {!! Form::text('id', null , ['class' => 'form-control', 'placeholder' => 'Id' ]) !!}
</div> --}}
{{-- <div class="form-group">
    {!! Form::label('brand_id', 'Marca') !!}
    {!! Form::text('brand_id', old('brand_id'), ['class' => 'form-control', 'placeholder' => 'Marca']) !!}
</div> --}}

<div class="form-group">
    {!! Form::label('description', 'Descripción') !!}
    {!! Form::text('description', old('description'), ['class' => 'form-control', 'placeholder' => 'Descripción del modelo']) !!}
</div>
<div class="form-group">
    {!! Form::label('created_at', 'Fecha de Creacion') !!}
    {!! Form::text('created_at', old('created_at'),['class' => 'form-control', 'placeholder' => 'Fecha de creacion' ,'id'=>'reservationtime']) !!}
</div>
<div class="form-group">
    <div class="form-check">
        {!! Form::checkbox('priority', 1, false) !!}
        {!! Form::label('priority', 'Prioritario') !!}
    </div>
</div>
<a class="btn btn-default" href="{{ route('brands.index') }}" role="button">Cancelar</a>

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