<!DOCTYPE html>
<html lang="en">
 @include('portal::layouts.wrapper')
 <body>
     @include('portal::layouts.partials.header')
     <main>
         @yield('body')
    </main>
    @include('portal::layouts.partials.footer')
    @include('portal::layouts.script')
    @livewireScripts
</body>
</html>