<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tld extends Model
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
            'whois_privacy' => 'boolean',
        ];
    }

    /**
     * Get the default registrar plugin assigned to this TLD.
     */
    public function plugin()
    {
        return $this->belongsTo(Plugin::class);
    }

    /**
     * Get all multi-currency prices for this TLD.
     */
    public function prices()
    {
        return $this->hasMany(TldPrice::class);
    }
    
    /**
     * Get all registrants (domain orders) using this TLD.
     */
    public function registrants()
    {
        return $this->hasMany(Registrant::class);
    }
}
