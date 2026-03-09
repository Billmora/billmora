<?php

namespace App\Providers;

use App\Http\Controllers\Admin\BrowseController;
use Illuminate\Support\Facades\View;
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
        View::composer('admin::components.browse', function ($view) {
            $view->with('browseItems', app(BrowseController::class)->getItems());
        });
    }
}
