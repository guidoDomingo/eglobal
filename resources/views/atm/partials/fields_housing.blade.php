<div class="form-group">

    <div class="form-group">
        {!! Form::label('housings', 'Housing', ['class' => 'col-xs-2']) !!} 
        @if(!empty($housing_id))
            {!! Form::select('housing_id', $housings, $housing_id,['class' => 'form-control select2']); !!}
        @else
            {!! Form::select('housing_id', $housings, null,['class' => 'form-control select2']); !!}
        @endif
    </div>

    @if(isset($branch) && $branch->updatedBy != null)
    <div class="form-group">
        {!! Form::label('updated_by', 'Modificado por:') !!}
        <p>{{  $branch->updatedBy->username }}  el {{ date('d/m/y H:i', strtotime($branch->updated_at)) }}</p>
    </div>
    @endif

    
    <a class="btn btn-default" href="{{ route('atm.index') }}" role="button">Cancelar</a>
    