{{-- Master Data - Status Edit --}}
<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Master Data', 'url' => '#'],
            ['label' => 'Status', 'url' => route('status.index')],
            ['label' => $status->nama ?? 'Edit'],
        ]" />
    </x-slot>
    <x-slot name="header">
        <x-page-header title="Edit Status" subtitle="Perbarui data status." />
    </x-slot>

    <x-card>
        <x-empty-state title="Form dalam pengembangan"
            description="Formulir edit status sedang dalam tahap pengembangan." />
    </x-card>
</x-app-layout>
