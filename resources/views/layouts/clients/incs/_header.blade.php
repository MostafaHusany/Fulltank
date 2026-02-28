<header class="navbar navbar-dark navbar-expand-md sticky-top bg-success flex-md-nowrap p-0 shadow">
    <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6" href="{{ route('client.dashboard') }}">
        {{ ENV('APP_NAME') }}
    </a>
    <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
            <li class="nav-item">
                <a class="nav-link" 
                    href="{{ App::getLocale() == 'ar' ? LaravelLocalization::getLocalizedURL('en') : LaravelLocalization::getLocalizedURL('ar') }}"
                    role="button"
                >
                <span class="text-uppercase badge badge-pill badge-dark">{{ App::getLocale() == 'ar' ? 'en' : 'ar' }}</span>
                </a>
            </li>
            
            {{-- Notifications Dropdown --}}
            <li class="nav-item dropdown mx-2" id="notificationDropdown">
                <a class="nav-link position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false" id="notificationBell">
                    <i class="fas fa-bell"></i>
                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger notification-badge" id="notificationCount" style="display: none;">
                        0
                    </span>
                </a>
                <div class="dropdown-menu dropdown-menu-end notification-dropdown p-0" style="width: 320px; max-height: 400px;">
                    <div class="d-flex justify-content-between align-items-center px-3 py-2 border-bottom bg-light">
                        <span class="fw-bold">@lang('notifications.title')</span>
                        <a href="#" class="text-muted small" id="markAllRead">@lang('notifications.mark_all_read')</a>
                    </div>
                    <div class="notification-list" id="notificationList" style="max-height: 300px; overflow-y: auto;">
                        <div class="text-center py-4 text-muted" id="noNotifications">
                            <i class="fas fa-bell-slash fa-2x mb-2"></i>
                            <p class="mb-0">@lang('notifications.no_notifications')</p>
                        </div>
                    </div>
                    <div class="border-top text-center py-2">
                        <a href="{{ route('client.notifications.index') }}" class="text-decoration-none">
                            @lang('notifications.view_all')
                        </a>
                    </div>
                </div>
            </li>

            <li class="nav-item dropdown mx-2">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user-circle"></i>
                    <span class="ms-1">{{ auth()->user()->name }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-lg-end">
                    <li><a class="dropdown-item" href="{{ route('client.profile.index') }}">@lang('layouts.Profile')</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="{{ route('logout') }}"
                            onclick="event.preventDefault();
                            document.getElementById('logout-form').submit();">
                            @lang('layouts.Logout')
                        </a>
                        <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</header>
