<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use App\Services\Station\BalanceService;

// use App\Models\WorkshopOrder;
// use App\Observers\WorkshopOrderObserver;

// use App\Models\Notification;
// use App\Observers\NotificationObserver;

// use App\Models\TruckingOrder;
// use App\Observers\SearchForTrucker;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::composer('layouts.station.*', function ($view) {
            $stationBalance = null;
            
            if (auth()->check()) {
                $user = auth()->user();
                if ($user->category === 'station_manager' || $user->managedStation) {
                    $balanceService = app(BalanceService::class);
                    $stationId = $balanceService->getStationIdForUser($user);
                    $stationBalance = $balanceService->getStationBalance($stationId);
                }
            }
            
            $view->with('stationBalance', $stationBalance);
        });
        // Notification::observe(NotificationObserver::class);

        $gardianClassifications = [
            'Father', 'Mother', 
            'Grandfather', 'Grandmother', 
            'Paternal Aunt', 'Paternal Uncle', 
            'Maternal Aunt', 'Maternal Uncle',
            'Guardian'
        ];

            
        View::share('gardianClassifications', $gardianClassifications);

        $employeesCategories = [
            'bus driver',
            // 'teacher',
            'manager',
            'worker',
            'security',
            'it',
        ];
        
        View::share('employeesCategories', $employeesCategories);
        
        $busModels = [
            'Mercedes-Benz',
            'Volvo',
            'MAN',
            'Scania',
            'Iveco',
            'DAF',
            'Renault',
            'Setra',
            'King Long/Yutong',
            'Solaris',
            'Isuzu',
            'Toyota',
            'Nissan',
            'Mitsubishi',
            'Otokar',
        ];

        View::share('busModels', $busModels);

    }
}
