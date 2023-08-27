<div class="row">
   @if (\Sentinel::getUser()->inRole('superuser') || \Sentinel::getUser()->inRole('atms_v2.area_eglobalt'))   {{--desbloquear campo de numero de contrato --}}
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('number', 'Número de Contrato') !!}
                <div class="input-group">
                    <div class="input-group-addon">
                        <i class="fa fa-file"></i>
                    </div>
                    {!! Form::text('number', isset($contrato) ? $contrato->number : null  , ['class' => 'form-control', 'placeholder' => 'Ingrese el número de contrato..' ,'id' =>'number_contract']) !!}

                </div>
            </div>
        </div>
    @else
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('number', 'Número de Contrato') !!}
                <div class="input-group">
                    <div class="input-group-addon">
                        <i class="fa fa-file"></i>
                    </div>
                    {!! Form::text('number', isset($contrato) ? $contrato->number : null  , ['class' => 'form-control', 'placeholder' => 'Ingrese el número de contrato..' ,'id' =>'number_contract','readonly'=>true]) !!}

                </div>
            </div>
        </div>
    @endif
    <div class="col-md-6">
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
    </div>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
           {!! Form::label('contract_type', 'Tipo de Contrato') !!} @if ((\Sentinel::getUser()->inRole('superuser')))<a style="margin-left: 8em" href='#' id="nuevoTipoContrato" data-toggle="modal" data-target="#modalNuevoTipoContrato"><small>Agregar <i class="fa fa-plus"></i></small></a>@endif
            <div class="input-group">
                <div class="input-group-addon">
                    <i class="fa fa-filter"></i>
                </div>
                {!! Form::select('contract_type', $contract_types, null, ['id' => 'contract_type','class' => 'form-control select2', 'style' => 'width: 100%','placeholder'=>'Seleccione un tipo de contrato...']) !!}
            </div>
       </div>
   </div>

    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('credit_limit', 'Línea de Crédito') !!}
            <div class="input-group">
                <div class="input-group-addon">
                    <i class="fa fa-money"></i>
                </div>
                {!! Form::text('credit_limit', null , ['class' => 'form-control', 'placeholder' => 'Ingrese la línea de crédito', 'id' =>'credit_limit_contract']) !!}
            </div>
        </div>
    </div>
</div>

<div class="row">

    {{-- <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('date_init', 'Fecha inicio de vigencia') !!}
            <div class="input-group">
                <div class="input-group-addon">
                    <i class="fa fa-clock-o"></i>
                </div>
                {!! Form::text('date_init', null, ['id'=>'date_init_contract' ,'class' => 'form-control', 'data-inputmask' => "'alias': 'dd/mm/yyyy'", 'data-mask' => 'dd/mm/yyyy','placeholder' => 'Ingrese la fecha de Vigencia' ]) !!}

            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('date_end', 'Fecha Finalización') !!}
            <div class="input-group">
                <div class="input-group-addon">
                    <i class="fa fa-clock-o"></i>
                </div>
                {!! Form::text('date_end', null, [ 'id'=>'date_end_contract' ,'class' => 'form-control','data-inputmask' => "'alias': 'dd/mm/yyyy'", 'data-mask' => 'dd/mm/yyyy' , 'placeholder' => 'Ingrese la fecha de Finalización']) !!}
            </div>
        </div>
    </div> --}}

    <div class="col-md-6">

        <div class="form-group">
            <label>Rango de vigencia:</label>
            <div class="input-group">
                <div class="input-group-addon">
                    <i class="fa fa-clock-o"></i>
                </div>
                <input name="reservationtime" type="text" id="reservationtime" class="form-control" value="{{$reservationtime_contract or ''}}"  />
            </div>
        </div>

        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('status', 'Estado') !!}
                <div class="input-group">
                    <div class="input-group-addon">
                        <i class="fa fa-check-square-o"></i>
                    </div>
                    {!! Form::select('status', ['1' => 'RECEPCIONADO','2' => 'ACTIVO', '3' => 'INACTIVO','4' => 'VENCIDO'],null, ['class' => 'form-control', 'id' =>'status_contract']) !!}
                </div>
            </div>

            @if ((\Sentinel::getUser()->inRole('contract.check.receptiondate')) || (\Sentinel::getUser()->inRole('superuser')))
                @if(isset($contrato))
                    @if ($contrato->signature_date !== null)
                        <div class="form-group">
                            <div class="form-check">
                                {!! Form::checkbox('reception_date', 1, true) !!}
                                {!! Form::label('reception_date', 'Documentos recepcionados') !!}
                            </div>
                        </div>
                    @else
                        <div class="form-group">
                            <div class="form-check">
                                {!! Form::checkbox('reception_date', 1, false) !!}
                                {!! Form::label('reception_date', 'Documentos recepcionados') !!}
                            </div>
                        </div>
                    @endif
                   
                @else
                    <div class="form-group">
                        <div class="form-check">
                            {!! Form::checkbox('reception_date', 1, false) !!}
                            {!! Form::label('reception_date', 'Documentos recepcionados') !!}
                        </div>
                    </div>
                @endif



            @endif

        </div>

    </div>

    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('observation', 'Observaciones') !!}
            <div class="input-group">
                <div class="input-group-addon">
                    <i class="fa fa-comments"></i>
                </div>
                {!! Form::textarea('observation', null , ['class' => 'form-control', 'placeholder' => 'Ingrese una observación' ]) !!}
            </div>
        </div>
    </div>

</div>

{{--
<div class="row">
    <div class="col-md-12">
        <div class="form-group">
            {!! Form::label('observation', 'Observaciones') !!}
            <div class="input-group">
                <div class="input-group-addon">
                    <i class="fa fa-comments"></i>
                </div>
                {!! Form::text('observation', null , ['class' => 'form-control', 'placeholder' => 'Ingrese una observación' ]) !!}
            </div>
        </div>
    </div>
</div> --}}




{{-- {!! Form::file('image') !!} --}}
