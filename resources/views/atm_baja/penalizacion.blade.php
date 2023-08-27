@extends('layout')

@section('title')
    BAJA | Generar Factura
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Generar Factura de penalización
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Gestor de terminales</a></li>
            <li><a href="#">Baja</a></li>
            <li class="active">Generar Factura</li>
        </ol>
    </section>
    <section class="content">
        <div id="myModal" class="modal fade" role="dialog">
            <div class="modal-dialog">
                <!-- Modal content-->
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title factura_titulo" style="display: none;">Seleccione una Factura para agregar</h4>
                        <h4 class="modal-title saldo_titulo" style="display: none;">Sucursales del grupo : <label class="grupo"></label></h4>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="text-center" id="cargando" style="margin: 50px 10px"> {{-- clase para bloquear el div y mostrar el loading --}}
                        <i class="fa fa-refresh fa-spin" style="font-size:24px"></i>
                    </div>
                    <div class="modal-body">
                        <table id="detalles" class="table table-bordered table-hover dataTable" role="grid"
                            aria-describedby="Table1_info">
                            <thead>
                                <tr role="row">
                                    <p class="mensaje" style="display: none; color: red" >Favor seleccionar un tipo de multa valido</p>
                                    <th class="sorting_disabled titulo_tipo_multa" style="display: none;" rowspan="1" colspan="1" width="150px">Tipo de multa:</th>
                                </tr>
                                <tr role="row" class="cabeceras_atms" style="display:none;">
                                    <th style="display:none;" class="sorting_disabled" rowspan="1" colspan="1"></th>
                                    <th style="display:none;" class="sorting_disabled" rowspan="1" colspan="1"></th>
                                    <th class="sorting_disabled" rowspan="1" colspan="1">#</th>
                                    <th class="sorting_disabled" rowspan="1" colspan="1">Sucursal</th>
                                    <th class="sorting_disabled" rowspan="1" colspan="1">Ultimo Uso</th>
                                    <th class="sorting_disabled" rowspan="1" colspan="1">Saldo</th>
                                    <th class="sorting_disabled" rowspan="1" colspan="1">Estado</th>
                                </tr>
                            </thead>
                            <tbody id="modal-contenido">
                                
                            </tbody>
                        </table>
                    </div>
                    <div class="modal-footer">
                        <!--para Cancelar sin hacer nada -->
                        <button type="button" class="btn btn-primary pull-left" id="show_form">Aceptar</button>
                        <button type="button" class="btn btn-default pull-right" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </div>
        </div>
        <div id="modal_detail" class="modal fade" role="dialog">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                  <div class="modal-header">
                    <h4 class="modal-title"><b>Detalles Finales de las Facturas</b></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                    </button>
                  </div>
                  <div class="modal-body">
                    <div class="container-fluid">
                        {{--<table class="grid-container_detail">
                            <tr>
                                <th class="grid-item">
                                    <h5 class="receipt-titulo">
                                        Portales Web
                                    </h5>
                                    <h5 class="receipt-titulo">
                                        Telecomunicaciones
                                    </h5>
                                    <br>
                                    <h6 class="receipt-titulo">
                                        Prof. Chavez Nº 273 c/ Dr. Bestard
                                    </h6>
                                    <h6 class="receipt-titulo">
                                        Tel.: (021) 2376740
                                    </h6>
                                    <h6 class="receipt-titulo">
                                        Asunción - Paraguay
                                    </h6>
                                </th>
                                <th class="grid-item grid-right">
                                    <div class="pull-right receipt-section">
                                        <span>RUC: 80083484-4</span><br>
                                        <span class="text-large"><strong>Timbrado Nº</strong></span>
                                        <h6 class="receipt-titulo">
                                            Válido desde:
                                        </h6>
                                        <h6 class="receipt-titulo">
                                            Válido hasta:
                                        </h6>
                                        <span class="text-large"><strong>F A C T U R A</strong></span><br>
                                        <span class="text-large">Nº</span>
                                    </div>
                                </th>
                            </tr>
                        </table>--}}
                        <div class="row">
                            <div class="col-md-8">
                                <div class="cont_detail">
                                    <h5 class="grid-container_detail titulo_detail border"><b>Detalles del Cliente</b></h5>
                                    <ul>
                                        <li> <strong> Cliente: {{$grupo->description}} </strong> </li>
                                        <li><strong> Ruc: </strong> {{$grupo->ruc}}</li>
                                        <li><strong> Dirección: </strong> {{$grupo->direccion}}</li>
                                        <li><strong> Teléfono: </strong> {{$grupo->telefono}}</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="cont_detail">
                                    <h5 class="grid-container_detail titulo_detail border"><b>Detalles del ATM</b></h5>
                                    <ul>
                                        <li> <strong id="name_atm"> Nombre: RCJA </strong> </li>
                                    </ul>
                                </div>
                                <div>
                                    <h4 class="grid-container_detail titulo_detail border"><b>SALDO:</b></h4>
                                    <h4><b>{{number_format($saldo_cliente, 0,'.','.')}} Gs.</b></h4>
                                </div>
                            </div>
                        </div>
                        <br>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="cont_detail">
                                    <table class="table table-bordered table-hover dataTable" role="grid">
                                        <thead>
                                          <tr>
                                            <th scope="col-2">Producto</th>
                                            <th scope="col-3">Descripcion</th>
                                            <th scope="col-2">Monto de la Multa </th>
                                            <th scope="col-2">Quita de Descuento</th>
                                            <th scope="col-3">TOTAL A PAGAR</th>
                                          </tr>
                                        </thead>
                                        <tbody id="details_facturas">
                                          {{--<tr>
                                            <td>INT103</td>
                                            <td>MULTA POR INTERES PUNITORIO</td>
                                            <td>219.000</td>
                                            <td>0</td>
                                            <th>219.000</th>
                                          </tr>
                                          <tr>
                                            <td>INT104</td>
                                            <td>MULTA MORATORIA</td>
                                            <td>43.800</td>
                                            <td>5.000</td>
                                            <th>38.800</th>
                                          </tr>
                                          <tr>
                                            <td>INT105</td>
                                            <td>MULTA MORATORIA + PUNITORIO</td>
                                            <td>56.940</td>
                                            <td>0</td>
                                            <th>56.940</th>
                                          </tr>
                                          <tr style="border-top: 3px solid rgb(186, 186, 186);">
                                            <th style="text-align: right; border-top: 2px solid rgb(186, 186, 186);" colspan="4">MONTO TOTAL A PAGAR</th>
                                            <th style="border-top: 2px solid rgb(186, 186, 186);" colspan="1">314.740 Gs.</th>
                                          </tr>--}}
                                        </tbody>
                                        
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                  </div>
                  <div class="modal-footer">
                    {!! Form::open(['route' => ['multas.store'] , 'method' => 'POST', 'role' => 'form', 'id'=>'multas-form']) !!}
                        {{--{!! Form::text('cadena',  null , ['class' => 'form-control', 'id' => 'cadena'  ]) !!}--}}}
                        {!! Form::hidden('cadena', null, ['id' => 'cadena']) !!}
                        <button type="submit" class="btn btn-primary pull-left">Guardar Factura/s</button>
                    {!! Form::close() !!}
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                  </div>
                </div>
              </div>
            <style>
                .cont_detail {
                    border: 1px solid rgb(186, 186, 186);
                    border-radius: 4px;
                    /*padding-left: 10px;*/
                }

                .grid-container_detail {
                    border-bottom: 1px solid rgb(186, 186, 186);
                }

                .titulo_detail{
                    padding-left: 5px;
                    padding-bottom: 5px;
                }
            </style>
        </div>
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
            
                    <div class="box-body">
                        {{-- @include('partials._flashes') --}}
                        @include('partials._messages')

                        {{--{!! Form::open(['route' => ['multas.store'] , 'method' => 'POST', 'role' => 'form', 'id'=>'multas-form']) !!}--}}
                            <div class="box-body no-padding">
                                <div class="row">
                                    <div class="col-xs-12">
                                        <div class="form-row">
                                            <div class="form-group col-md-12 borderd-info">
                                                <div class="title"><h4>&nbsp;<i class="fa fa-info-circle"></i>&nbsp;INFO &nbsp;</h4></div>
                                                <div class="container-info">
                                                
                                        
                                                    <div class="form-group col-md-12">
                                                        {!! Form::label('ruc', 'RUC/CI:') !!}
                                                        <div class="input-group">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-pencil-square-o"></i>
                                                            </div>
                                                            {!! Form::text('ruc', $grupo->ruc , ['class' => 'form-control', 'disabled' =>'disabled' ]) !!}
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-12">
                                                        {!! Form::label('group', 'Cliente:') !!}
                                                        <div class="input-group">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-pencil-square-o"></i>
                                                            </div>
                                                            {!! Form::text('group', $grupo->description , ['class' => 'form-control', 'disabled' =>'disabled', 'id' => 'descripcion_grupo' ]) !!}
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-12">
                                                        {!! Form::label('direccion', 'Dirección:') !!}
                                                        <div class="input-group">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-pencil-square-o"></i>
                                                            </div>
                                                            {!! Form::text('direccion', $grupo->direccion , ['class' => 'form-control', 'disabled' =>'disabled' ]) !!}
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-12">
                                                        {!! Form::label('telefono', 'Teléfono:') !!}
                                                        <div class="input-group">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-pencil-square-o"></i>
                                                            </div>
                                                            {!! Form::text('telefono', $grupo->telefono , ['class' => 'form-control', 'disabled' =>'disabled' ]) !!}
                                                        </div>
                                                    </div>
                                                    <p class="mensaje_atm" style="display: none; color: red" >Favor seleccione un ATM valido.</p>
                                                    <div class="form-group col-md-12">
                                                        {!! Form::label('atm', 'ATM', ['class' => 'col-xs-2']) !!}
                                                        {{--{!! Form::label('grupo', 'Cliente') !!}--}}
                                                        {!! Form::select('atm_id', $data_select, $atm_id, ['id' => 'atm_id', 'class' => 'form-control select2']) !!}
                                                    </div>

                                                    <div class="form-group col-md-12">
                                                        {!! Form::label('importe', 'SALDO PENDIENTE DEL CLIENTE (Gs.)') !!}
                                                        <div class="input-group">
                                                            <div class="input-group-addon">
                                                                <buttom class="btn-default btn-xs" title="Mostrar atms">
                                                                    <i class="pay-info fa fa-info-circle" style="cursor:pointer"></i>
                                                                </buttom>
                                                            </div>
                                                            
                                                            {!! Form::text('importe',  $saldo_cliente , ['class' => 'form-control', 'readonly' => true, 'id' => 'saldo_cliente' ]) !!}
                                                        </div>
                                                    </div>                                                    
                                                    {{--<div class="form-group col-md-12">
                                                        <div class="panel panel-default">
                                                            <div class="panel-heading"><b>ATMs asociados</b></div>
                                                            <table id="listadoAtms" class="table table-hover table-bordered table-condensed">
                                                                <thead>
                                                                    <tr>
                                                                      <th scope="col">#</th>
                                                                      <th scope="col">Codigo</th>
                                                                      <th scope="col">ATM</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                   @foreach ($atm_list as $item )
                                                                   <tr data-id="{{ $item->id  }}">
                                                                        <td>{{ $item->id }}</td>
                                                                        <td>{{ $item->code }}</td>
                                                                        <td>{{ $item->name }}</td>
                                                                    </td>
                                                                   @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    </div>--}}


                                                </div> 
                                            </div> 
                                        </div>
                                        <div class="form-row">
                                            @foreach ($penalty_types as $penalty_type )
                                                {!! Form::hidden('titulo_multa_'.$penalty_type->id, strtoupper($penalty_type->description), ['id' => 'titulo_multa_'.$penalty_type->id]) !!}
                                                <div class="form-group col-md-6 borderd-campaing height-ajust" id={{'form_penalizacion_'.$penalty_type->id}} style='display: none;'>
                                                    <div class="title"><h4>&nbsp;<i class="fa fa-history"></i>&nbsp; {{ strtoupper($penalty_type->description) }} &nbsp;</h4></div>
                                                    <div class="container-campaing">
                                            
                                                        <div class="form-row">
                                                        
                                                            <div class="form-group col-md-6">
                                                                {!! Form::label('saldo_mora', 'SALDO MORA') !!}
                                                                {!! Form::text('saldo_mora',   number_format($saldo_cliente, 0,'.','.') , ['class' => 'form-control', 'readonly' => true, 'id' => 'saldo_mora_'.$penalty_type->id ]) !!}
                                                            </div>
                                                            {{--<div class="form-group col-md-6">
                                                                {!! Form::label('fecha_vencimiento', 'FECHA') !!}
                                                                {!! Form::date('fecha_aux', new \DateTime(), ['class' => 'form-control','disabled' => 'disabled']) !!}
                                                                <input id="fecha_vencimiento" name="fecha_vencimiento" type="hidden" value="{!!\Carbon\Carbon::now()!!}">
                                                            </div>--}}
                                                            <div class="form-group col-md-6">
                                                                {!! Form::label('idproducto', 'PRODUCTO') !!}
                                                                {!! Form::text('idproducto_'.$penalty_type->id,  $penalty_type->cod_product , ['class' => 'form-control', 'readonly' => true, 'id' => 'idproducto_'.$penalty_type->id ]) !!}
                                                            </div>
                                                            <div class="form-group col-md-6">
                                                                {!! Form::label('debit_number', 'Monto a debitar') !!}
                                                                <div class="input-group">
                                                                    @if($penalty_type->percent_amount == 'pe')
                                                                        <div class="input-group-addon">
                                                                            <b>%</b>
                                                                        </div>
                                                                        {!! Form::number('debit_number_'.$penalty_type->id,  $penalty_type->amount_to_affected , ['class' => 'form-control', 'readonly' => true, 'id' => 'debit_number_'.$penalty_type->id  ]) !!}
                                                                    @else
                                                                        <div class="input-group-addon">
                                                                            <i class="fa fa-money"></i>
                                                                        </div>
                                                                        @if(in_array($penalty_type->id, $debit_fijo))
                                                                            {!! Form::number('debit_number_'.$penalty_type->id,  $penalty_type->amount_to_affected , ['class' => 'form-control', 'readonly' => true, 'id' => 'debit_number_'.$penalty_type->id  ]) !!}
                                                                        @else
                                                                            {!! Form::number('debit_number_'.$penalty_type->id,  $penalty_type->amount_to_affected , ['class' => 'form-control', 'id' => 'debit_number_'.$penalty_type->id  ]) !!}
                                                                        @endif 
                                                                    @endif  
                                                                </div>
                                                                <div style="display: none">
                                                                    {!! Form::number('number_original_'.$penalty_type->id,  $penalty_type->amount_original , ['class' => 'form-control', 'readonly' => true, 'id' => 'number_original_'.$penalty_type->id  ]) !!}
                                                                </div>
                                                            </div>
                                                            @if(in_array($penalty_type->id, $ar_porcentaje))
                                                                <div class="form-group col-md-6">
                                                                    {!! Form::label('cant_meses', 'CANTIDAD DE MESES') !!}
                                                                    {!! Form::number('cant_meses',  $last_sale , ['class' => 'form-control', 'id' => 'cant_meses_'.$penalty_type->id ]) !!}
                                                                </div>
                                                            @endif
                                                            @if($penalty_type->id == 5)
                                                                <div class="form-group col-md-6">
                                                                    {!! Form::label('cant_dias', 'CANTIDAD DE DIAS') !!}
                                                                    {!! Form::number('cant_dias',  1 , ['class' => 'form-control', 'id' => 'cant_dias_'.$penalty_type->id ]) !!}
                                                                </div>
                                                            @endif
                                                            <div class="form-group col-md-6">
                                                                {!! Form::label('amount_penalty', 'Monto de la Multa') !!}
                                                                {!! Form::text('amount_penalty_'.$penalty_type->id, number_format($penalty_type->amount_penalty, 0,'.','.') , ['class' => 'form-control', 'readonly' => true, 'id' => 'amount_penalty_'.$penalty_type->id ]) !!}
                                                            </div>
                                                            @if($penalty_type->id == 3)
                                                                <div class="form-group col-md-6">
                                                                    {!! Form::label('debit_number', 'Monto de Interes Punitorio') !!}
                                                                    <div class="input-group">
                                                                        <div class="input-group-addon">
                                                                            @if($penalty_type->percent_amount == 'pe')
                                                                                <b>%</b>
                                                                            @else
                                                                                <i class="fa fa-money"></i>
                                                                            @endif    
                                                                        </div>
                                                                        {!! Form::number('debit_punitorio_'.$penalty_type->id, $penalty_type->punitorio , ['class' => 'form-control', 'readonly' => true, 'id' => 'debit_punitorio_'.$penalty_type->id  ]) !!}
                                                                    </div>
                                                                </div>
                                                                <div class="form-group col-md-6">
                                                                    {!! Form::label('amount_penalty', 'Monto más el Interes Punitorio') !!}
                                                                    {!! Form::text('amount_punitorio_'.$penalty_type->id, number_format($penalty_type->amount_punitorio, 0,'.','.') , ['class' => 'form-control', 'readonly' => true, 'id' => 'amount_punitorio_'.$penalty_type->id ]) !!}
                                                                </div>
                                                            @endif
                                                            <div class="form-group col-md-6">
                                                                {!! Form::label('quita', 'QUITA DE DESCUENTO') !!}
                                                                {!! Form::number('quita_'.$penalty_type->id,   0, ['class' => 'form-control', 'id' => 'quita_'.$penalty_type->id ]) !!}
                                                            </div>
                                                            <div class="form-group col-md-6">
                                                                {!! Form::label('amount_total_to_pay', 'Multa Total a Pagar') !!}
                                                                {!! Form::text('amount_total_to_pay_'.$penalty_type->id,  number_format($penalty_type->total_to_pay, 0,'.','.') , ['class' => 'form-control', 'readonly' => true, 'id' => 'amount_total_to_pay_'.$penalty_type->id ]) !!}
                                                            </div>
                                                            <div class="form-group col-md-12">
                                                                {!! Form::label('observacion', 'Observación') !!}
                                                                {!! Form::textarea('observacion'.$penalty_type->id,  null , ['class' => 'form-control', 'rows' => "1", 'id' => 'observacion'.$penalty_type->id ]) !!}
                                                            </div>
                                                            <div class="form-group col-md-12"  style="text-align: center">
                                                                <a class="btn btn-danger btn-l btn-circle" id={{'btn_del_'.$penalty_type->id}} title="Generar Factura" role="button" style="text-align: center">
                                                                    <span class="fa fa-remove"></span> &nbsp; Borrar
                                                                </a>
                                                            </div>
                                                            {{--<button type="submit" class="btn btn-primary pull-right" id="btn_generar">GENERAR</button>--}}
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                            {!! Form::hidden('group_id', $grupo->id, ['id' => 'group_id']) !!}
                                        </div>
                                        <div class="form-row">
                                            <div class="form-group col-md-6"  style="text-align: center; margin-top: 50px;">
                                            <a class="btn btn-success btn-xl btn-circle btn-agg" title="Generar Factura" role="button" style="text-align: center">
                                                <span class="fa fa-plus"></span> &nbsp; Añadir Factura
                                            </a>
                                            </div>
                                        </div>
                                        <div class="form-row" style='display: none;'>
                                            <div class="form-group col-md-6 borderd-campaing">
                                                <div class="title"><h4>&nbsp;<i class="fa fa-history"></i>&nbsp; MULTA POR PENALIZACIÓN &nbsp;</h4></div>
                                                <div class="container-campaing">
                                        
                                                    <div class="form-row">
                                                     
                                                        <div class="form-group col-md-6">
                                                            {!! Form::label('pdv', 'PDV') !!}
                                                            {!! Form::text('pdv',   $grupo->ruc , ['class' => 'form-control', 'readonly' => true ]) !!}
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            {!! Form::label('fecha_vencimiento', 'FECHA') !!}
                                                            {!! Form::date('fecha_aux', new \DateTime(), ['class' => 'form-control','disabled' => 'disabled']) !!}
                                                            <input id="fecha_vencimiento" name="fecha_vencimiento" type="hidden" value="{!!\Carbon\Carbon::now()!!}">
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <div class="form-group">
                                                                {!! Form::label('idproducto', 'PRODUCTO') !!}
                                                                {!! Form::text('idproducto',  'INT103' , ['class' => 'form-control', 'readonly' => true ]) !!}
                                                            </div>
                                                            <div class="form-group">
                                                                {!! Form::label('importe', 'IMPORTE (Gs.)') !!}
                                                                {!! Form::text('importe',  1050000 , ['class' => 'form-control', 'readonly' => true ]) !!}
                                                            </div>
                                                        </div>
                                                        <div class="form-group col-md-6">
                                                            <div class="form-group">
                                                                {!! Form::label('imei', 'IMEI') !!}
                                                                {!! Form::text('imei', 'Vacío' , ['class' => 'form-control', 'readonly' => true ]) !!}
                                                            </div>
                                                        </div>
                                            
                                                        {{--<button type="submit" class="btn btn-primary pull-right" id="btn_generar">GENERAR</button>--}}

                                                        <div class="form-row">
                                                            <div class="form-group col-md-12 factura-generada" style="margin-top: 10%">
                                                                <div class="title"><h4>&nbsp;<i class="fa fa-check-square-o"></i>&nbsp; FACTURA GENERADA EGLOBALT | ONDANET&nbsp;</h4></div>
                                                                <div class="container-multa">
                                                                    <div class="form-row">
                                                                       
                                                                        <table class="table table-hover table-bordered table-condensed">
                                                                            <thead>
                                                                                <tr>
                                                                                  <th scope="col">ID#</th>
                                                                                  <th scope="col" style="vertical-align:middle; text-align:center;">Status</th>
                                                                                  <th scope="col" style="vertical-align:middle; text-align:center;">Nro de venta</th>
                                                                                  <th scope="col" style="vertical-align:middle; text-align:center;">Fecha Creacion</th>
                                                                                  <th scope="col" style="vertical-align:middle; text-align:center;">Importe</th>
                                                                                  {{-- <th scope="col" style="vertical-align:middle; text-align:center;">Saldo</th> --}}
                                                                                </tr>
                                                                            </thead>
                                                                            <tbody>
                                                                                {{--@if(isset($multas))
                                                                                    @foreach ($multas as $item )
                                                                                    <tr data-id="{{ $item->id  }}">
                                                                                            <td>{{ $item->id }}</td>
                                                                                            <td style="vertical-align:middle; text-align:center;">{{ $item->status }}</td>
                                                                                            <td style="vertical-align:middle; text-align:center;">{{ $item->nro_venta }}</td>
                                                                                            <td style="vertical-align:middle; text-align:center;">{{ date('d/m/Y', strtotime($item->creado)) }}</td>
                                                                                            <td style="vertical-align:middle;  text-align:center;">{{ number_format($item->total_multa) }}</td>
                                                                                        </td>
                                                                                    @endforeach
                                                                                @endif--}}
                                                                            </tbody>
                                                                        </table>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                    </div>
                                                </div>
                                            </div>
                                            {!! Form::hidden('group_id', $grupo->id) !!}
                                        </div>
                                    </div>  
                                </div>
                            </div>
                            <div class="box-footer">
                                <button class="btn btn-primary pull-left" id="btn_generar" disabled>GENERAR</button>
                                <a class="btn btn-default pull-right" href="{{ url('atm/new/'.$grupo->id.'/groups_atms')}}" role="button">Atrás</a>
                            </div>

                            <div class="form-row">
                                <div class="form-group col-md-12 factura-generada">
                                    <div class="title"><h4>&nbsp;<i class="fa fa-check-square-o"></i>&nbsp; FACTURA GENERADA EGLOBALT | ONDANET&nbsp;</h4></div>
                                    <div class="container-multa">
                                        <div class="form-row">
                                           
                                            <table class="table table-hover table-bordered table-condensed">
                                                <thead>
                                                    <tr>
                                                      <th scope="col">ID#</th>
                                                      <th scope="col" style="vertical-align:middle; text-align:center;">ATM</th>
                                                      <th scope="col" style="vertical-align:middle; text-align:center;">Tipo Multa</th>
                                                      <th scope="col" style="vertical-align:middle; text-align:center;">Status</th>
                                                      <th scope="col" style="vertical-align:middle; text-align:center;">Nro de venta</th>
                                                      <th scope="col" style="vertical-align:middle; text-align:center;">Fecha Creación</th>
                                                      <th scope="col" style="vertical-align:middle; text-align:center;">Importe</th>
                                                      <th scope="col" style="vertical-align:middle; text-align:center;">observación</th> 
                                                      {{-- <th scope="col" style="vertical-align:middle; text-align:center;">Saldo</th> --}}
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @if(isset($multas))
                                                        @foreach ($multas as $item )
                                                        <tr data-id="{{ $item->id  }}">
                                                                <td>{{ $item->id }}</td>
                                                                <td style="vertical-align:middle; text-align:center;">{{ $item->atm }}</td>
                                                                <td style="vertical-align:middle; text-align:center;">{{ $item->tipo_multa }}</td>
                                                                <td style="vertical-align:middle; text-align:center;">{{ $item->status }}</td>
                                                                <td style="vertical-align:middle; text-align:center;">{{ $item->nro_venta }}</td>
                                                                <td style="vertical-align:middle; text-align:center;">{{ date('d/m/Y', strtotime($item->creado)) }}</td>
                                                                <td style="vertical-align:middle;  text-align:center;">{{ number_format($item->total_multa) }}</td>
                                                                <td style="vertical-align:middle; text-align:center;">{{ $item->observation }}</td>
                                                            </td>
                                                        @endforeach
                                                    @endif
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {{--{!! Form::close() !!}--}}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@include('partials._selectize')

