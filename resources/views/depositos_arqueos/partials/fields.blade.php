<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            {!! Form::label('fecha', 'Fecha de la boleta del depósito', ['id' => 'label2', 'name' => 'label2']) !!}        
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
    <div class="col-md-12">
        <div class="form-group">
            {!! Form::label('boleta_numero', 'Numero de Boleta', ['id' => 'label1', 'name' => 'label1']) !!}            
            {!! Form::text('boleta_numero', null , ['id' => 'boleta_numero', 'class' => 'form-control', 'placeholder' => 'Ingrese el numero', 'required' => 'true' ]) !!}
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            {!! Form::label('banco', 'Banco') !!}
            <small> (Seleccionar banco para desplegar cuenta) </small>
            {!! Form::select('banco_id', $bancos, null, ['id' => 'banco_id', 'class' => 'form-control select2','placeholder' => 'Seleccione una opción', 'required' => 'true']) !!}
        </div>
    </div>
</div>  

<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            {!! Form::label('tipo_credito', 'Tipo Crédito') !!}
            <small> (Seleccionar Tipo de Crédito) </small>
            {!! Form::select('tipo_credito', $comboTipoCreditos, null, ['id' => 'tipo_credito', 'class' => 'form-control select2','placeholder' => 'Seleccione una opción', 're
            quired' => 'true']) !!}
        </div>
    </div>
</div>  

<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            {!! Form::label('recaudador', 'Recaudador') !!}
            <small> (Seleccionar Recaudador) </small>
            {!! Form::select('recaudador', $comboRecaudadores, null, ['id' => 'recaudador', 'class' => 'form-control select2','placeholder' => 'Seleccione una opción', 'required' => 'true']) !!}
        </div>

        <div class="form-group">                        
            {!! Form::hidden('transactions', null , ['id' => 'transactions_list', 'class' => 'form-control', 'placeholder' => '', 'required' => 'true' ]) !!}
        </div>        

        <div class="form-group">                        
            {!! Form::hidden('total_amount', null , ['id' => 'total_amount', 'class' => 'form-control', 'placeholder' => '', 'required' => 'true' ]) !!}
        </div>        

    </div>
</div>