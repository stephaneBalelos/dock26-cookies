<?php

namespace Dock26Cookies\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthenticateAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!is_user_logged_in()) {
            return response()->json([
                'message' => 'Authentication required'
            ], 401);
        }

        if (!current_user_can('manage_options')) {
            return response()->json([
                'message' => 'Admin access required'
            ], 403);
        }

        return $next($request);
    }
}
