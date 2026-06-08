{{-- Master Wilayah - RT Edit --}}
<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Master Wilayah', 'url' => '#'],
            ['label' => 'RT', 'url' => route('rt.index')],
            ['label' => $rt->nama ?? 'Edit'],
        ]" />
    </x-slot>
    <x-slot name="header">
        <x-page-header title="Edit RT" subtitle="Perbarui data RT." />
    </x-slot>

    <x-card>
        <x-empty-state title="Form dalam pengembangan" description="Formulir edit RT sedang dalam tahap pengembangan." />
    </x-card>
</x-app-layout>
