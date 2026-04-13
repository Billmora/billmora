@extends('client::registrants.show')

@section('workspaces')
    <div class="bg-billmora-bg p-8 border-2 border-billmora-2 rounded-2xl">
        <div class="flex items-center gap-3 mb-6">
            @if(!empty($clientActions[$slug]['icon']))
                <i class="{{ $clientActions[$slug]['icon'] }} text-2xl text-billmora-primary-500"></i>
            @endif
            <h2 class="text-xl font-bold text-slate-700">{{ $clientActions[$slug]['label'] ?? __('client/services.action.form_title') }}</h2>
        </div>

        <form action="{{ route('client.registrants.registrar.handle', ['registrant' => $registrant->registrant_number, 'slug' => $slug]) }}" method="POST" class="grid gap-6">
            @csrf
            @if(in_array(strtoupper($clientActions[$slug]['method'] ?? 'POST'), ['PUT', 'PATCH', 'DELETE']))
                @method(strtoupper($clientActions[$slug]['method']))
            @endif

            @foreach($pageSchema as $fieldKey => $fieldConfig)
                @if(($fieldConfig['type'] ?? 'text') !== 'hidden')
                    <div class="grid gap-2">
                        <label for="{{ $fieldKey }}" class="text-sm font-semibold text-slate-600">
                            {{ $fieldConfig['label'] ?? ucfirst($fieldKey) }}
                            @if(isset($fieldConfig['rules']) && (is_string($fieldConfig['rules']) ? str_contains($fieldConfig['rules'], 'required') : in_array('required', $fieldConfig['rules'])))
                                <span class="text-red-500">*</span>
                            @endif
                        </label>
                        
                        @if($fieldConfig['type'] === 'select')
                            <select name="{{ $fieldKey }}" id="{{ $fieldKey }}" class="w-full px-4 py-2 border-2 border-billmora-2 rounded-lg bg-white outline-none focus:border-billmora-primary-500 text-slate-700">
                                @foreach($fieldConfig['options'] ?? [] as $val => $label)
                                    <option value="{{ $val }}" {{ old($fieldKey, $fieldConfig['default'] ?? '') == $val ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        @elseif($fieldConfig['type'] === 'textarea')
                            <textarea name="{{ $fieldKey }}" id="{{ $fieldKey }}" rows="4" class="w-full px-4 py-2 border-2 border-billmora-2 rounded-lg bg-white outline-none focus:border-billmora-primary-500 text-slate-700">{{ old($fieldKey, $fieldConfig['default'] ?? '') }}</textarea>
                        @else
                            <input type="{{ $fieldConfig['type'] ?? 'text' }}" name="{{ $fieldKey }}" id="{{ $fieldKey }}" value="{{ old($fieldKey, $fieldConfig['default'] ?? '') }}" class="w-full px-4 py-2 border-2 border-billmora-2 rounded-lg bg-white outline-none focus:border-billmora-primary-500 text-slate-700">
                        @endif

                        @if(!empty($fieldConfig['helper']))
                            <p class="text-xs text-slate-500 font-medium">{{ $fieldConfig['helper'] }}</p>
                        @endif

                        @error($fieldKey)
                            <span class="text-xs text-red-500 font-bold">{{ $message }}</span>
                        @enderror
                    </div>
                @else
                    <input type="hidden" name="{{ $fieldKey }}" value="{{ old($fieldKey, $fieldConfig['default'] ?? '') }}">
                @endif
            @endforeach

            <div class="flex justify-end mt-2">
                <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-6 py-2 rounded-lg text-white font-bold transition-colors shadow">
                    {{ __('common.submit') }}
                </button>
            </div>
        </form>
    </div>
@endsection
