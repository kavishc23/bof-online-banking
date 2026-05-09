<?php

use App\Exceptions\BankingOperationException;
use App\Http\Middleware\EnsureBankingSession;
use App\Http\Middleware\EnsureRole;
use App\Http\Middleware\LogFailedBankingRequest;
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
        $middleware->web(append: [
            LogFailedBankingRequest::class,
        ]);

        $middleware->alias([
            'banking.session' => EnsureBankingSession::class,
            'role' => EnsureRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (BankingOperationException $exception, Request $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => $exception->getMessage(),
                    'context' => $exception->context,
                ], 422);
            }

            return back()->withInput()->with('error', $exception->getMessage());
        });
    })->create();
