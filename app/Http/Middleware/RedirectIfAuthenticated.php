<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfAuthenticated
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ...$guards): Response
    {
        $user = $request->user();

        if ($user) {
            // Role-based redirects
            if ($user->hasRole('user')) {
                return redirect()->route('items');
            }

            if ($user->hasRole('office')) {
                return redirect()->route('inventory');
            }

            // Default (admin or other roles)
            return redirect()->route('dashboard');
        }

        return $next($request);
    }
}