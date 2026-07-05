<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('client::layouts.meta')
</head>

<body class="bg-billmora-neutral-50">
    @if (session()->has('impersonating'))
        @php $impersonating = session('impersonating'); @endphp
        <div class="flex items-center justify-between gap-4 bg-amber-500 px-5 py-2.5 text-white text-sm font-semibold">
            <div class="flex items-center gap-2">
                <span>{{ __('admin/users.impersonate_banner_text', ['admin' => $impersonating['admin_email']]) }}</span>
            </div>
            <form action="{{ route('client.impersonate.exit') }}" method="POST">
                @csrf
                <button type="submit"
                    class="flex items-center gap-1.5 bg-white/20 hover:bg-white/30 px-3 py-1 rounded-lg transition-colors duration-150 cursor-pointer whitespace-nowrap">
                    {{ __('admin/users.impersonate_exit_button') }}
                </button>
            </form>
        </div>
    @endif
    <div class="flex flex-row gap-5">
        <!-- Sidebar -->
        @include('client::layouts.partials.sidebar')

        <!-- Main -->
        <div class="w-full flex flex-col gap-5 min-h-dvh p-5 xl:pl-0">
            {{-- Header --}}
            @include('client::layouts.partials.header')

            {{-- Alert --}}
            @if (session('success'))
                <x-client::alert variant="success" title="{{ session('success') }}" />
            @endif
            @if (session('warning'))
                <x-client::alert variant="warning" title="{{ session('warning') }}" />
            @endif
            @if (session('error'))
                <x-client::alert variant="danger" title="{{ session('error') }}" />
            @endif
            <!-- Content -->
            <main>
                @yield('body')
            </main>

            {{-- Footer --}}
            @include('client::layouts.partials.footer')

        </div>
    </div>
    {{-- Scripts --}}
    @include('client::layouts.script')
    @livewireScripts
</body>

</html>
