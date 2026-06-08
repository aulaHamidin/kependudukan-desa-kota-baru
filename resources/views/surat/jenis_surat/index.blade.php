{{-- Surat - Jenis Surat Index --}}
<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[['label' => 'Surat', 'url' => '#'], ['label' => 'Jenis Surat']]" />
    </x-slot>
    <x-slot name="header">
        <x-page-header title="Jenis Surat" subtitle="Kelola master data jenis surat.">
        </x-page-header>
    </x-slot>

    <x-card>
        @if (isset($jenisSurat) && count($jenisSurat) > 0)
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-700 uppercase bg-gray-50">
                        <tr>
                            <th scope="col" class="px-6 py-3">No</th>
                            <th scope="col" class="px-6 py-3">Nama</th>
                            <th scope="col" class="px-6 py-3">Kode</th>
                            <th scope="col" class="px-6 py-3">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($jenisSurat as $index => $item)
                            <tr class="bg-white border-b">
                                <td class="px-6 py-4">{{ $index + 1 }}</td>
                                <td class="px-6 py-4 text-gray-900">{{ $item->nama }}</td>
                                <td class="px-6 py-4">{{ $item->kode ?? '-' }}</td>
                                <td class="px-6 py-4">
                                    <x-button variant="ghost" size="sm"
                                        href="{{ route('master.jenis_surat.show', $item) }}">Detail</x-button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <x-empty-state title="Belum ada jenis surat" description="Tambahkan jenis surat untuk memulai." />
        @endif
    </x-card>

    @if (isset($stats))
        <x-card class="mt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Statistik</h3>
            <x-empty-state title="Statistik dalam pengembangan"
                description="Data statistik akan ditampilkan di sini." />
        </x-card>
    @endif
</x-app-layout>
