<!DOCTYPE html>
<html lang="en">
<head>
  @include('client::layouts.wrapper')
</head>
<body class="bg-billmora-1">
  <main class="flex justify-center w-full h-dvh">
    <div class="my-auto mx-4 md:mx-0 text-center">
      <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 800)" x-show="show" class="relative bg-billmora-1 flex">
        <div class="flex-col gap-4 w-full flex items-center justify-center">  
          <img src="{{ Billmora::getGeneral('company_logo') }}" alt="company logo" class="h-16 w-16 absolute top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 animate-none">
          <div class="w-30 h-30 border-4 border-transparent text-white text-4xl animate-spin flex items-center justify-center border-t-billmora-primary rounded-full">
            <div class="w-26 h-26 border-4 border-transparent text-billmora-primary  text-2xl animate-spin flex items-center justify-center border-t-billmora-primary  rounded-full">
            </div>
          </div>
        </div>
      </div>
      <div class="mt-8">
        <h1 class="text-xl text-billmora-primary font-semibold">{{ $message }}</h1>
      </div>
    </div>
  </main>
</body>
</html>