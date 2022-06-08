<!-- ========== Left Sidebar Start ========== -->
<div class="vertical-menu">

    <!-- LOGO -->
    <div class="navbar-brand-box">
        <a href="{{url('index')}}" class="logo logo-dark">
            <span class="logo-sm">
                <img src="{{ URL::asset('/assets/images/logo-sm.png?') . config('app.version')}}" alt="" height="25">
            </span>
            <span class="logo-lg">
                <img src="{{ URL::asset('/assets/images/logo-dark.png?') . config('app.version')}}" alt="" height="30">
            </span>
        </a>

        <a href="{{url('index')}}" class="logo logo-light">
            <span class="logo-sm">
                <img src="{{ URL::asset('/assets/images/logo-sm.png?') . config('app.version')}}" alt="" height="25">
            </span>
            <span class="logo-lg">
                <img src="{{ URL::asset('/assets/images/logo-light.png?') . config('app.version')}}" alt="" height="30">
            </span>
        </a>
    </div>

    <button type="button" class="btn btn-sm px-3 font-size-16 header-item waves-effect vertical-menu-btn">
        <i class="fa fa-fw fa-bars"></i>
    </button>

    <div data-simplebar class="sidebar-menu-scroll">

        <!--- Sidemenu -->
        <div id="sidebar-menu">
            <!-- Left Menu Start -->
            <ul class="metismenu list-unstyled" id="side-menu">
                <li class="menu-title">@lang('translation.Menu')</li>

                <li>
                    <a href="{{url('users')}}">
                        <i class="uil-user-circle"></i>
                        <span>@lang('translation.Users')</span>
                    </a>
                </li>

                <li>
                    <a href="{{url('events')}}">
                        <i class="uil-calender"></i>
                        <span>@lang('translation.Events')</span>
                    </a>
                </li>

                <li>
                    <a href="{{url('logs')}}">
                        <i class="uil-file-info-alt"></i>
                        {{-- <span>@lang('translation.Logs')</span> --}}
                        <span>Logs</span>
                    </a>
                </li>
            </ul>
        </div>
        <!-- Sidebar -->
    </div>
</div>
<!-- Left Sidebar End -->
