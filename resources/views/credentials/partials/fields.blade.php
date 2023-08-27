<div class="form-group">
    @if(isset($webservices))
        {!! Form::label('service_id', 'Proveedor') !!}
        {!! Form::select('service_id',$webservices ,null , ['class' => 'form-control object-type','placeholder' => 'Seleccione un Proveedor...']) !!}
    @endif
</div>
<div id="user_form">
<div class="form-group">
    {!! Form::label('user', 'Usuario') !!}
    {!! Form::text('user', null , ['class' => 'form-control', 'placeholder' => 'Usuario' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('password', 'Contraseña') !!}
    {!! Form::text('password', null , ['class' => 'form-control', 'placeholder' => 'Contraseña' ]) !!}
</div>
</div>
<div id="cnb_form" style="display:none">
<div class="form-group">
    {!! Form::label('cnbCode', 'Codigo CNB') !!}
    {!! Form::text('cnb_service_code', null , ['class' => 'form-control', 'placeholder' => '' ]) !!}
</div>
</div>
<div class="form-group" id="practipagoForm">
    {!! Form::label('codEntity', 'Cod. Entidad', ['id' => 'codEntity']) !!}
    {!! Form::text('codEntity', null , ['class' => 'form-control', 'placeholder' => 'Cod. Entidad' ]) !!}
</div>
<div id="pronet_form" style="display:none">
<div class="form-group">
    {!! Form::label('codBranch', 'Cod. Sucursal') !!}
    {!! Form::text('codBranch', null , ['class' => 'form-control', 'placeholder' => 'Cod. Sucursal' ]) !!}
</div>
<div class="form-group">
    {!! Form::label('codTerminal', 'Cod. Terminal') !!}
    {!! Form::text('codTerminal', null , ['class' => 'form-control', 'placeholder' => 'Cod. Terminal' ]) !!}
</div>
</div>


<a class="btn btn-default" href="{{ URL::previous() }}" role="button">Cancelar</a>
@section('page_scripts')
    @include('credentials.partials.js._js_scripts')
@endsection
@section('js')
<script>
    var service_id = $("#service_id").val();
    $("#practipagoForm").hide();
    if(service_id == 25){
        $("#pronet_form").show();
        $("#practipagoForm").show();
    }

    if(service_id == 2){
        $("#pronet_form").hide();
        $("#user_form").hide();
        $("#cnb_form").show();
    }

    if(service_id == 24){
        $("#pronet_form").hide();
        $("#cnb_form").hide();
        $("#user_form").show();
        $("#practipagoForm").show();
    }

    $("#service_id").change(function() {
        //pronet
        if($(this).val() == 25){
            $("#pronet_form").show();
            $("#practipagoForm").show();
            $("#codEntity").html('Cod. Entidad');
        }else{
            //practipago
            if($(this).val() == 24){    
                $("#codEntity").html('Session Id');
                $("#practipagoForm").show();
            }else{
                $("#practipagoForm").hide();
            }
            $("#pronet_form").hide();
        }
        //vision
        if($(this).val() == 2){
            $("#cnb_form").show();
            $("#user_form").hide();
        }else{
            $("#cnb_form").hide();
            $("#user_form").show();
        }

    });
</script>
@endsection