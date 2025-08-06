<?php

namespace App\Facades;

use App\Services\BillmoraService;
use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed getSetting(string $category, string $key, mixed $default = null)
 * @method static void setSetting(string $category, array $data)
 *
 * Facade for accessing BillmoraService methods more conveniently.
 */
class Billmora extends Facade
{

     /**
     * Get the service container binding key.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return BillmoraService::class;
    }
}
