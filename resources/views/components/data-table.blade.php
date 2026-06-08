{{--
    Data Table Component
    Wrapper for consistent table styling
--}}

@props([
    'searchable' => true,
    'searchPlaceholder' => 'Cari data...',
    'datatable' => false,
    'datatableOptions' => null,
])

<div class="card">
    {{-- Table Toolbar (server-side only) --}}
    @if (!$datatable && ($searchable || isset($toolbar)))
        <x-data-table.toolbar :searchable="$searchable" :searchPlaceholder="$searchPlaceholder">
            @isset($toolbar)
                {{ $toolbar }}
            @endisset
        </x-data-table.toolbar>
    @endif

    {{-- Filter Toolbar (client-side datatable) --}}
    @if ($datatable && isset($filters))
        <x-data-table.filters>
            {{ $filters }}
        </x-data-table.filters>
    @endif

    {{-- Table Content --}}
    <div class="overflow-x-auto">
        <table {{ $attributes->merge(['class' => 'w-full text-left table-auto']) }}
            @if ($datatable) data-datatable @endif
            @if ($datatableOptions) data-datatable-options='@json($datatableOptions)' @endif>
            @if (isset($head))
                <thead>
                    <tr class="bg-gray-100">
                        {{ $head }}
                    </tr>
                </thead>
            @endif

            <tbody class="divide-y divide-gray-200">
                {{ $slot }}
            </tbody>
        </table>
    </div>

    {{-- Pagination / Footer --}}
    @if (isset($footer))
        <div class="px-5 sm:px-6 py-4 border-t border-gray-100">
            {{ $footer }}
        </div>
    @endif
</div>
