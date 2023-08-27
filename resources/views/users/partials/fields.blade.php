<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            {!! Form::label('description', 'Descripcion') !!}
            {!! Form::text('description', null, ['class' => 'form-control', 'autocomplete' => 'off']) !!}
        </div>
    </div>

    <div class="col-md-12">
        <div class="form-group">
            {!! Form::label('username', 'Usuario') !!}
            {!! Form::text('username', null, ['class' => 'form-control', 'placeholder' => 'Introduzca nombre de usuario', 'autocomplete' => 'off']) !!}
        </div>
    </div>

    <div class="col-md-12">
        <div class="form-group">
            {!! Form::label('doc_number', 'Documento') !!}
            {!! Form::text('doc_number', null, ['class' => 'form-control', 'placeholder' => 'Nro de Cédula', 'autocomplete' => 'off']) !!}
        </div>
    </div>

    <div class="col-md-12">
        <div class="form-group">
            {!! Form::label('email', 'E-mail') !!}
            {!! Form::text('email', null, ['class' => 'form-control', 'placeholder' => 'Introduzca e-mail', 'autocomplete' => 'off']) !!}
        </div>
    </div>

    <div class="col-md-12">
        <div class="form-group">
            {!! Form::label('branch', 'Sucursal:') !!}
            {!! Form::text('branch', null, ['class' => 'form-control input-lg', 'id' => 'selectBranches', 'placeholder' => 'Seleccione una Sucursal']) !!}
        </div>
    </div>

    <div class="col-md-12">
        <div class="form-group">
            {!! Form::label('owners', 'Red:') !!}
            {!! Form::text('owners', null, ['class' => 'form-control input-lg', 'id' => 'selectOwners', 'placeholder' => 'Seleccione una Red']) !!}
        </div>
    </div>

    <div class="col-md-12">
        <div class="form-group">
            {!! Form::label('roles', 'Roles de Usuario:') !!}
            {!! Form::text('roles', !empty($rolesIds) ? $rolesIds : null, ['class' => 'form-control input-lg', 'id' => 'selectRoles', 'placeholder' => 'Seleccione un Rol de Usuario']) !!}
        </div>
    </div>
</div>

@section('page_scripts')
    @include('partials._selectize')
    <script>
        $('#selectRoles').selectize({
            delimiter: ',',
            persist: false,
            openOnFocus: true,
            valueField: 'id',
            labelField: 'name',
            searchField: 'name',
            render: {
                item: function(item, escape) {
                    return '<div><span class="label label-primary">' + escape(item.name) + '</span></div>';
                }
            },
            options: {!! $rolesJson !!}
        });
    </script>
    <script>
        $('#selectBranches').val('');
        $('#selectBranches').selectize({
            delimiter: ',',
            persist: false,
            openOnFocus: true,
            valueField: 'id',
            labelField: 'description',
            searchField: 'description',
            maxItems: 1,
            options: {!! $branchJson !!}
        });
    </script>
    <script>
        $('#selectOwners').selectize({
            delimiter: ',',
            persist: false,
            openOnFocus: true,
            valueField: 'id',
            labelField: 'name',
            searchField: 'name',
            maxItems: 1,

            options: {!! $ownersJson !!}
        });
    </script>
@append