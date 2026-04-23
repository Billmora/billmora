@extends('client::layouts.app')

@section('title', __('common.configure') . ' - ' . $domain_name)

@section('body')
    @livewire('client.store.domain-configure', ['domainName' => $domain_name, 'type' => $type])
@endsection
