<div class="form-group">
    {!! Form::label('grupo', 'Cliente', ['class' => 'col-xs-2']) !!}
    {{--{!! Form::label('grupo', 'Cliente') !!}--}}
    {!! Form::select('atm_id', $atms, null, ['id' => 'atm_id', 'class' => 'form-control select2','placeholder' => 'Seleccione una opción']) !!}
</div>

<div class="form-group">
    {!! Form::label('amount', 'Importe') !!}
    {!! Form::text('amount', null, ['id' => 'amount','class' => 'form-control', 'placeholder' => 'Ingresar el importe' ]) !!}
</div>

<a class="btn btn-default" href="{{ route('recibos_comisiones.index') }}" role="button">Cancelar</a>
<a class="info btn btn-primary" role="button">Aceptar</a>
{{--<button type="submit" class="btn btn-primary">Aceptar</button>--}}