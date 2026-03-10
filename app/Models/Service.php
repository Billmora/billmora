<?php

namespace App\Models;

use App\Observers\ServiceObserver;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

#[ObservedBy(ServiceObserver::class)]
class Service extends Model
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
        'configuration' => 'array',
        'activated_at' => 'datetime',
        'next_due_date' => 'datetime',
        'suspended_at' => 'datetime',
        'terminated_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'price' => 'decimal:2',
        'setup_fee' => 'decimal:2',
    ];

    /**
     * Boot the model and register event listeners.
     *
     * @return void
     */
    protected static function booted()
    {
        static::deleted(function ($service) {
            if ($service->order) {
                $service->order->update([
                    'status' => 'cancelled',
                    'cancelled_at' => now(),
                ]);
            }
        });
    }

    /**
     * Get the user that owns this service.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the order that created this service.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the package for this service.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function package()
    {
        return $this->belongsTo(Package::class);
    }

    /**
     * Get the package price for this service.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function packagePrice()
    {
        return $this->belongsTo(PackagePrice::class);
    }

    /**
     * Get the invoices associated with the service through invoice items.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function invoices()
    {
        return $this->belongsToMany(
            Invoice::class, 
            'invoice_items',
            'service_id',
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
     * Get all cancellation requests associated with the service.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cancellations()
    {
        return $this->hasMany(ServiceCancellation::class);
    }

    /**
     * Get the latest pending cancellation request for the service.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function activeCancellation()
    {
        return $this->hasOne(ServiceCancellation::class)
            ->where('status', 'pending')
            ->latestOfMany();
    }

    /**
     * Get the provisioning for this service.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function provisioning()
    {
        return $this->belongsTo(Plugin::class, 'plugin_id');
    }

    /**
     * Scope a query to only include active services.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive(Builder $query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include pending services.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending(Builder $query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Activate the service and set next due date based on billing cycle.
     *
     * @return bool
     */
    public function activate()
    {
        return $this->update([
            'status' => 'active',
            'activated_at' => now(),
            'next_due_date' => $this->calculateNextDueDate(),
        ]);
    }

    /**
     * Suspend the service and record suspension timestamp.
     *
     * @return bool
     */
    public function suspend()
    {
        return $this->update([
            'status' => 'suspended',
            'suspended_at' => now(),
        ]);
    }

    /**
     * Unsuspend the service and clear suspension timestamp.
     *
     * @return bool
     */
    public function unsuspend()
    {
        return $this->update([
            'status' => 'active',
            'suspended_at' => null,
        ]);
    }

    /**
     * Terminate the service and clear all billing-related timestamps.
     *
     * @return bool
     */
    public function terminate()
    {
        return $this->update([
            'status' => 'terminated',
            'terminated_at' => now(),
            'suspended_at' => null,
            'next_due_date' => null,
        ]);
    }

    /**
     * Calculate the next due date based on billing period and interval.
     *
     * @return \Illuminate\Support\Carbon|null
     */
    public function calculateNextDueDate()
    {
        if ($this->billing_type !== 'recurring') {
            return null;
        }

        $date = $this->next_due_date ? $this->next_due_date->copy() : ($this->activated_at ? $this->activated_at->copy() : now());
        
        switch ($this->billing_period) {
            case 'daily':
                return $date->addDays($this->billing_interval);
            case 'weekly':
                return $date->addWeeks($this->billing_interval);
            case 'monthly':
                return $date->addMonths($this->billing_interval);
            case 'yearly':
                return $date->addYears($this->billing_interval);
            default:
                return null;
        }
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
