<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiRoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $path = request()->route()->uri;

        $route_role = str_replace('-', '_', explode('/', $path)[2]);

        if (auth()->user()->category !== $route_role) {
            return response()->json([
                'success' => false,
                'message' => 'FORBIDDEN'
            ], 403);
        }

        return $next($request);
    }
}
