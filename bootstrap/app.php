<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
            'admin.auth' => \App\Http\Middleware\AdminAuthMiddleware::class,
            'maintenance.check' => \App\Http\Middleware\CheckMaintenanceMode::class,
        ]);

        // Add maintenance mode check to web middleware group
        $middleware->web(append: [
            \App\Http\Middleware\CheckMaintenanceMode::class,
        ]);

        // Redirect unauthenticated users to customer login
        $middleware->redirectGuestsTo(fn($request) => route('customer.login'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
