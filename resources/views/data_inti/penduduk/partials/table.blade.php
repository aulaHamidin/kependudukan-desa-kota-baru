<div x-data>
    {{-- Filter Section --}}
    <x-card class="mb-6">
        <div class="p-5">
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
                        </svg>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-700">Filter Penduduk</h3>
                </div>
                @can('create', \App\Models\Penduduk::class)
                    <x-button type="button" icon="plus" x-on:click="$dispatch('open-modal', 'create-penduduk')">
                        Tambah Penduduk
                    </x-button>
                @endcan
            </div>

            <form method="GET" action="{{ route('penduduk.index') }}">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Cari</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari nama atau NIK..." class="form-input-custom w-full text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Status</label>
                        <select name="status_kependudukan_code" class="form-select-custom w-full text-sm">
                            <option value="">Semua Status</option>
                            @foreach ($statusOptions as $value => $label)
                                <option value="{{ $value }}" @selected(request('status_kependudukan_code') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Jenis Kelamin</label>
                        <select name="jenis_kelamin" class="form-select-custom w-full text-sm">
                            <option value="">Semua JK</option>
                            <option value="L" @selected(request('jenis_kelamin') === 'L')>Laki-laki</option>
                            <option value="P" @selected(request('jenis_kelamin') === 'P')>Perempuan</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">RT</label>
                        <select name="rt_id" class="form-select-custom w-full text-sm">
                            <option value="">Semua RT</option>
                            @foreach ($rtOptions as $value => $label)
                                <option value="{{ $value }}" @selected((string) request('rt_id') === (string) $value)>
                                    {{ $label }}
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
                    @if (request()->anyFilled(['search', 'status_kependudukan_code', 'jenis_kelamin', 'rt_id']))
                        <a href="{{ route('penduduk.index') }}"
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
        if (request('status_kependudukan_code') && isset($statusOptions[request('status_kependudukan_code')])) {
            $activeFilters[] = ['label' => 'Status', 'value' => $statusOptions[request('status_kependudukan_code')]];
        }
        if (request('jenis_kelamin')) {
            $jkLabel = request('jenis_kelamin') === 'L' ? 'Laki-laki' : 'Perempuan';
            $activeFilters[] = ['label' => 'Jenis Kelamin', 'value' => $jkLabel];
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
            <a href="{{ route('penduduk.index') }}"
                class="inline-flex items-center gap-1 text-xs font-semibold text-red-600 hover:text-red-700 hover:underline">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
                Hapus Semua
            </a>
        </div>
    @endif

    {{-- Table Section --}}
    <x-card :padding="false">
        <x-slot name="header">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Daftar Penduduk</h3>
                    <p class="text-sm text-gray-500">Total {{ $penduduks->total() }} penduduk</p>
                </div>
            </div>
        </x-slot>

        <x-data-table :datatable="false" :searchable="false">
            <x-slot name="head">
                <tr>
                    <x-table-header>NIK</x-table-header>
                    <x-table-header>Nama</x-table-header>
                    <x-table-header>JK</x-table-header>
                    <x-table-header>Wilayah</x-table-header>
                    <x-table-header>Status</x-table-header>
                    <x-table-header class="text-center">Aksi</x-table-header>
                </tr>
            </x-slot>

            @forelse ($penduduks as $item)
                @php
                    $editModal = 'edit-penduduk-' . $item->id;
                    $mutasiModal = 'mutasi-penduduk-' . $item->id;
                @endphp
                <tr class="table-row-hover">
                    <x-table-cell>{{ \App\Support\Masking::nik($item->nik) }}</x-table-cell>
                    <x-table-cell class="font-medium text-gray-900">
                        {{ $item->nama_lengkap }}
                    </x-table-cell>
                    <x-table-cell>
                        <span class="badge {{ $item->jenis_kelamin === 'L' ? 'badge-laki' : 'badge-perempuan' }}">
                            {{ $item->jenis_kelamin === 'L' ? 'Laki-laki' : 'Perempuan' }}
                        </span>
                    </x-table-cell>
                    <x-table-cell>
                        @if ($item->rt)
                            RT {{ $item->rt->nomor_rt }} / RW {{ $item->rt->rw?->nomor_rw ?? '-' }}
                        @else
                            -
                        @endif
                    </x-table-cell>
                    <x-table-cell>
                        @php
                            $statusCode = $item->status_kependudukan_code;
                            $statusLabel = $item->statusKependudukan?->nama ?? $statusCode;
                            $badgeClass = match ($statusCode) {
                                'AKTIF' => 'badge-aktif',
                                'PINDAH' => 'badge-pindah',
                                'MENINGGAL' => 'badge-meninggal',
                                default => 'badge-pending',
                            };
                        @endphp
                        <span class="badge {{ $badgeClass }}">{{ $statusLabel }}</span>
                    </x-table-cell>
                    <x-table-cell class="text-center">
                        <div class="flex items-center justify-center gap-1">
                            @can('view', $item)
                                <a href="{{ route('penduduk.show', $item) }}" class="btn-action btn-action-view"
                                    title="Detail">
                                    <x-button-icon icon="eye" class="w-4 h-4" />
                                </a>
                            @endcan
                            @can('update', $item)
                                <a href="{{ route('penduduk.edit', $item) }}" class="btn-action btn-action-edit"
                                    title="Edit">
                                    <x-button-icon icon="edit" class="w-4 h-4" />
                                </a>
                            @endcan
                        </div>
                    </x-table-cell>
                </tr>
            @empty
                <tr>
                    <td colspan="6">
                        <x-empty-state title="Belum Ada Penduduk"
                            description="Belum ada data penduduk yang ditampilkan." icon="empty" />
                    </td>
                </tr>
            @endforelse

            @if ($penduduks->hasPages())
                <x-slot name="footer">
                    {{ $penduduks->withQueryString()->links() }}
                </x-slot>
            @endif
        </x-data-table>
    </x-card>
</div>
