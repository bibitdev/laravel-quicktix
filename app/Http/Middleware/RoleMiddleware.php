<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        if (!auth()->check()) {
            // Check if request is API
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Unauthenticated. Please login first.'
                ], 401);
            }
            return redirect()->route('login.page');
        }

        $user = auth()->user();

        // Check if user has any of the required roles
        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        // Return JSON response for API requests
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'status' => 'error',
                'message' => 'Forbidden. Anda tidak memiliki akses. Role Anda: ' . $user->role,
                'required_roles' => $roles,
                'your_role' => $user->role
            ], 403);
        }

        // Redirect based on user role if they don't have access (for web)
        if ($user->isStaff()) {
            return redirect()->route('transaksi.index')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
        }

        return redirect()->route('home')->with('error', 'Anda tidak memiliki akses ke halaman tersebut.');
    }
}
