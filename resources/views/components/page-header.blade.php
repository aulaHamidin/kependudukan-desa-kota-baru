@props(['title', 'subtitle' => null, 'actions' => null])

<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
    <div>
        <h1 class="text-xl sm:text-2xl font-bold text-gray-800">{{ $title }}
        </h1>
        @if ($subtitle)
            <p class="text-gray-500 text-sm mt-0.5">{{ $subtitle }}</p>
        @endif
    </div>

    @if ($actions)
        <div class="flex items-center gap-2">
            {{ $actions }}
        </div>
    @endif
</div>
