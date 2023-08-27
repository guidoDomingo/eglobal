<div class="row">
    <div class="col-md-6">
        <div class="form-group">
        {!! Form::label('departamento_id', 'Departamento') !!} <a href='#' id="nuevaDepartamento" data-toggle="modal" data-target="#modalNuevaDepartamento" class="mb-2"><small>Agregar <i class="fa fa-plus"></i></small></a>

        {!! Form::select('departamento_id', $departamentos, null , ['class' => 'form-control select2', 'placeholder' => 'Seleccione una opción' , 'style' => 'width:100%']) !!}
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
        {!! Form::label('ciudad_id', 'Ciudad') !!} <a href='#' id="nuevaDepartamento" data-toggle="modal" data-target="#modalNuevaDepartamento" class="mb-2"><small>Agregar <i class="fa fa-plus"></i></small></a>
        {!! Form::select('ciudad_id', [], null , ['class' => 'form-control select2', 'placeholder' => 'Seleccione una opción' , 'style' => 'width:100%']) !!}
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
        {!! Form::label('barrio_id', 'Barrio') !!} <a href='#' id="nuevaDepartamento" data-toggle="modal" data-target="#modalNuevaDepartamento" class="mb-2"><small>Agregar <i class="fa fa-plus"></i></small></a>
        {!! Form::select('barrio_id', [], null , ['class' => 'form-control select2', 'placeholder' => 'Seleccione una opción' , 'style' => 'width:100%']) !!}
        </div>
    </div>
</div>
<label>Ubicación. <small><i class="fto-help-circled mar_r4 fs_lg"></i> Hace clic sobre el mapa para definir la ubicacion exacta.</small></label>
<div id="map" style="width:100%;height:270px"></div>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
        {!! Form::label('latitud', 'Latitud') !!}
        {!! Form::text('latitud', null , ['class' => 'form-control', 'placeholder' => 'Latitud' ]) !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group">
        {!! Form::label('longitud', 'Longitud') !!}
        <input type="text" id="longitud" name="longitud" class="form-control" title="Solo se admite puntooooo">
        {{-- {!! Form::text('longitud', null , ['class' => 'form-control', 'placeholder' => 'Longitud']) !!} --}}
        </div>
    </div>
</div>
<script type="text/javascript">
    var eglobaltIcon = "{{ asset('map-icon-36x36.png') }}";
    function initMap() {
        var mapProp= {
            center:new google.maps.LatLng(-25.284437539757388,-57.58209245312503),
            zoom:14,
        };
        var map = new google.maps.Map(document.getElementById("map"),mapProp);
        var infoWindow = new google.maps.InfoWindow;

        var marker = new google.maps.Marker({
            position: google.maps.LatLng(-25.284437539757388,-57.58209245312503), 
            map: map,
            draggable: true,
            icon: eglobaltIcon
        });

        google.maps.event.addListener(map, 'click', function(event) {
            $("input[type='text'][name='latitud']").val(event.latLng.lat());
            $("input[type='text'][name='longitud']").val(event.latLng.lng());
            marker.setPosition(event.latLng);

        });

        google.maps.event.addListener(marker, 'drag', function(event) {
            $("input[type='text'][name='latitud']").val(event.latLng.lat());
            $("input[type='text'][name='longitud']").val(event.latLng.lng());
        });

        $(document).on('keypress','#latitud',function(e){
            if(e.which == 13) {
                if($(this).val() !== ''){
                    $('#longitud').focus();
                }
            }   
        });

        // $('#latitude').on( "keypress",function (e) {
        //     var keypress = e.keyCode || e.which || e.charCode; 
        //     var key = String.fromCharCode(keypress);
        //     var regEx = /^(\+|-)?(?:180(?:(?:\.0{1,6})?)|(?:[0-9]|[1-9][0-9]|1[0-7][0-9])(?:(?:\.[0-9]{1,6})?))$/;

        //     var txt = $(this).val() + key;
        //     if (!regEx.test(txt)) {
        //         if(keypress != 8){
        //             e.preventDefault();
        //         }else{
        //         }
        //     }else{

        //     }
        // });



        $(document).on('keyup','#longitud',function(e){
            if($(this).val() !== ''){
                marker.setPosition(new google.maps.LatLng($("#latitud").val(), $("#longitud").val()));
                newLocation($("#latitud").val(),$("#longitud").val());
            }
        });



        

        function newLocation(newLat,newLng) {
            map.setCenter({
                lat : parseFloat(newLat),
                lng : parseFloat(newLng)
            });
        }

        $(document).on('select2:select','#barrio_id',function(){
            var request = {
                query: $('#barrio_id > option:selected').text()+', '+$('#ciudad_id > option:selected').text()+', '+$('#departamento_id > option:selected').text(),
                fields: ['name', 'geometry'],
            };

            service = new google.maps.places.PlacesService(map);

            service.findPlaceFromQuery(request, function(results, status) {
                if (status === google.maps.places.PlacesServiceStatus.OK) {
                    for (var i = 0; i < results.length; i++) {
                        marker.setPosition(results[i].geometry.location);
                    }
                    map.setCenter(results[0].geometry.location);
                }
            });
        })
    }

    // function validateLatLng(lat, lng) {    
    //     let pattern = new RegExp('^-?([1-8]?[1-9]|[1-9]0)\\.{1}\\d{1,6}');
  
    //     return pattern.test(lat) && pattern.test(lng);
    // }
    //pattern="^(\+|-)?(?:180(?:(?:\.0{1,6})?)|(?:[0-9]|[1-9][0-9]|1[0-7][0-9])(?:(?:\.[0-9]{1,6})?))$" 

 


</script>

<script async defer
src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAf5MejWxz8Z42K9qKDTtvuseVxQdGC0FQ&libraries=places&callback=initMap&language=es">
</script>

