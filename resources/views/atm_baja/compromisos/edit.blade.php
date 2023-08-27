@extends('layout')

@section('title')
    BAJA | Modificar compromiso de pago
@endsection
@section('content')

    <section class="content-header">
        <h1>
           Gestión de legales
            <small>Modificación del compromiso de pago</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Gestor de terminales</a></li>
            <li><a href="#">Baja</a></li>
            <li><a href="#">Documentaciones</a></li>
            <li><a href="#">Compromiso de pago</a></li>
            <li class="active">Modificar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                    </div>
                    <div class="box-body">
                        @include('partials._flashes')
                        @include('partials._messages')
                        {!! Form::model($compromiso, ['route' => ['compromisos.update', $compromiso->id ] , 'method' => 'PUT', 'id' => 'editarCompromiso-form']) !!}
                            <div class="form-row">
                                <div class="form-group col-md-6 borderd-campaing">
                                    <div class="title"><h4>&nbsp;<i class="fa fa-file-text-o"></i>&nbsp; COMPROMISO DE PAGO &nbsp;</h4></div>
                                    <div class="container-campaing">
                                        <div class="form-row">
                            
                                            <div class="form-group col-md-12">
                                                {!! Form::label('estado', 'Estado:') !!}
                                                <br>

                                                @if ($compromiso->estado == 1)
                                                    {!! Form::radio('estado',1,true) !!}
                                                    {!! Form::label('estado', 'Incumplido') !!}
                                                    &nbsp;  &nbsp; &nbsp;&nbsp;
                                                    {!! Form::radio('estado', 'cumplido') !!}
                                                    {!! Form::label('estado', 'Cumplido') !!}
                                                @else
                                                    {!! Form::radio('estado', 'incumplido') !!}
                                                    {!! Form::label('estado', 'Incumplido') !!}
                                                    &nbsp;  &nbsp; &nbsp;&nbsp;
                                                    {!! Form::radio('estado',2,true) !!}
                                                    {!! Form::label('estado', 'Cumplido') !!}
                                                @endif
                                            </div>
                            
                                            <div class="form-group col-md-6">
                                                {!! Form::label('numero', 'Número de compromiso:') !!}
                                                <div class="input-group">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-pencil-square-o"></i>
                                                    </div>
                                                    {!! Form::text('numero', null , ['class' => 'form-control',  'readonly' => 'readonly'  ]) !!}
                                                </div>
                                            </div>
                            
                                            <div class="form-group col-md-6">
                                                {!! Form::label('cantidad_pago', 'Cantidad de pagos:') !!}
                                                <div class="input-group">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-keyboard-o"></i>
                                                    </div>
                                                    {!! Form::text('cantidad_pago', null , ['class' => 'form-control', 'placeholder' => 'Ingrese la cantidad de pagos' ]) !!}
                                                </div>
                                            </div>
                            
                                            <div class="form-group col-md-6">
                                                {!! Form::label('monto', 'Monto:') !!}
                                                <div class="input-group">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-money"></i>
                                                    </div>
                                                    {!! Form::text('monto', null , ['id' => 'monto','class' => 'form-control', 'placeholder' => 'Gs.' ]) !!}
                                                </div>
                                            </div>
                            
                                            <div class="form-group col-md-6">
                                                {!! Form::label('fecha', 'Fecha:') !!}
                                                <div class="input-group">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-calendar"></i>
                                                    </div>
                                                    {!! Form::text('fecha', null , ['class' => 'form-control', 'data-inputmask' => "'alias': 'dd/mm/yyyy'", 'data-mask' => 'dd/mm/yyyy' ]) !!}
                                                </div>
                                            </div>
                            
                                            <div class="form-group col-md-12">
                                                <div class="form-group">
                                                    {!! Form::label('comentario', 'Comentario:') !!}
                                                    <div class="input-group">
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-comments"></i>
                                                        </div>
                                                        {!! Form::textarea('comentario', null , ['class' => 'form-control', 'placeholder' => 'Ingrese un comentario'  ]) !!}
                                                        {{-- <textarea rows="8" cols="30" class="form-control" id="comentario" name="comentario" placeholder="Agregar un comentario" value={!!$compromiso->comentario!!}></textarea> --}}
                                                    </div>
                                                </div>
                                            </div>                           
                                        </div>
                                    </div>
                                </div>
                                @include('atm_baja.info')
                            </div>
                            <div class="clearfix"></div>
                            <div class="form-row">
                                <a class="btn btn-default"  href="{{ url('atm/new/'.$grupo->id.'/'.$grupo->id.'/compromiso') }}" role="button">Cancelar</a>
                                <button type="submit" class="btn btn-primary">Guardar</button>
                            </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('js')
