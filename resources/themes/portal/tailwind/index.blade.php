@extends('portal::layouts.app')

@section('body')
<div class="fixed top-0 h-full w-full bg-billmora-1 bg-[radial-gradient(circle_at_center,var(--color-billmora-primary)_1px,transparent_0)] [background-size:1.5rem_1.5rem] -z-1"></div>
<div class="flex flex-col xl:max-w-[87.5rem] mx-auto my-14 px-4 xl:px-0">
  <span class="flex flex-row mx-auto items-center gap-2 bg-billmora-1 text-billmora-primary font-bold px-4 py-2 rounded-xl border-2 border-billmora-secondary-hover">
    <x-lucide-star class="h-6 w-auto"/>
    Good choice for the Future!
  </span>
  <h1 class="text-billmora-primary text-6xl font-semibold mt-18 text-center">{{ Billmora::getGeneral('company_name') }}</h1>
  <p class="text-slate-800 text-2xl mt-2 text-center">{{ Billmora::getGeneral('company_description') }}</p>
  <div class="flex flex-col md:flex-row gap-4 mt-40 flex-wrap flex-1 justify-evenly">
    <x-portal::feature icon="lucide-clock" title="24/7 Support" description="Get peace of mind with our expert support team available 24/7. Whether you need technical help or have questions, we're here to assist you at any time, day or night."/>
    <x-portal::feature icon="lucide-expand" title="Scalable Resource" description="Grow with ease—our hosting solutions scale with your needs. Add more storage, bandwidth, or processing power whenever you need it, without any downtime."/>
    <x-portal::feature icon="lucide-cpu" title="Fast Performance" description="Enjoy blazing-fast performance with our optimized servers. Whether it's a website, application, or database, we ensure minimal load times and a smooth experience for your users."/>
  </div>
</div>
@endsection