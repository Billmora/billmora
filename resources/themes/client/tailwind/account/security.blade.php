@extends('client::layouts.app')

@section('body')
  <div class="flex gap-4">
    <form action="" class="w-full h-auto bg-billmora-2 p-6 rounded-lg border-3 border-billmora-3">
      @csrf
        <div class="flex flex-col gap-4">
          <h3 class="text-lg text-slate-600 font-bold">{{ __('client.update_password') }}</h3>
            {{-- progress --}}
        </div>
    </form>
  </div>
@endsection