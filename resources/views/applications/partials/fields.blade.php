<div class="form-group">
    {!! Form::label('name', 'Nombre') !!}
    {!! Form::text('name', null , ['class' => 'form-control', 'placeholder' => 'Nombre' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('resolution_width', 'Resolución: Ancho') !!}
    {!! Form::text('resolution_width', null , ['class' => 'form-control', 'placeholder' => 'Ancho en Pixeles' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('resolution_height', 'Resolución: Alto') !!}
    {!! Form::text('resolution_height', null , ['class' => 'form-control', 'placeholder' => 'Alto en Pixeles' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('version_code', 'Código de Versión') !!}
    {!! Form::text('version_code', null , ['class' => 'form-control', 'placeholder' => 'Código de Versión' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('version_name', 'Nombre de Versión') !!}
    {!! Form::text('version_name', null , ['class' => 'form-control', 'placeholder' => 'Nombre de Versión' ]) !!}
</div>
@if (\Sentinel::getUser()->hasRole('superuser') || \Sentinel::getUser()->hasRole('security.admin'))
    <div class="form-group">
        {!! Form::label('owner_id', 'Red:') !!}
        {!! Form::select('owner_id',$owners ,$selected_owner , ['class' => 'form-control','placeholder' => 'Seleccione un Tipo...']) !!}
    </div>
@endif
<div class="form-group">
    {!! Form::label('active', 'Estado') !!}
    {{ $active_desc }}
</div>


<a class="btn btn-default" href="{{ route('applications.index') }}" role="button">Cancelar</a>
