@props([
    'name',
    'show' => false,
    'maxWidth' => 'sm',
    'title' => 'Hapus Data?',
    'description' => 'Tindakan ini tidak dapat dibatalkan. Data akan dihapus secara permanen dari sistem.',
    'confirmText' => 'Ya, Hapus',
    'cancelText' => 'Batal',
    'confirmVariant' => 'danger',
    'action' => null,
    'method' => 'DELETE',
    'confirmEvent' => 'confirm-modal',
])

@php
    $usesForm = !is_null($action);
@endphp

<x-modal :name="$name" :show="$show" :maxWidth="$maxWidth" panelClass="rounded-3xl shadow-2xl">
    <div class="modal-body text-center">
        <div class="w-14 h-14 rounded-lg bg-rose-50 flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-rose-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
        </div>
        <h3 class="text-lg font-bold text-gray-800 mb-1">{{ $title }}</h3>
        <p class="text-sm text-gray-400">{{ $description }}</p>
    </div>

    @if ($usesForm)
        <form action="{{ $action }}" method="POST" class="modal-footer justify-center">
            @csrf
            @method($method)
            {{ $slot }}
            <x-button type="button" variant="secondary" class="flex-1"
                x-on:click="$dispatch('close-modal', '{{ $name }}')">
                {{ $cancelText }}
            </x-button>
            <x-button type="submit" :variant="$confirmVariant" class="flex-1">
                {{ $confirmText }}
            </x-button>
        </form>
    @else
        <div class="modal-footer justify-center">
            <x-button type="button" variant="secondary" class="flex-1"
                x-on:click="$dispatch('close-modal', '{{ $name }}')">
                {{ $cancelText }}
            </x-button>
            <x-button type="button" :variant="$confirmVariant" class="flex-1"
                x-on:click="$dispatch('{{ $confirmEvent }}', '{{ $name }}')">
                {{ $confirmText }}
            </x-button>
        </div>
    @endif
</x-modal>
