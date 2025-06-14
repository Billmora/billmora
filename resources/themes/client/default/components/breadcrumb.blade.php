@props(['title' => null, 'route' => null])

<div class="breadcrumb">
  <h2>{{ $title }}</h2>
  <div class="link">
    <a href="/">Portal</a>
    @if($title && $route)
      <a href="/dashboard">Dashboard</a>
      <a class="active" href="{{ $route }}">{{ $title }}</a>
    @else
      <a class="active" href="/dashboard">Dashboard</a>
    @endif
  </div>
</div>