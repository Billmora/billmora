@extends('client::layouts.app')

@section('title', __('client/store.domain_search_label'))

@section('body')
    @livewire('client.store.domain-search')
@endsection
