<div>
    <div class="row">
        <div class="col-md-12">
            @if (\Sentinel::getUser()->hasAccess('monitoreo.atms'))
                <div class="box box-default">
                    <div class="box-header with-border">

                        <h3 class="box-title">ATMS</h3>

                        <div class="box-tools pull-right" style="cursor:pointer">
                            <!--<i id="reload_data_pie" style="margin: 10px;" class="fa fa-refresh pull-right" title="Actualizar" data-toggle="tooltip"></i>-->

                            <label class="radio-inline">
                                <input wire:model="redes" wire:click='buscaratms' type="radio" name="redes"
                                    checked="checked" value="todos" selected>Todos
                            </label>

                            <label class="radio-inline">
                                <input wire:model="redes" wire:click='buscaratms' type="radio" name="redes"
                                    value="terminales">Terminales
                            </label>

                            <label class="radio-inline">
                                <input wire:model="redes" wire:click='buscaratms' type="radio" name="redes"
                                    value="miniterminales">Miniterminales
                            </label>

                            <button wire:model="redes" wire:click='buscaratms' value="todos" class="btn btn-default"
                                type="button" title="Actualizar"
                                style="margin-left: 10px; background: transparent; color: #333; border:none; outline: none; border-radius: 25%; padding: 2px;"
                                id="reload_data_pie">
                                <span class="fa fa-refresh"></span>
                            </button>
                        </div>

                    </div>
                    <div class="box-body">
                        <div id="atm_spinn" class="text-center" style="margin: 50px 10px"><i
                                class="fa fa-refresh fa-spin" style="font-size:24px"></i></div>
                        <div class="graficoAtm" id="graficoAtm"></div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    <script>
        let valores;
        let redes = "todos";

        $(document).ready(function() {
            Livewire.emit('actualizaratms', 'todos');
        });

        setInterval(function() {
            Livewire.emit('actualizaratms', redes);
        }, 15000);

        window.addEventListener('cms', event => {
            valores = event.detail.data;
            redes = event.detail.redes;
            actualizar_grafico();
        })

        function actualizar_grafico() {
            $(document).ready(function() {
                var chart = AmCharts.makeChart("graficoAtm", {
                    // "language": "es",
                    "type": "pie",
                    "startDuration": 0,
                    "pullOutDuration": 0,
                    "pullOutRadius": 0,
                    "radius": 80,
                    "theme": "none",
                    "addClassNames": true,
                    "legend": {
                        "position": "bottom",
                        "autoMargins": true
                    },
                    "colorField": "color",
                    "innerRadius": "20%",
                    "fontFamily": "Helvetica",
                    "defs": {
                        "filter": [{
                            "id": "shadow",
                            "width": "200%",
                            "height": "200%",
                            "feOffset": {
                                "result": "offOut",
                                "in": "SourceAlpha",
                                "dx": 0,
                                "dy": 0
                            },
                            "feGaussianBlur": {
                                "result": "blurOut",
                                "in": "offOut",
                                "stdDeviation": 5
                            },
                            "feBlend": {
                                "in": "SourceGraphic",
                                "in2": "blurOut",
                                "mode": "normal"
                            }
                        }]
                    },
                    "dataProvider": [{
                            "estado": "Cap. Máxima",
                            "minutos": valores.capacidad_maxima,
                            "color": "#00008e",
                            "param": "capacidad_maxima"
                        },
                        {
                            "estado": "Cant. Mínima",
                            "minutos": valores.cantidad_minima,
                            "color": "#00b8ef",
                            "param": "cantidad_minima"
                        },
                        {
                            "estado": "Online",
                            "minutos": valores.online,
                            "color": "#0A8B19",
                            "param": "online"
                        },
                        {
                            "estado": "Offline",
                            "minutos": valores.offline,
                            "color": "#FDB504",
                            "param": "offline"
                        },
                        {
                            "estado": "Suspendido",
                            "minutos": valores.suspendido,
                            "color": "#FD0404",
                            "param": "suspendido"
                        },
                        {
                            "estado": "Bloqueados",
                            "minutos": valores.bloqueados,
                            "color": "#770000",
                            "param": "bloqueados"
                        },
                    ],
                    "valueField": "minutos",
                    "titleField": "estado",
                    "export": {
                        "enabled": true,
                        "label": "Exportar",
                    }
                });

                chart.addListener("clickSlice", handleClick);

                function handleClick(e) {
                    if (e.dataItem.dataContext.param == 'capacidad_maxima') {
                        $('.actual').show();
                        $('.maxima').show();
                    } else {
                        $('.maxima').hide();
                        $('.actual').hide();
                    }

                    $("#modal-contenido").html('');
                    $("#modal-footer").html('');
                    console.log(urlGetDetalle + e.dataItem.dataContext.param + '/' + redes);
                    $.get(urlGetDetalle + e.dataItem.dataContext.param + '/' + redes, {
                            status: e.dataItem.dataContext.param,
                            redes: redes
                        },
                        function(data) {
                            $("#modal-contenido").html(data.modal_contenido);
                            $("#modal-footer").html(data.modal_footer);
                            $("#modalDetalleAtms").modal('show');
                        });
                }
                $("#atm_spinn").hide();
                $("#graficoAtm").show();
            });
        }


        $("#graficoAtm").hide();
        $("#atm_spinn").show();

    </script>
</div>
