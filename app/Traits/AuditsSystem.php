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
}
