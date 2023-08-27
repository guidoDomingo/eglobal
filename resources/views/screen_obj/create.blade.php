@extends('layout')

@section('title')
    Nuevo Objeto
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Objetos de Pantalla
            <small>Agregar Objeto</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Pantallas</a></li>
            <li><a href="#">Nombre</a></li>
            <li class="active">Agregar Objeto</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Nuevo Objeto</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._messages')
                        {!! Form::open(['route' => 'screens_objects.store' , 'id' =>  'object_form','method' => 'POST', 'files'=>true]) !!}
                        @include('screen_obj.partials.fields')
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        {!! Form::close() !!}
                    </div>
                </div>

            </div>
        </div>
    </section>
@endsection
@section('page_scripts')
    <script src="{{ asset('/bower_components/admin-lte/plugins/ckeditor_full/ckeditor.js') }}"></script>
    <script type="text/javascript">
        $(document).ready(function () {
            function getObjectTypeProperties(object_id){
                if(object_id !== ""){
                    $.ajax({
                        url: '{{ route("object_types.properties","") }}'+'/'+object_id,
                        /*url: '/admin/object-properties/'+object_id,*/
                        type: 'GET',
                        dataType: 'json',
                        success: populateForm
                    });
                }
            }
            var id = $('.object-type').val();
            getObjectTypeProperties(id);

            $('.object-type').change(function(e){
                e.preventDefault();
                var id = $(this).val();
                getObjectTypeProperties(id);
            });
            function populateForm(objects){
                $("#properties_container").html("");
                $.each(objects, function(idx, obj) {
                    var div = document.createElement("div");
                    div.className = "form-group";
                    var newLabel = document.createElement("label");
                    var newInput = document.createElement("input");
                    newInput.type=obj.html_input_type;
                    newInput.name=obj.key;
                    newInput.id=obj.key;
                    if(obj.html_input_type !== 'file' && obj.html_input_type !== 'checkbox' ){
                        newInput.className="form-control";
                    }else if(obj.html_input_type === 'checkbox'){
                        newInput.className="checkbox";
                    }
                    newLabel.htmlFor = obj.key;
                    newLabel.innerHTML = obj.name;
                    div.appendChild(newLabel);
                    div.appendChild(newInput);
                    $("#properties_container").append(div);
                    if(obj.html_input_type == 'textarea'){
                        CKEDITOR.replace( 'html' );
                    }
                });
            }
        });

        $( "#object_form" ).submit(function() {
            var content = CKEDITOR.instances['html'].getData();
            $('.hdn_html').val(content);
        });

    </script>
@endsection
