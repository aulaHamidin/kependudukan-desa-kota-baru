{{-- Master Wilayah - Desa Show --}}
<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Master Wilayah', 'url' => '#'],
            ['label' => 'Desa', 'url' => route('desa.index')],
            ['label' => $desa->nama ?? 'Detail'],
        ]" />
    </x-slot>
    <x-slot name="header">
        <x-page-header title="Detail Desa" subtitle="Informasi lengkap data desa.">
            <x-slot name="actions">
                @can('update', $desa)
                    <x-button variant="secondary" icon="edit">Edit</x-button>
                @endcan
            </x-slot>
        </x-page-header>
    </x-slot>

    <x-card>
        <x-empty-state title="Detail dalam pengembangan"
            description="Tampilan detail desa sedang dalam tahap pengembangan." />
    </x-card>
</x-app-layout>
