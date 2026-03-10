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
}
