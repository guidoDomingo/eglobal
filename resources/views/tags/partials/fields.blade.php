
<div class="form-row">
    <div class="form-group col-md-12">
        {!! Form::label('description', 'Descripción') !!}
        {!! Form::text('description', null , ['class' => 'form-control', 'placeholder' => 'Ingrese una descripción' ]) !!}
    </div>
    <div class="form-group col-md-12">
        {!! Form::label('value', 'Valor') !!}
        {!! Form::text('value', null , ['class' => 'form-control', 'placeholder' => 'Ingrese un valor' ]) !!}
    </div>
</div>
{!! Form::hidden('tickets_campaigns_id', $ticket_id) !!}
{!! Form::hidden('campaign_id', $campaign_id) !!}


<div class="clearfix"></div>
