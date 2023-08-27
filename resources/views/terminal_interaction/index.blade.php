@extends('terminal_interaction.layout')

@section('title')
    Inicio
@endsection

@section('content')
    <section class="content-header">
        <h1>
            Inicio
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="/"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li>Accesos directos</li>
        </ol>
    </section>
    <section class="content">
        <div class="delay_slide_up">
            @include('partials._flashes')
            @include('partials._messages')
        </div>

        <div class="box box-default">
            <div class="box-body" title='Accesos directos'>
                @if (\Sentinel::getUser()->hasAccess('terminal_interaction.manage'))
                    <a class="btn btn-default" href="{{ route('terminal_interaction_users') }}">
                        <i class="fa fa-user"></i> Usuarios
                    </a>
                @endif

                &nbsp;

                @if (\Sentinel::getUser()->hasAccess('terminal_interaction.reports'))
                    <a class="btn btn-default" href="{{ route('pos_box_movement_index') }}">
                        <i class="fa fa-filter"></i> Movimientos de caja
                    </a>
                @endif

                @if (!\Sentinel::getUser()->hasAccess('terminal_interaction.manage') and !\Sentinel::getUser()->hasAccess('terminal_interaction.reports'))
                    <div class="alert alert-danger" role="alert">
                        El usuario no tiene opciones habilitadas, comunicarse con el administrador de sistemas.
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection

@section('js')
    <script type="text/javascript">
        $(".delay_slide_up").delay(5000).slideUp(300);
    </script>
@endsection
