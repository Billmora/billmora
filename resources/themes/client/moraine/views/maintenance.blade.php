@section('title', 'Oopss.. Maintenance')

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('client::layouts.meta')
</head>
<body class="bg-white">
    <div class="flex justify-center w-full min-h-dvh">
        <div class="bg-billmora-1 my-auto mx-4 md:mx-none p-8 border-2 border-billmora-2 rounded-xl">
            <p class="text-xl text-billmora-primary font-semibold">{{ $message }}</p>
        </div>
    </div>
    @livewireScripts
</body>
</html>