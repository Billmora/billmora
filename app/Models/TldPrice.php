<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TldPrice extends Model
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
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'register_price' => 'decimal:2',
            'transfer_price' => 'decimal:2',
            'renew_price' => 'decimal:2',
        ];
    }
    
    /**
     * Get the TLD that owns the price.
     */
    public function tld()
    {
        return $this->belongsTo(Tld::class);
    }
}
