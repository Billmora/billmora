<?php

namespace App\Models;

use Billmora;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Order extends Model
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
        'variant_selections' => 'array',
        'terms_accepted' => 'boolean',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'subtotal' => 'decimal:2',
        'discount' => 'decimal:2',
        'setup_fee' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    /**
     * Boot method to auto-generate order_number.
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->order_number)) {
                $order->order_number = static::generateOrderNumber();
            }
        });
    }

    /**
     * Generate order number based on settings.
     *
     * @return string
     */
    public static function generateOrderNumber(): string
    {
        $format = Billmora::getGeneral('ordering_number_format');
        $padding = (int) Billmora::getGeneral('ordering_number_padding');
        $increment = (int) Billmora::getGeneral('ordering_number_increment');

        $lastOrder = static::whereNotNull('order_number')
            ->orderBy('id', 'desc')
            ->first();

        if ($lastOrder && preg_match('/(\d+)/', $lastOrder->order_number, $matches)) {
            $lastNumber = (int) end($matches);
            $nextNumber = $lastNumber + $increment;
        } else {
            $nextNumber = $increment;
        }

        $paddedNumber = str_pad($nextNumber, $padding, '0', STR_PAD_LEFT);

        $orderNumber = str_replace(
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
        $originalNumber = $orderNumber;
        while (static::where('order_number', $orderNumber)->exists()) {
            $counter++;
            $orderNumber = $originalNumber . '-' . $counter;
        }

        return $orderNumber;
    }

    /**
     * Get the user that owns this order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the package associated with this order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Get the package price for this order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function packagePrice()
    {
        return $this->belongsTo(PackagePrice::class);
    }

    /**
     * Get the coupon applied to this order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    /**
     * Get the service created from this order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function service()
    {
        return $this->hasOne(Service::class);
    }

    /**
     * Get the invoices for this order.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Scope a query to only include pending orders.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending(Builder $query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope a query to only include completed orders.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeCompleted(Builder $query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Mark the order as completed with timestamp.
     *
     * @return bool
     */
    public function markAsCompleted()
    {
        $this->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);
    }
}
