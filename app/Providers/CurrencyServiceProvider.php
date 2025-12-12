<?php

namespace App\Providers;

use App\Facades\Currency;
use App\Services\CurrencyService;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\ServiceProvider;

class CurrencyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(CurrencyService::class, fn () => new CurrencyService());

        AliasLoader::getInstance()->alias('Currency', Currency::class);
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
