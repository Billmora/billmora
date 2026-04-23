<?php

namespace App\Models;

use App\Contracts\BrowseInterface;
use App\Observers\RegistrantObserver;
use App\Traits\BrowseTrait;
use Billmora;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

#[ObservedBy(RegistrantObserver::class)]
class Registrant extends Model implements BrowseInterface
{
    use BrowseTrait;

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
     * Boot method to auto-generate registrant_number.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($registrant) {
            if (empty($registrant->registrant_number)) {
                $registrant->registrant_number = static::generateRegistrantNumber();
            }
        });
    }

    /**
     * Generate registrant number based on settings.
     *
     * @return string
     */
    public static function generateRegistrantNumber(): string
    {
        $format = Billmora::getGeneral('domain_number_format', 'DOM-{number}');
        $padding = (int) Billmora::getGeneral('domain_number_padding', 4);
        $increment = (int) Billmora::getGeneral('domain_number_increment', 1);

        return DB::transaction(function () use ($format, $padding, $increment) {
            $lastRegistrant = static::whereNotNull('registrant_number')
                ->orderBy('id', 'desc')
                ->lockForUpdate()
                ->first();

            if ($lastRegistrant && preg_match_all('/(\d+)/', $lastRegistrant->registrant_number, $matches)) {
                $lastNumber = (int) end($matches[1]);
                $nextNumber = $lastNumber + $increment;
            } else {
                $nextNumber = $increment;
            }

            $paddedNumber = str_pad($nextNumber, $padding, '0', STR_PAD_LEFT);

            $registrantNumber = str_replace(
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
            $originalNumber = $registrantNumber;
            while (static::where('registrant_number', $registrantNumber)->exists()) {
                $counter++;
                $registrantNumber = $originalNumber . '-' . $counter;
            }

            return $registrantNumber;
        }); 
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'auto_renew' => 'boolean',
            'nameservers' => 'array',
            'configuration' => 'array',
            'registered_at' => 'datetime',
            'expires_at' => 'datetime',
            'suspended_at' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    /**
     * Activate the domain registration.
     */
    public function activate(?int $years = null): void
    {
        $this->update([
            'status' => 'active',
            'registered_at' => $this->registered_at ?? now(),
            'expires_at' => now()->addYears($years ?? $this->years),
        ]);
    }

    /**
     * Mark the domain as suspended.
     */
    public function suspend(): void
    {
        $this->update([
            'status' => 'suspended',
            'suspended_at' => now(),
        ]);
    }

    /**
     * Unsuspend (reactivate) the domain.
     */
    public function unsuspend(): void
    {
        $this->update([
            'status' => 'active',
            'suspended_at' => null,
        ]);
    }

    /**
     * Mark the domain as expired.
     */
    public function expire(): void
    {
        $this->update(['status' => 'expired']);
    }

    /**
     * Cancel the domain registration.
     */
    public function cancel(): void
    {
        $this->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);
    }

    /**
     * Get the user that owns the domain.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order associated with the domain.
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the specific order item that generated this domain registration.
     */
    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    /**
     * Get the TLD of the domain.
     */
    public function tld()
    {
        return $this->belongsTo(Tld::class);
    }

    /**
     * Get the registrar plugin that manages this domain.
     */
    public function plugin()
    {
        return $this->belongsTo(Plugin::class);
    }

    /**
     * Get the invoices associated with the registrant (domain) through invoice items.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function invoices()
    {
        return $this->belongsToMany(
            Invoice::class, 
            'invoice_items',
            'registrant_id',
            'invoice_id'
        )
        ->withPivot([
            'description', 
            'quantity', 
            'unit_price', 
            'amount'
        ])
        ->withTimestamps();
    }

    /**
     * Return a collection of registrant records formatted as browse items for quick search indexing.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function toBrowseItems(): Collection
    {
        return static::select('id', 'registrant_number')
            ->limit(50)
            ->get()
            ->map(fn($item) => [
                'title' => "{$item->registrant_number}",
                'category' => 'registrant',
                'url' => route('admin.registrants.edit', ['registrant' => $item->id]),
            ]);
    }
}
