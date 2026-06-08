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
                    <h3 class="text-sm font-semibold text-gray-700">Filter Kartu Keluarga</h3>
                </div>
                @can('create', \App\Models\KartuKeluarga::class)
                    <x-button type="button" icon="plus" x-on:click="$dispatch('open-modal', 'create-kk')">
                        Tambah KK
                    </x-button>
                @endcan
            </div>

            <form method="GET" action="{{ route('kartu-keluarga.index') }}">
                <div class="grid grid-cols-1 lg:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Cari</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Cari no KK atau alamat..." class="form-input-custom w-full text-sm" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">RT</label>
                        <select name="rt_id" class="form-select-custom w-full text-sm">
                            <option value="">Semua RT</option>
                            @foreach ($rtOptions as $rtId => $rtLabel)
                                <option value="{{ $rtId }}" @selected((string) $rtId === (string) request('rt_id'))>
                                    {{ $rtLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Status</label>
                        <select name="status_kk" class="form-select-custom w-full text-sm">
                            <option value="">Semua Status</option>
                            @foreach ($statusKkOptions as $statusValue => $statusLabel)
                                <option value="{{ $statusValue }}" @selected($statusValue === request('status_kk'))>
                                    {{ $statusLabel }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-600 mb-1.5">Kepala KK</label>
                        <select name="no_kepala" class="form-select-custom w-full text-sm">
                            <option value="">Semua KK</option>
                            <option value="1" @selected(request('no_kepala') === '1')>Tanpa Kepala KK</option>
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
                    @if (request()->anyFilled(['search', 'status_kk', 'rt_id', 'no_kepala']))
                        <a href="{{ route('kartu-keluarga.index') }}"
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
        if (request('status_kk') && isset($statusKkOptions[request('status_kk')])) {
            $activeFilters[] = ['label' => 'Status', 'value' => $statusKkOptions[request('status_kk')]];
        }
        if (request('no_kepala') === '1') {
            $activeFilters[] = ['label' => 'Filter', 'value' => 'Tanpa Kepala KK'];
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
            <a href="{{ route('kartu-keluarga.index') }}"
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
                    <h3 class="text-lg font-semibold text-gray-800">Daftar Kartu Keluarga</h3>
                    <p class="text-sm text-gray-500">Total {{ $kartuKeluargas->total() }} kartu keluarga</p>
                </div>
            </div>
        </x-slot>

        <x-data-table :datatable="false" :searchable="false">
            <x-slot name="head">
                <tr>
                    <x-table-header>No. KK</x-table-header>
                    <x-table-header>Alamat</x-table-header>
                    <x-table-header>Wilayah</x-table-header>
                    <x-table-header>Status</x-table-header>
                    <x-table-header>Anggota</x-table-header>
                    <x-table-header class="text-center">Aksi</x-table-header>
                </tr>
            </x-slot>

            @forelse ($kartuKeluargas as $item)
                @php
                    $editModal = 'edit-kk-' . $item->id;
                @endphp
                <tr class="table-row-hover">
                    <x-table-cell>{{ \App\Support\Masking::nik($item->no_kk) }}</x-table-cell>
                    <x-table-cell>{{ $item->alamat }}</x-table-cell>
                    <x-table-cell>
                        RT {{ $item->rt?->nomor_rt ?? '-' }} / RW {{ $item->rt?->rw?->nomor_rw ?? '-' }}
                    </x-table-cell>
                    <x-table-cell>
                        @if ($item->status_kk === 'AKTIF')
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Aktif
                            </span>
                        @else
                            <span
                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-gray-50 text-gray-700 border border-gray-200">
                                <svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" stroke-width="2"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636" />
                                </svg>
                                Nonaktif
                            </span>
                        @endif
                    </x-table-cell>
                    <x-table-cell>{{ $item->active_members_count ?? 0 }}</x-table-cell>
                    <x-table-cell class="text-center">
                        <div class="flex items-center justify-center gap-1">
                            @can('view', $item)
                                <a href="{{ route('kartu-keluarga.show', $item) }}" class="btn-action btn-action-view"
                                    title="Detail">
                                    <x-button-icon icon="eye" class="w-4 h-4" />
                                </a>
                            @endcan
                            @can('update', $item)
                                <a href="{{ route('kartu-keluarga.edit', $item) }}" class="btn-action btn-action-edit"
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
                        <x-empty-state title="Belum Ada Kartu Keluarga"
                            description="Belum ada data kartu keluarga yang ditampilkan." icon="empty" />
                    </td>
                </tr>
            @endforelse

            @if ($kartuKeluargas->hasPages())
                <x-slot name="footer">
                    {{ $kartuKeluargas->withQueryString()->links() }}
                </x-slot>
            @endif
        </x-data-table>
    </x-card>
</div>
