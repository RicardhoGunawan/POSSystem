<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\CheckRole; // Tambahkan middleware role
use Illuminate\Auth\Middleware\Authenticate;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Tambahkan middleware yang dibutuhkan
        $middleware->alias([
            'auth' => Authenticate::class, // Middleware auth bawaan Laravel
            'role' => CheckRole::class, // Middleware role yang kita buat
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
