@extends('client::layouts.app')

@section('title', 'Join Affiliate Program')

@section('body')
<div class="flex flex-col gap-6">
    <div class="grid gap-1">
        <h1 class="text-2xl font-bold text-slate-700">Affiliate Program</h1>
        <p class="text-slate-500 text-sm">Earn commissions by referring new customers to our services.</p>
    </div>

    <div class="bg-billmora-bg border-2 border-billmora-2 rounded-2xl p-12 flex flex-col items-center justify-center text-center gap-5">
        <div class="bg-billmora-primary-500/10 p-5 rounded-full text-billmora-primary-500">
            <x-lucide-handshake class="w-12 h-12" />
        </div>
        <div class="grid gap-2 max-w-md">
            <h2 class="text-xl font-bold text-slate-700">Join Our Affiliate Program</h2>
            <p class="text-slate-500">Share your unique referral link, refer new customers, and earn commissions on their purchases. It's simple and free to join.</p>
        </div>
        <form action="{{ route('client.modules.affiliate.join') }}" method="POST">
            @csrf
            <button type="submit" class="bg-billmora-primary-500 hover:bg-billmora-primary-600 px-6 py-3 text-white font-semibold rounded-lg transition-colors ease-in-out duration-150 cursor-pointer">
                Join Now
            </button>
        </form>
    </div>
</div>
@endsection
