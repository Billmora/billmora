<?php

namespace App\Observers;

use App\Events\Registrant\Created;
use App\Models\Registrant;
use App\Traits\AuditsSystem;

class RegistrantObserver
{
    use AuditsSystem;

    /**
     * Handle the Registrant "created" event.
     *
     * @param  \App\Models\Registrant  $registrant
     * @return void
     */
    public function created(Registrant $registrant)
    {
        event(new Created($registrant));
    }

    /**
     * Handle the Registrant "updated" event.
     *
     * @param  \App\Models\Registrant  $registrant
     * @return void
     */
    public function updated(Registrant $registrant)
    {
        // Audit logging is handled explicitly in controllers/services
    }

    /**
     * Handle the Order "deleted" event.
     */
    public function deleted(Registrant $registrant): void
    {
        //
    }

    /**
     * Handle the Order "restored" event.
     */
    public function restored(Registrant $registrant): void
    {
        //
    }

    /**
     * Handle the Order "force deleted" event.
     */
    public function forceDeleted(Registrant $registrant): void
    {
        //
    }
}
