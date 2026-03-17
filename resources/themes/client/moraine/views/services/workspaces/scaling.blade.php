@extends('client::services.show')

@section('workspaces')
    @livewire('client.service.scaling-wizard', ['service' => $service])
@endsection