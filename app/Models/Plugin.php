<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class Plugin extends Model
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
     * Get the attributes that should be cast.
     *
     * @var list<string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'config' => 'array',
    ];

    /**
     * Get plugin manifest data from plugin.json file with caching.
     *
     * @return array<string, mixed>
     */
    public function getManifestAttribute(): array
    {
        $cacheKey = "{$this->type}-{$this->provider}";

        if (isset(self::$manifestCache[$cacheKey])) {
            return self::$manifestCache[$cacheKey];
        }

        $folderType = Str::plural(ucfirst($this->type));
        $path = base_path("plugin/{$folderType}/{$this->provider}/plugin.json");

        if (File::exists($path)) {
            $data = json_decode(File::get($path), true);
            self::$manifestCache[$cacheKey] = $data;
            return $data;
        }

        return [];
    }
}
