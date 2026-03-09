<?php

namespace App\Observers;

use App\Http\Controllers\Admin\BrowseController;

class BrowseObserver
{
    /**
     * Invalidate the browse cache when a new model record is created.
     *
     * @param  mixed  $model
     * @return void
     */
    public function created($model): void
    {
        BrowseController::clearCache();
    }

    /**
     * Invalidate the browse cache when an existing model record is updated.
     *
     * @param  mixed  $model
     * @return void
     */
    public function updated($model): void
    {
        BrowseController::clearCache();
    }

    /**
     * Invalidate the browse cache when a model record is deleted.
     *
     * @param  mixed  $model
     * @return void
     */
    public function deleted($model): void
    {
        BrowseController::clearCache();
    }
}
