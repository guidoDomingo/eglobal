@extends('layout')

@section('title')
    BAJA | Modificar Pagaré
@endsection
@section('content')

    <section class="content-header">
        <h1>
            Elaboración de documentaciones
            <small>Modificación de pagaré</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Gestor de terminales</a></li>
            <li><a href="#">Baja</a></li>
            <li><a href="#">Documentaciones</a></li>
            <li><a href="#">Pagaré</a></li>
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
                        {!! Form::model($pagare, ['route' => ['pagares.update', $pagare->id ] , 'method' => 'PUT', 'id' => 'editarPagare-form']) !!}
                            <div class="form-row">
                                <div class="form-group col-md-6 borderd-campaing">
                                    <div class="title"><h4>&nbsp;<i class="fa fa-file-text-o"></i>&nbsp; PAGARÉ &nbsp;</h4></div>
                                    <div class="container-campaing">
                            
                                        <div class="form-row">
                            
                                            <div class="form-group col-md-6">
                                                {!! Form::label('tipo', 'Tipo de pagaré') !!}
                                                <br>
                                                @if ($pagare->tipo == 1)
                                                    {!! Form::radio('tipo', 1, true) !!}
                                                    {!! Form::label('tipo', 'Único') !!}
                                                    &nbsp;  &nbsp; &nbsp;&nbsp;
                                                    {!! Form::radio('tipo', 2) !!}
                                                    {!! Form::label('tipo', 'Financiado') !!}
                                                @else
                                                    {!! Form::radio('tipo', 1) !!}
                                                    {!! Form::label('tipo', 'Único') !!}
                                                    &nbsp;  &nbsp; &nbsp;&nbsp;
                                                    {!! Form::radio('tipo', 2,true) !!}
                                                    {!! Form::label('tipo', 'Financiado') !!}
                                                @endif
                                                   
                                            </div>
                            
                                            <div class="form-group col-md-6">
                                                {!! Form::label('numero', 'Número interno') !!}
                                                <div class="input-group">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-pencil-square-o"></i>
                                                    </div>
                                                    {!! Form::text('numero', null , ['class' => 'form-control', 'readonly' => 'readonly']) !!}
                                                </div>
                                            </div>

                                            <div class="form-group col-md-12">
                                                {!! Form::label('firmante', 'Titular firmante:') !!}
                                                <div class="input-group">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-user"></i>
                                                    </div>
                                                    {!! Form::text('firmante', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el titular firmante.' ]) !!}
                                                 </div>
                                            </div>
                                        
                                            <div class="form-group col-md-12">
                                                {!! Form::label('monto', 'Monto') !!}
                                                <div class="input-group">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-money"></i>
                                                    </div>
                                                    {!! Form::text('monto', null , ['id' => 'monto_pagare','class' => 'form-control', 'placeholder' => 'Gs.' ]) !!}
                                                </div>
                                            </div>
                                            
                                            <div class="form-group col-md-12">
                                                {!! Form::label('cantidad_pagos', 'Cantidad de pagos') !!}
                                                <div class="input-group">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-keyboard-o"></i>
                                                    </div>
                                                    {!! Form::text('cantidad_pagos', null , ['class' => 'form-control', 'placeholder' => 'Ingrese la cantidad de pagos pagaré' ]) !!}
                                                </div>
                                            </div>
                            
                                            <div class="form-group col-md-12">
                                                {!! Form::label('tasa_interes', 'Tasa de interes %') !!}
                                                <div class="input-group">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-info-circle"></i>
                                                    </div>
                                                    {!! Form::text('tasa_interes', null , ['class' => 'form-control', 'placeholder' => 'Ingrese la tasa de interes' ]) !!}
                                                </div>
                                            </div>
                            
                                            <div class="form-group col-md-12">
                                                {!! Form::label('vencimiento', 'Vencimiento:') !!}
                                                <div class="input-group">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-calendar"></i>
                                                    </div>
                                                    {!! Form::text('vencimiento', null , ['class' => 'form-control', 'data-inputmask-alias' =>'date', 'data-inputmask-inputformat'=> 'dd/mm/yyyy', 'im-insert' => 'false','placeholder'=> 'dd/mm/yyyy', 'id' =>'vencimiento' ]) !!}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @include('atm_baja.info')
                            </div>      
                            {{-- {!! Form::hidden('atm_id', $atm_id) !!} --}}

                            <div class="clearfix"></div>
                            {{-- @include('partials._date_picker') --}}
                            
                            <div class="form-row">
                                <a class="btn btn-default"  href="{{ url('atm/new/'.$grupo->id.'/'.$grupo->id.'/pagare') }}" role="button">Cancelar</a>
                                <button type="submit" class="btn btn-primary">Actualizar</button>
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
         var separadorPol = document.getElementById('monto_pagare');

        separadorPol.addEventListener('input', (e) => {
            var entradaPol = e.target.value.split(','),
            parteEnteraPol = entradaPol[0].replace(/\./g, ''),
            salidaPol = parteEnteraPol.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
            e.target.value = salidaPol;
        }, false);

        var monto_pagare = document.getElementById('monto_pagare').value;
        entryPoliza = monto_pagare.split(',');
        partEnteraPoliza = entryPoliza[0].replace(/\./g, ''),
        outputPoliza = partEnteraPoliza.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
        //insertar valor con separadores de miles
        document.getElementById("monto_pagare").value = outputPoliza;
        
    });
</script>    
<script type="text/javascript">

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
    $('.select2').select2();
   

    $('#vencimiento').datepicker({
        language: 'es',
        format: 'dd/mm/yyyy',
    });
    //validacion formulario 
    $('#editarPagare-form').validate({
        rules: {
            "numero": {
                required: true,
            },
            "firmante": {
                required: true,
            },
            "monto": {
                required: true,
            },
            "cantidad_pagos": {
                required: true,
            },
            "tasa_interes": {
                required: true,
            },
            "vencimiento": {
                required: true,
            },
        },
        messages: {
            "numero": {
                required: "Ingrese una númeracion interna.",
            },
            "firmante": {
                required: "Ingrese nombre del titular del pagaré.",
            },
            "monto": {
                required: "Ingrese el monto del pagaré.",
            },
            "cantidad_pagos": {
                required: "Ingrese la cantidad de pagos.",
            },
            "tasa_interes": {
                required: "Ingrese la tasa de interes del pagaré.",
            },
            "vencimiento": {
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