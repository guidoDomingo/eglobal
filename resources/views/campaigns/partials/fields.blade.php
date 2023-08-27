<div class="form-row">
    <div class="form-group col-md-12 borderd-campaing">
        <div class="title"><h4>&nbsp;<i class="fa fa-cogs"></i> Configuración de la campaña &nbsp;</h4></div>
        <div class="container-campaing">

            <div class="form-group col-md-6">
                <div class="form-group">
                    {!! Form::label('name', 'Nombre de la camapaña') !!}
                    {!! Form::text('name', null , ['class' => 'form-control', 'placeholder' => 'Ingrese el nombre de la campaña']) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('start_date', 'Duración de la campaña') !!}
                    <input id="reservationtime" type="text" name="reservationtime" class="form-control" value="{{old('reservationtime', $datetime  ?? '')}}" placeholder="Seleccione un rango de fecha"/>     
                </div>
                <div class="form-group text-left">
                    {!! Form::label('code_generate', 'Generación de código o voucher') !!}
                </div>
                <div class="radio">
                    {!! Form::radio('code_generate', 'text',true,['style' => 'margin-left:150px']) !!}
                    {!! Form::label('code_generate', 'Texto') !!}
                    <br>
                    {!! Form::radio('code_generate', 'qr',null,['style' => 'margin-left:150px']) !!}
                    {!! Form::label('code_generate', 'Código QR') !!}
                    <br>
                    {!! Form::radio('code_generate', 'barcode',null,['style' => 'margin-left:150px']) !!}
                    {!! Form::label('code_generate', 'Código de barra') !!}            
                </div>
            </div>

            <div class="form-group col-md-6">
                <div class="form-group">
                    {!! Form::label('flow', 'Flujo') !!}
                    {!! Form::select('flow', ['1' => 'Inicio de la transacción','2' => 'Durante la transacción', '3' => 'Al finalizar la transacción'],$flow_id ,['class' => 'form-control select2','placeholder' => 'Seleccione una opción', 'id' => 'flow', 'style' => 'width:100%']) !!}
                </div>
                <div class="form-group">
                    {!! Form::label('tipoCampaña', 'Tipo de campaña') !!}
                    {!! Form::select('tipoCampaña', ['1' => 'Campaña informativa','2' => 'Promoción de productos', '3' => 'Promoción + venta de productos'],null ,['class' => 'form-control select2','placeholder' => 'Seleccione una opción', 'id' => 'tipoCampaña','style' => 'width:100%']) !!}
                </div>
            </div>

            @if(isset($campaign))
                <div class="form-group">
                    {!! Form::label('perpetuity', 'Opción de perpetuidad') !!} &nbsp;
                    <label class="switch">
                        {!! Form::checkbox('perpetuity', 1, $campaign->perpetuity ? true : false) !!}<span class="slider round"></span>
                    </label>
                </div>
            @else
                <div class="form-group">
                    {!! Form::label('perpetuity', 'Opción de perpetuidad') !!} &nbsp;
                    <label class="switch">
                        <input type="checkbox" name="perpetuity" id="perpetuity"><span class="slider round"></span>
                    </label>
                </div>
            @endif

        </div>
    </div>
</div>

