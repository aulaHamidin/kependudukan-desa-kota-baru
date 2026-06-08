@props([
    'items' => [],
])

<nav class="flex items-center gap-2 text-sm text-blue-100">
    <a href="{{ route('dashboard') }}" class="hover:text-white transition no-underline">Dashboard</a>

    @foreach ($items as $item)
        <svg class="w-3 h-3 text-blue-200" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
        </svg>

        @if ($loop->last)
            <span class="text-white font-medium">{{ $item['label'] }}</span>
        @else
            <a href="{{ $item['url'] ?? '#' }}"
                class="hover:text-white transition no-underline">{{ $item['label'] }}</a>
        @endif
    @endforeach
</nav>
