@props(['title', 'route'])

<div class="breadcrumb">
  <h2>{{ $title }}</h2>
  <div class="link">
    <a href="/">Portal</a>
    <a href="/dashboard">Dashboard</a>
    <a href="{{ $route }}">{{ $title }}</a>
  </div>
</div>