<div class="clearfix"></div>
{{-- 
<div class="form-row">
    <div class="form-group col-md-12 borderd-campaing">
        <div class="title"><h4>&nbsp;<i class="fa fa-bullhorn"></i> Alcance de la campaña &nbsp;</h4></div>
        <div class="form-group col-md-12">
            <div class="form-group">
    
                <div class="campaing">
                    <div class="form-row">
                        <hr>
                        <div class="form-group col-md-4">
                            <div class="form-group">
                                {!! Form::label('atm_id', 'ATM') !!} 
                                {!! Form::select('atm_id', $atms, null , ['class' => 'form-control select2', 'placeholder' => 'Seleccione una opción' , 'style' => 'width:100%']) !!}
                            </div>
                        </div> 

                        <div class="form-group col-md-3">
                            <div class="form-group">
                                {!! Form::label('provider_id', 'Proveedor de promociones') !!}
                                {!! Form::select('provider_id', $providers, null , ['class' => 'form-control select2', 'placeholder' => 'Seleccione una opción' , 'style' => 'width:100%']) !!}
                            </div>
                        </div>

                        <div class="form-group col-md-3">
                            <div class="form-group">
                                {!! Form::label('branch_id', 'Sucursal para retirar') !!}
                                {!! Form::select('branch_id', $branches, null , ['class' => 'form-control select2', 'placeholder' => 'Seleccione una opción' , 'style' => 'width:100%']) !!}        
                            </div>
                        </div>

                        <div class="form-group col-md-2" style="margin-top: 25px;">
                            <div class="form-group" style="text-align:center">
                                <button type="button" id="agregar" class="btn btn-success">Agregar</button>
                            </div>
                        </div>

                    </div>
                    <hr>
                    <div class="form-group">
                        <div class="table-responsive col-md-12">
                            <table id="detalles" class="table table-bordered table-hover table-condensed">
                                <thead style="background-color: rgb(237, 237, 237);">
                                    <tr>
                                        <th style="width: 100px; text-align:center;">Eliminar</th>
                                        <th style="width: 50%">ATM</th>
                                        <th style="width: 50%">Sucursal a retirar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
        </div>
    </div>
</div>

<div class="clearfix"></div> --}}

<div class="form-row">
    <div class="form-group col-md-12 borderd-content">
        <div class="title"><h4>&nbsp;<i class="fa fa-tags"></i> Contenidos a promocionar &nbsp;</h4></div>
        <div class="col-md-12">
            <div class="form-group">
                <div class="content">
                    <br>
                    {!! Form::label('content_id', 'Contenido') !!} 
                    {!! Form::text('contents', !empty($contentsIds) ? $contentsIds : null, ['class' => 'form-control input-lg', 'id' => 'selectContents', 'placeholder' => 'Seleccione un Contenido']) !!}
                </div>
            </div>
        </div>   
    </div>  
</div>

