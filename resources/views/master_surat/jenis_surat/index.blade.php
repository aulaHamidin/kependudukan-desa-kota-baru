{{-- Master Surat - Jenis Surat Index --}}
<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[['label' => 'Master Surat', 'url' => '#'], ['label' => 'Jenis Surat']]" />
    </x-slot>
    <x-slot name="header">
        <x-page-header title="Data Jenis Surat" subtitle="Kelola data master jenis surat.">
            <x-slot name="actions">
                <x-button variant="primary" icon="plus">Tambah Jenis Surat</x-button>
            </x-slot>
        </x-page-header>
    </x-slot>
    <x-card>
        <x-empty-state title="Halaman dalam pengembangan" description="Fitur ini sedang dalam tahap pengembangan." />
    </x-card>
</x-app-layout>
