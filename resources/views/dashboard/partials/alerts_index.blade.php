<div class="box">
    <div class="box-header">
        <h3 class="box-title">Alertas por cajero</h3>
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
            <div class="col-xs-12">
                <table class="table table-striped">
                    <tbody><thead>
                    <tr>
                        <th style="width:10px">#</th>
                        <th>Atm</th>
                        <th>Servicio</th>
                        <th>Estado</th>
                        <th>Mensaje</th>
                        <th style="width:150px">Creado</th>
                        <th style="width:150px">Modificado</th>
                        <th style="width:100px">Acciones</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($alerts as $alert)
                    <tr data-id="{{$alert->id}}">
                        <td>{{$alert->id}}</td>
                        <td>{{$alert->atm_code}}</td>
                        <td>{{$alert->name}}</td>
                        @if($alert->status == false)
                            <td><span class="label label-danger">Pendiente</span></td>
                        @else
                            <td><span class="label label-success">Cerrado</span></td>
                        @endif
                        <td>{{$alert->message}}</td>
                        <td>{{$alert->created_at}}</td>
                        <td>{{$alert->updated_at}} / {{$alert->username}}</td>
                        <td><a href="#">Activar</a></td>
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
                <div class="dataTables_info" role="status" aria-live="polite"> registros en total</div>
            </div>
            <div class="col-sm-7">
                <div class="dataTables_paginate paging_simple_numbers">

                </div>
            </div>
        </div>
    </div>
</div>