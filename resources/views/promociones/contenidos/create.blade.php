@extends('layout')

@section('title')
    Nuevo Contenido
@endsection
@section('content')

    <section class="content-header">
        <h1>
            Contenidos
            <small>Creación de nuevo contenido</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Promociones</a></li>
            <li><a href="#">Contenidos</a></li>
            <li class="active">agregar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <small style="color: red"><strong>Campos obligatorios (*)</strong></small>
                    </div>
                    <div class="box-body">
                        {{-- @include('partials._flashes') --}}
                        @include('partials._messages')
                        {!! Form::open(['route' => 'contents.store' , 'method' => 'POST', 'role' => 'form', 'id' => 'nuevoContenido-form']) !!}
                        @include('promociones.contenidos.partials.fields')
                        {{-- <button type="submit" class="btn btn-primary">Guardar</button> --}}
                    
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('js')

<!-- add before </body> -->
<script src="/js/filepond/filepond-plugin-image-preview.js"></script>
<script src="/js/filepond/filepond-plugin-image-exif-orientation.js"></script>
<script src="/js/filepond/filepond-plugin-file-validate-size.js"></script>
<script src="/js/filepond/filepond-plugin-file-encode.js"></script>
<script src="/js/filepond/filepond.min.js"></script>
<script src="/js/filepond/filepond.jquery.js"></script>
<script src="/bower_components/admin-lte/plugins/pnotify/pnotify.custom.min.js" charset="UTF-8"></script>

<script src="/bower_components/admin-lte/plugins/jquery-validation/dist/jquery.validate.min.js"></script>
<script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>
<!-- bootstrap time picker -->
<script src="/bower_components/admin-lte/plugins/timepicker/bootstrap-timepicker.min.js"></script>
<script type="text/javascript">
    $('.select2').select2();

    
        // //separador de miles - limite de credito | Contratos
        // var separador = document.getElementById('precionormal');
        // separador.addEventListener('input', (e) => {
        //     var entrada = e.target.value.split(','),
        //     parteEntera = entrada[0].replace(/\./g, ''),
        //     salida = parteEntera.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
        //     e.target.value = salida;
        // }, true);

        // var precionormal = document.getElementById('precionormal').value;
        // entry = precionormal.split(',');
        // partEntera = entry[0].replace(/\./g, ''),
        // output = partEntera.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
        // console.log(output);
        // //insertar valor con separadores de miles
        // document.getElementById("precionormal").value = output;


    //validacion formulario 
    $('#nuevoContenido-form').validate({
        rules: {
            "name": {
                required: true,
            },
            "description": {
                required: true,
            },
            "image": {
                required: true,
            },
            "price": {
                required: true,
            },
            "precionormal": {
                required: true,
            },
            "porcentajedescuento": {
                required: true,
            },
            "categoria_id": {
                required: true,
            },
        },
        messages: {
            "name": {
                required: "Ingrese el nombre del contenido.",
            },
            "description": {
                required: "Ingrese una descripción del contenido.",
            },
            "image": {
                required: "Ingrese una url para la imagen asociada.",
            },
            "price": {
                required: "Ingrese el precio del contenido.",
            },
            "precionormal": {
                required: "Ingrese el precio normal.",
            },
            "porcentajedescuento": {
                required: "Ingrese el porcentaje de descuento.",
            },
            "categoria_id": {
                required: "Seleccione una categoria",
            },
        },
        errorPlacement: function (error, element) {
            error.appendTo(element.parent());
        }
    });

      // Validaciones del modal de nuevo departamento
      $('#nuevaCategoria-form').validate({
            rules: {
                "name": {
                    required: true,
                }
            },
            messages: {
                "name": {
                    required: "Ingrese un nombre",
                }
            },
            errorPlacement: function(error, element) {
                error.appendTo(element.parent());
            },
            submitHandler: function(form) {
                $('#btnCategoria').toggleClass('active');
                $(form).find('input[name="name"]').prop('readonly', true)
                $.post(form.action, $(form).serialize()).done(function(respuesta) {
                    $('#btnCategoria').toggleClass('active');
                    $(form).find('input[name="name"]').val('').prop(
                        'readonly', false);
                    if (respuesta.tipo == 'error') {
                        var myStack = {
                            "dir1": "down",
                            "dir2": "right",
                            "push": "top"
                        };
                        return new PNotify({
                            title: "Atención",
                            text: respuesta.mensaje,
                            addclass: "stack-custom",
                            stack: myStack,
                            type: respuesta.tipo
                        });
                    }
                    $('#modalNuevaCategoria').modal('hide');
                    var newOption = new Option(respuesta.data.name, respuesta.data.id, false, true);
                    $('#categoria_id').append(newOption).trigger('change');
                });
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
        allowMultiple: false
    });

     //Timepicker
     $('.timepicker').timepicker({
      showInputs: false,
      //format: 'hh:mm:ss',  
      minuteStep: 10
    })
</script>
@if (session('error') == 'ok')
<script>
    swal({
            type: "error",
            title: 'Ocurrió un error al intentar registrar el contenido. Verifique los campos',
            showConfirmButton: true,
            // timer: 1500
            });
</script>
@endif
@endsection

@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="/css/filepond/filepond.css" rel="stylesheet">
    <link href="/css/filepond/filepond-plugin-image-preview.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

  <link rel="stylesheet" href="/bower_components/admin-lte/plugins/timepicker/bootstrap-timepicker.min.css">

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
@include('promociones.contenidos.partials.modal_categoria')
