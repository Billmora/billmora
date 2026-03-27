@extends('admin::layouts.app')

@section('title', 'Create Announcement')

@section('body')
<div class="flex flex-col gap-4">
    <div class="w-full md:w-100">
        <h1 class="text-2xl font-bold text-slate-800">Create Announcement</h1>
        <p class="text-slate-500 text-sm">Publish a new announcement to your clients.</p>
    </div>

    <form action="{{ route('admin.modules.announcement.store') }}" method="POST" class="flex flex-col gap-5">
        @csrf
        <div class="grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
            <x-admin::input type="text" name="title" label="Title" helper="The title of the announcement." value="{{ old('title') }}" required />
            
            <x-admin::editor.text name="content" label="Content" helper="The main body of the announcement." required>{{ old('content') }}</x-admin::editor.text>

            <x-admin::toggle name="is_published" label="Publish immediately" helper="If enabled, clients will see this immediately." :checked="old('is_published') ? true : false" />
        </div>
        <div class="flex gap-4 ml-auto">
            <a href="{{ route('admin.modules.announcement.index') }}" class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-billmora-primary-500 hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</a>
            <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-3 py-2 text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.create') }}</button>
        </div>
    </form>
</div>
@endsection
