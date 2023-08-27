<script type="text/javascript">
    $(document).ready(function () {
        $('.btn-baneo').click(function(e){
            e.preventDefault();
            var row = $(this).parents('tr');
            var id = row.data('id');
            var params = row.data('banned');
            var action = "desbloquear";
            if(params != 1){
                params = 'ban';
                action = "bloquear";
            }
            swal({
                        title: "Atención!",
                        text: "Está a punto de " + action + " a este usuario, está seguro?.",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "Si, "+ action +"!",
                        cancelButtonText: "No, cancelar!",
                        closeOnConfirm: true,
                        closeOnCancel: true
                    },
                    function(isConfirm){
                        if (isConfirm) {
                            var url = "/baneo";
                            var type = "";
                            var title = "";
                            $.post(url,{_token: token,_id: id,_params: params}, function(result){
                                if(result.error == false){
                                    type = "success";
                                    title = "Operación realizada!";
                                }else{
                                    type = "error";
                                    title =  "No se pudo realizar la operación"
                                }
                                //swal({   title: title,   text: result.message,   type: type,   confirmButtonText: "Aceptar" });
                                location.reload();
                            }).fail(function (){
                                swal('No se pudo realizar la petición.');
                            });
                        }
                    });
        });

    });
</script>
