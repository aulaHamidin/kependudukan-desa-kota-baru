{{-- Shared Component: Event Status Badge --}}

@props(['status'])

@php
    $variants = [
        'DRAFT' => ['color' => 'yellow', 'label' => 'Draf', 'icon' => 'pencil-alt'],
        'VERIFIED' => ['color' => 'green', 'label' => 'Terverifikasi', 'icon' => 'check-circle'],
        'PENDING' => ['color' => 'yellow', 'label' => 'Pending', 'icon' => 'clock'],
        'VOID' => ['color' => 'red', 'label' => 'Dibatalkan', 'icon' => 'ban'],
    ];

    $config = $variants[$status] ?? ['color' => 'gray', 'label' => $status, 'icon' => 'question'];
    $colorClasses = [
        'yellow' => 'bg-yellow-100 text-yellow-800 border-yellow-300',
        'green' => 'bg-green-100 text-green-800 border-green-300',
        'red' => 'bg-red-100 text-red-800 border-red-300',
        'gray' => 'bg-gray-100 text-gray-800 border-gray-300',
    ];
@endphp

<span
    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $colorClasses[$config['color']] }}">
    <i class="fas fa-{{ $config['icon'] }} mr-1"></i>
    {{ $config['label'] }}
</span>
