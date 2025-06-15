@extends('client::layouts.app')

@section('body')
  <div class="container client">
    <x-client.breadcrumb title="Account Details" route="{{ route('client.account.detail') }}" />
  </div>
@endsection