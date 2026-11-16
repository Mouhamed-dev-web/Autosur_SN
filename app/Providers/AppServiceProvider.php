<?php

namespace App\Providers;

use App\Contracts\SmsGatewayInterface;
use App\Services\Sms\LogSmsGateway;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use App\Services\Sms\HttpSmsGateway;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SmsGatewayInterface::class, function () {
            return match (config('sms.driver')) {
                'http' => app(HttpSmsGateway::class),
                default => app(LogSmsGateway::class),
            };
        });
    }

    public function boot(): void
    {
        // L'administrateur est autorisé partout ; les autres suivent les policies
        Gate::before(fn ($user) => $user->hasRole('admin') ? true : null);
    }
}