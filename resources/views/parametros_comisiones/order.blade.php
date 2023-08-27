@extends('layout')

@section('title')
    Ordenar Marcas
@endsection
@section('content')

    <section class="content-header">
        <h1>
            Marcas
            <small>Ordenar Marca</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Marcas</a></li>
            <li class="active">Ordenar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Ordenar</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._flashes')
                        @include('partials._messages')
                        {!! Form::open(['route' => 'marca.order' , 'method' => 'POST', 'role' => 'form', 'id' => 'nuevaMarca-form']) !!}
                        <div class="form-group">
                            {!! Form::label('categoria_id', 'Categoria') !!}
                            {!! Form::select('categoria_id', $categorias, null, ['id' => 'categoria_id', 'class' => 'form-control select2', 'placeholder' => 'Seleccione una opción']) !!}
                        </div>
                        <div class="form-group">
                            <label>Marcas</label>
                            <div class="row">
                                <div class="col-md-12" id="sortable">
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar</button>
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
<script src="/js/toastr.min.js"></script>
<script type="text/javascript">
    $('.select2').select2();

    //validacion formulario 
    $('#nuevaMarca-form').validate({
        rules: {
            "descripcion": {
                required: true,
            },
            "categoria_id": {
                required: true,
            },
            "service_source_id": {
                required: true,
            },
        },
        messages: {
            "descripcion": {
                required: "Ingrese la descripción",
            },
            "categoria_id": {
                required: "Seleccione la categoria",
            },
            "service_source_id": {
                required: "Seleccione el service source",
            },
        },
        errorPlacement: function (error, element) {
            error.appendTo(element.parent());
        },
        submitHandler: function(form){
            var orden = $("#sortable").sortable('serialize');

            $.ajax({
                data: {
                    orden,
                    categoria_id: $('#categoria_id').val(),
                    _token: token,
                },
                type: 'POST',
                url: '{{ route("marca.order") }}'
            }).done(function(response){
                if(response.error){
                    toastr.error('Ha ocurrido un error')
                }else{
                    toastr.success('Los cambios fueron aplicados exitosamente')
                }
            });

            return false;
        }
    });

    $("#sortable").sortable();
    $("#sortable").disableSelection();

    $(document).on('change', '#categoria_id', function(){
        var ordenable  = '';
        $.get("{{ route('marca.get_by_category') }}", {categoria_id: $(this).val()}).done(function(response){
            $("#sortable").html(response);
        });
    });

</script>
@endsection
@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
    <link href="/css/toastr.min.css" rel="stylesheet" type="text/css" />

    <link href="/css/jquery-ui.css" rel="stylesheet">
    <style type="text/css">
        #sortable { 
            list-style-type: none; 
            margin: 0; 
            /*padding: 0; */
            width: 100%; 
        }

        #sortable .ventana-2 { 
            margin: 3px 3px 3px 0; 
            padding: 1px; 
            float: left;
            width: 100%;
            height: 90px;
            /*font-size: 4em; */
            text-align: center;
            border: solid 1px #a9aaaa;
            border-radius: 4px;
            background-color: white;
        }

        #sortable .imagen_marcas_servicios {
            width: 46% !important;
            margin-left: auto;
            margin-right: auto;
            display: block;
            justify-content: center;
            position: relative;
            top: 22%;
        }

        .marca-descripcion {
            font-size: 9px;
            bottom: -19px;
            position: relative;
        }
    </style>
@endsection