@section('js')
<script src="/bower_components/admin-lte/plugins/jquery-validation/dist/jquery.validate.min.js"></script>
<script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>

<link rel="stylesheet" href="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.css">
<script src="/bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.min.js"></script>
{{-- <script src="//cdn.jsdelivr.net/npm/sweetalert2@11"></script> --}}

<script type="text/javascript">
    $(document).ready(function () {
         //separador de miles - Capital de la poliza
         var separadorPol = document.getElementById('importe');
        separadorPol.addEventListener('input', (e) => {
            var entradaPol = e.target.value.split(','),
            parteEnteraPol = entradaPol[0].replace(/\./g, ''),
            salidaPol = parteEnteraPol.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
            e.target.value = salidaPol;
        }, false);
        var importe = document.getElementById('importe').value;
        entryPoliza = importe.split(',');
        partEnteraPoliza = entryPoliza[0].replace(/\./g, ''),
        outputPoliza = partEnteraPoliza.replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1.");
        //insertar valor con separadores de miles
        document.getElementById("importe").value = outputPoliza;
    });

</script>  


<script type="text/javascript">
    $('.select2').select2();

    $('#listadoAtms').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": false,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "displayLength": 3,
            "language":{"url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"},
            "bInfo" : false
    });

    $('.btn-agg').on('click', function(e) {
        e.preventDefault();

        $("#modal-contenido").html('');
        $('#cargando').show();
        $("#detalles").hide();
        $('#show_form').show();
        $('.factura_titulo').show();
        $('.saldo_titulo').hide();
        $('.cabeceras_atms').hide();
        $('.titulo_tipo_multa').show();
        
        $.get('/penalizacion/add_penalty/', function(data) {
            console.log(data);
            $('#cargando').hide();
            $("#modal-contenido").html(data['payment_info']);
            $("#detalles").show();
            $("#myModal").modal();
            $('.mensaje').hide();
        });
    });

    $('#btn_del_1').on('click', function(e) {
        $('#form_penalizacion_1').hide();
        disable_button(1);
    });
    $('#btn_del_2').on('click', function(e) {
        $('#form_penalizacion_2').hide();
        disable_button(2);
    });
    $('#btn_del_3').on('click', function(e) {
        $('#form_penalizacion_3').hide();
        disable_button(3);
    });
    $('#btn_del_4').on('click', function(e) {
        $('#form_penalizacion_4').hide();
        disable_button(4);
    });
    $('#btn_del_5').on('click', function(e) {
        $('#form_penalizacion_5').hide();
        disable_button(5);
    });
    $('#btn_del_6').on('click', function(e) {
        $('#form_penalizacion_6').hide();
        disable_button(6);
    });

    function disable_button(id){
        var count = 0;
        for(var i=1; i<=6; i++){
            if($('#form_penalizacion_'+i).css('display') !== 'none'){
                count++;
            }
        }
        if(count == 0){
            $('#btn_generar').attr('disabled', true);
        }
    }

    if( $( "#atm_id" ).val() == 0){
        $('.mensaje_atm').show();
    }

    $('#atm_id').on('change',function(){
        console.log($(this).val());
        if($(this).val() == 0){
            $('.mensaje_atm').show();
        }else{
            $('.mensaje_atm').hide();
        }
    });

    $('#show_form').on('click', function(e) {
            e.preventDefault();

            const select = document.getElementById('penalty_select');
            var tipo_multa = select.value;
            //console.log(tipo_multa);

            if(tipo_multa == 0){
                $('.mensaje').show();
            }else{   
                $('#form_penalizacion_'+tipo_multa).show();
                $('.mensaje').hide();
                $('#btn_generar').attr('disabled', false);
                setTimeout(function() {
                    $('#myModal').modal('hide')
                }, 100);
            }
    });

    $('#btn_generar').on('click', function(e) {
        e.preventDefault();

        $('.factura_titulo').hide();
        $('.saldo_titulo').hide();
        $('#detalles').hide();
        $("#modal_detail").modal();
        $('#name_atm').text('Nombre: ' + $("#atm_id option:selected").text());

        var data = '';
        var sum_pay = 0;
        var saldo_cliente = $('#saldo_cliente').val();
        var atm_id = $("#atm_id").val();
        var group_id = $("#group_id").val();

        var detail_facturas = [];
        for(var i=1; i<=6; i++){
            console.log($('#form_penalizacion_'+i).css('display'));
            if($('#form_penalizacion_'+i).css('display') !== 'none'){
                var producto = $('#idproducto_'+i).val();
                var titulo_multa = $('#titulo_multa_'+i).val();
                //console.log($('#amount_punitorio_'+i).val());
                if( i == 3){
                    var amount_penalty = $('#amount_punitorio_'+i).val();
                }else{
                    var amount_penalty = $('#amount_penalty_'+i).val();
                }

                var signo='';
                if(i==1 || i==2 || i==3){
                    signo='%';
                }

                var debit_number = $('#debit_number_'+i).val()+signo;
                var quita = parseInt($('#quita_'+i).val()).toLocaleString('es-CL');
                var amount_to_pay = $('#amount_total_to_pay_'+i).val();
                var penalty_type_id= i;
                var observacion = $('#observacion'+i).val();
                
                var factura = {
                    atm_id: atm_id,
                    group_id: group_id,
                    penalty_type_id: penalty_type_id,
                    observacion: observacion,
                    saldo_cliente: saldo_cliente,
                    amount_penalty: amount_penalty.replace(/[^0-9|-]/g, ''),
                    descuento: quita.replace(/[^0-9|-]/g, ''),
                    amount_total_to_pay: amount_to_pay.replace(/[^0-9|-]/g, ''),
                    detail_penalty: []
                };

                switch(i){
                    case 1:
                    case 4:
                    case 6:
                            var penalty_detail=
                            {
                            saldo_cliente: saldo_cliente,
                            producto: producto,
                            monto_a_debitar: debit_number,
                            monto_penalty: amount_penalty.replace(/[^0-9|-]/g, ''),
                            quita_descuento: quita.replace(/[^0-9|-]/g, ''),
                            multa_total_a_pagar: amount_to_pay.replace(/[^0-9|-]/g, '')
                            };
                    break;
                    case 2:
                            var penalty_detail=
                            {
                            saldo_cliente: saldo_cliente,
                            producto: producto,
                            monto_a_debitar: debit_number,
                            cantidad_meses: $('#cant_meses_'+i).val(),
                            monto_multa: amount_penalty.replace(/[^0-9|-]/g, ''),
                            quita_descuento: quita.replace(/[^0-9|-]/g, ''),
                            multa_total_a_pagar: amount_to_pay.replace(/[^0-9|-]/g, '')
                            };
                    break;
                    case 3:
                            var penalty_detail=
                            {
                            saldo_cliente: saldo_cliente,
                            producto: producto,
                            monto_a_debitar: debit_number,
                            cantidad_meses: $('#cant_meses_'+i).val(),
                            monto_multa: $('#amount_penalty_'+i).val().replace(/[^0-9|-]/g, ''),
                            monto_interes_punitorio: $('#debit_punitorio_'+i).val() + '%',
                            monto_con_interes_punitorio: $('#amount_punitorio_'+i).val().replace(/[^0-9|-]/g, ''),
                            quita_descuento: quita.replace(/[^0-9|-]/g, ''),
                            multa_total_a_pagar: amount_to_pay.replace(/[^0-9|-]/g, '')
                            };
                    break;
                    case 5:
                            var penalty_detail=
                            {
                            saldo_cliente: saldo_cliente,
                            producto: producto,
                            monto_a_debitar: debit_number,
                            cantidad_dias: $('#cant_dias_'+i).val(),
                            monto_multa: amount_penalty.replace(/[^0-9|-]/g, ''),
                            quita_descuento: quita.replace(/[^0-9|-]/g, ''),
                            multa_total_a_pagar: amount_to_pay.replace(/[^0-9|-]/g, '')
                            };
                    break;
                }

                factura.detail_penalty.push(penalty_detail);
                detail_facturas.push(factura);

                sum_pay += parseInt($('#amount_total_to_pay_'+i).val().replace(/[^0-9|-]/g, ''));
                data +=  `
                    <tr>
                        <td>`+producto+`</td>
                        <td>`+titulo_multa+`</td>
                        <td>`+amount_penalty+`</td>
                        <td>`+quita+`</td>
                        <th>`+amount_to_pay+`</th>
                    </tr>
                `;
            }
        }
        console.log(detail_facturas);
        $("#cadena").val(JSON.stringify(detail_facturas));
        console.log($("#cadena").val());
        data +=  `
            <tr style="border-top: 2px solid rgb(186, 186, 186);">
                <th style="text-align: right; border-top: 2px solid rgb(186, 186, 186);font-size: 25px" colspan="4">MONTO TOTAL A PAGAR:</th>
                <th style="border-top: 2px solid rgb(186, 186, 186);font-size: 25px" colspan="1">`+sum_pay.toLocaleString('es-CL')+` Gs.</th>
            </tr>
        `;

        $("#details_facturas").html(data);
    });    

    $('.pay-info').on('click',function(e){

        e.preventDefault();
        var group_id = $('#group_id').val();
        var grupo = $('#descripcion_grupo').val();
        console.log(group_id);
        day=0;

        $("#modal-contenido").html('');
        $('#cargando').show();
        $("#detalles").hide();
        $('#show_form').hide();
        $('.factura_titulo').hide();
        $('.saldo_titulo').show();
        $('.titulo_tipo_multa').hide();
        $('.cabeceras_atms').show();
    
        $.get('{{ url('reports') }}/info/get_branch_groups/' + group_id + '/' + day, 
        function(data) {

            console.log(data);
                $(".grupo").html(grupo);
                $("#modal-contenido").html(data);
                $("#detalles").show();
                $('#cargando').hide();
                $("#myModal").modal('show');
        });

    });

    $('#quita_1').on('change keydown paste input',function(){
        /*console.log($('#amount_penalty_1').val());
        console.log($('#amount_total_to_pay_1').val());*/
        var amount_penalty=$('#amount_penalty_1').val().replace(/[^0-9|-]/g, '');
        var quita = $(this).val();

        var monto = amount_penalty - quita;
        
        $('input[name=amount_total_to_pay_1]').val(parseInt(monto).toLocaleString('es-CL'));
    });

    $('#quita_2').on('change keydown paste input',function(){
        /*console.log($('#amount_penalty_1').val());
        console.log($('#amount_total_to_pay_1').val());*/
        var amount_penalty=$('#amount_penalty_2').val().replace(/[^0-9|-]/g, '');
        var quita = $(this).val();

        var monto = amount_penalty - quita;
        
        $('input[name=amount_total_to_pay_2]').val(parseInt(monto).toLocaleString('es-CL'));
    });

    $('#quita_3').on('change keydown paste input',function(){
        /*console.log($('#amount_penalty_1').val());
        console.log($('#amount_total_to_pay_1').val());*/
        var amount_penalty=$('#amount_punitorio_3').val().replace(/[^0-9|-]/g, '');
        var quita = $(this).val();

        var monto = amount_penalty - quita;
        
        $('input[name=amount_total_to_pay_3]').val(parseInt(monto).toLocaleString('es-CL'));
    });

    $('#quita_4').on('change keydown paste input',function(){
        /*console.log($('#amount_penalty_1').val());
        console.log($('#amount_total_to_pay_1').val());*/
        var amount_penalty=$('#amount_penalty_4').val().replace(/[^0-9|-]/g, '');
        var quita = $(this).val();

        var monto = amount_penalty - quita;
        
        $('input[name=amount_total_to_pay_4]').val(parseInt(monto).toLocaleString('es-CL'));
    });

    $('#quita_5').on('change keydown paste input',function(){
        /*console.log($('#amount_penalty_1').val());
        console.log($('#amount_total_to_pay_1').val());*/
        var amount_penalty=$('#amount_penalty_5').val().replace(/[^0-9|-]/g, '');
        var quita = $(this).val();

        var monto = amount_penalty - quita;
        
        $('input[name=amount_total_to_pay_5]').val(parseInt(monto).toLocaleString('es-CL'));
    });

    $('#quita_6').on('change keydown paste input',function(){
        /*console.log($('#amount_penalty_1').val());
        console.log($('#amount_total_to_pay_1').val());*/
        var amount_penalty=$('#amount_penalty_6').val().replace(/[^0-9|-]/g, '');
        var quita = $(this).val();

        var monto = amount_penalty - quita;
        
        $('input[name=amount_total_to_pay_6]').val(parseInt(monto).toLocaleString('es-CL'));
    });

    $('#cant_meses_2').on('change keydown paste input',function(){

        var number_original=$('#number_original_2').val().replace(/[^0-9|-]/g, '');
        var cant_meses = $(this).val();

        var new_debit = cant_meses * number_original;
        $('input[name=debit_number_2]').val(new_debit);

        var debit_number = $('#debit_number_2').val().replace(/[^0-9|-]/g, '');
        var saldo_mora=$('#saldo_mora_2').val().replace(/[^0-9|-]/g, '');
        var quita=$('#quita_2').val().replace(/[^0-9|-]/g, '');
        console.log(quita);

        var new_amount_penalty = Math.round((saldo_mora * debit_number)/100);
        var new_amount_total_to_pay = Math.round((saldo_mora * debit_number)/100) - quita;

        $('input[name=amount_penalty_2]').val(parseInt(new_amount_penalty).toLocaleString('es-CL'));
        $('input[name=amount_total_to_pay_2]').val(parseInt(new_amount_total_to_pay).toLocaleString('es-CL'));
    });

    $('#cant_meses_3').on('change keydown paste input',function(){
        
        var number_original=$('#number_original_3').val().replace(/[^0-9|-]/g, '');
        var cant_meses = $(this).val();

        var new_debit = cant_meses * number_original;
        $('input[name=debit_number_3]').val(new_debit);

        var debit_number = $('#debit_number_3').val().replace(/[^0-9|-]/g, '');
        var saldo_mora=$('#saldo_mora_3').val().replace(/[^0-9|-]/g, '');
        var quita=$('#quita_3').val().replace(/[^0-9|-]/g, '');
        var debit_punitorio = $('#debit_punitorio_3').val().replace(/[^0-9|-]/g, '');
        console.log(quita);

        var new_amount_penalty = Math.round((saldo_mora * debit_number)/100);
        var new_amount_punitorio = Math.round((new_amount_penalty * debit_punitorio)/100) + new_amount_penalty;
        var new_amount_total_to_pay = new_amount_punitorio - quita;

        $('input[name=amount_penalty_3]').val(parseInt(new_amount_penalty).toLocaleString('es-CL'));
        $('input[name=amount_punitorio_3]').val(parseInt(new_amount_punitorio).toLocaleString('es-CL'));
        $('input[name=amount_total_to_pay_3]').val(parseInt(new_amount_total_to_pay).toLocaleString('es-CL'));
    });

    $('#cant_dias_5').on('change keydown paste input',function(){

        var number_original=$('#number_original_5').val().replace('.', '');
        var cant_meses = $(this).val();

        var new_debit = cant_meses * number_original;
        $('input[name=debit_number_5]').val(new_debit);

        var quita=$('#quita_5').val().replace(/[^0-9|-]/g, '');
        var new_amount_total_to_pay = new_debit - quita;

        $('input[name=amount_penalty_5]').val(parseInt(new_debit).toLocaleString('es-CL'));
        $('input[name=amount_total_to_pay_5]').val(parseInt(new_amount_total_to_pay).toLocaleString('es-CL'));
    });

    $('#debit_number_4').on('change keydown paste input',function(){
        /*console.log($('#amount_penalty_1').val());
        console.log($('#amount_total_to_pay_1').val());*/
        var amount_penalty=$('#amount_penalty_4').val().replace(/[^0-9|-]/g, '');
        var quita=$('#quita_4').val().replace(/[^0-9|-]/g, '');
        var discount = $(this).val();

        var new_amount_total_to_pay = discount - quita;
        
        $('input[name=amount_penalty_4]').val(parseInt(discount).toLocaleString('es-CL'));
        $('input[name=amount_total_to_pay_4]').val(parseInt(new_amount_total_to_pay).toLocaleString('es-CL'));
    });

   //ocultar-mostrar formulario de multa
    var x = document.getElementById("multa");
    $('#generar').on('change', function() {
             if ($(this).is(':checked') ) {
                x.style.display = "block";
             } else {
                x.style.display = "none";
            }
        });

