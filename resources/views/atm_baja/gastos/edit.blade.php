@extends('layout')

@section('title')
    BAJA | Modificar Gasto administrativo
@endsection
@section('content')

    <section class="content-header">
        <h1>
            GESTIÓN JUDICIAL |
            <small>Modificación gasto admnistrativo</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Gestor de terminales</a></li>
            <li><a href="#">Baja</a></li>
            <li><a href="#">Gasto administrativo</a></li>
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
                        {!! Form::model($gasto, ['route' => ['gastos_administrativo.update', $gasto->id ] , 'method' => 'PUT', 'id' => 'editarGasto-form']) !!}
                            <div class="form-row">
                                <div class="form-group col-md-6 borderd-campaing">
                                    <div class="title"><h4>&nbsp;<i class="fa fa-file-text-o"></i>&nbsp; GASTO ADMINISTRATIVO &nbsp;</h4></div>
                                    <div class="container-campaing">
                            
                                        <div class="form-group col-md-6">
                                            {!! Form::label('numero', 'Numeración interna:') !!}
                                            <div class="input-group">
                                                <div class="input-group-addon">
                                                    <i class="fa fa-money"></i>
                                                </div>
                                                {!! Form::text('numero', null , ['class' => 'form-control','readonly'=>'readonly']) !!}
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
                                            {!! Form::label('proveedor', 'Proveedor:') !!}
                                            <div class="input-group">
                                                <div class="input-group-addon">
                                                    <i class="fa fa-money"></i>
                                                </div>
                                                {!! Form::text('proveedor', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el nombre del proveedor.' ]) !!}
                                             </div>
                                        </div>
                                    
                                        <div class="form-group col-md-6">
                                            {!! Form::label('monto', 'Monto total:') !!}
                                            <div class="input-group">
                                                <div class="input-group-addon">
                                                    <i class="fa fa-money"></i>
                                                </div>
                                                {!! Form::text('monto', null , ['id' => 'monto','class' => 'form-control', 'placeholder' => 'Gs.' ]) !!}
                                             </div>
                                        </div>
                                                                
                                        <div class="form-group col-md-6">
                                            {!! Form::label('interno', 'Interno:') !!}
                                            <div class="input-group">
                                                <div class="input-group-addon">
                                                    <i class="fa fa-keyboard-o"></i>
                                                </div>
                                                {!! Form::text('interno', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el interno.' ]) !!}
                                             </div>
                                        </div>
                        
                                        <div class="form-group col-md-12">
                                            <div class="form-group">
                                                {!! Form::label('comentario', 'Comentario:') !!}
                                                <div class="input-group">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-comments"></i>
                                                    </div>
                                                    <textarea rows="6" cols="30" class="form-control" id="comentario" name="comentario" placeholder="Agregar un comentario" value="">{{$gasto->comentario}}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                           

                                    </div>
                                </div>
                                @include('atm_baja.info')
                            </div>      

                            <div class="clearfix"></div>
                            <div class="form-row">
                                <a class="btn btn-default"  href="{{ url('atm/new/'.$grupo->id.'/'.$grupo->id.'/gasto_administrativo') }}" role="button">Cancelar</a>
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
    $('#editarGasto-form').validate({
        rules: {
            "numero": {
                required: true,
            },
            "proveedor": {
                required: true,
            },
            "fecha": {
                required: true,
            },
            "interno": {
                required: true,
            },
            "monto": {
                required: true,
            }
        },
        messages: {
            "numero": {
                required: "Ingrese una númeracion interna.",
            },
            "proveedor": {
                required: "Ingrese nombre del proveedor.",
            },
            "fecha": {
                required: "Seleccione una fecha.",
            },
            "interno": {
                required: "Ingrese el interno del gasto.",
            },
            "monto": {
                required: "Ingrese el monto.",
            }
        },
        errorPlacement: function (error, element) {
            error.appendTo(element.parent());
        }
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