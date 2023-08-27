@extends('layout')

@section('title')
    Caracteristicas
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Cliente: {{ $grupo->ruc .' | '. $grupo->description}}
            <small>Caracteristicas</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Gestor de terminales</a></li>
            <li><a href="#">Contrato</a></li>
            <li class="active">Modificar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Modificar</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._flashes')
                        @include('partials._messages')
                        {!! Form::model($caracteristica, ['route' => ['caracteristicas.update', $caracteristica->id ] , 'method' => 'PUT', 'id' => 'editarCaracteristicas-form']) !!}

                            <div style="padding: 1%">
                                
                                <div class="row">

                                    <div class="col-md-9">
                                
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {!! Form::label('banco_id', 'Banco') !!}
                                                    <div class="input-group">
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-institution"></i>
                                                        </div>  
                                                        {!! Form::select('banco_id',$bancos ,null , ['class' => 'form-control select2', 'placeholder' => 'Seleccione una opción' , 'style' => 'width:100%']) !!}
                                                    </div> 
                                                </div>
                                            </div>
                                
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {!! Form::label('tipo_cuenta', 'Tipo de cuenta') !!}
                                                    <div class="input-group">
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-institution"></i>
                                                        </div>  
                                                        {!! Form::select('tipo_cuenta',$tipo_cuentas, null , ['class' => 'form-control select2', 'placeholder' => 'Seleccione una opción' , 'style' => 'width:100%' ]) !!}
                                                    </div> 
                                                </div>
                                            </div>
                                
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {!! Form::label('nro_cuenta', 'Nro de cuenta') !!}
                                                    <div class="input-group">
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-list-ul"></i>
                                                        </div>  
                                                        {!! Form::text('nro_cuenta', $nro_cuenta[0]->nro_cuenta , ['class' => 'form-control', 'placeholder' => 'Nro de cuenta' ]) !!}
                                                    </div> 
                                                </div>
                                            </div>
                                
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {!! Form::label('canal_id', 'Canal de venta') !!}
                                                    <div class="input-group">
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-bullhorn"></i>
                                                        </div>  
                                                        {!! Form::select('canal_id',$canales ,null , ['class' => 'form-control select2', 'placeholder' => 'Seleccione una opción' , 'style' => 'width:100%']) !!}
                                                    </div> 
                                                </div>
                                            </div>
                                        </div>
                                
                                         <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {!! Form::label('categoria_id', 'Categoria del comercio') !!}
                                                    <div class="input-group">
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-list-ul"></i>
                                                        </div>  
                                                        {!! Form::select('categoria_id',$categorias ,null , ['class' => 'form-control select2', 'placeholder' => 'Seleccione una opción' , 'style' => 'width:100%']) !!}
                                                    </div> 
                                                </div>
                                            </div>
                                
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {!! Form::label('accesibilidad', 'Accesibilidad') !!}
                                                    <div class="input-group">
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-list-ul"></i>
                                                        </div>  
                                                        {!! Form::text('accesibilidad', null , ['class' => 'form-control', 'placeholder' => 'Accesibilidad' ]) !!}
                                                    </div> 
                                                </div>
                                            </div>
                                        </div>
                                
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {!! Form::label('referencia', 'Referencia') !!}
                                                    <div class="input-group">
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-list-ul"></i>
                                                        </div>  
                                                        {!! Form::text('referencia', null , ['class' => 'form-control', 'placeholder' => 'Referencia del lugar' ]) !!}
                                                    </div> 
                                                </div>
                                            </div>
                                
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {!! Form::label('visibilidad', 'Visibilidad') !!}
                                                    <div class="input-group">
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-list-ul"></i>
                                                        </div>  
                                                        {!! Form::text('visibilidad', null , ['class' => 'form-control', 'placeholder' => 'Visibilidad' ]) !!}
                                                    </div> 
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {!! Form::label('trafico', 'Trafico') !!}
                                                    <div class="input-group">
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-list-ul"></i>
                                                        </div>  
                                                        {!! Form::text('trafico', null , ['class' => 'form-control', 'placeholder' => 'Trafico' ]) !!}
                                                    </div> 
                                                </div>
                                            </div>
                                
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {!! Form::label('dueño', 'Dueño') !!}
                                                    <div class="input-group">
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-list-ul"></i>
                                                        </div>  
                                                        {!! Form::text('dueño', null , ['class' => 'form-control', 'placeholder' => 'Dueño' ]) !!}
                                                    </div> 
                                                </div>
                                            </div>
                                        </div>
                                
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {!! Form::label('atendido_por', 'Atendido por') !!}
                                                    <div class="input-group">
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-list-ul"></i>
                                                        </div>  
                                                        {!! Form::text('atendido_por', null , ['class' => 'form-control', 'placeholder' => 'encargado' ]) !!}
                                                    </div> 
                                                </div>
                                            </div>
                                
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {!! Form::label('estado_pop', 'Estado en el que se encuentra el POP') !!}
                                                    <div class="input-group">
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-list-ul"></i>
                                                        </div>  
                                                        {!! Form::text('estado_pop', null , ['class' => 'form-control', 'placeholder' => 'Estado del POP' ]) !!}
                                                    </div> 
                                                </div>
                                            </div>

                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    {!! Form::label('correo', 'Correo') !!}
                                                    <div class="input-group">
                                                        <div class="input-group-addon">
                                                            <i class="fa fa-envelope-o"></i>
                                                        </div>  
                                                        {!! Form::email('correo', null , ['class' => 'form-control', 'placeholder' => 'Correo del cliente' ]) !!}
                                                    </div> 
                                                </div>
                                            </div>
                                        </div>     
                                
                                    </div>
                                
                                    <div class="col-md-3" style="border:solid 1px; padding: 25px;  border-radius: 15px">
                                        <i class="fa fa-check-square-o" aria-hidden="true"></i>                                
                                        {!! Form::label('cuestionario', 'Cuestionario',['style' => 'font-weight:bold']) !!}                
                                                                
                                        <div class="form-check">
                                            {!! Form::checkbox('permite_pop', 1, $caracteristica->permite_pop ? true : false) !!}</span>
                                            {!! Form::label('permite_pop', 'Permite POP ?') !!}
                                        </div> 
                                        <div class="form-check">
                                            {!! Form::checkbox('tiene_pop', 1, $caracteristica->tiene_pop ? true : false) !!}</span>
                                            {!! Form::label('tiene_pop', ' Tiene POP ?') !!}
                                        </div>
                                        <div class="form-check">
                                            {!! Form::checkbox('tiene_bancard', 1, $caracteristica->tiene_bancard ? true : false) !!}</span>
                                            {!! Form::label('tiene_bancard', 'Tiene BANCARD ?') !!}
                                        </div> 
                                        <div class="form-check">
                                            {!! Form::checkbox('tiene_pronet', 1, $caracteristica->tiene_pronet ? true : false) !!}</span>
                                            {!! Form::label('tiene_pronet', ' Tiene PRONET ?') !!}
                                        </div> 
                                        <div class="form-check">
                                            {!! Form::checkbox('tiene_netel', 1, $caracteristica->tiene_netel ? true : false) !!}</span>
                                            {!! Form::label('tiene_netel', 'Tiene NETEL?') !!}
                                        </div> 
                                        <div class="form-check">
                                            {!! Form::checkbox('tiene_pos_dinelco', 1, $caracteristica->tiene_pos_dinelco ? true : false) !!}</span>
                                            {!! Form::label('tiene_pos_dinelco', 'Tiene POS DINELCO ?') !!}
                                        </div> 
                                        <div class="form-check">
                                            {!! Form::checkbox('tiene_pos_bancard', 1, $caracteristica->tiene_pos_bancard ? true : false) !!}</span>
                                            {!! Form::label('tiene_pos_bancard', 'Tiene POS BANCARD ?') !!}
                                        </div> 
                                        <div class="form-check">
                                            {!! Form::checkbox('tiene_billetaje', 1, $caracteristica->tiene_billetaje ? true : false) !!}</span>
                                            {!! Form::label('tiene_billetaje', 'Tiene BILLETAJE ?') !!}
                                        </div> 
                                        <div class="form-check">
                                            {!! Form::checkbox('tiene_tm_telefonito', 1, $caracteristica->tiene_tm_telefonito ? true : false) !!}</span>
                                            {!! Form::label('tiene_tm_telefonito', 'Tiene tm Telefonito?') !!}
                                        </div> 

                                        <i class="fa fa-sitemap" aria-hidden="true"></i>
                                        {!! Form::label('segmentacion', 'Segmentación de clientes', ['style' => 'font-weight:bold']) !!}        

                                        <div class="form-check">
                                            {!! Form::checkbox('visicooler', 1, $caracteristica->visicooler ? true : false) !!}</span>
                                            {!! Form::label('visicooler', 'Cuenta con Visicooler?') !!}
                                        </div> 
                                        <div class="form-check">
                                            {!! Form::checkbox('bebidas_alcohol', 1, $caracteristica->bebidas_alcohol ? true : false) !!}</span>
                                            {!! Form::label('bebidas_alcohol', 'Vende bebidas con alcohol?') !!}
                                        </div> 
                                        <div class="form-check">
                                            {!! Form::checkbox('bebidas_gasificadas', 1, $caracteristica->bebidas_gasificadas ? true : false) !!}</span>
                                            {!! Form::label('bebidas_gasificadas', 'Vende bebidas gasificadas?') !!}
                                        </div> 
                                        <div class="form-check">
                                            {!! Form::checkbox('productos_limpieza', 1, $caracteristica->productos_limpieza ? true : false) !!}</span>
                                            {!! Form::label('productos_limpieza', 'Vende productos de limpieza?') !!}
                                        </div> 

                                
                                    </div>
                                    {{-- {!! Form::hidden('group_id', $group_id) !!} --}}
                                
                                </div>

                            </div>

                            <div class="form-row">
                                <a class="btn btn-default" href="{{ route('caracteristicas.show',['id' => $grupo->id]) }}" role="button">Cancelar</a>
                                <button type="submit" class="btn btn-primary">Guardar</button>
                            </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@include('partials._selectize')

