@extends('admin::layouts.app')

@section('title', 'Variant Options - Create')

@section('body')
    @livewire('admin.variants.option-create', ['variant' => $variant])
@endsection