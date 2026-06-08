{{-- Master Wilayah - Desa Create --}}
<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Master Wilayah', 'url' => '#'],
            ['label' => 'Desa', 'url' => route('desa.index')],
            ['label' => 'Tambah'],
        ]" />
    </x-slot>
    <x-slot name="header">
        <x-page-header title="Tambah Desa" subtitle="Tambahkan data desa baru." />
    </x-slot>

    <x-card>
        <x-empty-state title="Form dalam pengembangan"
            description="Formulir tambah desa sedang dalam tahap pengembangan." />
    </x-card>
</x-app-layout>
