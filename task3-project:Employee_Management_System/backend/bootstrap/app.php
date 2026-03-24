<?php

use App\Http\Middleware\ForceJsonResponse;
use App\Support\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        apiPrefix: 'api',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->appendToGroup('api', ForceJsonResponse::class);
        $middleware->redirectGuestsTo(function (Request $request): ?string {
            return $request->is('api/*') ? null : '/';
        });
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $exception, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error(
                    'Unauthenticated.',
                    ['auth' => ['Authentication token is missing or invalid.']],
                    'AUTH_UNAUTHENTICATED',
                    401
                );
            }

            return null;
        });

        $exceptions->render(function (ValidationException $exception, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error(
                    'The given data was invalid.',
                    $exception->errors(),
                    'VALIDATION_ERROR',
                    422
                );
            }

            return null;
        });

        $exceptions->render(function (NotFoundHttpException $exception, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::error(
                    'The requested API endpoint was not found.',
                    [],
                    'ROUTE_NOT_FOUND',
                    404
                );
            }

            return null;
        });
    })->create();
