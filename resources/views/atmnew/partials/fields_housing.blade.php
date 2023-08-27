
<div class="form-group">
    {!! Form::label('housings', 'Asignar Housing', ['class' => 'col-xs-2']) !!} 
    <div class="input-group">
        <div class="input-group-addon">
            <i class="fa fa-filter"></i>
        </div>
        {!! Form::select('housing_id', $housings, null,['class' => 'form-control select2']); !!}
    </div>
</div>

    
    