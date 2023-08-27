<aside class="main-sidebar">
    <section class="sidebar">
        <div class="user-panel">
            <div class="pull-left image">
                <img src="{{ '/bower_components/admin-lte/dist/img/user7-128x128.jpg' }}" class="img-circle"
                    alt="User Image">
            </div>
            <div class="pull-left info">
                <p>{{ Sentinel::getUser()->description }}</p>
                <a href="#">
                    <i class="fa fa-circle text-success"></i>En linea
                </a>
            </div>
        </div>

        <ul class="sidebar-menu">
            <li class="header">Menú Principal</li>

            @if (\Sentinel::getUser()->hasAccess('terminal_interaction.manage'))
                <li class="treeview {{ Request::is('terminal_interaction/manage*') ? 'active' : '' }}">
                    <a href="#">
                        <i class="fa fa-gears"></i>
                        <span>Administrar</span>
                        <i class="fa fa-angle-left pull-right"></i>
                    </a>
                    <ul class="treeview-menu">
                        <li @if (Request::is('terminal_interaction/manage/users*')) class="active" @endif>
                            <a href="{{ route('terminal_interaction_users') }}">
                                <i class="fa fa-user"></i><span>Usuarios</span>
                            </a>
                        </li>
                    </ul>
                </li>
            @endif

            @if (\Sentinel::getUser()->hasAccess('terminal_interaction.reports'))
                <li class="treeview {{ Request::is('terminal_interaction/reports*') ? 'active' : '' }}">

                    <a href="#">
                        <i class="fa fa-filter"></i>
                        <span>Reportes</span>
                        <i class="fa fa-angle-left pull-right"></i>
                    </a>

                    <ul class="treeview-menu">
                        <li @if (Request::is('terminal_interaction/reports/pos_box_movement*')) class="active" @endif>
                            <a href="{{ route('pos_box_movement_index') }}">
                                <i class="fa fa-filter"></i><span>Movimientos de caja</span>
                            </a>
                        </li>

                        <li @if (Request::is('terminal_interaction/reports/transaction*')) class="active" @endif>
                            <a href="{{ route('transaction_index') }}">
                                <i class="fa fa-filter"></i><span>Transacciones</span>
                            </a>
                        </li>

                        <li @if (Request::is('terminal_interaction/reports/ticket*')) class="active" @endif>
                            <a href="{{ route('ticket_index') }}">
                                <i class="fa fa-filter"></i><span>Tickets de transacciones</span>
                            </a>
                        </li>

                        <li @if (Request::is('terminal_interaction/reports/accounting_statement*')) class="active" @endif>
                            <a href="{{ route('accounting_statement_index') }}">
                                <i class="fa fa-filter"></i><span>Estado contable</span>
                            </a>
                        </li>
                    </ul>
                </li>
            @endif
        </ul>
    </section>
</aside>
