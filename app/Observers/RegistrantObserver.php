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
        $changes = $registrant->getChanges();
        
        $loggableChanges = collect($changes)
            ->except(['updated_at'])
            ->toArray();

        if (!empty($loggableChanges)) {
            $this->recordUpdate('registrant.updated', $loggableChanges, $registrant->toArray());
        }
    }

    /**
     * Handle the Registrant "deleted" event.
     *
     * @param  \App\Models\Registrant  $registrant
     * @return void
     */
    public function deleted(Registrant $registrant)
    {
        $this->recordDelete('registrant.deleted', $registrant->toArray());
    }
}
