<div class="box">
    <div class="box-header">
        <h3 class="box-title">Terminales con Saldos al límite</h3>
        <div class="row">
            <div class="col-md-12">
                <a href="#" class="btn-sm btn-primary active" role="button" id="mostrarTodos">Mostrar Todos</a>
                <a href="#" class="btn-sm btn-primary active" role="button" id="ocultarTodos">Ocultar Todos</a>
            </div>
        </div>
    </div>

    <div class="box-body">
        <div class="row">
            <div class="col-xs-12">
                <table class="table table-striped" id="parts">
                    <thead>
                        <tr>
                            <th style="width:10px"></th>
                            <th style="width:10px">#</th>
                            <th>Atm</th>
                            <th>Sucursal</th>
                            <th>Red</th>
                            <th>Ordenar Por Nombre Parte</th>
                            <th>Ordenar Por Diferencia</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="box-footer clearfix">
    </div>
</div>
@section('page_scripts')
    <script src="/bower_components/admin-lte/plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.js"></script>
    <script>
        $(function(){
            var ordenar;
            var ordenAtmName;
            var tabla = $('#parts').DataTable({
                "language": {
                    "url": "/bower_components/admin-lte/plugins/datatables/spanish.json",
                },
                "columns": [
                    {
                        "class": "details-control",
                        "orderable": false,
                        "data":           null,
                        "defaultContent": "<a><span class='fa fa-plus'></span></a>"
                    },
                    {"data": "atm_code", "orderable": false},
                    {"data": "atm_name"},
                    {"data": "pdv", "orderable": false},
                    {"data": "red", "orderable": false},
                    {"data": "nombre_parte", "visible": false},
                    {"data": "diferencia", "visible": false},
                ],
                "order": [[2, 'asc'],[6, 'asc']],
                "processing": true,
                "serverSide": true,
                "ajax": "{{ route('dashboard.balances') }}",
                "columnDef": [
                    {
                        "targets": [0,2,3,5,6],
                        "orderable": false
                    }
                ],
                "drawCallback": function( settings ) {
                    ordenar = tabla.order();
                    if(ordenar[0][0] == 2){
                        ordenAtmName = ordenar[0][1];
                    }
                },
            });

            var detailRows = [];
      
            $('#parts tbody').on( 'click', 'tr td.details-control', function () {        
                var tr = $(this).closest('tr');
                var row = $('#parts').DataTable().row( tr );
                var idx = $.inArray( tr.attr('id'), detailRows );
         
                if ( row.child.isShown() ) {
                    tr.removeClass( 'details' );
                    row.child.hide();
                    $(this).html("<a><span class='fa fa-plus'></span></a>");
                    detailRows.splice( idx, 1 );
                } else {
                    tr.addClass( 'details' );
                    row.child(format(row.data())).show();
                    if ( idx === -1 ) {
                        detailRows.push( tr.attr('id') );
                    }
                    $(this).html("<a><span class='fa fa-minus'></span></a>");
                }
            });
         
            tabla.on( 'draw', function () {
                $.each( detailRows, function ( i, id ) {
                    $('#'+id+' td.details-control').trigger( 'click' );
                });
            });
         
            function format ( d ) {
                var claseOrdenarNombre = 'sorting';
                var claseOrdenarCantidad = 'sorting';

                if(ordenar[0][0] == 5){
                    claseOrdenarNombre += '_'+ordenar[0][1];
                }

                if(ordenar[0][0] == 6){
                    claseOrdenarCantidad += '_'+ordenar[0][1];
                }                

                var detalle ='<table class="table table-bordered"><thead><tr><th class="'+claseOrdenarNombre+' ordenar" tabindex="0" aria-controls="parts" rowspan="1" colspan="1" aria-label="Ordenar Por Nombre Parte: activate to sort column ascending" style="width: 279px;">Ordenar Por Nombre Parte</th><th>Denominación</th><th class="'+claseOrdenarCantidad+' ordenarCantidad" tabindex="0" aria-controls="parts" rowspan="1" colspan="1" aria-label="Ordenar Por Nombre Parte: activate to sort column ascending" style="width: 279px;">Cant. Mínima/Cant. Actual</th><th>Estado</th></tr></thead>';
                for(var x=0;x<d.detalle.length;x++){
                    detalle+='<tr>'+
                        '<td width=350px>'+d.detalle[x].nombre_parte+'</td>'+
                        '<td>'+d.detalle[x].denominacion+'</td>'+
                        '<td width=250px><span><i class="fa fa-circle text-'+d.detalle[x].status+'"></i> '+d.detalle[x].cantidad+'</span></td>'+
                        '<td>'+d.detalle[x].label+'</td>'+
                    '</tr>';
                }
                return detalle;
            }

            $(document).on('click','.ordenar', function(){
                if(ordenar[0][1] == "desc"){
                    tabla.order([[5, 'asc'],[2, ordenAtmName]]).draw();
                }else{
                    tabla.order([[5, 'desc'],[2, ordenAtmName]]).draw();
                }
            });

            $(document).on('click','.ordenarCantidad', function(){
                if(ordenar[0][1] == "desc"){
                    tabla.order([[6, 'asc'],[2, ordenAtmName]]).draw();
                }else{
                    tabla.order([[6, 'desc'],[2, ordenAtmName]]).draw();
                }
            });

            $('#mostrarTodos').on('click', function(){
                tabla.rows(':not(.details)').nodes().to$().find('td:first-child').trigger('click');
            });

            $('#ocultarTodos').on('click', function(){
                tabla.rows('.details').nodes().to$().find('td:first-child').trigger('click');
            });
        });
    </script>
@endsection
@section('aditional_css')
    <link href="/bower_components/admin-lte/plugins/datatables/dataTables.bootstrap.css" rel="stylesheet" type="text/css" />
@endsection