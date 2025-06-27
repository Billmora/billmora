<!DOCTYPE html>
<html lang="en">
 @include('portal::layouts.wrapper')
 <body class="bg-billmora-1">
    @include('portal::layouts.partials.preload')
    @include('portal::layouts.partials.header')
    <main>
        @yield('body')
    </main>
    @include('portal::layouts.partials.footer')
    @include('portal::layouts.script')
    @livewireScripts
</body>
</html>