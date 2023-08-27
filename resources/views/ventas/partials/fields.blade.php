<div class="form-group">
    {!! Form::label('grupo', 'Cliente', ['class' => 'col-xs-2']) !!} <a href='#' id="nuevoGrupo" data-toggle="modal" data-target="#modalNuevoGrupo"><small>Agregar <i class="fa fa-plus"></i></small></a>
    {{--{!! Form::label('grupo', 'Cliente') !!}--}}
    {!! Form::select('group_id', $grupos, null, ['id' => 'group_id', 'class' => 'form-control select2','placeholder' => 'Seleccione una opción']) !!}
</div>
<div class="form-group">
    {!! Form::label('vendedores', 'Vendedor') !!}
    {!! Form::select('id_vendedor', $vendedores, null, ['id' => 'id_vendedor', 'class' => 'form-control select2','placeholder' => 'Seleccione una opción']) !!}
</div>

<div class="form-group">
    {!! Form::label('fecha', 'Fecha de la Venta') !!}
    {!! Form::input('date', 'fecha', 'fecha' , ['class' => 'form-control', 'placeholder' => 'fecha', 'style' => 'width:250' ]) !!}

</div> 

<div class="form-group">
    {!! Form::label('serialnumber', 'Codigo de maquina') !!}
    {!! Form::select('serialnumber', $seriales, null, ['id' => 'serial', 'class' => 'form-control select2', 'name' => 'serialnumber[]', 'multiple' => 'multiple']) !!}
</div>

<div class="form-group">
    {!! Form::label('acreedores', 'Acreedor') !!}
    {!! Form::select('id_acreedor', $acreedores, null, ['id' => 'id_acreedor', 'class' => 'form-control select2','placeholder' => 'Seleccione una opción']) !!}
</div>
<div class="form-group">
    {!! Form::label('tipo_venta', 'Tipo de Venta') !!}
    {!! Form::select('tipo_venta_id', $tipo_ventas, null, ['id' => 'tipo_venta_id', 'class' => 'form-control select2 tipo_venta_id', 'placeholder' => 'Seleccione una opción']) !!}
</div>
<div class="form-group">
    {!! Form::label('amount', 'Importe') !!}
    {!! Form::text('amount', 0, ['id' => 'amount','class' => 'form-control', 'placeholder' => 'Ingresar el importe' ]) !!}
</div>
<div class="num_cuotas" style="display: none">
    <div class="form-group">
        {!! Form::label('num_cuota', 'Cantidad de Cuotas') !!}
        {!! Form::number('num_cuota', null , ['id' => 'num_cuota', 'class' => 'form-control', 'placeholder' => 'Ingresar el numero de Cuotas']) !!}
        {!! Form::label('cant_cuotas', 'Monto en cuotas: ', ['id' => 'cant_cuotas', 'name' => 'cant_cuotas']) !!}
    </div>
</div>

<a class="btn btn-default" href="{{ route('venta.index') }}" role="button">Cancelar</a>