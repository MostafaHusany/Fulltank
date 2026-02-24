<?php

namespace App\Http\Middleware;

use Closure;

class AdminPermissionsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {

        if (!in_array(auth()->user()->category, ['admin', 'technical'])) {
            return redirect()->route('home');
        }

        if ($request->routeIs('admin.error.no_permission') || $request->routeIs('error.is_disabled')) {
            return $next($request);
        }

        if (!auth()->user()->is_active) {
            session()->flash('account_is_disabled', true);
            return redirect()->route('error.is_disabled');
        }

        if (auth()->user()->hasRole('admin') || auth()->user()->category == 'admin') {
            return $next($request);
        }

        if ($request->routeIs('admin.profile.*')) {
            return $next($request);
        }

        if ($request->routeIs('admin.search.*')) {
            return $next($request);
        }
        
        $permissions = [
            'users', 'roles', 'clients', 'cars',
            'managers' , 'workshops', 'workshopsOrders', 'districts',
            'services', 'wallets', 'walletsCharges', 'transactions', 'carBrands', 
        ];

        foreach($permissions as $permission) {
            if ($request->routeIs('admin.' . $permission . '.*')) {
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

        // HANDLE SPECIAL CASES 
        if (( $request->routeIs('admin.nearby.*') || $request->routeIs('admin.nearbyCategories.*') ) && auth()->user()->isAbleTo('schools_edit')) {
            return $next($request);
        }

        session()->flash('has_no_permission', true);
        return redirect()->route('admin.error.no_permission');
    }
}
