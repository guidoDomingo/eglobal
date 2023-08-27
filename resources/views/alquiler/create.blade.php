@extends('layout')

@section('title')
    Nuevo Alquiler Miniterminal
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Alquiler
            <small>Creación de Alquiler de Miniterminales</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Alquiler</a></li>
            <li class="active">agregar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Nuevo Alquiler</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._flashes')
                        @include('partials._messages')
                        {!! Form::open(['route' => 'alquiler.store' , 'method' => 'POST', 'role' => 'form']) !!}
                        @include('alquiler.partials.fields')
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('page_scripts')
    <script src="/js/filepond/filepond-plugin-image-preview.js"></script>
    <script src="/js/filepond/filepond-plugin-image-exif-orientation.js"></script>
    <script src="/js/filepond/filepond-plugin-file-validate-size.js"></script>
    <script src="/js/filepond/filepond-plugin-file-encode.js"></script>
    <script src="/js/filepond/filepond.min.js"></script>
    <script src="/js/filepond/filepond.jquery.js"></script>
    <script src="/bower_components/admin-lte/plugins/jquery-validation/dist/jquery.validate.min.js"></script>
    <script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>
    <script src="/bower_components/admin-lte/plugins/pnotify/pnotify.custom.min.js" charset="UTF-8"></script>
    <script src="/js/bootstrap-tagsinput.js"></script>

    <!-- date-range-picker -->
    <link href="/bower_components/admin-lte/plugins/datepicker/datepicker3.css" rel="stylesheet" type="text/css" />
    <script src="/bower_components/admin-lte/plugins/daterangepicker/moment.min.js"></script>

    <!-- bootstrap datepicker -->
    <script src="/bower_components/admin-lte/plugins/datepicker/bootstrap-datepicker.js"></script>
    <script src="/bower_components/admin-lte/plugins/datepicker/locales/bootstrap-datepicker.es.js" charset="UTF-8"></script>

@endsection    

@section('page_scripts')
    @include('partials._selectize')
    <script>
        $('.select2').select2();

        //Date range picker
        $('#last_update').datepicker({
            language: 'es',
            format: 'yyyy-mm-dd 00:00:00',
        });

        // validacion del modal de nueva red
        $('#nuevogroup-form').validate({
            rules: {
                "description": {
                    required: true,
                },
                "ruc": {
                    required: true
                }
            },
            messages: {
                "description": {
                    required: "Ingrese un Nombre de Grupo",
                },
                "ruc": {
                    required: "Ingrese un RUC"
                }
            },
            errorPlacement: function (error, element) {
                error.appendTo(element.parent());
            },
            submitHandler: function(form) {
                $('#load').toggleClass('active');
                $(form).find('input[type="text"]').prop('readonly',true)
                $.post(form.action, $(form).serialize()).done(function(respuesta){
                    $('#load').toggleClass('active');
                    $(form).find('input[name="name"]').val('').prop('readonly',false);
                    if(respuesta.tipo == 'error'){
                        var myStack = {"dir1":"down", "dir2":"right", "push":"top"};
                        $(form).find('input[type="text"]').prop('readonly',false)
                        return new PNotify({
                            title: "Atención",
                            text: respuesta.mensaje,
                            addclass: "stack-custom",
                            stack: myStack,
                            type: respuesta.tipo
                        });
                    }
                    $('#modalNuevoGrupo').modal('hide');
                    var newOption = new Option(respuesta.data.description, respuesta.data.id, false, true);
                    console.log(newOption);
                    $('#group_id').append(newOption).trigger('change');
                });
            }
        });

        $('#cbox1').on('change',function(){

            if ($(this).is(':checked')) {
                $("#serial").prop('disabled', true);
            }else{
                $("#serial").prop('disabled', false);
            }
        });

        /*$('#serial').on('change',function(){

            var n = 350000;
            var count = $('#serial option:selected').length;
            var multi= n*count;

            $('#amount').val(multi);  
            $('#amount').trigger('change.select2');
        });*/

        $('#num_cuota').on('change',function(){

            var monto= $('#amount').val() * $(this).val();
            $('#cant_cuotas').text("Monto total en " + $(this).val() + " meses: " + parseInt(monto).toLocaleString('es-CL') + " Gs." );
            
        });

        $('#managers').change(function(){                
            if(this.value == 0)
            {
                $("#block_form").css("display", "block");                    
            }else
            {
                $("#block_form").css("display", "none");                    
            } 
        });

    </script>
