<div class="form-group">
    {!! Form::label('description', 'Descripcion') !!}
    {!! Form::text('description', null , ['id' => 'description', 'class' => 'form-control', 'placeholder' => 'Descripcion' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('administration', 'Gestión de recaudo:') !!}
    {!! Form::select('managers', array('' =>'- Seleccionar -','1' => 'Eglobalt', '0' => 'Gestión Propia'), null ,['id' => 'managers', 'class' => 'form-control select']) !!}
</div>
<div class="form-group" id="block_form" style='display:none'>
    {!! Form::label('blocking', 'Frecuencia de bloqueo') !!}
    {!! Form::select(
        'blocking', 
        array(
            '1' => 'Lunes. (1 Día)', 
            '3' => 'Lunes, miércoles y viernes. (3 Días)',
            '5' => 'Lunes a Viernes. (5 Días)',
            '6' => 'Lunes, miércoles y viernes. (3 Días) - Franky'
        ), 
        null,
        ['id' => 'blocking', 'class' => 'form-control select']) !!}
</div>
<div class="form-group">
    {!! Form::label('ruc', 'Ruc') !!}
    {!! Form::text('ruc', null , ['id' => 'ruc', 'class' => 'form-control', 'placeholder' => 'Ruc' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('direccion', 'Direccion') !!}
    {!! Form::text('direccion', null , ['id' => 'direccion', 'class' => 'form-control', 'placeholder' => 'Direccion' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('telefono', 'Telefono') !!}
    {!! Form::text('telefono', null , ['id' => 'telefono', 'class' => 'form-control', 'placeholder' => 'Telefono' ]) !!}
</div>

@if(isset($group) && $group->createdBy != null)
    <div class="form-group">
        {!! Form::label('created_by', 'Creado por:') !!}
        <p>{{  $group->createdBy->username }}  el {{ date('d/m/y H:i', strtotime($group->created_at)) }}</p>
    </div>
@endif
@if(isset($group) && $group->updatedBy != null)
    <div class="form-group">
        {!! Form::label('updated_by', 'Modificado por:') !!}
        <p>{{  $group->updatedBy->username }}  el {{ date('d/m/y H:i', strtotime($group->updated_at)) }}</p>
    </div>
@endif
{!! Form::hidden('abm_v2','v2') !!} 
