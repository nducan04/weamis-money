<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

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
        if (config('app.env') === 'production' || request()->server('HTTP_X_FORWARDED_PROTO') === 'https' || request()->header('X-Forwarded-Proto') === 'https') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        // Auto-create framework directories if missing
        $sessionDir = storage_path('framework/sessions');
        if (!file_exists($sessionDir)) {
            @mkdir($sessionDir, 0777, true);
        }
        $viewsDir = storage_path('framework/views');
        if (!file_exists($viewsDir)) {
            @mkdir($viewsDir, 0777, true);
        }
    }
}
