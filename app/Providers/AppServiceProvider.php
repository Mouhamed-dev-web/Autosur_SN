<?php

namespace App\Providers;

use App\Contracts\SmsGatewayInterface;
use App\Services\Sms\LogSmsGateway;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(SmsGatewayInterface::class, LogSmsGateway::class);
    }

    public function boot(): void
    {
        // L'administrateur est autorisé partout ; les autres suivent les policies
        Gate::before(fn ($user) => $user->hasRole('admin') ? true : null);
    }
}