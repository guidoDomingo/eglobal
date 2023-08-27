@if(Session::has('message'))
    <div class="alert alert-success alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-check"></i>Operación Exitosa</h4>
        {{ Session::get('message') }}
    </div>
@endif
@if(Session::has('success'))
    <div class="alert alert-success alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-check"></i>Operación Exitosa</h4>
        {{ Session::get('message') }}
    </div>
@endif
@if(Session::has('error_message'))
    <div class="alert alert-error alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-check"></i>Ocurrió un error en la solicitud</h4>
        {{ Session::get('error_message') }}
    </div>
@endif
@if(Session::has('error'))
    <div class="alert alert-error alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
        <h4><i class="icon fa fa-check"></i>Ocurrió un error en la solicitud</h4>
        {{ Session::get('error_message') }}
    </div>
@endif

