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
        // Trust proxies for HTTPS detection
        if (isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') {
            \URL::forceScheme('https');
        }
        
        // Force HTTPS in production and local (if using HTTPS)
        if (config('app.env') === 'production' || request()->isSecure()) {
            \URL::forceScheme('https');
        }
        
        // Set proper asset URL
        if ($appUrl = config('app.url')) {
            \URL::forceRootUrl($appUrl);
        }
    }
}
