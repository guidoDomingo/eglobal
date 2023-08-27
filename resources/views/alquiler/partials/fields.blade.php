<div class="form-group">
    {!! Form::label('grupo', 'Cliente', ['class' => 'col-xs-2']) !!} <a href='#' id="nuevoGrupo" data-toggle="modal" data-target="#modalNuevoGrupo"><small>Agregar <i class="fa fa-plus"></i></small></a>
    {{--{!! Form::label('grupo', 'Cliente') !!}--}}
    {!! Form::select('group_id', $grupos, null, ['id' => 'group_id', 'class' => 'form-control select2','placeholder' => 'Seleccione una opción']) !!}
</div>

<div class="form-group">
    {!! Form::checkbox('checkbox', 'checked', false, ['id' => 'cbox1']) !!} <span style="font-weight:bold;">Este Cliente es prestador de servicios</span> 
</div>

<div class="form-group">
    {!! Form::label('serialnumber', 'Codigo de maquina') !!}
    {!! Form::select('serialnumber', $seriales, null, ['id' => 'serial', 'class' => 'form-control select2', 'name' => 'serialnumber[]', 'multiple' => 'multiple']) !!}
</div>

{{--<div class="form-group">
    {!! Form::label('tipo_venta', 'Tipo de Venta') !!}
    {!! Form::select('tipo_venta_id', $tipo_ventas, null, ['id' => 'tipo_venta_id', 'class' => 'form-control select2 tipo_venta_id', 'placeholder' => 'Seleccione una opción']) !!}
</div>--}}
<div class="form-group">
    {!! Form::label('amount', 'Importe') !!}
    {{--{!! Form::text('amount', 0, ['id' => 'amount','class' => 'form-control', 'readonly'=>'readonly', 'placeholder' => 'Ingresar el importe' ]) !!}--}}
    {!! Form::text('amount', null, ['id' => 'amount','class' => 'form-control', 'placeholder' => 'Ingresar el importe' ]) !!}
</div>

<div class="form-group">
    {!! Form::label('num_cuota', 'Cantidad de Cuotas') !!}
    {!! Form::text('num_cuota', null, ['id' => 'num_cuota','class' => 'form-control', 'placeholder' => 'Ingresar el numero de cuota' ]) !!}
    {{--{!! Form::select('num_cuota', $num_cuotas, null, ['id' => 'num_cuota', 'class' => 'form-control select2 num_cuota', 'placeholder' => 'Seleccione una opción']) !!}--}}
    {!! Form::label('cant_cuotas', 'Monto total: ', ['id' => 'cant_cuotas', 'name' => 'cant_cuotas']) !!}
</div>
{{--<div class="num_cuotas">
    <div class="form-group">
        {!! Form::label('num_cuota', 'Cantidad de Cuotas') !!}
        {!! Form::number('num_cuota', null , ['id' => 'num_cuota', 'class' => 'form-control', 'placeholder' => 'Ingresar el numero de Cuotas']) !!}
        {!! Form::label('cant_cuotas', 'Monto en cuotas: ', ['id' => 'cant_cuotas', 'name' => 'cant_cuotas']) !!}
    </div>
</div>--}}

<a class="btn btn-default" href="{{ route('alquiler.index') }}" role="button">Cancelar</a>