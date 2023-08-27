<div class="form-group">
    {!! Form::label('app', 'Aplicación') !!}
    @if(isset($aplicaciones))
    	{!! Form::select('application_id', $aplicaciones, $app_id, ['reasignar' => false, 'class' => 'form-control select2','placeholder' => 'Seleccione una aplicacion', 'style' => 'width:100%', 'id' => 'aplicacionId']) !!}
    @else
    	{!! Form::select('application_id', [], null, ['reasignar' => false, 'class' => 'form-control select2','placeholder' => 'Seleccione una aplicacion', 'style' => 'width:100%', 'id' => 'aplicacionId']) !!}
    @endif
    {!! Form::hidden('owner_id') !!}
    {!! Form::hidden('atm_parts',$atm_parts) !!}
    {!! Form::hidden('atm_id',null, ['id' => 'atmId']) !!}
</div>
@if($atm_parts <= 0)
    {!! Form::hidden('reasignar',false, ['id' => 'reasignar']) !!}
    <div class="form-group">
        {!! Form::label('tipo_dispositivo', 'Tipo Dispositivo') !!}
        {!! Form::select('tipo_dispositivo', [ '1 | 3' => 'Reciclador - 3 cassettes', '2 | 4' => 'Gran Pagador - 4 cassettes','2 | 6' => 'Gran Pagador - 6 cassettes', '3 | 0' => 'Miniterminal - Solo Box'], null, ['reasignar' => false, 'class' => 'form-control select2','placeholder' => 'Seleccione un dispositivo', 'style' => 'width:100%', 'id' => 'tipoDispositivo']) !!}
    </div>
@else
    {!! Form::hidden('reasignar',true, ['id' => 'reasignar']) !!}
@endif