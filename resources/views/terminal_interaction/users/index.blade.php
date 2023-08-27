@extends('layout')
@section('title')
    Usuarios
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Usuarios
            <small>Listado</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Usuarios</a></li>
            <li class="active">Listado</li>
        </ol>
    </section>
    <section class="content">

        <div class="delay_slide_up">
            @include('partials._flashes')
        </div>

        <div class="box">
            <div class="box-header">
                <h3 class="box-title">
                </h3>
                <a href="{{ route('terminal_interaction_users_create') }}" class="btn-sm btn-primary active"
                    role="button">
                    <i class="fa fa-plus"></i> &nbsp; Agregar

                </a>
            </div>
            <div class="box-body">
                <div class="row">
                    <div class="col-xs-12">
                        <table class="table table-bordered table-hover dataTable" role="grid" id="datatable_1">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Email</th>
                                    <th>Rol</th>
                                    <th>Creado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr data-id="{{ $user->id }}">
                                        <td>{{ $user->description }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->role }}</td>
                                        <td>{{ date('d/m/y H:i:s', strtotime($user->created_at)) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
    {!! Form::open(['route' => ['users.destroy', ':ROW_ID'], 'method' => 'DELETE', 'id' => 'form-delete']) !!}
@endsection
@section('page_scripts')
    @include('partials._delete_row_js')
    @include('partials._user_baneo_js')
@endsection

@section('js')

    <!-- datatables -->
    <link rel="stylesheet" href="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.css">
    <script src="/bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.min.js"></script>

    <!-- Iniciar objetos -->
    <script type="text/javascript">

        $(".delay_slide_up").delay(5000).slideUp(300);

        //Datatable config
        var data_table_config = {
            //custom
            orderCellsTop: true,
            fixedHeader: true,
            pageLength: 20,
            lengthMenu: [5, 10, 20, 30, 50, 70, 100, 250, 500, 1000],
            dom: '<"pull-left"f><"pull-right"l>tip',
            language: {
                "url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"
            },
            scroller: true
        }

        var table = $('#datatable_1').DataTable(data_table_config);
    </script>
@endsection
