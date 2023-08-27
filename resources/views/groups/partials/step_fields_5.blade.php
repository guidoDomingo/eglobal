{{--<div class="form-group">
    {!! Form::label('group_id', 'Grupo',['class' => 'col-md-2']) !!} <a href='#' id="nuevoGrupo" data-toggle="modal" data-target="#modalNuevoGrupo"><small>Agregar <i class="fa fa-plus"></i></small></a>
    {!! Form::select('group_id',$groups, $selected_group , ['class' => 'form-control select2','style' => 'width: 100%','placeholder' => 'Seleccione un grupo...']) !!}
</div>
--}}
<div class="form-group">
    {!! Form::label('group_id', 'Grupo') !!}  <a style="margin-left: 8em" href='#' id="nuevoGrupo" data-toggle="modal" data-target="#modalNuevoGrupo"><small>Agregar <i class="fa fa-plus"></i></small></a>
        <div class="input-group">
            <div class="input-group-addon">
                <i class="fa fa-sitemap"></i>
            </div>
            @if(isset($grupo))
                @if(empty($grupo))
                    {!! Form::select('group_id', $groups , null , ['id' => 'group_id', 'class' => 'form-control select2 object-type','placeholder' => 'Seleccione un Grupo...','style' => 'width: 100%']) !!}
                @else
                    {!! Form::select('group_id', [$grupo->id => $grupo->description], $grupo->id, ['class' => 'form-control select2 object-type','placeholder' => 'Seleccione un Grupo...','style' => 'width: 100%']) !!}
                @endif
            @else
                {!! Form::select('group_id', $groups , null , ['id' => 'group_id', 'class' => 'form-control select2 object-type','placeholder' => 'Seleccione un grupo...','style' => 'width: 100%']) !!}
            @endif
        </div>
</div>

@if(isset($atm->id))
    {!! Form::hidden('atm_id',null, ['id' => 'atm_id']) !!}
@endif


@section('page_scripts')
    <script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>
@endsection