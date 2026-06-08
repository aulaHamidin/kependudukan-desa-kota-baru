{{-- Administrator - Kelola User Create --}}
<x-app-layout>
    <x-slot name="breadcrumb">
        <x-breadcrumb :items="[
            ['label' => 'Administrator', 'url' => '#'],
            ['label' => 'Kelola User', 'url' => route('kelola-user.index')],
            ['label' => 'Tambah'],
        ]" />
    </x-slot>
    <x-slot name="header">
        <x-page-header title="Tambah User" subtitle="Tambahkan pengguna baru ke sistem." />
    </x-slot>

    <x-card>
        <x-empty-state title="Form dalam pengembangan"
            description="Formulir tambah user sedang dalam tahap pengembangan." />
    </x-card>
</x-app-layout>
