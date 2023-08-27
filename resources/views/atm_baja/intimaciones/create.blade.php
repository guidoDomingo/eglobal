@extends('layout')

@section('title')
    BAJA | Intimación
@endsection
@section('content')

    <section class="content-header">
        <h1>
            Intimación |
            <small>Agregar</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Gestor de terminales</a></li>
            <li><a href="#">Baja</a></li>
            <li><a href="#">Documentaciones</a></li>
            <li><a href="#">Intimación</a></li>
            <li class="active">Agregar</li>
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
                        {!! Form::open(['route' => 'intimaciones.store' , 'method' => 'POST', 'role' => 'form', 'id' => 'nuevoIntimacion-form']) !!}
                            @include('atm_baja.intimaciones.partials.fields')
                            <div class="form-row">
                                <a class="btn btn-default"  href="{{ url('atm/new/'.$group_id.'/'.$group_id.'/intimacion') }}" role="button">Cancelar</a>
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

<script src="/bower_components/admin-lte/plugins/jquery-validation/dist/jquery.validate.min.js"></script>
<script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>

<link rel="stylesheet" href="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.css">
<script src="/bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.min.js"></script>

<!-- date-range-picker -->
<link href="/bower_components/admin-lte/plugins/datepicker/datepicker3.css" rel="stylesheet" type="text/css" />
<script src="/bower_components/admin-lte/plugins/daterangepicker/moment.min.js"></script>

<!-- bootstrap datepicker -->
<script src="/bower_components/admin-lte/plugins/datepicker/bootstrap-datepicker.js"></script>
<script src="/bower_components/admin-lte/plugins/datepicker/locales/bootstrap-datepicker.es.js" charset="UTF-8"></script>


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
    $('#fecha_envio').datepicker({
        language: 'es',
        format: 'dd/mm/yyyy',
    });
    $('#fecha_envio').datepicker("setDate", new Date());

    $('#fecha_recepcion').datepicker({
        language: 'es',
        format: 'dd/mm/yyyy',
    });
    $('#fecha_recepcion').datepicker("setDate", new Date());

    $('#fecha_vencimiento').datepicker({
        language: 'es',
        format: 'dd/mm/yyyy',
    });
    $('#fecha_vencimiento').datepicker("setDate", new Date());

    //validacion formulario 
    $('#nuevoIntimacion-form').validate({
        rules: {
            "numero": {
                required: true,
            },
            "fecha_envio": {
                required: true,
            },
            "fecha_recepcion": {
                required: true,
            },
            "fecha_vencimiento": {
                required: true,
            },
        },
        messages: {
            "numero": {
                required: "Ingrese una númeracion interna.",
            },
            "fecha_envio": {
                required: "Seleccione una fecha de envio.",
            },
            "fecha_recepcion": {
                required: "Seleccione una fecha de recepcion.",
            },
            "fecha_vencimiento": {
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