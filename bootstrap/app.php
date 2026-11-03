<?php

use App\Models\User;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            Route::middleware(['web', 'auth', 'role:client'])
                ->prefix('espace-client')
                ->name('client.')
                ->group(base_path('routes/client.php'));

            Route::middleware(['web', 'auth', 'role:garage|depanneur|assurance'])
                ->prefix('espace-partenaire')
                ->name('partner.')
                ->group(base_path('routes/partner.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \Spatie\Permission\Middleware\RoleMiddleware::class,
            'permission' => \Spatie\Permission\Middleware\PermissionMiddleware::class,
            'role_or_permission' => \Spatie\Permission\Middleware\RoleOrPermissionMiddleware::class,
        ]);

        // Un utilisateur déjà connecté qui ouvre /connexion ou /inscription
        // est redirigé vers son espace, au lieu d'obtenir une erreur 403.
        $middleware->redirectUsersTo(function (Request $request) {
            /** @var User|null $user */
            $user = $request->user();

            return match (true) {
                $user?->hasRole('admin') => '/admin',
                $user?->hasAnyRole(['garage', 'depanneur', 'assurance']) => '/espace-partenaire',
                default => '/espace-client',
            };
        });
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();