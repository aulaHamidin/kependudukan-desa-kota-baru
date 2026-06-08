@props(['name', 'show' => false, 'size' => 'md', 'title' => null])

@php
    $sizeClass = match ($size) {
        'sm' => 'sm:w-[420px]',
        'lg' => 'sm:w-[640px]',
        default => 'sm:w-[520px]',
    };
@endphp

<div x-data="{ show: @js($show) }" x-init="$watch('show', value => {
    if (value) {
        document.body.classList.add('overflow-y-hidden');
    } else {
        document.body.classList.remove('overflow-y-hidden');
    }
})"
    x-on:open-drawer.window="$event.detail == '{{ $name }}' ? show = true : null"
    x-on:close-drawer.window="$event.detail == '{{ $name }}' ? show = false : null" x-on:close.stop="show = false"
    x-on:keydown.escape.window="show = false" x-show="show" class="fixed inset-0 z-50" style="display: none;">
    <div x-show="show" class="fixed inset-0 bg-gray-900/40 backdrop-blur-sm"
        x-on:click="show = false" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"></div>

    <div x-show="show"
        class="fixed top-0 right-0 h-full w-full {{ $sizeClass }} bg-white shadow-2xl border-l border-gray-100 overflow-y-auto"
        x-transition:enter="ease-out duration-300" x-transition:enter-start="translate-x-full"
        x-transition:enter-end="translate-x-0" x-transition:leave="ease-in duration-200"
        x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full">
        @if (isset($header))
            <div
                class="sticky top-0 z-10 bg-white/90 backdrop-blur-md border-b border-gray-100">
                {{ $header }}
            </div>
        @elseif ($title)
            <div
                class="sticky top-0 z-10 bg-white/90 backdrop-blur-md border-b border-gray-100">
                <div class="flex items-center justify-between px-6 py-4">
                    <h2 class="text-base font-bold text-gray-800">{{ $title }}</h2>
                    <button type="button" x-on:click="$dispatch('close-drawer', '{{ $name }}')"
                        class="w-9 h-9 rounded-lg flex items-center justify-center text-gray-400 hover:bg-gray-100 hover:text-gray-600 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        <div class="px-6 py-6">
            {{ $slot }}
        </div>

        @if (isset($footer))
            <div
                class="sticky bottom-0 bg-white/95 backdrop-blur-md border-t border-gray-100 px-6 py-4">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>
