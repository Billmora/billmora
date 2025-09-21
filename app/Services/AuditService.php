<?php

namespace App\Services;

use App\Models\AuditEmail;

class AuditService
{

    /**
     * Log an email audit entry.
     *
     * @param int|null $userId     The ID of the user associated with the email, or null if not applicable.
     * @param string   $to         The recipient email address.
     * @param string   $subject    The email subject line.
     * @param string   $status     The email status (e.g., "pending", "sent", "failed"). Defaults to "pending".
     * @param array    $properties Additional metadata or properties related to the email.
     *
     * @return \App\Models\AuditEmail
     */
    public function email(
            ?int $userId,
            string $to,
            string $subject,
            string $status = 'pending',
            array $properties = []
        ) 
    {
        return AuditEmail::create([
            'user_id' => $userId,
            'to' => $to,
            'subject' => $subject,
            'status' => $status,
            'properties' => $properties,
        ]);
    }
}
