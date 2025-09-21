<?php

namespace App\Providers;

use App\Services\Audit\EmailService;
use App\Services\AuditService;
use Illuminate\Support\ServiceProvider;

class AuditServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(AuditService::class, fn () => new AuditService());
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
