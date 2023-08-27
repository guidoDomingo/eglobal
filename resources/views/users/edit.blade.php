@extends('app')
@section('title')
    Usuarios
@endsection

@section('aditional_css')
    <!--  BEGIN CUSTOM STYLE FILE  -->
    <link href="{{ asset('src/assets/css/light/scrollspyNav.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('src/assets/css/light/components/tabs.css') }}" rel="stylesheet" type="text/css" />

    <link href="{{ asset('src/assets/css/dark/scrollspyNav.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('src/assets/css/dark/components/tabs.css') }}" rel="stylesheet" type="text/css" />
    <!--  END CUSTOM STYLE FILE  -->
@endsection

@section('content')
    <section class="content-header">
        <h1>
            Usuarios
            <small>Editar Usuario</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Usuario</a></li>
            <li class="active">Editar</li>
        </ol>
    </section>
    <section class="content">
        @include('partials._messages')
        
        {{-- <div class="box box-primary">
            <div class="box-header with-border">
                <h3 class="box-title">Editar Usuario</h3>
            </div>
            <div class="box-body">
                <div class="panel with-nav-tabs">
                    <div class="panel-heading">
                        <ul class="nav nav-tabs">
                            <li class="active"><a href="#tab_help_0" data-toggle="tab">
                                    Datos de usuario </a>
                            </li>
                            @if (\Sentinel::getUser()->hasAccess('pos_boxes_edit'))
                                <li><a href="#tab_help_2" data-toggle="tab">
                                        Interacciones con terminal</a></li>
                            @endif
                        </ul>
                    </div>
                    <div class="panel-body">
                        <div class="tab-content">
                            <div class="tab-pane fade in active" id="tab_help_0">
                                {!! Form::model($user, ['route' => ['users.update', $user->id], 'method' => 'PATCH', 'role' => 'form']) !!}

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="box box-default">
                                            <div class="box-header with-border">
                                                <h3 class="box-title">Usuario</h3>
                                                <div class="box-tools pull-right">
                                                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                                            class="fa fa-minus"></i></button>
                                                </div>
                                            </div>
                                            <div class="box-body">
                                                @include('users.partials.fields')
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="box box-default">
                                            <div class="box-header with-border">
                                                <h3 class="box-title">Opciones</h3>
                                                <div class="box-tools pull-right">
                                                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                                            class="fa fa-minus"></i></button>
                                                </div>
                                            </div>
                                            <div class="box-body">
                                                <a class="btn btn-default" href="{{ route('users.index') }}" role="button">Cancelar</a>
                                                {!! Form::submit('Enviar', ['class' => 'btn btn-primary']) !!}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <div class="box box-default">
                                            <div class="box-header with-border">
                                                <h3 class="box-title">Permisos</h3>
                                                <div class="box-tools pull-right">
                                                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                                            class="fa fa-minus"></i></button>
                                                </div>
                                            </div>
                                            <div class="box-body">
                                                @include('users.partials.permissions')
                                            </div>
                                        </div>
                                    </div>
                                </div>
                
                                {!! Form::close() !!}
                            </div>

                            @if (\Sentinel::getUser()->hasAccess('pos_boxes_edit'))
                                <div class="tab-pane fade" id="tab_help_2">
                                    @include('terminal_interaction_monitoring.pos_box.pos_boxes')
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div> --}}

        <div id="" class="">
            <div class="container">

                <div id="tabsSimple" class="col-xl-12 col-12 layout-spacing">
                    <div class="statbox widget box box-shadow">
                        <div class="widget-header">
                            <div class="row">
                                <div class="col-xl-12 col-md-12 col-sm-12 col-12">
                                    <h4>With Icons</h4>
                                </div>
                            </div>
                        </div>
                        <div class="widget-content widget-content-area">

                            <div class="simple-tab">

                                <ul class="nav nav-tabs" id="myTab" role="tablist">
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link active" id="home-tab-icon" data-bs-toggle="tab"
                                            data-bs-target="#home-tab-icon-pane" type="button" role="tab"
                                            aria-controls="home-tab-icon-pane" aria-selected="true">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round" class="feather feather-home">
                                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                                <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                            </svg> Datos de usuario
                                        </button>
                                    </li>
                                    <li class="nav-item" role="presentation">
                                        <button class="nav-link " id="profile-tab-icon" data-bs-toggle="tab"
                                            data-bs-target="#profile-tab-icon-panes" type="button" role="tab"
                                            aria-controls="home-tab-icon-pane" aria-selected="true">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                stroke-linecap="round" stroke-linejoin="round" class="feather feather-home">
                                                <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                                                <polyline points="9 22 9 12 15 12 15 22"></polyline>
                                            </svg> Interacciones con terminal
                                        </button>
                                    </li>
                                </ul>

                                <div class="tab-content" id="myTabContent">
                                    <div class="tab-pane fade show active" id="home-tab-icon-pane" role="tabpanel"
                                        aria-labelledby="home-tab-icon" tabindex="0">
                                        <div class="" id="">
                                            {!! Form::model($user, ['route' => ['users.update', $user->id], 'method' => 'PATCH', 'role' => 'form']) !!}

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="">
                                                        <div class="">
                                                            <h3 class="">Usuario</h3>
                                                            
                                                        </div>
                                                        <div class="box-body">
                                                            @include('users.partials.fields')
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="">
                                                        <div class="box-header with-border">
                                                            <h3 class="box-title">Opciones</h3>
                                                            
                                                        </div>
                                                        <div class="box-body">
                                                            <a class="btn btn-default" href="{{ route('users.index') }}" role="button">Cancelar</a>
                                                            {!! Form::submit('Enviar', ['class' => 'btn btn-primary']) !!}
                                                        </div>
                                                    </div>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="box box-default">
                                                        <div class="box-header with-border">
                                                            <h3 class="box-title">Permisos</h3>
                                                            <div class="box-tools pull-right">
                                                                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                                                        class="fa fa-minus"></i></button>
                                                            </div>
                                                        </div>
                                                        <div class="box-body">
                                                            @include('users.partials.permissions')
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                            
                                            {!! Form::close() !!}
                                        </div>
                                    </div>
                                    <div class="tab-pane fade" id="profile-tab-icon-panes" role="tabpanel"
                                        aria-labelledby="profile-tab-icon" tabindex="0">
                                        <p class="mt-3">Aliquam at sem nunc. Maecenas tincidunt lacus justo, non ultrices mauris
                                            egestas eu. Vestibulum ut ipsum ac eros rutrum blandit in eget quam. Nullam et lobortis
                                            nunc. Nam sodales, ante sed sodales rhoncus, diam ipsum faucibus mauris, non interdum
                                            nisl lacus vel justo.</p>
                                        <p>Sed imperdiet mi tincidunt mauris convallis, ut ullamcorper nunc interdum. Praesent
                                            maximus massa eu varius gravida. Nullam in malesuada enim. Morbi commodo pellentesque
                                            velit sodales pretium. Mauris scelerisque augue vel est pulvinar laoreet.</p>
                                    </div>
                                    <div class="tab-pane fade" id="contact-tab-icon-pane" role="tabpanel"
                                        aria-labelledby="contact-tab-icon" tabindex="0">
                                        <p class="mt-3">In diam odio, ullamcorper vitae dolor vel, lobortis rhoncus odio. Nullam
                                            lacinia euismod ex vitae ullamcorper. Integer fringilla arcu nunc, et tempus sapien
                                            ornare sed. Nam fringilla velit nec bibendum consectetur. Etiam pellentesque eu nulla
                                            vel tincidunt. </p>
                                        <p>Ut nec nunc sed risus viverra vehicula non non purus. Nunc semper sem ut ante suscipit
                                            vulputate. Integer tempus ornare ligula, sed lacinia leo posuere eu. </p>
                                    </div>
                                    <div class="tab-pane fade" id="disabled-tab-icon-pane" role="tabpanel"
                                        aria-labelledby="disabled-tab-icon" tabindex="0">
                                        <p class="mt-3">Nullam feugiat, sapien a lacinia condimentum, libero nibh fermentum
                                            lectus, eu dictum justo ex sit amet neque. Sed felis arcu, efficitur eget diam eget,
                                            maximus dapibus enim. Sed vitae varius lorem. Fusce non accumsan diam, quis porttitor
                                            dolor. </p>
                                        <p>Aenean ut aliquet dolor. Integer accumsan odio non dignissim lobortis. Sed rhoncus ante
                                            eros, vel ullamcorper orci molestie congue. Phasellus vel faucibus dolor. Morbi magna
                                            eros, vulputate eu sem nec, venenatis egestas quam. Maecenas hendrerit mollis eros, eget
                                            faucibus quam dignissim vel.</p>
                                    </div>
                                </div>

                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

    </section>
@endsection

@section('js')
    {{-- <script src="{{ asset('src/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ asset('src/plugins/src/perfect-scrollbar/perfect-scrollbar.min.js') }}"></script>
    <script src="{{ asset('src/plugins/src/mousetrap/mousetrap.min.js') }}"></script>
    <script src="{{ asset('src/plugins/src/waves/waves.min.js') }}"></script>
    <script src="{{ asset('layouts/horizontal-dark-menu/app.js') }}"></script>
    
    <script src="{{ asset('src/plugins/src/highlight/highlight.pack.js') }}"></script>
    <!-- END GLOBAL MANDATORY STYLES -->
    --}}
    <script src="{{ asset('src/assets/js/scrollspyNav.js') }}"></script>
@endsection
