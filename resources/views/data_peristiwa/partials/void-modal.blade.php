{{-- Shared Modal: Void Event --}}
{{-- Usage: Include di show.blade.php untuk event VERIFIED --}}

@props(['event'])

<x-modal name="void-event-{{ $event->id }}" title="Batalkan Event" max-width="md">
    <form method="POST" action="{{ route('events.void', $event) }}">
        @csrf

        <div class="space-y-4">
            <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-4">
                <div class="flex items-start">
                    <svg class="w-5 h-5 text-yellow-600 mt-0.5 mr-3" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <div>
                        <h4 class="text-sm font-semibold text-yellow-800">Perhatian</h4>
                        <p class="text-sm text-yellow-700 mt-1">
                            Event yang dibatalkan tidak dapat dikembalikan. Pastikan Anda yakin sebelum melanjutkan.
                        </p>
                    </div>
                </div>
            </div>

            <x-form-textarea name="void_reason" label="Alasan Pembatalan" rows="4"
                placeholder="Jelaskan alasan pembatalan event ini (minimal 10 karakter)" required :value="old('void_reason')" />
            @error('void_reason')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>

        <div class="flex justify-end gap-2 mt-6">
            <x-button type="button" variant="secondary"
                x-on:click="$dispatch('close-modal', 'void-event-{{ $event->id }}')">
                Batal
            </x-button>
            <x-button type="submit" variant="danger">
                <i class="fas fa-ban mr-2"></i>
                Batalkan Event
            </x-button>
        </div>
    </form>
</x-modal>
