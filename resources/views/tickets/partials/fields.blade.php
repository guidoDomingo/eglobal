{{-- <div class="form-row">
    <div class="form-group col-md-12">
        <div class="form-group">
            {!! Form::label('campaigns_id', 'Campañas/Promociones') !!} 
            {!! Form::select('campaigns_id', $campaigns, $campaigns_id, ['id' => 'atmId','class' => 'form-control select2', 'style' => 'width:100%', 'placeholder' => 'Seleccione una campaña']) !!}
        </div>
    </div>
</div>

<div class="clearfix"></div> --}}

<div class="form-row">
    <div class="form-group col-md-12">
        {!! Form::label('header', 'Header') !!}
        {!! Form::text('header', null , ['class' => 'form-control', 'placeholder' => 'Ingrese un header' ]) !!}
    </div>
    <div class="form-group col-md-12">
        {!! Form::label('footer', 'Footer') !!}
        {!! Form::text('footer', null , ['class' => 'form-control', 'placeholder' => 'Ingrese un footer' ]) !!}
    </div>
</div>

<div class="clearfix"></div>
{!! Form::hidden('campaigns_id', $campaign_id) !!}


