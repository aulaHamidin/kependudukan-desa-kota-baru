@props([
    'title' => 'Tidak ada data',
    'description' => 'Belum ada data yang tersedia.',
    'icon' => 'empty',
])

<div {{ $attributes->merge(['class' => 'py-12 text-center']) }}>
    <div class="w-16 h-16 rounded-lg bg-gray-100 flex items-center justify-center mx-auto mb-4">
        @if ($icon === 'empty')
            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z" />
            </svg>
        @elseif($icon === 'search')
            <svg class="w-8 h-8 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5"
                viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M15.182 16.318A4.486 4.486 0 0012.016 15a4.486 4.486 0 00-3.198 1.318M21 12a9 9 0 11-18 0 9 9 0 0118 0zM9.75 9.75c0 .414-.168.75-.375.75S9 10.164 9 9.75 9.168 9 9.375 9s.375.336.375.75zm-.375 0h.008v.015h-.008V9.75zm5.625 0c0 .414-.168.75-.375.75s-.375-.336-.375-.75.168-.75.375-.75.375.336.375.75zm-.375 0h.008v.015h-.008V9.75z" />
            </svg>
        @endif
    </div>
    <p class="text-sm font-semibold text-gray-400">{{ $title }}</p>
    <p class="text-xs text-gray-300 mt-1">{{ $description }}</p>

    @if (isset($action))
        <div class="mt-4">
            {{ $action }}
        </div>
    @endif
</div>
