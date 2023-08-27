<section class="content">
    <div class="row">
        <div class="col-md-12">
            <div class="box box-default">
                <div class="box-header with-border">
                    <h3 class="box-title">Filtros de búsqueda</h3>

                    <div class="box-tools pull-right">
                        <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                        <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-remove"></i></button>
                    </div>
                </div>
                <!-- /.box-header -->
                    <div class="box-body" style="display: block;">
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    {!! Form::label('redes', 'Redes') !!}
                                    {!! Form::select('owner_id', $owners, null, ['id' => 'owner_id','class' => 'form-control select2']) !!}
                                </div>
                            </div>
                            <!-- /.col -->
                            <div class="col-md-5">
                                <!-- /.form-group -->
                                <div class="form-group">
                                    {!! Form::label('sucursales', 'Sucursales') !!}
                                    {!! Form::select('branch_id', $branches, null, ['id' => 'branch_id','class' => 'form-control select2']) !!}
                                </div>
                            </div>
                            <!-- /.col -->
                            <div class="col-md-2">
                                <!-- /.form-group -->
                                <div class="form-group">
                                    <div style="margin-top:25px" class="btn btn-block btn-success" id="desc_resumen">EXPORTAR</div>
                                </div>
                            </div>
                        </div>
                        <!-- /.row -->
                    </div>
                    <!-- /.box-body -->
                    <div class="box-footer" style="display: block;">
                    </div>
            </div>
        </div>
    </div>

    <div id="graphic" class="row" style="visibility:hidden">

    </div>
</section>

