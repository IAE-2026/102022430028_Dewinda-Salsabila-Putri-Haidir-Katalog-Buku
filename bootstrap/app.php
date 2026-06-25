<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\ValidationException;

return Application::configure(basePath: dirname(__DIR__))

    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Terapkan ForceJsonResponse ke seluruh API route group
        $middleware->api(prepend: [
            \App\Http\Middleware\ForceJsonResponse::class,
        ]);

        $middleware->alias([
            'iae.key' => \App\Http\Middleware\VerifyIAEKey::class,
            'jwt.auth' => \App\Http\Middleware\VerifyJWT::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {

        // Route tidak ditemukan (404)
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Resource tidak ditemukan.',
                    'errors'  => null,
                ], 404)->header('Content-Type', 'application/json');
            }
        });

        // Validation error (422)
        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Validasi gagal.',
                    'errors'  => $e->errors(),
                ], 422)->header('Content-Type', 'application/json');
            }
        });

        // Unauthenticated (401)
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => 'Unauthorized.',
                    'errors'  => null,
                ], 401)->header('Content-Type', 'application/json');
            }
        });

        // Semua error lain yang tidak ke-handle khusus (500)
        $exceptions->render(function (\Throwable $e, Request $request) {
            if ($request->is('api/*')) {
                return response()->json([
                    'status'  => 'error',
                    'message' => $e->getMessage() ?: 'Terjadi kesalahan pada server.',
                    'errors'  => null,
                ], 500)->header('Content-Type', 'application/json');
            }
        });

    })->create();