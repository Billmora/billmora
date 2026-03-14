<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Theme Configuration - {{ $theme->name }}</title>
    @include('client::layouts.meta')
</head>
<body class="bg-billmora-1">
    <form action="{{ route('admin.themes.config.update', $theme->id) }}" method="POST" class="grid gap-5 p-6">
        @csrf
        <div class="w-full h-fit grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
            
            <div class="grid gap-4 col-span-12 lg:col-span-1 bg-white p-6 border-2 border-billmora-2 rounded-2xl h-fit">
                <div class="border-b-2 border-billmora-2 pb-4">
                    <h2 class="text-xl font-semibold text-slate-800">Auth Configuration</h2>
                    <p class="text-sm text-slate-500">Customize the text of the Moraine client theme in the authentication page.</p>
                </div>
                <div class="grid gap-4">
                    <x-client::input 
                        name="auth_logo_url"
                        label="Auth Logo URL"
                        value="{{ old('auth_logo_url', $theme->config['auth_logo_url'] ?? '') }}"
                        required
                    />
                    <x-client::input 
                        name="auth_message_title"
                        label="Auth Message Title"
                        value="{{ old('auth_message_title', $theme->config['auth_message_title'] ?? '') }}"
                        required
                    />
                    <x-client::textarea
                        name="auth_message_description"
                        label="Auth Message Description"
                        rows="6"
                        required
                    >{{ old('auth_message_description', $theme->config['auth_message_description'] ?? '') }}</x-client::textarea>
                </div>
            </div>

            <div class="w-full h-fit grid gap-4 col-span-12 lg:col-span-2 bg-white p-6 border-2 border-billmora-2 rounded-2xl">
                <div class="border-b-2 border-billmora-2 pb-4">
                    <h2 class="text-xl font-semibold text-slate-800">Color Configuration</h2>
                    <p class="text-sm text-slate-500">Customize the global appearance and palette of the Moraine client theme.</p>
                </div>
                @php
                    $colorPalette = [
                        'billmora_bg' => ['label' => 'Background', 'default' => '#ffffff'],
                        'billmora_1' => ['label' => 'Billmora 1 (Base)', 'default' => '#f4f7fe'],
                        'billmora_2' => ['label' => 'Billmora 2 (Border)', 'default' => '#eceeff'],
                        'billmora_3' => ['label' => 'Billmora 3', 'default' => '#e0e2ff'],
                        'billmora_4' => ['label' => 'Billmora 4', 'default' => '#d6d9ff'],
                        'billmora_5' => ['label' => 'Billmora 5', 'default' => '#cdd0ff'],
                        'billmora_6' => ['label' => 'Billmora 6 (Dark)', 'default' => '#c3c6ff'],
                        'billmora_primary_50' => ['label' => 'Primary 50', 'default' => '#f0f0ff'],
                        'billmora_primary_100' => ['label' => 'Primary 100', 'default' => '#e0e0ff'],
                        'billmora_primary_200' => ['label' => 'Primary 200', 'default' => '#c2c2ff'],
                        'billmora_primary_300' => ['label' => 'Primary 300', 'default' => '#9494ff'],
                        'billmora_primary_400' => ['label' => 'Primary 400', 'default' => '#7b71f9'],
                        'billmora_primary_500' => ['label' => 'Primary 500 (Base)', 'default' => '#7267ef'],
                        'billmora_primary_600' => ['label' => 'Primary 600 (Hover)', 'default' => '#6659e0'],
                        'billmora_primary_700' => ['label' => 'Primary 700', 'default' => '#5345cc'],
                        'billmora_primary_800' => ['label' => 'Primary 800', 'default' => '#4338a8'],
                        'billmora_primary_900' => ['label' => 'Primary 900', 'default' => '#383087'],
                        'billmora_primary_950' => ['label' => 'Primary 950', 'default' => '#211c4d'],
                    ];
                @endphp
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-5">
                    @foreach ($colorPalette as $key => $config)
                        <div
                            x-data="{ color: '{{ old($key, $theme->config[$key] ?? $config['default']) }}' }"
                            class="w-full"
                        >
                            <label class="block text-slate-600 font-semibold text-sm mb-1">{{ $config['label'] }}</label>
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-10 h-10 rounded-lg border-2 border-billmora-2 shrink-0 cursor-pointer shadow-sm"
                                    :style="'background-color: ' + color"
                                    x-on:click="$refs.picker_{{ $key }}.click()"
                                ></div>
                                <div class="flex-1 flex items-center">
                                    <input
                                        type="text"
                                        name="{{ $key }}"
                                        maxlength="7"
                                        x-model="color"
                                        class="w-full px-3 py-2 text-sm rounded-lg border-2 border-billmora-2 outline-none text-slate-700 placeholder:text-slate-500 focus:ring-2 ring-billmora-primary-500 transition-shadow"
                                        placeholder="{{ ltrim($config['default'], '#') }}"
                                    />
                                </div>
                                <input type="color" x-ref="picker_{{ $key }}" x-model="color" class="sr-only" />
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        
        <div class="flex gap-4 ml-auto pt-4">
            <a href="{{ route('admin.themes') }}" class="bg-billmora-1 border-2 border-billmora-primary-500 hover:bg-billmora-primary-600 px-5 py-2 text-billmora-primary-500 hover:text-white font-semibold rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                {{ __('common.cancel') }}
            </a>
            <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 border-2 border-billmora-primary-500 hover:border-billmora-primary-600 px-5 py-2 text-white font-semibold rounded-lg transition-colors ease-in-out duration-150 cursor-pointer shadow-sm">
                {{ __('common.save') }}
            </button>
        </div>
    </form>
</body>
</html>