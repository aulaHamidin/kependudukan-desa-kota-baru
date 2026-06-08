@props(['id', 'title', 'icon' => null, 'active' => false])

<div x-data="{ open: {{ $active ? 'true' : 'false' }} }">
    {{-- Dropdown Toggle Button --}}
    <button @click="open = !open"
        class="w-full flex items-center gap-3 px-4 py-2 rounded-md text-[0.8125rem] font-medium transition-colors duration-150 {{ $active ? 'text-white bg-white/20' : 'text-blue-100 hover:bg-white/15 hover:text-white' }}">
        @if ($icon)
            <x-sidebar-icon :name="$icon" :active="$active" />
        @endif

        <span class="{{ $active ? 'font-semibold' : '' }}">{{ $title }}</span>

        {{-- Chevron Icon --}}
        <svg class="w-4 h-4 ml-auto transition-transform duration-200" :class="{ 'rotate-180': open }" fill="none"
            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
        </svg>
    </button>

    {{-- Dropdown Content --}}
    <div x-show="open" x-collapse x-cloak class="mt-0.5 space-y-0.5">
        {{ $slot }}
    </div>
</div>
