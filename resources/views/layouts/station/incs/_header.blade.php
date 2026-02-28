<header class="navbar navbar-dark navbar-expand-md sticky-top bg-station flex-md-nowrap p-0 shadow">
    <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6" href="{{ route('station.dashboard') }}">
        {{ ENV('APP_NAME') }}
    </a>
    <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>
    
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
            {{-- Balance Widget in Navbar --}}
            @if(isset($stationBalance))
            <li class="nav-item me-3">
                <a class="nav-link d-flex align-items-center px-3 py-1 rounded" href="{{ route('station.financials.index') }}" style="background: rgba(255,255,255,0.15);">
                    <i class="fas fa-hand-holding-usd me-2"></i>
                    <div class="text-start">
                        <small class="d-block opacity-75" style="font-size: 0.7rem; line-height: 1;">@lang('station.outstanding_balance')</small>
                        <span class="fw-bold" style="font-size: 0.95rem;">{{ $stationBalance['formatted_balance'] }} @lang('station.currency')</span>
                    </div>
                </a>
            </li>
            @endif

            <li class="nav-item">
                <a class="nav-link" 
                    href="{{ App::getLocale() == 'ar' ? LaravelLocalization::getLocalizedURL('en') : LaravelLocalization::getLocalizedURL('ar') }}"
                    role="button"
                >
                <span class="text-uppercase badge badge-pill badge-dark">{{ App::getLocale() == 'ar' ? 'en' : 'ar' }}</span>
                </a>
            </li>

            <li class="nav-item dropdown mx-2">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user-circle"></i>
                    <span class="ms-1">{{ auth()->user()->name }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-lg-end">
                    <li><a class="dropdown-item" href="#">@lang('layouts.Profile')</a></li>
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
