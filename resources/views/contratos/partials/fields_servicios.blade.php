<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('status', 'Tipo de conexión') !!}
            <div class="input-group">
                <div class="input-group-addon">
                    <i class="fa fa-check-square-o"></i>
                </div>
                {!! Form::select('status', ['1' => 'FIBRA','2' => 'HFC', '3' => 'Modem M2M'],null, ['class' => 'form-control']) !!}
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('contrato', 'Número de contrato') !!}
            <div class="input-group">
                <div class="input-group-addon">
                    <i class="fa fa-file"></i>
                </div>
                {!! Form::text('observation', null , ['class' => 'form-control', 'placeholder' => 'Ingrese un numero de contrato o cuenta.' ]) !!}
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('contrato', 'Especificaciones del servicio') !!}
            <div class="input-group">
                <div class="input-group-addon">
                    <i class="fa fa-comments"></i>
                </div>
                {!! Form::text('observation', null , ['class' => 'form-control', 'placeholder' => 'Ej: Ancho de banda.' ]) !!}
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('number', 'Asignación de serial de miniterminal') !!}
            <div class="input-group">
                <div class="input-group-addon">
                    <i class="fa fa-filter"></i>                
                </div>
                {!! Form::text('number', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el número de contrato..' ]) !!}
            </div>
        </div>
    </div>
    
    
</div>