@append

@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="https://unpkg.com/filepond/dist/filepond.css" rel="stylesheet">
    <link href="https://unpkg.com/filepond-plugin-image-preview/dist/filepond-plugin-image-preview.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="/css/bootstrap-tagsinput.css">
    <link href="/bower_components/admin-lte/plugins/pnotify/pnotify.custom.min.css" rel="stylesheet" type="text/css" />
    <style type="text/css">

        .bootstrap-tagsinput {
            background-color: #fff;
            border: 1px solid #ccc;
            box-shadow: inset 0 1px 1px rgba(0, 0, 0, 0.075);
            display: block;
            padding: 4px 6px;
            color: #555;
            vertical-align: middle;
            border-radius: 4px;
            max-width: 100%;
            line-height: 22px;
            cursor: text;
        }
        .bootstrap-tagsinput input {
            border: none;
            box-shadow: none;
            outline: none;
            background-color: transparent;
            padding: 0 6px;
            margin: 0;
            width: auto;
            max-width: inherit;
        }
        .selector-serialnumber {
            color: white;
            background-color: #3d8dbc;
            border: 1px solid #aaa;
            border-radius: 4px;
            cursor: default;
            float: left;
            padding: 0 5px;
        }

        /* Optional theme */

        /*@import url('//netdna.bootstrapcdn.com/bootstrap/3.0.0/css/bootstrap-theme.min.css');*/
        .stepwizard-step p {
            margin-top: 0px;
            color:#666;
        }
        .stepwizard-row {
            display: table-row;
        }
        .stepwizard {
            display: table;
            width: 100%;
            position: relative;
        }
        .stepwizard-step button[disabled] {
            /*opacity: 1 !important;
            filter: alpha(opacity=100) !important;*/
        }
        .stepwizard .btn.disabled, .stepwizard .btn[disabled], .stepwizard fieldset[disabled] .btn {
            opacity:1 !important;
            color:#bbb;
        }
        .stepwizard-row:before {
            top: 14px;
            bottom: 0;
            position: absolute;
            content:" ";
            width: 100%;
            height: 1px;
            background-color: #ccc;
            z-index: 0;
        }
        .stepwizard-step {
            display: table-cell;
            text-align: center;
            position: relative;
        }
        .btn-circle {
            width: 30px;
            height: 30px;
            text-align: center;
            padding: 6px 0;
            font-size: 12px;
            line-height: 1.428571429;
            border-radius: 15px;
        }

        /* animacion del boton al guardar */
        .spinner {
          display: inline-block;
          opacity: 0;
          width: 0;

          -webkit-transition: opacity 0.25s, width 0.25s;
          -moz-transition: opacity 0.25s, width 0.25s;
          -o-transition: opacity 0.25s, width 0.25s;
          transition: opacity 0.25s, width 0.25s;
        }

        .has-spinner.active {
          cursor:progress;
        }

        .has-spinner.active .spinner {
          opacity: 1;
          width: auto; /* This doesn't work, just fix for unkown width elements */
        }

        .has-spinner.btn-mini.active .spinner {
            width: 10px;
        }

        .has-spinner.btn-small.active .spinner {
            width: 13px;
        }

        .has-spinner.btn.active .spinner {
            width: 16px;
        }

        .has-spinner.btn-large.active .spinner {
            width: 19px;
        }
    </style>
@endsection
@include('alquiler.partials.modal_group')