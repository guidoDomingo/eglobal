<header class="main-header">
    <a href="/" class="logo">
        <span class="logo-mini"><b>EG</b>T</span>
        <span class="logo-lg"><b>Eglobal</b>T</span>
    </a>
    <nav class="navbar navbar-static-top" role="navigation">
        <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
            <span class="sr-only">Mostrar Menú</span>
        </a>
        <button class="btn btn-danger" title="Salir y cerrar sesión."
            style="float: right; margin-right: 10px; margin-top: 8px;" id="logout" onclick="location.href = '/logout'">
            <i class="fa fa-sign-out"></i> &nbsp; Salir
        </button>
    </nav>
</header>

@section('js')
    <!-- Iniciar -->
    <script type="text/javascript">
        /*$(document).ready(function() {
            $("#logout").click(function() {
                swal({
                        title: 'Atención',
                        text: '¿Salir y cerrar sesión?.',
                        type: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#0073b7',
                        confirmButtonText: 'Aceptar',
                        cancelButtonText: 'Cancelar',
                        closeOnClickOutside: false,
                        showLoaderOnConfirm: false
                    },
                    function(isConfirmMessage) {
                        if (isConfirmMessage) {
                            location.href = '/logout'
                        }
                    }
                );
            });
        });*/
    </script>
@endsection
