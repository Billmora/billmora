<?php

namespace App\Providers;

use App\Facades\Billmora;
use App\Services\BillmoraService;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

/**
 * Service provider that binds the BillmoraService class into the container
 * and registers its facade alias for global access.
 */
class BillmoraServiceProvider extends ServiceProvider
{
    
    /**
     * Register bindings and aliases into the service container.
     *
     * @return void
     */
    public function register(): void
    {
        $this->app->singleton(BillmoraService::class, fn () => new BillmoraService());

        AliasLoader::getInstance()->alias('Billmora', Billmora::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