@section('page_scripts')
    @include('partials._selectize')
    
    {{-- <!-- iCheck -->
    <link rel="stylesheet" href="/bower_components/admin-lte/plugins/iCheck/square/grey.css">
    <script src="/bower_components/admin-lte/plugins/iCheck/icheck.min.js"></script>

    <!-- Openlayers -->
    <link rel="stylesheet" href="https://openlayers.org/en/latest/css/ol.css" />
    <script type="text/javascript" src="https://openlayers.org/en/latest/build/ol.js"></script>
    <script
        src="https://cdn.polyfill.io/v2/polyfill.min.js?features=requestAnimationFrame,Element.prototype.classList,URL,Object.assign">
    </script>

    <!-- ol-ext -->
    <link rel="stylesheet" href="/css/ol-ext/ol-ext.css" />
    <script type="text/javascript" src="/js/ol-ext/ol-ext.js"></script> --}}

     <!-- select2 -->
     <script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>
     <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
 

    <!-- Iniciar objetos
    <script type="text/javascript">
        /**
         * Parametros globales 
         */

        var selective_config = {
            delimiter: ',',
            persist: false,
            create: true,
            openOnFocus: true,
            valueField: 'id',
            labelField: 'description',
            searchField: 'description',
            maxItems: 1,
            options: {},
            onChange: null
        };


        var map = null;
        var latitude_main = -25.284437539757388;
        var longitude_main = -57.58209245312503;
        var zoom = 12;

        {{-- var eglobalt_icon = "{{ asset('favicon-96x96.png') }}"; --}}

        var popup_class = 'black';

        var markers_atms = [];
        var markers_business = [];
        var circles = [];
        var list_departments = [];
        var paraguay_json = [];
        var paraguay_json_vectors = [];

        /**
         * Parametros de OpenLayers
         */
        var tile_layer = new ol.layer.Tile({
            type: 'map',
            source: new ol.source.OSM()
        });

        var attribution = new ol.control.Attribution({
            collapsible: true,
            label: 'A',
            collapsed: true,
            tipLabel: 'Atribución'
        });

        var style_circle = new ol.style.Style({
            text: new ol.style.Text({
                //text: 'Radio de cobertura para ' + description,
                //font: 'bold 15px sans-serif',
                stroke: new ol.style.Stroke({
                    width: 3,
                    color: [255, 255, 255]
                })
            }),
            stroke: new ol.style.Stroke({
                width: 2,
                color: '#FF0000'
            }),
            fill: new ol.style.Fill({
                color: 'transparent'
            })
        });

        // Carga las sucursales

        function get_promotions_branches() {

            var url = '/get_promotions_branches/';

            var promotions_providers_id = parseInt($('#promotions_providers_id').val());
            var business_id = parseInt($('#business_id').val());

            promotions_providers_id = (Number.isNaN(promotions_providers_id)) ? null : promotions_providers_id;
            business_id = (Number.isNaN(business_id)) ? null : business_id;

            var json = {
                _token: token,
                promotions_providers_id: promotions_providers_id,
                business_id: business_id
            };

            $.post(url, json, function(data, status) {
                //console.log('get_promotions_branches', data);

                $('#promotions_branch_id').val(null).trigger('change');
                $('#promotions_branch_id').empty().trigger("change");

                var option = new Option('Todos', 'Todos', false, false);
                $('#promotions_branch_id').append(option);

                for (var i = 0; i < data.length; i++) {
                    var item = data[i];
                    var id = item.id;
                    var description = item.description;

                    var option = new Option(description, id, false, false);
                    $('#promotions_branch_id').append(option);

                    //('item:', data[i]);
                }

                $('#promotions_branch_id').trigger('change');

                //var newOption = new Option(data.text, data.id, false, false);
                //$('#mySelect2').append(newOption).trigger('change');
                //$('#promotions_branch_id').selectize();
                //$("#promotions_branch_id")[0].selectize.clear();
                //$('#promotions_branch_id').selectize(selective_config)[0].selectize.clear();
                //$('#promotions_branch_id').selectize(selective_config)[0].selectize.addOption(data);
            });
        }

        // Carga las ciudades del departamento seleccionado

        function get_cities() {

            var url = '/get_cities/';

            var departament_id = parseInt($('#departament_id').val());

            departament_id = (Number.isNaN(departament_id)) ? null : departament_id;

            var json = {
                _token: token,
                departament_id: departament_id
            };

            $.post(url, json, function(data, status) {
                //console.log('get_cities', data);

                $('#city_id').val(null).trigger('change');
                $('#city_id').empty().trigger("change");

                var option = new Option('Todos', 'Todos', false, false);
                $('#city_id').append(option);

                for (var i = 0; i < data.length; i++) {
                    var item = data[i];
                    var id = item.id;
                    var description = item.description;

                    var option = new Option(description, id, false, false);
                    $('#city_id').append(option);

                    //console.log('item:', data[i]);
                }

                $('#city_id').trigger('change');
            });
        }

        // Carga los barrios de la ciudad seleccionada

        function get_districts() {

            var url = '/get_districts/';

            var city_id = parseInt($('#city_id').val());

            city_id = (Number.isNaN(city_id)) ? null : city_id;

            var json = {
                _token: token,
                city_id: city_id
            };

            $.post(url, json, function(data, status) {
                //console.log('get_districts', data);

                $('#district_id').val(null).trigger('change');
                $('#district_id').empty().trigger("change");

                var option = new Option('Todos', 'Todos', false, false);
                $('#district_id').append(option);

                for (var i = 0; i < data.length; i++) {
                    var item = data[i];
                    var id = item.id;
                    var description = item.description;

                    var option = new Option(description, id, false, false);
                    $('#district_id').append(option);

                    //console.log('item:', data[i]);
                }

                $('#district_id').trigger('change');

                //$('#district_id').selectize(selective_config)[0].selectize.addOption(data);
            });
        }

        // Carga todas las ubicaciones con circulos.

        function load_data() {

            if (map !== null) {

                var url = '/load_atms_business_locations/';

                var record_limit = parseInt($('#record_limit').val());
                var radius = parseInt($('#radius').val());
                var atm_id = parseInt($('#atm_id').val());
                var promotions_providers_id = parseInt($('#promotions_providers_id').val());
                var business_id = parseInt($('#business_id').val());
                var promotions_branch_id = parseInt($('#promotions_branch_id').val());
                var departament_id = parseInt($('#departament_id').val());
                var city_id = parseInt($('#city_id').val());
                var district_id = parseInt($('#district_id').val());

                var radius_view = $('#radius_view').is(':checked');
                var departament_view = $('#departament_view').is(':checked');
                var atms_view = $('#atms_view').is(':checked');
                var business_view = $('#business_view').is(':checked');

                atm_id = (Number.isNaN(atm_id)) ? null : atm_id;
                promotions_providers_id = (Number.isNaN(promotions_providers_id)) ? null : promotions_providers_id;
                business_id = (Number.isNaN(business_id)) ? null : business_id;
                promotions_branch_id = (Number.isNaN(promotions_branch_id)) ? null : promotions_branch_id;
                departament_id = (Number.isNaN(departament_id)) ? null : departament_id;
                city_id = (Number.isNaN(city_id)) ? null : city_id;
                district_id = (Number.isNaN(district_id)) ? null : district_id;

                //console.log('atm_id seleccionado:', atm_id);
                //console.log('departament_view:', departament_view);
                //console.log('departament_id:', departament_id);

                var json = {
                    _token: token,
                    record_limit: record_limit,
                    atm_id: atm_id,
                    promotions_providers_id: promotions_providers_id,
                    business_id: business_id,
                    promotions_branch_id: promotions_branch_id,
                    departament_id: departament_id,
                    city_id: city_id,
                    district_id: district_id
                };

                $.post(url, json, function(data, status) {

                    var atms_locations = data.atms_locations;
                    var business_locations = data.business_locations;

                    // console.log('atms_locations:', atms_locations);

                    var list = map.getLayers().getArray();

                    //console.log('list.length:', list.length);

                    /*for (var i = 1; i < list.length; i++) {
                        var layer = list[i];

                        console.log('layer.get("type"):', layer.get('type'));

                        layer.getSource().clear();
                        map.removeLayer(layer);

                        //console.log('layer:', layer);

                        layer = null;
                    }*/

                    // Eliminar marcadores 

                    for (var i = 0; i < markers_atms.length; i++) {
                        map.removeOverlay(markers_atms[i]);
                        markers_atms[i] = null;

                        //console.log('Marcador de atm removido.');
                    }


                    for (var i = 0; i < markers_business.length; i++) {
                        map.removeOverlay(markers_business[i]);
                        markers_business[i] = null;

                        //console.log('Marcador de business removido.');
                    }

                    for (var i = 0; i < circles.length; i++) {
                        //paraguay_json_vectors[i].getSource().clear();
                        map.removeLayer(circles[i]);
                        circles[i] = null;

                        //console.log('Circulo removido.');
                    }

                    for (var i = 0; i < paraguay_json_vectors.length; i++) {
                        //paraguay_json_vectors[i].getSource().clear();
                        map.removeLayer(paraguay_json_vectors[i]);
                        //paraguay_json_vectors[i] = null;
                        //console.log('Departamento removido.');
                    }

                    markers_atms = [];
                    markers_business = [];
                    circles = [];
                    //paraguay_json_vectors = [];

                    // Cargar la ubicación de las terminales

                    if (atms_view) {
                        for (var i = 0; i < atms_locations.length; i++) {

                            var item = atms_locations[i];
                            var id = item.id;
                            var description = item.description;
                            var lat = item.latitude;
                            var lng = item.longitude;
                            var address = item.address;
                            var district = item.district;
                            var city = item.city;
                            var departament = item.departament;

                            //console.log('item:', business_locations[i]);

                            if ($.isNumeric(lng) && $.isNumeric(lat)) {

                                var position = [
                                    lng, lat,
                                ];

                                var transform = ol.proj.transform(position, 'EPSG:4326', 'EPSG:3857');

                                var title = "Dirección: " + address;
                                title += "\nBarrio: " + district;
                                title += "\nCiudad: " + city;
                                title += "\nDepartamento: " + departament;
                                title += "\nUbicación: " + lat + ", " + lng;

                                var html = '<div style="cursor: pointer;" title="' + title + '">';
                                html += '<img src="' + eglobalt_icon + '" style="width: 25px; height: 25px"> ';
                                html += description;
                                html += '</div>';

                                var marker_default = {
                                    position: transform,
                                    popupClass: popup_class,
                                    closeBox: false,
                                    positioning: 'bottom-center',
                                    html: html,
                                    stopEvent: false,
                                    autoPan: true
                                };

                                var marker = new ol.Overlay.Popup(marker_default);

                                markers_atms.push(marker);

                                var circle = new ol.geom.Circle(transform, radius);

                                var feature_circle = new ol.Feature(circle);

                                var vector_circle = new ol.layer.Vector({
                                    type: 'circle',
                                    name: 'circle_' + i,
                                    source: new ol.source.Vector(),
                                    style: style_circle
                                });

                                vector_circle.getSource().addFeature(feature_circle);

                                // console.log('circle center: ', vector_circle.getExtent());

                                circles.push(vector_circle);
                            } else {
                                // console.log('Hay una Latitud y Longitud incorrecta.');
                            }
                        }
                    }

                    // Cargar la ubicación de las empresas

                    if (business_view) {

                        for (var i = 0; i < business_locations.length; i++) {

                            var item = business_locations[i];
                            var id = item.id;
                            var description = item.description;
                            var lat = item.latitude;
                            var lng = item.longitude;
                            var address = item.address;
                            var phone = item.phone;
                            var business = item.business;
                            var business_image = 'business_images/' + item.image;
                            var provider = item.provider;

                            //console.log('item:', business_locations[i]);

                            if ($.isNumeric(lng) && $.isNumeric(lat)) {

                                var position = [
                                    lng, lat,
                                ];

                                var transform = ol.proj.transform(position, 'EPSG:4326', 'EPSG:3857');

                                var title = "Proveedor: " + provider;
                                title += "\nEmpresa: " + business;
                                title += "\nDirección: " + address;
                                title += "\nTeléfono: " + phone;
                                title += "\nUbicación: " + lat + ", " + lng;

                                var html = '<div style="cursor: pointer;" title="' + title + '">';
                                html += '<table>';
                                html += '<tr>';
                                //html += '<td>';
                                //html += '<input type="checkbox" ';
                                //html += 'style="cursor: pointer;" title="Seleccionar">';
                                //html += '</input> &nbsp;';
                                //html += '</td>';
                                html += '<td>';
                                html += '<img src="' + business_image + '" ';
                                html += 'style="width: 25px; height: 25px; padding: 2px"> ';
                                html += description;
                                html += '</td>';
                                html += '</tr>';
                                html += '</table>';
                                html += '</div>';

                                var marker_default = {
                                    position: transform,
                                    popupClass: popup_class,
                                    closeBox: false,
                                    positioning: 'bottom-center',
                                    html: html,
                                    stopEvent: false,
                                    autoPan: true
                                };

                                var marker = new ol.Overlay.Popup(marker_default);

                                markers_business.push(marker);
                            } else {
                                //console.log('Hay una Latitud y Longitud incorrecta.');
                                //console.log('lng:', lng, 'lat:', lat);
                            }
                        }
                    }



                    for (var i = 0; i < markers_atms.length; i++) {
                        map.addOverlay(markers_atms[i]);
                        markers_atms[i].show(true);
                    }

                    for (var i = 0; i < markers_business.length; i++) {
                        map.addOverlay(markers_business[i]);
                        markers_business[i].show(true);
                    }

                    if (radius_view) {
                        for (var i = 0; i < circles.length; i++) {
                            map.addLayer(circles[i]);
                        }
                    }

                    //console.log('departament_id:', departament_id);

                    var center_polygon = [];

                    if (departament_view) {
                        if ($.isNumeric(departament_id)) {
                            for (var i = 0; i < paraguay_json_vectors.length; i++) {
                                var layer = paraguay_json_vectors[i];

                                var id = layer.get('id');
                                var description = layer.get('description');

                                if (id == departament_id) {

                                    var style = new ol.style.Style({
                                        fill: new ol.style.Fill({
                                            color: 'rgba(255, 255, 255, 0.5)', //'#ffffff'
                                        }),
                                        stroke: new ol.style.Stroke({
                                            color: '#ffcc33',
                                            width: 4
                                        })
                                    });

                                    var style = new ol.style.Style({
                                        text: new ol.style.Text({
                                            text: description,
                                            font: '25px sans-serif',
                                            padding: [3, 3, 3, 5],
                                            stroke: new ol.style.Stroke({
                                                color: 'black',
                                                width: 0.75
                                            }),
                                            backgroundFill: new ol.style.Fill({
                                                color: 'white'
                                            }),
                                            backgroundStroke: new ol.style.Stroke({
                                                color: 'black',
                                                width: 1,
                                                radius: 20
                                            })
                                        }),
                                        stroke: new ol.style.Stroke({
                                            color: '#ffcc33',
                                            width: 4
                                        })
                                    });

                                    paraguay_json_vectors[i].setStyle(style);


                                    map.addLayer(paraguay_json_vectors[i]);
                                    //paraguay_json_vectors[i] = null;
                                    //console.log('Departamento agregado asdf.');

                                    center_polygon = paraguay_json_vectors[i].getSource().getExtent();
                                    break;
                                }
                            }
                        } else {
                            for (var i = 0; i < paraguay_json_vectors.length; i++) {
                                map.addLayer(paraguay_json_vectors[i]);
                                //console.log('Departamento agregado.');
                            }
                        }
                    }

                    var zoom = map.getView().getZoom();
                    //var center = map.getView().getCenter();
                    //var longitude_center = center[0];
                    //var latitude_center = center[1];

                    //console.log('ZOOM:', zoom);
                    //console.log('center:', center);

                    var center = ol.proj.transform([longitude_main, latitude_main], 'EPSG:4326', 'EPSG:3857');

                    if (center_polygon.length > 0) {

                        var x = center_polygon[0] + (center_polygon[2] - center_polygon[0]) / 2;
                        var y = center_polygon[1] + (center_polygon[3] - center_polygon[1]) / 2;

                        center = [x, y];
                        zoom = 8;
                        //console.log('CENTRADO AL POLIGONO');
                    }

                    if (atms_locations.length == 1) {
                        var item = atms_locations[0];
                        var latitude = item.latitude;
                        var longitude = item.longitude;

                        center = ol.proj.transform([longitude, latitude], 'EPSG:4326', 'EPSG:3857');
                        zoom = 12;
                        //console.log('CENTRADO AL ATM');
                    }

                    //console.log('CENTER:', center);
                    //console.log('ZOOM:', zoom);

                    map.getView().setCenter(center);
                    map.getView().setZoom(zoom);

                    //console.log('Fin de sgundo ciclo.');
                }).error(function(error) {
                    //console.log('Error al cargar...', error);
                });

            }
        }

        $(document).ready(function() {

            $('.select2').select2();

            var item_all = {
                value: 'Todos',
                text: 'Todos'
            };

            selective_config.onChange = function(value) {

                var selective_combo = $(this)[0].$input['0'];
                var id = selective_combo.id;

                load_data(); // para todos los casos

                switch (id) {
                    case 'promotions_providers_id':
                    case 'business_id':
                        //get_promotions_branches();
                    case 'departament_id':
                        //get_cities();
                    default:
                        // code block
                }

                console.log('COMBO SELECCIONADO EN selective_config:', id);
            };

            {{-- var lists = {!! $data['lists'] !!}; --}}

            //console.log('List:', lists.departaments);

            // $('#atm_id').selectize(selective_config)[0].selectize.addOption(lists.atms);
            // $('#departament_id').selectize(selective_config)[0].selectize.addOption(lists.departaments);
            // $('#promotions_providers_id').selectize(selective_config)[0].selectize.addOption(lists
            //     .promotions_providers);
            // $('#business_id').selectize(selective_config)[0].selectize.addOption(lists.business);

            //$('#city_id').selectize(selective_config)[0].selectize.addOption({});
            //$('#district_id').selectize(selective_config)[0].selectize.addOption({});
            //$('#promotions_branch_id').selectize(selective_config)[0].selectize.addOption({});

            $('.select2').on('select2:selecting', function(e) {

                var id = e.currentTarget.id;

                switch (id) {
                    case 'city_id':
                        get_districts();
                    default:
                        // code block
                }

                console.log('COMBO SELECCIONADO EN select2:', id);

                //console.log('Selecting: ' , e.params.args.data);
                //console.log('e: ' , e.currentTarget.id);
            });

            //-------------------------------------------------------------------------------------

            $('input[type="checkbox"]').iCheck('check');

            $('input[type="checkbox"]').iCheck({
                checkboxClass: 'icheckbox_square-grey',
                radioClass: 'iradio_square-grey'
            });

            $('input[type="checkbox"]').iCheck('check');

            $('input[type="checkbox"]').on('ifChanged', function() {
                load_data();
            });

            $('input[type="number"]').bind('click keyup', function() {
                load_data();
            });

            //-------------------------------------------------------------------------------------

            map = new ol.Map({
                target: 'map',
                view: new ol.View({
                    zoom: zoom,
                    center: ol.proj.fromLonLat([longitude_main, latitude_main])
                }),
                interactions: ol.interaction.defaults({
                    altShiftDragRotate: false,
                    pinchRotate: false
                }),
                layers: [tile_layer],
                controls: ol.control.defaults({
                    attribution: false
                }).extend([
                    new ol.control.FullScreen(),
                    //attribution
                ])
            });

            $.getJSON('/json/map/paraguay.json', function(data) {
                paraguay_json = data;

                for (var i = 0; i < paraguay_json.length; i++) {

                    var item = paraguay_json[i];
                    var id = item.id;
                    var description = item.description;
                    var coordinates = item.coordinates;

                    //console.log('coordinates:', coordinates);

                    var coordinates_transform = [];

                    for (var j = 0; j < coordinates.length; j++) {

                        var lng = coordinates[j][0];
                        var lat = coordinates[j][1];

                        coordinates_transform.push(
                            ol.proj.fromLonLat([lng, lat], "EPSG:4326")
                        );
                    }

                    var polygon_style = new ol.style.Style({
                        text: new ol.style.Text({
                            text: description,
                            font: '20px sans-serif',
                            padding: [3, 3, 3, 5],
                            stroke: new ol.style.Stroke({
                                color: 'black',
                                width: 0.75
                            }),
                            backgroundFill: new ol.style.Fill({
                                color: 'white'
                            }),
                            backgroundStroke: new ol.style.Stroke({
                                color: 'black',
                                width: 1
                            }),
                            overflow: true,
                            visible: false
                        }),
                        stroke: new ol.style.Stroke({
                            width: 2,
                            color: '#FF0000'
                        }),
                        //fill: new ol.style.Fill({
                        //color: 'rgba(255, 255, 255, 0.5)'
                        //})
                    });

                    // console.log('coordinates_transform:', coordinates_transform);

                    var polygon = new ol.geom.Polygon([coordinates_transform]);
                    polygon.transform('EPSG:4326', 'EPSG:3857');

                    var feature = new ol.Feature(polygon);

                    var source = new ol.source.Vector({
                        type: 'department'
                    });

                    source.addFeature(feature);

                    var vector = new ol.layer.Vector({
                        type: 'department',
                        id: id,
                        description: description,
                        source: source,
                        style: polygon_style
                    });

                    // vector.getSource().addFeature(feature);

                    //map.addLayer(vector);

                    paraguay_json_vectors.push(vector);
                }

                load_data();
            });
        });
    </script>
    -->
    <script>
        $(document).on('select2:select', '#provider_id', function() {
            $.get("/campaigns/branches/" + $(this).val(), function(response) {
                $('#branch_id').html(response).trigger('change');
            });
        });


        $('#selectContents').selectize({
            delimiter: ',',
            persist: false,
            openOnFocus: true,
            valueField: 'id',
            labelField: 'name',
            searchField: 'name',
            render: {
                item: function(item, escape) {
                    return '<div><span class="label label-primary">' + escape(item.name) + '</span></div>';
                }
            },
             options: {!! $contentsJsonAll !!}
        });
    

    </script> 
@append