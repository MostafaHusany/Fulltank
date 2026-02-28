@php
    $wallet = auth()->user()->wallet;
    $balance = $wallet ? number_format($wallet->balance, 2) : '0.00';
@endphp

<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3 sidebar-sticky">
        
        {{-- Wallet Balance Widget --}}
        <div class="card mx-3 mb-3 bg-success text-white">
            <div class="card-body text-center py-3">
                <small class="d-block mb-1">@lang('client.wallet_balance')</small>
                <h4 class="mb-0 fw-bold" id="wallet-balance-display">{{ $balance }} @lang('client.currency')</h4>
            </div>
        </div>

        <ul class="nav flex-column">
            {{-- Dashboard --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('client.dashboard*') ? 'active' : '' }}" href="{{ route('client.dashboard') }}">
                    <i class="fas fa-tachometer-alt fa-fw me-2"></i>
                    @lang('client.nav.dashboard')
                </a>
            </li>
            
            {{-- Live Monitor --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('client.live_monitor*') ? 'active' : '' }}" href="{{ route('client.live_monitor.index') }}">
                    <i class="fas fa-broadcast-tower fa-fw me-2"></i>
                    @lang('client.nav.live_monitor')
                    <span class="badge bg-danger ms-1" style="font-size: 0.6rem;">LIVE</span>
                </a>
            </li>
            
            {{-- Notifications --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('client.notifications*') ? 'active' : '' }}" href="{{ route('client.notifications.index') }}">
                    <i class="fas fa-bell fa-fw me-2"></i>
                    @lang('client.nav.notifications')
                    @if(auth()->user()->unreadNotifications->count() > 0)
                        <span class="badge bg-danger ms-1">{{ auth()->user()->unreadNotifications->count() }}</span>
                    @endif
                </a>
            </li>
            
            {{-- Vehicles --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('client.vehicles*') ? 'active' : '' }}" href="{{ route('client.vehicles.index') }}">
                    <i class="fas fa-car fa-fw me-2"></i>
                    @lang('client.nav.vehicles')
                </a>
            </li>

            {{-- Drivers --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('client.drivers*') ? 'active' : '' }}" href="{{ route('client.drivers.index') }}">
                    <i class="fas fa-id-card fa-fw me-2"></i>
                    @lang('client.nav.drivers')
                </a>
            </li>

            {{-- Quota Management --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('client.quotas*') ? 'active' : '' }}" href="{{ route('client.quotas.index') }}">
                    <i class="fas fa-tachometer-alt fa-fw me-2"></i>
                    @lang('client.nav.quotas')
                </a>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted text-uppercase">
            <span>@lang('client.nav.financial')</span>
        </h6>
        <ul class="nav flex-column mb-2">
            {{-- Wallet --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('client.wallet*') ? 'active' : '' }}" href="{{ route('client.wallet.index') }}">
                    <i class="fas fa-wallet fa-fw me-2"></i>
                    @lang('client.nav.wallet')
                </a>
            </li>

            {{-- Deposit Requests --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('client.deposits*') ? 'active' : '' }}" href="{{ route('client.deposits.index') }}">
                    <i class="fas fa-money-bill-wave fa-fw me-2"></i>
                    @lang('client.nav.deposits')
                </a>
            </li>

            {{-- Transactions --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('client.transactions*') ? 'active' : '' }}" href="{{ route('client.transactions.index') }}">
                    <i class="fas fa-gas-pump fa-fw me-2"></i>
                    @lang('client.nav.transactions')
                </a>
            </li>
        </ul>

        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted text-uppercase">
            <span>@lang('client.nav.analytics')</span>
        </h6>
        <ul class="nav flex-column mb-2">
            {{-- Reports --}}
            <li class="nav-item">
                <a class="nav-link {{ request()->routeIs('client.reports*') ? 'active' : '' }}" href="{{ route('client.reports.index') }}">
                    <i class="fas fa-chart-line fa-fw me-2"></i>
                    @lang('client.nav.reports')
                </a>
            </li>
        </ul>
    </div>
</nav>
