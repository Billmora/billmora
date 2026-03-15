<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    @include('portal::layouts.meta')
</head>
<body class="bg-billmora-1">
	<div class="flex flex-col">
        {{-- Header --}}
		@include('portal::layouts.partials.header')

        {{-- Main content --}}
		<main class="min-h-dvh">
			@yield('body')
		</main>
        
        {{-- Footer --}}
		@include('portal::layouts.partials.footer')
	</div>

    {{-- Scripts --}}
    @include('portal::layouts.script')
    @livewireScripts
</body>
</html>