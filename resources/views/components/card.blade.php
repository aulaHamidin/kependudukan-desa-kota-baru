@props([
    'padding' => true,
    'hover' => false,
    'variant' => null,
])

@php
    $variantMap = [
        'primary' => 'card-primary',
        'success' => 'card-success',
        'warning' => 'card-warning',
        'danger' => 'card-danger',
        'neutral' => 'card-neutral',
        'village' => 'card-village',
        'earth' => 'card-earth',
    ];

    $classes = $variant ? $variantMap[$variant] ?? 'card' : 'card';
    if ($hover) {
        $classes .= ' card-hover';
    }
@endphp

<div {{ $attributes->merge(['class' => $classes]) }}>
    @if (isset($header))
        <div class="px-4 sm:px-5 py-3 border-b border-gray-200 bg-gray-50 rounded-t-md">
            {{ $header }}
        </div>
    @endif

    <div class="{{ $padding ? 'p-4 sm:p-5' : '' }}">
        {{ $slot }}
    </div>

    @if (isset($footer))
        <div class="px-4 sm:px-5 py-3 border-t border-gray-200 bg-gray-50 rounded-b-md">
            {{ $footer }}
        </div>
    @endif
</div>
