@extends('layout')

@section('title')
    USSD - Menú - Reporte
@endsection
@section('content')
    <section class="content-header">
        <h1>
            USSD - Menú - Reporte
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">USSD - Menú - Reporte</a></li>
        </ol>
    </section>
    <section class="content">

        <?php 
            function get_options_recursively($options) {

                $option_list = array();

                for ($l = 0; $l < count($options); $l++) {

                    $description = $options[$l]['description'];
                    $level = $options[$l]['level'];
                    $command = $options[$l]['command'];
                    $options_amount = count($options[$l]['options']);
                    $message = '';

                    if ($options_amount > 0) {
                        if ($options_amount == 1) {
                            $message = "Tiene $options_amount opción.";
                        } else {
                            $message = "Tiene $options_amount opciones.";
                        }
                    } else if ($options_amount == 0) {
                        $message = "No tiene opciones.";
                    }

                    ?>
                        <div class="callout callout-default"
                            style="border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px">
                            <h4> <?php echo "$description ( $level )"; ?> </h4>
                            <h5> <?php echo $message; ?></h5>
                            <h5> <?php echo $command . '#'; ?></h5>
                            <?php 
                                get_options_recursively($options[$l]['options'])                              
                            ?>
                        </div>
                    <?php 
                }

                return $option_list;
            } 
        ?>


        @if (isset($data['list']))

            <?php $operators = $data['list']; ?>

            @for ($i = 0; $i < count($operators); $i++)

                <?php
                $id = $operators[$i]['id'];
                $description = $operators[$i]['description'];
                $updated_at = $operators[$i]['updated_at'];
                $user = $operators[$i]['user'];
                $status = $operators[$i]['status'];
                $menus = $operators[$i]['menus'];
                $amount_menus = count($menus);

                $checkted = $status == 'Activo' ? 'checked' : '';
                $title = $status == 'Activo' ? "Deshabilitar : $description" : "Habilitar $description";
                $value = $status == 'Activo' ? '1' : '0';
                ?>

                <div class="box box-default">
                    <div class="box-header with-border">
                        <h3 class="box-title">{{ $description }} ( {{ $status }} ). Tiene {{ $amount_menus }}
                            menú en total.</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                    class="fa fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-12">
                                <hr />
                            </div>
                        </div>

                        <div class="row">

                            @for ($j = 0; $j < count($menus); $j++)

                                <?php
                                $id = $menus[$j]['id'];
                                $updated_at = $menus[$j]['updated_at'];
                                $status = $menus[$j]['status'];
                                $options = $menus[$j]['options'];
                                $amount_options = count($menus[$j]['options']);

                                $checkted = $status == 'Activo' ? 'checked' : '';
                                $title = $status == 'Activo' ? "Deshabilitar : $id" : "Habilitar $id";
                                $value = $status == 'Activo' ? '1' : '0';
                                ?>

                                <div class="col-md-12">
                                    <div class="box box-default"
                                        style="border: 1px solid #d2d6de; border-width: 3px 1px 1px 1px">
                                        <div class="box-header with-border">
                                            <h3 class="box-title">Menú N° {{ $id }} ( {{ $status }} ). Tiene
                                                {{ $amount_options }} opciones en total.</h3>
                                            <div class="box-tools pull-right">
                                                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                                        class="fa fa-minus"></i></button>
                                            </div>
                                        </div>
                                        <div class="box-body">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Última actualización:</label>
                                                        <p class="help-block">{{ $updated_at }}</p>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                <!--
                                                    <label>Estado:</label> <br />
                                                    <input type='checkbox' title='{{ $title }}'
                                                        value='{{ $value }}'
                                                        onclick='ussd_service_set_status({{ $id }})'
                                                        style='cursor: pointer' id='checkbox_service_{{ $id }}'
                                                        {{ $checkted }}> &nbsp; Activar /
                                                    Inactivar-->
                                                </div>
                                                
                                            </div>

                                            <div class="row">                                                
                                                @for ($k = 0; $k < count($options); $k++)
                                                    <?php
                                                        $description = $options[$k]['description'];
                                                        $level = $options[$k]['level'];
                                                        $command = $options[$k]['command']; 
                                                        $options_amount = count($options[$k]['options']);

                                                        $message = '';

                                                        if ($options_amount > 0) {
                                                            if ($options_amount == 1) {
                                                                $message = "Tiene $options_amount opción.";
                                                            } else {
                                                                $message = "Tiene $options_amount opciones.";
                                                            }
                                                        } else if ($options_amount == 0) {
                                                            $message = "No tiene opciones.";
                                                        }
                                                    ?>
                                                    <div class="col-md-12">
                                                        <div class="callout callout-default"
                                                            style="border: 1px solid #d2d6de; border-width: 1px 1px 1px 4px">
                                                            <h4> {{ $description }} ( {{ $level }} )</h4>
                                                            <h5> {{ $message }} </h5>
                                                            <h5> {{ $command }} </h5>
                                                            <?php
                                                                get_options_recursively($options[$k]['options'])  
                                                            ?>
                                                        </div>
                                                    </div>
                                                @endfor
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
            @endfor
        @endif
    </section>
@endsection

@section('js')

    <!-- datatables -->
    <link rel="stylesheet" href="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.css">
    <script src="/bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.min.js"></script>

    <!-- date-range-picker -->
    <link href="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker-bs3.css" rel="stylesheet"
        type="text/css" />
    <script src="/bower_components/admin-lte/plugins/daterangepicker/moment.min.js"></script>
    <script src="/bower_components/admin-lte/plugins/daterangepicker/daterangepicker.js"></script>

    <!-- bootstrap datepicker -->
    <script src="/bower_components/admin-lte/plugins/datepicker/bootstrap-datepicker.js"></script>

    <!-- select2 -->
    <script src="/bower_components/admin-lte/plugins/select2/select2.min.js"></script>

    <!-- Iniciar objetos -->
    <script type="text/javascript">

    </script>
@endsection
