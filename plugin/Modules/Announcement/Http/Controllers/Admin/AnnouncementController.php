<?php

namespace Plugins\Modules\Announcement\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\AuditsSystem;
use Illuminate\Http\Request;
use Plugins\Modules\Announcement\Models\AnnouncementPost;

class AnnouncementController extends Controller
{
    use AuditsSystem;

    /**
     * Display a listing of all announcements.
     */
    public function index(Request $request)
    {
        $search = $request->query('search');

        $posts = AnnouncementPost::query()
            ->when($search, function ($query, $search) {
                $query->where('title', 'like', "%{$search}%");
            })
            ->orderByDesc('created_at')
            ->paginate(15)
            ->withQueryString();

        return view('module.announcement::admin.index', compact('posts'));
    }

    /**
     * Show the form for creating a new announcement.
     */
    public function create()
    {
        return view('module.announcement::admin.create');
    }

    /**
     * Store a newly created announcement.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'content'      => ['required', 'string'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $post = AnnouncementPost::create([
            'title'        => $validated['title'],
            'content'      => $validated['content'],
            'is_published' => $validated['is_published'] ?? false,
            'published_at' => ($validated['is_published'] ?? false) ? now() : null,
        ]);

        $this->recordCreate('module.announcement.create', $post->toArray());

        return redirect()->route('admin.modules.announcement.index')
            ->with('success', __('common.create_success', ['attribute' => 'Announcement']));
    }

    /**
     * Show the form for editing the specified announcement.
     */
    public function edit(AnnouncementPost $post)
    {
        return view('module.announcement::admin.edit', compact('post'));
    }

    /**
     * Update the specified announcement.
     */
    public function update(Request $request, AnnouncementPost $post)
    {
        $validated = $request->validate([
            'title'        => ['required', 'string', 'max:255'],
            'content'      => ['required', 'string'],
            'is_published' => ['nullable', 'boolean'],
        ]);

        $oldPost = $post->getOriginal();

        $isPublished = $validated['is_published'] ?? false;

        $post->update([
            'title'        => $validated['title'],
            'content'      => $validated['content'],
            'is_published' => $isPublished,
            'published_at' => $isPublished && !$post->published_at ? now() : $post->published_at,
        ]);

        $this->recordUpdate('module.announcement.update', $oldPost, $post->getChanges());

        return redirect()->route('admin.modules.announcement.index')
            ->with('success', __('common.save_success', ['attribute' => 'Announcement']));
    }

    /**
     * Remove the specified announcement.
     */
    public function destroy(AnnouncementPost $post)
    {
        $this->recordDelete('module.announcement.delete', $post->toArray());

        $post->delete();

        return redirect()->route('admin.modules.announcement.index')
            ->with('success', __('common.delete_success', ['attribute' => 'Announcement']));
    }
}