@section('js')
<link href="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker-bs3.css" rel="stylesheet" type="text/css" />
<script src="/bower_components/admin-lte/plugins/daterangepicker/moment.min.js"></script>
<script src="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker.js"></script>
<script src="/bower_components/admin-lte/plugins/jquery-validation/dist/jquery.validate.min.js"></script>
<script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>
<script type="text/javascript">
    $('.select2').select2();

    //validacion formulario 
    $('#editarCaracteristicas-form').validate({
        rules: {
            "number": {
                required: true,
            },
            "group_id": {
                required: true,
            },
            "contract_type": {
                required: true,
            },
            "credit_limit": {
                required: true,
            },
            "reservationtime": {
                required: true,
            },
        },
        messages: {
            "number": {
                required: "Ingrese el numero de contrato.",
            },
            "group_id": {
                required: "Seleccione el grupo.",
            },
            "contract_type": {
                required: "Seleccione el tipo de contrato.",
            },
            "credit_limit": {
                required: "Ingrese la linea de crédito.",
            },
            "reservationtime": {
                required: "Ingrese el rango de vigencia.",
            },
        },
        errorPlacement: function (error, element) {
            error.appendTo(element.parent());
        }
    });

    

  
</script>

@endsection

@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />

    <style>

        label span {
            font-size: 1rem;
        }

        label.error {
            color: red;
            font-size: 1rem;
            display: block;
            margin-top: 5px;
        }

        input.error {
            border: 1px dashed red;
            font-weight: 300;
            color: red;
        }
        .borderd-content {
            border: 1px solid #a1a1ac;
            border-radius: 4px;
            height: 300px;
            margin-top: 20px;
            position: relative;
        }
        .borderd-content .title {
            margin: -25px 0 0 50px;
            background: #fff;
            padding: 3px;
            display: inline-block;
            font-weight: bold;
            position: absolute;
        }
    </style>
@endsection