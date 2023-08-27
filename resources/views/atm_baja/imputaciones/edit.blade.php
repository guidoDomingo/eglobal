@extends('layout')

@section('title')
    BAJA | Modificar Imputación de deudas
@endsection
@section('content')

    <section class="content-header">
        <h1>
            GESTIÓN ASEGURADORA | 
            <small>Modificar Imputación de deudas</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Gestor de terminales</a></li>
            <li><a href="#">Baja</a></li>
            <li><a href="#">Imputación de deudas</a></li>
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
                        {!! Form::model($imputacion, ['route' => ['imputaciones.update', $imputacion->id ] , 'method' => 'PUT', 'id' => 'editarImputacion-form']) !!}
                            <div class="form-row">
                                <div class="form-group col-md-6 borderd-campaing">
                                    <div class="title"><h4>&nbsp;<i class="fa fa-file-text-o"></i>&nbsp; IMPUTACIÓN DE DEUDA &nbsp;</h4></div>
                                    <div class="container-campaing">
                            
                                        <div class="form-group col-md-12">
                                            {!! Form::label('estado', 'Estado:') !!}
                                            <br>

                                            @if ($imputacion->estado == 1)
                                            {!! Form::radio('estado', 1, true) !!}
                                            {!! Form::label('estado', 'Pendiente') !!}
                                            &nbsp;  &nbsp; &nbsp;&nbsp;
                                            {!! Form::radio('estado', 'cobrado') !!}
                                            {!! Form::label('estado', 'Cobrado') !!}
                                        @else
                                            {!! Form::radio('estado', 'pendiente') !!}
                                            {!! Form::label('estado', 'Pendiente') !!}
                                            &nbsp;  &nbsp; &nbsp;&nbsp;
                                            {!! Form::radio('estado', 0,true) !!}
                                            {!! Form::label('estado', 'Cobrado') !!}
                                        @endif

                                        </div>
                        
                                        <div class="form-group col-md-6">
                                            {!! Form::label('numero', 'Numeración interna:') !!}
                                            <div class="input-group">
                                                <div class="input-group-addon">
                                                    <i class="fa fa-money"></i>
                                                </div>
                                                {!! Form::text('numero', null , ['class' => 'form-control', 'readonly'=>'readonly' ]) !!}
                                             </div>
                                        </div>
                                        <div class="form-group col-md-6">
                                            {!! Form::label('numero_contrato', 'Número de contrato') !!}
                                            <div class="input-group">
                                                <div class="input-group-addon">
                                                    <i class="fa fa-money"></i>
                                                </div>
                                                {!! Form::text('numero_contrato', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el numero del contrato.' ]) !!}
                                             </div>
                                        </div>
                        
                                        <div class="form-group col-md-12">
                                            {!! Form::label('fecha_siniestro', 'Fecha de siniestro:') !!}
                                            <div class="input-group">
                                                <div class="input-group-addon">
                                                    <i class="fa fa-calendar"></i>
                                                </div>
                                                {!! Form::text('fecha_siniestro', null , ['class' => 'form-control', 'data-inputmask' => "'alias': 'dd/mm/yyyy'", 'data-mask' => 'dd/mm/yyyy' ]) !!}
                                            </div>
                                        </div>
                        
                                        <div class="form-group col-md-12">
                                            {!! Form::label('fecha_cobro', 'Fecha de cobro:') !!}
                                            <div class="input-group">
                                                <div class="input-group-addon">
                                                    <i class="fa fa-calendar"></i>
                                                </div>
                                                {!! Form::text('fecha_cobro', null , ['class' => 'form-control', 'data-inputmask' => "'alias': 'dd/mm/yyyy'", 'data-mask' => 'dd/mm/yyyy' ]) !!}
                                            </div>
                                        </div>
                        
                                        <div class="form-group col-md-12">
                                            {!! Form::label('monto', 'Monto:') !!}
                                            <div class="input-group">
                                                <div class="input-group-addon">
                                                    <i class="fa fa-money"></i>
                                                </div>
                                                {!! Form::text('monto', null , ['id' => 'monto','class' => 'form-control', 'placeholder' => 'Gs.' ]) !!}
                                             </div>
                                        </div>
                                        
                                        <div class="form-group col-md-12">
                                            {!! Form::label('procentaje_franquicia', 'Porcentaje de franquicia:') !!}
                                            <div class="input-group">
                                                <div class="input-group-addon">
                                                    <i class="fa fa-keyboard-o"></i>
                                                </div>
                                                {!! Form::text('procentaje_franquicia', null , ['class' => 'form-control', 'placeholder' => '%' ]) !!}
                                             </div>
                                        </div>
                        
                        
                                           

                                    </div>
                                </div>
                                @include('atm_baja.info')
                            </div>      

                            <div class="clearfix"></div>
                            <div class="form-row">
                                <a class="btn btn-default"  href="{{ url('atm/new/'.$grupo->id.'/'.$grupo->id.'/imputacion') }}" role="button">Cancelar</a>
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
    $('#fecha_cobro').datepicker({
        language: 'es',
        format: 'dd/mm/yyyy',
    });
    $('#fecha_siniestro').datepicker({
        language: 'es',
        format: 'dd/mm/yyyy',
    });
    //validacion formulario 
    $('#editarImputacion-form').validate({
        rules: {
            "numero": {
                required: true,
            },
            "numero_contrato": {
                required: true,
            },
            "fecha_siniestro": {
                required: true,
            },
            "fecha_cobro": {
                required: true,
            },
            "monto": {
                required: true,
            },
            "procentaje_franquicia": {
                required: true,
            }
        },
        messages: {
            "numero": {
                required: "Ingrese una númeracion interna.",
            },
            "numero_contrato": {
                required: "Ingrese el numero de contrato.",
            },
            "fecha_siniestro": {
                required: "Seleccione una fecha.",
            },
            "fecha_cobro": {
                required: "Seleccione una fecha.",
            },
            "procentaje_franquicia": {
                required: "Ingrese el porcentaje de franquicia.",
            },
            "monto": {
                required: "Ingrese el monto.",
            }
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
            height: 505px;
            margin-top: 20px;
            position: relative;
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