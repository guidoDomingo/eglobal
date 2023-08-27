@extends('layout')

@section('title')
    Nueva Regla de Servicios
@endsection
@section('content')
    <section class="content-header">
        <h1>
            Regla de Servicios
            <small>Creación de Regla de Servicios</small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Regla de Servicios</a></li>
            <li class="active">agregar</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">Nueva Regla</h3>
                    </div>
                    <div class="box-body">
                        @include('partials._flashes')
                        @include('partials._messages')
                        {!! Form::open(['route' => 'services_rules.store' , 'method' => 'POST', 'role' => 'form']) !!}
                        @include('services_rule.partials.fields')
                        <a class="btn btn-default" href="{{ route('services_rules.index') }}" role="button">Cancelar</a>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
@section('js')
<script src="/bower_components/admin-lte/plugins/input-mask/jquery.inputmask.js"></script>
<script src="/bower_components/admin-lte/plugins/input-mask/jquery.inputmask.date.extensions.js"></script>
<script src="/bower_components/admin-lte/plugins/input-mask/jquery.inputmask.extensions.js"></script>
<!-- date-range-picker -->
<link href="/bower_components/admin-lte/plugins/datepicker/datepicker3.css" rel="stylesheet" type="text/css" />
<script src="/bower_components/admin-lte/plugins/daterangepicker/moment.min.js"></script>

<!-- bootstrap datepicker -->
<script src="/bower_components/admin-lte/plugins/datepicker/bootstrap-datepicker.js"></script>
<script src="/bower_components/admin-lte/plugins/datepicker/locales/bootstrap-datepicker.es.js" charset="UTF-8"></script>

<script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>
<script>
    $('.select2').select2();
    $('#owner_id').on('change', function(e){
        var group_id = $( "#group_id" ).val();
        var owner_id = e.target.value;
        $.get('{{ url('reports') }}/ddl/branches/' + group_id + '/' + owner_id, function(branches) {
            $('#branch_id').empty();
            $.each(branches, function(i,item){
                $('#branch_id').append($('<option>', {
                    value: i,
                    text : item
                }));
            });
        });
    });
</script>
@endsection
@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
    <style type="text/css">
        @media print {
            body * {
                visibility:hidden;

            }
            #printSection, #printSection * {
                visibility:visible;
            }
            #printSection {
                font-size: 11px;
                width: 100%;
                height: 100%;
                margin: 0;
                padding: 0;
                left:0;
                top:0;
            }
        }
    </style>
@endsection