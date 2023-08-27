@extends('layout')

@section('title')
BAJA | Editar Retiro de dispositivo
@endsection
@section('content')

    <section class="content-header">
        <h1>
            Logísticas |
            <small>Modificar retiro de dispositivo</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Gestor de terminales</a></li>
            <li><a href="#">Baja</a></li>
            <li><a href="#">Documentaciones</a></li>
            <li><a href="#">Retiro de dispositivo</a></li>
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
                        {!! Form::model($retiro, ['route' => ['retiro_dispositivos.update', $retiro->id ] , 'method' => 'PUT', 'id' => 'editarRetiro-form','enctype'=>'multipart/form-data']) !!}
                            <div class="form-row">
                                <div class="form-group col-md-6 borderd-campaing">
                                    <div class="title"><h4>&nbsp;<i class="fa fa-file-text-o"></i>&nbsp; RETIRO DE DISPOSITIVO &nbsp;</h4></div>
                                    <div class="container-campaing">
                            
                                        <div class="form-row">
                                            <div class="form-group col-md-6">
                                                {!! Form::label('numero', 'Número interno:') !!}
                                                <div class="input-group">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-pencil-square-o"></i>
                                                    </div>
                                                    {!! Form::text('numero', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el numero interno.' ,'readonly' => 'readonly']) !!}
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
                                                {!! Form::label('encargado', 'Quien retiro:') !!}
                                                <div class="input-group">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-pencil-square-o"></i>
                                                    </div>
                                                    {!! Form::text('encargado', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el encargado.' ]) !!}
                                                 </div>
                                            </div>
                                            
                                            <div class="form-group col-md-12">
                                                {!! Form::label('firma', 'Quien firmo:') !!}
                                                <div class="input-group">
                                                    <div class="input-group-addon">
                                                        <i class="fa fa-pencil-square-o"></i>
                                                    </div>
                                                    {!! Form::text('firma', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el firma.' ]) !!}
                                                 </div>
                                            </div>

                                            <div class="form-group col-md-12">
                                                {!! Form::label('retiro', 'Dispositivo retirado:') !!}
                                                <br>

                                                @if ($retiro->retirado == TRUE)
                                                    {!! Form::radio('retiro', 'si',true) !!}
                                                    {!! Form::label('retiro', 'Sí') !!}
                                                    &nbsp;  &nbsp; &nbsp;&nbsp;
                                                    {!! Form::radio('retiro', 'no') !!}
                                                    {!! Form::label('retiro', 'No') !!}
                                                    <br>
                                                    <small style="color:red"><b>Nota:</b> Al marcar "Sí", esto suspenderá la generación de las cuotas de alquiler.</small>
                                                    <br>
                                                    <small style="color:red"><b>Nota:</b> Al marcar "No", la generación de las cuotas de alquiler seguirán activas.</small>
                                                @else
                                                    {!! Form::radio('retiro', 'si') !!}
                                                    {!! Form::label('retiro', 'Sí') !!}
                                                    &nbsp;  &nbsp; &nbsp;&nbsp;
                                                    {!! Form::radio('retiro', 'no',true) !!}
                                                    {!! Form::label('retiro', 'No') !!}
                                                    <br>
                                                    <small style="color:red"><b>Nota:</b> Al marcar "Sí", esto suspenderá la generación de las cuotas de alquiler.</small>
                                                    <br>
                                                    <small style="color:red"><b>Nota:</b> Al marcar "No", la generación de las cuotas de alquiler seguirán activas.</small>
                                                @endif

                                                    
                                            </div>
                            
                                            <div class="form-group col-md-6">
                                                {!! Form::label('imagen', 'Adjuntar comprobante de retiro:') !!}
                                                <input type="file" class="filepond"  name="imagen" data-max-file-size="3MB" data-max-files="3">
                                                @if(isset($retiro->imagen) && $retiro->imagen != '')
                                                <small style="">Nota: cargar un archivo multimedia solo en caso de querer modificar el actual</small>
                                                @endif
                                            </div>
                            
                                            <div class="form-group col-md-6">
                                                <div class="form-group">
                                                    {!! Form::label('comentario', 'Comentario:') !!}
                                                    <div class="input-group">
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-comments"></i>
                                                        </div>
                                                        <textarea rows="10" cols="30" class="form-control" id="comentario" name="comentario" placeholder="Agregar un comentario">{!!$retiro->comentario!!}</textarea>
                                                    </div>
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
                                <a class="btn btn-default"  href="{{ url('atm/new/'.$grupo->id.'/'.$grupo->id.'/retiro_dispositivo') }}" role="button">Cancelar</a>
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
<script src="/js/filepond/filepond-plugin-image-preview.js"></script>
<script src="/js/filepond/filepond-plugin-image-exif-orientation.js"></script>
<script src="/js/filepond/filepond-plugin-file-validate-size.js"></script>
<script src="/js/filepond/filepond-plugin-file-encode.js"></script>
<script src="/js/filepond/filepond.min.js"></script>
<script src="/js/filepond/filepond.jquery.js"></script>

<script src="/bower_components/admin-lte/plugins/datepicker/bootstrap-datepicker.js"></script>
<script src="/bower_components/admin-lte/plugins/datepicker/locales/bootstrap-datepicker.es.js" charset="UTF-8"></script>
<script src="/bower_components/admin-lte/plugins/jquery-validation/dist/jquery.validate.min.js"></script>
<script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>

<link rel="stylesheet" href="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.css">
<script src="/bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.min.js"></script>
  
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

    $('#fecha').datepicker({
        language: 'es',
        format: 'dd/mm/yyyy'
    });
    //validacion formulario 
    $('#editarRetiro-form').validate({
        rules: {
            "numero": {
                required: true,
            },
            "fecha": {
                required: true,
            },
            "encargado": {
                required: true,
            },
        },
        messages: {
            "numero": {
                required: "Ingrese una númeracion interna.",
            },
            "fec": {
                required: "Ingrese el monto del pagaré.",
            },
            "encargado": {
                required: "Ingrese el nombre del encargado.",
            },
            "fecha": {
                required: "Seleccione una fecha de retiro.",
            },
        },
        errorPlacement: function (error, element) {
            error.appendTo(element.parent());
        }
    });

    // Turn input element into a pond
    FilePond.registerPlugin(
            FilePondPluginFileEncode,
            FilePondPluginImagePreview,
            FilePondPluginImageExifOrientation,
            FilePondPluginFileValidateSize
        );

        $('.filepond').filepond({
            allowMultiple: false,
            imagePreviewHeight: 210,
        });
   
</script>
@endsection

@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet">
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet">
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
            height: 630px;
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
            height: 630px;
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