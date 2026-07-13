<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckRole;
use App\Http\Middleware\LogRootActivity;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Trust all proxies (ngrok, cloudflare, etc.)
        // Cho phép Laravel đọc đúng host/scheme từ X-Forwarded-* headers
        $middleware->trustProxies(at: '*');

        // Exempt PayOS webhook from CSRF protection
        // PayOS POST webhook không có CSRF token → phải exempt
        $middleware->validateCsrfTokens(except: [
            'api/payos-webhook',
           
        ]);
         $middleware->alias([
                'role'     => CheckRole::class,
                'log-root' => LogRootActivity::class,
         ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
