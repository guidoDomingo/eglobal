@extends('layout')

@section('title')
    BAJA | Modificar Remisión de Pagaré
@endsection
@section('content')

    <section class="content-header">
        <h1>
            GESTIÓN JUDICIAL |
            <small>Modificación remisión de pagaré</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Gestor de terminales</a></li>
            <li><a href="#">Baja</a></li>
            <li><a href="#">Documentaciones</a></li>
            <li><a href="#">Remisión de Pagaré</a></li>
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
                        {!! Form::model($remision, ['route' => ['remisiones.update', $remision->id ] , 'method' => 'PUT', 'id' => 'editarRemision-form']) !!}
                            <div class="form-row">
                                <div class="form-group col-md-6 borderd-campaing">
                                    <div class="title"><h4>&nbsp;<i class="fa fa-file-text-o"></i>&nbsp; PAGARÉ &nbsp;</h4></div>
                                    <div class="container-campaing">
                            
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                {!! Form::label('numero', 'Numeración interna:') !!}
                                                <div class="input-group">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-money"></i>
                                                    </div>
                                                    {!! Form::text('numero', null , ['class' => 'form-control','readonly'=>'readonly' ]) !!}
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
                                                {!! Form::label('titular_deudor', 'Titular deudor:') !!}
                                                <div class="input-group">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-money"></i>
                                                    </div>
                                                    {!! Form::text('titular_deudor', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el nombre del titular.' ]) !!}
                                                 </div>
                                            </div>
                                        
                                            <div class="form-group col-md-12">
                                                {!! Form::label('importe', 'Importe:') !!}
                                                <div class="input-group">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-money"></i>
                                                    </div>
                                                    {!! Form::text('importe', null , ['id' => 'importe','class' => 'form-control', 'placeholder' => 'Gs.' ]) !!}
                                                 </div>
                                            </div>
                            
                                            <div class="form-group col-md-12">
                                                {!! Form::label('importe_deuda', 'Importe deuda:') !!}
                                                <div class="input-group">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-money"></i>
                                                    </div>
                                                    {!! Form::text('importe_deuda', null , ['id' => 'importe_deuda','class' => 'form-control', 'placeholder' => 'Gs.' ]) !!}
                                                 </div>
                                            </div>
                            
                                            <div class="form-group col-md-12">
                                                {!! Form::label('importe_imputado', 'Importe del pagaré imputado:') !!}
                                                <div class="input-group">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-money"></i>
                                                    </div>
                                                    {!! Form::text('importe_imputado', null , ['id' => 'importe_imputado','class' => 'form-control', 'placeholder' => 'Gs.' ]) !!}
                                                 </div>
                                            </div>
                                            
                                            <div class="form-group col-md-12">
                                                {!! Form::label('nro_contrato', 'Número del contrato') !!}
                                                <div class="input-group">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-keyboard-o"></i>
                                                    </div>
                                                    {!! Form::text('nro_contrato', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el número del contrato.' ]) !!}
                                                 </div>
                                            </div>
                            
                                            <div class="form-group col-md-12">
                                                {!! Form::label('recepcionado', 'Recepcionado por:') !!}
                                                <div class="input-group">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-info-circle"></i>
                                                    </div>
                                                    {!! Form::text('recepcionado', null , ['class' => 'form-control', 'placeholder' => 'Ingrese quien recepcionó el pagaré.' ]) !!}
                                                 </div>
                                            </div>
                            
                                           

                                        </div>
                                    </div>
                                </div>
                                @include('atm_baja.info')
                            </div>      

                            <div class="clearfix"></div>
                            {{-- @include('partials._date_picker') --}}
                            
                            
                            <div class="form-row">
                                <a class="btn btn-default"  href="{{ url('atm/new/'.$grupo->id.'/'.$grupo->id.'/remision') }}" role="button">Cancelar</a>
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
         var separadorPol = document.getElementById('importe');
        separadorPol.addEventListener('input', (e) => {
            var entradaPol = e.target.value.split(','),
            parteEnteraPol = entradaPol[0].replace(/\./g, ''),
            salidaPol = parteEnteraPol.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
            e.target.value = salidaPol;
        }, false);
        var importe = document.getElementById('importe').value;
        entryPoliza = importe.split(',');
        partEnteraPoliza = entryPoliza[0].replace(/\./g, ''),
        outputPoliza = partEnteraPoliza.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
        //insertar valor con separadores de miles
        document.getElementById("importe").value = outputPoliza;

        //separador de miles - Capital de la poliza
        var separadorPol = document.getElementById('importe_deuda');
        separadorPol.addEventListener('input', (e) => {
            var entradaPol = e.target.value.split(','),
            parteEnteraPol = entradaPol[0].replace(/\./g, ''),
            salidaPol = parteEnteraPol.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
            e.target.value = salidaPol;
        }, false);
        var importe_deuda = document.getElementById('importe_deuda').value;
        entryPoliza = importe_deuda.split(',');
        partEnteraPoliza = entryPoliza[0].replace(/\./g, ''),
        outputPoliza = partEnteraPoliza.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
        //insertar valor con separadores de miles
        document.getElementById("importe_deuda").value = outputPoliza;

         //separador de miles - Capital de la poliza
         var separadorPol = document.getElementById('importe_imputado');
        separadorPol.addEventListener('input', (e) => {
            var entradaPol = e.target.value.split(','),
            parteEnteraPol = entradaPol[0].replace(/\./g, ''),
            salidaPol = parteEnteraPol.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
            e.target.value = salidaPol;
        }, false);
        var importe_imputado = document.getElementById('importe_imputado').value;
        entryPoliza = importe_imputado.split(',');
        partEnteraPoliza = entryPoliza[0].replace(/\./g, ''),
        outputPoliza = partEnteraPoliza.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
        //insertar valor con separadores de miles
        document.getElementById("importe_imputado").value = outputPoliza;
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
    $('#editarRemision-form').validate({
        rules: {
            "numero": {
                required: true,
            },
            "titular_deudor": {
                required: true,
            },
            "fecha": {
                required: true,
            },
            "importe": {
                required: true,
            },
            "importe_deuda": {
                required: true,
            },
            "importe_imputado": {
                required: true,
            },
            "nro_contrato": {
                required: true,
            },
            "recepcionado": {
                required: true,
            },
        },
        messages: {
            "numero": {
                required: "Ingrese una númeracion interna.",
            },
            "importe": {
                required: "Ingrese el importe de la remisión.",
            },
            "importe_deuda": {
                required: "Ingrese el importe de la deuda del pagaré.",
            },
            "importe_imputado": {
                required: "Ingrese el importe del pagaré imputado.",
            },
            "nro_contrato": {
                required: "Ingrese el numero de contrato.",
            },
            "recepcionado": {
                required: "Ingrese el nombre de quien recepcionó el pagaré.",
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
            height: 550px;
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
            height: 550px;
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