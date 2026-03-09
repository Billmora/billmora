<?php

namespace App\Traits;

use App\Observers\BrowseObserver;

trait BrowseTrait
{
    /**
     * Register the BrowseObserver to automatically invalidate browse cache on model events.
     *
     * @return void
     */
    public static function bootBrowseableTrait(): void
    {
        static::observe(BrowseObserver::class);
    }
}
