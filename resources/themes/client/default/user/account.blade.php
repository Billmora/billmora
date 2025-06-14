@extends('client::layouts.app')

@section('body')
  <div class="container client">
    <x-client.breadcrumb title="Account" route="{{ route('client.user.account') }}" />
  </div>
@endsection