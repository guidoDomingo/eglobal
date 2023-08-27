@extends('app')
@section('title')
    Reportes
@endsection
@section('content')
    <section class="content-header">
            
        <h1>
            Reportes
            <small>{{ $target }}</small>
        </h1>

        <ol class="breadcrumb">
            <li><a href="/"><i class="fa fa-dashboard"></i> Inicio</a></li>
            <li><a href="#">Reportes</a></li>
        </ol>

        <br/>

        <div class="row">
            <div class="col-md-12">
                @include('partials._flashes')
            </div>
        </div>
    </section>

    @if ($target == 'Transacciones')
        @include('reporting.partials.transactions_index')
    @endif
    @if ($target == 'Transacciones del Dia')
        @include('reporting.partials.one_day_transactions_index')
    @endif
    @if ($target == 'Transacciones_batch')
        @include('reporting.partials.batch_transaction_index')
    @endif
    @if ($target == 'Payments')
        @include('reporting.partials.payments_index')
    @endif
    @if ($target == 'Notificaciones')
        @include('reporting.partials.notifications_index')
    @endif

    @if ($target == 'Conciliations Details')
        @include('reporting.partials.conciliations_details_index')
    @endif

    @if ($target == 'Arqueos')
        @include('reporting.partials.arqueos_index')
    @endif
    @if ($target == 'Cargas')
        @include('reporting.partials.cargas_index')
    @endif
    @if ($target == 'Saldos')
        @include('reporting.partials.saldos_index')
    @endif
    @if ($target == 'Resumen')
        @include('reporting.partials.resumen_transacciones')
    @endif
    @if ($target == 'Disponibilidad ATMs')
        @include('reporting.partials.estado_atm')
    @endif
    @if ($target == 'Transacciones por Mes')
        @include('reporting.partials.transactions_amount_index')
    @endif
    @if ($target == 'Dispositivos')
        @include('reporting.partials.dispositivos_index')
    @endif
    @if ($target == 'Tickets de devolucion')
        @include('reporting.partials.transactions_vuelto_index')
    @endif
    @if ($target == 'Transacciones por ATM')
        @include('reporting.partials.transactions_atm_index')
    @endif
    @if ($target == 'Denominaciones Utilizadas')
        @include('reporting.partials.denominaciones_amount_index')
    @endif
    @if ($target == 'Vueltos Entregados')
        @include('reporting.partials.vuelto_entregado')
    @endif
    @if ($target == 'Estado Contable')
        @include('reporting.partials.estado_contable')
    @endif
    @if ($target == 'Estado Contable Old')
        @include('reporting.partials.estado_contable_old')
    @endif
    @if ($target == 'Resumen Mini Terminales')
        @include('reporting.partials.resumen_miniterminales')
    @endif
    @if ($target == 'Resumen Mini Terminales Old')
        @include('reporting.partials.resumen_miniterminales_old')
    @endif
    @if ($target == 'Depositos Miniterminales')
        @include('reporting.partials.boleta_depositos')
    @endif
    @if ($target == 'Comisiones')
        @include('reporting.partials.comisiones_index')
    @endif
    @if ($target == 'Ventas')
        @include('reporting.partials.sales_index')
    @endif
    @if ($target == 'Ventas Old')
        @include('reporting.partials.sales_index_old')
    @endif
    @if ($target == 'Cobranzas')
        @include('reporting.partials.cobranzas_index')
    @endif
    @if ($target == 'Conciliations')
        @include('reporting.partials.conciliations')
    @endif

    @if ($target == 'Saldos Contable')
        @include('reporting.partials.saldos_control_contable')
    @endif

    @if ($target == 'Bloqueados')
        @include('reporting.partials.bloqueados')
    @endif

    @if ($target == 'Instalaciones APP-Billetaje')
        @include('reporting.partials.installations_index')
    @endif
    @if ($target == 'Efectividad')
        @include('reporting.partials.efectividad')
    @endif
    @if ($target == 'Depositos Cuotas Miniterminales')
        @include('reporting.partials.depositos_cuotas')
    @endif
    @if ($target == 'Resumen Detallado Miniterminales')
        @include('reporting.partials.resumen_detallado')
    @endif
    @if ($target == 'Resumen Detallado Miniterminales Old')
        @include('reporting.partials.resumen_detallado_old')
    @endif
    @if ($target == 'Historial Bloqueos')
        @include('reporting.partials.historial_bloqueos')
    @endif
    @if ($target == 'Depositos Alquileres Miniterminales')
        @include('reporting.partials.depositos_alquileres')
    @endif
    @if ($target == 'Cuotas Alquiler')
        @include('reporting.partials.cuotas_alquiler')
    @endif
    @if ($target == 'Contratos miniterminales')
        @include('reporting.partials.contratos_index')
    @endif
    @if ($target == 'Transacciones sin reversa')
    @include('reporting.partials.rollbackNot_index')
    @endif

    @if ($target == 'Ventas pendientes de afectar extractos')
    @include('reporting.partials.movements_affecting_extracts_index')
    @endif

    @if ($target == 'Transacciones exitosas con monto cero')
    @include('reporting.partials.transaction_success_amount_zero_index')
    @endif

    @if ($target == 'Pagos de Clientes Miniterminales')
        @include('reporting.partials.pago_clientes')
    @endif

    @if ($target == 'Historico estados ATM')
        @include('reporting.partials.atm_status_history')
    @endif
    
    @if ($target == 'Dms')
        @include('reporting.partials.dms_index')
    @endif

    @if ($target == 'Transacciones Retiros')
        @include('reporting.partials.miniCashoutDevolucione')
    @endif
    
@endsection
