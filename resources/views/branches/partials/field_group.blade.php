<div class="form-group">

    <div class="form-group">
        {!! Form::label('branches', 'Sucursales', ['class' => 'col-xs-2']) !!} 
        {!! Form::select('branch_id', $branches, null,['id' => 'branch_id','class' => 'form-control select2', 'style' => 'width: 100%']); !!}
    </div>

    @if(isset($branch) && $branch->updatedBy != null)
    <div class="form-group">
        {!! Form::label('updated_by', 'Modificado por:') !!}
        <p>{{  $branch->updatedBy->username }}  el {{ date('d/m/y H:i', strtotime($branch->updated_at)) }}</p>
    </div>
    @endif

    
    <a class="btn btn-default" href="{{ route('groups.branches',['groupId' => $groupId]) }}" role="button">Cancelar</a>
    