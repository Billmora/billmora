@extends('admin::layouts.app')

@section('title', 'Variant Options - Edit')

@section('body')
    @livewire('admin.variants.option-edit', ['variant' => $variant, 'option' => $option])
@endsection