<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class WorkshopPermissionsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!in_array(auth()->user()->category, ['workshop_manager', 'workshop_employee'])) {
            return redirect()->route('home');
        }

        if ($request->routeIs('workshop.error.no_permission') || $request->routeIs('workshop.error.is_disabled')) {
            return $next($request);
        }

        if (!auth()->user()->is_active) {
            session()->flash('account_is_disabled', true);
            return redirect()->route('error.is_disabled');
        }

        if (auth()->user()->category == 'workshop_manager') {
            return $next($request);
        }

        if ($request->routeIs('workshop.profile.*')) {
            return $next($request);
        }

        if ($request->routeIs('workshop.search.*')) {
            return $next($request);
        }
        
        $permissions = [
            'users', 'workshops', 'workshopsOrders', 'transactions' 
        ];

        foreach($permissions as $permission) {
            if ($request->routeIs('workshop.' . $permission . '.*')) {
                if ($request->isMethod('get') && auth()->user()->isAbleTo($permission . '_*')) {
                    return $next($request);
                }

                if ($request->isMethod('post') && auth()->user()->isAbleTo($permission . '_add')) {
                    return $next($request);
                }

                if ($request->isMethod('put') && auth()->user()->isAbleTo($permission . '_edit')) {
                    return $next($request);
                }

                if ($request->isMethod('delete') && auth()->user()->isAbleTo($permission . '_delete')) {
                    return $next($request);
                }
            }
        }// end :: foreach

        if ($request->routeIs('workshop.workshopsOrders.*') || $request->routeIs('workshop.transactions.*')) {
            return $next($request);
        }

        // if () {}
        
        session()->flash('has_no_permission', true);
        return redirect()->route('workshop.error.no_permission');
    }
}
