<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;

class Theme extends Model
{
    protected static array $manifestCache = [];

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
        'is_active' => 'boolean',
        'is_core' => 'boolean',
        'config' => 'array',
    ];

    /**
     * Get theme manifest data from theme.json file with caching.
     *
     * @return array<string, mixed>
     */
    public function getManifestAttribute(): array
    {
        $cacheKey = "{$this->type}-{$this->provider}";

        if (isset(self::$manifestCache[$cacheKey])) {
            return self::$manifestCache[$cacheKey];
        }

        $path = resource_path("themes/{$this->type}/{$this->provider}/theme.json");

        if (File::exists($path)) {
            $data = json_decode(File::get($path), true) ?? [];
            self::$manifestCache[$cacheKey] = $data;
            return $data;
        }

        return [];
    }
}
