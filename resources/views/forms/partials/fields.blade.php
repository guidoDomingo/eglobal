
<div class="form-row">
    <div class="form-group col-md-12">
        {!! Form::label('label', 'Label') !!}
        {!! Form::text('label', null , ['class' => 'form-control', 'placeholder' => 'Ingrese un label para el formulario' ]) !!}
    </div>
    <div class="form-group col-md-12">
        {!! Form::label('data_type', 'Tipo de dato') !!}
        {!! Form::text('data_type', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el tipo de dato' ]) !!}
    </div>
</div>

<div class="clearfix"></div>

<div class="form-row">
    <div class="form-group col-md-6">
        {!! Form::label('valorminimo', 'Valor mínimo') !!}
        {!! Form::text('valorminimo', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el valor mínimo' ]) !!}
    </div>
    <div class="form-group col-md-6">
        {!! Form::label('valormaximo', 'Valor máximo') !!}
        {!! Form::text('valormaximo', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el valor máximo' ]) !!}
    </div>
</div>

<div class="clearfix"></div>
{!! Form::hidden('campaigns_id', $campaign_id) !!}

{{-- <div class="form-row">
    <div class="form-group col-md-6">
        <div class="form-group">
            {!! Form::label('campaigns_id', 'Campañas/Promociones') !!} 
            {!! Form::select('campaigns_id', $campaigns, $campaigns_id, ['id' => 'atmId','class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => 'Seleccione una campaña']) !!}
        </div>
    </div>
</div>
<div class="clearfix"></div> --}}
