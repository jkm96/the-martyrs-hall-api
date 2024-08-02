<?php

use App\Http\Middleware\ConvertRequestFieldsToSnakeCase;
use App\Http\Middleware\ConvertResponseFieldsToCamelCase;
use App\Utils\Helpers\ResponseHelpers;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/status',
        then: function () {
            Route::middleware('api')
                ->prefix('api')
                ->name('admin.')
                ->group(base_path('routes/api/v1/admin.php'));

            Route::middleware('api')
                ->prefix('api')
                ->name('submission.')
                ->group(base_path('routes/api/v1/submission.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->redirectGuestsTo('/login');
        $middleware->appendToGroup('api', [
            ConvertResponseFieldsToCamelCase::class,
            ConvertRequestFieldsToSnakeCase::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (ModelNotFoundException $e) {
            $message = 'Entry for ' . str_replace('App\\', '', $e->getModel()) . ' not found';
            return ResponseHelpers::ConvertToJsonResponseWrapper(['errors' => $e->getMessage()], $message, 404);
        });

        $exceptions->render(function (AuthenticationException $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper(
                ['errors' => $e->getMessage()], "You are not authenticated", 401);
        });

        $exceptions->render(function (NotFoundHttpException $e) {
            return ResponseHelpers::ConvertToJsonResponseWrapper( ['errors' => $e->getMessage()], "Page Not Found.", 404);
        });

    })->create();
