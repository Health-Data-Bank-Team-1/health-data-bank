<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Auth\Access\AuthorizationException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use App\Services\AuditLogger;

class Handler extends ExceptionHandler
{
    public function register(): void
    {

            // Validation errors
        $this->invalidate(function (ValidationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'ValidationError',
                    'message' => 'The given data was invalid.',
                    'details' => $e->errors(),
                ], 422);
            }
        });

        // Model not found
        $this->renderable(function (ModelNotFoundException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'NotFound',
                    'message' => 'Resource not found.',
                ], 404);
            }
        });

        // Authorization
        $this->renderable(function (AuthorizationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Forbidden',
                    'message' => 'You are not authorized to access this resource.',
                ], 403);
            }
        });

        // Unauthenticated
        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'Authentication required.',
                ], 401);
            }
        });

        // Generic fallback
        $this->renderable(function (Throwable $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error' => 'ServerError',
                    'message' => config('app.debug') ? $e->getMessage() : 'An error occurred.',
                ], 500);
            }
        });

        $this->renderable(function (Throwable $e, $request) {
            if ($e instanceof AuthorizationException || $e instanceof AccessDeniedHttpException) {
                AuditLogger::log(
                    'access_denied',
                    ['auth', 'outcome:blocked'],
                    null,
                    [],
                    [
                        'method' => $request->method(),
                        'route'  => optional($request->route())->getName() ?? 'unknown',
                        'path'   => $request->path(),
                        'reason' => class_basename($e),
                    ]
                );
            }

            return null;
        });
    }
}
