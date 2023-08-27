@extends('layout')

@section('title')
    BAJA | Cambio de estado 
@endsection
@section('content')
    <section class="content-header">
        <h1>
            {{-- {{ $grupo->name }} --}}
            Actualizar estado
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Gestor de terminales</a></li>
            <li><a href="#">Baja</a></li>
            <li class="active">Cambio de estado</li>
        </ol>
    </section>
    <section class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="box box-primary">
            
                    <div class="box-body">
                        {{-- @include('partials._flashes') --}}
                        @include('partials._messages')

                        {!! Form::open(['route' => ['change.status.group.update',$groupId] , 'method' => 'POST', 'role' => 'form', 'id'=>'updateStatus-form']) !!}
                            <div class="box-body  no-padding">
                                <div class="row">
                                    <div class="col-xs-12">

                                        <div class="form-row">
                                            <div class="form-group col-md-6 borderd-campaing">
                                                <div class="title"><h4>&nbsp;<i class="fa fa-history"></i>&nbsp; CAMBIO DE ESTADO &nbsp;</h4></div>
                                                <div class="container-campaing">
                                        
                                                    <div class="form-row">
                                                        <div class="form-group">
                                                            {!! Form::label('status', 'Estado:') !!}
                                                            {!! Form::select('status', ['0' => 'ACTIVO',
                                                                                        '1' => 'BLOQUEADO', 
                                                                                        '2' => 'EN PROCESO DE INACTIVACION', 
                                                                                        '8' => 'LOGISTICAS', 
                                                                                        '3' => 'GESTIÓN COMERCIAL (GC)', 
                                                                                        '4' => 'GESTIÓN PREJUDICIAL (GPJ)', 
                                                                                        '5' => 'GESTIÓN JUDICIAL (GJ)', 
                                                                                        '6' => 'GESTIÓN ASEGURADORA (GA)', 
                                                                                        '7' => 'INACTIVO'], $grupo->status ,['class' => 'form-control select2', 'id' => 'status_group']) !!}
                                                        </div>
                                                    </div>
                                                    <br>
                                                    <hr>
                                                    <br>
                                                    <div id="seleccionar_atms" style="display: none">
                                                        <div class="form-row">
                                                            <div class="form-group">
                                                                {!! Form::label('atm_id', 'ATM/s disponible/s del cliente:', ['style' => 'font-weight:bold; color: red; ']) !!} 
                                                                {{-- {!! Form::text('atms', !empty($atmsIds) ? $atmsIds : null, ['class' => 'form-control input-lg', 'id' => 'selectListAtms', 'placeholder' => 'Seleccione un ATM']) !!} --}}
                                                                {!! Form::select('atm_id', $atms_v2, null, ['id' => 'atm_id', 'class' => 'form-control','placeholder' => 'Seleccione un ATM' ]) !!}

                                                            </div>


                                                            {!! Form::label('observacion', 'Observacion:', ['style' => 'font-weight:bold; color: red;']) !!} 
                                                            <br>
                                                            {!! Form::label('observacion', '1- Este proceso inactivará el ATM.', ['style' => 'font-weight:bold; color: red;']) !!} 
                                                            <br>
                                                            {!! Form::label('observacion', '2- Se ejecutará la remision del equipo.', ['style' => 'font-weight:bold; color: red;']) !!} 
                                                            <br>
                                                            {!! Form::label('observacion', '3- Se inactivaran los alquileres', ['style' => 'font-weight:bold; color: red;']) !!} 

                                                        </div>
                                                    </div>
                                                   

                                                </div>
                                            </div>
                                            <div class="form-group col-md-6 borderd-info">
                                                <div class="title"><h4>&nbsp;<i class="fa fa-info-circle"></i>&nbsp;INFO &nbsp;</h4></div>
                                                <div class="container-info">
                                                
                                        
                                                    <div class="form-group col-md-6">
                                                        {!! Form::label('ruc', 'RUC/CI:') !!}
                                                        <div class="input-group">
                                                            <div class="input-group-addon">
                                                                <i class="fa fa-pencil-square-o"></i>
                                                            </div>
                                                            {!! Form::text('ruc', $grupo->ruc , ['class' => 'form-control', 'disabled' =>'disabled' ]) !!}
                                                        </div>
                                                    </div>
                                                    <div class="form-group col-md-6">
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
                                            {!! Form::hidden('group_id', $grupo->id) !!}
                                            {{-- {!! Form::hidden('groupId', $groupId) !!} --}}
                                        </div>
                                    </div>  
                                </div>
                            </div>
                            <div class="box-footer">
                                <a class="btn btn-default pull-right" href="{{ url('atm/new/'.$grupo->id.'/groups_atms')}}" role="button">Atrás</a>
                                <button type="submit" class="btn btn-primary pull-right">Actualizar</button>
                            </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('js')

@include('partials._selectize')

<script src="/bower_components/admin-lte/plugins/jquery-validation/dist/jquery.validate.min.js"></script>
<script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>

<link rel="stylesheet" href="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.css">
<script src="/bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js"></script>
<script src="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.min.js"></script>


