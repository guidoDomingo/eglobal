@extends('layout')

@section('title')
    Contratos
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Contrato
            <small>Modificación de datos del contrato</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="{{ route('contracts.index') }}">Gestor de terminales</a></li>
            <li><a href="#">Contrato</a></li>
            <li class="active">Modificar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Modificar</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._flashes')
                        @include('partials._messages')
                        {!! Form::model($contrato, ['route' => ['contracts.update', $contrato->id ] , 'method' => 'PUT', 'id' => 'editarContrato-form']) !!}
                            <div style="padding: 1%">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {!! Form::label('number', 'Número de Contrato') !!}
                                            <div class="input-group">
                                                <div class="input-group-addon">
                                                    <i class="fa fa-file"></i>
                                                </div>
                                                {!! Form::text('number', isset($contrato) ? $contrato->number : null  , ['class' => 'form-control', 'placeholder' => 'Ingrese el número de contrato.' ,'id' =>'number_contract']) !!}
                                            </div>
                                        </div>
                                    </div>
                            
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {!! Form::label('group_id', 'Grupo') !!}
                                            <div class="input-group">
                                                <div class="input-group-addon">
                                                    <i class="fa fa-sitemap"></i>
                                                </div>
                                                @if(isset($grupo))
                                                    @if(empty($grupo))
                                                        {!! Form::select('group_id', $groups , null , ['id' => 'group_id', 'class' => 'form-control select2 object-type','placeholder' => 'Seleccione un Grupo...','style' => 'width: 100%']) !!}
                                                    @else
                                                        {!! Form::select('group_id_aux', [$grupo->id => $grupo->description], $grupo->id, ['class' => 'form-control select2 object-type','disabled' => 'disabled','style' => 'width: 100%']) !!}                                                        {!! Form::hidden('group_id', $grupo->id) !!}

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
                                            {!! Form::label('contract_type', 'Tipo de Contrato') !!}
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
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>Rango de vigencia:</label>
                                            <div class="input-group">
                                                <div class="input-group-addon">
                                                    <i class="fa fa-clock-o"></i>
                                                </div>
                                                <input name="reservationtime" type="text" id="reservationtime" class="form-control" value="{{$reservationtime_contract or ''}}"  placeholder="__/__/____ - __/__/____" />
                                            </div>
                                        </div>
                                    </div>
                                </div>
        
                                <div class="row">
                                    <div class="col-md-6">
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
                            
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            {!! Form::label('observation', 'Observaciones') !!}
                                            <div class="input-group">
                                                <div class="input-group-addon">
                                                    <i class="fa fa-comments"></i>
                                                </div>
                                                {{-- {!! Form::textarea('observation', null , ['class' => 'form-control', 'placeholder' => 'Ingrese una observación' ]) !!} --}}
                                                <textarea rows="4" cols="30" class="form-control" id="observation" name="observation" placeholder="Agregar un comentario" value="">{{$contrato->observation}}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <div class="form-group">
                                        <div class="form-group col-md-3" style="margin-top: 25px;">
                                            <div class="form-group">
                                                <button type="submit" class="btn btn-primary">Guardar</button>
                                                <a class="btn btn-default" href="{{ route('contracts.index') }}" role="button">Cancelar</a>
                                            </div> 
                                        </div> 
                                    </div> 
                                </div> 
                            </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@include('partials._selectize')

@section('js')
<link href="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css" />
<script src="/bower_components/admin-lte/plugins/daterangepicker/moment.min.js"></script>
<script src="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker.js"></script>
<script src="/bower_components/admin-lte/plugins/jquery-validation/dist/jquery.validate.min.js"></script>
<script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>
<script type="text/javascript">
    $('.select2').select2();

    //validacion formulario 
    $('#nuevoContrato-form').validate({
        rules: {
            "number": {
                required: true,
            },
            "group_id": {
                required: true,
            },
            "contract_type": {
                required: true,
            },
            "credit_limit": {
                required: true,
            },
            "reservationtime": {
                required: true,
            },
        },
        messages: {
            "number": {
                required: "Ingrese el numero de contrato.",
            },
            "group_id": {
                required: "Seleccione el grupo.",
            },
            "contract_type": {
                required: "Seleccione el tipo de contrato.",
            },
            "credit_limit": {
                required: "Ingrese la linea de crédito.",
            },
            "reservationtime": {
                required: "Ingrese el rango de vigencia.",
            },
        },
        errorPlacement: function (error, element) {
            error.appendTo(element.parent());
        }
    });

    
    //separador de miles - limite de credito | Contratos
    var separador = document.getElementById('credit_limit_contract');
    separador.addEventListener('input', (e) => {
        var entrada = e.target.value.split(','),
        parteEntera = entrada[0].replace(/\./g, ''),
        salida = parteEntera.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
        e.target.value = salida;
    }, true);

    var credit_limit_contract = document.getElementById('credit_limit_contract').value;
    entry = credit_limit_contract.split(',');
    partEntera = entry[0].replace(/\./g, ''),
    output = partEntera.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
    console.log(output);
    //insertar valor con separadores de miles
    document.getElementById("credit_limit_contract").value = output;

    //Date range picker
    $('#reservationtime').daterangepicker({
        opens: 'right',
        locale: {
            applyLabel: 'Aplicar',
            fromLabel: 'Desde',
            toLabel: 'Hasta',
            customRangeLabel: 'Rango Personalizado',
            daysOfWeek: ['Dom', 'Lun', 'Mar', 'Mie', 'Jue', 'Vie','Sab'],
            monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Setiembre', 'Octubre', 'Noviembre', 'Diciembre'],
            firstDay: 1
        },
        format: 'DD/MM/YYYY',
        startDate: moment(),
        endDate: moment().add(12,'months'),
    });
  
</script>

@endsection

@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />

    <style>

        label span {
            font-size: 1rem;
        }

        label.error {
            color: red;
            font-size: 1rem;
            display: block;
            margin-top: 5px;
        }

        input.error {
            border: 1px dashed red;
            font-weight: 300;
            color: red;
        }
        .borderd-content {
            border: 1px solid #a1a1ac;
            border-radius: 4px;
            height: 300px;
            margin-top: 20px;
            position: relative;
        }
        .borderd-content .title {
            margin: -25px 0 0 50px;
            background: #fff;
            padding: 3px;
            display: inline-block;
            font-weight: bold;
            position: absolute;
        }
    </style>
@endsection