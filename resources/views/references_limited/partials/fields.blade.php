<div class="form-group col-md-12">
    <div class="form-group">
        {!! Form::label('parametros', 'Parámetros') !!}
        {!! Form::select('current_params_rule_id', $parametrosRules ,null , ['id' => 'current_params_rule_id','class' => 'form-control select2']) !!}
    </div>
</div>

<div class="form-group col-md-12">
    <div class="form-group">
        {!! Form::label('parametros', 'Reglas de Servicios') !!}
        {!! Form::select('service_rule_id', $serviciosRules ,null , ['id' => 'service_rule_id','class' => 'form-control select2']) !!}
    </div>
</div>

<div class="form-row">        
    <div class="form-group col-md-4">
        <div class="form-group">
            {!! Form::label('reference', 'Referencia') !!}
            {!! Form::text('reference', null , ['class' => 'form-control', 'placeholder' => 'Referencia' ]) !!}
        </div>
    </div>
    
</div>
<div class="clearfix"></div>


<div class="form-group col-md-4">
    <a class="btn btn-default" href="{{ route('references.index') }}" role="button">Cancelar</a>
    <button type="submit" class="btn btn-primary">Guardar</button>
</div>

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
