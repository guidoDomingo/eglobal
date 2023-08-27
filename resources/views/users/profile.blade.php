@extends('app')

@section('title')
    Usuarios
@endsection

@section('aditional_css')
    <style>
        body.dark .dl-horizontal {
            color:white;
            font-size: 15px;
        }

    </style>

    <!-- Bootstrap 3.3.4 -->
    <link rel="stylesheet" href="{{ URL::asset('/bower_components/admin-lte/bootstrap/css/bootstrap.min.css') }}">
@endsection


@section('content')

    <section class="content">
        {{-- @include('partials._flashes')
        @include('partials._messages') --}}
        @if(Session::has('success'))
            <div class="alert alert-success alert-dismissable">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h4><i class="icon fa fa-check"></i>Operación Exitosa</h4>
                {{session('success')}}
            </div>
        @endif
        @if(Session::has('error'))
            <div class="alert alert-danger alert-dismissable">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h4><i class="icon fa fa-check"></i>Error!!</h4>
                {{session('error')}}
            </div>
        @endif
        
            <div class="row">
                <div class="col-md-12">
                      <div class="card">
                            <div class="card-body">
                                <div class="col-md-10">
                                    <div class="box-header with-border">
                                        <i class="fa fa-user"></i>
                                        <h3 class="box-title">Datos de la Cuenta</h3>
                                    </div>
                                </div>
                                <div class="col-md-2">

                                    <div class="btn-group mb-2 me-4">
                                        <button type="button" class="btn btn-primary">Opciones</button>
                                        <button type="button" class="btn btn-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-chevron-down"><polyline points="6 9 12 15 18 9"></polyline></svg>
                                            <span class="visually-hidden ">Toggle Dropdown</span>
                                        </button>
                                        <div class="dropdown-menu">
                                            <a class="dropdown-item" href="{{ route('reset.password.request',['id' => $user->id]) }}">Cambiar Contraseña</a>
                                            @if(!$activate) 
                                            <a class="dropdown-item" href="{{ route('resend.activation.request',['id' => $user->id]) }}">Reenviar Activacion</a>
                                            @endif
                                        </div>
                                    </div>

                                </div>
                            
                                <dl class="dl-horizontal">
                                    <dt >Usuario</dt>
                                    <dd>{{ $user->username }}</dd>

                                    <dt>Email</dt>
                                    <dd>{{ $user->email }}</dd>

                                    <dt>Red</dt>
                                    <dd>
                                        @if(!is_null($user->owner))
                                            <a href="{{ route('home', ['id' => $user->owner_id]) }}">{{ $user->owner->name }}</a>
                                        @endif
                                    </dd>

                                    <dt>Sucursal</dt>
                                    <dd>
                                        @if(!is_null($user->branch)) <a href="#">{{ $user->branch->description }}</a> @else
                                            &nbsp; @endif
                                    </dd>

                                    <dt>Roles</dt>
                                    <dd>
                                        @foreach($user->roles as $role)
                                            <span class="label label-primary">{{ $role->name }}</span>
                                        @endforeach
                                    </dd>

                                    <dt>Miembro desde:</dt>
                                    <dd>{{ $user->created_at->diffForHumans() }}</dd>

                                    <dt>Última sesión:</dt>
                                    <dd>

                                        @if(!empty($user->last_login)) {{ $user->last_login->diffForHumans() }} @else
                                            &nbsp; @endif

                                    </dd>

                                    <dt>Estado de la cuenta:</dt>
                                    <dd>

                                        @if(!$activate) 
                                            <span><i class="fa fa-circle text-danger"></i> Inactivo </span>
                                        @else
                                            <span><i class="fa fa-circle text-success"></i> Activo </span>
                                        @endif

                                    </dd>

                                </dl>
                         
                            </div>
                        <!-- /.box-body -->
                      </div>
                </div>
            </div>

@stop