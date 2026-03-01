<?php

namespace App\Traits;

use App\Facades\Audit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

trait AuditsUser
{
    /**
     * Record a general user activity event with optional request metadata.
     *
     * @param  string  $event
     * @param  array  $data
     * @param  \Illuminate\Http\Request|null  $request
     * @return void
     */
    protected function recordActivity(string $event, array $data = [], ?Request $request = null): void
    {
        Audit::user(Auth::id(), $event, array_merge($data, [
            'ip' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
        ]));
    }

    /**
     * Record an audit log of changed fields between old and new data states.
     *
     * @param  string  $event
     * @param  array  $old
     * @param  array  $new
     * @param  \Illuminate\Http\Request|null  $request
     * @return void
     */
    protected function recordUpdate(string $event, array $old, array $new, ?Request $request = null): void
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
            Audit::user(Auth::id(), $event, [
                'changes' => $changes,
                'ip' => $request?->ip(),
                'user_agent' => $request?->userAgent(),
            ]);
        }
    }
}
