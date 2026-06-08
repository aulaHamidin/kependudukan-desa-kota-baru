{{-- Master Wilayah - RT Create --}}
<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Master Wilayah', 'url' => '#'],
            ['label' => 'RT', 'url' => route('rt.index')],
            ['label' => 'Tambah'],
        ]" />
    </x-slot>
    <x-slot name="header">
        <x-page-header title="Tambah RT" subtitle="Tambahkan data RT baru." />
    </x-slot>

    <x-card>
        <x-empty-state title="Form dalam pengembangan"
            description="Formulir tambah RT sedang dalam tahap pengembangan." />
    </x-card>
</x-app-layout>
