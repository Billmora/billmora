@extends('client::layouts.app')

@section('title', 'Store')

@section('body')
    @livewire('client.store.package-checkout', ['package' => $package])
@endsection