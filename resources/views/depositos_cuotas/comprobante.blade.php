<!DOCTYPE html>
<html>
<head>
    <title>Comprobante</title>
    <style>
        body {
          padding: 40px;
          }
      
        .receipt-main {
          display: inline-block;
          width: 100%;
          padding: 15px;
          font-size: 12px;
          border: 1px solid #000;
        }
      
        .receipt-title {
          text-transform: uppercase;
          font-size: 20px;
          font-weight: 600;
          margin: 0;
        }
      
        .receipt-titulo {
          text-transform: uppercase;
          margin: 0;
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
        
        .grid-container {
            margin-left: auto;
            margin-right: auto;
        }

        .grid-item {
          padding: 20px;
          font-size: 15px;
          text-align: center;
        }
      </style>
</head>
<body>
    <div class="receipt-main">
            <table style="width:100%" class="grid-container">
                <tr> 
                    <th>
                        <img src="https://eglobal.unnaki.net/eglobalt_si.png" width="100">
                        <h6 class="receipt-title">
                                E GLOBAL S.A.
                        </h6>
                    </th>
                    <th class="grid-item">
                        <h4 class="receipt-titulo">
                            Servicios de transmisión
                        </h4>
                        <h4 class="receipt-titulo">
                            De datos ATRAVEZ DE REDES
                        </h4>
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
                    <th class="grid-item">
                        <div class="pull-right receipt-section">
                                <span class="text-large">{{number_format($recibo->monto, 0)}}</span>
                                <span class="text-large receipt-label">Gs.</span>
                                <h6 class="receipt-titulo">
                                    Recibo de Dinero
                                </h6>
                                <h6 class="receipt-titulo">
                                    RUC: 80083484-4  
                                </h6>
                                <span class="text-large">Nº {{$recibo->recibo_nro}}</span>
                        </div>  
                    </th>
                </tr>
            </table>
            
            <div class="clearfix"></div>
            <br><br><br>
            
            <div>
                <div class="row">
                    <div>
                        <span class="receipt-label">Recibí(mos) de:</span>
                        {{$grupo->description}}
                    </div>
    
                    <div><span class="receipt-label">RUC:</span>
                        {{$grupo->ruc}}
                    </div>
    
                    <br><br>
    
                    <div class="col-sm-12">
                        <span class="receipt-label">La cantidad de guaranies: {{number_format($recibo->monto, 0)}} Gs.</span>
                    </div>
    
                    <br><br>
    
                    <div class="col-sm-12">
                        <span class="receipt-label">En concepto de:</span>
                        cuota {{$cuotas}} factura {{$venta->num_venta}}
                    </div>
    
                    <br><br>
    
                    <div class="col-sm-12">
                        <span class="receipt-label">En efectivo:</span>
                        {{$tipo_pago->descripcion}}
                    </div>
    
                    <br><br>
    
                    <div class="col-sm-6">
                        <span class="receipt-label">Segun boleta Nº:</span>
                        {{$recibo->boleta_numero}}
                    </div>
                    
                    <div class="col-sm-6">
                        <span class="receipt-label">c/Banco:</span>
                        {{$banco->descripcion}}
                    </div>
                </div>
            </div>
    
            <br><br><br>
    
            <div class="receipt-section">
                <p class="pull-left text-large">Asunción, {{ date('d/m/Y', strtotime($recibo->fecha)) }}</p>
            </div>
            
            <div class="clearfix"></div>
        </div>
    
    </body>
</html>


