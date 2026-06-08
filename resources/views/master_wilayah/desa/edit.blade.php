{{-- Master Wilayah - Desa Edit --}}
<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Master Wilayah', 'url' => '#'],
            ['label' => 'Desa', 'url' => route('desa.index')],
            ['label' => $desa->nama ?? 'Edit'],
        ]" />
    </x-slot>
    <x-slot name="header">
        <x-page-header title="Edit Desa" subtitle="Perbarui data desa." />
    </x-slot>

    <x-card>
        <x-empty-state title="Form dalam pengembangan"
            description="Formulir edit desa sedang dalam tahap pengembangan." />
    </x-card>
</x-app-layout>
