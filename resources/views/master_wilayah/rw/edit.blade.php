{{-- Master Wilayah - RW Edit --}}
<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Master Wilayah', 'url' => '#'],
            ['label' => 'RW', 'url' => route('rw.index')],
            ['label' => $rw->nama ?? 'Edit'],
        ]" />
    </x-slot>
    <x-slot name="header">
        <x-page-header title="Edit RW" subtitle="Perbarui data RW." />
    </x-slot>

    <x-card>
        <x-empty-state title="Form dalam pengembangan" description="Formulir edit RW sedang dalam tahap pengembangan." />
    </x-card>
</x-app-layout>
