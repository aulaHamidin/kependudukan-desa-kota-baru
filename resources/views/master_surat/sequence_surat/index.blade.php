{{-- Master Surat - Sequence Surat Index --}}
<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[['label' => 'Master Surat', 'url' => '#'], ['label' => 'Sequence Surat']]" />
    </x-slot>
    <x-slot name="header">
        <x-page-header title="Sequence Surat" subtitle="Kelola nomor urut surat.">
            <x-slot name="actions">
                <x-button variant="primary" icon="plus">Tambah Sequence</x-button>
            </x-slot>
        </x-page-header>
    </x-slot>
    <x-card>
        <x-empty-state title="Halaman dalam pengembangan" description="Fitur ini sedang dalam tahap pengembangan." />
    </x-card>
</x-app-layout>
