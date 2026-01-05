<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckMaintenanceMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if maintenance mode is enabled
        $maintenanceMode = website_setting('maintenance_mode', false);

        // Allow admin users to bypass maintenance mode
        if ($maintenanceMode && !$this->isAdminRoute($request) && !$this->isAdminUser()) {
            $message = website_setting('maintenance_message', 'We are currently performing scheduled maintenance. We\'ll be back soon!');

            return response()->view('maintenance', [
                'message' => $message
            ], 503);
        }

        return $next($request);
    }

    /**
     * Check if the current route is an admin route
     */
    private function isAdminRoute(Request $request): bool
    {
        return $request->is('admin/*') || $request->is('admin');
    }

    /**
     * Check if the current user is an admin
     */
    private function isAdminUser(): bool
    {
        if (!Auth::guard('admin')->check()) {
            return false;
        }

        return true;
    }
}
