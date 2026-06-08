{{-- Pindah RT - Index --}}
<x-app-layout>
    <x-slot name="title">Pindah RT</x-slot>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[['label' => 'Administrasi', 'url' => '#'], ['label' => 'Pindah RT']]" />
    </x-slot>
    <x-slot name="header">
        <x-page-header title="Pindah RT" subtitle="Pindahkan kartu keluarga ke RT lain dalam desa yang sama.">
        </x-page-header>
    </x-slot>

    <x-alert />

    {{-- Summary Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-teal-100 text-teal-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-gray-800 mb-1">{{ number_format($totalKK) }}</p>
            <p class="text-xs text-gray-500">Total KK Aktif</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-gray-800 mb-1">{{ number_format($totalRT) }}</p>
            <p class="text-xs text-gray-500">Jumlah RT</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-gray-800 mb-1">{{ number_format($kartuKeluargas->total()) }}</p>
            <p class="text-xs text-gray-500">KK Ditampilkan</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-gray-800 mb-1">{{ number_format($totalAnggota) }}</p>
            <p class="text-xs text-gray-500">Total Anggota</p>
        </div>
    </div>

    {{-- Filter Section --}}
    <x-card class="mb-6">
        <div class="p-5">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                    </svg>
                </div>
                <h3 class="text-sm font-semibold text-gray-700">Filter Kartu Keluarga</h3>
            </div>

            <form method="GET">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <div class="lg:col-span-2">
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Cari</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari no KK, nama kepala keluarga, atau alamat..."
                            class="form-input-custom w-full text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">RT</label>
                        <select name="rt_id" class="form-select-custom w-full text-sm">
                            <option value="">Semua RT</option>
                            @foreach ($rtOptions as $rtId => $rtLabel)
                                <option value="{{ $rtId }}" @selected((string) $rtId === (string) request('rt_id'))>{{ $rtLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="flex gap-2 mt-4">
                    <x-button type="submit" variant="primary" class="px-6">
                        <svg class="w-4 h-4 mr-1.5 inline" fill="none" stroke="currentColor" stroke-width="2"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                        </svg>
                        Terapkan Filter
                    </x-button>
                    @if (request('search') || request('rt_id'))
                        <a href="{{ route('pindah-rt.index') }}"
                            class="inline-flex items-center px-4 py-2 border border-gray-300 rounded-lg text-sm font-semibold text-gray-700 bg-white hover:bg-gray-50 transition-colors">
                            <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" stroke-width="2"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Reset Filter
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </x-card>

    {{-- Active Filters --}}
    @php
        $activeFilters = [];
        if (request('search')) {
            $activeFilters[] = ['label' => 'Pencarian', 'value' => request('search')];
        }
        if (request('rt_id') && isset($rtOptions[request('rt_id')])) {
            $activeFilters[] = ['label' => 'RT', 'value' => $rtOptions[request('rt_id')]];
        }
    @endphp
    @if (count($activeFilters) > 0)
        <div class="flex flex-wrap items-center gap-2 mb-6">
            <span class="text-xs font-semibold text-gray-500">Filter Aktif:</span>
            @foreach ($activeFilters as $filter)
                <span
                    class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    {{ $filter['label'] }}: <span class="font-bold">{{ Str::limit($filter['value'], 30) }}</span>
                </span>
            @endforeach
            <a href="{{ route('pindah-rt.index') }}"
                class="inline-flex items-center gap-1 text-xs font-semibold text-red-600 hover:text-red-700 hover:underline">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Hapus Semua
            </a>
        </div>
    @endif

    {{-- Card List --}}
    <div class="space-y-4">
        @forelse ($kartuKeluargas as $item)
            @php
                $memberCount = $item->kkMembers->count();
                $kepala = $item->kepalaKeluarga?->penduduk?->nama_lengkap ?? '-';
                $initials = collect(explode(' ', $kepala))
                    ->take(2)
                    ->map(fn($w) => strtoupper(substr($w, 0, 1)))
                    ->join('');
            @endphp

            <div
                class="group bg-white rounded-xl border border-gray-200 hover:border-teal-300 hover:shadow-lg transition-all duration-200 overflow-hidden">
                <div class="p-5">
                    <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                        {{-- Left: KK Info --}}
                        <div class="flex items-start gap-4 flex-1 min-w-0">
                            {{-- Avatar Initials --}}
                            <div
                                class="w-12 h-12 rounded-xl bg-gradient-to-br from-teal-500 to-emerald-600 text-white flex items-center justify-center shrink-0 shadow-md font-bold text-base group-hover:scale-110 transition-transform">
                                {{ $initials }}
                            </div>

                            {{-- Details --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap mb-2">
                                    <h3 class="text-base font-bold text-gray-800">
                                        {{ $kepala }}
                                    </h3>
                                    <span
                                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide bg-teal-100 text-teal-700 border border-teal-200">
                                        Kepala Keluarga
                                    </span>
                                </div>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 mb-3">
                                    {{-- No KK --}}
                                    <div class="flex items-center gap-2 text-sm">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-[10px] text-gray-400 uppercase font-semibold">No KK</p>
                                            <p class="font-mono text-xs text-gray-700 font-semibold truncate">
                                                {{ \App\Support\Masking::nik($item->no_kk) }}</p>
                                        </div>
                                    </div>

                                    {{-- Wilayah --}}
                                    <div class="flex items-center gap-2 text-sm">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-emerald-100 flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4 text-emerald-600" fill="none"
                                                stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-[10px] text-gray-400 uppercase font-semibold">Wilayah</p>
                                            <p class="text-xs text-gray-700 font-semibold">RT
                                                {{ $item->rt?->nomor_rt ?? '-' }} / RW
                                                {{ $item->rt?->rw?->nomor_rw ?? '-' }}</p>
                                        </div>
                                    </div>

                                    {{-- Jumlah Anggota --}}
                                    <div class="flex items-center gap-2 text-sm">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-[10px] text-gray-400 uppercase font-semibold">Anggota</p>
                                            <p class="text-xs text-gray-700 font-semibold">{{ $memberCount }} Orang
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                {{-- Alamat --}}
                                @if ($item->alamat)
                                    <div
                                        class="flex items-start gap-2 p-3 bg-gray-50 rounded-lg border border-gray-100">
                                        <svg class="w-4 h-4 text-gray-400 mt-0.5 shrink-0" fill="none"
                                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                                        </svg>
                                        <div class="flex-1 min-w-0">
                                            <p class="text-[10px] text-gray-400 uppercase font-semibold mb-0.5">Alamat
                                            </p>
                                            <p class="text-xs text-gray-700">{{ $item->alamat }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Right: Action Button --}}
                        <div class="flex items-center gap-2 lg:shrink-0 lg:ml-4">
                            <a href="{{ route('pindah-rt.show', $item) }}"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-teal-500 to-emerald-600 text-white hover:from-teal-600 hover:to-emerald-700 rounded-lg text-sm font-semibold transition-all duration-200 shadow-md hover:shadow-lg group-hover:scale-105">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M7.5 21L3 16.5m0 0L7.5 12M3 16.5h13.5m0-13.5L21 7.5m0 0L16.5 12M21 7.5H7.5" />
                                </svg>
                                Pindah RT
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            {{-- Empty State --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="py-16 text-center">
                    <div class="w-20 h-20 rounded-2xl bg-teal-50 flex items-center justify-center mx-auto mb-5">
                        <svg class="w-10 h-10 text-teal-400" fill="none" stroke="currentColor" stroke-width="1.5"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-gray-700 mb-1">Tidak Ada Data</h3>
                    <p class="text-sm text-gray-400 max-w-sm mx-auto">Tidak ada kartu keluarga yang sesuai dengan
                        filter pencarian Anda.</p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($kartuKeluargas->hasPages())
        <div class="mt-6">
            {{ $kartuKeluargas->withQueryString()->links() }}
        </div>
    @endif
</x-app-layout>