</script>
@if (session('actualizar') == 'ok')
<script>
    swal({
            type: 'success',
            title: 'El registro ha sido actualizado existosamente.',
            showConfirmButton: false,
            timer: 1500
            });
</script>
@endif
@if (session('guardar') == 'ok')
<script>
    swal({
        type: 'success',
            title: 'Factura de penalización, generada exitosamente.',
            showConfirmButton: false,
            timer: 1500
            });
</script>
@endif
@if (session('error') == 'ok')
<script>
    swal({
            type: "error",
            title: 'Ocurrió un error al intentar generar la multa.',
            showConfirmButton: false,
            timer: 1500
            });
</script>
@endif
@if (session('error_ondanet') == 'ok')
<script>
    swal({
            type: "error",
            title: 'Ocurrió un error al migrar la factura a ondanet.',
            showConfirmButton: false,
            timer: 1500
        });
</script>
@endif
@endsection

@section('aditional_css')
    {{-- <link href="{{"/bower_components/admin-lte/plugins/datepicker/datepicker3.css" }}" rel="stylesheet" type="text/css"/> --}}
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
    <style>
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .switch input { 
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            -webkit-transition: .4s;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            -webkit-transition: .4s;
            transition: .4s;
        }

        input:checked + .slider {
            background-color: #05b923;
        }

        input:focus + .slider {
            box-shadow: 0 0 1px #05b923;
        }

        input:checked + .slider:before {
            -webkit-transform: translateX(26px);
            -ms-transform: translateX(26px);
            transform: translateX(26px);
        }

        /* Rounded sliders */
        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }

        .borderd-campaing {
            border: 1px solid #a1a1ac;
            border-radius: 4px;
            margin-top: 20px;
            position: relative;
        }

        .height-ajust {
            height: 505px;
        }

        .borderd-campaing .title {
            margin: -25px 0 0 50px;
            background: #fff;
            padding: 3px;
            display: inline-block;
            font-weight: bold;
            position: absolute;
        }

        .borderd-campaing .campaing {
            padding: 10px;
        }
        .container-campaing {
            margin-top: 20px;
        }

        .borderd-content {
            border: 1px solid #a1a1ac;
            border-radius: 4px;
            height: 180px;
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
        /*MULTA*/
        
        .borderd-campaing-multa {
            border: 1px solid #a1a1ac;
            border-radius: 4px;
            height: 250px;
            margin-top: 20px;
            position: relative;
        }

        .borderd-campaing-multa .title {
            margin: -25px 0 0 50px;
            background: #fff;
            padding: 3px;
            display: inline-block;
            font-weight: bold;
            position: absolute;
        }

        .borderd-campaing-multa .campaing {
            padding: 10px;
        }
        .container-campaing {
            margin-top: 20px;
        }

        .borderd-content {
            border: 1px solid #a1a1ac;
            border-radius: 4px;
            height: 180px;
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


         /* INFO */
         .borderd-info {
            border: 1px solid #a1a1ac;
            border-radius: 4px;
            margin-top: 20px;
            position: relative;
            height: auto;
        }

        .borderd-info .title {
            margin: -25px 0 0 50px;
            background: #fff;
            padding: 3px;
            display: inline-block;
            font-weight: bold;
            position: absolute;
        }
        .borderd-info .campaing {
            padding: 10px;
        }
        .container-info {
            margin-top: 20px;
        }

        /*MULTA GENERADA*/
        .factura-generada {
            border: 1px solid #a1a1ac;
            border-radius: 4px;
            margin-top: 20px;
            position: relative;
            /* height: auto; */
        }

        .factura-generada .title {
            margin: -25px 0 0 50px;
            background: #fff;
            padding: 3px;
            display: inline-block;
            font-weight: bold;
            position: absolute;
        }
        .factura-generada {
            padding: 5px;
        }
        .container-multa {
            margin-top: 30px;
        }

        .btn-circle.btn-xl {
            border-radius: 35px;
            font-size: 24px;
        }
    </style>
@endsection