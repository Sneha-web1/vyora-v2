<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\EnsureSuperAdminAccess;
use App\Http\Middleware\EnsureMenuAccess;
use App\Http\Middleware\StripRolePrefix;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Guests are sent to the right login page
        // /{role}/... -> /...  (role panels get their own URL prefix)
        $middleware->prepend(StripRolePrefix::class);

        $middleware->redirectGuestsTo(function ($request) {
            if ($slug = $request->attributes->get('role_prefix')) {
                return '/' . $slug . '/login';
            }

            return $request->is('employee/*') ? '/employee/login' : '/';
        });

        $middleware->alias([
            'superadmin.access' => EnsureSuperAdminAccess::class,
            'menu.access' => EnsureMenuAccess::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
