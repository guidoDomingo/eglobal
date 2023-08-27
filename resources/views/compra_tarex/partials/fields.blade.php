<div class="form-group col-md-3 col-xs-3" >
    {!! Form::label('factura', 'Nro. de factura compra:') !!} <br>
    {!! Form::text('factura1', null, ['id' => 'factura1', 'maxlength' => '3', 'onkeyup' => "if (this.value.length == this.getAttribute('maxlength')) factura2.focus()", 'style' => 'width:30']) !!}
    {!! Form::text('factura2', null, ['id' => 'factura2', 'maxlength' => '3', 'onkeyup' => "if (this.value.length == this.getAttribute('maxlength')) factura3.focus()", 'style' => 'width:30']) !!}
    {!! Form::text('factura3', null, ['id' => 'factura3', 'style' => 'width:100']) !!}
</div>    
<div class="form-group col-md-2 col-xs-2">
    {!! Form::label('fecha', 'Fecha:', ['style' => 'padding-left:5' ]) !!}
    {!! Form::input('date', 'fecha', 'fecha' , ['placeholder' => 'fecha', 'style' => 'width:150', 'class' => 'form-control' ]) !!}
</div> 

<div class="form-group col-md-2 col-xs-2">
    {!! Form::label('timbrado', 'Timbrado', ['style' => 'padding-left:5' ]) !!}
    {!! Form::text('timbrado', null, ['id' => 'timbrado', 'style' => 'width:130', 'class' => 'form-control']) !!}
</div> 

<div class="form-group col-md-3 col-xs-3">
    {!! Form::label('forma_pago', 'Forma de Pago', ['style' => 'padding-left:5' ]) !!}
    {!! Form::select('forma_pago', $forma_pago, $selected_fp, ['id' => 'forma_pago', 'class' => 'select2 forma_pago form-control', 'placeholder' => 'Seleccione una opción']) !!}
</div> 

<div class="form-group col-md-2 col-xs-2">
    {!! Form::label('modalidad', 'Modalidad', ['style' => 'padding-left:5' ]) !!}
    {!! Form::select('modalidad', $modalidades, $selected_modalidad, ['id' => 'forma_pago', 'class' => 'select2 modalidad form-control', 'placeholder' => 'Seleccione una opción']) !!}
</div> 


<div class="form-group col-md-12 col-xs-12">
    {!! Form::label('producto', 'Producto: ') !!}
    {!! Form::text('producto', 'TAREX', ['id' => 'producto', 'Readonly'=>'Readonly', 'style' => 'width:150']) !!}

    {!! Form::label('costo', 'Costo:', ['style' => 'padding-left:20' ]) !!}
    {!! Form::number('costo', '933.40' , ['id' => 'costo', 'style' => 'width:120', 'Readonly'=>'Readonly']) !!}

    {!! Form::label('desde', 'Desde:', ['style' => 'padding-left:20' ]) !!}
    {!! Form::number('desde', null , ['id' => 'desde', 'style' => 'width:120']) !!}

    {!! Form::label('cantidad', 'Cantidad:', ['style' => 'padding-left:20' ]) !!}
    {!! Form::text('cantidad', null , ['id' => 'cantidad', 'style' => 'width:120']) !!}
</div>


<a class="btn btn-default" href="{{ route('compra_tarex.index') }}" role="button">Cancelar</a>