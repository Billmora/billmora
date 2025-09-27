@extends('admin::layouts.app')

@section('title', "Activity View - {$user->email}")

@section('body')
<div class="flex flex-col gap-5">
    <div class="grid gap-4 bg-white p-8 border-2 border-billmora-2 rounded-2xl">
        <h4 class="text-lg text-slate-600 font-semibold">{{ __('admin/audits/user.title') }}</h4>
        <div class="grid grid-cols-none md:grid-cols-2 gap-4">
            <div class="grid">
                <span class="text-slate-600 font-semibold">{{ __('admin/audits/user.event_label') }}</span>
                <span class="text-slate-500">{{ $activity->event }}</span>
            </div>
            <div class="grid">
                <span class="text-slate-600 font-semibold">{{ __('admin/audits/user.user_label') }}</span>
                <a href="{{ route('admin.users.summary', ['id' => $user->id]) }}" class="inline-flex items-center text-sm font-semibold text-billmora-primary hover:text-billmora-primary-hover">
                    {{ $user->email }}
                </a>
            </div>
        </div>
        @if($activity->properties)
            <div class="space-y-2">
                <h3 class="font-semibold text-slate-600">{{ __('admin/audits/user.properties_label') }}</h3>
                <div class="bg-billmora-1 px-4 py-2 border-2 border-billmora-2 rounded-lg">
                    <dl class="divide-y-2 divide-billmora-2 text-sm">
                        @foreach($activity->properties as $key => $value)
                            <div class="flex flex-col md:flex-row justify-between py-2">
                                <dt class="font-medium text-slate-600 md:w-1/3">{{ $key }}</dt>
                                <dd class="text-slate-500 md:w-2/3 mt-1 md:mt-0">
                                    @if(is_array($value) || is_object($value))
                                        <pre class="text-sm bg-white p-2 border border-billmora-2 rounded-lg overflow-x-auto">{{ json_encode($value, JSON_PRETTY_PRINT) }}</pre>
                                    @else
                                        <span class="break-words">{{ $value }}</span>
                                    @endif
                                </dd>
                            </div>
                        @endforeach
                    </dl>
                </div>
            </div>
        @endif
    </div>
    <div class="flex gap-4 ml-auto">
        <a href="{{ route('admin.users.activity', ['id' => $user->id]) }}" class="bg-billmora-1 border-2 border-billmora-primary hover:bg-billmora-primary-hover px-3 py-2 text-billmora-primary hover:text-white rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">{{ __('common.cancel') }}</a>
    </div>
</div>
@endsection