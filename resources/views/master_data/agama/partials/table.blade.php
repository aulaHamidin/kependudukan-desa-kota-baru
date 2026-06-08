<x-card :padding="false">
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h3 class="text-lg font-semibold text-gray-800">Daftar Agama</h3>
                <p class="text-sm text-gray-500">Total {{ $items->count() }} data agama</p>
            </div>
        </div>
    </x-slot>

    <x-data-table :datatable="true" :datatableOptions="[
        'perPage' => 10,
        'perPageSelect' => [10, 25, 50, 100],
        'searchable' => true,
        'paging' => true,
        'labels' => [
            'placeholder' => 'Cari kode atau nama...',
            'perPage' => 'data per halaman',
            'noRows' => 'Tidak ada data',
            'noResults' => 'Tidak ada hasil untuk pencarian ini.',
            'info' => 'Menampilkan {start} - {end} dari {rows} data',
        ],
    ]" id="agamaTable">
        <x-slot name="filters">
            <x-table.action-bar datatableSearchFor="agamaTable" :compact="true" />
        </x-slot>

        <x-slot name="head">
            <tr>
                <x-table-header>Kode</x-table-header>
                <x-table-header>Nama</x-table-header>
                <x-table-header>Urutan</x-table-header>
                <x-table-header>Status</x-table-header>
                <x-table-header>Jumlah Penduduk</x-table-header>
            </tr>
        </x-slot>

        @forelse ($items as $item)
            <tr class="table-row-hover">
                <x-table-cell class="font-medium text-gray-900">{{ $item->kode }}</x-table-cell>
                <x-table-cell>{{ $item->nama }}</x-table-cell>
                <x-table-cell>{{ $item->urutan ?? '-' }}</x-table-cell>
                <x-table-cell>
                    @if ($item->is_active)
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                            Aktif
                        </span>
                    @else
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-600">
                            Nonaktif
                        </span>
                    @endif
                </x-table-cell>
                <x-table-cell>
                    <span
                        class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-primary-100 text-primary-800">
                        {{ $item->penduduks_count ?? 0 }} Penduduk
                    </span>
                </x-table-cell>
            </tr>
        @empty
            <tr>
                <td colspan="5">
                    <x-empty-state title="Belum Ada Data Agama" description="Data agama belum tersedia dalam sistem."
                        icon="empty">
                    </x-empty-state>
                </td>
            </tr>
        @endforelse
    </x-data-table>
</x-card>
