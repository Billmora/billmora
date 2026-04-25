<?php

namespace App\Models;

use App\Contracts\BrowseInterface;
use App\Traits\BrowseTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Tld extends Model implements BrowseInterface
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

    /**
     * Return a collection of TLD records formatted as browse items for quick search indexing.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function toBrowseItems(): Collection
    {
        return static::select('id', 'tld')
            ->limit(50)
            ->get()
            ->map(fn($item) => [
                'title' => "{$item->tld}",
                'category' => 'tld',
                'url' => route('admin.tlds.edit', ['tld' => $item->id]),
            ]);
    }

    /**
     * The "booted" method of the model.
     */
    protected static function booted(): void
    {
        static::addGlobalScope('order', function (\Illuminate\Database\Eloquent\Builder $builder) {
            $builder->orderBy('sort_order', 'asc')->orderBy('id', 'asc');
        });
    }
}
