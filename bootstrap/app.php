<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

use App\Http\Middleware\AdminMiddleware;
use App\Http\Middleware\ContentMiddleware;
use App\Http\Middleware\FinanceMiddleware;
use App\Http\Middleware\OperationsMiddleware;
use App\Http\Middleware\SupplierMiddleware;
use App\Http\Middleware\AgentMiddleware;
use App\Http\Middleware\OtherMiddleware;
use App\Http\Middleware\RoleMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {

        $middleware->alias([
            'admin'      => AdminMiddleware::class,
            'content'    => ContentMiddleware::class,
            'finance'    => FinanceMiddleware::class,
            'operations' => OperationsMiddleware::class,
            'supplier'   => SupplierMiddleware::class,
            'agent'      => AgentMiddleware::class,
            'role' => RoleMiddleware::class,
            'other'      => OtherMiddleware::class,
        ]);

    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();