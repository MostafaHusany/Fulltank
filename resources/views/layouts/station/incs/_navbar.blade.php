<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3 sidebar-sticky">
        
        {{-- Station Info Widget --}}
        <div class="card mx-3 mb-3 bg-station text-white">
            <div class="card-body text-center py-3">
                <i class="fas fa-gas-pump fa-2x mb-2"></i>
                <small class="d-block mb-1">@lang('station.station_panel')</small>
                <h6 class="mb-0 fw-bold">{{ auth()->user()->name }}</h6>
            </div>
        </div>

        {{-- Outstanding Balance Widget --}}
        @if(isset($stationBalance))
        <a href="{{ route('station.financials.index') }}" class="text-decoration-none">
            <div class="card mx-3 mb-3 border-0 shadow-sm" style="background: linear-gradient(135deg, #28a745, #20c997);">
                <div class="card-body text-center py-3 text-white">
                    <div class="d-flex align-items-center justify-content-center mb-2">
                        <i class="fas fa-hand-holding-usd fa-lg me-2"></i>
                        <small>@lang('station.outstanding_balance')</small>
                    </div>
                    <h4 class="mb-0 fw-bold" id="station-balance-display">
                        {{ $stationBalance['formatted_balance'] }} @lang('station.currency')
                    </h4>
                    <small class="opacity-75">@lang('station.current_due')</small>
                </div>
            </div>
        </a>
        @endif

        <ul class="nav flex-column">
            {{-- Dashboard --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('station.dashboard*') ? 'active' : '' }}" href="{{ route('station.dashboard') }}">
                    <i class="fas fa-tachometer-alt fa-fw me-2"></i>
                    @lang('station.nav.dashboard')
                </a>
            </li>
            
            {{-- Workers --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('station.workers*') ? 'active' : '' }}" href="{{ route('station.workers.index') }}">
                    <i class="fas fa-users fa-fw me-2"></i>
                    @lang('station.nav.workers')
                </a>
            </li>
            
            {{-- Transactions --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('station.transactions*') ? 'active' : '' }}" href="{{ route('station.transactions.index') }}">
                    <i class="fas fa-exchange-alt fa-fw me-2"></i>
                    @lang('station.nav.transactions')
                </a>
            </li>
            
            {{-- Financials --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('station.financials*') ? 'active' : '' }}" href="{{ route('station.financials.index') }}">
                    <i class="fas fa-wallet fa-fw me-2"></i>
                    @lang('station.nav.financials')
                </a>
            </li>
        </ul>
    </div>
</nav>
