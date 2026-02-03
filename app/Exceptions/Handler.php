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

                $actor = $request->user();

                if ($actor) {
                    AuditLogger::log(
                        'access_denied',
                        ['authz', 'outcome:blocked'],
                        $actor,
                        [],
                        [
                            'method' => $request->method(),
                            'route'  => optional($request->route())->getName() ?? 'unknown',
                            'path'   => $request->path(),
                            'reason' => class_basename($e),
                        ]
                    );
                }
            }

            return null;
        });
    }
}
