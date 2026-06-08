{{-- Master Wilayah - RW Create --}}
<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Master Wilayah', 'url' => '#'],
            ['label' => 'RW', 'url' => route('rw.index')],
            ['label' => 'Tambah'],
        ]" />
    </x-slot>
    <x-slot name="header">
        <x-page-header title="Tambah RW" subtitle="Tambahkan data RW baru." />
    </x-slot>

    <x-card>
        <x-empty-state title="Form dalam pengembangan"
            description="Formulir tambah RW sedang dalam tahap pengembangan." />
    </x-card>
</x-app-layout>
