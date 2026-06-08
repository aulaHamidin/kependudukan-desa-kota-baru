<x-card :padding="false">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Daftar RW</h3>
                <p class="text-sm text-gray-500">Total {{ $rws->count() }} data RW</p>
            </div>
            @can('create', [\App\Models\Rw::class, auth()->user()->desa_id ?? 0])
                <div>
                    <button type="button" class="btn btn-primary" x-on:click="$dispatch('open-modal', 'create-rw')">
                        <x-button-icon icon="plus" />
                        Tambah RW
                    </button>
                </div>
            @endcan
        </div>
    </x-slot>

    @if ($desas->count() > 1)
        <div class="px-6 py-4 border-b border-gray-200 bg-gray-50" x-data="rwTableFilter()" x-init="init()">
            <div class="flex items-center gap-2 mb-3">
                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                </svg>
                <h4 class="text-sm font-semibold text-gray-700">Filter Data</h4>
            </div>

            <div class="flex items-end gap-3">
                <div class="flex-1">
                    <label class="block text-xs font-medium text-gray-700 mb-1.5">Desa</label>
                    <select x-model="filters.desa" class="form-select-custom w-full">
                        <option value="">Semua Desa</option>
                        @foreach ($desas as $desa)
                            <option value="{{ $desa->nama }}">{{ $desa->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-2">
                    <button type="button" class="btn btn-primary" x-on:click="applyFilters()">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z" />
                        </svg>
                        Terapkan Filter
                    </button>
                    <button type="button" class="btn btn-secondary" x-on:click="resetFilters()">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                        Reset Filter
                    </button>
                </div>
            </div>
        </div>
    @endif

    <x-data-table :datatable="true" :datatableOptions="[
        'perPage' => 10,
        'perPageSelect' => [10, 25, 50, 100],
        'searchable' => true,
        'paging' => true,
        'labels' => [
            'placeholder' => 'Cari nomor atau nama ketua...',
            'perPage' => 'data per halaman',
            'noRows' => 'Tidak ada data',
            'noResults' => 'Tidak ada hasil untuk pencarian ini.',
            'info' => 'Menampilkan {start} - {end} dari {rows} data',
        ],
    ]" id="rwTable">

        <x-slot name="head">
            <tr>
                <x-table-header>Desa</x-table-header>
                <x-table-header>Nomor RW</x-table-header>
                <x-table-header>Nama Ketua</x-table-header>
                <x-table-header>No. HP Ketua</x-table-header>
                <x-table-header>Jumlah RT</x-table-header>
                <x-table-header class="text-center">Aksi</x-table-header>
            </tr>
        </x-slot>

        @forelse ($rws as $item)
            @php
                $editModal = 'edit-rw-' . $item->id;
            @endphp
            <tr class="table-row-hover">
                <x-table-cell>{{ $item->desa->nama ?? '-' }}</x-table-cell>
                <x-table-cell class="font-medium text-gray-900">
                    RW{{ str_pad($item->nomor_rw, 3, '0', STR_PAD_LEFT) }}
                </x-table-cell>
                <x-table-cell>{{ $item->nama_ketua ?? '-' }}</x-table-cell>
                <x-table-cell>{{ $item->no_hp_ketua ?? '-' }}</x-table-cell>
                <x-table-cell>
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800">
                        {{ $item->rts_count ?? 0 }} RT
                    </span>
                </x-table-cell>
                <x-table-cell class="text-center">
                    <div class="flex items-center justify-center gap-1">
                        @can('view', $item)
                            <button type="button" class="btn-action btn-action-view" title="Lihat Detail"
                                x-on:click="$dispatch('open-drawer', 'rw-detail-{{ $item->id }}')">
                                <x-button-icon icon="eye" class="w-4 h-4" />
                            </button>
                        @endcan
                        @can('update', $item)
                            <button type="button" class="btn-action btn-action-edit" title="Edit"
                                x-on:click="$dispatch('open-modal', '{{ $editModal }}')">
                                <x-button-icon icon="edit" class="w-4 h-4" />
                            </button>
                        @endcan
                        @can('delete', $item)
                            <button type="button" class="btn-action btn-action-delete" title="Hapus"
                                x-on:click="$dispatch('open-modal', 'delete-rw-{{ $item->id }}')">
                                <x-button-icon icon="delete" class="w-4 h-4" />
                            </button>
                        @endcan
                    </div>
                </x-table-cell>
            </tr>
        @empty
            <tr>
                <td colspan="6">
                    <x-empty-state title="Belum Ada Data RW" description="Data RW belum tersedia dalam sistem."
                        icon="empty">
                        @can('create', [\App\Models\Rw::class, auth()->user()->desa_id ?? 0])
                            <button type="button" class="btn btn-primary mt-4"
                                x-on:click="$dispatch('open-modal', 'create-rw')">
                                <x-button-icon icon="plus" />
                                Tambah RW
                            </button>
                        @endcan
                    </x-empty-state>
                </td>
            </tr>
        @endforelse
    </x-data-table>
</x-card>
