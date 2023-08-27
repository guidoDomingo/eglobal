<script type="text/javascript">
  $(document).ready(function () {
  	$('.btn-delete-form').click(function(e){
  	  	e.preventDefault(e);
  	  	swal({
                title: "Atención!",
                text: "Está a punto de borrar el registro, está seguro?. Los datos de su usuario quedarán registrados.",
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
              return true;
            }
          }
        );
    });
  });
</script>
