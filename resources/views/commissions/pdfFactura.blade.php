<!DOCTYPE html>
<html>

<head>
    <title>Comprobante</title>
    <style>
        body {
            font-family: Arial, Helvetica, sans-serif;
        }
        
        .receipt-main {
            display: inline-block;
            width: 100%;
            font-size: 12px;
            /*border: 1px solid #000;*/
        }
        
        .receipt-title {
            text-transform: uppercase;
            font-size: 15px;
            font-weight: 500;
            margin: 0;
        }
        
        .receipt-titulo {
            text-transform: uppercase;
            margin: 0;
        }
        
        .receipt-detalle {
            margin: 5px;
        }
        
        .receipt-label {
            font-weight: 800;
            font-size: 15px;
        }
        
        .text-large {
            font-size: 16px;
        }
        
        .receipt-section {
            margin-top: 10px;
        }
        
        .container {
            border: 1px solid #000;
            border-radius: 5px;
        }
        
        .grid-container {
            border-bottom: 1px solid #000;
        }
        
        .grid-item {
            padding: 10px;
            font-size: 15px;
            text-align: center;
        }
        
        .grid-detalle {
            padding: 10px;
            font-size: 15px;
            text-align: left;
        }
        
        .grid-right {
            border-left: 1px solid #000;
        }
        
        .datos {
            font-weight: lighter;
        }
        
        #orangeBox {
            background: white;
            color: black;
            font-size: 1em;
            text-align: center;
            width: 50px;
            height: 40px;
            padding: 2px;
            padding-left: 5px;
            padding-right: 3px;
            border: 1px solid #000;
            border-left: 1px solid #000;
        }
        
        .table-datos {
            margin-top: 15px;
        }
        
        .border-titulo {
            border-right: 1px solid #000;
        }
        
        table {
            border-collapse: collapse;
        }
        
        .info {
            border-top: 0.5px solid #000;
            padding-top: 20px;
            padding-bottom: 180px;
        }

        .info_fac {
            border-top: 0.5px solid #000;
            padding-top: 5px;
            padding-bottom: 15px;
        }
        
        .subtotal {
            border-top: 0.5px solid #000;
            padding-top: 5px;
            padding-bottom: 5px;
            padding-left: 7px;
            text-align: left;
        }
        
        .informacion {
            border-top: 0.5px solid #000;
            padding-top: 20px;
            padding-bottom: 40px;
            padding-left: 10px;
            padding-right: 10px;
            text-align: left;
        }
        
        .subtotal-cantidad {
            border-top: 0.5px solid #000;
            padding-top: 5px;
            padding-bottom: 5px;
            padding-right: 5px;
            text-align: right;
        }
        
        .grid-firma {
            font-size: 15px;
            text-align: center;
        }
        
        .pull-left {
            text-align: center;
            padding-top: -20px;
            font-size: 10px;
        }
    </style>
</head>

