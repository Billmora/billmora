<?php

namespace App\Facades;

use App\Services\CurrencyService;
use Illuminate\Support\Facades\Facade;

/**
 * Facade for the Currency service.
 */
class Currency extends Facade
{

     /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return CurrencyService::class;
    }
}
