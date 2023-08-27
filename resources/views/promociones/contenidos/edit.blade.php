@extends('layout')

@section('title')
    Contenido {{ $content->name }}
@endsection
@section('content')
    <section class="content-header">
        <h1>
            {{ $content->name }}
            <small>Modificación de datos del contenido</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="{{ route('contents.index') }}">Contenidos</a></li>
            <li><a href="#">{{ $content->name }}</a></li>
            <li class="active">Modificar</li>
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
                        {!! Form::model($content, ['route' => ['contents.update', $content->id ] , 'method' => 'PUT', 'id' => 'editarContenido-form']) !!}
                            <div class="form-row">
                                <div class="form-group col-md-5">
                                    <div class="form-group">
                                        {!! Form::label('name', 'Nombre del contenido') !!}<small style="color: red"><strong>(*)</strong></small>
                                        {!! Form::text('name', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el nombre del contenido' ]) !!}
                                    </div>
                                </div>
                                <div class="form-group col-md-5">
                                    <div class="form-group">
                                        {!! Form::label('description', 'Descripción') !!}<small style="color: red"><strong>(*)</strong></small>
                                        {!! Form::text('description', null , ['class' => 'form-control', 'placeholder' => 'Ingrese la descripción' ]) !!}
                                    </div>
                                </div>
                                <div class="form-group col-md-2">
                                    <div class="form-group">
                                        {!! Form::label('provider_product_id', 'ID Proveedor') !!}<small style="color: red"><strong>(*)</strong></small>
                                        {!! Form::text('provider_product_id', null , ['class' => 'form-control', 'placeholder' => 'Código del producto' ]) !!}
                                    </div>
                                </div>
                            </div>  
                            <div class="form-row">
                                <div class="form-group col-md-3">
                                    <div class="form-group">
                                        {!! Form::label('precionormal', 'Precio') !!}<small style="color: red"><strong>(*)</strong></small>
                                        {!! Form::text('precionormal', null , ['class' => 'form-control', 'placeholder' => 'Gs.','id' => 'precionormal' ]) !!}
                                    </div>
                                </div>
                                <div class="form-group col-md-3">
                                    <div class="form-group">
                                        {!! Form::label('porcentajedescuento', 'Porcentaje de descuento (%)') !!}<small style="color: red"><strong>(*)</strong></small>
                                        {!! Form::text('porcentajedescuento', null , ['class' => 'form-control', 'placeholder' => '%' ,'id' => 'porcentajedescuento','onKeyPress' => 'return onKeyPressBlockChars(event,this.value);','onKeyUp' => 'return calculaPorcentajes(this.value);']) !!}
                                    </div>
                                </div>
                                <div class="form-group col-md-3">
                                    <div class="form-group">
                                        {!! Form::label('price', 'Precio final') !!}<small style="color: red"><strong>(*)</strong></small>
                                        {!! Form::text('price', null , ['class' => 'form-control', 'placeholder' => 'Gs.' ,'id' => 'price', 'readonly' =>'readonly']) !!}
                                    </div>
                                </div>
                                <div class="form-group col-md-3">
                                    <div class="form-group">
                                        {!! Form::label('categoria_id', 'Categoria') !!} <small style="color: red"><strong>(*)</strong></small>
                                        {!! Form::text('categories', !empty($categoriesIds) ? $categoriesIds : null, ['class' => 'form-control', 'id' => 'selectCategorias', 'placeholder' => 'Seleccione una categoria']) !!}
                                    </div>
                                </div>
                            </div>  

                            <div class="form-row">
                                <div class="form-group col-md-4">
                                    <div class="form-group">
                                        {!! Form::label('image', 'Archivo multimedia') !!} 
                                        {{-- <small>Formatos soportados : [Vídeo] <b>MP4</b> | Hasta 50 Mb  [Imágen] <b> JPG, JPEG, PNG, GIF, SVG</b> | Hasta 50 Mb</small> --}}
                                        <h5>Formatos soportados</h5>
                                        <h5>Vídeo: <b>MP4</b> | Hasta 50 Mb</h5>
                                        <h5>Imágen: <b> JPG, JPEG, PNG, GIF, SVG</b> | Hasta 50 Mb</h5>
                                    </div>
                                </div>
                                <div class="form-group col-md-8">
                                    <div class="form-group">
                                        <input type="file" class="filepond" name="image" data-max-file-size="50MB" data-max-files="1">
                                        @if(isset($content))
                                            <small style="">Nota: cargar un archivo multimedia solo en caso de querer modificar el actual</small>
                                        @endif
                                    </div>
                                </div>
                            </div>   

                            <div class="form-row">
                                <div class="form-group col-md-12">
                                    <div class="form-group">
                                        <div class="form-group col-md-3" style="margin-top: 25px;">
                                            <div class="form-group">
                                                <button type="submit" class="btn btn-primary">Guardar</button>
                                                <a class="btn btn-default" href="{{ route('contents.index') }}" role="button">Cancelar</a>
                                            </div> 
                                        </div> 
                                    </div> 
                                </div> 
                            </div>


                            {{-- <div class="form-group">
                                {!! Form::label('image', 'Imagen Asociada') !!}
                                <input type="file" class="filepond" name="image" data-max-file-size="10MB" data-max-files="3">
                                @if(isset($content))
                                    <small style="">Nota: cargar una imagen solo en caso de querer modificar la imagen actual</small>
                                @endif
                                </div> 
                                <div class="form-row">
                                <div class="form-group col-md-3">
                                    <div class="form-group">
                                        {!! Form::label('precionormal', 'Precio') !!}
                                        {!! Form::text('precionormal', null , ['class' => 'form-control', 'placeholder' => 'Gs.','id' => 'precionormal' ]) !!}
                                    </div>
                                </div>
                                <div class="form-group col-md-3">
                                    <div class="form-group">
                                        {!! Form::label('porcentajedescuento', 'Porcentaje de descuento (%)') !!}
                                        {!! Form::text('porcentajedescuento', null , ['class' => 'form-control', 'placeholder' => '%' ,'id' => 'porcentajedescuento','onKeyPress' => 'return onKeyPressBlockChars(event,this.value);','onKeyUp' => 'return calculaPorcentajes(this.value);']) !!}
                                    </div>
                                </div>
                                <div class="form-group col-md-3">
                                    <div class="form-group">
                                        {!! Form::label('price', 'Precio final') !!}
                                        {!! Form::text('price', null , ['class' => 'form-control', 'placeholder' => 'Gs.' ,'id' => 'price', 'readonly' =>'readonly']) !!}
                                    </div>
                                </div>
                                <div class="form-group col-md-3">
                                    <div class="form-group">
                                        {!! Form::label('categoria_id', 'Categoria') !!} 
                                        {!! Form::text('categories', !empty($categoriesIds) ? $categoriesIds : null, ['class' => 'form-control', 'id' => 'selectCategorias', 'placeholder' => 'Seleccione una categoria']) !!}
                                    </div>
                                </div>
                            
                            
                                <div class="form-group col-md-3" style="margin-top: 25px;">
                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary">Guardar</button>
                                        <a class="btn btn-default" href="{{ route('contents.index') }}" role="button">Cancelar</a>
                                    </div>
                                </div>
                            </div>    --}}

                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@include('partials._selectize')

@section('js')
<!-- add before </body> -->
<script src="/js/filepond/filepond-plugin-image-preview.js"></script>
<script src="/js/filepond/filepond-plugin-image-exif-orientation.js"></script>
<script src="/js/filepond/filepond-plugin-file-validate-size.js"></script>
<script src="/js/filepond/filepond-plugin-file-encode.js"></script>
<script src="/js/filepond/filepond.min.js"></script>
<script src="/js/filepond/filepond.jquery.js"></script>

<script src="/bower_components/admin-lte/plugins/jquery-validation/dist/jquery.validate.min.js"></script>
<script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>

<script type="text/javascript">
        
    function onKeyPressBlockChars(e,porcentaje){	
 
        var key = window.event ? e.keyCode : e.which;
        var keychar = String.fromCharCode(key);
        reg = /\d|\./;

        if (porcentaje.indexOf(".")!=-1 && keychar=="."){
            return false;
        }else{
            return reg.test(keychar);
        }			    
    }
    
    function calculaPorcentajes(porcentaje){
        var monto = $("input[name=precionormal]").val();
        if(porcentaje != ''){
            document.getElementById("price").value= monto - (Math.floor(porcentaje*monto)/100);
        }else{
            document.getElementById("price").value= 0;
        }
    }

</script> 
<script type="text/javascript">
    $('.select2').select2();

    $('#selectCategorias').selectize({
            delimiter: ',',
            persist: false,
            openOnFocus: true,
            valueField: 'id',
            labelField: 'descripcion',
            searchField: 'descripcion',
            render: {
                item: function(item, escape) {
                    return '<div><span class="label label-primary">' + escape(item.descripcion) + '</span></div>';
                }
            },
            options: {!! $categoriesJsonAll !!}
        });

    //validacion formulario 
    $('#editarContenido-form').validate({
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
    });
           
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
@if (session('error_categoria') == 'ok')
<script>
    swal({
            type: "error",
            title: 'Debe seleccionar una categoría.',
            showConfirmButton: true,
            // timer: 1500
            });
</script>
@endif
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