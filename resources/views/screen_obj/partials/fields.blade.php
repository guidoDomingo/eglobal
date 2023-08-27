<div class="form-group">
    @if (isset($screens))
        {!! Form::label('screen_id', 'Pantalla') !!}
        {!! Form::select('screen_id',$screens ,null , ['class' => 'form-control']) !!}
    @elseif(isset($screen_name))
        {!! Form::label('screen', 'Pantalla') !!}
        <p>{{ $screen_name }}</p>
    @endif
</div>
<div class="form-group">
    {!! Form::label('name', 'Nombre') !!}
    {!! Form::text('name', null , ['class' => 'form-control', 'placeholder' => 'Nombre' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('location_x', 'Posición X') !!}
    {!! Form::text('location_x', null , ['class' => 'form-control', 'placeholder' => 'Posición X' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('location_y', 'Posición Y') !!}
    {!! Form::text('location_y', null , ['class' => 'form-control', 'placeholder' => 'Posición Y' ]) !!}
</div>
<div class="form-group">
    @if(isset($objects))
        {!! Form::label('object_type_id', 'Tipo de Objeto') !!}
        {!! Form::select('object_type_id',$objects ,null , ['class' => 'form-control object-type','placeholder' => 'Seleccione un Tipo...']) !!}
    @elseif(isset($object_name))
        {!! Form::label('object_name', 'Tipo de Objeto') !!}
        <p>{{ $object_name }}</p>
    @endif
</div>
<div class="form-group">
    {!! Form::hidden('hdn_html', null , ['class' => 'form-control hdn_html' ]) !!}
</div>
<div id="properties_container">
    @include('screen_obj.partials.properties')
</div>
<a class="btn btn-default" href="{{ route('screens.screens_objects.index', [ 'screen_id' => $screen_id]) }}" role="button">Cancelar</a>
