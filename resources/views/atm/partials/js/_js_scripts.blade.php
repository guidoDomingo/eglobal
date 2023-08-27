<script type="text/javascript">
    $(document).ready(function () {
        $('.select2').select2();
        /* On generate key button click*/
        $(document).on('click', '.btn-generate-key', function(e){
            e.preventDefault();
            var key_text_control = $(this).parent().find('.key');
            var id = $(this).parent().find(".key").attr("id");
            swal({
                        title: "Atención!",
                        text: "Está seguro que desea generar una nueva clave para este ATM?.",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#DD6B55",
                        confirmButtonText: "Si, generar!",
                        cancelButtonText: "No, cancelar!",
                        closeOnConfirm: true,
                        closeOnCancel: true
                    },
                    function(isConfirm){
                        if (isConfirm) {
                            var form = $('#form-generate-hash');
                            var url = form.attr('action');
                            var data = form.serialize();
                            $.post(url,data, function(result){
                                $("#"+id).val(result);
                            }).fail(function (){
                                swal('No se pudo realizar la petición.');
                            });

                        }
                    });
        });
    });
</script>
