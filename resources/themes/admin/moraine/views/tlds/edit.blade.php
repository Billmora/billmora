@extends('admin::layouts.app')

@section('title', "TLD Edit - {$tld->tld}")

@section('body')
    @livewire('admin.tlds.tld-edit', ['tld' => $tld])
@endsection
