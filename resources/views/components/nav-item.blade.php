@props(['title','icon','routes' => []])  {{-- Tambahkan default kosong --}}
<li class="nav-item">
    @if (!empty($routes))
        <a href="{{ route($routes[0]) }}"
        class="nav-link {{ in_array(request()->route()->getName(), $routes) ? 'active' : '' }}">
        <i class="nav-icon {{ $icon }}"></i>
        <p>{{ $title }}</p>
        </a>
    @endif
</li>
