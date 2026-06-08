{{-- Surat - Jenis Surat Edit --}}
<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Surat', 'url' => '#'],
            ['label' => 'Jenis Surat', 'url' => route('jenis-surat.index')],
            ['label' => $jenisSurat->nama ?? 'Edit'],
        ]" />
    </x-slot>
    <x-slot name="header">
        <x-page-header title="Edit Jenis Surat" subtitle="Perbarui data jenis surat." />
    </x-slot>

    <x-card>
        <x-empty-state title="Form dalam pengembangan"
            description="Formulir edit jenis surat sedang dalam tahap pengembangan." />
    </x-card>
</x-app-layout>
