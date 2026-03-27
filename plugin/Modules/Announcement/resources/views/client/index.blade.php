@extends('client::layouts.app')

@section('title', 'Announcements')

@section('body')
<div class="flex flex-col gap-6">
    <div class="grid gap-1">
        <h1 class="text-2xl font-bold text-slate-700">Announcements</h1>
        <p class="text-slate-500 text-sm">Stay up to date with the latest news and updates.</p>
    </div>

    <div class="grid gap-5">
        @forelse($posts as $post)
            <div class="bg-billmora-bg border-2 border-billmora-2 rounded-2xl p-6 transition-colors duration-200 hover:border-billmora-primary-500">
                <div class="flex flex-col gap-3">
                    <div>
                        <a href="{{ route('client.modules.announcement.show', $post->slug) }}" class="text-xl font-bold text-slate-700 hover:text-billmora-primary-500 transition-colors">
                            {{ $post->title }}
                        </a>
                        <div class="flex items-center gap-1.5 text-sm font-semibold text-slate-500 mt-1">
                            <x-lucide-calendar class="w-4 h-4" />
                            {{ $post->published_at->format(Billmora::getGeneral('company_date_format')) }}
                        </div>
                    </div>
                    <p class="text-slate-600 line-clamp-3">
                        {{ Str::limit(strip_tags($post->content), 250) }}
                    </p>
                    <div class="mt-2">
                        <a href="{{ route('client.modules.announcement.show', $post->slug) }}" class="inline-flex items-center gap-1 text-sm font-semibold text-billmora-primary-500 hover:text-billmora-primary-600 transition-colors">
                            Read More
                            <x-lucide-arrow-right class="w-4 h-4" />
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="bg-billmora-bg border-2 border-billmora-2 rounded-2xl p-12 flex flex-col items-center justify-center text-center gap-3">
                <div class="bg-slate-100 p-4 rounded-full text-slate-400">
                    <x-lucide-megaphone class="w-8 h-8" />
                </div>
                <p class="text-slate-500 font-medium">No announcements at this time.</p>
            </div>
        @endforelse
    </div>

    @if($posts->hasPages())
        <div>
            {{ $posts->links('client::layouts.partials.pagination') }}
        </div>
    @endif
</div>
@endsection
