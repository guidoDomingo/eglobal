        <div class="box">
            <div class="box-header">
                <h3 class="box-title">FACTURAS / Conciliaciones por cajero</h3>
                <div class="box-tools">
                    <div class="input-group" style="width:150px;">
                        {!! Form::model(Request::only(['name']),['route' => 'applications.index', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
                        {!! Form::text('name' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'ATM', 'autocomplete' => 'off' ]) !!}
                        {!! Form::close()!!}
                    </div>
                </div>
            </div>

            <div class="box-body  no-padding">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-striped">
                            <tbody><thead>
                            <tr>
                                <th style="width:10px">#</th>
                                <th>Atm</th>
                                <th>ID Transacción</th>
                                <th>Servicio</th>
                                <th>Monto</th>
                                <th>Mensaje</th>
                                <th style="width:150px">Creado</th>
                                <th style="width:150px">Modificado</th>
                                <th style="width:100px">Acciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($invoices as $invoice)
                            <tr data-id="1">
                                <td>{{$invoice->id}}</td>
                                <td>{{$invoice->atm_code}}</td>
                                <td>{{$invoice->transaction_id}}</td>
                                <td>{{$invoice->service_description}}</td>
                                <td>{{number_format($invoice->amount,0)}}</td>
                                <td>{{$invoice->response}}</td>
                                <td>{{$invoice->created_at}}</td>
                                <td>{{$invoice->updated_at}}</td>
                                <td></td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="box-footer clearfix">
                <div class="row">
                    <div class="col-sm-5">
                        <div class="dataTables_info" role="status" aria-live="polite"> {{count($invoices)}} registros en total</div>
                    </div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers">

                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="box">
            <div class="box-header">
                <h3 class="box-title">INGRESOS / Conciliaciones por cajero</h3>
                <div class="box-tools">
                    <div class="input-group" style="width:150px;">
                        {!! Form::model(Request::only(['name']),['route' => 'applications.index', 'method' => 'GET', 'class' => 'form-horizontal', 'role' => 'search']) !!}
                        {!! Form::text('name' ,null , ['class' => 'form-control input-sm pull-right', 'placeholder' => 'ATM', 'autocomplete' => 'off' ]) !!}
                        {!! Form::close()!!}
                    </div>
                </div>
            </div>

            <div class="box-body  no-padding">
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-striped">
                            <tbody><thead>
                            <tr>
                                <th style="width:10px">#</th>
                                <th>Atm</th>
                                <th>ID Transacción</th>
                                <th>Servicio</th>
                                <th>Monto</th>
                                <th>Mensaje</th>
                                <th style="width:150px">Creado</th>
                                <th style="width:150px">Modificado</th>
                                <th style="width:100px">Acciones</th>
                            </tr>
                            </thead>
                            <tbody>
                            @foreach($incomes as $income)
                            <tr data-id="{{$income->id}}">
                                <td>{{$income->id}}</td>
                                <td>{{$income->atm_code}} - {{$income->name}}</td>
                                <td>{{$income->transaction_id}}</td>
                                <td>{{$income->service_description}}</td>
                                <td>{{number_format($income->amount,0)}}</td>
                                <td>{{$income->response}}</td>
                                <td>{{$income->created_at}}</td>
                                <td>{{$income->updated_at}}</td>
                                <td></td>
                            </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="box-footer clearfix">
                <div class="row">
                    <div class="col-sm-5">
                        <div class="dataTables_info" role="status" aria-live="polite"> {{count($incomes)}} registros en total</div>
                    </div>
                    <div class="col-sm-7">
                        <div class="dataTables_paginate paging_simple_numbers">

                        </div>
                    </div>
                </div>
            </div>
        </div>

