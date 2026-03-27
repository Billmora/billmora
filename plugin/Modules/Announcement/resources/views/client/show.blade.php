@extends('client::layouts.app')

@section('title', $post->title)

@section('body')
<div class="flex flex-col gap-5">
    <div class="flex flex-col md:flex-row gap-4 justify-between md:items-center">
        <div class="grid gap-1">
            <h1 class="text-2xl font-bold text-slate-700">{{ $post->title }}</h1>
            <div class="flex items-center gap-1.5 text-sm font-semibold text-slate-500">
                <x-lucide-calendar class="w-4 h-4" />
                {{ $post->published_at->format(Billmora::getGeneral('company_date_format')) }}
            </div>
        </div>
        <a href="{{ route('client.modules.announcement.index') }}" class="my-auto bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.back') }}</a>
    </div>

    <div class="bg-billmora-bg border-2 border-billmora-2 rounded-2xl p-6 md:p-8">
        <article class="prose prose-slate max-w-none prose-headings:font-bold prose-a:text-billmora-primary-500 text-slate-600">
            {!! $post->content !!}
        </article>
    </div>
</div>
@endsection
