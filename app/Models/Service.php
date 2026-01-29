<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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
     * Get the invoices for this service.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Scope a query to only include active services.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include pending services.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Activate the service and set next due date.
     *
     * @return bool
     */
    public function activate()
    {
        $this->update([
            'status' => 'active',
            'activated_at' => now(),
            'next_due_date' => $this->calculateNextDueDate(),
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

        $date = now();
        
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
}
