<?php

namespace Plugins\Modules\Announcement\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AnnouncementPost extends Model
{
    protected $table = 'pm_announcement_posts';

    protected $fillable = [
        'title',
        'slug',
        'content',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    /**
     * Scope to only include published announcements.
     */
    public function scopePublished($query)
    {
        return $query->where('is_published', true)
                     ->whereNotNull('published_at')
                     ->where('published_at', '<=', now());
    }

    /**
     * Auto-generate slug from title when creating.
     */
    protected static function booted(): void
    {
        static::creating(function (AnnouncementPost $post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);

                $count = static::where('slug', 'like', $post->slug . '%')->count();
                if ($count > 0) {
                    $post->slug .= '-' . ($count + 1);
                }
            }
        });
    }
}
