<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiCategoryMiddleware
{
    public function handle(Request $request, Closure $next, string ...$categories): Response
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'status'  => false,
                'message' => __('api.auth.unauthenticated'),
            ], 401);
        }

        if (!in_array($user->category, $categories)) {
            return response()->json([
                'status'  => false,
                'message' => __('api.auth.category_not_allowed'),
            ], 403);
        }

        if (!$user->is_active) {
            return response()->json([
                'status'  => false,
                'message' => __('api.auth.account_inactive'),
            ], 403);
        }

        return $next($request);
    }
}
