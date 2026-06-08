@props([
    'type'     => 'button',
    'variant'  => 'primary',
    'size'     => 'default',
    'icon'     => null,
    'iconPos'  => 'left',
    'href'     => null,
    'disabled' => false,
    'loading'  => false,
])

@php
    $variantClasses = match ($variant) {
        'primary'   => 'btn-primary',
        'secondary' => 'btn-secondary',
        'danger'    => 'btn-danger',
        'warning'   => 'btn-warning',
        'village'   => 'btn-village',
        'ghost'     => 'btn-ghost',
        default     => 'btn-primary',
    };

    $sizeClasses = match ($size) {
        'sm'    => 'text-xs px-3 py-1.5',
        'lg'    => 'text-base px-6 py-3',
        default => '',
    };

    $disabledClasses = ($disabled || $loading)
        ? 'opacity-50 cursor-not-allowed pointer-events-none'
        : '';

    $classes = implode(' ', array_filter([
        'btn',
        $variantClasses,
        $sizeClasses,
        $disabledClasses,
    ]));

    $tag = $href ? 'a' : 'button';
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @endif
    @if (!$href) type="{{ $type }}" @endif
    @if ($disabled || $loading) disabled aria-disabled="true" @endif
    {{ $attributes->merge(['class' => $classes]) }}
>
    @if ($loading)
        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
        </svg>
    @else
        @if ($icon && $iconPos === 'left')
            <x-button-icon :icon="$icon" />
        @endif
    @endif

    @if ($slot->isNotEmpty())
        <span>{{ $slot }}</span>
    @endif

    @if ($icon && $iconPos === 'right' && !$loading)
        <x-button-icon :icon="$icon" />
    @endif
</{{ $tag }}>