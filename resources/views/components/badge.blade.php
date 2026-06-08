@props([
    'type' => 'aktif', // aktif, pindah, meninggal, pending
])

@php
    $classes = match ($type) {
        'aktif' => 'badge-aktif',
        'pindah' => 'badge-pindah',
        'meninggal' => 'badge-meninggal',
        'pending' => 'badge-pending',
        default => 'badge-aktif',
    };
@endphp

<span {{ $attributes->merge(['class' => "badge {$classes}"]) }}>
    {{ $slot }}
</span>
