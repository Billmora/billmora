<?php

namespace Plugins\Modules\Announcement\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use Plugins\Modules\Announcement\Models\AnnouncementPost;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of published announcements.
     */
    public function index()
    {
        $posts = AnnouncementPost::published()
            ->orderByDesc('published_at')
            ->paginate(10);

        return view('module.announcement::client.index', compact('posts'));
    }

    /**
     * Display the specified published announcement.
     */
    public function show(AnnouncementPost $post)
    {
        abort_if(!$post->is_published, 404);

        return view('module.announcement::client.show', compact('post'));
    }
}
