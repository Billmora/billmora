@php
    $current = $paginator->currentPage();
    $last = $paginator->lastPage();
    $pages = [];

    if ($last <= 5) {
        $pages = range(1, $last);
    } elseif ($current <= 2) {
        $pages = [1, 2, 3, '...', $last];
    } elseif ($current == 3) {
        $pages = [1, '...', 3, 4, 5, '...', $last];
    } elseif ($current >= $last - 1) {
        $pages = [1, '...', $last - 2, $last - 1, $last];
    } elseif ($current == $last - 2) {
        $pages = [1, '...', $current, $current + 1, $current + 2];
    } else {
        $pages = [1, '...', $current - 1, $current, $current + 1, '...', $last];
    }
@endphp

@if ($paginator->hasPages())
  <nav class="flex items-center justify-between">
    @if ($paginator->onFirstPage())
      <span class="flex items-center gap-2 px-4 py-2 bg-billmora-2 border-2 border-billmora-3 text-slate-500 rounded-lg cursor-not-allowed">
        <x-lucide-chevron-left class="w-5 h-auto" />
        Previous
      </span>
    @else
      <a href="{{ $paginator->previousPageUrl() }}" class="flex items-center gap-2 px-4 py-2 bg-white border-2 border-billmora-2 hover:bg-billmora-primary-500 text-slate-600 hover:text-white rounded-lg transition ease-in-out duration-300">
        <x-lucide-chevron-left class="w-5 h-auto" />
        Previous
      </a>
    @endif
    <div class="hidden md:flex gap-2">
      @foreach ($pages as $page)
        @if ($page === '...')
          <x-lucide-ellipsis class="w-5 h-auto text-slate-600" />
        @elseif ($page == $current)
          <span class="px-3 py-2 bg-billmora-primary-500 border-2 border-billmora-2 text-white rounded-lg">{{ $page }}</span>
        @else
          <a href="{{ $paginator->url($page) }}" class="px-3 py-2 bg-white border-2 border-billmora-2 hover:bg-billmora-primary-500 text-slate-600 hover:text-white rounded-lg transition ease-in-out duration-300">{{ $page }}</a>
        @endif
      @endforeach
    </div>
    @if ($paginator->hasMorePages())
      <a href="{{ $paginator->nextPageUrl() }}" class="flex items-center gap-2 px-4 py-2 bg-white border-2 border-billmora-2 hover:bg-billmora-primary-500 text-slate-600 hover:text-white rounded-lg transition ease-in-out duration-300">
        Next
        <x-lucide-chevron-right class="w-5 h-auto" />
      </a>
    @else
      <span class="flex items-center gap-2 px-4 py-2 bg-billmora-2 border-2 border-billmora-3 text-slate-500 rounded-lg cursor-not-allowed">
        Next
        <x-lucide-chevron-right class="w-5 h-auto" />
      </span>
    @endif
  </nav>
@endif
