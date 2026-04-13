<?php

namespace App\Models;

use App\OrderItemType;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{

    /**
     * The attributes that aren't mass assignable.
     *
     * @var list<string>
     */
    protected $guarded = [
        'id',
        'created_at',
        'updated_at',
    ];


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'item_type' => OrderItemType::class,
        'quantity' => 'integer',
        'price' => 'decimal:2',
        'setup_fee' => 'decimal:2',
        'config_options' => 'array',
        'variant_selections' => 'array',
    ];

    /**
     * Get the order that owns the order item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the services created from this order item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function services()
    {
        return $this->hasMany(Service::class);
    }

    /**
     * Get the registrants (domains) created from this order item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function registrants()
    {
        return $this->hasMany(Registrant::class);
    }

    /**
     * Get human-readable billing cycle label with internationalization support.
     *
     * @return string|null
     */
    public function getCycleLabelAttribute()
    {
        if ($this->billing_type === 'free' || (float) $this->price == 0) {
            return __('billing.cycles.free');
        }

        if ($this->billing_type === 'onetime') {
            return __('billing.cycles.onetime');
        }

        if ($this->billing_type === 'recurring') {
            $interval = (int) $this->billing_interval;
            $period = $this->billing_period;

            if ($interval === 1) {
                return __('billing.cycles.' . $period);
            }

            $unitLabel = trans_choice('billing.units.' . $period, $interval);

            return __('billing.cycles.every', [
                'count' => $interval,
                'unit'  => $unitLabel
            ]);
        }

        return;
    }
}
