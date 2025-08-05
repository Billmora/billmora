<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('client::layouts.meta')
</head>
<body class="bg-billmora-1">
    <div class="flex w-full min-h-dvh">
        <div class="w-full lg:w-1/2 h-auto p-8">
            <div class="max-w-140 h-full flex flex-col justify-between mx-auto">
                <a href="#" class="flex gap-2 items-center text-slate-500 font-semibold">
                    <x-lucide-chevron-left class="w-auto h-5" />
                    <span>Back to Portal</span>
                </a>
                <div class="flex my-auto">
                    <h3 class="font-semibold text-2xl text-slate-700">Sign In to Continue</h3>
                </div>
            </div>
        </div>
        <div class="w-1/2 h-auto hidden lg:flex justify-center bg-billmora-primary rounded-bl-[100px]">
            <div class="max-w-140 my-auto space-y-6">
                <img src="https://media.billmora.com/logo/main-invert-bgnone.png" alt="brand logo" class="w-32 h-auto">
                <span class="text-4xl font-bold text-white">Grow your business with Billmora!</span>
                <p class="text-slate-200">Billmora is a free and open-source billing management platform designed to automate recurring services and simplify operations for hosting businesses.</p>
            </div>
        </div>
    </div>
    @livewireScripts
</body>
</html>