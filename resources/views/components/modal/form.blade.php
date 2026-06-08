@props(['name', 'show' => false, 'maxWidth' => '2xl', 'title' => null, 'subtitle' => null])

<x-modal :name="$name" :show="$show" :maxWidth="$maxWidth" panelClass="rounded-3xl shadow-2xl">
    @if (isset($header))
        <div class="modal-header">
            {{ $header }}
        </div>
    @else
        <div class="modal-header">
            <div>
                @if ($title)
                    <h2 class="modal-title">{{ $title }}</h2>
                @endif
                @if ($subtitle)
                    <p class="text-xs text-gray-600 mt-0.5">{{ $subtitle }}</p>
                @endif
            </div>
            <button type="button" x-on:click="$dispatch('close-modal', '{{ $name }}')" class="modal-close">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>
    @endif

    <div class="modal-body max-h-[calc(100vh-200px)] overflow-y-auto">
        {{ $slot }}
    </div>

    @if (isset($footer))
        <div class="modal-footer">
            {{ $footer }}
        </div>
    @endif
</x-modal>
