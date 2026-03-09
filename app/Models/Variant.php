<?php

namespace App\Models;

use App\Contracts\BrowseInterface;
use App\Traits\BrowseTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Variant extends Model implements BrowseInterface
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
     * @var list<string>
     */
    protected $casts = [
        'status' => 'string',
        'is_scalable' => 'boolean',
    ];

    /**
     * Get the packages associated with this variant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function packages()
    {
        return $this->belongsToMany(Package::class)->withTimestamps();
    }

    /**
     * Get all options belonging to this variant.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function options()
    {
        return $this->hasMany(VariantOption::class);
    }

    /**
     * Return a collection of variant records formatted as browse items for quick search indexing.
     *
     * @return \Illuminate\Support\Collection
     */
    public static function toBrowseItems(): Collection
    {
        return static::select('id', 'name')
            ->limit(50)
            ->get()
            ->map(fn($item) => [
                'title' => "{$item->name}",
                'category' => 'variant',
                'url' => route('admin.variants.edit', ['variant' => $item->id]),
            ]);
    }
}
