<div class="navbar-custom-menu">
    <ul class="nav navbar-nav">
        @if (!Sentinel::getUser()->hasRole('red_claro'))
            @include('partials._navbar_right_notifications')
        @endif
                <!-- User Account Menu -->
        <li class="dropdown user user-menu">
            <!-- Menu Toggle Button -->
            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                <!-- The user image in the navbar-->
                <img src="{{ "/bower_components/admin-lte/dist/img/user7-128x128.jpg" }}" class="user-image"
                     alt="User Image">
                <!-- hidden-xs hides the username on small devices so only the image appears. -->
                <span class="hidden-xs">{{ Sentinel::getUser()->description }}</span>
            </a>
            <ul class="dropdown-menu">
                <!-- The user image in the menu -->
                <li class="user-header">
                    <img src="{{ "/bower_components/admin-lte/dist/img/user7-128x128.jpg" }}" class="img-circle"
                         alt="User Image">

                    <p>
                        {{Sentinel::getUser()->username}}
                        <small>Miembro desde {{ Sentinel::getUser()->created_at }}</small>
                    </p>
                </li>
                <!-- Menu Body -->
                <li class="user-body">
                    <!-- <div class="col-xs-4 text-center">
                      <a href="#">Followers</a>
                    </div>
                    <div class="col-xs-4 text-center">
                      <a href="#">Sales</a>
                    </div>
                    <div class="col-xs-4 text-center">
                      <a href="#">Friends</a>
                    </div> -->
                </li>
                <!-- Menu Footer-->
                <li class="user-footer">
                    <div class="pull-left">
                        <a href="{{ route('users.showProfile', ['id' => \Sentinel::getUser()->id]) }}"
                           class="btn btn-success btn-flat">Perfil</a>
                    </div>
                    <div class="pull-right">
                        <a href="{{ route('logout') }}" class="btn btn-danger btn-flat">Salir</a>
                    </div>
                </li>
            </ul>
        </li>
        <!-- Control Sidebar Toggle Button -->
        <li>
            <a href="#" data-toggle="control-sidebar"><i class="fa fa-gears"></i></a>
        </li>
    </ul>