<nav id="sidebarMenu" class="col-md-3 col-lg-2 d-md-block bg-light sidebar collapse">
    <div class="position-sticky pt-3 sidebar-sticky">
        @php
            $user = auth()->user();
            $isAdmin = $user->category == 'admin' || $user->hasRole('admin');
            $path = Request::path();

            /**
             * OR Logic for Permission Check
             * A nav link is visible if the user has ANY permission for that module
             * Checks: {module}_show, {module}_add, {module}_edit, {module}_delete
             */
            $canAccess = function ($permission) use ($user, $isAdmin) {
                if ($isAdmin) return true;

                $checkModuleAccess = function ($module) use ($user) {
                    return $user->isAbleTo($module . '_show') || 
                           $user->isAbleTo($module . '_add') || 
                           $user->isAbleTo($module . '_edit') || 
                           $user->isAbleTo($module . '_delete') ||
                           $user->isAbleTo($module . '_*');
                };

                if (is_array($permission)) {
                    foreach ($permission as $perm) {
                        if ($checkModuleAccess($perm)) return true;
                    }
                    return false;
                }

                return $checkModuleAccess($permission);
            };

            $isActive = function ($segment, $exclude = null) use ($path) {
                if ($exclude && str_contains($path, $exclude)) {
                    return false;
                }
                return str_contains($path, $segment);
            };

            $navSections = [
                [
                    'key'   => 'dashboard',
                    'title' => null,
                    'items' => [
                        [
                            'permission' => 'dashboard',
                            'route'      => 'admin.dashboard.index',
                            'icon'       => 'fa-chart-line',
                            'label'      => 'dashboard.Title',
                            'segment'    => '/dashboard',
                        ],
                    ],
                ],
                [
                    'key'   => 'user_management',
                    'title' => 'layouts.User_Management',
                    'items' => [
                        [
                            'permission' => 'users',
                            'route'      => 'admin.users.index',
                            'icon'       => 'fa-user-cog',
                            'label'      => 'layouts.Users',
                            'segment'    => '/users',
                        ],
                        [
                            'permission' => 'roles',
                            'route'      => 'admin.roles.index',
                            'icon'       => 'fa-id-card-alt',
                            'label'      => 'layouts.Roles',
                            'segment'    => '/roles',
                        ],
                    ],
                ],
                [
                    'key'   => 'fleet_management',
                    'title' => 'layouts.Fleet_Management',
                    'items' => [
                        [
                            'permission' => 'clients',
                            'route'      => 'admin.clients.index',
                            'icon'       => 'fa-users',
                            'label'      => 'clients.Title Administration',
                            'segment'    => '/clients',
                        ],
                        [
                            'permission' => 'vehicles',
                            'route'      => 'admin.vehicles.index',
                            'icon'       => 'fa-car',
                            'label'      => 'vehicles.Title Administration',
                            'segment'    => '/vehicles',
                            'exclude'    => 'vehicle-quotas',
                        ],
                        [
                            'permission' => 'vehicles',
                            'route'      => 'admin.vehicleQuotas.index',
                            'icon'       => 'fa-tachometer-alt',
                            'label'      => 'vehicle_quotas.Title',
                            'segment'    => 'vehicle-quotas',
                        ],
                        [
                            'permission' => 'drivers',
                            'route'      => 'admin.drivers.index',
                            'icon'       => 'fa-id-badge',
                            'label'      => 'drivers.Title Administration',
                            'segment'    => '/drivers',
                        ],
                    ],
                ],
                [
                    'key'   => 'station_operations',
                    'title' => 'layouts.Station_Operations',
                    'items' => [
                        [
                            'permission' => 'stations',
                            'route'      => 'admin.stations.index',
                            'icon'       => 'fa-gas-pump',
                            'label'      => 'stations.Title',
                            'segment'    => '/stations',
                            'exclude'    => 'station-w',
                        ],
                        [
                            'permission' => 'stationWorkers',
                            'route'      => 'admin.stationWorkers.index',
                            'icon'       => 'fa-hard-hat',
                            'label'      => 'station_workers.Title',
                            'segment'    => '/station-workers',
                        ],
                        [
                            'permission' => 'stationWallets',
                            'route'      => 'admin.stationWallets.index',
                            'icon'       => 'fa-cash-register',
                            'label'      => 'station_wallets.Title',
                            'segment'    => '/station-wallets',
                        ],
                    ],
                ],
                [
                    'key'   => 'financial',
                    'title' => 'layouts.Financial',
                    'items' => [
                        [
                            'permission' => 'fuelTransactions',
                            'route'      => 'admin.fuelTransactions.index',
                            'icon'       => 'fa-exchange-alt',
                            'label'      => 'fuel_transactions.Title',
                            'segment'    => '/fuel-transactions',
                        ],
                        [
                            'permission' => 'settlements',
                            'route'      => 'admin.settlements.index',
                            'icon'       => 'fa-hand-holding-usd',
                            'label'      => 'settlements.Title',
                            'segment'    => '/settlements',
                        ],
                        [
                            'permission' => 'wallets',
                            'route'      => 'admin.wallets.index',
                            'icon'       => 'fa-wallet',
                            'label'      => 'wallets.Title Administration',
                            'segment'    => '/wallets',
                            'exclude'    => 'station-wallets',
                        ],
                        [
                            'permission' => 'depositRequests',
                            'route'      => 'admin.depositRequests.index',
                            'icon'       => 'fa-money-bill-wave',
                            'label'      => 'deposit_requests.Title Administration',
                            'segment'    => '/deposit-requests',
                        ],
                        [
                            'permission' => ['financialSettings', 'paymentMethods'],
                            'route'      => 'admin.financialSettings.index',
                            'icon'       => 'fa-sliders-h',
                            'label'      => 'deposit_requests.Financial Settings',
                            'segment'    => '/financial-settings',
                        ],
                        [
                            'permission' => 'reports',
                            'route'      => 'admin.reports.index',
                            'icon'       => 'fa-file-alt',
                            'label'      => 'reports.Title',
                            'segment'    => '/reports',
                        ],
                    ],
                ],
                [
                    'key'   => 'settings',
                    'title' => 'layouts.Settings',
                    'items' => [
                        [
                            'permission' => 'governorates',
                            'route'      => 'admin.governorates.index',
                            'icon'       => 'fa-map',
                            'label'      => 'governorates.Title',
                            'segment'    => '/governorates',
                        ],
                        [
                            'permission' => 'fuelTypes',
                            'route'      => 'admin.fuelTypes.index',
                            'icon'       => 'fa-gas-pump',
                            'label'      => 'fuel_types.Title',
                            'segment'    => '/fuel-types',
                        ],
                        [
                            'permission' => 'activityLogs',
                            'route'      => 'admin.activityLogs.index',
                            'icon'       => 'fa-history',
                            'label'      => 'activity_logs.Title',
                            'segment'    => '/activity-logs',
                        ],
                    ],
                ],
                [
                    'key'   => 'developer',
                    'title' => 'layouts.Developer_Tools',
                    'items' => [
                        [
                            'permission' => 'apiTester',
                            'route'      => 'admin.apiTester.index',
                            'icon'       => 'fa-flask',
                            'label'      => 'layouts.API_Test_Lab',
                            'segment'    => '/api-tester',
                        ],
                        [
                            'permission' => 'apiTester',
                            'route'      => 'admin.apiSimulator.index',
                            'icon'       => 'fa-rocket',
                            'label'      => 'layouts.Full_Cycle_Simulator',
                            'segment'    => '/api-simulator',
                        ],
                    ],
                ],
            ];
        @endphp

        @foreach($navSections as $section)
            @php
                $visibleItems = collect($section['items'])->filter(function ($item) use ($canAccess) {
                    return $canAccess($item['permission']);
                });
            @endphp

            @if($visibleItems->isNotEmpty())
                {{-- Section Header (skip for dashboard) --}}
                @if($section['title'])
                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted text-uppercase">
                    <span>@lang($section['title'])</span>
                </h6>
                @endif

                {{-- Section Items --}}
                <ul class="nav flex-column @if($section['title']) mb-2 @endif">
                    @foreach($visibleItems as $item)
                        @php
                            $activeClass = '';
                            if (isset($item['active'])) {
                                $activeClass = $item['active'] ? 'active' : '';
                            } elseif (isset($item['segment'])) {
                                $activeClass = $isActive($item['segment'], $item['exclude'] ?? null) ? 'active' : '';
                            }
                        @endphp
                        <li class="nav-item">
                            <a class="nav-link {{ $activeClass }}" href="{{ route($item['route']) }}">
                                <i class="fas mx-1 {{ $item['icon'] }}"></i>
                                <span class="mx-1">@lang($item['label'])</span>
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        @endforeach
    </div>
</nav>
