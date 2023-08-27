<div class="row">

    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('atm', 'ATM') !!}
            <small> (Seleccionar el atm que corresponde la boleta) </small>
            {!! Form::select('atm_id', $atms, null, ['id' => 'atm_id', 'class' => 'form-control select2', 'placeholder' => 'Seleccione una opción', 'required' => 'true']) !!}
        </div>
    </div>
    
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('tipo_pago', 'Tipo de Pago') !!}
            <small> (Seleccionar tipo de pago para desplegar banco/s) </small>
            {!! Form::select('tipo_pago_id', $tipo_pago, null, ['id' => 'tipo_pago_id', 'class' => 'form-control select2','placeholder' => 'Seleccione una opción', 'required' => 'true']) !!}
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('fecha', 'Fecha de la boleta del deposito', ['id' => 'label2', 'name' => 'label2']) !!}
            <small> (El rango válido es de 10 días antes a la actual o la fecha actual) </small>
            <div class="input-group">
                <div class="input-group-addon">
                    <i class="fa fa-clock-o"></i>
                </div>
                {!! Form::text('fecha', null, ['id' => 'last_update', 'class' => 'form-control', 'placeholder' => 'Ingrese la fecha', 'required' => 'true']) !!}
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('banco', 'Banco') !!}
            <small> (Seleccionar banco para desplegar cuenta) </small>
            {!! Form::select('banco_id', $bancos, null, ['id' => 'banco_id', 'class' => 'form-control select2','placeholder' => 'Seleccione una opción', 'required' => 'true']) !!}
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('boleta_numero', 'Numero de Boleta', ['id' => 'label1', 'name' => 'label1']) !!}
            <small> (Sin ceros a la izquierda. Modo correcto: 50000. Modo incorrecto: 00050000) </small>
            {!! Form::text('boleta_numero', null , ['id' => 'boleta_numero', 'class' => 'form-control', 'placeholder' => 'Ingrese el numero', 'required' => 'true' ]) !!}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('cuenta_bancaria', 'Cuenta Bancaria') !!}
            <small> (Número de Cuenta del banco seleccionado) </small>
            {!! Form::select('cuenta_bancaria_id_aux', $cuentas, null, ['id' => 'cuenta_bancaria_id_aux', 'class' => 'form-control select2','placeholder' => 'Seleccione una opción']) !!}
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('monto', 'Monto') !!}
            <small> (Sin ceros a la izquierda y que sea la TOTALIDAD de la cuota. Modo correcto: 350000. Modo incorrecto: 000350000) </small>
            {!! Form::text('monto', null, ['id' => 'monto', 'class' => 'form-control', 'placeholder' => 'Monto', 'required' => 'true']) !!}
            {!! Form::label('cant_cuotas', ' ', ['id' => 'cant_cuotas', 'name' => 'cant_cuotas']) !!}
        </div>
    </div>
</div>

<div class="cuota" style="display: none">
    {!! Form::text('cuota_monto', '', ['id' => 'cuota_monto', 'name' => 'cuota_monto']) !!}
    {{--{!! Form::label('cuota_numero', $cuota->num_cuota, ['id' => 'cuota_numero', 'name' => 'cuota_numero']) !!}--}}
</div>

<div style="display: none">
    {!! Form::select('cuenta_bancaria_id', $cuentas, null, ['id' => 'cuenta_bancaria_id', 'class' => 'form-control select2', 'placeholder' => 'Seleccione una opción']) !!}
</div> 