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
            
            <li class="treeview @if (Request::is('claro*')) active @endif">
                <a href="#"><i class="fa fa-archive"></i> Reportes
                    <span class="pull-right-container">
                        <i class="fa fa-angle-left pull-right"></i>
                    </span>
                </a>
                <ul class="treeview-menu">                    
                    <li @if (Request::is('claro/transactions*')) class="active" @endif>
                        <a href="{{ route('claro.transactions') }}">
                            <i class="fa fa-tag" aria-hidden="true"></i>
                            <span>Historico de transacciones</span>
                        </a>                                                        
                    </li>                    
                </ul>
            </li> 

        </ul>                                                    
    </section>
    <!-- /.sidebar -->
</aside>