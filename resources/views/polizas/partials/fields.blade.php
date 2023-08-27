<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('insurance_code', 'Endoce') !!}
            <div class="input-group">
                <div class="input-group-addon">
                    <i class="fa fa-keyboard-o"></i>
                </div>
                {!! Form::text('insurance_code', 'EGLOBALT S.A.' , ['class' => 'form-control', 'placeholder' => 'Ingrese el código del endoce..','readonly'=>true ]) !!}
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('number', 'Número de Póliza') !!}
            <div class="input-group">
                <div class="input-group-addon">
                    <i class="fa fa-file"></i>
                </div>
                {!! Form::text('number', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el número de contrato..' ]) !!}
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('capital', 'Capital asegurado') !!}
            <div class="input-group">
                <div class="input-group-addon">
                    <i class="fa fa-money"></i>
                </div>
                {!! Form::text('capital', null , ['id' => 'capital_poliza','class' => 'form-control', 'placeholder' => 'Ingrese el capital asegurado']) !!}
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
           {!! Form::label('insurance_policy_type_id', 'Tipo de Póliza') !!}@if ((\Sentinel::getUser()->inRole('superuser'))) <a style="margin-left: 8em" href='#' id="nuevoTipoPoliza" data-toggle="modal" data-target="#modalNuevoTipoPoliza"><small>Agregar <i class="fa fa-plus"></i></small></a>@endif
            <div class="input-group">
                <div class="input-group-addon">
                    <i class="fa fa-filter"></i>
                </div>
                {!! Form::select('insurance_policy_type_id', $insurance_types, null, ['id' => 'insurance_policy_type_id','class' => 'form-control select2', 'style' => 'width: 100%','placeholder'=>'Seleccione un tipo de póliza...']) !!}
            </div>
       </div>
   </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('observaciones', 'Observaciones') !!}
            <div class="input-group">
                <div class="input-group-addon">
                    <i class="fa fa-comments"></i>
                </div>
                {!! Form::text('observaciones', null , ['class' => 'form-control', 'placeholder' => 'Ingrese un observación' ]) !!}
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('status', 'Estado') !!}
            <div class="input-group">
                <div class="input-group-addon">
                    <i class="fa fa-check-square-o"></i>
                </div>
                {!! Form::select('status', ['1' => 'ACTIVO','2' => 'INACTIVO', '3' => 'VENCIDO'],null ,['class' => 'form-control select2','style' => 'width: 100%', 'id' => 'status_policy']) !!}
            </div>
        </div>
    </div>


</div>


