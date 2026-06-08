<x-app-layout>
    <x-slot name="title">Data Hubungan Keluarga</x-slot>

    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[['label' => 'Master Referensi'], ['label' => 'Hubungan Keluarga']]" />
    </x-slot>

    <x-slot name="header">
        <x-page-header title="Data Hubungan Keluarga" subtitle="Kelola data master hubungan keluarga dalam sistem">
        </x-page-header>
    </x-slot>

    <x-alert />

    <div class="mb-6 rounded-lg border border-gray-200 bg-gray-50 p-5">
        <div class="flex items-start gap-4">
            <div
                class="w-11 h-11 rounded-lg bg-primary-100 text-primary-600 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M11.25 11.25l.041-.02a.75.75 0 011.083.81l-.012.02a.75.75 0 01-1.083-.81z" />
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 18.75h.008v.008H12v-.008zM12 6.75h.008v.008H12v-.008z" />
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M12 6.75a5.25 5.25 0 00-5.25 5.25v3a5.25 5.25 0 0010.5 0v-3A5.25 5.25 0 0012 6.75z" />
                </svg>
            </div>
            <div>
                <h3 class="text-base font-semibold text-gray-800">Data hubungan keluarga bersifat
                    tetap</h3>
                <p class="text-sm text-gray-500 mt-1">
                    Data hubungan keluarga berasal dari seed dan tidak bisa ditambah, diubah, atau dihapus. Silakan
                    hubungi admin jika ada kebutuhan khusus.
                </p>
            </div>
        </div>
    </div>

    <x-card :padding="false">
        <x-slot name="header">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h3 class="text-lg font-semibold text-gray-800">Daftar Hubungan Keluarga</h3>
                    <p class="text-sm text-gray-500">Total {{ $items->count() }} data hubungan
                        keluarga</p>
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
        ]" id="hubunganKeluargaTable">
            <x-slot name="filters">
                <x-table.action-bar datatableSearchFor="hubunganKeluargaTable" :compact="true" />
            </x-slot>

            <x-slot name="head">
                <tr>
                    <x-table-header>Kode</x-table-header>
                    <x-table-header>Nama</x-table-header>
                    <x-table-header>Status</x-table-header>
                    <x-table-header>Anggota KK</x-table-header>
                    <x-table-header>Pelapor Kematian</x-table-header>
                </tr>
            </x-slot>

            @forelse ($items as $item)
                <tr class="table-row-hover">
                    <x-table-cell
                        class="font-medium text-gray-900">{{ $item->kode }}</x-table-cell>
                    <x-table-cell>{{ $item->nama }}</x-table-cell>
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
                            {{ $item->kk_members_count ?? 0 }} KK
                        </span>
                    </x-table-cell>
                    <x-table-cell>
                        <span
                            class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-200 text-gray-600">
                            {{ $item->event_kematian_reports_count ?? 0 }} Laporan
                        </span>
                    </x-table-cell>
                </tr>
            @empty
                <tr>
                    <td colspan="5">
                        <x-empty-state title="Belum Ada Data Hubungan Keluarga"
                            description="Data hubungan keluarga belum tersedia dalam sistem." icon="empty" />
                    </td>
                </tr>
            @endforelse
        </x-data-table>
    </x-card>
</x-app-layout>
