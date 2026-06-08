{{-- Master Wilayah - RW Show --}}
<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Master Wilayah', 'url' => '#'],
            ['label' => 'RW', 'url' => route('rw.index')],
            ['label' => $rw->nama ?? 'Detail'],
        ]" />
    </x-slot>
    <x-slot name="header">
        <x-page-header title="Detail RW" subtitle="Informasi lengkap data RW.">
            <x-slot name="actions">
                @can('update', $rw)
                    <x-button variant="secondary" icon="edit">Edit</x-button>
                @endcan
            </x-slot>
        </x-page-header>
    </x-slot>

    <x-card>
        <x-empty-state title="Detail dalam pengembangan"
            description="Tampilan detail RW sedang dalam tahap pengembangan." />
    </x-card>
</x-app-layout>
