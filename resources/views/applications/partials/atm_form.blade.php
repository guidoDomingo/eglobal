<div class="box box-success">


    <div class="box-header with-border">
        <h3 class="box-title">Asignar Cajeros</h3>
    </div>
    <div class="box-body">
        <div id="form-alert-container">
            @if(Session::has('atm_form_message'))
                <div class="alert alert-success alert-dismissable">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h4><i class="icon fa fa-check"></i>Operación Exitosa</h4>
                    {{ Session::get('atm_form_message') }}
                </div>
            @endif
            @if(Session::has('atm_form_error_message'))
                <div class="alert alert-error alert-dismissable">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <h4><i class="icon fa fa-check"></i>Ocurrió un error en la solicitud</h4>
                    {{ Session::get('atm_form_error_message') }}
                </div>
            @endif
        </div>
        {!! Form::open(['route' => ['applications.assign_atm', $application->id ] , 'method' => 'POST', 'role' => 'form', 'id' => 'form-ws-request']) !!}
        <div class="form-group">
            {!! Form::label('atm', 'ATM') !!}
            {!! Form::select('pdv_id',$pdvs , $application->atm_id , ['class' => 'form-control chosen-select','placeholder' => 'Ninguno']) !!}
            {!! Form::hidden('owner_id', $application->owner_id) !!}
        </div>
        <button type="submit" id="wsrequest-submit" class="btn btn-primary">Guardar</button>
        {!! Form::close() !!}

    </div>
</div>
@section('page_scripts')
    @parent
    @include('applications.partials.js._js_scripts')
@endsection
@section('page_styles')
    @parent
    @include('applications.partials.css._css_styles')
@endsection