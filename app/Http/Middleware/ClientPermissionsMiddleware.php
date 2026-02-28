<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class ClientPermissionsMiddleware
{
    /**
     * Handle an incoming request.
     * Ensures only users with category 'client' can access client routes.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        if (auth()->user()->category !== 'client') {
            abort(403, __('client.access_denied'));
        }

        if (!auth()->user()->is_active) {
            auth()->logout();
            session()->flash('account_is_disabled', true);
            return redirect()->route('login')->with('error', __('client.account_disabled'));
        }

        return $next($request);
    }
}
