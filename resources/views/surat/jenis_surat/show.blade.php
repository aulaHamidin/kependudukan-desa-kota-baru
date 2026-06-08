{{-- Surat - Jenis Surat Show --}}
<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Surat', 'url' => '#'],
            ['label' => 'Jenis Surat', 'url' => route('master.jenis_surat.index')],
            ['label' => $jenisSurat->nama ?? 'Detail'],
        ]" />
    </x-slot>
    <x-slot name="header">
        <x-page-header title="Detail Jenis Surat" subtitle="Informasi lengkap jenis surat.">
            <x-slot name="actions">
                    <x-button variant="secondary" icon="arrow-left" :href="route('master.jenis_surat.index')">Kembali</x-button>
            </x-slot>
        </x-page-header>
    </x-slot>

    <x-card>
        @if (isset($jenisSurat))
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 text-sm">
                <div>
                    <p class="text-gray-500">Nama Jenis Surat</p>
                    <p class="text-gray-900 font-medium">{{ $jenisSurat->nama }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Kode</p>
                    <p class="text-gray-900 font-medium">{{ $jenisSurat->kode ?? '-' }}</p>
                </div>
                <div>
                    <p class="text-gray-700">Deskripsi.</p>
                    <p class="text-gray-700">{{ $jenisSurat->deskripsi ?? '-' }}</p>
                </div>
            </div>
        @else
            <x-empty-state title="Detail tidak tersedia" description="Data jenis surat tidak ditemukan." />
        @endif
    </x-card>

    @if (isset($recentSurat) && count($recentSurat) > 0)
        <x-card class="mt-6">
            <h3 class="text-lg font-semibold text-gray-900 mb-4">Surat Terbaru</h3>
            <x-empty-state title="Dalam pengembangan" description="Daftar surat terbaru akan ditampilkan di sini." />
        </x-card>
    @endif
</x-app-layout>