<script src="/bower_components/admin-lte/plugins/datepicker/bootstrap-datepicker.js"></script>
<script src="/bower_components/admin-lte/plugins/datepicker/locales/bootstrap-datepicker.es.js" charset="UTF-8"></script>
<script src="/bower_components/admin-lte/plugins/jquery-validation/dist/jquery.validate.min.js"></script>
<script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>

<link rel="stylesheet" href="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.css">
<script src="/bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.min.js"></script>

<script type="text/javascript">
    $(document).ready(function () {
         //separador de miles - Capital de la poliza
         var separadorPol = document.getElementById('monto');

        separadorPol.addEventListener('input', (e) => {
            var entradaPol = e.target.value.split(','),
            parteEnteraPol = entradaPol[0].replace(/\./g, ''),
            salidaPol = parteEnteraPol.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
            e.target.value = salidaPol;
        }, false);

        var monto = document.getElementById('monto').value;
        entryPoliza = monto.split(',');
        partEnteraPoliza = entryPoliza[0].replace(/\./g, ''),
        outputPoliza = partEnteraPoliza.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
        //insertar valor con separadores de miles
        document.getElementById("monto").value = outputPoliza;
    });
</script>    
<script type="text/javascript">
    $('.select2').select2();

    $('#listadoAtms').DataTable({
        "paging": true,
        "lengthChange": false,
        "searching": false,
        "ordering": true,
        "info": true,
        "autoWidth": false,
        "displayLength": 3,
        "language":{"url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"},
        "bInfo" : false


    });

    $('#fecha').datepicker({
        language: 'es',
        format: 'dd/mm/yyyy',
    });
    //validacion formulario 
    $('#editarCompromiso-form').validate({
        rules: {
            "numero": {
                required: true,
            },
            "monto": {
                required: true,
            },
            "cantidad_pago": {
                required: true,
            },
            "fecha": {
                required: true,
            },
        },
        messages: {
            "numero": {
                required: "Ingrese una númeracion interna.",
            },
            "monto": {
                required: "Ingrese el monto de la promesa de pago.",
            },
            "cantidad_pagos": {
                required: "Ingrese la cantidad de pagos.",
            },
            "fecha": {
                required: "Seleccione una fecha de vencimiento.",
            },
        },
        errorPlacement: function (error, element) {
            error.appendTo(element.parent());
        }
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

        .borderd-campaing {
            border: 1px solid #a1a1ac;
            border-radius: 4px;
            height: 370px;
            margin-top: 20px;
            position: relative;
            height: auto;
        }

        .borderd-campaing .title {
            margin: -25px 0 0 50px;
            background: #fff;
            padding: 3px;
            display: inline-block;
            font-weight: bold;
            position: absolute;
        }

        .borderd-campaing .campaing {
            padding: 10px;
        }
        .container-campaing {
            margin-top: 20px;
        }

        .borderd-content {
            border: 1px solid #a1a1ac;
            border-radius: 4px;
            height: 180px;
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

        /* INFO */
        .borderd-info {
            border: 1px solid #a1a1ac;
            border-radius: 4px;
            height: 505px;
            margin-top: 20px;
            position: relative;
            /* height: auto; */
        }

        .borderd-info .title {
            margin: -25px 0 0 50px;
            background: #fff;
            padding: 3px;
            display: inline-block;
            font-weight: bold;
            position: absolute;
        }
        .borderd-info .campaing {
            padding: 10px;
        }
        .container-info {
            margin-top: 20px;
        }

    </style>
@endsection