{{-- @if(isset($network))

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
            {!! Form::label('internet_service_contract_id', 'Contrato de servicio de internet') !!} <a style="margin-left: 8em" href='#' id="nuevoInternetServiceContract" data-toggle="modal" data-target="#modalNuevoInternetServiceContract"><small>Agregar <i class="fa fa-plus"></i></small></a>
                <div class="input-group">
                    <div class="input-group-addon">
                        <i class="fa fa-filter"></i>
                    </div>
                    {!! Form::select('internet_service_contract_id', $internet_service_contracts, null, ['id' => 'internet_service_contract_id','class' => 'form-control select2', 'style' => 'width: 100%','placeholder'=>'Seleccione un tipo de contrato...']) !!}
                </div>
        </div>
    </div>

        <div class="col-md-6">
            <div class="form-group">
            {!! Form::label('network_technology_id', 'Tecnología de Red') !!} <a style="margin-left: 8em" href='#' id="nuevoNetworkTechnology" data-toggle="modal" data-target="#modalNuevoNetworkTechnology"><small>Agregar <i class="fa fa-plus"></i></small></a>
                <div class="input-group">
                    <div class="input-group-addon">
                        <i class="fa fa-filter"></i>
                    </div>
                    {!! Form::select('network_technology_id', $network_technologies, null, ['id' => 'network_technology_id','class' => 'form-control select2', 'style' => 'width: 100%','placeholder'=>'Seleccione una tecnologia de red...']) !!}
                </div>
            </div>
        </div>

    </div>
        
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('bandwidth', 'Ancho de banda') !!}
                <div class="input-group">
                    <div class="input-group-addon">
                        <i class="fa fa-keyboard-o"></i>
                    </div>
                    {!! Form::text('bandwidth', null , ['class' => 'form-control', 'placeholder' => 'Ingrese un ancho de banda' ]) !!}
                </div>
            </div>
        </div>
        <h1>prueba 1</h1>
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('installation_date', 'Fecha de instalación') !!}
                <div class="input-group">
                    <div class="input-group-addon">
                        <i class="fa fa-clock-o"></i>
                    </div>
                    {!! Form::text('installation_date', null, ['id' => 'installation_date', 'class' => 'form-control reservationtime', 'placeholder' => 'Ingrese la fecha de instalación',]) !!}
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {!! Form::label('description', 'Descripción') !!}
                <div class="input-group">
                    <div class="input-group-addon">
                        <i class="fa fa-comments"></i>
                    </div>
                    {!! Form::text('description', null , ['class' => 'form-control', 'placeholder' => 'Ingrese una descripción' , 'id' => 'description_network']) !!}
                </div>
            </div>
        </div>
    </div>


    @if(isset($housings))
    <div class="form-group">
        {!! Form::label('housings', 'Housing', ['class' => 'col-xs-2']) !!} 
        <div class="input-group">
            <div class="input-group-addon">
                <i class="fa fa-filter"></i>
            </div>
            {!! Form::select('housing_id',$housings, null, ['class' => 'form-control select2']); !!}
        </div>
    </div>

    @else
    <div class="form-group" style="display: none">
        {!! Form::label('housings', 'Housing', ['class' => 'col-xs-2']) !!} 
        <div class="input-group">
            <div class="input-group-addon">
                <i class="fa fa-filter"></i>
            </div>
            {!! Form::select('housing_id',$housings, null, ['class' => 'form-control select2']); !!}
        </div>
    </div>

    @endif

@else --}}

    <div class="row">
        
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('internet_service_contract_id', 'Contrato de servicio de internet') !!} <a style="margin-left: 8em" href='#' id="nuevoInternetServiceContract" data-toggle="modal" data-target="#modalNuevoInternetServiceContract"><small>Agregar <i class="fa fa-plus"></i></small></a>
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-filter"></i>
                        </div>
                        {!! Form::select('internet_service_contract_id', $internet_service_contracts, null, ['id' => 'internet_service_contract_id','class' => 'form-control select2', 'style' => 'width: 100%','placeholder'=>'Seleccione un tipo de contrato...']) !!}  
                    </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
            {!! Form::label('network_technology_id', 'Tecnología de Red') !!} @if (\Sentinel::getUser()->inRole('superuser')) <a style="margin-left: 8em" href='#' id="nuevoNetworkTechnology" data-toggle="modal" data-target="#modalNuevoNetworkTechnology"><small>Agregar <i class="fa fa-plus"></i></small></a>   @endif
                <div class="input-group">
                    <div class="input-group-addon">
                        <i class="fa fa-filter"></i>
                    </div>
                    {!! Form::select('network_technology_id', $network_technologies, null, ['id' => 'network_technology_id','class' => 'form-control select2', 'style' => 'width: 100%','placeholder'=>'Seleccione una tecnologia de red...']) !!}
                </div>
            </div>
        </div>

    </div>
        
    <div class="row">
        
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('bandwidth', 'Ancho de banda') !!}
                <div class="input-group">
                    <div class="input-group-addon">
                        <i class="fa fa-keyboard-o"></i>
                    </div>
                    {!! Form::text('bandwidth', null, ['class' => 'form-control', 'placeholder' => 'Ingrese un ancho de banda' ]) !!}
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('installation_date', 'Fecha de instalación') !!}
                <div class="input-group">
                    <div class="input-group-addon">
                        <i class="fa fa-clock-o"></i>
                    </div>
                    {!! Form::text('installation_date', null, [ 'class' => 'form-control','data-inputmask' => "'alias': 'dd/mm/yyyy'", 'data-mask' => 'dd/mm/yyyy' , 'placeholder' => 'Ingrese la fecha de instalación']) !!}
                </div>
            </div>
        </div>

    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {!! Form::label('description', 'Descripción') !!}
                <div class="input-group">
                    <div class="input-group-addon">
                        <i class="fa fa-comments"></i>
                    </div>
                    {!! Form::text('description', null , ['class' => 'form-control', 'placeholder' => 'Ingrese una descripción' , 'id' => 'description_network']) !!}
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        @if(isset($housing_id))
      
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('housing_id', 'Serial asignado') !!}
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-filter"></i>
                        </div>
                        {!! Form::select('housing_id', $housings, null,['class' => 'form-control select2','disabled' => 'disabled']) !!}
                    </div>
                </div>
            </div>
      
        @else
       
            <div class="col-md-6">
                <div class="form-group">
                    {!! Form::label('housing_id', 'Asignar serial') !!} 
                    <div class="input-group">
                        <div class="input-group-addon">
                            <i class="fa fa-filter"></i>
                        </div>
                        {!! Form::select('housing_id', $housings, null,[ 'id' => 'housing_id', 'class' => 'form-control select2','style' => 'width: 100%','placeholder'=>'Seleccione una tecnologia de red...']) !!}

                    </div>
                </div>
            </div>
        
        @endif


        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('remote_access', 'Acceso remoto') !!}
                <div class="input-group">
                    <div class="input-group-addon">
                        <i class="fa fa-keyboard-o"></i>
                    </div>
                    {!! Form::text('remote_access', null, ['class' => 'form-control', 'placeholder' => 'Ingrese el acceso remoto del equipo' ]) !!}
                </div>
            </div>
        </div>
    </div>

{{-- @endif --}}



