
<div class="form-group col-md-6 borderd-info">
    <div class="title"><h4>&nbsp;<i class="fa fa-info-circle"></i>&nbsp;INFO &nbsp;</h4></div>
    <div class="container-info">
        <div class="form-row">

         

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
                    {!! Form::text('group', $grupo->description , ['class' => 'form-control', 'disabled' =>'disabled' ]) !!}
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
        
            <div class="form-group col-md-12">
                
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
                                <td>{{ $item->id }}.</td>
                                <td>{{ $item->code }}</td>
                                <td>{{ $item->name }}</td>
                            </td>
                           @endforeach
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- @include('partials._date_picker') --}}






