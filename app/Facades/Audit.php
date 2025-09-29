<?php

namespace App\Facades;

use App\Services\AuditService;
use Illuminate\Support\Facades\Facade;

/**
 * Facade for the Audit service.
 */
class Audit extends Facade
{

    /**
     * Get the registered name of the component.
     *
     * @see \App\Services\AuditService
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return AuditService::class;
    }
}