@section('js')
    <script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>
    <!-- InputMask -->
    <script>
        $(function(){
            $('.select2').select2();
            //Cascading dropdown list de redes / sucursales
            var datos = '';
            $('#owner_id').on('change', function(e){
                var owner_id = e.target.value;
                //Cargar ddl de sucursales
                $.get('{{ url('reports') }}/ddl/branches/0/' + owner_id, function(data) {
                    $('#branch_id').empty();

                    $('#branch_id').append($('<option>', {
                        value: 0,
                        text : 'Seleccione Sucursal'
                    }));

                    $.each(data, function(i,item){
                        $('#branch_id').append($('<option>', {
                            value: i,
                            text : item
                        }));
                    });

                    $('#branch_id').trigger('change');
                });

                //Cargar reportes de todas las sucursales del owner seleccionado
                $.get('{{ url('reports') }}/saldos/detalles/' + owner_id + '/0', function(details) {
                    show_data(details.branches);
                });
            });

            $('#branch_id').on('change', function(e){
                var owner_id = $('#owner_id').val();
                var branch_id = e.target.value;
                $.get('{{ url('reports') }}/saldos/detalles/' + owner_id + '/' + branch_id, function(details) {
                    show_data(details.branches);
                });
            });

            /** Exportar a excel*/
            $('#desc_resumen').on("click",function(){
                var resumen = $('.info-box span')
                        .map(function() { return $(this).text(); }).get().join(',')
                var resumen = resumen.split(',');
                var arr = {};
                var j = 0;
                for(var i in resumen){
                    if(resumen[i] == ''){
                        j++;
                        arr[j] = {}
                    }else{
                        arr[j] += $.trim(resumen[i])+',';
                    }
                }

                $.post("/reports/saldos/export", {_token: token, _resumen: arr }, function( data ) {
                    if(data.status){
                        var a = document.createElement("a");
                        a.href = data.file;
                        a.download = data.name;
                        document.body.appendChild(a);
                        a.click();
                        a.remove();
                    }else{
                        console.log('Error al realizar la consulta');
                    }
                });

            });

            $( document ).ready(function() {
                //Cargar reportes de todas las sucursales del owner seleccionado
                $.get('{{ url('reports') }}/saldos/detalles/0/0', function(details) {
                    if(!details.error){
                        show_data(details.branches);
                    }
                });
            });

            function show_data(details){
                var $resultDisplay = jQuery('#graphic');
                $resultDisplay.empty();
                var display_info = '';
                jQuery.each(details, function(index, pdv) {
                    //setup navs panels
                    var tabs = '';
                    var subtotal = 0;
                    var totalCasettes = 0;
                    var totalHoppers = 0;
                    var totalBoxes = 0;
                    var totalPurga = 0;
                    var totalTarjeta = 0;
                    var totalTarjetaPurga = 0;

                    jQuery.each(pdv.pdvs, function(index, atms) {
                        var parts = '';
                        var listado = '';
                        jQuery.each(atms.parts, function(index_2, atm_parts) {
                            //console.log(atm_parts);
                            display_info = '';
                            subtotal = subtotal +  atm_parts.subtotal;

                            if(atm_parts.tipo_partes == 'Box'){
                                totalBoxes = totalBoxes +  atm_parts.subtotal;
                            }else if(atm_parts.tipo_partes == 'Hopper'){
                                totalHoppers = totalHoppers +  atm_parts.subtotal;
                            }else if(atm_parts.tipo_partes == 'Purga'){
                                totalPurga = totalPurga +  atm_parts.subtotal;
                            }else if(atm_parts.tipo_partes == 'DispTarj'){
                                totalTarjeta = totalTarjeta +  atm_parts.cantidad;
                            }else if(atm_parts.tipo_partes == 'DispTarjPurga'){
                                totalTarjetaPurga = totalTarjetaPurga +  atm_parts.cantidad;
                            }else{
                                totalCasettes = totalCasettes +  atm_parts.subtotal;
                            }

                            if(atm_parts.tipo_partes == 'Cassette'){
                                var color = '';
                                if(atm_parts.cantidad >= atm_parts.cantidad_alarma){
                                    color = 'green';
                                }

                                if(atm_parts.cantidad <= atm_parts.cantidad_alarma){
                                    color = 'yellow';
                                }

                                if(atm_parts.cantidad <= atm_parts.cantidad_min){
                                    color = 'red';
                                }
                                var percent = (atm_parts.cantidad * 100) / atm_parts.cantidad_max;
                                if(percent == 0){
                                    percent = percent  + 1;
                                }
                                var total = atm_parts.denominacion * atm_parts.cantidad;
                                listado += "<p class='lead'>"+ atm_parts.cantidad +" unidades de "+ format(atm_parts.denominacion) +" Gs. = "  + format(total) + " Gs. </p>";
                                listado += '<div class="progress"><div class="progress-bar progress-bar-'+color+'" role="progressbar" aria-valuenow="'+percent+'" aria-valuemin="0" aria-valuemax="100" style="width: '+percent+'%"><span class="sr-only">'+percent+'% Complete (success)</span></div></div>';

                            }
                            display_info = '<h3>Cassettes<h3>';
                            display_info += listado;
                        });

                        tabs += '<li class="dropdown">' +
                                '<a class="dropdown-toggle" data-toggle="dropdown" href="#" aria-expanded="false"> '+ atms.atm_code +' <span class="caret"></span></a>' +
                                '      <ul class="dropdown-menu">' +
                                '          <li role="presentation" data-branchid="'+ pdv.branch_id  +'" data-atmid="'+ atms.atm_code +'" data-part="Cassette"><a role="menuitem" tabindex="-1" href="#" >Cassettes</a></li>' +
                                '          <li role="presentation" data-branchid="'+ pdv.branch_id  +'" data-atmid="'+ atms.atm_code +'" data-part="Hopper"><a role="menuitem" tabindex="-1" href="#" >Hoppers</a></li>' +
                                '          <li role="presentation" data-branchid="'+ pdv.branch_id  +'" data-atmid="'+ atms.atm_code +'" data-part="Box"><a role="menuitem" tabindex="-1" href="#" >Boxes</a></li>' +
                                '          <li id="tab_purga" role="presentation" data-branchid="'+ pdv.branch_id  +'" data-atmid="'+ atms.atm_code +'" data-part="Purga"><a role="menuitem" tabindex="-1" href="#" >Purga</a></li>' +
                                '      </ul>' +
                                '</li>';
                    });

                    $resultDisplay.append('<div id="' + pdv.branch_id + '" class="row"><div class="col-md-12">' +
                    '<div class="col-md-4 col-sm-6 col-xs-12 pdv_resumen">'+
                    '<div class="info-box bg-aqua">' +
                    '<span class="info-box-icon" style="height:147px"><i class="fa fa-bookmark-o"></i></span>' +
                    '<div class="info-box-content">' +
                    '<span id="sucursal" class="info-box-text"> '+ pdv.branch_name +'  </span>' +
                    '<span class="info-box-number">'+ format(subtotal) +'</span>' +
                    '<div class="progress">' +
                    '<div class="progress-bar" style="width: 100%"></div>' +
                    '</div>' +
                    '<span class="progress-description">Cassettes: <b>'+ format(totalCasettes) +'</b></span>' +
                    '<span class="progress-description">Hoppers <b>'+ format(totalHoppers) +'</b></span>' +
                    '<span class="progress-description">Box: <b>'+ format(totalBoxes) +'</b></span>' +
                    '<span id ="total_purga" class="progress-description">Purga: <b>'+ format(totalPurga) +'</b></span>' +
                    '<span id ="totalTarjeta" class="progress-description">Simcard(Tigo): <b>'+ format(totalTarjeta) +'</b></span>' +
                    '<span id ="totalTarjetaPurga" class="progress-description">SimcardPurga: <b>'+ format(totalTarjetaPurga) +'</b></span>' +
                    '</div>'  +
                    '</div>' +
                    '</div>' +
                     // ADDING PANES
                    '<div class="col-md-8">' +
                    '<div class="nav-tabs-custom">' +
                    '<ul id="atms-tab" class="nav nav-tabs"> '+ tabs +' </ul>' +
                    '<div class="tab-content">' +
                    '<div class="tab-pane active" id="tab_'+ pdv.branch_id +'">' + display_info +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '</div>' +
                    '</div></div>');

                });

                $( ".dropdown-menu" ).on( "click", "li", function() {
                    var element = $(this).parent();
                    $(element.parent()).addClass("active");
                    var id = $(this).attr("data-atmid");
                    var branch_id = $(this).attr("data-branchid");
                    var atmPart = $(this).attr("data-part");
                    var parts = details[branch_id]['pdvs'][id]['parts'];
                    var listado = '';
                    jQuery.each(parts, function(index_5, atm_part) {
                        if(atm_part.tipo_partes == atmPart){
                            if(atm_part.cantidad >= atm_part.cantidad_alarma){
                                color = 'green';
                            }

                            if(atm_part.cantidad <= atm_part.cantidad_alarma){
                                color = 'yellow';
                            }

                            if(atm_part.cantidad <= atm_part.cantidad_min){
                                color = 'red';
                            }

                            var percent = (atm_part.cantidad * 100) / atm_part.cantidad_max;
                            if(percent == 0){
                                percent = percent  + 1;
                            }

                            var total = atm_part.denominacion * atm_part.cantidad;
                            listado += "<p class='lead'>"+ atm_part.cantidad +" unidades de "+ format(atm_part.denominacion) +" Gs. = "  + format(total) + " Gs. </p>";
                            listado += '<div class="progress"><div class="progress-bar progress-bar-'+color+'" role="progressbar" aria-valuenow="'+percent+'" aria-valuemin="0" aria-valuemax="100" style="width: '+percent+'%"><span class="sr-only">'+percent+'% Complete (success)</span></div></div>';
                        }
                    });
                    display_info = '<h3>'+$(this).text()+'<h3>';
                    display_info += listado;
                    //console.log(parts);
                    scrollTo('#'+branch_id);
                    $('#tab_'+branch_id).html(display_info);
                });

                $('#graphic').css('visibility', 'visible');
            }

            function scrollTo(hash) {
                $('body, html').animate({
                    'scrollTop':   $(hash).offset().top
                }, 500);
            }

            function format(nro){
                var num = nro;
                if(!isNaN(num)){
                    num = num.toString().split('').reverse().join('').replace(/(?=\d*\.?)(\d{3})/g,'$1.');
                    num = num.split('').reverse().join('').replace(/^[\.]/,'');
                    return num;
                }
            }
        });

    </script>
@endsection
@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
@endsection