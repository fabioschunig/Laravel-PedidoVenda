<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('admin', fn($user) => $user->role === 'admin');
        Gate::define('vendedor', fn($user) => in_array($user->role, ['admin', 'vendedor']));
        Gate::define('visualizador', fn($user) => in_array($user->role, ['admin', 'vendedor', 'visualizador']));
    }
}
