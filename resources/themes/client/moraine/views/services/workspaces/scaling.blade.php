@extends('client::services.show')

@section('workspaces')
    @livewire(\App\Livewire\Client\Service\ScalingWizard::class, ['service' => $service])
@endsection