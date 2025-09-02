<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailBroadcast extends Model
{
    /**
     * The attributes that aren't mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @var list<string>
     */
    protected $casts = [
        'recipient_custom' => 'array',
        'cc' => 'array',
        'bcc' => 'array',
        'schedule_at' => 'datetime',
    ];
}
