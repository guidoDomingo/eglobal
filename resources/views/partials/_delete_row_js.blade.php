<script type="text/javascript">
    $(document).ready(function() {
        $('.btn-delete').click(function(e) {
            e.preventDefault();
            var row = $(this).parents('tr');
            var id = row.data('id');
            console.log(id);
            Swal.fire({
                title: "Atención!",
                text: "Está a punto de borrar el registro, está seguro?.",
                type: "warning",
                showCancelButton: true,
                confirmButtonColor: "#DD6B55",
                confirmButtonText: "Si, eliminar!",
                cancelButtonText: "No, cancelar!",
                closeOnConfirm: true,
                closeOnCancel: true
            }).then((result) => {
                if (result.isConfirmed) {
                    var form = $('#form-delete');
                    var url = form.attr('action').replace(':ROW_ID', id);
                    console.log(url);

                    var data = form.serialize();
                    console.log(data);

                    var type = "";
                    var title = "";
                    $.post(url, data, function(result) {
                        if (result.error == false) {
                            row.fadeOut();
                            type = "success";
                            title = "Operación realizada!";
                        } else {
                            type = "error";
                            title = "No se pudo realizar la operación"
                        }
                        Swal.fire({
                            title: title,
                            text: result.message,
                            type: type,
                            confirmButtonText: "Aceptar"
                        });
                    }).fail(function() {
                        Swal.fire('No se pudo realizar la petición.');
                    });
                }
            })
        })

    });
</script>
