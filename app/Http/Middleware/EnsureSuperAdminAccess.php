<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSuperAdminAccess
{
    /**
     * Only Super Admin can manage roles, permissions, menu access and users.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !method_exists($user, 'isSuperAdmin') || !$user->isSuperAdmin()) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Access denied. Only Super Admin can manage roles, permissions and users.',
                ], 403);
            }

            return redirect()->route('home')->with('error', 'Only Super Admin can manage roles, permissions and users.');
        }

        return $next($request);
    }
}
