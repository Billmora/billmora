@extends('client::layouts.app')

@section('title', 'Store')

@section('body')
    @livewire(\App\Livewire\Client\Store\PackageCheckout::class, [
        'package' => $package,
        'pricesPayload' => $packagePricesPayload,
        'variantsPayload' => $variantsPayload,
        'checkoutSchema' => $checkoutSchema
    ])
@endsection