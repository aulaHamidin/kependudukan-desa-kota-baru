{{-- Data Peristiwa - Approvals Index --}}
<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[['label' => 'Data Peristiwa', 'url' => '#'], ['label' => 'Persetujuan']]" />
    </x-slot>

    <x-slot name="header">
        <x-page-header title="Persetujuan Data Peristiwa" subtitle="Kelola persetujuan data peristiwa penduduk." />
    </x-slot>

    <x-alert />

    {{-- Summary Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.746 3.746 0 011.043 3.296A3.745 3.745 0 0121 12z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-gray-800 mb-1">{{ number_format($stats['total']) }}</p>
            <p class="text-xs text-gray-500">Total Antrian</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-gray-800 mb-1">{{ number_format($stats['kelahiran']) }}</p>
            <p class="text-xs text-gray-500">Kelahiran</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-gray-200 text-gray-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 12H6" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-gray-800 mb-1">{{ number_format($stats['kematian']) }}</p>
            <p class="text-xs text-gray-500">Kematian</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-gray-800 mb-1">{{ number_format($stats['pindah']) }}</p>
            <p class="text-xs text-gray-500">Pindah</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-gray-800 mb-1">{{ number_format($stats['datang']) }}</p>
            <p class="text-xs text-gray-500">Datang</p>
        </div>
    </div>

    {{-- Filter Tabs & Card List --}}
    <div x-data="{ activeFilter: 'semua', searchQuery: '' }" class="space-y-4">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
            {{-- Toolbar --}}
            <div
                class="px-5 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                <div class="flex items-center gap-2 flex-wrap">
                    <button @click="activeFilter = 'semua'"
                        :class="activeFilter === 'semua' ? 'bg-indigo-600 text-white shadow-sm' :
                            'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all">
                        Semua <span class="ml-1 opacity-75">({{ $stats['total'] }})</span>
                    </button>
                    <button @click="activeFilter = 'KELAHIRAN'"
                        :class="activeFilter === 'KELAHIRAN' ? 'bg-emerald-600 text-white shadow-sm' :
                            'bg-emerald-50 text-emerald-700 hover:bg-emerald-100'"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all">
                        Kelahiran <span class="ml-1 opacity-75">({{ $stats['kelahiran'] }})</span>
                    </button>
                    <button @click="activeFilter = 'KEMATIAN'"
                        :class="activeFilter === 'KEMATIAN' ? 'bg-gray-600 text-white shadow-sm' :
                            'bg-gray-100 text-gray-700 hover:bg-gray-200'"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all">
                        Kematian <span class="ml-1 opacity-75">({{ $stats['kematian'] }})</span>
                    </button>
                    <button @click="activeFilter = 'PINDAH'"
                        :class="activeFilter === 'PINDAH' ? 'bg-amber-600 text-white shadow-sm' :
                            'bg-amber-50 text-amber-700 hover:bg-amber-100'"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all">
                        Pindah <span class="ml-1 opacity-75">({{ $stats['pindah'] }})</span>
                    </button>
                    <button @click="activeFilter = 'DATANG'"
                        :class="activeFilter === 'DATANG' ? 'bg-blue-600 text-white shadow-sm' :
                            'bg-blue-50 text-blue-700 hover:bg-blue-100'"
                        class="px-3 py-1.5 rounded-lg text-xs font-semibold transition-all">
                        Datang <span class="ml-1 opacity-75">({{ $stats['datang'] }})</span>
                    </button>
                </div>
                <div class="relative">
                    <input type="text" x-model="searchQuery" placeholder="Cari nama penduduk..."
                        class="w-full sm:w-56 pl-9 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-400 transition-all" />
                    <svg class="w-4 h-4 text-gray-400 absolute left-3 top-1/2 -translate-y-1/2" fill="none"
                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </div>
            </div>

            {{-- Card List --}}
            <div class="p-4 sm:p-5 space-y-4">
                @forelse ($pendingEvents as $event)
                    @php
                        $eventColors = [
                            'KELAHIRAN' => [
                                'bg' => 'bg-emerald-50',
                                'text' => 'text-emerald-600',
                                'border' => 'border-emerald-200',
                                'gradient' => 'from-emerald-500 to-emerald-600',
                                'light' => 'bg-emerald-100',
                            ],
                            'KEMATIAN' => [
                                'bg' => 'bg-gray-50',
                                'text' => 'text-gray-600',
                                'border' => 'border-gray-200',
                                'gradient' => 'from-gray-500 to-gray-600',
                                'light' => 'bg-gray-200',
                            ],
                            'PINDAH' => [
                                'bg' => 'bg-amber-50',
                                'text' => 'text-amber-600',
                                'border' => 'border-amber-200',
                                'gradient' => 'from-amber-500 to-amber-600',
                                'light' => 'bg-amber-100',
                            ],
                            'DATANG' => [
                                'bg' => 'bg-blue-50',
                                'text' => 'text-blue-600',
                                'border' => 'border-blue-200',
                                'gradient' => 'from-blue-500 to-blue-600',
                                'light' => 'bg-blue-100',
                            ],
                        ];
                        $colors = $eventColors[$event->event_type_code] ?? [
                            'bg' => 'bg-gray-50',
                            'text' => 'text-gray-600',
                            'border' => 'border-gray-200',
                            'gradient' => 'from-gray-500 to-gray-600',
                            'light' => 'bg-gray-200',
                        ];

                        $eventIcons = [
                            'KELAHIRAN' =>
                                '<path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />',
                            'KEMATIAN' => '<path stroke-linecap="round" stroke-linejoin="round" d="M18 12H6" />',
                            'PINDAH' =>
                                '<path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" />',
                            'DATANG' =>
                                '<path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" />',
                        ];
                        $iconPath =
                            $eventIcons[$event->event_type_code] ??
                            '<path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />';
                    @endphp

                    <div x-show="(activeFilter === 'semua' || activeFilter === '{{ $event->event_type_code }}') && (searchQuery === '' || '{{ strtolower($event->penduduk?->nama_lengkap ?? '') }}'.includes(searchQuery.toLowerCase()))"
                        x-transition:enter="transition ease-out duration-200"
                        x-transition:enter-start="opacity-0 translate-y-1"
                        x-transition:enter-end="opacity-100 translate-y-0"
                        class="group relative bg-white rounded-xl border {{ $colors['border'] }} hover:border-indigo-300 hover:shadow-lg transition-all duration-200 overflow-hidden">

                        {{-- Color accent bar --}}
                        <div class="absolute left-0 top-0 bottom-0 w-1 bg-gradient-to-b {{ $colors['gradient'] }}">
                        </div>

                        <div class="pl-5 pr-4 py-4">
                            <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                                {{-- Left: Event Info --}}
                                <div class="flex items-start gap-4 flex-1 min-w-0">
                                    {{-- Event Type Icon --}}
                                    <div
                                        class="w-12 h-12 rounded-xl bg-gradient-to-br {{ $colors['gradient'] }} text-white flex items-center justify-center shrink-0 shadow-md group-hover:scale-110 transition-transform">
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            {!! $iconPath !!}
                                        </svg>
                                    </div>

                                    {{-- Details --}}
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 flex-wrap mb-2">
                                            <h3 class="text-base font-bold text-gray-800">
                                                {{ $event->penduduk?->nama_lengkap ?? '-' }}
                                            </h3>
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide {{ $colors['bg'] }} {{ $colors['text'] }} border {{ $colors['border'] }}">
                                                {{ $event->eventType->nama ?? $event->event_type_code }}
                                            </span>
                                            @include('data_peristiwa.partials.status-badge', [
                                                'status' => $event->status_data,
                                            ])
                                        </div>

                                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 mb-3">
                                            {{-- Tanggal Event --}}
                                            <div class="flex items-center gap-2 text-sm">
                                                <div
                                                    class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                                                    <svg class="w-4 h-4 text-amber-600" fill="none"
                                                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                                    </svg>
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="text-[10px] text-gray-400 uppercase font-semibold">
                                                        Tanggal</p>
                                                    <p class="text-xs text-gray-700 font-semibold">
                                                        {{ $event->event_date?->format('d M Y') ?? '-' }}</p>
                                                </div>
                                            </div>

                                            {{-- Wilayah --}}
                                            <div class="flex items-center gap-2 text-sm">
                                                <div
                                                    class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                                                    <svg class="w-4 h-4 text-blue-600" fill="none"
                                                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                                    </svg>
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="text-[10px] text-gray-400 uppercase font-semibold">
                                                        Wilayah</p>
                                                    <p class="text-xs text-gray-700 font-semibold truncate">
                                                        RT {{ $event->rt?->nomor_rt ?? '-' }} / RW
                                                        {{ $event->rt?->rw?->nomor_rw ?? '-' }}</p>
                                                </div>
                                            </div>

                                            {{-- Dibuat Oleh --}}
                                            <div class="flex items-center gap-2 text-sm">
                                                <div
                                                    class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center shrink-0">
                                                    <svg class="w-4 h-4 text-gray-500" fill="none"
                                                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                                    </svg>
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="text-[10px] text-gray-400 uppercase font-semibold">Dibuat
                                                    </p>
                                                    <p class="text-xs text-gray-700 font-semibold truncate">
                                                        {{ $event->createdBy?->name ?? '-' }}</p>
                                                </div>
                                            </div>

                                            {{-- Waktu Masuk --}}
                                            <div class="flex items-center gap-2 text-sm">
                                                <div
                                                    class="w-8 h-8 rounded-lg bg-purple-100 flex items-center justify-center shrink-0">
                                                    <svg class="w-4 h-4 text-purple-600" fill="none"
                                                        stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round"
                                                            d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                    </svg>
                                                </div>
                                                <div class="min-w-0">
                                                    <p class="text-[10px] text-gray-400 uppercase font-semibold">Waktu
                                                    </p>
                                                    <p class="text-xs text-gray-700 font-semibold truncate">
                                                        {{ $event->created_at->diffForHumans() }}</p>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Keterangan jika ada --}}
                                        @if ($event->keterangan)
                                            <p
                                                class="mt-2 text-xs text-gray-500 bg-gray-50 rounded-lg px-3 py-2 border border-gray-100">
                                                <span class="font-medium text-gray-600">Keterangan:</span>
                                                {{ Str::limit($event->keterangan, 120) }}
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                {{-- Right: Action Buttons --}}
                                <div class="flex items-center gap-2 lg:shrink-0 lg:ml-4">
                                    {{-- Detail Button --}}
                                    <a href="{{ route('events.' . strtolower($event->event_type_code) . '.show', $event) }}"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-indigo-50 text-indigo-700 hover:bg-indigo-600 hover:text-white border border-indigo-200 hover:border-indigo-600 rounded-lg text-xs font-semibold transition-all duration-200 shadow-sm hover:shadow">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                        </svg>
                                        Detail
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    {{-- Empty State --}}
                    <div class="py-16 text-center">
                        <div class="w-20 h-20 rounded-2xl bg-emerald-50 flex items-center justify-center mx-auto mb-5">
                            <svg class="w-10 h-10 text-emerald-400" fill="none" stroke="currentColor"
                                stroke-width="1.5" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <h3 class="text-base font-bold text-gray-700 mb-1">Semua Data Sudah Diproses!</h3>
                        <p class="text-sm text-gray-400 max-w-sm mx-auto">Tidak ada data peristiwa yang menunggu
                            persetujuan saat ini. Data baru akan muncul di sini.</p>
                    </div>
                @endforelse
            </div>

            {{-- Pagination --}}
            @if ($pendingEvents->hasPages())
                <div class="px-5 py-4 border-t border-gray-100">
                    {{ $pendingEvents->links() }}
                </div>
            @endif
        </div>
    </div>

</x-app-layout>
