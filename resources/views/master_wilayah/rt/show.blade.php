{{-- Master Wilayah - RT Show --}}
<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Master Wilayah', 'url' => '#'],
            ['label' => 'RT', 'url' => route('rt.index')],
            ['label' => $rt->nama ?? 'Detail'],
        ]" />
    </x-slot>
    <x-slot name="header">
        <x-page-header title="Detail RT" subtitle="Informasi lengkap data RT.">
            <x-slot name="actions">
                @can('update', $rt)
                    <x-button variant="secondary" icon="edit">Edit</x-button>
                @endcan
            </x-slot>
        </x-page-header>
    </x-slot>

    <x-card>
        <x-empty-state title="Detail dalam pengembangan"
            description="Tampilan detail RT sedang dalam tahap pengembangan." />
    </x-card>
</x-app-layout>
