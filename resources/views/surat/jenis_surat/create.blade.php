{{-- Surat - Jenis Surat Create --}}
<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Surat', 'url' => '#'],
            ['label' => 'Jenis Surat', 'url' => route('jenis-surat.index')],
            ['label' => 'Tambah'],
        ]" />
    </x-slot>
    <x-slot name="header">
        <x-page-header title="Tambah Jenis Surat" subtitle="Tambahkan jenis surat baru." />
    </x-slot>

    <x-card>
        <x-empty-state title="Form dalam pengembangan"
            description="Formulir tambah jenis surat sedang dalam tahap pengembangan." />
    </x-card>
</x-app-layout>
