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
        $this->renderable(function (Throwable $e, $request) {

            if ($e instanceof AuthorizationException || $e instanceof AccessDeniedHttpException) {

                $routeName = optional($request->route())->getName() ?? 'unknown';

                AuditLogger::log(
                    'access_denied',
                    'blocked',
                    class_basename($e),
                    'route',
                    $routeName,
                    [
                        'method' => $request->method(),
                        'path'   => $request->path(),
                        'route'  => $routeName,
                    ]
                );
            }
            return null;
        });
    }
}
