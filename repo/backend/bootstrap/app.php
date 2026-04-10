<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'enforce.role' => \App\Http\Middleware\EnforceRole::class,
            'idempotency' => \App\Http\Middleware\IdempotencyGuard::class,
            'audit.request' => \App\Http\Middleware\AuditRequest::class,
            'set.locale' => \App\Http\Middleware\SetLocale::class,
            'security.headers' => \App\Http\Middleware\SecurityHeaders::class,
            'enforce.timeout' => \App\Http\Middleware\EnforceInactivityTimeout::class,
            'rate.login' => \App\Http\Middleware\RateLimitLogin::class,
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\RotateSession::class,
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\SetLocale::class,
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\AuditRequest::class,
            \App\Http\Middleware\EnforceInactivityTimeout::class,
        ]);

        $middleware->statefulApi();
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['code' => 401, 'msg' => 'Unauthenticated.'], 401);
            }
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                $firstError = collect($e->errors())->flatten()->first() ?? 'Validation failed.';
                return response()->json([
                    'code' => 422,
                    'msg' => $firstError,
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['code' => 404, 'msg' => 'Resource not found.'], 404);
            }
        });

        $exceptions->render(function (AccessDeniedHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['code' => 403, 'msg' => 'Forbidden.'], 403);
            }
        });

        $exceptions->render(function (MethodNotAllowedHttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['code' => 405, 'msg' => 'Method not allowed.'], 405);
            }
        });

        $exceptions->render(function (HttpException $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'code' => $e->getStatusCode(),
                    'msg' => $e->getMessage() ?: 'An error occurred.',
                ], $e->getStatusCode());
            }
        });

        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json(['code' => 500, 'msg' => 'Internal server error.'], 500);
            }
        });
    })->create();
