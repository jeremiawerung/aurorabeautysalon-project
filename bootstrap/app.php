<?php

use App\Http\Middleware\SetLocale;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Alias middleware kustom
        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'pelanggan' => \App\Http\Middleware\PelangganMiddleware::class,
            'check.reservasi.status' => \App\Http\Middleware\CheckReservasiStatusMiddleware::class,
            'check.user.reservasi.status' => \App\Http\Middleware\CheckUserReservasiStatusMiddleware::class,
        ]);

        // Terapkan SetLocale ke group web (setelah session)
        $middleware->web(append: [
            SetLocale::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })
    ->create();
