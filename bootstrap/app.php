<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Laravel Cloud (and most PaaS hosts) sit behind a load balancer whose IP isn't fixed —
        // without this, Request::secure()/url()/isSecure() misdetect HTTPS, breaking secure
        // cookies and https:// URL generation in production.
        $middleware->trustProxies(at: '*');

        $middleware->alias([
            'admin.access'      => \App\Http\Middleware\EnsureAdminAccess::class,
            'kingdom.selected'  => \App\Http\Middleware\EnsureKingdomSelected::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*'),
        );
    })->create();
