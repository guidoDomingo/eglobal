var notifications = {
    get:{
        elements:{
            alerts:function(){
                /*
                
                Comentado por que explota en todas las pantallas:

                $.post("/notifications/get-notifications", {_token: token }, function( data ) {
                    if(data.status){
                        $("#not-list").html('');
                        $("#saldo-list").html('');
                        $("#serv-list").html('');
                        var count_not = 0;
                        var count_saldo = 0;
                        var count_serv = 0;
                        $.each(data.result, function (item, value) {
                            if(value.type_id == 1 || value.type_id == 3){
                                count_not = count_not +1;
                                $("#not-list").append('<li><a href="#"><i class="fa fa-warning text-red"></i>'+value.code+ ' - '+value.description+'</a></li>');
                            }
                            if(value.type_id == 4){
                                count_saldo = count_saldo +1;
                                $("#saldo-list").append('<li><a href="#"><i class="fa fa-warning text-red"></i>'+value.code+ ' - '+value.description+'</a></li>');
                            }
                            if(value.type_id == 2){
                                count_serv = count_serv + 1;
                                $("#serv-list").append('<li><a href="#"><i class="fa fa-warning text-red"></i>'+value.code+ ' - '+value.description+'</a></li>');
                            }
                        });

                        if(count_not != 0){
                            $("#not-count").html(count_not);
                            $("#not-header").html("Tienes "+count_not+" notificaciones pendientes");
                        }

                        if(count_saldo != 0){
                            $("#saldo-count").html(count_saldo);
                            $("#saldo-header").html("Tienes "+count_saldo+" notificaciones pendientes");
                        }
                        if(count_serv != 0){
                            $("#serv-count").html(count_serv);
                            $("#serv-header").html("Tienes "+count_serv+" notificaciones pendientes");
                        }

                    }
                });
                */
            }
        },
        load:function(){
            //notifications.get.elements.alerts();
        }

    }
};

/*
notifications.get.load();


window.setInterval(function(){
    notifications.get.load();
}, 300000);
*/


