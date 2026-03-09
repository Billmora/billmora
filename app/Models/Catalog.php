<?php

namespace App\Models;

use App\Contracts\BrowseInterface;
use App\Traits\BrowseTrait;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Catalog extends Model implements BrowseInterface
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
    ];

    /**
     * Get all packages that belong to this catalog.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function packages()
    {
        return $this->hasMany(Package::class);
    }

    /**
     * Return a collection of catalog records formatted as browse items for quick search indexing.
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
                'category' => 'catalog',
                'url' => route('admin.catalogs.edit', ['id' => $item->id]),
            ]);
    }
}
