<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3 sidebar-sticky">
        @php 
            $user_category = auth()->user()->category;  
        @endphp

        @if( $user_category == 'admin' 
            || auth()->user()->isAbleTo('dashboard_*') 
            || auth()->user()->isAbleTo('users_*')
            || auth()->user()->isAbleTo('roles_*')
            || auth()->user()->isAbleTo('clients_*')
            || auth()->user()->isAbleTo('vehicles_*')
        )
        <ul class="nav flex-column">
            @if( $user_category == 'admin' || auth()->user()->isAbleTo('dashboard_*') )
            <li class="nav-item">
                <a class="nav-link {{ str_ends_with(Request::path(), 'admin') ? 'active' : ''}}" aria-current="page" href="{{ route('admin.dashboard.index') }}">
                    <i class="fas mx-1 fa-tachometer-alt"></i>
                    <span class="mx-1">@lang('layouts.Dashboard')</span>
                </a>
            </li>
            @endif
            
            @if( $user_category == 'admin' || auth()->user()->isAbleTo('users_*') )
            <li class="nav-item">
                <a class="nav-link {{ str_contains(Request::path(), '/users') ? 'active' : ''}}" href="{{ route('admin.users.index') }}">
                    <i class="fas mx-1 fa-user-cog"></i>
                    <span class="mx-1">@lang('layouts.Users')</span>
                </a>
            </li>
            @endif

            @if( $user_category == 'admin' || auth()->user()->isAbleTo('roles_*') )
            <li class="nav-item">
                <a class="nav-link {{ str_contains(Request::path(), '/roles') ? 'active' : ''}}" href="{{ route('admin.roles.index') }}">
                    <i class="fas mx-1 fa-id-card-alt"></i>
                    <span class="mx-1">@lang('layouts.Roles')</span>
                </a>
            </li>
            @endif

            @if( $user_category == 'admin' || auth()->user()->isAbleTo('clients_*') )
            <li class="nav-item">
                <a class="nav-link {{ str_contains(Request::path(), '/clients') ? 'active' : ''}}" href="{{ route('admin.clients.index') }}">
                    <i class="fas mx-1 fa-users"></i>
                    <span class="mx-1">@lang('clients.Title Administration')</span>
                </a>
            </li>
            @endif

            @if( $user_category == 'admin' || auth()->user()->isAbleTo('vehicles_*') )
            <li class="nav-item">
                <a class="nav-link {{ str_contains(Request::path(), '/vehicles') ? 'active' : ''}}" href="{{ route('admin.vehicles.index') }}">
                    <i class="fas mx-1 fa-car"></i>
                    <span class="mx-1">@lang('vehicles.Title Administration')</span>
                </a>
            </li>
            @endif
            
        </ul>
        @endif


        @if( $user_category == 'admin' || auth()->user()->isAbleTo('districts_*') )
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted text-uppercase">
            <span>@lang('layouts.Settings')</span>
        </h6>

        <ul class="nav flex-column mb-2">
            @if( $user_category == 'admin' || auth()->user()->isAbleTo('districts_*') )
            <li class="nav-item">
                <a class="nav-link {{ str_contains(Request::path(), '/districts') ? 'active' : ''}}" href="{{ route('admin.districts.index') }}">
                    <i class="fas mx-1 fa-map-marker-alt"></i>
                    <span class="mx-1">@lang('layouts.Districts')</span>
                </a>
            </li>
            @endif
        </ul>
        @endif

        
    </div>
</nav>