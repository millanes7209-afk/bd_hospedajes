<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        api: __DIR__ . '/../routes/api.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            \App\Http\Middleware\NormalizeDecimalInputs::class,
            \App\Http\Middleware\EnsureUpdatedSchema::class,
        ]);
        $middleware->api(prepend: [
            \App\Http\Middleware\EnsureUpdatedSchema::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            return redirect()->route('login')->withErrors([
                'email' => 'Tu sesión expiró o la página estuvo abierta demasiado tiempo. Por favor, inicia sesión nuevamente.',
            ]);
        });
    })->create();
