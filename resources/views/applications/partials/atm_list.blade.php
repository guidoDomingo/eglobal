<div class="box box-success">
    <div class="box-header with-border">
        <h3 class="box-title">Cajeros asignados</h3>
    </div>
    <div class="box-body">
        <table class="table table-striped">
            <tbody><thead>
            <tr>
                <th>#</th>
                <th>PDV</th>
                <th>Code</th>
                <th>Estado</th>
                <th>Fecha</th>
                <th style="width:150px"></th>
            </tr>
            </thead>
            <tbody>
            @foreach($assigned_atm as $atm)
                <tr data-id="{{$atm->atm_id}}">
                    <td>{{$atm->atm_id}}</td>
                    <td>{{$atm->description}}</td>
                    <td>{{$atm->code}}</td>
                    @if ($atm->active == 1)
                        <td>Activo</td>
                    @else
                        <td>Inactivo</td>
                    @endif
                    <td>{{$atm->created_at}}</td>
                    <td><a class="btn btn-danger btn-flat btn-row" href="#" class="btn-delete-atm"><i class="fa fa-remove" title="Eliminar"></i></a></td>
                </tr>
                @endforeach


                </tbody>
        </table>

        {!! Form::open(['route' => ['applications.delete_assigned_atm',':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-asignatm-delete']) !!}
        {!! Form::close() !!}

    </div>
</div>
@section('page_scripts')
    @include('partials._delete_row_atm_assigned_js')
@endsection