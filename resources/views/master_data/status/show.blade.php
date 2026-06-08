{{-- Master Data - Status Show --}}
<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Master Data', 'url' => '#'],
            ['label' => 'Status', 'url' => route('status.index')],
            ['label' => $status->nama ?? 'Detail'],
        ]" />
    </x-slot>
    <x-slot name="header">
        <x-page-header title="Detail Status" subtitle="Informasi lengkap data status.">
            <x-slot name="actions">
                @can('update', $status)
                    <x-button variant="secondary" icon="edit">Edit</x-button>
                @endcan
            </x-slot>
        </x-page-header>
    </x-slot>

    <x-card>
        <x-empty-state title="Detail dalam pengembangan"
            description="Tampilan detail status sedang dalam tahap pengembangan." />
    </x-card>
</x-app-layout>
