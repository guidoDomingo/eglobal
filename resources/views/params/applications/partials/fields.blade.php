<div class="form-group">
@if(!isset($app_params))
  {!! Form::label('param_id', 'Parámetro') !!}
  {!! Form::select('param_id',$params ,(isset($app_params->param_id))?$app_params->param_id:null , [ 'class' => 'form-control object-type','placeholder' => 'Seleccionar...']) !!}
@else
    {!! Form::label('param_id', 'Parámetro') !!} <br/>
    {!! Form::label('param_id', $app_params->description) !!}
@endif
</div>
<div class="form-group">
  {!! Form::label('value', 'Valor') !!}
  {!! Form::text('value', (isset($app_params->value))?$app_params->value:null , ['class' => 'form-control', 'placeholder' => '' ]) !!}
</div>
