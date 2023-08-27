<script type="text/javascript">
    $(document).ready(function () {
        $('.btn-delete-atm').click(function(e){
            e.preventDefault();
            var row = $(this).parents('tr');
            var id = row.data('id');
            swal({
                        title: "Atención!",
                        text: "Está a punto de borrar el registro, está seguro?.",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "Si, eliminar!",
                        cancelButtonText: "No, cancelar!",
                        closeOnConfirm: true,
                        closeOnCancel: true
                    },
                    function(isConfirm){
                        if (isConfirm) {
                            var form = $('#form-asignatm-delete');
                            var url = form.attr('action').replace(':ROW_ID',id);
                            var data = form.serialize();
                            var type = "";
                            var title = "";
                            $.post(url,data, function(result){
                                if(result.error == false){
                                    row.fadeOut();
                                    type = "success";
                                    title = "Operación realizada!";
                                }else{
                                    type = "error";
                                    title =  "No se pudo realizar la operación"
                                }
                                swal({   title: title,   text: result.message,   type: type,   confirmButtonText: "Aceptar" });
                            }).fail(function (){
                                swal('No se pudo realizar la petición.');
                            });
                        }
                    });
        });

    });
</script>
