<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )

    //middleware
    ->withMiddleware(function (Middleware $middleware): void {

        // Spatie role/permission middleware aliases
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);

    })

    //exception handling
    ->withExceptions(function (Exceptions $exceptions): void {

        //standardized workflow error responses
        $exceptions->render(function (
            \App\Exceptions\WorkflowException $e,
                                              $request
        ) {
            //only return JSON if API request
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'WorkflowError',
                    'message' => $e->getMessage(),
                    'status' => $e->getStatus(),
                ], $e->getStatus());
            }
        });

        //cohort suppression responses
        $exceptions->render(function (
            \App\Exceptions\CohortSuppressedException $e,
                                                      $request
        ) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'CohortSuppressed',
                    'message' => $e->getMessage(),
                ], 422);
            }
        });

    })

    ->create();
