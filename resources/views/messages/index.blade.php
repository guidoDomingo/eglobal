<?php

$colors = [
    'danger' => '#dd4b39',
    'warning' => '#f39c12',
    'success' => '#00a65a',
    'info' => '#00c0ef'
];

$type = [
    'error',
    'warning',
    'success',
    'info'
];

$type = 'info';

if (isset($data['type'])) {
    $type = $data['type'];
}

$title = 'Mensaje';

if (isset($data['title'])) {
    $title = $data['title'];
}

$explanation = 'Para obtener más información contacte a sistemas.';

if (isset($data['explanation'])) {
    $explanation = $data['explanation'];
}

$mode = 'alert';

if (isset($data['mode'])) {
    $mode = $data['mode'];
}

if ($mode != 'alert') {
    if ($type == 'error') {
        $type = 'danger';
    }
}

$error_detail = '';

if (isset($data['error_detail'])) {
    $error_detail = json_encode($data['error_detail']);
}

?>

@extends('layout')

@section('title')
{{ $title }}
@endsection
@section('content')

<section class="content-header">

</section>

<section class="content">
    <style>
        /*.content-wrapper, .right-side {
            background-color: white; pone el fondo de cualquier color
        }*/
    </style>

   

    <div class="box box-{{ $type }} box-solid" id="show_message" style="display: none">
        <div class="box-header with-border">
            <div class="pull-left">
                <a href="javascript:history.back()" class="btn btn-default" style="float: left"><i class="fa fa-arrow-left"></i> &nbsp; Volver</a>
            </div>

            <h3 class="box-title" style="font-size: 25px; margin-top: 4px"> &nbsp; {{ $title }} </h3>
        </div>

        <div class="box-body">
            <div class="row">
                <div class="col-md-12">
                    <h4 style="float: left"> {{ $explanation }} </h4>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('js')
<!-- Iniciar objetos -->
<script type="text/javascript">
    @if($mode == 'alert')
    swal({
            title: "{{ $title }}",
            text: "{{ $explanation }}",
            type: "{{ $type }}",
            showCancelButton: false,
            confirmButtonColor: '#3c8dbc',
            confirmButtonText: 'Volver',
            cancelButtonText: 'No.',
            closeOnClickOutside: false,
            html: true
        },
        function(isConfirm) {
            if (isConfirm) {
                history.back();
            }
        }
    );
    @else
    $('#show_message').css('display', 'block');
    @endif;

    console.log('Detalle del error:', {!! $error_detail !!});
</script>
@endsection