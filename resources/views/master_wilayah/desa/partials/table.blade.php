<x-card :padding="false">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Daftar Desa</h3>
                <p class="text-sm text-gray-500">Total {{ $desa->count() }} data desa</p>
            </div>
        </div>
    </x-slot>

    <x-data-table :datatable="true" :datatableOptions="[
        'perPage' => 10,
        'perPageSelect' => [10, 25, 50, 100],
        'searchable' => true,
        'paging' => true,
        'labels' => [
            'placeholder' => 'Cari kode/nama/kecamatan...',
            'perPage' => 'data per halaman',
            'noRows' => 'Tidak ada data',
            'noResults' => 'Tidak ada hasil untuk pencarian ini.',
            'info' => 'Menampilkan {start} - {end} dari {rows} data',
        ],
    ]" id="desaTable">
        <x-slot name="filters">
            <x-table.action-bar datatableSearchFor="desaTable" :compact="true">
                @can('create', \App\Models\Desa::class)
                    <x-button type="button" icon="plus" x-on:click="$dispatch('open-modal', 'create-desa')">
                        Tambah Desa
                    </x-button>
                @endcan
            </x-table.action-bar>
        </x-slot>

        <x-slot name="head">
            <tr>
                <x-table-header>Kode</x-table-header>
                <x-table-header>Nama</x-table-header>
                <x-table-header>Kecamatan</x-table-header>
                <x-table-header>Kabupaten</x-table-header>
                <x-table-header>Provinsi</x-table-header>
                <x-table-header>Kode Pos</x-table-header>
                <x-table-header class="text-center">Aksi</x-table-header>
            </tr>
        </x-slot>

        @forelse ($desa as $item)
            @php
                $editModal = 'edit-desa-' . $item->id;
            @endphp
            <tr class="table-row-hover">
                <x-table-cell class="font-medium text-gray-900">
                    {{ $item->kode_desa }}
                </x-table-cell>
                <x-table-cell>{{ $item->nama }}</x-table-cell>
                <x-table-cell>{{ $item->kecamatan }}</x-table-cell>
                <x-table-cell>{{ $item->kabupaten }}</x-table-cell>
                <x-table-cell>{{ $item->provinsi }}</x-table-cell>
                <x-table-cell>{{ $item->kode_pos ?? '-' }}</x-table-cell>
                <x-table-cell class="text-center">
                    <div class="flex items-center justify-center gap-1">
                        @can('view', $item)
                            <button type="button"
                                class="btn-action btn-action-view"
                                title="Lihat Detail"
                                x-on:click="$dispatch('open-drawer', 'desa-detail-{{ $item->id }}')">
                                <x-button-icon icon="eye" class="w-4 h-4" />
                            </button>
                        @endcan
                        @can('update', $item)
                            <button type="button"
                                class="btn-action btn-action-edit"
                                title="Edit" x-on:click="$dispatch('open-modal', '{{ $editModal }}')">
                                <x-button-icon icon="edit" class="w-4 h-4" />
                            </button>
                        @endcan
                        @can('delete', $item)
                            <button type="button"
                                class="btn-action btn-action-delete"
                                title="Hapus" x-on:click="$dispatch('open-modal', 'delete-desa-{{ $item->id }}')">
                                <x-button-icon icon="delete" class="w-4 h-4" />
                            </button>
                        @endcan
                    </div>
                </x-table-cell>
            </tr>
        @empty
            <tr>
                <td colspan="7">
                    <x-empty-state title="Belum Ada Data Desa" description="Data desa belum tersedia dalam sistem."
                        icon="empty">
                        @can('create', \App\Models\Desa::class)
                            <x-button icon="plus" type="button" x-on:click="$dispatch('open-modal', 'create-desa')"
                                class="mt-4">
                                Tambah Desa
                            </x-button>
                        @endcan
                    </x-empty-state>
                </td>
            </tr>
        @endforelse
    </x-data-table>
</x-card>
