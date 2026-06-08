{{-- Layanan Surat Index --}}
<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[['label' => 'Layanan Surat']]" />
    </x-slot>
    <x-slot name="header">
        <x-page-header title="Layanan Surat" subtitle="Kelola pengajuan dan penerbitan surat.">
            <x-slot name="actions">
                <x-button variant="primary" icon="plus">Buat Surat Baru</x-button>
            </x-slot>
        </x-page-header>
    </x-slot>

    {{-- Stats Section --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <x-stat-card value="0" label="Total Surat" icon="document" color="primary" />
        <x-stat-card value="0" label="Menunggu" icon="alert" color="amber" />
        <x-stat-card value="0" label="Diproses" icon="check" color="indigo" />
        <x-stat-card value="0" label="Selesai" icon="check" color="primary" />
    </div>

    <x-card>
        <x-empty-state title="Halaman dalam pengembangan" description="Fitur ini sedang dalam tahap pengembangan." />
    </x-card>
</x-app-layout>
