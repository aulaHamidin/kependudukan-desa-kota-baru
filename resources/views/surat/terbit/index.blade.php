{{-- Surat - Terbit Index --}}
<x-app-layout>
    <x-slot name="title">Daftar Surat Terbit</x-slot>

    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[['label' => 'Surat', 'url' => '#'], ['label' => 'Terbit']]" />
    </x-slot>
    <x-slot name="header">
        <x-page-header title="Surat Terbit" subtitle="Kelola daftar persuratan yang diterbitkan oleh desa.">
            <x-slot name="actions">
                @can('create', \App\Models\SuratTerbit::class)
                    <x-button variant="primary" icon="plus" :href="route('surat.terbit.create')">Terbitkan Surat</x-button>
                @endcan
            </x-slot>
        </x-page-header>
    </x-slot>

    <x-alert />

    {{-- Summary Stats --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-gray-800 mb-1">
                {{ number_format(($stats['aktif'] ?? 0) + ($stats['batal'] ?? 0)) }}</p>
            <p class="text-xs text-gray-500">Total Surat</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-gray-800 mb-1">{{ number_format($stats['aktif'] ?? 0) }}</p>
            <p class="text-xs text-gray-500">Surat Aktif</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-rose-100 text-rose-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-gray-800 mb-1">{{ number_format($stats['batal'] ?? 0) }}</p>
            <p class="text-xs text-gray-500">Dibatalkan</p>
        </div>

        <div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5">
            <div class="flex items-center justify-between mb-3">
                <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5m-9-6h.008v.008H12v-.008zM12 15h.008v.008H12V15zm0 2.25h.008v.008H12v-.008zM9.75 15h.008v.008H9.75V15zm0 2.25h.008v.008H9.75v-.008zM7.5 15h.008v.008H7.5V15zm0 2.25h.008v.008H7.5v-.008zm6.75-4.5h.008v.008h-.008v-.008zm0 2.25h.008v.008h-.008V15zm0 2.25h.008v.008h-.008v-.008zm2.25-4.5h.008v.008H16.5v-.008zm0 2.25h.008v.008H16.5V15z" />
                    </svg>
                </div>
            </div>
            <p class="text-2xl font-extrabold text-gray-800 mb-1">{{ number_format($surats->total()) }}</p>
            <p class="text-xs text-gray-500">Ditampilkan</p>
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
                <h3 class="text-sm font-semibold text-gray-700">Filter Surat</h3>
            </div>

            <form method="GET" action="{{ route('surat.terbit.index') }}">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Cari</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="No. Surat / Pemohon..." class="form-input-custom w-full text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jenis Surat</label>
                        <select name="jenis_surat_kode" class="form-select-custom w-full text-sm">
                            <option value="">Semua Jenis</option>
                            @foreach ($jenisSuratOptions as $kode => $nama)
                                <option value="{{ $kode }}" @selected(request('jenis_surat_kode') === $kode)>{{ $nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Status</label>
                        <select name="status" class="form-select-custom w-full text-sm">
                            <option value="">Semua Status</option>
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Dari Tanggal</label>
                        <x-form-date name="tanggal_dari" label="" :value="request('tanggal_dari')" class="w-full text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Sampai Tanggal</label>
                        <x-form-date name="tanggal_sampai" label="" :value="request('tanggal_sampai')" class="w-full text-sm" />
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
                    @if (request()->anyFilled(['search', 'jenis_surat_kode', 'status', 'tanggal_dari', 'tanggal_sampai']))
                        <a href="{{ route('surat.terbit.index') }}"
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
        if (request('jenis_surat_kode') && isset($jenisSuratOptions[request('jenis_surat_kode')])) {
            $activeFilters[] = ['label' => 'Jenis', 'value' => $jenisSuratOptions[request('jenis_surat_kode')]];
        }
        if (request('status') && isset($statusOptions[request('status')])) {
            $activeFilters[] = ['label' => 'Status', 'value' => $statusOptions[request('status')]];
        }
        if (request('tanggal_dari')) {
            $activeFilters[] = ['label' => 'Dari', 'value' => request('tanggal_dari')];
        }
        if (request('tanggal_sampai')) {
            $activeFilters[] = ['label' => 'Sampai', 'value' => request('tanggal_sampai')];
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
            <a href="{{ route('surat.terbit.index') }}"
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
        @forelse ($surats as $item)
            @php
                $isAktif = $item->status === 'AKTIF';
                $statusColor = $isAktif
                    ? [
                        'bg' => 'bg-emerald-50',
                        'text' => 'text-emerald-700',
                        'border' => 'border-emerald-200',
                        'gradient' => 'from-emerald-500 to-emerald-600',
                        'dot' => 'bg-emerald-500',
                    ]
                    : [
                        'bg' => 'bg-red-50',
                        'text' => 'text-red-700',
                        'border' => 'border-red-200',
                        'gradient' => 'from-red-400 to-red-500',
                        'dot' => 'bg-red-500',
                    ];
            @endphp

            <div
                class="group bg-white rounded-xl border border-gray-200 hover:border-amber-300 hover:shadow-lg transition-all duration-200 overflow-hidden">
                <div class="p-5">
                    <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                        {{-- Left: Surat Info --}}
                        <div class="flex items-start gap-4 flex-1 min-w-0">
                            {{-- Document Icon --}}
                            <div
                                class="w-12 h-12 rounded-xl {{ $isAktif ? 'bg-gradient-to-br from-amber-500 to-orange-600' : 'bg-gradient-to-br from-gray-400 to-gray-500' }} text-white flex items-center justify-center shrink-0 shadow-md group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                            </div>

                            {{-- Details --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap mb-2">
                                    <h3 class="text-base font-bold text-gray-800 font-mono">
                                        {{ $item->nomor_surat }}
                                    </h3>
                                    <span
                                        class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide {{ $statusColor['bg'] }} {{ $statusColor['text'] }} border {{ $statusColor['border'] }}">
                                        <span class="w-1.5 h-1.5 rounded-full {{ $statusColor['dot'] }}"></span>
                                        {{ $isAktif ? 'Aktif' : 'Dibatalkan' }}
                                    </span>
                                    @if ($item->pdf_status === 'READY' && $item->file_path)
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide bg-blue-100 text-blue-700 border border-blue-200">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                            </svg>
                                            PDF
                                        </span>
                                    @elseif ($item->pdf_status === 'PROCESSING')
                                        <span
                                            class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wide bg-yellow-100 text-yellow-700 border border-yellow-200">
                                            <svg class="w-3 h-3 animate-spin" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182" />
                                            </svg>
                                            Proses
                                        </span>
                                    @endif
                                </div>

                                <p class="text-sm text-gray-700 font-semibold mb-3">
                                    {{ $item->jenisSurat->nama ?? $item->jenis_surat_kode }}
                                </p>

                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                                    {{-- Pemohon --}}
                                    <div class="flex items-center gap-2 text-sm">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-[10px] text-gray-400 uppercase font-semibold">Pemohon</p>
                                            <p class="text-xs text-gray-700 font-semibold truncate">
                                                {{ $item->penduduk->nama_lengkap ?? 'Unknown' }}</p>
                                        </div>
                                    </div>

                                    {{-- NIK --}}
                                    <div class="flex items-center gap-2 text-sm">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-blue-100 flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 002.25-2.25V6.75A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25v10.5A2.25 2.25 0 004.5 19.5z" />
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-[10px] text-gray-400 uppercase font-semibold">NIK</p>
                                            <p class="font-mono text-xs text-gray-700 font-semibold truncate">
                                                {{ \App\Support\Masking::nik($item->penduduk->nik ?? '') }}</p>
                                        </div>
                                    </div>

                                    {{-- Tanggal --}}
                                    <div class="flex items-center gap-2 text-sm">
                                        <div
                                            class="w-8 h-8 rounded-lg bg-amber-100 flex items-center justify-center shrink-0">
                                            <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor"
                                                stroke-width="2" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round"
                                                    d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                            </svg>
                                        </div>
                                        <div class="min-w-0">
                                            <p class="text-[10px] text-gray-400 uppercase font-semibold">Terbit</p>
                                            <p class="text-xs text-gray-700 font-semibold">
                                                {{ $item->tanggal_terbit->format('d M Y') }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Right: Action Buttons --}}
                        <div class="flex flex-wrap items-center gap-2 lg:shrink-0 lg:ml-4">
                            @can('view', $item)
                                <a href="{{ route('surat.terbit.show', $item) }}"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 bg-gray-50 text-gray-700 hover:bg-indigo-600 hover:text-white border border-gray-200 hover:border-indigo-600 rounded-lg text-xs font-semibold transition-all duration-200 shadow-sm hover:shadow">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                        <path stroke-linecap="round" stroke-linejoin="round"
                                            d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    Detail
                                </a>
                            @endcan

                            @can('download', $item)
                                @if ($item->status === 'AKTIF' && $item->pdf_status === 'READY' && $item->file_path)
                                    <a href="{{ route('surat.terbit.download', $item) }}"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-blue-50 text-blue-700 hover:bg-blue-600 hover:text-white border border-blue-200 hover:border-blue-600 rounded-lg text-xs font-semibold transition-all duration-200 shadow-sm hover:shadow">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                                        </svg>
                                        Unduh
                                    </a>
                                @endif
                            @endcan

                            @can('batalkan', $item)
                                @if ($item->status === 'AKTIF')
                                    <a href="{{ route('surat.terbit.batalkan.form', $item) }}"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 bg-red-50 text-red-700 hover:bg-red-600 hover:text-white border border-red-200 hover:border-red-600 rounded-lg text-xs font-semibold transition-all duration-200 shadow-sm hover:shadow">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round"
                                                d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                        </svg>
                                        Batalkan
                                    </a>
                                @endif
                            @endcan
                        </div>
                    </div>
                </div>
            </div>
        @empty
            {{-- Empty State --}}
            <div class="bg-white rounded-xl border border-gray-200 shadow-sm">
                <div class="py-16 text-center">
                    <div class="w-20 h-20 rounded-2xl bg-amber-50 flex items-center justify-center mx-auto mb-5">
                        <svg class="w-10 h-10 text-amber-400" fill="none" stroke="currentColor"
                            stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                    </div>
                    <h3 class="text-base font-bold text-gray-700 mb-1">Belum Ada Surat</h3>
                    <p class="text-sm text-gray-400 max-w-sm mx-auto">Anda belum menerbitkan surat apapun. Klik tombol
                        "Terbitkan Surat" untuk memulai.</p>
                </div>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if ($surats->hasPages())
        <div class="mt-6">
            {{ $surats->withQueryString()->links() }}
        </div>
    @endif
</x-app-layout>
