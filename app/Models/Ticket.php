<?php

namespace App\Models;

use Billmora;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Ticket extends Model
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
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'last_reply_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    /**
     * Boot method to auto-generate ticket_number.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($ticket) {
            if (empty($ticket->ticket_number)) {
                $ticket->ticket_number = static::generateTicketNumber();
            }
        });
    }

    /**
     * Get the user who owns the ticket.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the staff user assigned to the ticket.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function assignedTo()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Get the service associated with the ticket.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function service()
    {
        return $this->belongsTo(Service::class);
    }

    /**
     * Get all messages belonging to the ticket.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function messages()
    {
        return $this->hasMany(TicketMessage::class);
    }

    /**
     * Get the most recent message of the ticket.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function latestMessage()
    {
        return $this->hasOne(TicketMessage::class)->latestOfMany();
    }


    /**
     * Generate ticket number based on settings.
     *
     * @return string
     */
    public static function generateTicketNumber(): string
    {
        $format = Billmora::getGeneral('ticketing_number_format');
        $padding = (int) Billmora::getGeneral('ticketing_number_padding');
        $increment = (int) Billmora::getGeneral('ticketing_number_increment');

        return DB::transaction(function () use ($format, $padding, $increment) {

            $lastTicket = static::whereNotNull('ticket_number')
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            if ($lastTicket && preg_match_all('/(\d+)/', $lastTicket->ticket_number, $matches)) {
                $lastNumber = (int) end($matches[1]);
                $nextNumber = $lastNumber + $increment;
            } else {
                $nextNumber = $increment;
            }

            $paddedNumber = str_pad($nextNumber, $padding, '0', STR_PAD_LEFT);

            $ticketNumber = str_replace(
                ['{number}', '{day}', '{month}', '{year}'],
                [
                    $paddedNumber,
                    date('d'),
                    date('m'),
                    date('Y'),
                ],
                $format
            );

            $counter = 0;
            $originalNumber = $ticketNumber;
            while (static::where('ticket_number', $ticketNumber)->exists()) {
                $counter++;
                $ticketNumber = $originalNumber . '-' . $counter;
            }

            return $ticketNumber;
        }); 
    }
}
