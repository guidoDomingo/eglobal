
<div class="form-row">
    <div class="form-group col-md-5">
        <div class="form-group">
            {!! Form::label('name', 'Nombre del contenido') !!}  <small style="color: red"><strong>(*)</strong></small>
            {!! Form::text('name', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el nombre del contenido' ]) !!}
        </div>
    </div>
    <div class="form-group col-md-5">
        <div class="form-group">
            {!! Form::label('description', 'Descripción') !!} <small style="color: red"><strong>(*)</strong></small>
            {!! Form::text('description', null , ['class' => 'form-control', 'placeholder' => 'Ingrese una descripción' ]) !!}
        </div>
    </div>
    <div class="form-group col-md-2">
        <div class="form-group">
            {!! Form::label('provider_product_id', 'ID Proveedor') !!} <small style="color: red"><strong>(*)</strong></small>
            {!! Form::text('provider_product_id', null , ['class' => 'form-control', 'placeholder' => 'Código del producto' ]) !!}
        </div>
    </div>
</div>  
<div class="form-row">
    <div class="form-group col-md-3">
        <div class="form-group">
            {!! Form::label('precionormal', 'Precio') !!} <small style="color: red"><strong>(*)</strong></small>
            {!! Form::text('precionormal', null , ['class' => 'form-control', 'placeholder' => 'Gs.','id' => 'precionormal' ]) !!}
        </div>
    </div>
    <div class="form-group col-md-3">
        <div class="form-group">
            {!! Form::label('porcentajedescuento', 'Porcentaje de descuento (%)') !!} <small style="color: red"><strong>(*)</strong></small>
            {!! Form::text('porcentajedescuento', null , ['class' => 'form-control', 'placeholder' => '%' ,'id' => 'porcentajedescuento','onKeyPress' => 'return onKeyPressBlockChars(event,this.value);','onKeyUp' => 'return calculaPorcentajes(this.value);']) !!}
        </div>
    </div>
    <div class="form-group col-md-3">
        <div class="form-group">
            {!! Form::label('price', 'Precio final') !!} <small style="color: red"><strong>(*)</strong></small>
            {!! Form::text('price', null , ['class' => 'form-control', 'placeholder' => 'Gs.' ,'id' => 'price', 'readonly' =>'readonly']) !!}
        </div>
    </div>
    <div class="form-group col-md-3">
        <div class="form-group">
             {!! Form::label('categoria_id', 'Categoría') !!} <small style="color: red"><strong>(*)</strong></small><a style="margin-left: 6em" href='#' id="nuevaCategoria" data-toggle="modal" data-target="#modalNuevaCategoria"><small>Agregar <i class="fa fa-plus"></i></small></a>{{--@if (\Sentinel::getUser()->inRole('promociones'))@endif --}}
            {{-- {!! Form::text('categories', !empty($categoriesIds) ? $categoriesIds : null, ['class' => 'form-control', 'id' => 'selectCategorias', 'placeholder' => 'Seleccione una categoria']) !!} --}}
            {!! Form::select('categoria_id', $categorias, null , ['class' => 'form-control select2', 'placeholder' => 'Seleccione una opción' , 'style' => 'width:100%']) !!}
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
    <div class="form-group col-md-8 borderd-content">
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
@section('page_scripts')
@include('partials._selectize')
    <script>
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
                options: {!! $categoriesJson !!}
            });
    </script>  

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
@append