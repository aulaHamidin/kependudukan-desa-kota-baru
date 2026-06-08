@props([
    'searchable' => true,
    'searchName' => 'search',
    'searchPlaceholder' => 'Cari data...',
    'searchValue' => null,
    'datatableSearchFor' => null,
    'compact' => false,
])

@php
    $value = $searchValue ?? request($searchName);
    $containerClasses = $compact ? 'w-full' : 'w-full px-4 py-3 border-b border-gray-200 bg-gray-50';
@endphp

<div class="{{ $containerClasses }}">
    <div class="flex flex-col sm:flex-row sm:items-center gap-3">
        @if ($datatableSearchFor)
            <div class="flex-1 max-w-sm" data-datatable-search-for="{{ $datatableSearchFor }}"></div>
        @elseif ($searchable)
            <div
                class="flex items-center gap-2 bg-gray-100/80 rounded-lg px-3 py-2.5 focus-within:ring-2 focus-within:ring-primary-500/30 focus-within:bg-white transition-all flex-1 max-w-sm">
                <svg class="w-4 h-4 text-gray-400 shrink-0" fill="none" stroke="currentColor" stroke-width="2"
                    viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                </svg>
                <input type="text" name="{{ $searchName }}" value="{{ $value }}"
                    placeholder="{{ $searchPlaceholder }}"
                    class="bg-transparent border-none outline-none text-sm text-gray-700 placeholder:text-gray-400 w-full focus:ring-0">
            </div>
        @endif

        @if (isset($filters) || trim($slot))
            <div class="flex items-center gap-2 ml-auto">
                @isset($filters)
                    {{ $filters }}
                @endisset
                {{ $slot }}
            </div>
        @endif
    </div>
</div>
