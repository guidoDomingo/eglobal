var $errorHtml = '<div title="Error al consultar" class="animated fadeIn text-center"><i class="fa fa-exclamation-triangle"></i><br></div>';
var urlGetDetalle = '/dashboard/atms_detalles/';

var dashboard =  {
    main:{
        elements:{
            atms: function(){
                $.post("/dashboard/atms", {_token: token }, function( data ) {
                    if(data.status){
                        $(".atm_info").html(data.result.message);
                    }else{
                        $(".atm_info").html("");
                    }

                }).error(function(){
                    $(".atm_info").html($errorHtml);
                });


            },
            services: function(){
                $.post("/dashboard/services", {_token: token }, function( data ) {

                    if(data.status){
                        $(".service_info").html(data.result.message);
                    }else{
                        $(".service_info").html("");
                    }

                }).error(function(){
                    $(".service_info").html($errorHtml);
                });
            },
            atm_balances: function(){
                $.post("/dashboard/balances", {_token: token }, function( data ) {

                    if(data.status){
                        $(".balances_info").html(data.result.message);
                    }else{
                        $(".balances_info").html("");
                    }

                }).error(function(){
                    $(".balances_info").html($errorHtml);
                });
            },
            warnings:function(){
                /*

                Comentado porque explota: 

                $.post("/dashboard/warnings", {_token: token }, function( data ) {

                    if(data.status){
                        $(".warning_info").html(data.result.message);
                    }else{
                        $(".warning_info").html("");
                    }

                }).error(function(){
                    $(".warning_info_info").html($errorHtml);
                });
                */
            },
            rollback:function(){
                $.post("/dashboard/rollback", {_token: token }, function( data ) {

                    if(data.status){
                        $(".rollback_info").html(data.result.message);
                    }else{
                        $(".rollback_info").html("");
                    }

                }).error(function(){
                    $(".rollback_info").html($errorHtml);
                });
            },
            montoCero:function(){
                $.post("/dashboard/montoCero", {_token: token }, function( data ) {

                    if(data.status){
                        $(".monto_cero_info").html(data.result.message);
                    }else{
                        $(".monto_cero_info").html("");
                    }

                }).error(function(){
                    $(".monto_cero_info").html($errorHtml);
                });
            },
            pendiente:function(){
                $.post("/dashboard/pendiente", {_token: token }, function( data ) {

                    if(data.status){
                        $(".pendiente_info").html(data.result.message);
                    }else{
                        $(".pendiente_info").html("");
                    }

                }).error(function(){
                    $(".pendiente_info").html($errorHtml);
                });
            },
            conciliations:function(){
                $.post("/dashboard/conciliations", {_token: token }, function( data ) {

                    if(data.status){
                        $(".conciliations_info").html(data.result.message);
                    }else{
                        $(".conciliations_info").html("");
                    }

                }).error(function(){
                    $(".conciliations_info").html($errorHtml);
                });
            },
            transactions:function(frecuency){
                $("#graph_spinn").show();  
                $("#chartdiv").hide(); 
                $.post("/dashboard/transactions", {_token: token, _frecuency: frecuency},function(data) {
                    if(data.status){
                        graphs.lines('title',data.result.data)
                        $("#graph-title").html(data.result.dates);
                        $("#graph_spinn").hide();
                        $("#chartdiv").show(); 
                    }else{
                        $("#chartdiv").html($errorHtml);
                    }

                    console.log('hizo pos');
                }).error(function(){
            
                    $("#chartdiv").html($errorHtml);
                });


            },
            refresh:function(){
                $("#keys_content").hide();
                $("#keys_spinn").show();
                $.post("/dashboard/keys", {_token: token }, function( data ) {
                    if(data.status){
                        $("#keys_spinn").hide();
                        $("#keys_content").html(data.result.message);
                        $("#keys_content").show();
                    }else{
                        $("#keys_spinn").hide();
                        $(".keys_content").html("");
                        $("#keys_content").show();
                    }

                }).error(function(){
                    $("#keys_spinn").hide();
                    $(".keys_content").html($errorHtml);
                    $("#keys_content").show();
                });
            },
            showkey:function(key_id){
                var key_pass    = '#pass_'+key_id;
                var key_eye     = '#eye_'+key_id;
                var key_forb     = '#forb_'+key_id;
                $.post("/dashboard/show_keys", {_token: token,_key_id: key_id }, function( data ) {
                    if(data.status){
                        $(key_pass).html(data.result.message);
                        $(key_eye).hide();
                        if(data.result == -213){
                            $(key_forb).show();
                        }
                    }else{
                        $(key_pass).html('Error');
                        $(key_eye).hide();
                    }
                });
            },
            refreshAtm:function(id){
                $("#retiro_content").hide();
                $("#retiro_spinn").show();
                $.post("/dashboard/atmsView", {_token: token, id: id }, function( data ) {
                    if(data.status){
                        $("#retiro_spinn").hide();
                        $("#retiro_content").html(data.result.message);
                        $("#retiro_content").show();
                    }else{
                        $("#retiro_spinn").hide();
                        $(".retiro_content").html("");
                        $("#retiro_content").show();
                    }

                }).error(function(){
                    $("#retiro_spinn").hide();
                    $(".retiro_content").html($errorHtml);
                    $("#retiro_content").show();
                });
            },
            atms_general:function(redes){                
                $("#graficoAtm").hide();
                $("#atm_spinn").show();

                $.post("/dashboard/atms_general", {_token: token, _redes: redes },function(data) {
                    var valores = data.result.data;

                    var chart = AmCharts.makeChart("graficoAtm", {
                        // "language": "es",
                        "type": "pie",
                        "startDuration": 0,
                        "pullOutDuration": 0,
                        "pullOutRadius": 0,
                        "radius": 80,
                        "theme": "none",
                        "addClassNames": true,
                        "legend":{
                            "position":"bottom",
                            "autoMargins":true
                        },
                        "colorField": "color",
                        "innerRadius": "20%",
                        "fontFamily": "Helvetica",
                        "defs": {
                            "filter": [{
                                "id": "shadow",
                                "width": "200%",
                                "height": "200%",
                                "feOffset": {
                                    "result": "offOut",
                                    "in": "SourceAlpha",
                                    "dx": 0,
                                    "dy": 0
                                },
                                "feGaussianBlur": {
                                    "result": "blurOut",
                                    "in": "offOut",
                                    "stdDeviation": 5
                                },
                                "feBlend": {
                                    "in": "SourceGraphic",
                                    "in2": "blurOut",
                                    "mode": "normal"
                                }
                            }]
                        },
                        "dataProvider": [
                            {
                                "estado": "Cap. Máxima",
                                "minutos": valores.capacidad_maxima,
                                "color": "#00008e",
                                "param": "capacidad_maxima"
                            }, 
                            {
                                "estado": "Cant. Mínima",
                                "minutos": valores.cantidad_minima,
                                "color": "#00b8ef",
                                "param": "cantidad_minima"
                            },
                            {
                                "estado": "Online",
                                "minutos": valores.online,
                                "color": "#0A8B19",
                                "param": "online"
                            }, 
                            {
                                "estado": "Offline",
                                "minutos": valores.offline,
                                "color": "#FDB504",
                                "param": "offline"
                            }, 
                            {
                                "estado": "Suspendido",
                                "minutos": valores.suspendido,
                                "color": "#FD0404",
                                "param": "suspendido"
                            },
                            {
                                "estado": "Bloqueados",
                                "minutos": valores.bloqueados,
                                "color": "#770000",
                                "param": "bloqueados"
                            }, 
                        ],
                        "valueField": "minutos",
                        "titleField": "estado",
                        "export": {
                            "enabled": true,
                            "label": "Exportar",
                        }
                    });

                    chart.addListener("clickSlice", handleClick);

                    function handleClick(e)
                    {
                        if(e.dataItem.dataContext.param == 'capacidad_maxima'){
                            $('.actual').show();
                            $('.maxima').show();
                        }else{
                            $('.maxima').hide();
                            $('.actual').hide();
                        }

                        $("#modal-contenido").html('');
                        $("#modal-footer").html('');
                        console.log(urlGetDetalle+e.dataItem.dataContext.param+'/'+redes);
                        $.get(urlGetDetalle+e.dataItem.dataContext.param+'/'+redes, 
                        {
                            status: e.dataItem.dataContext.param,
                            redes: redes
                        },
                        function(data) {
                            $("#modal-contenido").html(data.modal_contenido);
                            $("#modal-footer").html(data.modal_footer);
                            $("#modalDetalleAtms").modal('show');
                        });
                    }
                    $("#atm_spinn").hide();
                    $("#graficoAtm").show();
                }).error(function(){
                    $("#modal-contenido").html($errorHtml);
                });
            },
            balance_online: function(){
                $.post("/dashboard/balance_online", {_token: token }, function( data ) {
                    console.log(data);

                    if(data.status){
                        if(data.result.data.valor > 30000000){

                            $('#principal').append('<div class="small-box bg-green" style="border-radius: 15px;"><div class="inner"><h3 class="credit_online" style="margin-left: 35px;"></h3><h4 class="moneda" style="margin-left: 30px"></h4></div><div class="icon" style="margin-top: 45px; margin-right: 10px;"><i class="fa fa-money"></i></div><h4 class="small-box-footer">Saldo EPIN ( Estado: OK )</h4></div>');
                            $(".credit_online").html(data.result.data.credit);
                            $(".moneda").html(data.result.data.moneda);

                        }else if(data.result.data.valor <= 30000000 && data.result.data.valor > 20000000){
                            $('#principal').append('<div class="small-box bg-yellow" style="border-radius: 15px;"><div class="inner"><h3 class="credit_online" style="margin-left: 35px;"></h3><h4 class="moneda" style="margin-left: 30px"></h4></div><div class="icon" style="margin-top: 45px; margin-right: 10px;"><i class="fa fa-money"></i></div><h4 class="small-box-footer">Saldo EPIN ( Estado: Saldo bajo )</h4></div>');
                            $(".credit_online").html(data.result.data.credit);
                            $(".moneda").html(data.result.data.moneda);

                        }else if(data.result.data.valor <= 20000000 && data.result.data.valor > 5000000){
                            $('#principal').append('<div class="small-box bg-orange" style="border-radius: 15px;"><div class="inner"><h3 class="credit_online" style="margin-left: 35px;"></h3><h4 class="moneda" style="margin-left: 30px"></h4></div><div class="icon" style="margin-top: 45px; margin-right: 10px;"><i class="fa fa-money"></i></div><h4 class="small-box-footer">Saldo EPIN ( Estado: Crítico )</h4></div>');
                            $(".credit_online").html(data.result.data.credit);
                            $(".moneda").html(data.result.data.moneda);
                        }else if(data.result.data.valor >= 0 && data.result.data.valor <= 5000000){
                            $('#principal').append('<div class="small-box bg-red" style="border-radius: 15px;"><div class="inner"><h3 class="credit_online" style="margin-left: 35px;"></h3><h4 class="moneda" style="margin-left: 30px"></h4></div><div class="icon" style="margin-top: 45px; margin-right: 10px;"><i class="fa fa-money"></i></div><h4 class="small-box-footer">Saldo EPIN ( Estado: Sin saldo )</h4></div>');
                            $(".credit_online").html(data.result.data.credit);
                            $(".moneda").html(data.result.data.moneda);
                        }

                    }else{
                        $('#principal').append('<div class="small-box bg-gray" style="border-radius: 15px;"><div class="inner"><br><h3 class="credit_online" style="margin-left: 10px; color:white;"></h3><h4 class="moneda" style="margin-left: 10px; color:white;"></h4></div><div class="icon" style="margin-top: 65px; margin-right: 10px;"><i class="fa fa-money"></i></div><h4 class="small-box-footer">Saldo EPIN ( Error en la consulta )</h4></div>');
                        $(".credit_online").html("Sin información");
                        $(".moneda").html("Sin información");                    
                    }

                }).error(function(){
                    $(".atm_info").html($errorHtml);
                });


            },
        },
        load:function(){
            dashboard.main.elements.balance_online();

            dashboard.main.elements.atms();
            dashboard.main.elements.services();
            dashboard.main.elements.atm_balances();
            dashboard.main.elements.warnings();
            dashboard.main.elements.rollback();
            dashboard.main.elements.montoCero();
            dashboard.main.elements.pendiente();
            dashboard.main.elements.conciliations();


            @if (\Sentinel::getUser()->hasAccess('superuser') and \Sentinel::getUser()->hasAccess('monitoreo.transacciones'))
                dashboard.main.elements.transactions('daily');
            @endif



            dashboard.main.elements.refresh();
            dashboard.main.elements.refreshAtm();

            dashboard.main.elements.atms_general('todos');

        }
    }
};

dashboard.main.load();


