<?php

namespace App\Listeners\Service;

use App\Events\Service\ProvisioningFailed;
use App\Facades\Audit;
use App\Models\AuditSystem;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;

class LogProvisioningFailedTask implements ShouldQueue
{
    /**
     * Handle the event.
     */
    public function handle(ProvisioningFailed $event): void
    {
        $service = $event->service;
        $eventName = 'service.provisioning.' . strtolower($event->action);

        $existing = AuditSystem::where('event', $eventName)
            ->where('properties->service_id', $service->id)
            ->where('properties->status', 'failed')
            ->first();

        if ($existing) {
            $properties = $existing->properties;
            $properties['message'] = $event->errorMessage;
            $properties['attempts'] = ($properties['attempts'] ?? 1) + 1;
            
            $existing->update([
                'properties' => $properties,
                'created_at' => now(),
            ]);
        } else {
            $userId = Auth::id();
            
            $properties = [
                'service_id' => $service->id,
                'status' => 'failed',
                'message' => $event->errorMessage,
                'attempts' => 1,
            ];

            if (!$userId) {
                $properties['actor'] = 'system';
            }

            Audit::system($userId, $eventName, $properties);
        }
    }
}
