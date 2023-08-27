<aside class="main-sidebar">
    <!-- sidebar: style can be found in sidebar.less     style="width:250px;"-->
    <section class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="{{ '/bower_components/admin-lte/dist/img/user7-128x128.jpg' }}" class="img-circle"
                    alt="User Image">
            </div>
            <div class="pull-left info">
                <p>{{ Sentinel::getUser()->description }}</p>
                <!-- Status -->
                <a href="#"><i class="fa fa-circle text-success"></i>Online</a>
            </div>
        </div>
        <!-- Sidebar Menu -->
        <ul class="sidebar-menu">
            <li class="header">MENU PRINCIPAL</li>

            @if (\Sentinel::getUser()->hasAccess('users') || \Sentinel::getUser()->hasAccess('roles') ||
                \Sentinel::getUser()->hasAccess('permissions') || \Sentinel::getUser()->hasAccess('usuarios_bahia') ||
                \Sentinel::getUser()->hasAccess('departamentos') || \Sentinel::getUser()->hasAccess('ciudades') ||
                \Sentinel::getUser()->hasAccess('barrios') || \Sentinel::getUser()->hasAccess('parametros_comisiones')
                || \Sentinel::getUser()->hasAccess('group') || \Sentinel::getUser()->hasAccess('descuento_comision')
                || \Sentinel::getUser()->hasAccess('compra_tarex'))
                <li
                    class="treeview {{ Request::is('users*', 'roles*', 'permissions*', 'usuarios_bahia', 'departamentos*', 'ciudades*', 'barrios*', 'notifications_params*', 'parametros_comisiones*', 'group*', 'pago_clientes*', 'recibos_comisiones*', 'compra_tarex*') ? 'active' : '' }}">
                    <a href="#">
                        <i class="fa fa-gears"></i><span>Administrar</span><i
                            class="fa fa-angle-left pull-right"></i></a>
                    <ul class="treeview-menu">
                        @if (\Sentinel::getUser()->hasAccess('users'))
                            <li @if (Request::is('users*')) class="active" @endif>
                                <a href="{{ route('users.index') }}"><i
                                        class="fa fa-user"></i><span>Usuarios</span></a>
                            </li>
                        @endif
                        @if (\Sentinel::getUser()->hasAccess('roles'))
                            <li @if (Request::is('roles')) class="active" @endif>
                                <a href="{{ route('roles.index') }}"><i class="fa fa-users"></i><span>Roles</span></a>
                            </li>
                        @endif

                        @if (\Sentinel::getUser()->hasAccess('roles'))
                            <li @if (Request::is('roles_report')) class="active" @endif>
                                <a href="{{ route('roles_report') }}"><i class="fa fa-users"></i><span>Roles - Reporte</span></a>
                            </li>
                        @endif


                        @if (Sentinel::getUser()->hasAccess('permissions'))
                            <li @if (Request::is('permissions*')) class="active" @endif>
                                <a href="{{ route('permissions.index') }}"><i
                                        class="fa fa-key"></i><span>Permisos</span></a>
                            </li>
                        @endif
                        @if (Sentinel::getUser()->hasAccess('usuarios_bahia'))
                            <li @if (Request::is('usuarios_bahia*')) class="active" @endif>
                                <a href="{{ route('usuarios_bahia.index') }}"><i class="fa fa-user"></i><span>Usuarios
                                        Bahia</span></a>
                            </li>
                        @endif
                        @if (\Sentinel::getUser()->hasAnyAccess(['departamentos', 'ciudades', 'barrios']))
                            <li class="treeview @if (Request::is('departamentos*', 'ciudades*'
                                , 'barrios*' )) active @endif">
                                <a href="#"><i class="fa fa-map-o"></i> Zonas Geográficas
                                    <span class="pull-right-container">
                                        <i class="fa fa-angle-left pull-right"></i>
                                    </span>
                                </a>
                                <ul class="treeview-menu">
                                    @if (Sentinel::getUser()->hasAccess('departamentos'))
                                        <li @if (Request::is('departamentos*')) class="active" @endif>
                                            <a href="{{ route('departamentos.index') }}"><i
                                                    class="fa fa-location-arrow"></i> Departamentos</a>
                                        </li>
                                    @endif
                                    @if (Sentinel::getUser()->hasAccess('ciudades'))
                                        <li @if (Request::is('ciudades*')) class="active" @endif>
                                            <a href="{{ route('ciudades.index') }}"><i
                                                    class="fa fa-location-arrow"></i> Ciudades</a>
                                        </li>
                                    @endif
                                    @if (Sentinel::getUser()->hasAccess('barrios'))
                                        <li @if (Request::is('barrios*')) class="active" @endif>
                                            <a href="{{ route('barrios.index') }}"><i
                                                    class="fa fa-location-arrow"></i> Barrios</a>
                                        </li>
                                    @endif
                                </ul>
                            </li>
                        @endif
                        @if (Sentinel::getUser()->hasAccess('notifications_params'))
                            <li @if (Request::is('notifications_params*')) class="active" @endif>
                                <a href="{{ route('notifications_params.index') }}"><i
                                        class="fa fa-key"></i><span>Configurar Alertas</span></a>
                            </li>
                        @endif
                        @if (\Sentinel::getUser()->hasAccess('parametros_comisiones'))
                            <li @if (Request::is('parametros_comisiones*')) class="active" @endif>
                                <a href="{{ route('parametros_comisiones.index') }}">
                                    <i class="fa fa-gears"></i><span>Parametros de Comisiones</span>
                                </a>
                            </li>
                        @endif
                        @if (\Sentinel::getUser()->hasAccess('group'))
                            <li @if (Request::is('group*')) class="active" @endif>
                                <a href="{{ route('groups.index') }}">
                                    <i class="fa fa-object-group"></i></i><span>Grupos</span>
                                </a>
                            </li>
                        @endif
                        <li class="treeview @if (Request::is('ventas*', 'alquiler*', 'pago_clientes*' )) active @endif">
                            <a href="#"><i class="fa fa-briefcase"></i> Gestión de Miniterminal
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                @if (\Sentinel::getUser()->hasAccess('ventas') || \Sentinel::getUser()->hasAccess('pago_clientes') || \Sentinel::getUser()->hasAccess('descuento_comision'))
                                    <li @if (Request::is('ventas*, pago_clientes*, recibos_comisiones*')) class="active" @endif>
                                        @if (\Sentinel::getUser()->hasAccess('ventas'))
                                            <a href="{{ route('venta.index') }}">
                                                <i class="fa fa-briefcase"></i><span>Venta Miniterminal</span>
                                            </a>
                                            <a href="{{ route('alquiler.index') }}">
                                                <i class="fa fa-briefcase"></i><span>Alquiler Miniterminal</span>
                                            </a>
                                            <a href="{{ route('reporting.cuotas_alquiler') }}">
                                                <i class="fa fa-tags"></i><span>Cuotas de Alquiler</span>
                                            </a>
                                        @endif
                                        @if (\Sentinel::getUser()->hasAccess('descuento_comision'))
                                            <a href="{{ route('recibos_comisiones.index') }}">
                                                <i class="fa fa-list-alt"></i><span>Descuento por Comision</span>
                                            </a>
                                        @endif
                                    </li>
                                @endif
                            </ul>
                        </li>
                        @if (\Sentinel::getUser()->hasAccess('pago_clientes'))
                        <li class="treeview @if (Request::is('pago_clientes*' )) active @endif">
                            <a href="#"><i class="fa fa-money"></i> Gestión de Pago de Clientes
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                @if (\Sentinel::getUser()->hasAccess('pago_clientes'))
                                    <li @if (Request::is('pago_clientes')) class="active" @endif>
                                        @if (\Sentinel::getUser()->hasAccess('pago_clientes'))
                                            <a href="{{ route('pago_clientes') }}">
                                                <i class="fa fa-money"></i><span>Generar Pago de clientes</span>
                                            </a>
                                        @endif
                                    </li>
                                @endif
                                @if (\Sentinel::getUser()->hasAccess('pago_clientes.import_pago'))
                                    <li @if (Request::is('pago_clientes/register_pago')) class="active" @endif>
                                        @if (\Sentinel::getUser()->hasAccess('pago_clientes.import_pago'))
                                            <a href="{{ route('pago_clientes.register_pago') }}">
                                                <i class="fa fa-money"></i><span>Confirmar Pagos</span>
                                            </a>
                                        @endif
                                    </li>
                                @endif
                                @if (\Sentinel::getUser()->hasAccess('pago_clientes'))
                                    <li @if (Request::is('pago_clientes/reporte')) class="active" @endif>
                                        <a href="{{ route('reporting.pago_cliente') }}">
                                            <i class="fa fa-money"></i><span>Reporte de Pagos</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                        @endif
                        <li class="treeview @if (Request::is('housing*')) active @endif">
                            <a href="#"><i class="fa fa-archive"></i> Gestión de Dispositivos
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                @if (\Sentinel::getUser()->hasAccess('housing'))
                                    <li @if (Request::is('housing*')) class="active" @endif>
                                        <a href="{{ route('brands.index') }}">
                                            <i class="fa fa-eject" aria-hidden="true"></i><span>Marcas -
                                                Modelos</span>
                                        </a>
                                        <a href="{{ route('miniterminales.index') }}">
                                            <i class="fa fa-building"></i><span>Housing</span>
                                        </a>
                                        <a href="{{ route('devices.showGet') }}">
                                            <i class="fa fa-server"></i><span>Listado de dispositivos</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>

                        <!--@if (\Sentinel::getUser()->hasAccess('service_rule_params'))
                            <li @if (Request::is('service_rule_params*')) class="active" @endif>
                                <a href="{{ route('service_rule_params.index') }}">
                                    <i class="fa fa-th-list"></i> <span>Reglas de Cashout</span>
                                </a>
                            </li>
                        @endif-->

                        @if (\Sentinel::getUser()->hasAccess('compra_tarex'))
                            <li @if (Request::is('compra_tarex*')) class="active" @endif>
                                <a href="{{ route('compra_tarex.index') }}"><i class="fa fa-credit-card"></i><span>Compra de Saldo</span></a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif


            @if (\Sentinel::getUser()->hasAccess('supervisor_admin'))
                <li  class="treeview {{ Request::is('atms_per_*') ? 'active' : '' }}" title="Gestión de Usuarios - Usuarios por terminal">
                    <a href="#">
                        <i class="fa fa-user"></i>Usuarios &nbsp; <i class="fa fa-cubes"></i>Terminales
                        <i class="fa fa-angle-left pull-right"></i>
                    </a>

                    <ul class="treeview-menu">
                        @if (\Sentinel::getUser()->hasAccess('atms_per_users_management'))
                            <li class="{{ Request::is('atms_per_users_management*') ? 'active' : '' }}">
                                <a href="{{ route('atms_per_users_management') }}"><i
                                    class="fa fa-user"></i><span>Gestión de Usuarios</span></a>
                            </li>
                        @endif
                    </ul>

                    <ul class="treeview-menu">
                        @if (\Sentinel::getUser()->hasAccess('atms_per_users'))
                            <li class="{{ Request::is('atms_per_users') ? 'active' : '' }}">
                                <a href="{{ route('atms_per_users') }}"><i
                                class="fa fa-cubes"></i><span>Terminales por Usuario</span></a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif



            <li class="treeview @if (Request::is('atms*', 'atms_parts*' )) active @endif">
                <a href="#"><i class="fa fa-server"></i> ATM
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    @if (\Sentinel::getUser()->hasAccess('atms'))
                        <li @if (Request::is('atm', 'atm/*') && !Request::is('atm/gooddeals')) class="active" @endif>
                            <a href="{{ route('atm_index') }}"><i class="fa fa-server"></i> <span>ATMs</span></a>
                        </li>
                    @endif

                    @if (\Sentinel::getUser()->hasAccess('atms_parts'))
                        <li @if (Request::is('atms_parts', 'atms_parts/*')) class="active" @endif>
                            <a href="{{ route('atms_parts') }}"><i class="fa fa-server"></i> <span>Partes de ATMs</span></a>
                        </li>
                    @endif
                </ul>
            </li>


            <li class="treeview @if (Request::is('info*')) active @endif">
                @if (\Sentinel::getUser()->hasAccess('info'))

                    <a href="#"><i class="fa fa-info"></i> Info - Tools
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>

                    <ul class="treeview-menu">
                        <li @if(Request::is('info_table*')) class="active" @endif>
                            <a href="{{ route('info_table') }}">
                                <i class="fa fa-table"></i> 
                                <span>Tablas</span>
                            </a>
                        </li>

                        <li @if(Request::is('info_query_to_export*')) class="active" @endif>
                            <a href="{{ route('info_query_to_export') }}">
                                <i class="fa fa-table"></i> 
                                <span>Exportar consulta</span>
                            </a>
                        </li>

                        <li @if(Request::is('info_file_to_table*')) class="active" @endif>
                            <a href="{{ route('info_file_to_table') }}">
                                <i class="fa fa-table"></i> 
                                <span>Convertir archivo a tabla</span>
                            </a>
                        </li>

                        <li @if(Request::is('info_stat_activity*')) class="active" @endif>
                            <a href="{{ route('info_stat_activity') }}">
                                <i class="fa fa-table"></i> 
                                <span>Consultas Activas</span>
                            </a>
                        </li>

                        <li @if(Request::is('info_chat*')) class="active" @endif>
                            <a href="{{ route('info_chat') }}">
                                <i class="fa fa-comments"></i> 
                                <span>Chat</span>
                            </a>
                        </li>

                        <li @if(Request::is('info_plant_uml*')) class="active" @endif>
                            <a href="{{ route('info_plant_uml') }}">
                                <i class="fa fa-columns"></i> 
                                <span>Generar UML</span>
                            </a>
                        </li>
                    </ul>
                @endif
            </li>

            @if (\Sentinel::getUser()->hasAccess('atms_v2'))
            <li class="treeview {{ (Request::is('atmnew','atmnew/*','insurances','insurances/*','contracts','contracts/*','reporte/contrato*','reports/dms','reports/dms/*','caracteristicas','caracteristicas/*','caracteristicas_clientes.index','canales*','categorias*','atm/new/baja','atm/new/*','retiro_dispositivos*','notarescision*','pagares*','notaretiro*','cobranzas','cobranzas*','bancos*') ? 'active' : '') }}">
                <a href="#"><i class="fa fa-building"></i> <span>Gestor de terminales</span><i class="fa fa-angle-left pull-right"></i></a>

                <ul class="treeview-menu">
                    <li @if(Request::is('atmnew','atmnew/*','atm/new/*')) class="active" @endif>
                        <a href="{{ route('atmnew.index') }}">
                            <i class="fa fa-server"></i> 
                            <span>ABM miniterminales</span>
                        </a>
                    </li>
                    @if (\Sentinel::getUser()->hasAccess('insurances_form'))
                        <li @if(Request::is('insurances*')) class="active" @endif>
                            <a href="{{ route('insurances.index') }}">
                                <i class="fa fa-file-powerpoint-o"></i> 
                                <span>Gestor de Pólizas</span>
                            </a>
                        </li>
                    @endif

                    @if ( \Sentinel::getUser()->hasAnyAccess(['gestor_contratos','gestor_contratos.reports','reporting.contracts_atms']))
                        <li class="treeview @if (Request::is('contracts*','reporte/contrato*','reports/contracts/*')) active @endif">
                            <a href="#"><i class="fa fa-balance-scale"></i> Gestión | Legales
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">

                                @if (\Sentinel::getUser()->hasAccess('gestor_contratos'))
                                <li @if(Request::is('contracts*')) class="active" @endif>
                                    <a href="{{ route('contracts.index') }}">
                                        <i class="fa fa-file-text-o"></i> 
                                        <span>Contratos</span>
                                    </a>
                                </li>
                                @endif

                                @if (\Sentinel::getUser()->hasAccess('gestor_contratos.reports'))
                                <li @if(Request::is('reporte/contrato*')) class="active" @endif>
                                    <a href="{{ route('reports.contratos') }}">
                                        <i class="fa fa-file-text-o"></i> 
                                        <span>Reporte | Contratos</span>
                                    </a>
                                </li>
                                @endif

                                {{-- @if (\Sentinel::getUser()->hasAccess('reporting.contracts_atms'))
                                    <li @if (Request::is('reports/contracts/*')) class="active" @endif>
                                        <a href="{{ route('reports.contracts') }}">
                                            <i class="fa fa-file-text"></i><span>Contratos Miniterminales</span>
                                        </a>
                                    </li>
                                @endif --}}

                            </ul>
                        </li>
                    @endif


                    @if ( \Sentinel::getUser()->hasAnyAccess(['reports_dms','caracteristicas_clientes','categorias*','canales*','altas.bancos']))
                    <li class="treeview @if (Request::is('reports/dms','reports/dms/*','caracteristicas*','caracteristicas/clientes','caracteristicas/clientes/*','canales*','categorias*','bancos*')) active @endif">
                        <a href="#"><i class="fa fa-users"></i> Clientes
                            <span class="pull-right-container">
                                <i class="fa fa-angle-left pull-right"></i>
                            </span>
                        </a>
                        <ul class="treeview-menu">

                            @if (\Sentinel::getUser()->hasAccess('reports_dms'))
                                <li @if(Request::is('reports/dms','reports/dms/*')) class="active" @endif>
                                    <a href="{{ route('reports.dms') }}">
                                        <i class="fa fa-file-excel-o" aria-hidden="true"></i> 
                                        <span>Reporte de Clientes</span>
                                    </a>
                                </li>
                            @endif
                            @if (\Sentinel::getUser()->hasAccess('caracteristicas_clientes'))
                                <li @if(Request::is('caracteristicas*','caracteristicas/clientes','caracteristicas/clientes/*')) class="active" @endif>
                                    <a href="{{ route('caracteristicas.clientes') }}">
                                        <i class="fa fa-cog"></i> 
                                        <span>Caracteristicas Clientes</span>
                                    </a>
                                </li>
                            @endif
                            @if (\Sentinel::getUser()->hasAccess('canales'))
                                <li @if(Request::is('canales*')) class="active" @endif>
                                    <a href="{{ route('canales.index') }}">
                                        <i class="fa fa-bullhorn"></i> 
                                        <span>Canales de venta</span>
                                    </a>
                                </li>
                            @endif

                            @if (\Sentinel::getUser()->hasAccess('categorias'))
                                <li @if(Request::is('categorias*','categorias/*')) class="active" @endif>
                                    <a href="{{ route('categorias.index') }}">
                                        <i class="fa fa-bars"></i> 
                                        <span>Categorias</span>
                                    </a>
                                </li>
                            @endif

                            @if (\Sentinel::getUser()->hasAccess('bancos'))
                                <li @if(Request::is('bancos*','bancos/*')) class="active" @endif>
                                    <a href="{{ route('bancos.index') }}">
                                        <i class="fa fa-university"></i> 
                                        <span>Bancos</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                    @endif


                    @if (\Sentinel::getUser()->hasAccess('bajas'))
                        <li @if(Request::is('atm/new/baja','retiro_dispositivos*','notarescision*','pagares*','notaretiro*')) class="active" @endif>
                            <a href="{{ route('atms.baja') }}">
                                <i class="fa fa-power-off"></i> 
                                <span>Baja de miniterminales</span>
                            </a>
                        </li>
                    @endif

                    {{-- @if (\Sentinel::getUser()->hasAccess('cobranzas'))
                        <li @if(Request::is('cobranzas*')) class="active" @endif>
                            <a href="{{ route('cobranzas.index') }}">
                                <i class="fa fa-money"></i> 
                                <span>Cobranzas</span>
                            </a>
                        </li>
                     @endif --}}

                </ul>
            </li>
            @endif

            @if (\Sentinel::getUser()->hasAccess('owner'))
                <li @if (Request::is('owner', 'owner/*')) class="active" @endif>
                    <a href="{{ route('owner.index') }}"><i class="fa fa-sitemap"></i> <span>Redes /
                            Sucursales</span></a>
                </li>
            @endif
            @if (\Sentinel::getUser()->hasAccess('minis_cashout_devolucion_vuelto'))
            <li @if (Request::is('minis.cashout.devolucion.vuelto/*')) class="active" @endif>
                <a href="{{ route('reports.mini_retiro') }}">
                    <i class="fa fa-money"></i><span>Retiro de Dinero</span>
                </a>
            </li>
            @endif
            @if (\Sentinel::getUser()->hasAccess('pos') || \Sentinel::getUser()->hasAccess('vouchers') || \Sentinel::getUser()->hasAccess('providers') || \Sentinel::getUser()->hasAccess('products') || \Sentinel::getUser()->hasAccess('outcomes') || \Sentinel::getUser()->hasAccess('reversiones_bancard'))
                <li class="treeview
            @if (Request::is('pos*', 'vouchers*' , 'providers*' , 'products*' , 'outcome*'
                    , 'pointsofsale*', 'reversiones*' )) active @endif">
                    <a href="#"><i class="fa fa-money"></i><span>Contabilidad</span><i
                            class="fa fa-angle-left pull-right"></i></a>
                    <ul class="treeview-menu">

                        @if (\Sentinel::getUser()->hasAccess('pos'))
                            <li @if (Request::is('pos*', 'pointsofsale*')) class="active" @endif>
                                <a href="{{ route('pos.index') }}">
                                    <i class="fa fa-desktop"></i><span>Puntos de Venta</span>
                                </a>
                            </li>
                        @endif
                        @if (\Sentinel::getUser()->hasAccess('vouchers'))
                            <li @if (Request::is('vouchers*')) class="active" @endif>
                                <a href="{{ route('vouchers.index') }}">
                                    <i class="fa fa-list-alt"></i><span>Tipos de Comprobante</span>
                                </a>
                            </li>
                        @endif
                        @if (\Sentinel::getUser()->hasAccess('providers'))
                            <li @if (Request::is('providers*')) class="active" @endif>
                                <a href="{{ route('providers.index') }}">
                                    <i class="fa fa-truck"></i><span>Proveedores</span>
                                </a>
                            </li>
                        @endif
                        @if (\Sentinel::getUser()->hasAccess('products'))
                            <li @if (Request::is('products*')) class="active" @endif>
                                <a href="{{ route('products.index') }}">
                                    <i class="fa fa-shopping-cart"></i><span>Productos</span>
                                </a>
                            </li>
                        @endif
                        @if (\Sentinel::getUser()->hasAccess('outcomes'))
                            <li @if (Request::is('outcome*')) class="active" @endif>
                                <a href="{{ route('outcome.index') }}">
                                    <i class="fa fa-shopping-cart"></i><span>Entidades Externas</span>
                                </a>
                            </li>
                        @endif
                        @if(\Sentinel::getUser()->hasAccess('reversiones_bancard'))
                            <li @if(Request::is('reversiones*')) class="active" @endif>
                                <a href="{{ route('reversiones.index') }}">
                                    <i class="fa fa-undo"></i><span>Reversiones Bancard</span>
                                </a>
                            </li>
                        @endif
                       
                        
                    </ul>
                </li>
            @endif
            @if (\Sentinel::getUser()->hasAccess('applications'))
                <li class="treeview {{ Request::is('applications*', 'app_updates*','token_dropbox*') ? 'active' : '' }}">
                    <a href="#"><i class="fa fa-cube"></i><span>Aplicaciones</span><i
                            class="fa fa-angle-left pull-right"></i></a>
                    <ul class="treeview-menu">
                        <li @if (Request::is('applications', 'applications/*', 'screens', 'screens/*')) class="active" @endif><a
                                href="{{ route('applications.index') }}"><i class="fa fa-cube"></i>
                                <span>Gestor de Aplicaciones</span></a>
                        </li>
                        <li @if (Request::is('app_updates', 'app_updates/*')) class="active" @endif><a
                                href="{{ route('app_updates.index') }}"><i class="fa fa-cube"></i>
                                <span>Gestor de actualizaciones</span></a>
                        </li>
                        <li @if (Request::is('token_dropbox', 'token_dropbox/*')) class="active" @endif><a
                            href="{{url('token_dropbox/-1/edit')}}"><i class="fa fa-qrcode"></i>
                            <span>Gestor de Token/Dropbox</span></a>
                    </li>
                    </ul>
                </li>
            @endif
            @if (\Sentinel::getUser()->hasAnyAccess(['webservices', 'webservices.providers', 'webservices.providers.add|edit', 'webservices.providers.delete', 'atms.update_gooddeal', 'marcas', 'servicio_marca', 'marca.grilla', 'marca.consolidar', 'marca.order']))
                <li
                    class="treeview {{ Request::is('wsproviders*', 'webservices*', 'wsproducts*', 'atm/gooddeals', 'marca*', 'servicios_marca*') ? 'active' : '' }}">
                    <a href="#"><i class="fa fa-cubes"></i><span>Servicios Web</span><i
                            class="fa fa-angle-left pull-right"></i></a>
                    <ul class="treeview-menu">
                        @if (\Sentinel::getUser()->hasAccess('webservices.providers'))
                            <li @if (Request::is('wsproviders*')) class="active" @endif>
                                <a href="{{ route('wsproviders.index') }}"><i class="fa fa-cube"></i>
                                    <span>Proveedores</span></a>
                            </li>
                        @endif
                        @if (\Sentinel::getUser()->hasAccess('webservices.products'))
                            <li @if (Request::is('wsproducts*')) class="active" @endif>
                                <a href="{{ route('wsproducts.index') }}"><i class="fa fa-cube"></i>
                                    <span>Productos/Operaciones</span></a>
                            </li>
                        @endif
                        @if (\Sentinel::getUser()->hasAccess('webservices'))
                            <li @if (Request::is('webservices*')) class="active" @endif>
                                <a href="{{ route('webservices.index') }}"><i class="fa fa-cube"></i> <span>Web
                                        Services</span></a>
                            </li>
                        @endif
                        @if (\Sentinel::getUser()->hasAccess('atms.update_gooddeal'))
                            <li @if (Request::is('atm/gooddeals')) class="active" @endif>
                                <a href="{{ route('gooddeals.update') }}"><i class="fa fa-cube"></i> <span>Act.
                                        Promociones</span></a>
                            </li>
                        @endif
                        @if (\Sentinel::getUser()->hasAccess('marca'))
                            <li @if (Request::is('marca*') && !Request::is('marca/consolidar', 'marca/order', 'marca/grilla_servicios')) class="active" @endif>
                                <a href="{{ route('marca.index') }}"><i class="fa fa-cube"></i>
                                    <span>Marcas</span></a>
                            </li>
                        @endif
                        @if (\Sentinel::getUser()->hasAccess('servicio_marca'))
                            <li @if (Request::is('servicios_marca*')) class="active" @endif>
                                <a href="{{ route('servicios_marca.index') }}"><i class="fa fa-cube"></i>
                                    <span>Servicios Por Marca</span></a>
                            </li>
                        @endif
                        @if (\Sentinel::getUser()->hasAccess('marca.grilla'))
                            <li @if (Request::is('marca/grilla_servicios')) class="active" @endif>
                                <a href="{{ route('marca.grilla_servicios') }}"><i class="fa fa-cube"></i>
                                    <span>Grilla de Servicios</span></a>
                            </li>
                        @endif
                        {{--@if (\Sentinel::getUser()->hasAccess('marca.consolidar'))
                            <li @if (Request::is('marca/consolidar')) class="active" @endif>
                                <a href="{{ route('marca.consolidar') }}"><i class="fa fa-cube"></i> <span>Consolidar
                                        Marcas</span></a>
                            </li>
                        @endif--}}
                        @if (\Sentinel::getUser()->hasAccess('marca.order'))
                            <li @if (Request::is('marca/order')) class="active" @endif>
                                <a href="{{ route('marca.order') }}"><i class="fa fa-cube"></i> <span>Ordenar
                                        Marcas</span></a>
                            </li>
                        @endif

                        <li
                            class="treeview {{ Request::is('params_rules*', 'services_rules*', 'references_rules*') ? 'active' : '' }}">
                            <a href="#"><i class="fa fa-cube"></i> Reglas<span class="pull-right-container"><i
                                        class="fa fa-angle-left pull-right"></i></span></a>
                            <ul class="treeview-menu">
                                @if (\Sentinel::getUser()->hasAccess('params_rules'))
                                    <li @if (Request::is('params_rules*')) class="active" @endif">
                                        <a href="{{ route('params_rules.index') }}"><i
                                                class="fa fa-edit"></i></i><span>Parámetros</span></a>
                                    </li>
                                @endif
                                @if (\Sentinel::getUser()->hasAccess('services_rules'))
                                    <li @if (Request::is('services_rules*')) class="active" @endif>
                                        <a href="{{ route('services_rules.index') }}"><i
                                                class="fa fa-object-group"></i></i><span>Reglas de Servicios</span></a>
                                    </li>
                                @endif
                                @if (\Sentinel::getUser()->hasAccess('references_rules'))
                                    <li @if (Request::is('references*')) class="active" @endif>
                                        <a href="{{ route('references.index') }}"><i
                                                class="fa fa-phone"></i></i><span>Referencias</span></a>
                                    </li>
                                @endif
                            </ul>
                        </li>

                    </ul>
                </li>
            @endif
            @if (\Sentinel::getUser()->hasAccess('monitoreo.graylog'))
                <li @if (Request::is('monitoreo')) class="active" @endif>
                    <a href={{ route('monitoreo.index') }}><i class="fa fa-dashboard"></i><span>Monitoreo</span></a>
                </li>
            @endif

            @if (\Sentinel::getUser()->hasAccess('depositos_boletas', 'depositos_boletas.conciliations', 'depositos_cuotas'))
                <li
                    class="treeview {{ Request::is('depositos_boletas*', 'depositos_cuotas*') || Request::is('reporting/boletas_depositos*') ? 'active' : '' }}">
                    <a href="#"><i class="fa fa-ticket"></i><span>Gestor de Boletas</span><i
                            class="fa fa-angle-left pull-right"></i></a>
                    <ul class="treeview-menu">
                        <li @if (Request::is('depositos_boletas', 'depositos_boletas/*', 'screens', 'screens/*')) class="active" @endif><a
                                href="{{ route('depositos_boletas.index') }}"><i class="fa fa-ticket"></i>
                                <span>Deposito de Boletas</span></a>
                        </li>
                        <?php
                        $housing = \DB::table('atms')
                            ->select('atms.housing_id')
                            ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                            ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                            ->join('venta_housing', 'atms.housing_id', '=', 'venta_housing.housing_id')
                            ->join('venta', 'venta.id', '=', 'venta_housing.venta_id')
                            ->where('branches.user_id', \Sentinel::getUser()->id)
                            ->where('venta.tipo_venta', 'cr')
                            ->first();
                        ?>
                        @if (!empty($housing) || (\Sentinel::getUser()->inRole('superuser') || \Sentinel::getUser()->inRole('accounting.admin') || \Sentinel::getUser()->inRole('mantenimiento.operativo')))
                            <li @if (Request::is('depositos_cuotas', 'depositos_cuotas/*', 'screens', 'screens/*')) class="active" @endif><a
                                    href="{{ route('depositos_cuotas.index') }}"><i class="fa fa-tasks"></i>
                                    <span>Pago de Cuotas</span></a>
                            </li>

                            @if (\Sentinel::getUser()->inRole('mini_terminal'))
                                <li @if (Request::is('reporting.depositos_cuotas', 'reporting.depositos_cuotas/*', 'screens', 'screens/*')) class="active" @endif>
                                    <a href="{{ route('reporting.depositos_cuotas') }}">
                                        <i class="fa fa-tags"></i><span>Reporte de Cuotas</span>
                                    </a>
                                </li>
                            @endif
                        @endif
                        <?php
                        $housing_alquiler = \DB::table('atms')
                            ->select('atms.housing_id')
                            ->join('points_of_sale', 'atms.id', '=', 'points_of_sale.atm_id')
                            ->join('branches', 'branches.id', '=', 'points_of_sale.branch_id')
                            ->join('alquiler_housing', 'atms.housing_id', '=', 'alquiler_housing.housing_id')
                            ->join('alquiler', 'alquiler.id', '=', 'alquiler_housing.alquiler_id')
                            ->where('branches.user_id', \Sentinel::getUser()->id)
                            ->first();
                        ?>
                        @if (!empty($housing_alquiler) || (\Sentinel::getUser()->inRole('superuser') || \Sentinel::getUser()->inRole('accounting.admin') || \Sentinel::getUser()->inRole('mantenimiento.operativo')))
                            <li @if (Request::is('depositos_alquileres', 'depositos_alquileres/*', 'screens', 'screens/*')) class="active" @endif><a
                                    href="{{ route('depositos_alquileres.index') }}"><i class="fa fa-tasks"></i>
                                    <span>Pago de Alquiler</span></a>
                            </li>
                        @endif
                        @if (!\Sentinel::getUser()->inRole('mini_terminal') && !\Sentinel::getUser()->inRole('supervisor_miniterminal'))
                            <li @if (Request::is('boletas_conciliations*')) class="active" @endif><a
                                    href="{{ route('boletas.conciliations') }}"><i class="fa fa-ticket"></i>
                                    <span>Conciliaciones de Boletas</span></a>
                            </li>
                        @endif

                        <li @if (Request::is('reporting/boletas_depositos*')) class="active" @endif>
                            <a href="{{ route('reporting.boletas_depositos') }}">
                                <i class="fa fa-tags"></i><span>Reporte de Depositos</span>
                            </a>
                        </li>

                        @if (empty($housing) || (\Sentinel::getUser()->inRole('superuser') || \Sentinel::getUser()->inRole('accounting.admin') || \Sentinel::getUser()->inRole('mantenimiento.operativo')))
                            <li @if (Request::is('depositos_cuotas', 'depositos_cuotas/*', 'screens', 'screens/*')) class="active" @endif>
                                <a href="{{ route('reporting.depositos_cuotas') }}">
                                    <i class="fa fa-tags"></i><span>Reporte de Cuotas</span>
                                </a>
                            </li>
                        @endif

                        @if (!empty($housing_alquiler) || (\Sentinel::getUser()->inRole('superuser') || \Sentinel::getUser()->inRole('accounting.admin') || \Sentinel::getUser()->inRole('mantenimiento.operativo')))
                            <li @if (Request::is('depositos_cuotas', 'depositos_cuotas/*', 'screens', 'screens/*')) class="active" @endif>
                                <a href="{{ route('reporting.depositos_alquileres') }}">
                                    <i class="fa fa-tags"></i><span>Reporte de Alquiler</span>
                                </a>
                            </li>
                        @endif

                    </ul>
                </li>
            @endif

            @if (\Sentinel::getUser()->hasAccess('ticketea') && !\Sentinel::getUser()->hasAccess('superuser'))
                <li class="treeview @if (Request::is('reports*')) active @endif">
                    <a href="#"><i class="fa fa-filter"></i><span>Reportes</span><i
                            class="fa fa-angle-left pull-right"></i></a>
                    <ul class="treeview-menu">
                        <li @if (Request::is('reports/transactions*')) class="active" @endif>
                            <a href="{{ route('reports.transactions') }}">
                                <i class="fa fa-tags"></i><span>Transacciones</span>
                            </a>
                        </li>
                    </ul>
                </li>
            @endif
            @if (\Sentinel::getUser()->hasAccess('reporting_mini_terminal') && !\Sentinel::getUser()->hasAccess('superuser'))
                <li class="treeview
                @if (Request::is('reporting*')) active @endif">
                    <a href="#"><i class="fa fa-filter"></i><span>Reportes</span><i
                            class="fa fa-angle-left pull-right"></i></a>
                    <ul class="treeview-menu">
                        <li class="treeview @if (Request::is('reporting/transactions/*', 'reporting/resumen_miniterminales*'
                            , 'reporting/estado_contable*' , 'reporting/comisiones*' , 'reports/transactions/*' )) active @endif">
                            <a href="#"><i class="fa fa-tags"></i> Transacciones
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                <li @if (Request::is('reports/transactions/*')) class="active" @endif>
                                    <a href="{{ route('reports.transactions') }}">
                                        <i class="fa fa-tags"></i><span>Historico Transacciones</span>
                                    </a>
                                </li>
                                <li @if (Request::is('reports/transactions_vuelto/*')) class="active" @endif>
                                    <a href="{{ route('reports.transactions_vuelto') }}">
                                        <i class="fa fa-tags"></i><span>Tickets de Devolucion</span>
                                    </a>
                                </li>
                                <li @if (Request::is('reporting/estado_contable*')) class="active" @endif>
                                    <a href="{{ route('reporting.estado_contable') }}">
                                        <i class="fa fa-tags"></i><span>Estado Contable</span>
                                    </a>
                                </li>

                                @if (!\Sentinel::getUser()->inRole('mini_terminal') || \Sentinel::getUser()->hasAccess('reporting_resumen_mini_terminal'))
                                    <li @if (Request::is('reporting/resumen_miniterminales*')) class="active" @endif>
                                        <a href="{{ route('reporting.resumen_miniterminales') }}">
                                            <i class="fa fa-tags"></i><span>Estado Contable por cliente</span>
                                        </a>
                                    </li>
                                @endif
                                @if (!\Sentinel::getUser()->inRole('mini_terminal') && !\Sentinel::getUser()->inRole('supervisor_miniterminal'))
                                    <li @if (Request::is('reporting/resumen_detallado_miniterminal*')) class="active" @endif>
                                        <a href="{{ route('reporting.resumen_detallado_miniterminal') }}">
                                            <i class="fa fa-tags"></i><span style="font-size:11.5px;">Estado Contable
                                                Detallado</span>
                                        </a>
                                    </li>
                                @endif
                                <li @if (Request::is('reporting/comisiones*')) class="active" @endif>
                                    <a href="{{ route('reporting.comisiones') }}">
                                        <i class="fa fa-tags"></i><span>Comisiones Miniterminales</span>
                                    </a>
                                </li>
                         
                                @if ( \Sentinel::getUser()->inRole('mini_terminal') || \Sentinel::getUser()->inRole('supervisor_miniterminal') )
                                <li @if (Request::is('comisionFacturaCliente*')) class="active" @endif>
                                    <a href="{{ route('comisionFacturaCliente') }}">
                                        <i class="fa fa-money"></i><span>Comisiones Ventas Qr</span>
                                    </a>
                                </li>
                                @endif

                                @if (\Sentinel::getUser()->inRole('supervisor_miniterminal'))
                                    <li @if (Request::is('reporting/sales*')) class="active" @endif>
                                        <a href="{{ route('reporting.sales') }}">
                                            <i class="fa fa-tags"></i><span>Ventas Miniterminales</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                        <li class="treeview @if (Request::is('reports/arqueos*')) active @endif">
                            <a href="#"><i class="fa fa-gears"></i> Operaciones Técnicas
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                <li @if (Request::is('reports/arqueos*')) class="active" @endif>
                                    <a href="{{ route('reports.arqueos') }}">
                                        <i class="fa fa-suitcase"></i><span>Arqueos</span>
                                    </a>

                                </li>
                                {{-- @if (\Sentinel::getUser()->hasAccess('reporting.cargas'))
                                <li @if (Request::is('reports/cargas*')) class="active" @endif >
                                    <a href="{{ route('reports.cargas') }}">
                                        <i class="fa fa-money"></i><span>Cargas</span>
                                    </a>
                                </li>
                                @endif
                                @if (\Sentinel::getUser()->hasAccess('reporting.dispositivos'))
                                <li @if (Request::is('reports/dispositivos*')) class="active" @endif >
                                    <a href="{{ route('reports.dispositivos') }}">
                                        <i class="fa fa-gears"></i><span>Dispositivos</span>
                                    </a>
                                </li>
                                @endif --}}

                                @if (\Sentinel::getUser()->hasAccess('service_rule_params'))
                                    <li @if (Request::is('service_rule_params*')) class="active" @endif>
                                        <a href="{{ route('service_rule_params.index') }}">
                                            <i class="fa fa-th-list"></i> <span>Reglas de Cashout</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        </li>
                    </ul>
                </li>
                @if( (!empty($housing_alquiler)) )
                    <li class="treeview">
                        <a href="#"><i class="fa fa-briefcase"></i> <span>Gestión de Miniterminal</span>
                                <i class="fa fa-angle-left pull-right"></i>
                        </a>
                        <ul class="treeview-menu">
                            <li @if(Request::is('ventas*')) class="active" @endif >
                                <a href="{{ route('reporting.cuotas_alquiler') }}">
                                    <i class="fa fa-tags"></i><span>Cuotas de Alquiler</span>
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif
            @endif

            <!-- Devolución de transacciones -->
            @if (\Sentinel::getUser()->hasAccess('cms_transactions_devolutions'))

                <li class="treeview @if (Request::is('cms_transactions*')) active @endif">

                    <a href="#"><i class="fa fa-exchange"></i>
                        <span>Devoluciones</span>
                        <i class="fa fa-angle-left pull-right"></i>
                    </a>

                    <ul class="treeview-menu">
                        @if (!\Sentinel::getUser()->inRole('cms_transactions_report'))

                        <li @if (Request::is('cms_transactions_index*')) class="active" @endif>
                            <a href="{{ route('cms_transactions_index') }}">
                                <i class="fa fa-clone"></i><span>Realizar devolución</span>
                            </a>
                        </li>

                        @endif
                    </ul>

                    <ul class="treeview-menu">
                        @if (!\Sentinel::getUser()->inRole('cms_transactions_report_devolution'))

                        <li @if (Request::is('cms_transactions_index_devolutions*')) class="active" @endif>
                            <a href="{{ route('cms_transactions_index_devolutions') }}">
                                <i class="fa fa-list"></i><span>Devoluciones realizadas</span>
                            </a>
                        </li>

                        @endif
                    </ul>

                    <ul class="treeview-menu">
                        @if (!\Sentinel::getUser()->inRole('cms_services_with_more_returns'))

                        <li @if (Request::is('cms_services_with_more_returns_index*')) class="active" @endif>
                            <a href="{{ route('cms_services_with_more_returns_index') }}">
                                <i class="fa fa-list"></i><span>Servicios con más demanda</span>
                            </a>
                        </li>

                        @endif
                    </ul>
                </li>
            @endif


            @if (\Sentinel::getUser()->hasAccess('reporting'))
                <li class="treeview
                @if (Request::is('reports*', 'reporting*' ) && !Request::is('reports/saldos') &&
                    !Request::is('reporting/boletas_depositos*')) active @endif">
                    <a href="#"><i class="fa fa-filter"></i><span>Reportes</span><i
                            class="fa fa-angle-left pull-right"></i></a>
                    <ul class="treeview-menu">
                        <li class="treeview @if (Request::is('reports/transactions/*', 'reports/one_day_transactions*'
                            , 'reports/batch_transactions*' , 'reports/payments' , 'reports/transactions_vuelto*'
                            , 'reports/vuelto_entregado*' , 'reporting*' ) &&
                            !Request::is('reporting/boletas_depositos*')) active @endif">
                            <a href="#"><i class="fa fa-tags"></i> Transacciones
                                <span class="pull-right-container">
                                    <i class="fa fa-angle-left pull-right"></i>
                                </span>
                            </a>
                            <ul class="treeview-menu">
                                @if (\Sentinel::getUser()->hasAccess('reporting.transacciones'))
                                    <li @if (Request::is('reports/transactions/*')) class="active" @endif>
                                        <a href="{{ route('reports.transactions') }}">
                                            <i class="fa fa-tags"></i><span>Historico Transacciones</span>
                                        </a>
                                    </li>
                                    <li @if (Request::is('reports/one_day_transactions*')) class="active" @endif>
                                        <a href="{{ route('reports.one_day_transactions') }}">
                                            <i class="fa fa-tags"></i><span>Transacciones del Día</span>
                                        </a>
                                    </li>
                                @endif

                                @if (\Sentinel::getUser()->hasAccess('reporting.transacciones_batch'))
                                    <li @if (Request::is('reports/batch_transactions*')) class="active" @endif>
                                        <a href="{{ route('reports.batch_transactions') }}">
                                            <i class="fa fa-tags"></i><span>Transacciones Batch</span>
                                        </a>
                                    </li>
                                @endif
                                @if (\Sentinel::getUser()->hasAccess('reporting.payments'))
                                    <li @if (Request::is('reports/payments*')) class="active" @endif>
                                        <a href="{{ route('reports.payments') }}">
                                            <i class="fa fa-tags"></i><span>Pagos</span>
                                        </a>
                                    </li>
                                @endif

                                @if (\Sentinel::getUser()->inRole('superuser'))
                                    <li @if (Request::is('terminals_payments')) class="active" @endif>
                                        <a href="{{ route('terminals_payments') }}">
                                            <i class="fa fa-tags"></i><span>Pagos por terminal</span>
                                        </a>
                                    </li>
                                @endif

                                @if (\Sentinel::getUser()->hasAccess('reporting.vueltos'))
                                    <li @if (Request::is('reports/transactions_vuelto*')) class="active" @endif>
                                        <a href="{{ route('reports.transactions_vuelto') }}">
                                            <i class="fa fa-tags"></i><span>Tickets de devolucion</span>
                                        </a>
                                    </li>
                                @endif
                                @if (\Sentinel::getUser()->hasAccess('reporting.vueltos'))
                                    <li @if (Request::is('reports/vuelto_entregado*')) class="active" @endif>
                                        <a href="{{ route('reports.vuelto_entregado') }}">
                                            <i class="fa fa-tags"></i><span>Vueltos entregados</span>
                                        </a>
                                    <li class="treeview @if (Request::is('reporting/resumen_miniterminales*', 'reporting/comisiones*'
                                        , 'reporting/estado_contable*' , 'reporting/sales*' , 'reporting/cobranzas*'
                                        , 'reporting/conciliations' )) active @endif">
                                        <a href="#"><i class="fa fa-tags"></i> Miniterminales
                                            <span class="pull-right-container">
                                                <i class="fa fa-angle-left pull-right"></i>
                                            </span>
                                        </a>
                                        <ul class="treeview-menu">
                                    </li>
                                    @if (\Sentinel::getUser()->hasAccess('reporting.estado_contable'))
                                        <li @if (Request::is('reporting/estado_contable*')) class="active" @endif>
                                            <a href="{{ route('reporting.estado_contable') }}">
                                                <i class="fa fa-tags"></i><span style="font-size:11.5px;">Estado
                                                    Contable</span>
                                            </a>
                                        </li>
                                    @endif

                                    @if (\Sentinel::getUser()->hasAccess('accounting_statement_report'))

                                        <li @if (Request::is('accounting_statement*')) class="active" @endif>
                                            <a href="{{ route('accounting_statement') }}">
                                                <i class="fa fa-tags"></i><span style="font-size:11.5px;">Estado Contable Unificado</span>
                                            </a>
                                        </li>
                                        
                                    @endif


                                    @if (\Sentinel::getUser()->hasAccess('reporting.miniterminales'))
                                        <li @if (Request::is('reporting/resumen_miniterminales*')) class="active" @endif>
                                            <a href="{{ route('reporting.resumen_miniterminales') }}">
                                                <i class="fa fa-tags"></i><span style="font-size:11.5px;">Estado
                                                    Contable por Clientes</span>
                                            </a>
                                        </li>
                                    @endif
                                    @if (\Sentinel::getUser()->hasAccess('reporting.miniterminales'))
                                        <li @if (Request::is('reporting/resumen_detallado_miniterminal*')) class="active" @endif>
                                            <a href="{{ route('reporting.resumen_detallado_miniterminal') }}">
                                                <i class="fa fa-tags"></i><span style="font-size:11.5px;">Estado
                                                    Contable Detallado</span>
                                            </a>
                                        </li>
                                    @endif
                                    @if (\Sentinel::getUser()->hasAccess('reporting.comisiones'))
                                        <li @if (Request::is('reporting/comisiones*')) class="active" @endif>
                                            <a href="{{ route('reporting.comisiones') }}">
                                                <i class="fa fa-tags"></i><span style="font-size:11.5px;">Comisiones
                                                    Miniterminales</span>
                                            </a>
                                        </li>
                                    @endif
                                    @if (\Sentinel::getUser()->hasAccess('reporting.sales'))
                                        <li @if (Request::is('reporting/sales*')) class="active" @endif>
                                            <a href="{{ route('reporting.sales') }}">
                                                <i class="fa fa-tags"></i><span style="font-size:11.5px;">Ventas
                                                    Miniterminales</span>
                                            </a>
                                        </li>
                                    @endif
                                    @if (\Sentinel::getUser()->hasAccess('reporting.cobranzas'))
                                        <li @if (Request::is('reporting/cobranzas*')) class="active" @endif>
                                            <a href="{{ route('reporting.cobranzas') }}">
                                                <i class="fa fa-tags"></i><span style="font-size:11.5px;">Cobranzas
                                                    Miniterminales</span>
                                            </a>
                                        </li>
                                    @endif
                                    @if (\Sentinel::getUser()->hasAccess('reporting.conciliaciones'))
                                        <li @if (Request::is('reporting/conciliations')) class="active" @endif>
                                            <a href="{{ route('reporting.conciliaciones') }}">
                                                <i class="fa fa-tags"></i><span style="font-size:11.5px;">Conciliaciones
                                                    Miniterminales</span>
                                            </a>
                                        </li>
                                    @endif
                                    @if (\Sentinel::getUser()->hasAccess('reporting.conciliaciones'))
                                        <li @if (Request::is('reporting/estados_miniterminales')) class="active" @endif>
                                            <a href="{{ route('reporting.bloqueados') }}">
                                                <i class="fa fa-tags"></i><span style="font-size:11.5px;">Estados
                                                    Miniterminales</span>
                                            </a>
                                        </li>
                                    @endif
                                    @if (\Sentinel::getUser()->hasAccess('reporting.conciliaciones'))
                                        <li @if (Request::is('reporting/historial_bloqueos')) class="active" @endif>
                                            <a href="{{ route('reporting.historial_bloqueos') }}">
                                                <i class="fa fa-tags"></i><span style="font-size:11.5px;">Historial de
                                                    Bloqueos</span>
                                            </a>
                                        </li>
                                    @endif
                            </ul>
            @endif
        </ul>
        </li>
        @if (\Sentinel::getUser()->hasAccess('reporting.negocios'))
            <li class="treeview @if (Request::is('reports/resumen_transacciones*', 'reports/estado_atm*' , 'reports/transactions_amount*'
                , 'reports/transactions_atm*' , 'reports/denominaciones_amount*','reports/atm_status_history*' )) active @endif">
                <a href="#"><i class="fa fa-server"></i> Análisis 
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">
                    <li @if (Request::is('reports/resumen_transacciones*')) class="active" @endif>
                        <a href="{{ route('reports.resumen_transacciones') }}">
                            <i class="fa fa-server"></i><span>Resumen Por ATM</span>
                        </a>
                    </li>
                    <li @if (Request::is('reports/estado_atm*')) class="active" @endif>
                        <a href="{{ route('reports.estado_atm') }}">
                            <i class="fa fa-server"></i><span>Disponibilidad Por ATM</span>
                        </a>
                    </li>
                    <li @if (Request::is('reports/atm_status_history*')) class="active" @endif>
                        <a href="{{ route('reports.atm_status_history') }}"><i class="fa fa-server"></i> <span>Historial de Estados ATM</span></a>
                    </li>
                    <li @if (Request::is('reports/transactions_amount*')) class="active" @endif>
                        <a href="{{ route('reports.transactions_amount') }}">
                            <i class="fa fa-server"></i><span>Transacciones por Mes</span>
                        </a>
                    </li>
                    <li @if (Request::is('reports/transactions_atm*')) class="active" @endif>
                        <a href="{{ route('reports.transactions_atm') }}">
                            <i class="fa fa-server"></i><span>Transacciones por ATM</span>
                        </a>
                    </li>
                    <li @if (Request::is('reports/denominaciones_amount*')) class="active" @endif>
                        <a href="{{ route('reports.denominaciones_amount') }}">
                            <i class="fa fa-server"></i><span>Denominaciones Utilizadas</span>
                        </a>
                    </li>
                    <li @if (Request::is('reports/efectividad*')) class="active" @endif>
                        <a href="{{ route('reports.efectividad') }}">
                            <i class="fa fa-server"></i><span>Efectividad</span>
                        </a>
                    </li>
                </ul>
            </li>
        @endif
        @if (\Sentinel::getUser()->inRole('superuser') || \Sentinel::getUser()->inRole('accounting.admin'))
            @if (\Sentinel::getUser()->hasAccess('commissions_qr_invoices'))
                <li class="treeview @if (Request::is('comisionFactura*')) active @endif">
                    <a href="#"><i class="fa fa-server"></i> Reportes Qr
                        <span class="pull-right-container">
                            <i class="fa fa-angle-left pull-right"></i>
                        </span>
                    </a>
                    <ul class="treeview-menu">
                        <li @if (Request::is('comisionFactura')) class="active" @endif>
                            <a href="{{ route('comisionFactura') }}">
                                <i class="fa fa-money"></i><span>Comisiones Qr Venta</span>
                            </a>
                        </li>
                    </ul>
                </li>
            @endif
        @endif
        
        <li class="treeview @if (Request::is('reports/arqueos*', 'reports/cargas*'
            , 'reports/dispositivos*' )) active @endif">
            <a href="#"><i class="fa fa-gears"></i> Operaciones Técnicas
                <span class="pull-right-container">
                    <i class="fa fa-angle-left pull-right"></i>
                </span>
            </a>
            <ul class="treeview-menu">
                @if (\Sentinel::getUser()->hasAccess('reporting.arqueos'))
                    <li @if (Request::is('reports/arqueos*')) class="active" @endif>
                        <a href="{{ route('reports.arqueos') }}">
                            <i class="fa fa-suitcase"></i><span>Arqueos</span>
                        </a>
                    </li>
                @endif
                @if (\Sentinel::getUser()->hasAccess('reporting.cargas'))
                    <li @if (Request::is('reports/cargas*')) class="active" @endif>
                        <a href="{{ route('reports.cargas') }}">
                            <i class="fa fa-money"></i><span>Cargas</span>
                        </a>
                    </li>
                @endif
                @if (\Sentinel::getUser()->hasAccess('reporting.dispositivos'))
                    <li @if (Request::is('reports/dispositivos*')) class="active" @endif>
                        <a href="{{ route('reports.dispositivos') }}">
                            <i class="fa fa-gears"></i><span>Dispositivos</span>
                        </a>
                    </li>
                @endif
                @if(\Sentinel::getUser()->hasAccess('depositos_arqueos'))
                <li @if(Request::is('depositos_arqueos*')) class="active" @endif>
                    <a href="{{ route('depositos_arqueos.index') }}">
                        <i class="fa fa-shopping-cart"></i><span>Dépositos de Arqueos</span>
                    </a>
                </li>
            @endif
            </ul>
        </li>
        @if (\Sentinel::getUser()->hasAccess('reporting.transacciones'))
            <!--cambiar el acceso -->
            <li @if (Request::is('reports/installations/*')) class="active" @endif>
                <a href="{{ route('reports.installations') }}">
                    <i class="fa fa-dashboard"></i><span>Instalaciones APP-Billetaje</span>
                </a>
            </li>
        @endif
        @if (\Sentinel::getUser()->hasAccess('reporting.contracts_atms'))
        <li @if (Request::is('reports/contracts/*')) class="active" @endif>
            <a href="{{ route('reports.contracts') }}">
                <i class="fa fa-file-text"></i><span>Contratos Miniterminales</span>
            </a>
        </li>
    @endif
        </ul>
        </li>
        @endif
        @if (\Sentinel::getUser()->hasAccess('saldos_linea'))
            <li class="treeview @if (Request::is('saldos*', 'reports/saldos' )) active @endif">
                <a href="#"><i class="fa fa-money"></i><span>Saldos en línea</span><i
                        class="fa fa-angle-left pull-right"></i></a>
                <ul class="treeview-menu">
                    <li @if (Request::is('reports/saldos*')) class="active" @endif>
                        <a href="{{ route('reports.saldos') }}">
                            <i class="fa fa-bar-chart"></i><span>Saldos en línea</span>
                        </a>
                    </li>
                    <li @if (Request::is('saldos/contable')) class="active" @endif>
                        <a href="{{ route('saldos.contable') }}">
                            <i class="fa fa-server"></i><span>Control Contable</span>
                        </a>
                    </li>
                </ul>
            </li>
        @endif



        <!-- MENÚ USSD -->
        @if (\Sentinel::getUser()->hasAccess('ussd'))
            <li class="treeview {{ Request::is('ussd*') ? 'active' : '' }}">
                <a href="#"><i class="fa fa-asterisk"></i><span>USSD</span><i
                        class="fa fa-angle-left pull-right"></i></a>

                @if (\Sentinel::getUser()->hasAccess('ussd_reports'))
                    <ul class="treeview-menu">
                        <li
                            class="treeview {{ Request::is('ussd/phone/ussd_phone_report*') || Request::is('ussd/option/ussd_option_report*') || Request::is('ussd/menu/ussd_menu_report*') || Request::is('ussd/transaction/ussd_transaction_report*') || Request::is('ussd/black_list/ussd_black_list_report*') ? 'active' : '' }}">

                            @if (\Sentinel::getUser()->hasAccess('ussd_phone_report'))
                                <li @if (Request::is('ussd/phone/ussd_phone_report*')) class="active" @endif>
                                    <a href="{{ route('ussd_phone_report') }}">
                                        <i class="fa fa-asterisk"></i><span>Teléfonos</span>
                                    </a>
                                </li>
                            @endif

                            @if (\Sentinel::getUser()->hasAccess('ussd_option_report'))
                                <li @if (Request::is('ussd/option/ussd_option_report*')) class="active" @endif>
                                    <a href="{{ route('ussd_option_report') }}">
                                        <i class="fa fa-asterisk"></i><span>Menú y Opciones</span>
                                    </a>
                                </li>
                            @endif

                            <!--
                            @if (\Sentinel::getUser()->hasAccess('ussd_menu_report'))
                                <li @if (Request::is('ussd/menu/ussd_menu_report*')) class="active" @endif>
                                    <a href="{{ route('ussd_menu_report') }}">
                                        <i class="fa fa-asterisk"></i><span>Menú</span>
                                    </a>
                                </li>
                            @endif
                            -->

                            @if (\Sentinel::getUser()->hasAccess('ussd_transaction_report'))
                                <li @if (Request::is('ussd/transaction/ussd_transaction_report*')) class="active" @endif>
                                    <a href="{{ route('ussd_transaction_report') }}">
                                        <i class="fa fa-asterisk"></i><span>Transacciones</span>
                                    </a>
                                </li>
                            @endif

                            @if (\Sentinel::getUser()->hasAccess('ussd_black_list_report'))
                                <li @if (Request::is('ussd/black_list/ussd_black_list_report*')) class="active" @endif>
                                    <a href="{{ route('ussd_black_list_report') }}">
                                        <i class="fa fa-asterisk"></i><span>Lista negra</span>
                                    </a>
                                </li>
                            @endif
                        </li>
                    </ul>
                @endif
            </li>
        @endif

        <!-- Concialiadores -->
        @if (\Sentinel::getUser()->hasAccess('conciliators'))
            <li class="treeview {{ Request::is('conciliators*') ? 'active' : '' }}">
                <a href="#"><i class="fa fa-files-o"></i>
                    <span>Conciliadores</span>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>

                <ul class="treeview-menu">
                    @if (\Sentinel::getUser()->hasAccess('ballot_conciliator'))
                        <li @if (Request::is('conciliators/ballot/ballot_conciliator*')) class="active" @endif>
                            <a href="{{ route('ballot_conciliator') }}">
                                <i class="fa fa-file"></i><span>Conciliador de boletas</span>
                            </a>
                        </li>
                    @endif

                    @if (\Sentinel::getUser()->hasAccess('transaction_conciliator'))
                        <li @if (Request::is('conciliators/transaction/transaction_conciliator*')) class="active" @endif>
                            <a href="{{ route('transaction_conciliator') }}">
                                <i class="fa fa-file"></i><span>Conciliador de transacciones</span>
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif

        <!-- Monitoreo de cajas para terminales -->
        @if (\Sentinel::getUser()->hasAccess('terminal_interaction_monitoring'))
            <li class="treeview {{ Request::is('terminal_interaction_monitoring*') ? 'active' : '' }}">
                <a href="#"><i class="fa fa-clone"></i>
                    <span>Interacciones de Terminal</span>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>

                <ul class="treeview-menu">
                    @if (\Sentinel::getUser()->hasAccess('terminal_interaction_monitoring_pos_box'))
                        <li @if (Request::is('terminal_interaction_monitoring_pos_box')) class="active" @endif>
                            <a href="{{ route('terminal_interaction_monitoring_pos_box') }}">
                                <i class="fa fa-cube"></i><span>Cajas de ATMs</span>
                            </a>
                        </li>
                    @endif
    
                    @if (\Sentinel::getUser()->hasAccess('terminal_interaction_monitoring_pos_box_movement'))
                        <li @if (Request::is('terminal_interaction_monitoring_pos_box_movement')) class="active" @endif>
                            <a href="{{ route('terminal_interaction_monitoring_pos_box_movement') }}">
                                <i class="fa fa-cube"></i><span>Movimientos de caja</span>
                            </a>
                        </li>
                    @endif

                    @if (\Sentinel::getUser()->hasAccess('terminal_interaction_monitoring_change_pin'))
                        <li @if (Request::is('terminal_interaction_monitoring_change_pin')) class="active" @endif>
                            <a href="{{ route('terminal_interaction_monitoring_change_pin') }}">
                                <i class="fa fa-cube"></i><span>Cambio de pin</span>
                            </a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif

        <!-- Módulo de comisiones pagadas y detalles -->
        @if (\Sentinel::getUser()->hasAccess('commissions'))
            <li class="treeview {{ Request::is('commissions_*') ? 'active' : '' }}">
                <a href="#"><i class="fa fa-money"></i>
                    <span>Comisiones</span>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>

                <ul class="treeview-menu">

                    @if (\Sentinel::getUser()->hasAccess('commissions_parameters_values'))  
                        <li @if (Request::is('commissions_parameters_values')) class="active" @endif>
                            <a href="{{ route('commissions_parameters_values') }}">
                                <i class="fa fa-money"></i><span>Parametros de Comisiones</span>
                            </a>
                        </li>
                    @endif

                    @if (\Sentinel::getUser()->hasAccess('commissions_paid'))                
                        <li @if (Request::is('commissions_paid')) class="active" @endif>
                            <a href="{{ route('commissions_paid') }}">
                                <i class="fa fa-money"></i><span>Comisiones pagadas</span>
                            </a>
                        </li>
                    @endif

                    @if (\Sentinel::getUser()->hasAccess('commissions_transactions'))  
                        <li @if (Request::is('commissions_transactions')) class="active" @endif>
                            <a href="{{ route('commissions_transactions') }}">
                                <i class="fa fa-money"></i><span>Comisiones de Transacciones</span>
                            </a>
                        </li>
                    @endif

                    @if (\Sentinel::getUser()->hasAccess('commissions_transactions_client'))  
                        <li @if (Request::is('commissions_transactions_client')) class="active" @endif>
                            <a href="{{ route('commissions_transactions_client') }}">
                                <i class="fa fa-money"></i><span>Comisiones en Línea</span>
                            </a>
                        </li>
                    @endif
                    @if (\Sentinel::getUser()->hasAccess('commissions_transactions_client'))  
                        <li @if (Request::is('commissions_transactions_client')) class="active" @endif>
                            <a href="{{ route('commissions_generales') }}">
                                <i class="fa fa-money"></i><span>Comisiones generales</span>
                            </a>
                        </li>
                    @endif

                    @if (\Sentinel::getUser()->hasAccess('commissions_for_clients'))  
                        <li @if (Request::is('commissions_for_clients')) class="active" @endif>
                            <a href="{{ route('commissions_for_clients') }}">
                                <i class="fa fa-money"></i><span>Comisiones para el Cliente</span>
                            </a>
                        </li>
                    @endif

                    @if (\Sentinel::getUser()->hasAccess('commissions_qr_invoices') ||  \Sentinel::getUser()->hasAccess('superuser'))  
                        <li @if (Request::is('comisionFactura')) class="active" @endif>
                            <a href="{{ route('comisionFactura') }}">
                                <i class="fa fa-money"></i><span>Comisiones Qr Venta</span>
                            </a>
                        </li>
                    @endif
                    
                </ul>
            </li>
        @endif


        @if (\Sentinel::getUser()->hasAccess('maps'))
            <li class="treeview active">

                <a href="#"><i class="fa fa-map-o"></i>
                    <span>Mapas</span>
                    <i class="fa fa-angle-left pull-right"></i>
                </a>

                <ul class="treeview-menu">
                    <li class="active">
                        <a href="{{ route('maps_atms') }}">
                            <i class="fa fa-map-o"></i><span>Ubicación de ATMs</span>
                        </a>
                    </li>
                </ul>
            </li>
        @endif

        {{-- Módulo de promociones --}}
        @if(\Sentinel::getUser()->hasAccess('campaigns'))
            <li class="treeview {{ (Request::is('content*','campaigns*','arts*','forms*','promotions_vouchers*','tickets*','tags*','branches_providers*','atmhascampagins*') ? 'active' : '') }}">
                <a href="#"><i class="fa fa-gift"></i><span>Gestor de promociones</span><i class="fa fa-angle-left pull-right"></i></a>
                <ul class="treeview-menu">
                    @if(\Sentinel::getUser()->hasAccess('content'))
                        <li @if(Request::is('contents*')) class="active" @endif>
                            <a href="{{ route('contents.index') }}"><i class="fa fa-tag"></i> <span>Contenidos</span></a>
                        </li>
                    @endif
                    @if(\Sentinel::getUser()->hasAccess('branches_providers'))
                        <li @if(Request::is('branches_providers*')) class="active" @endif>
                            <a href="{{ route('branches_providers.index') }}"><i class="fa fa-map-marker"></i> <span>Sucursales para retirar</span></a>
                        </li>
                    @endif
                    @if(\Sentinel::getUser()->hasAccess('campaigns'))
                        <li @if(Request::is('campaigns*')) class="active" @endif>
                            <a href="{{ route('campaigns.index') }}"><i class="fa fa-bullhorn"></i> <span>Campañas</span></a>
                        </li>
                    @endif
                </ul>
            </li>
        @endif

        {{-- Telegram --}}
        @if(\Sentinel::getUser()->hasAccess('telegram_permission') || \Sentinel::getUser()->hasAccess('superuser'))
            <li class="treeview {{ (Request::is('content*') ? 'active' : '') }}">
                <a href="#"><i class="fa fa-gift"></i><span>Telegram</span><i class="fa fa-angle-left pull-right"></i></a>
                <ul class="treeview-menu">
                        <li @if(Request::is('contents*')) class="active" @endif>
                            <a href="{{ route('generar_token_telegram') }}"><i class="fa fa-tag"></i> <span>Crear usuario telegram</span></a>
                        </li>
                </ul>
            </li>
        @endif

        </ul>

    </section>
    <!-- /.sidebar -->
</aside>