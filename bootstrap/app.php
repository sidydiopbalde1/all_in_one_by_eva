<?php

use App\Exceptions\RegleMetierException;
use App\Http\Middleware\EnsureUserIsActive;
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
        $middleware->web(append: [EnsureUserIsActive::class]);
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('dashboard'));
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // Règle de gestion violée : retour au formulaire avec un message clair.
        $exceptions->render(function (RegleMetierException $e, Request $request) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $e->getMessage(), 'errors' => [$e->champ => [$e->getMessage()]]], 422);
            }

            return back()->withInput()->withErrors([$e->champ => $e->getMessage()]);
        });
    })->create();
