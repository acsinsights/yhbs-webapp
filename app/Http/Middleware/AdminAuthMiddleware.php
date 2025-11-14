<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdminAuthMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            // Store the intended URL in the session
            session()->put('url.intended', url()->current());
            // Redirect to custom login page if not authenticated
            return redirect()->route('admin.login');
        }

        // Check if user has admin or reception role
        $user = Auth::user();
        if (!$user->isAdminOrReception()) {
            Auth::logout();
            return redirect()->route('admin.login')
                ->with('error', 'Access denied. Only Admin and Reception can access this area.');
        }

        return $next($request);
    }
}
