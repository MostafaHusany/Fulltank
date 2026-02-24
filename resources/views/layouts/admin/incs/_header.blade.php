<header class="navbar navbar-dark navbar-expand-md sticky-top bg-primary flex-md-nowrap p-0 shadow">
    <a class="navbar-brand col-md-3 col-lg-2 me-0 px-3 fs-6" href="{{ url('admin/') }}">
        <!-- <img style="width: 120px" src="{{ asset('static_images/logo.png') }}" alt=""> -->
         {{ ENV('APP_NAME') }}
    </a>
    <button class="navbar-toggler position-absolute d-md-none collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu" aria-controls="sidebarMenu" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <!-- <input class="form-control form-control-dark w-100 rounded-0 border-0" type="text" placeholder="Search" aria-label="Search"> -->
    
    <!-- <div class="navbar-nav">
        <div class="nav-item text-nowrap">
            <a class="nav-link px-3" href="#">Sign out</a>
        </div>
    </div> -->
    
    
    <div class="collapse navbar-collapse">
        <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
            <li class="nav-item">
                <a class="nav-link" !data-widget="fullscreen"     
                    href="{{ App::getLocale() == 'ar' ? LaravelLocalization::getLocalizedURL('en') : LaravelLocalization::getLocalizedURL('ar') }}"
                    role="button"
                >
                <span class="text-uppercase badge badge-pill badge-dark">{{ App::getLocale() == 'ar' ? 'en' : 'ar' }}</span>
                </a>
            </li>
            
            <li class="nav-item dropdown mx-2">
                <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user-cog"></i>
                </a>
                <ul class="dropdown-menu dropdown-menu-lg-end">
                    <li><a class="dropdown-item" href="{{ route('admin.profile.index') }}">@lang('layouts.Profile')</a></li>
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