<script type="text/javascript">
    $('.select2').select2();

    $('#atmSeleccionar').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": false,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "displayLength": 8,
            "language":{"url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"},
            "bInfo" : false
    });

    $('#listadoAtms').DataTable({
            "paging": true,
            "lengthChange": false,
            "searching": false,
            "ordering": true,
            "info": true,
            "autoWidth": false,
            "displayLength": 3,
            "language":{"url": "//cdn.datatables.net/plug-ins/9dcbecd42ad/i18n/Spanish.json"},
            "bInfo" : false
    });

    // $('#selectListAtms').selectize({
    //     delimiter: ',',
    //     persist: false,
    //     openOnFocus: true,
    //     valueField: 'id',
    //     labelField: 'name',
    //     searchField: 'name',
    //     render: {
    //         item: function(item, escape) {
    //             return '<div><span class="label label-primary">' + escape(item.name) + '</span></div>';
    //         }
    //     },
    //     options: {!! $atmsJsonAll !!}
    // });


    
   //ocultar-mostrar formulario de multa
    var x = document.getElementById("seleccionar_atms");
  

    $('#status_group').on('change', function() {
        estado_seleccionado = this.value;

        if (estado_seleccionado == 7 ) {
            x.style.display = "block";
        } else {
            $("#atm_id").selectize()[0].selectize.clear();
            x.style.display = "none";
        }
    });

</script>


@if (session('actualizar') == 'ok')
    <script>
        swal({
                type: 'success',
                title: 'El registro ha sido actualizado existosamente.',
                showConfirmButton: false,
                timer: 1500
            });
    </script>
@endif

@if (session('error') == 'ok')
<script>
    swal({
            type: "error",
            title: 'Ocurrió un error al intentar inactivar el ATM.',
            showConfirmButton: false,
            timer: 1500
            });
</script>
@endif



@endsection

@section('aditional_css')
    {{-- <link href="{{"/bower_components/admin-lte/plugins/datepicker/datepicker3.css" }}" rel="stylesheet" type="text/css"/> --}}
    <link href="/bower_components/admin-lte/plugins/select2/select2.min.css" rel="stylesheet" type="text/css" />
    <style>
        .switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .switch input { 
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            -webkit-transition: .4s;
            transition: .4s;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            -webkit-transition: .4s;
            transition: .4s;
        }

        input:checked + .slider {
            background-color: #05b923;
        }

        input:focus + .slider {
            box-shadow: 0 0 1px #05b923;
        }

        input:checked + .slider:before {
            -webkit-transform: translateX(26px);
            -ms-transform: translateX(26px);
            transform: translateX(26px);
        }

        /* Rounded sliders */
        .slider.round {
            border-radius: 34px;
        }

        .slider.round:before {
            border-radius: 50%;
        }

        
        .borderd-campaing {
            border: 1px solid #a1a1ac;
            border-radius: 4px;
            height: 505px;
            margin-top: 20px;
            position: relative;
        }

        .borderd-campaing .title {
            margin: -25px 0 0 50px;
            background: #fff;
            padding: 3px;
            display: inline-block;
            font-weight: bold;
            position: absolute;
        }

        .borderd-campaing .campaing {
            padding: 10px;
        }
        .container-campaing {
            margin-top: 20px;
        }

        .borderd-content {
            border: 1px solid #a1a1ac;
            border-radius: 4px;
            height: 180px;
            margin-top: 20px;
            position: relative;
        }
        .borderd-content .title {
            margin: -25px 0 0 50px;
            background: #fff;
            padding: 3px;
            display: inline-block;
            font-weight: bold;
            position: absolute;
        }
        /*MULTA*/
        
        .borderd-campaing-multa {
            border: 1px solid #a1a1ac;
            border-radius: 4px;
            height: 250px;
            margin-top: 20px;
            position: relative;
        }

        .borderd-campaing-multa .title {
            margin: -25px 0 0 50px;
            background: #fff;
            padding: 3px;
            display: inline-block;
            font-weight: bold;
            position: absolute;
        }

        .borderd-campaing-multa .campaing {
            padding: 10px;
        }
        .container-campaing {
            margin-top: 20px;
        }

        .borderd-content {
            border: 1px solid #a1a1ac;
            border-radius: 4px;
            height: 180px;
            margin-top: 20px;
            position: relative;
        }
        .borderd-content .title {
            margin: -25px 0 0 50px;
            background: #fff;
            padding: 3px;
            display: inline-block;
            font-weight: bold;
            position: absolute;
        }


         /* INFO */
         .borderd-info {
            border: 1px solid #a1a1ac;
            border-radius: 4px;
            height: 505px;
            margin-top: 20px;
            position: relative;
            /* height: auto; */
        }

        .borderd-info .title {
            margin: -25px 0 0 50px;
            background: #fff;
            padding: 3px;
            display: inline-block;
            font-weight: bold;
            position: absolute;
        }
        .borderd-info .campaing {
            padding: 10px;
        }
        .container-info {
            margin-top: 20px;
        }
    </style>
@endsection