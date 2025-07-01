<!DOCTYPE html>
<html lang="en">
 @include('client::layouts.wrapper')
 <body class="bg-billmora-1">
    {{-- @include('client::layouts.partials.preload') --}}
    @if (App\Services\BillmoraService::getGeneral('company_maintenance'))
        <div class="bg-billmora-primary" x-data="{ show: true }" x-show="show">
            <div class="w-full h-auto xl:max-w-[87.5rem] flex justify-between gap-2 mx-auto py-2 px-4 2xl:px-0">
                <p class="text-white">{{ App\Services\BillmoraService::getGeneral('company_maintenance_message') }}</p>
                <x-lucide-x class="w-6 h-auto text-white cursor-pointer" x-on:click="show = false"/>
            </div>
        </div>
    @endif
    @include('client::layouts.partials.header')
    <main class="bg-billmora-1 xl:max-w-[87.5rem] mx-auto my-14 px-4 2xl:px-0">
        @yield('body')
    </main>
    @include('client::layouts.partials.footer')
    @include('client::layouts.script')
    @livewireScripts
</body>
</html>