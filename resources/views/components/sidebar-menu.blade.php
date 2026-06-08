@props([
    'href' => '#',
    'active' => false,
    'icon' => null,
    'badge' => null,
    'size' => 'default',
])

@php
    $baseClasses = 'flex items-center gap-3 rounded-md text-[0.8125rem] font-medium transition-colors duration-150';

    $sizeClasses = $size === 'sm' ? 'px-3 py-1.5 ml-7 text-xs' : 'px-4 py-2';

    $colorClasses = $active ? 'nav-active' : 'text-blue-100 hover:bg-white/15 hover:text-white';

    $classes = "{$baseClasses} {$sizeClasses} {$colorClasses}";
@endphp

<a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
    @if ($icon)
        <x-sidebar-icon :name="$icon" :active="$active" />
    @endif

    <span class="{{ $active ? 'font-semibold' : '' }}">{{ $slot }}</span>

    @if ($badge)
        <span
            class="ml-auto text-[11px] font-semibold {{ $active ? 'bg-white/20' : 'bg-danger-500 text-white' }} px-2 py-0.5 rounded-full">
            {{ $badge }}
        </span>
    @endif
</a>
