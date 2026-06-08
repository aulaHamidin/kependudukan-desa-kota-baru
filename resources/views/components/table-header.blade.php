@props([
    'sortable' => false,
    'sortKey' => null,
    'align' => 'left',
])

@php
    $alignClass = match ($align) {
        'center' => 'text-center',
        'right' => 'text-right',
        default => 'text-left',
    };
@endphp

<th {{ $attributes->merge(['class' => "table-header {$alignClass}"]) }}>
    @if ($sortable && $sortKey)
        <a href="{{ request()->fullUrlWithQuery(['sort' => $sortKey, 'direction' => request('direction') === 'asc' ? 'desc' : 'asc']) }}"
            class="inline-flex items-center gap-1 hover:text-gray-600 transition">
            {{ $slot }}
            <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M8.25 15L12 18.75 15.75 15m-7.5-6L12 5.25 15.75 9" />
            </svg>
        </a>
    @else
        {{ $slot }}
    @endif
</th>