<body>
    <div class="receipt-main">

        <div style="width:100%" class="container">
            <table style="width:100%" class="grid-container">
                <tr>
                    <th>
                        <img src="https://cms.eglobalt.com.py/eglobalt_si.png" width="100">
                        <h6 class="receipt-title">
                            E GLOBAL S.A.
                        </h6>
                    </th>
                    <th class="grid-item">
                        <h5 class="receipt-titulo">
                            Portales Web
                        </h5>
                        <h5 class="receipt-titulo">
                            Comercio al por menor de equipos de telecomunicaciones
                        </h5>
                        <h5 class="receipt-titulo">
                            Alquiler y arrendamiento de otros tipos de maquinaria, equipo y bienes materiales n.c.p. sin operario
                        </h5>
                        <h5 class="receipt-titulo">
                            Actividades de agencias de cobro y oficinas de crédito
                        </h5>
                        <h5 class="receipt-titulo">
                            Telecomunicaciones
                        </h5>
                        <br>
                        <h6 class="receipt-titulo">
                            Prof. Chavez Nº 273 c/ Dr. Bestard
                        </h6>
                        <h6 class="receipt-titulo">
                            Tel.: (021) 2376740
                        </h6>
                        <h6 class="receipt-titulo">
                            Asunción - Paraguay
                        </h6>
                    </th>
                    <th class="grid-item grid-right">
                        <div class="pull-right receipt-section">
                            <span>RUC: 80083484-4</span><br>
                            <span class="text-large"><strong>Timbrado Nº {{$timbrado->stamping}}</strong></span>
                            <h6 class="receipt-titulo">
                                Válido desde: {{date( 'd/m/Y', strtotime($timbrado->valid_from)) }}
                            </h6>
                            <h6 class="receipt-titulo">
                                Válido hasta: {{date( 'd/m/Y', strtotime($timbrado->valid_until)) }}
                            </h6>
                            <span class="text-large"><strong>F A C T U R A</strong></span><br>
                            <span class="text-large">Nº {{str_replace('-','',$voucher_data->comprobante_numero)}}</span>
                        </div>
                    </th>
                </tr>
            </table>
            <table style="width:100%">
                <tr>
                    <th class="grid-detalle">
                        <h5 class="receipt-detalle">
                            Fecha de Emisión &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:
                            <b class="datos">{{ date("d-m-Y", strtotime($fecha)) }}</b>
                        </h5>
                        <h5 class="receipt-detalle">
                            R.U.C. &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:
                            <b class="datos">{{ $voucher_data->client_ruc }}</b>
                        </h5>
                        <h5 class="receipt-detalle">
                            Nombre o Razón Social&nbsp;: <b class="datos">{{ $voucher_data->cliente_nombre }}</b>
                        </h5>
                        <h5 class="receipt-detalle">
                            Credito Bancario &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:
                        </h5>
                        <h5 class="receipt-detalle">
                            Dirección &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;:
                        </h5>
                    </th>
                    <th class="grid-detalle">
                        <h5 class="receipt-detalle">
                            CONDICION DE VENTA: CONTADO &nbsp;&nbsp;
                            <span id="orangeBox">X</span> &nbsp;&nbsp;&nbsp; CREDITO&nbsp;&nbsp;
                            <span id="orangeBox">&nbsp;&nbsp;&nbsp;</span>
                        </h5>
                        <h5 class="receipt-detalle">
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Vendedor&nbsp;:
                            <b class="datos">035 TESORERIA - CTA. C</b>
                        </h5>
                        <h5 class="receipt-detalle">
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Nota de Remisíon Nro.&nbsp;:
                        </h5>
                        <h5 class="receipt-detalle">
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Vencimiento&nbsp;:
                            <b class="datos"></b>
                        </h5>
                        <h5 class="receipt-detalle">
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Teléfono&nbsp;:
                        </h5>
                    </th>
                </tr>
            </table>
        </div>
        <br>
        <div style="width:100%" class="container">
            <table style="width:100%">
                <tr>
                    <th class="border-titulo" width="15%">Código</th>
                    <th class="border-titulo" width="29%">Descripcion Mercaderia</th>
                    <th class="border-titulo" width="6%">Cantidad</th>
                    <th class="border-titulo" width="13%">Precio<br>Unitario</th>
                    <th width="36%" colspan="3">Valor de Ventas</th>
                </tr>
                <tr>
                    <th class="border-titulo" width="15%"></th>
                    <th class="border-titulo" width="29%"></th>
                    <th class="border-titulo" width="6%"></th>
                    <th class="border-titulo" width="13%"></th>
                    <th class="border-titulo" width="11%" style='border-top: 0.5px solid #000;'>Exentas</th>
                    <th class="border-titulo" width="11%" style='border-top: 0.5px solid #000;'>5 %</th>
                    <th width="14%" style='border-top: 0.5px solid #000;'>10 %</th>
                </tr>
                @if (($grupo->total_comision_td + $grupo->total_comision_dc) > 0 and $grupo->total_comision_tc <= 0 )
                    <tr>
                        <th class="border-titulo info_fac  datos" width="15%">INT151</th>
                        <th class="border-titulo info_fac  datos" width="29%">{{$grupo->description}} - TD</th>
                        <th class="border-titulo info_fac  datos" width="6%">{{number_format($grupo->quantity, 2, ',', '.')}}</th>
                        <th class="border-titulo info_fac  datos" width="13%">{{number_format($grupo->total_comision_td + $grupo->total_comision_dc, 2, ',', '.')}}</th>
                        <th class="border-titulo info_fac  datos" width="11%">0,00</th>
                        <th class="border-titulo info_fac  datos" width="11%">0,00</th>
                        <th class="info_fac datos" width="14%">{{number_format($grupo->total_comision_td + $grupo->total_comision_dc, 2, ',', '.')}}</th>
                    </tr>
                @elseif (($grupo->total_comision_td + $grupo->total_comision_dc) <= 0 and $grupo->total_comision_tc > 0 )
                    <tr style="padding-top:15px">
                        <th class="border-titulo info_fac datos" width="15%">INT150</th>
                        <th class="border-titulo info_fac datos" width="29%">{{$grupo->description}} - TC</th>
                        <th class="border-titulo info_fac datos" width="6%">{{number_format($grupo->quantity, 2, ',', '.')}}</th>
                        <th class="border-titulo info_fac datos" width="13%">{{number_format($grupo->total_comision_tc, 2, ',', '.')}}</th>
                        <th class="border-titulo info_fac datos" width="11%">0,00</th>
                        <th class="border-titulo info_fac datos" width="11%">0,00</th>
                        <th class="info_fac datos" width="14%">{{number_format($grupo->total_comision_tc, 2, ',', '.')}}</th>
                    </tr>
                @elseif (($grupo->total_comision_td + $grupo->total_comision_dc) > 0 and $grupo->total_comision_tc > 0 )
                    <tr>
                        <th class="border-titulo info_fac  datos" width="15%">INT151</th>
                        <th class="border-titulo info_fac  datos" width="29%">{{$grupo->description}} - TD</th>
                        <th class="border-titulo info_fac  datos" width="6%">{{number_format($grupo->quantity, 2, ',', '.')}}</th>
                        <th class="border-titulo info_fac  datos" width="13%">{{number_format($grupo->total_comision_td + $grupo->total_comision_dc, 2, ',', '.')}}</th>
                        <th class="border-titulo info_fac  datos" width="11%">0,00</th>
                        <th class="border-titulo info_fac  datos" width="11%">0,00</th>
                        <th class="info_fac datos" width="14%">{{number_format($grupo->total_comision_td + $grupo->total_comision_dc, 2, ',', '.')}}</th>
                    </tr>

                    <tr style="padding-top:15px">
                        <th class="border-titulo info_fac datos" width="15%">INT150</th>
                        <th class="border-titulo info_fac datos" width="29%">{{$grupo->description}} - TC</th>
                        <th class="border-titulo info_fac datos" width="6%">{{number_format($grupo->quantity, 2, ',', '.')}}</th>
                        <th class="border-titulo info_fac datos" width="13%">{{number_format($grupo->total_comision_tc, 2, ',', '.')}}</th>
                        <th class="border-titulo info_fac datos" width="11%">0,00</th>
                        <th class="border-titulo info_fac datos" width="11%">0,00</th>
                        <th class="info_fac datos" width="14%">{{number_format($grupo->total_comision_tc, 2, ',', '.')}}</th>
                    </tr>

                @endif
                

                <tr>
                    <th class="border-titulo subtotal" width="66%" colspan="4">SUBTOTAL</th>
                    <th width="10%" class="border-titulo subtotal-cantidad datos">0,00</th>
                    <th width="10%" class="border-titulo subtotal-cantidad datos">0,00</th>
                    <th width="14%" class="subtotal-cantidad datos">{{number_format($grupo->total_comision, 2, ',', '.')}}</th>
                </tr>
               
                <tr>
                    <th class="border-titulo subtotal" width="86%" colspan="6">TOTAL A PAGAR &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <b class="datos" style="text-transform: uppercase;">GUARANIES {{$text}}</b></th>
                    <th width="14%" class="subtotal-cantidad datos">{{number_format($grupo->total_comision, 2, ',', '.')}}</th>
                </tr>
                <tr>
                    <th class="subtotal" colspan="2">LIQUIDACION DEL IVA 5 %</th>
                    <th class="datos subtotal-cantidad border-titulo">0,00</th>
                    <th colspan="1" class="subtotal">10 %</th>
                    <th colspan="1" class="border-titulo subtotal datos">{{number_format($porcentaje, 0, ',', '.')}},00</th>
                    <th colspan="1" class="subtotal">TOTAL IVA</th>
                    <th colspan="1" class="subtotal datos">{{number_format($porcentaje, 0, ',', '.')}},00</th>
                </tr>
                <tr>
                    {{-- <th colspan="7" class="informacion datos">Recibí conforme según descripción de esta factura y pagare el importe consignado al vencimiento del plazo señalado. El simple vencimiento establecerá la mora y devengará un interés punitorio del ...... mensual, sin necesidad de protesto.
                        Autorizando además la inclusión a la base de datos de IMFORMCONF a lo establecido en la Ley 1682, tambien para que se pueda proveer información a terceros interesados. A efectos de reclamo judicial, autorizo desde ya al acreedor
                        a ejecutar este documento a todos los efectos legales emergentes a esta obligacion quedando sometidos a la jurisdicción de los tribunales ordinarios de Asunción. El unico comprobante de cancelación de la FACTURA DE CREDITO constituye
                        nuestro recibo oficial autorizado para el efecto.
                    </th> --}}
                </tr>
                {{-- <tr>
                    <th colspan="2" class="grid-firma">
                        ................................
                    </th>
                    <th colspan="2" class="grid-firma">
                        ................................
                    </th>
                    <th colspan="3" class="grid-firma">
                        ................................
                    </th>
                </tr> --}}
                {{-- <tr>
                    <th colspan="2" class="grid-firma" style="padding-bottom: 10px;">
                        Firma del Cliente
                    </th>
                    <th colspan="2" class="grid-firma" style="padding-bottom: 10px;">
                        Aclaración
                    </th>
                    <th colspan="3" class="grid-firma" style="padding-bottom: 10px;">
                        C.I. Nro.
                    </th>
                </tr> --}}
            </table>
        </div>

        <div class="clearfix "></div>
        <table style="width:100%">
            <tr>
                <th style="width:25%" class="datos">O R I G I N A L</th>
                <th style="width:50%" class="datos pull-left">Autorizado como Autoimpresor y Timbrado Habilitación Nro: 350050010645</th>
                <th style="width:25%"></th>
            </tr>
        </table>
    </div>

</body>

</html>