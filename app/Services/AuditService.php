<?php

namespace App\Services;

use App\Models\AuditEmail;
use App\Models\AuditUser;

class AuditService
{

    /**
     * Log an email audit entry.
     *
     * @param int|null $userId     The ID of the user associated with the email, or null if not applicable.
     * @param string   $to         The recipient email address.
     * @param string   $event    The email event line.
     * @param string   $status     The email status (e.g., "pending", "sent", "failed"). Defaults to "pending".
     * @param array    $properties Additional metadata or properties related to the email.
     *
     * @return \App\Models\AuditEmail
     */
    public function email(
            ?int $userId,
            string $to,
            string $event,
            string $status = 'pending',
            array $properties = []
        ) 
    {
        return AuditEmail::create([
            'user_id' => $userId,
            'to' => $to,
            'event' => $event,
            'status' => $status,
            'properties' => $properties,
        ]);
    }

    /**
     * Log a user-related audit event.
     *
     * @param int   $userId     The ID of the user associated with the event.
     * @param string $event     The name of the event (e.g., "login", "update_profile").
     * @param array  $properties Optional additional data related to the event.
     *
     * @return \App\Models\AuditUser
     */
    public function user(
            int $userId,
            string $event,
            array $properties = []
        ) 
    {
        return AuditUser::create([
            'user_id' => $userId,
            'event' => $event,
            'properties' => $properties,
        ]);
    }
}
