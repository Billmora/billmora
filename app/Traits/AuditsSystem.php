<?php

namespace App\Traits;

use App\Facades\Audit;
use App\Models\Setting;
use Billmora;
use Illuminate\Support\Facades\Auth;

trait AuditsSystem
{
    /**
     * Update settings in a category and record changes into audit log.
     *
     * @param string $category   The settings category (e.g., 'general', 'mail')
     * @param array  $validated  The validated key-value pairs to update
     */
    protected function updateSettings(string $category, array $validated): void
    {
        $old = Setting::where('category', $category)
            ->whereIn('key', array_keys($validated))
            ->pluck('value', 'key')
            ->toArray();

        $changes = [];
        foreach ($validated as $key => $new) {
            $oldVal = $old[$key] ?? null;
            if ($oldVal != $new) {
                $changes[$key] = [
                    'old' => $oldVal,
                    'new' => $new,
                ];
            }
        }

        if ($changes) {
            Audit::system(Auth::id(), "settings.{$category}.update", [
                'changes' => $changes,
            ]);
        }
    }

    /**
     * Record a system-triggered event with no authenticated user (e.g., webhooks, cron jobs, API callbacks).
     *
     * @param string $event  The event name or action performed.
     * @param array  $data   The data associated with the event.
     * @param string $actor  The actor identifier (e.g., 'gateway', 'cron', 'system').
     *
     * @return void
     */
    protected function recordSystem(string $event, array $data = [], string $actor = 'system'): void
    {
        Audit::system(null, $event, array_merge($data, [
            'actor' => $actor,
        ]));
    }

    /**
     * Record a create event into the system audit log.
     *
     * @param string $event  The event name or action performed.
     * @param array  $data   The data that was newly created.
     *
     * @return void
     */
    protected function recordCreate(string $event, array $data): void
    {
        Audit::system(Auth::id(), $event, [
            'new' => $data,
        ]);
    }

    /**
     * Record an update event into the system audit log, including changed fields.
     *
     * @param string $event  The event name or action performed.
     * @param array  $old    The original data before the update.
     * @param array  $new    The new data after the update.
     *
     * @return void
     */
    protected function recordUpdate(string $event, array $old, array $new): void
    {
        $changes = [];
        foreach ($new as $key => $value) {
            $oldVal = $old[$key] ?? null;
            if ($oldVal != $value) {
                $changes[$key] = [
                    'old' => $oldVal,
                    'new' => $value,
                ];
            }
        }

        if ($changes) {
            Audit::system(Auth::id(), $event, [
                'id' => $old['id'] ?? null,
                'changes' => $changes,
            ]);
        }
    }

    /**
     * Record a delete event into the system audit log.
     *
     * @param string $event  The event name or action performed.
     * @param array  $data   The data that was deleted.
     *
     * @return void
     */
    protected function recordDelete(string $event, array $data): void
    {
        Audit::system(Auth::id(), $event, [
            'old' => $data,
        ]);
    }
}
